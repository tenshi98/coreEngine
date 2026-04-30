<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<h5 class="box-title text-color-red-dark">
    <div class="d-grid gap-2 d-md-flex justify-content-md-between">
        Leaflet Maps
        <a href="https://leafletjs.com/examples/quick-start/" target="_blank" class="btn btn-success btn-sm"><i class="bi bi-pencil-square"></i> Pagina Oficial</a>
    </div>
</h5>

<?php
//variable para los marcadores
$arrMarkers = [
    [-33.46192155093301,  -70.71959640502931, '',   '#81a1c1', "<i class='bi bi-alarm text-primary'></i>", '<b>Configuracion por defecto</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.46192155093301,  -70.70959640502931, 'A1', '#4c566a', "<i class='bi bi-alarm text-muted'></i>",   '<b>Configuracion por defecto</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.46192155093301,  -70.69959640502931, 'A2', '#2c9e68', "<i class='bi bi-alarm text-success'></i>", '<b>Configuracion por defecto</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.46192155093301,  -70.68959640502931, 'A3', '#ea5757', "<i class='bi bi-alarm text-white'></i>",   '<b>Configuracion por defecto</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.46192155093301,  -70.67959640502931, 'A4', '#f3c541', "<i class='bi bi-alarm text-white'></i>",   '<b>Configuracion por defecto</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],

    [-33.47192155093301,  -70.71959640502931, 'B1',  '#81a1c1', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 1</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.47192155093301,  -70.70959640502931, 'B2',  '#4c566a', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 1</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.47192155093301,  -70.69959640502931, 'B3',  '#2c9e68', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 1</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.47192155093301,  -70.68959640502931, 'B4',  '#ea5757', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 1</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.47192155093301,  -70.67959640502931, 'B5',  '#f3c541', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 1</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.47192155093301,  -70.66959640502931, 'B6',  '#88c0d0', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 1</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.47192155093301,  -70.65959640502931, 'B7',  '#81a1c1', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 1</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.47192155093301,  -70.64959640502931, 'B8',  '#4c566a', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 1</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.47192155093301,  -70.63959640502931, 'B9',  '#2c9e68', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 1</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.47192155093301,  -70.62959640502931, 'B10', '#ea5757', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 1</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.47192155093301,  -70.61959640502931, 'B11', '#f3c541', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 1</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.47192155093301,  -70.60959640502931, 'B12', '#88c0d0', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 1</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],

    [-33.48192155093301,  -70.71959640502931, 'C1',  '#81a1c1', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 2</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.48192155093301,  -70.70959640502931, 'C2',  '#4c566a', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 2</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.48192155093301,  -70.69959640502931, 'C3',  '#2c9e68', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 2</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.48192155093301,  -70.68959640502931, 'C4',  '#ea5757', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 2</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.48192155093301,  -70.67959640502931, 'C5',  '#f3c541', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 2</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.48192155093301,  -70.66959640502931, 'C6',  '#88c0d0', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 2</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.48192155093301,  -70.65959640502931, 'C7',  '#81a1c1', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 2</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.48192155093301,  -70.64959640502931, 'C8',  '#4c566a', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 2</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.48192155093301,  -70.63959640502931, 'C9',  '#2c9e68', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 2</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.48192155093301,  -70.62959640502931, 'C10', '#ea5757', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 2</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.48192155093301,  -70.61959640502931, 'C11', '#f3c541', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 2</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.48192155093301,  -70.60959640502931, 'C12', '#88c0d0', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 2</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],

    [-33.49192155093301,  -70.71959640502931, 'D1',  '#81a1c1', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 3</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.49192155093301,  -70.70959640502931, 'D2',  '#4c566a', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 3</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.49192155093301,  -70.69959640502931, 'D3',  '#2c9e68', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 3</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.49192155093301,  -70.68959640502931, 'D4',  '#ea5757', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 3</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.49192155093301,  -70.67959640502931, 'D5',  '#f3c541', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 3</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.49192155093301,  -70.66959640502931, 'D6',  '#88c0d0', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 3</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.49192155093301,  -70.65959640502931, 'D7',  '#81a1c1', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 3</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.49192155093301,  -70.64959640502931, 'D8',  '#4c566a', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 3</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.49192155093301,  -70.63959640502931, 'D9',  '#2c9e68', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 3</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.49192155093301,  -70.62959640502931, 'D10', '#ea5757', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 3</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.49192155093301,  -70.61959640502931, 'D11', '#f3c541', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 3</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],
    [-33.49192155093301,  -70.60959640502931, 'D12', '#88c0d0', "<i class='bi bi-alarm text-white'></i>",  '<b>Configuracion 3</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. '],

];

//variable para los poligonos
$arrPolygon = [
    [[[-33.4499, -70.6993],                      [-33.4439, -70.7193],                      [-33.4519, -70.7393]],                                                              1, 'Soy un poligono 1.'],
    [[[-33.465072090022645, -70.74491500854494], [-33.47681399959164, -70.74457168579103],  [-33.46750651907221, -70.72465896606447]],                                          2, 'Soy un poligono 2.'],
    [[[-33.49012911778188, -70.72105407714845],  [-33.494853345110236, -70.72156906127931], [-33.49356494503731, -70.71075439453126], [-33.48483195034282, -70.7102394104004]], 3, 'Soy un poligono 3.'],
];

//variable para los circulos
$arrCircles = [
    [-33.4389, -70.6593, 1, 500, 'Soy un circulo 1.'],
    [-33.4589, -70.6593, 2, 500, 'Soy un circulo 2.'],
    [-33.4789, -70.6593, 3, 500, 'Soy un circulo 3.'],
    [-33.4989, -70.6593, 4, 500, 'Soy un circulo 4.'],
];

//variable para las lineas
$arrPolyLine = [
    [[[-33.42970788147491, -70.71950912475587], [-33.43192841590051,  -70.7179641723633],  [-33.42985114347482,  -70.7116985321045]],  1],
    [[[-33.43837480684517, -70.73006629943849], [-33.435509803323974, -70.72216987609865], [-33.4381599348624,   -70.72165489196779]], 2],
    [[[-33.43694231690903, -70.70363044738771], [-33.43543817702395,  -70.69908142089845], [-33.438804549214396, -70.69916725158693]], 3],
];

//variable para los rectangulos
$arrRectangle = [
    [[[-33.45377257994491, -70.69667816162111], [-33.452197119397916, -70.68586349487306]], 1, 'Soy un rectangulo 1.'],
    [[[-33.51762622959025, -70.70654869079591], [-33.50832313519918,  -70.7000255584717]],  2, 'Soy un rectangulo 2.'],
];

//Variable con las direcciones
$arrDirecciones = [
    ['Los lirios 455, puente alto, santiago, chile'],
    ['Av. Concha y Toro 3459, Puente Alto, Región Metropolitana, Chile', 'A'],
    ['Av. Concha y Toro 6116, Puente Alto, Región Metropolitana, Chile', 'B', '#2c9e68'],
    ['Marcos Pérez 44, Puente Alto, Región Metropolitana, Chile',        'C', '#f3c541', "<i class='bi bi-shop text-info'></i>"],
    ['Av. Concha y Toro 1820, Puente Alto, Región Metropolitana, Chile', 'D', '#3b4252', "<i class='bi bi-tree text-warning'></i>",  'municipalidad'],
];

//variable para lo mapas de calor
$arrHeatMap = [
    [-33.8839,       -70.3745188667, 9.9],
    [-33.8869090667, -70.3657417333, 9.9],
    [-33.8894207167, -70.4015351167, 9.9],
    [-33.8927369333, -70.4087452333, 9.9],
    [-33.90585105,   -70.4453463833, 9.9],
    [-33.9064188833, -70.4441556833, 9.9],
    [-33.90584715,   -70.4463564333, 9.9],
    [-33.9033391333, -70.4244005667, 9.9],
    [-33.9061991333, -70.4492620333, 9.9],
    [-33.9058955167, -70.4445613167, 9.9],
    [-33.88888045,   -70.39146475,   9.9],
    [-33.8950811333, -70.41079175,   9.9],
    [-33.88909235,   -70.3922956333, 9.9],
    [-33.8889259667, -70.3938591667, 9.9],
    [-33.8876576333, -70.3859563833, 9.9],
    [-33.89027155,   -70.3973178833, 9.9],
    [-33.8864473667, -70.3806136833, 9.9],
    [-33.9000262833, -70.4183242167, 9.9],
    [-33.90036495,   -70.4189457, 9.9],
    [-33.9000976833, -70.4197312167, 9.9],
    [-33.90239975,   -70.42371165, 9.9],
    [-33.9043379667, -70.42430325, 9.9],
    [-33.9026441,    -70.4231055167, 9.9],
    [-33.8883536333, -70.3888573833, 9.9],
    [-33.9029948833, -70.4237386167, 9.9],
    [-33.89824135,   -70.4150421667, 9.9],
    [-33.8976067833, -70.41510265, 9.9],
    [-33.9023491333, -70.4225495, 9.9],
    [-33.8856157167, -70.3775632833, 9.9],
    [-33.8963032667, -70.4132068, 9.9],
    [-33.8922813667, -70.4073402333, 9.9],
    [-33.88933345,   -70.3956084333, 9.9],
    [-33.8936148833, -70.4090577, 9.9],
    [-33.8939398,    -70.4094444833, 9.9],
    [-33.8857355333, -70.3722297667, 9.9],
    [-33.8931092167, -70.4083014, 9.9],
    [-33.9008253167, -70.4198128, 9.9],
    [-33.9045052333, -70.4260735, 9.9],
    [-33.9053927167, -70.42822265, 9.9],
    [-33.90507935,   -70.4313065, 9.9],
    [-33.9055749667, -70.4319092167, 9.9],
    [-33.9039034833, -70.4274736667, 9.9],
    [-33.9037633,    -70.4261181833, 9.9],
    [-33.9038755,    -70.42871045, 9.9],
    [-33.90369555,   -70.4285285, 9.9],
    [-33.9056626,    -70.4341078833, 9.9],
    [-33.9018736833, -70.438852, 9.9],
    [-33.9057596167, -70.4356650167, 9.9],
    [-33.9053502,    -70.4361049333, 9.9],
    [-33.9053379167, -70.4366986167, 9.9],
    [-33.9058892333, -70.4381450333, 9.9],
    [-33.9060264167, -70.4400763167,9.9],
    [-33.9056766833, -70.4412592, 9.9],
    [-33.9057312167, -70.4418380333, 9.9],
    [-33.9061575833, -70.4421068667, 9.9],
    [-33.9063946167, -70.4438004667, 9.9],
    [-33.8996027667, -70.43995055, 9.9],
    [-33.9006449667, -70.4395556833, 9.9],
    [-33.9009138167, -70.4394061333, 9.9],
    [-33.9034547,    -70.4396315, 9.9],
    [-33.9055243,    -70.4396033, 9.9],
    [-33.89952325,   -70.4406619167, 9.9],
    [-33.90561525,   -70.4404853167, 9.9],
    [-33.9045602333, -70.4477690333, 9.9],
    [-33.9040051667, -70.4388491833, 9.9],
    [-33.90588145,   -70.4440349167, 9.9],
    [-33.90595915,   -70.4389286833, 9.9],
    [-33.9059939667, -70.4398068833, 9.9],
    [-33.8868631833, -70.37991055, 9.9],
    [-33.8878744833, -70.382179, 9.9],
    [-33.8880764,    -70.3839845667, 9.9],
    [-33.8850457333, -70.3759821, 9.9],
    [-33.88446045,   -70.3762872667, 9.9],
    [-33.8880782667, -70.38423415, 9.9],
    [-33.8863533833, -70.3690698667, 9.9],
    [-33.8861783167, -70.3710009833, 9.9],
    [-33.885424,     -70.3716677833, 9.9],
    [-33.88524065,   -70.3722141167, 9.9],
    [-33.9022371333, -70.47991035, 9.9],
    [-33.9020014833, -70.4799581667, 9.9],
    [-33.9020824,    -70.4802630167, 9.9],
    [-33.9018589833, -70.4804760833, 9.9],
    [-33.9018211333, -70.4806769667, 9.9],
    [-33.9021543667, -70.4805538833, 9.9],
    [-33.9022658,    -70.4807579333, 9.9],
    [-33.9024517833, -70.4806480667, 9.9],
    [-33.9024251167, -70.48041985, 9.9],
    [-33.9023317833, -70.4802119667, 9.9],
    [-33.9321212167, -70.4555088, 9.9],
    [-33.8956185167, -70.4719458667, 9.9],
    [-33.8954566,    -70.4728120333, 9.9],
    [-33.8957231833, -70.4727906, 9.9],
    [-33.8956085833, -70.4726702, 9.9],
    [-33.8956460167, -70.4718485167, 9.9],
    [-33.8953487167, -70.47202915, 9.9],
    [-33.8800121167, -70.4865467167, 9.9],
    [-33.8803487833, -70.48595255, 9.9],
    [-33.8802064167, -70.4861004, 9.9],
    [-33.8800705167, -70.4862671167, 9.9],
    [-33.8798887333, -70.4863712333, 9.9],
    [-33.8801676667, -70.4866722667, 9.9],
    [-33.88029245,   -70.4868499667, 9.9],
    [-33.8803302167, -70.4865822167, 9.9],
    [-33.88038715,   -70.4864004167, 9.9],
    [-33.8805029333, -70.4862314167, 9.9],
    [-33.9127148667, -70.4710607833, 9.9],
    [-33.9118609667, -70.4668648, 9.9],
    [-33.9122010667, -70.47078695, 9.9],
    [-33.91191245,   -70.4682913833, 9.9],
    [-33.9112774333, -70.4668027333, 9.9],
    [-33.91244995,   -70.4700709833, 9.9],
    [-33.9149636,    -70.4772568333, 9.9],
    [-33.9128421833, -70.4702103167, 9.9],
    [-33.91130515,   -70.4650217667, 9.9],
    [-33.9140405333, -70.4754503833, 9.9],
    [-33.91155815,   -70.4670938833, 9.9],
    [-33.9144416167, -70.4754564, 9.9],
    [-33.91149715,   -70.4668828667, 9.9],
    [-33.9155068167, -70.4784839167, 9.9],
    [-33.9135311667, -70.4736794833, 9.9],
    [-33.9146717667, -70.4773664833, 9.9],
    [-33.9135175667, -70.4724437333, 9.9],
    [-33.9117463,    -70.4676612167, 9.9],
    [-33.9136108833, -70.47263915, 9.9],
    [-33.9118005167, -70.46788515, 9.9],
    [-33.9142630167, -70.4748833333, 9.9],
    [-33.9118481833, -70.4680930167, 9.9],
    [-33.91519165,   -70.47727755, 9.9],
    [-33.9121701,    -70.4679073167, 9.9],
    [-33.9152358167, -70.4780924833, 9.9],
    [-33.9122425667, -70.4681859167, 9.9],
    [-33.9150027167, -70.47843285, 9.9],
    [-33.91196865,   -70.4684916833, 9.9],
    [-33.9132330333, -70.4726685333, 9.9],
    [-33.9123722,    -70.4685087667, 9.9],
    [-33.9151754667, -70.4790262, 9.9],
    [-33.9120319833, -70.46868985, 9.9],
    [-33.9151328167, -70.4788729, 9.9],
    [-33.9124617167, -70.4687799833, 9.9],
    [-33.9150617167, -70.4786454167, 9.9],
    [-33.9120926,    -70.4688931667, 9.9],
    [-33.9132881333, -70.47285965, 9.9],
    [-33.9119984333, -70.4691844, 9.9],
    [-33.9120311,    -70.4673706667, 9.9],
    [-33.91214925,   -70.46909885, 9.9],
    [-33.91408025,   -70.4759690833, 9.9],
    [-33.9125366,    -70.4691343, 9.9],
    [-33.9134794833, -70.4739836167, 9.9],
    [-33.9122081167, -70.4674649333, 9.9],
    [-33.9140814333, -70.4736708667, 9.9],
    [-33.9120801,    -70.4675947333, 9.9],
    [-33.9113324167, -70.46512405, 9.9],
    [-33.91185795,   -70.4686138167, 9.9],
    [-33.9144403167, -70.4767387667, 9.9],
    [-33.9125054167, -70.46896025, 9.9],
    [-33.9151334833, -70.4778022667, 9.9],
    [-33.9126167833, -70.4688409667, 9.9],
    [-33.9111576,    -70.4663765167, 9.9],
    [-33.9112960833, -70.4662379, 9.9],
    [-33.9116252167, -70.46602135, 9.9],
    [-33.9113666167, -70.4664507833, 9.9],
    [-33.9117068333, -70.466336, 9.9],
    [-33.9114338333, -70.4666576, 9.9],
    [-33.9119338667, -70.4665694167, 9.9],
    [-33.9117808333, -70.4665752, 9.9],
    [-33.9110205,    -70.4652438667, 9.9],
    [-33.9110742833, -70.4654501167, 9.9],
    [-33.9111370833, -70.4656566833, 9.9],
    [-33.9111865833, -70.4658542667, 9.9],
    [-33.9112390333, -70.46602075, 9.9],
    [-33.9118135167, -70.46543705, 9.9],
    [-33.9118572167, -70.46556135, 9.9],
    [-33.91145615,   -70.4655286, 9.9],
    [-33.9115389167, -70.4657957167, 9.9],
    [-33.9127748333, -70.4699760667, 9.9],
    [-33.9125127167, -70.4703133, 9.9],
    [-33.9129274,    -70.4704172833, 9.9],
    [-33.9125759833, -70.4705303667, 9.9],
    [-33.9129758667, -70.4706118, 9.9],
    [-33.9126359667, -70.4707644, 9.9],
    [-33.91226225,   -70.47106665, 9.9],
    [-33.9130937833, -70.4709588833, 9.9],
    [-33.9131644667, -70.4711523, 9.9],
    [-33.9132299667, -70.4713462167, 9.9],
    [-33.9127690833, -70.4712279667, 9.9],
    [-33.9133607167, -70.4730695833, 9.9],
    [-33.91367805,   -70.4728816667, 9.9],
    [-33.9134211,    -70.4732760667, 9.9],
    [-33.9137477833, -70.4731176, 9.9],
    [-33.9138932333, -70.4736511667, 9.9],
    [-33.9135950667, -70.4738879833, 9.9],
    [-33.9139430167, -70.4737982333, 9.9],
    [-33.9136486,    -70.4740868667, 9.9],
    [-33.91400415,   -70.4740125833, 9.9],
    [-33.9140350333, -70.4741693833, 9.9],
    [-33.91432385,   -70.475081, 9.9],
    [-33.9139975333, -70.47523055, 9.9],
    [-33.9143889667, -70.47526065, 9.9],
    [-33.9137640333, -70.47575135, 9.9],
    [-33.91449875,   -70.4756521167, 9.9],
    [-33.9141123,    -70.4756848833, 9.9],
    [-33.9145492167, -70.4758458667, 9.9],
    [-33.9141779667, -70.4758650667, 9.9],
    [-33.9146104833, -70.4760345, 9.9],
    [-33.9142320333, -70.4760478833, 9.9],
    [-33.9146642167, -70.47621125, 9.9],
    [-33.9142896667, -70.4762277667, 9.9],
    [-33.9147136833, -70.4764402833, 9.9],
    [-33.9143434167, -70.47639805, 9.9],
    [-33.9143937167, -70.4765685, 9.9],
    [-33.91501315,   -70.4774403667, 9.9],
    [-33.9154860667, -70.4774428167, 9.9],
    [-33.9149432667, -70.4782801, 9.9],
    [-33.9152651667, -70.47833035, 9.9],
    [-33.9299333167, -70.55909085, 9.9],
    [-33.9286782833, -70.5545978, 9.9],
    [-33.9300747333, -70.5497311333, 9.9],
    [-33.9276611167, -70.5543011333, 9.9],
    [-33.9305557833, -70.5594630333, 9.9],
    [-33.9280362,    -70.5517895, 9.9],
    [-33.9284764,    -70.5616764333, 9.9],
    [-33.93143935,   -70.55390345, 9.9],
    [-33.9288132,    -70.5647016167, 9.9],
    [-33.9275235,    -70.5619954833, 9.9],
    [-33.93081245,   -70.5577222333, 9.9],
    [-33.9300416667, -70.5566331333, 9.9],
    [-33.92921255,   -70.5684947, 9.9],
    [-33.9304111667, -70.5673126333, 9.9],
    [-33.9291729667, -70.5653647333, 9.9],
    [-33.9289266333, -70.5656691333, 9.9],
    [-33.92751825,   -70.5531413167, 9.9],
    [-33.9323321667, -70.5512635167, 9.9],
    [-33.9045377667, -70.4827770167, 9.9],
    [-33.9051343333, -70.4829339167, 9.9],
    [-33.9045625,    -70.4832139167, 9.9],
    [-33.9052854167, -70.4828661667, 9.9],
    [-33.9045012833, -70.4825234, 9.9],
    [-33.9054383,    -70.4831963, 9.9],
    [-33.9048575167, -70.4826078167, 9.9],
    [-33.9050790667, -70.4825558167, 9.9],
    [-33.90496205,   -70.4830191667, 9.9],
    [-33.9050431833, -70.4823803833, 9.9],
    [-33.9047063167, -70.4826914667, 9.9],
    [-33.9051649333, -70.4825182667, 9.9],
    [-33.9047697333, -70.4831092667, 9.9],
    [-33.9044120833, -70.4828407333, 9.9],
    [-33.8987653333, -70.4845873667, 9.9],
    [-33.89849935,   -70.4843253333, 9.9],
    [-33.8989808833, -70.4835699333, 9.9],
    [-33.8982670333, -70.4839818167, 9.9],
    [-33.89792805,   -70.4841291833, 9.9],
    [-33.8990696333, -70.48395625, 9.9],
    [-33.8983429167, -70.4837488833, 9.9],
    [-33.8986908667, -70.4846387167, 9.9],
    [-33.8985086333, -70.48330895, 9.9],
    [-33.8980756833, -70.4840675333, 9.9],
    [-33.8984572667, -70.4838707, 9.9],
    [-33.8988333833, -70.48411825, 9.9],
    [-33.89797735,   -70.4845139167, 9.9],
    [-33.8988639833, -70.4832473, 9.9],
    [-33.8985740667, -70.4844548333, 9.9],
];

?>

<section class="section">
    <div class="row">

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    Prueba de HeatMap
                </div>
                <div class="card-body">
                    <?php
                    //se imprime input
                    $Options = [
                        'Latitud'      => -33.8869090667,   //Latitud de la ubicacion
                        'Longitud'     => -70.3657417333,   //Longitud de la ubicacion
                        'ID_Map'       => 'map_aa',         //ID del div donde se dibuja el html
                        'Zoom'         => 13,               //Zoom del mapa
                        'attribution'  => '&copy; Test',    //Pie de pagina del mapa
                        'arrHeatMap'   => $arrHeatMap,      //array con los puntos de calor
                    ];
                    echo $data['Fnc_WidgetsMaps']->leaFletMap_from_gps($Options);
                    ?>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card mb-4">
                <div class="card-header">
                    Prueba de Configuracion Mapa
                </div>
                <div class="card-body">
                    <?php
                    //se imprime input
                    $Options = [
                        'Latitud'      => -33.4627808002225,  //Latitud de la ubicacion
                        'Longitud'     => -70.69187164306642, //Longitud de la ubicacion
                        'ID_Map'       => 'map_0',            //ID del div donde se dibuja el html
                        'Zoom'         => 13,                 //Zoom del mapa
                        'attribution'  => '&copy; Test',      //Pie de pagina del mapa
                        'ConfMode'     => 2,                  //Modo del mapa
                    ];
                    echo $data['Fnc_WidgetsMaps']->leaFletMap_from_gps($Options);
                    ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    Prueba de Direcciones
                </div>
                <div class="card-body">
                    <?php
                    //se imprime input
                    $Options = [
                        'ID_Map'         => 'map_dir',          //ID del div donde se dibuja el html
                        'Zoom'           => 13,                 //Zoom del mapa
                        'attribution'    => '&copy; Test',      //Pie de pagina del mapa
                        'arrDirecciones' => $arrDirecciones,    //array con las direcciones
                    ];
                    echo $data['Fnc_WidgetsMaps']->leaFletMap_from_direccion($Options);
                    ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    Prueba de marcadores
                </div>
                <div class="card-body">
                    <?php
                    //se imprime input
                    $Options = [
                        'Latitud'      => -33.4627808002225,      //Latitud de la ubicacion
                        'Longitud'     => -70.65959640502931,     //Longitud de la ubicacion
                        'ID_Map'       => 'map_1',                //ID del div donde se dibuja el html
                        'Zoom'         => 13,                     //Zoom del mapa
                        'attribution'  => '&copy; Test',          //Pie de pagina del mapa
                        'arrMarkers'   => $arrMarkers,            //array con los marcadores
                        'defaultLayer' => 'Esri_WorldTopoMap',    //Layer a mostrar en la carga
                    ];
                    echo $data['Fnc_WidgetsMaps']->leaFletMap_from_gps($Options);
                    ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    Prueba de poligonos
                </div>
                <div class="card-body">
                    <?php
                    //se imprime input
                    $Options = [
                        'Latitud'      => -33.4627808002225,   //Latitud de la ubicacion
                        'Longitud'     => -70.69187164306642,  //Longitud de la ubicacion
                        'ID_Map'       => 'map_2',             //ID del div donde se dibuja el html
                        'Zoom'         => 13,                  //Zoom del mapa
                        'attribution'  => '&copy; Test',       //Pie de pagina del mapa
                        'arrPolygon'   => $arrPolygon,         //array con los poligonos
                    ];
                    echo $data['Fnc_WidgetsMaps']->leaFletMap_from_gps($Options);
                    ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    Prueba de circulos
                </div>
                <div class="card-body">
                    <?php
                    //se imprime input
                    $Options = [
                        'Latitud'      => -33.4627808002225,  //Latitud de la ubicacion
                        'Longitud'     => -70.69187164306642, //Longitud de la ubicacion
                        'ID_Map'       => 'map_3',            //ID del div donde se dibuja el html
                        'Zoom'         => 13,                 //Zoom del mapa
                        'attribution'  => '&copy; Test',      //Pie de pagina del mapa
                        'arrCircles'   => $arrCircles,        //array con los circulos
                    ];
                    echo $data['Fnc_WidgetsMaps']->leaFletMap_from_gps($Options);
                    ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    Prueba de lineas
                </div>
                <div class="card-body">
                    <?php
                    //se imprime input
                    $Options = [
                        'Latitud'      => -33.4627808002225,   //Latitud de la ubicacion
                        'Longitud'     => -70.69187164306642,  //Longitud de la ubicacion
                        'ID_Map'       => 'map_4',             //ID del div donde se dibuja el html
                        'Zoom'         => 13,                  //Zoom del mapa
                        'attribution'  => '&copy; Test',       //Pie de pagina del mapa
                        'arrPolyLine'  => $arrPolyLine,        //array con las lineas
                    ];
                    echo $data['Fnc_WidgetsMaps']->leaFletMap_from_gps($Options);
                    ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    Prueba de rectangulos
                </div>
                <div class="card-body">
                    <?php
                    //se imprime input
                    $Options = [
                        'Latitud'      => -33.4627808002225,   //Latitud de la ubicacion
                        'Longitud'     => -70.69187164306642,  //Longitud de la ubicacion
                        'ID_Map'       => 'map_5',             //ID del div donde se dibuja el html
                        'Zoom'         => 13,                  //Zoom del mapa
                        'attribution'  => '&copy; Test',       //Pie de pagina del mapa
                        'arrRectangle' => $arrRectangle,       //array con los rectangulos
                    ];
                    echo $data['Fnc_WidgetsMaps']->leaFletMap_from_gps($Options);
                    ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    Prueba de eventos
                </div>
                <div class="card-body">
                    <?php
                    //se imprime input
                    $Options = [
                        'Latitud'      => -33.4627808002225,  //Latitud de la ubicacion
                        'Longitud'     => -70.69187164306642, //Longitud de la ubicacion
                        'ID_Map'       => 'map_6',            //ID del div donde se dibuja el html
                        'Zoom'         => 13,                 //Zoom del mapa
                        'attribution'  => '&copy; Test',      //Pie de pagina del mapa
                        'events'       => true,               //Si se ejecuta algo al hacer click (revisar)
                    ];
                    echo $data['Fnc_WidgetsMaps']->leaFletMap_from_gps($Options);
                    ?>
                </div>
            </div>
        </div>

    </div>
</section>


<style>
    .leaflet-iconex div{stroke: unset !important;}
</style>
