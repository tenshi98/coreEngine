<div class="clearfix"></div>
<div class="collapse" id="formSearch">
    <form id="FormSearchData" name="FormSearchData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
        <div class="container well">
            <div class="row">
                <div class="col align-self-center">
                    <h5 class="search-title text-center"><i class="bi bi-search"></i> Filtrar Datos</h5>
                    <?php
                    //se dibujan los inputs
                    $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha de Inicio',  'Name' => 'Fecha_Inicio',  'Id' => 'Search_Fecha_Inicio',  'Value' => '', 'Required' => 1, 'Icon' => 'bi bi-calendar3']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha de Termino', 'Name' => 'Fecha_Termino', 'Id' => 'Search_Fecha_Termino', 'Value' => '', 'Required' => 1, 'Icon' => 'bi bi-calendar3']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',           'Name' => 'Nombre',        'Id' => 'Search_Nombre',        'Value' => '', 'Required' => 1]);
                    $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Bodega',           'Name' => 'idBodegas',     'Id' => 'Search_idBodegas',     'Value' => '', 'Required' => 1, 'arrData' => $data['arrBodegas'],   'BASE' => $BASE]);
                    $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Usuario Creador',  'Name' => 'idUsuario',     'Id' => 'Search_idUsuario',     'Value' => '', 'Required' => 1, 'arrData' => $data['arrUsuarios'],  'BASE' => $BASE]);
                    $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado',           'Name' => 'idEstado',      'Id' => 'Search_idEstado',      'Value' => '', 'Required' => 1, 'arrData' => $data['arrEstados']]);

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