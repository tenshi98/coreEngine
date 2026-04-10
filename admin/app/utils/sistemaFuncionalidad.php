<?php
/*******************************************************************************************************************/
/*                                            Pantalla de inicio                                                   */
/*******************************************************************************************************************/
$f3->route('GET  /core/fileExplorer/updateList/@route/@tipos/@path',  'sistemaFuncionalidad->FileExplorer_updateView');   //actualizacion de la vista
$f3->route('POST /core/fileExplorer/createFolder',                    'sistemaFuncionalidad->FileExplorer_createFolder'); //Creacion de carpetas
$f3->route('POST /core/fileExplorer/uploadFile',                      'sistemaFuncionalidad->FileExplorer_uploadFile');   //Subida de archivos
$f3->route('POST /core/fileExplorer/deleteFolder',                    'sistemaFuncionalidad->FileExplorer_delFolder');    //Eliminacion de carpetas
$f3->route('POST /core/fileExplorer/deleteFile',                      'sistemaFuncionalidad->FileExplorer_delFile');      //Eliminacion de archivos



