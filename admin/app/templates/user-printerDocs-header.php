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

        <!-- Base -->
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/printerDocs/style.css'; ?>">


    </head>

    <?php
    if(isset($data['Imprimir'])&&$data['Imprimir']==1){
        $p_printer = 'onload="window.print();"';
    }else{
        $p_printer = '';
    }
    ?>
    <body <?php echo $p_printer; ?>>
        <div class="cs-container">
            <div class="cs-invoice cs-style1">
                <div class="cs-invoice_in" id="download_section">

