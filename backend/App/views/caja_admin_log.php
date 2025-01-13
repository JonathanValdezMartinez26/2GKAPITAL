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
                <div class="navbar-header card col-md-12" style="background: #2b2b2b">
                    <a class="navbar-brand">Admin sucursales / Log Transaccional </a>
                </div>

                <?= $submenu; ?>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fInicio">Desde *</label>
                                    <input type="date" class="form-control" id="fInicio" name="fInicio" value="<?= $fecha; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fFin">Hasta *</label>
                                    <input type="date" class="form-control" id="fFin" name="fFin" value="<?= $fecha; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="operacion">Operación *</label>
                                    <select class="form-control" id="operacion" name="operacion">
                                        <?= $opcOperaciones ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sucrusal">Sucursal *</label>
                                    <select class="form-control" id="sucrusal" name="sucrusal">
                                        <?= $opcSucursales ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="usuario">Usuario *</label>
                                    <select class="form-control" id="usuario" name="usuario">
                                        <?= $opcUsuarios ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4" style="padding-top: 25px">
                                <button class="btn btn-primary" onclick=getLog()>
                                    <i class="fa fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover" id="log">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Sucursal</th>
                                            <th>Usuario</th>
                                            <th>Cliente</th>
                                            <th>Contrato</th>
                                            <th>Operación</th>
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