<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">

    <div class="card">
        <div class="card-body pt-3">

            <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit"><i class="bi bi-pencil-square"></i> Editar Datos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-img"><i class="bi bi-image"></i> Cambiar Imagen</button></li>
                <?php if($data['UserData']["entidadesListadoVerCargas"]==2){ ?>      <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-cargas"     onclick="tabCargasLoadList()"><i class="bi bi-person"></i> Cargas</button></li><?php } ?>
                <?php if($data['UserData']["entidadesListadoVerContactos"]==2){ ?>   <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-contactos"  onclick="tabContactosLoadList()"><i class="bi bi-book"></i> Contactos</button></li><?php } ?>
                <?php if($data['UserData']["entidadesListadoVerDocumentos"]==2){ ?>  <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-documentos" onclick="tabDocumentosLoadList()"><i class="bi bi-file-text"></i> Documentos</button></li><?php } ?>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-obs"        onclick="tabObsLoadList()"><i class="bi bi-chat-dots"></i> Observaciones</button></li>
            </ul>
            <div class="tab-content pt-2">

                <div class="tab-pane fade show active" id="resumen">
                    <?php require_once('entidadesListado-Resumen-Update.php'); ?>
                </div>

                <div class="tab-pane fade" id="resumen-edit">

                    <form id="FormEditData" name="FormEditData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //Se verifican si existen los datos
                                $x0  = $data['rowData']['idTipo'] ?? '';
                                $x1  = $data['rowData']['idTipoEntidad'] ?? '';
                                $x2  = $data['rowData']['Nombre'] ?? '';
                                $x3  = $data['rowData']['ApellidoPat'] ?? '';
                                $x4  = $data['rowData']['ApellidoMat'] ?? '';
                                $x5  = $data['rowData']['Nick'] ?? '';
                                $x6  = $data['rowData']['idSexo'] ?? '';
                                $x7  = $data['rowData']['FNacimiento'] ?? '';
                                $x8  = $data['rowData']['RazonSocial'] ?? '';
                                $x9  = $data['rowData']['Web'] ?? '';
                                $x10  = $data['rowData']['Giro'] ?? '';
                                $x11  = $data['rowData']['idCiudad'] ?? '';
                                $x12  = $data['rowData']['idComuna'] ?? '';
                                $x13  = $data['rowData']['Direccion'] ?? '';
                                $x14  = $data['rowData']['idSector'] ?? '';
                                $x15  = $data['rowData']['Email'] ?? '';
                                $x16  = $data['rowData']['Rut'] ?? '';
                                $x17  = $data['rowData']['Fono1'] ?? '';
                                $x18  = $data['rowData']['Fono2'] ?? '';
                                $x19  = $data['rowData']['RepLegalNombre'] ?? '';
                                $x20  = $data['rowData']['RepLegalRut'] ?? '';
                                $x21  = $data['rowData']['RepLegalEmail'] ?? '';
                                $x22  = $data['rowData']['RepLegalFono1'] ?? '';
                                $x23  = $data['rowData']['RepLegalFono2'] ?? '';
                                $x24  = $data['rowData']['Social_X'] ?? '';
                                $x25  = $data['rowData']['Social_Facebook'] ?? '';
                                $x26  = $data['rowData']['Social_Instagram'] ?? '';
                                $x27  = $data['rowData']['Social_Linkedin'] ?? '';
                                $x28  = $data['rowData']['idEstado'] ?? '';

                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Básicos']);
                                $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Tipo',              'Name'  => 'idTipo',         'Id' => 'Edit_idTipo',         'Value'  => $x0,'Required' => 2,'arrData' => $data['arrTipo']]);
                                $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Tipo Entidad',      'Name'  => 'idTipoEntidad',  'Id' => 'Edit_idTipoEntidad',  'Value'  => $x1,'Required' => 2,'arrData' => $data['arrTipoEntidad']]);
                                //Persona natural
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',            'Name'  => 'Nombre',        'Id' => 'Edit_Nombre',         'Value' => $x2,'Required' => 1]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Paterno',  'Name'  => 'ApellidoPat',   'Id' => 'Edit_ApellidoPat',    'Value' => $x3,'Required' => 1]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Materno',  'Name'  => 'ApellidoMat',   'Id' => 'Edit_ApellidoMat',    'Value' => $x4,'Required' => 1]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nick',              'Name'  => 'Nick',          'Id' => 'Edit_Nick',           'Value' => $x5,'Required' => 1]);
                                $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Sexo',              'Name'  => 'idSexo',        'Id' => 'Edit_idSexo',         'Value' => $x6,'Required' => 1,'arrData' => $data['arrSexo']]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Nacimiento',  'Name'  => 'FNacimiento',   'Id' => 'Edit_FNacimiento',    'Value' => $x7,'Required' => 1,'Icon' => 'bi bi-calendar3']);
                                //empresas
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Razón Social',      'Name'  => 'RazonSocial',    'Id' => 'Edit_RazonSocial',   'Value' => $x8, 'Required' => 1]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Web',               'Name'  => 'Web',            'Id' => 'Edit_Web',           'Value' => $x9, 'Required' => 1]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Giro',              'Name'  => 'Giro',           'Id' => 'Edit_Giro',          'Value' => $x10,'Required' => 1]);
                                //Comun
                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Comunes']);
                                $data['Fnc_FormInputs']->formSelectDepend([          'Placeholder1' => 'Ciudad',           'Name1' => 'idCiudad',     'Id1' => 'Edit_idCiudad',    'Value1' => $x11,'Required1' => 1,'arrData1' => $data['arrCiudad'],
                                                                                       'Placeholder2' => 'Comuna',           'Name2' => 'idComuna',     'Id2' => 'Edit_idComuna',    'Value2' => $x12,'Required2' => 1,'arrData2' => $data['arrComuna'],
                                                                                       'FormName' => 'FormEditData']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Dirección',         'Name' => 'Direccion',     'Id' => 'Edit_Direccion',   'Value' => $x13,'Required' => 1,'Icon' => 'bi bi-geo-alt-fill']);
                                $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Sector',            'Name' => 'idSector',      'Id' => 'Edit_idSector',    'Value' => $x14,'Required' => 1,'arrData' => $data['arrSector'], 'BASE' => $BASE]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',             'Name' => 'Email',         'Id' => 'Edit_Email',       'Value' => $x15,'Required' => 1,'Icon' => 'bx bx-mail-send']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder' => 'Rut',               'Name' => 'Rut',           'Id' => 'Edit_Rut',         'Value' => $x16,'Required' => 1,'Icon' => 'bi bi-person-circle']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Celular',           'Name' => 'Fono1',         'Id' => 'Edit_Fono1',       'Value' => $x17,'Required' => 1,'Icon' => 'bi bi-telephone-fill']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Teléfono',          'Name' => 'Fono2',         'Id' => 'Edit_Fono2',       'Value' => $x18,'Required' => 1,'Icon' => 'bi bi-telephone-fill']);

                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Representante Legal']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',            'Name' => 'RepLegalNombre',   'Id' => 'Edit_RepLegalNombre',   'Value' => $x19,'Required' => 1]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder' => 'Rut',               'Name' => 'RepLegalRut',      'Id' => 'Edit_RepLegalRut',      'Value' => $x20,'Required' => 1,'Icon' => 'bi bi-person-circle']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',             'Name' => 'RepLegalEmail',    'Id' => 'Edit_RepLegalEmail',    'Value' => $x21,'Required' => 1,'Icon' => 'bx bx-mail-send']);
                                $data['Fnc_FormInputs']->formPostData(4, 4, 'exclamation-circle', 0, 'Considerar que todos los números telefónicos ingresados deben iniciar con el +56');
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Celular',           'Name' => 'RepLegalFono1',    'Id' => 'Edit_RepLegalFono1',    'Value' => $x22,'Required' => 1,'Icon' => 'bi bi-telephone-fill']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Teléfono',          'Name' => 'RepLegalFono2',    'Id' => 'Edit_RepLegalFono2',    'Value' => $x23,'Required' => 1,'Icon' => 'bi bi-telephone-fill']);

                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Social']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'X (Twitter)', 'Name' => 'Social_X',          'Id' => 'Edit_Social_X',         'Value' => $x24, 'Required' => 1, 'Icon' => 'bi bi-x']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Facebook',    'Name' => 'Social_Facebook',   'Id' => 'Edit_Social_Facebook',  'Value' => $x25, 'Required' => 1, 'Icon' => 'bi bi-facebook']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Instagram',   'Name' => 'Social_Instagram',  'Id' => 'Edit_Social_Instagram', 'Value' => $x26, 'Required' => 1, 'Icon' => 'bi bi-instagram']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Linkedin',    'Name' => 'Social_Linkedin',   'Id' => 'Edit_Social_Linkedin',  'Value' => $x27, 'Required' => 1, 'Icon' => 'bi bi-linkedin']);

                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Administración']);
                                $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado',       'Name' => 'idEstado',   'Id' => 'Edit_idEstado',  'Value'  => $x28,'Required' => 2,'arrData' => $data['arrEstado']]);
                                //permite la modificacion de la contraseña en caso de ser utilizada
                                if($data['UserData']["entidadesListadoUsoPassword"]==2){
                                    $data['Fnc_FormInputs']->formPostData(1, 4, 'exclamation-circle', 0, '<strong>Contraseña: </strong> permite modificar arbitrariamente la contraseña.');
                                    $data['Fnc_FormInputs']->formInput(['FormType' => 3,  'Placeholder' => 'Contraseña',   'Name' => 'password',   'Id' => 'Edit_password',  'Value'  => '','Required' => 1,'Icon' => 'bi bi-key']);
                                }
                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEntidad','Value' => $data['rowData']['idEntidad'],'Required' => 2]);
                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <?php
                /****************************************************/
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']);
                //Se obtiene el nombre o la razón social
                $Entidad  = '';
                $Entidad .= !empty($data['rowData']['Nombre'])
                            ? $data['rowData']['ApellidoPat'].' '.$data['rowData']['ApellidoMat'].' '.$data['rowData']['Nombre']
                            : $data['rowData']['RazonSocial'];
                ?>

                <div class="tab-pane fade" id="resumen-img">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Imagen de <?php echo $Entidad; ?>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <?php
                    if(isset($data['rowData']['Direccion_img'])&&$data['rowData']['Direccion_img']!=''){ ?>
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-8 col-md-6 col-lg-6 col-xl-4 col-xxl-3">
                                <div class="d-flex justify-content-center">
                                    <img src="<?php echo $BASE.'/upload/'.$data['rowData']['Direccion_img']; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
                                </div>
                                <div class="d-flex justify-content-center pt-2">
                                    <button  onclick="delIMG('<?php echo $data['rowData']['Direccion_img']; ?>')" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Borrar Imagen</button>
                                </div>
                            </div>
                        </div>
                    <?php }else{ ?>
                        <div class="d-flex justify-content-center pt-3">
                            <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 col-xl-6 col-xxl-5">
                                <?php $data['Fnc_FormInputs']->formUploadIMG(['Name' => 'Direccion_img','URL' => $BASE.'/'.$data['UserAccess']['RouteAccess'].'/update','ExtraData' => '"idEntidad": '.$data['rowData']['idEntidad']]);?>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <?php if($data['UserData']["entidadesListadoVerCargas"]==2){ ?>
                    <div class="tab-pane fade" id="resumen-cargas">
                        <h5 class="card-title">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                Cargas de <?php echo $Entidad; ?>
                                <button type="button" class="btn btn-success"  onclick="tabCargasNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                            </div>
                        </h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive" id="tabCargasDataTable">

                        </div>
                    </div>
                <?php } ?>

                <?php if($data['UserData']["entidadesListadoVerContactos"]==2){ ?>
                    <div class="tab-pane fade" id="resumen-contactos">
                        <h5 class="card-title">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                Contactos de <?php echo $Entidad; ?>
                                <button type="button" class="btn btn-success"  onclick="tabContactosNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                            </div>
                        </h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive" id="tabContactosDataTable">

                        </div>
                    </div>
                <?php } ?>

                <?php if($data['UserData']["entidadesListadoVerDocumentos"]==2){ ?>
                    <div class="tab-pane fade" id="resumen-documentos">
                        <h5 class="card-title">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                Documentos de <?php echo $Entidad; ?>
                                <button type="button" class="btn btn-success"  onclick="tabDocumentosNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                            </div>
                        </h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive" id="tabDocumentosDataTable">

                        </div>
                    </div>
                <?php } ?>

                <div class="tab-pane fade" id="resumen-obs">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Observaciones de <?php echo $Entidad; ?>
                            <button type="button" class="btn btn-success"  onclick="tabObsNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabObsDataTable">

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

<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modalContent">

        </div>
    </div>
</div>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    $("#FormEditData").submit(function(e) {
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
        //verifico el resultado
        if(validatorResult.valid===false){
            return !!validatorResult.valid;
        }else{
            e.preventDefault();
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Metodo      = 'POST';
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/update'; ?>';
            let Informacion = $("#FormEditData").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>'},
                ],
                showNoti:'Datos Editados Correctamente',
                triggerTab:'.nav-tabs button[data-bs-target="#resumen"]',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /******************************************/
    //Oculto
    document.getElementById('div_Edit_Nombre').style.display          = 'none';
    document.getElementById('div_Edit_ApellidoPat').style.display     = 'none';
    document.getElementById('div_Edit_ApellidoMat').style.display     = 'none';
    document.getElementById('div_Edit_idSexo').style.display          = 'none';
    document.getElementById('div_Edit_FNacimiento').style.display     = 'none';
    document.getElementById('div_Edit_RazonSocial').style.display     = 'none';
    document.getElementById('div_Edit_Web').style.display             = 'none';
    document.getElementById('div_Edit_Giro').style.display            = 'none';
    document.getElementById('div_Edit_RepLegalNombre').style.display  = 'none';
    document.getElementById('div_Edit_RepLegalRut').style.display     = 'none';
    document.getElementById('div_Edit_RepLegalEmail').style.display   = 'none';
    document.getElementById('div_Edit_RepLegalFono1').style.display   = 'none';
    document.getElementById('div_Edit_RepLegalFono2').style.display   = 'none';

    /**********************************************************************/
    //cargo
    document.getElementById("Edit_idTipoEntidad").onchange = function() {cngFnc_Edit_idTipoEntidad()}
    //al cargar
    $(document).ready(function(){cngFnc_Edit_idTipoEntidad();});
    //Ejecutar logica
    function cngFnc_Edit_idTipoEntidad() {
        //obtengo los valores
        let Edit_idTipoEntidad = $("#Edit_idTipoEntidad").val();
        //selecciono
        if (Edit_idTipoEntidad != "") {
            //selecciono
            switch (Edit_idTipoEntidad) {
                //Persona Natural
                case '1':
                    document.getElementById("Edit_Nombre").required                   = true;
                    document.getElementById("Edit_ApellidoPat").required              = true;
                    document.getElementById("Edit_RazonSocial").required              = false;
                    document.getElementById('div_Edit_Nombre').style.display          = '';
                    document.getElementById('div_Edit_ApellidoPat').style.display     = '';
                    document.getElementById('div_Edit_ApellidoMat').style.display     = '';
                    document.getElementById('div_Edit_idSexo').style.display          = '';
                    document.getElementById('div_Edit_FNacimiento').style.display     = '';
                    document.getElementById('div_Edit_RazonSocial').style.display     = 'none';
                    document.getElementById('div_Edit_Web').style.display             = 'none';
                    document.getElementById('div_Edit_Giro').style.display            = 'none';
                    document.getElementById('div_Edit_RepLegalNombre').style.display  = 'none';
                    document.getElementById('div_Edit_RepLegalRut').style.display     = 'none';
                    document.getElementById('div_Edit_RepLegalEmail').style.display   = 'none';
                    document.getElementById('div_Edit_RepLegalFono1').style.display   = 'none';
                    document.getElementById('div_Edit_RepLegalFono2').style.display   = 'none';
                    break;
                //Empresas
                case '2':
                    document.getElementById("Edit_Nombre").required                   = false;
                    document.getElementById("Edit_ApellidoPat").required              = false;
                    document.getElementById("Edit_RazonSocial").required              = true;
                    document.getElementById('div_Edit_Nombre').style.display          = 'none';
                    document.getElementById('div_Edit_ApellidoPat').style.display     = 'none';
                    document.getElementById('div_Edit_ApellidoMat').style.display     = 'none';
                    document.getElementById('div_Edit_idSexo').style.display          = 'none';
                    document.getElementById('div_Edit_FNacimiento').style.display     = 'none';
                    document.getElementById('div_Edit_RazonSocial').style.display     = '';
                    document.getElementById('div_Edit_Web').style.display             = '';
                    document.getElementById('div_Edit_Giro').style.display            = '';
                    document.getElementById('div_Edit_RepLegalNombre').style.display  = '';
                    document.getElementById('div_Edit_RepLegalRut').style.display     = '';
                    document.getElementById('div_Edit_RepLegalEmail').style.display   = '';
                    document.getElementById('div_Edit_RepLegalFono1').style.display   = '';
                    document.getElementById('div_Edit_RepLegalFono2').style.display   = '';
                    break;
                //el resto
                default:
                    document.getElementById("Edit_Nombre").required                   = false;
                    document.getElementById("Edit_ApellidoPat").required              = false;
                    document.getElementById("Edit_RazonSocial").required              = false;
                    document.getElementById('div_Edit_Nombre').style.display          = 'none';
                    document.getElementById('div_Edit_ApellidoPat').style.display     = 'none';
                    document.getElementById('div_Edit_ApellidoMat').style.display     = 'none';
                    document.getElementById('div_Edit_idSexo').style.display          = 'none';
                    document.getElementById('div_Edit_FNacimiento').style.display     = 'none';
                    document.getElementById('div_Edit_RazonSocial').style.display     = 'none';
                    document.getElementById('div_Edit_Web').style.display             = 'none';
                    document.getElementById('div_Edit_Giro').style.display            = 'none';
                    document.getElementById('div_Edit_RepLegalNombre').style.display  = 'none';
                    document.getElementById('div_Edit_RepLegalRut').style.display     = 'none';
                    document.getElementById('div_Edit_RepLegalEmail').style.display   = 'none';
                    document.getElementById('div_Edit_RepLegalFono1').style.display   = 'none';
                    document.getElementById('div_Edit_RepLegalFono2').style.display   = 'none';
                    break;
            }
        //si el select esta vacio
        }else{
            document.getElementById("Edit_Nombre").required                   = false;
            document.getElementById("Edit_ApellidoPat").required              = false;
            document.getElementById("Edit_RazonSocial").required              = false;
            document.getElementById('div_Edit_Nombre').style.display          = 'none';
            document.getElementById('div_Edit_ApellidoPat').style.display     = 'none';
            document.getElementById('div_Edit_ApellidoMat').style.display     = 'none';
            document.getElementById('div_Edit_idSexo').style.display          = 'none';
            document.getElementById('div_Edit_FNacimiento').style.display     = 'none';
            document.getElementById('div_Edit_RazonSocial').style.display     = 'none';
            document.getElementById('div_Edit_Web').style.display             = 'none';
            document.getElementById('div_Edit_Giro').style.display            = 'none';
            document.getElementById('div_Edit_RepLegalNombre').style.display  = 'none';
            document.getElementById('div_Edit_RepLegalRut').style.display     = 'none';
            document.getElementById('div_Edit_RepLegalEmail').style.display   = 'none';
            document.getElementById('div_Edit_RepLegalFono1').style.display   = 'none';
            document.getElementById('div_Edit_RepLegalFono2').style.display   = 'none';
        }
    }
    /*********************************************************************/
    /*                             IMAGENES                              */
    /*********************************************************************/
    /******************************************/
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
                    "idEntidad": <?php echo $data['rowData']['idEntidad']; ?>,
                    "Direccion_img": File
                };
                const Options     = {
                    Destino:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }

    <?php if($data['UserData']["entidadesListadoVerCargas"]==2){ ?>
        /*********************************************************************/
        /*                             CARGAS                                */
        /*********************************************************************/
        //Variables
        let CargasLoad = 0;
        /******************************************/
        function tabCargasLoadList() {
            //Comparo
            if(CargasLoad===0){
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Div       = '#tabCargasDataTable';
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/cargas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>';
                const Options = {
                    closeObject:'#PDloader',
                    refreshTables:'true',
                };
                //Se envian los datos al formulario
                UpdateContentId(Div, URL, Options);
                //Indico que esta cargado
                CargasLoad = 1;
            }
        }
        /******************************************/
        function tabCargasNew(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/cargas/new/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabCargasView(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/cargas/view/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabCargasEdit(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/cargas/getID/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabCargasDel(ID, Dato) {
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
                    let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/cargas'; ?>';
                    let Informacion = {"idCargas": ID};
                    const Options     = {
                        UpdateDiv : [
                            {Div:'#tabCargasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/cargas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
                        ],
                        showNoti:'Dato Borrado Correctamente',
                        closeObject:'#PDloader',
                    };
                    //Se envian los datos al formulario
                    SendDataForms(Metodo, Direccion, Informacion, Options);
                }
            });
        }
    <?php } ?>

    <?php if($data['UserData']["entidadesListadoVerContactos"]==2){ ?>
        /*********************************************************************/
        /*                             CONTACTOS                             */
        /*********************************************************************/
        //Variables
        let ContactosLoad = 0;
        /******************************************/
        function tabContactosLoadList() {
            //Comparo
            if(ContactosLoad===0){
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Div       = '#tabContactosDataTable';
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/contactos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>';
                const Options = {
                    closeObject:'#PDloader',
                    refreshTables:'true',
                };
                //Se envian los datos al formulario
                UpdateContentId(Div, URL, Options);
                //Indico que esta cargado
                ContactosLoad = 1;
            }
        }
        /******************************************/
        function tabContactosNew(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/contactos/new/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabContactosView(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/contactos/view/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabContactosEdit(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/contactos/getID/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabContactosDel(ID, Dato) {
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
                    let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/contactos'; ?>';
                    let Informacion = {"idContacto": ID};
                    const Options     = {
                        UpdateDiv : [
                            {Div:'#tabContactosDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/contactos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
                        ],
                        showNoti:'Dato Borrado Correctamente',
                        closeObject:'#PDloader',
                    };
                    //Se envian los datos al formulario
                    SendDataForms(Metodo, Direccion, Informacion, Options);
                }
            });
        }
    <?php } ?>

    <?php if($data['UserData']["entidadesListadoVerDocumentos"]==2){ ?>
        /*********************************************************************/
        /*                            DOCUMENTOS                             */
        /*********************************************************************/
        //Variables
        let DocumentosLoad = 0;
        /******************************************/
        function tabDocumentosLoadList() {
            //Comparo
            if(DocumentosLoad===0){
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Div       = '#tabDocumentosDataTable';
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>';
                const Options = {
                    closeObject:'#PDloader',
                    refreshTables:'true',
                };
                //Se envian los datos al formulario
                UpdateContentId(Div, URL, Options);
                //Indico que esta cargado
                DocumentosLoad = 1;
            }
        }
        /******************************************/
        function tabDocumentosNew(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/new/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabDocumentosView(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/view/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabDocumentosEdit(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/getID/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabDocumentosDel(ID, Dato) {
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
                    let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos'; ?>';
                    let Informacion = {"idDocumentos": ID};
                    const Options     = {
                        UpdateDiv : [
                            {Div:'#tabDocumentosDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
                        ],
                        showNoti:'Dato Borrado Correctamente',
                        closeObject:'#PDloader',
                    };
                    //Se envian los datos al formulario
                    SendDataForms(Metodo, Direccion, Informacion, Options);
                }
            });
        }
    <?php } ?>
    /*********************************************************************/
    /*                          OBSERVACIONES                            */
    /*********************************************************************/
    //Variables
    let ObsLoad = 0;
    /******************************************/
    function tabObsLoadList() {
        //Comparo
        if(ObsLoad===0){
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#tabObsDataTable';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>';
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
    /******************************************/
    function tabObsNew(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/new/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabObsView(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/view/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabObsEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
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
                        {Div:'#tabObsDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
                    ],
                    showNoti:'Dato Borrado Correctamente',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
</script>