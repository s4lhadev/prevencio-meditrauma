<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class QueryController extends AbstractController
{
    public function cambioIdioma(Request $request)
    {
        $session = $request->getSession();
	    $idioma = $_REQUEST['idioma'];
	    $session->set('_locale', $idioma);

	    $data = array();
	    array_push($data, "OK");

		return new JsonResponse($data);
    }

    public function recuperarEmpresas(Request $request){

        $session = $request->getSession();

        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('App\Entity\User');
        $usuario = $repository->findForSecurityUser($user);
        $usuarioId = $usuario->getId();

        //Comprobamos si el usuario pertenece a un grupo de empresas
        $grupoEmpresaSn = $session->get('grupoEmpresa');

        if($grupoEmpresaSn){
            $query = "select distinct e.id, e.empresa from (
            select b.id, b.empresa from user_intranet_empresa a 
            inner join empresa b on a.empresa_id = b.id 
            where a.usuario_intranet_id = (select id from user_intranet where usuario_id = $usuarioId)
            union all
            select b.id, b.empresa from user_intranet a
            inner join empresa b on a.empresa_id = b.id
             where usuario_id = $usuarioId) e order by e.empresa asc";
        }else{
            $query = "select id, empresa from empresa where empresa != '' order by empresa asc";
        }

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($query);
        $stmt->execute();
        $empresas = $stmt->fetchAll();

        return new JsonResponse($empresas);
    }

    public function cambiarEmpresa(Request $request){
        $session = $request->getSession();
        $id = $_REQUEST['id'];
        $empresa = $this->getDoctrine()->getRepository('App\Entity\Empresa')->find($id);
        $session->set('empresaIntranet', $empresa);

        $data = array();
        array_push($data, "OK");

        return new JsonResponse($data);
    }
}