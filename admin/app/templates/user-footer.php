    </main>

    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer">
      <div class="copyright">
        <?php
        $CompanyName  = !empty($data['UserData']['Sistema_Nombre'])
                        ? $data['UserData']['Sistema_Nombre']
                        : 'Nombre Compañia';
        ?>
        &copy; <strong><span><?php echo $CompanyName; ?></span></strong>. Todos los derechos reservados
      </div>
      <div class="credits">
        <?php echo ConfigAPP::SOFTWARE['CompanyCredits']; ?>
      </div>
    </footer><!-- End Footer -->

    <div id="PDloader"></div>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script type="text/javascript" src="<?php echo $BASE.'/vendor/bootstrap/js/bootstrap.bundle.min.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo $BASE.'/vendor/apexcharts/apexcharts.min.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo $BASE.'/vendor/chart.js/chart.umd.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo $BASE.'/vendor/echarts/echarts.min.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo $BASE.'/vendor/simple-datatables/simple-datatables.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo $BASE.'/vendor/sweetalert2/sweetalert2.min.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo $BASE.'/vendor/tinymce/tinymce.min.js'; ?>"></script>

    <!-- Bootstrap Colorpicker -->
    <script type="text/javascript" src="<?php echo $BASE.'/vendor/bootstrap_colorpicker/dist/js/bootstrap-colorpicker.min.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo $BASE.'/vendor/bootstrap_colorpicker/dist/js/bootstrap-colorpicker-plus.min.js'; ?>"></script>

    <!-- Archivos de la Plataforma -->
    <script type="text/javascript" src="<?php echo $BASE.'/js/main.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo $BASE.'/js/functions.js'; ?>"></script>

    <!-- Upload And Crop Image -->
    <link rel="stylesheet" type="text/css" href="<?php echo $BASE.'/vendor/upload_and_crop_image/croppie.css'; ?>">
    <script type="text/javascript" src="<?php echo $BASE.'/vendor/upload_and_crop_image/croppie.js'; ?>"></script>

    <script>
      //ajustar tamaño de todos los textarea
			autosize(document.querySelectorAll('textarea'));
    </script>

  </body>

</html>
