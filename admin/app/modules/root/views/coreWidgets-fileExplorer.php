<section class="section">
    <div class="row">

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <?php
            $Options = [
                'BASE'         => $BASE,
                'rootPaht'     => '',
                'Route'        => '',
                'ValidarTipo'  => '',
            ];
            $data['Fnc_WidgetsCommon']->widget_fileExplorer($Options);
            ?>
        </div>

    </div>
</section>
