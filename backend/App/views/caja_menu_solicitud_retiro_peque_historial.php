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
                    <a class="navbar-brand">Mi espacio / Procesar solicitudes de retiro</a>
                </div>

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
                                    <option value="0">REGISTRADO</option>
                                    <option value="1">APROBADO</option>
                                    <option value="2">RECHAZADO</option>
                                    <option value="3">ENTREGADO</option>
                                    <option value="4">DEVUELTO</option>
                                    <option value="5">CANCELADO</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="tipo">Tipo</label>
                                <select class="form-control" id="tipo" name="tipo">
                                    <option value="">TODOS</option>
                                    <option value="1">EXPRESS</option>
                                    <option value="2">PROGRAMADO</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group" style="margin-top: 25px;">
                                <button type="button" class="btn btn-primary" onclick=buscar()><i class="fa fa-search"></i> Buscar</button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group" style="margin-top: 25px;">
                                <button id="btnExportaExcel" type="button" class="btn btn-success btn-circle" onclick=imprimeExcel()><i class="fa fa-file-excel-o"></i><b> Exportar a Excel</b></button>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <form name="all" id="all" method="POST">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover" id="hstSolicitudes">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Nombre cliente</th>
                                            <th>Código cliente</th>
                                            <th>Fecha actualización</th>
                                            <th>Monto solicitado</th>
                                            <th>Estatus</th>
                                            <th>Fecha entrega</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?= $tabla; ?>
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

<?= $footer; ?>