<?= $header; ?>

<?php

use App\components\AhorroMenus_MiEspacio;
use App\components\BuscarCliente;

[$menu, $submenu] = AhorroMenus_MiEspacio::mostrar();
$buscarCliente = new BuscarCliente('Para poder dar de alta un nuevo contrato de una cuenta Peque, el cliente debe estar registrado en SICAFIN, si el cliente no tiene una cuenta abierta solicite el alta a su ADMINISTRADORA.');

?>

<div class="right_col">
    <?= $menu; ?>

    <div class="col-md-9">
        <form id="registroInicialAhorro" name="registroInicialAhorro">
            <div class="modal-content">
                <div class="modal-header" style="padding-bottom: 0px">
                    <div class="navbar-header card col-md-12" style="background: #2b2b2b">
                        <a class="navbar-brand">Mi espacio / Cuentas de ahorro corriente peque </a>
                    </div>

                    <?= $submenu; ?>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <?= $buscarCliente->mostrar(); ?>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="col-md-12">
                                    <p><b><span class="fa fa-sticky-note"></span> Identificación del cliente SICAFIN</b></p>
                                    <hr>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fechaRegistro">Fecha de registro del cliente SICAFIN</label>
                                        <input type="text" class="form-control" id="fechaRegistro" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="noCliente">Código de cliente SICAFIN</label>
                                        <input type="number" class="form-control" id="noCliente" readonly>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="nombre">Nombre del cliente SICAFIN</label>
                                        <input type="text" class="form-control" id="nombre" readonly>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <p><b><span class="fa fa-sticky-note"></span> Identificación del peque</b></p>
                                    <hr>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombre1">Primer nombre del peque *</label>
                                        <input type="text" class="form-control" id="nombre1" name="nombre1" oninput=camposLlenos(event) disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombre2">Segundo nombre del peque</label>
                                        <input type="text" class="form-control" id="nombre2" name="nombre2" oninput=camposLlenos(event) disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="apellido1">Primer apellido del peque *</label>
                                        <input type="text" class="form-control" id="apellido1" name="apellido1" oninput=camposLlenos(event) disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="apellido2">Segundo apellido del peque</label>
                                        <input type="text" class="form-control" id="apellido2" name="apellido2" oninput=camposLlenos(event) disabled>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label for="nombre">Sexo *</label>
                                    <div class="form-group">
                                        <div class="col-md-6">
                                            <input type="radio" name="sexo" id="sexoH" checked>
                                            <label for="sexoH">Hombre</label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="radio" name="sexo" id="sexoM">
                                            <label for="sexoM">Mujer</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="fecha_nac">Fecha de nacimiento *</label>
                                        <input type="date" class="form-control" id="fecha_nac" name="fecha_nac" min="<?= date("Y-m-d", strtotime('-18 years')) ?>" max="<?= $fecha ?>" oninput=camposLlenos(event) onkeydown=iniveCambio(event) disabled>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="edad">Edad</label>
                                        <input type="text" class="form-control" id="edad" oninput=camposLlenos(event) readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="pais">País *</label>
                                        <input type="text" class="form-control" id="pais" name="pais" value="MÉXICO" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="ciudad">Entidad de nacimiento *</label>
                                        <select class="form-control mr-sm-3" id="ciudad" name="ciudad" onchange=camposLlenos(event) disabled>
                                            <?php echo $opciones_ent; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="curp">CURP *</label>
                                        <input type="text" class="form-control" name="curp" id="curp" maxlength="18" oninput=camposLlenos(event) disabled>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <div style="display: flex; justify-content: space-between;">
                                            <div class="izquierda">
                                                <label for="direccion">Dirección *</label>
                                            </div>
                                            <div class="derecha" style="margin-left: 15px; font-size:12px">
                                                <input type="radio" name="confirmaDir" id="confirmaDir" onchange=camposLlenos(event) checked />
                                                <label for="confirmaDir"><b>Se autoriza usar la dirección del titular</b></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <textarea type="text" style="resize: none;" class="form-control" id="direccion" rows="3" cols="50" readonly>
                                                </textarea>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                            </div>
                            <div class="col-md-4">
                                <form id="registroInicialAhorro" name="registroInicialAhorro">
                                    <div class="col-md-12">
                                        <p><b><span class="fa fa-sticky-note"></span> Datos básicos de apertura para la cuenta de Ahorro Peque</b></p>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="tasa">Tasa Anual</label>
                                            <input id="tasaView" name="tasaView" class="form-control" value="<?= $tasaView ?>" disabled />
                                            <input id="tasa" name="tasa" class="form-control" value="<?= $tasa ?>" type="hidden" />
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="Fecha">Sucursal</label>
                                            <select class="form-control mr-sm-3" id="sucursal" name="sucursal">
                                                <?= $sucursales; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="ejecutivo">Ejecutivo</label>
                                            <input id="ejecutivo" name="ejecutivo" class="form-control" value="<?= $_SESSION['nombre']; ?>" disabled />
                                        </div>
                                    </div>
                                    <div class="modal-footer" style="margin-top:40px;">
                                        <button type="button" name="btnGeneraContrato" id="btnGeneraContrato" class="btn btn-primary" onclick="generaContrato(event)" style="border: 1px solid #c4a603; background: #ffffff" data-keyboard="false" disabled>
                                            <i class="fa fa-spinner" style="color: #1c4e63"></i>
                                            <span style="color: #1e283d"><b>GUARDAR DATOS Y PROCEDER AL COBRO </b></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- <div class="modal fade in" id="modal_agregar_pago" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: block; padding-right: 15px;"> -->
<div class="modal fade" id="modal_agregar_pago" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <center>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">Registro de pago por apertura y ahorro inicial cuenta corriente</h4>
                </center>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <form id="AddPagoApertura">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre_cliente">Nombre del titular</label>
                                    <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" value="<?= $Cliente[0]['NOMBRE']; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="mdlCurp">CURP del peque</label>
                                    <input type="text" class="form-control" id="mdlCurp" name="mdlCurp" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_pago">Fecha del depósito</label>
                                    <input type="text" class="form-control" id="fecha_pago" name="fecha_pago" readonly>
                                </div>
                            </div>
                            <div class="col-md-4" style="display: none!important;">
                                <div class="form-group">
                                    <label for="contrato">Número de contrato</label>
                                    <input type="text" class="form-control" id="contrato" name="contrato" aria-describedby="contrato" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="codigo_cl">Código de cliente SICAFIN</label>
                                    <input type="number" class="form-control" id="codigo_cl" name="codigo_cl" value="<?= $credito; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="nombre_ejecutivo">Nombre del ejecutivo</label>
                                    <input type="text" class="form-control" id="nombre_ejecutivo" name="nombre_ejecutivo" value="<?= $_SESSION['nombre']; ?>" readonly>
                                </div>
                            </div>
                            <hr>
                        </div>
                        <div class="row">
                            <div class="col-md-3" style="font-size: 18px; padding-top: 5px;">
                                <label style="color: #000000">Movimiento:</label>
                            </div>
                            <div class="col-md-4" style="text-align: center; font-size: 18px; padding-top: 5px;">
                                <input type="radio" name="esDeposito" onchange=cambioMovimiento(event) checked>
                                <label for="deposito">Depósito</label>
                            </div>
                            <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                                <h3>$</h3>
                            </div>
                            <div class="col-md-4" style="padding-top: 5px;">
                                <input type="number" class="form-control" id="monto" name="monto" min="1" max="100000" placeholder="0.00" style="font-size: 25px;" oninput=validaDeposito(event) onkeydown=soloNumeros(event)>
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
                            <div class="col-md-1">
                                <h4>+</h4>
                            </div>
                            <div class="col-md-7">
                                <h4>Depósito</h4>
                            </div>
                            <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                                <h4>$</h4>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" id="deposito" name="deposito" value="0.00" disabled>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-1">
                                <h4>-</h4>
                            </div>
                            <div class="col-md-7">
                                <h4>Costo Anual</h4>
                            </div>
                            <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                                <h4>$</h4>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" id="inscripcion" name="inscripcion" value="<?= $inscripcion ?>" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <h4>Saldo inicial de la cuenta ahorro corriente</h4>
                            </div>
                            <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                                <h4>$</h4>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" id="saldo_inicial" name="saldo_inicial" value="0.00" readonly>
                                <input type="hidden" class="form-control" id="sma" name="sma" value="<?= $saldoMinimo ?>" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12" style="display: flex; justify-content: center; color: red; height: 20px;">
                                <label id="tipSaldo" style="opacity:0; font-size: 18px;">El monto mínimo de apertura debe ser de $ <?= $saldoMinimo ?></label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="registraDepositoInicial" name="agregar" class="btn btn-primary" value="enviar" onclick=pagoApertura(event) disabled><span class="glyphicon glyphicon-floppy-disk"></span> Registrar depósito</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $footer; ?>