<?= $header; ?>

<?php

use App\components\AhorroMenus_MiEspacio;

[$menu, $submenu] = AhorroMenus_MiEspacio::mostrar();

?>

<div class="right_col">
    <?= $menu; ?>

    <div class="col-md-9" id="bloqueoAhorro">
        <div class="modal-content">
            <div class="modal-header" style="padding-bottom: 0px">
                <?= $submenu; ?>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Especifique el rango de fechas</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <input class="form-control" type="date" id="fechaI" value="<?= $fecha; ?>" max="<?= $fecha; ?>">
                                    <span id="availability1">Desde</span>
                                </div>
                                <div class="col-md-3">
                                    <input class="form-control" type="date" id="fechaF" value="<?= $fecha; ?>" max="<?= $fecha; ?>">
                                    <span id="availability1">Hasta</span>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-primary" type="button" id="buscar"><i class="fa fa-search"></i> Buscar</button>
                                </div>
                            </div>
                            <hr>
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover" id="historialPagos">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Referencia</th>
                                            <th>Monto</th>
                                            <th>Moneda</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal_pago" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <center>
                    <h4 class="modal-title" id="tituloModal"></h4>
                </center>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fechaPago">Fecha</label>
                                <input onkeydown="return false" type="date" class="form-control" id="fechaPago" name="fechaPago" max="<?= $fecha ?>">
                                <input type="date" id="fechaOriginalPago" name="fechaOriginalPago" hidden disabled>
                                <small class="form-text text-muted">Fecha de registro en sistema.</small>
                            </div>
                        </div>

                        <div class="col-md-4" style="display: none">
                            <div class="form-group">
                                <input type="text" class="form-control" id="usuario" name="usuario">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="medio">Medio de Registro</label>
                                <input type="text" class="form-control" id="medio" value="CAJERA" disabled>
                                <small class="form-text text-muted">Medio de registro del pago.</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="credito">Crédito</label>
                                <input type="number" class="form-control" id="credito" name="credito" disabled>
                                <small class="form-text text-muted">Número del crédito.</small>
                            </div>
                        </div>

                        <div class="col-md-10">
                            <div class="form-group">
                                <label for="nombre">Nombre del Cliente</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" disabled>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="ciclo2">Ciclo</label>
                                <input type="text" class="form-control" id="ciclo2" name="ciclo2" disabled>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="tipo">Tipo de Operación</label>
                                <select class="form-control mr-sm-3" autofocus type="select" id="tipo" name="tipo">
                                    <option value="P">PAGO</option>
                                    <option value="X">PAGO ELECTRÓNICO</option>
                                    <option value="S">SEGURO</option>
                                    <option value="M">MULTA</option>
                                    <option value="G">COMISIÓN</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="montoPago">Monto *</label>
                                <input autofocus type="number" class="form-control" id="montoPago" name="montoPago" autocomplete="off" max="10000">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group" id="grpSecuencia">
                                <label for="secuencia">Secuencia</label>
                                <input type="number" class="form-control" id="secuencia" name="secuencia" disabled>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="ejecutivo2">Nombre del Ejecutivo</label>
                                <select class="form-control mr-sm-3" autofocus type="select" id="ejecutivo2" name="ejecutivo2">
                                    <?= $status; ?>
                                </select>
                                <small class="form-text text-muted">Nombre del ejecutivo que entrega el pago.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancelar</button>
                <button type="submit" name="registrarPago" class="btn btn-primary" id="registrarPago"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar Pago</button>
                <button type="submit" name="editarPago" class="btn btn-primary" id="editarPago"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<?= $footer; ?>