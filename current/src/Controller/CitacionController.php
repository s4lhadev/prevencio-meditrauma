<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Entity\Centro;
use App\Entity\Citacion;
use App\Entity\Empresa;
use App\Entity\GdocFichero;
use App\Entity\LogEnvioMail;
use App\Form\CitacionShowType;
use App\Form\CitacionType;
use App\Logger;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Description of CitacionController
 *
 * @author smarin
 */
class CitacionController extends AbstractController {

    public function showCitacion(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $session = $request->getSession();

        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $agenda = $session->get('agenda');
        if(is_null($agenda)){
            $agenda = $em->getRepository('App\Entity\Agenda')->find(1);
            $session->set('agenda', $agenda);
        }else{
            $agenda = $em->getRepository('App\Entity\Agenda')->find($agenda->getId());
        }

        //Cogemos los datos de la agenda
        $nombreAgenda = $agenda->getDescripcion();

        if($agenda->getFinSemanaSn()){
            $finSemanaSn = "1";
        }else{
            $finSemanaSn = "0";
        }

        $duracionTramo = "00:05";
        if(!is_null($agenda->getDuracionTramo())){
            $duracionTramo = $agenda->getDuracionTramo()->format('H:i');
        }

        $horaInicio = "08:00";
        if(!is_null($agenda->getHorainicio())){
            $horaInicio = $agenda->getHorainicio()->format('H:i');
        }

        $horaFin = "21:00";
        if(!is_null($agenda->getHorafin())){
            $horaFin = $agenda->getHorafin()->format('H:i');
        }

        $ultimaFechaCita = $session->get('dataCalendar');

        //Recuperamos todos los estados
        $estados = $em->getRepository('App\Entity\EstadoCitacion')->findBy(array('anulado' => false));

        $form = $this->createForm(CitacionShowType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $citacionForm = $form->getData();
            $agenda = $form["agenda"]->getData();
            $session->set('agenda', $agenda);
            return $this->redirectToRoute('citacion_show');
        }

        $object = array("json"=>$username, "entidad"=>"citaciones", "id"=>$id);
        $logger = new Logger();
        $logger->addLog($em, "logout", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('citacion/show.html.twig', array('horaInicio' => $horaInicio, 'horaFin' => $horaFin, 'duracionTramo' => $duracionTramo, 'finSemanaSn' => $finSemanaSn, 'estados' => $estados, 'form' => $form->createView(), 'nombreAgenda' => $nombreAgenda, 'agendaId' => $agenda->getId(), 'ultimaFechaCita' => $ultimaFechaCita));
    }

    public function createCitacion(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getAddCitacionSn()){
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

	    //Creamos el objeto
        $citacion = new Citacion();

        $citacion->setUsuarioCrea($usuario);

	    //Buscamos el estado programado
        $estado = $em->getRepository('App\Entity\EstadoCitacion')->find(1);
        $citacion->setEstado($estado);

	    //Comprobamos si nos pasan una fecha por parametro
        $customDtini = null;
        if(isset($_REQUEST['dtini'])){
            if (!is_null($_REQUEST['dtini'])) {
                $customDtini = new \DateTime($_REQUEST['dtini']);
                $citacion->setFechainicio($customDtini);
                //$citacion->setFechafin($customDtini);
            }
        }

        //Asignamos la agenda a la citacion
        $agenda = $session->get('agenda');
        if(!is_null($agenda)){
            $agenda = $em->getRepository('App\Entity\Agenda')->find($agenda->getId());
            $citacion->setAgenda($agenda);
        }

        //Comprobamos si hay una empresa seleccionada
        $empresa = $session->get('empresa');
        if(!is_null($empresa)){
            $empresa = $em->getRepository('App\Entity\Empresa')->find($empresa->getId());
            $citacion->setEmpresa($empresa);
            $citacion->setPruebasComplementarias($empresa->getPruebasComplementarias());
        }

	    $form = $this->createForm(CitacionType::class, $citacion);
	    $form->handleRequest($request);

	    if ($form->isSubmitted()) {
		    $citacion = $form->getData();
            //fix 2025/04/08
            if($citacion->getEmpresa() != null) {
                if($citacion->getEmpresa()->getEstadoAreaAdministracion() != null) {
                    if ($citacion->getEmpresa()->getEstadoAreaAdministracion()->getId() != 4 and $citacion->getEmpresa()->getEstadoAreaAdministracion()->getId() != null) {
                        $this->addFlash('danger', "La empresa seleccionada no es valida");
                        return $this->redirectToRoute('citacion_add');
                    }
                }
            }

            //Guardamos la empresa
            $empresaId = $form["empresa"]->getViewData();
            if($empresaId != ""){
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $citacion->setEmpresa($empresa);
            }

            //Guardamos el trabajador
            $trabajador = null;
            $trabajadorId = $form["trabajador"]->getViewData();
            if($trabajadorId != ""){
                $trabajador = $em->getRepository('App\Entity\Trabajador')->find($trabajadorId);
                $citacion->setTrabajador($trabajador);
            }

            //Guardamos el tecnico
            $tecnico = null;
            $tecnicoId = $form["tecnico"]->getViewData();
            if($tecnicoId != ""){
                $tecnico = $em->getRepository('App\Entity\UsuarioTecnico')->find($tecnicoId);
                $citacion->setTecnico($tecnico);
            }

		    $em->persist($citacion);
		    $em->flush();

            $session->set('dataCalendar', $citacion->getFechainicio());

		    $traduccion = $translator->trans('TRANS_CREATE_OK');
		    $this->addFlash('success',  $traduccion);

            if(!is_null($trabajador)) {
                //Comprobamos si el trabajador tiene informado el correo electronico
                if (!is_null($trabajador->getMail()) && $trabajador->getMail() != "") {
                    if (!is_null($mail) && !is_null($passwordMail) && !is_null($puertoMail) && !is_null($hostMail) && !is_null($encriptacionMail) && !is_null($userMail)) {

                        $transport = new \Swift_SmtpTransport($hostMail, $puertoMail, $encriptacionMail);
                        $transport->setUsername($userMail);
                        $transport->setPassword($passwordMail);
                        $transport->setHost($hostMail);
                        $transport->setAuthMode('login');

                        $mailer = new \Swift_Mailer($transport);

                        //Enviamos el correo a la empresa
                        $this->sendEmail($trabajador->getMail(), $mailer, $citacion, $mail, $em, $usuario, $emailUser);
                    } else {
                        $traduccion = $translator->trans('TRANS_AVISO_NO_CONFIGURACION_CORREO');
                        $this->addFlash('danger', $traduccion);
                    }
                }
            }

		    return $this->redirectToRoute('citacion_update', array('id' => $citacion->getId()));
	    }

	    return $this->render('citacion/edit.html.twig', array('form' => $form->createView()));
    }

    function sendEmail($to, $mailer, $citacion, $mail, $em, $usuario, $emailUser){
        //Buscamos la configuracion de la gestion documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);

        //Buscamos la agenda para saber la plantilla
        $plantilla = $em->getRepository('App\Entity\GdocPlantillas')->findOneBy(array('nombre' => 'CITA PREVIA'));

        //Generamos el fichero
        $filePdf = $this->createRecordatorioCitacion($em, $citacion, $gdocConfig, $plantilla, $usuario);

        //Enviamos el mail al cliente
        $message = new \Swift_Message();
        $message->setSubject("Recordatorio cita");
        $message->setFrom($mail);
        $message->setTo($to);
        $message->setReplyTo($emailUser);
        $message->setBody( $this->renderView(
        // templates/emails/registration.html.twig
            'emails/send_citacion.html.twig',
            ['nombre' => $citacion->getTrabajador()->getNombre()]
        ),
            'text/html');
        $message->attach(\Swift_Attachment::fromPath($filePdf));
        $mailer->send($message);

        //Insertamos el correo en el log
        $this->insertLogMail($em, $usuario, "Recordatorio cita", $to, $message->getBody(), "Recordatorio cita");
    }

    function createRecordatorioCitacion($em, $citacion, $gdocConfig, $plantilla, $usuario){

        //Recuperamos los datos de la plantilla
        $nombreCompleto = $plantilla->getNombreCompleto();
        $nombrePlantilla = $plantilla->getNombre();

        //Buscamos la configuración de la gestión documental
        $rutaGestionDocumental = $gdocConfig->getRuta();
        $carpetaPlantillas = $gdocConfig->getCarpetaPlantillas();
        $carpetaPlantillaGenerada = $gdocConfig->getCarpetaCitacion();

        //Buscamos los datos de la citación
        $empresa = $citacion->getEmpresa();
        $nombreEmpresa = $empresa->getEmpresa();
        $nombreTrabajador = $citacion->getTrabajador()->getNombre();
        $nuevaPlantilla = $nombrePlantilla.' '.$nombreTrabajador.'.docx';

        //Generamos el nuevo fichero a partir de la plantilla
        $urlPlantilla = $rutaGestionDocumental.$carpetaPlantillas.'/'.$nombreCompleto;
        $urlNueva = $rutaGestionDocumental.$carpetaPlantillaGenerada.'/'.$nuevaPlantilla;

        $hoy = new \DateTime();
        $hoyString = $hoy->format('d/m/Y');

        copy($urlPlantilla, $urlNueva);

        //Reemplazamos los tags
        $templateProcessor = new TemplateProcessor($urlNueva);
        $templateProcessor->setValue("EMPRESA_NOMBRE",$nombreEmpresa);

        $fechaCitacion = $citacion->getFechainicio()->format('d') . ' de ' . $this->obtenerMes($citacion->getFechainicio()->format('m')) . ' de ' . $citacion->getFechainicio()->format('Y') .' - '. $citacion->getFechaInicio()->format('H:i');
        $fechaCitacionSinHora = $citacion->getFechainicio()->format('d') . ' de ' . $this->obtenerMes($citacion->getFechainicio()->format('m')) . ' de ' . $citacion->getFechainicio()->format('Y');

        $templateProcessor->setValue("CITACION_FECHA",$fechaCitacion);
        $templateProcessor->setValue("CITACION_FECHA_SIN_HORA",$fechaCitacionSinHora);
        $templateProcessor->setValue("CITACION_DIRECCION",$citacion->getAgenda()->getDireccion());

        if(!is_null($citacion->getTrabajador())){
            $nombreTrabajadorCitacion = $citacion->getTrabajador()->getNombre();
            $dniTrabajadorCitacion = $citacion->getTrabajador()->getDni();
            $templateProcessor->setValue("CITACION_TRABAJADOR_NOMBRE",$nombreTrabajadorCitacion);
            $templateProcessor->setValue("CITACION_TRABAJADOR_DNI",$dniTrabajadorCitacion);
        }

        $templateProcessor->saveAs($urlNueva);

        $gdocFichero = new GdocFichero();
        $gdocFichero->setEmpresa($empresa);
        $gdocFichero->setDtcrea(new \DateTime());
        $gdocFichero->setUsuario($usuario);
        $gdocFichero->setNombre($nuevaPlantilla);
        $gdocFichero->setAnulado(false);
        $gdocFichero->setPlantilla($plantilla);
        $em->persist($gdocFichero);
        $em->flush();

        $nombreFichero = $gdocFichero->getNombre();

        //Convertimos el word en pdf
        $fileDocx = $rutaGestionDocumental.$carpetaPlantillaGenerada.'/'.$nombreFichero;

        $filePdf = str_replace('docx', 'pdf', $fileDocx);
        $outdir = $rutaGestionDocumental.$carpetaPlantillaGenerada;

        //$cmd = '"C:\Program Files (x86)\LibreOffice 5\program\soffice.exe" --headless --convert-to pdf:writer_pdf_Export "'.$fileDocx.'" --outdir "'.$outdir.'"';
        $cmd = 'HOME=/tmp /usr/bin/soffice --headless --convert-to pdf:writer_pdf_Export "'.$fileDocx.'" --outdir "'.$outdir.'"';
        exec($cmd);

        return $filePdf;
    }

    function obtenerMes($mes){
        switch ($mes){
            case '01':
                $return = "enero";
                break;
            case '02':
                $return = "febrero";
                break;
            case '03':
                $return = "marzo";
                break;
            case '04':
                $return = "abril";
                break;
            case '05':
                $return = "mayo";
                break;
            case '06':
                $return = "junio";
                break;
            case '07':
                $return = "julio";
                break;
            case '08':
                $return = "agosto";
                break;
            case '09':
                $return = "septiembre";
                break;
            case '10':
                $return = "octubre";
                break;
            case '11':
                $return = "noviembre";
                break;
            case '12':
                $return = "diciembre";
                break;
        }

        return $return;
    }

	public function updateCitacion(Request $request, $id, TranslatorInterface $translator){

		$em = $this->getDoctrine()->getManager();
		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getEditCitacionSn()){
				return $this->redirectToRoute('error_403');
			}
		}

		$citacion = $em->getRepository('App\Entity\Citacion')->find($id);

        //Buscamos las plantillas de la carpeta CITACION
        $carpeta = $em->getRepository('App\Entity\GdocPlantillasCarpeta')->find(12);
        $listPlantillasCitacion = $em->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpeta, 'anulado' => false));

        //Comprobamos si el trabajador tiene la revisión creada
        $empresaId = null;
        if(!is_null($citacion->getEmpresa())){
            $empresaId = $citacion->getEmpresa()->getId();
        }

        $trabajadorId = null;
        if(!is_null($citacion->getTrabajador())){
            $trabajadorId = $citacion->getTrabajador()->getId();
        }

        $fechaVisita = $citacion->getFechaInicio()->format('Y-m-d');

        $revisionId = null;
        if(!is_null($empresaId) && !is_null($trabajadorId)){
            $query = "select id from revision where empresa_id = $empresaId and trabajador_id = $trabajadorId and anulado = false and fecha = '$fechaVisita 00:00:00'";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultRevisionTrabajador = $stmt->fetchAll();
            if(count($resultRevisionTrabajador) > 0){
                $revisionId = $resultRevisionTrabajador[0]['id'];
            }
        }

        $form = $this->createForm(CitacionType::class, $citacion);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$citacion = $form->getData();

			if ($form -> get('eliminar')->isClicked()) {
				$citacion->setAnulado(true);
				$traduccion = $translator->trans('TRANS_DELETE_OK');
				$this->addFlash('success',  $traduccion);
			}else{

                if($citacion->getEmpresa() != null) {
                    if($citacion->getEmpresa()->getEstadoAreaAdministracion() != null) {
                        if ($citacion->getEmpresa()->getEstadoAreaAdministracion()->getId() != 4 and $citacion->getEmpresa()->getEstadoAreaAdministracion()->getId() != null) {
                            $this->addFlash('danger', "La empresa seleccionada no es valida");
                            return $this->redirectToRoute('citacion_update', array('id' => $citacion->getId()));
                        }
                    }
                }
                
                //Guardamos la empresa
                $empresaId = $form["empresa"]->getViewData();
                if($empresaId != ""){
                    $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                    $citacion->setEmpresa($empresa);
                }

			    //Guardamos el trabajador
                $trabajadorId = $form["trabajador"]->getViewData();
                if($trabajadorId != ""){
                    $trabajador = $em->getRepository('App\Entity\Trabajador')->find($trabajadorId);
                    $citacion->setTrabajador($trabajador);
                }

                //Guardamos el tecnico
                $tecnico = null;
                $tecnicoId = $form["tecnico"]->getViewData();
                if($tecnicoId != ""){
                    $tecnico = $em->getRepository('App\Entity\UsuarioTecnico')->find($tecnicoId);
                    $citacion->setTecnico($tecnico);
                }

				$traduccion = $translator->trans('TRANS_UPDATE_OK');
				$this->addFlash('success',  $traduccion);
			}
			$em->persist($citacion);
			$em->flush();
            return $this->redirectToRoute('citacion_update', array('id' => $citacion->getId()));
		}
		return $this->render('citacion/edit.html.twig', array('form' => $form->createView(), 'trabajadorCitacion' => $citacion->getTrabajador(), 'empresaCitacion' => $citacion->getEmpresa(), 'listPlantillasCitacion' => $listPlantillasCitacion, 'empresaId' => $empresaId, 'trabajadorId' => $trabajadorId, 'revisionId' => $revisionId));
	}

    public function buscaTrabajadorEmpresa(Request $request){

        $empresaId = $_REQUEST['empresaId'];

        $query = "select distinct c.id, trim(c.nombre) as nombre from empresa a 
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
        order by trim(c.nombre) asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $trabajadorEmpresa = $stmt->fetchAll();

        $arrayTrabajadores = array();

        foreach ($trabajadorEmpresa as $te){
            $texto = "";
            $ultimaVisitaString = "(Última visita: ";
            $trabajadorId = $te['id'];
            $item = array();
            $item['id'] = $trabajadorId;

            //Buscamos la fecha de la ultima visita
            $query = "select to_char(max(fechainicio), 'DD/MM/YYYY') as ultima from citacion where anulado = false and trabajador_id = $trabajadorId";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $ultimaVisita = $stmt->fetchAll();

            if(count($ultimaVisita) > 0){
                $texto .= $te['nombre'] . " - ". $ultimaVisitaString . $ultimaVisita[0]['ultima'] . ")";
            }else{
                $texto .= $te['nombre'] . " - ". $ultimaVisitaString;
            }

            //Buscamos lel DNI y fecha de nacimiento
            $query = "select to_char(fecha_nacimiento, 'DD/MM/YYYY') as fecha_nacimiento, dni from trabajador where id = $trabajadorId";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $datosTrabajador = $stmt->fetchAll();

            if(count($ultimaVisita) > 0){
                $texto .= " - (DNI: ". $datosTrabajador[0]['dni'] . ") - (Fecha nacimiento: ". $datosTrabajador[0]['fecha_nacimiento'] . ")";
            }else{
                $texto .= " - (DNI: - Fecha nacimiento: )";
            }

            //Buscamos si el puesto de trabajo esta actualizado
            $query = "select b.actualizado from puesto_trabajo_trabajador a
                inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                where a.anulado = false
                and b.anulado = false 
                and a.trabajador_id = $trabajadorId
                and b.empresa_id = $empresaId
                order by b.descripcion asc";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultPuestoTrabajo = $stmt->fetchAll();

            if(count($resultPuestoTrabajo) > 0){
                if($resultPuestoTrabajo[0]['actualizado']){
                    $texto .= " - (Puesto de trabajo actualizado: Sí)";
                }else{
                    $texto .= " - (Puesto de trabajo actualizado: No)";
                }
            }

            $item['descripcion'] = $texto;

            array_push($arrayTrabajadores, $item);
        }

        if(is_null($arrayTrabajadores)){
            $item = array();
            $item['id'] = "";
            $item['descripcion'] = "";
            array_push($arrayTrabajadores, $item);
        }

        return new JsonResponse(json_encode($arrayTrabajadores));
    }

    public function buscaCitas(Request $request){

        $session = $request->getSession();

        //Recogemos las fechas
        $dtiniTimestamp = $_POST['start'];
        $dtfinTimestamp = $_POST['end'];

        $agenda = $session->get('agenda');
        $agendaId = $agenda->getId();

        $query = "select a.id, to_char(a.fechainicio, 'YYYY-mm-dd') as fecha, to_char(a.fechainicio, 'HH24:MI') as hora, b.color, c.empresa, d.nombre as trabajador, a.comentarios, concat(e.nombre,' ',e.apellido1,' ',e.apellido2) as tecnico from citacion a 
                    inner join estado_citacion b on a.estado_id = b.id 
                    left join empresa c on a.empresa_id = c.id 
                    left join trabajador d on a.trabajador_id = d.id 
                    left join usuario_tecnico e on a.tecnico_id = e.id
                    where a.anulado = false 
                    and a.agenda_id = $agendaId 
                    and a.fechainicio between '$dtiniTimestamp 00:00:00' and '$dtfinTimestamp 23:59:59'
                    order by a.fechainicio asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $citaciones = $stmt->fetchAll();
        $titulo = "";
        $citacionesArray = array();
        foreach ($citaciones as $citacion){
            $item = array();
            $item['id'] = $citacion['id'];

            if(!is_null($citacion['empresa'])){
               $titulo = $citacion['empresa'];
            }

            if(!is_null($citacion['trabajador'])){
                $titulo = $titulo .' - '. $citacion['trabajador'];
            }

            if(!is_null($citacion['tecnico'])){
                $titulo = $titulo .' - <b>'. $citacion['tecnico'].'</b>';
            }

            if(is_null($citacion['empresa']) && is_null($citacion['trabajador'])){
                $titulo = $citacion['comentarios'];
            }

            $item['title'] = $titulo;
            $item['start'] = $citacion['fecha'].'T'.$citacion['hora'];
            $fechaFin = new \DateTime($citacion['fecha'].' '.$citacion['hora']);
            $fechaFin = $fechaFin->add(new \DateInterval('PT10M'));
            $item['end'] = $fechaFin->format('Y-m-d').'T'.$fechaFin->format('H:i');
            $item['color'] = $citacion['color'];
            $item['textcolor'] = 'black';
            array_push($citacionesArray, $item);
        }

        return new JsonResponse(json_encode($citacionesArray));
    }

    public function buscaObservacionesEmpresa(Request $request){

        $empresaId = $_REQUEST['empresaId'];

        $query = "select pruebas_complementarias from empresa where id = $empresaId";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $observacionesEmpresa = $stmt->fetchAll();

        $data = array(
            'observaciones' => $observacionesEmpresa[0]['pruebas_complementarias']
        );

        return new JsonResponse($data);
    }

    public function deselectEmpresa(Request $request){
        $session = $request->getSession();

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);

        $empresa = $session->get('empresa');
        $session->set('empresa', null);

        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "deselect", $empresa, $usuario);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function showCitaciones(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $session = $request->getSession();

        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();
        //S'ha modificat la data inicial
        $fechaActual = date('d-m-Y');
        $citaciones = $this->buscaCitaciones($fechaActual, $fechaActual);


        $object = array("json"=>$username, "entidad"=>"listado citaciones", "id"=>$id);
        $logger = new Logger();
        $logger->addLog($em, "logout", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('citacion/show_citas.html.twig', array('citaciones' => $citaciones));
    }

    function buscaCitaciones($ini, $fin){
        $query = "select a.id, b.empresa, b.id as empresaid, c.nombre as trabajador, c.id as trabajadorid, c.dni, to_char(a.fechainicio, 'DD/MM/YYYY') as fecha, d.descripcion as estado, d.color, e.descripcion as agenda, a.pruebas_complementarias, concat(f.nombre,' ',f.apellido1,' ',f.apellido2) as tecnico from citacion a
        left join empresa b on a.empresa_id = b.id
        left join trabajador c on a.trabajador_id = c.id
        inner join estado_citacion d on a.estado_id = d.id
        left join agenda e on a.agenda_id = e.id
        left join usuario_tecnico f on a.tecnico_id = f.id
        where a.anulado = false
        and b.anulado = false ";

        if($ini != ""){
            $query .= "and a.fechainicio >= '$ini 00:00:00' ";
        }

        if($fin != ""){
            $query .= "and a.fechainicio <= '$fin 23:59:59' ";
        }

        $query .= "order by a.fechainicio desc";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $citas = $stmt->fetchAll();

        $arrayCitaciones = array();
        foreach ($citas as $c){

            $empresaId = $c['empresaid'];
            $trabajadorId = $c['trabajadorid'];

            $item = array();
            $item['id'] = $c['id'];
            $item['empresa'] = $c['empresa'];
            $item['trabajador'] = $c['trabajador'];
            $item['dni'] = $c['dni'];
            $item['fecha'] = $c['fecha'];
            $item['estado'] = $c['estado'];
            $item['color'] = $c['color'];
            $item['agenda'] = $c['agenda'];
            $auxPruebas = str_replace("'", "´", $c['pruebas_complementarias']);
            $item['pruebas_complementarias'] = trim(preg_replace('/\s\s+/', ' ', $auxPruebas));
            $item['tecnico'] = $c['tecnico'];

            if($trabajadorId != "" && $empresaId != ""){
                $query = "select b.descripcion from puesto_trabajo_trabajador a
                inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                where a.anulado = false
                and b.anulado = false 
                and a.trabajador_id = $trabajadorId
                and b.empresa_id = $empresaId
                order by b.descripcion asc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultPuestoTrabajo = $stmt->fetchAll();

                if(count($resultPuestoTrabajo) > 0){
                    $item['puesto'] = $resultPuestoTrabajo[0]['descripcion'];
                }else{
                    $item['puesto'] = '';
                }
            }else{
                $item['puesto'] = '';
            }

            array_push($arrayCitaciones, $item);
        }

        return $arrayCitaciones;
    }

    public function filtroCitaciones(Request $request){
        $ini = $_REQUEST['ini'];
        $fin = $_REQUEST['fin'];

        $citaciones = $this->buscaCitaciones($ini, $fin);

        return new JsonResponse(json_encode($citaciones));
    }

    function insertLogMail($em, $usuario, $asunto, $destinatario, $mensaje, $tipo){
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
