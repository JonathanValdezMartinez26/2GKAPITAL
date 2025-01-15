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
                            <!--<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_agregar_horario">
                                  <i class="fa fa-plus"></i> Nuevo Usuario
                              </button>-->
                            <p>Los usuarios que aqui se muestran tienen acceso al modulo de ahorro en la version <b>ADMINISTRADOR</b>, si desea asignar un nuevo usuario realice un soporte al área de desarrollo. </p>
                            <hr style=" margin-top: 5px;">
                        </div>
                        <div class="row">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover" id="muestra-cupones">
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Nombre</th>
                                            <th>Puesto</th>
                                            <th>Sucursal</th>
                                            <th>Estatus</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?= $tabla; ?>
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

<!-- <div class="modal fade in" id="modal_agregar_horario" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: block; padding-right: 15px;"> -->
<div class="modal fade" id="modal_agregar_horario" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <center>
                    <h4 class="modal-title" id="myModalLabel">Permisos Modulo Administración Ahorro</h4>
                </center>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <form id="datos" onsubmit=noSUBMIT(event)>
                        <div class="row">
                            <div class="col-md-12">
                                <p>Selecciona las opciones a las que te gustaria dar acceso a sus colaboradores</p>
                                <hr>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sucursal">Colaborador Administrativo MCM *</label>
                                    <select class="form-control" id="cajera" name="cajera" onchange=cambioCajera()>
                                        <?= $opcEmpleados; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group" style="border: #9baab8 !important; border-style: solid; padding:10px; padding-bottom: 19px;">
                                    <label for="sucursal">SALDOS DE SUCURSALES</label>
                                    <hr>
                                    <input name="A" type="checkbox" value="1" />
                                    <label for="A">Saldos del día por sucursal (A)</label>

                                    <br>
                                    <input name="B" type="checkbox" value="1" />
                                    <label for="B">Cierre de día (B)</label>

                                    <br>
                                    <input name="C" type="checkbox" value="1" />
                                    <label for="C">Fondear sucursal (C)</label>

                                    <br>
                                    <input name="D" type="checkbox" value="" />
                                    <label for="D">Retiro efectivo (D)</label>

                                    <br>
                                    <input name="E" type="checkbox" value="1" />
                                    <label for="E">Historail saldos por sucursal (E)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group" style="border: #9baab8 !important; border-style: solid; padding:10px; padding-bottom: 19px;">
                                    <label for="sucursal">SOLICITUDES</label>
                                    <hr>
                                    <input name="F" type="checkbox" value="1" />
                                    <label for="F">Reimpresión de tickets (F)</label>

                                    <br>
                                    <input name="G" type="checkbox" value="1" />
                                    <label for="G">Resumen de movimientos (G)</label>

                                    <br>
                                    <input name="H" type="checkbox" value="1" />
                                    <label for="H">Retiros ordinarios (H)</label>

                                    <br>
                                    <input name="I" type="checkbox" value="1" />
                                    <label for="I">Retiros express (I)</label>

                                    <br>
                                    <input name="J" type="checkbox" value="1" />
                                    <label for="J">Retirar efectivo de caja (J)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group" style="border: #9baab8 !important; border-style: solid; padding:10px; padding-bottom: 128px!important;">
                                    <label for="sucursal">CATÁLOGO DE CLIENTES</label>
                                    <hr>
                                    <input name="K" type="checkbox" value="1" />
                                    <label for="K">Catálogo de clientes (K)</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <hr>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group" style="border: #9baab8 !important; border-style: solid; padding:10px; padding-bottom: 108px;">
                                    <label for="sucursal">LOG TRANSACCIONAL</label>
                                    <hr>
                                    <input name="L" type="checkbox" value="1" />
                                    <label for="L">Log transaccional (L)</label>


                                    <br>
                                    <input name="M" type="checkbox" value="1" />
                                    <label for="M">Log de Configuración (M)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group" style="border: #9baab8 !important; border-style: solid; padding:10px; padding-bottom: 80px;">
                                    <label for="sucursal">CONFIGURAR MÓDULO</label>
                                    <hr>
                                    <input name="N" type="checkbox" value="1" />
                                    <label for="N">Activar módulo en sucursal (N)</label>

                                    <br>
                                    <input name="O" type="checkbox" value="1" />
                                    <label for="O">Permisos a usuarios (O)</label>

                                    <br>
                                    <input name="P" type="checkbox" value="1" />
                                    <label for="P">Parámetros de operación (P)</label>

                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group" style="border: #9baab8 !important; border-style: solid; padding:10px; padding-bottom: 53px;">
                                    <label for="sucursal">CONSULTAR REPORTES</label>
                                    <hr>
                                    <input name="Q" type="checkbox" value="1" />
                                    <label for="Q">Hostorial de transacciones (Q)</label>

                                    <br>
                                    <input name="R" type="checkbox" value="1" />
                                    <label for="R">Historial fondeo sucursal (R)</label>

                                    <br>
                                    <input name="S" type="checkbox" value="1" />
                                    <label for="S">Historial retiro sucursal(S)</label>
                                    <br>

                                    <input name="T" type="checkbox" value="1" />
                                    <label for="T">Historial cierre día(T)</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancelar</button>
                <button class="btn btn-primary" id="guardar" onclick=activarSucursal() disabled><span class="glyphicon glyphicon-floppy-disk"></span> Guardar Registro</button>
            </div>
        </div>
    </div>
</div>

<?= $footer; ?>