<?php

namespace App\Controller;


use App\Entity\Remesa;
use App\Logger;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RemesaController extends AbstractController
{

	public function showRemesas(Request $request){

		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getRemesaSn()){
				return $this->redirectToRoute('error_403');
			}
		}

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $remesaConfig = $this->getDoctrine()->getRepository('App\Entity\RemesaConfig')->find(1);
        $ordenante = $remesaConfig->getOrdenante();
        $ccc = $remesaConfig->getCcc();

		$query = "select a.id, d.empresa, d.id as empresaid, a.cuenta_id, concat(c.serie,'',b.num_fac) as num_fac, round(a.importe::numeric,2) as importe, to_char(a.fecha, 'DD/MM/YYYY') as fecha, 
        to_char(a.fecha, 'YYYYMMDDHHmm') as fechatimestamp, to_char(a.vencimiento, 'DD/MM/YYYY') as vencimiento, to_char(a.vencimiento, 'YYYYMMDDHHmm') as vencimientotimestamp, b.id as facturaid from giro_bancario a
        left join facturacion b on a.facturacion_id = b.id
        left join serie_factura c on b.serie_id = c.id
        inner join empresa d on b.empresa_id = d.id
        where a.remesa_id is null
        and a.anulado = false  
        and a.devolucion = false
        and a.remesado = false
        and a.girado = false
        and b.anulado = false
        order by a.fecha desc";
		$stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
		$stmt->execute();
		$remesas = $stmt->fetchAll();

		$arrayRemesas = array();
		foreach ($remesas as $r){
		    $empresaId = $r['empresaid'];
            $query = "select * from datos_bancarios where empresa_id = $empresaId and anulado = false and principal = true";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $cuentaBancaria = $stmt->fetchAll();

            $item = array();
            $item['id'] = $r['id'];
            $item['empresa'] = $r['empresa'];
            $item['cuenta_id'] = $r['cuenta_id'];
            $item['num_fac'] = $r['num_fac'];
            $item['importe'] = $r['importe'];
            $item['fecha'] = $r['fecha'];
            $item['fechatimestamp'] = $r['fechatimestamp'];
            $item['vencimiento'] = $r['vencimiento'];
            $item['vencimientotimestamp'] = $r['vencimientotimestamp'];

            if(count($cuentaBancaria) > 0){
                $item['cuenta_sn'] = true;
            }else{
                $item['cuenta_sn'] = false;
            }

            //Comprobamos si la factura tiene un abono añadido
            if(!is_null($r['facturaid'])){
                $facturaId = $r['facturaid'];
                $query = "select b.importe from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.empresa_id = $empresaId and a.serie_id = 6 and a.anulado = false and a.factura_asociada_id = $facturaId and b.anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $abonoFactura = $stmt->fetchAll();
                $importe = $r['importe'];
                foreach ($abonoFactura as $af){
                    $importe = $importe + $af['importe'];
                }

                $item['importe'] = round($importe,2);
            }else{
                $item['importe'] = $r['importe'];
            }

            array_push($arrayRemesas, $item);
        }

        $object = array("json"=>$username, "entidad"=>"remesas", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

		return $this->render('remesa/show.html.twig',  array('remesas' => $arrayRemesas, 'ordenante' => $ordenante, 'ccc' => $ccc));
	}

	public function showRemesasGeneradas(Request $request){

		$em = $this->getDoctrine()->getManager();

		$session = $request->getSession();
		$privilegios = $session->get('privilegiosRol');
		if(!is_null($privilegios)){
			if(!$privilegios->getRemesaSn()){
				return $this->redirectToRoute('error_403');
			}
		}

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, to_char(a.fecha, 'DD/MM/YYYY') as fecha, to_char(a.fecha, 'YYYYMMDDHHmm') as fechatimestamp from remesa a order by a.fecha desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $remesas = $stmt->fetchAll();

        $arrayRemesas = array();
        foreach ($remesas as $r){
            $remesaId = $r['id'];

            $item = array();
            $item['id'] = $r['id'];
            $item['fecha'] = $r['fecha'];
            $item['fechatimestamp'] = $r['fechatimestamp'];

            $importe = 0;

            $query = "select b.empresa_id, round(a.importe::numeric,2) as importe, b.id as facturaid from giro_bancario a
            left join facturacion b on a.facturacion_id = b.id
            where a.remesa_id = $remesaId
            order by a.fecha desc";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $giros = $stmt->fetchAll();

            foreach ($giros as $g){
                $empresaId = $g['empresa_id'];
                $importe = $importe + $g['importe'];
                //Comprobamos si la factura tiene un abono añadido


                if(!is_null($g['facturaid'])){
                    $facturaId = $g['facturaid'];
                    $query = "select b.importe from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.empresa_id = $empresaId and a.serie_id = 6 and a.anulado = false and a.factura_asociada_id = $facturaId and b.anulado = false";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $abonoFactura = $stmt->fetchAll();
                    $importeGiro = $g['importe'];
                    foreach ($abonoFactura as $af){
                        //fix Ticket#2025032510000016 — REMESA ERROR 25.03.25
                        $importe = $importe + $af['importe'];
                        //$importeGiro = $importeGiro + $af['importe'];
                    }
                    //$importe = $importeGiro;
                    //$importe = $importe + $importeGiro;
                }else{
                    $importe = $importe + $g['importe'];
                }
            }
            $item['importe'] = round($importe, 2);
            array_push($arrayRemesas, $item);
        }


        $object = array("json"=>$username, "entidad"=>"remesas generadas", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

		return $this->render('remesa/show_generadas.html.twig',  array('remesas' => $arrayRemesas) );
	}

	public function recuperateDatosRemesa(Request $request){
		$session = $request->getSession();

		$remesas = $_POST['remesas'];
		$nombreOrdenante = $_POST['nombreOrdenante'];
		$ccc = $_POST['ccc'];
		$importe = $_POST['importe'];

		$numeroOperaciones = count($remesas);

		$session->set('remesas', $remesas);
		$session->set('nombreOrdenante', $nombreOrdenante);
		$session->set('ccc', $ccc);
		$session->set('importe', $importe);
		$session->set('numeroOperaciones', $numeroOperaciones);

		$data = array();
		array_push($data, "OK");

		return new JsonResponse($data);
	}

	public function generaFicheroRemesa(Request $request, TranslatorInterface $translator){

		$em = $this->getDoctrine()->getManager();

		$user = $this->getUser();

        $remesaConfig = $this->getDoctrine()->getRepository('App\Entity\RemesaConfig')->find(1);
        $bicMeditrauma = $remesaConfig->getBic();

		$mediaMediaRepo = $this->getDoctrine()->getRepository('App\Application\Sonata\MediaBundle\Entity\Media');
		$usuario = $this->getDoctrine()->getRepository('App\Entity\User')->findForSecurityUser($user);
		$username = $usuario->getUsername();

		$session = $request->getSession();

		$remesas = $session->get('remesas');
		$nombreOrdenante = $session->get('nombreOrdenante');
		$ccc = $session->get('ccc');
		$importe = $session->get('importe');
		$numeroOperaciones = $session->get('numeroOperaciones');

		$hoy = new \DateTime();
		$hoyString = $hoy->format('Y-m-d').'T'.$hoy->format('H:i:s');
		$year = $hoy->format('Y');
		$yearString = substr($year, 2, 4);
		$hoyFichero = $yearString.$hoy->format('mdHis');
		$hoyFicheroXml = $hoy->format('Y-m-d');
		$dtMsgId = $hoy->format('YmdHis');

		$nombreFichero = 'adeudos_CORE_'.$hoyFichero.'_ES46000B58482415.xml';
//		$cvDir = $this->getParameter('kernel.root_dir');
//		$cvDir = $this->getParameter('kernel.root_dir') . '\\public\\upload\\media\\remesas\\';

		$dtRemesa = $hoy->format('d/m/Y H:i:s');

        $em->beginTransaction();

        try{
            $newRemesa = new Remesa();
            $newRemesa->setDescripcion('Remesa realizada el '.$dtRemesa.' por '.$username);
            $newRemesa->setFecha($hoy);
            $newRemesa->setFechaCargo($hoy);
            $newRemesa->setArchivo($nombreFichero);
            $newRemesa->setOrdenante(1);
            $newRemesa->setCuaderno(19143);
            $newRemesa->setRuta('upload/media/remesas/'.$nombreFichero);
            $em->persist($newRemesa);
            $em->flush();

            //Generamos la cabecera del xml
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02">';
            $xml .= '<CstmrDrctDbtInitn>';
            $xml .= '<GrpHdr>';
            $xml .= '<MsgId>PRE'.$dtMsgId.'00000PREVENCION</MsgId>';
            $xml .= '<CreDtTm>'.$hoyString.'</CreDtTm>';
            $xml .= '<NbOfTxs>'.$numeroOperaciones.'</NbOfTxs>';
            $xml .= '<CtrlSum>'.$importe.'</CtrlSum>';
            $xml .= '<InitgPty>';
            $xml .= '<Nm>MEDITRAUMA, SL</Nm>';
            $xml .= '<Id>';
            $xml .= '<OrgId>';
            $xml .= '<Othr>';
            $xml .= '<Id>ES46000B58482415</Id>';
            $xml .= '</Othr>';
            $xml .= '</OrgId>';
            $xml .= '</Id>';
            $xml .= '</InitgPty>';
            $xml .= '</GrpHdr>';

            $xml .= '<PmtInf>';
            $xml .= '<PmtInfId>ES46000B58482415-'.$dtMsgId.'</PmtInfId>';
            $xml .= '<PmtMtd>DD</PmtMtd>';
            $xml .= '<BtchBookg>true</BtchBookg>';
            $xml .= '<NbOfTxs>'.$numeroOperaciones.'</NbOfTxs>';
            $xml .= '<CtrlSum>'.$importe.'</CtrlSum>';
            $xml .= '<PmtTpInf>';
            $xml .= '<SvcLvl>';
            $xml .= '<Cd>SEPA</Cd>';
            $xml .= '</SvcLvl>';
            $xml .= '<LclInstrm>';
            $xml .= '<Cd>CORE</Cd>';
            $xml .= '</LclInstrm>';
            $xml .= '<SeqTp>RCUR</SeqTp>';
            $xml .= '</PmtTpInf>';
            $xml .= '<ReqdColltnDt>'.$hoyFicheroXml.'</ReqdColltnDt>';
            $xml .= '<Cdtr>';
            $xml .= '<Nm>MEDITRAUMA, SL</Nm>';
            $xml .= '<PstlAdr>';
            $xml .= '<Ctry>ES</Ctry>';
            $xml .= '<AdrLine>C/. MIGUEL BIADA, 119</AdrLine>';
            $xml .= '<AdrLine>08302 MATARO (BARCELONA)</AdrLine>';
            $xml .= '</PstlAdr>';
            $xml .= '</Cdtr>';
            $xml .= '<CdtrAcct>';
            $xml .= '<Id><IBAN>'.$ccc.'</IBAN></Id>';
            $xml .= '<Ccy>EUR</Ccy>';
            $xml .= '</CdtrAcct>';
            $xml .= '<CdtrAgt>';
            $xml .= '<FinInstnId><BIC>'.$bicMeditrauma.'</BIC></FinInstnId>';
            $xml .= '</CdtrAgt>';
            $xml .= '<ChrgBr>SLEV</ChrgBr>';
            $xml .= '<CdtrSchmeId>';
            $xml .= '<Id>';
            $xml .= '<PrvtId>';
            $xml .= '<Othr>';
            $xml .= '<Id>ES46000B58482415</Id>';
            $xml .= '<SchmeNm>';
            $xml .= '<Prtry>SEPA</Prtry>';
            $xml .= '</SchmeNm>';
            $xml .= '</Othr>';
            $xml .= '</PrvtId>';
            $xml .= '</Id>';
            $xml .= '</CdtrSchmeId>';

            for($i = 0 ; $i<count($remesas); $i++){
                $giroId = $remesas[$i];

                $query = "select a.id, a.concepto, to_char(a.fecha,'YYYY-MM-DD') as fecha, a.importe, b.bic, b.iban_digital, c.empresa, c.nombre_representante, c.domicilio_fiscal, c.localidad_fiscal, 
					c.codigo_postal_fiscal, d.cod_pais, c.id as empresaid, a.facturacion_id from giro_bancario a
					inner join datos_bancarios b on a.cuenta_id = b.id
					inner join empresa c on b.empresa_id = c.id
					inner join pais d on b.pais_id = d.id 
					where a.id = $giroId";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $datosGiro = $stmt->fetchAll();

                $giroId = $datosGiro[0]['id'];
                $concepto = $datosGiro[0]['concepto'];
                $fechaCobro = $datosGiro[0]['fecha'];
                $importeCobro = $datosGiro[0]['importe'];
                $bic = $datosGiro[0]['bic'];
                $iban = $datosGiro[0]['iban_digital'];
                $empresa = str_replace( '&', '&amp;', $datosGiro[0]['empresa']);
                $nombreRepresentante = $datosGiro[0]['nombre_representante'];
                $domicilio = $datosGiro[0]['domicilio_fiscal'];
                $localidad = $datosGiro[0]['localidad_fiscal'];
                $codigoPostal = $datosGiro[0]['codigo_postal_fiscal'];
                $codigoPais = $datosGiro[0]['cod_pais'];
                $empresaId = $datosGiro[0]['empresaid'];

                $query = "select referencia, to_char(firma,'YYYY-MM-DD') as fecha from mandato where empresa_id = $empresaId and anulado = false order by id desc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $datosMandato = $stmt->fetchAll();

                $mandato = "";
                $fechaFirma = "";
                if(count($datosMandato) > 0){
                    $mandato = $datosMandato[0]['referencia'];
                    $fechaFirma = $datosMandato[0]['fecha'];
                }else{
                    $em->rollBack();
                    $traduccion = $translator->trans('TRANS_MSG_KO_REMESA_1');
                    $traduccion2 = $translator->trans('TRANS_MSG_KO_REMESA_2');
                    $this->addFlash('danger', $traduccion.' '.$empresa.' '.$traduccion2);
                    return $this->redirectToRoute('remesa_show');
                }

                //Comprobamos si el giro tiene un abono
                $facturaId = $datosGiro[0]['facturacion_id'];
                $query = "select b.importe from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.empresa_id = $empresaId and a.serie_id = 6 and a.anulado = false and a.factura_asociada_id = $facturaId and b.anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $abonoFactura = $stmt->fetchAll();
                foreach ($abonoFactura as $af){
                    $importeCobro = $importeCobro + $af['importe'];
                }

                if($importeCobro == 0){
                    $em->rollBack();
                    $traduccion = $translator->trans('TRANS_MSG_KO_REMESA_1');
                    $traduccion3 = $translator->trans('TRANS_MSG_KO_REMESA_3');
                    $this->addFlash('danger', $traduccion.' '.$empresa.' '.$traduccion3);
                    return $this->redirectToRoute('remesa_show');
                }

                $ahora = new \DateTime();
                $ahoraString = $ahora->format('YmdHis');

                //Generamos el cuerpo del xml
                $xml .= '<DrctDbtTxInf>';

                $xml .= '<PmtId>';
                $xml .= '<InstrId>RCB'.$ahoraString.'-'.$giroId.'</InstrId>';
                $xml .= '<EndToEndId>RCB'.$ahoraString.''.$giroId.'</EndToEndId>';
                $xml .= '</PmtId>';
                $xml .= '<InstdAmt Ccy="EUR">'.round($importeCobro,2).'</InstdAmt>';

                $xml .= '<DrctDbtTx>';
                $xml .= '<MndtRltdInf>';
                $xml .= '<MndtId>'.$mandato.'</MndtId>';
                $xml .= '<DtOfSgntr>'.$fechaFirma.'</DtOfSgntr>';
                $xml .= '<AmdmntInd>false</AmdmntInd>';
                $xml .= '</MndtRltdInf>';
                $xml .= '</DrctDbtTx>';

                $xml .= '<DbtrAgt>';
                $xml .= '<FinInstnId>';
                $xml .= '<BIC>'.$bic.'</BIC>';
                $xml .= '</FinInstnId>';
                $xml .= '</DbtrAgt>';

                $xml .= '<Dbtr>';
                $xml .= '<Nm>'.$empresa.'</Nm>';
                $xml .= '<PstlAdr>';
                $xml .= '<Ctry>'.$codigoPais.'</Ctry>';
                $xml .= '<AdrLine>'.$domicilio.'</AdrLine>';
                $xml .= '<AdrLine>'.$codigoPostal.' '.$localidad.'</AdrLine>';
                $xml .= '</PstlAdr>';
                $xml .= '</Dbtr>';

                $xml .= '<DbtrAcct>';
                $xml .= '<Id>';
                $xml .= '<IBAN>'.$iban.'</IBAN>';
                $xml .= '</Id>';
                $xml .= '</DbtrAcct>';

                $xml .= '<RmtInf>';
                $xml .= '<Ustrd>'.$concepto.'</Ustrd>';
                $xml .= '</RmtInf>';

                $xml .= '</DrctDbtTxInf>';

                $giroBancario = $this->getDoctrine()->getRepository('App\Entity\GiroBancario')->find($giroId);
                $giroBancario->setRemesa($newRemesa);
                $giroBancario->setRemesado(true);
                $giroBancario->setGirado(true);
                $em->persist($giroBancario);
                $em->flush();
            }
        }catch(\Exception $e){
            $em->rollBack();

            $traduccion = $translator->trans('TRANS_MSG_KO_REMESA');
            $this->addFlash('danger', $traduccion);

            return $this->redirectToRoute('remesa_show');
        }

        $em->commit();


		//Generamos la parte final del xml
        $xml .= '</PmtInf>';
		$xml .= '</CstmrDrctDbtInitn>';
		$xml .= '</Document>';

		file_put_contents('upload/media/remesas/' . $nombreFichero, $xml);
		$sfile = new File('upload/media/remesas/'.$nombreFichero);
//		$sfile = new UploadedFile('uploads/media/remesas/' . $nombreFichero, $nombreFichero, "", $file);

		/*$media = new Media();
		$media->setName($nombreFichero);
		$media->setDescription("");
		$media->setProviderName('sonata.media.provider.file');
		$media->setEnabled(true);
		$media->setAuthorName($usuario->getUsername());
		$media->setBinaryContent($sfile);
		$em->persist($media);
		$em->flush();

		$mediaSave = $mediaMediaRepo->find($media->getId());
		$newRemesa->setMedia($mediaSave);
		$em->persist($newRemesa);
		$em->flush();*/

		$traduccion = $translator->trans('TRANS_MSG_OK_REMESA');
		$this->addFlash('success', $traduccion);

        return $this->redirectToRoute('remesa_show');
	}

	public function downRemesaGenerada(Request $request, $id, TranslatorInterface $translator){

		$em = $this->getDoctrine()->getManager();

		$remesa = $em->getRepository('App\Entity\Remesa')->find($id);

		if(!is_null($remesa->getRuta())){
			$nombreFichero = $remesa->getArchivo();
			$ruta = $remesa->getRuta();
			if(!file_exists($ruta)){
                $traduccion = $translator->trans('TRANS_MSG_NO_FICHERO_REMESA');
                $this->addFlash('danger', $traduccion);

                return $this->redirectToRoute('remesas_generadas_show');
            }
			$xml = file_get_contents($ruta, true);

			$response = new Response($xml);

			// Create the disposition of the file
			$disposition = $response->headers->makeDisposition(
				ResponseHeaderBag::DISPOSITION_ATTACHMENT,
				$nombreFichero
			);

			// Set the content disposition
			$response->headers->set('Content-Disposition', $disposition);

			$traduccion = $translator->trans('TRANS_MSG_OK_REMESA');
			$this->addFlash('success', $traduccion);

			return $response;
		}else{
			$traduccion = $translator->trans('TRANS_MSG_NO_FICHERO_REMESA');
			$this->addFlash('danger', $traduccion);

			return $this->redirectToRoute('remesas_generadas_show');
		}
	}

	public function filtraRemesas(Request $request){
		$ini = $_REQUEST['ini'];
		$fin = $_REQUEST['fin'];

		$query = "select a.id, d.empresa, d.id as empresaid, a.cuenta_id, concat(c.serie,'',b.num_fac) as num_fac, round(a.importe::numeric, 2) as importe, to_char(a.fecha, 'DD/MM/YYYY') as fecha, 
        to_char(a.fecha, 'YYYYMMDDHHmm') as fechatimestamp, to_char(a.vencimiento, 'DD/MM/YYYY') as vencimiento, to_char(a.vencimiento, 'YYYYMMDDHHmm') as vencimientotimestamp, b.id as facturaid from giro_bancario a
        inner join facturacion b on a.facturacion_id = b.id
        inner join serie_factura c on b.serie_id = c.id
        inner join empresa d on b.empresa_id = d.id
        where a.remesa_id is null
        and a.anulado = false  
        and a.devolucion = false
        and a.remesado = false
        and a.girado = false
        and b.anulado = false ";

		if ($ini != ""){
			$query .= " and a.vencimiento >= '$ini 00:00:00' ";
		}

		if ($fin != ""){
			$query .= " and a.vencimiento <= '$fin 23:59:59' ";
		}

		$query .= "order by a.vencimiento desc";

		$stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
		$stmt->execute();
		$remesas = $stmt->fetchAll();

        $arrayRemesas = array();
        foreach ($remesas as $r){
            $empresaId = $r['empresaid'];
            $query = "select * from datos_bancarios where empresa_id = $empresaId and anulado = false and principal = true";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $cuentaBancaria = $stmt->fetchAll();

            $item = array();
            $item['id'] = $r['id'];
            $item['empresa'] = $r['empresa'];
            $item['cuenta_id'] = $r['cuenta_id'];
            $item['num_fac'] = $r['num_fac'];
            $item['fecha'] = $r['fecha'];
            $item['fechatimestamp'] = $r['fechatimestamp'];
            $item['vencimiento'] = $r['vencimiento'];
            $item['vencimientotimestamp'] = $r['vencimientotimestamp'];

            if(count($cuentaBancaria) > 0){
                $item['cuenta_sn'] = true;
            }else{
                $item['cuenta_sn'] = false;
            }

            //Comprobamos si la factura tiene un abono añadido
            if(!is_null($r['facturaid'])){
                $facturaId = $r['facturaid'];
                $query = "select b.importe from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.empresa_id = $empresaId and a.serie_id = 6 and a.anulado = false and a.factura_asociada_id = $facturaId and b.anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $abonoFactura = $stmt->fetchAll();
                $importe = $r['importe'];
                foreach ($abonoFactura as $af){
                    $importe = $importe + $af['importe'];
                }

                $item['importe'] = round($importe,2);
            }else{
                $item['importe'] = $r['importe'];
            }

            array_push($arrayRemesas, $item);
        }

		return new JsonResponse(json_encode($arrayRemesas));
	}

    public function exportExcelRemesas(Request $request){

        $remesasSelect = $_REQUEST['remesas'];

        $query = "select b.id as facturaid, e.id as empresaid, e.empresa as Empresa, e.cif as CIF, d.iban_papel as IBAN, concat(c.serie,'',b.num_fac) as Factura, round(a.importe::numeric, 2) as Importe, to_char(a.fecha, 'DD/MM/YYYY') as Expedición, to_char(a.vencimiento, 'DD/MM/YYYY') as Vencimiento from giro_bancario a
        inner join facturacion b on a.facturacion_id = b.id
        inner join serie_factura c on b.serie_id = c.id
        inner join datos_bancarios d on a.cuenta_id = d.id
        inner join empresa e on d.empresa_id = e.id
        where a.remesa_id is null
        and a.anulado = false 
        and b.anulado = false 
        and d.principal = true
        and a.id in ($remesasSelect)
        order by a.fecha desc";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $remesas = $stmt->fetchAll();

        $arrayRemesas = array();
        foreach ($remesas as $r){
            $empresaId = $r['empresaid'];

            $item = array();
            $item['Empresa'] = $r['empresa'];
            $item['CIF'] = $r['cif'];
            $item['IBAN'] = $r['iban'];
            $item['Factura'] = $r['factura'];
            $item['Expedición'] = $r['expedición'];
            $item['Vencimiento'] = $r['vencimiento'];

            //Comprobamos si la factura tiene un abono añadido
            if(!is_null($r['facturaid'])){
                $facturaId = $r['facturaid'];
                $query = "select b.importe from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.empresa_id = $empresaId and a.serie_id = 6 and a.anulado = false and a.factura_asociada_id = $facturaId and b.anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $abonoFactura = $stmt->fetchAll();
                $importe = $r['importe'];
                foreach ($abonoFactura as $af){

                    $importe = $importe + $af['importe'];

                }

                $item['Importe'] = round($importe,2);
            }else{
                $item['Importe'] = $r['importe'];
            }

            array_push($arrayRemesas, $item);
        }

        $hoy = new \DateTime();
        $hoyString = $hoy->format('dmYHis');

        $phpExcelObject = new Spreadsheet();
        $phpExcelObject->getActiveSheet()->fromArray(array_keys($arrayRemesas[0]),  NULL, 'A1');
        $phpExcelObject->getActiveSheet()->fromArray($arrayRemesas,  NULL, 'A2');

        $writer = new Xlsx($phpExcelObject);

        $fileName = 'Remesas '.$hoyString.'.xlsx';

        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);

        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    public function deleteRemesaGenerada(Request $request, $id, TranslatorInterface $translator){

        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getDeleteRemesaSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $remesa = $em->getRepository('App\Entity\Remesa')->find($id);

        $giros = $em->getRepository('App\Entity\GiroBancario')->findBy(array('remesa' => $remesa, 'anulado' => false));
        foreach ($giros as $g){
            $g->setGirado(false);
            $g->setRemesado(false);
            $g->setRemesa(null);
            $em->persist($g);
            $em->flush();
        }

        unlink('upload/media/remesas/'.$remesa->getArchivo());

        $em->remove($remesa);
        $em->flush();

        $traduccion = $translator->trans('TRANS_REMESA_DELETE_OK');
        $this->addFlash('success', $traduccion);

        return $this->redirectToRoute('remesas_generadas_show');
    }

}