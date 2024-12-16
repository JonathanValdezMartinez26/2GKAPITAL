<?php

namespace App\components;

class SubMenuAhorro
{
    public $controlador;
    public $vista;
    // La key es el metodo de la clase Ahorro y el value es el titulo que se mostrara en el menu
    public $opciones = [
        'CuentaCorriente' => 'Ahorro cuenta corriente',
        'CajaCredito' => 'Caja Crédito',
        'LayoutPagosCredito' => 'Layout de pagos',
        'ContratoCuentaCorriente' => 'Nuevo contrato',
        'SolicitudRetiroCuentaCorriente' => 'Solicitud de retiro',
        'HistorialSolicitudRetiroCuentaCorriente' => 'Procesar solicitudes de retiro'
    ];

    public function __construct($v, $c = null)
    {
        $this->controlador = $c ?? 'Ahorro';
        $this->vista = $v;
    }

    public function mostrar()
    {
        $opc = '';
        foreach ($this->opciones as $key => $value) {
            if ($this->vista == $key) $opc .= "<li class='seleccionado'><a href='#'><p>$value</p></a></li>";
            else $opc .= "<li class='linea'><a href='/$this->controlador/$key/'><p>$value</p></a></li>";
        }

        return <<<HTML
            <ul class="nav navbar-nav">
                $opc
            </ul>

            <style>
                .seleccionado > a p {
                    font-weight: bold;
                    cursor: default;
                    font-size: 16px;
                }
                .linea > a p {
                    cursor: pointer;
                    font-size: 15px;
                }
                .linea:hover > a p {
                    text-decoration: underline;
                }
            </style>
        HTML;
    }
}
