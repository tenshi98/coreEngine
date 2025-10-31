<?php
//Solo si es un administrador
if(isset($data['UserData']['UserType'])&&$data['UserData']['UserType']==1){ ?>
    <li class="nav-item dropdown">
        <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-card-list"></i></a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">Administración</li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/permisos/categorias/listAll'; ?>">   <i class="bi bi-card-list color-blue"></i> Permisos - Categorias</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/permisos/listado/listAll'; ?>">      <i class="bi bi-card-list color-blue"></i> Permisos - Listado</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/administracion/usuarios/listAll'; ?>">           <i class="bi bi-card-list color-blue"></i> Administrar Administradores</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/administracion/bloqueo-usuarios/listAll'; ?>">   <i class="bi bi-card-list color-blue"></i> Usuarios Bloqueados</a></li>
            <<li><hr class="dropdown-divider"></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/plataforma/configuracion/resumen'; ?>">  <i class="bi bi-card-list color-blue"></i> Configuracion Sistema</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/plataforma/instalacion/resumen'; ?>">    <i class="bi bi-card-list color-blue"></i> Instalacion de Modulos</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/plataforma/rutas/listado'; ?>">          <i class="bi bi-card-list color-blue"></i> Comparación Rutas</a></li>
            <li><hr class="dropdown-divider"></li>
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-card-list"></i></a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">Pruebas</li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/testeos/controladores'; ?>">           <i class="bi bi-gear color-gray"></i> Pruebas controlador</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/testeos/funciones'; ?>">               <i class="bi bi-gear color-gray"></i> Pruebas funciones</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/testeos/inteligenciaArtificial'; ?>">  <i class="bi bi-chat-square color-gray"></i> Pruebas IA</a></li>
            <li><hr class="dropdown-divider"></li>
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-card-list"></i></a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">Pruebas Notificaciones</li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/testeos/send_SMTPMail'; ?>">       <i class="bx bx-mail-send color-green-dark"></i> Envío Correo SMTP</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/testeos/send_GMail'; ?>">          <i class="bi bi-google color-green-dark"></i> Envío Correo GMail</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/testeos/send_SendingBlue'; ?>">    <i class="ri-mail-open-line color-green-dark"></i> Envío Correo SendingBlue</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/testeos/send_Whatsapp'; ?>">       <i class="bi bi-whatsapp color-green-dark"></i> Envío Mensaje Whatsapp</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/testeos/send_mailTemplateSelect'; ?>">  <i class="bx bx-mail-send color-green-dark"></i> Prueba Plantilla Email</a></li>
            <li><hr class="dropdown-divider"></li>
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-card-list"></i></a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">Crud</li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/pruebas/crudNormal/listAll'; ?>">    <i class="bi bi-gear color-yellow-light"></i> Crud Normal</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/pruebas/crudResumen/listAll'; ?>">   <i class="bi bi-gear color-yellow-light"></i> Crud Resumen</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/pruebas/crudInforme/listAll'; ?>">   <i class="bi bi-gear color-yellow-light"></i> Informes</a></li>
            <li><hr class="dropdown-divider"></li>
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-card-list"></i></a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">Componentes</li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Componentes/acordeon'; ?>">     <i class="bi bi-puzzle color-blue"></i> Acordeon</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Componentes/alertas'; ?>">      <i class="bi bi-puzzle color-blue"></i> Alertas</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Componentes/badges'; ?>">       <i class="bi bi-puzzle color-blue"></i> Badges</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Componentes/breadcrumbs'; ?>">  <i class="bi bi-puzzle color-blue"></i> Breadcrumbs</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Componentes/buttons'; ?>">      <i class="bi bi-puzzle color-blue"></i> Botones</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Componentes/cards'; ?>">        <i class="bi bi-puzzle color-blue"></i> Cards</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Componentes/carousel'; ?>">     <i class="bi bi-puzzle color-blue"></i> Carousel</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Componentes/colors'; ?>">       <i class="bi bi-puzzle color-blue"></i> Colores</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Componentes/icons'; ?>">        <i class="bi bi-puzzle color-blue"></i> Iconos</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Componentes/listgroup'; ?>">    <i class="bi bi-puzzle color-blue"></i> Listgroup</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Componentes/modal'; ?>">        <i class="bi bi-puzzle color-blue"></i> Modal</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Componentes/pagination'; ?>">   <i class="bi bi-puzzle color-blue"></i> Paginacion</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Componentes/progress'; ?>">     <i class="bi bi-puzzle color-blue"></i> Barra Progreso</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Componentes/spinners'; ?>">     <i class="bi bi-puzzle color-blue"></i> Spinners</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Componentes/tabs'; ?>">         <i class="bi bi-puzzle color-blue"></i> Tabs</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Componentes/tooltips'; ?>">     <i class="bi bi-puzzle color-blue"></i> Tooltips</a></li>
            <li><hr class="dropdown-divider"></li>
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-card-list"></i></a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">Formularios</li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Formularios/formularios'; ?>">  <i class="bi bi-card-list color-green-dark"></i> Form Inputs</a></li>
            <li><hr class="dropdown-divider"></li>
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-card-list"></i></a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">Widgets</li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Widgets/box'; ?>">           <i class="bi bi-puzzle color-red"></i> Box</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Widgets/timeLine'; ?>">      <i class="bi bi-puzzle color-red"></i> Time Line</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Widgets/textDividers'; ?>">  <i class="bi bi-puzzle color-red"></i> Text Divider</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Widgets/components'; ?>">    <i class="bi bi-puzzle color-red"></i> Componentes Web</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Widgets/calendar'; ?>">      <i class="bi bi-puzzle color-red"></i> Calendario</a></li>
            <li><hr class="dropdown-divider"></li>
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-card-list"></i></a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">Tablas</li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Tablas/normal'; ?>">       <i class="bi bi-table color-blue-dark"></i> Normales</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Tablas/dataTables'; ?>">   <i class="bi bi-table color-blue-dark"></i> dataTables</a></li>
            <li><hr class="dropdown-divider"></li>
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-card-list"></i></a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">Gráficos</li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Graficos/apexcharts'; ?>">     <i class="bi bi-bar-chart-line color-amber-text"></i> Apexcharts</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Graficos/chartjs'; ?>">        <i class="bi bi-bar-chart-line color-amber-text"></i> Chartjs</a></li>
            <li><a class="notification-item" href="<?php echo $BASE.'/Core/Graficos/echarts'; ?>">        <i class="bi bi-bar-chart-line color-amber-text"></i> Echarts</a></li>
            <li><hr class="dropdown-divider"></li>
        </ul>
    </li>


<?php } ?>