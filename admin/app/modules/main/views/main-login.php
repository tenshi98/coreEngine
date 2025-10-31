<main>
    <div class="container">
        <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

                        <div class="card">
                            <div class="card-body">
                                <ul class="nav nav-tabs nav-tabs-bordered d-flex pt-4" id="borderedTabJustified" role="tablist">
                                    <li class="nav-item flex-fill" role="presentation"><button class="nav-link w-100 active" id="tab_1" data-bs-toggle="tab" data-bs-target="#bordered-tab_1" type="button" role="tab" aria-controls="tab_1" aria-selected="true">Ingresar</button></li>
                                    <li class="nav-item flex-fill" role="presentation"><button class="nav-link w-100"        id="tab_2" data-bs-toggle="tab" data-bs-target="#bordered-tab_2" type="button" role="tab" aria-controls="tab_2" aria-selected="false" tabindex="-1">Recuperar</button></li>
                                </ul>
                                <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                                    <div class="tab-pane fade show active" id="bordered-tab_1" role="tabpanel" aria-labelledby="tab_1">
                                        <div class="pt-4 pb-2">
                                            <h5 class="card-title text-center pb-0 fs-4">Iniciar Sesión</h5>
                                            <p class="text-center small">Ingrese su Email y contraseña para acceder</p>
                                        </div>
                                        <form id="LoginForm" name="LoginForm" autocomplete="off" method="POST" class="row g-3" action="" role="form" novalidate enctype="multipart/form-data">

                                            <div class="col-12">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="email" id="email" required>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label">Contraseña</label>
                                                <input type="password" class="form-control" name="password" id="password" required>
                                            </div>

                                            <div class="col-12">
                                                <input type="hidden" name="nombre" id="nombre">
                                                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-person"></i> Iniciar Sesión</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="bordered-tab_2" role="tabpanel" aria-labelledby="tab_2">
                                        <div class="pt-4 pb-2">
                                            <h5 class="card-title text-center pb-0 fs-4">¿Olvidaste tu contraseña?</h5>
                                            <p class="text-center small">Ingresa tu Email para recuperar tu contraseña.Revisa la bandeja de entrada o spam de tu correo.</p>
                                        </div>
                                        <form id="RecoverForm" name="RecoverForm" autocomplete="off" method="POST" class="row g-3" action="" role="form" novalidate enctype="multipart/form-data">

                                            <div class="col-12">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="email" id="email" required>
                                            </div>

                                            <div class="col-12">
                                                <input type="hidden" name="nombre" id="nombre">
                                                <button class="btn btn-danger w-100" type="submit"><i class="ri-mail-line"></i> Recuperar contraseña</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </section>

    </div>

</main>

<script>
    /******************************************/
    $("#LoginForm").submit(function(e) {
        e.preventDefault();
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Metodo      = 'POST';
        let Direccion   = '<?php echo $BASE.'/login'; ?>';
        let Informacion = $("#LoginForm").serialize();
        const Options     = {
            Destino:'<?php echo $BASE.'/principal'; ?>',
            closeObject:'#PDloader',
        };
        SendDataForms(Metodo, Direccion, Informacion, Options);
    });
    /******************************************/
    $("#RecoverForm").submit(function(e) {
        e.preventDefault();
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Metodo      = 'POST';
        let Direccion   = '<?php echo $BASE.'/forgot'; ?>';
        let Informacion = $("#RecoverForm").serialize();
        const Options     = {
            Destino:'<?php echo $BASE.'/login'; ?>',
            closeObject:'#PDloader',
        };
        SendDataForms(Metodo, Direccion, Informacion, Options);
    });

</script>