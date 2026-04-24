<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
/**
 * Asistente IA bajo /agent — proxy a admin_agent/ (local). Rutas en config/routes.yaml.
 *
 * @IsGranted("ROLE_ADMIN")
 */
class AdminAsistenteController extends AbstractController
{
    public function index(): Response
    {
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

    public function chat(Request $request): JsonResponse
    {
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

    public function indexStatus(): JsonResponse
    {
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
}
