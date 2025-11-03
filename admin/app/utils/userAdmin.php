<?php
/*******************************************************************************************************************/
/*                    superadministradores                   */
/*******************************************************************************************************************/
if($f3->get('SESSION.DataInfo.UserType')==1){
    /*************************************************************/
    /*                       Componentes                         */
    /*************************************************************/
    /*******************************/
    //Componentes
    $f3->route('GET /Core/Componentes/acordeon', 'coreComponentes->acordeon');        //Acordeon
    $f3->route('GET /Core/Componentes/alertas', 'coreComponentes->alertas');          //Alertas
    $f3->route('GET /Core/Componentes/badges', 'coreComponentes->badges');            //Badges
    $f3->route('GET /Core/Componentes/breadcrumbs', 'coreComponentes->breadcrumbs');  //Breadcrumbs
    $f3->route('GET /Core/Componentes/buttons', 'coreComponentes->buttons');          //Buttons
    $f3->route('GET /Core/Componentes/cards', 'coreComponentes->cards');              //Cards
    $f3->route('GET /Core/Componentes/carousel', 'coreComponentes->carousel');        //Carousel
    $f3->route('GET /Core/Componentes/colors', 'coreComponentes->colors');            //Colors
    $f3->route('GET /Core/Componentes/icons', 'coreComponentes->icons');              //Icons
    $f3->route('GET /Core/Componentes/listgroup', 'coreComponentes->listgroup');      //Listgroup
    $f3->route('GET /Core/Componentes/modal', 'coreComponentes->modal');              //Modal
    $f3->route('GET /Core/Componentes/pagination', 'coreComponentes->pagination');    //Pagination
    $f3->route('GET /Core/Componentes/progress', 'coreComponentes->progress');        //Progress
    $f3->route('GET /Core/Componentes/spinners', 'coreComponentes->spinners');        //Spinners
    $f3->route('GET /Core/Componentes/tabs', 'coreComponentes->tabs');                //Tabs
    $f3->route('GET /Core/Componentes/tooltips', 'coreComponentes->tooltips');        //Tooltips
    //Formularios
    $f3->route('GET /Core/Formularios/formularios', 'coreFormularios->Formularios');  //Formularios
    //Widgets
    $f3->route('GET /Core/Widgets/box', 'coreWidgets->box');                     //box
    $f3->route('GET /Core/Widgets/timeLine', 'coreWidgets->timeLine');           //timeLine
    $f3->route('GET /Core/Widgets/textDividers', 'coreWidgets->textDividers');   //timeLine
    $f3->route('GET /Core/Widgets/components', 'coreWidgets->components');       //Componentes
    $f3->route('GET /Core/Widgets/calendar', 'coreWidgets->calendar');           //Calendario
    //Tablas
    $f3->route('GET /Core/Tablas/normal', 'coreTablas->normal');            //Tablas Normales
    $f3->route('GET /Core/Tablas/dataTables', 'coreTablas->dataTables');    //Tablas dataTables
    //Gráficos
    $f3->route('GET /Core/Graficos/apexcharts', 'coreGraficos->apexcharts');    //Apexcharts
    $f3->route('GET /Core/Graficos/chartjs', 'coreGraficos->chartjs');          //Chartjs
    $f3->route('GET /Core/Graficos/echarts', 'coreGraficos->echarts');          //Echarts
    //Páginas


    /*******************************/
    //Pruebas
    $f3->route('GET /Core/testeos/controladores', 'testeos->controladores');          //Pruebas al controlador base
    $f3->route('GET /Core/testeos/funciones', 'testeos->funciones');                  //Pruebas a las funciones
    $f3->route('GET /Core/testeos/inteligenciaArtificial', 'testeos->IA_View');       //Prueba del chat GPT
    $f3->route('POST /Core/testeos/inteligenciaArtificial', 'testeos->IA_Response');  //Respuesta del chat GPT
    //Envios de datos
    $f3->route('GET /Core/testeos/send_SMTPMail', 'testeos->SMTPMail');                           //Envio de correo por SMTP (solo un correo, con uno o varios receptores)
    $f3->route('GET /Core/testeos/send_GMail', 'testeos->GMail');                                 //Envio de correo por Gmail (solo un correo, con uno o varios receptores)
    $f3->route('GET /Core/testeos/send_SendingBlue', 'testeos->SendingBlue');                     //Envio de correo por Sending Blue
    $f3->route('GET /Core/testeos/send_Whatsapp', 'testeos->Whatsapp');                           //Envio de mensaje por Whatsapp
    $f3->route('GET /Core/testeos/send_mailTemplateSelect', 'testeos->testMailTemplateSelect');   //Prueba Plantilla Email
    $f3->route('GET /Core/testeos/send_mailTemplate/@id', 'testeos->testMailTemplate');           //Prueba Plantilla Email

    $f3->route('POST /Core/testeos/send_SMTPMail', 'testeos->send_SMTPMail');              //Envio de correo por SMTP (solo un correo, con uno o varios receptores)
    $f3->route('POST /Core/testeos/send_GMail', 'testeos->send_GMail');                    //Envio de correo por Gmail (solo un correo, con uno o varios receptores)
    $f3->route('POST /Core/testeos/send_SendingBlue', 'testeos->send_SendingBlue');        //Envio de correo por Sending Blue
    $f3->route('POST /Core/testeos/send_Whatsapp', 'testeos->send_Whatsapp');              //Envio de mensaje por Whatsapp

    /*************************************************************/
    /*                       Crud Normal                         */
    /*************************************************************/
    //Vistas
    $f3->route('GET /Core/pruebas/crudNormal/listAll', 'crudNormal->listAll');        //Listar Toda la Información
    //Fragments
    $f3->route('POST /Core/pruebas/crudNormal/search', 'crudNormal->UpdateList');     //Filtrar datos
    $f3->route('GET /Core/pruebas/crudNormal/updateList', 'crudNormal->UpdateList');  //Actualizar Lista
    $f3->route('GET /Core/pruebas/crudNormal/view/@id', 'crudNormal->View');          //Mostrar Detallado
    $f3->route('GET /Core/pruebas/crudNormal/getID/@id', 'crudNormal->GetID');        //Mostrar información
    //Acciones
    $f3->route('POST /Core/pruebas/crudNormal', 'crudNormal->Insert');                //Crear
    $f3->route('POST /Core/pruebas/crudNormal/update', 'crudNormal->Update');         //Editar por post (modificar y subir archivos)
    $f3->route('DELETE /Core/pruebas/crudNormal', 'crudNormal->Delete');              //Borrar dato y archivos
    /*************************************************************/
    /*                       Crud Resumen                         */
    /*************************************************************/
    //Vistas
    $f3->route('GET /Core/pruebas/crudResumen/listAll', 'crudResumen->listAll');        //Listar Toda la Información
    //Fragments
    $f3->route('POST /Core/pruebas/crudResumen/search', 'crudResumen->UpdateList');               //Filtrar datos
    $f3->route('GET /Core/pruebas/crudResumen/updateList', 'crudResumen->UpdateList');            //Actualizar Lista
    $f3->route('GET /Core/pruebas/crudResumen/view/@id', 'crudResumen->View');                    //Mostrar Detallado
    $f3->route('GET /Core/pruebas/crudResumen/resumen/@id', 'crudResumen->Resumen');              //Mostrar información
    $f3->route('GET /Core/pruebas/crudResumen/resumenUpdate/@id', 'crudResumen->ResumenUpdate');  //Mostrar Detallado
    //Acciones
    $f3->route('POST /Core/pruebas/crudResumen', 'crudResumen->Insert');                //Crear
    $f3->route('POST /Core/pruebas/crudResumen/update', 'crudResumen->Update');         //Editar por post (modificar y subir archivos)
    $f3->route('PUT /Core/pruebas/crudResumen/delFiles', 'crudResumen->DelFiles');      //Permite eliminar archivos
    $f3->route('DELETE /Core/pruebas/crudResumen', 'crudResumen->Delete');              //Borrar dato y archivos
    //Observaciones - Fragments
    $f3->route('GET /Core/pruebas/crudResumen/observaciones/new/@id', 'crudResumenObservaciones->New');                //Mostrar modal nuevo
    $f3->route('GET /Core/pruebas/crudResumen/observaciones/updateList/@id', 'crudResumenObservaciones->UpdateList');  //Actualizar Lista
    $f3->route('GET /Core/pruebas/crudResumen/observaciones/view/@id', 'crudResumenObservaciones->View');              //Mostrar Detallado
    $f3->route('GET /Core/pruebas/crudResumen/observaciones/getID/@id', 'crudResumenObservaciones->GetID');            //Mostrar información
    //Observaciones - Acciones
    $f3->route('POST /Core/pruebas/crudResumen/observaciones', 'crudResumenObservaciones->Insert');                //Crear
    $f3->route('POST /Core/pruebas/crudResumen/observaciones/update', 'crudResumenObservaciones->Update');         //Editar por post (modificar y subir archivos)
    $f3->route('DELETE /Core/pruebas/crudResumen/observaciones', 'crudResumenObservaciones->Delete');              //Borrar dato y archivos
    /*************************************************************/
    /*                       INFORMES                         */
    /*************************************************************/
    //Vistas
    $f3->route('GET /Core/pruebas/crudInforme/listAll', 'crudInforme->listAll');        //Listar Toda la Información
    //Fragments
    $f3->route('POST /Core/pruebas/crudInforme/search', 'crudInforme->UpdateList');     //Filtrar datos
    $f3->route('GET /Core/pruebas/crudInforme/view/@id', 'crudInforme->View');          //Mostrar Detallado

    /*************************************************************/
    /*               Categoria de los Permisos                   */
    /*************************************************************/
    //Vistas
    $f3->route('GET /Core/permisos/categorias/listAll', 'permisosCategorias->listAll');        //Listar Toda la Información
    //Fragments
    $f3->route('POST /Core/permisos/categorias/search', 'permisosCategorias->UpdateList');     //Filtrar datos
    $f3->route('GET /Core/permisos/categorias/updateList', 'permisosCategorias->UpdateList');  //Actualizar Lista
    $f3->route('GET /Core/permisos/categorias/view/@id', 'permisosCategorias->View');          //Mostrar Detallado
    $f3->route('GET /Core/permisos/categorias/getID/@id', 'permisosCategorias->GetID');        //Mostrar información
    //Acciones
    $f3->route('POST /Core/permisos/categorias', 'permisosCategorias->Insert');                //Crear
    $f3->route('POST /Core/permisos/categorias/update', 'permisosCategorias->Update');         //Editar por post (modificar y subir archivos)
    $f3->route('DELETE /Core/permisos/categorias', 'permisosCategorias->Delete');              //Borrar dato y archivos
    /*************************************************************/
    /*                         Permisos                          */
    /*************************************************************/
    //Vistas
    $f3->route('GET /Core/permisos/listado/listAll', 'permisosListado->listAll');        //Listar Toda la Información
    //Fragments
    $f3->route('POST /Core/permisos/listado/search', 'permisosListado->UpdateList');               //Filtrar datos
    $f3->route('GET /Core/permisos/listado/updateList', 'permisosListado->UpdateList');            //Actualizar Lista
    $f3->route('GET /Core/permisos/listado/viewAll', 'permisosListado->ViewAll');                  //Mostrar todas las rutas
    $f3->route('GET /Core/permisos/listado/view/@id', 'permisosListado->View');                    //Mostrar Detallado
    $f3->route('GET /Core/permisos/listado/resumen/@id', 'permisosListado->Resumen');              //Mostrar información
    $f3->route('GET /Core/permisos/listado/resumenUpdate/@id', 'permisosListado->ResumenUpdate');  //Mostrar Detallado
    //Acciones
    $f3->route('POST /Core/permisos/listado', 'permisosListado->Insert');                //Crear
    $f3->route('POST /Core/permisos/listado/update', 'permisosListado->Update');         //Editar por post (modificar y subir archivos)
    $f3->route('DELETE /Core/permisos/listado', 'permisosListado->Delete');              //Borrar dato y archivos
    //Rutas - Fragments
    $f3->route('GET /Core/permisos/listado/rutas/updateList/@id', 'permisosListadoRutas->UpdateList');  //Actualizar Lista
    $f3->route('GET /Core/permisos/listado/rutas/view/@id', 'permisosListadoRutas->View');              //Mostrar Detallado
    $f3->route('GET /Core/permisos/listado/rutas/getID/@id', 'permisosListadoRutas->GetID');            //Mostrar información
    //Rutas - Acciones
    $f3->route('POST /Core/permisos/listado/rutas', 'permisosListadoRutas->Insert');                //Crear
    $f3->route('POST /Core/permisos/listado/rutas/update', 'permisosListadoRutas->Update');         //Editar por post (modificar y subir archivos)
    $f3->route('DELETE /Core/permisos/listado/rutas', 'permisosListadoRutas->Delete');              //Borrar dato y archivos
    /*************************************************************/
    /*                   Administracion de usuarios              */
    /*************************************************************/
    //Vistas
    $f3->route('GET /Core/administracion/usuarios/listAll', 'usuarios->listAll');        //Listar Toda la Información
    //Fragments
    $f3->route('POST /Core/administracion/usuarios/search', 'usuarios->UpdateList');               //Filtrar datos
    $f3->route('GET /Core/administracion/usuarios/updateList', 'usuarios->UpdateList');            //Actualizar Lista
    $f3->route('GET /Core/administracion/usuarios/view/@id', 'usuarios->View');                    //Mostrar Detallado
    $f3->route('GET /Core/administracion/usuarios/resumen/@id', 'usuarios->Resumen');              //Mostrar información
    $f3->route('GET /Core/administracion/usuarios/resumenUpdate/@id', 'usuarios->ResumenUpdate');  //Mostrar Detallado
    //Acciones
    $f3->route('POST /Core/administracion/usuarios', 'usuarios->Insert');                //Crear
    $f3->route('POST /Core/administracion/usuarios/update', 'usuarios->Update');         //Editar por post (modificar y subir archivos)
    $f3->route('PUT /Core/administracion/usuarios/delFiles', 'usuarios->DelFiles');      //Permite eliminar archivos
    $f3->route('DELETE /Core/administracion/usuarios', 'usuarios->Delete');              //Borrar dato y archivos
    //Observaciones - Fragments
    $f3->route('GET /Core/administracion/usuarios/observaciones/updateList/@id', 'usuariosObservaciones->UpdateList');  //Actualizar Lista
    $f3->route('GET /Core/administracion/usuarios/observaciones/view/@id', 'usuariosObservaciones->View');              //Mostrar Detallado
    $f3->route('GET /Core/administracion/usuarios/observaciones/getID/@id', 'usuariosObservaciones->GetID');            //Mostrar información
    //Observaciones - Acciones
    $f3->route('POST /Core/administracion/usuarios/observaciones', 'usuariosObservaciones->Insert');                //Crear
    $f3->route('POST /Core/administracion/usuarios/observaciones/update', 'usuariosObservaciones->Update');         //Editar por post (modificar y subir archivos)
    $f3->route('DELETE /Core/administracion/usuarios/observaciones', 'usuariosObservaciones->Delete');              //Borrar dato y archivos
    /*************************************************************/
    /*                      Bloqueos de usuarios                 */
    /*************************************************************/
    //Vistas
    $f3->route('GET /Core/administracion/bloqueo-usuarios/listAll', 'usuariosBloqueados->listAll');        //Listar Toda la Información
    //Fragments
    $f3->route('GET /Core/administracion/bloqueo-usuarios/updateList', 'usuariosBloqueados->UpdateList');  //Actualizar Lista
    //Acciones
    $f3->route('DELETE /Core/administracion/bloqueo-usuarios', 'usuariosBloqueados->Delete');              //Borrar dato y archivos
    /*************************************************************/
    /*                    Opciones del Sistema                   */
    /*************************************************************/
    //Vistas
    $f3->route('GET /Core/plataforma/configuracion/resumen', 'sistemaOpciones->Resumen');              //Mostrar información
    //Fragments
    $f3->route('GET /Core/plataforma/configuracion/resumenUpdate', 'sistemaOpciones->ResumenUpdate');  //Mostrar Detallado
    //Acciones
    $f3->route('POST /Core/plataforma/configuracion/update', 'sistemaOpciones->Update');         //Editar por post (modificar y subir archivos)
    $f3->route('PUT /Core/plataforma/configuracion/delFiles', 'sistemaOpciones->DelFiles');      //Permite eliminar archivos
    /*************************************************************/
    /*                  Instalacion de Modulos                   */
    /*************************************************************/
    //Vistas
    $f3->route('GET /Core/plataforma/instalacion/resumen', 'sistemaInstalacion->Resumen');                 //Listar modulos disponibles
    $f3->route('GET /Core/plataforma/instalacion/resumenUpdate', 'sistemaInstalacion->resumenUpdate');     //Actualizar vista
    $f3->route('PUT /Core/plataforma/instalacion/installModule', 'sistemaInstalacion->installModule');     //Instalacion de modulos
    $f3->route('PUT /Core/plataforma/instalacion/uninstallModule', 'sistemaInstalacion->uninstallModule'); //Desinstalacion de modulos
    $f3->route('GET /Core/plataforma/rutas/listado', 'sistemaRutas->Resumen');                             //Listar las rutas disponibles


}
