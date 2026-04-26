<?php

namespace App\Controller;

use App\Entity\GdocFichero;
use App\Logger;
use PhpOffice\PhpWord\Shared\ZipArchive;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class GdocController extends AbstractController
{
    // Función helper para crear nombres de archivo seguros
    function createSafeFilename($originalFilename)
    {
        $filenameFallback = str_replace(['%', '/', '\\'], '_', $originalFilename);
        return preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filenameFallback);
    }

    public function downFile(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $hoy = new \DateTime();
        $hoyString = $hoy->format('YmdHis');

        $user = $this->getUser();
        $usuario = $em->getRepository('App\Entity\User')->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $tipo = $_REQUEST['tipo'];
        $fileId = $_REQUEST['fileId'];

        if ($tipo == '19') {
            $fichero = $em->getRepository('App\Entity\GdocEmpresa')->find($fileId);
            $nombreFichero = $fichero->getNombreCompleto();
        } else {
            $fichero = $em->getRepository('App\Entity\GdocFichero')->find($fileId);
            $nombreFichero = $fichero->getNombre();
        }

        // Buscamos la configuración de la gestión documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);

        $rutaCompleta = $gdocConfig->getRuta();
        $carpetaPlantillas = $gdocConfig->getCarpetaPlantillas();
        $carpetaTemporal = $gdocConfig->getCarpetaTemporal();
        $passwordUsuario = $gdocConfig->getPasswordFicherosEncriptados();
        $carpetaEmpresa = $gdocConfig->getCarpetaEmpresa();

        $encriptar = false;
        $aptitudRestriccionSn = false;

        switch ($tipo) {
                // Contrato
            case '1':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaContrato();
                $contrato = $em->getRepository('App\Entity\Contrato')->findOneBy(array('fichero' => $fichero));
                $passwordOwner = $contrato->getPasswordPdf();
                $encriptar = true;

                if (is_null($passwordOwner)) {
                    $contratoId = $contrato->getId();
                    $passwordOwner = hash('sha256', 'OpenticPrevencion' . $contratoId);
                    $contrato->setPasswordPdf($passwordOwner);
                    $em->persist($contrato);
                    $em->flush();
                }
                break;

                // Factura
            case '2':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaFactura();
                $factura = $em->getRepository('App\Entity\Facturacion')->findOneBy(array('fichero' => $fichero));
                $passwordOwner = $factura->getPasswordPdf();
                $encriptar = true;

                if (is_null($passwordOwner)) {
                    $facturacionId = $factura->getId();
                    $passwordOwner = hash('sha256', 'OpenticPrevencion' . $facturacionId);
                    $factura->setPasswordPdf($passwordOwner);
                    $em->persist($factura);
                    $em->flush();
                }
                break;

                // Notificación
            case '3':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaNotificacion();
                break;

                // Certificación
            case '4':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaCertificacion();
                $certificacion = $em->getRepository('App\Entity\EmpresaCertificacion')->findOneBy(array('fichero' => $fichero));
                $passwordOwner = $certificacion->getPasswordPdf();
                $encriptar = true;

                if (is_null($passwordOwner)) {
                    $certificacionId = $certificacion->getId();
                    $passwordOwner = hash('sha256', 'OpenticPrevencion' . $certificacionId);
                    $certificacion->setPasswordPdf($passwordOwner);
                    $em->persist($certificacion);
                    $em->flush();
                }
                break;

                // Modelo 347
            case '5':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaModelo347();
                break;

                // Accidente
            case '6':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaAccidente();
                break;

                // Evaluación
            case '7':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaEvaluacion();
                $evaluacion = $em->getRepository('App\Entity\Evaluacion')->findOneBy(array('fichero' => $fichero));
                $passwordOwner = $evaluacion->getPasswordPdf();
                $encriptar = true;

                if (is_null($passwordOwner)) {
                    $evaluacionId = $evaluacion->getId();
                    $passwordOwner = hash('sha256', 'OpenticPrevencion' . $evaluacionId);
                    $evaluacion->setPasswordPdf($passwordOwner);
                    $em->persist($evaluacion);
                    $em->flush();
                }
                break;

                // Plan de prevención
            case '8':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaPlanPrevencion();
                break;

                // Citación
            case '9':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaCitacion();
                break;

                // Cuestionario de revisión
            case '10':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaRevision();
                break;

                // Certificado de aptitud
            case '11':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaAptitud();
                $revision = $em->getRepository('App\Entity\Revision')->findOneBy(array('fichero' => $fichero));
                $passwordOwner = $revision->getPasswordPdf();
                $encriptar = false;

                if ($revision->getApto()->getId() == 2 && !is_null($revision->getFicheroRestriccion())) {
                    $aptitudRestriccionSn = true;
                }
                break;

                // Manual de VS
            case '18':
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaManualVs();
                break;

                // Formación
            case '19':
                $empresaId = $fichero->getEmpresa()->getId();
                $rutaFichero = $rutaCompleta . $carpetaEmpresa . '/' . $empresaId . '/' . $nombreFichero;

                if (!file_exists($rutaFichero)) {
                    throw $this->createNotFoundException('El archivo no existe');
                }
                if (!is_readable($rutaFichero)) {
                    throw new \Exception('No se puede acceder al archivo');
                }
                $fileDown = file_get_contents($rutaFichero, true);
                if ($fileDown === false) {
                    throw new \Exception('Error al leer el archivo');
                }
                $response = new Response($fileDown);

                $disposition = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    $nombreFichero,
                    $this->createSafeFilename($nombreFichero)
                );
                $response->headers->set('Content-Disposition', $disposition);
                $response->headers->set('Content-Type', 'application/octet-stream');
                $response->headers->set('Cache-Control', 'private');
                $response->headers->set('Content-Transfer-Encoding', 'binary');

                // Registrar la descarga
                $object = array("json" => $username, "entidad" => "descargar fichero:" . ' ' . $fileId, "id" => $id);
                $logger = new Logger();
                $logger->addLog($em, "down", $object, $usuario, TRUE);
                $em->flush();

                return $response;
                break;

            default:
                $carpetaPlantillaGenerada = $gdocConfig->getCarpetaGenerada();
        }
        // Cambiamos la extensión del nombre del fichero
        $nombreFicheroPdf = str_replace('docx', 'pdf', $nombreFichero);

        // Convertimos el word en pdf
        $fileDocx = $rutaCompleta . $carpetaPlantillaGenerada . '/' . $fichero->getNombre();
        $filePdf = str_replace('docx', 'pdf', $fileDocx);
        $outdir = $rutaCompleta . $carpetaPlantillaGenerada;

        $cmd = 'soffice --headless --convert-to pdf:writer_pdf_Export "' . $fileDocx . '" --outdir "' . $outdir . '"';
        exec($cmd);

        if ($encriptar) {
            $filePdfEncriptado = $rutaCompleta . $carpetaTemporal . '/' . $nombreFicheroPdf;
            $this->encriptarPdf($filePdfEncriptado, $filePdf, $passwordOwner, $passwordUsuario);
            $filePdf = $filePdfEncriptado;
        }
        // Registrar la descarga
        $object = array("json" => $username, "entidad" => "descargar fichero:" . ' ' . $fileId, "id" => $id);
        $logger = new Logger();
        $logger->addLog($em, "down", $object, $usuario, TRUE);
        $em->flush();

        if ($tipo == '11') {
            if ($aptitudRestriccionSn) {
                $nombreZip = $revision->getTrabajador()->getNombre() . '_' .
                    $revision->getTrabajador()->getDni() . '_' .
                    $hoyString . '.zip';

                $zipDir = $this->getParameter('kernel.project_dir') . '/upload/media/ziprevision/';
                if (!is_dir($zipDir)) {
                    mkdir($zipDir, 0775, true);
                }
                $zipPath = $zipDir . $nombreZip;

                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                    // Convertimos el pdf en imagenes
                    $filePng = str_replace('docx', 'png', $fileDocx);
                    $this->convertPdfToImage($filePdf, $filePng);

                    // Convertimos las imagenes en pdf
                    $nombrePng = str_replace('.docx', '', $fileDocx);
                    $this->convertImageToPdf($nombrePng, $filePdf);

                    $zip->addFile($filePdf, $nombreFicheroPdf);

                    $resultado = $this->generarFicheroRestriccionAptitud($revision, $em, $rutaCompleta, $carpetaPlantillas, $carpetaPlantillaGenerada, $usuario, $outdir);

                    if (is_array($resultado)) {
                        $zip->addFile($resultado['fileRestriccionPdf'], $resultado['nombrePlantillaRestriccionPdf']);
                    }
                    $zip->close();
                }
                $response = new Response(file_get_contents($zipPath));

                $disposition = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    $nombreZip,
                    $this->createSafeFilename($nombreZip)
                );
                $response->headers->set('Content-Type', 'application/zip');
                $response->headers->set('Content-Disposition', $disposition);
                $response->headers->set('Cache-Control', 'private');
                $response->headers->set('Content-Transfer-Encoding', 'binary');

                return $response;
            } else {
                // Convertimos el pdf en imagenes
                $filePng = str_replace('docx', 'png', $fileDocx);
                $this->convertPdfToImage($filePdf, $filePng);

                // Convertimos las imagenes en pdf
                $nombrePng = str_replace('.docx', '', $fileDocx);
                $this->convertImageToPdf($nombrePng, $filePdf);

                $response = new BinaryFileResponse($filePdf);

                $disposition = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    $nombreFicheroPdf,
                    $this->createSafeFilename($nombreFicheroPdf)
                );
                $response->headers->set('Content-Type', 'application/pdf');
                $response->headers->set('Content-Disposition', $disposition);
                $response->headers->set('Cache-Control', 'private');
                $response->headers->set('Content-Transfer-Encoding', 'binary');

                return $response;
            }
        } else {
            $response = new BinaryFileResponse($filePdf);

            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $nombreFicheroPdf,
                $this->createSafeFilename($nombreFicheroPdf)
            );
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set('Content-Disposition', $disposition);
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-Transfer-Encoding', 'binary');

            return $response;
        }
    }

    public function downAptitudes(Request $request)
    {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $hoy = new \DateTime();
        $hoyString = $hoy->format('YmdHis');

        $user = $this->getUser();
        $usuario = $em->getRepository('App\Entity\User')->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        // Buscamos la configuración de la gestión documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $rutaCompleta = $gdocConfig->getRuta();
        $carpetaPlantillaGenerada = $gdocConfig->getCarpetaAptitud();
        $carpetaPlantillas = $gdocConfig->getCarpetaPlantillas();

        $revisionesSelectArray = array();

        if (isset($_REQUEST['revisiones'])) {
            $revisionesSelectArray = explode(",", $_REQUEST['revisiones']);
        }
        if (isset($_REQUEST['id'])) {
            array_push($revisionesSelectArray, $_REQUEST['id']);
        }
        $empresa = $session->get('empresaIntranet');
        $empresaNombre = $this->eliminar_tildes($empresa->getEmpresa());
        $nombreZip = 'APTITUDES ' . $empresaNombre . '_' . $hoyString . '.zip';

        $zipDir = $this->getParameter('kernel.project_dir') . '/upload/media/zipaptitud/';
        if (!is_dir($zipDir)) {
            mkdir($zipDir, 0775, true);
        }
        $zipPath = $zipDir . $nombreZip;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($revisionesSelectArray as $rs) {
                $revision = $em->getRepository('App\Entity\Revision')->find($rs);
                $fichero = $revision->getFichero();

                $nombreFichero = $fichero->getNombre();
                $fileId = $fichero->getId();

                // Cambios la extension el nombre del fichero
                $nombreFicheroPdf = str_replace('docx', 'pdf', $nombreFichero);

                // Convertimos el word en pdf
                $fileDocx = $rutaCompleta . $carpetaPlantillaGenerada . '/' . $fichero->getNombre();

                $filePdf = str_replace('docx', 'pdf', $fileDocx);
                $filePng = str_replace('docx', 'png', $fileDocx);
                $outdir = $rutaCompleta . $carpetaPlantillaGenerada;

                $cmd = 'soffice --headless --convert-to pdf:writer_pdf_Export "' . $fileDocx . '" --outdir "' . $outdir . '"';
                exec($cmd);

                // Convertimos el pdf en imagenes
                $this->convertPdfToImage($filePdf, $filePng);

                // Convertimos las imagenes en pdf
                $nombrePng = str_replace('.docx', '', $fileDocx);
                $this->convertImageToPdf($nombrePng, $filePdf);

                $zip->addFile($filePdf, $nombreFicheroPdf);

                $object = array("json" => $username, "entidad" => "descargar fichero:" . ' ' . $fileId, "id" => $id);

                $logger = new Logger();
                $em = $this->getDoctrine()->getManager();
                $logger->addLog($em, "down", $object, $usuario, TRUE);
                $em->flush();

                // Buscamos si la aptitud tiene restriccion
                if ($revision->getApto()->getId() == 2 && !is_null($revision->getFicheroRestriccion())) {
                    $resultado = $this->generarFicheroRestriccionAptitud($revision, $em, $rutaCompleta, $carpetaPlantillas, $carpetaPlantillaGenerada, $usuario, $outdir);

                    if (is_array($resultado)) {
                        $zip->addFile($resultado['fileRestriccionPdf'], $resultado['nombrePlantillaRestriccionPdf']);

                        $fileRestriccionId = $revision->getFicheroRestriccion()->getId();
                        $object = array("json" => $username, "entidad" => "descargar fichero:" . ' ' . $fileRestriccionId, "id" => $id);

                        $logger = new Logger();
                        $em = $this->getDoctrine()->getManager();
                        $logger->addLog($em, "down", $object, $usuario, TRUE);
                        $em->flush();
                    }
                }
            }
            $zip->close();
        }
        $response = new Response(file_get_contents($zipPath));

        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $nombreZip . '"');

        return $response;
    }

    public function downFacturas(Request $request)
    {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $hoy = new \DateTime();
        $hoyString = $hoy->format('YmdHis');

        $user = $this->getUser();
        $usuario = $em->getRepository('App\Entity\User')->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        // Buscamos la configuración de la gestión documental
        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $rutaCompleta = $gdocConfig->getRuta();
        $carpetaPlantillaGenerada = $gdocConfig->getCarpetaFactura();
        $carpetaPlantillas = $gdocConfig->getCarpetaPlantillas();

        $facturasSelectArray = array();

        if (isset($_REQUEST['facturas'])) {
            $facturasSelectArray = explode(",", $_REQUEST['facturas']);
        }
        if (isset($_REQUEST['id'])) {
            array_push($facturasSelectArray, $_REQUEST['id']);
        }
        $empresa = $session->get('empresaIntranet');
        $empresaNombre = $this->eliminar_tildes($empresa->getEmpresa());
        $nombreZip = 'FACTURAS ' . $empresaNombre . '_' . $hoyString . '.zip';

        $zip = new ZipArchive();
        if ($zip->open('upload/media/zipfactura/' . $nombreZip, ZipArchive::CREATE) === TRUE) {
            foreach ($facturasSelectArray as $rs) {
                $factura = $em->getRepository('App\Entity\Facturacion')->find($rs);
                $fichero = $factura->getFichero();

                $nombreFichero = $fichero->getNombre();
                $fileId = $fichero->getId();

                // Cambios la extension el nombre del fichero
                $nombreFicheroPdf = str_replace('docx', 'pdf', $nombreFichero);

                // Convertimos el word en pdf
                $fileDocx = $rutaCompleta . $carpetaPlantillaGenerada . '/' . $fichero->getNombre();

                $filePdf = str_replace('docx', 'pdf', $fileDocx);
                $outdir = $rutaCompleta . $carpetaPlantillaGenerada;

                $cmd = 'soffice --headless --convert-to pdf:writer_pdf_Export "' . $fileDocx . '" --outdir "' . $outdir . '"';
                exec($cmd);

                $zip->addFile($filePdf, $nombreFicheroPdf);

                $object = array("json" => $username, "entidad" => "descargar fichero:" . ' ' . $fileId, "id" => $id);

                $logger = new Logger();
                $em = $this->getDoctrine()->getManager();
                $logger->addLog($em, "down", $object, $usuario, TRUE);
                $em->flush();
            }
            $zip->close();
        }
        $response = new Response(file_get_contents('upload/media/zipfactura/' . $nombreZip));

        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $nombreZip . '"');

        return $response;
    }

    function encriptarPdf($filePdfEncriptado, $filePdf, $passwordOwner, $passwordUsuario)
    {
        // Encriptamos el pdf
        $cmd = 'pdftk "' . $filePdf . '" output "' . $filePdfEncriptado . '" owner_pw "' . $passwordOwner . '" user_pw "' . $passwordUsuario . '" allow printing';
        exec($cmd);
    }

    function convertPdfToImage($pdf, $image)
    {
        // Convertimos el pdf en imagenes
        $cmd = 'convert -density 150 "' . $pdf . '" -quality 100 "' . $image . '"';
        exec($cmd);
    }

    function convertImageToPdf($image, $pdf)
    {
        // Convertimos las imagenes en pdf
        $cmd = 'convert "' . $image . '*.png" "' . $pdf . '"';
        exec($cmd);
    }

    function generarFicheroRestriccionAptitud($revision, $em, $rutaCompleta, $carpetaPlantillas, $carpetaPlantillaGenerada, $usuario, $outdir)
    {
        // Si tiene restriccion buscamos el fichero
        $ficheroRestriccion = $revision->getFicheroRestriccion();
        if (!is_null($ficheroRestriccion)) {
            $nombrePlantillaRestriccion = $ficheroRestriccion->getPlantilla()->getNombre();
            $ficheroRestriccionEmpresa = $em->getRepository('App\Entity\GdocPlantillas')->findOneBy(array('nombre' => $nombrePlantillaRestriccion . ' EMPRESA', 'anulado' => false));

            if (!is_null($ficheroRestriccionEmpresa)) {
                // Recuperamos los datos de la plantilla
                $nombreCompleto = $ficheroRestriccionEmpresa->getNombreCompleto();
                $nombrePlantilla = $ficheroRestriccionEmpresa->getNombre();

                $empresa = $revision->getEmpresa();
                $nombreTrabajador = $revision->getTrabajador()->getNombre();
                $nuevaPlantilla = $nombrePlantilla . ' ' . $nombreTrabajador . '.docx';

                // Generamos el nuevo fichero a partir de la plantilla
                $urlPlantilla = $rutaCompleta . $carpetaPlantillas . '/' . $nombreCompleto;
                $urlNueva = $rutaCompleta . $carpetaPlantillaGenerada . '/' . $nuevaPlantilla;

                $return = $this->replaceTags($em, $urlPlantilla, $urlNueva, $revision);

                if ($return) {
                    $gdocFichero = new GdocFichero();
                    $gdocFichero->setEmpresa($empresa);
                    $gdocFichero->setDtcrea(new \DateTime());
                    $gdocFichero->setUsuario($usuario);
                    $gdocFichero->setNombre($nuevaPlantilla);
                    $gdocFichero->setAnulado(false);
                    $gdocFichero->setPlantilla($ficheroRestriccionEmpresa);
                    $em->persist($gdocFichero);
                    $em->flush();

                    $nombreFichero = $gdocFichero->getNombre();

                    // Convertimos el word en pdf
                    $fileRestriccionDocx = $rutaCompleta . $carpetaPlantillaGenerada . '/' . $nombreFichero;
                    $fileRestriccionPdf = str_replace('docx', 'pdf', $fileRestriccionDocx);

                    $nombrePlantillaRestriccionPdf = str_replace('docx', 'pdf', $nombreFichero);

                    $cmd = 'soffice --headless --convert-to pdf:writer_pdf_Export "' . $fileRestriccionDocx . '" --outdir "' . $outdir . '"';
                    exec($cmd);

                    // Convertimos el pdf en imagenes
                    $fileRestriccionPng = str_replace('docx', 'png', $fileRestriccionDocx);
                    $this->convertPdfToImage($fileRestriccionPdf, $fileRestriccionPng);

                    // Convertimos las imagenes en pdf
                    $nombrePng = str_replace('.docx', '', $fileRestriccionDocx);
                    $this->convertImageToPdf($nombrePng, $fileRestriccionPdf);

                    return array(
                        'fileRestriccionPdf' => $fileRestriccionPdf,
                        'nombrePlantillaRestriccionPdf' => $nombrePlantillaRestriccionPdf
                    );
                }
                return null;
            }
            return null;
        }
        return null;
    }

    function replaceTags($em, $urlPlantilla, $urlNueva, $revision)
    {
        $hoy = new \DateTime();

        copy($urlPlantilla, $urlNueva);

        $empresa = $revision->getEmpresa();
        $nombre = str_replace('&', '&amp;', $empresa->getEmpresa());

        $nombreTrabajador = $revision->getTrabajador()->getNombre();
        $doctorRevision = $revision->getMedico()->getDescripcion();
        $fechaRevision = $revision->getFecha()->format('d/m/Y');

        // Reemplazamos los tags
        $templateProcessor = new TemplateProcessor($urlNueva);
        $templateProcessor->setValue("EMPRESA_NOMBRE", $nombre);
        $templateProcessor->setValue("TRABAJADOR_NOMBRE", $nombreTrabajador);
        $templateProcessor->setValue('FECHA_REVISION', $fechaRevision);
        $templateProcessor->setValue('REVISION_DOCTOR', $doctorRevision);
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

    function eliminar_tildes($cadena)
    {
        // Codificamos la cadena en formato utf8 en caso de que nos de errores
        $cadena = utf8_encode($cadena);

        // Ahora reemplazamos las letras
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
            array('ñ', 'Ñ', 'ç', 'Ç'),
            array('n', 'N', 'c', 'C'),
            $cadena
        );
        return $cadena;
    }
}
