
<button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-list"></i> Menu</button>
<ul class="dropdown-menu dropdown-menu-large dropdown-menu-large-arrow row">

    <?php
    //Verifico si hay datos
    if(is_array($_SESSION['arrMenu'])){
        //Variables iniciales
        $Colmn      = 9;
        $arrMenu    = '<li class="col-xs-12 col-sm-6 col-md-4 col-lg-4 col-xl-4 col-xxl-4">';
        $countMenu  = 0;
        //Recorro
        foreach ($_SESSION['arrMenu'] AS $Categoria=>$permisos){
            //Variables
            $countMenu = 0;
            $arrMenu .= '<ul><li class="dropdown-tittle">'.$Categoria.'</li>';
            //se recorren los datos dentro de la categoría
            foreach ($permisos AS $perm){
                //Icono
                $Icono = $perm['PermisosIcon'] . (!empty($perm['PermisosIconColor']) ? ' ' . $perm['PermisosIconColor'] : '');
                //Menu
                $arrMenu .= '<li><a href="'.$BASE.'/'.$perm['RutaWeb'].'/listAll"><i class="'.$Icono.'" aria-hidden="true"></i> '.$perm['Nombre'].'</a></li>';
                //Sumo
                $countMenu++;
            }
            //Cierro la categoria
            $arrMenu .= '<li class="divider"></li></ul>';
            //Se restan elementos
            $Colmn = $Colmn - $countMenu;
            //Verifico columna
            if($Colmn <= 0){
                $arrMenu .= '</li><li class="col-xs-12 col-sm-6 col-md-4 col-lg-4 col-xl-4 col-xxl-4">';
                $Colmn = 9;
            }
        }
        //cierro el menu
        $arrMenu .= '</li>';
    }
    //Imprimo menu
    echo $arrMenu;
    ?>

</ul>
