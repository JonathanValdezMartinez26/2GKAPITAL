<?php

namespace App\components;

use App\components\AhorroMenu;
use App\components\AhorroSubMenu;

class AhorroMenus
{
    public static function getMenus($opcionesMenu)
    {
        $segmentos = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $controlador = $segmentos[0];
        $activa = $segmentos[1];

        foreach ($opcionesMenu as $opcion => $configuracion) {
            if (array_key_exists($activa, $configuracion['subMenu'])) {
                $opcionesSubmenu = $configuracion['subMenu'];
                break;
            }
        }

        $menu = AhorroMenu::getMenu($opcionesMenu, $controlador, $activa);
        $subMenu = AhorroSubMenu::getMenu($opcionesSubmenu, $controlador, $activa);
        return [$menu, $subMenu];
    }
}
