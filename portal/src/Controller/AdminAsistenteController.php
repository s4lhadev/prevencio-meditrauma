<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

/**
 * Asistente IA bajo /agent — proxy a admin_agent/ (local). Rutas en config/routes.yaml.
 * Acceso con clave en sesión; no requiere ROLE_ADMIN / login.
 */
class AdminAsistenteController extends AbstractController
{
    const SESSION_PAGE_UNLOCK = 'admin_asistente_page_unlocked';

    private const UNLOCK_COOKIE = 'admin_asistente_u';

    private const UNLOCK_COOKIE_LIFETIME = 18000;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    private function logAdmin(string $level, string $message, array $context = array()): void
    {
        try {
            $this->logger->log($level, $message, $context);
        } catch (\Throwable $e) {
        }
    }

    public function index(Request $request): Response
    {
        $pageKey = trim((string) $this->getParameter('admin_agent.page_key'));
        if ($pageKey === '') {
            $this->logAdmin('info', 'admin_asistente.index: page key empty (ADMIN_AGENT_PAGE_KEY not set)');

            return $this->render('admin_asistente/page_not_configured.html.twig');
        }
        if (!$this->isAgentPageUnlocked($request)) {
            $this->logAdmin('info', 'admin_asistente.index: not unlocked, showing form', $this->unlockDebugContext($request, $pageKey));

            return $this->render('admin_asistente/unlock.html.twig');
        }
        $this->logAdmin('debug', 'admin_asistente.index: unlocked, rendering assistant', $this->unlockDebugContext($request, $pageKey));

        $base = (string) $this->getParameter('admin_agent.internal_url');
        $secret = (string) $this->getParameter('admin_agent.secret');
        if ($base === '' || $secret === '' || $secret === 'change_me_match_admin_agent_env') {
            $configured = false;
        } else {
            $configured = true;
        }

        return $this->render('admin_asistente/index.html.twig', array(
            'assistant_configured' => $configured,
        ));
    }

    public function unlock(Request $request): Response
    {
        if (!$request->isMethod('POST')) {
            $this->logAdmin('warning', 'admin_asistente.unlock: not POST, redirecting');

            return $this->redirectToRoute('admin_asistente');
        }
        $pageKey = trim((string) $this->getParameter('admin_agent.page_key'));
        if ($pageKey === '') {
            $this->logAdmin('warning', 'admin_asistente.unlock: page key not configured');
            $this->addFlash('error', 'Falta ADMIN_AGENT_PAGE_KEY en .env.');

            return $this->redirectToRoute('admin_asistente');
        }
        $csrfToken = (string) $request->request->get('_csrf_token', '');
        $csrfOk = $this->isCsrfTokenValid('admin_asistente_unlock', $csrfToken);
        if (!$csrfOk) {
            $this->logAdmin('warning', 'admin_asistente.unlock: CSRF failed', array_merge(
                $this->unlockDebugContext($request, $pageKey),
                array('csrf_token_len' => strlen($csrfToken))
            ));
            $this->addFlash('error', 'Sesión de seguridad inválida. Prueba otra vez.');

            return $this->redirectToRoute('admin_asistente');
        }
        $submitted = trim((string) $request->request->get('key', ''));
        $keyLen = strlen($pageKey);
        $subLen = strlen($submitted);
        $lenMatch = $keyLen === $subLen;
        $hashMatch = $lenMatch && hash_equals($pageKey, $submitted);
        if (!$hashMatch) {
            $this->logAdmin('info', 'admin_asistente.unlock: key mismatch (lengths or hash)', array_merge(
                $this->unlockDebugContext($request, $pageKey),
                array('key_len' => $keyLen, 'submitted_len' => $subLen, 'length_match' => $lenMatch)
            ));
            $this->addFlash('error', 'Clave incorrecta.');

            return $this->redirectToRoute('admin_asistente');
        }
        $session = $request->getSession();
        $session->set(self::SESSION_PAGE_UNLOCK, true);
        $session->save();

        $this->logAdmin('info', 'admin_asistente.unlock: success, render assistant (200+Set-Cookie+replaceState)', $this->unlockDebugContext($request, $pageKey));

        $base = (string) $this->getParameter('admin_agent.internal_url');
        $secret = (string) $this->getParameter('admin_agent.secret');
        $configured = !($base === '' || $secret === '' || $secret === 'change_me_match_admin_agent_env');

        $response = $this->render('admin_asistente/index.html.twig', array(
            'assistant_configured' => $configured,
            'agent_replace_history' => true,
            'agent_unlock_notice' => 'Acceso al asistente activado.',
        ));
        $this->addUnlockCookie($response, $request, $pageKey);

        return $response;
    }

    public function logout(Request $request): Response
    {
        $request->getSession()->remove(self::SESSION_PAGE_UNLOCK);
        $response = $this->redirectToRoute('admin_asistente');
        $this->clearUnlockCookie($response, $request);

        return $response;
    }

    public function chat(Request $request): JsonResponse
    {
        if (!$this->isAgentPageUnlocked($request)) {
            return new JsonResponse(array('error' => 'forbidden', 'detail' => 'Desbloquea /agent con la clave.'), 403);
        }
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(array('error' => 'invalid_json'), 400);
        }
        if (!isset($data['_token']) || !$this->isCsrfTokenValid('admin_asistente', (string) $data['_token'])) {
            return new JsonResponse(array('error' => 'csrf'), 400);
        }
        $message = isset($data['message']) ? trim((string) $data['message']) : '';
        if ($message === '') {
            return new JsonResponse(array('error' => 'empty_message'), 400);
        }
        $history = isset($data['messages']) && is_array($data['messages']) ? $data['messages'] : null;

        $base = rtrim((string) $this->getParameter('admin_agent.internal_url'), '/');
        $internalSecret = (string) $this->getParameter('admin_agent.secret');
        if ($base === '' || $internalSecret === '' || $internalSecret === 'change_me_match_admin_agent_env') {
            return new JsonResponse(array('error' => 'agent_not_configured'), 503);
        }
        $url = $base.'/v1/chat';
        $useCodebase = !isset($data['use_codebase']) || $data['use_codebase'];
        $payload = array('message' => $message, 'use_codebase' => (bool) $useCodebase);
        if (null !== $history) {
            $payload['messages'] = $history;
        }
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nX-Admin-Agent-Secret: ".$internalSecret."\r\n",
                'content' => json_encode($payload),
                'timeout' => 130,
            ),
        ));
        $result = @file_get_contents($url, false, $context);
        if (false === $result) {
            return new JsonResponse(array('error' => 'agent_unreachable', 'detail' => 'No response from admin_agent. Is uvicorn running?'), 502);
        }
        $decoded = json_decode($result, true);
        if (!is_array($decoded) || !isset($decoded['reply'])) {
            return new JsonResponse(array('error' => 'bad_response', 'raw' => substr($result, 0, 500)), 502);
        }

        return new JsonResponse($decoded, 200);
    }

    public function indexStatus(Request $request): JsonResponse
    {
        if (!$this->isAgentPageUnlocked($request)) {
            return new JsonResponse(array('error' => 'forbidden', 'detail' => 'Desbloquea /agent con la clave.'), 403);
        }
        $base = rtrim((string) $this->getParameter('admin_agent.internal_url'), '/');
        $internalSecret = (string) $this->getParameter('admin_agent.secret');
        if ($base === '' || $internalSecret === '' || $internalSecret === 'change_me_match_admin_agent_env') {
            return new JsonResponse(array('error' => 'agent_not_configured'), 503);
        }
        $url = $base.'/v1/index/status';
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'header' => "X-Admin-Agent-Secret: ".$internalSecret."\r\n",
                'timeout' => 30,
            ),
        ));
        $result = @file_get_contents($url, false, $context);
        if (false === $result) {
            return new JsonResponse(array('error' => 'agent_unreachable'), 502);
        }
        $decoded = json_decode($result, true);
        if (!is_array($decoded)) {
            return new JsonResponse(array('error' => 'bad_response'), 502);
        }

        return new JsonResponse($decoded, 200);
    }

    public function reindex(Request $request): JsonResponse
    {
        if (!$this->isAgentPageUnlocked($request)) {
            return new JsonResponse(array('error' => 'forbidden', 'detail' => 'Desbloquea /agent con la clave.'), 403);
        }
        if (!$request->isMethod('POST')) {
            return new JsonResponse(array('error' => 'method'), 405);
        }
        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || !isset($data['_token']) || !$this->isCsrfTokenValid('admin_asistente', (string) $data['_token'])) {
            return new JsonResponse(array('error' => 'csrf'), 400);
        }
        $full = !empty($data['full']);
        $base = rtrim((string) $this->getParameter('admin_agent.internal_url'), '/');
        $internalSecret = (string) $this->getParameter('admin_agent.secret');
        if ($base === '' || $internalSecret === '' || $internalSecret === 'change_me_match_admin_agent_env') {
            return new JsonResponse(array('error' => 'agent_not_configured'), 503);
        }
        $url = $base.'/v1/reindex';
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nX-Admin-Agent-Secret: ".$internalSecret."\r\n",
                'content' => json_encode(array('full' => $full)),
                'timeout' => 600,
            ),
        ));
        $result = @file_get_contents($url, false, $context);
        if (false === $result) {
            return new JsonResponse(array('error' => 'agent_unreachable', 'detail' => 'Timeout o servicio detenido'), 502);
        }
        $decoded = json_decode($result, true);
        if (!is_array($decoded)) {
            return new JsonResponse(array('error' => 'bad_response', 'raw' => substr((string) $result, 0, 500)), 502);
        }

        return new JsonResponse($decoded, 200);
    }

    private function isAgentPageUnlocked(Request $request): bool
    {
        $pageKey = trim((string) $this->getParameter('admin_agent.page_key'));
        if ($pageKey === '') {
            return false;
        }

        if ((bool) $request->getSession()->get(self::SESSION_PAGE_UNLOCK)) {
            $this->logAdmin('debug', 'admin_asistente.isUnlocked: true via session', $this->unlockDebugContext($request, $pageKey));

            return true;
        }

        $expected = $this->unlockCookieHmac($pageKey);
        $fromCookie = (string) $request->cookies->get(self::UNLOCK_COOKIE, '');
        if ($fromCookie === '' || !hash_equals($expected, $fromCookie)) {
            $hmacEqual = $fromCookie !== '' && hash_equals($expected, $fromCookie);
            $this->logAdmin('notice', 'admin_asistente.isUnlocked: false (no session, cookie bad/missing)', array_merge(
                $this->unlockDebugContext($request, $pageKey),
                array('cookie_len' => strlen($fromCookie), 'hmac_match' => $hmacEqual)
            ));

            return false;
        }
        $this->logAdmin('info', 'admin_asistente.isUnlocked: true via cookie, syncing session', $this->unlockDebugContext($request, $pageKey));
        $request->getSession()->set(self::SESSION_PAGE_UNLOCK, true);

        return true;
    }

    private function unlockDebugContext(Request $request, string $pageKey): array
    {
        $session = $request->getSession();
        $sid = method_exists($session, 'getId') ? (string) $session->getId() : '';
        if (strlen($sid) > 8) {
            $sid = substr($sid, 0, 4).'…'.substr($sid, -4);
        }

        return array(
            'request_is_secure' => $request->isSecure(),
            'client_uses_tls' => $this->clientUsesTls($request),
            'session_id' => $sid,
            'session_flag' => (bool) $request->getSession()->get(self::SESSION_PAGE_UNLOCK),
            'cookie_present' => $request->cookies->has(self::UNLOCK_COOKIE),
            'page_key_configured' => $pageKey !== '',
            'page_key_len' => strlen($pageKey),
        );
    }

    private function clientUsesTls(Request $request): bool
    {
        if ($request->isSecure()) {
            return true;
        }
        $https = isset($_SERVER['HTTPS']) ? (string) $_SERVER['HTTPS'] : '';
        if ($https !== '' && 'off' !== strtolower($https)) {
            return true;
        }
        $xfProto = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) : '';
        if ('https' === $xfProto) {
            return true;
        }
        $xfSsl = isset($_SERVER['HTTP_X_FORWARDED_SSL']) ? strtolower((string) $_SERVER['HTTP_X_FORWARDED_SSL']) : '';
        if ('on' === $xfSsl) {
            return true;
        }
        $port = isset($_SERVER['SERVER_PORT']) ? (string) $_SERVER['SERVER_PORT'] : '';

        return '443' === $port;
    }

    private function unlockCookieHmac(string $pageKey): string
    {
        $kernelSecret = (string) $this->getParameter('kernel.secret');

        return hash_hmac('sha256', 'admin_asistente_unlock_v1'."\n".$pageKey, $kernelSecret);
    }

    private function addUnlockCookie(Response $response, Request $request, string $pageKey): void
    {
        $value = $this->unlockCookieHmac($pageKey);
        $secureFlag = $this->clientUsesTls($request);
        $response->headers->setCookie(new Cookie(
            self::UNLOCK_COOKIE,
            $value,
            time() + self::UNLOCK_COOKIE_LIFETIME,
            '/',
            null,
            $secureFlag,
            true,
            false,
            Cookie::SAMESITE_LAX
        ));
    }

    private function clearUnlockCookie(Response $response, Request $request): void
    {
        $secureFlag = $this->clientUsesTls($request);
        $response->headers->setCookie(new Cookie(
            self::UNLOCK_COOKIE,
            '',
            1,
            '/',
            null,
            $secureFlag,
            true,
            false,
            Cookie::SAMESITE_LAX
        ));
    }
}
