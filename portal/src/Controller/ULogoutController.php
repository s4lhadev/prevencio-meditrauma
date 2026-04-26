<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of LogoutController
 *
 * @author smarin
 */
class ULogoutController extends AbstractController {

    public function index(Request $request)
    {

        $user = $this->getUser();
        $session = $request->getSession();
        $auth = $session->get('auth');

        if (!is_null($user)) {
            $repository = $this->getDoctrine()->getRepository('App\Entity\User');
            $usuario = $repository->findForSecurityUser($user);
            $id = $usuario->getId();
            $username = $usuario->getUsername();

            $object = array("json"=>$username, "entidad"=>"logout", "id"=>$id);
            $em = $this->getDoctrine()->getManager();

            $logger = new Logger();
            $logger->addLog($em, "logout", $object, $usuario, TRUE);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('fos_user_security_logout'));
    }
}
