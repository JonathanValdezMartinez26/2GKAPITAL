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
                <div class="navbar-header card col-md-12" style="background: #2b2b2b">
                    <a class="navbar-brand">Mi espacio / Resumen de movimientos</a>
                </div>

                <?= $submenu; ?>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="card col-md-12">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-md-6">
                                            <p>Podrás hacer tus búsquedas por los siguientes criterios tales como fecha, numero de cliente, nombre del cliente o numero de contrato.</p>
                                            <hr>
                                        </div>
                                    </div>
                                </div>

                                <div class="card col-md-12">
                                    <form name="all" id="all" method="POST">
                                        <div class="dataTable_wrapper">
                                            <table class="table table-striped table-bordered table-hover" id="muestra-cupones">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha movimiento</th>
                                                        <th>Contrato</th>
                                                        <th>Concepto</th>
                                                        <th>Monto</th>
                                                        <th>Operación</th>
                                                        <th>Cliente</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?= $tabla; ?>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </form>
                                </div>
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
                    <form onsubmit="enviar_add_sol(); return false" id="Add">
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
                                        <option value="SE EXTRAVIO">MOTIVO 1</option>
                                        <option value="SE EXTRAVIO">MOTIVO 2</option>
                                        <option value="SE EXTRAVIO">MOTIVO 3</option>
                                        <option value="SE EXTRAVIO">MOTIVO 4</option>
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
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancelar</button>
                <button type="submit" name="agregar" class="btn btn-primary" value="enviar"><span class="glyphicon glyphicon-floppy-disk"></span> Terminar Solicitud</button>
            </div>
        </div>
    </div>
</div>

<?= $footer; ?>