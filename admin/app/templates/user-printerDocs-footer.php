

        </div>
        <div class="cs-invoice_btns cs-hide_print">
          <a href="javascript:window.print()" class="cs-invoice_btn cs-color1">
            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
              <path d="M384 368h24a40.12 40.12 0 0040-40V168a40.12 40.12 0 00-40-40H104a40.12 40.12 0 00-40 40v160a40.12 40.12 0 0040 40h24" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32"></path>
              <rect x="128" y="240" width="256" height="208" rx="24.32" ry="24.32" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32"></rect>
              <path d="M384 128v-24a40.12 40.12 0 00-40-40H168a40.12 40.12 0 00-40 40v24" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32"></path>
              <circle cx="392" cy="184" r="24"></circle>
            </svg>
            <span>Imprimir</span>
          </a>
          <button id="download_btn" class="cs-invoice_btn cs-color2">
            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
              <title>Descargar</title>
              <path d="M336 176h40a40 40 0 0140 40v208a40 40 0 01-40 40H136a40 40 0 01-40-40V216a40 40 0 0140-40h40" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32"></path>
              <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M176 272l80 80 80-80M256 48v288"></path>
            </svg>
            <span>Descargar</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Archivos de la Plataforma -->
    <script type="text/javascript" src="<?php echo $BASE.'/vendor/printerDocs/jquery.min.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo $BASE.'/vendor/printerDocs/jspdf.min.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo $BASE.'/vendor/printerDocs/html2canvas.min.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo $BASE.'/vendor/printerDocs/main.js'; ?>"></script>

  </body>

</html>
