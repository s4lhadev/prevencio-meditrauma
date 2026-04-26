<?php

namespace App\Controller;

use App\Entity\Enfermedad;
use App\Entity\Revision;
use App\Entity\TrabajadorEnfermedad;
use App\Form\EnfermedadType;
use App\Form\RevisionType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Logger;
use Symfony\Contracts\Translation\TranslatorInterface;

class EnfermedadController extends AbstractController
{
    public function show(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getEnfermedadProfesionalSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $enfermedades = $this->getDoctrine()->getRepository('App\Entity\Enfermedad')->findBy(array('anulado' => false), array('descripcion' => 'ASC'));

        $object = array("json"=>$username, "entidad"=>"enfermedades", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('enfermedad/show.html.twig', array('enfermedades' => $enfermedades));
    }

    public function createEnfermedad(Request $request, TranslatorInterface $translator){

        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getAddEnfermedadProfesionalSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        //Creamos el objeto
        $enfermedad = new Enfermedad();

        $form = $this->createForm(EnfermedadType::class, $enfermedad);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $enfermedad = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($enfermedad);
            $em->flush();

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('medico_enfermedad_update', array('id' => $enfermedad->getId()));
        }

        return $this->render('enfermedad/edit.html.twig', array('form' => $form->createView()));
    }

    public function updateEnfermedad(Request $request, $id, TranslatorInterface $translator){

        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getEditEnfermedadProfesionalSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        //Creamos el objeto
        $enfermedad = $em->getRepository('App\Entity\Enfermedad')->find($id);

        $form = $this->createForm(EnfermedadType::class, $enfermedad);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $enfermedad = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($enfermedad);
            $em->flush();

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('medico_enfermedad_update', array('id' => $enfermedad->getId()));
        }

        return $this->render('enfermedad/edit.html.twig', array('form' => $form->createView()));
    }

    public function deleteEnfermedad(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getDeleteEnfermedadProfesionalSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $enfermedad = $em->getRepository('App\Entity\Enfermedad')->find($id);
        $enfermedad->setAnulado(true);
        $em->persist($enfermedad);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);
        return $this->redirectToRoute('medico_enfermedad_show');
    }

    public function recuperaEnfermedadTrabajador(Request $request){
        $em = $this->getDoctrine()->getManager();

        $trabajadorEnfermedadId = $_REQUEST['trabajadorEnfermedadId'];
        $trabajadorEnfermedad = $em->getRepository('App\Entity\TrabajadorEnfermedad')->find($trabajadorEnfermedadId);

        $enfermedadId = null;
        if(!is_null($trabajadorEnfermedad->getEnfermedad())){
            $enfermedadId = $trabajadorEnfermedad->getEnfermedad()->getId();
        }

        $data = array(
            'enfermedad' => $enfermedadId,
            'id' => $trabajadorEnfermedad->getId()
        );

        return new JsonResponse($data);
    }

    public function addEnfermedadTrabajador(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $trabajadorId = $_REQUEST['trabajadorId'];
        $enfermedadId = $_REQUEST['enfermedadId'];

        $trabajador = $em->getRepository('App\Entity\Trabajador')->find($trabajadorId);
        $enfermedad = $em->getRepository('App\Entity\Enfermedad')->find($enfermedadId);

        $trabajadorEnfermedad = new TrabajadorEnfermedad();
        $trabajadorEnfermedad->setTrabajador($trabajador);
        $trabajadorEnfermedad->setEnfermedad($enfermedad);
        $trabajadorEnfermedad->setAnulado(false);
        $em->persist($trabajadorEnfermedad);
        $em->flush();

        $data = array();
        array_push($data, "OK");
        return new JsonResponse($data);
    }

    public function updateEnfermedadTrabajador(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $trabajadorEnfermedadId = $_REQUEST['trabajadorEnfermedadId'];
        $enfermedadId = $_REQUEST['enfermedadId'];

        $trabajadorEnfermedad = $em->getRepository('App\Entity\TrabajadorEnfermedad')->find($trabajadorEnfermedadId);
        $enfermedad = $em->getRepository('App\Entity\Enfermedad')->find($enfermedadId);

        $trabajadorEnfermedad->setEnfermedad($enfermedad);
        $trabajadorEnfermedad->setAnulado(false);
        $em->persist($trabajadorEnfermedad);
        $em->flush();

        $data = array();
        array_push($data, "OK");
        return new JsonResponse($data);
    }

    public function deleteEnfermedadTrabajador(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $trabajadorEnfermedadId = $_REQUEST['trabajadorEnfermedadId'];
        $trabajadorEnfermedad = $em->getRepository('App\Entity\TrabajadorEnfermedad')->find($trabajadorEnfermedadId);
        $trabajadorEnfermedad->setAnulado(true);
        $em->persist($trabajadorEnfermedad);
        $em->flush();

        $data = array();
        array_push($data, "OK");
        return new JsonResponse($data);
    }

}