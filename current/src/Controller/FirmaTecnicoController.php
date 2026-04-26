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

class FirmaTecnicoController extends AbstractController
{
    public function showFirmas(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getFirmaTecnicoSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

	    //Buscamos los contratos no renovados
	    $query = "select * from usuario_tecnico where anulado = false and tecnico = true order by nombre asc";
	    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
	    $stmt->execute();
        $tecnicos = $stmt->fetchAll();

        $object = array("json"=>$username, "entidad"=>"firmas técnicos", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('firmatecnico/show.html.twig', array('tecnicos' => $tecnicos));
    }

    public function updateFirmas(Request $request, $id, TranslatorInterface $translator)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getFirmaTecnicoSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $tecnico = $this->getDoctrine()->getRepository('App\Entity\UsuarioTecnico')->find($id);
        $form = $this->createForm(FirmaType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();

            //Si ha informado la firma la guardamos
            $firma = $form->get('firma')->getData();
            if(!is_null($firma)){

                //Obtenemos el nombre y la extension
                $filename = $firma->getClientOriginalName();

                move_uploaded_file($firma, "upload/media/firmas/tecnico/$filename");
                $path_info = pathinfo("upload/media/firmas/tecnico/$filename");
                $extension = $path_info['extension'];

                $tecnicoId = $tecnico->getId();
                $newName = $tecnicoId.'.'.$extension;

                //Renombramos el logo
                rename("upload/media/firmas/tecnico/$filename","upload/media/firmas/tecnico/$newName");

                //Añadimos el logo a la empresa
                $tecnico->setFirma($newName);
                $em->persist($tecnico);
                $em->flush();
            }

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('tecnico_firma_tecnicos_show');
        }

        return $this->render('firmatecnico/edit.html.twig', array('form' => $form->createView(), 'firma' => $tecnico->getFirma(), 'nombreTecnico' => $tecnico->getNombreCompleto()));
    }
}