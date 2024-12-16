<?php echo $header; ?>

<?php

use App\components\SubMenuAhorro;
use App\components\BuscarCliente;

$subMenuAhorro = new SubMenuAhorro('CajaCredito');
$buscarCliente = new BuscarCliente('Para realizar un movimiento es necesario que el cliente tenga una cuenta ahorro corriente activa, de lo contrario, es necesaria la creación de una a través de la opción: <a href="/Ahorro/ContratoCuentaCorriente/" target="_blank">Nuevo Contrato</a>.');

?>

<div class="right_col">
    <div class="col-md-12 col-sm-12 col-xs-12 col-lg-12">
        <div class="col-md-3 panel panel-body" style="margin-bottom: 0px;">
            <a id="link" href="/Ahorro/CuentaCorriente/">
                <div class="col-md-5" style="margin-top: 5px; margin-left: 10px; margin-right: 30px; border: 1px solid #dfdfdf; border-radius: 10px;">
                    <img src="https://cdn-icons-png.flaticon.com/512/5575/5575939.png" style="border-radius: 3px; padding-top: 5px;" width="110" height="110">
                    <p style="font-size: 12px; padding-top: 5px; color: #000000"><b>Ahorro y Crédito </b></p>
                    <! -- https://cdn-icons-png.flaticon.com/512/5575/5575938.png -->
                </div>
            </a>

            <a id="link" href="/Ahorro/ContratoInversion/">
                <div class="col-md-5 imagen" style="margin-top: 5px; margin-left: 0px; border: 1px solid #dfdfdf; border-radius: 10px;">
                    <img src="https://cdn-icons-png.flaticon.com/512/5836/5836503.png" style="border-radius: 3px; padding-top: 5px;" width="110" height="110">
                    <p style="font-size: 12px; padding-top: 5px; color: #000000"><b>Inversión </b></p>
                    <! -- https://cdn-icons-png.flaticon.com/512/5836/5836477.png -->
                </div>
            </a>

            <a id="link" href="/Ahorro/CuentaPeque/">
                <div class="col-md-5 imagen" style="margin-top: 20px; margin-left: 10px; margin-right: 30px; border: 1px solid #dfdfdf; border-radius: 10px;">
                    <img src="https://cdn-icons-png.flaticon.com/512/2995/2995390.png" style="border-radius: 3px; padding-top: 5px;" width="110" height="110">
                    <p style="font-size: 12px; padding-top: 6px; color: #000000"><b>Ahorro Peque </b></p>
                    <! -- https://cdn-icons-png.flaticon.com/512/2995/2995467.png -->
                </div>
            </a>

            <a id="link" href="/Ahorro/EstadoCuenta/">
                <div class="col-md-5 imagen" style="margin-top: 20px; margin-left: 0px; border: 1px solid #dfdfdf; border-radius: 10px;">
                    <img src="https://cdn-icons-png.flaticon.com/512/12202/12202939.png" style="border-radius: 3px; padding-top: 5px;" width="110" height="110">
                    <p style="font-size: 12px; padding-top: 6px; color: #000000"><b>Resumen Movimientos </b></p>
                    <! -- https://cdn-icons-png.flaticon.com/512/12202/12202918.png -->
                </div>
            </a>

            <a id="link" href="/Ahorro/SaldosDia/">
                <div class="col-md-5 imagen" style="margin-top: 20px; margin-left: 10px; margin-right: 30px; border: 1px solid #dfdfdf; border-radius: 10px;">
                    <img src="https://cdn-icons-png.flaticon.com/512/5833/5833855.png" style="border-radius: 3px; padding-top: 5px;" width="100" height="110">
                    <p style="font-size: 12px; padding-top: 6px; color: #000000"><b>Arqueo </b></p>
                    <! -- https://cdn-icons-png.flaticon.com/512/5833/5833897.png -->
                </div>
            </a>

            <a id="link" href="/Ahorro/ReimprimeTicket/">
                <div class="col-md-5 imagen" style="margin-top: 20px; margin-left: 0px; border: 1px solid #dfdfdf; border-radius: 10px;">
                    <img src="https://cdn-icons-png.flaticon.com/512/7325/7325275.png" style="border-radius: 3px; padding-top: 5px;" width="110" height="110">
                    <p style="font-size: 12px; padding-top: 6px; color: #000000"><b>Reimprime Ticket </b></p>
                    <! -- https://cdn-icons-png.flaticon.com/512/942/942752.png -->
                </div>
            </a>
        </div>

        <div class="col-md-9" id="bloqueoAhorro">
            <div class="modal-content">
                <div class="modal-header" style="padding-bottom: 0px">
                    <div class="navbar-header card col-md-12" style="background: #2b2b2b">
                        <a class="navbar-brand">Mi espacio / Cuentas de ahorro corriente</a>
                        &nbsp;&nbsp;
                    </div>
                    <div>
                        <?= $subMenuAhorro->mostrar(); ?>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <?= $buscarCliente->mostrar(['tipo' => "crédito"]); ?>
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-2 form-group">
                                        <label>Cliente:</label>
                                        <input class="form-control" id="cliente" disabled />
                                    </div>
                                    <div class="col-md-5 form-group">
                                        <label>Nombre:</label>
                                        <input class="form-control" id="clienteNombre" disabled />
                                    </div>
                                    <div class="col-md-1 form-group">
                                        <label>Ciclo:</label>
                                        <input class="form-control" id="ciclo" disabled />
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label>Préstamo:</label>
                                        <input class="form-control" id="monto" disabled />
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label>Situación:</label>
                                        <input class="form-control" id="situacion" disabled />
                                        </input>
                                    </div>

                                    <div class="col-md-3 form-group">
                                        <label>Sucursal:</label>
                                        <input class="form-control" id="sucursal" disabled />
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label>Día de Pago:</label>
                                        <input class="form-control" id="diaPago" disabled />
                                    </div>

                                    <div class="col-md-2 form-group">
                                        <label>Parcialidad:</label>
                                        <input class="form-control" id="parcialidad" disabled />
                                    </div>
                                    <div class="col-md-3 form-group">
                                        <label>Ejecutivo de cuenta:</label>
                                        <input class="form-control" id="ejecutivo" disabled />
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <div style="height: 58px; width: 100%; display: flex; align-items: center; justify-content: center;">
                                            <button type="button" class="btn btn-primary" id="agregarPago" disabled>
                                                <i class="fa fa-plus"></i> Agregar Pago
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="card-body">
                                <div class="dataTable_wrapper">
                                    <table class="table table-striped table-bordered table-hover" id="historialPagos">
                                        <thead>
                                            <tr>
                                                <th>Medio</th>
                                                <th>Consecutivo</th>
                                                <th>Crédito</th>
                                                <th>Ciclo</th>
                                                <th>Fecha</th>
                                                <th>Monto</th>
                                                <th>Tipo</th>
                                                <th>Ejecutivo</th>
                                                <th>Acciones</th>
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
</div>


<div class="modal fade" id="modal_pago" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <center>
                    <h4 class="modal-title" id="tituloModal"></h4>
                </center>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fechaPago">Fecha</label>
                                <input onkeydown="return false" type="date" class="form-control" id="fechaPago" name="fechaPago" max="<?= $fecha ?>">
                                <input type="date" id="fechaOriginalPago" name="fechaOriginalPago" hidden disabled>
                                <small class="form-text text-muted">Fecha de registro en sistema.</small>
                            </div>
                        </div>

                        <div class="col-md-4" style="display: none">
                            <div class="form-group">
                                <input type="text" class="form-control" id="usuario" name="usuario">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="medio">Medio de Registro</label>
                                <input type="text" class="form-control" id="medio" value="CAJERA" disabled>
                                <small class="form-text text-muted">Medio de registro del pago.</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="credito">Crédito</label>
                                <input type="number" class="form-control" id="credito" name="credito" disabled>
                                <small class="form-text text-muted">Número del crédito.</small>
                            </div>
                        </div>

                        <div class="col-md-10">
                            <div class="form-group">
                                <label for="nombre">Nombre del Cliente</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" disabled>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="ciclo2">Ciclo</label>
                                <input type="text" class="form-control" id="ciclo2" name="ciclo2" disabled>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="tipo">Tipo de Operación</label>
                                <select class="form-control mr-sm-3" autofocus type="select" id="tipo" name="tipo">
                                    <option value="P">PAGO</option>
                                    <option value="X">PAGO ELECTRÓNICO</option>
                                    <option value="S">SEGURO</option>
                                    <option value="M">MULTA</option>
                                    <option value="G">COMISIÓN</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="montoPago">Monto *</label>
                                <input autofocus type="number" class="form-control" id="montoPago" name="montoPago" autocomplete="off" max="10000">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group" id="grpSecuencia">
                                <label for="secuencia">Secuencia</label>
                                <input type="number" class="form-control" id="secuencia" name="secuencia" disabled>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="ejecutivo2">Nombre del Ejecutivo</label>
                                <select class="form-control mr-sm-3" autofocus type="select" id="ejecutivo2" name="ejecutivo2">
                                    <?= $status; ?>
                                </select>
                                <small class="form-text text-muted">Nombre del ejecutivo que entrega el pago.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancelar</button>
                <button type="submit" name="registrarPago" class="btn btn-primary" id="registrarPago"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar Pago</button>
                <button type="submit" name="editarPago" class="btn btn-primary" id="editarPago"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<style>
    .imagen {
        transform: scale(var(--escala, 1));
        transition: transform 0.25s;
    }

    .imagen:hover {
        --escala: 1.2;
        cursor: pointer;
    }
</style>

<?php echo $footer; ?>