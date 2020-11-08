<?php   

const ESTADO_ERROR_BD = 1;
const ESTADO_URL_INCORRECTA = 2;
const ESTADO_EXISTENCIA_RECURSO = 3;
const ESTADO_METODO_NO_PERMITIDO = 4;

require "view/viewJson.php";
require "util/excepcionapi.php";
require "util/conexion.php";
require "util/constantes.php";
require "model/user.php";
require "model/login.php";

$vista = new viewJson();

set_exception_handler(function ($exception) use ($vista) {
    $cuerpo = array(
        "respuesta" => $exception->respuesta,
        "estado" => $exception->estado,
        "mensaje" => $exception->getMessage()
    );
    if ($exception->getCode()) {
        $vista->estado = $exception->getCode();
    } else {
        $vista->estado = 500;
    }

    $vista->print($cuerpo);
});

// Extraer segmento de la url
if (isset($_GET['PATH_INFO']))
    $peticion = explode('/', $_GET['PATH_INFO']);
else
    throw new ExcepcionApi(ESTADO_URL_INCORRECTA, utf8_encode("No se reconoce la petición"));

// Obtener recurso
$recurso = array_shift($peticion);
$recursos_existentes = array('login', 'user');

// Comprobar si existe el recurso
if (!in_array($recurso, $recursos_existentes)) {
 throw new ExcepcionApi(ESTADO_EXISTENCIA_RECURSO,
        "No se reconoce el recurso al que intentas acceder");
}

$metodo = strtolower($_SERVER['REQUEST_METHOD']);

switch ($metodo) {
    case 'get':
        // Procesar método get        
        $respuesta = call_user_func(array($recurso, 'get'), $peticion);   
        $vista->print($respuesta);        
        break;

    case 'post':
        // Procesar método post     
        $respuesta = call_user_func(array($recurso, 'post'), $peticion);   
        $vista->print($respuesta);        
        break;
    case 'put':
        // Procesar método put        
        $respuesta = call_user_func(array($recurso, 'put'), $peticion);
        $vista->print($respuesta);        
        break;

    case 'delete':
        // Procesar método delete
        break;
    default:
        // Método no aceptado
        http_response_code(405);
        $cuerpo = [
            "estado" => ESTADO_METODO_NO_PERMITIDO,
            "mensaje" => utf8_encode("Método no permitido")
        ];
        $vista->print($cuerpo);
}

?>