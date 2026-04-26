<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Entity\Centro;
use App\Entity\Citacion;
use App\Entity\LogEnvioMail;
use App\Entity\TareaTecnico;
use App\Form\CitacionShowType;
use App\Form\CitacionType;
use App\Form\TareaTecnicoType;
use App\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Description of AgendaTecnicoController
 *
 * @author smarin
 */
class AgendaTecnicoController extends AbstractController {

    public function showAgenda(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $session = $request->getSession();

        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $agendaTecnico = $em->getRepository('App\Entity\AgendaTecnico')->findOneBy(array('usuario' => $usuario));

        $duracionTramo = "00:05";
        $horaInicio = "08:00";
        $horaFin = "21:00";
        $finSemanaSn = "1";
        if(!is_null($agendaTecnico)){
            if(!is_null($agendaTecnico->getDuracionTramo())){
                $duracionTramo = $agendaTecnico->getDuracionTramo()->format('H:i');
            }


            if(!is_null($agendaTecnico->getHorainicio())){
                $horaInicio = $agendaTecnico->getHorainicio()->format('H:i');
            }


            if(!is_null($agendaTecnico->getHorafin())){
                $horaFin = $agendaTecnico->getHorafin()->format('H:i');
            }

            if($agendaTecnico->getFinSemanaSn()){
                $finSemanaSn = "1";
            }
        }

        $nombreAgenda = $username;

        //Recuperamos todos los estados
        $estados = $em->getRepository('App\Entity\EstadoTareaTecnico')->findBy(array('anulado' => false));

        $object = array("json"=>$username, "entidad"=>"agenda técnico", "id"=>$id);
        $logger = new Logger();
        $logger->addLog($em, "logout", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('agendatecnico/show.html.twig', array('horaInicio' => $horaInicio, 'horaFin' => $horaFin, 'duracionTramo' => $duracionTramo, 'finSemanaSn' => $finSemanaSn, 'estados' => $estados, 'nombreAgenda' => $nombreAgenda));
    }

    public function createTarea(Request $request, TranslatorInterface $translator, \Swift_Mailer $mailer){

        $em = $this->getDoctrine()->getManager();
	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getAddAgendaTecnicoSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $user = $this->getUser();
        $usuario = $repository->findForSecurityUser($user);

	    //Creamos el objeto
        $tareaTecnico = new TareaTecnico();

        $tareaTecnico->setUsuario($usuario);

	    //Buscamos el estado programado
        $estado = $em->getRepository('App\Entity\EstadoTareaTecnico')->find(1);
        $tareaTecnico->setEstado($estado);

	    //Comprobamos si nos pasan una fecha por parametro
        $customDtini = null;
        if(isset($_REQUEST['dtini'])){
            if (!is_null($_REQUEST['dtini'])) {
                $customDtini = new \DateTime($_REQUEST['dtini']);
                $tareaTecnico->setFechainicio($customDtini);
            }
        }

	    $form = $this->createForm(TareaTecnicoType::class, $tareaTecnico);
	    $form->handleRequest($request);

	    if ($form->isSubmitted()) {
            $tareaTecnico = $form->getData();
		    $em = $this->getDoctrine()->getManager();

		    //Calculamos la fecha fin
            $duracion = $tareaTecnico->getDuracion()->format('H:i');
            $tiempo_arr = explode(':', $duracion);
            $horas = intval($tiempo_arr[0]);
            $minutos = intval($tiempo_arr[1]);
            $tiempo_dti = new \DateInterval('PT' . $horas . 'H' . $minutos . 'M');

            $fechaInicioString = $tareaTecnico->getFechainicio()->format('Y-m-d H:i:s');
            $fechaFin = new \DateTime($fechaInicioString);
            $fechaFin->add($tiempo_dti);
            $tareaTecnico->setFechafin($fechaFin);

            $em->persist($tareaTecnico);
		    $em->flush();

		    $traduccion = $translator->trans('TRANS_CREATE_OK');
		    $this->addFlash('success',  $traduccion);
		    return $this->redirectToRoute('tecnico_agenda_show');
	    }

	    return $this->render('agendatecnico/edit.html.twig', array('form' => $form->createView()));
    }

	public function updateTarea(Request $request, $id, TranslatorInterface $translator){

		$em = $this->getDoctrine()->getManager();
		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getEditAgendaTecnicoSn()){
				return $this->redirectToRoute('error_403');
			}
		}

		$tareaTecnico = $em->getRepository('App\Entity\TareaTecnico')->find($id);

        //Si el estado es REALIZADO deshabilitamos los campos
        $disabled = false;
        if($tareaTecnico->getEstado()->getId() == 2){
            $disabled = true;
        }

        $form = $this->createForm(TareaTecnicoType::class, $tareaTecnico, array('disabled' => $disabled));
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
            $tareaTecnico = $form->getData();
			if ($form -> get('eliminar')->isClicked()) {
                $tareaTecnico->setAnulado(true);
				$traduccion = $translator->trans('TRANS_DELETE_OK');
				$this->addFlash('success',  $traduccion);
			}else{

                //Calculamos la fecha fin
                $duracion = $tareaTecnico->getDuracion()->format('H:i');
                $tiempo_arr = explode(':', $duracion);
                $horas = intval($tiempo_arr[0]);
                $minutos = intval($tiempo_arr[1]);
                $tiempo_dti = new \DateInterval('PT' . $horas . 'H' . $minutos . 'M');

                $fechaInicioString = $tareaTecnico->getFechainicio()->format('Y-m-d H:i:s');
                $fechaFin = new \DateTime($fechaInicioString);
                $fechaFin->add($tiempo_dti);
                $tareaTecnico->setFechafin($fechaFin);

				$traduccion = $translator->trans('TRANS_UPDATE_OK');
				$this->addFlash('success',  $traduccion);
			}
			$em->persist($tareaTecnico);
			$em->flush();
			return $this->redirectToRoute('tecnico_agenda_show');
		}
		return $this->render('agendatecnico/edit.html.twig', array('form' => $form->createView()));
	}

    public function buscaTareas(Request $request){

        //Recogemos las fechas
        $dtiniTimestamp = $_POST['start'];
        $dtfinTimestamp = $_POST['end'];

        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $user = $this->getUser();
        $usuario = $repository->findForSecurityUser($user);
        $usuarioId = $usuario->getId();

        $query = "select a.id, to_char(a.fechainicio, 'YYYY-mm-dd') as fecha, to_char(a.fechainicio, 'HH24:MI') as hora, to_char(a.fechafin, 'YYYY-mm-dd') as fechafin, to_char(a.fechafin, 'HH24:MI') as horafin,  b.color, a.descripcion from tarea_tecnico a 
                    inner join estado_tarea_tecnico b on a.estado_id = b.id 
                    where a.anulado = false 
                    and a.usuario_id = $usuarioId 
                    and a.fechainicio between '$dtiniTimestamp 00:00:00' and '$dtfinTimestamp 23:59:59'
                    order by a.fechainicio asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $tareas = $stmt->fetchAll();

        $tareasArray = array();
        foreach ($tareas as $tarea){
            $item = array();
            $item['id'] = $tarea['id'];
            $item['title'] = $tarea['descripcion'];
            $item['start'] = $tarea['fecha'].'T'.$tarea['hora'];
            $item['end'] = $tarea['fechafin'].'T'.$tarea['horafin'];
            $item['color'] = $tarea['color'];
            array_push($tareasArray, $item);
        }

        return new JsonResponse(json_encode($tareasArray));
    }
}
