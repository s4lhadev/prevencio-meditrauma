<?php

namespace App\Controller;

use App\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    public function index(Request $request)
    {
        // Recogemos los privilegios del usuario
        $session = $request->getSession();
        $user = $this->getUser();
        $rol = $user->getRol();
        $rolId = $rol->getId();

        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);

        // Comprobamos si el usuario tiene la password expirada
        $credentialsExpired = $usuario->getCredentialsExpired();
        if ($credentialsExpired) {
            return $this->redirectToRoute('change_password_otic');
        }
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $session->set('privilegiosRolIntranet', $rol);
        $privilegios = $session->get('privilegiosRolIntranet');
        if (!is_null($privilegios)) {
            if (!$privilegios->getIntranetSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $administradorSn = true;
        $empresaUserIntranet = $this->getDoctrine()->getRepository('App\Entity\UserIntranet')->findOneBy(array('usuario' => $usuario));
        $empresa = $session->get('empresaIntranet');

        if (is_null($empresa)) {
            if (is_null($empresaUserIntranet)) {
                if ($rolId == 2) {
                    return $this->redirectToRoute('error_403');
                } else {
                    $empresa = $this->getDoctrine()->getRepository('App\Entity\Empresa')->findOneBy(array('anulado' => false));
                    $session->set('empresaIntranet', $empresa);
                }
            } else {
                $session->set('empresaIntranet', $empresaUserIntranet->getEmpresa());
                $empresa = $empresaUserIntranet->getEmpresa();
                $administradorSn = false;
            }
        } else {
            if (!is_null($empresaUserIntranet)) {
                $administradorSn = false;
            }
        }
        $session->set('administrador', $administradorSn);

        if (!$administradorSn) {
            // Comprobamos si la empresa esta activa
            if ($empresa->getAnulado()) {
                return $this->redirectToRoute('error_403');
            }
            // Comprobamos si la empresa esta marcada como impagada
            if (!is_null($empresa->getEstadoAreaAdministracion())) {
                $estadoAreaAdministrativoId = $empresa->getEstadoAreaAdministracion()->getId();
                if ($estadoAreaAdministrativoId != 4) {
                    return $this->redirectToRoute('error_403');
                }
            }
        }
        $object = array("json" => $username, "entidad" => "intranet", "id" => $id);

        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->redirectToRoute('certificaciones_show');
    }

    public function showCertificaciones(Request $request)
    {
        $session = $request->getSession();
        $empresa = $session->get('empresaIntranet');
        $empresaId = $empresa->getId();

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, a.fichero_id, coalesce(c.nombre, b.nombre) as nombre from empresa_certificacion a inner join gdoc_fichero b on a.fichero_id = b.id and (b.anulado = false or b.anulado is null) left join gdoc_plantillas c on b.plantilla_id = c.id where a.empresa_id = $empresaId and a.enviada = true";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $certificaciones = $stmt->fetchAll();

        $object = array("json" => $username, "entidad" => "contratos", "id" => $id);

        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('certificaciones/show.html.twig', array('certificaciones' => $certificaciones));
    }

    public function showContratos(Request $request)
    {
        $session = $request->getSession();
        $empresa = $session->get('empresaIntranet');
        $empresaId = $empresa->getId();

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, a.contrato, to_char(a.fechainicio, 'DD/MM/YYYY') as fechainicio, to_char(a.fechainicio, 'YYYYMMDDHHmm') as fechainiciotimestamp, 
            to_char(c.fechavencimiento, 'DD/MM/YYYY') as fechavencimiento, 
            to_char(c.fechavencimiento, 'YYYYMMDDHHmm') as fechavencimientotimestamp, a.fichero_id, concat(d.id, ' - ', d.descripcion) as tipo from contrato a 
            inner join empresa b on a.empresa_id = b.id
            inner join renovacion c on a.id = c.contrato_id
            left join codigo_empresa d on b.codigo_empresa_id = d.id
            where a.anulado = false
            and c.anulado = false
            and a.empresa_id = $empresaId
            and a.cancelado = false
            order by a.fechainicio desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $contratos = $stmt->fetchAll();

        $object = array("json" => $username, "entidad" => "contratos", "id" => $id);

        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('contrato/show.html.twig', array('contratos' => $contratos));
    }

    public function showFacturas(Request $request)
    {
        $session = $request->getSession();
        $empresa = $session->get('empresaIntranet');
        $empresaId = $empresa->getId();

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, a.num_fac, a.fecha, a.fichero_id from facturacion a where a.anulado = false and a.empresa_id = $empresaId group by a.id, a.num_fac, a.fecha, a.fichero_id order by fecha desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $facturasEmpresa = $stmt->fetchAll();

        $facturas = array();
        foreach ($facturasEmpresa as $fe) {
            $item = array();

            $item['id'] = $fe['id'];
            $item['num_fac'] = $fe['num_fac'];
            $item['fecha'] = $fe['fecha'];
            $item['fichero_id'] = $fe['fichero_id'];

            $facturaId = $fe['id'];

            $query = "select * from facturacion_lineas_pagos where facturacion_id = $facturaId and anulado = false";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $facturaPagos = $stmt->fetchAll();

            $importe = 0;
            if (count($facturaPagos) > 0) {
                foreach ($facturaPagos as $fp) {
                    $importe += $fp['importe_sin_iva'];
                }
            } else {
                $query = "select * from facturacion_lineas_conceptos where facturacion_id = $facturaId and anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $facturaConceptos = $stmt->fetchAll();

                foreach ($facturaConceptos as $fc) {
                    $importe += $fc['importe_unidad'] * $fc['unidades'];
                }
            }
            $item['importe_total'] = $importe;

            array_push($facturas, $item);
        }
        $object = array("json" => $username, "entidad" => "facturas", "id" => $id);

        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('facturacion/show.html.twig', array('facturas' => $facturas));
    }

    public function showRevisiones(Request $request)
    {
        $session = $request->getSession();
        $empresa = $session->get('empresaIntranet');
        $empresaId = $empresa->getId();

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, a.fecha, a.fichero_id, a.apto_id, c.nombre as trabajador, d.descripcion as puesto from revision a left join gdoc_fichero b on a.fichero_id = b.id inner join trabajador c on a.trabajador_id = c.id inner join puesto_trabajo_centro d on a.puesto_trabajo_id = d.id where a.empresa_id = $empresaId and a.anulado = false and c.anulado = false and a.aptitud_enviada = true order by a.fecha desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $revisiones = $stmt->fetchAll();

        $object = array("json" => $username, "entidad" => "revisiones", "id" => $id);

        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('revision/show.html.twig', array('revisiones' => $revisiones));
    }

    public function showEvaluaciones(Request $request)
    {
        $session = $request->getSession();
        $empresa = $session->get('empresaIntranet');
        $empresaId = $empresa->getId();

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, a.finalizada, b.empresa, string_agg(f.nombre::text || ' ' ||f.apellido1::text || ' ' ||f.apellido2::text, ' / '::text) as tecnicos,
                to_char(a.fecha_inicio, 'DD/MM/YYYY') as fechainicio, to_char(a.fecha_inicio, 'YYYYMMDDHHmm') as fechainiciotimestamp, to_char(a.fecha_fin, 'DD/MM/YYYY') as fechafin, to_char(a.fecha_fin, 'YYYYMMDDHHmm') as fechafintimestamp, d.descripcion as tipo,
                a.fichero_id from evaluacion a 
                inner join empresa b on a.empresa_id = b.id
                inner join evaluacion_centro_trabajo c on a.id = c.evaluacion_id
                inner join tipo_evaluacion d on a.tipo_evaluacion_id = d.id
                left join tecnico_evaluacion e on a.id = e.evaluacion_id
                left join usuario_tecnico f on e.tecnico_id = f.id 
                where a.anulado = false
                and a.empresa_id = $empresaId
                and a.finalizada = true
                group by a.id, a.finalizada, b.empresa, a.fecha_inicio, a.fecha_fin, d.descripcion, a.fichero_id
                order by a.fecha_inicio desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $evaluaciones = $stmt->fetchAll();

        $object = array("json" => $username, "entidad" => "evaluaciones", "id" => $id);

        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('evaluacion/show.html.twig', array('evaluaciones' => $evaluaciones));
    }

    public function showManualVs(Request $request)
    {
        $session = $request->getSession();
        $empresa = $session->get('empresaIntranet');
        $empresaId = $empresa->getId();

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select id, fecha, fichero_id from empresa_manual_vs 
                where empresa_id = $empresaId
                order by fecha desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $manualvs = $stmt->fetchAll();

        $object = array("json" => $username, "entidad" => "manual vs", "id" => $id);

        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('manualvs/show.html.twig', array('manualvs' => $manualvs));
    }

    public function showDocumentosFormacion(Request $request)
    {
        $session = $request->getSession();
        $empresa = $session->get('empresaIntranet');
        $empresaId = $empresa->getId();

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "SELECT a.id, a.nombre_completo 
                  FROM gdoc_empresa a
                  INNER JOIN gdoc_empresa_carpeta b ON a.carpeta_id = b.id 
                  WHERE a.empresa_id = :empresaId 
                  AND b.nombre LIKE '%FORMACIÓN%'
                  AND (a.anulado = false OR a.anulado IS NULL)
                  ORDER BY a.nombre ASC";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->executeQuery(['empresaId' => $empresaId]);
        $formacion = $stmt->fetchAll();

        $object = array("json" => $username, "entidad" => "documento formación", "id" => $id);

        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('formacion/show.html.twig', array('formacion' => $formacion));
    }

    public function showDocumentosAdicionales(Request $request)
    {
        $session = $request->getSession();
        $empresa = $session->get('empresaIntranet');
        $empresaId = $empresa->getId();

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "SELECT a.id, a.nombre_completo 
                  FROM gdoc_empresa a
                  INNER JOIN gdoc_empresa_carpeta b ON a.carpeta_id = b.id 
                  WHERE a.empresa_id = :empresaId 
                  AND b.nombre LIKE '%DOCUMENTOS ADICIONALES%'
                  AND (a.anulado = false OR a.anulado IS NULL)
                  ORDER BY a.nombre ASC";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->executeQuery(['empresaId' => $empresaId]);
        $documentosAdicionales = $stmt->fetchAll();

        $object = array("json" => $username, "entidad" => "documento adicionales", "id" => $id);

        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('documentosadicionales/show.html.twig', array('documentosAdicionales' => $documentosAdicionales));
    }
}
