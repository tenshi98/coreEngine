<div class="container">

    <section class="section error-404 min-vh-100 d-flex flex-column align-items-center justify-content-center">
        <h1>500</h1>
        <?php
        /***************************************************************/
        /* Blindaje de datos                                           */
        /*                                                             */
        /* La plantilla puede invocarse desde showError() o desde      */
        /* rutas que no envían información del error (p.ej.            */
        /* Core/Paginas/error5xx). Se normaliza el contenido para      */
        /* evitar "Undefined array key" (F3 convierte las warnings     */
        /* en respuestas 500).                                         */
        /***************************************************************/
        //Contenido del error (puede no llegar, llegar como string o como arreglo)
        $dataError = $data['dataError'] ?? [];

        //Si llega como texto simple se adapta a la estructura esperada
        if (is_string($dataError)) {
            $dataError = ['errors' => [$dataError], 'data' => [$dataError]];
        } elseif (!is_array($dataError)) {
            $dataError = [];
        }

        //Se recuperan las listas garantizando que siempre sean arreglos
        $listErrors = is_array($dataError['errors'] ?? null) ? $dataError['errors'] : [];
        $listData   = is_array($dataError['data']   ?? null) ? $dataError['data']   : [];

        //Elimino elementos que no sean texto, vacíos y duplicados
        $cleanList = function ($list) {
            $result = [];
            foreach ($list as $item) {
                if (is_scalar($item) && trim((string)$item) !== '') {
                    $result[] = (string)$item;
                }
            }
            return array_values(array_unique($result));
        };

        $errors  = $cleanList($listErrors);
        $details = $cleanList($listData);

        //Tipo de usuario actual (1 = superadministrador)
        $UserType = $data['UserData']['UserType'] ?? 0;

        //En el caso de no ser superadministrador
        if ($UserType != 1) {
            //Mensaje genérico si no hay detalle disponible
            if (empty($errors)) {
                $errors = ['Se ha producido un error al procesar la solicitud. Por favor, inténtelo de nuevo más tarde.'];
            }
            //Imprimo los datos
            echo '<h2>'.implode('<br>', $errors).'</h2>';
            echo '<img src="'.$BASE.'/img/not-found.svg" class="img-fluid py-5" alt="Page Not Found">';
        }else{
            //Se usa la traza técnica; si no existe, se recurro a los mensajes de error
            if (empty($details)) {
                $details = $errors;
            }
            //Mensaje de respaldo si no hay ningún dato disponible
            if (empty($details)) {
                $details = ['No hay información detallada del error disponible.'];
            }
            echo '
            <div class="terminal-box">
                <div class="terminal-header">
                    <div class="terminal-dot red"></div>
                    <div class="terminal-dot yellow"></div>
                    <div class="terminal-dot green"></div>
                </div>
                <div class="terminal-body">
                    <div><span class="prompt">❯</span> <span class="path">~/web</span> <span class="cmd">fetch /requested-page</span></div>
                    <div class="output">'.implode('<br><br>', $details).'</div>
                    <div><span class="prompt">❯</span> <span class="cmd">_</span><span class="cursor-blink"></span></div>
                </div>
            </div>
            ';
        }

        ?>

    </section>

</div>
