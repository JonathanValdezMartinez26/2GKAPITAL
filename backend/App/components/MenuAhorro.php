<?php

namespace App\components;

use App\components\OpcionMenuAhorro;

class MenuAhorro
{
    private $opciones = [];
    private $activa;

    public function __construct($activa)
    {
        $this->activa = $activa;
        array_push($this->opciones, ['ruta' => '/Ahorro/CuentaCorriente/', 'iconoON' => 'https://cdn-icons-png.flaticon.com/512/5575/5575939.png', 'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/5575/5575938.png', 'etiqueta' => 'Ahorro']);
        array_push($this->opciones, ['ruta' => '/Ahorro/ContratoInversion/', 'iconoON' => 'https://cdn-icons-png.flaticon.com/512/5836/5836503.png', 'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/5836/5836477.png', 'etiqueta' => 'Inversión']);
        array_push($this->opciones, ['ruta' => '/Ahorro/CuentaPeque/', 'iconoON' => 'https://cdn-icons-png.flaticon.com/512/2995/2995390.png', 'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/2995/2995467.png', 'etiqueta' => 'Ahorro Peque']);
        array_push($this->opciones, ['ruta' => '/Ahorro/ReporteMovimientos/', 'iconoON' => 'https://cdn-icons-png.flaticon.com/512/12202/12202939.png', 'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/12202/12202918.png', 'etiqueta' => 'Resumen Movimientos']);
        array_push($this->opciones, ['ruta' => '/Ahorro/SaldosDia/', 'iconoON' => 'https://cdn-icons-png.flaticon.com/512/5833/5833855.png', 'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/5833/5833897.png', 'etiqueta' => 'Arqueo']);
        array_push($this->opciones, ['ruta' => '/Ahorro/ReimprimeTicket/', 'iconoON' => 'https://cdn-icons-png.flaticon.com/512/7325/7325275.png', 'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/942/942752.png', 'etiqueta' => 'Reimprime Ticket']);
    }

    private function getOpciones()
    {
        $opcionesHTML = '';

        foreach ($this->opciones as $opcion) {
            $oma = new OpcionMenuAhorro($opcion);
            if ($opcion['etiqueta'] == $this->activa) $oma->toogleICONO();
            $opcionesHTML .= $oma->mostrar();
        }

        return $opcionesHTML;
    }

    public function mostrar()
    {
        $opcionesHTML = $this->getOpciones();

        return <<<HTML
        <div class="col-md-3 panel panel-body" style="margin-bottom: 0px;">
            $opcionesHTML
        </div>
        HTML;
    }
}