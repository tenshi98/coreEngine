<div class="clearfix"></div>
<div class="collapse" id="formSearch">
    <form id="FormSearchData" name="FormSearchData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
        <div class="container well">
            <div class="row">
                <div class="col align-self-center">
                    <h5 class="search-title text-center"><i class="bi bi-search"></i> Filtrar Datos</h5>
                    <?php
                    //se dibujan los inputs
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Nombre',              'Name' => 'Nombre',      'Id' => 'Search_Nombre',       'Value' => '', 'Required' => 1]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Icono',               'Name' => 'Icon',        'Id' => 'Search_Icon',         'Value' => '', 'Required' => 1]);
                    $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Color Icono',         'Name' => 'IdIconColor', 'Id' => 'Search_IdIconColor',  'Value' => '', 'Required' => 1,'arrData' => $data['arrColores']]);
                    $data['Fnc_FormInputs']->formTextarea([              'Placeholder' => 'Descripcion',         'Name' => 'Descripcion', 'Id' => 'Search_Descripcion',  'Value' => '', 'Required' => 1]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Carpeta Contenedora', 'Name' => 'Carpeta',     'Id' => 'Search_Carpeta',      'Value' => '', 'Required' => 1]);

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
    /******************************************/
    $("#FormSearchData").submit(function(e) {
        e.preventDefault();
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Metodo      = 'POST';
        let Direccion   = '<?php echo $BASE.'/Core/permisos/categorias/search'; ?>';
        let Informacion = $("#FormSearchData").serialize();
        const Options     = {
            UpdateDivFrom : 'X_datatable',
            colapseDiv : 'true',
            refreshTables : 'true',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        SendDataForms(Metodo, Direccion, Informacion, Options);
    });
</script>