<?php

namespace App\Controller;

use App\Entity\CentroTrabajoEmpresa;
use App\Entity\GdocPlantillas;
use App\Entity\GdocTrabajador;
use App\Entity\PlanificacionRiesgoCausa;
use App\Entity\PuestoTrabajoTrabajador;
use App\Entity\RiesgoCausaEvaluacion;
use App\Entity\Trabajador;
use App\Entity\TrabajadorAltaBaja;
use App\Entity\TrabajadorEmpresa;
use App\Form\GdocTrabajadorType;
use App\Form\GdocType;
use App\Form\PuestoTrabajoEvaluarType;
use App\Form\RiesgoCausaType;
use App\Form\TrabajadorType;
use App\Form\TrabajadorImportType;
use App\Form\TrabajadorMedicoType;
use App\Form\TrabajadorTecnicoType;
use App\Logger;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TrabajadorController extends AbstractController
{
    public function createTrabajador(Request $request, TranslatorInterface $translator)
    {
	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getAddTrabajadorSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

        $em = $this->getDoctrine()->getManager();
        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        $trabajador = new Trabajador();
        $form = $this->createForm(TrabajadorType::class, $trabajador);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
	        $trabajador = $form->getData();

	        $trabajador->setAnulado(false);
            $em->persist($trabajador);
            $em->flush();

            //Asignamos el trabajador a la empresa
            if(!is_null($empresa)){
                $empresa = $this->getDoctrine()->getRepository('App\Entity\Empresa')->find($empresa->getId());
                $trabajadorEmpresa = $this->getDoctrine()->getRepository('App\Entity\TrabajadorEmpresa')->findOneBy(array('trabajador' => $trabajador, 'empresa' => $empresa, 'anulado' => false));
                if(is_null($trabajadorEmpresa)){
                    $trabajadorEmpresa = new TrabajadorEmpresa();
                    $trabajadorEmpresa->setEmpresa($empresa);
                    $trabajadorEmpresa->setTrabajador($trabajador);
                    $trabajadorEmpresa->setAnulado(false);
                    $em->persist($trabajadorEmpresa);
                    $em->flush();
                }

                $trabajadorAltaBaja = $this->getDoctrine()->getRepository('App\Entity\TrabajadorAltaBaja')->findOneBy(array('trabajador' => $trabajador, 'empresa' => $empresa, 'anulado' => false));
                if(is_null($trabajadorAltaBaja)){
                    $trabajadorAltaBaja = new TrabajadorAltaBaja();
                    $trabajadorAltaBaja->setEmpresa($empresa);
                    $trabajadorAltaBaja->setTrabajador($trabajador);
                    $trabajadorAltaBaja->setAnulado(false);
                    $trabajadorAltaBaja->setActivo(true);
                    $trabajadorAltaBaja->setFechaAlta(new \DateTime());
                    $em->persist($trabajadorAltaBaja);
                    $em->flush();
                }
            }

	        $traduccion = $translator->trans('TRANS_CREATE_OK');
	        $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('trabajador_update', array('id' => $trabajador->getId()));
        }
        return $this->render( 'trabajador/edit.html.twig', array('listEmpresa' => null, 'altaBajaTrabajador' => null, 'centrosTrabajoTrabajador' => null, 'form' => $form->createView(), 'trabajadorNuevoSn' => true) );
    }

    public function viewTrabajador($id)
    {
	    $trabajador = $this->getDoctrine()->getRepository('App\Entity\Trabajador')->find($id);

        if (!$trabajador) {
	        throw $this->createNotFoundException(
		        'El trabajador con id: ' . $id.' no existe'
	        );
        }

        return $this->render( 'trabajador/view.html.twig', array('article' => $trabajador) );
    }

    public function showTrabajadores(Request $request, TranslatorInterface $translator)
    {
	    $em = $this->getDoctrine()->getManager();

	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getTrabajadorSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

	    $arrayEmpresaId = array();
	    $empresa = $session->get('empresa');
	    if(!is_null($empresa)){
		    $empresaId = $empresa->getId();
		    $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
		    array_push($arrayEmpresaId, $empresaId);
	    }else{
		    $empresas = $em->getRepository('App\Entity\Empresa')->findBy(array('anulado' => false));
		    foreach ($empresas as $e){
			    array_push($arrayEmpresaId, $e->getId());
		    }
	    }

	    $form = $this->createForm(TrabajadorImportType::class, null, array('empresaId' => $arrayEmpresaId, 'empresaObj' => $empresa));
	    $form->handleRequest($request);

	    if ($form->isSubmitted()) {

	    	//Recogemos la empresa activa
		    $empresaId = $form->get('empresa')->getData();
		    $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

		    //Recogemos el fichero a importar
		    $fichero = $form->get('fichero')->getData();
		    $extension = $fichero->getClientOriginalExtension();

		    if($extension != 'xlsx'){
			    $traduccion = $translator->trans('TRANS_ERROR_EXTENSION');
			    $this->addFlash('danger',  $traduccion);
		    }else{

			    $sheetDataNombre = null;
			    $dniTrabajador = null;
			    $altaTrabajador = null;
		    	$start = 16;
		    	//Cargamos el fichero a importar
			    $spreadsheet = IOFactory::load($fichero);

			    while($sheetDataNombre != '* FIN DE INFORME *'){
				    //Seleccionamos del Excel donde comienza la lista de trabajadores
				    $sheetDataNombre = $spreadsheet->getActiveSheet()->getCell('A'.$start);
				    $sheetDataDNI = $spreadsheet->getActiveSheet()->getCell('M'.$start);

				    //Recogemos los datos
				    $nombreTrabajador = $sheetDataNombre->getValue();
				    $dniTrabajador = $sheetDataDNI->getValue();

				    if($nombreTrabajador != "" && !is_null($nombreTrabajador) && $nombreTrabajador != "* FIN DE INFORME *" && $dniTrabajador != "" && !is_null($dniTrabajador)){
                        $dniTrabajador = trim($dniTrabajador);

					    //Comprobamos que el DNI no está dado ya de alta
					    $trabajador = $em->getRepository('App\Entity\Trabajador')->findOneBy(array('dni' => $dniTrabajador));

					    $trabajadorEmpresa = new TrabajadorEmpresa();
					    $trabajadorEmpresa->setEmpresa($empresa);
					    $trabajadorEmpresa->setAnulado(false);

					    //Si el dni no existe creamos el trabajador
					    if(is_null($trabajador)){
					        $fechaActual = new \DateTime();
					    	$trabajador = new Trabajador();
					    	$trabajador->setNombre($nombreTrabajador);
					    	$trabajador->setDni($dniTrabajador);
					    	$trabajador->setObservaciones('Creado automáticamente desde la importación de trabajadores '.$fechaActual->format('d-m-Y H:i:s'));
					    	$em->persist($trabajador);

						    $trabajadorEmpresa->setTrabajador($trabajador);
					    }else{
						    //Comprobamos si el trabajador está dado de alta en otra empresa.
						    $trabajadorEmpresaOld = $em->getRepository('App\Entity\TrabajadorEmpresa')->findBy(array('trabajador' => $trabajador));

						    foreach ($trabajadorEmpresaOld as $teo){
							    $teo->setAnulado(true);
							    $em->persist($teo);
							    $em->flush();
						    }

						    //Damos de baja el trabajador
						    $baja = $em->getRepository('App\Entity\TrabajadorAltaBaja')->findBy(array('trabajador' => $trabajador, 'anulado' => false, 'activo' => true, 'fechaBaja' => null));
						    foreach ($baja as $b){
								$b->setFechaBaja(new \DateTime());
							    $em->persist($b);
							    $em->flush();
						    }

						    $trabajadorEmpresa->setTrabajador($trabajador);
					    }

					    //Creamos el alta
					    $alta = new TrabajadorAltaBaja();
					    $alta->setTrabajador($trabajador);
					    $alta->setEmpresa($empresa);
					    $alta->setFechaAlta(new \DateTime());
					    $alta->setActivo(true);
					    $alta->setAnulado(false);

					    $em->persist($alta);
					    $em->persist($trabajadorEmpresa);
					    $em->flush();

					    $start++;
				    }
			    }

			    $traduccion = $translator->trans('TRANS_IMPORTACION_OK');
			    $this->addFlash('success',  $traduccion);
		    }

		    return $this->redirectToRoute('trabajador_show');
	    }

	    $trabajadores = $this->getDoctrine()->getRepository('App\Entity\Trabajador')->findBy(array('anulado' => false, 'historicoPrevenet' => false));

        $object = array("json"=>$username, "entidad"=>"trabajadores", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('trabajador/show.html.twig', array('form' => $form->createView(), 'trabajadores' => $trabajadores) );
    }

    public function deleteTrabajador($id, Request $request, TranslatorInterface $translator, $tipo)
    {
        $em = $this->getDoctrine()->getManager();

	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getDeleteTrabajadorSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

	    $trabajador = $em->getRepository('App\Entity\Trabajador')->find($id);

        if (!$trabajador) {
	        throw $this->createNotFoundException(
		        'El trabajador con id: ' . $id.' no existe'
	        );
        }

	    //Buscamos si el trabajador está en alguna empresa
	    $trabajadores = $em->getRepository('App\Entity\TrabajadorEmpresa')->findBy(array('trabajador' => $trabajador, 'anulado' => false));

	    foreach ($trabajadores as $trabajadorE){
		    $trabajadorE->setAnulado(true);
		    $em->persist($trabajadorE);
		    $em->flush();
	    }

        //Buscamos si el trabajador está activo en alguna empresa
        $trabajadorAltaBaja = $em->getRepository('App\Entity\TrabajadorAltaBaja')->findBy(array('trabajador' => $trabajador, 'anulado' => false));

        foreach ($trabajadorAltaBaja as $tab){
            $tab->setAnulado(true);
            $tab->setActivo(false);
            $em->persist($tab);
            $em->flush();
        }

	    $trabajador->setAnulado(true);
	    $em->persist($trabajador);
        $em->flush();

        $session->set('trabajadorId', null);

	    $traduccion = $translator->trans('TRANS_DELETE_OK');
	    $this->addFlash('success', $traduccion);

	    switch ($tipo){
            case 1:
                return $this->redirectToRoute('trabajador_show');
                break;
            case 2:
                return $this->redirectToRoute('tecnico_trabajador_show');
        }
    }

    public function updateTrabajador(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getEditTrabajadorSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

        $trabajador = $em->getRepository('App\Entity\Trabajador')->find($id);

        if (!$trabajador) {
            throw $this->createNotFoundException(
                'El trabajador con id: ' . $id.' no existe'
            );
        }

	    //Buscamos los centro de trabajo del trabajador
	    $query = "select distinct b.id, e.nombre from trabajador a inner join trabajador_empresa b ON a.id = b.trabajador_id inner join centro_trabajo_empresa c on b.empresa_id = c.empresa_id inner join empresa d on c.empresa_id = d.id inner join centro e on c.centro_id = e.id where a.anulado = false and b.anulado = false and c.anulado = false and d.anulado = false and e.anulado = false and a.id = $id";
	    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
	    $stmt->execute();
	    $centrosTrabajoTrabajador = $stmt->fetchAll();

	    //Buscamos las altas y bajas del trabajador
	    $altaBajaTrabajador = $em->getRepository('App\Entity\TrabajadorAltaBaja')->findBy(array('trabajador' => $trabajador, 'anulado' => false));

	    //Buscamos todas las empresas no anuladas
	    $empresas = $em->getRepository('App\Entity\Empresa')->findAll();

	    $form = $this->createForm(TrabajadorType::class, $trabajador);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
	        $trabajador = $form->getData();
	        $em->persist($trabajador);
            $em->flush();

	        $traduccion = $translator->trans('TRANS_UPDATE_OK');
	        $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('trabajador_update', array('id' => $trabajador->getId()));
        }

        return $this->render('trabajador/edit.html.twig',  array('listEmpresa' => $empresas, 'altaBajaTrabajador' => $altaBajaTrabajador, 'centrosTrabajoTrabajador' => $centrosTrabajoTrabajador, 'form' => $form->createView()) );
    }

    public function showTrabajadoresMedico(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getTrabajadorMedicoSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $object = array("json"=>$username, "entidad"=>"trabajadores médico", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('trabajadormedico/show.html.twig' );
    }

    public function dataTrabajadoresMedico(Request $request, TranslatorInterface $translator){

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

        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');


        $dql = "SELECT a.id as id, trim(a.nombre) as nombre, a.dni as dni, c.id as empresaid, c.empresa as empresa
        FROM App\Entity\Trabajador a 
        INNER JOIN a.empresas b
        LEFT JOIN b.empresa c 
        WHERE b.activo = true and b.fechaBaja IS NULL and b.anulado = false ";

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
         * Agrupaciones
         *
         * */
        $groupBy = "GROUP BY a.id, a.nombre, a.dni, c.id, c.empresa ";
        $dql .= $groupBy;


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


        $arrayTrabajadores = array();

        foreach ($paginator as $r) {

            $item = array();
            $actions = '<div class="list-icons">';

            if (!is_null($privilegios)) {

                if ($privilegios->getEditTrabajadorMedicoSn()) {
                    $route = $this->generateUrl('medico_trabajador_update', array('id' => $r['id']));
                    $actions .= '<a href="' . $route . '" class="list-icons-item" data-popup="tooltip" title="Editar" data-container="body"><i class="icon-pencil7"></i></a>';
                }

                if ($privilegios->getHistorialLaboralSn()) {
                    $route = $this->generateUrl('medico_trabajador_historial_laboral', array('id' => $r['id']));
                    $actions .= '<a href="' . $route . '" class="list-icons-item" data-popup="tooltip" title="Historial laboral" data-container="body"><i class="fas fa-h-square"></i></a>';
                }

                if ($privilegios->getInvestigacionSn()) {
                    $route = $this->generateUrl('medico_trabajador_update', array('id' => $r['id']));
                    $actions .= '<a href="#" class="list-icons-item" data-popup="tooltip" title="Investigaciones" data-container="body"><i class="icon-search4"></i></a>';
                }


            }
            $actions .= '</div>';


            $item['actions'] = $actions;
            $item['nombre'] = $r['nombre'];
            $item['dni'] = $r['dni'];
            $item['empresa'] = $r['empresa'];

            $empresaId = $r['empresaid'];
            $trabajadorId = $r['id'];

            if(!is_null($empresaId)){
                $query = "select string_agg(b.descripcion::text, ' , '::text) as puesto from puesto_trabajo_trabajador a
                inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                inner join empresa c on b.empresa_id = c.id
                where a.anulado = false
                and b.anulado = false 
                and a.trabajador_id = $trabajadorId 
                and c.id = $empresaId";

                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $puestoTrabajo = $stmt->fetchAll();

                if(count($puestoTrabajo) > 0){
                    $item['puesto'] = $puestoTrabajo[0]['puesto'];
                }else{
                    $item['puesto'] = '';
                }
            }else{
                $item['puesto'] = '';
            }

            array_push($arrayTrabajadores, $item);
        }

        return $this->json([
            'draw' => $draw,
            'recordsTotal' => $length,
            'recordsFiltered' => $recordsTotal,
            'data' => $arrayTrabajadores,
            'dql' => $dql,
            'dqlCountFiltered' => '',
        ]);
    }

    public function updateTrabajadorMedico(Request $request, $id, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getEditTrabajadorMedicoSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $usuario = $em->getRepository('App\Entity\User')->findForSecurityUser($user);

        $trabajador = $em->getRepository('App\Entity\Trabajador')->find($id);
        $session->set('trabajadorId', $id);

        if (!$trabajador) {
            throw $this->createNotFoundException(
                'El trabajador con id: ' . $id.' no existe'
            );
        }

        //Buscamos las altas y bajas del trabajador
        $altaBajaTrabajador = $em->getRepository('App\Entity\TrabajadorAltaBaja')->findBy(array('trabajador' => $trabajador, 'anulado' => false));

        //Buscamos las evaluaciones del trabajador
        $query = "select c.id, to_char(c.fecha_inicio, 'DD/MM/YYYY') as fechainicio, to_char(c.fecha_fin, 'DD/MM/YYYY') as fechafin, c.descripcion, d.empresa, h.nombre as centro, e.descripcion as metodologia, f.descripcion tipoevaluacion, b.id as puestotrabajoevaluarid, i.descripcion puestotrabajo from puesto_trabajo_trabajador a 
            inner join puesto_trabajo_evaluacion b on a.puesto_trabajo_id = b.puesto_trabajo_id 
            inner join evaluacion c on b.evaluacion_id = c.id
            inner join empresa d on c.empresa_id = d.id
            inner join metodologia_evaluacion e on c.metodologia_id = e.id
            inner join tipo_evaluacion f on c.tipo_evaluacion_id = f.id
            inner join evaluacion_centro_trabajo g on c.id = g.evaluacion_id
            inner join centro h on g.centro_id = h.id
            inner join puesto_trabajo_centro i on b.puesto_trabajo_id = i.id
            where a.anulado = false
            and a.trabajador_id = $id
            and b.anulado = false
            and c.anulado = false
            and d.anulado = false
            order by c.fecha_inicio desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $evaluaciones = $stmt->fetchAll();

        //Buscamos la gestión documental del trabajador
        $query = "select * from gdoc_trabajador_carpeta where anulado = false and trabajador_id = $id";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $carpetas = $stmt->fetchAll();

        $data = array();
        foreach ($carpetas as $carpeta) {
            $row = array();
            $row['id'] = $carpeta['id'] . "";
            if (is_null($carpeta['padre_id'])) {
                $row['parent'] = "#";
            } else {
                $row['parent'] = $carpeta['padre_id'] . "";
            }

            $row['text'] = $carpeta['nombre'];
            $row['icon'] = "icon-folder";
            array_push($data, $row);
        }

        //Recuperamos los ficheros de las carpetas
        $query = "select * from gdoc_trabajador where anulado = false and trabajador_id = $id";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $ficheros = $stmt->fetchAll();

        foreach ($ficheros as $fichero) {
            $row = array();
            $row['id'] = "fileId" . $fichero['id'];

            if (is_null($fichero['carpeta_id'])) {
                $row['parent'] = "#";
            } else {
                $row['parent'] = $fichero['carpeta_id'] . "";
            }

            $row['text'] = $fichero['nombre'];
            $row['icon'] = "icon-file-word";

            array_push($data, $row);
        }

        $data = \json_encode($data);
        //$mediaMediaRepo = $this->getDoctrine()->getRepository('App\Application\Sonata\MediaBundle\Entity\Media');
        $listCarpetas = $em->getRepository('App\Entity\GdocTrabajadorCarpeta')->findBy(array('anulado' => false, 'trabajador' => $trabajador));

        //Buscamos las enfermedades del trabajador
        $enfermedadesTrabajador = $em->getRepository('App\Entity\TrabajadorEnfermedad')->findBy(array('trabajador' => $trabajador, 'anulado' => false));
        $listEnfermedades = $em->getRepository('App\Entity\Enfermedad')->findBy(array('anulado' => false), array('descripcion' => 'ASC'));

        //Buscamos las revisiones del trabajador
        $revisionesTrabajador = $em->getRepository('App\Entity\Revision')->findBy(array('trabajador' => $trabajador, 'anulado' => false), array('fecha' => 'DESC'));

        $form = $this->createForm(TrabajadorMedicoType::class, $trabajador);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $trabajador = $form->getData();
            $em->persist($trabajador);
            $em->flush();

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('medico_trabajador_update', array('id' => $trabajador->getId()));
        }

        $ficheroTrabajador = new GdocTrabajador();
        $form2 = $this->createForm(GdocTrabajadorType::class, $ficheroTrabajador, array('trabajadorId' => $id));

        $form2->handleRequest($request);
        if ($form2->isSubmitted()) {
            $ficheroTrabajador = $form2->getData();

            //Buscamos la configuración de la gestión documental
            $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
            $rutaCompleta = $gdocConfig->getRuta().$gdocConfig->getCarpetaTrabajador().'/'.$id;

            if(!is_dir($rutaCompleta)){
                mkdir($rutaCompleta);
            }

            $fichero = $form2->get('media')->getData();
            $nombreFichero = $fichero->getClientOriginalName();
            $fichero->move($rutaCompleta, $nombreFichero);

            $ficheroTrabajador->setNombreCompleto($nombreFichero);
            $ficheroTrabajador->setUsuario($usuario);
            $ficheroTrabajador->setDtcrea(new \DateTime());
            $ficheroTrabajador->setAnulado(false);
            $ficheroTrabajador->setMedia(null);
            $ficheroTrabajador->setTrabajador($trabajador);

            //Comprobamos si el fichero ya estaba subido
            $ficheroBusca = $em->getRepository('App\Entity\GdocTrabajador')->findOneBy(array('nombreCompleto' => $nombreFichero, 'anulado' => false));
            if(!is_null($ficheroBusca)){
                $ficheroBusca->setUsuarioModifica($usuario);
                $ficheroBusca->setDtmodifica(new \DateTime());
                $em->persist($ficheroBusca);
                $em->remove($ficheroTrabajador);
                $em->flush();

            }else{
                $em->persist($ficheroTrabajador);
                $em->flush();
            }

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('medico_trabajador_update', array('id' => $id));
        }

        return $this->render('trabajadormedico/edit.html.twig',  array('listCarpetas' => $listCarpetas, 'tree' => $data, 'form' => $form->createView(), 'form2' => $form2->createView(), 'altaBajaTrabajador' => $altaBajaTrabajador, 'evaluaciones' => $evaluaciones, 'enfermedades' => $enfermedadesTrabajador, 'listEnfermedades' => $listEnfermedades, 'revisiones' => $revisionesTrabajador));
    }

    public function showTrabajadoresTecnico(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getTrabajadorTecnicoSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $empresas = $em->getRepository('App\Entity\Empresa')->findAll();
        $object = array("json"=>$username, "entidad"=>"trabajadores técnico", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('trabajadortecnico/show.html.twig', array('listEmpresa' => $empresas));
    }

    public function dataTrabajadoresTecnico(Request $request, TranslatorInterface $translator){

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

        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');

        $dql = "SELECT a.id as id, trim(a.nombre) as nombre, a.dni as dni, c.id as empresaid, c.empresa as empresa, a.discapacidad as discapacidad
        FROM App\Entity\Trabajador a 
        INNER JOIN a.empresas b
        LEFT JOIN b.empresa c 
        WHERE b.activo = true and b.fechaBaja IS NULL and b.anulado = false ";

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
         * Agrupaciones
         *
         * */
        $groupBy = "GROUP BY a.id, a.nombre, a.dni, c.id, c.empresa, a.discapacidad ";
        $dql .= $groupBy;

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


        $arrayTrabajadores = array();

        foreach ($paginator as $r) {
            //Petició 28/07/2023
            $trabajador = $em->getRepository('App\Entity\Trabajador')->find($r['id']);
            $altaBajaTrabajador = $em->getRepository('App\Entity\TrabajadorAltaBaja')->findOneBy(array('trabajador' => $trabajador, 'anulado' => false));

            $item = array();
            $actions = '<div class="list-icons">';

            if (!is_null($privilegios)) {

                if ($privilegios->getEditTrabajadorTecnicoSn()) {
                    $route = $this->generateUrl('tecnico_trabajador_update', array('id' => $r['id']));
                    $actions .= '<a href="' . $route . '" target="_blank" class="list-icons-item" data-popup="tooltip" title="Editar" data-container="body"><i class="icon-pencil7"></i></a>';
                }
                //Petició 28/07/2023
                if ($privilegios->getEditTrabajadorTecnicoSn()) {

                    $actions .= '<a href="#" class="list-icons-item" data-toggle="modal" onclick="recuperaAltaBaja('.$altaBajaTrabajador ->getId().')" title="Altas/Bajas" data-target="#modal_editar_alta_baja"><i class="fa fa-user"></i></a>';

                }
                if ($privilegios->getDeleteTrabajadorTecnicoSn()) {
                    $route = $this->generateUrl('trabajador_delete', array('id' => $r['id'], 'tipo' => 2));
                    $msgEliminar = $translator->trans('TRANS_CONFIRM_DELETE_REGISTRO');
                    $actions .= '<a href="' . $route . '"class="list-icons-item" data-popup="tooltip" title="Eliminar" data-container="body" onclick="return confirm(\'' . $msgEliminar . '\');"><i class="icon-trash"></i></a>';
                }

            }
            //Petició 28/07/2023
            $actions .= '</div>

            <script>
            var pathArray = window.location.pathname.split(\'tecnico/trabajadores\').join(\'\') + "recuperaAltaBaja";

            console.log(pathArray);
                route = window.location.href;
                function recuperaAltaBaja(id) {
                    console.log(id);
                    $.ajax({
                        type: "POST",
                        url: pathArray,
                        data: {
                            \'altaBajaId\': id
                        },
                        dataType: "JSON",
                        success: function (data) {
                            if (data != null) {
                                $(\'#empresaEditar\').val(data.empresa).change();
                                $(\'#fechaAltaEditar\').val(data.fechaAlta);
                                $(\'#fechaBajaEditar\').val(data.fechaBaja);
                                $(\'#motivoBajaEditar\').val(data.motivoBaja);
                                $(\'#altaBajaId\').val(data.id);
                            }
                        },
                        error: function () {
                            new PNotify({
                                title: \'Ops!\',
                                text: \'{% trans from "messages" %}TRANS_AVISO_ERROR{% endtrans %}\',
                                icon: \'icon-blocked\',
                                type: \'error\'
                            });
                        }
                    });
                }
            </script>';

            $sensible = '<span class="badge badge-flat border-danger text-danger-600"><i class="icon-cross3"></i></span>';
            if ($r['discapacidad'] == true) {
                $sensible = '<span class="badge badge-flat border-success text-success-600"><i class="icon-checkmark2"></i></span>';
            }

            $item['actions'] = $actions;
            $item['nombre'] = $r['nombre'];
            $item['dni'] = $r['dni'];
            $item['empresa'] = $r['empresa'];
            $item['sensible'] = $sensible;

            $empresaId = $r['empresaid'];
            $trabajadorId = $r['id'];

            if(!is_null($empresaId)){
                $query = "select string_agg(b.descripcion::text, ' , '::text) as puesto from puesto_trabajo_trabajador a
                inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                inner join empresa c on b.empresa_id = c.id
                where a.anulado = false
                and b.anulado = false 
                and a.trabajador_id = $trabajadorId 
                and c.id = $empresaId";

                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $puestoTrabajo = $stmt->fetchAll();

                if(count($puestoTrabajo) > 0){
                    $item['puesto'] = $puestoTrabajo[0]['puesto'];
                }else{
                    $item['puesto'] = '';
                }
            }else{
                $item['puesto'] = '';
            }

            array_push($arrayTrabajadores, $item);
        }

        return $this->json([
            'draw' => $draw,
            'recordsTotal' => $length,
            'recordsFiltered' => $recordsTotal,
            'data' => $arrayTrabajadores,
            'dql' => $dql,
            'dqlCountFiltered' => '',
        ]);

    }

    public function createTrabajadorTecnico(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getAddTrabajadorTecnicoSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $session->set('trabajadorTecnico', null);

        $empresa = $session->get('empresa');
        if(!is_null($empresa)){
            $empresaId = $empresa->getId();
            $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
        }

        $trabajador = new Trabajador();

        $form = $this->createForm(TrabajadorTecnicoType::class, $trabajador);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $trabajador = $form->getData();
            $em->persist($trabajador);
            $em->flush();

            //Asignamos el trabajador a la empresa
            if(!is_null($empresa)){
                $empresa = $this->getDoctrine()->getRepository('App\Entity\Empresa')->find($empresa->getId());
                $trabajadorEmpresa = $this->getDoctrine()->getRepository('App\Entity\TrabajadorEmpresa')->findOneBy(array('trabajador' => $trabajador, 'empresa' => $empresa, 'anulado' => false));
                if(is_null($trabajadorEmpresa)){
                    $trabajadorEmpresa = new TrabajadorEmpresa();
                    $trabajadorEmpresa->setEmpresa($empresa);
                    $trabajadorEmpresa->setTrabajador($trabajador);
                    $trabajadorEmpresa->setAnulado(false);
                    $em->persist($trabajadorEmpresa);
                    $em->flush();
                }

                $trabajadorAltaBaja = $this->getDoctrine()->getRepository('App\Entity\TrabajadorAltaBaja')->findOneBy(array('trabajador' => $trabajador, 'empresa' => $empresa, 'anulado' => false));
                if(is_null($trabajadorAltaBaja)){
                    $trabajadorAltaBaja = new TrabajadorAltaBaja();
                    $trabajadorAltaBaja->setEmpresa($empresa);
                    $trabajadorAltaBaja->setTrabajador($trabajador);
                    $trabajadorAltaBaja->setAnulado(false);
                    $trabajadorAltaBaja->setActivo(true);
                    $trabajadorAltaBaja->setFechaAlta(new \DateTime());
                    $em->persist($trabajadorAltaBaja);
                    $em->flush();
                }
            }

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('tecnico_trabajador_update', array('id' => $trabajador->getId()));
        }

        return $this->render('trabajadortecnico/edit.html.twig',  array( 'form' => $form->createView(), 'centrosTrabajoTrabajador' => null, 'trabajadorNuevoSn' => true, 'altaBajaTrabajador' => null, 'listEmpresa' => null, 'puestoTrabajoTrabajador' => null));
    }

    public function updateTrabajadorTecnico(Request $request, $id, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getEditTrabajadorTecnicoSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $session->set('trabajadorTecnico', $id);
        $session->set('puestoTrabajoEvaluacionId', null);

        $trabajador = $em->getRepository('App\Entity\Trabajador')->find($id);

        if (!$trabajador) {
            throw $this->createNotFoundException(
                'El trabajador con id: ' . $id.' no existe'
            );
        }

        $query = "select distinct b.id, e.nombre from trabajador a inner join trabajador_empresa b ON a.id = b.trabajador_id inner join centro_trabajo_empresa c on b.empresa_id = c.empresa_id inner join empresa d on c.empresa_id = d.id inner join centro e on c.centro_id = e.id where a.anulado = false and b.anulado = false and c.anulado = false and d.anulado = false and e.anulado = false and a.id = $id";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $centrosTrabajoTrabajador = $stmt->fetchAll();

        //Buscamos las altas y bajas del trabajador
        $altaBajaTrabajador = $em->getRepository('App\Entity\TrabajadorAltaBaja')->findBy(array('trabajador' => $trabajador, 'anulado' => false));

        //Buscamos todas las empresas no anuladas
        $empresas = $em->getRepository('App\Entity\Empresa')->findAll();

        //Buscamos la empresa donde esta dado de alta el trabajador
        $query = " select a.id, d.descripcion as puesto, to_char(a.fecha_alta, 'DD/MM/YYYY') as fecha_alta, to_char(a.fecha_baja, 'DD/MM/YYYY') as fecha_baja, e.empresa, e.id as empresaid from puesto_trabajo_trabajador a
             inner join trabajador b on a.trabajador_id = b.id
             left join centro_trabajo_empresa c on a.centro_id = c.centro_id 
             inner join puesto_trabajo_centro d on a.puesto_trabajo_id = d.id
             inner join empresa e on d.empresa_id = e.id
             where a.anulado = false
             and b.anulado = false
             and a.trabajador_id = $id
             order by a.fecha_alta desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $puestoTrabajoTrabajador = $stmt->fetchAll();

        $form = $this->createForm(TrabajadorTecnicoType::class, $trabajador);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $trabajador = $form->getData();
            $em->persist($trabajador);
            $em->flush();

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('tecnico_trabajador_update', array('id' => $trabajador->getId()));
        }

        return $this->render('trabajadortecnico/edit.html.twig',  array( 'form' => $form->createView(), 'centrosTrabajoTrabajador' => $centrosTrabajoTrabajador, 'altaBajaTrabajador' => $altaBajaTrabajador, 'listEmpresa' => $empresas, 'puestoTrabajoTrabajador' => $puestoTrabajoTrabajador));
    }

	public function addCentroTrabajoTrabajador(Request $request, TranslatorInterface $translator){
		$em = $this->getDoctrine()->getManager();
		$session = $request->getSession();

		$centroTrabajoId = $_REQUEST['centroTrabajoId'];
		$trabajadorId = $_REQUEST['trabajadorId'];

		$centro = $em->getRepository('App\Entity\Centro')->find($centroTrabajoId);
		$trabajador = $em->getRepository('App\Entity\Trabajador')->find($trabajadorId);

		$centroEmpresa = $em->getRepository('App\Entity\CentroTrabajoEmpresa')->findOneBy(array('centro' => $centro, 'anulado' => false));

		$centroTrabajoTrabajador = new TrabajadorEmpresa();
		$centroTrabajoTrabajador->setEmpresa($centroEmpresa->getEmpresa());
		$centroTrabajoTrabajador->setTrabajador($trabajador);
		$em->persist($centroTrabajoTrabajador);
		$em->flush();

		$traduccion = $translator->trans('TRANS_CREATE_OK');
		$this->addFlash('success', $traduccion);

		$data = array();
		array_push($data, "OK");

		return new JsonResponse($data);
	}

	public function deleteCentroTrabajoTrabajador(Request $request, TranslatorInterface $translator){
		$em = $this->getDoctrine()->getManager();
		$session = $request->getSession();

		$trabajadorEmpresaId = $_REQUEST['trabajadorEmpresaId'];
		$trabajadorEmpresa = $em->getRepository('App\Entity\TrabajadorEmpresa')->find($trabajadorEmpresaId);

		$trabajadorEmpresa->setAnulado(true);
		$em->persist($trabajadorEmpresa);
		$em->flush();

		$traduccion = $translator->trans('TRANS_DELETE_OK');
		$this->addFlash('success', $traduccion);

		$data = array();
		array_push($data, "OK");

		return new JsonResponse($data);
	}

	public function recuperaAltaBaja(Request $request){
		$em = $this->getDoctrine()->getManager();
		$session = $request->getSession();

		$altaBajaId = $_REQUEST['altaBajaId'];
		$altaBaja = $em->getRepository('App\Entity\TrabajadorAltaBaja')->find($altaBajaId);

		$fechaAlta = null;
		$fechaBaja = null;

        if(!is_null($altaBaja->getFechaAlta())){
            $fechaAlta = $altaBaja->getFechaAlta()->format('Y-m-d');
        }

		if(!is_null($altaBaja->getFechaBaja())){
			$fechaBaja = $altaBaja->getFechaBaja()->format('Y-m-d');
		}

		$data = array(
			'empresa' => $altaBaja->getEmpresa()->getId(),
			'fechaAlta' => $fechaAlta,
			'fechaBaja' => $fechaBaja,
			'motivoBaja' => $altaBaja->getMotivoBaja(),
			'id' => $altaBaja->getId()
		);

		return new JsonResponse($data);
	}

	public function updateAltaBaja(Request $request, TranslatorInterface $translator){
		$em = $this->getDoctrine()->getManager();
		$session = $request->getSession();

		$altaBajaId = $_REQUEST['altaBajaId'];
		$altaBaja = $em->getRepository('App\Entity\TrabajadorAltaBaja')->find($altaBajaId);

		$empresaId = $_REQUEST['empresaId'];
		$empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

		$centroTrabajo = $em->getRepository('App\Entity\Centro')->findBy(array('empresa' => $empresa));

		$centroTrabajoIdArray = array();
		foreach ($centroTrabajo as $ct){
		    array_push($centroTrabajoIdArray, $ct->getId());
        }

		$fechaAlta = $_REQUEST['fechaAlta'];
		$fechaAlta = new \DateTime($fechaAlta);

		$fechaBaja = $_REQUEST['fechaBaja'];
		$motivoBaja = $_REQUEST['motivoBaja'];

		$altaBaja->setFechaAlta($fechaAlta);
		$altaBaja->setMotivoBaja($motivoBaja);
		$altaBaja->setEmpresa($empresa);

		$trabajadorId = $altaBaja->getTrabajador()->getId();

		if($fechaBaja != "" && !is_null($fechaBaja)){
			$fechaBaja = new \DateTime($fechaBaja);
			$altaBaja->setFechaBaja($fechaBaja);
			$altaBaja->setActivo(false);

			//Damos de baja al trabajador del puesto de trabajo
            if(count($centroTrabajoIdArray) > 0){
                $centrosId = implode(',', $centroTrabajoIdArray);
                $query = "select id from puesto_trabajo_trabajador where anulado = false and trabajador_id = $trabajadorId and (empresa_id = $empresaId or centro_id in ($centrosId))";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $puestoTrabajoTrabajador = $stmt->fetchAll();
                foreach ($puestoTrabajoTrabajador as $ptt){
                    $puestoTrabajoTrabajadorAnular = $em->getRepository('App\Entity\PuestoTrabajoTrabajador')->find($ptt['id']);
                    $puestoTrabajoTrabajadorAnular->setFechaBaja($fechaBaja);
                    $em->persist($puestoTrabajoTrabajadorAnular);
                }
            }
		}else{
			$altaBaja->setFechaBaja(null);
			$altaBaja->setActivo(true);
		}

		$em->persist($altaBaja);
		$em->flush();

		$traduccion = $translator->trans('TRANS_UPDATE_OK');
		$this->addFlash('success', $traduccion);

		$data = array();
		array_push($data, "OK");

		return new JsonResponse($data);
	}

	public function deleteAltaBaja(Request $request, TranslatorInterface $translator){
		$em = $this->getDoctrine()->getManager();

		$altaBajaId = $_REQUEST['altaBajaId'];
		$altaBaja = $em->getRepository('App\Entity\TrabajadorAltaBaja')->find($altaBajaId);

		$altaBaja->setAnulado(true);
		$em->persist($altaBaja);
		$em->flush();

		$traduccion = $translator->trans('TRANS_DELETE_OK');
		$this->addFlash('success', $traduccion);

		$data = array();
		array_push($data, "OK");

		return new JsonResponse($data);
	}

	public function historialLaboral(Request $request, $id){
        $em = $this->getDoctrine()->getManager();

        $trabajador = $em->getRepository('App\Entity\Trabajador')->find($id);
        $alta = $em->getRepository('App\Entity\TrabajadorAltaBaja')->findBy(array('trabajador' => $trabajador, 'anulado' => false, 'activo' => true));

        $fechaAlta = "";
        $puestoTrabajo = "";

        foreach ($alta as $a){
            if(!is_null($a->getEmpresa())){
                $empresaId = $a->getEmpresa()->getId();
                $query = "select d.descripcion as puesto, to_char(c.fecha_alta , 'DD/MM/YYYY') as alta from trabajador_alta_baja a
                    inner join trabajador b on a.trabajador_id = b.id
                    inner join puesto_trabajo_trabajador c on b.id = c.trabajador_id
                    inner join puesto_trabajo_centro d on c.puesto_trabajo_id = d.id
                    where a.trabajador_id = $id
                    and a.anulado = false
                    and a.activo = true
                    and c.trabajador_id = $id
                    and c.centro_id in (select centro_id from centro_trabajo_empresa where empresa_id = $empresaId)";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $puestoTrabajoTrabajador = $stmt->fetchAll();

                if(count($puestoTrabajoTrabajador) > 0){
                    $puestoTrabajo = $puestoTrabajoTrabajador[0]['puesto'];
                    $fechaAlta = $puestoTrabajoTrabajador[0]['alta'];
                }
            }
        }


        return $this->render('trabajadormedico/historialLaboral.html.twig',  array( 'trabajador' => $trabajador, 'puestoTrabajo' => $puestoTrabajo, 'fechaAlta' => $fechaAlta));
    }

    public function puestoTrabajoEvaluar(Request $request, $id, TranslatorInterface $translator){

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getEditEvaluacionSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        //Recuperamos el trabajador
        $trabajadorId = $session->get('trabajadorId');
        $trabajador = $this->getDoctrine()->getRepository('App\Entity\Trabajador')->find($trabajadorId);

        $puestoTrabajoEvaluacion = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoEvaluacion')->find($id);

        $fechaEvaluacion = "";
        if(!is_null($puestoTrabajoEvaluacion->getEvaluacion()->getFechaInicio())){
            $fechaEvaluacion = $puestoTrabajoEvaluacion->getEvaluacion()->getFechaInicio()->format('d/m/Y');
        }

        $puestoTrabajoDesc = $puestoTrabajoEvaluacion->getPuestoTrabajo()->getDescripcion();

        //Buscamos los riesgos-casuas del puesto
        $riesgosCausasPuestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\RiesgoCausaEvaluacion')->findBy(array('evaluacion' => $puestoTrabajoEvaluacion->getEvaluacion(), 'puestoTrabajo' => $puestoTrabajoEvaluacion->getPuestoTrabajo(), 'anulado' => false));

        //Buscamos los trabajadores del puesto de trabajo
        $trabajadoresPuestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoTrabajador')->findBy(array('puestoTrabajo' => $puestoTrabajoEvaluacion->getPuestoTrabajo(), 'anulado' => false, 'trabajador' => $trabajador));

        $evaluacionId = $puestoTrabajoEvaluacion->getEvaluacion()->getId();

        $session->set('puestoTrabajoEvaluacionId', $puestoTrabajoEvaluacion->getId());
        $session->set('zonaTrabajoEvaluacionId', null);
        $session->set('evaluacionId', $evaluacionId);

        return $this->render('trabajadormedico/puestoTrabajoEvaluar.html.twig', array('trabajador' => $trabajador, 'trabajadoresPuestoTrabajo' => $trabajadoresPuestoTrabajo, 'evaluacionId' => $evaluacionId, 'desc' => $puestoTrabajoDesc, 'fechaEvaluacion' => $fechaEvaluacion, 'riesgosCausas' => $riesgosCausasPuestoTrabajo));
    }

    public function createPuestoTrabajoEvaluarRiesgoCausa(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getEditEvaluacionSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $session->set('riesgoCausaId', null);
        $riesgoCausa = new RiesgoCausaEvaluacion();
        $evaluacionId = $session->get('evaluacionId');
        $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($evaluacionId);
        $grupoRiesgo = $em->getRepository('App\Entity\GrupoRiesgo')->find(0);
        $tipoPlanificacion = $em->getRepository('App\Entity\TipoPlanificacion')->findOneBy(array('descripcion' => 'CORRECTORAS Y/O PREVENTIVAS'));

        $metodologia = $evaluacion->getMetodologia();
        if(!is_null($metodologia)){
            $metodologiaId = $metodologia->getId();
        }

        //Buscamos el responsable de la empresa
        $responsable = $evaluacion->getEmpresa()->getNombreRepresentante();

        $form = $this->createForm(RiesgoCausaType::class, $riesgoCausa, array('metodologia' => $metodologiaId, 'grupoRiesgoId' => $grupoRiesgo->getId(), 'grupoRiesgoObj' => $grupoRiesgo, 'trabajadorId' => null, 'tipoPlanificacion' => $tipoPlanificacion, 'fechaPrevista' => null, 'fechaRealizacion' => null, 'coste' => null, 'responsable' => $responsable));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $riesgoCausa = $form->getData();

            $puestoTrabajoEvaluacionId = $session->get('puestoTrabajoEvaluacionId');
            $zonaTrabajoEvaluacionId = $session->get('zonaTrabajoEvaluacionId');

            if(!is_null($puestoTrabajoEvaluacionId)){
                $puestoTrabajoEvaluacion = $em->getRepository('App\Entity\PuestoTrabajoEvaluacion')->find($puestoTrabajoEvaluacionId);
                $riesgoCausa->setPuestoTrabajo($puestoTrabajoEvaluacion->getPuestoTrabajo());
            }

            if(!is_null($zonaTrabajoEvaluacionId)){
                $zonaTrabajoEvaluacion = $em->getRepository('App\Entity\ZonaTrabajoEvaluacion')->find($zonaTrabajoEvaluacionId);
                $riesgoCausa->setZonaTrabajo($zonaTrabajoEvaluacion->getZonaTrabajo());
            }

            $riesgoCausa->setEvaluacion($evaluacion);
            $em->persist($riesgoCausa);
            $em->flush();

            //Guardamos la planificacion
            $fechaPrevista = $form["fechaPrevista"]->getData();
            $fechaRealizacion = $form["fechaRealizacion"]->getData();
            $coste = $form["costePrevisto"]->getData();
            $responsable = $form["responsable"]->getData();
            $tipoPlanificacion = $form["tipoPlanificacion"]->getData();

            $planificacionNew = new PlanificacionRiesgoCausa();
            $planificacionNew->setRiesgoCausa($riesgoCausa);
            $planificacionNew->setTipoPlanificacion($tipoPlanificacion);
            $planificacionNew->setFechaPrevista($fechaPrevista);
            $planificacionNew->setFechaRealizacion($fechaRealizacion);
            $planificacionNew->setCostePrevisto($coste);
            $planificacionNew->setResponsable($responsable);
            $em->persist($planificacionNew);
            $em->flush();

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('medico_trabajador_puesto_trabajo_riesgo_causa_update', array('id' => $riesgoCausa->getId()));
        }

        return $this->render('trabajadormedico/puestoTrabajoRiesgoCausa.html.twig', array('form' => $form->createView(), 'metodologiaId' => $metodologiaId));
    }

    public function updatePuestoTrabajoEvaluarRiesgoCausa(Request $request, $id, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getEditEvaluacionSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $riesgoCausa = $em->getRepository('App\Entity\RiesgoCausaEvaluacion')->find($id);
        $session->set('riesgoCausaId', $id);
        $grupoRiesgo = $riesgoCausa->getGrupoRiesgo();

        $metodologia = $riesgoCausa->getEvaluacion()->getMetodologia();
        if(!is_null($metodologia)){
            $metodologiaId = $metodologia->getId();
        }

        //Buscamos la planificacion
        $planificacion = $em->getRepository('App\Entity\PlanificacionRiesgoCausa')->findOneBy(array('riesgoCausa' => $riesgoCausa), array('fechaPrevista' => 'ASC'));
        $fechaPrevista = null;
        $fechaRealizacion = null;
        $coste = null;
        $responsable = null;
        $tipoPlanificacion = null;
        if(!is_null($planificacion)){
            $fechaPrevista = $planificacion->getFechaPrevista();
            $fechaRealizacion = $planificacion->getFechaRealizacion();
            $coste = $planificacion->getCostePrevisto();
            $responsable = $planificacion->getResponsable();
            $tipoPlanificacion = $planificacion->getTipoPlanificacion();
        }

        $form = $this->createForm(RiesgoCausaType::class, $riesgoCausa, array('metodologia' => $metodologiaId, 'grupoRiesgoId' => $grupoRiesgo->getId(), 'grupoRiesgoObj' => $grupoRiesgo, 'trabajadorId' => null, 'tipoPlanificacion' => $tipoPlanificacion, 'fechaPrevista' => $fechaPrevista, 'fechaRealizacion' => $fechaRealizacion, 'coste' => $coste, 'responsable' => $responsable));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $riesgoCausa = $form->getData();
            $em = $this->getDoctrine()->getManager();

            $em->persist($riesgoCausa);
            $em->flush();

            //Comprobamos si tiene planificacion sino la creamos
            $fechaPrevista = $form["fechaPrevista"]->getData();
            $fechaRealizacion = $form["fechaRealizacion"]->getData();
            $coste = $form["costePrevisto"]->getData();
            $responsable = $form["responsable"]->getData();
            $tipoPlanificacion = $form["tipoPlanificacion"]->getData();

            $planificacionUpdate = $em->getRepository('App\Entity\PlanificacionRiesgoCausa')->findOneBy(array('riesgoCausa' => $riesgoCausa));
            if(!is_null($planificacionUpdate)){
                $planificacionUpdate->setTipoPlanificacion($tipoPlanificacion);
                $planificacionUpdate->setFechaPrevista($fechaPrevista);
                $planificacionUpdate->setFechaRealizacion($fechaRealizacion);
                $planificacionUpdate->setCostePrevisto($coste);
                $planificacionUpdate->setResponsable($responsable);
                $em->persist($planificacionUpdate);
                $em->flush();
            }else{
                $planificacionNew = new PlanificacionRiesgoCausa();
                $planificacionNew->setRiesgoCausa($riesgoCausa);
                $planificacionNew->setTipoPlanificacion($tipoPlanificacion);
                $planificacionNew->setFechaPrevista($fechaPrevista);
                $planificacionNew->setFechaRealizacion($fechaRealizacion);
                $planificacionNew->setCostePrevisto($coste);
                $planificacionNew->setResponsable($responsable);
                $em->persist($planificacionNew);
                $em->flush();
            }

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('medico_trabajador_puesto_trabajo_riesgo_causa_update', array('id' => $riesgoCausa->getId()));
        }

        return $this->render('trabajadormedico/puestoTrabajoRiesgoCausa.html.twig', array('form' => $form->createView(), 'metodologiaId' => $metodologiaId));
    }

    public function comprobarDniTrabajador(Request $request){
        $em = $this->getDoctrine()->getManager();

        $dni = $_REQUEST['dni'];
        $trabajador = $em->getRepository('App\Entity\Trabajador')->findBy(array('anulado' => false, 'dni' => $dni));

        $success = 0;
        if(count($trabajador) > 0){
            $success = 1;
        }

        $data = array(
            'success' => $success,
        );

        return new JsonResponse($data);
    }

}