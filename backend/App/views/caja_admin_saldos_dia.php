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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fechaI">Desde *</label>
                                    <input type="date" class="form-control" id="fechaI" name="fechaI" value="<?= $fechaI; ?>" min="2024-01-01" max="<?= $fechaF; ?>" onchange="validaFIF('fechaI', 'fechaF')">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fechaF">Hasta *</label>
                                    <input type="date" class="form-control" id="fechaF" name="fechaF" value="<?= $fechaF; ?>" min="2024-01-01" max="<?= $fechaF; ?>" onchange="validaFIF('fechaI', 'fechaF')">
                                </div>
                            </div>
                            <div class="col-md-3" style="padding-top: 25px">
                                <button type="button" class="btn btn-primary btn-circle" onclick=consultaSaldos()>
                                    <i class="fa fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3" style="padding-top: 25px">
                                <button type="button" class="btn btn-success btn-circle" data-toggle="modal" data-target="#modal_agregar_horario" onclick=imprimeExcel()>
                                    <i class="fa fa-file-excel-o"></i> Exportar a Excel
                                </button>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover" id="saldos">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Cod sucursal</th>
                                            <th>Nombre sucursal</th>
                                            <th>Saldo</th>
                                            <th>Diferencia al cierre</th>
                                            <th>Capacidad operativa</th>
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
        </form>
    </div>
</div>

<?= $footer; ?>