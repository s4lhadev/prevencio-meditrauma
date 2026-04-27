<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    public function index(Request $request): Response
    {
        $pageKey = trim((string) $this->getParameter('admin_agent.page_key'));
        if ($pageKey === '') {
            return $this->render('admin_asistente/page_not_configured.html.twig');
        }
        if (!$this->isAgentPageUnlocked($request)) {
            return $this->render('admin_asistente/unlock.html.twig');
        }

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
            return $this->redirectToRoute('admin_asistente');
        }
        $pageKey = trim((string) $this->getParameter('admin_agent.page_key'));
        if ($pageKey === '') {
            $this->addFlash('error', 'Falta ADMIN_AGENT_PAGE_KEY en .env.');

            return $this->redirectToRoute('admin_asistente');
        }
        if (!$this->isCsrfTokenValid('admin_asistente_unlock', (string) $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Sesión de seguridad inválida. Prueba otra vez.');

            return $this->redirectToRoute('admin_asistente');
        }
        $submitted = trim((string) $request->request->get('key', ''));
        if (strlen($pageKey) !== strlen($submitted) || !hash_equals($pageKey, $submitted)) {
            $this->addFlash('error', 'Clave incorrecta.');

            return $this->redirectToRoute('admin_asistente');
        }
        $session = $request->getSession();
        $session->set(self::SESSION_PAGE_UNLOCK, true);
        $session->save();

        $this->addFlash('success', 'Acceso al asistente activado.');

        $response = $this->redirectToRoute('admin_asistente');
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
            return true;
        }

        $expected = $this->unlockCookieHmac($pageKey);
        $fromCookie = (string) $request->cookies->get(self::UNLOCK_COOKIE, '');
        if ($fromCookie === '' || !hash_equals($expected, $fromCookie)) {
            return false;
        }
        $request->getSession()->set(self::SESSION_PAGE_UNLOCK, true);

        return true;
    }

    private function unlockCookieHmac(string $pageKey): string
    {
        $kernelSecret = (string) $this->getParameter('kernel.secret');

        return hash_hmac('sha256', 'admin_asistente_unlock_v1'."\n".$pageKey, $kernelSecret);
    }

    private function addUnlockCookie(Response $response, Request $request, string $pageKey): void
    {
        $value = $this->unlockCookieHmac($pageKey);
        $response->headers->setCookie(new Cookie(
            self::UNLOCK_COOKIE,
            $value,
            time() + self::UNLOCK_COOKIE_LIFETIME,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            Cookie::SAMESITE_LAX
        ));
    }

    private function clearUnlockCookie(Response $response, Request $request): void
    {
        $response->headers->setCookie(new Cookie(
            self::UNLOCK_COOKIE,
            '',
            1,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            Cookie::SAMESITE_LAX
        ));
    }
}
