<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
*/

namespace App\Controller;

use DateTime;
use App\Entity\LogEnvioMail;
use App\Entity\Revision;
use App\Entity\RevisionDocumentoAdjunto;
use App\Entity\RevisionRespuesta;
use App\Entity\RevisionSubRespuesta;
use App\Form\CheckDownResumenType;
use App\Form\EnviarCuestionarioRevisionType;
use App\Form\RevisionType;
use App\Logger;
use PhpOffice\PhpWord\Shared\ZipArchive;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Description of RevisionController
 *
 * @author smarin
 */
class RevisionController extends AbstractController
{
    function checkRevision($empresa, $trabajador, $fecha)
    {
        $fechaString = $fecha->format('Y-m-d');
        $empresaId = $empresa->getId();
        $trabajadorId = $trabajador->getId();

        $query = "select id from revision where anulado = false and empresa_id = $empresaId and trabajador_id = $trabajadorId and fecha = '$fechaString 00:00:00' order by id desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $resultRevision = $stmt->fetchAll();

        $revisionId = -1;
        if (count($resultRevision) > 0) {
            $revisionId = $resultRevision[0]['id'];
        }
        return $revisionId;
    }

    public function showRevision(Request $request)
    {
        $session = $request->getSession();
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $session->set('revisionRiesgoCausaId', null);

        //Buscamos las plantillas de la carpeta resumen revision
        $carpetaResumenRevision = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(16);
        $plantillasResumen = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaResumenRevision, 'anulado' => false));

        //Buscamos las plantillas de la carpeta aptitud
        $carpetaAptitud = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(14);
        $plantillasAptitud = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaAptitud, 'anulado' => false));

        //Buscamos los estados
        $estados = $this->getDoctrine()->getRepository('App\Entity\EstadoRevision')->findBy(array('anulado' => false));

        //Buscamos las aptitudes
        $aptitudes = $this->getDoctrine()->getRepository('App\Entity\Apto')->findBy(array('anulado' => false));

        $object = array("json" => $username, "entidad" => "revisiones", "id" => $id);
        $em = $this->getDoctrine()->getManager();
        $logger = new Logger();
        $logger->addLog($em, "logout", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('revision/show.html.twig', array('listPlantillasResumen' => $plantillasResumen, 'listPlantillasAptitud' => $plantillasAptitud, 'estados' => $estados, 'aptos' => $aptitudes));
    }

    public function dataRevisiones(Request $request)
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
        $rolId = $privilegios->getId();

        $dql = "SELECT a.id as id, a.fecha as fecha, b.nombre as trabajador, c.descripcion as puesto, d.empresa as empresa, IDENTITY(a.apto) as apto, 
        IDENTITY(a.fichero) as fichero_id, e.id as estadoid, e.descripcion as estado, IDENTITY(a.ficheroResumen) as fichero_resumen_id, 
        f.descripcion as doctor, a.aptitudEnviada as aptitud_enviada, b.dni, a.pruebasComplementarias, h.descripcion as agenda, CASE WHEN b.idRiesgos is not null then b.idRiesgos else b.id end as codigo,
        a.fechaEnvio, a.fechaFirma, a.usuarioFirma, i.descripcion as restriccion
        FROM App\Entity\Revision a 
        JOIN a.trabajador b
        JOIN a.puestoTrabajo c
        JOIN a.empresa d
        LEFT JOIN a.estado e
        LEFT JOIN a.medico f
        LEFT JOIN a.citacion g
        LEFT JOIN g.agenda h
        LEFT JOIN a.aptitudRestriccion i
        WHERE a.anulado = false
        and b.anulado = false ";

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

        $arrayRevisiones = array();
        foreach ($paginator as $r) {
            $item = array();

            $actions = '<div class="list-icons">';
            if (!is_null($privilegios)) {

                if ($privilegios->getEditRevisionSn()) {
                    $route = $this->generateUrl('medico_revision_update', array('id' => $r['id']));
                    $actions .= '<a href="' . $route . '" target="_blank" class="list-icons-item" data-popup="tooltip" title="Editar" data-container="body"><i class="icon-pencil7"></i></a>';
                }
                if ($privilegios->getPrintAptitudRevisionSn()) {
                    if (!is_null($r['fichero_id'])) {
                        $route = $this->generateUrl('gdoc_open_file', array('fileId' => $r['fichero_id'], 'tipo' => 11));
                        $actions .= '<a href="' . $route . '" target="_blank" class="list-icons-item" data-popup="tooltip" title="Imprimir aptitud" data-container="body"><i class="icon-file-check"></i></a>';
                        if ($privilegios->getDeleteGdocFileSn()) {
                            $actions .= '<a href="#" data-id="' . $r['fichero_id'] . '" class="list-icons-item eliminarAptitud" data-popup="tooltip" title="Eliminar aptitud" data-container="body"><i class="icon-file-minus"></i></a>';
                        }
                    } else {
                        $actions .= '<a href="#" data-id="' . $r['id'] . '" class="list-icons-item selectAptitud" data-popup="tooltip" title="Imprimir aptitud" data-container="body" data-toggle="modal" data-target="#modal_print_aptitud"><i class="icon-file-check"></i></a>';
                    }
                }
                if ($privilegios->getPrintResumenRevisionSn()) {
                    if (!is_null($r['fichero_resumen_id'])) {
                        $route = $this->generateUrl('gdoc_open_file', array('fileId' => $r['fichero_resumen_id'], 'tipo' => 13));
                        $actions .= '<a href="' . $route . '" target="_blank" class="list-icons-item" data-popup="tooltip" title="Imprimir resumen" data-container="body"><i class="icon-file-text2"></i></a>';
                        if ($privilegios->getDeleteGdocFileSn()) {
                            $actions .= '<a href="#" data-id="' . $r['fichero_resumen_id'] . '" class="list-icons-item eliminarResumen" data-popup="tooltip" title="Eliminar resumen" data-container="body"><i class="icon-file-minus"></i></a>';
                        }
                    } else {
                        $actions .= '<a href="#" data-id="' . $r['id'] . '" class="list-icons-item selectRevision" data-popup="tooltip" title="Imprimir resumen" data-container="body" data-toggle="modal" data-target="#modal_resumen_revision"><i class="icon-file-text2"></i></a>';
                    }
                }
                if ($privilegios->getEnviarAptitudRevisionSn()) {
                    if ($r['estadoid'] == 4 && $r['aptitud_enviada']) {
                        $route = $this->generateUrl('medico_revision_enviar_aptitud') . "?id=" . $r['id'];
                        $actions .= '<a href="' . $route . '" class="list-icons-item" data-popup="tooltip" title="Reenviar aptitud" data-container="body"><i class="icon-file-upload"></i></a>';
                    }
                }
                if ($privilegios->getDeleteRevisionSn()) {
                    $actions .= '<a href="#" data-id="' . $r['id'] . '" class="list-icons-item eliminarRevision" data-popup="tooltip" title="Eliminar" data-container="body"><i class="icon-trash"></i></a>';
                }
            }

            $actions .= '</div>';

            if ($r['estadoid'] == 4 && ($rolId != 1 && $rolId != 5)) {
                $actions = '<div class="list-icons">';

                if (!is_null($privilegios)) {

                    if ($privilegios->getEditRevisionSn()) {
                        $route = $this->generateUrl('medico_revision_update', array('id' => $r['id']));
                        $actions .= '<a href="' . $route . '" target="_blank" class="list-icons-item" data-popup="tooltip" title="Editar" data-container="body"><i class="icon-pencil7"></i></a>';
                    }
                    if ($privilegios->getPrintAptitudRevisionSn()) {
                        if (!is_null($r['fichero_id'])) {
                            $route = $this->generateUrl('gdoc_open_file', array('fileId' => $r['fichero_id'], 'tipo' => 11));
                            $actions .= '<a href="' . $route . '" target="_blank" class="list-icons-item" data-popup="tooltip" title="Imprimir aptitud" data-container="body"><i class="icon-file-check"></i></a>';
                            if ($privilegios->getDeleteGdocFileSn()) {
                                $actions .= '<a href="#" data-id="' . $r['fichero_id'] . '" class="list-icons-item eliminarAptitud" data-popup="tooltip" title="Eliminar aptitud" data-container="body"><i class="icon-file-minus"></i></a>';
                            }
                        } else {
                            $actions .= '<a href="#" data-id="' . $r['id'] . '" class="list-icons-item selectAptitud" data-popup="tooltip" title="Imprimir aptitud" data-container="body" data-toggle="modal" data-target="#modal_print_aptitud"><i class="icon-file-check"></i></a>';
                        }
                    }
                    if ($privilegios->getPrintResumenRevisionSn()) {
                        if (!is_null($r['fichero_resumen_id'])) {
                            $route = $this->generateUrl('gdoc_open_file', array('fileId' => $r['fichero_resumen_id'], 'tipo' => 13));
                            $actions .= '<a href="' . $route . '" target="_blank" class="list-icons-item" data-popup="tooltip" title="Imprimir resumen" data-container="body"><i class="icon-file-text2"></i></a>';
                            if ($privilegios->getDeleteGdocFileSn()) {
                                $actions .= '<a href="#" data-id="' . $r['fichero_resumen_id'] . '" class="list-icons-item eliminarResumen" data-popup="tooltip" title="Eliminar resumen" data-container="body"><i class="icon-file-minus"></i></a>';
                            }
                        } else {
                            $actions .= '<a href="#" data-id="' . $r['id'] . '" class="list-icons-item selectRevision" data-popup="tooltip" title="Imprimir resumen" data-container="body" data-toggle="modal" data-target="#modal_resumen_revision"><i class="icon-file-text2"></i></a>';
                        }
                    }
                }
                $actions .= '</div>';
            }
            $item['actions'] = $actions;

            if ($r['estadoid'] == "") {
                $item['estado'] = '';
            } else {
                if ($r['estadoid'] == 1) {
                    $item['estado'] = '<span class="badge badge-primary"><i class="icon-eye-blocked"></i> ' . $r['estado'] . '</span>';
                } elseif ($r['estadoid'] == 2) {
                    $item['estado'] = '<span class="badge badge-warning"><i class="icon-eye"></i> ' . $r['estado'] . '</span>';
                } elseif ($r['estadoid'] == 3) {
                    $item['estado'] = '<span class="badge badge-success"><i class="icon-highlight"></i> ' . $r['estado'] . '</span>';
                } elseif ($r['estadoid'] == 4) {
                    $item['estado'] = '<span class="badge badge-danger"><i class="icon-cash"></i> ' . $r['estado'] . '</span>';
                } else {
                    $item['estado'] = '<span class="badge badge-warning"><i class="icon-reload-alt"></i> ' . $r['estado'] . '</span>';
                }
            }
            $fecha = "";
            if (!is_null($r['fecha'])) {
                $fecha = $r['fecha']->format('d/m/Y');
            }
            $item['fecha'] = $fecha;
            $item['trabajador'] = $r['trabajador'];
            $item['dni'] = $r['dni'];
            $item['puesto'] = $r['puesto'];
            $item['empresa'] = $r['empresa'];
            $item['doctor'] = $r['doctor'];

            if ($r['apto'] == 1) {
                $item['apto'] = '<span class="badge badge-success"><i class="icon-check"></i></span>';
            } elseif ($r['apto'] == 2) {
                $item['apto'] = '<span class="badge badge-warning"><i class="icon-check"></i></span>';
            } elseif ($r['apto'] == 3) {
                $item['apto'] = '<span class="badge badge-danger"><i class="icon-cross"></i></span>';
            } else {
                $item['apto'] = '';
            }
            if ($r['aptitud_enviada']) {
                $item['enviada'] = '<span class="badge badge-success"><i class="icon-check"></i></span>';
            } else {
                $item['enviada'] = '<span class="badge badge-danger"><i class="icon-cross"></i></span>';
            }
            $item['pruebas'] = $r['pruebasComplementarias'];
            $item['agenda'] = $r['agenda'];
            $item['codigo'] = $r['codigo'];

            $fechaEnvio = "";
            if (!is_null($r['fechaEnvio'])) {
                $fechaEnvio = $r['fechaEnvio']->format('d/m/Y');
            }
            $item['fechaEnvio'] = $fechaEnvio;

            $fechaFirma = "";
            if (!is_null($r['fechaFirma'])) {
                $fechaFirma = $r['fechaFirma']->format('d/m/Y');
            }
            $item['fechaFirma'] = $fechaFirma;
            $item['usuarioFirma'] = $r['usuarioFirma'];
            $item['restriccion'] = $r['restriccion'];

            array_push($arrayRevisiones, $item);
        }
        return $this->json([
            'draw' => $draw,
            'recordsTotal' => $length,
            'recordsFiltered' => $recordsTotal,
            'data' => $arrayRevisiones,
            'dql' => $dql,
            'dqlCountFiltered' => '',
        ]);
    }

    public function filtraRevisiones(Request $request)
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
            $estado = $_REQUEST['estado'];
            $apto = $_REQUEST['apto'];
        } else {
            die();
        }
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        $rolId = $privilegios->getId();

        $dql = "SELECT a.id as id, a.fecha as fecha, b.nombre as trabajador, c.descripcion as puesto, d.empresa as empresa, IDENTITY(a.apto) as apto, 
        IDENTITY(a.fichero) as fichero_id, e.id as estadoid, e.descripcion as estado, IDENTITY(a.ficheroResumen) as fichero_resumen_id, 
        f.descripcion as doctor, a.aptitudEnviada as aptitud_enviada, b.dni, a.pruebasComplementarias, h.descripcion as agenda, CASE WHEN b.idRiesgos is not null then b.idRiesgos else b.id end as codigo,
        a.fechaEnvio, a.fechaFirma, a.usuarioFirma, i.descripcion as restriccion
        FROM App\Entity\Revision a 
        JOIN a.trabajador b
        JOIN a.puestoTrabajo c
        JOIN a.empresa d
        LEFT JOIN a.estado e
        LEFT JOIN a.medico f
        LEFT JOIN a.citacion g
        LEFT JOIN g.agenda h
        LEFT JOIN a.aptitudRestriccion i
        WHERE a.anulado = false
        and b.anulado = false ";

        if ($dtini != "") {
            $dql .= " and a.fecha >= '$dtini' ";
        }
        if ($dtfin != "") {
            $dql .= " and a.fecha <= '$dtfin' ";
        }
        if ($estado != "") {
            $dql .= " and a.estado = $estado ";
        }
        if ($apto != "") {
            $dql .= " and a.apto = $apto ";
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

        $arrayRevisiones = array();
        foreach ($paginator as $r) {
            $item = array();

            $actions = '<div class="list-icons">';
            if (!is_null($privilegios)) {

                if ($privilegios->getEditRevisionSn()) {
                    $route = $this->generateUrl('medico_revision_update', array('id' => $r['id']));
                    $actions .= '<a href="' . $route . '" target="_blank" class="list-icons-item" data-popup="tooltip" title="Editar" data-container="body"><i class="icon-pencil7"></i></a>';
                }
                if ($privilegios->getPrintAptitudRevisionSn()) {
                    if (!is_null($r['fichero_id'])) {
                        $route = $this->generateUrl('gdoc_open_file', array('fileId' => $r['fichero_id'], 'tipo' => 11));
                        $actions .= '<a href="' . $route . '" target="_blank" class="list-icons-item" data-popup="tooltip" title="Imprimir aptitud" data-container="body"><i class="icon-file-check"></i></a>';
                        if ($privilegios->getDeleteGdocFileSn()) {
                            $actions .= '<a href="#" data-id="' . $r['fichero_id'] . '" class="list-icons-item eliminarAptitud" data-popup="tooltip" title="Eliminar aptitud" data-container="body"><i class="icon-file-minus"></i></a>';
                        }
                    } else {
                        $actions .= '<a href="#" data-id="' . $r['id'] . '" class="list-icons-item selectAptitud" data-popup="tooltip" title="Imprimir aptitud" data-container="body" data-toggle="modal" data-target="#modal_print_aptitud"><i class="icon-file-check"></i></a>';
                    }
                }
                if ($privilegios->getPrintResumenRevisionSn()) {
                    if (!is_null($r['fichero_resumen_id'])) {
                        $route = $this->generateUrl('gdoc_open_file', array('fileId' => $r['fichero_resumen_id'], 'tipo' => 13));
                        $actions .= '<a href="' . $route . '" target="_blank" class="list-icons-item" data-popup="tooltip" title="Imprimir resumen" data-container="body"><i class="icon-file-text2"></i></a>';
                        if ($privilegios->getDeleteGdocFileSn()) {
                            $actions .= '<a href="#" data-id="' . $r['fichero_resumen_id'] . '" class="list-icons-item eliminarResumen" data-popup="tooltip" title="Eliminar resumen" data-container="body"><i class="icon-file-minus"></i></a>';
                        }
                    } else {
                        $actions .= '<a href="#" data-id="' . $r['id'] . '" class="list-icons-item selectRevision" data-popup="tooltip" title="Imprimir resumen" data-container="body" data-toggle="modal" data-target="#modal_resumen_revision"><i class="icon-file-text2"></i></a>';
                    }
                }
                if ($privilegios->getEnviarAptitudRevisionSn()) {
                    if ($r['estadoid'] == 4 && $r['aptitud_enviada']) {
                        $route = $this->generateUrl('medico_revision_enviar_aptitud') . "?id=" . $r['id'];
                        $actions .= '<a href="' . $route . '" class="list-icons-item" data-popup="tooltip" title="Reenviar aptitud" data-container="body"><i class="icon-file-upload"></i></a>';
                    }
                }
                if ($privilegios->getDeleteRevisionSn()) {
                    $actions .= '<a href="#" data-id="' . $r['id'] . '" class="list-icons-item eliminarRevision" data-popup="tooltip" title="Eliminar" data-container="body"><i class="icon-trash"></i></a>';
                }
            }

            $actions .= '</div>';

            if ($r['estadoid'] == 4 && ($rolId != 1 && $rolId != 5)) {
                $actions = '<div class="list-icons">';

                if (!is_null($privilegios)) {

                    if ($privilegios->getEditRevisionSn()) {
                        $route = $this->generateUrl('medico_revision_update', array('id' => $r['id']));
                        $actions .= '<a href="' . $route . '" target="_blank" class="list-icons-item" data-popup="tooltip" title="Editar" data-container="body"><i class="icon-pencil7"></i></a>';
                    }
                    if ($privilegios->getPrintAptitudRevisionSn()) {
                        if (!is_null($r['fichero_id'])) {
                            $route = $this->generateUrl('gdoc_open_file', array('fileId' => $r['fichero_id'], 'tipo' => 11));
                            $actions .= '<a href="' . $route . '" target="_blank" class="list-icons-item" data-popup="tooltip" title="Imprimir aptitud" data-container="body"><i class="icon-file-check"></i></a>';
                            if ($privilegios->getDeleteGdocFileSn()) {
                                $actions .= '<a href="#" data-id="' . $r['fichero_id'] . '" class="list-icons-item eliminarAptitud" data-popup="tooltip" title="Eliminar aptitud" data-container="body"><i class="icon-file-minus"></i></a>';
                            }
                        } else {
                            $actions .= '<a href="#" data-id="' . $r['id'] . '" class="list-icons-item selectAptitud" data-popup="tooltip" title="Imprimir aptitud" data-container="body" data-toggle="modal" data-target="#modal_print_aptitud"><i class="icon-file-check"></i></a>';
                        }
                    }
                    if ($privilegios->getPrintResumenRevisionSn()) {
                        if (!is_null($r['fichero_resumen_id'])) {
                            $route = $this->generateUrl('gdoc_open_file', array('fileId' => $r['fichero_resumen_id'], 'tipo' => 13));
                            $actions .= '<a href="' . $route . '" target="_blank" class="list-icons-item" data-popup="tooltip" title="Imprimir resumen" data-container="body"><i class="icon-file-text2"></i></a>';
                            if ($privilegios->getDeleteGdocFileSn()) {
                                $actions .= '<a href="#" data-id="' . $r['fichero_resumen_id'] . '" class="list-icons-item eliminarResumen" data-popup="tooltip" title="Eliminar resumen" data-container="body"><i class="icon-file-minus"></i></a>';
                            }
                        } else {
                            $actions .= '<a href="#" data-id="' . $r['id'] . '" class="list-icons-item selectRevision" data-popup="tooltip" title="Imprimir resumen" data-container="body" data-toggle="modal" data-target="#modal_resumen_revision"><i class="icon-file-text2"></i></a>';
                        }
                    }
                }
                $actions .= '</div>';
            }
            $item['actions'] = $actions;

            if ($r['estadoid'] == "") {
                $item['estado'] = '';
            } else {
                if ($r['estadoid'] == 1) {
                    $item['estado'] = '<span class="badge badge-primary"><i class="icon-eye-blocked"></i> ' . $r['estado'] . '</span>';
                } elseif ($r['estadoid'] == 2) {
                    $item['estado'] = '<span class="badge badge-warning"><i class="icon-eye"></i> ' . $r['estado'] . '</span>';
                } elseif ($r['estadoid'] == 3) {
                    $item['estado'] = '<span class="badge badge-success"><i class="icon-highlight"></i> ' . $r['estado'] . '</span>';
                } elseif ($r['estadoid'] == 4) {
                    $item['estado'] = '<span class="badge badge-danger"><i class="icon-cash"></i> ' . $r['estado'] . '</span>';
                } else {
                    $item['estado'] = '<span class="badge badge-warning"><i class="icon-reload-alt"></i> ' . $r['estado'] . '</span>';
                }
            }

            $fecha = "";
            if (!is_null($r['fecha'])) {
                $fecha = $r['fecha']->format('d/m/Y');
            }
            $item['fecha'] = $fecha;
            $item['trabajador'] = $r['trabajador'];
            $item['dni'] = $r['dni'];
            $item['puesto'] = $r['puesto'];
            $item['empresa'] = $r['empresa'];
            $item['doctor'] = $r['doctor'];

            if ($r['apto'] == 1) {
                $item['apto'] = '<span class="badge badge-success"><i class="icon-check"></i></span>';
            } elseif ($r['apto'] == 2) {
                $item['apto'] = '<span class="badge badge-warning"><i class="icon-check"></i></span>';
            } elseif ($r['apto'] == 3) {
                $item['apto'] = '<span class="badge badge-danger"><i class="icon-cross"></i></span>';
            } else {
                $item['apto'] = '';
            }
            if ($r['aptitud_enviada']) {
                $item['enviada'] = '<span class="badge badge-success"><i class="icon-check"></i></span>';
            } else {
                $item['enviada'] = '<span class="badge badge-danger"><i class="icon-cross"></i></span>';
            }
            $item['pruebas'] = $r['pruebasComplementarias'];
            $item['agenda'] = $r['agenda'];
            $item['codigo'] = $r['codigo'];

            $fechaEnvio = "";
            if (!is_null($r['fechaEnvio'])) {
                $fechaEnvio = $r['fechaEnvio']->format('d/m/Y');
            }
            $item['fechaEnvio'] = $fechaEnvio;

            $fechaFirma = "";
            if (!is_null($r['fechaFirma'])) {
                $fechaFirma = $r['fechaFirma']->format('d/m/Y');
            }
            $item['fechaFirma'] = $fechaFirma;
            $item['usuarioFirma'] = $r['usuarioFirma'];
            $item['restriccion'] = $r['restriccion'];

            array_push($arrayRevisiones, $item);
        }
        return $this->json([
            'draw' => $draw,
            'recordsTotal' => $length,
            'recordsFiltered' => $recordsTotal,
            'data' => $arrayRevisiones,
            'dql' => $dql,
            'dqlCountFiltered' => '',
        ]);
    }

    public function createRevision(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getAddRevisionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $session->set('revisionId', null);

        $arrayTrabajadorId = array();

        //Creamos el objeto
        $revision = new Revision();

        $empresa = null;
        $empresaId = null;
        if (isset($_REQUEST['empresaId'])) {
            $empresa = $em->getRepository('App\Entity\Empresa')->find($_REQUEST['empresaId']);
            $session->set('empresa', $empresa);
            $revision->setFecha(new \DateTime());

            $vigilanciaSalud = $empresa->getVigilanciaSalud();
            if (!is_null($vigilanciaSalud)) {
                $medico = $vigilanciaSalud->getMedico();
                if (!is_null($medico)) {
                    $revision->setMedico($medico);
                }
            }
        }
        $trabajadorId = null;
        $trabajador = null;
        if (isset($_REQUEST['trabajadorId'])) {
            $trabajador = $em->getRepository('App\Entity\Trabajador')->find($_REQUEST['trabajadorId']);
            $trabajadorId = $trabajador->getId();
            $revision->setTelefono($trabajador->getTelefono1());
            //            $revision->setDni($trabajador->getDni());
            //            $revision->setFechaNacimiento($trabajador->getFechaNacimiento());
            array_push($arrayTrabajadorId, $trabajadorId);
        }
        $citacion = null;
        if (isset($_REQUEST['citacionId'])) {
            $citacion = $em->getRepository('App\Entity\Citacion')->find($_REQUEST['citacionId']);
            $estadosPermitidos = [2, 3, 7]; // Realizado, Finalizado, Pasada
            if (!is_null($citacion) && !is_null($citacion->getEstado()) && !in_array($citacion->getEstado()->getId(), $estadosPermitidos)) {
                $this->addFlash('danger', 'No se puede crear una revisión con el estado actual de la citación');
                return $this->redirectToRoute('citacion_update', array('id' => $citacion->getId()));
            }
            $revision->setCitacion($citacion);
            $revision->setPruebasComplementarias($citacion->getPruebasComplementarias());
            $revision->setFecha($citacion->getFechainicio());
        }
        if (!is_null($empresa) && !is_null($trabajador) && !is_null($citacion)) {
            $revisionId = $this->checkRevision($empresa, $trabajador, $citacion->getFechainicio());

            if ($revisionId != -1) {
                return $this->redirectToRoute('medico_revision_update', array('id' => $revisionId));
            }
        }
        $empresaId = null;
        $empresa = null;
        $listTrabajadores = null;
        $puestoTrabajoId = null;
        $puestoTrabajo = null;

        //Comprobamos si hay una empresa seleccionada
        $empresa = $session->get('empresa');
        if (!is_null($empresa)) {
            $empresa = $em->getRepository('App\Entity\Empresa')->find($empresa->getId());
            $revision->setEmpresa($empresa);

            $empresaId = $empresa->getId();

            //Buscamos los trabajadores
            $query = "select distinct c.id, trim(c.nombre) as descripcion from empresa a 
            inner join trabajador_empresa b on a.id = b.empresa_id
            inner join trabajador c on b.trabajador_id = c.id
            where a.anulado = false
            and b.anulado = false
            and c.anulado = false
            and a.id = $empresaId
            order by trim(c.nombre) asc";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $trabajadorEmpresa = $stmt->fetchAll();

            $listTrabajadores = array();
            foreach ($trabajadorEmpresa as $te) {
                array_push($listTrabajadores, $te['id']);
            }
            if (!is_null($trabajador)) {
                $query = "select b.id, b.descripcion from puesto_trabajo_trabajador a
                inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                where a.anulado = false
                and b.anulado = false 
                and a.trabajador_id = $trabajadorId
                and b.empresa_id = $empresaId
                and a.fecha_baja is null
                order by b.descripcion asc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultPuestoTrabajo = $stmt->fetchAll();

                if (count($resultPuestoTrabajo) > 0) {
                    $puestoTrabajoId = $resultPuestoTrabajo[0]['id'];
                    $puestoTrabajo = $em->getRepository('App\Entity\PuestoTrabajoCentro')->find($puestoTrabajoId);
                    $revision->setPuestoTrabajo($puestoTrabajo);
                }
            }
        }
        $validez = $em->getRepository('App\Entity\ValidezAptitud')->find(1);
        $revision->setValidez($validez);

        $form = $this->createForm(RevisionType::class, $revision, array('trabajadorObj' => $trabajador, 'empresaObj' => $empresa, 'empresaId' => $empresaId, 'puestoTrabajoObj' => $puestoTrabajo, 'listTrabajadores' => $listTrabajadores, 'puestoTrabajoId' => $puestoTrabajoId, 'protocolos' => null, 'trabajadorId' => $arrayTrabajadorId, 'citacionObj' => $citacion, 'puestoTrabajoActualizadoSn' => null, 'disabledProtocolosSn' => false));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $revision = $form->getData();
            $em = $this->getDoctrine()->getManager();

            //Obtenemos la empresa
            if (is_null($empresa)) {
                $empresa = $revision->getEmpresa();
            }
            //Obtenemos el trabajador
            $trabajadorFormId = $form["trabajador"]->getViewData();
            $trabajador = $em->getRepository('App\Entity\Trabajador')->find($trabajadorFormId);
            $revision->setTrabajador($trabajador);

            if (!is_null($revision->getTelefono())) {
                $telefono = $revision->getTelefono();
                if (is_null($trabajador->getTelefono1())  || $trabajador->getTelefono1() == "") {
                    $trabajador->setTelefono1($telefono);
                    $em->persist($trabajador);
                }
            }
            $dni = $form["dni"]->getViewData();
            if (!is_null($dni) && $dni != "") {
                if ($dni != $trabajador->getDni()) {
                    $trabajador->setDni($dni);
                    $em->persist($trabajador);
                }
            }
            $fechaNacimiento = $form["fechaNacimiento"]->getData();
            if (!is_null($fechaNacimiento) && $fechaNacimiento != "") {
                if ($fechaNacimiento != $trabajador->getFechaNacimiento()) {
                    $trabajador->setFechaNacimiento($fechaNacimiento);
                    $em->persist($trabajador);
                }
            }
            //Obtenemos la cita
            $citacionFormId = $form["citacion"]->getViewData();
            if (!is_null($citacionFormId) && $citacionFormId != "") {
                $citacion = $em->getRepository('App\Entity\Citacion')->find($citacionFormId);
                $revision->setCitacion($citacion);
            }
            //Obtenemos el puesto de trabajo
            $puestoTrabajoFormId = $form["puestoTrabajo"]->getViewData();
            $puestoTrabajo = $em->getRepository('App\Entity\PuestoTrabajoCentro')->find($puestoTrabajoFormId);
            $revision->setPuestoTrabajo($puestoTrabajo);

            if (!is_null($revision->getEmpresa()) && !is_null($trabajador) && !is_null($revision)) {
                $revisionId = $this->checkRevision($revision->getEmpresa(), $trabajador, $revision->getFecha());

                if ($revisionId != -1) {
                    $traduccion = $translator->trans('TRANS_REVISION_EXISTE', array(), 'revision');
                    $this->addFlash('danger',  $traduccion);
                    return $this->redirectToRoute('medico_revision_add');
                }
            }
            $em->persist($revision);
            $em->flush();

            //Generamos los cuestionarios que debe rellenar
            $protocolos = $em->getRepository('App\Entity\PuestoTrabajoProtocolo')->findBy(array('puestoTrabajo' => $puestoTrabajo, 'empresa' => $empresa, 'anulado' => false));
            foreach ($protocolos as $p) {

                //Buscamos los cuestionarios del protocolo
                $protocoloCuestionario = $em->getRepository('App\Entity\ProtocoloCuestionario')->findBy(array('protocolo' => $p->getProtocolo(), 'anulado' => false));
                foreach ($protocoloCuestionario as $pc) {

                    //Buscamos las preguntas del cuestionario
                    $cuestionarioPregunta = $em->getRepository('App\Entity\CuestionarioPregunta')->findBy(array('cuestionario' => $pc->getCuestionario(), 'anulado' => false));
                    foreach ($cuestionarioPregunta as $cp) {

                        if (!is_null($cp->getCuestionario()->getTipoCuestionario())) {
                            if ($cp->getCuestionario()->getTipoCuestionario()->getId() == 1) {
                                $cuestionarioRevision = new RevisionRespuesta();
                                $cuestionarioRevision->setRevision($revision);
                                $cuestionarioRevision->setCuestionario($cp->getCuestionario());
                                $cuestionarioRevision->setPregunta($cp->getPregunta());

                                //Si tiene respuesta por defecto la informamos
                                if (!is_null($cp->getPregunta()->getValorPorDefecto()) && $cp->getPregunta()->getValorPorDefecto() != "") {
                                    $serieRespuesta = $cp->getPregunta()->getSerieRespuesta();
                                    if (!is_null($serieRespuesta)) {
                                        $indicador = $serieRespuesta->getIndicador();
                                        if (!is_null($indicador)) {
                                            if ($indicador->getId() == 0) {
                                                $respuestaDefault = $cp->getPregunta()->getValorPorDefecto();
                                            } else {
                                                $respuestaDefault = str_replace(";", "", $cp->getPregunta()->getValorPorDefecto());
                                            }
                                        } else {
                                            $respuestaDefault = str_replace(";", "", $cp->getPregunta()->getValorPorDefecto());
                                        }
                                    } else {
                                        $respuestaDefault = str_replace(";", "", $cp->getPregunta()->getValorPorDefecto());
                                    }
                                } else {
                                    $respuestaDefault = "";
                                }
                                $cuestionarioRevision->setRespuesta($respuestaDefault);
                                $em->persist($cuestionarioRevision);
                                $em->flush();

                                //Si la pregunta es de tipo SUB generamos los registros por cada respuesta
                                if (!is_null($cp->getPregunta()->getTipoRespuesta())) {
                                    $tipoRespuestaId = $cp->getPregunta()->getTipoRespuesta()->getId();
                                    if ($tipoRespuestaId == 6) {

                                        $cuestionarioRevision->setRespuesta("");
                                        $em->persist($cuestionarioRevision);
                                        $em->flush();

                                        $subPreguntas = $em->getRepository('App\Entity\SubPregunta')->findBy(array('pregunta' => $cp->getPregunta(), 'anulado' => false), array('orden' => 'ASC'));
                                        foreach ($subPreguntas as $sp) {
                                            $revisionSubRespuesta = new RevisionSubRespuesta();
                                            $revisionSubRespuesta->setOrden($sp->getOrden());
                                            $revisionSubRespuesta->setRevisionRespuesta($cuestionarioRevision);
                                            $revisionSubRespuesta->setCuestionarioPregunta($cp);
                                            $revisionSubRespuesta->setRespuesta($respuestaDefault);
                                            $em->persist($revisionSubRespuesta);
                                            $em->flush();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('medico_revision_update', array('id' => $revision->getId()));
        }
        return $this->render('revision/edit.html.twig', array('form' => $form->createView(), 'protocolos' => null, 'puestoTrabajo' => $puestoTrabajo));
    }

    public function updateRevision(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditRevisionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $username = $usuario->getUsername();

        $rolId = $privilegios->getId();

        $session->set('revisionRiesgoCausaId', $id);
        $session->set('revisionId', $id);

        $arrayTrabajadorId = array();

        //Creamos el objeto
        $revision = $em->getRepository('App\Entity\Revision')->find($id);
        $trabajadorRevision = $revision->getTrabajador();
        $puestoTrabajoRevision = $revision->getPuestoTrabajo();
        $empresa = $revision->getEmpresa();
        $empresaId = $empresa->getId();
        $numeroPeticion = $revision->getNumeroPeticion();
        $fechaResultadoAnalitica = $revision->getFechaRecuperacionResultado();

        $analiticasConfig = $this->getDoctrine()->getRepository('App\Entity\AnaliticasConfig')->find(1);
        $carpetaResultadosAnaliticas = $analiticasConfig->getCarpetaResultadoAnalitica();
        $urlDocumento = "";

        if (!is_null($numeroPeticion) && !is_null($fechaResultadoAnalitica)) {
            $fechaLimite = new DateTime('2024-11-01 00:00:00');

            // Comparar la fecha de la analítica con la fecha límite
            if ($fechaResultadoAnalitica < $fechaLimite) {
                // Si la fecha es menor que el 1 de noviembre de 2024, usar la extensión .PDF
                $urlDocumento = "../../../upload/media/$carpetaResultadosAnaliticas/$id/$numeroPeticion.PDF";
            } else {
                // Si la fecha es posterior o igual, usar la extensión .pdf
                $urlDocumento = "../../../upload/media/$carpetaResultadosAnaliticas/$id/$numeroPeticion.pdf";
            }
        }
        //Buscamos los trabajadores
        $query = "select distinct c.id, trim(c.nombre) as descripcion from empresa a 
        inner join trabajador_empresa b on a.id = b.empresa_id
        inner join trabajador c on b.trabajador_id = c.id
        where b.anulado = false
        and c.anulado = false
        and a.id = $empresaId
        order by trim(c.nombre) asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $trabajadorEmpresa = $stmt->fetchAll();

        $listTrabajadores = array();
        foreach ($trabajadorEmpresa as $te) {
            array_push($listTrabajadores, $te['id']);
        }
        if (!is_null($revision->getTrabajador())) {
            array_push($arrayTrabajadorId, $revision->getTrabajador()->getId());
        }
        //Buscamos los protocolos del puesto de trabajo
        $protocoloRepo = $this->getDoctrine()->getRepository('App\Entity\Protocolo');
        $protocolos = $em->getRepository('App\Entity\PuestoTrabajoProtocolo')->findBy(array('puestoTrabajo' => $puestoTrabajoRevision, 'empresa' => $empresa, 'anulado' => false), array('protocolo' => 'ASC'));

        //Buscamos las respuestas
        $puestoTrabajoId = $puestoTrabajoRevision->getId();
        $query = "select distinct f.id, f.descripcion as cuestionario, f.orden from revision a
            inner join empresa b on a.empresa_id = b.id 
            inner join puesto_trabajo_centro c on a.puesto_trabajo_id = c.id
            inner join puesto_trabajo_protocolo d on c.id = d.puesto_trabajo_id 
            inner join protocolo_cuestionario e on d.protocolo_id = e.protocolo_id
            inner join cuestionario f on e.cuestionario_id = f.id
            where a.id = $id
            and a.empresa_id = $empresaId
            and a.puesto_trabajo_id = $puestoTrabajoId
            and b.id = $empresaId
            and c.id = $puestoTrabajoId
            and d.puesto_trabajo_id = $puestoTrabajoId
            and d.empresa_id = $empresaId
            and a.anulado = false
            and c.anulado = false
            and d.anulado = false
            and e.anulado = false
            and f.anulado = false
            and f.tipo_cuestionario_id = 1
            order by f.orden asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $cuestionariosRellenar = $stmt->fetchAll();

        //Generamos la tabla de resumen
        $resumen = $this->generarResumen($id);

        //Buscamos los protocolos del puesto de trabajo
        $protocolosSelect = array();
        if (count($protocolos) > 0) {
            foreach ($protocolos as $p) {
                $protocolo = $p->getProtocolo();
                if (!is_null($protocolo)) {
                    array_push($protocolosSelect, $protocolo);
                }
            }
        }
        $puestoTrabajoActualizadoSn = false;
        $disableProtocolosSn = false;

        if ($puestoTrabajoRevision->getActualizado()) {
            $puestoTrabajoActualizadoSn = true;
            $disableProtocolosSn = true;
        }
        //Buscamos las recomendaciones
        $query = "select distinct b.id as pregunta, replace(a.respuesta, ';', '') as respuesta, b.serie_respuesta_id from revision_respuesta a
            inner join pregunta b on a.pregunta_id = b.id
            inner join cuestionario_pregunta c on a.pregunta_id = c.pregunta_id
            inner join respuesta e on b.serie_respuesta_id = e.serie_respuesta_id 
            where a.revision_id = $id
            and b.serie_respuesta_id is not null
            and e.consejo_medico_id is not null
            order by b.id asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $respuestasPreguntas = $stmt->fetchAll();

        $serieRespuestaRepo = $this->getDoctrine()->getRepository('App\Entity\SerieRespuesta');
        $respuestaRepo = $this->getDoctrine()->getRepository('App\Entity\Respuesta');
        $arrayConsejosMedicos = array();

        foreach ($respuestasPreguntas as $rp) {

            $respuestaText = $rp['respuesta'];

            //Buscamos en la serie de respuestas si la respuesta que ha introducido tiene un consejo medico
            $serieRespuesta = $serieRespuestaRepo->find($rp['serie_respuesta_id']);
            $respuesta = $respuestaRepo->findBy(array('serieRespuesta' => $serieRespuesta));

            foreach ($respuesta as $r) {
                if (strtolower($r->getDescripcion()) === strtolower($respuestaText)) {
                    if (!is_null($r->getConsejoMedico())) {
                        $consejoMedicoDescripcion = $r->getConsejoMedico()->getDescripcion();

                        array_push($arrayConsejosMedicos, $consejoMedicoDescripcion);
                    }
                }
            }
        }
        $consejos = array_unique($arrayConsejosMedicos);

        //Buscamos las plantillas para imprimir el cuestionario
        $carpetaRevisiones = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(13);
        $plantillasRevisiones = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaRevisiones, 'anulado' => false));

        //Buscamos las plantillas para imprimir la certificacion
        $carpetaAptitudes = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(14);
        $plantillasAptitudes = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaAptitudes, 'anulado' => false));

        //Buscamos las plantillas para imprimir el cuestionario
        $carpetaRevisionMedica = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(17);
        $plantillasRevisionesMedicas = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaRevisionMedica, 'anulado' => false));

        //Buscamos las plantillas para imprimir las restricciones
        $carpetaRestriccion = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(20);
        $plantillasRestricciones = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaRestriccion, 'anulado' => false));

        $electrocardiogramaImg = $revision->getElectrocardiograma();

        //Buscamos los documentos que tenga adjuntos la revision
        $documentosAdjuntos = $this->getDoctrine()->getRepository('App\Entity\RevisionDocumentoAdjunto')->findBy(array('revision' => $revision, 'anulado' => false));

        //Si esta FACTURADA no pueden hacer nada
        $disabled = false;
        if (!is_null($revision->getEstado())) {
            if ($revision->getEstado()->getId() == 4 && ($rolId != 1 && $rolId != 5)) {
                $disabled = true;
            }
        }
        $form = $this->createForm(RevisionType::class, $revision, array('disabled' => $disabled, 'trabajadorObj' => $trabajadorRevision, 'empresaObj' => $empresa, 'empresaId' => $empresaId, 'puestoTrabajoObj' => $puestoTrabajoRevision, 'listTrabajadores' => $listTrabajadores, 'puestoTrabajoId' => $puestoTrabajoRevision->getId(), 'protocolos' => $protocolosSelect, 'trabajadorId' => $arrayTrabajadorId, 'citacionObj' => $revision->getCitacion(), 'puestoTrabajoActualizadoSn' => $puestoTrabajoActualizadoSn, 'disabledProtocolosSn' => $disableProtocolosSn));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $revision = $form->getData();
            $em = $this->getDoctrine()->getManager();

            //Obtenemos el trabajador
            $trabajadorFormId = $form["trabajador"]->getViewData();
            $trabajador = $em->getRepository('App\Entity\Trabajador')->find($trabajadorFormId);
            $revision->setTrabajador($trabajador);

            if (!is_null($revision->getTelefono())) {
                $telefono = $revision->getTelefono();
                if (is_null($trabajador->getTelefono1()) || $trabajador->getTelefono1() == "") {
                    $trabajador->setTelefono1($telefono);
                    $em->persist($trabajador);
                }
            }
            $dni = $form["dni"]->getViewData();
            if (!is_null($dni) && $dni != "") {
                if ($dni != $trabajador->getDni()) {
                    $trabajador->setDni($dni);
                    $em->persist($trabajador);
                }
            }
            $fechaNacimiento = $form["fechaNacimiento"]->getData();
            if (!is_null($fechaNacimiento) && $fechaNacimiento != "") {
                if ($fechaNacimiento != $trabajador->getFechaNacimiento()) {
                    $trabajador->setFechaNacimiento($fechaNacimiento);
                    $em->persist($trabajador);
                }
            }
            //Obtenemos el puesto de trabajo
            $puestoTrabajoFormId = $form["puestoTrabajo"]->getViewData();
            $puestoTrabajo = $em->getRepository('App\Entity\PuestoTrabajoCentro')->find($puestoTrabajoFormId);
            $revision->setPuestoTrabajo($puestoTrabajo);

            //            $actualizado = $form["actualizado"]->getData();
            //
            //            $puestoTrabajo->setActualizado($actualizado);
            //            $em->persist($puestoTrabajo);
            //            $em->flush();

            //Obtenemos la cita
            $citacionFormId = $form["citacion"]->getViewData();
            if (!is_null($citacionFormId) && $citacionFormId != "") {
                $citacion = $em->getRepository('App\Entity\Citacion')->find($citacionFormId);
                $revision->setCitacion($citacion);
            }
            //Si ha informado el electrocardiograma lo guardamos
            $electrocardiograma = $form->get('ficheroElectrocardiograma')->getData();
            if (!is_null($electrocardiograma)) {

                //Obtenemos el nombre y la extension
                $filename =  $electrocardiograma->getClientOriginalName();

                move_uploaded_file($electrocardiograma, "upload/media/electrocardiograma/$filename");
                $path_info = pathinfo("upload/media/electrocardiograma/$filename");
                $extension = $path_info['extension'];

                if (strtolower($extension) != 'jpg' && strtolower($extension) != 'png' && strtolower($extension) != 'jpeg') {
                    $traduccion = $translator->trans('TRANS_AVISO_ELECTRO_IMG', array(), 'revision');
                    $this->addFlash('danger',  $traduccion);

                    $em->persist($revision);
                    $em->flush();
                    return $this->redirectToRoute('medico_revision_update', array('id' => $revision->getId()));
                }
                $revisionId = $revision->getId();
                $newName = $revisionId . '.' . $extension;

                //Renombramos el logo
                rename("upload/media/electrocardiograma/$filename", "upload/media/electrocardiograma/$newName");

                $revision->setElectrocardiograma($newName);
                $em->persist($revision);
                $em->flush();
            }
            if (is_null($revision->getMedico())) {
                if (!is_null($empresa)) {
                    $vigilanciaSalud = $empresa->getVigilanciaSalud();
                    if (!is_null($vigilanciaSalud)) {
                        $medico = $vigilanciaSalud->getMedico();
                        if (!is_null($medico)) {
                            $revision->setMedico($medico);
                        }
                    }
                }
            }
            //Comprobamos si el estado es FIRMADA para informar la fecha de firma
            if (!is_null($revision->getEstado())) {
                if ($revision->getEstado()->getId() == 3) {
                    if (is_null($revision->getFechaFirma())) {
                        $revision->setFechaFirma(new \DateTime());
                        $revision->setUsuarioFirma($username);
                    }
                } else {
                    if ($revision->getEstado()->getId() < 3) {
                        $revision->setFechaFirma(null);
                        $revision->setUsuarioFirma(null);
                    }
                }
            } else {
                $revision->setFechaFirma(null);
                $revision->setUsuarioFirma(null);
            }
            $em->persist($revision);
            $em->flush();

            //Eliminamos los protocolos existentes
            //            foreach ($protocolos as $ptDelete) {
            //                $em->remove($ptDelete);
            //                $em->flush();
            //            }

            //Los volvemos a generar
            //            $protocolo_checked = $form["protocolo"]->getData();
            //            if (!is_null($protocolo_checked)) {
            //                foreach ($protocolo_checked as $pt) {
            //                    $protocoloObj = $protocoloRepo->find($pt);
            //                    $ptNew = new PuestoTrabajoProtocolo();
            //                    $ptNew->setEmpresa($empresa);
            //                    $ptNew->setProtocolo($protocoloObj);
            //                    $ptNew->setPuestoTrabajo($puestoTrabajo);
            //                    $ptNew->setAnulado(false);
            //                    $em->persist($ptNew);
            //                    $em->flush();
            //                }
            //            }
            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('medico_revision_update', array('id' => $revision->getId()));
        }
        return $this->render('revision/edit.html.twig', array('rolId' => $rolId, 'electrocardiograma' => $electrocardiogramaImg, 'form' => $form->createView(), 'protocolos' => $protocolos, 'cuestionariosRellenar' => $cuestionariosRellenar, 'resumen' => $resumen, 'consejos' => $consejos, 'listPlantillasRevisiones' => $plantillasRevisiones, 'listPlantillasAptitudes' => $plantillasAptitudes, 'puestoTrabajo' => $puestoTrabajoRevision, 'listPlantillasRevisionesMedicas' => $plantillasRevisionesMedicas, 'numeroPeticion' => $numeroPeticion, 'revisionId' => $revision->getId(), 'urlDocumentoAnalitica' => $urlDocumento, 'listPlantillasRestricciones' => $plantillasRestricciones, 'documentosAdjuntos' => $documentosAdjuntos));
    }

    public function deleteRevision(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getDeleteRevisionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $revision = $em->getRepository('App\Entity\Revision')->find($id);
        $revision->setAnulado(true);
        $em->persist($revision);
        $em->flush();

        $session->set('revisionRiesgoCausaId', null);

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        return $this->redirectToRoute('medico_revision_show');
    }

    public function buscaCuestionarios(Request $request)
    {
        $protocoloId = $_REQUEST['protocoloId'];
        $query = "select b.id, b.descripcion from protocolo_cuestionario a
        inner join cuestionario b on a.cuestionario_id = b.id
        where a.anulado = false 
        and a.protocolo_id = $protocoloId
        order by b.descripcion asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $cuestionarios = $stmt->fetchAll();
        return new JsonResponse(json_encode($cuestionarios));
    }

    public function buscaPreguntas(Request $request)
    {
        $cuestionarioId = $_REQUEST['cuestionarioId'];
        $query = "select a.orden, b.descripcion from cuestionario_pregunta a
        inner join pregunta b on a.pregunta_id = b.id
        where a.anulado = false
        and b.anulado = false
        and a.cuestionario_id = $cuestionarioId
        order by a.orden asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $preguntas = $stmt->fetchAll();

        return new JsonResponse(json_encode($preguntas));
    }

    public function buscaPuestoTrabajoTrabajador(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $trabajadorId = $_REQUEST['trabajadorId'];
        $query = "select b.id, concat(b.descripcion, ' ', '(',c.empresa,')') as descripcion from puesto_trabajo_trabajador a
        inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
        inner join empresa c on b.empresa_id = c.id
        where a.anulado = false
        and b.anulado = false 
        and a.trabajador_id = $trabajadorId
        and a.fecha_baja is null
        order by b.descripcion asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $puestoTrabajo = $stmt->fetchAll();

        $trabajador = $em->getRepository('App\Entity\Trabajador')->find($trabajadorId);
        $telefono = $trabajador->getTelefono1();
        $dni = $trabajador->getDni();
        $fechaNacimiento = $trabajador->getFechaNacimiento();

        $fechaNacimientoString = "";
        if (!is_null($fechaNacimiento)) {
            $fechaNacimientoString = $fechaNacimiento->format('Y-m-d');
        }
        $data = array(
            'puestos' => json_encode($puestoTrabajo),
            'telefono' => $telefono,
            'dni' => $dni,
            'dtnacimiento' => $fechaNacimientoString
        );
        return new JsonResponse($data);
    }

    public function buscaCitacionesTrabajador(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $trabajadorId = $_REQUEST['trabajadorId'];
        $query = "select a.id, concat(to_char(a.fechainicio, 'DD/MM/YYYY'), ' - ', b.descripcion) as descripcion from citacion a
        inner join agenda b on a.agenda_id = b.id
        where a.anulado = false
        and a.trabajador_id = $trabajadorId
        order by a.fechainicio desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $citas = $stmt->fetchAll();

        return new JsonResponse(json_encode($citas));
    }

    public function buscaPreguntasRespuestas(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $cuestionarioId = $_REQUEST['cuestionarioId'];
        $revisionId = $_REQUEST['revisionId'];

        $query = "select a.orden, b.descripcion, b.id as pregunta, a.id as cuestionariopregunta, b.valor_por_defecto from cuestionario_pregunta a
        inner join pregunta b on a.pregunta_id = b.id
        where a.anulado = false
        and b.anulado = false
        and a.cuestionario_id = $cuestionarioId
        order by a.orden asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $preguntasRespuestas = $stmt->fetchAll();

        $arrayPreguntas = array();
        foreach ($preguntasRespuestas as $pr) {
            $valorPorDefecto = $pr['valor_por_defecto'];
            $arrayPregunta['id'] = $pr['pregunta'];
            $arrayPregunta['orden'] = $pr['orden'];
            $arrayPregunta['descripcion'] = $pr['descripcion'];

            $preguntaId = $arrayPregunta['id'];
            $cuestionarioPreguntaId = $pr['cuestionariopregunta'];

            //Buscamos si ya tiene una respuesta informada
            $query = "select a.id, a.respuesta from revision_respuesta a
            inner join pregunta b on a.pregunta_id = b.id
            inner join cuestionario_pregunta c on a.pregunta_id = c.pregunta_id
            where a.revision_id = $revisionId
            and a.cuestionario_id = $cuestionarioId
            and c.cuestionario_id = $cuestionarioId
            and a.pregunta_id = $preguntaId
            order by c.orden asc";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $revisionRespuestaResult = $stmt->fetchAll();

            $revisionRespuesta = null;
            $revisionRespuestaId = null;
            $respuestaForm = "";
            if (count($revisionRespuestaResult) > 0) {
                $revisionRespuestaId = $revisionRespuestaResult[0]['id'];
                $respuestaForm = $revisionRespuestaResult[0]['respuesta'];
                $revisionRespuesta = $em->getRepository('App\Entity\RevisionRespuesta')->find($revisionRespuestaId);
            }
            //Si no tiene respuesta buscamos la de por defecto
            if ($respuestaForm == "") {
                $respuestaForm = $valorPorDefecto;
            }
            $cuestionarioPregunta = $em->getRepository('App\Entity\CuestionarioPregunta')->find($cuestionarioPreguntaId);

            //Buscamos el tipo de pregunta para mostrar las posibles respuestas
            $pregunta = $em->getRepository('App\Entity\Pregunta')->find($arrayPregunta['id']);

            $respuestaInput = "";
            if (!is_null($pregunta->getTipoRespuesta())) {
                //Buscamos el tipo de respuesta
                switch ($pregunta->getTipoRespuesta()->getId()) {
                        //TIPO TEXTO - TIPO NUMERICO - TIPO NUMERICO + DECIMAL
                    case 0:
                    case 1:
                    case 2:
                    case 7:
                        $respuestaInput = "<input type='text' name='$revisionRespuestaId' id='$preguntaId' class='form-control' value='$respuestaForm' />";
                        break;
                        //TIPO SI/NO
                    case 3:
                        //Comprobamos la que haya marcado
                        if (strtolower($respuestaForm) === "si") {
                            $respuestaInput .= "<label class='radio-inline'><input type='radio' name='$preguntaId' id='$preguntaId' value='Si' checked> Si</label> ";
                        } else {
                            $respuestaInput .= "<label class='radio-inline'><input type='radio' name='$preguntaId' id='$preguntaId' value='Si'> Si</label> ";
                        }
                        $respuestaInput .= " ";
                        if (strtolower($respuestaForm) === "no") {
                            $respuestaInput .= "<label class='radio-inline'><input type='radio' name='$preguntaId' id='$preguntaId' value='No' checked> No</label> ";
                        } else {
                            $respuestaInput .= "<label class='radio-inline'><input type='radio' name='$preguntaId' id='$preguntaId' value='No'> No</label> ";
                        }
                        break;
                        //TIPO FECHA
                    case 4:
                        $respuestaInput = "<input type='date' name='$revisionRespuestaId' id='$preguntaId' class='form-control'>";
                        break;
                        //TIPO SERIE CAMPO
                    case 5:
                        //Comprobamos que la serie no sea nula
                        if (!is_null($pregunta->getSerieRespuesta())) {
                            $respuestasSerie = $em->getRepository('App\Entity\Respuesta')->findBy(array('serieRespuesta' => $pregunta->getSerieRespuesta(), 'anulado' => false), array('sub' => 'ASC'));

                            //Comprobamos ei es una unica respuesta o es multiple
                            if (!is_null($pregunta->getSerieRespuesta()->getIndicador())) {
                                $indicadorId = $pregunta->getSerieRespuesta()->getIndicador()->getId();

                                switch ($indicadorId) {
                                        //MULTIRESPUESTA
                                    case 0:
                                        foreach ($respuestasSerie as $rs) {
                                            $checked = "";
                                            $respuestaSerieId = $rs->getId();
                                            $respuestaSerieDescripcion = $rs->getDescripcion();

                                            if ($respuestaForm != "" && str_contains($respuestaForm, ';;')) {
                                                $arrayExplode = explode(';;', $respuestaForm);
                                            } else {
                                                $arrayExplode = explode(';', $respuestaForm);
                                            }
                                            foreach ($arrayExplode as $ae) {
                                                //Comprobamos la que haya marcado
                                                if (strtolower($respuestaSerieDescripcion) === strtolower(str_replace(';', '', $ae))) {
                                                    $checked = "checked";
                                                    break;
                                                }
                                            }
                                            $respuestaInput .= "<input type='checkbox' name='$revisionRespuestaId' class='$preguntaId' id='$respuestaSerieId' $checked data-value='$respuestaSerieDescripcion' /> $respuestaSerieDescripcion <br/>";
                                        }
                                        break;
                                        //UNICA RESPUESTA
                                    case 1:
                                        $respuestaInput = "<select id='$preguntaId' name='$revisionRespuestaId' class='select-search form-control'>";
                                        $respuestaInput .= "<option value=''></option>";
                                        foreach ($respuestasSerie as $rs) {
                                            $selected = "";
                                            $respuestaSerieId = $rs->getId();
                                            $respuestaSerieDescripcion = $rs->getDescripcion();

                                            //Comprobamos la que haya marcado
                                            if (strtolower($respuestaSerieDescripcion) === strtolower($respuestaForm)) {
                                                $selected = "selected";
                                            }
                                            $respuestaInput .= "<option value='$respuestaSerieId' $selected>$respuestaSerieDescripcion</option>";
                                        }
                                        $respuestaInput .= "</select>";
                                        break;
                                }
                            }
                        }
                        break;
                        //TIPO SUB PREGUNTA
                    case 6:
                        $query = "select * from sub_pregunta where pregunta_id = $preguntaId and anulado = false order by orden asc";
                        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                        $stmt->execute();
                        $subPregunta = $stmt->fetchAll();
                        $countSubPreguntas = count($subPregunta);
                        if ($countSubPreguntas > 0) {
                            for ($i = 1; $i <= $countSubPreguntas; $i++) {
                                $subPreguntaId = $subPregunta[$i - 1]['id'];
                                $orden = $subPregunta[$i - 1]['orden'];
                                $descripcion = $subPregunta[$i - 1]['descripcion'];

                                $inputId = $preguntaId . '_' . $orden . '_' . $cuestionarioPreguntaId;

                                if ($preguntaId == 86) {
                                    $ordenDescripcion = $descripcion;
                                } else {
                                    $ordenDescripcion = $pr['orden'] . '.' . $orden . ' - ' . $descripcion;
                                }
                                //Buscamos los valores de la sub respuesta
                                $revisionSubRespuesta = null;
                                if (!is_null($revisionRespuesta)) {
                                    $query = "select respuesta, orden from revision_sub_respuesta where revision_respuesta_id = $revisionRespuestaId and orden = '$orden' and cuestionario_pregunta_id = $cuestionarioPreguntaId order by id asc";
                                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                                    $stmt->execute();
                                    $revisionSubRespuesta = $stmt->fetchAll();
                                }
                                $value = "";
                                if (!is_null($revisionSubRespuesta)) {
                                    if (isset($revisionSubRespuesta[0]['respuesta'])) {
                                        $value = $revisionSubRespuesta[0]['respuesta'];
                                    }
                                }
                                $respuestaInput .= "<label>$ordenDescripcion</label> <input type='text' name='$revisionRespuestaId' id='$inputId' class='form-control' value='$value' />";
                            }
                        }
                        break;
                        //TIPO FORMULA
                        /*case 7:
                        if(is_null($pregunta->getFormula())){
                            $formulaVariable = $em->getRepository('App\Entity\FormulaVariable')->findBy(array('formula' => $pregunta->getFormula(), 'anulado' => false), array('descripcion' => 'ASC'));
                            foreach ($formulaVariable as $fv){
                                $formulaVariableDescripcion = $fv->getDescripcion();
                                $respuestaInput .= "<input type='text' name='$revisionRespuestaId' id='$preguntaId' placeholder='$formulaVariableDescripcion' class='form-control' /><br/>";
                            }
                        }
                        break;*/
                }
                $arrayPregunta['respuesta'] = $respuestaInput;
            }
            array_push($arrayPreguntas, $arrayPregunta);
        }
        return new JsonResponse(json_encode($arrayPreguntas));
    }

    public function guardarRespuesta(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $respuestas = $_REQUEST['respuestas'];
        $revisionId = $_REQUEST['revisionId'];
        $cuestionarioId = $_REQUEST['cuestionarioId'];

        //Buscamos la revision y las preguntas de los cuestionarios
        $cuestionario = $em->getRepository('App\Entity\Cuestionario')->find($cuestionarioId);
        $revision = $em->getRepository('App\Entity\Revision')->find($revisionId);

        //Recorremos las respuestas y actualizamos el valor
        foreach ($respuestas as $r) {
            //Si contiene el caracter _ quiere decir que es sub pregunta
            if (str_contains($r['id'], '_')) {
                $arrayId = explode("_", $r['id']);
                $id = $arrayId[0];
                $orden = $arrayId[1];
                $cuestionarioPreguntaId = $arrayId[2];

                $pregunta = $em->getRepository('App\Entity\Pregunta')->find($id);
                $revisionPreguntas = $em->getRepository('App\Entity\RevisionRespuesta')->findOneBy(array('revision' => $revision, 'pregunta' => $pregunta, 'cuestionario' => $cuestionario));

                if (is_null($revisionPreguntas)) {
                    $revisionPreguntas = new RevisionRespuesta();
                    $revisionPreguntas->setPregunta($pregunta);
                    $revisionPreguntas->setCuestionario($cuestionario);
                    $revisionPreguntas->setRevision($revision);
                    $em->persist($revisionPreguntas);
                }
                $respuestaForm = $r['val'];
                if (str_contains($r['val'], ';')) {
                    $respuestaForm = implode(';', array_unique(explode(';', $r['val'])));
                    $respuestaForm = $respuestaForm . ";";
                }
                $cuestionarioPregunta = $em->getRepository('App\Entity\CuestionarioPregunta')->find($cuestionarioPreguntaId);
                $revisionSubRespuesta = $em->getRepository('App\Entity\RevisionSubRespuesta')->findOneBy(array('revisionRespuesta' => $revisionPreguntas, 'cuestionarioPregunta' => $cuestionarioPregunta, 'orden' => $orden));

                if (!is_null($revisionSubRespuesta)) {
                    $revisionSubRespuesta->setRespuesta($respuestaForm);
                    $em->persist($revisionSubRespuesta);
                    $em->flush();
                } else {
                    $revisionSubRespuesta = new RevisionSubRespuesta();
                    $revisionSubRespuesta->setCuestionarioPregunta($cuestionarioPregunta);
                    $revisionSubRespuesta->setRevisionRespuesta($revisionPreguntas);
                    $revisionSubRespuesta->setRespuesta($respuestaForm);
                    $revisionSubRespuesta->setOrden($orden);
                    $em->persist($revisionSubRespuesta);
                    $em->flush();
                }
            } else {
                $pregunta = $em->getRepository('App\Entity\Pregunta')->find($r['id']);
                $revisionPreguntas = $em->getRepository('App\Entity\RevisionRespuesta')->findOneBy(array('revision' => $revision, 'pregunta' => $pregunta, 'cuestionario' => $cuestionario));

                $respuestaForm = $r['val'];
                if (str_contains($r['val'], ';')) {
                    $respuestaForm = implode(';', array_unique(explode(';', $r['val'])));
                    $respuestaForm = $respuestaForm . ";";
                }
                if (is_null($revisionPreguntas)) {
                    $revisionPreguntas = new RevisionRespuesta();
                    $revisionPreguntas->setPregunta($pregunta);
                    $revisionPreguntas->setRespuesta($respuestaForm);
                    $revisionPreguntas->setCuestionario($cuestionario);
                    $revisionPreguntas->setRevision($revision);
                } else {
                    $revisionPreguntas->setRespuesta($respuestaForm);
                }
                $em->persist($revisionPreguntas);
                $em->flush();
            }
        }
        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function generarResumen($revisionId)
    {
        $arrayResumen = array();

        $query = "select distinct d.descripcion as protocolo, b.descripcion as cuestionario, b.id from revision_respuesta a
            inner join cuestionario b on a.cuestionario_id = b.id 
            inner join protocolo_cuestionario c on b.id = c.cuestionario_id
            inner join protocolo d on c.protocolo_id = d.id
            where a.revision_id = $revisionId
            and b.tipo_cuestionario_id = 1
            order by d.descripcion, b.descripcion";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $resumen = $stmt->fetchAll();

        foreach ($resumen as $r) {
            $item = array();
            $item['protocolo'] = $r['protocolo'];
            $item['cuestionario'] = $r['cuestionario'];

            $cuestionarioId = $r['id'];
            $noInformadas = 0;
            $informadas = 0;

            //Calculamos las respuestas informdas y no informadas que no sean subpreguntas
            $query = "select a.respuesta from revision_respuesta a
            inner join pregunta b on a.pregunta_id = b.id
            where a.revision_id = $revisionId 
            and a.cuestionario_id = $cuestionarioId
            and b.tipo_respuesta_id != 6";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();

            $respuestas = $stmt->fetchAll();
            foreach ($respuestas as $re) {
                if ($re['respuesta'] != "" && !is_null($re['respuesta'])) {
                    $informadas++;
                } else {
                    $noInformadas++;
                }
            }
            //Calculamos las respuestas informdas y no informadas que sean subpreguntas
            $query = "select c.respuesta from revision_respuesta a
            inner join pregunta b on a.pregunta_id = b.id
            inner join revision_sub_respuesta c on a.id = c.revision_respuesta_id 
            where a.revision_id = $revisionId 
            and a.cuestionario_id = $cuestionarioId
            and b.tipo_respuesta_id = 6";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();

            $respuestas = $stmt->fetchAll();
            foreach ($respuestas as $re) {
                if ($re['respuesta'] != "" && !is_null($re['respuesta'])) {
                    $informadas++;
                } else {
                    $noInformadas++;
                }
            }
            $item['informadas'] = $informadas;
            $item['noInformadas'] = $noInformadas;
            $item['total'] = $informadas + $noInformadas;

            array_push($arrayResumen, $item);
        }
        return $arrayResumen;
    }

    public function sendCuestionarioRevision(Request $request, \Swift_Mailer $mailer, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getSendCuestionarioRevisionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $user = $this->getUser();
        $usuario = $repository->findForSecurityUser($user);
        $mail = $usuario->getMail();
        $emailUser = $usuario->getEmail();
        $passwordMail = $usuario->getPasswordMail();
        $hostMail = $usuario->getHostMail();
        $puertoMail = $usuario->getPuertoMail();
        $encriptacionMail = $usuario->getEncriptacionMail();
        $userMail = $usuario->getUserMail();

        //Buscamos la configuración de la gestión documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $rutaCompleta = $gdocConfig->getRuta();
        $carpetaTemporal = $gdocConfig->getCarpetaTemporal();

        $correo = "";
        if (isset($_GET['id'])) {
            $revisionId = $_GET['id'];
            $revision = $em->getRepository('App\Entity\Revision')->find($revisionId);
            $correo = $revision->getTrabajador()->getMail();
        }
        $form = $this->createForm(EnviarCuestionarioRevisionType::class, null, array('correo' => $correo));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //Recogemos lo datos
            $para = $form["para"]->getData();
            $cc = $form["cc"]->getData();
            $cco = $form["cco"]->getData();
            $asunto = $form["asunto"]->getData();
            $mensaje = $form["mensaje"]->getData();

            if (is_null($mail) || is_null($passwordMail) || is_null($puertoMail) || is_null($hostMail) || is_null($encriptacionMail) || is_null($userMail)) {
                $traduccion = $translator->trans('TRANS_AVISO_NO_CONFIGURACION_CORREO');
                $this->addFlash('danger', $traduccion);
                return $this->redirectToRoute('medico_revision_update', array('id' => $revisionId));
            }
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

            $file = $form->get('fichero')->getData();
            $name = $file->getClientOriginalName();
            $urlNueva = $rutaCompleta . $carpetaTemporal . '/' . $name;
            move_uploaded_file($file, $urlNueva);

            //Lo adjuntamos al correo
            $message->attach(\Swift_Attachment::fromPath($urlNueva));

            //Enviamos el correo
            $mailer->send($message);

            //Insertamos el correo en el log
            $this->insertLogMail($em, $usuario, $asunto, $para, $message->getBody(), "Envío cuestionario revisión");

            //unlink($rutaCompleta.$carpetaTemporal.'/'.$name);

            $traduccion = $translator->trans('TRANS_SEND_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('medico_revision_enviar_cuestionario');
        }
        return $this->render('revision/send_cuestionario.html.twig', array('form' => $form->createView()));
    }

    public function buscaTrabajadorEmpresa(Request $request)
    {
        $empresaId = $_REQUEST['empresaId'];

        $query = "select distinct c.id, concat(trim(c.nombre), '(', c.dni, ')') as descripcion from empresa a 
        inner join trabajador_empresa b on a.id = b.empresa_id
        inner join trabajador c on b.trabajador_id = c.id
        inner join trabajador_alta_baja d on c.id = d.trabajador_id 
        where a.anulado = false
        and b.anulado = false
        and c.anulado = false
        and a.id = $empresaId
        and d.empresa_id = $empresaId
        and d.activo = true
        and d.fecha_baja is null
        and d.anulado = false
        order by concat(trim(c.nombre), '(', c.dni, ')') asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $trabajadorEmpresa = $stmt->fetchAll();

        return new JsonResponse(json_encode($trabajadorEmpresa));
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

    public function showRevisionePendientes(Request $request)
    {
        $session = $request->getSession();
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $revisiones = $this->buscaRevisionesPendientes("", "", "", "");

        $session->set('revisioestadosnRiesgoCausaId', null);

        //Buscamos las plantillas de la carpeta resumen revision
        $carpetaResumenRevision = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(16);
        $plantillasResumen = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaResumenRevision, 'anulado' => false));

        //Buscamos las plantillas de la carpeta aptitud
        $carpetaAptitud = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(14);
        $plantillasAptitud = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaAptitud, 'anulado' => false));

        //Buscamos los estados
        $estados = $this->getDoctrine()->getRepository('App\Entity\EstadoRevision')->findBy(array('anulado' => false));

        //Buscamos los medicos
        $medicos = $this->getDoctrine()->getRepository('App\Entity\Doctor')->findBy(array('anulado' => false));

        $object = array("json" => $username, "entidad" => "revisiones pendientes", "id" => $id);
        $em = $this->getDoctrine()->getManager();
        $logger = new Logger();
        $logger->addLog($em, "logout", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('revision/show_pendientes.html.twig', array('revisiones' => $revisiones, 'listPlantillasAptitud' => $plantillasAptitud, 'listPlantillasResumen' => $plantillasResumen, 'estados' => $estados, 'medicos' => $medicos));
    }

    public function filtraRevisionesPendientes(Request $request)
    {
        $dtini = $_REQUEST['ini'];
        $dtfin = $_REQUEST['fin'];
        $estado = $_REQUEST['estado'];
        $medico = $_REQUEST['medico'];

        $revisiones = $this->buscaRevisionesPendientes($dtini, $dtfin, $estado, $medico);

        return new JsonResponse(json_encode($revisiones));
    }

    function buscaRevisionesPendientes($dtini, $dtfin, $estado, $medico)
    {
        $query = "select a.id, to_char(a.fecha, 'DD/MM/YYYY') as fecha, to_char(a.fecha, 'YYYYMMDDHHmm') as fechatimestamp, b.nombre as trabajador, b.dni, c.descripcion as puesto, d.empresa, e.id as estadoId, e.descripcion as estado, a.electrocardiograma, a.analitica, f.descripcion as doctor, a.apto_id, a.fichero_resumen_id, a.fichero_id from revision a
            inner join trabajador b on a.trabajador_id = b.id
            inner join puesto_trabajo_centro c on a.puesto_trabajo_id = c.id
            inner join empresa d on a.empresa_id = d.id
            left join estado_revision e on a.estado_id = e.id
            left join doctor f on a.medico_id = f.id
            where a.anulado = false
            and b.anulado = false
            and c.anulado = false
            and a.estado_id is not null
            and a.estado_id != 4 ";

        if ($dtini != "") {
            $query .= " and a.fecha >= '$dtini 00:00:00'";
        }
        if ($dtfin != "") {
            $query .= " and a.fecha <= '$dtfin 00:00:00'";
        }
        if ($estado != "") {
            $query .= " and a.estado_id = $estado";
        }
        if ($medico != "") {
            $query .= " and a.medico_id = $medico";
        }
        $query .= " order by a.fecha desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $revisiones = $stmt->fetchAll();

        return $revisiones;
    }

    public function deleteFechaAnalitica(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $revisionId = $_REQUEST['id'];
        $revision = $this->getDoctrine()->getRepository('App\Entity\Revision')->find($revisionId);
        $revision->setFechaRecuperacionResultado(null);
        $em->persist($revision);
        $em->flush();

        //Buscamos el fichero y lo movemos a la carpeta de no procesados
        $gdocConfig = $this->getDoctrine()->getRepository('App\Entity\GdocConfig')->find(1);
        $carpetaResultadosAnaliticasTmp = $gdocConfig->getCarpetaResultadoAnaliticaTmp();
        $rutaGdoc = $gdocConfig->getRuta();
        $rutaTmp = $rutaGdoc . $carpetaResultadosAnaliticasTmp;

        $analiticasConfig = $this->getDoctrine()->getRepository('App\Entity\AnaliticasConfig')->find(1);
        $carpetaResultadosAnaliticas = $analiticasConfig->getCarpetaResultadoAnalitica();
        if (file_exists("upload/media/$carpetaResultadosAnaliticas/$revisionId")) {
            $files = scandir("upload/media/$carpetaResultadosAnaliticas/$revisionId", 1);

            foreach ($files as $file) {
                if ($file != "." && $files != "..") {
                    rename("upload/media/$carpetaResultadosAnaliticas/$revisionId/$file", $rutaTmp . '/' . $file);
                }
            }
        }
        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function deleteImagenElectrocardiograma(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $revisionId = $_REQUEST['id'];
        $revision = $this->getDoctrine()->getRepository('App\Entity\Revision')->find($revisionId);
        $filename = $revision->getElectrocardiograma();

        //Eliminamos la imagen
        if (file_exists("upload/media/electrocardiograma/$filename")) {
            unlink("upload/media/electrocardiograma/$filename");
        }
        $revision->setElectrocardiograma(null);
        $em->persist($revision);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function enviarAptitud(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEnviarAptitudRevisionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $user = $this->getUser();
        $usuario = $repository->findForSecurityUser($user);
        $mail = $usuario->getMail();
        $emailUser = $usuario->getEmail();
        $passwordMail = $usuario->getPasswordMail();
        $hostMail = $usuario->getHostMail();
        $puertoMail = $usuario->getPuertoMail();
        $encriptacionMail = $usuario->getEncriptacionMail();
        $userMail = $usuario->getUserMail();

        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $urlPortal = $gdocConfig->getRutaPortal();
        $passwordFichero = $gdocConfig->getPasswordFicherosEncriptados();

        $smsConfig = $this->getDoctrine()->getRepository('App\Entity\SmsConfig')->find(1);
        $centro = $smsConfig->getCentro();
        $remite = $smsConfig->getRemite();
        $mensaje = $smsConfig->getMensaje();

        $revisionesEnviarAptitudId = array();

        if (isset($_REQUEST['revisiones'])) {
            $revisionesEnviarAptitudId = explode(",", $_REQUEST['revisiones']);
        }
        if (isset($_REQUEST['id'])) {
            array_push($revisionesEnviarAptitudId, $_REQUEST['id']);
        }
        $telefonoNoValido = "";
        $dniTrabajadorNoValido = "";

        if (is_null($mail) || is_null($passwordMail) || is_null($puertoMail) || is_null($hostMail) || is_null($encriptacionMail) || is_null($userMail)) {
            $em->commit();
            $traduccion = $translator->trans('TRANS_AVISO_NO_CONFIGURACION_CORREO');
            $this->addFlash('danger', $traduccion);
            return $this->redirectToRoute('dashboard_admin');
        }
        $em->beginTransaction();

        try {
            foreach ($revisionesEnviarAptitudId as $r) {
                //Enviamos el correo a la empresa
                $transport = new \Swift_SmtpTransport($hostMail, $puertoMail, $encriptacionMail);
                $transport->setUsername($userMail);
                $transport->setPassword($passwordMail);
                $transport->setHost($hostMail);
                $transport->setAuthMode('login');

                $mailer = new \Swift_Mailer($transport);
                $revision = $this->getDoctrine()->getRepository('App\Entity\Revision')->find($r);

                $nombreTrabajador = $revision->getTrabajador()->getNombre();
                $dniTrabajador = $revision->getTrabajador()->getDni();
                $numero = $revision->getTelefono();

                if (is_null($numero) || $numero == "") {
                    $telefonoNoValido .= $nombreTrabajador . ", ";
                    continue;
                }
                if (! preg_match("/^[6,7]\d{8}$/", $numero)) {
                    $telefonoNoValido .= $nombreTrabajador . ", ";
                    continue;
                }
                if (is_null($dniTrabajador) || $dniTrabajador == "") {
                    $dniTrabajadorNoValido .= $nombreTrabajador . ", ";
                    continue;
                }
                //Generamos el token
                $token = $this->generarToken();
                $revision->setToken($token);
                $em->persist($revision);
                $em->flush();

                $url = $this->generateUrl('medico_revision_descargar_resumen_check', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

                $mensaje = str_replace('%enlace%', $url, $mensaje);

                //Enviamos el sms al trabajador
                $smsController = new SmsController();
                $return = $smsController->enviaSms($centro, $remite, $numero, $mensaje, 'VI', $r);

                if ($return == 1) {
                    $revision->setAptitudEnviada(true);
                    $revision->setFechaEnvio(new \DateTime());
                    $em->persist($revision);
                    $em->flush();
                }
                //Buscamos el email para el envío de aviso de aptitud
                $empresa = $revision->getEmpresa();
                $funcionCorreo = $em->getRepository('App\Entity\FuncionCorreo')->find(5);
                $empresaCorreo = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'funcion' => $funcionCorreo, 'anulado' => false));

                foreach ($empresaCorreo as $ec) {
                    //Enviamos el correo a la empresa
                    $transport = new \Swift_SmtpTransport($hostMail, $puertoMail, $encriptacionMail);
                    $transport->setUsername($userMail);
                    $transport->setPassword($passwordMail);
                    $transport->setHost($hostMail);
                    $transport->setAuthMode('login');
                    $mailer = new \Swift_Mailer($transport);

                    $para = trim($ec->getCorreo());

                    $message = new \Swift_Message();
                    $message->setSubject("Certificado de aptitud disponible");
                    $message->setFrom($mail);
                    $message->setTo($para);
                    $message->setReplyTo($emailUser);
                    $message->setBody(
                        $this->renderView(
                            // templates/emails/registration.html.twig
                            'emails/send_aviso_aptitud.html.twig',
                            ['email' => $para, 'trabajador' => $nombreTrabajador, 'url' => $urlPortal, 'passwordFichero' => $passwordFichero]
                        ),
                        'text/html'
                    );
                    $mailer->send($message);

                    //Insertamos el correo en el log
                    $this->insertLogMail($em, $usuario, "Certificado de aptitud disponible", $para, $message->getBody(), "Certificado de aptitud disponible");
                    $mailer->getTransport()->stop();
                }
                $mensaje = $smsConfig->getMensaje();
            }
            $em->commit();
        } catch (\Exception $e) {
            $em->rollBack();
            $traduccion = $translator->trans('TRANS_ENVIAR_CORREO_ERROR');
            $this->addFlash('danger', $traduccion);
            return $this->redirectToRoute('dashboard_admin');
        }
        if ($telefonoNoValido != "") {
            $traduccion = $translator->trans('TRANS_AVISO_NO_TELEFONO');
            $this->addFlash('danger', $traduccion . " (" . $telefonoNoValido . ")");
        }
        if ($dniTrabajadorNoValido != "") {
            $traduccion = $translator->trans('TRANS_AVISO_TRABAJADOR_NO_DNI');
            $this->addFlash('danger', $traduccion . " (" . $dniTrabajadorNoValido . ")");
        }
        $traduccion = $translator->trans('TRANS_UPDATE_OK');
        $this->addFlash('success', $traduccion);

        return $this->redirectToRoute('dashboard_admin');
    }

    public function marcarAptitudEnviada(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEnviarAptitudRevisionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $user = $this->getUser();
        $usuario = $repository->findForSecurityUser($user);

        $revisionesmarcarEnviadaId = array();

        if (isset($_REQUEST['revisiones'])) {
            $revisionesmarcarEnviadaId = explode(",", $_REQUEST['revisiones']);
        }
        if (isset($_REQUEST['id'])) {
            array_push($revisionesmarcarEnviadaId, $_REQUEST['id']);
        }
        $em->beginTransaction();

        try {
            foreach ($revisionesmarcarEnviadaId as $r) {
                $revision = $this->getDoctrine()->getRepository('App\Entity\Revision')->find($r);
                $revision->setAptitudEnviada(true);
                $revision->setFechaEnvio(new \DateTime());
                $em->persist($revision);
                $em->flush();
            }
        } catch (\Exception $e) {
            $em->rollBack();
            throw $e;
        }
        $em->commit();

        $traduccion = $translator->trans('TRANS_UPDATE_OK');
        $this->addFlash('success', $traduccion);

        return $this->redirectToRoute('dashboard_admin');
    }

    public function downResumenCheck(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();

        if (is_null($token)) {
            return $this->redirectToRoute('error_down_resumen_revision');
        }
        $revision = $em->getRepository('App\Entity\Revision')->findOneBy(array('token' => $token));

        if (is_null($revision->getFicheroResumen())) {
            return $this->redirectToRoute('error_down_resumen_revision');
        }
        $form = $this->createForm(CheckDownResumenType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $dni = $form["dni"]->getData();

            $dniTrabajador = $revision->getTrabajador()->getDni();
            if (trim($dni) !== trim($dniTrabajador)) {
                $this->addFlash('danger', 'La contraseña no es válida.');
                return $this->redirectToRoute('medico_revision_descargar_resumen_check', array('token' => $token));
            }
            return $this->redirectToRoute('medico_revision_descargar_resumen', array('token' => $token));
        }
        return $this->render('revision/check_down.html.twig', array('form' => $form->createView()));
    }

    public function downResumen(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();
        $hoy = new \DateTime();
        $hoyString = $hoy->format('YmdHis');

        if (is_null($token)) {
            return $this->redirectToRoute('error_down_resumen_revision');
        }
        $revision = $em->getRepository('App\Entity\Revision')->findOneBy(array('token' => $token));

        if (is_null($revision)) {
            return $this->redirectToRoute('error_down_resumen_revision');
        }
        if (is_null($revision->getFicheroResumen())) {
            return $this->redirectToRoute('error_down_resumen_revision');
        }
        $revisionId = $revision->getId();

        //Obtenemos el dni del trabajador
        $passwordUsuario = trim($revision->getTrabajador()->getDni());

        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $rutaCompleta = $gdocConfig->getRuta();
        $carpetaPlantillaGenerada = $gdocConfig->getCarpetaRevision();
        $carpetaPlantillaGeneradaAptitud = $gdocConfig->getCarpetaAptitud();
        $carpetaTemporal = $gdocConfig->getCarpetaTemporal();
        $carpetaDocumentosAdjuntosRevision = $gdocConfig->getCarpetaDocumentoAdjuntoRevision();

        $analiticasConfig = $this->getDoctrine()->getRepository('App\Entity\AnaliticasConfig')->find(1);
        $carpetaResultadosAnaliticas = $analiticasConfig->getCarpetaResultadoAnalitica();

        //Obtenemos el resumen de la revision
        $fileResumen = $revision->getFicheroResumen();
        $nombrePlantillaResumen = $fileResumen->getNombre();

        //Convertimos el resumen de la revision a pdf
        $fileResumenDocx = $rutaCompleta . $carpetaPlantillaGenerada . '/' . $nombrePlantillaResumen;
        $nombrePlantillaResumenPdf = str_replace('docx', 'pdf', $nombrePlantillaResumen);

        $fileResumenPdf = str_replace('docx', 'pdf', $fileResumenDocx);
        $outdir = $rutaCompleta . $carpetaPlantillaGenerada;

        $cmd = 'soffice --headless --convert-to pdf:writer_pdf_Export "' . $fileResumenDocx . '" --outdir "' . $outdir . '"';
        exec($cmd);

        //Encriptamos el resumen de la revision
        $fileResumenPdfEncriptado = $rutaCompleta . $carpetaTemporal . '/' . $nombrePlantillaResumenPdf;
        $passwordOwner = $token;

        $this->encriptarPdf($fileResumenPdfEncriptado, $fileResumenPdf, $passwordOwner, $passwordUsuario);

        //Buscamos el fichero de la aptitud
        $fileAptitud = $revision->getFichero();
        $nombrePlantillAptitud = $fileAptitud->getNombre();

        //Convertimos la aptitud de la revision a pdf
        $fileAptitudDocx = $rutaCompleta . $carpetaPlantillaGeneradaAptitud . '/' . $nombrePlantillAptitud;
        $nombrePlantillaAptitudPdf = str_replace('docx', 'pdf', $nombrePlantillAptitud);

        $fileAptitudPdf = str_replace('docx', 'pdf', $fileAptitudDocx);
        $outdirAptitud = $rutaCompleta . $carpetaPlantillaGeneradaAptitud;

        $cmd = 'soffice --headless --convert-to pdf:writer_pdf_Export "' . $fileAptitudDocx . '" --outdir "' . $outdirAptitud . '"';
        exec($cmd);

        //Encriptamos la aptitud de la revision
        $fileAptitudPdfEncriptado = $rutaCompleta . $carpetaTemporal . '/' . $nombrePlantillaAptitudPdf;
        $passwordOwner = $token;

        $this->encriptarPdf($fileAptitudPdfEncriptado, $fileAptitudPdf, $passwordOwner, $passwordUsuario);

        //Generamos el nombre del zip
        $nombreZip = $revision->getTrabajador()->getNombre() . '_' . $revision->getTrabajador()->getDni() . '_' . $hoyString . '.zip';

        //Buscamos si la revision tiene documentos adjuntos
        $documentosAdjuntos = $this->getDoctrine()->getRepository('App\Entity\RevisionDocumentoAdjunto')->findBy(array('revision' => $revision, 'anulado' => false, 'mostrar' => true));

        //Creamos el zip
        $zip = new ZipArchive();
        if ($zip->open('upload/media/ziprevision/' . $nombreZip, ZipArchive::CREATE) === TRUE) {

            //Añadimos el resumen de la revisión
            $zip->addFile($fileResumenPdfEncriptado, $nombrePlantillaResumenPdf);

            //Añadimos la aptitud de la revision
            $zip->addFile($fileAptitudPdfEncriptado, $nombrePlantillaAptitudPdf);

            if ($revision->getApto()->getId() == 2) {
                //Si tiene restriccion buscamos el fichero
                $ficheroRestriccion = $revision->getFicheroRestriccion();
                if (!is_null($ficheroRestriccion)) {
                    $nombrePlantillaRestriccion = $ficheroRestriccion->getNombre();

                    //Convertimos el word en pdf
                    $fileRestriccionDocx = $rutaCompleta . $carpetaPlantillaGenerada . '/' . $nombrePlantillaRestriccion;
                    $nombrePlantillaRestriccionPdf = str_replace('docx', 'pdf', $nombrePlantillaRestriccion);
                    $fileRestriccionPdf = str_replace('docx', 'pdf', $fileRestriccionDocx);

                    $cmd = 'soffice --headless --convert-to pdf:writer_pdf_Export "' . $fileRestriccionDocx . '" --outdir "' . $outdir . '"';
                    exec($cmd);

                    $zip->addFile($fileRestriccionPdf, $nombrePlantillaRestriccionPdf);
                }
            }
            //Comprobamos si tiene la analitica informada
            if ($revision->getAnalitica()) {
                //Buscamos la analitica
                $numeroPeticion = $revision->getNumeroPeticion();
                if (!is_null($numeroPeticion)) {
                    if (file_exists("upload/media/$carpetaResultadosAnaliticas/$revisionId/$numeroPeticion.pdf")) {
                        //Encriptamos el resultado de la analitica
                        $fileAnaliticaPdfEncriptado = $rutaCompleta . $carpetaTemporal . '/' . $numeroPeticion . '.pdf';
                        $fileAnaliticaPdf = "upload/media/$carpetaResultadosAnaliticas/$revisionId/$numeroPeticion.pdf";
                        $this->encriptarPdf($fileAnaliticaPdfEncriptado, $fileAnaliticaPdf, $passwordOwner, $passwordUsuario);

                        $zip->addFile($fileAnaliticaPdfEncriptado, "$numeroPeticion.pdf");
                    }
                }
            }
            //Buscamos si la revision tiene documentos adjuntos
            foreach ($documentosAdjuntos as $da) {
                $nombre = $da->getNombre();
                $nombreEncriptado = $revisionId . '_' . $nombre;

                //Encriptamos el documento adjunto
                $fileAdjuntoPdf = $rutaCompleta . $carpetaDocumentosAdjuntosRevision . '/' . $revisionId . '/' . $nombre;
                $fileAdjuntoPdfEncriptado = $rutaCompleta . $carpetaTemporal . '/' . $nombreEncriptado;
                $this->encriptarPdf($fileAdjuntoPdfEncriptado, $fileAdjuntoPdf, $passwordOwner, $passwordUsuario);

                $zip->addFile($fileAdjuntoPdfEncriptado, $nombre);
            }
            // All files are added, so close the zip file.
            $zip->close();
        }
        $response = new Response(file_get_contents('upload/media/ziprevision/' . $nombreZip));

        // Set the content disposition
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $nombreZip . '"');

        return $response;
    }

    function encriptarPdf($filePdfEncriptado, $filePdf, $passwordOwner, $passwordUsuario)
    {
        // Copiamos el PDF sin encriptar
        copy($filePdf, $filePdfEncriptado);
    }

    public function downAnalitica(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $revision = $em->getRepository('App\Entity\Revision')->find($id);

        $revisionId = $revision->getId();
        $numeroPeticion = $revision->getNumeroPeticion();
        $analiticasConfig = $this->getDoctrine()->getRepository('App\Entity\AnaliticasConfig')->find(1);
        $carpetaResultadosAnaliticas = $analiticasConfig->getCarpetaResultadoAnalitica();

        $ruta = "upload/media/$carpetaResultadosAnaliticas/$revisionId/$numeroPeticion.PDF";

        if (file_exists($ruta)) {
            $fileDown = file_get_contents($ruta, true);

            $response = new Response($fileDown);

            // Create the disposition of the file
            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                "$numeroPeticion.PDF"
            );
            // Set the content disposition
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set('Content-Disposition', $disposition);

            return $response;
        }
        return $this->redirectToRoute('medico_revision_show');
    }

    public function addDocumentoAdjunto(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $revisionId = $session->get('revisionId');
        $revision = $em->getRepository('App\Entity\Revision')->find($revisionId);

        $gdocConfig = $this->getDoctrine()->getRepository('App\Entity\GdocConfig')->find(1);
        $carpetaDocumentosAdjuntosRevision = $gdocConfig->getCarpetaDocumentoAdjuntoRevision();
        $rutaGdoc = $gdocConfig->getRuta();
        $rutaDocumentosAdjuntosRevision = $rutaGdoc . $carpetaDocumentosAdjuntosRevision;

        //Obtenemos los datos del archivo
        $filename = $_FILES['file']['name'];
        $filename = $this->eliminar_tildes($filename);

        if (!file_exists("$rutaDocumentosAdjuntosRevision/$revisionId")) {
            mkdir("$rutaDocumentosAdjuntosRevision/$revisionId");
        }

        move_uploaded_file($_FILES["file"]["tmp_name"], "$rutaDocumentosAdjuntosRevision/$revisionId/$filename");
        $path_info = pathinfo("$rutaDocumentosAdjuntosRevision/$revisionId/$filename");
        $extension = strtolower($path_info['extension']);

        $extensionesValidas = array("pdf");
        if (in_array($extension, $extensionesValidas)) {
            $revisionDocumentoAdjunto = new RevisionDocumentoAdjunto();
            $revisionDocumentoAdjunto->setRevision($revision);
            $revisionDocumentoAdjunto->setAnulado(false);
            $revisionDocumentoAdjunto->setMostrar(true);
            $revisionDocumentoAdjunto->setNombre($filename);
            $em->persist($revisionDocumentoAdjunto);
            $em->flush();
        }
        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function mostrarDocumentoAdjunto(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $revisionDocumentoAdjunto = $em->getRepository('App\Entity\RevisionDocumentoAdjunto')->find($id);
        $revisionDocumentoAdjunto->setMostrar(true);
        $em->persist($revisionDocumentoAdjunto);
        $em->flush();

        $revisionId = $session->get('revisionId');
        return $this->redirectToRoute('medico_revision_update', array('id' => $revisionId));
    }

    public function noMostrarDocumentoAdjunto(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $revisionDocumentoAdjunto = $em->getRepository('App\Entity\RevisionDocumentoAdjunto')->find($id);
        $revisionDocumentoAdjunto->setMostrar(false);
        $em->persist($revisionDocumentoAdjunto);
        $em->flush();

        $revisionId = $session->get('revisionId');

        return $this->redirectToRoute('medico_revision_update', array('id' => $revisionId));
    }

    public function deleteDocumentoAdjunto(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $documentoAdjuntoId = $_REQUEST['documentoAdjuntoId'];
        $revisionDocumentoAdjunto = $em->getRepository('App\Entity\RevisionDocumentoAdjunto')->find($documentoAdjuntoId);
        $revisionDocumentoAdjunto->setAnulado(true);
        $em->persist($revisionDocumentoAdjunto);
        $em->flush();

        $gdocConfig = $this->getDoctrine()->getRepository('App\Entity\GdocConfig')->find(1);
        $carpetaDocumentosAdjuntosRevision = $gdocConfig->getCarpetaDocumentoAdjuntoRevision();
        $rutaGdoc = $gdocConfig->getRuta();
        $rutaDocumentosAdjuntosRevision = $rutaGdoc . $carpetaDocumentosAdjuntosRevision;

        $filename = $revisionDocumentoAdjunto->getNombre();
        $revisionId = $revisionDocumentoAdjunto->getRevision()->getId();

        unlink("$rutaDocumentosAdjuntosRevision/$revisionId/$filename");

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function viewDocumentoAdjunto(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $revisionDocumentoAdjunto = $em->getRepository('App\Entity\RevisionDocumentoAdjunto')->find($id);
        $filename = $revisionDocumentoAdjunto->getNombre();
        $revisionId = $revisionDocumentoAdjunto->getRevision()->getId();

        $gdocConfig = $this->getDoctrine()->getRepository('App\Entity\GdocConfig')->find(1);
        $carpetaDocumentosAdjuntosRevision = $gdocConfig->getCarpetaDocumentoAdjuntoRevision();
        $rutaGdoc = $gdocConfig->getRuta();
        $rutaDocumentosAdjuntosRevision = $rutaGdoc . $carpetaDocumentosAdjuntosRevision . '/' . $revisionId . '/' . $filename;

        $fileDown = file_get_contents($rutaDocumentosAdjuntosRevision, true);

        $response = new Response($fileDown);

        // Create the disposition of the file
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $filename
        );
        // Set the content disposition
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-disposition:', $disposition);

        return $response;
    }

    function generarToken()
    {
        //Generamos la contraseña aleatoria
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 10; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function eliminar_tildes($cadena)
    {
        //Codificamos la cadena en formato utf8 en caso de que nos de errores
        //$cadena = utf8_encode($cadena);

        //Ahora reemplazamos las letras
        $cadena = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $cadena
        );
        $cadena = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $cadena
        );
        $cadena = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $cadena
        );
        $cadena = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $cadena
        );
        $cadena = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $cadena
        );
        $cadena = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç', '&', '·', '´', '`'),
            array('n', 'N', 'c', 'C', '', '', '', ''),
            $cadena
        );
        return $cadena;
    }
}
