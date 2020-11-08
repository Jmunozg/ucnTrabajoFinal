<?php

class login
{
    // Datos de la tabla "usuario"
    const NOMBRE_TABLA = "usuario";

    // Datos Columna
    const Username = "username";
    const NOMBRE = "Nombre";
    const CONTRASENA = "Contrasena";
    const CLAVE_API = "Token";

    public static function post($peticion)
    {
        if ($peticion[0] == 'auth') {
            return self::loguear();
        } else {
            throw new ExcepcionApi(constantes::ESTADO_URL_INCORRECTA, "Url mal formada", 400);
        }
    }

    private static function loguear()
    {
        $respuesta = array();

        $body = file_get_contents('php://input');
        $usuario = json_decode($body);

        $username = $usuario->Username;
        $password = $usuario->Password;

        if (self::autenticar($username, $password)) {
            $usuarioBD = self::obtenerUsuarioPorIdentificador($username);

            if ($usuarioBD != NULL) {
                    http_response_code(200);

                    $respuesta[self::Username] = $usuarioBD[self::Username];
                    $respuesta[self::CLAVE_API] = $usuarioBD[self::CLAVE_API];
                    $respuesta[self::NOMBRE] = $usuarioBD[self::NOMBRE];

                    return ["estado" => 1, "respuesta" => true, "usuario" => $respuesta];
                
            } else {
                throw new ExcepcionApi(constantes::ESTADO_FALLA_DESCONOCIDA,
                    "Ha ocurrido un error");
            }
        } else {
            throw new ExcepcionApi(constantes::ESTADO_PARAMETROS_INCORRECTOS,
                utf8_encode("Correo o contraseña inválidos"));
        }
    }

    private static function autenticar($username, $contrasena)
    {
        $comando = "SELECT ". self::CONTRASENA ." FROM " . self::NOMBRE_TABLA .
            " WHERE " . self::Username . "=?";

        try {

            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

            $sentencia->bindParam(1, $username);

            $sentencia->execute();

            if ($sentencia) {
                $resultado = $sentencia->fetch();

                if (self::validarContrasena($contrasena, $resultado[self::CONTRASENA])) {
                    return true;
                } else return false;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(constantes::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    public static function validarContrasena($contrasenaPlana, $contrasenaHash)
    {
        return password_verify($contrasenaPlana, $contrasenaHash);
    }

    private static function obtenerUsuarioPorIdentificador($username)
    {
        $comando = "SELECT " .
            self::Username . ", " .
            self::NOMBRE . ", " .
            self::CLAVE_API .
            " FROM " . self::NOMBRE_TABLA .
            " WHERE " . self::Username . "=?";

        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

        $sentencia->bindParam(1, $username);

        if ($sentencia->execute())
            return $sentencia->fetch(PDO::FETCH_ASSOC);
        else
            throw new ExcepcionApi(constantes::ESTADO_ERROR, "Se ha producido un error");;
    }

    public static function autorizar()
    {
        $cabeceras = apache_request_headers();

        if (isset($cabeceras["Autorizacion"])) {

            $claveApi = $cabeceras["Autorizacion"];

            if (self::validarClaveApi($claveApi)) {
                return self::obtenerIdUsuario($claveApi);
            } else {
                throw new ExcepcionApi(
                    constantes::ESTADO_CLAVE_NO_AUTORIZADA, "Clave de API no autorizada", 401);
            }

        } else {
            throw new ExcepcionApi(
                constantes::ESTADO_AUSENCIA_CLAVE_API,
                utf8_encode("Se requiere Clave del API para autenticación"));
        }
    }

    private static function validarClaveApi($claveApi)
    {
        $comando = "SELECT COUNT(" . self::Username . ")" .
            " FROM " . self::NOMBRE_TABLA .
            " WHERE " . self::CLAVE_API . "=?";

        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

        $sentencia->bindParam(1, $claveApi);

        $sentencia->execute();

        return $sentencia->fetchColumn(0) > 0;
    }

    private static function obtenerIdUsuario($claveApi)
    {
        $comando = "SELECT " . self::Username .
            " FROM " . self::NOMBRE_TABLA .
            " WHERE " . self::CLAVE_API . "=?";

        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

        $sentencia->bindParam(1, $claveApi);

        if ($sentencia->execute()) {
            $resultado = $sentencia->fetch();
            return $resultado["username"];
        } else
            return null;
    }

    public static function put($peticion){
        $idUsuario = login::autorizar();

        if (empty($idUsuario)){
            return  [
                "respuesta" => false,
                "estado" => constantes::ESTADO_NO_AUTORIZA,
                "datos" => 0,
                "mensaje" => "Usuario no autorizado"
            ];
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

        return user::ConsultarUsuarioId($idUsuario);
    }
}