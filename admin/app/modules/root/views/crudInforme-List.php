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
                            $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',   'Name' => 'Email',    'Id' => 'Search_Email',    'Value' => '','Required' => 1,'Icon' => 'bx bx-mail-send']);
                            $data['Fnc_FormInputs']->formNumberSpinner([   'Placeholder' => 'Numero',  'Name' => 'Numero',   'Id' => 'Search_Numero',   'Value' => '','Required' => 1,'Min' => 1,'Max' => 20,'Step' => 1,'Ndecimal' => 0]);
                            $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder' => 'Rut',     'Name' => 'Rut',      'Id' => 'Search_Rut',      'Value' => '','Required' => 1,'Icon' => 'bi bi-person-circle']);
                            $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Patente', 'Name' => 'Patente',  'Id' => 'Search_Patente',  'Value' => '','Required' => 1,'Icon' => 'ri-car-fill']);
                            $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha',   'Name' => 'Fecha',    'Id' => 'Search_Fecha',    'Value' => '','Required' => 1,'Icon' => 'bi bi-calendar3']);
                            $data['Fnc_FormInputs']->formInput(['FormType' => 10, 'Placeholder' => 'Hora',    'Name' => 'Hora',     'Id' => 'Search_Hora',     'Value' => '','Required' => 1,'Icon' => 'bi bi-clock']);
                            $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Palabra', 'Name' => 'Palabra',  'Id' => 'Search_Palabra',  'Value' => '','Required' => 1]);

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
    <div class="modal-dialog modal-lg">
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
            refreshTables : 'true',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        SendDataForms(Metodo, Direccion, Informacion, Options);
    });
    /*********************************************************************/
    /*                        OPCIONES DE LA TABLA                       */
    /*********************************************************************/
    /******************************************/
    function TDviewBTN(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/view/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
</script>

