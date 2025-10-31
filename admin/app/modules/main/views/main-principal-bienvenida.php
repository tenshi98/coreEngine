<?php
$UserIMG = !empty($data['UserData']['UserIMG'])
    ? $BASE.'/upload/'.$data['UserData']['UserIMG']
    : $BASE.'/img/profile-img.jpg';
?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-5 col-xl-5 col-xxl-5">
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
                    <div class="border-top pt-2">
                        <div class="row text-center">
                            <div class="col">
                                <h6>Projects</h6>
                                <strong>25</strong>
                            </div>
                            <div class="col border-start">
                                <h6>Following</h6>
                                <strong>142</strong>
                            </div>
                            <div class="col border-start">
                                <h6>Followers</h6>
                                <strong>289</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-around">
                <a class="btn btn-primary flex-grow-1" href="<?php echo $BASE.'/perfil'; ?>"><i class="bi bi-pen"></i> Editar Perfil</a>
            </div>
        </div>
    </div>
</div>