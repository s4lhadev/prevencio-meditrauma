<?php
// src/EventListener/LoginListener.php

namespace App\EventListener;

use App\Logger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use App\Entity\User;

class LoginListener
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $em = $this->em;
        $user = $event->getAuthenticationToken()->getUser();
        $repository = $em->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        if (null === $usuario) {
            return;
        }
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $object = array("json"=>$username, "entidad"=>"login", "id"=>$id);
        $logger = new Logger();
        $logger->addLog($em, "login", $object, $usuario, TRUE);
        $em->flush();
    }
}