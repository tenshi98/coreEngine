<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsServerSecurity {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	public function getDataSIIindicadores(string $URL): array {
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite Obtener los datos de los indicadores desde el SII
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$ServerWeb->getDataSIIindicadores('https://zeus.sii.cl/admin/rss/sii_ind_rss.xml');
		*
		*=================================================    Parametros   =================================================
		* @input   string   $URL  URL
		* @return  array
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if($URL==''){  return ['success' => false, 'error' => 'No ha ingresado una URL']; }

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
        try {
            //Variables
            $arrData = array();
            //Obtengo los datos
			$Server    = new FunctionsServerWeb();
            $resultado = $Server->obtenerDatosXML($URL);
            //Recorro los datos
            foreach($resultado as $data_lvl1){
                foreach($data_lvl1 as $data_lvl2){
                    //Verifico que el dato exista
                    if($data_lvl2['title']!=''){
                        $arrData[] = [
                            'title'       => $data_lvl2['title'],
                            'link'        => $data_lvl2['link'],
                            'description' => $data_lvl2['description'],
                        ];
                    }
                }
            }
			//Devuelvo
			return ['success' => true, 'data' => $arrData];
        } catch (Exception $e) {
			return ['success' => false, 'error' => $e->getMessage()];
        }
	}

	/************************************************************************************************************/
	public function sendIPtoBlackList(string $IP): array {
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite Obtener los datos de los indicadores desde el SII
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$ServerWeb->getDataSIIindicadores('https://zeus.sii.cl/admin/rss/sii_ind_rss.xml');
		*
		*=================================================    Parametros   =================================================
		* @input   string   $IP  IP a enviar a la lista negra
		* @return  array
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if($IP==''){  return ['success' => false, 'error' => 'No ha ingresado una IP']; }

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
        try {
            //se envian los datos
            $Server    = new FunctionsServerServer();
            $resultado = $Server->tareasServer($IP, 1); //se le indica que se quiere bloquear la ip
			//Devuelvo
			return ['success' => $resultado['success'], 'data' => $resultado['data']];
        } catch (Exception $e) {
			return ['success' => false, 'error' => $e->getMessage()];
        }
	}


}

