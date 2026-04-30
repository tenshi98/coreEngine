<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

//Se carga la imagen
$UserIMG = !empty($data['UserData']['UserIMG'])
    ? $data['UserData']['MainPathUrl'].$data['UserData']['UserIMG']
    : $BASE.'/img/profile-img.jpg';
?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
    <div class="card">
        <div class="row g-0">
            <div class="col-md-4 p-3 text-center">
                <img src="<?php echo $UserIMG; ?>" class="rounded-circle img-thumbnail" alt="Profile">
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <h5 class="card-title align-items-center">
                        <?php echo '<strong>Bienvenido</strong>'; ?>
                        <br>
                        <?php echo $data['UserData']['UserName']; ?>
                    </h5>
                    <p class="card-text text-muted">
                        <i class="fas fa-briefcase"></i> <?php echo $data['UserData']['UserPosition']; ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-around">
                <a class="btn btn-primary flex-grow-1" href="<?php echo $BASE.'/perfil'; ?>"><i class="bi bi-pen"></i> Editar Perfil</a>
            </div>
        </div>
    </div>

    <?php if($data['UserData']["Config_Principal_Radio"]==2){ $data['Fnc_WidgetsCommon']->widget_radio_player($BASE, 2);} ?>
</div>
