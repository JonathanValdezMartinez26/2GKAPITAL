<?= $header; ?>

<?php

use App\components\AhorroMenus_MiEspacio;

[$menu, $submenu] = AhorroMenus_MiEspacio::mostrar();

?>

<div class="right_col">
    <?= $menu; ?>

    <div class="col-md-9">
        <div class="modal-content">
            <div class="modal-header" style="padding-bottom: 0px">
                <?= $submenu; ?>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="row">
                            <div class="col-md-12" style="text-align:center;">
                                <h4>Mi historial de tickets</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fechaI">Desde</label>
                                    <input type="date" class="form-control" id="fechaI" value="<?= $fecha; ?>" max="<?= $fecha; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fechaF">Hasta</label>
                                    <input type="date" class="form-control" id="fechaF" value="<?= $fecha; ?>" max="<?= $fecha; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label></label>
                                    <button type="button" class="btn btn-primary" id="buscar"><i class="fa fa-search"></i> Buscar</button>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="card col-md-12">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover" id="tblTickets">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Contrato</th>
                                            <th>Fecha cobro</th>
                                            <th>Monto</th>
                                            <th>Operación</th>
                                            <th>Cliente</th>
                                            <th>Caja</th>
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
        </div>
    </div>
</div>

<div class="modal fade" id="modal_ticket" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <center>
                    <h4 class="modal-title" id="myModalLabel">Reimpresión de tickets</h4>
                </center>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fecha">Fecha de solicitud*</label>
                                <input onkeydown="return false" type="text" class="form-control" id="fecha" name="fecha" value="<?= $fecha_actual; ?>" readonly>
                                <small id="emailHelp" class="form-text text-muted">Fecha de registro en sistema.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="folio">Folio del ticket*</label>
                                <input type="text" class="form-control" id="folio" name="folio" readonly>
                                <small id="emailHelp" class="form-text text-muted">Medio de registro del pago.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="motivo">Motivo *</label>
                                <select class="form-control mr-sm-3" autofocus type="select" id="motivo" name="motivo">
                                    <option value="TICKET EXTRAVIADO">TICKET EXTRAVIADO</option>
                                    <option value="TICKET DAÑADO">TICKET DAÑADO</option>
                                    <option value="FALLA IMPRESION">FALLA IMPRESION</option>
                                    <option value="AUDITORIA">AUDITORIA</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="descripcion">Escriba brevemente el motivo de la reimpresión *</label>
                                <textarea type="text" class="form-control" id="descripcion" name="descripcion" rows="3" cols="50"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancelar</button>
                <button type="button" class="btn btn-primary" id="regSolicitud"><i class="glyphicon glyphicon-floppy-disk"></i> Registrar Solicitud</button>
            </div>
        </div>
    </div>
</div>

<?= $footer; ?>