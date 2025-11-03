<div class="clearfix"></div>
<div class="collapse" id="formSearch">
    <form id="FormSearchData" name="FormSearchData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
        <div class="container well">
            <div class="row">
                <div class="col align-self-center">
                    <h5 class="search-title text-center"><i class="bi bi-search"></i> Filtrar Datos</h5>
                    <?php
                    //se dibujan los inputs
                    $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Tipo',              'Name'  => 'idTipo',         'Id'  => 'Search_idTipo',        'Value'  => '','Required' => 1,'arrData' => $data['arrTipo']]);
                    $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Tipo Entidad',      'Name'  => 'idTipoEntidad',  'Id'  => 'Search_idTipoEntidad', 'Value'  => '','Required' => 1,'arrData' => $data['arrTipoEntidad']]);
                    //Persona natural
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',            'Name'  => 'Nombre',         'Id'  => 'Search_Nombre',        'Value'  => '','Required' => 1]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Paterno',  'Name'  => 'ApellidoPat',    'Id'  => 'Search_ApellidoPat',   'Value'  => '','Required' => 1]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Materno',  'Name'  => 'ApellidoMat',    'Id'  => 'Search_ApellidoMat',   'Value'  => '','Required' => 1]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nick',              'Name'  => 'Nick',           'Id'  => 'Search_Nick',          'Value'  => '','Required' => 1]);
                    $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Sexo',              'Name'  => 'idSexo',         'Id'  => 'Search_idSexo',        'Value'  => '','Required' => 1,'arrData' => $data['arrSexo']]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Nacimiento',  'Name'  => 'FNacimiento',    'Id'  => 'Search_FNacimiento',   'Value'  => '','Required' => 1,'Icon' => 'bi bi-calendar3']);
                    //empresas
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Razón Social',      'Name'  => 'RazonSocial',    'Id'  => 'Search_RazonSocial',   'Value'  => '','Required' => 1]);
                    //Comun
                    $data['Fnc_FormInputs']->formSelectDepend([            'Placeholder1' => 'Ciudad',     'Name1' => 'idCiudad',    'Id1' => 'Search_idCiudad',   'Value1' => '','Required1' => 1,'arrData1' => $data['arrCiudad'],
                                                                           'Placeholder2' => 'Comuna',     'Name2' => 'idComuna',    'Id2' => 'Search_idComuna',   'Value2' => '','Required2' => 1,'arrData2' => $data['arrComuna']]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,   'Placeholder'  => 'Dirección',  'Name'  => 'Direccion',   'Id'  => 'Search_Direccion',  'Value'  => '','Required'  => 1,'Icon' => 'bi bi-geo-alt-fill']);
                    $data['Fnc_FormInputs']->formSelectFilter([            'Placeholder'  => 'Sector',     'Name'  => 'idSector',    'Id'  => 'Search_idSector',   'Value'  => '','Required'  => 1,'arrData' => $data['arrSector'], 'BASE' => $BASE]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 2,   'Placeholder'  => 'Email',      'Name'  => 'Email',       'Id'  => 'Search_Email',      'Value'  => '','Required'  => 1,'Icon' => 'bx bx-mail-send']);
                    $data['Fnc_FormInputs']->formSelect([                  'Placeholder'  => 'Estado',     'Name'  => 'idEstado',    'Id'  => 'Search_idEstado',   'Value'  => '','Required'  => 1,'arrData' => $data['arrEstado']]);

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
    /******************************************/
    //Oculto
    document.getElementById('div_Search_Nombre').style.display       = 'none';
    document.getElementById('div_Search_ApellidoPat').style.display  = 'none';
    document.getElementById('div_Search_ApellidoMat').style.display  = 'none';
    document.getElementById('div_Search_Nick').style.display         = 'none';
    document.getElementById('div_Search_idSexo').style.display       = 'none';
    document.getElementById('div_Search_FNacimiento').style.display  = 'none';
    document.getElementById('div_Search_RazonSocial').style.display  = 'none';

    /**********************************************************************/
    //cargo
    document.getElementById("Search_idTipoEntidad").onchange = function() {cngFnc_Search_idTipoEntidad()}
    //Ejecutar logica
    function cngFnc_Search_idTipoEntidad() {
        //obtengo los valores
        let Search_idTipoEntidad = $("#Search_idTipoEntidad").val();
        //selecciono
        if (Search_idTipoEntidad != "") {
            //selecciono
            switch (Search_idTipoEntidad) {
                //Persona Natural
                case '1':
                    document.getElementById('div_Search_Nombre').style.display       = '';
                    document.getElementById('div_Search_ApellidoPat').style.display  = '';
                    document.getElementById('div_Search_ApellidoMat').style.display  = '';
                    document.getElementById('div_Search_Nick').style.display         = '';
                    document.getElementById('div_Search_idSexo').style.display       = '';
                    document.getElementById('div_Search_FNacimiento').style.display  = '';
                    document.getElementById('div_Search_RazonSocial').style.display  = 'none';
                    break;
                //Empresas
                case '2':
                    document.getElementById('div_Search_Nombre').style.display       = 'none';
                    document.getElementById('div_Search_ApellidoPat').style.display  = 'none';
                    document.getElementById('div_Search_ApellidoMat').style.display  = 'none';
                    document.getElementById('div_Search_Nick').style.display         = 'none';
                    document.getElementById('div_Search_idSexo').style.display       = 'none';
                    document.getElementById('div_Search_FNacimiento').style.display  = 'none';
                    document.getElementById('div_Search_RazonSocial').style.display  = '';
                    break;
                //el resto
                default:
                    document.getElementById('div_Search_Nombre').style.display       = 'none';
                    document.getElementById('div_Search_ApellidoPat').style.display  = 'none';
                    document.getElementById('div_Search_ApellidoMat').style.display  = 'none';
                    document.getElementById('div_Search_Nick').style.display         = 'none';
                    document.getElementById('div_Search_idSexo').style.display       = 'none';
                    document.getElementById('div_Search_FNacimiento').style.display  = 'none';
                    document.getElementById('div_Search_RazonSocial').style.display  = 'none';
                    break;
            }
        //si el select esta vacio
        }else{
            document.getElementById('div_Search_Nombre').style.display       = 'none';
            document.getElementById('div_Search_ApellidoPat').style.display  = 'none';
            document.getElementById('div_Search_ApellidoMat').style.display  = 'none';
            document.getElementById('div_Search_Nick').style.display         = 'none';
            document.getElementById('div_Search_idSexo').style.display       = 'none';
            document.getElementById('div_Search_FNacimiento').style.display  = 'none';
            document.getElementById('div_Search_RazonSocial').style.display  = 'none';
        }
    }
</script>