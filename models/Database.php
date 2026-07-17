<?php

require_once __DIR__ . "/../config/config.php";

class Database {
    private $conexion;

    public function conectar() {
        $this->conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        if ($this->conexion->connect_error) {
            die("Error, no se pudo establecer la conexion" . $this->conexion->connect_error);
        }
        $this->conexion->set_charset("utf8");
        return $this->conexion;
    }
}



?>
