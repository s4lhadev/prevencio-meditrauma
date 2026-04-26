<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class QueryController extends AbstractController
{
    public function cambioIdioma(Request $request)
    {
        $session = $request->getSession();
	    $idioma = $_REQUEST['idioma'];
	    $session->set('_locale', $idioma);

	    $data = array();
	    array_push($data, "OK");

		return new JsonResponse($data);
    }

    public function buscaAjax(Request $request, $option){

	    $session = $request->getSession();
	    if (isset($_REQUEST['q'])) {
		    $query = $_REQUEST['q'];
		    $query = strtolower($query);
	    } else {
		    return new JsonResponse(0);
	    }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();

	    switch ($option){
		    case 'centroTrabajo':
			    $sql = "SELECT * FROM (SELECT id, nombre as descripcion from centro ) AS s where LOWER(s.descripcion) like '%".$query."%' ";
			    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);
			    $stmt->execute();
			    $centroTrabajoEmpresa = $stmt->fetchAll();

			    return new JsonResponse(json_encode($centroTrabajoEmpresa));
		    	break;
		    case 'trabajadorEmpresa':
			    $sql = "SELECT * FROM (SELECT id, nombre || ' - ' || dni as descripcion from trabajador ) AS s where LOWER(s.descripcion) like '%".$query."%' ";
			    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);
			    $stmt->execute();
			    $trabajadorEmpresa = $stmt->fetchAll();

			    return new JsonResponse(json_encode($trabajadorEmpresa));
			    break;
            case 'empresa':
                $sql = "SELECT * FROM (SELECT id, empresa as descripcion from empresa ) AS s where LOWER(s.descripcion) like '%".$query."%' ";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);
                $stmt->execute();
                $empresa = $stmt->fetchAll();

                return new JsonResponse(json_encode($empresa));
                break;
            case 'copiarRiesgosPuestoTrabajo':
                $sql = "SELECT * FROM (select distinct d.empresa || ' - ' || to_char(a.fecha_inicio, 'DD-MM-YYYY') || ' - ' || c.descripcion as descripcion, b.id from evaluacion a
                    inner join puesto_trabajo_evaluacion b on a.id = b.evaluacion_id
                    inner join puesto_trabajo_centro c on b.puesto_trabajo_id = c.id
                    inner join empresa d on a.empresa_id = d.id
                    where a.anulado = false
                    and b.anulado = false ";

                if($id == 30){
                    $sql .= " and d.id in (select a.id from empresa a inner join tecnico_empresa b on a.id = b.empresa_id inner join tecnico c on b.tecnico_id = c.id where a.anulado = false and c.anulado = false and b.tecnico_id = 37) ";
                }

                if($id == 31){
                    $sql .= " and d.id in (select a.id from empresa a inner join tecnico_empresa b on a.id = b.empresa_id inner join tecnico c on b.tecnico_id = c.id where a.anulado = false and c.anulado = false and b.tecnico_id = 10) ";
                }

                $sql .= " and c.anulado = false ) AS s where LOWER(s.descripcion) like '%".$query."%' ";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);
                $stmt->execute();
                $empresa = $stmt->fetchAll();

                return new JsonResponse(json_encode($empresa));
                break;
            case 'copiarRiesgosZonaTrabajo':
                $sql = "SELECT * FROM (select distinct d.empresa || ' - ' || to_char(a.fecha_inicio, 'DD-MM-YYYY') || ' - ' || c.descripcion as descripcion, b.id from evaluacion a
                    inner join zona_trabajo_evaluacion b on a.id = b.evaluacion_id
                    inner join zona_trabajo c on b.zona_trabajo_id = c.id
                    inner join empresa d on a.empresa_id = d.id
                    where a.anulado = false
                    and b.anulado = false ";

                if($id == 30){
                    $sql .= " and d.id in (select a.id from empresa a inner join tecnico_empresa b on a.id = b.empresa_id inner join tecnico c on b.tecnico_id = c.id where a.anulado = false and c.anulado = false and b.tecnico_id = 37) ";
                }

                if($id == 31){
                    $sql .= " and d.id in (select a.id from empresa a inner join tecnico_empresa b on a.id = b.empresa_id inner join tecnico c on b.tecnico_id = c.id where a.anulado = false and c.anulado = false and b.tecnico_id = 10) ";
                }

                $sql .= " and c.anulado = false ) AS s where LOWER(s.descripcion) like '%".$query."%' ";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);
                $stmt->execute();
                $empresa = $stmt->fetchAll();

                return new JsonResponse(json_encode($empresa));
                break;
	    }

    }
}