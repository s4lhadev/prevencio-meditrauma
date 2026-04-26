<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Logger;

class LogEnvioMailController extends AbstractController
{
    public function index(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getLogEnvioMailSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $object = array("json"=>$username, "entidad"=>"log envio mail", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $object, $usuario, TRUE);
        $em->flush();

        $log = $this->getDoctrine()->getRepository('App\Entity\LogEnvioMail')->findBy(array(), array('fecha' => 'DESC'));

        return $this->render('emails/log.html.twig', array('log' => $log));
    }
}