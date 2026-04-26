<?php

namespace App\Controller;

use App\Entity\ConsejoMedico;
use App\Entity\Cuestionario;
use App\Entity\CuestionarioPregunta;
use App\Entity\Formula;
use App\Entity\FormulaVariable;
use App\Entity\Pregunta;
use App\Entity\SerieRespuesta;
use App\Entity\Respuesta;
use App\Entity\SubPregunta;
use App\Form\ConsejoMedicoType;
use App\Form\CuestionarioPreguntaType;
use App\Form\CuestionarioType;
use App\Form\FormulaType;
use App\Form\PreguntaType;
use App\Form\RespuestaType;
use App\Form\SerieRespuestaType;
use App\Form\SubPreguntaType;
use App\Form\VariableType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Logger;
use Symfony\Contracts\Translation\TranslatorInterface;

class MantenimientoReconocimientosController extends AbstractController
{
    public function index(Request $request)
    {
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoReconocimientosSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $id = $usuario->getId();
        $username = $usuario->getUsername();

        $seriesRespuesta = $this->getDoctrine()->getRepository('App\Entity\SerieRespuesta')->findBy(array('anulado' => false), array('codigo' => 'ASC'));
        $consejoMedico = $this->getDoctrine()->getRepository('App\Entity\ConsejoMedico')->findBy(array('anulado' => false), array('codigo' => 'ASC'));
        $preguntas = $this->getDoctrine()->getRepository('App\Entity\Pregunta')->findBy(array('anulado' => false), array('id' => 'ASC'));
        $respuestas = $this->getDoctrine()->getRepository('App\Entity\Respuesta')->findBy(array('anulado' => false), array('descripcion' => 'ASC'));
        $formulas = $this->getDoctrine()->getRepository('App\Entity\Formula')->findBy(array('anulado' => false), array('codigo' => 'ASC'));
        $cuestionarios = $this->getDoctrine()->getRepository('App\Entity\Cuestionario')->findBy(array('anulado' => false), array('codigo' => 'ASC'));

        $object = array("json"=>$username, "entidad"=>"mantenimiento de reconocimientos", "id"=>$id);
        $logger = new Logger();
        $em = $this->getDoctrine()->getManager();
        $logger->addLog($em, "select", $object, $usuario, TRUE);
        $em->flush();

        return $this->render('mantenimientoreconocimientos/index.html.twig', array('seriesRespuesta' => $seriesRespuesta, 'consejoMedico' => $consejoMedico, 'preguntas' => $preguntas, 'respuestas' => $respuestas, 'formulas' => $formulas, 'cuestionarios' => $cuestionarios));
    }

    public function createSerieRespuesta(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoSerieRespuestaSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $serieRespuesta = new SerieRespuesta();

        $form = $this->createForm(SerieRespuestaType::class, $serieRespuesta);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $serieRespuesta = $form->getData();
            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            $em->persist($serieRespuesta);
            $em->flush();

            //Control para saber en que pagina crea la respuesta
            $session->set('pregunta', null);
            $session->set('serieRespuesta', $serieRespuesta->getId());
            return $this->redirectToRoute('mantenimiento_reconocimientos_serie_respuesta_update', array('id' => $serieRespuesta->getId()));
        }

        return $this->render('mantenimientoreconocimientos/serieRespuesta.html.twig', array('form' => $form->createView()));
    }

    public function updateSerieRespuesta(Request $request, $id, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoSerieRespuestaSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $serieRespuesta = $this->getDoctrine()->getRepository('App\Entity\SerieRespuesta')->find($id);
        $respuestas = $this->getDoctrine()->getRepository('App\Entity\Respuesta')->findBy(array('serieRespuesta' => $serieRespuesta, 'anulado' => false), array('sub' => 'ASC'));

        //Control para saber en que pagina crea la respuesta
        $session->set('pregunta', null);
        $session->set('serieRespuesta', $serieRespuesta->getId());

        $form = $this->createForm(SerieRespuestaType::class, $serieRespuesta);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $serieRespuesta = $form->getData();
            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            $em->persist($serieRespuesta);
            $em->flush();
            return $this->redirectToRoute('mantenimiento_reconocimientos_serie_respuesta_update', array('id' => $serieRespuesta->getId()));
        }

        return $this->render('mantenimientoreconocimientos/serieRespuesta.html.twig', array('form' => $form->createView(), 'respuestas' => $respuestas));
    }

    public function deleteSerieRespuesta(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $serieId = $_REQUEST['serieId'];

        $serieRespuesta = $this->getDoctrine()->getRepository('App\Entity\SerieRespuesta')->find($serieId);
        $respuestas = $this->getDoctrine()->getRepository('App\Entity\Respuesta')->findBy(array('serieRespuesta' => $serieRespuesta, 'anulado' => false));

        foreach ($respuestas as $r){
            $r->setAnulado(true);
            $em->persist($r);
            $em->flush();
        }

        $serieRespuesta->setAnulado(true);
        $em->persist($serieRespuesta);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        //Control para saber en que pagina crea la respuesta
        $session->set('serieRespuesta',null);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

//    public function addRepuestaSerie(Request $request, TranslatorInterface $translator){
//        $em = $this->getDoctrine()->getManager();
//
//        $respuesta = $_REQUEST['respuesta'];
//        $serieRespuestaId = $_REQUEST['serieRespuestaId'];
//        $serieRespuesta = $this->getDoctrine()->getRepository('App\Entity\SerieRespuesta')->find($serieRespuestaId);
//
//        $respuestaNew = new Respuesta();
//        $respuestaNew->setDescripcion($respuesta);
//        $respuestaNew->setSerieRespuesta($serieRespuesta);
//        $em->persist($respuestaNew);
//        $em->flush();
//
//        $traduccion = $translator->trans('TRANS_CREATE_OK');
//        $this->addFlash('success',  $traduccion);
//
//        $data = array();
//        array_push($data, "OK");
//
//        return new JsonResponse($data);
//    }
//
//    public function updateRespuestaSerie(Request $request, TranslatorInterface $translator){
//        $em = $this->getDoctrine()->getManager();
//
//        $respuestaId = $_REQUEST['respuestaId'];
//        $respuesta = $_REQUEST['respuesta'];
//        $respuestas = $this->getDoctrine()->getRepository('App\Entity\Respuesta')->find($respuestaId);
//        $respuestas->setDescripcion($respuesta);
//        $em->persist($respuestas);
//        $em->flush();
//
//        $traduccion = $translator->trans('TRANS_UPDATE_OK');
//        $this->addFlash('success',  $traduccion);
//
//        $data = array();
//        array_push($data, "OK");
//
//        return new JsonResponse($data);
//    }
//
//    public function deleteRespuestaSerie(Request $request, TranslatorInterface $translator){
//        $em = $this->getDoctrine()->getManager();
//
//        $respuestaId = $_REQUEST['respuestaId'];
//        $respuesta = $this->getDoctrine()->getRepository('App\Entity\Respuesta')->find($respuestaId);
//        $respuesta->setAnulado(true);
//        $em->persist($respuesta);
//        $em->flush();
//
//        $traduccion = $translator->trans('TRANS_DELETE_OK');
//        $this->addFlash('success',  $traduccion);
//
//        $data = array();
//        array_push($data, "OK");
//
//        return new JsonResponse($data);
//    }
//
//    public function recuperaRespuestaSerie(Request $request){
//        $respuestaId = $_REQUEST['respuestaId'];
//        $respuesta = $this->getDoctrine()->getRepository('App\Entity\Respuesta')->find($respuestaId);
//
//        $data = array(
//            'id' => $respuesta->getId(),
//            'descripcion' => $respuesta->getDescripcion(),
//        );
//        return new JsonResponse($data);
//    }

    public function createConsejoMedico(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoConsejosMedicosSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        //Control para saber en que pagina crea la respuesta
        $session->set('pregunta', null);
        $session->set('serieRespuesta',null);

        $consejoMedico = new ConsejoMedico();
        $form = $this->createForm(ConsejoMedicoType::class, $consejoMedico);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $consejoMedico = $form->getData();
            $em->persist($consejoMedico);
            $em->flush();

            //Si ha informado el documento lo guardamos
            $documento = $form->get('documento')->getData();
            if(!is_null($documento)){

                //Obtenemos el nombre y la extension
                $filename =  $documento->getClientOriginalName();

                move_uploaded_file($documento, "upload/media/consejos/$filename");
                $path_info = pathinfo("upload/media/consejos/$filename");
                $extension = $path_info['extension'];

                //Generamos un nombre aleatorio
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < 20; $i++) {
                    $randomString .= $characters[rand(0, $charactersLength - 1)];
                }

                $randomName = $randomString.'.'.$extension;

                //Renombramos el documento
                rename("upload/media/consejos/$filename","upload/media/consejos/$randomName");

                //Añadimos el logo a la empresa
                $consejoMedico->setDocumento($randomName);
                $em->persist($consejoMedico);
                $em->flush();
            }

            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('mantenimiento_reconocimientos_consejo_medico_update', array('id' => $consejoMedico->getId()));
        }
        return $this->render('mantenimientoreconocimientos/consejoMedico.html.twig', array('form' => $form->createView()));
    }

    public function updateConsejoMedico(Request $request, $id, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoConsejosMedicosSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        //Control para saber en que pagina crea la respuesta
        $session->set('pregunta', null);
        $session->set('serieRespuesta',null);

        $consejoMedico = $this->getDoctrine()->getRepository('App\Entity\ConsejoMedico')->find($id);
        $form = $this->createForm(ConsejoMedicoType::class, $consejoMedico);
        $form->handleRequest($request);

        //Comprobamos si el consejo tiene el documento informado
        $documento = $consejoMedico->getDocumento();

        if ($form->isSubmitted()) {
            $serieRespuesta = $form->getData();
            $em->persist($consejoMedico);
            $em->flush();

            //Si ha informado el documento lo guardamos
            $documento = $form->get('documento')->getData();
            if(!is_null($documento)){

                //Obtenemos el nombre y la extension
                $filename =  $documento->getClientOriginalName();

                move_uploaded_file($documento, "upload/media/consejos/$filename");
                $path_info = pathinfo("upload/media/consejos/$filename");
                $extension = $path_info['extension'];

                //Generamos un nombre aleatorio
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < 20; $i++) {
                    $randomString .= $characters[rand(0, $charactersLength - 1)];
                }

                $randomName = $randomString.'.'.$extension;

                //Renombramos el documento
                rename("upload/media/consejos/$filename","upload/media/consejos/$randomName");

                //Añadimos el logo a la empresa
                $consejoMedico->setDocumento($randomName);
                $em->persist($consejoMedico);
                $em->flush();
            }

            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);

            return $this->redirectToRoute('mantenimiento_reconocimientos_consejo_medico_update', array('id' => $consejoMedico->getId()));
        }
        return $this->render('mantenimientoreconocimientos/consejoMedico.html.twig', array('form' => $form->createView(), 'documento' => $documento));
    }

    public function deleteConsejoMedico(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $consejoId = $_REQUEST['consejoId'];
        $consejoMedico = $this->getDoctrine()->getRepository('App\Entity\ConsejoMedico')->find($consejoId);
        $consejoMedico->setAnulado(true);
        $em->persist($consejoMedico);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        //Control para saber en que pagina crea la respuesta
        $session->set('pregunta', null);
        $session->set('serieRespuesta',null);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function deleteDocumentoConsejoMedico(Request $request, $id, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $consejoMedico = $this->getDoctrine()->getRepository('App\Entity\ConsejoMedico')->find($id);

        $documento = $consejoMedico->getDocumento();
        unlink("upload/media/consejos/$documento");

        $consejoMedico->setDocumento(null);
        $em->persist($consejoMedico);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        return $this->redirectToRoute('mantenimiento_reconocimientos_consejo_medico_update', array('id' => $consejoMedico->getId()));
    }

    public function createPregunta(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoPreguntasSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $pregunta = new Pregunta();

        $form = $this->createForm(PreguntaType::class, $pregunta);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $pregunta = $form->getData();
            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            $em->persist($pregunta);
            $em->flush();

            //Control para saber en que pagina crea la respuesta
            $session->set('pregunta', $pregunta->getId());
            $session->set('serieRespuesta',null);

            return $this->redirectToRoute('mantenimiento_reconocimientos_pregunta_update', array('id' => $pregunta->getId()));
        }

        return $this->render('mantenimientoreconocimientos/pregunta.html.twig', array('form' => $form->createView()));
    }

    public function updatePregunta(Request $request, $id, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoPreguntasSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $pregunta = $this->getDoctrine()->getRepository('App\Entity\Pregunta')->find($id);

        //Control para saber en que pagina crea la respuesta
        $session->set('pregunta', $pregunta->getId());
        $session->set('serieRespuesta',null);

        //Comprobamos si la pregunta tiene una serie informada
        if(!is_null($pregunta->getSerieRespuesta())){
            $respuestas = $this->getDoctrine()->getRepository('App\Entity\Respuesta')->findBy(array('serieRespuesta' => $pregunta->getSerieRespuesta(), 'anulado' => false), array('sub' => 'ASC'));
        }else{
            $respuestas = $this->getDoctrine()->getRepository('App\Entity\Respuesta')->findBy(array('pregunta' => $pregunta, 'anulado' => false), array('sub' => 'ASC'));
        }

        //Buscamos si las subpreguntas si tiene
        $subpreguntas = $this->getDoctrine()->getRepository('App\Entity\SubPregunta')->findBy(array('pregunta' => $pregunta, 'anulado' => false), array('orden' => 'ASC'));

        $form = $this->createForm(PreguntaType::class, $pregunta);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $pregunta = $form->getData();
            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            $em->persist($pregunta);
            $em->flush();
            return $this->redirectToRoute('mantenimiento_reconocimientos_pregunta_update', array('id' => $pregunta->getId()));
        }

        return $this->render('mantenimientoreconocimientos/pregunta.html.twig', array('form' => $form->createView(), 'respuestas' => $respuestas, 'subpreguntas' => $subpreguntas));
    }

    public function deletePregunta(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $preguntaId = $_REQUEST['preguntaId'];
        $pregunta = $this->getDoctrine()->getRepository('App\Entity\Pregunta')->find($preguntaId);

        //Eliminamos las respuestas si tiene
        $respuestas = $this->getDoctrine()->getRepository('App\Entity\Respuesta')->findBy(array('pregunta' => $pregunta, 'anulado' => false));

        foreach ($respuestas as $r){
            $r->setAnulado(true);
            $em->persist($r);
            $em->flush();
        }

        //Eliminamos las subpreguntas si tiene
        $subPreguntas = $this->getDoctrine()->getRepository('App\Entity\SubPregunta')->findBy(array('pregunta' => $pregunta, 'anulado' => false));
        foreach ($subPreguntas as $s){
            $s->setAnulado(true);
            $em->persist($s);
            $em->flush();
        }

        $pregunta->setAnulado(true);
        $em->persist($pregunta);
        $em->flush();

        //Control para saber en que pagina crea la respuesta
        $session->set('pregunta', null);

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function createSubPregunta(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoSubPreguntasSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $subPregunta = new SubPregunta();

        $preguntaId = $session->get('pregunta');
        $pregunta = $this->getDoctrine()->getRepository('App\Entity\Pregunta')->find($preguntaId);
        $subPregunta->setPregunta($pregunta);

        $form = $this->createForm(SubPreguntaType::class, $subPregunta);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $subPregunta = $form->getData();
            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            $em->persist($subPregunta);
            $em->flush();

            $session->set('subPregunta', $subPregunta->getId());

            return $this->redirectToRoute('mantenimiento_reconocimientos_subpregunta_update', array('id' => $pregunta->getId()));
        }

        return $this->render('mantenimientoreconocimientos/subPregunta.html.twig', array('form' => $form->createView()));
    }

    public function updateSubPregunta(Request $request, $id, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoSubPreguntasSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $subPregunta = $this->getDoctrine()->getRepository('App\Entity\SubPregunta')->find($id);
        $session->set('subPregunta', $id);

        $preguntaId = $session->get('pregunta');
        $pregunta = $this->getDoctrine()->getRepository('App\Entity\Pregunta')->find($preguntaId);

        $form = $this->createForm(SubPreguntaType::class, $subPregunta);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $subPregunta = $form->getData();
            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            $em->persist($subPregunta);
            $em->flush();

            $session->set('subPregunta', $subPregunta->getId());

            return $this->redirectToRoute('mantenimiento_reconocimientos_subpregunta_update', array('id' => $pregunta->getId()));
        }

        return $this->render('mantenimientoreconocimientos/subPregunta.html.twig', array('form' => $form->createView()));
    }

    public function deleteSubPregunta(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $subPreguntaId = $_REQUEST['subPreguntaId'];
        $subPregunta = $this->getDoctrine()->getRepository('App\Entity\SubPregunta')->find($subPreguntaId);
        $subPregunta->setAnulado(true);
        $em->persist($subPregunta);
        $em->flush();

        //Control para saber en que pagina crea la respuesta
        $session->set('subPregunta', null);

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function createRespuesta(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoPreguntasSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $respuesta = new Respuesta();

        //Control para saber en que pagina crea la respuesta
        $preguntaId = $session->get('pregunta');
        $serieRespuestaId = $session->get('serieRespuesta');

        if(!is_null($preguntaId)){
            $pregunta = $this->getDoctrine()->getRepository('App\Entity\Pregunta')->find($preguntaId);
            $respuesta->setPregunta($pregunta);
        }

        if(!is_null($serieRespuestaId)){
            $serieRespuesta = $this->getDoctrine()->getRepository('App\Entity\SerieRespuesta')->find($serieRespuestaId);
            $respuesta->setSerieRespuesta($serieRespuesta);
        }

        $form = $this->createForm(RespuestaType::class, $respuesta);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $respuesta = $form->getData();
            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            $em->persist($respuesta);
            $em->flush();

            //Comprobamos por que pantalla ha entrado el usuario
            $preguntaId = $session->get('pregunta');
            if(!is_null($preguntaId)){
                return $this->redirectToRoute('mantenimiento_reconocimientos_pregunta_update', array('id' => $preguntaId));
            }
            $serieRespuestaId = $session->get('serieRespuesta');
            if(!is_null($serieRespuestaId)){
                return $this->redirectToRoute('mantenimiento_reconocimientos_serie_respuesta_update', array('id' => $serieRespuestaId));
            }

            return $this->redirectToRoute('mantenimiento_reconocimientos_respuesta_update', array('id' => $respuesta->getId()));
        }

        return $this->render('mantenimientoreconocimientos/respuesta.html.twig', array('form' => $form->createView()));
    }

    public function updateRespuesta(Request $request, $id, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoPreguntasSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $respuesta = $this->getDoctrine()->getRepository('App\Entity\Respuesta')->find($id);

        $form = $this->createForm(RespuestaType::class, $respuesta);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $respuesta = $form->getData();
            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            $em->persist($respuesta);
            $em->flush();

            //Comprobamos por que pantalla ha entrado el usuario
            $preguntaId = $session->get('pregunta');
            if(!is_null($preguntaId)){
                return $this->redirectToRoute('mantenimiento_reconocimientos_pregunta_update', array('id' => $preguntaId));
            }
            $serieRespuestaId = $session->get('serieRespuesta');
            if(!is_null($serieRespuestaId)){
                return $this->redirectToRoute('mantenimiento_reconocimientos_serie_respuesta_update', array('id' => $serieRespuestaId));
            }

            return $this->redirectToRoute('mantenimiento_reconocimientos_respuesta_update', array('id' => $respuesta->getId()));
        }

        return $this->render('mantenimientoreconocimientos/respuesta.html.twig', array('form' => $form->createView()));
    }

    public function deleteRespuesta(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $respuestaId = $_REQUEST['respuestaId'];
        $respuesta = $this->getDoctrine()->getRepository('App\Entity\Respuesta')->find($respuestaId);
        $respuesta->setAnulado(true);
        $em->persist($respuesta);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function createFormula(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoFormulasSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $formula = new Formula();

        $form = $this->createForm(FormulaType::class, $formula);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $formula = $form->getData();
            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            $em->persist($formula);
            $em->flush();

            $session->set('formula', $formula->getId());

            return $this->redirectToRoute('mantenimiento_reconocimientos_formula_update', array('id' => $formula->getId()));
        }

        return $this->render('mantenimientoreconocimientos/formula.html.twig', array('form' => $form->createView()));
    }

    public function updateFormula(Request $request, $id, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoFormulasSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $formula = $this->getDoctrine()->getRepository('App\Entity\Formula')->find($id);
        $variables = $this->getDoctrine()->getRepository('App\Entity\FormulaVariable')->findBy(array('formula' => $formula, 'anulado' => false));

        $form = $this->createForm(FormulaType::class, $formula);
        $form->handleRequest($request);

        $session->set('formula', $id);

        if ($form->isSubmitted()) {
            $formula = $form->getData();
            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            $em->persist($formula);
            $em->flush();

            return $this->redirectToRoute('mantenimiento_reconocimientos_formula_update', array('id' => $formula->getId()));
        }

        return $this->render('mantenimientoreconocimientos/formula.html.twig', array('form' => $form->createView(), 'variables' => $variables));
    }

    public function deleteFormula(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $formulaId = $_REQUEST['formulaId'];
        $formula = $this->getDoctrine()->getRepository('App\Entity\Formula')->find($formulaId);
        $variables = $this->getDoctrine()->getRepository('App\Entity\FormulaVariable')->findBy(array('formula' => $formula, 'anulado' => false));

        foreach ($variables as $v){
            $v->setAnulado(true);
            $em->persist($v);
            $em->flush();
        }

        $formula->setAnulado(true);
        $em->persist($formula);
        $em->flush();

        $session->set('formula', null);

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function createVariable(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $formulaId = $_REQUEST['formulaId'];
        $formula = $this->getDoctrine()->getRepository('App\Entity\Formula')->find($formulaId);
        $variable = $_REQUEST['variable'];
        $codigo = $_REQUEST['codigo'];

        $formulaVariable = new FormulaVariable();
        $formulaVariable->setDescripcion($variable);
        $formulaVariable->setFormula($formula);
        $formulaVariable->setCodigo($codigo);
        $em->persist($formulaVariable);
        $em->flush();

        $traduccion = $translator->trans('TRANS_CREATE_OK');
        $this->addFlash('success',  $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function recuperaVariable(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $variableId = $_REQUEST['variableId'];
        $formulaVariable = $this->getDoctrine()->getRepository('App\Entity\FormulaVariable')->find($variableId);

        $data = array(
            'id' => $formulaVariable->getId(),
            'descripcion' => $formulaVariable->getDescripcion(),
            'codigo' => $formulaVariable->getCodigo()
        );
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function updateVariable(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $variableId = $_REQUEST['variableId'];
        $formulaVariable = $this->getDoctrine()->getRepository('App\Entity\FormulaVariable')->find($variableId);
        $variable = $_REQUEST['variable'];
        $codigo = $_REQUEST['codigo'];

        $formulaVariable->setDescripcion($variable);
        $formulaVariable->setCodigo($codigo);
        $em->persist($formulaVariable);
        $em->flush();

        $traduccion = $translator->trans('TRANS_UPDATE_OK');
        $this->addFlash('success',  $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function deleteVariable(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $variableId = $_REQUEST['variableId'];
        $variable = $this->getDoctrine()->getRepository('App\Entity\FormulaVariable')->find($variableId);
        $variable->setAnulado(true);
        $em->persist($variable);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function createCuestionario(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoCuestionariosSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $cuestionario = new Cuestionario();

        $form = $this->createForm(CuestionarioType::class, $cuestionario);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $cuestionario = $form->getData();
            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            $em->persist($cuestionario);
            $em->flush();

            $session->set('cuestionario', $cuestionario->getId());

            return $this->redirectToRoute('mantenimiento_reconocimientos_cuestionario_update', array('id' => $cuestionario->getId()));
        }

        return $this->render('mantenimientoreconocimientos/cuestionario.html.twig', array('form' => $form->createView()));
    }

    public function updateCuestionario(Request $request, $id, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoCuestionariosSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $cuestionario = $this->getDoctrine()->getRepository('App\Entity\Cuestionario')->find($id);
        $session->set('cuestionario', $id);

        $preguntas = $this->getDoctrine()->getRepository('App\Entity\CuestionarioPregunta')->findBy(array('cuestionario' => $cuestionario, 'anulado' => false), array('orden' => 'ASC'));

        $form = $this->createForm(CuestionarioType::class, $cuestionario);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $cuestionario = $form->getData();
            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            $em->persist($cuestionario);
            $em->flush();

            return $this->redirectToRoute('mantenimiento_reconocimientos_cuestionario_update', array('id' => $cuestionario->getId()));
        }

        return $this->render('mantenimientoreconocimientos/cuestionario.html.twig', array('form' => $form->createView(), 'preguntas' => $preguntas));
    }

    public function deleteCuestionario(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $cuestionarioId = $_REQUEST['cuestionarioId'];
        $cuestionario = $this->getDoctrine()->getRepository('App\Entity\Cuestionario')->find($cuestionarioId);
        $preguntas = $this->getDoctrine()->getRepository('App\Entity\CuestionarioPregunta')->findBy(array('cuestionario' => $cuestionario, 'anulado' => false));

        foreach ($preguntas as $p){
            $p->setAnulado(true);
            $em->persist($p);
            $em->flush();
        }

        $cuestionario->setAnulado(true);
        $em->persist($cuestionario);
        $em->flush();

        $session->set('cuestionario', null);

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

    public function createPreguntaCuestionario(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoCuestionariosSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $cuestionarioPregunta = new CuestionarioPregunta();

        $cuestionarioId = $session->get('cuestionario');
        $cuestionario = $this->getDoctrine()->getRepository('App\Entity\Cuestionario')->find($cuestionarioId);

        $cuestionarioPregunta->setCuestionario($cuestionario);

        $form = $this->createForm(CuestionarioPreguntaType::class, $cuestionarioPregunta);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $cuestionarioPregunta = $form->getData();
            $traduccion = $translator->trans('TRANS_CREATE_OK');
            $this->addFlash('success',  $traduccion);
            $em->persist($cuestionarioPregunta);
            $em->flush();

            return $this->redirectToRoute('mantenimiento_reconocimientos_cuestionario_update', array('id' => $cuestionarioId));
        }

        return $this->render('mantenimientoreconocimientos/cuestionarioPregunta.html.twig', array('form' => $form->createView()));
    }

    public function updatePreguntaCuestionario(Request $request, $id, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $privilegios = $session->get('privilegiosRol');
        if(!is_null($privilegios)){
            if(!$privilegios->getMantenimientoCuestionariosSn()){
                return $this->redirectToRoute('error_403');
            }
        }

        $cuestionarioPregunta = $this->getDoctrine()->getRepository('App\Entity\CuestionarioPregunta')->find($id);

        $cuestionarioId = $session->get('cuestionario');

        $form = $this->createForm(CuestionarioPreguntaType::class, $cuestionarioPregunta);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $cuestionarioPregunta = $form->getData();
            $traduccion = $translator->trans('TRANS_UPDATE_OK');
            $this->addFlash('success',  $traduccion);
            $em->persist($cuestionarioPregunta);
            $em->flush();

            return $this->redirectToRoute('mantenimiento_reconocimientos_cuestionario_update', array('id' => $cuestionarioId));
        }

        return $this->render('mantenimientoreconocimientos/cuestionarioPregunta.html.twig', array('form' => $form->createView()));
    }

    public function deletePreguntaCuestionario(Request $request, TranslatorInterface $translator){
        $em = $this->getDoctrine()->getManager();

        $preguntaCuestionarioId = $_REQUEST['preguntaCuestionarioId'];
        $pregunta = $this->getDoctrine()->getRepository('App\Entity\CuestionarioPregunta')->find($preguntaCuestionarioId);
        $pregunta->setAnulado(true);
        $em->persist($pregunta);
        $em->flush();

        $traduccion = $translator->trans('TRANS_DELETE_OK');
        $this->addFlash('success',  $traduccion);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }

}