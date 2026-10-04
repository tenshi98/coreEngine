/*******************************************************************************/
//Permite el ingreso solo de caracteres predefinidos
function soloLetras(e){
	//defino el evento
	key = e.keyCode || e.which;
	tecla = String.fromCharCode(key).toLowerCase();
	//caracteres permitidos
	letras = " áéíóúabcdefghijklmnñopqrstuvwxyz1234567890-_?¿°()=/+-,.<>:;*@";
	//caracteres especiales permitidos
	const especiales = [];
	especiales.push(8);   //backspace
	especiales.push(37);  //left arrow
	especiales.push(38);  //up arrow
	especiales.push(39);  //right arrow
	especiales.push(40);  //down arrow
	especiales.push(45);  //insert
	especiales.push(46);  //delete
	especiales.push(96);  //numpad 0
	especiales.push(97);  //numpad 1
	especiales.push(98);  //numpad 2
	especiales.push(99);  //numpad 3
	especiales.push(100); //numpad 4
	especiales.push(101); //numpad 5
	especiales.push(102); //numpad 6
	especiales.push(103); //numpad 7
	especiales.push(104); //numpad 8
	especiales.push(105); //numpad 9
	especiales.push(109); //numpad guion
	especiales.push(173); //guion
	especiales.push(189); //guion - otros navegadores
	especiales.push(110); //numpad punto
	especiales.push(172); //°
	especiales.push(188); //comma
	especiales.push(190); //punto
	especiales.push(192); //letra ñ
	especiales.push(221); //¿
	especiales.push(222); //?

	tecla_especial = false;
	//reviso si alguna de las acciones es un caracter especial
	for(var i in especiales){
		if(key == especiales[i]){
			tecla_especial = true;
			break;
		}
	}
	//impido la ejecucion
	if(letras.indexOf(tecla)==-1 && !tecla_especial){
		return false;
	}
}
/*******************************************************************************/
//Permite el ingreso solo de caracteres predefinidos
function soloLetrasTextArea(e){
	//defino el evento
	key = e.keyCode || e.which;
	tecla = String.fromCharCode(key).toLowerCase();
	//caracteres permitidos
	letras = " áéíóúabcdefghijklmnñopqrstuvwxyz1234567890-_?¿°()=/+-,.<>:;*@";
	//caracteres especiales permitidos
	const especiales = [];
	especiales.push(8);   //backspace
	especiales.push(13);  //enter
	especiales.push(37);  //left arrow
	especiales.push(38);  //up arrow
	especiales.push(39);  //right arrow
	especiales.push(40);  //down arrow
	especiales.push(45);  //insert
	especiales.push(46);  //delete
	especiales.push(96);  //numpad 0
	especiales.push(97);  //numpad 1
	especiales.push(98);  //numpad 2
	especiales.push(99);  //numpad 3
	especiales.push(100); //numpad 4
	especiales.push(101); //numpad 5
	especiales.push(102); //numpad 6
	especiales.push(103); //numpad 7
	especiales.push(104); //numpad 8
	especiales.push(105); //numpad 9
	especiales.push(109); //numpad guion
	especiales.push(173); //guion
	especiales.push(189); //guion - otros navegadores
	especiales.push(110); //numpad punto
	especiales.push(172); //°
	especiales.push(188); //comma
	especiales.push(190); //punto
	especiales.push(192); //letra ñ
	especiales.push(221); //¿
	especiales.push(222); //?

	tecla_especial = false;
	//reviso si alguna de las acciones es un caracter especial
	for(var i in especiales){
		if(key == especiales[i]){
			tecla_especial = true;
			break;
		}
	}
	//impido la ejecucion
	if(letras.indexOf(tecla)==-1 && !tecla_especial){
		return false;
	}
}
/*******************************************************************************/
//Permite el ingreso solo de numeros enteros o decimales, positivos o negativos
function soloNumeroRealRacional(e){
	//defino el evento
	key = e.keyCode || e.which;
	tecla = String.fromCharCode(key).toLowerCase();
	//caracteres permitidos
	letras = "1234567890-.";
	//caracteres especiales permitidos
	const especiales = [];
	especiales.push(8);   //backspace
	especiales.push(37);  //left arrow
	especiales.push(38);  //up arrow
	especiales.push(39);  //right arrow
	especiales.push(40);  //down arrow
	especiales.push(45);  //insert
	especiales.push(46);  //delete
	especiales.push(96);  //numpad 0
	especiales.push(97);  //numpad 1
	especiales.push(98);  //numpad 2
	especiales.push(99);  //numpad 3
	especiales.push(100); //numpad 4
	especiales.push(101); //numpad 5
	especiales.push(102); //numpad 6
	especiales.push(103); //numpad 7
	especiales.push(104); //numpad 8
	especiales.push(105); //numpad 9
	especiales.push(109); //numpad guion
	especiales.push(110); //numpad punto
	especiales.push(173); //guion
	especiales.push(189); //guion - otros navegadores
	especiales.push(190); //punto

	tecla_especial = false;
	//reviso si alguna de las acciones es un caracter especial
	for(var i in especiales){
		if(key == especiales[i]){
			tecla_especial = true;
			break;
		}
	}
	//impido la ejecucion
	if(letras.indexOf(tecla)==-1 && !tecla_especial){
		return false;
	}
}
/*******************************************************************************/
//Permite el ingreso solo de numeros enteros, positivos o negativos
function soloNumeroNaturalReal(e){
	//defino el evento
	key = e.keyCode || e.which;
	tecla = String.fromCharCode(key).toLowerCase();
	//caracteres permitidos
	letras = "1234567890-";
	//caracteres especiales permitidos
	const especiales = [];
	especiales.push(8);   //backspace
	especiales.push(37);  //left arrow
	especiales.push(38);  //up arrow
	especiales.push(39);  //right arrow
	especiales.push(40);  //down arrow
	especiales.push(45);  //insert
	especiales.push(46);  //delete
	especiales.push(96);  //numpad 0
	especiales.push(97);  //numpad 1
	especiales.push(98);  //numpad 2
	especiales.push(99);  //numpad 3
	especiales.push(100); //numpad 4
	especiales.push(101); //numpad 5
	especiales.push(102); //numpad 6
	especiales.push(103); //numpad 7
	especiales.push(104); //numpad 8
	especiales.push(105); //numpad 9
	especiales.push(109); //numpad guion
	especiales.push(173); //guion
	especiales.push(189); //guion - otros navegadores

	tecla_especial = false;
	//reviso si alguna de las acciones es un caracter especial
	for(var i in especiales){
		if(key == especiales[i]){
			tecla_especial = true;
			break;
		}
	}
	//impido la ejecucion
	if(letras.indexOf(tecla)==-1 && !tecla_especial){
		return false;
	}
}
/*******************************************************************************/
//Permite el ingreso solo de numeros enteros, positivos
function soloNumeroNatural(e){
	//defino el evento
	key = e.keyCode || e.which;
	tecla = String.fromCharCode(key).toLowerCase();
	//caracteres permitidos
	letras = "1234567890";
	//caracteres especiales permitidos
	const especiales = [];
	especiales.push(8);   //backspace
	especiales.push(37);  //left arrow
	especiales.push(38);  //up arrow
	especiales.push(39);  //right arrow
	especiales.push(40);  //down arrow
	especiales.push(45);  //insert
	especiales.push(46);  //delete
	especiales.push(96);  //numpad 0
	especiales.push(97);  //numpad 1
	especiales.push(98);  //numpad 2
	especiales.push(99);  //numpad 3
	especiales.push(100); //numpad 4
	especiales.push(101); //numpad 5
	especiales.push(102); //numpad 6
	especiales.push(103); //numpad 7
	especiales.push(104); //numpad 8
	especiales.push(105); //numpad 9

	tecla_especial = false;
	//reviso si alguna de las acciones es un caracter especial
	for(var i in especiales){
		if(key == especiales[i]){
			tecla_especial = true;
			break;
		}
	}
	//impido la ejecucion
	if(letras.indexOf(tecla)==-1 && !tecla_especial){
		return false;
	}
}
/*******************************************************************************/
//Permite el ingreso solo de numeros enteros, positivos
function soloRut(e){
	//Info en https://css-tricks.com/snippets/javascript/javascript-keycodes/
	//defino el evento
	key = e.keyCode || e.which;
	tecla = String.fromCharCode(key).toLowerCase();
	//caracteres permitidos
	letras = "kK1234567890-.";
	//caracteres especiales permitidos
	const especiales = [];
	especiales.push(8);   //backspace
	especiales.push(37);  //left arrow
	especiales.push(38);  //up arrow
	especiales.push(39);  //right arrow
	especiales.push(40);  //down arrow
	especiales.push(45);  //insert
	especiales.push(46);  //delete
	especiales.push(96);  //numpad 0
	especiales.push(97);  //numpad 1
	especiales.push(98);  //numpad 2
	especiales.push(99);  //numpad 3
	especiales.push(100); //numpad 4
	especiales.push(101); //numpad 5
	especiales.push(102); //numpad 6
	especiales.push(103); //numpad 7
	especiales.push(104); //numpad 8
	especiales.push(105); //numpad 9
	especiales.push(109); //numpad guion
	especiales.push(110); //numpad punto
	especiales.push(173); //guion
	especiales.push(189); //guion - otros navegadores
	especiales.push(190); //punto

	tecla_especial = false;
	//reviso si alguna de las acciones es un caracter especial
	for(var i in especiales){
		if(key == especiales[i]){
			tecla_especial = true;
			break;
		}
	}
	//impido la ejecucion
	if(letras.indexOf(tecla)==-1 && !tecla_especial){
		return false;
	}
}
