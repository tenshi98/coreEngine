<li class="nav-item dropdown pe-3">
    <?php
    $UserIMG = !empty($data['UserData']['UserIMG'])
        ? $BASE.'/upload/'.$data['UserData']['UserIMG']
        : $BASE.'/img/profile-img.jpg';
    ?>
    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
        <img src="<?php echo $UserIMG; ?>" alt="Profile" class="rounded-circle">
        <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo $data['UserData']['UserName']; ?></span>
    </a>

    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
        <li class="dropdown-header">
            <h6><?php echo $data['UserData']['UserName']; ?></h6>
            <span><?php echo $data['UserData']['UserPosition']; ?></span>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item d-flex align-items-center" href="<?php echo $BASE.'/perfil'; ?>"><i class="bi bi-person"></i><span>Ver mi Perfil</span></a></li>
        <li><hr class="dropdown-divider"></li>
        <li><button type="button" class="dropdown-item d-flex align-items-center dropdown-close-session" onclick="showConfirmRedirect('warning','Esta a punto de cerrar sesion', 'Confirmar', '<?php echo $BASE.'/auth/logout'; ?>')"><i class="bi bi-box-arrow-right"></i><span>Cerrar Sesión</span></button></li>
    </ul>

</li>