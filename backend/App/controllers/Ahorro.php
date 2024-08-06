<?php

namespace App\controllers;

defined("APPPATH") or die("Access denied");

use \Core\View;
use \Core\Controller;
use \Core\MasterDom;
use \App\models\CajaAhorro as CajaAhorroDao;
use \App\models\Ahorro as AhorroDao;
use \App\components\TarjetaDedo;
use DateTime;

class Ahorro extends Controller
{
    private $_contenedor;
    private $operacionesNulas = [2, 5]; // [Comisión, Transferencia]
    private $urlHuellas = 'http://18.117.29.228:8008/huellas/endpoints/';
    private $XLSX = '<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" integrity="sha512-r22gChDnGvBylk90+2e/ycr3RVrDi8DIOkIGNhJlKfuyQM4tIRAI062MaV8sfjQKYVGjOBaZBOA87z+IhZE9DA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>';
    private $swal2 = '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    private $huellas = '<script src="/js/huellas/es6-shim.js"></script><script src="/js/huellas/fingerprint.sdk.min.js"></script><script src="/js/huellas/huellas.js"></script><script src="/js/huellas/websdk.client.bundle.min.js"></script>';
    private $showError = 'const showError = (mensaje) => swal({ text: mensaje, icon: "error" })';
    private $showSuccess = 'const showSuccess = (mensaje) => swal({ text: mensaje, icon: "success" })';
    private $showInfo = 'const showInfo = (mensaje) => swal({ text: mensaje, icon: "info" })';
    private $showWarning = 'const showWarning = (mensaje) => swal({ text: mensaje, icon: "warning" })';
    private $showBloqueo = 'const showBloqueo = (mensaje) => {
        Swal.fire({
            html: mensaje,
            icon: "warning",
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showConfirmButton: false,
            target: document.getElementById("bloqueoAhorro"),
            customClass: {
                container: "sweet-bloqueoAhorro-container",
                popup: "sweet-bloqueoAhorro-popup",
            }
        })
    }';
    private $confirmarMovimiento = 'const confirmarMovimiento = async (titulo, mensaje, html = null) => {
        return await swal({ title: titulo, content: html, text: mensaje, icon: "warning", buttons: ["No", "Si, continuar"], dangerMode: true })
    }';
    private $validarYbuscar = 'const validarYbuscar = (e, t) => {
        if (e.keyCode < 9 || e.keyCode > 57) e.preventDefault()
        if (e.keyCode === 13) buscaCliente(t)
    }';
    private $buscaCliente = <<<script
    const buscaCliente = (t) => {
        document.querySelector("#btnBskClnt").disabled = true
        const noCliente = document.querySelector("#clienteBuscado").value
         
        if (!noCliente) {
            limpiaDatosCliente()
            document.querySelector("#btnBskClnt").disabled = false
            return showError("Ingrese un número de cliente a buscar.")
        }
        
        consultaServidor("/Ahorro/BuscaContratoAhorro/", { cliente: noCliente }, (respuesta) => {
                limpiaDatosCliente()
                if (!respuesta.success) {
                    if (respuesta.datos && !sinContrato(respuesta.datos)) return
                     
                    limpiaDatosCliente()
                    return showError(respuesta.mensaje)
                }
                 
                if (respuesta.datos.SUCURSAL !== noSucursal) {
                    limpiaDatosCliente()
                    return showError("El cliente " + noCliente + " no puede realizar transacciones en esta sucursal, su contrato esta asignado a la sucursal " + respuesta.datos.NOMBRE_SUCURSAL + ", contacte a la gerencia de Administración.")
                }
                 
                llenaDatosCliente(respuesta.datos)
            })
        
        document.querySelector("#btnBskClnt").disabled = false
    }
    script;
    private $getHoy = 'const getHoy = (completo = true) => {
        const hoy = new Date()
        const dd = String(hoy.getDate()).padStart(2, "0")
        const mm = String(hoy.getMonth() + 1).padStart(2, "0")
        const yyyy = hoy.getFullYear()
        const r = dd + "/" + mm + "/" + yyyy
        return completo ? r  + " " + hoy.getHours().toString().padStart(2, "0") + ":" + hoy.getMinutes().toString().padStart(2, "0") + ":" + hoy.getSeconds().toString().padStart(2, "0") : r
    }';
    private $soloNumeros = 'const soloNumeros = (e) => {
        valKD = false
        if (
            !(e.key >= "0" && e.key <= "9") &&
            e.key !== "." &&
            e.key !== "Backspace" &&
            e.key !== "Delete" &&
            e.key !== "ArrowLeft" &&
            e.key !== "ArrowRight" &&
            e.key !== "ArrowUp" &&
            e.key !== "ArrowDown" &&
            e.key !== "Tab"
        ) e.preventDefault()
        if (e.key === "." && e.target.value.includes(".")) e.preventDefault()
        valKD = true
    }';
    private $numeroLetras = 'const numeroLetras = (numero) => {
        if (!numero) return ""
        const unidades = ["", "un", "dos", "tres", "cuatro", "cinco", "seis", "siete", "ocho", "nueve"]
        const especiales = [
            "",
            "once",
            "doce",
            "trece",
            "catorce",
            "quince",
            "dieciséis",
            "diecisiete",
            "dieciocho",
            "diecinueve",
            "veinte",
            "veintiún",
            "veintidós",
            "veintitrés",
            "veinticuatro",
            "veinticinco",
            "veintiséis",
            "veintisiete",
            "veintiocho",
            "veintinueve"
        ]
        const decenas = [
            "",
            "diez",
            "veinte",
            "treinta",
            "cuarenta",
            "cincuenta",
            "sesenta",
            "setenta",
            "ochenta",
            "noventa"
        ]
        const centenas = [
            "cien",
            "ciento",
            "doscientos",
            "trescientos",
            "cuatrocientos",
            "quinientos",
            "seiscientos",
            "setecientos",
            "ochocientos",
            "novecientos"
        ]
    
        const convertirMenorA1000 = (numero) => {
            let letra = ""
            if (numero >= 100) {
                letra += centenas[(numero === 100 ? 0 : Math.floor(numero / 100))] + " "
                numero %= 100
            }
            if (numero === 10 || numero === 20 || (numero > 29 && numero < 100)) {
                letra += decenas[Math.floor(numero / 10)]
                numero %= 10
                letra += numero > 0 ? " y " : " "
            }
            if (numero != 20 && numero >= 11 && numero <= 29) {
                letra += especiales[numero % 10 + (numero > 20 ? 10 : 0)] + " "
                numero = 0
            }
            if (numero > 0) {
                letra += unidades[numero] + " "
            }
            return letra.trim()
        }
    
        const convertir = (numero) => {
            if (numero === 0) {
                return "cero"
            }
        
            let letra = ""
        
            if (numero >= 1000000) {
                letra += convertirMenorA1000(Math.floor(numero / 1000000)) + (numero === 1000000 ? " millón " : " millones ")
                numero %= 1000000
            }
        
            if (numero >= 1000) {
                letra += (numero === 1000 ? "" : convertirMenorA1000(Math.floor(numero / 1000))) + " mil "
                numero %= 1000
            }
        
            letra += convertirMenorA1000(numero)
            return letra.trim()
        }
    
        const parteEntera = Math.floor(numero)
        const parteDecimal = Math.round((numero - parteEntera) * 100).toString().padStart(2, "0")
        return primeraMayuscula(convertir(parteEntera)) + (numero == 1 ? " peso " : " pesos ") + parteDecimal + "/100 M.N."
    }';
    private $primeraMayuscula = 'const primeraMayuscula = (texto) => texto.charAt(0).toUpperCase() + texto.slice(1)';
    private $muestraPDF = <<<script
    const muestraPDF = (titulo, ruta) => {
        let plantilla = '<!DOCTYPE html>'
            plantilla += '<html lang="es">'
            plantilla += '<head>'
            plantilla += '<meta charset="UTF-8">'
            plantilla += '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
            plantilla += '<link rel="shortcut icon" href="" + host + "/img/logo.png">'
            plantilla += '<title>' + titulo + '</title>'
            plantilla += '</head>'
            plantilla += '<body style="margin: 0; padding: 0; background-color: #333333;">'
            plantilla += '<iframe src="' + ruta + '" style="width: 100%; height: 99vh; border: none; margin: 0; padding: 0;"></iframe>'
            plantilla += '</body>'
            plantilla += '</html>'
        
            const blob = new Blob([plantilla], { type: 'text/html' })
            const url = URL.createObjectURL(blob)
            window.open(url, '_blank')
    }
    script;
    private $imprimeTicket = <<<script
    const imprimeTicket = async (ticket, sucursal = '', copia = true) => {
        const espera = swal({ text: "Procesando la solicitud, espere un momento...", icon: "/img/wait.gif", button: false, closeOnClickOutside: false, closeOnEsc: false })
        const rutaImpresion = 'http://127.0.0.1:5005/api/impresora/ticket'
        const host = window.location.origin
        const titulo = 'Ticket: ' + ticket
        const ruta = host + '/Ahorro/Ticket/?'
        + 'ticket=' + ticket
        + '&sucursal=' + sucursal
        + (copia ? '&copiaCliente=true' : '')
         
        // muestraPDF(titulo, ruta)
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
    script;
    private $valida_MCM_Complementos = 'const valida_MCM_Complementos = async () => {
        swal({ text: "Procesando la solicitud, espere un momento...", icon: "/img/wait.gif", button: false, closeOnClickOutside: false, closeOnEsc: false })
        
        let resultado = false
        try {
            const res = await fetch("http://localhost:5005/api/impresora/verificar")
            if (res.ok) {
                swal.close()
                resultado = true
            } else {
                const r = await res.json()
                showError(r.estatus.impresora.mensaje.replaceAll("<br>", "\\n", "g"))
            }
        } catch (error) {
            showError("El servicio de impresión no está disponible.")
        }

        return resultado
    }';
    private $imprimeContrato = <<<script
    const imprimeContrato = (numero_contrato, producto = 1) => {
        if (!numero_contrato) return
        const host = window.location.origin
        const titulo = 'Contrato ' + numero_contrato
        const ruta = host
            + '/Ahorro/Contrato/?'
            + 'contrato=' + numero_contrato
            + '&producto=' + producto
         
        muestraPDF(titulo, ruta)
    }
    script;
    private $sinContrato = <<<script
    const sinContrato = (datosCliente) => {
        if (datosCliente["NO_CONTRATOS"] == 0) {
            swal({
                title: "Cuenta de ahorro corriente",
                text: "El cliente " + datosCliente['CDGCL'] + " no tiene una cuenta de ahorro.\\n¿Desea aperturar una cuenta de ahorro en este momento?",
                icon: "info",
                buttons: ["No", "Sí"],
                dangerMode: true
            }).then((abreCta) => {
                if (abreCta) {
                    window.location.href = "/Ahorro/ContratoCuentaCorriente/?cliente=" + datosCliente['CDGCL']
                    return
                }
            })
            return false
        }
        const msj2 = (typeof mEdoCta !== 'undefined') ? "No podemos generar un estado de cuenta para el cliente  " + datosCliente['CDGCL'] + ", porque este no ha concluido con su proceso de apertura de la cuenta de ahorro corriente.\\n¿Desea completar el proceso en este momento?" 
        : "El cliente " + datosCliente['CDGCL'] + " no ha completado el proceso de apertura de la cuenta de ahorro.\\n¿Desea completar el proceso en este momento?"
        if (datosCliente["NO_CONTRATOS"] == 1 && datosCliente["CONTRATO_COMPLETO"] == 0) {
            swal({
                title: "Cuenta de ahorro corriente",
                text: msj2,
                icon: "info",
                buttons: ["No", "Sí"],
                dangerMode: true
            }).then((abreCta) => {
                if (abreCta) {
                    window.location.href = "/Ahorro/ContratoCuentaCorriente/?cliente=" + datosCliente['CDGCL']
                    return
                }
            })
            return false
        }
        return true
    }
    script;
    private $addParametro = 'const addParametro = (parametros, newParametro, newValor) => {
        parametros.push({ name: newParametro, value: newValor })
    }';
    private $consultaServidor = 'const consultaServidor = (url, datos, fncOK, metodo = "POST", tipo = "JSON", tipoContenido = null) => {
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
    }';
    private $parseaNumero = 'const parseaNumero = (numero) => parseFloat(numero.replace(/[^0-9.-]/g, "")) || 0';
    private $formatoMoneda = 'const formatoMoneda = (numero) => parseFloat(numero).toLocaleString("es-MX", { minimumFractionDigits: 2, maximumFractionDigits: 2 })';
    private $limpiaMontos = 'const limpiaMontos = (datos, campos = []) => {
        datos.forEach(dato => {
            if (campos.includes(dato.name)) {
                dato.value = parseaNumero(dato.value)
            }
        })
    }';
    private $noSubmit = 'const noSUBMIT = (e) => e.preventDefault()';
    private $configuraTabla = 'const configuraTabla = (id) => {
        $("#" + id).tablesorter()
        $("#" + id).DataTable({
            lengthMenu: [
                [10, 40, -1],
                [10, 40, "Todos"]
            ],
            columnDefs: [
                {
                    orderable: false,
                    targets: 0
                }
            ],
            order: false,
            language: {
                emptyTable: "No hay datos disponibles",
                paginate: {
                    previous: "Anterior",
                    next: "Siguiente",
                }
            }
        })

        $("#"  + id + " input[type=search]").keyup(() => {
            $("#example")
                .DataTable()
                .search(jQuery.fn.DataTable.ext.type.search.html(this.value))
                .draw()
        })
    }';
    private $exportaExcel = 'const exportaExcel = (id, nombreArchivo, nombreHoja = "Reporte") => {
        const tabla = document.querySelector("#" + id)
        const wb = XLSX.utils.book_new()
        const ws = XLSX.utils.table_to_sheet(tabla)
        XLSX.utils.book_append_sheet(wb, ws, nombreHoja)
        XLSX.writeFile(wb, nombreArchivo + ".xlsx")
    }';
    private $validaFIF = 'const validaFIF = (idI, idF) => {
        const fechaI = document.getElementById(idI).value
        const fechaF = document.getElementById(idF).value
        if (fechaI && fechaF && fechaI > fechaF) {
            document.getElementById(idI).value = fechaF
        }
    }';
    private $validaHorarioOperacion = 'const validaHorarioOperacion = (inicio, fin, sinMsj = false) => {
        if ("__PERFIL__" === "ADMIN" || "__USUARIO__" === "AMGM") return

        const horaActual = new Date()
        const horaInicio = new Date()
        const horaFin = new Date()
        const [hi, mi, si] = inicio.split(":")
        const [hf, mf, sf] = fin.split(":")
        
        horaInicio.setHours(hi, mi, si)
        horaFin.setHours(hf, mf, sf)
        if (sinMsj) return horaActual >= horaInicio && horaActual <= horaFin

        if (!(horaActual >= horaInicio && horaActual <= horaFin)) showBloqueo("No es posible realizar operaciones fuera del horario establecido (de " + inicio + " a " + fin + ").<br><br><b>Consulte con la gerencia de administración.</b>")
    }';
    private $showHuella = 'const showHuella = (autorizacion = false, datos =  null) => {
        Swal.fire({
            html: `HTML_HUELLA<span id="mensajeHuella" style="height: 50px;"></span>`,
            allowOutsideClick: false,
            showConfirmButton: false,
            showCloseButton: true,
            target: document.getElementById("bloqueoAhorro"),
            customClass: {
                container: "sweet-bloqueoAhorro-container",
                popup: "sweet-bloqueo-mano-popup",
                htmlContainer: "sweet-bloqueo-mano-htmlContainer",
            }
        })

        const lector = new LectorHuellas({
            notificacion: (mensaje, error = false) => {
                const huella = document.querySelector("#mensajeHuella")
                huella.style.color = error ? "red": ""
                huella.innerText = mensaje
            }
        })
        mano = new Mano("manoIzquierda", lector, document.querySelector(".sweet-bloqueo-mano-htmlContainer"))
        mano.modoAutorizacion()
        mano.datosCliente = datos
        lector.estatus.lecturaOK = "Validando huella.."
        if (autorizacion) {
            document.querySelector(".sweet-bloqueo-mano-htmlContainer").addEventListener("validaHuella", autorizaOperacion)
            lector.estatus.lecturaI = "Autorización del cliente."
        } else {
            document.querySelector(".sweet-bloqueo-mano-htmlContainer").addEventListener("validaHuella", validaHuella)
            lector.estatus.lecturaI = "Identificación del cliente."
            lector.estatus.lecturaOK = "Validando huella..."
        }
    }';
    private $validaHuella = 'const validaHuella = (e) => {
        const datos = {
            muestra: e.detail.muestra
        }
    
        consultaServidor("/Ahorro/ValidaHuella/", datos, (respuesta) => {
            e.detail.colorImagen(respuesta.success ? "green" : "red")
            if (!respuesta.success) {
                e.detail.conteoErrores()
                e.detail.mensajeLector("Haz clic en la imagen para intentar nuevamente.")
                return showError(respuesta.mensaje)
            }

            if (respuesta.cliente) {
                document.querySelector("#clienteBuscado").value = respuesta.cliente
                buscaCliente()
            }
            Swal.close()
        })
    }';
    private $autorizaOperacion = 'const autorizaOperacion = (e) => {
        if (e.detail.erroresValidacion >= 5) {
            showError("Se ha alcanzado el límite de intentos, la operación no se puede completar.")
            .then(() => {
                Swal.close()
                limpiaDatosCliente()
                return
            })
            return
        }

        const datos = {
            muestra: e.detail.muestra
        }

        consultaServidor("/Ahorro/ValidaHuella/", datos, (respuesta) => {
            e.detail.colorImagen(respuesta.success ? "green" : "red")
            if (!respuesta.success) {
                e.detail.conteoErrores()
                e.detail.mensajeLector("Haz clic en la imagen para intentar nuevamente.")
                return showError(respuesta.mensaje)
            }

            Swal.close()
            if (respuesta.cliente && document.querySelector("#cliente").value !== respuesta.cliente) {
                showError("La huella corresponde a otro cliente, comuníquese con su administrador.").
                then(() => {
                    limpiaDatosCliente()
                })
            }
            showSuccess(respuesta.mensaje).then(() => enviaRegistroOperacion(mano.datosCliente))
        })
    }';

    function __construct()
    {
        parent::__construct();
        $this->_contenedor = new Contenedor;
        $tarjetaDedo = new TarjetaDedo("derecha", 1);
        $this->showHuella = str_replace("HTML_HUELLA", $tarjetaDedo->mostrar(), $this->showHuella);
        $this->validaHorarioOperacion = str_replace("__PERFIL__", $_SESSION['perfil'], $this->validaHorarioOperacion);
        $this->validaHorarioOperacion = str_replace("__USUARIO__", $_SESSION['usuario'], $this->validaHorarioOperacion);
        View::set('header', $this->_contenedor->header());
        View::set('footer', $this->_contenedor->footer());
    }

    //********************AHORRO CORRIENTE********************//
    // Apertura de contratos para cuentas de ahorro corriente
    public function ContratoCuentaCorriente()
    {
        $saldosMM = CajaAhorroDao::GetSaldoMinimoApertura($_SESSION['cdgco_ahorro']);
        $saldoMinimoApertura = 650; //$saldosMM['MONTO_MINIMO'];
        $costoInscripcion = 400;
        $mensajeCaptura = "Capture las huellas del cliente haciendo clic sobre una imagen.";

        $extraFooter = <<<html
        <script>
            const saldoMinimoApertura = $saldoMinimoApertura
            const costoInscripcion = $costoInscripcion
            const montoMaximo = 1000000
            const txtGuardaContrato = "GUARDAR DATOS Y PROCEDER AL COBRO"
            const txtGuardaPago = "REGISTRAR DEPÓSITO DE APERTURA"
            let valKD = false
            let manoIzquierda
            let manoDerecha
         
            window.onload = () => {
                const lector = new LectorHuellas(
                {
                    notificacion: (mensaje, error = false) => {
                        const huella = document.querySelector("#mensajeHuella")
                        huella.style.color = error ? "red": ""
         
                        huella.innerText = mensaje
                    }
                })
                    
                manoIzquierda = new Mano("izquierda", lector, document.querySelector("#manoizquierda"))
                manoDerecha = new Mano("derecha", lector, document.querySelector("#manoderecha"))
         
                document.querySelector("#manoizquierda").addEventListener("muestraObtenida", huellasCompletas)
                document.querySelector("#manoderecha").addEventListener("muestraObtenida", huellasCompletas)

                document.querySelector("#manoderecha").addEventListener("validaHuella", validaHuella)
                document.querySelector("#manoizquierda").addEventListener("validaHuella", validaHuella)

                document.querySelector("#manoderecha").addEventListener("actualizaHuella", actualizaHuella)
                document.querySelector("#manoizquierda").addEventListener("actualizaHuella", actualizaHuella)
                
                if(document.querySelector("#clienteBuscado").value !== "") buscaCliente()
            }
         
            {$this->showError}
            {$this->showSuccess}
            {$this->showInfo}
            {$this->confirmarMovimiento}
            {$this->validarYbuscar}
            {$this->getHoy}
            {$this->soloNumeros}
            {$this->numeroLetras}
            {$this->primeraMayuscula}
            {$this->muestraPDF}
            {$this->imprimeTicket}
            {$this->imprimeContrato}
            {$this->addParametro}
            {$this->consultaServidor}
            {$this->parseaNumero}
            {$this->formatoMoneda}
            {$this->limpiaMontos}
            {$this->valida_MCM_Complementos}
             
            const buscaCliente = () => {
                if (document.querySelector("#sucursal").value === "") {
                    showError("Usted no tiene una sucursal asignada.\\n\\nNo es posible continuar con la operación, consulte a su administrador.")
                    return
                }
                 
                const noCliente = document.querySelector("#clienteBuscado").value
                limpiaDatosCliente()
                 
                if (!noCliente) return showError("Ingrese un número de cliente a buscar.")
                 
                consultaServidor("/Ahorro/BuscaCliente/", { cliente: noCliente }, async (respuesta) => {
                    document.querySelector("#lnkContrato").innerText = "Creación del contrato"
                    if (!respuesta.success) {
                        if (!respuesta.datos) {
                            limpiaDatosCliente()
                            return showError(respuesta.mensaje)
                        }
                         
                        const datosCliente = respuesta.datos
                        document.querySelector("#btnGeneraContrato").style.display = "none"
                        document.querySelector("#contratoOK").value = datosCliente.CONTRATO
                        document.querySelector("#fecha").value = datosCliente.FECHA_CONTRATO
                        document.querySelector("#lnkContrato").innerText = "Creación del contrato (" + datosCliente.FECHA_CONTRATO.split(" ")[0] + ")"
                        if (Array.from(document.querySelector("#ejecutivo_comision").options).some(option => option.value === datosCliente.EJECUTIVO_COMISIONA)) {
                            document.querySelector("#ejecutivo_comision").value = datosCliente.EJECUTIVO_COMISIONA
                        } else {
                            document.querySelector("#ejecutivo_comision").appendChild(new Option(datosCliente.NOMBRE_EJECUTIVO_COMISIONA, "tmp", true, true))
                        }
                         
                        if (datosCliente['NO_CONTRATOS'] >= 0 && datosCliente.CONTRATO_COMPLETO == 0) {
                            await showInfo("La apertura del contrato no ha concluido, realice el depósito de apertura.")
                            document.querySelector("#fecha_pago").value = getHoy()
                            document.querySelector("#contrato").value = datosCliente.CONTRATO
                            document.querySelector("#codigo_cl").value = datosCliente.CDGCL
                            document.querySelector("#nombre_cliente").value = datosCliente.NOMBRE
                            document.querySelector("#mdlCurp").value = datosCliente.CURP
                            $("#modal_agregar_pago").modal("show")
                            document.querySelector("#chkCreacionContrato").classList.add("green")
                            document.querySelector("#chkCreacionContrato").classList.add("fa-check")
                            document.querySelector("#chkCreacionContrato").classList.remove("red")
                            document.querySelector("#chkCreacionContrato").classList.remove("fa-times")
                            document.querySelector("#lnkContrato").style.cursor = "pointer"
                            document.querySelector("#chkPagoApertura").classList.remove("green")
                            document.querySelector("#chkPagoApertura").classList.remove("fa-check")
                            document.querySelector("#chkPagoApertura").classList.add("fa-times")
                            document.querySelector("#chkPagoApertura").classList.add("red")
                            document.querySelector("#btnGuardar").innerText = txtGuardaPago
                            document.querySelector("#btnGeneraContrato").style.display = "block"
                        }
                         
                        if (datosCliente['NO_CONTRATOS'] >= 0 && datosCliente.CONTRATO_COMPLETO == 1) {
                            await showInfo("El cliente " + datosCliente.CDGCL + " ya cuenta con un contrato de ahorro corriente aperturada el " + datosCliente.FECHA_CONTRATO + ".")
                            document.querySelector("#chkCreacionContrato").classList.remove("red")
                            document.querySelector("#chkCreacionContrato").classList.remove("fa-times")
                            document.querySelector("#chkCreacionContrato").classList.add("green")
                            document.querySelector("#chkCreacionContrato").classList.add("fa-check")
                            document.querySelector("#lnkContrato").style.cursor = "pointer"
                            document.querySelector("#chkPagoApertura").classList.remove("red")
                            document.querySelector("#chkPagoApertura").classList.remove("fa-times")
                            document.querySelector("#chkPagoApertura").classList.add("green")
                            document.querySelector("#chkPagoApertura").classList.add("fa-check")
                        }
                         
                        consultaServidor("/Ahorro/GetBeneficiarios/", { contrato: datosCliente.CONTRATO }, (respuesta) => {
                            if (!respuesta.success) return showError(respuesta.mensaje)
                             
                            const beneficiarios = respuesta.datos
                            for (let i = 0; i < beneficiarios.length; i++) {
                                document.querySelector("#beneficiario_" + (i + 1)).value = beneficiarios[i].NOMBRE
                                document.querySelector("#parentesco_" + (i + 1)).value = beneficiarios[i].CDGCT_PARENTESCO
                                document.querySelector("#porcentaje_" + (i + 1)).value = beneficiarios[i].PORCENTAJE
                                document.querySelector("#btnBen" + (i + 1)).disabled = true
                                document.querySelector("#parentesco_" + (i + 1)).disabled = true
                                document.querySelector("#porcentaje_" + (i + 1)).disabled = true
                                document.querySelector("#ben" + (i + 1)).style.opacity = "1"
                            }
                        })

                        consultaServidor("/Ahorro/ValidaRegistroHuellas", { cliente: datosCliente.CDGCL }, (respuesta) => {
                            if (!respuesta.success) {
                                console.error(respuesta.error)
                                return showError(respuesta.mensaje)
                            }
                             
                            if (respuesta.datos.HUELLAS == 1) {
                                document.querySelector("#chkRegistroHuellas").classList.remove("red")
                                document.querySelector("#chkRegistroHuellas").classList.remove("fa-times")
                                document.querySelector("#chkRegistroHuellas").classList.add("green")
                                document.querySelector("#chkRegistroHuellas").classList.add("fa-check")
                                document.querySelector("#lnkHuellas").style.cursor = "default"
                            }
                            
                            if (respuesta.datos.HUELLAS == 0) {
                                document.querySelector("#chkRegistroHuellas").classList.remove("green")
                                document.querySelector("#chkRegistroHuellas").classList.remove("fa-check")
                                document.querySelector("#chkRegistroHuellas").classList.add("red")
                                document.querySelector("#chkRegistroHuellas").classList.add("fa-times")
                                document.querySelector("#lnkHuellas").style.cursor = "pointer"
                            }
                        })
                    }
                     
                    const datosCL = respuesta.datos
                     
                    document.querySelector("#fechaRegistro").value = datosCL.FECHA_REGISTRO
                    document.querySelector("#noCliente").value = noCliente
                    document.querySelector("#nombre").value = datosCL.NOMBRE
                    document.querySelector("#curp").value = datosCL.CURP
                    document.querySelector("#edad").value = datosCL.EDAD
                    document.querySelector("#direccion").value = datosCL.DIRECCION
                    document.querySelector("#marcadores").style.opacity = "1"
                    document.querySelector("#codigo_cl_huellas").value = noCliente
                    document.querySelector("#nombre_cliente_huellas").value = datosCL.NOMBRE
                    noCliente.value = ""
                    manoIzquierda.limpiarMano()
                    manoDerecha.limpiarMano()
                    if (respuesta.success) habilitaBeneficiario(1, true)
                })
            }
             
            const habilitaBeneficiario = (numBeneficiario, habilitar) => {
                document.querySelector("#beneficiario_" + numBeneficiario).disabled = !habilitar
                document.querySelector("#tasa").disabled = false
                document.querySelector("#sucursal").disabled = false
            }
             
            const limpiaDatosCliente = () => {
                manoIzquierda.modoCaptura()
                manoDerecha.modoCaptura()
                document.querySelector("#AddPagoApertura").reset()
                document.querySelector("#registroInicialAhorro").reset()
                document.querySelector("#chkCreacionContrato").classList.remove("green")
                document.querySelector("#chkCreacionContrato").classList.remove("fa-check")
                document.querySelector("#chkCreacionContrato").classList.add("red")
                document.querySelector("#chkCreacionContrato").classList.add("fa-times")
                document.querySelector("#lnkContrato").style.cursor = "default"
                document.querySelector("#chkPagoApertura").classList.remove("green")
                document.querySelector("#chkPagoApertura").classList.remove("fa-check")
                document.querySelector("#chkPagoApertura").classList.add("red")
                document.querySelector("#chkPagoApertura").classList.add("fa-times")
                document.querySelector("#chkRegistroHuellas").classList.remove("green")
                document.querySelector("#chkRegistroHuellas").classList.remove("fa-check")
                document.querySelector("#chkRegistroHuellas").classList.add("red")
                document.querySelector("#chkRegistroHuellas").classList.add("fa-times")
                document.querySelector("#lnkHuellas").style.cursor = "pointer"
                document.querySelector("#fechaRegistro").value = ""
                document.querySelector("#noCliente").value = ""
                document.querySelector("#nombre").value = ""
                document.querySelector("#curp").value = ""
                document.querySelector("#edad").value = ""
                document.querySelector("#direccion").value = ""
                habilitaBeneficiario(1, false)
                document.querySelector("#ben2").style.opacity = "0"
                document.querySelector("#ben3").style.opacity = "0"
                document.querySelector("#btnGeneraContrato").style.display = "none"
                document.querySelector("#btnGuardar").innerText = txtGuardaContrato
                document.querySelector("#marcadores").style.opacity = "0"
                document.querySelector("#tasa").disabled = true
                document.querySelector("#sucursal").disabled = true
                document.querySelector("#contratoOK").value = ""
                document.querySelector("#ejecutivo_comision").childNodes.forEach((option) => {
                    if (option.value === "tmp") option.remove()
                })
            }
            
            const generaContrato = async (e) => {
                e.preventDefault()
                const btnGuardar = document.querySelector("#btnGuardar")
                if (btnGuardar.innerText === txtGuardaPago) return $("#modal_agregar_pago").modal("show")
                 
                document.querySelector("#fecha_pago").value = getHoy()
                document.querySelector("#contrato").value = ""
                document.querySelector("#codigo_cl").value = document.querySelector("#noCliente").value
                document.querySelector("#nombre_cliente").value = document.querySelector("#nombre").value
                document.querySelector("#mdlCurp").value = document.querySelector("#curp").value
                    
                await showInfo("Debe registrar el depósito por apertura de cuenta.")
                btnGuardar.innerText = txtGuardaPago
                $("#modal_agregar_pago").modal("show")
            }
                        
            const pagoApertura = async (e) => {
                if (!await valida_MCM_Complementos()) return
                 
                e.preventDefault()
                if (parseaNumero(document.querySelector("#deposito").value) < saldoMinimoApertura) return showError("El saldo inicial no puede ser menor a " + saldoMinimoApertura.toLocaleString("es-MX", {style:"currency", currency:"MXN"}) + ".")
                 
                confirmarMovimiento(
                    "Cuenta de ahorro corriente",
                    "¿Está segura de continuar con la apertura de la cuenta de ahorro del cliente: " +
                        document.querySelector("#nombre").value +
                        "?"
                ).then((continuar) => {
                    if (!continuar) return
                
                    const noCredito = document.querySelector("#noCliente").value
                    const datosContrato = $("#registroInicialAhorro").serializeArray()
                    addParametro(datosContrato, "credito", noCredito)
                    addParametro(datosContrato, "ejecutivo", "{$_SESSION['usuario']}")
                     
                    if (document.querySelector("#contrato").value !== "") return regPago(document.querySelector("#contrato").value)
                     
                    consultaServidor("/Ahorro/AgregaContratoAhorro/", $.param(datosContrato), (respuesta) => {
                        if (!respuesta.success) {
                            console.error(respuesta.error)
                            return showError(respuesta.mensaje)
                        }
                         
                        regPago(respuesta.datos.contrato)
                    })
                })
            }
             
            const regPago = (contrato) => {
                const datos = $("#AddPagoApertura").serializeArray()
                limpiaMontos(datos, ["deposito", "inscripcion", "saldo_inicial"])
                addParametro(datos, "sucursal", "{$_SESSION['cdgco_ahorro']}")
                addParametro(datos, "ejecutivo", "{$_SESSION['usuario']}")
                addParametro(datos, "contrato", contrato)
                 
                consultaServidor("/Ahorro/PagoApertura/", $.param(datos), (respuesta) => {
                    if (!respuesta.success) return showError(respuesta.mensaje)
                
                    showSuccess(respuesta.mensaje)
                    .then(() => {
                        document.querySelector("#registroInicialAhorro").reset()
                        document.querySelector("#AddPagoApertura").reset()
                        $("#modal_agregar_pago").modal("hide")
                        limpiaDatosCliente()
                        
                        showSuccess("Se ha generado el contrato: " + contrato + ".")
                        .then(() => {
                            imprimeContrato(contrato, 1)
                            imprimeTicket(respuesta.datos.ticket, "{$_SESSION['cdgco_ahorro']}")
                        })
                    })
                })
            }
             
            const validaDeposito = (e) => {
                if (!valKD) return
                 
                let monto = parseaNumero(e.target.value)
                if (monto <= 0) {
                    e.preventDefault()
                    e.target.value = ""
                    showError("El monto a depositar debe ser mayor a 0.")
                }
                 
                if (monto > montoMaximo) {
                    e.preventDefault()
                    monto = montoMaximo
                    e.target.value = monto
                }
                 
                const valor = e.target.value.split(".")
                if (valor[1] && valor[1].length > 2) {
                    e.preventDefault()
                    e.target.value = parseFloat(valor[0] + "." + valor[1].substring(0, 2))
                }
                
                document.querySelector("#monto_letra").value = numeroLetras(parseFloat(e.target.value))
                calculaSaldoFinal(e)
            }
             
            const calculaSaldoFinal = (e) => {
                const monto = parseaNumero(e.target.value)
                document.querySelector("#deposito").value = formatoMoneda(monto)
                const saldoInicial = (monto - parseaNumero(document.querySelector("#inscripcion").value))
                document.querySelector("#saldo_inicial").value = formatoMoneda(saldoInicial > 0 ? saldoInicial : 0)
                document.querySelector("#monto_letra").value = primeraMayuscula(numeroLetras(monto))
                    
                if (saldoInicial < (saldoMinimoApertura - costoInscripcion)) {
                    document.querySelector("#saldo_inicial").setAttribute("style", "color: red")
                    document.querySelector("#tipSaldo").setAttribute("style", "opacity: 100%;")
                    document.querySelector("#registraDepositoInicial").disabled = true
                } else {
                    document.querySelector("#saldo_inicial").removeAttribute("style")
                    document.querySelector("#tipSaldo").setAttribute("style", "opacity: 0%;")
                    document.querySelector("#registraDepositoInicial").disabled = false
                }
            }
             
            const camposLlenos = (e) => {
                if (document.querySelector("#sucursal").value === "") return
                const val = () => {
                    let porcentaje = 0
                    for (let i = 1; i <= 3; i++) {
                        document.querySelector("#beneficiario_" + i).value = document.querySelector("#beneficiario_" + i).value.toUpperCase()
                        porcentaje += parseFloat(document.querySelector("#porcentaje_" + i).value) || 0
                        if (document.querySelector("#ben" + i).style.opacity === "1") {
                            if (!document.querySelector("#beneficiario_" + i).value) {
                                document.querySelector("#parentesco_" + i).disabled = true
                                document.querySelector("#porcentaje_" + i).disabled = true
                                document.querySelector("#btnBen" + i).disabled = true
                                return false
                            }
                            document.querySelector("#parentesco_" + i).disabled = false
                             
                            if (document.querySelector("#parentesco_" + i).selectedIndex === 0) {
                                document.querySelector("#porcentaje_" + i).disabled = true
                                document.querySelector("#btnBen" + i).disabled = true
                                return false
                            }
                            document.querySelector("#porcentaje_" + i).disabled = false
                             
                            if (!document.querySelector("#porcentaje_" + i).value) {
                                document.querySelector("#btnBen" + i).disabled = true
                                return false
                            }
                            document.querySelector("#btnBen" + i).disabled = porcentaje >= 100 && document.querySelector("#btnBen1").querySelector("i").classList.contains("fa-plus")
                        }
                    }
                    
                    if (porcentaje > 100) {
                        e.preventDefault()
                        e.target.value = ""
                        showError("La suma de los porcentajes no puede ser mayor a 100%.")
                    }
                     
                    return porcentaje === 100
                }
                 
                if (e.target.tagName === "SELECT") actualizarOpciones(e.target)
                 
                document.querySelector("#btnGeneraContrato").style.display = !val() ? "none" : "block"
            }
             
            const validaPorcentaje = (e) => {
                let porcentaje = 0
                for (let i = 1; i <= 3; i++) {
                    if (i == 1 || document.querySelector("#ben" + i).style.opacity === "1") {
                        const porcentajeBeneficiario = parseFloat(document.querySelector("#porcentaje_" + i).value) || 0
                        porcentaje += porcentajeBeneficiario
                    }
                }
                if (porcentaje > 100) {
                    e.preventDefault()
                    e.target.value = ""
                    return showError("La suma de los porcentajes no puede ser mayor a 100%")
                }
                 
                document.querySelector("#btnGeneraContrato").style.display = porcentaje !== 100 ? "none" : "block"
            }
             
            const toggleBeneficiario = (numBeneficiario) => {
                const ben = document.getElementById(`ben` + numBeneficiario)
                ben.style.opacity = ben.style.opacity === "0" ? "1" : "0"
            }
             
            const toggleButtonIcon = (btnId, show) => {
                const btn = document.getElementById("btnBen" + btnId)
                btn.innerHTML = show ? '<i class="fa fa-minus"></i>' : '<i class="fa fa-plus"></i>'
            }
             
            const addBeneficiario = (event) => {
                const btn = event.target === event.currentTarget ? event.target : event.target.parentElement
                 
                if (btn.innerHTML.trim() === '<i class="fa fa-plus"></i>') {
                    const noID = parseInt(btn.id.split("btnBen")[1])
                    habilitaBeneficiario(noID+1, true)
                    toggleBeneficiario(noID+1)
                    toggleButtonIcon(noID, true)
                } else {
                    const noID = parseInt(btn.id.split("btnBen")[1])
                    for (let j = noID; j < 3; j++) {
                        moveData(j+1, j)
                    }
                    for (let i = 3; i > 0; i--) {
                        if (document.getElementById(`ben` + i).style.opacity === "1") {
                            habilitaBeneficiario(i, false)
                            toggleButtonIcon(i-1, false)
                            toggleBeneficiario(i)
                            break
                        }
                    }
                }
                camposLlenos(event)
            }
             
            const moveData = (from, to) => {
                const beneficiarioFrom = document.getElementById(`beneficiario_` + from)
                const parentescoFrom = document.getElementById(`parentesco_` + from)
                const porcentajeFrom = document.getElementById(`porcentaje_` + from)
                 
                const beneficiarioTo = document.getElementById(`beneficiario_` + to)
                const parentescoTo = document.getElementById(`parentesco_` + to)
                const porcentajeTo = document.getElementById(`porcentaje_` + to)
                 
                beneficiarioTo.value = beneficiarioFrom.value
                parentescoTo.value = parentescoFrom.value
                porcentajeTo.value = porcentajeFrom.value
                 
                beneficiarioFrom.value = ""
                parentescoFrom.value = ""
                porcentajeFrom.value = ""
            }
             
            const actualizarOpciones = (select) => {
                const valoresUnicos = [
                    "CÓNYUGE",
                    "PADRE",
                    "MADRE",
                ]
                     
                const valorSeleccionado = select.value
                const selects = document.querySelectorAll("#parentesco_1, #parentesco_2, #parentesco_3")
                const valoresSeleccionados = [
                    document.querySelector("#parentesco_1").value,
                    document.querySelector("#parentesco_2").value,
                    document.querySelector("#parentesco_3").value
                ]     
                 
                selects.forEach(element => {
                    if (element !== select) {
                        element.querySelectorAll("option").forEach(opcion => {
                            if (!valoresUnicos.includes(opcion.text)) return
                            if (valoresUnicos.includes(opcion.text) &&
                            valoresSeleccionados.includes(opcion.value)) return opcion.style.display = "none"
                            opcion.style.display = opcion.value === valorSeleccionado ? "none" : "block"
                        })
                    }
                })
            }
             
            const reImprimeContrato = (e) => {
                const c = document.querySelector('#contratoOK').value
                if (!c) {
                    e.preventDefault()
                    return
                }
                 
                imprimeContrato(c)
            }

            const mostrarModalHuellas = () => {
                const valContrato = document.querySelector("#chkCreacionContrato").classList.contains("red")
                const valPago = document.querySelector("#chkPagoApertura").classList.contains("red")
                const valHuellas = document.querySelector("#chkRegistroHuellas").classList.contains("green")

                if (valHuellas) return
                if (valContrato) return showError("Debe completar el proceso de creación del contrato.")
                if (valPago) return showError("Debe completar el proceso de pago de apertura.")

                $("#modal_registra_huellas").modal("show")
            }
         
            const huellasCompletas = (e) => {
                if (e.detail.modo === "captura") {
                    if (manoDerecha.manoLista() && manoIzquierda.manoLista()) {
                        document.querySelector("#registraHuellas").disabled = false
                        document.querySelector("#mensajeHuella").innerText = "Huellas capturadas correctamente."
                        return
                    }
            
                    document.querySelector("#registraHuellas").disabled = true
                    document.querySelector("#mensajeHuella").innerText = "$mensajeCaptura"
                }

                if (e.detail.modo === "actualizacion" && e.detail.muestrasOK) {
                    e.detail.evento()
                }
            }
         
            const guardarHuellas = async () => {
                if (!manoDerecha.manoLista() || !manoIzquierda.manoLista()) return showError("Debe capturar las muestras necesarias para ambas manos.")
                
                const manos = {}
                Object.assign(manos, manoIzquierda.getMano())
                Object.assign(manos, manoDerecha.getMano())
         
                const datos = {
                    cliente: document.querySelector("#noCliente").value,
                    ejecutivo: "{$_SESSION['usuario']}",
                    manos: JSON.stringify(manos)
                }
         
                consultaServidor("/Ahorro/RegistraHuellas/", datos, (respuesta) => {
                    if (!respuesta.success) return showError(respuesta.mensaje)
                    showSuccess(respuesta.mensaje)
                    .then(() => {
                        manoIzquierda.modoValidacion()
                        manoDerecha.modoValidacion()
                        document.querySelector("#mensajeHuella").innerText = "Huellas registradas correctamente, valide y confirme."
                        document.querySelector("#registraHuellas").style.display = "none"
                        document.querySelector("#cerrar_modal").style.display = "none"
                    })
                })
            }

            const validaHuella = (e) => {         
                const datos = {
                    cliente: document.querySelector("#noCliente").value,
                    dedo: e.detail.dedo,
                    muestra: e.detail.muestra
                }
         
                consultaServidor("/Ahorro/ValidaHuella/", datos, (respuesta) => {
                    e.detail.colorImagen(respuesta.success ? "green" : "red")
                    if (!respuesta.success) {
                        e.detail.conteoErrores()
                        return showError(respuesta.mensaje)
                    }
         
                    e.detail.conteoErrores(0)
                    e.detail.boton.style.display = "none"
         
                    showSuccess(respuesta.mensaje).then(() => {
                        const botones = document.querySelectorAll(".btnHuella")
                        if (Array.from(botones).every(boton => boton.style.display === "none")) {
                            manoIzquierda.limpiarMano()
                            manoDerecha.limpiarMano()
                            document.querySelector("#registraHuellas").style.display = null
                            document.querySelector("#mensajeHuella").innerText = "Huellas registradas correctamente."
                            document.querySelector("#chkRegistroHuellas").classList.remove("red")
                            document.querySelector("#chkRegistroHuellas").classList.remove("fa-times")
                            document.querySelector("#chkRegistroHuellas").classList.add("green")
                            document.querySelector("#chkRegistroHuellas").classList.add("fa-check")
                            document.querySelector("#lnkHuellas").style.cursor = "default"
                            document.querySelector("#cerrar_modal").style.display = null
                            showSuccess("Huellas validadas correctamente.").then(() => {
                                $("#modal_registra_huellas").modal("hide")
                            })
                        }
                    })
                })
            }

            const actualizaHuella = (e) => {
                const manos = {}
                manos[e.detail.mano] = {}
                manos[e.detail.mano][e.detail.dedo] = e.detail.muestras

                const datos = {
                    cliente: document.querySelector("#noCliente").value,
                    manos: JSON.stringify(manos)
                }

                consultaServidor("/Ahorro/ActualizaHuella/", datos, (respuesta) => {
                    if (!respuesta.success) {
                        e.detail.limpiar()
                        e.detail.mensajeLector("Haz clic en la imagen para intentar nuevamente.")
                        return showError(respuesta.mensaje)
                    }
         
                    e.detail.valida()
                })
            }
        </script>
        html;

        $sucursales = CajaAhorroDao::GetSucursalAsignadaCajeraAhorro($this->__usuario);
        $opcSucursales = "";
        foreach ($sucursales as $sucursales) {
            $opcSucursales .= "<option value='{$sucursales['CODIGO']}'>{$sucursales['NOMBRE']}</option>";
            $suc_eje = $sucursales['CODIGO'];
        }

        $ejecutivos = CajaAhorroDao::GetEjecutivosSucursal($suc_eje);
        $opcEjecutivos = "";
        foreach ($ejecutivos as $ejecutivos) {
            $opcEjecutivos .= "<option value='{$ejecutivos['ID_EJECUTIVO']}'>{$ejecutivos['EJECUTIVO']}</option>";
        }
        $opcEjecutivos .= "<option value='{$this->__usuario}' selected>{$this->__nombre} - CAJER(A)</option>";

        $parentescos = CajaAhorroDao::GetCatalogoParentescos();
        $opcParentescos = "<option value='' disabled selected>Seleccionar</option>";
        foreach ($parentescos as $parentesco) {
            $opcParentescos .= "<option value='{$parentesco['CODIGO']}'>{$parentesco['DESCRIPCION']}</option>";
        }


        if ($_GET['cliente']) View::set('cliente', $_GET['cliente']);
        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Contrato Ahorro Corriente", [$this->huellas])));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        view::set('saldoMinimoApertura', $saldoMinimoApertura);
        view::set('costoInscripcion', $costoInscripcion);
        View::set('fecha', date('d/m/Y H:i:s'));
        view::set('opcParentescos', $opcParentescos);
        view::set('sucursales', $opcSucursales);
        view::set('ejecutivos', $opcEjecutivos);
        View::set('mensajeCaptura', $mensajeCaptura);
        View::render("caja_menu_contrato_ahorro");
    }

    public function BuscaCliente()
    {
        if (self::ValidaHorario()) {
            echo CajaAhorroDao::BuscaClienteNvoContrato($_POST);
            return;
        }
        echo self::FueraHorario();
    }

    public function GetBeneficiarios()
    {
        echo CajaAhorroDao::GetBeneficiarios($_POST['contrato']);
    }

    public function AgregaContratoAhorro()
    {
        echo CajaAhorroDao::AgregaContratoAhorro($_POST);
    }

    public function PagoApertura()
    {
        $pago = CajaAhorroDao::AddPagoApertura($_POST);
        echo $pago;
        return $pago;
    }

    public function RegistraHuellas()
    {
        $datosEngine = [
            "manos" => $_POST['manos'],
        ];

        $huellas = self::EngineHuellas("preregistro.php", $datosEngine);

        if (!$huellas['success']) {
            echo json_encode($huellas);
            exit;
        }

        $datos = [
            "cliente" => $_POST['cliente'],
            "ejecutivo" => $_POST['ejecutivo'],
            "izquierda" => $huellas['datos']['izquierda'],
            "derecha" => $huellas['datos']['derecha']
        ];

        echo CajaAhorroDao::RegistraHuellas($datos);
    }

    public function ActualizaHuella()
    {
        $datosEngine = [
            "manos" => $_POST['manos'],
        ];

        $huellas = self::EngineHuellas("preregistro.php", $datosEngine);

        if (!$huellas['success']) {
            echo json_encode($huellas);
            exit;
        }

        $dedos = [];
        foreach ($huellas['datos'] as $mano => $dedo) {
            foreach ($dedo as $dedo => $huella) {
                $d = $dedo . "_" . $mano[0];
                $dedos[$d] = $huella;
            }
        }

        $datos = [
            "cliente" => $_POST['cliente'],
            "dedos" => $dedos,
        ];

        echo CajaAhorroDao::ActualizaHuella($datos);
    }

    public function ValidaHuella()
    {
        $repuesta = [
            "success" => false,
            "mensaje" => "No se ha podido validar la huella."
        ];

        $huellas = CajaAhorroDao::GetHuellas($_POST);

        if (count($huellas) == 0) {
            $repuesta['mensaje'] = "No se encontraron registros en la base de datos.";
            echo json_encode($repuesta);
            return;
        }

        $huellasEngine = [];
        foreach ($huellas as $huella => $valor) {
            array_push($huellasEngine, $valor["HUELLA"]);
        }

        $datosEngine = [
            "dedo" => $_POST['muestra'],
            "huellas" => json_encode($huellasEngine)
        ];

        $resultado = self::EngineHuellas("identifica.php", $datosEngine);
        $repuesta["resultado"] = $resultado;

        $repuesta["success"] = $resultado["success"];
        $repuesta["mensaje"] = $resultado["mensaje"];
        if ($resultado["success"]) {
            $repuesta["cliente"] = $huellas[$resultado["coincidencia"]]["CLIENTE"];
        }

        echo json_encode($repuesta);
    }

    public function EngineHuellas($endpoint, $datos)
    {
        $ci = curl_init($this->urlHuellas . $endpoint);
        curl_setopt($ci, CURLOPT_POST, true);
        curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($datos));
        curl_setopt($ci, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ci);
        curl_close($ci);
        return json_decode($response, true);
    }

    public function ValidaRegistroHuellas()
    {
        echo CajaAhorroDao::ValidaRegistroHuellas($_POST);
    }

    public function EliminaHuellas()
    {
        echo CajaAhorroDao::EliminaHuellas($_POST);
    }

    // Movimientos sobre cuentas de ahorro corriente //
    public function CuentaCorriente()
    {
        $saldoMinimoApertura = 100;
        $montoMaximoRetiro = 50000;
        $montoMaximoDeposito = 1000000;
        $maximoRetiroDia = 50000;

        $extraFooter = <<<script
        <script>
            const saldoMinimoApertura = $saldoMinimoApertura
            const montoMaximoRetiro = $montoMaximoRetiro
            const montoMaximoDeposito = $montoMaximoDeposito
            const maximoRetiroDia = $maximoRetiroDia
            const noSucursal = "{$_SESSION['cdgco_ahorro']}"
            let huellas = 0
            let retiroDispobible = maximoRetiroDia
            let mano

            {$this->showError}
            {$this->showSuccess}
            {$this->showInfo}
            {$this->showWarning}
            {$this->confirmarMovimiento}
            {$this->validarYbuscar}
            {$this->buscaCliente}
            {$this->getHoy}
            {$this->soloNumeros}
            {$this->numeroLetras}
            {$this->primeraMayuscula}
            {$this->muestraPDF}
            {$this->imprimeTicket}
            {$this->sinContrato}
            {$this->addParametro}
            {$this->parseaNumero}
            {$this->formatoMoneda}
            {$this->consultaServidor}
            {$this->limpiaMontos}
            {$this->showBloqueo}
            {$this->validaHorarioOperacion}
            {$this->valida_MCM_Complementos}
            {$this->showHuella}
            {$this->validaHuella}
            {$this->autorizaOperacion}
 
            window.onload = () => {
                validaHorarioOperacion("{$_SESSION['inicio']}", "{$_SESSION['fin']}")
                if(document.querySelector("#clienteBuscado").value !== "") buscaCliente()
            }

            const llenaDatosCliente = (datosCliente) => {
                retiroDispobible = maximoRetiroDia
                let blkRetiro = false
                consultaServidor("/Ahorro/ValidaRetirosDia/", { contrato: datosCliente.CONTRATO }, (respuesta) => {
                    if (!respuesta.success && respuesta.datos.RETIROS >= maximoRetiroDia) {
                        showWarning("El cliente " + datosCliente.CDGCL + " ha alcanzado el límite de retiros diarios.")
                        blkRetiro = true
                        retiroDispobible = maximoRetiroDia - respuesta.datos.RETIROS
                    }
                    huellas = datosCliente.HUELLAS
                    document.querySelector("#nombre").value = datosCliente.NOMBRE
                    document.querySelector("#curp").value = datosCliente.CURP
                    document.querySelector("#contrato").value = datosCliente.CONTRATO
                    document.querySelector("#cliente").value = datosCliente.CDGCL
                    document.querySelector("#saldoActual").value = formatoMoneda(datosCliente.SALDO)
                    document.querySelector("#deposito").disabled = false
                    document.querySelector("#retiro").disabled = blkRetiro
                })
            }
             
            const limpiaDatosCliente = () => {
                huellas = 0
                document.querySelector("#registroOperacion").reset()
                document.querySelector("#monto").disabled = true
                document.querySelector("#btnRegistraOperacion").disabled = true
                document.querySelector("#retiro").disabled = true
                document.querySelector("#deposito").disabled = true
            }
             
            const validaMonto = () => {
                if (!valKD) return
                const montoIngresado = document.querySelector("#monto")
                 
                let monto = parseaNumero(montoIngresado.value)
                 
                if (!document.querySelector("#deposito").checked && monto > montoMaximoRetiro) {
                    monto = montoMaximoRetiro
                    swal({
                        title: "Cuenta de ahorro corriente",
                        text: "Para retiros mayores a " + montoMaximoRetiro.toLocaleString("es-MX", { style: "currency", currency: "MXN" }) + " es necesario realizar una solicitud de retiro\\nDesea generar una solicitud de retiro ahora?.",
                        icon: "info",
                        buttons: ["No", "Sí"],
                        dangerMode: true
                    }).then((regRetiro) => {
                        if (regRetiro) {
                            window.location.href = "/Ahorro/SolicitudRetiroCuentaCorriente/?cliente=" + document.querySelector("#cliente").value
                            return
                        }
                    })
                    montoIngresado.value = monto
                }
                 
                if (document.querySelector("#deposito").checked && monto > montoMaximoDeposito) {
                    monto = montoMaximoDeposito
                    montoIngresado.value = monto
                }
                 
                const valor = montoIngresado.value.split(".")
                if (valor[1] && valor[1].length > 2) {
                    montoIngresado.value = parseFloat(valor[0] + "." + valor[1].substring(0, 2))
                }
                
                if (montoIngresado.id === "mdlDeposito_inicial") return calculaSaldoInicial(e)
                 
                document.querySelector("#monto_letra").value = numeroLetras(parseFloat(montoIngresado.value))
                if (document.querySelector("#deposito").checked || document.querySelector("#retiro").checked) calculaSaldoFinal()
            }
             
            const calculaSaldoInicial = (e) => {
                const monto = parseaNumero(e.target.value)
                document.querySelector("#mdlDeposito").value = formatoMoneda(monto)
                const saldoInicial = (monto - parseaNumero(document.querySelector("#mdlInscripcion").value)).toFixed(2)
                document.querySelector("#mdlSaldo_inicial").value = formatoMoneda(saldoInicial > 0 ? saldoInicial : 0)
                document.querySelector("#mdlDeposito_inicial_letra").value = primeraMayuscula(numeroLetras(monto))
                    
                if (saldoInicial < saldoMinimoApertura) {
                    document.querySelector("#mdlSaldo_inicial").setAttribute("style", "color: red")
                    document.querySelector("#mdlTipSaldo").setAttribute("style", "opacity: 100%;")
                    document.querySelector("#mdlRegistraDepositoInicial").disabled = true
                } else {
                    document.querySelector("#mdlSaldo_inicial").removeAttribute("style")
                    document.querySelector("#mdlTipSaldo").setAttribute("style", "opacity: 0%;")
                    document.querySelector("#mdlRegistraDepositoInicial").disabled = false
                }
            }
             
            const calculaSaldoFinal = () => {
                const esDeposito = document.querySelector("#deposito").checked
                const saldoActual = parseaNumero(document.querySelector("#saldoActual").value)
                const monto = parseaNumero(document.querySelector("#monto").value)
                document.querySelector("#montoOperacion").value = formatoMoneda(monto)
                document.querySelector("#saldoFinal").value = formatoMoneda(esDeposito ? saldoActual + monto : saldoActual - monto)
                compruebaSaldoFinal()
            }
             
            const cambioMovimiento = (e) => {
                document.querySelector("#monto").disabled = false
                const esDeposito = document.querySelector("#deposito").checked
                document.querySelector("#simboloOperacion").innerText = esDeposito ? "+" : "-"
                document.querySelector("#descOperacion").innerText = (esDeposito ? "Depósito" : "Retiro") + " a cuenta ahorro corriente"
                document.querySelector("#monto").max = esDeposito ? montoMaximoDeposito : montoMaximoRetiro
                valKD = true
                validaMonto()
                calculaSaldoFinal()
            }
             
            const compruebaSaldoFinal = () => {
                const saldoFinal = parseaNumero(document.querySelector("#saldoFinal").value)
                if (saldoFinal < 0) {
                    document.querySelector("#saldoFinal").setAttribute("style", "color: red")
                    document.querySelector("#tipSaldo").setAttribute("style", "opacity: 100%;")
                    document.querySelector("#tipSaldo").innerText = "El monto a retirar no puede ser mayor al saldo de la cuenta."
                    document.querySelector("#btnRegistraOperacion").disabled = true
                    return
                } else {
                    document.querySelector("#saldoFinal").removeAttribute("style")
                    document.querySelector("#tipSaldo").setAttribute("style", "opacity: 0%;")
                }
                if (document.querySelector("#retiro").checked && retiroDispobible < parseaNumero(document.querySelector("#montoOperacion").value)) {
                    document.querySelector("#saldoFinal").setAttribute("style", "color: red")
                    document.querySelector("#tipSaldo").setAttribute("style", "opacity: 100%;")
                    document.querySelector("#tipSaldo").innerText = "El monto a retirar excede el límite de retiros diarios, disponible para retirar el día de hoy: " + retiroDispobible.toLocaleString("es-MX", { style: "currency", currency: "MXN" })
                    document.querySelector("#btnRegistraOperacion").disabled = true
                    return
                }
                document.querySelector("#btnRegistraOperacion").disabled = !(saldoFinal >= 0 && parseaNumero(document.querySelector("#montoOperacion").value) > 0)
            }
             
            const registraOperacion = async (e) => {
                if (!await valida_MCM_Complementos()) return
                 
                e.preventDefault()
                const datos = $("#registroOperacion").serializeArray()
                
                limpiaMontos(datos, ["saldoActual", "montoOperacion", "saldoFinal"])
                addParametro(datos, "sucursal", "{$_SESSION['cdgco_ahorro']}")
                addParametro(datos, "ejecutivo", "{$_SESSION['usuario']}")
                addParametro(datos, "producto", "cuenta de ahorro corriente")
                 
                if (!document.querySelector("#deposito").checked && !document.querySelector("#retiro").checked) return showError("Seleccione el tipo de operación a realizar.")
                
                datos.forEach((dato) => {
                    if (dato.name === "esDeposito") dato.value = document.querySelector("#deposito").checked
                })
                 
                confirmarMovimiento(
                    "Confirmación de movimiento de ahorro corriente",
                    "¿Está segur(a) de continuar con el registro de un "
                    + (document.querySelector("#deposito").checked ? "depósito" : "retiro")
                    + " de cuanta ahorro corriente por la cantidad de "
                    + parseaNumero(document.querySelector("#montoOperacion").value).toLocaleString("es-MX", { style: "currency", currency: "MXN" })
                    + " (" + document.querySelector("#monto_letra").value + ")?"
                ).then((continuar) => {
                    if (!continuar) return
                    if (!document.querySelector("#deposito").checked && huellas > 0) return showHuella(true, datos)
                    enviaRegistroOperacion(datos)
                })
            }

            const enviaRegistroOperacion = (datos) => {
                consultaServidor("/Ahorro/RegistraOperacion/", $.param(datos), (respuesta) => {
                    if (!respuesta.success){
                        if (respuesta.error) return showError(respuesta.error)
                        return showError(respuesta.mensaje)
                    }
                    showSuccess(respuesta.mensaje).then(() => {
                        imprimeTicket(respuesta.datos.ticket, "{$_SESSION['cdgco_ahorro']}")
                        limpiaDatosCliente()
                    })
                })
            }
        </script>
        script;

        if ($_GET['cliente']) View::set('cliente', $_GET['cliente']);

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Ahorro Corriente", [$this->swal2, $this->huellas])));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        view::set('saldoMinimoApertura', $saldoMinimoApertura);
        view::set('montoMaximoRetiro', $montoMaximoRetiro);
        View::set('fecha', date('d/m/Y H:i:s'));
        View::render("caja_menu_ahorro");
    }

    public function BuscaContratoAhorro()
    {
        if (self::ValidaHorario()) {
            echo CajaAhorroDao::BuscaContratoAhorro($_POST);
            return;
        }
        echo self::FueraHorario();
    }

    public function RegistraOperacion()
    {
        $resutado =  CajaAhorroDao::RegistraOperacion($_POST);
        echo $resutado;
    }

    public function ValidaRetirosDia()
    {
        echo CajaAhorroDao::ValidaRetirosDia($_POST);
    }

    // Registro de solicitudes de retiros mayores de cuentas de ahorro //
    public function SolicitudRetiroCuentaCorriente()
    {
        $montoMinimoRetiro = 10000;
        $montoMaximoExpress = 1000000;
        $montoMaximoRetiro = 1000000;

        $extraFooter = <<<html
        <script>
            window.onload = () => {
                if(document.querySelector("#clienteBuscado").value !== "") buscaCliente()
            }
         
            const montoMinimo = $montoMinimoRetiro
            const montoMaximoExpress = $montoMaximoExpress
            const montoMaximoRetiro = $montoMaximoRetiro
            const noSucursal = "{$_SESSION['cdgco_ahorro']}"
            let valKD = false
            let huellas = 0
         
            {$this->showError}
            {$this->showSuccess}
            {$this->showInfo}
            {$this->confirmarMovimiento}
            {$this->validarYbuscar}
            {$this->buscaCliente}
            {$this->soloNumeros}
            {$this->primeraMayuscula}
            {$this->numeroLetras}
            {$this->muestraPDF}
            {$this->addParametro}
            {$this->sinContrato}
            {$this->getHoy}
            {$this->parseaNumero}
            {$this->formatoMoneda}
            {$this->limpiaMontos}
            {$this->consultaServidor}
            {$this->valida_MCM_Complementos}
            {$this->showHuella}
            {$this->validaHuella}
            {$this->autorizaOperacion}
             
            const llenaDatosCliente = (datosCliente) => {
                if (parseaNumero(datosCliente.SALDO) < montoMinimo) {
                    swal({
                        title: "Retiro de cuenta corriente",
                        text: "El saldo de la cuenta es menor al monto mínimo para retiros express (" + montoMinimo.toLocaleString("es-MX", {style:"currency", currency:"MXN"}) + ").\\n¿Desea realizar un retiro simple?",
                        icon: "info",
                        buttons: ["No", "Sí"]
                    }).then((retSimple) => {
                        if (retSimple) {
                            window.location.href = "/Ahorro/CuentaCorriente/?cliente=" + datosCliente.CDGCL
                            return
                        }
                    })
                    return
                }
                 
                huellas = datosCliente.HUELLAS
                document.querySelector("#nombre").value = datosCliente.NOMBRE
                document.querySelector("#curp").value = datosCliente.CURP
                document.querySelector("#contrato").value = datosCliente.CONTRATO
                document.querySelector("#cliente").value = datosCliente.CDGCL
                document.querySelector("#saldoActual").value = formatoMoneda(datosCliente.SALDO)
                document.querySelector("#monto").disabled = false
                document.querySelector("#saldoFinal").value = formatoMoneda(datosCliente.SALDO)
                document.querySelector("#express").disabled = false
                document.querySelector("#programado").disabled = false
            }
             
            const limpiaDatosCliente = () => {
                huellas = 0
                document.querySelector("#registroOperacion").reset()
                document.querySelector("#monto").disabled = true
                document.querySelector("#btnRegistraOperacion").disabled = true
                document.querySelector("#express").disabled = true
                document.querySelector("#programado").disabled = true
                document.querySelector("#fecha_retiro_hide").setAttribute("style", "display: none;")
                document.querySelector("#fecha_retiro").removeAttribute("style")
            }
             
            const validaMonto = () => {
                document.querySelector("#express").disabled = false
                const montoIngresado = document.querySelector("#monto")
                 
                let monto = parseaNumero(montoIngresado.value) || 0
                 
                if (monto > montoMaximoExpress) {
                    document.querySelector("#programado").checked = true
                    document.querySelector("#express").disabled = true
                    cambioMovimiento()
                }
                 
                if (monto > montoMaximoRetiro) {
                    monto = montoMaximoRetiro
                    montoIngresado.value = monto
                }
                                  
                document.querySelector("#monto_letra").value = primeraMayuscula(numeroLetras(monto))
                const saldoActual = parseaNumero(document.querySelector("#saldoActual").value)
                document.querySelector("#montoOperacion").value = formatoMoneda(monto)
                const saldoFinal = (saldoActual - monto)
                document.querySelector("#saldoFinal").value = formatoMoneda(saldoFinal)
                compruebaSaldoFinal()
            }
             
            const valSalMin = () => {
                const montoIngresado = document.querySelector("#monto")
                 
                let monto = parseFloat(montoIngresado.value) || 0
                 
                if (monto < montoMinimo) {
                    monto = montoMinimo
                    swal({
                        title: "Retiro de cuenta corriente",
                        text: "El monto mínimo para retiros express es de " + montoMinimo.toLocaleString("es-MX", {
                            style: "currency",
                            currency: "MXN"
                        }) + ", para un monto menor debe realizar el retiro de manera simple.\\n¿Desea realizar el retiro de manera simple?",
                        icon: "info",
                        buttons: ["No", "Sí"]
                    }).then((retSimple) => {
                        if (retSimple) {
                            window.location.href = "/Ahorro/CuentaCorriente/?cliente=" + document.querySelector("#cliente").value
                            return
                        }
                    })
                }
            }
             
            const cambioMovimiento = (e) => {
                const express = document.querySelector("#express").checked
                
                if (express) {
                    document.querySelector("#fecha_retiro").removeAttribute("style")
                    document.querySelector("#fecha_retiro_hide").setAttribute("style", "display: none;")
                    document.querySelector("#fecha_retiro").value = getHoy()
                    return
                }
                
                document.querySelector("#fecha_retiro_hide").removeAttribute("style")
                document.querySelector("#fecha_retiro").setAttribute("style", "display: none;")
                pasaFecha({ target: document.querySelector("#fecha_retiro") })
            }
             
            const compruebaSaldoFinal = () => {
                const saldoFinal = parseaNumero(document.querySelector("#saldoFinal").value)
                if (saldoFinal < 0) {
                    document.querySelector("#saldoFinal").setAttribute("style", "color: red")
                    document.querySelector("#tipSaldo").setAttribute("style", "opacity: 100%;")
                    document.querySelector("#btnRegistraOperacion").disabled = true
                    return
                } else {
                    document.querySelector("#saldoFinal").removeAttribute("style")
                    document.querySelector("#tipSaldo").setAttribute("style", "opacity: 0%;")
                }
                document.querySelector("#btnRegistraOperacion").disabled = !(saldoFinal >= 0 && parseaNumero(document.querySelector("#montoOperacion").value) >= montoMinimo && parseaNumero(document.querySelector("#montoOperacion").value) < montoMaximoRetiro)
            }
             
            const pasaFecha = (e) => {
                const fechaSeleccionada = new Date(e.target.value)
                if (fechaSeleccionada.getDay() === 5 || fechaSeleccionada.getDay() === 6) {
                    showError("No se pueden realizar retiros los fines de semana.")
                    const f = getHoy(false).split("/")
                    e.target.value = f[2] + "-" + f[1] + "-" + f[0]
                    return
                }
                const f = document.querySelector("#fecha_retiro_hide").value.split("-")
                document.querySelector("#fecha_retiro").value = f[2] + "/" + f[1] + "/" + f[0]
            }
             
            const registraSolicitud = (e) => {
                e.preventDefault()
                const datos = $("#registroOperacion").serializeArray()
                
                limpiaMontos(datos, ["saldoActual", "montoOperacion", "saldoFinal"])
                addParametro(datos, "sucursal", "{$_SESSION['cdgco_ahorro']}")
                addParametro(datos, "ejecutivo", "{$_SESSION['usuario']}")
                addParametro(datos, "retiroExpress", document.querySelector("#express").checked)
                 
                confirmarMovimiento(
                    "Confirmación de movimiento ahorro corriente",
                    "¿Está segur(a) de continuar con el registro de un retiro "
                    + (document.querySelector("#express").checked ? "express" : "programado")
                    + ", por la cantidad de "
                    + parseaNumero(document.querySelector("#montoOperacion").value).toLocaleString("es-MX", { style: "currency", currency: "MXN" })
                    + " (" + document.querySelector("#monto_letra").value + ")?"
                ).then((continuar) => {
                    if (!continuar) return
                    if (huellas > 0) return showHuella(true, datos)
                    enviaRegistroOperacion(datos)
                })
            }

            const enviaRegistroOperacion = (datos) => {
                consultaServidor("/Ahorro/RegistraSolicitud/", $.param(datos), (respuesta) => {
                    if (!respuesta.success) {
                        console.log(respuesta.error)
                        return showError(respuesta.mensaje)
                    }
                    showSuccess(respuesta.mensaje).then(() => {
                        document.querySelector("#registroOperacion").reset()
                        limpiaDatosCliente()
                    })
                })
            }
        </script>
        html;

        if ($_GET['cliente']) View::set('cliente', $_GET['cliente']);

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Solicitud de Retiro", [$this->swal2, $this->huellas])));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::set('montoMinimoRetiro', $montoMinimoRetiro);
        View::set('montoMaximoExpress', $montoMaximoExpress);
        View::set('montoMaximoRetiro', $montoMaximoRetiro);
        View::set('fecha', date('d/m/Y H:i:s'));
        View::set('fechaInput', date('Y-m-d', strtotime('+1 day')));
        View::set('fechaInputMax', date('Y-m-d', strtotime('+30 day')));
        View::render("caja_menu_retiro_ahorro");
    }

    public function RegistraSolicitud()
    {
        $datos = CajaAhorroDao::RegistraSolicitud($_POST);
        echo $datos;
    }

    // Historial de solicitudes de retiros de cuentas de ahorro //
    public function HistorialSolicitudRetiroCuentaCorriente()
    {
        $extraFooter = <<<html
        <script>
            {$this->showError}
            {$this->showSuccess}
            {$this->showInfo}
            {$this->confirmarMovimiento}
            {$this->consultaServidor}
            {$this->configuraTabla}
            {$this->exportaExcel}
            {$this->imprimeTicket}
            {$this->muestraPDF}
            {$this->addParametro}
            {$this->validaFIF}
            {$this->valida_MCM_Complementos}
         
            $(document).ready(() => {
                configuraTabla("hstSolicitudes")
            })
            
            const imprimeExcel = () => exportaExcel("hstSolicitudes", "Historial solicitudes de retiro")
             
            const actualizaEstatus = async (estatus, id) => {
                if (!await valida_MCM_Complementos()) return
                 
                const accion = estatus === 3 ? "entrega" : "cancelación"
                 
                consultaServidor("/Ahorro/ResumenEntregaRetiro", $.param({id}), (respuesta) => {
                    if (!respuesta.success) {
                        if (respuesta.error) return showError(respuesta.error)
                        return showError(respuesta.mensaje)
                    }
                     
                    const resumen = respuesta.datos
                    confirmarMovimiento(
                        "Seguimiento solicitudes de retiro",
                        null,
                        resumenRetiro(resumen, accion)
                    ).then((continuar) => {
                        if (!continuar) return
                        const datos = {
                            estatus, 
                            id, 
                            ejecutivo: "{$_SESSION['usuario']}", 
                            sucursal: "{$_SESSION['cdgco_ahorro']}", 
                            monto: resumen.MONTO, 
                            contrato: resumen.CONTRATO,
                            cliente: resumen.CLIENTE,
                            tipo: resumen.TIPO_RETIRO
                        }
                         
                        consultaServidor("/Ahorro/EntregaRetiro/", $.param(datos), (respuesta) => {
                            if (!respuesta.success) {
                                if (respuesta.error) return showError(respuesta.error)
                                return showError(respuesta.mensaje)
                            }
                             
                            showSuccess(respuesta.mensaje).then(() => {
                                if (estatus === 3) {
                                    imprimeTicket(respuesta.datos.CODIGO, "{$_SESSION['cdgco_ahorro']}")
                                    swal({ text: "Actualizando pagina...", icon: "/img/wait.gif", button: false, closeOnClickOutside: false, closeOnEsc: false })
                                    window.location.reload()
                                }
                                if (estatus === 4) devuelveRetiro(resumen)
                            })
                        })
                    })
                })
            }
             
            const resumenRetiro = (datos, accion) => {
                const resumen = document.createElement("div")
                resumen.setAttribute("style", "color: rgba(0, 0, 0, .65); text-align: left;")
                
                const tabla = document.createElement("table")
                tabla.setAttribute("style", "width: 100%;")
                tabla.innerHTML = "<thead><tr><th colspan='2' style='font-size: 25px; text-align: center;'>Retiro " + (datos.TIPO_RETIRO == 1 ? "express" : "programado") + "</th></tr></thead>"
                 
                const tbody = document.createElement("tbody")
                tbody.setAttribute("style", "width: 100%;")
                tbody.innerHTML += "<tr><td><strong>Cliente:</strong></td><td style='text-align: center;'>" + datos.NOMBRE + "</td></tr>"
                tbody.innerHTML += "<tr><td><strong>Contrato:</strong></td><td style='text-align: center;'>" + datos.CONTRATO + "</td></tr>"
                tbody.innerHTML += "<tr><td><strong>Monto:</strong></td><td style='text-align: center;'>" + parseFloat(datos.MONTO).toLocaleString("es-MX", { style: "currency", currency: "MXN" }) + "</td></tr>"
                
                const tInterno = document.createElement("table")
                tInterno.setAttribute("style", "width: 100%; margin-top: 20px;")
                const tbodyI = document.createElement("tbody")
                tbodyI.innerHTML += "<tr><td><strong>Autorizado por:</strong></td style='text-align: center;'><td>" + datos.APROBADO_POR + "</td></tr>"
                tbodyI.innerHTML += "<tr><td><strong>A " + (accion === "entrega" ? "entregar" : "cancelar") + " por:</strong></td style='text-align: center;'><td>{$_SESSION['nombre']}</td></tr>"
                tInterno.appendChild(tbodyI)
                 
                const tFechas = document.createElement("table")
                tFechas.setAttribute("style", "width: 100%; margin-top: 20px;")
                const tbodyF = document.createElement("tbody")
                tbodyF.innerHTML += "<tr><td style='text-align: center; width: 50%;'><strong>Fecha entrega solicitada</strong></td><td style='text-align: center; width: 50%;'><strong>Fecha " + (accion === "entrega" ? accion + " real" : accion) + "</strong></td></tr>"
                tbodyF.innerHTML += "<tr><td style='text-align: center; width: 50%;'>" + datos.FECHA_ESPERADA + "</td><td style='text-align: center; width: 50%;'>" + new Date().toLocaleString("es-MX", { day: "2-digit", month: "2-digit", year: "numeric"}) + "</td></tr>"
                tFechas.appendChild(tbodyF)
                 
                tabla.appendChild(tbody)
                resumen.appendChild(tabla)
                resumen.appendChild(tInterno)
                resumen.appendChild(tFechas)
                 
                const pregunta = document.createElement("label")
                pregunta.setAttribute("style", "width: 100%; font-size: 20px; text-align: center; font-weight: bold; margin-top: 20px;")
                pregunta.innerText = "¿Desea continuar con la " + accion + " del retiro?"
                 
                const advertencia = document.createElement("label")
                advertencia.setAttribute("style", "width: 100%; color: red; font-size: 15px; text-align: center;")
                advertencia.innerText = "Esta acción no se puede deshacer."
                 
                resumen.appendChild(pregunta)
                resumen.appendChild(advertencia)
                return resumen
            }
             
            const devuelveRetiro = (datos) => {
                const datosDev = {
                    contrato: datos.CONTRATO,
                    monto: datos.MONTO,
                    ejecutivo: "{$_SESSION['usuario']}",
                    sucursal: "{$_SESSION['cdgco_ahorro']}",
                    tipo: datos.TIPO_RETIRO
                }
                 
                consultaServidor("/Ahorro/DevolucionRetiro/", $.param(datosDev), (respuesta) => {
                    if (!respuesta.success) {
                        console.log(respuesta.error)
                        return showError(respuesta.mensaje)
                    }
                     
                    showSuccess(respuesta.mensaje).then(() => {
                        imprimeTicket(respuesta.datos.ticket, "{$_SESSION['cdgco_ahorro']}", false)
                        swal({ text: "Actualizando pagina...", icon: "/img/wait.gif", button: false, closeOnClickOutside: false, closeOnEsc: false })
                        window.location.reload()
                    })
                })
            }
             
            const buscar = () => {
                const datos = []
                addParametro(datos, "producto", 1)
                addParametro(datos, "fechaI", document.querySelector("#fechaI").value)
                addParametro(datos, "fechaF", document.querySelector("#fechaF").value)
                addParametro(datos, "estatus", document.querySelector("#estatus").value)
                addParametro(datos, "tipo", document.querySelector("#tipo").value)
                 
                consultaServidor("/Ahorro/HistoricoSolicitudRetiro/", $.param(datos), (respuesta) => {
                    $("#hstSolicitudes").DataTable().destroy()
                     
                    if (respuesta.datos == "") showError("No se encontraron solicitudes de retiro en el rango de fechas seleccionado.")
                     
                    $("#hstSolicitudes tbody").html(respuesta.datos)
                    configuraTabla("hstSolicitudes")
                })
            }
             
            const validaFechaEntrega = (fecha) => showError("La solicitud no está disponible para entrega, la fecha programada de entrega es el " + fecha + ".")
        </script>
        html;

        $tabla = self::HistoricoSolicitudRetiro(1);
        $tabla = $tabla['success'] ? $tabla['datos'] : "";

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Historial de solicitudes de retiro", [$this->XLSX])));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::set('tabla', $tabla);
        View::set('fecha', date('Y-m-d'));
        View::render("caja_menu_solicitud_retiro_historial");
    }

    public function ResumenEntregaRetiro()
    {
        echo CajaAhorroDao::ResumenEntregaRetiro($_POST);
    }

    public function EntregaRetiro()
    {
        echo CajaAhorroDao::EntregaRetiro($_POST);
    }

    public function DevolucionRetiro()
    {
        echo CajaAhorroDao::DevolucionRetiro($_POST);
    }

    public function HistoricoSolicitudRetiro($p = 1)
    {
        $producto = $_POST['producto'] ?? $p;
        $fi = $_POST['fechaI'] ?? date('Y-m-d');
        $ff = $_POST['fechaF'] ?? date('Y-m-d');
        $estatus = $_POST['estatus'] ?? "1";
        $tipo = $_POST['tipo'] ?? "";

        $historico = json_decode(CajaAhorroDao::HistoricoSolicitudRetiro(["producto" => $producto, "fechaI" => $fi, "fechaF" => $ff, "estatus" => $estatus, "tipo" => $tipo]));
        $detalles = $historico->success ? $historico->datos : [];

        $tabla = "";
        foreach ($detalles as $key1 => $detalle) {
            $tabla .= "<tr>";
            $acciones = "";
            foreach ($detalle as $key2 => $valor) {
                if ($key2 === "ID") continue;
                $v = $valor;
                if ($key2 === "MONTO") $v = "$ " . number_format($valor, 2);

                $tabla .= "<td style='vertical-align: middle;'>$v</td>";

                if ($key2 === "ESTATUS" && $valor === "APROBADO") {
                    $acciones .= "<button type='button' class='btn btn-success btn-circle' onclick='" .
                        ($detalle->FECHA_SOLICITUD == date("d/m/Y") ? "actualizaEstatus(3, {$detalle->ID})" :
                            "validaFechaEntrega(\"{$detalle->FECHA_SOLICITUD}\")") .
                        "'><i class='glyphicon glyphicon-transfer'></i></button>";
                    $acciones .= "<button type='button' class='btn btn-danger btn-circle' onclick='actualizaEstatus(4, {$detalle->ID})'><i class='fa fa-trash'></i></button>";
                }
            }

            $tabla .= "<td style='vertical-align: middle;'>" . $acciones . "</td>";
            $tabla .= "</tr>";
        }

        $r = ["success" => true, "datos" => $tabla];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') echo json_encode($r);
        else return $r;
    }

    //********************INVERSIONES********************//
    // Apertura de contratos para cuentas de inversión
    public function ContratoInversion()
    {
        $saldoMinimoApertura = CajaAhorroDao::GetSaldoMinimoInversion();
        $tasas = CajaAhorroDao::GetTasas();
        $tasas = $tasas ? json_encode($tasas) : "[]";
        $suc = $_SESSION['cdgco_ahorro'] !== "NULL" ? $_SESSION['cdgco_ahorro'] : CajaAhorroDao::GetSucCajeraAhorro($_SESSION['usuario'])['CDGCO_AHORRO'];
        $usr = $_SESSION['usuario'];

        $extraFooter = <<<html
        <script>
            const saldoMinimoApertura = $saldoMinimoApertura
            const montoMaximo = 1000000
            const sucursal_ahorro = "$suc"
            const usuario_ahorro = "$usr"
            const noSucursal = "{$_SESSION['cdgco_ahorro']}"
            let tasasDisponibles
            let huellas = 0
            let mano
         
            try {
                tasasDisponibles = JSON.parse('$tasas')
            } catch (error) {
                console.error(error)
                tasasDisponibles = []
            }
            let valKD = false
         
            {$this->showError}
            {$this->showSuccess}
            {$this->showInfo}
            {$this->confirmarMovimiento}
            {$this->validarYbuscar}
            {$this->buscaCliente}
            {$this->getHoy}
            {$this->soloNumeros}
            {$this->numeroLetras}
            {$this->primeraMayuscula}
            {$this->muestraPDF}
            {$this->imprimeTicket}
            {$this->imprimeContrato}
            {$this->sinContrato}
            {$this->addParametro}
            {$this->parseaNumero}
            {$this->formatoMoneda}
            {$this->limpiaMontos}
            {$this->consultaServidor}
            {$this->showBloqueo}
            {$this->validaHorarioOperacion}
            {$this->valida_MCM_Complementos}
            {$this->showHuella}
            {$this->validaHuella}
            {$this->autorizaOperacion}
         
            window.onload = () => {
                validaHorarioOperacion("{$_SESSION['inicio']}", "{$_SESSION['fin']}")
            }
             
            const llenaDatosCliente = (datos) => {
                const saldoActual = parseaNumero(datos.SALDO)
                         
                huellas = datos.HUELLAS
                document.querySelector("#nombre").value = datos.NOMBRE
                document.querySelector("#curp").value = datos.CURP
                document.querySelector("#contrato").value = datos.CONTRATO
                document.querySelector("#cliente").value = datos.CDGCL
                document.querySelector("#saldoActual").value = formatoMoneda(saldoActual)
                document.querySelector("#saldoFinal").value = formatoMoneda(saldoActual)
                if (saldoActual >= saldoMinimoApertura) return document.querySelector("#monto").disabled = false
                
                showError("No es posible hacer la apertura de inversión.\\nEl saldo mínimo de apertura es de " + saldoMinimoApertura.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' }) + 
                "\\nEl saldo actual del cliente es de " + saldoActual.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' }))
            }
            
            const limpiaDatosCliente = () => {
                huellas = 0
                document.querySelector("#registroOperacion").reset()
                document.querySelector("#monto").disabled = true
                document.querySelector("#btnRegistraOperacion").disabled = true
                document.querySelector("#plazo").innerHTML = ""
                document.querySelector("#plazo").disabled = true
                habiltaEspecs()
            }
            
            const validaDeposito = (e) => {
                if (!valKD) return
                
                let monto = parseaNumero(e.target.value) || 0
                if (monto <= 0) {
                    e.preventDefault()
                    e.target.value = ""
                }
                
                if (monto > montoMaximo) {
                    e.preventDefault()
                    monto = montoMaximo
                    e.target.value = monto
                }
                
                const valor = e.target.value.split(".")
                if (valor[1] && valor[1].length > 2) {
                    e.preventDefault()
                    e.target.value = parseaNumero(valor[0] + "." + valor[1].substring(0, 2))
                }
                 
                const saldoFinal = parseaNumero(document.querySelector("#saldoActual").value) - monto
                document.querySelector("#montoOperacion").value = formatoMoneda(monto)
                document.querySelector("#saldoFinal").value = formatoMoneda(saldoFinal < 0 ? 0 : saldoFinal)
                document.querySelector("#monto_letra").value = numeroLetras(monto)
                compruebaSaldoFinal(saldoFinal)
                habiltaEspecs(monto)
                compruebaSaldoMinimo()
            }
            
            const compruebaSaldoMinimo = () => {
                const monto = parseaNumero(document.querySelector("#monto").value)
                let mMax = 0
                
                const tasas =  tasasDisponibles
                .filter(tasa => {
                    const r = monto >= saldoMinimoApertura && tasa.MONTO_MINIMO <= monto 
                    mMax = r ? tasa.MONTO_MINIMO : mMax
                    return r
                })
                .filter(tasa => tasa.MONTO_MINIMO == mMax)
                 
                if (tasas.length > 0) {
                    document.querySelector("#plazo").innerHTML = tasas.map(tasa => "<option value='" + tasa.CODIGO + "'>" + tasa.PLAZO + "</option>").join("")
                    document.querySelector("#plazo").disabled = false
                    cambioPlazo()
                    return 
                }
                 
                document.querySelector("#plazo").innerHTML = ""
                document.querySelector("#plazo").disabled = true
                document.querySelector("#rendimiento").value = ""
            }
             
            const cambioPlazo = () => {
                const info = tasasDisponibles.find(tasa => tasa.CODIGO == document.querySelector("#plazo").value)
                const plazo = parseaNumero(info.PLAZO_NUMERO)
                const tasa = parseaNumero(info.TASA)
                const monto = parseaNumero(document.querySelector("#monto").value) 
                if (tasa) {
                    document.querySelector("#rendimiento").value = formatoMoneda(monto * plazo * ((tasa/100) / 12))
                    document.querySelector("#leyendaRendimiento").innerText = "* Rendimiento calculado con una tasa anual fija del " + info.TASA + "%"
                    return
                }
                 
                document.querySelector("#rendimiento").value = ""
                document.querySelector("#leyendaRendimiento").innerText = ""
            }
             
            const compruebaSaldoFinal = saldoFinal => {
                if (saldoFinal < 0) {
                    document.querySelector("#saldoFinal").setAttribute("style", "color: red")
                    document.querySelector("#tipSaldo").setAttribute("style", "opacity: 100%;")
                } else {
                    document.querySelector("#saldoFinal").removeAttribute("style")
                    document.querySelector("#tipSaldo").setAttribute("style", "opacity: 0%;")
                }
                habilitaBoton()
            }
             
            const habilitaBoton = (e) => {
                if (e && e.target.id === "plazo") cambioPlazo()
                document.querySelector("#btnRegistraOperacion").disabled = !(parseaNumero(document.querySelector("#saldoFinal").value) >= 0 && parseaNumero(document.querySelector("#montoOperacion").value) >= saldoMinimoApertura)
            }
             
            const habiltaEspecs = (monto = parseaNumero(document.querySelector("#monto").value)) => {
                document.querySelector("#plazo").disabled = !(monto >= saldoMinimoApertura)
                document.querySelector("#renovacion").disabled = !(monto >= saldoMinimoApertura)
                 
                if (monto < saldoMinimoApertura) {
                    document.querySelector("#plazo").innerHTML = ""
                    document.querySelector("#rendimiento").value = ""
                    document.querySelector("#renovacion").selectedIndex = 0
                }
            }
            
            const registraOperacion = async (e) => {
                if (!await valida_MCM_Complementos()) return
                 
                e.preventDefault()
                const datos = $("#registroOperacion").serializeArray()
                 
                limpiaMontos(datos, ["saldoActual", "montoOperacion", "saldoFinal"])
                addParametro(datos, "sucursal", sucursal_ahorro)
                addParametro(datos, "ejecutivo", usuario_ahorro)
                 
                datos.push({ name: "tasa", value: document.querySelector("#plazo").value })
                 
                const plazo = document.querySelector("#plazo")
                confirmarMovimiento(
                    "Apertura de cuenta de inversión",
                    "¿Está segur(a) de continuar con la apertura de la cuenta de inversión por la cantidad de "
                    + parseaNumero(document.querySelector("#montoOperacion").value).toLocaleString("es-MX", { style: "currency", currency: "MXN" })
                    + " (" + document.querySelector("#monto_letra").value + ")" 
                    + " a un plazo de " + plazo.options[plazo.selectedIndex].text + "?"
                ).then((continuar) => {
                    if (!continuar) return
                    if (huellas > 0) return showHuella(true, datos)
                    enviaRegistroOperacion(datos)
                })
            }

            const enviaRegistroOperacion = (datos) => {
                consultaServidor("/Ahorro/RegistraInversion/", $.param(datos), (respuesta) => {
                    if (!respuesta.success){
                        console.log(respuesta.error)
                        return showError(respuesta.mensaje)
                    }
                    showSuccess(respuesta.mensaje).then(() => {
                        imprimeContrato(respuesta.datos.codigo, 2)
                        imprimeTicket(respuesta.datos.ticket, sucursal_ahorro)
                        limpiaDatosCliente()
                    })
                })
            }
             
            const validaBlur = (e) => {
                const monto = parseaNumero(e.target.value)
                 
                if (monto < saldoMinimoApertura) {
                    e.target.value = ""
                    return showError("El monto mínimo de apertura es de " + saldoMinimoApertura.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' }))
                }
            }
        </script>
        html;

        $sucursales = CajaAhorroDao::GetSucursalAsignadaCajeraAhorro($this->__usuario);
        $opcSucursales = "";
        foreach ($sucursales as $sucursales) {
            $opcSucursales .= "<option value='{$sucursales['CODIGO']}'>{$sucursales['NOMBRE']}</option>";
            $suc_eje = $sucursales['CODIGO'];
        }

        $ejecutivos = CajaAhorroDao::GetEjecutivosSucursal($suc_eje);
        $opcEjecutivos = "";
        foreach ($ejecutivos as $ejecutivos) {
            $opcEjecutivos .= "<option value='{$ejecutivos['ID_EJECUTIVO']}'>{$ejecutivos['EJECUTIVO']}</option>";
        }
        $opcEjecutivos .= "<option value='{$this->__usuario}'>{$this->__nombre} - CAJER(A)</option>";

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Contrato Inversión", [$this->swal2, $this->huellas])));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::set('fecha', date('d/m/Y H:i:s'));
        view::set('ejecutivos', $opcEjecutivos);
        View::render("caja_menu_contrato_inversion");
    }

    public function RegistraInversion()
    {
        $contrato = CajaAhorroDao::RegistraInversion($_POST);
        echo $contrato;
    }

    // Visualización de cuentas de inversión
    public function ConsultaInversion()
    {
        $extraFooter = <<<html
        <script>
            const noSucursal = "{$_SESSION['cdgco_ahorro']}"
         
            {$this->showError}
            {$this->showSuccess}
            {$this->showInfo}
            {$this->sinContrato}
            {$this->validarYbuscar}
            {$this->buscaCliente}
            {$this->soloNumeros}
            {$this->primeraMayuscula}
            {$this->consultaServidor}
            {$this->configuraTabla}
            {$this->showHuella}
            {$this->validaHuella}
             
            $(document).ready(configuraTabla("muestra-cupones"))
             
            const llenaDatosCliente = (datosCliente) => {
                consultaServidor("/Ahorro/GetInversiones/", { contrato: datosCliente.CONTRATO }, (respuesta) => {
                    if (!respuesta.success) return showError(respuesta.mensaje)
                    const inversiones = respuesta.datos
                    if (!inversiones) return
                    let inversionesTotal = 0
                    
                    const tTMP = $("#muestra-cupones").DataTable()
                    if (tTMP) tTMP.destroy()
                    
                    const filas = document.createDocumentFragment()
                    inversiones.forEach((inversion) => {
                        const fila = document.createElement("tr")
                        Object.keys(inversion).forEach((key) => {
                            let dato = inversion[key]
                            if (["RENDIMIENTO", "MONTO"].includes(key))
                                dato = parseFloat(dato).toLocaleString("es-MX", {
                                    style: "currency",
                                    currency: "MXN"
                                })
            
                            inversionesTotal += key === "MONTO" ? parseFloat(inversion[key]) : 0
                            const celda = document.createElement("td")
                            celda.innerText = dato
                            fila.appendChild(celda)
                        })
                        filas.appendChild(fila)
                    })
                    
                    document.querySelector("#datosTabla").appendChild(filas)
                    document.querySelector("#inversion").value = inversionesTotal.toLocaleString("es-MX", {
                        style: "currency",
                        currency: "MXN"
                    })
                    document.querySelector("#cliente").value = datosCliente.CDGCL
                    document.querySelector("#contrato").value = datosCliente.CONTRATO
                    document.querySelector("#nombre").value = datosCliente.NOMBRE
                    document.querySelector("#curp").value = datosCliente.CURP
                    configuraTabla("muestra-cupones")
                }, "GET")
            }
                 
            const limpiaDatosCliente = () => {
                const tTMP = $("#muestra-cupones").DataTable()
                if (tTMP) tTMP.destroy()
                document.querySelector("#datosTabla").innerHTML = ""
                document.querySelector("#cliente").value = ""
                document.querySelector("#contrato").value = ""
                document.querySelector("#inversion").value = ""
                document.querySelector("#nombre").value = ""
                document.querySelector("#curp").value = ""
                configuraTabla("muestra-cupones")
            }
        </script>
        html;

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Consulta Inversiones", [$this->swal2, $this->huellas])));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::render("caja_menu_estatus_inversion");
    }

    public function GetInversiones()
    {
        $inversiones = CajaAhorroDao::GetInversiones($_GET);
        echo $inversiones;
    }

    //********************CUENTA PEQUES********************//
    // Apertura de contratos para cuentas de ahorro Peques
    public function ContratoCuentaPeque()
    {
        $extraFooter = <<<html
        <script>
            window.onload = () => {
                if(document.querySelector("#clienteBuscado").value !== "") buscaCliente()
            }
        
            const noSucursal = "{$_SESSION['cdgco_ahorro']}"
            let valKD = false
             
            {$this->showError}
            {$this->showSuccess}
            {$this->showInfo}
            {$this->confirmarMovimiento}
            {$this->validarYbuscar}
            {$this->getHoy}
            {$this->soloNumeros}
            {$this->numeroLetras}
            {$this->primeraMayuscula}
            {$this->muestraPDF}
            {$this->imprimeContrato}
            {$this->addParametro}
            {$this->consultaServidor}
            {$this->showHuella}
            {$this->validaHuella}
             
            const buscaCliente = () => {
                const noCliente = document.querySelector("#clienteBuscado")
                 
                if (!noCliente.value) {
                    limpiaDatosCliente()
                    return showError("Ingrese un número de cliente a buscar.")
                }
                
                consultaServidor("/Ahorro/BuscaClientePQ/", { cliente: noCliente.value }, (respuesta) => {
                    if (!respuesta.success) {
                        if (respuesta.datos) {
                            const datosCliente = respuesta.datos
                            if (datosCliente["NO_CONTRATOS"] == 0) {
                                swal({
                                    title: "Cuenta de ahorro Peques™",
                                    text: "El cliente " + noCliente.value + " no tiene una cuenta de ahorro.\\nDesea aperturar una cuenta de ahorro en este momento?",
                                    icon: "info",
                                    buttons: ["No", "Sí"],
                                    dangerMode: true
                                }).then((abreCta) => {
                                    if (abreCta) return window.location.href = "/Ahorro/ContratoCuentaCorriente/?cliente=" + noCliente.value
                                })
                                return
                            }
                            if (datosCliente["NO_CONTRATOS"] == 1 && datosCliente["CONTRATO_COMPLETO"] == 0) {
                                swal({
                                    title: "Cuenta de ahorro Peques™",
                                    text: "El cliente " + noCliente.value + " no ha completado el proceso de apertura de la cuenta de ahorro.\\nDesea completar el proceso en este momento?",
                                    icon: "info",
                                    buttons: ["No", "Sí"],
                                    dangerMode: true
                                }).then((abreCta) => {
                                    if (abreCta) return window.location.href = "/Ahorro/ContratoCuentaCorriente/?cliente=" + noCliente.value
                                })
                                return
                            }
                        }
                            
                        limpiaDatosCliente()
                        return showError(respuesta.mensaje)
                    }
                        
                    const datosCliente = respuesta.datos
                     
                    document.querySelector("#nombre1").disabled = false
                    document.querySelector("#nombre2").disabled = false
                    document.querySelector("#apellido1").disabled = false
                    document.querySelector("#apellido2").disabled = false
                    document.querySelector("#fecha_nac").disabled = false
                    document.querySelector("#ciudad").disabled = false
                    document.querySelector("#curp").disabled = false
                        
                    document.querySelector("#fechaRegistro").value = datosCliente.FECHA_REGISTRO
                    document.querySelector("#noCliente").value = noCliente.value
                    document.querySelector("#nombre").value = datosCliente.NOMBRE
                    document.querySelector("#direccion").value = datosCliente.DIRECCION
                    noCliente.value = ""
                })
            }
             
            const limpiaDatosCliente = () => {
                document.querySelector("#registroInicialAhorro").reset()
                 
                document.querySelector("#fechaRegistro").value = ""
                document.querySelector("#noCliente").value = ""
                document.querySelector("#nombre").value = ""
                document.querySelector("#curp").value = ""
                document.querySelector("#edad").value = ""
                document.querySelector("#direccion").value = ""
                 
                document.querySelector("#nombre1").disabled = true
                document.querySelector("#nombre2").disabled = true
                document.querySelector("#apellido1").disabled = true
                document.querySelector("#apellido2").disabled = true
                document.querySelector("#fecha_nac").disabled = true
                document.querySelector("#ciudad").disabled = true
                document.querySelector("#curp").disabled = true
                document.querySelector("#btnGeneraContrato").disabled = true
            }
            
            const generaContrato = async (e) => {
                e.preventDefault()
                 
                if (document.querySelector("#curp").value.length !== 18) {
                    showError("La CURP debe tener 18 caracteres.")
                    return
                }
                 
                if (document.querySelector("#edad").value > 17) {
                    showError("El peque a registrar debe tener menos de 18 años.")
                    return 
                }
                 
                if (document.querySelector("#apellido2").value === "") {
                    const respuesta = await swal({
                        title: "Cuenta de ahorro Peques™",
                        text: "No se ha capturado el segundo apellido.\\n¿Desea continuar con el registro?",
                        icon: "info",
                        buttons: ["No", "Sí"]
                    })
                    if (!respuesta) return
                }
                 
                const cliente = document.querySelector("#nombre").value
                 
                confirmarMovimiento("Cuenta de ahorro Peques™",
                    "¿Está segura de continuar con la apertura de la cuenta Peques™ asociada al cliente "
                    + cliente
                    + "?"
                ).then((continuar) => {
                    if (!continuar) return
                    const noCredito = document.querySelector("#noCliente").value
                    const datos = $("#registroInicialAhorro").serializeArray()
                    addParametro(datos, "credito", noCredito)
                    addParametro(datos, "sucursal", noSucursal)
                    addParametro(datos, "ejecutivo", "{$_SESSION['usuario']}")
                    addParametro(datos, "tasa", document.querySelector("#tasa").value)
                    
                    datos.forEach((dato) => {
                        if (dato.name === "sexo") {
                            dato.value = document.querySelector("#sexoH").checked
                        }
                    })
                    
                    consultaServidor("/Ahorro/AgregaContratoAhorroPQ/", $.param(datos), (respuesta) => {
                        if (!respuesta.success) {
                            console.error(respuesta.error)
                            limpiaDatosCliente()
                            return showError(respuesta.mensaje)
                        }
                    
                        const contrato = respuesta.datos
                        limpiaDatosCliente()
                        showSuccess("Se ha generado el contrato: " + contrato.contrato).then(() => {
                            imprimeContrato(contrato.contrato, 3)
                        })
                    })
                })
            }
             
            const validaDeposito = (e) => {
                if (!valKD) return
                 
                const monto = parseFloat(e.target.value) || 0
                if (monto <= 0) {
                    e.preventDefault()
                    e.target.value = ""
                    showError("El monto a depositar debe ser mayor a 0")
                }
                 
                if (monto > 1000000) {
                    e.preventDefault()
                    e.target.value = 1000000.00
                }
                 
                const valor = e.target.value.split(".")
                if (valor[1] && valor[1].length > 2) {
                    e.preventDefault()
                    e.target.value = parseFloat(valor[0] + "." + valor[1].substring(0, 2))
                }
                
                document.querySelector("#deposito_inicial_letra").value = numeroLetras(parseFloat(e.target.value))
                calculaSaldoFinal(e)
            }
             
            const calculaSaldoFinal = (e) => {
                const monto = parseFloat(e.target.value)
                document.querySelector("#deposito").value = monto.toFixed(2)
                const saldoInicial = (monto - parseFloat(document.querySelector("#inscripcion").value)).toFixed(2)
                document.querySelector("#saldo_inicial").value = saldoInicial > 0 ? saldoInicial : "0.00"
                document.querySelector("#deposito_inicial_letra").value = primeraMayuscula(numeroLetras(monto))
                    
                if (saldoInicial < saldoMinimoApertura) {
                    document.querySelector("#saldo_inicial").setAttribute("style", "color: red")
                    document.querySelector("#tipSaldo").setAttribute("style", "opacity: 100%;")
                    document.querySelector("#registraDepositoInicial").disabled = true
                } else {
                    document.querySelector("#saldo_inicial").removeAttribute("style")
                    document.querySelector("#tipSaldo").setAttribute("style", "opacity: 0%;")
                    document.querySelector("#registraDepositoInicial").disabled = false
                }
            }
             
            const iniveCambio = (e) => e.preventDefault()
             
            const camposLlenos = (e) => {
                document.querySelector("#nombre1").value = document.querySelector("#nombre1").value.toUpperCase()
                document.querySelector("#nombre2").value = document.querySelector("#nombre2").value.toUpperCase()
                document.querySelector("#apellido1").value = document.querySelector("#apellido1").value.toUpperCase()
                document.querySelector("#apellido2").value = document.querySelector("#apellido2").value.toUpperCase()
                 
                const val = () => {
                    const campos = [
                        document.querySelector("#nombre1").value,
                        document.querySelector("#apellido1").value,
                        document.querySelector("#fecha_nac").value,
                        document.querySelector("#ciudad").value,
                        document.querySelector("#curp").value,
                        document.querySelector("#edad").value,
                        document.querySelector("#direccion").value,
                        document.querySelector("#confirmaDir").checked,
                        document.querySelector("#edad").value <= 17
                    ]
                    
                    return campos.every((campo) => campo)
                }
                if (e.target.id === "fecha_nac") calculaEdad(e)
                if (e.target.id !== "curp") generaCURP({
                    nombre1: document.querySelector("#nombre1").value,
                    nombre2: document.querySelector("#nombre1").value,
                    apellido1: document.querySelector("#apellido1").value,
                    apellido2: document.querySelector("#apellido2").value,
                    fecha: document.querySelector("#fecha_nac").value,
                    sexo: document.querySelector("#sexoH").checked ? "H" : "M",
                    entidad: document.querySelector("#ciudad").value
                })
                document.querySelector("#btnGeneraContrato").disabled = !val()
            }
             
            const calculaEdad = (e) => {
                const fecha = new Date(e.target.value)
                const hoy = new Date()
                let edad = hoy.getFullYear() - fecha.getFullYear()
                 
                const mesActual = hoy.getMonth()
                const diaActual = hoy.getDate()
                const mesNacimiento = fecha.getMonth()
                const diaNacimiento = fecha.getDate()
                if (mesActual < mesNacimiento || (mesActual === mesNacimiento && diaActual < diaNacimiento)) edad--
                 
                document.querySelector("#edad").value = edad
                if (edad > 17) {
                    document.querySelector("#edad").setAttribute("style", "color: red")
                    showError("El peque a registrar debe tener menos de 18 años.")
                } else document.querySelector("#edad").removeAttribute("style")
            }
             
            const generaCURP = (datos) => {
                datos.apellido1 = datos.apellido1.toUpperCase()
                datos.apellido2 = datos.apellido2.toUpperCase()
                datos.nombre1 = datos.nombre1.toUpperCase()
                datos.nombre2 = datos.nombre2.toUpperCase()
                 
                const CURP = []
                CURP[0] = datos.apellido1 ? datos.apellido1.charAt(0) : "X"
                CURP[1] = datos.apellido1 ? datos.apellido1.slice(1).replace(/\a\e\i\o\u/gi, "").charAt(0) : "X"
                CURP[2] = datos.apellido2 ? datos.apellido2.charAt(0) : "X"
                CURP[3] = datos.nombre1 ? datos.nombre1.charAt(0) : "X"
                CURP[4] = datos.fecha ? datos.fecha.slice(2, 4) : "00"
                CURP[5] = datos.fecha ? datos.fecha.slice(5, 7) : "00"
                CURP[6] = datos.fecha ? datos.fecha.slice(8, 10) : "00"
                CURP[7] = datos.sexo ? datos.sexo : "X"
                CURP[8] = datos.entidad ? datos.entidad : "NE"
                CURP[9] = datos.apellido1 ? datos.apellido1.slice(1).replace(/[aeiou]/gi, "").charAt(0) : "X"
                CURP[10] = datos.apellido2 ? datos.apellido2.slice(1).replace(/[aeiou]/gi, "").charAt(0) : "X"
                CURP[11] = datos.nombre1 ? datos.nombre1.slice(1).replace(/[aeiou]/gi, "").charAt(0) : "X"
                CURP[12] = "00"
                
                document.querySelector("#curp").value = CURP.join("")
            }
        </script>
        html;

        $ComboEntidades = CajaAhorroDao::GetEFed();

        $opciones_ent = "";
        foreach ($ComboEntidades as $key => $val2) {
            $opciones_ent .= <<<html
                <option  value="{$val2['CDGCURP']}"> {$val2['NOMBRE']}</option>
            html;
        }

        if ($_GET['cliente']) View::set('cliente', $_GET['cliente']);
        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Contrato Cuenta Peque", [$this->swal2, $this->huellas])));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::set('fecha', date('Y-m-d'));
        View::set('opciones_ent', $opciones_ent);
        View::render("caja_menu_contrato_peque");
    }

    public function BuscaClientePQ()
    {
        if (self::ValidaHorario()) {
            echo CajaAhorroDao::BuscaClienteNvoContratoPQ($_POST);
            return;
        }
        echo self::FueraHorario();
    }

    public function AgregaContratoAhorroPQ()
    {
        $contrato = CajaAhorroDao::AgregaContratoAhorroPQ($_POST);
        echo $contrato;
    }

    public function BuscaContratoPQ()
    {
        if (self::ValidaHorario()) {
            echo CajaAhorroDao::BuscaClienteContratoPQ($_POST);
            return;
        }
        echo self::FueraHorario();
    }

    // Movimientos sobre cuentas de ahorro Peques
    public function CuentaPeque()
    {
        $maximoRetiroDia = 50000;
        $montoMaximoRetiro = 1000000;

        $extraFooter = <<<html
        <script>
            const noSucursal = "{$_SESSION['cdgco_ahorro']}"
            const maximoRetiroDia = $maximoRetiroDia
            const montoMaximoRetiro = $montoMaximoRetiro
            let retiroDispobible = maximoRetiroDia
            let retiroBloqueado = false
            let valKD = false
            let huellas = 0
            let mano
         
            {$this->showError}
            {$this->showSuccess}
            {$this->showInfo}
            {$this->showWarning}
            {$this->confirmarMovimiento}
            {$this->validarYbuscar}
            {$this->getHoy}
            {$this->soloNumeros}
            {$this->numeroLetras}
            {$this->primeraMayuscula}
            {$this->muestraPDF}
            {$this->imprimeTicket}
            {$this->addParametro}
            {$this->parseaNumero}
            {$this->formatoMoneda}
            {$this->limpiaMontos}
            {$this->consultaServidor}
            {$this->showBloqueo}
            {$this->validaHorarioOperacion}
            {$this->valida_MCM_Complementos}
            {$this->showHuella}
            {$this->validaHuella}
            {$this->autorizaOperacion}
         
            window.onload = () => {
                validaHorarioOperacion("{$_SESSION['inicio']}", "{$_SESSION['fin']}")
                if (document.querySelector("#clienteBuscado").value !== "") buscaCliente()
            }
            
            const buscaCliente = () => {
                retiroBloqueado = false
                const noCliente = document.querySelector("#clienteBuscado").value
                
                if (!noCliente) {
                    limpiaDatosCliente()
                    return showError("Ingrese un número de cliente a buscar.")
                }
                 
                consultaServidor("/Ahorro/BuscaContratoPQ/", { cliente: noCliente }, (respuesta) => {
                    limpiaDatosCliente()
                    if (!respuesta.success) {
                        if (!respuesta.datos) return showError(respuesta.mensaje)
                        const datosCliente = respuesta.datos
                            
                        if (datosCliente["NO_CONTRATOS"] == 0) {
                            swal({
                                title: "Cuenta de ahorro Peques™",
                                text: "La cuenta " + noCliente + " no tiene una cuenta de ahorro.\\nDesea realizar la apertura en este momento?",
                                icon: "info",
                                buttons: ["No", "Sí"],
                                dangerMode: true
                            }).then((realizarDeposito) => {
                                if (realizarDeposito) return window.location.href = "/Ahorro/ContratoCuentaCorriente/?cliente=" + noCliente
                            })
                            return
                        }
                        if (datosCliente["NO_CONTRATOS"] == 1 && datosCliente["CONTRATO_COMPLETO"] == 0) {
                            swal({
                                title: "Cuenta de ahorro Peques™",
                                text: "La cuenta " + noCliente + " no ha concluido con el proceso de apertura de la cuenta de ahorro.\\nDesea completar el contrato en este momento?",
                                icon: "info",
                                buttons: ["No", "Sí"],
                                dangerMode: true
                            }).then((realizarDeposito) => {
                                if (realizarDeposito) return window.location.href = "/Ahorro/ContratoCuentaCorriente/?cliente=" + noCliente
                            })
                        }
                        if (datosCliente["NO_CONTRATOS"] == 1 && datosCliente["CONTRATO_COMPLETO"] == 1) {
                            swal({
                                title: "Cuenta de ahorro Peques™",
                                text: "La cuenta " + noCliente + " no tiene asignadas cuentas Peques™.\\nDesea aperturar una cuenta Peques™ en este momento?",
                                icon: "info",
                                buttons: ["No", "Sí"],
                                dangerMode: true
                            }).then((realizarDeposito) => {
                                if (realizarDeposito) return window.location.href = "/Ahorro/ContratoCuentaPeque/?cliente=" + noCliente
                            })
                            return
                        }
                    }
                 
                    if (respuesta.datos[0].SUCURSAL !== noSucursal) {
                        limpiaDatosCliente()
                        return showError("El cliente " + noCliente + " no puede realizar transacciones en esta sucursal, su contrato esta asignado a la sucursal " + respuesta.datos[0].NOMBRE_SUCURSAL + ", contacte a la gerencia de Administración.")
                    }
                     
                    const datosCliente = respuesta.datos
                    const contratos = document.createDocumentFragment()
                    const seleccionar = document.createElement("option")
                    seleccionar.value = ""
                    seleccionar.disabled = true
                    seleccionar.innerText = "Seleccionar"
                    contratos.appendChild(seleccionar)
                        
                    datosCliente.forEach(cliente => {
                        const opcion = document.createElement("option")
                        opcion.value = cliente.CDG_CONTRATO
                        opcion.innerText = cliente.NOMBRE
                        contratos.appendChild(opcion)
                        huellas = cliente.HUELLAS
                    })
                        
                    document.querySelector("#contrato").appendChild(contratos)
                    if (document.querySelector("#contrato").options.length == 2) {
                        document.querySelector("#contrato").selectedIndex = 1
                        pqSeleccionado(datosCliente, document.querySelector("#contrato").value)
                    } else {
                        document.querySelector("#contrato").selectedIndex = 0
                        document.querySelector("#contrato").addEventListener("change", (e) => {
                            pqSeleccionado(datosCliente, e.target.value)
                        })
                    }
                    
                    if (document.querySelector("#contratoSel").value !== "") {
                        document.querySelector("#contrato").selectedIndex = document.querySelector("#contratoSel").value
                        document.querySelector("#contrato").dispatchEvent(new Event("change"))
                        document.querySelector("#retiro").checked = true
                        document.querySelector("#retiro").dispatchEvent(new Event("change"))
                    }
                    
                    document.querySelector("#clienteBuscado").value = ""
                    document.querySelector("#contrato").disabled = false
                })
            }
             
            const pqSeleccionado = (datosCliente, pq) => {
                retiroDispobible = maximoRetiroDia
                retiroBloqueado = false
                datosCliente.forEach(contrato => {
                    if (contrato.CDG_CONTRATO == pq) {
                        consultaServidor("/Ahorro/ValidaRetirosDia/", $.param({ contrato: contrato.CDG_CONTRATO }), (respuesta) => {
                            if (!respuesta.success && respuesta.datos.RETIROS >= maximoRetiroDia) {
                                showWarning("El peque " + contrato.NOMBRE + " ha alcanzado el límite de retiros diarios.")
                                retiroBloqueado = true   
                                retiroDispobible = maximoRetiroDia - respuesta.datos.RETIROS
                            }
                             
                            document.querySelector("#nombre").value = contrato.CDG_CONTRATO
                            document.querySelector("#curp").value = contrato.CURP
                            document.querySelector("#cliente").value = contrato.CDGCL
                            document.querySelector("#saldoActual").value = formatoMoneda(contrato.SALDO)
                            document.querySelector("#deposito").disabled = false
                            document.querySelector("#retiro").disabled = retiroBloqueado
                        })
                    }
                })
            }
             
            const limpiaDatosCliente = () => {
                huellas = 0
                document.querySelector("#registroOperacion").reset()
                document.querySelector("#fecha_pago").value = getHoy()
                document.querySelector("#monto").disabled = true
                document.querySelector("#deposito").disabled = true
                document.querySelector("#retiro").disabled = true
                document.querySelector("#contrato").innerHTML = ""
                document.querySelector("#contrato").disabled = true
            }
             
            const boton_contrato = (numero_contrato) => {
                const host = window.location.origin
                
                let plantilla = "<!DOCTYPE html>"
                plantilla += '<html lang="es">'
                plantilla += '<head>'
                plantilla += '<meta charset="UTF-8">'
                plantilla += '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
                plantilla += '<link rel="shortcut icon" href="' + host + '/img/logo.png">'
                plantilla += '<title>Contrato ' + numero_contrato + '</title>'
                plantilla += '</head>'
                plantilla += '<body style="margin: 0; padding: 0; background-color: #333333;">'
                plantilla +=
                    '<iframe src="' + host + '/Ahorro/ImprimeContrato/' +
                    numero_contrato +
                    '/" style="width: 100%; height: 99vh; border: none; margin: 0; padding: 0;"></iframe>'
                plantilla += "</body>"
                plantilla += "</html>"
            
                const blob = new Blob([plantilla], { type: "text/html" })
                const url = URL.createObjectURL(blob)
                window.open(url, "_blank")
            }
             
            const validaDeposito = (e) => {
                if (!valKD) return
                 
                let monto = parseaNumero(e.target.value) || 0
                if (monto <= 0) {
                    e.preventDefault()
                    e.target.value = ""
                    showError("El monto a depositar debe ser mayor a 0")
                }
                 
                if (!document.querySelector("#deposito").checked && monto > montoMaximoRetiro) {
                    monto = montoMaximoRetiro
                    swal({
                        title: "Cuenta de ahorro Peques™",
                        text: "Para retiros mayores a " + montoMaximoRetiro.toLocaleString("es-MX", { style: "currency", currency: "MXN" }) + " es necesario realizar una solicitud de retiro.\\nDesea generar una solicitud de retiro ahora?.",
                        icon: "info",
                        buttons: ["No", "Sí"],
                        dangerMode: true
                    }).then((regRetiro) => {
                        if (regRetiro) {
                            window.location.href = "/Ahorro/SolicitudRetiroCuentaPeque/?cliente=" + document.querySelector("#cliente").value + "&contrato=" + document.querySelector("#contrato").selectedIndex
                            return
                        }
                    })
                    e.target.value = monto
                }
                 
                if (monto > 1000000) {
                    monto = 1000000
                    e.preventDefault()
                    e.target.value = 1000000.00
                }
                 
                const valor = e.target.value.split(".")
                if (valor[1] && valor[1].length > 2) {
                    e.preventDefault()
                    e.target.value = parseaNumero(valor[0] + "." + valor[1].substring(0, 2))
                }
                
                document.querySelector("#monto_letra").value = numeroLetras(parseaNumero(e.target.value))
                if (document.querySelector("#deposito").checked || document.querySelector("#retiro").checked) calculaSaldoFinal()
            }
             
            const calculaSaldoFinal = () => {
                const esDeposito = document.querySelector("#deposito").checked
                const saldoActual = parseaNumero(document.querySelector("#saldoActual").value)
                const monto = parseaNumero(document.querySelector("#monto").value)
                document.querySelector("#montoOperacion").value = formatoMoneda(monto)
                document.querySelector("#saldoFinal").value = formatoMoneda(esDeposito ? saldoActual + monto : saldoActual - monto)
                compruebaSaldoFinal(document.querySelector("#saldoFinal").value)
            }
             
            const cambioMovimiento = (e) => {
                document.querySelector("#monto").disabled = false
                const esDeposito = document.querySelector("#deposito").checked
                document.querySelector("#simboloOperacion").innerText = esDeposito ? "+" : "-"
                document.querySelector("#descOperacion").innerText = (esDeposito ? "Depósito" : "Retiro") + " a cuenta ahorro corriente"
                calculaSaldoFinal()
            }
             
            const compruebaSaldoFinal = () => {
                const saldoFinal = parseaNumero(document.querySelector("#saldoFinal").value)
                if (saldoFinal < 0) {
                    document.querySelector("#saldoFinal").setAttribute("style", "color: red")
                    document.querySelector("#tipSaldo").setAttribute("style", "opacity: 100%;")
                    document.querySelector("#tipSaldo").innerText = "El monto a retirar no puede ser mayor al saldo de la cuenta."
                    document.querySelector("#btnRegistraOperacion").disabled = true
                    return
                } else {
                    document.querySelector("#saldoFinal").removeAttribute("style")
                    document.querySelector("#tipSaldo").setAttribute("style", "opacity: 0%;")
                }
                if (document.querySelector("#retiro").checked && retiroDispobible < parseaNumero(document.querySelector("#montoOperacion").value)) {
                    document.querySelector("#saldoFinal").setAttribute("style", "color: red")
                    document.querySelector("#tipSaldo").setAttribute("style", "opacity: 100%;")
                    document.querySelector("#tipSaldo").innerText = "El monto a retirar excede el límite de retiros diarios, disponible para retirar el día de hoy: " + retiroDispobible.toLocaleString("es-MX", { style: "currency", currency: "MXN" })
                    document.querySelector("#btnRegistraOperacion").disabled = true
                    return
                }
                document.querySelector("#btnRegistraOperacion").disabled = !(saldoFinal >= 0 && parseaNumero(document.querySelector("#montoOperacion").value) > 0)
            }
             
            const registraOperacion = async (e) => {
                if (!await valida_MCM_Complementos()) return
                 
                e.preventDefault()
                const datos = $("#registroOperacion").serializeArray()
                 
                limpiaMontos(datos, ["saldoActual", "montoOperacion", "saldoFinal"])
                addParametro(datos, "sucursal", noSucursal)
                addParametro(datos, "ejecutivo", "{$_SESSION['usuario']}")
                addParametro(datos, "producto", "cuenta de ahorro Peques")
                 
                if (!document.querySelector("#deposito").checked && !document.querySelector("#retiro").checked) {
                    return showError("Seleccione el tipo de operación a realizar.")
                }
                
                datos.forEach((dato) => {
                    if (dato.name === "esDeposito") {
                        dato.value = document.querySelector("#deposito").checked
                    }
                })
                 
                confirmarMovimiento(
                    "Confirmación de movimiento de cuenta ahorro Peques™",
                    "¿Está segur(a) de continuar con el registro de un "
                    + (document.querySelector("#deposito").checked ? "depósito" : "retiro")
                    + " de cuenta ahorro peque, por la cantidad de "
                    + parseaNumero(document.querySelector("#montoOperacion").value).toLocaleString("es-MX", { style: "currency", currency: "MXN" })
                    + " (" + document.querySelector("#monto_letra").value + ")?"
                ).then((continuar) => {
                    if (!continuar) return
                    if (!document.querySelector("#deposito").checked && huellas > 0) return showHuella(true, datos)
                    enviaRegistroOperacion(datos)
                })
            }

            const enviaRegistroOperacion = (datos) => {
                consultaServidor("/Ahorro/registraOperacion/", $.param(datos), (respuesta) => {
                    if (!respuesta.success){
                        if (respuesta.error) return showError(respuesta.error)
                        return showError(respuesta.mensaje)
                    }
                    showSuccess(respuesta.mensaje).then(() => {
                        imprimeTicket(respuesta.datos.ticket, noSucursal)
                        limpiaDatosCliente()
                    })
                })
            }
        </script>
        html;

        if ($_GET['cliente']) View::set('cliente', $_GET['cliente']);
        if ($_GET['contrato']) View::set('contratoSel', $_GET['contrato']);

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Cuenta Peque", [$this->swal2, $this->huellas])));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::set('fecha', date('d/m/Y H:i:s'));
        View::render("caja_menu_peque");
    }

    public function SolicitudRetiroCuentaPeque()
    {
        $montoMinimoRetiro = 10000;
        $montoMaximoExpress = 49999.99;
        $montoMaximoRetiro = 1000000;

        $extraFooter = <<<html
        <script>
            window.onload = () => {
                if(document.querySelector("#clienteBuscado").value !== "") buscaCliente()
            }
            
            const noSucursal = "{$_SESSION['cdgco_ahorro']}"
            const montoMinimoRetiro = $montoMinimoRetiro
            const montoMaximoExpress = $montoMaximoExpress
            const montoMaximoRetiro = $montoMaximoRetiro
            let valKD = false
            let huellas = 0
            let mano
         
            {$this->showError}
            {$this->showSuccess}
            {$this->showInfo}
            {$this->confirmarMovimiento}
            {$this->validarYbuscar}
            {$this->soloNumeros}
            {$this->primeraMayuscula}
            {$this->numeroLetras}
            {$this->muestraPDF}
            {$this->addParametro}
            {$this->sinContrato}
            {$this->getHoy}
            {$this->parseaNumero}
            {$this->formatoMoneda}
            {$this->limpiaMontos}
            {$this->consultaServidor}
            {$this->showHuella}
            {$this->validaHuella}
            {$this->autorizaOperacion}
             
            const buscaCliente = () => {
                const noCliente = document.querySelector("#clienteBuscado").value
                
                if (!noCliente) {
                    limpiaDatosCliente()
                    return showError("Ingrese un número de cliente a buscar.")
                }
                 
                consultaServidor("/Ahorro/BuscaContratoPQ/", { cliente: noCliente }, (respuesta) => {
                    limpiaDatosCliente()
                    if (!respuesta.success) {
                        if (!respuesta.datos) return showError(respuesta.mensaje)
                        const datosCliente = respuesta.datos
                            
                        if (datosCliente["NO_CONTRATOS"] == 0) {
                            swal({
                                title: "Cuenta de ahorro Peques™",
                                text: "La cuenta " + noCliente + " no tiene una cuenta de ahorro.\\nDesea realizar la apertura en este momento?",
                                icon: "info",
                                buttons: ["No", "Sí"],
                                dangerMode: true
                            }).then((realizarDeposito) => {
                                if (realizarDeposito) return window.location.href = "/Ahorro/ContratoCuentaCorriente/?cliente=" + noCliente
                            })
                            return
                        }
                        if (datosCliente["NO_CONTRATOS"] == 1 && datosCliente["CONTRATO_COMPLETO"] == 0) {
                            swal({
                                title: "Cuenta de ahorro Peques™",
                                text: "La cuenta " + noCliente + " no ha concluido con el proceso de apertura de la cuenta de ahorro.\\nDesea completar el contrato en este momento?",
                                icon: "info",
                                buttons: ["No", "Sí"],
                                dangerMode: true
                            }).then((realizarDeposito) => {
                                if (realizarDeposito) return window.location.href = "/Ahorro/ContratoCuentaCorriente/?cliente=" + noCliente
                            })
                        }
                        if (datosCliente["NO_CONTRATOS"] == 1 && datosCliente["CONTRATO_COMPLETO"] == 1) {
                            swal({
                                title: "Cuenta de ahorro Peques™",
                                text: "La cuenta " + noCliente + " no tiene asignadas cuentas Peques™.\\nDesea aperturar una cuenta Peques™ en este momento?",
                                icon: "info",
                                buttons: ["No", "Sí"],
                                dangerMode: true
                            }).then((realizarDeposito) => {
                                if (realizarDeposito) return window.location.href = "/Ahorro/ContratoCuentaPeque/?cliente=" + noCliente
                            })
                            return
                        }
                    }
                 
                    if (respuesta.datos[0].SUCURSAL !== noSucursal) {
                        limpiaDatosCliente()
                        return showError("El cliente " + noCliente + " no puede realizar transacciones en esta sucursal, su contrato esta asignado a la sucursal " + respuesta.datos[0].NOMBRE_SUCURSAL + ", contacte a la gerencia de Administración.")
                    }
                     
                    const datosCliente = respuesta.datos
                    const contratos = document.createDocumentFragment()
                    const seleccionar = document.createElement("option")
                    seleccionar.value = ""
                    seleccionar.disabled = true
                    seleccionar.innerText = "Seleccionar"
                    contratos.appendChild(seleccionar)
                        
                    datosCliente.forEach(cliente => {
                        hue = cliente.HUELLAS
                        const opcion = document.createElement("option")
                        opcion.value = cliente.CDG_CONTRATO
                        opcion.innerText = cliente.NOMBRE
                        contratos.appendChild(opcion)
                    })
                        
                    document.querySelector("#contrato").appendChild(contratos)
                    document.querySelector("#contrato").selectedIndex = 0
                    document.querySelector("#contrato").disabled = false
                    document.querySelector("#contrato").addEventListener("change", (e) => {
                        datosCliente.forEach(contrato => {
                            if (contrato.CDG_CONTRATO == e.target.value) {
                                document.querySelector("#nombre").value = contrato.CDG_CONTRATO
                                document.querySelector("#curp").value = contrato.CURP
                                document.querySelector("#cliente").value = contrato.CDGCL
                                document.querySelector("#saldoActual").value = formatoMoneda(contrato.SALDO)
                                document.querySelector("#express").disabled = false
                                document.querySelector("#programado").disabled = false
                                document.querySelector("#monto").disabled = !(contrato.SALDO > montoMinimoRetiro)
                                if (contrato.SALDO < montoMinimoRetiro) {
                                    swal({
                                        title: "Retiro de cuenta corriente peques™",
                                        text: "El saldo actual de la cuenta del Peque es menor al monto mínimo para retiros express.\\n¿Desea realizar un retiro simple?",
                                        icon: "info",
                                        buttons: ["No", "Sí"]
                                    }).then((retSimple) => {
                                        if (retSimple) {
                                            window.location.href = "/Ahorro/CuentaPeque/?cliente=" + document.querySelector("#cliente").value + "&contrato=" + e.target.selectedIndex
                                            return
                                        }
                                    })
                                }
                                
                            }
                        })
                    })
                    
                    if (document.querySelector("#contratoSel").value !== "") {
                        document.querySelector("#contrato").selectedIndex = document.querySelector("#contratoSel").value
                        document.querySelector("#contrato").dispatchEvent(new Event("change"))
                    }
                    document.querySelector("#clienteBuscado").value = ""
                })
            }
             
            const limpiaDatosCliente = () => {
                huellas = 0
                document.querySelector("#registroOperacion").reset()
                document.querySelector("#fecha_retiro").value = getHoy()
                document.querySelector("#monto").disabled = true
                document.querySelector("#express").disabled = true
                document.querySelector("#programado").disabled = true
                document.querySelector("#contrato").innerHTML = ""
                document.querySelector("#contrato").disabled = true
                document.querySelector("#monto").disabled = true
            }
             
            const validaMonto = () => {
                document.querySelector("#express").disabled = false
                const montoIngresado = document.querySelector("#monto")
                 
                let monto = parseaNumero(montoIngresado.value) || 0
                 
                if (monto > montoMaximoExpress) {
                    document.querySelector("#programado").checked = true
                    document.querySelector("#express").disabled = true
                    cambioMovimiento()
                }
                 
                if (monto > montoMaximoRetiro) {
                    monto = montoMaximoRetiro
                    montoIngresado.value = monto
                }
                                  
                document.querySelector("#monto_letra").value = primeraMayuscula(numeroLetras(monto))
                const saldoActual = parseaNumero(document.querySelector("#saldoActual").value)
                document.querySelector("#montoOperacion").value = formatoMoneda(monto)
                const saldoFinal = (saldoActual - monto)
                compruebaSaldoFinal(saldoFinal)
                document.querySelector("#saldoFinal").value = formatoMoneda(saldoFinal)
            }
             
            const valSalMin = () => {
                const montoIngresado = document.querySelector("#monto")
                 
                let monto = parseFloat(montoIngresado.value) || 0
                 
                if (monto < montoMinimoRetiro) {
                    monto = montoMinimoRetiro
                    swal({
                        title: "Retiro de cuenta corriente",
                        text: "El monto mínimo para retiros express es de " + montoMinimoRetiro.toLocaleString("es-MX", {
                            style: "currency",
                            currency: "MXN"
                        }) + ", para un monto menor debe realizar el retiro de manera simple.\\n¿Desea realizar el retiro de manera simple?",
                        icon: "info",
                        buttons: ["No", "Sí"]
                    }).then((retSimple) => {
                        if (retSimple) {
                            window.location.href = "/Ahorro/CuentaCorriente/?cliente=" + document.querySelector("#cliente").value
                            return
                        }
                    })
                }
            }
             
            const cambioMovimiento = (e) => {
                const express = document.querySelector("#express").checked
                
                if (express) {
                    document.querySelector("#fecha_retiro").removeAttribute("style")
                    document.querySelector("#fecha_retiro_hide").setAttribute("style", "display: none;")
                    document.querySelector("#fecha_retiro").value = getHoy()
                    return
                }
                
                document.querySelector("#fecha_retiro_hide").removeAttribute("style")
                document.querySelector("#fecha_retiro").setAttribute("style", "display: none;")
                pasaFecha({ target: document.querySelector("#fecha_retiro") })
            }
             
            const compruebaSaldoFinal = () => {
                const saldoFinal = parseaNumero(document.querySelector("#saldoFinal").value)
                if (saldoFinal < 0) {
                    document.querySelector("#saldoFinal").setAttribute("style", "color: red")
                    document.querySelector("#tipSaldo").setAttribute("style", "opacity: 100%;")
                    document.querySelector("#btnRegistraOperacion").disabled = true
                    return
                } else {
                    document.querySelector("#saldoFinal").removeAttribute("style")
                    document.querySelector("#tipSaldo").setAttribute("style", "opacity: 0%;")
                }
                document.querySelector("#btnRegistraOperacion").disabled = !(saldoFinal >= 0 && parseaNumero(document.querySelector("#montoOperacion").value) >= montoMinimoRetiro && parseaNumero(document.querySelector("#montoOperacion").value) < montoMaximoRetiro)
            }
             
            const pasaFecha = (e) => {
                const fechaSeleccionada = new Date(e.target.value)
                if (fechaSeleccionada.getDay() === 5 || fechaSeleccionada.getDay() === 6) {
                    showError("No se pueden realizar retiros los fines de semana.")
                    const f = getHoy(false).split("/")
                    e.target.value = f[2] + "-" + f[1] + "-" + f[0]
                    return
                }
                const f = document.querySelector("#fecha_retiro_hide").value.split("-")
                document.querySelector("#fecha_retiro").value = f[2] + "/" + f[1] + "/" + f[0]
            }
             
            const registraSolicitud = (e) => {
                e.preventDefault()
                const datos = $("#registroOperacion").serializeArray()
                
                limpiaMontos(datos, ["saldoActual", "montoOperacion", "saldoFinal"])
                addParametro(datos, "sucursal", "{$_SESSION['cdgco_ahorro']}")
                addParametro(datos, "ejecutivo", "{$_SESSION['usuario']}")
                addParametro(datos, "retiroExpress", document.querySelector("#express").checked)
                 
                confirmarMovimiento(
                    "Confirmación de movimiento ahorro corriente",
                    "¿Está segur(a) de continuar con el registro de un retiro "
                    + (document.querySelector("#express").checked ? "express" : "programado")
                    + ", por la cantidad de "
                    + parseaNumero(document.querySelector("#montoOperacion").value).toLocaleString("es-MX", { style: "currency", currency: "MXN" })
                    + " (" + document.querySelector("#monto_letra").value + ")?"
                ).then((continuar) => {
                    if (!continuar) return
                    if (huellas > 0) return showHuella(true, datos)
                    enviaRegistroOperacion(datos)
                })
            }

            const enviaRegistroOperacion = (datos) => {
                consultaServidor("/Ahorro/RegistraSolicitud/", $.param(datos), (respuesta) => {
                    if (!respuesta.success) {
                        console.log(respuesta.error)
                        return showError(respuesta.mensaje)
                    }
                    showSuccess(respuesta.mensaje).then(() => {
                        document.querySelector("#registroOperacion").reset()
                        limpiaDatosCliente()
                    })
                })
            }
        </script>
        html;

        $fechaMax = new DateTime();
        for ($i = 0; $i < 7; $i++) {
            $fechaMax->modify('+1 day');
            if ($fechaMax->format('N') >= 6 || $fechaMax->format('N') === 0) $fechaMax->modify('+1 day');
        }

        if ($_GET['cliente']) View::set('cliente', $_GET['cliente']);
        if ($_GET['contrato']) View::set('contratoSel', $_GET['contrato']);

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Solicitud de Retiro Peque", [$this->swal2, $this->huellas])));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::set('montoMinimoRetiro', $montoMinimoRetiro);
        View::set('montoMaximoExpress', $montoMaximoExpress);
        View::set('montoMaximoRetiro', $montoMaximoRetiro);
        View::set('fecha', date('d/m/Y H:i:s'));
        View::set('fechaInput', date('Y-m-d', strtotime('+1 day')));
        View::set('fechaInputMax', $fechaMax->format('Y-m-d'));
        View::render("caja_menu_retiro_peque");
    }

    public function HistorialSolicitudRetiroCuentaPeque()
    {
        $extraFooter = <<<html
        <script>
            {$this->showError}
            {$this->showSuccess}
            {$this->showInfo}
            {$this->confirmarMovimiento}
            {$this->consultaServidor}
            {$this->configuraTabla}
            {$this->exportaExcel}
            {$this->imprimeTicket}
            {$this->muestraPDF}
            {$this->addParametro}
            {$this->valida_MCM_Complementos}
         
            $(document).ready(() => {
                configuraTabla("hstSolicitudes")
            })
             
            const validaFIF = (idI, idF) => {
                const fechaI = document.getElementById(idI).value
                const fechaF = document.getElementById(idF).value
                if (fechaI && fechaF && fechaI > fechaF) {
                    document.getElementById(idI).value = fechaF
                }
            }
            
            const imprimeExcel = () => exportaExcel("hstSolicitudes", "Historial solicitudes de retiro")
             
            const actualizaEstatus = async (estatus, id) => {
                if (!await valida_MCM_Complementos()) return
                 
                const accion = estatus === 3 ? "entrega" : "cancelación"
                 
                consultaServidor("/Ahorro/ResumenEntregaRetiro", $.param({id}), (respuesta) => {
                    if (!respuesta.success) {
                        console.log(respuesta.error)
                        return showError(respuesta.mensaje)
                    }
                     
                    const resumen = respuesta.datos
                    confirmarMovimiento(
                        "Seguimiento solicitudes de retiro",
                        null,
                        resumenRetiro(resumen, accion)
                    ).then((continuar) => {
                        if (!continuar) return
                        const datos = {
                            estatus, 
                            id, 
                            ejecutivo: "{$_SESSION['usuario']}", 
                            sucursal: "{$_SESSION['cdgco_ahorro']}", 
                            monto: resumen.MONTO, 
                            contrato: resumen.CONTRATO,
                            cliente: resumen.CLIENTE,
                            tipo: resumen.TIPO_RETIRO
                        }
                        
                        consultaServidor("/Ahorro/EntregaRetiro/", $.param(datos), (respuesta) => {
                            if (!respuesta.success) {
                                if (respuesta.error) return showError(respuesta.error)
                                return showError(respuesta.mensaje)
                            }
                             
                            showSuccess(respuesta.mensaje).then(() => {
                                if (estatus === 3) {
                                    imprimeTicket(respuesta.datos.CODIGO, "{$_SESSION['cdgco_ahorro']}")
                                    swal({ text: "Actualizando pagina...", icon: "/img/wait.gif", button: false, closeOnClickOutside: false, closeOnEsc: false })
                                    window.location.reload()
                                }
                                if (estatus === 4) devuelveRetiro(resumen)
                            })
                        })
                    })
                })
            }
             
            const resumenRetiro = (datos, accion) => {
                const resumen = document.createElement("div")
                resumen.setAttribute("style", "color: rgba(0, 0, 0, .65); text-align: left;")
                
                const tabla = document.createElement("table")
                tabla.setAttribute("style", "width: 100%;")
                tabla.innerHTML = "<thead><tr><th colspan='2' style='font-size: 25px; text-align: center;'>Retiro " + (datos.TIPO_RETIRO == 1 ? "express" : "programado") + "</th></tr></thead>"
                 
                const tbody = document.createElement("tbody")
                tbody.setAttribute("style", "width: 100%;")
                tbody.innerHTML += "<tr><td><strong>Cliente:</strong></td><td style='text-align: center;'>" + datos.NOMBRE + "</td></tr>"
                tbody.innerHTML += "<tr><td><strong>Contrato:</strong></td><td style='text-align: center;'>" + datos.CONTRATO + "</td></tr>"
                tbody.innerHTML += "<tr><td><strong>Monto:</strong></td><td style='text-align: center;'>" + parseFloat(datos.MONTO).toLocaleString("es-MX", { style: "currency", currency: "MXN" }) + "</td></tr>"
                
                const tInterno = document.createElement("table")
                tInterno.setAttribute("style", "width: 100%; margin-top: 20px;")
                const tbodyI = document.createElement("tbody")
                tbodyI.innerHTML += "<tr><td><strong>Autorizado por:</strong></td style='text-align: center;'><td>" + datos.APROBADO_POR + "</td></tr>"
                tbodyI.innerHTML += "<tr><td><strong>A " + (accion === "entrega" ? "entregar" : "cancelar") + " por:</strong></td style='text-align: center;'><td>{$_SESSION['nombre']}</td></tr>"
                tInterno.appendChild(tbodyI)
                 
                const tFechas = document.createElement("table")
                tFechas.setAttribute("style", "width: 100%; margin-top: 20px;")
                const tbodyF = document.createElement("tbody")
                tbodyF.innerHTML += "<tr><td style='text-align: center; width: 50%;'><strong>Fecha entrega solicitada</strong></td><td style='text-align: center; width: 50%;'><strong>Fecha " + (accion === "entrega" ? accion + " real" : accion) + "</strong></td></tr>"
                tbodyF.innerHTML += "<tr><td style='text-align: center; width: 50%;'>" + datos.FECHA_ESPERADA + "</td><td style='text-align: center; width: 50%;'>" + new Date().toLocaleString("es-MX", { day: "2-digit", month: "2-digit", year: "numeric"}) + "</td></tr>"
                tFechas.appendChild(tbodyF)
                 
                tabla.appendChild(tbody)
                resumen.appendChild(tabla)
                resumen.appendChild(tInterno)
                resumen.appendChild(tFechas)
                 
                const pregunta = document.createElement("label")
                pregunta.setAttribute("style", "width: 100%; font-size: 20px; text-align: center; font-weight: bold; margin-top: 20px;")
                pregunta.innerText = "¿Desea continuar con la " + accion + " del retiro?"
                 
                const advertencia = document.createElement("label")
                advertencia.setAttribute("style", "width: 100%; color: red; font-size: 15px; text-align: center;")
                advertencia.innerText = "Esta acción no se puede deshacer."
                 
                resumen.appendChild(pregunta)
                resumen.appendChild(advertencia)
                return resumen
            }
             
            const devuelveRetiro = (datos) => {
                const datosDev = {
                    contrato: datos.CONTRATO,
                    monto: datos.MONTO,
                    ejecutivo: "{$_SESSION['usuario']}",
                    sucursal: "{$_SESSION['cdgco_ahorro']}",
                    tipo: datos.TIPO_RETIRO
                }
                 
                consultaServidor("/Ahorro/DevolucionRetiro/", $.param(datosDev), (respuesta) => {
                    if (!respuesta.success) {
                        console.log(respuesta.error)
                        return showError(respuesta.mensaje)
                    }
                     
                    showSuccess(respuesta.mensaje).then(() => {
                        imprimeTicket(respuesta.datos.ticket, "{$_SESSION['cdgco_ahorro']}", false)
                        swal({ text: "Actualizando pagina...", icon: "/img/wait.gif", button: false, closeOnClickOutside: false, closeOnEsc: false })
                        window.location.reload()
                    })
                })
            }
             
            const buscar = () => {
                const datos = []
                addParametro(datos, "producto", 2)
                addParametro(datos, "fechaI", document.querySelector("#fechaI").value)
                addParametro(datos, "fechaF", document.querySelector("#fechaF").value)
                addParametro(datos, "estatus", document.querySelector("#estatus").value)
                addParametro(datos, "tipo", document.querySelector("#tipo").value)
                 
                consultaServidor("/Ahorro/HistoricoSolicitudRetiro/", $.param(datos), (respuesta) => {
                    $("#hstSolicitudes").DataTable().destroy()
                     
                    if (respuesta.datos == "") showError("No se encontraron solicitudes de retiro en el rango de fechas seleccionado.")
                     
                    $("#hstSolicitudes tbody").html(respuesta.datos)
                    configuraTabla("hstSolicitudes")
                })
            }
             
            const validaFechaEntrega = (fecha) => showError("La solicitud no está disponible para entrega, la fecha programada de entrega es el " + fecha + ".")
        </script>
        html;

        $tabla = self::HistoricoSolicitudRetiro(2);
        $tabla = $tabla['success'] ? $tabla['datos'] : "";

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Historial de solicitudes de retiro", [$this->XLSX])));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::set('tabla', $tabla);
        View::set('fecha', date('Y-m-d'));
        View::render("caja_menu_solicitud_retiro_peque_historial");
    }

    //******************REPORTE DE SALDO EN CAJA******************//
    // Muestra un reporte para el segimiento de los saldos en caja
    public function SaldosDia()
    {
        $extraFooter = <<<html
        <script>
            {$this->noSubmit}
            {$this->showError}
            {$this->showSuccess}
            {$this->showInfo}
            {$this->confirmarMovimiento}
            {$this->getHoy}
            {$this->soloNumeros}
            {$this->numeroLetras}
            {$this->primeraMayuscula}
            {$this->addParametro}
            {$this->parseaNumero}
            {$this->formatoMoneda}
            {$this->limpiaMontos}
            {$this->consultaServidor}
            {$this->configuraTabla}
            {$this->muestraPDF}
            {$this->exportaExcel}
            {$this->validaHorarioOperacion}
         
            $(document).ready(() => configuraTabla("tblArqueos"))
             
            const imprimeExcel = () => exportaExcel("tblArqueos", "Reporte de arqueos de caja al " + getHoy(false))
             
            const mostrarModal = () => {
                validaHorarioOperacion("{$_SESSION['inicio']}", "{$_SESSION['fin']}")
                document.querySelector("#frmModal").reset()
                $("#modalArqueo").modal("show")
                $("#fechaArqueo").val(getHoy())
            }
         
            const calculaTotal = (e) => {
                const id = e.target.id.replace("cant", "")
                if (!id) return
                 
                const maximo = e.target.max
                let cantidad = parseaNumero(e.target.value)
                if (cantidad > maximo) {
                    e.preventDefault()
                    e.target.value = maximo
                    cantidad = maximo
                }
                 
                const valor = parseaNumero(id.substring(0, 1) === "0" ? id.replace("0", ".", 1) : id)
                document.querySelector("#total" + id).value = formatoMoneda(cantidad * valor)
                
                const totalEfectivo = Array.from(document.querySelectorAll(".efectivo")).reduce((total, input) => total + parseaNumero(input.value), 0)
                
                document.querySelector("#totalEfectivo").value = formatoMoneda(totalEfectivo)
                document.querySelector("#btnRegistrarArqueo").disabled = !(totalEfectivo >= 1000)
            }
             
            const registraArqueo = () => {
                const totalEfectivo = parseaNumero(document.querySelector("#totalEfectivo").value)
                if (totalEfectivo < 1000) return showError("El total de efectivo debe ser mayor o igual a $1,000.00")
                
                confirmarMovimiento(
                    "Confirmación de arqueo de caja",
                    null,
                    tablaResumenArqueo(),
                ).then((continuar) => {
                    if (!continuar) return
                     
                    const datos = []
                    addParametro(datos, "sucursal", "{$_SESSION['cdgco_ahorro']}")
                    addParametro(datos, "ejecutivo", "{$_SESSION['usuario']}")
                    addParametro(datos, "monto", totalEfectivo)
                     
                    addCantidades(datos, "billete")
                    addCantidades(datos, "moneda")
                     
                    consultaServidor("/Ahorro/RegistraArqueo/", $.param(datos), (respuesta) => {
                        if (!respuesta.success) {
                            console.log(respuesta.error)
                            return showError(respuesta.mensaje)
                        }
                            
                        showSuccess(respuesta.mensaje).then(() => {
                            const host = window.location.origin
                            const titulo = 'Comprobante arqueo de caja'
                            const ruta = host + '/Ahorro/TicketArqueo/?'
                            + 'sucursal=' + "{$_SESSION['cdgco_ahorro']}"
                            
                            muestraPDF(titulo, ruta)
                             
                            swal({ text: "Actualizando pagina...", icon: "/img/wait.gif", button: false, closeOnClickOutside: false, closeOnEsc: false })
                            window.location.reload()
                        })
                    })
                })
            }
                 
            const addCantidades = (datos, tipo) => {
                const t = tipo === "billete" ? "b" : "m"
                 
                Array.from(document.querySelectorAll("." + tipo)).forEach((input) => {
                    const id = input.id.replace("cant", "")
                    if (!id) return
                    const cantidad = parseaNumero(input.value)
                    addParametro(datos, t + "_" + id, cantidad)
                })
            }
             
            const tablaResumenArqueo = () => {
                const tabla = document.createElement("table")
                tabla.setAttribute("style", "width: 100%;")
                const thead = document.createElement("thead")
                const tr0 = document.createElement("tr")
                tr0.style.height = "40px"
                 
                const th0 = document.createElement("th")
                th0.setAttribute("colspan", "3")
                th0.style.textAlign = "center"
                th0.style.fontSize = "25px"
                th0.innerText = "Resumen"
                tr0.appendChild(th0)
                thead.appendChild(tr0)
                 
                const tr1 = document.createElement("tr")
                const th1 = document.createElement("th")
                const th2 = document.createElement("th")
                const th3 = document.createElement("th")
                 
                th1.style.textAlign = "center"
                th2.style.textAlign = "center"
                th3.style.textAlign = "center"
                 
                th1.innerText = "Denominación"
                th2.innerText = "Cantidad"
                th3.innerText = "Total"
                 
                tr1.appendChild(th1)
                tr1.appendChild(th2)
                tr1.appendChild(th3)
                 
                thead.appendChild(tr1)
                tabla.appendChild(thead)
                const tbody = document.createElement("tbody")
                 
                const filasB = Array.from(document.querySelector("#tbl_billete").querySelectorAll("tr"))
                filasResumenArqueo(filasB, tbody)
                 
                const filasM = Array.from(document.querySelector("#tbl_moneda").querySelectorAll("tr"))
                filasResumenArqueo(filasM, tbody)
                tabla.appendChild(tbody)
                 
                const tf = document.createElement("tfoot")
                const trf = document.createElement("tr")
                const tdf = document.createElement("td")
                tdf.setAttribute("colspan", "2")
                tdf.style.textAlign = "right"
                tdf.style.fontSize = "20px"
                tdf.style.fontWeight = "bold"
                tdf.innerText = "Total efectivo:"
                trf.appendChild(tdf)
                const tdf2 = document.createElement("td")
                tdf2.style.textAlign = "center"
                tdf2.style.fontSize = "20px"
                tdf2.style.fontWeight = "bold"
                tdf2.innerText = parseaNumero(document.querySelector("#totalEfectivo").value).toLocaleString("es-MX", { style: "currency", currency: "MXN" })
                trf.appendChild(tdf2)
                trf.style.borderTop = "2px solid black"
                trf.style.height = "40px"
                tf.appendChild(trf)
                 
                const trf2 = document.createElement("tr")
                const tdf3 = document.createElement("td")
                tdf3.style.color = "black"
                tdf3.setAttribute("colspan", "3")
                tdf3.style.textAlign = "center"
                tdf3.innerText = "¿Está segur(a) de continuar con el registro del arqueo de caja?"
                trf2.appendChild(tdf3)
                tf.appendChild(trf2)
                tabla.appendChild(tf)
                 
                return tabla
            }
             
            const filasResumenArqueo = (filas, tbody) => {
                filas.forEach((fila) => {
                    const entradas = fila.querySelectorAll("input")
                    if (entradas[1].value === "0.00") return
                    const tr = document.createElement("tr")
                     
                    const d = document.createElement("td")
                    d.style.textAlign = "center"
                    d.innerText = fila.querySelectorAll("td")[0].innerText
                    tr.appendChild(d)
                     
                    Array.from(entradas).forEach((celda, i) => {
                        const td = document.createElement("td")
                        td.style.textAlign = "center"
                        td.innerText = i === 0 ? celda.value : parseaNumero(celda.value).toLocaleString("es-MX", { style: "currency", currency: "MXN" })
                        tr.appendChild(td)
                    })
                    tbody.appendChild(tr)
                })
            }

            const buscarArqueos = () => {
                const datos = []
                addParametro(datos, "fecha_inicio", document.querySelector("#fechaInicio").value)
                addParametro(datos, "fecha_fin", document.querySelector("#fechaFin").value)

                consultaServidor("/Ahorro/HistoricoArqueos/", $.param(datos), (respuesta) => {
                    $("#tblArqueos").DataTable().destroy()
                    if (!respuesta.success) {
                        console.log(respuesta.error)
                        return showError(respuesta.mensaje)
                    }
                    $("#tblArqueos tbody").html(respuesta.datos)
                    configuraTabla("tblArqueos")
                })
            }
        </script>
        html;

        $d = CajaAhorroDao::HistoricoArqueo(["fecha_inicio" => date('Y-m-d', strtotime('-7 day')), "fecha_fin" => date('Y-m-d'), "sucursal" => $_SESSION['cdgco_ahorro'], "ejecutivo" => $_SESSION['usuario']]);

        $d = json_decode($d, true);
        $detalles = $d['datos'];

        $tabla = "";

        foreach ($detalles as $key => $detalle) {
            $tabla .= "<tr>";
            foreach ($detalle as $key => $valor) {
                if ($key == 'MONTO') $valor = "$ " . number_format($valor, 2);
                $tabla .= "<td style='vertical-align: middle;'>$valor</td>";
            }
            $tabla .= "</tr>";
        }

        $billetes = [
            ["simbolo" => "$", "valor" => "1,000.00", "id" => "1000"],
            ["simbolo" => "$", "valor" => "500.00", "id" => "500"],
            ["simbolo" => "$", "valor" => "200.00", "id" => "200"],
            ["simbolo" => "$", "valor" => "100.00", "id" => "100"],
            ["simbolo" => "$", "valor" => "50.00", "id" => "50"],
            ["simbolo" => "$", "valor" => "20.00", "id" => "20"],
        ];

        $monedas = [
            ["simbolo" => "$", "valor" => "10.00", "id" => "10"],
            ["simbolo" => "$", "valor" => "5.00", "id" => "5"],
            ["simbolo" => "$", "valor" => "2.00", "id" => "2"],
            ["simbolo" => "$", "valor" => "1.00", "id" => "1"],
            ["simbolo" => "¢", "valor" => "0.50", "id" => "050"],
            ["simbolo" => "¢", "valor" => "0.20", "id" => "020"],
            ["simbolo" => "¢", "valor" => "0.10", "id" => "010"]
        ];

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Saldos del día", [$this->XLSX])));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::set('tabla', $tabla);
        View::set('fecha', date('d/m/Y'));
        View::set('fechaInicio', date('Y-m-d', strtotime('-7 day')));
        View::set('fechaFin', date('Y-m-d'));
        View::set('tablaBilletes', self::generaTabla($billetes, "billete"));
        View::set('tablaMonedas', self::generaTabla($monedas, "moneda"));
        View::set('nomSucursal', CajaAhorroDao::getSucursal($_SESSION['cdgco_ahorro'])['NOMBRE']);
        View::render("caja_menu_saldos_dia");
    }

    public function generaTabla($denominaciones, $tipo)
    {
        $max = $tipo === "billete" ? 5000 : 1000;
        $filas = <<<html
        <table style="width: 100%;">
        <thead>
            <tr>
                <th style="text-align: center;">Denominación</th>
                <th style="text-align: center; width: 28%;">Cantidad</th>
                <th style="text-align: center; width: 37%;">Total</th>
            </tr>
        </thead>
        <tbody id="tbl_$tipo">
        html;

        foreach ($denominaciones as $denominacion) {
            $simbolo = $denominacion["simbolo"];
            $valor = $denominacion["valor"];
            $id = $denominacion["id"];

            $filas .= "<tr>";
            $filas .= "<td style='text-align: center;'>" . $simbolo . $valor . "</td>";
            $filas .= "<td><input class='form-control " . $tipo . "' id='cant" . $id . "' name='cant" . $id . "' type='number' min='0' max='" . $max . "' value='0' oninput=calculaTotal(event) onkeydown=soloNumeros(event) /></td>";
            $filas .= "<td><input style='text-align: right;' class='form-control efectivo' id='total" . $id . "' name='total" . $id . "' value='0.00' disabled /></td>";
            $filas .= "</tr>";
        }

        $filas .= "</tbody></table>";
        return $filas;
    }

    public function HistoricoArqueos()
    {
        echo CajaAhorroDao::HistoricoArqueo($_POST);
    }

    public function RegistraArqueo()
    {
        if (self::ValidaHorario()) {
            echo CajaAhorroDao::RegistraArqueo($_POST);
            return;
        }
        echo self::FueraHorario();
    }

    public function IconoOperacion($movimiento, $operacion)
    {
        if (in_array($operacion, $this->operacionesNulas)) return '<i class="fa fa-minus" style="color: #0000ac;"></i>';
        if ($movimiento == 1) return '<i class="fa fa-arrow-down" style="color: #00ac00;"></i>';
        if ($movimiento == 0) return '<i class="fa fa-arrow-up" style="color: #ac0000;"></i>';
    }

    public function SeparaMontos($movimiento, $operacion, $monto)
    {
        if (in_array($operacion, $this->operacionesNulas)) return [0, 0];
        if ($movimiento == 0) return [0, $monto];
        if ($movimiento == 1) return [$monto, 0];
    }

    public function ExportaExcel()
    {
        $tabla = $_POST['tabla'];
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=DetallesMovimientos.xlsx");
        echo $tabla;
    }

    public function GetLogTransacciones()
    {
        $log = CajaAhorroDao::GetLogTransacciones($_POST);
        echo $log;
    }

    public function EstadoCuenta()
    {
        $fecha = date('Y-m-d');
        $fechaInicio =  date('Y-m-d', strtotime('-1 month'));

        $extraFooterAnterior = <<<script
        <script>
            const mEdoCta = true
            let datosCliente = {}
            {$this->showError}
            {$this->showSuccess}
            {$this->showInfo}
            {$this->validarYbuscar}
            {$this->buscaCliente}
            {$this->sinContrato}
            {$this->getHoy}
            {$this->soloNumeros}
            {$this->consultaServidor}
         
            const limpiaDatosCliente = () => {
                datosCliente = {}
                document.querySelector("#cliente").value = ""
                document.querySelector("#nombre").value = ""
                document.querySelector("#contrato").value = ""
                document.querySelector("#fechaInicio").value = "{$fechaInicio}"
                document.querySelector("#fechaFin").value = "{$fecha}"
                document.querySelector("#cliente").disabled = true
                document.querySelector("#nombre").disabled = true
                document.querySelector("#contrato").disabled = true
                document.querySelector("#fechaInicio").disabled = true
                document.querySelector("#fechaFin").disabled = true
                document.querySelector("#generarEdoCta").disabled = true
            }
             
            const llenaDatosCliente = (datos) => {
                if (!datos) return
                datosCliente = datos
                document.querySelector("#clienteBuscado").value = ""
                document.querySelector("#nombre").value = datos.NOMBRE
                document.querySelector("#cliente").value = datos.CDGCL
                document.querySelector("#contrato").value = datos.CONTRATO
                document.querySelector("#fechaInicio").disabled = false
                document.querySelector("#fechaFin").disabled = false
                document.querySelector("#generarEdoCta").disabled = false
            }
             
            const imprimeEdoCta = () => {
                const cliente = document.querySelector("#cliente").value
                if (!cliente) return showError("Ingrese un código de cliente.")
                mostrar(cliente)
            }
             
            const mostrar = (cliente) => {
                const host = window.location.origin
                fInicio = getFecha(document.querySelector("#fechaInicio").value)
                fFin = getFecha(document.querySelector("#fechaFin").value)
            
                let plantilla = '<!DOCTYPE html>'
                plantilla += '<html lang="es">'
                plantilla += '<head>'
                plantilla += '<meta charset="UTF-8">'
                plantilla += '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
                plantilla += '<link rel="shortcut icon" href="" + host + "/img/logo.png">'
                plantilla += '<title>Estado de Cuenta: ' + cliente + '</title>'
                plantilla += '</head>'
                plantilla += '<body style="margin: 0; padding: 0; background-color: #333333;">'
                plantilla += '<iframe src="'
                    + host + '/Ahorro/EdoCta/?'
                    + 'cliente=' + cliente
                    + '&fInicio=' + fInicio
                    + '&fFin=' + fFin
                    + '" style="width: 100%; height: 99vh; border: none; margin: 0; padding: 0;"></iframe>'
                plantilla += '</body>'
                plantilla += '</html>'
            
                const blob = new Blob([plantilla], { type: 'text/html' })
                const url = URL.createObjectURL(blob)
                window.open(url, '_blank')
            }
             
            const getFecha = (fecha) => {
                const f = new Date(fecha + 'T06:00:00Z')
                return f.toLocaleString("es-MX", { year: "numeric", month:"2-digit", day:"2-digit" })
            }
        </script>
        script;

        $extraFooter = <<<html
        <script>
            {$this->showError}
            {$this->showSuccess}
            {$this->showInfo}
            {$this->confirmarMovimiento}
            {$this->configuraTabla}
            {$this->consultaServidor}
             
            $(document).ready(function(){
                configuraTabla("muestra-cupones")
                configuraTabla("muestra-cupones1")
            })
           
            Reimprime_ticket = (folio) => {
                $('#modal_ticket').modal('show');
                document.getElementById("folio").value = folio;
            }
        
            enviar_add_sol = () =>  {             
                $('#modal_ticket').modal('hide');
                confirmarMovimiento("Resumen de movimientos", null, "¿Está segura de continuar?").then((continuar) => {
                    if (!continuar) return $('#modal_ticket').modal('show')
                    
                    consultaServidor("/Ahorro/AddSolicitudReimpresion/", $.param($('#Add').serializeArray()), (respuesta) => {
                        if (respuesta == '1') return showSuccess("Solicitud enviada a tesorería.");
                        
                        $('#modal_encuesta_cliente').modal('hide')
                        swal(respuesta, { icon: "error" })
                    },
                    "POST",
                    "Text")
                })
            }
        </script>
        html;

        $registros = CajaAhorroDao::GetMovimientosSucursal(["sucursal" => $_SESSION['cdgco_ahorro']]);
        $tabla = "";

        foreach ($registros as $key => $value) {
            $tabla .= "<tr>";
            foreach ($value as $key2 => $valor) {
                $estilo = "";
                if ($key2 === 'MONTO') $valor = "$ " . number_format($valor, 2);
                if ($key2 === 'CONCEPTO' || $key2 === 'CLIENTE') $estilo .= " text-align: left;";

                $tabla .= "<td style='vertical-align: middle;" . $estilo . "'>$valor</td>";
            }

            $tabla .= "<td style='vertical-align: middle;'><button type='button' class='btn btn-success btn-circle' onclick='Reimprime_ticket(\"{$value['CODIGO']}\");'><i class='fa fa-print'></i></button></td>";
            $tabla .= "</tr>";
        }

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Estado de Cuenta")));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::set('tabla', $tabla);
        View::set('fecha_actual', date("Y-m-d H:i:s"));
        View::render("caja_menu_resumen_movimientos");
        // View::set('fecha', $fecha);
        // View::set('fechaInicio', date('Y-m-d', strtotime('-1 month')));
        // View::render("caja_menu_estado_cuenta");
    }

    //********************UTILS********************//
    // Generación de ticket's de operaciones realizadas
    public function ValidaHorario()
    {
        if ($_SESSION['perfil'] == 'ADMIN' || $_SESSION['usuario'] == 'AMGM') return true;
        $ahora = new DateTime();
        $inicio = DateTime::createFromFormat('H:i:s', $_SESSION['inicio']);
        $fin = DateTime::createFromFormat('H:i:s', $_SESSION['fin']); // "19:00:00"); //

        return $ahora >= $inicio && $ahora <= $fin;
    }

    public function FueraHorario()
    {
        return json_encode(['success' => false, 'mensaje' => 'No es posible realizar operaciones fuera del horario establecido (' . $_SESSION['inicio'] . ' - ' . $_SESSION['fin'] . ')']);
    }

    public function Contrato()
    {
        $productos = [
            1 => 'Cuenta de Ahorro Corriente',
            2 => 'Cuenta de Inversión',
            3 => 'Cuenta de Ahorro Peque',
        ];

        if (!isset($_GET['contrato'])) exit('No se ha especificado el número de contrato');
        if (!isset($_GET['producto'])) exit('No se ha especificado el producto:<br>1 = Cuenta de Ahorro Corriente<br>2 = Cuenta de Inversión<br>3 = Cuenta de Ahorro Peque');
        if (!array_key_exists($_GET['producto'], $productos)) exit('El producto especificado no es válido');

        $noContrato = $_GET['contrato'];

        $style = <<<HTML
        <style>
            body {
                margin: 0;
                padding: 0;
            }
            .titulo {
                text-align: center;
                font-weight: bold;
                font-size: 14pt;
            }
            .sub-titulo {
                text-align: center;
                font-weight: bold;
                font-size: 12pt;
            }
            .listaLetras {
                list-style-type: lower-alpha;
            }
            .fechaTitulo {
                text-align: right;
                padding-top: 50px;
                margin-bottom: 180px;
                font-weight: normal;
            }
            li {
                font-size: 11pt;
            }
        </style>  
        HTML;

        $contrato = "";
        if ($_GET['producto'] == 1) $contrato = self::GetContratoAhorro($noContrato);
        if ($_GET['producto'] == 2) $contrato = self::GetContratoAhorro_2($noContrato);
        // if ($_GET['producto'] == 2) $contrato = self::GetContratoInversion($noContrato);
        if ($_GET['producto'] == 3) $contrato = self::GetContratoPeque($noContrato);

        $nombreArchivo = "Contrato de " . $productos[$_GET['producto']];

        $mpdf = new \mPDF([
            'mode' => 'utf-8',
            'format' => 'Letter',
            'default_font_size' => 11.5,
            'default_font' => 'Arial',
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 0,
            'margin_footer' => 5,
        ]);
        $mpdf->SetDefaultBodyCSS('text-align', 'justify');
        $fi = date('d/m/Y H:i:s');
        $pie = <<< html
        <table style="width: 100%; font-size: 10px">
            <tr>
            <td style="text-align: left; width: 50%;">
                Fecha de impresión  {$fi}
            </td>
            <td style="text-align: right; width: 50%;">
                Página {PAGENO} de {nb}
            </td>
            </tr>
        </table>
        html;
        $mpdf->SetHTMLFooter($pie);
        $mpdf->SetTitle($nombreArchivo);
        $mpdf->WriteHTML($style, 1);
        $mpdf->WriteHTML($contrato, 2);

        $mpdf->Output($nombreArchivo . '.pdf', 'I');
    }

    public function Ticket()
    {
        $ticket = $_GET['ticket'];
        $sucursal = $_GET['sucursal'] ?? "";
        $datos = CajaAhorroDao::DatosTicket($ticket);
        if (!$datos) {
            echo "No se encontró información para el ticket: " . $ticket;
            return;
        }

        $nombreArchivo = "Ticket " . $ticket;
        $mensajeImpresion = 'Fecha de impresión:<br>' . date('d/m/Y H:i:s');
        if ($sucursal) {
            $datosImpresion = CajaAhorroDao::getSucursal($sucursal);
            $mensajeImpresion = 'Fecha y sucursal de impresión:<br>' . date('d/m/Y H:i:s') . ' - ' . $datosImpresion['NOMBRE'] . ' (' . $datosImpresion['CODIGO'] . ')';
        }

        $mpdf = new \mPDF([
            'mode' => 'utf-8',
            'format' => [80, 190],
            'default_font_size' => 10,
            'default_font' => 'Arial',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 5,
        ]);
        // PIE DE PAGINA
        $mpdf->SetHTMLFooter('<div style="text-align:center;font-size:10px;font-family:Arial;">' . $mensajeImpresion . '</div>');
        $mpdf->SetTitle($nombreArchivo);
        $mpdf->SetMargins(0, 0, 5);

        $tktEjecutivo = $datos['COD_EJECUTIVO'] ? "<label>" . $datos['RECIBIO'] . ": " . $datos['NOM_EJECUTIVO'] . " (" . $datos['COD_EJECUTIVO'] . ")</label><br>" : "";
        $tktSucursal = $datos['CDG_SUCURSAL'] ? '<label>Sucursal: ' . $datos['NOMBRE_SUCURSAL'] . ' (' . $datos['CDG_SUCURSAL'] . ')</label>' : "";
        $tktMontoLetra = self::NumeroLetras($datos['MONTO']);
        $tktSaldoA = number_format($datos['SALDO_ANTERIOR'], 2, '.', ',');
        $tktMontoOP = number_format($datos['MONTO'], 2, '.', ',');
        $tktSaldoN = number_format($datos['SALDO_NUEVO'], 2, '.', ',');
        $tktComision =  $datos['COMISION'] > 0 ?  '<tr><td style="text-align: left; width: 60%;">COMISION:</td><td style="text-align: right; width: 40%;">$ ' . number_format($datos['COMISION'], 2, '.', ',') . '</td></tr>' : "";

        $detalleMovimientos = "";
        if ($datos['COMPROBANTE'] == 'DEPÓSITO' && !$tktComision) {
            $detalleMovimientos = <<<html
            <tr>
                <td style="text-align: left; width: 60%;">
                    {$datos['ES_DEPOSITO']}:
                </td>
                <td style="text-align: right; width: 40%;">
                    $ {$tktMontoOP}
                </td>
            </tr>
            html;
        } else if ($datos['TIPO_PAGO'] == '6' || $datos['TIPO_PAGO'] == '7') {
            $detalleMovimientos = <<<html
            <tr>
                <td style="text-align: left; width: 60%;">
                    {$datos['ES_DEPOSITO']}:
                </td>
                <td style="text-align: right; width: 40%;">
                    $ {$tktMontoOP}
                </td>
            </tr>
            html;
        } else {
            $detalleMovimientos = <<<html
            <tr>
                <td style="text-align: center; font-weight: bold; font-size: 12px;" colspan="2">
                    SALDOS EN CUENTA DE AHORRO
                </td>
            <tr>
                <td style="text-align: left; width: 60%;">
                    SALDO ANTERIOR:
                </td>
                <td style="text-align: right; width: 40%;">
                    $ {$tktSaldoA}
                </td>
            </tr>
            <tr>
                <td style="text-align: left; width: 60%;">
                    {$datos['ES_DEPOSITO']}:
                </td>
                <td style="text-align: right; width: 40%;">
                    $ {$tktMontoOP}
                </td>
            </tr>
            $tktComision
            <tr>
                <td style="text-align: left; width: 60%;">
                    SALDO FINAL:
                </td>
                <td style="text-align: right; width: 40%;">
                    $ {$tktSaldoN}
                </td>
            </tr>
            html;
        }

        $ticketHTML = <<<html
        <body style="font-family:Helvetica; padding: 0; margin: 0">
            <div>
                <div style="text-align:center; font-size: 20px; font-weight: bold;">
                    <label>2GKAPITAL</label>
                </div>
                <div style="text-align:center; font-size: 15px;">
                    <label>COMPROBANTE DE {$datos['COMPROBANTE']}</label>
                </div>
                <div style="text-align:center; font-size: 14px;margin-top:5px; margin-bottom: 5px">
                    ***********************************************
                </div>
                <div style="font-size: 11px;">
                    <label>Fecha de la operación: {$datos['FECHA']}</label>
                    <br>
                    <label>Método de pago: {$datos['METODO']}</label>
                    <br>
                    $tktEjecutivo
                    $tktSucursal
                </div>
                <div style="text-align:center; font-size: 10px;margin-top:5px; margin-bottom: 5px; font-weight: bold;">
                    ___________________________________________________________________
                </div>
                <div style="font-size: 11px;">
                    <label>Nombre del cliente: {$datos['NOMBRE_CLIENTE']}</label>
                    <br>
                    <label>Código de cliente: {$datos['CODIGO']}</label>
                    <br>
                    <label>Código de contrato: {$datos['CONTRATO']}</label>
                </div>
                <div style="text-align:center; font-size: 10px;margin-top:5px; margin-bottom: 5px; font-weight: bold;">
                ___________________________________________________________________
                </div>
                <div style="text-align:center; font-size: 13px; font-weight: bold;">
                    <label>{$datos['PRODUCTO']}</label>
                </div>
                <div style="text-align:center; font-size: 14px;margin-top:5px; margin-bottom: 5px">
                ***********************************************
                </div>
                <div style="text-align:center; font-size: 15px; font-weight: bold;">
                    <label>{$datos['ENTREGA']} $ {$tktMontoOP}</label>
                </div>
                <div style="text-align:center; font-size: 11px;">
                    <label>($tktMontoLetra)</label>
                </div>
                <div style="text-align:center; font-size: 14px;margin-top:5px; margin-bottom: 5px">
                ***********************************************
                </div>
                <div style="text-align:center; font-size: 13px;">
                    <table style="width: 100%; font-size: 11spx">
                        $detalleMovimientos
                    </table>
                </div>
                <div style="text-align:center; font-size: 14px;margin-top:5px; margin-bottom: 5px">
                ***********************************************
                </div>
                <div style="text-align:center; font-size: 15px; margin-top:25px; font-weight: bold;">
                    <label>Firma de conformidad del cliente</label>
                    <div style="text-align:center; font-size: 15px; margin-top:25px; margin-bottom: 5px">
                        ______________________
                    </div>
                </div>
                <div style="text-align:center; font-size: 12px; font-weight: bold;">
                    <label>FOLIO DE LA OPERACIÓN</label>
                    <barcode code="$ticket-{$datos['CODIGO']}-{$datos['MONTO']}-{$datos['COD_EJECUTIVO']}" type="C128A" size=".60" height="1" />
                </div>
            </div>
        </body>
        html;

        // Agregar contenido al PDF
        $mpdf->WriteHTML($ticketHTML);

        if ($_GET['copiaCliente']) {
            $mpdf->WriteHTML('<div style="text-align:center; font-size: 15px;"><label><b>COPIA SUCURSAL</b></label></div>');
            $mpdf->AddPage();
            $mpdf->WriteHTML($ticketHTML);
            $mpdf->WriteHTML('<div style="text-align:center; font-size: 15px;"><label><b>COPIA CLIENTE</b></label></div>');
        }

        $mpdf->Output($nombreArchivo . '.pdf', 'I');
        exit;
    }

    public function TicketArqueo()
    {
        $datos = CajaAhorroDao::DatosTicketArqueo($_GET);
        if (!$datos) {
            echo "No se encontró el número de arqueo: " . ($_GET['arqueo'] ? $_GET['arqueo'] : "No indicado") . ", para la sucursal: " . $_GET['sucursal'];
            return;
        }

        $nombreArchivo = "Ticket Arqueo " . $datos['CDG_ARQUEO'];

        $mpdf = new \mPDF([
            'mode' => 'utf-8',
            'format' => [90, 190],
            'default_font_size' => 10,
            'default_font' => 'Arial',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 5,
        ]);
        // PIE DE PAGINA
        $mpdf->SetHTMLFooter('<div style="text-align:center;font-size:10px;font-family:Arial;">Fecha de impresión:<br>' . date('d/m/Y H:i:s') . '</div>');
        $mpdf->SetTitle($nombreArchivo);
        $mpdf->SetMargins(0, 0, 5);

        $filasDetalle = "";
        $totalEfectivo = number_format($datos['MONTO'], 2, '.', ',');

        foreach ($datos as $key => $detalle) {
            if (strpos($key, "B_") === 0 || strpos($key, "M_") === 0) {
                $denominacion = str_replace("M_", "", str_replace("B_", "", $key));
                $denominacion = strpos($denominacion, "0") === 0 ? $denominacion / 100 : $denominacion;
                $monto = number_format($denominacion * $datos[$key], 2, '.', ',');
                $filasDetalle .= "<tr>";
                $filasDetalle .= "<td style='text-align: center;'>" . ($denominacion < 1 ? "¢" : "$") . number_format($denominacion, 2, '.', ',') . "</td>";
                $filasDetalle .= "<td style='text-align: center;'>" . $datos[$key] . "</td>";
                $filasDetalle .= "<td style='text-align: right;'>" . ($monto < 1 && $monto > 0 ? "¢" : "$") . $monto . "</td>";
                $filasDetalle .= "</tr>";
            }
        }

        $ticketHTML = <<<html
        <body style="font-family:Helvetica; padding: 0; margin: 0">
            <div>
                <div style="text-align:center; font-size: 20px; font-weight: bold;">
                    <label>2GKAPITAL</label>
                </div>
                <div style="text-align:center; font-size: 15px;">
                    <label>COMPROBANTE DE ARQUEO</label>
                </div>
                <div style="text-align:center; font-size: 14px; margin-top:5px; margin-bottom: 5px">
                    *****************************************
                </div>
                <div style="font-size: 11px;">
                    <label>Fecha de creación: {$datos['FECHA']}</label>
                    <br>
                    <label>Sucursal: {$datos['SUCURSAL']} ({$datos['CDG_SUCURSAL']})</label>
                    <br>
                    <label>Cajera: {$datos['USUARIO']} ({$datos['CDG_USUARIO']})</label>
                </div>
                <div style="text-align:center; font-size: 14px; margin-top:5px; margin-bottom: 5px">
                    *****************************************
                </div>
                <div style="text-align:center; font-size: 15px; font-weight: bold;">
                    <label>ARQUEO DE CAJA</label>
                </div>
                <div style="text-align:center; font-size: 10px; margin-top:5px; margin-bottom: 5px">
                    __________________________________________________________
                </div>
                <div style="text-align:center; font-size: 15px; font-weight: bold; margin-top:5px; margin-bottom: 5px">
                    <label>DETALLE</label>
                </div>
                <div style="text-align:center;">
                    <table style="width: 100%; font-size: 15px;">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 60%;">Denominación</th>
                                <th style="text-align: center; width: 40%;">Cantidad</th>
                                <th style="text-align: center; width: 40%;">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            $filasDetalle
                        </tbody>
                    </table>
                </div>
                <div style="text-align:center; font-size: 10px; margin-top:5px; margin-bottom: 5px; font-weight: bold;">
                    __________________________________________________________
                </div>
                <div style="text-align:center; font-size: 15px; margin-top:15px; font-weight: bold;">
                    <label>Total de efectivo: $ {$totalEfectivo}</label>
                </div>
                <div style="text-align:center; font-size: 14px; margin-top:5px; margin-bottom: 5px">
                    *****************************************
                </div>
                <div style="text-align:center; font-size: 15px; margin-top:25px; font-weight: bold;">
                    <label>Firma de conformidad</label>
                    <div style="text-align:center; font-size: 15px; margin-top:25px; margin-bottom: 5px">
                        ______________________
                    </div>
                </div>
            </div>
        </body>
        html;

        // Configurar copira
        if ($_GET['copia']) {
            $mpdf->WriteHTML('<div style="text-align:center; font-size: 15px;"><label><b>COPIA SUCURSAL</b></label></div>');
            $mpdf->AddPage();
            $mpdf->WriteHTML($ticketHTML);
            $mpdf->WriteHTML('<div style="text-align:center; font-size: 15px;"><label><b>COPIA CAJERA</b></label></div>');
        }

        // Agregar contenido al PDF
        $mpdf->WriteHTML($ticketHTML);
        $mpdf->Output($nombreArchivo . '.pdf', 'I');

        exit;
    }

    public function EdoCta()
    {
        if (!isset($_GET['cliente'])) {
            echo "No se especificó el cliente para generar el estado de cuenta.";
            return;
        }

        $dtsGrls = CajaAhorroDao::GetDatosEdoCta($_GET['cliente']);
        if (!$dtsGrls) {
            echo "No se encontró información para el cliente: " . $_GET['cliente'];
            return;
        }

        $fInicio = $_GET['fInicio'] ?? $dtsGrls['FECHA_APERTURA'];
        $fFin = $_GET['fFin'] ?? date('d/m/Y');
        $segmento = $_GET['segmento'] ?? 0;

        $fi = DateTime::createFromFormat('d/m/Y', $fInicio);
        $ff = DateTime::createFromFormat('d/m/Y', $fFin);
        $msjError = !($fi && $fi->format('d/m/Y') === $fInicio) ? "La fecha de inicio no es válida.<br>" : "";
        $msjError .= !($ff && $ff->format('d/m/Y') === $fFin) ? "La fecha de final no es válida.<br>" : "";
        $msjError .= ($fi > $ff) ? "La fecha de inicio no puede ser mayor a la fecha de final.<br>" : "";
        if ($msjError) {
            echo $msjError;
            return;
        }


        $estilo = <<<css
        <style>
            body {
                margin: 0;
                padding: 0;
            }
            .datosGenerales {
                margin-bottom: 20px;
            }
            .tablaTotales {
                margin: 5px 0;
            }
            .tituloTablas {
                font-size: 20px;
                font-weight: bold;
            }
            .datosCliente {
                width: 100%;
                border-collapse: collapse;
                border: 1px solid #000;
            }
            .datosCliente td {
                text-align: center;
                margin: 15px 0;
            }
            .contenedorTotales {
                margin: 10px 0;
            }
            .tablaTotales {
                width: 100%;
                border-collapse: collapse;
            }
            .contenedorDetalle {
                margin: 5px 0;
            }
            .tablaDetalle {
                border-collapse: collapse;
                width: 100%;
                margin: 0 0 20px 0;
            }
            .tablaDetalle th {
                background-color: #f2f2f2;
            }
            .tablaDetalle th, .tablaDetalle td {
                border: 1px solid #ddd;
            }
        </style>
        css;

        $cuerpo = <<<html
        <body>
            <div class="datosGenerales" style="text-align:center;">
                <h1>Estado de Cuenta</h1>
                <table class="datosCliente">
                    <tr>
                        <td colspan="6">
                            <b>Nombre del Cliente: </b>{$dtsGrls['NOMBRE']}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="width: 50%;">
                            <b>Número de Contrato: </b>{$dtsGrls['CONTRATO']}
                        </td>
                        <td colspan="3" style="width: 50%;">
                            <b>Número de Cliente: </b>{$dtsGrls['CLIENTE']}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="width: 50%;">
                            <b>Inicio del Período: </b>{$fInicio}
                        </td>
                        <td colspan="3" style="width: 50%;">
                            <b>Fin del Período: </b>{$fFin}
                        </td>
                    </tr>
                </table>
            </div>
        html;

        if ($segmento == 0 || $segmento == 1) $cuerpo .= self::TablaMovimientosAhorro($dtsGrls['CONTRATO'], $fInicio, $fFin);
        if ($segmento == 0 || $segmento == 2) $cuerpo .= self::TablaMovimientosInversion($dtsGrls['CONTRATO']);
        if ($segmento == 0 || $segmento == 3) $cuerpo .= self::TablaMovimientosPeque($_GET['cliente'], $fInicio, $fFin);

        $cuerpo .= <<<html
            <div class="notices">
                <h2>Avisos y Leyendas</h2>
                <p>[Avisos y Leyendas Legales]</p>
            </div>
        </body>
        html;

        $nombreArchivo = "Estado de Cuenta: " . $_GET['cliente'];

        $mpdf = new \mPDF([
            'mode' => 'utf-8',
            'format' => 'Letter',
            'default_font_size' => 10
        ]);
        $fi = date('d/m/Y H:i:s');
        $pie = <<< html
        <table style="width: 100%; font-size: 10px">
            <tr>
            <td style="text-align: left; width: 50%;">
                Fecha de impresión  {$fi}
            </td>
            <td style="text-align: right; width: 50%;">
                Página {PAGENO} de {nb}
            </td>
            </tr>
        </table>
        html;

        $mpdf->SetHTMLFooter($pie);
        $mpdf->SetTitle($nombreArchivo);
        $mpdf->WriteHTML($estilo, 1);
        $mpdf->WriteHTML($cuerpo, 2);

        $mpdf->Output($nombreArchivo . '.pdf', 'I');
    }

    public function TablaMovimientosAhorro($contrato, $fIni, $fFin)
    {
        $datos = CajaAhorroDao::GetMovimientosAhorro($contrato, $fIni, $fFin);
        $cargos = 0;
        $abonos = 0;
        $transito = 0;
        $filas = "<tr><td colspan='6' style='text-align: center;'>Sin movimientos en el periodo.</td></tr>";
        $salto = false;
        if ($datos || count($datos) > 0) {
            $filas = "";
            foreach ($datos as $dato) {
                $transito = number_format($dato['TRANSITO'], 2, '.', ',');
                $abono = number_format($dato['ABONO'], 2, '.', ',');
                $cargo = number_format($dato['CARGO'], 2, '.', ',');
                $saldo = number_format($dato['SALDO'], 2, '.', ',');
                $cargos += $dato['CARGO'];
                $abonos += $dato['ABONO'];

                $filas .= <<<html
                <tr>
                    <td style="text-align: center;">{$dato['FECHA']}</td>
                    <td>{$dato['DESCRIPCION']}</td>
                    <td style="text-align: right;">$ $transito</td>
                    <td style="text-align: right;">$ $abono</td>
                    <td style="text-align: right;">$ $cargo</td>
                    <td style="text-align: right;">$ $saldo</td>
                </tr>
                html;
            }
            $salto = true;
        }

        $sf = number_format($datos[count($datos) - 1]['SALDO'], 2, '.', ',');
        $c = number_format($cargos, 2, '.', ',');
        $a = number_format($abonos, 2, '.', ',');
        $tabla = <<<html
        <span class="tituloTablas">Cuenta Ahorro Corriente</span>
        <div class="contenedorTotales">
            <table class="tablaTotales">
                <thead>
                    <tr>
                        <th>Abonos</th>
                        <th>Cargos</th>
                        <th>Saldo Final</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: center; width: 33%;">
                            $ $a
                        </td>
                        <td style="text-align: center; width: 33%;">
                            $ $c
                        </td>
                        <td style="text-align: center; width: 33%;">
                            $ $sf
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="contenedorDetalle">
            <table class="tablaDetalle">
                <thead>
                    <tr>
                        <th style="width: 80px;">Fecha</th>
                        <th>Descripción</th>
                        <th style="width: 100px;">En transito</th>
                        <th style="width: 100px;">Abono</th>
                        <th style="width: 100px;">Cargo</th>
                        <th style="width: 100px;">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    $filas
                </tbody>
            </table>
        </div>
        html;

        return $tabla . ($salto ? "<div style='page-break-after: always;'></div>" : "");
    }

    public function TablaMovimientosInversion($contrato)
    {
        $datos = CajaAhorroDao::GetMovimientosInversion($contrato);
        if ($datos || count($datos) > 0) {
            $inversionTotal = 0;
            $rendimientoTotal = 0;
            $salto = false;
            // $filas = "<tr><td colspan='8' style='text-align: center;'>Sin movimientos en el periodo.</td></tr>";
            $filas = "";
            foreach ($datos as $dato) {
                $inversion = number_format($dato['MONTO'], 2, '.', ',');
                $rendimiento = number_format($dato['RENDIMIENTO'], 2, '.', ',');
                $inversionTotal += $dato['MONTO'];
                $rendimientoTotal += $dato['RENDIMIENTO'];

                $filas .= <<<html
                <tr>
                    <td style="text-align: center;">{$dato['FECHA_APERTURA']}</td>
                    <td style="text-align: center;">{$dato['FECHA_VENCIMIENTO']}</td>
                    <td style="text-align: right;">$ {$inversion}</td>
                    <td style="text-align: center;">{$dato['PLAZO']}</td>
                    <td style="text-align: center;">{$dato['TASA']} %</td>
                    <td style="text-align: center;">{$dato['ESTATUS']}</td>
                    <td style="text-align: center;">{$dato['FECHA_LIQUIDACION']}</td>
                    <td style="text-align: right;">$ {$rendimiento}</td>
                    <td style="text-align: center;">{$dato['ACCION']}</td>
                </tr>
                html;
            }
            $salto = true;

            $it = number_format($inversionTotal, 2, '.', ',');
            $rt = number_format($rendimientoTotal, 2, '.', ',');

            $tabla = <<<html
            <span class="tituloTablas">Cuenta Inversión</span>
            <div class="contenedorTotales">
                <table class="tablaTotales">
                    <thead>
                        <tr>
                            <th>Monto Total Invertido</th>
                            <th>Rendimientos Recibidos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: center; width: 50%;">
                                $ $it
                            </td>
                            <td style="text-align: center; width: 50%;">
                                $ $rt
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="contenedorDetalle">
                <table class="tablaDetalle">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Fecha Apertura</th>
                            <th style="width: 80px;">Fecha Cierre</th>
                            <th style="width: 100px;">Monto</th>
                            <th>Plazo</th>
                            <th style="width: 60px;">Tasa Anual</th>
                            <th>Estatus</th>
                            <th style="width: 100px;">Fecha Liquidación</th>
                            <th>Rendimiento</th>
                            <th>Destino</th>
                        </tr>
                    </thead>
                    <tbody>
                        $filas
                    </tbody>
                </table>
            </div>
            html;

            return $tabla . ($salto ? "<div style='page-break-after: always;'></div>" : "");
        }
    }

    public function TablaMovimientosPeque($clPadre, $fIni, $fFin)
    {
        $cuentas = CajaAhorroDao::GetCuentasPeque($clPadre);
        if ($cuentas || count($cuentas) > 0) {
            $tabla = "<span class='tituloTablas'>Cuenta Ahorro Peque</span>";
            $salto = false;
            foreach ($cuentas as $cuenta) {
                $transito = 0;
                $cargos = 0;
                $abonos = 0;
                $filas = "";
                $datos = CajaAhorroDao::GetMovimientosPeque($cuenta['CONTRATO'], $fIni, $fFin);
                if ($datos || count($datos) > 0) {
                    foreach ($datos as $dato) {
                        $transito = number_format($dato['TRANSITO'], 2, '.', ',');
                        $abono = number_format($dato['ABONO'], 2, '.', ',');
                        $cargo = number_format($dato['CARGO'], 2, '.', ',');
                        $saldo = number_format($dato['SALDO'], 2, '.', ',');
                        $cargos += $dato['CARGO'];
                        $abonos += $dato['ABONO'];

                        $filas .= <<<html
                        <tr>
                            <td style="text-align: center;">{$dato['FECHA']}</td>
                            <td>{$dato['DESCRIPCION']}</td>
                            <td style="text-align: right;">$ $transito</td>
                            <td style="text-align: right;">$ $abono</td>
                            <td style="text-align: right;">$ $cargo</td>
                            <td style="text-align: right;">$ $saldo</td>
                        </tr>
                        html;
                    }
                    $salto = true;
                }
                $filas = $filas ? $filas : "<tr><td colspan='6' style='text-align: center;'>Sin movimientos en el periodo.</td></tr>";

                $sf = number_format($datos[count($datos) - 1]['SALDO'], 2, '.', ',');
                $c = number_format($cargos, 2, '.', ',');
                $a = number_format($abonos, 2, '.', ',');
                $tabla .= <<<html
                <div class="contenedorTotales">
                    <table class="tablaTotales">
                        <tr>
                            <td colspan="2" style="text-align: center; width: 50%;">
                                <b>Nombre: </b>{$cuenta['NOMBRE']}
                            </td>
                            <td colspan="2" style="text-align: center; width: 50%;">
                                <b>No. Cuenta: </b>{$cuenta['CONTRATO']}
                            </td>
                        </tr>
                        <tr>
                            <th>Abonos</th>
                            <th>Cargos</th>
                            <th>Saldo Final</th>
                        </tr>
                        <tr>
                            <td style="text-align: center; width: 33%;">
                                $ $a
                            </td>
                            <td style="text-align: center; width: 33%;">
                                $ $c
                            </td>
                            <td style="text-align: center; width: 33%;">
                                $ $sf
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="contenedorDetalle">
                    <table class="tablaDetalle">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Fecha</th>
                                <th>Descripción</th>
                                <th style="width: 100px;">Transito</th>
                                <th style="width: 100px;">Abono</th>
                                <th style="width: 100px;">Cargo</th>
                                <th style="width: 100px;">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            $filas
                        </tbody>
                    </table>
                </div>
                html;
            }

            return $tabla . ($salto ? "<div style='page-break-after: always;'></div>" : "");
        }
    }

    public function toLetras($numero)
    {
        $cifras = array(
            0 => 'cero',
            1 => 'uno',
            2 => 'dos',
            3 => 'tres',
            4 => 'cuatro',
            5 => 'cinco',
            6 => 'seis',
            7 => 'siete',
            8 => 'ocho',
            9 => 'nueve',
            11 => 'once',
            12 => 'doce',
            13 => 'trece',
            14 => 'catorce',
            15 => 'quince',
            16 => 'dieciséis',
            17 => 'diecisiete',
            18 => 'dieciocho',
            19 => 'diecinueve',
            21 => 'veintiuno',
            22 => 'veintidós',
            23 => 'veintitrés',
            24 => 'veinticuatro',
            25 => 'veinticinco',
            26 => 'veintiséis',
            27 => 'veintisiete',
            28 => 'veintiocho',
            29 => 'veintinueve',
            10 => 'diez',
            20 => 'veinte',
            30 => 'treinta',
            40 => 'cuarenta',
            50 => 'cincuenta',
            60 => 'sesenta',
            70 => 'setenta',
            80 => 'ochenta',
            90 => 'noventa',
            100 => 'cien',
            200 => 'doscientos',
            300 => 'trescientos',
            400 => 'cuatrocientos',
            500 => 'quinientos',
            600 => 'seiscientos',
            700 => 'setecientos',
            800 => 'ochocientos',
            900 => 'novecientos'
        );

        $letra = '';

        if ($numero >= 1000000) {
            $letra .= floor($numero / 1000000) == 1 ? 'un' : $cifras[floor($numero / 1000000)];
            $numero %= 1000000;
            $letra .= (floor($numero / 1000000) > 1 ? ' millones' : ' millón') . ($numero > 0 ? ' ' : '');
            $letra .= $letra == 'un millón' ? ' de' : '';
        }

        if ($numero >= 100000) {
            $letra .= floor($numero / 100000) == 1 ? ' cien' : $cifras[floor($numero / 100000) * 100];
            $numero %= 100000;
            $letra .= $numero > 1000 ? ' ' : ' mil ';
        }

        if ($numero >= 1000) {
            $letra .= floor($numero / 1000) == 1 ? ' un' : $cifras[floor($numero / 1000)];
            $numero %= 1000;
            $letra .= ' mil' . ($numero > 0 ? ' ' : '');
        }

        if ($numero >= 100) {
            $letra .= $cifras[floor($numero / 100) * 100];
            $letra .= ($cifras[floor($numero / 100) * 100] === "cien" && $numero % 100 != 0) ? 'to' : '';
            $numero %= 100;
            $letra .= $numero > 0 ? ' ' : '';
        }

        if ($numero >= 30) {
            $letra .= $cifras[floor($numero / 10) * 10];
            $numero %= 10;
            $letra .= $numero > 0 ? ' y' : '';
        }


        if ($numero == 1) $letra .= ' un';
        else if ($numero == 21) $letra .= ' veintiún';
        else if ($numero > 0) $letra .= ' ' . $cifras[$numero];

        return trim($letra);
    }

    public function NumeroLetras($numero, $soloLetras = false)
    {
        if (!is_numeric($numero)) return "No es un número válido";
        $letra = '';
        $letra = ($numero == 0) ? 'cero' : self::toLetras(floor($numero));

        $tmp = [
            ucfirst($letra),
            (floor($numero) == 1 ? "peso" : "pesos"),
            str_pad(round(($numero - floor($numero)) * 100), 2, "0", STR_PAD_LEFT) . "/100 M.N."
        ];

        if ($soloLetras) return $tmp[0];
        return implode(" ", $tmp);
    }

    public function GetContratoAhorro($contrato)
    {
        $datos = CajaAhorroDao::DatosContratoAhorro($contrato);
        if (!$datos) exit("No se encontró información para el contrato: " . $contrato);

        $monto = "$" . number_format($datos['MONTO_APERTURA'], 2, '.', ',');
        $monto_letra = self::NumeroLetras($datos['MONTO_APERTURA']);
        $firma = "/img/firma_1.jpg";
        $gk = "<b>CAJA SOLIDARIA 2G KAPITAL</b>";
        $socio = "<b>SOCIO</b>";
        $pagina = "<b>www.cajasolidaria2gkapital.com.mx</b>";
        $domicilio = "<b>S. Rafael 6, Tecamac Centro. Tecamac, Estado de México C.P. 55740</b>";

        return <<<HTML
        <div class="contenedor">
            <p>
                CONTRATO MÚLTIPLE DE DEPÓSITO DE DINERO EN MONEDA NACIONAL QUE CELEBRAN, POR UNA PARTE, $gk, ENTIDAD COOPERATIVA DE AHORRO Y PRESTAMO POPULAR, A LA QUE EN LO SUCESIVO SE LE DENOMINARÁ COMO "$gk", Y POR LA OTRA PARTE, LA(S) PERSONA(S) CUYO(S) NOMBRE(S) SE PRECISA EN LA SOLICITUD DEL PRESENTE INSTRUMENTO, EN ADELANTE EL "$socio", A QUIENES EN SU CONJUNTO SE LES DENOMINARÁ COMO LAS "PARTES", AL TENOR DE LAS SIGUIENTES:
            </p>
            <p class="titulo">DECLARACIONES</p>
            <div calss="decalraciones">
                <ol>
                    <li>Declara el $socio que:</li>
                    <ol class="listaLetras">
                        <li>
                            Es una persona física de nacionalidad mexicana, con pleno ejercicio y goce de sus facultades para la celebración de este Contrato.
                        </li>
                        <li>
                           Sus datos generales son los que han quedado asentados en la Solicitud de Apertura de ahorro, que corresponda (la "Solicitud"), la cual forma parte integrante de este Contrato, en la que precisa su deseo de contratar una cuenta de depósito, en los términos y condiciones estipuladas en este Contrato.
                        </li>
                        <li>
                            Los Recursos depositados en la Cuenta son de su propiedad y en todo momento proceden y procederán de fuentes lícitas, manifestando que entiende plenamente las disposiciones relativas a operaciones con recursos de procedencia ilícita y sus consecuencias.
                        </li>
                        <li>
                            Conoce y acepta que $gk puede rechazar la realización de cualquier operación y/o servicio al amparo del presente Contrato en los casos en que el Solicitante y/o Socio se encuentre en la Lista de Personas Bloqueadas emitida por la Unidad de Inteligencia Financiera, o bien, en la lista "Specially Designated Nationals List (SDN)" de la "Office of Foreign Assets Control (OFAC)".
                        </li>
                        <li>
                            Conoce y acepta que $gk podrá bloquear en cualquier momento los Recursos del Socio cuando así lo solicite la Unidad de Inteligencia Financiera de la Secretaría de Hacienda y Crédito Público por encontrarse este último en la lista de Personas Bloqueadas. Actúa en nombre y por cuenta propia manifestando que tiene conocimiento que actuar en nombre y por cuenta de un tercero o proporcionar datos y documentación falsa constituye un delito.
                        </li>
                        <li>
                            Su estado civil o régimen matrimonial es el que se desprende de la Solicitud.
                        </li>
                        <li>
                            Tiene conocimiento y otorga su consentimiento a $gk para que actúe como responsable de sus datos personales y de sus datos personales patrimoniales/financieros que, de acuerdo a lo estipulado en el Aviso de Privacidad Integral para Socios Ahorro publicado en $pagina le han sido solicitados o le sean solicitados en el futuro por $gk. De igual manera manifiesta que conoce las finalidades para las que $gk recaba sus datos personales generales y personales patrimoniales/financieros.
                        </li>
                        <li>
                            Tiene conocimiento de que, en caso que sea su voluntad revocar el consentimiento que ha otorgado a $gk para el tratamiento de sus datos personales generales y personales patrimoniales/financieros, así como ejercer los derechos que la Ley Federal de Protección de Datos Personales en Posesión de los Particulares le otorga, deberá llenar debidamente el formulario que $gk pone a su disposición en las siguientes modalidades:<br>
                            a) a través de la página de internet $pagina en la sección de Privacidad.<br>
                            b) En la Oficina de Servicio y/o Sucursales de $gk más cercana a su domicilio.<br>
                            Para aclarar dudas sobre el procedimiento y requisitos para el ejercicio de los derechos y para la revocación de su consentimiento al tratamiento de sus Datos Personales, podrá llamar al siguiente número telefónico <b>(55) 5555555 extensión 55,</b> así como, ingresar al sitio de Internet $pagina en la sección de Privacidad, o bien, ponerse en contacto con la Gerencia de Privacidad de Datos, de la Información de $gk, quien dará trámite a las solicitudes para el ejercicio de estos derechos, y atenderá cualquier duda que pudiera tener respecto al tratamiento de su información. Los datos de contacto son los siguientes: Dirigido a: Oficial de Cumplimiento. Domicilio: $domicilio correo electrónico: oficialdecumplimiento@cajasolidaria2gkapital.com.mx
                        </li>
                        <li>
                            Manifiesta que $gk ha hecho de su conocimiento que sus datos personales generales y personales patrimoniales/financieros serán manejados de forma confidencial, y serán protegidos a través de medidas de seguridad tecnológicas, físicas y administrativas.
                        </li>
                        <li>
                            Declara bajo protesta de decir verdad que la información y documentación proporcionada por él es verídica y carece de toda falsedad.
                        </li>
                        <li>
                            Manifiesta que $gk ha hecho de su conocimiento que podrá consultar las disposiciones legales referidas en el presente Contrato, en el Registro de Contratos de Adhesión (RECA) así como en las Oficinas de Servicio y/o Sucursales de $gk.
                        </li>
                    </ol>
                    <li>Declara <b>CAJA DE AHORRO $gk</b>, que:</li>
                    <ol class="listaLetras">
                        <li>
                            Es una sociedad anónima debidamente constituida de acuerdo a las leyes de los Estados Unidos Mexicanos, y cuenta con las autorizaciones necesarias para operar y organizarse como Caja de ahorro, por lo que cuenta con las facultades para la celebración y cumplimiento de este Contrato.
                        </li>
                        <li>
                            Está inscrita en el Registro Federal de Contribuyentes con la clave <b>RFC_2GKAPITAL</b>, y su página de internet es $pagina Tiene su domicilio en $domicilio.
                        </li>
                        <li>
                            Contrato se encuentra debidamente inscrito en el Registro de Contratos de Adhesión de la CONDUSEF de acuerdo al Producto (término definido en la cláusula Primera siguiente) contratado, bajo los siguientes números: e.1) "mi Ahorró $gk" RECA No. _____________________.
                        </li>
                    </ol>
                    <li>Declaran las Partes que:</li>
                    <ol class="listaLetras">
                        <li>
                            Conocen el contenido del presente Contrato el cual se podrá individualizar conforme la Carátula que corresponda de cualquiera de los Productos enunciados en el siguiente:
                        </li>
                    </ol>
                </ol>
                <p>
                    Expuestas las anteriores Declaraciones, las Partes que suscriben el presente Contrato manifiestan su voluntad de otorgar y sujetarse al tenor de las siguientes:
                </p>
            </div>
            <p class="titulo">CLAUSULAS</p>
            <p class="sub-titulo">CAPÍTULO PRIMERO. DEFINICIONES</p>
            <p>
                <b>PRIMERA.- DEFINICIONES.</b> Para efectos del presente Contrato, los siguientes términos escritos con mayúscula inicial tendrán los significados que se expresan a continuación, igualmente aplicables en singular o plural:<br>
                <b>Banca Electrónica:</b> Al conjunto de servicios y operaciones bancarias que $gk realiza con el Socio a través de los Medio Electrónicos identificados como $gk Net (Banca Net), $gk SMS (Pago Móvil) y App $gk(Banca Móvil).<br>
                <b>Cajero Automático:</b> Dispositivo de acceso de autoservicio que le permite al Socio realizar diversas consultas y operaciones, tales como la disposición de dinero en efectivo y al cual el Socio accede mediante la Tarjeta de Débito.<br>
                <b>Carátula:</b> Documento mediante el cual se individualiza el Producto elegido por el Socio y precisan las características esenciales de este Contrato, el cual forma parte integral del mismo.<br>
                <b>Socio:</b> La(s) persona(s) cuyo(s) nombre(s) se precisa en la solicitud del presente instrumento.<br>
                <b>Comercios Afiliados:</b> Corresponsales bancarios y no bancarios propios o terceros de $gk., en los cuales el Socio puede realizar transacciones con la Tarjeta de Débito como instrumento de pago o Medio de Disposición del dinero depositado en la Cuenta.<br>
                <b>Comisión:</b> Cantidad establecida por $gk por los servicios y transacciones relacionados con la Cuenta y que se estipulan en el presente Contrato.<br>
                <b>SERVTEL:</b> Medio telefónico mediante el cual $gk y el Socio podrán convenir determinados Servicios.<br>
                <b>Cuenta:</b> Cuenta bancaria que $gk abrirá al Socio en términos de lo dispuesto en el presente Contrato, considerándose una Cuenta por cada Producto contratado por el Socio.<br>
                <b>Días Hábiles:</b> Días del año en que $gk abra sus Oficinas de Servicios y Sucursales para atención al público, que no sean domingos ni considerados inhábiles por las autoridades bancarias en que las instituciones de crédito estén autorizadas para celebrar operaciones con el público.<br>
                <b>Divisas:</b> dólares de los Estados Unidos de América (dólares americanos), así como cualquier otra moneda extranjera libremente transferible y convertible de inmediato a dólares americanos.<br>
                <b>Fecha de Corte:</b> Mes aniversario considerando la fecha de firma del presente Contrato.<br>
                <b>Horas Hábiles:</b> Al horario comprendido de las 08:00 a las 18:00 horas, hora centro de México en el cual $gk brinda atención en sus Oficinas de Servicio y/ Sucursales, mismo que podrá ser modificado en cualquier momento por $gk.<br>
                <b>Identificación Oficial:</b> La credencial para votar vigente con fotografía, la cédula profesional o el pasaporte mexicano, expedidos por las autoridades competentes, de acuerdo con la normatividad aplicable.<br>
                <b>Inversión:</b> Operación mediante la cual el Socio podrá ordenar a $gk invertir los Recursos o parte de estos en pagarés con rendimiento liquidable al vencimiento conforme a los montos autorizados por $gk y lo estipulado en el capítulo Cuarto del presente Contrato, dicha inversión tendrá la calidad de préstamo mercantil.<br>
                <b>Medios de Disposición:</b> Se entenderá como aquellos medios por los cuales el Socio podrá disponer de los Recursos que obran en la Cuenta, incluyendo cajeros automáticos, disposición en ventanilla, comercios afiliados, comisionistas bancarios, la Tarjeta de Débito presentada por el Socio y Banca Electrónica.<br>
                <b>Mis Apartados:</b> Funcionalidad exclusiva de la cuenta Mi Ahorro $gk que posibilita al Socio generar apartados de dinero a la vista con el fin de cumplir sus metas financieras personales, en los términos que él mismo establezca y bajo las condiciones ofertadas previamente por $gk previstas en el presente Contrato y en el "Reglamento de Ahorro, Prestamos e Inversiones", Emitido por esta institución.<br>
                <b>NIP:</b> Número de identificación personal asociado a una Tarjeta de Débito, confidencial, intransferible y que será medio de autentificación del Socio mediante una cadena de caracteres numéricos.<br>
                <b>Oficina de Servicios:</b> Lugar establecido de $gk con atención al público sin comprender operaciones bancarias de ventanilla.<br>
                <b>Pago Móvil:</b> Al servicio de Banca Electrónica en el cual el dispositivo de acceso consiste en un teléfono móvil del Socio, cuyo número de línea se encuentre asociado al servicio y mediante el cual el Socio sólo recibirá notificaciones.<br>
                <b>Recursos:</b> El importe en dinero depositado en la Cuenta, mismo que el Socio puede disponer mediante los Medios de Disposición previstos en el presente Contrato.<br>
                <b>Remesa:</b> Cantidad en moneda nacional o extranjera proveniente del exterior, transferida a través de empresas, originada por un remitente (persona física residente en el exterior que transfiere recursos económicos a sus familiares en México) para ser entregada en territorio nacional a un beneficiario (persona física residente en México que recibe los recursos que transfiere el remitente).<br>
                <b>Sucursal:</b> Aquellas instalaciones de $gk distintas a Oficinas de Servicio destinadas a la atención al público usuario, para la celebración de operaciones y prestación de servicios.<br>
                <b>Tarjeta de Débito:</b> Tarjeta de plástico con banda magnética y chip que el socio proporcione a $gk, de conformidad con lo dispuesto en el Contrato, la cual será utilizada por el Socio como un Medio de Disposición del dinero depositado en la Cuenta.<br>
                <b>Trasferencia Electrónica SPEI:</b> Servicio ofrecido por $gk en sus Oficinas de Servicios y/o Sucursales para que el Socio disponga de los Recursos de la Cuenta a través del Sistema de Pagos Electrónicos Interbancarios mediante su instrucción para el abono a otra cuenta del Socio o de terceros.<br>
            </p>
            <p class="sub-titulo">CAPÍTULO SEGUNDO. DEL CONTRATO</p>
            <p>
                <b>SEGUNDA.- OBJETO.</b> Este Contrato tiene por objeto regular los términos y condiciones conforme los cuales $gk habrá de operar la <b>Cuenta de depósito bancario de dinero a la vista</b> que el $socio contrate, cuyas características se describen más adelante (en lo sucesivo, los "Productos"). Cualquiera o todos los Productos que sean contratados y firmados por primera vez mediante el presente instrumento, será con la finalidad de poner a disposición del $socio los Recursos que se depositen en la Cuenta de cada Producto contratado. Cada producto o servicio adicional que sea contratado por el Socio deberá contar con su consentimiento expreso.
            </p>
            <p>
                El Producto que el Socio solicite a CAJA SOLIDARIA 2G KAPITAL en conformidad con el presente Contrato será el que sea señalado en la Carátula del mismo, el cual tendrá su número de Cuenta, y en caso de contratar otro producto, se le entregará su carátula con el número de cuenta correspondiente en el entendido de que CAJA SOLIDARIA 2G KAPITAL podrá a su sola discreción cambiarlo con la única obligación de hacerlo del conocimiento del Socio por cualquier medio electrónico, automatizado, impreso o a través de su personal en Sucursales u Oficinas de Servicio con 30 (treinta) días de anticipación a la fecha en que se haga efectivo el cambio de número.
            </p>
            <p>
                La celebración del presente Contrato no implica obligación de CAJA SOLIDARIA 2G KAPITAL a otorgar al Socio todos los Productos previstos en este instrumento, lo anterior en virtud de que el Socio deberá reunir y cumplir con los requisitos que al efecto CAJA SOLIDARIA 2G KAPITAL establezca para cada Producto, los cuales podrá consultar en las Sucursales, Oficinas de Servicio, página de internet de CAJA SOLIDARIA 2G KAPITAL o a través de los medios que este último establezca; sin embargo, en caso que CAJA SOLIDARIA 2G KAPITAL otorgue al Socio algún Producto indicado en el presente Contrato, se obliga a mantener operando, disponible y vigente la Cuenta del Producto otorgado y el Socio a utilizar la Cuenta y Medios de Disposición de acuerdo a lo aquí expresado.
            </p>
            <p>
            La celebración del presente Contrato no implica obligación de CAJA SOLIDARIA 2G KAPITAL a otorgar al Socio todos los Productos previstos en este instrumento, lo anterior en virtud de que el Socio deberá reunir y cumplir con los requisitos que al efecto CAJA SOLIDARIA 2G KAPITAL establezca para cada Producto, los cuales podrá consultar en las Sucursales, Oficinas de Servicio, página de internet de CAJA SOLIDARIA 2G KAPITAL o a través de los medios que este último establezca; sin embargo, en caso que CAJA SOLIDARIA 2G KAPITAL otorgue al Socio algún Producto indicado en el presente Contrato, se obliga a mantener operando, disponible y vigente la Cuenta del Producto otorgado y el Socio a utilizar la Cuenta y Medios de Disposición de acuerdo a lo aquí expresado.
            </p>
            <p class="sub-titulo">CAPÍTULO TERCERO. DE LA CUENTA EJE DE DEPÓSITO DE DINERO A LA VISTA "MI AHORRO CAJA DE AHORRO"</p>
            <p>
                <b>TERCERA. - DESCRIPCIÓN. </b>La Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL consiste en una cuenta eje de depósito bancario de dinero a la vista, en la cual el Socio podrá efectuar depósitos y retiro de dinero durante la vigencia del presente Contrato.
            </p>
            <p>
                Los depósitos realizados a la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL, serán constituidos y reembolsables en Moneda Nacional de los Estados Unidos Mexicanos en cualquier tiempo, durante la vigencia del presente Contrato, de acuerdo con los términos y condiciones aquí establecidas; así mismo, los servicios incluidos en la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL son:
                <ul>
                    <li>
                        Apertura y mantenimiento de la Cuenta de Mi Ahorro CAJA SOLIDARIA 2G KAPITAL.
                    </li>
                    <li>
                        Abonos de Recursos a la Cuenta de Mi Ahorro CAJA SOLIDARIA 2G KAPITAL.
                    </li>
                    <li>
                        Retiro de efectivo con cargo al saldo disponible de la Cuenta de Mi Ahorro CAJA SOLIDARIA 2G KAPITAL.
                    </li>
                    <li>
                        Realizar operaciones permitidas en la red de corresponsales autorizados por CAJA SOLIDARIA 2G KAPITAL para tal efecto, así como en las Sucursales y Oficinas de Servicios de CAJA SOLIDARIA 2G KAPITAL.
                    </li>
                    <li>
                        Consulta de saldos.
                    </li>
                    <li>
                        Transferencia Electrónica SPEI en Oficinas de Servicio y/o Sucursales de CAJA SOLIDARIA 2G KAPITAL a través de los medios establecidos por CAJA SOLIDARIA 2G KAPITAL que para tal efecto comunique con antelación al Socio.
                    </li>
                    <li>
                        Consulta de recepción de depósitos bancarios a través del número celular asociado a la Cuenta de ahorro CAJA SOLIDARIA 2G KAPITAL conforme lo estipulado en la cláusula Quincuagésima Segunda del presente Contrato.
                    </li>
                    <li>
                        Cierre de la Cuenta de ahorro CAJA SOLIDARIA 2G KAPITAL
                    </li>
                </ul>
            </p>
            <p>
                <b>CUARTA. - MONTO MÍNIMO POR APERTURA. </b> El Socio requiere un monto mínimo de apertura el cual será establecido por CAJA SOLIDARIA 2G KAPITAL, de acuerdo con el tipo de ahorro o inversión que elija el socio, mismo que deberá reunir y cumplir los requisitos de información y/o documentos que le sean solicitados por CAJA SOLIDARIA 2G KAPITAL, los cuales podrá consultar en las Sucursales y Oficinas de Servicios o página de internet o a través de los medios que CAJA SOLIDARIA 2G KAPITAL establezca.
            </p>
            <p>
                <b>QUINTA. - DE LA APERTURA, CIERRE Y USO DE MIS APARTADOS. </b> El Socio podrá solicitar a CAJA SOLIDARIA 2G KAPITAL a través de los canales o medios que este último ponga a su disposición, la apertura de Mis Apartados, así como la asignación de los recursos propios
                del Socio que este determine destinar para la realización de sus metas financieras personales; lo anterior se realizará únicamente
                por la indicación explícita del Socio a través de los medios que CAJA SOLIDARIA 2G KAPITAL ponga a su disposición, del monto que en cada caso y
                de forma directa ejecute a CAJA SOLIDARIA 2G KAPITAL, y que programe a través de esos mismos medios. A partir de lo anterior, el Socio instruye a CAJA SOLIDARIA 2G KAPITAL sin responsabilidad de parte de esta última, a lo siguiente:
                <ol  class="listaLetras">
                    <li>
                        Generar Mis Apartados con el fin de administrarlos, asociados a la cuenta de ahorro CAJA SOLIDARIA 2G KAPITAL y cuyos movimientos periódicos podrán observarse en el estado de cuenta del periodo que corresponda, bajo el entendido de que toda instrucción y asignación de recursos requerirá de la previa autorización del Socio a CAJA SOLIDARIA 2G KAPITAL mediante los canales o medios que este último ponga a su disposición.
                    </li>
                    <li>
                        En adición a lo convenido en la Carátula, CAJA SOLIDARIA 2G KAPITAL podrá generar rendimientos derivados de los recursos asignados a Mis
                        Apartados, en los términos ofrecidos por CAJA SOLIDARIA 2G KAPITAL en la Cláusula Sexta de este Contrato.
                    </li>
                    <li>
                        Así mismo, el Socio reconoce y acepta que la funcionalidad de Mis Apartados no constituye un producto de ahorro o inversión diferente a la cuenta Mi Ahorro MAS POR MENOS, sino únicamente un accesorio o beneficio asociado
                    </li>
                </ol>
            </p>
            <p>
            Por lo anterior, el Socio podrá disponer en cualquier momento de los recursos depositados en Mis Apartados previamente aperturados, concepción del producto de Inversión, de acuerdo a lo indicado en el “REGLAMENTO DE AHORRO, PRESTAMOS E INVERSIONES”.
            </p>
            <p>
            El Socio podrá solicitar a CAJA SOLIDARIA 2G KAPITAL en cualquier momento el cierre total de uno o varios de Mis Apartados, con la finalidad de dar por terminada la instrucción previa y restituir los recursos del Apartado a la Cuenta de ahorro CAJA SOLIDARIA 2G KAPITAL.
            </p>
            <p>
            El (los) Apartado (s) de los cuales CAJA SOLIDARIA 2G KAPITAL reciba la instrucción por parte del Socio de realizar el cierre total, serán cerrados en la misma fecha de la instrucción, cesando a partir de ese momento toda instrucción previa que se encontrara vigente al momento de la indicación; así mismo, cesará a partir de esa fecha la generación de los rendimientos que pudieron haberse obtenido hasta la próxima fecha de corte.
            </p>
            <p>
            Para efectos del estado de cuenta de la Cuenta Mi Ahorro CAJA DE AHORRO, el cierre total del o los Apartados aplicables se verá reflejado
            en el estado de cuenta del periodo inmediato siguiente que corresponda.
            </p>
            <p>
                <b>SEXTA. - RENDIMIENTOS EN APARTADOS. </b> El Socio reconoce y acepta que CAJA SOLIDARIA 2G KAPITAL no asume obligación alguna de
                garantizar rendimientos ni será responsable de generarlos, ya que estos dependen de circunstancias que son ajenas a CAJA SOLIDARIA 2G KAPITAL,
                por lo que los rendimientos que en cada caso pudieran generarse en Mis Apartados se calcularán de manera independiente tomando
                como base el saldo promedio del periodo en cada Apartado, y los rendimientos serán abonados directamente en la Cuenta Mi Ahorro
                CAJA SOLIDARIA 2G KAPITAL al corte del periodo que corresponda, , quedando el pago de estos rendimientos sujeto a la existencia de recursos en
                Mis Apartados vigentes a lo largo del periodo siendo esto último responsabilidad del Socio.
            </p>
            <p>
            En adición a lo estipulado en el párrafo anterior, el Socio podrá hacer uso de Mis Apartados que son una funcionalidad accesoria de la cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL, los cuales le permitirán apartar recursos con rendimientos equivalentes a tasas de mercado, sin que se encuentren sujetos a un plazo fijo.
            </p>
            <p>
            <b>SÉPTIMA - INTERESES.  </b>Los depósitos realizados a la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL podrán generar intereses o no, en caso de ser generados, dichos intereses serán calculados en términos anuales y tomando la tasa de interés señalada en la Carátula de este Contrato aplicable a la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL, siendo pagaderos a la fecha de mes aniversario que corresponda a la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL. El interés neto será el que resulte de multiplicar el saldo promedio diario de la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL, por la tasa de interés dividida entre 360 (trescientos sesenta), multiplicado por el número de días del mes, menos el impuesto retenido. El interés neto será capitalizable en el mes inmediato posterior.
            </p>
            <p>
                <b>OCTAVA. - SALDO PROMEDIO MENSUAL MÍNIMO. </b>CAJA SOLIDARIA 2G KAPITAL podrá determinar libremente los montos mínimos a partir de los cuales esté dispuesto a mantener operando la Cuenta de ahorro CAJA DE AHORRO. Dichos montos mínimos se calcularán por saldos
                promedios mensuales y le serán notificados al Socio al momento de la contratación, o por cualquier otro medio permitido por las
                disposiciones legales aplicables. En caso de que el Socio no mantenga el saldo mínimo mensual requerido por CAJA SOLIDARIA 2G KAPITAL durante 18 (dieciocho) meses consecutivos, se le notificará al Socio mediante comunicación que por escrito CAJA SOLIDARIA 2G KAPITAL dirija a su domicilio o a través del estado de cuenta la posibilidad de dar por cancelada la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL.
            </p>
            <p>
                <b>NOVENA. - DEPÓSITOS. </b>Los depósitos que se efectúen, en las Sucursales bancarias y corresponsales habilitados para tal efecto, en la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL se recibirán contra entrega del comprobante de depósito respectivo que al efecto se emita. Los comprobantes tendrán plena validez, una vez que ostenten la certificación de la estación receptora.
                <br>Los depósitos que se efectúen en la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL se sujetarán en todo momento a lo establecido a continuación:<br>
                <ol class="listaLetras">
                    <li>
                    Los depósitos recibidos en efectivo por causar extraordinarias, se acreditarán en el mismo día en que lo reciba MAS CONMENOS, siempre que se trate de Días y Horas Hábiles en caso contrario serán acreditados al Día Hábil siguiente.  
                    </li>
                    <li>
                    Los depósitos realizados a través de Trasferencias Electrónicas SPEI o mediante cargos y abonos a cuentas de CAJA SOLIDARIA 2G KAPITAL, se acreditará el mismo día siempre que se trate de Días y Horas Hábiles.
                    </li>
                    <li>
                    Los depósitos que se hagan dentro de los horarios establecidos por MASCON MENOS, en cheques u otros medios a cargo de
                    instituciones distintas a CAJA SOLIDARIA 2G KAPITAL, se entenderán recibidos por este último salvo buen cobro y su importe se abonará en la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL únicamente al efectuarse su cobro, conforme a los acuerdos interbancarios y reglas del Banco de México aplicables al caso.
                    </li>
                    <li>
                    Los depósitos recibidos con motivo de Prestamos que CAJA SOLIDARIA 2G KAPITAL otorgue al Socio, serán abonados en la misma fecha en que su importe quede disponible, siempre que se trate de Días y Horas Hábiles.
                    </li>
                    <li>
                    eLos depósitos a la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL podrán generar rendimientos o intereses que se señalan en la Carátula respectiva
                    </li>
                    <li>
                    Todos los depósitos de ahorros e inversiones deben se depositados a la cuenta de la caja sin excepción alguna, por lo que se entrega dinero al personal de la caja, esta no se hace responsable del registro y aplicación correspondiente.
                    </li>
                </ol>
            </p>
            <p>
                <b>DÉCIMA. - VINCULACIÓN CON PRESTAMOS OTORGADOS POR CAJA DE AHORRO.</b>Independientemente de los Medios de Disposición para los Recursos y de la libre adquisición de bienes y servicios que puede realizar el Socio por medio de su tarjeta de débito, el presente Contrato sólo podrá amparar un Producto de depósito por lo que corresponde a la cuenta "Mi Ahorro CAJA SOLIDARIA 2G KAPITAL"; sin embargo, en caso de que el Socio celebre operaciones de préstamo con CAJA SOLIDARIA 2G KAPITAL éstas podrán estar vinculadas a la Cuenta "Mi Ahorro" del Socio donde CAJA SOLIDARIA 2G KAPITAL únicamente podrá depositar los Recursos de los préstamo. Para lo estipulado en el presente párrafo, el Socio acepta y reconoce que, si dispone de los Recursos depositados en su Cuenta de ahorro" derivados de créditos otorgados, se entiende la expresa disposición de dichos prestamos, para lo cual las Partes se sujetarán a lo dispuesto por el contrato de crédito que entre ellas hayan celebrado. Si el Socio llegara a cancelar el (los) préstamos (s) otorgados por CAJA SOLIDARIA 2G KAPITAL en el plazo que sea señalado dentro de los contratos respectivos y éstos son depositados en la Cuenta, entonces el Socio no deberá disponer en ningún momento de dichos Recursos, y deberá proceder a retornar dichos recursos a la cuenta de CAJA SOLIDARIA 2G KAPITAL, en un plazo no mayor a 48 horas de haberse depositado, para que sea considerados como prestamos(s) cancelado(s) y no como prestamos activos. 
            </p>
            <p>
            Por lo anterior, el Socio se compromete a realizar la devolución del préstamo cancelado a CAJA SOLIDARIA 2G KAPITAL en los tiempos establecidos en este documento, declarando que de no ser así se compromete a realizar el pago correspondiente del prestamos en cuestión.
            </p>
            <p>
                <b>DÉCIMA PRIMERA. - ACCESO A LOS APARTADOS POR ORDEN JUDICIAL. </b>CAJA SOLIDARIA 2G KAPITAL solo podrá disponer total o parcialmente los recursos que contenga la cuenta a Mi Ahorro CAJA SOLIDARIA 2G KAPITAL, incluyendo los recursos que se encuentren en Mis Apartados sin excepción alguna, siempre y cuando sea para dar cumplimiento a una orden de autoridad judicial o fiscal competente, según sea el caso, en la cual se le ordene a CAJA SOLIDARIA 2G KAPITAL a disponer de dichos recursos.
            </p>
            <p class="sub-titulo">CAPÍTULO CUARTO. DE LAS INVERSIONES CAJA DE AHORRO</p>
            <p>
                <b>DÉCIMA SEGUNDA</b>. El Socio podrá ordenar a CAJA SOLIDARIA 2G KAPITAL invertir los Recursos o parte de estos en pagarés con rendimiento liquidable al vencimiento conforme a los montos autorizados por CAJA SOLIDARIA 2G KAPITAL y la estipulado en el presente capítulo, dicha inversión tendrá la calidad de préstamo mercantil. La Inversión se documentará con un pagaré o constancia de operación emitido por CAJA SOLIDARIA 2G KAPITAL con un rendimiento liquidable al vencimiento, misma que será siempre nominativa y no se podrá pagar anticipadamente sino hasta la conclusión del plazo pactado.
            </p>
            <p>
            La Inversión habrá de ser en Moneda Nacional y CAJA SOLIDARIA 2G KAPITAL restituirá las sumas de los Recursos invertidos más los intereses en la misma moneda en la Cuenta eje que el Socio haya designado.
            </p>
            <p>
            <b>DECIMA TERCERA - ACEPTACIÓN DE PRÉSTAMOS. </b>El Socio podrá girar instrucciones a CAJA SOLIDARIA 2G KAPITAL con el fin de que con cargo a los Recursos depositado en la Cuenta eje contratada, se invierta la cantidad que el Socio asigne a CAJA SOLIDARIA 2G KAPITAL en calidad de préstamo mercantil; dicho préstamo se documentará conforme lo estipulado en la cláusula anterior a través de pagarés con rendimiento liquidable al vencimiento.
            </p>
            <p>
            Los beneficiarios de la inversión serán los mismos que los designados por el Socio en la cláusula Cuadragésima Quinta para los Recursos de la Cuenta eje.
            </p>
            <p>
                <b>DECIMA CUARTA. - MONTOS MÍNIMOS. </b>CAJA SOLIDARIA 2G KAPITAL podrá establecer el monto mínimo que esté dispuesto a recibir para aperturar la Inversión, así como para su mantenimiento; dichos montos CAJA SOLIDARIA 2G KAPITAL los informará al Socio al momento de contratación, a través de su portal de internet, en medios impresos, o por cualquier medio que al efecto CAJA SOLIDARIA 2G KAPITAL determine y, en su caso, se especificarán en el Anexo de Comisiones.
            </p>
            <p>
                <b>DECIMA QUINTA. - DOCUMENTACIÓN. </b>Cada Inversión se documentará en un Pagaré emitido por CAJA SOLIDARIA 2G KAPITAL con rendimiento liquidable al vencimiento. Los Pagarés o constancias de operación que emita CAJA SOLIDARIA 2G KAPITAL respecto a las Inversiones serán siempre nominativos, no podrán ser pagados anticipadamente y no podrán ser transferidos excepto a Instituciones de Crédito, las que tampoco podrán recibirlos en garantía.
            </p>
            <p>
                <b>DECIMA SEXTA. - DEPÓSITO. </b>CAJA SOLIDARIA 2G KAPITAL recibirá del Socio los Pagarés en depósito para su administración al amparo del contrato de depósito bancario de títulos valor y de dinero en administración consignado en el presente Contrato múltiple. La entrega de los Pagarés en depósito se comprobará con las constancias de pagarés en administración que CAJA SOLIDARIA 2G KAPITAL expida al Socio.
            </p>
            <p>
                <b>DECIMA SEPTIMA. - PLAZO. </b>Las Partes pactarán, en cada caso, el plazo que corresponda al Pagaré en días naturales, debiendo ser no menor a un día y el mismo será forzoso para ambas partes. El plazo y la fecha de vencimiento de cada pagaré se establecerá en cada pagaré o en la constancia de operación correspondiente. Transcurridos los plazos convenidos para su devolución, CAJA SOLIDARIA 2G KAPITAL pagará al Socio el día de vencimiento, mediante abono a la Cuenta eje los Recursos objeto de la Inversión más los rendimientos generados.
            </p>
            <p>

            </p>

            <table style="width: 100%">
                <tr>
                    <td colspan="3" style="text-align: center; height: 90px">
                        <b>Ciudad de México, a {$datos['FECHA_F_LEGAL']}</b>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center; width: 45%">
                        <b>EL MUTUANTE Y/O PRESTAMISTA</b>
                    </td>
                    <td style="width: 10%"></td>
                    <td style="text-align: center; width: 45%">
                        <b>EL MUTUARIO Y/O PRESTATARIO</b>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="height: 80px"></td>
                    <td style="height: 80px; text-align: center; width: 45%">
                        <img src="{$firma}" alt="Firma" style="width: 150px; height: 100px">
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center; width: 45%; border-top: 1px solid">
                        <b>{$datos['NOMBRE']}</b>
                    </td>
                    <td style="width: 10%"></td>
                    <td style="text-align: center; width: 45%; border-top: 1px solid">
                        <b>ANTONIO LORENZO HERNÁNDEZ</b>
                    </td>
                </tr>
            </table>
        </div>
        <div style="page-break-after: always"></div>
        <div>
            <h3 class="fechaTitulo">Ciudad de México a {$datos['FECHA_F_LEGAL']}</h3>
            <p>
                El suscrito <b>{$datos['NOMBRE']}</b>, a través de la presente y bajo
                protesta de decir verdad, manifiesto que los recursos que he exhibido y que se señalan a
                detalle en el <b>CONTRATO DE MUTUO</b> de fecha {$datos['FECHA_F_LEGAL']}, celebrado en mi carácter de
                <b>“MUTUANTE Y/O PRESTAMISTA”</b> con el <b>C. ANTONIO LORENZO HERNÁNDEZ</b> en su carácter de “MUTUARIO
                Y/O PRESTATARIO” provienen de un <b>ORIGEN LÍCITO</b>, por lo que desde este momento señalo que no
                me encuentro en ninguno de los supuestos referidos en el artículo 400 Bis del Código Penal
                Federal en vigor.
            </p>
            <p>
                De la misma forma, <b>DESLINDO al “MUTUARIO Y/O PRESTATARIO”</b> de cualquier tema que pueda
                presentarse en el futuro y que sea relacionado con los recursos económicos del suscrito en
                los diversos actos jurídicos que se celebren.
            </p>
            <table style="width: 100%; padding-top: 150px">
                <tr>
                    <td style="text-align: center; width: 33%"></td>
                    <td style="text-align: center; width: 33%">
                        <b>ATENTAMENTE</b>
                    </td>
                    <td style="text-align: center; width: 33%"></td>
                </tr>
                <tr>
                    <td colspan="3" style="height: 100px"></td>
                </tr>
                <tr>
                    <td style="text-align: center; width: 25%"></td>
                    <td style="text-align: center; width: 50%; border-top: 1px solid">
                        <b>{$datos['NOMBRE']}</b>
                    </td>
                    <td style="text-align: center; width: 25%"></td>
                </tr>
            </table>
        </div>    
        HTML;
    }

    public function GetContratoAhorro_2($codigoInversion)
    {
        return <<<HTML
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><strong><span style='font-size:12px;font-family:"Verdana",sans-serif;'>CONTRATO M&Uacute;LTIPLE DE DEP&Oacute;SITO DE DINERO EN MONEDA NACIONAL QUE CELEBRAN, POR UNA PARTE, CAJA SOLIDARIA 2G KAPIATAL, ENTIDAD COOPERATIVA DE AHORRO Y PRESTAMO POPULAR, A LA QUE EN LO SUCESIVO SE LE DENOMINAR&Aacute; COMO &quot;CAJA SOLIDARIA 2G KAPITAL&quot;, Y POR LA OTRA PARTE, LA(S) PERSONA(S) CUYO(S) NOMBRE(S) SE PRECISA EN LA SOLICITUD DEL PRESENTE INSTRUMENTO, EN ADELANTE EL &quot;SOCIOS&quot;, A QUIENES EN SU CONJUNTO SE LES DENOMINAR&Aacute; COMO LAS &quot;PARTES&quot;, AL TENOR DE LAS SIGUIENTES:</span></strong></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:center;'><strong><u><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>DECLARACIONES</span></u></strong></p>
        <div style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;'>
            <ol style="margin-bottom:0cm;list-style-type: upper-roman;margin-left:44px;">
                <li style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;'><strong><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>Declara el Socio, que:</span></strong></li>
            </ol>
        </div>
        <ol style="list-style-type: lower-alpha;">
            <li><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>Es una persona f&iacute;sica de nacionalidad mexicana, con pleno ejercicio y goce de sus facultades para la celebraci&oacute;n de este Contrato.</span></li>
            <li><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>Sus datos generales son los que han quedado asentados en la Solicitud de Apertura de ahorro, que corresponda (la &quot;Solicitud&quot;), la cual forma parte integrante de este Contrato, en la que precisa su deseo de contratar una cuenta de dep&oacute;sito, en los t&eacute;rminos y condiciones estipuladas en este Contrato.</span></li>
            <li><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>Los Recursos depositados en la Cuenta son de su propiedad y en todo momento proceden y proceder&aacute;n de fuentes l&iacute;citas, manifestando que entiende plenamente las disposiciones relativas a operaciones con recursos de procedencia il&iacute;cita y sus consecuencias.</span></li>
            <li><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>Conoce y acepta que CAJA SOLIDARIA 2G CAPITAL puede rechazar la realizaci&oacute;n de cualquier operaci&oacute;n y/o servicio al amparo del presente Contrato en los casos en que el Solicitante y/o Socio se encuentre en la Lista de Personas Bloqueadas emitida por la Unidad de Inteligencia Financiera, o bien, en la lista &quot;Specially Designated Nationals List (SDN)&quot; de la &quot;Office of Foreign Assets Control (OFAC)&quot;.</span></li>
            <li><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>Conoce y acepta que CAJA SOLIDARIA 2G KAPITAL podr&aacute; bloquear en cualquier momento los Recursos del Socio cuando as&iacute; lo solicite la Unidad de Inteligencia Financiera de la Secretar&iacute;a de Hacienda y Cr&eacute;dito P&uacute;blico por encontrarse este &uacute;ltimo en la lista de Personas Bloqueadas. Act&uacute;a en nombre y por cuenta propia manifestando que tiene conocimiento que actuar en nombre y por cuenta de un tercero o proporcionar datos y documentaci&oacute;n falsa constituye un delito.</span></li>
            <li><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>Su estado civil o r&eacute;gimen matrimonial es el que se desprende de la Solicitud.</span></li>
            <li><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>Tiene conocimiento y otorga su consentimiento a CAJA SOLIDARIA 2G KAPITAL para que act&uacute;e como responsable de sus datos personales y de sus datos personales patrimoniales/financieros que, de acuerdo a lo estipulado en el Aviso de Privacidad Integral para Socios Ahorro publicado en&nbsp;</span><a href="http://www.cajasolidaria2gkapital.com.mx"><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>http://www.cajasolidaria2gkapital.com.mx</span></a><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>&nbsp;le han sido solicitados o le sean solicitados en el futuro por CAJA SOLIDARIA 2G KAPITAL. De igual manera manifiesta que conoce las finalidades para las que CAJA SOLIDARIA 2G KAPITAL recaba sus datos personales generales y personales patrimoniales/financieros.</span></li>
            <li><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>Tiene conocimiento de que, en caso que sea su voluntad revocar el consentimiento que ha otorgado a CAJA SOLIDARIA 2G KAPITAL para el tratamiento de sus datos personales generales y personales patrimoniales/financieros, as&iacute; como ejercer los derechos que la Ley Federal de Protecci&oacute;n de Datos Personales en Posesi&oacute;n de los Particulares le otorga, deber&aacute; lenar debidamente el formulario que CAJA SOLIDARIA 2G KAPITAL pone a su disposici&oacute;n en las siguientes modalidades: a) a trav&eacute;s de la p&aacute;gina de internet&nbsp;</span><a href="http://www.cajasolidaria2gkapital.com.mx"><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>http://www.cajasolidaria2gkapital.com.mx</span></a><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>&nbsp;en la secci&oacute;n de Privacidad; b) En la Oficina de Servicio y/o Sucursales de CAJA SOLIDARIA 2G KAPITAL m&aacute;s cercana a su domicilio. Para aclarar dudas sobre el procedimiento y requisitos para el ejercicio de los derechos y para la revocaci&oacute;n de su consentimiento al tratamiento de sus Datos Personales, podr&aacute; llamar al siguiente n&uacute;mero telef&oacute;nico (55) 5555555, extensi&oacute;n 55 as&iacute; como, ingresar al sitio de Internet&nbsp;</span><a href="http://www.cajasolidaria2gkapital.com.mx"><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>http://www.cajasolidaria2gkapital.com.mx</span></a><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>&nbsp;en la secci&oacute;n de Privacidad, o bien, ponerse en contacto con la Gerencia de Privacidad de Datos, de la Informaci&oacute;n de CAJA SOLIDARIA 2G KAPITAL, quien dar&aacute; tr&aacute;mite a las solicitudes para el ejercicio de estos derechos, y atender&aacute; cualquier duda que pudiera tener respecto al tratamiento de su informaci&oacute;n. Los datos de contacto son los siguientes: Dirigido a: Oficial de Cumplimiento.</span></li>
        </ol>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:72.0pt;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>Domicilio:<strong>&nbsp;S. Rafael 6, Tecamac Centro. Tecamac, Estado de M&eacute;xico C.P. 55740 c</strong>orreo electr&oacute;nico:&nbsp;</span><a href="mailto:oficialdecumplimiento@cajasolidaria2gkapital.com.mx"><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>oficialdecumplimiento@cajasolidaria2gkapital.com.mx</span></a></p>
        <ol style="list-style-type: lower-alpha;">
            <li><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>Manifiesta que CAJA SOLIDARIA 2G KAPITAL ha hecho de su conocimiento que sus datos personales generales y personales patrimoniales/financieros ser&aacute;n manejados de forma confidencial, y ser&aacute;n protegidos a trav&eacute;s de medidas de seguridad tecnol&oacute;gicas, f&iacute;sicas y administrativas.</span></li>
            <li><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>Declara bajo protesta de decir verdad que la informaci&oacute;n y documentaci&oacute;n proporcionada por &eacute;l es ver&iacute;dica y carece de toda falsedad.</span></li>
            <li><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>Manifiesta que CAJA SOLIDARIA 2G KAPITAL ha hecho de su conocimiento que podr&aacute; consultar las disposiciones legales referidas en el presente Contrato, en el Registro de Contratos de Adhesi&oacute;n (RECA) as&iacute; como en las Oficinas de Servicio y/o Sucursales de CAJA SOLIDARIA 2G KAPITAL.</span></li>
        </ol>
        <ol style="list-style-type: undefined;margin-left:44px;">
            <li><strong><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>Declara CAJA DE AHORRO CAJA SOLIDARIA 2G KAPITAL, que:</span></strong>
                <ol style="list-style-type: lower-alpha;">
                    <li><span style="font-size:9px;">Es una sociedad an&oacute;nima debidamente constituida de acuerdo a las leyes de los Estados Unidos Mexicanos, y cuenta con las autorizaciones necesarias para operar y organizarse como Caja de ahorro, por lo que cuenta con las facultades para la celebraci&oacute;n y cumplimiento de este Contrato.</span></li>
                    <li><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>Est&aacute; inscrita en el Registro Federal de Contribuyentes con la clave __________, y su p&aacute;gina de internet es&nbsp;</span><a href="http://www.cajasolidaria2Gkapital.com.mx"><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>http://www.cajasolidaria2Gkapital.com.mx</span></a></li>
                </ol>
            </li>
        </ol>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:72.0pt;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>Tiene su domicilio en calle. <strong>S. Rafael 6, Tecamac Centro. Tecamac, Estado de M&eacute;xico C.P. 55740</strong></span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:72.0pt;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>El lugar donde el Socio podr&aacute; consultar las cuentas activas de CAJA SOLIDARIA 2G KAPITAL en internet es&nbsp;</span><a href="http://www.cajasolidaria2gkapital.com.mx"><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>http://www.cajasolidaria2gkapital.com.mx</span></a></p>
        <div style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;'>
            <ol style="margin-bottom:0cm;list-style-type: lower-alpha;">
                <li style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;'><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>Contrato se encuentra debidamente inscrito en el Registro de Contratos de Adhesi&oacute;n de la CONDUSEF de acuerdo al Producto (t&eacute;rmino definido en la cl&aacute;usula Primera siguiente) contratado, bajo los siguientes n&uacute;meros:&nbsp;</span></li>
            </ol>
        </div>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:72.0pt;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-size:9px;font-family:"Verdana",sans-serif;background:yellow;'>e.1)</span><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>&nbsp;&quot;mi Ahorr&oacute; CAJA SOLIDARIA 2G KAPITAL&quot; RECA No. _________________</span></p>
        <div style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;'>
            <ol style="margin-bottom:0cm;list-style-type: undefined;margin-left:44px;">
                <li style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;'><strong><span style='line-height:107%;font-family:"Verdana",sans-serif;font-size:9px;'>Declaran las Partes que:</span></strong></li>
            </ol>
        </div>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:54.0pt;font-size:11.0pt;font-family:"Calibri",sans-serif;'><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>Conocen el contenido del presente Contrato el cual se podr&aacute; individualizar conforme la Car&aacute;tula que corresponda de cualquiera de los Productos enunciados en el siguiente:</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:54.0pt;font-size:11.0pt;font-family:"Calibri",sans-serif;'><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>&nbsp;</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style="font-size:9px;">AQU&Iacute; VA EL INDICE--------------------------------------------------------------------------------------------------------------</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style="font-size:9px;">&nbsp;</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style="font-size:9px;">Expuestas las anteriores Declaraciones, las Partes que suscriben el presente Contrato manifiestan su voluntad de otorgar y sujetarse al tenor de las siguientes:</span></p>
        <p style="margin: 0cm 0cm 8pt; font-size: 11pt; font-family: Calibri, sans-serif; text-align: center;"><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>&nbsp;</span><strong><u><span style='font-size:11px;line-height:107%;font-family:"Verdana",sans-serif;'>CL&Aacute;USULAS</span></u></strong></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:center;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>CAP&Iacute;TULO PRIMERO. DEFINICIONES</span></strong></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">PRIMERA. - DEFINICIONES.&nbsp;</span></span><span style="font-size:9px;">Para efectos del presente Contrato, los siguientes t&eacute;rminos escritos con may&uacute;scula inicial tendr&aacute;n los<br>&nbsp;significados que se expresan a continuaci&oacute;n, igualmente aplicables en singular o plural:</span></p>
        <p style='margin:0cm;text-align:left;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Banca Electr&oacute;nica:&nbsp;</span></span><span style="font-size:9px;">Al conjunto de servicios y operaciones bancarias que CAJA SOLIDARIA 2G KAPITAL realiza con el Socio a trav&eacute;s de los Medios<br>&nbsp;Electr&oacute;nicos identificados como CAJA SOLIDARIA 2G KAPITAL Net (Banca Net), CAJA SOLIDARIA 2G KAPITAL SMS (Pago M&oacute;vil) y App CAJA SOLIDARIA 2G KAPITAL(Banca M&oacute;vil).<br>&nbsp;</span><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Cajero Autom&aacute;tico:&nbsp;</span></span><span style="font-size:9px;">Dispositivo de acceso de autoservicio que le permite al Socio realizar diversas consultas y operaciones, tales como&nbsp;la disposici&oacute;n de dinero en efectivo y al cual el Socio accede mediante la Tarjeta de D&eacute;bito.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Car&aacute;tula:&nbsp;</span></span><span style="font-size:9px;">Documento mediante el cual se individualiza el Producto elegido por el Socio y precisan las caracter&iacute;sticas esenciales de<br>&nbsp;este Contrato, el cual forma parte integral del mismo.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Socio:&nbsp;</span></span><span style="font-size:9px;">La(s) persona(s) cuyo(s) nombre(s) se precisa en la solicitud del presente instrumento.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Comercios Afiliados:&nbsp;</span></span><span style="font-size:9px;">Corresponsales bancarios y no bancarios propios o terceros de CAJA SOLIDARIA 2G KAPITAL., en los cuales el Socio puede realizar transacciones con la Tarjeta de D&eacute;bito como instrumento de pago o Medio de Disposici&oacute;n del dinero depositado en la Cuenta.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Comisi&oacute;n:&nbsp;</span></span><span style="font-size:9px;">Cantidad establecida por CAJA SOLIDARIA 2G KAPITAL por los servicios y transacciones relacionados con la Cuenta y que se estipulan en el presente Contrato.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">SERVTEL:&nbsp;</span></span><span style="font-size:9px;">Medio telef&oacute;nico mediante el cual CAJA SOLIDARIA 2G KAPITAL y el Socio podr&aacute;n convenir determinados Servicios.<br>&nbsp;</span><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Cuenta:&nbsp;</span></span><span style="font-size:9px;">Cuenta bancaria que CAJA SOLIDARIA 2G KAPITAL abrir&aacute; al Socio en t&eacute;rminos de lo dispuesto en el presente Contrato, consider&aacute;ndose<br>&nbsp;una Cuenta por cada Producto contratado por el Socio.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">D&iacute;as H&aacute;biles:&nbsp;</span></span><span style="font-size:9px;">D&iacute;as del a&ntilde;o en que CAJA SOLIDARIA 2G KAPITAL abra sus Oficinas de Servicios y Sucursales para atenci&oacute;n al p&uacute;blico, que no sean<br>&nbsp;domingos ni considerados inh&aacute;biles por las autoridades bancarias en que las instituciones de cr&eacute;dito est&eacute;n autorizadas para celebrar<br>&nbsp;operaciones con el p&uacute;blico.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Divisas:&nbsp;</span></span><span style="font-size:9px;">d&oacute;lares de los Estados Unidos de Am&eacute;rica (d&oacute;lares americanos), as&iacute; como cualquier otra moneda extranjera libremente<br>&nbsp;transferible y convertible de inmediato a d&oacute;lares americanos.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Fecha de Corte:&nbsp;</span></span><span style="font-size:9px;">Mes aniversario considerando la fecha de firma del presente Contrato.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Horas H&aacute;biles:&nbsp;</span></span><span style="font-size:9px;">Al horario comprendido de las 08:00 a las 18:00 horas, hora centro de M&eacute;xico en el cual CAJA SOLIDARIA 2G KAPITAL brinda atenci&oacute;n<br>&nbsp;en sus Oficinas de Servicio y/ Sucursales, mismo que podr&aacute; ser modificado en cualquier momento por CAJA SOLIDARIA 2G KAPITAL.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Identificaci&oacute;n Oficial:&nbsp;</span></span><span style="font-size:9px;">La credencial para votar vigente con fotograf&iacute;a, la c&eacute;dula profesional o el pasaporte mexicano, expedidos por las<br>&nbsp;autoridades competentes, de acuerdo con la normatividad aplicable.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Inversi&oacute;n:&nbsp;</span></span><span style="font-size:9px;">Operaci&oacute;n mediante la cual el Socio podr&aacute; ordenar a CAJA SOLIDARIA 2G KAPITAL invertir los Recursos o parte de estos en pagar&eacute;s con rendimiento liquidable al vencimiento conforme a los montos autorizados por CAJA SOLIDARIA 2G KAPITAL y lo estipulado en el cap&iacute;tulo Cuarto del presente Contrato, dicha inversi&oacute;n tendr&aacute; la calidad de pr&eacute;stamo mercantil.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Medios de Disposici&oacute;n:&nbsp;</span></span><span style="font-size:9px;">Se entender&aacute; como aquellos medios por los cuales el Socio podr&aacute; disponer de los Recursos que obran en la<br>&nbsp;Cuenta, incluyendo cajeros autom&aacute;ticos, disposici&oacute;n en ventanilla, comercios afiliados, comisionistas bancarios, la Tarjeta de D&eacute;bito<br>&nbsp;presentada por el Socio y Banca Electr&oacute;nica.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Mis Apartados:&nbsp;</span></span><span style="font-size:9px;">Funcionalidad exclusiva de la cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL que posibilita al Socio generar apartados de dinero a la vista con el fin de cumplir sus metas financieras personales, en los t&eacute;rminos que &eacute;l mismo establezca y bajo las condiciones<br> ofertadas previamente por CAJA SOLIDARIA 2G KAPITAL previstas en el presente Contrato y en el &ldquo;<strong>Reglamento de Ahorro, Prestamos e Inversiones</strong>&rdquo;, Emitido por esta instituci&oacute;n.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">NIP:&nbsp;</span></span><span style="font-size:9px;">N&uacute;mero de identificaci&oacute;n personal asociado a una &nbsp; &nbsp;Tarjeta de D&eacute;bito, confidencial, intransferible y que ser&aacute; medio de autentificaci&oacute;n<br>&nbsp;del Socio mediante una cadena de caracteres num&eacute;ricos.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Oficina de Servicios:&nbsp;</span></span><span style="font-size:9px;">Lugar establecido de CAJA SOLIDARIA 2G KAPITAL con atenci&oacute;n al p&uacute;blico sin comprender operaciones bancarias de ventanilla.<br>&nbsp;</span><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Pago M&oacute;vil:&nbsp;</span></span><span style="font-size:9px;">Al servicio de Banca Electr&oacute;nica en el cual el dispositivo de acceso consiste en un tel&eacute;fono m&oacute;vil del Socio, cuyo n&uacute;mero<br>&nbsp;de l&iacute;nea se encuentre asociado al servicio y mediante el cual el Socio s&oacute;lo recibir&aacute; notificaciones.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Recursos:&nbsp;</span></span><span style="font-size:9px;">El importe en dinero depositado en la Cuenta, mismo que el Socio puede disponer mediante los Medios de Disposici&oacute;n<br>&nbsp;previstos en el presente Contrato.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Remesa:&nbsp;</span></span><span style="font-size:9px;">Cantidad en moneda nacional o extranjera proveniente del exterior, transferida a trav&eacute;s de empresas, originada por un<br>&nbsp;remitente (persona f&iacute;sica residente en el exterior que transfiere recursos econ&oacute;micos a sus familiares en M&eacute;xico) para ser entregada en territorio nacional a un beneficiario (persona f&iacute;sica residente en M&eacute;xico que recibe los recursos que transfiere el remitente).</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Sucursal:&nbsp;</span></span><span style="font-size:9px;">Aquellas instalaciones de CAJA SOLIDARIA 2G KAPITAL distintas a Oficinas de Servicio destinadas a la atenci&oacute;n al p&uacute;blico usuario, para la celebraci&oacute;n de operaciones y prestaci&oacute;n de servicios.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Tarjeta de D&eacute;bito:&nbsp;</span></span><span style="font-size:9px;">Tarjeta de pl&aacute;stico con banda magn&eacute;tica y chip que el socio proporcione a CAJA SOLIDARIA 2G KAPITAL, de conformidad con lo dispuesto en el Contrato, la cual ser&aacute; utilizada por el Socio como un Medio de Disposici&oacute;n del dinero depositado en la Cuenta.<br>&nbsp;</span><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">Trasferencia Electr&oacute;nica SPEI:&nbsp;</span></span><span style="font-size:9px;">Servicio ofrecido por CAJA SOLIDARIA 2G KAPITAL en sus Oficinas de Servicios y/o Sucursales para que el Socio disponga de los Recursos de la Cuenta a trav&eacute;s del Sistema de Pagos Electr&oacute;nicos Interbancarios mediante su instrucci&oacute;n para el abono a otra cuenta del Socio o de terceros.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:center;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>CAP&Iacute;TULO SEGUNDO. DEL CONTRATO</span></strong></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">SEGUNDA. - OBJETO.&nbsp;</span></span><span style="font-size:9px;">Este Contrato tiene por objeto regular los t&eacute;rminos y condiciones conforme los cuales CAJA SOLIDARIA 2G KAPITAL habr&aacute; de operar la Cuenta de dep&oacute;sito bancario de dinero a la vista que el Socio contrate, cuyas caracter&iacute;sticas se describen m&aacute;s adelante (en lo sucesivo, los &quot;Productos&quot;). Cualquiera o todos los Productos que sean contratados y firmados por primera vez mediante el presente instrumento, ser&aacute; con la finalidad de poner a disposici&oacute;n del Socio los Recursos que se depositen en la Cuenta de cada Producto contratado. Cada producto o servicio adicional que sea contratado por el Socio deber&aacute; contar con su consentimiento expreso.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style="font-size:9px;">El Producto que el Socio solicite a CAJA SOLIDARIA 2G KAPITAL en conformidad con el presente Contrato ser&aacute; el que sea se&ntilde;alado en la Car&aacute;tula del mismo, el cual tendr&aacute; su n&uacute;mero de Cuenta, y en caso de contratar otro producto, se le entregar&aacute; su car&aacute;tula con el n&uacute;mero de cuenta correspondiente en el entendido de que CAJA SOLIDARIA 2G KAPITAL podr&aacute; a su sola discreci&oacute;n cambiarlo&nbsp;con&nbsp;la &uacute;nica obligaci&oacute;n de hacerlo del conocimiento del Socio por cualquier medio electr&oacute;nico, automatizado, impreso o a trav&eacute;s de su personal en Sucursales u Oficinas de Servicio con 30 (treinta) d&iacute;as de anticipaci&oacute;n a la fecha en que se haga efectivo el cambio de n&uacute;mero.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style="font-size:9px;">La celebraci&oacute;n del presente Contrato no implica obligaci&oacute;n de CAJA SOLIDARIA 2G KAPITAL a otorgar al Socio todos los Productos previstos en este instrumento, lo anterior en virtud de que el Socio deber&aacute; reunir y cumplir con los requisitos que al efecto CAJA SOLIDARIA 2G KAPITAL establezca para cada Producto, los cuales podr&aacute; consultar en las Sucursales, Oficinas de Servicio, p&aacute;gina de internet de CAJA SOLIDARIA 2G KAPITAL o a trav&eacute;s de los medios&nbsp;que&nbsp;este &uacute;ltimo establezca;&nbsp;sin&nbsp;embargo, en caso&nbsp;que&nbsp;CAJA SOLIDARIA 2G KAPITAL otorgue al Socio alg&uacute;n Producto indicado en el&nbsp;presente&nbsp;Contrato, se obliga a mantener operando, disponible y vigente la Cuenta del Producto otorgado y el Socio a utilizar la Cuenta y Medios de Disposici&oacute;n de acuerdo a lo aqu&iacute; expresado.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:center;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>CAP&Iacute;TULO TERCERO. DE LA CUENTA EJE DE DEP&Oacute;SITO DE DINERO A LA VISTA &quot;MI AHORRO CAJA DE AHORRO&quot;</span></strong></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:0cm;margin-left:0cm;text-align:justify;line-height:9.1pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;font-weight:bold;'><span style="font-size:9px;">&nbsp;</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.85pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.2pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">TERCERA. - DESCRIPCI&Oacute;N.&nbsp;</span></span><span style="font-size:9px;">La Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL consiste en una cuenta eje de dep&oacute;sito bancario de dinero a la vista, en la cual el Socio podr&aacute; efectuar dep&oacute;sitos y retiro de dinero durante la vigencia del presente Contrato.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:9.8pt;'><span style="font-size:9px;">Los dep&oacute;sitos realizados a la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL, ser&aacute;n constituidos y reembolsables en Moneda Nacional de los Estados Unidos Mexicanos en cualquier tiempo,&nbsp;durante&nbsp;la vigencia del presente Contrato, de acuerdo con los t&eacute;rminos y condiciones aqu&iacute; establecidas; as&iacute; mismo, los servicios incluidos en la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL son:</span></p>
        <ul style="list-style-type: square;margin-left:26px;">
            <li><span style="font-size:9px;">Apertura y mantenimiento de la Cuenta de Mi Ahorro CAJA SOLIDARIA 2G KAPITAL.</span></li>
            <li><span style="font-size:9px;">Abonos de Recursos a la Cuenta de Mi Ahorro CAJA SOLIDARIA 2G KAPITAL.</span></li>
            <li><span style="font-size:9px;">Retiro de efectivo con cargo al saldo disponible de la Cuenta de Mi Ahorro CAJA SOLIDARIA 2G KAPITAL.</span></li>
            <li><span style="font-size:9px;">Realizar operaciones permitidas en la red de corresponsales autorizados por CAJA SOLIDARIA 2G KAPITAL para tal efecto, as&iacute; como en las Sucursales y Oficinas de Servicios de CAJA SOLIDARIA 2G KAPITAL.</span></li>
        </ul>
        <ul style="list-style-type: square;">
            <li><span style='font-family:"Verdana",sans-serif;font-size:9px;'>Consulta de saldos.</span></li>
            <li><span style='font-family:"Verdana",sans-serif;font-size:9px;'>Transferencia Electr&oacute;nica SPEI en Oficinas de Servicio y/o Sucursales de CAJA SOLIDARIA 2G KAPITAL a trav&eacute;s de los medios establecidos por CAJA SOLIDARIA 2G KAPITAL que para tal efecto comunique con antelaci&oacute;n al Socio.</span></li>
            <li><span style='font-family:"Verdana",sans-serif;font-size:9px;'>Consulta de recepci&oacute;n de dep&oacute;sitos bancarios a trav&eacute;s del n&uacute;mero celular asociado a la Cuenta de ahorro CAJA SOLIDARIA 2G KAPITAL<br>&nbsp;conforme lo estipulado en la cl&aacute;usula Quincuag&eacute;sima Segunda del presente Contrato.</span></li>
            <li><span style='font-family:"Verdana",sans-serif;font-size:9px;'>Cierre de la Cuenta de ahorro CAJA SOLIDARIA 2G KAPITAL<span style="color:yellow;">.</span></span></li>
        </ul>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>CUARTA. -</span></strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>&nbsp;<strong>MONTO M&Iacute;NIMO POR APERTURA</strong>. El Socio requiere un monto m&iacute;nimo de apertura el cual ser&aacute; establecido por CAJA SOLIDARIA 2G KAPITAL, de acuerdo con el tipo de ahorro o inversi&oacute;n que elija el socio, mismo que deber&aacute; reunir y cumplir los requisitos de informaci&oacute;n y/o documentos que le sean solicitados por CAJA SOLIDARIA 2G KAPITAL, los cuales podr&aacute; consultar en las Sucursales y Oficinas de Servicios o p&aacute;gina de internet o a trav&eacute;s de los medios que CAJA SOLIDARIA 2G KAPITAL establezca.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>QUINTA. - DE LA APERTURA, CIERRE Y USO DE MIS APARTADOS.</span></strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>&nbsp;El Socio podr&aacute; solicitar a CAJA SOLIDARIA 2G KAPITAL a trav&eacute;s de los<br>&nbsp;canales o medios que este &uacute;ltimo ponga a su disposici&oacute;n, la apertura de Mis Apartados, as&iacute; como la asignaci&oacute;n de los recursos propios<br>&nbsp;del Socio que este determine destinar para la realizaci&oacute;n de sus metas financieras personales; lo anterior se realizar&aacute; &uacute;nicamente<br>&nbsp;por la indicaci&oacute;n expl&iacute;cita del Socio a trav&eacute;s de los medios que CAJA SOLIDARIA 2G KAPITAL ponga a su disposici&oacute;n, del monto que en cada caso y<br>&nbsp;de forma directa ejecute a CAJA SOLIDARIA 2G KAPITAL, y que programe a trav&eacute;s de esos mismos medios. A partir de lo anterior, el Socio instruye<br>&nbsp;a CAJA SOLIDARIA 2G KAPITAL sin responsabilidad de parte de esta &uacute;ltima, a lo siguiente:</span></p>
        <div style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;'>
            <ol start="1" style="margin-bottom:0cm;list-style-type: lower-alpha;margin-left:0cmundefined;">
                <li style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;'><span style='font-family:"Verdana",sans-serif;font-size:9px;'>Generar Mis Apartados con el fin de administrarlos, asociados a la cuenta de ahorro CAJA SOLIDARIA 2G KAPITAL y cuyos movimientos peri&oacute;dicos podr&aacute;n observarse en el estado de cuenta del periodo que corresponda, bajo el entendido de que toda instrucci&oacute;n y asignaci&oacute;n de recursos requerir&aacute; de la previa autorizaci&oacute;n del Socio a CAJA SOLIDARIA 2G KAPITAL mediante los canales o medios que este &uacute;ltimo ponga a su disposici&oacute;n.</span></li>
            </ol>
        </div>
        <ol start="2" style="list-style-type: lower-alpha;margin-left:0cmundefined;">
            <li><span style='font-family:"Verdana",sans-serif;font-size:9px;'>En adici&oacute;n a lo convenido en la Car&aacute;tula, CAJA SOLIDARIA 2G KAPITAL podr&aacute; generar rendimientos derivados de los recursos asignados a Mis<br>&nbsp;Apartados, en los t&eacute;rminos ofrecidos por CAJA SOLIDARIA 2G KAPITAL en la Cl&aacute;usula Sexta de este Contrato.</span></li>
            <li><span style='font-family:"Verdana",sans-serif;font-size:9px;'>As&iacute; mismo, el Socio reconoce y acepta que la funcionalidad de Mis Apartados no constituye un producto de ahorro o inversi&oacute;n diferente a la cuenta Mi Ahorro MAS POR MENOS, sino &uacute;nicamente un accesorio o beneficio asociado.</span></li>
        </ol>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:9.6pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;line-height:9.1pt;'><span style='font-size:9px;font-family:"Verdana",sans-serif;'>Por lo anterior, el Socio podr&aacute; disponer en cualquier momento de los recursos depositados en Mis Apartados previamente aperturados, concepci&oacute;n del producto de Inversi&oacute;n, de acuerdo a lo indicado en el &ldquo;<strong>REGLAMENTO DE AHORRO, PRESTAMOS E INVERSIONES&rdquo;</strong>.&nbsp;</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>El Socio podr&aacute; solicitar a CAJA SOLIDARIA 2G KAPITAL en cualquier momento el cierre total de uno o varios de Mis Apartados, con la finalidad de dar por terminada la instrucci&oacute;n previa y restituir los recursos del Apartado a la Cuenta de ahorro CAJA SOLIDARIA 2G KAPITAL.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>El (los) Apartado (s) de los cuales CAJA SOLIDARIA 2G KAPITAL reciba la instrucci&oacute;n por parte del Socio de realizar el cierre total, ser&aacute;n cerrados en la misma fecha de la instrucci&oacute;n, cesando a partir de ese momento toda instrucci&oacute;n previa que se encontrara vigente al momento de la indicaci&oacute;n; as&iacute; mismo, cesar&aacute; a partir de esa fecha la generaci&oacute;n de los rendimientos que pudieron haberse obtenido hasta la pr&oacute;xima fecha de corte.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>Para efectos del estado de cuenta de la Cuenta Mi Ahorro CAJA DE AHORRO, el cierre total del o los Apartados aplicables se ver&aacute; reflejado<br>&nbsp;en el estado de cuenta del periodo inmediato siguiente que corresponda.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:9.8pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>SEXTA. - RENDIMIENTOS EN APARTADOS.</span></strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>&nbsp;El Socio reconoce y acepta que CAJA SOLIDARIA 2G KAPITAL no asume obligaci&oacute;n alguna de<br> garantizar rendimientos ni ser&aacute; responsable de generarlos, ya que estos dependen de circunstancias que son ajenas a CAJA SOLIDARIA 2G KAPITAL<span style="color:yellow;">,</span><br>&nbsp;por lo que los rendimientos que en cada caso pudieran generarse en Mis Apartados se calcular&aacute;n de manera independiente tomando<br>&nbsp;como base el saldo promedio del periodo en cada Apartado, y los rendimientos ser&aacute;n abonados directamente en la Cuenta Mi Ahorro<br>&nbsp;CAJA SOLIDARIA 2G KAPITAL al corte del periodo que corresponda, , quedando el pago de estos rendimientos sujeto a la existencia de recursos en<br>&nbsp;Mis Apartados vigentes a lo largo del periodo siendo esto &uacute;ltimo responsabilidad del Socio.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.2pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;line-height:9.85pt;'><span style='font-size:9px;font-family:"Verdana",sans-serif;'>En adici&oacute;n a lo estipulado en el p&aacute;rrafo anterior, el Socio podr&aacute; hacer uso de Mis Apartados que son una funcionalidad accesoria de la cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL, los cuales le permitir&aacute;n apartar recursos con rendimientos equivalentes a tasas de mercado, sin que se encuentren sujetos a un plazo fijo.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.4pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>S&Eacute;PTIMA - INTERESES.</span></strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>&nbsp;Los dep&oacute;sitos realizados a la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL podr&aacute;n generar intereses o no, en caso de ser generados, dichos intereses ser&aacute;n calculados en t&eacute;rminos anuales y tomando la tasa de inter&eacute;s se&ntilde;alada en la Car&aacute;tula de este Contrato aplicable a la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL, siendo pagaderos a la fecha de mes aniversario que corresponda a la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL. El inter&eacute;s neto ser&aacute; el que resulte de multiplicar el saldo promedio diario de la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL, por la tasa de inter&eacute;s dividida entre 360 (trescientos sesenta), multiplicado por el n&uacute;mero de d&iacute;as del mes, menos el impuesto retenido. El inter&eacute;s neto ser&aacute;&nbsp;</span><span style='font-size:9px;font-family:"Verdana",sans-serif;'>capitalizable&nbsp;</span><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>en el mes inmediato posterior.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;line-height:9.1pt;'><strong><span style='font-size:9px;font-family:"Verdana",sans-serif;'>OCTAVA. - SALDO PROMEDIO MENSUAL M&Iacute;NIMO</span></strong><span style='font-size:9px;font-family:"Verdana",sans-serif;'>. CAJA SOLIDARIA 2G KAPITAL podr&aacute; determinar libremente los montos m&iacute;nimos a partir de los cuales est&eacute; dispuesto a mantener operando la Cuenta de ahorro CAJA DE AHORRO. Dichos montos m&iacute;nimos se calcular&aacute;n por saldos<br>&nbsp;promedios mensuales y le ser&aacute;n notificados al Socio al momento de la contrataci&oacute;n, o por cualquier otro medio permitido por las<br>&nbsp;disposiciones legales aplicables. En caso de que el Socio no mantenga el saldo m&iacute;nimo mensual requerido por CAJA SOLIDARIA 2G KAPITAL durante 18 (dieciocho) meses consecutivos, se le notificar&aacute; al Socio mediante comunicaci&oacute;n que por escrito CAJA SOLIDARIA 2G KAPITAL dirija a su domicilio o a trav&eacute;s del estado de cuenta la posibilidad de dar por cancelada la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.85pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.6pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">NOVENA.&nbsp;</span></span><span style="font-size:9px;">-&nbsp;</span><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">DEP&Oacute;SITOS.&nbsp;</span></span><span style="font-size:9px;">Los dep&oacute;sitos que se efect&uacute;en, en las Sucursales bancarias y corresponsales habilitados para tal efecto, en la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL se recibir&aacute;n contra entrega del comprobante de dep&oacute;sito respectivo que al efecto se emita. Los comprobantes tendr&aacute;n plena validez, una vez que ostenten la certificaci&oacute;n de la estaci&oacute;n receptora.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.1pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-top:0cm;margin-right:0cm;margin-bottom:9.4pt;margin-left:36.0pt;'><span style="font-size:9px;">Los&nbsp;dep&oacute;sitos que se efect&uacute;en en la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL se sujetar&aacute;n en todo momento a lo establecido a continuaci&oacute;n:</span></p>
        <ol start="1" style="list-style-type: lower-alpha;margin-left:26px;">
            <li><span style="font-size:9px;">Los dep&oacute;sitos recibidos en efectivo por causar extraordinarias, se acreditar&aacute;n en el mismo d&iacute;a en que lo reciba MAS CONMENOS, siempre que se trate de D&iacute;as y Horas H&aacute;biles en caso contrario ser&aacute;n acreditados al D&iacute;a H&aacute;bil siguiente. &nbsp;</span></li>
            <li><span style="font-size:9px;">Los dep&oacute;sitos realizados a trav&eacute;s de Trasferencias Electr&oacute;nicas SPEI o mediante cargos y abonos a cuentas de CAJA SOLIDARIA 2G KAPITAL<span style="color:yellow;">,</span> se acreditar&aacute; el mismo d&iacute;a siempre que se trate de D&iacute;as y Horas H&aacute;biles.</span></li>
            <li><span style="font-size:9px;">Los&nbsp;dep&oacute;sitos que se hagan dentro de los horarios establecidos por MASCON MENOS, en cheques u otros medios a&nbsp;cargo&nbsp;de<br>&nbsp;instituciones distintas a CAJA SOLIDARIA 2G KAPITAL, se entender&aacute;n recibidos por este &uacute;ltimo salvo buen cobro y su importe se abonar&aacute; en la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL &uacute;nicamente al efectuarse su cobro, conforme a los acuerdos interbancarios y reglas del Banco de M&eacute;xico aplicables al caso.</span></li>
            <li><span style="font-size:9px;">Los dep&oacute;sitos recibidos con motivo de Prestamos que CAJA SOLIDARIA 2G KAPITAL otorgue al Socio, ser&aacute;n abonados en la misma fecha en&nbsp;que&nbsp;su importe quede disponible, siempre que se trate de D&iacute;as y Horas H&aacute;biles.</span></li>
            <li><span style="font-size:9px;">&nbsp;Los dep&oacute;sitos a la Cuenta Mi Ahorro CAJA SOLIDARIA 2G KAPITAL podr&aacute;n generar rendimientos o intereses que se se&ntilde;alan en la Car&aacute;tula respectiva.</span></li>
        </ol>
        <ol start="6" style="list-style-type: lower-alpha;">
            <li><span style='font-family:"Verdana",sans-serif;font-size:9px;'>Todos los dep&oacute;sitos de ahorros e inversiones deben se depositados a la cuenta de la caja sin excepci&oacute;n alguna, por lo que se entrega dinero al personal de la caja, esta no se hace responsable del registro y aplicaci&oacute;n correspondiente.</span></li>
        </ol>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">D&Eacute;CIMA. - VINCULACI&Oacute;N CON PRESTAMOS OTORGADOS POR CAJA DE AHORRO.&nbsp;</span></span><span style="font-size:9px;">Independientemente de los Medios de Disposici&oacute;n para los Recursos y de la libre adquisici&oacute;n de bienes y servicios que puede realizar el Socio por medio de su tarjeta de d&eacute;bito, el presente Contrato s&oacute;lo podr&aacute; amparar un Producto de dep&oacute;sito por lo que corresponde a la cuenta &quot;Mi Ahorro CAJA SOLIDARIA 2G KAPITAL&quot;; sin embargo, en caso de que el Socio celebre operaciones de pr&eacute;stamo con CAJA SOLIDARIA 2G KAPITAL &eacute;stas podr&aacute;n estar vinculadas a la Cuenta &quot;Mi Ahorro&quot; del Socio donde CAJA SOLIDARIA 2G KAPITAL &uacute;nicamente podr&aacute; depositar los Recursos de los pr&eacute;stamo. Para lo estipulado en el presente p&aacute;rrafo, el Socio acepta y reconoce que, si dispone de los Recursos depositados en su Cuenta de ahorro&quot; derivados de cr&eacute;ditos otorgados, se entiende la expresa disposici&oacute;n de dichos prestamos, para lo cual las Partes se sujetar&aacute;n a lo dispuesto por el contrato de cr&eacute;dito que entre ellas hayan celebrado. Si el Socio llegara a cancelar el (los) pr&eacute;stamos (s) otorgados por CAJA SOLIDARIA 2G KAPITAL en el plazo que sea se&ntilde;alado dentro de los contratos respectivos y &eacute;stos son depositados en la Cuenta, entonces el Socio no deber&aacute; disponer en ning&uacute;n momento de dichos Recursos, y deber&aacute; proceder a retornar dichos recursos a la cuenta de CAJA SOLIDARIA 2G KAPITAL, en un plazo no mayor a 48 horas de haberse depositado, para que sea considerados como prestamos(s) cancelado(s) y no como prestamos activos.&nbsp;</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style="font-size:9px;">Por lo anterior, el Socio se compromete a realizar la devoluci&oacute;n del pr&eacute;stamo cancelado a CAJA SOLIDARIA 2G KAPITAL en los tiempos establecidos en este documento, declarando que de no ser as&iacute; se compromete a realizar el pago correspondiente del prestamos en cuesti&oacute;n.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">D&Eacute;CIMA PRIMERA. - ACCESO A LOS APARTADOS POR ORDEN JUDICIAL.&nbsp;</span></span><span style="font-size:9px;">CAJA SOLIDARIA 2G KAPITAL solo podr&aacute; disponer total o parcialmente los recursos que contenga la cuenta a Mi Ahorro CAJA SOLIDARIA 2G KAPITAL, incluyendo los recursos que se encuentren en Mis Apartados sin excepci&oacute;n alguna, siempre y cuando sea para dar cumplimiento a una orden de autoridad judicial o fiscal competente, seg&uacute;n sea el caso, en la cual se le ordene a CAJA SOLIDARIA 2G KAPITAL a disponer de dichos recursos.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:center;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>CAP&Iacute;TULO CUARTO. DE LAS INVERSIONES CAJA DE AHORRO</span></strong></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">D&Eacute;CIMA SEGUNDA.&nbsp;</span></span><span style="font-size:9px;">El Socio podr&aacute; ordenar a CAJA SOLIDARIA 2G KAPITAL invertir los Recursos o parte de estos en pagar&eacute;s con rendimiento liquidable al vencimiento conforme a los montos autorizados por CAJA SOLIDARIA 2G KAPITAL y la estipulado en el presente cap&iacute;tulo, dicha inversi&oacute;n tendr&aacute; la calidad de pr&eacute;stamo mercantil. La Inversi&oacute;n se documentar&aacute;&nbsp;con&nbsp;un pagar&eacute; o constancia de operaci&oacute;n emitido por CAJA SOLIDARIA 2G KAPITAL con un rendimiento liquidable al vencimiento, misma que ser&aacute; siempre nominativa y no se podr&aacute; pagar anticipadamente sino hasta la conclusi&oacute;n del plazo pactado.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style="font-size:9px;">La Inversi&oacute;n habr&aacute; de ser en Moneda Nacional y CAJA SOLIDARIA 2G KAPITAL restituir&aacute; las sumas de los Recursos invertidos m&aacute;s los intereses en la misma moneda en la Cuenta eje que el Socio haya designado.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">DECIMA TERCERA - ACEPTACI&Oacute;N DE PR&Eacute;STAMOS.&nbsp;</span></span><span style="font-size:9px;">El Socio podr&aacute; girar instrucciones a CAJA SOLIDARIA 2G KAPITAL con el fin de que con cargo a los Recursos depositado en la Cuenta eje contratada, se invierta la cantidad que el Socio asigne a CAJA SOLIDARIA 2G KAPITAL en calidad de pr&eacute;stamo mercantil; dicho pr&eacute;stamo se documentar&aacute; conforme lo estipulado en la cl&aacute;usula anterior a trav&eacute;s de pagar&eacute;s con rendimiento liquidable al vencimiento.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style="font-size:9px;">Los beneficiarios de la inversi&oacute;n ser&aacute;n los mismos que los designados por el Socio en la cl&aacute;usula Cuadrag&eacute;sima Quinta para los Recursos de la Cuenta eje.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">DECIMA CUARTA. - MONTOS M&Iacute;NIMOS.&nbsp;</span></span><span style="font-size:9px;">CAJA SOLIDARIA 2G KAPITAL podr&aacute; establecer el monto m&iacute;nimo que est&eacute; dispuesto a recibir para aperturar la Inversi&oacute;n, as&iacute; como para su mantenimiento; dichos montos CAJA SOLIDARIA 2G KAPITAL los informar&aacute; al Socio al momento de contrataci&oacute;n, a trav&eacute;s de su portal de internet, en medios impresos, o por cualquier medio que al efecto CAJA SOLIDARIA 2G KAPITAL determine y, en su caso, se especificar&aacute;n en el Anexo de Comisiones.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:9.8pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">DECIMA QUINTA. - DOCUMENTACI&Oacute;N.&nbsp;</span></span><span style="font-size:9px;">Cada Inversi&oacute;n se documentar&aacute; en un Pagar&eacute; emitido por CAJA SOLIDARIA 2G KAPITAL con rendimiento liquidable al vencimiento. Los Pagar&eacute;s o constancias de operaci&oacute;n que emita CAJA SOLIDARIA 2G KAPITAL respecto a las Inversiones ser&aacute;n siempre nominativos, no podr&aacute;n ser pagados anticipadamente y no podr&aacute;n ser transferidos excepto a Instituciones de Cr&eacute;dito, las que tampoco podr&aacute;n recibirlos en garant&iacute;a.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.85pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.2pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">DECIMA SEXTA. - DEP&Oacute;SITO.&nbsp;</span></span><span style="font-size:9px;">CAJA SOLIDARIA 2G KAPITAL recibir&aacute; del Socio los Pagar&eacute;s en dep&oacute;sito para su administraci&oacute;n al amparo del contrato de dep&oacute;sito bancario de t&iacute;tulos valor y de dinero en administraci&oacute;n consignado en el presente Contrato m&uacute;ltiple. La entrega de los Pagar&eacute;s en dep&oacute;sito se comprobar&aacute; con las constancias de pagar&eacute;s en administraci&oacute;n que CAJA SOLIDARIA 2G KAPITAL expida al Socio.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">DECIMA SEPTIMA. - PLAZO.&nbsp;</span></span><span style="font-size:9px;">Las Partes pactar&aacute;n, en cada caso, el plazo que corresponda al Pagar&eacute; en d&iacute;as naturales, debiendo ser no menor a un d&iacute;a y el mismo ser&aacute; forzoso para ambas partes. El plazo y la fecha de vencimiento de cada pagar&eacute; se establecer&aacute; en cada pagar&eacute; o en la constancia de operaci&oacute;n correspondiente. Transcurridos los plazos convenidos para su devoluci&oacute;n, CAJA SOLIDARIA 2G KAPITAL pagar&aacute; al Socio el d&iacute;a de vencimiento, mediante abono a la Cuenta eje los Recursos objeto de la Inversi&oacute;n m&aacute;s los rendimientos generados.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.2pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">DECIMA OCTAVA. - RENOVACI&Oacute;N AUTOM&Aacute;TICA.&nbsp;</span></span><span style="font-size:9px;">Si se hubiere convenido la renovaci&oacute;n autom&aacute;tica de la Inversi&oacute;n, la misma ser&aacute; renovada a su vencimiento en un plazo igual al originalmente contratado y ser&aacute; interrumpida cuando se actualicen indistintamente los siguientes supuestos:</span></p>
        <ol start="1" style="list-style-type: lower-alpha;margin-left:26px;">
            <li><span style="font-size:9px;">Cuando el Socio de acuerdo a la fecha de vencimiento de su Inversi&oacute;n gire instrucciones para dar por terminada la renovaci&oacute;n<br>&nbsp;autom&aacute;tica retirando los intereses y/o capital de su inversi&oacute;n.</span></li>
            <li><span style="font-size:9px;">Cuando la renovaci&oacute;n autom&aacute;tica de la Inversi&oacute;n, no importando el n&uacute;mero de periodos, alcance un plazo m&aacute;ximo de 2 (dos) a&ntilde;os y 6(seis) meses contados a partir de la fecha de contrataci&oacute;n.</span></li>
        </ol>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style="font-size:9px;">En referencia a los incisos a) y b) anteriores, los intereses y/o capital ser&aacute;n transferidos a la Cuenta Eje del mismo Socio, una vez que haya vencido la &uacute;ltima de las renovaciones autom&aacute;ticas. Para tal fin ser&aacute; aplicable la tasa bruta de inter&eacute;s expresada en t&eacute;rminos anuales que CAJA SOLIDARIA 2G KAPITAL haya dado a conocer al Socio mediante cualquier medio de comunicaci&oacute;n el d&iacute;a de la renovaci&oacute;n y para Inversiones de la misma clase de la&nbsp;que&nbsp;se renueve.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-left:14.0pt;'><span style="font-size:9px;">Si el vencimiento ocurre en un D&iacute;a Inh&aacute;bil la operaci&oacute;n ser&aacute; renovada al D&iacute;a H&aacute;bil siguiente. El Socio si as&iacute; lo desea, el referido D&iacute;a H&aacute;bil siguiente podr&aacute; solicitar a CAJA SOLIDARIA 2G KAPITAL la cancelaci&oacute;n de la renovaci&oacute;n de la Inversi&oacute;n y CAJA SOLIDARIA 2G KAPITAL entregar&aacute; los Recursos los intereses correspondientes, los cuales se devengar&aacute;n a la tasa pactada originalmente, considerando todos los d&iacute;as efectivamente transcurridos.</span></p>
        <p style='margin:0cm;text-align:left;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-left:14.0pt;'><span style="font-size:9px;">Los intereses se revisar&aacute;n y determinar&aacute;n por CAJA SOLIDARIA 2G KAPITAL en cada renovaci&oacute;n autom&aacute;tica y ser&aacute;n informados al Socio a trav&eacute;s de los medios de comunicaci&oacute;n&nbsp;o a trav&eacute;s de su Estado de Cuenta. La tasa de inter&eacute;s pactada originalmente nunca se aplicar&aacute; a las renovaciones autom&aacute;ticas y tampoco se aplicar&aacute; la pactada en el documento anterior a la renovaci&oacute;n.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-left:14.0pt;'><span style="font-size:9px;">Si el Socio inversionista no solicita la terminaci&oacute;n de su inversi&oacute;n con 30 d&iacute;as de anticipaci&oacute;n, se dar&aacute; por confirmada la renovaci&oacute;n autom&aacute;tica de la misma.&nbsp;</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.1pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-top:0cm;margin-right:0cm;margin-bottom:9.6pt;margin-left:14.0pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">DECIMA NOVENA. - RENDIMIENTOS.&nbsp;</span></span><span style="font-size:9px;">CAJA SOLIDARIA 2G KAPITAL pagar&aacute; los intereses que correspondan a cada Inversi&oacute;n a la tasa bruta anual de inter&eacute;s se convenga con el Socio en la constancia de pagar&eacute; correspondiente, dicha tasa permanecer&aacute; sin variaci&oacute;n alguna durante el plazo fijo de la Inversi&oacute;n y no proceder&aacute; revisi&oacute;n alguna de la misma. Los intereses se causar&aacute;n a partir del d&iacute;a en&nbsp;que&nbsp;se reciba la Inversi&oacute;n y hasta el d&iacute;a anterior al del vencimiento del plazo.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.1pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-top:0cm;margin-right:0cm;margin-bottom:9.6pt;margin-left:14.0pt;'><span style="font-size:9px;">Los intereses se calcular&aacute;n multiplicando el capital por el factor que resulte de dividir la tasa bruta anual convenida entre 360 (trescientos sesenta) y multiplicando el resultado as&iacute; obtenido por el n&uacute;mero de d&iacute;as efectivamente transcurridos durante el per&iacute;odo en el cual se<br>&nbsp;devenguen los rendimientos. Los c&aacute;lculos se efectuar&aacute;n cerr&aacute;ndose a cent&eacute;simas. Los intereses ser&aacute;n pagaderos al vencimiento del plazo.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:10.1pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-top:0cm;margin-right:0cm;margin-bottom:10.2pt;margin-left:14.0pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">VIG&Eacute;SIMA. - OPERACI&Oacute;N DE LA INVERSI&Oacute;N.&nbsp;</span></span><span style="font-size:9px;">Para la operaci&oacute;n de la Inversi&oacute;n el Socio deber&aacute; contar o abrir una cuenta de dep&oacute;sito bancario y mantenerla vigente durante el Plazo de vigencia del Pagar&eacute; respectivo.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.85pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-left:14.0pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">VIG&Eacute;SIMA PRIMERA. - SUPLETORIEDAD.&nbsp;</span></span><span style="font-size:9px;">En todo lo no previsto en el presente Cap&iacute;tulo y sin menoscabo de lo aqu&iacute; dispuesto, para este tipo de Inversiones ser&aacute;n aplicables las cl&aacute;usulas contenidas en el presente Contrato m&uacute;ltiple.</span></p>
        <p style='margin:0cm;text-align:center;line-height:9.85pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-left:14.0pt;'><strong><span style="font-size:9px;">CAPITULO QUINTO. LUGARES PARA EFECTUAR RETIROS MEDIOS DE DISPOSICI&Oacute;N</span></strong></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:14.0pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'>VIG&Eacute;SIMA SEGUNDA. - LUGAR COM&Uacute;N PARA EFECTUAR RETIROS.&nbsp;</span>El Socio podr&aacute; disponer libremente de los Recursos depositados en la Cuenta que corresponda a trav&eacute;s de cualquier Sucursal de CAJA SOLIDARIA 2G KAPITAL en D&iacute;as y Horas H&aacute;biles, para lo cual, deber&aacute; identificarse plenamente mediante Identificaci&oacute;n Oficial vigente y el llenado de la &quot;Solicitud Disposici&oacute;n de recursos de la cuenta&quot;. Conforme lo dispuesto en esta cl&aacute;usula y la D&eacute;cima Cuarta.</p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:14.0pt;'>La disposici&oacute;n de los Recursos en las Sucursales de CAJA SOLIDARIA 2G KAPITAL podr&aacute; ser hasta el saldo disponible; para la disposici&oacute;n en Comercios Afiliados el Socio podr&aacute; disponer hasta el l&iacute;mite establecido por el propio comercio.</p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:14.0pt;'>El Socio podr&aacute; disponer de los Recursos que obran en la Cuenta a trav&eacute;s de los diversos Medios de Disposici&oacute;n que&nbsp;CAJA SOLIDARIA 2G KAPITAL pone a su alcance.</p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:14.0pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'>VIG&Eacute;SIMA TERCERA. - DE LA TARJETA DE D&Eacute;BITO.&nbsp;</span>&Uacute;nicamente para el Producto Mi Ahorro, CAJA SOLIDARIA 2G KAPITAL, solicitar&aacute; al Socio le presente una tarjeta pl&aacute;stica de d&eacute;bito de su propiedad, con la vigencia estipulada y un n&uacute;mero &uacute;nico impreso en el anverso de la misma, para poder recibir la devoluci&oacute;n total o parcial de ahorro, inversi&oacute;n o pr&eacute;stamo, y con ella pueda realizar las siguientes operaciones y disposiciones:</p>
        <ol start="1" style="list-style-type: lower-alpha;margin-left:62px;">
            <li style="font-family: Verdana, Geneva, sans-serif; font-size: 8px;">Consulta de saldos y movimientos.</li>
            <li style="font-family: Verdana, Geneva, sans-serif; font-size: 8px;">Retiro de efectivo en cajeros autom&aacute;ticos.</li>
            <li style="font-family: Verdana, Geneva, sans-serif; font-size: 8px;">Pago&nbsp;en Comercios Afiliados al sistema de tarjetas con el que&nbsp;opere el banco emisor con cargo&nbsp;al saldo disponible, para&nbsp;la adquisici&oacute;n de bienes y servicios.</li>
            <li style="font-family: Verdana, Geneva, sans-serif; font-size: 8px;">Retiro&nbsp;de efectivo en Comercios Afiliados.</li>
            <li style="font-family: Verdana, Geneva, sans-serif; font-size: 8px;">Transferencias Electr&oacute;nicas SPEI.</li>
        </ol>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:9.6pt;margin-left:14.0pt;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;line-height:9.1pt;'><strong><span style='font-size:9px;font-family:"Verdana",sans-serif;'>VIG&Eacute;SIMA CUARTA.</span></strong><span style='font-size:9px;font-family:"Verdana",sans-serif;'>&nbsp;&ndash; <strong>PRESENTACI&Oacute;N DE LA TARJETA</strong>. MAS CON MENO solicitara al socio al Socio, presente una Tarjeta de D&eacute;bito de su propiedad, en este acto o de acuerdo con el procedimiento que CAJA SOLIDARIA 2G KAPITAL, en el entendido que CAJA SOLIDARIA 2G KAPITAL estar&aacute; facultado para registrar est&aacute; en la Solicitud de apertura de la Cuenta correspondiente, o a la persona f&iacute;sica que este &uacute;ltimo autorice para tal fin. La Presentaci&oacute;n de la Tarjeta de D&eacute;bito s&oacute;lo aplica para el Producto Mi Ahorro CAJA SOLIDARIA 2G KAPITAL.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:14.0pt;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>CAJA SOLIDARIA 2G KAPITAL y el Socio acuerdan se podr&aacute; proporcionar otra tarjeta de d&eacute;bito para devoluci&oacute;n de ahorro, inversi&oacute;n y pr&eacute;stamo, siempre que se informe por escrito, cada que el socio lo requiera, para poder tener una transaccionalidad segura y confiable para ambas partes.&nbsp;</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:14.0pt;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>VIG&Eacute;SIMA QUINTA</span></strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>. - <strong>PROPIEDAD DE LA TARJETA.</strong> La Tarjeta de D&eacute;bito vinculada al Producto Mi Ahorro CAJA SOLIDARIA 2G KAPITAL es propiedad del Banco emisor, por lo que CAJA SOLIDARIA 2G KAPITAL se deslinda de cualquier reclamo de por mal uso del socio.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:14.0pt;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>VIG&Eacute;SIMA SEPTIMA NOTIFICACIONES DE DEPOSITOS A TARJETA DE DEBITO.</span></strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>&nbsp;Para el Producto Mi Ahorro CAJA SOLIDARIA 2G KAPITAL, deber&aacute; entregar de manera f&iacute;sica o por medios electr&oacute;nicos, en todo momento, el comprobante de la transacci&oacute;n realizada y expedir una copia de este cuando as&iacute; lo requiera el socio.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:14.0pt;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>VIG&Eacute;SIMA OCTAVA &ndash; EMISI&Oacute;N DE COMPROBANTES ADICIONALES.</span></strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>&nbsp;Respecto de la emisi&oacute;n de comprobantes adicionales para aclaraciones de transacciones, CAJA SOLIDARIA 2G KAPITAL, las realizara a petici&oacute;n del Socio en periodo m&aacute;ximo de 24n Horas. De haberse solicitado, en el entendido que dichos comprobantes son emitidos de manera informativa.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:9.45pt;margin-left:14.0pt;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;line-height:8.9pt;'><strong><span style='font-size:9px;font-family:"Verdana",sans-serif;'>VIG&Eacute;SIMA NOVENA. &ndash; COSTO DE COMPROBANTES.</span></strong><span style='font-size:9px;font-family:"Verdana",sans-serif;'>&nbsp;CAJA SOLIDARIA 2G KAPITAL, establecer&aacute; el tarifario para determinar el costo por la emisi&oacute;n de comprobantes adicionales de transacciones (dep&oacute;sitos, estados de cuenta, etc.). el pago de este concepto debe realizarse en bancos a la cuenta de CAJA SOLIDARIA 2G KAPITAL.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:center;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>CAP&Iacute;TULO SEXTO. DISPOSICIONES GENERALES</span></strong></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><strong><span style="font-size:9px;">TRIG&Eacute;SIMA PRIMERA - MODIFICACI&Oacute;N AL CONTRATO.</span></strong><span style="font-size:9px;">&nbsp;CAJA SOLIDARIA 2G KAPITAL dar&aacute; aviso al Socio de cualquier modificaci&oacute;n al presente Contrato con 30 (treinta) d&iacute;as naturales de anticipaci&oacute;n a su entrada en vigor, mediante publicaciones en la p&aacute;gina de internet de<br>&nbsp;CAJA SOLIDARIA 2G KAPITAL &nbsp;&nbsp;</span><a href="http://www.cajasolidaria2gkapital.com.mx"><span style="font-size:9px;">http://www.cajasolidaria2gkapital.com.mx</span></a><span style="font-size:9px;">&nbsp;dando aviso a trav&eacute;s del estado de cuenta de dicha publicaci&oacute;n y en caso de tener su n&uacute;mero de tel&eacute;fono celular vinculado, a trav&eacute;s de mensajes de datos (SMS). En el evento de que el Socio no est&eacute; de acuerdo con las modificaciones propuestas al contenido obligacional, podr&aacute; solicitar por escrito la terminaci&oacute;n del Contrato dentro de los 30 (treinta) d&iacute;as naturales posteriores al aviso, sin responsabilidad alguna a su cargo y bajo las condiciones anteriores a la modificaci&oacute;n, debiendo cubrir, en su caso, los adeudos que por concepto de comisiones se hubieren generado a la fecha en que solicite dar por terminado el Contrato y, en su caso, retirando de la Cuenta los Recursos restantes. El uso o la continuaci&oacute;n en el empleo del Producto y/o servicio sobre los que se haya hecho la modificaci&oacute;n o adici&oacute;n, se considerar&aacute; como un consentimiento expreso respecto del cambio generado si despu&eacute;s del t&eacute;rmino expresado en la presente cl&aacute;usula el Socio no manifiesta su inconformidad. El Socio podr&aacute; en cualquier momento acudir a cualquier Oficina de Servicios o Sucursales de CAJA SOLIDARIA 2G KAPITAL por una reimpresi&oacute;n gratuita del Contrato.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">TRIG&Eacute;SIMA SEGUNDA. - RECHAZO DEL SERVICIO.&nbsp;</span></span><span style="font-size:9px;">CAJA SOLIDARIA 2G KAPITAL se reserva el derecho de otorgar o negar los Productos materia de este Contrato cuando: a) el Socio no cumpla con los requisitos que al efecto solicite CAJA SOLIDARIA 2G KAPITAL, b) cuando CAJA SOLIDARIA 2G KAPITAL tenga sospecha fundada de que los Recursos del Socio son de procedencia il&iacute;cita o, c) falsedad en las declaraciones del Socio.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:9.8pt;'><span style="font-size:9px;">El Socio reconoce y acepta que la solicitud que efect&uacute;e a CAJA SOLIDARIA 2G KAPITAL para la prestaci&oacute;n del servicio mediante el Producto convenido en la Car&aacute;tula&nbsp;que&nbsp;corresponda no implica la aceptaci&oacute;n por&nbsp;parte&nbsp;de este &uacute;ltimo&nbsp;para&nbsp;su consumaci&oacute;n, dicha aceptaci&oacute;n queda en todo caso sujeto al an&aacute;lisis que lleve a cabo CAJA SOLIDARIA 2G KAPITAL para dar tr&aacute;mite a dicha solicitud reserv&aacute;ndose en todo momento la facultad de otorgar o negar la activaci&oacute;n o acceso al Producto.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.85pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.2pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">TRIG&Eacute;SIMA TERCERA - ACTUALIZACI&Oacute;N DE LA INFORMACI&Oacute;N.&nbsp;</span></span><span style="font-size:9px;">El Socio tiene la obligaci&oacute;n de actualizar los datos proporcionados a CAJA SOLIDARIA 2G KAPITAL que se contienen en la Solicitud de apertura que forma parte de este Contrato, en un plazo no mayor de 30 (treinta) d&iacute;as naturales contados a partir del d&iacute;a en que dichos datos hayan cambiado, o cuando sean requeridos por CAJA SOLIDARIA 2G KAPITAL.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">TRIG&Eacute;SIMA CUARTA. - ESTADOS DE CUENTA.&nbsp;</span></span><span style="font-size:9px;">CAJA SOLIDARIA 2G KAPITAL generar&aacute; mensualmente un estado de cuenta correspondiente al Producto contratado, mismo que reflejar&aacute; las operaciones efectuadas durante el per&iacute;odo inmediato anterior a la Fecha de Corte, especificando los dep&oacute;sitos, retiros, transacciones y operaciones realizadas en la Cuenta y, en su caso, el impuesto retenido por disposici&oacute;n fiscal vigente y las comisiones y gastos generados durante dicho periodo. Dentro de los primeros 5 (cinco) D&iacute;as H&aacute;biles posteriores a la Fecha de Corte, las partes convienen, recabando la firma del Socio como autorizaci&oacute;n, que en sustituci&oacute;n del env&iacute;o al domicilio del Socio CAJA SOLIDARIA 2G KAPITAL pondr&aacute; a disposici&oacute;n del Socio dicho estado de cuenta en las Oficinas de Servicio y Sucursales de CAJA SOLIDARIA 2G KAPITAL, para que le(s) sea entregado gratuitamente, presentando su Identificaci&oacute;n Oficial vigente. La generaci&oacute;n del primer estado de cuenta por periodo ser&aacute; gratuita, aquellos estados de cuenta subsecuentes, solicitados por el Socio&nbsp;para&nbsp;el mismo periodo podr&aacute;n generar el cobro de comisiones de conformidad con lo establecido en la cl&aacute;usula Cuadrag&eacute;sima Octava del presente Contrato. En el estado de cuenta se especificar&aacute;n las cantidades abonadas o cargadas, fecha al corte de la Cuenta y, en su caso, el importe de las comisiones a cargo durante el periodo comprendido del &uacute;ltimo corte a la fecha. As&iacute; mismo, en dicho estado de cuenta se har&aacute;n constar e identificar&aacute;n las operaciones realizadas al amparo de los servicios convenidos, materia de este contrato.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.4pt;'><span style="font-size:9px;">As&iacute; mismo, en caso de que el Socio requiera consultar saldos, transacciones y movimientos, deber&aacute; acudir a cualquier Oficina de Servicios y/o Sucursales de CAJA SOLIDARIA 2G KAPITAL para que le sea entregada la informaci&oacute;n gratuitamente, presentando su Identificaci&oacute;n Oficial vigente o podr&aacute; llamar a la l&iacute;nea de n&uacute;mero gratuito 800 (_ _ _ _ _ _ _ _ _ _)- autenticando su identidad al realizar la llamada.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.1pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:9.6pt;'><span style="font-size:9px;">CAJA SOLIDARIA 2G KAPITAL informar&aacute; por escrito al Socio la fecha de corte de la Cuenta misma que no podr&aacute; variar sin previo aviso por escrito, comunicado por lo menos con 1 (un) mes de anticipaci&oacute;n. El Socio podr&aacute; objetar por escrito su estado de cuenta con las observaciones que considere procedentes dentro de los 90 (noventa) d&iacute;as naturales siguientes al corte de la Cuenta en los t&eacute;rminos dispuestos por la cl&aacute;usula Cuadrag&eacute;sima Tercera del presente Contrato. Transcurrido este plazo sin haberse hecho reparo a la Cuenta los asientos y conceptos que figuran en la contabilidad de CAJA SOLIDARIA 2G KAPITAL har&aacute;n fe en contra del Socio, salvo prueba en contrario en el juicio respectivo.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style="font-size:9px;">En caso que el Socio requiera la restauraci&oacute;n de un estado de cuenta con una antig&uuml;edad mayor a los dos meses, deber&aacute; acudir y solicitarlo a la Oficina de Servicios de CAJA SOLIDARIA 2G KAPITAL que le corresponda para que le sea entregado sin costo alguno en un periodo m&aacute;ximo de 8 (ocho) d&iacute;as h&aacute;biles contados a partir de dicha solicitud,&nbsp;para&nbsp;segundos estados de cuenta el Socio deber&aacute;&nbsp;absorber&nbsp;el costo referente a la generaci&oacute;n del mismo el cual se establece en la cl&aacute;usula Cuadrag&eacute;sima Octava del presente Contrato, solicitando por escrito en la Oficina de Servicios o Sucursal de CAJA SOLIDARIA 2G KAPITAL y recibiendo los estados de cuenta dentro de los 15 (quince) D&iacute;as H&aacute;biles posteriores a la recepci&oacute;n de la solicitud.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">TRIG&Eacute;SIMA QUINTA. - &nbsp;</span></span><span style="font-size:9px;">CAJA SOLIDARIA 2G KAPITAL no podr&aacute; dar informaci&oacute;n sobre las operaciones, el estado y movimientos de la Cuenta m&aacute;s que al Socio, a su representante legal o a las personas que tengan poder para disponer en la misma, salvo en los casos previstos por el art&iacute;culo 115 de la Ley de Instituciones de Cr&eacute;dito. Toda la informaci&oacute;n que el Socio proporcione para efectos de este Contrato y de los Productos y operaciones particulares que celebre con CAJA SOLIDARIA 2G KAPITAL estar&aacute;n protegidos conforme al art&iacute;culo 142 de la Ley de Instituciones de Cr&eacute;dito y dem&aacute;s normatividad aplicable.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;">TRIG&Eacute;SIMA SEXTA. - ACLARACIONES. CONSULTAS, QUEJAS O RECLAMACIONES.&nbsp;</span></span><span style="font-size:9px;">Con la finalidad de brindar un mejor servicio, CAJA SOLIDARIA 2G KAPITAL pone a disposici&oacute;n del Socio el procedimiento para la recepci&oacute;n de aclaraciones, consultas, quejas o reclamaciones, el cual se menciona a continuaci&oacute;n, la Unidad Especializada de CAJA SOLIDARIA 2G KAPITAL le indicar&aacute; al Socio el proceso a seguir dependiendo de cada caso, pudiendo realizarlo de la siguiente forma:</span></p>
        <div style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;'>
            <ol style="margin-bottom:0cm;list-style-type: upper-roman;margin-left:26px;">
                <li style="margin: 0cm 0cm 8pt; font-size: 8px; font-family: Verdana, Geneva, sans-serif;">Cuando el Socio no est&eacute; de acuerdo con alguno de los movimientos que aparezcan en el estado de cuenta respectivo, podr&aacute; levantar la aclaraci&oacute;n de manera inicial al n&uacute;mero gratuito 800 (_ _ _ _ _ _ _ _) dentro del plazo de 90 (noventa) d&iacute;as naturales contados a partir de la Fecha de Corte o, en su caso, de la realizaci&oacute;n de la operaci&oacute;n o del servicio. Posteriormente, la solicitud respectiva deber&aacute; presentarse con los comprobantes correspondientes ante la Oficina de Servicio o Sucursal de CAJA SOLIDARIA 2G KAPITAL en la que radica la Cuenta, o bien, en la Unidad Especializada de CAJA SOLIDARIA 2G KAPITAL, mediante escrito, correo electr&oacute;nico o cualquier otro medio por el que se pueda comprobar fehacientemente su recepci&oacute;n. En todos los casos, CAJA SOLIDARIA 2G KAPITAL se estar&aacute; obligado a acusar recibo de dicha solicitud y generar un folio.&nbsp;</li>
            </ol>
        </div>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:36.0pt;'>Trat&aacute;ndose de cantidades a cargo del Socio dispuestas mediante cualquier mecanismo determinado al efecto por la Comisi&oacute;n Nacional para la Protecci&oacute;n y Defensa de los Usuarios de los Servicios Financieros en disposiciones de car&aacute;cter general, el Socio tendr&aacute; el derecho de solicitar una aclaraci&oacute;n, as&iacute; como el de cualquier otra cantidad relacionada con dicho cargo, hasta en tanto se resuelva conforme al procedimiento a que se refiere esta cl&aacute;usula.</p>
        <div style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;'>
            <ol style="margin-bottom:0cm;list-style-type: undefined;margin-left:26px;">
                <li style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;'><span style="font-size:9px;">Una vez&nbsp;</span><span style="font-size: 9px;">recibida la solicitud de aclaraci&oacute;n, CAJA SOLIDARIA 2G KAPITAL tendr&aacute; un plazo m&aacute;ximo de 45 (cuarenta y cinco) d&iacute;as naturales para entregar al Socio el dictamen correspondiente, anexando copia simple del documento o evidencia considerada para la emisi&oacute;n de dicho dictamen, con base en la informaci&oacute;n que, conforme a las disposiciones aplicables, deba obrar en su poder, as&iacute; como un informe detallado en el que se respondan todos los hechos contenidos en la solicitud presentada por el Socio. En el caso de</span> <span style="font-size:9px;">reclamaciones relativas a operaciones realizadas en el extranjero, el plazo previsto en este p&aacute;rrafo ser&aacute; hasta de 180 (ciento ochenta) d&iacute;as naturales.&nbsp;</span></li>
            </ol>
        </div>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:36.0pt;'><span style="font-size:9px;">El dictamen e informe antes referidos deber&aacute;n formularse por escrito y suscribirse por personal de CAJA SOLIDARIA 2G KAPITAL facultado para ello. En el evento de que, conforme al dictamen que emita CAJA DE AHORRO, resulte procedente la devoluci&oacute;n del monto respectivo, CAJA SOLIDARIA 2G KAPITAL informar&aacute; por el mismo medio al Socio de tal resoluci&oacute;n y reembolsar&aacute; en la Cuenta la cantidad correspondiente, en caso de no proceder se entregar&aacute; una copia al Socio con la informaci&oacute;n pertinente.</span></p>
        <div style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;'>
            <ol style="margin-bottom:0cm;list-style-type: undefined;margin-left:26px;">
                <li style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;'><span style="font-size:9px;">&nbsp; &nbsp; Dentro del plazo de 45 (cuarenta y cinco) d&iacute;as naturales contados a partir de la entrega del dictamen a que se refiere la fracci&oacute;n<br>&nbsp;anterior, CAJA SOLIDARIA 2G KAPITAL estar&aacute; obligado a poner a disposici&oacute;n del Socio o bien, en la Unidad Especializada de CAJA SOLIDARIA 2G KAPITAL de que se trate, el expediente generado con motivo de la solicitud, as&iacute; como a integrar en &eacute;ste, bajo su m&aacute;s estricta responsabilidad, toda la documentaci&oacute;n e informaci&oacute;n que, conforme a las disposiciones aplicables, deba obrar en su poder y que se relacione directamente con la solicitud de aclaraci&oacute;n que corresponda y sin incluir datos correspondientes a operaciones relacionadas con terceras personas.&nbsp;</span></li>
            </ol>
        </div>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:36.0pt;'><span style="font-size:9px;">Lo antes dispuesto es sin perjuicio del derecho del Socio de acudir ante la Comisi&oacute;n Nacional para la Protecci&oacute;n y Defensa de los<br>&nbsp;Usuarios de Servicios Financieros o ante la autoridad jurisdiccional correspondiente conforme a las disposiciones legales aplicables.&nbsp;</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:36.0pt;'><span style="font-size:9px;">No obstante, lo anterior, el procedimiento previsto en esta cl&aacute;usula quedar&aacute; sin efectos a partir de que el Socio presente su demanda<br>&nbsp;ante autoridad jurisdiccional o conduzca su reclamaci&oacute;n en t&eacute;rminos de los art&iacute;culos 63 y 65 de la Ley de Protecci&oacute;n y Defensa al Usuario de Servicios Financieros.&nbsp;</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:36.0pt;'><span style="font-size:9px;">Transcurrido el plazo de 90 (noventa) d&iacute;as naturales contados a partir de la Fecha de Corte o, en su caso, de la realizaci&oacute;n de la<br>&nbsp;operaci&oacute;n o del servicio sin que CAJA SOLIDARIA 2G KAPITAL reciba objeci&oacute;n alguna de parte del Socio conforme a la presente cl&aacute;usula, se entender&aacute; la conformidad de &eacute;ste con el estado de cuenta correspondiente, y los asientos que figuren en la contabilidad de CAJA SOLIDARIA 2G KAPITAL har&aacute;n prueba plena en favor de este &uacute;ltimo.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;line-height:9.1pt;'><span style='font-size:9px;font-family:"Verdana",sans-serif;'>El Socio podr&aacute; contactar a la Unidad Especializada de CAJA SOLIDARIA 2G KAPITAL por medio de una de las siguientes formas:</span></p>
        <ul style="list-style-type: square;margin-left:26px;">
            <li><span style='font-family:"Verdana",sans-serif;font-size:9px;'>Llamando al tel&eacute;fono sin costo: 800 (_ _ _ _ _ _ _ _).</span></li>
            <li><span style='font-family:"Verdana",sans-serif;font-size:9px;'>En las Unidades Especializadas Estatales que hayan sido habilitadas en las oficinas de CAJA SOLIDARIA 2G KAPITAL que correspondan.</span></li>
            <li><span style='font-family:"Verdana",sans-serif;font-size:9px;'>Correo electr&oacute;nico:</span><a href="mailto:%20unidadespecializada@2gkapital.com"><span style='font-family:"Verdana",sans-serif;font-size:9px;'>&nbsp;</span><span style="font-family: Verdana, sans-serif; font-size: 9px;">unidadespecializada@2gkapital.com</span></a><span style="color:#0563C1;text-decoration:underline;"><span style="font-family: Verdana, sans-serif; font-size: 9px;">.mx</span></span></li>
        </ul>
        <p style='margin:0cm;text-align:justify;line-height:9.85pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;'><span style="font-size:9px;">Centro de Atenci&oacute;n Telef&oacute;nica de la Comisi&oacute;n Nacional para la Protecci&oacute;n y Defensa de los Usuarios de Servicios Financieros (CONDUSEF):</span></p>
        <ul style="list-style-type: square;margin-left:26px;">
            <li><span style="font-size:9px;">Tel&eacute;fono: 55 5340 0999.</span></li>
            <li><span style="font-size:9px;">P&aacute;gina de Internet</span><a href="http://www.condusef.gob.mx/"><span style="font-size:9px;color:windowtext;color:windowtext;">&nbsp;</span><span style="font-size:9px;color:windowtext;">www.condusef.gob.mx&nbsp;</span></a><span style="font-size:9px;">Correo electr&oacute;nico:</span><a href="mailto:asesoria@condusef.gob.mx"><span style="font-size:9px;color:windowtext;color:windowtext;">&nbsp;asesoria@condusef.gob.mx</span></a></li>
        </ul>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;line-height:107%;">TRIG&Eacute;SIMA SEPTIMA&nbsp;</span></span><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>- CESI&Oacute;N DE DERECHOS. El Socio no podr&aacute; ceder los derechos u obligaciones que para &eacute;l se deriven del presente Contrato y/o de los Productos que contrae mediante la firma de este Contrato. Lo dispuesto en la presente cl&aacute;usula.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;line-height:107%;">TRIG&Eacute;SIMA OCTAVA&nbsp;</span></span><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>- BENEFICIARIOS. El Socio deber&aacute; designar beneficiarios de la Cuenta que corresponda y podr&aacute;(n) en cualquier tiempo sustituirlos, as&iacute; como modificar la proporci&oacute;n correspondiente a cada uno de ellos, mediante el correcto llenado del formato que CAJA SOLIDARIA 2G KAPITAL proporcionar&aacute; para tal efecto, mismo que deber&aacute; ser entregado en la Oficina de Servicio o en la Sucursal de CAJA SOLIDARIA 2G KAPITAL en que radique la Cuenta. En caso de fallecimiento del Socio, CAJA SOLIDARIA 2G KAPITAL entregar&aacute; el importe correspondiente a quienes se hubiese designado como beneficiarios expresamente por escrito, en la proporci&oacute;n estipulada&nbsp;</span><span style='font-size:9px;font-family:"Verdana",sans-serif;'>para&nbsp;</span><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>cada uno de ellos, siendo requisito indispensable contar con la presencia conjunta en caso de ser 2 (dos) o m&aacute;s los beneficiarios&nbsp;</span><span style='font-size:9px;font-family:"Verdana",sans-serif;'>para&nbsp;</span><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>realizar el cobro la suma correspondiente estipulada.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>Si no existieren beneficiarios, el importe deber&aacute; entregarse en los t&eacute;rminos previstos en la legislaci&oacute;n com&uacute;n.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-family:"Verdana",sans-serif;color:black;background:white;font-weight:bold;'><span style="font-size:9px;line-height:107%;">TRIG&Eacute;SIMA NOVENA</span></span><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>. - CUENTA QUE NO REGISTREN MOVIMIENTOS. En caso de que la Cuenta no presente actividad o registro de movimientos, y &eacute;sta se encuentre sin saldo, durante un periodo de 3 meses o que el socio deje de ahorrar en forma consecutiva m&aacute;s de 4 veces en forma continuas, (De acuerdo a lo indicado en el Reglamento de Ahorro, Inversiones y Prestamos&rdquo;, CAJA SOLIDARIA 2G KAPITAL proceder&aacute; al realizar el cierre de dicha Cuenta. El monto contenido en la Cuenta y los intereses originados por la misma que no tengan fecha de vencimiento, o bien, que teni&eacute;ndola se renueven en forma autom&aacute;tica, as&iacute; como las transferencias o las inversiones vencidas y no reclamadas, que en el transcurso de 12 meses no hayan tenido movimiento por dep&oacute;sitos o retiros y, despu&eacute;s de que CAJA SOLIDARIA 2G KAPITAL haya dado aviso por escrito, en el domicilio del Socio que conste en el expediente respectivo, con 90 (noventa) d&iacute;as de antelaci&oacute;n deber&aacute;n ser abonados en una cuenta global que llevar&aacute; CAJA SOLIDARIA 2G KAPITAL para esos efectos. Con respecto a lo anterior, no se considerar&aacute;n movimientos a los cobros de comisiones que realice CAJA SOLIDARIA 2G KAPITAL. CAJA SOLIDARIA 2G KAPITAL no podr&aacute; cobrar comisiones a partir de su inclusi&oacute;n en la cuenta global de los instrumentos bancarios de captaci&oacute;n que se encuentren en los supuestos antes referidos. Los Recursos aportados en la cuenta global &uacute;nicamente generar&aacute;n un inter&eacute;s mensual equivalente al aumento en el &Iacute;ndice Nacional de Precios al Consumidor en el per&iacute;odo respectivo. Cuando el Socio se presente para realizar un dep&oacute;sito o retiro, o reclamar la transferencia o inversi&oacute;n, CAJA SOLIDARIA 2G KAPITAL deber&aacute; retirar de la cuenta global el importe total, a efecto de abonarlo a la cuenta respectiva o entreg&aacute;rselo.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>Los derechos derivados por los dep&oacute;sitos e inversiones y sus intereses a que se refiere esta cl&aacute;usula, sin movimiento en el transcurso de 12 meses contados a partir de que estos &uacute;ltimos se depositen en la cuenta global, cuyo importe no exceda por cuenta, al equivalente a 300 (trescientos) d&iacute;as de salario m&iacute;nimo general vigente en el Ciudad de M&eacute;xico prescribir&aacute;n en favor del patrimonio de la beneficencia p&uacute;blica. MAS CON MENO Sestar&aacute; obligado a enterar los Recursos correspondientes a la beneficencia p&uacute;blica dentro de un plazo m&aacute;ximo de 15 (quince) d&iacute;as contados a partir del 31 de diciembre del a&ntilde;o en que se cumpla el supuesto previsto en este p&aacute;rrafo. CAJA SOLIDARIA 2G KAPITAL estar&aacute; obligado a notificar a la Comisi&oacute;n Nacional Bancaria y de Valores sobre el cumplimiento de lo establecido en esta cl&aacute;usula dentro de los 2 (dos) primeros meses de cada a&ntilde;o, lo anterior en conformidad con el art&iacute;culo 61 de la Ley de Instituciones de Cr&eacute;dito.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>CUADRAG&Eacute;SIMA. - IMPUESTOS.&nbsp;</span></strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>En el caso de que &eacute;stos se generen de conformidad con la legislaci&oacute;n fiscal vigente durante la vigencia de la Cuenta, CAJA SOLIDARIA 2G KAPITAL efectuar&aacute; la retenci&oacute;n y entero del impuesto generado a la autoridad fiscal correspondiente y depositar&aacute; al Socio el rendimiento neto en su caso.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>CUADRAG&Eacute;SIMA PRIMERA. -</span></strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>&nbsp;<strong>AUTORIZACI&Oacute;N DE CARGOS Y COMISIONES</strong>. CAJA SOLIDARIA 2G KAPITAL cobrar&aacute; al Socio las comisiones que se establecen en el anexo de comisiones el cual formar&aacute; parte integrante del presente Contrato. Las operaciones realizadas a trav&eacute;s de los comisionistas bancarios podr&aacute;n generar una Comisi&oacute;n, consulte antes de realizar su operaci&oacute;n.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>CAJA SOLIDARIA 2G KAPITAL dar&aacute; a conocer al Socio a trav&eacute;s de su p&aacute;gina web y en medios impresos, los incrementos al importe de las comisiones, as&iacute; como las nuevas comisiones que pretenda cobrar, por lo menos con 30 (treinta) d&iacute;as naturales de anticipaci&oacute;n a la fecha prevista para que &eacute;stas surtan efectos. Sin perjuicio de lo anterior, el Socio en los t&eacute;rminos previstos en este Contrato, tendr&aacute; derecho a darlo por terminado en caso de no estar de acuerdo&nbsp;</span><span style='font-size:9px;font-family:"Verdana",sans-serif;'>con&nbsp;</span><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>los nuevos montos, sin que CAJA SOLIDARIA 2G KAPITAL pueda cobrar cantidad adicional alguna por este hecho, con excepci&oacute;n de los adeudos que ya se hubieren generado a la fecha en que se solicite dar por terminado el Producto que corresponda.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>CAJA SOLIDARIA 2G KAPITAL podr&aacute; ofrecer el servicio de Domiciliaci&oacute;n y en el supuesto de que el Socio haya aceptado o contratado con terceros cargos recurrentes en su Cuenta, relativos al pago de bienes, servicios o cr&eacute;ditos (&quot;Domiciliaci&oacute;n&quot;), este podr&aacute; solicitar a CAJA SOLIDARIA 2G KAPITAL en cualquier momento la terminaci&oacute;n del servicio de Domiciliaci&oacute;n, bastando para ello, la presentaci&oacute;n del formato de solicitud de Cancelaci&oacute;n de Domiciliaci&oacute;n que CAJA SOLIDARIA 2G KAPITAL previamente haya puesto a disposici&oacute;n del Socio.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>La cancelaci&oacute;n del servicio de Domiciliaci&oacute;n solicitada por el Socio en t&eacute;rminos del p&aacute;rrafo que antecede surtir&aacute; efectos en un plazo no mayor a 3 (tres) D&iacute;as H&aacute;biles contados a partir de la fecha en que CAJA SOLIDARIA 2G KAPITAL reciba el formato que se indica en el p&aacute;rrafo inmediato anterior, por lo que a partir de dicha fecha CAJA SOLIDARIA 2G KAPITAL rechazar&aacute; cualquier cargo que se pretenda efectuar a la Cuenta, por concepto de Domiciliaci&oacute;n.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>CUADRAG&Eacute;SIMA SEGUNDA - DOMICILIOS. AVISOS Y NOTIFICACIONES</span></strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>. Los avisos, notificaciones o cualquier requerimiento que las Partes deban darse conforme al presente Contrato, se realizar&aacute;n en los domicilios se&ntilde;alados por el Socio en la Solicitud que en su caso se genere y que forma parte integrante de este Contrato o en acto posterior en formatos de CAJA SOLIDARIA 2G KAPITAL y/o a trav&eacute;s de medios electr&oacute;nicos o automatizados disponibles y aceptados por CAJA SOLIDARIA 2G KAPITAL.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>CUADRAG&Eacute;SIMA TERCERA. - VIGENCIA Y TERMINACI&Oacute;N.</span></strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>&nbsp;La duraci&oacute;n del presente Contrato es por tiempo indeterminado, no obstante, se podr&aacute; dar por terminado a partir de la fecha en que el Socio solicite la terminaci&oacute;n o cancelaci&oacute;n del Contrato, bastando para ello la presentaci&oacute;n de una solicitud por escrito en cualquier Oficina de Servicios o Sucursal de CAJA SOLIDARIA 2G KAPITAL.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style="font-size:9px;">CAJA SOLIDARIA 2G KAPITAL se cerciorar&aacute; de la autenticidad y veracidad de la identidad del Socio que formule la solicitud de terminaci&oacute;n respectiva, para lo cual, CAJA SOLIDARIA 2G KAPITAL confirmar&aacute; los datos personalmente y proporcionar&aacute; al Socio el saldo existente en la Cuenta correspondiente. El Contrato se dar&aacute; por terminado a partir de la fecha en que el Socio solicite por escrito su terminaci&oacute;n, siempre y cuando se cubran los adeudos y comisiones devengados a esa fecha y se retire el saldo existente en ese momento. Una vez realizado el retiro del saldo de la Cuenta, CAJA SOLIDARIA 2G KAPITAL proporcionar&aacute; al Socio el acuse de recibo y clave de confirmaci&oacute;n o n&uacute;mero de folio de cancelaci&oacute;n, renunciando tanto CAJA SOLIDARIA 2G KAPITAL como el Socio a sus derechos de cobro residuales, que pudieran subsistir despu&eacute;s del momento de la cancelaci&oacute;n, en el caso del Producto de Inversiones, cuando el Socio de por terminado el Contrato de&nbsp;forma&nbsp;anticipada, los recursos ser&aacute;n entregados en la fecha de vencimiento del pagar&eacute;. Derivado de la solicitud de terminaci&oacute;n de Contrato presentada por el Socio CAJA SOLIDARIA 2G KAPITAL proceder&aacute; de la siguiente manera: a) cancelar&aacute; los Medios de Disposici&oacute;n vinculados al Contrato en la fecha de presentaci&oacute;n de la solicitud; el Socio deber&aacute; hacer entrega de &eacute;stos o manifestar por escrito y bajo protesta de decir verdad en escrito&nbsp;libre,&nbsp;que fueron destruidos o que no cuenta con ellos, por lo que no podr&aacute;n hacer disposici&oacute;n alguna a partir de dicha fecha, b) rechazar&aacute; cualquier disposici&oacute;n que pretenda efectuarse con posterioridad a la cancelaci&oacute;n de los Medios de Disposici&oacute;n, en consecuencia, no se podr&aacute;n hacer nuevos cargos adicionales a partir del momento en que se realice la cancelaci&oacute;n, excepto los ya generados, c) cancelar&aacute;, sin su responsabilidad, los productos y/o servicios adicionales necesariamente vinculados o asociados a la Cuenta en la fecha de la solicitud de terminaci&oacute;n incluyendo aquellos indicados en la cl&aacute;usula Quincuag&eacute;sima segunda, d) se abstendr&aacute; de condicionar la terminaci&oacute;n del presente Contrato a la devoluci&oacute;n del Contrato que obre en poder del Socio, y e) se abstendr&aacute; de cobrar al Socio Comisi&oacute;n o penalizaci&oacute;n por la terminaci&oacute;n del Contrato.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style="font-size:9px;">El Socio podr&aacute; cancelar sin responsabilidad alguna de su parte el presente Contrato en un per&iacute;odo de 10 (diez) D&iacute;as H&aacute;biles posteriores a su firma. En este caso, CAJA SOLIDARIA 2G KAPITAL no podr&aacute; cobrar Comisi&oacute;n alguna, siempre y cuando el Socio no haya utilizado u operado el(los) Producto(s) contratado(s).</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:9.8pt;'><span style="font-size:9px;">CAJA SOLIDARIA 2G KAPITAL podr&aacute; dar por terminado el&nbsp;presente&nbsp;Contrato sin su responsabilidad y el Socio tendr&aacute; la obligaci&oacute;n de cubrir de inmediato todas y cada una de sus obligaciones, pago de comisiones y dem&aacute;s accesorios cuando: a) el Socio no mantenga&nbsp;durante&nbsp;3 meses consecutivos saldo en la Cuenta, b) el Socio no realice transacci&oacute;n, operaci&oacute;n o movimiento alguno en la Cuenta durante 12 meses consecutivos c) el Socio proporcione informaci&oacute;n y/o documentaci&oacute;n falsa a CAJA SOLIDARIA 2G KAPITAL d) en caso de la Cuenta Mi Grupo CAJA SOLIDARIA 2G KAPITAL, &eacute;sta no mantenga los cotitulares necesarios conforme el Cap&iacute;tulo Cuarto del presente Contrato, y e) el Socio incumpla con cualquiera de las obligaciones a su cargo con motivo del presente Contrato.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.85pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.2pt;'><span style="font-size:9px;">Al momento de la terminaci&oacute;n o cancelaci&oacute;n del&nbsp;presente&nbsp;Contrato el cobro de los servicios adicionales incluyendo el servicio de Domiciliaci&oacute;n de productos o servicios asociados a la cuenta, se cancelar&aacute;n sin responsabilidad para CAJA SOLIDARIA 2G KAPITAL con independencia de quien conserve la autorizaci&oacute;n de los cargos correspondientes.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>CUADRAG&Eacute;SIMA CUARTA - LEY DE PROTECCI&Oacute;N AL AHORRO.&nbsp;</span></strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>&Uacute;nicamente est&aacute;n garantizados por el Instituto para la Protecci&oacute;n al Ahorro Bancario (IPAB), los dep&oacute;sitos bancarios de dinero a la vista, retirables en d&iacute;as preestablecidos, de ahorro, y a plazo o con previo aviso, as&iacute; como los pr&eacute;stamos y cr&eacute;ditos que acepte CAJA SOLIDARIA 2G KAPITAL, hasta por el equivalente a cuatrocientas mil UDI por persona, cualquiera que sea el n&uacute;mero, tipo y clase de dichas obligaciones a su favor y a cargo de la instituci&oacute;n de banca m&uacute;ltiple.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>Para efectos del presente Contrato, se entender&aacute; por Ganancia Anual Total (GAT), la ganancia anual total neta expresada en t&eacute;rminos porcentuales anuales que, para fines informativos y de comparaci&oacute;n, incorpora los intereses nominales capitalizables que, en su caso, genere el producto contratado por el Socio, menos los costos relacionados con el mismo, incluidos los de apertura, la GAT Nominal y la GAT Real del producto contratado podr&aacute; consultarse en la Car&aacute;tula del mismo y/o en la Constancia de pagar&eacute; respectivo (&quot;GAT REAL&quot; es el rendimiento que obtendr&iacute;a despu&eacute;s de descontar la inflaci&oacute;n estimada&quot;).</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:10.6pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>CUADRAG&Eacute;SIMA QUINTA. - SERVICIOS ADICIONALES</span></strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>. CAJA SOLIDARIA 2G KAPITAL ofrecer&aacute; al Socio el servicio de Pago M&oacute;vil, para lo cual, este &uacute;ltimo deber&aacute; acudir a cualquier Oficina de Servicio o Sucursal para otorgar su aprobaci&oacute;n y solicitar los formatos para realizar el alta, baja o modificaci&oacute;n de su n&uacute;mero de tel&eacute;fono celular vinculado a la Cuenta; el registro de la informaci&oacute;n &nbsp;se realizar&aacute; como m&aacute;ximo 24 (veinticuatro) horas despu&eacute;s de haber entregado debidamente firmados los formatos que se mencionan en la presente cl&aacute;usula sin que exista ning&uacute;n costo por la recepci&oacute;n de mensajes, registros, bajas o cambios de n&uacute;meros de tel&eacute;fono celular vinculados.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;line-height:8.9pt;'><span style='font-size:9px;font-family:"Verdana",sans-serif;'>Cuando el servicio de Pago M&oacute;vil se encuentre activo, el Socio recibir&aacute; notificaciones mediante mensajes de datos (SMS) en el tel&eacute;fono celular vinculado a la Cuenta, mismas que ser&aacute;n: a) cuando se efect&uacute;en transacciones en la Cuenta; b) por bienvenida a la Cuenta y; c) para promociones, aniversarios y/o campa&ntilde;as de CAJA SOLIDARIA 2G KAPITAL.</span></p>
        <p style='margin:0cm;text-align:justify;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:13.0pt;'><span style="font-size:9px;">De la misma forma, el Socio podr&aacute; solicitar la baja de este servicio acudiendo a cualquier Oficina de Servicios o Sucursal sin que obre impedimento o costo alguno para su realizaci&oacute;n.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:13.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>CUADRAG&Eacute;SIMA SEXTA. - JURISDICCI&Oacute;N Y COMPETENCIA.</span></strong><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>&nbsp;Para la interpretaci&oacute;n y cumplimiento de este Contrato, las Partes que intervienen en el presente instrumento se someten a la jurisdicci&oacute;n y competencia de los Tribunales que correspondan al del lugar en que se suscribe este Contrato, o a los Tribunales Judiciales de la Ciudad de M&eacute;xico, a elecci&oacute;n de CAJA SOLIDARIA 2G KAPITAL, renunciando a cualquier otro fuero que por raz&oacute;n de su domicilio presente o futuro les pudiera corresponder.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:13.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>CONSENTIMIENTO PARA EL TRATAMIENTO DE LOS DATOS PERSONALES:</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;'><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>Por el presente, otorgo de manera expresa, mi consentimiento y autorizaci&oacute;n para que CAJA SOLIDARIA 2G KAPITAL, Instituci&oacute;n de Ahorro Popular (en adelante &quot;CAJA SOLIDARIA 2G KAPITAL&quot;) con domicilio <strong>S. Rafael 6, Tecamac Centro. Tecamac, Estado de M&eacute;xico C.P. 55740,&nbsp;</strong>utilice mis datos personales recabados para: Otorgar los servicios indicados en este contrato. Para mayor informaci&oacute;n acerca del tratamiento y de los derechos que puede hacer valer, usted puede acceder al Aviso de Privacidad&nbsp;</span><span style='font-size:9px;font-family:"Verdana",sans-serif;'>Integral para&nbsp;</span><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>Socios Ahorro a trav&eacute;s de la siguiente liga:&nbsp;</span><a href="http://www.cajasolidaria2gkapital.com.mx"><span style='font-size:9px;font-family:"Verdana",sans-serif;'>http://www.cajasolidaria2gkapital.com.mx</span></a><span style='font-size:9px;font-family:"Verdana",sans-serif;'>&nbsp;e</span><span style='font-size:9px;line-height:107%;font-family:"Verdana",sans-serif;'>n la secci&oacute;n de Privacidad.</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:justify;line-height:11.05pt;'><span style='font-size:9px;font-family:"Verdana",sans-serif;'>Le&iacute;do que fue el presente Contrato por las Partes, comprendiendo su contenido incluyendo Car&aacute;tula y anexo de Disposiciones Legales las cuales forman parte integrante del mismo, el Socio manifiesta la libre expresi&oacute;n de su voluntad la cual no tiene vicios de consentimiento que pudiera invalidar el presente Contrato, en consecuencia, el Socio lo firma en el lugar y fecha&nbsp;que&nbsp;se se&ntilde;alan en la Solicitud de Apertura de Cuenta del presente Contrato.</span></p>
        <table style="width: 100%;">
            <tbody>
                <tr>
                    <td style="width: 100.0000%;">
                        <p style="text-align:center;font-size:11.0pt;"><span style="font-size:9px;">EL SOCIO/ EL TITULAR</span></p>
                        <p style="font-size:11.0pt;"><span style="font-size:9px;">&nbsp;</span></p>
                        <p style="font-size:11.0pt;">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;____________________________</p>
                        <p style="text-align:center;font-size:10px;"><span style="font-size:9px;">Si no sabe o no puede firmar El Socio firma a su ruego y en su nombre un tercero indicando su nombre y</span></p>
                        <p style="text-align:center;font-size:10px;"><span style="font-size:9px;">estampando la huella digital del Socio.</span></p>
                        <p style="text-align:justify;font-size:11.0pt;"><span style="font-size:9px;">&nbsp;</span></p>
                        <p style="text-align:justify;font-size:11.0pt;"><span style="font-size:9px;">&nbsp;</span></p>
                        <p style="text-align:justify;font-size:11.0pt;">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;________________________ &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; _________________________</p>
                        <p style="text-align:left;font-size:10px;"><span style="font-size:9px;">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Gerente de Sucursal &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Comit&eacute;</span></p><br>
                    </td>
                </tr>
            </tbody>
        </table>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;text-align:center;line-height:8.9pt;'><br></p>
        <p style='margin:0cm;text-align:left;line-height:9.6pt;background:transparent;font-size:10px;font-family:"Verdana",sans-serif;margin-bottom:10.0pt;'><span style="font-size:9px;">&nbsp;</span></p>
        <p style='margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;font-size:11.0pt;font-family:"Calibri",sans-serif;'>&nbsp;</p>
        HTML;
    }

    public function GetContratoPeque($contrato)
    {
        $datos = CajaAhorroDao::DatosContratoPeque($contrato);
        if (!$datos) exit("No se encontró información para el contrato: " . $contrato);

        $monto = "$" . number_format($datos['MONTO_APERTURA'], 2, '.', ',');
        $monto_letra = self::NumeroLetras($datos['MONTO_APERTURA']);

        return <<<html
        <div class="contenedor">
            <p>
                CONTRATO PRIVADO DE MUTUO A PLAZO INDETERMINADO QUE CELEBRAN POR UNA PARTE EL (LA)
                <b>C. {$datos['NOMBRE']}</b>, EN LO SUCESIVO COMO EL “MUTUANTE Y/O PRESTAMISTA” Y POR LA OTRA
                PARTE EL <b>C. ANTONIO LORENZO HERNÁNDEZ</b>, EN LO SUCESIVO EL “MUTUARIO Y/O PRESTATARIO”, DE
                CONFORMIDAD CON LAS SIGUIENTES:
            </p>
            <h3>DECLARACIONES</h3>
            <div calss="decalraciones">
                <ol>
                    <li>Declara <b>"EL MUTUARIO Y/O PRESTATARIO"</b> bajo protesta de decir verdad:</li>
                    <ol class="listaLetras">
                        <li>
                            Ser persona física con plena capacidad jurídica para la celebración del presente
                            contrato y para obligarse individualmente a todos sus términos con pleno
                            conocimiento de su objetivo y efectos jurídicos.
                        </li>
                        <li>
                            Tener su domicilio en <b>Avenida Melchor Ocampo, número 416 Interior 1, Colonia 
                            Cuauhtémoc, Alcaldía Cuauhtémoc, Ciudad de México, C.P. 06500</b>, mismo que 
                            señala para todos sus efectos derivados de este contrato.
                        </li>
                        <li>
                            Que cuenta con la capacidad y solvencia económica suficiente para cumplir con las
                            obligaciones a su cargo derivadas del presente contrato.
                        </li>
                    </ol>
                    <li>Declara el <b>“MUTUANTE Y/O PRESTAMISTA”:</b></li>
                    <ol class="listaLetras">
                        <li>Contar con la capacidad suficiente para la celebración del presente contrato.</li>
                        <li>
                            Que su domicilio para los efectos de este contrato es el ubicado en <b>{$datos['DIRECCION']}</b>.
                        </li>
                    </ol>
                    <li><b>LAS PARTES</b> declaran:</li>
                    <ol class="listaLetras">
                        <li>
                            Que reconocen recíprocamente la capacidad jurídica con la que comparecen a la
                            celebración de este contrato, manifestando que el mismo está libre de cualquier
                            vicio del consentimiento que pudiera afectar su plena validez.
                        </li>
                        <li>
                            Que manifiestan su consentimiento para celebrar el presente contrato de mutuo con
                            interés.
                        </li>
                        <li>
                            Que reconocen en forma mutua la personalidad con que actúan en la celebración del
                            presente instrumento.
                        </li>
                    </ol>
                </ol>
            </div>
            <h3>CLAUSULAS</h3>
            <p>
                <b>PRIMERA.-</b> <b>OBJETO DEL CONTRATO.</b> Que las partes tienen pleno conocimiento que el
                objeto del presente contrato es el préstamo de dinero con interés a un plazo indeterminado.
            </p>
            <p>
                <b>SEGUNDA.-</b> <b>MONTO DEL PRESTAMO.</b> Será variable conforme a los depósitos o
                exhibiciones que haga el “MUTUANTE Y/O PRESTAMISTA” al “MUTUARIO Y/O PRESTATARIO”.
            </p>
            <p>
                <b>TERCERA.-</b> <b>PLAZO.</b> Las partes convienen que el préstamo no contará con un plazo
                determinado, por lo que una vez que el “MUTUANTE Y/O PRESTAMISTA” reclame la devolución del
                monto mutuado le será devuelto en un plazo de siete días hábiles después de su solicitud de
                devolución, la cual deberá hacer por escrito al “MUTUARIO Y/O PRESTATARIO”; el interés
                ordinario que obtendrá el “MUTUARIO Y/O PRESTATARIO” será del 5% anualizado.
            </p>
            <p>
                <b>CUARTA.-</b> <b>RECIBO DE DINERO.</b> “MUTUARIO Y/O PRESTATARIO” recibe del “MUTUANTE Y/O
                PRESTAMISTA” a su más entera satisfacción la cantidad de <b>{$monto} ({$monto_letra})</b>,
                otorgando como el recibo más amplio y eficaz de la recepción de dicho dinero la firma del
                presente contrato; dicha cantidad será el mínimo que se podrá exhibir o entregar para
                celebrar el presente contrato.
            </p>
            <p>
                <b>QUINTA.-</b> <b>LUGAR DE PAGO.</b> “MUTUANTE Y/O PRESTAMISTA” acudirá al domicilio del
                “MUTUARIO Y/O PRESTATARIO” o al lugar que éste le indique a recibir el pago, conforme a lo
                señalado en la cláusula TERCERA del presente contrato, el “MUTUANTE Y/O PRESTAMISTA” deberá
                acudir personalmente.
            </p>
            <p>
                <b>SEXTA.-</b> <b>INTERÉS MORATORIO.</b> “MUTUARIO Y/O PRESTATARIO” pagará al “MUTUANTE Y/O
                PRESTAMISTA” una comisión del 10% sobre el monto total del préstamo.
            </p>
            <p>
                <b>SEPTIMA.-</b> <b>INCUMPLIMIENTO.</b> En caso de incumplimiento de pago del “MUTUARIO Y/O
                PRESTATARIO”, el “MUTUANTE Y/O PRESTAMISTA” podrá reclamar el cumplimiento forzoso del
                presente contrato mediante los procesos legales que la Ley vigente determine.
            </p>
            <p>
                <b>OCTAVA.-</b> <b>OTROS CONTRATOS.</b> En caso de que el “MUTUANTE Y/O PRESTAMISTA” tuviera
                algún otro tipo de negociación con el “MUTUARIO Y/O PRESTATARIO”; autoriza desde este
                momento que en caso de cualquier tipo de incumplimiento referente a pagos, se pueda aplicar
                del presente contrato el pago pendiente a los otros instrumentos o contratos que existan.
            </p>
            <p>
                <b>NOVENA.-</b> En caso de fallecimiento del “MUTUANTE Y/O PRESTAMISTA”, el adeudo que
                exista en esa fecha deberá ser cubierto a la persona que haya señalado como beneficiario;
                para que esto proceda, se deberá acreditar en forma fehaciente el hecho con el acta de
                defunción correspondiente.
            </p>
            <p>
                <b>DECIMA.-</b> Para la celebración del presente Contrato el “MUTUANTE Y/O PRESTAMISTA”
                acepta cubrir al “MUTUARIO Y/O PRESTATARIO” a la firma del presente la cantidad de $200.00
                (DOSCIENTOS PESOS 00/100 M. N.) por concepto de gastos de papelería.
            </p>
            <p>
                <b>DECIMA PRIMERA.-</b> <b>LAS PARTES</b> manifiestan que no existe dolo ni cláusula
                contraria a derecho, no dándose los supuestos de ignorancia ni extrema necesidad,
                conscientes de su alcance y valor jurídico lo firman de conformidad.
            </p>
            <p>
                <b>DECIMA SEGUNDA.-</b> <b>COMPETENCIA.</b> Para el cumplimiento y resolución del presente
                contrato las partes se someten a la jurisdicción y competencia de los Juzgados de la Ciudad
                de México, renunciando expresamente a la jurisdicción de futuro domicilio.
            </p>
            <table style="width: 100%">
                <tr>
                    <td colspan="3" style="text-align: center; height: 90px">
                        <b>Ciudad de México, a {$datos['FECHA_F_LEGAL']}</b>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center; width: 45%">
                        <b>EL MUTUANTE Y/O PRESTAMISTA</b>
                    </td>
                    <td style="width: 10%"></td>
                    <td style="text-align: center; width: 45%">
                        <b>EL MUTUARIO Y/O PRESTATARIO</b>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="height: 80px"></td>
                </tr>
                <tr>
                    <td style="text-align: center; width: 45%; border-top: 1px solid">
                        <b>{$datos['NOMBRE']}</b>
                    </td>
                    <td style="width: 10%"></td>
                    <td style="text-align: center; width: 45%; border-top: 1px solid">
                        <b>ANTONIO LORENZO HERNÁNDEZ</b>
                    </td>
                </tr>
            </table>
        </div>
        <div style="page-break-after: always"></div>
        <div>
            <h3 class="fechaTitulo">Ciudad de México a {$datos['FECHA_F_LEGAL']}</h3>
            <p>
                El suscrito <b>{$datos['NOMBRE']}</b>, a través de la presente y bajo
                protesta de decir verdad, manifiesto que los recursos que he exhibido y que se señalan a
                detalle en el <b>CONTRATO DE MUTUO</b> de fecha {$datos['FECHA_F_LEGAL']}, celebrado en mi carácter de
                <b>“MUTUANTE Y/O PRESTAMISTA”</b> con el <b>C. ANTONIO LORENZO HERNÁNDEZ</b> en su carácter de “MUTUARIO
                Y/O PRESTATARIO” provienen de un <b>ORIGEN LÍCITO</b>, por lo que desde este momento señalo que no
                me encuentro en ninguno de los supuestos referidos en el artículo 400 Bis del Código Penal
                Federal en vigor.
            </p>
            <p>
                De la misma forma, <b>DESLINDO al “MUTUARIO Y/O PRESTATARIO”</b> de cualquier tema que pueda
                presentarse en el futuro y que sea relacionado con los recursos económicos del suscrito en
                los diversos actos jurídicos que se celebren.
            </p>
            <table style="width: 100%; padding-top: 150px">
                <tr>
                    <td style="text-align: center; width: 33%"></td>
                    <td style="text-align: center; width: 33%">
                        <b>ATENTAMENTE</b>
                    </td>
                    <td style="text-align: center; width: 33%"></td>
                </tr>
                <tr>
                    <td colspan="3" style="height: 100px"></td>
                </tr>
                <tr>
                    <td style="text-align: center; width: 25%"></td>
                    <td style="text-align: center; width: 50%; border-top: 1px solid">
                        <b>{$datos['NOMBRE']}</b>
                    </td>
                    <td style="text-align: center; width: 25%"></td>
                </tr>
            </table>
        </div>    
        html;
    }

    //********************BORRAR????********************//
    public function SolicitudRetiroHistorial()
    {
        $extraHeader = <<<html
        <title>Caja Cobrar</title>
        <link rel="shortcut icon" href="/img/logo.png">
html;

        $extraFooter = <<<html
        <script>
           
        </script>
html;

        View::set('header', $this->_contenedor->header($extraHeader));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::render("caja_menu_solicitud_retiro_historial");
    }

    //////////////////////////////////////////////////
    public function ReimprimeTicketSolicitudes()
    {
        $extraFooter = <<<html
        <script>
            {$this->showError}
            {$this->configuraTabla}
            {$this->muestraPDF}
            {$this->imprimeTicket}
            {$this->addParametro}
            {$this->validaFIF}
            {$this->consultaServidor}
            {$this->valida_MCM_Complementos}
         
            $(document).ready(() => {
                configuraTabla("solicitudes");
            })
             
            const buscar = () => {
                const datos = []
                addParametro(datos, "usuario", "{$_SESSION['usuario']}")
                addParametro(datos, "fechaI", document.querySelector("#fechaI").value)
                addParametro(datos, "fechaF", document.querySelector("#fechaF").value)
                addParametro(datos, "estatus", document.querySelector("#estatus").value)
                 
                consultaServidor("/Ahorro/GetSolicitudesTickets/", $.param(datos), (respuesta) => {
                    $("#solicitudes").DataTable().destroy()
                     
                    if (respuesta.datos == "") showError("No se encontraron solicitudes de retiro en el rango de fechas seleccionado.")
                     
                    $("#solicitudes tbody").html(respuesta.datos)
                    configuraTabla("solicitudes")
                })
            }
             
            const impTkt = async (tkt) => {
                if (!await valida_MCM_Complementos()) return
                 
                imprimeTicket(tkt)
            }
        </script>
        html;

        $tabla = self::GetSolicitudesTickets();
        $tabla = $tabla['success'] ? $tabla['datos'] : "";

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Solicitudes de reimpresión Tickets", $this->XLSX)));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::set('tabla', $tabla);
        View::set('fecha', date("Y-m-d"));
        View::set('fecha_actual', date("Y-m-d H:i:s"));
        View::render("caja_menu_reimprime_ticket_historial");
    }

    public function GetSolicitudesTickets()
    {
        $usuario = $_POST['usuario'] ?? $this->__usuario;
        $fi = $_POST['fechaI'] ?? "2024-05-17"; //date('Y-m-d');
        $ff = $_POST['fechaF'] ?? "2024-05-17"; //date('Y-m-d');
        $estatus = $_POST['estatus'] ?? "";

        $Consulta = AhorroDao::ConsultaSolicitudesTickets([
            'usuario' => $usuario,
            'fechaI' => $fi,
            'fechaF' => $ff,
            'estatus' => $estatus
        ]);

        $tabla = "";
        foreach ($Consulta as $key => $value) {
            if ($value['AUTORIZA'] == 0) {
                $autoriza = "PENDIENTE";

                $imprime = "<span class='count_top' style='font-size: 22px'><i class='fa fa-clock-o' style='color: #ac8200'></i></span>";
            } else if ($value['AUTORIZA'] == 1) {
                $autoriza = "ACEPTADO";

                $imprime = <<<html
                    <button type="button" class="btn btn-success btn-circle" onclick="impTkt('{$value['CDGTICKET_AHORRO']}');"><i class="fa fa-print"></i></button>
                html;
            } else if ($value['AUTORIZA'] == 2) {
                $imprime = '<span class="count_top" style="font-size: 22px"><i class="fa fa-close" style="color: #ac1d00"></i></span>';
                $autoriza = "RECHAZADO";
            }

            if ($value['CDGPE_AUTORIZA'] == '') {
                $autoriza_nombre = "-";
            } else if ($value['CDGPE_AUTORIZA'] != '') {
                $autoriza_nombre = $value['CDGPE_AUTORIZA'];
            }

            $tabla .= <<<html
            <tr style="padding: 0px !important;">
                <td style="padding: 0px !important;">{$value['CDGTICKET_AHORRO']} </td>
                <td style="padding: 0px !important;" width="45" nowrap=""><span class="count_top" style="font-size: 14px"> &nbsp;&nbsp;<i class="fa fa-barcode" style="color: #787b70"></i> </span>{$value['CDG_CONTRATO']} &nbsp;</td>
                <td style="padding: 0px !important;">{$value['FREGISTRO']} </td>
                <td style="padding: 0px !important;">{$value['MOTIVO']}</td>
                <td style="padding: 0px !important;"> {$autoriza}</td>
                <td style="padding: 0px !important;">{$autoriza_nombre}</td>
                <td style="padding: 0px !important;" class="center">
                {$imprime}
                </td>
            </td>
            html;
        }

        $r = ["success" => true, "datos" => $tabla];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') echo json_encode($r);
        else return $r;
    }

    public function ReimprimeTicket()
    {
        $extraHeader = <<<html
        <title>Reimprime Tickets</title>
        <link rel="shortcut icon" href="/img/logo.png">
html;

        $extraFooter = <<<html
        <script>
           $(document).ready(function(){
            $("#muestra-cupones").tablesorter();
          var oTable = $('#muestra-cupones').DataTable({
           "lengthMenu": [
                    [10, 50, -1],
                    [10, 50, 'Todos'],
                ],
                "columnDefs": [{
                    "orderable": false,
                    "targets": 0
                }],
                 "order": false
            });
            // Remove accented character from search input as well
            $('#muestra-cupones input[type=search]').keyup( function () {
                var table = $('#example').DataTable();
                table.search(
                    jQuery.fn.DataTable.ext.type.search.html(this.value)
                ).draw();
            });
            var checkAll = 0;
        });
           
            $(document).ready(function(){
            $("#muestra-cupones1").tablesorter();
          var oTable = $('#muestra-cupones1').DataTable({
           "lengthMenu": [
                    [10, 50, -1],
                    [10, 50, 'Todos'],
                ],
                "columnDefs": [{
                    "orderable": false,
                    "targets": 0
                }],
                 "order": false
            });
            // Remove accented character from search input as well
            $('#muestra-cupones1 input[type=search]').keyup( function () {
                var table = $('#example').DataTable();
                table.search(
                    jQuery.fn.DataTable.ext.type.search.html(this.value)
                ).draw();
            });
            var checkAll = 0;
        });
           
        function Reimprime_ticket(folio)
        {
              
              $('#modal_ticket').modal('show');
              document.getElementById("folio").value = folio;
             
        }
        
        function enviar_add_sol()
        {
             const showSuccess = (mensaje) => swal(mensaje, { icon: "success" } )
             
             $('#modal_ticket').modal('hide');
             swal({
                   title: "¿Está segura de continuar?",
                   text: "",
                   icon: "warning",
                   buttons: ["Cancelar", "Continuar"],
                   dangerMode: false
                   })
                   .then((willDelete) => {
                   if (willDelete) {
                        $.ajax({
                        type: 'POST',
                        url: '/Ahorro/AddSolicitudReimpresion/',
                        data: $('#Add').serialize(),
                        success: function(respuesta) {
                        if(respuesta=='1')
                        {
                           return showSuccess("Solicitud enviada a tesorería." );
                        }
                        else {
                              $('#modal_encuesta_cliente').modal('hide')
                                      swal(respuesta, {
                                      icon: "error",
                                     });
                                                
                              }
                         }
                            });
                         }
                        else {
                                    $('#modal_ticket').modal('show');
                              }
                        });
        }
        </script>
html;

        $Consulta = AhorroDao::ConsultaTickets($this->__usuario);
        $tabla = "";

        foreach ($Consulta as $key => $value) {
            $monto = number_format($value['MONTO'], 2);

            $tabla .= <<<html
                <tr style="padding: 0px !important;">
                   <td style="padding: 0px !important;">{$value['CODIGO']} </td>
                    <td style="padding: 0px !important;" width="45" nowrap=""><span class="count_top" style="font-size: 14px"> &nbsp;&nbsp;<i class="fa fa-barcode" style="color: #787b70"></i> </span>{$value['CDG_CONTRATO']} &nbsp;</td>
                    <td style="padding: 0px !important;">{$value['FECHA_ALTA']} </td>
                    <td style="padding: 0px !important;">$ {$monto}</td>
                    <td style="padding: 0px !important;">{$value['TIPO_AHORRO']}</td>
                    <td style="padding: 0px !important;">{$value['NOMBRE_CLIENTE']}</td>
                    <td style="padding: 0px !important;">{$value['CDGPE']}</td>
                    <td style="padding: 0px !important;" class="center">
                         <button type="button" class="btn btn-success btn-circle" onclick="Reimprime_ticket('{$value['CODIGO']}');"><i class="fa fa-print"></i></button>
                    </td>
                </td>
html;
        }

        $fecha_y_hora = date("Y-m-d H:i:s");



        View::set('header', $this->_contenedor->header($extraHeader));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::set('tabla', $tabla);
        View::set('fecha_actual', $fecha_y_hora);
        View::render("caja_menu_reimprime_ticket");
    }

    public function AddSolicitudReimpresion()
    {
        $solicitud = new \stdClass();

        $solicitud->_folio = MasterDom::getData('folio');
        $solicitud->_descripcion = MasterDom::getData('descripcion');
        $solicitud->_motivo = MasterDom::getData('motivo');
        $solicitud->_cdgpe = $this->__usuario;


        $id = AhorroDao::insertSolicitudAhorro($solicitud);

        return $id;
    }

    //////////////////////////////////////////////////
    public function Calculadora()
    {
        $extraHeader = <<<html
        <title>Caja Cobrar</title>
        <link rel="shortcut icon" href="/img/logo.png">
html;

        $extraFooter = <<<html
        <script>
           
        </script>
html;

        View::set('header', $this->_contenedor->header($extraHeader));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::render("caja_menu_calculadora");
    }

    public function CalculadoraView()
    {
        View::render("calculadora_view");
    }
}
