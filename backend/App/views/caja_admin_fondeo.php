<?= $header; ?>

<?php

use App\components\AhorroMenus_AdminSuc;

[$menu, $submenu] = AhorroMenus_AdminSuc::mostrar();

?>

<div class="right_col">
    <?= $menu; ?>

    <div class="col-md-9">
        <form id="datos" onsubmit=noSUBMIT()>
            <div class="modal-content">
                <div class="modal-header" style="padding-bottom: 0px">
                    <?= $submenu; ?>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                <p>Para poder fondear una sucursal, esta debe estar habilitada para realizar transacciones de ahorro. Para habilitar una sucursal para transacciones de ahorro, comuníquese con el ADMINISTRADOR</p>
                                <hr>
                            </div>
                            <div class="col-md-4">
                                <label for="sucursalBuscada">Código de sucursal</label>
                                <input type="text" onkeypress=validarYbuscar(event) class="form-control" id="sucursalBuscada" name="sucursalBuscada" placeholder="000" required>
                            </div>

                            <div class="col-md-2" style="padding-top: 25px">
                                <button type="button" class="btn btn-primary" onclick="buscar()">
                                    <i class="fa fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="codigoSuc">Código de sucursal</label>
                                    <input type="text" class="form-control" id="codigoSuc" name="codigoSuc" readonly>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="nombreSuc">Nombre de la sucursal</label>
                                    <input type="text" class="form-control" id="nombreSuc" name="nombreSuc" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fechaFondeo">Fecha del fondeo</label>
                                    <input type="text" class="form-control" id="fechaFondeo" name="fechaFondeo" value="<?= $fecha; ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="codigoCajera">Código de cajera</label>
                                    <input type="text" class="form-control" id="codigoCajera" name="codigoCajera" readonly>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="nombreCajera">Nombre cajera</label>
                                    <input type="text" class="form-control" id="nombreCajera" name="nombreCajera" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fechaCierre">Fecha del ultimo cierre</label>
                                    <input type="text" class="form-control" id="fechaCierre" name="fechaCierre" value="" readonly>
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
                            <div class="col-md-4" style="text-align: center; font-size: 18px; padding-top: 5px;">
                                <input type="radio" name="esFondeo" id="esFondeo" checked>
                                <label for="esFondeo">Fondeo</label>
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
                        <div class="row">
                            <div class="col-md-8" style="display: flex; justify-content: flex-start;">
                                <h4>Saldo actual de la sucursal</h4>
                            </div>
                            <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                                <h4>$</h4>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" id="saldoActual" name="saldoActual" value="0.00" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-1">
                                <h4 id="simboloOperacion">+</h4>
                            </div>
                            <div class="col-md-7" style="display: flex; justify-content: flex-start;">
                                <h4 id="descOperacion">Fondeo</h4>
                            </div>
                            <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                                <h4>$</h4>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" id="montoOperacion" name="montoOperacion" value="0.00" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8" style="display: flex; justify-content: flex-start;">
                                <h2>Saldo final de la sucursal</h2>
                            </div>
                            <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                                <h4>$</h4>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" id="saldoFinal" name="saldoFinal" value="0.00" readonly>
                            </div>
                            <div class="col-md-12" style="display: flex; justify-content: center; color: red; height: 30px;">
                                <label id="tipSaldo" style="font-size: 18px;"></label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button id="btnFondear" class="btn btn-primary" onclick=fondear(event) disabled><span class="glyphicon glyphicon-floppy-disk"></span> Confirmar fondeo</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $footer; ?>