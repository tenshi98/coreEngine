<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="utf-8">
        <meta name="viewport"   content="width=device-width, initial-scale=1">

        <title><?php echo $data['PageTitle']; ?></title>
        <meta name="description" content="<?php echo $data['PageDescription']; ?>">
        <meta name="author"      content="<?php echo $data['PageAuthor']; ?>">
        <meta name="keywords"    content="<?php echo $data['PageKeywords']; ?>">
        <meta name="robots"      content="nofollow, noindex" />

        <!-- Favicons -->
        <link rel="icon"             type="image/png"                    href="<?php echo $BASE.'/img/favicon/mifavicon.png'; ?>" >
        <link rel="shortcut icon"    type="image/x-icon"                 href="<?php echo $BASE.'/img/favicon/mifavicon.png'; ?>" >
        <link rel="apple-touch-icon" type="image/x-icon"                 href="<?php echo $BASE.'/img/favicon/mifavicon-57x57.png'; ?>">
        <link rel="apple-touch-icon" type="image/x-icon" sizes="72x72"   href="<?php echo $BASE.'/img/favicon/mifavicon-72x72.png'; ?>">
        <link rel="apple-touch-icon" type="image/x-icon" sizes="114x114" href="<?php echo $BASE.'/img/favicon/mifavicon-114x114.png'; ?>">
        <link rel="apple-touch-icon" type="image/x-icon" sizes="144x144" href="<?php echo $BASE.'/img/favicon/mifavicon-144x144.png'; ?>">

        <!-- Google Fonts -->
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i">
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/icon?family=Material+Icons">

        <!-- Base -->
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/bootstrap/css/bootstrap.min.css'; ?>">

        <!-- Iconos -->
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/bootstrap-icons/bootstrap-icons.css'; ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/boxicons/css/boxicons.min.css'; ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/remixicon/remixicon.css'; ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/glyphicons/glyphicons.min.css'; ?>">

        <!-- Tablas -->
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/simple-datatables/style.css'; ?>">

        <!-- Notificaciones -->
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/sweetalert2/sweetalert2.min.css'; ?>">

        <!-- Scripts -->
        <script type="text/javascript" src="<?php echo $BASE.'/js/jquery-3.6.0.min.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo $BASE.'/js/form_functions.js?get='.time(); ?>"></script>

        <!-- Material Datetimepicker -->
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/material_datetimepicker/css/bootstrap-material-datetimepicker.min.css'; ?>">
        <script type="text/javascript" src="<?php echo $BASE.'/vendor/material_datetimepicker/js/moment-with-locales.min.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo $BASE.'/vendor/material_datetimepicker/js/bootstrap-material-datetimepicker.min.js'; ?>"></script>

        <!-- Bootstrap Colorpicker -->
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/bootstrap_colorpicker/dist/css/bootstrap-colorpicker.min.css'; ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/bootstrap_colorpicker/dist/css/bootstrap-colorpicker-plus.min.css'; ?>">

        <!-- Bootstrap Touchspin -->
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/bootstrap_touchspin/src/jquery.bootstrap-touchspin.min.css'; ?>">
        <script type="text/javascript" src="<?php echo $BASE.'/vendor/bootstrap_touchspin/src/jquery.bootstrap-touchspin.min.js'; ?>"></script>

        <!-- Clock Timepicker -->
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/clock_timepicker/dist/jquery-clockpicker.min.css'; ?>">
        <script type="text/javascript" src="<?php echo $BASE.'/vendor/clock_timepicker/dist/jquery-clockpicker.min.js'; ?>"></script>

        <!-- Select2 -->
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/select2/select2.min.css'; ?>">
        <script type="text/javascript" src="<?php echo $BASE.'/vendor/select2/select2.min.js'; ?>"></script>

        <!-- Bootstrap Fileinput -->
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/bootstrap_fileinput/css/fileinput.min.css'; ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/bootstrap_fileinput/themes/explorer/theme.min.css'; ?>">
        <script type="text/javascript" src="<?php echo $BASE.'/vendor/bootstrap_fileinput/js/plugins/sortable.min.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo $BASE.'/vendor/bootstrap_fileinput/js/fileinput.min.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo $BASE.'/vendor/bootstrap_fileinput/js/locales/es.min.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo $BASE.'/vendor/bootstrap_fileinput/themes/explorer/theme.min.js'; ?>"></script>

        <!-- Rut Validate -->
        <script type="text/javascript" src="<?php echo $BASE.'/vendor/rut_validate/jquery.rut.min.js'; ?>"></script>

        <!-- Form Validate -->
		<script type="text/javascript" src="<?php echo $BASE.'/vendor/form_validator/validator.min.js'; ?>"></script>

        <!-- Animaciones Div -->
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/aos/aos.css'; ?>">
        <script type="text/javascript" src="<?php echo $BASE.'/vendor/aos/aos.js'; ?>"></script>

        <!-- Redimensionar Cuadro texto -->
		<script type="text/javascript" src="<?php echo $BASE.'/vendor/autosize/dist/autosize.min.js'; ?>"></script>

        <!-- Full Calendar -->
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/fullcalendar/fullcalendar.min.css'; ?>">
        <script type="text/javascript" src="<?php echo $BASE.'/vendor/fullcalendar/fullcalendar.min.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo $BASE.'/vendor/fullcalendar/es.js'; ?>"></script>

        <!-- Archivos de la Plataforma -->
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/css/base_color.css?get='.time(); ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/css/style.css?get='.time(); ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/css/theme.css?get='.time(); ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/css/media.css?get='.time(); ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/css/my_colors.min.css?get='.time(); ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/css/efects.css?get='.time(); ?>">

        <script>
            /******************************************/
            //Estancia del validacion formularios
            var validator = new FormValidator();
        </script>

    </head>

    <body>

