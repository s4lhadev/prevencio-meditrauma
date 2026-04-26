<?php

namespace App\Controller;

use App\Entity\ContratoPago;
use App\Entity\Renovacion;
use App\Entity\ServicioContratado;
use App\Form\RenovacionRenovarType;
use App\Form\RenovacionType;
use App\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class RenovacionController extends AbstractController
{
    public function createRenovacion(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getAddRenovacionSn()) {
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
        $renovacion = new Renovacion();
        $form = $this->createForm(RenovacionRenovarType::class, $renovacion, array('empresaId' => $arrayEmpresaId, 'empresaObj' => $empresa, 'contratoObj' => null));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $renovacion = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($renovacion);
            $em->flush();

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('renovacion_show');
        }
        return $this->render('renovacion/edit_renovar.html.twig', array('listPlantillasContratos' => null, 'tipoContrato' => null, 'form' => $form->createView()));
    }

    public function viewRenovacion($id)
    {
        $renovacion = $this->getDoctrine()->getRepository('App\Entity\Renovacion')->find($id);

        if (!$renovacion) {
            throw $this->createNotFoundException(
                'La renovación con id ' . $id . ' no existe'
            );
        }
        return $this->render('renovacion/view.html.twig', array('article' => $renovacion));
    }

    public function showRenovaciones(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getRenovacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, to_char(a.fechainicio, 'DD/MM/YYYY') as fechainicio, to_char(a.fechainicio, 'YYYYMMDDHHmm') as fechainiciotimestamp, to_char(a.fechavencimiento, 'DD/MM/YYYY') as fechavencimiento, to_char(a.fechavencimiento, 'YYYYMMDDHHmm') as fechavencimientotimestamp, b.contrato, b.tipo_contrato_id, a.renovado, a.cancelada, a.documento_renovacion, c.empresa, b.id as contratoId, b.fichero_id from renovacion a
				inner join contrato b on a.contrato_id = b.id
				inner join empresa c on b.empresa_id = c.id
				where a.anulado = false 
				and b.anulado = false 
				and c.anulado = false
				order by a.fechainicio desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $renovaciones = $stmt->fetchAll();

        // Buscamos las plantillas de la carpeta contratos
        $carpetaContratos = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(5);
        $plantillasContratos = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaContratos, 'anulado' => false));

        $object = array("json" => $username, "entidad" => "renovaciones", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('renovacion/show.html.twig', array('listPlantillasContratos' => $plantillasContratos, 'renovaciones' => $renovaciones));
    }

    public function deleteRenovacion($id, Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getDeleteRenovacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $renovacion = $em->getRepository('App\Entity\Renovacion')->find($id);

        if (!$renovacion) {
            throw $this->createNotFoundException(
                'La renovación con id ' . $id . ' no existe'
            );
        }
        $renovacion->setAnulado(true);
        $em->persist($renovacion);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        return $this->redirectToRoute('renovacion_show');
    }

    public function updateRenovacion(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditRenovacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $renovacion = $em->getRepository('App\Entity\Renovacion')->find($id);

        if (!$renovacion) {
            throw $this->createNotFoundException(
                'La renovación con id ' . $id . ' no existe'
            );
        }
        $arrayEmpresaId = array();
        // Comprobamos si tiene una empresa seleccionada sino le asignamos la del contrato
        $empresa = $session->get('empresa');
        if (!is_null($empresa)) {
            $empresaId = $empresa->getId();
            $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
        } else {
            $empresaId = $renovacion->getContrato()->getEmpresa()->getId();
            $empresa = $renovacion->getContrato()->getEmpresa();
            $session->set('empresa', $empresa);
        }
        array_push($arrayEmpresaId, $empresaId);

        $form = $this->createForm(RenovacionType::class, $renovacion, array('empresaId' => $arrayEmpresaId, 'empresaObj' => $empresa, 'contratoObj' => $renovacion->getContrato()));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $renovacion = $form->getData();
            $em->persist($renovacion);
            $em->flush();

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('renovacion_show');
        }
        return $this->render('renovacion/edit.html.twig',  array('form' => $form->createView()));
    }

    public function renovarRenovacion(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getRenovarRenovacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $renovacion = $em->getRepository('App\Entity\Renovacion')->find($id);

        if (!$renovacion) {
            throw $this->createNotFoundException(
                'La renovación con id ' . $id . ' no existe'
            );
        }
        $arrayEmpresaId = array();
        $empresaId = $renovacion->getContrato()->getEmpresa()->getId();
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
        $session->set('empresa', $empresa);

        array_push($arrayEmpresaId, $empresaId);

        $form = $this->createForm(RenovacionRenovarType::class, $renovacion, array('empresaId' => $arrayEmpresaId, 'empresaObj' => $empresa, 'contratoObj' => $renovacion->getContrato()));
        $form->handleRequest($request);

        $tipoContrato = $renovacion->getTipoContrato();
        if (!is_null($tipoContrato)) {
            $tipoContratoId = $tipoContrato->getId();
        } else {
            $tipoContratoId = null;
        }
        if ($form->isSubmitted()) {
            $renovacion = $form->getData();

            // Generamos el numero de contrato
            $hoy = new \DateTime();
            $year = $hoy->format('Y');
            $yearString = substr($year, 2, 4);

            // Calculamos el numero de contrato
            $numeroContrato = $this->calcularNumeroContrato($year);
            $newNumeroContrato = $numeroContrato . '/' . $yearString;

            // Recogemos el tipo de documento de renovación
            $tipoRenovacion = $form["documentoRenovacion"]->getData();
            $numeroPagos = $form["numeroPagos"]->getData();

            $em->beginTransaction();
            try {
                $this->renovarContrato($em, $session, $renovacion, $tipoRenovacion, $numeroPagos, $newNumeroContrato, $year, $translator, 0);
            } catch (\Exception $e) {
                $em->rollBack();

                $traduccion = $translator->trans('TRANS_AVISO_ERROR');
                $this->addFlash('danger', $traduccion);
                return $this->redirectToRoute('renovacion_show');

                throw $e;
            }
            $em->commit();

            // renovado??
            $renovacion->setRenovado(true);
            $renovacion->setDocumentoRenovacion($tipoRenovacion);

            $em->persist($renovacion);
            $em->flush();

            $traduccion = $translator->trans('TRANS_RENOVAR_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('renovacion_show');
        }
        return $this->render('renovacion/edit_renovar.html.twig',  array('form' => $form->createView(), 'tipoContrato' => $tipoContratoId));
    }

    public function filtraRenovaciones(Request $request)
    {
        $ini = $_REQUEST['ini'];
        $fin = $_REQUEST['fin'];

        $query = "select a.id, to_char(a.fechainicio, 'DD/MM/YYYY') as fechainicio, to_char(a.fechainicio, 'YYYYMMDDHHmm') as fechainiciotimestamp, to_char(a.fechavencimiento, 'DD/MM/YYYY') as fechavencimiento, to_char(a.fechavencimiento, 'YYYYMMDDHHmm') as fechavencimientotimestamp, b.contrato, b.tipo_contrato_id, a.renovado, a.cancelada, a.documento_renovacion, c.empresa, b.fichero_id from renovacion a
				inner join contrato b on a.contrato_id = b.id
				inner join empresa c on b.empresa_id = c.id
				where a.anulado = false 
				and b.anulado = false 
				and c.anulado = false";

        if ($ini != "") {
            $query .= " and a.fechainicio >= '$ini 00:00:00' ";
        }
        if ($fin != "") {
            $query .= " and a.fechavencimiento <= '$fin 00:00:00' ";
        }
        $query .= " order by a.fechainicio desc";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $renovaciones = $stmt->fetchAll();

        return new JsonResponse(json_encode($renovaciones));
    }

    public function renovarContratoMultiple(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getRenovarContratoMultipleSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $hoy = new \DateTime();
        $year = $hoy->format('Y');
        $yearString = substr($year, 2, 4);

        $contratosSelect = $_REQUEST['contratos'];
        $incrementoIPCNumber = $_REQUEST['numeroDecimal'];

        $contratosSelectArray = explode(",", $contratosSelect);

        $object = array("json" => $username, "entidad" => "Pulsa botón Renovar contratos", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $object, $usuario, TRUE);
        $em->flush();

        //David
        $session->set('contratosGenerados', array());

        $em->beginTransaction();

        foreach ($contratosSelectArray as $csa) {
            $contrato = $em->getRepository('App\Entity\Contrato')->find($csa);

            $renovacion = $em->getRepository('App\Entity\Renovacion')->findOneBy(array('contrato' => $contrato));
            $newNumeroContrato = $this->calcularNumeroContrato($year);
            $newNumeroContrato = $newNumeroContrato . '/' . $yearString;

            try {
                $this->renovarContrato($em, $session, $renovacion, 1, null, $newNumeroContrato, $year, $translator, $incrementoIPCNumber);
            } catch (\Exception $e) {
                $em->rollBack();

                $traduccion = $translator->trans('TRANS_AVISO_ERROR');
                $this->addFlash('danger', $traduccion);
                return $this->redirectToRoute('contrato_show');
            }
            $renovacion->setRenovado(true);
            $renovacion->setDocumentoRenovacion(1);

            $em->persist($renovacion);
            $em->flush();
        }
        $em->commit();

        $traduccion = $translator->trans('TRANS_RENOVAR_OK');
        $this->addFlash('success', $traduccion);

        return $this->redirectToRoute('contrato_show_renovados_multiple');
    }

    function renovarContrato($em, $session, $renovacion, $tipoRenovacion, $numeroPagos, $newNumeroContrato, $year, $translator, $incrementoIPCNumber)
    {
        $newFechaVencimiento = null;
        $yearString = substr($year, 2, 4);

        $arrayContratosGenerados = $session->get('contratosGenerados');

        switch ($tipoRenovacion) {
                // Renovación con nuevo contrato
            case 1:
                // Buscamos el antiguo contrato
                $oldContrato = $renovacion->getContrato();
                $oldContrato->setRenovado(true);
                $em->persist($oldContrato);
                $em->flush();

                // Comprobamos si la fecha de vencimiento sera modificada
                if (!is_null($renovacion->getFechaRenovacion())) {
                    $newFechaVencimiento = $renovacion->getFechaRenovacion();
                }
                // Creamos el nuevo contrato
                $newContrato = clone $oldContrato;

                $newFechaInicioContratoString = $oldContrato->getFechaInicio()->format('Y-m-d');
                $dateInicio = date("Y-m-d", strtotime($newFechaInicioContratoString . "+ 1 year"));
                $newFechaInicioContrato = new \DateTime($dateInicio);
                $newContrato->setFechaInicio($newFechaInicioContrato);

                $newFechaVencimientoContratoString = $newContrato->getFechaInicio()->format('Y-m-d');
                $dateVencimiento = date("Y-m-d", strtotime($newFechaVencimientoContratoString . "-1 day"));
                $dateVencimiento = date("Y-m-d", strtotime($dateVencimiento . "+ 1 year"));
                $newFechaVencimientoContrato = new \DateTime($dateVencimiento);
                $newContrato->setFechavencimiento($newFechaVencimientoContrato);

                $contrato = $renovacion->getContrato();
                //$increment = $this->importIncrement($renovacion,$contrato->getImporteSujetoIva());

                $newContrato->setContrato($newNumeroContrato);
                $newContrato->setReferencia($newNumeroContrato);
                $newContrato->setTextoFormaPago(null);
                $newContrato->setAnyo($year);
                $newContrato->setOldContrato($oldContrato);
                $newContrato->setAnulado(false);
                $newContrato->setCancelado(false);
                $newContrato->setPasswordPdf(null);
                $newContrato->setRenovado(false);
                $newContrato->setFichero(null);
                $newContrato->setImporteSujetoIva($newContrato->getImporteSujetoIva());
                $newContrato->setImporteIva(round($newContrato->getImporteSujetoIva() * 0.21, 2));
                $newContrato->setImporteContrato($newContrato->getImporteSujetoIva() + $newContrato->getImporteIva());
                $em->persist($newContrato);
                $em->flush();

                $vigenciaIni = $newFechaInicioContrato->format('d/m/Y');
                $vigenciaFin = $newFechaVencimientoContrato->format('d/m/Y');

                // Buscamos los pagos del contrato
                if (is_null($numeroPagos)) {
                    $contratoPagos = $em->getRepository('App\Entity\ContratoPago')->findBy(array('contrato' => $oldContrato, 'anulado' => false));
                    $numeroPagos = count($contratoPagos);
                }
                $importeServicios = 0;
                $importeRenovacion = 0;
                $serviciosContrato = $em->getRepository('App\Entity\ServicioContratado')->findBy(array('contrato' => $oldContrato, 'anulado' => false));

                if (count($serviciosContrato) == 0) {
                    $em->rollBack();
                    $nombreEmpresa = $oldContrato->getEmpresa()->getEmpresa();
                    $traduccion1 = $translator->trans('TRANS_CONTRATO_NO_SERVICIO_1', array(), 'contratos');
                    $traduccion2 = $translator->trans('TRANS_CONTRATO_NO_SERVICIO_2', array(), 'contratos');
                    $this->addFlash('danger', $traduccion1 . ' ' . $nombreEmpresa . ' ' . $traduccion2);
                    return $this->redirectToRoute('contrato_show');
                }
                $contrato = $renovacion->getContrato();
                // Fix error renovaci´nes con incremento, si ponen "," da error, sustituir por "."
                if (strpos($incrementoIPCNumber, ',') !== false) {
                    $incrementoIPCNumber = str_replace(',', '.', $incrementoIPCNumber);
                }
                foreach ($serviciosContrato as $sc) {
                    if ($incrementoIPCNumber == 0 || $incrementoIPCNumber == '0') {
                        if (!is_null($sc->getPrecioRenovacion())) {
                            $importeRenovacion = $sc->getPrecioRenovacion();
                            $importeServicios += $sc->getPrecioRenovacion();
                        } else {
                            $importeServicios += $sc->getPrecio();
                            $importeRenovacion = $sc->getPrecio();
                        }
                    } else {
                        if (!is_null($sc->getPrecioRenovacion())) {
                            $importeRenovacion = $sc->getPrecioRenovacion() + $sc->getPrecioRenovacion() * $incrementoIPCNumber / 100;
                            $importeServicios += $sc->getPrecioRenovacion() + $sc->getPrecioRenovacion() * $incrementoIPCNumber / 100;
                        } else {
                            $importeServicios += $sc->getPrecio() + $sc->getPrecio() * $incrementoIPCNumber / 100;
                            $importeRenovacion = $sc->getPrecio() + $sc->getPrecio() * $incrementoIPCNumber / 100;
                        }
                    }
                    $newServicioContratado = new ServicioContratado();
                    $newServicioContratado->setAnulado(false);
                    $newServicioContratado->setContrato($newContrato);
                    $newServicioContratado->setServicio($sc->getServicio());
                    $newServicioContratado->setPrecio($importeRenovacion);
                    $newServicioContratado->setPrecioRenovacion($importeRenovacion);
                    $em->persist($newServicioContratado);
                    $em->flush();
                }
                // Calculamos los importes
                $oldImporteContratoIva = round($importeServicios * 0.21, 2);
                $oldImporteContratoTotal = round($importeServicios * 1.21, 2);
                $oldImporteContratoExento = 0;
                $oldImporteContratoSujeto = $importeServicios;
                $oldImporteContratoSinIva = $importeServicios;
                $newImporteContratoTotal = round($oldImporteContratoTotal / $numeroPagos, 2);
                $newImporteContratoIva = $oldImporteContratoIva / $numeroPagos;
                $newImporteContratoExento = $oldImporteContratoExento / $numeroPagos;
                $newImporteContratoSujeto = $oldImporteContratoSujeto / $numeroPagos;
                $newImporteContratoSinIva = $oldImporteContratoSinIva / $numeroPagos;
                $newPorcentaje = 100 / $numeroPagos;

                for ($i = 0; $i <= $numeroPagos - 1; $i++) {
                    $nPago = $i + 1;

                    $modalidadContratoId = $newContrato->getContratoModalidad()->getId();
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
                            $modalidadContrato = $newContrato->getContratoModalidad()->getDescripcion();
                    }
                    $textoPago = $newPorcentaje . '% ' . $modalidadContrato . ' ANUAL DEL CONTRATO ' . $newNumeroContrato . ' CON VIGENCIA ' . $vigenciaIni . '-' . $vigenciaFin;

                    $newContratoPago = new ContratoPago();
                    $newContratoPago->setContrato($newContrato);
                    $newContratoPago->setFacturado(false);
                    $newContratoPago->setTextoPago(null);
                    $newContratoPago->setNPago($nPago);
                    $newContratoPago->setVencimiento($newFechaVencimiento);
                    $newContratoPago->setTextoPago($textoPago);
                    $newContratoPago->setPorcentaje(round($newPorcentaje, 3));
                    $newContratoPago->setImporteIva($newImporteContratoIva);
                    $newContratoPago->setImporteExentoIva($newImporteContratoExento);
                    $newContratoPago->setImporteSujetoIva($newImporteContratoSujeto);
                    $newContratoPago->setImporteSinIva($newImporteContratoSinIva);
                    $newContratoPago->setImporteTotal($newImporteContratoTotal);
                    $newContratoPago->setAnulado(false);
                    $em->persist($newContratoPago);
                    $em->flush();
                }
                // Creamos la nueva renovación
                $newRenovacion = new Renovacion();
                $newRenovacion->setContrato($newContrato);
                $newRenovacion->setFechainicio($newContrato->getFechainicio());
                $newRenovacion->setFechavencimiento($newContrato->getFechavencimiento());
                //$newRenovacion->setRenovado(false);
                $newRenovacion->setCancelada(false);
                $em->persist($newRenovacion);
                $em->flush();

                array_push($arrayContratosGenerados, $newContrato->getId());
                $session->set('contratosGenerados', $arrayContratosGenerados);

                break;
                // Renovación con factura
            case 2:
                // Buscamos el numero de factura
                $newNumeroFacturacion = $this->calcularNumeroFactura($year);
                $newNumeroFacturacion = $newNumeroFacturacion . '/' . $yearString;

                // Buscamos el antiguo contrato
                $oldContrato = $renovacion->getContrato();

                // Comprobamos si la fecha de vencimiento sera modificada
                if (!is_null($renovacion->getFechaRenovacion())) {
                    $newFechaVencimiento = $renovacion->getFechaRenovacion();
                }
                // Creamos el nuevo contrato
                $newContrato = clone $oldContrato;

                if (!is_null($newFechaVencimiento)) {
                    $newContrato->setFechainicio($newFechaVencimiento);
                    $newContrato->setFechavencimiento($newFechaVencimiento);
                } else {
                    $newFechaInicioContratoString = $oldContrato->getFechainicio()->format('Y-m-d');

                    $newFechaInicioVencimientoContratoString = date("Y-m-d", strtotime($newFechaInicioContratoString . "+ 1 year"));
                    $newFechaInicioVencimientoContrato = new \DateTime($newFechaInicioVencimientoContratoString);
                    $newContrato->setFechainicio($newFechaInicioVencimientoContrato);
                    $newContrato->setFechavencimiento($newFechaInicioVencimientoContrato);
                }
                $newContrato->setContrato($newNumeroContrato);
                $newContrato->setReferencia($newNumeroContrato);
                $newContrato->setTextoFormaPago(null);
                $newContrato->setAnyo($year);
                $newContrato->setOldContrato($oldContrato);
                $em->persist($newContrato);
                $em->flush();

                // Buscamos la factura del anterior contrato
                $oldFacturaContrato = $em->getRepository('App\Entity\Facturacion')->findOneBy(array('contrato' => $oldContrato, 'anulado' => false));

                $newFacturacionContrato = clone $oldFacturaContrato;
                if (!is_null($newFechaVencimiento)) {
                    $newFacturacionContrato->setFecha($newFechaVencimiento);
                } else {
                    $newFacturacionContrato->setFecha($oldFacturaContrato->getFecha());
                }
                $newFacturacionContrato->setContrato($newContrato);
                $newFacturacionContrato->setNumFac($newNumeroFacturacion);
                $newFacturacionContrato->setRenovacion(false);
                $newFacturacionContrato->setPagada(false);
                $newFacturacionContrato->setCancelada(false);
                $em->persist($newFacturacionContrato);
                $em->flush();

                // Buscamos las lineas de conceptos de la factura
                $oldFacturacionLineas = $em->getRepository('App\Entity\FacturacionLineasConceptos')->findBy(array('facturacion' => $oldFacturaContrato, 'anulado' => false));

                foreach ($oldFacturacionLineas as $oldFacturacionLinea) {
                    $newFacturacionLineas = clone $oldFacturacionLinea;

                    if ($renovacion->getIncrement()) {
                        //zxc
                        $increment = $this->importIncrement($renovacion, $oldFacturacionLinea->getImporte());
                        $newFacturacionLineas->setImporte($newFacturacionLineas->getImporte() + $increment);
                        $newFacturacionLineas->setIva($newFacturacionLineas->getImporte() * 0.21);
                    }
                    $newFacturacionLineas->setFacturacion($newFacturacionContrato);

                    $em->persist($newFacturacionLineas);
                    $em->flush();
                }
                // Buscamos las lineas de pagos de la factura
                $oldFacturacionPagos = $em->getRepository('App\Entity\FacturacionLineasPagos')->findBy(array('facturacion' => $oldFacturaContrato, 'anulado' => false));

                foreach ($oldFacturacionPagos as $oldFacturacionPago) {
                    $newFacturacionPagos = clone $oldFacturacionPago;
                    $newFacturacionPagos->setFacturacion($newFacturacionContrato);
                    if (!is_null($newFechaVencimiento)) {
                        $newFacturacionPagos->setVencimiento($newFechaVencimiento);
                    } else {
                        $newFacturacionPagos->setVencimiento($oldFacturaContrato->getFecha());
                    }
                    if ($renovacion->getIncrement()) {
                        $increment = $this->importIncrement($renovacion, $newFacturacionPagos->getImporteSujetoIva());
                        $newFacturacionPagos->setImporteSujetoIva($newFacturacionPagos->getImporteSujetoIva() + $increment);
                        $newFacturacionPagos->setImporteIva(round($newFacturacionPagos->getImporteSujetoIva() * 0.21, 2));
                        $newFacturacionPagos->setImporteTotal($newFacturacionPagos->getImporteSujetoIva() + $newFacturacionPagos->getImporteIva());
                    }
                    $em->persist($newFacturacionPagos);
                    $em->flush();
                }
                // Buscamos los giros bancarios de la factura
                $oldGirosBancarios = $em->getRepository('App\Entity\GiroBancario')->findBy(array('facturacion' => $oldFacturaContrato, 'anulado' => false));

                $numGirosBancarios = count($oldGirosBancarios);                     // Obtenir numero de pagos
                $incrementPorce = $renovacion->getIncrementPor();                   // Obtenir el porcentatge a incremetnar
                $importeTotal = $newFacturacionPagos->getImporteTotal();
                $incrementPerPagoPorce = $incrementPorce / $numGirosBancarios;            // Obtenir el porcentatge a incremetnar per pago

                $incrementPerPag = round(($importeTotal / $numGirosBancarios) * ($renovacion->getIncrementPor() / 100), 2);     // Obtenir la cantitat a incrementar a cada pago

                foreach ($oldGirosBancarios as $oldGiroBancario) {
                    $newGiroBancario = clone $oldGiroBancario;
                    $newGiroBancario->setFacturacion($newFacturacionContrato);
                    if (!is_null($newFechaVencimiento)) {
                        $newGiroBancario->setFecha($newFechaVencimiento);
                        $newGiroBancario->setVencimiento($newFechaVencimiento);
                    } else {
                        $newGiroBancario->setFecha($oldGiroBancario->getFecha());
                        $newGiroBancario->setVencimiento($oldGiroBancario->getVencimiento());
                    }
                    if ($renovacion->getIncrement()) {
                        $newGiroBancario->setImporte(round($importeTotal / $numGirosBancarios), 2);
                    } else {
                        $newGiroBancario->setImporte($oldGiroBancario->getImporte());
                    }
                    //$newGiroBancario->setImporte($oldGiroBancario->getImporte() + $incrementPerPag);
                    $newGiroBancario->setGirado(false);
                    $newGiroBancario->setManual(false);
                    $newGiroBancario->setDevolucion(false);
                    $newGiroBancario->setComision(false);
                    $newGiroBancario->setEsFactura(false);
                    $newGiroBancario->setPagoConfirmado(false);
                    $em->persist($newGiroBancario);
                    $em->flush();
                }
                // Creamos la nueva renovación
                $newRenovacion = new Renovacion();
                $newRenovacion->setContrato($newContrato);
                $newRenovacion->setFechainicio($newContrato->getFechainicio());
                $newRenovacion->setFechavencimiento($newContrato->getFechavencimiento());
                //$newRenovacion->setRenovado(false);
                $newRenovacion->setCancelada(false);

                $em->persist($newRenovacion);
                $em->flush();
                break;
                // Renovación tácita
            case 3:
                // Buscamos el antiguo contrato
                $oldContrato = $renovacion->getContrato();
                $oldContratoId = $oldContrato->getId();
                $numeroContrato = $oldContrato->getContrato();
                $empresa = $oldContrato->getEmpresa();

                if (is_null($newFechaVencimiento)) {
                    $newFechaVencimiento = $oldContrato->getFechaInicio();
                }
                $datosBancarios = $this->getDoctrine()->getRepository('App\Entity\DatosBancarios')->findOneBy(array('empresa' => $empresa, 'principal' => true, 'anulado' => false));

                if (!is_null($numeroPagos)) {
                    $oldContratoPagos = $em->getRepository('App\Entity\ContratoPago')->findBy(array('contrato' => $oldContrato, 'anulado' => false));

                    if (count($oldContratoPagos) > 1) {
                        // Buscamos los importes
                        $query = "select sum(importe_sin_iva) as importe from contrato_pago where contrato_id = $oldContratoId and anulado = false";
                        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                        $stmt->execute();
                        $resultImporteSinIva = $stmt->fetchAll();

                        $query = "select sum(importe_exento_iva) as importe from contrato_pago where contrato_id = $oldContratoId and anulado = false";
                        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                        $stmt->execute();
                        $resultImporteExentoIva = $stmt->fetchAll();

                        $query = "select sum(importe_sujeto_iva) as importe from contrato_pago where contrato_id = $oldContratoId and anulado = false";
                        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                        $stmt->execute();
                        $resultImporteSujetoIva = $stmt->fetchAll();

                        $query = "select sum(importe_iva) as importe from contrato_pago where contrato_id = $oldContratoId and anulado = false";
                        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                        $stmt->execute();
                        $resultImporteIva = $stmt->fetchAll();

                        $query = "select sum(importe_total) as importe from contrato_pago where contrato_id = $oldContratoId and anulado = false";
                        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                        $stmt->execute();
                        $resultImporteTotal = $stmt->fetchAll();

                        $newImporteSinIva = round($resultImporteSinIva[0]['importe'] / $numeroPagos, 2);
                        $newImporteExentoIva = round($resultImporteExentoIva[0]['importe'] / $numeroPagos, 2);
                        $newImporteSujetoIva = round($resultImporteSujetoIva[0]['importe'] / $numeroPagos, 2);
                        $newImporteIva = round($resultImporteIva[0]['importe'] / $numeroPagos, 2);
                        $newImporteTotal = round($resultImporteTotal[0]['importe'] / $numeroPagos, 2);
                    } else {
                        $newImporteSinIva = round($oldContratoPagos[0]->getImporteSinIva() / $numeroPagos, 2);
                        $newImporteExentoIva = round($oldContratoPagos[0]->getImporteExentoIva() / $numeroPagos, 2);
                        $newImporteSujetoIva = round($oldContratoPagos[0]->getImporteSujetoIva() / $numeroPagos, 2);
                        $newImporteIva = round($oldContratoPagos[0]->getImporteIva() / $numeroPagos, 2);
                        $newImporteTotal = round($oldContratoPagos[0]->getImporteTotal() / $numeroPagos, 2);
                    }
                    $newPorcentaje = round(100 / $numeroPagos, 2);

                    // Buscamos la primera fecha de vencimiento
                    $diaPago = $datosBancarios->getDiaPago();
                    if (!is_null($diaPago)) {
                        $fechaInicioYear = $newFechaVencimiento->format('Y');
                        $fechaInicioMonth = $newFechaVencimiento->format('m');

                        $fechaInicioPago = new \DateTime($fechaInicioYear . '-' . $fechaInicioMonth . '-' . $diaPago);

                        $fechaInicioString = $newFechaVencimiento->format('Y-m-d');
                        $fechaInicioPagoString = $fechaInicioPago->format('Y-m-d');

                        $date1 = strtotime($fechaInicioString);
                        $date2 = strtotime($fechaInicioPagoString);

                        if ($date1 > $date2) {
                            $fechaVencimiento = date("Y-m-d", strtotime($fechaInicioPagoString . "+ 1 month"));
                        } else {
                            $fechaVencimiento = $fechaInicioPago;
                        }
                    }
                    $fechaVencimientoString = $fechaVencimiento->format('Y-m-d');

                    for ($i = 0; $i < $numeroPagos; $i++) {
                        $countPago = $i + 1;

                        $newContratoPago = clone $oldContratoPagos[0];
                        $newContratoPago->setImporteSinIva($newImporteSinIva);
                        $newContratoPago->setImporteExentoIva($newImporteExentoIva);
                        $newContratoPago->setImporteSujetoIva($newImporteSujetoIva);
                        $newContratoPago->setImporteIva($newImporteIva);
                        $newContratoPago->setImporteTotal($newImporteTotal);
                        $newContratoPago->setPorcentaje($newPorcentaje);
                        $newContratoPago->setTextoPago($newPorcentaje . ' % de la renovación tácita del contrato ' . $numeroContrato);
                        $newContratoPago->setVencimiento($fechaVencimiento);
                        $newContratoPago->setNPago($countPago);
                        $newContratoPago->setFacturado(false);
                        $em->persist($newContratoPago);
                        $em->flush();

                        $fechaVencimiento = date("Y-m-d", strtotime($fechaVencimientoString . "+ 1 month"));
                        $fechaVencimiento = new \DateTime($fechaVencimiento);
                        $fechaVencimientoString = $fechaVencimiento->format('Y-m-d');
                    }
                } else {
                    $oldContratoPagos = $em->getRepository('App\Entity\ContratoPago')->findBy(array('contrato' => $oldContrato, 'anulado' => false));

                    // Buscamos la primera fecha de vencimiento
                    $diaPago = $datosBancarios->getDiaPago();
                    if (!is_null($diaPago)) {
                        $fechaInicioYear = $newFechaVencimiento->format('Y');
                        $fechaInicioMonth = $newFechaVencimiento->format('m');

                        $fechaInicioPago = new \DateTime($fechaInicioYear . '-' . $fechaInicioMonth . '-' . $diaPago);

                        $fechaInicioString = $newFechaVencimiento->format('Y-m-d');
                        $fechaInicioPagoString = $fechaInicioPago->format('Y-m-d');

                        $date1 = strtotime($fechaInicioString);
                        $date2 = strtotime($fechaInicioPagoString);

                        if ($date1 > $date2) {
                            $fechaVencimiento = date("Y-m-d", strtotime($fechaInicioPagoString . "+ 1 month"));
                        } else {
                            $fechaVencimiento = $fechaInicioPago;
                        }
                    }
                    $fechaVencimientoString = $fechaVencimiento->format('Y-m-d');

                    foreach ($oldContratoPagos as $oldContratoPago) {
                        $newContratoPago = clone $oldContratoPago;
                        $newContratoPago->setFacturado(false);
                        $newContratoPago->setVencimiento($fechaVencimiento);
                        $em->persist($newContratoPago);
                        $em->flush();

                        $fechaVencimiento = date("Y-m-d", strtotime($fechaVencimientoString . "+ 1 month"));
                        $fechaVencimiento = new \DateTime($fechaVencimiento);
                        $fechaVencimientoString = $fechaVencimiento->format('Y-m-d');
                    }
                }
                break;
                // Renovación programa anual FALTA
            case 4:
                break;
        }
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

    function calcularNumeroFactura($year)
    {
        $query = "select MAX(CAST(substring(num_fac, 0, 6)  AS INTEGER)) as facturacion from facturacion where fecha >= '$year-01-01' and anulado = false and serie_id = 7";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $resultFacturacion = $stmt->fetchAll();

        if (count($resultFacturacion) > 0) {
            $numeroFacturacion = str_pad($resultFacturacion[0]['facturacion'] + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $numeroFacturacion = '00001';
        }
        return $numeroFacturacion;
    }

    function importIncrement($renovacion, $importe)
    {
        $increment = 0;
        if ($renovacion->getIncrement()) {
            $contrato = $renovacion->getContrato();
            $increment = $importe / 100 * $renovacion->getIncrementPor();
        }
        return round($increment, 2);
    }
}
