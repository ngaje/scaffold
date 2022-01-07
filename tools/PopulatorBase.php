<?php
use Ngaje\Scaffold\Database;

abstract class PopulatorBase
{
    /** @var Database */
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    abstract public function populate();
}
