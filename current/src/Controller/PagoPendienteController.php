<?php

namespace App\Controller;

use App\Entity\ContratoPago;
use App\Entity\Empresa;
use App\Entity\Facturacion;
use App\Form\FacturacionType;
use App\Form\PagoPendienteType;
use App\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class PagoPendienteController extends AbstractController
{
    public function createPagoPendiente(Request $request, TranslatorInterface $translator)
    {

	    $em = $this->getDoctrine()->getManager();

	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getAddPagoPendienteSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

	    $arrayEmpresaId = array();
	    $empresa = $session->get('empresa');
	    if(!is_null($empresa)){
		    $empresaId = $empresa->getId();
		    $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
		    array_push($arrayEmpresaId, $empresaId);
	    }else{
		    $empresas = $em->getRepository('App\Entity\Empresa')->findBy(array('anulado' => false));
		    foreach ($empresas as $e){
			    array_push($arrayEmpresaId, $e->getId());
		    }
	    }

        $pago = new ContratoPago();
        $form = $this->createForm(PagoPendienteType::class, $pago, array('empresaId' => $arrayEmpresaId, 'empresaObj' => $empresa, 'contratoObj' => null));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
	        $pago = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($pago);
            $em->flush();

	        $traduccion = $translator->trans('TRANS_CREATE_OK');
	        $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('pago_pendiente_show');

        }

        return $this->render( 'pagopendiente/edit.html.twig', array('form' => $form->createView()));
    }

    public function showPagosPendientes(Request $request)
    {
	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getPagoPendienteSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

	    $pagos = $this->buscaPagosPendientes("", "", 1, false);

        $object = array("json"=>$username, "entidad"=>"pagos pendientes", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('pagopendiente/show.html.twig', array('pagos' => $pagos));
    }

    public function showPagosPorFacturar(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getPagoPendienteSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $pagos = $this->buscaPagosPendientes("", "", 1, true);

        $object = array("json"=>$username, "entidad"=>"pagos pendientes", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('pagopendiente/show_facturar.html.twig', array('pagos' => $pagos));
    }

	public function showPagosPendientesPendientesDashboard(Request $request)
	{
		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getPagoPendienteSn()){
				return $this->redirectToRoute('error_403');
			}
		}

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $pagos = $this->buscaPagosPendientes("", "", 1, false);

        $object = array("json"=>$username, "entidad"=>"pagos pendientes dashboard", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

		return $this->render('pagopendiente/show.html.twig', array('pagos' => $pagos));
	}

    public function filtroPagosPendientesPendientes(Request $request)
    {
        $ini = $_REQUEST['ini'];
        $fin = $_REQUEST['fin'];
        $tipo = $_REQUEST['tipo'];
        $pagosFacturarSn = $_REQUEST['pagosFacturarSn'];

        if($pagosFacturarSn == "true"){
            $pagosFacturarSn = true;
        }else{
            $pagosFacturarSn = false;
        }

        $pagos = $this->buscaPagosPendientes($ini, $fin, $tipo, $pagosFacturarSn);

        return new JsonResponse(json_encode($pagos));
    }

	public function showPagosPendientesPagadosDashboard(Request $request)
	{
		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getPagoPendienteSn()){
				return $this->redirectToRoute('error_403');
			}
		}

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $pagos = $this->buscaPagosPendientes("", "",  2, false);

        $object = array("json"=>$username, "entidad"=>"pagos pendientes pagados dashboard", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

		return $this->render('pagopendiente/show.html.twig', array('pagos' => $pagos));
	}

    public function deletePagoPendiente($id, Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getDeletePagoPendienteSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

	    $pago = $em->getRepository('App\Entity\ContratoPago')->find($id);

        if (!$pago) {
	        throw $this->createNotFoundException(
		        'El pago con id ' . $id.' no existe'
	        );
        }

	    $pago->setAnulado(true);
	    $em->persist($pago);
	    $em->flush();

	    $traduccion = $translator->trans('TRANS_DELETE_OK');
	    $this->addFlash('success', $traduccion);
        return $this->redirectToRoute('pago_pendiente_show');
    }

    public function updatePagoPendiente(Request $request, $id, TranslatorInterface $translator)
    {
	    $em = $this->getDoctrine()->getManager();

	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getEditPagoPendienteSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

	    $pago = $em->getRepository('App\Entity\ContratoPago')->find($id);

	    $arrayEmpresaId = array();
	    $contrato = $pago->getContrato();
	    $empresa = null;
        $empresaId = null;
	    if(!is_null($contrato->getEmpresa())){
            $empresa = $contrato->getEmpresa();
            $empresaId = $pago->getContrato()->getEmpresa()->getId();
            $session->set('empresa', $empresa);
            array_push($arrayEmpresaId, $empresaId);
        }

        if (!$pago) {
            throw $this->createNotFoundException(
                'El pago con id ' . $id.' no existe'
            );
        }

        $form = $this->createForm(PagoPendienteType::class, $pago, array('empresaId' => $arrayEmpresaId, 'empresaObj' => $empresa, 'contratoObj' => $pago->getContrato()));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
	        $pago = $form->getData();
            $em->persist($pago);
            $em->flush();

	        $traduccion = $translator->trans('TRANS_UPDATE_OK');
	        $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('pago_pendiente_show');
        }

        return $this->render('pagopendiente/edit.html.twig',  array('form' => $form->createView()));
    }

    public function showPagosFacturadosMultiple(Request $request){

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getFacturarPagoPendienteSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $arrayPagosFacturados = $session->get('pagosFacturados');
        $arrayPagosFacturadosString = "";
        foreach ($arrayPagosFacturados as $apf){
            $arrayPagosFacturadosString .= $apf.',';
        }
        $arrayPagosFacturadosString = rtrim($arrayPagosFacturadosString, ",");

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, concat(c.serie,a.num_fac) as num_fac, to_char(a.fecha, 'DD/MM/YYYY') as fecha, to_char(a.fecha, 'YYYYMMDDHHmm') as fechatimestamp, to_char(a.fecha, 'MM') as mes,
        anyo as any, a.codigo, d.importe_total, b.empresa
        from facturacion a 
        inner join empresa b on a.empresa_id = b.id
        inner join serie_factura c on a.serie_id = c.id
        inner join facturacion_lineas_pagos d on a.id = d.facturacion_id
        where a.anulado = false 
        and a.id in ($arrayPagosFacturadosString) 
        group by a.id, a.num_fac, a.fecha, b.empresa, c.serie, d.importe_total
        order by fecha desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $facturas = $stmt->fetchAll();

        $object = array("json"=>$username, "entidad"=>"pagos facturados", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('pagopendiente/show_facturados.html.twig', array('facturas' => $facturas));
    }

    public function showVencimientosGeneradosMultiple(Request $request){

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getFacturarPagoPendienteSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $arrayVencimientosGenerados = $session->get('vencimientosGenerados');
        $arrayVencimientosGeneradosString = "";
        foreach ($arrayVencimientosGenerados as $avg){
            $arrayVencimientosGeneradosString .= $avg.',';
        }
        $arrayVencimientosGeneradosString = rtrim($arrayVencimientosGeneradosString, ",");

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, concat(c.serie,a.num_fac) as num_fac, to_char(a.fecha, 'DD/MM/YYYY') as fecha, to_char(a.fecha, 'YYYYMMDDHHmm') as fechatimestamp, to_char(a.fecha, 'MM') as mes,
        anyo as any, a.codigo, d.importe_total, b.empresa
        from facturacion a 
        inner join empresa b on a.empresa_id = b.id
        inner join serie_factura c on a.serie_id = c.id
        inner join facturacion_lineas_pagos d on a.id = d.facturacion_id
        where a.anulado = false 
        and a.id in ($arrayVencimientosGeneradosString) 
        group by a.id, a.num_fac, a.fecha, b.empresa, c.serie, d.importe_total
        order by fecha desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $facturas = $stmt->fetchAll();

        $object = array("json"=>$username, "entidad"=>"vencimientos generados pagos", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('pagopendiente/show_vencimientos_generados.html.twig', array('facturas' => $facturas));
    }

    function buscaPagosPendientes($ini, $fin, $tipo, $pagosFacturarSn){
        $query = "select distinct b.id, a.fechainicio, to_char(b.vencimiento, 'DD/MM/YYYY') as vencimiento, b.vencimiento as vencimientodate, 
            b.importe_sin_iva, b.importe_iva, b.importe_total, b.facturado, c.empresa, calculasaldo(c.id) as saldo, string_agg(e.nombre::text, ' , '::text) AS tecnico, f.descripcion as comercial, g.nombre as colaborador from contrato a
            inner join contrato_pago b on a.id = b.contrato_id 
            inner join empresa c on a.empresa_id = c.id
            left join tecnico_empresa d on c.id = d.empresa_id 
            left join tecnico e on d.tecnico_id = e.id
            left join comercial f on c.agente_id = f.id
            left join asesoria g on c.colaborador_id = g.id
            where a.anulado = false 
            and b.anulado = false 
            and c.anulado = false
            and a.cancelado = false
            and b.vencimiento is not null ";

        if ($ini != ""){
            $query .= " and b.vencimiento >= '$ini 00:00:00' ";
        }

        if ($fin != ""){
            $query .= " and b.vencimiento <= '$fin 00:00:00' ";
        }

        if ($tipo == 1){
            $query .= " and b.facturado = false ";
        }

        if ($tipo == 2){
            $query .= " and b.facturado = true ";
        }

        $query .= "group by b.id, a.fechainicio, b.vencimiento, b.importe_sin_iva, b.importe_iva, b.importe_total, b.facturado, c.empresa, c.id, e.nombre, f.descripcion, g.nombre";

        if(!$pagosFacturarSn){

            $query = "select distinct b.id, a.fechainicio, to_char(b.vencimiento, 'DD/MM/YYYY') as vencimiento, b.vencimiento as vencimientodate, 
            b.importe_sin_iva, b.importe_iva, b.importe_total, b.facturado, c.empresa, calculasaldo(c.id) as saldo, string_agg(e.nombre::text, ' , '::text) AS tecnico, f.descripcion as comercial, g.nombre as colaborador,
            concat(i.serie,h.num_fac) as num_fac, j.descripcion as formapago from contrato a
            inner join contrato_pago b on a.id = b.contrato_id 
            inner join empresa c on a.empresa_id = c.id
            left join tecnico_empresa d on c.id = d.empresa_id 
            left join tecnico e on d.tecnico_id = e.id
            left join comercial f on c.agente_id = f.id
            left join asesoria g on c.colaborador_id = g.id
            left join facturacion h on a.id = h.contrato_id
            left join serie_factura i on h.serie_id = i.id
            left join forma_pago j on h.forma_pago_id = j.id
            where a.anulado = false 
            and b.anulado = false 
            and c.anulado = false
            and a.cancelado = false ";

            if ($ini != ""){
                $query .= " and b.vencimiento >= '$ini 00:00:00' ";
            }

            if ($fin != ""){
                $query .= " and b.vencimiento <= '$fin 00:00:00' ";
            }

            if ($tipo == 1){
                $query .= " and b.facturado = false ";
            }

            if ($tipo == 2){
                $query .= " and b.facturado = true ";
            }

            $query .= "group by b.id, a.fechainicio, b.vencimiento, b.importe_sin_iva, b.importe_iva, b.importe_total, b.facturado, c.empresa, c.id, e.nombre, f.descripcion, g.nombre, i.serie, h.num_fac, j.descripcion";

            $query .= " union all ";

            $query .= " select a.id, a.fecha, to_char(a.fecha, 'DD/MM/YYYY') as vencimiento, a.fecha as vencimientodate, a.importe as importe_sin_iva,
        a.importe as importe_iva, a.importe as importe_total, a.confirmado as facturado, c.empresa, calculasaldo(c.id) as saldo, string_agg(e.nombre::text, ' , '::text) AS tecnico, f.descripcion as comercial, g.nombre as colaborador,
        concat(h.serie,b.num_fac) as num_fac, i.descripcion as formapago from facturacion_vencimiento a
        inner join facturacion b on a.factura_asociada_id = b.id
        inner join empresa c on b.empresa_id = c.id
        left join tecnico_empresa d on c.id = d.empresa_id 
        left join tecnico e on d.tecnico_id = e.id
        left join comercial f on c.agente_id = f.id
        left join asesoria g on c.colaborador_id = g.id
        left join serie_factura h on b.serie_id = h.id
        left join forma_pago i on b.forma_pago_id = i.id
        where a.anulado = false 
        and b.anulado = false
        and c.anulado = false ";

            if ($ini != ""){
                $query .= " and a.fecha >= '$ini 00:00:00' ";
            }

            if ($fin != ""){
                $query .= " and a.fecha <= '$fin 00:00:00' ";
            }

            if ($tipo == 1){
                $query .= " and a.confirmado = false ";
            }

            if ($tipo == 2){
                $query .= " and a.confirmado = true ";
            }

            $query .= "group by a.id, a.fecha, a.importe, a.confirmado, c.empresa, c.id, e.nombre, f.descripcion, g.nombre, h.serie, b.num_fac, i.descripcion";

            $query .= " union all ";

            $query .= "select a.id, a.fecha, to_char(a.fecha, 'DD/MM/YYYY') as vencimiento, a.fecha as vencimientodate, j.importe as importe_sin_iva,
            j.importe as importe_iva, j.importe as importe_total, false as facturado, c.empresa, calculasaldo(c.id) as saldo, string_agg(e.nombre::text, ' , '::text) AS tecnico, f.descripcion as comercial, g.nombre as colaborador,
            concat(h.serie,b.num_fac) as num_fac, i.descripcion as formapago from giro_bancario_devolucion a 
            inner join facturacion b on a.facturacion_id = b.id
            inner join empresa c on b.empresa_id = c.id
            left join tecnico_empresa d on c.id = d.empresa_id 
            left join tecnico e on d.tecnico_id = e.id
            left join comercial f on c.agente_id = f.id
            left join asesoria g on c.colaborador_id = g.id
            left join serie_factura h on b.serie_id = h.id
            left join forma_pago i on b.forma_pago_id = i.id
            inner join giro_bancario j on a.giro_bancario_id = j.id
            where a.anulado = false
            and c.anulado = false
            and j.anulado = false
            and a.recibo_generado = false ";

            if ($ini != ""){
                $query .= " and a.fecha >= '$ini 00:00:00' ";
            }

            if ($fin != ""){
                $query .= " and a.fecha <= '$fin 00:00:00' ";
            }

            $query .= "group by a.id, a.fecha, j.importe, c.empresa, c.id, e.nombre, f.descripcion, g.nombre, h.serie, b.num_fac, i.descripcion ";
        }

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $pagos = $stmt->fetchAll();

        return $pagos;
    }

}