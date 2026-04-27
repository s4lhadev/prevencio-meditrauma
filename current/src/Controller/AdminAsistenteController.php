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

    /** Cookie con HMAC: funciona aunque falle el guardado de sesión (permisos var/sessions, proxy, etc.) */
    private const UNLOCK_COOKIE = 'admin_asistente_u';

    /** Alineado con config/packages/framework.yaml session.cookie_lifetime */
    private const UNLOCK_COOKIE_LIFETIME = 18000;

    /** Anti-CSRF del formulario /agent sin depender de sesión PHP (sesión a menudo no persiste GET→POST). */
    private const UNLOCK_CSRF_COOKIE = 'admin_asistente_csrf';

    private const UNLOCK_CSRF_LIFETIME = 900;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * El logging no debe provocar 500 (handlers sin permiso, syslog lleno, etc.).
     */
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

            $nonce = bin2hex(random_bytes(16));
            $response = $this->render('admin_asistente/unlock.html.twig', array('unlock_csrf' => $nonce));
            $this->addUnlockCsrfCookie($response, $request, $nonce);

            return $response;
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
            'agent_ajax_token' => $this->agentAjaxToken(),
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
        $postedCsrf = (string) $request->request->get('unlock_csrf', '');
        $cookieCsrf = (string) $request->cookies->get(self::UNLOCK_CSRF_COOKIE, '');
        $csrfOk = $postedCsrf !== '' && $cookieCsrf !== '' && hash_equals($cookieCsrf, $postedCsrf);
        if (!$csrfOk) {
            $this->logAdmin('warning', 'admin_asistente.unlock: form token failed (cookie vs POST)', array_merge(
                $this->unlockDebugContext($request, $pageKey),
                array('unlock_csrf_post_len' => strlen($postedCsrf), 'unlock_csrf_cookie_len' => strlen($cookieCsrf))
            ));
            $this->addFlash('error', 'El formulario caducó o la cookie no llegó. Recarga la página e inténtalo de nuevo.');

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
            'agent_ajax_token' => $this->agentAjaxToken(),
        ));
        $this->addUnlockCookie($response, $request, $pageKey);
        $this->clearUnlockCsrfCookie($response, $request);

        return $response;
    }

    public function logout(Request $request): Response
    {
        $request->getSession()->remove(self::SESSION_PAGE_UNLOCK);
        $response = $this->redirectToRoute('admin_asistente');
        $this->clearUnlockCookie($response, $request);
        $this->clearUnlockCsrfCookie($response, $request);

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
        if (!isset($data['_token']) || !$this->isValidAgentAjaxToken((string) $data['_token'])) {
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
        $fetch = $this->fetchAdminAgent($url, array(
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nX-Admin-Agent-Secret: ".$internalSecret."\r\n",
            'content' => json_encode($payload),
            'timeout' => 130,
        ));
        $fail = $this->adminAgentFailureResponse($fetch, 'No response from admin_agent. Is uvicorn running?');
        if (null !== $fail) {
            return $fail;
        }
        $result = $fetch['body'];
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
        $fetch = $this->fetchAdminAgent($url, array(
            'method' => 'GET',
            'header' => "X-Admin-Agent-Secret: ".$internalSecret."\r\n",
            'timeout' => 30,
        ));
        $fail = $this->adminAgentFailureResponse($fetch, 'No response from admin_agent. Is uvicorn running?');
        if (null !== $fail) {
            return $fail;
        }
        $result = $fetch['body'];
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
        if (!is_array($data) || !isset($data['_token']) || !$this->isValidAgentAjaxToken((string) $data['_token'])) {
            return new JsonResponse(array('error' => 'csrf'), 400);
        }
        $full = !empty($data['full']);
        $base = rtrim((string) $this->getParameter('admin_agent.internal_url'), '/');
        $internalSecret = (string) $this->getParameter('admin_agent.secret');
        if ($base === '' || $internalSecret === '' || $internalSecret === 'change_me_match_admin_agent_env') {
            return new JsonResponse(array('error' => 'agent_not_configured'), 503);
        }
        $url = $base.'/v1/reindex';
        $fetch = $this->fetchAdminAgent($url, array(
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nX-Admin-Agent-Secret: ".$internalSecret."\r\n",
            'content' => json_encode(array('full' => $full)),
            'timeout' => 600,
        ));
        $fail = $this->adminAgentFailureResponse($fetch, 'Timeout o servicio detenido');
        if (null !== $fail) {
            return $fail;
        }
        $result = $fetch['body'];
        $decoded = json_decode($result, true);
        if (!is_array($decoded)) {
            return new JsonResponse(array('error' => 'bad_response', 'raw' => substr((string) $result, 0, 500)), 502);
        }

        return new JsonResponse($decoded, 200);
    }

    /**
     * PHP http wrapper returns false on 4xx/5xx unless ignore_errors is true; then we read status from $http_response_header.
     *
     * @param array<string, mixed> $httpOptions options for stream_context_create http
     *
     * @return array{body: string|false, status: int|null}
     */
    private function fetchAdminAgent(string $url, array $httpOptions): array
    {
        $httpOptions['ignore_errors'] = true;
        $context = stream_context_create(array('http' => $httpOptions));
        $body = @file_get_contents($url, false, $context);
        $status = null;
        if (isset($http_response_header[0]) && preg_match('{HTTP/\S+\s+(\d+)\s*}', $http_response_header[0], $m)) {
            $status = (int) $m[1];
        }

        return array(
            'body' => false === $body ? false : (string) $body,
            'status' => $status,
        );
    }

    /**
     * @param array{body: string|false, status: int|null} $fetch
     */
    private function adminAgentFailureResponse(array $fetch, string $unreachableDetail): ?JsonResponse
    {
        if (false === $fetch['body']) {
            return new JsonResponse(array('error' => 'agent_unreachable', 'detail' => $unreachableDetail), 502);
        }
        if (401 === $fetch['status']) {
            return new JsonResponse(array(
                'error' => 'agent_unauthorized',
                'detail' => 'ADMIN_AGENT_SECRET en .env de Symfony debe coincidir con portal/admin_agent/.env (sin espacios/CRLF distintos). Tras cambiar: cache:clear y reiniciar uvicorn.',
            ), 502);
        }
        if (null !== $fetch['status'] && $fetch['status'] >= 400) {
            return new JsonResponse(array(
                'error' => 'agent_http_error',
                'http_status' => $fetch['status'],
                'detail' => substr($fetch['body'], 0, 500),
            ), 502);
        }

        return null;
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

    /**
     * Sin datos sensibles: no clave, no token completo, no HMAC en claro.
     */
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

    /**
     * Cabeceras de proxy/terminación TLS que Symfony no considera si falta TRUSTED_PROXIES.
     */
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

    /**
     * Token JSON (chat / reindex) sin sesión: HMAC(APP_SECRET). Mitiga CSRF entre orígenes; no sustituye el desbloqueo /agent.
     */
    private function agentAjaxToken(): string
    {
        return hash_hmac('sha256', 'admin_asistente_ajax_v1', (string) $this->getParameter('kernel.secret'));
    }

    private function isValidAgentAjaxToken(string $token): bool
    {
        $expected = $this->agentAjaxToken();

        return $token !== '' && hash_equals($expected, $token);
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

    private function addUnlockCsrfCookie(Response $response, Request $request, string $nonce): void
    {
        $secureFlag = $this->clientUsesTls($request);
        $response->headers->setCookie(new Cookie(
            self::UNLOCK_CSRF_COOKIE,
            $nonce,
            time() + self::UNLOCK_CSRF_LIFETIME,
            '/',
            null,
            $secureFlag,
            true,
            false,
            Cookie::SAMESITE_LAX
        ));
    }

    private function clearUnlockCsrfCookie(Response $response, Request $request): void
    {
        $secureFlag = $this->clientUsesTls($request);
        $response->headers->setCookie(new Cookie(
            self::UNLOCK_CSRF_COOKIE,
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
