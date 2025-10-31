<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="utf-8">
        <meta name="viewport"   content="width=device-width, initial-scale=1">

        <title>Maqueta</title>
        <meta name="description" content="">
        <meta name="author"      content="">
        <meta name="keywords"    content="">

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

        <!-- Archivos de la Plataforma -->
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/css/base_color.css'; ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/css/style.css'; ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/css/theme.css'; ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/css/media.css'; ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/css/my_colors.min.css'; ?>">

        <style>
            #main, #footer {margin-left: 0px;}
            body {background-color: #ffffff;}
        </style>

    </head>

    <?php
    if(isset($data['Imprimir'])&&$data['Imprimir']==1){
        $p_printer = 'onload="window.print();"';
    }else{
        $p_printer = '';
    }
    ?>
    <body <?php echo $p_printer; ?>>

        <main id="main" class="main">


