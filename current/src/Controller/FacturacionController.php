<?php

namespace App\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Logger;
use App\Entity\Facturacion;
use App\Entity\FacturacionLineasConceptos;
use App\Entity\FacturacionLineasPagos;
use App\Entity\FacturacionVencimiento;
use App\Entity\GiroBancario;
use App\Entity\LogEnvioMail;
use App\Form\EnviarCorreoType;
use App\Form\FacturacionType;

class FacturacionController extends AbstractController
{
    public function createFactura(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getAddFacturacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $observaciones = null;
        $arrayEmpresaId = array();
        $empresa = $session->get('empresa');

        if (!is_null($empresa)) {
            $empresaId = $empresa->getId();
            $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
            array_push($arrayEmpresaId, $empresaId);
            $observaciones = $empresa->getObservacionesGenerales();
        } else {
            $empresas = $em->getRepository('App\Entity\Empresa')->findBy(array('anulado' => false));
            foreach ($empresas as $e) {
                array_push($arrayEmpresaId, $e->getId());
            }
        }
        $factura = new Facturacion();

        //Generamos el numero de contrato
        $hoy = new \DateTime();
        $year = $hoy->format('Y');
        $yearString = substr($year, 2, 4);

        //Calculamos el numero de factura
        $numeroFactura = $this->calcularNumeroFactura($year, 7);

        $factura->setFecha(new \DateTime());
        $factura->setNumFac($numeroFactura . '/' . $yearString);

        //Asignamos por defecto que la serie sea A - Factura
        $serie = $this->getDoctrine()->getRepository('App\Entity\SerieFactura')->find(7);
        $factura->setSerie($serie);

        $form = $this->createForm(FacturacionType::class, $factura, array('empresaId' => $arrayEmpresaId, 'empresaObj' => $empresa, 'contratoObj' => null, 'formaPagoObj' => null));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $factura = $form->getData();
            $em = $this->getDoctrine()->getManager();

            $fecha = $factura->getFecha();
            $anyo = $fecha->format('Y');

            //Asignado el codigo que le corresponde
            $codigoFactura = $this->calcularCodigoFactura($empresaId);

            $factura->setCodigo($codigoFactura);
            $factura->setAnyo($anyo);
            $em->persist($factura);
            $em->flush();

            $contratoFactura = $factura->getContrato();

            if (!is_null($contratoFactura)) {
                if (!$contratoFactura->getFacturado()) {
                    $contratoFactura->setFacturado(true);
                    $em->persist($contratoFactura);
                    $em->flush();
                }
            }
            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('factura_update', array('id' => $factura->getId()));
        }
        return $this->render('facturacion/edit.html.twig', array('observaciones' => $observaciones, 'formaPago' => null, 'vencimiento' => null, 'listConceptos' => null, 'form' => $form->createView(), 'lineasPagosFactura' => null, 'lineasConceptosFactura' => null));
    }

    public function dataFacturas(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $draw = intval($request->request->get('draw'));
            $start = $request->request->get('start');
            $length = $request->request->get('length');
            $search = $request->request->get('search');
            $orders = $request->request->get('order');
            $columns = $request->request->get('columns');
        } else {
            die();
        }
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');

        $dql = "SELECT a.id as id, b.id as empresaid, c.serie, a.numFac, a.fecha, a.renovacion, b.cif,b.empresa, b.trabajadores, IDENTITY(a.fichero) as fichero_id, d.descripcion as formapago, a.enviada, g.descripcion as agente, h.nombre as partner, 
                e.nombre as responsableadministrativo, f.nombre as medico
                FROM App\Entity\Facturacion a 
                JOIN a.empresa b
                JOIN a.serie c
                JOIN a.formaPago d
                LEFT JOIN b.gestorAdministrativo e
                LEFT JOIN b.vigilanciaSalud f
                LEFT JOIN b.agente g
                LEFT JOIN b.colaborador h
                WHERE a.anulado = false ";

        if ($search['value'] != "") {

            $search['value'] = str_replace('\'', "''", $search['value']);

            $queryLikes = "and (";
            foreach ($columns as $column) {

                if ($column['searchable'] != "false") {
                    $queryLikes .= "lower(" . $column['name'] . ") LIKE '%" . mb_strtolower($search['value']) . "%' OR ";
                }
            }
            $queryLikes = rtrim($queryLikes, "OR ");
            $queryLikes .= ") ";

            $dql .= $queryLikes;
        }
        //$dql .= "group by a.id, c.serie, b.empresa, b.marcaComercial, b.trabajadores, d.descripcion, e.nombre, f.nombre, g.descripcion, h.nombre ";

        $orderBy = "ORDER BY ";
        foreach ($orders as $order) {
            $columName = $columns[$order['column']]['name'];
            $orderBy .= $columName . ' ' . $order['dir'] . ', ';
        }
        $orderBy = rtrim($orderBy, ", ");
        $dql .= $orderBy;

        $query = $this->getDoctrine()->getManager()->createQuery($dql)
            ->setFirstResult($start)
            ->setMaxResults($length);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $paginator->setUseOutputWalkers(false);
        $recordsTotal = count($paginator);

        $arrayFacturas = array();
        foreach ($paginator as $r) {

            $actions = '<div class="list-icons">';
            if (!is_null($privilegios)) {

                if ($privilegios->getEditFacturacionSn()) {
                    $route = $this->generateUrl('factura_update', array('id' => $r['id']));
                    $actions .= '<a href="' . $route . '" target="_blank" class="list-icons-item" data-popup="tooltip" title="Editar" data-container="body"><i class="icon-pencil7"></i></a>';
                }
                if ($privilegios->getPrintFacturacionSn()) {
                    if (!is_null($r['fichero_id'])) {
                        $route = $this->generateUrl('gdoc_open_file', array('fileId' => $r['fichero_id'], 'tipo' => 2));
                        $actions .= '<a href="' . $route . '" target="_blank" class="list-icons-item" data-popup="tooltip" title="Imprimir" data-container="body"><i class="icon-printer2"></i></a>';
                        if ($privilegios->getDeleteGdocFileSn()) {
                            $actions .= '<a href="#" data-id="' . $r['fichero_id'] . '" class="list-icons-item eliminarFichero" data-popup="tooltip" title="Eliminar" data-container="body"><i class="icon-file-minus"></i></a>';
                        }
                    } else {
                        $actions .= '<a href="#" data-id="' . $r['id'] . '" class="list-icons-item selectFactura" data-popup="tooltip" title="Generar fichero" data-container="body" data-toggle="modal" data-target="#modal_generate_factura"><i class="icon-file-plus"></i></a>';
                    }
                }
                if ($privilegios->getDeleteFacturacionSn()) {
                    $actions .= '<a href="#" data-id="' . $r['id'] . '" class="list-icons-item eliminarFactura" data-popup="tooltip" title="Eliminar" data-container="body"><i class="icon-trash"></i></a>';
                }
            }
            $actions .= '</div>';

            $facturaId = $r['id'];
            $fecha = "";
            $fechatimestamp = "";
            if (!is_null($r['fecha'])) {
                $fecha = $r['fecha']->format('d/m/Y');
                $fechatimestamp = $r['fecha']->format('Ymdhi');
            }
            $empresaId = $r['empresaid'];

            $queryTecnicos = "select string_agg(tec2.nombre::text, ' , '::text) as tecnicos from tecnico_empresa tec
            inner join tecnico tec2 on tec.tecnico_id = tec2.id
            where tec.anulado = false and tec.empresa_id = $empresaId";

            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($queryTecnicos);
            $stmt->execute();
            $resultTecnicos = $stmt->fetchAll();
            $tecnicos = "";

            if (count($resultTecnicos) > 0) {
                $tecnicos = $resultTecnicos[0]['tecnicos'];
            }

            $queryPagos = "select a.concepto, a.importe_sin_iva, a.importe_total, a.importe_iva, a.unidades, b.codigo from facturacion_lineas_pagos a left join concepto b on a.concepto_facturacion_id = b.id where a.facturacion_id = $facturaId and a.anulado = false";

            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($queryPagos);
            $stmt->execute();
            $resultPagos = $stmt->fetchAll();

            if (count($resultPagos) > 0) {
                foreach ($resultPagos as $rp) {
                    $item = array();
                    $item['id'] = $facturaId;
                    $item['actions'] = $actions;
                    $item['num_fac'] = $r['serie'] . $r['numFac'];
                    $item['fecha'] = $fecha;
                    $item['fechatimestamp'] = $fechatimestamp;
                    $item['mes'] = $r['fecha']->format('m');
                    $item['any'] = $r['fecha']->format('Y');
                    $item['renovacion'] = $r['renovacion'];
                    $item['cif'] = $r['cif'];
                    $item['empresa'] = $r['empresa'];
                    $item['agente'] = $r['agente'];
                    $item['partner'] = $r['partner'];
                    $item['tecnico'] = $tecnicos;
                    $item['trabajadores'] = $r['trabajadores'];
                    $item['fichero_id'] = $r['fichero_id'];
                    $item['formapago'] = $r['formapago'];
                    $item['responsableadministrativo'] = $r['responsableadministrativo'];
                    $item['medico'] = $r['medico'];
                    $item['enviada'] = $r['enviada'];
                    $item['codigo'] = $rp['codigo'];
                    $item['cantidad'] = $rp['unidades'];
                    $item['texto'] = $rp['concepto'];
                    $item['importe'] = $rp['importe_total'];

                    if ($rp['unidades'] > 1 && $rp['importe_iva'] != 0) {
                        if (($rp['importe_total'] - $rp['importe_iva']) != $rp['importe_sin_iva']) {
                            $importeSinIva = $rp['importe_total'] - ($rp['importe_total'] * 0.21);
                            $item['importesiniva'] = $importeSinIva;
                            $item['iva'] = $item['importesiniva'] * 0.21;
                        } else {
                            $item['importesiniva'] = $rp['importe_sin_iva'];
                            $item['iva'] = $rp['importe_iva'];
                        }
                    } else {
                        $item['importesiniva'] = $rp['importe_sin_iva'];
                        $item['iva'] = $rp['importe_iva'];
                    }
                    if ($r['enviada'] == true) {
                        $item['enviada'] = '<span class="badge badge-flat border-success text-success-600"><i class="icon-checkmark2"></i></span>';
                    } else {
                        $item['enviada'] = '<span class="badge badge-flat border-danger text-danger-600"><i class="icon-cross3"></i></span>';
                    }
                    if ($r['renovacion'] == true) {
                        $item['renovada'] = '1';
                    } else {
                        $item['renovada'] = '0';
                    }
                    $item['input'] = '<div class="uniform-checker" id="uniform-' . $facturaId . '"><span><input type="checkbox" name="factura" id="' . $facturaId . '" class="form-check-input-styled form-check-input" value="1" /></span></div>';

                    array_push($arrayFacturas, $item);
                }
            } else {
                $queryConceptos = "select a.concepto, a.importe, a.importe_unidad, a.iva, a.unidades, a.iva_sn, a.concepto_facturacion_id, b.codigo from facturacion_lineas_conceptos a
                left join concepto b on a.concepto_facturacion_id = b.id
                where a.facturacion_id = $facturaId 
                and a.anulado = false";

                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($queryConceptos);
                $stmt->execute();
                $resultConceptos = $stmt->fetchAll();

                foreach ($resultConceptos as $rc) {
                    $item = array();
                    $item['id'] = $facturaId;
                    $item['actions'] = $actions;
                    $item['num_fac'] = $r['serie'] . $r['numFac'];
                    $item['fecha'] = $r['fecha'] !== null ? $r['fecha']->format('d/m/Y') : 'Fecha no disponible';
                    $item['fechatimestamp'] = $r['fecha'] !== null ? $r['fecha']->format('Ymdhi') : '000000';
                    $item['mes'] = $r['fecha'] !== null ? $r['fecha']->format('m') : '-';
                    $item['any'] = $r['fecha'] !== null ? $r['fecha']->format('Y') : '-';
                    $item['renovacion'] = $r['renovacion'];
                    $item['cif'] = $r['cif'];
                    $item['empresa'] = $r['empresa'];
                    $item['agente'] = $r['agente'];
                    $item['partner'] = $r['partner'];
                    $item['tecnico'] = $tecnicos;
                    $item['trabajadores'] = $r['trabajadores'];
                    $item['fichero_id'] = $r['fichero_id'];
                    $item['formapago'] = $r['formapago'];
                    $item['responsableadministrativo'] = $r['responsableadministrativo'];
                    $item['medico'] = $r['medico'];
                    $item['enviada'] = $r['enviada'];
                    $item['codigo'] = $rc['codigo'];
                    $item['cantidad'] = $rc['unidades'];
                    $item['texto'] = $rc['concepto'];
                    $item['importe'] = $rc['importe'];

                    if ($rc['unidades'] > 1 && $rc['iva'] != 0) {
                        if (($rc['importe'] - $rc['iva']) != $rc['importe_unidad']) {
                            $importeSinIva = $rc['importe'] - ($rc['importe'] * 0.21);
                            $item['importesiniva'] = $importeSinIva;
                            $item['iva'] = $item['importesiniva'] * 0.21;
                        } else {
                            $item['importesiniva'] = $rc['importe_unidad'] * $rc['unidades'];
                            $item['iva'] = $rc['iva'];
                        }
                    } else {
                        $item['importesiniva'] = $rc['importe_unidad'] * $rc['unidades'];
                        $item['iva'] = $rc['iva'];
                    }
                    if ($r['enviada'] == true) {
                        $item['enviada'] = '<span class="badge badge-flat border-success text-success-600"><i class="icon-checkmark2"></i></span>';
                    } else {
                        $item['enviada'] = '<span class="badge badge-flat border-danger text-danger-600"><i class="icon-cross3"></i></span>';
                    }
                    if ($r['renovacion'] == true) {
                        $item['renovada'] = '1';
                    } else {
                        $item['renovada'] = '0';
                    }
                    $item['input'] = '<div class="uniform-checker" id="uniform-' . $facturaId . '"><span><input type="checkbox" name="factura" id="' . $facturaId . '" class="form-check-input-styled form-check-input" value="1" /></span></div>';

                    array_push($arrayFacturas, $item);
                }
            }
        }
        return $this->json([
            'draw' => $draw,
            'recordsTotal' => $length,
            'recordsFiltered' => $recordsTotal,
            'data' => $arrayFacturas,
            'dql' => $dql,
            'dqlCountFiltered' => '',
        ]);
    }

    public function filtraFacturas(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $draw = intval($request->request->get('draw'));
            $start = $request->request->get('start');
            $length = $request->request->get('length');
            $search = $request->request->get('search');
            $orders = $request->request->get('order');
            $columns = $request->request->get('columns');

            $dtini = $_REQUEST['ini'];
            $dtfin = $_REQUEST['fin'];
            $renovada = $_REQUEST['renovada'];
            $enviada = $_REQUEST['enviada'];
        } else {
            die();
        }

        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');

        $dql = "SELECT a.id as id, b.id as empresaid, c.serie, a.numFac, a.fecha, a.renovacion, b.cif, b.empresa, b.trabajadores, IDENTITY(a.fichero) as fichero_id, d.descripcion as formapago, a.enviada, g.descripcion as agente, h.nombre as partner, 
                e.nombre as responsableadministrativo, f.nombre as medico
                FROM App\Entity\Facturacion a 
                JOIN a.empresa b
                JOIN a.serie c
                join a.formaPago d
                LEFT JOIN b.gestorAdministrativo e
                LEFT JOIN b.vigilanciaSalud f
                LEFT JOIN b.agente g
                LEFT JOIN b.colaborador h
                WHERE a.anulado = false ";

        if ($dtini != "") {
            $dql .= " and a.fecha >= '$dtini 00:00:00' ";
        }

        if ($dtfin != "") {
            $dql .= " and a.fecha <= '$dtfin 23:59:59' ";
        }

        if ($renovada != "") {
            switch ($renovada) {
                case '1':
                    $dql .= " and a.renovacion = true ";
                    break;
                case '0':
                    $dql .= " and a.renovacion = false ";
            }
        }

        if ($enviada != "") {
            switch ($enviada) {
                case '1':
                    $dql .= " and a.enviada = true ";
                    break;
                case '0':
                    $dql .= " and a.enviada = false ";
            }
        }

        /*
         *
         * Filtros
         *
         * */
        if ($search['value'] != "") {

            $search['value'] = str_replace('\'', "''", $search['value']);

            $queryLikes = "and (";
            foreach ($columns as $column) {

                if ($column['searchable'] != "false") {
                    $queryLikes .= "lower(" . $column['name'] . ") LIKE '%" . mb_strtolower($search['value']) . "%' OR ";
                }
            }
            $queryLikes = rtrim($queryLikes, "OR ");
            $queryLikes .= ") ";

            $dql .= $queryLikes;
        }

        //$dql .= "group by a.id, c.serie, b.empresa, b.marcaComercial, b.trabajadores, d.descripcion, e.nombre, f.nombre, g.descripcion, h.nombre ";

        /*
         *
         * Ordenaciones
         *
         * */
        $orderBy = "ORDER BY ";
        foreach ($orders as $order) {
            $columName = $columns[$order['column']]['name'];
            $orderBy .= $columName . ' ' . $order['dir'] . ', ';
        }
        $orderBy = rtrim($orderBy, ", ");
        $dql .= $orderBy;

        $query = $this->getDoctrine()->getManager()->createQuery($dql)
            ->setFirstResult($start)
            ->setMaxResults($length);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $paginator->setUseOutputWalkers(false);
        $recordsTotal = count($paginator);

        $arrayFacturas = array();
        foreach ($paginator as $r) {

            $actions = '<div class="list-icons">';
            if (!is_null($privilegios)) {

                if ($privilegios->getEditFacturacionSn()) {
                    $route = $this->generateUrl('factura_update', array('id' => $r['id']));
                    $actions .= '<a href="' . $route . '" target="_blank" class="list-icons-item" data-popup="tooltip" title="Editar" data-container="body"><i class="icon-pencil7"></i></a>';
                }

                if ($privilegios->getPrintFacturacionSn()) {
                    if (!is_null($r['fichero_id'])) {
                        $route = $this->generateUrl('gdoc_open_file', array('fileId' => $r['fichero_id'], 'tipo' => 2));
                        $actions .= '<a href="' . $route . '" target="_blank" class="list-icons-item" data-popup="tooltip" title="Imprimir" data-container="body"><i class="icon-printer2"></i></a>';
                        if ($privilegios->getDeleteGdocFileSn()) {
                            $actions .= '<a href="#" data-id="' . $r['fichero_id'] . '" class="list-icons-item eliminarFichero" data-popup="tooltip" title="Eliminar" data-container="body"><i class="icon-file-minus"></i></a>';
                        }
                    } else {
                        $actions .= '<a href="#" data-id="' . $r['id'] . '" class="list-icons-item selectFactura" data-popup="tooltip" title="Generar fichero" data-container="body" data-toggle="modal" data-target="#modal_generate_factura"><i class="icon-file-plus"></i></a>';
                    }
                }

                if ($privilegios->getDeleteFacturacionSn()) {
                    $actions .= '<a href="#" data-id="' . $r['id'] . '" class="list-icons-item eliminarFactura" data-popup="tooltip" title="Eliminar" data-container="body"><i class="icon-trash"></i></a>';
                }
            }

            $actions .= '</div>';

            $facturaId = $r['id'];
            $fecha = "";
            $fechatimestamp = "";
            if (!is_null($r['fecha'])) {
                $fecha = $r['fecha']->format('d/m/Y');
                $fechatimestamp = $r['fecha']->format('Ymdhi');
            }

            $empresaId = $r['empresaid'];

            $queryTecnicos = "select string_agg(tec2.nombre::text, ' , '::text) as tecnicos from tecnico_empresa tec
            inner join tecnico tec2 on tec.tecnico_id = tec2.id
            where tec.anulado = false and tec.empresa_id = $empresaId";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($queryTecnicos);
            $stmt->execute();
            $resultTecnicos = $stmt->fetchAll();
            $tecnicos = "";
            if (count($resultTecnicos) > 0) {
                $tecnicos = $resultTecnicos[0]['tecnicos'];
            }

            $queryPagos = "select a.concepto, a.importe_sin_iva, a.importe_total, a.importe_iva, a.unidades, b.codigo from facturacion_lineas_pagos a left join concepto b on a.concepto_facturacion_id = b.id where a.facturacion_id = $facturaId and a.anulado = false";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($queryPagos);
            $stmt->execute();
            $resultPagos = $stmt->fetchAll();

            if (count($resultPagos) > 0) {
                foreach ($resultPagos as $rp) {
                    $item = array();
                    $item['id'] = $facturaId;
                    $item['actions'] = $actions;
                    $item['num_fac'] = $r['serie'] . $r['numFac'];
                    $item['fecha'] = $fecha;
                    $item['fechatimestamp'] = $fechatimestamp;
                    $item['mes'] = $r['fecha']->format('m');
                    $item['any'] = $r['fecha']->format('Y');
                    $item['renovacion'] = $r['renovacion'];
                    $item['cif'] = $r['cif'];
                    $item['empresa'] = $r['empresa'];
                    $item['agente'] = $r['agente'];
                    $item['partner'] = $r['partner'];
                    $item['tecnico'] = $tecnicos;
                    $item['trabajadores'] = $r['trabajadores'];
                    $item['fichero_id'] = $r['fichero_id'];
                    $item['formapago'] = $r['formapago'];
                    $item['responsableadministrativo'] = $r['responsableadministrativo'];
                    $item['medico'] = $r['medico'];
                    $item['enviada'] = $r['enviada'];

                    $item['codigo'] = $rp['codigo'];
                    $item['cantidad'] = $rp['unidades'];
                    $item['texto'] = $rp['concepto'];
                    $item['importe'] = $rp['importe_total'];

                    if ($rp['unidades'] > 1 && $rp['importe_iva'] != 0) {
                        if (($rp['importe_total'] - $rp['importe_iva']) != $rp['importe_sin_iva']) {
                            $importeSinIva = $rp['importe_total'] - ($rp['importe_total'] * 0.21);
                            $item['importesiniva'] = $importeSinIva;
                            $item['iva'] = $item['importesiniva'] * 0.21;
                        } else {
                            $item['importesiniva'] = $rp['importe_sin_iva'];
                            $item['iva'] = $rp['importe_iva'];
                        }
                    } else {
                        $item['importesiniva'] = $rp['importe_sin_iva'];
                        $item['iva'] = $rp['importe_iva'];
                    }

                    if ($r['enviada'] == true) {
                        $item['enviada'] = '<span class="badge badge-flat border-success text-success-600"><i class="icon-checkmark2"></i></span>';
                    } else {
                        $item['enviada'] = '<span class="badge badge-flat border-danger text-danger-600"><i class="icon-cross3"></i></span>';
                    }

                    if ($r['renovacion'] == true) {
                        $item['renovada'] = '1';
                    } else {
                        $item['renovada'] = '0';
                    }

                    $item['input'] = '<div class="uniform-checker" id="uniform-' . $facturaId . '"><span><input type="checkbox" name="factura" id="' . $facturaId . '" class="form-check-input-styled form-check-input" value="1" /></span></div>';

                    array_push($arrayFacturas, $item);
                }
            } else {
                $queryConceptos = "select a.concepto, a.importe, a.importe_unidad, a.iva, a.unidades, a.iva_sn, a.concepto_facturacion_id, b.codigo from facturacion_lineas_conceptos a
                left join concepto b on a.concepto_facturacion_id = b.id
                where a.facturacion_id = $facturaId 
                and a.anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($queryConceptos);
                $stmt->execute();
                $resultConceptos = $stmt->fetchAll();

                foreach ($resultConceptos as $rc) {
                    $item = array();
                    $item['id'] = $facturaId;
                    $item['actions'] = $actions;
                    $item['num_fac'] = $r['serie'] . $r['numFac'];
                    $item['fecha'] = $fecha;
                    $item['fechatimestamp'] = $fechatimestamp;
                    $item['mes'] = $r['fecha']->format('m');
                    $item['any'] = $r['fecha']->format('Y');
                    $item['renovacion'] = $r['renovacion'];
                    $item['cif'] = $r['cif'];
                    $item['empresa'] = $r['empresa'];
                    $item['agente'] = $r['agente'];
                    $item['partner'] = $r['partner'];
                    $item['tecnico'] = $tecnicos;
                    $item['trabajadores'] = $r['trabajadores'];
                    $item['fichero_id'] = $r['fichero_id'];
                    $item['formapago'] = $r['formapago'];
                    $item['responsableadministrativo'] = $r['responsableadministrativo'];
                    $item['medico'] = $r['medico'];
                    $item['enviada'] = $r['enviada'];

                    $item['codigo'] = $rc['codigo'];
                    $item['cantidad'] = $rc['unidades'];
                    $item['texto'] = $rc['concepto'];
                    $item['importe'] = $rc['importe'];

                    if ($rc['unidades'] > 1 && $rc['iva'] != 0) {
                        if (($rc['importe'] - $rc['iva']) != $rc['importe_unidad']) {
                            $importeSinIva = $rc['importe'] - ($rc['importe'] * 0.21);
                            $item['importesiniva'] = $importeSinIva;
                            $item['iva'] = $item['importesiniva'] * 0.21;
                        } else {
                            $item['importesiniva'] = $rc['importe_unidad'] * $rc['unidades'];
                            $item['iva'] = $rc['iva'];
                        }
                    } else {
                        $item['importesiniva'] = $rc['importe_unidad'] * $rc['unidades'];
                        $item['iva'] = $rc['iva'];
                    }

                    if ($r['enviada'] == true) {
                        $item['enviada'] = '<span class="badge badge-flat border-success text-success-600"><i class="icon-checkmark2"></i></span>';
                    } else {
                        $item['enviada'] = '<span class="badge badge-flat border-danger text-danger-600"><i class="icon-cross3"></i></span>';
                    }

                    if ($r['renovacion'] == true) {
                        $item['renovada'] = '1';
                    } else {
                        $item['renovada'] = '0';
                    }

                    $item['input'] = '<div class="uniform-checker" id="uniform-' . $facturaId . '"><span><input type="checkbox" name="factura" id="' . $facturaId . '" class="form-check-input-styled form-check-input" value="1" /></span></div>';

                    array_push($arrayFacturas, $item);
                }
            }
        }

        return $this->json([
            'draw' => $draw,
            'recordsTotal' => $length,
            'recordsFiltered' => $recordsTotal,
            'data' => $arrayFacturas,
            'dql' => $dql,
            'dqlCountFiltered' => ''
        ]);
    }

    public function showFacturas(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getFacturacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        //$facturas = $this->buscaFacturas("", "", "", false, false);

        //Buscamos las plantillas de la carpeta facturas
        $carpetaContratos = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(6);
        $plantillas = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaContratos, 'anulado' => false));

        $object = array("json" => $username, "entidad" => "facturas", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('facturacion/show.html.twig', array('listPlantillas' => $plantillas));
    }

    public function showFacturasPagadas(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getFacturacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $facturas = $this->buscaFacturas("", "", "", true, false);

        //Buscamos las plantillas de la carpeta facturas
        $carpetaContratos = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(6);
        $plantillas = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaContratos, 'anulado' => false));

        $object = array("json" => $username, "entidad" => "facturas pagadas", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('facturacion/show.html.twig', array('listPlantillas' => $plantillas, 'facturas' => $facturas));
    }

    public function showFacturasNoPagadas(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getFacturacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $facturas = $this->buscaFacturas("", "", "", false, true);

        //Buscamos las plantillas de la carpeta facturas
        $carpetaContratos = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(6);
        $plantillas = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaContratos, 'anulado' => false));

        $object = array("json" => $username, "entidad" => "facturas renovadas", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('facturacion/show.html.twig', array('listPlantillas' => $plantillas, 'facturas' => $facturas));
    }

    public function showFacturasCancelada(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getFacturacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, concat(c.serie,a.num_fac) as num_fac, to_char(a.fecha, 'DD/MM/YYYY') as fecha, to_char(a.fecha, 'YYYYMMDDHHmm') as fechatimestamp, to_char(a.fecha, 'MM') as mes, to_char(a.fecha, 'YYYY') as any,
            a.renovacion,
            b.empresa,
            b.marca_comercial, 
            (select distinct t.nombre from tecnico_empresa te inner join tecnico t on te.tecnico_id = t.id where te.anulado = false and te.empresa_id = a.empresa_id ) as tecnico, 
            b.trabajadores, a.fichero_id, d.descripcion as formaPago, e.nombre as responsableAdministrativo, f.nombre as medico,
            g.concepto as texto,
            g.importe_total as importe,
            g.importe_sin_iva as importeSinIva,
            (select b1.codigo from facturacion_lineas_conceptos a1 inner join concepto b1 on a1.concepto_facturacion_id = b1.id where a1.facturacion_id = a.id limit 1) as codigo
            from facturacion a
            inner join empresa b on a.empresa_id = b.id
            inner join serie_factura c on a.serie_id = c.id
            inner join forma_pago d on a.forma_pago_id = d.id
            left join tecnico e on b.gestor_administrativo_id = e.id
            left join tecnico f on b.vigilancia_salud_id = f.id
            inner join facturacion_lineas_pagos g on a.id = g.facturacion_id
            where a.anulado = false
            and b.anulado = false
            and a.cancelada = true
            group by a.id, c.serie, b.empresa, b.marca_comercial, b.trabajadores, d.descripcion, e.nombre, f.nombre, g.concepto, g.importe_total, g.importe_sin_iva 
            order by a.fecha desc";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $facturas = $stmt->fetchAll();

        //Buscamos las plantillas de la carpeta facturas
        $carpetaContratos = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(6);
        $plantillas = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaContratos, 'anulado' => false));

        $object = array("json" => $username, "entidad" => "facturas canceladas", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('facturacion/show.html.twig', array('listPlantillas' => $plantillas, 'facturas' => $facturas));
    }

    public function deleteFactura($id, Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getDeleteFacturacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $factura = $em->getRepository('App\Entity\Facturacion')->find($id);

        if (!$factura) {
            throw $this->createNotFoundException(
                'La factura con id ' . $id . ' no existe'
            );
        }

        $factura->setAnulado(true);
        $factura->setCancelada(true);
        $em->persist($factura);
        $em->flush();

        $contratoFactura = $factura->getContrato();
        if (!is_null($contratoFactura)) {
            $contratoFactura->setFacturado(false);
            $em->persist($contratoFactura);
            $em->flush();
        }

        //Buscamos si hay alguna factura rectificativa
        $facturaRectificativa = $this->getDoctrine()->getRepository('App\Entity\Facturacion')->findBy(array('anulado' => false, 'facturaRectificativa' => $factura));
        foreach ($facturaRectificativa as $fr) {
            $fr->setFacturaRectificativa(null);
            $em->persist($fr);
            $em->flush();
        }

        //Buscamos si hay revisiones con esta factura
        $estadoFirmada = $this->getDoctrine()->getRepository('App\Entity\EstadoRevision')->find(3);
        $revisionesFactura = $em->getRepository('App\Entity\Revision')->findBy(array('factura' => $factura, 'anulado' => false));
        foreach ($revisionesFactura as $rf) {
            $rf->setEstado($estadoFirmada);
            $rf->setFactura(null);
            $em->persist($rf);
            $em->flush();
        }

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);
        return $this->redirectToRoute('empresa_update', array('id' => $factura->getEmpresa()->getId()));
    }

    public function updateFactura(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditFacturacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $factura = $em->getRepository('App\Entity\Facturacion')->find($id);

        $arrayEmpresaId = array();
        //Comprobamos si tiene una empresa seleccionada sino le asignamos la de la factura
        $empresaId = $factura->getEmpresa()->getId();
        $empresa = $factura->getEmpresa();
        $session->set('empresa', $empresa);
        array_push($arrayEmpresaId, $empresaId);

        if (!$factura) {
            throw $this->createNotFoundException(
                'La factura con id ' . $id . ' no existe'
            );
        }

        //Buscamos las lineas de pagos de la factura
        $lineasPagosFactura = $em->getRepository('App\Entity\FacturacionLineasPagos')->findBy(array('facturacion' => $factura, 'anulado' => false));

        //Buscamos las lineas de conceptos de la factura
        $lineasConceptosFactura = $em->getRepository('App\Entity\FacturacionLineasConceptos')->findBy(array('facturacion' => $factura, 'anulado' => false));

        //Buscamos los conceptos
        $conceptosFactura = $em->getRepository('App\Entity\Concepto')->findBy(array('anulado' => false));

        //Comprobamos si la factura tiene un vencimiento creado
        $facturaVencimiento = $em->getRepository('App\Entity\FacturacionVencimiento')->findOneBy(array('anulado' => false, 'facturaAsociada' => $factura));

        $vencimiento = false;
        if (!is_null($facturaVencimiento)) {
            $vencimiento = true;
        }

        $giroBancario = false;
        if (!is_null($factura->getFormaPago())) {
            if ($factura->getFormaPago()->getFormaPagoContable() == 8) {
                $gironBancarioObj = $em->getRepository('App\Entity\GiroBancario')->findOneBy(array('anulado' => false, 'facturacion' => $factura));
                if (is_null($gironBancarioObj)) {
                    $giroBancario = true;
                }
                $vencimiento = true;
            }
        }

        //Buscamos las formas de pago
        $formaPago = $em->getRepository('App\Entity\FormaPago')->findAll();

        $serieSn = false;
        $serieId = null;
        if (!is_null($factura->getSerie())) {
            $serieId = $factura->getSerie()->getId();
            if ($factura->getSerie()->getId() == 7) {
                $serieSn = true;
            }
        }

        $form = $this->createForm(FacturacionType::class, $factura, array('empresaId' => $arrayEmpresaId, 'empresaObj' => $empresa, 'contratoObj' => $factura->getContrato(), 'formaPagoObj' => $factura->getFormaPago()));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $factura = $form->getData();
            $em->persist($factura);
            $em->flush();

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('factura_update', array('id' => $id));
        }

        return $this->render('facturacion/edit.html.twig',  array('serieId' => $serieId, 'formaPago' => $formaPago, 'giroBancario' => $giroBancario, 'vencimiento' => $vencimiento, 'listConceptos' => $conceptosFactura, 'form' => $form->createView(), 'lineasPagosFactura' => $lineasPagosFactura, 'lineasConceptosFactura' => $lineasConceptosFactura, 'serieSn' => $serieSn));
    }

    public function buscaDatosEmpresa(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $empresaId = $_REQUEST['empresaId'];

        $formaPagoId = null;

        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        $datosBancarios = $em->getRepository('App\Entity\DatosBancarios')->findOneBy(array('principal' => true, 'empresa' => $empresa, 'anulado' => false));
        if (!is_null($datosBancarios)) {
            $formaPago = $datosBancarios->getFormaPago();
            if (!is_null($formaPago)) {
                $formaPagoId = $formaPago->getId();
            }
        } else {
            $formaPago = $empresa->getFormaPago();
            if (!is_null($formaPago)) {
                $formaPagoId = $formaPago->getId();
            }
        }

        $data = array(
            'direccion' => $empresa->getDomicilioFiscal(),
            'cif' => $empresa->getCif(),
            'formaPagoId' => $formaPagoId,
            'observaciones' => $empresa->getObservacionesGenerales()
        );

        return new JsonResponse($data);
    }

    public function recuperaPagos(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $facturaId = $_REQUEST['facturaId'];

        $query = $em->createQuery(
            'SELECT c
            FROM App:FacturacionLineasPagos c
            where c.facturacion = ' . $facturaId . '
            and c.anulado = false'
        );

        $lineasPagos = $query->getArrayResult();
        return new JsonResponse(json_encode($lineasPagos));
    }

    public function addPago(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $facturaId = $_REQUEST['facturaId'];
        $concepto = $_REQUEST['concepto'];
        $porcentaje = $_REQUEST['porcentaje'];
        $exento = $_REQUEST['exento'];
        $sujeto = $_REQUEST['sujeto'];
        $sinIva = $_REQUEST['sinIva'];
        $iva = $_REQUEST['iva'];
        $total = $_REQUEST['total'];

        $factura = $em->getRepository('App\Entity\Facturacion')->find($facturaId);

        $conceptoFacturacionPago = null;

        $contrato = $factura->getContrato();
        if (!is_null($contrato)) {
            //Buscamos la modalidad del contrato
            $modalidadContratoId = $contrato->getContratoModalidad()->getId();
            switch ($modalidadContratoId) {
                case 1:
                    $conceptoFacturacionPago = $em->getRepository('App\Entity\Concepto')->findOneBy(array('descripcion' => 'PRL SIN SALUD', 'anulado' => false));
                    break;
                case 2:
                    $conceptoFacturacionPago = $em->getRepository('App\Entity\Concepto')->findOneBy(array('descripcion' => 'SALUD', 'anulado' => false));
                    break;
                case 3:
                    $conceptoFacturacionPago = $em->getRepository('App\Entity\Concepto')->findOneBy(array('descripcion' => 'PRL+SALUD', 'anulado' => false));
                    break;
                default:
                    $conceptoFacturacionPago = null;
            }
        }

        $lineaPago = new FacturacionLineasPagos();
        $lineaPago->setPorcentaje($porcentaje);
        $lineaPago->setConcepto($concepto);
        $lineaPago->setImporteExentoIva($exento);
        $lineaPago->setImporteSujetoIva($sujeto);
        $lineaPago->setImporteSinIva($sinIva);
        $lineaPago->setImporteIva($iva);
        $lineaPago->setImporteTotal($total);
        $lineaPago->setFacturacion($factura);
        $lineaPago->setConceptoFacturacion($conceptoFacturacionPago);
        $em->persist($lineaPago);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function updatePago(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $pagoId = $_REQUEST['pagoId'];
        $concepto = $_REQUEST['concepto'];
        $porcentaje = $_REQUEST['porcentaje'];
        $exento = $_REQUEST['exento'];
        $sujeto = $_REQUEST['sujeto'];
        $sinIva = $_REQUEST['sinIva'];
        $iva = $_REQUEST['iva'];
        $total = $_REQUEST['total'];

        $lineaPago = $em->getRepository('App\Entity\FacturacionLineasPagos')->find($pagoId);

        $lineaPago->setPorcentaje($porcentaje);
        $lineaPago->setConcepto($concepto);
        $lineaPago->setImporteExentoIva($exento);
        $lineaPago->setImporteSujetoIva($sujeto);
        $lineaPago->setImporteSinIva($sinIva);
        $lineaPago->setImporteIva($iva);
        $lineaPago->setImporteTotal($total);
        $em->persist($lineaPago);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function recuperaPago(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $lineaPagoId = $_REQUEST['lineaPagoId'];

        $lineaPago = $em->getRepository('App\Entity\FacturacionLineasPagos')->find($lineaPagoId);

        $data = array(
            'id' => $lineaPago->getId(),
            'concepto' => $lineaPago->getConcepto(),
            'porcentaje' => $lineaPago->getPorcentaje(),
            'exento' => $lineaPago->getImporteExentoIva(),
            'sujeto' => $lineaPago->getImporteSujetoIva(),
            'sinIva' => $lineaPago->getImporteSinIva(),
            'iva' => $lineaPago->getImporteIva(),
            'total' => $lineaPago->getImporteTotal()
        );

        return new JsonResponse($data);
    }

    public function deletePago(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $pagoId = $_REQUEST['pagoId'];
        $lineaPago = $em->getRepository('App\Entity\FacturacionLineasPagos')->find($pagoId);

        $lineaPago->setAnulado(true);
        $em->persist($lineaPago);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function recuperaConceptos(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $facturaId = $_REQUEST['facturaId'];

        $query = $em->createQuery(
            'SELECT c
            FROM App:FacturacionLineasConceptos c
            where c.facturacion = ' . $facturaId . '
            and c.anulado = false'
        );

        $lineasConceptos = $query->getArrayResult();
        return new JsonResponse(json_encode($lineasConceptos));
    }

    public function addConcepto(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $facturaId = $_REQUEST['facturaId'];
        $conceptoDesc = $_REQUEST['concepto'];
        $importe = $_REQUEST['importe'];
        $iva = $_REQUEST['iva'];
        $total = $_REQUEST['total'];
        $conceptoObj = $_REQUEST['conceptoObj'];
        $cantidad = $_REQUEST['cantidad'];

        if ($conceptoObj != "") {
            $concepto = $em->getRepository('App\Entity\Concepto')->find($conceptoObj);
        }

        $factura = $em->getRepository('App\Entity\Facturacion')->find($facturaId);

        $lineaConcepto = new FacturacionLineasConceptos();
        $lineaConcepto->setConcepto($conceptoDesc);
        $lineaConcepto->setImporteUnidad($importe);
        $lineaConcepto->setIva($iva);
        $lineaConcepto->setImporte($total);
        $lineaConcepto->setFacturacion($factura);

        if ($cantidad == "") {
            $lineaConcepto->setUnidades(1);
        } else {
            $lineaConcepto->setUnidades($cantidad);
        }

        if ($conceptoObj != "") {
            $lineaConcepto->setConceptoFacturacion($concepto);
        }

        if ($iva == "" || $iva == 0) {
            $lineaConcepto->setIvaSn(false);
        } else {
            $lineaConcepto->setIvaSn(true);
        }

        $em->persist($lineaConcepto);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function updateConcepto(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $conceptoId = $_REQUEST['conceptoId'];
        $conceptoDesc = $_REQUEST['concepto'];
        $importe = $_REQUEST['importe'];
        $iva = $_REQUEST['iva'];
        $total = $_REQUEST['total'];
        $conceptoObj = $_REQUEST['conceptoObj'];
        $cantidad = $_REQUEST['cantidad'];

        $lineaConcepto = $em->getRepository('App\Entity\FacturacionLineasConceptos')->find($conceptoId);

        if ($conceptoObj != "") {
            $concepto = $em->getRepository('App\Entity\Concepto')->find($conceptoObj);
        }
        $lineaConcepto->setConcepto($conceptoDesc);
        $lineaConcepto->setImporteUnidad($importe);
        $lineaConcepto->setIva($iva);
        $lineaConcepto->setImporte($total);

        if ($cantidad == "") {
            $lineaConcepto->setUnidades(1);
        } else {
            $lineaConcepto->setUnidades($cantidad);
        }
        if ($conceptoObj != "") {
            $lineaConcepto->setConceptoFacturacion($concepto);
        }
        if ($iva == "" || $iva == 0) {
            $lineaConcepto->setIvaSn(false);
        } else {
            $lineaConcepto->setIvaSn(true);
        }
        $em->persist($lineaConcepto);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function recuperaConcepto(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $lineaConceptoId = $_REQUEST['lineaConceptoId'];

        $lineaConcepto = $em->getRepository('App\Entity\FacturacionLineasConceptos')->find($lineaConceptoId);

        if (is_null($lineaConcepto->getUnidades())) {
            $cantidad = 1;
        } else {
            $cantidad =  $lineaConcepto->getUnidades();
        }
        $data = array(
            'id' => $lineaConcepto->getId(),
            'concepto' => $lineaConcepto->getConcepto(),
            'importe' => $lineaConcepto->getImporteUnidad(),
            'iva' => $lineaConcepto->getIva(),
            'total' => $lineaConcepto->getImporte(),
            'cantidad' => $cantidad
        );
        return new JsonResponse($data);
    }

    public function deleteConcepto(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $conceptoId = $_REQUEST['conceptoId'];
        $lineaConcepto = $em->getRepository('App\Entity\FacturacionLineasConceptos')->find($conceptoId);
        $lineaConcepto->setAnulado(true);
        $em->persist($lineaConcepto);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function filtroFacturas(Request $request)
    {
        $ini = $_REQUEST['ini'];
        $fin = $_REQUEST['fin'];
        $pagadaSn = $_REQUEST['pagadaSn'];
        $noPagadaSn = $_REQUEST['noPagadaSn'];
        $renovada = $_REQUEST['renovada'];

        if ($pagadaSn == "true") {
            $pagadaSn = true;
        } else {
            $pagadaSn = false;
        }

        if ($noPagadaSn == "true") {
            $noPagadaSn = true;
        } else {
            $noPagadaSn = false;
        }

        $facturas = $this->buscaFacturas($ini, $fin, $renovada, $pagadaSn, $noPagadaSn);

        $total = 0;
        $iva = 0;
        $base = 0;
        $countFacturas = 0;

        if ($ini != "" || $fin != "") {
            $query = "select distinct a.num_fac, a.serie_id from facturacion a where a.anulado = false ";

            if ($ini != "") {
                $query .= " and a.fecha >= '$ini 00:00:00' ";
            }

            if ($fin != "") {
                $query .= " and a.fecha <= '$fin 23:59:59' ";
            }

            if ($renovada != "") {
                switch ($renovada) {
                    case '1':
                        $query .= " and a.renovacion = true ";
                        break;
                    case '0':
                        $query .= " and a.renovacion = false ";
                }
            }

            if ($pagadaSn) {
                $query .= " and a.serie_id = 7 and a.id in (select facturacion_id from facturacion_lineas_pagos where anulado = false and facturado = true) ";
            }

            if ($noPagadaSn) {
                $query .= " and a.serie_id = 7 and a.id not in (select facturacion_id from facturacion_lineas_pagos where anulado = false) ";
            }

            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultCountFacturas = $stmt->fetchAll();
            $countFacturas = count($resultCountFacturas);

            foreach ($facturas as $f) {
                $base = $base + $f['importesiniva'];
                $iva = $iva + $f['iva'];
                $total = $total + $f['importe'];
            }

            $base = round($base, 2);
            $total = round($total, 2);
            $iva = round($iva, 2);
        }

        $array = array(
            'facturas' => json_encode($facturas),
            'numero' => $countFacturas,
            'total' => number_format($total, 2, ',', '.'),
            'iva' => number_format($iva, 2, ',', '.'),
            'base' => number_format($base, 2, ',', '.')
        );

        return new JsonResponse($array);
    }

    public function recuperaContratos(Request $request)
    {
        $empresaId = $_REQUEST['empresaId'];

        $query = "select a.id, a.contrato || ' - ' || b.empresa as descripcion from contrato a inner join empresa b on a.empresa_id = b.id where a.empresa_id = $empresaId and b.id = $empresaId and a.anulado = false order by a.fechainicio desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $contratos = $stmt->fetchAll();

        return new JsonResponse(json_encode($contratos));
    }

    public function recuperaNumeracion(Request $request)
    {

        $serieId = $_REQUEST['serieId'];

        //Generamos el numero de contrato
        $hoy = new \DateTime();
        $year = $hoy->format('Y');
        $yearString = substr($year, 2, 4);

        $numeroFactura = $this->calcularNumeroFactura($year, $serieId);

        $data = array(
            'num' => $numeroFactura . '/' . $yearString
        );

        return new JsonResponse($data);
    }

    public function generarAbono(Request $request, TranslatorInterface $translator)
    {

        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $id = $_REQUEST['id'];
        $fecha = $_REQUEST['fecha'];

        $fechaAbono = new \DateTime($fecha);

        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        $factura = $em->getRepository('App\Entity\Facturacion')->find($id);

        //Obtenemos el objeto de la serie abono
        $serieAbono = $em->getRepository('App\Entity\SerieFactura')->find(6);

        //Obtenemos el año actual
        $hoy = new \DateTime();
        $year = $hoy->format('Y');
        $yearString = substr($year, 2, 4);

        //Buscamos el numero que le corresponde a la serie ABONO
        $numeroFactura = $this->calcularNumeroFactura($year, 6);

        //Obtenemos el codigo de la factura de la empresa
        $codigoFactura = $this->calcularCodigoFactura($empresaId);

        $newFactura = clone $factura;
        $newFactura->setFacturaAsociada($factura);
        $newFactura->setNumFac($numeroFactura . '/' . $yearString);
        $newFactura->setSerie($serieAbono);
        $newFactura->setFichero(null);
        $newFactura->setPagada(false);
        $newFactura->setRenovacion(false);
        $newFactura->setCancelada(false);
        $newFactura->setAnyo($year);
        $newFactura->setCodigo($codigoFactura);
        $newFactura->setNumero($numeroFactura);
        $newFactura->setFecha($fechaAbono);
        $em->persist($newFactura);
        $em->flush();

        //Buscamos los conceptos de la factura
        $conceptosFactura = $em->getRepository('App\Entity\FacturacionLineasConceptos')->findBy(array('facturacion' => $factura, 'anulado' => false));
        foreach ($conceptosFactura as $conceptoFactura) {
            $importe = $conceptoFactura->getImporte();
            $iva = $conceptoFactura->getIva();
            $importeUnidad = $conceptoFactura->getImporteUnidad();

            $newConceptoFactura = clone $conceptoFactura;
            $newConceptoFactura->setImporte(-$importe);
            $newConceptoFactura->setIva(-$iva);
            $newConceptoFactura->setImporteUnidad(-$importeUnidad);
            $newConceptoFactura->setFacturacion($newFactura);
            $em->persist($newConceptoFactura);
            $em->flush();
        }

        //Buscamos los pagos de la factura
        $pagosFactura = $em->getRepository('App\Entity\FacturacionLineasPagos')->findBy(array('facturacion' => $factura, 'anulado' => false));
        foreach ($pagosFactura as $pagoFactura) {
            $importeSinIva = $pagoFactura->getImporteSinIva();
            $importeExentoIva = $pagoFactura->getImporteExentoIva();
            $importeSujetoIva = $pagoFactura->getImporteSujetoIva();
            $importeIva = $pagoFactura->getImporteIva();
            $importeTotal = $pagoFactura->getImporteTotal();

            $newPagoFactura = clone $pagoFactura;
            $newPagoFactura->setImporteSinIva(-$importeSinIva);
            $newPagoFactura->setImporteExentoIva(-$importeExentoIva);
            $newPagoFactura->setImporteSujetoIva(-$importeSujetoIva);
            $newPagoFactura->setImporteIva(-$importeIva);
            $newPagoFactura->setImporteTotal(-$importeTotal);
            $newPagoFactura->setFacturacion($newFactura);
            $em->persist($newPagoFactura);
            $em->flush();
        }

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function sendFactura(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');

        if (!is_null($privilegios)) {
            if (!$privilegios->getSendFacturacionSn()) {
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
        $facturaRepo = $em->getRepository('App\Entity\Facturacion');

        $facturasEnviar = $_REQUEST['facturas'];
        $facturasEnviarArray = explode(",", $facturasEnviar);

        $nombresFacturas = array();

        //Buscamos las facturas que se enviaran y las mostramos al usuario
        for ($i = 0; $i < count($facturasEnviarArray); $i++) {
            $facturaId = $facturasEnviarArray[$i];
            $factura = $facturaRepo->find($facturaId);

            $fichero = $factura->getFichero();
            if (!is_null($fichero)) {
                array_push($nombresFacturas, str_replace('docx', 'pdf', $fichero->getNombre()));
            }
        }
        //Buscamos de la empresa el correo o correos para enviar facturas
        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        $destinatarios = null;
        $funcionEnviarCorreo = $em->getRepository('App\Entity\FuncionCorreo')->find(2);
        $correosEnviarFactura = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false, 'funcion' => $funcionEnviarCorreo));

        foreach ($correosEnviarFactura as $cef) {
            $destinatarios .= $cef->getCorreo() . ';';
        }
        $destinatarios = rtrim($destinatarios, ";");

        $form = $this->createForm(EnviarCorreoType::class, null, array('destinatario' => $destinatarios, 'cco' => $emailUser));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //Obtenemos los datos de configuracion de la gestion documental
            $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
            $rutaCompleta = $gdocConfig->getRuta();
            $carpetaGenerada = $gdocConfig->getCarpetaFactura();
            $carpetaTemporal = $gdocConfig->getCarpetaTemporal();
            $passwordUsuario = $gdocConfig->getPasswordFicherosEncriptados();

            if (is_null($mail) || is_null($passwordMail) || is_null($puertoMail) || is_null($hostMail) || is_null($encriptacionMail) || is_null($userMail)) {
                $traduccion = $translator->trans('TRANS_AVISO_NO_CONFIGURACION_CORREO');
                $this->addFlash('danger', $traduccion);
                return $this->redirectToRoute('empresa_update', array('id' => $empresaId));
            }
            //Recogemos lo datos
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
            $message->setReplyTo($emailUser);
            $message->setTo(explode(";", $para));

            if (!is_null($cc) && $cc != "") {
                $message->setCc(explode(";", $cc));
            }
            if (!is_null($cco) && $cco != "") {
                $message->setBcc(explode(";", $cco));
            }
            $message->setBody($mensaje, 'text/plain');

            //Buscamos las facturas y adjuntamos el pdf al correo
            for ($i = 0; $i < count($facturasEnviarArray); $i++) {
                $facturaId = $facturasEnviarArray[$i];
                $factura = $facturaRepo->find($facturaId);
                $fichero = $factura->getFichero();

                //Convertimos el word en pdf
                $fileDocx = $rutaCompleta . $carpetaGenerada . '/' . $fichero->getNombre();

                $filePdf = str_replace('docx', 'pdf', $fileDocx);
                $outdir = $rutaCompleta . $carpetaGenerada;

                $cmd = 'HOME=/tmp /usr/bin/soffice --headless --convert-to pdf:writer_pdf_Export "' . $fileDocx . '" --outdir "' . $outdir . '"';
                exec($cmd);

                //Encriptamos el documento
                $passwordOwner = $factura->getPasswordPdf();
                if (is_null($passwordOwner)) {
                    $facturacionId = $factura->getId();
                    $passwordOwner = hash('sha256', 'OpenticPrevencion' . $facturacionId);
                    $factura->setPasswordPdf($passwordOwner);
                }
                $nombrePlantillaRestriccionPdf = str_replace('docx', 'pdf', $fichero->getNombre());
                $filePdfEncriptado = $rutaCompleta . $carpetaTemporal . '/' . $nombrePlantillaRestriccionPdf;

                $this->encriptarPdf($filePdfEncriptado, $filePdf, $passwordOwner, $passwordUsuario);

                //Lo adjuntamos al correo
                $message->attach(\Swift_Attachment::fromPath($filePdfEncriptado));

                //Marcamos la factura como enviada
                $factura->setEnviada(true);
                $factura->setFechaEnvio(new \DateTime());
                $em->persist($factura);
                $em->flush();
                //unlink($filePdf);
            }
            $facturasNoEnviadas = "";

            try {
                // Enviamos el correo
                $mailer->send($message);
            } catch (\Exception $e) {
                $facturasNoEnviadas .= $factura->getEmpresa()->getEmpresa() . " , ";
                $mailer->getTransport()->stop();
                $mensajeError = $translator->trans('TRANS_ENVIAR_CORREO_ERROR');
            }
            if ($facturasNoEnviadas != "") {
                $traduccion = $translator->trans('TRANS_ENVIAR_FACTURA_ERROR_EMPRESA');
                // Mostramos primero el mensaje traducido
                $this->addFlash('danger', $traduccion . " " . $facturasNoEnviadas);
                // Luego mostramos el mensaje de error
                if (isset($mensajeError)) {
                    $this->addFlash('danger', $mensajeError);
                }
            } else {
                // Insertamos el correo en el log
                $this->insertLogMail($em, $usuario, $asunto, $para, $message->getBody(), "Envío de facturas");

                $traduccion = $translator->trans('TRANS_SEND_OK');
                $this->addFlash('success', $traduccion);
            }
            return $this->redirectToRoute('empresa_update', array('id' => $empresaId));
        }
        return $this->render('emails/send_email.html.twig', array('form' => $form->createView(), 'ficherosEnviar' => $nombresFacturas));
    }

    public function generaVencimientoManual(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $facturaId = $_REQUEST['facturaId'];
        $tipoFechaVencimiento = $_REQUEST['tipoFechaVencimiento'];
        $fechaVencimiento = $_REQUEST['fechaVencimiento'];
        $ignorarDiaPago = $_REQUEST['ignorarDiaPago'];
        $confirmarCobro = $_REQUEST['confirmarCobro'];
        $vencimientoCobro = $_REQUEST['vencimientoCobro'];
        $fraccionamientoCobro = $_REQUEST['fraccionamientoCobro'];
        $formaPagoId = $_REQUEST['formaPago'];
        $numeroCobros = $_REQUEST['numeroCobros'];
        $cadaCuando = $_REQUEST['cadaCuando'];
        $frecuencia = $_REQUEST['frecuencia'];

        $confirmado = false;
        if ($confirmarCobro == 'true') {
            $confirmado = true;
        }

        $facturaAsociada = $em->getRepository('App\Entity\Facturacion')->find($facturaId);

        if ($tipoFechaVencimiento == '1') {
            $dateVencimiento = $facturaAsociada->getFecha();
        }

        if ($tipoFechaVencimiento == '2') {
            $dateVencimiento = new \DateTime($fechaVencimiento);
        }

        $numeroFactura = $facturaAsociada->getSerie()->getSerie() . $facturaAsociada->getNumFac();

        //Si la forma de pago no está informada cogemos la que viene por parametro
        if ($formaPagoId != "") {
            $formaPagoObj = $em->getRepository('App\Entity\FormaPago')->find($formaPagoId);
            $formaPagoDesc = $formaPagoObj->getDescripcion();
        } else {
            $formaPagoObj = $facturaAsociada->getFormaPago();
            $formaPagoDesc = $facturaAsociada->getFormaPago()->getDescripcion();
        }

        //Calculamos el importe de la factura
        $importe = $this->calcularImporteFactura($facturaId);

        $em->beginTransaction();

        try {
            $this->generarVencimientoFacturaManual($em, $vencimientoCobro, $ignorarDiaPago, $facturaAsociada, $dateVencimiento, $numeroFactura, $formaPagoDesc, $importe, $formaPagoObj, $confirmado, $fraccionamientoCobro, $numeroCobros, $frecuencia, $cadaCuando);
        } catch (\Exception $e) {
            $em->rollBack();
            throw $e;
        }

        $em->commit();

        $traduccion = $translator->trans('TRANS_VENCIMIENTO_GENERADO');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function generaVencimientoAutomatico(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $factura = $em->getRepository('App\Entity\Facturacion')->find($id);
        $formaPago = $factura->getFormaPago();

        $importe = 0;
        $facturacionLineas = $em->getRepository('App\Entity\FacturacionLineasConceptos')->findBy(array('facturacion' => $factura, 'anulado' => false));
        foreach ($facturacionLineas as $fl) {
            $importe += $fl->getImporte();
        }

        if (count($facturacionLineas) == 0) {
            $traduccion = $translator->trans('TRANS_FACTURA_NO_SERVICIOS', array(), 'facturacion');
            $this->addFlash('danger', $traduccion);

            return $this->redirectToRoute('factura_update', array('id' => $id));
        }

        $em->beginTransaction();

        try {
            $this->generarVencimientoFormaPago($em, $factura, $formaPago, $importe);
        } catch (\Exception $e) {
            $em->rollBack();
            $traduccion = $translator->trans('TRANS_AVISO_ERROR');
            $this->addFlash('danger', $traduccion);

            return $this->redirectToRoute('factura_update', array('id' => $id));
        }

        $em->commit();

        $traduccion = $translator->trans('TRANS_VENCIMIENTO_GENERADO');
        $this->addFlash('success', $traduccion);

        return $this->redirectToRoute('factura_update', array('id' => $id));
    }

    public function deleteVencimiento(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $vencimientoId = $_REQUEST['vencimientoId'];

        $vencimiento = $em->getRepository('App\Entity\FacturacionVencimiento')->find($vencimientoId);
        $vencimiento->setAnulado(true);
        $em->persist($vencimiento);
        $em->flush();

        $traduccion = $translator->trans('TRANS_VENCIMIENTO_ELIMINADO');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function confirmarCobroVencimiento(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $vencimiento = $em->getRepository('App\Entity\FacturacionVencimiento')->find($id);
        $vencimiento->setConfirmado(true);
        $em->persist($vencimiento);
        $em->flush();

        $traduccion = $translator->trans('TRANS_VENCIMIENTO_COBRO_CONFIRMADO');
        $this->addFlash('success', $traduccion);

        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();

        return $this->redirectToRoute('empresa_update', array('id' => $empresaId));
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

    public function renovarFacturaMultiple(Request $request, TranslatorInterface $translator)
    {

        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getFacturarContratoMultipleSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $hoy = new \DateTime();
        $year = $hoy->format('Y');
        $yearString = substr($year, 2, 4);

        $contratosSelect = $_REQUEST['contratos'];
        $contratosSelectArray = explode(",", $contratosSelect);

        $object = array("json" => $username, "entidad" => "Pulsa botón Facturar contratos", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $object, $usuario, TRUE);
        $em->flush();

        $session->set('contratosFacturados', array());

        $em->beginTransaction();
        foreach ($contratosSelectArray as $csa) {
            $contrato = $em->getRepository('App\Entity\Contrato')->find($csa);

            //Buscamos el numero de factura
            $newNumeroFacturacion = $this->calcularNumeroFactura($year, 7);

            //Buscamos la modalidad del contrato
            $modalidadContratoId = $contrato->getContratoModalidad()->getId();
            switch ($modalidadContratoId) {
                case 1:
                    $conceptoFacturacionPago = $em->getRepository('App\Entity\Concepto')->findOneBy(array('descripcion' => 'PRL SIN SALUD', 'anulado' => false));
                    break;
                case 2:
                    $conceptoFacturacionPago = $em->getRepository('App\Entity\Concepto')->findOneBy(array('descripcion' => 'SALUD', 'anulado' => false));
                    break;
                case 3:
                    $conceptoFacturacionPago = $em->getRepository('App\Entity\Concepto')->findOneBy(array('descripcion' => 'PRL+SALUD', 'anulado' => false));
                    break;
                default:
                    $conceptoFacturacionPago = null;
            }

            try {
                $return = $this->facturarContratoMultiple($em, $session, $contrato, $newNumeroFacturacion, $year, $translator, $conceptoFacturacionPago);
                if (!$return) {
                    return $this->redirectToRoute('contrato_show_renovados_multiple');
                }

                $contrato->setFacturado(true);
                $em->persist($contrato);
            } catch (\Exception $e) {
                $em->rollBack();
                $traduccion = $translator->trans('TRANS_AVISO_ERROR');
                $this->addFlash('danger', $traduccion);
                return $this->redirectToRoute('contrato_show_renovados_multiple');
            }
        }

        $em->commit();

        $traduccion = $translator->trans('TRANS_FACTURAR_OK');
        $this->addFlash('success', $traduccion);

        return $this->redirectToRoute('factura_show_contratos_facturados_multiple');
    }

    public function facturarPagosMultiple(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getFacturarPagoPendienteSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $hoy = new \DateTime();
        $year = $hoy->format('Y');
        $yearString = substr($year, 2, 4);

        $pagosSelect = $_REQUEST['pagos'];
        $pagosSelectArray = explode(",", $pagosSelect);

        $session->set('pagosFacturados', array());

        $arrayPagosFacturados = array();

        $em->beginTransaction();
        foreach ($pagosSelectArray as $psa) {
            $contratoPago = $em->getRepository('App\Entity\ContratoPago')->find($psa);
            $contrato = $contratoPago->getContrato();

            $empresa = $contrato->getEmpresa();
            $nombreEmpresa = $empresa->getEmpresa();

            $formaPago = null;
            $datosBancarios = $em->getRepository('App\Entity\DatosBancarios')->findOneBy(array('principal' => true, 'empresa' => $contrato->getEmpresa(), 'anulado' => false));
            if (!is_null($datosBancarios)) {
                $formaPago = $datosBancarios->getFormaPago();
            } else {
                $formaPago = $empresa->getFormaPago();
            }

            if (is_null($formaPago)) {
                $em->rollBack();
                $traduccion1 = $translator->trans('TRANS_AVISO_NO_FORMA_PAGO', array(), 'pagopendiente');
                $traduccion2 = $translator->trans('TRANS_AVISO_NO_FORMA_PAGO_2', array(), 'pagopendiente');
                $this->addFlash('danger', $traduccion1 . ' ' . $nombreEmpresa . ' ' . $traduccion2);
                return $this->redirectToRoute('pago_por_facturar_show');
            }

            //Buscamos el numero de factura
            $newNumeroFacturacion = $this->calcularNumeroFactura($year, 7);
            $serieFactura = $em->getRepository('App\Entity\SerieFactura')->find(7);

            try {
                $facturaId = $this->facturarPagoMultiple($em, $contrato, $contratoPago, $newNumeroFacturacion, $year, $serieFactura, $empresa, $formaPago);
                array_push($arrayPagosFacturados, $facturaId);
            } catch (\Exception $e) {
                $em->rollBack();

                $traduccion = $translator->trans('TRANS_AVISO_ERROR');
                $this->addFlash('danger', $traduccion);
                return $this->redirectToRoute('pago_por_facturar_show');
            }
        }

        $session->set('pagosFacturados', $arrayPagosFacturados);

        $em->commit();

        $traduccion = $translator->trans('TRANS_PAGOS_FACTURADOS_OK', array(), 'pagopendiente');
        $this->addFlash('success', $traduccion);

        return $this->redirectToRoute('pago_pendiente_show_facturados');
    }

    public function revisionFacturar(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getFacturarRevisionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $empresa = null;
        $revisionesFacturarId = array();

        if (isset($_REQUEST['revisiones'])) {
            $revisionesFacturarId = explode(",", $_REQUEST['revisiones']);
        }

        if (isset($_REQUEST['id'])) {
            array_push($revisionesFacturarId, $_REQUEST['id']);
        }

        $revisionesFacturarId = array_unique($revisionesFacturarId);

        //Revisamos las revisiones que no se han facturado aun
        $arrayRevisionesFacturar = array();
        foreach ($revisionesFacturarId as $revId) {
            $revision = $this->getDoctrine()->getRepository('App\Entity\Revision')->find($revId);
            if (is_null($revision->getFactura())) {
                array_push($arrayRevisionesFacturar, $revId);
            }
        }

        $em->beginTransaction();

        try {
            $factura = new Facturacion();

            $hoy = new \DateTime();
            $year = $hoy->format('Y');
            $yearString = substr($year, 2, 4);

            //Calculamos el numero de factura
            $numeroFactura = $this->calcularNumeroFactura($year, 7);

            $factura->setFecha(new \DateTime());
            $factura->setAnyo($year);
            $factura->setNumero($numeroFactura);
            $factura->setNumFac($numeroFactura . '/' . $yearString);

            //Asignamos por defecto que la serie sea A - Factura
            $serie = $this->getDoctrine()->getRepository('App\Entity\SerieFactura')->find(7);
            $factura->setSerie($serie);
            $em->persist($factura);
            $em->flush();

            foreach ($arrayRevisionesFacturar as $r) {
                $estadoFacturada = $this->getDoctrine()->getRepository('App\Entity\EstadoRevision')->find(4);

                $revision = $this->getDoctrine()->getRepository('App\Entity\Revision')->find($r);
                $revision->setEstado($estadoFacturada);
                $revision->setFactura($factura);
                $em->persist($revision);
                $em->flush();

                $nombreTrabajador = $revision->getTrabajador()->getNombre();
                $fechaRevision = $revision->getFecha()->format('d/m/Y');

                $empresaId = $revision->getEmpresa()->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

                $citacion = $revision->getCitacion();
                if (!is_null($citacion)) {
                    $estadoFinalizado = $this->getDoctrine()->getRepository('App\Entity\EstadoCitacion')->find(3);
                    $citacion->setEstado($estadoFinalizado);
                    $em->persist($citacion);
                    $em->flush();

                    $codigoAgenda = $citacion->getAgenda()->getCodigo();
                    $conceptoFactura = 'RM ' . $codigoAgenda;

                    $query = "select a.importe, b.id from tarifa_revision_medica a 
                        inner join concepto b on a.concepto_id = b.id 
                        where a.empresa_id = $empresaId 
                        and a.anulado = false
                        and b.descripcion like '%$conceptoFactura%'";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $tarifaRevisiones = $stmt->fetchAll();

                    $importe = 0;
                    $conceptoFacturacion = $this->getDoctrine()->getRepository('App\Entity\Concepto')->findOneBy(array('descripcion' => $conceptoFactura, 'anulado' => false));
                    if (count($tarifaRevisiones) > 0) {
                        $importe = $tarifaRevisiones[0]['importe'];
                    }

                    $facturaConcepto = new FacturacionLineasConceptos();
                    $facturaConcepto->setFacturacion($factura);
                    $facturaConcepto->setUnidades(1);
                    $facturaConcepto->setImporte($importe);
                    $facturaConcepto->setImporteUnidad($importe);
                    $facturaConcepto->setIva(0);
                    $facturaConcepto->setConcepto($conceptoFactura . ' ' . $nombreTrabajador . ' ' . $fechaRevision);
                    $facturaConcepto->setConceptoFacturacion($conceptoFacturacion);
                    $facturaConcepto->setAnulado(false);
                    $em->persist($facturaConcepto);
                    $em->flush();
                }
            }

            $codigoFactura = $this->calcularCodigoFactura($empresaId);
            $factura->setCodigo($codigoFactura);

            $formaPago = null;
            $formaPago = $empresa->getFormaPagoRml();

            if (is_null($formaPago)) {
                $formaPago = $empresa->getFormaPago();
                if (is_null($formaPago)) {
                    $datosBancarios =  $this->getDoctrine()->getRepository('App\Entity\DatosBancarios')->findOneBy(array('principal' => true, 'anulado' => false, 'empresa' => $empresa));
                    if (!is_null($datosBancarios)) {
                        $formaPago = $datosBancarios->getFormaPago();
                    }
                }
            }

            $factura->setFormaPago($formaPago);
            $factura->setEmpresa($empresa);
            $em->persist($factura);
            $em->flush();

            //Generamos el recibo
            $importe = 0;
            $facturacionLineas = $em->getRepository('App\Entity\FacturacionLineasConceptos')->findBy(array('facturacion' => $factura, 'anulado' => false));
            foreach ($facturacionLineas as $fl) {
                $importe += $fl->getImporte();
            }

            if ($importe > 0) {
                $this->generarVencimientoFormaPago($em, $factura, $formaPago, $importe);
            }

            //$session->set('empresa', $empresa);

        } catch (\Exception $e) {
            $em->rollBack();
            throw $e;
        }

        $em->commit();

        $traduccion = $translator->trans('TRANS_REVISIONES_FACTURADAS');
        $this->addFlash('success', $traduccion);

        return $this->redirectToRoute('dashboard_admin');
    }

    public function revisionIncluir(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getFacturarRevisionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $empresa = null;
        $revisionesFacturarId = array();

        if (isset($_REQUEST['revisiones'])) {
            $revisionesFacturarId = explode(",", $_REQUEST['revisiones']);
        }

        if (isset($_REQUEST['id'])) {
            array_push($revisionesFacturarId, $_REQUEST['id']);
        }

        $em->beginTransaction();

        try {

            foreach ($revisionesFacturarId as $r) {
                $estadoFacturada = $this->getDoctrine()->getRepository('App\Entity\EstadoRevision')->find(4);

                $revision = $this->getDoctrine()->getRepository('App\Entity\Revision')->find($r);
                $revision->setEstado($estadoFacturada);
                $em->persist($revision);
                $em->flush();
            }

            $session->set('empresa', $empresa);
        } catch (\Exception $e) {
            $em->rollBack();
            throw $e;
        }

        $em->commit();

        $traduccion = $translator->trans('TRANS_REVISIONES_INCLUIDAS');
        $this->addFlash('success', $traduccion);

        return $this->redirectToRoute('dashboard_admin');
    }

    function calcularNumeroFactura($year, $serieId)
    {
        $numeroFacturacion = '00001';

        //Buscamos si hay algun hueco de factura libre
        $query = "select MIN(CAST(substring(num_fac, 0, 6)  AS INTEGER)) as facturacion from facturacion where fecha >= '$year-01-01' and fecha <= '$year-12-31' and anyo = '$year' and anulado = true and serie_id = $serieId and num_fac not in (select num_fac from facturacion where anulado = false and fecha >= '$year-01-01' and fecha <= '$year-12-31' and anyo = '$year' and serie_id = $serieId)";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $resultFacturacionAnulada = $stmt->fetchAll();
        if (count($resultFacturacionAnulada) > 0) {
            if (!is_null($resultFacturacionAnulada[0]['facturacion'])) {
                $numeroFacturacion = str_pad($resultFacturacionAnulada[0]['facturacion'], 5, '0', STR_PAD_LEFT);
            } else {
                $query = "select MAX(CAST(substring(num_fac, 0, 6)  AS INTEGER)) as facturacion from facturacion where fecha >= '$year-01-01' and fecha <= '$year-12-31' and anyo = '$year' and anulado = false and serie_id = $serieId";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultFacturacion = $stmt->fetchAll();

                if (count($resultFacturacion) > 0) {
                    $numeroFacturacion = str_pad($resultFacturacion[0]['facturacion'] + 1, 5, '0', STR_PAD_LEFT);
                }
            }
        } else {
            $query = "select MAX(CAST(substring(num_fac, 0, 6)  AS INTEGER)) as facturacion from facturacion where fecha >= '$year-01-01' and fecha <= '$year-12-31' and anyo = '$year' and anulado = false and serie_id = $serieId";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultFacturacion = $stmt->fetchAll();

            if (count($resultFacturacion) > 0) {
                $numeroFacturacion = str_pad($resultFacturacion[0]['facturacion'] + 1, 5, '0', STR_PAD_LEFT);
            }
        }

        return $numeroFacturacion;
    }

    function facturarContratoMultiple($em, $session, $contrato, $newNumeroFacturacion, $year, $translator, $conceptoFacturacionPago)
    {
        $yearString = substr($year, 2, 4);

        $arrayContratosFacturados = $session->get('contratosFacturados');

        $empresa = $contrato->getEmpresa();
        $empresaId = $contrato->getEmpresa()->getId();
        $nombreEmpresa = $empresa->getEmpresa();

        $serieFactura = $em->getRepository('App\Entity\SerieFactura')->find(7);

        $importeContrato = 0;
        $serviciosContrato = $em->getRepository('App\Entity\ServicioContratado')->findBy(array('contrato' => $contrato, 'anulado' => false));
        foreach ($serviciosContrato as $sc) {
            $importeContrato += $sc->getPrecio();
        }

        if ($importeContrato > 0) {

            //Buscamos la factura del anterior contrato
            $oldFacturaContrato = $em->getRepository('App\Entity\Facturacion')->findOneBy(array('serie' => $serieFactura, 'anulado' => false, 'empresa' => $empresa), array('fecha' => 'DESC'));

            $datosBancarios = $em->getRepository('App\Entity\DatosBancarios')->findOneBy(array('empresa' => $empresa, 'principal' => true, 'anulado' => false));

            if (is_null($datosBancarios)) {
                $formaPago = $empresa->getFormaPago();
            } else {
                $formaPago = $datosBancarios->getFormaPago();
            }

            if (is_null($formaPago)) {
                if (!is_null($oldFacturaContrato)) {
                    $formaPago = $oldFacturaContrato->getFormaPago();
                }
            }

            if (is_null($formaPago)) {
                $em->rollBack();
                $traduccion1 = $translator->trans('TRANS_AVISO_NO_FORMA_PAGO', array(), 'pagopendiente');
                $traduccion2 = $translator->trans('TRANS_AVISO_NO_FORMA_PAGO_2', array(), 'pagopendiente');
                $this->addFlash('danger', $traduccion1 . ' ' . $nombreEmpresa . ' ' . $traduccion2);
                return false;
            }

            $newFacturacionContrato = new Facturacion();
            $newFacturacionContrato->setEmpresa($empresa);
            $newFacturacionContrato->setFecha(new \DateTime());
            $newFacturacionContrato->setCodigo($this->calcularCodigoFactura($empresaId));
            $newFacturacionContrato->setAnyo($year);
            $newFacturacionContrato->setNumero(intval($newNumeroFacturacion));
            $newFacturacionContrato->setOldFactura($oldFacturaContrato);
            $newFacturacionContrato->setContrato($contrato);
            $newFacturacionContrato->setNumFac($newNumeroFacturacion . '/' . $yearString);
            $newFacturacionContrato->setRenovacion(false);
            $newFacturacionContrato->setPagada(false);
            $newFacturacionContrato->setCancelada(false);
            $newFacturacionContrato->setEnviada(false);
            $newFacturacionContrato->setFacturaAsociada(null);
            $newFacturacionContrato->setFichero(null);
            $newFacturacionContrato->setPasswordPdf(null);
            $newFacturacionContrato->setSerie($serieFactura);
            $newFacturacionContrato->setFormaPago($formaPago);
            $em->persist($newFacturacionContrato);
            $em->flush();

            $importeContrato = 0;
            $serviciosContrato = $em->getRepository('App\Entity\ServicioContratado')->findBy(array('contrato' => $contrato, 'anulado' => false));
            foreach ($serviciosContrato as $sc) {
                $importeContrato = $sc->getPrecio();
                $concepto = $sc->getServicio();
                $conceptoDesc = null;
                $conceptoFacturacion = null;
                if (!is_null($concepto)) {
                    $conceptoDesc = $concepto->getDescripcion();

                    $query = "select id from concepto where descripcion = '$conceptoDesc' and anulado = false";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $resultConceptoFacturacion = $stmt->fetchAll();

                    if (count($resultConceptoFacturacion) > 0) {
                        $conceptoFacturacionId = $resultConceptoFacturacion[0]['id'];
                        $conceptoFacturacion = $em->getRepository('App\Entity\Concepto')->find($conceptoFacturacionId);
                    }
                }

                $newFacturacionLineas = new FacturacionLineasConceptos();
                $newFacturacionLineas->setAnulado(false);
                $newFacturacionLineas->setFacturacion($newFacturacionContrato);
                $newFacturacionLineas->setImporteUnidad($importeContrato);
                $newFacturacionLineas->setImporte($importeContrato * 1.21);
                $newFacturacionLineas->setIva($importeContrato * 0.21);
                $newFacturacionLineas->setUnidades(1);
                $newFacturacionLineas->setConcepto($conceptoDesc);
                $newFacturacionLineas->setConceptoFacturacion($conceptoFacturacion);
                $em->persist($newFacturacionLineas);
                $em->flush();
            }

            $contratoPago = $em->getRepository('App\Entity\ContratoPago')->findBy(array('contrato' => $contrato, 'anulado' => false));

            foreach ($contratoPago as $cp) {
                $newFacturacionPagos = new FacturacionLineasPagos();
                $newFacturacionPagos->setAnulado(false);
                $newFacturacionPagos->setFacturacion($newFacturacionContrato);
                $newFacturacionPagos->setUnidades(1);
                $newFacturacionPagos->setVencimiento(new \DateTime());
                $newFacturacionPagos->setImporteIva($cp->getImporteIva());
                $newFacturacionPagos->setImporteExentoIva($cp->getImporteExentoIva());
                $newFacturacionPagos->setImporteTotal($cp->getImporteTotal());
                $newFacturacionPagos->setImporteSujetoIva($cp->getImporteSujetoIva());
                $newFacturacionPagos->setImporteSinIva($cp->getImporteSinIva());
                $newFacturacionPagos->setConcepto($cp->getTextoPago());
                $newFacturacionPagos->setVencimiento($cp->getVencimiento());
                $newFacturacionPagos->setPorcentaje($cp->getPorcentaje());
                $newFacturacionPagos->setConceptoFacturacion($conceptoFacturacionPago);
                $em->persist($newFacturacionPagos);
                $em->flush();

                $cp->setFacturado(true);
                $em->persist($cp);
                $em->flush();
            }

            array_push($arrayContratosFacturados, $newFacturacionContrato->getId());
            $session->set('contratosFacturados', $arrayContratosFacturados);
        }

        return true;
    }

    function facturarPagoMultiple($em, $contrato, $contratoPago, $newNumeroFacturacion, $year, $serieFactura, $empresa, $formaPago)
    {
        $yearString = substr($year, 2, 4);

        $newFactura = new Facturacion();
        $newFactura->setFecha(new \DateTime());
        $newFactura->setCodigo($this->calcularCodigoFactura($empresa->getId()));
        $newFactura->setAnyo($year);
        $newFactura->setNumero(intval($newNumeroFacturacion));
        $newFactura->setContrato($contrato);
        $newFactura->setNumFac($newNumeroFacturacion . '/' . $yearString);
        $newFactura->setRenovacion(false);
        $newFactura->setPagada(false);
        $newFactura->setCancelada(false);
        $newFactura->setEnviada(false);
        $newFactura->setFacturaAsociada(null);
        $newFactura->setAnulado(false);
        $newFactura->setEmpresa($contrato->getEmpresa());
        $newFactura->setFormaPago($formaPago);
        $newFactura->setSerie($serieFactura);
        $em->persist($newFactura);
        $em->flush();

        //Buscamos la modalidad del contrato
        $modalidadContratoId = $contrato->getContratoModalidad()->getId();
        switch ($modalidadContratoId) {
            case 1:
                $conceptoFacturacionPago = $em->getRepository('App\Entity\Concepto')->findOneBy(array('descripcion' => 'PRL SIN SALUD', 'anulado' => false));
                break;
            case 2:
                $conceptoFacturacionPago = $em->getRepository('App\Entity\Concepto')->findOneBy(array('descripcion' => 'SALUD', 'anulado' => false));
                break;
            case 3:
                $conceptoFacturacionPago = $em->getRepository('App\Entity\Concepto')->findOneBy(array('descripcion' => 'PRL+SALUD', 'anulado' => false));
                break;
            default:
                $conceptoFacturacionPago = null;
        }

        $newFacturaPago = new FacturacionLineasPagos();
        $newFacturaPago->setFacturacion($newFactura);
        $newFacturaPago->setAnulado(false);
        $newFacturaPago->setUnidades(1);
        $newFacturaPago->setImporteExentoIva($contratoPago->getImporteExentoIva());
        $newFacturaPago->setImporteIva($contratoPago->getImporteIva());
        $newFacturaPago->setImporteSinIva($contratoPago->getImporteSinIva());
        $newFacturaPago->setImporteSujetoIva($contratoPago->getImporteSujetoIva());
        $newFacturaPago->setImporteTotal($contratoPago->getImporteTotal());
        $newFacturaPago->setConcepto($contratoPago->getTextoPago());
        $newFacturaPago->setPorcentaje($contratoPago->getPorcentaje());
        $newFacturaPago->setPago($contratoPago);
        $newFacturaPago->setFacturado(true);
        $newFacturaPago->setConceptoFacturacion($conceptoFacturacionPago);
        $em->persist($newFacturaPago);
        $em->flush();

        $newFacturaLinea = new FacturacionLineasConceptos();
        $newFacturaLinea->setFacturacion($newFactura);
        $newFacturaLinea->setAnulado(false);
        $newFacturaLinea->setUnidades(1);
        $newFacturaLinea->setImporte($contratoPago->getImporteTotal());
        $newFacturaLinea->setImporteUnidad($contratoPago->getImporteSujetoIva());
        $newFacturaLinea->setIva($contratoPago->getImporteIva());
        $newFacturaLinea->setConcepto($contratoPago->getTextoPago());
        $em->persist($newFacturaLinea);
        $em->flush();

        $contratoPago->setFacturado(true);
        $em->persist($contratoPago);
        $em->flush();

        return $newFactura->getId();
    }

    function generarVencimientoFacturaManual($em, $vencimientoCobro, $ignorarDiaPago, $facturaAsociada, $dateVencimiento, $numeroFactura, $formaPagoDesc, $importe, $formaPagoObj, $confirmado, $fraccionamientoCobro, $numeroCobros, $frecuencia, $cadaCuando)
    {
        //Depende del modo de generacion hacemos un proceso u otro
        switch ($vencimientoCobro) {
            case 1:

                //Si el campo ignorar dia de pago de la empresa no está marcado buscamos la fecha mas proxima al realizar el cobro
                if ($ignorarDiaPago == 'false') {
                    $empresa = $facturaAsociada->getEmpresa();
                    $datosBancarios = $em->getRepository('App\Entity\DatosBancarios')->findOneBy(array('empresa' => $empresa));
                    if (!is_null($datosBancarios)) {
                        $dateVencimientoOld = $dateVencimiento;
                        $dateVencimiento = $this->calcularFechaVencimientoFactura($datosBancarios->getDiaPago(), $dateVencimientoOld);
                    }
                }

                $vencimiento = new FacturacionVencimiento();
                $vencimiento->setFacturaAsociada($facturaAsociada);
                $vencimiento->setConcepto('Cobro 1 de la factura ' . $numeroFactura . ' (' . $formaPagoDesc . ')');
                $vencimiento->setFecha($dateVencimiento);
                $vencimiento->setImporte($importe);
                $vencimiento->setFormaPago($formaPagoObj);
                $vencimiento->setConfirmado($confirmado);
                $em->persist($vencimiento);
                $em->flush();
                break;
            case 2:

                switch ($fraccionamientoCobro) {
                    case 1:

                        $importe = round($importe / $numeroCobros, 2);

                        $dateVencimientoString = $dateVencimiento->format('Y-m-d');
                        $numCobro = 1;
                        for ($i = 1; $i <= $numeroCobros; $i++) {
                            $vencimiento = new FacturacionVencimiento();
                            $vencimiento->setFacturaAsociada($facturaAsociada);
                            $vencimiento->setConcepto('Cobro ' . $numCobro . ' de la factura ' . $numeroFactura . ' (' . $formaPagoDesc . ')');
                            $vencimiento->setFecha($dateVencimiento);
                            $vencimiento->setImporte($importe);
                            $vencimiento->setFormaPago($formaPagoObj);
                            $vencimiento->setConfirmado($confirmado);
                            $em->persist($vencimiento);
                            $em->flush();

                            //1 == Dias
                            //2 == meses
                            //3 == años
                            if ($frecuencia == 1) {
                                $dateVencimiento = date("Y-m-d", strtotime($dateVencimientoString . "+ " . $cadaCuando . " day"));
                            } elseif ($frecuencia == 2) {
                                $dateVencimiento = date("Y-m-d", strtotime($dateVencimientoString . "+ " . $cadaCuando . " month"));
                            } elseif ($frecuencia == 3) {
                                $dateVencimiento = date("Y-m-d", strtotime($dateVencimientoString . "+ " . $cadaCuando . " year"));
                            }

                            $dateVencimiento = new \DateTime($dateVencimiento);

                            $dateVencimientoString = $dateVencimiento->format('Y-m-d');
                            $numCobro++;
                        }

                        break;
                    case 2:
                        $vencimiento = new FacturacionVencimiento();
                        $vencimiento->setFacturaAsociada($facturaAsociada);
                        $vencimiento->setConcepto('Cobro 1 de la factura ' . $numeroFactura . ' (' . $formaPagoDesc . ')');
                        $vencimiento->setFecha($dateVencimiento);
                        $vencimiento->setImporte($importe);
                        $vencimiento->setFormaPago($formaPagoObj);
                        $vencimiento->setConfirmado($confirmado);
                        $em->persist($vencimiento);
                        $em->flush();
                        break;
                }
                break;
        }
    }

    public function showContratosFacturadosMultiple(Request $request)
    {

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getFacturarContratoMultipleSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $arrayContratosFacturados = $session->get('contratosFacturados');
        $arrayContratosFacturadosString = "";
        foreach ($arrayContratosFacturados as $acf) {
            $arrayContratosFacturadosString .= $acf . ',';
        }
        $arrayContratosFacturadosString = rtrim($arrayContratosFacturadosString, ",");

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, concat(c.serie,a.num_fac) as num_fac, to_char(a.fecha, 'DD/MM/YYYY') as fecha, to_char(a.fecha, 'YYYYMMDDHHmm') as fechatimestamp, to_char(a.fecha, 'MM') as mes,
        anyo as any, a.codigo, (select sum(importe_unidad) from facturacion_lineas_conceptos where facturacion_id = a.id and anulado = false) as importe_total, b.empresa
        from facturacion a 
        inner join empresa b on a.empresa_id = b.id
        inner join serie_factura c on a.serie_id = c.id
        where a.anulado = false ";

        if ($arrayContratosFacturadosString != "") {
            $query .= "and a.id in ($arrayContratosFacturadosString) ";
        }

        $query .= "group by a.id, a.num_fac, a.fecha, b.empresa, c.serie order by fecha desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $facturas = $stmt->fetchAll();

        $object = array("json" => $username, "entidad" => "contratos facturados", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('facturacion/show_facturadas.html.twig', array('facturas' => $facturas));
    }

    public function generarVencimientoFacturaMultiple(Request $request, TranslatorInterface $translator)
    {

        $em = $this->getDoctrine()->getManager();
        $hoy = new \DateTime();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getAddVencimientoFacturacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $tipo = $_REQUEST['tipo'];
        $facturasSelect = $_REQUEST['facturas'];

        $object = array("json" => $username, "entidad" => "Pulsa botón Generar vencimiento facturas", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $object, $usuario, TRUE);
        $em->flush();

        $facturasSelectArray = explode(",", $facturasSelect);

        $em->beginTransaction();

        $arrayVencimientosGenerados = array();

        foreach ($facturasSelectArray as $fs) {

            try {
                //Buscamos la factura a generar el vencimiento
                $factura = $em->getRepository('App\Entity\Facturacion')->find($fs);

                $formaPago = $factura->getFormaPago();

                $importe = 0;
                $facturacionLineas = $em->getRepository('App\Entity\FacturacionLineasConceptos')->findBy(array('facturacion' => $factura, 'anulado' => false));
                foreach ($facturacionLineas as $fl) {
                    $importe += $fl->getImporte();
                }
                if (count($facturacionLineas) == 0) {
                    $em->rollBack();

                    $numeroFactura = $factura->getNumFac();
                    $serieFactura = $factura->getSerie()->getSerie();

                    $numFac = $serieFactura . $numeroFactura;
                    $traduccion = $translator->trans('TRANS_FACTURA_NO_SERVICIOS_1', array(), 'facturacion');
                    $traduccion2 = $translator->trans('TRANS_FACTURA_NO_SERVICIOS_2', array(), 'facturacion');
                    $this->addFlash('danger', $traduccion . ' ' . $numFac . ' ' . $traduccion2);
                    switch ($tipo) {
                        case 1:
                            return $this->redirectToRoute('factura_show_contratos_facturados_multiple');
                            break;
                        case 2:
                            return $this->redirectToRoute('pago_pendiente_show_vencimientos');
                            break;
                    }
                }
                $this->generarVencimientoFormaPago($em, $factura, $formaPago, $importe);
                array_push($arrayVencimientosGenerados, $factura->getId());
            } catch (\Exception $e) {
                $em->rollBack();
                $traduccion = $translator->trans('TRANS_AVISO_ERROR');
                $this->addFlash('danger', $traduccion);
                switch ($tipo) {
                    case 1:
                        return $this->redirectToRoute('factura_show_contratos_facturados_multiple');
                        break;
                    case 2:
                        return $this->redirectToRoute('pago_pendiente_show_vencimientos');
                        break;
                }
            }
        }
        $em->commit();

        $session->set('vencimientosGenerados', $arrayVencimientosGenerados);

        $traduccion = $translator->trans('TRANS_VENCIMIENTO_GENERADO');
        $this->addFlash('success', $traduccion);

        switch ($tipo) {
            case 1:
                return $this->redirectToRoute('factura_show_vencimientos');
                break;
            case 2:
                return $this->redirectToRoute('pago_pendiente_show_vencimientos');
                break;
        }
    }

    public function facturarEmpresaContratoAutomatico(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $em = $this->getDoctrine()->getManager();
        $hoy = new \DateTime();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getAddFacturacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $contrato = $em->getRepository('App\Entity\Contrato')->find($id);

        $em->beginTransaction();

        try {
            $session->set('contratosFacturados', array());

            $empresa = $contrato->getEmpresa();
            $nombreEmpresa = $empresa->getEmpresa();

            $formaPago = null;
            $datosBancarios = $em->getRepository('App\Entity\DatosBancarios')->findOneBy(array('principal' => true, 'empresa' => $contrato->getEmpresa(), 'anulado' => false));
            if (!is_null($datosBancarios)) {
                $formaPago = $datosBancarios->getFormaPago();
            } else {
                $formaPago = $empresa->getFormaPago();
            }
            if (is_null($formaPago)) {
                $em->rollBack();
                $traduccion1 = $translator->trans('TRANS_AVISO_NO_FORMA_PAGO', array(), 'pagopendiente');
                $traduccion2 = $translator->trans('TRANS_AVISO_NO_FORMA_PAGO_2', array(), 'pagopendiente');
                $this->addFlash('danger', $traduccion1 . ' ' . $nombreEmpresa . ' ' . $traduccion2);
                return $this->redirectToRoute('empresa_update', array('id' => $contrato->getEmpresa()->getId()));
            }
            $hoy = new \DateTime();
            $year = $hoy->format('Y');

            //Buscamos el numero de factura
            $newNumeroFacturacion = $this->calcularNumeroFactura($year, 7);

            //Buscamos la modalidad del contrato
            $modalidadContratoId = $contrato->getContratoModalidad()->getId();
            switch ($modalidadContratoId) {
                case 1:
                    $conceptoFacturacionPago = $em->getRepository('App\Entity\Concepto')->findOneBy(array('descripcion' => 'PRL SIN SALUD', 'anulado' => false));
                    break;
                case 2:
                    $conceptoFacturacionPago = $em->getRepository('App\Entity\Concepto')->findOneBy(array('descripcion' => 'SALUD', 'anulado' => false));
                    break;
                case 3:
                    $conceptoFacturacionPago = $em->getRepository('App\Entity\Concepto')->findOneBy(array('descripcion' => 'PRL+SALUD', 'anulado' => false));
                    break;
                default:
                    $conceptoFacturacionPago = null;
            }

            $return = $this->facturarContratoMultiple($em, $session, $contrato, $newNumeroFacturacion, $year, $translator, $conceptoFacturacionPago);
            if (!$return) {
                return $this->redirectToRoute('empresa_update', array('id' => $contrato->getEmpresa()->getId()));
            }

            $contrato->setFacturado(true);
            $em->persist($contrato);

            $factura = $em->getRepository('App\Entity\Facturacion')->findOneBy(array('contrato' => $contrato, 'anulado' => false));
            if (!is_null($factura)) {

                $importe = 0;
                $facturacionLineas = $em->getRepository('App\Entity\FacturacionLineasConceptos')->findBy(array('facturacion' => $factura, 'anulado' => false));
                foreach ($facturacionLineas as $fl) {
                    $importe += $fl->getImporte();
                }
                if (count($facturacionLineas) == 0) {
                    $em->rollBack();

                    $numeroFactura = $factura->getNumFac();
                    $serieFactura = $factura->getSerie()->getSerie();

                    $numFac = $serieFactura . $numeroFactura;
                    $traduccion = $translator->trans('TRANS_FACTURA_NO_SERVICIOS_1', array(), 'facturacion');
                    $traduccion2 = $translator->trans('TRANS_FACTURA_NO_SERVICIOS_2', array(), 'facturacion');
                    $this->addFlash('danger', $traduccion . ' ' . $numFac . ' ' . $traduccion2);
                    return $this->redirectToRoute('empresa_update', array('id' => $contrato->getEmpresa()->getId()));
                }
                $this->generarVencimientoFormaPago($em, $factura, $formaPago, $importe);
            }
        } catch (\Exception $e) {
            $em->rollBack();
            $traduccion = $translator->trans('TRANS_AVISO_ERROR');
            $this->addFlash('danger', $traduccion);
            return $this->redirectToRoute('empresa_update', array('id' => $contrato->getEmpresa()->getId()));
        }
        $em->commit();

        $session->set('contratosFacturados', null);
        $session->set('contratosFacturados', null);

        $traduccion = $translator->trans('TRANS_CONTRATO_FACTURADO_OK');
        $this->addFlash('success', $traduccion);

        return $this->redirectToRoute('empresa_update', array('id' => $contrato->getEmpresa()->getId()));
    }

    function generarVencimientoFormaPago($em, $factura, $formaPago, $importe)
    {
        $empresa = $factura->getEmpresa();
        $datosBancarios = $em->getRepository('App\Entity\DatosBancarios')->findOneBy(array('empresa' => $empresa, 'principal' => true, 'anulado' => false));
        $dateFactura = $factura->getFecha();
        
        if (!is_null($datosBancarios)) {
            $dateVencimiento = $this->calcularFechaVencimientoFactura($datosBancarios->getDiaPago(), $dateFactura);
        } else if (!is_null($empresa->getFormaPago())) {
            $diaPago = $empresa->getDiaPago();
            if (is_null($diaPago)) {
                $diaPago = 1;
            }
            $dateVencimiento = $this->calcularFechaVencimientoFactura($diaPago, $dateFactura);
        } else {
            $dateVencimiento = $dateFactura;
        }
        $numeroFactura = $factura->getNumFac();
        $serieFactura = $factura->getSerie()->getSerie();
        $formaPagoDesc = $formaPago->getDescripcion();

        $formaPagoCadencia = $em->getRepository('App\Entity\FormaPagoCadencia')->findOneBy(array('formaPago' => $formaPago));

        if (is_null($formaPagoCadencia)) {

            if ($formaPago->getFormaPagoContable() == 8) {
                $newGiroBancario = new GiroBancario();
                $newGiroBancario->setFecha($factura->getFecha());
                $newGiroBancario->setVencimiento($dateVencimiento);
                $newGiroBancario->setFacturacion($factura);
                $newGiroBancario->setGirado(false);
                $newGiroBancario->setManual(false);
                $newGiroBancario->setDevolucion(false);
                $newGiroBancario->setComision(false);
                $newGiroBancario->setEsFactura(false);
                $newGiroBancario->setPagoConfirmado(false);
                $newGiroBancario->setImporte($importe);
                $newGiroBancario->setCuenta($datosBancarios);
                $newGiroBancario->setConcepto('Recibo 1 de la factura ' . $serieFactura . $numeroFactura);
                $em->persist($newGiroBancario);
                $em->flush();
            } else {
                $vencimiento = new FacturacionVencimiento();
                $vencimiento->setFacturaAsociada($factura);
                $vencimiento->setConcepto('Cobro 1 de la factura A' . $numeroFactura . ' (' . $formaPagoDesc . ')');
                $vencimiento->setFecha($dateVencimiento);
                $vencimiento->setImporte($importe);
                $vencimiento->setFormaPago($formaPago);
                $vencimiento->setConfirmado(false);
                $em->persist($vencimiento);
                $em->flush();
            }
        } else {
            $formaPagoCadencia = $em->getRepository('App\Entity\FormaPagoCadencia')->findBy(array('formaPago' => $formaPago), array('pagos' => 'ASC'));

            $countPagos = count($formaPagoCadencia);
            $importeVencimiento = round($importe / $countPagos, 2);

            foreach ($formaPagoCadencia as $fpc) {
                $dias = $fpc->getCadencia();
                $pagos = $fpc->getPagos();

                $dateVencimientoString = $dateVencimiento->format('Y-m-d');

                switch ($fpc->getTipo()) {
                        //dias
                    case 1:
                        $dateVencimientoGiroBancario = date("Y-m-d", strtotime($dateVencimientoString . "+ " . $dias . " day"));
                        break;
                        //meses
                    case 2:
                        $dateVencimientoGiroBancario = date("Y-m-d", strtotime($dateVencimientoString . "+ " . $dias . " month"));
                        break;
                }
                $dateVencimientoGiroBancario = new \DateTime($dateVencimientoGiroBancario);

                if ($formaPago->getFormaPagoContable() == 8) {
                    $newGiroBancario = new GiroBancario();
                    $newGiroBancario->setFecha($factura->getFecha());
                    $newGiroBancario->setVencimiento($dateVencimientoGiroBancario);
                    $newGiroBancario->setFacturacion($factura);
                    $newGiroBancario->setGirado(false);
                    $newGiroBancario->setManual(false);
                    $newGiroBancario->setDevolucion(false);
                    $newGiroBancario->setComision(false);
                    $newGiroBancario->setEsFactura(false);
                    $newGiroBancario->setPagoConfirmado(false);
                    $newGiroBancario->setImporte($importeVencimiento);
                    $newGiroBancario->setCuenta($datosBancarios);
                    $newGiroBancario->setConcepto('Recibo ' . $pagos . ' de la factura ' . $serieFactura . $numeroFactura);
                    $em->persist($newGiroBancario);
                    $em->flush();
                } else {
                    $vencimiento = new FacturacionVencimiento();
                    $vencimiento->setFacturaAsociada($factura);
                    $vencimiento->setConcepto('Cobro ' . $pagos . ' de la factura A' . $numeroFactura . ' (' . $formaPagoDesc . ')');
                    $vencimiento->setFecha($dateVencimientoGiroBancario);
                    $vencimiento->setImporte($importeVencimiento);
                    $vencimiento->setFormaPago($formaPago);
                    $vencimiento->setConfirmado(false);
                    $em->persist($vencimiento);
                    $em->flush();
                }
            }
        }
    }

    function calcularFechaVencimientoFactura($diaPago, $dateFactura)
    {
        $fechaVencimiento = $dateFactura;
        if (!is_null($diaPago)) {
            $fechaProvisionalString = $dateFactura->format('Y-m');
            $newFechaProvisionalString = $fechaProvisionalString . '-' . $diaPago;
            $fechaProvisional = new \DateTime($newFechaProvisionalString);

            $dateFacturaString = $dateFactura->format('Y-m-d');
            $fechaProvisionalString = $fechaProvisional->format('Y-m-d');

            $date1 = strtotime($dateFacturaString);
            $date2 = strtotime($fechaProvisionalString);

            if ($date1 <= $date2) {
                $fechaVencimiento = $fechaProvisional;
            }
        }
        return $fechaVencimiento;
    }

    function calcularCodigoFactura($empresaId)
    {
        $query = "select max(codigo::integer) as codigo from facturacion where empresa_id = $empresaId and anulado = false";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $resultCodigo = $stmt->fetchAll();

        if (count($resultCodigo) > 0) {
            $codigoFactura = $resultCodigo[0]['codigo'] + 1;
        } else {
            $codigoFactura = '1';
        }
        return $codigoFactura;
    }

    function calcularImporteFactura($facturaId)
    {
        $query = "select sum(importe) as importe from facturacion_lineas_conceptos where facturacion_id = $facturaId and anulado = false";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $resultImporteFactura = $stmt->fetchAll();
        $importe = $resultImporteFactura[0]['importe'];

        return $importe;
    }

    public function showVencimientosGeneradosMultiple(Request $request)
    {
        $session = $request->getSession();

        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getFacturarContratoMultipleSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $arrayVencimientosGenerados = $session->get('vencimientosGenerados');
        $arrayVencimientosGeneradosString = "";
        foreach ($arrayVencimientosGenerados as $avg) {
            $arrayVencimientosGeneradosString .= $avg . ',';
        }
        $arrayVencimientosGeneradosString = rtrim($arrayVencimientosGeneradosString, ",");

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, concat(c.serie,a.num_fac) as num_fac, to_char(a.fecha, 'DD/MM/YYYY') as fecha, to_char(a.fecha, 'YYYYMMDDHHmm') as fechatimestamp, to_char(a.fecha, 'MM') as mes,
        anyo as any, a.codigo, (select sum(importe_unidad) from facturacion_lineas_conceptos where facturacion_id = a.id and anulado = false) as importe_total, b.empresa
        from facturacion a 
        inner join empresa b on a.empresa_id = b.id
        inner join serie_factura c on a.serie_id = c.id
        where a.anulado = false ";

        if ($arrayVencimientosGeneradosString != "") {
            $query .= " and a.id in ($arrayVencimientosGeneradosString) ";
        } else {
            $query .= " and a.id is null ";
        }
        $query .= " group by a.id, a.num_fac, a.fecha, b.empresa, c.serie order by fecha desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $facturas = $stmt->fetchAll();

        $object = array("json" => $username, "entidad" => "vencimientos generados", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('facturacion/show_vencimientos_generados.html.twig', array('facturas' => $facturas));
    }

    function encriptarPdf($filePdfEncriptado, $filePdf, $passwordOwner, $passwordUsuario)
    {
        // Copiamos el PDF sin encriptar
        copy($filePdf, $filePdfEncriptado);
    }

    public function recuperaPrecioConcepto(Request $request)
    {
        $session = $request->getSession();

        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();

        $conceptoId = $_REQUEST['conceptoId'];
        $iva = $_REQUEST['iva'];

        $query = "select importe from tarifa_revision_medica where empresa_id = $empresaId and concepto_id = $conceptoId and anulado = false";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $resultImporteConcepto = $stmt->fetchAll();

        $importe = 0;
        $importeIva = 0;
        $importeTotal = 0;
        if (count($resultImporteConcepto) > 0) {
            $importe = $resultImporteConcepto[0]['importe'];
            $importeIva = $importe * $iva / 100;
            $importeTotal = $importe + (($importe * $iva / 100));
        }
        $data = array(
            'importe' => $importe,
            'iva' => $iva,
            'importeIva' => $importeIva,
            'importeTotal' => $importeTotal
        );
        return new JsonResponse($data);
    }

    public function generarReciboDevolucion(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $giroBancarioDevolucion = $em->getRepository('App\Entity\GiroBancarioDevolucion')->find($id);
        $giroBancario = $giroBancarioDevolucion->getGiroBancario();
        if (!is_null($giroBancario)) {

            $factura = $giroBancario->getFacturacion();
            $countGirosBancariosFactura = $em->getRepository('App\Entity\GiroBancario')->findBy(array('anulado' => false, 'facturacion' => $factura));
            $countRecibos = count($countGirosBancariosFactura) + 1;
            $numFac = $factura->getSerie()->getSerie() . $factura->getNumFac();

            $newGiroBancario = clone $giroBancario;
            $newGiroBancario->setDevolucion(false);
            $newGiroBancario->setFecha(new \DateTime());
            $newGiroBancario->setVencimiento(new \DateTime());
            $newGiroBancario->setObservaciones(null);
            $newGiroBancario->setAnulado(false);
            $newGiroBancario->setRemesado(false);
            $newGiroBancario->setPagoConfirmado(false);
            $newGiroBancario->setEsFactura(false);
            $newGiroBancario->setManual(false);
            $newGiroBancario->setGirado(false);
            $newGiroBancario->setRemesa(null);
            $newGiroBancario->setImporte($giroBancarioDevolucion->getImporte());
            $newGiroBancario->setConcepto('Recibo ' . $countRecibos . ' de la factura ' . $numFac);
            $em->persist($newGiroBancario);
            $em->flush();
        }
        $giroBancarioDevolucion->setReciboGenerado(true);
        $em->persist($giroBancarioDevolucion);
        $em->flush();

        $traduccion = $translator->trans('TRANS_RECIBO_GENERADO_OK');
        $this->addFlash('success', $traduccion);

        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();

        return $this->redirectToRoute('empresa_update', array('id' => $empresaId));
    }

    public function showHuecosFacturas(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getVerHuecosFactura()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $hoy = new \DateTime();
        $year = $hoy->format('Y');

        $listSerieFactura = $em->getRepository('App\Entity\SerieFactura')->findAll();

        $huecos = $this->buscaHuecoFactura($year, "");

        $object = array("json" => $username, "entidad" => "huecos factura", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('facturacion/show_huecos.html.twig', array('huecos' => $huecos, 'listSerieFactura' => $listSerieFactura));
    }

    public function filtraHuecosFactura(Request $request)
    {
        $serieId = $_REQUEST['serie'];
        $hoy = new \DateTime();
        $year = $hoy->format('Y');
        $huecos = $this->buscaHuecoFactura($year, $serieId);

        return new JsonResponse(json_encode($huecos));
    }

    public function showFacturasVencidas(Request $request)
    {
        $session = $request->getSession();

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, a.num_fac, c.empresa, b.descripcion as formapago, d.concepto, d.fecha, d.importe,
                    (select string_agg(tec2.nombre::text, ' , '::text) from tecnico_empresa tec
                    inner join tecnico tec2 on tec.tecnico_id = tec2.id
                    where tec.anulado = false and tec.empresa_id = c.id) as tecnico, e.descripcion as agente, f.nombre as colaborador, a.factura_rectificativa_id from facturacion a 
                        inner join forma_pago b on a.forma_pago_id = b.id
                        inner join empresa c on a.empresa_id = c.id
                        inner join facturacion_vencimiento d on a.id = d.factura_asociada_id 
                        left join comercial e on c.agente_id = e.id
                        left join asesoria f on c.colaborador_id = f.id
                    where a.anulado = false
                        and c.anulado = false
                        and d.anulado = false
                        and d.confirmado = false
                        and a.serie_id = 7
                        and a.id not in (select factura_rectificativa_id from facturacion where anulado = false and factura_rectificativa_id is not null)
                    union all
                    select b.id, b.num_fac, d.empresa, c.descripcion as formapago, a.concepto, a.fecha, a.importe, (select string_agg(tec2.nombre::text, ' , '::text) from tecnico_empresa tec
                    inner join tecnico tec2 on tec.tecnico_id = tec2.id
                    where tec.anulado = false and tec.empresa_id = d.id) as tecnico, e.descripcion as agente, f.nombre as colaborador, b.factura_rectificativa_id from balance_economico_entrada a
                        inner join facturacion b on a.facturacion_id = b.id
                        inner join forma_pago c on b.forma_pago_id = c.id
                        inner join empresa d on b.empresa_id = d.id
                        left join comercial e on d.agente_id = e.id
                        left join asesoria f on d.colaborador_id = f.id
                    where a.anulado = false
                        and b.anulado = false
                        and d.anulado = false
                        and a.pago_confirmado = false
                        and a.tipo = 2
                        and b.serie_id = 7
                        and b.id not in (select factura_rectificativa_id from facturacion where anulado = false and factura_rectificativa_id is not null)
                    order by fecha asc";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $resultFacturasVencidas = $stmt->fetchAll();

        $arrayFacturas = array();
        foreach ($resultFacturasVencidas as $rfv) {
            $facturacionId = $rfv['id'];
            $facturaRectificativaId = $rfv['factura_rectificativa_id'];
            //Ticket#2024060610000085 — Empresa JOSE RAMON PEREA MEGIAS no sale en vencidos
            //Buscamos el importe total para compararlo.
            $query = "select importe_total from facturacion_lineas_pagos where facturacion_id = $facturacionId and anulado = false";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultPreuTotal = $stmt->fetchAll();

            //Buscamos si ese giro tiene mas devoluciones
            $query = "select * from facturacion where serie_id = 6 and factura_asociada_id = $facturacionId and anulado = false";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultAbono = $stmt->fetchAll();

            if (count($resultAbono) == 0) {
                //Buscamos si tiene alguna entrada manual
                $importe = $rfv['importe'];
                $query = "select sum(importe) as importe from balance_economico_entrada where anulado = false and facturacion_id = $facturacionId and pago_confirmado = true";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultEntradaManual = $stmt->fetchAll();
                $pagadoSn = false;

                if (count($resultEntradaManual) > 0) {
                    $importePagado = $resultEntradaManual[0]['importe'];
                    if ($importe == $importePagado) {
                        //Ticket#2024060610000085 — Empresa JOSE RAMON PEREA MEGIAS no sale en vencidos
                        if ($resultPreuTotal[0]['importe_total'] == $importePagado) {
                            $pagadoSn = true;
                        }
                    }
                }
                if (!is_null($facturaRectificativaId)) {
                    $importe = $rfv['importe'];
                    $query = "select sum((b.importe_unidad + b.iva) * b.unidades) as importe from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.id = $facturaRectificativaId and a.anulado = false and b.anulado = false";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $resultFacturaRectificativa = $stmt->fetchAll();
                    if (count($resultFacturaRectificativa) > 0) {
                        $importePagado = $resultFacturaRectificativa[0]['importe'];
                        if ($importe == abs($importePagado)) {
                            $pagadoSn = true;
                        }
                    }
                }
                if (!$pagadoSn) {
                    $item = array();
                    $item['num_fac'] = $rfv['num_fac'];
                    $item['empresa'] = $rfv['empresa'];
                    $item['comercial'] = $rfv['agente'];
                    $item['tecnico'] = $rfv['tecnico'];
                    $item['colaborador'] = $rfv['colaborador'];
                    $item['formapago'] = $rfv['formapago'];
                    $item['concepto'] = $rfv['concepto'];
                    $item['fecha'] = $rfv['fecha'];
                    $item['importe'] = $rfv['importe'];
                    array_push($arrayFacturas, $item);
                }
            }
        }
        $object = array("json" => $username, "entidad" => "facturas vencidas dashboard", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('facturacion/show_facturas_vencidas.html.twig', array('facturas' => $arrayFacturas));
    }

    public function showGirosDevueltos(Request $request)
    {
        $session = $request->getSession();
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select d.id, d.facturacion_id, a.num_fac, c.empresa, d.vencimiento, d.concepto, b.descripcion as formapago, d.importe, 
                    (select string_agg(tec2.nombre::text, ' , '::text) 
                    from tecnico_empresa tec
                    inner join tecnico tec2 on tec.tecnico_id = tec2.id
                    where tec.anulado = false and tec.empresa_id = c.id) as tecnico, e.descripcion as agente, f.nombre as colaborador, a.factura_rectificativa_id from facturacion a
                        inner join forma_pago b on a.forma_pago_id = b.id
                        inner join empresa c on a.empresa_id = c.id
                        inner join giro_bancario d on a.id = d.facturacion_id 
                        left join comercial e on c.agente_id = e.id
                        left join asesoria f on c.colaborador_id = f.id
                    where a.anulado = false 
                        and b.forma_pago_contable = 8
                        and c.anulado = false
                        and d.anulado = false
                        and d.devolucion = true
                        and a.id NOT IN(19270, 19706, 18451, 32737)
                        and a.id not in (select factura_rectificativa_id from facturacion where anulado = false and factura_rectificativa_id is not null)
                    order by d.id asc";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $resultGirosDevueltos = $stmt->fetchAll();

        $arrayGiros = array();
        foreach ($resultGirosDevueltos as $rgd) {
            $giroId = $rgd['id'];
            $facturacionId = $rgd['facturacion_id'];
            $facturaRectificativaId = $rgd['factura_rectificativa_id'];

            $query = "select * from giro_bancario where id > $giroId and facturacion_id = $facturacionId and anulado = false";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultGiro = $stmt->fetchAll();

            $query2 = "select devolucion from giro_bancario where id = $giroId and facturacion_id = $facturacionId and anulado = false";
            $stmt2 = $this->getDoctrine()->getManager()->getConnection()->prepare($query2);
            $stmt2->execute();
            $resultGiro2 = $stmt2->fetchAll();

            if (count($resultGiro) == 0 || $resultGiro2[0]['devolucion'] == true) {
                $query = "select * from facturacion where serie_id = 6 and factura_asociada_id = $facturacionId and anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultAbono = $stmt->fetchAll();

                if (count($resultAbono) == 0) {
                    $importe = $rgd['importe'];
                    $query = "select sum(importe) as importe from balance_economico_entrada where anulado = false and facturacion_id = $facturacionId and pago_confirmado = true";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $resultEntradaManual = $stmt->fetchAll();

                    $pagadoSn = false;
                    if (count($resultEntradaManual) > 0) {
                        $importePagado = $resultEntradaManual[0]['importe'];
                        if ($importe == $importePagado || $importePagado >= $importe) {
                            $pagadoSn = true;
                        }
                    }

                    $query2 = "select * from facturacion_vencimiento where factura_asociada_id = $facturacionId and anulado = false";
                    $stmt2 = $this->getDoctrine()->getManager()->getConnection()->prepare($query2);
                    $stmt2->execute();
                    $resultGirosYaPagados = $stmt2->fetchAll();

                    if (count($resultGirosYaPagados) > 0) {
                        $pagadoSn = true;
                    }
                    if (!is_null($facturaRectificativaId)) {
                        $importe = $rgd['importe'];
                        $query = "select sum((b.importe_unidad + b.iva) * b.unidades) as importe from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.id = $facturaRectificativaId and a.anulado = false and b.anulado = false";
                        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                        $stmt->execute();
                        $resultFacturaRectificativa = $stmt->fetchAll();
                        if (count($resultFacturaRectificativa) > 0) {
                            $importePagado = $resultFacturaRectificativa[0]['importe'];
                            if ($importe == abs($importePagado)) {
                                $pagadoSn = true;
                            }
                        }
                    }
                    $query3 = "select importe_total from facturacion_lineas_pagos where facturacion_id = $facturacionId and anulado = false";
                    $stmt3 = $this->getDoctrine()->getManager()->getConnection()->prepare($query3);
                    $stmt3->execute();
                    $resultGiro3 = $stmt3->fetchAll();

                    $query4 = "select SUM(importe) as importe from giro_bancario where facturacion_id = $facturacionId and anulado = false and devolucion != true";
                    $stmt4 = $this->getDoctrine()->getManager()->getConnection()->prepare($query4);
                    $stmt4->execute();
                    $resultGiro4 = $stmt4->fetchAll();

                    if (count($resultEntradaManual) > 0) {
                        $importePagado = $resultEntradaManual[0]['importe'];
                        $importeTotalAux = $importePagado + $resultGiro4[0]['importe'];
                        if ($importe <= $importeTotalAux) {
                            $pagadoSn = true;
                        }
                    } else {
                        if ($importe <= $resultGiro4[0]['importe']) {
                            $pagadoSn = true;
                        }
                    }
                    // Forzamos a que se muestren los giros devueltos
                    $pagadoSn = ($facturacionId == 34165 || $facturacionId == 29406)
                        ? false : $pagadoSn;

                    if (!$pagadoSn) {
                        $item = array();
                        $item['num_fac'] = $rgd['num_fac'];
                        $item['empresa'] = $rgd['empresa'];
                        $item['comercial'] = $rgd['agente'];
                        $item['tecnico'] = $rgd['tecnico'];
                        $item['colaborador'] = $rgd['colaborador'];
                        $item['vencimiento'] = $rgd['vencimiento'];
                        $item['concepto'] = $rgd['concepto'];
                        $item['formapago'] = $rgd['formapago'];
                        $item['importe'] = $rgd['importe'];
                        array_push($arrayGiros, $item);
                    }
                }
            }
        }
        $object = array("json" => $username, "entidad" => "giros devueltos dashboard", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('facturacion/show_giros_devueltos.html.twig', array('giros' => $arrayGiros));
    }

    public function preSendFactura(Request $request)
    {
        $session = $request->getSession();
        $facturasSelect = $_REQUEST['facturas'];
        $session->set('facturasSeleccionadasMultiple', $facturasSelect);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    function buscaFacturas($ini, $fin, $renovada, $pagadaSn, $noPagadaSn)
    {
        $query = "select a.id, concat(c.serie,a.num_fac) as num_fac, to_char(a.fecha, 'DD/MM/YYYY') as fecha, to_char(a.fecha, 'YYYYMMDDHHmm') as fechatimestamp, to_char(a.fecha, 'MM') as mes, to_char(a.fecha, 'YYYY') as any,
                a.renovacion,
                b.empresa,
                i.descripcion as agente, 
                j.nombre as partner,
                string_agg(h.nombre::text, ' , '::text) AS tecnico, 
                b.trabajadores, a.fichero_id, d.descripcion as formaPago, e.nombre as responsableAdministrativo, f.nombre as medico, a.enviada
                from facturacion a
                inner join empresa b on a.empresa_id = b.id
                inner join serie_factura c on a.serie_id = c.id
                inner join forma_pago d on a.forma_pago_id = d.id
                left join tecnico e on b.gestor_administrativo_id = e.id
                left join tecnico f on b.vigilancia_salud_id = f.id
                left join tecnico_empresa g on b.id = g.empresa_id 
                left join tecnico h on g.tecnico_id = h.id
                left join comercial i on b.agente_id = i.id
                left join asesoria j on b.colaborador_id = j.id
                where a.anulado = false ";

        if ($ini != "") {
            $query .= " and a.fecha >= '$ini 00:00:00' ";
        }
        if ($fin != "") {
            $query .= " and a.fecha <= '$fin 23:59:59' ";
        }
        if ($renovada != "") {
            switch ($renovada) {
                case '1':
                    $query .= " and a.renovacion = true ";
                    break;
                case '0':
                    $query .= " and a.renovacion = false ";
            }
        }
        if ($pagadaSn) {
            $query .= " and a.serie_id = 7 and a.id in (select facturacion_id from facturacion_lineas_pagos where anulado = false and facturado = true) ";
        }
        if ($noPagadaSn) {
            $query .= " and a.serie_id = 7 and a.id not in (select facturacion_id from facturacion_lineas_pagos where anulado = false) ";
        }
        $query .= "group by a.id, c.serie, b.empresa, b.marca_comercial, b.trabajadores, d.descripcion, e.nombre, f.nombre, i.descripcion, j.nombre order by a.fecha desc";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $facturas = $stmt->fetchAll();

        $arrayFacturas = array();

        foreach ($facturas as $f) {
            $facturaId = $f['id'];

            $queryPagos = "select a.concepto, a.importe_sin_iva, a.importe_total, a.importe_iva, a.unidades, b.codigo from facturacion_lineas_pagos a left join concepto b on a.concepto_facturacion_id = b.id where a.facturacion_id = $facturaId and a.anulado = false";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($queryPagos);
            $stmt->execute();
            $resultPagos = $stmt->fetchAll();

            if (count($resultPagos) > 0) {
                foreach ($resultPagos as $rp) {
                    $item = array();
                    $item['id'] = $facturaId;
                    $item['num_fac'] = $f['num_fac'];
                    $item['fecha'] = $f['fecha'];
                    $item['fechatimestamp'] = $f['fechatimestamp'];
                    $item['mes'] = $f['mes'];
                    $item['any'] = $f['any'];
                    $item['renovacion'] = $f['renovacion'];
                    $item['empresa'] = $f['empresa'];
                    $item['agente'] = $f['agente'];
                    $item['partner'] = $f['partner'];
                    $item['tecnico'] = $f['tecnico'];
                    $item['trabajadores'] = $f['trabajadores'];
                    $item['fichero_id'] = $f['fichero_id'];
                    $item['formapago'] = $f['formapago'];
                    $item['responsableadministrativo'] = $f['responsableadministrativo'];
                    $item['medico'] = $f['medico'];
                    $item['enviada'] = $f['enviada'];
                    $item['codigo'] = $rp['codigo'];
                    $item['cantidad'] = $rp['unidades'];
                    $item['texto'] = $rp['concepto'];
                    $item['importe'] = $rp['importe_total'];

                    if ($rp['unidades'] > 1 && $rp['importe_iva'] != 0) {
                        if (($rp['importe_total'] - $rp['importe_iva']) != $rp['importe_sin_iva']) {
                            $importeSinIva = $rp['importe_total'] - ($rp['importe_total'] * 0.21);
                            $item['importesiniva'] = $importeSinIva;
                            $item['iva'] = $item['importesiniva'] * 0.21;
                        } else {
                            $item['importesiniva'] = $rp['importe_sin_iva'];
                            $item['iva'] = $rp['importe_iva'];
                        }
                    } else {
                        $item['importesiniva'] = $rp['importe_sin_iva'];
                        $item['iva'] = $rp['importe_iva'];
                    }
                    array_push($arrayFacturas, $item);
                }
            } else {
                $queryConceptos = "select a.concepto, a.importe, a.importe_unidad, a.iva, a.unidades, a.iva_sn, a.concepto_facturacion_id, b.codigo from facturacion_lineas_conceptos a
                left join concepto b on a.concepto_facturacion_id = b.id
                where a.facturacion_id = $facturaId 
                and a.anulado = false";

                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($queryConceptos);
                $stmt->execute();
                $resultConceptos = $stmt->fetchAll();

                foreach ($resultConceptos as $rc) {
                    $item = array();
                    $item['id'] = $facturaId;
                    $item['num_fac'] = $f['num_fac'];
                    $item['fecha'] = $f['fecha'];
                    $item['fechatimestamp'] = $f['fechatimestamp'];
                    $item['mes'] = $f['mes'];
                    $item['any'] = $f['any'];
                    $item['renovacion'] = $f['renovacion'];
                    $item['empresa'] = $f['empresa'];
                    $item['agente'] = $f['agente'];
                    $item['partner'] = $f['partner'];
                    $item['tecnico'] = $f['tecnico'];
                    $item['trabajadores'] = $f['trabajadores'];
                    $item['fichero_id'] = $f['fichero_id'];
                    $item['formapago'] = $f['formapago'];
                    $item['responsableadministrativo'] = $f['responsableadministrativo'];
                    $item['medico'] = $f['medico'];
                    $item['enviada'] = $f['enviada'];
                    $item['codigo'] = $rc['codigo'];
                    $item['cantidad'] = $rc['unidades'];
                    $item['texto'] = $rc['concepto'];
                    $item['importe'] = $rc['importe'];

                    if ($rc['unidades'] > 1 && $rc['iva'] != 0) {
                        if (($rc['importe'] - $rc['iva']) != $rc['importe_unidad']) {
                            $importeSinIva = $rc['importe'] - ($rc['importe'] * 0.21);
                            $item['importesiniva'] = $importeSinIva;
                            $item['iva'] = $item['importesiniva'] * 0.21;
                        } else {
                            $item['importesiniva'] = $rc['importe_unidad'];
                            $item['iva'] = $rc['iva'];
                        }
                    } else {
                        $item['importesiniva'] = $rc['importe_unidad'] * $rc['unidades'];
                        $item['iva'] = $rc['iva'];
                    }
                    array_push($arrayFacturas, $item);
                }
            }
        }
        return $arrayFacturas;
    }

    function buscaHuecoFactura($year, $serieId)
    {
        $query = "select distinct a.num_fac, b.descripcion as serie, a.numero from facturacion a inner join serie_factura b on a.serie_id = b.id where a.fecha >= '$year-01-01' and a.fecha <= '$year-12-31' and a.anyo = '$year' and a.anulado = true ";

        if ($serieId != "") {
            $query .= " and a.serie_id = $serieId and a.num_fac not in (select num_fac from facturacion where anulado = false and fecha >= '$year-01-01' and fecha <= '$year-12-31' and anyo = '$year' and serie_id = $serieId)";
        } else {
            $query .= " and a.num_fac not in (select num_fac from facturacion where anulado = false and fecha >= '$year-01-01' and fecha <= '$year-12-31' and anyo = '$year')";
        }
        $query .= " order by a.numero asc";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $resultFacturacionAnulada = $stmt->fetchAll();

        return $resultFacturacionAnulada;
    }
}