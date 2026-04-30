<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="card" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
    <div class="card-body">
        <h5 class="card-title">Email Template</h5>

        <?php
        for ($i=1; $i < 5; $i++) {
            echo '<a target="_blank" rel="noopener noreferrer" href="'.$BASE.'/Core/testeos/send_mailTemplate/'.$i.'"  class="btn btn-primary"><i class="bx bx-mail-send"></i> Abrir Plantilla '.$i.'</a>';
        }
        ?>
    </div>
</div>

