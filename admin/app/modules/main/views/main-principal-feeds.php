<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
    <?php
    //URL
    $URL = (isset($data['UserData']["Config_Principal_FeedURL"])&&$data['UserData']["Config_Principal_FeedURL"]!=''&&$data['UserData']["Config_Principal_FeedURL"]!='0')
            ? $data['UserData']["Config_Principal_FeedURL"]
            : 'https://feeds.elpais.com/mrss-s/pages/ep/site/elpais.com/section/chile/portada';
    //Opciones
    $Options = [
        'Titulo' => 'elpais.com',
        'URL'    => $URL,
        'Type'   => 2,
        'BASE'   => $BASE,
    ];
    $data['Fnc_WidgetsCommon']->widget_feed($Options);
    ?>
</div>
