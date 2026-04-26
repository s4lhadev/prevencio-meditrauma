<?php

namespace App\Controller;

use App\Entity\Centro;
use App\Entity\CentroTrabajoEmpresa;
use App\Form\EmpresaType;
use App\Form\FirmaType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Logger;
use Symfony\Contracts\Translation\TranslatorInterface;

class FirmaMedicoController extends AbstractController
{
    public function showFirmas(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getFirmaMedicoSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

	    //Buscamos los contratos no renovados
	    $query = "select * from doctor where anulado = false order by descripcion asc";
	    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
	    $stmt->execute();
        $medicos = $stmt->fetchAll();

        $object = array("json"=>$username, "entidad"=>"firmas médicos", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('firmamedico/show.html.twig', array('medicos' => $medicos));
    }

    public function updateFirmas(Request $request, $id, TranslatorInterface $translator)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getFirmaMedicoSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $medico = $this->getDoctrine()->getRepository('App\Entity\Doctor')->find($id);
        $form = $this->createForm(FirmaType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();

            //Si ha informado la firma la guardamos
            $firma = $form->get('firma')->getData();
            if(!is_null($firma)){

                //Obtenemos el nombre y la extension
                $filename = $firma->getClientOriginalName();

                move_uploaded_file($firma, "upload/media/firmas/medico/$filename");
                $path_info = pathinfo("upload/media/firmas/medico/$filename");
                $extension = $path_info['extension'];

                $medicoId = $medico->getId();
                $newName = $medicoId.'.'.$extension;

                //Renombramos el logo
                rename("upload/media/firmas/medico/$filename","upload/media/firmas/medico/$newName");

                //Añadimos el logo a la empresa
                $medico->setFirma($newName);
                $em->persist($medico);
                $em->flush();
            }

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('medico_firma_medicos_show');
        }

        return $this->render('firmamedico/edit.html.twig', array('form' => $form->createView(), 'firma' => $medico->getFirma(), 'nombreMedico' => $medico->getDescripcion()));
    }
}