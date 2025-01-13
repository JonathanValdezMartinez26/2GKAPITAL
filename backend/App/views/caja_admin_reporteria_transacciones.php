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
                    <div class="navbar-header card col-md-12" style="background: #2b2b2b">
                        <a class="navbar-brand">Admin sucursales / Consultar reportes / Flujo efectivo</a>
                    </div>

                    <?= $submenu; ?>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="card col-md-12">
                                    <form class="" id="consulta" action="/Operaciones/PerfilTransaccional/" method="GET" onsubmit="return Validar()">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="Inicial">Fecha a consultar (flujo de efectivo) *</label>
                                                    <input type="date" class="form-control" min="2024-06-03" max="<?= $fechaActual; ?>" id="Inicial" name="Inicial" value="<?= $fecha_inicial; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="Sucursal">Sucursal activa *</label>
                                                    <select class="form-control" id="Sucursal" name="Sucursal">
                                                        <option value="0">TODAS LAS SUCURSALES</option>
                                                        <?= $sucursales; ?>
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
                                                    <label for="Operacion">Operación *</label>
                                                    <select class="form-control" id="Operacion" name="Operacion">
                                                        <?= $operacion; ?>
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
                                    <br>
                                    <form name="all" id="all" method="POST">
                                        <button id="export_excel_consulta" type="button" class="btn btn-success btn-circle"><i class="fa fa-file-excel-o"> </i> <b>Exportar a Excel</b></button>
                                        <hr>
                                        <div class="dataTable_wrapper">
                                            <table class="table table-striped table-bordered table-hover" id="muestra-cupones">
                                                <thead>
                                                    <tr>
                                                        <th>Cliente</th>
                                                        <th></th>
                                                        <th>Fecha Transacción</th>
                                                        <th>Detalle Producto</th>
                                                        <th>Ingreso</th>
                                                        <th>Egreso</th>
                                                        <th>Saldo</th>
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