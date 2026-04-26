<?php

namespace App\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Logger;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuditoriaController extends AbstractController
{
    public function index(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMonitorAuditoriaSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $object = array("json"=>$username, "entidad"=>"auditoria", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $object, $usuario, TRUE);
        $em->flush();

        //Buscamos los contratos no renovados
//	    $query = "select action, to_char(logged_at, 'DD/MM/YYYY HH24:MI') as logged_at, object_class, data, username from ext_log_entries order by logged_at desc";
//////	    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
//////	    $stmt->execute();
//////	    $auditoria = $stmt->fetchAll();

        return $this->render('auditoria/index.html.twig');
    }

    public function filtraMonitorAuditoria(Request $request){
        $ini = "";
        if(isset($_REQUEST['ini'])){
            $ini = $_REQUEST['ini'];
        }

        $fin = "";
        if(isset($_REQUEST['fin'])){
            $fin = $_REQUEST['fin'];
        }

        $query = "select action, to_char(logged_at, 'DD/MM/YYYY HH24:MI') as logged_at, object_class, data, username from ext_log_entries where 1=1";

        if ($ini != ""){
            $query .= " and logged_at >= '$ini 00:00:00' ";
        }

        if ($fin != ""){
            $query .= " and logged_at <= '$fin 23:59:59' ";
        }

        $query .= " order by logged_at desc";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $auditoria = $stmt->fetchAll();
        return new JsonResponse(json_encode($auditoria));
    }

    public function dataAuditoria(Request $request, TranslatorInterface $translator){

        if ($request->getMethod() == 'POST')
        {
            $draw = intval($request->request->get('draw'));
            $start = $request->request->get('start');
            $length = $request->request->get('length');
            $search = $request->request->get('search');
            $orders = $request->request->get('order');
            $columns = $request->request->get('columns');
        }
        else{
            die();
        }

        $dql = "SELECT a.id, a.action, a.loggedAt, '' AS data, a.objectClass, a.username FROM Gedmo\Loggable\Entity\LogEntry a where 1=1 ";

        /*
         *
         * Filtros
         *
         * */
        if($search['value'] != "")
        {
            $queryLikes = "and (";
            foreach ($columns as $column){

                if($column['searchable'] != "false"){
                    $queryLikes .= "lower(".$column['name'].") LIKE '%".mb_strtolower($search['value'])."%' OR ";
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
        foreach($orders as $order){
            $columName = $columns[$order['column']]['name'];
            $orderBy.= $columName.' '.$order['dir'].', ';
        }
        $orderBy = rtrim($orderBy, ", ");
        $dql .= $orderBy;

        $query = $this->getDoctrine()->getManager()->createQuery($dql)
            ->setFirstResult($start)
            ->setMaxResults($length);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $paginator->setUseOutputWalkers(false);
        $recordsTotal = count($paginator);

        $arrayAuditoria = array();

        foreach ($paginator as $r) {
            $id = $r['id'];
            $item['action'] = $r['action'];

            $fecha = $r['loggedAt']->format('d/m/Y H:i:s');
            $fechatimestamp = $r['loggedAt']->format('Ymdhis');
            $item['fecha'] = '<span style="display:none;">'.$fechatimestamp.'</span>'.$fecha;

            $item['clase'] = $r['objectClass'];

            $query = "select data from ext_log_entries where id = $id";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultData = $stmt->fetchAll();

            $item['data'] = $resultData[0]['data'];
            $item['username'] = $r['username'];

            array_push($arrayAuditoria, $item);
        }

        return $this->json([
            'draw' => $draw,
            'recordsTotal' => $length,
            'recordsFiltered' => $recordsTotal,
            'data' => $arrayAuditoria,
            'dql' => $dql,
            'dqlCountFiltered' => '',
        ]);
    }
}