<?= $header; ?>

<?php

use App\components\AhorroMenus_AdminSuc;

[$menu, $submenu] = AhorroMenus_AdminSuc::mostrar();

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
                    <div class="col-md-12">
                        <div class="row">
                            <div class="card col-md-12">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="fechaI">Desde *</label>
                                            <input type="date" class="form-control" id="fechaI" name="fechaI" value="<?= $fechaI; ?>" min="2024-01-01" max="<?= $fechaF; ?>" onchange=validaFechas()>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="fechaF">Hasta *</label>
                                            <input type="date" class="form-control" id="fechaF" name="fechaF" value="<?= $fechaF; ?>" min="2024-01-01" max="<?= $fechaF; ?>" onchange=validaFechas()>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="sucursal">Sucursal *</label>
                                            <select class="form-control" id="sucursal" name="sucursal">
                                                <option value="0">TODAS LAS SUCURSALES</option>
                                                <?= $opcSucursales; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3" style="padding-top: 25px">
                                        <button class="btn btn-primary" onclick=buscarFondeos()>
                                            <i class="fa fa-search"></i> Buscar
                                        </button>
                                    </div>
                                </div>
                                <br>
                                <button id="export_excel_consulta" class="btn btn-success btn-circle"><i class="fa fa-file-excel-o"> </i> <b>Exportar a Excel</b></button>
                                <hr>
                                <div class="dataTable_wrapper">
                                    <table class="table table-striped table-bordered table-hover" id="fondeos">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Código sucursal</th>
                                                <th>Nombre Sucursal</th>
                                                <th>Código usuario</th>
                                                <th>Nombre usuario</th>
                                                <th>Movimiento</th>
                                                <th>Monto</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?= $filas; ?>
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
</div>

<?= $footer; ?>