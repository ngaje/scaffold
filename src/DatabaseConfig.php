<?php
namespace Netshine\Scaffold;

class DatabaseConfig
{
    public $entity_namespace;
    public $entity_path;
    public $host;
    public $username;
    public $password;
    public $database;
    public $port;
    public $dsn;

    public function __construct($entity_namespace, $entity_path, $host = 'localhost', $username = 'root', $password, $database, $port = 3306, $dsn = '')
    {
        $this->entity_namespace = $entity_namespace;
        $this->entity_path = $entity_path;
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
        $this->dsn = $dsn;
    }
}