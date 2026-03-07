    </main>

    <!-- ======= Footer ======= -->
    <div id="footer" class="footer">
      <div class="container">
        <footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
          <div class="copyright col-md-4 d-flex align-items-center">
            <a href="<?php echo $BASE.'/principal'; ?>" class="mb-3 me-2 mb-md-0 text-muted text-decoration-none lh-1">
              <i class="ri-copyright-line"></i>
            </a>
            <span class="company text-muted">
              <?php $CompanyName  = !empty($data['UserData']['Sistema_Nombre']) ? $data['UserData']['Sistema_Nombre'] : 'Nombre Compañia'; ?>
              <strong><span><?php echo $CompanyName; ?></span></strong>. Todos los derechos reservados
            </span>
            <div class="credits">
              <?php echo ConfigAPP::SOFTWARE['CompanyCredits']; ?>
            </div>
          </div>

          <ul class="social-links nav col-md-4 justify-content-end list-unstyled d-flex">
            <?php if($data['UserData']['Social_X']!=''){ ?>          <li><a target="_blank" rel="noopener noreferrer" href="<?php echo $data['UserData']['Social_X']; ?>"         class="twitter text-muted"><i class="bi bi-twitter"></i></a></li><?php } ?>
            <?php if($data['UserData']['Social_Facebook']!=''){ ?>   <li><a target="_blank" rel="noopener noreferrer" href="<?php echo $data['UserData']['Social_Facebook']; ?>"  class="facebook text-muted"><i class="bi bi-facebook"></i></a></li><?php } ?>
            <?php if($data['UserData']['Social_Instagram']!=''){ ?>  <li><a target="_blank" rel="noopener noreferrer" href="<?php echo $data['UserData']['Social_Instagram']; ?>" class="instagram text-muted"><i class="bi bi-instagram"></i></a></li><?php } ?>
            <?php if($data['UserData']['Social_Linkedin']!=''){ ?>   <li><a target="_blank" rel="noopener noreferrer" href="<?php echo $data['UserData']['Social_Linkedin']; ?>"  class="linkedin text-muted"><i class="bi bi-linkedin"></i></a></li><?php } ?>
          </ul>
        </footer>
      </div>
    </div>



    <div id="PDloader"></div>

    <div class="modal fade" id="viewModal-xl" tabindex="-1">
      <div class="modal-dialog modal-xl">
        <div class="modal-content" id="modalContent-xl">

        </div>
      </div>
    </div>

    <div class="modal fade" id="viewModal-lg" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modalContent-lg">

        </div>
      </div>
    </div>

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
