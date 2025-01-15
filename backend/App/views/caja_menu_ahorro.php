<?= $header; ?>

<?php

use App\components\AhorroMenus_MiEspacio;
use App\components\BuscarCliente;

[$menu, $submenu] = AhorroMenus_MiEspacio::mostrar();
$buscarCliente = new BuscarCliente('Para realizar un movimiento es necesario que el cliente tenga una cuenta ahorro corriente activa, de lo contrario, es necesaria la creación de una a través de la opción: <a href="/Ahorro/ContratoCuentaCorriente/" target="_blank">Nuevo Contrato</a>.');

?>

<div class="right_col">
    <?= $menu ?>

    <div class="col-md-9" id="bloqueoAhorro">
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
                                    <label for="fecha_pago">Fecha del movimiento</label>
                                    <input type="text" class="form-control" id="fecha_pago" name="fecha_pago" value="<?= $fecha; ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="contrato">Número de contrato</label>
                                    <input type="text" class="form-control" id="contrato" name="contrato" aria-describedby="contrato" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="cliente">Código de cliente SICAFIN</label>
                                    <input type="number" class="form-control" id="cliente" name="cliente" readonly>
                                </div>
                            </div>
                            <div class="col-md-7" id="contenedor_apoderado" style="opacity: 0;">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="esTitular">¿Es el titular?</label>
                                        <input type="checkbox" class="form-control" id="esTitular" name="esTitular" checked onchange=validaApoderado() />
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <label for="apoderado">Apoderado</label>
                                        <select name="apoderado" id="apoderado" class="form-control" onchange=cambioApoderado() disabled>
                                        </select>
                                        <select name="tipoApoderado" id="tipoApoderado" hidden>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5" style="display: none;">
                                <div class="form-group">
                                    <label for="nombre_ejecutivo">Nombre de la cajera que captura el deposito/retiro *</label>
                                    <input type="text" class="form-control" id="nombre_ejecutivo" name="nombre_ejecutivo" value="<?= $_SESSION['nombre'] ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <hr>
                        </div>
                        <div class="row">
                            <div class="col-md-3" style="font-size: 18px; padding-top: 5px;">
                                <label style="color: #000000">Movimiento:</label>
                            </div>
                            <div class="col-md-2" style="text-align: center; font-size: 18px; padding-top: 5px;">
                                <input type="radio" name="esDeposito" id="deposito" onchange=cambioMovimiento(event) disabled>
                                <label for="deposito">Depósito</label>
                            </div>
                            <div class="col-md-2" style="text-align: center; font-size: 18px; padding-top: 5px;">
                                <input type="radio" name="esDeposito" id="retiro" onchange=cambioMovimiento(event) disabled>
                                <label for="retiro">Retiro</label>
                            </div>
                            <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                                <h3>$</h3>
                            </div>
                            <div class="col-md-4" style="padding-top: 5px;">
                                <input type="number" class="form-control" id="monto" name="monto" min="1" placeholder="0.00" style="font-size: 25px;" oninput=validaMonto(event) onkeydown=soloNumeros(event) disabled>
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
                        <div class="row" style="display: none!important;;">
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
                                <h4 id="descOperacion">Depósito a cuenta ahorro corriente</h4>
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
                            <button type="button" id="btnRegistraOperacion" name="agregar" class="btn btn-primary" value="enviar" onclick=registraOperacion(event) disabled><span class="glyphicon glyphicon-floppy-disk"></span> Registrar transacción</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $footer; ?>