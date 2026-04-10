<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class ImageManager{

    public function __construct(){
        //nada
    }

	/******************************************************************************/
	/**
	 * Procesa, manipula y optimiza imágenes en el servidor.
	 * * Este método permite realizar una suite completa de transformaciones sobre un archivo de imagen:
	 * cambio de dimensiones manteniendo la relación de aspecto, aplicación de filtros artísticos,
	 * rotación, volteo (flip) y conversión entre formatos (JPG, PNG, GIF), optimizando además
	 * el peso del archivo resultante.
	 *
	 * @param array $Options Configuración de procesamiento (nombres, rutas, dimensiones, filtros, etc.).
	 * @return bool Retorna true tras completar el procesamiento y guardado de la imagen.
	 */
	public function optimize($Options){

		/********************** Definiciones   **********************/
		// Extracción de parámetros con valores por defecto para asegurar la ejecución
		$FileOriginal   = $Options['FileOriginal'];
		$FileNew        = $Options['FileNew'];
		$rutaArchivo    = $Options['rutaArchivo'];
		$Formato        = $Options['Formato'] ?? 'jpeg';
		$quality        = $Options['quality'] ?? 75;
		$max_width      = $Options['max_width'] ?? 640;
		$max_height     = $Options['max_height'] ?? 640;
		$IMGFilter      = $Options['IMGFilter'] ?? '';
		$IMGRotate      = $Options['IMGRotate'] ?? 0;
		$IMGFlip        = $Options['IMGFlip'] ?? '';

		/********************** Lectura de Imagen **********************/
		// Obtiene metadatos físicos del archivo (ancho, alto y tipo MIME)
		$FileInfo    = getimagesize($rutaArchivo.'/'.$FileOriginal);
		$file_width  = $FileInfo[0];
		$file_height = $FileInfo[1];
		$file_type   = $FileInfo["mime"];

		// Crea un recurso de imagen en memoria dependiendo del formato de origen
		switch ($file_type) {
			case 'image/jpg':
			case 'image/jpeg': $TempIMG = imagecreatefromjpeg($rutaArchivo.'/'.$FileOriginal); break;
			case 'image/gif':  $TempIMG = imagecreatefromgif($rutaArchivo.'/'.$FileOriginal); break;
			case 'image/png':  $TempIMG = imagecreatefrompng($rutaArchivo.'/'.$FileOriginal); break;
		}

		/********************** Cálculo de Proporciones **********************/
		// Lógica de redimensionamiento proporcional para evitar deformaciones
		if ($file_width > $file_height) {
			// Paisaje: el ancho manda
			$newwidth  = min($file_width, $max_width);
			$divisor   = $file_width / $newwidth;
			$newheight = floor($file_height / $divisor);
		} else {
			// Retrato o Cuadrada: el alto manda
			$newheight = min($file_height, $max_height);
			$divisor   = $file_height / $newheight;
			$newwidth  = floor($file_width / $divisor);
		}

		/********************** Aplicación de Filtros **********************/
		if($IMGFilter != ''){
			switch ($IMGFilter) {
				case 'negativo':   imagefilter($TempIMG, IMG_FILTER_NEGATE); break;
				case 'grises':     imagefilter($TempIMG, IMG_FILTER_GRAYSCALE); break;
				case 'rojo':       imagefilter($TempIMG, IMG_FILTER_COLORIZE, 100, 0, 0); break;
				case 'verde':      imagefilter($TempIMG, IMG_FILTER_COLORIZE, 0, 100, 0); break;
				case 'azul':       imagefilter($TempIMG, IMG_FILTER_COLORIZE, 0, 0, 100); break;
				case 'amarillo':   imagefilter($TempIMG, IMG_FILTER_COLORIZE, 100, 100, -100); break;
				case 'brillo':     imagefilter($TempIMG, IMG_FILTER_BRIGHTNESS, 50); break;
				case 'contraste':  imagefilter($TempIMG, IMG_FILTER_CONTRAST, 20); break;
				case 'sepia':
					imagefilter($TempIMG, IMG_FILTER_GRAYSCALE);
					imagefilter($TempIMG, IMG_FILTER_COLORIZE, 100, 70, 50);
					break;
				case 'contornos':  imagefilter($TempIMG, IMG_FILTER_EDGEDETECT); break;
				case 'emboss':     imagefilter($TempIMG, IMG_FILTER_EMBOSS); break;
				case 'selectivo':  imagefilter($TempIMG, IMG_FILTER_SELECTIVE_BLUR); break;
				case 'removal':    imagefilter($TempIMG, IMG_FILTER_MEAN_REMOVAL); break;
				case 'suavizado':  imagefilter($TempIMG, IMG_FILTER_SMOOTH, -7); break;
				case 'pixelado':   imagefilter($TempIMG, IMG_FILTER_PIXELATE, 10, true); break;
				case 'gauss':
					// Aplica desenfoque iterativo para lograr un efecto más profundo
					for ($i=0; $i < 40 ; $i++) {
						imagefilter($TempIMG, IMG_FILTER_GAUSSIAN_BLUR);
					}
					imagefilter($TempIMG, IMG_FILTER_SMOOTH, -7);
					break;
			}
		}

		/********************** Transformaciones Físicas **********************/
		// Rotación de la imagen (grados)
		if($IMGRotate != 0){
			$TempIMG = imagerotate($TempIMG, $IMGRotate, 0);
		}

		// Volteo (Mirroring)
		if($IMGFlip != ''){
			switch ($IMGFlip) {
				case 'vertical':   imageflip($TempIMG, IMG_FLIP_VERTICAL); break;
				case 'horizontal': imageflip($TempIMG, IMG_FLIP_HORIZONTAL); break;
			}
		}

		/********************** Renderizado y Guardado **********************/
		// Crea un lienzo nuevo con las dimensiones finales y procesa según formato destino
		switch ($Formato) {
			case 'jpg':
			case 'jpeg':
				$lienzo = imagecreatetruecolor($newwidth, $newheight);
				imagecopyresampled($lienzo, $TempIMG, 0, 0, 0, 0, $newwidth, $newheight, $file_width, $file_height);
				imagejpeg($lienzo, $rutaArchivo."/".$FileNew.".jpg", $quality);
				break;

			case 'gif':
				$lienzo = imagecreatetruecolor($newwidth, $newheight);
				imagecopyresampled($lienzo, $TempIMG, 0, 0, 0, 0, $newwidth, $newheight, $file_width, $file_height);
				imagegif($lienzo, $rutaArchivo."/".$FileNew.".gif");
				break;

			case 'png':
				$lienzo = imagecreatetruecolor($newwidth, $newheight);
				// Configuración especial para preservar la transparencia (canal alfa) en PNG
				imagecolortransparent($lienzo, imagecolorallocatealpha($lienzo, 0, 0, 0, 127));
				imagealphablending($lienzo, false);
				imagesavealpha($lienzo, true);
				imagecopyresampled($lienzo, $TempIMG, 0, 0, 0, 0, $newwidth, $newheight, $file_width, $file_height);
				imagepng($lienzo, $rutaArchivo."/".$FileNew.".png");
				break;
		}

		/********************** Limpieza de Memoria **********************/
		// Libera los recursos de imagen para evitar fugas de memoria en el servidor
		imagedestroy($TempIMG);
		if(isset($lienzo)) { imagedestroy($lienzo); }

		return true;
	}

}




