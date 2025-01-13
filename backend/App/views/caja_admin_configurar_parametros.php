<?= $header; ?>

<?php

use App\components\AhorroMenus_AdminSuc;

[$menu, $submenu] = AhorroMenus_AdminSuc::mostrar();

?>

<div class="right_col">
    <?= $menu; ?>
    <div class="col-md-9">
        <form id="registroOperacion" name="registroOperacion">
            <div class="modal-content">
                <div class="modal-header" style="padding-bottom: 0px">
                    <div class="navbar-header card col-md-12" style="background: #2b2b2b">
                        <a class="navbar-brand">Admin sucursales / Configuración de módulo para Ahorro / Parametros de operacion</a>
                    </div>

                    <?= $submenu; ?>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <form id="datos" onsubmit="noSUBMIT(event)">
                                <br>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="tasa_anual">TASA ANUAL, CONTRATO AHORRO </label>
                                                <input type="text" class="form-control" id="tasa_anual" name="tasa_anual" placeholder="0.00" min="0" max="100000" onkeydown="soloNumeros(event)" onblur="validaMaxMin()" oninput="cambioMonto()" disabled="" value="5% (CINCO PORCIENTO ANUAL)">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <button type="button" class="btn btn-success btn-circle" onclick="EditarTasa();"><i class="fa fa-edit"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="monto_minimo_apertura">PRECIO POR MANEJO DE CUENTA</label>
                                                <input type="text" class="form-control" id="monto_minimo_apertura" name="monto_minimo_apertura" placeholder="0.00" min="0" max="100000" onkeydown="soloNumeros(event)" onblur="validaMaxMin()" oninput="cambioMonto()" readonly value="200.00 (DOSCIENTOS PESOS 00/100 M.N)">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <button type="button" class="btn btn-success btn-circle" onclick="EditarTasa();"><i class="fa fa-edit"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="monto_minimo_apertura">MONTO MAXIMO A DEPOSITAR POR TRANSACCIÓN</label>
                                                <input type="text" class="form-control" id="monto_minimo_apertura" name="monto_minimo_apertura" placeholder="0.00" min="0" max="1000000" value="1,000,000.00 (UN MILLON 00/100 M.N)" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <button type="button" class="btn btn-success btn-circle" onclick="EditarTasa();"><i class="fa fa-edit"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="monto_minimo_apertura">MONTO MÍNIMO, CONTRATO AHORRO </label>
                                                <input type="text" class="form-control" id="monto_minimo_apertura" name="monto_minimo_apertura" placeholder="0.00" min="0" max="100000" onkeydown="soloNumeros(event)" onblur="validaMaxMin()" oninput="cambioMonto()" disabled="" value="300.00 (TRESCIENTOS PESOS 100/00 M.N)">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <button type="button" class="btn btn-success btn-circle" onclick="EditarTasa();"><i class="fa fa-edit"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="monto_minimo_apertura">MONTO MAXIMO A RETIRAR POR CLIENTE AL DÍA</label>
                                                <input type="text" class="form-control" id="monto_minimo_apertura" name="monto_minimo_apertura" placeholder="0.00" min="0" max="100000" onkeydown="soloNumeros(event)" onblur="validaMaxMin()" oninput="cambioMonto()" readonly value="50,000.00 (CINCUENTA MIL PESOS 00/100 M.N)">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <button type="button" class="btn btn-success btn-circle" onclick="EditarTasa();"><i class="fa fa-edit"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <small id="emailHelp" class="form-text text-muted"><b>ATENCIÓN:</b> Al momento de modificar la tasa anual o el monto mínimo, estos cambios seran inmediatos en la creación de un nuevo contrato.</small>
                                    </div>
                                </div>
                                <br>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $footer; ?>