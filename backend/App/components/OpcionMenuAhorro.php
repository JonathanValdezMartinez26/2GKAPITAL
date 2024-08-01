<?php

namespace App\components;

class OpcionMenuAhorro
{
    private $ruta;
    private $icono;
    private $iconoON;
    private $iconoOFF;
    private $etiqueta;

    public function construct($ruta, $iconoON, $iconoOFF, $etiqueta)
    {
        $this->ruta = $ruta;
        $this->iconoON = $iconoON;
        $this->iconoOFF = $iconoOFF;
        $this->icono = $iconoOFF;
        $this->etiqueta = $etiqueta;
    }

    public function construct1($datos)
    {
        $this->ruta = $datos['ruta'];
        $this->iconoON = $datos['iconoON'];
        $this->iconoOFF = $datos['iconoOFF'];
        $this->icono = $datos['iconoOFF'];
        $this->etiqueta = $datos['etiqueta'];
    }

    public function toogleICONO()
    {
        $this->icono = ($this->icono == $this->iconoON) ? $this->iconoOFF : $this->iconoON;
            
    }

    public function mostrar()
    {
        return <<<HTML
        <a id="link" href="$this->ruta">
            <div class="col-md-5" style="margin-top: 5px; margin-left: 10px; margin-right: 30px; border: 1px solid #dfdfdf; border-radius: 10px;">
                <img src="$this->icono" style="border-radius: 3px; padding-top: 5px;" width="110" height="110">
                <p style="font-size: 12px; padding-top: 5px; color: #000000"><b>$this->etiqueta</b></p>
            </div>
        </a>
        HTML;
    }
}