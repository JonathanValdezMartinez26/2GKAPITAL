<?php

namespace App\components;

use App\components\AhorroMenus;

class AhorroMenus_AdminSuc extends AhorroMenus
{
    private static $opcionesMenu = [
        'Saldos de Sucursales' => [
            'iconoON' => 'https://cdn-icons-png.flaticon.com/512/2910/2910306.png',
            'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/2910/2910156.png',
            'subMenu' => [
                'SaldosDiarios' => [
                    'etiqueta' => 'Saldos del día por sucursal'
                ],
                'FondearSucursal' => [
                    'etiqueta' => 'Fondear sucursal'
                ],
                'RetiroSucursal' => [
                    'etiqueta' => 'Retiro efectivo'
                ]
            ]
        ],
        'Solicitudes' => [
            'iconoON' => 'https://cdn-icons-png.flaticon.com/512/2972/2972528.png',
            'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/2972/2972449.png',
            'subMenu' => [
                'SolicitudesReimpresionTicket' => [
                    'etiqueta' => 'Reimpresión de tickets'
                ],
                'SolicitudResumenMovimientos' => [
                    'etiqueta' => 'Resumen de movimientos'
                ],
                'SolicitudRetiroOrdinario' => [
                    'etiqueta' => 'Retiros programados'
                ],
                'SolicitudRetiroExpress' => [
                    'etiqueta' => 'Retiros express'
                ]
            ]
        ],
        'Consultar Reportes' => [
            'iconoON' => 'https://cdn-icons-png.flaticon.com/512/3201/3201558.png',
            'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/3201/3201495.png',
            'subMenu' => [
                'Reporteria' => [
                    'etiqueta' => 'Flujo efectivo'
                ],
                'Transacciones' => [
                    'etiqueta' => 'Transacciones'
                ],
                'HistorialFondeoSucursal' => [
                    'etiqueta' => 'Historial fondeo sucursal'
                ],
                'HistorialRetiroSucursal' => [
                    'etiqueta' => 'Historial retiro sucursal'
                ],
                'SituacionAhorro' => [
                    'etiqueta' => 'Situación ahorro'
                ],
                'DevengoInteres' => [
                    'etiqueta' => 'Devengo interés'
                ]
            ]
        ],
        'Catalogo de Clientes' => [
            'iconoON' => 'https://cdn-icons-png.flaticon.com/512/3201/3201558.png',
            'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/5864/5864275.png',
            'subMenu' => [
                'EstadoCuentaCliente' => [
                    'etiqueta' => 'Resumen de movimientos'
                ]
            ]
        ],
        'Configurar Módulo' => [
            'iconoON' => 'https://cdn-icons-png.flaticon.com/512/10491/10491253.png',
            'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/10491/10491249.png',
            'subMenu' => [
                'Configuracion' => [
                    'etiqueta' => 'Activar modulo en sucursal'
                ],
                'ConfiguracionUsuarios' => [
                    'etiqueta' => 'Permisos a usuarios'
                ],
                'ConfiguracionParametros' => [
                    'etiqueta' => 'Parámetros de operación'
                ]
            ]
        ],
        'Log Transaccional' => [
            'iconoON' => 'https://cdn-icons-png.flaticon.com/512/10491/10491362.png',
            'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/10491/10491361.png',
            'subMenu' => [
                'Log' => [
                    'etiqueta' => 'Historial transacciones'
                ]
            ]
        ],
        "Layout's" => [
            'iconoON' => 'https://cdn-icons-png.flaticon.com/512/7310/7310500.png',
            'iconoOFF' => 'https://cdn-icons-png.flaticon.com/512/7310/7310480.png',
            'subMenu' => [
                'LayoutPagosCredito' => [
                    'etiqueta' => 'Layout Contable'
                ],
            ],
        ],
    ];

    public static function mostrar()
    {
        self::$opcPrincipal = 'Admin. Sucursales';
        return self::getMenus(self::$opcionesMenu);
    }
}
