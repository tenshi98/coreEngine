<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

// Se obtiene el ID
$BodegasID = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idBodegas']);
?>
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">

    <div class="card">

        <?php
        // Datos
        $Title          = 'Resumen Bodega';
        $SubTitle       = 'Resumen de la Bodega '.($data['rowData']['Nombre'] ?? '');
        $Text           = 'Información general y datos asociados a la bodega';
        $IMG            = $BASE.'/img/img-backgrounds/Bodegas_1.jpg';
        $Title_color    = 25;
        $SubTitle_color = 17;
        $Text_color     = 40;
        // Imprimo
        echo $data['Fnc_WidgetsViews']->headerProfile($Title, $SubTitle, $Text, $IMG, $Title_color, $SubTitle_color, $Text_color);
        ?>

        <div class="card-body pt-3">

            <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit"><i class="bi bi-pencil-square"></i> Editar Datos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-img"><i class="bi bi-image"></i> Cambiar Imagen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-obs" onclick="tabObsLoadList()"><i class="bi bi-chat-dots"></i> Observaciones</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-distribucion" onclick="distLoadEstructura()"><i class="bi bi-diagram-3"></i> Distribución</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-mapa" onclick="distLoadMapa()"><i class="bi bi-grid-3x3-gap"></i> Mapa</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-visual" onclick="distLoadVisual()"><i class="bi bi-box-seam"></i> Visual</button></li>
            </ul>
            <div class="tab-content pt-2">

                <div class="tab-pane fade show active" id="resumen">
                    <?php require_once('bodegasListado-Resumen-Update.php'); ?>
                </div>

                <div class="tab-pane fade" id="resumen-edit">

                    <form id="FormEditData" name="FormEditData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Básicos', 'Clase' => 'box-title text-color-red-dark']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Nombre',     'Name'  => 'Nombre',    'Id'  => 'Edit_Nombre',     'Value'  => ($data['rowData']['Nombre'] ?? ''),    'Required'  => 2]);
                                $data['Fnc_FormInputs']->formSelectDepend([           'Placeholder1' => 'Ciudad',     'Name1' => 'idCiudad',  'Id1' => 'Edit_idCiudad',   'Value1' => ($data['rowData']['idCiudad'] ?? ''),  'Required1' => 1,'arrData1' => $data['arrCiudad'],
                                                                                      'Placeholder2' => 'Comuna',     'Name2' => 'idComuna',  'Id2' => 'Edit_idComuna',   'Value2' => ($data['rowData']['idComuna'] ?? ''),  'Required2' => 1,'arrData2' => $data['arrComuna']]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Dirección',  'Name'  => 'Direccion', 'Id'  => 'Edit_Direccion',  'Value'  => ($data['rowData']['Direccion'] ?? ''), 'Required'  => 1,'Icon' => 'bi bi-geo-alt-fill']);

                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Administración', 'Clase' => 'box-title text-color-red-dark']);
                                $data['Fnc_FormInputs']->formSelect([  'Placeholder' => 'Estado', 'Name' => 'idEstado',  'Id' => 'Edit_idEstado',  'Value'  => ($data['rowData']['idEstado'] ?? ''),'Required' => 2,'arrData' => $data['arrEstado']]);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idBodegas','Value' => $data['rowData']['idBodegas'],'Required' => 2]);
                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <div class="tab-pane fade" id="resumen-img">
                    <div class="d-flex justify-content-center pt-4">
                        <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                            <h4 class="title_h4 box-title text-color-red-dark">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                    Imagen de <?php echo $data['rowData']['Nombre']; ?>
                                </div>
                            </h4>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <?php
                    if(isset($data['rowData']['Direccion_img'])&&$data['rowData']['Direccion_img']!=''){ ?>
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-8 col-md-6 col-lg-6 col-xl-4 col-xxl-3">
                                <div class="d-flex justify-content-center">
                                    <img src="<?php echo $data['UserData']['MainPathUrl'].$data['rowData']['Direccion_img']; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
                                </div>
                                <div class="d-flex justify-content-center pt-2">
                                    <button  onclick="delIMG('<?php echo $data['rowData']['Direccion_img']; ?>')" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Borrar Imagen</button>
                                </div>
                            </div>
                        </div>
                    <?php }else{ ?>
                        <div class="d-flex justify-content-center pt-3">
                            <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 col-xl-6 col-xxl-5">
                                <?php
                                // Se construye el dato
                                $ExtraData  = '"idBodegas": '.$data['rowData']['idBodegas'];
                                $ExtraData .= ',"idEstado": '.$data['rowData']['idEstado'];
                                $ExtraData .= ',"Nombre": "'.$data['rowData']['Nombre'].'"';
                                // Se imprime el dato
                                $data['Fnc_FormInputs']->formUploadIMG(['Name' => 'Direccion_img','URL' => $BASE.'/'.$data['UserAccess']['RouteAccess'].'/update','ExtraData' => $ExtraData]);?>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="tab-pane fade" id="resumen-obs">
                    <h5 class="text-color-red-dark">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Observaciones de <?php echo $data['rowData']['Nombre']; ?>
                            <button type="button" class="btn btn-success"  onclick="tabObsNew('<?php echo $BodegasID['data']; ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabObsDataTable">

                    </div>
                </div>

                <div class="tab-pane fade" id="resumen-distribucion">
                    <h5 class="text-color-red-dark">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Distribución de <?php echo $data['rowData']['Nombre']; ?>
                            <?php if($data['UserAccess']['LevelAccess']>=2){ ?>
                            <button type="button" class="btn btn-success" onclick="distZonaNew('<?php echo $BodegasID['data']; ?>')"><i class="bi bi-plus-lg"></i> Crear Zona</button>
                            <?php } ?>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div id="tabDistEstructura">

                    </div>
                </div>

                <div class="tab-pane fade" id="resumen-mapa">
                    <h5 class="text-color-red-dark">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Mapa de posiciones de <?php echo $data['rowData']['Nombre']; ?>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div id="tabDistMapa">

                    </div>
                </div>

                <div class="tab-pane fade" id="resumen-visual">
                    <h5 class="text-color-red-dark">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Visual de <?php echo $data['rowData']['Nombre']; ?>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div id="tabDistVisual">

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<div class="clearfix"></div>
<div class="d-grid gap-2 d-md-flex justify-content-md-end">
    <a href="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/listAll'; ?>" class="btn btn-danger float-end"><i class="bi bi-arrow-left-circle"></i> Volver</a>
</div>
<div class="clearfix"></div>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    /************************************/
    $("#FormEditData").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/update'; ?>';
            let Informacion = $("#FormEditData").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate/'.$BodegasID['data']; ?>'},
                ],
                showNoti:'Datos Editados Correctamente',
                triggerTab:'.nav-tabs button[data-bs-target="#resumen"]',
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });

    /*********************************************************************/
    /*                             IMAGENES                              */
    /*********************************************************************/
    /************************************/
    function delIMG(File) {
        Swal.fire({
            title: "Borrar Imagen",
            text: "Esta a punto de borrar la imagen, ¿Desea continuar?",
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, borrar",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'PUT';
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/delFiles'; ?>';
                let Informacion = {
                    "idBodegas": <?php echo $data['rowData']['idBodegas']; ?>,
                    "Direccion_img": File
                };
                const Options     = {
                    Destino:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$BodegasID['data']; ?>',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }

    /*********************************************************************/
    /*                          OBSERVACIONES                            */
    /*********************************************************************/
    // Variables
    let ObsLoad = 0;
    /************************************/
    function tabObsLoadList() {
        //Comparo
        if(ObsLoad===0){
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#tabObsDataTable';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/updateList/'.$BodegasID['data']; ?>';
            const Options = {
                closeObject:'#PDloader',
                refreshTables:'true',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
            //Indico que esta cargado
            ObsLoad = 1;
        }
    }
    /************************************/
    function tabObsNew(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/new/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function tabObsView(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/view/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function tabObsEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function tabObsDel(ID, Dato) {
        Swal.fire({
            title: "Borrar Dato",
            text: "Esta a punto de eliminar el dato " + Dato + ", ¿Desea continuar?",
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, borrar",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((result2) => {
            if (result2.isConfirmed) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'DELETE';
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones'; ?>';
                let Informacion = {"idObservaciones": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabObsDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/updateList/'.$BodegasID['data']; ?>', refreshTbl:'true'}
                    ],
                    showNoti:'Dato Borrado Correctamente',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
    /*********************************************************************/
    /*                        DISTRIBUCION FISICA                        */
    /*********************************************************************/
    /************************************/
    function distLoadEstructura() {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#tabDistEstructura';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/distribucion/estructura/'.$BodegasID['data']; ?>';
        const Options = {
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function distLoadMapa() {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#tabDistMapa';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/distribucion/mapa/'.$BodegasID['data']; ?>';
        const Options = {
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    // three.js (carga unica, diferida) para la vista visual 3D
    function distLoadVisual() {
        //Destruyo la escena anterior si existia (libera WebGL/RAF)
        if (window.bodegaVisual3D && typeof window.bodegaVisual3D.destroy === 'function') {
            window.bodegaVisual3D.destroy();
        }
        //Cargo el loader
        $('#PDloader').show();
        //Direccion base de los vendor
        let VendorBase = '<?php echo $BASE; ?>/vendor/threejs';
        //Cargo three.js + OrbitControls en secuencia (solo la primera vez)
        function loadScript(Src) {
            return new Promise(function(Resolve, Reject){
                if (document.querySelector('script[src="'+Src+'"]')) { Resolve(); return; }
                let s = document.createElement('script');
                s.src = Src;
                s.onload  = Resolve;
                s.onerror = function(){ Reject(new Error(Src)); };
                document.head.appendChild(s);
            });
        }
        loadScript(VendorBase+'/three.min.js')
            .then(function(){ return loadScript(VendorBase+'/OrbitControls.js'); })
            .then(function(){
                //Ejecuto
                let Div       = '#tabDistVisual';
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/distribucion/visual/'.$BodegasID['data']; ?>';
                const Options = {
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                UpdateContentId(Div, URL, Options);
            })
            .catch(function(){
                //Cierro el loader y aviso
                $('#PDloader').hide();
                if (typeof notiBubble === 'function') {
                    notiBubble('No se pudo cargar three.js para la vista 3D', 'error', true);
                }
            });
    }
    /************************************/
    function distShowMapa() {
        //Se activa la pestana del mapa
        $('.nav-tabs button[data-bs-target="#resumen-mapa"]').tab('show');
        //Se carga el mapa
        distLoadMapa();
    }

    /*********************************************************************/
    /*                              ZONAS                                */
    /*********************************************************************/
    /************************************/
    function distZonaNew(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/new/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function distZonaView(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/view/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function distZonaEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function distZonaDel(ID, idZona, Nombre) {
        Swal.fire({
            title: "Borrar Zona",
            text: "Esta a punto de eliminar la zona " + Nombre + " y toda su estructura (pasillos, racks y posiciones), ¿Desea continuar?",
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, borrar",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((resultZona) => {
            if (resultZona.isConfirmed) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'DELETE';
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas'; ?>';
                let Informacion = {"idZona": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabDistEstructura', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/distribucion/estructura/'; ?>'+BodegasID['data']}
                    ],
                    showNoti:'Zona Borrada Correctamente',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
    /************************************/
    function distZonaTogglePasillos(ID, idZona) {
        //Variables
        let Contenedor = '#tabPasillosZ'+idZona;
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = Contenedor;
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos/updateList/'; ?>'+ID;
        const Options = {
            refreshTables:'true',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
        //Se muestra el contenedor
        $(Contenedor).show();
    }

    /*********************************************************************/
    /*                             PASILLOS                              */
    /*********************************************************************/
    /************************************/
    function distPasilloNew(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos/new/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function distPasilloView(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos/view/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function distPasilloEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function distPasilloDel(ID, idPasillo, idZona, Nombre) {
        Swal.fire({
            title: "Borrar Pasillo",
            text: "Esta a punto de eliminar el pasillo " + Nombre + " y toda su estructura (racks y posiciones), ¿Desea continuar?",
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, borrar",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((resultPas) => {
            if (resultPas.isConfirmed) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'DELETE';
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos'; ?>';
                let Informacion = {"idPasillo": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabPasillosZ'+idZona, fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos/updateList/'; ?>'+ID, refreshTbl:'true'}
                    ],
                    showNoti:'Pasillo Borrado Correctamente',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
    /************************************/
    function distPasilloToggleRacks(ID, idPasillo) {
        //Variables
        let Contenedor = '#tabRacksP'+idPasillo;
        let Fila       = '#tabRacksRowP'+idPasillo;
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = Contenedor;
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos/racks/updateList/'; ?>'+ID;
        const Options = {
            refreshTables:'true',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
        //Se muestra el contenedor
        $(Fila).show();
    }

    /*********************************************************************/
    /*                              RACKS                                */
    /*********************************************************************/
    /************************************/
    function distRackNew(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos/racks/new/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function distRackView(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos/racks/view/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function distRackEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos/racks/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function distRackDel(ID, idPasillo, IDPasillo, Nombre) {
        Swal.fire({
            title: "Borrar Rack",
            text: "Esta a punto de eliminar el rack " + Nombre + " y sus posiciones, ¿Desea continuar?",
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, borrar",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((resultRack) => {
            if (resultRack.isConfirmed) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'DELETE';
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos/racks'; ?>';
                let Informacion = {"idRack": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabRacksP'+idPasillo, fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos/racks/updateList/'; ?>'+IDPasillo, refreshTbl:'true'}
                    ],
                    showNoti:'Rack Borrado Correctamente',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }

    /*********************************************************************/
    /*                           POSICIONES                              */
    /*********************************************************************/
    /************************************/
    function distPosicionView(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/distribucion/posicion/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }

</script>
