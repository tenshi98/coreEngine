<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">

    <div class="card">
        <div class="card-body pt-3">

            <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit_1"><i class="bi bi-pencil-square"></i> Editar Datos Basicos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit_2"><i class="bi bi-bookmark-check"></i> Editar Contacto</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit_3"><i class="bi bi-person-square"></i> Editar Representante</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit_4"><i class="bi bi-puzzle"></i> Editar APIS</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit_5"><i class="bi bi-pencil-square"></i> Editar Configuracion</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit_6"><i class="bi bi-image"></i> Cambiar Imagen</button></li>
            </ul>
            <div class="tab-content pt-2">

                <div class="tab-pane fade show active" id="resumen">
                    <?php require_once('sistemaOpciones-Resumen-Update.php'); ?>
                </div>

                <div class="tab-pane fade" id="resumen-edit_1">

                    <form id="FormEditData_1" name="FormEditData_1" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Datos Basicos', 'Clase' => 'box-title text-color-red-dark']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Nombre',     'Name'  => 'Sistema_Nombre',    'Value'  => $data['rowData']['Sistema_Nombre'] ?? '',    'Required'  => 2]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder'  => 'Email',      'Name'  => 'Sistema_Email',     'Value'  => $data['rowData']['Sistema_Email'] ?? '',     'Required'  => 1, 'Icon'     => 'bx bx-mail-send']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder'  => 'Rut',        'Name'  => 'Sistema_Rut',       'Value'  => $data['rowData']['Sistema_Rut'] ?? '',       'Required'  => 1, 'Icon'     => 'bi bi-person-circle']);
                                $data['Fnc_FormInputs']->formSelectDepend([           'Placeholder1' => 'Ciudad',     'Name1' => 'Sistema_idCiudad',  'Value1' => $data['rowData']['Sistema_idCiudad'] ?? '',  'Required1' => 1, 'arrData1' => $data['arrCiudad'],
                                                                                      'Placeholder2' => 'Comuna',     'Name2' => 'Sistema_idComuna',  'Value2' => $data['rowData']['Sistema_idComuna'] ?? '',  'Required2' => 1, 'arrData2' => $data['arrComuna']]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Dirección',  'Name'  => 'Sistema_Direccion', 'Value'  => $data['rowData']['Sistema_Direccion'] ?? '', 'Required'  => 1, 'Icon'     => 'bi bi-geo-alt-fill']);
                                $data['Fnc_FormInputs']->formSelectFilter([           'Placeholder'  => 'Tema',       'Name'  => 'Sistema_idTema',    'Value'  => $data['rowData']['Sistema_idTema'] ?? '',    'Required'  => 2, 'arrData'  => $data['arrTemas'], 'BASE' => $BASE]);

                                //Se condiciona el uso de Whatsapp
                                if($data['UserData']["sistemaUsoWhatsapp"]==2){
                                    $data['Fnc_FormInputs']->formPostData(4, 4, 'exclamation-circle', 0, 'El numero debe ingresarse iniciando con 56, sin el simbolo + y sin espacios ni separaciones');
                                    $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder'  => 'Fono Noti Whatsapp',  'Name' => 'Sistema_NotiWhatsapp',  'Value' => $data['rowData']['Sistema_NotiWhatsapp'] ?? '', 'Required' => 1, 'Icon' => 'bi bi-telephone-fill']);
                                }

                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Social', 'Clase' => 'box-title text-color-red-dark']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'X (Twitter)', 'Name' => 'Social_X',         'Value' => $data['rowData']['Social_X'] ?? '',         'Required' => 1, 'Icon' => 'bi bi-x']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Facebook',    'Name' => 'Social_Facebook',  'Value' => $data['rowData']['Social_Facebook'] ?? '',  'Required' => 1, 'Icon' => 'bi bi-facebook']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Instagram',   'Name' => 'Social_Instagram', 'Value' => $data['rowData']['Social_Instagram'] ?? '', 'Required' => 1, 'Icon' => 'bi bi-instagram']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Linkedin',    'Name' => 'Social_Linkedin',  'Value' => $data['rowData']['Social_Linkedin'] ?? '',  'Required' => 1, 'Icon' => 'bi bi-linkedin']);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idSistema','Value' => $data['rowData']['idSistema'],'Required' => 2]);
                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <div class="tab-pane fade" id="resumen-edit_2">

                    <form id="FormEditData_2" name="FormEditData_2" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Datos de Contacto', 'Clase' => 'box-title text-color-red-dark']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre', 'Name' => 'Contacto_Nombre',   'Value' => $data['rowData']['Contacto_Nombre'] ?? '', 'Required' => 1]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Fono 1', 'Name' => 'Contacto_Fono1',    'Value' => $data['rowData']['Contacto_Fono1'] ?? '',  'Required' => 1, 'Icon' => 'bi bi-telephone-fill']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Fono 2', 'Name' => 'Contacto_Fono2',    'Value' => $data['rowData']['Contacto_Fono2'] ?? '',  'Required' => 1, 'Icon' => 'bi bi-telephone-fill']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Fax',    'Name' => 'Contacto_Fax',      'Value' => $data['rowData']['Contacto_Fax'] ?? '',    'Required' => 1, 'Icon' => 'bi bi-telephone-fill']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',  'Name' => 'Contacto_Email',    'Value' => $data['rowData']['Contacto_Email'] ?? '',  'Required' => 1, 'Icon' => 'bx bx-mail-send']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Web',    'Name' => 'Contacto_Web',      'Value' => $data['rowData']['Contacto_Web'] ?? '',    'Required' => 1, 'Icon' => 'ri-edge-fill']);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idSistema','Value' => $data['rowData']['idSistema'],'Required' => 2]);
                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <div class="tab-pane fade" id="resumen-edit_3">

                    <form id="FormEditData_3" name="FormEditData_3" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Datos del Representante', 'Clase' => 'box-title text-color-red-dark']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre', 'Name' => 'RepresentanteNombre',  'Value' => $data['rowData']['RepresentanteNombre'] ?? '', 'Required' => 1]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder' => 'Rut',    'Name' => 'RepresentanteRut',     'Value' => $data['rowData']['RepresentanteRut'] ?? '',    'Required' => 1, 'Icon' => 'bi bi-person-circle']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Fono 1', 'Name' => 'RepresentanteFono',    'Value' => $data['rowData']['RepresentanteFono'] ?? '',   'Required' => 1, 'Icon' => 'bi bi-telephone-fill']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',  'Name' => 'RepresentanteEmail',   'Value' => $data['rowData']['RepresentanteEmail'] ?? '',  'Required' => 1, 'Icon' => 'bx bx-mail-send']);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idSistema','Value' => $data['rowData']['idSistema'],'Required' => 2]);
                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <div class="tab-pane fade" id="resumen-edit_4">

                    <form id="FormEditData_4" name="FormEditData_4" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'APIS', 'Clase' => 'box-title text-color-red-dark']);
                                //Se condiciona el motor de mapas
                                switch ($data['UserData']["Config_motorMap"]) {
                                    case 1:$data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'API GoogleMaps', 'Name' => 'Config_API_GoogleMaps', 'Value' => $data['rowData']['Config_API_GoogleMaps'] ?? '', 'Required' => 1, 'Icon' => 'bi bi-puzzle']); break;
                                }
                                //Se condiciona el uso de Whatsapp
                                if($data['UserData']["sistemaUsoWhatsapp"]==2){
                                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'API Whatsapp Token',       'Name' => 'Config_WhatsappToken',       'Value' => $data['rowData']['Config_WhatsappToken'] ?? '',      'Required' => 1, 'Icon' => 'bi bi-puzzle']);
                                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'API Whatsapp Instance Id', 'Name' => 'Config_WhatsappInstanceId',  'Value' => $data['rowData']['Config_WhatsappInstanceId'] ?? '', 'Required' => 1, 'Icon' => 'bi bi-puzzle']);
                                }
                                //Se condiciona el uso de IA
                                if($data['UserData']["Config_IA_Uso"]==2){
                                    //se dibujan los inputs
                                    $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Uso de IA', 'Clase' => 'box-title text-color-red-dark']);
                                    $data['Fnc_FormInputs']->formSelectFilter([           'Placeholder' => 'Provider',     'Name' => 'Config_IA_Provider',   'Value' => $data['rowData']['Config_IA_Provider'] ?? '', 'Required' => 1, 'arrData'  => $data['arrIAProvider'], 'BASE' => $BASE]);
                                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'API Key',      'Name' => 'Config_IA_ApiKey',     'Value' => $data['rowData']['Config_IA_ApiKey'] ?? '',   'Required' => 1, 'Icon' => 'bi bi-puzzle']);
                                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'API Model',    'Name' => 'Config_IA_Model',      'Value' => $data['rowData']['Config_IA_Model'] ?? '',    'Required' => 1, 'Icon' => 'bi bi-puzzle']);
                                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'API Base URL', 'Name' => 'Config_IA_Base_URL',   'Value' => $data['rowData']['Config_IA_Base_URL'] ?? '', 'Required' => 1, 'Icon' => 'bi bi-puzzle']);
                                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre IA',    'Name' => 'Config_IA_Name',       'Value' => $data['rowData']['Config_IA_Name'] ?? '',     'Required' => 1, 'Icon' => 'bi bi-puzzle']);
                                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Tono IA',      'Name' => 'Config_IA_Tone',       'Value' => $data['rowData']['Config_IA_Tone'] ?? '',     'Required' => 1, 'Icon' => 'bi bi-puzzle']);
                                    $Title = 'Uso Memoria Cache IA';
                                    $Info  = 'Uso de memoria de cache en la Inteligencia Artificial (solo Gemini)';
                                    $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'Config_IA_UsoCache',  'Value' => $data['rowData']['Config_IA_UsoCache'] ?? '',  'Required' => 1,'Color' => 3]);
                                }

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idSistema','Value' => $data['rowData']['idSistema'],'Required' => 2]);
                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <div class="tab-pane fade" id="resumen-edit_5">

                    <form id="FormEditData_5" name="FormEditData_5" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">

                        <div class="row">

                            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
                                <div class="card">
                                    <div class="card-header">Pantalla Principal</div>
                                    <div class="card-body">
                                        <?php
                                        /********************************/
                                        $Title = 'Mostrar Widget Meteorologico';
                                        $Info  = 'Permite la opcion de mostrar el widget en la pantalla principal';
                                        $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'Config_Principal_Meteo',  'Value' => $data['rowData']['Config_Principal_Meteo'] ?? '',  'Required' => 1,'Color' => 3]);
                                        /********************************/
                                        $Title = 'Mostrar Widget Radio';
                                        $Info  = 'Permite la opcion de mostrar el widget en la pantalla principal';
                                        $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'Config_Principal_Radio',  'Value' => $data['rowData']['Config_Principal_Radio'] ?? '',  'Required' => 1,'Color' => 3]);
                                        /********************************/
                                        $Title = 'Mostrar Widget Feed';
                                        $Info  = 'Permite la opcion de mostrar el widget en la pantalla principal';
                                        $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'Config_Principal_Feed',  'Value' => $data['rowData']['Config_Principal_Feed'] ?? '',  'Required' => 1,'Color' => 3]);
                                        /********************************/
                                        $Title = 'URL Feed';
                                        $Info  = 'La URL con el feed de noticias, si no existe se ocupa la opcion por defecto';
                                        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'FormAling' => 2, 'FormCol' => 12, 'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'Config_Principal_FeedURL',  'Value' => $data['rowData']['Config_Principal_FeedURL'] ?? '', 'Required' => 1, 'Icon' => 'bi bi-globe']);
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
                                <div class="card">
                                    <div class="card-header">Sistema</div>
                                    <div class="card-body">
                                        <?php
                                        /********************************/
                                        $Title = 'Modal - Subtítulos';
                                        $Info  = 'Permite mostrar los subtítulos en las ventanas Modales';
                                        $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'sistemaModalSubtitle',  'Value' => $data['rowData']['sistemaModalSubtitle'] ?? '',  'Required' => 1,'Color' => 3]);
                                        /********************************/
                                        $Title = 'Modal - Botón Cerrar';
                                        $Info  = 'Permite mostrar el botón de cierre en la ventana modal';
                                        $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'sistemaModalCloseBTN',  'Value' => $data['rowData']['sistemaModalCloseBTN'] ?? '',  'Required' => 1,'Color' => 3]);
                                        /********************************/
                                        $Title = 'Uso Whatsapp';
                                        $Info  = 'Permite configurar el uso de Whatsapp en la plataforma';
                                        $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'sistemaUsoWhatsapp',  'Value' => $data['rowData']['sistemaUsoWhatsapp'] ?? '',  'Required' => 1,'Color' => 3]);
                                        /********************************/
                                        $Title = 'Configuracion Motor Email';
                                        $Info  = 'Permite configurar el tipo de motor email a utilizar';
                                        $data['Fnc_FormInputs']->formSelect([ 'FormAling' => 2,'FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'Config_motorEmail', 'Id' => 'Config_motorEmail', 'Value' => $data['rowData']['Config_motorEmail'] ?? '' ,'Required' => 2,'arrData' => $data['arrConfigEmail']]);
                                        /********************************/
                                        $Title = 'Configuracion Motor Mapas';
                                        $Info  = 'Permite configurar el tipo de motor de mapas a utilizar';
                                        $data['Fnc_FormInputs']->formSelect([ 'FormAling' => 2,'FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'Config_motorMap', 'Id' => 'Config_motorMap', 'Value' => $data['rowData']['Config_motorMap'] ?? '' ,'Required' => 2,'arrData' => $data['arrConfigMap']]);
                                        /********************************/
                                        $Title = 'Configuracion Uso IA';
                                        $Info  = 'Permite la configuracion de la IA en las transacciones que hace uso de esta';
                                        $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'Config_IA_Uso',  'Value' => $data['rowData']['Config_IA_Uso'] ?? '',  'Required' => 1,'Color' => 3]);
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <?php if(isset($data['MainViewData']['Count_GestionProyectos'])&&$data['MainViewData']['Count_GestionProyectos']!=0){ ?>
                                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
                                    <div class="card">
                                        <div class="card-header">Gestión de Proyectos</div>
                                        <div class="card-body">
                                            <?php
                                            /********************************/
                                            $Title = 'Kanban Tareas';
                                            $Info  = 'Uso listados de tareas en las Tareas Pendientes';
                                            $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'KanbanTareasUsoTareas',  'Value' => $data['rowData']['KanbanTareasUsoTareas'] ?? '',  'Required' => 1,'Color' => 3]);
                                            /********************************/
                                            $Title = 'Kanban Tareas';
                                            $Info  = 'Admin Tableros Independiente de las Tareas';
                                            $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'KanbanTareasAdminTabIndepend',  'Value' => $data['rowData']['KanbanTareasAdminTabIndepend'] ?? '',  'Required' => 1,'Color' => 3]);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if(isset($data['MainViewData']['Count_GestionEntidades'])&&$data['MainViewData']['Count_GestionEntidades']!=0){ ?>
                                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
                                    <div class="card">
                                        <div class="card-header">Gestion Entidades</div>
                                        <div class="card-body">
                                            <?php
                                            /********************************/
                                            $Title = 'Uso Cargas';
                                            $Info  = 'Permite guardar cargas en las entidades';
                                            $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'entidadesListadoVerCargas',  'Value' => $data['rowData']['entidadesListadoVerCargas'] ?? '',  'Required' => 1,'Color' => 3]);
                                            /********************************/
                                            $Title = 'Uso Contactos';
                                            $Info  = 'Permite guardar contactos en las entidades';
                                            $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'entidadesListadoVerContactos',  'Value' => $data['rowData']['entidadesListadoVerContactos'] ?? '',  'Required' => 1,'Color' => 3]);
                                            /********************************/
                                            $Title = 'Uso Documentos';
                                            $Info  = 'Permite guardar documentos en las entidades';
                                            $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'entidadesListadoVerDocumentos',  'Value' => $data['rowData']['entidadesListadoVerDocumentos'] ?? '',  'Required' => 1,'Color' => 3]);
                                            /********************************/
                                            $Title = 'Uso Password';
                                            $Info  = 'Permite la modificacion de la contraseña e el caso de ser usada';
                                            $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'entidadesListadoUsoPassword',  'Value' => $data['rowData']['entidadesListadoUsoPassword'] ?? '',  'Required' => 1,'Color' => 3]);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if(isset($data['MainViewData']['Count_Productos'])&&$data['MainViewData']['Count_Productos']!=0){ ?>
                                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
                                    <div class="card">
                                        <div class="card-header">Productos - Listado</div>
                                        <div class="card-body">
                                            <?php
                                            /********************************/
                                            $Title = 'Uso Documentos';
                                            $Info  = 'Permite guardar documentos en los productos';
                                            $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'productosListadoVerDocumentos',  'Value' => $data['rowData']['productosListadoVerDocumentos'] ?? '',  'Required' => 1,'Color' => 3]);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if(isset($data['MainViewData']['Count_Servicios'])&&$data['MainViewData']['Count_Servicios']!=0){ ?>
                                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
                                    <div class="card">
                                        <div class="card-header">Servicios - Listado</div>
                                        <div class="card-body">
                                            <?php
                                            /********************************/
                                            $Title = 'Uso Documentos';
                                            $Info  = 'Permite guardar documentos en los servicios';
                                            $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'serviciosListadoVerDocumentos',  'Value' => $data['rowData']['serviciosListadoVerDocumentos'] ?? '',  'Required' => 1,'Color' => 3]);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if(isset($data['MainViewData']['Count_DocumentosMercantiles'])&&$data['MainViewData']['Count_DocumentosMercantiles']!=0){ ?>
                                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
                                    <div class="card">
                                        <div class="card-header">Gestion Documentos</div>
                                        <div class="card-body">
                                            <?php
                                            /********************************/
                                            $Title = 'Uso Bodega';
                                            $Info  = 'Permite la interaccion con la bodega, crea documentos de movimientos';
                                            $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'gestionDocumentosUsoBodega',  'Value' => $data['rowData']['gestionDocumentosUsoBodega'] ?? '',  'Required' => 1,'Color' => 3]);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if(isset($data['MainViewData']['Count_Externalizacion'])&&$data['MainViewData']['Count_Externalizacion']!=0){ ?>
                                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
                                    <div class="card">
                                        <div class="card-header">Externalizacion Servicios</div>
                                        <div class="card-body">
                                            <?php
                                            /********************************/
                                            $Title = 'Clientes - Uso Planes';
                                            $Info  = 'Permite la asignacion de planes a los clientes';
                                            $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'entidadesListadoUsoPlanes',  'Value' => $data['rowData']['entidadesListadoUsoPlanes'] ?? '',  'Required' => 1,'Color' => 3]);
                                            /********************************/
                                            $Title = 'Clientes - Uso Usuarios';
                                            $Info  = 'Permite la creacion de usuarios para los clientes';
                                            $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'entidadesListadoUsoUsuarios',  'Value' => $data['rowData']['entidadesListadoUsoUsuarios'] ?? '',  'Required' => 1,'Color' => 3]);
                                            /********************************/
                                            $Title = 'Clientes - Uso Maquinas';
                                            $Info  = 'Permite la asignacion de maquinas a los clientes';
                                            $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'entidadesListadoUsoMaquinas',  'Value' => $data['rowData']['entidadesListadoUsoMaquinas'] ?? '',  'Required' => 1,'Color' => 3]);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if(isset($data['MainViewData']['Count_Maquinas'])&&$data['MainViewData']['Count_Maquinas']!=0){ ?>
                                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
                                    <div class="card">
                                        <div class="card-header">Maquinas - Listado</div>
                                        <div class="card-body">
                                            <?php
                                            /********************************/
                                            $Title = 'Uso Documentos';
                                            $Info  = 'Permite guardar documentos en las maquinas';
                                            $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'maquinasListadoVerDocumentos',  'Value' => $data['rowData']['maquinasListadoVerDocumentos'] ?? '',  'Required' => 1,'Color' => 3]);
                                            /********************************/
                                            $Title = 'Uso Componentes';
                                            $Info  = 'Permite la opción de uso de componentes';
                                            $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'maquinasListadoComponentes',  'Value' => $data['rowData']['maquinasListadoComponentes'] ?? '',  'Required' => 1,'Color' => 3]);
                                            /********************************/
                                            $Title = 'Uso Telemetría';
                                            $Info  = 'Permite la opción de telemetría y uso de sensores';
                                            $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'maquinasListadoTelemetria',  'Value' => $data['rowData']['maquinasListadoTelemetria'] ?? '',  'Required' => 1,'Color' => 3]);
                                            /********************************/
                                            $Title = 'Uso Backups Telemetría';
                                            $Info  = 'Permite el respaldo automático de la tabla relacionada';
                                            $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'maquinasListadoBackups',  'Value' => $data['rowData']['maquinasListadoBackups'] ?? '',  'Required' => 1,'Color' => 3]);
                                            /********************************/
                                            $Title = 'Envío de Notificaciones';
                                            $Info  = 'Permite el envío de whatsap o correos en caso de alertas';
                                            $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => $Title, 'DataInfo' => $Info, 'Name' => 'maquinasListadoNotificaciones',  'Value' => $data['rowData']['maquinasListadoNotificaciones'] ?? '',  'Required' => 1,'Color' => 3]);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                        </div>

                        <div class="justify-content-center pt-4">

                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                <?php
                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idSistema','Value' => $data['rowData']['idSistema'],'Required' => 2]);
                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <div class="tab-pane fade" id="resumen-edit_6">
                    <?php
                    if(isset($data['rowData']['Sistema_IMGLogo'])&&$data['rowData']['Sistema_IMGLogo']!=''){ ?>
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-8 col-md-6 col-lg-6 col-xl-4 col-xxl-3">
                                <div class="d-flex justify-content-center">
                                    <img src="<?php echo $data['UserData']['MainPathUrl'].$data['rowData']['Sistema_IMGLogo']; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
                                </div>
                                <div class="d-flex justify-content-center pt-2">
                                    <button  onclick="delIMG('<?php echo $data['rowData']['Sistema_IMGLogo']; ?>')" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Borrar Imagen</button>
                                </div>
                            </div>
                        </div>
                    <?php }else{ ?>
                        <div class="d-flex justify-content-center pt-3">
                            <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 col-xl-6 col-xxl-5">
                                <?php $data['Fnc_FormInputs']->formUploadIMG(['Name' => 'Sistema_IMGLogo','URL' => $BASE.'/Core/plataforma/configuracion/update','ExtraData' => '"idSistema": '.$data['rowData']['idSistema']]);?>
                            </div>
                        </div>
                    <?php } ?>
                </div>

            </div>

        </div>
    </div>
</div>

<style>
    #tableSwitch td .mb-3 {margin-bottom: 0 !important;}
</style>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    /******************************************/
    $("#FormEditData_1").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/Core/plataforma/configuracion/update'; ?>';
            let Informacion = $("#FormEditData_1").serialize();
            const Options     = {
                Destino:'<?php echo $BASE.'/Core/plataforma/configuracion/resumen'; ?>',
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /******************************************/
    $("#FormEditData_2").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/Core/plataforma/configuracion/update'; ?>';
            let Informacion = $("#FormEditData_2").serialize();
            const Options     = {
                Destino:'<?php echo $BASE.'/Core/plataforma/configuracion/resumen'; ?>',
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /******************************************/
    $("#FormEditData_3").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/Core/plataforma/configuracion/update'; ?>';
            let Informacion = $("#FormEditData_3").serialize();
            const Options     = {
                Destino:'<?php echo $BASE.'/Core/plataforma/configuracion/resumen'; ?>',
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /******************************************/
    $("#FormEditData_4").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/Core/plataforma/configuracion/update'; ?>';
            let Informacion = $("#FormEditData_4").serialize();
            const Options     = {
                Destino:'<?php echo $BASE.'/Core/plataforma/configuracion/resumen'; ?>',
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /******************************************/
    $("#FormEditData_5").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/Core/plataforma/configuracion/update'; ?>';
            let Informacion = $("#FormEditData_5").serialize();
            const Options     = {
                Destino:'<?php echo $BASE.'/Core/plataforma/configuracion/resumen'; ?>',
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /******************************************/
    $("#FormEditFile").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/Core/plataforma/configuracion/update'; ?>';
            let Informacion = appendFiles('#FormEditFile', 'File', 1);
            const Options     = {
                Destino:'<?php echo $BASE.'/Core/plataforma/configuracion/resumen'; ?>',
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataFormsFiles(Metodo, Direccion, Informacion, Options);
        }
    });
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
                let Direccion   = '<?php echo $BASE.'/Core/plataforma/configuracion/delFiles'; ?>';
                let Informacion = {
                    "idSistema": <?php echo $data['rowData']['idSistema']; ?>,
                    "Sistema_IMGLogo": File
                };
                const Options     = {
                    Destino:'<?php echo $BASE.'/Core/plataforma/configuracion/resumen'; ?>',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
    /******************************************/
    //Oculto
    document.addEventListener("DOMContentLoaded", function () {

        const checkbox   = document.getElementById("Config_Principal_Feed");
        const divFeedURL = document.getElementById("div_Config_Principal_FeedURL");

        function toggleFeedURL() {
            if (checkbox.checked) {
                divFeedURL.style.display = '';
            } else {
                divFeedURL.style.display = 'none';
            }
        }

        // Ejecutar al cargar la página
        toggleFeedURL();

        // Ejecutar al cambiar el checkbox
        checkbox.addEventListener("change", toggleFeedURL);

    });

</script>
