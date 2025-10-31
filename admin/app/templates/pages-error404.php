<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport"   content="width=device-width, initial-scale=1">

        <title><?php echo $data['PageTitle']; ?></title>
        <meta name="description" content="<?php echo $data['PageDescription']; ?>">
        <meta name="author"      content="<?php echo $data['PageAuthor']; ?>">
        <meta name="keywords"    content="<?php echo $data['PageKeywords']; ?>">

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

        <!-- Scripts -->
        <script type="text/javascript" src="<?php echo $BASE.'/js/jquery-3.6.0.min.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo $BASE.'/js/form_functions.js'; ?>"></script>

        <!-- Animaciones Div -->
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/aos/aos.css'; ?>">
        <script type="text/javascript" src="<?php echo $BASE.'/vendor/aos/aos.js'; ?>"></script>

        <!-- Archivos de la Plataforma -->
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/css/base_color.css'; ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/css/style.css'; ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/css/theme.css'; ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/css/media.css'; ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/css/my_colors.min.css'; ?>">

    </head>

    <body>

        <main>
            <div class="container">

                <section class="section error-404 min-vh-100 d-flex flex-column align-items-center justify-content-center">
                    <h1>404</h1>
                    <h2>La pagina que intentas ver no existe.</h2>
                    <a class="btn" href="<?php echo $BASE.'/login'; ?>">Volver al inicio</a>
                    <img src="<?php echo $BASE.'/img/not-found.svg'; ?>" class="img-fluid py-5" alt="Page Not Found">
                </section>

            </div>
        </main>

        <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

        <!-- Vendor JS Files -->
        <script type="text/javascript" src="<?php echo $BASE.'/vendor/bootstrap/js/bootstrap.bundle.min.js'; ?>"></script>

        <!-- Archivos de la Plataforma -->
        <script type="text/javascript" src="<?php echo $BASE.'/js/main.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo $BASE.'/js/functions.js'; ?>"></script>

    </body>

</html>