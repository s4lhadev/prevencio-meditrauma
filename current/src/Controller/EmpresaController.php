<?php

namespace App\Controller;

use App\Admin\User;
use App\Entity\Centro;
use App\Entity\GdocEmpresaCarpeta;
use App\Entity\CentroTrabajoEmpresa;
use App\Entity\CnaeEmpresa;
use App\Entity\CorreoEmpresa;
use App\Entity\Empresa;
use App\Entity\Evaluacion;
use App\Entity\GdocEmpresa;
use App\Entity\GdocTrabajador;
use App\Entity\LogEnvioMail;
use App\Entity\Mandato;
use App\Entity\MaquinaEmpresa;
use App\Entity\PuestoTrabajoTrabajador;
use App\Entity\RiesgoCausaImg;
use App\Entity\TarifaPrl;
use App\Entity\TarifaRevisionMedica;
use App\Entity\TecnicoEmpresa;
use App\Entity\Trabajador;
use App\Entity\TrabajadorAltaBaja;
use App\Entity\TrabajadorEmpresa;
use App\Entity\UserIntranet;
use App\Entity\UserIntranetEmpresa;
use App\Form\EmpresaMedicoType;
use App\Form\EmpresaTecnicoType;
use App\Form\EmpresaType;
use App\Form\EnviarCorreoType;
use App\Form\EvaluacionType;
use App\Form\GdocEmpresaType;
use App\Form\GdocEmpresaType2;
use App\Form\GdocTrabajadorType;
use App\Form\MaquinaEmpresaType;
use App\Form\TrabajadorImportType;
use App\Logger;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class EmpresaController extends AbstractController
{
    public function createEmpresa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getAddEmpresaSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $session->set('empresa', null);

        $spa = $em->getRepository('App\Entity\ServicioPrevencion')->find(3);

        $empresa = new Empresa();
        $empresa->setFechaAlta(new \DateTime());
        $form = $this->createForm(EmpresaType::class, $empresa, array('disabled' => false, 'spa' => $spa));

        $listTecnicos = $em->getRepository('App\Entity\Tecnico')->findBy(array('anulado' => false));
        $listCnae = $em->getRepository('App\Entity\Cnae')->findBy(array('anulado' => false));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $empresa = $form->getData();
            $em = $this->getDoctrine()->getManager();

            //Calculamos el valor del codigo de empresa autonumerico
            $query = "select MAX(CAST(split_part(codigo, '-', 1)  AS INTEGER)) as codigo from empresa";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultCodigoEmpresa = $stmt->fetchAll();

            if (is_null($empresa->getCodigo())) {
                $codigo = intval($resultCodigoEmpresa[0]['codigo']) + 1;

                $codigoEmpresa = "";
                if (!is_null($empresa->getCodigoEmpresa())) {
                    $codigoEmpresa = $empresa->getCodigoEmpresa()->getCodigo();
                }

                $empresa->setCodigo($codigo . '-' . $codigoEmpresa);
            }

            $em->persist($empresa);
            $em->flush();

            //Creamos automaticamente el centro de trabajo
            //            $centroTrabajo = new Centro();
            //            $centroTrabajo->setNombre($empresa->getEmpresa());
            //            $centroTrabajo->setTrabajadores($empresa->getTrabajadores());
            //            $centroTrabajo->setCcc($empresa->getCcc());
            //
            //            $em->persist($centroTrabajo);
            //            $em->flush();

            //Asignamos el centro de trabajo a la empresa
            //            $centroTrabajoEmpresa = new CentroTrabajoEmpresa();
            //            $centroTrabajoEmpresa->setEmpresa($empresa);
            //            $centroTrabajoEmpresa->setCentro($centroTrabajo);
            //            $centroTrabajoEmpresa->setAnulado(false);
            //
            //            $em->persist($centroTrabajoEmpresa);
            //            $em->flush();

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('empresa_update', array('id' => $empresa->getId()));
        }
        return $this->render('empresa/edit.html.twig', array('listPlantillasModelo347' => null, 'carpetas' => null, 'form2' => null, 'modelo347Empresa' => null, 'tree' => null, 'impagadaSn' => false, 'listConceptoTarifas' => null, 'listPlantillasNotificaciones' => null, 'listPlantillasCertificaciones' => null, 'tarifasPrl' => null, 'tarifasRevisionesMedicas' => null, 'listConceptosFactura' => null, 'listPlantillasFacturas' => null, 'notificaciones' => null, 'certificaciones' => null, 'listFuncionCorreo' => null, 'listPlantillasContratos' => null, 'contratos' => null, 'renovaciones' => null, 'facturas' => null, 'pagos' => null, 'datosBancarios' => null, 'balanceEconomico' => null, 'form' => $form->createView(), 'tecnicos' => null, 'listTecnicos' => $listTecnicos, 'correos' => null, 'cnae' => null, 'listCnae' => $listCnae, 'trabajadoresEmpresa' => null, 'centroTrabajoEmpresa' => null, 'manualVsEmpresa' => null, 'listPlantillasManualVs' => null));
    }

    public function createEmpresaTecnico(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getAddEmpresaTecnicoSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $session->set('empresa', null);

        $spa = $em->getRepository('App\Entity\ServicioPrevencion')->find(3);

        $listCnae = $em->getRepository('App\Entity\Cnae')->findBy(array('anulado' => false));

        $empresa = new Empresa();
        $form = $this->createForm(EmpresaTecnicoType::class, $empresa, array('disabled' => false, 'fechaContrato' => null));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $empresa = $form->getData();
            $em = $this->getDoctrine()->getManager();

            //Calculamos el valor del codigo de empresa autonumerico
            $query = "select MAX(CAST(split_part(codigo, '-', 1)  AS INTEGER)) as codigo from empresa";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultCodigoEmpresa = $stmt->fetchAll();

            if (is_null($empresa->getCodigo())) {
                $codigo = intval($resultCodigoEmpresa[0]['codigo']) + 1;
                $empresa->setCodigo($codigo);
            }

            $em->persist($empresa);
            $em->flush();

            //Creamos automaticamente el centro de trabajo
            /*$centroTrabajo = new Centro();
            $centroTrabajo->setNombre($empresa->getEmpresa());
            $centroTrabajo->setTrabajadores($empresa->getTrabajadores());
            $centroTrabajo->setCcc($empresa->getCcc());
            $centroTrabajo->setEmpresa($empresa);
            $em->persist($centroTrabajo);
            $em->flush();

            //Asignamos el centro de trabajo a la empresa
            $centroTrabajoEmpresa = new CentroTrabajoEmpresa();
            $centroTrabajoEmpresa->setEmpresa($empresa);
            $centroTrabajoEmpresa->setCentro($centroTrabajo);
            $centroTrabajoEmpresa->setAnulado(false);
            $em->persist($centroTrabajoEmpresa);
            $em->flush();*/

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('tecnico_empresa_update', array('id' => $empresa->getId()));
        }
        return $this->render('empresatecnico/edit.html.twig', array('form' => $form->createView(), 'cnae' => null, 'listCnae' => $listCnae, 'trabajadoresEmpresa' => null, 'centroTrabajoEmpresa' => null, 'accidentesEmpresa' => null, 'listPlantillaAccidentes' => null, 'evaluaciones' => null));
    }

    public function viewEmpresa(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);

        $empresa = $em->getRepository('App\Entity\Empresa')->find($id);
        $session->set('empresa', $empresa);
        $empresaId = $empresa->getId();

        if (!$empresa) {
            throw $this->createNotFoundException(
                'La empresa con id ' . $id . ' no existe'
            );
        }

        //Buscamos todos los tecnicos
        $listTecnicos = $em->getRepository('App\Entity\Tecnico')->findBy(array('anulado' => false));

        //Buscamos los tecnicos de la empresa
        $tecnicoEmpresa = $em->getRepository('App\Entity\TecnicoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos los correos de la empresa
        $correoEmpresa = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos todos los cnaes
        $listCnae = $em->getRepository('App\Entity\Cnae')->findBy(array('anulado' => false));
        $cnaeEmpresa = $em->getRepository('App\Entity\CnaeEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos los trabajadores de la empresa
        $trabajadoresEmpresa = $em->getRepository('App\Entity\TrabajadorEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos los centros de trabajo de la empresa
        $centroTrabajoEmpresa = $em->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos los contratos de la empresa
        $contratosEmpresa = $this->buscarContratosEmpresa($empresaId);

        //Buscamos las renovaciones de la empresa
        $query = "select a.id, a.fechainicio, a.fechavencimiento, b.contrato, b.tipo_contrato_id, a.renovado, a.cancelada, a.documento_renovacion from renovacion a
				inner join contrato b on a.contrato_id = b.id
			where b.empresa_id = $empresaId
			and a.anulado = false 
			and b.anulado = false";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $renovacionesEmpresa = $stmt->fetchAll();

        //Buscamos los pagos pendientes de la empresa
        $query = "select b.id, a.contrato, a.fechainicio, b.vencimiento, b.importe_iva, b.importe_total, b.facturado from contrato a
			inner join contrato_pago b on a.id = b.contrato_id 
			where a.empresa_id = $empresaId
			and b.facturado = false
			and a.anulado = false 
			and b.anulado = false
			and a.cancelado = false";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $pagosPendientesEmpresa = $stmt->fetchAll();

        //Buscamos las facturas de la empresa
        $facturasEmpresa = $this->buscarFacturasEmpresa($empresaId);

        //Buscamos el balance economico de la empresa
        $balanceEmpresa = $this->createBalanceEconomico($em, $empresaId);

        //Buscamos los datos bancarios de la empresa
        $datosBancariosEmpresa = $this->getDoctrine()->getRepository('App\Entity\DatosBancarios')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos las notificaciones de la empresa
        $notificacionesEmpresa = $this->getDoctrine()->getRepository('App\Entity\EmpresaNotificacion')->findBy(array('empresa' => $empresa));

        //Buscamos las certificaciones de la empresa
        $certificacionesEmpresa = $this->getDoctrine()->getRepository('App\Entity\EmpresaCertificacion')->findBy(array('empresa' => $empresa));

        //Buscamos las tarifas de la empresa
        $tarifasPrl = $this->getDoctrine()->getRepository('App\Entity\TarifaPrl')->findBy(array('empresa' => $empresa, 'anulado' => false));
        $tarifasRevisionesMedicas = $this->getDoctrine()->getRepository('App\Entity\TarifaRevisionMedica')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos las certificaciones de la empresa
        $modelo347Empresa = $this->getDoctrine()->getRepository('App\Entity\EmpresaModelo347')->findBy(array('empresa' => $empresa));

        //Buscamos la gestión documental de la empresa
        $tree = $this->buscarGestionDocumentalEmpresa($empresaId);

        //Buscamos los manuales de vs
        $manualVs = $this->getDoctrine()->getRepository('App\Entity\EmpresaManualVs')->findBy(array('empresa' => $empresa));

        $form = $this->createForm(EmpresaType::class, $empresa, array('disabled' => true));
        $form->handleRequest($request);

        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "view", $empresa, $usuario);
        $em->flush();

        return $this->render('empresa/view.html.twig',  array('tree' => $tree, 'modelo347Empresa' => $modelo347Empresa, 'tarifasPrl' => $tarifasPrl, 'tarifasRevisionesMedicas' => $tarifasRevisionesMedicas, 'notificaciones' => $notificacionesEmpresa, 'certificaciones' => $certificacionesEmpresa, 'datosBancarios' => $datosBancariosEmpresa, 'balanceEconomico' => $balanceEmpresa, 'facturas' => $facturasEmpresa, 'pagos' => $pagosPendientesEmpresa, 'renovaciones' => $renovacionesEmpresa, 'contratos' => $contratosEmpresa, 'form' => $form->createView(), 'tecnicos' => $tecnicoEmpresa, 'listTecnicos' => $listTecnicos, 'correos' => $correoEmpresa, 'cnae' => $cnaeEmpresa, 'listCnae' => $listCnae, 'trabajadoresEmpresa' => $trabajadoresEmpresa, 'centroTrabajoEmpresa' => $centroTrabajoEmpresa, 'manualVsEmpresa' => $manualVs));
    }
    public function recuperaAltaBaja(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $altaBajaId = $_REQUEST['altaBajaId'];
        $altaBaja = $em->getRepository('App\Entity\TrabajadorAltaBaja')->find($altaBajaId);

        $fechaAlta = null;
        $fechaBaja = null;

        if (!is_null($altaBaja->getFechaAlta())) {
            $fechaAlta = $altaBaja->getFechaAlta()->format('Y-m-d');
        }

        if (!is_null($altaBaja->getFechaBaja())) {
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
    public function viewEmpresaTecnico(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getViewEmpresaTecnicoSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $empresa = $em->getRepository('App\Entity\Empresa')->find($id);
        $session->set('empresa', $empresa);
        $empresaId = $empresa->getId();

        if (!$empresa) {
            throw $this->createNotFoundException(
                'La empresa con id ' . $id . ' no existe'
            );
        }

        //Buscamos los tecnicos de la empresa
        $tecnicoEmpresa = $em->getRepository('App\Entity\TecnicoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos los correos de la empresa
        $correoEmpresa = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos todos los cnaes
        $listCnae = $em->getRepository('App\Entity\Cnae')->findBy(array('anulado' => false));
        $cnaeEmpresa = $em->getRepository('App\Entity\CnaeEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos los trabajadores de la empresa
        $trabajadoresEmpresa = $em->getRepository('App\Entity\TrabajadorEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));
        $trabajadoresEmpresaArray = $this->buscaTrabajadoresEmpresaTecnico($id, $trabajadoresEmpresa);

        //Buscamos los centros de trabajo de la empresa
        $centroTrabajoEmpresa = $em->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos los accidentes de la empresa
        $accidentesEmpresa = $em->getRepository('App\Entity\EmpresaAccidenteLaboral')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos las evaluaciones de la empresa
        $query = "select a.id, a.finalizada, b.empresa, string_agg(d.nombre::text, ' , '::text) as centros, 
                to_char(a.fecha_inicio, 'DD/MM/YYYY') as fechainicio, to_char(a.fecha_fin, 'DD/MM/YYYY') as fechafin, e.descripcion as tipo,
                f.descripcion as metodologia, a.fichero_id from evaluacion a 
                inner join empresa b on a.empresa_id = b.id
                left join evaluacion_centro_trabajo c on a.id = c.evaluacion_id
                left join centro d on c.centro_id = d.id
                left join tipo_evaluacion e on a.tipo_evaluacion_id = e.id
                left join metodologia_evaluacion f on a.metodologia_id = f.id 
                where a.anulado = false
                and a.empresa_id = $empresaId
                group by a.id, a.finalizada, b.empresa, d.nombre, a.fecha_inicio, a.fecha_fin, e.descripcion, f.descripcion, a.fichero_id
                order by a.fecha_inicio desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $evaluaciones = $stmt->fetchAll();

        //Buscamos los plan de prevencion de la empresa
        $planPrevencionEmpresa = $em->getRepository('App\Entity\EmpresaPlanPrevencion')->findBy(array('empresa' => $empresa));

        //Buscamos las maquinas de la empresa
        $maquinas = $this->getDoctrine()->getRepository('App\Entity\MaquinaEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos la gestión documental de la empresa
        $tree = $this->buscarGestionDocumentalEmpresa($empresaId);

        //Recuperamos las maquinas genericas de los puestos de trabajo
        $maquinasGenericasPuestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoMaquinaGenerica')->findBy(array('anulado' => false, 'empresa' => $empresa));

        //Recuperamos las maquinas empresa de los puestos de trabajo
        $maquinasEmpresaPuestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoMaquinaEmpresa')->findBy(array('anulado' => false, 'empresa' => $empresa));

        //Recuperamos las maquinas genericas de los puestos de trabajo
        $maquinasGenericasZonaTrabajo = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajoMaquinaGenerica')->findBy(array('anulado' => false, 'empresa' => $empresa));

        //Recuperamos las maquinas empresa de los puestos de trabajo
        $maquinasEmpresaZonaTrabajo = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajoMaquinaEmpresa')->findBy(array('anulado' => false, 'empresa' => $empresa));

        //Buscamos los puestos de trabajo del centro
        $listPuestosTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoCentro')->findBy(array('anulado' => false, 'empresa' => $empresa));

        //Buscamos las zonas de trabajo del centro
        $listZonasTrabajo = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajo')->findBy(array('anulado' => false, 'empresa' => $empresa), array('descripcion' => 'ASC'));

        //Buscamos las maquinas genericas
        $listMaquinasGenericas = $this->getDoctrine()->getRepository('App\Entity\MaquinaGenerica')->findBy(array('anulado' => false), array('descripcion' => 'ASC'));

        //Buscamos las maquinas del centro
        $listMaquinasEmpresa = $this->getDoctrine()->getRepository('App\Entity\MaquinaEmpresa')->findBy(array('anulado' => false, 'empresa' => $empresa), array('descripcion' => 'ASC'));

        //Buscamos los puesto de trabajo genericos
        $listPuestosGenericos = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoGenerico')->findBy(array(), array('descripcion' => 'ASC'));

        //Recuperamos las zonas del centro
        $zonas = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajo')->findBy(array('anulado' => false, 'empresa' => $empresaId));

        //Buscamos las tarifas de la empresa
        $tarifasRevisionesMedicas = $this->getDoctrine()->getRepository('App\Entity\TarifaRevisionMedica')->findBy(array('empresa' => $empresa, 'anulado' => false));

        $treePuestos = $this->buscarTreePuestosTrabajoEmpresa($empresaId, $empresa);

        //Buscamos los protocolos de acoso de la empresa
        $protocoloAcoso = $em->getRepository('App\Entity\EmpresaProtocoloAcoso')->findBy(array('empresa' => $empresa));

        //Buscamos el ultimo contrato de la empresa
        $query = "select fechainicio from contrato where empresa_id = $empresaId and anulado = false and cancelado = false order by fechainicio desc limit 1";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $resultContrato = $stmt->fetchAll();
        $fechaContrato = null;
        $dateContrato = null;
        if (count($resultContrato) > 0) {
            $fechaContrato = $resultContrato[0]['fechainicio'];
            $dateContrato = new \DateTime($fechaContrato);
        }

        $form = $this->createForm(EmpresaTecnicoType::class, $empresa, array('disabled' => true, 'fechaContrato' => $dateContrato));

        return $this->render('empresatecnico/view.html.twig',  array('protocoloAcoso' => $protocoloAcoso, 'tarifasRevisionesMedicas' => $tarifasRevisionesMedicas, 'treePuestos' => $treePuestos, 'tree' => $tree, 'planPrevencion' => $planPrevencionEmpresa, 'maquinas' => $maquinas, 'tecnicos' => $tecnicoEmpresa, 'correos' => $correoEmpresa, 'form' => $form->createView(), 'cnae' => $cnaeEmpresa, 'listCnae' => $listCnae, 'trabajadoresEmpresa' => $trabajadoresEmpresaArray, 'centroTrabajoEmpresa' => $centroTrabajoEmpresa, 'accidentesEmpresa' => $accidentesEmpresa, 'evaluaciones' => $evaluaciones, 'zonasTrabajo' => $zonas, 'maquinasGenericasPuestoTrabajo' => $maquinasGenericasPuestoTrabajo, 'maquinasGenericasZonaTrabajo' => $maquinasGenericasZonaTrabajo, 'listPuestosTrabajo' => $listPuestosTrabajo, 'listMaquinasGenericas' => $listMaquinasGenericas, 'listZonasTrabajo' => $listZonasTrabajo, 'maquinasEmpresaPuestoTrabajo' => $maquinasEmpresaPuestoTrabajo, 'maquinasEmpresaZonaTrabajo' => $maquinasEmpresaZonaTrabajo, 'listMaquinasEmpresa' => $listMaquinasEmpresa, 'listPuestosGenericos' => $listPuestosGenericos));
    }

    public function viewEmpresaMedico(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getViewEmpresaMedicoSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $empresa = $em->getRepository('App\Entity\Empresa')->find($id);
        $session->set('empresa', $empresa);
        $empresaId = $empresa->getId();

        if (!$empresa) {
            throw $this->createNotFoundException(
                'La empresa con id ' . $id . ' no existe'
            );
        }

        //Buscamos todos los tecnicos
        $listTecnicos = $em->getRepository('App\Entity\Tecnico')->findBy(array('anulado' => false));

        //Buscamos los tecnicos de la empresa
        $tecnicoEmpresa = $em->getRepository('App\Entity\TecnicoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos los correos de la empresa
        $correoEmpresa = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos todos los cnaes
        $listCnae = $em->getRepository('App\Entity\Cnae')->findBy(array('anulado' => false));
        $cnaeEmpresa = $em->getRepository('App\Entity\CnaeEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos los trabajadores de la empresa
        $trabajadoresEmpresa = $em->getRepository('App\Entity\TrabajadorEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        $trabajadoresEmpresaArray = array();
        foreach ($trabajadoresEmpresa as $te) {
            if (!is_null($te->getTrabajador())) {
                $trabajadorId = $te->getTrabajador()->getId();

                $item['id'] = $trabajadorId;
                $item['trabajador'] = $te->getTrabajador()->getNombre();
                $item['dni'] = $te->getTrabajador()->getDni();
                //Buscamos la ultima revision para este trabajador
                $query = "select to_char(a.fecha, 'DD/MM/YYYY') as fecha, b.descripcion as puesto, a.apto_id from revision a
                    inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                    where a.anulado = false
                    and a.trabajador_id = $trabajadorId
                    and a.empresa_id = $id
                    order by a.fecha desc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $ultimaRevision = $stmt->fetchAll();

                if (count($ultimaRevision) > 0) {
                    $item['puesto'] = $ultimaRevision[0]['puesto'];
                    $item['apto'] = $ultimaRevision[0]['apto_id'];
                    $item['fechaultima'] = $ultimaRevision[0]['fecha'];
                } else {
                    $item['puesto'] = "";
                    $item['apto'] = "";
                    $item['fechaultima'] = "";
                }

                array_push($trabajadoresEmpresaArray, $item);
            }
        }

        //Buscamos los centros de trabajo de la empresa
        $centroTrabajoEmpresa = $em->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos las memorias de la empresa
        $memoriaEmpresa = $em->getRepository('App\Entity\EmpresaMemoria')->findBy(array('empresa' => $empresa, 'anulado' => false), array('anyo' => 'DESC'));

        //Buscamos los estudios de la empresa
        $estudioEmpresa = $em->getRepository('App\Entity\EmpresaEstudioEpidemiologico')->findBy(array('empresa' => $empresa, 'anulado' => false), array('anyo' => 'DESC'));

        //Buscamos las revisiones de la empresa
        $query = "select a.id, to_char(a.fecha, 'DD/MM/YYYY') as fecha, to_char(a.fecha, 'YYYYMMDDHHmm') as fechatimestamp, b.nombre as trabajador, c.descripcion as puesto, d.empresa, a.apto_id, a.fichero_id, e.id as estadoId, e.descripcion as estado, a.fichero_resumen_id, f.descripcion as doctor from revision a
            inner join trabajador b on a.trabajador_id = b.id
            inner join puesto_trabajo_centro c on a.puesto_trabajo_id = c.id
            inner join empresa d on a.empresa_id = d.id
            left join estado_revision e on a.estado_id = e.id
            left join doctor f on a.medico_id = f.id
            where a.anulado = false
            and b.anulado = false
            and c.anulado = false
            and a.empresa_id = $id
            order by a.fecha desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $revisionEmpresa = $stmt->fetchAll();

        //Buscamos la gestión documental de la empresa
        $tree = $this->buscarGestionDocumentalEmpresa($empresaId);

        $form = $this->createForm(EmpresaMedicoType::class, $empresa, array('disabled' => true));

        return $this->render('empresamedico/view.html.twig',  array('correos' => $correoEmpresa, 'form' => $form->createView(), 'cnae' => $cnaeEmpresa, 'listCnae' => $listCnae, 'trabajadoresEmpresa' => $trabajadoresEmpresaArray, 'centroTrabajoEmpresa' => $centroTrabajoEmpresa, 'tecnicos' => $tecnicoEmpresa, 'listTecnicos' => $listTecnicos, 'memoriaEmpresa' => $memoriaEmpresa, 'estudioEmpresa' => $estudioEmpresa, 'revisionEmpresa' => $revisionEmpresa, 'tree' => $tree));
    }

    public function showEmpresas(Request $request)
    {

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEmpresaSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $today = new \DateTime();
        $year = $today->format('Y') - 1;

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, a.empresa, a.codigo, a.cif, a.telefono1, a.telefono2, a.anulado, concat(i.id, ' - ', i.descripcion) as tipo, c.nombre as colaborador, 
            f.descripcion as agente, g.nombre as medico, h.nombre as responsableAdministrativo, a.domicilio_postal, a.localidad_postal, a.codigo_postal_postal,
            a.trabajadores, j.descripcion as estado, j.id as estadoid, i.id as tipoid,
            (select string_agg(tec2.nombre::text, ' , '::text) from tecnico_empresa tec
            inner join tecnico tec2 on tec.tecnico_id = tec2.id
            where tec.anulado = false and tec.empresa_id = a.id) as tecnico,
            (select string_agg(distinct cor.correo::text, ' , '::text) from correo_empresa cor
            inner join empresa cor2 on cor.empresa_id = cor2.id
            where cor.anulado = false and cor.empresa_id = a.id) as email, a.pruebas_especiales, (select sum(fac2.importe) from facturacion fac 
            inner join facturacion_lineas_conceptos fac2 on fac.id = fac2.facturacion_id 
            and fac.anulado = false
            and fac.serie_id = 7
            and fac2.anulado = false
            and fac.empresa_id = a.id
            and fac.fecha between '$year-01-01 00:00:00' and '$year-12-31 23:59:59') as facturacionanterior from empresa a
			left join asesoria c on a.colaborador_id = c.id
            left join comercial f on a.agente_id = f.id
            left join tecnico g on a.vigilancia_salud_id = g.id
            left join tecnico h on a.gestor_administrativo_id = h.id
            left join codigo_empresa i on a.codigo_empresa_id = i.id
            left join estado_prevencion j on a.estado_area_administracion_id = j.id
            where historico_prevenet = false
			group by a.id, a.empresa, a.codigo, a.cif, a.telefono1, a.anulado, c.nombre, f.id, g.nombre, h.nombre, i.id, i.descripcion, j.descripcion, j.id
			order by a.anulado asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $empresas = $stmt->fetchAll();

        $object = array("json" => $username, "entidad" => "empresas", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('empresa/show.html.twig', array('empresas' => $empresas));
    }

    public function showEmpresasTecnico(Request $request)
    {

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEmpresaTecnicoSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, a.empresa, a.codigo, a.marca_comercial, a.cif, d.grupo_empresa as grupo, a.estado_empresa_tecnico_id,
                    e.descripcion as estado, e.id as estadoid, a.anulado, a.localidad_postal, a.telefono1, a.nombre_representante,
                    (select to_char(fechavencimiento, 'DD/MM/YYYY') as fecha from renovacion 
            where anulado = false 
            and contrato_id in (select id from contrato where empresa_id = a.id and anulado = false and cancelado = false)
            order by fechavencimiento desc limit 1) as fecha_renovacion,
            (select string_agg(tec2.nombre::text, ' , '::text) from tecnico_empresa tec
            inner join tecnico tec2 on tec.tecnico_id = tec2.id
            where tec.anulado = false and tec.empresa_id = a.id) as tecnico,
            (select string_agg(cn2.cnae::text, ' , '::text) from cnae_empresa cn
            inner join cnae cn2 on cn.cnae_id = cn2.id
            where cn.anulado = false
            and cn.empresa_id = a.id) as cnae,
            (select string_agg(distinct cor.correo::text, ' , '::text) from correo_empresa cor
            inner join empresa cor2 on cor.empresa_id = cor2.id
            where cor.anulado = false and cor.empresa_id = a.id) as email
            from empresa a
            left join grupo_empresa d on a.grupo_empresa_id = d.id
            left join estado_prevencion e on a.estado_area_administracion_id = e.id ";

        if ($id == 30) {
            $query .= " where a.id in (select empresa_id from tecnico_empresa where anulado = false and tecnico_id = 37) ";
        }

        if ($id == 31) {
            $query .= " where a.id in (select empresa_id from tecnico_empresa where anulado = false and tecnico_id = 10) ";
        }

        $query .= " group by a.id, a.empresa, a.codigo_tecnico, a.marca_comercial, a.cif, a.anulado, d.grupo_empresa, e.descripcion, e.id order by a.anulado asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $empresas = $stmt->fetchAll();

        $object = array("json" => $username, "entidad" => "empresas técnico", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('empresatecnico/show.html.twig', array('empresas' => $empresas));
    }

    public function showEmpresasMedico(Request $request)
    {

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEmpresaMedicoSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select id, empresa, domicilio_fiscal, codigo_postal_fiscal, localidad_fiscal, anulado, pruebas_especiales from empresa order by anulado asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $empresas = $stmt->fetchAll();

        $object = array("json" => $username, "entidad" => "empresas médico", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('empresamedico/show.html.twig', array('empresas' => $empresas));
    }

    public function deleteEmpresa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getDeleteEmpresaSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $id = $_REQUEST['empresaId'];
        $tipo = $_REQUEST['tipo'];
        $fechaBaja = $_REQUEST['fechaBaja'];
        $motivoBaja = $_REQUEST['motivoBaja'];

        $empresa = $em->getRepository('App\Entity\Empresa')->find($id);

        if (!$empresa) {
            throw $this->createNotFoundException(
                'La empresa con id ' . $id . ' no existe'
            );
        }

        //Buscamos los trabajadores de la empresa
        /*$trabajadoresEmpresa = $em->getRepository('App\Entity\TrabajadorEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        foreach ($trabajadoresEmpresa as $trabajadorEmpresa){
            $trabajadorEmpresa->setAnulado(true);
            $em->persist($trabajadorEmpresa);
            $em->flush();
        }*/

        $trabajadoresAltaBaja = $em->getRepository('App\Entity\TrabajadorAltaBaja')->findBy(array('empresa' => $empresa, 'anulado' => false));

        foreach ($trabajadoresAltaBaja as $tab) {
            $tab->setActivo(false);
            $tab->setFechaBaja(new \DateTime($fechaBaja));
            $tab->setMotivoBaja('Baja empresa automática.');
            $em->persist($tab);
            $em->flush();
        }

        //Buscamos los centros de trabajo de la empresa
        $centroTrabajoEmpresa = $em->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        foreach ($centroTrabajoEmpresa as $cte) {
            /*$cte->setAnulado(true);
            $em->persist($cte);
            $em->flush();*/

            //Buscamos los trabajadores de la empresa
            $puestoTrabajoTrabajadores = $em->getRepository('App\Entity\PuestoTrabajoTrabajador')->findBy(array('centro' => $cte, 'anulado' => false));
            foreach ($puestoTrabajoTrabajadores as $ptt) {
                $ptt->setFechaBaja(new \DateTime($fechaBaja));
                $ptt->setObservaciones('Baja empresa automática.');
                $em->persist($ptt);
                $em->flush();
            }
        }

        //Buscamos los trabajadores de la empresa
        $puestoTrabajoTrabajadores = $em->getRepository('App\Entity\PuestoTrabajoTrabajador')->findBy(array('empresa' => $empresa, 'anulado' => false));
        foreach ($puestoTrabajoTrabajadores as $ptt) {
            $ptt->setFechaBaja(new \DateTime($fechaBaja));
            $ptt->setObservaciones('Baja empresa automática.');
            $em->persist($ptt);
            $em->flush();
        }

        $empresa->setAnulado(true);

        switch ($tipo) {
            case 1:
                $empresa->setFechaBaja(new \DateTime($fechaBaja));
                $empresa->setMotivoBaja($motivoBaja);

                $estadoBaja = $em->getRepository('App\Entity\EstadoEmpresa')->find(5);
                $empresa->setEstadoAdministrativoPrevencion($estadoBaja);
                break;
            case 2:
                $empresa->setFechaBajaTecnica(new \DateTime($fechaBaja));
                $empresa->setMotivoBajaTecnica($motivoBaja);
                break;
            case 3:
                $empresa->setFechaBajaVigilanciaSalud(new \DateTime($fechaBaja));
                $empresa->setMotivoBajaVigilanciaSalud($motivoBaja);
                break;
        }

        $em->persist($empresa);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");


        ///////////////////////////////////////////////////////////////////////// David Gil petició
        $session = $request->getSession();
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $username = $usuario->getUsername();

        $mensaje = "El usuario " . $username . ", ha dado de baja la siguiente empresa: " . $empresa->getEmpresa();


        $connection = $this->getDoctrine()->getManager()->getConnection();
        $tecnicoslista = $em->getRepository('App\Entity\TecnicoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        foreach ($tecnicoslista as $tecnicoEmpresa) {
            $tecnicoId = $tecnicoEmpresa->getTecnico();

            // Obtener la entidad Tecnico usando el id
            $tecnico = $em->getRepository('App\Entity\Tecnico')->find($tecnicoId);
            $destinatario = $tecnico->getAlias();

            $query = "INSERT INTO notificaciones_internas (id, fecha, destinatario, remitente, mensaje, leido)
                          VALUES (:id, :fecha, :destinatario, :remitente, :mensaje, false)";
            $statement = $connection->prepare($query);
            $querySelectUpdated = "SELECT id FROM notificaciones_internas ORDER BY id DESC LIMIT 1;";
            $statementSelect = $connection->prepare($querySelectUpdated);
            $statementSelect->execute();
            $updatedRows = $statementSelect->fetchAll();
            foreach ($updatedRows as $row) {
                $ultimoId = $row['id'];
                // Hacer algo con el valor de $id
            }
            $fechaActual = new \DateTime();
            $fechaFormateada = $fechaActual->format('d-m-Y H:i:s'); // Formatear la fecha a formato 'YYYY-MM-DD HH:MM:SS'
            // Reemplazar saltos de línea por otro carácter (por ejemplo, <br>)
            $mensajeSinSaltos = str_replace("\n", " ", $mensaje);
            $params = [
                'id' => $ultimoId + 1,
                'fecha' => $fechaFormateada,
                'destinatario' => $destinatario,
                'remitente' => $username,
                'mensaje' => $mensajeSinSaltos,
            ];

            $statement->execute($params);
        }
        /////////////////////////////////////////////////////////////////////////




        return new JsonResponse($data);
    }

    public function activarEmpresa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getDeleteEmpresaSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $id = $_REQUEST['id'];
        $tipo = $_REQUEST['tipo'];

        $empresa = $em->getRepository('App\Entity\Empresa')->find($id);
        //fix Ticket#2025032010000123 — PROBLEMATICA BAJA - REACTIVACION EMPRESAS 31/03/2025
        $trabajadores = $em->getRepository('App\Entity\TrabajadorAltaBaja')->findBy(['empresa' => $id]);

        if (!$empresa) {
            throw $this->createNotFoundException(
                'La empresa con id ' . $id . ' no existe'
            );
        }

        $empresa->setAnulado(false);

        switch ($tipo) {
            case 1:
                $empresa->setFechaBaja(null);
                $empresa->setMotivoBaja(null);
                $empresa->setEstadoAdministrativoPrevencion(null);
                //fix Ticket#2025032010000123 — PROBLEMATICA BAJA - REACTIVACION EMPRESAS 31/03/2025
                foreach ($trabajadores as $trabajador) {
                    if($trabajador->getMotivoBaja() == 'Baja empresa automática.') {
                        $trabajador->setFechaBaja(null);
                        $trabajador->setMotivoBaja(null);
                        $trabajador->setActivo(true);
                        $trabajadorId = $trabajador->getTrabajador()->getId();
                        $puestosTrabajo = $em->getRepository('App\Entity\PuestoTrabajoTrabajador')->findBy(['trabajador' => $trabajadorId, 'empresa' => $id]);
                        foreach ($puestosTrabajo as $puestoTrabajo) {
                            if($puestoTrabajo->getObservaciones() == 'Baja empresa automática.') {
                                $puestoTrabajo->setFechaBaja(null);
                                $puestoTrabajo->setObservaciones(null);
                                $em->persist($puestoTrabajo);
                            }
                        }
                        $em->persist($trabajador);
                    }
                }
                 $route = 'empresa_show';
                break;
            case 2:
                $empresa->setFechaBajaTecnica(null);
                $empresa->setMotivoBajaTecnica(null);
                $route = 'tecnico_empresa_show';
                break;
            case 3:
                $empresa->setFechaBajaVigilanciaSalud(null);
                $empresa->setMotivoBajaVigilanciaSalud(null);
                $route = 'medico_empresa_show';
                break;
            default:
                $route = 'empresa_show';
        }

        $em->persist($empresa);
        $em->flush();

        $traduccion = $translator->trans('TRANS_UPDATE_OK');
        $this->addFlash('success', $traduccion);

        return $this->redirectToRoute($route);
    }

    public function updateEmpresa(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditEmpresaSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $rolId = $privilegios->getId();

        $user = $this->getUser();
        $usuario = $em->getRepository('App\Entity\User')->find($user);
        $estadoaux2 = "";
        $empresa = $em->getRepository('App\Entity\Empresa')->find($id);
        if ($empresa) {
            $estadoAreaAdministracion = $empresa->getEstadoAreaAdministracion();

            if ($estadoAreaAdministracion) {
                $estadoaux2 = $estadoAreaAdministracion->getId();

                // Resto del código...
            } else {
                // Manejar el caso en el que getEstadoAreaAdministracion() devuelve null
            }
        } else {
            // Manejar el caso en el que no se encuentra la entidad Empresa
        }
        $session->set('empresa', $empresa);
        $empresaId = $empresa->getId();

        if (!$empresa) {
            throw $this->createNotFoundException(
                'La empresa con id ' . $id . ' no existe'
            );
        }

        //Miramos si la empresa esta impagada
        $impagadaSn = false;
        if (!is_null($empresa->getEstadoAreaAdministracion())) {
            $estadoAreaAdministrativoId = $empresa->getEstadoAreaAdministracion()->getId();
            if ($estadoAreaAdministrativoId != 4 && ($rolId != 1 && $rolId != 5)) {
                $impagadaSn = true;
            }
        } else {
            $impagadaSn = false;
        }

        //Buscamos todos los tecnicos
        $listTecnicos = $em->getRepository('App\Entity\Tecnico')->findBy(array('anulado' => false));

        //Buscamos todos las funciones de correo
        $listFuncionCorreo = $em->getRepository('App\Entity\FuncionCorreo')->findAll();

        //Buscamos los tecnicos de la empresa
        $tecnicoEmpresa = $em->getRepository('App\Entity\TecnicoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos los correos de la empresa
        $correoEmpresa = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos todos los cnaes
        $listCnae = $em->getRepository('App\Entity\Cnae')->findBy(array('anulado' => false));
        $cnaeEmpresa = $em->getRepository('App\Entity\CnaeEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos los trabajadores de la empresa
        $trabajadoresEmpresa = $em->getRepository('App\Entity\TrabajadorEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos los centros de trabajo de la empresa
        $centroTrabajoEmpresa = $em->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos los contratos de la empresa
        $contratosEmpresa = $this->buscarContratosEmpresa($empresaId);

        //Buscamos las renovaciones de la empresa
        $query = "select a.id, a.fechainicio, a.fechavencimiento, b.contrato, b.tipo_contrato_id, a.renovado, a.cancelada, a.documento_renovacion, b.id as contratoId, b.fichero_id from renovacion a
				inner join contrato b on a.contrato_id = b.id
			where b.empresa_id = $empresaId
			and a.anulado = false 
			and b.anulado = false";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $renovacionesEmpresa = $stmt->fetchAll();

        //Buscamos los pagos pendientes de la empresa
        $query = "select b.id, a.contrato, a.fechainicio, b.vencimiento, b.importe_iva, b.importe_total, b.facturado from contrato a
			inner join contrato_pago b on a.id = b.contrato_id 
			where a.empresa_id = $empresaId
			and b.facturado = false
			and a.anulado = false 
			and b.anulado = false
			and a.cancelado = false";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $pagosPendientesEmpresa = $stmt->fetchAll();

        //Buscamos las facturas de la empresa
        $facturasEmpresa = $this->buscarFacturasEmpresa($empresaId);

        //Buscamos el balance economico de la empresa
        $balanceEmpresa = $this->createBalanceEconomico($em, $empresaId);

        //Buscamos los datos bancarios de la empresa
        $datosBancariosEmpresa = $this->getDoctrine()->getRepository('App\Entity\DatosBancarios')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos las plantillas de la carpeta contratos
        $carpetaContratos = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(5);
        $plantillasContratos = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaContratos, 'anulado' => false));

        //Buscamos las plantillas de la carpeta facturas
        $carpetaFacturas = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(6);
        $plantillasFacturas = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaFacturas, 'anulado' => false));

        //Buscamos las notificaciones de la empresa
        $notificacionesEmpresa = $this->getDoctrine()->getRepository('App\Entity\EmpresaNotificacion')->findBy(array('empresa' => $empresa));

        //Buscamos las plantillas de la carpeta notificaciones
        $carpetaNotificaciones = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(7);
        $plantillasNotifaciones = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaNotificaciones, 'anulado' => false));

        //Buscamos las certificaciones de la empresa
        $certificacionesEmpresa = $this->getDoctrine()->getRepository('App\Entity\EmpresaCertificacion')->findBy(array('empresa' => $empresa));

        //Buscamos las plantillas de la carpeta certificaciones
        $carpetaCertificaciones = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(4);
        $plantillasCertificaciones = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaCertificaciones, 'anulado' => false));

        //Buscamos los conceptos
        $conceptosTarifas = $em->getRepository('App\Entity\Concepto')->findBy(array('anulado' => false));

        //Buscamos las tarifas de la empresa
        $tarifasPrl = $this->getDoctrine()->getRepository('App\Entity\TarifaPrl')->findBy(array('empresa' => $empresa, 'anulado' => false));
        $tarifasRevisionesMedicas = $this->getDoctrine()->getRepository('App\Entity\TarifaRevisionMedica')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos las certificaciones de la empresa
        $modelo347Empresa = $this->getDoctrine()->getRepository('App\Entity\EmpresaModelo347')->findBy(array('empresa' => $empresa));

        //Buscamos las plantillas de la carpeta modelo 347
        $carpetaModelo347 = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(21);
        $plantillasModelo347 = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaModelo347, 'anulado' => false));

        //Buscamos la gestión documental de la empresa
        $tree = $this->buscarGestionDocumentalEmpresa($empresaId);

        $query = "select * from gdoc_empresa_carpeta where anulado = false and (empresa_id = $id or compartida = true)";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $carpetas = $stmt->fetchAll();

        //Buscamos las plantillas de la carpeta manual vs
        $carpetaManualVs = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(3);
        $plantillasManualVs = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaManualVs, 'anulado' => false));
        $manualVs = $this->getDoctrine()->getRepository('App\Entity\EmpresaManualVs')->findBy(array('empresa' => $empresa));

        $form = $this->createForm(EmpresaType::class, $empresa, array('disabled' => $empresa->getAnulado()));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $empresa2 = $form->getData();
            $estadoAreaAdministracion = $empresa2->getEstadoAreaAdministracion();
            $estadoaux3 = "";
            if ($estadoAreaAdministracion) {
                $estadoaux3 = $estadoAreaAdministracion->getId();

                // Resto del código...
            } else {
                // Manejar el caso en el que getEstadoAreaAdministracion() devuelve null
            }
            if ($estadoaux2 === $estadoaux3) {
                $empresa = $form->getData();
            } else {
                if ($estadoaux3 === 4 || $estadoaux3 === 17 || $estadoaux3 === 19) {
                    ////////////////////////////////////////////////////////////////////
                    //Peticio David Gil
                    $session = $request->getSession();
                    $empresa2 = $form->getData();
                    $estadoaux3 = $empresa2->getEstadoAreaAdministracion()->getId();
                    $user = $this->getUser();
                    $repository = $this->getDoctrine()->getRepository('App\Entity\User');
                    $usuario = $repository->find($user);
                    $username = $usuario->getUsername();
                    switch ($estadoaux3) {
                        case 4:
                            $mensaje = "El usuario " . $username . ", ha cambiado el estado de la empresa: " . $empresa->getEmpresa() . " al estado: < sin especificar> ";
                            break;

                        case 17:
                            $mensaje = "El usuario " . $username . ", ha cambiado el estado de la empresa: " . $empresa->getEmpresa() . " al estado: Impagado-giro devuelto ";
                            break;

                        case 19:
                            $mensaje = "El usuario " . $username . ", ha cambiado el estado de la empresa: " . $empresa->getEmpresa() . " al estado: Contado no regularizado ";
                            break;
                    }

                    $connection = $this->getDoctrine()->getManager()->getConnection();
                    $tecnicoslista = $em->getRepository('App\Entity\TecnicoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

                    foreach ($tecnicoslista as $tecnicoEmpresa) {
                        $tecnicoId = $tecnicoEmpresa->getTecnico();

                        // Obtener la entidad Tecnico usando el id
                        $tecnico = $em->getRepository('App\Entity\Tecnico')->find($tecnicoId);
                        $destinatario = $tecnico->getAlias();

                        $query = "INSERT INTO notificaciones_internas (id, fecha, destinatario, remitente, mensaje, leido)
                          VALUES (:id, :fecha, :destinatario, :remitente, :mensaje, false)";
                        $statement = $connection->prepare($query);
                        $querySelectUpdated = "SELECT id FROM notificaciones_internas ORDER BY id DESC LIMIT 1;";
                        $statementSelect = $connection->prepare($querySelectUpdated);
                        $statementSelect->execute();
                        $updatedRows = $statementSelect->fetchAll();
                        foreach ($updatedRows as $row) {
                            $ultimoId = $row['id'];
                            // Hacer algo con el valor de $id
                        }
                        $fechaActual = new \DateTime();
                        $fechaFormateada = $fechaActual->format('d-m-Y H:i:s'); // Formatear la fecha a formato 'YYYY-MM-DD HH:MM:SS'
                        // Reemplazar saltos de línea por otro carácter (por ejemplo, <br>)
                        $mensajeSinSaltos = str_replace("\n", " ", $mensaje);
                        $params = [
                            'id' => $ultimoId + 1,
                            'fecha' => $fechaFormateada,
                            'destinatario' => $destinatario,
                            'remitente' => $username,
                            'mensaje' => $mensajeSinSaltos,
                        ];

                        $statement->execute($params);
                    }
                    ///////////////////////////////////////////////////////////////////////
                } else {
                    $empresa = $form->getData();
                }
            }
            $em->persist($empresa);
            $em->flush();

            //Buscamos el centro de trabajo y actualizamos los datos
            /*$centroTrabajoEmpresa = $this->getDoctrine()->getRepository('App\Entity\CentroTrabajoEmpresa')->findOneBy(array('empresa' => $empresa, 'anulado' => false));

            if(!is_null($centroTrabajoEmpresa)){
                $centroTrabajo = $centroTrabajoEmpresa->getCentro();
                $centroTrabajo->setNombre($empresa->getEmpresa());
                $centroTrabajo->setTrabajadores($empresa->getTrabajadores());
                $centroTrabajo->setCcc($empresa->getCcc());
                $centroTrabajo->setDireccion($empresa->getDomicilioFiscal());
                $centroTrabajo->setLocalidad($empresa->getLocalidadFiscal());
                $centroTrabajo->setCodigoPostal($empresa->getCodigoPostalFiscal());
                $centroTrabajo->setProvincia($empresa->getProvinciaFiscal());
                $centroTrabajo->setFax($empresa->getFax());
                $centroTrabajo->setTelefono($empresa->getTelefono1());
                $centroTrabajo->setTelefono2($empresa->getTelefono2());
                $centroTrabajo->setProvinciaSerpa($empresa->getProvinciaFiscalSerpa());
                $centroTrabajo->setMunicipioSerpa($empresa->getMunicipioFiscalSerpa());
                $centroTrabajo->setActividadCentro($empresa->getActividad());

                //Buscamos el CNAE de la empresa
                $cnaeEmpresa = $this->getDoctrine()->getRepository('App\Entity\CnaeEmpresa')->findOneBy(array('empresa' => $empresa, 'anulado' => false, 'principal' => true));

                if(!is_null($cnaeEmpresa)){
                    $centroTrabajo->setCnae($cnaeEmpresa->getCnae());
                }

                $em->persist($centroTrabajo);
                $em->flush();
            }*/
            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('empresa_update', array('id' => $empresa->getId()));
        }

        $ficheroEmpresa = new GdocEmpresa();
        $form2 = $this->createForm(GdocEmpresaType::class, $ficheroEmpresa, array('empresaId' => $empresaId));

        $form2->handleRequest($request);
        if ($form2->isSubmitted()) {
            $ficheroEmpresa = $form2->getData();

            //Buscamos la configuración de la gestión documental
            $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
            $rutaCompleta = $gdocConfig->getRuta() . $gdocConfig->getCarpetaEmpresa() . '/' . $id;

            if (!is_dir($rutaCompleta)) {
                mkdir($rutaCompleta);
            }

            $fichero = $form2->get('media')->getData();
            $nombreFichero = $fichero->getClientOriginalName();
            $fichero->move($rutaCompleta, $nombreFichero);

            $ficheroEmpresa->setNombreCompleto($nombreFichero);
            $ficheroEmpresa->setUsuario($usuario);
            $ficheroEmpresa->setDtcrea(new \DateTime());
            $ficheroEmpresa->setAnulado(false);
            $ficheroEmpresa->setMedia(null);
            $ficheroEmpresa->setEmpresa($empresa);

            //Comprobamos si el fichero ya estaba subido
            $ficheroBusca = $em->getRepository('App\Entity\GdocEmpresa')->findOneBy(array('nombreCompleto' => $nombreFichero, 'anulado' => false));
            if (!is_null($ficheroBusca)) {
                $ficheroBusca->setUsuarioModifica($usuario);
                $ficheroBusca->setDtmodifica(new \DateTime());
                $em->persist($ficheroBusca);
                $em->remove($ficheroEmpresa);
                $em->flush();
            } else {
                $em->persist($ficheroEmpresa);
                $em->flush();
            }

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('empresa_update', array('id' => $id));
        }

        $form3 = $this->createForm(TrabajadorImportType::class, null);
        $form3->handleRequest($request);

        if ($form3->isSubmitted()) {

            //Recogemos el fichero a importar
            $fichero = $form3->get('fichero')->getData();
            $extension = $fichero->getClientOriginalExtension();

            if ($extension != 'xlsx') {
                $traduccion = $translator->trans('TRANS_ERROR_EXTENSION');
                $this->addFlash('danger',  $traduccion);
            } else {

                $em->beginTransaction();

                try {
                    $this->importarTrabajadoresITA($em, $fichero, $empresa);
                    $traduccion = $translator->trans('TRANS_IMPORTACION_OK');
                    $this->addFlash('success',  $traduccion);
                } catch (\Exception $e) {
                    $em->rollBack();

                    $traduccion = $translator->trans('TRANS_IMPORTACION_KO');
                    $this->addFlash('danger', $traduccion);
                    return $this->redirectToRoute('empresa_update', array('id' => $id));
                }

                $em->commit();
            }
            return $this->redirectToRoute('empresa_update', array('id' => $id));
        }

        return $this->render('empresa/edit.html.twig',  array('impagadaSn' => $impagadaSn, 'carpetas' => $carpetas, 'tree' => $tree, 'form2' => $form2->createView(), 'form3' => $form3->createView(), 'listPlantillasModelo347' => $plantillasModelo347, 'modelo347Empresa' => $modelo347Empresa, 'tarifasPrl' => $tarifasPrl, 'tarifasRevisionesMedicas' => $tarifasRevisionesMedicas, 'listConceptoTarifas' => $conceptosTarifas, 'listPlantillasNotificaciones' => $plantillasNotifaciones, 'listPlantillasCertificaciones' => $plantillasCertificaciones, 'notificaciones' => $notificacionesEmpresa, 'certificaciones' => $certificacionesEmpresa, 'listPlantillasFacturas' => $plantillasFacturas, 'listFuncionCorreo' => $listFuncionCorreo, 'listPlantillasContratos' => $plantillasContratos, 'datosBancarios' => $datosBancariosEmpresa, 'balanceEconomico' => $balanceEmpresa, 'facturas' => $facturasEmpresa, 'pagos' => $pagosPendientesEmpresa, 'renovaciones' => $renovacionesEmpresa, 'contratos' => $contratosEmpresa, 'form' => $form->createView(), 'tecnicos' => $tecnicoEmpresa, 'listTecnicos' => $listTecnicos, 'correos' => $correoEmpresa, 'cnae' => $cnaeEmpresa, 'listCnae' => $listCnae, 'trabajadoresEmpresa' => $trabajadoresEmpresa, 'centroTrabajoEmpresa' => $centroTrabajoEmpresa, 'listPlantillasManualVs' => $plantillasManualVs, 'manualVsEmpresa' => $manualVs));
    }

    public function updateEmpresaTecnico(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditEmpresaTecnicoSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $rolId = $privilegios->getId();

        $user = $this->getUser();
        $usuario = $em->getRepository('App\Entity\User')->find($user);

        $empresa = $em->getRepository('App\Entity\Empresa')->find($id);
        $session->set('empresa', $empresa);
        $empresaId = $empresa->getId();

        if (!$empresa) {
            throw $this->createNotFoundException(
                'La empresa con id ' . $empresaId . ' no existe'
            );
        }

        //Miramos si la empresa esta impagada
        $impagadaSn = false;
        if (!is_null($empresa->getEstadoAreaAdministracion())) {
            $estadoAreaAdministrativoId = $empresa->getEstadoAreaAdministracion()->getId();
            if ($estadoAreaAdministrativoId != 4 && ($rolId != 1 && $rolId != 5 && $rolId != 3)) {
                $impagadaSn = true;
            }
        } else {
            $impagadaSn = false;
        }

        //Buscamos todos los tecnicos
        $listTecnicos = $em->getRepository('App\Entity\Tecnico')->findBy(array('anulado' => false));

        //Buscamos todos las funciones de correo
        $listFuncionCorreo = $em->getRepository('App\Entity\FuncionCorreo')->findAll();

        //Buscamos los tecnicos de la empresa
        $tecnicoEmpresa = $em->getRepository('App\Entity\TecnicoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos los correos de la empresa
        $correoEmpresa = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos todos los cnaes
        $listCnae = $em->getRepository('App\Entity\Cnae')->findBy(array('anulado' => false));
        $cnaeEmpresa = $em->getRepository('App\Entity\CnaeEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos los trabajadores de la empresa
        $trabajadoresEmpresa = $em->getRepository('App\Entity\TrabajadorEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));
        $trabajadoresEmpresaArray = $this->buscaTrabajadoresEmpresaTecnico($id, $trabajadoresEmpresa);

        //Buscamos los centros de trabajo de la empresa
        $centroTrabajoEmpresa = $em->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('empresa' => $empresa));

        //Buscamos los accidentes de la empresa
        $accidentesEmpresa = $em->getRepository('App\Entity\EmpresaAccidenteLaboral')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos las plantillas de la carpeta accidentes
        $carpetaAccidentes = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(9);
        $plantillasAccidentes = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaAccidentes, 'anulado' => false));

        //Buscamos los plan de prevencion de la empresa
        //$planPrevencionEmpresa = $em->getRepository('App\Entity\EmpresaPlanPrevencion')->findBy(array('empresa' => $empresa));
        //Buscamos planes de prevencion de la empresa
        $query2 = "select a.id, a.empresa_id, a.fecha, a.fichero_id, (select numero from (select id, empresa_id, row_number() over(order by fecha asc) as numero from empresa_plan_prevencion
                where empresa_id = a.empresa_id
                order by fecha asc) consulta
                where id = a.id) as numero from empresa_plan_prevencion a 
                where a.empresa_id = $empresaId
                group by a.id, a.fecha, a.fichero_id
                order by a.fecha desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query2);
        $stmt->execute();
        $planPrevencionEmpresa = $stmt->fetchAll();



        //Buscamos las plantillas de la carpeta plan de prevencion
        $carpetaPlanPrevencion = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(11);
        $plantillasPlanPrevencion = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaPlanPrevencion, 'anulado' => false));

        //Petició 28/07/2023
        //Buscamos las evaluaciones de la empresa
        $query = "select a.id, a.finalizada, 
                       (select numero from (select id, empresa_id, row_number() over(order by fecha_inicio asc) as numero from evaluacion
                        where anulado = false 
                        and empresa_id = a.empresa_id
                        order by fecha_inicio asc) consulta
                        where id = a.id) as numero, 
                       b.empresa, 
                       string_agg(d.direccion::text, ' , '::text) as centros, 
                       to_char(a.fecha_inicio, 'DD/MM/YYYY') as fechainicio, 
                       to_char(a.fecha_fin, 'DD/MM/YYYY') as fechafin, 
                       e.descripcion as tipo,
                       f.descripcion as metodologia, 
                       a.fichero_id, 
                       a.fichero_centro 
                from evaluacion a 
                inner join empresa b on a.empresa_id = b.id
                left join evaluacion_centro_trabajo c on a.id = c.evaluacion_id
                left join centro d on c.centro_id = d.id
                left join tipo_evaluacion e on a.tipo_evaluacion_id = e.id
                left join metodologia_evaluacion f on a.metodologia_id = f.id 
                where a.anulado = false
                and a.empresa_id = $empresaId
                group by a.id, a.finalizada, b.empresa, a.fecha_inicio, a.fecha_fin, e.descripcion, f.descripcion, a.fichero_id, a.fichero_centro
                order by a.fecha_inicio desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $evaluaciones = $stmt->fetchAll();

        //Buscamos las maquinas de la empresa
        $maquinas = $this->getDoctrine()->getRepository('App\Entity\MaquinaEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos la gestión documental de la empresa
        $tree = $this->buscarGestionDocumentalEmpresa($empresaId);
        //Peticio 01/09/2023
        $tree2 = $this->buscarGestionDocumentalEmpresa2($empresaId);
        //////Nova petició David Gil
        $hojasActuacion = $this->getDoctrine()->getRepository('App\Entity\GdocEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false, 'carpeta' => 25));

        $query = "select * from gdoc_empresa_carpeta where anulado = false and (empresa_id = $id or compartida = true)";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $carpetas = $stmt->fetchAll();

        //Recuperamos las maquinas genericas de los puestos de trabajo
        $maquinasGenericasPuestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoMaquinaGenerica')->findBy(array('anulado' => false, 'empresa' => $empresa));

        //Recuperamos las maquinas empresa de los puestos de trabajo
        $maquinasEmpresaPuestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoMaquinaEmpresa')->findBy(array('anulado' => false, 'empresa' => $empresa));

        //Recuperamos las maquinas genericas de los puestos de trabajo
        $maquinasGenericasZonaTrabajo = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajoMaquinaGenerica')->findBy(array('anulado' => false, 'empresa' => $empresa));

        //Recuperamos las maquinas empresa de los puestos de trabajo
        $maquinasEmpresaZonaTrabajo = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajoMaquinaEmpresa')->findBy(array('anulado' => false, 'empresa' => $empresa));

        //Buscamos los puestos de trabajo del centro
        $listPuestosTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoCentro')->findBy(array('anulado' => false, 'empresa' => $empresa));

        //Buscamos las zonas de trabajo del centro
        $listZonasTrabajo = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajo')->findBy(array('anulado' => false, 'empresa' => $empresa), array('descripcion' => 'ASC'));

        //Buscamos las maquinas genericas
        $listMaquinasGenericas = $this->getDoctrine()->getRepository('App\Entity\MaquinaGenerica')->findBy(array('anulado' => false), array('descripcion' => 'ASC'));

        //Buscamos las maquinas del centro
        $listMaquinasEmpresa = $this->getDoctrine()->getRepository('App\Entity\MaquinaEmpresa')->findBy(array('anulado' => false, 'empresa' => $empresa), array('descripcion' => 'ASC'));

        //Buscamos los puesto de trabajo genericos
        $listPuestosGenericos = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoGenerico')->findBy(array('anulado' => false), array('descripcion' => 'ASC'));

        //Recuperamos las zonas del centro
        $zonas = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajo')->findBy(array('anulado' => false, 'empresa' => $empresaId));

        //Buscamos las tarifas de la empresa
        $tarifasRevisionesMedicas = $this->getDoctrine()->getRepository('App\Entity\TarifaRevisionMedica')->findBy(array('empresa' => $empresa, 'anulado' => false));

        $treePuestos = $this->buscarTreePuestosTrabajoEmpresa($empresaId, $empresa);

        //Buscamos el ultimo contrato de la empresa
        $query = "select fechainicio from contrato where empresa_id = $empresaId and anulado = false and cancelado = false order by fechainicio desc limit 1";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $resultContrato = $stmt->fetchAll();
        $fechaContrato = null;
        $dateContrato = null;
        if (count($resultContrato) > 0) {
            $fechaContrato = $resultContrato[0]['fechainicio'];
            $dateContrato = new \DateTime($fechaContrato);
        }

        $form = $this->createForm(EmpresaTecnicoType::class, $empresa, array('disabled' => $empresa->getAnulado(), 'fechaContrato' => $dateContrato));
        $form->handleRequest($request);

        //Comprobamos si la empresa tiene el logo informado
        $logo = $empresa->getLogo();

        //Buscamos los protocolos de acoso de la empresa
        $protocoloAcoso = $em->getRepository('App\Entity\EmpresaProtocoloAcoso')->findBy(array('empresa' => $empresa));

        //Buscamos las plantillas de la carpeta protocolo de acoso
        $carpetaProtocoloAcoso = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(22);
        $plantillasProtocoloAcoso = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaProtocoloAcoso, 'anulado' => false));

        if ($form->isSubmitted()) {
            $empresa = $form->getData();
            $em->persist($empresa);
            $em->flush();

            //Si ha informado el logo lo guardamos
            $logo = $form->get('logo')->getData();
            if (!is_null($logo)) {

                //Obtenemos el nombre y la extension
                $filename =  $logo->getClientOriginalName();

                move_uploaded_file($logo, "upload/media/logos/$filename");
                $path_info = pathinfo("upload/media/logos/$filename");
                $extension = $path_info['extension'];

                $newName = $empresaId . '.' . $extension;

                //Renombramos el logo
                rename("upload/media/logos/$filename", "upload/media/logos/$newName");

                //Añadimos el logo a la empresa
                $empresa->setLogo($newName);
                $em->persist($empresa);
                $em->flush();
            }

            //Buscamos el centro de trabajo y actualizamos los datos
            /*$centroTrabajoEmpresa = $this->getDoctrine()->getRepository('App\Entity\CentroTrabajoEmpresa')->findOneBy(array('empresa' => $empresa, 'anulado' => false));

            if(!is_null($centroTrabajoEmpresa)){
                $centroTrabajo = $centroTrabajoEmpresa->getCentro();
                $centroTrabajo->setNombre($empresa->getEmpresa());
                $centroTrabajo->setTrabajadores($empresa->getTrabajadores());
                $centroTrabajo->setCcc($empresa->getCcc());
                $centroTrabajo->setDireccion($empresa->getDomicilioFiscal());
                $centroTrabajo->setLocalidad($empresa->getLocalidadFiscal());
                $centroTrabajo->setCodigoPostal($empresa->getCodigoPostalFiscal());
                $centroTrabajo->setProvincia($empresa->getProvinciaFiscal());
                $centroTrabajo->setFax($empresa->getFax());
                $centroTrabajo->setTelefono($empresa->getTelefono1());
                $centroTrabajo->setTelefono2($empresa->getTelefono2());
                $centroTrabajo->setProvinciaSerpa($empresa->getProvinciaFiscalSerpa());
                $centroTrabajo->setMunicipioSerpa($empresa->getMunicipioFiscalSerpa());
                $centroTrabajo->setActividadCentro($empresa->getActividad());

                //Buscamos el CNAE de la empresa
                $cnaeEmpresa = $this->getDoctrine()->getRepository('App\Entity\CnaeEmpresa')->findOneBy(array('empresa' => $empresa, 'anulado' => false, 'principal' => true));

                if(!is_null($cnaeEmpresa)){
                    $centroTrabajo->setCnae($cnaeEmpresa->getCnae());
                }

                $em->persist($centroTrabajo);
                $em->flush();
            }*/

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('tecnico_empresa_update', array('id' => $empresa->getId()));
        }

        $ficheroEmpresa = new GdocEmpresa();
        $form2 = $this->createForm(GdocEmpresaType::class, $ficheroEmpresa, array('empresaId' => $empresaId));

        $form2->handleRequest($request);

        if ($form2->isSubmitted()) {
            $ficheroEmpresa = $form2->getData();

            //Buscamos la configuración de la gestión documental
            $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
            $rutaCompleta = $gdocConfig->getRuta() . $gdocConfig->getCarpetaEmpresa() . '/' . $id;

            if (!is_dir($rutaCompleta)) {
                mkdir($rutaCompleta);
            }

            $fichero = $form2->get('media')->getData();
            $nombreFichero = $fichero->getClientOriginalName();
            $fichero->move($rutaCompleta, $nombreFichero);

            $ficheroEmpresa->setNombreCompleto($nombreFichero);
            $ficheroEmpresa->setUsuario($usuario);
            $ficheroEmpresa->setDtcrea(new \DateTime());
            $ficheroEmpresa->setAnulado(false);
            $ficheroEmpresa->setMedia(null);
            $ficheroEmpresa->setEmpresa($empresa);

            //Comprobamos si el fichero ya estaba subido
            $ficheroBusca = $em->getRepository('App\Entity\GdocEmpresa')->findOneBy(array('nombreCompleto' => $nombreFichero, 'anulado' => false));
            if (!is_null($ficheroBusca)) {
                $ficheroBusca->setUsuarioModifica($usuario);
                $ficheroBusca->setDtmodifica(new \DateTime());
                $em->persist($ficheroBusca);
                $em->remove($ficheroEmpresa);
                $em->flush();
            } else {
                $em->persist($ficheroEmpresa);
                $em->flush();
            }

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('tecnico_empresa_update', array('id' => $id));
        }

        $form3 = $this->createForm(TrabajadorImportType::class, null);
        $form3->handleRequest($request);

        if ($form3->isSubmitted()) {

            //Recogemos el fichero a importar
            $fichero = $form3->get('fichero')->getData();
            $extension = $fichero->getClientOriginalExtension();

            if ($extension != 'xlsx') {
                $traduccion = $translator->trans('TRANS_ERROR_EXTENSION');
                $this->addFlash('danger',  $traduccion);
            } else {

                $em->beginTransaction();

                try {
                    $this->importarTrabajadoresITA($em, $fichero, $empresa);
                    $traduccion = $translator->trans('TRANS_IMPORTACION_OK');
                    $this->addFlash('success',  $traduccion);
                } catch (\Exception $e) {
                    $em->rollBack();

                    $traduccion = $translator->trans('TRANS_IMPORTACION_KO');
                    $this->addFlash('danger', $traduccion);
                    return $this->redirectToRoute('tecnico_empresa_update', array('id' => $id));
                }

                $em->commit();
            }

            return $this->redirectToRoute('tecnico_empresa_update', array('id' => $id));
        }
        //Petició 01/09/2023
        $form4 = $this->createForm(GdocEmpresaType2::class, $ficheroEmpresa, array('empresaId' => $empresaId));
        $form4->handleRequest($request);
        if ($form4->isSubmitted()) {
            $ficheroEmpresa = $form4->getData();

            //Buscamos la configuración de la gestión documental
            $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
            $rutaCompleta = $gdocConfig->getRuta() . $gdocConfig->getCarpetaEmpresa() . '/' . $id;

            if (!is_dir($rutaCompleta)) {
                mkdir($rutaCompleta);
            }

            $fichero = $form4->get('media')->getData();
            $nombreFichero = $fichero->getClientOriginalName();
            $fichero->move($rutaCompleta, $nombreFichero);

            $ficheroEmpresa->setNombreCompleto($nombreFichero);
            $ficheroEmpresa->setUsuario($usuario);
            $carpetaGdoc = $em->getRepository('App\Entity\GdocEmpresaCarpeta')->find(25);
            $ficheroEmpresa->setCarpeta($carpetaGdoc);
            $ficheroEmpresa->setDtcrea(new \DateTime());
            $ficheroEmpresa->setAnulado(false);
            $ficheroEmpresa->setMedia(null);
            $ficheroEmpresa->setEmpresa($empresa);

            //Comprobamos si el fichero ya estaba subido
            $ficheroBusca = $em->getRepository('App\Entity\GdocEmpresa')->findOneBy(array('nombreCompleto' => $nombreFichero, 'empresa' => $empresa, 'anulado' => false));
            if (!is_null($ficheroBusca)) {
                $ficheroBusca->setUsuarioModifica($usuario);
                $ficheroBusca->setDtmodifica(new \DateTime());
                $em->persist($ficheroBusca);
                $em->remove($ficheroEmpresa);
                $em->flush();
            } else {
                $em->persist($ficheroEmpresa);
                $em->flush();
            }

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('tecnico_empresa_update', array('id' => $id));
        }
        //Peticio 01/09/2023
        return $this->render('empresatecnico/edit.html.twig',  array('hojaActu' => $hojasActuacion, 'tree2' => $tree2, 'protocoloAcoso' => $protocoloAcoso, 'listPlantillasProtocoloAcoso' => $plantillasProtocoloAcoso, 'tarifasRevisionesMedicas' => $tarifasRevisionesMedicas, 'treePuestos' => $treePuestos, 'tree' => $tree, 'carpetas' => $carpetas, 'form3' => $form3->createView(), 'form2' => $form2->createView(), 'form4' => $form4->createView(), 'logo' => $logo, 'maquinas' => $maquinas, 'listFuncionCorreo' => $listFuncionCorreo, 'tecnicos' => $tecnicoEmpresa, 'listTecnicos' => $listTecnicos, 'correos' => $correoEmpresa, 'form' => $form->createView(), 'cnae' => $cnaeEmpresa, 'listCnae' => $listCnae, 'listTrabajadoresEmpresa' => $trabajadoresEmpresa, 'trabajadoresEmpresa' => $trabajadoresEmpresaArray, 'centroTrabajoEmpresa' => $centroTrabajoEmpresa, 'accidentesEmpresa' => $accidentesEmpresa, 'listPlantillaAccidentes' => $plantillasAccidentes, 'evaluaciones' => $evaluaciones, 'planPrevencion' => $planPrevencionEmpresa, 'listPlantillaPlanPrevencion' => $plantillasPlanPrevencion, 'impagadaSn' => $impagadaSn, 'zonasTrabajo' => $zonas, 'maquinasGenericasPuestoTrabajo' => $maquinasGenericasPuestoTrabajo, 'maquinasGenericasZonaTrabajo' => $maquinasGenericasZonaTrabajo, 'listPuestosTrabajo' => $listPuestosTrabajo, 'listMaquinasGenericas' => $listMaquinasGenericas, 'listZonasTrabajo' => $listZonasTrabajo, 'maquinasEmpresaPuestoTrabajo' => $maquinasEmpresaPuestoTrabajo, 'maquinasEmpresaZonaTrabajo' => $maquinasEmpresaZonaTrabajo, 'listMaquinasEmpresa' => $listMaquinasEmpresa, 'listPuestosGenericos' => $listPuestosGenericos));
    }

    public function updateEmpresaMedico(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditEmpresaMedicoSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $rolId = $privilegios->getId();

        $user = $this->getUser();
        $usuario = $em->getRepository('App\Entity\User')->find($user);

        $empresa = $em->getRepository('App\Entity\Empresa')->find($id);
        $session->set('empresa', $empresa);
        $empresaId = $empresa->getId();

        if (!$empresa) {
            throw $this->createNotFoundException(
                'La empresa con id ' . $empresaId . ' no existe'
            );
        }
        // Miramos si la empresa esta impagada
        $impagadaSn = false;
        if (!is_null($empresa->getEstadoAreaAdministracion())) {
            $estadoAreaAdministrativoId = $empresa->getEstadoAreaAdministracion()->getId();
            if ($estadoAreaAdministrativoId != 4 && ($rolId != 1 && $rolId != 5 && $rolId != 4)) {
                $impagadaSn = true;
            }
        } else {
            $impagadaSn = false;
        }
        //Buscamos todos los tecnicos
        $listTecnicos = $em->getRepository('App\Entity\Tecnico')->findBy(array('anulado' => false));

        //Buscamos los tecnicos de la empresa
        $tecnicoEmpresa = $em->getRepository('App\Entity\TecnicoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos todos las funciones de correo
        $listFuncionCorreo = $em->getRepository('App\Entity\FuncionCorreo')->findAll();

        //Buscamos los correos de la empresa
        $correoEmpresa = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos todos los cnaes
        $listCnae = $em->getRepository('App\Entity\Cnae')->findBy(array('anulado' => false));
        $cnaeEmpresa = $em->getRepository('App\Entity\CnaeEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos los trabajadores de la empresa
        $trabajadoresEmpresa = $em->getRepository('App\Entity\TrabajadorEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        $trabajadoresEmpresaArray = array();
        foreach ($trabajadoresEmpresa as $te) {
            if (!is_null($te->getTrabajador())) {
                $trabajadorId = $te->getTrabajador()->getId();

                $item['id'] = $trabajadorId;
                $item['trabajador'] = $te->getTrabajador()->getNombre();
                $item['dni'] = $te->getTrabajador()->getDni();
                //Buscamos la ultima revision para este trabajador
                $query = "select to_char(a.fecha, 'DD/MM/YYYY') as fecha, b.descripcion as puesto, a.apto_id from revision a
                    inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                    where a.anulado = false
                    and a.trabajador_id = $trabajadorId
                    and a.empresa_id = $id
                    order by a.fecha desc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $ultimaRevision = $stmt->fetchAll();

                if (count($ultimaRevision) > 0) {
                    $item['puesto'] = $ultimaRevision[0]['puesto'];
                    $item['apto'] = $ultimaRevision[0]['apto_id'];
                    $item['fechaultima'] = $ultimaRevision[0]['fecha'];
                } else {
                    $item['puesto'] = "";
                    $item['apto'] = "";
                    $item['fechaultima'] = "";
                }
                array_push($trabajadoresEmpresaArray, $item);
            }
        }
        //Buscamos los centros de trabajo de la empresa
        $centroTrabajoEmpresa = $em->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        //Buscamos las memorias de la empresa
        $memoriaEmpresa = $em->getRepository('App\Entity\EmpresaMemoria')->findBy(array('empresa' => $empresa, 'anulado' => false), array('anyo' => 'DESC'));

        //Buscamos las plantilla de la memoria
        $carpetaPlantillasMemoria = $em->getRepository('App\Entity\GdocPlantillasCarpeta')->find(18);
        $listPlantillasMemoria = $em->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaPlantillasMemoria, 'anulado' => false));

        //Buscamos los estudios de la empresa
        $estudioEmpresa = $em->getRepository('App\Entity\EmpresaEstudioEpidemiologico')->findBy(array('empresa' => $empresa, 'anulado' => false), array('anyo' => 'DESC'));

        //Buscamos las plantilla de estudio
        $carpetaPlantillasEstudio = $em->getRepository('App\Entity\GdocPlantillasCarpeta')->find(19);
        $listPlantillasEstudio = $em->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaPlantillasEstudio, 'anulado' => false));

        //Buscamos las revisiones de la empresa
        $query = "select a.id, to_char(a.fecha, 'DD/MM/YYYY') as fecha, to_char(a.fecha, 'YYYYMMDDHHmm') as fechatimestamp, b.nombre as trabajador, c.descripcion as puesto, d.empresa, a.apto_id, a.fichero_id, e.id as estadoId, e.descripcion as estado, a.fichero_resumen_id, f.descripcion as doctor from revision a
            inner join trabajador b on a.trabajador_id = b.id
            inner join puesto_trabajo_centro c on a.puesto_trabajo_id = c.id
            inner join empresa d on a.empresa_id = d.id
            left join estado_revision e on a.estado_id = e.id
            left join doctor f on a.medico_id = f.id
            where a.anulado = false
            and b.anulado = false
            and c.anulado = false
            and a.empresa_id = $id
            order by a.fecha desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $revisionEmpresa = $stmt->fetchAll();

        //Buscamos las plantillas de la carpeta resumen revision
        $carpetaResumenRevision = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(16);
        $plantillasResumen = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaResumenRevision, 'anulado' => false));

        //Buscamos las plantillas de la carpeta aptitud
        $carpetaAptitud = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(14);
        $plantillasAptitud = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaAptitud, 'anulado' => false));

        //Buscamos la gestión documental de la empresa
        $tree = $this->buscarGestionDocumentalEmpresa($empresaId);

        $query = "select * from gdoc_empresa_carpeta where anulado = false and (empresa_id = $id or compartida = true)";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $carpetas = $stmt->fetchAll();

        $form = $this->createForm(EmpresaMedicoType::class, $empresa, array('disabled' => $empresa->getAnulado()));
        $form->handleRequest($request);

        //Comprobamos si la empresa tiene el logo informado
        $logo = $empresa->getLogo();

        if ($form->isSubmitted()) {
            $empresa = $form->getData();
            $em->persist($empresa);
            $em->flush();
            //Si ha informado el logo lo guardamos
            $logo = $form->get('logo')->getData();
            if (!is_null($logo)) {

                //Obtenemos el nombre y la extension
                $filename =  $logo->getClientOriginalName();

                move_uploaded_file($logo, "upload/media/logos/$filename");
                $path_info = pathinfo("upload/media/logos/$filename");
                $extension = $path_info['extension'];

                $newName = $empresaId . '.' . $extension;

                //Renombramos el logo
                rename("upload/media/logos/$filename", "upload/media/logos/$newName");

                //Añadimos el logo a la empresa
                $empresa->setLogo($newName);
                $em->persist($empresa);
                $em->flush();
            }
            //Buscamos el centro de trabajo y actualizamos los datos
            /*$centroTrabajoEmpresa = $this->getDoctrine()->getRepository('App\Entity\CentroTrabajoEmpresa')->findOneBy(array('empresa' => $empresa, 'anulado' => false));

            if(!is_null($centroTrabajoEmpresa)){
                $centroTrabajo = $centroTrabajoEmpresa->getCentro();
                $centroTrabajo->setNombre($empresa->getEmpresa());
                $centroTrabajo->setTrabajadores($empresa->getTrabajadores());
                $centroTrabajo->setCcc($empresa->getCcc());
                $centroTrabajo->setDireccion($empresa->getDomicilioFiscal());
                $centroTrabajo->setLocalidad($empresa->getLocalidadFiscal());
                $centroTrabajo->setCodigoPostal($empresa->getCodigoPostalFiscal());
                $centroTrabajo->setProvincia($empresa->getProvinciaFiscal());
                $centroTrabajo->setFax($empresa->getFax());
                $centroTrabajo->setTelefono($empresa->getTelefono1());
                $centroTrabajo->setTelefono2($empresa->getTelefono2());
                $centroTrabajo->setProvinciaSerpa($empresa->getProvinciaFiscalSerpa());
                $centroTrabajo->setMunicipioSerpa($empresa->getMunicipioFiscalSerpa());
                $centroTrabajo->setActividadCentro($empresa->getActividad());

                //Buscamos el CNAE de la empresa
                $cnaeEmpresa = $this->getDoctrine()->getRepository('App\Entity\CnaeEmpresa')->findOneBy(array('empresa' => $empresa, 'anulado' => false, 'principal' => true));

                if(!is_null($cnaeEmpresa)){
                    $centroTrabajo->setCnae($cnaeEmpresa->getCnae());
                }

                $em->persist($centroTrabajo);
                $em->flush();
            }*/
            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('medico_empresa_update', array('id' => $empresa->getId()));
        }
        $ficheroEmpresa = new GdocEmpresa();
        $form2 = $this->createForm(GdocEmpresaType::class, $ficheroEmpresa, array('empresaId' => $empresaId));

        $form2->handleRequest($request);
        if ($form2->isSubmitted()) {
            $ficheroEmpresa = $form2->getData();

            //Buscamos la configuración de la gestión documental
            $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
            $rutaCompleta = $gdocConfig->getRuta() . $gdocConfig->getCarpetaEmpresa() . '/' . $id;

            if (!is_dir($rutaCompleta)) {
                mkdir($rutaCompleta);
            }
            $fichero = $form2->get('media')->getData();
            $nombreFichero = $fichero->getClientOriginalName();
            $fichero->move($rutaCompleta, $nombreFichero);

            $ficheroEmpresa->setNombreCompleto($nombreFichero);
            $ficheroEmpresa->setUsuario($usuario);
            $ficheroEmpresa->setDtcrea(new \DateTime());
            $ficheroEmpresa->setAnulado(false);
            $ficheroEmpresa->setMedia(null);
            $ficheroEmpresa->setEmpresa($empresa);

            //Comprobamos si el fichero ya estaba subido
            $ficheroBusca = $em->getRepository('App\Entity\GdocEmpresa')->findOneBy(array('nombreCompleto' => $nombreFichero, 'anulado' => false));
            if (!is_null($ficheroBusca)) {
                $ficheroBusca->setUsuarioModifica($usuario);
                $ficheroBusca->setDtmodifica(new \DateTime());
                $em->persist($ficheroBusca);
                $em->remove($ficheroEmpresa);
                $em->flush();
            } else {
                $em->persist($ficheroEmpresa);
                $em->flush();
            }
            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('medico_empresa_update', array('id' => $id));
        }
        return $this->render('empresamedico/edit.html.twig',  array('logo' => $logo, 'listFuncionCorreo' => $listFuncionCorreo, 'correos' => $correoEmpresa, 'form' => $form->createView(), 'cnae' => $cnaeEmpresa, 'listCnae' => $listCnae, 'trabajadoresEmpresa' => $trabajadoresEmpresaArray, 'centroTrabajoEmpresa' => $centroTrabajoEmpresa, 'tecnicos' => $tecnicoEmpresa, 'listTecnicos' => $listTecnicos, 'memoriaEmpresa' => $memoriaEmpresa, 'listPlantillasMemoria' => $listPlantillasMemoria, 'estudioEmpresa' => $estudioEmpresa, 'listPlantillasEstudio' => $listPlantillasEstudio, 'revisionEmpresa' => $revisionEmpresa, 'listPlantillasResumen' => $plantillasResumen, 'listPlantillasAptitud' => $plantillasAptitud, 'tree' => $tree, 'carpetas' => $carpetas, 'form2' => $form2->createView(), 'impagadaSn' => $impagadaSn));
    }

    public function addTecnico(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $tecnicoId = $_REQUEST['tecnicoId'];
        $empresaId = $_REQUEST['empresaId'];

        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
        $tecnico = $em->getRepository('App\Entity\Tecnico')->find($tecnicoId);

        $tecnicoEmpresa = new TecnicoEmpresa();
        $tecnicoEmpresa->setEmpresa($empresa);
        $tecnicoEmpresa->setTecnico($tecnico);

        $em->persist($tecnicoEmpresa);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        ////////////////////////////////////////////////////////////////////
        //Peticio 01/09/2023 David Gil
        $session = $request->getSession();
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $username = $usuario->getUsername();

        $mensaje = "El usuario " . $username . ", te ha asignado la siguiente empresa: " . $empresa->getEmpresa();


        $connection = $this->getDoctrine()->getManager()->getConnection();
        $query = "INSERT INTO notificaciones_internas (id, fecha, destinatario, remitente, mensaje, leido)
                          VALUES (:id, :fecha, :destinatario, :remitente, :mensaje, false)";
        $statement = $connection->prepare($query);
        $querySelectUpdated = "SELECT id FROM notificaciones_internas ORDER BY id DESC LIMIT 1;";
        $statementSelect = $connection->prepare($querySelectUpdated);
        $statementSelect->execute();
        $updatedRows = $statementSelect->fetchAll();
        foreach ($updatedRows as $row) {
            $ultimoId = $row['id'];
            // Hacer algo con el valor de $id
        }
        $fechaActual = new \DateTime();
        $fechaFormateada = $fechaActual->format('d-m-Y H:i:s'); // Formatear la fecha a formato 'YYYY-MM-DD HH:MM:SS'
        // Reemplazar saltos de línea por otro carácter (por ejemplo, <br>)
        $mensajeSinSaltos = str_replace("\n", " ", $mensaje);
        $params = [
            'id' => $ultimoId + 1,
            'fecha' => $fechaFormateada,
            'destinatario' => $tecnico->getAlias(),
            'remitente' => $username,
            'mensaje' => $mensajeSinSaltos,
        ];

        $statement->execute($params);
        ///////////////////////////////////////////////////////////////////////
        return new JsonResponse($data);
    }

    public function deleteTecnico(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $tecnicoEmpresaId = $_REQUEST['tecnicoEmpresaId'];
        $tecnicoEmpresa = $em->getRepository('App\Entity\TecnicoEmpresa')->find($tecnicoEmpresaId);

        $tecnicoEmpresa->setAnulado(true);
        $em->persist($tecnicoEmpresa);
        $em->flush();

        $data = array();
        array_push($data, "OK");
        return new JsonResponse($data);
    }

    public function recuperaTecnicos(Request $request)
    {
        $empresaId = $_REQUEST['empresaId'];

        $query = "select a.id, b.nombre from tecnico_empresa a inner join tecnico b on a.tecnico_id = b.id where a.empresa_id = $empresaId and a.anulado = false and b.anulado = false order by b.nombre asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $tecnicos = $stmt->fetchAll();

        return new JsonResponse(json_encode($tecnicos));
    }

    public function recuperaTecnico(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $tecnicoEmpresaId = $_REQUEST['tecnicoEmpresaId'];
        $tecnicoEmpresa = $em->getRepository('App\Entity\TecnicoEmpresa')->find($tecnicoEmpresaId);

        $data = array(
            'id' => $tecnicoEmpresa->getId(),
            'tecnico' => $tecnicoEmpresa->getTecnico()->getId(),
        );
        return new JsonResponse($data);
    }

    public function updateTecnico(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $tecnicoId = $_REQUEST['tecnicoId'];
        $tecnicoEmpresaId = $_REQUEST['tecnicoEmpresaId'];

        $tecnicoEmpresa = $em->getRepository('App\Entity\TecnicoEmpresa')->find($tecnicoEmpresaId);
        $tecnico = $em->getRepository('App\Entity\Tecnico')->find($tecnicoId);

        $tecnicoEmpresa->setTecnico($tecnico);
        $em->persist($tecnicoEmpresa);
        $em->flush();

        ////////////////////////////////////////////////////////////////////
        //Peticio 01/09/2023 David Gil
        $session = $request->getSession();
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);
        $username = $usuario->getUsername();

        $mensaje = "El usuario " . $username . ", te ha asignado la siguiente empresa: " . $tecnicoEmpresa->getEmpresa();


        $connection = $this->getDoctrine()->getManager()->getConnection();
        $query = "INSERT INTO notificaciones_internas (id, fecha, destinatario, remitente, mensaje, leido)
                          VALUES (:id, :fecha, :destinatario, :remitente, :mensaje, false)";
        $statement = $connection->prepare($query);
        $querySelectUpdated = "SELECT id FROM notificaciones_internas ORDER BY id DESC LIMIT 1;";
        $statementSelect = $connection->prepare($querySelectUpdated);
        $statementSelect->execute();
        $updatedRows = $statementSelect->fetchAll();
        foreach ($updatedRows as $row) {
            $ultimoId = $row['id'];
            // Hacer algo con el valor de $id
        }
        $fechaActual = new \DateTime();
        $fechaFormateada = $fechaActual->format('d-m-Y H:i:s'); // Formatear la fecha a formato 'YYYY-MM-DD HH:MM:SS'
        // Reemplazar saltos de línea por otro carácter (por ejemplo, <br>)
        $mensajeSinSaltos = str_replace("\n", " ", $mensaje);
        $params = [
            'id' => $ultimoId + 1,
            'fecha' => $fechaFormateada,
            'destinatario' => $tecnico->getAlias(),
            'remitente' => $username,
            'mensaje' => $mensajeSinSaltos,
        ];

        $statement->execute($params);
        ///////////////////////////////////////////////////////////////////////

        $data = array();
        array_push($data, "OK");
        return new JsonResponse($data);
    }

    public function addEmail(Request $request, UserPasswordEncoderInterface $passwordEncoder, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $email = $_REQUEST['email'];
        $funcionId = $_REQUEST['funcion'];
        $empresaId = $_REQUEST['empresaId'];

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

        $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
        $rutaPortal = $gdocConfig->getRutaPortal();

        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        if ($funcionId != "") {
            $funcionCorreo = $em->getRepository('App\Entity\FuncionCorreo')->find($funcionId);

            //Comprobamos que el correo no esté dado de alta para la empresa
            $correoEmpresaCheck = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false, 'correo' => $email, 'funcion' => $funcionCorreo));

            if (count($correoEmpresaCheck) > 0) {
                $data = array();
                array_push($data, "EXISTE");
                return new JsonResponse($data);
            }

            $correoEmpresa = new CorreoEmpresa();
            $correoEmpresa->setEmpresa($empresa);
            $correoEmpresa->setCorreo($email);
            $correoEmpresa->setFuncion($funcionCorreo);

            //Si la función es 4, debemos crear el usuario para la intranet
            if ($funcionId == 4) {
                $rolIntranet = $em->getRepository('App\Entity\PrivilegioRoles')->find(2);

                //Primero comprobamos que el correo no esté creado
                $userIntranet = $em->getRepository('App\Entity\User')->findOneBy(array('email' => $email));

                if (is_null($userIntranet)) {
                    $userIntranet = new \App\Entity\User();
                    $userIntranet->setRol($rolIntranet);
                    $userIntranet->setEmail($email);
                    $userIntranet->setEmailCanonical($email);
                    $userIntranet->setUsername($email);
                    $userIntranet->setUsername($email);
                    $userIntranet->setEnabled(true);
                    $userIntranet->setCredentialsExpired(true);
                    $userIntranet->setLocale('es');
                    //                    $userIntranet->setRoles('a:1:{i:0;s:10:"ROLE_ADMIN";}');

                    //Generamos la contraseña aleatoria
                    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $charactersLength = strlen($characters);
                    $randomString = '';
                    for ($i = 0; $i < 10; $i++) {
                        $randomString .= $characters[rand(0, $charactersLength - 1)];
                    }

                    $plainPassword = $randomString;
                    $encoded = $passwordEncoder->encodePassword($userIntranet, $plainPassword);
                    $userIntranet->setPassword($encoded);

                    $em->persist($userIntranet);
                    $em->flush();

                    $usuarioIntranet = new UserIntranet();
                    $usuarioIntranet->setUsuario($userIntranet);
                    $usuarioIntranet->setEmpresa($empresa);
                    $em->persist($usuarioIntranet);
                    $em->flush();

                    if (is_null($mail) || is_null($passwordMail) || is_null($puertoMail) || is_null($hostMail) || is_null($encriptacionMail) || is_null($userMail)) {
                        $traduccion = $translator->trans('TRANS_AVISO_NO_CONFIGURACION_CORREO');
                        $this->addFlash('danger', $traduccion);
                    } else {
                        $transport = new \Swift_SmtpTransport($hostMail, $puertoMail, $encriptacionMail);
                        $transport->setUsername($userMail);
                        $transport->setPassword($passwordMail);
                        $transport->setHost($hostMail);
                        $transport->setAuthMode('login');

                        $mailer = new \Swift_Mailer($transport);

                        //Enviamos el mail al cliente
                        $message = new \Swift_Message();
                        $message->setSubject("Alta portal empresa");
                        $message->setFrom($mail);
                        $message->setTo($email);
                        $message->setReplyTo($emailUser);
                        $message->setBody(
                            $this->renderView(
                                // templates/emails/registration.html.twig
                                'emails/send_intranet.html.twig',
                                ['email' => $email, 'password' => $plainPassword, 'ruta' => $rutaPortal]
                            ),
                            'text/html'
                        );
                        if (!is_null($emailUser) && $emailUser != "") {
                            $message->setBcc($emailUser);
                        }
                        $mailer->send($message);

                        //Insertamos el correo en el log
                        $this->insertLogMail($em, $usuario, "Alta portal empresa", $email, $message->getBody(), "Alta portal empresa");
                    }
                } else {
                    //Miramos si está dado de alta para alguna empresa
                    $usuarioIntranet = $em->getRepository('App\Entity\UserIntranet')->findOneBy(array('usuario' => $userIntranet, 'empresa' => $empresa));

                    if (is_null($usuarioIntranet)) {
                        $usuarioIntranet = $em->getRepository('App\Entity\UserIntranet')->findOneBy(array('usuario' => $userIntranet));
                        $usuarioIntranetEmpresa = $em->getRepository('App\Entity\UserIntranetEmpresa')->findOneBy(array('usuarioIntranet' => $usuarioIntranet, 'empresa' => $empresa));
                        if (is_null($usuarioIntranetEmpresa)) {
                            $usuarioIntranetEmpresa = new UserIntranetEmpresa();
                            $usuarioIntranetEmpresa->setUsuarioIntranet($usuarioIntranet);
                            $usuarioIntranetEmpresa->setEmpresa($empresa);
                            $em->persist($usuarioIntranetEmpresa);
                            $em->flush();

                            if (is_null($mail) || is_null($passwordMail) || is_null($puertoMail) || is_null($hostMail) || is_null($encriptacionMail) || is_null($userMail)) {
                                $traduccion = $translator->trans('TRANS_AVISO_NO_CONFIGURACION_CORREO');
                                $this->addFlash('danger', $traduccion);
                            } else {
                                $transport = new \Swift_SmtpTransport($hostMail, $puertoMail, $encriptacionMail);
                                $transport->setUsername($userMail);
                                $transport->setPassword($passwordMail);
                                $transport->setHost($hostMail);
                                $transport->setAuthMode('login');

                                $mailer = new \Swift_Mailer($transport);

                                //Enviamos el mail al cliente
                                $message = new \Swift_Message();
                                $message->setSubject("Alta portal empresa");
                                $message->setFrom($mail);
                                $message->setTo($email);
                                $message->setReplyTo($emailUser);
                                $message->setBody(
                                    $this->renderView(
                                        // templates/emails/registration.html.twig
                                        'emails/send_intranet_empresa.html.twig',
                                        ['email' => $email, 'empresa' => $empresa->getEmpresa(), 'ruta' => $rutaPortal]
                                    ),
                                    'text/html'
                                );
                                if (!is_null($emailUser) && $emailUser != "") {
                                    $message->setBcc($emailUser);
                                }
                                $mailer->send($message);

                                //Insertamos el correo en el log
                                $this->insertLogMail($em, $usuario, "Alta portal empresa", $email, $message->getBody(), "Alta portal empresa");
                            }
                        }
                    }
                }
            }

            $em->persist($correoEmpresa);
            $em->flush();
        }

        $data = array();
        array_push($data, "OK");
        return new JsonResponse($data);
    }

    public function recuperaEmails(Request $request)
    {
        $empresaId = $_REQUEST['empresaId'];

        $query = "select a.id, a.correo, b.descripcion as funcion from correo_empresa a left join funcion_correo b on a.funcion_id = b.id where a.empresa_id = $empresaId and a.anulado = false ";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $correos = $stmt->fetchAll();

        return new JsonResponse(json_encode($correos));
    }

    public function recuperaEmail(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $correoEmpresaId = $_REQUEST['correoEmpresaId'];
        $correoEmpresa = $em->getRepository('App\Entity\CorreoEmpresa')->find($correoEmpresaId);

        $funcion = null;
        if (!is_null($correoEmpresa->getFuncion())) {
            $funcion = $correoEmpresa->getFuncion()->getId();
        }

        $data = array(
            'id' => $correoEmpresa->getId(),
            'email' => $correoEmpresa->getCorreo(),
            'funcion' => $funcion,
        );
        return new JsonResponse($data);
    }

    public function updateEmail(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $email = $_REQUEST['email'];
        $funcionId = $_REQUEST['funcion'];
        $correoEmpresaId = $_REQUEST['correoEmpresaId'];

        $correoEmpresa = $em->getRepository('App\Entity\CorreoEmpresa')->find($correoEmpresaId);

        if ($funcionId != "") {
            $funcionCorreo = $em->getRepository('App\Entity\FuncionCorreo')->find($funcionId);
            $correoEmpresa->setFuncion($funcionCorreo);
        }

        $correoEmpresa->setCorreo($email);
        $em->persist($correoEmpresa);
        $em->flush();

        $data = array();
        array_push($data, "OK");
        return new JsonResponse($data);
    }

    public function deleteEmail(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $correoEmpresaId = $_REQUEST['correoEmpresaId'];
        $correoEmpresa = $em->getRepository('App\Entity\CorreoEmpresa')->find($correoEmpresaId);

        $correoEmpresa->setAnulado(true);
        $em->persist($correoEmpresa);
        $em->flush();

        $empresa = $correoEmpresa->getEmpresa();

        //Comprobamos si el correo es de intranet y lo deshabilitamos
        if (!is_null($correoEmpresa->getFuncion())) {
            if ($correoEmpresa->getFuncion()->getId() == 4) {
                $email = $correoEmpresa->getCorreo();
                $user = $em->getRepository('App\Entity\User')->findOneBy(array('email' => $email));
                if (!is_null($user)) {
                    $user->setEnabled(false);
                    $em->persist($user);
                    $em->flush();
                }

                $userIntranet = $em->getRepository('App\Entity\UserIntranet')->findBy(array('usuario' => $user, 'empresa' => $empresa));
                foreach ($userIntranet as $ui) {
                    $em->remove($ui);
                    $em->flush();
                }

                $userIntranet = $em->getRepository('App\Entity\UserIntranet')->findOneBy(array('usuario' => $user));
                $userIntranetEmpresa = $em->getRepository('App\Entity\UserIntranetEmpresa')->findBy(array('usuarioIntranet' => $userIntranet, 'empresa' => $empresa));
                foreach ($userIntranetEmpresa as $uie) {
                    $em->remove($uie);
                    $em->flush();
                }
            }
        }

        $data = array();
        array_push($data, "OK");
        return new JsonResponse($data);
    }

    public function addCnae(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $cnaeId = $_REQUEST['cnaeId'];
        $principal = $_REQUEST['principal'];
        $empresaId = $_REQUEST['empresaId'];

        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
        $cnae = $em->getRepository('App\Entity\Cnae')->find($cnaeId);

        $cnaeEmpresa = new CnaeEmpresa();
        $cnaeEmpresa->setEmpresa($empresa);
        $cnaeEmpresa->setCnae($cnae);

        if ($principal == 'true') {
            $cnaeEmpresa->setPrincipal(true);
        } else {
            $cnaeEmpresa->setPrincipal(false);
        }

        $em->persist($cnaeEmpresa);
        $em->flush();

        $data = array();
        array_push($data, "OK");
        return new JsonResponse($data);
    }

    public function deleteCnae(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cnaeEmpresaId = $_REQUEST['cnaeEmpresaId'];
        $cnaeEmpresa = $em->getRepository('App\Entity\CnaeEmpresa')->find($cnaeEmpresaId);

        $cnaeEmpresa->setAnulado(true);
        $em->persist($cnaeEmpresa);
        $em->flush();

        $data = array();
        array_push($data, "OK");
        return new JsonResponse($data);
    }

    public function updateCnae(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $cnaeId = $_REQUEST['cnaeId'];
        $principal = $_REQUEST['principal'];
        $cnaeEmpresaId = $_REQUEST['cnaeEmpresaId'];

        $cnaeEmpresa = $em->getRepository('App\Entity\CnaeEmpresa')->find($cnaeEmpresaId);
        $cnae = $em->getRepository('App\Entity\Cnae')->find($cnaeId);

        $cnaeEmpresa->setCnae($cnae);
        $cnaeEmpresa->setPrincipal($principal);

        if ($principal == 'true') {
            $cnaeEmpresa->setPrincipal(true);
        } else {
            $cnaeEmpresa->setPrincipal(false);
        }

        $em->persist($cnaeEmpresa);
        $em->flush();

        $data = array();
        array_push($data, "OK");
        return new JsonResponse($data);
    }

    public function recuperaCnaes(Request $request)
    {
        $empresaId = $_REQUEST['empresaId'];

        $query = "SELECT a.id, b.descripcion, b.cnae, a.principal from cnae_empresa a inner join cnae b on a.cnae_id = b.id where a.empresa_id = $empresaId and a.anulado = false and b.anulado = false order by b.descripcion asc ";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $correos = $stmt->fetchAll();

        return new JsonResponse(json_encode($correos));
    }

    public function recuperaCnae(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cnaeEmpresaId = $_REQUEST['cnaeEmpresaId'];
        $cnaeEmpresa = $em->getRepository('App\Entity\CnaeEmpresa')->find($cnaeEmpresaId);

        $data = array(
            'id' => $cnaeEmpresa->getId(),
            'cnaeId' => $cnaeEmpresa->getCnae()->getId(),
            'codigo' => $cnaeEmpresa->getCnae()->getCnae(),
            'principal' => $cnaeEmpresa->getPrincipal()
        );
        return new JsonResponse($data);
    }

    public function selectEmpresa(Request $request, $id, $tipo)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);

        $empresa = $em->getRepository('App\Entity\Empresa')->find($id);
        $session->set('empresa', $empresa);

        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $empresa, $usuario);
        $em->flush();

        switch ($tipo) {
            case 1:
                return $this->redirectToRoute('empresa_show');
                break;
            case 2:
                return $this->redirectToRoute('tecnico_empresa_show');
                break;
            case 3:
                return $this->redirectToRoute('medico_empresa_show');
                break;
        }

        return $this->redirectToRoute('empresa_show');
    }

    public function deselectEmpresa(Request $request, $tipo)
    {
        $session = $request->getSession();

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->find($user);

        $empresa = $session->get('empresa');
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "deselect", $empresa, $usuario);
        $em->flush();

        $session->set('empresa', null);

        switch ($tipo) {
            case 1:
                return $this->redirectToRoute('empresa_show');
                break;
            case 2:
                return $this->redirectToRoute('tecnico_empresa_show');
                break;
            case 3:
                return $this->redirectToRoute('medico_empresa_show');
                break;
        }
    }

    public function deleteTrabajadorEmpresa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $trabajadorEmpresaId = $_REQUEST['trabajadorEmpresaId'];
        $trabajadorEmpresa = $em->getRepository('App\Entity\TrabajadorEmpresa')->find($trabajadorEmpresaId);

        $trabajadorEmpresa->setAnulado(true);
        $em->persist($trabajadorEmpresa);
        $em->flush();

        //Anulamos el registro en la tabla de alta/baja
        $session = $request->getSession();
        $empresaId = $session->get('empresa');
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        $trabajador = $trabajadorEmpresa->getTrabajador();

        $altaBaja = $em->getRepository('App\Entity\TrabajadorAltaBaja')->findOneBy(array('empresa' => $empresa, 'trabajador' => $trabajador, 'anulado' => false, 'activo' => true));

        if (!is_null($altaBaja)) {
            $altaBaja->setAnulado(true);
            $em->persist($altaBaja);
            $em->flush();
        }

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function deleteCentroEmpresa(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $centroEmpresaId = $_REQUEST['centroEmpresaId'];
        $centroEmpresa = $em->getRepository('App\Entity\CentroTrabajoEmpresa')->find($centroEmpresaId);

        $centroEmpresa->setAnulado(true);
        $em->persist($centroEmpresa);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function recuperaCentroTrabajoEmpresa(Request $request)
    {
        $empresaId = $_REQUEST['empresaId'];

        $query = "SELECT a.id, b.direccion from centro_trabajo_empresa a inner join centro b on a.centro_id = b.id where a.empresa_id = $empresaId and a.anulado = false and b.anulado = false order by b.nombre asc ";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $centroTrabajoEmpresa = $stmt->fetchAll();

        return new JsonResponse(json_encode($centroTrabajoEmpresa));
    }

    public function addCentroTrabajoEmpresa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();

        $centroTrabajoId = $_REQUEST['centroTrabajoId'];
        $centro = $em->getRepository('App\Entity\Centro')->find($centroTrabajoId);

        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        $centroTrabajoEmpresa = new CentroTrabajoEmpresa();
        $centroTrabajoEmpresa->setEmpresa($empresa);
        $centroTrabajoEmpresa->setCentro($centro);
        $em->persist($centroTrabajoEmpresa);
        $em->flush();

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function addTrabajadorEmpresa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();

        $trabajadorId = $_REQUEST['trabajadorId'];
        $trabajador = $em->getRepository('App\Entity\Trabajador')->find($trabajadorId);

        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        $trabajadorEmpresa = $this->getDoctrine()->getRepository('App\Entity\TrabajadorEmpresa')->findOneBy(array('trabajador' => $trabajador, 'empresa' => $empresa, 'anulado' => false));
        if (is_null($trabajadorEmpresa)) {
            $trabajadorEmpresa = new TrabajadorEmpresa();
            $trabajadorEmpresa->setEmpresa($empresa);
            $trabajadorEmpresa->setTrabajador($trabajador);
            $trabajadorEmpresa->setAnulado(false);
            $em->persist($trabajadorEmpresa);
            $em->flush();
        }

        $trabajadorAltaBaja = $this->getDoctrine()->getRepository('App\Entity\TrabajadorAltaBaja')->findOneBy(array('trabajador' => $trabajador, 'empresa' => $empresa, 'anulado' => false));
        if (is_null($trabajadorAltaBaja)) {
            $trabajadorAltaBaja = new TrabajadorAltaBaja();
            $trabajadorAltaBaja->setEmpresa($empresa);
            $trabajadorAltaBaja->setTrabajador($trabajador);
            $trabajadorAltaBaja->setAnulado(false);
            $trabajadorAltaBaja->setActivo(true);
            $trabajadorAltaBaja->setFechaAlta(new \DateTime());
            $em->persist($trabajadorAltaBaja);
            $em->flush();
        }

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function compruebaCif(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = array();

        $cif = $_REQUEST['cif'];
        $empresaId = $_REQUEST['empresaId'];

        $query = "SELECT id from empresa where cif = '$cif' and anulado = false ";

        if ($empresaId != "") {
            $query .= " and id != $empresaId";
        }

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $empresaResult = $stmt->fetchAll();

        if (count($empresaResult) > 0) {
            array_push($data, "1");
        } else {
            array_push($data, "0");
        }

        return new JsonResponse($data);
    }

    public function addTarifa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $conceptoId = $_REQUEST['conceptoId'];
        $concepto = $em->getRepository('App\Entity\Concepto')->find($conceptoId);

        $iva = $_REQUEST['iva'];
        $importe = $_REQUEST['total'];

        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        //tipo == 1 PRL
        //tipo == 2 REVISIONES MEDICAS
        $tipo = $_REQUEST['tipo'];

        switch ($tipo) {
            case '1':
                $tarifaPrl = new TarifaPrl();
                $tarifaPrl->setEmpresa($empresa);
                $tarifaPrl->setConcepto($concepto);
                $tarifaPrl->setImporte($importe);
                $tarifaPrl->setIva($iva);
                $em->persist($tarifaPrl);
                $em->flush();
                break;
            case '2':
                $tarifaRevisionesMedicas = new TarifaRevisionMedica();
                $tarifaRevisionesMedicas->setEmpresa($empresa);
                $tarifaRevisionesMedicas->setConcepto($concepto);
                $tarifaRevisionesMedicas->setImporte($importe);
                $tarifaRevisionesMedicas->setIva($iva);
                $em->persist($tarifaRevisionesMedicas);
                $em->flush();
                break;
        }

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function editTarifa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $conceptoId = $_REQUEST['conceptoId'];
        $concepto = $em->getRepository('App\Entity\Concepto')->find($conceptoId);

        $iva = $_REQUEST['iva'];
        $importe = $_REQUEST['total'];

        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        //tipo == 1 PRL
        //tipo == 2 REVISIONES MEDICAS
        $tipo = $_REQUEST['tipo'];

        $tarifaId = $_REQUEST['tarifaId'];

        switch ($tipo) {
            case '1':
                $tarifaPrl = $em->getRepository('App\Entity\TarifaPrl')->find($tarifaId);
                $tarifaPrl->setEmpresa($empresa);
                $tarifaPrl->setConcepto($concepto);
                $tarifaPrl->setImporte($importe);
                $tarifaPrl->setIva($iva);
                $em->persist($tarifaPrl);
                $em->flush();
                break;
            case '2':
                $tarifaRevisionesMedicas = $em->getRepository('App\Entity\TarifaRevisionMedica')->find($tarifaId);
                $tarifaRevisionesMedicas->setEmpresa($empresa);
                $tarifaRevisionesMedicas->setConcepto($concepto);
                $tarifaRevisionesMedicas->setImporte($importe);
                $tarifaRevisionesMedicas->setIva($iva);
                $em->persist($tarifaRevisionesMedicas);
                $em->flush();
                break;
        }

        $traduccion = $translator->trans('TRANS_UPDATE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function recuperaTarifa(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $tarifaId = $_REQUEST['tarifaId'];

        //tipo == 1 PRL
        //tipo == 2 REVISIONES MEDICAS
        $tipo = $_REQUEST['tipo'];

        switch ($tipo) {
            case '1':
                $tarifa = $em->getRepository('App\Entity\TarifaPrl')->find($tarifaId);
                break;
            case '2':
                $tarifa = $em->getRepository('App\Entity\TarifaRevisionMedica')->find($tarifaId);
                break;
        }

        $data = array(
            'id' => $tarifa->getId(),
            'concepto' => $tarifa->getConcepto()->getId(),
            'importe' => $tarifa->getImporte(),
            'iva' => $tarifa->getIva()
        );

        return new JsonResponse($data);
    }

    public function deleteTarifa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $tarifaId = $_REQUEST['tarifaId'];
        //tipo == 1 PRL
        //tipo == 2 REVISIONES MEDICAS
        $tipo = $_REQUEST['tipo'];

        switch ($tipo) {
            case '1':
                $tarifaPrl = $em->getRepository('App\Entity\TarifaPrl')->find($tarifaId);
                $tarifaPrl->setAnulado(true);
                $em->persist($tarifaPrl);
                $em->flush();
                break;
            case '2':
                $tarifaRevisionesMedicas = $em->getRepository('App\Entity\TarifaRevisionMedica')->find($tarifaId);
                $tarifaRevisionesMedicas->setAnulado(true);
                $em->persist($tarifaRevisionesMedicas);
                $em->flush();
                break;
        }

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function addMaquinaEmpresa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getAddMaquinaEmpresaSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $maquinaEmpresa = new MaquinaEmpresa();

        //Comprobamos si hay una empresa seleccionada
        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
        $maquinaEmpresa->setEmpresa($empresa);

        //Buscamos los centros de la empresa
        $centros = $this->getDoctrine()->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        $arrayCentrosId = array();
        foreach ($centros as $c) {
            array_push($arrayCentrosId, $c->getCentro()->getId());
        }

        $form = $this->createForm(MaquinaEmpresaType::class, $maquinaEmpresa, array('centrosId' => $arrayCentrosId));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $maquinaEmpresa = $form->getData();
            $em = $this->getDoctrine()->getManager();

            $em->persist($maquinaEmpresa);
            $em->flush();

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('tecnico_empresa_maquina_update', array('id' => $maquinaEmpresa->getId()));
        }

        return $this->render('empresatecnico/maquinaEmpresa.html.twig', array('form' => $form->createView()));
    }

    public function updateMaquinaEmpresa(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditMaquinaEmpresaSn()) {
                return $this->redirectToRoute('error_403');
            }
        }

        $maquinaEmpresa = $this->getDoctrine()->getRepository('App\Entity\MaquinaEmpresa')->find($id);

        //Comprobamos si hay una empresa seleccionada
        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        //Buscamos los centros de la empresa
        $centros = $this->getDoctrine()->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        $arrayCentrosId = array();
        foreach ($centros as $c) {
            array_push($arrayCentrosId, $c->getCentro()->getId());
        }

        $form = $this->createForm(MaquinaEmpresaType::class, $maquinaEmpresa, array('centrosId' => $arrayCentrosId));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $maquinaEmpresa = $form->getData();
            $em = $this->getDoctrine()->getManager();

            $em->persist($maquinaEmpresa);
            $em->flush();

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('tecnico_empresa_maquina_update', array('id' => $maquinaEmpresa->getId()));
        }

        return $this->render('empresatecnico/maquinaEmpresa.html.twig', array('form' => $form->createView()));
    }

    public function deleteMaquinaEmpresa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $maquinaEmpresaId = $_REQUEST['maquinaEmpresaId'];

        $maquinaEmpresa = $em->getRepository('App\Entity\MaquinaEmpresa')->find($maquinaEmpresaId);
        $maquinaEmpresa->setAnulado(true);
        $em->persist($maquinaEmpresa);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function recogerFacturaEnviar(Request $request)
    {

        $facturas = $_REQUEST['facturas'];
        $session = $request->getSession();

        $session->set('facturasEnviar', $facturas);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function sendNotificacion(Request $request, TranslatorInterface $translator)
    {

        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getSendNotificacionSn()) {
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

        $notificacionesEnviar = $_REQUEST['notificaciones'];
        $notificacionesEnviarArray = explode(",", $notificacionesEnviar);

        $fileRepo = $em->getRepository('App\Entity\GdocFichero');
        $notificacionRepo = $em->getRepository('App\Entity\EmpresaNotificacion');

        $nombresNotificaciones = array();

        //Buscamos las notificaciones que se enviaran y las mostramos al usuario
        for ($i = 0; $i < count($notificacionesEnviarArray); $i++) {
            $notificacionId = $notificacionesEnviarArray[$i];
            $notificacion = $notificacionRepo->find($notificacionId);

            $fichero = $notificacion->getFichero();
            array_push($nombresNotificaciones, str_replace('docx', 'pdf', $fichero->getNombre()));
        }

        //Buscamos de la empresa el correo o correos para enviar facturas
        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        $destinatarios = null;
        $funcionEnviarCorreo = $em->getRepository('App\Entity\FuncionCorreo')->find(3);
        $correosEnviarNotificacion = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false, 'funcion' => $funcionEnviarCorreo));

        foreach ($correosEnviarNotificacion as $cen) {
            $destinatarios .= $cen->getCorreo() . ';';
        }

        $destinatarios = rtrim($destinatarios, ";");

        $form = $this->createForm(EnviarCorreoType::class, null, array('destinatario' => $destinatarios, 'cco' => $emailUser));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            //Obtenemos los datos de configuracion de la gestion documental
            $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
            $rutaCompleta = $gdocConfig->getRuta();
            $carpetaGenerada = $gdocConfig->getCarpetaNotificacion();
            $carpetaTemporal = $gdocConfig->getCarpetaTemporal();
            $passwordUsuario = $gdocConfig->getPasswordFicherosEncriptados();

            if (is_null($mail) || is_null($passwordMail) || is_null($puertoMail) || is_null($hostMail) || is_null($encriptacionMail) || is_null($userMail)) {
                $traduccion = $translator->trans('TRANS_AVISO_NO_CONFIGURACION_CORREO');
                $this->addFlash('danger', $traduccion);
                return $this->redirectToRoute('empresa_update', array('id' => $empresaId));
            }

            //Recogemos lo datos
            $para = $form["para"]->getData();
            $cc = $form["cc"]->getData();
            $cco = $form["cco"]->getData();
            $asunto = $form["asunto"]->getData();
            $mensaje = $form["mensaje"]->getData();

            $transport = new \Swift_SmtpTransport($hostMail, $puertoMail, $encriptacionMail);
            $transport->setUsername($userMail);
            $transport->setPassword($passwordMail);
            $transport->setHost($hostMail);
            $transport->setAuthMode('login');

            $mailer = new \Swift_Mailer($transport);

            $para = trim($para);

            $message = new \Swift_Message();
            $message->setSubject($asunto);
            $message->setFrom($mail);
            $message->setTo(explode(";", $para));
            $message->setReplyTo($emailUser);
            if (!is_null($cc) && $cc != "") {
                $message->setCc(explode(";", $cc));
            }
            if (!is_null($cco) && $cco != "") {
                $message->setBcc(explode(";", $cco));
            }
            $message->setBody($mensaje, 'text/plain');

            //Buscamos las notificaciones y adjuntamos el pdf al correo
            for ($i = 0; $i < count($notificacionesEnviarArray); $i++) {
                $notificacionId = $notificacionesEnviarArray[$i];
                $notificacion = $notificacionRepo->find($notificacionId);
                $fichero = $notificacion->getFichero();

                //Convertimos el word en pdf
                $fileDocx = $rutaCompleta . $carpetaGenerada . '/' . $fichero->getNombre();

                $filePdf = str_replace('docx', 'pdf', $fileDocx);
                $outdir = $rutaCompleta . $carpetaGenerada;

                $cmd = 'HOME=/tmp /usr/bin/soffice --headless --convert-to pdf:writer_pdf_Export "' . $fileDocx . '" --outdir "' . $outdir . '"';
                exec($cmd);

                //Encriptamos el documento
                $passwordOwner = $notificacion->getPasswordPdf();
                if (is_null($passwordOwner)) {
                    $notificacionId = $notificacion->getId();
                    $passwordOwner = hash('sha256', 'OpenticPrevencion' . $notificacionId);
                    $notificacion->setPasswordPdf($passwordOwner);
                }

                $nombrePlantillaPdf = str_replace('docx', 'pdf', $fichero->getNombre());
                $filePdfEncriptado = $rutaCompleta . $carpetaTemporal . '/' . $nombrePlantillaPdf;

                $this->encriptarPdf($filePdfEncriptado, $filePdf, $passwordOwner, $passwordUsuario);

                //Encriptamos el documento
                $passwordOwner = $notificacion->getPasswordPdf();
                if (is_null($passwordOwner)) {
                    $notificacionId = $notificacion->getId();
                    $passwordOwner = hash('sha256', 'OpenticPrevencion' . $notificacionId);
                    $notificacion->setPasswordPdf($passwordOwner);
                }

                $nombrePlantillaPdf = str_replace('docx', 'pdf', $fichero->getNombre());
                $filePdfEncriptado = $rutaCompleta . $carpetaTemporal . '/' . $nombrePlantillaPdf;

                $this->encriptarPdf($filePdfEncriptado, $filePdf, $passwordOwner, $passwordUsuario);

                //Lo adjuntamos al correo
                $message->attach(\Swift_Attachment::fromPath($filePdfEncriptado));

                //Marcamos la certificacion como enviada y añadimos la fecha de envio
                $notificacion->setEnviada(true);
                $notificacion->setFechaEnvio(new \DateTime());
                $em->persist($notificacion);
                $em->flush();

                //unlink($filePdf);
            }

            //Enviamos el correo
            $mailer->send($message);

            //Insertamos el correo en el log
            $this->insertLogMail($em, $usuario, $asunto, $para, $message->getBody(), "Envío notificación empresa");

            $traduccion = $translator->trans('TRANS_SEND_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('empresa_update', array('id' => $empresaId));
        }

        return $this->render('emails/send_email.html.twig', array('form' => $form->createView(), 'ficherosEnviar' => $nombresNotificaciones));
    }

    public function sendCertificacion(Request $request, TranslatorInterface $translator)
    {

        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getSendNotificacionSn()) {
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

        $certificacionesEnviar = $_REQUEST['certificaciones'];
        $certificacionesEnviarArray = explode(",", $certificacionesEnviar);

        $fileRepo = $em->getRepository('App\Entity\GdocFichero');
        $certificacionRepo = $em->getRepository('App\Entity\EmpresaCertificacion');

        $nombresCertificaciones = array();

        //Buscamos las notificaciones que se enviaran y las mostramos al usuario
        for ($i = 0; $i < count($certificacionesEnviarArray); $i++) {
            $certificacionId = $certificacionesEnviarArray[$i];
            $certificacion = $certificacionRepo->find($certificacionId);

            $fichero = $certificacion->getFichero();
            array_push($nombresCertificaciones, str_replace('docx', 'pdf', $fichero->getNombre()));
        }

        //Buscamos de la empresa el correo o correos para enviar facturas
        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        $destinatarios = null;
        $funcionEnviarCorreo = $em->getRepository('App\Entity\FuncionCorreo')->find(6);
        $correosEnviarCertificacion = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false, 'funcion' => $funcionEnviarCorreo));

        foreach ($correosEnviarCertificacion as $cec) {
            $destinatarios .= $cec->getCorreo() . ';';
        }

        $destinatarios = rtrim($destinatarios, ";");

        $form = $this->createForm(EnviarCorreoType::class, null, array('destinatario' => $destinatarios, "cco" => $emailUser));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            //Obtenemos los datos de configuracion de la gestion documental
            $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
            $rutaCompleta = $gdocConfig->getRuta();
            $carpetaGenerada = $gdocConfig->getCarpetaCertificacion();
            $carpetaTemporal = $gdocConfig->getCarpetaTemporal();
            $passwordUsuario = $gdocConfig->getPasswordFicherosEncriptados();

            if (is_null($mail) || is_null($passwordMail) || is_null($puertoMail) || is_null($hostMail) || is_null($encriptacionMail) || is_null($userMail)) {
                $traduccion = $translator->trans('TRANS_AVISO_NO_CONFIGURACION_CORREO');
                $this->addFlash('danger', $traduccion);
                return $this->redirectToRoute('empresa_update', array('id' => $empresaId));
            }

            //Recogemos lo datos
            $para = $form["para"]->getData();
            $cc = $form["cc"]->getData();
            $cco = $form["cco"]->getData();
            $asunto = $form["asunto"]->getData();
            $mensaje = $form["mensaje"]->getData();

            $transport = new \Swift_SmtpTransport($hostMail, $puertoMail, $encriptacionMail);
            $transport->setUsername($userMail);
            $transport->setPassword($passwordMail);
            $transport->setHost($hostMail);
            $transport->setAuthMode('login');

            $mailer = new \Swift_Mailer($transport);

            $para = trim($para);

            $message = new \Swift_Message();
            $message->setSubject($asunto);
            $message->setFrom($mail);
            $message->setTo(explode(";", $para));
            $message->setReplyTo($emailUser);
            if (!is_null($cc) && $cc != "") {
                $message->setCc(explode(";", $cc));
            }
            if (!is_null($cco) && $cco != "") {
                $message->setBcc(explode(";", $cco));
            }
            $message->setBody($mensaje, 'text/plain');

            //Buscamos las notificaciones y adjuntamos el pdf al correo
            for ($i = 0; $i < count($certificacionesEnviarArray); $i++) {
                $certificacionId = $certificacionesEnviarArray[$i];
                $certificacion = $certificacionRepo->find($certificacionId);
                $fichero = $certificacion->getFichero();

                //Convertimos el word en pdf
                $fileDocx = $rutaCompleta . $carpetaGenerada . '/' . $fichero->getNombre();

                $filePdf = str_replace('docx', 'pdf', $fileDocx);
                $outdir = $rutaCompleta . $carpetaGenerada;

                $cmd = 'HOME=/tmp /usr/bin/soffice --headless --convert-to pdf:writer_pdf_Export "' . $fileDocx . '" --outdir "' . $outdir . '"';
                exec($cmd);

                //Encriptamos el documento
                $passwordOwner = $certificacion->getPasswordPdf();
                if (is_null($passwordOwner)) {
                    $notificacionId = $certificacion->getId();
                    $passwordOwner = hash('sha256', 'OpenticPrevencion' . $notificacionId);
                    $certificacion->setPasswordPdf($passwordOwner);
                }

                $nombrePlantillaPdf = str_replace('docx', 'pdf', $fichero->getNombre());
                $filePdfEncriptado = $rutaCompleta . $carpetaTemporal . '/' . $nombrePlantillaPdf;

                $this->encriptarPdf($filePdfEncriptado, $filePdf, $passwordOwner, $passwordUsuario);

                //Lo adjuntamos al correo
                $message->attach(\Swift_Attachment::fromPath($filePdfEncriptado));

                //Marcamos la certificación como enviada y su fecha de envío
                $certificacion->setEnviada(true);
                $certificacion->setFechaEnvio(new \DateTime());
                $em->persist($certificacion);
                $em->flush();

                //unlink($filePdf);
            }

            //Enviamos el correo
            $mailer->send($message);

            //Insertamos el correo en el log
            $this->insertLogMail($em, $usuario, $asunto, $para, $message->getBody(), "Envío certificación empresa");

            $traduccion = $translator->trans('TRANS_SEND_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('empresa_update', array('id' => $empresaId));
        }

        return $this->render('emails/send_email.html.twig', array('form' => $form->createView(), 'ficherosEnviar' => $nombresCertificaciones));
    }

    public function sendModelo347(Request $request, TranslatorInterface $translator)
    {

        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getSendModelo347Sn()) {
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

        $modelo347Enviar = $_REQUEST['modelo347'];
        $modelo347EnviarArray = explode(",", $modelo347Enviar);

        $fileRepo = $em->getRepository('App\Entity\GdocFichero');
        $modelo347Repo = $em->getRepository('App\Entity\EmpresaModelo347');

        $nombresModelo347 = array();

        //Buscamos las notificaciones que se enviaran y las mostramos al usuario
        for ($i = 0; $i < count($modelo347EnviarArray); $i++) {
            $modelo347Id = $modelo347EnviarArray[$i];
            $modelo347 = $modelo347Repo->find($modelo347Id);

            $fichero = $modelo347->getFichero();
            array_push($nombresModelo347, str_replace('docx', 'pdf', $fichero->getNombre()));
        }

        //Buscamos de la empresa el correo o correos para enviar facturas
        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        $destinatarios = null;
        $funcionEnviarCorreo = $em->getRepository('App\Entity\FuncionCorreo')->find(2);
        $correosEnviarModelo347 = $em->getRepository('App\Entity\CorreoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false, 'funcion' => $funcionEnviarCorreo));

        foreach ($correosEnviarModelo347 as $cec) {
            $destinatarios .= $cec->getCorreo() . ';';
        }

        $destinatarios = rtrim($destinatarios, ";");

        $form = $this->createForm(EnviarCorreoType::class, null, array('destinatario' => $destinatarios, "cco" => $emailUser));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            //Obtenemos los datos de configuracion de la gestion documental
            $gdocConfig = $em->getRepository('App\Entity\GdocConfig')->find(1);
            $rutaCompleta = $gdocConfig->getRuta();
            $carpetaGenerada = $gdocConfig->getCarpetaModelo347();
            $carpetaTemporal = $gdocConfig->getCarpetaTemporal();
            $passwordUsuario = $gdocConfig->getPasswordFicherosEncriptados();

            if (is_null($mail) || is_null($passwordMail) || is_null($puertoMail) || is_null($hostMail) || is_null($encriptacionMail) || is_null($userMail)) {
                $traduccion = $translator->trans('TRANS_AVISO_NO_CONFIGURACION_CORREO');
                $this->addFlash('danger', $traduccion);
                return $this->redirectToRoute('empresa_update', array('id' => $empresaId));
            }

            //Recogemos lo datos
            $para = $form["para"]->getData();
            $cc = $form["cc"]->getData();
            $cco = $form["cco"]->getData();
            $asunto = $form["asunto"]->getData();
            $mensaje = $form["mensaje"]->getData();

            $transport = new \Swift_SmtpTransport($hostMail, $puertoMail, $encriptacionMail);
            $transport->setUsername($userMail);
            $transport->setPassword($passwordMail);
            $transport->setHost($hostMail);
            $transport->setAuthMode('login');

            $mailer = new \Swift_Mailer($transport);

            $para = trim($para);

            $message = new \Swift_Message();
            $message->setSubject($asunto);
            $message->setFrom($mail);
            $message->setTo(explode(";", $para));
            $message->setReplyTo($emailUser);
            if (!is_null($cc) && $cc != "") {
                $message->setCc(explode(";", $cc));
            }
            if (!is_null($cco) && $cco != "") {
                $message->setBcc(explode(";", $cco));
            }
            $message->setBody($mensaje, 'text/plain');

            //Buscamos las notificaciones y adjuntamos el pdf al correo
            for ($i = 0; $i < count($modelo347EnviarArray); $i++) {
                $modelo347Id = $modelo347EnviarArray[$i];
                $modelo347 = $modelo347Repo->find($modelo347Id);
                $fichero = $modelo347->getFichero();

                //Convertimos el word en pdf
                $fileDocx = $rutaCompleta . $carpetaGenerada . '/' . $fichero->getNombre();

                $filePdf = str_replace('docx', 'pdf', $fileDocx);
                $outdir = $rutaCompleta . $carpetaGenerada;

                $cmd = 'HOME=/tmp /usr/bin/soffice --headless --convert-to pdf:writer_pdf_Export "' . $fileDocx . '" --outdir "' . $outdir . '"';
                exec($cmd);

                //Encriptamos el documento
                $passwordOwner = $modelo347->getPasswordPdf();
                if (is_null($passwordOwner)) {
                    $notificacionId = $modelo347->getId();
                    $passwordOwner = hash('sha256', 'OpenticPrevencion' . $notificacionId);
                    $modelo347->setPasswordPdf($passwordOwner);
                }

                $nombrePlantillaPdf = str_replace('docx', 'pdf', $fichero->getNombre());
                $filePdfEncriptado = $rutaCompleta . $carpetaTemporal . '/' . $nombrePlantillaPdf;

                $this->encriptarPdf($filePdfEncriptado, $filePdf, $passwordOwner, $passwordUsuario);

                //Lo adjuntamos al correo
                $message->attach(\Swift_Attachment::fromPath($filePdfEncriptado));

                //Marcamos la certificación como enviada y su fecha de envío
                $modelo347->setEnviada(true);
                $modelo347->setFechaEnvio(new \DateTime());
                $em->persist($modelo347);
                $em->flush();

                //unlink($filePdf);
            }

            //Enviamos el correo
            $mailer->send($message);

            //Insertamos el correo en el log
            $this->insertLogMail($em, $usuario, $asunto, $para, $message->getBody(), "Envío Modelo 347 empresa");

            $traduccion = $translator->trans('TRANS_SEND_OK');
            $this->addFlash('success', $traduccion);

            return $this->redirectToRoute('empresa_update', array('id' => $empresaId));
        }

        return $this->render('emails/send_email.html.twig', array('form' => $form->createView(), 'ficherosEnviar' => $nombresModelo347));
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

    function createBalanceEconomico($em, $empresaId)
    {
        $query = "select a.id, to_char(a.fecha, 'DD/MM/YYYY') as fecha, a.fecha as fechatime, to_char(a.fecha, 'YYYYMMDDHHmm') as fechatimestamp, case when b.serie = 'A' then concat('Factura ',b.serie,'',a.num_fac)
            when b.serie = 'B' then concat('Factura ',b.serie,'',a.num_fac,' (Abono de la factura ',b.serie,'',a.num_fac,')')
            end as concepto, null as debe, null as haber, b.serie, a.factura_asociada_id, true as facturasn, a.factura_rectificativa_id from facturacion a
            inner join serie_factura b on a.serie_id  = b.id 
            where a.empresa_id = $empresaId
            and a.anulado = false
            and a.id not in (select factura_rectificativa_id from facturacion where anulado = false and factura_rectificativa_id is not null)
            union all
            select id, to_char(fecha, 'DD/MM/YYYY') as fecha, fecha as fechatime, to_char(fecha, 'YYYYMMDDHHmm') as fechatimestamp, concepto, case when tipo = 1 then importe else null end as debe, case when tipo = 2 then importe else null end as haber, null, facturacion_id, false as facturasn, null from balance_economico_entrada 
            where anulado = false 
            and empresa_id = $empresaId
            and facturacion_id is null
            order by fecha desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $balanceFacturasEmpresa = $stmt->fetchAll();

        $balanceEmpresa = array();
        foreach ($balanceFacturasEmpresa as $bfe) {
            $facturacionId = $bfe['id'];
            $facturaSn = $bfe['facturasn'];
            $facturaRectificativaId = $bfe['factura_rectificativa_id'];

            //Si es abono y tiene factura asociada pasamos a la siguiente
            if ($bfe['serie'] == 'B' && !is_null($bfe['factura_asociada_id'])) {
                continue;
            }

            $item = array();
            $item['fechatimestamp'] = $bfe['fechatimestamp'];
            $item['fechatime'] = $bfe['fechatime'];
            $item['fecha'] = $bfe['fecha'];
            $item['vencimiento'] = null;
            $item['pagos'] = null;
            $item['abonos'] = null;
            $item['entradas'] = null;
            $item['rectificativa'] = null;

            $item['concepto'] = $bfe['concepto'];
            $item['haber'] = $bfe['haber'];

            $debe = 0;
            if ($facturaSn) {
                $query = "select * from facturacion_lineas_pagos where facturacion_id = $facturacionId and anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $facturaPagos = $stmt->fetchAll();

                if (count($facturaPagos) > 0) {
                    foreach ($facturaPagos as $fp) {
                        $debe += $fp['importe_total'];
                    }
                } else {
                    $query = "select * from facturacion_lineas_conceptos where facturacion_id = $facturacionId and anulado = false";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $facturaConceptos = $stmt->fetchAll();
                    foreach ($facturaConceptos as $fc) {
                        $debe += ($fc['importe_unidad'] + $fc['iva']) * $fc['unidades'];
                    }
                }

                $item['debe'] = $debe;

                //Buscamos el vencimiento de la factura
                $query = "select a.id, to_char(a.fecha, 'DD/MM/YYYY') as fecha, a.concepto, a.importe, a.confirmado from facturacion_vencimiento a where a.factura_asociada_id = $facturacionId and a.anulado = false order by a.fecha asc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $vencimientoFactura = $stmt->fetchAll();

                $vencimientoArray = array();
                foreach ($vencimientoFactura as $vf) {
                    $vencimiento = array();
                    $vencimiento['fecha'] = $vf['fecha'];
                    $vencimiento['concepto'] = $vf['concepto'];
                    $vencimiento['confirmado'] = $vf['confirmado'];
                    $vencimiento['haber'] = $vf['importe'];
                    $vencimiento['id'] = $vf['id'];
                    array_push($vencimientoArray, $vencimiento);
                }

                if (count($vencimientoArray) > 0) {
                    $item['vencimiento'] = $vencimientoArray;
                }

                //Buscamos los pagos de la factura
                $query = "select to_char(a.vencimiento, 'DD/MM/YYYY') as fecha, a.concepto, a.girado, a.es_factura, a.remesa_id, a.id, a.importe from giro_bancario a where a.facturacion_id = $facturacionId and a.anulado = false order by a.vencimiento asc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $pagosFactura = $stmt->fetchAll();

                $pagoArray = array();
                foreach ($pagosFactura as $pf) {
                    $devolucionArray = array();

                    $giroId = $pf['id'];
                    $pago = array();

                    if (!is_null($pf['remesa_id'])) {
                        $remesaId = $pf['remesa_id'];
                        $query = "select to_char(a.fecha, 'DD/MM/YYYY') as fecha from remesa a where a.id = $remesaId";
                        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                        $stmt->execute();
                        $remesaGiro = $stmt->fetchAll();
                        if (count($remesaGiro) > 0) {
                            $pago['fecha'] = $remesaGiro[0]['fecha'];
                        } else {
                            $pago['fecha'] = $pf['fecha'];
                        }
                    } else {
                        $pago['fecha'] = $pf['fecha'];
                    }

                    $pago['concepto'] = $pf['concepto'];
                    $pago['girado'] = $pf['girado'];
                    $pago['es_factura'] = $pf['es_factura'];
                    $pago['remesado'] = $pf['remesa_id'];

                    $pago['haber'] = $pf['importe'];
                    $pago['id'] = $giroId;

                    //Buscamos si el giro tiene alguna devolucion
                    $query = "select a.id, to_char(a.fecha , 'DD/MM/YYYY') as fecha, a.concepto, a.recibo_generado, a.importe from giro_bancario_devolucion a where a.facturacion_id = $facturacionId and a.anulado = false and a.giro_bancario_id = $giroId order by a.fecha asc";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $devolucionPago = $stmt->fetchAll();

                    $pago['devolucion'] = null;
                    foreach ($devolucionPago as $dp) {
                        $devolucion = array();
                        $devolucion['id'] = $dp['id'];
                        $devolucion['fecha'] = $dp['fecha'];
                        $devolucion['concepto'] = $dp['concepto'];
                        if (!is_null($dp['importe'])) {
                            $devolucion['debe'] = $dp['importe'];
                        } else {
                            $devolucion['debe'] = $pf['importe'];
                        }

                        $devolucion['recibo_generado'] = $dp['recibo_generado'];

                        array_push($devolucionArray, $devolucion);
                    }

                    if (count($devolucionArray) > 0) {
                        $pago['devolucion'] = $devolucionArray;
                    }

                    array_push($pagoArray, $pago);
                }

                if (count($pagoArray) > 0) {
                    $item['pagos'] = $pagoArray;
                }

                //Buscamos los abonos de la factura
                $query = "select a.id, to_char(a.fecha, 'DD/MM/YYYY') as fecha, sum((b.importe_unidad + b.iva) * b.unidades) as importe, a.num_fac from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id where a.factura_asociada_id = $facturacionId and a.anulado = false and a.serie_id = 6 and b.anulado = false group by a.id order by a.fecha asc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $abonosFactura = $stmt->fetchAll();

                $abonoArray = array();
                foreach ($abonosFactura as $af) {
                    $abono = array();
                    $abono['fecha'] = $af['fecha'];
                    $abono['concepto'] = "Factura B" . $af['num_fac'] . ' (Abono de la factura ' . $bfe['concepto'] . ')';
                    $abono['debe'] = $af['importe'];
                    $abono['id'] = $af['id'];
                    array_push($abonoArray, $abono);
                }

                if (count($abonoArray) > 0) {
                    $item['abonos'] = $abonoArray;
                }

                //Buscamos las entradas de la factura
                $query = "select id, to_char(fecha, 'DD/MM/YYYY') as fecha, importe, tipo, concepto, pago_confirmado from balance_economico_entrada where empresa_id = $empresaId and facturacion_id = $facturacionId and anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $entradasFactura = $stmt->fetchAll();

                $entradaArray = array();
                foreach ($entradasFactura as $ea) {
                    $entrada = array();
                    $entrada['fecha'] = $ea['fecha'];
                    $entrada['concepto'] = $ea['concepto'];
                    if ($ea['tipo'] == 1) {
                        $entrada['debe'] = $ea['importe'];
                    } else {
                        $entrada['haber'] = $ea['importe'];
                    }
                    $entrada['id'] = $ea['id'];
                    $entrada['confirmado'] = $ea['pago_confirmado'];
                    array_push($entradaArray, $entrada);
                }

                if (count($entradaArray) > 0) {
                    $item['entradas'] = $entradaArray;
                }

                //Buscamos si tiene alguna factura rectificativa
                if (!is_null($facturaRectificativaId)) {
                    $query = "select a.id, to_char(a.fecha, 'DD/MM/YYYY') as fecha, sum((b.importe_unidad + b.iva) * b.unidades) as importe, concat(c.serie,a.num_fac) as num_fac from facturacion a inner join facturacion_lineas_conceptos b on a.id = b.facturacion_id inner join serie_factura c on a.serie_id = c.id where a.id = $facturaRectificativaId and a.anulado = false and b.anulado = false group by a.id, c.serie order by a.fecha asc";
                    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                    $stmt->execute();
                    $rectificativaFactura = $stmt->fetchAll();

                    $rectificativaArray = array();
                    foreach ($rectificativaFactura as $rf) {
                        $rectificativa = array();
                        $rectificativa['fecha'] = $rf['fecha'];
                        $rectificativa['concepto'] = "Factura " . $rf['num_fac'] . ' (Rectificativa de la factura ' . $bfe['concepto'] . ')';
                        $rectificativa['debe'] = $rf['importe'];
                        $rectificativa['id'] = $rf['id'];
                        array_push($rectificativaArray, $rectificativa);
                    }

                    if (count($rectificativaArray) > 0) {
                        $item['rectificativa'] = $rectificativaArray;
                    }
                }
            } else {
                $item['debe'] = $bfe['debe'];
                $item['id'] = $bfe['id'];
            }

            array_push($balanceEmpresa, $item);
        }

        $keys = array_column($balanceEmpresa, 'fechatime');
        array_multisort($keys, SORT_DESC, $balanceEmpresa);

        return $balanceEmpresa;
    }

    public function buscaLocalidadProvincia(Request $request)
    {

        $codigoPostal = $_REQUEST['codigopostal'];

        $localidad = null;
        $localidadId = null;
        $provincia = null;
        $provinciaId = null;

        //Buscamos si existe el codigo postal
        $query = "select poblacion, provincia from codigo_postal_poblacion where codigo_postal like '%" . $codigoPostal . "%'";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $resultCodigoPostal = $stmt->fetchAll();

        if (count($resultCodigoPostal) > 0) {
            $poblacion = $resultCodigoPostal[0]['poblacion'];
            $poblacion = str_replace('\'', "''", $poblacion);
            if (!is_null($poblacion)) {
                //Buscamos la poblacion y la provincia en serpa
                $query = "select a.descripcion as localidad, a.id as localidadid, b.descripcion as provincia, b.id as provinciaid from municipio_serpa a left join provincia_serpa b on a.provincia_id = b.id where translate(lower(a.descripcion),'àáéèíóú','aaeeiou') = translate(lower('" . $poblacion . "'),'áàéèíóú','aaeeiou');";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $resultPoblacionProvincia = $stmt->fetchAll();

                if (count($resultPoblacionProvincia) > 0) {
                    $localidad = $resultPoblacionProvincia[0]['localidad'];
                    $localidadId = $resultPoblacionProvincia[0]['localidadid'];
                    $provincia = $resultPoblacionProvincia[0]['provincia'];
                    $provinciaId = $resultPoblacionProvincia[0]['provinciaid'];
                }
            }
        }

        $data = array(
            'localidad_id' => $localidadId,
            'localidad' => $localidad,
            'provincia_id' => $provinciaId,
            'provincia' => $provincia
        );

        return new JsonResponse($data);
    }

    function encriptarPdf($filePdfEncriptado, $filePdf, $passwordOwner, $passwordUsuario)
    {
        // Copiamos el PDF sin encriptar
        copy($filePdf, $filePdfEncriptado);
    }

    function importarTrabajadoresITA($em, $fichero, $empresa)
    {

        //Damos de baja a los trabajadores actuales de la empresa
        $trabajadoresEmpresa = $em->getRepository('App\Entity\TrabajadorEmpresa')->findBy(array('empresa' => $empresa));

        foreach ($trabajadoresEmpresa as $te) {
            $te->setAnulado(true);
            $em->persist($te);
            $em->flush();
        }

        $trabajadoresBaja = $em->getRepository('App\Entity\TrabajadorAltaBaja')->findBy(array('empresa' => $empresa, 'anulado' => false));
        foreach ($trabajadoresBaja as $tb) {
            $tb->setFechaBaja(new \DateTime());
            $tb->setActivo(false);
            $em->persist($tb);
            $em->flush();
        }

        // Cargamos el fichero a importar
        $spreadsheet = IOFactory::load($fichero);
        $totalSheets = $spreadsheet->getSheetCount();

        // Procesamos todas las hojas del Excel
        for ($sheetIndex = 0; $sheetIndex < $totalSheets; $sheetIndex++) {
            $sheet = $spreadsheet->getSheet($sheetIndex);
            $start = 16;
            $contador = 1;
            $letra = 'M';
            $nombreTrabajador = "";

            while (!str_contains($nombreTrabajador, 'FIN DE INFORME') && !str_contains($nombreTrabajador, '•')) {
                // Seleccionamos del Excel donde comienza la lista de trabajadores
                $sheetDataNombre = $sheet->getCell('A' . $start);
                $sheetDataDNI = $sheet->getCell($letra . $start);

                // Manejo de cambios en las letras de columnas por orden
                if ($contador == 1 || is_null($sheetDataDNI->getValue())) {
                    $sheetDataDNI = $sheet->getCell('L' . $start);
                    $letra = 'L';
                }

                if (is_null($sheetDataDNI->getValue())) {
                    $sheetDataDNI = $sheet->getCell('N' . $start);
                    $letra = 'N';
                }

                $nombreTrabajador = "";
                $dniTrabajador = "";

                // Recogemos los datos del nombre del trabajador
                if (!is_null($sheetDataNombre->getValue())) {
                    if ($sheetDataNombre->getValue() instanceof RichText) {
                        foreach ($sheetDataNombre->getValue()->getRichTextElements() as $object) {
                            $nombreTrabajador .= $object->getText();
                        }
                    } else {
                        // Si es un valor simple, lo tomamos directamente
                        $nombreTrabajador = $sheetDataNombre->getValue();
                    }
                }

                // Recogemos los datos para el DNI del trabajador
                if (!is_null($sheetDataDNI->getValue())) {
                    if ($sheetDataDNI->getValue() instanceof RichText) {
                        foreach ($sheetDataDNI->getValue()->getRichTextElements() as $object) {
                            $dniTrabajador .= $object->getText();
                        }
                    } else {
                        // Si es un valor simple, lo tomamos directamente
                        $dniTrabajador = $sheetDataDNI->getValue();
                    }

                    // Modificar este substr() según cómo estés manejando el DNI
                    $dniTrabajador = substr($dniTrabajador, 3);
                }

                // Condiciones para validar los datos antes de insertarlos en la base de datos
                if ($nombreTrabajador != "" && !str_contains($nombreTrabajador, 'FIN DE INFORME') && !str_contains($nombreTrabajador, '•') && !str_contains($nombreTrabajador, 'Relevo') && !is_null($dniTrabajador) && $dniTrabajador != "") {
                    $nombreTrabajador = trim($nombreTrabajador);
                    $dniTrabajador = trim($dniTrabajador);

                    // Comprobamos que el DNI no está dado de alta
                    $trabajador = $em->getRepository('App\Entity\Trabajador')->findOneBy(array('dni' => $dniTrabajador));

                    // Asignamos los datos del trabajador a la entidad
                    $trabajadorEmpresa = new TrabajadorEmpresa();
                    $trabajadorEmpresa->setEmpresa($empresa);
                    $trabajadorEmpresa->setAnulado(false);

                    // Si el DNI no existe, creamos el trabajador
                    if (is_null($trabajador)) {
                        $fechaActual = new \DateTime();

                        $trabajador = new Trabajador();
                        $trabajador->setNombre($nombreTrabajador);
                        $trabajador->setDni($dniTrabajador);
                        $trabajador->setObservaciones('Creado automáticamente desde la importación de trabajadores ' . $fechaActual->format('d-m-Y H:i:s'));
                        $em->persist($trabajador);
                        $em->flush();
                    }

                    // Asignamos la relación trabajador-empresa
                    $trabajadorEmpresa->setTrabajador($trabajador);
                    $em->persist($trabajadorEmpresa);
                    $em->flush();

                    // Creamos el alta
                    $alta = new TrabajadorAltaBaja();
                    $alta->setTrabajador($trabajador);
                    $alta->setEmpresa($empresa);
                    $alta->setFechaAlta(new \DateTime());
                    $alta->setActivo(true);
                    $alta->setAnulado(false);

                    $em->persist($alta);
                    $em->flush();
                }

                // Romper el bucle si encontramos los valores específicos
                if (str_contains($nombreTrabajador, 'FIN DE INFORME') || str_contains($nombreTrabajador, '•')) {
                    break;
                }

                // Ajusta el siguiente valor de $start según las condiciones
                if (str_contains($nombreTrabajador, 'Relevo') || str_contains($nombreTrabajador, '•')) {
                    if ($start > 192) {
                        $start = 236;
                    } elseif ($start > 148) {
                        $start = 192;
                    } elseif ($start > 104) {
                        $start = 148;
                    } elseif ($start > 60) {
                        $start = 104;
                    } else {
                        $start = 60;
                    }
                } else {
                    $start++;
                    $contador++;
                }
            }
        }
    }

    public function buscarDatosAutonomo(Request $request)
    {

        $empresaId = $_REQUEST['empresaId'];

        $query = "select nombre_representante, dni_representante, cargo_representante from empresa where id = $empresaId";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $resultEmpresa = $stmt->fetchAll();

        $data = array(
            'nombre_representante' => $resultEmpresa[0]['nombre_representante'],
            'dni_representante' => $resultEmpresa[0]['dni_representante'],
            'cargo_representante' => $resultEmpresa[0]['cargo_representante'],
        );

        return new JsonResponse($data);
    }

    function buscarFacturasEmpresa($empresaId)
    {
        $query = "select a.id, to_char(a.fecha, 'DD/MM/YYYY') as fecha, to_char(a.fecha, 'YYYYMMDDHHmm') as fechatimestamp, concat(b.serie,a.num_fac) as factura, a.fichero_id, a.enviada, c.descripcion as formapago, b.serie, to_char(a.fecha_envio, 'DD/MM/YYYY') as fechaenvio, a.factura_rectificativa_id from facturacion a 
            inner join serie_factura b on a.serie_id = b.id
            left join forma_pago c on a.forma_pago_id = c.id
            where a.empresa_id = $empresaId
            and a.anulado = false
            group by a.id, b.serie, a.num_fac, a.enviada, c.descripcion
            order by a.fecha desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $facturasEmpresa = $stmt->fetchAll();

        $facturas = array();
        foreach ($facturasEmpresa as $fe) {
            $item = array();

            $item['id'] = $fe['id'];
            $item['fecha'] = $fe['fecha'];
            $item['fechatimestamp'] = $fe['fechatimestamp'];
            $item['factura'] = $fe['factura'];
            $item['fichero_id'] = $fe['fichero_id'];
            $item['enviada'] = $fe['enviada'];
            $item['formapago'] = $fe['formapago'];
            $item['serie'] = $fe['serie'];

            $facturaId = $fe['id'];

            //Buscamos si tiene una factura rectificativa
            $facturaRectificativaId = $fe['factura_rectificativa_id'];
            if (!is_null($facturaRectificativaId)) {
                $query = "select concat(b.serie,a.num_fac) as factura from facturacion a 
                inner join serie_factura b on a.serie_id = b.id
                where a.empresa_id = $empresaId
                and a.id = $facturaRectificativaId
                and a.anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $facturaRectificativa = $stmt->fetchAll();
                if (count($facturaRectificativa) > 0) {
                    $item['facturaRectificativa'] = $facturaRectificativa[0]['factura'];
                } else {
                    $item['facturaRectificativa'] = '';
                }
            } else {
                $item['facturaRectificativa'] = '';
            }

            $query = "select * from facturacion_lineas_pagos where facturacion_id = $facturaId and anulado = false";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $facturaPagos = $stmt->fetchAll();

            $revisionSn = false;
            $renovacionSn = false;

            $importe = 0;
            if (count($facturaPagos) > 0) {
                foreach ($facturaPagos as $fp) {
                    $importe += $fp['importe_sin_iva'];
                    if (str_contains(strtolower($fp['concepto']), 'rm')) {
                        $revisionSn = true;
                    }
                    if (str_contains(strtolower($fp['concepto']), 'del contrato') || str_contains(strtolower($fp['concepto']), 'prl')) {
                        $renovacionSn = true;
                    }
                }
            } else {
                $query = "select * from facturacion_lineas_conceptos where facturacion_id = $facturaId and anulado = false";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $facturaConceptos = $stmt->fetchAll();

                foreach ($facturaConceptos as $fc) {
                    $importe += $fc['importe_unidad'] * $fc['unidades'];
                    if (str_contains(strtolower($fc['concepto']), 'rm')) {
                        $revisionSn = true;
                    }

                    if (str_contains(strtolower($fc['concepto']), 'prl')) {
                        $renovacionSn = true;
                    }
                }
            }


            $item['revision'] = $revisionSn;
            $item['renovacion'] = $renovacionSn;
            $item['importe'] = $importe;
            $item['fechaenvio'] = $fe['fechaenvio'];;

            array_push($facturas, $item);
        }

        return $facturas;
    }

    function buscarContratosEmpresa($empresaId)
    {
        $query = "select a.id, a.contrato, to_char(a.fechainicio, 'DD/MM/YYYY') as fechainicio, to_char(a.fechainicio, 'YYYYMMDDHHmm') as fechainiciotimestamp, 
            to_char(c.fechavencimiento, 'DD/MM/YYYY') as fechavencimiento, 
            to_char(c.fechavencimiento, 'YYYYMMDDHHmm') as fechavencimientotimestamp, b.empresa, c.renovado, a.fichero_id, c.id as renovacionid, d.descripcion as tipo,
            a.enviado, a.cancelado, d.id as tipoid, a.facturado, to_char(a.fecha_envio, 'DD/MM/YYYY') as fechaenvio from contrato a 
            inner join empresa b on a.empresa_id = b.id
            inner join renovacion c on a.id = c.contrato_id
            left join contrato_modalidad d on a.contrato_modalidad_id = d.id
            where a.empresa_id = $empresaId
            and c.anulado = false
            and b.id = $empresaId
            group by a.id, c.fechavencimiento, b.empresa, c.renovado, c.id, d.id, a.facturado
            order by a.fechainicio desc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $resultContratoEmpresa = $stmt->fetchAll();

        $contratos = array();
        foreach ($resultContratoEmpresa as $rce) {
            $item = array();
            $item['id'] = $rce['id'];
            $item['contrato'] = $rce['contrato'];
            $item['fechainicio'] = $rce['fechainicio'];
            $item['fechainiciotimestamp'] = $rce['fechainiciotimestamp'];
            $item['fechavencimiento'] = $rce['fechavencimiento'];
            $item['fechavencimientotimestamp'] = $rce['fechavencimientotimestamp'];
            $item['empresa'] = $rce['empresa'];
            $item['renovado'] = $rce['renovado'];
            $item['fichero_id'] = $rce['fichero_id'];
            $item['renovacionid'] = $rce['renovacionid'];
            $item['tipo'] = $rce['tipo'];
            $item['enviado'] = $rce['enviado'];
            $item['cancelado'] = $rce['cancelado'];
            $item['tipoid'] = $rce['tipoid'];
            $item['fechaenvio'] = $rce['fechaenvio'];

            $contratoId = $rce['id'];

            $query = "select sum(importe_sin_iva) as importe from contrato_pago where anulado = false and contrato_id = $contratoId";
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
            $stmt->execute();
            $resultImporteContrato = $stmt->fetchAll();

            if (count($resultImporteContrato) > 0) {
                $item['importe'] = $resultImporteContrato[0]['importe'];
            } else {
                $item['importe'] = $rce['importe'];
            }

            $item['facturado'] = $rce['facturado'];

            array_push($contratos, $item);
        }

        return $contratos;
    }

    function buscaTrabajadoresEmpresaTecnico($empresaId, $trabajadoresEmpresa)
    {
        $trabajadoresEmpresaArray = array();
        foreach ($trabajadoresEmpresa as $te) {
            $item = array();
            $item['id'] = $te->getId();
            if (!is_null($te->getTrabajador())) {
                $trabajadorId = $te->getTrabajador()->getId();

                $item['trabajadorid'] = $trabajadorId;
                $item['trabajador'] = $te->getTrabajador()->getNombre();
                $item['dni'] = $te->getTrabajador()->getDni();

                //Buscamos los puestos de trabajo del trabajador
                $query = "select string_agg(b.descripcion::text, ' , '::text) as puestos from puesto_trabajo_trabajador a
                inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                where a.anulado = false
                and b.anulado = false 
                and a.trabajador_id = $trabajadorId
                and b.empresa_id = $empresaId";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $puestoTrabajoTrabajador = $stmt->fetchAll();

                if (count($puestoTrabajoTrabajador) > 0) {
                    $item['puesto'] = $puestoTrabajoTrabajador[0]['puestos'];
                } else {
                    $item['puesto'] = "";
                }

                $query = "select a.id from puesto_trabajo_trabajador a
                inner join puesto_trabajo_centro b on a.puesto_trabajo_id = b.id
                where a.anulado = false
                and b.anulado = false 
                and a.trabajador_id = $trabajadorId
                and b.empresa_id = $empresaId
                and a.fecha_baja is null
                order by b.descripcion asc";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
                $stmt->execute();
                $puestoTrabajoTrabajadorEstado = $stmt->fetchAll();

                if (count($puestoTrabajoTrabajadorEstado) > 0) {
                    $item['activo'] = true;
                } else {
                    if (count($puestoTrabajoTrabajador) > 0) {
                        $item['activo'] = false;
                    } else {
                        $item['activo'] = true;
                    }
                }

                array_push($trabajadoresEmpresaArray, $item);
            }
        }
        return $trabajadoresEmpresaArray;
    }

    function buscarTreePuestosTrabajoEmpresa($empresaId, $empresa)
    {
        //Recuperamos las zonas de trabajo
        $query = "select id, descripcion from zona_trabajo where empresa_id = $empresaId and anulado = false";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $zonasTrabajo = $stmt->fetchAll();

        $dataPuestos = array();

        $row = array();
        $row['id'] = $empresaId . ' ' . $empresa->getCodigo();
        $row['parent'] = "#";
        $row['text'] = $empresa->getEmpresa();
        $row['icon'] = "icon-home";
        array_push($dataPuestos, $row);

        foreach ($zonasTrabajo as $zonaTrabajo) {
            $row = array();
            $row['id'] = $zonaTrabajo['id'] . "";
            $row['parent'] = $empresaId . ' ' . $empresa->getCodigo();
            $row['text'] = $zonaTrabajo['descripcion'];
            $row['icon'] = "icon-pin";
            array_push($dataPuestos, $row);
        }

        //Recuperamos los puestos de trabajo
        $query = "select id, descripcion, zona_trabajo_id from puesto_trabajo_centro where empresa_id = $empresaId and anulado = false";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $puestosTrabajo = $stmt->fetchAll();

        foreach ($puestosTrabajo as $puestoTrabajo) {
            $row = array();
            $row['id'] = "puestoTrabajoId" . $puestoTrabajo['id'];
            if (is_null($puestoTrabajo['zona_trabajo_id'])) {
                $row['parent'] = $empresaId . ' ' . $empresa->getCodigo();
            } else {
                $row['parent'] = $puestoTrabajo['zona_trabajo_id'] . "";
            }
            $row['text'] = $puestoTrabajo['descripcion'];
            $row['icon'] = "icon-man";
            array_push($dataPuestos, $row);
        }

        return \json_encode($dataPuestos);
    }
    //Peticio 01/09/2023
    function buscarGestionDocumentalEmpresa2($empresaId)
    {
        $query = "select * from gdoc_empresa_carpeta where (padre_id = 25 or id = 25) and anulado = false and (empresa_id = $empresaId or compartida = true)";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $carpetas = $stmt->fetchAll();

        $tree = array();
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
            if ($carpeta['compartida']) {
                $row['modificar'] = false;
            } else {
                $row['modificar'] = true;
            }
            array_push($tree, $row);
        }

        //Recuperamos los ficheros de las carpetas
        $query = "select * from gdoc_empresa where anulado = false and empresa_id = $empresaId";
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

            array_push($tree, $row);
        }

        return \json_encode($tree);
    }
    function buscarGestionDocumentalEmpresa($empresaId)
    {
        $query = "select * from gdoc_empresa_carpeta where (padre_id != 25 or id != 25) and anulado = false and (empresa_id = $empresaId or compartida = true)";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $carpetas = $stmt->fetchAll();

        $tree = array();
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
            if ($carpeta['compartida']) {
                $row['modificar'] = false;
            } else {
                $row['modificar'] = true;
            }
            array_push($tree, $row);
        }

        //Recuperamos los ficheros de las carpetas
        $query = "select * from gdoc_empresa where anulado = false and empresa_id = $empresaId";
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

            array_push($tree, $row);
        }

        return \json_encode($tree);
    }
    public function buscaCentrosEmpresa(Request $request)
    {

        $empresaId = $_REQUEST['empresaId'];

        $query = "select c.id, c.nombre from centro_trabajo_empresa a
        inner join empresa b on a.empresa_id = b.id
        inner join centro c on a.centro_id = c.id
        where a.anulado = false 
        and b.anulado = false
        and c.anulado = false
        and a.empresa_id = $empresaId
        and b.id = $empresaId
        order by c.nombre asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $centrosEmpresa = $stmt->fetchAll();

        return new JsonResponse(json_encode($centrosEmpresa));
    }

    public function buscaPuestoTrabajoEmpresa(Request $request)
    {
        $empresaId = $_REQUEST['empresaId'];
        $query = "select id, descripcion from puesto_trabajo_centro
        where anulado = false
        and empresa_id = $empresaId
        order by descripcion asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $puestoTrabajo = $stmt->fetchAll();

        $data = array(
            'puestos' => json_encode($puestoTrabajo),
        );

        return new JsonResponse($data);
    }

    public function addPuestoTrabajoTrabajadorMultiple(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $trabajadoresId = $_REQUEST['trabajadoresId'];
        $puestoTrabajoId = $_REQUEST['puestoTrabajoId'];

        $puestoTrabajo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoCentro')->find($puestoTrabajoId);

        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        foreach ($trabajadoresId as $t) {
            $trabajador = $this->getDoctrine()->getRepository('App\Entity\Trabajador')->find($t);

            $puestoTrabajoTrabajador = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoTrabajador')->findBy(array('anulado' => false, 'empresa' => $empresa, 'puestoTrabajo' => $puestoTrabajo, 'trabajador' => $trabajador));
            if (count($puestoTrabajoTrabajador) == 0) {
                $puestoTrabajoTrabajador = new PuestoTrabajoTrabajador();
                $puestoTrabajoTrabajador->setEmpresa($empresa);
                $puestoTrabajoTrabajador->setAnulado(false);
                $puestoTrabajoTrabajador->setFechaAlta(new \DateTime());
                $puestoTrabajoTrabajador->setTrabajador($trabajador);
                $puestoTrabajoTrabajador->setPuestoTrabajo($puestoTrabajo);
                $em->persist($puestoTrabajoTrabajador);
                $em->flush();
            }
        }

        $data = array();
        array_push($data, 'OK');

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success', $traduccion);

        return new JsonResponse($data);
    }
}
