<?php

namespace App\components;

use App\components\AhorroMenus;

class AhorroMenus_MiEspacio extends AhorroMenus
{
    private static $opcionesMenu = [
        'Ahorro' => [
            'iconoON' => 'https://cdn-icons-png.flaticon.com/512/5575/5575939.png',
            'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/5575/5575938.png',
            'subMenu' => [
                'CuentaCorriente' => [
                    'etiqueta' => 'Ahorro cuenta corriente'
                ],
                'CajaCredito' => [
                    'etiqueta' => 'Caja Crédito'
                ],
                'LayoutPagosCredito' => [
                    'etiqueta' => 'Layout de pagos'
                ],
                'ContratoCuentaCorriente' => [
                    'etiqueta' => 'Nuevo contrato'
                ],
                'SolicitudRetiroCuentaCorriente' => [
                    'etiqueta' => 'Solicitud de retiro'
                ],
                'HistorialSolicitudRetiroCuentaCorriente' => [
                    'etiqueta' => 'Procesar solicitudes de retiro'
                ]
            ]
        ],
        'Inversión' => [
            'iconoON' => 'https://cdn-icons-png.flaticon.com/512/5836/5836477.png',
            'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/5836/5836503.png',
            'subMenu' => [
                'ContratoInversion' => [
                    'etiqueta' => 'Nuevo contrato de inversión'
                ],
                'ConsultaInversion' => [
                    'etiqueta' => 'Consultar estatus de inversión'
                ]
            ]
        ],
        'Ahorro Peque' => [
            'iconoON' => 'https://cdn-icons-png.flaticon.com/512/2995/2995467.png',
            'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/2995/2995390.png',
            'subMenu' => [
                'CuentaPeque' => [
                    'etiqueta' => 'Ahorro cuenta corriente peque'
                ],
                'ContratoCuentaPeque' => [
                    'etiqueta' => 'Nuevo contrato'
                ],
                'SolicitudRetiroCuentaPeque' => [
                    'etiqueta' => 'Solicitud de retiro'
                ],
                'HistorialSolicitudRetiroCuentaPeque' => [
                    'etiqueta' => 'Procesar solicitudes de retiro'
                ]
            ]
        ],
        'Resumen Movimientos' => [
            'iconoON' => 'https://cdn-icons-png.flaticon.com/512/12202/12202918.png',
            'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/12202/12202939.png',
            'subMenu' => [
                'EstadoCuenta' => [
                    'etiqueta' => 'Resumen de mis movimientos del día'
                ]
            ]
        ],
        'Arqueo' => [
            'iconoON' => 'https://cdn-icons-png.flaticon.com/512/5833/5833897.png',
            'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/5833/5833855.png',
            'subMenu' => [
                'SaldosDia' => [
                    'etiqueta' => 'Resumen de arqueos'
                ]
            ]
        ],
        'Reimprime Ticket' => [
            'iconoON' => 'https://cdn-icons-png.flaticon.com/512/7325/7325359.png',
            'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/7325/7325275.png',
            'subMenu' => [
                'ReimprimeTicket' => [
                    'etiqueta' => 'Tickets'
                ],
                'ReimprimeTicketSolicitudes' => [
                    'etiqueta' => 'Historial de solicitudes'
                ]
            ]
        ]
    ];

    public static function mostrar()
    {
        self::$opcPrincipal = 'Mi espacio';
        return self::getMenus(self::$opcionesMenu);
    }
}
