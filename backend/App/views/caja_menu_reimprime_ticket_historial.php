<?= $header; ?>

<?php

use App\components\AhorroMenus_MiEspacio;

[$menu, $submenu] = AhorroMenus_MiEspacio::mostrar();

?>

<div class="right_col">
    <?= $menu; ?>

    <div class="col-md-9">
        <form id="registroInicialAhorro" name="registroInicialAhorro">
            <div class="modal-content">
                <div class="modal-header" style="padding-bottom: 0px">
                    <?= $submenu; ?>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fechaI">Fecha inical</label>
                                    <input type="date" class="form-control" id="fechaI" name="fechaI" value="<?= $fecha; ?>" onchange="validaFIF('fechaI', 'fechaF')" max="<?= $fecha; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fechaF">Fecha final</label>
                                    <input type="date" class="form-control" id="fechaF" name="fechaF" value="<?= $fecha; ?>" onchange="validaFIF('fechaI', 'fechaF')" max="<?= $fecha; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="estatus">Estatus</label>
                                    <select class="form-control" id="estatus" name="estatus">
                                        <option value="">TODOS</option>
                                        <option value="0">PENDIENTE</option>
                                        <option value="1">RECHAZADA</option>
                                        <option value="2">APROBADA</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group" style="margin-top: 25px;">
                                    <button type="button" class="btn btn-primary" onclick=buscar()><i class="fa fa-search"></i> Buscar</button>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group" style="margin-top: 25px;">
                                    <button id="btnExportaExcel" type="button" class="btn btn-success btn-circle" onclick=imprimeExcel()><i class="fa fa-file-excel-o"></i><b> Exportar a Excel</b></button>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-8">
                                <p>Podrás encontrar tus solicitudes a traves del historial, en el que se marcaran con tres estatus: <br> <span class="count_top" style="font-size: 18px"><i class="fa fa-clock-o" style="color: #ac8200"></i></span> PENDIENTE (Está en validación). <br><span class="count_top" style="font-size: 18px"><i class="fa fa-close" style="color: #ac1d00"></i></span> RECHAZADA (Tú solicitud fue rechazada por tesorería). <br><span class="count_top" style="font-size: 18px"><i class="fa fa-print" style="color: #26b99a"></i></span> APROBADA (Puedes imprimir tu ticket una vez más).</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <form name="all" id="all" method="POST">
                                <div class="dataTable_wrapper">
                                    <table class="table table-striped table-bordered table-hover" id="solicitudes">
                                        <thead>
                                            <tr>
                                                <th>ID Ticket</th>
                                                <th>Contrato</th>
                                                <th>Fecha Solicitud</th>
                                                <th>Motivo</th>
                                                <th>Estatus</th>
                                                <th>Autoriza</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?= $tabla ?>
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
                                    <input type="text" class="form-control" id="folio" readonly>
                                    <small id="emailHelp" class="form-text text-muted">Medio de registro del pago.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo">Motivo *</label>
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
                                    <label for="tipo">Escriba brevemente el motivo de la reimpresión *</label>
                                    <textarea type="text" class="form-control" id="direccion" name="direccion" rows="3" cols="50"></textarea>
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