<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<section class="section" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $data['TableTitle']; ?></h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Funcion</th>
                                    <th scope="col" style="width: 120px;" class="text-center">Verificacion Funcion</th>
                                    <th scope="col" style="width: 120px;" class="text-center">Devolucion Datos</th>
                                    <th scope="col" style="width: 120px;" class="text-center">Tipo Datos</th>
                                    <th scope="col">Comparacion Datos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                //Contador
                                $i      = 0;
                                $dearch = '';
                                //recorro
                                foreach ($data['test'] as $result) {
                                    //sumo
                                    $i++;
                                    //Solo si es el primer dato
                                    if($i==1){
                                        echo '<tr><td>';
                                        $dearch = $result['text'];
                                    }else{
                                        echo '</td><td>';
                                    }

                                    //Divido texto
                                    $resultado = $data['Fnc_DataText']->dividirTexto($result['text'], 'aaa');
                                    //si hay efectivamente datos en ambos lados
                                    if(isset($resultado['derecha'])&&$resultado['derecha']!=''){
                                        //Variables
                                        $col_left   = $resultado['izquierda'];
                                        $resultado2 = $data['Fnc_DataText']->dividirTexto($resultado['derecha'], ' -&gt; ');
                                        $col_right  = ltrim($resultado2['izquierda'], '(');
                                        //Se dibuja
                                        echo $col_left;
                                        echo '</td><td>';
                                        //Se crea boton
                                        if($result['status'] === true){
                                            $x_text  = 'Pasa';
                                            $x_color = 'text-success';
                                        }else{
                                            $x_text  = 'Falla';
                                            $x_color = 'text-danger';
                                        }
                                        if(isset($col_right)&&$col_right!=''){
                                            $x_color .= ' tooltiplink';
                                            $x_title  = 'data-title="'.$col_right.'"';
                                        }else{
                                            $x_title = '';
                                        }
                                        echo '<p class="text-center fw-bold '.$x_color.'" '.$x_title.'>'.$x_text.'</p>';
                                    //Solo datos en un lado
                                    }else{
                                        //Variables
                                        $resultado2 = $data['Fnc_DataText']->dividirTexto($resultado['izquierda'], ' -&gt; ');
                                        $col_right  = ltrim($resultado2['izquierda'], '(');
                                        //Se crea boton
                                        if($result['status'] === true){
                                            $x_text  = 'Pasa';
                                            $x_color = 'text-success';
                                        }else{
                                            $x_text  = 'Falla';
                                            $x_color = 'text-danger';
                                        }
                                        if(isset($col_right)&&$col_right!=''){
                                            $x_color .= ' tooltiplink';
                                            $x_title  = 'data-title="'.$col_right.'"';
                                        }else{
                                            $x_title = '';
                                        }
                                        echo '<p class="text-center fw-bold '.$x_color.'" '.$x_title.'>'.$x_text.'</p>';
                                    }

                                    //Si existen datos extras
                                    if(isset($result['extraText'])&&$result['extraText']!=''){
                                        // Extrae texto posterior a la palabra clave 'Devuelve '
                                        $resultado = $data['Fnc_DataText']->buscarPalabraYExtraer($dearch, 'Devuelve ');
                                        // Validar que la extracción fue exitosa
                                        if ($resultado !== false) {
                                            // Elimina el último carácter del texto extraído
                                            $extraido = substr($resultado['extraido'], 0, -1);
                                            // Caso 1: Coincidencia exacta
                                            if ($result['extraText'] == $extraido) {
                                                $SubData = 'OK: '.$result['extraText'];

                                                echo '</td><td>
                                                <div style="border: solid #dee2e6; border-width: 1px; border-radius: 0.375rem; background-color: #f8f9fa;">
                                                    <code>' . $SubData . '</code>
                                                </div>';

                                            // Caso 2: Diferencia (excluyendo valor especial 'asd')
                                            } elseif ($extraido != 'asd') {
                                                $SubData = 'Hay diferencias: ' . $result['extraText'] . ' - ' . $extraido;
                                                echo '</td><td>
                                                <div style="border: solid #af3434; border-width: 1px; border-radius: 0.375rem; background-color: #f9cccc;">
                                                    <code>' . $SubData . '</code>
                                                </div>';
                                            }
                                        }
                                    }
                                    if ($result['status']!==true){
                                        echo '</td><td><div style="border: solid #af3434;border-width: 1px;border-radius: 0.375rem;background-color: #f9cccc;"><code>'.$result['source'].'</code></div>';
                                    }
                                    //
                                    if($i==3){echo '</td></tr>';$i = 0;}

                                    ?>
                                <?php } ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

</section>
