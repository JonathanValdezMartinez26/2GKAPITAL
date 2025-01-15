<?php

namespace App\components;

use App\components\AhorroMenu;
use App\components\AhorroSubMenu;

class AhorroMenus
{
    public static $opcPrincipal = '';
    private static $ruta = '';

    public static function getMenus($opcionesMenu)
    {
        $segmentos = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $controlador = $segmentos[0];
        $activa = $segmentos[1];
        $ruta = self::$opcPrincipal . ' / ';

        foreach ($opcionesMenu as $opcion => $configuracion) {
            if (array_key_exists($activa, $configuracion['subMenu'])) {
                $opcionesSubmenu = $configuracion['subMenu'];
                $ruta .= $opcion . ' / ' . $configuracion['subMenu'][$activa]['etiqueta'];
                break;
            }
        }

        $menu = AhorroMenu::getMenu($opcionesMenu, $controlador, $activa);
        
        $subMenu = <<<HTML
            <div class="navbar-header card col-md-12" style="background: #2b2b2b">
                <a class="navbar-brand">$ruta</a>
            </div>
        HTML;
        $subMenu .= AhorroSubMenu::getMenu($opcionesSubmenu, $controlador, $activa);
        
        return [$menu, $subMenu];
    }
}
