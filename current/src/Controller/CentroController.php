<?php

namespace App\Controller;

use App\Entity\Centro;
use App\Entity\CentroTrabajoEmpresa;
use App\Form\CentroType;

use App\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;


class CentroController extends AbstractController
{
    public function createCentro(Request $request, TranslatorInterface $translator)
    {

	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getAddCentroTrabajoSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

        $centro = new Centro();
        $form = $this->createForm(CentroType::class, $centro);

        $empresa = $session->get('empresa');

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $centro = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($centro);
            $em->flush();

            //Asignamos el centro a la empresa
            if(!is_null($empresa)){
                $empresa = $this->getDoctrine()->getRepository('App\Entity\Empresa')->find($empresa->getId());
                $centroTrabajoEmpresa = new CentroTrabajoEmpresa();
                $centroTrabajoEmpresa->setEmpresa($empresa);
                $centroTrabajoEmpresa->setCentro($centro);
                $centroTrabajoEmpresa->setAnulado(false);
                $em->persist($centroTrabajoEmpresa);
                $em->flush();
            }

	        $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('centro_update', array('id' => $centro->getId()));
        }
        return $this->render( 'centro/edit.html.twig', array('trabajadoresEmpresa' => null, 'form' => $form->createView()) );
    }

    public function showCentros(Request $request)
    {
	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getCentroTrabajoSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $centros = $this->getDoctrine()->getRepository('App\Entity\Centro')->findBy(array('anulado' => false));

        $object = array("json"=>$username, "entidad"=>"centros de trabajo", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('centro/show.html.twig', array('centros' => $centros) );
    }

    public function deleteCentro($id, Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getDeleteCentroTrabajoSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

        $centro = $em->getRepository('App\Entity\Centro')->find($id);

        if (!$centro) {
            throw $this->createNotFoundException(
                'There are no articles with the following id: ' . $id
            );
        }

	    //Buscamos los centros de trabajo de la empresa
	    $centroTrabajoEmpresa = $em->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('centro' => $centro, 'anulado' => false));

	    foreach ($centroTrabajoEmpresa as $cte){
		    $cte->setAnulado(true);
		    $em->persist($cte);
		    $em->flush();
	    }

	    $centro->setAnulado(true);
	    $em->persist($centro);
        $em->flush();

	    $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        return $this->redirectToRoute('centro_show');
    }

    public function updateCentro(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getEditCentroTrabajoSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

        $centro = $em->getRepository('App\Entity\Centro')->find($id);

        if (!$centro) {
            throw $this->createNotFoundException(
                'There are no articles with the following id: ' . $id
            );
        }

	    //Buscamos los centros de trabajo de la empresa
	    $centroTrabajoEmpresa = $em->getRepository('App\Entity\CentroTrabajoEmpresa')->findOneBy(array('centro' => $centro, 'anulado' => false));

        if(!is_null($centroTrabajoEmpresa)){
	        $session->set('empresa', $centroTrabajoEmpresa->getEmpresa());
        }else{
            $session->set('empresa', null);
        }

	    $empresa = $session->get('empresa');
        $trabajadoresEmpresa = null;
        if(!is_null($empresa)){
            //Buscamos los trabajadores de la empresa
            $trabajadoresEmpresa = $em->getRepository('App\Entity\TrabajadorEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));
        }

        $form = $this->createForm(CentroType::class, $centro);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $centro = $form->getData();
            $em->persist($centro);
            $em->flush();

	        $traduccion = $translator->trans('TRANS_UPDATE_OK');
	        $this->addFlash('success', $traduccion);
            return $this->redirectToRoute('centro_update', array('id' => $centro->getId()));
        }
        return $this->render('centro/edit.html.twig',  array('trabajadoresEmpresa' => $trabajadoresEmpresa, 'form' => $form->createView()) );
    }
}