<?php

namespace App\Controller;

use App\Entity\Protocolo;
use App\Entity\ProtocoloCuestionario;
use App\Entity\PuestoTrabajoProtocolo;
use App\Form\ProtocoloType;
use App\Form\PuestoTrabajoProtocoloType;
use App\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class PuestoTrabajoProtocoloCuestionarioController extends AbstractController {

    public function index(Request $request) {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoReconocimientosSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        //$puestoTrabajoProtocolo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoProtocolo')->findBy(array('anulado' => false), array('empresa' => 'ASC'));
        $protocolos = $this->getDoctrine()->getRepository('App\Entity\Protocolo')->findBy(array('anulado' => false), array('descripcion' => 'ASC'));

        //Buscamos los cuestionarios del protocolo
        $query = "select b.id as empresaId, a.id as puestoTrabajoId, b.empresa, a.descripcion, a.actualizado from puesto_trabajo_centro a
            inner join empresa b on a.empresa_id = b.id
            where a.anulado = false
            order by b.empresa ASC";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $puestoTrabajoProtocolo = $stmt->fetchAll();

        $object = array("json"=>$username, "entidad"=>"puesto de trabajo - protocolo - cuestionario", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('puestotrabajoprotocolocuestionario/index.html.twig', array('puestoTrabajoProtocolo' => $puestoTrabajoProtocolo, 'protocolos' => $protocolos));
    }

    public function createProtocolo(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getProtocoloSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $protocolo = new Protocolo();

        $form = $this->createForm(ProtocoloType::class, $protocolo);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $protocolo = $form->getData();
            $em->persist($protocolo);
            $em->flush();

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('puesto_trabajo_protocolo_cuestionario_protocolo_update', array('id' => $protocolo->getId()));
        }

        return $this->render( 'puestotrabajoprotocolocuestionario/protocolo.html.twig', array('form' => $form->createView()));
    }

    public function updateProtocolo(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getProtocoloSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $protocolo = $this->getDoctrine()->getRepository('App\Entity\Protocolo')->find($id);

        //Buscamos los cuestionarios tipo 0
        $tipoCuestionario = $this->getDoctrine()->getRepository('App\Entity\TipoCuestionario')->find(0);
        $listCuestionarios = $this->getDoctrine()->getRepository('App\Entity\Cuestionario')->findBy(array('anulado' => false));

        //Buscamos los cuestionarios tipo 1
        $tipoExploracion = $this->getDoctrine()->getRepository('App\Entity\TipoCuestionario')->find(1);
        $listExploraciones = $this->getDoctrine()->getRepository('App\Entity\Cuestionario')->findBy(array('tipoCuestionario' => $tipoExploracion, 'anulado' => false));

        //Buscamos los cuestionarios del protocolo
        $query = "select a.id, b.descripcion from protocolo_cuestionario a
            inner join cuestionario b on a.cuestionario_id = b.id
            where a.anulado = false
            and a.protocolo_id = $id
            order by b.descripcion asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $cuestionarios = $stmt->fetchAll();

        //Buscamos las exploraciones del protocolo
        $query = "select a.id, b.descripcion from protocolo_cuestionario a
            inner join cuestionario b on a.cuestionario_id = b.id
            where a.anulado = false
            and a.protocolo_id = $id
            and b.tipo_cuestionario_id = 1
            order by b.descripcion asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $exploraciones = $stmt->fetchAll();

        $form = $this->createForm(ProtocoloType::class, $protocolo);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $protocolo = $form->getData();
            $em->persist($protocolo);
            $em->flush();

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('puesto_trabajo_protocolo_cuestionario_protocolo_update', array('id' => $protocolo->getId()));
        }

        return $this->render( 'puestotrabajoprotocolocuestionario/protocolo.html.twig', array('form' => $form->createView(), 'listCuestionarios' => $listCuestionarios, 'listExploraciones' => $listExploraciones, 'cuestionarios' => $cuestionarios, 'exploraciones' => $exploraciones));
    }

    public function deleteProtocolo(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $protocoloId = $_REQUEST['protocoloId'];
        $protocolo = $this->getDoctrine()->getRepository('App\Entity\Protocolo')->find($protocoloId);
        $cuestionarios = $this->getDoctrine()->getRepository('App\Entity\ProtocoloCuestionario')->findBy(array('protocolo' => $protocolo, 'anulado' => false));

        foreach ($cuestionarios as $c){
            $c->setAnulado(true);
            $em->persist($c);
            $em->flush();
        }

        $protocolo->setAnulado(true);
        $em->persist($protocolo);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function createProtocoloCuestionario(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $protocoloId = $_REQUEST['protocoloId'];
        $cuestionarioId = $_REQUEST['cuestionarioId'];
        $protocolo = $this->getDoctrine()->getRepository('App\Entity\Protocolo')->find($protocoloId);
        $cuestionario = $this->getDoctrine()->getRepository('App\Entity\Cuestionario')->find($cuestionarioId);

        $protocoloCuestionario = new ProtocoloCuestionario();
        $protocoloCuestionario->setCuestionario($cuestionario);
        $protocoloCuestionario->setProtocolo($protocolo);
        $em->persist($protocoloCuestionario);
        $em->flush();

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success',  $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function updateProtocoloCuestionario(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $protocoloCuestionarioId = $_REQUEST['protocoloCuestionarioId'];
        $cuestionarioId = $_REQUEST['cuestionarioId'];
        $protocoloCuestionario = $this->getDoctrine()->getRepository('App\Entity\ProtocoloCuestionario')->find($protocoloCuestionarioId);
        $cuestionario = $this->getDoctrine()->getRepository('App\Entity\Cuestionario')->find($cuestionarioId);

        $protocoloCuestionario->setCuestionario($cuestionario);
        $em->persist($protocoloCuestionario);
        $em->flush();

        $traduccion = $translator->trans('TRANS_UPDATE_OK');
        $this->addFlash('success',  $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function recuperaProtocoloCuestionario(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $protocoloCuestionarioId = $_REQUEST['protocoloCuestionarioId'];
        $protocoloCuestionario = $this->getDoctrine()->getRepository('App\Entity\ProtocoloCuestionario')->find($protocoloCuestionarioId);

        $data = array(
            'id' => $protocoloCuestionario->getId(),
            'cuestionario' => $protocoloCuestionario->getCuestionario()->getId()
        );

        return new JsonResponse($data);
    }

    public function deleteProtocoloCuestionario(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $protocoloCuestionarioId = $_REQUEST['protocoloCuestionarioId'];
        $protocoloCuestionario = $this->getDoctrine()->getRepository('App\Entity\ProtocoloCuestionario')->find($protocoloCuestionarioId);
        $protocoloCuestionario->setAnulado(true);
        $em->persist($protocoloCuestionario);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function recuperaPreguntasCuestionario(Request $request)
    {
        //Buscamos las preguntas del cuestionario
        $protocoloCuestionarioId = $_REQUEST['protocoloCuestionarioId'];
        $protocoloCuestionario = $this->getDoctrine()->getRepository('App\Entity\ProtocoloCuestionario')->find($protocoloCuestionarioId);
        $cuestionarioId = $protocoloCuestionario->getCuestionario()->getId();

        $query = "select b.id, a.grupo, a.orden, b.descripcion as pregunta, c.descripcion as tipo from cuestionario_pregunta a
        inner join pregunta b on a.pregunta_id = b.id
        inner join tipo_respuesta c on b.tipo_respuesta_id = c.id
        where a.anulado = false
        and b.anulado = false
        and a.cuestionario_id = $cuestionarioId
        order by orden asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $listPreguntas = $stmt->fetchAll();

        return new JsonResponse(json_encode($listPreguntas));
    }

    public function createPuestoTrabajoProtocolo(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getPuestoTrabajoProtocoloSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $puestoTrabajoProtocolo = new PuestoTrabajoProtocolo();

        $form = $this->createForm(PuestoTrabajoProtocoloType::class, $puestoTrabajoProtocolo, array('protocolos' => null));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $puestoTrabajoProtocolo = $form->getData();
            $em->persist($puestoTrabajoProtocolo);
            $em->flush();

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('puesto_trabajo_protocolo_cuestionario_puesto_trabajo_protocolo_update', array('id' => $puestoTrabajoProtocolo->getId()));
        }

        return $this->render( 'puestotrabajoprotocolocuestionario/puestoTrabajoProtocolo.html.twig', array('form' => $form->createView(), 'empresaNombre' => null, 'puestoTrabajoNombre' => null));
    }

    public function updatePuestoTrabajoProtocolo(Request $request, $empresaId, $puestoTrabajoId, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getPuestoTrabajoProtocoloSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $protocoloRepo = $this->getDoctrine()->getRepository('App\Entity\Protocolo');

        //Buscamos los protocolos del puesto de trabajo
        $empresa = $this->getDoctrine()->getRepository('App\Entity\Empresa')->find($empresaId);
        $puestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoCentro')->find($puestoTrabajoId);
        $protocolos = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoProtocolo')->findBy(array('anulado' => false, 'puestoTrabajo' => $puestoTrabajo, 'empresa' => $empresa));

        $protocolosSelect = array();
        if (count($protocolos) > 0){
            foreach ($protocolos as $p){
                $protocolo = $p->getProtocolo();
                if(!is_null($protocolo)){
                    array_push($protocolosSelect, $protocolo);
                }
            }
        }

        $form = $this->createForm(PuestoTrabajoProtocoloType::class, null, array('protocolos' => $protocolosSelect, 'actualizadoSn' => $puestoTrabajo->getActualizado()));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $actualizado = $form["actualizado"]->getData();

            $puestoTrabajo->setActualizado($actualizado);
            $em->persist($puestoTrabajo);
            $em->flush();

            //Eliminamos los protocolos existentes
            foreach ($protocolos as $ptDelete) {
                $em->remove($ptDelete);
                $em->flush();
            }

            //Los volvemos a generar
            $protocolo_checked = $form["protocolo"]->getData();
            if (!is_null($protocolo_checked)) {
                foreach ($protocolo_checked as $pt) {
                    $protocoloObj = $protocoloRepo->find($pt);
                    $ptNew = new PuestoTrabajoProtocolo();
                    $ptNew->setEmpresa($empresa);
                    $ptNew->setProtocolo($protocoloObj);
                    $ptNew->setPuestoTrabajo($puestoTrabajo);
                    $ptNew->setAnulado(false);
                    $em->persist($ptNew);
                    $em->flush();
                }
            }

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('puesto_trabajo_protocolo_cuestionario_puesto_trabajo_protocolo_update', array('empresaId' => $empresaId, 'puestoTrabajoId' => $puestoTrabajoId));
        }

        return $this->render( 'puestotrabajoprotocolocuestionario/puestoTrabajoProtocolo.html.twig', array('form' => $form->createView(), 'empresaNombre' => $empresa->getEmpresa(), 'puestoTrabajoNombre' => $puestoTrabajo->getDescripcion()));
    }

    public function deletePuestoTrabajoProtocolo(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $puestoTrabajoProtocoloId = $_REQUEST['puestoTrabajoProtocoloId'];
        $puestoTrabajoProtocolo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoProtocolo')->find($puestoTrabajoProtocoloId);
        $puestoTrabajoProtocolo->setAnulado(true);
        $em->persist($puestoTrabajoProtocolo);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function buscaPuestoTrabajosEmpresa(Request $request){

        $empresaId = $_REQUEST['empresaId'];
        $query = "select id, descripcion from puesto_trabajo_centro 
            where anulado = false
            and empresa_id = $empresaId
            order by descripcion asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $puestoTrabajoEmpresa = $stmt->fetchAll();

        return new JsonResponse(json_encode($puestoTrabajoEmpresa));
    }

}