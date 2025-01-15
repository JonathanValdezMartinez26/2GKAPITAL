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
                        <div class="col-md-12">
                            <div class="row">
                                <div class="card col-md-12">
                                    <form class="" id="consulta" action="/Operaciones/PerfilTransaccional/" method="GET" onsubmit="return Validar()">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="Inicial">Desde *</label>
                                                    <input type="date" class="form-control" min="2024-05-22" max="<?= $fechaActual; ?>" id="Inicial" name="Inicial" value="<?= $fecha_inicial; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="Final">Hasta *</label>
                                                    <input type="date" class="form-control" min="2024-05-22" max="<?= $fechaActual; ?>" id="Final" name="Final" value="<?= $fecha_final; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="Operacion">Operación *</label>
                                                    <select class="form-control" id="Operacion" name="Operacion">
                                                        <?= $operacion; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <label for="Producto">Producto *</label>
                                                    <select class="form-control" id="Producto" name="Producto">
                                                        <?= $productos; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="Sucursal">Sucursal activa*</label>
                                                    <select class="form-control" id="Sucursal" name="Sucursal">
                                                        <option value="0">TODAS LAS SUCURSALES</option>
                                                        <?= $sucursales; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4" style="padding-top: 25px">
                                                <button class="btn btn-primary" onclick="getLog()">
                                                    <i class="fa fa-search"></i> Buscar
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    <form name="all" id="all" method="POST">
                                        <br>
                                        <button id="export_excel_con_transacciones" type="button" class="btn btn-success btn-circle"><i class="fa fa-file-excel-o"> </i> <b>Exportar a Excel</b></button>
                                        <hr>
                                        <div class="col-md-12">
                                            <div class="col-md-12">
                                                <p>Atención:
                                                    <br><span class="count_top" style="font-size: 18px"><i class="fa fa-minus" style="color: #00ac00"></i></span> Movimiento virtual ingreso.
                                                    | <span class="count_top" style="font-size: 18px"><i class="fa fa-arrow-down" style="color: #00ac00"></i></span> Movimiento en efectivo egreso.
                                                    | <span class="count_top" style="font-size: 18px"><i class="fa fa-minus" style="color: #ac0000"></i></span> Movimiento virtual egreso.
                                                    | <span class="count_top" style="font-size: 18px"><i class="fa fa-arrow-up" style="color: #ac0000"></i></span> Movimiento en efectivo egreso.
                                                    | <span class="count_top" style="font-size: 18px"><i class="fa fa-asterisk" style="color: #005dac"></i></span> Movimiento virtual (Solicitud Retiro).

                                            </div>
                                        </div>
                                        <div class="dataTable_wrapper">
                                            <table class="table table-striped table-bordered table-hover" id="muestra-cupones">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th></th>
                                                        <th>Fecha Transacción</th>
                                                        <th>Detalle Producto</th>
                                                        <th>Ingreso</th>
                                                        <th>Egreso</th>
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
        </form>
    </div>
</div>

<?= $footer; ?>