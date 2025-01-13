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
                    <a class="navbar-brand">Admin Sucursales / Catálogo de Clientes</a>
                </div>

                <?= $submenu; ?>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <br><br>
                    <div class="col-md-6">
                        <p>Podrá encontrar el resumen detallado de las cuentas de los clientes MCM – Ahorro, si la información no es correcta, contacte a soporte. Si desea el detalle de los movimentos, consulte el siguiente <a href="/AdminSucursales/Reporteria/" target="_blank">enlace</a>.</p>
                        <hr>
                    </div>
                    <div class="col-md-4">
                        <label for="clienteBuscado">Código de cliente SICAFIN *</label>
                        <input onkeypress=validarYbuscar(event) class="form-control" id="clienteBuscado" name="clienteBuscado" placeholder="000000" required>
                    </div>
                    <div class="col-md-2" style="padding-top: 25px">
                        <button class="btn btn-primary" id="btnBskClnt" onclick="buscaCliente()">
                            <i class="fa fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
                <div class="col-md-12">
                    <ul class="nav navbar-nav" id="opcionesCat" style="margin-bottom: 20px;">
                        <li>
                            <span style="font-size: 15px; margin: 0 15px;font-weight: bold; color: #0D0A0A;" id="ResumenCuenta" onclick=actualizaVista(event)>Resumen de cuenta</span>
                        </li>
                        <li class="linea">
                            <span style="font-size: 15px; margin: 0 15px; color: #0D0A0A;" id="Rendimiento" onclick=actualizaVista(event)>Rendimiento</span>
                        </li>
                    </ul>
                </div>
                <div class="container-fluid">
                    <div class="row" id="cuerpoModal">
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="pieModal">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalClientes" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="text-align: center;">
                <h4 class="modal-title" id="myModalLabel">Activar Modulo de Ahorro para Sucursal</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <form onsubmit=noSubmit() id="frmModal">
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancelar</button>
                    <button class="btn btn-primary" value="enviar"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar Registro</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $footer; ?>