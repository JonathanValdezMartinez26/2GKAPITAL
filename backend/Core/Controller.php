<?php

namespace Core;

defined("APPPATH") or die("Access denied");


class Controller
{
    public $showError = 'const showError = (mensaje) => swal({ text: mensaje, icon: "error" })';
    public $showSuccess = 'const showSuccess = (mensaje) => swal({ text: mensaje, icon: "success" })';
    public $showInfo = 'const showInfo = (mensaje) => swal({ text: mensaje, icon: "info" })';
    public $showWarning = 'const showWarning = (mensaje) => swal({ text: mensaje, icon: "warning" })';
    public $confirmarMovimiento = <<<JAVASCRIPT
        const confirmarMovimiento = async (titulo, mensaje, html = null) => {
            return await swal({ title: titulo, content: html, text: mensaje, icon: "warning", buttons: ["No", "Si, continuar"], dangerMode: true })
        }
    JAVASCRIPT;
    public $configuraTabla = <<<JAVASCRIPT
        const configuraTabla = (id, {noRegXvista = true} = {}) => {
            const configuracion = {
                lengthMenu: [
                    [10, 40, -1],
                    [10, 40, "Todos"]
                ],
                order: [],
                autoWidth: false,
                language: {
                    emptyTable: "No hay datos disponibles",
                    paginate: {
                        previous: "Anterior",
                        next: "Siguiente",
                    },
                    info: "Mostrando de _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "Sin registros para mostrar",
                    zeroRecords: "No se encontraron registros",
                    lengthMenu: "Mostrar _MENU_ registros por página",
                    search: "Buscar:",
                },
                createdRow: (row) => {
                    $(row).find('td').css('vertical-align', 'middle');
                }
            }

            configuracion.lengthChange = noRegXvista

            $("#" + id).DataTable(configuracion)
        }
    JAVASCRIPT;
    public $actualizaDatosTabla = <<<JAVASCRIPT
        const actualizaDatosTabla = (id, datos) => {
            const tabla = $("#" + id).DataTable()
            tabla.clear().draw()
            datos.forEach((item) => {
                if (Array.isArray(item)) tabla.row.add(item)
                else tabla.row.add(Object.values(item))
            })
        }
    JAVASCRIPT;
    public $consultaServidor = <<<JAVASCRIPT
        const consultaServidor = (url, datos, fncOK, metodo = "POST", tipo = "JSON", tipoContenido = null) => {
            swal({ text: "Procesando la solicitud, espere un momento...", icon: "/img/wait.gif", button: false, closeOnClickOutside: false, closeOnEsc: false })
            const configuracion = {
                type: metodo,
                url: url,
                data: datos,
                success: (res) => {
                    if (tipo === "JSON") {
                        try {
                            res = JSON.parse(res)
                        } catch (error) {
                            console.error(error)
                            res =  {
                                success: false,
                                mensaje: "Ocurrió un error al procesar la respuesta del servidor."
                            }
                        }
                    }
                    if (tipo === "blob") res = new Blob([res], { type: "application/pdf" })

                    swal.close()
                    fncOK(res)
                },
                error: (error) => {
                    console.error(error)
                    showError("Ocurrió un error al procesar la solicitud.")
                }
            }
            if (tipoContenido) configuracion.contentType = tipoContenido 
            $.ajax(configuracion)
        }
    JAVASCRIPT;
    public $imprimeTicket = <<<JAVASCRIPT
        const imprimeTicket = async (ticket, sucursal = '', copia = true, otro = null) => {
            const espera = swal({ text: "Procesando la solicitud, espere un momento...", icon: "/img/wait.gif", button: false, closeOnClickOutside: false, closeOnEsc: false })
            const rutaImpresion = 'http://127.0.0.1:5005/api/impresora/ticket'
            const host = window.location.origin
            const titulo = 'Ticket: ' + ticket
            const ruta = host + '/Ahorro/Ticket' + (otro ? '_' + otro : '') + '/?'
            + 'ticket=' + ticket
            + '&sucursal=' + sucursal
            + (copia ? '&copiaCliente=true' : '')
            
            //muestraPDF(titulo, ruta)
            fetch(ruta, {
                method: 'GET'
            })
            .then(resp => resp.blob())
            .then(blob => {
                const datos = new FormData()
                datos.append('ticket', blob)
                
                fetch(rutaImpresion, {
                    method: 'POST',
                    body: datos
                })
                .then(resp => resp.json())
                .then(res => {
                    if (!res.success) return showError(res.mensaje)
                    showSuccess(res.mensaje)
                })
                .catch(error => {
                    console.error(error)
                    showError('El servicio de impresión no está disponible.')
                })
            })
            .catch(error => {
                console.error(error)
                showError('Ocurrió un error al generar el ticket.')
            })
        }
    JAVASCRIPT;
    public $validaFIF = 'const validaFIF = (idI = "fechaI", idF = "fechaF") => {
        const fechaI = document.getElementById(idI).value
        const fechaF = document.getElementById(idF).value
        if (fechaI && fechaF && fechaI > fechaF) {
            document.getElementById(idI).value = fechaF
        }
    }';
    public $descargaExcel = <<<JAVASCRIPT
        const descargaExcel = (url) => {
            swal({ text: "Generando archivo, espere un momento...", icon: "/img/wait.gif", closeOnClickOutside: false, closeOnEsc: false })
            const ventana = window.open(url, "_blank")
            const intervalo = setInterval(() => {
                if (ventana.closed) {
                    clearInterval(intervalo)
                    swal.close()
                }
            }, 1000)

            window.focus()
        }
    JAVASCRIPT;

    public $__usuario = '';
    public $__nombre = '';
    public $__puesto = '';
    public $__cdgco = '';
    public $__cdgco_ahorro = '';
    public $__perfil = '';
    public $__ahorro = '';
    public $__hora_inicio_ahorro = '';
    public $__hora_fin_ahorro = '';

    public function __construct()
    {
        session_start();
        if ($_SESSION['usuario'] == '' || empty($_SESSION['usuario'])) {
            unset($_SESSION);
            session_unset();
            session_destroy();
            header("Location: /Login/");
            exit();
        } else {
            $this->__usuario = $_SESSION['usuario'];
            $this->__nombre = $_SESSION['nombre'];
            $this->__puesto = $_SESSION['puesto'];
            $this->__cdgco = $_SESSION['cdgco'];
            $this->__perfil = $_SESSION['perfil'];
            $this->__ahorro = $_SESSION['ahorro'];
            $this->__cdgco_ahorro = $_SESSION['cdgco_ahorro'];
            $this->__hora_inicio_ahorro = $_SESSION['inicio'];
            $this->__hora_fin_ahorro = $_SESSION['fin'];
        }
    }

    public function GetExtraHeader($titulo, $elementos = [])
    {
        $html = <<<html
        <title>$titulo</title>
        html;

        if (!empty($elementos)) {
            foreach ($elementos as $elemento) {
                $html .= "\n" . $elemento;
            }
        }

        return $html;
    }
}
