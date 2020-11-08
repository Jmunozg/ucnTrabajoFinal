<?php

class user
{
    public static function post($peticion)
    {
        $idUsuario = login::autorizar();
        if (empty($idUsuario)){
            return  [
                "respuesta" => false,
                "estado" => constantes::ESTADO_NO_AUTORIZA,
                "datos" => 0,
                "mensaje" => "Usuario no autorizado"
            ];
        }

        if ($peticion[0] == 'registro') {
            return self::registrar();
        } else {
            throw new ExcepcionApi(constantes::ESTADO_URL_INCORRECTA, "Url mal formada", 400);
        }
    }    

    private static function registrar()
    {
        $cuerpo = file_get_contents('php://input');
        $datos = json_decode($cuerpo);
        $validacion = self::validarUsuario($datos->Username);

        if((bool) $validacion == true){
            return
                [
                    "respuesta" => false,
                    "estado" => constantes::ESTADO_EXITO,
                    "mensaje" => utf8_encode("¡Usuario ya existe!")
                ];
        }

        $resultado = self::crear($datos);

        http_response_code(200);
        return
            [
                "respuesta" => true,
                "estado" => constantes::ESTADO_CREACION_EXITOSA,
                "mensaje" => utf8_encode("¡Su usuario ha sido creado exitosamente!"),
                "Id" => $resultado
            ];
    }

    private static function crear($datosUsuario)
    {
        $username = $datosUsuario->Username;
        $nombre = $datosUsuario->Nombre;
        $apellido = $datosUsuario->Apellido;
        $contrasena = $datosUsuario->Contrasena;
        $contrasenaEncriptada = self::encriptarContrasena($contrasena);    
        $claveApi = self::generarClaveApi();
        
        try {

            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            // Sentencia INSERT
            $comando = "INSERT INTO " . login::NOMBRE_TABLA . " ( " .
                login::Username . "," .
                login::NOMBRE . "," .
                login::CONTRASENA . "," .
                login::CLAVE_API .")" .
                " VALUES(?,?,?,?)";

            $sentencia = $pdo->prepare($comando);

            $sentencia->bindParam(1, $username);
            $sentencia->bindParam(2, $nombre);
            $sentencia->bindParam(3, $contrasenaEncriptada);
            $sentencia->bindParam(4, $claveApi);

            $resultado = $sentencia->execute();

            if ($resultado) {
                return $pdo->lastInsertId();
            } 

        } catch (PDOException $e) {
            throw new ExcepcionApi(constantes::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    private static function encriptarContrasena($contrasenaPlana)
    {
        if ($contrasenaPlana)
            return password_hash($contrasenaPlana, PASSWORD_DEFAULT);
        else return null;
    }

    private static function generarClaveApi()
    {
        return md5(microtime().rand());
    }
    
    private static function validarUsuario($username)
    {
        $comando = "SELECT * FROM " . login::NOMBRE_TABLA .
            " WHERE " . login::Username . " =?";

        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

        $sentencia->bindParam(1, $username);

        if ($sentencia->execute())
            return $sentencia->fetch(PDO::FETCH_ASSOC);
        else
            return false;
    }

    private static function consultarUsuarios(){
        try {

            $cuerpo = file_get_contents('php://input');
            $datos = json_decode($cuerpo);
            $comando = "SELECT * FROM " . login::NOMBRE_TABLA;
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

            if ($sentencia->execute()){
                http_response_code(200);

                return
                    [
                        "respuesta" => true,
                        "estado" => constantes::ESTADO_EXITO,
                        "datos" =>  $sentencia->fetchAll(PDO::FETCH_ASSOC),
                        "cantidad" => $sentencia->rowCount()
                    ];
            }
            else
                throw new ExcepcionApi(constantes::ESTADO_ERROR_BD, "Se ha producido un error");

        } catch (PDOException $e) {
            throw new ExcepcionApi(constantes::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    public static function put($peticion){
        $idUsuario = login::autorizar();

        if (!empty($idUsuario)){
            if (!empty($peticion[0])) {
                $body = file_get_contents('php://input');
                $datos = json_decode($body);

                if ($datos === NULL){
                    return
                        [
                            "respuesta" => false,
                            "estado" => constantes::ESTADO_ERROR,
                            "mensaje" => utf8_encode("Ingresar todos los campos requeridos")
                        ];
                };

                if($peticion[0] == 'CambiarContrasena'){
                    return self::CambioContrasena($datos);
                }

                http_response_code(200);
                return
                    [
                        "respuesta" => true,
                        "estado" => constantes::ESTADO_EXITO,
                        "mensaje" => utf8_encode("¡Actualización exitosamente!"),
                        "Id" => $resultado
                    ];
            }
        }

        return  [
            "respuesta" => false,
            "estado" => constantes::ESTADO_NO_AUTORIZA,
            "datos" => 0,
            "mensaje" => "Usuario no autorizado"
        ];
    }

    private static function CambioContrasena($datos){
        try {
            $username = $datos->Correo;
            $contrasena = $datos->Contrasena;

            $contrasenaEncriptada = self::encriptarContrasena($contrasena);

            // Sentencia ACTUALIZAR
            $comando2 = "UPDATE " . login::NOMBRE_TABLA .
                " SET " . login::CONTRASENA ." =? " .
                " WHERE " . login::Username . " =?";

            $pdo = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando2);
            $pdo->bindParam(1, $contrasenaEncriptada);
            $pdo->bindParam(2, $username);
            $resultado = $pdo->execute();

            if ($resultado) {
                http_response_code(200);
                return
                    [
                        "respuesta" => true,
                        "estado" => constantes::ESTADO_EXITO,
                        "mensaje" => utf8_encode("¡Actualización exitosamente!"),
                        "Id" => $resultado
                    ];
            } else {
                return
                    [
                        "respuesta" => false,
                        "estado" => constantes::ESTADO_ERROR,
                        "mensaje" => utf8_encode("¡No se puedo actualizar la contraseña!")
                    ];
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(constantes::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    public static function get($peticion){
        $idUsuario = login::autorizar();

        if (empty($idUsuario)){
            return  [
                "respuesta" => false,
                "estado" => constantes::ESTADO_NO_AUTORIZA,
                "datos" => 0,
                "mensaje" => "Usuario no autorizado"
            ];
        }

        if (!empty($peticion[0])) {
            return self::ConsultarUsuarioId($peticion[0]);
        } else {
            throw new ExcepcionApi(constantes::ESTADO_URL_INCORRECTA, "Url mal formada", 400);
        }
    }

    public static function ConsultarUsuarioId($idUsuario){
        try {

            if (!empty($idUsuario)){
                $comando = "SELECT * FROM " . login::NOMBRE_TABLA . " WHERE username = ?";

                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

                $sentencia->bindParam(1, $idUsuario);

                if ($sentencia->execute()){
                    http_response_code(200);

                    return
                        [
                            "respuesta" => true,
                            "estado" => constantes::ESTADO_EXITO,
                            "datos" =>  $sentencia->fetchAll(PDO::FETCH_ASSOC),
                            "cantidad" => $sentencia->rowCount()
                        ];
                }
                else
                    throw new ExcepcionApi(constantes::ESTADO_ERROR_BD, "Se ha producido un error");
            }else {
                throw new ExcepcionApi(constantes::ESTADO_ERROR, "Ingresar todos los campos requeridos");
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(constantes::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

}