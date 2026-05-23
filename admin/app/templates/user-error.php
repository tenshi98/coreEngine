<div class="container">

    <section class="section error-404 min-vh-100 d-flex flex-column align-items-center justify-content-center">
        <h1>500</h1>
        <?php
        //En el caso de no ser superadministrador
        if ($data['UserData']['UserType'] != 1) {
            //Elimino duplicados
            $errors = array_values(
                array_unique($data['dataError']['errors'])
            );
            //Imprimo los datos
            echo '<h2>'.implode('<br>', $errors).'</h2>';
            echo '<img src="'.$BASE.'/img/not-found.svg" class="img-fluid py-5" alt="Page Not Found">';
        }else{
            //Elimino duplicados
            $errors = array_values(
                array_unique($data['dataError']['data'])
            );
            echo '
            <div class="terminal-box">
                <div class="terminal-header">
                    <div class="terminal-dot red"></div>
                    <div class="terminal-dot yellow"></div>
                    <div class="terminal-dot green"></div>
                </div>
                <div class="terminal-body">
                    <div><span class="prompt">❯</span> <span class="path">~/web</span> <span class="cmd">fetch /requested-page</span></div>
                    <div class="output">'.implode('<br><br>', $errors).'</div>
                    <div><span class="prompt">❯</span> <span class="cmd">_</span><span class="cursor-blink"></span></div>
                </div>
            </div>
            ';
        }

        ?>

    </section>

</div>

