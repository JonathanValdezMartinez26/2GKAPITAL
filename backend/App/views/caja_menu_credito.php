<?php echo $header; ?>

<?php

use App\components\MenuAhorro;
use App\components\BuscarCliente;

// $menuAhorro = new MenuAhorro('/Ahorro/CuentaCorriente/');

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
                        <ul class="nav navbar-nav">
                            <li class="linea">
                                <a href="/Ahorro/CuentaCorriente/">
                                    <p style="font-size: 15px;">Ahorro cuenta corriente</p>
                                </a>
                            </li>

                            <li>
                                <a href="/Ahorro/CajaCredito/">
                                    <p style="font-size: 16px;"><b>Caja Crédito</b></p>
                                </a>
                            </li>
                            <li class="linea">
                                <a href="/Ahorro/ContratoCuentaCorriente/">
                                    <p style="font-size: 15px;">Nuevo contrato</p>
                                </a>
                            </li>
                            <li class="linea">
                                <a href="/Ahorro/SolicitudRetiroCuentaCorriente/">
                                    <p style="font-size: 15px;">Solicitud de retiro</p>
                                </a>
                            </li>
                            <li class="linea">
                                <a href="/Ahorro/HistorialSolicitudRetiroCuentaCorriente/">
                                    <p style="font-size: 15px;">Procesar solicitudes de retiro</p>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <?= $buscarCliente->mostrar(); ?>
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label>Cliente:</label>
                                        <input class="form-control" id="cliente" value="ALBERTO SOTO ORTEGA" disabled />
                                    </div>
                                    
                                    <div class="col-md-1 form-group">
                                        <label>Ciclo:</label>
                                        <input class="form-control" id="ciclo" value="01" disabled />
                                    </div>
                                    
                                    <div class="col-md-2 form-group">
                                        <label>Préstamo:</label>
                                        <input class="form-control" id="monto" value="$ 99,999.99" disabled />
                                    </div>
                                    
                                    <div class="col-md-2 form-group">
                                        <label>Situación:</label>
                                        <input class="form-control" id="situacion" value="Entregado" disabled />
                                        </input>
                                    </div>
                                    
                                    <div class="col-md-3 form-group">
                                        <label>Sucursal:</label>
                                        <input class="form-control" id="sucursal" value="TECAMAC" disabled />
                                    </div>
                                    
                                    <div class="col-md-2 form-group">
                                        <label>Día de Pago:</label>
                                        <input class="form-control" id="diaPago" value="Miércoles" disabled />
                                    </div>
                                    
                                    <div class="col-md-2 form-group">
                                        <label>Parcialidad:</label>
                                        <input class="form-control" id="parcialidad" value="$ 99,999.99" disabled />
                                    </div>
                                    
                                    <div class="col-md-4 form-group">
                                        <label>Ejecutivo de cuenta:</label>
                                        <input class="form-control" id="ejecutivo" value="JUAN PEREZ" disabled />
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <div style="height: 58px; width: 100%; display: flex; align-items: center; justify-content: center;">
                                            <button type="button" class="btn btn-primary">
                                                <i class="fa fa-plus"></i> Agregar Pago
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr style="border-top: 1px solid #787878; margin-top: 5px;">
                            <div class="card-body">
                                <div class="dataTable_wrapper">
                                    <table class="table table-striped table-bordered table-hover" id="historialPagos">
                                        <thead>
                                            <tr>
                                                <th>Medio</th>
                                                <th>Consecutivo</th>
                                                <th>CDGNS</th>
                                                <th>Fecha</th>
                                                <th>Ciclo</th>
                                                <th>Monto</th>
                                                <th>Tipo</th>
                                                <th>Ejecutivo</th>
                                                <th>Acciones</th>
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

    .linea:hover {
        --escala: 1.2;
        cursor: pointer;
        text-decoration: underline;
    }
</style>


<?php echo $footer; ?>