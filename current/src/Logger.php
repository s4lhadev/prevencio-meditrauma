<?php

namespace App;

use Gedmo\Tool\Wrapper\AbstractWrapper;
use Gedmo\Loggable\Mapping\Event\LoggableAdapter;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

class Logger {

    /**
     * Creates a new Log entry
     *
     * @param entity manager $em
     * @param string $action
     * @param object $object
     * @param object $username
     * @param boolean $json
     * @return void
     */
    public static function addLog($em, $action, $object, $username, $json=FALSE) {

        if($action == "login"){
            $newdata = $object["json"];
            $entityName = $object["entidad"];
            $objectId = $object["id"];
        }
        else if (!$json){
            (string)$username = $username->getUsername();
            $wrapped = AbstractWrapper::wrap($object, $em);
            $entityName = $em->getMetadataFactory()->getMetadataFor(get_class($object))->getName();
            $objectId = $wrapped->getIdentifier();

            $newdata = "";
            $serializer = SerializerBuilder::create()->build();
            $data = $serializer->serialize($object, 'json', SerializationContext::create()->enableMaxDepthChecks());
            $newdata = str_replace("'", " ", $data, $count);

        } else if($json) {
            (string)$username = $username->getUsername();
            $newdata = $object["json"];
            $entityName = $object["entidad"];
            $objectId = $object["id"];
        }

        /*$query = "select last_value FROM ext_log_entries_id_seq";
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $resultId = $stmt->fetchAll();
        $id = $resultId[0]['last_value'];*/

        $query = "INSERT INTO ext_log_entries VALUES (nextval('ext_log_entries_id_seq'),'".$action."',now(), '".$objectId."', '".$entityName."', 1, '$newdata', '".$username."')";
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();

        /* $query = "SELECT setval('ext_log_entries_id_seq', (select max(id) from ext_log_entries), true)";
         $stmt = $em->getConnection()->prepare($query);
         $stmt->execute();*/
    }
    public static function addLogRenovaciones($em, $username, $boto, $contractes) {
        $hoy = date("Y-m-d H:i:s");
        //$query1 = "select MAX(id) FROM log_renovaciones";
        //$stmt = $em->getConnection()->prepare($query1);
        //$stmt->execute();
        //$resultId = $stmt->fetchAll();
        //$id = $resultId[0]['id'];
        //$idFinal = $id + 1;
        $query = "INSERT INTO log_renovaciones (id, usuari, data_log, boto, contractes) VALUES (nextval('ext_log_entries_id_seq'), '".$username."', '".$hoy."','".$boto. "','".$contractes."')";
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();

        /* $query = "SELECT setval('ext_log_entries_id_seq',
        (select max(id) from ext_log_entries), true)";
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();*/
    }
}
