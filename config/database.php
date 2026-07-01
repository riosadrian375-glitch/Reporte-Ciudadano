<?php

class Database {
    private static $instancia = null;
    private $conexion;

    private $host;
    private $dbname;
    private $port;
    private $usuario;
    private $password;
    private $charset;

    private function __construct() {
        $this->host     = defined('DB_HOST')     ? DB_HOST     : 'localhost';
        $this->dbname   = defined('DB_NAME')     ? DB_NAME     : 'reporteciudadano';
        $this->port     = defined('DB_PORT')     ? DB_PORT     : '3306';
        $this->usuario  = defined('DB_USER')     ? DB_USER     : 'root';
        $this->password = defined('DB_PASSWORD') ? DB_PASSWORD : '';
        $this->charset  = defined('DB_CHARSET')  ? DB_CHARSET  : 'utf8mb4';

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset={$this->charset}";
            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->conexion = new PDO($dsn, $this->usuario, $this->password, $opciones);
        } catch (PDOException $e) {
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }

    public static function obtener() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    public function getConexion() {
        return $this->conexion;
    }
}
