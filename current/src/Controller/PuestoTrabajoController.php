<?php

namespace App\Controller;

use App\Entity\Centro;
use App\Entity\PuestoTrabajoCentro;
use App\Entity\PuestoTrabajoMaquinaEmpresa;
use App\Entity\PuestoTrabajoMaquinaGenerica;
use App\Entity\ZonaTrabajo;
use App\Entity\ZonaTrabajoMaquinaEmpresa;
use App\Entity\ZonaTrabajoMaquinaGenerica;
use App\Form\CentroType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Logger;
use Symfony\Contracts\Translation\TranslatorInterface;

class PuestoTrabajoController extends AbstractController
{

    public function showPuestosTrabajo(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getPuestoTrabajoSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, a.empresa from empresa a
            inner join tecnico_empresa b on a.id = b.empresa_id 
            inner join tecnico c on b.tecnico_id = c.id
            where a.anulado = false ";

        if($id == 30){
            $query .= " and b.tecnico_id = 37";
        }

        if($id == 31){
            $query .= " and b.tecnico_id = 10";
        }

        $query .= " order by a.empresa asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $empresas = $stmt->fetchAll();

        $object = array("json"=>$username, "entidad"=>"puestos de trabajo", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('puestoTrabajo/show.html.twig', array('empresas' => $empresas) );
    }

    public function editPuestosTrabajo(Request $request, $empresaId){

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getPuestoTrabajoSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $empresa = $this->getDoctrine()->getRepository('App\Entity\Empresa')->find($empresaId);
        $session->set('empresa', $empresa);

        //Recuperamos las zonas del centro
        $zonas = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajo')->findBy(array('anulado' => false, 'empresa' => $empresaId));

        //Recuperamos las zonas de trabajo
        $query = "select id, descripcion from zona_trabajo where empresa_id = $empresaId and anulado = false";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $zonasTrabajo = $stmt->fetchAll();

        $data = array();

        $row = array();
        $row['id'] = $empresaId.' '.$empresa->getCodigo();
        $row['parent'] = "#";
        $row['text'] = $empresa->getEmpresa();
        $row['icon'] = "icon-home";
        array_push($data, $row);

        foreach ($zonasTrabajo as $zonaTrabajo) {
            $row = array();
            $row['id'] = $zonaTrabajo['id'] . "";
            $row['parent'] = $empresaId.' '.$empresa->getCodigo();
            $row['text'] = $zonaTrabajo['descripcion'];
            $row['icon'] = "icon-pin";
            array_push($data, $row);
        }

        //Recuperamos los puestos de trabajo
        $query = "select id, descripcion, zona_trabajo_id from puesto_trabajo_centro where empresa_id = $empresaId and anulado = false";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $puestosTrabajo = $stmt->fetchAll();

        foreach ($puestosTrabajo as $puestoTrabajo) {
            $row = array();
            $row['id'] = "puestoTrabajoId" . $puestoTrabajo['id'];
            if (is_null($puestoTrabajo['zona_trabajo_id'])) {
                $row['parent'] = $empresaId.' '.$empresa->getCodigo();
            } else {
                $row['parent'] = $puestoTrabajo['zona_trabajo_id'] . "";
            }
            $row['text'] = $puestoTrabajo['descripcion'];
            $row['icon'] = "icon-man";
            array_push($data, $row);
        }

        $data = \json_encode($data);

        //Recuperamos las maquinas genericas de los puestos de trabajo
        $maquinasGenericasPuestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoMaquinaGenerica')->findBy(array('anulado' => false, 'empresa' => $empresa));

        //Recuperamos las maquinas empresa de los puestos de trabajo
        $maquinasEmpresaPuestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoMaquinaEmpresa')->findBy(array('anulado' => false, 'empresa' => $empresa));

        //Recuperamos las maquinas genericas de los puestos de trabajo
        $maquinasGenericasZonaTrabajo = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajoMaquinaGenerica')->findBy(array('anulado' => false, 'empresa' => $empresa));

        //Recuperamos las maquinas empresa de los puestos de trabajo
        $maquinasEmpresaZonaTrabajo = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajoMaquinaEmpresa')->findBy(array('anulado' => false, 'empresa' => $empresa));

        //Buscamos los puestos de trabajo del centro
        $listPuestosTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoCentro')->findBy(array('anulado' => false, 'empresa' => $empresa));

        //Buscamos las zonas de trabajo del centro
        $listZonasTrabajo = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajo')->findBy(array('anulado' => false, 'empresa' => $empresa), array('descripcion' => 'ASC'));

        //Buscamos las maquinas genericas
        $listMaquinasGenericas = $this->getDoctrine()->getRepository('App\Entity\MaquinaGenerica')->findBy(array('anulado' => false), array('descripcion' => 'ASC'));

        //Buscamos las maquinas del centro
        $listMaquinasEmpresa = $this->getDoctrine()->getRepository('App\Entity\MaquinaEmpresa')->findBy(array('anulado' => false, 'empresa' => $empresa), array('descripcion' => 'ASC'));

        //Buscamos los puesto de trabajo genericos
        $listPuestosGenericos = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoGenerico')->findBy(array(), array('descripcion' => 'ASC'));

        return $this->render('puestoTrabajo/edit.html.twig', array('tree' => $data, 'empresa' => $empresa, 'zonasTrabajo' => $zonas, 'maquinasGenericasPuestoTrabajo' => $maquinasGenericasPuestoTrabajo, 'maquinasGenericasZonaTrabajo' => $maquinasGenericasZonaTrabajo, 'listPuestosTrabajo' => $listPuestosTrabajo, 'listMaquinasGenericas' => $listMaquinasGenericas, 'listZonasTrabajo' => $listZonasTrabajo, 'maquinasEmpresaPuestoTrabajo' => $maquinasEmpresaPuestoTrabajo, 'maquinasEmpresaZonaTrabajo' => $maquinasEmpresaZonaTrabajo, 'listMaquinasEmpresa' => $listMaquinasEmpresa, 'listPuestosGenericos' => $listPuestosGenericos));
    }

    public function addZonaTrabajo(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $empresaId = $_REQUEST['empresaId'];
        $descripcion = $_REQUEST['descripcion'];

        $empresa = $this->getDoctrine()->getRepository('App\Entity\Empresa')->find($empresaId);

        $newZonaTrabajo = new ZonaTrabajo();
        $newZonaTrabajo->setAnulado(false);
        $newZonaTrabajo->setEmpresa($empresa);
        $newZonaTrabajo->setDescripcion($descripcion);

        $em->persist($newZonaTrabajo);
        $em->flush();

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function recuperateZonaTrabajo(Request $request){

        $zonaId = $_REQUEST['zonaId'];
        $zona = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajo')->find($zonaId);

        $data = array(
            'id' => $zona->getId(),
            'descripcion' => $zona->getDescripcion()
        );

        return new JsonResponse($data);
    }

    public function updateZonaTrabajo(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $zonaId = $_REQUEST['zonaId'];
        $descripcion = $_REQUEST['descripcion'];
        $zona = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajo')->find($zonaId);

        $zona->setDescripcion($descripcion);
        $em->persist($zona);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        $traduccion = $translator->trans('TRANS_UPDATE_OK');
        $this->addFlash('success', $traduccion);

        return new JsonResponse($data);
    }

    public function deleteZonaTrabajo(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $zonaId = $_REQUEST['zonaId'];
        $zona = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajo')->find($zonaId);

        $puestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoCentro')->findBy(array('zonaTrabajo' => $zona, 'anulado' => false));

        foreach ($puestoTrabajo as $pt){
            $pt->setAnulado(true);
            $em->persist($pt);
            $em->flush();
        }

        $zona->setAnulado(true);
        $em->persist($zona);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        return new JsonResponse($data);
    }

    public function addPuestoTrabajo(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $zonaId = "";
        $empresaId = $_REQUEST['empresaId'];
        $nombre = $_REQUEST['nombre'];
        $codigo = $_REQUEST['codigo'];
        //Peticio 28/07/2023
        //$zonaId = $_REQUEST['zonaId'];
        $observaciones = $_REQUEST['observaciones'];
        $puestoTrabajoGenericoId = $_REQUEST['puestoTrabajoGenerico'];

        $empresa = $this->getDoctrine()->getRepository('App\Entity\Empresa')->find($empresaId);

        $newPuestoTrabajo = new PuestoTrabajoCentro();
        $newPuestoTrabajo->setEmpresa($empresa);
        $newPuestoTrabajo->setAnulado(false);
        $newPuestoTrabajo->setDescripcion($nombre);
        $newPuestoTrabajo->setCodigo($codigo);
        $newPuestoTrabajo->setObservaciones($observaciones);

        if($zonaId != ""){
            $zona = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajo')->find($zonaId);
            $newPuestoTrabajo->setZonaTrabajo($zona);
        }

        if($puestoTrabajoGenericoId != ""){
            $puestoTrabajoGenerico = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoGenerico')->find($puestoTrabajoGenericoId);
            $newPuestoTrabajo->setPuestoTrabajoGenerico($puestoTrabajoGenerico);
        }

        $em->persist($newPuestoTrabajo);
        $em->flush();

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function recuperatePuestoTrabajo(Request $request){

        $puestoTrabajoId = $_REQUEST['puestoTrabajoId'];
        $puestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoCentro')->find($puestoTrabajoId);

        $zonaId = null;
        if(!is_null($puestoTrabajo->getZonaTrabajo())){
            $zonaId = $puestoTrabajo->getZonaTrabajo()->getId();
        }

        $puestoTrabajoGenericoId = null;
        if(!is_null($puestoTrabajo->getPuestoTrabajoGenerico())){
            $puestoTrabajoGenericoId = $puestoTrabajo->getPuestoTrabajoGenerico()->getId();
        }

        $data = array(
            'id' => $puestoTrabajo->getId(),
            'descripcion' => $puestoTrabajo->getDescripcion(),
            'observaciones' => $puestoTrabajo->getObservaciones(),
            'codigo' => $puestoTrabajo->getCodigo(),
            'zonaId' => $zonaId,
            'puestoTrabajoGenericoId' => $puestoTrabajoGenericoId
        );

        return new JsonResponse($data);
    }

    public function updatePuestoTrabajo(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $puestoTrabajoId = $_REQUEST['puestoTrabajoId'];
        $zonaId = $_REQUEST['zonaId'];
        $descripcion = $_REQUEST['descripcion'];
        $codigo = $_REQUEST['codigo'];
        $observaciones = $_REQUEST['observaciones'];
        $puestoTrabajoGenericoId = $_REQUEST['puestoTrabajoGenerico'];

        $puestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoCentro')->find($puestoTrabajoId);

        $puestoTrabajo->setDescripcion($descripcion);
        $puestoTrabajo->setCodigo($codigo);
        $puestoTrabajo->setObservaciones($observaciones);

        if($zonaId != ""){
            $zona = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajo')->find($zonaId);
            $puestoTrabajo->setZonaTrabajo($zona);
        }

        if($puestoTrabajoGenericoId != ""){
            $puestoTrabajoGenerico = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoGenerico')->find($puestoTrabajoGenericoId);
            $puestoTrabajo->setPuestoTrabajoGenerico($puestoTrabajoGenerico);
        }

        $em->persist($puestoTrabajo);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        $traduccion = $translator->trans('TRANS_UPDATE_OK');
        $this->addFlash('success', $traduccion);

        return new JsonResponse($data);
    }

    public function deletePuestoTrabajo(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $puestoTrabajoId = $_REQUEST['puestoTrabajoId'];
        $puestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoCentro')->find($puestoTrabajoId);

        $puestoTrabajo->setAnulado(true);
        $em->persist($puestoTrabajo);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        return new JsonResponse($data);
    }

    public function addMaquinaGenerica(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $tipo = $_REQUEST['tipo'];

        $empresaId = $_REQUEST['empresaId'];
        $empresa = $this->getDoctrine()->getRepository('App\Entity\Empresa')->find($empresaId);

        /*$centroId = $_REQUEST['centroId'];
        $centro = $this->getDoctrine()->getRepository('App\Entity\Centro')->find($centroId);*/

        switch ($tipo){
            case 1:
                $maquinaGenericaId = $_REQUEST['maquinaGenericaId'];
                $maquinaGenerica = $this->getDoctrine()->getRepository('App\Entity\MaquinaGenerica')->find($maquinaGenericaId);

                $puestoTrabajoId = $_REQUEST['puestoTrabajoId'];
                $puestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoCentro')->find($puestoTrabajoId);

                $maquinaGenericaPuestoTrabajo = new PuestoTrabajoMaquinaGenerica();
                $maquinaGenericaPuestoTrabajo->setPuestoTrabajo($puestoTrabajo);
                $maquinaGenericaPuestoTrabajo->setMaquinaGenerica($maquinaGenerica);
                $maquinaGenericaPuestoTrabajo->setEmpresa($empresa);
                //$maquinaGenericaPuestoTrabajo->setCentro($centro);
                $maquinaGenericaPuestoTrabajo->setAnulado(false);
                $em->persist($maquinaGenericaPuestoTrabajo);
                break;
            case 2:
                $maquinaGenericaId = $_REQUEST['maquinaGenericaId'];
                $maquinaGenerica = $this->getDoctrine()->getRepository('App\Entity\MaquinaGenerica')->find($maquinaGenericaId);

                $zonaTrabajoId = $_REQUEST['zonaTrabajoId'];
                $zonaTrabajo = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajo')->find($zonaTrabajoId);

                $maquinaGenericaZonaTrabajo = new ZonaTrabajoMaquinaGenerica();
                $maquinaGenericaZonaTrabajo->setZonaTrabajo($zonaTrabajo);
                $maquinaGenericaZonaTrabajo->setMaquinaGenerica($maquinaGenerica);
                $maquinaGenericaZonaTrabajo->setEmpresa($empresa);
                //$maquinaGenericaZonaTrabajo->setCentro($centro);
                $maquinaGenericaZonaTrabajo->setAnulado(false);
                $em->persist($maquinaGenericaZonaTrabajo);
                break;
            case 3:
                $maquinaEmpresaId = $_REQUEST['maquinaEmpresaId'];
                $maquinaEmpresa = $this->getDoctrine()->getRepository('App\Entity\MaquinaEmpresa')->find($maquinaEmpresaId);

                $puestoTrabajoId = $_REQUEST['puestoTrabajoId'];
                $puestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoCentro')->find($puestoTrabajoId);

                $maquinaEmpresaPuestoTrabajo = new PuestoTrabajoMaquinaEmpresa();
                $maquinaEmpresaPuestoTrabajo->setPuestoTrabajo($puestoTrabajo);
                $maquinaEmpresaPuestoTrabajo->setMaquinaEmpresa($maquinaEmpresa);
                $maquinaEmpresaPuestoTrabajo->setEmpresa($empresa);
                //$maquinaEmpresaPuestoTrabajo->setCentro($centro);
                $maquinaEmpresaPuestoTrabajo->setAnulado(false);
                $em->persist($maquinaEmpresaPuestoTrabajo);
                break;
            case 4:
                $maquinaEmpresaId = $_REQUEST['maquinaEmpresaId'];
                $maquinaEmpresa = $this->getDoctrine()->getRepository('App\Entity\MaquinaEmpresa')->find($maquinaEmpresaId);

                $zonaTrabajoId = $_REQUEST['zonaTrabajoId'];
                $zonaTrabajo = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajo')->find($zonaTrabajoId);

                $maquinaEmpresaZonaTrabajo = new ZonaTrabajoMaquinaEmpresa();
                $maquinaEmpresaZonaTrabajo->setZonaTrabajo($zonaTrabajo);
                $maquinaEmpresaZonaTrabajo->setMaquinaEmpresa($maquinaEmpresa);
                $maquinaEmpresaZonaTrabajo->setEmpresa($empresa);
                //$maquinaEmpresaZonaTrabajo->setCentro($centro);
                $maquinaEmpresaZonaTrabajo->setAnulado(false);
                $em->persist($maquinaEmpresaZonaTrabajo);
                break;
        }

        $em->flush();

        $data = array();
        array_push($data, "OK");

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success', $traduccion);

        return new JsonResponse($data);
    }

    public function deleteMaquinaGenerica(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $tipo = $_REQUEST['tipo'];

        switch ($tipo){
            case 1:
                $maquinaGenericaPuestoTrabajoId = $_REQUEST['maquinaId'];
                $maquinaGenericaPuestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoMaquinaGenerica')->find($maquinaGenericaPuestoTrabajoId);

                $maquinaGenericaPuestoTrabajo->setAnulado(true);
                $em->persist($maquinaGenericaPuestoTrabajo);
                break;
            case 2:
                $maquinaGenericaZonaTrabajoId = $_REQUEST['maquinaId'];
                $maquinaGenericaZonaTrabajo = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajoMaquinaGenerica')->find($maquinaGenericaZonaTrabajoId);

                $maquinaGenericaZonaTrabajo->setAnulado(true);
                $em->persist($maquinaGenericaZonaTrabajo);
                break;
            case 3:
                $maquinaEmpresaPuestoTrabajoId = $_REQUEST['maquinaId'];
                $maquinaEmpresaPuestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoMaquinaEmpresa')->find($maquinaEmpresaPuestoTrabajoId);

                $maquinaEmpresaPuestoTrabajo->setAnulado(true);
                $em->persist($maquinaEmpresaPuestoTrabajo);
                break;
            case 4:
                $maquinaEmpresaZonaTrabajoId = $_REQUEST['maquinaId'];
                $maquinaEmpresaZonaTrabajo = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajoMaquinaEmpresa')->find($maquinaEmpresaZonaTrabajoId);

                $maquinaEmpresaZonaTrabajo->setAnulado(true);
                $em->persist($maquinaEmpresaZonaTrabajo);
                break;
        }

        $em->flush();

        $data = array();
        array_push($data, "OK");

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        return new JsonResponse($data);
    }
}