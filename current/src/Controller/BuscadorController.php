<?php

namespace App\Controller;

use App\Entity\BuscadorQueries;
use App\Entity\BuscadorQueriesVariable;
use App\Form\BuscadorType;
use App\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class BuscadorController extends AbstractController
{

	public function showBuscadores(Request $request){

		$em = $this->getDoctrine()->getManager();

		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getBuscadorSn()){
				return $this->redirectToRoute('error_403');
			}
		}

		$user = $this->getUser();
		$usuario = $this->getDoctrine()->getRepository('App\Entity\User')->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

		$consultasPropias = $em->getRepository('App\Entity\BuscadorQueries')->findBy(array('usuario' => $usuario, 'anulado' => false));
		$consultasCompartidas = $em->getRepository('App\Entity\BuscadorQueries')->findBy(array('anulado' => false, 'compartida' => true));

        $object = array("json"=>$username, "entidad"=>"buscadores", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

		return $this->render('buscador/show.html.twig',  array('consultasPropias' => $consultasPropias, 'consultasCompartidas' => $consultasCompartidas) );
	}

	public function createBuscador(Request $request){

		$em = $this->getDoctrine()->getManager();

		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getAddBuscadorSn()){
				return $this->redirectToRoute('error_403');
			}
		}

		$user = $this->getUser();
		$usuario = $this->getDoctrine()->getRepository('App\Entity\User')->findForSecurityUser($user);

		//Recuperamos las variables disponibles y generamos el array
		$buscadoresVariablesRepo = $this->getDoctrine()->getRepository('App\Entity\BuscadorVariable');
		$buscadoresVariables = $buscadoresVariablesRepo->findBy(array('anulado'=>false), array('alias' => 'ASC'));

		$arrayVariables = Array();
		foreach($buscadoresVariables as $variable){
			$id = $variable->getId();
			$tabla = $variable->getTabla()->getAlias();
			$campo = $variable->getCampo();
			$descripcion = $variable->getAlias();

			//Si no existe el array lo definimos.
			if (!isset($arrayVariables[$tabla])){
				$arrayVariables[$tabla] = array();
			}
			$arrayVariables[$tabla][$descripcion] = $id;
		}

		$query = new BuscadorQueries();
		$form = $this->createForm(BuscadorType::class, $query, array('variables' => $arrayVariables, 'variablesSeleccionadas' => null));
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$em = $this->getDoctrine()->getManager();

			$query = $form->getData();

			$query->setDtcrea(new \DateTime());
			$query->setAnulado(false);
			$query->setCompartida(false);
			$query->setUsuario($usuario);
			$em->persist($query);
			$em->flush();

			$variablesSeleccionadas = $form->get('variables')->getViewData();
			foreach ($variablesSeleccionadas as $var){
				$varObj = $buscadoresVariablesRepo->findOneBy(array('id' => $var, 'anulado'=>false));
				$statsQueryVars = new BuscadorQueriesVariable();
				$statsQueryVars->setQuery($query);
				$statsQueryVars->setVariable($varObj);
				$em->persist($statsQueryVars);
				$em->flush();
			}

			return $this->redirectToRoute('buscadores_condiciones', array('id' => $query->getId()));
		}

		return $this->render( 'buscador/edit.html.twig', array('form' => $form->createView(), 'nombreConsulta' => null));
	}

	public function updateBuscador(Request $request, $id){

		$em = $this->getDoctrine()->getManager();

		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getEditBuscadorSn()){
				return $this->redirectToRoute('error_403');
			}
		}

		$query = $this->getDoctrine()->getRepository('App\Entity\BuscadorQueries')->find($id);

		//Recuperamos las variables disponibles y generamos el array
		$buscadoresVariablesRepo = $this->getDoctrine()->getRepository('App\Entity\BuscadorVariable');
		$buscadoresVariables = $buscadoresVariablesRepo->findBy(array('anulado'=>false), array('alias' => 'ASC'));

		$arrayVariables = Array();
		foreach($buscadoresVariables as $variable){
			$id = $variable->getId();
			$tabla = $variable->getTabla()->getAlias();
			$campo = $variable->getCampo();
			$descripcion = $variable->getAlias();

			//Si no existe el array lo definimos.
			if (!isset($arrayVariables[$tabla])){
				$arrayVariables[$tabla] = array();
			}
			$arrayVariables[$tabla][$descripcion] = $id;
		}

		//Recuperamos las variables seleccionadas de la consulta
		$variablesSeleccionadasRepo = $this->getDoctrine()->getRepository('App\Entity\BuscadorQueriesVariable');
		$variablesSeleccionadas = $variablesSeleccionadasRepo->findBy(array('query' => $query));

		$arrayVariablesSeleccionadas = array();
		foreach($variablesSeleccionadas as $qvar){
			array_push($arrayVariablesSeleccionadas, $qvar->getVariable()->getId());
		}

		$form = $this->createForm(BuscadorType::class, $query, array('variables' => $arrayVariables, 'variablesSeleccionadas' => $arrayVariablesSeleccionadas));
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$query = $form->getData();
			$em->persist($query);
			$em->flush();

			//Borramos las variables seleccionadas
			$variablesGuardadas = $variablesSeleccionadasRepo->findBy(array('query' => $query));
			foreach($variablesGuardadas as $varGuardada){
				$em->remove($varGuardada);
				$em->flush();
			}

			$variablesSeleccionadas = $form->get('variables')->getViewData();
			foreach ($variablesSeleccionadas as $var){
				$varObj = $buscadoresVariablesRepo->findOneBy(array('id' => $var, 'anulado'=>false));
				$statsQueryVars = new BuscadorQueriesVariable();
				$statsQueryVars->setQuery($query);
				$statsQueryVars->setVariable($varObj);
				$em->persist($statsQueryVars);
				$em->flush();
			}

			return $this->redirectToRoute('buscadores_condiciones', array('id' => $query->getId()));
		}

		return $this->render( 'buscador/edit.html.twig', array('form' => $form->createView(), 'nombreConsulta' => $query->getNombre()));
	}

	public function condiciones(Request $request, $id){

		//Si no tenemos el $id volvemos a la pantalla principal
		if (is_null($id)){
			return $this->redirect($this->generateUrl('buscadores_add'));
		}

		$em = $this->getDoctrine()->getManager();

		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getEditBuscadorSn()){
				return $this->redirectToRoute('error_403');
			}
		}

		$query = $this->getDoctrine()->getRepository('App\Entity\BuscadorQueries')->find($id);

		//Recuperamos las variables seleccionadas de la consulta
		$variablesSeleccionadasRepo = $this->getDoctrine()->getRepository('App\Entity\BuscadorQueriesVariable');
		$variablesSeleccionadas = $variablesSeleccionadasRepo->findBy(array('query' => $query));

		$arrayOptions = Array();
		foreach($variablesSeleccionadas as $variable){
			$arrayItem = Array();
			$arrayItem["id"] = $variable->getVariable()->getId();
			$arrayItem["tipo"] = $variable->getVariable()->getTipo();
			$arrayItem["campo"] = $variable->getVariable()->getTabla()->getTabla() . "." . $variable->getVariable()->getCampo();
			$arrayItem["alias"] = $variable->getVariable()->getTabla()->getAlias(). ' - ' . $variable->getVariable()->getAlias();
			array_push($arrayOptions, $arrayItem);
		}

		$form = $this->createForm(BuscadorType::class, $query, array('variables' => null, 'variablesSeleccionadas' => null));
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$query = $form->getData();
			$em->persist($query);
			$em->flush();


			return $this->redirectToRoute('buscadores_execute', array('id' => $query->getId()));
		}

		return $this->render( 'buscador/condiciones.html.twig', array('form' => $form->createView(), 'nombreConsulta' => $query->getNombre(), 'queryOptions' => $arrayOptions, 'queryRestricciones'=>$query->getRestricciones()));
	}

	public function execute(Request $request, $id, TranslatorInterface $translator){

		$em = $this->getDoctrine()->getManager();

		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getExecuteBuscadorSn()){
				return $this->redirectToRoute('error_403');
			}
		}

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);

		$queryObj = $this->getDoctrine()->getRepository('App\Entity\BuscadorQueries')->find($id);

		//Recuperamos las variables seleccionadas de la consulta
		$variablesSeleccionadasRepo = $this->getDoctrine()->getRepository('App\Entity\BuscadorQueriesVariable');
		$variablesSeleccionadas = $variablesSeleccionadasRepo->findBy(array('query' => $queryObj));

		$querySelect = "SELECT DISTINCT ";
		$queryFrom = "FROM empresa_view ";
		$queryJoin = "";
		$queryWhere = "WHERE 1=1 ";

		//Generamos la cabecera de la tabla
		$columns = array();

		//Recuperamos los campos seleccionados
		foreach($variablesSeleccionadas as $var){
			$var = $var->getVariable();

			$select = $var->getTabla()->getTabla().".".$var->getCampo();
			$campo = $var->getTabla()->getTabla()."_".$var->getCampo();
			$alias = $var->getAlias();
			$nombreColumna = $var->getTabla()->getAlias(). '.'. $var->getAlias();

			$querySelect = $querySelect . $select ." as ". $campo. ", ";


			switch ($var->getTipo()){
				case "string":
					array_push($columns, $nombreColumna);
					break;

				case "date":
					array_push($columns, $nombreColumna);
					break;

				case "datetime":
					array_push($columns, $nombreColumna);
					break;

				case "time":
					array_push($columns, $nombreColumna);
					break;

				case "int":
					array_push($columns, $nombreColumna);
					break;

				case "boolean":
					array_push($columns, $nombreColumna);
					break;
			}
		}

		$querySelect = rtrim($querySelect,", ");
		$querySelect .= " ";
		$queryCount = "SELECT count(*) FROM (".$querySelect;

		//Recuperamos las tablas que intervienen
		$query = "select distinct c.tabla, c.join_text as join from buscador_queries_variable a inner join buscador_variable b on a.variable_id = b.id inner join buscador_tabla c on b.tabla_id = c.id where query_id = '".$id."' ";
		$stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
		$stmt->execute();
		$results = $stmt->fetchAll();

		foreach ($results as $result){
			$queryJoin .= $result['join']." ";
		}
		$queryFrom = rtrim($queryFrom,", ");
		$queryFrom .= " ";

		$restricciones = ltrim($queryObj->getRestriccionesSql(), "\"");
		$restricciones = rtrim($restricciones, "\"");

		if ($restricciones != ""){
			$queryWhere .= "and ". $restricciones;
		}

		$queryCount = $queryCount.$queryFrom.$queryJoin.$queryWhere.") as query";
		$query = $querySelect.$queryFrom.$queryJoin.$queryWhere;

		//Hacemos el count para saber cuantos registros devolvera la query
		$stmt = $em->getConnection()->prepare($queryCount);
		$stmt->execute();
		$resultsCount = $stmt->fetchAll();

		$contador = $resultsCount[0]['count'];

		if($contador > 100000){
			$traduccion = $translator->trans('TRANS_BUSCADOR_ERROR_REGISTROS');
			$this->addFlash('danger',  $traduccion);
			return $this->redirectToRoute('buscadores_condiciones', array('id' => $queryObj->getId()));
		}

		$stmt = $em->getConnection()->prepare($query);
		$stmt->execute();
		$results = $stmt->fetchAll();

        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $queryObj, $usuario);
        $em->flush();

		return $this->render( 'buscador/execute.html.twig', array('columns' => $columns, 'resultados' => $results, 'nombreConsulta' => $queryObj->getNombre()));
	}

	public function copy(Request $request, $id, TranslatorInterface $translator){

		$em = $this->getDoctrine()->getManager();

		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getCopyBuscadorSn()){
				return $this->redirectToRoute('error_403');
			}
		}

		$user = $this->getUser();
		$usuario = $this->getDoctrine()->getRepository('App\Entity\User')->findForSecurityUser($user);

		//Buscamos la consulta
		$query = $this->getDoctrine()->getRepository('App\Entity\BuscadorQueries')->find($id);

		$newQuery = clone $query;
		$newQuery->setUsuario($usuario);
		$newQuery->setDtcrea(new \DateTime());
		$newQuery->setCompartida(false);
		$newQuery->setAnulado(false);
		$nombre = $query->getNombre();
		$newQuery->setNombre('Copia de '.$nombre);
		$em->persist($newQuery);
		$em->flush();

		//Buscamos las variables
		$variables = $this->getDoctrine()->getRepository('App\Entity\BuscadorQueriesVariable')->findBy(array('query' => $query));

		foreach ($variables as $variable){
			$newVariable = clone $variable;
			$newVariable->setQuery($newQuery);
			$em->persist($newVariable);
			$em->flush();
		}

		$traduccion = $translator->trans('TRANS_COPY_OK');
		$this->addFlash('success',  $traduccion);
		return $this->redirectToRoute('buscadores_show');

	}

	public function delete(Request $request, $id, TranslatorInterface $translator){

		$em = $this->getDoctrine()->getManager();

		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getDeleteBuscadorSn()){
				return $this->redirectToRoute('error_403');
			}
		}

		$query = $this->getDoctrine()->getRepository('App\Entity\BuscadorQueries')->find($id);

		$variables = $this->getDoctrine()->getRepository('App\Entity\BuscadorQueriesVariable')->findBy(array('query' => $query));

		foreach ($variables as $variable){
			$em->remove($variable);
			$em->flush();
		}

		$em->remove($query);
		$em->flush();

		$traduccion = $translator->trans('TRANS_DELETE_OK');
		$this->addFlash('success',  $traduccion);
		return $this->redirectToRoute('buscadores_show');
	}

}