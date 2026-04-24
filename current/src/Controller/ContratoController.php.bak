<?php

namespace App\Controller;

use App\Entity\Contrato;
use App\Entity\ContratoPago;
use App\Entity\LogEnvioMail;
use App\Entity\Renovacion;
use App\Entity\ServicioContratado;
use App\Entity\TarifaContrato;
use App\Form\ContratoType;
use App\Form\EnviarCorreoType;
use App\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContratoController extends AbstractController
{
    public function createContrato(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getAddContratoSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $arrayEmpresaId = array();
        $empresa = $session->get('empresa');
        if (!is_null($empresa)) {
            $empresaId = $empresa->getId();
            $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
            array_push($arrayEmpresaId, $empresaId);
        } else {
            $empresas = $em->getRepository('App\Entity\Empresa')->findBy(array('anulado' => false));
            foreach ($empresas as $e) {
                array_push($arrayEmpresaId, $e->getId());
            }
        }
        $contrato = new Contrato();

        // Recuperamos los servicios
        $listServicios = $em->getRepository('App\Entity\ListaServiciosContratados')->findBy(array('anulado' => false));

        // Generamos el numero de contrato
        $hoy = new \DateTime();
        $year = $hoy->format('Y');
        $yearString = substr($year, 2, 4);

        // Calculamos el numero de contrato
        $numeroContrato = $this->calcularNumeroContrato($year);

        $contrato->setFechainicio(new \DateTime());
        $contrato->setContrato($numeroContrato . '/' . $yearString);
        $contrato->setReferencia($numeroContrato . '/' . $yearString);

        $hoyString =  $contrato->getFechainicio()->format('Y-m-d');
        $dateVencimiento = date("Y-m-d", strtotime($hoyString . "- 1 day"));
        $dateVencimiento = date("Y-m-d", strtotime($dateVencimiento . "+ 1 year"));
        $dateVencimiento = new \DateTime($dateVencimiento);
        $contrato->setFechavencimiento($dateVencimiento);

        // Recuperamos los centros de trabajo de la empresa
        $centroTrabajoEmpresaId = array();
        $centroTrabajoEmpresa = $em->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));
        foreach ($centroTrabajoEmpresa as $cte) {
            array_push($centroTrabajoEmpresaId, $cte->getCentro()->getId());
        }
        $form = $this->createForm(ContratoType::class, $contrato, array('empresaId' => $arrayEmpresaId, 'empresaObj' => $empresa, 'centroId' => $centroTrabajoEmpresaId, 'centroObj' => null));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $contrato = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $contrato->setAnyo($year);
            $em->persist($contrato);
            $em->flush();

            $renovacion = new Renovacion();
            $renovacion->setContrato($contrato);
            $renovacion->setFechainicio($contrato->getFechainicio());
            $renovacion->setFechavencimiento($contrato->getFechavencimiento());
            // $renovacion->setRenovado(false);

            if (!is_null($contrato->getTipoContrato())) {
                $tipoVigilanciaSalud = $this->getDoctrine()->getRepository('App\Entity\TipoContrato')->find(1);
                $tipoMixto = $this->getDoctrine()->getRepository('App\Entity\TipoContrato')->find(2);

                $tipoContratoId = $contrato->getTipoContrato()->getId();

                switch ($tipoContratoId) {
                    case 1:
                        $renovacion->setTipoContrato($tipoVigilanciaSalud);
                        break;
                    case 2:
                        $renovacion->setTipoContrato($tipoMixto);
                        break;
                }
            }
            $em->persist($renovacion);
            $em->flush();

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('contrato_update', array('id' => $contrato->getId()));
        }
        return $this->render('contrato/edit.html.twig', array('form' => $form->createView(), 'tarifaContratoReconocimientosServicios' => null, 'tarifaContratoAnaliticas' => null, 'serviciosContratados' => null, 'listServicios' => $listServicios, 'pagosContrato' => null));
    }

    public function viewContrato($id)
    {
        $contrato = $this->getDoctrine()->getRepository('App\Entity\Contrato')->find($id);

        if (!$contrato) {
            throw $this->createNotFoundException(
                'El contrato con id: ' . $id . ' no existe.'
            );
        }
        return $this->render('contrato/view.html.twig', array('contrato' => $contrato));
    }

    public function showContratos(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getContratoSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $contratos = $this->buscaContratos("", "", "", "", "", "");

        // Buscamos las plantillas de la carpeta contratos
        $carpetaContratos = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(5);
        $plantillas = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaContratos, 'anulado' => false));

        // Buscamos los tipos de empresa
        $tipoEmpresa = $this->getDoctrine()->getRepository('App\Entity\CodigoEmpresa')->findAll();

        $object = array("json" => $username, "entidad" => "contratos", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('contrato/show.html.twig', array('contratos' => $contratos, 'listPlantillas' => $plantillas, 'tipoEmpresa' => $tipoEmpresa));
    }

    public function deleteContrato($id, Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getDeleteContratoSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $contrato = $em->getRepository('App\Entity\Contrato')->find($id);

        if (!$contrato) {
            throw $this->createNotFoundException(
                'El contrato con id: ' . $id . ' no existe.'
            );
        }
        $oldContrato = $contrato->getOldContrato();
        if (!is_null($oldContrato)) {
            $oldContrato->setRenovado(false);
            $em->persist($oldContrato);
        }
        $contrato->setCancelado(true);
        $contrato->setRenovado(false);
        //$contrato->setAnulado(true);
        $em->persist($contrato);

        // Eliminamos todas las tarifas del contrato
        //	    $tarifas = $em->getRepository('App\Entity\TarifaContrato')->findBy(array('contrato' => $contrato, 'anulado' => false));
        //
        //	    foreach ($tarifas as $tarifa){
        //		    $tarifa->setAnulado(true);
        //		    $em->persist($tarifa);
        //		    $em->flush();
        //	    }
        //
        //        //Eliminamos las renovaciones que tenga asociadas el contrato
        //	    $renovaciones = $em->getRepository('App\Entity\Renovacion')->findBy(array('contrato' => $contrato, 'anulado' => false));
        //
        //	    foreach ($renovaciones as $renovacion){
        //		    $renovacion->setAnulado(true);
        //		    $em->persist($renovacion);
        //		    $em->flush();
        //	    }
        //
        //	    //Eliminamos todos los servicios contratados
        //	    $serviciosContratados = $em->getRepository('App\Entity\ServicioContratado')->findBy(array('contrato' => $contrato, 'anulado' => false));
        //
        //	    foreach ($serviciosContratados as $servicioContratado){
        //		    $servicioContratado->setAnulado(true);
        //		    $em->persist($servicioContratado);
        //		    $em->flush();
        //	    }
        //
        //	    //Eliminamos todos los pagos del contrato
        //	    $contratoPagos = $em->getRepository('App\Entity\ContratoPago')->findBy(array('contrato' => $contrato, 'anulado' => false));
        //
        //	    foreach ($contratoPagos as $contratoPago){
        //		    $contratoPago->setAnulado(true);
        //		    $em->persist($contratoPago);
        //		    $em->flush();
        //	    }

        // Eliminamos todas las facturas
        //	    $contratoFacturas = $em->getRepository('App\Entity\Facturacion')->findBy(array('contrato' => $contrato, 'anulado' => false));
        //
        //	    foreach ($contratoFacturas as $contratoFactura){
        //
        //	    	//Buscamos las lineas de la factura
        //		    $contratoFacturaLineasPagos = $em->getRepository('App\Entity\FacturacionLineasPagos')->findBy(array('facturacion' => $contratoFactura, 'anulado' => false));
        //
        //		    foreach ($contratoFacturaLineasPagos as $contratoFacturaLineaPago){
        //			    $contratoFacturaLineaPago->setAnulado(true);
        //			    $em->persist($contratoFacturaLineaPago);
        //			    $em->flush();
        //		    }
        //
        //		    $contratoFacturaLineasConceptos = $em->getRepository('App\Entity\FacturacionLineasConceptos')->findBy(array('facturacion' => $contratoFactura, 'anulado' => false));
        //
        //		    foreach ($contratoFacturaLineasConceptos as $contratoFacturaLineaConcepto){
        //			    $contratoFacturaLineaConcepto->setAnulado(true);
        //			    $em->persist($contratoFacturaLineaConcepto);
        //			    $em->flush();
        //		    }
        //
        //		    //Eliminamos todos los giros bancarios
        //		    $girosBancarios = $em->getRepository('App\Entity\GiroBancario')->findBy(array('facturacion' => $contratoFactura, 'anulado' => false));
        //
        //		    foreach ($girosBancarios as $giroBancario){
        //			    $giroBancario->setAnulado(true);
        //			    $em->persist($giroBancario);
        //			    $em->flush();
        //		    }
        //
        //		    $contratoFactura->setAnulado(true);
        //		    $em->persist($contratoFactura);
        //	    }
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        return $this->redirectToRoute('contrato_show');
    }

    public function updateContrato(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditContratoSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $contrato = $em->getRepository('App\Entity\Contrato')->find($id);

        $arrayEmpresaId = array();
        // Comprobamos si tiene una empresa seleccionada sino le asignamos la del contrato
        $empresaId = $contrato->getEmpresa()->getId();
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
        $session->set('empresa', $empresa);

        array_push($arrayEmpresaId, $empresaId);

        if (!$contrato) {
            throw $this->createNotFoundException(
                'El contrato con id: ' . $id . ' no existe.'
            );
        }
        // Buscamos los precios de los reconocimientos o servicios
        $tarifaContratoReconocimientosServicios = $em->getRepository('App\Entity\TarifaContrato')->findBy(array('tipo' => 1, 'contrato' => $contrato), array('descripcion' => 'ASC'));

        // Buscamos los precios de las analiticas
        $tarifaContratoAnaliticas = $em->getRepository('App\Entity\TarifaContrato')->findBy(array('tipo' => 2, 'contrato' => $contrato), array('descripcion' => 'ASC'));

        // Buscamos los servicios contratados
        $query = "select a.id, a.precio, a.precio_renovacion, b.descripcion as servicio, b.tipo_id from servicio_contratado a inner join lista_servicios_contratados b on a.servicio_id = b.id where a.contrato_id = $id and a.anulado = false and b.anulado = false order by b.tipo_id asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $serviciosContratados = $stmt->fetchAll();

        // Recuperamos los servicios
        $listServicios = $em->getRepository('App\Entity\ListaServiciosContratados')->findBy(array('anulado' => false));

        // Buscamos los pagos del contrato
        $pagosContrato = $em->getRepository('App\Entity\ContratoPago')->findBy(array('contrato' => $contrato, 'anulado' => false), array('nPago' => 'ASC'));

        // Recuperamos los centros de trabajo de la empresa
        $centroTrabajoEmpresaId = array();
        $centroTrabajoEmpresa = $em->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));
        foreach ($centroTrabajoEmpresa as $cte) {
            array_push($centroTrabajoEmpresaId, $cte->getCentro()->getId());
        }
        $form = $this->createForm(ContratoType::class, $contrato, array('empresaId' => $arrayEmpresaId, 'empresaObj' => $empresa, 'centroId' => $centroTrabajoEmpresaId, 'centroObj' => $contrato->getCentro()));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $contrato = $form->getData();
            $em->persist($contrato);
            $em->flush();

            $renovacion = $em->getRepository('App\Entity\Renovacion')->findOneBy(array('contrato' => $contrato, 'anulado' => false));
            if (!is_null($renovacion)) {
                $renovacion->setFechainicio($contrato->getFechainicio());
                $renovacion->setFechavencimiento($contrato->getFechavencimiento());
                $em->persist($renovacion);
                $em->flush();
            }

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('contrato_update', array('id' => $contrato->getId()));
        }
        return $this->render('contrato/edit.html.twig',  array('form' => $form->createView(), 'tarifaContratoReconocimientosServicios' => $tarifaContratoReconocimientosServicios, 'tarifaContratoAnaliticas' => $tarifaContratoAnaliticas, 'serviciosContratados' => $serviciosContratados, 'listServicios' => $listServicios, 'pagosContrato' => $pagosContrato));
    }

    /*
     * Tipo
     * 0 --> Precios por trabajador de los reconocimientos medicos
     * 1 -> Precios por trabajador de las analisitcas
     */
    public function addTarifa(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $concepto = $_REQUEST['concepto'];
        $importe = $_REQUEST['importe'];
        $iva = $_REQUEST['iva'];
        $tipo = $_REQUEST['tipo'];
        $contratoId = $_REQUEST['contratoId'];

        $contrato = $em->getRepository('App\Entity\Contrato')->find($contratoId);

        $tarifaContrato = new TarifaContrato();
        $tarifaContrato->setDescripcion($concepto);
        $tarifaContrato->setImporte($importe);
        $tarifaContrato->setImporteIva($iva);
        $tarifaContrato->setTipo($tipo);
        $tarifaContrato->setContrato($contrato);

        $em->persist($tarifaContrato);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function recuperaTarifas(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $tipo = $_REQUEST['tipo'];
        $contratoId = $_REQUEST['contratoId'];

        $query = $em->createQuery(
            'SELECT c
            FROM App:TarifaContrato c
            where c.contrato = ' . $contratoId . '
            and c.tipo = ' . $tipo . '
            and c.anulado = false
			order by c.descripcion ASC '
        );
        $tarifaContrato = $query->getArrayResult();

        return new JsonResponse(json_encode($tarifaContrato));
    }

    public function updateTarifa(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $tarifaContratoId = $_REQUEST['tarifaContratoId'];
        $concepto = $_REQUEST['concepto'];
        $importe = $_REQUEST['importe'];
        $importeIva = $_REQUEST['importeIva'];

        $tarifaContrato = $em->getRepository('App\Entity\TarifaContrato')->find($tarifaContratoId);

        $tarifaContrato->setDescripcion($concepto);
        $tarifaContrato->setImporte($importe);
        $tarifaContrato->setImporteIva($importeIva);
        $em->persist($tarifaContrato);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function deleteTarifa(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $tarifaContratoId = $_REQUEST['tarifaContratoId'];
        $tarifaContrato = $em->getRepository('App\Entity\TarifaContrato')->find($tarifaContratoId);

        $tarifaContrato->setAnulado(true);
        $em->persist($tarifaContrato);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function recuperaTarifa(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $tarifaContratoId = $_REQUEST['tarifaContratoId'];

        $tarifaContrato = $em->getRepository('App\Entity\TarifaContrato')->find($tarifaContratoId);

        $data = array(
            'id' => $tarifaContrato->getId(),
            'concepto' => $tarifaContrato->getDescripcion(),
            'tipo' => $tarifaContrato->getTipo(),
            'importe' => $tarifaContrato->getImporte(),
            'iva' => $tarifaContrato->getImporteIva()
        );
        return new JsonResponse($data);
    }

    /*
	* Tipo
	* 1 --> Servicio prevención
	* 2 -> Servicio vigilancia de la salud
	*/
    public function addServicio(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $servicio = $_REQUEST['servicio'];
        $importe = $_REQUEST['importe'];
        $contratoId = $_REQUEST['contratoId'];
        $importeRenovacion = $_REQUEST['importeRenovacion'];

        $contrato = $em->getRepository('App\Entity\Contrato')->find($contratoId);
        $listaServicio = $em->getRepository('App\Entity\ListaServiciosContratados')->find($servicio);

        $servicioContratado = new ServicioContratado();
        $servicioContratado->setServicio($listaServicio);
        $servicioContratado->setPrecio($importe);
        $servicioContratado->setContrato($contrato);

        if ($importeRenovacion != "") {
            $servicioContratado->setPrecioRenovacion($importeRenovacion);
        }
        $em->persist($servicioContratado);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function recuperaServicios(Request $request)
    {
        $contratoId = $_REQUEST['contratoId'];

        $query = "select a.id, a.precio, a.precio_renovacion, b.descripcion, b.tipo_id from servicio_contratado a inner join lista_servicios_contratados b on a.servicio_id = b.id where a.contrato_id = $contratoId and a.anulado = false and b.anulado = false order by b.tipo_id asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $serviciosContratados = $stmt->fetchAll();

        return new JsonResponse(json_encode($serviciosContratados));
    }

    public function deleteServicio(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $servicioId = $_REQUEST['servicioContratadoId'];
        $servicioContratado = $em->getRepository('App\Entity\ServicioContratado')->find($servicioId);

        $em->remove($servicioContratado);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function recuperaServicio(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $servicioId = $_REQUEST['servicioContratadoId'];

        $servicioContratado = $em->getRepository('App\Entity\ServicioContratado')->find($servicioId);

        $data = array(
            'id' => $servicioContratado->getId(),
            'descripcion' => $servicioContratado->getServicio()->getId(),
            'importe' => $servicioContratado->getPrecio(),
            'importerenovacion' => $servicioContratado->getPrecioRenovacion(),
        );
        return new JsonResponse($data);
    }

    public function updateServicio(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $servicioContratadoId = $_REQUEST['servicioContratadoId'];
        $servicioId = $_REQUEST['servicio'];
        $importe = $_REQUEST['importe'];
        $importeRenovacion = $_REQUEST['importeRenovacion'];

        if ($importeRenovacion == "") {
            $importeRenovacion = null;
        }
        $servicioContratado = $em->getRepository('App\Entity\ServicioContratado')->find($servicioContratadoId);
        $servicio = $em->getRepository('App\Entity\ListaServiciosContratados')->find($servicioId);

        $servicioContratado->setServicio($servicio);
        $servicioContratado->setPrecio($importe);
        $servicioContratado->setPrecioRenovacion($importeRenovacion);
        $em->persist($servicioContratado);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function updatePago(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $contratoPagoId = $_REQUEST['contratoPagoId'];
        $concepto = $_REQUEST['concepto'];
        $vencimiento = $_REQUEST['vencimiento'];
        $importeSinIva = $_REQUEST['importeSinIva'];
        $importeExento = $_REQUEST['importeExento'];
        $importeSujeto = $_REQUEST['importeSujeto'];
        $importeIva = $_REQUEST['importeIva'];
        $importeTotal = $_REQUEST['importeTotal'];
        $facturado = $_REQUEST['facturado'];

        $contratoPago = $em->getRepository('App\Entity\ContratoPago')->find($contratoPagoId);

        $contratoPago->setTextoPago($concepto);
        $contratoPago->setVencimiento(new \DateTime($vencimiento));
        $contratoPago->setImporteSinIva($importeSinIva);
        $contratoPago->setImporteExentoIva($importeExento);
        $contratoPago->setImporteSujetoIva($importeSujeto);
        $contratoPago->setImporteIva($importeIva);
        $contratoPago->setImporteTotal($importeTotal);

        if ($facturado == "true") {
            $contratoPago->setFacturado(true);
        } else {
            $contratoPago->setFacturado(false);
        }
        $em->persist($contratoPago);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function recuperaPago(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $contratoPagoId = $_REQUEST['contratoPagoId'];
        $contratoPago = $em->getRepository('App\Entity\ContratoPago')->find($contratoPagoId);

        $data = array(
            'id' => $contratoPago->getId(),
            'concepto' => $contratoPago->getTextoPago(),
            'vencimiento' => $contratoPago->getVencimiento()->format('Y-m-d'),
            'sinIva' => round($contratoPago->getImporteSinIva(), 2),
            'exento' => round($contratoPago->getImporteExentoIva(), 2),
            'sujeto' => round($contratoPago->getImporteSujetoIva(), 2),
            'iva' => round($contratoPago->getImporteIva(), 2),
            'total' =>  round($contratoPago->getImporteTotal(), 2),
            'facturado' => $contratoPago->getFacturado()
        );
        return new JsonResponse($data);
    }

    public function recuperaPagos(Request $request)
    {
        $contratoId = $_REQUEST['contratoId'];

        $query = "select id, texto_pago, to_char(vencimiento, 'DD/MM/YYYY') as vencimiento, importe_sin_iva, importe_exento_iva, importe_sujeto_iva, importe_iva, importe_total, facturado from contrato_pago where contrato_id = $contratoId and anulado = false";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $pagos = $stmt->fetchAll();

        return new JsonResponse(json_encode($pagos));
    }

    public function deletePago(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $pagoId = $_REQUEST['pagoId'];
        $contratoPago = $em->getRepository('App\Entity\ContratoPago')->find($pagoId);

        $contratoPago->setAnulado(true);
        $em->persist($contratoPago);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function fraccionaPagosAutomatico(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $contratoId = $_REQUEST['contratoId'];
        $contrato = $em->getRepository('App\Entity\Contrato')->find($contratoId);
        $numeroContrato = $contrato->getContrato();

        $vigenciaContratoIni = $contrato->getFechainicio()->format('d/m/Y');
        $vigenciaContratoFin = $contrato->getFechavencimiento()->format('d/m/Y');

        $serviciosContratados = $em->getRepository('App\Entity\ServicioContratado')->findBy(array('contrato' => $contrato, 'anulado' => false));
        if (count($serviciosContratados) == 0) {
            $data = array();
            array_push($data, "KO");

            return new JsonResponse($data);
        }
        $importeServicios = 0;
        foreach ($serviciosContratados as $sc) {
            $importeServicios = $importeServicios + $sc->getPrecio();
        }
        $importeTotal = $importeServicios * 1.21;
        $importeIva = $importeServicios * 0.21;

        $contrato->setImporteSujetoIva($importeServicios);
        $contrato->setImporteContrato($importeTotal);
        $contrato->setImporteExentoIva(0);
        $contrato->setImporteIva($importeIva);
        $em->persist($contrato);
        $em->flush();

        $modalidadContratoId = $contrato->getContratoModalidad()->getId();
        switch ($modalidadContratoId) {
            case 1:
                $modalidadContrato = 'PRL SIN SALUD';
                break;
            case 2:
                $modalidadContrato = 'SALUD';
                break;
            case 3:
                $modalidadContrato = 'PRL+SALUD';
                break;
            default:
                $modalidadContrato = $contrato->getContratoModalidad()->getDescripcion();
        }
        $textoPago = '100% ' . $modalidadContrato . ' ANUAL DEL CONTRATO ' . $numeroContrato . ' CON VIGENCIA ' . $vigenciaContratoIni . '-' . $vigenciaContratoFin;

        $contratoPago = new ContratoPago();
        $contratoPago->setAnulado(false);
        $contratoPago->setPorcentaje(100);
        $contratoPago->setContrato($contrato);
        $contratoPago->setFacturado(false);
        $contratoPago->setNPago(1);
        $contratoPago->setTextoPago($textoPago);
        $contratoPago->setVencimientoMeses(0);
        $contratoPago->setImporteSinIva(round($importeServicios, 2));
        $contratoPago->setImporteSujetoIva(round($importeServicios, 2));
        $contratoPago->setImporteTotal(round($importeTotal, 2));
        $contratoPago->setImporteExentoIva(0);
        $contratoPago->setImporteIva(round($importeIva, 2));
        $contratoPago->setVencimiento(new \DateTime());

        $em->persist($contratoPago);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function fraccionaPagosManual(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $hoy = new \DateTime('today');

        $pagos = $_REQUEST['pagos'];
        $frecuencia = $_REQUEST['frecuencia'];
        $diaPago = $_REQUEST['dia'];

        $contratoId = $_REQUEST['contratoId'];
        $contrato = $em->getRepository('App\Entity\Contrato')->find($contratoId);
        $numeroContrato = $contrato->getContrato();

        $vigenciaContratoIni = $contrato->getFechainicio()->format('d/m/Y');
        $vigenciaContratoFin = $contrato->getFechavencimiento()->format('d/m/Y');

        $porcentaje = round(100 / $pagos, 2);

        $serviciosContratados = $em->getRepository('App\Entity\ServicioContratado')->findBy(array('contrato' => $contrato, 'anulado' => false));
        if (count($serviciosContratados) == 0) {
            $data = array();
            array_push($data, "KO");

            return new JsonResponse($data);
        }
        $importeServicios = 0;
        foreach ($serviciosContratados as $sc) {
            $importeServicios = $importeServicios + $sc->getPrecio();
        }
        $importeTotal = $importeServicios * 1.21;
        $importeIva = $importeServicios * 0.21;

        $contrato->setImporteSujetoIva($importeServicios);
        $contrato->setImporteContrato($importeTotal);
        $contrato->setImporteExentoIva(0);
        $contrato->setImporteIva($importeIva);
        $em->persist($contrato);
        $em->flush();

        // Calculamos la fecha de vencimiento
        $fechaInicioYear = $hoy->format('Y');
        $fechaInicioMonth = $hoy->format('m');

        $fechaInicioPago = new \DateTime($fechaInicioYear . '-' . $fechaInicioMonth . '-' . $diaPago);
        if ($fechaInicioPago >= $hoy) {
            $fechaVencimiento = $fechaInicioPago;
        } else {
            switch ($frecuencia) {
                case 1:
                    $fechaVencimiento = date_add($fechaInicioPago, date_interval_create_from_date_string("1 months"));
                    break;
                case 2:
                    $fechaVencimiento = date_add($fechaInicioPago, date_interval_create_from_date_string("1 year"));
                    break;
            }
        }
        $modalidadContratoId = $contrato->getContratoModalidad()->getId();
        switch ($modalidadContratoId) {
            case 1:
                $modalidadContrato = 'PRL SIN SALUD';
                break;
            case 2:
                $modalidadContrato = 'SALUD';
                break;
            case 3:
                $modalidadContrato = 'PRL+SALUD';
                break;
            default:
                $modalidadContrato = $contrato->getContratoModalidad()->getDescripcion();
        }
        $importeSinIva = $importeServicios / $pagos;
        $importeTotal = $importeSinIva * 1.21;
        $importeIva = $importeSinIva * 0.21;

        $textoPago = $porcentaje . '% ' . $modalidadContrato . ' ANUAL DEL CONTRATO ' . $numeroContrato . ' CON VIGENCIA ' . $vigenciaContratoIni . '-' . $vigenciaContratoFin;

        $contratoPago = new ContratoPago();
        $contratoPago->setAnulado(false);
        $contratoPago->setPorcentaje($porcentaje);
        $contratoPago->setContrato($contrato);
        $contratoPago->setFacturado(false);
        $contratoPago->setNPago(1);
        $contratoPago->setTextoPago($textoPago);
        $contratoPago->setVencimientoMeses(0);
        $contratoPago->setImporteSinIva(round($importeSinIva, 2));
        $contratoPago->setImporteSujetoIva(round($importeSinIva, 2));
        $contratoPago->setImporteTotal(round($importeTotal, 2));
        $contratoPago->setImporteExentoIva(0);
        $contratoPago->setImporteIva(round($importeIva, 2));
        $contratoPago->setVencimiento($fechaVencimiento);

        $em->persist($contratoPago);
        $em->flush();

        for ($i = 1; $i <= $pagos - 1; $i++) {
            $numeroPago = $i + 1;

            // Calculamos la fecha
            switch ($frecuencia) {
                case 1:
                    $fechaVencimiento =  date_add($fechaVencimiento, date_interval_create_from_date_string("1 months"));
                    break;
                case 2:
                    $fechaVencimiento =  date_add($fechaVencimiento, date_interval_create_from_date_string("1 year"));
                    break;
            }
            $newContratoPago = clone $contratoPago;
            $newContratoPago->setNPago($numeroPago);
            $newContratoPago->setVencimiento($fechaVencimiento);
            $em->persist($newContratoPago);
            $em->flush();
        }
        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function showContratosRenovados(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getRenovacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $query = "select a.id, to_char(b.fechainicio, 'DD/MM/YYYY') as fechainicio, to_char(b.fechainicio, 'YYYYMMDDHHmm') as fechainiciotimestamp, 
            to_char(a.fechavencimiento, 'DD/MM/YYYY') as fechavencimiento, 
            to_char(a.fechavencimiento, 'YYYYMMDDHHmm') as fechavencimientotimestamp, b.contrato, b.tipo_contrato_id, a.renovado, a.cancelada, a.documento_renovacion, c.empresa, b.fichero_id, b.id as contratoid from renovacion a
				inner join contrato b on a.contrato_id = b.id
				inner join empresa c on b.empresa_id = c.id
				where a.renovado = true
				and a.anulado = false 
				and b.anulado = false 
				and c.anulado = false
				order by b.fechainicio desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $renovaciones = $stmt->fetchAll();

        // Buscamos las plantillas de la carpeta contratos
        $carpetaContratos = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(5);
        $plantillasContratos = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaContratos, 'anulado' => false));

        return $this->render('renovacion/show.html.twig', array('renovaciones' => $renovaciones, 'listPlantillasContratos' => $plantillasContratos));
    }

    public function showContratosNoRenovados(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getRenovacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $query = "select a.id, to_char(b.fechainicio, 'DD/MM/YYYY') as fechainicio, to_char(b.fechainicio, 'YYYYMMDDHHmm') as fechainiciotimestamp, 
            to_char(a.fechavencimiento, 'DD/MM/YYYY') as fechavencimiento, 
            to_char(a.fechavencimiento, 'YYYYMMDDHHmm') as fechavencimientotimestamp, b.contrato, b.tipo_contrato_id, a.renovado, a.cancelada, a.documento_renovacion, c.empresa, b.fichero_id, b.id as contratoid from renovacion a
				inner join contrato b on a.contrato_id = b.id
				inner join empresa c on b.empresa_id = c.id
				where a.renovado = false
				and a.anulado = false 
				and b.anulado = false 
				and c.anulado = false
				order by b.fechainicio desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $renovaciones = $stmt->fetchAll();

        // Buscamos las plantillas de la carpeta contratos
        $carpetaContratos = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(5);
        $plantillasContratos = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaContratos, 'anulado' => false));

        return $this->render('renovacion/show.html.twig', array('renovaciones' => $renovaciones, 'listPlantillasContratos' => $plantillasContratos));
    }

    public function filtroContratos(Request $request)
    {
        $ini = $_REQUEST['ini'];
        $fin = $_REQUEST['fin'];

        $renovado = "";
        if (isset($_REQUEST['renovado'])) {
            $renovado = $_REQUEST['renovado'];
        }
        $tipo = "";
        if (isset($_REQUEST['tipo'])) {
            $tipo = $_REQUEST['tipo'];
        }
        $activa = "";
        if (isset($_REQUEST['activa'])) {
            $activa = $_REQUEST['activa'];
        }
        $facturado = "";
        if (isset($_REQUEST['facturado'])) {
            $facturado = $_REQUEST['facturado'];
        }
        $contratos = $this->buscaContratos($ini, $fin, $renovado, $tipo, $activa, $facturado);

        return new JsonResponse(json_encode($contratos));
    }

    function buscaContratos($ini, $fin, $renovado, $tipo, $activa, $facturado)
    {
        $query = "SELECT distinct
                    a.fechainicio,
                    b.localidad_fiscal,
                    ms.descripcion as municipio_serpa,
                    b.domicilio_fiscal,
                    t3.nombre AS vigilante_salud,
                    ce3.correo,
                    b.codigo_postal_postal AS codigo_postal,
                    b.cif,
                    a.fechavencimiento AS fecha_vencimiento_contrato,
                    ce2.descripcion AS codigo_empresa,
                    b.ccc,
                    b.nombre_representante,
                    b.fecha_alta AS fecha_alta,
                    t2.nombre AS vigilante_salud,
                    -- Subconsulta para obtener el último cnae
                    (SELECT c2.cnae 
                    FROM cnae_empresa ce 
                    INNER JOIN cnae c2 ON ce.cnae_id = c2.id 
                    WHERE ce.empresa_id = b.id
                    ORDER BY ce.id DESC -- Ordenar por el ID para obtener el último registro
                    LIMIT 1) AS cnae,
                    a.id,
                    a.contrato,
                    TO_CHAR(a.fechainicio, 'DD/MM/YYYY') AS fechainicio,
                    TO_CHAR(a.fechainicio, 'YYYYMMDDHHmm') AS fechainiciotimestamp,
                    TO_CHAR(c.fechavencimiento, 'DD/MM/YYYY') AS fechavencimiento,
                    TO_CHAR(c.fechavencimiento, 'YYYYMMDDHHmm') AS fechavencimientotimestamp,
                    b.empresa,
                    c.renovado,
                    a.fichero_id,
                    c.id AS renovacionid,
                    d.descripcion AS tipo,
                    -- Importe subconsulta
                    (SELECT CASE WHEN SUM(sc.precio_renovacion) > 0 THEN SUM(sc.precio_renovacion) ELSE SUM(sc.precio) END AS importe 
                    FROM servicio_contratado sc 
                    WHERE sc.anulado = false 
                    AND sc.contrato_id = a.id) AS importe,
                    -- Técnico subconsulta
                    (SELECT STRING_AGG(tec.nombre::text, ' , ') 
                    FROM tecnico_empresa te 
                    INNER JOIN tecnico tec ON te.tecnico_id = tec.id 
                    WHERE te.anulado = false 
                    AND te.empresa_id = a.empresa_id) AS tecnico,
                    h.nombre AS colaborador,
                    i.descripcion AS agente,
                    d.id AS tipoid,
                    b.localidad_postal,
                    b.provincia_postal,
                    b.trabajadores,
                    b.anulado,
                    a.facturado
                FROM contrato a 
                INNER JOIN empresa b ON a.empresa_id = b.id
                INNER JOIN renovacion c ON a.id = c.contrato_id
                LEFT JOIN contrato_modalidad d ON a.contrato_modalidad_id = d.id
                LEFT JOIN contrato_pago e ON a.id = e.contrato_id 
                LEFT JOIN asesoria h ON b.colaborador_id = h.id
                LEFT JOIN comercial i ON b.agente_id = i.id
                LEFT JOIN tecnico t2 ON t2.id = b.vigilancia_salud_id 
                LEFT JOIN tecnico t3 ON t3.id = b.gestor_administrativo_id 
                LEFT JOIN codigo_empresa ce2 ON ce2.id = b.codigo_empresa_id 
                LEFT JOIN municipio_serpa ms ON ms.id = b.municipio_fiscal_serpa_id 
                LEFT JOIN (
                    SELECT DISTINCT ON (empresa_id) * 
                    FROM correo_empresa 
                    ORDER BY empresa_id, correo
                ) ce3 ON ce3.empresa_id = b.id 
                WHERE a.anulado = false
                AND c.anulado = false
                AND a.renovado = false
                AND a.cancelado = false";

        if ($ini != "") {
            if ($fin != "") {
                $dateVencimiento = date("Y-m-d", strtotime($fin . "- 1 year"));
                $dateVencimiento = new \DateTime($dateVencimiento);

                $dateVencimientoString = $dateVencimiento->format('Y-m-d');
                $query .= " and a.fechainicio between '$ini 00:00:00' and '$dateVencimientoString 00:00:00' ";
            } else {
                $query .= " and a.fechainicio >= '$ini 00:00:00' ";
            }
        }
        if ($renovado != "") {
            switch ($renovado) {
                case '1':
                    $query .= " and c.renovado = true ";
                    break;
                case '0':
                    $query .= " and c.renovado = false ";
            }
        }
        if ($tipo != "") {
            $tipos = "";
            foreach ($tipo as $t) {
                $tipos .= $t . ',';
            }
            $tipos = rtrim($tipos, ',');

            if ($tipos != "") {
                $query .= " and d.id in($tipos) ";
            }
        }
        if ($activa != "") {
            switch ($activa) {
                case '1':
                    $query .= " and b.anulado = false ";
                    break;
                case '0':
                    $query .= " and b.anulado = true ";
            }
        } else {
            $query .= " and b.anulado = false ";
        }
        if ($facturado != "") {
            switch ($facturado) {
                case '1':
                    $query .= " and a.facturado = true ";
                    break;
                case '0':
                    $query .= " and a.facturado = false ";
            }
        }
        $query .= "group by b.localidad_fiscal,	ms.descripcion,	b.domicilio_fiscal,	t3.nombre, ce3.correo, b.codigo_postal_postal, b.cif, a.fechavencimiento, ce2.descripcion, b.ccc, b.nombre_representante, b.fecha_alta, t2.nombre ,cnae, a.id, c.fechavencimiento, b.empresa, b.anulado, b.localidad_postal, b.provincia_postal, b.trabajadores, c.renovado, c.id, d.id, h.nombre, i.descripcion, a.facturado order by a.fechainicio desc";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $contratos = $stmt->fetchAll();

        return $contratos;
    }

    public function showContratosRenovadosMultiple(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getRenovarContratoMultipleSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $arrayContratosGenerados = $session->get('contratosGenerados');
        $arrayContratosGeneradosString = "";
        foreach ($arrayContratosGenerados as $acg) {
            $arrayContratosGeneradosString .= $acg . ',';
        }
        $arrayContratosGeneradosString = rtrim($arrayContratosGeneradosString, ",");

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, a.contrato, to_char(a.fechainicio, 'DD/MM/YYYY') as fechainicio, to_char(a.fechainicio, 'YYYYMMDDHHmm') as fechainiciotimestamp, to_char(a.fechavencimiento, 'DD/MM/YYYY') as fechavencimiento, to_char(a.fechavencimiento, 'YYYYMMDDHHmm') as fechavencimientotimestamp, b.empresa, c.renovado, a.fichero_id, c.id as renovacionid, d.descripcion as tipo, d.id as tipoid from contrato a 
            inner join empresa b on a.empresa_id = b.id
            inner join renovacion c on a.id = c.contrato_id
            left join contrato_modalidad d on a.contrato_modalidad_id = d.id
            where a.anulado = false
            and c.anulado = false
            and a.id in ($arrayContratosGeneradosString)
            order by a.fechainicio desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $contratos = $stmt->fetchAll();

        $object = array("json" => $username, "entidad" => "contratos renovados", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('contrato/show_renovados.html.twig', array('contratos' => $contratos));
    }

    function calcularNumeroContrato($year)
    {
        $query = "select MAX(CAST(substring(contrato, 0, 6)  AS INTEGER)) as contrato from contrato where fechainicio >= '$year-01-01' and anulado = false";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $resultContrato = $stmt->fetchAll();

        if (count($resultContrato) > 0) {
            $numeroContrato = str_pad($resultContrato[0]['contrato'] + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $numeroContrato = '00001';
        }
        return $numeroContrato;
    }

    public function sendContrato(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getSendContratoSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $user = $this->getUser();
        $usuario = $repository->find($user);
        $mail = $usuario->getMail();
        $emailUser = $usuario->getEmail();
        $passwordMail = $usuario->getPasswordMail();
        $hostMail = $usuario->getHostMail();
        $puertoMail = $usuario->getPuertoMail();
        $encriptacionMail = $usuario->getEncriptacionMail();
        $userMail = $usuario->getUserMail();

        $fileRepo = $em->getRepository('App\Entity\GdocFichero');
        $contratoRepo = $em->getRepository('App\Entity\Contrato');

        $contratosEnviar = $_REQUEST['contratos'];
        $contratosEnviarArray = explode(",", $contratosEnviar);

        $nombresContratos = array();

        // Buscamos las facturas que se enviaran y las mostramos al usuario
        for ($i = 0; $i < count($contratosEnviarArray); $i++) {
            $contratoId = $contratosEnviarArray[$i];
            $contrato = $contratoRepo->find($contratoId);

            $fichero = $contrato->getFichero();
            array_push($nombresContratos, str_replace('docx', 'pdf', $fichero->getNombre()));
        }
        // Buscamos de la empresa el correo o correos para enviar facturas
        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        $destinatarios = null;
        $funcionEnviarCorreo = $em->getRepository('App\Entity\FuncionCorreo')->find(1);
        $correosEnviarContrato = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false, 'funcion' => $funcionEnviarCorreo));

        foreach ($correosEnviarContrato as $cef) {
            $destinatarios .= $cef->getCorreo() . ';';
        }
        $destinatarios = rtrim($destinatarios, ";");

        $form = $this->createForm(EnviarCorreoType::class, null, array('destinatario' => $destinatarios, 'cco' => $emailUser));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Obtenemos los datos de configuracion de la gestion documental
            $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
            $rutaCompleta = $gdocConfig->getRuta();
            $carpetaGenerada = $gdocConfig->getCarpetaContrato();
            $carpetaTemporal = $gdocConfig->getCarpetaTemporal();
            $passwordUsuario = $gdocConfig->getPasswordFicherosEncriptados();

            if (is_null($mail) || is_null($passwordMail) || is_null($puertoMail) || is_null($hostMail) || is_null($encriptacionMail) || is_null($userMail)) {
                $traduccion = $translator->trans('TRANS_AVISO_NO_CONFIGURACION_CORREO');
                $this->addFlash('danger', $traduccion);
                return $this->redirectToRoute('empresa_update', array('id' => $empresaId));
            }
            // Recogemos lo datos
            $para = $form["para"]->getData();
            $cc = $form["cc"]->getData();
            $cco = $form["cco"]->getData();
            $asunto = $form["asunto"]->getData();
            $mensaje = $form["mensaje"]->getData();

            $transport = new \Swift_SmtpTransport($hostMail, $puertoMail, $encriptacionMail);
            $transport->setUsername($userMail);
            $transport->setPassword($passwordMail);
            $transport->setHost($hostMail);
            $transport->setAuthMode('login');

            $mailer = new \Swift_Mailer($transport);

            $para = trim($para);

            $message = new \Swift_Message();
            $message->setSubject($asunto);
            $message->setFrom($mail);
            $message->setTo(explode(";", $para));
            $message->setReplyTo($emailUser);
            if (!is_null($cc) && $cc != "") {
                $message->setCc(explode(";", $cc));
            }
            if (!is_null($cco) && $cco != "") {
                $message->setBcc(explode(";", $cco));
            }
            $message->setBody($mensaje, 'text/plain');

            // Buscamos las facturas y adjuntamos el pdf al correo
            for ($i = 0; $i < count($contratosEnviarArray); $i++) {
                $contratoId = $contratosEnviarArray[$i];
                $contrato = $contratoRepo->find($contratoId);
                $fichero = $contrato->getFichero();

                // Convertimos el word en pdf
                $fileDocx = $rutaCompleta . $carpetaGenerada . '/' . $fichero->getNombre();

                $filePdf = str_replace('docx', 'pdf', $fileDocx);
                $outdir = $rutaCompleta . $carpetaGenerada;

                $cmd = 'soffice --headless --convert-to pdf:writer_pdf_Export "' . $fileDocx . '" --outdir "' . $outdir . '"';
                exec($cmd);

                // Encriptamos el documento
                $passwordOwner = $contrato->getPasswordPdf();
                if (is_null($passwordOwner)) {
                    $contratoId = $contrato->getId();
                    $passwordOwner = hash('sha256', 'OpenticPrevencion' . $contratoId);
                    $contrato->setPasswordPdf($passwordOwner);
                }
                $nombrePlantillaRestriccionPdf = str_replace('docx', 'pdf', $fichero->getNombre());
                $filePdfEncriptado = $rutaCompleta . $carpetaTemporal . '/' . $nombrePlantillaRestriccionPdf;

                $this->encriptarPdf($filePdfEncriptado, $filePdf, $passwordOwner, $passwordUsuario);

                // Lo adjuntamos al correo
                $message->attach(\Swift_Attachment::fromPath($filePdfEncriptado));

                // Marcamos la factura como enviada
                $contrato->setEnviado(true);
                $contrato->setFechaEnvio(new \DateTime());
                $em->persist($contrato);
                $em->flush();
                //unlink($filePdf);
            }
            // Enviamos el correo
            $mailer->send($message);

            // Insertamos el correo en el log
            $this->insertLogMail($em, $usuario, $asunto, $para, $message->getBody(), "Envío de contratos");

            $traduccion = $translator->trans('TRANS_SEND_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('empresa_update', array('id' => $empresaId));
        }
        return $this->render('emails/send_email.html.twig', array('form' => $form->createView(), 'ficherosEnviar' => $nombresContratos));
    }

    function encriptarPdf($filePdfEncriptado, $filePdf, $passwordOwner, $passwordUsuario)
    {
        // Copiamos el PDF sin encriptar
        copy($filePdf, $filePdfEncriptado);
    }

    function insertLogMail($em, $usuario, $asunto, $destinatario, $mensaje, $tipo)
    {
        $logEnvioMail = new LogEnvioMail();
        $logEnvioMail->setTipo($tipo);
        $logEnvioMail->setUsuario($usuario);
        $logEnvioMail->setFecha(new \DateTime());
        $logEnvioMail->setAsunto($asunto);
        $logEnvioMail->setDestinatario($destinatario);
        $logEnvioMail->setMensaje($mensaje);
        $em->persist($logEnvioMail);
        $em->flush();
    }
}
