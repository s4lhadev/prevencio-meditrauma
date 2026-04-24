<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Logger;

class SmsController extends AbstractController
{
    public function compruebaSmsCentro($centro){

        $json_url = 'https://communityhigea.com/ws/smsdisponibles/';
        $data = array("centro" => $centro);
        $data2 = http_build_query($data);
        //$json_string = json_encode($data);

        // Initializing curl
        $ch = \curl_init( $json_url );

        // Configuring curl options
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array('Content-type:  application/x-www-form-urlencoded'),
            CURLOPT_POSTFIELDS => $data2,
            CURLOPT_SSL_VERIFYPEER =>false,
            CURLOPT_SSL_VERIFYHOST=> false,
            CURLOPT_POST=>1
        );

        // Setting curl options
        \curl_setopt_array( $ch, $options );

        // Getting results
        $result = \curl_exec($ch); // Getting jSON result string
        $info = curl_getinfo($ch);
        curl_close($ch); // close cURL handler


        //Comprobamos si el codigo de respuesta http ha sido 200.// OK
        if($info['http_code'] == 200){
            $response = json_decode($result, true);
            return intval($response['sms_restantes']);
        }
        //Si no ha sido 200. Webservice Caido.
        else{
            return 0;
        }

        return 0;
    }

    public function enviaSms($centro, $remite, $numero, $mensaje, $tipoActividad, $actoId){

        $json_url = 'https://communityhigea.com/ws/enviasms/';

        $data = array(
            "centro"    => $centro,
            "remite"    => $remite,
            "mensaje"   => $mensaje,
            "numero"    => $numero,
            "tipoact"   => $tipoActividad,
            "acto"      => $actoId
        );
        $data2 = http_build_query($data);
        //$json_string = json_encode($data);

        // Initializing curl
        $ch = \curl_init( $json_url );

        // Configuring curl options
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array('Content-type:  application/x-www-form-urlencoded'),
            CURLOPT_POSTFIELDS => $data2,
            CURLOPT_SSL_VERIFYPEER =>false,
            CURLOPT_SSL_VERIFYHOST=> false,
            CURLOPT_POST=>1
        );

        // Setting curl options
        \curl_setopt_array( $ch, $options );

        // Getting results
        $result = \curl_exec($ch); // Getting jSON result string
        $info = curl_getinfo($ch);
        curl_close($ch); // close cURL handler


        //Comprobamos si el codigo de respuesta http ha sido 200.// OK
        if($info['http_code'] == 200){
            $response = json_decode($result, true);
            return $response['apiresponse'];
        }

        return 0;
    }
}