<?php
// src/EventListener/LoginListener.php

namespace App\EventListener;

use App\Logger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

class LoginListener
{
    private $em;
	private $templating;
	private $router;
	private $status;
	private $session;

    public function __construct(EntityManagerInterface $em, \Twig_Environment $templating, SessionInterface $session, RouterInterface $router)
    {
        $this->em = $em;
	    $this->templating = $templating;
	    $this->session         = $session;
	    $this->router          = $router;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $em = $this->em;
        $session = $this->session;
        $user = $event->getAuthenticationToken()->getUser();
        $repository = $em->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();
        $rol = $usuario->getRol();
        $rolId = $rol->getId();

        $privilegios = $em->getRepository('App\Entity\PrivilegioRoles')->find($rolId);

        //Comprobamos que el usuario tenga acceso a la intranet
	    $usuarioIntranet = $em->getRepository('App\Entity\UserIntranet')->findOneBy(array('usuario' => $usuario));

	    if(is_null($usuarioIntranet) && !$privilegios->getIntranetSn()){
		    $this->status = -1;
		    $this->session->invalidate(1);
		    $this->session->clear();
	    }

        //Comprobamos si el usuario puede acceder a más de una empresa
        $grupoEmpresaSn = false;
        if(!is_null($usuarioIntranet)){
            $usuarioIntranetEmpresa = $em->getRepository('App\Entity\UserIntranetEmpresa')->findBy(array('usuarioIntranet' => $usuarioIntranet));
            if(count($usuarioIntranetEmpresa) > 0){
                $grupoEmpresaSn = true;
            }
        }

        $session->set('grupoEmpresa', $grupoEmpresaSn);

        $object = array("json"=>$username, "entidad"=>"login", "id"=>$id);
        $logger = new Logger();
        $logger->addLog($em, "login", $object, $usuario, TRUE);
        $em->flush();
    }

	public function onDeauthGiven(FilterResponseEvent  $event){
		if($this->status == -1){
			$event->setResponse(new RedirectResponse($this->router->generate('error_403')));
		}
	}
}