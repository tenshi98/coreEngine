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

                    <form id="FormEditData_1" name="FormEditData_1" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //Se verifican si existen los datos
                                $x1  = $data['rowData']['Sistema_Nombre'] ?? '';
                                $x2  = $data['rowData']['Sistema_Email'] ?? '';
                                $x3  = $data['rowData']['Sistema_Rut'] ?? '';
                                $x4  = $data['rowData']['Sistema_idCiudad'] ?? '';
                                $x5  = $data['rowData']['Sistema_idComuna'] ?? '';
                                $x6  = $data['rowData']['Sistema_Direccion'] ?? '';
                                $x7  = $data['rowData']['Sistema_idTema'] ?? '';
                                $x8  = $data['rowData']['Sistema_NotiWhatsapp'] ?? '';
                                $x9  = $data['rowData']['Social_X'] ?? '';
                                $x10 = $data['rowData']['Social_Facebook'] ?? '';
                                $x11 = $data['rowData']['Social_Instagram'] ?? '';
                                $x12 = $data['rowData']['Social_Linkedin'] ?? '';

                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Nombre', 'Name'  => 'Sistema_Nombre',   'Value'  => $x1,'Required'  => 2]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder'  => 'Email',  'Name'  => 'Sistema_Email',    'Value'  => $x2,'Required'  => 1,'Icon' => 'bx bx-mail-send']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder'  => 'Rut',    'Name'  => 'Sistema_Rut',      'Value'  => $x3,'Required'  => 1,'Icon' => 'bi bi-person-circle']);
                                $data['Fnc_FormInputs']->formSelectDepend([          'Placeholder1' => 'Ciudad', 'Name1' => 'Sistema_idCiudad', 'Value1' => $x4,'Required1' => 1,'arrData1' => $data['arrCiudad'],
                                                                                       'Placeholder2' => 'Comuna', 'Name2' => 'Sistema_idComuna', 'Value2' => $x5,'Required2' => 1,'arrData2' => $data['arrComuna'],
                                                                                       'FormName' => 'FormEditData_1']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Dirección',           'Name' => 'Sistema_Direccion',     'Value' => $x6, 'Required' => 1, 'Icon' => 'bi bi-geo-alt-fill']);
                                $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder'  => 'Tema',                'Name' => 'Sistema_idTema',        'Value' => $x7, 'Required' => 2, 'arrData' => $data['arrTemas'], 'BASE' => $BASE]);

                                $data['Fnc_FormInputs']->formPostData(4, 4, 'exclamation-circle', 0, 'El numero debe ingresarse iniciando con 56, sin el simbolo + y sin espacios ni separaciones');
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder'  => 'Fono Noti Whatsapp',  'Name' => 'Sistema_NotiWhatsapp',  'Value' => $x8, 'Required' => 1, 'Icon' => 'bi bi-telephone-fill']);

                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Social']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'X (Twitter)', 'Name' => 'Social_X',         'Value' => $x9,  'Required' => 1, 'Icon' => 'bi bi-x']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Facebook',    'Name' => 'Social_Facebook',  'Value' => $x10, 'Required' => 1, 'Icon' => 'bi bi-facebook']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Instagram',   'Name' => 'Social_Instagram', 'Value' => $x11, 'Required' => 1, 'Icon' => 'bi bi-instagram']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Linkedin',    'Name' => 'Social_Linkedin',  'Value' => $x12, 'Required' => 1, 'Icon' => 'bi bi-linkedin']);


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

                    <form id="FormEditData_2" name="FormEditData_2" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //Se verifican si existen los datos
                                $x1  = $data['rowData']['Contacto_Nombre'] ?? '';
                                $x2  = $data['rowData']['Contacto_Fono1'] ?? '';
                                $x3  = $data['rowData']['Contacto_Fono2'] ?? '';
                                $x4  = $data['rowData']['Contacto_Fax'] ?? '';
                                $x5  = $data['rowData']['Contacto_Email'] ?? '';
                                $x6  = $data['rowData']['Contacto_Web'] ?? '';

                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre', 'Name' => 'Contacto_Nombre',   'Value' => $x1, 'Required' => 1]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Fono 1', 'Name' => 'Contacto_Fono1',    'Value' => $x2, 'Required' => 1, 'Icon' => 'bi bi-telephone-fill']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Fono 2', 'Name' => 'Contacto_Fono2',    'Value' => $x3, 'Required' => 1, 'Icon' => 'bi bi-telephone-fill']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Fax',    'Name' => 'Contacto_Fax',      'Value' => $x4, 'Required' => 1, 'Icon' => 'bi bi-telephone-fill']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',  'Name' => 'Contacto_Email',    'Value' => $x5, 'Required' => 1, 'Icon' => 'bx bx-mail-send']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Web',    'Name' => 'Contacto_Web',      'Value' => $x6, 'Required' => 1, 'Icon' => 'ri-edge-fill']);

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

                    <form id="FormEditData_3" name="FormEditData_3" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //Se verifican si existen los datos
                                $x1  = $data['rowData']['RepresentanteNombre'] ?? '';
                                $x2  = $data['rowData']['RepresentanteRut'] ?? '';
                                $x3  = $data['rowData']['RepresentanteFono'] ?? '';
                                $x4  = $data['rowData']['RepresentanteEmail'] ?? '';

                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre', 'Name' => 'RepresentanteNombre',  'Value' => $x1, 'Required' => 1]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder' => 'Rut',    'Name' => 'RepresentanteRut',     'Value' => $x2, 'Required' => 1, 'Icon' => 'bi bi-person-circle']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Fono 1', 'Name' => 'RepresentanteFono',    'Value' => $x3, 'Required' => 1, 'Icon' => 'bi bi-telephone-fill']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',  'Name' => 'RepresentanteEmail',   'Value' => $x4, 'Required' => 1, 'Icon' => 'bx bx-mail-send']);

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

                    <form id="FormEditData_4" name="FormEditData_4" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //Se verifican si existen los datos
                                $x1  = $data['rowData']['Config_API_GoogleMaps'] ?? '';
                                $x2  = $data['rowData']['Config_WhatsappToken'] ?? '';
                                $x3  = $data['rowData']['Config_WhatsappInstanceId'] ?? '';

                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'API GoogleMaps',           'Name' => 'Config_API_GoogleMaps',      'Value' => $x1, 'Required' => 1, 'Icon' => 'bi bi-puzzle']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'API Whatsapp Token',       'Name' => 'Config_WhatsappToken',       'Value' => $x2, 'Required' => 1, 'Icon' => 'bi bi-puzzle']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'API Whatsapp Instance Id', 'Name' => 'Config_WhatsappInstanceId',  'Value' => $x3, 'Required' => 1, 'Icon' => 'bi bi-puzzle']);

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

                    <form id="FormEditData_5" name="FormEditData_5" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">

                        <table class="table table-sm table-hover" id="tableSwitch">
                            <tbody>
                                <tr class="table-secondary"><td colspan="2"><strong>Sistema</strong></td></tr>
                                <tr>
                                    <td><strong>Modal - Subtítulos:</strong> Permite mostrar los subtítulos en las ventanas Modales</td>
                                    <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '', 'Name' => 'sistemaModalSubtitle',  'Value' => ($data['rowData']['sistemaModalSubtitle'] ?? ''),  'Required' => 1,'Color' => 3]);?></td>
                                </tr>
                                <tr>
                                    <td><strong>Modal - Botón Cerrar:</strong> Permite mostrar el botón de cierre en la ventana modal</td>
                                    <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '', 'Name' => 'sistemaModalCloseBTN',  'Value' => ($data['rowData']['sistemaModalCloseBTN'] ?? ''),  'Required' => 1,'Color' => 3]);?></td>
                                </tr>

                                <?php  if(isset($data['MainViewData']['Count_GestionProyectos'])&&$data['MainViewData']['Count_GestionProyectos']!=0){ ?>
                                    <tr class="table-secondary"><td colspan="2"><strong>Gestion Proyectos</strong></td></tr>
                                    <tr>
                                        <td><strong>Kanban Tareas:</strong> Uso listados de tareas en las Tareas Pendientes</td>
                                        <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '', 'Name' => 'KanbanTareasUsoTareas',         'Value' => ($data['rowData']['KanbanTareasUsoTareas'] ?? ''),         'Required' => 1,'Color' => 3]);?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Kanban Tareas:</strong> Admin Tableros Independiente de las Tareas</td>
                                        <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '', 'Name' => 'KanbanTareasAdminTabIndepend',  'Value' => ($data['rowData']['KanbanTareasAdminTabIndepend'] ?? ''),  'Required' => 1,'Color' => 3]);?></td>
                                    </tr>
                                <?php } ?>

                                <?php  if(isset($data['MainViewData']['Count_GestionEntidades'])&&$data['MainViewData']['Count_GestionEntidades']!=0){ ?>
                                    <tr class="table-secondary"><td colspan="2"><strong>Gestion Entidades</strong></td></tr>
                                    <tr>
                                        <td><strong>Uso Cargas:</strong> Permite guardar cargas en las entidades</td>
                                        <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '', 'Name' => 'entidadesListadoVerCargas',      'Value' => ($data['rowData']['entidadesListadoVerCargas'] ?? ''),      'Required' => 1,'Color' => 3]);?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Uso Contactos:</strong> Permite guardar contactos en las entidades</td>
                                        <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '', 'Name' => 'entidadesListadoVerContactos',   'Value' => ($data['rowData']['entidadesListadoVerContactos'] ?? ''),   'Required' => 1,'Color' => 3]);?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Uso Documentos:</strong> Permite guardar documentos en las entidades</td>
                                        <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '', 'Name' => 'entidadesListadoVerDocumentos',  'Value' => ($data['rowData']['entidadesListadoVerDocumentos'] ?? ''),  'Required' => 1,'Color' => 3]);?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Uso Password:</strong> Permite la modificacion de la contraseña e el caso de ser usada</td>
                                        <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '', 'Name' => 'entidadesListadoUsoPassword',    'Value' => ($data['rowData']['entidadesListadoUsoPassword'] ?? ''),    'Required' => 1,'Color' => 3]);?></td>
                                    </tr>
                                <?php } ?>

                                <?php  if(isset($data['MainViewData']['Count_Productos'])&&$data['MainViewData']['Count_Productos']!=0){ ?>
                                    <tr class="table-secondary"><td colspan="2"><strong>Productos - Listado</strong></td></tr>
                                    <tr>
                                        <td><strong>Uso Documentos:</strong> Permite guardar documentos en los productos</td>
                                        <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '',  'Name' => 'productosListadoVerDocumentos',      'Value' => ($data['rowData']['productosListadoVerDocumentos'] ?? ''),      'Required' => 1,'Color' => 3]);?></td>
                                    </tr>
                                <?php } ?>

                                <?php  if(isset($data['MainViewData']['Count_Servicios'])&&$data['MainViewData']['Count_Servicios']!=0){ ?>
                                    <tr class="table-secondary"><td colspan="2"><strong>Servicios - Listado</strong></td></tr>
                                    <tr>
                                        <td><strong>Uso Documentos:</strong> Permite guardar documentos en los servicios</td>
                                        <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '',  'Name' => 'serviciosListadoVerDocumentos',      'Value' => ($data['rowData']['serviciosListadoVerDocumentos'] ?? ''),      'Required' => 1,'Color' => 3]);?></td>
                                    </tr>
                                <?php } ?>

                                <?php  if(isset($data['MainViewData']['Count_DocumentosMercantiles'])&&$data['MainViewData']['Count_DocumentosMercantiles']!=0){ ?>
                                    <tr class="table-secondary"><td colspan="2"><strong>Gestion Documentos</strong></td></tr>
                                    <tr>
                                        <td><strong>Uso Bodega:</strong> Permite la interaccion con la bodega, crea documentos de movimientos</td>
                                        <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '',   'Name' => 'gestionDocumentosUsoBodega',      'Value' => ($data['rowData']['gestionDocumentosUsoBodega'] ?? ''),      'Required' => 1,'Color' => 3]);?></td>
                                    </tr>
                                <?php } ?>

                                <?php  if(isset($data['MainViewData']['Count_Externalizacion'])&&$data['MainViewData']['Count_Externalizacion']!=0){ ?>
                                    <tr class="table-secondary"><td colspan="2"><strong>Externalizacion Servicios</strong></td></tr>
                                    <tr>
                                        <td><strong>Clientes - Uso Planes:</strong> Permite la asignacion de planes a los clientes</td>
                                        <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '', 'Name' => 'entidadesListadoUsoPlanes',     'Value' => ($data['rowData']['entidadesListadoUsoPlanes'] ?? ''),      'Required' => 1,'Color' => 3]);?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Clientes - Uso Usuarios:</strong> Permite la creacion de usuarios para los clientes</td>
                                        <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '', 'Name' => 'entidadesListadoUsoUsuarios',   'Value' => ($data['rowData']['entidadesListadoUsoUsuarios'] ?? ''),    'Required' => 1,'Color' => 3]);?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Clientes - Uso Maquinas:</strong> Permite la asignacion de maquinas a los clientes</td>
                                        <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '', 'Name' => 'entidadesListadoUsoMaquinas',   'Value' => ($data['rowData']['entidadesListadoUsoMaquinas'] ?? ''),    'Required' => 1,'Color' => 3]);?></td>
                                    </tr>
                                <?php } ?>

                                <?php  if(isset($data['MainViewData']['Count_Maquinas'])&&$data['MainViewData']['Count_Maquinas']!=0){ ?>
                                    <tr class="table-secondary"><td colspan="2"><strong>Maquinas - Listado</strong></td></tr>
                                    <tr>
                                        <td><strong>Uso Documentos:</strong> Permite guardar documentos en las maquinas</td>
                                        <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '', 'Name' => 'maquinasListadoVerDocumentos',     'Value' => ($data['rowData']['maquinasListadoVerDocumentos'] ?? ''),      'Required' => 1,'Color' => 3]);?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Uso Componentes:</strong> Permite la opción de uso de componentes</td>
                                        <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '', 'Name' => 'maquinasListadoComponentes',       'Value' => ($data['rowData']['maquinasListadoComponentes'] ?? ''),        'Required' => 1,'Color' => 3]);?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Uso Telemetría:</strong> Permite la opción de telemetría y uso de sensores</td>
                                        <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '', 'Name' => 'maquinasListadoTelemetria',        'Value' => ($data['rowData']['maquinasListadoTelemetria'] ?? ''),         'Required' => 1,'Color' => 3]);?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Uso Backups Telemetría:</strong> Permite el respaldo automático de la tabla relacionada</td>
                                        <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '', 'Name' => 'maquinasListadoBackups',           'Value' => ($data['rowData']['maquinasListadoBackups'] ?? ''),            'Required' => 1,'Color' => 3]);?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Envío de Notificaciones:</strong> Permite el envío de whatsap o correos en caso de alertas</td>
                                        <td><?php $data['Fnc_FormInputs']->formSwitch(['FormCol' => 12,'Placeholder' => '', 'Name' => 'maquinasListadoNotificaciones',    'Value' => ($data['rowData']['maquinasListadoNotificaciones'] ?? ''),     'Required' => 1,'Color' => 3]);?></td>
                                    </tr>
                                <?php } ?>

                            </tbody>
                        </table>

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
                                    <img src="<?php echo $BASE.'/upload/'.$data['rowData']['Sistema_IMGLogo']; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
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
    $("#FormEditData_1").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/Core/plataforma/configuracion/update'; ?>';
            let Informacion = $("#FormEditData_1").serialize();
            const Options     = {
                Destino:'<?php echo $BASE.'/Core/plataforma/configuracion/resumen'; ?>',
                closeObject:'#PDloader',
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
</script>