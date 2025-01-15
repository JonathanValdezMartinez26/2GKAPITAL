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
                    <div class="container">
                        <section id="fancyTabWidget" class="tabs t-tabs">
                            <ul class="nav nav-tabs fancyTabs" role="tablist">

                                <li class="tab fancyTab active">
                                    <div class="arrow-down">
                                        <div class="arrow-down-inner"></div>
                                    </div>
                                    <a id="tab0" href="#tabBody0" role="tab" aria-controls="tabBody0" aria-selected="true" data-toggle="tab" tabindex="0"><span class="fa fa-clock-o"></span><span class="hidden-xs"> Solicitudes pendientes</span></a>
                                    <div class="whiteBlock"></div>
                                </li>

                                <li class="tab fancyTab">
                                    <div class="arrow-down">
                                        <div class="arrow-down-inner"></div>
                                    </div>
                                    <a id="tab1" href="#tabBody1" role="tab" aria-controls="tabBody1" aria-selected="true" data-toggle="tab" tabindex="0"><span class="fa fa-history"></span><span class="hidden-xs"> Historial de Solicitudes</span></a>
                                    <div class="whiteBlock"></div>
                                </li>
                            </ul>
                            <div id="myTabContent" class="tab-content fancyTabContent" aria-live="polite">
                                <div class="tab-pane  fade active in" id="tabBody0" role="tabpanel" aria-labelledby="tab0" aria-hidden="false" tabindex="0">
                                    <div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="container-fluid">
                                                    <br>
                                                    <button id="export_excel_consulta" type="button" class="btn btn-success btn-circle"><i class="fa fa-file-excel-o"> </i> <b>Exportar a Excel</b></button>
                                                    <hr>
                                                    <div class="col-md-12">
                                                        <div class="row">
                                                            <div class="card col-md-12">
                                                                <div class="dataTable_wrapper">
                                                                    <table class="table table-striped table-bordered table-hover" id="muestra-cupones">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Cod Sucursal</th>
                                                                                <th>Nombre Sucursal</th>
                                                                                <th>Hora Cierre</th>
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
                                <div class="tab-pane  fade" id="tabBody1" role="tabpanel" aria-labelledby="tab1" aria-hidden="true" tabindex="0">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="container-fluid">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <br>
                                                        <button id="export_excel_consulta" type="button" class="btn btn-success btn-circle"><i class="fa fa-file-excel-o"> </i> <b>Exportar a Excel</b></button>
                                                        <hr>
                                                        <hr>
                                                        <div class="card col-md-12">
                                                            <div class="dataTable_wrapper">
                                                                <table class="table table-striped table-bordered table-hover" id="muestra-cupones1">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Cod Sucursal</th>
                                                                            <th>Nombre Sucursal</th>
                                                                            <th>Estatus</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?= $tabla_his; ?>
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
                        </section>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<script>
    function EditarHorario(sucursal, nombre_suc, hora_actual) {


        var o = new Option(nombre_suc, sucursal);
        $(o).html(nombre_suc);
        $("#sucursal_e").append(o);

        document.getElementById("hora_ae").value = hora_actual;

        $('#modal_update_horario').modal('show');

    }
</script>

<style>
    .container {
        margin-top: 0px;
    }

    .fancyTab.active .fa {
        color: #cfb87c;
    }

    .fancyTab a:focus {
        outline: none;
    }
</style>


<?= $footer; ?>