<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<section class="section">
    <div class="row">

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <?php
            $Options = [
                'BASE'             => $BASE,
                'rootPath'         => $data['UserData']['MainPathUrl'],
                'Route'            => 'fileExplorer',
                'ValidarTipo'      => '',
                'levelPermission'  => $data['UserAccess']['LevelAccess'],
                // Ámbito de autorización: el endpoint valida la concesión que este módulo
                // registró en sesión al renderizar la vista (ver coreWidgets.php)
                'AccessScope'      => 'coreWidgets',
            ];
            $data['Fnc_WidgetsCommon']->widget_fileExplorer($Options);
            ?>
        </div>

    </div>
</section>
