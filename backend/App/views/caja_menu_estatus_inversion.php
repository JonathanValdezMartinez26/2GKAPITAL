<?= $header; ?>

<?php

use App\components\AhorroMenus_MiEspacio;
use App\components\BuscarCliente;

[$menu, $submenu] = AhorroMenus_MiEspacio::mostrar();
$buscarCliente = new BuscarCliente('Para poder dar de alta un nuevo contrato de una cuenta de Ahorro, el cliente debe estar registrado en SICAFIN, si el cliente no tiene una cuenta abierta solicite el alta a su ADMINISTRADORA.');

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
                    <?= $buscarCliente->mostrar(); ?>
                    <div class="row">
                        <div class="col-md-8 tile_stats_count">
                            <div class="form-group">
                                <label for="nombre">Nombre del cliente</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 tile_stats_count">
                            <div class="form-group">
                                <label for="curp">CURP</label>
                                <input type="text" class="form-control" id="curp" name="curp" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 tile_stats_count">
                            <div class="form-group">
                                <label for="contrato">Número de contrato</label>
                                <input type="text" class="form-control" id="contrato" name="contrato" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 tile_stats_count">
                            <div class="form-group">
                                <label for="cliente">Código de cliente SICAFIN</label>
                                <input type="text" class="form-control" id="cliente" name="cliente" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 tile_stats_count">
                            <div class="form-group">
                                <label for="inversion">Capital invertido</label>
                                <input type="text" class="form-control" id="inversion" name="inversion" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="dataTable_wrapper">
                            <table class="table table-striped table-bordered table-hover" id="muestra-cupones">
                                <thead>
                                    <tr>
                                        <th>Apertura</th>
                                        <th>Monto</th>
                                        <th>Tasa</th>
                                        <th>Plazo</th>
                                        <th>Periodicidad</th>
                                        <th>Vencimiento</th>
                                        <th>Rendimiento</th>
                                        <th>Liquidación</th>
                                        <th>Acción al cierre</th>
                                    </tr>
                                </thead>
                                <tbody id="datosTabla">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $footer; ?>