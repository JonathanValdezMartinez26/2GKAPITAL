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
                    <?= $submenu; ?>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_agregar_horario">
                                <i class="fa fa-plus"></i> Nueva Activación
                            </button>
                            <hr style="border-top: 1px solid #787878; margin-top: 5px;">
                        </div>
                        <div class="row">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover" id="sucursalesActivas">
                                    <thead>
                                        <tr>
                                            <th>Fecha de Registro</th>
                                            <th>Cod Sucursal</th>
                                            <th>Nombre Sucursal</th>
                                            <th>Cod Cajera</th>
                                            <th>Nombre Cajera</th>
                                            <th>Hora Apertura</th>
                                            <th>Hora Cierre</th>
                                            <th>Monto Mínimo</th>
                                            <th>Monto Máximo</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?= $tabla; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modal_agregar_horario" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <center>
                    <h4 class="modal-title" id="myModalLabel">Activar Modulo de Ahorro para Sucursal</h4>
                </center>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <form id="datos" onsubmit=noSUBMIT(event)>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="fecha_registro">Fecha de Registro</label>
                                    <input class="form-control" id="fecha_registro" name="fecha_registro" readonly value="<?= $fecha; ?>">
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="sucursal">Sucursal *</label>
                                    <select class="form-control" id="sucursal" name="sucursal" onchange=cambioSucursal()>
                                        <?= $opcSucursales; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="sucursal">Cajera *</label>
                                    <select class="form-control" id="cajera" name="cajera" onchange=cambioCajera() disabled>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Horario de Apertura *</label>
                                    <select class="form-control" id="horaA" name="horaA" disabled>
                                        <option value="09:00:00">09:00 a.m</option>
                                        <option value="10:00:00">10:00 a.m</option>
                                        <option value="11:00:00">11:00 a.m</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="horaC">Horario de Cierre *</label>
                                    <select class="form-control" id="horaC" name="horaC" disabled>
                                        <option value="15:00:00">03:00 p.m</option>
                                        <option value="16:00:00">04:00 p.m</option>
                                        <option value="17:00:00">05:00 p.m</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <small id="emailHelp" class="form-text text-muted"><b>ATENCIÓN:</b> La cajera no tendrá acceso antes del horario de apertura y después del horario de cierre solo podrá acceder al arqueo y cierre de día.</small>
                            </div>
                        </div>
                        <div class="row" style="margin-top:20px">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="montoMin">Monto mínimo en caja *</label>
                                    <input type="number" class="form-control" id="montoMin" name="montoMin" placeholder="0.00" min="0" max="100000" onkeydown=soloNumeros(event) onblur=validaMaxMin() oninput=cambioMonto() disabled />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hora">Monto máximo en caja *</label>
                                    <input type="number" class="form-control" id="montoMax" name="montoMax" placeholder="0.00" min="0" max="100000" onkeydown=soloNumeros(event) onblur=validaMaxMin() oninput=cambioMonto() onchange=cambioMonto() disabled />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <small id="emailHelp" class="form-text text-muted"><b>ATENCIÓN:</b> La cajera no podrá realizar retiros una vez que el monto este en el mínimo, solo podrá realizar retiros express, que deben ser aprobados por tesorería.</small>
                            </div>
                        </div>
                        <div class="row" style="margin-top:20px">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="saldo">Saldo inicial *</label>
                                    <input type="number" class="form-control" id="saldo" name="saldo" placeholder="0.00" min="0" max="500000" onkeydown=soloNumeros(event) onblur=validaMaxMin() oninput=cambioMonto() disabled />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancelar</button>
                <button class="btn btn-primary" id="guardar" onclick=activarSucursal() disabled><span class="glyphicon glyphicon-floppy-disk"></span> Guardar Registro</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_configurar_montos" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <center>
                    <h4 class="modal-title" id="myModalLabel">Configurar montos de sucursal</h4>
                </center>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <form id="configMontos" onsubmit=noSUBMIT(event)>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codSucMontos">Código Sucursal</label>
                                    <input name="codSucMontos" id="codSucMontos" class="form-control" readonly />
                                    <input name="codigo" id="codigo" class="form-control" type="hidden" />
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="nomSucMontos">Nombre Sucursal</label>
                                    <input name="nomSucMontos" id="nomSucMontos" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="minimoApertura">Monto mínimo apertura</label>
                                    <div class="row">
                                        <div class="col-md-1">
                                            <span style="font-size: x-large;">$</span>
                                        </div>
                                        <div class="col-md-10">
                                            <input class="form-control" id="minimoApertura" name="minimoApertura" placeholder="0.00" style="font-size: 25px;" onkeydown=soloNumeros(event) oninput=validaMontoMinMax(event) />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="maximoApertura">Monto máximo apertura</label>
                                    <div class="row">
                                        <div class="col-md-1">
                                            <span style="font-size: x-large;">$</span>
                                        </div>
                                        <div class="col-md-10">
                                            <input class="form-control" id="maximoApertura" name="maximoApertura" placeholder="0.00" style="font-size: 25px;" onkeydown=soloNumeros(event) oninput=validaMontoMinMax(event) />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancelar</button>
                <button class="btn btn-primary" onclick=guardarMontos()><span class="glyphicon glyphicon-floppy-disk"></span> Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<?= $footer; ?>