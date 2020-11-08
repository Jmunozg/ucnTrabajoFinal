<?php

    require_once "viewApi.php";

    /**
     * Clase para imprimir en la salida respuestas con formato JSON
     */
    class viewJson extends viewApi
    {
        public function __construct($estado = 400)
        {
            $this->estado = $estado;
        }

        /**
         * Imprime el cuerpo de la respuesta y setea el código de respuesta
         * @param mixed $cuerpo de la respuesta a enviar
         */
        public function print($cuerpo)
        {
            //if ($this->estado) {
            //    http_response_code($this->estado);
            //}
            header('Content-Type: application/json; charset=utf8');
            echo json_encode($cuerpo, JSON_PRETTY_PRINT);
            exit;
        }
    }

?>