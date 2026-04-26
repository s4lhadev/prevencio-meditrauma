<?php

namespace App\Controller;

use App\Entity\DatosBancarios;
use App\Entity\Mandato;
use App\Entity\Renovacion;
use App\Form\DatosBancariosType;
use App\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class DatosBancariosController extends AbstractController
{
    public function createDatoBancario(Request $request, TranslatorInterface $translator)
    {
	    $em = $this->getDoctrine()->getManager();

	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getAddDatosBancariosSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

	    $empresa = $session->get('empresa');
	    $empresaId = $empresa->getId();
	    $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        $datoBancario = new DatosBancarios();

        //Ponemos el pais España como principal
        $pais = $em->getRepository('App\Entity\Pais')->find(26);
        $datoBancario->setPais($pais);

        $form = $this->createForm(DatosBancariosType::class, $datoBancario, array('empresaId' => $empresaId, 'empresaObj' => $empresa));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
	        $datoBancario = $form->getData();

            if(!is_null($empresa)){
                $datoBancario->setEmpresa($empresa);
            }

	        $datoBancario->setAnulado(false);
            $em->persist($datoBancario);
            $em->flush();

	        $traduccion = $translator->trans('TRANS_CREATE_OK');
	        $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('empresa_update', array('id'=>$empresaId));
        }

        return $this->render( 'datosbancarios/edit.html.twig', array('form' => $form->createView(), 'mandatos' => null));
    }

    public function showDatosBancarios(Request $request)
    {
	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getDatosBancariosSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

	    $empresa = $session->get('empresa');
	    $datosBancarios = $this->getDoctrine()->getRepository('App\Entity\DatosBancarios')->findBy(array('empresa' => $empresa, 'anulado' => false));

        $object = array("json"=>$username, "entidad"=>"datos bancarios: ".$empresa->getEmpresa(), "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('datosbancarios/show.html.twig', array('datosbancarios' => $datosBancarios) );
    }

    public function deleteDatoBancario($id, Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getDeleteDatosBancariosSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

	    $empresa = $session->get('empresa');
	    $empresaId = $empresa->getId();

	    $datosBancarios = $em->getRepository('App\Entity\DatosBancarios')->find($id);

        if (!$datosBancarios) {
	        throw $this->createNotFoundException(
		        'El dato bancario con id ' . $id.' no existe'
	        );
        }

	    $datosBancarios->setAnulado(true);
	    $em->persist($datosBancarios);
	    $em->flush();

	    $traduccion = $translator->trans('TRANS_DELETE_OK');
	    $this->addFlash('success', $traduccion);

        return $this->redirectToRoute('empresa_update', array('id'=>$empresaId));
    }

    public function updateDatoBancario(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

	    $session = $request->getSession();
	    $privilegios = $session->get('privilegiosRol');
	    if(!is_null($privilegios)){
		    if(!$privilegios->getEditDatosBancariosSn()){
			    return $this->redirectToRoute('error_403');
		    }
	    }

	    $datosBancarios = $em->getRepository('App\Entity\DatosBancarios')->find($id);

	    //Comprobamos si tiene una empresa seleccionada sino le asignamos la del contrato
	    $empresa = $session->get('empresa');
	    if(!is_null($empresa)){
		    $empresaId = $empresa->getId();
		    $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
	    }else{
		    $empresaId = $datosBancarios->getEmpresa()->getId();
		    $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
		    $session->set('empresa', $empresa);
	    }

        if (!$datosBancarios) {
	        throw $this->createNotFoundException(
		        'El dato bancario con id ' . $id.' no existe'
	        );
        }

        //Buscamos los mandatos de la empresa
	    $mandatos = $em->getRepository('App\Entity\Mandato')->findBy(array('empresa' => $empresa, 'anulado' => false));

        $mandatoSn = true;
        if(count($mandatos) == 0){
            $mandatoSn = false;
        }

        $form = $this->createForm(DatosBancariosType::class, $datosBancarios, array('empresaId' => $empresa->getId(), 'empresaObj' => $empresa));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
	        $datosBancarios = $form->getData();
	        $em->persist($datosBancarios);
            $em->flush();

	        $traduccion = $translator->trans('TRANS_UPDATE_OK');
	        $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('empresa_update', array('id'=>$empresaId));
        }
        return $this->render('datosbancarios/edit.html.twig',  array('form' => $form->createView(), 'mandatos' => $mandatos, 'mandatoSn' => $mandatoSn));
    }

    public function generarMandato(Request $request){
	    $em = $this->getDoctrine()->getManager();

	    $session = $request->getSession();
	    $empresa = $session->get('empresa');
	    $empresaId = $empresa->getId();
	    $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

	    $firma = new \DateTime();
	    $mandato = 'MDT'.$empresa->getCodigo().'/01 01';

	    $newMandato = new Mandato();
	    $newMandato->setEmpresa($empresa);
	    $newMandato->setReferencia($mandato);
	    $newMandato->setTipoPago(2);
	    $newMandato->setFirma($firma);
	    $newMandato->setTipoMandato(1);
	    $em->persist($newMandato);
	    $em->flush();

	    $data = array();
	    array_push($data, "OK");

	    return new JsonResponse($data);
    }

	public function recuperaMandatos(Request $request){

		$em = $this->getDoctrine()->getManager();
		$datoBancarioId = $_REQUEST['datoBancarioId'];

		$datoBancario = $em->getRepository('App\Entity\DatosBancarios')->find($datoBancarioId);
		$empresaId = $datoBancario->getEmpresa()->getId();

		$query = "SELECT id, referencia, to_char(firma, 'DD/MM/YYYY') as firma from mandato where empresa_id = $empresaId and anulado = false order by firma asc";
		$stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
		$stmt->execute();
		$mandatos = $stmt->fetchAll();

		return new JsonResponse(json_encode($mandatos));
	}

	public function recuperaMandato(Request $request){
		$em = $this->getDoctrine()->getManager();

		$mandatoId = $_REQUEST['mandatoId'];
		$mandato = $em->getRepository('App\Entity\Mandato')->find($mandatoId);

		$data = array(
			'mandatoId' => $mandato->getId(),
			'referencia' => $mandato->getReferencia(),
			'firma' => $mandato->getFirma()->format('Y-m-d'),
			'tipoPago' => $mandato->getTipoPago()
		);

		return new JsonResponse($data);
	}

	public function updateMandato(Request $request){
		$em = $this->getDoctrine()->getManager();

		$referencia = $_REQUEST['referencia'];
		$firma = new \DateTime($_REQUEST['fecha']);
		$tipoPago = $_REQUEST['tipoPago'];
		$mandatoId = $_REQUEST['mandatoId'];

		$mandato = $em->getRepository('App\Entity\Mandato')->find($mandatoId);
		$mandato->setReferencia($referencia);
		$mandato->setTipoPago($tipoPago);
		$mandato->setFirma($firma);
		$em->persist($mandato);
		$em->flush();

		$data = array();
		array_push($data, "OK");

		return new JsonResponse($data);
	}

	public function deleteMandato(Request $request){

		$em = $this->getDoctrine()->getManager();

		$mandatoId = $_REQUEST['mandatoId'];
		$mandato = $em->getRepository('App\Entity\Mandato')->find($mandatoId);
		$mandato->setAnulado(true);
		$em->persist($mandato);
		$em->flush();

		$data = array();
		array_push($data, "OK");

		return new JsonResponse($data);
	}

	public function recuperaBicNrb(Request $request){

		$em = $this->getDoctrine()->getManager();
		$entidadId = $_REQUEST['entidadId'];
		$entidad = $em->getRepository('App\Entity\EntidadBancaria')->find($entidadId);

		$data = array(
			'bic' => $entidad->getBic(),
			'nrb' => str_pad($entidad->getNrb(), 4, '0', STR_PAD_LEFT)
		);

		return new JsonResponse($data);
	}

	public function recuperaCodigoPais(Request $request){

		$em = $this->getDoctrine()->getManager();
		$paisId = $_REQUEST['paisId'];
		$pais = $em->getRepository('App\Entity\Pais')->find($paisId);

		$data = array(
			'codigo' => $pais->getCodPais(),
		);

		return new JsonResponse($data);
	}

	public function validarIban(){

		$ccc = $_REQUEST['iban'];
		$valido = true;

		//Dígito de control de la entidad y sucursal:
		//Se multiplica cada dígito por su factor de peso
		$suma = 0;
		$suma += $ccc[0] * 4;
		$suma += $ccc[1] * 8;
		$suma += $ccc[2] * 5;
		$suma += $ccc[3] * 10;
		$suma += $ccc[4] * 9;
		$suma += $ccc[5] * 7;
		$suma += $ccc[6] * 3;
		$suma += $ccc[7] * 6;
		$division = floor($suma/11);
		$resto    = $suma - ($division  * 11);
		$primer_digito_control = 11 - $resto;
		if($primer_digito_control == 11)
			$primer_digito_control = 0;
		if($primer_digito_control == 10)
			$primer_digito_control = 1;
		if($primer_digito_control != $ccc[8])
			$valido = false;

		//Dígito de control de la cuenta:
		$suma = 0;
		$suma += $ccc[10] * 1;
		$suma += $ccc[11] * 2;
		$suma += $ccc[12] * 4;
		$suma += $ccc[13] * 8;
		$suma += $ccc[14] * 5;
		$suma += $ccc[15] * 10;
		$suma += $ccc[16] * 9;
		$suma += $ccc[17] * 7;
		$suma += $ccc[18] * 3;
		$suma += $ccc[19] * 6;
		$division = floor($suma/11);
		$resto = $suma-($division  * 11);
		$segundo_digito_control = 11- $resto;
		if($segundo_digito_control == 11)
			$segundo_digito_control = 0;
		if($segundo_digito_control == 10)
			$segundo_digito_control = 1;
		if($segundo_digito_control != $ccc[9])
			$valido = false;

		$data = array(
			'result' => $valido
		);

		return new JsonResponse($data);
	}

	public function compruebaIBAN(Request $request){
		$em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();

		$iban = $_REQUEST['iban'];

        $query = "select * from datos_bancarios where iban_digital = '$iban' and empresa_id != $empresaId and anulado = false";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $ibanObj = $stmt->fetchAll();

		$data = array();
		if(count($ibanObj) > 0){
			array_push($data, "KO");
		}else{
			array_push($data, "OK");
		}

		return new JsonResponse($data);
	}

}