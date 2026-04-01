<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
    <?php
    $Options = [
        'BASE'      => $BASE,
        'Type'      => 1,
        'latitude'  => $data['UserData']['Latitud'],
        'longitude' => $data['UserData']['Longitud'],
    ];
    $data['Fnc_WidgetsCommon']->widget_meteo($Options);
    ?>
</div>

