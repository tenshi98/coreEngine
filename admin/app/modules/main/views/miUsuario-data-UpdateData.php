<?php
$arrData_1 = [
    ['Icon' => '','Titulo' => 'Email',           'Texto' => $data['rowData']['email']],
    ['Icon' => '','Titulo' => 'Tipo de usuario', 'Texto' => $data['rowData']['TipoUsuario']],
];
$arrData_2 = [
    ['Icon' => '','Titulo' => 'Nombre',               'Texto' => $data['rowData']['Nombre']],
    ['Icon' => '','Titulo' => 'Fono',                 'Texto' => $data['Fnc_DataNumbers']->formatPhone($data['rowData']['Fono'])],
    ['Icon' => '','Titulo' => 'Rut',                  'Texto' => $data['rowData']['Rut']],
    ['Icon' => '','Titulo' => 'Fecha de Nacimiento',  'Texto' => $data['Fnc_DataDate']->fechaCompleta($data['rowData']['fNacimiento'])],
    ['Icon' => '','Titulo' => 'Ciudad',               'Texto' => $data['rowData']['Ciudad']],
    ['Icon' => '','Titulo' => 'Comuna',               'Texto' => $data['rowData']['Comuna']],
    ['Icon' => '','Titulo' => 'Dirección',            'Texto' => $data['rowData']['Direccion']],
];
$arrData_3 = [
    ['Icon' => '','Titulo' => 'X(Twitter)',  'Texto' => $data['rowData']['Social_X']],
    ['Icon' => '','Titulo' => 'Facebook',    'Texto' => $data['rowData']['Social_Facebook']],
    ['Icon' => '','Titulo' => 'Instagram',   'Texto' => $data['rowData']['Social_Instagram']],
    ['Icon' => '','Titulo' => 'Linkedin',    'Texto' => $data['rowData']['Social_Linkedin']],
];
$arrData_4 = [
    ['Icon' => '','Titulo' => 'Posición Menu',  'Texto' => $data['rowData']['MenuPosicion']],
];

/*******************************************/
echo '<h5 class="card-title">Datos del Perfil</h5>';
$data['Fnc_WidgetsCommon']->responsiveTable($arrData_1, 8);

echo '<h5 class="card-title">Datos Personales</h5>';
$data['Fnc_WidgetsCommon']->responsiveTable($arrData_2, 8);

echo '<h5 class="card-title">Social</h5>';
$data['Fnc_WidgetsCommon']->responsiveTable($arrData_3, 8);

echo '<h5 class="card-title">Opciones</h5>';
$data['Fnc_WidgetsCommon']->responsiveTable($arrData_4, 8);
?>
