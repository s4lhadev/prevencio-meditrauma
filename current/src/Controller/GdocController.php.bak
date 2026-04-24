<?php

namespace App\Controller;

use App\Entity\EmpresaAccidenteLaboral;
use App\Entity\EmpresaCertificacion;
use App\Entity\EmpresaEstudioEpidemiologico;
use App\Entity\EmpresaManualVs;
use App\Entity\EmpresaMemoria;
use App\Entity\EmpresaModelo347;
use App\Entity\EmpresaNotificacion;
use App\Entity\EmpresaPlanPrevencion;
use App\Entity\EmpresaProtocoloAcoso;
use App\Entity\GdocEmpresaCarpeta;
use App\Entity\GdocFichero;
use App\Entity\GdocPlantillas;
use App\Entity\GdocPlantillasCarpeta;
use App\Entity\GdocTrabajadorCarpeta;
use App\Entity\LogEnvioMail;
use App\Form\EnviarFacturaMultipleType;
use App\Form\GdocType;
use App\Logger;
use pChart\pColor;
use pChart\pDraw;
use pChart\pCharts;
use pChart\pPie;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GdocController extends AbstractController
{
    public function index(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getGdocSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $user = $this->getUser();
        $usuario = $em->getRepository('App\Entity\User')->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        //Recuperamos las carpetas
        $query = "select * from gdoc_plantillas_carpeta where anulado = false ";
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
        $query = "select * from gdoc_plantillas where anulado = false";
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

            if (str_contains($fichero['nombre_completo'], '.pdf')) {
                $row['icon'] = "icon-file-pdf";
            } else {
                $row['icon'] = "icon-file-word";
            }
            array_push($data, $row);
        }
        $data = \json_encode($data);
        $mediaMediaRepo = $this->getDoctrine()->getRepository('App\Application\Sonata\MediaBundle\Entity\Media');
        $carpetas = $em->getRepository('App\Entity\GdocPlantillasCarpeta')->findAll();

        $plantilla = new GdocPlantillas();
        $form = $this->createForm(GdocType::class, $plantilla);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $plantilla = $form->getData();

            //			$mediaManager = $this->get('sonata.media.manager.media');
            /*$media = new Media();
			$media->setName($form->get('nombre')->getData());
			$media->setDescription("");
			$media->setProviderName('sonata.media.provider.file');
			$media->setEnabled(true);
			$media->setAuthorName($usuario->getUsername());
			$media->setBinaryContent($form->get('media')->getData());

			$em->persist($media);
			$em->flush();

            // $mediaManager->save($media);
			$mediaSave = $mediaMediaRepo->find($media->getId());
			$plantilla->setMedia($mediaSave);*/

            //Buscamos la configuración de la gestión documental
            $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
            $rutaGestionDocumental = $gdocConfig->getRuta();
            $carpetaPlantillas = $gdocConfig->getCarpetaPlantillas();

            $fichero = $form->get('media')->getData();
            $nombreFichero = $fichero->getClientOriginalName();

            $fichero->move($rutaGestionDocumental . $carpetaPlantillas, $nombreFichero);
            //Al mover o copiar el fichero no le aplica las ACL por defecto. Por lo tanto las copiamos.
            $cmd = "getfacl '" . $rutaGestionDocumental . "PERMISSIONS' | setfacl --set-file=- '" . $rutaGestionDocumental . $carpetaPlantillas . "/" . $nombreFichero . "'";
            exec($cmd);

            $plantilla->setNombreCompleto($nombreFichero);
            $plantilla->setUsuario($usuario);
            $plantilla->setDtcrea(new \DateTime());
            $plantilla->setAnulado(false);
            $plantilla->setMedia(null);

            //Comprobamos si la plantilla ya estaba subida
            $plantillaBusca = $em->getRepository('App\Entity\GdocPlantillas')->findOneBy(array('nombreCompleto' => $nombreFichero, 'anulado' => false));
            if (!is_null($plantillaBusca)) {
                $plantillaBusca->setUsuarioModifica($usuario);
                $plantillaBusca->setDtmodifica(new \DateTime());
                $em->persist($plantillaBusca);
                $em->remove($plantilla);
                $em->flush();
            } else {
                $em->persist($plantilla);
                $em->flush();
            }
            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('gdoc_index');
        }
        $object = array("json" => $username, "entidad" => "plantillas", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('gdoc/show.html.twig', array('form' => $form->createView(), 'tree' => $data, 'carpetas' => $carpetas));
    }

    public function addCarpeta(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $nombre = $_REQUEST['nombre'];
        $padreId = $_REQUEST['padre'];
        $tipo = $_REQUEST['tipo'];
        $carpetaPadre = null;

        switch ($tipo) {
                //Plantillas
            case 1:
                if (!is_null($padreId) && $padreId != "") {
                    $carpetaPadre = $em->getRepository('App\Entity\GdocPlantillasCarpeta')->find($padreId);
                }
                $newCarpeta = new GdocPlantillasCarpeta();
                break;

                //Trabajadores
            case 2:
                if (!is_null($padreId) && $padreId != "") {
                    $carpetaPadre = $em->getRepository('App\Entity\GdocTrabajadorCarpeta')->find($padreId);
                }
                $trabajadorId = $_REQUEST['trabajadorId'];
                $trabajador = $em->getRepository('App\Entity\Trabajador')->find($trabajadorId);

                $newCarpeta = new GdocTrabajadorCarpeta();
                $newCarpeta->setTrabajador($trabajador);
                break;

                //Empresas
            case 3:
                if (!is_null($padreId) && $padreId != "") {
                    $carpetaPadre = $em->getRepository('App\Entity\GdocEmpresaCarpeta')->find($padreId);
                }
                $empresaId = $_REQUEST['empresaId'];
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

                $newCarpeta = new GdocEmpresaCarpeta();
                $newCarpeta->setEmpresa($empresa);
                break;
        }
        $newCarpeta->setNombre($nombre);

        if (!is_null($carpetaPadre)) {
            $newCarpeta->setPadre($carpetaPadre);
        }
        $em->persist($newCarpeta);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success',  $traduccion);

        return new JsonResponse($data);
    }

    public function viewPlantilla(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $tipo = $_REQUEST['tipo'];
        $fileId = $_REQUEST['id'];

        //Buscamos la configuración de la gestión documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $rutaGestionDocumental = $gdocConfig->getRuta();
        $plantillas = $gdocConfig->getCarpetaPlantillas();
        $carpetaTrabajador = $gdocConfig->getCarpetaTrabajador();
        $carpetaEmpresa = $gdocConfig->getCarpetaEmpresa();

        $pdfSn = false;
        switch ($tipo) {
                //Plantilla
            case 1:
                $file = $em->getRepository('App\Entity\GdocPlantillas')->find($fileId);
                $nombreFichero = $file->getNombreCompleto();
                $rutaFichero = $rutaGestionDocumental . $plantillas . '/' . $nombreFichero;
                if (str_contains($nombreFichero, '.pdf')) {
                    $pdfSn = true;
                }
                break;

                //Trabajador
            case 2:
                $trabajadorId = $_REQUEST['trabajadorId'];
                $file = $em->getRepository('App\Entity\GdocTrabajador')->find($fileId);
                $nombreFichero = $file->getNombreCompleto();
                $rutaFichero = $rutaGestionDocumental . $carpetaTrabajador . '/' . $trabajadorId . '/' . $nombreFichero;
                break;

                //Empresa
            case 3:
                $empresaId = $_REQUEST['empresaId'];
                $file = $em->getRepository('App\Entity\GdocEmpresa')->find($fileId);
                $nombreFichero = $file->getNombreCompleto();
                $rutaFichero = $rutaGestionDocumental . $carpetaEmpresa . '/' . $empresaId . '/' . $nombreFichero;
                break;
        }

        $data = array(
            'name' => $file->getNombre(),
            'fecha' => $file->getDtcrea()->format('d/m/Y H:i'),
            'usuario' => $file->getUsuario()->getUsername(),
            'tipo' => mime_content_type($rutaFichero),
            'mida' => $this->humanFileSize(filesize($rutaFichero)),
            'id' => $file->getId(),
            'down' => $fileId,
            'open' => $fileId,
            'pdf' => $pdfSn
        );
        $object = array("json" => $username, "entidad" => "ver fichero:" . ' ' . $fileId, "id" => $id);

        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "view", $object, $usuario, TRUE);
        $em->flush();

        return new JsonResponse($data);
    }

    public function downPlantilla(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $fileId = $_REQUEST['plantillaId'];
        $tipo = $_REQUEST['tipo'];

        // Buscamos la configuración de la gestión documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);

        $rutaGestionDocumental = $gdocConfig->getRuta();
        $plantillas = $gdocConfig->getCarpetaPlantillas();
        $carpetaTrabajador = $gdocConfig->getCarpetaTrabajador();
        $carpetaEmpresa = $gdocConfig->getCarpetaEmpresa();

        switch ($tipo) {
                // Plantilla
            case 1:
                $file = $em->getRepository('App\Entity\GdocPlantillas')->find($fileId);
                $nombreFichero = $file->getNombreCompleto();
                $rutaFichero = $rutaGestionDocumental . $plantillas . '/' . $nombreFichero;
                break;

                // Trabajador
            case 2:
                $trabajadorId = $_REQUEST['trabajadorId'];
                $file = $em->getRepository('App\Entity\GdocTrabajador')->find($fileId);
                $nombreFichero = $file->getNombreCompleto();
                $rutaFichero = $rutaGestionDocumental . $carpetaTrabajador . '/' . $trabajadorId . '/' . $nombreFichero;
                break;

                // Empresa
            case 3:
                $empresaId = $_REQUEST['empresaId'];
                $file = $em->getRepository('App\Entity\GdocEmpresa')->find($fileId);
                $nombreFichero = $file->getNombreCompleto();
                $rutaFichero = $rutaGestionDocumental . $carpetaEmpresa . '/' . $empresaId . '/' . $nombreFichero;
                break;
        }
        $fileDown = file_get_contents($rutaFichero, true);

        $response = new Response($fileDown);

        $filenameFallback = str_replace(['%', '/', '\\'], '_', $nombreFichero);
        $filenameFallback = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filenameFallback);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $nombreFichero,
            $filenameFallback
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/octet-stream');

        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        return $response;
    }

    public function deletePlantilla(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $fileId = $_REQUEST['id'];
        $tipo = $_REQUEST['tipo'];

        switch ($tipo) {
                // Plantillas
            case 1:
                $file = $em->getRepository('App\Entity\GdocPlantillas')->find($fileId);
                break;

                // Trabajador
            case 2:
                $file = $em->getRepository('App\Entity\GdocTrabajador')->find($fileId);
                break;

                // Empresa
            case 3:
                $file = $em->getRepository('App\Entity\GdocEmpresa')->find($fileId);
                break;
        }
        $file->setAnulado(true);
        $em->persist($file);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function deleteFile(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        //Anulamos el registro
        $fileId = $_REQUEST['id'];
        $file = $em->getRepository('App\Entity\GdocFichero')->find($fileId);
        $file->setAnulado(true);
        $em->persist($file);
        $em->flush();

        //Buscamos la configuración de la gestión documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $rutaGestionDocumental = $gdocConfig->getRuta();
        $carpetaGeneradas = $gdocConfig->getCarpetaGenerada();

        //tipo == 1 es CONTRATO
        //tipo == 2 es FACTURA
        //tipo == 3 es NOTIFICACION
        //tipo == 4 es CERTIFICACION
        //tipo == 5 es MODELO347
        //tipo == 6 es ACCIDENTE
        //tipo == 7 es EVALUACION
        //tipo == 8 es PLAN PREVENCION
        //tipo == 9 es CITACION/CERTIFICADO DE ASISTENCIA
        //tipo == 10 es CUESTIONARIO REVISION
        //tipo == 11 es CERTIFICADO APTITUD
        //tipo == 13 es RESUMEN REVISION
        //tipo == 14 es REVISION MEDICA
        //tipo == 15 es MEMORIA
        //tipo == 16 es ESTUDIO EPIDEMIOLOGICO
        //tipo == 17 es RESTRICCION APTITUD
        //tipo == 18 es MANUAL VS
        //tipo == 19 es PROTOCOLO ACOSO
        $tipo = $_REQUEST['tipo'];

        switch ($tipo) {
            case '1':
                $fileContrato = $em->getRepository('App\Entity\Contrato')->findOneBy(array('fichero' => $file, 'anulado' => false));
                $fileContrato->setFichero(null);
                $em->persist($fileContrato);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaContrato();
                break;
            case '2':
                $fileFactura = $em->getRepository('App\Entity\Facturacion')->findOneBy(array('fichero' => $file, 'anulado' => false));
                $fileFactura->setFichero(null);
                $em->persist($fileFactura);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaFactura();
                break;
            case '3':
                $fileNotificacion = $em->getRepository('App\Entity\EmpresaNotificacion')->findOneBy(array('fichero' => $file));
                $em->remove($fileNotificacion);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaNotificacion();
                break;
            case '4':
                $fileCertificacion = $em->getRepository('App\Entity\EmpresaCertificacion')->findOneBy(array('fichero' => $file));
                $em->remove($fileCertificacion);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaCertificacion();
                break;
            case '5':
                $fileModelo347 = $em->getRepository('App\Entity\EmpresaModelo347')->findOneBy(array('fichero' => $file));
                $em->remove($fileModelo347);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaModelo347();
                break;
            case '6':
                $fileAccidente = $em->getRepository('App\Entity\EmpresaAccidenteLaboral')->findOneBy(array('fichero' => $file, 'anulado' => false));
                $em->remove($fileAccidente);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaAccidente();
                break;
            case '7':
                $fileEvaluacion = $em->getRepository('App\Entity\Evaluacion')->findOneBy(array('fichero' => $file, 'anulado' => false));
                $fileEvaluacion->setFichero(null);
                $em->persist($fileEvaluacion);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaEvaluacion();
                break;
            case '8':
                $filePlanPrevencion = $em->getRepository('App\Entity\EmpresaPlanPrevencion')->findOneBy(array('fichero' => $file));
                $em->remove($filePlanPrevencion);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaPlanPrevencion();
                break;
            case '11':
                $fileCertificadoAptitud = $em->getRepository('App\Entity\Revision')->findOneBy(array('fichero' => $file));
                $fileCertificadoAptitud->setFichero(null);
                $em->persist($fileCertificadoAptitud);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaAptitud();
                break;
            case '13':
                $fileResumenRevision = $em->getRepository('App\Entity\Revision')->findOneBy(array('ficheroResumen' => $file));
                $fileResumenRevision->setFicheroResumen(null);
                $em->persist($fileResumenRevision);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaRevision();
                break;
            case '14':
                $fileRevisionMedica = $em->getRepository('App\Entity\Revision')->findOneBy(array('ficheroRevisionMedica' => $file));
                $fileRevisionMedica->setFicheroRevisionMedica(null);
                $em->persist($fileRevisionMedica);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaRevision();
                break;
            case '15':
                $fileMemoria = $em->getRepository('App\Entity\EmpresaMemoria')->findOneBy(array('fichero' => $file));
                $em->remove($fileMemoria);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaMemoria();
                break;
            case '16':
                $fileEstudio = $em->getRepository('App\Entity\EmpresaEstudioEpidemiologico')->findOneBy(array('fichero' => $file));
                $em->remove($fileEstudio);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaEstudioEpidemiologico();
                break;
            case '18':
                $fileManualVs = $em->getRepository('App\Entity\EmpresaManualVs')->findOneBy(array('fichero' => $file));
                $em->remove($fileManualVs);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaManualVs();
                break;
            case '19':
                $fileProtocoloAcoso = $em->getRepository('App\Entity\EmpresaProtocoloAcoso')->findOneBy(array('fichero' => $file));
                $em->remove($fileProtocoloAcoso);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaProtocoloAcoso();
                break;
        }
        $nombreFichero = $file->getNombre();
        $fileDelete = $rutaGestionDocumental . $carpetaGeneradas . '/' . $nombreFichero;

        $data = array();
        array_push($data, "OK");

        unlink($fileDelete);

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        return new JsonResponse($data);
    }

    // Peticio 28/07/2023
    public function deleteFile2(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        //Anulamos el registro
        $fileId = $_REQUEST['id'];
        $file = $em->getRepository('App\Entity\GdocFichero')->find($fileId);
        $file->setAnulado(true);
        $em->persist($file);
        $em->flush();

        //Buscamos la configuración de la gestión documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $rutaGestionDocumental = $gdocConfig->getRuta();
        $carpetaGeneradas = $gdocConfig->getCarpetaGenerada();

        //tipo == 1 es CONTRATO
        //tipo == 2 es FACTURA
        //tipo == 3 es NOTIFICACION
        //tipo == 4 es CERTIFICACION
        //tipo == 5 es MODELO347
        //tipo == 6 es ACCIDENTE
        //tipo == 7 es EVALUACION
        //tipo == 8 es PLAN PREVENCION
        //tipo == 9 es CITACION/CERTIFICADO DE ASISTENCIA
        //tipo == 10 es CUESTIONARIO REVISION
        //tipo == 11 es CERTIFICADO APTITUD
        //tipo == 13 es RESUMEN REVISION
        //tipo == 14 es REVISION MEDICA
        //tipo == 15 es MEMORIA
        //tipo == 16 es ESTUDIO EPIDEMIOLOGICO
        //tipo == 17 es RESTRICCION APTITUD
        //tipo == 18 es MANUAL VS
        //tipo == 19 es PROTOCOLO ACOSO
        $tipo = $_REQUEST['tipo'];

        switch ($tipo) {
            case '1':
                $fileContrato = $em->getRepository('App\Entity\Contrato')->findOneBy(array('fichero' => $file, 'anulado' => false));
                $fileContrato->setFichero(null);
                $em->persist($fileContrato);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaContrato();
                break;
            case '2':
                $fileFactura = $em->getRepository('App\Entity\Facturacion')->findOneBy(array('fichero' => $file, 'anulado' => false));
                $fileFactura->setFichero(null);
                $em->persist($fileFactura);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaFactura();
                break;
            case '3':
                $fileNotificacion = $em->getRepository('App\Entity\EmpresaNotificacion')->findOneBy(array('fichero' => $file));
                $em->remove($fileNotificacion);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaNotificacion();
                break;
            case '4':
                $fileCertificacion = $em->getRepository('App\Entity\EmpresaCertificacion')->findOneBy(array('fichero' => $file));
                $em->remove($fileCertificacion);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaCertificacion();
                break;
            case '5':
                $fileModelo347 = $em->getRepository('App\Entity\EmpresaModelo347')->findOneBy(array('fichero' => $file));
                $em->remove($fileModelo347);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaModelo347();
                break;
            case '6':
                $fileAccidente = $em->getRepository('App\Entity\EmpresaAccidenteLaboral')->findOneBy(array('fichero' => $file, 'anulado' => false));
                $em->remove($fileAccidente);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaAccidente();
                break;
            case '7':
                $fileEvaluacion = $em->getRepository('App\Entity\Evaluacion')->findOneBy(array('fichero_centro' => $fileId, 'anulado' => false));
                $fileEvaluacion->setFicheroCentro(null);
                $em->persist($fileEvaluacion);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaEvaluacion();
                break;
            case '8':
                $filePlanPrevencion = $em->getRepository('App\Entity\EmpresaPlanPrevencion')->findOneBy(array('fichero' => $file));
                $em->remove($filePlanPrevencion);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaPlanPrevencion();
                break;
            case '11':
                $fileCertificadoAptitud = $em->getRepository('App\Entity\Revision')->findOneBy(array('fichero' => $file));
                $fileCertificadoAptitud->setFichero(null);
                $em->persist($fileCertificadoAptitud);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaAptitud();
                break;
            case '13':
                $fileResumenRevision = $em->getRepository('App\Entity\Revision')->findOneBy(array('ficheroResumen' => $file));
                $fileResumenRevision->setFicheroResumen(null);
                $em->persist($fileResumenRevision);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaRevision();
                break;
            case '14':
                $fileRevisionMedica = $em->getRepository('App\Entity\Revision')->findOneBy(array('ficheroRevisionMedica' => $file));
                $fileRevisionMedica->setFicheroRevisionMedica(null);
                $em->persist($fileRevisionMedica);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaRevision();
                break;
            case '15':
                $fileMemoria = $em->getRepository('App\Entity\EmpresaMemoria')->findOneBy(array('fichero' => $file));
                $em->remove($fileMemoria);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaMemoria();
                break;
            case '16':
                $fileEstudio = $em->getRepository('App\Entity\EmpresaEstudioEpidemiologico')->findOneBy(array('fichero' => $file));
                $em->remove($fileEstudio);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaEstudioEpidemiologico();
                break;
            case '18':
                $fileManualVs = $em->getRepository('App\Entity\EmpresaManualVs')->findOneBy(array('fichero' => $file));
                $em->remove($fileManualVs);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaManualVs();
                break;
            case '19':
                $fileProtocoloAcoso = $em->getRepository('App\Entity\EmpresaProtocoloAcoso')->findOneBy(array('fichero' => $file));
                $em->remove($fileProtocoloAcoso);
                $em->flush();
                $carpetaGeneradas = $gdocConfig->getCarpetaProtocoloAcoso();
                break;
        }
        $nombreFichero = $file->getNombre();
        $fileDelete = $rutaGestionDocumental . $carpetaGeneradas . '/' . $nombreFichero;

        $data = array();
        array_push($data, "OK");

        unlink($fileDelete);

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        return new JsonResponse($data);
    }

    public function recuperaCarpeta(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $carpetaId = $_REQUEST['id'];
        $tipo = $_REQUEST['tipo'];

        switch ($tipo) {
                //Plantillas
            case 1:
                $carpeta = $em->getRepository('App\Entity\GdocPlantillasCarpeta')->find($carpetaId);
                break;
                //Trabajadores
            case 2:
                $carpeta = $em->getRepository('App\Entity\GdocTrabajadorCarpeta')->find($carpetaId);
                break;
                //Empresas
            case 3:
                $carpeta = $em->getRepository('App\Entity\GdocEmpresaCarpeta')->find($carpetaId);
                break;
        }
        $nombre = $carpeta->getNombre();

        $padre = null;
        if (!is_null($carpeta->getPadre())) {
            $padre =  $carpeta->getPadre()->getId();
        }
        $data = array(
            'id' => $carpeta->getId(),
            'nombre' => $nombre,
            'padre'  => $padre
        );
        return new JsonResponse($data);
    }

    public function updateCarpeta(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $carpetaId = $_REQUEST['id'];
        $nombre = $_REQUEST['nombre'];
        $padreId = $_REQUEST['padre'];
        $tipo = $_REQUEST['tipo'];
        $carpetaPadre = null;

        switch ($tipo) {
            case 1:
                $carpeta = $em->getRepository('App\Entity\GdocPlantillasCarpeta')->find($carpetaId);
                if (!is_null($padreId) && $padreId != "") {
                    $carpetaPadre = $em->getRepository('App\Entity\GdocPlantillasCarpeta')->find($padreId);
                }
                break;
            case 2:
                $carpeta = $em->getRepository('App\Entity\GdocTrabajadorCarpeta')->find($carpetaId);
                if (!is_null($padreId) && $padreId != "") {
                    $carpetaPadre = $em->getRepository('App\Entity\GdocTrabajadorCarpeta')->find($padreId);
                }
                break;
            case 3:
                $carpeta = $em->getRepository('App\Entity\GdocEmpresaCarpeta')->find($carpetaId);
                if (!is_null($padreId) && $padreId != "") {
                    $carpetaPadre = $em->getRepository('App\Entity\GdocEmpresaCarpeta')->find($padreId);
                }
                break;
        }
        $carpeta->setNombre($nombre);

        if (!is_null($carpetaPadre)) {
            $carpeta->setPadre($carpetaPadre);
        }
        $em->persist($carpeta);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        $traduccion = $translator->trans('TRANS_UPDATE_OK');
        $this->addFlash('success',  $traduccion);

        return new JsonResponse($data);
    }

    public function deleteCarpeta(Request $request,  TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $carpetaId = $_REQUEST['id'];
        $tipo = $_REQUEST['tipo'];

        switch ($tipo) {
            case 1:
                $carpeta = $em->getRepository('App\Entity\GdocPlantillasCarpeta')->find($carpetaId);
                break;
            case 2:
                $carpeta = $em->getRepository('App\Entity\GdocTrabajadorCarpeta')->find($carpetaId);
                break;
            case 3:
                $carpeta = $em->getRepository('App\Entity\GdocEmpresaCarpeta')->find($carpetaId);
                break;
        }


        $carpeta->setAnulado(true);
        $em->persist($carpeta);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        return new JsonResponse($data);
    }

    public function openPlantilla(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $user = $this->getUser();
        $usuario = $em->getRepository('App\Entity\User')->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        //Buscamos la configuración de la gestión documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $rutaGestionDocumental = $gdocConfig->getRuta();
        $url = $gdocConfig->getUrl();
        $host = $gdocConfig->getHost();
        $plantillas = $gdocConfig->getCarpetaPlantillas();

        $plantillaId = $_REQUEST['plantillaId'];
        $tipo = $_REQUEST['tipo'];

        switch ($tipo) {
                //Plantillas
            case 1:
                $plantilla = $em->getRepository('App\Entity\GdocPlantillas')->find($plantillaId);
                $nombreCompleto = $plantilla->getNombreCompleto();
                $urlPlantilla = $rutaGestionDocumental . $plantillas . '/' . $nombreCompleto;
                break;

                //Trabajador
            case 2:
                $trabajadorId = $_REQUEST['trabajadorId'];
                $plantilla = $em->getRepository('App\Entity\GdocTrabajador')->find($plantillaId);
                $nombreCompleto = $plantilla->getNombreCompleto();
                $carpetaGenerada = $gdocConfig->getCarpetaTrabajador();
                $urlPlantilla = $rutaGestionDocumental . $carpetaGenerada . '/' . $trabajadorId . '/' . $nombreCompleto;
                break;

                //Empresa
            case 3:
                $empresaId = $_REQUEST['empresaId'];
                $plantilla = $em->getRepository('App\Entity\GdocEmpresa')->find($plantillaId);
                $nombreCompleto = $plantilla->getNombreCompleto();
                $carpetaGenerada = $gdocConfig->getCarpetaEmpresa();
                $urlPlantilla = $rutaGestionDocumental . $carpetaGenerada . '/' . $empresaId . '/' . $nombreCompleto;
                break;
        }

        $object = array("json" => $username, "entidad" => "abrir plantilla:" . ' ' . $plantillaId, "id" => $id);

        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "open", $object, $usuario, TRUE);
        $em->flush();

        return new RedirectResponse($url . '?file_path=file://' . $urlPlantilla . '&host=' . $host);
    }

    public function openFile(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $usuario = $em->getRepository('App\Entity\User')->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();
        $filtreSemafor = $_REQUEST['fileId'];
        $fileId = $_REQUEST['fileId'];
        if ($fileId == 1) {
        } else {
            $fichero = $em->getRepository('App\Entity\GdocFichero')->find($fileId);
            $nombreFichero = $fichero->getNombre();

            // Buscamos la configuración de la gestión documental
            $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
            $rutaGestionDocumental = $gdocConfig->getRuta();
            $url = $gdocConfig->getUrl();
            $host = $gdocConfig->getHost();

            $tipo = $_REQUEST['tipo'];

            // tipo == 1 es CONTRATO
            // tipo == 2 es FACTURA
            // tipo == 3 es NOTIFICACION
            // tipo == 4 es CERTIFICACION
            // tipo == 5 es MODELO347
            // tipo == 6 es ACCIDENTE
            // tipo == 7 es EVALUACION
            // tipo == 8 es PLAN DE PREVENCION
            // tipo == 9 es CITACION/CERTIFICADO DE ASISTENCIA
            // tipo == 10 es CUESTIONARIO REVISION
            // tipo == 11 es CERTIFICADO APTITUD
            // tipo == 13 es RESUMEN REVISION
            // tipo == 14 es REVISION MEDICA
            // tipo == 15 es MEMORIA
            // tipo == 16 es ESTUDIO EPIDEMIOLOGICO
            // tipo == 17 es RESTRICCION APTITUD
            // tipo == 18 es MANUAL VS
            // tipo == 19 es PROTOCOLO ACOSO

            switch ($tipo) {
                case '1':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaContrato();
                    break;
                case '2':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaFactura();
                    break;
                case '3':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaNotificacion();
                    break;
                case '4':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaCertificacion();
                    break;
                case '5':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaModelo347();
                    break;
                case '6':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaAccidente();
                    break;
                case '7':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaEvaluacion();
                    break;
                case '8':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaPlanPrevencion();
                    break;
                case '9':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaCitacion();
                    break;
                case '10':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaRevision();
                    break;
                case '11':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaAptitud();
                    break;
                case '12':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaFichaRiesgos();
                    break;
                case '13':
                case '14':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaRevision();
                    break;
                case '15':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaMemoria();
                    break;
                case '16':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaEstudioEpidemiologico();
                    break;
                case '17':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaRevision();
                    break;
                case '18':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaManualVs();
                    break;
                case '19':
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaProtocoloAcoso();
                    break;
                default:
                    $carpetaPlantillaGenerada = $gdocConfig->getCarpetaGenerada();
            }
            $object = array("json" => $username, "entidad" => "abrir fichero:" . ' ' . $fileId, "id" => $id);

            $logger = new Logger();
            $em = $this->getDoctrine()->getManager();
            $logger->addLog($em, "open", $object, $usuario, TRUE);
            $em->flush();

            // RUTA LOCAL
            // return new RedirectResponse($url.'?file_path=file://'.'C:\Users\david.jimenez\Desktop\Versiones codigo\CARPETAS PROVES LIBREOFFICE PREVENCIÓ\NOVA CARPETA'.'/'.$nombreFichero.'&host='.$host);

            // RUTA PROD
            return new RedirectResponse($url . '?file_path=file://' . $rutaGestionDocumental . $carpetaPlantillaGenerada . '/' . $nombreFichero . '&host=' . $host);
        }
    }

    // Peticio 28/07/2023
    public function openFileSoloCentros(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $usuario = $em->getRepository('App\Entity\User')->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();
        $fileId = $_REQUEST['fileId'];

        $fichero = $em->getRepository('App\Entity\GdocFichero')->find($fileId);
        $nombreFichero = $fichero->getNombre();
        $empresaAux = $fichero->getEmpresa();
        $empresaId = $empresaAux->getId();
        $palabra = "Evaluacion solo centros de";
        if (strpos($nombreFichero, $palabra) !== false) {

            $query = "select id,nombre from gdoc_fichero where anulado = false and empresa_id = $empresaId and nombre like '%Evaluacion solo centros de%'";

            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $plantilla = $stmt->fetchAll();
            $nombreAux = $plantilla[0]['nombre'];
            $fileIdAux = $plantilla[0]['id'];
        }

        //Buscamos la configuración de la gestión documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $rutaGestionDocumental = $gdocConfig->getRuta();
        $url = $gdocConfig->getUrl();
        $host = $gdocConfig->getHost();

        $tipo = $_REQUEST['tipo'];

        //tipo == 1 es CONTRATO
        //tipo == 2 es FACTURA
        //tipo == 3 es NOTIFICACION
        //tipo == 4 es CERTIFICACION
        //tipo == 5 es MODELO347
        //tipo == 6 es ACCIDENTE
        //tipo == 7 es EVALUACION
        //tipo == 8 es PLAN DE PREVENCION
        //tipo == 9 es CITACION/CERTIFICADO DE ASISTENCIA
        //tipo == 10 es CUESTIONARIO REVISION
        //tipo == 11 es CERTIFICADO APTITUD
        //tipo == 13 es RESUMEN REVISION
        //tipo == 14 es REVISION MEDICA
        //tipo == 15 es MEMORIA
        //tipo == 16 es ESTUDIO EPIDEMIOLOGICO
        //tipo == 17 es RESTRICCION APTITUD
        //tipo == 18 es MANUAL VS
        //tipo == 19 es PROTOCOLO ACOSO
        switch ($tipo) {
            case '1':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaContrato();
                break;
            case '2':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaFactura();
                break;
            case '3':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaNotificacion();
                break;
            case '4':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaCertificacion();
                break;
            case '5':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaModelo347();
                break;
            case '6':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaAccidente();
                break;
            case '7':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaEvaluacion();
                break;
            case '8':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaPlanPrevencion();
                break;
            case '9':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaCitacion();
                break;
            case '10':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaRevision();
                break;
            case '11':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaAptitud();
                break;
            case '12':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaFichaRiesgos();
                break;
            case '13':
            case '14':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaRevision();
                break;
            case '15':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaMemoria();
                break;
            case '16':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaEstudioEpidemiologico();
                break;
            case '17':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaRevision();
                break;
            case '18':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaManualVs();
                break;
            case '19':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaProtocoloAcoso();
                break;
            default:
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaGenerada();
        }

        $object = array("json" => $username, "entidad" => "abrir fichero:" . ' ' . $fileIdAux, "id" => $id);


        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "open", $object, $usuario, TRUE);
        $em->flush();
        //RUTA LOCAL
        //return new RedirectResponse($url.'?file_path=file://'.'C:\Users\david.jimenez\Desktop\Versiones codigo\CARPETAS PROVES LIBREOFFICE PREVENCIÓ\NOVA CARPETA'.'/'.$nombreFichero.'&host='.$host);

        //RUTA PROD
        return new RedirectResponse($url . '?file_path=file://' . $rutaGestionDocumental . $carpetaPlantillaGenerada . '/' . $nombreAux . '&host=' . $host);
    }

    // Peticio 28/07/2023
    public function generarFileSoloCentro(Request $request,  TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $user = $this->getUser();
        $usuario = $em->getRepository('App\Entity\User')->find($user);
        $puestosTrabajoSeleccionadosSelect2 = $_REQUEST['select2'];
        $plantillaId = $_REQUEST['plantillaId'];
        $plantilla = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->find($plantillaId);
        $nombreCompleto = $plantilla->getNombreCompleto();
        $nombrePlantilla = $plantilla->getNombre();

        //Buscamos la configuración de la gestión documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $rutaGestionDocumental = $gdocConfig->getRuta();
        $carpetaPlantillaGenerada = $gdocConfig->getCarpetaGenerada();
        $carpetaPlantillas = $gdocConfig->getCarpetaPlantillas();
        $url = $gdocConfig->getUrl();
        $host = $gdocConfig->getHost();

        //tipo == 1 es CONTRATO
        //tipo == 2 es FACTURA
        //tipo == 3 es NOTIFICACION
        //tipo == 4 es CERTIFICACION
        //tipo == 5 es MODELO347
        //tipo == 6 es ACCIDENTE
        //tipo == 7 es EVALUACION
        //tipo == 8 es PLAN DE PREVENCION
        //tipo == 9 es CITACION/CERTIFICADO DE ASISTENCIA
        //tipo == 10 es CUESTIONARIO REVISION
        //tipo == 11 es CERTIFICADO APTITUD
        //tipo == 12 es FICHA DE RIESGOS
        //tipo == 13 es RESUMEN REVISION
        //tipo == 14 es REVISION MEDICA
        //tipo == 15 es MEMORIA
        //tipo == 16 es ESTUDIO EPIDEMIOLOGICO
        //tipo == 17 es RESTRICCION APTITUD
        //tipo == 18 es MANUAL VS
        //tipo == 19 es PROTOCOLO ACOSO
        $tipo = $_REQUEST['tipo'];

        $contrato = null;
        $factura = null;
        $trabajador = null;
        $evaluacion = null;
        $citacion = null;
        $revision = null;
        $puestoTrabajoEvaluacion = null;
        $anyoMemoriaEstudio = null;
        $idiomaPlantilla = null;

        if (str_contains($nombrePlantilla, 'CAT')) {
            $idiomaPlantilla = 'CAT';
        } else {
            $idiomaPlantilla = 'ESP';
        }
        $hoy = new \DateTime();
        $hoyString = $hoy->format('YmdHis');

        switch ($tipo) {
            case '1':
                $contratoId = $_REQUEST['id'];
                $contrato = $em->getRepository('App\Entity\Contrato')->find($contratoId);
                $empresa = $contrato->getEmpresa();
                $numContrato = str_replace('/', '-', $contrato->getContrato());
                $nombreEmpresa = $empresa->getEmpresa();
                $nombreEmpresa = rtrim($nombreEmpresa, ".");
                $nombreEmpresa = $this->eliminar_tildes($nombreEmpresa);
                $nuevaPlantilla = 'Contrato ' . $numContrato . ' de ' . $nombreEmpresa . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaContrato();
                break;
            case '2':
                $facturaId = $_REQUEST['id'];
                $factura = $em->getRepository('App\Entity\Facturacion')->find($facturaId);
                $empresa = $factura->getEmpresa();
                $nombreEmpresa = $empresa->getEmpresa();
                $nombreEmpresa = rtrim($nombreEmpresa, ".");
                $nombreEmpresa = $this->eliminar_tildes($nombreEmpresa);
                $numFac = $factura->getSerie()->getSerie() . $factura->getNumFac();
                $numFac = str_replace('/', '-', $numFac);
                $nuevaPlantilla = 'Factura ' . $numFac . ' de ' . $nombreEmpresa . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaFactura();
                break;
            case '3':
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $nombreEmpresa = $empresa->getEmpresa();
                $nombreEmpresa = rtrim($nombreEmpresa, ".");
                $nombreEmpresa = $this->eliminar_tildes($nombreEmpresa);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreEmpresa . ' ' . $hoyString . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaNotificacion();
                break;
            case '4':
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $nombreEmpresa = $empresa->getEmpresa();
                $nombreEmpresa = rtrim($nombreEmpresa, ".");
                $nombreEmpresa = $this->eliminar_tildes($nombreEmpresa);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreEmpresa . ' ' . $hoyString . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaCertificacion();
                break;
            case '5':
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $nombreEmpresa = $empresa->getEmpresa();
                $nombreEmpresa = rtrim($nombreEmpresa, ".");
                $nombreEmpresa = $this->eliminar_tildes($nombreEmpresa);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreEmpresa . ' ' . $hoyString . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaModelo347();
                break;
            case '6':
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $trabajadorId = $_REQUEST['id'];
                $trabajador = $em->getRepository('App\Entity\Trabajador')->find($trabajadorId);
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < 10; $i++) {
                    $randomString .= $characters[rand(0, $charactersLength - 1)];
                }
                $nuevaPlantilla = $nombrePlantilla . ' ' . $randomString . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaAccidente();

                $fecha = $_REQUEST['fecha'];
                $fechaAccidenteLaboral = new \DateTime($fecha);
                break;
            case '7':
                $evaluacionId = $_REQUEST['id'];
                $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($evaluacionId);
                $empresa = $evaluacion->getEmpresa();
                $nombreEmpresa = $empresa->getEmpresa();
                $nombreEmpresa = rtrim($nombreEmpresa, ".");
                $nombreEmpresa = $this->eliminar_tildes($nombreEmpresa);
                $fechaEvaluacion = $evaluacion->getFechaInicio()->format('dmY');
                $nuevaPlantilla = 'Evaluacion solo centros de ' . $nombreEmpresa . ' ' . $fechaEvaluacion . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaEvaluacion();
                break;
            case '8':
                $empresaId = $_REQUEST['id'];
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $nombreEmpresa = $empresa->getEmpresa();
                $nombreEmpresa = rtrim($nombreEmpresa, ".");
                $nombreEmpresa = $this->eliminar_tildes($nombreEmpresa);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $hoyString . ' ' . $nombreEmpresa . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaPlanPrevencion();

                $fecha = $_REQUEST['fecha'];
                $fechaPlanPrevencion = new \DateTime($fecha);
                break;
            case '9':
                $citacionId = $_REQUEST['id'];
                $citacion = $em->getRepository('App\Entity\Citacion')->find($citacionId);
                $empresa = $citacion->getEmpresa();
                $nombreTrabajador = $citacion->getTrabajador()->getNombre();
                $nombreTrabajador = $this->eliminar_tildes($nombreTrabajador);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreTrabajador . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaCitacion();
                break;
            case '10':
                $revisionId = $_REQUEST['id'];
                $revision = $em->getRepository('App\Entity\Revision')->find($revisionId);
                $trabajador = $revision->getTrabajador();
                $empresa = $revision->getEmpresa();
                $nombreTrabajador = $trabajador->getNombre();
                $dniTrabajador = $trabajador->getDni();
                $nombreTrabajador = $this->eliminar_tildes($nombreTrabajador);
                $fechaRevision = $revision->getFecha()->format('dmY');
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreTrabajador . ' ' . $dniTrabajador . ' ' . $fechaRevision . ' ' . $revisionId . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaRevision();
                break;
            case '11':
                $revisionId = $_REQUEST['id'];
                $revision = $em->getRepository('App\Entity\Revision')->find($revisionId);
                $trabajador = $revision->getTrabajador();
                $empresa = $revision->getEmpresa();
                $nombreTrabajador = $trabajador->getNombre();
                $dniTrabajador = $trabajador->getDni();
                $nombreTrabajador = $this->eliminar_tildes($nombreTrabajador);
                $fechaAptitud = "";
                if (!is_null($revision->getFechaCertificacion())) {
                    $fechaAptitud = $revision->getFechaCertificacion()->format('dmY');
                }
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreTrabajador . ' ' . $dniTrabajador . ' ' . $hoyString . ' ' . $revisionId . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaAptitud();
                break;
            case '12':
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $puestoEvaluarId = $_REQUEST['id'];
                $puestoTrabajoEvaluacion = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoEvaluacion')->find($puestoEvaluarId);
                $puestoTrabajo = $puestoTrabajoEvaluacion->getPuestoTrabajo()->getDescripcion();
                $evaluacion = $puestoTrabajoEvaluacion->getEvaluacion();
                $fechaEvaluacion = $evaluacion->getFechaInicio()->format('dmY');
                $puestoTrabajo = str_replace('/', '-', $puestoTrabajo);
                $puestoTrabajo = str_replace('\\', '-', $puestoTrabajo);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $puestoTrabajo . ' ' . $fechaEvaluacion . ' ' . $puestoEvaluarId . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaFichaRiesgos();
                break;
            case '13':
                $revisionId = $_REQUEST['id'];
                $revision = $em->getRepository('App\Entity\Revision')->find($revisionId);
                $trabajador = $revision->getTrabajador();
                $empresa = $revision->getEmpresa();
                $nombreTrabajador = $trabajador->getNombre();
                $dniTrabajador = $trabajador->getDni();
                $fecha = $revision->getFecha()->format('dmY');
                $nombreTrabajador = $this->eliminar_tildes($nombreTrabajador);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreTrabajador . ' ' . $dniTrabajador . ' ' . $fecha . ' ' . $revisionId . '.docx';;
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaRevision();
                break;
            case '14':
                $revisionId = $_REQUEST['id'];
                $revision = $em->getRepository('App\Entity\Revision')->find($revisionId);
                $trabajador = $revision->getTrabajador();
                $empresa = $revision->getEmpresa();
                $nombreTrabajador = $trabajador->getNombre();
                $dniTrabajador = $trabajador->getDni();
                $fecha = $revision->getFecha()->format('dmY');
                $nombreTrabajador = $this->eliminar_tildes($nombreTrabajador);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreTrabajador . ' ' . $dniTrabajador . ' ' . $fecha . ' ' . $revisionId . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaRevision();
                break;
            case '15':
                $anyoMemoriaEstudio = $_REQUEST['anyo'];
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $empresaNombre = $empresa->getEmpresa();
                $empresaNombre = $this->eliminar_tildes($empresaNombre);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $empresaNombre . ' ' . $anyoMemoriaEstudio . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaMemoria();
                break;
            case '16':
                $anyoMemoriaEstudio = $_REQUEST['anyo'];
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $empresaNombre = $empresa->getEmpresa();
                $empresaNombre = $this->eliminar_tildes($empresaNombre);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $empresaNombre . ' ' . $anyoMemoriaEstudio . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaEstudioEpidemiologico();
                break;
            case '17':
                $revisionId = $_REQUEST['id'];
                $revision = $em->getRepository('App\Entity\Revision')->find($revisionId);
                $trabajador = $revision->getTrabajador();
                $empresa = $revision->getEmpresa();
                $nombreTrabajador = $trabajador->getNombre();
                $dniTrabajador = $trabajador->getDni();
                $fecha = $revision->getFecha()->format('dmY');
                $nombreTrabajador = $this->eliminar_tildes($nombreTrabajador);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreTrabajador . ' ' . $dniTrabajador . ' ' . $fecha . ' ' . $revisionId . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaRevision();
                break;
            case '18':
                $fechaManualVs = new \DateTime();
                $fechaManualVsString = $fechaManualVs->format('dmY');
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $empresaNombre = $empresa->getEmpresa();
                $empresaNombre = $this->eliminar_tildes($empresaNombre);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $empresaNombre . ' ' . $fechaManualVsString . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaManualVs();
                break;
            case '19':
                $empresaId = $_REQUEST['id'];
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $nombreEmpresa = $empresa->getEmpresa();
                $nombreEmpresa = rtrim($nombreEmpresa, ".");
                $nombreEmpresa = $this->eliminar_tildes($nombreEmpresa);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $hoyString . ' ' . $nombreEmpresa . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaProtocoloAcoso();

                $fecha = $_REQUEST['fecha'];
                $fechaProtocoloAcoso = new \DateTime($fecha);
                break;
            default:
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $nuevaPlantilla = $empresa->getCodigo() . '_' . $hoyString . '_' . $nombreCompleto;
        }

        //RUTAS LOCAL
        //$urlPlantilla = 'C:\Users\david.jimenez\Desktop\Versiones codigo\CARPETAS PROVES LIBREOFFICE PREVENCIÓ\CARPETA PLANTILLAS'.'/'.$nombreCompleto;
        //$urlNueva = 'C:\Users\david.jimenez\Desktop\Versiones codigo\CARPETAS PROVES LIBREOFFICE PREVENCIÓ\NOVA CARPETA'.'/'.$nuevaPlantilla;
        //RUTAS PROD

        //Generamos el nuevo fichero a partir de la plantilla
        $urlPlantilla = $rutaGestionDocumental . $carpetaPlantillas . '/' . $nombreCompleto;
        $urlNueva = $rutaGestionDocumental . $carpetaPlantillaGenerada . '/' . $nuevaPlantilla;

        $return = $this->replaceTagsSoloCentro($puestosTrabajoSeleccionadosSelect2, $em, $urlPlantilla, $urlNueva, $tipo, $empresa, $contrato, $factura, $trabajador, $evaluacion, $citacion, $revision, $puestoTrabajoEvaluacion, $anyoMemoriaEstudio, $idiomaPlantilla);

        if ($return) {
            //Creamos el registro
            $gdocFichero = new GdocFichero();
            $gdocFichero->setEmpresa($empresa);
            $gdocFichero->setDtcrea(new \DateTime());
            $gdocFichero->setUsuario($usuario);
            $gdocFichero->setNombre($nuevaPlantilla);
            $gdocFichero->setAnulado(false);
            $gdocFichero->setPlantilla($plantilla);
            $em->persist($gdocFichero);
            $em->flush();

            switch ($tipo) {
                case '1':
                    $contrato->setFichero($gdocFichero);
                    $em->persist($contrato);
                    $em->flush();
                    break;
                case '2':
                    $factura->setFichero($gdocFichero);
                    $em->persist($factura);
                    $em->flush();
                    break;
                case '3':
                    $notificacion = new EmpresaNotificacion();
                    $notificacion->setEmpresa($empresa);
                    $notificacion->setFichero($gdocFichero);
                    $em->persist($notificacion);
                    $em->flush();
                    break;
                case '4':
                    $certificacion = new EmpresaCertificacion();
                    $certificacion->setEmpresa($empresa);
                    $certificacion->setFichero($gdocFichero);
                    $em->persist($certificacion);
                    $em->flush();
                    break;
                case '5':
                    $modelo347 = new EmpresaModelo347();
                    $modelo347->setEmpresa($empresa);
                    $modelo347->setFichero($gdocFichero);
                    $em->persist($modelo347);
                    $em->flush();
                    break;
                case '6':
                    $accidenteLaboral = new EmpresaAccidenteLaboral();
                    $accidenteLaboral->setEmpresa($empresa);
                    $accidenteLaboral->setFichero($gdocFichero);
                    $accidenteLaboral->setTrabajador($trabajador);
                    $accidenteLaboral->setFecha($fechaAccidenteLaboral);
                    $em->persist($accidenteLaboral);
                    $em->flush();
                    break;
                case '7':
                    //Peticio 28/07/2023
                    $evaluacion->setFicheroCentro($gdocFichero->getId());
                    $em->persist($evaluacion);
                    $em->flush();
                    break;
                case '8':
                    $planPrevencion = new EmpresaPlanPrevencion();
                    $planPrevencion->setEmpresa($empresa);
                    $planPrevencion->setFichero($gdocFichero);
                    $planPrevencion->setFecha($fechaPlanPrevencion);
                    $em->persist($planPrevencion);
                    $em->flush();
                    break;
                case '11':
                    $revision->setFichero($gdocFichero);
                    $em->persist($revision);
                    $em->flush();
                    break;
                case '13':
                    $revision->setFicheroResumen($gdocFichero);
                    $em->persist($revision);
                    $em->flush();
                    break;
                case '14':
                    $revision->setFicheroRevisionMedica($gdocFichero);
                    $em->persist($revision);
                    $em->flush();
                    break;
                case '15':
                    $memoria = new EmpresaMemoria();
                    $memoria->setEmpresa($empresa);
                    $memoria->setFichero($gdocFichero);
                    $memoria->setAnyo($anyoMemoriaEstudio);
                    $em->persist($memoria);
                    $em->flush();
                    break;
                case '16':
                    $estudio = new EmpresaEstudioEpidemiologico();
                    $estudio->setEmpresa($empresa);
                    $estudio->setFichero($gdocFichero);
                    $estudio->setAnyo($anyoMemoriaEstudio);
                    $em->persist($estudio);
                    $em->flush();
                    break;
                case '17':
                    $revision->setFicheroRestriccion($gdocFichero);
                    $em->persist($revision);
                    $em->flush();
                    break;
                case '18':
                    $manualvs = new EmpresaManualVs();
                    $manualvs->setEmpresa($empresa);
                    $manualvs->setFichero($gdocFichero);
                    $manualvs->setFecha(new \DateTime());
                    $em->persist($manualvs);
                    $em->flush();
                    break;
                case '19':
                    $protocoloAcoso = new EmpresaProtocoloAcoso();
                    $protocoloAcoso->setEmpresa($empresa);
                    $protocoloAcoso->setFichero($gdocFichero);
                    $protocoloAcoso->setFecha($fechaProtocoloAcoso);
                    $em->persist($protocoloAcoso);
                    $em->flush();
                    break;
            }
            if ($tipo == '13') {
                $data = array(
                    'url' => $url . '?file_path=file://' . $rutaGestionDocumental . $carpetaPlantillaGenerada . '/' . $nuevaPlantilla . '&host=' . $host,
                    'urlpdf' => $this->obtenerPdfAnalitica($revision)
                );
            } else {
                $data = array(
                    'url' => $url . '?file_path=file://' . $rutaGestionDocumental . $carpetaPlantillaGenerada . '/' . $nuevaPlantilla . '&host=' . $host
                );
            }
            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success', $traduccion);

            return new JsonResponse($data);
        }
    }

    public function generarFile(Request $request,  TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $user = $this->getUser();
        $usuario = $em->getRepository('App\Entity\User')->find($user);
        //eliminar
        $puestosTrabajoSeleccionadosSelect2 = $_REQUEST['select2'];
        //$puestosTrabajoSeleccionadosSelect2 = "";

        $plantillaId = $_REQUEST['plantillaId'];

        $plantilla = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->find($plantillaId);
        $nombreCompleto = $plantilla->getNombreCompleto();
        $nombrePlantilla = $plantilla->getNombre();

        // Buscamos la configuración de la gestión documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $rutaGestionDocumental = $gdocConfig->getRuta();
        $carpetaPlantillaGenerada = $gdocConfig->getCarpetaGenerada();
        $carpetaPlantillas = $gdocConfig->getCarpetaPlantillas();
        $url = $gdocConfig->getUrl();
        $host = $gdocConfig->getHost();

        //tipo == 1 es CONTRATO
        //tipo == 2 es FACTURA
        //tipo == 3 es NOTIFICACION
        //tipo == 4 es CERTIFICACION
        //tipo == 5 es MODELO347
        //tipo == 6 es ACCIDENTE
        //tipo == 7 es EVALUACION
        //tipo == 8 es PLAN DE PREVENCION
        //tipo == 9 es CITACION/CERTIFICADO DE ASISTENCIA
        //tipo == 10 es CUESTIONARIO REVISION
        //tipo == 11 es CERTIFICADO APTITUD
        //tipo == 12 es FICHA DE RIESGOS
        //tipo == 13 es RESUMEN REVISION
        //tipo == 14 es REVISION MEDICA
        //tipo == 15 es MEMORIA
        //tipo == 16 es ESTUDIO EPIDEMIOLOGICO
        //tipo == 17 es RESTRICCION APTITUD
        //tipo == 18 es MANUAL VS
        //tipo == 19 es PROTOCOLO ACOSO
        $tipo = $_REQUEST['tipo'];

        $contrato = null;
        $factura = null;
        $trabajador = null;
        $evaluacion = null;
        $citacion = null;
        $revision = null;
        $puestoTrabajoEvaluacion = null;
        $anyoMemoriaEstudio = null;
        $idiomaPlantilla = null;

        if (str_contains($nombrePlantilla, 'CAT')) {
            $idiomaPlantilla = 'CAT';
        } else {
            $idiomaPlantilla = 'ESP';
        }
        $hoy = new \DateTime();
        $hoyString = $hoy->format('YmdHis');

        switch ($tipo) {
            case '1':
                $contratoId = $_REQUEST['id'];
                $contrato = $em->getRepository('App\Entity\Contrato')->find($contratoId);
                $empresa = $contrato->getEmpresa();
                $numContrato = str_replace('/', '-', $contrato->getContrato());
                $nombreEmpresa = $empresa->getEmpresa();
                $nombreEmpresa = rtrim($nombreEmpresa, ".");
                $nombreEmpresa = $this->eliminar_tildes($nombreEmpresa);
                $nuevaPlantilla = 'Contrato ' . $numContrato . ' de ' . $nombreEmpresa . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaContrato();
                break;

            case '2':
                $facturaId = $_REQUEST['id'];
                $factura = $em->getRepository('App\Entity\Facturacion')->find($facturaId);
                $empresa = $factura->getEmpresa();
                $nombreEmpresa = $empresa->getEmpresa();
                $nombreEmpresa = rtrim($nombreEmpresa, ".");
                $nombreEmpresa = $this->eliminar_tildes($nombreEmpresa);
                $numFac = $factura->getSerie()->getSerie() . $factura->getNumFac();
                $numFac = str_replace('/', '-', $numFac);
                $nuevaPlantilla = 'Factura ' . $numFac . ' de ' . $nombreEmpresa . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaFactura();
                break;

            case '3':
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $nombreEmpresa = $empresa->getEmpresa();
                $nombreEmpresa = rtrim($nombreEmpresa, ".");
                $nombreEmpresa = $this->eliminar_tildes($nombreEmpresa);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreEmpresa . ' ' . $hoyString . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaNotificacion();
                break;

            case '4':
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $nombreEmpresa = $empresa->getEmpresa();
                $nombreEmpresa = rtrim($nombreEmpresa, ".");
                $nombreEmpresa = $this->eliminar_tildes($nombreEmpresa);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreEmpresa . ' ' . $hoyString . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaCertificacion();
                break;

            case '5':
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $nombreEmpresa = $empresa->getEmpresa();
                $nombreEmpresa = rtrim($nombreEmpresa, ".");
                $nombreEmpresa = $this->eliminar_tildes($nombreEmpresa);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreEmpresa . ' ' . $hoyString . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaModelo347();
                break;

            case '6':
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $trabajadorId = $_REQUEST['id'];
                $trabajador = $em->getRepository('App\Entity\Trabajador')->find($trabajadorId);
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < 10; $i++) {
                    $randomString .= $characters[rand(0, $charactersLength - 1)];
                }
                $nuevaPlantilla = $nombrePlantilla . ' ' . $randomString . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaAccidente();

                $fecha = $_REQUEST['fecha'];
                $fechaAccidenteLaboral = new \DateTime($fecha);
                break;

            case '7':
                $evaluacionId = $_REQUEST['id'];
                $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($evaluacionId);
                $empresa = $evaluacion->getEmpresa();
                $nombreEmpresa = $empresa->getEmpresa();
                $nombreEmpresa = rtrim($nombreEmpresa, ".");
                $nombreEmpresa = $this->eliminar_tildes($nombreEmpresa);
                $fechaEvaluacion = $evaluacion->getFechaInicio()->format('dmY');
                $nuevaPlantilla = 'Evaluacion de ' . $nombreEmpresa . ' ' . $fechaEvaluacion . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaEvaluacion();
                break;

            case '8':
                $empresaId = $_REQUEST['id'];
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $nombreEmpresa = $empresa->getEmpresa();
                $nombreEmpresa = rtrim($nombreEmpresa, ".");
                $nombreEmpresa = $this->eliminar_tildes($nombreEmpresa);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $hoyString . ' ' . $nombreEmpresa . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaPlanPrevencion();

                $fecha = $_REQUEST['fecha'];
                $fechaPlanPrevencion = new \DateTime($fecha);
                break;

            case '9':
                $citacionId = $_REQUEST['id'];
                $citacion = $em->getRepository('App\Entity\Citacion')->find($citacionId);
                $empresa = $citacion->getEmpresa();
                $nombreTrabajador = $citacion->getTrabajador()->getNombre();
                $nombreTrabajador = $this->eliminar_tildes($nombreTrabajador);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreTrabajador . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaCitacion();
                break;

            case '10':
                $revisionId = $_REQUEST['id'];
                $revision = $em->getRepository('App\Entity\Revision')->find($revisionId);
                $trabajador = $revision->getTrabajador();
                $empresa = $revision->getEmpresa();
                $nombreTrabajador = $trabajador->getNombre();
                $dniTrabajador = $trabajador->getDni();
                $nombreTrabajador = $this->eliminar_tildes($nombreTrabajador);
                $fechaRevision = $revision->getFecha()->format('dmY');
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreTrabajador . ' ' . $dniTrabajador . ' ' . $fechaRevision . ' ' . $revisionId . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaRevision();
                break;

            case '11':
                $revisionId = $_REQUEST['id'];
                $revision = $em->getRepository('App\Entity\Revision')->find($revisionId);
                $trabajador = $revision->getTrabajador();
                $empresa = $revision->getEmpresa();
                $nombreTrabajador = $trabajador->getNombre();
                $dniTrabajador = $trabajador->getDni();
                $nombreTrabajador = $this->eliminar_tildes($nombreTrabajador);
                $fechaAptitud = "";
                if (!is_null($revision->getFechaCertificacion())) {
                    $fechaAptitud = $revision->getFechaCertificacion()->format('dmY');
                }
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreTrabajador . ' ' . $dniTrabajador . ' ' . $hoyString . ' ' . $revisionId . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaAptitud();
                break;

            case '12':

                //fix evaluacion riesgos 20/03/2025
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $puestoEvaluarId = $_REQUEST['id'];
                $puestoTrabajoEvaluacion = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoEvaluacion')->find($puestoEvaluarId);
                $puestoTrabajo = $puestoTrabajoEvaluacion->getPuestoTrabajo()->getDescripcion();
                $evaluacion = $puestoTrabajoEvaluacion->getEvaluacion();
                $fechaEvaluacion = $evaluacion->getFechaInicio()->format('dmY');
                $puestoTrabajo = str_replace('/', '-', $puestoTrabajo);
                $puestoTrabajo = str_replace('\\', '-', $puestoTrabajo);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $puestoTrabajo . ' ' . $fechaEvaluacion . ' ' . $puestoEvaluarId . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaFichaRiesgos();
                $puestoZonaTrabajoEvaluacion = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajoEvaluacion')->find($puestoEvaluarId);
                $evaluacionZonaTrabajo = null;
                if($puestoZonaTrabajoEvaluacion != null) {
                    $evaluacionZonaTrabajo = $puestoZonaTrabajoEvaluacion->getEvaluacion();
                }

                break;

            case '13':
                $revisionId = $_REQUEST['id'];
                $revision = $em->getRepository('App\Entity\Revision')->find($revisionId);
                $trabajador = $revision->getTrabajador();
                $empresa = $revision->getEmpresa();
                $nombreTrabajador = $trabajador->getNombre();
                $dniTrabajador = $trabajador->getDni();
                $fecha = $revision->getFecha()->format('dmY');
                $nombreTrabajador = $this->eliminar_tildes($nombreTrabajador);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreTrabajador . ' ' . $dniTrabajador . ' ' . $fecha . ' ' . $revisionId . '.docx';;
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaRevision();
                break;

            case '14':
                $revisionId = $_REQUEST['id'];
                $revision = $em->getRepository('App\Entity\Revision')->find($revisionId);
                $trabajador = $revision->getTrabajador();
                $empresa = $revision->getEmpresa();
                $nombreTrabajador = $trabajador->getNombre();
                $dniTrabajador = $trabajador->getDni();
                $fecha = $revision->getFecha()->format('dmY');
                $nombreTrabajador = $this->eliminar_tildes($nombreTrabajador);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreTrabajador . ' ' . $dniTrabajador . ' ' . $fecha . ' ' . $revisionId . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaRevision();
                break;

            case '15':
                $anyoMemoriaEstudio = $_REQUEST['anyo'];
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $empresaNombre = $empresa->getEmpresa();
                $empresaNombre = $this->eliminar_tildes($empresaNombre);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $empresaNombre . ' ' . $anyoMemoriaEstudio . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaMemoria();
                break;

            case '16':
                $anyoMemoriaEstudio = $_REQUEST['anyo'];
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $empresaNombre = $empresa->getEmpresa();
                $empresaNombre = $this->eliminar_tildes($empresaNombre);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $empresaNombre . ' ' . $anyoMemoriaEstudio . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaEstudioEpidemiologico();
                break;

            case '17':
                $revisionId = $_REQUEST['id'];
                $revision = $em->getRepository('App\Entity\Revision')->find($revisionId);
                $trabajador = $revision->getTrabajador();
                $empresa = $revision->getEmpresa();
                $nombreTrabajador = $trabajador->getNombre();
                $dniTrabajador = $trabajador->getDni();
                $fecha = $revision->getFecha()->format('dmY');
                $nombreTrabajador = $this->eliminar_tildes($nombreTrabajador);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreTrabajador . ' ' . $dniTrabajador . ' ' . $fecha . ' ' . $revisionId . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaRevision();
                break;

            case '18':
                $fechaManualVs = new \DateTime();
                $fechaManualVsString = $fechaManualVs->format('dmY');
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $empresaNombre = $empresa->getEmpresa();
                $empresaNombre = $this->eliminar_tildes($empresaNombre);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $empresaNombre . ' ' . $fechaManualVsString . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaManualVs();
                break;

            case '19':
                $empresaId = $_REQUEST['id'];
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $nombreEmpresa = $empresa->getEmpresa();
                $nombreEmpresa = rtrim($nombreEmpresa, ".");
                $nombreEmpresa = $this->eliminar_tildes($nombreEmpresa);
                $nuevaPlantilla = $nombrePlantilla . ' ' . $hoyString . ' ' . $nombreEmpresa . '.docx';
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaProtocoloAcoso();

                $fecha = $_REQUEST['fecha'];
                $fechaProtocoloAcoso = new \DateTime($fecha);
                break;

            default:
                $empresa = $session->get('empresa');
                $empresaId = $empresa->getId();
                $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
                $nuevaPlantilla = $empresa->getCodigo() . '_' . $hoyString . '_' . $nombreCompleto;
        }
        // RUTAS LOCAL

        //$urlPlantilla = 'C:\Users\david.jimenez\Desktop\Versiones codigo\CARPETAS PROVES LIBREOFFICE PREVENCIÓ\CARPETA PLANTILLAS'.'/'.$nombreCompleto;
        //$urlNueva = 'C:\Users\david.jimenez\Desktop\Versiones codigo\CARPETAS PROVES LIBREOFFICE PREVENCIÓ\NOVA CARPETA'.'/'.$nuevaPlantilla;

        //RUTAS PROD

        // Generamos el nuevo fichero a partir de la plantilla
        $urlPlantilla = $rutaGestionDocumental . $carpetaPlantillas . '/' . $nombreCompleto;
        $urlNueva = $rutaGestionDocumental . $carpetaPlantillaGenerada . '/' . $nuevaPlantilla;
        /*if($plantillaId != 107 and $tipo == 8){
            $urlNueva = str_replace('.docx', '.doc', $urlNueva);
        }*/
        $return = $this->replaceTags($puestosTrabajoSeleccionadosSelect2, $em, $urlPlantilla, $urlNueva, $tipo, $empresa, $contrato, $factura, $trabajador, $evaluacion, $citacion, $revision, $puestoTrabajoEvaluacion, $anyoMemoriaEstudio, $idiomaPlantilla, $evaluacionZonaTrabajo);

        if ($return) {
            //Creamos el registro
            $gdocFichero = new GdocFichero();
            $gdocFichero->setEmpresa($empresa);
            $gdocFichero->setDtcrea(new \DateTime());
            $gdocFichero->setUsuario($usuario);
            $gdocFichero->setNombre($nuevaPlantilla);
            $gdocFichero->setAnulado(false);
            $gdocFichero->setPlantilla($plantilla);
            $em->persist($gdocFichero);
            $em->flush();

            switch ($tipo) {
                case '1':
                    $contrato->setFichero($gdocFichero);
                    $em->persist($contrato);
                    $em->flush();
                    break;

                case '2':
                    $factura->setFichero($gdocFichero);
                    $em->persist($factura);
                    $em->flush();
                    break;

                case '3':
                    $notificacion = new EmpresaNotificacion();
                    $notificacion->setEmpresa($empresa);
                    $notificacion->setFichero($gdocFichero);
                    $em->persist($notificacion);
                    $em->flush();
                    break;

                case '4':
                    $certificacion = new EmpresaCertificacion();
                    $certificacion->setEmpresa($empresa);
                    $certificacion->setFichero($gdocFichero);
                    $em->persist($certificacion);
                    $em->flush();
                    break;

                case '5':
                    $modelo347 = new EmpresaModelo347();
                    $modelo347->setEmpresa($empresa);
                    $modelo347->setFichero($gdocFichero);
                    $em->persist($modelo347);
                    $em->flush();
                    break;

                case '6':
                    $accidenteLaboral = new EmpresaAccidenteLaboral();
                    $accidenteLaboral->setEmpresa($empresa);
                    $accidenteLaboral->setFichero($gdocFichero);
                    $accidenteLaboral->setTrabajador($trabajador);
                    $accidenteLaboral->setFecha($fechaAccidenteLaboral);
                    $em->persist($accidenteLaboral);
                    $em->flush();
                    break;

                case '7':
                    $evaluacion->setFichero($gdocFichero);
                    $em->persist($evaluacion);
                    $em->flush();
                    break;

                case '8':
                    $planPrevencion = new EmpresaPlanPrevencion();
                    $planPrevencion->setEmpresa($empresa);
                    $planPrevencion->setFichero($gdocFichero);
                    $planPrevencion->setFecha($fechaPlanPrevencion);
                    $em->persist($planPrevencion);
                    $em->flush();
                    break;

                case '11':
                    $revision->setFichero($gdocFichero);
                    $em->persist($revision);
                    $em->flush();
                    break;

                case '13':
                    $revision->setFicheroResumen($gdocFichero);
                    $em->persist($revision);
                    $em->flush();
                    break;

                case '14':
                    $revision->setFicheroRevisionMedica($gdocFichero);
                    $em->persist($revision);
                    $em->flush();
                    break;

                case '15':
                    $memoria = new EmpresaMemoria();
                    $memoria->setEmpresa($empresa);
                    $memoria->setFichero($gdocFichero);
                    $memoria->setAnyo($anyoMemoriaEstudio);
                    $em->persist($memoria);
                    $em->flush();
                    break;

                case '16':
                    $estudio = new EmpresaEstudioEpidemiologico();
                    $estudio->setEmpresa($empresa);
                    $estudio->setFichero($gdocFichero);
                    $estudio->setAnyo($anyoMemoriaEstudio);
                    $em->persist($estudio);
                    $em->flush();
                    break;

                case '17':
                    $revision->setFicheroRestriccion($gdocFichero);
                    $em->persist($revision);
                    $em->flush();
                    break;

                case '18':
                    $manualvs = new EmpresaManualVs();
                    $manualvs->setEmpresa($empresa);
                    $manualvs->setFichero($gdocFichero);
                    $manualvs->setFecha(new \DateTime());
                    $em->persist($manualvs);
                    $em->flush();
                    break;

                case '19':
                    $protocoloAcoso = new EmpresaProtocoloAcoso();
                    $protocoloAcoso->setEmpresa($empresa);
                    $protocoloAcoso->setFichero($gdocFichero);
                    $protocoloAcoso->setFecha($fechaProtocoloAcoso);
                    $em->persist($protocoloAcoso);
                    $em->flush();
                    break;
            }
            if ($tipo == '13') {
                $data = array(
                    'url' => $url . '?file_path=file://' . $rutaGestionDocumental . $carpetaPlantillaGenerada . '/' . $nuevaPlantilla . '&host=' . $host,
                    'urlpdf' => $this->obtenerPdfAnalitica($revision)
                );
            } else {
                $data = array(
                    'url' => $url . '?file_path=file://' . $rutaGestionDocumental . $carpetaPlantillaGenerada . '/' . $nuevaPlantilla . '&host=' . $host
                );
            }
            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success', $traduccion);

            return new JsonResponse($data);
        }
    }

    public function recuperarTextoPlantilla(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $plantillaId = $_REQUEST['plantillaId'];

        $plantilla = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->find($plantillaId);
        $nombreCompleto = $plantilla->getNombreCompleto();

        //Buscamos la configuración de la gestión documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $rutaGestionDocumental = $gdocConfig->getRuta();
        $rutaPlantillas = $gdocConfig->getCarpetaPlantillas();

        $urlPlantilla = $rutaGestionDocumental . $rutaPlantillas . '/' . $nombreCompleto;

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

        $data = array(
            'texto' => $striped_content
        );

        return new JsonResponse($data);
    }

    function cleanText($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    function replaceTags($puestosTrabajoSeleccionadosSelect2, $em, $urlPlantilla, $urlNueva, $tipo, $empresa, $contrato, $factura, $trabajador, $evaluacion, $citacion, $revision, $puestoTrabajoEvaluacion, $anyoMemoriaEstudio, $idiomaPlantilla, $evaluacionZonaTrabajo)
    {
        // Nombre de la empresa
        $nombre = str_replace('&', '&amp;', $empresa->getEmpresa());
        $empresaId = $empresa->getId();

        // Buscamos los CNAES de la empresa
        $cnaesString = null;
        $cnaes = $em->getRepository('App\Entity\CnaeEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false, 'principal' => true));
        foreach ($cnaes as $cn) {
            $cnaesString .= $cn->getCnae()->getCnae() . ' - ' . $cn->getCnae()->getDescripcion() . ', ';
        }

        // Buscamos el CCC de la empresa
        $cccString = $empresa->getCcc();

        // Buscamos los correos de la empresa
        $emailString = null;
        $email = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));
        foreach ($email as $e) {
            $emailString .= $e->getCorreo() . ', ';
        }
        $emailString = rtrim($emailString, ", ");

        // Buscamos los correos de la empresa de notificaciones solo
        $emailStringNotificaciones = null;
        $email2 = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false, 'funcion' => 3));
        foreach ($email2 as $e) {
            $emailStringNotificaciones .= $e->getCorreo();
        }

        // Buscamos la cuenta bancaria principal de la empresa
        $iban = null;
        $pais = null;
        $cccPrincipal = null;
        $diaPago = null;
        $formaPagoEmpresa = null;
        $bic = null;

        $datosBancarios = $em->getRepository('App\Entity\DatosBancarios')->findOneBy(array('empresa' => $empresa, 'anulado' => false, 'principal' => true));
        if (!is_null($datosBancarios)) {
            $iban = $datosBancarios->getIbanDigital();
            if (!is_null($datosBancarios->getPais())) {
                $pais = $datosBancarios->getPais()->getDescripcion();
            }
            $cccPrincipal = $datosBancarios->getNumCuenta();
            $diaPago = $datosBancarios->getDiaPago();
            if (!is_null($datosBancarios->getFormaPago())) {
                $formaPagoEmpresa = $datosBancarios->getFormaPago()->getDescripcion();
            }
            if (!is_null($datosBancarios->getEntidadBancaria())) {
                $bic = $datosBancarios->getEntidadBancaria()->getBic();
            }
        }
        $facturaSn = false;
        $evaluacionSn = false;
        $citacionSn = false;
        $planPrevencionSn = false;
        $revisionSn = false;
        $fichaRiesgosSn = false;
        $resumenRevisionSn = false;
        $revisionMedicaSn = false;
        $memoriaSn = false;
        $estudioSn = false;
        $restriccionSn = false;
        $modelo347Sn = false;
        $contratoFecha = null;
        $contratoFechaVencimiento = null;
        $contratoNumero = null;
        $importePrevencion = 0;
        $fechaFactura = null;
        $numFactura = null;
        $observaciones = null;
        $formaPago = null;
        $conceptos = null;
        $importeExentoIva = 0;
        $importeSujetoIva = 0;
        $importeIva = 0;
        $importeTotalFactura = 0;
        $giros = null;
        $nombreTrabajador = null;
        $dniTrabajador = null;
        $edadTrabajador = null;
        $fechaNacimiento = null;
        $sexo = null;
        $emailTrabajador = null;
        $telefonoTrabajador = null;
        $puestoTrabajoTrabajador = null;
        $direccionTrabajador = null;
        $codigoTrabajador = null;
        $nombreCentro = null;
        $direccionCentro = null;
        $localidadCentro = null;
        $provinciaCentro = null;
        $telefonoCentro = null;
        $descripcionCentro = null;
        $nombreCentro2 = null;
        $direccionCentro2 = null;
        $localidadCentro2 = null;
        $provinciaCentro2 = null;
        $telefonoCentro2 = null;
        $descripcionCentro2 = null;
        $tipoEvaluacion = null;
        $visitasEvaluacion = null;
        $acompanyantesEvaluacion = null;
        $trabajadoresPorPuestoTrabajo = null;
        $arrayPuestosTrabajo = array();
        $tipoEvaluacionDesc = NULL;
        $evaluacionId = null;
        $tecnicoEvaluacion = null;
        $firmaTecnico = null;
        $normativas = null;
        $fechaCitacion = null;
        $trabajadorCitacion = null;
        $fechaPlanPrevencion = null;
        $fechaRevision = null;
        $fechaAptitud = null;
        $aptitudRevision = null;
        $aptitudRestriccion = null;
        $doctorRevision = null;
        $protocolosRevision = null;
        $cuestionarioRevision = null;
        $preguntaCuesionarioRevision = null;
        $respuestasCuestionarioPreguntaRevision = null;

        // Buscamos el contrato activo de la empresa
        if (is_null($contrato)) {
            $contrato = $em->getRepository('App\Entity\Contrato')->findOneBy(array('empresa' => $empresa, 'anulado' => false, 'cancelado' => false), array('fechainicio' => 'DESC'));
            if (!is_null($contrato)) {
                $contratoNumero = $contrato->getContrato();
                $contratoFecha = $contrato->getFechainicio()->format('d/m/Y');

                $renovacion = $em->getRepository('App\Entity\Renovacion')->findOneBy(array('contrato' => $contrato, 'anulado' => false));
                if (!is_null($renovacion)) {
                    $contratoFechaVencimiento = $renovacion->getFechavencimiento()->format('d/m/Y');
                }
            }
        } else {
            $contratoNumero = $contrato->getContrato();
            $contratoFecha = $contrato->getFechainicio()->format('d/m/Y');

            $renovacion = $em->getRepository('App\Entity\Renovacion')->findOneBy(array('contrato' => $contrato, 'anulado' => false));
            if (!is_null($renovacion)) {
                $contratoFechaVencimiento = $renovacion->getFechavencimiento()->format('d/m/Y');
            }
        }
        // Buscamos el importe total del contrato
        $contratoPago = $em->getRepository('App\Entity\ContratoPago')->findBy(array('contrato' => $contrato, 'anulado' => false));
        foreach ($contratoPago as $cp) {
            $importePrevencion += $cp->getImporteSinIva();
        }

        // Buscamos la ultima factura
        if (is_null($factura)) {
            $factura = $em->getRepository('App\Entity\Facturacion')->findOneBy(array('contrato' => $contrato, 'anulado' => false));
        }
        if (!is_null($factura)) {
            // Validar que getFecha() no sea null antes de llamar a format()
            $fechaFactura = !is_null($factura->getFecha())
                ? $factura->getFecha()->format('Y-m-d')
                : 'Fecha no disponible';

            // Validar que getSerie() y getSerie()->getSerie() no sean null
            $serie = $factura->getSerie();
            $numFactura = (!is_null($serie) && !is_null($serie->getSerie()))
                ? $serie->getSerie() . $factura->getNumFac()
                : 'Número de factura no disponible';

            // Validar que getObservaciones() no sea null
            $observaciones = !is_null($factura->getObservaciones())
                ? $factura->getObservaciones()
                : 'Sin observaciones';

            // Validar que getFormaPago() y getFormaPago()->getDescripcion() no sean null
            $formaPagoObj = $factura->getFormaPago();
            $formaPago = (!is_null($formaPagoObj) && !is_null($formaPagoObj->getDescripcion()))
                ? $formaPagoObj->getDescripcion()
                : 'Forma de pago no disponible';
        }
        // Buscamos los importes de la factura
        $facturaLineasConceptos = $em->getRepository('App\Entity\FacturacionLineasPagos')->findBy(array('facturacion' => $factura, 'anulado' => false));
        $arrayConceptosFactura = array();
        if (count($facturaLineasConceptos) > 0) {
            foreach ($facturaLineasConceptos as $flc) {
                $item = array();
                $item['CONCEPTO_UNIDADES'] = $flc->getUnidades();
                $item['CONCEPTO_DESC'] = $this->cleanText(trim($flc->getConcepto()));
                $item['CONCEPTO_EXENTO_IVA'] = '0,00';
                $item['CONCEPTO_SUJETO_IVA'] = number_format(round($flc->getImporteSujetoIva(), 2), 2, ',', '.');
                $item['CONCEPTO_IVA'] = number_format(round($flc->getImporteIva(), 2), 2, ',', '.');
                $item['CONCEPTO_TOTAL'] = number_format(round($flc->getImporteTotal(), 2), 2, ',', '.');
                array_push($arrayConceptosFactura, $item);

                $importeExentoIva += $flc->getImporteExentoIva();
                $importeSujetoIva += $flc->getImporteSujetoIva();
                $importeIva += $flc->getImporteIva();
                $importeTotalFactura += $flc->getImporteTotal();
            }
        } else {
            $facturaLineasConceptos = $em->getRepository('App\Entity\FacturacionLineasConceptos')->findBy(array('facturacion' => $factura, 'anulado' => false));
            foreach ($facturaLineasConceptos as $flc) {
                $item = array();
                $item['CONCEPTO_UNIDADES'] = $flc->getUnidades();
                $item['CONCEPTO_DESC'] = $this->cleanText(trim($flc->getConcepto()));
                $item['CONCEPTO_EXENTO_IVA'] = '0,00';
                $item['CONCEPTO_SUJETO_IVA'] = number_format(round($flc->getImporteUnidad() * $flc->getUnidades(), 2), 2, ',', '.');
                $item['CONCEPTO_IVA'] = number_format(round($flc->getIva(), 2), 2, ',', '.');
                $item['CONCEPTO_TOTAL'] = number_format(round($flc->getImporte(), 2), 2, ',', '.');
                array_push($arrayConceptosFactura, $item);

                $importeExentoIva += $flc->getImporteUnidad() * $flc->getUnidades();
                $importeSujetoIva += $flc->getImporteUnidad() * $flc->getUnidades();
                $importeIva += $flc->getIva();
                $importeTotalFactura += $flc->getImporte();
            }
            if ($importeIva > 0) {
                $importeExentoIva = 0;
            } else {
                $importeSujetoIva = 0;
            }
        }
        $importeExentoIva = number_format(round($importeExentoIva, 2), 2, ',', '.');
        $importeSujetoIva = number_format(round($importeSujetoIva, 2), 2, ',', '.');
        $importeIva = number_format(round($importeIva, 2), 2, ',', '.');
        $importeTotalFactura = number_format(round($importeTotalFactura, 2), 2, ',', '.');

        // Buscamos el numero de cuenta de la empresa
        $datosBancarios = $em->getRepository('App\Entity\DatosBancarios')->findOneBy(array('principal' => true, 'empresa' => $empresa, 'anulado' => false));
        $numeroCuentaEmpresa = "";
        if (!is_null($datosBancarios)) {
            $numeroCuentaEmpresa = $datosBancarios->getIbanPapel();
            $numeroCuentaEmpresa = substr($numeroCuentaEmpresa, 0, -4);
            $numeroCuentaEmpresa = $numeroCuentaEmpresa . 'XXXX';
        }
        // Buscamos los giros de la factura
        $giro = $em->getRepository('App\Entity\GiroBancario')->findBy(array('facturacion' => $factura, 'anulado' => false));
        $arrayDetalleGiros = array();
        foreach ($giro as $g) {
            $item = array();
            $item['DETALLEGIROS_CONCEPTO'] = $g->getConcepto();
            $item['DETALLEGIROS_FECHA'] = $g->getVencimiento()->format('d/m/Y');
            $item['DETALLEGIROS_IMPORTE'] = round($g->getImporte(), 2) . " €";
            $item['DETALLEGIROS_CUENTA'] = $numeroCuentaEmpresa;
            array_push($arrayDetalleGiros, $item);
        }
        $giroDevolucion = $em->getRepository('App\Entity\GiroBancarioDevolucion')->findBy(array('facturacion' => $factura, 'anulado' => false));
        foreach ($giroDevolucion as $gd) {
            $item = array();
            $item['DETALLEGIROS_CONCEPTO'] = $gd->getConcepto();
            $item['DETALLEGIROS_FECHA'] = $gd->getFecha()->format('d/m/Y');
            $item['DETALLEGIROS_IMPORTE'] = round($gd->getImporte(), 2) . " €";
            $item['DETALLEGIROS_CUENTA'] = "";
            array_push($arrayDetalleGiros, $item);
        }
        $facturaVencimiento = $em->getRepository('App\Entity\FacturacionVencimiento')->findBy(array('facturaAsociada' => $factura, 'anulado' => false));
        foreach ($facturaVencimiento as $fv) {
            $item = array();
            $item['DETALLEGIROS_CONCEPTO'] = $fv->getConcepto();
            $item['DETALLEGIROS_FECHA'] = $fv->getFecha()->format('d/m/Y');
            $item['DETALLEGIROS_IMPORTE'] = round($fv->getImporte(), 2) . " €";
            $item['DETALLEGIROS_CUENTA'] = "";
            array_push($arrayDetalleGiros, $item);
        }
        if (count($arrayDetalleGiros) == 0) {
            $item = array();
            $item['DETALLEGIROS_CONCEPTO'] = "";
            $item['DETALLEGIROS_FECHA'] = "";
            $item['DETALLEGIROS_IMPORTE'] = "";
            $item['DETALLEGIROS_CUENTA'] = "";
            array_push($arrayDetalleGiros, $item);
        }
        // Buscamos los datos del trabajador
        if (!is_null($trabajador)) {
            $nombreTrabajador = $trabajador->getNombre();
            $dniTrabajador = $trabajador->getDni();
            $edadTrabajador = $trabajador->getEdad();
            $emailTrabajador = $trabajador->getMail();
            $telefonoTrabajador = $trabajador->getTelefono1();
            $direccionTrabajador = $trabajador->getDomicilio();
            if (!is_null($trabajador->getFechaNacimiento())) {
                $fechaNacimiento = $trabajador->getFechaNacimiento()->format('d') . '/' . $trabajador->getFechaNacimiento()->format('m') . '/' . $trabajador->getFechaNacimiento()->format('Y');
            }
            switch ($trabajador->getSexo()) {
                case '1':
                    $sexo = 'Hombre';
                    break;
                case '2':
                    $sexo = 'Mujer';
                    break;
            }
            $trabajadorId = $trabajador->getId();

            if (!is_null($trabajador->getIdRiesgos())) {
                $codigoTrabajador = $trabajador->getIdRiesgos();
            } else {
                $codigoTrabajador = $trabajadorId;
            }
            $query = "select b.descripcion from puesto_trabajo_trabajador a
            inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
            where a.anulado = false
            and b.anulado = false 
            and a.trabajador_id = $trabajadorId
            order by a.id desc";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $puestoTrabajoArray = $stmt->fetchAll();

            if (count($puestoTrabajoArray) > 0) {
                $puestoTrabajoTrabajador = $puestoTrabajoArray[0]['descripcion'];
            }
        }
        // Buscamos los centros de la empresa
        $query = "select b.nombre, b.direccion, b.localidad, b.provincia, b.telefono, b.actividad_centro from centro_trabajo_empresa a
                inner join centro b on a.centro_id = b.id
                where a.empresa_id = $empresaId
                and a.anulado = false
                order by a.centro_id asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $centrosTrabajo = $stmt->fetchAll();

        if (count($centrosTrabajo) > 0) {
            $nombreCentro = $centrosTrabajo[0]['nombre'];
            $direccionCentro = $centrosTrabajo[0]['direccion'];
            $localidadCentro = $centrosTrabajo[0]['localidad'];
            $provinciaCentro = $centrosTrabajo[0]['provincia'];
            $telefonoCentro = $centrosTrabajo[0]['telefono'];
            $descripcionCentro = $centrosTrabajo[0]['actividad_centro'];

            if (count($centrosTrabajo) > 1) {
                $nombreCentro2 = $centrosTrabajo[1]['nombre'];
                $direccionCentro2 = $centrosTrabajo[1]['direccion'];
                $localidadCentro2 = $centrosTrabajo[1]['localidad'];
                $provinciaCentro2 = $centrosTrabajo[1]['provincia'];
                $telefonoCentro2 = $centrosTrabajo[1]['telefono'];
                $descripcionCentro2 = $centrosTrabajo[1]['actividad_centro'];
            }
        } else {
            $direccionCentro = 'Centro Itinerante';
            $localidadCentro = '';
        }
        switch ($tipo) {
            case '1':
                break;
            case '2':
                $facturaSn = true;
                break;
            case '5':
                $modelo347Sn = true;
                break;
            case '6':
                break;
            case '7':
                $evaluacionSn = true;

                $tipo = $evaluacion->getTipo();
                switch ($tipo) {
                    case 1:
                        $tipoEvaluacion = 'I-';
                        $tipoEvaluacionDesc = 'INICIAL';
                        break;
                    case 2:
                        $tipoEvaluacion = 'R-';
                        $tipoEvaluacionDesc = 'REVISIÓN';
                        break;
                }
                $evaluacionId = $evaluacion->getId();
                $query = "select count(*) as numero from evaluacion where empresa_id = $empresaId and anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $countEvaluaciones = $stmt->fetchAll();

                if (count($countEvaluaciones) > 0) {
                    $contador = $countEvaluaciones[0]['numero'];
                    if ($contador == 0) {
                        $contador++;
                    }
                    $tipoEvaluacion .= str_pad($contador, 2, '0', STR_PAD_LEFT);
                }
                // Buscamos los centros de la evaluacion
                $centrosEvaluacion = $em->getRepository('App\Entity\EvaluacionCentroTrabajo')->findBy(array('evaluacion' => $evaluacion));
                $count = 1;
                $multiCentroSn = false;
                $dosCentrosSn = false;
                $evaluacionCentro1Id = null;
                $evaluacionCentro2Id = null;
                $arrayIdCentroTrabajoEvaluacion = array();
                $arrayCentroTrabajoEvaluacion = array();
                $centroTrabajoDireccion = "";
                $centroTrabajoDireccion2 = "";

                foreach ($centrosEvaluacion as $c) {
                    if ($count == 1) {
                        $nombreCentro = $c->getCentro()->getNombre();
                        $direccionCentro = $c->getCentro()->getDireccion();
                        $localidadCentro = $c->getCentro()->getLocalidad();
                        $provinciaCentro = $c->getCentro()->getProvincia();
                        $telefonoCentro = $c->getCentro()->getTelefono();
                        $descripcionCentro = $c->getCentro()->getActividadCentro();
                        $evaluacionCentro1Id = $c->getCentro()->getId();
                    }
                    if ($count == 2) {
                        $nombreCentro2 = $c->getCentro()->getNombre();
                        $direccionCentro2 = $c->getCentro()->getDireccion();
                        $localidadCentro2 = $c->getCentro()->getLocalidad();
                        $provinciaCentro2 = $c->getCentro()->getProvincia();
                        $telefonoCentro2 = $c->getCentro()->getTelefono();
                        $descripcionCentro2 = $c->getCentro()->getActividadCentro();
                        $evaluacionCentro2Id = $c->getCentro()->getId();
                    }
                    $item = array();
                    $item['CENTRO_TEXTO#' . $count] = 'Centro ' . $count;
                    $item['CENTRO_DIRECCION#' . $count] = $c->getCentro()->getNombre();
                    $item['CENTRO_LOCALIDAD#' . $count] = $c->getCentro()->getLocalidad();
                    $item['CENTRO_PROVINCIA#' . $count] = $c->getCentro()->getProvincia();
                    $item['CENTRO_TELEFONO#' . $count] = $c->getCentro()->getTelefono();
                    $item['CENTRO_DESCRIPCION#' . $count] = $c->getCentro()->getActividadCentro();

                    array_push($arrayIdCentroTrabajoEvaluacion, $c->getCentro()->getId());
                    array_push($arrayCentroTrabajoEvaluacion, $item);

                    $centroTrabajoDireccion .= $c->getCentro()->getDireccion() . ' en ' . $c->getCentro()->getProvincia() . ', ';
                    $centroTrabajoDireccion2 .= $c->getCentro()->getDireccion() . '(' . $c->getCentro()->getProvincia() . '), ';

                    $count++;
                }

                if (count($centrosEvaluacion) == 2) {
                    $dosCentrosSn = true;
                }
                if (count($arrayIdCentroTrabajoEvaluacion) > 2) {
                    $multiCentroSn = true;
                }
                // Buscamos las visitas de la evaluacion
                $visitas =  $em->getRepository('App\Entity\VisitaEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));
                foreach ($visitas as $v) {
                    $visitasEvaluacion .= $v->getDtVisita()->format('d/m/Y') . ",";
                }
                $visitasEvaluacion = rtrim($visitasEvaluacion, ",");

                // Buscamos los acompañantes de la evaluacion
                $acompanyantes =  $em->getRepository('App\Entity\PersonaEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));
                foreach ($acompanyantes as $a) {
                    $acompanyantesEvaluacion .= $a->getNombre() . " " . $a->getApellido1() . " " . $a->getApellido2() . ",";
                }
                $acompanyantesEvaluacion = rtrim($acompanyantesEvaluacion, ",");
                if ($puestosTrabajoSeleccionadosSelect2 === "0") {
                } else {
                    $cadena = implode(',', $puestosTrabajoSeleccionadosSelect2);
                }
                if ($puestosTrabajoSeleccionadosSelect2[0] === 0 || $puestosTrabajoSeleccionadosSelect2[0] === "0" || $puestosTrabajoSeleccionadosSelect2 === "0") {
                    // Buscamos los puestos de trabajo de la empresa
                    $query = "select a.id, b.descripcion, a.trabajadores, a.tarea from puesto_trabajo_evaluacion a
                inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                where a.anulado = false
                and evaluacion_id = $evaluacionId
                order by b.descripcion ASC";
                } else {
                    // Buscamos los puestos de trabajo de la empresa
                    $query = "select a.id, b.descripcion, a.trabajadores, a.tarea from puesto_trabajo_evaluacion a
                inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                where a.anulado = false
                and evaluacion_id = $evaluacionId and a.id IN($cadena)
                order by b.descripcion ASC";
                }
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $puestosTrabajos = $stmt->fetchAll();

                $count = 1;
                foreach ($puestosTrabajos as $pt) {
                    $trabajadoresPorPuestoTrabajo .= " - " . $pt['descripcion'] . " (" . $pt['trabajadores'] . ")" . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                    $item = array();
                    $item['FICHA_NOMBRE_PUESTO_TRABAJO'] = 'Nº ' . $count . ' ' . $pt['descripcion'];
                    $item['FICHA_TAREA_PUESTO_TRABAJO'] = $pt['tarea'];
                    array_push($arrayPuestosTrabajo, $item);
                    $count++;
                }
                // Comprobamos si la evaluación tiene marcada toda la normativa
                $grupoTodaNormativa = $this->getDoctrine()->getRepository('App\Entity\GrupoNormativa')->find(14);
                $todaNormativa = $this->getDoctrine()->getRepository('App\Entity\GrupoNormativaEvaluacion')->findOneBy(array('evaluacion' => $evaluacion, 'anulado' => false, 'grupoNormativa' => $grupoTodaNormativa));

                if (!is_null($todaNormativa)) {
                    $query = "select  concat(b.titulo_es,' ', b.descripcion_es) as normativa from grupo_normativa a inner join normativa b on a.id = b.grupo_normativa_id where b.anulado = false";
                } else {
                    $query = "select concat(c.titulo_es,' ', c.descripcion_es) as normativa from grupo_normativa_evaluacion a
                    inner join grupo_normativa b on a.grupo_normativa_id = b.id
                    inner join normativa c on b.id = c.grupo_normativa_id
                    where a.evaluacion_id = $evaluacionId
                    and c.anulado = false
                    order by a.grupo_normativa_id asc";
                }
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $normativasEvaluacion = $stmt->fetchAll();
                foreach ($normativasEvaluacion as $n) {
                    $normativas .= " - " . $n['normativa'] . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                }
                // Buscamos el tecnico de la evaluacion y la firma
                $tecnicos = $em->getRepository('App\Entity\TecnicoEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));
                $arrayFirmasTecnicos = array();
                foreach ($tecnicos as $t) {
                    $tecnicoEvaluacion .= $t->getTecnico()->getNombre() . ' ' . $t->getTecnico()->getApellido1() . ' ' . $t->getTecnico()->getApellido2() . ', ';
                    array_push($arrayFirmasTecnicos, $t->getTecnico()->getFirma());
                }
                $tecnicoEvaluacion = rtrim($tecnicoEvaluacion, ", ");
                break;
            case '8':
                /*
                //Buscamos los puestos de trabajo de la empresa
                $query = "select a.id, b.descripcion, a.trabajadores, a.tarea from puesto_trabajo_evaluacion a
                inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                where a.anulado = false
                and evaluacion_id in (select id from evaluacion where empresa_id = $empresaId and anulado = false order by fecha_inicio desc limit 1)
                order by b.descripcion ASC";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $puestosTrabajos = $stmt->fetchAll();
                foreach ($puestosTrabajos as $pt){
                    $trabajadoresPorPuestoTrabajo .= " - ". $pt['descripcion'] ." (". $pt['trabajadores'].")". "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                }*/
                //Codi nou
                $planPrevencionSn = true;
                $query = "select id from evaluacion where empresa_id = $empresaId and anulado = false order by fecha_inicio desc limit 1";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();

                $puestosTrabajos = $stmt->fetchAll();
                if ($puestosTrabajos . is_null()) {
                    $query = "SELECT ptc.descripcion,COUNT(*) AS trabajadores FROM puesto_trabajo_trabajador ptt
                              INNER JOIN puesto_trabajo_centro ptc ON ptc.id = ptt.puesto_trabajo_id WHERE ptt.anulado = false AND ptt.empresa_id = $empresaId
                              GROUP BY ptc.descripcion ORDER BY ptc.descripcion ASC;";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $puestosTrabajos = $stmt->fetchAll();
                    foreach ($puestosTrabajos as $pt) {
                        $trabajadoresPorPuestoTrabajo .= " - " . $pt['descripcion'] . " (" . $pt['trabajadores'] . ")" . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                    }
                } else {
                    $query = "select a.id, b.descripcion, a.trabajadores, a.tarea from puesto_trabajo_evaluacion a
                    inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                    where a.anulado = false
                    and evaluacion_id in (select id from evaluacion where empresa_id = $empresaId and anulado = false order by fecha_inicio desc limit 1)
                    order by b.descripcion ASC";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $puestosTrabajos = $stmt->fetchAll();
                    foreach ($puestosTrabajos as $pt) {
                        $trabajadoresPorPuestoTrabajo .= " - " . $pt['descripcion'] . " (" . $pt['trabajadores'] . ")" . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                    }
                }
                break;
            case '9':
                $citacionSn = true;
                break;
            case '10':
            case '11':
                $revisionSn = true;
                break;
            case '12':
                $fichaRiesgosSn = true;
                break;
            case '13':
                $resumenRevisionSn = true;
                break;
            case '14':
                $revisionMedicaSn = true;
                break;
            case '15':
                $memoriaSn = true;
                break;
            case '16':
                $estudioSn = true;
                break;
            case '17':
                $restriccionSn = true;
                break;
        }
        $empresaId = $empresa->getId();
        $queryAux = "SELECT COUNT(tab.id) as numero FROM trabajador_alta_baja tab WHERE tab.empresa_id = $empresaId and tab.activo = true";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($queryAux);
        $stmt->execute();
        $numTrabajadores = $stmt->fetchAll();
        $numTrabajadoresAux = $numTrabajadores[0]['numero'];

        // Petició 28/07/2023 #63749
        $queryAux2 = "SELECT COUNT(*) AS total_registros
        FROM (
            SELECT (SELECT numero
                    FROM (
                        SELECT id, empresa_id, row_number() OVER (ORDER BY fecha_inicio ASC) AS numero
                        FROM evaluacion
                        WHERE anulado = false 
                        AND empresa_id = a.empresa_id
                        ORDER BY fecha_inicio ASC
                    ) consulta
                    WHERE id = a.id) AS numero
            FROM evaluacion a
            WHERE a.anulado = false AND a.empresa_id = $empresaId
        ) subconsulta";
        $stmt2 = $this->getDoctrine()->getManager()->getConnection()->prepare($queryAux2);
        $stmt2->execute();
        $numEvaluaciones = $stmt2->fetchAll();
        $numEvaluaciones2 = $numEvaluaciones[0]['total_registros'];

        $hoy = new \DateTime();
        $hoyString = $hoy->format('d/m/Y');

        copy($urlPlantilla, $urlNueva);
        $centroTrabajoDireccion = "";
        $centroTrabajoDireccion2 = "";

        // Reemplazamos los tags
        $templateProcessor = new TemplateProcessor($urlNueva);
        $templateProcessor->setValue("EMPRESA_NOMBRE", $nombre);
        // Petició 28/07/2023 #63749
        $templateProcessor->setValue("NUM_EVALU", $numEvaluaciones2);
        $templateProcessor->setValue("EMPRESA_CODIGO", $empresa->getCodigo());
        $templateProcessor->setValue("EMPRESA_CIF", $empresa->getCif());
        $templateProcessor->setValue("EMPRESA_DOMICILIO_FISCAL", $empresa->getDomicilioFiscal());
        $templateProcessor->setValue("EMPRESA_CODPOSTAL_FISCAL", $empresa->getCodigoPostalFiscal());
        $templateProcessor->setValue("EMPRESA_LOCALIDAD_FISCAL", $empresa->getLocalidadFiscal());
        $templateProcessor->setValue("EMPRESA_PROVINCIA_FISCAL", $empresa->getProvinciaFiscal());
        $templateProcessor->setValue("EMPRESA_CNAES", $cnaesString);
        $templateProcessor->setValue("EMPRESA_NUMTRABAJADORES", $numTrabajadoresAux);
        $templateProcessor->setValue("CENTROTRABAJO_CCC", $cccString);
        $templateProcessor->setValue("EMPRESA_TELEFONO1", $empresa->getTelefono1());
        $templateProcessor->setValue("EMPRESA_FAX", $empresa->getFax());
        $templateProcessor->setValue("EMPRESA_EMAIL", $emailString);
        $templateProcessor->setValue("EMPRESA_MAIL", $emailStringNotificaciones);
        $templateProcessor->setValue("CONTRATO_FECHA", $contratoFecha);
        $templateProcessor->setValue("CONTRATO_VENCIMIENTO", $contratoFechaVencimiento);
        $templateProcessor->setValue("EMPRESA_REPRESENTANTE", $empresa->getNombreRepresentante());
        $templateProcessor->setValue("EMPRESA_REPRESENTANTE_DNI", $empresa->getDniRepresentante());
        $templateProcessor->setValue("CUENTAPRINCIPAL_IBAN", $iban);
        $templateProcessor->setValue("DIA_PAGO", $diaPago);
        $templateProcessor->setValue("FORMA_PAGO", $formaPagoEmpresa);
        $templateProcessor->setValue("BIC", $bic);
        $templateProcessor->setValue("IMPORTE_PREVENCION", round($importePrevencion, 2));
        $templateProcessor->setValue("EMPRESA_PAIS_FISCAL", strtoupper($pais));
        $templateProcessor->setValue("EMPRESA_ACTIVIDAD", $empresa->getActividad());
        $templateProcessor->setValue("IMPORTE_VIGILANCIA", round($importePrevencion, 2));
        $templateProcessor->setValue("CUENTAPRINCIPAL_CCC", $cccPrincipal);
        $templateProcessor->setValue("EMPRESA_REPRESENTANTE_CARGO", $empresa->getCargoRepresentante());
        $templateProcessor->setValue("EMPRESA_CODCLIENTE", $empresa->getCodigo());
        $templateProcessor->setValue("FACTURA_NUMERO", $numFactura);
        $templateProcessor->setValue("FACTURA_FECHA", $fechaFactura);
        $templateProcessor->setValue("EMPRESA_DIRECCION", $empresa->getDomicilioPostal());
        $templateProcessor->setValue("EMPRESA_CODPOSTAL", $empresa->getCodigoPostalPostal());
        $templateProcessor->setValue("EMPRESA_LOCALIDAD", $empresa->getLocalidadPostal());
        $templateProcessor->setValue("EMPRESA_PROVINCIA", $empresa->getProvinciaPostal());
        //$templateProcessor->setValue("FACTURA_CONCEPTOS", $conceptos);
        $templateProcessor->setValue("EXENTOIVA", $importeExentoIva);
        $templateProcessor->setValue("SUJETOIVA", $importeSujetoIva);
        $templateProcessor->setValue("IVA", $importeIva);
        $templateProcessor->setValue("TOTFACTURA", $importeTotalFactura);
        $templateProcessor->setValue("FACTURA_OBSERVACIONES", $observaciones);
        $templateProcessor->setValue("FACTURA_FORMAPAGO", $formaPago);
        $templateProcessor->setValue("DETALLEGIROS", $giros);
        $templateProcessor->setValue("MIEMPRESA_LOCALIDAD", 'BARCELONA');
        $templateProcessor->setValue("FECHA", $hoyString);
        $templateProcessor->setValue("EMPRESA", $nombre);
        $templateProcessor->setValue("DIRECCION", $empresa->getDomicilioFiscal());
        $templateProcessor->setValue("CODPOSTAL", $empresa->getCodigoPostalFiscal());
        $templateProcessor->setValue("LOCALIDAD", $empresa->getLocalidadFiscal());
        $templateProcessor->setValue("PROVINCIA", $empresa->getProvinciaFiscal());
        $templateProcessor->setValue("CONTRATO_NUMERO", $contratoNumero);
        $templateProcessor->setValue("CONTRATO_NUMERO", $contratoNumero);
        $templateProcessor->setValue("TRABAJADOR_NOMBRE", $nombreTrabajador);
        $templateProcessor->setValue("TRABAJADOR_DNI", $dniTrabajador);
        $templateProcessor->setValue("TRABAJADOR_EDAD", $edadTrabajador);
        $templateProcessor->setValue("TRABAJADOR_FECHA_NACIMIENTO", $fechaNacimiento);
        $templateProcessor->setValue("TRABAJADOR_TELEFONO", $telefonoTrabajador);
        $templateProcessor->setValue("TRABAJADOR_EMAIL", $emailTrabajador);
        $templateProcessor->setValue("TRABAJADOR_SEXO", $sexo);
        $templateProcessor->setValue("TRABAJADOR_PUESTO_TRABAJO", $puestoTrabajoTrabajador);
        $templateProcessor->setValue("TRABAJADOR_DIRECCION", $direccionTrabajador);
        $templateProcessor->setValue("TRABAJADOR_CODIGO", $codigoTrabajador);
        $templateProcessor->setValue("EMPRESA_CENTRO", $nombreCentro);
        $templateProcessor->setValue("EMPRESA_CENTRO2", $nombreCentro2);
        $templateProcessor->setValue("EMPRESA_CODIGO_EVALUACION", $empresa->getCodigo() . ' ' . $tipoEvaluacion);
        $templateProcessor->setValue("EMPRESA_CODIGO_TECNICO", $empresa->getCodigoTecnico());
        $templateProcessor->setValue("EMPRESA_MARCA_COMERCIAL" ,str_replace('&', '&amp;', $empresa->getMarcaComercial()));
        $templateProcessor->setValue("CENTRO_DIRECCION", $direccionCentro);
        $templateProcessor->setValue("CENTRO_LOCALIDAD", $localidadCentro);
        $templateProcessor->setValue("CENTRO_PROVINCIA", $provinciaCentro);
        $templateProcessor->setValue("CENTRO_TELEFONO", $telefonoCentro);
        $templateProcessor->setValue("CENTRO_DESCRIPCION", $descripcionCentro);
        $templateProcessor->setValue("CENTRO2_DIRECCION", $direccionCentro2);
        $templateProcessor->setValue("CENTRO2_LOCALIDAD", $localidadCentro2);
        $templateProcessor->setValue("CENTRO2_PROVINCIA", $provinciaCentro2);
        $templateProcessor->setValue("CENTRO2_TELEFONO", $telefonoCentro2);
        $templateProcessor->setValue("CENTRO2_DESCRIPCION", $descripcionCentro2);
        $templateProcessor->setValue("EVALUACION_VISITAS", $visitasEvaluacion);
        $templateProcessor->setValue("EVALUACION_ACOMPAÑANTES", $acompanyantesEvaluacion);
        $templateProcessor->setValue("TRABAJADORES_POR_PUESTO_TRABAJO", $trabajadoresPorPuestoTrabajo);
        $templateProcessor->setValue("NORMATIVAS", $normativas);
        $templateProcessor->setValue("TECNICO_EVALUACION", $tecnicoEvaluacion);
        $templateProcessor->setValue("EMPRESA_PROCESO_PRODUCTIVO", $empresa->getProcesoProductivo());
        $templateProcessor->setValue("CENTRO_TRABAJO_MULTI", $centroTrabajoDireccion);
        $templateProcessor->setValue("CENTRO_TRABAJO_MULTI2", $centroTrabajoDireccion2);

        //ini_set("pcre.backtrack_limit", -1);

        if ($facturaSn) {
            $templateProcessor->cloneRowAndSetValues('CONCEPTO_UNIDADES', $arrayConceptosFactura);
            if (count($arrayDetalleGiros) > 0) {
                $templateProcessor->cloneRowAndSetValues('DETALLEGIROS_CONCEPTO', $arrayDetalleGiros);
            }
        }
        if ($modelo347Sn) {
            $today = new \DateTime();
            $year = $today->format('Y') - 1;

            $templateProcessor->setValue('AÑO_EJERCICIO', $year);

            $query = "select a.id from facturacion a 
            where a.anulado = false
            and a.serie_id = 7
            and a.empresa_id = $empresaId
            and a.fecha between '$year-01-01 00:00:00' and '$year-03-31 23:59:59'";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $facturasPrimerEjercicio = $stmt->fetchAll();
            $importePrimerEjercicio = 0;
            foreach ($facturasPrimerEjercicio as $fpe) {

                $facturacionId = $fpe['id'];

                // Comprobamos si tiene abono
                $query = "select b.importe from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.serie_id = 6 and a.factura_asociada_id = $facturacionId and a.anulado = false and b.anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultAbono = $stmt->fetchAll();
                $importeAbono = 0;
                foreach ($resultAbono as $ra) {
                    $importeAbono += abs(round($ra['importe'], 2));
                }
                // Comprobamos si tiene entrada en el balance
                $query = "select importe from balance_economico_entrada where anulado = false and facturacion_id = $facturacionId and tipo = 1";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultEntrada = $stmt->fetchAll();
                $importeEntrada = 0;
                foreach ($resultEntrada as $re) {
                    $importeEntrada += abs(round($re['importe'], 2));
                }
                // Buscamos los conceptos
                $query = "select importe_total as importe from facturacion_lineas_pagos where anulado = false and facturacion_id = $facturacionId";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $conceptosFactura = $stmt->fetchAll();
                $importeFacturas = 0;
                if (count($conceptosFactura) == 0) {
                    $query = "select importe from facturacion_lineas_conceptos where anulado = false and facturacion_id = $facturacionId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $conceptosFactura = $stmt->fetchAll();
                }
                foreach ($conceptosFactura as $cf) {
                    $importeFacturas += round($cf['importe'], 2);
                }
                $tmpImporte = round($importeFacturas, 2) - (round($importeAbono, 2) + $importeEntrada);

                $importePrimerEjercicio += round($tmpImporte, 2);
            }
            $templateProcessor->setValue('IMPORTE_PRIMER_TRIMESTRE', number_format(round($importePrimerEjercicio, 2), 2, ',', '.'));

            $query = "select a.id from facturacion a 
            where a.anulado = false
            and a.serie_id = 7
            and a.empresa_id = $empresaId
            and a.fecha between '$year-04-01 00:00:00' and '$year-06-30 23:59:59'";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $facturasSegundoEjercicio = $stmt->fetchAll();
            $importeSegundoEjercicio = 0;

            foreach ($facturasSegundoEjercicio as $fse) {
                $facturacionId = $fse['id'];

                // Comprobamos si tiene abono
                $query = "select b.importe from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.serie_id = 6 and a.factura_asociada_id = $facturacionId and a.anulado = false and b.anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultAbono = $stmt->fetchAll();
                $importeAbono = 0;
                foreach ($resultAbono as $ra) {
                    $importeAbono += abs(round($ra['importe'], 2));
                }
                // Comprobamos si tiene entrada en el balance
                $query = "select importe from balance_economico_entrada where anulado = false and facturacion_id = $facturacionId and tipo = 1";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultEntrada = $stmt->fetchAll();
                $importeEntrada = 0;
                foreach ($resultEntrada as $re) {
                    $importeEntrada += abs(round($re['importe'], 2));
                }
                // Buscamos los conceptos
                $query = "select importe_total as importe from facturacion_lineas_pagos where anulado = false and facturacion_id = $facturacionId";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $conceptosFactura = $stmt->fetchAll();
                $importeFacturas = 0;
                if (count($conceptosFactura) == 0) {
                    $query = "select importe from facturacion_lineas_conceptos where anulado = false and facturacion_id = $facturacionId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $conceptosFactura = $stmt->fetchAll();
                }
                foreach ($conceptosFactura as $cf) {
                    $importeFacturas += round($cf['importe'], 2);
                }
                $tmpImporte = round($importeFacturas, 2) - (round($importeAbono, 2) + $importeEntrada);

                $importeSegundoEjercicio += round($tmpImporte, 2);
            }
            $templateProcessor->setValue('IMPORTE_SEGUNDO_TRIMESTRE', number_format(round($importeSegundoEjercicio, 2), 2, ',', '.'));

            $query = "select a.id from facturacion a 
            where a.anulado = false
            and a.serie_id = 7
            and a.empresa_id = $empresaId
            and a.fecha between '$year-07-01 00:00:00' and '$year-09-30 23:59:59'";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $facturasTercerEjercicio = $stmt->fetchAll();
            $importeTercerEjercicio = 0;

            foreach ($facturasTercerEjercicio as $fte) {
                $facturacionId = $fte['id'];

                // Comprobamos si tiene abono
                $query = "select b.importe from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.serie_id = 6 and a.factura_asociada_id = $facturacionId and a.anulado = false and b.anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultAbono = $stmt->fetchAll();
                $importeAbono = 0;
                foreach ($resultAbono as $ra) {
                    $importeAbono += abs(round($ra['importe'], 2));
                }
                // Comprobamos si tiene entrada en el balance
                $query = "select importe from balance_economico_entrada where anulado = false and facturacion_id = $facturacionId and tipo = 1";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultEntrada = $stmt->fetchAll();
                $importeEntrada = 0;
                foreach ($resultEntrada as $re) {
                    $importeEntrada += abs(round($re['importe'], 2));
                }
                // Buscamos los conceptos
                $query = "select importe_total as importe from facturacion_lineas_pagos where anulado = false and facturacion_id = $facturacionId";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $conceptosFactura = $stmt->fetchAll();
                $importeFacturas = 0;

                if (count($conceptosFactura) == 0) {
                    $query = "select importe from facturacion_lineas_conceptos where anulado = false and facturacion_id = $facturacionId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $conceptosFactura = $stmt->fetchAll();
                }
                foreach ($conceptosFactura as $cf) {
                    $importeFacturas += round($cf['importe'], 2);
                }
                $tmpImporte = round($importeFacturas, 2) - (round($importeAbono, 2) + $importeEntrada);

                $importeTercerEjercicio += round($tmpImporte, 2);
            }
            $templateProcessor->setValue('IMPORTE_TERCER_TRIMESTRE', number_format(round($importeTercerEjercicio, 2), 2, ',', '.'));

            $query = "select a.id from facturacion a 
            where a.anulado = false
            and a.serie_id = 7
            and a.empresa_id = $empresaId
            and a.fecha between '$year-10-01 00:00:00' and '$year-12-31 23:59:59'";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $facturasCuartoEjercicio = $stmt->fetchAll();
            $importeCuartoEjercicio = 0;
            foreach ($facturasCuartoEjercicio as $fce) {

                $facturacionId = $fce['id'];

                //Comprobamos si tiene abono
                $query = "select b.importe from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.serie_id = 6 and a.factura_asociada_id = $facturacionId and a.anulado = false and b.anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultAbono = $stmt->fetchAll();
                $importeAbono = 0;
                foreach ($resultAbono as $ra) {
                    $importeAbono += abs(round($ra['importe'], 2));
                }

                //Comprobamos si tiene entrada en el balance
                $query = "select importe from balance_economico_entrada where anulado = false and facturacion_id = $facturacionId and tipo = 1";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultEntrada = $stmt->fetchAll();
                $importeEntrada = 0;
                foreach ($resultEntrada as $re) {
                    $importeEntrada += abs(round($re['importe'], 2));
                }

                //Buscamos los conceptos
                $query = "select importe_total as importe from facturacion_lineas_pagos where anulado = false and facturacion_id = $facturacionId";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $conceptosFactura = $stmt->fetchAll();
                $importeFacturas = 0;
                if (count($conceptosFactura) == 0) {
                    $query = "select importe from facturacion_lineas_conceptos where anulado = false and facturacion_id = $facturacionId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $conceptosFactura = $stmt->fetchAll();
                }
                foreach ($conceptosFactura as $cf) {
                    $importeFacturas += round($cf['importe'], 2);
                }

                $tmpImporte = round($importeFacturas, 2) - (round($importeAbono, 2) + $importeEntrada);

                $importeCuartoEjercicio += round($tmpImporte, 2);
            }

            $templateProcessor->setValue('IMPORTE_CUARTO_TRIMESTRE', number_format(round($importeCuartoEjercicio, 2), 2, ',', '.'));

            $query = "select a.id, a.num_fac, to_char(a.fecha, 'DD/MM/YYYY') as fecha from facturacion a 
            where a.anulado = false
            and a.serie_id = 7
            and a.empresa_id = $empresaId
            and a.fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59'
            order by fecha ASC";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $facturasEjercicio = $stmt->fetchAll();
            $importeTotalEjercicio = 0;
            $arrayFacturasEjercicio = array();
            foreach ($facturasEjercicio as $fe) {

                $facturacionId = $fe['id'];

                $item = array();
                $item['FACTURA_NUMERO_EJERCICIO'] = $fe['num_fac'];
                $item['FACTURA_FECHA_EJERCICIO'] = $fe['fecha'];

                //Comprobamos si tiene abono
                $query = "select b.importe, b.iva, b.importe_unidad from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.serie_id = 6 and a.factura_asociada_id = $facturacionId and a.anulado = false and b.anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultAbono = $stmt->fetchAll();
                $importeBaseAbono = 0;
                $importeIvaAbono = 0;
                $importeTotalAbono = 0;
                foreach ($resultAbono as $ra) {
                    $importeBaseAbono += round(abs($ra['importe_unidad']), 2);
                    $importeIvaAbono += round(abs($ra['iva']), 2);
                    $importeTotalAbono += round(abs($ra['importe']), 2);
                }

                //Buscamos los conceptos
                $query = "select importe_total as importe, importe_iva as iva, importe_sin_iva as importe_unidad from facturacion_lineas_pagos where anulado = false and facturacion_id = $facturacionId";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $conceptosFactura = $stmt->fetchAll();
                $importeBase = 0;
                $importeIva = 0;
                $importeTotal = 0;
                if (count($conceptosFactura) == 0) {
                    $query = "select importe, iva, importe_unidad from facturacion_lineas_conceptos where anulado = false and facturacion_id = $facturacionId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $conceptosFactura = $stmt->fetchAll();
                }
                foreach ($conceptosFactura as $cf) {
                    $importeBase += round($cf['importe_unidad'], 2);
                    $importeIva += round($cf['iva'], 2);
                    $importeTotal += round($cf['importe'], 2);
                }

                /*
                //p
                $item['FACTURA_BASE_EJERCICIO'] = number_format(round($importeBase - $importeBaseAbono, 2), 2, ',', '.');
                $item['FACTURA_IVA_EJERCICIO'] = number_format(round($importeIva - $importeIvaAbono, 2), 2, ',', '.');
                $item['FACTURA_TOTAL_EJERCICIO'] = number_format(round($importeTotal - $importeTotalAbono, 2), 2, ',', '.');
                */

                $factura_iva_ejercicio = $importeIva - $importeIvaAbono;
                $facutra_total_ejercicio = $importeTotal - $importeTotalAbono;

                $item['FACTURA_IVA_EJERCICIO'] = number_format(round($importeIva - $importeIvaAbono, 2), 2, ',', '.');
                $item['FACTURA_TOTAL_EJERCICIO'] = number_format(round($importeTotal - $importeTotalAbono, 2), 2, ',', '.');
                $item['FACTURA_BASE_EJERCICIO'] = number_format(round($facutra_total_ejercicio - $factura_iva_ejercicio, 2), 2, ',', '.');

                array_push($arrayFacturasEjercicio, $item);

                $importeTotalEjercicio += round($importeTotal - $importeTotalAbono, 2);
            }

            $templateProcessor->setValue('IMPORTE_EJERCICIO', number_format(round($importeTotalEjercicio, 2), 2, ',', '.'));
            $templateProcessor->setValue('IMPORTE_TOTAL_EJERCICIO', number_format(round($importeTotalEjercicio, 2), 2, ',', '.'));
            $templateProcessor->cloneRowAndSetValues('FACTURA_NUMERO_EJERCICIO', $arrayFacturasEjercicio);
        }

        if ($evaluacionSn) {
            $deslocalizado = $empresa->getCentroTrabajoDeslocalizado();

            //Añadimos el logo
            $logo = $empresa->getLogo();
            if (!is_null($logo) && file_exists("upload/media/logos/$logo")) {
                $templateProcessor->setImageValue('EMPRESA_LOGO', array('path' => 'upload/media/logos/' . $logo, 'width' => 300, 'ratio' => false));
            } else {
                $templateProcessor->setValue("EMPRESA_LOGO", null);
            }

            //Añadimos la firma del tecnico
            $templateProcessor->cloneBlock('FIRMAS_TECNICO', count($arrayFirmasTecnicos), true, true);
            $f = 1;
            foreach ($arrayFirmasTecnicos as $arrayFirmaTecnico) {
                if (!is_null($arrayFirmaTecnico)) {
                    if (file_exists("upload/media/firmas/tecnico/$arrayFirmaTecnico")) {
                        $templateProcessor->setImageValue('FIRMA_TECNICO#' . $f, array('path' => 'upload/media/firmas/tecnico/' . $arrayFirmaTecnico, 'width' => 150, 'ratio' => false, 'line' => false));
                        $f++;
                    } else {
                        $templateProcessor->setValue('FIRMA_TECNICO#' . $f, null);
                    }
                }
            }

            //Añadimos los puestos de trabajo en la ficha
            $templateProcessor->cloneRowAndSetValues('FICHA_NOMBRE_PUESTO_TRABAJO', $arrayPuestosTrabajo);

            //Buscamos los riesgos de cada zona de trabajo
            $query = "select distinct b.id, b.descripcion from zona_trabajo_evaluacion a
                inner join zona_trabajo b on a.zona_trabajo_id = b.id
                inner join evaluacion_centro_trabajo c on a.evaluacion_id = c.evaluacion_id 
                where a.anulado = false
                and a.evaluacion_id = $evaluacionId
                order by b.descripcion ASC";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $zonasTrabajo = $stmt->fetchAll();

            //Clonamos las tablas por cada zona de trabajo a evaluar
            $templateProcessor->cloneBlock('block_zonas_trabajos', count($zonasTrabajo), true, true);

            //Creamos el array para despues reemplazar los nombres por la ruta de la imagen
            $arrayImagenesRiesgosZonas = array();

            $count = 1;
            foreach ($zonasTrabajo as $zt) {
                $zonaTrabajoId =  $zt['id'];

                //Buscamos la actividad del centro
                $query = "select b.direccion from evaluacion_centro_trabajo a
                inner join centro b on a.centro_id = b.id
                where b.anulado = false
                and a.evaluacion_id = $evaluacionId
                order by b.direccion asc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $actividadCentroArray = $stmt->fetchAll();

                $actividadCentro = "";
                if (count($actividadCentroArray) > 0) {
                    if (isset($actividadCentroArray[$count - 1]['direccion'])) {
                        $actividadCentro = $actividadCentroArray[$count - 1]['direccion'];
                    }
                }

                //Si la empresa tiene el centro deslocalizado no generamos la cabecera de las zonas en la plantilla
                if (!$deslocalizado || count($centrosEvaluacion) == 0) {
                    //Generamos la primera tabla con el riesgo
                    $arrayZonaTrabajo = array();
                    $item = array();
                    $item['NUMERO_RIESGO_Z#' . $count] = 'C' . $count;
                    $item['NOMBRE_EMPRESA_Z#' . $count] = $nombre;
                    $item['CENTRO_RIESGO_Z#' . $count] = $direccionCentro;
                    $item['ZONA_TRABAJO_RIESGO#' . $count] = $zt['descripcion'];
                    $item['FECHA_TOMA_DATOS_RIESGO_Z#' . $count] = $evaluacion->getFechaInicio()->format('d/m/Y');
                    $item['TIPO_EVALUACION_RIESGO_Z#' . $count] = $tipoEvaluacionDesc;
                    $item['CENTRO_DESCRIPCION_Z#' . $count] = $actividadCentro;

                    array_push($arrayZonaTrabajo, $item);
                    $templateProcessor->cloneBlock('block_zona_trabajo#' . $count, 1, true, false, $arrayZonaTrabajo);
                }
                //Generamos las tablas con los riesgos-causas
                $arrayRiesgosZonaTrabajo = array();

                $query = "select a.id, b.descripcion as severidad, c.descripcion as probabilidad, d.descripcion as valorriesgo, e.descripcion as causa, f.codigo as riesgocodigo, f.descripcion as riesgo, a.observacion_causa from riesgo_causa_evaluacion a
                        left join severidad b on a.severidad_id = b.id
                        left join probabilidad c on a.probabilidad_id = c.id
                        left join valor_riesgo d on a.valor_riesgo_id = d.id
                        left join causa e on a.causa_id = e.id
                        left join riesgo f on a.riesgo_id = f.id
                        where a.evaluacion_id = $evaluacionId 
                        and a.zona_trabajo_id = $zonaTrabajoId
                        and a.anulado = false
                        order by f.codigo asc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $riesgosCausas = $stmt->fetchAll();

                foreach ($riesgosCausas as $rc) {
                    $item = array();
                    $item['CODIGO_RIESGO_Z#' . $count] = $rc['riesgocodigo'];
                    $item['NOMBRE_RIESGO_Z#' . $count] = $rc['riesgo'];
                    $item['SEVERIDAD_RIESGO_Z#' . $count] = $rc['severidad'];
                    $item['PROBABILIDAD_RIESGO_Z#' . $count] = $rc['probabilidad'];
                    $item['VALOR_RIESGO_Z#' . $count] = strtoupper($rc['valorriesgo']);
                    $causaAux = preg_replace('/\([^)]*\)/', '', $rc['causa']);
                    $item['CAUSA_RIESGO_Z#' . $count] = $causaAux;
                    $item['OBSERVACION_CAUSA_RIESGO_Z#' . $count] = $rc['observacion_causa'];

                    $riesgoCausaId = $rc['id'];

                    //Buscamos la planificacion
                    $query = "select b.descripcion as tipoplanificacion, case when substring(b.descripcion, 1,1) = 'P' then 'CONTINUO' ELSE to_char(a.fecha_prevista, 'DD/MM/YYYY') END as fechaprevista, to_char(a.fecha_realizacion , 'DD/MM/YYYY') as fecharealizacion, a.responsable, a.trabajadores from planificacion_riesgo_causa a
                        left join tipo_planificacion b on a.tipo_planificacion_id = b.id
                        where a.riesgo_causa_id = $riesgoCausaId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $planificacionRiesgosCausas = $stmt->fetchAll();

                    if (count($planificacionRiesgosCausas) > 0) {
                        $item['T_P_Z#' . $count] = $planificacionRiesgosCausas[0]['tipoplanificacion'];
                        $item['FECHA_PREVISION_Z#' . $count] = $planificacionRiesgosCausas[0]['fechaprevista'];
                        $item['FECHA_REALIZACION_Z#' . $count] = $planificacionRiesgosCausas[0]['fecharealizacion'];

                        if ($planificacionRiesgosCausas[0]['trabajadores']) {
                            $item['RESPONSABLE_EMPRESA_Z#' . $count] = strtoupper($planificacionRiesgosCausas[0]['responsable']) . ' - ' . 'Trabajadores/as';
                        } else {
                            $item['RESPONSABLE_EMPRESA_Z#' . $count] = strtoupper($planificacionRiesgosCausas[0]['responsable']);
                        }
                    } else {
                        $item['T_P_Z#' . $count] = '';
                        $item['FECHA_PREVISION_Z#' . $count] = '';
                        $item['FECHA_REALIZACION_Z#' . $count] = '';
                        $item['RESPONSABLE_EMPRESA_Z#' . $count] = '';
                    }

                    //Buscamos las medidas preventivas
                    $query = "select descripcion from accion_preventiva_empresa_riesgo_causa
                    where anulado = false
                    and riesgo_causa_id = $riesgoCausaId
                    order by descripcion asc";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $medidasPreventivas = $stmt->fetchAll();
                    $medidasPreventivasRiesgoCausa = "</w:t>\n<w:t xml:space=\"preserve\"><w:rPr><w:b w:val=\"0\" /></w:rPr>";
                    $countMedidasPreventivas = 1;
                    // Agrega dos espacios a la primera línea
                    $medidasPreventivasRiesgoCausa .= "\n•    " . wordwrap($medidasPreventivas[0]['descripcion'], 133, "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\"><w:rPr><w:b w:val=\"0\" /></w:rPr>       ", true) . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\"><w:rPr><w:b w:val=\"0\" /></w:rPr>";

                    for ($i = 1; $i < count($medidasPreventivas); $i++) {
                        // Concatena las descripciones de las líneas siguientes al fragmento existente
                        $medidasPreventivasRiesgoCausa .= "\n•    " . wordwrap($medidasPreventivas[$i]['descripcion'], 133, "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\"><w:rPr><w:b w:val=\"0\" /></w:rPr>       ", true) . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\"><w:rPr><w:b w:val=\"0\" /></w:rPr>";
                        $countMedidasPreventivas++;
                    }
                    $item['MEDIDAS_PREVENTIVAS_Z#' . $count] = $medidasPreventivasRiesgoCausa;

                    //Buscamos si los riesgos tienes imagenes
                    $query = "select nombre from riesgo_causa_img where anulado = false and riesgo_causa_id = $riesgoCausaId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $imagenesRiesgos = $stmt->fetchAll();

                    $contadorImagenesRiesgos = count($imagenesRiesgos);
                    if ($contadorImagenesRiesgos > 0) {
                        $listImagenes = "";
                        foreach ($imagenesRiesgos as $imgRiesgos) {
                            $nombreImagen = $imgRiesgos['nombre'];
                            array_push($arrayImagenesRiesgosZonas, $nombreImagen);
                            $listImagenes .= '${' . $nombreImagen . '}' . "---";
                            $item['IMAGENES_RIESGOS_Z#' . $count] = $listImagenes;
                        }
                    } else {
                        $item['IMAGENES_RIESGOS_Z#' . $count] = '';
                    }
                    array_push($arrayRiesgosZonaTrabajo, $item);
                }

                //Clonamos los riesgos del puesto de trabajo
                $templateProcessor->cloneBlock('block_riesgos_zonas#' . $count, count($arrayRiesgosZonaTrabajo), true, false, $arrayRiesgosZonaTrabajo);

                $count++;
            }
            if ($puestosTrabajoSeleccionadosSelect2 === "0") {
            } else {
                $cadena = implode(',', $puestosTrabajoSeleccionadosSelect2);
            }
            if ($puestosTrabajoSeleccionadosSelect2[0] === "0" || $puestosTrabajoSeleccionadosSelect2 === "0") {
                $query = "select b.id, b.descripcion, a.tarea,
                (select string_agg(distinct me.descripcion ::text, ' , '::text) from maquina_empresa_trabajador met inner join maquina_empresa me on met.maquina_empresa_id = me.id where a.puesto_trabajo_id = met.puesto_trabajo_id and met.anulado = false) as maquina,               
                (select string_agg(distinct ptc.nombre ::text, ' , '::text) from puesto_trabajo_contaminante ptc where a.puesto_trabajo_id = ptc.puesto_trabajo_id and ptc.anulado = false) as quimico
                from puesto_trabajo_evaluacion a
                inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                where a.anulado = false
                and a.evaluacion_id = $evaluacionId
                order by b.descripcion ASC";
            } else {
                $query = "select b.id, b.descripcion, a.tarea,
                (select string_agg(distinct me.descripcion ::text, ' , '::text) from maquina_empresa_trabajador met inner join maquina_empresa me on met.maquina_empresa_id = me.id where a.puesto_trabajo_id = met.puesto_trabajo_id and met.anulado = false) as maquina,               
                (select string_agg(distinct ptc.nombre ::text, ' , '::text) from puesto_trabajo_contaminante ptc where a.puesto_trabajo_id = ptc.puesto_trabajo_id and ptc.anulado = false) as quimico
                from puesto_trabajo_evaluacion a
                inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                where a.anulado = false and a.id IN($cadena)
                and a.evaluacion_id = $evaluacionId
                order by b.descripcion ASC";
            }
            //Buscamos los riesgos de cada puesto de trabajo

            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $puestosTrabajos = $stmt->fetchAll();

            //Clonamos las tablas por cada puesto de trabajo a evaluar
            $templateProcessor->cloneBlock('block_puestos_trabajos', count($puestosTrabajos), true, true);

            //Creamos el array para despues reemplazar los nombres por la ruta de la imagen
            $arrayImagenesRiesgos = array();

            $count = 1;
            foreach ($puestosTrabajos as $pt) {
                $puestoTrabajoId =  $pt['id'];

                //Generamos la primera tabla con el riesgo
                $arrayPuestoTrabajo = array();
                $item = array();
                $item['NUMERO_RIESGO#' . $count] = $count;
                $item['NOMBRE_EMPRESA#' . $count] = $nombre;
                $item['CENTRO_RIESGO#' . $count] = $direccionCentro;
                $item['PUESTO_TRABAJO_RIESGO#' . $count] = $pt['descripcion'];
                $item['FECHA_TOMA_DATOS_RIESGO#' . $count] = $evaluacion->getFechaInicio()->format('d/m/Y');
                $item['TIPO_EVALUACION_RIESGO#' . $count] = $tipoEvaluacionDesc;
                $item['PUESTO_TRABAJO_TAREA#' . $count] = $pt['tarea'];
                //Peticio 01/09/2023 Mejora continua #67377 - Incorporar maquinaria en sección evaluación
                if ($pt['maquina'] == null) {
                    $item['PUESTO_TRABAJO_MAQUINARIA#' . $count] = " ";
                } else {
                    $item['PUESTO_TRABAJO_MAQUINARIA#' . $count] = $pt['maquina'];
                }
                if ($pt['quimico'] == null) {
                    $item['PUESTO_TRABAJO_QUIMICO#' . $count] = " ";
                } else {
                    $item['PUESTO_TRABAJO_QUIMICO#' . $count] = $pt['quimico'];
                }
                array_push($arrayPuestoTrabajo, $item);

                //Clonamos la información del puesto de trabajo
                $templateProcessor->cloneBlock('block_puesto_trabajo#' . $count, 1, true, false, $arrayPuestoTrabajo);

                //Generamos las tablas con los riesgos-causas
                $arrayRiesgosPuestoTrabajo = array();

                $query = "select a.id, b.descripcion as severidad, c.descripcion as probabilidad, d.descripcion as valorriesgo, e.descripcion as causa, f.codigo as riesgocodigo, f.descripcion as riesgo, a.observacion_causa from riesgo_causa_evaluacion a
                        left join severidad b on a.severidad_id = b.id
                        left join probabilidad c on a.probabilidad_id = c.id
                        left join valor_riesgo d on a.valor_riesgo_id = d.id
                        left join causa e on a.causa_id = e.id
                        left join riesgo f on a.riesgo_id = f.id
                        where a.evaluacion_id = $evaluacionId 
                        and a.puesto_trabajo_id = $puestoTrabajoId
                        and a.anulado = false
                        order by f.codigo asc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $riesgosCausas = $stmt->fetchAll();

                foreach ($riesgosCausas as $rc) {
                    $item = array();
                    $item['CODIGO_RIESGO#' . $count] = $rc['riesgocodigo'];
                    $item['NOMBRE_RIESGO#' . $count] = $rc['riesgo'];
                    $item['SEVERIDAD_RIESGO#' . $count] = $rc['severidad'];
                    $item['PROBABILIDAD_RIESGO#' . $count] = $rc['probabilidad'];
                    $item['VALOR_RIESGO#' . $count] = strtoupper($rc['valorriesgo']);
                    $causaAux = preg_replace('/\([^)]*\)/', '', $rc['causa']);
                    $item['CAUSA_RIESGO#' . $count] = $causaAux;
                    //fix Ticket#2025032010000089 20/03/2025
                    $item['OBSERVACION_CAUSA_RIESGO#' . $count] = str_replace("<", '&lt;', $rc['observacion_causa']);

                    $riesgoCausaId = $rc['id'];

                    //Buscamos la planificacion
                    $query = "select b.descripcion as tipoplanificacion, case when substring(b.descripcion, 1,1) = 'P' then 'CONTINUO' ELSE to_char(a.fecha_prevista, 'DD/MM/YYYY') END as fechaprevista, case when substring(b.descripcion, 1,1) = 'P' then 'PERIODICAMENTE' ELSE to_char(a.fecha_realizacion, 'DD/MM/YYYY') END as fecharealizacion, a.responsable, a.trabajadores from planificacion_riesgo_causa a
                        left join tipo_planificacion b on a.tipo_planificacion_id = b.id
                        where a.riesgo_causa_id = $riesgoCausaId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $planificacionRiesgosCausas = $stmt->fetchAll();

                    if (count($planificacionRiesgosCausas) > 0) {
                        $item['TIPO_PLANIFICACION#' . $count] = $planificacionRiesgosCausas[0]['tipoplanificacion'];
                        $item['FECHA_PREVISION#' . $count] = $planificacionRiesgosCausas[0]['fechaprevista'];
                        $item['FECHA_REALIZACION#' . $count] = $planificacionRiesgosCausas[0]['fecharealizacion'];

                        if ($planificacionRiesgosCausas[0]['trabajadores']) {
                            $item['RESPONSABLE_EMPRESA#' . $count] = strtoupper($planificacionRiesgosCausas[0]['responsable']) . ' - ' . 'Trabajadores/as';
                        } else {
                            $item['RESPONSABLE_EMPRESA#' . $count] = strtoupper($planificacionRiesgosCausas[0]['responsable']);
                        }
                    } else {
                        $item['TIPO_PLANIFICACION#' . $count] = '';
                        $item['FECHA_PREVISION#' . $count] = '';
                        $item['FECHA_REALIZACION#' . $count] = '';
                        $item['RESPONSABLE_EMPRESA#' . $count] = '';
                    }
                    //Buscamos las medidas preventivas
                    $query = "select descripcion from accion_preventiva_empresa_riesgo_causa
                    where anulado = false
                    and riesgo_causa_id = $riesgoCausaId
                    order by descripcion asc";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $medidasPreventivas = $stmt->fetchAll();
                    $medidasPreventivasRiesgoCausa = "</w:t>\n<w:t xml:space=\"preserve\"><w:rPr><w:b w:val=\"0\" /></w:rPr>";
                    $countMedidasPreventivas = 1;
                    // Agrega dos espacios a la primera línea
                    //eliminar
                    //if(!empty($medidasPreventivas)) {
                        $medidasPreventivasRiesgoCausa .= "\n•    " . wordwrap($medidasPreventivas[0]['descripcion'], 133, "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\"><w:rPr><w:b w:val=\"0\" /></w:rPr>       ", true) . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\"><w:rPr><w:b w:val=\"0\" /></w:rPr>";
                    //}

                    for ($i = 1; $i < count($medidasPreventivas); $i++) {
                        // Concatena las descripciones de las líneas siguientes al fragmento existente
                        $medidasPreventivasRiesgoCausa .= "\n•    " . wordwrap($medidasPreventivas[$i]['descripcion'], 133, "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\"><w:rPr><w:b w:val=\"0\" /></w:rPr>       ", true) . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\"><w:rPr><w:b w:val=\"0\" /></w:rPr>";
                        $countMedidasPreventivas++;
                    }
                    $item['MEDIDAS_PREVENTIVAS#' . $count] = $medidasPreventivasRiesgoCausa;

                    //Buscamos si los riesgos tienes imagenes
                    $query = "select nombre from riesgo_causa_img where anulado = false and riesgo_causa_id = $riesgoCausaId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $imagenesRiesgos = $stmt->fetchAll();

                    $contadorImagenesRiesgos = count($imagenesRiesgos);
                    if ($contadorImagenesRiesgos > 0) {
                        $listImagenes = "";
                        foreach ($imagenesRiesgos as $imgRiesgos) {
                            $nombreImagen = $imgRiesgos['nombre'];
                            array_push($arrayImagenesRiesgos, $nombreImagen);
                            $listImagenes .= '${' . $nombreImagen . '}' . "---";
                            $item['IMAGENES_RIESGOS#' . $count] = $listImagenes;
                        }
                    } else {
                        $item['IMAGENES_RIESGOS#' . $count] = '';
                    }

                    array_push($arrayRiesgosPuestoTrabajo, $item);
                }
                //Clonamos los riesgos del puesto de trabajo
                $templateProcessor->cloneBlock('block_riesgos#' . $count, count($arrayRiesgosPuestoTrabajo), true, false, $arrayRiesgosPuestoTrabajo);

                $count++;
            }
            //Modificació ticket error llistat treballadors a evalaucions anexo 2
            //Buscamos la lista de los trabajadores con sus puestos de trabajo
            if(!$dosCentrosSn && !$multiCentroSn) {

                if (is_null($evaluacionCentro1Id)) {
                    $query = "select distinct e.nombre, e.dni, c.descripcion as puestotrabajo from puesto_trabajo_evaluacion a
                    left join puesto_trabajo_trabajador b on a.puesto_trabajo_id = b.puesto_trabajo_id
                    left join puesto_trabajo_centro c on b.puesto_trabajo_id = c.id
                    left join trabajador e on b.trabajador_id = e.id
                    where a.anulado = false
                    and a.evaluacion_id = $evaluacionId
                    order by e.nombre, e.dni, c.descripcion asc";
                } else {
                    $query = "select distinct e.nombre, e.dni, c.descripcion as puestotrabajo from puesto_trabajo_evaluacion a
                    inner join puesto_trabajo_trabajador b on a.puesto_trabajo_id = b.puesto_trabajo_id
                    inner join puesto_trabajo_centro c on b.puesto_trabajo_id = c.id
                    inner join trabajador e on b.trabajador_id = e.id
                    inner join trabajador_alta_baja f on e.id = f.trabajador_id 
                    where a.anulado = false
                    and a.evaluacion_id = $evaluacionId
                    and b.anulado = false
                    and c.anulado = false
                    and e.anulado = false
                    and c.descripcion not like '%GENERAL A TODOS LOS PUESTOS DE TRABAJO%'
                    and f.empresa_id = $empresaId
                    and f.anulado = false
                    and f.activo = true
                    and f.fecha_baja is null
                    order by e.nombre, e.dni, c.descripcion asc";
                }
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $trabajadores = $stmt->fetchAll();
                $arrayTrabajadoresPuestoTrabajo = array();
                foreach ($trabajadores as $t) {
                    $item = array();
                    $item['NOMBRE_TRABAJADOR'] = $t['nombre'];
                    $item['DNI_TRABAJADOR'] = $t['dni'];
                    $item['PUESTO_TRABAJO_TRABAJADOR'] = $t['puestotrabajo'];
                    array_push($arrayTrabajadoresPuestoTrabajo, $item);
                }
                //Clonamos la lista de trabajadores
                $templateProcessor->cloneRowAndSetValues('NOMBRE_TRABAJADOR', $arrayTrabajadoresPuestoTrabajo);
            }
            //Si se trata de una evaluación con mas de un centro se añade la lista de trabajadores del otro centro
            if ($dosCentrosSn) {
                $query = "select distinct e.nombre, e.dni, c.descripcion as puestotrabajo from puesto_trabajo_evaluacion a
                inner join puesto_trabajo_trabajador b on a.puesto_trabajo_id = b.puesto_trabajo_id
                inner join puesto_trabajo_centro c on b.puesto_trabajo_id = c.id
                inner join trabajador e on b.trabajador_id = e.id
                inner join trabajador_alta_baja f on e.id = f.trabajador_id 
                where a.anulado = false
                and a.evaluacion_id = $evaluacionId
                and b.anulado = false
                and c.anulado = false
                and e.anulado = false
                and c.descripcion not like '%GENERAL A TODOS LOS PUESTOS DE TRABAJO%'
                and b.centro_id = $evaluacionCentro2Id
                and f.empresa_id = $empresaId
                and f.anulado = false
                and f.activo = true
                and f.fecha_baja is null
                order by e.nombre, e.dni, c.descripcion  asc";

                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $trabajadores = $stmt->fetchAll();
                $arrayTrabajadoresPuestoTrabajo2 = array();

                foreach ($trabajadores as $t) {
                    $item = array();
                    $item['NOMBRE_TRABAJADOR2'] = $t['nombre'];
                    $item['DNI_TRABAJADOR2'] = $t['dni'];
                    $item['PUESTO_TRABAJO_TRABAJADOR2'] = $t['puestotrabajo'];
                    array_push($arrayTrabajadoresPuestoTrabajo2, $item);
                }
                $templateProcessor->cloneRowAndSetValues('NOMBRE_TRABAJADOR2', $arrayTrabajadoresPuestoTrabajo2);
            }

            if ($multiCentroSn) {
                $templateProcessor->cloneBlock('block_centro_trabajo', count($arrayCentroTrabajoEvaluacion), true, true);

                $count = 1;
                foreach ($arrayCentroTrabajoEvaluacion as $act) {
                    $templateProcessor->setValue('CENTRO_TEXTO#' . $count, $act['CENTRO_TEXTO#' . $count]);
                    $templateProcessor->setValue('CENTRO_DIRECCION_MULTI#' . $count, $act['CENTRO_DIRECCION#' . $count]);
                    $templateProcessor->setValue('CENTRO_LOCALIDAD_MULTI#' . $count, $act['CENTRO_LOCALIDAD#' . $count]);
                    $templateProcessor->setValue('CENTRO_PROVINCIA_MULTI#' . $count, $act['CENTRO_PROVINCIA#' . $count]);
                    $templateProcessor->setValue('CENTRO_TELEFONO_MULTI#' . $count, $act['CENTRO_TELEFONO#' . $count]);
                    $templateProcessor->setValue('CENTRO_DESCRIPCION_MULTI#' . $count, $act['CENTRO_DESCRIPCION#' . $count]);
                    $count++;
                }
                $templateProcessor->cloneBlock('block_trabajador_empresa', count($arrayIdCentroTrabajoEvaluacion), true, true);
                $count = 1;
                foreach ($arrayIdCentroTrabajoEvaluacion as $acte) {
                    $query = "select distinct e.nombre, e.dni, c.descripcion as puestotrabajo from puesto_trabajo_evaluacion a
                    inner join puesto_trabajo_trabajador b on a.puesto_trabajo_id = b.puesto_trabajo_id
                    inner join puesto_trabajo_centro c on b.puesto_trabajo_id = c.id
                    inner join trabajador e on b.trabajador_id = e.id
                    inner join trabajador_alta_baja f on e.id = f.trabajador_id 
                    where a.anulado = false
                    and a.evaluacion_id = $evaluacionId
                    and b.anulado = false
                    and c.anulado = false
                    and e.anulado = false
                    and c.descripcion not like '%GENERAL A TODOS LOS PUESTOS DE TRABAJO%'
                    and b.centro_id = $acte
                    and f.empresa_id = $empresaId
                    and f.anulado = false
                    and f.activo = true
                    and f.fecha_baja is null
                    order by e.nombre, e.dni, c.descripcion asc";

                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $trabajadores = $stmt->fetchAll();
                    $arrayTrabajadoresPuestoTrabajoMulti = array();

                    foreach ($trabajadores as $t) {
                        $item = array();
                        $item['NOMBRE_TRABAJADOR_MULTI#' . $count] = $t['nombre'];
                        $item['DNI_TRABAJADOR_MULTI#' . $count] = $t['dni'];
                        $item['PUESTO_TRABAJO_TRABAJADOR_MULTI#' . $count] = $t['puestotrabajo'];
                        array_push($arrayTrabajadoresPuestoTrabajoMulti, $item);
                    }
                    $centroTrabajoObj = $em->getRepository('App\Entity\Centro')->find($acte);
                    $templateProcessor->setValue("EMPRESA_NOMBRE_MULTI#" . $count, $empresa->getEmpresa());
                    $templateProcessor->setValue("FECHA_MULTI#" . $count, $hoyString);
                    $templateProcessor->setValue("CENTRO_DIRECCION_MULTI#" . $count, $centroTrabajoObj->getDireccion());

                    if (count($arrayTrabajadoresPuestoTrabajoMulti) == 0) {
                        $item = array();
                        $item['NOMBRE_TRABAJADOR_MULTI#' . $count] = '';
                        $item['DNI_TRABAJADOR_MULTI#' . $count] = '';
                        $item['PUESTO_TRABAJO_TRABAJADOR_MULTI#' . $count] = '';
                        array_push($arrayTrabajadoresPuestoTrabajoMulti, $item);
                    }
                    $templateProcessor->cloneRowAndSetValues('NOMBRE_TRABAJADOR_MULTI#' . $count, $arrayTrabajadoresPuestoTrabajoMulti);
                    $count++;
                }
                //$templateProcessor->cloneBlock('EMPRESA_NOMBRE_MULTI#'.$count, count($arrayTrabajadoresPuestoTrabajoMulti), true, false, $arrayTrabajadoresPuestoTrabajoMulti);

                //Clonamos los centros evaluados e insertamos los valores
                $templateProcessor->cloneBlock('block_centros_evaluados', count($arrayCentroTrabajoEvaluacion), true, true);
                $count = 1;
                foreach ($arrayCentroTrabajoEvaluacion as $act) {
                    $templateProcessor->setValue('CENTRO_EVALUADO_FICHA#' . $count, 'C' . $count);
                    $templateProcessor->setValue('CENTRO_EVALUADO_DIRECCION#' . $count, $act['CENTRO_DIRECCION#' . $count]);
                    $count++;
                }
            }
            //Buscamos la maquinaria de la empresa
            $query = "select distinct descripcion as equipo, placa_caracteristica, fabricante, modelo, num_serie, anyo_fabricacion, marcado_ce, conformidad, manual_instrucciones from maquina_empresa 
                      where anulado = false 
                      and empresa_id = $empresaId
                      order by descripcion ASC";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $maquinas = $stmt->fetchAll();
            $arrayMaquinariaEmpresa = array();
            foreach ($maquinas as $m) {
                $item = array();
                $item['EQUIPO_MAQUINA'] = $m['equipo'];
                $item['PLACA_MAQUINA'] = $m['placa_caracteristica'];
                $item['FABRICANTE_MAQUINA'] = $m['fabricante'];
                $item['MODELO_MAQUINA'] = $m['modelo'];
                $item['NUM_SERIE_MAQUINA'] = $m['num_serie'];
                $item['AÑO_FABRICACION_MAQUINA'] = $m['anyo_fabricacion'];
                $item['CE_MAQUINA'] = $m['marcado_ce'];

                if ($m['conformidad'] == true) {
                    $item['CONFORMIDAD_MAQUINA'] = 'SI';
                } else {
                    $item['CONFORMIDAD_MAQUINA'] = 'NO';
                }
                if ($m['manual_instrucciones'] == true) {
                    $item['MANUAL_MAQUINA'] = 'SI';
                } else {
                    $item['MANUAL_MAQUINA'] = 'NO';
                }
                array_push($arrayMaquinariaEmpresa, $item);
            }
            //eliminar
            $templateProcessor->cloneRowAndSetValues('EQUIPO_MAQUINA', $this->cleanText($arrayMaquinariaEmpresa));
            //$templateProcessor->cloneRowAndSetValues('EQUIPO_MAQUINA', $arrayMaquinariaEmpresa);

            //Recorremos el array de imagenes añadir las imagenes de las zonas de trabajo
            foreach ($arrayImagenesRiesgosZonas as $arrayImagenRiesgoZona) {
                $nombreImagen = str_replace('${', '', $arrayImagenRiesgoZona);
                $nombreImagen = str_replace('}', '', $nombreImagen);
                if (file_exists("upload/media/evaluaciones/causas/$nombreImagen")) {
                    $templateProcessor->setImageValue($arrayImagenRiesgoZona, 'upload/media/evaluaciones/causas/' . $nombreImagen);
                } else {
                    $templateProcessor->setValue($arrayImagenRiesgoZona, null);
                }
            }

            //Recorremos el array de imagenes añadir las imagenes de los puestos de trabajo
            foreach ($arrayImagenesRiesgos as $arrayImagenRiesgo) {
                $nombreImagen = str_replace('${', '', $arrayImagenRiesgo);
                $nombreImagen = str_replace('}', '', $nombreImagen);
                if (file_exists("upload/media/evaluaciones/causas/$nombreImagen")) {
                    $templateProcessor->setImageValue($arrayImagenRiesgo, 'upload/media/evaluaciones/causas/' . $nombreImagen);
                } else {
                    $templateProcessor->setValue($arrayImagenRiesgo, null);
                }
            }
        }
        if ($citacionSn) {
            $fechaCitacion = $citacion->getFechainicio()->format('d') . ' de ' . $this->obtenerMes($citacion->getFechainicio()->format('m')) . ' de ' . $citacion->getFechainicio()->format('Y') . ' - ' . $citacion->getFechaInicio()->format('H:i');
            $fechaCitacionSinHora = $citacion->getFechainicio()->format('d') . ' de ' . $this->obtenerMes($citacion->getFechainicio()->format('m')) . ' de ' . $citacion->getFechainicio()->format('Y');

            $templateProcessor->setValue("CITACION_FECHA", $fechaCitacion);
            $templateProcessor->setValue("CITACION_FECHA_SIN_HORA", $fechaCitacionSinHora);
            $templateProcessor->setValue("CITACION_DIRECCION", $citacion->getAgenda()->getDireccion());

            if (!is_null($citacion->getTrabajador())) {
                $nombreTrabajadorCitacion = $citacion->getTrabajador()->getNombre();
                $dniTrabajadorCitacion = $citacion->getTrabajador()->getDni();
                $templateProcessor->setValue("CITACION_TRABAJADOR_NOMBRE", $nombreTrabajadorCitacion);
                $templateProcessor->setValue("CITACION_TRABAJADOR_DNI", $dniTrabajadorCitacion);
            }
        }
        //Afegir plantilles
        if ($planPrevencionSn) {
            $fechaPlanPrevencion = $hoy->format('d') . ' / ' . $this->obtenerMes($hoy->format('m')) . ' / ' . $hoy->format('Y');
            $templateProcessor->setValue("FECHA_PLAN_PREVENCION", $fechaPlanPrevencion);

            //Buscamos el tecnico de la EMPRESA y la firma
            $tecnicosEmpresa = $em->getRepository('App\Entity\TecnicoEmpresa')->findOneBy(array('empresa' => $empresa, 'anulado' => false));
            $tecnicoEmpresa = null;
            $tecnicoEmpresaCorreo = null;
            if (!is_null($tecnicosEmpresa)) {
                $tecnicoEmpresa = $tecnicosEmpresa->getTecnico()->getNombre();
                $tecnicoEmpresaCorreo = $tecnicosEmpresa->getTecnico()->getCorreo();
            }
            $templateProcessor->setValue("TECNICO_EMPRESA", $tecnicoEmpresa);
            $templateProcessor->setValue("TECNICO_EMPRESA_CORREO", $tecnicoEmpresaCorreo);

            //Buscamos si la empresa tiene algun plan de prevencion previo
            $planPrevencionPrevio = $em->getRepository('App\Entity\EmpresaPlanPrevencion')->findBy(array('empresa' => $empresa));
            if (count($planPrevencionPrevio) == 0) {
                $templateProcessor->setValue("PRL_TIPO", 'I');
            } else {
                $templateProcessor->setValue("PRL_TIPO", 'R');
            }
            $templateProcessor->setValue("PRL_CONTRATO", $contratoNumero);

            //Petició 28/07/2023 #63749
            $queryAux3 = "SELECT COUNT(*) AS total_registros
            FROM (
                SELECT (SELECT numero
                        FROM (
                            SELECT id, empresa_id, row_number() OVER (ORDER BY fecha ASC) AS numero
                            FROM empresa_plan_prevencion
                            WHERE empresa_id = a.empresa_id
                            ORDER BY fecha ASC
                        ) consulta
                        WHERE id = a.id) AS numero
                FROM empresa_plan_prevencion a
                WHERE a.empresa_id = $empresaId
            ) subconsulta";

            $stmt3 = $this->getDoctrine()->getManager()->getConnection()->prepare($queryAux3);
            $stmt3->execute();
            $numPlanPre = $stmt3->fetchAll();
            $numPlanPre2 = 1 + $numPlanPre[0]['total_registros'];

            $templateProcessor->setValue("NUM_PLAN_PRE", $numPlanPre2);

            //Buscamos las tarifas por RM
            $query = "select importe from tarifa_revision_medica where anulado = false and empresa_id = $empresaId ";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $importeRevisionMedica = $stmt->fetchAll();
            if (count($importeRevisionMedica) > 0) {
                $templateProcessor->setValue("PRL_IMPORTE_RM", number_format(round($importeRevisionMedica[0]['importe'], 2), 2, ',', '.'));
            } else {
                $templateProcessor->setValue("PRL_IMPORTE_RM", '0,00');
            }
            $templateProcessor->setValue("PRL_IMPORTE_CONTRATO", number_format(round($importePrevencion, 2), 2, ',', '.'));
        }
        if ($revisionSn) {
            $revisionId = $revision->getId();

            if (!is_null($revision->getFecha())) {
                $fechaRevision = $revision->getFecha()->format('d') . ' / ' . $revision->getFecha()->format('m') . ' / ' . $revision->getFecha()->format('Y');
            }
            $templateProcessor->setValue("FECHA_REVISION", $fechaRevision);

            $templateProcessor->setValue("REVISION_PUESTO_TRABAJO", $revision->getPuestoTrabajo()->getDescripcion());

            if (!is_null($revision->getFechaCertificacion())) {
                $fechaAptitud = $revision->getFechaCertificacion()->format('d') . ' / ' . $revision->getFechaCertificacion()->format('m') . ' / ' . $revision->getFechaCertificacion()->format('Y');
            }
            $templateProcessor->setValue("FECHA_APTITUD", $fechaAptitud);

            if (!is_null($revision->getValidez())) {
                $templateProcessor->setValue("VALIDEZ", $revision->getValidez()->getDescripcion());
            } else {
                $templateProcessor->setValue("VALIDEZ", '');
            }
            $query = "select respuesta from revision_respuesta where revision_id = $revisionId and pregunta_id = 280";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $tipoReconocimientoResult = $stmt->fetchAll();

            if (count($tipoReconocimientoResult) > 0) {
                $templateProcessor->setValue("TIPO_RECONOCIMIENTO", $tipoReconocimientoResult[0]['respuesta']);
            } else {
                $templateProcessor->setValue("TIPO_RECONOCIMIENTO", '');
            }
            if (!is_null($revision->getApto())) {
                $aptitudRevisionId = $revision->getApto()->getId();
                switch ($aptitudRevisionId) {
                    case 1:
                        $aptitudRevision = ($idiomaPlantilla == 'ESP') ? 'APTO' : 'APTE';
                        break;
                    case 2:
                        $aptitudRevision = $revision->getApto()->getDescripcion();
                        if (!is_null($revision->getAptitudRestriccion())) {
                            $aptitudRestriccion = ' - ' . $revision->getAptitudRestriccion()->getDescripcion();
                        }
                        break;
                    case 3:
                        $aptitudRevision = ($idiomaPlantilla == 'ESP') ? 'APTE' : 'NO APTE';
                        break;
                }
            }
            $templateProcessor->setValue("APTITUD_REVISION", $aptitudRevision);
            $templateProcessor->setValue("APTITUD_RESTRICCION_TIPO", $aptitudRestriccion);

            $firmaMedico = "";
            $colegiadoMedico = "";
            $especialidadMedico = "";
            $gestoraMedico = "";

            if (!is_null($revision->getMedico())) {
                $doctorRevision = $revision->getMedico()->getDescripcion();
                $firmaMedico = $revision->getMedico()->getFirma();
                $colegiadoMedico = $revision->getMedico()->getNumeroColegiado();
                $especialidadMedico = $revision->getMedico()->getEspecialidad();
                $gestoraMedico = $revision->getMedico()->getGestora();
            }
            $templateProcessor->setValue("DOCTOR_REVISION", $doctorRevision);

            if ($firmaMedico != "") {
                $templateProcessor->setImageValue('FIRMA_DOCTOR_REVISION', 'upload/media/firmas/medico/' . $firmaMedico);
            } else {
                $templateProcessor->setValue('FIRMA_DOCTOR_REVISION', null);
            }
            $templateProcessor->setValue('COLEGIADO_DOCTOR_REVISION', $colegiadoMedico);
            $templateProcessor->setValue('ESPECIALIDAD_DOCTOR_REVISION', $especialidadMedico);
            $templateProcessor->setValue('GESTORA_DOCTOR_REVISION', $gestoraMedico);

            if (!is_null($revision->getPuestoTrabajo())) {
                $revisionId = $revision->getId();

                $query = "select distinct c.descripcion as protocolo, c.descripcion_ca as protocolo_ca from revision a
                inner join puesto_trabajo_protocolo b on a.puesto_trabajo_id = b.puesto_trabajo_id 
                inner join protocolo c on b.protocolo_id = c.id
                where a.id = $revisionId
                and b.empresa_id = $empresaId
                order by c.descripcion asc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $protocolosRevisionArray = $stmt->fetchAll();
                $protocolosRevision1 = null;
                $protocolosRevision2 = null;
                $countProtocolos = 0;

                foreach ($protocolosRevisionArray as $pra) {
                    if ($idiomaPlantilla == 'ESP') {
                        $protocoloDesc = $pra['protocolo'];
                    } else {
                        $protocoloDesc = $pra['protocolo_ca'];
                    }
                    if ($countProtocolos > 12) {
                        $protocolosRevision2 .= $protocoloDesc . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                    } else {
                        $protocolosRevision1 .= $protocoloDesc . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                    }
                    $countProtocolos++;
                }
            }
            $templateProcessor->setValue('PROTOCOLOS_REVISION_1', $protocolosRevision1);
            $templateProcessor->setValue('PROTOCOLOS_REVISION_2', $protocolosRevision2);
        }
        if ($fichaRiesgosSn) {
            //Buscamos el nombre del puesto de trabajo
            $puestoTrabajoFicha = $puestoTrabajoEvaluacion->getPuestoTrabajo();
            $templateProcessor->setValue('PUESTO_TRABAJO_FICHA_RIESGOS', $puestoTrabajoFicha->getDescripcion());

            //Buscamos los riesgos, sus causa y sus medidas preventivas
            $puestoTrabajoId = $puestoTrabajoFicha->getId();
            if ($evaluacionZonaTrabajo != null) {
                $evaluacionId = $evaluacionZonaTrabajo->getId();
                $puestoZonaTrabajoEvaluacion = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajoEvaluacion')->find($puestoTrabajoEvaluacion->getId());
                $zonaPuestoTrabajoId = $puestoZonaTrabajoEvaluacion->getZonaTrabajo()->getId();
                $condition = "a.zona_trabajo_id = " . $zonaPuestoTrabajoId ;
            } else {
                $evaluacion = $puestoTrabajoEvaluacion->getEvaluacion();
                $evaluacionId = $evaluacion->getId();
                $condition = "a.puesto_trabajo_id = " . $puestoTrabajoId ;
            }


            //fix 26/03/2025 #2025032610000041
            $query = "select distinct a.id, a.riesgo_id, b.descripcion as riesgo, b.codigo, c.descripcion as causa from riesgo_causa_evaluacion a
            inner join riesgo b on a.riesgo_id = b.id
            inner join causa c on a.causa_id = c.id
            where a.evaluacion_id = $evaluacionId 
            and $condition 
            and a.anulado = false 
            order by b.codigo ASC";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $riesgosArray = $stmt->fetchAll();

            $arrayRiesgos = array();
            foreach ($riesgosArray as $ra) {
                $item = array();
                $item['CODIGO_RIESGO_FICHA_RIESGOS'] = $ra['codigo'];
                $item['RIESGO_FICHA_RIESGOS'] = $ra['riesgo'];
                $item['CAUSA_RIESGO_FICHA_RIESGOS'] = $ra['causa'];

                $riesgoCausaId = $ra['id'];
                //Buscamos las medidas preventivas para el puesto de trabajo
                $query = "select b.descripcion from accion_preventiva_trabajador_riesgo_causa a
                inner join preventiva_trabajador b on a.preventiva_trabajador_id = b.id
                where a.anulado = false
                and a.riesgo_causa_id = $riesgoCausaId";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $medidasPreventivasArray = $stmt->fetchAll();

                $medidasPreventivas = "";
                foreach ($medidasPreventivasArray as $mda) {
                    $medidasPreventivas .= " - " . $mda['descripcion'] . ".</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                }

                $item['MEDIDAS_RIESGO_FICHA_RIESGOS'] = $medidasPreventivas;
                array_push($arrayRiesgos, $item);
            }
            $templateProcessor->cloneRowAndSetValues('CODIGO_RIESGO_FICHA_RIESGOS', $arrayRiesgos);
        }

        if ($resumenRevisionSn) {
            if (!is_null($revision->getFecha())) {
                $fechaRevision = $revision->getFecha()->format('d') . ' / ' . $revision->getFecha()->format('m') . ' / ' . $revision->getFecha()->format('Y');
            }
            $templateProcessor->setValue("FECHA_REVISION", $fechaRevision);

            if (!is_null($revision->getFechaCertificacion())) {
                $fechaAptitud = $revision->getFechaCertificacion()->format('d') . ' / ' . $revision->getFechaCertificacion()->format('m') . ' / ' . $revision->getFechaCertificacion()->format('Y');
            }
            $templateProcessor->setValue("FECHA_APTITUD", $fechaAptitud);

            $revisionId = $revision->getId();

            //Buscamos el tipo de reconocimiento
            $query = "select respuesta from revision_respuesta where revision_id = $revisionId and pregunta_id = 280";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $tipoReconocimientoResult = $stmt->fetchAll();

            if (count($tipoReconocimientoResult) > 0) {
                $templateProcessor->setValue("TIPO_RECONOCIMIENTO", $tipoReconocimientoResult[0]['respuesta']);
            } else {
                $templateProcessor->setValue("TIPO_RECONOCIMIENTO", '');
            }
            //Buscamos los cuestionarios que debe rellanar el trabajador
            $empresaId = $revision->getEmpresa()->getId();
            $puestoTrabajoId = $revision->getPuestoTrabajo()->getId();

            $query = "select distinct f.id, f.codigo as cuestionario, f.orden from revision a
            inner join empresa b on a.empresa_id = b.id 
            inner join puesto_trabajo_centro c on a.puesto_trabajo_id = c.id
            inner join puesto_trabajo_protocolo d on c.id = d.puesto_trabajo_id 
            inner join protocolo_cuestionario e on d.protocolo_id = e.protocolo_id
            inner join cuestionario f on e.cuestionario_id = f.id
            where a.id = $revisionId
            and a.empresa_id = $empresaId
            and a.puesto_trabajo_id = $puestoTrabajoId
            and b.id = $empresaId
            and c.id = $puestoTrabajoId
            and d.puesto_trabajo_id = $puestoTrabajoId
            and d.empresa_id = $empresaId
            and a.anulado = false
            and c.anulado = false
            and d.anulado = false
            and e.anulado = false
            and f.anulado = false
            and f.tipo_cuestionario_id = 1
            order by f.orden asc";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $cuestionariosRellenar = $stmt->fetchAll();

            $templateProcessor->cloneBlock('block_cuestionarios', count($cuestionariosRellenar), true, true);

            $count = 1;
            $audiometriaOidoDArray = array();
            $audiometriaOidoIArray = array();
            $filename = "";
            $filenameEspirometria = "";
            $espirometriaArray = array();
            $espirometriaArray2 = array();
            foreach ($cuestionariosRellenar as $cr) {
                $templateProcessor->setValue("CUESTIONARIO#" . $count, $cr['cuestionario']);

                $cuestionarioId = $cr['id'];

                //Buscamos las preguntas del cuestionario
                $query = "select b.descripcion, b.descripcion_ca, b.id, a.orden, a.id as cuestionariopreguntaid from cuestionario_pregunta a
                    inner join pregunta b on a.pregunta_id = b.id
                    where a.anulado = false
                    and b.anulado = false
                    and a.cuestionario_id = $cuestionarioId
                    order by a.orden asc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $preguntas = $stmt->fetchAll();

                $arrayPreguntas = array();
                foreach ($preguntas as $p) {

                    $item = array();

                    if ($idiomaPlantilla == 'ESP') {
                        $descripcionPregunta = $p['descripcion'];
                    } else {
                        $descripcionPregunta = $p['descripcion_ca'];
                    }
                    $item['PREGUNTA#' . $count] = $descripcionPregunta;

                    $cuestionarioPreguntaId = $p['cuestionariopreguntaid'];

                    //Buscamos las posibles respuestas de la pregunta
                    $respuestas = "";
                    $preguntaId = $p['id'];
                    $pregunta = $em->getRepository('App\Entity\Pregunta')->find($preguntaId);

                    //Buscamos la respuesta del trabajador para cada pregunta
                    $query = "select a.id, a.respuesta, c.id as cuestionariopregunta from revision_respuesta a
                    inner join pregunta b on a.pregunta_id = b.id
                    inner join cuestionario_pregunta c on a.pregunta_id = c.pregunta_id
                    where a.revision_id = $revisionId
                    and a.cuestionario_id = $cuestionarioId
                    and c.cuestionario_id = $cuestionarioId
                    and a.pregunta_id = $preguntaId
                    order by c.orden asc";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $revisionRespuestaResult = $stmt->fetchAll();

                    $revisionRespuesta = null;
                    $revisionRespuestaId = null;
                    $respuestaForm = "";

                    if (count($revisionRespuestaResult) > 0) {
                        $revisionRespuestaId = $revisionRespuestaResult[0]['id'];
                        $respuestaForm = $revisionRespuestaResult[0]['respuesta'];
                        $revisionRespuesta = $em->getRepository('App\Entity\RevisionRespuesta')->find($revisionRespuestaId);
                    }
                    if (!is_null($pregunta->getTipoRespuesta())) {
                        //Buscamos el tipo de respuesta
                        switch ($pregunta->getTipoRespuesta()->getId()) {
                                //TIPO TEXTO - TIPO NUMERICO - TIPO NUMERICO + DECIMAL
                            case 0:
                            case 1:
                            case 2:
                            case 7:
                                $respuestas = $respuestaForm;

                                //Generamos el grafico de espirometria
                                if ($preguntaId == 363) {
                                    if ($respuestaForm != "" && !is_nan($respuestaForm)) {
                                        array_push($espirometriaArray, $respuestaForm);
                                    }
                                }
                                if ($preguntaId == 365) {
                                    if ($respuestaForm != "" && !is_nan($respuestaForm)) {
                                        array_push($espirometriaArray2, $respuestaForm);
                                    }
                                }
                                break;

                                //TIPO SI/NO
                            case 3:
                                if (strtolower($respuestaForm) === "si") {
                                    //$respuestas = "☑  Si";
                                    $respuestas = "Si";
                                } elseif (strtolower($respuestaForm) === "no") {
                                    //$respuestas .= "☑  No";
                                    $respuestas .= "No";
                                }
                                break;

                                //TIPO FECHA
                            case 4:
                                $respuestas = $respuestaForm;
                                break;

                                //TIPO SERIE CAMPO
                            case 5:
                                //Comprobamos que la serie no sea nula
                                if (!is_null($pregunta->getSerieRespuesta())) {
                                    $respuestasSerie = $em->getRepository('App\Entity\Respuesta')->findBy(array('serieRespuesta' => $pregunta->getSerieRespuesta(), 'anulado' => false), array('descripcion' => 'ASC'));

                                    //Comprobamos ei es una unica respuesta o es multiple
                                    if (!is_null($pregunta->getSerieRespuesta()->getIndicador())) {
                                        $indicadorId = $pregunta->getSerieRespuesta()->getIndicador()->getId();

                                        switch ($indicadorId) {
                                                //MULTIRESPUESTA
                                            case 0:
                                                foreach ($respuestasSerie as $rs) {
                                                    $checked = false;
                                                    $respuestaSerieDescripcion = $rs->getDescripcion();

                                                    if (str_contains($respuestaForm, ';;')) {
                                                        $arrayExplode = explode(';;', $respuestaForm);
                                                    } else {
                                                        $arrayExplode = explode(';', $respuestaForm);
                                                    }
                                                    foreach ($arrayExplode as $ae) {
                                                        //Comprobamos la que haya marcado
                                                        if (strtolower($respuestaSerieDescripcion) === strtolower(str_replace(';', '', $ae))) {
                                                            $checked = true;
                                                            break;
                                                        }
                                                    }
                                                    if ($checked) {
                                                        if ($idiomaPlantilla == 'ESP') {
                                                            $respuestaSerieDescripcion = $rs->getDescripcion();
                                                        } else {
                                                            $respuestaSerieDescripcion = $rs->getDescripcionCa();
                                                        }
                                                        $respuestas .= "$respuestaSerieDescripcion";
                                                        $respuestas .= "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                                                    }
                                                }
                                                break;

                                                //UNICA RESPUESTA
                                            case 1:
                                                foreach ($respuestasSerie as $rs) {
                                                    $respuestaSerieDescripcion = $rs->getDescripcion();

                                                    //Comprobamos la que haya marcado
                                                    if (strtolower($respuestaSerieDescripcion) === strtolower($respuestaForm)) {

                                                        if ($idiomaPlantilla == 'ESP') {
                                                            $respuestaSerieDescripcion = $rs->getDescripcion();
                                                        } else {
                                                            $respuestaSerieDescripcion = $rs->getDescripcionCa();
                                                        }
                                                        $respuestas .= "$respuestaSerieDescripcion";
                                                    }
                                                }
                                                break;
                                        }
                                    }
                                }
                                break;

                                //TIPO SUB PREGUNTA
                            case 6:
                                $query = "select * from sub_pregunta where pregunta_id = $preguntaId and anulado = false order by orden asc";
                                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                                $stmt->execute();
                                $subPregunta = $stmt->fetchAll();
                                $countSubPreguntas = count($subPregunta);
                                if ($countSubPreguntas > 0) {
                                    for ($i = 1; $i <= $countSubPreguntas; $i++) {
                                        $orden = $subPregunta[$i - 1]['orden'];
                                        if ($idiomaPlantilla == 'ESP') {
                                            $ordenDescripcion = $subPregunta[$i - 1]['descripcion'];
                                        } else {
                                            $ordenDescripcion = $subPregunta[$i - 1]['descripcion_ca'];
                                        }
                                        $revisionSubRespuesta = null;
                                        if (!is_null($revisionRespuesta)) {
                                            $query = "select respuesta, orden from revision_sub_respuesta where revision_respuesta_id = $revisionRespuestaId and orden = '$orden' and cuestionario_pregunta_id = $cuestionarioPreguntaId order by id asc";
                                            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                                            $stmt->execute();
                                            $revisionSubRespuesta = $stmt->fetchAll();
                                        }
                                        $value = "";
                                        if (!is_null($revisionSubRespuesta)) {
                                            if (isset($revisionSubRespuesta[0]['respuesta'])) {
                                                $value = $revisionSubRespuesta[0]['respuesta'];
                                            }
                                        }
                                        if ($preguntaId == 86) {
                                            if ($value == "") {
                                                array_push($audiometriaOidoDArray, 0);
                                            } else {
                                                array_push($audiometriaOidoDArray, intval($value));
                                            }
                                        }
                                        if ($preguntaId == 367) {
                                            if ($value == "") {
                                                array_push($audiometriaOidoIArray, 0);
                                            } else {
                                                array_push($audiometriaOidoIArray, intval($value));
                                            }
                                        }
                                        $respuestas .= "$ordenDescripcion: " . $value;
                                        $respuestas .= "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                                    }
                                }
                                break;
                                //TIPO FORMULA
                                /*case 7:
                                if(is_null($pregunta->getFormula())){
                                    $formulaVariable = $em->getRepository('App\Entity\FormulaVariable')->findBy(array('formula' => $pregunta->getFormula(), 'anulado' => false), array('descripcion' => 'ASC'));
                                    foreach ($formulaVariable as $fv){
                                        $formulaVariableDescripcion = $fv->getDescripcion();
                                        $respuestaInput .= "<input type='text' name='$revisionRespuestaId' id='$preguntaId' placeholder='$formulaVariableDescripcion' class='form-control' /><br/>";
                                    }
                                }
                                break;*/
                        }
                    }
                    $item['RESPUESTAS#' . $count] = $respuestas;

                    array_push($arrayPreguntas, $item);
                }
                $templateProcessor->cloneRowAndSetValues('PREGUNTA#' . $count, $arrayPreguntas);

                //Si han informado la pregunta de audiometria creamos el grafico
                if (count($audiometriaOidoDArray) > 0 && count($audiometriaOidoIArray) > 0) {
                    if ($filename == "") {
                        $filename = $this->crearGraficoAudiometria($revisionId, $audiometriaOidoDArray, $audiometriaOidoIArray);
                        if (file_exists($filename)) {
                            $templateProcessor->setImageValue('GRAFICO_AUDIOMETRIA#' . $count, array('path' => $filename, 'width' => 600, 'height' => 300, 'ratio' => false));
                        }
                    } else {
                        $templateProcessor->setValue('GRAFICO_AUDIOMETRIA#' . $count, "");
                    }
                } else {
                    $templateProcessor->setValue('GRAFICO_AUDIOMETRIA#' . $count, "");
                }

                //Si han informado la pregunta de audiometria creamos el grafico
                if (count($espirometriaArray) > 0 && count($espirometriaArray2) > 0) {
                    if ($filenameEspirometria == "") {
                        $filenameEspirometria = $this->crearGraficoEspirometria($revisionId, $espirometriaArray, $espirometriaArray2);
                        if (file_exists($filenameEspirometria)) {
                            $templateProcessor->setImageValue('GRAFICO_ESPIROMETRIA#' . $count, array('path' => $filenameEspirometria, 'width' => 600, 'height' => 300, 'ratio' => false));
                        }
                    } else {
                        $templateProcessor->setValue('GRAFICO_ESPIROMETRIA#' . $count, "");
                    }
                } else {
                    $templateProcessor->setValue('GRAFICO_ESPIROMETRIA#' . $count, "");
                }
                $count++;
            }
            //Buscamos el medico de la revision y su firma
            $medicoRevision = "";
            $firmaMedico = "";
            if (!is_null($revision->getMedico())) {
                $medicoRevision = $revision->getMedico()->getDescripcion();
                $firmaMedico = $revision->getMedico()->getFirma();
            }
            $templateProcessor->setValue('DOCTOR_REVISION', $medicoRevision);

            if ($firmaMedico != "") {
                $templateProcessor->setImageValue('FIRMA_DOCTOR_REVISION', 'upload/media/firmas/medico/' . $firmaMedico);
            } else {
                $templateProcessor->setValue('FIRMA_DOCTOR_REVISION', null);
            }
            //Consejos medicos
            $query = "select distinct b.id as pregunta, replace(a.respuesta, ';', '') as respuesta, b.serie_respuesta_id from revision_respuesta a
            inner join pregunta b on a.pregunta_id = b.id
            inner join cuestionario_pregunta c on a.pregunta_id = c.pregunta_id
            inner join respuesta e on b.serie_respuesta_id = e.serie_respuesta_id 
            where a.revision_id = $revisionId
            and b.serie_respuesta_id is not null
            and e.consejo_medico_id is not null
            order by b.id asc";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $respuestasPreguntas = $stmt->fetchAll();

            $serieRespuestaRepo = $this->getDoctrine()->getRepository('App\Entity\SerieRespuesta');
            $respuestaRepo = $this->getDoctrine()->getRepository('App\Entity\Respuesta');
            $arrayConsejosMedicos = array();
            foreach ($respuestasPreguntas as $rp) {

                $respuestaText = $rp['respuesta'];

                //Buscamos en la serie de respuestas si la respuesta que ha introducido tiene un consejo medico
                $serieRespuesta = $serieRespuestaRepo->find($rp['serie_respuesta_id']);
                $respuesta = $respuestaRepo->findBy(array('serieRespuesta' => $serieRespuesta));

                foreach ($respuesta as $r) {
                    if (strtolower($r->getDescripcion()) === strtolower($respuestaText)) {
                        if (!is_null($r->getConsejoMedico())) {
                            $consejoMedicoDescripcion = ($idiomaPlantilla == 'ESP')
                                ? $r->getConsejoMedico()->getDescripcion()
                                : $r->getConsejoMedico()->getDescripcionCa();
                            array_push($arrayConsejosMedicos, $consejoMedicoDescripcion);
                        }
                    }
                }
            }
            $consejos = array_unique($arrayConsejosMedicos);

            $consejosMedicos = "";
            foreach ($consejos as $c) {
                $consejosMedicos .= $c . ".</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
            }
            $templateProcessor->setValue("RESUMEN_REVISION_CONSEJOS_MEDICOS", $consejosMedicos);

            $templateProcessor->setValue("RECOMENDACIONES_REVISION", $revision->getRecomendaciones());

            $electrocardiograma = $revision->getElectrocardiograma();
            if ($electrocardiograma != "") {
                $templateProcessor->setImageValue('ELECTROCARDIOGRAMA_REVISION', array('path' => 'upload/media/electrocardiograma/' . $electrocardiograma, 'width' => 620, 'height' => 876, 'ratio' => false));
            } else {
                $templateProcessor->setValue('ELECTROCARDIOGRAMA_REVISION', null);
            }
        }
        if ($revisionMedicaSn) {
            $revisionId = $revision->getId();

            if (!is_null($revision->getFecha())) {
                $fechaRevision = $revision->getFecha()->format('d') . ' / ' . $revision->getFecha()->format('m') . ' / ' . $revision->getFecha()->format('Y');
            }
            $templateProcessor->setValue("FECHA_REVISION", $fechaRevision);

            //Buscamos los cuestionarios que debe rellanar el trabajador
            $empresaId = $revision->getEmpresa()->getId();
            $puestoTrabajoId = $revision->getPuestoTrabajo()->getId();

            $query = "select distinct f.id, f.codigo as cuestionario, f.orden from revision a
            inner join empresa b on a.empresa_id = b.id 
            inner join puesto_trabajo_centro c on a.puesto_trabajo_id = c.id
            inner join puesto_trabajo_protocolo d on c.id = d.puesto_trabajo_id 
            inner join protocolo_cuestionario e on d.protocolo_id = e.protocolo_id
            inner join cuestionario f on e.cuestionario_id = f.id
            where a.id = $revisionId
            and a.empresa_id = $empresaId
            and a.puesto_trabajo_id = $puestoTrabajoId
            and b.id = $empresaId
            and c.id = $puestoTrabajoId
            and d.puesto_trabajo_id = $puestoTrabajoId
            and d.empresa_id = $empresaId
            and a.anulado = false
            and c.anulado = false
            and d.anulado = false
            and e.anulado = false
            and f.anulado = false
            and f.tipo_cuestionario_id = 1
            order by f.orden asc";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $cuestionariosRellenar = $stmt->fetchAll();

            $templateProcessor->cloneBlock('block_cuestionarios', count($cuestionariosRellenar), true, true);

            $count = 1;
            foreach ($cuestionariosRellenar as $cr) {
                $templateProcessor->setValue("CUESTIONARIO#" . $count, $cr['cuestionario']);

                $cuestionarioId = $cr['id'];
                //Buscamos las preguntas del cuestionario
                $query = "select b.descripcion, b.descripcion_ca, b.id, a.orden from cuestionario_pregunta a
                    inner join pregunta b on a.pregunta_id = b.id
                    where a.anulado = false
                    and b.anulado = false
                    and a.cuestionario_id = $cuestionarioId
                    order by a.orden asc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $preguntas = $stmt->fetchAll();

                $arrayPreguntas = array();
                foreach ($preguntas as $p) {
                    $item = array();
                    $descripcionPregunta = ($idiomaPlantilla == 'ESP') ? $p['descripcion'] : $p['descripcion_ca'];
                    $item['PREGUNTA#' . $count] = $descripcionPregunta;

                    //Buscamos las posibles respuestas de la pregunta
                    $respuestas = "";
                    $preguntaId = $p['id'];

                    $pregunta = $em->getRepository('App\Entity\Pregunta')->find($preguntaId);
                    if (!is_null($pregunta->getTipoRespuesta())) {
                        //Buscamos el tipo de respuesta
                        switch ($pregunta->getTipoRespuesta()->getId()) {
                                //TIPO TEXTO - TIPO NUMERICO - TIPO NUMERICO + DECIMAL
                            case 0:
                            case 1:
                            case 2:
                            case 7:
                                $respuestas = "";
                                break;
                                //TIPO SI/NO
                            case 3:
                                $respuestas = "☐  Si";
                                $respuestas .= "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                                $respuestas .= "☐  No";
                                break;
                                //TIPO FECHA
                            case 4:
                                $respuestas = "__/__/____";
                                break;
                                //TIPO SERIE CAMPO
                            case 5:
                                //Comprobamos que la serie no sea nula
                                if (!is_null($pregunta->getSerieRespuesta())) {
                                    $respuestasSerie = $em->getRepository('App\Entity\Respuesta')->findBy(array('serieRespuesta' => $pregunta->getSerieRespuesta(), 'anulado' => false), array('descripcion' => 'ASC'));

                                    //Comprobamos ei es una unica respuesta o es multiple
                                    if (!is_null($pregunta->getSerieRespuesta()->getIndicador())) {
                                        $indicadorId = $pregunta->getSerieRespuesta()->getIndicador()->getId();

                                        switch ($indicadorId) {
                                                //MULTIRESPUESTA
                                            case 0:
                                                $respuestas = $idiomaPlantilla == 'ESP' ? "Si, indique cuales:" : "Si, indiqui quins:";
                                                $respuestas .= "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";

                                                foreach ($respuestasSerie as $rs) {
                                                    $respuestaSerieDescripcion = $idiomaPlantilla == 'ESP' ? $rs->getDescripcion() : $rs->getDescripcionCa();
                                                    $respuestas .= "☐  $respuestaSerieDescripcion";
                                                    $respuestas .= "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                                                }
                                                break;
                                                //UNICA RESPUESTA
                                            case 1:
                                                if ($idiomaPlantilla == 'ESP') {
                                                    $respuestas = "Si, indique quin:";
                                                } else {
                                                    $respuestas = "Si, indiqui quin:";
                                                }
                                                $respuestas .= "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                                                foreach ($respuestasSerie as $rs) {
                                                    $respuestaSerieDescripcion = $idiomaPlantilla == 'ESP' ? $rs->getDescripcion() : $rs->getDescripcionCa();
                                                    $respuestas .= "☐  $respuestaSerieDescripcion";
                                                    $respuestas .= "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                                                }
                                                break;
                                        }
                                    }
                                }
                                break;
                                //TIPO SUB PREGUNTA
                            case 6:
                                $subPregunta = $em->getRepository('App\Entity\SubPregunta')->findBy(array('pregunta' => $pregunta, 'anulado' => false), array('orden' => 'ASC'));
                                foreach ($subPregunta as $sp) {
                                    $ordenDescripcion = ($idiomaPlantilla == 'ESP') ? $sp->getDescripcion() : $sp->getDescripcionCa();
                                    $respuestas .= "$ordenDescripcion:";
                                    $respuestas .= "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                                }
                                break;
                                //TIPO FORMULA
                                /*case 7:
                                if(is_null($pregunta->getFormula())){
                                    $formulaVariable = $em->getRepository('App\Entity\FormulaVariable')->findBy(array('formula' => $pregunta->getFormula(), 'anulado' => false), array('descripcion' => 'ASC'));
                                    foreach ($formulaVariable as $fv){
                                        $formulaVariableDescripcion = $fv->getDescripcion();
                                        $respuestaInput .= "<input type='text' name='$revisionRespuestaId' id='$preguntaId' placeholder='$formulaVariableDescripcion' class='form-control' /><br/>";
                                    }
                                }
                                break;*/
                        }
                    }
                    $item['RESPUESTAS#' . $count] = $respuestas;

                    array_push($arrayPreguntas, $item);
                }
                $templateProcessor->cloneRowAndSetValues('PREGUNTA#' . $count, $arrayPreguntas);

                $count++;
            }
        }

        if ($memoriaSn) {
            $templateProcessor->setValue('ANYO_MEMORIA', $anyoMemoriaEstudio);
            $templateProcessor->setValue('FECHA_IMPRESION_MEMORIA', $hoyString);
            $templateProcessor->setValue('PERIODO_EXAMENES_SALUD', '01-01-' . $anyoMemoriaEstudio . ' - 31-12-' . $anyoMemoriaEstudio);

            $fechaImpresionMemoria = $hoy->format('d') . ' de ' . $this->obtenerMes($hoy->format('m')) . ' de ' . $anyoMemoriaEstudio;
            $templateProcessor->setValue('FECHA_IMPRESION_MEMORIA_2', $fechaImpresionMemoria);

            //Calculamos los reconocimientos en el periodo
            $query = "select count(*) as contador from revision where empresa_id = $empresaId and fecha between '$anyoMemoriaEstudio-01-01 00:00:00' and '$anyoMemoriaEstudio-12-31 23:59:59' and anulado = false";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultRevisionesPeriodo = $stmt->fetchAll();

            $templateProcessor->setValue('EXAMENES_SALUD', $resultRevisionesPeriodo[0]['contador']);
            $templateProcessor->setValue('RECONOCIMIENTOS_MEDICOS', $resultRevisionesPeriodo[0]['contador']);

            $firmaMedico = "";
            $colegiadoMedico = "";
            $especialidadMedico = "";
            $gestoraMedico = "";
            if (!is_null($empresa->getVigilanciaSalud())) {
                if (!is_null($empresa->getVigilanciaSalud()->getMedico())) {
                    $doctorRevision = $empresa->getVigilanciaSalud()->getMedico()->getDescripcion();
                    $firmaMedico = $empresa->getVigilanciaSalud()->getMedico()->getFirma();
                    $colegiadoMedico = $empresa->getVigilanciaSalud()->getMedico()->getNumeroColegiado();
                    $especialidadMedico = $empresa->getVigilanciaSalud()->getMedico()->getEspecialidad();
                    $gestoraMedico = $empresa->getVigilanciaSalud()->getMedico()->getGestora();
                }
            }
            if ($firmaMedico != "") {
                $templateProcessor->setImageValue('MEDICO_FIRMA', 'upload/media/firmas/medico/' . $firmaMedico);
            } else {
                $templateProcessor->setValue('MEDICO_FIRMA', null);
            }
            $templateProcessor->setValue("MEDICO_NOMBRE", $doctorRevision);
            $templateProcessor->setValue('MEDICO_COLEGIADO', $colegiadoMedico);
            $templateProcessor->setValue('MEDICO_ESPECIALIDAD', $especialidadMedico);
            $templateProcessor->setValue('MEDICO_GESTORA', $gestoraMedico);
        }
        if ($estudioSn) {
            $templateProcessor->setValue('ANYO_ESTUDIO', $anyoMemoriaEstudio);
            $templateProcessor->setValue('ANYO_ESTUDIO_2', $anyoMemoriaEstudio - 1);
            $templateProcessor->setValue('ANYO_ESTUDIO_3', $anyoMemoriaEstudio - 2);

            $templateProcessor->setValue('PERIODO_EXAMENES_SALUD', '01-01-' . $anyoMemoriaEstudio . ' - 31-12-' . $anyoMemoriaEstudio);

            $fechaImpresionMemoria = $hoy->format('d') . ' de ' . $this->obtenerMes($hoy->format('m')) . ' de ' . $anyoMemoriaEstudio;
            $templateProcessor->setValue('FECHA_IMPRESION_ESTUDIO_2', $fechaImpresionMemoria);

            //Calculamos los reconocimientos en el periodo
            $templateProcessor->setValue('EXAMENES_SALUD', $this->calcularReconocimientos($empresaId, $anyoMemoriaEstudio));
            $templateProcessor->setValue('RECONOCIMIENTOS_MEDICOS', $this->calcularReconocimientos($empresaId, $anyoMemoriaEstudio));

            //Calculamos los reconocimientos de 3 años atras
            $templateProcessor->setValue('RA_1_V', $this->calcularReconocimientos($empresaId, $anyoMemoriaEstudio));
            $templateProcessor->setValue('RA_2_V', $this->calcularReconocimientos($empresaId, $anyoMemoriaEstudio - 1));
            $templateProcessor->setValue('RA_3_V', $this->calcularReconocimientos($empresaId, $anyoMemoriaEstudio - 2));

            //Calculamos el numero de aptitudes por tipos
            $templateProcessor->setValue('TIPO_APT_I_1', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio, 'Inicial'));
            $templateProcessor->setValue('TIPO_APT_I_2', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio - 1, 'Inicial'));
            $templateProcessor->setValue('TIPO_APT_I_3', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio - 2, 'Inicial'));

            $templateProcessor->setValue('TIPO_APT_P_1', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio, 'Periodico'));
            $templateProcessor->setValue('TIPO_APT_P_2', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio - 1, 'Periodico'));
            $templateProcessor->setValue('TIPO_APT_P_3', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio - 2, 'Periodico'));

            $templateProcessor->setValue('TIPO_APT_TA_1', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio, 'Tras ausencia prolongada'));
            $templateProcessor->setValue('TIPO_APT_TA_2', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio - 1, 'Tras ausencia prolongada'));
            $templateProcessor->setValue('TIPO_APT_TA_3', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio - 2, 'Tras ausencia prolongada'));

            //Calculamos el recuento por aptitud
            $templateProcessor->setValue('APTO_1_1', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio, 1));
            $templateProcessor->setValue('APTO_1_2', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio - 1, 1));
            $templateProcessor->setValue('APTO_1_3', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio - 2, 1));

            $templateProcessor->setValue('APTO_2_1', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio, 2));
            $templateProcessor->setValue('APTO_2_2', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio - 1, 2));
            $templateProcessor->setValue('APTO_2_3', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio - 2, 2));

            $templateProcessor->setValue('APTO_3_1', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio, 3));
            $templateProcessor->setValue('APTO_3_2', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio - 1, 3));
            $templateProcessor->setValue('APTO_3_3', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio - 2, 3));

            //Calculamos el recuento por tipo de restriccion
            $templateProcessor->setValue('AR_1_1', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio, 1));
            $templateProcessor->setValue('AR_1_2', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 1, 1));
            $templateProcessor->setValue('AR_1_3', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 2, 1));

            $templateProcessor->setValue('AR_2_1', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio, 2));
            $templateProcessor->setValue('AR_2_2', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 1, 2));
            $templateProcessor->setValue('AR_2_3', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 2, 2));

            $templateProcessor->setValue('AR_3_1', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio, 3));
            $templateProcessor->setValue('AR_3_2', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 1, 3));
            $templateProcessor->setValue('AR_3_3', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 2, 3));

            $templateProcessor->setValue('AR_4_1', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio, 4));
            $templateProcessor->setValue('AR_4_2', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 1, 4));
            $templateProcessor->setValue('AR_4_3', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 2, 4));

            $templateProcessor->setValue('AR_5_1', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio, 5));
            $templateProcessor->setValue('AR_5_2', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 1, 5));
            $templateProcessor->setValue('AR_5_3', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 2, 5));

            $templateProcessor->setValue('AR_6_1', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio, 6));
            $templateProcessor->setValue('AR_6_2', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 1, 6));
            $templateProcessor->setValue('AR_6_3', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 2, 6));

            //Calculamos las restricciones por puesto de trabajo
            $yearMenos2 = $anyoMemoriaEstudio - 2;
            $query = "select distinct b.descripcion, b.id from revision a 
            inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id 
            where a.empresa_id = $empresaId 
            and fecha between '$yearMenos2-01-01 00:00:00' and '$anyoMemoriaEstudio-12-31 23:59:59'
            and a.anulado = false
            and a.aptitud_restriccion_id is not null
            order by b.descripcion asc";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultRestriccionPorPuestoTrabajo = $stmt->fetchAll();
            $arrayRestriccionPorPuestoTrabajo = array();
            foreach ($resultRestriccionPorPuestoTrabajo as $r) {
                $item = array();
                $item['AR_PT_DESC'] = $r['descripcion'];
                $item['AR_PT_1'] = $this->calcularRestriccionesPorPuestoTrabajo($empresaId, $anyoMemoriaEstudio, $r['id']);
                $item['AR_PT_2'] = $this->calcularRestriccionesPorPuestoTrabajo($empresaId, $anyoMemoriaEstudio - 1, $r['id']);
                $item['AR_PT_3'] = $this->calcularRestriccionesPorPuestoTrabajo($empresaId, $anyoMemoriaEstudio - 2, $r['id']);
                array_push($arrayRestriccionPorPuestoTrabajo, $item);
            }
            if (count($arrayRestriccionPorPuestoTrabajo) == 0) {
                $item = array();
                $item['AR_PT_DESC'] = '';
                $item['AR_PT_1'] = '';
                $item['AR_PT_2'] = '';
                $item['AR_PT_3'] = '';
                array_push($arrayRestriccionPorPuestoTrabajo, $item);
            }
            $templateProcessor->cloneRowAndSetValues('AR_PT_DESC', $arrayRestriccionPorPuestoTrabajo);

            //Calculamos los reconocimientos por puesto de trabajo
            $query = "select distinct b.descripcion, b.id from revision a 
            inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id 
            where a.empresa_id = $empresaId 
            and fecha between '$anyoMemoriaEstudio-01-01 00:00:00' and '$anyoMemoriaEstudio-12-31 23:59:59'
            and a.anulado = false
            order by b.descripcion asc";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultReconocimientoPorPuestoTrabajo = $stmt->fetchAll();
            $arrayReconocimientoPorPuestoTrabajo = array();
            foreach ($resultReconocimientoPorPuestoTrabajo as $r) {
                $item = array();
                $item['PUESTO_TRABAJO_ESTUDIO'] = $r['descripcion'];
                $item['PUESTO_TRABAJO_CANTIDAD'] = $this->calcularReconocimientosPorPuestoTrabajo($empresaId, $anyoMemoriaEstudio, $r['id']);
                array_push($arrayReconocimientoPorPuestoTrabajo, $item);
            }
            if (count($arrayReconocimientoPorPuestoTrabajo) == 0) {
                $item = array();
                $item['PUESTO_TRABAJO_ESTUDIO'] = "";
                $item['PUESTO_TRABAJO_CANTIDAD'] = "";
                array_push($arrayReconocimientoPorPuestoTrabajo, $item);
            }
            $templateProcessor->cloneRowAndSetValues('PUESTO_TRABAJO_ESTUDIO', $arrayReconocimientoPorPuestoTrabajo);

            //Calculamos protocolos aplicados
            $query = "select c.descripcion, count(*) as contador from revision a 
            inner join puesto_trabajo_protocolo b on a.puesto_trabajo_id = b.puesto_trabajo_id 
            inner join protocolo c on b.protocolo_id = c.id
            where a.empresa_id = $empresaId 
            and a.fecha between '$anyoMemoriaEstudio-01-01 00:00:00' and '$anyoMemoriaEstudio-12-31 23:59:59'
            and a.anulado = false
            and c.id NOT IN (32, 40)
            group by c.descripcion 
            order by c.descripcion asc";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultProtocolosAplicados = $stmt->fetchAll();
            $arrayProtocolosAplicados = array();
            $arrayProtocolosAplicadosLabels = array();
            $arrayProtocolosAplicadosValores = array();
            foreach ($resultProtocolosAplicados as $r) {
                $item = array();
                $item['PROTOCOLO_ESTUDIO'] = $r['descripcion'];
                $item['PROTOCOLO_CANTIDAD'] = $r['contador'];
                array_push($arrayProtocolosAplicados, $item);
                array_push($arrayProtocolosAplicadosLabels, $r['descripcion']);
                array_push($arrayProtocolosAplicadosValores, $r['contador']);
            }
            if (count($arrayProtocolosAplicados) == 0) {
                $item = array();
                $item['PROTOCOLO_ESTUDIO'] = "";
                $item['PROTOCOLO_CANTIDAD'] = "";
                array_push($arrayProtocolosAplicados, $item);
            }
            $templateProcessor->cloneRowAndSetValues('PROTOCOLO_ESTUDIO', $arrayProtocolosAplicados);

            if (count($arrayProtocolosAplicadosLabels) > 0 && count($arrayProtocolosAplicadosValores) > 0) {
                $filenameProtocolos = $this->crearGraficoProtocolosEstudios($empresaId, $arrayProtocolosAplicadosLabels, $arrayProtocolosAplicadosValores);
                $templateProcessor->setImageValue('GRAFICO_PROTOCOLOS', array('path' => $filenameProtocolos, 'width' => 600, 'height' => 700, 'ratio' => false));
            } else {
                $templateProcessor->setValue('GRAFICO_PROTOCOLOS', "");
            }
            //Calculamos los trabajadores que han pasado reconocimiento en el periodo
            $templateProcessor->setValue('TRABAJADORES_RECONOCIMIENTO_MEDICO', $this->calcularReconocimientosPorTrabajador($empresaId, $anyoMemoriaEstudio));

            //Calculamos los reconocimientos por sexo y edad
            $templateProcessor->setValue('EDAD_H_1', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 1, '< 20'));
            $templateProcessor->setValue('EDAD_H_2', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 1, 'BETWEEN 21 and 30'));
            $templateProcessor->setValue('EDAD_H_3', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 1, 'BETWEEN 31 and 40'));
            $templateProcessor->setValue('EDAD_H_4', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 1, 'BETWEEN 41 and 50'));
            $templateProcessor->setValue('EDAD_H_5', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 1, 'BETWEEN 51 and 60'));
            $templateProcessor->setValue('EDAD_H_6', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 1, '> 60'));
            $templateProcessor->setValue('EDAD_H_T', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 1, null));

            $templateProcessor->setValue('EDAD_M_1', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 2, '< 20'));
            $templateProcessor->setValue('EDAD_M_2', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 2, 'BETWEEN 21 and 30'));
            $templateProcessor->setValue('EDAD_M_3', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 2, 'BETWEEN 31 and 40'));
            $templateProcessor->setValue('EDAD_M_4', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 2, 'BETWEEN 41 and 50'));
            $templateProcessor->setValue('EDAD_M_5', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 2, 'BETWEEN 51 and 60'));
            $templateProcessor->setValue('EDAD_M_6', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 2, '> 60'));
            $templateProcessor->setValue('EDAD_M_T', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 2, null));

            //Diagnostico IMC
            $templateProcessor->setValue('IMC_1', $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio, 1));
            $templateProcessor->setValue('IMC_2', $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio, 2));
            $templateProcessor->setValue('IMC_3', $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio, 3));
            $templateProcessor->setValue('IMC_4', $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio, 4));

            $cantidadTotalImc = $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio, 1) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio, 2) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio, 3) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio, 4);
            $cantidadTotalImc1 = $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio - 1, 1) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio - 1, 2) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio - 1, 3) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio - 1, 4);
            $cantidadTotalImc2 = $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio - 2, 1) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio - 2, 2) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio - 2, 3) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio - 2, 4);

            $templateProcessor->setValue('IMC_1_2', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio, 1, $cantidadTotalImc));
            $templateProcessor->setValue('IMC_1_3', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio - 1, 1, $cantidadTotalImc1));
            $templateProcessor->setValue('IMC_1_4', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio - 2, 1, $cantidadTotalImc2));

            $templateProcessor->setValue('IMC_2_2', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio, 2, $cantidadTotalImc));
            $templateProcessor->setValue('IMC_2_3', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio - 1, 2, $cantidadTotalImc1));
            $templateProcessor->setValue('IMC_2_4', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio - 2, 2, $cantidadTotalImc2));

            $templateProcessor->setValue('IMC_3_2', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio, 3, $cantidadTotalImc));
            $templateProcessor->setValue('IMC_3_3', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio - 1, 3, $cantidadTotalImc1));
            $templateProcessor->setValue('IMC_3_4', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio - 2, 3, $cantidadTotalImc2));

            $templateProcessor->setValue('IMC_4_2', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio, 4, $cantidadTotalImc));
            $templateProcessor->setValue('IMC_4_3', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio - 1, 4, $cantidadTotalImc1));
            $templateProcessor->setValue('IMC_4_4', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio - 2, 4, $cantidadTotalImc2));

            //Hábito tabáquico
            $templateProcessor->setValue('TABACO_1', $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 1));
            $templateProcessor->setValue('TABACO_2', $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 2));
            $templateProcessor->setValue('TABACO_3', $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 3));
            $templateProcessor->setValue('TABACO_4', $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 4));
            $templateProcessor->setValue('TABACO_5', $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 5));
            $templateProcessor->setValue('TABACO_6', $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 6));

            $cantidadTotalTabaco = $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 1) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 2) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 3) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 4) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 5);
            $cantidadTotalTabaco1 = $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 1) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 2) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 3) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 4) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 5);
            $cantidadTotalTabaco2 = $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 1) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 2) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 3) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 4) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 5);

            $templateProcessor->setValue('TABACO_1_2', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 1, $cantidadTotalTabaco));
            $templateProcessor->setValue('TABACO_1_3', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 1, $cantidadTotalTabaco1));
            $templateProcessor->setValue('TABACO_1_4', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 1, $cantidadTotalTabaco2));

            $templateProcessor->setValue('TABACO_2_2', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 2, $cantidadTotalTabaco));
            $templateProcessor->setValue('TABACO_2_3', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 2, $cantidadTotalTabaco1));
            $templateProcessor->setValue('TABACO_2_4', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 2, $cantidadTotalTabaco2));

            $templateProcessor->setValue('TABACO_3_2', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 3, $cantidadTotalTabaco));
            $templateProcessor->setValue('TABACO_3_3', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 3, $cantidadTotalTabaco1));
            $templateProcessor->setValue('TABACO_3_4', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 3, $cantidadTotalTabaco2));

            $templateProcessor->setValue('TABACO_4_2', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 4, $cantidadTotalTabaco));
            $templateProcessor->setValue('TABACO_4_3', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 4, $cantidadTotalTabaco1));
            $templateProcessor->setValue('TABACO_4_4', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 4, $cantidadTotalTabaco2));

            $templateProcessor->setValue('TABACO_5_2', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 5, $cantidadTotalTabaco));
            $templateProcessor->setValue('TABACO_5_3', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 5, $cantidadTotalTabaco1));
            $templateProcessor->setValue('TABACO_5_4', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 5, $cantidadTotalTabaco2));

            $templateProcessor->setValue('TABACO_6_2', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 6, $cantidadTotalTabaco));
            $templateProcessor->setValue('TABACO_6_3', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 6, $cantidadTotalTabaco1));
            $templateProcessor->setValue('TABACO_6_4', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 6, $cantidadTotalTabaco2));

            //Hipertension arterial
            $templateProcessor->setValue('HPA_1', $this->calcularHipertensionArterialTrabajador($empresaId, $anyoMemoriaEstudio, true));

            $templateProcessor->setValue('HPA_1_2', $this->calcularPorcentajeHipertensionArterialTrabajador($empresaId, $anyoMemoriaEstudio, $this->calcularHipertensionArterialTrabajador($empresaId, $anyoMemoriaEstudio, false)));
            $templateProcessor->setValue('HPA_1_3', $this->calcularPorcentajeHipertensionArterialTrabajador($empresaId, $anyoMemoriaEstudio - 1, $this->calcularHipertensionArterialTrabajador($empresaId, $anyoMemoriaEstudio - 1, false)));
            $templateProcessor->setValue('HPA_1_4', $this->calcularPorcentajeHipertensionArterialTrabajador($empresaId, $anyoMemoriaEstudio - 2, $this->calcularHipertensionArterialTrabajador($empresaId, $anyoMemoriaEstudio - 2, false)));

            //Hipercolesterolemia
            $templateProcessor->setValue('HC_1', $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio, true, 1));
            $templateProcessor->setValue('HC_1_2', $this->calcularPorcentajeHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio, $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio, false, 1), 1));
            $templateProcessor->setValue('HC_1_3', $this->calcularPorcentajeHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio - 1, $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio - 1, false, 1), 1));
            $templateProcessor->setValue('HC_1_4', $this->calcularPorcentajeHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio - 2, $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio - 2, false, 1), 1));

            //Diabetes
            $templateProcessor->setValue('DIABETES_1', $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio, true, 2));
            $templateProcessor->setValue('DIABETES_1_2', $this->calcularPorcentajeHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio, $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio, false, 2), 2));
            $templateProcessor->setValue('DIABETES_1_3', $this->calcularPorcentajeHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio - 1, $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio - 1, false, 2), 2));
            $templateProcessor->setValue('DIABETES_1_4', $this->calcularPorcentajeHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio - 2, $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio - 2, false, 2), 2));

            //Sedentarismo
            $templateProcessor->setValue('SEDENTARISMO_1', $this->calcularSedentarismoTrabajador($empresaId, $anyoMemoriaEstudio, true));

            $templateProcessor->setValue('SEDENTARISMO_1_2', $this->calcularPorcentajeSedentarismoTrabajador($empresaId, $anyoMemoriaEstudio, $this->calcularSedentarismoTrabajador($empresaId, $anyoMemoriaEstudio, false)));
            $templateProcessor->setValue('SEDENTARISMO_1_3', $this->calcularPorcentajeSedentarismoTrabajador($empresaId, $anyoMemoriaEstudio - 1, $this->calcularSedentarismoTrabajador($empresaId, $anyoMemoriaEstudio - 1, false)));
            $templateProcessor->setValue('SEDENTARISMO_1_4', $this->calcularPorcentajeSedentarismoTrabajador($empresaId, $anyoMemoriaEstudio - 2, $this->calcularSedentarismoTrabajador($empresaId, $anyoMemoriaEstudio - 2, false)));

            //Alteraciones audiometria
            $templateProcessor->setValue('AUDIOMETRIA_1', $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio, 1));
            $templateProcessor->setValue('AUDIOMETRIA_2', $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio, 2));
            $templateProcessor->setValue('AUDIOMETRIA_1_1', $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio, 3));
            $templateProcessor->setValue('AUDIOMETRIA_2_1', $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio, 4));

            $templateProcessor->setValue('AUDIOMETRIA_1_2', $this->calcularPorcentajeAlteracionAudiometriaRuido($this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio, 1), $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio, 3)));
            $templateProcessor->setValue('AUDIOMETRIA_2_2', $this->calcularPorcentajeAlteracionAudiometriaRuido($this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio, 2), $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio, 4)));
            $templateProcessor->setValue('AUDIOMETRIA_1_3', $this->calcularPorcentajeAlteracionAudiometriaRuido($this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio - 1, 1), $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio - 1, 3)));
            $templateProcessor->setValue('AUDIOMETRIA_2_3', $this->calcularPorcentajeAlteracionAudiometriaRuido($this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio - 1, 2), $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio - 1, 4)));
            $templateProcessor->setValue('AUDIOMETRIA_1_4', $this->calcularPorcentajeAlteracionAudiometriaRuido($this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio - 2, 1), $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio - 2, 3)));
            $templateProcessor->setValue('AUDIOMETRIA_2_4', $this->calcularPorcentajeAlteracionAudiometriaRuido($this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio - 2, 2), $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio - 2, 4)));

            //Alteraciones agudeza visual
            $templateProcessor->setValue('AV_1', $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, true, 1));
            $templateProcessor->setValue('AV_1_2', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, false, 1), 1));
            $templateProcessor->setValue('AV_1_3', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 1, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 1, false, 1), 1));
            $templateProcessor->setValue('AV_1_4', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 2, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 2, false, 1), 1));

            $templateProcessor->setValue('AV_2', $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, true, 2));
            $templateProcessor->setValue('AV_2_2', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, false, 2), 2));
            $templateProcessor->setValue('AV_2_3', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 1, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 1, false, 2), 2));
            $templateProcessor->setValue('AV_2_4', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 2, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 2, false, 2), 2));

            $templateProcessor->setValue('AV_3', $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, true, 3));
            $templateProcessor->setValue('AV_3_2', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, false, 3), 3));
            $templateProcessor->setValue('AV_3_3', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 1, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 1, false, 3), 3));
            $templateProcessor->setValue('AV_3_4', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 2, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 2, false, 3), 3));

            //Alteraciones osteomusculares
            $templateProcessor->setValue('AO_1', $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 1, null));
            $templateProcessor->setValue('AO_2', $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 2, null));
            $templateProcessor->setValue('AO_3', $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 3, null));
            $templateProcessor->setValue('AO_4', $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 4, null));
            $templateProcessor->setValue('AO_5', $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 5, null));
            $alteracionOsteomuscular = $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 1, null) + $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 2, null) + $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 3, null) + $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 4, null) + $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 5, null);
            $templateProcessor->setValue('AO_TOTAL', $alteracionOsteomuscular);

            //Generamos el grafico de las alteraciones osteomusculares
            $arrayAlteracionOsteomuscularsLabels = array();
            $arrayAlteracionOsteomuscularValores = array();

            array_push($arrayAlteracionOsteomuscularsLabels, 'Columna vertebral');
            array_push($arrayAlteracionOsteomuscularsLabels, 'Muñeca/Manos');
            array_push($arrayAlteracionOsteomuscularsLabels, 'Rodillas');
            array_push($arrayAlteracionOsteomuscularsLabels, 'Extremidades superiores');
            array_push($arrayAlteracionOsteomuscularsLabels, 'Extremidades inferiores');

            array_push($arrayAlteracionOsteomuscularValores, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 1, null));
            array_push($arrayAlteracionOsteomuscularValores, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 2, null));
            array_push($arrayAlteracionOsteomuscularValores, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 3, null));
            array_push($arrayAlteracionOsteomuscularValores, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 4, null));
            array_push($arrayAlteracionOsteomuscularValores, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 5, null));

            if (count($arrayAlteracionOsteomuscularsLabels) > 0 && count($arrayAlteracionOsteomuscularValores) > 0) {
                $filenameAlteracionOsteomuscular = $this->crearGraficoAlteracionOsteomuscular($empresaId, $arrayAlteracionOsteomuscularsLabels, $arrayAlteracionOsteomuscularValores);
                $templateProcessor->setImageValue('GRAFICO_ALTERACION_OSTEOMUSCULAR', array('path' => $filenameAlteracionOsteomuscular, 'width' => 600, 'height' => 300, 'ratio' => false));
            } else {
                $templateProcessor->setValue('GRAFICO_ALTERACION_OSTEOMUSCULAR', "");
            }
            //Buscamos las alteraciones osteomusculares por cada puesto de trabajo
            $query = "select distinct b.id, b.descripcion from revision a 
            inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id 
            where a.empresa_id = $empresaId 
            and a.fecha between '$anyoMemoriaEstudio-01-01 00:00:00' and '$anyoMemoriaEstudio-12-31 23:59:59'
            and a.anulado = false
            order by b.descripcion asc";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultPuestoTrabajoAo = $stmt->fetchAll();
            $arrayAlteracionesOsteomuscularesPuesoTrabajo = array();
            $countPuestoTrabajoAo = 1;
            $puestoTrabajoAo = "";
            foreach ($resultPuestoTrabajoAo as $r) {
                $item = array();
                $puestoTrabajoId = $r['id'];
                $puestoTrabajoDesc = $r['descripcion'];

                $grupoPuestoTrabajo = "G" . $countPuestoTrabajoAo . ": ";

                $item['AO_PUESTO'] = $grupoPuestoTrabajo . $puestoTrabajoDesc;
                $item['AO_PUESTO_1'] = $this->calcularPorcentajeAlteracionOsteomuscular($empresaId, $anyoMemoriaEstudio, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, false, 1, $puestoTrabajoId), 1, $puestoTrabajoId);
                $item['AO_PUESTO_2'] = $this->calcularPorcentajeAlteracionOsteomuscular($empresaId, $anyoMemoriaEstudio, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, false, 2, $puestoTrabajoId), 2, $puestoTrabajoId);
                $item['AO_PUESTO_3'] = $this->calcularPorcentajeAlteracionOsteomuscular($empresaId, $anyoMemoriaEstudio, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, false, 3, $puestoTrabajoId), 3, $puestoTrabajoId);
                $item['AO_PUESTO_4'] = $this->calcularPorcentajeAlteracionOsteomuscular($empresaId, $anyoMemoriaEstudio, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, false, 4, $puestoTrabajoId), 4, $puestoTrabajoId);
                $item['AO_PUESTO_5'] = $this->calcularPorcentajeAlteracionOsteomuscular($empresaId, $anyoMemoriaEstudio, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, false, 5, $puestoTrabajoId), 5, $puestoTrabajoId);

                array_push($arrayAlteracionesOsteomuscularesPuesoTrabajo, $item);

                //Buscamos los trabajadores por cada puesto de trabajo
                $countPuestoTrabajoReconocimiento = $this->calcularReconocimientosPorPuestoTrabajo($empresaId, $anyoMemoriaEstudio, $puestoTrabajoId);

                $puestoTrabajoAo .= $grupoPuestoTrabajo . $puestoTrabajoDesc . " (" . $countPuestoTrabajoReconocimiento . " trabajadores) " . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";

                $countPuestoTrabajoAo++;
            }
            if (count($arrayAlteracionesOsteomuscularesPuesoTrabajo) == 0) {
                $item = array();
                $item['AO_PUESTO'] = "";
                $item['AO_PUESTO_1'] = "";
                $item['AO_PUESTO_2'] = "";
                $item['AO_PUESTO_3'] = "";
                $item['AO_PUESTO_4'] = "";
                $item['AO_PUESTO_5'] = "";
                array_push($arrayAlteracionesOsteomuscularesPuesoTrabajo, $item);
            }
            $templateProcessor->cloneRowAndSetValues('AO_PUESTO', $arrayAlteracionesOsteomuscularesPuesoTrabajo);

            $templateProcessor->setValue('AO_GRUPO_PUESTO_TRABAJO', $puestoTrabajoAo);

            if (count($arrayAlteracionesOsteomuscularesPuesoTrabajo) > 0 && count($arrayAlteracionesOsteomuscularesPuesoTrabajo) > 0) {
                $filenameAlteracionOsteomuscularPuestoTrabajoGrupo = $this->crearGraficoAlteracionOsteomuscularPuestoTrabajoGrupo($empresaId, $arrayAlteracionesOsteomuscularesPuesoTrabajo);
                $templateProcessor->setImageValue('GRAFICO_ALTERACION_OSTEOMUSCULAR_GRUPO', array('path' => $filenameAlteracionOsteomuscularPuestoTrabajoGrupo, 'width' => 600, 'height' => 300, 'ratio' => false));
            } else {
                $templateProcessor->setValue('GRAFICO_ALTERACION_OSTEOMUSCULAR_GRUPO', "");
            }
            //Vacunados contra el COVID
            $cantidadTotal = $this->calcularVacunadosCovid($empresaId, $anyoMemoriaEstudio, false);
            $templateProcessor->setValue('VAC_COVID', $this->calcularPorcentajeVacunadosCovid($empresaId, $anyoMemoriaEstudio, $cantidadTotal));

            //Pruebas complementarias especificas SPT
            $templateProcessor->setValue('PRUEBAS_SPT', $this->calcularPruebasComplementariasSptTrabajador($empresaId, $anyoMemoriaEstudio));

            //Buscamos los datos del medico de la empresa
            $firmaMedico = "";
            $colegiadoMedico = "";
            $especialidadMedico = "";
            $gestoraMedico = "";
            if (!is_null($empresa->getVigilanciaSalud())) {
                if (!is_null($empresa->getVigilanciaSalud()->getMedico())) {
                    $doctorRevision = $empresa->getVigilanciaSalud()->getMedico()->getDescripcion();
                    $firmaMedico = $empresa->getVigilanciaSalud()->getMedico()->getFirma();
                    $colegiadoMedico = $empresa->getVigilanciaSalud()->getMedico()->getNumeroColegiado();
                    $especialidadMedico = $empresa->getVigilanciaSalud()->getMedico()->getEspecialidad();
                    $gestoraMedico = $empresa->getVigilanciaSalud()->getMedico()->getGestora();
                }
            }
            if ($firmaMedico != "") {
                $templateProcessor->setImageValue('MEDICO_FIRMA', 'upload/media/firmas/medico/' . $firmaMedico);
            } else {
                $templateProcessor->setValue('MEDICO_FIRMA', "");
            }
            $templateProcessor->setValue("MEDICO_NOMBRE", $doctorRevision);
            $templateProcessor->setValue('MEDICO_COLEGIADO', $colegiadoMedico);
            $templateProcessor->setValue('MEDICO_ESPECIALIDAD', $especialidadMedico);
            $templateProcessor->setValue('MEDICO_GESTORA', $gestoraMedico);
        }
        if ($restriccionSn) {
            $medicoRevision = "";
            if (!is_null($revision->getMedico())) {
                $medicoRevision = $revision->getMedico()->getDescripcion();
            }
            $templateProcessor->setValue('REVISION_DOCTOR', $medicoRevision);

            $fechaRevision = "";
            if (!is_null($revision->getFecha())) {
                $fechaRevision = $revision->getFecha()->format('d') . ' / ' . $revision->getFecha()->format('m') . ' / ' . $revision->getFecha()->format('Y');
            }
            $templateProcessor->setValue("FECHA_REVISION", $fechaRevision);
        }
        $templateProcessor->saveAs($urlNueva);

        return true;
    }

    //Peticio 28/07/2023
    function replaceTagsSoloCentro($puestosTrabajoSeleccionadosSelect2, $em, $urlPlantilla, $urlNueva, $tipo, $empresa, $contrato, $factura, $trabajador, $evaluacion, $citacion, $revision, $puestoTrabajoEvaluacion, $anyoMemoriaEstudio, $idiomaPlantilla)
    {
        //Nombre de la empresa
        $nombre = str_replace('&', '&amp;', $empresa->getEmpresa());
        $empresaId = $empresa->getId();

        //Buscamos los CNAES de la empresa
        $cnaesString = null;
        $cnaes = $em->getRepository('App\Entity\CnaeEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false, 'principal' => true));
        foreach ($cnaes as $cn) {
            $cnaesString .= $cn->getCnae()->getCnae() . ' - ' . $cn->getCnae()->getDescripcion() . ', ';
        }

        //Buscamos el CCC de la empresa
        $cccString = $empresa->getCcc();

        //Buscamos los correos de la empresa
        $emailString = null;
        $email = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));
        foreach ($email as $e) {
            $emailString .= $e->getCorreo() . ', ';
        }

        $emailString = rtrim($emailString, ", ");

        //Buscamos la cuenta bancaria principal de la empresa
        $iban = null;
        $pais = null;
        $cccPrincipal = null;
        $diaPago = null;
        $formaPagoEmpresa = null;
        $bic = null;
        $datosBancarios = $em->getRepository('App\Entity\DatosBancarios')->findOneBy(array('empresa' => $empresa, 'anulado' => false, 'principal' => true));
        if (!is_null($datosBancarios)) {
            $iban = $datosBancarios->getIbanDigital();
            if (!is_null($datosBancarios->getPais())) {
                $pais = $datosBancarios->getPais()->getDescripcion();
            }
            $cccPrincipal = $datosBancarios->getNumCuenta();
            $diaPago = $datosBancarios->getDiaPago();
            if (!is_null($datosBancarios->getFormaPago())) {
                $formaPagoEmpresa = $datosBancarios->getFormaPago()->getDescripcion();
            }
            if (!is_null($datosBancarios->getEntidadBancaria())) {
                $bic = $datosBancarios->getEntidadBancaria()->getBic();
            }
        }
        $facturaSn = false;
        $evaluacionSn = false;
        $citacionSn = false;
        $planPrevencionSn = false;
        $revisionSn = false;
        $fichaRiesgosSn = false;
        $resumenRevisionSn = false;
        $revisionMedicaSn = false;
        $memoriaSn = false;
        $estudioSn = false;
        $restriccionSn = false;
        $modelo347Sn = false;
        $contratoFecha = null;
        $contratoFechaVencimiento = null;
        $contratoNumero = null;
        $importePrevencion = 0;
        $fechaFactura = null;
        $numFactura = null;
        $observaciones = null;
        $formaPago = null;
        $conceptos = null;
        $importeExentoIva = 0;
        $importeSujetoIva = 0;
        $importeIva = 0;
        $importeTotalFactura = 0;
        $giros = null;
        $nombreTrabajador = null;
        $dniTrabajador = null;
        $edadTrabajador = null;
        $fechaNacimiento = null;
        $sexo = null;
        $emailTrabajador = null;
        $telefonoTrabajador = null;
        $puestoTrabajoTrabajador = null;
        $direccionTrabajador = null;
        $codigoTrabajador = null;
        $nombreCentro = null;
        $direccionCentro = null;
        $localidadCentro = null;
        $provinciaCentro = null;
        $telefonoCentro = null;
        $descripcionCentro = null;
        $nombreCentro2 = null;
        $direccionCentro2 = null;
        $localidadCentro2 = null;
        $provinciaCentro2 = null;
        $telefonoCentro2 = null;
        $descripcionCentro2 = null;
        $tipoEvaluacion = null;
        $visitasEvaluacion = null;
        $acompanyantesEvaluacion = null;
        $trabajadoresPorPuestoTrabajo = null;
        $arrayPuestosTrabajo = array();
        $tipoEvaluacionDesc = NULL;
        $evaluacionId = null;
        $tecnicoEvaluacion = null;
        $firmaTecnico = null;
        $normativas = null;
        $fechaCitacion = null;
        $trabajadorCitacion = null;
        $fechaPlanPrevencion = null;
        $fechaRevision = null;
        $fechaAptitud = null;
        $aptitudRevision = null;
        $aptitudRestriccion = null;
        $doctorRevision = null;
        $protocolosRevision = null;
        $cuestionarioRevision = null;
        $preguntaCuesionarioRevision = null;
        $respuestasCuestionarioPreguntaRevision = null;

        //Buscamos el contrato activo de la empresa
        if (is_null($contrato)) {
            $contrato = $em->getRepository('App\Entity\Contrato')->findOneBy(array('empresa' => $empresa, 'anulado' => false, 'cancelado' => false), array('fechainicio' => 'DESC'));
            if (!is_null($contrato)) {
                $contratoNumero = $contrato->getContrato();
                $contratoFecha = $contrato->getFechainicio()->format('d/m/Y');

                $renovacion = $em->getRepository('App\Entity\Renovacion')->findOneBy(array('contrato' => $contrato, 'anulado' => false));
                if (!is_null($renovacion)) {
                    $contratoFechaVencimiento = $renovacion->getFechavencimiento()->format('d/m/Y');
                }
            }
        } else {
            $contratoNumero = $contrato->getContrato();
            $contratoFecha = $contrato->getFechainicio()->format('d/m/Y');

            $renovacion = $em->getRepository('App\Entity\Renovacion')->findOneBy(array('contrato' => $contrato, 'anulado' => false));
            if (!is_null($renovacion)) {
                $contratoFechaVencimiento = $renovacion->getFechavencimiento()->format('d/m/Y');
            }
        }

        //Buscamos el importe total del contrato
        $contratoPago = $em->getRepository('App\Entity\ContratoPago')->findBy(array('contrato' => $contrato, 'anulado' => false));
        foreach ($contratoPago as $cp) {
            $importePrevencion += $cp->getImporteSinIva();
        }

        //Buscamos la ultima factura
        if (is_null($factura)) {
            $factura = $em->getRepository('App\Entity\Facturacion')->findOneBy(array('contrato' => $contrato, 'anulado' => false));
        }
        if (!is_null($factura)) {
            $fechaFactura = $factura->getFecha()->format('Y-m-d');
            $numFactura = $factura->getSerie()->getSerie() . $factura->getNumFac();
            $observaciones = $factura->getObservaciones();
            if (!is_null($factura->getFormaPago())) {
                $formaPago = $factura->getFormaPago()->getDescripcion();
            }
        }

        //Buscamos los importes de la factura
        $facturaLineasConceptos = $em->getRepository('App\Entity\FacturacionLineasPagos')->findBy(array('facturacion' => $factura, 'anulado' => false));
        $arrayConceptosFactura = array();
        if (count($facturaLineasConceptos) > 0) {
            foreach ($facturaLineasConceptos as $flc) {
                $item = array();
                $item['CONCEPTO_UNIDADES'] = $flc->getUnidades();
                $item['CONCEPTO_DESC'] = trim($flc->getConcepto());
                $item['CONCEPTO_EXENTO_IVA'] = '0,00';
                $item['CONCEPTO_SUJETO_IVA'] = number_format(round($flc->getImporteSujetoIva(), 2), 2, ',', '.');
                $item['CONCEPTO_IVA'] = number_format(round($flc->getImporteIva(), 2), 2, ',', '.');
                $item['CONCEPTO_TOTAL'] = number_format(round($flc->getImporteTotal(), 2), 2, ',', '.');
                array_push($arrayConceptosFactura, $item);

                $importeExentoIva += $flc->getImporteExentoIva();
                $importeSujetoIva += $flc->getImporteSujetoIva();
                $importeIva += $flc->getImporteIva();
                $importeTotalFactura += $flc->getImporteTotal();
            }
        } else {
            $facturaLineasConceptos = $em->getRepository('App\Entity\FacturacionLineasConceptos')->findBy(array('facturacion' => $factura, 'anulado' => false));
            foreach ($facturaLineasConceptos as $flc) {
                $item = array();
                $item['CONCEPTO_UNIDADES'] = $flc->getUnidades();
                $item['CONCEPTO_DESC'] = trim($flc->getConcepto());
                $item['CONCEPTO_EXENTO_IVA'] = '0,00';
                $item['CONCEPTO_SUJETO_IVA'] = number_format(round($flc->getImporteUnidad() * $flc->getUnidades(), 2), 2, ',', '.');
                $item['CONCEPTO_IVA'] = number_format(round($flc->getIva(), 2), 2, ',', '.');
                $item['CONCEPTO_TOTAL'] = number_format(round($flc->getImporte(), 2), 2, ',', '.');
                array_push($arrayConceptosFactura, $item);

                $importeExentoIva += $flc->getImporteUnidad() * $flc->getUnidades();
                $importeSujetoIva += $flc->getImporteUnidad() * $flc->getUnidades();
                $importeIva += $flc->getIva();
                $importeTotalFactura += $flc->getImporte();
            }

            if ($importeIva > 0) {
                $importeExentoIva = 0;
            } else {
                $importeSujetoIva = 0;
            }
        }
        $importeExentoIva = number_format(round($importeExentoIva, 2), 2, ',', '.');
        $importeSujetoIva = number_format(round($importeSujetoIva, 2), 2, ',', '.');
        $importeIva = number_format(round($importeIva, 2), 2, ',', '.');
        $importeTotalFactura = number_format(round($importeTotalFactura, 2), 2, ',', '.');

        //Buscamos el numero de cuenta de la empresa
        $datosBancarios = $em->getRepository('App\Entity\DatosBancarios')->findOneBy(array('principal' => true, 'empresa' => $empresa, 'anulado' => false));
        $numeroCuentaEmpresa = "";
        if (!is_null($datosBancarios)) {
            $numeroCuentaEmpresa = $datosBancarios->getIbanPapel();
            $numeroCuentaEmpresa = substr($numeroCuentaEmpresa, 0, -4);
            $numeroCuentaEmpresa = $numeroCuentaEmpresa . 'XXXX';
        }


        //Buscamos los giros de la factura
        $giro = $em->getRepository('App\Entity\GiroBancario')->findBy(array('facturacion' => $factura, 'anulado' => false));
        $arrayDetalleGiros = array();
        foreach ($giro as $g) {
            $item = array();
            $item['DETALLEGIROS_CONCEPTO'] = $g->getConcepto();
            $item['DETALLEGIROS_FECHA'] = $g->getVencimiento()->format('d/m/Y');
            $item['DETALLEGIROS_IMPORTE'] = round($g->getImporte(), 2) . " €";
            $item['DETALLEGIROS_CUENTA'] = $numeroCuentaEmpresa;
            array_push($arrayDetalleGiros, $item);
        }

        $giroDevolucion = $em->getRepository('App\Entity\GiroBancarioDevolucion')->findBy(array('facturacion' => $factura, 'anulado' => false));
        foreach ($giroDevolucion as $gd) {
            $item = array();
            $item['DETALLEGIROS_CONCEPTO'] = $gd->getConcepto();
            $item['DETALLEGIROS_FECHA'] = $gd->getFecha()->format('d/m/Y');
            $item['DETALLEGIROS_IMPORTE'] = round($gd->getImporte(), 2) . " €";
            $item['DETALLEGIROS_CUENTA'] = "";
            array_push($arrayDetalleGiros, $item);
        }

        $facturaVencimiento = $em->getRepository('App\Entity\FacturacionVencimiento')->findBy(array('facturaAsociada' => $factura, 'anulado' => false));
        foreach ($facturaVencimiento as $fv) {
            $item = array();
            $item['DETALLEGIROS_CONCEPTO'] = $fv->getConcepto();
            $item['DETALLEGIROS_FECHA'] = $fv->getFecha()->format('d/m/Y');
            $item['DETALLEGIROS_IMPORTE'] = round($fv->getImporte(), 2) . " €";
            $item['DETALLEGIROS_CUENTA'] = "";
            array_push($arrayDetalleGiros, $item);
        }

        if (count($arrayDetalleGiros) == 0) {
            $item = array();
            $item['DETALLEGIROS_CONCEPTO'] = "";
            $item['DETALLEGIROS_FECHA'] = "";
            $item['DETALLEGIROS_IMPORTE'] = "";
            $item['DETALLEGIROS_CUENTA'] = "";
            array_push($arrayDetalleGiros, $item);
        }

        //Buscamos los datos del trabajador
        if (!is_null($trabajador)) {
            $nombreTrabajador = $trabajador->getNombre();
            $dniTrabajador = $trabajador->getDni();
            $edadTrabajador = $trabajador->getEdad();
            $emailTrabajador = $trabajador->getMail();
            $telefonoTrabajador = $trabajador->getTelefono1();
            $direccionTrabajador = $trabajador->getDomicilio();
            if (!is_null($trabajador->getFechaNacimiento())) {
                $fechaNacimiento = $trabajador->getFechaNacimiento()->format('d') . '/' . $trabajador->getFechaNacimiento()->format('m') . '/' . $trabajador->getFechaNacimiento()->format('Y');
            }

            switch ($trabajador->getSexo()) {
                case '1':
                    $sexo = 'Hombre';
                    break;
                case '2':
                    $sexo = 'Mujer';
                    break;
            }

            $trabajadorId = $trabajador->getId();

            if (!is_null($trabajador->getIdRiesgos())) {
                $codigoTrabajador = $trabajador->getIdRiesgos();
            } else {
                $codigoTrabajador = $trabajadorId;
            }

            $query = "select b.descripcion from puesto_trabajo_trabajador a
            inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
            where a.anulado = false
            and b.anulado = false 
            and a.trabajador_id = $trabajadorId
            order by a.id desc";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $puestoTrabajoArray = $stmt->fetchAll();

            if (count($puestoTrabajoArray) > 0) {
                $puestoTrabajoTrabajador = $puestoTrabajoArray[0]['descripcion'];
            }
        }

        //Buscamos los centros de la empresa
        $query = "select b.nombre, b.direccion, b.localidad, b.provincia, b.telefono, b.actividad_centro from centro_trabajo_empresa a
                inner join centro b on a.centro_id = b.id
                where a.empresa_id = $empresaId
                and a.anulado = false
                order by a.centro_id asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $centrosTrabajo = $stmt->fetchAll();

        if (count($centrosTrabajo) > 0) {
            $nombreCentro = $centrosTrabajo[0]['nombre'];
            $direccionCentro = $centrosTrabajo[0]['direccion'];
            $localidadCentro = $centrosTrabajo[0]['localidad'];
            $provinciaCentro = $centrosTrabajo[0]['provincia'];
            $telefonoCentro = $centrosTrabajo[0]['telefono'];
            $descripcionCentro = $centrosTrabajo[0]['actividad_centro'];

            if (count($centrosTrabajo) > 1) {
                $nombreCentro2 = $centrosTrabajo[1]['nombre'];
                $direccionCentro2 = $centrosTrabajo[1]['direccion'];
                $localidadCentro2 = $centrosTrabajo[1]['localidad'];
                $provinciaCentro2 = $centrosTrabajo[1]['provincia'];
                $telefonoCentro2 = $centrosTrabajo[1]['telefono'];
                $descripcionCentro2 = $centrosTrabajo[1]['actividad_centro'];
            }
        } else {
            $direccionCentro = 'Deslocalizado';
            $localidadCentro = '';
        }

        switch ($tipo) {
            case '1':
                break;
            case '2':
                $facturaSn = true;
                break;
            case '5':
                $modelo347Sn = true;
                break;
            case '6':
                break;
            case '7':
                $evaluacionSn = true;

                $tipo = $evaluacion->getTipo();
                switch ($tipo) {
                    case 1:
                        $tipoEvaluacion = 'I-';
                        $tipoEvaluacionDesc = 'INICIAL';
                        break;
                    case 2:
                        $tipoEvaluacion = 'R-';
                        $tipoEvaluacionDesc = 'REVISIÓN';
                        break;
                }

                $evaluacionId = $evaluacion->getId();
                $query = "select count(*) as numero from evaluacion where empresa_id = $empresaId and anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $countEvaluaciones = $stmt->fetchAll();

                if (count($countEvaluaciones) > 0) {
                    $contador = $countEvaluaciones[0]['numero'];
                    if ($contador == 0) {
                        $contador++;
                    }

                    $tipoEvaluacion .= str_pad($contador, 2, '0', STR_PAD_LEFT);
                }

                //Buscamos los centros de la evaluacion
                $centrosEvaluacion = $em->getRepository('App\Entity\EvaluacionCentroTrabajo')->findBy(array('evaluacion' => $evaluacion));
                $count = 1;
                $multiCentroSn = false;
                $dosCentrosSn = false;
                $evaluacionCentro1Id = null;
                $evaluacionCentro2Id = null;
                $arrayIdCentroTrabajoEvaluacion = array();
                $arrayCentroTrabajoEvaluacion = array();
                $centroTrabajoDireccion = "";
                $centroTrabajoDireccion2 = "";
                foreach ($centrosEvaluacion as $c) {
                    if ($count == 1) {
                        $nombreCentro = $c->getCentro()->getNombre();
                        $direccionCentro = $c->getCentro()->getDireccion();
                        $localidadCentro = $c->getCentro()->getLocalidad();
                        $provinciaCentro = $c->getCentro()->getProvincia();
                        $telefonoCentro = $c->getCentro()->getTelefono();
                        $descripcionCentro = $c->getCentro()->getActividadCentro();
                        $evaluacionCentro1Id = $c->getCentro()->getId();
                    }

                    if ($count == 2) {
                        $nombreCentro2 = $c->getCentro()->getNombre();
                        $direccionCentro2 = $c->getCentro()->getDireccion();
                        $localidadCentro2 = $c->getCentro()->getLocalidad();
                        $provinciaCentro2 = $c->getCentro()->getProvincia();
                        $telefonoCentro2 = $c->getCentro()->getTelefono();
                        $descripcionCentro2 = $c->getCentro()->getActividadCentro();
                        $evaluacionCentro2Id = $c->getCentro()->getId();
                    }

                    $item = array();
                    $item['CENTRO_TEXTO#' . $count] = 'Centro ' . $count;
                    $item['CENTRO_DIRECCION#' . $count] = $c->getCentro()->getNombre();
                    $item['CENTRO_LOCALIDAD#' . $count] = $c->getCentro()->getLocalidad();
                    $item['CENTRO_PROVINCIA#' . $count] = $c->getCentro()->getProvincia();
                    $item['CENTRO_TELEFONO#' . $count] = $c->getCentro()->getTelefono();
                    $item['CENTRO_DESCRIPCION#' . $count] = $c->getCentro()->getActividadCentro();

                    array_push($arrayIdCentroTrabajoEvaluacion, $c->getCentro()->getId());
                    array_push($arrayCentroTrabajoEvaluacion, $item);

                    $centroTrabajoDireccion .= $c->getCentro()->getDireccion() . ' en ' . $c->getCentro()->getProvincia() . ', ';
                    $centroTrabajoDireccion2 .= $c->getCentro()->getDireccion() . '(' . $c->getCentro()->getProvincia() . '), ';

                    $count++;
                }

                if (count($centrosEvaluacion) == 2) {
                    $dosCentrosSn = true;
                }

                if (count($arrayIdCentroTrabajoEvaluacion) > 2) {
                    $multiCentroSn = true;
                }

                //Buscamos las visitas de la evaluacion
                $visitas =  $em->getRepository('App\Entity\VisitaEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));
                foreach ($visitas as $v) {
                    $visitasEvaluacion .= $v->getDtVisita()->format('d/m/Y') . ",";
                }
                $visitasEvaluacion = rtrim($visitasEvaluacion, ",");

                //Buscamos los acompañantes de la evaluacion
                $acompanyantes =  $em->getRepository('App\Entity\PersonaEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));
                foreach ($acompanyantes as $a) {
                    $acompanyantesEvaluacion .= $a->getNombre() . " " . $a->getApellido1() . " " . $a->getApellido2() . ",";
                }
                $acompanyantesEvaluacion = rtrim($acompanyantesEvaluacion, ",");

                if ($puestosTrabajoSeleccionadosSelect2 === 0) {
                } else {
                    $cadena = implode(',', $puestosTrabajoSeleccionadosSelect2);
                }
                if ($puestosTrabajoSeleccionadosSelect2[0] === 0 || $puestosTrabajoSeleccionadosSelect2 === 0) {
                    //Buscamos los puestos de trabajo de la empresa
                    $query = "select a.id, b.descripcion, a.trabajadores, a.tarea from puesto_trabajo_evaluacion a
                inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                where a.anulado = false
                and evaluacion_id = $evaluacionId
                order by b.descripcion ASC";
                } else {
                    //Buscamos los puestos de trabajo de la empresa
                    $query = "select a.id, b.descripcion, a.trabajadores, a.tarea from puesto_trabajo_evaluacion a
                inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                where a.anulado = false
                and evaluacion_id = $evaluacionId and a.id IN($cadena)
                order by b.descripcion ASC";
                }

                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $puestosTrabajos = $stmt->fetchAll();
                $count = 1;
                foreach ($puestosTrabajos as $pt) {
                    $trabajadoresPorPuestoTrabajo .= " - " . $pt['descripcion'] . " (" . $pt['trabajadores'] . ")" . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                    $item = array();
                    $item['FICHA_NOMBRE_PUESTO_TRABAJO'] = 'Nº ' . $count . ' ' . $pt['descripcion'];
                    $item['FICHA_TAREA_PUESTO_TRABAJO'] = $pt['tarea'];
                    array_push($arrayPuestosTrabajo, $item);
                    $count++;
                }

                //Comprobamos si la evaluación tiene marcada toda la normativa
                $grupoTodaNormativa = $this->getDoctrine()->getRepository('App\Entity\GrupoNormativa')->find(14);
                $todaNormativa = $this->getDoctrine()->getRepository('App\Entity\GrupoNormativaEvaluacion')->findOneBy(array('evaluacion' => $evaluacion, 'anulado' => false, 'grupoNormativa' => $grupoTodaNormativa));

                if (!is_null($todaNormativa)) {
                    $query = "select  concat(b.titulo_es,' ', b.descripcion_es) as normativa from grupo_normativa a inner join normativa b on a.id = b.grupo_normativa_id where b.anulado = false";
                } else {
                    $query = "select concat(c.titulo_es,' ', c.descripcion_es) as normativa from grupo_normativa_evaluacion a
                    inner join grupo_normativa b on a.grupo_normativa_id = b.id
                    inner join normativa c on b.id = c.grupo_normativa_id
                    where a.evaluacion_id = $evaluacionId
                    and c.anulado = false
                    order by a.grupo_normativa_id asc";
                }
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $normativasEvaluacion = $stmt->fetchAll();
                foreach ($normativasEvaluacion as $n) {
                    $normativas .= " - " . $n['normativa'] . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                }

                //Buscamos el tecnico de la evaluacion y la firma
                $tecnicos = $em->getRepository('App\Entity\TecnicoEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));
                $arrayFirmasTecnicos = array();
                foreach ($tecnicos as $t) {
                    $tecnicoEvaluacion .= $t->getTecnico()->getNombre() . ' ' . $t->getTecnico()->getApellido1() . ' ' . $t->getTecnico()->getApellido2() . ', ';
                    array_push($arrayFirmasTecnicos, $t->getTecnico()->getFirma());
                }
                $tecnicoEvaluacion = rtrim($tecnicoEvaluacion, ", ");
                break;
            case '8':
                $planPrevencionSn = true;

                //Buscamos los puestos de trabajo de la empresa
                $query = "select a.id, b.descripcion, a.trabajadores, a.tarea from puesto_trabajo_evaluacion a
                inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                where a.anulado = false
                and evaluacion_id in (select id from evaluacion where empresa_id = $empresaId and anulado = false order by fecha_inicio desc limit 1)
                order by b.descripcion ASC";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $puestosTrabajos = $stmt->fetchAll();
                foreach ($puestosTrabajos as $pt) {
                    $trabajadoresPorPuestoTrabajo .= " - " . $pt['descripcion'] . " (" . $pt['trabajadores'] . ")" . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                }

                break;
            case '9':
                $citacionSn = true;
                break;
            case '10':
            case '11':
                $revisionSn = true;
                break;
            case '12':
                $fichaRiesgosSn = true;
                break;
            case '13':
                $resumenRevisionSn = true;
                break;
            case '14':
                $revisionMedicaSn = true;
                break;
            case '15':
                $memoriaSn = true;
                break;
            case '16':
                $estudioSn = true;
                break;
            case '17':
                $restriccionSn = true;
                break;
        }

        $empresaId = $empresa->getId();
        $queryAux = "SELECT COUNT(tab.id) as numero FROM trabajador_alta_baja tab WHERE tab.empresa_id = $empresaId and tab.activo = true";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($queryAux);
        $stmt->execute();
        $numTrabajadores = $stmt->fetchAll();
        $numTrabajadoresAux = $numTrabajadores[0]['numero'];

        //Petició 28/07/2023 #63749
        $queryAux2 = "SELECT COUNT(*) AS total_registros
        FROM (
            SELECT (SELECT numero
                    FROM (
                        SELECT id, empresa_id, row_number() OVER (ORDER BY fecha_inicio ASC) AS numero
                        FROM evaluacion
                        WHERE anulado = false 
                        AND empresa_id = a.empresa_id
                        ORDER BY fecha_inicio ASC
                    ) consulta
                    WHERE id = a.id) AS numero
            FROM evaluacion a
            WHERE a.anulado = false AND a.empresa_id = $empresaId
        ) subconsulta";
        $stmt2 = $this->getDoctrine()->getManager()->getConnection()->prepare($queryAux2);
        $stmt2->execute();
        $numEvaluaciones = $stmt2->fetchAll();
        $numEvaluaciones2 = $numEvaluaciones[0]['total_registros'];


        $hoy = new \DateTime();
        $hoyString = $hoy->format('d/m/Y');

        copy($urlPlantilla, $urlNueva);
        $centroTrabajoDireccion = "";
        $centroTrabajoDireccion2 = "";

        //Reemplazamos los tags
        $templateProcessor = new TemplateProcessor($urlNueva);
        $templateProcessor->setValue("EMPRESA_NOMBRE", $nombre);
        //Petició 28/07/2023 #63749
        $templateProcessor->setValue("NUM_EVALU", $numEvaluaciones2);
        $templateProcessor->setValue("EMPRESA_CODIGO", $empresa->getCodigo());
        $templateProcessor->setValue("EMPRESA_CIF", $empresa->getCif());
        $templateProcessor->setValue("EMPRESA_DOMICILIO_FISCAL", $empresa->getDomicilioFiscal());
        $templateProcessor->setValue("EMPRESA_CODPOSTAL_FISCAL", $empresa->getCodigoPostalFiscal());
        $templateProcessor->setValue("EMPRESA_LOCALIDAD_FISCAL", $empresa->getLocalidadFiscal());
        $templateProcessor->setValue("EMPRESA_PROVINCIA_FISCAL", $empresa->getProvinciaFiscal());
        $templateProcessor->setValue("EMPRESA_CNAES", $cnaesString);
        $templateProcessor->setValue("EMPRESA_NUMTRABAJADORES", $numTrabajadoresAux);
        $templateProcessor->setValue("CENTROTRABAJO_CCC", $cccString);
        $templateProcessor->setValue("EMPRESA_TELEFONO1", $empresa->getTelefono1());
        $templateProcessor->setValue("EMPRESA_FAX", $empresa->getFax());
        $templateProcessor->setValue("EMPRESA_EMAIL", $emailString);
        $templateProcessor->setValue("CONTRATO_FECHA", $contratoFecha);
        $templateProcessor->setValue("CONTRATO_VENCIMIENTO", $contratoFechaVencimiento);
        $templateProcessor->setValue("EMPRESA_REPRESENTANTE", $empresa->getNombreRepresentante());
        $templateProcessor->setValue("EMPRESA_REPRESENTANTE_DNI", $empresa->getDniRepresentante());
        $templateProcessor->setValue("CUENTAPRINCIPAL_IBAN", $iban);
        $templateProcessor->setValue("DIA_PAGO", $diaPago);
        $templateProcessor->setValue("FORMA_PAGO", $formaPagoEmpresa);
        $templateProcessor->setValue("BIC", $bic);
        $templateProcessor->setValue("IMPORTE_PREVENCION", round($importePrevencion, 2));
        $templateProcessor->setValue("EMPRESA_PAIS_FISCAL", strtoupper($pais));
        $templateProcessor->setValue("EMPRESA_ACTIVIDAD", $empresa->getActividad());
        $templateProcessor->setValue("IMPORTE_VIGILANCIA", round($importePrevencion, 2));
        $templateProcessor->setValue("CUENTAPRINCIPAL_CCC", $cccPrincipal);
        $templateProcessor->setValue("EMPRESA_REPRESENTANTE_CARGO", $empresa->getCargoRepresentante());
        $templateProcessor->setValue("EMPRESA_CODCLIENTE", $empresa->getCodigo());
        $templateProcessor->setValue("FACTURA_NUMERO", $numFactura);
        $templateProcessor->setValue("FACTURA_FECHA", $fechaFactura);
        $templateProcessor->setValue("EMPRESA_DIRECCION", $empresa->getDomicilioPostal());
        $templateProcessor->setValue("EMPRESA_CODPOSTAL", $empresa->getCodigoPostalPostal());
        $templateProcessor->setValue("EMPRESA_LOCALIDAD", $empresa->getLocalidadPostal());
        $templateProcessor->setValue("EMPRESA_PROVINCIA", $empresa->getProvinciaPostal());
        //$templateProcessor->setValue("FACTURA_CONCEPTOS", $conceptos);
        $templateProcessor->setValue("EXENTOIVA", $importeExentoIva);
        $templateProcessor->setValue("SUJETOIVA", $importeSujetoIva);
        $templateProcessor->setValue("IVA", $importeIva);
        $templateProcessor->setValue("TOTFACTURA", $importeTotalFactura);
        $templateProcessor->setValue("FACTURA_OBSERVACIONES", $observaciones);
        $templateProcessor->setValue("FACTURA_FORMAPAGO", $formaPago);
        $templateProcessor->setValue("DETALLEGIROS", $giros);
        $templateProcessor->setValue("MIEMPRESA_LOCALIDAD", 'BARCELONA');
        $templateProcessor->setValue("FECHA", $hoyString);
        $templateProcessor->setValue("EMPRESA", $nombre);
        $templateProcessor->setValue("DIRECCION", $empresa->getDomicilioFiscal());
        $templateProcessor->setValue("CODPOSTAL", $empresa->getCodigoPostalFiscal());
        $templateProcessor->setValue("LOCALIDAD", $empresa->getLocalidadFiscal());
        $templateProcessor->setValue("PROVINCIA", $empresa->getProvinciaFiscal());
        $templateProcessor->setValue("CONTRATO_NUMERO", $contratoNumero);
        $templateProcessor->setValue("CONTRATO_NUMERO", $contratoNumero);
        $templateProcessor->setValue("TRABAJADOR_NOMBRE", $nombreTrabajador);
        $templateProcessor->setValue("TRABAJADOR_DNI", $dniTrabajador);
        $templateProcessor->setValue("TRABAJADOR_EDAD", $edadTrabajador);
        $templateProcessor->setValue("TRABAJADOR_FECHA_NACIMIENTO", $fechaNacimiento);
        $templateProcessor->setValue("TRABAJADOR_TELEFONO", $telefonoTrabajador);
        $templateProcessor->setValue("TRABAJADOR_EMAIL", $emailTrabajador);
        $templateProcessor->setValue("TRABAJADOR_SEXO", $sexo);
        $templateProcessor->setValue("TRABAJADOR_PUESTO_TRABAJO", $puestoTrabajoTrabajador);
        $templateProcessor->setValue("TRABAJADOR_DIRECCION", $direccionTrabajador);
        $templateProcessor->setValue("TRABAJADOR_CODIGO", $codigoTrabajador);
        $templateProcessor->setValue("EMPRESA_CENTRO", $nombreCentro);
        $templateProcessor->setValue("EMPRESA_CENTRO2", $nombreCentro2);
        $templateProcessor->setValue("EMPRESA_CODIGO_EVALUACION", $empresa->getCodigo() . ' ' . $tipoEvaluacion);
        $templateProcessor->setValue("EMPRESA_CODIGO_TECNICO", $empresa->getCodigoTecnico());
        $templateProcessor->setValue("EMPRESA_MARCA_COMERCIAL" ,str_replace('&', '&amp;', $empresa->getMarcaComercial()));
        //$templateProcessor->setValue("EMPRESA_MARCA_COMERCIAL", $empresa->getMarcaComercial());
        $templateProcessor->setValue("CENTRO_DIRECCION", $direccionCentro);
        $templateProcessor->setValue("CENTRO_LOCALIDAD", $localidadCentro);
        $templateProcessor->setValue("CENTRO_PROVINCIA", $provinciaCentro);
        $templateProcessor->setValue("CENTRO_TELEFONO", $telefonoCentro);
        $templateProcessor->setValue("CENTRO_DESCRIPCION", $descripcionCentro);
        $templateProcessor->setValue("CENTRO2_DIRECCION", $direccionCentro2);
        $templateProcessor->setValue("CENTRO2_LOCALIDAD", $localidadCentro2);
        $templateProcessor->setValue("CENTRO2_PROVINCIA", $provinciaCentro2);
        $templateProcessor->setValue("CENTRO2_TELEFONO", $telefonoCentro2);
        $templateProcessor->setValue("CENTRO2_DESCRIPCION", $descripcionCentro2);
        $templateProcessor->setValue("EVALUACION_VISITAS", $visitasEvaluacion);
        $templateProcessor->setValue("EVALUACION_ACOMPAÑANTES", $acompanyantesEvaluacion);
        $templateProcessor->setValue("TRABAJADORES_POR_PUESTO_TRABAJO", $trabajadoresPorPuestoTrabajo);
        $templateProcessor->setValue("NORMATIVAS", $normativas);
        $templateProcessor->setValue("TECNICO_EVALUACION", $tecnicoEvaluacion);
        $templateProcessor->setValue("EMPRESA_PROCESO_PRODUCTIVO", $empresa->getProcesoProductivo());
        $templateProcessor->setValue("CENTRO_TRABAJO_MULTI", $centroTrabajoDireccion);
        $templateProcessor->setValue("CENTRO_TRABAJO_MULTI2", $centroTrabajoDireccion2);

        //ini_set("pcre.backtrack_limit", -1);

        if ($facturaSn) {
            $templateProcessor->cloneRowAndSetValues('CONCEPTO_UNIDADES', $arrayConceptosFactura);
            if (count($arrayDetalleGiros) > 0) {
                $templateProcessor->cloneRowAndSetValues('DETALLEGIROS_CONCEPTO', $arrayDetalleGiros);
            }
        }

        if ($modelo347Sn) {
            $today = new \DateTime();
            $year = $today->format('Y') - 1;

            $templateProcessor->setValue('AÑO_EJERCICIO', $year);

            $query = "select a.id from facturacion a 
            where a.anulado = false
            and a.serie_id = 7
            and a.empresa_id = $empresaId
            and a.fecha between '$year-01-01 00:00:00' and '$year-03-31 23:59:59'";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $facturasPrimerEjercicio = $stmt->fetchAll();
            $importePrimerEjercicio = 0;
            foreach ($facturasPrimerEjercicio as $fpe) {

                $facturacionId = $fpe['id'];

                //Comprobamos si tiene abono
                $query = "select b.importe from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.serie_id = 6 and a.factura_asociada_id = $facturacionId and a.anulado = false and b.anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultAbono = $stmt->fetchAll();
                $importeAbono = 0;
                foreach ($resultAbono as $ra) {
                    $importeAbono += abs(round($ra['importe'], 2));
                }

                //Comprobamos si tiene entrada en el balance
                $query = "select importe from balance_economico_entrada where anulado = false and facturacion_id = $facturacionId and tipo = 1";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultEntrada = $stmt->fetchAll();
                $importeEntrada = 0;
                foreach ($resultEntrada as $re) {
                    $importeEntrada += abs(round($re['importe'], 2));
                }

                //Buscamos los conceptos
                $query = "select importe_total as importe from facturacion_lineas_pagos where anulado = false and facturacion_id = $facturacionId";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $conceptosFactura = $stmt->fetchAll();
                $importeFacturas = 0;
                if (count($conceptosFactura) == 0) {
                    $query = "select importe from facturacion_lineas_conceptos where anulado = false and facturacion_id = $facturacionId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $conceptosFactura = $stmt->fetchAll();
                }
                foreach ($conceptosFactura as $cf) {
                    $importeFacturas += round($cf['importe'], 2);
                }

                $tmpImporte = round($importeFacturas, 2) - (round($importeAbono, 2) + $importeEntrada);

                $importePrimerEjercicio += round($tmpImporte, 2);
            }

            $templateProcessor->setValue('IMPORTE_PRIMER_TRIMESTRE', number_format(round($importePrimerEjercicio, 2), 2, ',', '.'));

            $query = "select a.id from facturacion a 
            where a.anulado = false
            and a.serie_id = 7
            and a.empresa_id = $empresaId
            and a.fecha between '$year-04-01 00:00:00' and '$year-06-30 23:59:59'";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $facturasSegundoEjercicio = $stmt->fetchAll();
            $importeSegundoEjercicio = 0;
            foreach ($facturasSegundoEjercicio as $fse) {

                $facturacionId = $fse['id'];

                //Comprobamos si tiene abono
                $query = "select b.importe from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.serie_id = 6 and a.factura_asociada_id = $facturacionId and a.anulado = false and b.anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultAbono = $stmt->fetchAll();
                $importeAbono = 0;
                foreach ($resultAbono as $ra) {
                    $importeAbono += abs(round($ra['importe'], 2));
                }

                //Comprobamos si tiene entrada en el balance
                $query = "select importe from balance_economico_entrada where anulado = false and facturacion_id = $facturacionId and tipo = 1";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultEntrada = $stmt->fetchAll();
                $importeEntrada = 0;
                foreach ($resultEntrada as $re) {
                    $importeEntrada += abs(round($re['importe'], 2));
                }

                //Buscamos los conceptos
                $query = "select importe_total as importe from facturacion_lineas_pagos where anulado = false and facturacion_id = $facturacionId";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $conceptosFactura = $stmt->fetchAll();
                $importeFacturas = 0;
                if (count($conceptosFactura) == 0) {
                    $query = "select importe from facturacion_lineas_conceptos where anulado = false and facturacion_id = $facturacionId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $conceptosFactura = $stmt->fetchAll();
                }
                foreach ($conceptosFactura as $cf) {
                    $importeFacturas += round($cf['importe'], 2);
                }

                $tmpImporte = round($importeFacturas, 2) - (round($importeAbono, 2) + $importeEntrada);

                $importeSegundoEjercicio += round($tmpImporte, 2);
            }

            $templateProcessor->setValue('IMPORTE_SEGUNDO_TRIMESTRE', number_format(round($importeSegundoEjercicio, 2), 2, ',', '.'));

            $query = "select a.id from facturacion a 
            where a.anulado = false
            and a.serie_id = 7
            and a.empresa_id = $empresaId
            and a.fecha between '$year-07-01 00:00:00' and '$year-09-30 23:59:59'";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $facturasTercerEjercicio = $stmt->fetchAll();
            $importeTercerEjercicio = 0;
            foreach ($facturasTercerEjercicio as $fte) {

                $facturacionId = $fte['id'];

                //Comprobamos si tiene abono
                $query = "select b.importe from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.serie_id = 6 and a.factura_asociada_id = $facturacionId and a.anulado = false and b.anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultAbono = $stmt->fetchAll();
                $importeAbono = 0;
                foreach ($resultAbono as $ra) {
                    $importeAbono += abs(round($ra['importe'], 2));
                }

                //Comprobamos si tiene entrada en el balance
                $query = "select importe from balance_economico_entrada where anulado = false and facturacion_id = $facturacionId and tipo = 1";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultEntrada = $stmt->fetchAll();
                $importeEntrada = 0;
                foreach ($resultEntrada as $re) {
                    $importeEntrada += abs(round($re['importe'], 2));
                }

                //Buscamos los conceptos
                $query = "select importe_total as importe from facturacion_lineas_pagos where anulado = false and facturacion_id = $facturacionId";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $conceptosFactura = $stmt->fetchAll();
                $importeFacturas = 0;
                if (count($conceptosFactura) == 0) {
                    $query = "select importe from facturacion_lineas_conceptos where anulado = false and facturacion_id = $facturacionId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $conceptosFactura = $stmt->fetchAll();
                }
                foreach ($conceptosFactura as $cf) {
                    $importeFacturas += round($cf['importe'], 2);
                }

                $tmpImporte = round($importeFacturas, 2) - (round($importeAbono, 2) + $importeEntrada);

                $importeTercerEjercicio += round($tmpImporte, 2);
            }

            $templateProcessor->setValue('IMPORTE_TERCER_TRIMESTRE', number_format(round($importeTercerEjercicio, 2), 2, ',', '.'));

            $query = "select a.id from facturacion a 
            where a.anulado = false
            and a.serie_id = 7
            and a.empresa_id = $empresaId
            and a.fecha between '$year-10-01 00:00:00' and '$year-12-31 23:59:59'";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $facturasCuartoEjercicio = $stmt->fetchAll();
            $importeCuartoEjercicio = 0;
            foreach ($facturasCuartoEjercicio as $fce) {

                $facturacionId = $fce['id'];

                //Comprobamos si tiene abono
                $query = "select b.importe from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.serie_id = 6 and a.factura_asociada_id = $facturacionId and a.anulado = false and b.anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultAbono = $stmt->fetchAll();
                $importeAbono = 0;
                foreach ($resultAbono as $ra) {
                    $importeAbono += abs(round($ra['importe'], 2));
                }

                //Comprobamos si tiene entrada en el balance
                $query = "select importe from balance_economico_entrada where anulado = false and facturacion_id = $facturacionId and tipo = 1";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultEntrada = $stmt->fetchAll();
                $importeEntrada = 0;
                foreach ($resultEntrada as $re) {
                    $importeEntrada += abs(round($re['importe'], 2));
                }

                //Buscamos los conceptos
                $query = "select importe_total as importe from facturacion_lineas_pagos where anulado = false and facturacion_id = $facturacionId";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $conceptosFactura = $stmt->fetchAll();
                $importeFacturas = 0;
                if (count($conceptosFactura) == 0) {
                    $query = "select importe from facturacion_lineas_conceptos where anulado = false and facturacion_id = $facturacionId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $conceptosFactura = $stmt->fetchAll();
                }
                foreach ($conceptosFactura as $cf) {
                    $importeFacturas += round($cf['importe'], 2);
                }

                $tmpImporte = round($importeFacturas, 2) - (round($importeAbono, 2) + $importeEntrada);

                $importeCuartoEjercicio += round($tmpImporte, 2);
            }

            $templateProcessor->setValue('IMPORTE_CUARTO_TRIMESTRE', number_format(round($importeCuartoEjercicio, 2), 2, ',', '.'));

            $query = "select a.id, a.num_fac, to_char(a.fecha, 'DD/MM/YYYY') as fecha from facturacion a 
            where a.anulado = false
            and a.serie_id = 7
            and a.empresa_id = $empresaId
            and a.fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59'
            order by fecha ASC";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $facturasEjercicio = $stmt->fetchAll();
            $importeTotalEjercicio = 0;
            $arrayFacturasEjercicio = array();
            foreach ($facturasEjercicio as $fe) {

                $facturacionId = $fe['id'];

                $item = array();
                $item['FACTURA_NUMERO_EJERCICIO'] = $fe['num_fac'];
                $item['FACTURA_FECHA_EJERCICIO'] = $fe['fecha'];

                //Comprobamos si tiene abono
                $query = "select b.importe, b.iva, b.importe_unidad from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.serie_id = 6 and a.factura_asociada_id = $facturacionId and a.anulado = false and b.anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultAbono = $stmt->fetchAll();
                $importeBaseAbono = 0;
                $importeIvaAbono = 0;
                $importeTotalAbono = 0;
                foreach ($resultAbono as $ra) {
                    $importeBaseAbono += round(abs($ra['importe_unidad']), 2);
                    $importeIvaAbono += round(abs($ra['iva']), 2);
                    $importeTotalAbono += round(abs($ra['importe']), 2);
                }

                //Buscamos los conceptos
                $query = "select importe_total as importe, importe_iva as iva, importe_sin_iva as importe_unidad from facturacion_lineas_pagos where anulado = false and facturacion_id = $facturacionId";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $conceptosFactura = $stmt->fetchAll();
                $importeBase = 0;
                $importeIva = 0;
                $importeTotal = 0;
                if (count($conceptosFactura) == 0) {
                    $query = "select importe, iva, importe_unidad from facturacion_lineas_conceptos where anulado = false and facturacion_id = $facturacionId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $conceptosFactura = $stmt->fetchAll();
                }
                foreach ($conceptosFactura as $cf) {
                    $importeBase += round($cf['importe_unidad'], 2);
                    $importeIva += round($cf['iva'], 2);
                    $importeTotal += round($cf['importe'], 2);
                }

                /*
                //Fix Modelo 347
                $item['FACTURA_BASE_EJERCICIO'] = number_format(round($importeBase - $importeBaseAbono, 2), 2, ',', '.');
                $item['FACTURA_IVA_EJERCICIO'] = number_format(round($importeIva - $importeIvaAbono, 2), 2, ',', '.');
                $item['FACTURA_TOTAL_EJERCICIO'] = number_format(round($importeTotal - $importeTotalAbono, 2), 2, ',', '.');
                */

                $factura_iva_ejercicio = $importeIva - $importeIvaAbono;
                $facutra_total_ejercicio = $importeTotal - $importeTotalAbono;

                $item['FACTURA_IVA_EJERCICIO'] = number_format(round($importeIva - $importeIvaAbono, 2), 2, ',', '.');
                $item['FACTURA_TOTAL_EJERCICIO'] = number_format(round($importeTotal - $importeTotalAbono, 2), 2, ',', '.');
                $item['FACTURA_BASE_EJERCICIO'] = number_format(round($facutra_total_ejercicio - $factura_iva_ejercicio, 2), 2, ',', '.');

                array_push($arrayFacturasEjercicio, $item);

                $importeTotalEjercicio += round($importeTotal - $importeTotalAbono, 2);
            }

            $templateProcessor->setValue('IMPORTE_EJERCICIO', number_format(round($importeTotalEjercicio, 2), 2, ',', '.'));
            $templateProcessor->setValue('IMPORTE_TOTAL_EJERCICIO', number_format(round($importeTotalEjercicio, 2), 2, ',', '.'));
            $templateProcessor->cloneRowAndSetValues('FACTURA_NUMERO_EJERCICIO', $arrayFacturasEjercicio);
        }

        if ($evaluacionSn) {

            $deslocalizado = $empresa->getCentroTrabajoDeslocalizado();

            //Añadimos el logo
            $logo = $empresa->getLogo();
            if (!is_null($logo) && file_exists("upload/media/logos/$logo")) {
                $templateProcessor->setImageValue('EMPRESA_LOGO', array('path' => 'upload/media/logos/' . $logo, 'width' => 300, 'ratio' => false));
            } else {
                $templateProcessor->setValue("EMPRESA_LOGO", null);
            }

            //Añadimos la firma del tecnico
            $templateProcessor->cloneBlock('FIRMAS_TECNICO', count($arrayFirmasTecnicos), true, true);
            $f = 1;
            foreach ($arrayFirmasTecnicos as $arrayFirmaTecnico) {
                if (!is_null($arrayFirmaTecnico)) {
                    if (file_exists("upload/media/firmas/tecnico/$arrayFirmaTecnico")) {
                        $templateProcessor->setImageValue('FIRMA_TECNICO#' . $f, array('path' => 'upload/media/firmas/tecnico/' . $arrayFirmaTecnico, 'width' => 150, 'ratio' => false, 'line' => false));
                        $f++;
                    } else {
                        $templateProcessor->setValue('FIRMA_TECNICO#' . $f, null);
                    }
                }
            }

            //Añadimos los puestos de trabajo en la ficha
            //$templateProcessor->cloneRowAndSetValues('FICHA_NOMBRE_PUESTO_TRABAJO', $arrayPuestosTrabajo);

            //Buscamos los riesgos de cada zona de trabajo
            $query = "select distinct b.id, b.descripcion from zona_trabajo_evaluacion a
                inner join zona_trabajo b on a.zona_trabajo_id = b.id
                inner join evaluacion_centro_trabajo c on a.evaluacion_id = c.evaluacion_id 
                where a.anulado = false
                and a.evaluacion_id = $evaluacionId
                order by b.descripcion ASC";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $zonasTrabajo = $stmt->fetchAll();

            //Clonamos las tablas por cada zona de trabajo a evaluar
            $templateProcessor->cloneBlock('block_zonas_trabajos', count($zonasTrabajo), true, true);

            //Creamos el array para despues reemplazar los nombres por la ruta de la imagen
            $arrayImagenesRiesgosZonas = array();

            $count = 1;
            foreach ($zonasTrabajo as $zt) {
                $zonaTrabajoId =  $zt['id'];

                //Buscamos la actividad del centro
                $query = "select b.direccion from evaluacion_centro_trabajo a
                inner join centro b on a.centro_id = b.id
                where b.anulado = false
                and a.evaluacion_id = $evaluacionId
                order by b.direccion asc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $actividadCentroArray = $stmt->fetchAll();

                $actividadCentro = "";
                if (count($actividadCentroArray) > 0) {
                    if (isset($actividadCentroArray[$count - 1]['direccion'])) {
                        $actividadCentro = $actividadCentroArray[$count - 1]['direccion'];
                    }
                }

                //Si la empresa tiene el centro deslocalizado no generamos la cabecera de las zonas en la plantilla
                if (!$deslocalizado || count($centrosEvaluacion) == 0) {
                    //Generamos la primera tabla con el riesgo
                    $arrayZonaTrabajo = array();
                    $item = array();
                    $item['NUMERO_RIESGO_Z#' . $count] = 'C' . $count;
                    $item['NOMBRE_EMPRESA_Z#' . $count] = $nombre;
                    $item['CENTRO_RIESGO_Z#' . $count] = $direccionCentro;
                    $item['ZONA_TRABAJO_RIESGO#' . $count] = $zt['descripcion'];
                    $item['FECHA_TOMA_DATOS_RIESGO_Z#' . $count] = $evaluacion->getFechaInicio()->format('d/m/Y');
                    $item['TIPO_EVALUACION_RIESGO_Z#' . $count] = $tipoEvaluacionDesc;
                    $item['CENTRO_DESCRIPCION_Z#' . $count] = $actividadCentro;

                    array_push($arrayZonaTrabajo, $item);
                    $templateProcessor->cloneBlock('block_zona_trabajo#' . $count, 1, true, false, $arrayZonaTrabajo);
                }

                //Generamos las tablas con los riesgos-causas
                $arrayRiesgosZonaTrabajo = array();

                $query = "select a.id, b.descripcion as severidad, c.descripcion as probabilidad, d.descripcion as valorriesgo, e.descripcion as causa, f.codigo as riesgocodigo, f.descripcion as riesgo, a.observacion_causa from riesgo_causa_evaluacion a
                        left join severidad b on a.severidad_id = b.id
                        left join probabilidad c on a.probabilidad_id = c.id
                        left join valor_riesgo d on a.valor_riesgo_id = d.id
                        left join causa e on a.causa_id = e.id
                        left join riesgo f on a.riesgo_id = f.id
                        where a.evaluacion_id = $evaluacionId 
                        and a.zona_trabajo_id = $zonaTrabajoId
                        and a.anulado = false
                        order by f.codigo asc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $riesgosCausas = $stmt->fetchAll();

                foreach ($riesgosCausas as $rc) {
                    $item = array();
                    $item['CODIGO_RIESGO_Z#' . $count] = $rc['riesgocodigo'];
                    $item['NOMBRE_RIESGO_Z#' . $count] = $rc['riesgo'];
                    $item['SEVERIDAD_RIESGO_Z#' . $count] = $rc['severidad'];
                    $item['PROBABILIDAD_RIESGO_Z#' . $count] = $rc['probabilidad'];
                    $item['VALOR_RIESGO_Z#' . $count] = strtoupper($rc['valorriesgo']);
                    $item['CAUSA_RIESGO_Z#' . $count] = $rc['causa'];
                    $item['OBSERVACION_CAUSA_RIESGO_Z#' . $count] = $rc['observacion_causa'];

                    $riesgoCausaId = $rc['id'];

                    //Buscamos la planificacion
                    $query = "select b.descripcion as tipoplanificacion, case when substring(b.descripcion, 1,1) = 'P' then 'CONTINUO' ELSE to_char(a.fecha_prevista, 'DD/MM/YYYY') END as fechaprevista, to_char(a.fecha_realizacion , 'DD/MM/YYYY') as fecharealizacion, a.responsable, a.trabajadores from planificacion_riesgo_causa a
                        left join tipo_planificacion b on a.tipo_planificacion_id = b.id
                        where a.riesgo_causa_id = $riesgoCausaId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $planificacionRiesgosCausas = $stmt->fetchAll();

                    if (count($planificacionRiesgosCausas) > 0) {
                        $item['T_P_Z#' . $count] = $planificacionRiesgosCausas[0]['tipoplanificacion'];
                        $item['FECHA_PREVISION_Z#' . $count] = $planificacionRiesgosCausas[0]['fechaprevista'];
                        $item['FECHA_REALIZACION_Z#' . $count] = $planificacionRiesgosCausas[0]['fecharealizacion'];

                        if ($planificacionRiesgosCausas[0]['trabajadores']) {
                            $item['RESPONSABLE_EMPRESA_Z#' . $count] = strtoupper($planificacionRiesgosCausas[0]['responsable']) . ' - ' . 'Trabajadores/as';
                        } else {
                            $item['RESPONSABLE_EMPRESA_Z#' . $count] = strtoupper($planificacionRiesgosCausas[0]['responsable']);
                        }
                    } else {
                        $item['T_P_Z#' . $count] = '';
                        $item['FECHA_PREVISION_Z#' . $count] = '';
                        $item['FECHA_REALIZACION_Z#' . $count] = '';
                        $item['RESPONSABLE_EMPRESA_Z#' . $count] = '';
                    }

                    //Buscamos las medidas preventivas
                    $query = "select descripcion from accion_preventiva_empresa_riesgo_causa
                    where anulado = false
                    and riesgo_causa_id = $riesgoCausaId
                    order by descripcion asc";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $medidasPreventivas = $stmt->fetchAll();
                    $medidasPreventivasRiesgoCausa = "</w:t>\n<w:t xml:space=\"preserve\"><w:rPr><w:b w:val=\"0\" /></w:rPr>";
                    $countMedidasPreventivas = 1;
                    // Agrega dos espacios a la primera línea
                    // Agrega dos espacios a la primera línea
                    $medidasPreventivasRiesgoCausa .= "       \n•    " . wordwrap($medidasPreventivas[0]['descripcion'], 133, "\n    ", true) . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\"><w:rPr><w:b w:val=\"0\" /></w:rPr>";

                    for ($i = 1; $i < count($medidasPreventivas); $i++) {
                        // Concatena las descripciones de las líneas siguientes al fragmento existente
                        $medidasPreventivasRiesgoCausa .= "\n•    " . wordwrap($medidasPreventivas[$i]['descripcion'], 133, "\n    ", true) . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\"><w:rPr><w:b w:val=\"0\" /></w:rPr>";
                        $countMedidasPreventivas++;
                    }
                    $item['MEDIDAS_PREVENTIVAS_Z#' . $count] = $medidasPreventivasRiesgoCausa;


                    //Buscamos si los riesgos tienes imagenes
                    $query = "select nombre from riesgo_causa_img where anulado = false and riesgo_causa_id = $riesgoCausaId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $imagenesRiesgos = $stmt->fetchAll();

                    $contadorImagenesRiesgos = count($imagenesRiesgos);
                    if ($contadorImagenesRiesgos > 0) {
                        $listImagenes = "";
                        foreach ($imagenesRiesgos as $imgRiesgos) {
                            $nombreImagen = $imgRiesgos['nombre'];
                            array_push($arrayImagenesRiesgosZonas, $nombreImagen);
                            $listImagenes .= '${' . $nombreImagen . '}' . "    ";
                            $item['IMAGENES_RIESGOS_Z#' . $count] = $listImagenes;
                        }
                    } else {
                        $item['IMAGENES_RIESGOS_Z#' . $count] = '';
                    }

                    array_push($arrayRiesgosZonaTrabajo, $item);
                }

                //Clonamos los riesgos del puesto de trabajo
                $templateProcessor->cloneBlock('block_riesgos_zonas#' . $count, count($arrayRiesgosZonaTrabajo), true, false, $arrayRiesgosZonaTrabajo);

                $count++;
            }

            //Peticio 01/09/2023 Mejora continua #67377 - Incorporar maquinaria en sección evaluación
            //Buscamos los riesgos de cada puesto de trabajo
            if ($puestosTrabajoSeleccionadosSelect2 === 0) {
            } else {
                $cadena = implode(',', $puestosTrabajoSeleccionadosSelect2);
            }
            if ($puestosTrabajoSeleccionadosSelect2[0] === 0 || $puestosTrabajoSeleccionadosSelect2 === 0) {
                $query = "select b.id, b.descripcion, a.tarea,
                (select string_agg(distinct me.descripcion ::text, ' , '::text) from maquina_empresa_trabajador met inner join maquina_empresa me on met.maquina_empresa_id = me.id where a.puesto_trabajo_id = met.puesto_trabajo_id and met.anulado = false) as maquina
                from puesto_trabajo_evaluacion a
                inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                where a.anulado = false
                and a.evaluacion_id = $evaluacionId
                order by b.descripcion ASC";
            } else {
                $query = "select b.id, b.descripcion, a.tarea,
                (select string_agg(distinct me.descripcion ::text, ' , '::text) from maquina_empresa_trabajador met inner join maquina_empresa me on met.maquina_empresa_id = me.id where a.puesto_trabajo_id = met.puesto_trabajo_id and met.anulado = false) as maquina
                from puesto_trabajo_evaluacion a
                inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                where a.anulado = false and a.id IN($cadena)
                and a.evaluacion_id = $evaluacionId
                order by b.descripcion ASC";
            }

            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $puestosTrabajos = $stmt->fetchAll();

            //Clonamos las tablas por cada puesto de trabajo a evaluar
            $templateProcessor->cloneBlock('block_puestos_trabajos', count($puestosTrabajos), true, true);

            //Creamos el array para despues reemplazar los nombres por la ruta de la imagen
            $arrayImagenesRiesgos = array();

            $count = 1;
            foreach ($puestosTrabajos as $pt) {
                $puestoTrabajoId =  $pt['id'];

                //Generamos la primera tabla con el riesgo
                $arrayPuestoTrabajo = array();
                $item = array();
                $item['NUMERO_RIESGO#' . $count] = $count;
                $item['NOMBRE_EMPRESA#' . $count] = $nombre;
                $item['CENTRO_RIESGO#' . $count] = $direccionCentro;
                $item['PUESTO_TRABAJO_RIESGO#' . $count] = $pt['descripcion'];
                $item['FECHA_TOMA_DATOS_RIESGO#' . $count] = $evaluacion->getFechaInicio()->format('d/m/Y');
                $item['TIPO_EVALUACION_RIESGO#' . $count] = $tipoEvaluacionDesc;
                $item['PUESTO_TRABAJO_TAREA#' . $count] = $pt['tarea'];
                //Peticio 01/09/2023 Mejora continua #67377 - Incorporar maquinaria en sección evaluación
                if ($pt['maquina'] == null) {
                    $item['PUESTO_TRABAJO_MAQUINARIA#' . $count] = " ";
                } else {
                    $item['PUESTO_TRABAJO_MAQUINARIA#' . $count] = $pt['maquina'];
                }

                if ($pt['quimico'] == null) {
                    $item['PUESTO_TRABAJO_QUIMICO#' . $count] = " ";
                } else {
                    $item['PUESTO_TRABAJO_QUIMICO#' . $count] = $pt['quimico'];
                }
                array_push($arrayPuestoTrabajo, $item);

                //Clonamos la información del puesto de trabajo
                $templateProcessor->cloneBlock('block_puesto_trabajo#' . $count, 1, true, false, $arrayPuestoTrabajo);

                //Generamos las tablas con los riesgos-causas
                $arrayRiesgosPuestoTrabajo = array();

                $query = "select a.id, b.descripcion as severidad, c.descripcion as probabilidad, d.descripcion as valorriesgo, e.descripcion as causa, f.codigo as riesgocodigo, f.descripcion as riesgo, a.observacion_causa from riesgo_causa_evaluacion a
                        left join severidad b on a.severidad_id = b.id
                        left join probabilidad c on a.probabilidad_id = c.id
                        left join valor_riesgo d on a.valor_riesgo_id = d.id
                        left join causa e on a.causa_id = e.id
                        left join riesgo f on a.riesgo_id = f.id
                        where a.evaluacion_id = $evaluacionId 
                        and a.puesto_trabajo_id = $puestoTrabajoId
                        and a.anulado = false
                        order by f.codigo asc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $riesgosCausas = $stmt->fetchAll();

                foreach ($riesgosCausas as $rc) {
                    $item = array();
                    $item['CODIGO_RIESGO#' . $count] = $rc['riesgocodigo'];
                    $item['NOMBRE_RIESGO#' . $count] = $rc['riesgo'];
                    $item['SEVERIDAD_RIESGO#' . $count] = $rc['severidad'];
                    $item['PROBABILIDAD_RIESGO#' . $count] = $rc['probabilidad'];
                    $item['VALOR_RIESGO#' . $count] = strtoupper($rc['valorriesgo']);
                    $item['CAUSA_RIESGO#' . $count] = $rc['causa'];
                    $item['OBSERVACION_CAUSA_RIESGO#' . $count] = $rc['observacion_causa'];

                    $riesgoCausaId = $rc['id'];

                    //Buscamos la planificacion
                    $query = "select b.descripcion as tipoplanificacion, case when substring(b.descripcion, 1,1) = 'P' then 'CONTINUO' ELSE to_char(a.fecha_prevista, 'DD/MM/YYYY') END as fechaprevista, case when substring(b.descripcion, 1,1) = 'P' then 'PERIODICAMENTE' ELSE to_char(a.fecha_realizacion, 'DD/MM/YYYY') END as fecharealizacion, a.responsable, a.trabajadores from planificacion_riesgo_causa a
                        left join tipo_planificacion b on a.tipo_planificacion_id = b.id
                        where a.riesgo_causa_id = $riesgoCausaId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $planificacionRiesgosCausas = $stmt->fetchAll();

                    if (count($planificacionRiesgosCausas) > 0) {
                        $item['TIPO_PLANIFICACION#' . $count] = $planificacionRiesgosCausas[0]['tipoplanificacion'];
                        $item['FECHA_PREVISION#' . $count] = $planificacionRiesgosCausas[0]['fechaprevista'];
                        $item['FECHA_REALIZACION#' . $count] = $planificacionRiesgosCausas[0]['fecharealizacion'];

                        if ($planificacionRiesgosCausas[0]['trabajadores']) {
                            $item['RESPONSABLE_EMPRESA#' . $count] = strtoupper($planificacionRiesgosCausas[0]['responsable']) . ' - ' . 'Trabajadores/as';
                        } else {
                            $item['RESPONSABLE_EMPRESA#' . $count] = strtoupper($planificacionRiesgosCausas[0]['responsable']);
                        }
                    } else {
                        $item['TIPO_PLANIFICACION#' . $count] = '';
                        $item['FECHA_PREVISION#' . $count] = '';
                        $item['FECHA_REALIZACION#' . $count] = '';
                        $item['RESPONSABLE_EMPRESA#' . $count] = '';
                    }

                    //Buscamos las medidas preventivas
                    $query = "select descripcion from accion_preventiva_empresa_riesgo_causa
                    where anulado = false
                    and riesgo_causa_id = $riesgoCausaId
                    order by descripcion asc";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $medidasPreventivas = $stmt->fetchAll();
                    $medidasPreventivasRiesgoCausa = "</w:t>\n<w:t xml:space=\"preserve\"><w:rPr><w:b w:val=\"0\" /></w:rPr>";
                    $countMedidasPreventivas = 1;
                    // Agrega dos espacios a la primera línea
                    $medidasPreventivasRiesgoCausa .= "       \n•    " . wordwrap($medidasPreventivas[0]['descripcion'], 133, "\n    ", true) . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\"><w:rPr><w:b w:val=\"0\" /></w:rPr>";

                    for ($i = 1; $i < count($medidasPreventivas); $i++) {
                        // Concatena las descripciones de las líneas siguientes al fragmento existente
                        $medidasPreventivasRiesgoCausa .= " \n•    " . wordwrap($medidasPreventivas[$i]['descripcion'], 133, "\n    ", true) . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\"><w:rPr><w:b w:val=\"0\" /></w:rPr>";
                        $countMedidasPreventivas++;
                    }
                    $item['MEDIDAS_PREVENTIVAS#' . $count] = $medidasPreventivasRiesgoCausa;


                    //Buscamos si los riesgos tienes imagenes
                    $query = "select nombre from riesgo_causa_img where anulado = false and riesgo_causa_id = $riesgoCausaId";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $imagenesRiesgos = $stmt->fetchAll();

                    $contadorImagenesRiesgos = count($imagenesRiesgos);
                    if ($contadorImagenesRiesgos > 0) {
                        $listImagenes = "";
                        foreach ($imagenesRiesgos as $imgRiesgos) {
                            $nombreImagen = $imgRiesgos['nombre'];
                            array_push($arrayImagenesRiesgos, $nombreImagen);
                            $listImagenes .= '${' . $nombreImagen . '}' . "    ";
                            $item['IMAGENES_RIESGOS#' . $count] = $listImagenes;
                        }
                    } else {
                        $item['IMAGENES_RIESGOS#' . $count] = '';
                    }

                    array_push($arrayRiesgosPuestoTrabajo, $item);
                }

                //Clonamos los riesgos del puesto de trabajo
                $templateProcessor->cloneBlock('block_riesgos#' . $count, count($arrayRiesgosPuestoTrabajo), true, false, $arrayRiesgosPuestoTrabajo);

                $count++;
            }

            if (!$dosCentrosSn && !$multiCentroSn) {
                //Buscamos la lista de los trabajadores con sus puestos de trabajo
                if (is_null($evaluacionCentro1Id)) {
                    $query = "select distinct e.nombre, e.dni, c.descripcion as puestotrabajo from puesto_trabajo_evaluacion a
                    left join puesto_trabajo_trabajador b on a.puesto_trabajo_id = b.puesto_trabajo_id
                    left join puesto_trabajo_centro c on b.puesto_trabajo_id = c.id
                    left join trabajador e on b.trabajador_id = e.id
                    where a.anulado = false
                    and a.evaluacion_id = $evaluacionId
                    order by e.nombre, e.dni, c.descripcion asc";
                } else {
                    $query = "select distinct e.nombre, e.dni, c.descripcion as puestotrabajo from puesto_trabajo_evaluacion a
                    inner join puesto_trabajo_trabajador b on a.puesto_trabajo_id = b.puesto_trabajo_id
                    inner join puesto_trabajo_centro c on b.puesto_trabajo_id = c.id
                    inner join trabajador e on b.trabajador_id = e.id
                    inner join trabajador_alta_baja f on e.id = f.trabajador_id 
                    where a.anulado = false
                    and a.evaluacion_id = $evaluacionId
                    and b.anulado = false
                    and c.anulado = false
                    and e.anulado = false
                    and c.descripcion not like '%GENERAL A TODOS LOS PUESTOS DE TRABAJO%'
                    and f.empresa_id = $empresaId
                    and f.anulado = false
                    and f.activo = true
                    and f.fecha_baja is null
                    order by e.nombre, e.dni, c.descripcion asc";
                }
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $trabajadores = $stmt->fetchAll();
                $arrayTrabajadoresPuestoTrabajo = array();
                foreach ($trabajadores as $t) {
                    $item = array();
                    $item['NOMBRE_TRABAJADOR'] = $t['nombre'];
                    $item['DNI_TRABAJADOR'] = $t['dni'];
                    $item['PUESTO_TRABAJO_TRABAJADOR'] = $t['puestotrabajo'];
                    array_push($arrayTrabajadoresPuestoTrabajo, $item);
                }
                //Clonamos la lista de trabajadores
                //$templateProcessor->cloneRowAndSetValues('NOMBRE_TRABAJADOR', $arrayTrabajadoresPuestoTrabajo);
            }

            //Si se trata de una evaluación con mas de un centro se añade la lista de trabajadores del otro centro
            if ($dosCentrosSn) {
                $query = "select distinct e.nombre, e.dni, c.descripcion as puestotrabajo from puesto_trabajo_evaluacion a
                inner join puesto_trabajo_trabajador b on a.puesto_trabajo_id = b.puesto_trabajo_id
                inner join puesto_trabajo_centro c on b.puesto_trabajo_id = c.id
                inner join trabajador e on b.trabajador_id = e.id
                inner join trabajador_alta_baja f on e.id = f.trabajador_id 
                where a.anulado = false
                and a.evaluacion_id = $evaluacionId
                and b.anulado = false
                and c.anulado = false
                and e.anulado = false
                and c.descripcion not like '%GENERAL A TODOS LOS PUESTOS DE TRABAJO%'
                and b.centro_id = $evaluacionCentro2Id
                and f.empresa_id = $empresaId
                and f.anulado = false
                and f.activo = true
                and f.fecha_baja is null
                order by e.nombre, e.dni, c.descripcion  asc";

                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $trabajadores = $stmt->fetchAll();
                $arrayTrabajadoresPuestoTrabajo2 = array();

                foreach ($trabajadores as $t) {
                    $item = array();
                    $item['NOMBRE_TRABAJADOR2'] = $t['nombre'];
                    $item['DNI_TRABAJADOR2'] = $t['dni'];
                    $item['PUESTO_TRABAJO_TRABAJADOR2'] = $t['puestotrabajo'];
                    array_push($arrayTrabajadoresPuestoTrabajo2, $item);
                }
                $templateProcessor->cloneRowAndSetValues('NOMBRE_TRABAJADOR2', $arrayTrabajadoresPuestoTrabajo2);
            }

            if ($multiCentroSn) {
                $templateProcessor->cloneBlock('block_centro_trabajo', count($arrayCentroTrabajoEvaluacion), true, true);

                $count = 1;
                foreach ($arrayCentroTrabajoEvaluacion as $act) {
                    $templateProcessor->setValue('CENTRO_TEXTO#' . $count, $act['CENTRO_TEXTO#' . $count]);
                    $templateProcessor->setValue('CENTRO_DIRECCION_MULTI#' . $count, $act['CENTRO_DIRECCION#' . $count]);
                    $templateProcessor->setValue('CENTRO_LOCALIDAD_MULTI#' . $count, $act['CENTRO_LOCALIDAD#' . $count]);
                    $templateProcessor->setValue('CENTRO_PROVINCIA_MULTI#' . $count, $act['CENTRO_PROVINCIA#' . $count]);
                    $templateProcessor->setValue('CENTRO_TELEFONO_MULTI#' . $count, $act['CENTRO_TELEFONO#' . $count]);
                    $templateProcessor->setValue('CENTRO_DESCRIPCION_MULTI#' . $count, $act['CENTRO_DESCRIPCION#' . $count]);
                    $count++;
                }
                $templateProcessor->cloneBlock('block_trabajador_empresa', count($arrayIdCentroTrabajoEvaluacion), true, true);
                $count = 1;

                foreach ($arrayIdCentroTrabajoEvaluacion as $acte) {
                    $query = "select distinct e.nombre, e.dni, c.descripcion as puestotrabajo from puesto_trabajo_evaluacion a
                    inner join puesto_trabajo_trabajador b on a.puesto_trabajo_id = b.puesto_trabajo_id
                    inner join puesto_trabajo_centro c on b.puesto_trabajo_id = c.id
                    inner join trabajador e on b.trabajador_id = e.id
                    inner join trabajador_alta_baja f on e.id = f.trabajador_id 
                    where a.anulado = false
                    and a.evaluacion_id = $evaluacionId
                    and b.anulado = false
                    and c.anulado = false
                    and e.anulado = false
                    and c.descripcion not like '%GENERAL A TODOS LOS PUESTOS DE TRABAJO%'
                    and b.centro_id = $acte
                    and f.empresa_id = $empresaId
                    and f.anulado = false
                    and f.activo = true
                    and f.fecha_baja is null
                    order by e.nombre, e.dni, c.descripcion asc";

                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $trabajadores = $stmt->fetchAll();
                    $arrayTrabajadoresPuestoTrabajoMulti = array();

                    foreach ($trabajadores as $t) {
                        $item = array();
                        $item['NOMBRE_TRABAJADOR_MULTI#' . $count] = $t['nombre'];
                        $item['DNI_TRABAJADOR_MULTI#' . $count] = $t['dni'];
                        $item['PUESTO_TRABAJO_TRABAJADOR_MULTI#' . $count] = $t['puestotrabajo'];
                        array_push($arrayTrabajadoresPuestoTrabajoMulti, $item);
                    }
                    $centroTrabajoObj = $em->getRepository('App\Entity\Centro')->find($acte);
                    $templateProcessor->setValue("EMPRESA_NOMBRE_MULTI#" . $count, $empresa->getEmpresa());
                    $templateProcessor->setValue("FECHA_MULTI#" . $count, $hoyString);
                    $templateProcessor->setValue("CENTRO_DIRECCION_MULTI#" . $count, $centroTrabajoObj->getDireccion());

                    if (count($arrayTrabajadoresPuestoTrabajoMulti) == 0) {
                        $item = array();
                        $item['NOMBRE_TRABAJADOR_MULTI#' . $count] = '';
                        $item['DNI_TRABAJADOR_MULTI#' . $count] = '';
                        $item['PUESTO_TRABAJO_TRABAJADOR_MULTI#' . $count] = '';
                        array_push($arrayTrabajadoresPuestoTrabajoMulti, $item);
                    }
                    $templateProcessor->cloneRowAndSetValues('NOMBRE_TRABAJADOR_MULTI#' . $count, $arrayTrabajadoresPuestoTrabajoMulti);
                    $count++;
                }
                //$templateProcessor->cloneBlock('EMPRESA_NOMBRE_MULTI#'.$count, count($arrayTrabajadoresPuestoTrabajoMulti), true, false, $arrayTrabajadoresPuestoTrabajoMulti);

                //Clonamos los centros evaluados e insertamos los valores
                $templateProcessor->cloneBlock('block_centros_evaluados', count($arrayCentroTrabajoEvaluacion), true, true);
                $count = 1;
                foreach ($arrayCentroTrabajoEvaluacion as $act) {
                    $templateProcessor->setValue('CENTRO_EVALUADO_FICHA#' . $count, 'C' . $count);
                    $templateProcessor->setValue('CENTRO_EVALUADO_DIRECCION#' . $count, $act['CENTRO_DIRECCION#' . $count]);
                    $count++;
                }
            }
            //Buscamos la maquinaria de la empresa
            $query = "select distinct descripcion as equipo, placa_caracteristica, fabricante, modelo, num_serie, anyo_fabricacion, marcado_ce, conformidad, manual_instrucciones from maquina_empresa 
                    where anulado = false 
                    and empresa_id = $empresaId
                    order by descripcion ASC";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $maquinas = $stmt->fetchAll();
            $arrayMaquinariaEmpresa = array();

            foreach ($maquinas as $m) {
                $item = array();
                $item['EQUIPO_MAQUINA'] = $m['equipo'];
                $item['PLACA_MAQUINA'] = $m['placa_caracteristica'];
                $item['FABRICANTE_MAQUINA'] = $m['fabricante'];
                $item['MODELO_MAQUINA'] = $m['modelo'];
                $item['NUM_SERIE_MAQUINA'] = $m['num_serie'];
                $item['AÑO_FABRICACION_MAQUINA'] = $m['anyo_fabricacion'];
                $item['CE_MAQUINA'] = $m['marcado_ce'];

                if ($m['conformidad'] == true) {
                    $item['CONFORMIDAD_MAQUINA'] = 'SI';
                } else {
                    $item['CONFORMIDAD_MAQUINA'] = 'NO';
                }
                if ($m['manual_instrucciones'] == true) {
                    $item['MANUAL_MAQUINA'] = 'SI';
                } else {
                    $item['MANUAL_MAQUINA'] = 'NO';
                }
                array_push($arrayMaquinariaEmpresa, $item);
            }
            //$templateProcessor->cloneRowAndSetValues('EQUIPO_MAQUINA', $arrayMaquinariaEmpresa);

            //Recorremos el array de imagenes añadir las imagenes de las zonas de trabajo
            foreach ($arrayImagenesRiesgosZonas as $arrayImagenRiesgoZona) {
                $nombreImagen = str_replace('${', '', $arrayImagenRiesgoZona);
                $nombreImagen = str_replace('}', '', $nombreImagen);
                if (file_exists("upload/media/evaluaciones/causas/$nombreImagen")) {
                    $templateProcessor->setImageValue($arrayImagenRiesgoZona, 'upload/media/evaluaciones/causas/' . $nombreImagen);
                } else {
                    $templateProcessor->setValue($arrayImagenRiesgoZona, null);
                }
            }
            //Recorremos el array de imagenes añadir las imagenes de los puestos de trabajo
            foreach ($arrayImagenesRiesgos as $arrayImagenRiesgo) {
                $nombreImagen = str_replace('${', '', $arrayImagenRiesgo);
                $nombreImagen = str_replace('}', '', $nombreImagen);
                if (file_exists("upload/media/evaluaciones/causas/$nombreImagen")) {
                    $templateProcessor->setImageValue($arrayImagenRiesgo, 'upload/media/evaluaciones/causas/' . $nombreImagen);
                } else {
                    $templateProcessor->setValue($arrayImagenRiesgo, null);
                }
            }
        }

        if ($citacionSn) {
            $fechaCitacion = $citacion->getFechainicio()->format('d') . ' de ' . $this->obtenerMes($citacion->getFechainicio()->format('m')) . ' de ' . $citacion->getFechainicio()->format('Y') . ' - ' . $citacion->getFechaInicio()->format('H:i');
            $fechaCitacionSinHora = $citacion->getFechainicio()->format('d') . ' de ' . $this->obtenerMes($citacion->getFechainicio()->format('m')) . ' de ' . $citacion->getFechainicio()->format('Y');

            $templateProcessor->setValue("CITACION_FECHA", $fechaCitacion);
            $templateProcessor->setValue("CITACION_FECHA_SIN_HORA", $fechaCitacionSinHora);
            $templateProcessor->setValue("CITACION_DIRECCION", $citacion->getAgenda()->getDireccion());

            if (!is_null($citacion->getTrabajador())) {
                $nombreTrabajadorCitacion = $citacion->getTrabajador()->getNombre();
                $dniTrabajadorCitacion = $citacion->getTrabajador()->getDni();
                $templateProcessor->setValue("CITACION_TRABAJADOR_NOMBRE", $nombreTrabajadorCitacion);
                $templateProcessor->setValue("CITACION_TRABAJADOR_DNI", $dniTrabajadorCitacion);
            }
        }

        if ($planPrevencionSn) {
            $fechaPlanPrevencion = $hoy->format('d') . ' / ' . $this->obtenerMes($hoy->format('m')) . ' / ' . $hoy->format('Y');
            $templateProcessor->setValue("FECHA_PLAN_PREVENCION", $fechaPlanPrevencion);

            //Buscamos el tecnico de la EMPRESA y la firma
            $tecnicosEmpresa = $em->getRepository('App\Entity\TecnicoEmpresa')->findOneBy(array('empresa' => $empresa, 'anulado' => false));
            $tecnicoEmpresa = null;
            $tecnicoEmpresaCorreo = null;
            if (!is_null($tecnicosEmpresa)) {
                $tecnicoEmpresa = $tecnicosEmpresa->getTecnico()->getNombre();
                $tecnicoEmpresaCorreo = $tecnicosEmpresa->getTecnico()->getCorreo();
            }
            $templateProcessor->setValue("TECNICO_EMPRESA", $tecnicoEmpresa);
            $templateProcessor->setValue("TECNICO_EMPRESA_CORREO", $tecnicoEmpresaCorreo);

            //Buscamos si la empresa tiene algun plan de prevencion previo
            $planPrevencionPrevio = $em->getRepository('App\Entity\EmpresaPlanPrevencion')->findBy(array('empresa' => $empresa));
            if (count($planPrevencionPrevio) == 0) {
                $templateProcessor->setValue("PRL_TIPO", 'I');
            } else {
                $templateProcessor->setValue("PRL_TIPO", 'R');
            }
            $templateProcessor->setValue("PRL_CONTRATO", $contratoNumero);

            //Petició 28/07/2023 #63749
            $queryAux3 = "SELECT COUNT(*) AS total_registros
            FROM (
                SELECT (SELECT numero
                        FROM (
                            SELECT id, empresa_id, row_number() OVER (ORDER BY fecha ASC) AS numero
                            FROM empresa_plan_prevencion
                            WHERE empresa_id = a.empresa_id
                            ORDER BY fecha ASC
                        ) consulta
                        WHERE id = a.id) AS numero
                FROM empresa_plan_prevencion a
                WHERE a.empresa_id = $empresaId
            ) subconsulta";
            $stmt3 = $this->getDoctrine()->getManager()->getConnection()->prepare($queryAux3);
            $stmt3->execute();
            $numPlanPre = $stmt3->fetchAll();
            $numPlanPre2 = 1 + $numPlanPre[0]['total_registros'];

            $templateProcessor->setValue("NUM_PLAN_PRE", $numPlanPre2);

            //Buscamos las tarifas por RM
            $query = "select importe from tarifa_revision_medica where anulado = false and empresa_id = $empresaId ";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $importeRevisionMedica = $stmt->fetchAll();
            if (count($importeRevisionMedica) > 0) {
                $templateProcessor->setValue("PRL_IMPORTE_RM", number_format(round($importeRevisionMedica[0]['importe'], 2), 2, ',', '.'));
            } else {
                $templateProcessor->setValue("PRL_IMPORTE_RM", '0,00');
            }
            $templateProcessor->setValue("PRL_IMPORTE_CONTRATO", number_format(round($importePrevencion, 2), 2, ',', '.'));
        }

        if ($revisionSn) {
            $revisionId = $revision->getId();

            if (!is_null($revision->getFecha())) {
                $fechaRevision = $revision->getFecha()->format('d') . ' / ' . $revision->getFecha()->format('m') . ' / ' . $revision->getFecha()->format('Y');
            }
            $templateProcessor->setValue("FECHA_REVISION", $fechaRevision);
            $templateProcessor->setValue("REVISION_PUESTO_TRABAJO", $revision->getPuestoTrabajo()->getDescripcion());

            if (!is_null($revision->getFechaCertificacion())) {
                $fechaAptitud = $revision->getFechaCertificacion()->format('d') . ' / ' . $revision->getFechaCertificacion()->format('m') . ' / ' . $revision->getFechaCertificacion()->format('Y');
            }
            $templateProcessor->setValue("FECHA_APTITUD", $fechaAptitud);

            if (!is_null($revision->getValidez())) {
                $templateProcessor->setValue("VALIDEZ", $revision->getValidez()->getDescripcion());
            } else {
                $templateProcessor->setValue("VALIDEZ", '');
            }
            $query = "select respuesta from revision_respuesta where revision_id = $revisionId and pregunta_id = 280";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $tipoReconocimientoResult = $stmt->fetchAll();

            if (count($tipoReconocimientoResult) > 0) {
                $templateProcessor->setValue("TIPO_RECONOCIMIENTO", $tipoReconocimientoResult[0]['respuesta']);
            } else {
                $templateProcessor->setValue("TIPO_RECONOCIMIENTO", '');
            }
            if (!is_null($revision->getApto())) {
                $aptitudRevisionId = $revision->getApto()->getId();
                switch ($aptitudRevisionId) {
                    case 1:
                        if ($idiomaPlantilla == 'ESP') {
                            $aptitudRevision = 'APTO';
                        } else {
                            $aptitudRevision = 'APTE';
                        }
                        break;
                    case 2:
                        $aptitudRevision = $revision->getApto()->getDescripcion();
                        if (!is_null($revision->getAptitudRestriccion())) {
                            $aptitudRestriccion = ' - ' . $revision->getAptitudRestriccion()->getDescripcion();
                        }
                        break;
                    case 3:
                        if ($idiomaPlantilla == 'ESP') {
                            $aptitudRevision = 'APTE';
                        } else {
                            $aptitudRevision = 'NO APTE';
                        }
                        break;
                }
            }
            $templateProcessor->setValue("APTITUD_REVISION", $aptitudRevision);
            $templateProcessor->setValue("APTITUD_RESTRICCION_TIPO", $aptitudRestriccion);

            $firmaMedico = "";
            $colegiadoMedico = "";
            $especialidadMedico = "";
            $gestoraMedico = "";
            if (!is_null($revision->getMedico())) {
                $doctorRevision = $revision->getMedico()->getDescripcion();
                $firmaMedico = $revision->getMedico()->getFirma();
                $colegiadoMedico = $revision->getMedico()->getNumeroColegiado();
                $especialidadMedico = $revision->getMedico()->getEspecialidad();
                $gestoraMedico = $revision->getMedico()->getGestora();
            }
            $templateProcessor->setValue("DOCTOR_REVISION", $doctorRevision);

            if ($firmaMedico != "") {
                $templateProcessor->setImageValue('FIRMA_DOCTOR_REVISION', 'upload/media/firmas/medico/' . $firmaMedico);
            } else {
                $templateProcessor->setValue('FIRMA_DOCTOR_REVISION', null);
            }
            $templateProcessor->setValue('COLEGIADO_DOCTOR_REVISION', $colegiadoMedico);
            $templateProcessor->setValue('ESPECIALIDAD_DOCTOR_REVISION', $especialidadMedico);
            $templateProcessor->setValue('GESTORA_DOCTOR_REVISION', $gestoraMedico);

            if (!is_null($revision->getPuestoTrabajo())) {
                $revisionId = $revision->getId();

                $query = "select distinct c.descripcion as protocolo, c.descripcion_ca as protocolo_ca from revision a
                inner join puesto_trabajo_protocolo b on a.puesto_trabajo_id = b.puesto_trabajo_id 
                inner join protocolo c on b.protocolo_id = c.id
                where a.id = $revisionId
                and b.empresa_id = $empresaId
                order by c.descripcion asc";

                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $protocolosRevisionArray = $stmt->fetchAll();
                $protocolosRevision1 = null;
                $protocolosRevision2 = null;
                $countProtocolos = 0;

                foreach ($protocolosRevisionArray as $pra) {
                    if ($idiomaPlantilla == 'ESP') {
                        $protocoloDesc = $pra['protocolo'];
                    } else {
                        $protocoloDesc = $pra['protocolo_ca'];
                    }
                    if ($countProtocolos > 12) {
                        $protocolosRevision2 .= $protocoloDesc . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                    } else {
                        $protocolosRevision1 .= $protocoloDesc . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                    }
                    $countProtocolos++;
                }
            }
            $templateProcessor->setValue('PROTOCOLOS_REVISION_1', $protocolosRevision1);
            $templateProcessor->setValue('PROTOCOLOS_REVISION_2', $protocolosRevision2);
        }

        if ($fichaRiesgosSn) {

            //Buscamos el nombre del puesto de trabajo
            $puestoTrabajoFicha = $puestoTrabajoEvaluacion->getPuestoTrabajo();
            $templateProcessor->setValue('PUESTO_TRABAJO_FICHA_RIESGOS', $puestoTrabajoFicha->getDescripcion());

            //Buscamos los riesgos, sus causa y sus medidas preventivas
            $evaluacion = $puestoTrabajoEvaluacion->getEvaluacion();
            $puestoTrabajoId = $puestoTrabajoFicha->getId();
            $evaluacionId = $evaluacion->getId();

            $query = "select distinct a.id, a.riesgo_id, b.descripcion as riesgo, b.codigo, c.descripcion as causa from riesgo_causa_evaluacion a
            inner join riesgo b on a.riesgo_id = b.id
            inner join causa c on a.causa_id = c.id
            where a.evaluacion_id = $evaluacionId 
            and a.puesto_trabajo_id = $puestoTrabajoId 
            and a.anulado = false
            order by b.codigo ASC";

            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $riesgosArray = $stmt->fetchAll();

            $arrayRiesgos = array();
            foreach ($riesgosArray as $ra) {
                $item = array();
                $item['CODIGO_RIESGO_FICHA_RIESGOS'] = $ra['codigo'];
                $item['RIESGO_FICHA_RIESGOS'] = $ra['riesgo'];
                $item['CAUSA_RIESGO_FICHA_RIESGOS'] = $ra['causa'];

                $riesgoCausaId = $ra['id'];
                //Buscamos las medidas preventivas para el puesto de trabajo
                $query = "select b.descripcion from accion_preventiva_trabajador_riesgo_causa a
                inner join preventiva_trabajador b on a.preventiva_trabajador_id = b.id
                where a.anulado = false
                and a.riesgo_causa_id = $riesgoCausaId";

                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $medidasPreventivasArray = $stmt->fetchAll();

                $medidasPreventivas = "";
                foreach ($medidasPreventivasArray as $mda) {
                    $medidasPreventivas .= " - " . $mda['descripcion'] . ".</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                }
                $item['MEDIDAS_RIESGO_FICHA_RIESGOS'] = $medidasPreventivas;
                array_push($arrayRiesgos, $item);
            }
            $templateProcessor->cloneRowAndSetValues('CODIGO_RIESGO_FICHA_RIESGOS', $arrayRiesgos);
        }

        if ($resumenRevisionSn) {
            if (!is_null($revision->getFecha())) {
                $fechaRevision = $revision->getFecha()->format('d') . ' / ' . $revision->getFecha()->format('m') . ' / ' . $revision->getFecha()->format('Y');
            }
            $templateProcessor->setValue("FECHA_REVISION", $fechaRevision);

            if (!is_null($revision->getFechaCertificacion())) {
                $fechaAptitud = $revision->getFechaCertificacion()->format('d') . ' / ' . $revision->getFechaCertificacion()->format('m') . ' / ' . $revision->getFechaCertificacion()->format('Y');
            }
            $templateProcessor->setValue("FECHA_APTITUD", $fechaAptitud);

            $revisionId = $revision->getId();

            //Buscamos el tipo de reconocimiento
            $query = "select respuesta from revision_respuesta where revision_id = $revisionId and pregunta_id = 280";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $tipoReconocimientoResult = $stmt->fetchAll();

            if (count($tipoReconocimientoResult) > 0) {
                $templateProcessor->setValue("TIPO_RECONOCIMIENTO", $tipoReconocimientoResult[0]['respuesta']);
            } else {
                $templateProcessor->setValue("TIPO_RECONOCIMIENTO", '');
            }
            //Buscamos los cuestionarios que debe rellanar el trabajador
            $empresaId = $revision->getEmpresa()->getId();
            $puestoTrabajoId = $revision->getPuestoTrabajo()->getId();

            $query = "select distinct f.id, f.codigo as cuestionario, f.orden from revision a
            inner join empresa b on a.empresa_id = b.id 
            inner join puesto_trabajo_centro c on a.puesto_trabajo_id = c.id
            inner join puesto_trabajo_protocolo d on c.id = d.puesto_trabajo_id 
            inner join protocolo_cuestionario e on d.protocolo_id = e.protocolo_id
            inner join cuestionario f on e.cuestionario_id = f.id
            where a.id = $revisionId
            and a.empresa_id = $empresaId
            and a.puesto_trabajo_id = $puestoTrabajoId
            and b.id = $empresaId
            and c.id = $puestoTrabajoId
            and d.puesto_trabajo_id = $puestoTrabajoId
            and d.empresa_id = $empresaId
            and a.anulado = false
            and c.anulado = false
            and d.anulado = false
            and e.anulado = false
            and f.anulado = false
            and f.tipo_cuestionario_id = 1
            order by f.orden asc";

            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $cuestionariosRellenar = $stmt->fetchAll();

            $templateProcessor->cloneBlock('block_cuestionarios', count($cuestionariosRellenar), true, true);

            $count = 1;
            $audiometriaOidoDArray = array();
            $audiometriaOidoIArray = array();
            $filename = "";
            $filenameEspirometria = "";
            $espirometriaArray = array();
            $espirometriaArray2 = array();
            foreach ($cuestionariosRellenar as $cr) {
                $templateProcessor->setValue("CUESTIONARIO#" . $count, $cr['cuestionario']);
                $cuestionarioId = $cr['id'];

                //Buscamos las preguntas del cuestionario
                $query = "select b.descripcion, b.descripcion_ca, b.id, a.orden, a.id as cuestionariopreguntaid from cuestionario_pregunta a
                    inner join pregunta b on a.pregunta_id = b.id
                    where a.anulado = false
                    and b.anulado = false
                    and a.cuestionario_id = $cuestionarioId
                    order by a.orden asc";

                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $preguntas = $stmt->fetchAll();

                $arrayPreguntas = array();
                foreach ($preguntas as $p) {

                    $item = array();

                    if ($idiomaPlantilla == 'ESP') {
                        $descripcionPregunta = $p['descripcion'];
                    } else {
                        $descripcionPregunta = $p['descripcion_ca'];
                    }
                    $item['PREGUNTA#' . $count] = $descripcionPregunta;
                    $cuestionarioPreguntaId = $p['cuestionariopreguntaid'];

                    //Buscamos las posibles respuestas de la pregunta
                    $respuestas = "";
                    $preguntaId = $p['id'];
                    $pregunta = $em->getRepository('App\Entity\Pregunta')->find($preguntaId);

                    //Buscamos la respuesta del trabajador para cada pregunta
                    $query = "select a.id, a.respuesta, c.id as cuestionariopregunta from revision_respuesta a
                    inner join pregunta b on a.pregunta_id = b.id
                    inner join cuestionario_pregunta c on a.pregunta_id = c.pregunta_id
                    where a.revision_id = $revisionId
                    and a.cuestionario_id = $cuestionarioId
                    and c.cuestionario_id = $cuestionarioId
                    and a.pregunta_id = $preguntaId
                    order by c.orden asc";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $revisionRespuestaResult = $stmt->fetchAll();

                    $revisionRespuesta = null;
                    $revisionRespuestaId = null;
                    $respuestaForm = "";
                    if (count($revisionRespuestaResult) > 0) {
                        $revisionRespuestaId = $revisionRespuestaResult[0]['id'];
                        $respuestaForm = $revisionRespuestaResult[0]['respuesta'];
                        $revisionRespuesta = $em->getRepository('App\Entity\RevisionRespuesta')->find($revisionRespuestaId);
                    }
                    if (!is_null($pregunta->getTipoRespuesta())) {
                        //Buscamos el tipo de respuesta
                        switch ($pregunta->getTipoRespuesta()->getId()) {
                                //TIPO TEXTO - TIPO NUMERICO - TIPO NUMERICO + DECIMAL
                            case 0:
                            case 1:
                            case 2:
                            case 7:
                                $respuestas = $respuestaForm;

                                //Generamos el grafico de espirometria
                                if ($preguntaId == 363) {
                                    if ($respuestaForm != "" && !is_nan($respuestaForm)) {
                                        array_push($espirometriaArray, $respuestaForm);
                                    }
                                }
                                if ($preguntaId == 365) {
                                    if ($respuestaForm != "" && !is_nan($respuestaForm)) {
                                        array_push($espirometriaArray2, $respuestaForm);
                                    }
                                }
                                break;
                                //TIPO SI/NO
                            case 3:
                                if (strtolower($respuestaForm) === "si") {
                                    //$respuestas = "☑  Si";
                                    $respuestas = "Si";
                                } elseif (strtolower($respuestaForm) === "no") {
                                    //$respuestas .= "☑  No";
                                    $respuestas .= "No";
                                }
                                break;
                                //TIPO FECHA
                            case 4:
                                $respuestas = $respuestaForm;
                                break;
                                //TIPO SERIE CAMPO
                            case 5:
                                //Comprobamos que la serie no sea nula
                                if (!is_null($pregunta->getSerieRespuesta())) {
                                    $respuestasSerie = $em->getRepository('App\Entity\Respuesta')->findBy(array('serieRespuesta' => $pregunta->getSerieRespuesta(), 'anulado' => false), array('descripcion' => 'ASC'));

                                    //Comprobamos ei es una unica respuesta o es multiple
                                    if (!is_null($pregunta->getSerieRespuesta()->getIndicador())) {
                                        $indicadorId = $pregunta->getSerieRespuesta()->getIndicador()->getId();

                                        switch ($indicadorId) {
                                                //MULTIRESPUESTA
                                            case 0:
                                                foreach ($respuestasSerie as $rs) {
                                                    $checked = false;
                                                    $respuestaSerieDescripcion = $rs->getDescripcion();

                                                    if (str_contains($respuestaForm, ';;')) {
                                                        $arrayExplode = explode(';;', $respuestaForm);
                                                    } else {
                                                        $arrayExplode = explode(';', $respuestaForm);
                                                    }
                                                    foreach ($arrayExplode as $ae) {
                                                        //Comprobamos la que haya marcado
                                                        if (strtolower($respuestaSerieDescripcion) === strtolower(str_replace(';', '', $ae))) {
                                                            $checked = true;
                                                            break;
                                                        }
                                                    }
                                                    if ($checked) {

                                                        if ($idiomaPlantilla == 'ESP') {
                                                            $respuestaSerieDescripcion = $rs->getDescripcion();
                                                        } else {
                                                            $respuestaSerieDescripcion = $rs->getDescripcionCa();
                                                        }

                                                        $respuestas .= "$respuestaSerieDescripcion";
                                                        $respuestas .= "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                                                    }
                                                }
                                                break;
                                                //UNICA RESPUESTA
                                            case 1:
                                                foreach ($respuestasSerie as $rs) {
                                                    $respuestaSerieDescripcion = $rs->getDescripcion();

                                                    //Comprobamos la que haya marcado
                                                    if (strtolower($respuestaSerieDescripcion) === strtolower($respuestaForm)) {

                                                        if ($idiomaPlantilla == 'ESP') {
                                                            $respuestaSerieDescripcion = $rs->getDescripcion();
                                                        } else {
                                                            $respuestaSerieDescripcion = $rs->getDescripcionCa();
                                                        }

                                                        $respuestas .= "$respuestaSerieDescripcion";
                                                    }
                                                }
                                                break;
                                        }
                                    }
                                }
                                break;
                                //TIPO SUB PREGUNTA
                            case 6:
                                $query = "select * from sub_pregunta where pregunta_id = $preguntaId and anulado = false order by orden asc";
                                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                                $stmt->execute();
                                $subPregunta = $stmt->fetchAll();
                                $countSubPreguntas = count($subPregunta);
                                if ($countSubPreguntas > 0) {
                                    for ($i = 1; $i <= $countSubPreguntas; $i++) {
                                        $orden = $subPregunta[$i - 1]['orden'];
                                        if ($idiomaPlantilla == 'ESP') {
                                            $ordenDescripcion = $subPregunta[$i - 1]['descripcion'];
                                        } else {
                                            $ordenDescripcion = $subPregunta[$i - 1]['descripcion_ca'];
                                        }
                                        $revisionSubRespuesta = null;
                                        if (!is_null($revisionRespuesta)) {
                                            $query = "select respuesta, orden from revision_sub_respuesta where revision_respuesta_id = $revisionRespuestaId and orden = '$orden' and cuestionario_pregunta_id = $cuestionarioPreguntaId order by id asc";
                                            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                                            $stmt->execute();
                                            $revisionSubRespuesta = $stmt->fetchAll();
                                        }
                                        $value = "";
                                        if (!is_null($revisionSubRespuesta)) {
                                            if (isset($revisionSubRespuesta[0]['respuesta'])) {
                                                $value = $revisionSubRespuesta[0]['respuesta'];
                                            }
                                        }
                                        if ($preguntaId == 86) {
                                            if ($value == "") {
                                                array_push($audiometriaOidoDArray, 0);
                                            } else {
                                                array_push($audiometriaOidoDArray, intval($value));
                                            }
                                        }
                                        if ($preguntaId == 367) {
                                            if ($value == "") {
                                                array_push($audiometriaOidoIArray, 0);
                                            } else {
                                                array_push($audiometriaOidoIArray, intval($value));
                                            }
                                        }
                                        $respuestas .= "$ordenDescripcion: " . $value;
                                        $respuestas .= "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                                    }
                                }
                                break;
                                //TIPO FORMULA
                                /*case 7:
                                if(is_null($pregunta->getFormula())){
                                    $formulaVariable = $em->getRepository('App\Entity\FormulaVariable')->findBy(array('formula' => $pregunta->getFormula(), 'anulado' => false), array('descripcion' => 'ASC'));
                                    foreach ($formulaVariable as $fv){
                                        $formulaVariableDescripcion = $fv->getDescripcion();
                                        $respuestaInput .= "<input type='text' name='$revisionRespuestaId' id='$preguntaId' placeholder='$formulaVariableDescripcion' class='form-control' /><br/>";
                                    }
                                }
                                break;*/
                        }
                    }
                    $item['RESPUESTAS#' . $count] = $respuestas;

                    array_push($arrayPreguntas, $item);
                }
                //$templateProcessor->cloneRowAndSetValues('PREGUNTA#'.$count, $arrayPreguntas);

                //Si han informado la pregunta de audiometria creamos el grafico
                if (count($audiometriaOidoDArray) > 0 && count($audiometriaOidoIArray) > 0) {
                    if ($filename == "") {
                        $filename = $this->crearGraficoAudiometria($revisionId, $audiometriaOidoDArray, $audiometriaOidoIArray);
                        if (file_exists($filename)) {
                            $templateProcessor->setImageValue('GRAFICO_AUDIOMETRIA#' . $count, array('path' => $filename, 'width' => 600, 'height' => 300, 'ratio' => false));
                        }
                    } else {
                        $templateProcessor->setValue('GRAFICO_AUDIOMETRIA#' . $count, "");
                    }
                } else {
                    $templateProcessor->setValue('GRAFICO_AUDIOMETRIA#' . $count, "");
                }
                //Si han informado la pregunta de audiometria creamos el grafico
                if (count($espirometriaArray) > 0 && count($espirometriaArray2) > 0) {
                    if ($filenameEspirometria == "") {
                        $filenameEspirometria = $this->crearGraficoEspirometria($revisionId, $espirometriaArray, $espirometriaArray2);
                        if (file_exists($filenameEspirometria)) {
                            $templateProcessor->setImageValue('GRAFICO_ESPIROMETRIA#' . $count, array('path' => $filenameEspirometria, 'width' => 600, 'height' => 300, 'ratio' => false));
                        }
                    } else {
                        $templateProcessor->setValue('GRAFICO_ESPIROMETRIA#' . $count, "");
                    }
                } else {
                    $templateProcessor->setValue('GRAFICO_ESPIROMETRIA#' . $count, "");
                }

                $count++;
            }
            //Buscamos el medico de la revision y su firma
            $medicoRevision = "";
            $firmaMedico = "";
            if (!is_null($revision->getMedico())) {
                $medicoRevision = $revision->getMedico()->getDescripcion();
                $firmaMedico = $revision->getMedico()->getFirma();
            }
            $templateProcessor->setValue('DOCTOR_REVISION', $medicoRevision);

            if ($firmaMedico != "") {
                $templateProcessor->setImageValue('FIRMA_DOCTOR_REVISION', 'upload/media/firmas/medico/' . $firmaMedico);
            } else {
                $templateProcessor->setValue('FIRMA_DOCTOR_REVISION', null);
            }

            //Consejos medicos
            $query = "select distinct b.id as pregunta, replace(a.respuesta, ';', '') as respuesta, b.serie_respuesta_id from revision_respuesta a
            inner join pregunta b on a.pregunta_id = b.id
            inner join cuestionario_pregunta c on a.pregunta_id = c.pregunta_id
            inner join respuesta e on b.serie_respuesta_id = e.serie_respuesta_id 
            where a.revision_id = $revisionId
            and b.serie_respuesta_id is not null
            and e.consejo_medico_id is not null
            order by b.id asc";

            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $respuestasPreguntas = $stmt->fetchAll();

            $serieRespuestaRepo = $this->getDoctrine()->getRepository('App\Entity\SerieRespuesta');
            $respuestaRepo = $this->getDoctrine()->getRepository('App\Entity\Respuesta');
            $arrayConsejosMedicos = array();
            foreach ($respuestasPreguntas as $rp) {

                $respuestaText = $rp['respuesta'];

                //Buscamos en la serie de respuestas si la respuesta que ha introducido tiene un consejo medico
                $serieRespuesta = $serieRespuestaRepo->find($rp['serie_respuesta_id']);
                $respuesta = $respuestaRepo->findBy(array('serieRespuesta' => $serieRespuesta));

                foreach ($respuesta as $r) {
                    if (strtolower($r->getDescripcion()) === strtolower($respuestaText)) {
                        if (!is_null($r->getConsejoMedico())) {

                            if ($idiomaPlantilla == 'ESP') {
                                $consejoMedicoDescripcion = $r->getConsejoMedico()->getDescripcion();
                            } else {
                                $consejoMedicoDescripcion = $r->getConsejoMedico()->getDescripcionCa();
                            }
                            array_push($arrayConsejosMedicos, $consejoMedicoDescripcion);
                        }
                    }
                }
            }
            $consejos = array_unique($arrayConsejosMedicos);

            $consejosMedicos = "";
            foreach ($consejos as $c) {
                $consejosMedicos .= $c . ".</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
            }
            $templateProcessor->setValue("RESUMEN_REVISION_CONSEJOS_MEDICOS", $consejosMedicos);

            $templateProcessor->setValue("RECOMENDACIONES_REVISION", $revision->getRecomendaciones());

            $electrocardiograma = $revision->getElectrocardiograma();
            if ($electrocardiograma != "") {
                $templateProcessor->setImageValue('ELECTROCARDIOGRAMA_REVISION', array('path' => 'upload/media/electrocardiograma/' . $electrocardiograma, 'width' => 620, 'height' => 876, 'ratio' => false));
            } else {
                $templateProcessor->setValue('ELECTROCARDIOGRAMA_REVISION', null);
            }
        }

        if ($revisionMedicaSn) {
            $revisionId = $revision->getId();

            if (!is_null($revision->getFecha())) {
                $fechaRevision = $revision->getFecha()->format('d') . ' / ' . $revision->getFecha()->format('m') . ' / ' . $revision->getFecha()->format('Y');
            }
            $templateProcessor->setValue("FECHA_REVISION", $fechaRevision);

            //Buscamos los cuestionarios que debe rellanar el trabajador
            $empresaId = $revision->getEmpresa()->getId();
            $puestoTrabajoId = $revision->getPuestoTrabajo()->getId();

            $query = "select distinct f.id, f.codigo as cuestionario, f.orden from revision a
            inner join empresa b on a.empresa_id = b.id 
            inner join puesto_trabajo_centro c on a.puesto_trabajo_id = c.id
            inner join puesto_trabajo_protocolo d on c.id = d.puesto_trabajo_id 
            inner join protocolo_cuestionario e on d.protocolo_id = e.protocolo_id
            inner join cuestionario f on e.cuestionario_id = f.id
            where a.id = $revisionId
            and a.empresa_id = $empresaId
            and a.puesto_trabajo_id = $puestoTrabajoId
            and b.id = $empresaId
            and c.id = $puestoTrabajoId
            and d.puesto_trabajo_id = $puestoTrabajoId
            and d.empresa_id = $empresaId
            and a.anulado = false
            and c.anulado = false
            and d.anulado = false
            and e.anulado = false
            and f.anulado = false
            and f.tipo_cuestionario_id = 1
            order by f.orden asc";

            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $cuestionariosRellenar = $stmt->fetchAll();

            $templateProcessor->cloneBlock('block_cuestionarios', count($cuestionariosRellenar), true, true);

            $count = 1;
            foreach ($cuestionariosRellenar as $cr) {
                $templateProcessor->setValue("CUESTIONARIO#" . $count, $cr['cuestionario']);

                $cuestionarioId = $cr['id'];
                //Buscamos las preguntas del cuestionario
                $query = "select b.descripcion, b.descripcion_ca, b.id, a.orden from cuestionario_pregunta a
                    inner join pregunta b on a.pregunta_id = b.id
                    where a.anulado = false
                    and b.anulado = false
                    and a.cuestionario_id = $cuestionarioId
                    order by a.orden asc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $preguntas = $stmt->fetchAll();

                $arrayPreguntas = array();
                foreach ($preguntas as $p) {
                    $item = array();

                    if ($idiomaPlantilla == 'ESP') {
                        $descripcionPregunta = $p['descripcion'];
                    } else {
                        $descripcionPregunta = $p['descripcion_ca'];
                    }
                    $item['PREGUNTA#' . $count] = $descripcionPregunta;

                    //Buscamos las posibles respuestas de la pregunta
                    $respuestas = "";
                    $preguntaId = $p['id'];

                    $pregunta = $em->getRepository('App\Entity\Pregunta')->find($preguntaId);
                    if (!is_null($pregunta->getTipoRespuesta())) {
                        //Buscamos el tipo de respuesta
                        switch ($pregunta->getTipoRespuesta()->getId()) {
                                //TIPO TEXTO - TIPO NUMERICO - TIPO NUMERICO + DECIMAL
                            case 0:
                            case 1:
                            case 2:
                            case 7:
                                $respuestas = "";
                                break;
                                //TIPO SI/NO
                            case 3:
                                $respuestas = "☐  Si";
                                $respuestas .= "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                                $respuestas .= "☐  No";
                                break;
                                //TIPO FECHA
                            case 4:
                                $respuestas = "__/__/____";
                                break;
                                //TIPO SERIE CAMPO
                            case 5:
                                //Comprobamos que la serie no sea nula
                                if (!is_null($pregunta->getSerieRespuesta())) {
                                    $respuestasSerie = $em->getRepository('App\Entity\Respuesta')->findBy(array('serieRespuesta' => $pregunta->getSerieRespuesta(), 'anulado' => false), array('descripcion' => 'ASC'));

                                    //Comprobamos ei es una unica respuesta o es multiple
                                    if (!is_null($pregunta->getSerieRespuesta()->getIndicador())) {
                                        $indicadorId = $pregunta->getSerieRespuesta()->getIndicador()->getId();

                                        switch ($indicadorId) {
                                                //MULTIRESPUESTA
                                            case 0:
                                                $respuestas = ($idiomaPlantilla == 'ESP') ? "Si, indique quin:" : "Si, indiqui quin:";
                                                $respuestas .= "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                                                foreach ($respuestasSerie as $rs) {
                                                    if ($idiomaPlantilla == 'ESP') {
                                                        $respuestaSerieDescripcion = $rs->getDescripcion();
                                                    } else {
                                                        $respuestaSerieDescripcion = $rs->getDescripcionCa();
                                                    }
                                                    $respuestas .= "☐  $respuestaSerieDescripcion";
                                                    $respuestas .= "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                                                }
                                                break;
                                                //UNICA RESPUESTA
                                            case 1:
                                                $respuestas = ($idiomaPlantilla == 'ESP') ? "Si, indique quin:" : "Si, indiqui quin:";
                                                $respuestas .= "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                                                foreach ($respuestasSerie as $rs) {
                                                    if ($idiomaPlantilla == 'ESP') {
                                                        $respuestaSerieDescripcion = $rs->getDescripcion();
                                                    } else {
                                                        $respuestaSerieDescripcion = $rs->getDescripcionCa();
                                                    }
                                                    $respuestas .= "☐  $respuestaSerieDescripcion";
                                                    $respuestas .= "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                                                }
                                                break;
                                        }
                                    }
                                }
                                break;
                                //TIPO SUB PREGUNTA
                            case 6:
                                $subPregunta = $em->getRepository('App\Entity\SubPregunta')->findBy(array('pregunta' => $pregunta, 'anulado' => false), array('orden' => 'ASC'));
                                foreach ($subPregunta as $sp) {

                                    if ($idiomaPlantilla == 'ESP') {
                                        $ordenDescripcion = $sp->getDescripcion();
                                    } else {
                                        $ordenDescripcion = $sp->getDescripcionCa();
                                    }
                                    $respuestas .= "$ordenDescripcion:";
                                    $respuestas .= "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";
                                }
                                break;
                                //TIPO FORMULA
                                /*case 7:
                                if(is_null($pregunta->getFormula())){
                                    $formulaVariable = $em->getRepository('App\Entity\FormulaVariable')->findBy(array('formula' => $pregunta->getFormula(), 'anulado' => false), array('descripcion' => 'ASC'));
                                    foreach ($formulaVariable as $fv){
                                        $formulaVariableDescripcion = $fv->getDescripcion();
                                        $respuestaInput .= "<input type='text' name='$revisionRespuestaId' id='$preguntaId' placeholder='$formulaVariableDescripcion' class='form-control' /><br/>";
                                    }
                                }
                                break;*/
                        }
                    }
                    $item['RESPUESTAS#' . $count] = $respuestas;

                    array_push($arrayPreguntas, $item);
                }
                //$templateProcessor->cloneRowAndSetValues('PREGUNTA#'.$count, $arrayPreguntas);

                $count++;
            }
        }

        if ($memoriaSn) {
            $templateProcessor->setValue('ANYO_MEMORIA', $anyoMemoriaEstudio);
            $templateProcessor->setValue('FECHA_IMPRESION_MEMORIA', $hoyString);
            $templateProcessor->setValue('PERIODO_EXAMENES_SALUD', '01-01-' . $anyoMemoriaEstudio . ' - 31-12-' . $anyoMemoriaEstudio);

            $fechaImpresionMemoria = $hoy->format('d') . ' de ' . $this->obtenerMes($hoy->format('m')) . ' de ' . $anyoMemoriaEstudio;
            $templateProcessor->setValue('FECHA_IMPRESION_MEMORIA_2', $fechaImpresionMemoria);

            //Calculamos los reconocimientos en el periodo
            $query = "select count(*) as contador from revision where empresa_id = $empresaId and fecha between '$anyoMemoriaEstudio-01-01 00:00:00' and '$anyoMemoriaEstudio-12-31 23:59:59' and anulado = false";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultRevisionesPeriodo = $stmt->fetchAll();

            $templateProcessor->setValue('EXAMENES_SALUD', $resultRevisionesPeriodo[0]['contador']);
            $templateProcessor->setValue('RECONOCIMIENTOS_MEDICOS', $resultRevisionesPeriodo[0]['contador']);

            $firmaMedico = "";
            $colegiadoMedico = "";
            $especialidadMedico = "";
            $gestoraMedico = "";
            if (!is_null($empresa->getVigilanciaSalud())) {
                if (!is_null($empresa->getVigilanciaSalud()->getMedico())) {
                    $doctorRevision = $empresa->getVigilanciaSalud()->getMedico()->getDescripcion();
                    $firmaMedico = $empresa->getVigilanciaSalud()->getMedico()->getFirma();
                    $colegiadoMedico = $empresa->getVigilanciaSalud()->getMedico()->getNumeroColegiado();
                    $especialidadMedico = $empresa->getVigilanciaSalud()->getMedico()->getEspecialidad();
                    $gestoraMedico = $empresa->getVigilanciaSalud()->getMedico()->getGestora();
                }
            }

            if ($firmaMedico != "") {
                $templateProcessor->setImageValue('MEDICO_FIRMA', 'upload/media/firmas/medico/' . $firmaMedico);
            } else {
                $templateProcessor->setValue('MEDICO_FIRMA', null);
            }
            $templateProcessor->setValue("MEDICO_NOMBRE", $doctorRevision);
            $templateProcessor->setValue('MEDICO_COLEGIADO', $colegiadoMedico);
            $templateProcessor->setValue('MEDICO_ESPECIALIDAD', $especialidadMedico);
            $templateProcessor->setValue('MEDICO_GESTORA', $gestoraMedico);
        }

        if ($estudioSn) {
            $templateProcessor->setValue('ANYO_ESTUDIO', $anyoMemoriaEstudio);
            $templateProcessor->setValue('ANYO_ESTUDIO_2', $anyoMemoriaEstudio - 1);
            $templateProcessor->setValue('ANYO_ESTUDIO_3', $anyoMemoriaEstudio - 2);

            $templateProcessor->setValue('PERIODO_EXAMENES_SALUD', '01-01-' . $anyoMemoriaEstudio . ' - 31-12-' . $anyoMemoriaEstudio);

            $fechaImpresionMemoria = $hoy->format('d') . ' de ' . $this->obtenerMes($hoy->format('m')) . ' de ' . $anyoMemoriaEstudio;
            $templateProcessor->setValue('FECHA_IMPRESION_ESTUDIO_2', $fechaImpresionMemoria);

            //Calculamos los reconocimientos en el periodo
            $templateProcessor->setValue('EXAMENES_SALUD', $this->calcularReconocimientos($empresaId, $anyoMemoriaEstudio));
            $templateProcessor->setValue('RECONOCIMIENTOS_MEDICOS', $this->calcularReconocimientos($empresaId, $anyoMemoriaEstudio));

            //Calculamos los reconocimientos de 3 años atras
            $templateProcessor->setValue('RA_1_V', $this->calcularReconocimientos($empresaId, $anyoMemoriaEstudio));
            $templateProcessor->setValue('RA_2_V', $this->calcularReconocimientos($empresaId, $anyoMemoriaEstudio - 1));
            $templateProcessor->setValue('RA_3_V', $this->calcularReconocimientos($empresaId, $anyoMemoriaEstudio - 2));

            //Calculamos el numero de aptitudes por tipos
            $templateProcessor->setValue('TIPO_APT_I_1', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio, 'Inicial'));
            $templateProcessor->setValue('TIPO_APT_I_2', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio - 1, 'Inicial'));
            $templateProcessor->setValue('TIPO_APT_I_3', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio - 2, 'Inicial'));

            $templateProcessor->setValue('TIPO_APT_P_1', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio, 'Periodico'));
            $templateProcessor->setValue('TIPO_APT_P_2', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio - 1, 'Periodico'));
            $templateProcessor->setValue('TIPO_APT_P_3', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio - 2, 'Periodico'));

            $templateProcessor->setValue('TIPO_APT_TA_1', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio, 'Tras ausencia prolongada'));
            $templateProcessor->setValue('TIPO_APT_TA_2', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio - 1, 'Tras ausencia prolongada'));
            $templateProcessor->setValue('TIPO_APT_TA_3', $this->calcularAptitudesPorTipo($empresaId, $anyoMemoriaEstudio - 2, 'Tras ausencia prolongada'));

            //Calculamos el recuento por aptitud
            $templateProcessor->setValue('APTO_1_1', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio, 1));
            $templateProcessor->setValue('APTO_1_2', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio - 1, 1));
            $templateProcessor->setValue('APTO_1_3', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio - 2, 1));

            $templateProcessor->setValue('APTO_2_1', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio, 2));
            $templateProcessor->setValue('APTO_2_2', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio - 1, 2));
            $templateProcessor->setValue('APTO_2_3', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio - 2, 2));

            $templateProcessor->setValue('APTO_3_1', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio, 3));
            $templateProcessor->setValue('APTO_3_2', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio - 1, 3));
            $templateProcessor->setValue('APTO_3_3', $this->calcularRecuentoPorAptitud($empresaId, $anyoMemoriaEstudio - 2, 3));

            //Calculamos el recuento por tipo de restriccion
            $templateProcessor->setValue('AR_1_1', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio, 1));
            $templateProcessor->setValue('AR_1_2', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 1, 1));
            $templateProcessor->setValue('AR_1_3', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 2, 1));

            $templateProcessor->setValue('AR_2_1', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio, 2));
            $templateProcessor->setValue('AR_2_2', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 1, 2));
            $templateProcessor->setValue('AR_2_3', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 2, 2));

            $templateProcessor->setValue('AR_3_1', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio, 3));
            $templateProcessor->setValue('AR_3_2', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 1, 3));
            $templateProcessor->setValue('AR_3_3', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 2, 3));

            $templateProcessor->setValue('AR_4_1', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio, 4));
            $templateProcessor->setValue('AR_4_2', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 1, 4));
            $templateProcessor->setValue('AR_4_3', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 2, 4));

            $templateProcessor->setValue('AR_5_1', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio, 5));
            $templateProcessor->setValue('AR_5_2', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 1, 5));
            $templateProcessor->setValue('AR_5_3', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 2, 5));

            $templateProcessor->setValue('AR_6_1', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio, 6));
            $templateProcessor->setValue('AR_6_2', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 1, 6));
            $templateProcessor->setValue('AR_6_3', $this->calcularRecuentoPorRestriccion($empresaId, $anyoMemoriaEstudio - 2, 6));

            //Calculamos las restricciones por puesto de trabajo
            $yearMenos2 = $anyoMemoriaEstudio - 2;
            $query = "select distinct b.descripcion, b.id from revision a 
            inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id 
            where a.empresa_id = $empresaId 
            and fecha between '$yearMenos2-01-01 00:00:00' and '$anyoMemoriaEstudio-12-31 23:59:59'
            and a.anulado = false
            and a.aptitud_restriccion_id is not null
            order by b.descripcion asc";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultRestriccionPorPuestoTrabajo = $stmt->fetchAll();
            $arrayRestriccionPorPuestoTrabajo = array();

            foreach ($resultRestriccionPorPuestoTrabajo as $r) {
                $item = array();
                $item['AR_PT_DESC'] = $r['descripcion'];
                $item['AR_PT_1'] = $this->calcularRestriccionesPorPuestoTrabajo($empresaId, $anyoMemoriaEstudio, $r['id']);
                $item['AR_PT_2'] = $this->calcularRestriccionesPorPuestoTrabajo($empresaId, $anyoMemoriaEstudio - 1, $r['id']);
                $item['AR_PT_3'] = $this->calcularRestriccionesPorPuestoTrabajo($empresaId, $anyoMemoriaEstudio - 2, $r['id']);
                array_push($arrayRestriccionPorPuestoTrabajo, $item);
            }
            if (count($arrayRestriccionPorPuestoTrabajo) == 0) {
                $item = array();
                $item['AR_PT_DESC'] = '';
                $item['AR_PT_1'] = '';
                $item['AR_PT_2'] = '';
                $item['AR_PT_3'] = '';
                array_push($arrayRestriccionPorPuestoTrabajo, $item);
            }
            //$templateProcessor->cloneRowAndSetValues('AR_PT_DESC', $arrayRestriccionPorPuestoTrabajo);

            //Calculamos los reconocimientos por puesto de trabajo
            $query = "select distinct b.descripcion, b.id from revision a 
            inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id 
            where a.empresa_id = $empresaId 
            and fecha between '$anyoMemoriaEstudio-01-01 00:00:00' and '$anyoMemoriaEstudio-12-31 23:59:59'
            and a.anulado = false
            order by b.descripcion asc";

            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultReconocimientoPorPuestoTrabajo = $stmt->fetchAll();
            $arrayReconocimientoPorPuestoTrabajo = array();

            foreach ($resultReconocimientoPorPuestoTrabajo as $r) {
                $item = array();
                $item['PUESTO_TRABAJO_ESTUDIO'] = $r['descripcion'];
                $item['PUESTO_TRABAJO_CANTIDAD'] = $this->calcularReconocimientosPorPuestoTrabajo($empresaId, $anyoMemoriaEstudio, $r['id']);
                array_push($arrayReconocimientoPorPuestoTrabajo, $item);
            }
            if (count($arrayReconocimientoPorPuestoTrabajo) == 0) {
                $item = array();
                $item['PUESTO_TRABAJO_ESTUDIO'] = "";
                $item['PUESTO_TRABAJO_CANTIDAD'] = "";
                array_push($arrayReconocimientoPorPuestoTrabajo, $item);
            }
            //$templateProcessor->cloneRowAndSetValues('PUESTO_TRABAJO_ESTUDIO', $arrayReconocimientoPorPuestoTrabajo);

            //Calculamos protocolos aplicados
            $query = "select c.descripcion, count(*) as contador from revision a 
            inner join puesto_trabajo_protocolo b on a.puesto_trabajo_id = b.puesto_trabajo_id 
            inner join protocolo c on b.protocolo_id = c.id
            where a.empresa_id = $empresaId 
            and a.fecha between '$anyoMemoriaEstudio-01-01 00:00:00' and '$anyoMemoriaEstudio-12-31 23:59:59'
            and a.anulado = false
            group by c.descripcion 
            order by c.descripcion asc";

            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultProtocolosAplicados = $stmt->fetchAll();
            $arrayProtocolosAplicados = array();
            $arrayProtocolosAplicadosLabels = array();
            $arrayProtocolosAplicadosValores = array();

            foreach ($resultProtocolosAplicados as $r) {
                $item = array();
                $item['PROTOCOLO_ESTUDIO'] = $r['descripcion'];
                $item['PROTOCOLO_CANTIDAD'] = $r['contador'];
                array_push($arrayProtocolosAplicados, $item);
                array_push($arrayProtocolosAplicadosLabels, $r['descripcion']);
                array_push($arrayProtocolosAplicadosValores, $r['contador']);
            }
            if (count($arrayProtocolosAplicados) == 0) {
                $item = array();
                $item['PROTOCOLO_ESTUDIO'] = "";
                $item['PROTOCOLO_CANTIDAD'] = "";
                array_push($arrayProtocolosAplicados, $item);
            }
            //$templateProcessor->cloneRowAndSetValues('PROTOCOLO_ESTUDIO', $arrayProtocolosAplicados);

            if (count($arrayProtocolosAplicadosLabels) > 0 && count($arrayProtocolosAplicadosValores) > 0) {
                $filenameProtocolos = $this->crearGraficoProtocolosEstudios($empresaId, $arrayProtocolosAplicadosLabels, $arrayProtocolosAplicadosValores);
                $templateProcessor->setImageValue('GRAFICO_PROTOCOLOS', array('path' => $filenameProtocolos, 'width' => 600, 'height' => 300, 'ratio' => false));
            } else {
                $templateProcessor->setValue('GRAFICO_PROTOCOLOS', "");
            }
            //Calculamos los trabajadores que han pasado reconocimiento en el periodo
            $templateProcessor->setValue('TRABAJADORES_RECONOCIMIENTO_MEDICO', $this->calcularReconocimientosPorTrabajador($empresaId, $anyoMemoriaEstudio));

            //Calculamos los reconocimientos por sexo y edad
            $templateProcessor->setValue('EDAD_H_1', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 1, '< 20'));
            $templateProcessor->setValue('EDAD_H_2', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 1, 'BETWEEN 21 and 30'));
            $templateProcessor->setValue('EDAD_H_3', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 1, 'BETWEEN 31 and 40'));
            $templateProcessor->setValue('EDAD_H_4', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 1, 'BETWEEN 41 and 50'));
            $templateProcessor->setValue('EDAD_H_5', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 1, 'BETWEEN 51 and 60'));
            $templateProcessor->setValue('EDAD_H_6', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 1, '> 60'));
            $templateProcessor->setValue('EDAD_H_T', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 1, null));

            $templateProcessor->setValue('EDAD_M_1', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 2, '< 20'));
            $templateProcessor->setValue('EDAD_M_2', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 2, 'BETWEEN 21 and 30'));
            $templateProcessor->setValue('EDAD_M_3', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 2, 'BETWEEN 31 and 40'));
            $templateProcessor->setValue('EDAD_M_4', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 2, 'BETWEEN 41 and 50'));
            $templateProcessor->setValue('EDAD_M_5', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 2, 'BETWEEN 51 and 60'));
            $templateProcessor->setValue('EDAD_M_6', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 2, '> 60'));
            $templateProcessor->setValue('EDAD_M_T', $this->calcularReconocimientosPorSexo($empresaId, $anyoMemoriaEstudio, 2, null));

            //Diagnostico IMC
            $templateProcessor->setValue('IMC_1', $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio, 1));
            $templateProcessor->setValue('IMC_2', $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio, 2));
            $templateProcessor->setValue('IMC_3', $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio, 3));
            $templateProcessor->setValue('IMC_4', $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio, 4));

            $cantidadTotalImc = $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio, 1) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio, 2) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio, 3) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio, 4);
            $cantidadTotalImc1 = $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio - 1, 1) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio - 1, 2) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio - 1, 3) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio - 1, 4);
            $cantidadTotalImc2 = $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio - 2, 1) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio - 2, 2) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio - 2, 3) + $this->calcularImcPorTipo($empresaId, $anyoMemoriaEstudio - 2, 4);

            $templateProcessor->setValue('IMC_1_2', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio, 1, $cantidadTotalImc));
            $templateProcessor->setValue('IMC_1_3', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio - 1, 1, $cantidadTotalImc1));
            $templateProcessor->setValue('IMC_1_4', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio - 2, 1, $cantidadTotalImc2));

            $templateProcessor->setValue('IMC_2_2', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio, 2, $cantidadTotalImc));
            $templateProcessor->setValue('IMC_2_3', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio - 1, 2, $cantidadTotalImc1));
            $templateProcessor->setValue('IMC_2_4', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio - 2, 2, $cantidadTotalImc2));

            $templateProcessor->setValue('IMC_3_2', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio, 3, $cantidadTotalImc));
            $templateProcessor->setValue('IMC_3_3', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio - 1, 3, $cantidadTotalImc1));
            $templateProcessor->setValue('IMC_3_4', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio - 2, 3, $cantidadTotalImc2));

            $templateProcessor->setValue('IMC_4_2', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio, 4, $cantidadTotalImc));
            $templateProcessor->setValue('IMC_4_3', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio - 1, 4, $cantidadTotalImc1));
            $templateProcessor->setValue('IMC_4_4', $this->calcularPorcentajeImcPorTipo($empresaId, $anyoMemoriaEstudio - 2, 4, $cantidadTotalImc2));

            //Hábito tabáquico
            $templateProcessor->setValue('TABACO_1', $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 1));
            $templateProcessor->setValue('TABACO_2', $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 2));
            $templateProcessor->setValue('TABACO_3', $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 3));
            $templateProcessor->setValue('TABACO_4', $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 4));
            $templateProcessor->setValue('TABACO_5', $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 5));
            $templateProcessor->setValue('TABACO_6', $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 6));

            $cantidadTotalTabaco = $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 1) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 2) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 3) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 4) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 5);
            $cantidadTotalTabaco1 = $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 1) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 2) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 3) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 4) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 5);
            $cantidadTotalTabaco2 = $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 1) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 2) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 3) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 4) + $this->calcularTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 5);

            $templateProcessor->setValue('TABACO_1_2', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 1, $cantidadTotalTabaco));
            $templateProcessor->setValue('TABACO_1_3', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 1, $cantidadTotalTabaco1));
            $templateProcessor->setValue('TABACO_1_4', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 1, $cantidadTotalTabaco2));

            $templateProcessor->setValue('TABACO_2_2', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 2, $cantidadTotalTabaco));
            $templateProcessor->setValue('TABACO_2_3', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 2, $cantidadTotalTabaco1));
            $templateProcessor->setValue('TABACO_2_4', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 2, $cantidadTotalTabaco2));

            $templateProcessor->setValue('TABACO_3_2', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 3, $cantidadTotalTabaco));
            $templateProcessor->setValue('TABACO_3_3', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 3, $cantidadTotalTabaco1));
            $templateProcessor->setValue('TABACO_3_4', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 3, $cantidadTotalTabaco2));

            $templateProcessor->setValue('TABACO_4_2', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 4, $cantidadTotalTabaco));
            $templateProcessor->setValue('TABACO_4_3', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 4, $cantidadTotalTabaco1));
            $templateProcessor->setValue('TABACO_4_4', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 4, $cantidadTotalTabaco2));

            $templateProcessor->setValue('TABACO_5_2', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 5, $cantidadTotalTabaco));
            $templateProcessor->setValue('TABACO_5_3', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 5, $cantidadTotalTabaco1));
            $templateProcessor->setValue('TABACO_5_4', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 5, $cantidadTotalTabaco2));

            $templateProcessor->setValue('TABACO_6_2', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio, 6, $cantidadTotalTabaco));
            $templateProcessor->setValue('TABACO_6_3', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 1, 6, $cantidadTotalTabaco1));
            $templateProcessor->setValue('TABACO_6_4', $this->calcularPorcentajeTabacoPorTipo($empresaId, $anyoMemoriaEstudio - 2, 6, $cantidadTotalTabaco2));

            //Hipertension arterial
            $templateProcessor->setValue('HPA_1', $this->calcularHipertensionArterialTrabajador($empresaId, $anyoMemoriaEstudio, true));

            $templateProcessor->setValue('HPA_1_2', $this->calcularPorcentajeHipertensionArterialTrabajador($empresaId, $anyoMemoriaEstudio, $this->calcularHipertensionArterialTrabajador($empresaId, $anyoMemoriaEstudio, false)));
            $templateProcessor->setValue('HPA_1_3', $this->calcularPorcentajeHipertensionArterialTrabajador($empresaId, $anyoMemoriaEstudio - 1, $this->calcularHipertensionArterialTrabajador($empresaId, $anyoMemoriaEstudio - 1, false)));
            $templateProcessor->setValue('HPA_1_4', $this->calcularPorcentajeHipertensionArterialTrabajador($empresaId, $anyoMemoriaEstudio - 2, $this->calcularHipertensionArterialTrabajador($empresaId, $anyoMemoriaEstudio - 2, false)));

            //Hipercolesterolemia
            $templateProcessor->setValue('HC_1', $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio, true, 1));
            $templateProcessor->setValue('HC_1_2', $this->calcularPorcentajeHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio, $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio, false, 1), 1));
            $templateProcessor->setValue('HC_1_3', $this->calcularPorcentajeHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio - 1, $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio - 1, false, 1), 1));
            $templateProcessor->setValue('HC_1_4', $this->calcularPorcentajeHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio - 2, $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio - 2, false, 1), 1));

            //Diabetes
            $templateProcessor->setValue('DIABETES_1', $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio, true, 2));
            $templateProcessor->setValue('DIABETES_1_2', $this->calcularPorcentajeHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio, $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio, false, 2), 2));
            $templateProcessor->setValue('DIABETES_1_3', $this->calcularPorcentajeHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio - 1, $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio - 1, false, 2), 2));
            $templateProcessor->setValue('DIABETES_1_4', $this->calcularPorcentajeHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio - 2, $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $anyoMemoriaEstudio - 2, false, 2), 2));

            //Sedentarismo
            $templateProcessor->setValue('SEDENTARISMO_1', $this->calcularSedentarismoTrabajador($empresaId, $anyoMemoriaEstudio, true));

            $templateProcessor->setValue('SEDENTARISMO_1_2', $this->calcularPorcentajeSedentarismoTrabajador($empresaId, $anyoMemoriaEstudio, $this->calcularSedentarismoTrabajador($empresaId, $anyoMemoriaEstudio, false)));
            $templateProcessor->setValue('SEDENTARISMO_1_3', $this->calcularPorcentajeSedentarismoTrabajador($empresaId, $anyoMemoriaEstudio - 1, $this->calcularSedentarismoTrabajador($empresaId, $anyoMemoriaEstudio - 1, false)));
            $templateProcessor->setValue('SEDENTARISMO_1_4', $this->calcularPorcentajeSedentarismoTrabajador($empresaId, $anyoMemoriaEstudio - 2, $this->calcularSedentarismoTrabajador($empresaId, $anyoMemoriaEstudio - 2, false)));

            //Alteraciones audiometria
            $templateProcessor->setValue('AUDIOMETRIA_1', $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio, 1));
            $templateProcessor->setValue('AUDIOMETRIA_2', $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio, 2));
            $templateProcessor->setValue('AUDIOMETRIA_1_1', $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio, 3));
            $templateProcessor->setValue('AUDIOMETRIA_2_1', $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio, 4));

            $templateProcessor->setValue('AUDIOMETRIA_1_2', $this->calcularPorcentajeAlteracionAudiometriaRuido($this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio, 1), $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio, 3)));
            $templateProcessor->setValue('AUDIOMETRIA_2_2', $this->calcularPorcentajeAlteracionAudiometriaRuido($this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio, 2), $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio, 4)));
            $templateProcessor->setValue('AUDIOMETRIA_1_3', $this->calcularPorcentajeAlteracionAudiometriaRuido($this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio - 1, 1), $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio - 1, 3)));
            $templateProcessor->setValue('AUDIOMETRIA_2_3', $this->calcularPorcentajeAlteracionAudiometriaRuido($this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio - 1, 2), $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio - 1, 4)));
            $templateProcessor->setValue('AUDIOMETRIA_1_4', $this->calcularPorcentajeAlteracionAudiometriaRuido($this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio - 2, 1), $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio - 2, 3)));
            $templateProcessor->setValue('AUDIOMETRIA_2_4', $this->calcularPorcentajeAlteracionAudiometriaRuido($this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio - 2, 2), $this->calcularAlteracionAudiometriaRuido($empresaId, $anyoMemoriaEstudio - 2, 4)));

            //Alteraciones agudeza visual
            $templateProcessor->setValue('AV_1', $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, true, 1));
            $templateProcessor->setValue('AV_1_2', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, false, 1), 1));
            $templateProcessor->setValue('AV_1_3', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 1, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 1, false, 1), 1));
            $templateProcessor->setValue('AV_1_4', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 2, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 2, false, 1), 1));

            $templateProcessor->setValue('AV_2', $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, true, 2));
            $templateProcessor->setValue('AV_2_2', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, false, 2), 2));
            $templateProcessor->setValue('AV_2_3', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 1, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 1, false, 2), 2));
            $templateProcessor->setValue('AV_2_4', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 2, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 2, false, 2), 2));

            $templateProcessor->setValue('AV_3', $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, true, 3));
            $templateProcessor->setValue('AV_3_2', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio, false, 3), 3));
            $templateProcessor->setValue('AV_3_3', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 1, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 1, false, 3), 3));
            $templateProcessor->setValue('AV_3_4', $this->calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 2, $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $anyoMemoriaEstudio - 2, false, 3), 3));

            //Alteraciones osteomusculares
            $templateProcessor->setValue('AO_1', $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 1, null));
            $templateProcessor->setValue('AO_2', $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 2, null));
            $templateProcessor->setValue('AO_3', $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 3, null));
            $templateProcessor->setValue('AO_4', $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 4, null));
            $templateProcessor->setValue('AO_5', $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 5, null));
            $alteracionOsteomuscular = $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 1, null) + $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 2, null) + $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 3, null) + $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 4, null) + $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 5, null);
            $templateProcessor->setValue('AO_TOTAL', $alteracionOsteomuscular);

            //Generamos el grafico de las alteraciones osteomusculares
            $arrayAlteracionOsteomuscularsLabels = array();
            $arrayAlteracionOsteomuscularValores = array();

            array_push($arrayAlteracionOsteomuscularsLabels, 'Columna vertebral');
            array_push($arrayAlteracionOsteomuscularsLabels, 'Muñeca/Manos');
            array_push($arrayAlteracionOsteomuscularsLabels, 'Rodillas');
            array_push($arrayAlteracionOsteomuscularsLabels, 'Extremidades superiores');
            array_push($arrayAlteracionOsteomuscularsLabels, 'Extremidades inferiores');

            array_push($arrayAlteracionOsteomuscularValores, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 1, null));
            array_push($arrayAlteracionOsteomuscularValores, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 2, null));
            array_push($arrayAlteracionOsteomuscularValores, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 3, null));
            array_push($arrayAlteracionOsteomuscularValores, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 4, null));
            array_push($arrayAlteracionOsteomuscularValores, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, true, 5, null));

            if (count($arrayAlteracionOsteomuscularsLabels) > 0 && count($arrayAlteracionOsteomuscularValores) > 0) {
                $filenameAlteracionOsteomuscular = $this->crearGraficoAlteracionOsteomuscular($empresaId, $arrayAlteracionOsteomuscularsLabels, $arrayAlteracionOsteomuscularValores);
                $templateProcessor->setImageValue('GRAFICO_ALTERACION_OSTEOMUSCULAR', array('path' => $filenameAlteracionOsteomuscular, 'width' => 600, 'height' => 300, 'ratio' => false));
            } else {
                $templateProcessor->setValue('GRAFICO_ALTERACION_OSTEOMUSCULAR', "");
            }
            //Buscamos las alteraciones osteomusculares por cada puesto de trabajo
            $query = "select distinct b.id, b.descripcion from revision a 
            inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id 
            where a.empresa_id = $empresaId 
            and a.fecha between '$anyoMemoriaEstudio-01-01 00:00:00' and '$anyoMemoriaEstudio-12-31 23:59:59'
            and a.anulado = false
            order by b.descripcion asc";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultPuestoTrabajoAo = $stmt->fetchAll();
            $arrayAlteracionesOsteomuscularesPuesoTrabajo = array();
            $countPuestoTrabajoAo = 1;
            $puestoTrabajoAo = "";

            foreach ($resultPuestoTrabajoAo as $r) {
                $item = array();
                $puestoTrabajoId = $r['id'];
                $puestoTrabajoDesc = $r['descripcion'];

                $grupoPuestoTrabajo = "G" . $countPuestoTrabajoAo . ": ";

                $item['AO_PUESTO'] = $grupoPuestoTrabajo . $puestoTrabajoDesc;
                $item['AO_PUESTO_1'] = $this->calcularPorcentajeAlteracionOsteomuscular($empresaId, $anyoMemoriaEstudio, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, false, 1, $puestoTrabajoId), 1, $puestoTrabajoId);
                $item['AO_PUESTO_2'] = $this->calcularPorcentajeAlteracionOsteomuscular($empresaId, $anyoMemoriaEstudio, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, false, 2, $puestoTrabajoId), 2, $puestoTrabajoId);
                $item['AO_PUESTO_3'] = $this->calcularPorcentajeAlteracionOsteomuscular($empresaId, $anyoMemoriaEstudio, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, false, 3, $puestoTrabajoId), 3, $puestoTrabajoId);
                $item['AO_PUESTO_4'] = $this->calcularPorcentajeAlteracionOsteomuscular($empresaId, $anyoMemoriaEstudio, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, false, 4, $puestoTrabajoId), 4, $puestoTrabajoId);
                $item['AO_PUESTO_5'] = $this->calcularPorcentajeAlteracionOsteomuscular($empresaId, $anyoMemoriaEstudio, $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $anyoMemoriaEstudio, false, 5, $puestoTrabajoId), 5, $puestoTrabajoId);

                array_push($arrayAlteracionesOsteomuscularesPuesoTrabajo, $item);

                //Buscamos los trabajadores por cada puesto de trabajo
                $countPuestoTrabajoReconocimiento = $this->calcularReconocimientosPorPuestoTrabajo($empresaId, $anyoMemoriaEstudio, $puestoTrabajoId);

                $puestoTrabajoAo .= $grupoPuestoTrabajo . $puestoTrabajoDesc . " (" . $countPuestoTrabajoReconocimiento . " trabajadores) " . "</w:t>\n<w:br />\n<w:t xml:space=\"preserve\">";

                $countPuestoTrabajoAo++;
            }
            if (count($arrayAlteracionesOsteomuscularesPuesoTrabajo) == 0) {
                $item = array();
                $item['AO_PUESTO'] = "";
                $item['AO_PUESTO_1'] = "";
                $item['AO_PUESTO_2'] = "";
                $item['AO_PUESTO_3'] = "";
                $item['AO_PUESTO_4'] = "";
                $item['AO_PUESTO_5'] = "";
                array_push($arrayAlteracionesOsteomuscularesPuesoTrabajo, $item);
            }
            //$templateProcessor->cloneRowAndSetValues('AO_PUESTO', $arrayAlteracionesOsteomuscularesPuesoTrabajo);

            $templateProcessor->setValue('AO_GRUPO_PUESTO_TRABAJO', $puestoTrabajoAo);

            if (count($arrayAlteracionesOsteomuscularesPuesoTrabajo) > 0 && count($arrayAlteracionesOsteomuscularesPuesoTrabajo) > 0) {
                $filenameAlteracionOsteomuscularPuestoTrabajoGrupo = $this->crearGraficoAlteracionOsteomuscularPuestoTrabajoGrupo($empresaId, $arrayAlteracionesOsteomuscularesPuesoTrabajo);
                $templateProcessor->setImageValue('GRAFICO_ALTERACION_OSTEOMUSCULAR_GRUPO', array('path' => $filenameAlteracionOsteomuscularPuestoTrabajoGrupo, 'width' => 600, 'height' => 300, 'ratio' => false));
            } else {
                $templateProcessor->setValue('GRAFICO_ALTERACION_OSTEOMUSCULAR_GRUPO', "");
            }
            //Vacunados contra el COVID
            $cantidadTotal = $this->calcularVacunadosCovid($empresaId, $anyoMemoriaEstudio, false);
            $templateProcessor->setValue('VAC_COVID', $this->calcularPorcentajeVacunadosCovid($empresaId, $anyoMemoriaEstudio, $cantidadTotal));

            //Pruebas complementarias especificas SPT
            $templateProcessor->setValue('PRUEBAS_SPT', $this->calcularPruebasComplementariasSptTrabajador($empresaId, $anyoMemoriaEstudio));

            //Buscamos los datos del medico de la empresa
            $firmaMedico = "";
            $colegiadoMedico = "";
            $especialidadMedico = "";
            $gestoraMedico = "";
            if (!is_null($empresa->getVigilanciaSalud())) {
                if (!is_null($empresa->getVigilanciaSalud()->getMedico())) {
                    $doctorRevision = $empresa->getVigilanciaSalud()->getMedico()->getDescripcion();
                    $firmaMedico = $empresa->getVigilanciaSalud()->getMedico()->getFirma();
                    $colegiadoMedico = $empresa->getVigilanciaSalud()->getMedico()->getNumeroColegiado();
                    $especialidadMedico = $empresa->getVigilanciaSalud()->getMedico()->getEspecialidad();
                    $gestoraMedico = $empresa->getVigilanciaSalud()->getMedico()->getGestora();
                }
            }
            if ($firmaMedico != "") {
                $templateProcessor->setImageValue('MEDICO_FIRMA', 'upload/media/firmas/medico/' . $firmaMedico);
            } else {
                $templateProcessor->setValue('MEDICO_FIRMA', "");
            }
            $templateProcessor->setValue("MEDICO_NOMBRE", $doctorRevision);
            $templateProcessor->setValue('MEDICO_COLEGIADO', $colegiadoMedico);
            $templateProcessor->setValue('MEDICO_ESPECIALIDAD', $especialidadMedico);
            $templateProcessor->setValue('MEDICO_GESTORA', $gestoraMedico);
        }

        if ($restriccionSn) {
            $medicoRevision = "";
            if (!is_null($revision->getMedico())) {
                $medicoRevision = $revision->getMedico()->getDescripcion();
            }
            $templateProcessor->setValue('REVISION_DOCTOR', $medicoRevision);

            $fechaRevision = "";
            if (!is_null($revision->getFecha())) {
                $fechaRevision = $revision->getFecha()->format('d') . ' / ' . $revision->getFecha()->format('m') . ' / ' . $revision->getFecha()->format('Y');
            }
            $templateProcessor->setValue("FECHA_REVISION", $fechaRevision);
        }
        $templateProcessor->saveAs($urlNueva);

        return true;
    }

    function humanFileSize($size, $unit = "")
    {
        if ((!$unit && $size >= 1 << 30) || $unit == " GB")
            return number_format($size / (1 << 30), 2) . " GB";
        if ((!$unit && $size >= 1 << 20) || $unit == " MB")
            return number_format($size / (1 << 20), 2) . " MB";
        if ((!$unit && $size >= 1 << 10) || $unit == " KB")
            return number_format($size / (1 << 10), 2) . " KB";
        return number_format($size) . " bytes";
    }

    function obtenerMes($mes)
    {
        switch ($mes) {
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

    function calcularReconocimientos($empresaId, $year)
    {
        $query = "select count(*) as contador from revision where empresa_id = $empresaId and fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' and anulado = false";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result[0]['contador'];
    }

    function calcularAptitudesPorTipo($empresaId, $year, $respuesta)
    {
        $query = "select count(*) as contador from revision a 
        inner join revision_respuesta b on a.id = b.revision_id 
        where a.empresa_id = $empresaId 
        and a.fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' 
        and a.anulado = false
        and b.pregunta_id = 280
        and b.respuesta = '$respuesta'";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result[0]['contador'];
    }

    function calcularRecuentoPorAptitud($empresaId, $year, $aptoId)
    {
        $query = "select count(*) as contador from revision where empresa_id = $empresaId and fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' and anulado = false and apto_id = $aptoId";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result[0]['contador'];
    }

    function calcularRecuentoPorRestriccion($empresaId, $year, $restriccionId)
    {
        $query = "select count(*) as contador from revision where empresa_id = $empresaId and fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' and anulado = false and aptitud_restriccion_id = $restriccionId";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result[0]['contador'];
    }

    function calcularRestriccionesPorPuestoTrabajo($empresaId, $year, $puestoTrabajoId)
    {
        $query = "select count(*) as contador from revision where empresa_id = $empresaId and fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' and anulado = false and puesto_trabajo_id = $puestoTrabajoId and aptitud_restriccion_id is not null";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result[0]['contador'];
    }

    function calcularReconocimientosPorPuestoTrabajo($empresaId, $year, $puestoTrabajoId)
    {
        $query = "select count(*) as contador from revision where empresa_id = $empresaId and fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' and anulado = false and puesto_trabajo_id = $puestoTrabajoId";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result[0]['contador'];
    }

    function calcularReconocimientosPorTrabajador($empresaId, $year)
    {
        $query = "select distinct trabajador_id as contador from revision where empresa_id = $empresaId and fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' and anulado = false";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return count($result);
    }

    function calcularReconocimientosPorSexo($empresaId, $year, $sexo, $tipo)
    {
        $query = "select count(*) as contador from revision a
        inner join trabajador b on a.trabajador_id = b.id
        where a.empresa_id = $empresaId 
        and a.fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' 
        and a.anulado = false
        and b.sexo = $sexo ";

        if (!is_null($tipo)) {
            $query .= "and date_part('year',age(current_timestamp, b.fecha_nacimiento)) $tipo";
        }
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result[0]['contador'];
    }

    function calcularImcPorTipo($empresaId, $year, $tipo)
    {
        $query = "select count(*) as contador from revision a
        inner join revision_respuesta b on a.id = b.revision_id
        where a.empresa_id = $empresaId 
        and a.fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' 
        and a.anulado = false
        and b.pregunta_id = 92
        and b.respuesta != ''";

        switch ($tipo) {
                //Normopeso
            case 1:
                $query .= " and b.respuesta < '25'";
                break;
                //Sobrepeso
            case 2:
                $query .= " and b.respuesta between '25' and '30'";
                break;
                //Obesidad y Exceso de peso
            case 3:
            case 4:
                $query .= " and b.respuesta > '30'";
                break;
        }
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result[0]['contador'];
    }

    function calcularPorcentajeImcPorTipo($empresaId, $year, $tipo, $cantidadTotal)
    {
        //Calculamos la cantidad de registros
        $recuento = $this->calcularImcPorTipo($empresaId, $year, $tipo);

        //Calculamos el porcentaje final
        if (floatval($cantidadTotal) != 0) {
            $total = (100 * floatval($recuento)) / floatval($cantidadTotal);
        } else {
            $total = 0;
        }
        return round($total, 2);
    }

    function calcularTabacoPorTipo($empresaId, $year, $tipo)
    {
        $query = "select count(*) as contador from revision a
        inner join revision_respuesta b on a.id = b.revision_id
        where a.empresa_id = $empresaId 
        and a.fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' 
        and a.anulado = false
        and b.pregunta_id = 286
        and b.respuesta != ''";

        switch ($tipo) {
                //Ex-fumador
            case 1:
                $query .= " and b.respuesta like '%Ex fumador%'";
                break;
                //No fumador
            case 2:
                $query .= " and b.respuesta like '%No fumador%'";
                break;
                //Menos de 10 cig./día
            case 3:
                $query .= " and b.respuesta like '%menos de 10 cig./dia%'";
                break;
                //De 10 a 20 cig./día
            case 4:
                $query .= " and b.respuesta like '%entre 10 y 20 cig./dia%'";
                break;
                //Mas de 20 cig./día
            case 5:
                $query .= " and b.respuesta like '%mas de 20 cig./dia%'";
                break;
            case 6:
                $query .= " and b.respuesta != ';No fumador;'";
            default:
        }

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result[0]['contador'];
    }

    function calcularPorcentajeTabacoPorTipo($empresaId, $year, $tipo, $cantidadTotal)
    {
        //Calculamos la cantidad de registros
        $recuento = $this->calcularTabacoPorTipo($empresaId, $year, $tipo);

        //Calculamos el porcentaje final
        if (floatval($cantidadTotal) != 0) {
            $total = (100 * floatval($recuento)) / floatval($cantidadTotal);
        } else {
            $total = 0;
        }
        return round($total, 2);
    }

    function calcularHipertensionArterialTrabajador($empresaId, $year, $filtro)
    {
        $query = "select count(*) as contador from revision a 
        inner join revision_respuesta b on a.id = b.revision_id 
        where a.empresa_id = $empresaId 
        and a.fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' 
        and a.anulado = false
        and b.pregunta_id = 374 ";

        if ($filtro) {
            $query .= " and b.respuesta = 'Hipertensión arterial'";
        }
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result[0]['contador'];
    }

    function calcularPorcentajeHipertensionArterialTrabajador($empresaId, $year, $cantidadTotal)
    {
        //Calculamos la cantidad de registros
        $recuento = $this->calcularHipertensionArterialTrabajador($empresaId, $year, true);

        //Calculamos el porcentaje final
        if (floatval($cantidadTotal) != 0) {
            $total = (100 * floatval($recuento)) / floatval($cantidadTotal);
        } else {
            $total = 0;
        }
        return round($total, 2);
    }

    function calcularSedentarismoTrabajador($empresaId, $year, $filtro)
    {
        $query = "select count(*) as contador from revision a 
        inner join revision_respuesta b on a.id = b.revision_id 
        where a.empresa_id = $empresaId 
        and a.fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' 
        and a.anulado = false
        and b.pregunta_id = 288 ";

        if ($filtro) {
            $query .= " and b.respuesta = 'No'";
        }
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result[0]['contador'];
    }

    function calcularPorcentajeSedentarismoTrabajador($empresaId, $year, $cantidadTotal)
    {
        //Calculamos la cantidad de registros
        $recuento = $this->calcularSedentarismoTrabajador($empresaId, $year, true);

        //Calculamos el porcentaje final
        if (floatval($cantidadTotal) != 0) {
            $total = (100 * floatval($recuento)) / floatval($cantidadTotal);
        } else {
            $total = 0;
        }
        return round($total, 2);
    }

    function calcularAlteracionAudiometriaRuido($empresaId, $year, $tipo)
    {
        //fix Ticket#2024121110000036
        /*
        $query = "select count(*) as contador from revision a 
        inner join revision_respuesta b on a.id = b.revision_id 
        where a.empresa_id = $empresaId 
        and a.fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' 
        and a.anulado = false ";
        */
        $query = "select count(distinct a.id) as contador from revision a 
        inner join revision_respuesta b on a.id = b.revision_id 
        where a.empresa_id = $empresaId 
        and a.fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' 
        and a.anulado = false ";

        switch ($tipo) {
                //Expuesto a ruidos
            case 1:
                //fix Ticket#2024121110000036
                //$query .= " and a.trabajador_id in (select trabajador_id from citacion where anulado = false and empresa_id = $empresaId)";
                $query .= " and a.puesto_trabajo_id in (select puesto_trabajo_id from puesto_trabajo_protocolo where anulado = false and protocolo_id = 33 and empresa_id = $empresaId)";
                break;
                //No expuesto a ruidos
            case 2:
                //fix Ticket#2024121110000036
                //$query .= " and a.trabajador_id in (select trabajador_id from citacion where anulado = false and empresa_id = $empresaId)";
                $query .= " and a.puesto_trabajo_id not in (select puesto_trabajo_id from puesto_trabajo_protocolo where anulado = false and protocolo_id = 33 and empresa_id = $empresaId)";
                break;
                //Trabajadores con alteraciones expuestos a ruido
            case 3:
                $query .= " and a.puesto_trabajo_id in (select puesto_trabajo_id from puesto_trabajo_protocolo where anulado = false and protocolo_id = 33 and empresa_id = $empresaId)";
                $query .= " and a.apto_id = 2";
                break;
                //Trabajadores con alteraciones no expuestos a ruido
            case 4:
                $query .= " and a.puesto_trabajo_id not in (select puesto_trabajo_id from puesto_trabajo_protocolo where anulado = false and protocolo_id = 33 and empresa_id = $empresaId)";
                $query .= " and a.apto_id = 2";
                break;
        }
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result[0]['contador'];
    }

    function calcularPorcentajeAlteracionAudiometriaRuido($cantidadTotal, $recuento)
    {
        //Calculamos el porcentaje final
        if (floatval($cantidadTotal) != 0) {
            $total = (100 * floatval($recuento)) / floatval($cantidadTotal);
        } else {
            $total = 0;
        }
        return round($total, 2);
    }

    function calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $year, $filtro, $tipo)
    {
        $query = "select count(*) as contador from revision a 
        inner join revision_respuesta b on a.id = b.revision_id 
        where a.empresa_id = $empresaId 
        and a.fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' 
        and a.anulado = false
        and b.pregunta_id = 290
        and b.respuesta != '' ";

        if ($filtro) {
            switch ($tipo) {
                case 1:
                    $query .= " and b.respuesta like '%Para el colesterol%'";
                    break;
                case 2:
                    $query .= " and b.respuesta like '%Para el azucar%'";
                    break;
            }
        }
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result[0]['contador'];
    }

    function calcularPorcentajeHipercolesterolemiaDiabetesTrabajador($empresaId, $year, $cantidadTotal, $tipo)
    {
        //Calculamos la cantidad de registros
        switch ($tipo) {
            case 1:
                $recuento = $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $year, true, 1);
                break;
            case 2:
                $recuento = $this->calcularHipercolesterolemiaDiabetesTrabajador($empresaId, $year, true, 2);
                break;
        }

        //Calculamos el porcentaje final
        if (floatval($cantidadTotal) != 0) {
            $total = (100 * floatval($recuento)) / floatval($cantidadTotal);
        } else {
            $total = 0;
        }
        return round($total, 2);
    }

    function calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $year, $filtro, $tipo)
    {
        if ($filtro) {
            $filtro = " and b.respuesta in ('1','2','3','4','5')";
        } else {
            $filtro = "";
        }

        $query = "select count(distinct a.id) as contador from revision a 
        inner join revision_respuesta b on a.id = b.revision_id 
        where a.empresa_id = $empresaId 
        and a.fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' 
        and a.anulado = false
        and b.respuesta != '' $filtro ";

        switch ($tipo) {
            case 1:
                $query .= " and b.pregunta_id in (246,247)";
                break;
            case 2:
                $query .= " and b.pregunta_id in (242,243)";
                break;
            case 3:
                $query = "SELECT COUNT(*) AS contador
                            FROM (
                                SELECT a.trabajador_id
                                FROM revision a 
                                INNER JOIN revision_respuesta b ON a.id = b.revision_id 
                                WHERE a.empresa_id = $empresaId
                                  AND a.fecha BETWEEN '$year-01-01 00:00:00' AND '$year-12-31 23:59:59' 
                                  AND a.anulado = false
                                  AND b.respuesta != ''
                                  $filtro
                                  AND b.pregunta_id IN (242, 243, 246, 247)
                                GROUP BY a.trabajador_id
                                HAVING 
                                  SUM(CASE WHEN b.pregunta_id IN (242, 243) THEN 1 ELSE 0 END) > 0
                                  AND SUM(CASE WHEN b.pregunta_id IN (246, 247) THEN 1 ELSE 0 END) > 0 
                            ) AS subquery;";
                break;
        }
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result[0]['contador'];
    }

    function calcularPorcentajeAgudezaVisualCercanaLejanaTrabajador($empresaId, $year, $cantidadTotal, $tipo)
    {
        //Calculamos la cantidad de registros
        switch ($tipo) {
            case 1:
                $recuento = $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $year, true, 1);
                break;
            case 2:
                $recuento = $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $year, true, 2);
                break;
            case 3:
                $recuento = $this->calcularAgudezaVisualCercanaLejanaTrabajador($empresaId, $year, true, 3);
                break;
        }

        //Calculamos el porcentaje final
        if (floatval($cantidadTotal) != 0) {
            $total = (100 * floatval($recuento)) / floatval($cantidadTotal);
        } else {
            $total = 0;
        }
        return round($total, 2);
    }

    function calcularAlteracionOsteomuscularTrabajador($empresaId, $year, $filtro,  $tipo, $puestoTrabajoId)
    {
        $query = "select count(*) as contador from revision a 
        inner join revision_respuesta b on a.id = b.revision_id
        inner join puesto_trabajo_centro c on a.puesto_trabajo_id = c.id
        where a.empresa_id = $empresaId 
        and a.fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' 
        and a.anulado = false
        and b.respuesta != '' ";

        switch ($tipo) {
            case 1:
                $query .= " and b.pregunta_id = 354";
                if ($filtro) {
                    $query .= " and b.respuesta != ';Movilidad columna vertebral conservada, no se aprecian contracturas ni puntos dolorosos.;'";
                }
                break;
            case 2:
                $query .= " and b.pregunta_id = 351";
                if ($filtro) {
                    $query .= " and b.respuesta != ';Fuerza y sensibilidad conservada.;'";
                }
                break;
            case 3:
                $query .= " and b.pregunta_id = 353";
                if ($filtro) {
                    $query .= " and b.respuesta != ';Ausencia de hallazgos patológicos. Movilidad normal.;'";
                }
                break;
            case 4:
                $query .= " and b.pregunta_id = 350";
                if ($filtro) {
                    $query .= " and b.respuesta != ';No se observan deformidades ni signos articulares anormales, no se aprecian distrofias, movilidad conservada;'";
                }
                break;
            case 5:
                $query .= " and b.pregunta_id = 352";
                if ($filtro) {
                    $query .= " and b.respuesta != ';No se observan deformidades;'";
                }
                break;
        }
        if (!is_null($puestoTrabajoId)) {
            $query .= " and c.id = $puestoTrabajoId";
        }
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result[0]['contador'];
    }

    function calcularPorcentajeAlteracionOsteomuscular($empresaId, $year, $cantidadTotal, $tipo, $puestoTrabajoId)
    {
        //Calculamos la cantidad de registros
        $recuento = $this->calcularAlteracionOsteomuscularTrabajador($empresaId, $year, true, $tipo, $puestoTrabajoId);

        //Calculamos el porcentaje final
        if (floatval($cantidadTotal) != 0) {
            $total = (100 * floatval($recuento)) / floatval($cantidadTotal);
        } else {
            $total = 0;
        }
        return round($total, 2);
    }

    function calcularVacunadosCovid($empresaId, $year, $filtro)
    {
        $query = "select count(*) as contador from revision a 
        inner join revision_respuesta b on a.id = b.revision_id
        inner join puesto_trabajo_centro c on a.puesto_trabajo_id = c.id
        where a.empresa_id = $empresaId 
        and a.fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' 
        and a.anulado = false
        and b.respuesta != ''
        and b.pregunta_id = 291 ";

        if ($filtro) {
            $query .= " and b.respuesta like '%;Covid-19;%'";
        }
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result[0]['contador'];
    }

    function calcularPorcentajeVacunadosCovid($empresaId, $year, $cantidadTotal)
    {
        //Calculamos la cantidad de registros
        $recuento = $this->calcularVacunadosCovid($empresaId, $year, true);

        //Calculamos el porcentaje final
        if (floatval($cantidadTotal) != 0) {
            $total = (100 * floatval($recuento)) / floatval($cantidadTotal);
        } else {
            $total = 0;
        }
        return round($total, 2);
    }

    function calcularPruebasComplementariasSptTrabajador($empresaId, $year)
    {
        $query = "select count(*) as contador from revision a
        inner join revision_respuesta b on a.id = b.revision_id
        where a.empresa_id = $empresaId 
        and a.fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' 
        and a.anulado = false
        and (a.analitica is true or a.electrocardiograma is not null)
        union all 
        select count(*) as contador from revision a
        inner join revision_respuesta b on a.id = b.revision_id
        where a.empresa_id = $empresaId 
        and a.fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59' 
        and a.anulado = false
        and b.pregunta_id = 362 
        and b.respuesta != ''";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result[0]['contador'];
    }

    function convertWordToPdf($word, $pdf)
    {
        $cmd = 'soffice --headless --convert-to pdf:writer_pdf_Export "' . $word . '" --outdir "' . $pdf . '"';
        exec($cmd);
    }

    public function enviarAvisoMultipleFactura(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getAddVencimientoFacturacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();
        $mail = $usuario->getMail();
        $emailUser = $usuario->getEmail();
        $passwordMail = $usuario->getPasswordMail();
        $hostMail = $usuario->getHostMail();
        $puertoMail = $usuario->getPuertoMail();
        $encriptacionMail = $usuario->getEncriptacionMail();
        $userMail = $usuario->getUserMail();

        $facturasSelect = $_REQUEST['facturas'];
        $facturasSelectArray = explode(",", $facturasSelect);

        $object = array("json" => $username, "entidad" => "Pulsa botón Generar fichero y avisar al cliente factura", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $object, $usuario, TRUE);
        $em->flush();

        $funcionEnvioFactura = $this->getDoctrine()->getRepository('App\Entity\FuncionCorreo')->find(2);

        $arrayEnviarAvisoFacturaId = array();
        $facturasNoEnviadas = "";
        $em->beginTransaction();

        try {
            foreach ($facturasSelectArray as $fs) {
                $factura = $em->getRepository('App\Entity\Facturacion')->find($fs);
                $return = $this->enviarFacturaCliente($em, $factura, $usuario, $funcionEnvioFactura, $mail, $passwordMail, $puertoMail, $hostMail, $encriptacionMail);
                if (!$return) {
                    $facturasNoEnviadas .= $factura->getEmpresa()->getEmpresa() . " , ";
                } else {
                    array_push($arrayEnviarAvisoFacturaId, $factura->getId());
                }
            }
        } catch (\Exception $e) {
            $em->rollBack();
            $traduccion = $translator->trans('TRANS_AVISO_ERROR');
            $this->addFlash('danger', $traduccion);
            return $this->redirectToRoute('factura_show_vencimientos');
        }
        $em->commit();

        //Buscamos la configuración de la gestión documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $rutaGestionDocumental = $gdocConfig->getRuta();
        $carpetaTemporal = $gdocConfig->getCarpetaTemporal();

        $transport = new \Swift_SmtpTransport($hostMail, $puertoMail, $encriptacionMail);
        $transport->setUsername($userMail);
        $transport->setPassword($passwordMail);
        $transport->setHost($hostMail);
        $transport->setAuthMode('login');

        $mailer = new \Swift_Mailer($transport);
        $contador = 0;
        //Enviamos el aviso al cliente
        foreach ($arrayEnviarAvisoFacturaId as $aeafe) {
            $transport = new \Swift_SmtpTransport($hostMail, $puertoMail, $encriptacionMail);
            $transport->setUsername($userMail);
            $transport->setPassword($passwordMail);
            $transport->setHost($hostMail);
            $transport->setAuthMode('login');

            $mailer = new \Swift_Mailer($transport);
            $factura = $em->getRepository('App\Entity\Facturacion')->find($aeafe);
            $fichero = $factura->getFichero();

            $correoEmpresa = $this->getDoctrine()->getRepository('App\Entity\CorreoEmpresa')->findOneBy(array('funcion' => $funcionEnvioFactura, 'anulado' => false, 'empresa' => $factura->getEmpresa()));
            $to = $correoEmpresa->getCorreo();

            $nombrePlantillaFacturaPdf = str_replace('docx', 'pdf', $fichero->getNombre());
            $filePdfEncriptado = $rutaGestionDocumental . $carpetaTemporal . '/' . $nombrePlantillaFacturaPdf;
            //$filePdfEncriptado = "C:/tmp/".$nombrePlantillaFacturaPdf;
            try {
                $this->sendAvisoNuevaFactura(trim($to), $mailer, $mail, $em, $usuario, $filePdfEncriptado, null, $emailUser, null, null);
                $factura->setEnviada(true);
                $factura->setFechaEnvio(new \DateTime());
                $em->persist($factura);
                $em->flush();
                $mailer->getTransport()->stop();
            } catch (\Exception $e) {
                $facturasNoEnviadas .= $factura->getEmpresa()->getEmpresa() . " , ";

                $mailer->getTransport()->stop();
            }
            //Antiguo sleep para evitar colapso envio mail, pero satura servidor
            //$contador = $contador + 1;
            //if($contador == 100)
            //{
            //sleep(10);
            //$contador = 0;
            //}
        }
        if ($facturasNoEnviadas != "") {
            $traduccion = $translator->trans('TRANS_ENVIAR_FACTURA_ERROR_EMPRESA');
            $this->addFlash('danger', $traduccion . " " . $facturasNoEnviadas);
        }
        $traduccion = $translator->trans('TRANS_FACTURAR_OK');
        $this->addFlash('success', $traduccion);
        return $this->redirectToRoute('factura_show');
    }

    public function enviarAvisoMultipleFacturaRevision(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getAddVencimientoFacturacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $mail = $usuario->getMail();
        $emailUser = $usuario->getEmail();
        $passwordMail = $usuario->getPasswordMail();
        $hostMail = $usuario->getHostMail();
        $puertoMail = $usuario->getPuertoMail();
        $encriptacionMail = $usuario->getEncriptacionMail();
        $userMail = $usuario->getUserMail();

        $revisionesSelectArray = array();

        if (isset($_REQUEST['revisiones'])) {
            $revisionesSelectArray = explode(",", $_REQUEST['revisiones']);
        }
        if (isset($_REQUEST['id'])) {
            array_push($revisionesSelectArray, $_REQUEST['id']);
        }
        //Revisamos las revisiones que no se han enviado aun
        $arrayRevisionesEnviar = array();
        foreach ($revisionesSelectArray as $revId) {
            $revision = $this->getDoctrine()->getRepository('App\Entity\Revision')->find($revId);
            if (!is_null($revision->getFactura())) {
                $factura = $revision->getFactura();
                if (!$factura->getEnviada()) {
                    array_push($arrayRevisionesEnviar, $revId);
                }
            }
        }
        $funcionEnvioFactura = $this->getDoctrine()->getRepository('App\Entity\FuncionCorreo')->find(2);

        $arrayEnviarAvisoFacturaId = array();
        $facturasNoEnviadas = "";
        $em->beginTransaction();

        try {
            foreach ($arrayRevisionesEnviar as $rs) {
                $revision = $em->getRepository('App\Entity\Revision')->find($rs);
                if (!is_null($revision->getFactura())) {
                    $factura = $revision->getFactura();
                    if (!$factura->getEnviada()) {
                        $return = $this->enviarFacturaCliente($em, $factura, $usuario, $funcionEnvioFactura, $mail, $passwordMail, $puertoMail, $hostMail, $encriptacionMail);
                        if (!$return) {
                            $facturasNoEnviadas .= $factura->getEmpresa()->getEmpresa() . " , ";
                        } else {
                            array_push($arrayEnviarAvisoFacturaId, $factura->getId());
                        }
                    }
                }
            }
            $em->commit();
        } catch (\Exception $e) {
            $em->rollBack();
            $traduccion = $translator->trans('TRANS_ENVIAR_CORREO_ERROR');
            $this->addFlash('danger', $traduccion);
            return $this->redirectToRoute('dashboard_admin');
        }

        //Buscamos la configuración de la gestión documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $rutaGestionDocumental = $gdocConfig->getRuta();
        $carpetaTemporal = $gdocConfig->getCarpetaTemporal();

        //Enviamos el aviso al cliente
        foreach ($arrayEnviarAvisoFacturaId as $aeafe) {
            $transport = new \Swift_SmtpTransport($hostMail, $puertoMail, $encriptacionMail);
            $transport->setUsername($userMail);
            $transport->setPassword($passwordMail);
            $transport->setHost($hostMail);
            $transport->setAuthMode('login');

            $mailer = new \Swift_Mailer($transport);

            $factura = $em->getRepository('App\Entity\Facturacion')->find($aeafe);
            $fichero = $factura->getFichero();

            $correoEmpresa = $this->getDoctrine()->getRepository('App\Entity\CorreoEmpresa')->findOneBy(array('funcion' => $funcionEnvioFactura, 'anulado' => false, 'empresa' => $factura->getEmpresa()));
            $to = $correoEmpresa->getCorreo();

            $nombrePlantillaFacturaPdf = str_replace('docx', 'pdf', $fichero->getNombre());
            $filePdfEncriptado = $rutaGestionDocumental . $carpetaTemporal . '/' . $nombrePlantillaFacturaPdf;
            //$filePdfEncriptado = "C:/tmp/".$nombrePlantillaFacturaPdf;
            if (!$factura->getEnviada()) {
                $this->sendAvisoNuevaFacturaRevision(trim($to), $mailer, $mail, $em, $usuario, $filePdfEncriptado, null, $emailUser, null, null);

                $factura->setEnviada(true);
                $factura->setFechaEnvio(new \DateTime());
                $em->persist($factura);
                $em->flush();
            }
        }
        if ($facturasNoEnviadas != "") {
            $traduccion = $translator->trans('TRANS_ENVIAR_FACTURA_ERROR_EMPRESA');
            $this->addFlash('danger', $traduccion . " " . $facturasNoEnviadas);
        }
        $traduccion = $translator->trans('TRANS_FACTURAR_OK');
        $this->addFlash('success', $traduccion);
        return $this->redirectToRoute('dashboard_admin');
    }

    public function enviarAvisoMultipleFacturaManual(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getSendFacturacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $mail = $usuario->getMail();
        $emailUser = $usuario->getEmail();
        $passwordMail = $usuario->getPasswordMail();
        $hostMail = $usuario->getHostMail();
        $puertoMail = $usuario->getPuertoMail();
        $encriptacionMail = $usuario->getEncriptacionMail();
        $userMail = $usuario->getUserMail();

        $facturasSelectArray = $session->get('facturasSeleccionadasMultiple');

        $funcionEnvioFactura = $this->getDoctrine()->getRepository('App\Entity\FuncionCorreo')->find(2);

        $form = $this->createForm(EnviarFacturaMultipleType::class, null, array('destinatario' => null, 'cco' => $emailUser));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            //Recogemos lo datos
            $cc = $form["cc"]->getData();
            $cco = $form["cco"]->getData();
            $asunto = $form["asunto"]->getData();
            $mensaje = $form["mensaje"]->getData();

            $arrayEnviarAvisoFacturaId = array();
            $facturasNoEnviadas = "";

            $em->beginTransaction();
            try {
                foreach ($facturasSelectArray as $fs) {
                    $factura = $em->getRepository('App\Entity\Facturacion')->find($fs);
                    $return = $this->enviarFacturaCliente($em, $factura, $usuario, $funcionEnvioFactura, $mail, $passwordMail, $puertoMail, $hostMail, $encriptacionMail);
                    if (!$return) {
                        $facturasNoEnviadas .= $factura->getEmpresa()->getEmpresa() . " , ";
                    } else {
                        array_push($arrayEnviarAvisoFacturaId, $factura->getId());
                    }
                }
            } catch (\Exception $e) {
                $em->rollBack();
                $traduccion = $translator->trans('TRANS_AVISO_ERROR');
                $this->addFlash('danger', $traduccion);
                return $this->redirectToRoute('factura_enviar_aviso_factura_manual');
            }
            $em->commit();

            //Buscamos la configuración de la gestión documental
            $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
            $rutaGestionDocumental = $gdocConfig->getRuta();
            $carpetaTemporal = $gdocConfig->getCarpetaTemporal();

            //Enviamos el aviso al cliente
            foreach ($arrayEnviarAvisoFacturaId as $aeafe) {
                $transport = new \Swift_SmtpTransport($hostMail, $puertoMail, $encriptacionMail);
                $transport->setUsername($userMail);
                $transport->setPassword($passwordMail);
                $transport->setHost($hostMail);
                $transport->setAuthMode('login');

                $mailer = new \Swift_Mailer($transport);

                $factura = $em->getRepository('App\Entity\Facturacion')->find($aeafe);
                $fichero = $factura->getFichero();

                $correoEmpresa = $this->getDoctrine()->getRepository('App\Entity\CorreoEmpresa')->findOneBy(array('funcion' => $funcionEnvioFactura, 'anulado' => false, 'empresa' => $factura->getEmpresa()));
                $to = $correoEmpresa->getCorreo();

                $nombrePlantillaFacturaPdf = str_replace('docx', 'pdf', $fichero->getNombre());
                $filePdfEncriptado = $rutaGestionDocumental . $carpetaTemporal . '/' . $nombrePlantillaFacturaPdf;
                //$filePdfEncriptado = "C:/tmp/".$nombrePlantillaFacturaPdf;
                try {
                    $this->sendAvisoNuevaFactura(trim($to), $mailer, $mail, $em, $usuario, $filePdfEncriptado, $cc, $cco, $asunto, $mensaje);
                    $factura->setEnviada(true);
                    $factura->setFechaEnvio(new \DateTime());
                    $em->persist($factura);
                    $em->flush();
                    $mailer->getTransport()->stop();
                } catch (\Exception $e) {
                    $facturasNoEnviadas .= $factura->getEmpresa()->getEmpresa() . " , ";

                    $mailer->getTransport()->stop();
                }
            }
            if ($facturasNoEnviadas != "") {
                $traduccion = $translator->trans('TRANS_ENVIAR_FACTURA_ERROR_EMPRESA');
                $this->addFlash('danger', $traduccion . " " . $facturasNoEnviadas);
            }
            $traduccion = $translator->trans('TRANS_FACTURAR_OK');
            $this->addFlash('success', $traduccion);
            return $this->redirectToRoute('factura_show');
        }
        return $this->render('facturacion/send_multiple.html.twig', array('form' => $form->createView()));
    }

    function enviarFacturaCliente($em, $factura, $usuario, $funcionEnvioFactura, $mail, $passwordMail, $puertoMail, $hostMail, $encriptacionMail)
    {
        $formaPago = $factura->getFormaPago();

        if ($formaPago->getFormaPagoContable() == 8) {
            $plantillaId = 63;
        } else if ($formaPago->getFormaPagoContable() == 1) {
            $plantillaId = 64;
        } else {
            $plantillaId = 62;
        }
        $plantilla = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->find($plantillaId);
        $nombreCompleto = $plantilla->getNombreCompleto();
        $nombrePlantilla = $plantilla->getNombre();

        //Buscamos la configuración de la gestión documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $rutaGestionDocumental = $gdocConfig->getRuta();
        $carpetaPlantillaGenerada = $gdocConfig->getCarpetaFactura();
        $carpetaPlantillas = $gdocConfig->getCarpetaPlantillas();
        $carpetaTemporal = $gdocConfig->getCarpetaTemporal();
        $passwordUsuario = $gdocConfig->getPasswordFicherosEncriptados();

        //Recuperamos los datos de la factura
        $empresa = $factura->getEmpresa();
        $nombreEmpresa = $empresa->getEmpresa();
        $nombreEmpresa = rtrim($nombreEmpresa, ".");
        $nombreEmpresa = $this->eliminar_tildes($nombreEmpresa);
        $numFac = $factura->getSerie()->getSerie() . $factura->getNumFac();
        $numFac = str_replace('/', '-', $numFac);
        $nuevaPlantilla = 'Factura ' . $numFac . ' de ' . $nombreEmpresa . '.docx';

        //Generamos el nuevo fichero a partir de la plantilla
        //RUTA PROD
        $urlPlantilla = $rutaGestionDocumental . $carpetaPlantillas . '/' . $nombreCompleto;
        $urlNueva = $rutaGestionDocumental . $carpetaPlantillaGenerada . '/' . $nuevaPlantilla;
        //RUTA LOCAL
        //$urlPlantilla = "C:/tmp/".$nombreCompleto;
        //$urlNueva = "C:/tmp/".$nuevaPlantilla;

        $return = $this->replaceTags("0", $em, $urlPlantilla, $urlNueva, '2', $empresa, null, $factura, null, null, null, null, null, null, null, null);

        if ($return) {
            //Creamos el registro
            $gdocFichero = new GdocFichero();
            $gdocFichero->setEmpresa($empresa);
            $gdocFichero->setDtcrea(new \DateTime());
            $gdocFichero->setUsuario($usuario);
            $gdocFichero->setNombre($nuevaPlantilla);
            $gdocFichero->setAnulado(false);
            $gdocFichero->setPlantilla($plantilla);
            $em->persist($gdocFichero);
            $em->flush();

            $factura->setFichero($gdocFichero);
            $em->persist($factura);
            $em->flush();

            //Buscamos si la empresa tiene un usuario para acceder al portal de empresa
            $correoEmpresa = $this->getDoctrine()->getRepository('App\Entity\CorreoEmpresa')->findOneBy(array('funcion' => $funcionEnvioFactura, 'anulado' => false, 'empresa' => $empresa));

            if (!is_null($correoEmpresa)) {
                $to = $correoEmpresa->getCorreo();
                if (!is_null($to)) {
                    //Enviamos el aviso por correo
                    if (!is_null($mail) || !is_null($passwordMail) || !is_null($puertoMail) || !is_null($hostMail) || !is_null($encriptacionMail)) {

                        //Convertimos el word en pdf
                        //RUTA PROD
                        $fileDocx = $rutaGestionDocumental . $carpetaPlantillaGenerada . '/' . $nuevaPlantilla;
                        //RUTA LOCAL
                        //$fileDocx = "C:/tmp/".$nuevaPlantilla;

                        $filePdf = str_replace('docx', 'pdf', $fileDocx);
                        //RUTA PROD
                        $outdir = $rutaGestionDocumental . $carpetaPlantillaGenerada;
                        //RUTA LOCAL
                        //$outdir = "C:/tmp/";

                        //RUTA PROD
                        $cmd = 'soffice --headless --convert-to pdf:writer_pdf_Export "' . $fileDocx . '" --outdir "' . $outdir . '"';
                        //RUTA LOCAL
                        //$cmd = '"C:\Program Files (x86)\LibreOffice 5\program\soffice.exe" --headless --convert-to pdf:writer_pdf_Export "'.$fileDocx.'" --outdir "'.$outdir.'"';
                        exec($cmd);

                        //Encriptamos el documento
                        $passwordOwner = $factura->getPasswordPdf();
                        if (is_null($passwordOwner)) {
                            $facturacionId = $factura->getId();
                            $passwordOwner = hash('sha256', 'OpenticPrevencion' . $facturacionId);
                            $factura->setPasswordPdf($passwordOwner);
                        }
                        $em->persist($factura);
                        $em->flush();

                        $fichero = $factura->getFichero();

                        $nombrePlantillaRestriccionPdf = str_replace('docx', 'pdf', $fichero->getNombre());
                        //RUTA PROD
                        $filePdfEncriptado = $rutaGestionDocumental . $carpetaTemporal . '/' . $nombrePlantillaRestriccionPdf;
                        //RUTA LOCAL
                        //$filePdfEncriptado = "C:/tmp/".$nombrePlantillaRestriccionPdf;

                        $this->encriptarPdf($filePdfEncriptado, $filePdf, $passwordOwner, $passwordUsuario);

                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function encriptarPdf($filePdfEncriptado, $filePdf, $passwordOwner, $passwordUsuario)
    {
        // Copiamos el PDF sin encriptar
        copy($filePdf, $filePdfEncriptado);
    }

    function sendAvisoNuevaFactura($to, $mailer, $mail, $em, $usuario, $filePdf, $cc, $cco, $asunto, $mensaje)
    {
        //Enviamos el mail al cliente
        $message = new \Swift_Message();

        if (!is_null($cc)) {
            $message->setCc(explode(";", $cc));
        }
        if (!is_null($cco)) {
            $message->setBcc(explode(";", $cco));
            $message->setReplyTo(explode(";", $cco));
        }
        if (!is_null($asunto)) {
            $message->setSubject($asunto);
        } else {
            $message->setSubject("Nueva factura disponible");
        }
        if (!is_null($mensaje)) {
            $message->setBody($mensaje, 'text/plain');
        } else {
            $message->setBody(
                $this->renderView(
                    // templates/emails/registration.html.twig
                    'emails/send_aviso_factura.html.twig',
                    ['email' => $to]
                ),
                'text/plain'
            );
        }
        $message->setFrom($mail);
        $message->setTo($to);
        $message->attach(\Swift_Attachment::fromPath($filePdf));
        //sleep(1);
        $mailer->send($message);
        //Insertamos el correo en el log
        $this->insertLogMail($em, $usuario, "Nueva factura disponible", $to, $message->getBody(), "Nueva factura disponible");
        $mailer->getTransport()->stop();
    }

    function sendAvisoNuevaFacturaRevision($to, $mailer, $mail, $em, $usuario, $filePdf, $cc, $cco, $asunto, $mensaje)
    {
        //Enviamos el mail al cliente
        $message = new \Swift_Message();

        if (!is_null($cc)) {
            $message->setCc(explode(";", $cc));
        }
        if (!is_null($cco)) {
            $message->setBcc(explode(";", $cco));
            $message->setReplyTo(explode(";", $cco));
        }
        if (!is_null($asunto)) {
            $message->setSubject($asunto);
        } else {
            $message->setSubject("Nueva factura disponible");
        }
        if (!is_null($mensaje)) {
            $message->setBody($mensaje, 'text/plain');
        } else {
            $message->setBody(
                $this->renderView(
                    // templates/emails/registration.html.twig
                    'emails/send_aviso_factura_revision.html.twig',
                    ['email' => $to]
                ),
                'text/plain'
            );
        }
        $message->setFrom($mail);
        $message->setTo($to);
        $message->attach(\Swift_Attachment::fromPath($filePdf));

        $mailer->send($message);

        //Insertamos el correo en el log
        $this->insertLogMail($em, $usuario, "Nueva factura disponible", $to, $message->getBody(), "Nueva factura disponible");
        $mailer->getTransport()->stop();
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

    function crearGraficoAudiometria($revisionId, $oidoD, $oidoI)
    {
        $hoy = new \DateTime();
        $hoyString = $hoy->format('YmdHis');

        $ruta = 'pChart/fonts';
        /* Create the pChart object */
        $myPicture = new pDraw(700, 230);

        /* Populate the pData object */
        $myPicture->myData->addPoints($oidoD, "Oído derecho");
        $myPicture->myData->addPoints($oidoI, "Oído izquierdo");
        $myPicture->myData->setAxisName(0, "Values");
        $myPicture->myData->negateValues(array('Oído derecho', 'Oído izquierdo'));
        $myPicture->myData->setAxisDisplay(0, AXIS_FORMAT_CUSTOM, "NegateValuesDisplay");
        $myPicture->myData->addPoints(["500", "1000", "2000", "3000", "4000", "6000", "8000"], "Labels");
        $myPicture->myData->setSerieDescription("Labels", "Months");
        $myPicture->myData->setAbscissa("Labels");

        /* Turn off Anti-aliasing */
        //        $myPicture->setAntialias(FALSE);

        /* Add a border to the picture */
        $myPicture->drawRectangle(0, 0, 699, 229, ["Color" => new pColor(0, 0, 0)]);

        /* Write the chart title */
        $myPicture->setFontProperties(["FontName" => "$ruta/Cairo-Regular.ttf", "FontSize" => 11]);
        $myPicture->drawText(150, 35, "Audiometría", ["FontSize" => 20, "Align" => TEXT_ALIGN_BOTTOMMIDDLE]);

        /* Set the default font */
        $myPicture->setFontProperties(["FontSize" => 7]);

        /* Define the chart area */
        $myPicture->setGraphArea(60, 40, 650, 200);

        /* Draw the scale */
        $myPicture->drawScale(["XMargin" => 10, "YMargin" => 10, "Floating" => TRUE, "GridColor" => new pColor(0, 0, 0), "DrawSubTicks" => TRUE, "CycleBackground" => TRUE]);

        /* Turn on Anti-aliasing */
        //        $myPicture->setAntialias(TRUE);

        /* Draw the line chart */
        (new pCharts($myPicture))->drawLineChart();

        /* Write the chart legend */
        $myPicture->drawLegend(540, 20, ["Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL]);
        $filename = "upload/media/audiometria_img/Audiometria_" . $revisionId . "_" . $hoyString . ".png";
        $myPicture->render($filename);

        return $filename;
    }

    function NegateValuesDisplay($Value)
    {
        return ($Value == VOID) ? VOID : -$Value;
    }

    function crearGraficoEspirometria($revisionId, $valor1, $valor2)
    {
        $hoy = new \DateTime();
        $hoyString = $hoy->format('YmdHis');

        $ruta = 'pChart/fonts';
        /* Create the pChart object */
        $myPicture = new pDraw(700, 230);

        /* Populate the pData object */
        $myPicture->myData->addPoints([$valor1[0], VOID], "FVC (%)");
        $myPicture->myData->addPoints([VOID, $valor2[0]], "FEV1 / FVC (%)");
        $myPicture->myData->addPoints(["FVC (%)", "FEV1 / FVC (%)"], "Value");
        $myPicture->myData->setSerieDescription("Value", "Month");
        $myPicture->myData->setAbscissa("Value");

        /* Turn off Anti-aliasing */
        //        $myPicture->setAntialias(FALSE);

        /* Add a border to the picture */
        $myPicture->drawRectangle(0, 0, 699, 229, ["Color" => new pColor(0, 255, 255)]);

        $myPicture->setFontProperties(["FontName" => "$ruta/Cairo-Regular.ttf", "FontSize" => 11]);
        $myPicture->drawText(150, 35, "Cuadrante de Miller", ["FontSize" => 20, "Align" => TEXT_ALIGN_BOTTOMMIDDLE]);

        /* Set the default font */
        $myPicture->setFontProperties(["FontName" => "$ruta/Cairo-Regular.ttf", "FontSize" => 7]);

        /* Define the chart area */
        $myPicture->setGraphArea(60, 40, 650, 200);

        /* Draw the scale */
        $myPicture->drawScale(["GridColor" => new pColor(200, 200, 200), "DrawSubTicks" => TRUE, "CycleBackground" => TRUE]);

        /* Write the chart legend */
        $myPicture->drawLegend(580, 12, ["Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL]);

        /* Turn on shadow computing */
        $myPicture->setShadow(TRUE, ["X" => 1, "Y" => 1, "Color" => new pColor(0, 0, 0, 10)]);

        /* Draw the chart */
        (new pCharts($myPicture))->drawBarChart([
            "Gradient" => TRUE,
            "GradientMode" => GRADIENT_EFFECT_CAN,
            "DisplayPos" => LABEL_POS_INSIDE,
            "DisplayValues" => TRUE,
            "DisplayColor" => new pColor(255, 255, 255),
            "DisplayShadow" => TRUE,
            "Surrounding" => 10
        ]);

        $filenameEspirometria = "upload/media/espirometria_img/Espirometria_" . $revisionId . "_" . $hoyString . ".png";
        $myPicture->render($filenameEspirometria);

        return $filenameEspirometria;
    }

    function crearGraficoProtocolosEstudios($empresaId, $labels, $valores)
    {
        $hoy = new \DateTime();
        $hoyString = $hoy->format('YmdHis');

        $ruta = 'pChart/fonts';
        /* Create the pChart object */
        $myPicture = new pDraw(700, 700);

        /* Populate the pData object */
        $myPicture->myData->addPoints($valores, "ScoreA");
        $myPicture->myData->setSerieDescription("ScoreA", "Application A");

        /* Define the abscissa serie */
        $myPicture->myData->addPoints($labels, "Labels");
        $myPicture->myData->setAbscissa("Labels");

        /* Draw a solid background */
        $myPicture->drawFilledRectangle(0, 0, 700, 700, ["Color" => new pColor(170, 183, 87), "Dash" => TRUE, "DashColor" => new pColor(190, 203, 107)]);

        /* Overlay with a gradient */
        $myPicture->drawGradientArea(0, 0, 700, 650, DIRECTION_VERTICAL, ["StartColor" => new pColor(219, 231, 139, 50), "EndColor" => new pColor(1, 138, 68, 50)]);
        $myPicture->drawGradientArea(0, 0, 700, 20, DIRECTION_VERTICAL, ["StartColor" => new pColor(0, 0, 0), "EndColor" => new pColor(50, 50, 50)]);

        /* Add a border to the picture */
        $myPicture->drawRectangle(0, 0, 700, 649, ["Color" => new pColor(0, 0, 0)]);

        /* Write the picture title */
        $myPicture->setFontProperties(["FontName" => "$ruta/PressStart2P-Regular.ttf", "FontSize" => 6]);
        $myPicture->drawText(10, 15, "Protocolos aplicados", ["Color" => new pColor(206, 206, 206)]);

        /* Set the default font properties */
        $myPicture->setFontProperties(["FontName" => "$ruta/Cairo-Regular.ttf", "FontSize" => 9, "Color" => new pColor(80, 80, 80)]);

        /* Enable shadow computing */
        $myPicture->setShadow(TRUE, ["X" => 2, "Y" => 2, "Color" => new pColor(0, 0, 0, 50)]);

        /* Create the pPie object */
        $PieChart = new pPie($myPicture);

        /* Draw an AA pie chart */
        $PieChart->draw2DRing(340, 320, ["DrawLabels" => TRUE, "LabelStacked" => TRUE, "Border" => TRUE, "OuterRadius" => 110, "InnerRadius" => 20]);

        /* Write the legend box */
        /*
        $myPicture->setShadow(FALSE);
        $PieChart->drawPieLegend(15, 40, ["Color" => new pColor(200, 200, 200, 20)]);
        */
        $filename = "upload/media/estudios_img/Estudio_" . $empresaId . "_" . $hoyString . ".png";
        $myPicture->render($filename);

        return $filename;
    }

    function crearGraficoAlteracionOsteomuscular($revisionId, $labels, $valores)
    {
        $hoy = new \DateTime();
        $hoyString = $hoy->format('YmdHis');

        $ruta = 'pChart/fonts';
        /* Create the pChart object */
        $myPicture = new pDraw(700, 230);

        /* Populate the pData object */
        $myPicture->myData->addPoints([$valores[0], VOID, VOID, VOID, VOID], $labels[0]);
        $myPicture->myData->addPoints([VOID, $valores[1], VOID, VOID, VOID], $labels[1]);
        $myPicture->myData->addPoints([VOID, VOID, $valores[2], VOID, VOID], $labels[2]);
        $myPicture->myData->addPoints([VOID, VOID, VOID, $valores[3], VOID], $labels[3]);
        $myPicture->myData->addPoints([VOID, VOID, VOID, VOID, $valores[4]], $labels[4]);
        $myPicture->myData->addPoints(["Cantidad"], "Value");
        $myPicture->myData->setSerieDescription("Value", "Month");
        $myPicture->myData->setAbscissa("Value");

        /* Turn off Anti-aliasing */
        //        $myPicture->setAntialias(FALSE);

        /* Add a border to the picture */
        $myPicture->drawRectangle(0, 0, 699, 229, ["Color" => new pColor(0, 255, 255)]);

        $myPicture->setFontProperties(["FontName" => "$ruta/Cairo-Regular.ttf", "FontSize" => 11]);
        $myPicture->drawText(250, 40, "Alteraciones osteomusculares", ["FontSize" => 10, "Align" => TEXT_ALIGN_BOTTOMMIDDLE]);

        /* Set the default font */
        $myPicture->setFontProperties(["FontName" => "$ruta/Cairo-Regular.ttf", "FontSize" => 7]);

        /* Define the chart area */
        $myPicture->setGraphArea(60, 40, 650, 200);

        /* Draw the scale */
        $myPicture->drawScale(["GridColor" => new pColor(200, 200, 200), "DrawSubTicks" => TRUE, "CycleBackground" => TRUE]);

        /* Write the chart legend */
        $myPicture->drawLegend(200, 12, ["Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL]);

        /* Turn on shadow computing */
        $myPicture->setShadow(TRUE, ["X" => 1, "Y" => 1, "Color" => new pColor(0, 0, 0, 10)]);

        /* Draw the chart */
        (new pCharts($myPicture))->drawBarChart([
            "Gradient" => TRUE,
            "GradientMode" => GRADIENT_EFFECT_CAN,
            "DisplayPos" => LABEL_POS_INSIDE,
            "DisplayValues" => TRUE,
            "DisplayColor" => new pColor(255, 255, 255),
            "DisplayShadow" => TRUE,
            "Surrounding" => 10
        ]);
        $filenameAlteracionOsteomuscular = "upload/media/estudios_img/Estudio_AO_" . $revisionId . "_" . $hoyString . ".png";
        $myPicture->render($filenameAlteracionOsteomuscular);

        return $filenameAlteracionOsteomuscular;
    }

    function crearGraficoAlteracionOsteomuscularPuestoTrabajoGrupo($revisionId, $valores)
    {
        $hoy = new \DateTime();
        $hoyString = $hoy->format('YmdHis');

        $ruta = 'pChart/fonts';
        /* Create the pChart object */
        $myPicture = new pDraw(700, 230);

        /* Populate the pData object */
        foreach ($valores as $v) {
            $myPicture->myData->addPoints([$v['AO_PUESTO_1'], $v['AO_PUESTO_2'], $v['AO_PUESTO_3'], $v['AO_PUESTO_4'], $v['AO_PUESTO_5']], $v['AO_PUESTO']);
        }
        $myPicture->myData->addPoints(["Columna vertebral", "Muñeca/Manos", "Rodillas", "Extremidades superiores", "Extremidades inferiores"], "Value");
        $myPicture->myData->setSerieDescription("Value", "Month");
        $myPicture->myData->setAbscissa("Value");

        /* Turn off Anti-aliasing */
        //        $myPicture->setAntialias(FALSE);

        /* Add a border to the picture */
        $myPicture->drawRectangle(0, 0, 699, 229, ["Color" => new pColor(0, 255, 255)]);

        $myPicture->setFontProperties(["FontName" => "$ruta/Cairo-Regular.ttf", "FontSize" => 11]);
        $myPicture->drawText(300, 40, "Alteraciones osteomusculares - Puesto de trabajo", ["FontSize" => 15, "Align" => TEXT_ALIGN_BOTTOMMIDDLE]);

        /* Set the default font */
        $myPicture->setFontProperties(["FontName" => "$ruta/Cairo-Regular.ttf", "FontSize" => 7]);

        /* Define the chart area */
        $myPicture->setGraphArea(60, 40, 650, 200);

        /* Draw the scale */
        $myPicture->drawScale(["GridColor" => new pColor(200, 200, 200), "DrawSubTicks" => TRUE, "CycleBackground" => TRUE]);

        /* Write the chart legend */
        $myPicture->drawLegend(50, 12, ["Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL]);

        /* Turn on shadow computing */
        $myPicture->setShadow(TRUE, ["X" => 1, "Y" => 1, "Color" => new pColor(0, 0, 0, 10)]);

        /* Draw the chart */
        (new pCharts($myPicture))->drawBarChart([
            "Gradient" => TRUE,
            "GradientMode" => GRADIENT_EFFECT_CAN,
            "DisplayPos" => LABEL_POS_INSIDE,
            "DisplayValues" => TRUE,
            "DisplayColor" => new pColor(255, 255, 255),
            "DisplayShadow" => TRUE,
            "Surrounding" => 10
        ]);
        $filename = "upload/media/estudios_img/Estudio_AOG_" . $revisionId . "_" . $hoyString . ".png";
        $myPicture->render($filename);

        return $filename;
    }

    function eliminar_tildes($cadena)
    {
        //Codificamos la cadena en formato utf8 en caso de que nos de errores
        //$cadena = utf8_encode($cadena);

        //Ahora reemplazamos las letras
        $cadena = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $cadena
        );
        $cadena = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $cadena
        );
        $cadena = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $cadena
        );
        $cadena = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $cadena
        );
        $cadena = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $cadena
        );
        $cadena = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç', '&', '·', '´', '`'),
            array('n', 'N', 'c', 'C', '', '', '', ''),
            $cadena
        );
        return $cadena;
    }

    function obtenerPdfAnalitica($revision)
    {
        $pdf = "";
        if (!is_null($revision->getNumeroPeticion()) && !is_null($revision->getFechaRecuperacionResultado())) {
            $pdf = $this->generateUrl('medico_revision_descargar_analitica', array('id' => $revision->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
        }
        return $pdf;
    }
}
