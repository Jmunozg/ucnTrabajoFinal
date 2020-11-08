<?php

    abstract class viewApi {
        
        // Código de error
        public $estado;

        public abstract function print($cuerpo);
    }

?>