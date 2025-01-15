<?= $header; ?>

<?php

use App\components\AhorroMenus_MiEspacio;
use App\components\BuscarCliente;

[$menu, $submenu] = AhorroMenus_MiEspacio::mostrar();
$buscarCliente = new BuscarCliente('Para poder depositar o retirar a una cuenta Peque, el cliente debe tener una cuenta activa de Ahorro Corriente y, por lo menos, una cuenta Peque registrada. Si el cliente no tiene una cuenta de ahorro abierta <a href="/Ahorro/ContratoCuentaCorriente/" target="_blank">presione aquí</a>.');

?>


<div class="right_col">
    <?= $menu; ?>

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
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="contrato">Nombre del cliente peque *</label>
                                    <select class="form-control" id="contrato" name="contrato" disabled>
                                    </select>
                                    <input type="text" id="contratoSel" value="<?= $contratoSel; ?>" hidden>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="curp">CURP</label>
                                    <input type="text" class="form-control" id="curp" name="curp" readonly>
                                </div>
                            </div>
                            <div class="col-md-4" style="display: none;">
                                <div class="form-group">
                                    <label for="nombre_ejecutivo">Nombre de la cajera que captura el pago</label>
                                    <input type="text" class="form-control" id="nombre_ejecutivo" name="nombre_ejecutivo" value="<?= $_SESSION['nombre'] ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="nombre">Contrato del peque</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cliente">Código de cliente SICAFIN (Tutor)</label>
                                    <input type="number" class="form-control" id="cliente" name="cliente" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_pago">Fecha del movimiento</label>
                                    <input type="text" class="form-control" id="fecha_pago" name="fecha_pago" value="<?= $fecha; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <hr>
                            </div>
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
                                <input type="number" class="form-control" id="monto" name="monto" placeholder="0.00" style="font-size: large; font-size: 25px;" oninput=validaDeposito(event) onkeydown=soloNumeros(event) disabled>
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
                                <label id="tipSaldo" style="opacity:0; font-size: 18px;">No se puede realizar un retiro mayor al saldo disponible.</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="btnRegistraOperacion" name="agregar" class="btn btn-primary" value="enviar" onclick=registraOperacion(event) disabled><span class="glyphicon glyphicon-floppy-disk"></span> Procesar Transaccion</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $footer; ?>