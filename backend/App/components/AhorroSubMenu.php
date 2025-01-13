<?php

namespace App\components;

class AhorroSubMenu
{
    public static function getMenu($opciones, $controlador, $seleccionada)
    {
        $subMenu = '';
        foreach ($opciones as $opcion => $configuracion) {
            $c = $configuracion['controlador'] ?? $controlador;
            if ($opcion === $seleccionada) $subMenu .= "<li class='opcSubMenuAhorroActiva'><a href='#'><p>{$configuracion['etiqueta']}</p></a></li>";
            else $subMenu .= "<li class='opcSubMenuAhorroinactiva'><a href='/$c/$opcion/'><p>{$configuracion['etiqueta']}</p></a></li>";
        }

        return <<<HTML
            <div>
                <ul class="nav navbar-nav">
                    $subMenu
                </ul>
            </div>

            <style>
                .opcSubMenuAhorroActiva > a p {
                    font-weight: bold;
                    cursor: default;
                    font-size: 16px;
                }
                .opcSubMenuAhorroinactiva > a p {
                    cursor: pointer;
                    font-size: 15px;
                }
                .opcSubMenuAhorroinactiva:hover > a p {
                    text-decoration: underline;
                }
            </style>
        HTML;
    }
}
