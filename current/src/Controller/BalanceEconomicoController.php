<?php

namespace App\Controller;

use App\Entity\BalanceEconomicoEntrada;
use App\Entity\Centro;
use App\Entity\GiroBancario;
use App\Entity\GiroBancarioDevolucion;
use App\Form\BalanceEconomicoEntradaType;
use App\Form\CentroType;
use App\Form\ContratoType;
use App\Form\FacturacionVencimientoType;
use App\Form\GiroBancarioDevolucionType;
use App\Form\GiroBancarioTransferenciaType;
use App\Form\GiroBancarioType;
use App\Form\GiroBancarioEditType;
use App\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class BalanceEconomicoController extends AbstractController
{

	public function showBalanceEconomico(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getBalanceEconomicoSn()){
				return $this->redirectToRoute('error_403');
			}
		}

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

		$empresas = $em->getRepository('App\Entity\Empresa')->findBy(array('anulado' => false));

        $object = array("json"=>$username, "entidad"=>"balance económico", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

		return $this->render('balanceeconomico/show.html.twig', array('empresas' => $empresas));
	}

    public function viewBalanceEconomico(Request $request, TranslatorInterface $translator)
    {

	    $em = $this->getDoctrine()->getManager();

	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getBalanceEconomicoSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

	    $balanceEconomico = null;
	    $empresa = $session->get('empresa');

	    if(!is_null($empresa)){
		    $empresaId = $empresa->getId();
		    $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

		    //Buscamos los servicios contratados
		    $query = "select to_char(a.fecha, 'DD/MM/YYYY') as fechaString, 
				concat('Factura Nº: ',b.serie,'',a.num_fac) as concepto,
				(select sum(importe_total) from facturacion_lineas_pagos where facturacion_id = a.id and facturado = true and anulado = false) as debe,
				null as haber,
				a.fecha as fecha,
				a.id as facturacionId,
				null as giroId,
				1 as tipo,
				null as remesa_id,
				null as girado,
				null as es_factura
				from facturacion a
				left join serie_factura b on a.serie_id  = b.id 
				where a.empresa_id = $empresaId
				and a.anulado = false
				union all
				select to_char(a.fecha , 'DD/MM/YYYY') as fechaString,
				a.concepto,
				null,
				sum(a.importe) as haber,
				a.fecha as fecha,
				null as facturacionId,
				a.id as giroId,
				2 as tipo,
				a.remesa_id,
				a.girado,
				a.es_factura
				from giro_bancario a
				inner join facturacion b on a.facturacion_id = b.id 
				where b.empresa_id = $empresaId
				and a.anulado = false 
				and b.anulado = false
				group by a.fecha, a.concepto, a.id, a.girado, a.es_factura";
		    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
		    $stmt->execute();
		    $balanceEconomico = $stmt->fetchAll();

            $object = array("json"=>$username, "entidad"=>"balance económico: ".$empresa->getEmpresa(), "id"=>$id);
            $logger = new Logger();
            $em = $this->getDoctrine()->getManager();
            $logger->addLog($em, "view", $object, $usuario, TRUE);
            $em->flush();
	    }

        return $this->render('balanceeconomico/view.html.twig',  array('balanceEconomico' => $balanceEconomico));
    }

    public function updateGiroBalanceEconomico(Request $request, $id, TranslatorInterface $translator){

	    $em = $this->getDoctrine()->getManager();

	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getEditGiroBalanceEconomicoSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

	    $giroBancario = $em->getRepository('App\Entity\GiroBancario')->find($id);

	    //Comprobamos si tiene una empresa seleccionada sino le asignamos la del giro
	    $empresa = $session->get('empresa');
	    if(!is_null($empresa)){
		    $empresaId = $empresa->getId();
		    $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
	    }else{
	    	$facturacionId = $giroBancario->getFacturacion()->getId();
		    $facturacion = $em->getRepository('App\Entity\Facturacion')->find($facturacionId);
	    	$empresaId = $facturacion->getEmpresa()->getId();
		    $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
		    $session->set('empresa', $empresa);
	    }

	    if (!$giroBancario) {
		    throw $this->createNotFoundException(
			    'El giro bancario con id ' . $id.' no existe'
		    );
	    }

	    $form = $this->createForm(GiroBancarioEditType::class, $giroBancario, array('empresaId' => $empresaId, 'empresaObj' => $empresa));
	    $form->handleRequest($request);

	    if ($form->isSubmitted()) {
		    $giroBancario = $form->getData();

            //Si han marcado el check de devolucion comprobamos si se ha hecho el registro
            if($giroBancario->getDevolucion()){
                $giroBancarioDevolucionObj = $em->getRepository('App\Entity\GiroBancarioDevolucion')->findBy(array('giroBancario' => $giroBancario, 'anulado' => false));
                if(count($giroBancarioDevolucionObj) == 0){
                    $giroBancarioDevolucion = new GiroBancarioDevolucion();
                    $giroBancarioDevolucion->setFecha(new \DateTime());
                    $giroBancarioDevolucion->setConcepto('Devolución '.$giroBancario->getConcepto());
                    $giroBancarioDevolucion->setFacturacion($giroBancario->getFacturacion());
                    $giroBancarioDevolucion->setGiroBancario($giroBancario);
                    $giroBancarioDevolucion->setImporte($giroBancario->getImporte());
                    $giroBancarioDevolucion->setAnulado(false);
                    $em->persist($giroBancarioDevolucion);
                    $em->flush();
                }
            }

		    $em->persist($giroBancario);
		    $em->flush();

		    $traduccion = $translator->trans('TRANS_UPDATE_OK');
		    $this->addFlash('success', $traduccion);

		    return $this->redirectToRoute('empresa_update', array('id' => $empresaId));
	    }

	    return $this->render('balanceeconomico/edit_giro.html.twig',  array('form' => $form->createView()));
    }

    public function addGiroBalanceEconomico(Request $request, $facturaId, TranslatorInterface $translator){

	    $em = $this->getDoctrine()->getManager();

	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getAddGiroBalanceEconomicoSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

        $factura = $em->getRepository('App\Entity\Facturacion')->find($facturaId);

	    $empresa = $session->get('empresa');
	    $empresaId = $empresa->getId();
	    $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

	    $datosBancarios = $this->getDoctrine()->getRepository('App\Entity\DatosBancarios')->findOneBy(array('empresa' => $empresa, 'principal' => true, 'anulado' => false));

	    $hoy = new \DateTime();

	    $giroBancario = new GiroBancario();
	    $giroBancario->setFecha($hoy);
	    $giroBancario->setFacturacion($factura);

	    if(!is_null($datosBancarios)){
            $dateVencimiento = $factura->getFecha();
            $diaPago = $datosBancarios->getDiaPago();
            if(!is_null($diaPago)){
                $fechaVencimientoString = $factura->getFecha()->format('Y-m');
                $newFechaVencimientoString = $fechaVencimientoString.'-'.$diaPago;
                $fechaVencimiento = new \DateTime($newFechaVencimientoString);

                $dateVencimientoString = $factura->getFecha()->format('Y-m-d');
                $fechaVencimientoString = $fechaVencimiento->format('Y-m-d');

                $date1 = strtotime($dateVencimientoString);
                $date2 = strtotime($fechaVencimientoString);

                if($date1 < $date2){
                    $dateVencimiento = $fechaVencimiento;
                }
            }
            $giroBancario->setVencimiento($dateVencimiento);
        }

	    $form = $this->createForm(GiroBancarioType::class, $giroBancario, array('empresaId' => $empresaId, 'empresaObj' => $empresa));
	    $form->handleRequest($request);

	    if ($form->isSubmitted()) {
		    $giroBancario = $form->getData();

		    $serieFactura = null;
		    $numFac = null;
		    if(!is_null($giroBancario->getFacturacion())){
			    $numFac = $giroBancario->getFacturacion()->getNumFac();
                $serieFactura = $giroBancario->getFacturacion()->getSerie()->getSerie();
		    }

		    //Comprobamos si ha generado un giro periodico
		    $fechaInicio = $form["giroFechaInicio"]->getData();
		    $veces = intval($form["giroRealizar"]->getData());
		    $cada = intval($form["giroCada"]->getData());
		    $frecuencia = $form["giroFrecuencia"]->getData();
		    $importeFactura = $form["importeFactura"]->getData();

		    if(!is_null($fechaInicio) && !is_null($veces) && !is_null($cada) && !is_null($frecuencia)){

		    	//Buscamos el dia de pago de la empresa
		    	if(!is_null($datosBancarios)){

		    		$diaPago = $datosBancarios->getDiaPago();
		    		if(!is_null($diaPago)){
					    $fechaInicioYear = $fechaInicio->format('Y');
					    $fechaInicioMonth = $fechaInicio->format('m');

					    $fechaInicioPago = new \DateTime($fechaInicioYear.'-'.$fechaInicioMonth.'-'.$diaPago);

					    $fechaInicioString = $fechaInicio->format('Y-m-d');
					    $fechaInicioPagoString = $fechaInicioPago->format('Y-m-d');

					    $date1 = strtotime($fechaInicioString);
					    $date2 = strtotime($fechaInicioPagoString);

					    if($date1 > $date2){
					    	switch ($frecuencia){
							    case 0:
								    $fechaInicio = date_add($fechaInicioPago, date_interval_create_from_date_string("1 months"));
								    break;
							    case 1:
								    $fechaInicio = date_add($fechaInicioPago, date_interval_create_from_date_string("1 months"));
							    	break;
							    case 2:
								    $fechaInicio = date_add($fechaInicioPago, date_interval_create_from_date_string("1 year"));
							    	break;
						    }
					    }else{
					    	$fechaInicio = $fechaInicioPago;
					    }
				    }

		    		//Calculamos el importe por giro
				    $importePorGiro = $importeFactura / $veces;

				    $giroBancario->setFecha($fechaInicio);
				    $giroBancario->setVencimiento($fechaInicio);
		    		$giroBancario->setImporte($importePorGiro);
				    $giroBancario->setGirado(false);
				    $giroBancario->setManual(false);
				    $giroBancario->setDevolucion(false);
				    $giroBancario->setComision(false);
				    $giroBancario->setEsFactura(false);
				    $giroBancario->setPagoConfirmado(false);
				    $giroBancario->setConcepto('Recibo 01 de la factura '.$serieFactura.$numFac);

				    $em->persist($giroBancario);
				    $em->flush();

				    $fechaProximoGiro = $fechaInicio;

		    		for ($i = 1 ; $i<=($veces-1) ; $i++){

		    			$numeroRecibo = $i+1;

		    			//Calculamos la fecha
					    switch ($frecuencia){
						    case 0:
							    $fechaProximoGiro =  date_add($fechaProximoGiro, date_interval_create_from_date_string("$cada days"));
							    break;
						    case 1:
							    $fechaProximoGiro =  date_add($fechaProximoGiro, date_interval_create_from_date_string("$cada months"));
							    break;
						    case 2:
							    $fechaProximoGiro =  date_add($fechaProximoGiro, date_interval_create_from_date_string("$cada year"));
							    break;
					    }

						$proximoGiro = clone $giroBancario;
						$proximoGiro->setFacturacion($giroBancario->getFacturacion());
					    $proximoGiro->setFecha($fechaProximoGiro);
					    $proximoGiro->setVencimiento($fechaProximoGiro);
					    $proximoGiro->setConcepto('Recibo '.$numeroRecibo.' de la factura '.$serieFactura.$numFac);

					    $em->persist($proximoGiro);
					    $em->flush();
				    }

			    }
		    }else{

			    $giroBancario->setGirado(false);
			    $giroBancario->setManual(false);
			    $giroBancario->setDevolucion(false);
			    $giroBancario->setComision(false);
			    $giroBancario->setEsFactura(false);
			    $giroBancario->setPagoConfirmado(false);

			    $giroBancario->setConcepto('Recibo 01 de la factura '.$serieFactura.$numFac);
			    $em->persist($giroBancario);
			    $em->flush();
		    }


		    $traduccion = $translator->trans('TRANS_CREATE_OK');
		    $this->addFlash('success', $traduccion);

		    return $this->redirectToRoute('factura_update', array('id'=>$facturaId));
	    }

	    return $this->render('balanceeconomico/add_giro.html.twig',  array('form' => $form->createView()));
    }

	public function deleteGiroTransferenciaBalanceEconomico(Request $request, TranslatorInterface $translator){

		$em = $this->getDoctrine()->getManager();

		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getDeleteTransferenciaBalanceEconomicoSn() || !$privilegios->getDeleteGiroBalanceEconomicoSn()){
				return $this->redirectToRoute('error_403');
			}
		}

		$giroId = $_REQUEST['giroId'];

    	$giroBancario = $this->getDoctrine()->getRepository('App\Entity\GiroBancario')->find($giroId);
        $giroBancario->setAnulado(true);
		$em->persist($giroBancario);
		$em->flush();

		//Buscamos las devoluciones del giro
        $giroBancarioDevolucion = $this->getDoctrine()->getRepository('App\Entity\GiroBancarioDevolucion')->findBy(array('giroBancario' => $giroBancario, 'anulado' => false));
        foreach ($giroBancarioDevolucion as $gbd){
            $gbd->setAnulado(true);
            $em->persist($gbd);
            $em->flush();
        }

		$traduccion = $translator->trans('TRANS_DELETE_OK');
		$this->addFlash('success', $traduccion);

		$data = array();
		array_push($data, "OK");

		return new JsonResponse($data);
	}

	public function marcarGiroGiradoBalanceEconomico(Request $request, $id){

		$em = $this->getDoctrine()->getManager();
		$session = $request->getSession();
		$empresa = $session->get('empresa');
		$empresaId = $empresa->getId();
		$empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

		$giroBancario = $this->getDoctrine()->getRepository('App\Entity\GiroBancario')->find($id);
		$giroBancario->setGirado(true);
		$em->persist($giroBancario);
		$em->flush();

		return $this->redirectToRoute('empresa_update', array('id'=>$empresaId));

	}

	public function buscaDatosFactura(Request $request){

		$em = $this->getDoctrine()->getManager();
		$facturaId = $_REQUEST['facturaId'];

		$query = "select sum(a.importe) as total, to_char(b.fecha, 'YYYY-MM-DD') as fecha from facturacion_lineas_conceptos a inner join facturacion b on a.facturacion_id = b.id  where facturacion_id = $facturaId and a.anulado = false and b.anulado = false group by b.fecha";
		$stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
		$stmt->execute();
		$importeFactura = $stmt->fetchAll();

        $importe = null;
        $fecha = null;
		if(count($importeFactura) > 0){
		    $importe = $importeFactura[0]['total'];
		    $fecha = $importeFactura[0]['fecha'];
        }

		$data = array(
			'importe' => $importe,
			'fecha'   => $fecha);

		return new JsonResponse($data);

	}

	public function addTransferenciaBalanceEconomico(Request $request, TranslatorInterface $translator){

		$em = $this->getDoctrine()->getManager();

		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getAddTransferenciaBalanceEconomicoSn()){
				return $this->redirectToRoute('error_403');
			}
		}

		$empresa = $session->get('empresa');
		$empresaId = $empresa->getId();
		$empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

		$giroBancario = new GiroBancario();
		$form = $this->createForm(GiroBancarioTransferenciaType::class, $giroBancario, array('empresaId' => $empresaId));
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$giroBancario = $form->getData();

			$numFac = null;
			if(!is_null($giroBancario->getFacturacion())){
				$numFac = $giroBancario->getFacturacion()->getNumFac();
			}

			$giroBancario->setGirado(false);
			$giroBancario->setManual(false);
			$giroBancario->setDevolucion(false);
			$giroBancario->setComision(false);
			$giroBancario->setEsFactura(true);
			$giroBancario->setPagoConfirmado(true);

			$giroBancario->setConcepto('Transferencia Factura Nº: '.$numFac);
			$em->persist($giroBancario);
			$em->flush();

			$traduccion = $translator->trans('TRANS_CREATE_OK');
			$this->addFlash('success', $traduccion);

			return $this->redirectToRoute('empresa_update', array('id'=>$empresaId));
		}

		return $this->render('balanceeconomico/add_transferencia.html.twig',  array('form' => $form->createView(), 'nombreEmpresa' => $empresa->getEmpresa()) );
	}

	public function showGirosRemesados(Request $request){

		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getBalanceEconomicoSn()){
				return $this->redirectToRoute('error_403');
			}
		}

		$query = "select a.id, b.num_fac, a.concepto, c.empresa, a.importe, a.fecha, girado, remesa_id from giro_bancario a
				inner join facturacion b on a.facturacion_id  = b.id
				inner join empresa c on b.empresa_id = c.id
				where a.remesa_id is not null
				and a.anulado = false
				and b.anulado = false 
				and c.anulado = false";
		$stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
		$stmt->execute();
		$giros = $stmt->fetchAll();

		return $this->render('balanceeconomico/show_giros.html.twig', array('giros' => $giros) );
	}

	public function showGirosNoRemesados(Request $request){

		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getBalanceEconomicoSn()){
				return $this->redirectToRoute('error_403');
			}
		}

		$query = "select a.id, b.num_fac, a.concepto, c.empresa, a.importe, a.fecha, girado, remesa_id from giro_bancario a
				inner join facturacion b on a.facturacion_id  = b.id
				inner join empresa c on b.empresa_id = c.id
				where a.remesa_id is null
				and a.anulado = false
				and b.anulado = false 
				and c.anulado = false";
		$stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
		$stmt->execute();
		$giros = $stmt->fetchAll();

		return $this->render('balanceeconomico/show_giros.html.twig', array('giros' => $giros) );
	}

	public function showGirosGirados(Request $request){

		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getBalanceEconomicoSn()){
				return $this->redirectToRoute('error_403');
			}
		}

		$query = "select a.id, b.num_fac, a.concepto, c.empresa, a.importe, a.fecha, girado, remesa_id from giro_bancario a
				inner join facturacion b on a.facturacion_id  = b.id
				inner join empresa c on b.empresa_id = c.id
				where a.girado = true
				and a.anulado = false
				and b.anulado = false 
				and c.anulado = false";
		$stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
		$stmt->execute();
		$giros = $stmt->fetchAll();

		return $this->render('balanceeconomico/show_giros.html.twig', array('giros' => $giros) );
	}

	public function showGirosNoGirados(Request $request){

		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getBalanceEconomicoSn()){
				return $this->redirectToRoute('error_403');
			}
		}

		$query = "select a.id, b.num_fac, a.concepto, c.empresa, a.importe, a.fecha, girado, remesa_id from giro_bancario a
				inner join facturacion b on a.facturacion_id  = b.id
				inner join empresa c on b.empresa_id = c.id
				where a.girado = false
				and a.anulado = false
				and b.anulado = false 
				and c.anulado = false";
		$stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
		$stmt->execute();
		$giros = $stmt->fetchAll();

		return $this->render('balanceeconomico/show_giros.html.twig', array('giros' => $giros) );
	}

    public function updateGiroBancarioDevolucion(Request $request, $id, TranslatorInterface $translator){

        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getEditGiroBalanceEconomicoSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $giroBancarioDevolucion = $em->getRepository('App\Entity\GiroBancarioDevolucion')->find($id);

        //Comprobamos si tiene una empresa seleccionada sino le asignamos la del giro
        $facturacionId = $giroBancarioDevolucion->getFacturacion()->getId();
        $facturacion = $em->getRepository('App\Entity\Facturacion')->find($facturacionId);
        $empresaId = $facturacion->getEmpresa()->getId();
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
        $session->set('empresa', $empresa);

        if (!$giroBancarioDevolucion) {
            throw $this->createNotFoundException(
                'El giro bancario con id ' . $id.' no existe'
            );
        }

        $form = $this->createForm(GiroBancarioDevolucionType::class, $giroBancarioDevolucion, array('empresaId' => $empresaId, 'empresaObj' => $empresa));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $giroBancarioDevolucion = $form->getData();
            $em->persist($giroBancarioDevolucion);
            $em->flush();

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('empresa_update', array('id' => $empresaId));
        }

        return $this->render('balanceeconomico/edit_devolucion.html.twig',  array('form' => $form->createView()));

    }

    public function deleteGiroBancarioDevolucion(Request $request, TranslatorInterface $translator){

        $em = $this->getDoctrine()->getManager();

        $giroBancarioDevolucionId = $_REQUEST['giroBancarioDevolucionId'];
        $giroBancarioDevolucion = $this->getDoctrine()->getRepository('App\Entity\GiroBancarioDevolucion')->find($giroBancarioDevolucionId);
        $giroBancarioDevolucion->setAnulado(true);

        $giroBancario = $giroBancarioDevolucion->getGiroBancario();
        $giroBancario->setDevolucion(false);

        $em->persist($giroBancarioDevolucion);
        $em->persist($giroBancario);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function addEntradaBalance(Request $request, TranslatorInterface $translator){

        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getAddTransferenciaBalanceEconomicoSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        $balanceEconomicoEntrada = new BalanceEconomicoEntrada();
        $balanceEconomicoEntrada->setEmpresa($empresa);
        $balanceEconomicoEntrada->setFecha(new \DateTime());

        $form = $this->createForm(BalanceEconomicoEntradaType::class, $balanceEconomicoEntrada, array('empresaId' => $empresaId));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $balanceEconomicoEntrada = $form->getData();
            $em->persist($balanceEconomicoEntrada);
            $em->flush();

            //Comprobamos si la forma de pago es GIRO BANCARIO
            $formaPago = $balanceEconomicoEntrada->getFormaPago();
            if(!is_null($formaPago)){
                if($formaPago->getFormaPagoContable() == 8){
                    $newGiro = new GiroBancario();
                    $newGiro->setFecha($balanceEconomicoEntrada->getFecha());
                    $newGiro->setVencimiento($balanceEconomicoEntrada->getFecha());
                    $newGiro->setConcepto($balanceEconomicoEntrada->getConcepto());
                    $newGiro->setFacturacion($balanceEconomicoEntrada->getFacturacion());
                    $newGiro->setImporte($balanceEconomicoEntrada->getImporte());
                    $datosBancarios = $em->getRepository('App\Entity\DatosBancarios')->findOneBy(array('anulado' => false, 'principal' => true, 'empresa' => $empresa));
                    $newGiro->setCuenta($datosBancarios);
                    $em->persist($newGiro);
                    $em->flush();
                }
            }

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('empresa_update', array('id'=>$empresaId));
        }

        return $this->render('balanceeconomico/entrada.html.twig',  array('form' => $form->createView()));
    }

    public function updateEntradaBalance(Request $request, $id, TranslatorInterface $translator){

        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getAddTransferenciaBalanceEconomicoSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();

        $balanceEconomicoEntrada = $em->getRepository('App\Entity\BalanceEconomicoEntrada')->find($id);

        $form = $this->createForm(BalanceEconomicoEntradaType::class, $balanceEconomicoEntrada, array('empresaId' => $empresaId));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $balanceEconomicoEntrada = $form->getData();
            $em->persist($balanceEconomicoEntrada);
            $em->flush();

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('empresa_update', array('id'=>$empresaId));
        }

        return $this->render('balanceeconomico/entrada.html.twig',  array('form' => $form->createView()));
    }

    public function deleteEntradaBalanceEconomico(Request $request, TranslatorInterface $translator){

        $em = $this->getDoctrine()->getManager();

        $entradaBalanceEconomicoId = $_REQUEST['entradaBalanceEconomicoId'];
        $entradaBalanceEconomico = $this->getDoctrine()->getRepository('App\Entity\BalanceEconomicoEntrada')->find($entradaBalanceEconomicoId);
        $entradaBalanceEconomico->setAnulado(true);
        $em->persist($entradaBalanceEconomico);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function updateFacturaVencimiento(Request $request, $id, TranslatorInterface $translator){

        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getEditFacturacionSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $facturacionVencimiento = $em->getRepository('App\Entity\FacturacionVencimiento')->find($id);

        $empresaId = null;
        $empresa = null;
        if(!is_null($facturacionVencimiento->getFacturaAsociada())){
            $empresaId = $facturacionVencimiento->getFacturaAsociada()->getEmpresa()->getId();
            $empresa = $facturacionVencimiento->getFacturaAsociada()->getEmpresa();
        }

        $form = $this->createForm(FacturacionVencimientoType::class, $facturacionVencimiento, array('empresaId' => $empresaId, 'empresaObj' => $empresa));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $facturacionVencimiento = $form->getData();

            $em->persist($facturacionVencimiento);
            $em->flush();

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('empresa_update', array('id' => $empresaId));
        }

        return $this->render('balanceeconomico/edit_facturacion_vencimiento.html.twig',  array('form' => $form->createView()));

    }

}