<?php

namespace App\components;

/**
 * Clase TarjetaDedo
 * 
 * Representa un componente que muestra una tarjeta de dedo para la captura de huellas.
 */
class TarjetaDedo
{
    private $dedo;
    private $id;
    private $modoValidacion;

    /**
     * Constructor de la clase TarjetaDedo.
     * 
     * @param string $mano La mano a la que pertenece el dedo.
     * @param int $dedo El número del dedo, del 1 al 5.
     * @param bool $modoValidacion (Opcional) Especifica si la tarjeta no debe mostrar los elementos para el registro inicial. Por defecto true.
     */
    public function __construct($mano, $dedo, $modoValidacion = true)
    {
        $this->dedo = $dedo;
        $this->id = $mano . $dedo;
        $this->modoValidacion = $modoValidacion;
    }

    /**
     * Método para mostrar la tarjeta de dedo.
     * 
     * @return string El código HTML del componente.
     */
    public function mostrar()
    {
        $estilo = 'style="
        width: 150px;
        border: 1px solid #000;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        align-items: center;
        grid-area: dedo;
        ' . (!$this->modoValidacion ? "height: 200px;" : "height: 300px;") . '
        "';

        return '<div class="huella-contenedor" id="' . $this->id . '" ' . $estilo . '>' .
            self::dedos() .
            self::imagen() .
            self::etiqueta() .
            self::boton() .
            '</div>
        ';
    }

    /**
     * Método para generar el elemento HTML que permite seleccionar el dedo a capturar.
     * Se muestra solo si el modo de validación está activado.
     * 
     * @return string El código HTML del selector.
     */
    private function dedos()
    {
        if (!$this->modoValidacion) return '';

        $dedos = [
            1 => 'Pulgar',
            2 => 'Índice',
            3 => 'Medio',
            4 => 'Anular',
            5 => 'Meñique'
        ];

        $opciones = '';
        foreach ($dedos as $key => $dedo) {
            $opciones .= '<option value="' . $key . '"' . ($key == $this->dedo ? 'selected' : '') . '>' . $dedo . '</option>';
        }

        return '
        <select id="selector_' . $this->id . '" class="form-control mr-sm-3" style="width: 100px;">
            ' . $opciones . '
        </select>
        ';
    }

    /**
     * Método para generar el elemento HTML de la imagen de la huella.
     * 
     * @return string El código HTML de la imagen de la huella.
     */
    public function imagen()
    {
        return '
        <svg id="imagen_' . $this->id . '" class="huella" version="1.1" xmlns="http://www.w3.org/2000/svg"
            xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" xml:space="preserve">
            <defs>
                <linearGradient id="grad_' . $this->id . '" x1="0%" y1="100%" x2="0%" y2="0%">
                    <stop offset="0%" style="stop-color:green;stop-opacity:1" />
                    <stop offset="0%" style="stop-color:green;stop-opacity:1" />
                    <stop offset="0%" style="stop-color:rgb(255,255,255);stop-opacity:1" />
                    <stop offset="100%" style="stop-color:rgb(255,255,255);stop-opacity:1" />
                </linearGradient>
            </defs>
            <path id="fondoHuella" fill="url(#grad_' . $this->id . ')"
                d="M441.214,192.553c0-102.304-82.934-185.238-185.238-185.238S70.737,90.248,70.737,192.553 c0,22.288,0,104.607,0,126.895c0,102.304,82.934,185.238,185.238,185.238s185.238-82.934,185.238-185.238 C441.214,297.159,441.214,214.841,441.214,192.553z">
            </path>
            <path style="fill: rgba(168, 168, 168,.9);"
                d="M255.975,7.314c-8.856,0-17.563,0.636-26.088,1.837c89.95,12.681,159.151,89.953,159.151,183.401 c0,22.288,0,104.607,0,126.895c0,93.448-69.201,170.72-159.151,183.401c8.525,1.202,17.231,1.837,26.088,1.837 c102.304,0,185.238-82.934,185.238-185.238c0-22.288,0-104.607,0-126.895C441.214,90.248,358.279,7.314,255.975,7.314z">
            </path>
            <path fill="#000000"
                d="M212.574,344.898c1.499-0.001,3.011-0.46,4.313-1.412c7.263-5.314,14.148-11.184,20.464-17.448 c16.896-16.755,25.888-43.77,25.32-76.068c-0.006-0.301-0.011-0.548-0.013-0.737c0.17-2.067-0.536-4.192-2.118-5.774 c-2.856-2.855-7.488-2.855-10.344,0c-2.232,2.232-2.19,4.642-2.152,6.769c0.497,28.27-6.959,51.505-20.995,65.424 c-5.8,5.751-12.125,11.144-18.801,16.029c-3.26,2.386-3.969,6.962-1.584,10.222C208.097,343.86,210.32,344.898,212.574,344.898z">
            </path>
            <path fill="#000000"
                d="M63.422,319.448c0,7.8,0.479,15.489,1.385,23.049c-0.007,0.28-0.002,0.562,0.024,0.847c0.04,0.44,0.124,0.864,0.237,1.275 c1.54,11.739,4.147,23.145,7.709,34.125c0.12,0.956,0.421,1.855,0.872,2.659c4.006,11.756,9.117,23.004,15.212,33.619 c0.345,1.179,0.977,2.22,1.806,3.061c7.044,11.76,15.307,22.708,24.612,32.672c0.535,0.875,1.246,1.621,2.08,2.199 c13.177,13.676,28.353,25.409,45.052,34.732c0.399,0.28,0.827,0.517,1.275,0.714c27.419,15.037,58.871,23.6,92.288,23.6 c72.56,0,135.867-40.349,168.684-99.783c0.22-0.327,0.416-0.675,0.584-1.044c14.845-27.286,23.285-58.538,23.285-91.725v-1.455 c0.071-0.585,0.067-1.164,0-1.731v-123.71c0-17.11-2.255-33.702-6.463-49.505c-0.027-0.102-0.051-0.204-0.083-0.305 C419.974,60.637,344.928,0,255.975,0c-44.067,0-85.536,14.488-119.924,41.896c-32.885,26.21-56.54,62.875-66.775,103.404 c-0.388,0.855-0.6,1.747-0.646,2.639c-3.449,14.537-5.208,29.54-5.208,44.613L63.422,319.448 M90.534,384.922 c35.28-1.965,67.27-9.648,95.094-22.875c3.648-1.734,5.2-6.098,3.465-9.746c-1.734-3.649-6.097-5.202-9.746-3.466 c-26.545,12.619-57.272,19.881-91.329,21.585l-2.506,0.03c-2.066-6.892-3.724-13.957-4.947-21.169 c5.662-0.488,11.516-0.968,17.52-1.432c0.164-0.012,0.327-0.03,0.49-0.054c44.339-6.452,75.872-20.576,96.4-43.179 c21.159-23.295,23.11-47.898,22.39-60.49c-0.179-3.127-0.104-5.668,0.22-7.551c3.262-18.908,19.577-32.631,38.792-32.631 c21.706,0,39.365,17.659,39.365,39.365c0,0.202,0.009,0.413,0.026,0.614c0.05,0.601,4.561,60.537-32.818,101.975 c-20.73,22.982-69.399,50.108-143.864,57.322l-18.979,1.953C96.511,398.657,93.305,391.899,90.534,384.922z M298.233,492.288 c27.928-13.252,51.834-31.295,71.062-53.685c8.41-9.793,16.01-20.607,22.589-32.139c0.101-0.177,0.286-0.262,0.426-0.211 l15.576,5.735C383.691,451.557,344.477,480.98,298.233,492.288z M433.899,271.367l-6.901-0.22c-1.772-0.062-3.16-1.506-3.16-3.288 c-0.002-40.77-10.073-80.716-29.125-115.523c-2.746-5.016-5.837-9.944-9.187-14.647c-2.344-3.29-6.911-4.058-10.201-1.713 c-3.29,2.344-4.057,6.91-1.713,10.201c3.017,4.235,5.799,8.67,8.269,13.183c17.877,32.66,27.327,70.178,27.329,108.499 c0,9.705,7.59,17.57,17.3,17.908l7.389,0.236v21.803l-43.936-10.635c-3.48-0.866-5.781-4.199-5.354-7.754 c1.749-14.557,3.451-34.732,1.703-48.589c-0.639-34.073-14.394-66.04-38.76-90.05c-24.492-24.135-56.871-37.428-91.174-37.428 c-30.797,0-60.684,11.001-84.157,30.977c-2.55,2.17-5.045,4.466-7.415,6.823c-2.864,2.849-2.877,7.48-0.029,10.345 c2.85,2.865,7.48,2.877,10.344,0.029c2.104-2.092,4.318-4.13,6.581-6.056c20.829-17.727,47.349-27.489,74.675-27.489 c30.436,0,59.169,11.797,80.906,33.218c21.715,21.399,33.935,49.91,34.408,80.279c0.004,0.283,0.025,0.566,0.062,0.848 c1.278,9.637,0.685,25.742-1.67,45.349c-1.305,10.865,5.722,21.051,16.391,23.706l47.38,11.469 c-0.224,11.862-1.615,23.441-4.062,34.632l-45.219-15.112c-13.354-4.463-27.988,1.698-34.04,14.334 c-8.107,16.93-18.282,32.314-30.244,45.728c-15.113,16.948-33.224,31.147-53.83,42.204c-3.56,1.91-4.897,6.344-2.987,9.903 c1.32,2.459,3.843,3.858,6.451,3.858c1.167-0.001,2.351-0.281,3.452-0.871c22.119-11.868,41.577-27.129,57.832-45.357 c12.877-14.44,23.818-30.974,32.52-49.146c2.871-5.993,9.839-8.906,16.208-6.778l46.14,15.42 c-2.919,9.49-6.615,18.641-11.008,27.385l-17.738-6.531c-6.881-2.498-14.514,0.322-18.154,6.702 c-6.118,10.724-13.176,20.77-20.98,29.856c-18.402,21.429-41.447,38.613-68.44,51.052c-0.167,0.075-16.871,7.509-51.809,16.34 c-16.876-1.706-33.058-5.787-48.218-11.891c23.059-4.924,39.837-9.688,50.654-13.138c3.849-1.228,5.973-5.343,4.745-9.191 c-1.228-3.849-5.342-5.97-9.191-4.746c-13.212,4.215-35.626,10.455-68.004,16.52c-10.732-6.137-20.762-13.364-29.95-21.522 l32.878-3.609c0.144-0.016,0.289-0.036,0.431-0.06c43.837-7.476,80.39-22.654,108.643-45.113 c22.89-18.195,40.38-41.146,51.982-68.215c1.591-3.713-0.129-8.013-3.841-9.605s-8.013,0.129-9.604,3.841 c-10.638,24.819-26.666,45.856-47.639,62.528c-26.258,20.872-60.501,35.038-101.786,42.108l-44.499,4.885 c-5.792-6.348-11.131-13.114-15.967-20.249l11.959-1.231c65.747-6.37,124.477-30.157,153.272-62.08 c40.662-45.076,36.984-107.08,36.558-112.69c-0.162-29.634-24.32-53.691-53.992-53.691c-26.355,0-48.732,18.83-53.208,44.772 c-0.521,3.021-0.655,6.578-0.41,10.873c0.589,10.304-1.046,30.478-18.614,49.822c-18.119,19.949-46.719,32.544-87.436,38.502 c-6.169,0.478-12.185,0.972-17.994,1.474c-0.432-5.048-0.666-10.151-0.666-15.309v-6.397c97.91-9.453,103.218-53.339,102.448-66.818 c-0.344-6.013-0.116-11.228,0.698-15.94c3.017-17.486,12.182-33.502,25.807-45.097c13.775-11.724,31.31-18.18,49.375-18.18 c42.068,0,76.292,34.224,76.292,76.291c0,0.283,0.018,0.575,0.05,0.856c0.027,0.234,2.659,23.833-4.142,54.143 c-0.885,3.942,1.594,7.854,5.535,8.739c3.944,0.886,7.854-1.593,8.738-5.536c6.932-30.893,4.831-54.925,4.446-58.61 c-0.223-49.944-40.923-90.511-90.92-90.511c-21.535,0-42.437,7.695-58.856,21.668c-16.226,13.81-27.144,32.899-30.741,53.75 c-1.005,5.823-1.295,12.124-0.887,19.262c0.298,5.208-0.853,15.476-11.902,25.731c-14.194,13.175-40.357,21.957-75.941,25.558 v-24.552c14.719-1.621,27.445-4.191,37.9-7.662c14.423-4.79,24.494-17.878,25.655-33.346c0.233-3.103,0.61-6.18,1.122-9.145 c2.201-12.761,6.538-25.006,12.89-36.393c1.968-3.528,0.703-7.983-2.825-9.95c-3.529-1.97-7.983-0.703-9.951,2.825 c-7.158,12.834-12.047,26.639-14.529,41.031c-0.591,3.424-1.027,6.97-1.294,10.538c-0.718,9.564-6.872,17.632-15.678,20.556 c-9.116,3.027-20.291,5.313-33.29,6.823v-24.543c13.538-1.783,24.493-11.97,27.29-25.411c0.899-4.322,1.67-7.655,2.287-9.891 c18.418-66.182,79.586-112.405,148.75-112.405c35.315,0,69.221,12.576,95.471,35.412c3.047,2.652,7.668,2.33,10.319-0.717 c2.651-3.048,2.33-7.668-0.718-10.319C332.535,86.06,295.22,72.207,256.376,72.207c-75.708,0-142.672,50.626-162.848,123.128 c-0.704,2.552-1.549,6.192-2.511,10.817c-1.395,6.703-6.49,11.915-12.968,13.519v-27.114c0-14.196,1.676-28.321,4.981-41.986 c27.236-50.811,74.923-86.964,131.055-99.27c3.946-0.865,6.443-4.765,5.579-8.711c-0.865-3.946-4.769-6.441-8.711-5.578 c-38.565,8.455-73.412,27.389-101.186,54.204c9.852-14.2,21.766-27.01,35.399-37.876c31.771-25.322,70.088-38.707,110.807-38.707 c59.511,0,112.287,29.373,144.605,74.379C362.012,52.996,310.7,32.11,256.378,32.11c-3.426,0-6.9,0.084-10.325,0.249 c-4.035,0.194-7.148,3.623-6.954,7.657s3.612,7.153,7.657,6.954c3.192-0.153,6.43-0.231,9.622-0.231 c71.311,0,137.052,38.671,171.796,100.978c3.734,14.329,5.726,29.354,5.726,44.836v78.814H433.899z">
            </path>
        </svg>
        ';
    }

    /**
     * Método para generar el elemento HTML de la etiqueta que indica el numero de muestras capturadas.
     * Se muestra solo si el modo de validación está activado.
     * 
     * @return string El código HTML de la etiqueta para el dedo.
     */

    private function etiqueta()
    {
        return !$this->modoValidacion ? '' : '<span id="etiqueta_' . $this->id . '" style="font-size: medium; height: 15px"></span>';
    }

    /**
     * Método para generar el elemento HTML del botón para reiniciar la captura de la huella.
     * Se muestra solo si el modo de validación está activado.
     * 
     * @return string El código HTML del botón para limpiar la huella.
     */
    private function boton()
    {
        return !$this->modoValidacion ? '' : '<button id="boton_' . $this->id . '" class="btn btn-primary btnHuella">Limpiar</button>';
    }
}