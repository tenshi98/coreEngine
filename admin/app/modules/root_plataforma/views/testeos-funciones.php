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
                                /************************************/
                                // Normaliza a texto cualquier valor entregado por el controlador.
                                // Test::expect() guarda en extraText el dato REAL devuelto, que puede ser
                                // string, int, float, bool, array u objeto (p.ej. indicesServer), por lo que
                                // concatenarlo a ciegas provoca "Array to string conversion" o un fatal error.
                                $aTexto = function ($v) {
                                    if (is_array($v) || is_object($v)) {
                                        $json = json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                        return ($json === false) ? gettype($v) : $json;
                                    }
                                    if (is_bool($v)) { return $v ? 'true' : 'false'; }
                                    if ($v === null)  { return 'null'; }
                                    return (string) $v;
                                };
                                // El controlador escribe el separador como ' -> ' crudo, mientras que la vista
                                // lo buscaba ya escapado (' -&gt; ') y por eso nunca llegaba a dividir el texto.
                                $separador = function ($texto) {
                                    foreach ([' -> ', ' -&gt; '] as $sep) {
                                        if (strpos((string) $texto, $sep) !== false) { return $sep; }
                                    }
                                    return ' -> ';
                                };
                                // Boton Pasa/Falla con tooltip; el contenido se escapa para no romper el atributo HTML
                                $boton = function ($status, $col_right) {
                                    $x_text  = ($status === true) ? 'Pasa' : 'Falla';
                                    $x_color = ($status === true) ? 'text-success' : 'text-danger';
                                    $x_title = '';
                                    if (isset($col_right) && $col_right !== '') {
                                        $x_color .= ' tooltiplink';
                                        $x_title  = ' data-title="'.htmlspecialchars($col_right, ENT_QUOTES, 'UTF-8').'"';
                                    }
                                    return '<p class="text-center fw-bold '.$x_color.'"'.$x_title.'>'.$x_text.'</p>';
                                };

                                //Contador
                                $i      = 0;
                                $dearch = '';
                                // Recorro
                                foreach ($data['test'] as $result) {
                                    //sumo
                                    $i++;
                                    // Texto de la comprobacion (siempre string)
                                    $texto = (string) ($result['text'] ?? '');
                                    //Solo si es el primer dato
                                    if($i==1){
                                        echo '<tr><td>';
                                        $dearch = $texto;
                                    }else{
                                        echo '</td><td>';
                                    }

                                    //Divido texto (el controlador delimita el nombre de la funcion con 'aaa')
                                    $resultado = $data['Fnc_DataText']->dividirTexto($texto, 'aaa');
                                    //si hay efectivamente datos en ambos lados
                                    if(isset($resultado['derecha'])&&$resultado['derecha']!=''){
                                        // Variables
                                        $col_left   = $resultado['izquierda'];
                                        $resultado2 = $data['Fnc_DataText']->dividirTexto($resultado['derecha'], $separador($resultado['derecha']));
                                        $col_right  = ltrim($resultado2['izquierda'], '(');
                                        //Se dibuja
                                        echo $col_left;
                                        echo '</td><td>';
                                        echo $boton($result['status'], $col_right);
                                    //Solo datos en un lado
                                    }else{
                                        // Variables: sin 'aaa' el texto completo es el valor/tipo devuelto.
                                        // Si el texto esta vacio no se genera tooltip (evita publicar el
                                        // mensaje interno de validacion "Sin datos ingresados en texto").
                                        if ($texto === '') {
                                            $col_right = '';
                                        } else {
                                            $resultado2 = $data['Fnc_DataText']->dividirTexto($resultado['izquierda'], $separador($resultado['izquierda']));
                                            $col_right  = ltrim($resultado2['izquierda'], '(');
                                        }
                                        echo $boton($result['status'], $col_right);
                                    }

                                    //Columna "Comparacion Datos": se abre SOLO en la tercera comprobacion
                                    //del grupo para respetar las 5 columnas del thead (antes solo se abria
                                    //si habia comparacion o fallo, lo que dejaba filas incompletas, y el
                                    //fallo abria una sexta celda extra que desalineaba la fila).
                                    if ($i == 3) { echo '</td><td>'; }

                                    //Si existen datos extras
                                    if (isset($result['extraText']) && $result['extraText'] !== null && $result['extraText'] !== '') {
                                        // Extrae texto posterior a la palabra clave 'Devuelve '
                                        $resultado = $data['Fnc_DataText']->buscarPalabraYExtraer($dearch, 'Devuelve ');
                                        // Validar que la extraccion fue exitosa (devuelve array o false)
                                        if ($resultado['success'] === true) {
                                            // Elimina el ultimo caracter del texto extraido
                                            $extraido = norm_text(substr($resultado['data']['extraido'], 0, -1));
                                            // Valor real devuelto, normalizado a texto
                                            $actual   = $aTexto($result['extraText']);
                                            // Caso 1: Coincidencia exacta
                                            if ($actual == $extraido) {
                                                $SubData = 'OK: '.$actual;

                                                echo '<div style="border: solid #dee2e6; border-width: 1px; border-radius: 0.375rem; background-color: #f8f9fa;">
                                                    <code>' . htmlspecialchars($SubData, ENT_QUOTES, 'UTF-8') . '</code>
                                                </div>';

                                            // Caso 2: Diferencia (excluyendo valor especial 'asd')
                                            } elseif ($extraido != 'asd') {
                                                $SubData = 'Hay diferencias: ' . norm_text($actual) . ' - ' . $extraido;
                                                echo '<div style="border: solid #af3434; border-width: 1px; border-radius: 0.375rem; background-color: #f9cccc;">
                                                    <code>' . htmlspecialchars($SubData, ENT_QUOTES, 'UTF-8') . '</code>
                                                </div>';
                                            }
                                        }
                                    }
                                    //Fallo: el origen se muestra dentro de la MISMA celda (no se abren mas columnas)
                                    if ($result['status'] !== true) {
                                        echo '<div style="border: solid #af3434;border-width: 1px;border-radius: 0.375rem;background-color: #f9cccc;"><code>'.htmlspecialchars((string) ($result['source'] ?? ''), ENT_QUOTES, 'UTF-8').'</code></div>';
                                    }
                                    //
                                    if($i==3){echo '</td></tr>'; $i = 0;}

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

<?php
function norm_text($Text){
    //Datos buscados
    $healthy = array('&lt;', '&gt;', '&quot;', '&amp;nbsp;');
    $yummy   = array('<', '>', '"', '&nbsp;');
    //devolver
    return str_replace($healthy, $yummy, $Text);

}
