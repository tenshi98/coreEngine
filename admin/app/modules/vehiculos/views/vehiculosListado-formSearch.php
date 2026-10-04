<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="clearfix"></div>

<div class="collapse" id="formSearch">
    <form id="FormSearchData" name="FormSearchData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
        <div class="container well">
            <div class="row">
                <div class="col align-self-center">
                    <h5 class="search-title text-center"><i class="bi bi-search"></i> Filtrar Datos</h5>
                    <?php
                    //se dibujan los inputs
                    $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Tipo',        'Name' => 'idTipo',       'Id' => 'Search_idTipo',       'Value' => '','Required' => 1,'arrData' => $data['arrTipo']]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',      'Name' => 'Nombre',       'Id' => 'Search_Nombre',       'Value' => '','Required' => 1]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Marca',       'Name' => 'Marca',        'Id' => 'Search_Marca',        'Value' => '','Required' => 1]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Modelo',      'Name' => 'Modelo',       'Id' => 'Search_Modelo',       'Value' => '','Required' => 1]);
                    $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Tipo Carga',  'Name' => 'idTipoCarga',  'Id' => 'Search_idTipoCarga',  'Value' => '','Required' => 1,'arrData' => $data['arrTipoCarga']]);
                    $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado',      'Name' => 'idEstado',     'Id' => 'Search_idEstado',     'Value' => '','Required' => 1,'arrData' => $data['arrEstado']]);

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
    /************************************/
    $("#FormSearchData").submit(function(e) {
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
        //verifico el resultado
        if(validatorResult.valid===false){
            return !!validatorResult.valid;
        }else{
            // Si ya se está ejecutando, salimos
            if (ejecutandoForm.valor) return;
            //Cambio los valores
            ejecutandoForm.valor = true;
            //Ejecucion normal
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
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /************************************/
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
