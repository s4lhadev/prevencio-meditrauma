<?php

namespace App\Controller;

use App\Entity\CorreoEmpresa;
use App\Entity\LogEnvioMail;
use App\Form\EnviarCorreoMasivoType;
use App\Form\EnviarCorreoMasivoType2;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Logger;
use Symfony\Contracts\Translation\TranslatorInterface;

class CorreoMasivoController extends AbstractController
{
    public function show(Request $request)
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
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $tipoEmpresa = $this->getDoctrine()->getRepository('App\Entity\CodigoEmpresa')->findAll();

        $empresas = $this->buscaEmpresas("");

        $object = array("json" => $username, "entidad" => "correo masivo", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('correomasivo/show.html.twig', array('empresas' => $empresas, 'tipoEmpresa' => $tipoEmpresa));
    }

    public function show2(Request $request)
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
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $tipoEmpresa = $this->getDoctrine()->getRepository('App\Entity\CodigoEmpresa')->findAll();

        $personal = $this->buscaPersonal("");

        $object = array("json" => $username, "entidad" => "correo masivo", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('correomasivo/show2.html.twig', array('personal' => $personal, 'tipoEmpresa' => $tipoEmpresa));
    }

    public function filtroEmpresas(Request $request)
    {
        //Petició 01/09/2023
        $tipo = $_REQUEST['tipo'];
        $contenido = reset($tipo);
        if ($contenido == 44) {
            $empresas = $this->buscaEmpresasReconocimiento();
        } else {
            $empresas = $this->buscaEmpresas($tipo);
        }
        return new JsonResponse(json_encode($empresas));
    }

    public function buscaPersonal($tipo)
    {
        $query = "select id, username, email from fos_user where rol_id IN(1,3,4,5)";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $personal = $stmt->fetchAll();

        return $personal;
    }

    public function buscaEmpresasReconocimiento()
    {
        $query = "SELECT a.id, a.empresa, a.codigo, a.cif, a.telefono1, a.telefono2, 
                    CONCAT(i.id, ' - ', i.descripcion) AS tipo, 
                    STRING_AGG(DISTINCT b.correo::text, ' , '::text) AS email, 
                    c.nombre AS colaborador, 
                    STRING_AGG(e.nombre::text, ' , '::text) AS tecnico, 
                    f.descripcion AS agente, 
                    g.nombre AS medico, 
                    h.nombre AS responsableAdministrativo, 
                    a.domicilio_postal, a.localidad_postal, a.codigo_postal_postal,
                    a.trabajadores, j.descripcion AS estado, j.id AS estadoid, i.id AS tipoid 
                FROM empresa a
                LEFT JOIN correo_empresa b ON a.id = b.empresa_id 
                LEFT JOIN asesoria c ON a.colaborador_id = c.id
                LEFT JOIN tecnico_empresa d ON a.id = d.empresa_id 
                LEFT JOIN tecnico e ON d.tecnico_id = e.id
                LEFT JOIN comercial f ON a.agente_id = f.id
                LEFT JOIN tecnico g ON a.vigilancia_salud_id = g.id
                LEFT JOIN tecnico h ON a.gestor_administrativo_id = h.id
                LEFT JOIN codigo_empresa i ON a.codigo_empresa_id = i.id
                LEFT JOIN estado_prevencion j ON a.estado_area_administracion_id = j.id
                LEFT JOIN revision r ON a.id = r.empresa_id
                WHERE a.historico_prevenet = false
                AND a.anulado = false
                AND r.empresa_id IS null
                GROUP BY a.id, a.empresa, a.codigo, a.cif, a.telefono1, c.nombre, f.id, g.nombre, h.nombre, i.id, i.descripcion, j.descripcion, j.id
                ORDER BY a.empresa ASC";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $empresas = $stmt->fetchAll();

        return $empresas;
    }

    public function buscaEmpresas($tipo)
    {
        $query = "select a.id, a.empresa, a.codigo, a.cif, a.telefono1, a.telefono2, concat(i.id, ' - ', i.descripcion) as tipo, string_agg(distinct b.correo::text, ' , '::text) AS email, c.nombre as colaborador, 
            string_agg(e.nombre::text, ' , '::text) AS tecnico, f.descripcion as agente, g.nombre as medico, h.nombre as responsableAdministrativo, a.domicilio_postal, a.localidad_postal, a.codigo_postal_postal,
            a.trabajadores, j.descripcion as estado, j.id as estadoid, i.id as tipoid from empresa a
			left join correo_empresa b on a.id = b.empresa_id 
			left join asesoria c on a.colaborador_id = c.id
			left join tecnico_empresa d on a.id = d.empresa_id 
            left join tecnico e on d.tecnico_id = e.id
            left join comercial f on a.agente_id = f.id
            left join tecnico g on a.vigilancia_salud_id = g.id
            left join tecnico h on a.gestor_administrativo_id = h.id
            left join codigo_empresa i on a.codigo_empresa_id = i.id
            left join estado_prevencion j on a.estado_area_administracion_id = j.id
            where historico_prevenet = false
            and a.anulado = false ";

        if ($tipo != "") {
            $tipos = "";
            foreach ($tipo as $t) {
                $tipos .= $t . ',';
            }
            $tipos = rtrim($tipos, ',');
            $query .= " and i.id in ($tipos) ";
        }
        $query .= " group by a.id, a.empresa, a.codigo, a.cif, a.telefono1, c.nombre, f.id, g.nombre, h.nombre, i.id, i.descripcion, j.descripcion, j.id order by a.empresa asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $empresas = $stmt->fetchAll();

        return $empresas;
    }

    public function preSendCorreoMasivo2(Request $request)
    {
        $session = $request->getSession();
        $empresasSelect = $_REQUEST['personal'];
        $session->set('empresasSeleccionadasMailMasivo', $empresasSelect);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function preSendCorreoMasivo(Request $request)
    {
        $session = $request->getSession();
        $empresasSelect = $_REQUEST['empresas'];
        $session->set('empresasSeleccionadasMailMasivo', $empresasSelect);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function sendCorreoMasivo2(Request $request, TranslatorInterface $translator)
    {

        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEnviarCorreoMasivoSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();
        $emailUser = $usuario->getEmail();
        $mail = "mdtmeditrauma@meditrauma.com";
        $passwordMail = "6M%y59ns1";
        $hostMail = "mail.meditrauma.com";
        $puertoMail = "25";
        $encriptacionMail = "";
        $userMail = "mdtmeditrauma@meditrauma.com";

        $empresasSeleccionadasArray = $session->get('empresasSeleccionadasMailMasivo');

        $form = $this->createForm(EnviarCorreoMasivoType2::class, null, array("cco" => $emailUser));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if (is_null($mail) || is_null($passwordMail) || is_null($puertoMail) || is_null($hostMail) || is_null($encriptacionMail) || is_null($userMail)) {
                $traduccion = $translator->trans('TRANS_AVISO_NO_CONFIGURACION_CORREO');
                $this->addFlash('danger', $traduccion);
                return $this->redirectToRoute('envio_masivo_correo_show');
            }
            $em->beginTransaction();
            $mensaje = $form["mensaje"]->getData();
            //Peticio 28/07/2023
            try {
                for ($i = 0; $i <= count($empresasSeleccionadasArray) - 1; $i++) {
                    $personalId = $empresasSeleccionadasArray[$i];
                    $personal = $this->getDoctrine()->getRepository('App\Entity\User')->find($personalId);
                    $connection = $this->getDoctrine()->getManager()->getConnection();
                    $query = "INSERT INTO notificaciones_internas (id, fecha, destinatario, remitente, mensaje, leido)
                          VALUES (:id, :fecha, :destinatario, :remitente, :mensaje, false)";
                    $statement = $connection->prepare($query);
                    $querySelectUpdated = "SELECT id FROM notificaciones_internas ORDER BY id DESC LIMIT 1;";
                    $statementSelect = $connection->prepare($querySelectUpdated);
                    $statementSelect->execute();
                    $updatedRows = $statementSelect->fetchAll();
                    foreach ($updatedRows as $row) {
                        $ultimoId = $row['id'];
                        // Hacer algo con el valor de $id
                    }
                    $fechaActual = new \DateTime();
                    $fechaFormateada = $fechaActual->format('d-m-Y H:i:s'); // Formatear la fecha a formato 'YYYY-MM-DD HH:MM:SS'
                    // Reemplazar saltos de línea por otro carácter (por ejemplo, <br>)
                    $mensajeSinSaltos = str_replace("\n", " ", $mensaje);
                    $params = [
                        'id' => $ultimoId + 1,
                        'fecha' => $fechaFormateada,
                        'destinatario' => $personal->getUsername(),
                        'remitente' => $username,
                        'mensaje' => $mensajeSinSaltos,
                    ];
                    $statement->execute($params);
                }
            } catch (\Exception $e) {
                $em->rollBack();

                $traduccion = $translator->trans('TRANS_AVISO_ERROR');
                $this->addFlash('danger', $traduccion);
                return $this->redirectToRoute('envio_masivo_correo_show2');
            }
            $em->commit();

            $session->set('empresasSeleccionadasMailMasivo', null);

            $traduccion = "El correo interno se ha enviado con éxito.";
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('envio_masivo_correo_show2');
        }
        $object = array("json" => $username, "entidad" => "enviar correo masivo", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('correomasivo/send_email2.html.twig', array('form' => $form->createView()));
    }

    public function sendCorreoMasivo(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEnviarCorreoMasivoSn()) {
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

        $empresasSeleccionadasArray = $session->get('empresasSeleccionadasMailMasivo');

        $form = $this->createForm(EnviarCorreoMasivoType::class, null, array("cco" => $emailUser));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (is_null($mail) || is_null($passwordMail) || is_null($puertoMail) || is_null($hostMail) || is_null($encriptacionMail) || is_null($userMail)) {
                $traduccion = $translator->trans('TRANS_AVISO_NO_CONFIGURACION_CORREO');
                $this->addFlash('danger', $traduccion);
                return $this->redirectToRoute('envio_masivo_correo_show');
            }
            //Recogemos lo datos
            $cc = $form["cc"]->getData();
            $cco = $form["cco"]->getData();
            $asunto = $form["asunto"]->getData();
            $mensaje = $form["mensaje"]->getData();
            $fichero = $form->get('fichero')->getData();

            $em->beginTransaction();

            $transport = new \Swift_SmtpTransport($hostMail, $puertoMail, $encriptacionMail);
            $transport->setUsername($userMail);
            $transport->setPassword($passwordMail);
            $transport->setHost($hostMail);
            $transport->setAuthMode('login');
            $contador = 0;
            $mailer = new \Swift_Mailer($transport);

            try {
                for ($i = 0; $i <= count($empresasSeleccionadasArray) - 1; $i++) {
                    $mailer = new \Swift_Mailer($transport);
                    $empresaId = $empresasSeleccionadasArray[$i];
                    $empresa = $this->getDoctrine()->getRepository('App\Entity\Empresa')->find($empresaId);
                    $contador = $contador + 1;
                    if ($contador == 100) {
                        sleep(1);
                        $contador = 0;
                    }
                    $this->enviarCorreoMasivo($em, $empresa, $mail, $asunto, $cc, $cco, $mensaje, $usuario, $fichero, $mailer, $emailUser);
                }
            } catch (\Exception $e) {
                $em->rollBack();

                //PROVA PROD 12/09/2023 ERROR ENVIO MASSIVO 2K MAILS
                //$traduccion = $translator->trans('TRANS_AVISO_ERROR');
                $traduccion = $e;
                $this->addFlash('danger', $traduccion);
                return $this->redirectToRoute('envio_masivo_correo_show');
            }
            $em->commit();

            $session->set('empresasSeleccionadasMailMasivo', null);

            $traduccion = $translator->trans('TRANS_CORREO_MASIVO_OK');
            $this->addFlash('success', $traduccion);
            return $this->redirectToRoute('envio_masivo_correo_show');
        }
        $object = array("json" => $username, "entidad" => "enviar correo masivo", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('correomasivo/send_email.html.twig', array('form' => $form->createView()));
    }

    public function enviarCorreoMasivo2($em, $personal, $mail, $asunto, $cc, $cco, $mensaje, $usuario, $fichero, $mailer, $emailUser)
    {
        $destinatarios = null;
        //$correosEnviarNotificacion = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false, 'funcion' => $funcionEnviarCorreo));

        $destinatarios .= $personal->getEmail() . ';';

        if (!is_null($destinatarios) && $destinatarios != "") {
            $destinatarios = rtrim($destinatarios, ";");

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

            $filename = null;
            if (!is_null($fichero)) {
                //Obtenemos el nombre y la extension
                $filename = $fichero->getClientOriginalName();

                //RUTA PROD
                move_uploaded_file($fichero, "upload/media/tmp/$filename");
                $message->attach(\Swift_Attachment::fromPath("upload/media/tmp/$filename"));
                //RUTA LOCAL
                //move_uploaded_file($fichero, "C:\Users\david.jimenez\Desktop\Versiones codigo\CARPETAS PROVES LIBREOFFICE PREVENCIÓ/$filename");
                //$message->attach(\Swift_Attachment::fromPath("C:\Users\david.jimenez\Desktop\Versiones codigo\CARPETAS PROVES LIBREOFFICE PREVENCIÓ/$filename"));
            }
            //Enviamos el correo
            $mailer->send($message);
            $mailer->getTransport()->stop();

            //if(!is_null($fichero)){
            //unlink("upload/media/tmp/$filename");
            //}

            //Insertamos el correo en el log
            $this->insertLogMail($em, $usuario, $asunto, $destinatarios, $message->getBody(), "Envío de correo interno");
        }
    }

    public function enviarCorreoMasivo($em, $empresa, $mail, $asunto, $cc, $cco, $mensaje, $usuario, $fichero, $mailer, $emailUser)
    {
        $destinatarios = null;
        $funcionEnviarFactura = $em->getRepository('App\Entity\FuncionCorreo')->find(2);
        $funcionEnviarCorreo = $em->getRepository('App\Entity\FuncionCorreo')->find(3);
        $correosEnviarNotificacion = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false, 'funcion' => $funcionEnviarCorreo));
        $correosEnviarFactura = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false, 'funcion' => $funcionEnviarFactura));

        if (count($correosEnviarNotificacion) == 0) {
            if (count($correosEnviarFactura) > 0) {
                foreach ($correosEnviarFactura as $fef) {
                    $newCorreoEnvioNotificacion = new CorreoEmpresa();
                    $newCorreoEnvioNotificacion->setEmpresa($empresa);
                    $newCorreoEnvioNotificacion->setAnulado(false);
                    $newCorreoEnvioNotificacion->setCorreo($fef->getCorreo());
                    $newCorreoEnvioNotificacion->setFuncion($funcionEnviarCorreo);
                    $em->persist($newCorreoEnvioNotificacion);
                    $em->flush();
                }
            }
        }
        $correosEnviarNotificacion = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false, 'funcion' => $funcionEnviarCorreo));

        foreach ($correosEnviarNotificacion as $cef) {
            if (!is_null($cef->getCorreo()) && $cef->getCorreo() != "") {
                $destinatarios .= trim($cef->getCorreo()) . ';';
            }
        }
        if (!is_null($destinatarios) && $destinatarios != "") {
            $destinatarios = rtrim($destinatarios, ";");

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

            $filename = null;
            if (!is_null($fichero)) {
                //Obtenemos el nombre y la extension
                $filename = $fichero->getClientOriginalName();

                move_uploaded_file($fichero, "upload/media/tmp/$filename");
                $message->attach(\Swift_Attachment::fromPath("upload/media/tmp/$filename"));
            }
            //Enviamos el correo
            $mailer->send($message);
            $mailer->getTransport()->stop();
            
            //if(!is_null($fichero)){
            //unlink("upload/media/tmp/$filename");
            //}

            //Insertamos el correo en el log
            $this->insertLogMail($em, $usuario, $asunto, $destinatarios, $message->getBody(), "Envío de correo masivo");
        }
    }

    public function insertLogMail($em, $usuario, $asunto, $destinatario, $mensaje, $tipo)
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
