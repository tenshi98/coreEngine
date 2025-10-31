<div class="col-xs-12 col-sm-6 col-md-6 col-lg-7 col-xl-5 col-xxl-5">
    <div class="card">
        <div class="card-body p-4">
            <?php if($data['UserData']['UbicacionWheater']!=''){ ?>
                <a class="weatherwidget-io" href="<?php echo $data['UserData']['UbicacionWheater']; ?>" data-label_1="<?php echo $data['UserData']['UbicacionNombre']; ?>" data-label_2="Tiempo" data-theme="pure" ><?php echo $data['UserData']['UbicacionNombre']; ?></a>
                <script>
                    !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='https://weatherwidget.io/js/widget.min.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','weatherwidget-io-js');
                </script>
            <?php }else{ ?>
                <div class="alert alert-danger alert-white alert-dismissible fade show" role="alert">
                    <div class="icon"><i class="bi bi-star me-1"></i></div>Configura la region en tu perfil para recibir el pronostico metereológico
                </div>
            <?php } ?>
        </div>
    </div>
</div>


