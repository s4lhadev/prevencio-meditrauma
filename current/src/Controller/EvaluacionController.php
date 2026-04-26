<?php

namespace App\Controller;

use App\Entity\AccionPreventivaEmpresaRiesgoCausa;
use App\Entity\AccionPreventivaTrabajadorRiesgoCausa;
use App\Entity\BuscadorQueriesVariable;
use App\Entity\EpiPreventivaEmpresa;
use App\Entity\EpiPreventivaTrabajador;
use App\Entity\Evaluacion;
use App\Entity\EvaluacionCentroTrabajo;
use App\Entity\GrupoNormativaEvaluacion;
use App\Entity\MaquinaEmpresa;
use App\Entity\MaquinaEmpresaTrabajador;
use App\Entity\PersonaEvaluacion;
use App\Entity\PlanificacionRiesgoCausa;
use App\Entity\PuestoTrabajoContaminante;
use App\Entity\PuestoTrabajoEvaluacion;
use App\Entity\PuestoTrabajoTrabajador;
use App\Entity\RiesgoCausaEvaluacion;
use App\Entity\RiesgoCausaImg;
use App\Entity\TareaTecnico;
use App\Entity\TecnicoEvaluacion;
use App\Entity\LogEnvioMail;
use App\Entity\VisitaEvaluacion;
use App\Entity\ZonaTrabajoEvaluacion;
use App\Entity\ZonaTrabajo;
use App\Entity\PreventivaEmpresa;
use App\Entity\PreventivaTrabajador;
use App\Form\AccionPreventivaEmpresaType;
use App\Form\AccionPreventivaTrabajadorType;
use App\Form\EvaluacionType;
use App\Form\PuestoTrabajoContaminanteType;
use App\Form\PuestoTrabajoEvaluarType;
use App\Form\PuestoTrabajoTrabajadorType;
use App\Form\RiesgoCausaType;
use App\Form\ZonaTrabajoEvaluarType;
use App\Logger;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Contracts\Translation\TranslatorInterface;

class EvaluacionController extends AbstractController
{
    public function preCrearEvaluacion(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $empresaId = $_REQUEST['empresaId'];

        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
        $session = $request->getSession();
        $session->set('empresa', $empresa);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function createEvaluacion(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getAddEvaluacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);

        // Comprobamos si hay una empresa seleccionada
        $empresa = $session->get('empresa');
        $empresaId = $empresa->getId();
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);

        $tipo = null;
        if (isset($_REQUEST['tipo'])) {
            $tipo = $_REQUEST['tipo'];

            if ($tipo == 2) {
                $evaluacionId = null;
                $em->beginTransaction();
                try {
                    $evaluacionId = $this->copiarEvaluacion($em, $empresa, $translator);
                } catch (\Exception $e) {
                    $em->rollBack();
                    throw $e;
                }
                if ($evaluacionId > 0) {
                    $em->commit();
                    return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_update', array('id' => $evaluacionId));
                }
            }
        }
        $evaluacion = new Evaluacion();

        $tipoEvaluacion = $em->getRepository('App\Entity\TipoEvaluacion')->find(1);
        $evaluacion->setTipoEvaluacion($tipoEvaluacion);
        $evaluacion->setEmpresa($empresa);
        $evaluacion->setFechaInicio(new \DateTime());
        $evaluacion->setTipo($tipo);

        // Buscamos los centros de la empresa
        $centros = $this->getDoctrine()->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        $arrayCentrosId = array();
        foreach ($centros as $c) {
            array_push($arrayCentrosId, $c->getCentro()->getId());
        }
        $form = $this->createForm(EvaluacionType::class, $evaluacion, array('empresaObj' => $empresa, 'empresaId' => $empresaId, 'centroObj' => null, 'centrosId' => $arrayCentrosId));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $evaluacion = $form->getData();
            $em->persist($evaluacion);
            $em->flush();

            // Buscamos los centros seleccionados
            $centrosSeleccionados = $form["centro"]->getData();

            if (!is_null($centrosSeleccionados)) {
                foreach ($centrosSeleccionados as $c) {
                    $evaluacionCentro = new EvaluacionCentroTrabajo();
                    $evaluacionCentro->setEvaluacion($evaluacion);
                    $evaluacionCentro->setCentro($c);
                    $em->persist($evaluacionCentro);
                    $em->flush();
                }
            }
            if (is_null($evaluacion->getFechaProxima())) {
                $fechaInicioString = $evaluacion->getFechaInicio()->format('Y-m-d');
                $fechaProxima = date("Y-m-d", strtotime($fechaInicioString . "+1 year"));
                $evaluacion->setFechaProxima(new \DateTime($fechaProxima));
            }
            // Si la fecha de la proxima evaluación esta informada creamos la tarea en la agenda del tecnico
            if (!is_null($evaluacion->getFechaProxima())) {

                $estadoTarea = $em->getRepository('App\Entity\EstadoTareaTecnico')->find(1);

                // Buscamos si ya existe el registro
                $tareaTecnico = $em->getRepository('App\Entity\TareaTecnico')->findOneBy(array('evaluacion' => $evaluacion, 'anulado' => false));

                if (is_null($tareaTecnico)) {
                    $tareaTecnico = new TareaTecnico();
                    $tareaTecnico->setUsuario($usuario);
                    $tareaTecnico->setAnulado(false);
                    $tareaTecnico->setEstado($estadoTarea);
                    if (!is_null($evaluacion->getEmpresa()->getEmpresa())) {
                        $tareaTecnico->setDescripcion("Próxima evaluación: " . $evaluacion->getEmpresa()->getEmpresa());
                    }
                    $tareaTecnico->setEvaluacion($evaluacion);
                }
                $fechaProximaString = $evaluacion->getFechaProxima()->format('Y-m-d');
                $fechaProximaString = $fechaProximaString . ' 08:00:00';

                $tareaTecnico->setFechainicio(new \DateTime($fechaProximaString));

                // Generamos la duracion y fecha fin de la tarea
                $horas = '01';
                $minutos = '00';
                $tiempo_dti = new \DateInterval('PT' . $horas . 'H' . $minutos . 'M');
                $tiempo = new \DateTime('2000-01-01 ' . $horas . ':' . $minutos . ':00');
                $tareaTecnico->setDuracion($tiempo);

                $fechaInicioString = $tareaTecnico->getFechainicio()->format('Y-m-d H:i:s');
                $fechaFin = new \DateTime($fechaInicioString);
                $fechaFin->add($tiempo_dti);
                $tareaTecnico->setFechafin($fechaFin);

                $em->persist($tareaTecnico);
                $em->flush();
            }
            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_update', array('id' => $evaluacion->getId()));
        }
        return $this->render('evaluacion/edit.html.twig', array('form' => $form->createView(), 'tecnicos' => null, 'listTecnicos' => null, 'personas' => null, 'puestosEvaluar' => null, 'listPuestoTrabajo' => null, 'zonasEvaluar' => null, 'listZonaTrabajo' => null, 'normativas' => null, 'listGrupoNormativa' => null, 'visitas' => null));
    }

    public function showEmpresaCentro(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEvaluacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $query = "select a.id, a.finalizada, (select numero from (select id, empresa_id, row_number() over(order by fecha_inicio asc) as numero from evaluacion
                where anulado = false 
                and empresa_id = a.empresa_id
                order by fecha_inicio asc) consulta
                where id = a.id) as numero, b.empresa, string_agg(d.nombre::text, ' / '::text) as centros, string_agg(h.nombre::text || ' ' ||h.apellido1::text || ' ' ||h.apellido2::text, ' / '::text) as tecnicos,
                to_char(a.fecha_inicio, 'DD/MM/YYYY') as fechainicio, to_char(a.fecha_inicio, 'YYYYMMDDHHmm') as fechainiciotimestamp, to_char(a.fecha_fin, 'DD/MM/YYYY') as fechafin, to_char(a.fecha_fin, 'YYYYMMDDHHmm') as fechafintimestamp, e.descripcion as tipo,
                f.descripcion as metodologia, a.fichero_id from evaluacion a 
                inner join empresa b on a.empresa_id = b.id
                left join evaluacion_centro_trabajo c on a.id = c.evaluacion_id
                left join centro d on c.centro_id = d.id
                left join tipo_evaluacion e on a.tipo_evaluacion_id = e.id
                left join metodologia_evaluacion f on a.metodologia_id = f.id
                left join tecnico_evaluacion g on a.id = g.evaluacion_id
                left join usuario_tecnico h on g.tecnico_id = h.id 
                where a.anulado = false ";

        if ($id == 30) {
            $query .= " and b.id in (select a.id from empresa a inner join tecnico_empresa b on a.id = b.empresa_id inner join tecnico c on b.tecnico_id = c.id where a.anulado = false and c.anulado = false and b.tecnico_id = 37) and h.id = 4615 ";
        }
        if ($id == 31) {
            $query .= " and b.id in (select a.id from empresa a inner join tecnico_empresa b on a.id = b.empresa_id inner join tecnico c on b.tecnico_id = c.id where a.anulado = false and c.anulado = false and b.tecnico_id = 10) and h.id = 4616 ";
        }
        $query .= " group by a.id, a.finalizada, b.empresa, a.fecha_inicio, a.fecha_fin, e.descripcion, f.descripcion, a.fichero_id order by a.fecha_inicio desc";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $evaluaciones = $stmt->fetchAll();

        // Buscamos las plantillas de la carpeta evaluacion
        $carpetaEvaluaciones = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(10);
        $plantillas = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaEvaluaciones, 'anulado' => false));

        $object = array("json" => $username, "entidad" => "evaluaciones", "id" => $id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "show", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('evaluacion/show.html.twig', array('evaluaciones' => $evaluaciones, 'listPlantillas' => $plantillas));
    }

    public function updateEvaluacion(Request $request, $id, TranslatorInterface $translator)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditEvaluacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);

        $evaluacion = $this->getDoctrine()->getRepository('App\Entity\Evaluacion')->find($id);
        $session->set('empresa', $evaluacion->getEmpresa());
        $session->set('evaluacionId', $evaluacion->getId());

        // Buscamos los centros de la empresa
        $centros = $this->getDoctrine()->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('empresa' => $evaluacion->getEmpresa(), 'anulado' => false));

        $arrayCentrosId = array();
        foreach ($centros as $c) {
            array_push($arrayCentrosId, $c->getCentro()->getId());
        }
        // Peticio 28/07/2028
        foreach ($centros as $c) {

            $query = "SELECT COUNT(*) AS numero FROM evaluacion_centro_trabajo ect where ect.evaluacion_id  = :empresaId and ect.centro_id = :centroId";
            $entityManager = $this->getDoctrine()->getManager();
            $stmt = $entityManager->getConnection()->prepare($query);
            $stmt->bindValue('empresaId', $evaluacion->getId());
            $stmt->bindValue('centroId', $c->getCentro()->getId());
            $stmt->execute();
            $countEvalu = $stmt->fetchAll();
            $conteoRegsitros = null;
            foreach ($countEvalu as $pec) {
                $conteoRegsitros = $pec['numero'];
            }
            // Asegurémonos de que el resultado tiene al menos un registro
            if ($conteoRegsitros > 0) {
            } else {
                $em = $this->getDoctrine()->getManager();
                $evaluCentoTrabajo = new EvaluacionCentroTrabajo();
                $evaluCentoTrabajo->setEvaluacion($evaluacion);
                $evaluCentoTrabajo->setCentro($c->getCentro());
                $em->persist($evaluCentoTrabajo);
                $em->flush();
            }
        }
        // Peticio 01/09/2028
        foreach ($centros as $c) {
            $aux = $c->getCentro()->getNombre();
            $query = "SELECT COUNT(*) AS numero FROM zona_trabajo zt where zt.empresa_id = :empresaId and zt.descripcion = :centroId";
            $entityManager = $this->getDoctrine()->getManager();
            $stmt = $entityManager->getConnection()->prepare($query);
            $stmt->bindValue('empresaId', $evaluacion->getEmpresa()->getId());
            $stmt->bindValue('centroId', $aux);
            $stmt->execute();
            $countEvalu = $stmt->fetchAll();
            $conteoRegsitros = null;
            foreach ($countEvalu as $pec) {
                $conteoRegsitros = $pec['numero'];
            }
            // Asegurémonos de que el resultado tiene al menos un registro
            if ($conteoRegsitros > 0) {
            } else {
                $em = $this->getDoctrine()->getManager();
                $newZonaTrabajo = new ZonaTrabajo();
                $newZonaTrabajo->setAnulado(false);
                $newZonaTrabajo->setEmpresa($evaluacion->getEmpresa());
                $newZonaTrabajo->setDescripcion($aux);
                $em->persist($newZonaTrabajo);
                $em->flush();
            }
        }
        $empresa = $evaluacion->getEmpresa();
        $centrosEvaluacion = null;
        if ($empresa->getCentroTrabajoDeslocalizado()) {
        } else {
            // Buscamos los centros de la evaluación
            $centrosEvaluacion = $this->getDoctrine()->getRepository('App\Entity\EvaluacionCentroTrabajo')->findBy(array('evaluacion' => $evaluacion));
        }
        // AQUI SEGUIR DEMA
        $arrayCentrosEvaluacion = array();
        if ($centrosEvaluacion != null) {
            foreach ($centrosEvaluacion as $c) {
                array_push($arrayCentrosEvaluacion, $c->getCentro());
            }
        }
        // Buscamos los tecnicos de la evaluacion
        $tecnicos = $this->getDoctrine()->getRepository('App\Entity\TecnicoEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));
        // Buscamos la lista de tecnicos
        $listTecnicos = $this->getDoctrine()->getRepository('App\Entity\UsuarioTecnico')->findBy(array('anulado' => false, 'tecnico' => true));
        // Buscamos los acompañantes de la evaluacion
        $personas = $this->getDoctrine()->getRepository('App\Entity\PersonaEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));
        // Buscamos los puestos de trabajo del centro
        $puestosTrabajoCentro = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoCentro')->findBy(array('empresa' => $evaluacion->getEmpresa(), 'anulado' => false));
        // Buscamos las zonas del centro
        $zonasTrabajoCentro = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajo')->findBy(array('empresa' => $evaluacion->getEmpresa(), 'anulado' => false));
        // Recuperamos los puestos de trabajo seleccionados
        $puestoTrabajoEvaluacionRepo = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoEvaluacion');
        $puestosSeleccionados = $puestoTrabajoEvaluacionRepo->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));
        // Recuperamos las zonas de trabajo seleccionadas
        $zonaTrabajoEvaluacionRepo = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajoEvaluacion');
        $zonaTrabajoSeleccionadas = $zonaTrabajoEvaluacionRepo->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));
        // Recuperamos loas normativas de la evaluacion
        $normativasRepo = $this->getDoctrine()->getRepository('App\Entity\GrupoNormativaEvaluacion');
        $normativas = $normativasRepo->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));
        // Buscamos la lista de normativas
        $listGrupoNormativa = $this->getDoctrine()->getRepository('App\Entity\GrupoNormativa')->findBy(array('anulado' => false));
        // Buscamos las visitas de la evaluacion
        $visitas = $this->getDoctrine()->getRepository('App\Entity\VisitaEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false), array('dtVisita' => 'DESC'));

        $form = $this->createForm(EvaluacionType::class, $evaluacion, array('empresaObj' => $evaluacion->getEmpresa(), 'empresaId' => $evaluacion->getEmpresa()->getId(), 'centroObj' => $arrayCentrosEvaluacion, 'centrosId' => $arrayCentrosId));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $evaluacion = $form->getData();
            $em->persist($evaluacion);
            $em->flush();

            // Buscamos los centros de la evaluacion y los eliminamos
            $centrosEvaluacion = $this->getDoctrine()->getRepository('App\Entity\EvaluacionCentroTrabajo')->findBy(array('evaluacion' => $evaluacion));
            foreach ($centrosEvaluacion as $c) {
                $em->remove($c);
                $em->flush();
            }
            // Buscamos los centros seleccionados
            $centrosSeleccionados = $form["centro"]->getData();
            if (!is_null($centrosSeleccionados)) {
                foreach ($centrosSeleccionados as $c) {
                    $evaluacionCentro = new EvaluacionCentroTrabajo();
                    $evaluacionCentro->setEvaluacion($evaluacion);
                    $evaluacionCentro->setCentro($c);
                    $em->persist($evaluacionCentro);
                    $em->flush();
                }
            }
            // Si la fecha de la proxima evaluación esta informada creamos la tarea en la agenda del tecnico
            if (!is_null($evaluacion->getFechaProxima())) {

                $estadoTarea = $em->getRepository('App\Entity\EstadoTareaTecnico')->find(1);

                // Buscamos si ya existe el registro
                $tareaTecnico = $em->getRepository('App\Entity\TareaTecnico')->findOneBy(array('evaluacion' => $evaluacion, 'anulado' => false));

                if (is_null($tareaTecnico)) {
                    $tareaTecnico = new TareaTecnico();
                    $tareaTecnico->setUsuario($usuario);
                    $tareaTecnico->setAnulado(false);
                    $tareaTecnico->setEstado($estadoTarea);
                    $tareaTecnico->setDescripcion("Próxima evaluación: " . $evaluacion->getEmpresa()->getEmpresa());
                    $tareaTecnico->setEvaluacion($evaluacion);
                }
                $fechaProximaString = $evaluacion->getFechaProxima()->format('Y-m-d');
                $fechaProximaString = $fechaProximaString . ' 08:00:00';

                $tareaTecnico->setFechainicio(new \DateTime($fechaProximaString));

                // Generamos la duracion y fecha fin de la tarea
                $horas = '01';
                $minutos = '00';
                $tiempo_dti = new \DateInterval('PT' . $horas . 'H' . $minutos . 'M');
                $tiempo = new \DateTime('2000-01-01 ' . $horas . ':' . $minutos . ':00');
                $tareaTecnico->setDuracion($tiempo);

                $fechaInicioString = $tareaTecnico->getFechainicio()->format('Y-m-d H:i:s');
                $fechaFin = new \DateTime($fechaInicioString);
                $fechaFin->add($tiempo_dti);
                $tareaTecnico->setFechafin($fechaFin);

                $em->persist($tareaTecnico);
                $em->flush();

                sleep(2);

                /////////////////////////////////////////////////////////////
                // Canvi fecha inicio recalcular fechaprevista Petició 01/09/2023 David Gil

                $queryFechas = "select e.fecha_inicio , prc.id as idplanifi,rce.*from planificacion_riesgo_causa prc inner join riesgo_causa_evaluacion rce on prc.riesgo_causa_id = rce.id inner join evaluacion e on e.id = rce.evaluacion_id where rce.evaluacion_id = '$id' and rce.valor_riesgo_id is not null";

                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($queryFechas);
                $stmt->execute();
                $registrosFechas = $stmt->fetchAll();
                $miArray = array();
                foreach ($registrosFechas as $regist) {
                    //////Conseguimos los valores
                    $idFechaPrev = $regist['idplanifi'];
                    $severidadTipo = $regist['valor_riesgo_id'];
                    $fechaInicioAux = $regist['fecha_inicio'];
                    $fechaPrevistaFinal = $fechaInicioAux;
                    $fechaEvaluacion = new \DateTime($fechaPrevistaFinal);
                    $severidadTipo = $regist['valor_riesgo_id'];

                    // Ajustar la fecha de acuerdo al tipo de severidad
                    switch ($severidadTipo) {
                        case '1':
                            $fechaEvaluacion->modify('+1 month');
                            break;
                        case '2':
                            $fechaEvaluacion->modify('+3 months');
                            break;
                        case '3':
                            $fechaEvaluacion->modify('+6 months');
                            break;
                        case '4':
                            $fechaEvaluacion->modify('+1 year');
                            break;
                        case '5':
                            $fechaEvaluacion->modify('+1 year');
                            break;
                    }
                    // Verificar si la fecha es válida
                    $year = $fechaEvaluacion->format('Y');
                    $month = $fechaEvaluacion->format('m');
                    $day = $fechaEvaluacion->format('d');

                    if (checkdate($month, $day, $year)) {
                        $fechaPrevistaFinal = $fechaEvaluacion->format('Y-m-d');
                    } else {
                        // Si la fecha no es válida, ajustarla al último día del mes
                        $fechaEvaluacion->setDate($year, $month, 1);
                        $fechaEvaluacion->modify('last day of');

                        $fechaPrevistaFinal = $fechaEvaluacion->format('Y-m-d');
                    }
                    // Agregar los valores al array
                    $miArray[] = array(
                        'idFechaPrev' => $idFechaPrev,
                        'fechaPrevistaFinal' => $fechaPrevistaFinal
                    );
                }

                foreach ($miArray as $item) {
                    $idFechaPrev = $item['idFechaPrev'];
                    $fechaPrevistaFinal = $item['fechaPrevistaFinal'];

                    $queryUpdate = "UPDATE planificacion_riesgo_causa SET fecha_prevista = '$fechaPrevistaFinal' WHERE id = '$idFechaPrev'";

                    $stmtUpdate = $this->getDoctrine()->getManager()->getConnection()->prepare($queryUpdate);
                    $stmtUpdate->execute();
                }
            }
            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_update', array('id' => $evaluacion->getId()));
        }
        return $this->render('evaluacion/edit.html.twig', array('form' => $form->createView(), 'tecnicos' => $tecnicos, 'listTecnicos' => $listTecnicos, 'personas' => $personas, 'puestosEvaluar' => $puestosSeleccionados, 'listPuestoTrabajo' => $puestosTrabajoCentro, 'zonasEvaluar' => $zonaTrabajoSeleccionadas, 'listZonaTrabajo' => $zonasTrabajoCentro, 'normativas' => $normativas, 'listGrupoNormativa' => $listGrupoNormativa, 'visitas' => $visitas, 'ficheroGenerado' => $evaluacion->getFichero()));
    }

    public function deleteEvaluacion(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getDeleteEvaluacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($id);

        $evaluacion->setAnulado(true);
        $em->persist($evaluacion);
        $em->flush();

        // Anulamos los puestos del puesto de trabajo a evaluar
        $puestosTrabajo = $em->getRepository('App\Entity\PuestoTrabajoEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));

        foreach ($puestosTrabajo as $puestoTrabajo) {
            $puestoTrabajo->setAnulado(true);
            $em->persist($puestoTrabajo);
            $em->flush();
        }
        // Anulamos las zonas de trabajo a evaluar
        $zonasTrabajo = $em->getRepository('App\Entity\ZonaTrabajoEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));

        foreach ($zonasTrabajo as $zonaTrabajo) {
            $zonaTrabajo->setAnulado(true);
            $em->persist($zonaTrabajo);
            $em->flush();
        }
        // Anulamos los riesgos-causas
        $riesgosCausa = $em->getRepository('App\Entity\RiesgoCausaEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));

        foreach ($riesgosCausa as $riesgoCausa) {
            // Buscamos las acciones preventivas de la empresa
            $accionesPreventivasEmpresa = $em->getRepository('App\Entity\AccionPreventivaEmpresaRiesgoCausa')->findBy(array('riesgoCausa' => $riesgoCausa, 'anulado' => false));

            foreach ($accionesPreventivasEmpresa as $accionPreventivaEmpresa) {
                // Buscamos los epi de la preventiva de empresa
                $episEmpresa = $em->getRepository('App\Entity\EpiPreventivaEmpresa')->findBy(array('preventivaEmpresa' => $accionPreventivaEmpresa, 'anulado' => false));

                foreach ($episEmpresa as $epiEmpresa) {
                    $epiEmpresa->setAnulado(true);
                    $em->persist($epiEmpresa);
                    $em->flush();
                }
                $accionPreventivaEmpresa->setAnulado(true);
                $em->persist($accionPreventivaEmpresa);
                $em->flush();
            }
            // Buscamos las acciones preventivas del trabajador
            $accionesPreventivasTrabajador = $em->getRepository('App\Entity\AccionPreventivaTrabajadorRiesgoCausa')->findBy(array('riesgoCausa' => $riesgoCausa, 'anulado' => false));

            foreach ($accionesPreventivasTrabajador as $accionPreventivaTrabajador) {
                // Buscamos los epi de la preventiva de empresa
                $episTrabajador = $em->getRepository('App\Entity\EpiPreventivaTrabajador')->findBy(array('preventivaTrabajador' => $accionPreventivaTrabajador, 'anulado' => false));

                foreach ($episTrabajador as $epiTrabajador) {
                    $epiTrabajador->setAnulado(true);
                    $em->persist($epiTrabajador);
                    $em->flush();
                }
                $accionPreventivaTrabajador->setAnulado(true);
                $em->persist($accionPreventivaTrabajador);
                $em->flush();
            }
            $riesgoCausa->setAnulado(true);
            $em->persist($riesgoCausa);
            $em->flush();
        }
        // Anulamos los tecnicos
        $tecnicos = $em->getRepository('App\Entity\TecnicoEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));

        foreach ($tecnicos as $tecnico) {
            $tecnico->setAnulado(true);
            $em->persist($tecnico);
            $em->flush();
        }
        // Anulamos los acompañantes
        $personas = $em->getRepository('App\Entity\PersonaEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));

        foreach ($personas as $persona) {
            $persona->setAnulado(true);
            $em->persist($persona);
            $em->flush();
        }
        $session->set('evaluacionId', null);
        $session->set('puestoTrabajoEvaluacionId', null);
        $session->set('zonaTrabajoEvaluacionId', null);
        $session->set('riesgoCausaId', null);

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        return $this->redirectToRoute('tecnico_evaluaciones_show');
    }

    function copiarEvaluacion($em, $empresa, TranslatorInterface $translator)
    {
        // Buscamos la ultima evaluacion
        $evaluacion = $em->getRepository('App\Entity\Evaluacion')->findOneBy(array('empresa' => $empresa, 'anulado' => false), array('fechaInicio' => 'DESC'));
        if (is_null($evaluacion)) {
            return -1;
        }
        $newEvaluacion = clone $evaluacion;
        $newEvaluacion->setEmpresa($empresa);
        $newEvaluacion->setFechaInicio(new \DateTime());
        $newEvaluacion->setTipo(2);
        $newEvaluacion->setFechaFin(null);
        $newEvaluacion->setFinalizada(false);
        $newEvaluacion->setFechaProxima(null);
        $newEvaluacion->setFichero(null);
        $newEvaluacion->setPasswordPdf(null);
        $newEvaluacion->setDescripcion(null);
        $newEvaluacion->setAnulado(false);
        $em->persist($newEvaluacion);
        $em->flush();

        // Creamos los centros de la evaluacion
        $centros = $em->getRepository('App\Entity\EvaluacionCentroTrabajo')->findBy(array('evaluacion' => $evaluacion));

        foreach ($centros as $centro) {
            $newCentro = clone $centro;
            $newCentro->setEvaluacion($newEvaluacion);
            $em->persist($newEvaluacion);
            $em->flush();
        }
        // Creamos los puestos del puesto de trabajo a evaluar
        $puestosTrabajo = $em->getRepository('App\Entity\PuestoTrabajoEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));

        foreach ($puestosTrabajo as $puestoTrabajo) {
            $newPuestoTrabajo = clone $puestoTrabajo;
            $newPuestoTrabajo->setEvaluacion($newEvaluacion);
            $em->persist($newPuestoTrabajo);
            $em->flush();
        }
        // Creamos las zonas de trabajo a evaluar
        $zonasTrabajo = $em->getRepository('App\Entity\ZonaTrabajoEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));

        foreach ($zonasTrabajo as $zonaTrabajo) {
            $newZonaTrabajo = clone $zonaTrabajo;
            $newZonaTrabajo->setEvaluacion($newEvaluacion);
            $em->persist($newZonaTrabajo);
            $em->flush();
        }
        // Creamos los riesgos-causas
        $riesgosCausa = $em->getRepository('App\Entity\RiesgoCausaEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));

        foreach ($riesgosCausa as $riesgoCausa) {

            $newRiesgoCausa = clone $riesgoCausa;
            $newRiesgoCausa->setEvaluacion($newEvaluacion);
            $em->persist($newRiesgoCausa);
            $em->flush();

            // Buscamos las acciones preventivas de la empresa
            $accionesPreventivasEmpresa = $em->getRepository('App\Entity\AccionPreventivaEmpresaRiesgoCausa')->findBy(array('riesgoCausa' => $riesgoCausa, 'anulado' => false));

            foreach ($accionesPreventivasEmpresa as $accionPreventivaEmpresa) {

                $newAccionPreventivaEmpresa = clone $accionPreventivaEmpresa;
                $newAccionPreventivaEmpresa->setRiesgoCausa($newRiesgoCausa);
                $em->persist($newAccionPreventivaEmpresa);
                $em->flush();

                // Buscamos los epi de la preventiva de empresa
                $episEmpresa = $em->getRepository('App\Entity\EpiPreventivaEmpresa')->findBy(array('preventivaEmpresa' => $accionPreventivaEmpresa, 'anulado' => false));

                foreach ($episEmpresa as $epiEmpresa) {
                    $newEpiEmpresa = clone $epiEmpresa;
                    $newEpiEmpresa->setPreventivaEmpresa($newAccionPreventivaEmpresa);
                    $em->persist($newEpiEmpresa);
                    $em->flush();
                }
            }
            // Buscamos las acciones preventivas del trabajador
            $accionesPreventivasTrabajador = $em->getRepository('App\Entity\AccionPreventivaTrabajadorRiesgoCausa')->findBy(array('riesgoCausa' => $riesgoCausa, 'anulado' => false));

            foreach ($accionesPreventivasTrabajador as $accionPreventivaTrabajador) {

                $newAccionPreventivaTrabajador = clone $accionPreventivaTrabajador;
                $newAccionPreventivaTrabajador->setRiesgoCausa($newRiesgoCausa);
                $em->persist($newAccionPreventivaTrabajador);
                $em->flush();

                // Buscamos los epi de la preventiva de empresa
                $episTrabajador = $em->getRepository('App\Entity\EpiPreventivaTrabajador')->findBy(array('preventivaTrabajador' => $accionPreventivaTrabajador, 'anulado' => false));

                foreach ($episTrabajador as $epiTrabajador) {
                    $newEpiTrabajador = clone $epiTrabajador;
                    $newEpiTrabajador->setPreventivaTrabajador($newAccionPreventivaTrabajador);
                    $em->persist($newEpiTrabajador);
                    $em->flush();
                }
            }
        }
        // Creamos los tecnicos
        $tecnicos = $em->getRepository('App\Entity\TecnicoEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));

        foreach ($tecnicos as $tecnico) {
            $newTecnico = clone $tecnico;
            $newTecnico->setEvaluacion($newEvaluacion);
            $em->persist($newTecnico);
            $em->flush();
        }
        // Creamos los acompañantes
        $personas = $em->getRepository('App\Entity\PersonaEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));

        foreach ($personas as $persona) {
            $newPersona = clone $persona;
            $newPersona->setEvaluacion($newEvaluacion);
            $em->persist($newPersona);
            $em->flush();
        }
        // Creamos las normativas
        $normativas = $em->getRepository('App\Entity\GrupoNormativaEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));

        foreach ($normativas as $normativa) {
            $newNormativa = clone $normativa;
            $newNormativa->setEvaluacion($newEvaluacion);
            $em->persist($newNormativa);
            $em->flush();
        }
        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success', $traduccion);

        return $newEvaluacion->getId();
    }

    public function finalizarEvaluacion(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getDeleteEvaluacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($id);

        // Buscamos los puestos a evaluar
        $puestoTrabajoEvaluacion = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'anulado' => false));
        if (count($puestoTrabajoEvaluacion) > 0) {

            foreach ($puestoTrabajoEvaluacion as $pte) {
                // Buscamos los riesgos-causas del puesto de trabajo
                $riesgoCausaPuesto = $this->getDoctrine()->getRepository('App\Entity\RiesgoCausaEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'puestoTrabajo' => $pte->getPuestoTrabajo(), 'anulado' => false));

                if (count($riesgoCausaPuesto) > 0) {
                    // Comprobamos si el riesgo-causa esta evaluado
                    foreach ($riesgoCausaPuesto as $rcg) {
                        if (is_null($rcg->getProbabilidad()) || is_null($rcg->getSeveridad()) || is_null($rcg->getValorRiesgo())) {
                            $traduccion = $translator->trans('TRANS_EVALUACION_FINALIZADA_ERROR_RIESGOS_NO_EVALUADOS', array(), 'evaluacion');
                            $this->addFlash('danger', $traduccion);

                            return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_update', array('id' => $id));
                        }
                    }
                } else {
                    $traduccion = $translator->trans('TRANS_EVALUACION_FINALIZADA_ERROR_RIESGOS', array(), 'evaluacion');
                    $this->addFlash('danger', $traduccion);

                    return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_update', array('id' => $id));
                }
            }
        } else {
            $traduccion = $translator->trans('TRANS_EVALUACION_FINALIZADA_ERROR_PUESTOS', array(), 'evaluacion');
            $this->addFlash('danger', $traduccion);

            return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_update', array('id' => $id));
        }

        $evaluacion->setFechaFin(new \DateTime());
        $evaluacion->setFinalizada(true);
        $em->persist($evaluacion);
        $em->flush();

        $traduccion = $translator->trans('TRANS_FINALIZAR_EVALUACION_OK', array(), 'evaluacion');
        $this->addFlash('success', $traduccion);

        // Peticio 28/07/2023
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $user = $this->getUser();
        $usuario = $repository->findForSecurityUser($user);
        $username = $usuario->getUsername();
        $mail = "mdtmeditrauma@meditrauma.com";
        $emailUser = $usuario->getEmail();
        $passwordMail = "6M%y59ns1";
        $hostMail = "mail.meditrauma.com";
        $puertoMail = "25";
        $encriptacionMail = "";
        $userMail = "mdtmeditrauma@meditrauma.com";

        $transport = new \Swift_SmtpTransport($hostMail, $puertoMail, $encriptacionMail);
        $transport->setUsername($userMail);
        $transport->setPassword($passwordMail);
        $transport->setHost($hostMail);
        $transport->setAuthMode('login');

        $mailer = new \Swift_Mailer($transport);

        //select COUNT(*) from evaluacion e where empresa_id = 3148
        $empresa = $em->getRepository('App\Entity\Empresa')->find($evaluacion->getEmpresa());
        // Recuperamos el conteo de evaluaciones de la empresa
        $empresaId = $empresa->getId();
        $query = "SELECT COUNT(*) AS numero FROM evaluacion e WHERE empresa_id = :empresaId";
        $entityManager = $this->getDoctrine()->getManager();
        $stmt = $entityManager->getConnection()->prepare($query);
        $stmt->bindValue('empresaId', $empresaId);
        $stmt->execute();
        $countEvalu = $stmt->fetchAll();

        // Asegurémonos de que el resultado tiene al menos un registro
        if (count($countEvalu) > 0) {
            // Obtenemos el valor del contador "numero" del primer registro
            $numero = $countEvalu[0]['numero'];
        } else {
            // Si no hay registros, significa que no se encontraron evaluaciones
            $numero = 0;
        }
        $mensaje = "La evaluación número: " . $numero . ", para la empresa: " . $empresa->getEmpresa() . ", con fecha de inicio: " . $evaluacion->getFechaInicio()->format('d/m/Y') . " ha sido finalizada.";

        $medicos = $this->getDoctrine()->getRepository('App\Entity\User')->findBy(['rol_id' => 4]);

        $connection = $this->getDoctrine()->getManager()->getConnection();
        $query = "INSERT INTO notificaciones_internas (id, fecha, destinatario, remitente, mensaje, leido)
                          VALUES (:id, :fecha, :destinatario, :remitente, :mensaje, false)";
        $statement = $connection->prepare($query);
        $querySelectUpdated = "SELECT id FROM notificaciones_internas ORDER BY id DESC LIMIT 1;";
        $statementSelect = $connection->prepare($querySelectUpdated);
        $statementSelect->execute();
        $updatedRows = $statementSelect->fetchAll();
        //Peticio 28/07/2023
        foreach ($updatedRows as $row) {
            $ultimoId = $row['id'];
            // Hacer algo con el valor de $id
        }
        $querySelectUpdated2 = "SELECT t.alias from empresa e inner join tecnico t on e.gestor_administrativo_id = t.id where e.id = $empresaId;";
        $statementSelect2 = $connection->prepare($querySelectUpdated2);
        $statementSelect2->execute();
        $updatedRows2 = $statementSelect2->fetchAll();
        foreach ($updatedRows2 as $row2) {
            $destinatario = $row2['alias'];
            // Hacer algo con el valor de $id
        }
        $fechaActual = new \DateTime();
        $fechaFormateada = $fechaActual->format('d-m-Y H:i:s'); // Formatear la fecha a formato 'YYYY-MM-DD HH:MM:SS'
        // Reemplazar saltos de línea por otro carácter (por ejemplo, <br>)
        //$mensajeSinSaltos = str_replace("\n", " ", $mensaje);
        $params = [
            'id' => $ultimoId + 1,
            'fecha' => $fechaFormateada,
            'destinatario' => $destinatario,
            'remitente' => $username,
            'mensaje' => $mensaje,
        ];
        $statement->execute($params);
        //foreach ($medicos as $e){
        //$this->sendEmail($e->getEmail(), $mensaje, $mailer, $mail, $em, $usuario, $emailUser,$empresa);
        //}
        return $this->redirectToRoute('tecnico_evaluaciones_show');
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

    function sendEmail($to, $mensaje, $mailer, $mail, $em, $usuario, $emailUser, $empresa)
    {
        // Enviamos el mail al cliente
        $message = new \Swift_Message();
        $message->setSubject("Evaluacion de " . $empresa->getEmpresa() . " Finalizada");
        $message->setFrom($mail);
        $message->setTo($to);
        $message->setBody($mensaje, 'text/plain');
        $message->setReplyTo($emailUser);
        $mailer->send($message);
        // Insertamos el correo en el log
        $this->insertLogMail($em, $usuario, "Evaluacion de " . $empresa->getEmpresa() . " Finalizada", $to, $message->getBody(), "Evaluacion Finalizada");
    }

    public function viewEvaluaciones(Request $request, $id)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEvaluacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $empresa = $this->getDoctrine()->getRepository('App\Entity\Empresa')->find($id);

        if (!$empresa) {
            throw $this->createNotFoundException(
                'La empresa con id ' . $id . ' no existe'
            );
        }
        $session->set('empresa', $empresa);

        // Buscamos los centros de la empresa
        $centrosEmpresa = $this->getDoctrine()->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        return $this->render('evaluacion/view.html.twig', array('empresa' => $empresa, 'centros' => $centrosEmpresa));
    }

    public function addTecnicoEvaluacion(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $evaluacionId = $_REQUEST['evaluacionId'];
        $tecnicoId = $_REQUEST['tecnicoId'];

        $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($evaluacionId);
        $tecnico = $em->getRepository('App\Entity\UsuarioTecnico')->find($tecnicoId);

        $tecnicoEvaluacion = new TecnicoEvaluacion();
        $tecnicoEvaluacion->setEvaluacion($evaluacion);
        $tecnicoEvaluacion->setTecnico($tecnico);
        $em->persist($tecnicoEvaluacion);
        $em->flush();

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function deleteTecnicoEvaluacion(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $tecnicoEvaluacionId = $_REQUEST['tecnicoEvaluacionId'];
        $tecnicoEvaluacion = $em->getRepository('App\Entity\TecnicoEvaluacion')->find($tecnicoEvaluacionId);

        $tecnicoEvaluacion->setAnulado(true);
        $em->persist($tecnicoEvaluacion);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function addPersonaEvaluacion(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $evaluacionId = $_REQUEST['evaluacionId'];
        $nombre = $_REQUEST['nombre'];
        $apellido1 = $_REQUEST['apellido1'];
        $apellido2 = $_REQUEST['apellido2'];
        $cargo = $_REQUEST['cargo'];
        $documento = $_REQUEST['documento'];
        $horario = $_REQUEST['horario'];
        $sitio = $_REQUEST['sitio'];

        $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($evaluacionId);

        $persona = new PersonaEvaluacion();
        $persona->setEvaluacion($evaluacion);
        $persona->setNombre($nombre);
        $persona->setApellido1($apellido1);
        $persona->setApellido2($apellido2);
        $persona->setDocumento($documento);
        $persona->setCargo($cargo);
        $persona->setHorario($horario);
        $persona->setSitio($sitio);
        $persona->setAnulado(false);

        $em->persist($persona);
        $em->flush();

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function deletePersonaEvaluacion(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $personaEvaluacionId = $_REQUEST['personaEvaluacionId'];
        $persona = $em->getRepository('App\Entity\PersonaEvaluacion')->find($personaEvaluacionId);
        $persona->setAnulado(true);

        $em->persist($persona);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function addPuestoTrabajoEvaluar(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $evaluacionId = $_REQUEST['evaluacionId'];
        $puestoId = $_REQUEST['puestoId'];
        $trabajadores = $_REQUEST['trabajadores'];

        $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($evaluacionId);
        $puesto = $em->getRepository('App\Entity\PuestoTrabajoCentro')->find($puestoId);

        $puestoTrabajoEvaluacion = new PuestoTrabajoEvaluacion();
        $puestoTrabajoEvaluacion->setAnulado(false);
        $puestoTrabajoEvaluacion->setPuestoTrabajo($puesto);
        $puestoTrabajoEvaluacion->setEvaluacion($evaluacion);
        if ($trabajadores != "") {
            $puestoTrabajoEvaluacion->setTrabajadores($trabajadores);
        }
        if (!is_null($puesto->getPuestoTrabajoGenerico())) {
            $puestoTrabajoEvaluacion->setTarea($puesto->getPuestoTrabajoGenerico()->getObservaciones());
        }
        $em->persist($puestoTrabajoEvaluacion);
        $em->flush();

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        //Ticket#2023121310000061 — Introduïr treballadors
        ////////////////////////////////////////////////////////////////////////////////////
        $empresa = $evaluacion->getEmpresa();
        $empresaId = $empresa->getId();

        // Buscamos los centros de trabajo de la empresa
        $centroEmpresa = $this->getDoctrine()->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('empresa' => $empresa));
        $centroEmpresaString = "";
        foreach ($centroEmpresa as $ce) {
            if (!is_null($ce->getCentro())) {
                $centroEmpresaString .= $ce->getCentro()->getId() . ', ';
            }
        }
        $centroEmpresaString = rtrim($centroEmpresaString, ', ');

        // Buscamos los trabajadores del puesto de trabajo
        $query = "select a.id, c.id as empresaid, d.id as centroid, b.nombre, c.empresa, d.nombre as centro from puesto_trabajo_trabajador a 
        inner join trabajador b on a.trabajador_id = b.id
        left join empresa c on a.empresa_id = c.id
        left join centro d on a.centro_id = d.id
        where a.puesto_trabajo_id = $puestoId 
        and a.anulado = false
        and a.fecha_baja is null ";

        if ($centroEmpresaString != "") {
            $query .= "and (a.centro_id in ($centroEmpresaString) or a.empresa_id = $empresaId) ";
        } else {
            $query .= "and a.empresa_id = $empresaId ";
        }
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $trabajadoresPuestoTrabajo = $stmt->fetchAll();

        if (count($trabajadoresPuestoTrabajo) == 0) {
            $puestoTrabajoEvaluacion->setTrabajadores(0);
            $em->persist($puestoTrabajoEvaluacion);
            $em->flush();
        } else {
            if (intval($puestoTrabajoEvaluacion->getTrabajadores()) != count($trabajadoresPuestoTrabajo)) {
                $puestoTrabajoEvaluacion->setTrabajadores(count($trabajadoresPuestoTrabajo));
                $em->persist($puestoTrabajoEvaluacion);
                $em->flush();
            }
        }
        return new JsonResponse($data);
    }

    public function deletePuestoTrabajoEvaluar(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $puestoTrabajoEvaluacionId = $_REQUEST['puestoTrabajoEvaluacionId'];
        $puestoTrabajoEvaluacion = $em->getRepository('App\Entity\PuestoTrabajoEvaluacion')->find($puestoTrabajoEvaluacionId);

        $puestoTrabajoEvaluacion->setAnulado(true);
        $em->persist($puestoTrabajoEvaluacion);
        $em->flush();

        $session->set('puestoTrabajoEvaluacionId', null);

        $evaluacionId = $session->get('evaluacionId');
        $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($evaluacionId);

        // Anulamos los riesgos-causas del puesto de trabajo
        $riesgosCausas = $em->getRepository('App\Entity\RiesgoCausaEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'puestoTrabajo' => $puestoTrabajoEvaluacion->getPuestoTrabajo(), 'anulado' => false));

        foreach ($riesgosCausas as $riesgoCausa) {
            $riesgoCausa->setAnulado(true);
            $em->persist($riesgoCausa);
            $em->flush();
        }
        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function addZonaTrabajoEvaluar(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $evaluacionId = $_REQUEST['evaluacionId'];
        $zonaId = $_REQUEST['zonaId'];
        $trabajadores = $_REQUEST['trabajadores'];

        $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($evaluacionId);
        $zona = $em->getRepository('App\Entity\ZonaTrabajo')->find($zonaId);

        $zonaTrabajoEvaluacion = new ZonaTrabajoEvaluacion();
        $zonaTrabajoEvaluacion->setAnulado(false);
        $zonaTrabajoEvaluacion->setZonaTrabajo($zona);
        $zonaTrabajoEvaluacion->setEvaluacion($evaluacion);
        if ($trabajadores != "") {
            $zonaTrabajoEvaluacion->setTrabajadores($trabajadores);
        }
        $em->persist($zonaTrabajoEvaluacion);
        $em->flush();

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function deleteZonaTrabajoEvaluar(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $zonaTrabajoEvaluacionId = $_REQUEST['zonaTrabajoEvaluacionId'];
        $zonaTrabajoEvaluacion = $em->getRepository('App\Entity\ZonaTrabajoEvaluacion')->find($zonaTrabajoEvaluacionId);

        $zonaTrabajoEvaluacion->setAnulado(true);
        $em->persist($zonaTrabajoEvaluacion);
        $em->flush();

        $session->set('zonaTrabajoEvaluacionId', null);

        $evaluacionId = $session->get('evaluacionId');
        $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($evaluacionId);

        // Anulamos los riesgos-causas del puesto de trabajo
        $riesgosCausas = $em->getRepository('App\Entity\RiesgoCausaEvaluacion')->findBy(array('evaluacion' => $evaluacion, 'zonaTrabajo' => $zonaTrabajoEvaluacion->getZonaTrabajo(), 'anulado' => false));

        foreach ($riesgosCausas as $riesgoCausa) {
            $riesgoCausa->setAnulado(true);
            $em->persist($riesgoCausa);
            $em->flush();
        }
        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function puestoTrabajoEvaluar(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditEvaluacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $session->set('trabajadorTecnico', null);

        $puestoTrabajoEvaluacion = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoEvaluacion')->find($id);

        $evaluacion = $puestoTrabajoEvaluacion->getEvaluacion();
        $empresa = $evaluacion->getEmpresa();
        $empresaId = $empresa->getId();

        $fechaEvaluacion = "";
        if (!is_null($evaluacion->getFechaInicio())) {
            $fechaEvaluacion = $evaluacion->getFechaInicio()->format('d/m/Y');
        }
        $puestoTrabajoId = $puestoTrabajoEvaluacion->getPuestoTrabajo()->getId();
        $puestoTrabajoDesc = $puestoTrabajoEvaluacion->getPuestoTrabajo()->getDescripcion();

        $evaluacionId = $evaluacion->getId();

        // Buscamos los riesgos-casuas del puesto
        $query = "select a.id, b.codigo || ' - ' || b.descripcion as gruporiesgo, c.codigo || ' - ' || c.descripcion as riesgo, e.descripcion as causa, d.descripcion as valoracion, a.finalizado, a.ultimo_modificado from riesgo_causa_evaluacion a
           left join grupo_riesgo b on a.grupo_riesgo_id = b.id
           left join riesgo c on a.riesgo_id = c.id
           left join valor_riesgo d on a.valor_riesgo_id = d.id 
           left join causa e on a.causa_id = e.id
           where a.evaluacion_id = $evaluacionId
           and a.puesto_trabajo_id = $puestoTrabajoId
           and a.anulado = false
           order by c.codigo asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $riesgosCausasPuestoTrabajo = $stmt->fetchAll();

        // Buscamos los centros de trabajo de la empresa
        $centroEmpresa = $this->getDoctrine()->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('empresa' => $empresa));
        $centroEmpresaString = "";
        foreach ($centroEmpresa as $ce) {
            if (!is_null($ce->getCentro())) {
                $centroEmpresaString .= $ce->getCentro()->getId() . ', ';
            }
        }
        $centroEmpresaString = rtrim($centroEmpresaString, ', ');

        // Buscamos los trabajadores del puesto de trabajo
        $query = "select a.id, c.id as empresaid, d.id as centroid, b.nombre, c.empresa, d.nombre as centro from puesto_trabajo_trabajador a 
        inner join trabajador b on a.trabajador_id = b.id
        left join empresa c on a.empresa_id = c.id
        left join centro d on a.centro_id = d.id
        where a.puesto_trabajo_id = $puestoTrabajoId 
        and a.anulado = false
        and a.fecha_baja is null ";

        if ($centroEmpresaString != "") {
            $query .= "and (a.centro_id in ($centroEmpresaString) or a.empresa_id = $empresaId) ";
        } else {
            $query .= "and a.empresa_id = $empresaId ";
        }
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $trabajadoresPuestoTrabajo = $stmt->fetchAll();

        if (count($trabajadoresPuestoTrabajo) == 0) {
            $puestoTrabajoEvaluacion->setTrabajadores(0);
            $em->persist($puestoTrabajoEvaluacion);
            $em->flush();
        } else {
            if (intval($puestoTrabajoEvaluacion->getTrabajadores()) != count($trabajadoresPuestoTrabajo)) {
                $puestoTrabajoEvaluacion->setTrabajadores(count($trabajadoresPuestoTrabajo));
                $em->persist($puestoTrabajoEvaluacion);
                $em->flush();
            }
        }
        // Buscamos las maquinas del puesto de trabajo
        $maquinas = $this->getDoctrine()->getRepository('App\Entity\MaquinaEmpresaTrabajador')->findBy(array('puestoTrabajo' => $puestoTrabajoEvaluacion->getPuestoTrabajo(), 'anulado' => false));
        // Buscamos las maquinas del centro
        $empresa = $puestoTrabajoEvaluacion->getEvaluacion()->getEmpresa();
        //<!--Peticio 01/09/2023-->
        $listMaquinasEmpresa = $this->getDoctrine()->getRepository('App\Entity\MaquinaEmpresa')->findBy(array('anulado' => false), array('descripcion' => 'ASC'));
        // Buscamos los productos quimicos del puesto de trabajo
        $productosQuimicos = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoContaminante')->findBy(array('puestoTrabajo' => $puestoTrabajoEvaluacion->getPuestoTrabajo(), 'anulado' => false));
        $evaluacionId = $puestoTrabajoEvaluacion->getEvaluacion()->getId();
        // Buscamos las plantilla de la ficha de riesgos
        $carpetaFichaRiesgos = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(15);
        $plantillasFichaRiesgos = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaFichaRiesgos, 'anulado' => false));

        $form = $this->createForm(PuestoTrabajoEvaluarType::class, $puestoTrabajoEvaluacion);
        $form->handleRequest($request);

        $session->set('puestoTrabajoEvaluacionId', $puestoTrabajoEvaluacion->getId());
        $session->set('zonaTrabajoEvaluacionId', null);

        if ($form->isSubmitted()) {
            $puestoTrabajoEvaluacion = $form->getData();

            $em->persist($puestoTrabajoEvaluacion);
            $em->flush();

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_puesto_trabajo_evaluar', array('id' => $puestoTrabajoEvaluacion->getId()));
        }

        return $this->render('evaluacion/evaluar.html.twig', array('listPlantillasFichaRiesgos' => $plantillasFichaRiesgos, 'form' => $form->createView(), 'zonaTrabajoSn' => false, 'trabajadoresPuestoTrabajo' => $trabajadoresPuestoTrabajo, 'evaluacionId' => $evaluacionId, 'desc' => $puestoTrabajoDesc, 'fechaEvaluacion' => $fechaEvaluacion, 'riesgosCausas' => $riesgosCausasPuestoTrabajo, 'puestotrabajoid' => $puestoTrabajoId, 'maquinaria' => $maquinas, 'listMaquinasEmpresa' => $listMaquinasEmpresa, 'productosQuimicos' => $productosQuimicos, 'empresaId' => $empresaId));
    }

    public function addTrabajadorPuestoTrabajo(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditTrabajadorTecnicoSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $arrayTrabajadoresId = array();
        $arrayEmpresasId = array();
        $empresaId = null;

        $empresa = $session->get('empresa');
        if (!is_null($empresa)) {
            $empresaId = $empresa->getId();
            $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
        }
        $trabajadorId = $session->get('trabajadorTecnico');
        $trabajador = null;
        if (!is_null($trabajadorId)) {
            $trabajador = $em->getRepository('App\Entity\Trabajador')->find($trabajadorId);
            $trabajadoresEmpresa = $this->getDoctrine()->getRepository('App\Entity\TrabajadorEmpresa')->findBy(array('trabajador' => $trabajador, 'anulado' => false));
        } else {
            $trabajadoresEmpresa = $this->getDoctrine()->getRepository('App\Entity\TrabajadorEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));
        }
        foreach ($trabajadoresEmpresa as $te) {
            if (!is_null($te->getTrabajador())) {
                array_push($arrayTrabajadoresId, $te->getTrabajador()->getId());
            }
            if (!is_null($te->getEmpresa())) {
                array_push($arrayEmpresasId, $te->getEmpresa()->getId());
            }
        }
        $puestoTrabajoObj = null;
        $puestoTrabajoEvaluacionId = $session->get('puestoTrabajoEvaluacionId');
        if (!is_null($puestoTrabajoEvaluacionId)) {
            $puestoTrabajoEvaluacion = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoEvaluacion')->find($puestoTrabajoEvaluacionId);
            $puestoTrabajoObj = $puestoTrabajoEvaluacion->getPuestoTrabajo();
        }
        $puestoTrabajoTrabajador = new PuestoTrabajoTrabajador();
        $puestoTrabajoTrabajador->setFechaAlta(new \DateTime());
        $form = $this->createForm(PuestoTrabajoTrabajadorType::class, $puestoTrabajoTrabajador, array('trabajadoresId' => $arrayTrabajadoresId, 'puestoTrabajoId' => null, 'centrosId' => null, 'empresaId' => $empresaId, 'puestoTrabajoObj' => $puestoTrabajoObj, 'centroTrabajoObj' => null, 'empresasId' => $arrayEmpresasId, 'empresaObj' => $empresa, 'trabajadorObj' => $trabajador));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $puestoTrabajoTrabajador = $form->getData();

            // Obtenemos el puesto de trabajo
            $puestoTrabajoFormId = $form["puestoTrabajo"]->getViewData();
            $puestoTrabajo = $em->getRepository('App\Entity\PuestoTrabajoCentro')->find($puestoTrabajoFormId);
            $puestoTrabajoTrabajador->setPuestoTrabajo($puestoTrabajo);

            // Obtenemos el centro de trabajo
            $centroTrabajoFormId = $form["centro"]->getViewData();
            if ($centroTrabajoFormId != "") {
                $centroTrabajo = $em->getRepository('App\Entity\Centro')->find($centroTrabajoFormId);
                $puestoTrabajoTrabajador->setCentro($centroTrabajo);
            }
            $em->persist($puestoTrabajoTrabajador);
            $em->flush();

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_puesto_trabajo_update_trabajador', array('id' => $puestoTrabajoTrabajador->getId(), 'empresaId' => $puestoTrabajoTrabajador->getEmpresa()->getId()));
        }
        return $this->render('evaluacion/puestoTrabajoTrabajador.html.twig', array('form' => $form->createView(), 'trabajadorId' => $trabajadorId));
    }

    public function updateTrabajadorPuestoTrabajo(Request $request, $id, $empresaId, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditTrabajadorTecnicoSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $puestoTrabajoTrabajador = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoTrabajador')->find($id);
        $centroTrabajo = $puestoTrabajoTrabajador->getCentro();

        $empresa = $puestoTrabajoTrabajador->getEmpresa();
        if (!is_null($empresa)) {
            $empresaId = $empresa->getId();
        } else {
            $empresa = $this->getDoctrine()->getRepository('App\Entity\Empresa')->find($empresaId);
        }
        $puestoTrabajo = $puestoTrabajoTrabajador->getPuestoTrabajo();
        $puestoTrabajoId = $puestoTrabajo->getId();
        $trabajadorObj = $puestoTrabajoTrabajador->getTrabajador();

        $arrayTrabajadoresId = array();
        $arrayEmpresasId = array();

        array_push($arrayTrabajadoresId, $trabajadorObj->getId());
        array_push($arrayEmpresasId, $empresaId);

        // Buscamos los centros de la empresa
        $centrosEmpresa = $this->getDoctrine()->getRepository('App\Entity\CentroTrabajoEmpresa')->findBy(array('empresa' => $empresa, 'anulado' => false));

        $arrayCentrosEmpresa = array();
        foreach ($centrosEmpresa as $c) {
            array_push($arrayCentrosEmpresa, $c->getCentro());
        }
        $form = $this->createForm(PuestoTrabajoTrabajadorType::class, $puestoTrabajoTrabajador, array('trabajadoresId' => $arrayTrabajadoresId, 'puestoTrabajoId' => $puestoTrabajoId, 'centrosId' => $arrayCentrosEmpresa, 'empresaId' => $empresaId, 'puestoTrabajoObj' => $puestoTrabajo, 'centroTrabajoObj' => $centroTrabajo, 'empresasId' => $arrayEmpresasId, 'empresaObj' => $empresa, 'trabajadorObj' => $trabajadorObj));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $puestoTrabajoTrabajador = $form->getData();
            $em->persist($puestoTrabajoTrabajador);
            $em->flush();

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_puesto_trabajo_update_trabajador', array('id' => $id, 'empresaId' => $empresaId));
        }
        return $this->render('evaluacion/puestoTrabajoTrabajador.html.twig', array('form' => $form->createView()));
    }

    public function deleteTrabajadorPuestoTrabajo(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $puestoTrabajoEvaluacionId = $_REQUEST['puestoTrabajoTrabajadorId'];
        $puestoTrabajoTrabajador = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoTrabajador')->find($puestoTrabajoEvaluacionId);
        $puestoTrabajoTrabajador->setAnulado(true);
        $em->persist($puestoTrabajoTrabajador);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function zonaTrabajoEvaluar(Request $request, $id, TranslatorInterface $translator)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditEvaluacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $zonaTrabajoEvaluacion = $this->getDoctrine()->getRepository('App\Entity\ZonaTrabajoEvaluacion')->find($id);

        $fechaEvaluacion = "";
        if (!is_null($zonaTrabajoEvaluacion->getEvaluacion()->getFechaInicio())) {
            $fechaEvaluacion = $zonaTrabajoEvaluacion->getEvaluacion()->getFechaInicio()->format('d/m/Y');
        }
        $zonaTrabajoDesc = $zonaTrabajoEvaluacion->getZonaTrabajo()->getDescripcion();
        $zonaTrabajoId = $zonaTrabajoEvaluacion->getZonaTrabajo()->getId();

        $evaluacionId = $zonaTrabajoEvaluacion->getEvaluacion()->getId();

        $evaluacion = $this->getDoctrine()->getRepository('App\Entity\Evaluacion')->find($evaluacionId);
        $empresa = $evaluacion->getEmpresa();
        $empresaId = $empresa->getId();

        // Buscamos los riesgos-casuas de la zona
        $query = "select a.id, b.codigo || ' - ' || b.descripcion as gruporiesgo, c.codigo || ' - ' || c.descripcion as riesgo, e.descripcion as causa, d.descripcion as valoracion, a.finalizado, a.ultimo_modificado from riesgo_causa_evaluacion a
           left join grupo_riesgo b on a.grupo_riesgo_id = b.id
           left join riesgo c on a.riesgo_id = c.id
           left join valor_riesgo d on a.valor_riesgo_id = d.id 
           left join causa e on a.causa_id = e.id
           where a.evaluacion_id = $evaluacionId
           and a.zona_trabajo_id = $zonaTrabajoId
           and a.anulado = false
           order by c.codigo asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $riesgosCausasZonaTrabajo = $stmt->fetchAll();

        // Buscamos las plantilla de la ficha de riesgos
        $carpetaFichaRiesgos = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillasCarpeta')->find(15);
        $plantillasFichaRiesgos = $this->getDoctrine()->getRepository('App\Entity\GdocPlantillas')->findBy(array('carpeta' => $carpetaFichaRiesgos, 'anulado' => false));

        $form = $this->createForm(ZonaTrabajoEvaluarType::class, $zonaTrabajoEvaluacion);
        $form->handleRequest($request);

        $session->set('puestoTrabajoEvaluacionId', null);
        $session->set('zonaTrabajoEvaluacionId', $zonaTrabajoEvaluacion->getId());

        $repository = $this->getDoctrine()->getRepository('App\Entity\Centro');

        $centro = $repository->findOneBy([
            'empresa' => $empresa,
            'nombre' => $zonaTrabajoDesc,
        ]);
        if ($form->isSubmitted()) {
            $zonaTrabajoEvaluacion = $form->getData();
            $em = $this->getDoctrine()->getManager();
            //Petició David Gil.
            $descripcionActividad = $form["tarea"]->getData();
            $centro->setActividadCentro($descripcionActividad);
            if ($centro) {
                $em->persist($centro);
            }
            $em->persist($zonaTrabajoEvaluacion);
            $em->flush();

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_puesto_trabajo_evaluar', array('id' => $zonaTrabajoEvaluacion->getId()));
        }
        return $this->render('evaluacion/evaluar.html.twig', array('zonaTrabajoSn' => true, 'trabajadoresPuestoTrabajo' => null, 'productosQuimicos' => null, 'maquinaria' => null, 'puestotrabajoid' => 1, 'listMaquinasEmpresa' => null, 'productosQuimicos' => null, 'form' => $form->createView(), 'evaluacionId' => $evaluacionId, 'desc' => $zonaTrabajoDesc, 'empresaId' => $empresaId, 'fechaEvaluacion' => $fechaEvaluacion, 'riesgosCausas' => $riesgosCausasZonaTrabajo, 'listPlantillasFichaRiesgos' => $plantillasFichaRiesgos));
    }

    public function createRiesgoCausa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditEvaluacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $session->set('riesgoCausaId', null);
        $riesgoCausa = new RiesgoCausaEvaluacion();
        $evaluacionId = $session->get('evaluacionId');
        $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($evaluacionId);
        $grupoRiesgo = $em->getRepository('App\Entity\GrupoRiesgo')->find(0);
        $tipoPlanificacion = $em->getRepository('App\Entity\TipoPlanificacion')->findOneBy(array('descripcion' => 'CORRECTORAS Y/O PREVENTIVAS'));

        $metodologiaId = null;
        $metodologia = $evaluacion->getMetodologia();
        if (!is_null($metodologia)) {
            $metodologiaId = $metodologia->getId();
        }
        $arrayTrabajadorId = array();
        $trabajadorEmpresa = $em->getRepository('App\Entity\TrabajadorEmpresa')->findBy(array('empresa' => $evaluacion->getEmpresa(), 'anulado' => false));

        foreach ($trabajadorEmpresa as $ta) {
            if (!is_null($ta->getTrabajador())) {
                array_push($arrayTrabajadorId, $ta->getTrabajador()->getId());
            }
        }
        // Buscamos el responsable de la empresa
        $responsable = $evaluacion->getEmpresa()->getNombreRepresentante();

        $form = $this->createForm(RiesgoCausaType::class, $riesgoCausa, array('metodologia' => $metodologiaId, 'grupoRiesgoId' => $grupoRiesgo->getId(), 'grupoRiesgoObj' => $grupoRiesgo, 'trabajadorId' => $arrayTrabajadorId, 'tipoPlanificacion' => $tipoPlanificacion, 'fechaPrevista' => null, 'fechaRealizacion' => null, 'coste' => null, 'responsable' => $responsable, 'trabajadoresSn' => false));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $riesgoCausa = $form->getData();

            $puestoTrabajoEvaluacionId = $session->get('puestoTrabajoEvaluacionId');
            $zonaTrabajoEvaluacionId = $session->get('zonaTrabajoEvaluacionId');

            if (!is_null($puestoTrabajoEvaluacionId)) {
                $puestoTrabajoEvaluacion = $em->getRepository('App\Entity\PuestoTrabajoEvaluacion')->find($puestoTrabajoEvaluacionId);
                $riesgoCausa->setPuestoTrabajo($puestoTrabajoEvaluacion->getPuestoTrabajo());
            }
            if (!is_null($zonaTrabajoEvaluacionId)) {
                $zonaTrabajoEvaluacion = $em->getRepository('App\Entity\ZonaTrabajoEvaluacion')->find($zonaTrabajoEvaluacionId);
                $riesgoCausa->setZonaTrabajo($zonaTrabajoEvaluacion->getZonaTrabajo());
            }
            $riesgoCausa->setGrupoRiesgo($grupoRiesgo);
            $riesgoCausa->setEvaluacion($evaluacion);
            $em->persist($riesgoCausa);
            $em->flush();

            // Guardamos la planificacion
            $fechaPrevista = $form["fechaPrevista"]->getData();
            $fechaRealizacion = $form["fechaRealizacion"]->getData();
            $coste = $form["costePrevisto"]->getData();
            $responsable = $form["responsable"]->getData();
            $tipoPlanificacion = $form["tipoPlanificacion"]->getData();
            $trabajadoresSn = $form["trabajadores"]->getData();

            $planificacionNew = new PlanificacionRiesgoCausa();
            $planificacionNew->setRiesgoCausa($riesgoCausa);
            $planificacionNew->setTipoPlanificacion($tipoPlanificacion);
            $planificacionNew->setFechaPrevista($fechaPrevista);
            $planificacionNew->setFechaRealizacion($fechaRealizacion);
            $planificacionNew->setCostePrevisto($coste);
            $planificacionNew->setResponsable($responsable);
            $planificacionNew->setTrabajadores($trabajadoresSn);
            $em->persist($planificacionNew);
            $em->flush();

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_update_riesgo_causa', array('id' => $riesgoCausa->getId()));
        }
        return $this->render('evaluacion/riesgoCausa.html.twig', array('form' => $form->createView(), 'accionPreventivaEmpresa' => null, 'accionPreventivaTrabajador' => null, 'fechaEvaluacion' => null, 'metodologiaId' => $metodologiaId, 'imagenes' => null));
    }

    public function updateRiesgoCausa(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditEvaluacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $riesgoCausa = $em->getRepository('App\Entity\RiesgoCausaEvaluacion')->find($id);
        $session->set('riesgoCausaId', $id);
        $grupoRiesgo = $riesgoCausa->getGrupoRiesgo();

        $grupoRiesgoId = null;
        if (!is_null($grupoRiesgo)) {
            $grupoRiesgoId = $grupoRiesgo->getId();
        } else {
            $grupoRiesgoId = 0;
            $grupoRiesgo = $em->getRepository('App\Entity\GrupoRiesgo')->find($grupoRiesgoId);
        }
        // Buscamos las acciones preventivas del riesgo-causa
        $accionPreventivaEmpresa = $em->getRepository('App\Entity\AccionPreventivaEmpresaRiesgoCausa')->findBy(array('riesgoCausa' => $riesgoCausa, 'anulado' => false));
        // Buscamos las acciones preventivas del riesgo-causa
        $accionPreventivaTrabajador = $em->getRepository('App\Entity\AccionPreventivaTrabajadorRiesgoCausa')->findBy(array('riesgoCausa' => $riesgoCausa, 'anulado' => false));

        $metodologia = $riesgoCausa->getEvaluacion()->getMetodologia();
        if (!is_null($metodologia)) {
            $metodologiaId = $metodologia->getId();
        }
        // Buscamos las imagenes del riesgo-causa
        $imagenes = $em->getRepository('App\Entity\RiesgoCausaImg')->findBy(array('riesgoCausa' => $riesgoCausa, 'anulado' => false));

        $arrayTrabajadorId = array();
        $trabajadorEmpresa = $em->getRepository('App\Entity\TrabajadorEmpresa')->findBy(array('empresa' => $riesgoCausa->getEvaluacion()->getEmpresa(), 'anulado' => false));

        foreach ($trabajadorEmpresa as $ta) {
            if (!is_null($ta->getTrabajador())) {
                array_push($arrayTrabajadorId, $ta->getTrabajador()->getId());
            }
        }
        // Buscamos la planificacion
        $planificacion = $em->getRepository('App\Entity\PlanificacionRiesgoCausa')->findOneBy(array('riesgoCausa' => $riesgoCausa), array('fechaPrevista' => 'ASC'));
        $fechaPrevista = null;
        $fechaRealizacion = null;
        $coste = null;
        $responsable = null;
        $tipoPlanificacion = null;
        $trabajadoresSn = false;

        if (!is_null($planificacion)) {
            $fechaPrevista = $planificacion->getFechaPrevista();
            $fechaRealizacion = $planificacion->getFechaRealizacion();
            $coste = $planificacion->getCostePrevisto();
            $responsable = $planificacion->getResponsable();
            $tipoPlanificacion = $planificacion->getTipoPlanificacion();
            $trabajadoresSn = $planificacion->getTrabajadores();
        }
        $form = $this->createForm(RiesgoCausaType::class, $riesgoCausa, array('metodologia' => $metodologiaId, 'grupoRiesgoId' => $grupoRiesgoId, 'grupoRiesgoObj' => $grupoRiesgo, 'trabajadorId' => $arrayTrabajadorId, 'tipoPlanificacion' => $tipoPlanificacion, 'fechaPrevista' => $fechaPrevista, 'fechaRealizacion' => $fechaRealizacion, 'coste' => $coste, 'responsable' => $responsable, 'trabajadoresSn' => $trabajadoresSn));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $riesgoCausa = $form->getData();
            $em = $this->getDoctrine()->getManager();

            $riesgosCausasEvaluacion = $em->getRepository('App\Entity\RiesgoCausaEvaluacion')->findBy(array('evaluacion' => $riesgoCausa->getEvaluacion(), 'puestoTrabajo' => $riesgoCausa->getPuestoTrabajo(), 'zonaTrabajo' => $riesgoCausa->getZonaTrabajo(), 'anulado' => false));
            foreach ($riesgosCausasEvaluacion as $rce) {
                $rce->setUltimoModificado(false);
                $em->persist($rce);
                $em->flush();
            }
            // Marcamos el campo ultimo modificado a true
            $riesgoCausa->setUltimoModificado(true);

            $em->persist($riesgoCausa);
            $em->flush();

            // Comprobamos si tiene planificacion sino la creamos
            $fechaPrevista = $form["fechaPrevista"]->getData();
            $fechaRealizacion = $form["fechaRealizacion"]->getData();
            $coste = $form["costePrevisto"]->getData();
            $responsable = $form["responsable"]->getData();
            $tipoPlanificacion = $form["tipoPlanificacion"]->getData();
            $trabajadoresSn = $form["trabajadores"]->getData();

            $planificacionUpdate = $em->getRepository('App\Entity\PlanificacionRiesgoCausa')->findOneBy(array('riesgoCausa' => $riesgoCausa));
            if (!is_null($planificacionUpdate)) {
                $planificacionUpdate->setTipoPlanificacion($tipoPlanificacion);
                $planificacionUpdate->setFechaPrevista($fechaPrevista);
                $planificacionUpdate->setFechaRealizacion($fechaRealizacion);
                $planificacionUpdate->setCostePrevisto($coste);
                $planificacionUpdate->setResponsable($responsable);
                $planificacionUpdate->setTrabajadores($trabajadoresSn);
                $em->persist($planificacionUpdate);
                $em->flush();
            } else {
                $planificacionNew = new PlanificacionRiesgoCausa();
                $planificacionNew->setRiesgoCausa($riesgoCausa);
                $planificacionNew->setTipoPlanificacion($tipoPlanificacion);
                $planificacionNew->setFechaPrevista($fechaPrevista);
                $planificacionNew->setFechaRealizacion($fechaRealizacion);
                $planificacionNew->setCostePrevisto($coste);
                $planificacionNew->setResponsable($responsable);
                $planificacionNew->setTrabajadores($trabajadoresSn);
                $em->persist($planificacionNew);
                $em->flush();
            }
            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_update_riesgo_causa', array('id' => $riesgoCausa->getId()));
        }
        return $this->render('evaluacion/riesgoCausa.html.twig', array('form' => $form->createView(), 'imagenes' => $imagenes,  'accionPreventivaEmpresa' => $accionPreventivaEmpresa, 'accionPreventivaTrabajador' => $accionPreventivaTrabajador, 'metodologiaId' => $metodologiaId, 'fechaEvaluacion' => $riesgoCausa->getEvaluacion()->getFechaInicio()));
    }

    public function deleteRiesgoCausa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();

        $riesgoCausaId = $_REQUEST['riesgoCausaId'];
        $riesgoCausa = $em->getRepository('App\Entity\RiesgoCausaEvaluacion')->find($riesgoCausaId);

        $riesgoCausa->setAnulado(true);
        $em->persist($riesgoCausa);
        $em->flush();

        // Anulamos las acciones preventivas del riesgo
        $accionesPreventivasEmpresa = $em->getRepository('App\Entity\AccionPreventivaEmpresaRiesgoCausa')->findBy(array('riesgoCausa' => $riesgoCausa, 'anulado' => false));

        foreach ($accionesPreventivasEmpresa as $accionPreventivaEmpresa) {
            $accionPreventivaEmpresa->setAnulado(true);
            $em->persist($accionPreventivaEmpresa);
            $em->flush();
        }
        // Anulamos las acciones preventivas del riesgo
        $accionesPreventivasTrabajador = $em->getRepository('App\Entity\AccionPreventivaTrabajadorRiesgoCausa')->findBy(array('riesgoCausa' => $riesgoCausa, 'anulado' => false));

        foreach ($accionesPreventivasTrabajador as $accionPreventivaTrabajador) {
            $accionPreventivaTrabajador->setAnulado(true);
            $em->persist($accionPreventivaTrabajador);
            $em->flush();
        }
        $session->set('riesgoCausaId', null);

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function buscaValorRiesgo(Request $request)
    {
        $severidadId = $_REQUEST['severidadId'];
        $probabilidadId = $_REQUEST['probabilidadId'];

        $arrayValorRiesgo = array();
        $arrayValorRiesgo[3][3] = 1;
        $arrayValorRiesgo[3][2] = 2;
        $arrayValorRiesgo[3][1] = 3;
        $arrayValorRiesgo[2][3] = 2;
        $arrayValorRiesgo[2][2] = 3;
        $arrayValorRiesgo[2][1] = 4;
        $arrayValorRiesgo[1][3] = 3;
        $arrayValorRiesgo[1][2] = 4;
        $arrayValorRiesgo[1][1] = 5;

        $data = array(
            'valorRiesgo' => $arrayValorRiesgo[$severidadId][$probabilidadId]
        );
        return new JsonResponse($data);
    }

    public function createAccionPreventivaEmpresa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditEvaluacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $accionPreventivaEmpresa = new AccionPreventivaEmpresaRiesgoCausa();

        $riesgoCausaId = $session->get('riesgoCausaId');
        $riesgoCausa = $em->getRepository('App\Entity\RiesgoCausaEvaluacion')->find($riesgoCausaId);
        $causaId = $riesgoCausa->getCausa()->getId();

        // Buscamos las medidas preventivas de la causa
        $arrayPreventivaEmpresaId = array();

        $query = "select preventiva_empresa_id from preventiva_empresa_causa where causa_id = $causaId and anulado = false and preventiva_empresa_id not in (select preventiva_empresa_id from accion_preventiva_empresa_riesgo_causa where anulado = false and riesgo_causa_id = $riesgoCausaId)";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $preventivaEmpresaCausa = $stmt->fetchAll();
        foreach ($preventivaEmpresaCausa as $pec) {
            array_push($arrayPreventivaEmpresaId, $pec['preventiva_empresa_id']);
        }
        $form = $this->createForm(AccionPreventivaEmpresaType::class, $accionPreventivaEmpresa, array('preventivaEmpresaId' => $arrayPreventivaEmpresaId));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $accionPreventivaEmpresa = $form->getData();
            $newPreventEmpresa = new PreventivaEmpresa();
            // Si no es controla si te PreventivaEmpresa, pot duplicar el registre quan no en tingui, aixi si en te, no es crea repetit.
            if ($accionPreventivaEmpresa->getPreventivaEmpresa()) {
            } else {
                $newPreventEmpresa->setDescripcion($accionPreventivaEmpresa->getDescripcion());
                $newPreventEmpresa->setDescripcionEs($accionPreventivaEmpresa->getDescripcion());
                $newPreventEmpresa->setDescripcionCa($accionPreventivaEmpresa->getDescripcion());
                $em->persist($newPreventEmpresa);
                $em->flush();
                $accionPreventivaEmpresa->setPreventivaEmpresa($newPreventEmpresa);
            }
            $accionPreventivaEmpresa->setRiesgoCausa($riesgoCausa);
            $em->persist($accionPreventivaEmpresa);
            $em->flush();

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_update_accion_preventiva_empresa', array('id' => $accionPreventivaEmpresa->getId()));
        }
        return $this->render('evaluacion/accionPreventivaEmpresa.html.twig', array('form' => $form->createView()));
    }

    public function updateAccionPreventivaEmpresa(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditEvaluacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $accionPreventivaEmpresa = $em->getRepository('App\Entity\AccionPreventivaEmpresaRiesgoCausa')->find($id);
        $riesgoCausa = $accionPreventivaEmpresa->getRiesgoCausa();
        $riesgoCausaId = $riesgoCausa->getId();
        $causaId = $riesgoCausa->getCausa()->getId();

        // Buscamos las medidas preventivas de la causa
        $arrayPreventivaEmpresaId = array();

        $query = "select preventiva_empresa_id from preventiva_empresa_causa 
                  where causa_id = $causaId 
                  and anulado = false 
                  and preventiva_empresa_id not in (select preventiva_empresa_id from accion_preventiva_empresa_riesgo_causa where anulado = false and riesgo_causa_id = $riesgoCausaId)
                  union all 
                  select preventiva_empresa_id from accion_preventiva_empresa_riesgo_causa
                  where id = $id";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $preventivaEmpresaCausa = $stmt->fetchAll();
        foreach ($preventivaEmpresaCausa as $pec) {
            array_push($arrayPreventivaEmpresaId, $pec['preventiva_empresa_id']);
        }
        $form = $this->createForm(AccionPreventivaEmpresaType::class, $accionPreventivaEmpresa, array('preventivaEmpresaId' => $arrayPreventivaEmpresaId));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $accionPreventivaEmpresa = $form->getData();
            $em->persist($accionPreventivaEmpresa);
            $em->flush();

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_update_accion_preventiva_empresa', array('id' => $accionPreventivaEmpresa->getId()));
        }
        return $this->render('evaluacion/accionPreventivaEmpresa.html.twig', array('form' => $form->createView()));
    }

    public function deleteAccionPreventivaEmpresa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();

        $accionPreventivaEmpresaId = $_REQUEST['accionPreventivaEmpresaId'];
        $accionPreventivaEmpresa = $em->getRepository('App\Entity\AccionPreventivaEmpresaRiesgoCausa')->find($accionPreventivaEmpresaId);

        $accionPreventivaEmpresa->setAnulado(true);
        $em->persist($accionPreventivaEmpresa);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function addEpcEmpresa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $preventivaId = $_REQUEST['preventivaId'];
        $epcId = $_REQUEST['epcId'];

        $accionPreventivaEmpresa = $em->getRepository('App\Entity\AccionPreventivaEmpresaRiesgoCausa')->find($preventivaId);
        $epc = $em->getRepository('App\Entity\Epi')->find($epcId);

        $newEpcEmpresa = new EpiPreventivaEmpresa();
        $newEpcEmpresa->setEpi($epc);
        $newEpcEmpresa->setPreventivaEmpresa($accionPreventivaEmpresa);
        $em->persist($newEpcEmpresa);
        $em->flush();

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function deleteEpcEmpresa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $epcPreventivaEmpresaId = $_REQUEST['epcPreventivaEmpresaId'];

        $epcPreventivaEmpresa = $em->getRepository('App\Entity\EpiPreventivaEmpresa')->find($epcPreventivaEmpresaId);
        $epcPreventivaEmpresa->setAnulado(true);
        $em->persist($epcPreventivaEmpresa);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function createAccionPreventivaTrabajador(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditEvaluacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $accionPreventivaTrabajador = new AccionPreventivaTrabajadorRiesgoCausa();

        $riesgoCausaId = $session->get('riesgoCausaId');
        $riesgoCausa = $em->getRepository('App\Entity\RiesgoCausaEvaluacion')->find($riesgoCausaId);
        $causaId = $riesgoCausa->getCausa()->getId();

        // Buscamos las medidas preventivas de la causa
        $arrayPreventivaTrabajadorId = array();

        $query = "select preventiva_trabajador_id from preventiva_trabajador_causa where causa_id = $causaId and anulado = false and preventiva_trabajador_id not in (select preventiva_trabajador_id from accion_preventiva_trabajador_riesgo_causa where anulado = false and riesgo_causa_id = $riesgoCausaId)";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $preventivaTrabajadorCausa = $stmt->fetchAll();
        foreach ($preventivaTrabajadorCausa as $ptc) {
            array_push($arrayPreventivaTrabajadorId, $ptc['preventiva_trabajador_id']);
        }
        $form = $this->createForm(AccionPreventivaTrabajadorType::class, $accionPreventivaTrabajador, array('preventivaTrabajadorId' => $arrayPreventivaTrabajadorId));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $accionPreventivaTrabajador = $form->getData();
            $newPreventTrabaj = new PreventivaTrabajador();

            if ($accionPreventivaTrabajador->getPreventivaTrabajador()) {
            } else {
                $newPreventTrabaj->setDescripcion($accionPreventivaTrabajador->getDescripcion());
                $newPreventTrabaj->setDescripcionEs($accionPreventivaTrabajador->getDescripcion());
                $newPreventTrabaj->setDescripcionCa($accionPreventivaTrabajador->getDescripcion());
                $em->persist($newPreventTrabaj);
                $em->flush();
                $accionPreventivaTrabajador->setPreventivaTrabajador($newPreventTrabaj);
            }
            $accionPreventivaTrabajador->setRiesgoCausa($riesgoCausa);
            $em->persist($accionPreventivaTrabajador);
            $em->flush();

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_update_accion_preventiva_trabajador', array('id' => $accionPreventivaTrabajador->getId()));
        }
        return $this->render('evaluacion/accionPreventivaTrabajador.html.twig', array('form' => $form->createView(), 'epi' => null, 'listEpi' => null));
    }

    public function updateAccionPreventivaTrabajador(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditEvaluacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $accionPreventivaTrabajador = $em->getRepository('App\Entity\AccionPreventivaTrabajadorRiesgoCausa')->find($id);
        $riesgoCausa = $accionPreventivaTrabajador->getRiesgoCausa();
        $riesgoCausaId = $riesgoCausa->getId();
        $causaId = $riesgoCausa->getCausa()->getId();

        // Buscamos las medidas preventivas de la causa
        $arrayPreventivaTrabajadorId = array();

        $query = "select preventiva_trabajador_id from preventiva_trabajador_causa 
                  where causa_id = $causaId 
                  and anulado = false 
                  and preventiva_trabajador_id not in (select preventiva_trabajador_id from accion_preventiva_trabajador_riesgo_causa where anulado = false and riesgo_causa_id = $riesgoCausaId)
                  union all 
                  select preventiva_trabajador_id from accion_preventiva_trabajador_riesgo_causa
                  where id = $id";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $preventivaTrabajadorCausa = $stmt->fetchAll();
        foreach ($preventivaTrabajadorCausa as $ptc) {
            array_push($arrayPreventivaTrabajadorId, $ptc['preventiva_trabajador_id']);
        }
        $epi = $em->getRepository('App\Entity\EpiPreventivaTrabajador')->findBy(array('preventivaTrabajador' => $accionPreventivaTrabajador, 'anulado' => false));
        $listEpi = $em->getRepository('App\Entity\Epi')->findBy(array('trabajador' => true, 'anulado' => false));

        $form = $this->createForm(AccionPreventivaTrabajadorType::class, $accionPreventivaTrabajador, array('preventivaTrabajadorId' => $arrayPreventivaTrabajadorId));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $accionPreventivaTrabajador = $form->getData();

            $em->persist($accionPreventivaTrabajador);
            $em->flush();

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_update_accion_preventiva_trabajador', array('id' => $accionPreventivaTrabajador->getId()));
        }
        return $this->render('evaluacion/accionPreventivaTrabajador.html.twig', array('form' => $form->createView(), 'epi' => $epi, 'listEpi' => $listEpi));
    }

    public function deleteAccionPreventivaTrabajador(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $accionPreventivaTrabajadorId = $_REQUEST['accionPreventivaTrabajadorId'];
        $accionPreventivaTrabajador = $em->getRepository('App\Entity\AccionPreventivaTrabajadorRiesgoCausa')->find($accionPreventivaTrabajadorId);

        $accionPreventivaTrabajador->setAnulado(true);
        $em->persist($accionPreventivaTrabajador);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function addEpiTrabajador(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $preventivaId = $_REQUEST['preventivaId'];
        $epiId = $_REQUEST['epiId'];

        $accionPreventivaTrabajador = $em->getRepository('App\Entity\AccionPreventivaTrabajadorRiesgoCausa')->find($preventivaId);
        $epi = $em->getRepository('App\Entity\Epi')->find($epiId);

        $newEpiTrabajador = new EpiPreventivaTrabajador();
        $newEpiTrabajador->setEpi($epi);
        $newEpiTrabajador->setPreventivaTrabajador($accionPreventivaTrabajador);
        $em->persist($newEpiTrabajador);
        $em->flush();

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function deleteEpiTrabajador(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $epiPreventivaTrabajadorId = $_REQUEST['epiPreventivaTrabajadorId'];

        $epiPreventivaTrabajador = $em->getRepository('App\Entity\EpiPreventivaTrabajador')->find($epiPreventivaTrabajadorId);
        $epiPreventivaTrabajador->setAnulado(true);
        $em->persist($epiPreventivaTrabajador);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function addGrupoNormativa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $evaluacionId = $_REQUEST['evaluacionId'];
        $grupoNormativaId = $_REQUEST['grupoNormativaId'];

        $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($evaluacionId);

        foreach ($grupoNormativaId as $gnId) {
            $grupoNormativa = $em->getRepository('App\Entity\GrupoNormativa')->find($gnId);

            $newNormativaEvaluacion = new GrupoNormativaEvaluacion();
            $newNormativaEvaluacion->setEvaluacion($evaluacion);
            $newNormativaEvaluacion->setGrupoNormativa($grupoNormativa);
            $newNormativaEvaluacion->setAnulado(false);
            $em->persist($newNormativaEvaluacion);
            $em->flush();
        }
        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function deleteGrupoNormativa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $grupoNormativaEvaluacionId = $_REQUEST['grupoNormativaEvaluacionId'];

        $grupoNormativaEvaluacion = $em->getRepository('App\Entity\GrupoNormativaEvaluacion')->find($grupoNormativaEvaluacionId);
        $grupoNormativaEvaluacion->setAnulado(true);
        $em->persist($grupoNormativaEvaluacion);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function createVisita(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $evaluacionId = $_REQUEST['evaluacionId'];
        $fecha = $_REQUEST['fecha'];
        $hinicio = $_REQUEST['inicio'];
        $hfin = $_REQUEST['fin'];
        $tecnicoId = $_REQUEST['tecnico'];

        $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($evaluacionId);

        $visitaEvaluacion = new VisitaEvaluacion();

        if ($fecha != "") {
            $visitaEvaluacion->setDtVisita(new \DateTime($fecha));
        }
        if ($hinicio != "") {
            $visitaEvaluacion->setHinicio(new \DateTime($hinicio));
        }
        if ($hfin != "") {
            $visitaEvaluacion->setHfin(new \DateTime($hfin));
        }
        $visitaEvaluacion->setEvaluacion($evaluacion);

        if ($tecnicoId != "") {
            $tecnico = $em->getRepository('App\Entity\UsuarioTecnico')->find($tecnicoId);
            $visitaEvaluacion->setTecnico($tecnico);
        }
        $em->persist($visitaEvaluacion);
        $em->flush();

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function deleteVisita(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $visitaEvaluacionId = $_REQUEST['visitaEvaluacionId'];

        $visitaEvaluacion = $em->getRepository('App\Entity\VisitaEvaluacion')->find($visitaEvaluacionId);
        $visitaEvaluacion->setAnulado(true);
        $em->persist($visitaEvaluacion);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function addImgRiesgoCausa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $riesgoCausaId = $session->get('riesgoCausaId');
        $riesgoCausa = $em->getRepository('App\Entity\RiesgoCausaEvaluacion')->find($riesgoCausaId);

        // Obtenemos los datos del archivo
        $filename = $_FILES['file']['name'];
        move_uploaded_file($_FILES["file"]["tmp_name"], "upload/media/evaluaciones/causas/$filename");
        $path_info = pathinfo("upload/media/evaluaciones/causas/$filename");
        $extension = $path_info['extension'];

        $extensionesValidas = array("png", "PNG", "jpg", "jpeg", "JPG");
        if (in_array($extension, $extensionesValidas)) {
            // Generamos un nombre aleatorio
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 20; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            $randomName = $randomString . '.' . $extension;
            rename("upload/media/evaluaciones/causas/$filename", "upload/media/evaluaciones/causas/$randomName");

            $riesgoCausaImg = new RiesgoCausaImg();
            $riesgoCausaImg->setNombre($randomName);
            $riesgoCausaImg->setNombreOriginal($filename);
            $riesgoCausaImg->setRiesgoCausa($riesgoCausa);
            $riesgoCausaImg->setAnulado(false);
            $em->persist($riesgoCausaImg);
            $em->flush();

            $this->get('session')->getFlashBag()->clear();
            $traduccion = $translator->trans('TRANS_UPLOAD_IMG');
            $this->addFlash('success', $traduccion);
        }
        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function deleteImgRiesgoCausa(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $riesgoCausaImgId = $_REQUEST['riesgoCausaImgId'];
        $riesgoCausaImg = $em->getRepository('App\Entity\RiesgoCausaImg')->find($riesgoCausaImgId);
        $riesgoCausaImg->setAnulado(true);
        $em->persist($riesgoCausaImg);
        $em->flush();

        // Eliminamos la imagen
        $nombre = $riesgoCausaImg->getNombre();
        unlink('upload/media/evaluaciones/causas/' . $nombre);

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function downImgRiesgoCausa(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $riesgoCausaImg = $em->getRepository('App\Entity\RiesgoCausaImg')->find($id);

        $nombre = $riesgoCausaImg->getNombre();
        $nombreOriginal = $riesgoCausaImg->getNombreOriginal();
        $ruta = 'upload/media/evaluaciones/causas/' . $nombre;
        $file = file_get_contents($ruta, true);

        $response = new Response($file);

        // Create the disposition of the file
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $nombreOriginal
        );

        // Set the content disposition
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    public function addMaquinaEmpresaTrabajador(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $puestoTrabajoEvaluacionId = $_REQUEST['puestoTrabajoEvaluacionId'];
        $puestoTrabajoEvaluacion = $em->getRepository('App\Entity\PuestoTrabajoEvaluacion')->find($puestoTrabajoEvaluacionId);
        $puestoTrabajo = $puestoTrabajoEvaluacion->getPuestoTrabajo();
        $empresa = $puestoTrabajoEvaluacion->getEvaluacion()->getEmpresa();

        $maquinaEmpresaTrabajador = new MaquinaEmpresaTrabajador();

        $maquinaManual = $_REQUEST['maquinaManual'];
        if ($maquinaManual != "") {
            $maquinaEmpresa = new MaquinaEmpresa();
            $maquinaEmpresa->setEmpresa($empresa);
            $maquinaEmpresa->setAnulado(false);
            $maquinaEmpresa->setDescripcion($maquinaManual);
            $em->persist($maquinaEmpresa);
            $em->flush();

            $maquinaEmpresaTrabajador->setMaquinaEmpresa($maquinaEmpresa);
        }
        //<!--Peticio 01/09/2023-->
        $maquinaEmpresaId = $maquinaEmpresaTrabajador->getMaquinaEmpresa()->getId();
        //$maquinaEmpresaId = $_REQUEST['maquinaEmpresaId'];
        if ($maquinaEmpresaId != "") {
            $maquinaEmpresaAux = $em->getRepository('App\Entity\MaquinaEmpresa')->find($_REQUEST['maquinaEmpresaId']);
            $maquinaEmpresa = $em->getRepository('App\Entity\MaquinaEmpresa')->find($maquinaEmpresaId);
            $maquinaEmpresa->setCentro($maquinaEmpresaAux->getCentro());
            $maquinaEmpresa->setDescripcion($maquinaEmpresaAux->getDescripcion());
            $maquinaEmpresa->setCodigo($maquinaEmpresaAux->getCodigo());
            $maquinaEmpresa->setFabricante($maquinaEmpresaAux->getFabricante());
            $maquinaEmpresa->setModelo($maquinaEmpresaAux->getModelo());
            $maquinaEmpresa->setNumSerie($maquinaEmpresaAux->getNumSerie());
            $maquinaEmpresa->setAnyoFabricacion($maquinaEmpresaAux->getAnyoFabricacion());
            $maquinaEmpresa->setNumSerie($maquinaEmpresaAux->getNumSerie());
            $maquinaEmpresa->setAnyoFabricacion($maquinaEmpresaAux->getAnyoFabricacion());
            $maquinaEmpresa->setAnyoCompra($maquinaEmpresaAux->getAnyoCompra());
            $maquinaEmpresa->setPlacaCaracteristica($maquinaEmpresaAux->getPlacaCaracteristica());
            $maquinaEmpresa->setMarcadoCE($maquinaEmpresaAux->getMarcadoCE());
            $maquinaEmpresa->setObservaciones($maquinaEmpresaAux->getObservaciones());
            $maquinaEmpresa->setConformidad($maquinaEmpresaAux->isConformidad());
            $maquinaEmpresa->setManualInstrucciones($maquinaEmpresaAux->isManualInstrucciones());
            $maquinaEmpresaTrabajador->setMaquinaEmpresa($maquinaEmpresa);
        }
        $maquinaEmpresaTrabajador->setPuestoTrabajo($puestoTrabajo);
        $maquinaEmpresaTrabajador->setTrabajador(null);
        $em->persist($maquinaEmpresaTrabajador);
        $em->flush();

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function deleteMaquinaEmpresaTrabajador(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $maquinaEmpresaTrabajadorId = $_REQUEST['maquinaEmpresaTrabajadorId'];
        $maquinaEmpresaTrabajador = $em->getRepository('App\Entity\MaquinaEmpresaTrabajador')->find($maquinaEmpresaTrabajadorId);
        $maquinaEmpresaTrabajador->setAnulado(true);
        $em->persist($maquinaEmpresaTrabajador);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }
    public function deleteQuimicosTrabajador2(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $_REQUEST['id'];
        $maquinaEmpresaTrabajador = $em->getRepository('App\Entity\PuestoTrabajoContaminante')->find($id);
        $maquinaEmpresaTrabajador->setAnulado(true);
        $em->persist($maquinaEmpresaTrabajador);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success', $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function createProductoQuimico(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditEvaluacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $productoQuimico = new PuestoTrabajoContaminante();
        $grupoContaminante = $em->getRepository('App\Entity\GrupoContaminante')->find(4577);

        $form = $this->createForm(PuestoTrabajoContaminanteType::class, $productoQuimico);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $productoQuimico = $form->getData();

            $puestoTrabajoEvaluacionId = $session->get('puestoTrabajoEvaluacionId');

            if (!is_null($puestoTrabajoEvaluacionId)) {
                $puestoTrabajoEvaluacion = $em->getRepository('App\Entity\PuestoTrabajoEvaluacion')->find($puestoTrabajoEvaluacionId);
                $productoQuimico->setPuestoTrabajo($puestoTrabajoEvaluacion->getPuestoTrabajo());
            }
            $productoQuimico->setGrupoContaminante($grupoContaminante);
            $em->persist($productoQuimico);
            $em->flush();

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_update_producto_quimico_puesto_trabajo', array('id' => $productoQuimico->getId()));
        }
        return $this->render('evaluacion/productoQuimico.html.twig', array('form' => $form->createView()));
    }

    public function updateProductoQuimico(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditEvaluacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $productoQuimico = $em->getRepository('App\Entity\PuestoTrabajoContaminante')->find($id);

        $form = $this->createForm(PuestoTrabajoContaminanteType::class, $productoQuimico);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $productoQuimico = $form->getData();
            $em->persist($productoQuimico);
            $em->flush();

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_update_producto_quimico_puesto_trabajo', array('id' => $productoQuimico->getId()));
        }
        return $this->render('evaluacion/productoQuimico.html.twig', array('form' => $form->createView()));
    }

    public function deleteProductoQuimico(Request $request, $id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if (!is_null($privilegios)) {
            if (!$privilegios->getEditEvaluacionSn()) {
                return $this->redirectToRoute('error_403');
            }
        }
        $productoQuimico = $em->getRepository('App\Entity\PuestoTrabajoContaminante')->find($id);
        $productoQuimico->setAnulado(true);
        $em->persist($productoQuimico);
        $em->flush();

        $puestoTrabajoEvaluacionId = $session->get('puestoTrabajoEvaluacionId');

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        return $this->redirectToRoute('tecnico_evaluaciones_evaluacion_puesto_trabajo_evaluar', array('id' => $puestoTrabajoEvaluacionId));
    }

    public function buscaRiesgos(Request $request)
    {
        $grupoRiesgoId = $_REQUEST['grupoRiesgoId'];

        $query = "select a.id, concat(a.codigo,' - ', a.descripcion) as descripcion from riesgo a inner join grupo_riesgo b on a.grupo_riesgo_id = b.id where a.anulado = false and b.anulado = false and a.grupo_riesgo_id = $grupoRiesgoId order by a.codigo ASC";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $riesgos = $stmt->fetchAll();

        return new JsonResponse(json_encode($riesgos));
    }

    public function buscaCausas(Request $request)
    {
        $riesgoId = $_REQUEST['riesgoId'];
        //$grupoRiesgoId = $_REQUEST['grupoRiesgoId'];

        $query = "select a.id, a.descripcion from causa a inner join riesgo b on a.riesgo_id = b.id where a.anulado = false and b.anulado = false and a.riesgo_id = $riesgoId order by a.descripcion asc";
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $causas = $stmt->fetchAll();

        return new JsonResponse(json_encode($causas));
    }

    public function cargarCentrosAction(Request $request)
    {
        $miParametro = $_REQUEST['plantillaId'];
        $query = "SELECT ptc.*, pte.* FROM puesto_trabajo_centro ptc 
              INNER JOIN puesto_trabajo_evaluacion pte ON ptc.id = pte.puesto_trabajo_id
              WHERE pte.evaluacion_id = $miParametro and ptc.anulado = false and pte.anulado = false ORDER BY ptc.descripcion ASC";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $centros = $stmt->fetchAll();

        // Devuelve los centros como JSON
        return new JsonResponse($centros);
    }

    public function buscaPlantillaEvaluacionSoloCentroTrabajo(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $_REQUEST['id'];

        // Buscamos la evaluacion y sus centros asignados
        $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($id);
        $centros = $em->getRepository('App\Entity\EvaluacionCentroTrabajo')->findBy(array('evaluacion' => $evaluacion));
        $count = count($centros);

        switch ($count) {
            case 0:
                //Peticio 28/07/2023
                $empresa = $evaluacion->getEmpresa();
                if ($empresa->getCentroTrabajoDeslocalizado()) {
                    $query = "select id from gdoc_plantillas where anulado = false and nombre_completo like '%EVALUACION SIN CENTRO SOLO CENTRO%'";
                } else {
                    if ($empresa->getCentroTrabajoDeslocalizadoConstruccio()) {
                        $query = "select id from gdoc_plantillas where anulado = false and nombre_completo like '%EVALUACIÓN SIN CENTRO CONSTRUCCION SOLO CENTRO%'";
                    } else {
                        $query = "select id from gdoc_plantillas where anulado = false and nombre_completo like '%EVALUACION SIN CENTRO SOLO CENTRO%'";
                    }
                }
                break;
            case 1:
                //Comprobamos si el centro esta deslocalizado
                $queryDeslocalizado = "select b.id from evaluacion_centro_trabajo a inner join centro b on a.centro_id = b.id where a.evaluacion_id = $id and lower(b.nombre) like '%deslocalizado%'";
                $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($queryDeslocalizado);
                $stmt->execute();
                $deslocalizado = $stmt->fetchAll();

                if (count($deslocalizado) == 0) {
                    $query = "select id from gdoc_plantillas where anulado = false and nombre_completo like '%EVALUACION 1 CENTRO SOLO CENTRO%'";
                } else {
                    $query = "select id from gdoc_plantillas where anulado = false and nombre_completo like '%EVALUACION SIN CENTRO SOLO CENTRO%'";
                }
                break;
            case 2:
                $query = "select id from gdoc_plantillas where anulado = false and nombre_completo like '%EVALUACION 2 CENTROS SOLO CENTRO%'";
                break;
            default:
                $query = "select id from gdoc_plantillas where anulado = false and nombre_completo like '%EVALUACION MULTI CENTRO SOLO CENTRO%'";
                break;
        }
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $plantilla = $stmt->fetchAll();
        $plantillaId = $plantilla[0]['id'];

        if (!is_null($plantillaId)) {
            $success = true;
        } else {
            $success = false;
            $traduccion = $translator->trans('TRANS_ERROR_PRINT_EVALUACION');
            $this->addFlash('danger', $traduccion);
        }
        $data = array('success' => $success, 'plantillaId' => $plantillaId);

        return new JsonResponse($data);
    }

    public function buscaPlantillaEvaluacion(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $_REQUEST['id'];

        // Buscamos la evaluacion y sus centros asignados
        $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($id);
        $centros = $em->getRepository('App\Entity\EvaluacionCentroTrabajo')->findBy(array('evaluacion' => $evaluacion));
        $count = count($centros);

        switch ($count) {
            case 0:
                //Peticio 28/07/2023
                $empresa = $evaluacion->getEmpresa();
                if ($empresa->getCentroTrabajoDeslocalizado()) {
                    $query = "select id from gdoc_plantillas where anulado = false and nombre_completo like '%EVALUACION SIN CENTRO%'";
                } else {
                    if ($empresa->getCentroTrabajoDeslocalizadoConstruccio()) {
                        $query = "select id from gdoc_plantillas where anulado = false and nombre_completo like '%EVALUACIÓN SIN CENTRO CONSTRUCCION%'";
                    } else {
                        $query = "select id from gdoc_plantillas where anulado = false and nombre_completo like '%EVALUACION SIN CENTRO%'";
                    }
                }
                break;
            case 1:
                //Comprobamos si el centro esta deslocalizado
                //$queryDeslocalizado = "select b.id from evaluacion_centro_trabajo a inner join centro b on a.centro_id = b.id where a.evaluacion_id = $id and lower(b.nombre) like '%deslocalizado%'";
                //$stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($queryDeslocalizado);
                //$stmt->execute();
                //$deslocalizado = $stmt->fetchAll();
                $empresa = $evaluacion->getEmpresa();
                if ($empresa->getCentroTrabajoDeslocalizado()) {
                    $query = "select id from gdoc_plantillas where anulado = false and nombre_completo like '%EVALUACION SIN CENTRO%'";
                } else {
                    $query = "select id from gdoc_plantillas where anulado = false and nombre_completo like '%EVALUACION 1 CENTRO%'";
                }
                break;
            case 2:
                $query = "select id from gdoc_plantillas where anulado = false and nombre_completo like '%EVALUACION 2 CENTROS%'";
                break;
            default:
                $query = "select id from gdoc_plantillas where anulado = false and nombre_completo like '%EVALUACION MULTI CENTRO%'";
                break;
        }
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $plantilla = $stmt->fetchAll();
        $plantillaId = $plantilla[0]['id'];

        if (!is_null($plantillaId)) {
            $success = true;
        } else {
            $success = false;
            $traduccion = $translator->trans('TRANS_ERROR_PRINT_EVALUACION');
            $this->addFlash('danger', $traduccion);
        }
        $data = array('success' => $success, 'plantillaId' => $plantillaId);

        return new JsonResponse($data);
    }

    // Import quimicos David Gil
    public function importDataFromXLSXQuimicos(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $contenido = $data['contenido'];
        $puestoId = $data['puesto'];
        $em = $this->getDoctrine()->getManager();

        // Obtén la primera fila con los nombres de las columnas
        $columnNames = $contenido[0];

        $nombreIndex = array_search('Nombre', $columnNames);
        $casIndex = array_search('CAS', $columnNames);
        $episIndex = array_search('EPIS', $columnNames);
        $composicionIndex = array_search('Composición', $columnNames);
        $grupoContaminanteAux = $em->getRepository('App\Entity\GrupoContaminante')->find(4577);
        $puesto = $em->getRepository('App\Entity\PuestoTrabajoCentro')->find($puestoId);
        if (
            $nombreIndex !== false && $casIndex !== false && $episIndex !== false &&
            $composicionIndex !== false
        ) {
            // Recorremos las filas a partir de la segunda fila, ya que la primera fila tiene los nombres de las columnas
            for ($i = 1; $i < count($contenido); $i++) {
                $row = $contenido[$i];

                // Asegúrate de que todas las filas tengan las mismas columnas
                $nombre = isset($row[$nombreIndex]) ? $row[$nombreIndex] : null;
                $cas = isset($row[$casIndex]) ? $row[$casIndex] : null;
                $epis = isset($row[$episIndex]) ? $row[$episIndex] : null;
                $composicion = isset($row[$composicionIndex]) ? $row[$composicionIndex] : null;

                // Luego, puedes hacer lo que necesites con estas columnas, como guardarlas en la base de datos
                $quimicoPuesto = new PuestoTrabajoContaminante();
                $quimicoPuesto->setNombre($nombre);
                $quimicoPuesto->setCas($cas);
                $quimicoPuesto->setEpis($epis);
                $quimicoPuesto->setComposicion($composicion);
                $quimicoPuesto->setPuestoTrabajo($puesto);
                $quimicoPuesto->setGrupoContaminante($grupoContaminanteAux);
                $em->persist($quimicoPuesto);
            }
            $em->flush();
            return new JsonResponse(['message' => 'Los datos se han importado correctamente.']);
        } else {
            return new JsonResponse(['message' => 'Nombres de columna no encontrados en los datos.']);
        }
    }

    // Importd de maquinaria empresa Peticio David Gil
    public function importDataFromXLSX(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $contenido = $data['contenido'];
        $empresaId = $data['empresa'];
        $em = $this->getDoctrine()->getManager();

        // Obtén la primera fila con los nombres de las columnas
        $columnNames = $contenido[0];

        $descripcionIndex = array_search('Descripción', $columnNames);
        $codigoIndex = array_search('Codigo', $columnNames);
        $fabricanteIndex = array_search('Fabricante', $columnNames);
        $modeloIndex = array_search('Modelo', $columnNames);
        $numSerieIndex = array_search('Número de serie', $columnNames);
        $añoFabIndex = array_search('Año de fabricación', $columnNames);
        $añoCompraIndex = array_search('Año de compra', $columnNames);
        $cEIndex = array_search('Marcado CE', $columnNames);
        $observacionesIndex = array_search('Observaciones', $columnNames);
        $empresa = $em->getRepository('App\Entity\Empresa')->find($empresaId);
        if (
            $codigoIndex !== false && $fabricanteIndex !== false && $modeloIndex !== false &&
            $numSerieIndex !== false && $añoFabIndex !== false && $añoCompraIndex !== false &&
            $cEIndex !== false && $observacionesIndex !== false && $descripcionIndex !== false
        ) {
            // Recorremos las filas a partir de la segunda fila, ya que la primera fila tiene los nombres de las columnas
            for ($i = 1; $i < count($contenido); $i++) {
                $row = $contenido[$i];

                // Asegúrate de que todas las filas tengan las mismas columnas
                $descripcion = isset($row[$descripcionIndex]) ? $row[$descripcionIndex] : null;
                $codigo = isset($row[$codigoIndex]) ? $row[$codigoIndex] : null;
                $fabricante = isset($row[$fabricanteIndex]) ? $row[$fabricanteIndex] : null;
                $modelo = isset($row[$modeloIndex]) ? $row[$modeloIndex] : null;
                $numSerie = isset($row[$numSerieIndex]) ? $row[$numSerieIndex] : null;
                $añoFab = isset($row[$añoFabIndex]) ? $row[$añoFabIndex] : null;
                $añoCompra = isset($row[$añoCompraIndex]) ? $row[$añoCompraIndex] : null;
                $cE = isset($row[$cEIndex]) ? $row[$cEIndex] : null;
                $observaciones = isset($row[$observacionesIndex]) ? $row[$observacionesIndex] : null;

                // Luego, puedes hacer lo que necesites con estas columnas, como guardarlas en la base de datos
                $maquinaEmpresa = new MaquinaEmpresa();
                $maquinaEmpresa->setCodigo($codigo);
                $maquinaEmpresa->setFabricante($fabricante);
                $maquinaEmpresa->setModelo($modelo);
                $maquinaEmpresa->setNumSerie($numSerie);
                $maquinaEmpresa->setAnyoFabricacion($añoFab);
                $maquinaEmpresa->setAnyoCompra($añoCompra);
                $maquinaEmpresa->setMarcadoCE($cE);
                $maquinaEmpresa->setObservaciones($observaciones);
                $maquinaEmpresa->setDescripcion($descripcion);
                $maquinaEmpresa->setEmpresa($empresa);
                $em->persist($maquinaEmpresa);
            }
            $em->flush();
            return new JsonResponse(['message' => 'Los datos se han importado correctamente.']);
        } else {
            return new JsonResponse(['message' => 'Nombres de columna no encontrados en los datos.']);
        }
    }

    // Peticio David Gil
    public function selectRiesgoCausas(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        // Realiza una consulta o procesamiento para obtener los datos de riesgos y causas
        // Buscamos los riesgos/causas/medidas que se tienen que copiar
        $puestoTrabajoEvaluacionId = $_REQUEST['selectedOption'];
        $puestoTrabajoEvaluacionOrigen = $em->getRepository('App\Entity\PuestoTrabajoEvaluacion')->find($puestoTrabajoEvaluacionId);
        // Buscamos los riesgos/causas del puesto de trabajo de origen
        $riesgoCausaEvaluacion = $em->getRepository('App\Entity\RiesgoCausaEvaluacion')->findBy(array('anulado' => false, 'evaluacion' => $puestoTrabajoEvaluacionOrigen->getEvaluacion(), 'puestoTrabajo' => $puestoTrabajoEvaluacionOrigen->getPuestoTrabajo()));

        $query = "select a.id, c.codigo || ' - ' || c.descripcion || ' - ' || e.descripcion || ' - ' || d.descripcion as causariesgovalor 
           from riesgo_causa_evaluacion a
           left join riesgo c on a.riesgo_id = c.id
           left join valor_riesgo d on a.valor_riesgo_id = d.id 
           left join causa e on a.causa_id = e.id
           where a.evaluacion_id = :evaluacionId
           and a.puesto_trabajo_id = :puestoTrabajoId
           and a.anulado = false
           order by c.codigo asc";

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute([
            'evaluacionId' => $puestoTrabajoEvaluacionOrigen->getEvaluacion()->getId(),
            'puestoTrabajoId' => $puestoTrabajoEvaluacionOrigen->getPuestoTrabajo()->getId(),
        ]);
        $riesgosCausasPuestoTrabajo = $stmt->fetchAll();
        $datosSelect2 = [];

        // Llena $datosSelect2 con los datos adecuados (id y text) para el Select2
        foreach ($riesgosCausasPuestoTrabajo as $item) {
            $datosSelect2[] = [
                'id' => $item['id'],
                'text' => $item['causariesgovalor']
            ];
        }
        return new JsonResponse($datosSelect2);
    }

    public function copiarRiesgosCausasMedidas(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        // Buscamos la evaluacion
        $evaluacionId = $_REQUEST['evaluacionId'];
        $seleccion = $_REQUEST['seleccion'];

        $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($evaluacionId);

        // Buscamos el puesto de trabajo de destino
        $puestoTrabajoDestinoId = $_REQUEST['puestoTrabajoEvaluarId'];
        $puestoTrabajo = $em->getRepository('App\Entity\PuestoTrabajoCentro')->find($puestoTrabajoDestinoId);

        // Buscamos los riesgos/causas/medidas que se tienen que copiar
        $puestoTrabajoEvaluacionId = $_REQUEST['puestoTrabajoEvaluacionId'];
        $puestoTrabajoEvaluacionOrigen = $em->getRepository('App\Entity\PuestoTrabajoEvaluacion')->find($puestoTrabajoEvaluacionId);

        $em->beginTransaction();

        try {
            // Comprobamos si el puesto de trabajo ya esta creado
            $puestoTrabajoEvaluacion = $em->getRepository('App\Entity\PuestoTrabajoEvaluacion')->findOneBy(array('puestoTrabajo' => $puestoTrabajo, 'anulado' => false, 'evaluacion' => $evaluacion));

            if (is_null($puestoTrabajoEvaluacion)) {
                // Copiamos los datos
                $puestoTrabajoEvaluacionNew = clone $puestoTrabajoEvaluacionOrigen;
                $puestoTrabajoEvaluacionNew->setEvaluacion($evaluacion);
                $puestoTrabajoEvaluacionNew->setTrabajadores(null);
                $puestoTrabajoEvaluacionNew->setMotivoEvaluacion(null);
                $puestoTrabajoEvaluacionNew->setTarea(null);
                $puestoTrabajoEvaluacionNew->setPuestoTrabajo($puestoTrabajo);
                $em->persist($puestoTrabajoEvaluacionNew);
                $em->flush();
            }
            // Buscamos los productos quimicos
            $productosQuimicos = $this->getDoctrine()->getRepository('App\Entity\PuestoTrabajoContaminante')->findBy(array('puestoTrabajo' => $puestoTrabajoEvaluacionOrigen->getPuestoTrabajo(), 'anulado' => false));

            foreach ($productosQuimicos as $pq) {
                $productosQuimicosNew = clone $pq;
                $productosQuimicosNew->setPuestoTrabajo($puestoTrabajo);
                $em->persist($productosQuimicosNew);
                $em->flush();
            }
            $cadena = implode(',', $seleccion);
            $ids = explode(',', $cadena);
            if ($seleccion[0] === "0") {
                //Buscamos los riesgos/causas del puesto de trabajo de origen
                $riesgoCausaEvaluacion = $em->getRepository('App\Entity\RiesgoCausaEvaluacion')->findBy(array('anulado' => false, 'evaluacion' => $puestoTrabajoEvaluacionOrigen->getEvaluacion(), 'puestoTrabajo' => $puestoTrabajoEvaluacionOrigen->getPuestoTrabajo()));
            } else {
                $riesgoCausaEvaluacion = $em->getRepository('App\Entity\RiesgoCausaEvaluacion')
                    ->createQueryBuilder('rce')
                    ->where('rce.anulado = false')
                    ->andWhere('rce.id IN (:ids)')
                    ->setParameter('ids', $ids)
                    ->getQuery()
                    ->getResult();
            }
            foreach ($riesgoCausaEvaluacion as $rce) {
                $riesgoCausaEvaluacionNew = clone $rce;
                $riesgoCausaEvaluacionNew->setEvaluacion($evaluacion);
                $riesgoCausaEvaluacionNew->setPuestoTrabajo($puestoTrabajo);
                $riesgoCausaEvaluacionNew->setFinalizado(false);
                $riesgoCausaEvaluacionNew->setUltimoModificado(false);
                $em->persist($riesgoCausaEvaluacionNew);
                $em->flush();

                // Buscamos la planificacion
                $planificacionEmpresa = $em->getRepository('App\Entity\PlanificacionRiesgoCausa')->findBy(array('riesgoCausa' => $rce));
                foreach ($planificacionEmpresa as $pe) {
                    $planificacionEmpresaNew = clone $pe;
                    $planificacionEmpresaNew->setRiesgoCausa($riesgoCausaEvaluacionNew);
                    $planificacionEmpresaNew->setResponsable($evaluacion->getEmpresa()->getNombreRepresentante());
                    $em->persist($planificacionEmpresaNew);
                    $em->flush();
                }
                // Buscamos las medidas preventivas de la empresa
                $accionPreventivaEmpresa = $em->getRepository('App\Entity\AccionPreventivaEmpresaRiesgoCausa')->findBy(array('anulado' => false, 'riesgoCausa' => $rce));
                foreach ($accionPreventivaEmpresa as $ape) {
                    $accionPreventivaEmpresaNew = clone $ape;
                    $accionPreventivaEmpresaNew->setRiesgoCausa($riesgoCausaEvaluacionNew);
                    $em->persist($accionPreventivaEmpresaNew);
                    $em->flush();
                }
                // Buscamos las medidas preventivas del trabajador
                $accionPreventivaTrabajador = $em->getRepository('App\Entity\AccionPreventivaTrabajadorRiesgoCausa')->findBy(array('anulado' => false, 'riesgoCausa' => $rce));
                foreach ($accionPreventivaTrabajador as $apt) {
                    $accionPreventivaTrabajadorNew = clone $apt;
                    $accionPreventivaTrabajadorNew->setRiesgoCausa($riesgoCausaEvaluacionNew);
                    $em->persist($accionPreventivaTrabajadorNew);
                    $em->flush();
                }
            }
        } catch (\Exception $e) {
            $em->rollBack();
            throw $e;
        }
        $em->commit();

        $traduccion = $translator->trans('TRANS_COPY_OK');
        $this->addFlash('success',  $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function copiarRiesgosCausasMedidasZonaTrabajo(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        // Buscamos la evaluacion
        $evaluacionId = $_REQUEST['evaluacionId'];
        $evaluacion = $em->getRepository('App\Entity\Evaluacion')->find($evaluacionId);

        // Buscamos el puesto de trabajo de destino
        $zonaTrabajoDestinoId = $_REQUEST['zonaTrabajoEvaluarId'];
        $zonaTrabajo = $em->getRepository('App\Entity\ZonaTrabajo')->find($zonaTrabajoDestinoId);

        // Buscamos los riesgos/causas/medidas que se tienen que copiar
        $zonaTrabajoEvaluacionId = $_REQUEST['zonaTrabajoEvaluacionId'];
        $zonaTrabajoEvaluacionOrigen = $em->getRepository('App\Entity\ZonaTrabajoEvaluacion')->find($zonaTrabajoEvaluacionId);

        $em->beginTransaction();

        try {
            // Comprobamos si el puesto de trabajo ya esta creado
            $zonaTrabajoEvaluacion = $em->getRepository('App\Entity\ZonaTrabajoEvaluacion')->findOneBy(array('zonaTrabajo' => $zonaTrabajo, 'anulado' => false, 'evaluacion' => $evaluacion));

            if (is_null($zonaTrabajoEvaluacion)) {
                // Copiamos los datos
                $zonaTrabajoEvaluacionNew = clone $zonaTrabajoEvaluacionOrigen;
                $zonaTrabajoEvaluacionNew->setEvaluacion($evaluacion);
                $zonaTrabajoEvaluacionNew->setTrabajadores(null);
                $zonaTrabajoEvaluacionNew->setMotivoEvaluacion(null);
                $zonaTrabajoEvaluacionNew->setZonaTrabajo($zonaTrabajo);
                $em->persist($zonaTrabajoEvaluacionNew);
                $em->flush();
            }
            // Buscamos los riesgos/causas del puesto de trabajo de origen
            $riesgoCausaEvaluacion = $em->getRepository('App\Entity\RiesgoCausaEvaluacion')->findBy(array('anulado' => false, 'evaluacion' => $zonaTrabajoEvaluacionOrigen->getEvaluacion(), 'zonaTrabajo' => $zonaTrabajoEvaluacionOrigen->getZonaTrabajo()));

            foreach ($riesgoCausaEvaluacion as $rce) {
                $riesgoCausaEvaluacionNew = clone $rce;
                $riesgoCausaEvaluacionNew->setEvaluacion($evaluacion);
                $riesgoCausaEvaluacionNew->setZonaTrabajo($zonaTrabajo);
                $em->persist($riesgoCausaEvaluacionNew);
                $em->flush();

                // Buscamos la planificacion
                $planificacionEmpresa = $em->getRepository('App\Entity\PlanificacionRiesgoCausa')->findBy(array('riesgoCausa' => $rce));
                foreach ($planificacionEmpresa as $pe) {
                    $planificacionEmpresaNew = clone $pe;
                    $planificacionEmpresaNew->setRiesgoCausa($riesgoCausaEvaluacionNew);
                    $planificacionEmpresaNew->setResponsable($evaluacion->getEmpresa()->getNombreRepresentante());
                    $em->persist($planificacionEmpresaNew);
                    $em->flush();
                }
                // Buscamos las medidas preventivas de la empresa
                $accionPreventivaEmpresa = $em->getRepository('App\Entity\AccionPreventivaEmpresaRiesgoCausa')->findBy(array('anulado' => false, 'riesgoCausa' => $rce));
                foreach ($accionPreventivaEmpresa as $ape) {
                    $accionPreventivaEmpresaNew = clone $ape;
                    $accionPreventivaEmpresaNew->setRiesgoCausa($riesgoCausaEvaluacionNew);
                    $em->persist($accionPreventivaEmpresaNew);
                    $em->flush();
                }
                // Buscamos las medidas preventivas del trabajador
                $accionPreventivaTrabajador = $em->getRepository('App\Entity\AccionPreventivaTrabajadorRiesgoCausa')->findBy(array('anulado' => false, 'riesgoCausa' => $rce));
                foreach ($accionPreventivaTrabajador as $apt) {
                    $accionPreventivaTrabajadorNew = clone $apt;
                    $accionPreventivaTrabajadorNew->setRiesgoCausa($riesgoCausaEvaluacionNew);
                    $em->persist($accionPreventivaTrabajadorNew);
                    $em->flush();
                }
            }
        } catch (\Exception $e) {
            $em->rollBack();
            throw $e;
        }
        $em->commit();

        $traduccion = $translator->trans('TRANS_COPY_OK');
        $this->addFlash('success',  $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }
}
