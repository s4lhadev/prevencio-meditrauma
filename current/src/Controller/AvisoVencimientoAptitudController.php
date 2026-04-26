<?php

namespace App\Controller;

use App\Entity\LogEnvioMail;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Logger;
use Symfony\Contracts\Translation\TranslatorInterface;

class AvisoVencimientoAptitudController extends AbstractController
{
    public function show(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getAvisoVencimientoAptitud()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $object = array("json" => $username, "entidad" => "aviso vencimiento aptitud", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('avisovencimientoaptitud/show.html.twig', array());
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

        $dql = "SELECT d.id as id, a.fecha as fecha, b.nombre as trabajador, c.descripcion as puesto, d.empresa as empresa, IDENTITY(a.apto) as apto, 
        b.dni, CASE WHEN b.idRiesgos is not null then b.idRiesgos else b.id end as codigo, e.descripcion as validez, f.nombre as gestor
        FROM App\Entity\Revision a 
        JOIN a.trabajador b
        JOIN a.puestoTrabajo c
        JOIN a.empresa d
        JOIN a.validez e
        LEFT JOIN d.gestorAdministrativo f
        WHERE a.anulado = false
        and b.anulado = false
        and c.anulado = false
        and d.anulado = false ";
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

            $revisionId = $r['id'];

            $fecha = "";
            if (!is_null($r['fecha'])) {
                $fecha = $r['fecha']->format('d/m/Y');
            }
            $item['fecha'] = $fecha;
            $item['trabajador'] = $r['trabajador'];
            $item['dni'] = $r['dni'];
            $item['puesto'] = $r['puesto'];
            $item['empresa'] = $r['empresa'];
            $item['gestor'] = $r['gestor'];

            if ($r['apto'] == 1) {
                $item['apto'] = '<span class="badge badge-success"><i class="icon-check"></i></span>';
            } elseif ($r['apto'] == 2) {
                $item['apto'] = '<span class="badge badge-warning"><i class="icon-check"></i></span>';
            } elseif ($r['apto'] == 3) {
                $item['apto'] = '<span class="badge badge-danger"><i class="icon-cross"></i></span>';
            } else {
                $item['apto'] = '';
            }
            $item['codigo'] = $r['codigo'];
            $item['validez'] = $r['validez'];

            $item['input'] = '<div class="uniform-checker" id="uniform-' . $revisionId . '"><span><input type="checkbox" name="aptitud" id="' . $revisionId . '" class="form-check-input-styled form-check-input" value="1" /></span></div>';

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

    public function filtrarRevisiones(Request $request)
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
        } else {
            die();
        }
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $arrayRevisiones = "";
        if ($dtini != "" && $dtfin != "") {
            $query = "select id from revision where anulado = false and validez_id is not null
                and case when validez_id = 1 then
                fecha + interval '1 year'
                when validez_id = 2 then
                fecha + interval '2 year'
                when validez_id = 3 then
                fecha + interval '3 year'
                when validez_id = 4 then
                fecha + interval '4 year'
                when validez_id = 5 then
                fecha + interval '5 year'
                else null end between '$dtini' and '$dtfin'";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $revisiones = $stmt->fetchAll();

            foreach ($revisiones as $r) {
                $arrayRevisiones .= $r['id'] . ",";
            }
        }
        $arrayRevisiones = rtrim($arrayRevisiones, ",");

        $dql = "SELECT d.id as id, a.fecha as fecha, b.nombre as trabajador, c.descripcion as puesto, d.empresa as empresa, IDENTITY(a.apto) as apto, 
        b.dni, CASE WHEN b.idRiesgos is not null then b.idRiesgos else b.id end as codigo, e.descripcion as validez, f.nombre as gestor
        FROM App\Entity\Revision a 
        JOIN a.trabajador b
        JOIN a.puestoTrabajo c
        JOIN a.empresa d
        JOIN a.validez e
        LEFT JOIN d.gestorAdministrativo f
        WHERE a.anulado = false
        and b.anulado = false
        and c.anulado = false
        and d.anulado = false
        and a.estado = 4 ";

        if ($arrayRevisiones != "") {
            $dql .= "and a.id in ($arrayRevisiones) ";
        } else {
            if ($dtini != "" && $dtfin != "") {
                $dql .= "and a.id = -1 ";
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

            $revisionId = $r['id'];

            $fecha = "";
            if (!is_null($r['fecha'])) {
                $fecha = $r['fecha']->format('d/m/Y');
            }
            $item['fecha'] = $fecha;
            $item['trabajador'] = $r['trabajador'];
            $item['dni'] = $r['dni'];
            $item['puesto'] = $r['puesto'];
            $item['empresa'] = $r['empresa'];
            $item['gestor'] = $r['gestor'];

            if ($r['apto'] == 1) {
                $item['apto'] = '<span class="badge badge-success"><i class="icon-check"></i></span>';
            } elseif ($r['apto'] == 2) {
                $item['apto'] = '<span class="badge badge-warning"><i class="icon-check"></i></span>';
            } elseif ($r['apto'] == 3) {
                $item['apto'] = '<span class="badge badge-danger"><i class="icon-cross"></i></span>';
            } else {
                $item['apto'] = '';
            }
            $item['codigo'] = $r['codigo'];
            $item['validez'] = $r['validez'];

            $item['input'] = '<div class="uniform-checker" id="uniform-' . $revisionId . '"><span><input type="checkbox" name="aptitud" id="' . $revisionId . '" class="form-check-input-styled form-check-input" value="1" /></span></div>';

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

    public function preSendAviso(Request $request)
    {
        $session = $request->getSession();
        $aptitudesSelect = $_REQUEST['aptitudes'];
        $session->set('aptitudesSeleccionadasRecordatorio', $aptitudesSelect);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function sendAvisoVencimientoAptitud(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getAvisoVencimientoAptitud()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();
        $mail = $usuario->getMail();
        $mail = trim($mail);
        $emailUser = $usuario->getEmail();
        $passwordMail = $usuario->getPasswordMail();
        $hostMail = $usuario->getHostMail();
        $puertoMail = $usuario->getPuertoMail();
        $encriptacionMail = $usuario->getEncriptacionMail();
        $userMail = $usuario->getUserMail();

        //Buscamos la configuración de la gestión documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $rutaGestionDocumental = $gdocConfig->getRuta();
        $carpetaPlantillas = $gdocConfig->getCarpetaPlantillas();

        if (is_null($mail) || is_null($passwordMail) || is_null($puertoMail) || is_null($hostMail) || is_null($encriptacionMail) || is_null($userMail)) {
            $traduccion = $translator->trans('TRANS_AVISO_NO_CONFIGURACION_CORREO');
            $this->addFlash('danger', $traduccion);
            return $this->redirectToRoute('aviso_vencimiento_aptitud_show');
        }
        $aptitudesSelectArray = $session->get('aptitudesSeleccionadasRecordatorio');
        $carpetaAvisoVencimiento = $em->getRepository('App\Entity\GdocPlantillasCarpeta')->findOneBy(array('nombre' => 'PLANTILLAS AVISO VENCIMIENTO APTITUD', 'anulado' => false));
        $facturasNoEnviadas = "";
        $noEmails = null;
        try {
            for ($i = 0; $i <= count($aptitudesSelectArray) - 1; $i++) {
                $empresaId = $aptitudesSelectArray[$i];
                $empresa = $this->getDoctrine()->getRepository('App\Entity\Empresa')->find($empresaId);

                $ficheroAvisoAptitud = $em->getRepository('App\Entity\GdocPlantillas')->findOneBy(array('nombre' => 'AVISO VENCIMIENTO APTITUD', 'anulado' => false));
                $nombreCompleto = $ficheroAvisoAptitud->getNombreCompleto();

                $urlPlantilla = $rutaGestionDocumental . $carpetaPlantillas . '/' . $nombreCompleto;
                $mensaje = $this->recuperarTextoPlantilla($urlPlantilla, $empresa->getEmpresa());

                $funcionIntranet = $em->getRepository('App\Entity\FuncionCorreo')->find(4);
                $correosIntranet = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false, 'funcion' => $funcionIntranet));

                $destinatarios = null;
                if (count($correosIntranet) > 0) {
                    foreach ($correosIntranet as $ci) {
                        if (!is_null($ci->getCorreo()) && $ci->getCorreo() != "") {
                            $destinatarios .= trim($ci->getCorreo()) . ';';
                        }
                    }
                }
                if (!is_null($destinatarios)) {
                    $urlPlantillaConformidad = null;
                    if (!is_null($carpetaAvisoVencimiento)) {
                        $ficheroConformidad = $em->getRepository('App\Entity\GdocPlantillas')->findOneBy(array('nombre' => 'CONFORMIDAD REVISIONES MEDICAS', 'carpeta' => $carpetaAvisoVencimiento));
                        $nombreCompletoConformidad = $ficheroConformidad->getNombreCompleto();
                        $urlPlantillaConformidad = $rutaGestionDocumental . $carpetaPlantillas . '/' . $nombreCompletoConformidad;
                    }
                    try {
                        $this->enviarAvisoVencimientoAptitud($em, $hostMail, $puertoMail, $encriptacionMail, $mail, $passwordMail, "Recordatorio vencimiento aptitudes", null, $emailUser, $mensaje, $usuario, $destinatarios, $userMail, $emailUser, $urlPlantillaConformidad);
                        $this->insertLogMail($em, $usuario, "Aviso vencimiento aptitud enviada con exito", $mail, $empresa->getEmpresa(), "Aviso vencimiento aptitud enviada con exito");
                    } catch (\Exception $e) {
                        $facturasNoEnviadas .= $empresa->getEmpresa() . " , ";
                        $this->insertLogMail($em, $usuario, "Aviso vencimiento aptitud no enviada, error", $mail, $e, $empresa->getEmpresa());
                    }
                } else {
                    $noEmails .= $empresa->getEmpresa() . ',';
                }
            }
        } catch (\Exception $e) {
            $traduccion = $translator->trans('TRANS_AVISO_ERROR');
            $this->addFlash('danger', $traduccion);
            return $this->redirectToRoute('aviso_vencimiento_aptitud_show');
        }
        $session->set('aptitudesSeleccionadasRecordatorio', null);

        $traduccion = $translator->trans('TRANS_AVISO_VENCIMIENTO_APTITUD_OK');
        $this->addFlash('success', $traduccion);
        if($facturasNoEnviadas != ""){
            $traduccion = $translator->trans('TRANS_ENVIAR_AVISO_VENCIMIENTO_APTITUD_ERROR_EMPRESA');
            $this->addFlash('danger', $traduccion." ".$facturasNoEnviadas);
        }

        if (!is_null($noEmails)) {
            $noEmails = rtrim($noEmails, ',');
            $traduccion = $translator->trans('TRANS_ENVIAR_AVISO_VENCIMIENTO_APTITUD_ERROR_EMPRESA');
            $this->addFlash('danger', $traduccion . ' ' . $noEmails);
        }
        return $this->redirectToRoute('aviso_vencimiento_aptitud_show');
    }

    function enviarAvisoVencimientoAptitud($em, $hostMail, $puertoMail, $encriptacionMail, $mail, $passwordMail, $asunto, $cc, $cco, $mensaje, $usuario, $destinatarios, $userMail, $emailUser, $urlPlantillaConformidad)
    {
        $destinatarios = rtrim($destinatarios, ";");

        $transport = new \Swift_SmtpTransport($hostMail, $puertoMail, $encriptacionMail);
        $transport->setUsername($userMail);
        $transport->setPassword($passwordMail);
        $transport->setHost($hostMail);
        $transport->setAuthMode('login');

        $mailer = new \Swift_Mailer($transport);

        $message = new \Swift_Message();
        $message->setSubject($asunto);
        $message->setFrom($mail);
        $message->setReplyTo($emailUser);
        $message->setTo(explode(";", $destinatarios));

        if (!is_null($cc) && $cc != "") {
            $message->setCc(explode(";", $cc));
        }
        if (!is_null($cco) && $cco != "") {
            $message->setBcc(explode(";", $cco));
        }
        $message->setBody($mensaje, 'text/plain');

        if (!is_null($urlPlantillaConformidad)) {
            $message->attach(\Swift_Attachment::fromPath($urlPlantillaConformidad));
        }
        //Enviamos el correo
        $mailer->send($message);

        //Insertamos el correo en el log
        $this->insertLogMail($em, $usuario, $asunto, $destinatarios, $message->getBody(), "Envío de recordatorio vencimiento aptitudes");
    }

    function recuperarTextoPlantilla($urlPlantilla, $empresa)
    {
        $striped_content = '';
        $content = '';

        $zip = zip_open($urlPlantilla);

        if (!$zip || is_numeric($zip)) return false;

        while ($zip_entry = zip_read($zip)) {

            if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

            if (zip_entry_name($zip_entry) != "word/document.xml") continue;

            $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

            zip_entry_close($zip_entry);
        }
        zip_close($zip);

        $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
        $content = str_replace('</w:r></w:p>', "\r\n", $content);
        $striped_content = strip_tags($content);

        $texto = str_replace("{EMPRESA_NOMBRE}", $empresa, $striped_content);

        return $texto;
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
