<?php

namespace App\components;

class AhorroMenu
{
    private static function getOpcion($ruta, $icono, $etiqueta, $activa)
    {
        $clase = $activa ? '' : 'opcMenuAhorroInactiva';
        $url = $activa ? '#' : $ruta;

        return <<<HTML
            <div class="col-md-6" style="margin: 0; padding: 0">
                <div class="opcMenuAhorro {$clase}">
                    <a class="link" href="{$url}">
                        <img src="{$icono}" alt="{$etiqueta}" width="100" height="100">
                        <p style="margin: 0; padding: 0; font-size: 12px; color: #000000"><b>{$etiqueta}</b></p>
                    </a>
                </div>
            </div>
        HTML;
    }

    public static function getMenu($opciones, $controlador, $seleccionada)
    {
        $menu = '';
        foreach ($opciones as $opcion => $configuracion) {
            $inicial = array_key_first($configuracion['subMenu']);
            $ruta = '/' . ($configuracion['controlador'] ?? $controlador) . '/' . $inicial . '/';
            $activa = array_key_exists($seleccionada, $configuracion['subMenu']);
            $icnono = $activa ? $configuracion['iconoON'] : $configuracion['iconoOFF'];
            $menu .= self::getOpcion($ruta, $icnono, $opcion, $activa);
        }

        return <<<HTML
            <div class="col-md-3 panel panel-body">
                $menu
            </div>
            <style>
                .opcMenuAhorro {
                    margin: 5px;
                    padding: 5px;
                    text-align: center;
                    border: 1px solid #dfdfdf;
                    border-radius: 10px;
                    transform: scale(var(--escala, .9));
                    transition: transform 0.25s;
                    & a {
                        cursor: default;
                    }
                }

                .opcMenuAhorroInactiva:hover {
                    --escala: 1.1;
                    cursor: pointer;
                    & a {
                        cursor: pointer;
                    }
                }
            </style>
        HTML;
    }
}
