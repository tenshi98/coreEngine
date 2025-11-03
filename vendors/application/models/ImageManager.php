<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class ImageManager{

    public function __construct(){
        //nada
    }

	/******************************************************************************/
	public function optimize($Options){
        /*
		*=================================================     Detalles    =================================================
		*
		* Permite optimizar el formato de la imagen
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de las opciones
        *    $Options = [
		*		'FileOriginal' => 'file.jpg'  //Nombre original del archivo
		*		'FileNew'      => 'new_file'  //Nombre deseado
		*		'rutaArchivo'  => 'img/'      //Ruta al archivo
		*		'Formato'      => 'png'       //Formato
		*		'quality'      => ''          //Calidad
		*		'max_width'    => '640'       //Ancho maximo deseado
		*		'max_height'   => ''          //Alto maximo deseado
		*		'IMGFilter'    => 'grises'    //Filtro
		*		'IMGRotate'    => '25'        //Rotacion
		*		'IMGFlip'      => ''          //Voltear
		*	];
		*
		*=================================================    Parametros   =================================================
		* @input   array    $Options   Parametros
		* @return  bool
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		//Definicion de Valores
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

        /********************** Si todo esta ok **********************/
		//Se obtienen los datos de la imagen
		$FileInfo    = getimagesize($rutaArchivo.'/'.$FileOriginal);
		$file_width  = $FileInfo[0];
		$file_height = $FileInfo[1];
		$file_type   = $FileInfo["mime"];

		//Leemos la información del archivo a memoria
		switch ($file_type) {
			case 'image/jpg':  $TempIMG = imagecreatefromjpeg($rutaArchivo.'/'.$FileOriginal); break;
			case 'image/jpeg': $TempIMG = imagecreatefromjpeg($rutaArchivo.'/'.$FileOriginal); break;
			case 'image/gif':  $TempIMG = imagecreatefromgif($rutaArchivo.'/'.$FileOriginal); break;
			case 'image/png':  $TempIMG = imagecreatefrompng($rutaArchivo.'/'.$FileOriginal); break;
		}

		//Se establece el nuevo tamaño
		if ($file_width > $file_height) {
			$newwidth  = min($file_width, $max_width);
			$divisor   = $file_width / $newwidth;
			$newheight = floor( $file_height / $divisor);
		}elseif ($file_width < $file_height) {
			$newheight = min($file_height, $max_height);
			$divisor   = $file_height / $newheight;
			$newwidth  = floor( $file_width / $divisor );
		}

		//Se aplican filtros
		if($IMGFilter!=''){
			switch ($IMGFilter) {
				case 'negativo':   imagefilter($TempIMG, IMG_FILTER_NEGATE);break;
				case 'grises':     imagefilter($TempIMG, IMG_FILTER_GRAYSCALE);break;
				case 'rojo':       imagefilter($TempIMG, IMG_FILTER_COLORIZE,100,0,0);break;
				case 'verde':      imagefilter($TempIMG, IMG_FILTER_COLORIZE,0,100,0);break;
				case 'azul':       imagefilter($TempIMG, IMG_FILTER_COLORIZE,0,0,100);break;
				case 'amarillo':   imagefilter($TempIMG, IMG_FILTER_COLORIZE,100,100,-100);break;
				case 'brillo':     imagefilter($TempIMG, IMG_FILTER_BRIGHTNESS,50);break;
				case 'contraste':  imagefilter($TempIMG, IMG_FILTER_CONTRAST,20);break;
				case 'sepia':      imagefilter($TempIMG, IMG_FILTER_GRAYSCALE); imagefilter($TempIMG, IMG_FILTER_COLORIZE,100,70,50);break;
				case 'contornos':  imagefilter($TempIMG, IMG_FILTER_EDGEDETECT);break;
				case 'emboss':     imagefilter($TempIMG, IMG_FILTER_EMBOSS);break;
				case 'selectivo':  imagefilter($TempIMG, IMG_FILTER_SELECTIVE_BLUR);break;
				case 'removal':    imagefilter($TempIMG, IMG_FILTER_MEAN_REMOVAL);break;
				case 'suavizado':  imagefilter($TempIMG, IMG_FILTER_SMOOTH,-7);break;
				case 'pixelado':   imagefilter($TempIMG, IMG_FILTER_PIXELATE, 10, true);break;
				case 'gauss':
					for ($i=0; $i < 40 ; $i++) {
						imagefilter($TempIMG, IMG_FILTER_GAUSSIAN_BLUR);
					}
					imagefilter($TempIMG, IMG_FILTER_SMOOTH,-7);
					break;

			}
		}

		//Se rota imagen
		if($IMGRotate!=0){
			$TempIMG = imagerotate($TempIMG, $IMGRotate, 0);
		}

		//Se aplican filtros
		if($IMGFlip!=''){
			switch ($IMGFlip) {
				case 'vertical':   imageflip($TempIMG, IMG_FLIP_VERTICAL); break;
				case 'horizontal': imageflip($TempIMG, IMG_FLIP_HORIZONTAL); break;
			}
		}


		//Se da el formato destino
		switch ($Formato) {
			/*******************************/
			case 'jpg':
			case 'jpeg':
				//Se escala la imagen
				//$TempIMG = imagescale($TempIMG, $newwidth, $newheight);
				//Crear el lienzo
				$lienzo = imagecreatetruecolor($newwidth, $newheight);
				//Se copia la imagen dentro del lienzo
				imagecopyresampled($lienzo, $TempIMG, 0, 0, 0, 0, $newwidth, $newheight, $file_width, $file_height);
				//Se genera la imagen
				imagejpeg($lienzo, $rutaArchivo."/".$FileNew.".jpg", $quality);
				break;
			/*******************************/
			case 'gif':
				//Crear el lienzo
				$lienzo = imagecreatetruecolor($newwidth, $newheight);
				//Se copia la imagen dentro del lienzo
				imagecopyresampled($lienzo, $TempIMG, 0, 0, 0, 0, $newwidth, $newheight, $file_width, $file_height);
				//Se genera la imagen
				imagegif($lienzo, $rutaArchivo."/".$FileNew.".gif");
				break;
			/*******************************/
			case 'png':
				//Crear el lienzo
				$lienzo = imagecreatetruecolor($newwidth, $newheight);
				//Preparamos el lienzo para el canal alfa
				imagecolortransparent($lienzo, imagecolorallocatealpha($lienzo, 0, 0, 0, 127));
				imagealphablending($lienzo, false);
				imagesavealpha($lienzo, true);
				//Se copia la imagen dentro del lienzo
				imagecopyresampled($lienzo, $TempIMG, 0, 0, 0, 0, $newwidth, $newheight, $file_width, $file_height);
				//Se genera la imagen
				imagepng($lienzo, $rutaArchivo."/".$FileNew.".png");
				break;
		}

		//se eliminan las imagenes de la memoria
		imagedestroy($TempIMG);

		//Devuelvo respuesta
		return true;
	}

}




