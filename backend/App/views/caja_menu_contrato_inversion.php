<?= $header; ?>

<?php

use App\components\AhorroMenus_MiEspacio;
use App\components\BuscarCliente;

[$menu, $submenu] = AhorroMenus_MiEspacio::mostrar();
$buscarCliente = new BuscarCliente('Para hacer la apertura de una cuenta de Inversión, el cliente debe tener una cuenta activa de Ahorro Corriente, si el cliente no tiene una cuenta abierta <a href="/Ahorro/ContratoCuentaCorriente/" target="_blank">presione aquí</a>.');

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
                                    <label for="fecha_pago">Fecha de apertura inversión</label>
                                    <input type="text" class="form-control" id="fecha_pago" name="fecha_pago" value="<?= $fecha; ?>" readonly>
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
                                    <label for="nombre_ejecutivo">Comisión ejecutivo *</label>
                                    <select class="form-control mr-sm-3" id="nombre_ejecutivo" name="nombre_ejecutivo" readonly>
                                        <?php echo $ejecutivos; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <hr>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="monto">Monto a invertir *</label>
                                    <div class="row">
                                        <div class="col-md-1">
                                            <span style="font-size: x-large;">$</span>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="number" class="form-control" id="monto" name="monto" min="1" max="4000000" placeholder="0.00" style="font-size: 25px;" oninput=validaDeposito(event) onkeydown=soloNumeros(event) onblur=validaBlur(event) disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="plazo">Plazo *</label>
                                    <select class="form-control" id="plazo" name="plazo" onchange=habilitaBoton(event) disabled>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="rendimiento">Rendimiento</label>
                                    <div class="row">
                                        <div class="col-md-1">
                                            <span style="font-size: x-large;">$</span>
                                        </div>
                                        <div class="col-md-10">
                                            <input class="form-control" id="rendimiento" name="rendimiento" placeholder="0.00" style="font-size: 25px;" disabled />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="renovacion">Acción al vencimiento *</label>
                                    <select class="form-control" id="renovacion" name="renovacion" disabled>
                                        <option value="D">Depositar a cuenta</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="display: flex; justify-content: center; font-size: medium; height: 30px;">
                            <span id="leyendaRendimiento"></span>
                        </div>
                        <div class="row" style="display: flex; align-items: center; justify-content: center; font-size: medium">
                            <input type="text" class="form-control" id="monto_letra" name="monto_letra" style="border: 1px solid #000000; text-align: center; font-size: 25px;" readonly>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12" style="text-align:center;">
                                <h3 style="color: #000000">Resumen de movimientos</h3>
                            </div>
                        </div>
                        <div class="row" style="display: none!important;">
                            <div class="col-md-8" style="display: flex; justify-content: flex-start;">
                                <h4>Saldo actual ahorro cuenta corriente</h4>
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
                                <h4 id="simboloOperacion">-</h4>
                            </div>
                            <div class="col-md-7" style="display: flex; justify-content: flex-start;">
                                <h4 id="descOperacion">Transferencia a cuenta de inversión</h4>
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
                                <h2>Saldo final ahorro cuenta corriente</h2>
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
                                <label id="tipSaldo" style="opacity:0; font-size: 18px;">El monto a invertir no puede ser mayor al saldo de la cuenta.</label>
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


<!-- <div class="modal fade in" id="modal_actualiza_inversion" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: block; padding-right: 15px;"> -->
<div class="modal fade" id="modal_actualiza_inversion" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <center>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">Actualización monto de inversión</h4>
                </center>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <form id="AddPagoApertura">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="modal_act_cl">Código de cliente SICAFIN</label>
                                    <input type="number" class="form-control" id="modal_act_cl" name="modal_act_cl" value="<?php echo $credito; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="modal_act_contrato">Número de contrato</label>
                                    <input type="text" class="form-control" id="modal_act_contrato" name="modal_act_contrato" readonly>
                                    <input type="hidden" class="form-control" id="modal_act_codigo_inv" name="modal_act_codigo_inv" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="modal_act_nombre_cliente">Nombre del cliente</label>
                                    <input type="text" class="form-control" id="modal_act_nombre_cliente" name="modal_act_nombre_cliente" value="<?php echo $Cliente[0]['NOMBRE']; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="modal_act_inv_actual">Monto Invertido</label>
                                    <input type="text" class="form-control" id="modal_act_inv_actual" name="modal_act_inv_actual" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="modal_act_tasa_actual">Tasa Anual</label>
                                    <input type="text" class="form-control" id="modal_act_tasa_actual" name="modal_act_tasa_actual" readonly>
                                    <input type="hidden" class="form-control" id="modal_act_id_tasa_actual" name="modal_act_id_tasa_actual" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="modal_act_plazo">Plazo</label>
                                    <input type="text" class="form-control" id="modal_act_plazo_completo" name="modal_act_plazo_completo" readonly>
                                    <input type="hidden" class="form-control" id="modal_act_plazo" name="modal_act_plazo" readonly>
                                    <input type="hidden" class="form-control" id="modal_act_periodicidad" name="modal_act_periodicidad" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="modal_act_tasa_actual">Saldo Ahorro</label>
                                    <input type="text" class="form-control" id="modal_act_saldo_ahorro" name="modal_act_saldo_ahorro" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="modal_act_fApertura">Fecha de apertura</label>
                                    <input type="text" class="form-control" id="modal_act_fApertura" name="modal_act_fApertura" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="modal_act_fVencimiento">Fecha de Vencimiento</label>
                                    <input type="text" class="form-control" id="modal_act_fVencimiento" name="modal_act_fVencimiento" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="modal_act_fActualizacion">Ultima Actualización</label>
                                    <input type="text" class="form-control" id="modal_act_fActualizacion" name="modal_act_fActualizacion" readonly>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-3" style="font-size: 18px; padding-top: 5px;">
                                <label style="color: #000000">Movimiento:</label>
                            </div>
                            <div class="col-md-4" style="text-align: center; font-size: 18px; padding-top: 5px;">
                                <input type="radio" name="esDeposito" checked>
                                <label for="deposito">Transferencia</label>
                            </div>
                            <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                                <h3>$</h3>
                            </div>
                            <div class="col-md-4" style="padding-top: 5px;">
                                <input type="number" class="form-control" id="modal_act_monto" name="modal_act_monto" min="1" max="1000000" placeholder="0.00" style="font-size: 25px;" oninput=validaNuevaTransferencia(event) onkeydown=soloNumeros(event)>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="text" class="form-control" id="modal_act_monto_letra" name="modal_act_monto_letra" style="border: 1px solid #000000; text-align: center; font-size: 25px;" readonly>
                            </div>
                        </div>

                        <div class="row" style="padding-top: 5px;">
                            <div class="col-md-3">
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="modal_act_plazo_nvo">Nuevo plazo</label>
                                    <select class="form-control" id="modal_act_plazo_nvo" name="modal_act_plazo_nvo" onchange=cambioNvoPlazo(event) disabled>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="modal_act_tasa">Nueva tasa anual</label>
                                    <input class="form-control" id="modal_act_tasa" name="modal_act_tasa" value="0.00" readonly>
                                    <input type="hidden" class="form-control" id="modal_act_id_tasa" name="modal_act_id_tasa" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
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
                            <div class="col-md-8">
                                <h4>Monto invertido</h4>
                            </div>
                            <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                                <h4>$</h4>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" id="modal_act_invertido" name="modal_act_invertido" value="0.00" disabled>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-1">
                                <h4>+</h4>
                            </div>
                            <div class="col-md-7">
                                <h4>Monto a transferir</h4>
                            </div>
                            <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                                <h4>$</h4>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" id="modal_act_transferir" name="modal_act_transferir" value="0.00" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-1">
                                <h4>+</h4>
                            </div>
                            <div class="col-md-7">
                                <h4>Rendimiento generado</h4>
                            </div>
                            <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                                <h4>$</h4>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" id="modal_act_rendimiento" name="modal_act_rendimiento" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <h4>Nuevo monto de inversión</h4>
                            </div>
                            <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                                <h4>$</h4>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" id="modal_act_total" name="modal_act_total" value="0.00" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12" style="display: flex; justify-content: center; color: red; height: 20px;">
                                <label id="modal_act_tipSaldo" style="opacity:0; font-size: 18px;"></label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="registraCambioInversion" name="agregar" class="btn btn-primary" value="enviar" onclick=actaulizaInversion(event) disabled><span class="glyphicon glyphicon-floppy-disk"></span> Actualiza inversión</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $footer; ?>