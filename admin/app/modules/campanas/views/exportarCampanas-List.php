<div id="listTableData" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-center pt-3">
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">
                        <div class="text-center">
                            <h5 class="search-title"><i class="bi bi-search"></i> Filtrar Datos</h5>
                        </div>
                        <form id="FormSearchData" name="FormSearchData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                            <?php
                            //se dibujan los inputs
                            $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Campaña']);
                            $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Campaña',          'Name' => 'idCampana',     'Id' => 'Search_idCampana',     'Value' => '', 'Required' => 1, 'arrData' => $data['arrCampanas'],  'BASE' => $BASE]);
                            $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Bodega',           'Name' => 'idBodegas',     'Id' => 'Search_idBodegas',     'Value' => '', 'Required' => 1, 'arrData' => $data['arrBodegas'],   'BASE' => $BASE]);
                            $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Usuario Creador',  'Name' => 'idUsuario',     'Id' => 'Search_idUsuario',     'Value' => '', 'Required' => 1, 'arrData' => $data['arrUsuarios'],  'BASE' => $BASE]);
                            $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado',           'Name' => 'idEstado',      'Id' => 'Search_idEstado',      'Value' => '', 'Required' => 1, 'arrData' => $data['arrEstados']]);

                            $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Partidas']);
                            $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Documento Mercantil', 'Name' => 'idDocumentos',    'Id' => 'Search_idDocumentos',    'Value' => '', 'Required' => 1, 'arrData' => $data['arrDocumentos']]);
                            $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Numero Documento',    'Name' => 'N_Doc',           'Id' => 'Search_N_Doc',           'Value' => '', 'Required' => 1, 'Icon'    => 'bi bi-sort-numeric-down']);
                            $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Numero Referencia',   'Name' => 'idFacturacion',   'Id' => 'Search_idFacturacion',   'Value' => '', 'Required' => 1, 'Icon'    => 'bi bi-sort-numeric-down']);
                            $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Entidad',             'Name' => 'idEntidad',       'Id' => 'Search_idEntidad',       'Value' => '', 'Required' => 1, 'arrData' => $data['arrEntidades'], 'BASE' => $BASE]);
                            $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha de Inicio',     'Name' => 'F_Inicio',        'Id' => 'Search_F_Inicio',        'Value' => '', 'Required' => 1, 'Icon'    => 'bi bi-calendar3']);
                            $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha de Termino',    'Name' => 'F_Termino',       'Id' => 'Search_F_Termino',       'Value' => '', 'Required' => 1, 'Icon'    => 'bi bi-calendar3']);

                            ?>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <button type="submit" class="btn btn-success"><i class="bi bi-search"></i> Filtrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" id="modalContent">

        </div>
    </div>
</div>

<script>
    /*********************************************************************/
    /*                      FORMULARIO DE BUSQUEDA                       */
    /*********************************************************************/
    /******************************************/
    $("#FormSearchData").submit(function(e) {
        e.preventDefault();
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Metodo      = 'POST';
        let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/search'; ?>';
        let Informacion = $("#FormSearchData").serialize();
        const Options     = {
            UpdateDivFrom : 'listTableData',
            colapseDiv : 'true',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        SendDataForms(Metodo, Direccion, Informacion, Options);
    });
</script>
