<?php

namespace Core;

use DateTime;
use DateTimeZone;

class Job
{
    private $logPath;
    private $nombreJob;

    public function __construct($nj)
    {
        $validaHV = new DateTime("now", new DateTimeZone("America/Mexico_City"));
        if ($validaHV->format("I")) date_default_timezone_set("America/Mazatlan");
        else date_default_timezone_set("America/Mexico_City");

        $this->logPath = dirname(__DIR__) . "/Jobs/Logs/";
        $this->nombreJob = $nj;

        $this->ValidaPathLog($this->logPath);
    }

    public function SaveLog($tdatos)
    {
        $archivo = $this->logPath . $this->nombreJob . ".log";

        clearstatcache();
        if (file_exists($archivo) && filesize($archivo) > 10 * 1024 * 1024) { // 10 MB
            $nuevoNombre = $this->logPath  . $this->nombreJob . date("Ymd") . ".log";
            rename($archivo, $nuevoNombre);
        }

        $log = fopen($archivo, "a");

        $infoReg = date("Y-m-d H:i:s") . ": " . debug_backtrace()[1]["function"] . " -> " . $tdatos;

        fwrite($log, $infoReg . PHP_EOL);
        fclose($log);
    }

    public function ValidaPathLog($path)
    {
        if (!file_exists($path)) mkdir($path, 0777, true);
    }
}
