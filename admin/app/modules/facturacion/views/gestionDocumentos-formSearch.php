<div class="clearfix"></div>
<div class="collapse" id="formSearch">
    <form id="FormSearchData" name="FormSearchData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
        <div class="container well">
            <div class="row">
                <div class="col align-self-center">
                    <h5 class="search-title text-center"><i class="bi bi-search"></i> Filtrar Datos</h5>
                    <?php
                    //se dibujan los inputs
                    $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Documento Mercantil', 'Name' => 'idDocumentos',    'Id' => 'Search_idDocumentos',    'Value' => '', 'Required' => 1, 'arrData' => $data['arrDocumentos']]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Numero Documento',    'Name' => 'N_Doc',           'Id' => 'Search_N_Doc',           'Value' => '', 'Required' => 1, 'Icon' => 'bi bi-sort-numeric-down']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Numero Referencia',   'Name' => 'idFacturacion',   'Id' => 'Search_idFacturacion',   'Value' => '', 'Required' => 1, 'Icon' => 'bi bi-sort-numeric-down']);
                    $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Entidad',             'Name' => 'idEntidad',       'Id' => 'Search_idEntidad',       'Value' => '', 'Required' => 1, 'arrData' => $data['arrEntidades'], 'BASE' => $BASE]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha de Inicio',     'Name' => 'F_Inicio',        'Id' => 'Search_F_Inicio',        'Value' => '', 'Required' => 1, 'Icon' => 'bi bi-calendar3']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha de Termino',    'Name' => 'F_Termino',       'Id' => 'Search_F_Termino',       'Value' => '', 'Required' => 1, 'Icon' => 'bi bi-calendar3']);
                    $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado Pago',         'Name' => 'idEstadoPago',    'Id' => 'Search_idEstadoPago',    'Value' => '', 'Required' => 1, 'arrData' => $data['arrEstadoPago']]);

                    ?>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <button type="button" class="btn btn-danger" data-bs-toggle="collapse" data-bs-target="#formSearch"><i class="bx bi-x-circle"></i> Cerrar</button>
                        <button type="button" class="btn btn-secondary" onclick="deleteFilter('.collapse')"><i class="ri-filter-off-line"></i> Quitar Filtro</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-search"></i> Filtrar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
            refreshTables : 'true',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        SendDataForms(Metodo, Direccion, Informacion, Options);
    });

</script>