<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<section class="section dashboard">
    <div class="row">


        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <div class="row">
                <div class="col-sm-4 col-xl-4">
                    <?php
                    $Options = [
                        'Titulo' => 'elpais.com',
                        'URL'    => 'https://feeds.elpais.com/mrss-s/pages/ep/site/elpais.com/section/chile/portada',
                        'BASE'   => $BASE,
                    ];
                    $data['Fnc_WidgetsCommon']->widget_feed($Options);
                    ?>
                </div>
                <div class="col-sm-4 col-xl-4">
                    <?php
                    $Options = [
                        'Titulo' => 'elpais.com',
                        'URL'    => 'https://feeds.elpais.com/mrss-s/pages/ep/site/elpais.com/section/chile/portada',
                        'Type'   => 2,
                        'BASE'   => $BASE,
                    ];
                    $data['Fnc_WidgetsCommon']->widget_feed($Options);
                    ?>
                </div>
                <div class="col-sm-4 col-xl-4">


                </div>
            </div>
        </div>


    </div>
</section>
