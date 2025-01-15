<?= $header; ?>

<?php

use App\components\AhorroMenus_MiEspacio;
use App\components\BuscarCliente;

[$menu, $submenu] = AhorroMenus_MiEspacio::mostrar();
$buscarCliente = new BuscarCliente('Para realizar un retiro es necesario que el cliente tenga una cuenta ahorro corriente activa, de lo contrario, es necesaria la creación de una a través de la opción: <a href="/Ahorro/ContratoCuentaCorriente/" target="_blank">Nuevo Contrato</a>.');

?>

<div class="right_col">
    <?= $menu; ?>

    <div class="col-md-9">
        <form id="registroOperacion" name="registroOperacion">
            <div class="modal-content">
                <div class="modal-header" style="padding-bottom: 0px">
                    <?= $submenu; ?>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <?= $buscarCliente->mostrar(); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre del cliente</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="curp">CURP</label>
                                    <input type="text" class="form-control" id="curp" name="curp" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_retiro">Fecha del retiro</label>
                                    <input type="text" class="form-control" id="fecha_retiro" name="fecha_retiro" value="<?= $fecha; ?>" readonly>
                                    <input type="date" class="form-control" id="fecha_retiro_hide" name="fecha_retiro_sel" style="display: none" min="<?= $fechaInput; ?>" max="<?= $fechaInputMax; ?>" value="<?= $fechaInput; ?>" oninput=pasaFecha(event) />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="contrato">Número de contrato</label>
                                    <input type="text" class="form-control" id="contrato" name="contrato" aria-describedby="contrato" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cliente">Código de cliente SICAFIN</label>
                                    <input type="number" class="form-control" id="cliente" name="cliente" readonly>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="nombre_ejecutivo">Nombre del ejecutivo</label>
                                    <input type="text" class="form-control" id="nombre_ejecutivo" name="nombre_ejecutivo" value="<?= $_SESSION['nombre'] ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <hr>
                        </div>
                        <div class="row">
                            <div class="col-md-2" style="font-size: 18px; padding-top: 5px;">
                                <label style="color: #000000">Movimiento:</label>
                            </div>
                            <div class="col-md-2" style="text-align: center; font-size: 18px; padding-top: 5px;">
                                <input type="radio" name="tipoRetiro" id="express" onchange=cambioMovimiento(event) checked disabled>
                                <label for="express">Express</label>
                            </div>
                            <div class="col-md-3" style="text-align: center; font-size: 18px; padding-top: 5px;">
                                <input type="radio" name="tipoRetiro" id="programado" onchange=cambioMovimiento(event) disabled>
                                <label for="programado">Programado</label>
                            </div>
                            <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                                <h3>$</h3>
                            </div>
                            <div class="col-md-4" style="padding-top: 5px;">
                                <input type="number" class="form-control" id="monto" name="monto" min="<?= $montoMinimoRetiro ?>" max="<?= $montoMaximoRetiro ?>" placeholder="0.00" style="font-size: 25px;" oninput=validaMonto(event) onkeydown=soloNumeros(event) onblur=valSalMin() disabled>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="text" class="form-control" id="monto_letra" name="monto_letra" style="border: 1px solid #000000; text-align: center; font-size: 25px;" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12" style="text-align:center;">
                                <hr>
                                <h3 style="color: #000000">Resumen de movimientos</h3>
                                <br>
                            </div>
                        </div>
                        <div class="row" style="display: none!important;">
                            <div class="col-md-8" style="display: flex; justify-content: flex-start;">
                                <h4>Saldo actual cuenta ahorro corriente</h4>
                            </div>
                            <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                                <h4>$</h4>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" id="saldoActual" name="saldoActual" value="0.00" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-1">
                                <h4 id="simboloOperacion">+</h4>
                            </div>
                            <div class="col-md-7" style="display: flex; justify-content: flex-start;">
                                <h4 id="descOperacion">Retiro de cuenta ahorro corriente</h4>
                            </div>
                            <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                                <h4>$</h4>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" id="montoOperacion" name="montoOperacion" value="0.00" readonly>
                            </div>
                        </div>
                        <div class="row" style="display: none;">
                            <div class="col-md-8" style="display: flex; justify-content: flex-start;">
                                <h2>Saldo final cuenta ahorro corriente</h2>
                            </div>
                            <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                                <h4>$</h4>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" id="saldoFinal" name="saldoFinal" value="0.00" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12" style="display: flex; justify-content: center; color: red; height: 30px;">
                                <label id="tipSaldo" style="opacity:0; font-size: 18px;">El monto a retirar no puede ser mayor al saldo de la cuenta.</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="btnRegistraOperacion" name="agregar" class="btn btn-primary" value="enviar" onclick=registraSolicitud(event) disabled><span class="glyphicon glyphicon-floppy-disk"></span> Registrar solicitud</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $footer; ?>