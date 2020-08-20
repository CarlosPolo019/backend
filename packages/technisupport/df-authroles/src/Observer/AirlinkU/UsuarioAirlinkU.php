<?php
namespace TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU;

use TechniSupport\DreamFactory\AuthRoles\Events\Event;
use TechniSupport\DreamFactory\AuthRoles\Observer\BaseObserver;
use TechniSupport\DreamFactory\AuthRoles\Subject\BaseSubject;
use TechniSupport\DreamFactory\AuthRoles\Subject\EventSubject;

/**
 * Class Usuario Maneja los eventos de creación de usuarios
 *
 * @package TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU
 */
class UsuarioAirlinkU extends BaseObserver
{
    /**
     * Obtiene la información de ún sujeto
     *
     * @param BaseSubject $subject_in
     * @return mixed
     */
    function update(BaseSubject &$subject_in) {

        /**
         * @var EventSubject $subject_in
         */
     
        if(($subject_in->getAccion() == "user" && $subject_in->getServicio() == "system") || ($subject_in->getAccion() == "register" && $subject_in->getServicio() == "user")) {
        
            $dfEvent = $subject_in->getDfEvent();

            if(isset($dfEvent["response"])) {
                $this->postCreacionUsuario($subject_in);
            }
            else {
                $this->preCreacionUsuario($subject_in);
            }
        }
    }

    /**
     * @param BaseSubject $subject_in Sujeto generador de eventos
     */
    function preCreacionUsuario(BaseSubject &$subject_in) {
        /**
         * @var $subject_in EventSubject
         */
        if($subject_in->getDfPlatform()["session"]["role"]["name"] == "AIRLINKU_ANONIMO") {
            $get = $subject_in->getDfPlatform()["api"]->get;
            $payload = $subject_in->getDfEvent()["request"]["payload"];
            $idUniversidad = $payload["id_universidad"];
            $email = $payload["email"];

            if(!$idUniversidad || is_nan($idUniversidad)) {
                throw new \Exception("Debe seleccionar universidad");
            }
            else {
                $result = $get("airlinku/_table/universidad/$idUniversidad?fields=dominios");
                $content = $result["content"];              
                $dominios = $content["dominios"];
                $dominiosArr = explode(",", $dominios);
                $partesMail = explode("@", $email);
                $dominioMail = $partesMail[1];
                
                if(!in_array($dominioMail, $dominiosArr)) {
                //   throw new \Exception("Correo no valido");
                }
            }
        }  
    }

    /**
     * @param BaseSubject $subject_in Sujeto generador de eventos
     */
    function postCreacionUsuario(BaseSubject &$subject_in) {
        
        /**
         * @var $subject_in EventSubject
         */
        if($subject_in->getDfPlatform()["session"]["role"]["name"] == "AIRLINKU_ANONIMO") {
            $get = $subject_in->getDfPlatform()["api"]->get;
            $post = $subject_in->getDfPlatform()["api"]->post;
            $patch = $subject_in->getDfPlatform()["api"]->patch;
            $put = $subject_in->getDfPlatform()["api"]->put;
            $payload = $subject_in->getDfEvent()["request"]["payload"];
            $response = $subject_in->getDfEvent()["response"];
            $idUniversidad = $payload["id_universidad"];
            $idTipoDocumento = $payload["id_tipo_documento"];
            $nroDocumento = $payload["nro_documento"];

            if(!$idUniversidad || is_nan($idUniversidad)) {
                throw new \Exception("Debe proporcionar universidad");
            }
            else {
                $result = $get("system/user?fields=id&filter=".urlencode("email=".$payload["email"]));
                $user = $result["content"];
                $code = md5(base64_encode(sha1(date("Y-m-d H:i:s").json_encode($payload))));
                $body = [];
                $body["resource"] = [];

                $body["resource"][] = [
                    "id_tipo_documento" =>  $idTipoDocumento,
                    "nro_documento"     =>  $nroDocumento,
                    "nombres"           =>  $payload["first_name"]." ".$payload["last_name"],
                    "primer_nombre"     =>  $payload["first_name"],
                    "primer_apellido"   =>  $payload["last_name"],
                    "telefono"          =>  $payload["phone"],
                    "id_universidad"    =>  $idUniversidad,
                    "user_id"           =>  $user["resource"][0]["id"],
		    "codigo"            =>  $code,
		    "correo_contacto"   =>  $payload["correo_contacto"] 		
                ];

                $content = $post("airlinku/_table/usuario",$body);

                $idUsuario = $content["content"]["resource"][0]["id"];

                $user["resource"][0]["user_lookup_by_user_id"] = [[
                    "name"=>"id_usuario",
                    "value"=>$idUsuario,
                    "private"=>false,
                    "allow_user_update"=>false,
                ],[
                    "name"=>"id_universidad",
                    "value"=>$idUniversidad,
                    "private"=>false,
                    "allow_user_update"=>false,
                ]];
                
                $user["resource"][0]["is_active"] = 1;
                $user["resource"][0]["confirm_code"] = $code;
                $user["resource"][0]["user_to_app_to_role_by_user_id"] = [[
                    "user_id" =>  $user["resource"][0]["id"],
                    "app_id" => 11,
                    "role_id" => 10
                ]];

                $res = $put("system/user",$user);

                $result=$post("email?template_id=5",[
                    "to" => [
                        "name" => $payload["first_name"]." ".$payload["last_name"],
                        "email" => $payload["email"]
                    ],
                    "first_name" => $payload["first_name"],
                    "confirm_code" => $code
                ]);
            }
            //file_put_contents("/tmp/log.txt", json_encode([$result,$code]),FILE_APPEND );
            $idUsuario=$content["content"]["resource"][0]["id"];
        }
        else if($subject_in->getDfPlatform()["session"]["role"]["name"] == "AIRLINKU_ADMIN") {

            $get = $subject_in->getDfPlatform()["api"]->get;
            $post = $subject_in->getDfPlatform()["api"]->post;
            $patch = $subject_in->getDfPlatform()["api"]->patch;
            $put = $subject_in->getDfPlatform()["api"]->put;
            $payload = $subject_in->getDfEvent()["request"]["payload"];
            $response = $subject_in->getDfEvent()["response"];
            $idUniversidad = $payload["id_universidad"];

            if(!$idUniversidad || is_nan($idUniversidad)) {
                throw new \Exception("Debe proporcionar universidad");
            }
            else {
                $result = $get("system/user?fields=id&filter=".urlencode("email=".$payload["email"]));
                $user = $result["content"];
                $body = [];
                $body["resource"] = [];

                $body["resource"][] = [
                    "id_universidad"    =>  $idUniversidad,
                    "user_id"           =>  $user["resource"][0]["id"]
                ];

                $content = $post("airlinku/_table/operario", $body);     
                $idOperario = $content["content"]["resource"][0]["id"];
  
                $user["resource"][0]["user_lookup_by_user_id"] = [[
                    "name"=>"id_operario",
                    "value"=>$idOperario,
                    "private"=>false,
                    "allow_user_update"=>false,
                ],[
                    "name"=>"id_universidad",
                    "value"=>$idUniversidad,
                    "private"=>false,
                    "allow_user_update"=>false,
                ]];

                $user["resource"][0]["user_to_app_to_role_by_user_id"] = [[
                    "user_id" =>  $user["resource"][0]["id"],
                    "app_id" => 9,
                    "role_id" => 3
                ]];

                $res = $put("system/user",$user);
            }
            
            $idUsuario = $content["content"]["resource"][0]["id"];
        }
        else if($subject_in->getDfPlatform()["session"]["role"]["name"] == "AIRLINKU_ADMIN_UNIVERSIDAD") {
            $get = $subject_in->getDfPlatform()["api"]->get;
            $post = $subject_in->getDfPlatform()["api"]->post;
            $patch = $subject_in->getDfPlatform()["api"]->patch;
            $put = $subject_in->getDfPlatform()["api"]->put;
            $payload = $subject_in->getDfEvent()["request"]["payload"];
            $response = $subject_in->getDfEvent()["response"];
            $idUniversidad = $payload["id_universidad"];
            $idUbicacion = $payload["id_ubicacion"];
            $idEmpresa = $payload["id_empresa"];
            $rolId = $payload["role_id"];
            $idUniversidad = $subject_in->getDfPlatform()["session"]["lookup"]["id_universidad"]; 
      
            if(!$rolId || is_nan($rolId)) {
                throw new \Exception("Debe proporcionar rol");
            }
            else {
                $result = $get("system/user?fields=id&filter=".urlencode("email=".$payload["email"]));
                $user = $result["content"];
                $body = [];
                $body["resource"] = [];
                
                $body["resource"][] = [
                    "id_universidad" =>  $idUniversidad,
                    "id_ubicacion"   =>  $idUbicacion,
                    "id_empresa"     =>  $idEmpresa,
                    "user_id"        =>  $user["resource"][0]["id"]
                ];
 
                $content = $post("airlinku/_table/operario", $body);
                $idOperario = $content["content"]["resource"][0]["id"];
                
                $user["resource"][0]["user_lookup_by_user_id"] = [[
                    "name"=>"id_operario",
                    "value"=>$idOperario,
                    "private"=>false,
                    "allow_user_update"=>false,
                ],[
                    "name"=>"id_universidad",
                    "value"=>$idUniversidad,
                    "private"=>false,
                    "allow_user_update"=>false,
                ]];

                $user["resource"][0]["user_to_app_to_role_by_user_id"] = [[
                    "user_id" =>  $user["resource"][0]["id"],
                    "app_id" => 9,
                    "role_id" => $payload["role_id"]
                ]];

                $res = $put("system/user",$user);
            }
           
            $idUsuario = $content["content"]["resource"][0]["id"];
        }             
    }

    /**
     * Retorna el id del observador
     * @return string
     */
    function id()
    {
        return md5(__NAMESPACE__.__CLASS__);
    }
}
