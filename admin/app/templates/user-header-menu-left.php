<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

  <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
      <a class="nav-link " href="<?php echo $BASE.'/principal'; ?>"><i class="bi bi-grid"></i><span>Principal</span></a>
    </li>

    <?php
    //Verifico si hay datos
    if(is_array($_SESSION['arrMenu'])){
      //Variable
      $x_var = 0;
      //Recorro
      foreach ($_SESSION['arrMenu'] AS $Categoria=>$permisos){
        //var
        $x_var++;
        //Icono
        $Icono  = $permisos[0]['PermisosIcon'];
        //Si hay colores
        $Icono .= !empty($permisos[0]['PermisosIconColor'])
                  ? ' '.$permisos[0]['PermisosIconColor']
                  : '';
        //imprimimos la categoría
        echo '
        <li class="nav-item">
          <a class="nav-link collapsed" data-bs-target="#test-nav_'.$x_var.'" data-bs-toggle="collapse" href="#">
            <i class="'.$Icono.'"></i><span>'.$Categoria.'</span><i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <ul id="test-nav_'.$x_var.'" class="nav-content collapse " data-bs-parent="#sidebar-nav">';
            //se recorren los datos dentro de la categoría
            foreach ($permisos AS $perm){
              echo '<li><a href="'.$BASE.'/'.$perm['RutaWeb'].'/listAll"><i class="bi bi-circle"></i><span> '.$perm['Nombre'].'</span></a></li>';
            }
            echo '
          </ul>
        </li>';
      }
    } ?>

  </ul>

</aside>