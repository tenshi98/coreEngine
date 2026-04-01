const STATIONS = [
  {id:1,  name:"Los 40 Chile",               emoji:"🎵", band:"FM 101.7", genre:"Pop",        bitrate:128, codec:"MP3", logo:rp_covers+"2941.v13.png",  url:"https://playerservices.streamtheworld.com/api/livestream-redirect/LOS40_CHILEAAC.aac?dist=onlineradiobox"},
  {id:2,  name:"Radio Corazón",              emoji:"❤️", band:"FM 101.3", genre:"Romántica",  bitrate:128, codec:"MP3", logo:rp_covers+"2940.v12.png",  url:"https://playerservices.streamtheworld.com/api/livestream-redirect/CORAZONAAC.aac?dist=onlineradiobox"},
  {id:3,  name:"Pudahuel FM",                emoji:"🎶", band:"FM 90.5",  genre:"Pop",        bitrate:128, codec:"AAC", logo:rp_covers+"309.v9.png",    url:"https://playerservices.streamtheworld.com/api/livestream-redirect/PUDAHUEL.mp3?dist=onlineradiobox"},
  {id:4,  name:"Radio Bío Bío",              emoji:"📡", band:"FM 99.1",  genre:"Noticias",   bitrate:128, codec:"AAC", logo:rp_covers+"2939.v10.png",  url:"https://redirector.dps.live/biobiosantiago/aac/icecast.audio"},
  {id:5,  name:"Rock & Pop",                 emoji:"🎸", band:"FM 94.1",  genre:"Rock",       bitrate:128, codec:"MP3", logo:rp_covers+"2920.v8.png",   url:"https://playerservices.streamtheworld.com/api/livestream-redirect/ROCK_AND_POPAAC.aac?dist=onlineradiobox"},
  {id:6,  name:"Radio Concierto",            emoji:"🎹", band:"FM 88.5",  genre:"Rock",       bitrate:128, codec:"MP3", logo:rp_covers+"2894.v5.png",   url:"https://playerservices.streamtheworld.com/api/livestream-redirect/CONCIERTOAAC.aac?dist=onlineradiobox"},
  {id:7,  name:"ADN Radio Chile",            emoji:"🧬", band:"FM 91.7",  genre:"Noticias",   bitrate:128, codec:"MP3", logo:rp_covers+"334.v17.png",   url:"https://playerservices.streamtheworld.com/api/livestream-redirect/ADN.mp3?dist=onlineradiobox"},
  {id:8,  name:"Radio Cooperativa",          emoji:"⭕", band:"FM 97.7",  genre:"Noticias",   bitrate:128, codec:"AAC", logo:rp_covers+"2990.v21.png",  url:"https://redirector.dps.live/cooperativafm/mp3/icecast.audio"},
  {id:9,  name:"Duna FM",                    emoji:"🏜️", band:"FM 89.7",  genre:"Adulto",     bitrate:128, codec:"MP3", logo:rp_covers+"328.v12.png",   url:"https://unlimited3-cl.dps.live/duna/gotardis/audio/now/livestream1.m3u8"},
  {id:10, name:"Radio Futuro",               emoji:"🚀", band:"FM 88.9",  genre:"Pop",        bitrate:128, codec:"AAC", logo:rp_covers+"2895.v8.png",   url:"https://playerservices.streamtheworld.com/api/livestream-redirect/FUTURO_SC?dist=onlineradiobox"},
  {id:11, name:"Carolina FM",                emoji:"🌺", band:"FM 99.3",  genre:"Pop",        bitrate:128, codec:"MP3", logo:rp_covers+"358.v20.png",   url:"https://unlimited1-us.dps.live/carolinatv/carolinatv.smil/playlist.m3u8"},
  {id:12, name:"Sonar FM",                   emoji:"📻", band:"FM 90.9",  genre:"Pop",        bitrate:128, codec:"MP3", logo:rp_covers+"2988.v8.png",   url:"https://mdstrm.com/audio/5c915724519bce27671c4d15/icecast.audio?property=radiobox"},
  {id:13, name:"Radio Agricultura",          emoji:"🌾", band:"AM 540",   genre:"Noticias",   bitrate:64,  codec:"MP3", logo:rp_covers+"318.v12.png",   url:"https://unlimited4-us.dps.live/agricultura/gotardis/audio/now/livestream1.m3u8"},
  {id:14, name:"Radio Universo",             emoji:"🌌", band:"FM 93.3",  genre:"Urbano",     bitrate:128, codec:"AAC", logo:rp_covers+"306.v7.png",    url:"https://unlimited4-us.dps.live/universo/aac/icecast.audio"},
  {id:15, name:"El Conquistador",            emoji:"🏆", band:"FM 91.3",  genre:"Tropical",   bitrate:128, codec:"MP3", logo:rp_covers+"2898.v16.png",  url:"https://stream10.usastreams.com:10998/"},
  {id:16, name:"Play FM",                    emoji:"▶️", band:"FM 95.3",  genre:"Pop",        bitrate:128, codec:"AAC", logo:rp_covers+"3545.v8.png",   url:"https://mdstrm.com/audio/5c8d6406f98fbf269f57c82c/live.m3u8"},
  {id:17, name:"Radio Activa",               emoji:"⚡",  band:"FM 92.5",  genre:"Rock",       bitrate:128, codec:"MP3", logo:rp_covers+"3124.v11.png",  url:"https://playerservices.streamtheworld.com/api/livestream-redirect/ACTIVA.mp3?dist=onlineradiobox"},
  {id:18, name:"Beethoven FM",               emoji:"🎼", band:"FM 96.5",  genre:"Clásica",    bitrate:128, codec:"MP3", logo:rp_covers+"332.v10.png",   url:"https://unlimited3-cl.dps.live/beethovenfm/gotardis/audio/now/livestream1.m3u8"},
  {id:19, name:"Tele 13 Radio",              emoji:"📺", band:"FM 89.9",  genre:"Noticias",   bitrate:128, codec:"MP3", logo:rp_covers+"63666.v9.png",  url:"https://mdstrm.com/audio/5c915613519bce27671c4caa/live.m3u8"},
  {id:20, name:"Radio Pauta",                emoji:"📊", band:"FM 100.5", genre:"Noticias",   bitrate:128, codec:"MP3", logo:rp_covers+"75624.v8.png",  url:"https://onlineradiobox.com/json/cl/pauta/play?platform=web"},
  {id:21, name:"FM Dos",                     emoji:"2️⃣", band:"FM 98.9",  genre:"Adulto",     bitrate:128, codec:"MP3", logo:rp_covers+"2938.v8.png",   url:"https://playerservices.streamtheworld.com/api/livestream-redirect/FMDOS_SC?dist=onlineradiobox"},
  {id:22, name:"Radio Punto 7 Concepción",   emoji:"🌊", band:"FM 92.1",  genre:"Urbano",     bitrate:128, codec:"AAC", logo:rp_covers+"62835.v15.png", url:"https://unlimited4-us.dps.live/p7concepcion/mp3/icecast.audio"},
  {id:23, name:"La Clave",                   emoji:"🔑", band:"FM 97.1",  genre:"Adulto",     bitrate:128, codec:"MP3", logo:rp_covers+"63522.v12.png", url:"https://aac.noot.live/laclavebb.aac"},
  {id:24, name:"Imagina FM",                 emoji:"✨", band:"FM 88.1",  genre:"Adulto",     bitrate:128, codec:"MP3", logo:rp_covers+"322.v8.png",    url:"https://playerservices.streamtheworld.com/api/livestream-redirect/IMAGINA_SC?dist=onlineradiobox"},
  {id:25, name:"Radio Kpop Star",            emoji:"🧊", band:"FM 100.1", genre:"Pop",        bitrate:128, codec:"AAC", logo:rp_covers+"63733.v12.png", url:"https://sp.tvcontrolcp.com:10905/"},
];


/*
$arr[] = array('https://onlineradiobox.com/json/cl/paloma/play?platform=web',                                                '3434.v16.png',         'Radio Paloma');
$arr[] = array('https://stream.edelweiss.fm/radio/8040/radio.mp3',                                                           '3266.v19.png',         'Radio Mirador');
$arr[] = array('https://onlineradiobox.com/json/cl/delosrecuerdos/play?platform=web',                                        '63830.v9.png',         'FM de los Recuerdos');
$arr[] = array('https://mdstrm.com/audio/5c915497c6fd7c085b29169d/live.m3u8',                                                '2943.v6.png',          'Radio Oasis');
$arr[] = array('https://stream.edelweiss.fm/radio/8000/radio.mp3',                                                           '63821.v15.png',        'Radio Edelweiss');
$arr[] = array('https://onlineradiobox.com/json/cl/carabineros/play?platform=web',                                           '75629.v8.png',         'Radio Carabineros');
$arr[] = array('https://audio1.tustreaming.cl/9020/stream',                                                                  '3655.v7.png',          'Mi Radio');
$arr[] = array('https://unlimited4-us.dps.live/romantica/aac/icecast.audio',                                                 '307.v19.png',          'Radio Romantica');
$arr[] = array('https://onlineradiobox.com/json/cl/carnavalantofagasta/play?platform=web',                                   '3070.v10.png',         'Radio Carnaval');
$arr[] = array('https://radio.trix.hosting:18094/;',                                                                         '63045.v13.png',        'Retroclásicos Radio');
$arr[] = array('https://unlimited4-us.dps.live/disney/mp364k/icecast.audio',                                                 '62400.v11.png',        'Radio Disney');
$arr[] = array('https://stream.festival.cl/1',                                                                               '313.v13.png',          'Radio Festival');
$arr[] = array('https://centova.neonetwork.cl:9154/stream',                                                                  '63848.v9.png',         'Radio Lola');
$arr[] = array('https://unlimited4-us.dps.live/digitalfm/aac/icecast.audio',                                                 '329.v13.png',          'Digital FM');
$arr[] = array('https://xradiopanel.com/8004/stream',                                                                        '63092.v10.png',        'Radio 80s');
$arr[] = array('https://onlineradiobox.com/json/cl/estacion247/play?platform=web',                                           '73087.v11.png',        'Radio Estación 24/7');
$arr[] = array('https://streaming.conectaapp.cl/fmplus',                                                                     '3085.v6.png',          'Radio Plus FM');
$arr[] = array('https://onlineradiobox.com/json/cl/scuraexitos8090s/play?platform=web',                                      '63095.v14.png',        'Radioscura Éxitos 80/90&amp;#39;s');
$arr[] = array('https://kpopreplay.radioca.st//stream',                                                                      '63655.v8.png',         'Kpop Replay');
$arr[] = array('https://sonic.portalfoxmix.club:7157/;',                                                                     '80313.v24.png',        'Radio Raol Retro');
$arr[] = array('https://unlimited11-cl.dps.live/infinita/aac/icecast.audio',                                                 '321.v9.png',           'Infinita Radio');
$arr[] = array('',                             '',          'Beethoven');
$arr[] = array('https://onlineradiobox.com/json/cl/araucana/play?platform=web',                                              '3293.v10.png',         'Radio Araucana');
$arr[] = array('https://onlineradiobox.com/json/cl/ritoque/play?platform=web',                                               '3570.v6.png',          'Radio Ritoque');
$arr[] = array('https://sonic.portalfoxmix.cl:7045/;',                                                                       '3401.v9.png',          'Picarona Panguipulli');
$arr[] = array('https://vintage.ice.infomaniak.ch/vintage.mp3',                                                              '63368.v7.png',         'Radio Vintage');
$arr[] = array('https://stream.zenolive.com/p0ar2tuq98quv',                                                                  '80442.v4.png',         'Radio K-pop Music');
$arr[] = array('https://unlimited4-us.dps.live/nostalgica/aac/icecast.audio',                                                '3111.v9.png',          'Radio Nostalgica');
$arr[] = array('https://audio1.tustreaming.cl:10973/stream',                                                                 '3147.v12.png',         'Radio Corporacion');
$arr[] = array('',                                                                        '',        'Radio La Clave');
$arr[] = array('https://sonic.portalfoxmix.cl:7034/live',                                                                    '3553.v6.png',          'FM Dance');
$arr[] = array('https://onlineradiobox.com/json/cl/maxima/play?platform=web',                                                '62964.v13.png',        'Radio Máxima');
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
$arr[] = array('',                                                 '',         'Pauta FM');
$arr[] = array('https://estilofm.tustreamings2.cl/stream',                                                                   '3417.v9.png',          'Estilo FM');
$arr[] = array('https://onlineradiobox.com/json/cl/azul/play?platform=web',                                                  '3571.v7.png',          'Radio Azul');
$arr[] = array('https://mdstrm.com/audio/5d013e4bc8a64d0da420ced6/live.m3u8',                                                '63579.v10.png',        'Súbela Radio');
$arr[] = array('https://cp.streamchileno.cl/radio/8130/radio.mp3',                                                           '3251.v6.png',          'Radio Pinamar');
*/


let cur=-1, playing=false, muted=false, vol=80, activeCat='Todas';
const audio = new Audio();
audio.volume = 0.8;

audio.onplaying = () => { playing=true; setStatus(true,'En vivo'); document.getElementById('btnPlay').innerHTML='&#9646;&#9646;'; updateStreamBar('Conectado'); };
audio.onwaiting = () => { setStatus(false,'Cargando...'); updateStreamBar('Bufferizando'); };
audio.onerror   = () => { setStatus(false,'Error de stream'); updateStreamBar('Error'); };
audio.onpause   = () => { document.getElementById('btnPlay').innerHTML='&#9654;'; setStatus(false,'Pausado'); updateStreamBar('Pausado'); };
audio.onstalled = () => updateStreamBar('Interrumpido');

function updateStreamBar(state) {
  document.getElementById('streamState').textContent = state;
  if (cur >= 0) {
    document.getElementById('streamBitrate').textContent = STATIONS[cur].bitrate + ' kbps';
    document.getElementById('streamType').textContent = STATIONS[cur].codec;
  } else {
    document.getElementById('streamBitrate').textContent = '—';
    document.getElementById('streamType').textContent = '—';
  }
}

function setCover(s, imgId, emojiId) {
  const img = document.getElementById(imgId), em = document.getElementById(emojiId);
  if (s.logo) {
    img.src                  = s.logo;
    img.style.display        = 'block';
    if (em) em.style.display = 'none';
    img.onerror = () => { img.style.display = 'none'; if (em) em.style.display = ''; };
  } else {
    img.style.display = 'none';
    if (em) { em.style.display = ''; em.textContent = s.emoji; }
  }
}

function loadStation(idx, autoplay=true) {
  if (idx < 0 || idx >= STATIONS.length) return;
  cur = idx;
  const s = STATIONS[idx];
  document.getElementById('mainName').textContent = s.name;
  document.getElementById('mainGenre').textContent = s.genre;
  document.getElementById('mainBand').textContent = s.band;
  setCover(s, 'mainCoverImg', 'mainCoverEmoji');
  setStatus(false, 'Cargando...');
  updateStreamBar('Conectando...');
  try { localStorage.setItem('lr', idx); } catch(e) {}
  audio.pause();
  audio.src = s.url;
  if(rp_Type==1){
    renderList();
  }
  if (autoplay) { audio.load(); audio.play().then(() => { playing=true; }).catch(() => setStatus(false,'Error')); }
}

function togglePlay() { if (cur<0) { loadStation(0); return; } if (playing) { audio.pause(); playing=false; } else { audio.play().catch(()=>{}); playing=true; } }
function playPrev() { loadStation(cur<=0 ? STATIONS.length-1 : cur-1); }
function playNext() { loadStation(cur>=STATIONS.length-1 ? 0 : cur+1); }
function setVol(v) { vol=parseInt(v); audio.volume=muted?0:vol/100; document.getElementById('volPct').textContent=vol+'%'; }
function toggleMute() { muted=!muted; audio.volume=muted?0:vol/100; document.getElementById('btnMute').innerHTML=muted?'&#128263;':'&#128266;'; document.getElementById('btnMute').classList.toggle('active',muted); }
function setStatus(live, txt) { document.getElementById('liveDot').className='live-dot'+(live?' on':''); document.getElementById('statusTxt').textContent=txt; }

function buildCatPills() {
  const cats = ['Todas', ...new Set(STATIONS.map(s => s.genre))];
  document.getElementById('catPills').innerHTML = cats.map(c =>
    `<button class="cat-pill${c===activeCat?' active':''}" onclick="setCat('${c}')">${c}</button>`
  ).join('');
}

function setCat(cat) { activeCat=cat; buildCatPills(); renderList(); }

function renderList() {
  const q = (document.getElementById('srch').value||'').toLowerCase();
  const el = document.getElementById('stList');
  const filtered = STATIONS.filter(s => {
    const matchCat = activeCat==='Todas' || s.genre===activeCat;
    const matchQ = !q || s.name.toLowerCase().includes(q) || s.genre.toLowerCase().includes(q) || s.band.toLowerCase().includes(q);
    return matchCat && matchQ;
  });
  if (!filtered.length) { el.innerHTML='<div style="padding:16px;text-align:center;font-size:13px;color:var(--color-text-secondary)">Sin resultados</div>'; return; }
  el.innerHTML = filtered.map(s => {
    const i = STATIONS.indexOf(s);
    const imgPart = s.logo
      ? `<img src="${s.logo}" alt="" onerror="this.style.display='none';this.nextSibling.style.display=''" style="width:100%;height:100%;object-fit:cover;border-radius:5px"><span style="display:none;font-size:16px">${s.emoji}</span>`
      : `<span style="font-size:16px">${s.emoji}</span>`;
    return `<div class="station-item d-flex align-items-center gap-2 px-3 py-2${i===cur?' active':''}" style="border-bottom:0.5px solid var(--color-border-tertiary)" onclick="loadStation(${i})">
      <span class="live-dot${i===cur&&playing?' on':''}"></span>
      <div class="station-cover">${imgPart}</div>
      <div class="flex-grow-1 overflow-hidden">
        <div style="font-size:13px;font-weight:500;color:var(--color-text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${s.name}</div>
        <div style="font-size:11px;color:var(--color-text-secondary)">${s.genre} · ${s.bitrate} kbps</div>
      </div>
      <span class="badge-band">${s.band}</span>
    </div>`;
  }).join('');
}

try { const l = localStorage.getItem('lr'); if (l !== null) loadStation(parseInt(l), false); } catch(e) {}

switch (rp_Type) {
    case 1:
        buildCatPills();
        renderList();
        break;
    case 2:
        const sel = document.getElementById('stationSelect');
        STATIONS.forEach((s, i) => {
            const opt = document.createElement('option');
            opt.value = i;
            opt.textContent = s.emoji + '  ' + s.name + ' — ' + s.band;
            sel.appendChild(opt);
        });
        break;
}

function toggleTheme() {
	const root = document.documentElement;
	const isDark = root.classList.toggle('dark');

	// Guardar preferencia
	localStorage.setItem('theme', isDark ? 'dark' : 'light');

	updateThemeIcon(isDark);
}

function updateThemeIcon(isDark) {
	document.getElementById('themeBtn').textContent = isDark ? '☀️' : '🌙';
}

// Cargar preferencia al iniciar
(function initTheme() {
	const saved = localStorage.getItem('theme');

	if (saved === 'dark') {
		document.documentElement.classList.add('dark');
		updateThemeIcon(true);
	} else {
		updateThemeIcon(false);
	}
})();
