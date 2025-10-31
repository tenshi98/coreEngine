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
                                    <th scope="col">Test</th>
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
                                    //
                                    if($i==1){
                                        echo '<tr><td>';
                                        $dearch = $result['text'];
                                    }else{
                                        echo '<br/>';
                                    }
                                    echo '<strong>' . ($result['status'] === true ? 'Pasa' : 'Falla') . '</strong> ';
                                    echo $result['text'];
                                    if(isset($result['extraText'])&&$result['extraText']!=''){
                                        $resultado = $data['Fnc_DataText']->buscarPalabraYExtraer($dearch, 'Devuelve ');
                                        if ($resultado !== false) {
                                            if($result['extraText']==substr($resultado['extraido'], 0, -1)){
                                                $SubData = 'OK: '.$result['extraText'];
                                                echo '<br/><div style="border: solid #dee2e6;border-width: 1px;border-radius: 0.375rem;background-color: #f8f9fa;"><code>'.$SubData.'</code></div>';
                                            }elseif(substr($resultado['extraido'], 0, -1)!='asd'){
                                                $SubData = 'Hay diferencias:'.$result['extraText'].' - '.substr($resultado['extraido'], 0, -1);
                                                echo '<br/><div style="border: solid #af3434;border-width: 1px;border-radius: 0.375rem;background-color: #f9cccc;"><code>'.$SubData.'</code></div>';
                                            }
                                        }
                                    }
                                    if ($result['status']!==true){
                                        echo '<br/><div style="border: solid #af3434;border-width: 1px;border-radius: 0.375rem;background-color: #f9cccc;"><code>'.$result['source'].'</code></div>';
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