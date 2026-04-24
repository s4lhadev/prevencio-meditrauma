<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Logger;

class HomeController extends AbstractController
{
    public function index(Request $request)
    {
        //Recogemos los privilegios del usuario
        $session = $request->getSession();
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);

        //Comprobamos si el usuario tiene la password expirada
        $credentialsExpired = $usuario->getCredentialsExpired();
        if ($credentialsExpired) {
            return $this->redirectToRoute('change_password_otic');
        }
        $rol = $user->getRol();
        $rolId = $rol->getId();

        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $session->set('privilegiosRol', $rol);
        $session->set('rol', $rolId);
        $session->set('empresa', null);

        switch ($rolId) {
            case 1:
            case 5:
            case 10:
                //Calculamos las revisiones pendientes de facturar
                $revisiones = $this->revisionesPendientesFacturar();
                $session->set('revisionesPendientesFacturar', count($revisiones));

                //Buscamos los giros bancarios sin remesar
                $query = "select count(*) as total from giro_bancario where remesa_id is null";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultGirosNoRemesados = $stmt->fetchAll();
                $girosNoRemesados = $resultGirosNoRemesados[0]['total'];

                //Buscamos los giros bancarios remesados
                $query = "select count(*) as total from giro_bancario where remesa_id is not null";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultGirosRemesados = $stmt->fetchAll();
                $girosRemesados = $resultGirosRemesados[0]['total'];

                //Buscamos los giros bancarios no girados
                $query = "select count(*) as total from giro_bancario where girado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultGirosNoGirados = $stmt->fetchAll();
                $girosNoGirados = $resultGirosNoGirados[0]['total'];

                //Buscamos los giros bancarios girados
                $query = "select count(*) as total from giro_bancario where girado = true";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultGirosGirados = $stmt->fetchAll();
                $girosGirados = $resultGirosGirados[0]['total'];

                //Buscamos los pagos pendientes
                $query = "select count(*) as total from contrato_pago a inner join contrato b on a.contrato_id = b.id where a.facturado = false and b.cancelado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultPagosPendientes = $stmt->fetchAll();
                $pagosPendientes = $resultPagosPendientes[0]['total'];

                //Buscamos los pagos facturados
                $query = "select count(*) as total from contrato_pago a inner join contrato b on a.contrato_id = b.id where a.facturado = true and b.cancelado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultPagosPagados = $stmt->fetchAll();
                $pagosPagados = $resultPagosPagados[0]['total'];

                //Buscamos las facturas pagadas
                $query = "select count(*) as total from facturacion a 
                    inner join empresa b on a.empresa_id = b.id  
                    where a.anulado = false
                    and b.anulado = false
                    and a.serie_id = 7
                    and a.id in (select facturacion_id from facturacion_lineas_pagos where anulado = false and facturado = true)";

                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultFacturasPagadas = $stmt->fetchAll();
                $facturasPagadas = $resultFacturasPagadas[0]['total'];

                //Buscamos las facturas no pagadas
                $query = "select count(*) as total from facturacion a 
                    inner join empresa b on a.empresa_id = b.id 
                    where a.anulado = false
                    and b.anulado = false
                    and a.serie_id = 7
                    and a.id not in (select facturacion_id from facturacion_lineas_pagos where anulado = false and facturado = true)";

                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultFacturasNoPagadas = $stmt->fetchAll();
                $facturasNoPagadas = $resultFacturasNoPagadas[0]['total'];

                //Buscamos los contratos renovados
                $query = "select count(*) as total from renovacion where renovado = true";

                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultContratosRenovados = $stmt->fetchAll();
                $contratosRenovados = $resultContratosRenovados[0]['total'];

                //Buscamos los contratos no renovados
                $query = "select count(*) as total from renovacion where renovado = false";

                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultContratosNoRenovados = $stmt->fetchAll();
                $contratosNoRenovados = $resultContratosNoRenovados[0]['total'];

                //Buscamos los giros devueltos
                $query = "select d.id, d.facturacion_id, d.importe, a.factura_rectificativa_id from facturacion a
                inner join forma_pago b on a.forma_pago_id = b.id
                inner join empresa c on a.empresa_id = c.id
                inner join giro_bancario d on a.id = d.facturacion_id 
                where a.anulado = false 
                and b.forma_pago_contable = 8
                and c.anulado = false
                and d.anulado = false
                and d.devolucion = true
                and a.id not in (select factura_rectificativa_id from facturacion where anulado = false and factura_rectificativa_id is not null)
                order by a.id asc";

                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultGirosDevueltos = $stmt->fetchAll();
                $countGirosDevueltos = 0;
                $importeGirosDevueltos = 0;
                foreach ($resultGirosDevueltos as $rgd) {
                    $giroId = $rgd['id'];
                    $facturacionId = $rgd['facturacion_id'];
                    $facturaRectificativaId = $rgd['factura_rectificativa_id'];

                    //Buscamos si ese giro tiene mas devoluciones
                    $query = "select * from giro_bancario where id > $giroId and facturacion_id = $facturacionId and anulado = false";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();

                    $resultGiro = $stmt->fetchAll();
                    if (count($resultGiro) == 0) {
                        //Buscamos si tiene un abono
                        $query = "select * from facturacion where serie_id = 6 and factura_asociada_id = $facturacionId and anulado = false";
                        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                        $stmt->execute();
                        $resultAbono = $stmt->fetchAll();
                        if (count($resultAbono) == 0) {
                            //Buscamos si tiene alguna entrada manual
                            $importe = $rgd['importe'];
                            $query = "select sum(importe) as importe from balance_economico_entrada where anulado = false and facturacion_id = $facturacionId and pago_confirmado = true";
                            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                            $stmt->execute();
                            $resultEntradaManual = $stmt->fetchAll();
                            $pagadoSn = false;
                            if (count($resultEntradaManual) > 0) {
                                $importePagado = $resultEntradaManual[0]['importe'];
                                if ($importe == $importePagado) {
                                    $pagadoSn = true;
                                }
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
                            if (!$pagadoSn) {
                                $importeGirosDevueltos += round($rgd['importe'], 2);
                                $countGirosDevueltos++;
                            }
                        }
                    }
                }
                //Buscamos las facturas vencidas
                $query = "select a.id, d.importe, a.factura_rectificativa_id from facturacion a 
                inner join forma_pago b on a.forma_pago_id = b.id
                inner join empresa c on a.empresa_id = c.id
                inner join facturacion_vencimiento d on a.id = d.factura_asociada_id 
                where a.anulado = false
                and c.anulado = false
                and d.anulado = false
                and d.confirmado = false
                and a.id not in (select factura_rectificativa_id from facturacion where anulado = false and factura_rectificativa_id is not null)
                and a.serie_id = 7
                union all
                select b.id, bee.importe, b.factura_rectificativa_id from balance_economico_entrada bee
                inner join facturacion b on bee.facturacion_id = b.id
                inner join empresa c on bee.empresa_id = c.id
                where bee.anulado = false
                and b.anulado = false
                and c.anulado = false
                and bee.tipo = 2
                and bee.pago_confirmado = false
                and b.id not in (select factura_rectificativa_id from facturacion where anulado = false and factura_rectificativa_id is not null)
                and b.serie_id = 7";

                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultFacturasVencidas = $stmt->fetchAll();
                $countFacturasVencidas = 0;
                $importeFacturasVencidas = 0;
                foreach ($resultFacturasVencidas as $rfv) {
                    $facturacionId = $rfv['id'];
                    $facturaRectificativaId = $rfv['factura_rectificativa_id'];
                    //Buscamos si esta factura tiene abono
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
                                $pagadoSn = true;
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
                            $importeFacturasVencidas += round($rfv['importe'], 2);
                            $countFacturasVencidas++;
                        }
                    }
                }
                $object = array("json" => $username, "entidad" => "dashboard", "id" => $id);

                $logger = new Logger();
                $em = $this->getDoctrine()->getManager();
                $logger->addLog($em, "select", $object, $usuario, TRUE);
                $em->flush();

                return $this->render('dashboard/index.html.twig', array('girosNoRemesados' => $girosNoRemesados, 'girosNoGirados' => $girosNoGirados, 'girosRemesados' => $girosRemesados, 'girosGirados' => $girosGirados, 'pagosPendientes' => $pagosPendientes, 'pagosPagados' => $pagosPagados, 'facturasPagadas' => $facturasPagadas, 'facturasNoPagadas' => $facturasNoPagadas, 'contratosRenovados' => $contratosRenovados, 'contratosNoRenovados' => $contratosNoRenovados, 'facturasVencidas' => $countFacturasVencidas, 'importeFacturasVencidas' => $importeFacturasVencidas, 'girosDevueltos' => $countGirosDevueltos, 'importeGirosDevueltos' => $importeGirosDevueltos));

                break;
            case 2:
                return $this->render('dashboard/index_intranet.html.twig', array());
                break;
            case 3:
                return $this->render('dashboard/index_tecnico.html.twig', array());
                break;
            case 4:
                return $this->render('dashboard/index_medico.html.twig', array());
                break;
            default:
                $object = array("json" => $username, "entidad" => "dashboard", "id" => $id);

                $logger = new Logger();
                $em = $this->getDoctrine()->getManager();
                $logger->addLog($em, "select", $object, $usuario, TRUE);
                $em->flush();

                return $this->render('dashboard/index_default.html.twig', array());
        }
    }

    public function dashboardAdminNotify(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEnviarCorreoMasivoSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $tipoEmpresa = $this->getDoctrine()->getRepository('App\Entity\CodigoEmpresa')->findAll();

        $personal = $this->buscaPersonal($username);

        $object = array("json" => $username, "entidad" => "correo masivo", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('dashboard/show2.html.twig', array('personal' => $personal, 'tipoEmpresa' => $tipoEmpresa));
    }

    function buscaPersonal($username)
    {
        $query = "select id, destinatario, remitente, mensaje, fecha, leido from notificaciones_internas where destinatario like '$username'ORDER BY leido ASC,fecha DESC";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $personal = $stmt->fetchAll();

        return $personal;
    }

    public function dashboardAdmin(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getFacturarRevisionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        //Buscamos las revisiones firmadas y que estan pendientes de facturar
        $revisiones = $this->revisionesPendientesFacturar();

        $object = array("json" => $username, "entidad" => "dashboard administración", "id" => $id);
        $em = $this->getDoctrine()->getManager();
        $logger = new Logger();
        $logger->addLog($em, "logout", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('dashboard/dashboard_admin.html.twig', array('revisiones' => $revisiones));
    }

    public function marcarLeido(Request $request)
    {
        $data = $request->request->all();
        $selectedRows = $data['selectedRows'];
        $ids = implode(',', $selectedRows);
        $connection = $this->getDoctrine()->getManager()->getConnection();

        $query = "UPDATE notificaciones_internas SET leido=true WHERE id IN ($ids)";
        $statement = $connection->prepare($query);
        $statement->execute();

        // Por ejemplo, si deseas obtener los registros actualizados
        $querySelectUpdated = "SELECT * FROM notificaciones_internas WHERE id IN ($ids)";
        $statementSelect = $connection->prepare($querySelectUpdated);
        $statementSelect->execute();
        $updatedRows = $statementSelect->fetchAll();

        return $updatedRows;
    }

    function revisionesMensajesInternos()
    {
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select id, destinatario, remitente, mensaje, fecha, leido from notificaciones_internas where destinatario like '$username' and leido = false or leido = null";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $revisiones = $stmt->fetchAll();

        return $revisiones;
    }

    function revisionesPendientesFacturar()
    {
        $query = "select a.id, to_char(a.fecha, 'DD/MM/YYYY') as fecha, to_char(a.fecha, 'YYYYMMDDHHmm') as fechatimestamp, b.nombre as trabajador, b.dni, c.descripcion as puesto, d.empresa, a.apto_id, a.fichero_id, e.descripcion as doctor, g.descripcion as agenda, a.fichero_resumen_id, a.aptitud_enviada, a.estado_id, a.pruebas_complementarias from revision a
        inner join trabajador b on a.trabajador_id = b.id
        inner join puesto_trabajo_centro c on a.puesto_trabajo_id = c.id
        inner join empresa d on a.empresa_id = d.id
        left join doctor e on a.medico_id = e.id
        left join citacion f on a.citacion_id = f.id
        left join agenda g on f.agenda_id = g.id
        where a.anulado = false
        and b.anulado = false
        and a.estado_id in (3,4)
        and a.aptitud_enviada = false
        and a.fichero_resumen_id is not null
        and a.fichero_id is not null
        order by a.fecha desc";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $revisiones = $stmt->fetchAll();

        return $revisiones;
    }

    public function calcularRevisionesPendientesFacturar(Request $request)
    {
        $session = $request->getSession();
        $revisiones = $this->revisionesPendientesFacturar();

        $data = array(
            'count' => count($revisiones)
        );
        //Calculamos las revisiones pendientes de facturar
        $session->set('revisionesPendientesFacturar', count($revisiones));

        return new JsonResponse($data);
    }

    public function calcularMensajesInternos(Request $request)
    {
        $session = $request->getSession();
        $revisiones = $this->revisionesMensajesInternos();

        $data = array(
            'count' => count($revisiones)
        );
        //Calculamos las revisiones pendientes de facturar
        $session->set('mensajesNoLeidos', count($revisiones));

        return new JsonResponse($data);
    }
}
