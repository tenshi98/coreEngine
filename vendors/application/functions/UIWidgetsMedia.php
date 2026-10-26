<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class UIWidgetsMedia {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	//Definiciones
	private $Alertas;

	/******************************************************************************/
	/**
	 * Crea las instancias internas que la clase necesita: el proveedor de alertas de UIWidgetsCommon.
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $Media = new UIWidgetsMedia();
	 * ```
	 */
	public function __construct() {
		$this->Alertas = new UIWidgetsCommon();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
	 * Genera un reproductor de radio con MediaElement.js y una lista fija de emisoras.
	 *
	 * Incluye la hoja de estilo del player, el reproductor de audio, la playlist completa de
	 * emisoras (logo, nombre y stream) y el script que cambia la URL activa al hacer click
	 * en una emisora.
	 *
	 * @param string $BASE Ruta de la raíz del sitio.
	 *
	 * @return void Imprime el widget del reproductor de radio con su playlist de emisoras.
	 *
	 * @example
	 * ```php
	 * $Media = new UIWidgetsMedia();
	 * $Media->widget_mejs_radio_player('https://mi-sitio.cl'); // imprime el reproductor con la playlist de emisoras
	 * ```
	 */
	public function widget_mejs_radio_player($BASE){

		/********************** Si todo esta ok **********************/
		/****************************************/
		//radios
		$arr = array();
		$arr[] = array('https://redirector.dps.live/biobiosantiago/aac/icecast.audio',                                               '2939.v10.png',         'Radio Bio Bio');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/CORAZONAAC.aac?dist=onlineradiobox',       '2940.v12.png',         'Radio Corazón');
		$arr[] = array('https://mdstrm.com/audio/5c8d6406f98fbf269f57c82c/live.m3u8',                                                '3545.v8.png',          'Play FM');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/ADN.mp3?dist=onlineradiobox',              '334.v17.png',          'ADN Radio');
		$arr[] = array('https://redirector.dps.live/cooperativafm/mp3/icecast.audio',                                                '2990.v21.png',         'Radio Cooperativa');
		$arr[] = array('https://unlimited4-us.dps.live/p7concepcion/mp3/icecast.audio',                                              '62835.v15.png',        'Radio Punto 7 Concepción');
		$arr[] = array('https://sp.tvcontrolcp.com:10905/;',                                                                         '63733.v12.png',        'Radio Kpop Star');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/FUTURO_SC?dist=onlineradiobox',            '2895.v8.png',          'Radio Futuro');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/ROCK_AND_POPAAC.aac?dist=onlineradiobox',  '2920.v8.png',          'Rock & Pop');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/IMAGINA_SC?dist=onlineradiobox',           '322.v8.png',           'Radio Imagina');
		$arr[] = array('https://onlineradiobox.com/json/cl/paloma/play?platform=web',                                                '3434.v16.png',         'Radio Paloma');
		$arr[] = array('https://unlimited1-us.dps.live/carolinatv/carolinatv.smil/playlist.m3u8',                                    '358.v20.png',          'Radio Carolina');
		$arr[] = array('https://stream.edelweiss.fm/radio/8040/radio.mp3',                                                           '3266.v19.png',         'Radio Mirador');
		$arr[] = array('https://onlineradiobox.com/json/cl/delosrecuerdos/play?platform=web',                                        '63830.v9.png',         'FM de los Recuerdos');
		$arr[] = array('https://mdstrm.com/audio/5c915497c6fd7c085b29169d/live.m3u8',                                                '2943.v6.png',          'Radio Oasis');
		$arr[] = array('https://stream.edelweiss.fm/radio/8000/radio.mp3',                                                           '63821.v15.png',        'Radio Edelweiss');
		$arr[] = array('https://onlineradiobox.com/json/cl/carabineros/play?platform=web',                                           '75629.v8.png',         'Radio Carabineros');
		$arr[] = array('https://audio1.tustreaming.cl/9020/stream',                                                                  '3655.v7.png',          'Mi Radio');
		$arr[] = array('https://mdstrm.com/audio/5c915724519bce27671c4d15/icecast.audio?property=radiobox',                          '2988.v8.png',          'Sonar 105.3 FM');
		$arr[] = array('https://unlimited4-us.dps.live/romantica/aac/icecast.audio',                                                 '307.v19.png',          'Radio Romantica');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/ACTIVA.mp3?dist=onlineradiobox',           '3124.v11.png',         'RadioActiva');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/FMDOS_SC?dist=onlineradiobox',             '2938.v8.png',          'FM Dos');
		$arr[] = array('https://onlineradiobox.com/json/cl/carnavalantofagasta/play?platform=web',                                   '3070.v10.png',         'Radio Carnaval');
		$arr[] = array('https://unlimited4-us.dps.live/universo/aac/icecast.audio',                                                  '306.v7.png',           'Universo');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/PUDAHUEL.mp3?dist=onlineradiobox',         '309.v9.png',           'Radio Pudahuel');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/CONCIERTOAAC.aac?dist=onlineradiobox',     '2894.v5.png',          'Concierto 88.5 FM');
		$arr[] = array('https://radio.trix.hosting:18094/;',                                                                         '63045.v13.png',        'Retroclásicos Radio');
		$arr[] = array('https://unlimited4-us.dps.live/agricultura/gotardis/audio/now/livestream1.m3u8',                             '318.v12.png',          'Radio Agricultura');
		$arr[] = array('https://stream10.usastreams.com:10998/;',                                                                    '2898.v16.png',         'El Conquistador');
		$arr[] = array('https://unlimited4-us.dps.live/disney/mp364k/icecast.audio',                                                 '62400.v11.png',        'Radio Disney');
		$arr[] = array('https://mdstrm.com/audio/5c915613519bce27671c4caa/live.m3u8',                                                '63666.v9.png',         'Tele 13 Radio');
		$arr[] = array('https://stream.festival.cl/1',                                                                               '313.v13.png',          'Radio Festival');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/LOS40_CHILEAAC.aac?dist=onlineradiobox',   '2941.v13.png',         'Los 40');
		$arr[] = array('https://centova.neonetwork.cl:9154/stream',                                                                  '63848.v9.png',         'Radio Lola');
		$arr[] = array('https://unlimited4-us.dps.live/digitalfm/aac/icecast.audio',                                                 '329.v13.png',          'Digital FM');
		$arr[] = array('https://xradiopanel.com/8004/stream',                                                                        '63092.v10.png',        'Radio 80s');
		$arr[] = array('https://onlineradiobox.com/json/cl/estacion247/play?platform=web',                                           '73087.v11.png',        'Radio Estación 24/7');
		$arr[] = array('https://streaming.conectaapp.cl/fmplus',                                                                     '3085.v6.png',          'Radio Plus FM');
		$arr[] = array('https://onlineradiobox.com/json/cl/scuraexitos8090s/play?platform=web',                                      '63095.v14.png',        'Radioscura Éxitos 80/90&amp;#39;s');
		$arr[] = array('https://kpopreplay.radioca.st//stream',                                                                      '63655.v8.png',         'Kpop Replay');
		$arr[] = array('https://sonic.portalfoxmix.club:7157/;',                                                                     '80313.v24.png',        'Radio Raol Retro');
		$arr[] = array('https://unlimited11-cl.dps.live/infinita/aac/icecast.audio',                                                 '321.v9.png',           'Infinita Radio');
		$arr[] = array('https://unlimited3-cl.dps.live/beethovenfm/gotardis/audio/now/livestream1.m3u8',                             '332.v10.png',          'Beethoven');
		$arr[] = array('https://onlineradiobox.com/json/cl/araucana/play?platform=web',                                              '3293.v10.png',         'Radio Araucana');
		$arr[] = array('https://onlineradiobox.com/json/cl/ritoque/play?platform=web',                                               '3570.v6.png',          'Radio Ritoque');
		$arr[] = array('https://sonic.portalfoxmix.cl:7045/;',                                                                       '3401.v9.png',          'Picarona Panguipulli');
		$arr[] = array('https://vintage.ice.infomaniak.ch/vintage.mp3',                                                              '63368.v7.png',         'Radio Vintage');
		$arr[] = array('https://stream.zenolive.com/p0ar2tuq98quv',                                                                  '80442.v4.png',         'Radio K-pop Music');
		$arr[] = array('https://unlimited4-us.dps.live/nostalgica/aac/icecast.audio',                                                '3111.v9.png',          'Radio Nostalgica');
		$arr[] = array('https://audio1.tustreaming.cl:10973/stream',                                                                 '3147.v12.png',         'Radio Corporacion');
		$arr[] = array('https://aac.noot.live/laclavebb.aac',                                                                        '63522.v12.png',        'Radio La Clave');
		$arr[] = array('https://sonic.portalfoxmix.cl:7034/live',                                                                    '3553.v6.png',          'FM Dance');
		$arr[] = array('https://onlineradiobox.com/json/cl/maxima/play?platform=web',                                                '62964.v13.png',        'Radio Máxima');
		$arr[] = array('https://unlimited3-cl.dps.live/duna/gotardis/audio/now/livestream1.m3u8',                                    '328.v12.png',          'Duna');
		$arr[] = array('https://streamuchile.teslati.com/liveruch',                                                                  '3081.v11.png',         'Radio Universidad de Chile');
		$arr[] = array('https://unlimited1-us.dps.live/fmtiempotv/fmtiempotv.smil/playlist.m3u8',                                    '324.v8.png',           'FM Tiempo');
		$arr[] = array('https://onlineradiobox.com/json/cl/mirasol/play?platform=web',                                               '63863.v8.png',         'Radio Mirasol');
		$arr[] = array('https://audio4.tustreaming.cl/8160/stream',                                                                  '63010.v13.png',        'Viña del Mar Classic');
		$arr[] = array('https://sonic.portalfoxmix.cl/8226/stream',                                                                  '80534.v7.png',         'Recuerdos Retro');
		$arr[] = array('https://us9.maindigitalstream.com/ssl/7389',                                                                 '1840.v10.png',         'Radio Sol');
		$arr[] = array('https://broadcast.radio247.net/radio/8100/stream',                                                           '3012.v11.png',         'Desierto FM');
		$arr[] = array('https://onlineradiobox.com/json/cl/rtl/play?platform=web',                                                   '3432.v17.png',         'Radio RTL Curicó');
		$arr[] = array('https://unlimited11-cl.dps.live/elcarbon/aac/icecast.audio',                                                 '63826.v10.png',        'Radio El Carbon');
		$arr[] = array('https://mdstrm.com/audio/5de7fdb07e2fde0798203821/live.m3u8',                                                '63379.v26.png',        'Rockaxis');
		$arr[] = array('https://rusach.janus.cl/playlist/stream.m3u8',                                                               '3543.v15.png',         'Radio USACH');
		$arr[] = array('https://onlineradiobox.com/json/cl/nahuel/play?platform=web',                                                '3324.v9.png',          'Radio Nahuel');
		$arr[] = array('https://onlineradiobox.com/json/cl/vln/play?platform=web',                                                   '69682.v11.png',        'VLN Radio');
		$arr[] = array('https://archi-us.digitalproserver.com/osorno-fm.aac',                                                        '3322.v6.png',          'Radio Sago');
		$arr[] = array('https://unlimited4-us.dps.live/positiva/aac/icecast.audio',                                                  '68190.v15.png',        'Radio Positiva');
		$arr[] = array('https://onlineradiobox.com/json/cl/powerplaydiscotheque/play?platform=web',                                  '63328.v9.png',         'Power Play Discotheque');
		$arr[] = array('https://sonando-us.digitalproserver.com/ucvradio',                                                           '62979.v9.png',         'UCV Radio');
		$arr[] = array('https://sonic.portalfoxmix.cl:7026/stream',                                                                  '63196.v10.png',        'Radio Fiesta Mix');
		$arr[] = array('https://onlineradiobox.com/json/cl/lavozdelacosta/play?platform=web',                                        '63841.v9.png',         'Radio La Voz de la Costa');
		$arr[] = array('https://streaming.conectaapp.cl/fmquiero',                                                                   '71461.v9.png',         'FM Quiero');
		$arr[] = array('https://onlineradiobox.com/json/cl/libra/play?platform=web',                                                 '62980.v9.png',         'Radio Libra');
		$arr[] = array('https://onlineradiobox.com/json/cl/codigometal/play?platform=web',                                           '58095.v9.png',         'Código Metal Radio');
		$arr[] = array('https://archi-us.digitalproserver.com/austral.aac',                                                          '3406.v6.png',          'Radio Austral');
		$arr[] = array('https://streaming.conectaapp.cl/canal95',                                                                    '3008.v6.png',          'Radio Canal 95');
		$arr[] = array('https://onlineradiobox.com/json/cl/dulce/play?platform=web',                                                 '3564.v7.png',          'Radio Dulce');
		$arr[] = array('https://portales.tustreamings1.cl/stream',                                                                   '3552.v7.png',          'Radio Portales');
		$arr[] = array('https://radiostreaming.cloudserverlatam.com/8088/stream',                                                    '74515.v5.png',         'Radio Beat 98.7 FM');
		$arr[] = array('https://onlineradiobox.com/json/cl/punto9/play?platform=web',                                                '62871.v14.png',        'Radio Punto 9');
		$arr[] = array('https://onlineradiobox.com/json/cl/azukar1079/play?platform=web',                                            '74095.v3.png',         'Radio Azukar 107.9 FM');
		$arr[] = array('https://onlineradiobox.com/json/cl/caramelo/play?platform=web',                                              '3230.v15.png',         'Radio Caramelo-Malleco');
		$arr[] = array('https://sonic-us.streaming-chile.com:7037/;',                                                                '63866.v25.png',        'Dossil Radio Chile');
		$arr[] = array('https://onlineradiobox.com/json/cl/sinfoniaonline/play?platform=web',                                        '63067.v16.png',        'Radio Sinfonia Online');
		$arr[] = array('https://onlineradiobox.com/json/cl/lagosdelsur/play?platform=web',                                           '79342.v7.png',         'FM Lagos del Sur');
		$arr[] = array('https://stream.zeno.fm/cpvysp4m4ceuv',                                                                       '76736.v21.png',        'World Hits Radio (Radio Hits Chile)');
		$arr[] = array('https://archi-us.digitalproserver.com/definitiva.aac',                                                       '314.v7.png',           'Radio Definitiva');
		$arr[] = array('https://audio4.tustreaming.cl/8130/stream',                                                                  '3551.v13.png',         'Radio Santiago');
		$arr[] = array('https://onlineradiobox.com/json/cl/contemporanea/play?platform=web',                                         '62974.v9.png',         'Radio Contemporánea');
		$arr[] = array('https://onlineradiobox.com/json/cl/toromondo/play?platform=web',                                             '63060.v10.png',        'ToroMondo');
		$arr[] = array('https://unlimited3-cl.dps.live/radiopaula/gotardis/audio/now/livestream1.m3u8',                              '2991.v8.png',          'Paula FM');
		$arr[] = array('https://radiox.tustreamings5.cl/stream',                                                                     '63636.v12.png',        'Radio X FM');
		$arr[] = array('https://radio.tvstream.cl/8008/stream',                                                                      '68735.v34.png',        'Radio Zona Activa');
		$arr[] = array('https://onlineradiobox.com/json/cl/folclordechile/play?platform=web',                                        '63373.v8.png',         'Radio Folclor De Chile');
		$arr[] = array('https://radio.saopaulo01.com.br/8188/stream',                                                                '62832.v11.png',        '94.1 FM Patagonia Radio');
		$arr[] = array('https://onlineradiobox.com/json/cl/sanbartolome/play?platform=web',                                          '3249.v8.png',          'Radio San Bartolome');
		$arr[] = array('https://onlineradiobox.com/json/cl/classica1063/play?platform=web',                                          '3352.v10.png',         'Radio Classica');
		$arr[] = array('https://centova.neonetwork.cl:9172/stream',                                                                  '3354.v8.png',          'Radio Reloncavi');
		$arr[] = array('https://onlineradiobox.com/json/cl/chileno/play?platform=web',                                               '63413.v7.png',         'Rock Chileno');
		$arr[] = array('https://stream.zeno.fm/ktmru7k741zuv',                                                                       '75973.v9.png',         'Radio Modelo');
		$arr[] = array('https://stream.zeno.fm/c16qw0esehruv',                                                                       '82795.v10.png',        'Radio Retrocadas');
		$arr[] = array('https://onlineradiobox.com/json/cl/congreso/play?platform=web',                                              '62981.v9.png',         'Radio Congreso');
		$arr[] = array('https://cp.streamchileno.cl/radio/8040/radio.mp3',                                                           '3252.v19.png',         'Radio Riquelme');
		$arr[] = array('https://onlineradiobox.com/json/cl/supersol/play?platform=web',                                              '3656.v6.png',          'Radio SuperSol');
		$arr[] = array('https://audio.streaminghd.cl:2000/stream/RadioPulso',                                                        '80554.v20.png',        'Radio Pulso');
		$arr[] = array('https://sonic.portalfoxmix.cl:7012/;',                                                                       '3335.v9.png',          'Radio La Palabra');
		$arr[] = array('https://onlineradiobox.com/json/cl/magiztral/play?platform=web',                                             '63528.v11.png',        'Radio Magiztral');
		$arr[] = array('https://onlineradiobox.com/json/cl/gabrielaonline/play?platform=web',                                        '63349.v11.png',        'Radio Gabriela On Line');
		$arr[] = array('https://onlineradiobox.com/json/cl/galaxia/play?platform=web',                                               '63512.v7.png',         'Radio Galaxia');
		$arr[] = array('https://onlineradiobox.com/json/cl/fiessta/play?platform=web',                                               '3465.v8.png',          'Radio Fiessta');
		$arr[] = array('https://archi-us.digitalproserver.com/portales-fm-valparaiso-vina-del-mar.aac',                              '72051.v5.png',         'Radio Portales de Valparaiso');
		$arr[] = array('https://onlineradiobox.com/json/cl/macarena997/play?platform=web',                                           '320.v10.png',          'Macarena');
		$arr[] = array('https://onlineradiobox.com/json/cl/dimension/play?platform=web',                                             '70347.v14.png',        'Dimensión Primavera FM');
		$arr[] = array('https://archi-us.digitalproserver.com/santa-maria-am.aac',                                                   '3194.v6.png',          'Radio Santa Maria');
		$arr[] = array('https://onlineradiobox.com/json/cl/futura/play?platform=web',                                                '62773.v9.png',         'Futura 100.7 FM');
		$arr[] = array('https://audio3.tustreaming.cl:10964/caramelosvicente',                                                       '62926.v13.png',        'Radio Caramelo 104.5 FM');
		$arr[] = array('https://onlineradiobox.com/json/cl/pauta/play?platform=web',                                                 '75624.v8.png',         'Pauta FM');
		$arr[] = array('https://estilofm.tustreamings2.cl/stream',                                                                   '3417.v9.png',          'Estilo FM');
		$arr[] = array('https://onlineradiobox.com/json/cl/azul/play?platform=web',                                                  '3571.v7.png',          'Radio Azul');
		$arr[] = array('https://mdstrm.com/audio/5d013e4bc8a64d0da420ced6/live.m3u8',                                                '63579.v10.png',        'Súbela Radio');
		$arr[] = array('https://cp.streamchileno.cl/radio/8130/radio.mp3',                                                           '3251.v6.png',          'Radio Pinamar');


		//Hoja de estilo
		$input ='
		<link rel="stylesheet prefetch" href="'.$BASE.'/vendor/mejs-player/build/mediaelementplayer.css">
		<link rel="stylesheet prefetch" href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
		';

		//se crea widget
		$input .='
		<div id="main-wrapper">
			<div class="player-wrapper">
				<audio id="audio" class="mejs__player" controls="controls" src="">
					Your browser does not support the audio format.
				</audio>
				<ul class="playlist custom-counter" id="list">';
					foreach ($arr as $prod) {
						$input .='
						<li>
						<div class="track-info">
								<img class="station__title__logo" src="'.$BASE.'/vendor/mejs-player/emisoras/'.$prod[1].'" alt="'.$prod[2].'" title="'.$prod[2].'">
								<a href="#" data-value="'.$prod[0].'">'.$prod[2].'</a>
							</div>
						</li>';
					}
				$input .='
				</ul>
			</div>
		</div>';

		//script
		$input .='
		<script src="'.$BASE.'/vendor/mejs-player/build/mediaelement-and-player.js"></script>
		<script >
			// Dynamic URL change
			list.onclick = function(e) {
			e.preventDefault();

			var elm = e.target;
			var audio = document.getElementById("audio");

			var source = document.getElementById("audio");
			source.src = elm.getAttribute("data-value");

			audio.load(); //call this to just preload the audio without playing
			audio.play(); //call this to play the song right away
			};
		</script>
		<style>
			/* Radio Player */
			#main-wrapper{padding:0;}
			#main-wrapper .player-wrapper{border-radius: 5px;box-shadow: 0 0 8px -1px rgba(0, 0, 0, 0.25);background-image: -webkit-linear-gradient(315deg, #FF5572, #FF7555);background-image: linear-gradient(135deg, #FF5572, #FF7555);overflow: hidden;margin: 0 auto;max-width:100%;width: 100%;padding: 0;border-radius:0;}
			#main-wrapper .player-wrapper .playlist {margin:0;padding:15px 15px 0 15px;height: 400px;overflow-x: auto;color:#fff;}
			#main-wrapper .player-wrapper .playlist li{ overflow: hidden;line-height: 20px;display: flex;padding: 10px 0;border-bottom: 1px solid rgba(230, 211, 211, 0.31);}
			#main-wrapper .player-wrapper .playlist li .track-info {display: inline-block;position: relative;line-height: 1.3em;width: 100%;font-weight:500;}
			#main-wrapper .player-wrapper .playlist li .track-info img { margin-right: 10px;border-radius: 3px;width: 90px;}
			#main-wrapper .player-wrapper .playlist li .track-info a{color: #fff;text-decoration:none;}
			.mejs__controls{height:60px;}
			.mejs__button.mejs__playpause-button.mejs__replay,.mejs__button.mejs__playpause-button.mejs__pause{background: #FFB00E;width: 40px;padding: 0 5px;border-radius: 50%;}
			.mejs__button.mejs__playpause-button.mejs__replay{background: #29cf54;}
			.mejs__button.mejs__playpause-button.mejs__play {background: #29cf54;width: 40px;padding: 0 5px;border-radius: 50%;}
			.mejs__time {box-sizing: content-box;color: #444;font-size: 15px;font-weight: bold;height: 24px;overflow: hidden;width: 50px;padding:16px 0;}
			.mejs__button > button  {display: block;padding: 0;border: 0;font-family: FontAwesome;font-size: 20px;color: #444;background: transparent!important;}
			.mejs__button.mejs__playpause-button.mejs__play button:before {content: "\f04b";color:#fff;}
			.mejs__button.mejs__playpause-button.mejs__pause button:before {content: "\f04c";color:#fff;}
			.mejs__button.mejs__playpause-button.mejs__replay button:before {content: "\f01e";color:#fff;}
			.mejs__button.mejs__volume-button.mejs__mute button:before {content: "\f028";}
			.mejs__button.mejs__volume-button.mejs__unmute button:before {content: "\f026";}
			.mejs__container {font-family: Segui Ui,Arial,serif;background-size: cover;position: relative;background:#fff;text-align: left;text-indent: 0;vertical-align: top;height: 80px!important;width: 100%!important;}
			.mejs__controls:not([style*="display: none"]) {background: none;}
			.mejs__time-total {background: rgb(212, 245, 221);margin: 5px 0 0;width: 100%;}
			span.mejs__time-current {background: #dedede;}
			span.mejs__time-loaded {background: #29cf54;}
			.mejs__time-handle-content {border: 4px solid rgba(255, 255, 255, 0.9);border-radius: 0;height: 10px;left: -5px;top: -4px;-webkit-transform: scale(0);-ms-transform: scale(0);transform: scale(0);width: 1px;}
			.mejs__horizontal-volume-total {background: rgb(41, 207, 84);height: 10px;top:14px;border-radius:0;}
		</style>';

		/**********************/
		//Imprimir dato
		echo $input;

	}

	/******************************************************************************/
	/**
	 * Genera un reproductor de radio moderno (player propio con buscador o versión mini).
	 *
	 * $Type define el modo: 1 = reproductor completo con buscador, categorías y lista de
	 * emisoras; 2 = mini reproductor con selector desplegable. El catálogo de emisoras y los
	 * controles viven en vendor/radio_player/radio_player.js. Si $Type viene vacío o está
	 * fuera de catálogo imprime una alerta y detiene la ejecución (exit).
	 *
	 * @param string $BASE Ruta de la raíz del sitio.
	 * @param int    $Type Tipo de radio a mostrar (1 = completo, 2 = mini).
	 *
	 * @return void Imprime el widget del reproductor de radio.
	 *
	 * @example
	 * ```php
	 * $Media = new UIWidgetsMedia();
	 * $Media->widget_radio_player('https://mi-sitio.cl', 1); // imprime el reproductor completo con buscador
	 * ```
	 */
	public function widget_radio_player($BASE, $Type){

		/**********************  Validaciones   **********************/
		//se definen las opciones disponibles
		$tipos = array(1, 2);
		//Validaciones
		//if(!isset($BASE) || $BASE==''){              echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Ruta de la raiz del sitio.');                    exit;}
		if(!isset($Type) || $Type==''){              echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el tipo de configuracion.');                        exit;}
		if(isset($Type)&&!in_array($Type, $tipos)){  echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'La configuracion $type entregada no esta dentro de las opciones.'); exit;}

		/********************** Si todo esta ok **********************/
		$widget  = '
		<link type="text/css" rel="stylesheet" href="'.$BASE.'/vendor/radio_player/radio_player.css?get='.time().'"/>
		<div class="rp-wrap">
			<div class="card radio-card" style="border-radius:var(--rp-border-radius-lg) !important;background:var(--rp-color-background-primary)">
				<div class="card-body pb-2">
					<div class="d-flex align-items-center gap-3 mb-3">
						<div class="cover-box">
							<img id="mainCoverImg" alt="cover">
							<span id="mainCoverEmoji" style="font-size:28px">📻</span>
						</div>
						<div class="flex-grow-1 overflow-hidden">
							<div class="d-flex align-items-center gap-2 mb-1">
								<span class="live-dot" id="liveDot"></span>
								<span style="font-size:11px;color:var(--rp-color-text-secondary)" id="statusTxt">Sin reproducir</span>
							</div>
							<div style="font-size:16px;font-weight:500;color:var(--rp-color-text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis" id="mainName">Selecciona una emisora</div>
							<div style="font-size:12px;color:var(--rp-color-text-secondary)" id="mainGenre">—</div>
						</div>
						<span class="badge-band" id="mainBand">—</span>
					</div>
					<div class="d-flex align-items-center gap-2 mb-2">
						<button class="btn-ctrl" onclick="toggleTheme()" id="themeBtn" title="Cambiar tema">🌙</button>
						<button class="btn-ctrl" onclick="playPrev()" title="Anterior">&#9198;</button>
						<button class="btn-play-main" id="btnPlay" onclick="togglePlay()">&#9654;</button>
						<button class="btn-ctrl" onclick="playNext()" title="Siguiente">&#9197;</button>
						<button class="btn-ctrl" id="btnMute" onclick="toggleMute()">&#128266;</button>
						<input type="range" class="flex-grow-1" min="0" max="100" value="80" step="1" id="volSlider" oninput="setVol(this.value)" style="height:4px">
						<span style="font-size:11px;color:var(--rp-color-text-secondary);min-width:30px;text-align:right" id="volPct">80%</span>
					</div>';
					//Si es minireproductor
					if($Type==2){
						$widget  .= '
						<select class="station-select" id="stationSelect" onchange="loadStation(this.value)">
							<option value="">— Elige una emisora —</option>
						</select>';
					}
					$widget  .= '
				</div>
				<div class="stream-bar">
					<div class="stream-stat">Estado: <strong id="streamState">—</strong></div>
					<div class="stream-divider"></div>
					<div class="stream-stat">Bitrate: <strong id="streamBitrate">—</strong></div>
					<div class="stream-divider"></div>
					<div class="stream-stat">Tipo: <strong id="streamType">—</strong></div>
				</div>';
				//Si no es minireproductor
				if($Type==1){
					$widget  .= '
					<div style="border-top:0.5px solid var(--rp-color-border-tertiary);padding:8px 12px">
						<input type="text" class="form-control form-control-sm" id="srch" placeholder="Buscar emisora..." oninput="renderList()" style="background:var(--rp-color-background-secondary);color:var(--rp-color-text-primary);border:0.5px solid var(--rp-color-border-tertiary);font-size:13px">
					</div>
					<div class="cat-pills" id="catPills"></div>
					<div class="list-scroll" id="stList"></div>';
				}
				$widget  .= '
			</div>
		</div>
		<script>
			//Ubicacion de las caratulas
			let rp_covers = "'.$BASE.'/vendor/mejs-player/emisoras/";
			let rp_Type   = '.$Type.';
		</script>
		<script type="text/javascript" src="'.$BASE.'/vendor/radio_player/radio_player.js?get='.time().'"></script>';

		/**********************/
		//Imprimir dato
		echo $widget;

	}


}
