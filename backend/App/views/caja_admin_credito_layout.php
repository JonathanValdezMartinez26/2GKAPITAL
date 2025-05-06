<?= $header; ?>

<?php

use App\components\AhorroMenus_AdminSuc;

[$menu, $submenu] = AhorroMenus_AdminSuc::mostrar();

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
                                <div class="col-md-3" style="display: flex; justify-content: space-between;">
                                    <button class="btn btn-primary" type="button" id="buscar"><i class="fa fa-search">&nbsp;</i>Buscar</button>
                                    <button class="btn btn-success" type="button" id="exportar"><i class="fa fa-file-excel-o">&nbsp;</i>Exportar</button>
                                </div>
                            </div>
                            <hr>
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover" id="historialPagos">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Tipo</th>
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

<?= $footer; ?>