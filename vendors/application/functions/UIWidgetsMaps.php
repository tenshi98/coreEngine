<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class UIWidgetsMaps {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	//Definiciones
	private $Alertas;
    const buscar     = ['Nº', 'nº', ' n ', "'", 'Av.', 'av.'];
    const reemplazar = ['',   '',   '',   '',  'Avenida', 'Avenida'];


	/************************************************************************************************************/
	//Instancias
	public function __construct() {
		$this->Alertas = new UIWidgetsCommon();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /************************************************************************************************************/
    public function GMaps_from_gps($Options){
		/*
		*=================================================     Detalles    =================================================
		*
		* Despliega un mapa desde una latitud y longitud entregadas, se genera un infowindow en el marcador centrado
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		*   $Options = [
		*		'Latitud'    => -77.5,                    //Latitud de la ubicacion
		*		'Longitud'   => -33.6,                    //Longitud de la ubicacion
		*		'Titulo'     => 'Casa',                   //Titulo del InfoWindow
		*		'SubTitulo'  => 'mi casa',                //SubTitulo del InfoWindow
		*		'Contenido'  => 'descripcion de la casa', //Contenido del InfoWindow
		*		'IDGoogle'   => 'xxKeyGooglexx',          //API de Google Maps
		*		'zoom_map'   => 15,                       //Zoom del mapa
		*		'MapTypeId'  => 1,                        //Tipo de mapa
		*		'BASE'       => 'http://google.cl',       //Ruta base del sistema
		*		'ID_Cont'    => 'div_1',                  //ID del div donde se dibuja el html
		*	];
		* 	$UIWidgetsMaps->GMaps_from_gps($Options);
		*
		*=================================================    Parametros   =================================================
		* @input   array   $Options    array con los datos
		* @return  string
		*===================================================================================================================
		*/

        /**********************  Validaciones   **********************/
		//se definen las opciones disponibles
		$tipos = array(1, 2, 3, 4);
		//Validaciones
		if(!isset($Options['Latitud']) || $Options['Latitud']==''){   echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Latitud.');                                           exit;}
		if(!isset($Options['Longitud']) || $Options['Longitud']==''){ echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Longitud.');                                          exit;}
		if(!isset($Options['Titulo']) || $Options['Titulo']==''){     echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el Titulo.');                                            exit;}
        if(!isset($Options['IDGoogle']) || $Options['IDGoogle']==''){ echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado Una API de Google Maps.');                               exit;}
		if(!in_array($Options['MapTypeId'], $tipos)){                 echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'La configuracion $MapTypeId entregada no esta dentro de las opciones.'); exit;}

		/**********************  Definiciones   **********************/
		$Latitud    = $Options['Latitud'];
		$Longitud   = $Options['Longitud'];
		$Titulo     = $Options['Titulo'];
		$SubTitulo  = $Options['SubTitulo'] ?? '';
		$Contenido  = $Options['Contenido'] ?? $Titulo;
		$IDGoogle   = $Options['IDGoogle'];
		$zoom_map   = $Options['zoom_map'] ?? 15;
		$MapTypeId  = $Options['MapTypeId'] ?? 1;
		$BASE       = $Options['BASE'];
		$ID_Cont    = $Options['ID_Cont'] ?? 'div_map_'.uniqid();

		/********************** Si todo esta ok **********************/
        //Se selecciona el tipo
        switch ($MapTypeId) {
            case 1:  $int_map_type = 'ROADMAP';   break; //muestra la vista predeterminada del mapa de carreteras. Este es el tipo de mapa predeterminado.
            case 2:  $int_map_type = 'SATELLITE'; break; //muestra imágenes de satélite de Google Earth.
            case 3:  $int_map_type = 'HYBRID';    break; //muestra una mezcla de vistas normales y de satélite.
            case 4:  $int_map_type = 'TERRAIN';   break; //muestra un mapa físico basado en la información del terreno.
            default: $int_map_type = 'ROADMAP';   break; // valor por defecto
        }

        /**********************/
        $mapa = '
        <script async src="https://maps.googleapis.com/maps/api/js?key='.$IDGoogle.'&callback=initMap"></script>
        <script type="text/javascript">
            let map;

            async function initMap() {
                const { Map } = await google.maps.importLibrary("maps");

                var myLatlng = new google.maps.LatLng('.$Latitud.', '.$Longitud.');

                var myOptions = {
                    zoom: '.$zoom_map.',
                    scrollwheel: false,
                    center: myLatlng,
                    mapTypeId: google.maps.MapTypeId.'.$int_map_type.'
                };

                map = new Map(document.getElementById("'.$ID_Cont.'"), myOptions);

                // marker position
                var factory = new google.maps.LatLng('.$Latitud.', '.$Longitud.');

                // InfoWindow content
                var content = 	\'<div id="iw-container">\' +
                                \'<div class="iw-title">'.$Titulo.'</div>\' +
                                \'<div class="iw-content">\' +
                                \'<div class="iw-subTitle">'.$SubTitulo.'</div>\' +
                                \'<p>'.$Contenido.'</p>\' +
                                \'</div>\' +
                                \'<div class="iw-bottom-gradient"></div>\' +
                                \'</div>\';

                // A new Info Window is created and set content
                var infowindow = new google.maps.InfoWindow({
                    content: content,
                    maxWidth: 350
                });

                // marker options
                var marker = new google.maps.Marker({
                    position	: factory,
                    map			: map,
                    title		: "Dirección",
                    animation 	: google.maps.Animation.DROP,
                    icon      	: "'.$BASE.'/img/map-icons/1_series_orange.png"
                });

                // This event expects a click on a marker
                // When this event is fired the Info Window is opened.
                google.maps.event.addListener(marker, \'click\', function() {
                    infowindow.open(map,marker);
                });

                // Event that closes the Info Window with a click on the map
                google.maps.event.addListener(map, \'click\', function() {
                    infowindow.close();
                });

                // *
                // START INFOWINDOW CUSTOMIZE.
                // The google.maps.event.addListener() event expects
                // the creation of the infowindow HTML structure \'domready\'
                // and before the opening of the infowindow, defined styles are applied.
                // *
                google.maps.event.addListener(infowindow, \'domready\', function() {

                    // Reference to the DIV that wraps the bottom of infowindow
                    var iwOuter = $(\'.gm-style-iw\');

                    /* Since this div is in a position prior to .gm-div style-iw.
                    * We use jQuery and create a iwBackground variable,
                    * and took advantage of the existing reference .gm-style-iw for the previous div with .prev().
                    */
                    var iwBackground = iwOuter.prev();

                    // Removes background shadow DIV
                    iwBackground.children(\':nth-child(2)\').css({\'display\' : \'none\'});

                    // Removes white background DIV
                    iwBackground.children(\':nth-child(4)\').css({\'display\' : \'none\'});

                    // Moves the infowindow 25px to the right.
                    //iwOuter.parent().parent().css({left: \'5px\'});

                    // Moves the shadow of the arrow 76px to the left margin.
                    iwBackground.children(\':nth-child(1)\').attr(\'style\', function(i,s){ return s + \'left: 6px !important;\'});

                    // Moves the arrow 76px to the left margin.
                    iwBackground.children(\':nth-child(3)\').attr(\'style\', function(i,s){ return s + \'left: 6px !important;\'});

                    // Changes the desired tail shadow color.
                    iwBackground.children(\':nth-child(3)\').find(\'div\').children().css({\'box-shadow\': \'rgba(72, 181, 233, 0.6) 0px 1px 6px\', \'z-index\' : \'1\'});

                    // Reference to the div that groups the close button elements.
                    var iwCloseBtn = iwOuter.next();

                    // Apply the desired effect to the close button
                    iwCloseBtn.css({width: \'28px\',height: \'28px\', opacity: \'1\', right: \'38px\', top: \'3px\', border: \'7px solid #48b5e9\', \'border-radius\': \'13px\', \'box-shadow\': \'0 0 5px #3990B9\'});

                    // If the content of infowindow not exceed the set maximum height, then the gradient is removed.
                    if($(\'.iw-content\').height() < 140){
                        $(\'.iw-bottom-gradient\').css({display: \'none\'});
                    }

                    // The API automatically applies 0.7 opacity to the button after the mouseout event. This function reverses this event to the desired value.
                    iwCloseBtn.mouseout(function(){
                        $(this).css({opacity: \'1\'});
                    });
                });

                //muestro la infowindow al inicio
                infowindow.open(map,marker);

            }
        </script>
        <div id="'.$ID_Cont.'" style="width:100%; height:500px"></div>';

        /**********************/
        //devuelvo
        echo $mapa;

    }

    /************************************************************************************************************/
    public function GMaps_from_direccion($Options){
		/*
		*=================================================     Detalles    =================================================
		*
		* Despliega un mapa desde una ubicacion entregada, se genera un infowindow en el marcador centrado
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		*   $Options = [
		*		'Ubicacion'  => 'los lirios 455',         //Direccion de la ubicacion
		*		'Contenido'  => 'descripcion de la casa', //Contenido del InfoWindow
		*		'IDGoogle'   => 'xxKeyGooglexx',          //API de Google Maps
		*		'zoom_map'   => 15,                       //Zoom del mapa
		*		'MapTypeId'  => 1,                        //Tipo de mapa
		*		'BASE'       => 'http://google.cl',       //Ruta base del sistema
		*		'ID_Cont'    => 'div_1',                  //ID del div donde se dibuja el html
		*	];
		* 	$UIWidgetsMaps->GMaps_from_direccion($Options);
		*
		*=================================================    Parametros   =================================================
		* @input   array   $Options    array con los datos
		* @return  string
		*===================================================================================================================
		*/

        /**********************  Validaciones   **********************/
		//se definen las opciones disponibles
		$tipos = array(1, 2, 3, 4);
		//Validaciones
		if(!isset($Options['Ubicacion']) || $Options['Ubicacion']==''){     echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Ubicacion.');                                         exit;}
        if(!isset($Options['IDGoogle']) || $Options['IDGoogle']==''){       echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado Una API de Google Maps.');                               exit;}
		if(!in_array($Options['MapTypeId'], $tipos)){                       echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'La configuracion $MapTypeId entregada no esta dentro de las opciones.'); exit;}

		/**********************  Definiciones   **********************/

        /**********************/
        //Se limpian los nombres
        $Ubicacion  = str_replace(self::buscar, self::reemplazar, $Options['Ubicacion']);
		$Contenido  = $Options['Contenido'] ?? $Ubicacion;
		$IDGoogle   = $Options['IDGoogle'];
		$zoom_map   = $Options['zoom_map'] ?? 15;
		$MapTypeId  = $Options['MapTypeId'] ?? 1;
		$BASE       = $Options['BASE'];
		$ID_Cont    = $Options['ID_Cont'] ?? 'div_map_'.uniqid();

		/********************** Si todo esta ok **********************/
        //Se selecciona el tipo
        switch ($MapTypeId) {
            case 1:  $int_map_type = 'ROADMAP';   break; //muestra la vista predeterminada del mapa de carreteras. Este es el tipo de mapa predeterminado.
            case 2:  $int_map_type = 'SATELLITE'; break; //muestra imágenes de satélite de Google Earth.
            case 3:  $int_map_type = 'HYBRID';    break; //muestra una mezcla de vistas normales y de satélite.
            case 4:  $int_map_type = 'TERRAIN';   break; //muestra un mapa físico basado en la información del terreno.
            default: $int_map_type = 'ROADMAP';   break; // valor por defecto
        }

        /**********************/
        $mapa = '
        <script async src="https://maps.googleapis.com/maps/api/js?key='.$IDGoogle.'&callback=initMap"></script>
        <script>
            let map;
            var geocoder;
            var _infoBox;

            async function initMap() {
                const { Map } = await google.maps.importLibrary("maps");

                geocoder = new google.maps.Geocoder();

                var myLatlng = new google.maps.LatLng(-33.477271996598965, -70.65170304882815);

                var myOptions = {
                    zoom: '.$zoom_map.',
                    scrollwheel: false,
                    center: myLatlng,
                    mapTypeId: google.maps.MapTypeId.'.$int_map_type.'
                };

                map = new Map(document.getElementById("'.$ID_Cont.'"), myOptions);

                codeAddress();
            }

            function codeAddress() {

                geocoder.geocode( { \'address\': \''.$Ubicacion.'\'}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {

                        // marker position
                        var factory = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());

                        // InfoWindow content
                        var content = 	\'<div id="iw-container">\' +
                                        \'<div class="iw-title">Dirección</div>\' +
                                        \'<div class="iw-content">\' +
                                        \'<div class="iw-subTitle">Calle</div>\' +
                                        \'<p>'.$Contenido.'</p>\' +
                                        \'</div>\' +
                                        \'<div class="iw-bottom-gradient"></div>\' +
                                        \'</div>\';

                        // A new Info Window is created and set content
                        var infowindow = new google.maps.InfoWindow({
                            content: content,
                            maxWidth: 350
                        });

                        // marker options
                        var marker = new google.maps.Marker({
                            position	: factory,
                            map			: map,
                            title		: "Dirección",
                            animation 	: google.maps.Animation.DROP,
                            icon      	: "'.$BASE.'/img/map-icons/1_series_orange.png"
                        });

                        // This event expects a click on a marker
                        // When this event is fired the Info Window is opened.
                        google.maps.event.addListener(marker, \'click\', function() {
                            infowindow.open(map,marker);
                        });

                        // Event that closes the Info Window with a click on the map
                        google.maps.event.addListener(map, \'click\', function() {
                            infowindow.close();
                        });

                        // *
                        // START INFOWINDOW CUSTOMIZE.
                        // The google.maps.event.addListener() event expects
                        // the creation of the infowindow HTML structure \'domready\'
                        // and before the opening of the infowindow, defined styles are applied.
                        // *
                        google.maps.event.addListener(infowindow, \'domready\', function() {

                            // Reference to the DIV that wraps the bottom of infowindow
                            var iwOuter = $(\'.gm-style-iw\');

                            /* Since this div is in a position prior to .gm-div style-iw.
                            * We use jQuery and create a iwBackground variable,
                            * and took advantage of the existing reference .gm-style-iw for the previous div with .prev().
                            */
                            var iwBackground = iwOuter.prev();

                            // Removes background shadow DIV
                            iwBackground.children(\':nth-child(2)\').css({\'display\' : \'none\'});

                            // Removes white background DIV
                            iwBackground.children(\':nth-child(4)\').css({\'display\' : \'none\'});

                            // Moves the infowindow 25px to the right.
                            //iwOuter.parent().parent().css({left: \'5px\'});

                            // Moves the shadow of the arrow 76px to the left margin.
                            iwBackground.children(\':nth-child(1)\').attr(\'style\', function(i,s){ return s + \'left: 6px !important;\'});

                            // Moves the arrow 76px to the left margin.
                            iwBackground.children(\':nth-child(3)\').attr(\'style\', function(i,s){ return s + \'left: 6px !important;\'});

                            // Changes the desired tail shadow color.
                            iwBackground.children(\':nth-child(3)\').find(\'div\').children().css({\'box-shadow\': \'rgba(72, 181, 233, 0.6) 0px 1px 6px\', \'z-index\' : \'1\'});

                            // Reference to the div that groups the close button elements.
                            var iwCloseBtn = iwOuter.next();

                            // Apply the desired effect to the close button
                            iwCloseBtn.css({width: \'28px\',height: \'28px\', opacity: \'1\', right: \'38px\', top: \'3px\', border: \'7px solid #48b5e9\', \'border-radius\': \'13px\', \'box-shadow\': \'0 0 5px #3990B9\'});

                            // If the content of infowindow not exceed the set maximum height, then the gradient is removed.
                            if($(\'.iw-content\').height() < 140){
                                $(\'.iw-bottom-gradient\').css({display: \'none\'});
                            }

                            // The API automatically applies 0.7 opacity to the button after the mouseout event. This function reverses this event to the desired value.
                            iwCloseBtn.mouseout(function(){
                                $(this).css({opacity: \'1\'});
                            });
                        });

                        //muestro la infowindow al inicio
                        infowindow.open(map,marker);


                    } else {
                        alert(\'Geocode was not successful for the following reason: \' + status);
                    }
                });
            }

        </script>
        <div id="'.$ID_Cont.'" style="width:100%; height:500px"></div>';

        /**********************/
        //devuelvo
        echo $mapa;

    }

    /************************************************************************************************************/
    public function GMaps_from_ubicacion_mixta($Options){
		/*
		*=================================================     Detalles    =================================================
		*
		* Despliega un mapa desde dos ubicaciones entregadas, se genera un infowindow en el marcador de ambas ubicaciones
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		*   $Options = [
		*		'Ubicacion_1'  => 'los lirios 455',         //Direccion de la ubicacion
		*		'Contenido_1'  => 'descripcion de la casa', //Contenido del InfoWindow
		*		'Ubicacion_2'  => 'los lirios 455',         //Direccion de la ubicacion
		*		'Contenido_2'  => 'descripcion de la casa', //Contenido del InfoWindow
		*		'IDGoogle'     => 'xxKeyGooglexx',          //API de Google Maps
		*		'zoom_map'     => 15,                       //Zoom del mapa
		*		'MapTypeId'    => 1,                        //Tipo de mapa
		*		'BASE'         => 'http://google.cl',       //Ruta base del sistema
		*	];
		* 	$UIWidgetsMaps->GMaps_from_ubicacion_mixta($Options);
		*
		*=================================================    Parametros   =================================================
		* @input   array   $Options    array con los datos
		* @return  string
		*===================================================================================================================
		*/

        /**********************  Validaciones   **********************/
		//se definen las opciones disponibles
		$tipos = array(1, 2, 3, 4);
		//Validaciones
		if(!isset($Options['Ubicacion_1']) || $Options['Ubicacion_1']==''){  echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Ubicacion 1.');                                       exit;}
        if(!isset($Options['Ubicacion_2']) || $Options['Ubicacion_2']==''){  echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Ubicacion 2.');                                       exit;}
        if(!isset($Options['IDGoogle']) || $Options['IDGoogle']==''){        echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado Una API de Google Maps.');                               exit;}
		if(!in_array($Options['MapTypeId'], $tipos)){                        echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'La configuracion $MapTypeId entregada no esta dentro de las opciones.'); exit;}

		/**********************  Definiciones   **********************/

        /**********************/
        //Se limpian los nombres
        $Ubicacion_1 = str_replace(self::buscar, self::reemplazar, $Options['Ubicacion_1']);
        $Ubicacion_2 = str_replace(self::buscar, self::reemplazar, $Options['Ubicacion_2']);
		$Contenido_1 = $Options['Contenido'] ?? $Ubicacion_1;
		$Contenido_2 = $Options['Contenido'] ?? $Ubicacion_2;
		$IDGoogle    = $Options['IDGoogle'];
		$zoom_map    = $Options['zoom_map'] ?? 15;
		$MapTypeId   = $Options['MapTypeId'] ?? 1;
		$BASE        = $Options['BASE'];
		$ID_Cont     = $Options['ID_Cont'] ?? 'div_map_'.uniqid();

		/********************** Si todo esta ok **********************/
        //Se selecciona el tipo
        switch ($MapTypeId) {
            case 1:  $int_map_type = 'ROADMAP';   break; //muestra la vista predeterminada del mapa de carreteras. Este es el tipo de mapa predeterminado.
            case 2:  $int_map_type = 'SATELLITE'; break; //muestra imágenes de satélite de Google Earth.
            case 3:  $int_map_type = 'HYBRID';    break; //muestra una mezcla de vistas normales y de satélite.
            case 4:  $int_map_type = 'TERRAIN';   break; //muestra un mapa físico basado en la información del terreno.
            default: $int_map_type = 'ROADMAP';   break; // valor por defecto
        }

        /**********************/
        $mapa = '
        <script async src="https://maps.googleapis.com/maps/api/js?key='.$IDGoogle.'&callback=initMap_'.$ID_Cont.'"></script>
        <script>
            let map_'.$ID_Cont.';
            var geocoder;

            async function initMap_'.$ID_Cont.'() {
                const { Map } = await google.maps.importLibrary("maps");

                geocoder = new google.maps.Geocoder();

                var myLatlng = new google.maps.LatLng(-33.477271996598965, -70.65170304882815);

                var myOptions = {
                    zoom: '.$zoom_map.',
                    scrollwheel: false,
                    center: myLatlng,
                    mapTypeId: google.maps.MapTypeId.'.$int_map_type.'
                };

                map_'.$ID_Cont.' = new Map(document.getElementById("'.$ID_Cont.'"), myOptions);
                codeAddress_'.$ID_Cont.'();
            }

            function codeAddress_'.$ID_Cont.'() {
                bounds  = new google.maps.LatLngBounds();

                geocoder.geocode( { \'address\': \''.$Ubicacion_1.'\'}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {

                        // marker position
                        var factory_1 = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());

                        // InfoWindow content
                        var content = 	\'<div id="iw-container">\' +
                                        \'<div class="iw-title">Dirección</div>\' +
                                        \'<div class="iw-content">\' +
                                        \'<div class="iw-subTitle">Calle</div>\' +
                                        \'<p>'.$Contenido_1.'</p>\' +
                                        \'</div>\' +
                                        \'<div class="iw-bottom-gradient"></div>\' +
                                        \'</div>\';

                        // A new Info Window is created and set content
                        var infowindow = new google.maps.InfoWindow({
                            content: content,
                            maxWidth: 350
                        });

                        // marker options
                        var marker_1 = new google.maps.Marker({
                            position	: factory_1,
                            map			: map_'.$ID_Cont.',
                            title		: "Dirección",
                            animation 	: google.maps.Animation.DROP,
                            icon      	: "'.$BASE.'/img/map-icons/1_series_orange.png"
                        });

                        // This event expects a click on a marker_1
                        // When this event is fired the Info Window is opened.
                        google.maps.event.addListener(marker_1, \'click\', function() {
                            infowindow.open(map_'.$ID_Cont.',marker_1);
                        });

                        // Event that closes the Info Window with a click on the map
                        google.maps.event.addListener(map, \'click\', function() {
                            infowindow.close();
                        });

                        // *
                        // START INFOWINDOW CUSTOMIZE.
                        // The google.maps.event.addListener() event expects
                        // the creation of the infowindow HTML structure \'domready\'
                        // and before the opening of the infowindow, defined styles are applied.
                        // *
                        google.maps.event.addListener(infowindow, \'domready\', function() {

                            // Reference to the DIV that wraps the bottom of infowindow
                            var iwOuter = $(\'.gm-style-iw\');

                            /* Since this div is in a position prior to .gm-div style-iw.
                            * We use jQuery and create a iwBackground variable,
                            * and took advantage of the existing reference .gm-style-iw for the previous div with .prev().
                            */
                            var iwBackground = iwOuter.prev();

                            // Removes background shadow DIV
                            iwBackground.children(\':nth-child(2)\').css({\'display\' : \'none\'});

                            // Removes white background DIV
                            iwBackground.children(\':nth-child(4)\').css({\'display\' : \'none\'});

                            // Moves the infowindow 25px to the right.
                            //iwOuter.parent().parent().css({left: \'5px\'});

                            // Moves the shadow of the arrow 76px to the left margin.
                            iwBackground.children(\':nth-child(1)\').attr(\'style\', function(i,s){ return s + \'left: 6px !important;\'});

                            // Moves the arrow 76px to the left margin.
                            iwBackground.children(\':nth-child(3)\').attr(\'style\', function(i,s){ return s + \'left: 6px !important;\'});

                            // Changes the desired tail shadow color.
                            iwBackground.children(\':nth-child(3)\').find(\'div\').children().css({\'box-shadow\': \'rgba(72, 181, 233, 0.6) 0px 1px 6px\', \'z-index\' : \'1\'});

                            // Reference to the div that groups the close button elements.
                            var iwCloseBtn = iwOuter.next();

                            // Apply the desired effect to the close button
                            iwCloseBtn.css({width: \'28px\',height: \'28px\', opacity: \'1\', right: \'38px\', top: \'3px\', border: \'7px solid #48b5e9\', \'border-radius\': \'13px\', \'box-shadow\': \'0 0 5px #3990B9\'});

                            // If the content of infowindow not exceed the set maximum height, then the gradient is removed.
                            if($(\'.iw-content\').height() < 140){
                                $(\'.iw-bottom-gradient\').css({display: \'none\'});
                            }

                            // The API automatically applies 0.7 opacity to the button after the mouseout event. This function reverses this event to the desired value.
                            iwCloseBtn.mouseout(function(){
                                $(this).css({opacity: \'1\'});
                            });
                        });

                        //muestro la infowindow al inicio
                        infowindow.open(map_'.$ID_Cont.',marker_1);

                        /*var marker_1 = new google.maps.Marker({
                                    position:  new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng()),
                                    map: map_'.$ID_Cont.',
                                    title:"Marcador"

                                });
                        var infowindow = new google.maps.InfoWindow({
                            content: "'.$Contenido_1.'"
                        });
                        marker_1.addListener(\'click\', function() {
                            infowindow.open(map_'.$ID_Cont.', marker_1);
                        });
                        infowindow.open(map_'.$ID_Cont.', marker_1);*/

                        loc = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
                        bounds.extend(loc);

                    } else {
                        alert(\'Geocode was not successful for the following reason: \' + status);
                    }
                });

                geocoder.geocode( { \'address\': \''.$Ubicacion_2.'\'}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {

                        // marker position
                        var factory_2 = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());

                        // InfoWindow content
                        var content = 	\'<div id="iw-container">\' +
                                        \'<div class="iw-title">Dirección</div>\' +
                                        \'<div class="iw-content">\' +
                                        \'<div class="iw-subTitle">Calle</div>\' +
                                        \'<p>'.$Contenido_2.'</p>\' +
                                        \'</div>\' +
                                        \'<div class="iw-bottom-gradient"></div>\' +
                                        \'</div>\';

                        // A new Info Window is created and set content
                        var infowindow = new google.maps.InfoWindow({
                            content: content,
                            maxWidth: 350
                        });

                        // marker options
                        var marker_2 = new google.maps.Marker({
                            position	: factory_2,
                            map			: map_'.$ID_Cont.',
                            title		: "Dirección",
                            animation 	: google.maps.Animation.DROP,
                            icon      	: "'.$BASE.'/img/map-icons/1_series_green.png"
                        });

                        // This event expects a click on a marker_2
                        // When this event is fired the Info Window is opened.
                        google.maps.event.addListener(marker_2, \'click\', function() {
                            infowindow.open(map_'.$ID_Cont.',marker_2);
                        });

                        // Event that closes the Info Window with a click on the map
                        google.maps.event.addListener(map, \'click\', function() {
                            infowindow.close();
                        });

                        // *
                        // START INFOWINDOW CUSTOMIZE.
                        // The google.maps.event.addListener() event expects
                        // the creation of the infowindow HTML structure \'domready\'
                        // and before the opening of the infowindow, defined styles are applied.
                        // *
                        google.maps.event.addListener(infowindow, \'domready\', function() {

                            // Reference to the DIV that wraps the bottom of infowindow
                            var iwOuter = $(\'.gm-style-iw\');

                            /* Since this div is in a position prior to .gm-div style-iw.
                            * We use jQuery and create a iwBackground variable,
                            * and took advantage of the existing reference .gm-style-iw for the previous div with .prev().
                            */
                            var iwBackground = iwOuter.prev();

                            // Removes background shadow DIV
                            iwBackground.children(\':nth-child(2)\').css({\'display\' : \'none\'});

                            // Removes white background DIV
                            iwBackground.children(\':nth-child(4)\').css({\'display\' : \'none\'});

                            // Moves the infowindow 25px to the right.
                            //iwOuter.parent().parent().css({left: \'5px\'});

                            // Moves the shadow of the arrow 76px to the left margin.
                            iwBackground.children(\':nth-child(1)\').attr(\'style\', function(i,s){ return s + \'left: 6px !important;\'});

                            // Moves the arrow 76px to the left margin.
                            iwBackground.children(\':nth-child(3)\').attr(\'style\', function(i,s){ return s + \'left: 6px !important;\'});

                            // Changes the desired tail shadow color.
                            iwBackground.children(\':nth-child(3)\').find(\'div\').children().css({\'box-shadow\': \'rgba(72, 181, 233, 0.6) 0px 1px 6px\', \'z-index\' : \'1\'});

                            // Reference to the div that groups the close button elements.
                            var iwCloseBtn = iwOuter.next();

                            // Apply the desired effect to the close button
                            iwCloseBtn.css({width: \'28px\',height: \'28px\', opacity: \'1\', right: \'38px\', top: \'3px\', border: \'7px solid #48b5e9\', \'border-radius\': \'13px\', \'box-shadow\': \'0 0 5px #3990B9\'});

                            // If the content of infowindow not exceed the set maximum height, then the gradient is removed.
                            if($(\'.iw-content\').height() < 140){
                                $(\'.iw-bottom-gradient\').css({display: \'none\'});
                            }

                            // The API automatically applies 0.7 opacity to the button after the mouseout event. This function reverses this event to the desired value.
                            iwCloseBtn.mouseout(function(){
                                $(this).css({opacity: \'1\'});
                            });
                        });

                        //muestro la infowindow al inicio
                        infowindow.open(map_'.$ID_Cont.',marker_2);

                        /*var marker_2 = new google.maps.Marker({
                                    position:  new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng()),
                                    map: map_'.$ID_Cont.',
                                    title:"Marcador"

                                });
                        var infowindow = new google.maps.InfoWindow({
                            content: "'.$Contenido_2.'"
                        });
                        marker_2.addListener(\'click\', function() {
                            infowindow.open(map_'.$ID_Cont.', marker_2);
                        });
                        infowindow.open(map_'.$ID_Cont.', marker_2);*/

                        loc = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
                        bounds.extend(loc);
                        //centralizado y redimensionado del mapa
                        map_'.$ID_Cont.'.fitBounds(bounds);
                        map_'.$ID_Cont.'.panToBounds(bounds);

                    } else {
                        alert(\'Geocode was not successful for the following reason: \' + status);
                    }
                });
            }

        </script>
        <div id="'.$ID_Cont.'" style="width:100%; height:500px"></div>';

        /**********************/
        //devuelvo
        echo $mapa;

    }

    /************************************************************************************************************/
    public function leaFletMap_from_gps($Options){
		/*
		*=================================================     Detalles    =================================================
		*
		* Despliega un mapa desde una latitud y longitud entregadas, se genera un infowindow en el marcador centrado
		*
		*=================================================    Modo de uso  =================================================
		*
        *
        *    //variable para los circulos
        *    $arrMarkers = [
        *        [-33.4389, -70.6993, '<b>Santiago de Chile</b><br>I am a marker 1.'],
        *        [-33.4589, -70.6993, '<b>Santiago de Chile</b><br>I am a marker 2.'],
        *        [-33.4789, -70.6993, '<b>Santiago de Chile</b><br>I am a marker 3.'],
        *        [-33.4989, -70.6993, '<b>Santiago de Chile</b><br>I am a marker 4.'],
        *    ];
        *
        *   //variable para los poligonos
		*   $arrPolygon = [
        *        [[[-33.4499, -70.6993], [-33.4439, -70.7193], [-33.4519, -70.7393]], 'I am a polygon 1.'],
        *        [[[-33.465072090022645, -70.74491500854494], [-33.47681399959164, -70.74457168579103], [-33.46750651907221, -70.72465896606447]], 'I am a polygon 2.'],
        *        [[[-33.49012911778188, -70.72105407714845], [-33.494853345110236, -70.72156906127931], [-33.49356494503731, -70.71075439453126], [-33.48483195034282, -70.7102394104004]], 'I am a polygon 3.'],
        *    ];
        *
        *    //variable para los circulos
		*   $arrCircles = [
        *        [-33.4389, -70.6593, 1, 500, 'I am a circle 1.'],
        *        [-33.4589, -70.6593, 1, 500, 'I am a circle 2.'],
        *        [-33.4789, -70.6593, 1, 500, 'I am a circle 3.'],
        *        [-33.4989, -70.6593, 1, 500, 'I am a circle 4.'],
        *    ];
        *
        *   //variable para las lineas
		*   $arrPolyLine = [
        *        [[-33.4499, -70.6993], [-33.4439, -70.7193], [-33.4519, -70.7393]],
        *        [[-33.4499, -70.6993], [-33.4439, -70.7193], [-33.4519, -70.7393]],
        *        [[-33.4499, -70.6993], [-33.4439, -70.7193], [-33.4519, -70.7393]],
        *    ];
        *
		* 	//se imprime input
		*   $Options = [
        *        'Latitud'          => -77.5,          //Latitud de la ubicacion
        *        'Longitud'         => -33.6,          //Longitud de la ubicacion
        *        'ID_Map'           => 'map',          //ID del div donde se dibuja el html
        *        'Zoom'             => 13,             //Zoom del mapa
        *        'attribution'      => '&copy; Test',  //Pie de pagina del mapa
        *        'arrMarkers'       => $arrMarkers,    //array con los marcadores
        *        'arrPolygon'       => $arrPolygon,    //array con los poligonos
        *        'arrCircles'       => $arrCircles,    //array con los circulos
        *        'arrPolyLine'      => $arrPolyLine,   //array con las lineas
        *        'arrRectangle'     => $arrRectangle,  //array con los rectangulos
        *        'arrHeatMap'       => $arrHeatMap,    //array con los puntos de calor
        *        'events'           => true,           //Si se ejecuta algo al hacer click (revisar)
        *        'ConfMode'         => 1,              //Modo del mapa
        *        'defaultLayer'     => 'defaultLayer', //Layer a mostrar en la carga
        *        'scrollWheelZoom'  => true,           //Scrool del mouse
        *    ];
		* 	$UIWidgetsMaps->leaFletMap_from_gps($Options);
		*
		*=================================================    Parametros   =================================================
		* @input   array   $Options    array con los datos
		* @return  string
		*===================================================================================================================
		*/

        /**********************  Validaciones   **********************/
		if(!isset($Options['Latitud']) || $Options['Latitud']==''){   echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Latitud.');  exit;}
		if(!isset($Options['Longitud']) || $Options['Longitud']==''){ echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Longitud.'); exit;}

		/**********************  Definiciones   **********************/
		$Latitud             = $Options['Latitud'];
		$Longitud            = $Options['Longitud'];
		$ID_Map              = $Options['ID_Map'] ?? 'div_map_'.uniqid();
		$Zoom                = $Options['Zoom'] ?? 13;
		$attribution         = $Options['attribution'] ?? '&copy; Todos los derechos reservados.';
        $arrMarkers          = $Options['arrMarkers'] ?? "";
		$arrPolygon          = $Options['arrPolygon'] ?? "";
		$arrCircles          = $Options['arrCircles'] ?? "";
		$arrPolyLine         = $Options['arrPolyLine'] ?? "";
		$arrRectangle        = $Options['arrRectangle'] ?? "";
		$arrHeatMap          = $Options['arrHeatMap'] ?? "";
		$events              = $Options['events'] ?? false;
		$ConfMode            = $Options['ConfMode'] ?? 1;
		$defaultLayer        = $Options['defaultLayer'] ?? "OpenStreetMap";
		$scrollWheelZoom     = $Options['scrollWheelZoom'] ?? true;
		$dragabbleMarker     = $Options['dragabbleMarker'] ?? false;
		$dragabbleFormItems  = $Options['dragabbleFormItems'] ?? '';

        /*******************************************/
        // Se seleccionan los colores mediante un array
        $types = [
            1 => "color: '#5E81AC', fillColor: '#5E81AC', fillOpacity: 0.5,",
            2 => "color: '#BF616A', fillColor: '#BF616A', fillOpacity: 0.5,",
            3 => "color: '#D08770', fillColor: '#D08770', fillOpacity: 0.5,",
            4 => "color: '#EBCB8B', fillColor: '#EBCB8B', fillOpacity: 0.5,",
            5 => "color: '#A3BE8C', fillColor: '#A3BE8C', fillOpacity: 0.5,",
            6 => "color: '#B48EAD', fillColor: '#B48EAD', fillOpacity: 0.5,"
        ];
        $colors = [
            1 => "color: '#5E81AC',",
            2 => "color: '#BF616A',",
            3 => "color: '#D08770',",
            4 => "color: '#EBCB8B',",
            5 => "color: '#A3BE8C',",
            6 => "color: '#B48EAD',"
        ];

		/********************** Si todo esta ok **********************/

        $mapa = "
        <div id='".$ID_Map."'></div>
        <style>
        #".$ID_Map."{width: 100%;aspect-ratio: 16 / 9;}
        </style>
        <script type='text/javascript'>
            //Layers
            var OpenStreetMap            = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19,attribution: '".$attribution."'});
            var OpenStreetMap_HOT        = L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {maxZoom: 19,attribution: '".$attribution."'});
            var openTopoMap              = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {maxZoom: 17,attribution: '".$attribution."'});
            var OpenStreetMap_France     = L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {maxZoom: 20,attribution: '".$attribution."'});
            var OpenStreetMap_CAT        = L.tileLayer('https://tile.openstreetmap.bzh/ca/{z}/{x}/{y}.png', {maxZoom: 19,attribution: '".$attribution."'});
            var OPNVKarte                = L.tileLayer('https://tileserver.memomaps.de/tilegen/{z}/{x}/{y}.png', {maxZoom: 18,attribution: '".$attribution."'});
            var Esri_WorldStreetMap      = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {attribution: '".$attribution."'});
            var Esri_WorldTopoMap        = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {attribution: '".$attribution."'});
            var Esri_WorldImagery        = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {attribution: '".$attribution."'});
            var Esri_WorldTerrain        = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Terrain_Base/MapServer/tile/{z}/{y}/{x}', {attribution: '".$attribution."',maxZoom: 13});
            var Esri_WorldShadedRelief   = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Shaded_Relief/MapServer/tile/{z}/{y}/{x}', {attribution: '".$attribution."',maxZoom: 13});
            var Esri_WorldPhysical       = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Physical_Map/MapServer/tile/{z}/{y}/{x}', {attribution: '".$attribution."',maxZoom: 8});
            var Esri_OceanBasemap        = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Ocean/World_Ocean_Base/MapServer/tile/{z}/{y}/{x}', {attribution: '".$attribution."',maxZoom: 13});
            var Esri_NatGeoWorldMap      = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/NatGeo_World_Map/MapServer/tile/{z}/{y}/{x}', {attribution: '".$attribution."',maxZoom: 16});
            var GeoportailFrance_plan    = L.tileLayer('https://data.geopf.fr/wmts?REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0&STYLE={style}&TILEMATRIXSET=PM&FORMAT={format}&LAYER=GEOGRAPHICALGRIDSYSTEMS.PLANIGNV2&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}', {attribution: '".$attribution."',minZoom: 2,maxZoom: 18,format: 'image/png',style: 'normal'});
            var GeoportailFrance_orthos  = L.tileLayer('https://data.geopf.fr/wmts?REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0&STYLE={style}&TILEMATRIXSET=PM&FORMAT={format}&LAYER=ORTHOIMAGERY.ORTHOPHOTOS&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}', {attribution: '".$attribution."',minZoom: 2,maxZoom: 19,format: 'image/jpeg',style: 'normal'});
            var USGS_USTopo              = L.tileLayer('https://basemap.nationalmap.gov/arcgis/rest/services/USGSTopo/MapServer/tile/{z}/{y}/{x}', {maxZoom: 20,attribution: '".$attribution."'});
            var USGS_USImagery           = L.tileLayer('https://basemap.nationalmap.gov/arcgis/rest/services/USGSImageryOnly/MapServer/tile/{z}/{y}/{x}', {maxZoom: 20,attribution: '".$attribution."'});
            var USGS_USImageryTopo       = L.tileLayer('https://basemap.nationalmap.gov/arcgis/rest/services/USGSImageryTopo/MapServer/tile/{z}/{y}/{x}', {maxZoom: 20,attribution: '".$attribution."'});
            var TopPlusOpen_Color        = L.tileLayer('http://sgx.geodatenzentrum.de/wmts_topplus_open/tile/1.0.0/web/default/WEBMERCATOR/{z}/{y}/{x}.png', {maxZoom: 18,attribution: '".$attribution."'});
            var NASAGIBS_EarthAtNight    = L.tileLayer('https://map1.vis.earthdata.nasa.gov/wmts-webmerc/VIIRS_CityLights_2012/default/{time}/{tilematrixset}{maxZoom}/{z}/{y}/{x}.{format}', {attribution: '".$attribution."',minZoom: 1,maxZoom: 8,format: 'jpg',time: '',tilematrixset: 'GoogleMapsCompatible_Level'});
            var m_color                  = L.tileLayer('https://tile.mierune.co.jp/mierune/{z}/{x}/{y}.png', {attribution:'".$attribution."',}); //MIERUNE Color
            var m_mono                   = L.tileLayer('https://tile.mierune.co.jp/mierune_mono/{z}/{x}/{y}.png', {attribution:'".$attribution."',}); //MIERUNE MONO

            ";

            //Se verifica la configuracion
            switch ($ConfMode) {
                case 1:
                    $mapa .= "
                    //Carga de los layers
                    var baseMaps = {
                        'OpenStreetMap': OpenStreetMap,
                        'OpenStreetMap: HOT': OpenStreetMap_HOT,
                        'OpenStreetMap_France': OpenStreetMap_France,
                        'OpenStreetMap_CAT': OpenStreetMap_CAT,
                        'openTopoMap': openTopoMap,
                        'OPNVKarte': OPNVKarte,
                        'Esri_WorldStreetMap': Esri_WorldStreetMap,
                        'Esri_WorldTopoMap': Esri_WorldTopoMap,
                        'Esri_WorldImagery': Esri_WorldImagery,
                        'Esri_WorldTerrain': Esri_WorldTerrain,
                        'Esri_WorldShadedRelief': Esri_WorldShadedRelief,
                        'Esri_WorldPhysical': Esri_WorldPhysical,
                        'Esri_OceanBasemap': Esri_OceanBasemap,
                        'Esri_NatGeoWorldMap': Esri_NatGeoWorldMap,
                        'GeoportailFrance_plan': GeoportailFrance_plan,
                        'GeoportailFrance_orthos': GeoportailFrance_orthos,
                        'USGS_USTopo': USGS_USTopo,
                        'USGS_USImagery': USGS_USImagery,
                        'USGS_USImageryTopo': USGS_USImageryTopo,
                        'TopPlusOpen_Color': TopPlusOpen_Color,
                        'NASAGIBS_EarthAtNight': NASAGIBS_EarthAtNight,
                    };

                    //Se inicia mapa
                    var ".$ID_Map." = L.map('".$ID_Map."', {
                        center: [".$Latitud.", ".$Longitud."],
                        zoom: ".$Zoom.",
                        layers: [".$defaultLayer."],
                        scrollWheelZoom: ".$scrollWheelZoom."
                    });

                    //Se inician controles
                    L.control.layers(baseMaps).addTo(".$ID_Map.");
                    L.control.betterscale().addTo(".$ID_Map.");
                    ";
                    break;
                case 2:
                    $mapa .= "
                    //BaseLayer
                    const Map_BaseLayer = {
                        'OpenStreetMap': OpenStreetMap,
                        'OpenStreetMap: HOT': OpenStreetMap_HOT,
                        'OpenStreetMap: France': OpenStreetMap_France,
                        'OpenStreetMap: CAT': OpenStreetMap_CAT,
                    };

                    //AddLayer
                    const Map_AddLayer = {
                        'MIERUNE MONO': m_mono,
                        'openTopoMap': openTopoMap,
                    };

                    //Se inicia mapa
                    var ".$ID_Map." = L.map('".$ID_Map."', {
                        center: [".$Latitud.", ".$Longitud."],
                        zoom: ".$Zoom.",
                        layers: [".$defaultLayer."],
                        scrollWheelZoom: ".$scrollWheelZoom."
                    });

                    //Se inician controles
                    L.control.layers(Map_BaseLayer, Map_AddLayer, {collapsed: false,}).addTo(".$ID_Map."); //LayerControl
                    L.control.opacity(Map_AddLayer, {label: 'Layers Opacity',}).addTo(".$ID_Map.");        //OpacityControl

                    ";
                    break;
                case 3:
                    $mapa .= "
                    //Se inicia mapa
                    var ".$ID_Map." = L.map('".$ID_Map."', {
                        center: [".$Latitud.", ".$Longitud."],
                        zoom: ".$Zoom.",
                        layers: [".$defaultLayer."],
                        zoomControl: false,
                        dragging: false,
                        scrollWheelZoom: false,
                        doubleClickZoom: false,
                        boxZoom: false,
                        touchZoom: false,
                        keyboard: false
                    });
                    ";
                    break;
                case 4:
                    $mapa .= "
                    //Se inicia mapa
                    var ".$ID_Map." = L.map('".$ID_Map."', {
                        center: [".$Latitud.", ".$Longitud."],
                        zoom: ".$Zoom.",
                        layers: [".$defaultLayer."],
                        zoomControl: true,
                        dragging: true,
                        scrollWheelZoom: false,
                        doubleClickZoom: false,
                        boxZoom: false,
                        touchZoom: false,
                        keyboard: false
                    });
                    ";
                    break;
            }

            /*******************************************/
            //Se agreg la opcion de visualizacion en los modal
            $mapa .= "
            try {
                document.getElementById('viewModal-xl').addEventListener('shown.bs.modal', function () {
                    ".$ID_Map.".invalidateSize();
                });
            } catch (error) {
                //nada
            }
            try {
                document.getElementById('viewModal-lg').addEventListener('shown.bs.modal', function () {
                    ".$ID_Map.".invalidateSize();
                });
            } catch (error) {
                //nada
            }
            ";

            /*******************************************/
            //Se agregan los marcadores
            if(is_array($arrMarkers)){
                //Id dinamico para los elementos
                $ID_marker    = 'marker_'.uniqid();
                $ID_multi     = 'marker_'.uniqid();
                //Se agrega elemento
                $mapa .= 'var '.$ID_marker.' = [';
                foreach ($arrMarkers as $item) {
                    //Se agrega elemento
                    $mapa .= '{
                        lat: '.$item[0].',
                        lng: '.$item[1].',
                        iconExName: "'.$item[2].'",
                        iconFill: "'.$item[3].'",
                        contentHtml: "'.$item[4].'",
                        content: "'.$item[5].'",
                    },';
                }
                $mapa .= '];';

                $mapa .= '
                var '.$ID_multi.' = new L.MultiMarkers('.$ID_marker.', {
                    iconExPredefined: {
                        default: {
                            iconStrokeOpacity: .5,
                        },
                        A1: {
                            iconHtml: `<svg width="32" height="40" viewBox="0 0 32 40" xmlns="http://www.w3.org/2000/svg"><path stroke-width="1" d="m 2.5,0.5 c -1.107998,0 -2,0.892002 -2,2 v 27 c 0,1.107998 0.892002,2 2,2 h 4.7044922 a 4.1676656,4.1676656 24.095192 0 1 3.1064288,1.38926 L 16,39.25 21.68908,32.88926 A 4.1676657,4.1676657 155.90481 0 1 24.795508,31.5 H 29.5 c 1.107998,0 2,-0.892002 2,-2 v -27 c 0,-1.107998 -0.892002,-2 -2,-2 z" /></svg>`,
                            backgroundHtml: `<svg width="32" height="40" viewBox="0 0 32 40" xmlns="http://www.w3.org/2000/svg"><path stroke-width="0" d="M 5.5483871,4 C 4.6905822,4 4,4.6905822 4,5.5483871 V 26.451613 C 4,27.309418 4.6905822,28 5.5483871,28 h 3.6421875 a 3.2265798,3.2265798 0 0 1 2.4049774,1.075556 L 16,34 20.404449,29.075556 A 3.2265799,3.2265799 0 0 1 22.809426,28 h 3.642187 C 27.309418,28 28,27.309418 28,26.451613 V 5.5483871 C 28,4.6905822 27.309418,4 26.451613,4 Z" /></svg>`,
                            backgroundHtmlSize: [32, 40],
                            backgroundHtmlAnchor: [16, 20],
                            contentFontSize: 18,
                        },
                        A2: {
                            iconHtml: `<svg width="32" height="48" viewBox="0 0 32 48" xmlns="http://www.w3.org/2000/svg"><path stroke-width="1" d="M 16,0.5 C 7.4396,0.5 0.5,7.4396 0.5,16 0.5,20.2802 2.0133,23.9336 5.0398,26.9602 16,37.9203 13,45 16,47.3635 19,45 16,37.9203 26.9602,26.9602 29.9867,23.9336 31.5,20.2802 31.5,16 31.5,7.4396 24.5604,0.5 16,0.5 Z" /></svg>`,
                            iconHtmlSize: [32, 48],
                            iconHtmlAnchor: [16, 48],
                            iconHtmlPopupAnchor: [0, -32],
                        },
                        A3: {
                            iconHtml: `<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><rect stroke-width="1" width="23" height="23" x=".5" y=".5" rx="4" ry="4" /></svg>`,
                            iconHtmlSize: [24, 24],
                            iconHtmlAnchor: [12, 24],
                            iconHtmlPopupAnchor: [0, -12],
                            backgroundHtml: "",
                            contentHtmlAnchor: [12, 12],
                            contentColor: "#fff",
                        },
                        A4: {
                            iconHtml: `<svg width="28" height="28" viewBox="0 0 28 28" xmlns="http://www.w3.org/2000/svg"><circle stroke-width="1" cx="14" cy="14" r="13.5" /></svg>`,
                            iconHtmlSize: [28, 28],
                            iconHtmlAnchor: [14, 28],
                            iconHtmlPopupAnchor: [0, -14],
                            backgroundHtml: "",
                            contentHtmlAnchor: [14, 14],
                            contentColor: "#fff",
                        },
                        B1: {
                            iconHtml: `<svg width="30px" height="41px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 41"><path d="M15 1a14 14 0 0 1 11.83 21.49l-11 18.02a1 1 0 0 1-1.73 0L3.15 22.45A14 14 0 0 1 15 1Z"></path></svg>`,
                            iconHtmlSize: [30, 41],
                            iconHtmlAnchor: [16, 41],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        B2: {
                            iconHtml: `<svg width="30px" height="41px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 41"><path d="M12.2 1.16a3.97 3.97 0 0 1 5.6 0L28.85 12.2a3.99 3.99 0 0 1 .39 5.16L15.93 40.4a1 1 0 0 1-1.83.05L.69 17.24a3.97 3.97 0 0 1 .47-5.05L12.2 1.16Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M12.2 1.16a3.97 3.97 0 0 1 5.6 0L28.85 12.2a3.99 3.99 0 0 1 .39 5.16L15.92 40.4a1 1 0 0 1-1.82.05L.69 17.24a3.97 3.97 0 0 1 .47-5.05L12.2 1.16Zm4.9.7a2.97 2.97 0 0 0-4.2 0L1.87 12.9a2.97 2.97 0 0 0-.36 3.77l.03.03.02.04 13 22.5a.5.5 0 0 0 .87 0l12.93-22.39.03-.05.03-.04c.86-1.16.76-2.8-.29-3.86L17.1 1.87Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 41],
                            iconHtmlAnchor: [16, 41],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        B3: {
                            iconHtml: `<svg width="30px" height="41px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 41"><path d="M22.49 2c1.21 0 2.31.71 2.8 1.82l4.45 10c.38.86.34 1.86-.11 2.7L15.88 40.48a1 1 0 0 1-1.76 0L.37 16.52a3.06 3.06 0 0 1-.1-2.7l4.43-10A3.1 3.1 0 0 1 7.51 2h14.98Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M22.49 2c1.21 0 2.31.71 2.8 1.82l4.45 10c.38.86.34 1.86-.11 2.7L15.88 40.48a1 1 0 0 1-1.76 0L.37 16.52a3.06 3.06 0 0 1-.1-2.7l4.43-10A3.1 3.1 0 0 1 7.51 2h14.98ZM7.51 3c-.82 0-1.56.48-1.9 1.23l-4.43 9.99c-.26.58-.24 1.26.07 1.82l13.32 23.2a.5.5 0 0 0 .86 0l13.31-23.19v-.01c.32-.56.34-1.23.08-1.82l-4.44-10A2.07 2.07 0 0 0 22.62 3H7.51Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 41],
                            iconHtmlAnchor: [16, 41],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        B4: {
                            iconHtml: `<svg width="30px" height="41px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 41"><path d="M24.2 2h.02A4 4 0 0 1 28 6v18.22a4 4 0 0 1-3.78 3.77H24l-.04.01h-1.62a2 2 0 0 0-1.78 1.1l-.07.14v.01l-.01.02-4.56 11.1a1 1 0 0 1-1.84 0L9.5 29.25A2 2 0 0 0 7.66 28H6a4 4 0 0 1-3.96-3.44l-.03-.3a1 1 0 0 1 0-.07l-.01-.2V6a4 4 0 0 1 4-4h18.19Z"></path><path fill="#fff" d="M24 27h-.03.02Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M24.2 2h.02A4 4 0 0 1 28 6v18.22a4 4 0 0 1-3.78 3.77H24l-.04.01h-1.62a2 2 0 0 0-1.78 1.1l-.07.14v.01l-.01.02-4.56 11.1a1 1 0 0 1-1.84 0L9.5 29.25A2 2 0 0 0 7.66 28H6a4 4 0 0 1-3.96-3.44l-.03-.3a1 1 0 0 1 0-.07l-.01-.2V6a4 4 0 0 1 4-4h18.19ZM6 3a3 3 0 0 0-3 3v18.15a.5.5 0 0 0 0 .04l.03.27A3 3 0 0 0 6 27h1.66a3 3 0 0 1 2.77 1.86l4.1 10a.5.5 0 0 0 .93 0l4.1-9.97v-.03l.01-.02.07-.17a3 3 0 0 1 2.7-1.67h1.82A3 3 0 0 0 27 24.16v-.01l.01-.14V6a3 3 0 0 0-2.54-2.96l-.3-.03h-.02a.5.5 0 0 0-.03 0h-.02L23.96 3H6Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 41],
                            iconHtmlAnchor: [16, 41],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        B5: {
                            iconHtml: `<svg width="30px" height="41px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 41"><path d="M13 .82a2.85 2.85 0 0 1 4 0l1.94 1.9.2.18c.5.4 1.12.63 1.76.63l2.72.03a2.85 2.85 0 0 1 2.82 2.82l.03 2.72.01.27c.07.64.35 1.23.8 1.7l1.9 1.94a2.85 2.85 0 0 1 0 3.99l-1.9 1.94a2.85 2.85 0 0 0-.8 1.69l-.01.27-.03 2.72a2.85 2.85 0 0 1-2.53 2.8l-.29.02-1.33.02a2 2 0 0 0-1.86 1.32l-4.47 12.5-.02.08-.05.1c-.05.1-.06.1-.09.13a1 1 0 0 1-.7.4l-.12.01a1 1 0 0 1-.92-.64l-4.5-12.58a2 2 0 0 0-1.7-1.32h-.15l-1.33-.02a2.85 2.85 0 0 1-2.82-2.82l-.03-2.72c0-.73-.3-1.44-.81-1.96L.82 17a2.85 2.85 0 0 1 0-4l1.9-1.94c.51-.52.8-1.23.81-1.96l.03-2.72.02-.29a2.85 2.85 0 0 1 2.8-2.53l2.72-.03a2 2 0 0 0 .1 0h.08l.1-.01a2.85 2.85 0 0 0 1.68-.8l1.95-1.9Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M13 .81a2.85 2.85 0 0 1 4 0l1.94 1.9.2.19c.5.4 1.12.62 1.76.63l2.72.03a2.85 2.85 0 0 1 2.82 2.82l.03 2.72.01.27c.07.63.35 1.23.8 1.7l1.9 1.94a2.85 2.85 0 0 1 0 3.98l-1.9 1.95a2.85 2.85 0 0 0-.8 1.69l-.01.27-.03 2.72a2.85 2.85 0 0 1-2.53 2.8l-.29.02-1.33.01a2 2 0 0 0-1.86 1.33l-4.47 12.5-.02.08-.05.1c-.05.1-.06.1-.09.13a1 1 0 0 1-.7.4h-.12a1 1 0 0 1-.92-.63l-4.5-12.58a2 2 0 0 0-1.7-1.32h-.15l-1.33-.02a2.85 2.85 0 0 1-2.82-2.82l-.03-2.72c0-.73-.3-1.44-.81-1.96l-1.9-1.95a2.85 2.85 0 0 1 0-3.98l1.9-1.95c.51-.52.8-1.23.81-1.96l.03-2.72.02-.29a2.85 2.85 0 0 1 2.8-2.53l2.72-.03a2 2 0 0 0 .1 0h.08l.1-.02a2.85 2.85 0 0 0 1.68-.8l1.95-1.9Zm3.3.72c-.72-.7-1.87-.7-2.6 0l-1.94 1.9a3.85 3.85 0 0 1-2.42 1.09h-.08l-.15.01-2.72.03c-.94 0-1.7.71-1.82 1.62v.03l-.01.18v.03l-.03 2.69c0 .99-.4 1.94-1.1 2.65l-1.9 1.95c-.7.71-.7 1.86 0 2.58l1.9 1.95a3.85 3.85 0 0 1 1.1 2.46v.2l.03 2.7a1.85 1.85 0 0 0 1.83 1.84l1.33.01h.04l.14.01h.06a3 3 0 0 1 2.47 1.78l.08.2 4.02 11.24a.5.5 0 0 0 .94 0l4.02-11.24a3 3 0 0 1 2.8-1.99l1.27-.01h.02l.22-.01h.03c.9-.12 1.6-.89 1.61-1.82l.03-2.72v-.05l.02-.27v-.05c.1-.86.47-1.66 1.08-2.28l1.9-1.95c.7-.72.7-1.87 0-2.58l-1.9-1.95a3.85 3.85 0 0 1-1.03-1.96l-.05-.32v-.06l-.02-.26V9.1l-.03-2.72v-.16l-.01-.03-.03-.17a1.85 1.85 0 0 0-1.79-1.47l-2.72-.03c-.87 0-1.7-.31-2.37-.85l-.02-.02-.03-.02-.2-.17-.02-.02-.01-.02-1.95-1.9Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 41],
                            iconHtmlAnchor: [16, 41],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        B6: {
                            iconHtml: `<svg width="30px" height="41px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 41"><path d="M15 0c.53 0 1.08.14 1.56.44C24.12 5.06 29.3 12.96 29.99 21.9a3.06 3.06 0 0 1-1.66 2.97 29.2 29.2 0 0 1-5.26 2.04 3 3 0 0 0-1.77 1.49l-.07.13-5.26 11.78-.04.08a1 1 0 0 1-1.82 0l-.03-.05-5.53-11.91a3 3 0 0 0-1.72-1.55l-.06-.02a29.19 29.19 0 0 1-5.1-2A3.06 3.06 0 0 1 .01 21.9c.52-6.8 3.65-13 8.44-17.62A26.35 26.35 0 0 1 12.65.9l.02-.01.03-.02.65-.41.18-.1.18-.1c.4-.18.84-.27 1.28-.26Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M15 0c.53 0 1.08.14 1.56.44C24.12 5.06 29.3 12.96 29.99 21.9a3.06 3.06 0 0 1-1.66 2.97 29.5 29.5 0 0 1-5.26 2.04 3 3 0 0 0-1.77 1.49l-.07.13-5.26 11.78a3.2 3.2 0 0 0-.04.08 1 1 0 0 1-1.83 0l-.02-.05-5.53-11.91a3 3 0 0 0-1.72-1.55l-.06-.02a29.19 29.19 0 0 1-5.1-2A3.06 3.06 0 0 1 .01 21.9c.52-6.8 3.65-13 8.44-17.62A26.35 26.35 0 0 1 12.65.9l.02-.01.03-.02.65-.41.18-.1.18-.1c.4-.18.84-.27 1.28-.26Zm-.03 1c-.29 0-.58.05-.85.17l-.08.05a.55.55 0 0 0-.04.02l-.13.07-.01.01-.62.39h-.01l-.01.01h-.01v.01l-.03.02h-.01C11.7 2.71 10.37 3.8 9.16 5l-.02.01A26.4 26.4 0 0 0 1.01 22c-.07.84.38 1.62 1.11 2l.52.25c1.23.59 2.48 1.08 3.77 1.48l.63.2h.03l.06.02h.02l.01.01a4 4 0 0 1 2.3 2.06v.01L14.56 39a.5.5 0 0 0 .9 0l4.86-10.87.01-.03.07-.13.01-.03a4 4 0 0 1 2.37-1.98h.02a28.2 28.2 0 0 0 5.08-1.98l.13-.07c.65-.4 1.04-1.14.98-1.92A26.78 26.78 0 0 0 16.72 1.72l-.68-.43-.12-.07A2 2 0 0 0 15 1h-.03Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 41],
                            iconHtmlAnchor: [16, 41],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        B7: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="41px" viewBox="0 0 30 41"><path d="M15 1a14 14 0 0 1 11.83 21.49l-11 18.02a1 1 0 0 1-1.73 0L3.15 22.45A14 14 0 0 1 15 1Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M15 1a14 14 0 0 1 11.83 21.49l-11 18.02a1 1 0 0 1-1.73 0L3.15 22.45A14 14 0 0 1 15 1Zm0 2a12 12 0 1 0 0 24 12 12 0 0 0 0-24Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 41],
                            iconHtmlAnchor: [16, 41],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        B8: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="41px" viewBox="0 0 30 41"><path d="M12.2 1.16a3.97 3.97 0 0 1 5.6 0L28.85 12.2a3.99 3.99 0 0 1 .39 5.16L15.93 40.4a1 1 0 0 1-1.83.05L.69 17.24a3.97 3.97 0 0 1 .47-5.05L12.2 1.16Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M12.2 1.16a3.97 3.97 0 0 1 5.6 0L28.85 12.2a3.99 3.99 0 0 1 .39 5.16L15.93 40.4a1 1 0 0 1-1.83.05L.69 17.24a3.97 3.97 0 0 1 .47-5.05L12.2 1.16Zm4.19 1.42a1.97 1.97 0 0 0-2.78 0L2.57 13.6a1.97 1.97 0 0 0 0 2.78l11.04 11.03c.77.77 2.01.77 2.78 0L27.42 16.4c.77-.77.77-2.01 0-2.78L16.4 2.58Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 41],
                            iconHtmlAnchor: [16, 41],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        B9: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="41px" viewBox="0 0 30 41"><path d="M22.49 2c1.21 0 2.31.71 2.8 1.82l4.45 10c.38.86.34 1.86-.11 2.7L15.88 40.48a1 1 0 0 1-1.76 0L.37 16.52a3.06 3.06 0 0 1-.1-2.7l4.43-10A3.1 3.1 0 0 1 7.51 2h14.98Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M22.49 2c1.21 0 2.31.71 2.8 1.82l4.45 10c.38.86.34 1.86-.11 2.7L15.88 40.48a1 1 0 0 1-1.76 0L.37 16.52a3.06 3.06 0 0 1-.1-2.7l4.43-10A3.1 3.1 0 0 1 7.51 2h14.98ZM7.42 4c-.39 0-.76.22-.96.59l-.06.12-4.3 9.86c-.19.43-.1.93.19 1.27l11.89 13.78c.22.25.52.38.82.38.3 0 .6-.13.82-.38l11.9-13.78c.29-.34.37-.84.18-1.27l-4.3-9.89A1.1 1.1 0 0 0 22.57 4H7.42Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 41],
                            iconHtmlAnchor: [16, 41],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        B10: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="41px" viewBox="0 0 30 41"><path d="M24.2 2h.02A4 4 0 0 1 28 6v18.22a4 4 0 0 1-3.78 3.77H24l-.04.01h-1.62a2 2 0 0 0-1.78 1.1l-.07.14v.01l-.01.02-4.56 11.1a1 1 0 0 1-1.84 0L9.5 29.25A2 2 0 0 0 7.66 28H6a4 4 0 0 1-3.96-3.44l-.03-.3a1 1 0 0 1 0-.07l-.01-.2V6a4 4 0 0 1 4-4h18.19Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M24.2 2h.02A4 4 0 0 1 28 6v18.22a4 4 0 0 1-3.78 3.77H24l-.04.01h-1.62a2 2 0 0 0-1.78 1.1l-.07.14v.01l-.01.02-4.56 11.1a1 1 0 0 1-1.84 0L9.5 29.25A2 2 0 0 0 7.66 28H6a4 4 0 0 1-3.96-3.44l-.03-.3a1 1 0 0 1 0-.07l-.01-.2V6a4 4 0 0 1 4-4h18.19ZM6 4a2 2 0 0 0-2 2v18c0 1.1.9 2 2 2h18a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 41],
                            iconHtmlAnchor: [16, 41],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        B11: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="41px" viewBox="0 0 30 41"><path d="M13 .82a2.85 2.85 0 0 1 4 0l1.94 1.9.2.18c.5.4 1.12.63 1.76.63l2.72.03a2.85 2.85 0 0 1 2.82 2.82l.03 2.72.01.27c.07.64.35 1.23.8 1.7l1.9 1.94a2.85 2.85 0 0 1 0 3.99l-1.9 1.94a2.85 2.85 0 0 0-.8 1.69l-.01.27-.03 2.72a2.85 2.85 0 0 1-2.53 2.8l-.29.02-1.33.02a2 2 0 0 0-1.86 1.32l-4.47 12.5-.02.08-.05.1c-.05.1-.06.1-.09.13a1 1 0 0 1-.7.4l-.12.01a1 1 0 0 1-.92-.64l-4.5-12.58a2 2 0 0 0-1.7-1.32h-.15l-1.33-.02a2.85 2.85 0 0 1-2.82-2.82l-.03-2.72c0-.73-.3-1.44-.81-1.96L.82 17a2.85 2.85 0 0 1 0-4l1.9-1.94c.51-.52.8-1.23.81-1.96l.03-2.72.02-.29a2.85 2.85 0 0 1 2.8-2.53l2.72-.03a2 2 0 0 0 .1 0h.08l.1-.01a2.85 2.85 0 0 0 1.68-.8l1.95-1.9Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M13 .81a2.85 2.85 0 0 1 4 0l1.94 1.9.2.19c.5.4 1.12.62 1.76.63l2.72.03a2.85 2.85 0 0 1 2.82 2.82l.03 2.72.01.27c.07.63.35 1.23.8 1.7l1.9 1.94a2.85 2.85 0 0 1 0 3.98l-1.9 1.95a2.85 2.85 0 0 0-.8 1.69l-.01.27-.03 2.72a2.85 2.85 0 0 1-2.53 2.8l-.29.02-1.33.01a2 2 0 0 0-1.86 1.33l-4.47 12.5-.02.08-.05.1c-.05.1-.06.1-.09.13a1 1 0 0 1-.7.4h-.12a1 1 0 0 1-.92-.63l-4.5-12.58a2 2 0 0 0-1.7-1.32h-.15l-1.33-.02a2.85 2.85 0 0 1-2.82-2.82l-.03-2.72c0-.73-.3-1.44-.81-1.96l-1.9-1.95a2.85 2.85 0 0 1 0-3.98l1.9-1.95c.51-.52.8-1.23.81-1.96l.03-2.72.02-.29a2.85 2.85 0 0 1 2.8-2.53l2.72-.03a2 2 0 0 0 .1 0h.08l.1-.02a2.85 2.85 0 0 0 1.68-.8l1.95-1.9Zm2.6 1.43a.85.85 0 0 0-1.2 0l-1.94 1.9a4.88 4.88 0 0 1-3.34 1.4l-2.72.02a.85.85 0 0 0-.84.84l-.03 2.72a4.85 4.85 0 0 1-1.38 3.34l-1.9 1.95a.85.85 0 0 0 0 1.18l1.9 1.95c.87.9 1.37 2.09 1.38 3.34l.03 2.72c0 .46.38.84.84.84l2.72.03a4.8 4.8 0 0 1 3.34 1.38l1.95 1.9c.33.33.85.33 1.18 0l1.95-1.9a4.86 4.86 0 0 1 3.34-1.38l2.72-.03c.46 0 .84-.38.84-.84l.03-2.72a4.8 4.8 0 0 1 1.38-3.34l1.9-1.95a.85.85 0 0 0 0-1.18l-1.9-1.95a4.85 4.85 0 0 1-1.38-3.34l-.03-2.72a.85.85 0 0 0-.84-.84l-2.72-.03a4.85 4.85 0 0 1-3.34-1.38l-1.95-1.9Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 41],
                            iconHtmlAnchor: [16, 41],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        B12: {
                            iconHtml: `<svg width="30px" height="41px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 41"><path d="M15 0c.53 0 1.08.14 1.56.44C24.12 5.06 29.3 12.96 29.99 21.9a3.06 3.06 0 0 1-1.66 2.97 29.2 29.2 0 0 1-5.26 2.04 3 3 0 0 0-1.77 1.49l-.07.13-5.26 11.78-.04.08a1 1 0 0 1-1.82 0l-.03-.05-5.53-11.91a3 3 0 0 0-1.72-1.55l-.06-.02a29.19 29.19 0 0 1-5.1-2A3.06 3.06 0 0 1 .01 21.9c.52-6.8 3.65-13 8.44-17.62A26.35 26.35 0 0 1 12.65.9l.02-.01.03-.02.65-.41.18-.1.18-.1c.4-.18.84-.27 1.28-.26Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M13.8.26a2.99 2.99 0 0 1 2.76.18 28.71 28.71 0 0 1 9.8 9.96l.24.4.24.45A26.7 26.7 0 0 1 30 21.91a3.06 3.06 0 0 1-1.66 2.97 28.6 28.6 0 0 1-8.88 2.8 2 2 0 0 0-1.6 1.31l-1.89 5.3-.02.07-.04.1a.63.63 0 0 1-.06.09l-.03.03.02-.02-.01.02-.03.03a1 1 0 0 1-.58.37l-.1.02h-.13a1 1 0 0 1-.85-.5l-.06-.14v-.01l-1.92-5.36a2 2 0 0 0-1.47-1.28l-.13-.02-.55-.09a28.5 28.5 0 0 1-7.74-2.43l-.59-.29a3.06 3.06 0 0 1-1.66-2.97A26.69 26.69 0 0 1 3.4 10.8 28.69 28.69 0 0 1 13.44.44l.18-.1.18-.08Zm2 34.33Zm0 0h.01v-.01Zm-.28-32.44a.98.98 0 0 0-.96-.04l-.1.06C7.4 6.49 2.63 13.82 2 22.07c-.03.44.2.84.57 1.02A26.63 26.63 0 0 0 13 25.94h.02l.87.04h.01l.57.02h1.06l.56-.02.78-.04c3.87-.23 7.3-1.2 10.56-2.85.37-.18.6-.58.57-1.02-.64-8.27-5.43-15.6-12.48-19.92Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 41],
                            iconHtmlAnchor: [16, 41],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },

                        C1: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="35px" viewBox="0 0 30 35"><path d="M15 1a13.96 13.96 0 0 1 14 14 13.96 13.96 0 0 1-9.83 13.37 2 2 0 0 0-1.16.95l-.1.2-1.99 4.86a1 1 0 0 1-1.7.24l-.08-.1-.07-.14-2-4.86a2 2 0 0 0-1.04-1.07l-.2-.08-.53-.18-.5-.19A13.99 13.99 0 0 1 1 15 13.96 13.96 0 0 1 15 1Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M15 1a13.96 13.96 0 0 1 14 14 13.96 13.96 0 0 1-9.83 13.37 2 2 0 0 0-1.16.95l-.1.2-1.99 4.86a1 1 0 0 1-1.7.24l-.08-.1-.07-.14-1.99-4.86a2 2 0 0 0-1.05-1.07l-.2-.08-.53-.18-.5-.19A13.99 13.99 0 0 1 6.33 4 13.99 13.99 0 0 1 15 1Zm0 1a12.96 12.96 0 0 0-9.97 4.66l-.24.3a12.99 12.99 0 0 0 5.84 20.29h.01l.5.17.05.01.2.08.03.01.02.01A3 3 0 0 1 13 29.14l1.53 3.73a.5.5 0 0 0 .93 0L17 29.11l.01-.02.1-.2v-.02l.02-.03a3 3 0 0 1 1.75-1.43 12.92 12.92 0 0 0 8.2-7.57 12.96 12.96 0 0 0-4.04-15.05A12.99 12.99 0 0 0 15 2Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 35],
                            iconHtmlAnchor: [16, 35],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        C2: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="35px" viewBox="0 0 30 35"><path d="M12.2 1.17a3.97 3.97 0 0 1 5.6 0L28.84 12.2a3.97 3.97 0 0 1 .16 5.44l-.2.2-10.36 10.37a3 3 0 0 0-.7 1.11l-1.77 4.95-.02.06-.01.03-.02.04-.02.06-.01.02-.02.02-.01.03-.07.08v.01l-.02.02a1 1 0 0 1-.67.36h-.12a1 1 0 0 1-.91-.63v-.02l-1.8-5.03a3 3 0 0 0-.6-1l-.11-.11-10.4-10.4a3.97 3.97 0 0 1 0-5.61l2.4-2.4.14-.14 8.5-8.5Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M12.2 1.17a3.97 3.97 0 0 1 5.6 0L28.84 12.2a3.97 3.97 0 0 1 .16 5.44l-.2.2-10.36 10.37a3 3 0 0 0-.7 1.1l-1.77 4.96-.02.06-.01.02v.01l-.02.04-.02.06-.01.02-.02.02-.01.03-.07.08v.01l-.02.02a1 1 0 0 1-.67.36h-.12a1 1 0 0 1-.91-.63v-.02l-1.81-5.03a3 3 0 0 0-.59-1l-.11-.11-10.4-10.4a3.97 3.97 0 0 1 0-5.61l2.4-2.4.14-.14 8.5-8.5Zm4.9.7a2.97 2.97 0 0 0-3.97-.2l-.02.02-.2.17-8.5 8.5-.12.12-.03.02-.02.02-2.36 2.38h-.01a2.97 2.97 0 0 0 0 4.2l10.42 10.42.1.12.03.03c.22.25.42.52.57.82l.14.3.08.2 1.32 3.7a.5.5 0 0 0 .94 0L16.8 29l.08-.21a4 4 0 0 1 .86-1.28L28.1 17.14l.15-.16v-.01c.97-1.1 1-2.73.08-3.85l-.2-.22L17.1 1.87Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 35],
                            iconHtmlAnchor: [16, 35],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        C3: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="35px" viewBox="0 0 30 35"><path d="M21.86 2h.06l.12.01.18.02h.06l.33.06.02.01.31.08.04.01a4.22 4.22 0 0 1 .62.25l.02.01c.3.16.59.35.85.57l.19.18.17.17.06.08.1.12.05.06.11.15.04.05a3.93 3.93 0 0 1 .21.37l.05.1.07.14.04.09.09.23 4.15 12.36c.08.26.14.52.17.78l.01.14.01.14v.47l-.02.18-.02.13-.03.16-.03.14-.04.15-.04.14-.06.15-.05.14-.06.14-.06.13a4 4 0 0 1-.1.17c0 .03-.03.06-.04.09a4.08 4.08 0 0 1-.95 1.08c-.02 0-.03.02-.05.04l-.11.08-.04.03-.03.02-.01.01-9.6 6.74a3 3 0 0 0-1 1.23l-.1.22L16 34.18v.03l-.01.02-.04.1v.02l-.05.1a.55.55 0 0 1-.07.11s0 .02-.02.03a1 1 0 0 1-.7.4h-.12a1 1 0 0 1-.92-.64l-1.42-3.98-.2-.55a3 3 0 0 0-.91-1.3l-.17-.14L1.7 21.6a4.02 4.02 0 0 1-1.34-1.61c-.4-.89-.48-1.9-.16-2.87l4.13-12.3V4.8l.02-.04.01-.03.04-.11.02-.05a3.88 3.88 0 0 1 .37-.7l.05-.09a4 4 0 0 1 .1-.13L5 3.57l.08-.1.1-.1.05-.06A4.11 4.11 0 0 1 8.28 2h13.58Z"></path><path fill="#fff" d="M28.55 18.85h.01-.01Zm.44-.7v.01-.03.02ZM5.27 5.31v-.03.03Zm19.2-.7.02.03a.5.5 0 0 0-.05-.05l.03.02Zm-.05-.04h-.01Zm-.22-.11.03.02-.05-.02h.01Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M21.86 2h.06l.12.02.18.01.06.01.32.06h.03l.31.09h.04a4.22 4.22 0 0 1 .62.25l.02.02c.3.15.59.34.85.56l.19.18.17.18.06.07.1.12.05.07.11.14.04.06a3.93 3.93 0 0 1 .21.37l.05.09.07.15.04.08.09.24 4.15 12.36c.08.26.14.52.17.78l.01.14.01.13v.48l-.02.17-.02.13-.03.16-.03.14-.04.16-.04.13-.06.16-.05.13-.06.14-.06.14a4 4 0 0 1-.1.16c0 .03-.03.06-.04.09a4.15 4.15 0 0 1-.95 1.08l-.05.04-.11.09-.04.02-.03.02-.01.01-9.6 6.75a3 3 0 0 0-1 1.23l-.1.21L16 34.2v.02l-.01.02-.04.1v.03l-.05.1a.56.56 0 0 1-.07.1l-.02.03a1 1 0 0 1-.7.4l-.12.01a1 1 0 0 1-.92-.64l-1.42-3.98-.2-.56a3 3 0 0 0-.92-1.3l-.17-.13-9.66-6.8a4.02 4.02 0 0 1-1.34-1.61c-.4-.88-.48-1.9-.16-2.86L4.33 4.82V4.8l.02-.04.01-.04.04-.1.02-.06a3.9 3.9 0 0 1 .37-.7l.05-.08.1-.14.06-.08.08-.1.1-.1.05-.06A4.12 4.12 0 0 1 8.28 2h13.58ZM8.28 3h-.02l-.25.02h-.02A3.2 3.2 0 0 0 5.96 4l-.03.03-.03.03-.01.01-.01.01v.01h-.01v.01l-.02.02-.06.06v.02l-.05.05-.07.1-.01.02-.02.02-.01.03a3.1 3.1 0 0 0-.06.09l-.04.07-.06.1a2.89 2.89 0 0 0-.08.17l-.04.1v.02l-.02.02-.02.08-.01.01v.02l-.02.03-4.13 12.32a3 3 0 0 0 1.12 3.33l9.66 6.79.02.01.01.01.17.13.02.02h.01a4 4 0 0 1 1.02 1.28l.1.21.1.27.2.54.95 2.65a.5.5 0 0 0 .94 0l1.15-3.22v-.03l.1-.2v-.04a4 4 0 0 1 1.36-1.64l.1-.07 9.47-6.66.03-.02.02-.01.02-.02.02-.01.07-.05a.5.5 0 0 0 .03-.03l.03-.02a3.35 3.35 0 0 0 .47-.47l.03-.03.07-.1.01-.02.05-.07a3 3 0 0 0 .08-.12c0-.02.02-.03.02-.03v-.01l.02-.03.07-.12v-.02l.01-.02.01-.03h.01v-.02l.01-.01.03-.06.02-.05.01-.04.03-.06.03-.1a1.76 1.76 0 0 1 .02-.06v.02-.04l.02-.04.02-.06v-.05l.01-.02.02-.08v-.05l.01-.01v-.02a.95.95 0 0 0 .02-.07v-.11h.01v-.09c.01-.03.01-.05 0-.07H29v.05-.49l-.01-.01v-.05l-.05-.3-.01-.02-.06-.22-.01-.03L24.7 5.09a2.9 2.9 0 0 0-.07-.18V4.9l-.01-.02v-.02l-.02-.01-.03-.07v-.03a.49.49 0 0 0-.06-.09c0 .02.01.02.02.03v-.01l-.01-.01-.05-.1-.04-.06-.07-.12-.02-.02-.02-.03v-.01h-.01l-.07-.1-.01-.02-.02-.01-.07-.1-.03-.03-.02-.02-.11-.12-.02-.01-.15-.14-.02-.02c-.19-.16-.4-.3-.62-.41h-.01l-.03-.02-.17-.08h-.02l-.04-.02-.21-.08h-.04a3.25 3.25 0 0 0-.23-.07h-.02a3.23 3.23 0 0 0-.26-.05h-.04L21.97 3H8.28Zm16.27 1.74v-.02.02Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 35],
                            iconHtmlAnchor: [16, 35],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        C4: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="35px" viewBox="0 0 30 35"><path d="M24 2c.97 0 1.91.36 2.64 1l.14.13C27.55 3.88 28 4.93 28 6v18a4 4 0 0 1-4 4h-4.12a2 2 0 0 0-1.85 1.24l-2.1 5.14a1 1 0 0 1-1.8.13l-.06-.13-2.1-5.13A2 2 0 0 0 10.12 28H6a4 4 0 0 1-4-4V6a4 4 0 0 1 4-4h18Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M24 2c.97 0 1.91.36 2.64 1l.14.13A4 4 0 0 1 28 6v18a4 4 0 0 1-4 4h-4.12a2 2 0 0 0-1.85 1.24l-2.1 5.14a1 1 0 0 1-1.8.13l-.06-.13-2.1-5.13A2 2 0 0 0 10.12 28H6a4 4 0 0 1-4-4V6a4 4 0 0 1 4-4h18ZM6 3a3 3 0 0 0-3 3v18a3 3 0 0 0 3 3h4.12a3 3 0 0 1 2.77 1.87l1.64 4a.5.5 0 0 0 .93 0l1.65-4A3 3 0 0 1 19.88 27H24a3 3 0 0 0 3-3V6c0-.8-.33-1.58-.9-2.14l-.02-.01-.11-.1-.01-.02A3.01 3.01 0 0 0 24 3H6Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 35],
                            iconHtmlAnchor: [16, 35],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        C5: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="35px" viewBox="0 0 30 35"><path d="M13.12.71a2.85 2.85 0 0 1 3.76 0l.12.12 1.9 1.85a3 3 0 0 0 2.06.86l2.66.02.19.01.35.05.14.03.2.07.23.08h.01c.87.4 1.5 1.2 1.66 2.15l.03.24v.22l.04 2.63a3 3 0 0 0 .85 2.07l1.87 1.9a2.85 2.85 0 0 1 0 3.98l-1.87 1.9a3 3 0 0 0-.85 2.08l-.03 2.66a2.85 2.85 0 0 1-2.53 2.8l-.29.01-2.66.03a3 3 0 0 0-1.85.67l-.22.19-.19.18a3 3 0 0 0-.66.98l-.06.14-2.02 5.66-.02.04v.03l-.05.1a.57.57 0 0 1-.09.13 1 1 0 0 1-.7.4h-.01l-.11.01a1 1 0 0 1-.9-.61l-.01-.02v-.01l-2.05-5.71a3 3 0 0 0-.6-1.01l-.12-.12-.2-.2a3 3 0 0 0-1.78-.83l-.29-.02-2.65-.03a2.85 2.85 0 0 1-2.82-2.81L3.53 21c0-.78-.3-1.54-.86-2.1L.81 17l-.19-.22a2.85 2.85 0 0 1 .2-3.77l1.85-1.9a3 3 0 0 0 .86-2.07l.02-1.97v-.69a2.85 2.85 0 0 1 2.83-2.82l2.66-.02a3 3 0 0 0 2.06-.86L13 .83l.12-.12Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M13.12.71a2.85 2.85 0 0 1 3.76 0l.12.12 1.9 1.85a3 3 0 0 0 2.06.86l2.66.02.19.01c.11 0 .23.03.35.05l.14.03.2.06.23.1h.01c.87.38 1.5 1.18 1.66 2.14l.03.24v.22l.04 2.63a3 3 0 0 0 .85 2.07l1.86 1.9a2.85 2.85 0 0 1 0 3.98l-1.86 1.9a3 3 0 0 0-.85 2.08l-.03 2.65a2.85 2.85 0 0 1-2.53 2.8l-.29.02-2.66.03a3 3 0 0 0-1.85.66l-.22.2-.19.18a3 3 0 0 0-.66.98l-.06.14-2.02 5.66-.02.04v.03l-.05.1a.58.58 0 0 1-.07.1c0 .01 0 .02-.02.03a1 1 0 0 1-.7.4h-.01l-.11.01a1 1 0 0 1-.9-.61l-.01-.02v-.01l-2.05-5.71a3 3 0 0 0-.6-1.01l-.12-.12-.2-.2a3 3 0 0 0-1.78-.83l-.29-.02-2.65-.03a2.85 2.85 0 0 1-2.82-2.81L3.53 21c0-.78-.3-1.55-.86-2.1L.81 17l-.19-.22A2.85 2.85 0 0 1 .82 13l1.85-1.9a3 3 0 0 0 .86-2.07l.02-1.97v-.68a2.85 2.85 0 0 1 2.83-2.82l2.66-.02a3 3 0 0 0 2.06-.86L13 .83l.12-.12Zm2.95.64a1.85 1.85 0 0 0-2.16 0l-.13.11-.08.07-.01.01L11.8 3.4a4 4 0 0 1-2.75 1.14l-2.66.02c-.2 0-.38.04-.55.1H5.8c-.68.24-1.17.85-1.24 1.58v.15l-.01.66v.03l-.02 1.97a4 4 0 0 1-1.14 2.76l-1.86 1.9-.12.13a1.85 1.85 0 0 0 0 2.31l.13.16.02.02L3.4 18.2a4.02 4.02 0 0 1 1.14 2.79l.03 2.63v.18c.11.92.88 1.63 1.83 1.64l2.65.03h.02l.32.02h.05a4 4 0 0 1 2.25 1l.12.12.2.2.01.01.12.12.02.02a4 4 0 0 1 .81 1.35l1.57 4.38a.5.5 0 0 0 .94 0l1.57-4.4v-.01l.01-.02.06-.14v-.02a4 4 0 0 1 .89-1.3l.19-.18.02-.03h.02l.21-.2.02-.02h.01l.01-.01a4 4 0 0 1 2.47-.89l2.62-.03h.23l.03-.01c.9-.12 1.6-.88 1.6-1.81l.04-2.66a4 4 0 0 1 1.14-2.76l1.86-1.9c.7-.73.7-1.87 0-2.59l-1.86-1.9a4 4 0 0 1-1.14-2.76l-.03-2.61v-.15l-.01-.03-.01-.13-.01-.05-.04-.17v-.03l-.08-.19a1.86 1.86 0 0 0-.97-.97l-.13-.06h-.03l-.09-.03-.04-.01-.07-.02h-.03l-.16-.03h-.18l-2.65-.03a4 4 0 0 1-2.76-1.15L16.3 1.54h-.01l-.07-.08h-.01l-.14-.11Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 35],
                            iconHtmlAnchor: [16, 35],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        C6: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="35px" viewBox="0 0 30 35"><path d="M13.8.26a2.99 2.99 0 0 1 2.76.18 28.7 28.7 0 0 1 9.8 9.96l.24.4.25.45A26.7 26.7 0 0 1 30 21.91a3.06 3.06 0 0 1-1.67 2.97 28.6 28.6 0 0 1-8.88 2.8 2 2 0 0 0-1.6 1.31l-1.89 5.3-.02.07-.04.1a.63.63 0 0 1-.06.09l-.03.03.02-.02-.01.02-.03.03a1 1 0 0 1-.58.37l-.1.02h-.13a1 1 0 0 1-.85-.5l-.06-.14v-.01l-1.92-5.36a2 2 0 0 0-1.47-1.28l-.13-.02-.55-.09a28.49 28.49 0 0 1-7.74-2.43l-.59-.29a3.06 3.06 0 0 1-1.66-2.97A26.69 26.69 0 0 1 3.4 10.8 28.69 28.69 0 0 1 13.44.44l.18-.1.18-.08Zm2 34.33Zm0 0h.01v-.01Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M13.8.26a2.99 2.99 0 0 1 2.76.18 28.71 28.71 0 0 1 9.8 9.96l.24.4.24.45A26.7 26.7 0 0 1 30 21.91a3.06 3.06 0 0 1-1.66 2.97 28.6 28.6 0 0 1-8.88 2.8 2 2 0 0 0-1.6 1.31l-1.89 5.3-.02.07-.04.1a.63.63 0 0 1-.06.09l-.03.03.02-.02-.01.02-.03.03a1 1 0 0 1-.58.37l-.1.02h-.13a1 1 0 0 1-.85-.5l-.06-.14v-.01l-1.92-5.36a2 2 0 0 0-1.47-1.28l-.13-.02-.55-.09a28.5 28.5 0 0 1-7.74-2.43l-.59-.29a3.06 3.06 0 0 1-1.66-2.97A26.69 26.69 0 0 1 3.4 10.8 28.69 28.69 0 0 1 13.44.44l.18-.1.18-.08Zm2 34.33Zm0 0h.01v-.01Zm.24-33.3a1.99 1.99 0 0 0-1.82-.13l-.02.01-.1.05-.04.02-.1.05v.01A27.69 27.69 0 0 0 3.35 13.1 25.7 25.7 0 0 0 1 21.99c-.07.84.38 1.62 1.1 2l.58.28.45.2a27.45 27.45 0 0 0 7.02 2.15l.56.08.13.03h.04a3 3 0 0 1 2.2 1.93l1.44 4.03a.5.5 0 0 0 .94 0l1.44-4.03a3 3 0 0 1 2.4-1.96 27.6 27.6 0 0 0 8.57-2.71 2.06 2.06 0 0 0 1.11-2 25.7 25.7 0 0 0-3.03-10.27l-.23-.41-.23-.41-.44-.73a27.76 27.76 0 0 0-9.02-8.87Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 35],
                            iconHtmlAnchor: [16, 35],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        C7: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="35px" viewBox="0 0 30 35"><path d="M15 1a13.96 13.96 0 0 1 14 14 13.96 13.96 0 0 1-9.83 13.37 2 2 0 0 0-1.16.95l-.1.2-1.99 4.86a1 1 0 0 1-1.7.24l-.08-.1-.07-.14-2-4.86a2 2 0 0 0-1.04-1.07l-.2-.08-.53-.18-.5-.19A13.99 13.99 0 0 1 1 15 13.96 13.96 0 0 1 15 1Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M15 1a13.96 13.96 0 0 1 14 14 13.96 13.96 0 0 1-9.83 13.37 2 2 0 0 0-1.16.95l-.1.2-1.99 4.86a1 1 0 0 1-1.7.24l-.08-.1-.07-.14-1.99-4.86a2 2 0 0 0-1.05-1.07l-.2-.08-.53-.18-.5-.19A13.99 13.99 0 0 1 1 15 13.96 13.96 0 0 1 15 1Zm0 2a12 12 0 1 0 0 24 12 12 0 0 0 0-24Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 35],
                            iconHtmlAnchor: [16, 35],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        C8: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="35px" viewBox="0 0 30 35"><path d="M12.2 1.17a3.97 3.97 0 0 1 5.6 0L28.84 12.2a3.97 3.97 0 0 1 .16 5.44l-.2.2-10.36 10.37a3 3 0 0 0-.7 1.11l-1.77 4.95-.02.06-.01.03-.02.04-.02.06-.01.02-.02.02-.01.03-.07.08v.01l-.02.02a1 1 0 0 1-.67.36h-.12a1 1 0 0 1-.91-.63v-.02l-1.8-5.03a3 3 0 0 0-.6-1l-.11-.11-10.4-10.4a3.97 3.97 0 0 1 0-5.61l2.4-2.4.14-.14 8.5-8.5Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M12.2 1.17a3.97 3.97 0 0 1 5.6 0L28.84 12.2a3.97 3.97 0 0 1 .16 5.44l-.2.2-10.36 10.37a3 3 0 0 0-.7 1.1l-1.77 4.96-.02.06-.01.02v.01l-.02.04-.02.06-.01.02-.02.02-.01.03-.07.08v.01l-.02.02a1 1 0 0 1-.67.36h-.12a1 1 0 0 1-.91-.63v-.02l-1.81-5.03a3 3 0 0 0-.59-1l-.11-.11-10.4-10.4a3.97 3.97 0 0 1 0-5.61l2.4-2.4.14-.14 8.5-8.5Zm4.2 1.41a1.97 1.97 0 0 0-2.8 0L2.59 13.61a1.97 1.97 0 0 0 0 2.79L13.6 27.43c.77.77 2.01.77 2.78 0L27.42 16.4c.77-.77.77-2.02 0-2.79L16.4 2.58Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 35],
                            iconHtmlAnchor: [16, 35],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        C9: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="35px" viewBox="0 0 30 35"><path d="M21.86 2h.06l.12.01.18.02h.06l.33.06.02.01.31.08.04.01a4.22 4.22 0 0 1 .62.25l.02.01c.3.16.59.35.85.57l.19.18.17.17.06.08.1.12.05.06.11.15.04.05a3.93 3.93 0 0 1 .21.37l.05.1.07.14.04.09.09.23 4.15 12.36c.08.26.14.52.17.78l.01.14.01.14v.47l-.02.18-.02.13-.03.16-.03.14-.04.15-.04.14-.06.15-.05.14-.06.14-.06.13a4 4 0 0 1-.1.17c0 .03-.03.06-.04.09a4.08 4.08 0 0 1-.95 1.08c-.02 0-.03.02-.05.04l-.11.08-.04.03-.03.02-.01.01-9.6 6.74a3 3 0 0 0-1 1.23l-.1.22L16 34.18v.03l-.01.02-.04.1v.02l-.05.1a.55.55 0 0 1-.07.11s0 .02-.02.03a1 1 0 0 1-.7.4h-.12a1 1 0 0 1-.92-.64l-1.42-3.98-.2-.55a3 3 0 0 0-.91-1.3l-.17-.14L1.7 21.6a4.02 4.02 0 0 1-1.34-1.61c-.4-.89-.48-1.9-.16-2.87l4.13-12.3V4.8l.02-.04.01-.03.04-.11.02-.05a3.88 3.88 0 0 1 .37-.7l.05-.09a4 4 0 0 1 .1-.13L5 3.57l.08-.1.1-.1.05-.06A4.11 4.11 0 0 1 8.28 2h13.58Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M21.86 2h.06l.12.02.18.01.06.01.32.06h.03l.31.09h.04a4.22 4.22 0 0 1 .62.25l.02.02c.3.15.59.34.85.56l.19.18.17.18.06.07.1.12.05.07.11.14.04.06a3.93 3.93 0 0 1 .21.37l.05.09.07.15.04.08.09.24 4.15 12.36c.08.26.14.52.17.78l.01.14.01.13v.48l-.02.17-.02.13-.03.16-.03.14-.04.16-.04.13-.06.16-.05.13-.06.14-.06.14a4 4 0 0 1-.1.16c0 .03-.03.06-.04.09a4.15 4.15 0 0 1-.95 1.08l-.05.04-.11.09-.04.02-.03.02-.01.01-9.6 6.75a3 3 0 0 0-1 1.23l-.1.21L16 34.2v.02l-.01.02-.04.1v.03l-.05.1a.56.56 0 0 1-.07.1l-.02.03a1 1 0 0 1-.7.4l-.12.01a1 1 0 0 1-.92-.64l-1.42-3.98-.2-.56a3 3 0 0 0-.92-1.3l-.17-.13-9.66-6.8a4.02 4.02 0 0 1-1.34-1.61c-.4-.88-.48-1.9-.16-2.86L4.33 4.82V4.8l.02-.04.01-.04.04-.1.02-.06a3.9 3.9 0 0 1 .37-.7l.05-.08.1-.14.06-.08.08-.1.1-.1.05-.06A4.12 4.12 0 0 1 8.28 2h13.58ZM8.28 4c-.9 0-1.66.53-1.98 1.26l-.05.14L2.1 17.77c-.27.78.01 1.67.75 2.2l10.87 7.63c.76.54 1.8.54 2.56 0l10.86-7.64a1.93 1.93 0 0 0 .76-2.2L23.75 5.4A2.12 2.12 0 0 0 21.7 4H8.28Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 35],
                            iconHtmlAnchor: [16, 35],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        C10: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="35px" viewBox="0 0 30 35"><path d="M24 2c.97 0 1.91.36 2.64 1l.14.13C27.55 3.88 28 4.93 28 6v18a4 4 0 0 1-4 4h-4.12a2 2 0 0 0-1.85 1.24l-2.1 5.14a1 1 0 0 1-1.8.13l-.06-.13-2.1-5.13A2 2 0 0 0 10.12 28H6a4 4 0 0 1-4-4V6a4 4 0 0 1 4-4h18Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M24 2c.97 0 1.91.36 2.64 1l.14.13A4 4 0 0 1 28 6v18a4 4 0 0 1-4 4h-4.12a2 2 0 0 0-1.85 1.24l-2.1 5.14a1 1 0 0 1-1.8.13l-.06-.13-2.1-5.13A2 2 0 0 0 10.12 28H6a4 4 0 0 1-4-4V6a4 4 0 0 1 4-4h18ZM6 4a2 2 0 0 0-2 2v18c0 1.1.9 2 2 2h18a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 35],
                            iconHtmlAnchor: [16, 35],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        C11: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="35px" viewBox="0 0 30 35"><path d="M13.12.71a2.85 2.85 0 0 1 3.76 0l.12.12 1.9 1.85a3 3 0 0 0 2.06.86l2.66.02.19.01.35.05.14.03.2.07.23.08h.01c.87.4 1.5 1.2 1.66 2.15l.03.24v.22l.04 2.63a3 3 0 0 0 .85 2.07l1.87 1.9a2.85 2.85 0 0 1 0 3.98l-1.87 1.9a3 3 0 0 0-.85 2.08l-.03 2.66a2.85 2.85 0 0 1-2.53 2.8l-.29.01-2.66.03a3 3 0 0 0-1.85.67l-.22.19-.19.18a3 3 0 0 0-.66.98l-.06.14-2.02 5.66-.02.04v.03l-.05.1a.57.57 0 0 1-.09.13 1 1 0 0 1-.7.4h-.01l-.11.01a1 1 0 0 1-.9-.61l-.01-.02v-.01l-2.05-5.71a3 3 0 0 0-.6-1.01l-.12-.12-.2-.2a3 3 0 0 0-1.78-.83l-.29-.02-2.65-.03a2.85 2.85 0 0 1-2.82-2.81L3.53 21c0-.78-.3-1.54-.86-2.1L.81 17l-.19-.22a2.85 2.85 0 0 1 .2-3.77l1.85-1.9a3 3 0 0 0 .86-2.07l.02-1.97v-.69a2.85 2.85 0 0 1 2.83-2.82l2.66-.02a3 3 0 0 0 2.06-.86L13 .83l.12-.12Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M13.12.71a2.85 2.85 0 0 1 3.76 0l.12.12 1.9 1.85a3 3 0 0 0 2.06.86l2.66.02.19.01c.11 0 .23.03.35.05l.14.03.2.06.23.1h.01c.87.38 1.5 1.18 1.66 2.14l.03.24v.22l.04 2.63a3 3 0 0 0 .85 2.07l1.86 1.9a2.85 2.85 0 0 1 0 3.98l-1.86 1.9a3 3 0 0 0-.85 2.08l-.03 2.66a2.85 2.85 0 0 1-2.53 2.8l-.29.01-2.66.03a3 3 0 0 0-1.85.66l-.22.2-.19.18a3 3 0 0 0-.66.98l-.06.14-2.02 5.66-.02.04v.03l-.05.1a.57.57 0 0 1-.07.1c0 .01 0 .02-.02.03a1 1 0 0 1-.7.4h-.01l-.11.01a1 1 0 0 1-.9-.61l-.01-.02v-.01l-2.05-5.71a3 3 0 0 0-.6-1.01l-.12-.12-.2-.2a3 3 0 0 0-1.78-.83l-.29-.02-2.65-.03a2.85 2.85 0 0 1-2.82-2.81L3.53 21c0-.78-.3-1.54-.86-2.1L.81 17l-.19-.22A2.85 2.85 0 0 1 .82 13l1.85-1.9a3 3 0 0 0 .86-2.07l.02-1.97v-.68a2.85 2.85 0 0 1 2.83-2.82l2.66-.02a3 3 0 0 0 2.06-.86L13 .83l.12-.12Zm2.47 1.54a.85.85 0 0 0-1.18 0l-1.95 1.9a4.86 4.86 0 0 1-3.34 1.38l-2.72.03a.85.85 0 0 0-.84.84l-.03 2.72a4.85 4.85 0 0 1-1.38 3.34l-1.9 1.95a.85.85 0 0 0 0 1.19l1.9 1.94c.87.9 1.37 2.1 1.38 3.34l.03 2.72c0 .47.38.84.84.84l2.72.03c1.25.01 2.45.51 3.34 1.39l1.95 1.9c.33.32.85.32 1.18 0l1.95-1.9a4.83 4.83 0 0 1 3.34-1.39l2.72-.03c.46 0 .83-.37.84-.84l.03-2.72a4.8 4.8 0 0 1 1.38-3.34l1.9-1.94a.85.85 0 0 0 0-1.2l-1.9-1.94a4.85 4.85 0 0 1-1.38-3.34l-.03-2.72a.85.85 0 0 0-.84-.84l-2.72-.03a4.85 4.85 0 0 1-3.34-1.38l-1.95-1.9Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 35],
                            iconHtmlAnchor: [16, 35],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        C12: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="35px" viewBox="0 0 30 35"><path d="M13.8.26a2.99 2.99 0 0 1 2.76.18 28.7 28.7 0 0 1 9.8 9.96l.24.4.25.45A26.7 26.7 0 0 1 30 21.91a3.06 3.06 0 0 1-1.67 2.97 28.6 28.6 0 0 1-8.88 2.8 2 2 0 0 0-1.6 1.31l-1.89 5.3-.02.07-.04.1a.63.63 0 0 1-.06.09l-.03.03.02-.02-.01.02-.03.03a1 1 0 0 1-.58.37l-.1.02h-.13a1 1 0 0 1-.85-.5l-.06-.14v-.01l-1.92-5.36a2 2 0 0 0-1.47-1.28l-.13-.02-.55-.09a28.49 28.49 0 0 1-7.74-2.43l-.59-.29a3.06 3.06 0 0 1-1.66-2.97A26.69 26.69 0 0 1 3.4 10.8 28.69 28.69 0 0 1 13.44.44l.18-.1.18-.08Zm2 34.33Zm0 0h.01v-.01Z"></path><path fill="rgba(0,0,0,0.25)" fill-rule="evenodd" d="M13.8.26a2.99 2.99 0 0 1 2.76.18 28.71 28.71 0 0 1 9.8 9.96l.24.4.24.45A26.7 26.7 0 0 1 30 21.91a3.06 3.06 0 0 1-1.66 2.97 28.6 28.6 0 0 1-8.88 2.8 2 2 0 0 0-1.6 1.31l-1.89 5.3-.02.07-.04.1a.63.63 0 0 1-.06.09l-.03.03.02-.02-.01.02-.03.03a1 1 0 0 1-.58.37l-.1.02h-.13a1 1 0 0 1-.85-.5l-.06-.14v-.01l-1.92-5.36a2 2 0 0 0-1.47-1.28l-.13-.02-.55-.09a28.5 28.5 0 0 1-7.74-2.43l-.59-.29a3.06 3.06 0 0 1-1.66-2.97A26.69 26.69 0 0 1 3.4 10.8 28.69 28.69 0 0 1 13.44.44l.18-.1.18-.08Zm2 34.33Zm0 0h.01v-.01Zm-.28-32.44a.98.98 0 0 0-.96-.04l-.1.06C7.4 6.49 2.63 13.82 2 22.07c-.03.44.2.84.57 1.02A26.63 26.63 0 0 0 13 25.94h.02l.87.04h.01l.57.02h1.06l.56-.02.78-.04c3.87-.23 7.3-1.2 10.56-2.85.37-.18.6-.58.57-1.02-.64-8.27-5.43-15.6-12.48-19.92Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 35],
                            iconHtmlAnchor: [16, 35],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        D1: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="33px" viewBox="0 0 30 33" style="filter: drop-shadow(rgba(0, 0, 0, 0.32) 2px 2px 2px);"><path fill-rule="evenodd" d="M15 1a14 14 0 0 1 9.8 24l-6.17 6.44a4.99 4.99 0 0 1-7.26 0L5.2 25A14 14 0 0 1 15 1Z" clip-rule="evenodd"></path><path fill="white" fill-rule="evenodd" d="M15 1a14 14 0 0 1 9.8 24l-6.17 6.44a4.99 4.99 0 0 1-7.26 0L5.2 25A14 14 0 0 1 15 1Zm0 2a12 12 0 1 0 0 24 12 12 0 0 0 0-24Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 33],
                            iconHtmlAnchor: [16, 33],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        D2: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="33px" viewBox="0 0 30 33" style="filter: drop-shadow(rgba(0, 0, 0, 0.32) 2px 2px 2px);"><path d="M12.2 1.16a3.97 3.97 0 0 1 5.6 0L28.85 12.2a3.97 3.97 0 0 1 .22 5.37L18.38 31.15a4 4 0 0 1-6.76-.01L.96 17.59a3.97 3.97 0 0 1 .2-5.4L12.2 1.16Z"></path><path fill="white" fill-rule="evenodd" d="M12.2 1.16a3.97 3.97 0 0 1 5.6 0L28.85 12.2a3.97 3.97 0 0 1 .22 5.37L18.38 31.15a4 4 0 0 1-6.76-.01L.96 17.59a3.97 3.97 0 0 1 .2-5.4L12.2 1.16Zm4.2 1.42a1.97 1.97 0 0 0-2.8 0L2.59 13.6a1.97 1.97 0 0 0 0 2.78L13.6 27.42c.77.77 2.01.77 2.78 0L27.42 16.4c.77-.77.77-2.01 0-2.78L16.4 2.58Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 33],
                            iconHtmlAnchor: [16, 33],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        D3: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="33px" viewBox="0 0 30 33" style="filter: drop-shadow(rgba(0, 0, 0, 0.32) 2px 2px 2px);"><path d="M21.71 2c1.8 0 3.38 1.12 3.94 2.77l4.15 12.36a3.9 3.9 0 0 1-1.06 4.1L17.9 31.76a3.99 3.99 0 0 1-5.8 0L1.23 21.22a3.91 3.91 0 0 1-1.03-4.1L4.35 4.78A4.12 4.12 0 0 1 8.28 2h13.43Z"></path><path fill="white" fill-rule="evenodd" d="M21.71 2c1.8 0 3.38 1.12 3.94 2.76l4.15 12.36a3.94 3.94 0 0 1-1.06 4.12L17.9 31.75a3.99 3.99 0 0 1-5.8 0L1.23 21.21A3.91 3.91 0 0 1 .2 17.12L4.35 4.77A4.12 4.12 0 0 1 8.28 2h13.44ZM8.3 4c-.91 0-1.67.53-1.98 1.25l-.06.15L2.1 17.76c-.27.79.01 1.68.75 2.2l10.87 7.64c.76.53 1.8.53 2.56 0l10.87-7.64a1.93 1.93 0 0 0 .75-2.2L23.75 5.4A2.12 2.12 0 0 0 21.72 4H8.29Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 33],
                            iconHtmlAnchor: [16, 33],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        D4: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="33px" viewBox="0 0 30 33" style="filter: drop-shadow(rgba(0, 0, 0, 0.32) 2px 2px 2px);"><path d="M24 2a4 4 0 0 1 4 4v18a4 4 0 0 1-2 3.47l-8.25 4.7a4.98 4.98 0 0 1-5.5 0l-8.12-4.63-.23-.14A4 4 0 0 1 2 24V6a4 4 0 0 1 4-4h18Z"></path><path fill="white" fill-rule="evenodd" d="M24 2a4 4 0 0 1 4 4v18a4 4 0 0 1-2 3.47l-8.25 4.7a4.98 4.98 0 0 1-5.5 0l-8.12-4.63-.23-.14A4 4 0 0 1 2 24V6a4 4 0 0 1 4-4h18ZM6 4a2 2 0 0 0-2 2v18c0 1.1.9 2 2 2h18a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 33],
                            iconHtmlAnchor: [16, 33],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        D5: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="33px" viewBox="0 0 30 33" style="filter: drop-shadow(rgba(0, 0, 0, 0.32) 2px 2px 2px);"><path d="M13 .82a2.85 2.85 0 0 1 4 0l1.94 1.9c.52.51 1.23.8 1.96.81l2.72.03a2.85 2.85 0 0 1 2.82 2.82l.03 2.72c0 .74.3 1.44.81 1.96l1.9 1.95a2.85 2.85 0 0 1 0 3.99l-1.9 1.94c-.51.52-.8 1.23-.81 1.96l-.03 2.72a2.85 2.85 0 0 1-1.27 2.35l-7.64 6.13a3.98 3.98 0 0 1-5.06 0l-7.64-6.13a2.85 2.85 0 0 1-1.27-2.35l-.03-2.72c0-.73-.3-1.44-.81-1.96L.82 17a2.85 2.85 0 0 1 0-4l1.9-1.94c.51-.52.8-1.22.81-1.96l.03-2.72a2.85 2.85 0 0 1 2.82-2.82l2.72-.03c.73 0 1.44-.3 1.96-.81l1.95-1.9Z"></path><path fill="white" fill-rule="evenodd" d="M13 .81a2.85 2.85 0 0 1 4 0l1.94 1.9c.52.52 1.23.81 1.96.82l2.72.03a2.85 2.85 0 0 1 2.82 2.82l.03 2.72c0 .73.3 1.44.81 1.96l1.9 1.95a2.85 2.85 0 0 1 0 3.98l-1.9 1.95c-.51.52-.8 1.23-.81 1.96l-.03 2.72a2.85 2.85 0 0 1-1.27 2.34l-7.64 6.14a3.98 3.98 0 0 1-5.06 0l-7.64-6.13a2.85 2.85 0 0 1-1.27-2.35l-.03-2.72c0-.73-.3-1.44-.81-1.96l-1.9-1.95a2.85 2.85 0 0 1 0-3.98l1.9-1.95c.51-.52.8-1.23.81-1.96l.03-2.72a2.85 2.85 0 0 1 2.82-2.82l2.72-.03c.73 0 1.44-.3 1.96-.81l1.95-1.9Zm2.6 1.43a.85.85 0 0 0-1.2 0l-1.94 1.9a4.88 4.88 0 0 1-3.34 1.4l-2.72.02a.85.85 0 0 0-.84.84l-.03 2.72a4.85 4.85 0 0 1-1.38 3.34l-1.9 1.95a.85.85 0 0 0 0 1.18l1.9 1.95c.87.9 1.37 2.09 1.38 3.34l.03 2.72c0 .46.38.84.84.84l2.72.03a4.8 4.8 0 0 1 3.34 1.38l1.95 1.9c.33.33.85.33 1.18 0l1.95-1.9a4.86 4.86 0 0 1 3.34-1.38l2.72-.03c.46 0 .84-.38.84-.84l.03-2.72a4.8 4.8 0 0 1 1.38-3.34l1.9-1.95a.85.85 0 0 0 0-1.18l-1.9-1.95a4.85 4.85 0 0 1-1.38-3.34l-.03-2.72a.85.85 0 0 0-.84-.84l-2.72-.03a4.85 4.85 0 0 1-3.34-1.38l-1.95-1.9Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 33],
                            iconHtmlAnchor: [16, 33],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        D6: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="33px" viewBox="0 0 30 33" style="filter: drop-shadow(rgba(0, 0, 0, 0.32) 2px 2px 2px);"><path d="M13.44.44a3 3 0 0 1 2.94-.1l.18.1C24.12 5.06 29.3 12.96 29.99 21.9v.26l.01.03a3 3 0 0 1-1.37 2.52L17.26 32.3a3.98 3.98 0 0 1-4.52 0L1.37 24.72A3 3 0 0 1 0 22.2v-.3C.7 12.96 5.88 5.07 13.45.44Z"></path><path fill="white" fill-rule="evenodd" d="M13.44.44a3 3 0 0 1 2.94-.1l.18.1C24.12 5.06 29.3 12.96 29.99 21.9v.26l.01.03a3 3 0 0 1-1.37 2.52L17.26 32.3a3.98 3.98 0 0 1-4.52 0L1.37 24.72A3 3 0 0 1 0 22.2v-.3C.7 12.96 5.88 5.06 13.45.44Zm2.08 1.7a.98.98 0 0 0-.96-.03l-.1.05C7.4 6.48 2.64 13.81 2 22.06c-.03.44.2.85.57 1.03A26.63 26.63 0 0 0 13 25.93h.02l.87.05h.01l.57.02h1.06l.57-.02.77-.04c3.87-.23 7.3-1.21 10.56-2.85.37-.18.6-.59.57-1.03-.64-8.26-5.43-15.6-12.48-19.91Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 33],
                            iconHtmlAnchor: [16, 33],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        D7: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="33px" viewBox="0 0 30 33" style="filter: drop-shadow(rgba(0, 0, 0, 0.32) 2px 2px 2px);"><path fill-rule="evenodd" d="M15 1a14 14 0 0 1 9.8 24l-6.17 6.44a4.99 4.99 0 0 1-7.26 0L5.2 25A14 14 0 0 1 15 1Z" clip-rule="evenodd"></path></svg>`,
                            iconHtmlSize: [30, 33],
                            iconHtmlAnchor: [16, 33],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        D8: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="33px" viewBox="0 0 30 33" style="filter: drop-shadow(rgba(0, 0, 0, 0.32) 2px 2px 2px);"><path d="M12.2 1.16a3.97 3.97 0 0 1 5.6 0L28.85 12.2a3.97 3.97 0 0 1 .22 5.37L18.38 31.15a4 4 0 0 1-6.76-.01L.96 17.59a3.97 3.97 0 0 1 .2-5.4L12.2 1.16Z"></path></svg>`,
                            iconHtmlSize: [30, 33],
                            iconHtmlAnchor: [16, 33],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        D9: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="33px" viewBox="0 0 30 33" style="filter: drop-shadow(rgba(0, 0, 0, 0.32) 2px 2px 2px);"><path d="M21.71 2c1.8 0 3.38 1.12 3.94 2.77l4.15 12.36a3.9 3.9 0 0 1-1.06 4.1L17.9 31.76a3.99 3.99 0 0 1-5.8 0L1.23 21.22a3.91 3.91 0 0 1-1.03-4.1L4.35 4.78A4.12 4.12 0 0 1 8.28 2h13.43Z"></path></svg>`,
                            iconHtmlSize: [30, 33],
                            iconHtmlAnchor: [16, 33],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        D10: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="33px" viewBox="0 0 30 33" style="filter: drop-shadow(rgba(0, 0, 0, 0.32) 2px 2px 2px);"><path d="M24 2a4 4 0 0 1 4 4v18a4 4 0 0 1-2 3.47l-8.25 4.7a4.98 4.98 0 0 1-5.5 0l-8.12-4.63-.23-.14A4 4 0 0 1 2 24V6a4 4 0 0 1 4-4h18Z"></path></svg>`,
                            iconHtmlSize: [30, 33],
                            iconHtmlAnchor: [16, 33],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        D11: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="33px" viewBox="0 0 30 33" style="filter: drop-shadow(rgba(0, 0, 0, 0.32) 2px 2px 2px);"><path d="M13 .82a2.85 2.85 0 0 1 4 0l1.94 1.9c.52.51 1.23.8 1.96.81l2.72.03a2.85 2.85 0 0 1 2.82 2.82l.03 2.72c0 .74.3 1.44.81 1.96l1.9 1.95a2.85 2.85 0 0 1 0 3.99l-1.9 1.94c-.51.52-.8 1.23-.81 1.96l-.03 2.72a2.85 2.85 0 0 1-1.27 2.35l-7.64 6.13a3.98 3.98 0 0 1-5.06 0l-7.64-6.13a2.85 2.85 0 0 1-1.27-2.35l-.03-2.72c0-.73-.3-1.44-.81-1.96L.82 17a2.85 2.85 0 0 1 0-4l1.9-1.94c.51-.52.8-1.22.81-1.96l.03-2.72a2.85 2.85 0 0 1 2.82-2.82l2.72-.03c.73 0 1.44-.3 1.96-.81l1.95-1.9Z"></path></svg>`,
                            iconHtmlSize: [30, 33],
                            iconHtmlAnchor: [16, 33],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },
                        D12: {
                            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="33px" viewBox="0 0 30 33" style="filter: drop-shadow(rgba(0, 0, 0, 0.32) 2px 2px 2px);"><path d="M13.44.44a3 3 0 0 1 2.94-.1l.18.1C24.12 5.06 29.3 12.96 29.99 21.9v.26l.01.03a3 3 0 0 1-1.37 2.52L17.26 32.3a3.98 3.98 0 0 1-4.52 0L1.37 24.72A3 3 0 0 1 0 22.2v-.3C.7 12.96 5.88 5.07 13.45.44Z"></path></svg>`,
                            iconHtmlSize: [30, 33],
                            iconHtmlAnchor: [16, 33],
                            iconHtmlPopupAnchor: [0, -30],
                            backgroundHtml: "",
                            contentHtmlAnchor: [15, 15],
                        },


                    },
                    setIconExOptions: (elem) => {
                        if (Object.prototype.hasOwnProperty.call(elem, "iconExName") && ["C", "D"].includes(elem["iconExName"]))
                            return {}
                        return { contentColor: ["#a11", "#1a1", "#11a", "#aa1", "#1aa", "#a1a"][Math.floor(Math.random() * 6)] };
                    },
                    markerOptions: {
                        riseOnHover: true,';
                        if($dragabbleMarker){
                            $mapa .= 'draggable: true,';
                        }
                        $mapa .= '
                    },
                    setMarkerOptions: (elem) => {
                        return {
                            title: `${elem["content"]}`,
                            alt: `${elem["content"]}`,
                        }
                    },
                    defaultPopupContent: `<div>Simulate "Data Fetching"</div><div style="display: flex; align-items: center; justify-content: center; height: 6rem"><div class="leaflet-iconex-loader"></div></div>`,
                    fetchPopupContent: ('.$ID_marker.') => {
                        return new Promise((resolve, reject) => {
                            resolve(`<pre style="color:${'.$ID_marker.'.elem.iconFill}">${'.$ID_marker.'.elem.content}</pre>`);
                        });
                    },
                    onClick: (event) => {
                        console.log("marker clicked. id:", event.target.elem["id"]);
                    },
                }).addTo('.$ID_Map.');';

                if($dragabbleMarker){
                    //Separo los elementos
                    $items = $dragabbleFormItems ? explode(',', $dragabbleFormItems) : null;
                    $item1 = $items[0] ?? null;
                    $item2 = $items[1] ?? null;


                    $mapa .= '
                    Object.values('.$ID_multi.'._layers).forEach(function('.$ID_marker.'){
                        '.$ID_marker.'.dragging.enable();
                        '.$ID_marker.'.on("dragend", function(e){
                            var pos = e.target.getLatLng();
                            console.log(pos.lat, pos.lng);
                            document.getElementById("'.$item1.'").value = pos.lat;
                            document.getElementById("'.$item2.'").value = pos.lng;
                        });
                    });
                    ';
                }

            }

            /*******************************************/
            //Se agregan los polygon
            if(is_array($arrPolygon)){
                foreach ($arrPolygon as $poly) {
                    //Datos
                    $coordenadas = $poly[0];
                    $type        = $types[$poly[1]] ?? "";
                    $ID_polygon  = 'polygon_'.uniqid();
                    //Se agrega elemento
                    $mapa .= "const ".$ID_polygon." = L.polygon([";
                    //Se recorren las coordenadas
                    foreach ($coordenadas as $coord) {
                        $mapa .= '['.$coord[0].', '.$coord[1].'],';
                    }
                    $mapa .= "], {".$type."}).addTo(".$ID_Map.");";
                    //Popup
                    if(isset($poly[2])&&$poly[2]!=''){
                        $mapa .= $ID_polygon.".bindPopup('".$poly[2]."');";
                    }
                }
            }

            /*******************************************/
            //Se recorren los circle
            if(is_array($arrCircles)){
                foreach ($arrCircles as $item) {
                    // Obtener el tipo según el índice
                    $type = $types[$item[2]] ?? "";
                    //Id dinamico para los elementos
                    $ID_circle    = 'circle_'.uniqid();
                    //Se agrega elemento
                    $mapa .= "
                    const ".$ID_circle." = L.circle([".$item[0].", ".$item[1]."], {
                        ".$type."
                        radius: ".$item[3]."
                    }).addTo(".$ID_Map.");";
                    //Popup
                    if(isset($item[4])&&$item[4]!=''){
                        $mapa .= $ID_circle.".bindPopup('".$item[4]."');";
                    }
                }
            }

            /*******************************************/
            //Se agregan las polilineas
            if(is_array($arrPolyLine)){
                foreach ($arrPolyLine as $poly) {
                    //Datos
                    $coordenadas  = $poly[0];
                    $color        = $colors[$poly[1]] ?? "";
                    $ID_polyline  = 'polyline_'.uniqid();
                    //Se agrega elemento
                    $mapa .= "
                    var latlngs_".$ID_polyline." = [";
                    //Se recorren las coordenadas
                    foreach ($coordenadas as $coord) {
                        $mapa .= '['.$coord[0].', '.$coord[1].'],';
                    }
                    $mapa .= "];
                    var ".$ID_polyline." = L.polyline(latlngs_".$ID_polyline.", {".$color."}).addTo(".$ID_Map.");";
                }
            }

            /*******************************************/
            //Se agregan los rectangulos
            if(is_array($arrRectangle)){
                foreach ($arrRectangle as $poly) {
                    //Datos
                    $coordenadas  = $poly[0];
                    $color        = $colors[$poly[1]] ?? "";
                    $ID_rectangle  = 'rectangle_'.uniqid();
                    //Se agrega elemento
                    $mapa .= "
                    // define rectangle geographical bounds
                    var bounds_".$ID_rectangle." = [";
                    //Se recorren las coordenadas
                    foreach ($coordenadas as $coord) {
                        $mapa .= '['.$coord[0].', '.$coord[1].'],';
                    }
                    $mapa .= "];
                    // create an orange rectangle
                    var ".$ID_rectangle." = L.rectangle(bounds_".$ID_rectangle.", {".$color." weight: 1}).addTo(".$ID_Map.");";
                    //Popup
                    if(isset($poly[2])&&$poly[2]!=''){
                        $mapa .= $ID_rectangle.".bindPopup('".$poly[2]."');";
                    }
                }
            }

            /*******************************************/
            //Se agregan los marcadores
            if(is_array($arrHeatMap)){
                //Id dinamico para los elementos
                $ID_marker    = 'HeatMap_'.uniqid();
                //Se agrega elemento
                $mapa .= '
                var '.$ID_marker.' = L.heatLayer([';
                foreach ($arrHeatMap as $item) {
                    //Se agrega elemento
                    $mapa .= '['.$item[0].', '.$item[1].', '.$item[2].'],';
                }
                $mapa .= '], {radius: 25}).addTo('.$ID_Map.');';

            }

            /*******************************************/
            //Se agregan los eventos
            if($events){
                $mapa .= "
                // Evento click en mapa
                ".$ID_Map.".on('click', function(e){

                    var lat = e.latlng.lat;
                    var lng = e.latlng.lng;

                    L.marker([lat, lng])
                        .addTo(".$ID_Map.")
                        .bindPopup('Lat: ' + lat + '<br>Lng: ' + lng)
                        .openPopup();

                });
                ";
            }



        $mapa .= "
            //Se centra el mapa
            ".$ID_Map.".setView([".$Latitud.", ".$Longitud."], ".$Zoom.");
        </script>";

        /**********************/
        //devuelvo
        echo $mapa;

    }

    /************************************************************************************************************/
    public function leaFletMap_from_direccion($Options){
		/*
		*=================================================     Detalles    =================================================
		*
		* Despliega un mapa desde una ubicacion entregada, se genera un infowindow en el marcador centrado
		*
		*=================================================    Modo de uso  =================================================
		*
		*   //se imprime input
        *    $Options = [
        *        'ID_Map'         => 'map_1',            //ID del div donde se dibuja el html
        *        'Zoom'           => 13,                 //Zoom del mapa
        *        'attribution'    => '&copy; Test',      //Pie de pagina del mapa
        *        'arrDirecciones' => $arrDirecciones,    //array con las direcciones
        *    ];
		* 	$UIWidgetsMaps->leaFletMap_from_direccion($Options);
		*
		*=================================================    Parametros   =================================================
		* @input   array   $Options    array con los datos
		* @return  string
		*===================================================================================================================
		*/

        /**********************  Validaciones   **********************/
		if(!isset($Options['arrDirecciones']) || $Options['arrDirecciones']==''){     echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Ubicacion.');  exit;}

		/**********************  Definiciones   **********************/
        $arrMarkers  = [];
        $LatLong     = [];
        $fncLocation = new FunctionsLocation;

        //Se recorren los datos
        foreach ($Options['arrDirecciones'] as $item) {
            //Se verifican datos
            if(isset($item[0])&&$item[0]!=''){
                //Se limpian los nombres
                $Ubicacion  = str_replace(self::buscar, self::reemplazar, $item[0]);
                //Se verifica la existencia de los datos
                $iconExName  = $item[1] ?? 'A';
                $iconFill    = $item[2] ?? '#FF5722';
                $contentHtml = $item[3] ?? "<i class='bi bi-pin-angle text-primary'></i>";
                $content     = $item[4] ?? $Ubicacion;
                //Se hace la busqueda de lat y long por su direccion
                $result = $fncLocation->geocodeAddress($Ubicacion);
                //Si hay resultados se guarda
                if ($result) {
                    //Se guarda el dato
                    $arrMarkers[] = [$result['lat'], $result['lon'], $iconExName, $iconFill, $contentHtml, $content];
                    //Se guarda el ultimo dato
                    $LatLong['lat'] = $result['lat'];
                    $LatLong['lon'] = $result['lon'];
                }
            }
        }

		/********************** Si todo esta ok **********************/
        //Si hay datos
        if(!empty($arrMarkers)){
            //se imprime input
            $New_Options = [
                'Latitud'      => $LatLong['lat'],                //Latitud de la ubicacion
                'Longitud'     => $LatLong['lon'],                //Longitud de la ubicacion
                'ID_Map'       => $Options['ID_Map'] ?? '',       //ID del div donde se dibuja el html
                'Zoom'         => $Options['Zoom'] ?? 13,         //Zoom del mapa
                'attribution'  => $Options['attribution'] ?? '',  //Pie de pagina del mapa
                'arrMarkers'   => $arrMarkers,                    //array con los marcadores
            ];
            //Se manda a imprimir dato
            $this->leaFletMap_from_gps($New_Options);
        }else{
            echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'Las ubicaciones entregadas estan incorrectas o no existen.');
        }
    }

}
