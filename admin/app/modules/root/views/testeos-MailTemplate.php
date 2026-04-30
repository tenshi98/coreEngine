<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)


echo norm_text($data['MailTemplate']);

function norm_text($Text){
    //Datos buscados
    $healthy = array('&lt;', '&gt;', '&quot;', '&amp;nbsp;');
    $yummy   = array('<', '>', '"', '&nbsp;');
    //devolver
    return str_replace($healthy, $yummy, $Text);

}


