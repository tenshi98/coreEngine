class Popover {

    constructor(element){

        // Elemento HTML que dispara el popover
        this.el = element;

        // Obtiene el título desde el atributo data-title
        // Si no existe, usa string vacío
        this.title = element.dataset.title || "";

        // Obtiene el contenido desde el atributo data-content
        this.content = element.dataset.content || "";

        // Define si el contenido debe interpretarse como HTML
        // data-html="true" → habilita HTML dentro del popover
        this.html = element.dataset.html === "true";

        // Define la posición del popover respecto al elemento
        // Valores posibles: top | bottom | left | right
        // Si no se define, usa "top"
        this.placement = element.dataset.placement || "top";

        // Obtiene el contenido desde el atributo data-extraclass
        this.extraclass = element.dataset.extraclass || "";

        // Crea la estructura HTML del popover
        this.create();

        // Evento click sobre el elemento que activa el popover
        element.addEventListener("click", e=>{

            // Evita que el click se propague al documento
            // (esto evita que el popover se cierre inmediatamente)
            e.stopPropagation();

            // Cierra cualquier otro popover abierto
            Popover.closeAll();

            // Alterna entre mostrar u ocultar el popover
            this.toggle();
        });

        // Recalcular posición al hacer scroll o resize
        window.addEventListener("scroll", ()=> this.updatePosition());
        window.addEventListener("resize", ()=> this.updatePosition());

    }

    create(){

        // Crea el contenedor principal del popover
        this.pop = document.createElement("div");
        this.pop.className = "popover_ex " + this.extraclass;

        // Crea el elemento visual que representa la flecha del popover
        this.arrow = document.createElement("div");
        this.arrow.className = "popover_ex-arrow";

        // Si existe un título, se crea el header
        if(this.title){

            this.header = document.createElement("div");
            this.header.className = "popover_ex-header";

            // Inserta el texto del título
            this.header.innerText = this.title;

            // Agrega el header al popover
            this.pop.appendChild(this.header);
        }

        // Crea el cuerpo del popover
        this.body = document.createElement("div");
        this.body.className = "popover_ex-body";

        // Si html=true interpreta el contenido como HTML
        if(this.html){
            this.body.innerHTML = this.content;
        }else{
            // Si no, lo muestra como texto plano
            this.body.innerText = this.content;
        }

        // Agrega el contenido al popover
        this.pop.appendChild(this.body);

        // Agrega la flecha
        this.pop.appendChild(this.arrow);

        // Inserta el popover dentro del body del documento
        document.getElementById("popover_ex_div").appendChild(this.pop);

    }

    calculatePosition(){

        // Obtiene las dimensiones y posición del elemento activador
        const rect = this.el.getBoundingClientRect();

        // Obtiene las dimensiones del popover
        const popRect = this.pop.getBoundingClientRect();

        // Variables donde se calculará la posición final
        let top, left;

        // Posicionamiento según el valor de placement
        switch(this.placement){

            case "top":

                // Posiciona el popover arriba del elemento
                top = rect.top - window.scrollY - popRect.height - 10;

                // Centra horizontalmente el popover respecto al elemento
                left = rect.left + (rect.width / 2) - (popRect.width / 2);

                // Ajusta posición de la flecha
                this.arrow.style.bottom = "-6px";
                this.arrow.style.top    = "auto";
                this.arrow.style.left   = "calc(50% - 6px)";
                // Se agrega estilo para la flecha
                this.pop.classList.add("arrow_top");
                break;

            case "bottom":

                // Posiciona el popover debajo del elemento
                top = rect.bottom + window.scrollY + 10;

                // Centrado horizontal
                left = rect.left + (rect.width / 2) - (popRect.width / 2);

                // Posición de la flecha
                this.arrow.style.top  = "-6px";
                this.arrow.style.left = "calc(50% - 6px)";
                // Se agrega estilo para la flecha
                this.pop.classList.add("arrow_bottom");
                break;

            case "left":

                // Posiciona el popover a la izquierda del elemento
                top = rect.top + (rect.height / 2) - (popRect.height / 2);
                left = rect.left - popRect.width - 10;

                // Posición de la flecha
                this.arrow.style.right = "-6px";
                this.arrow.style.left  = "auto";
                this.arrow.style.top   = "calc(50% - 6px)";
                // Se agrega estilo para la flecha
                this.pop.classList.add("arrow_left");
                break;

            case "right":

                // Posiciona el popover a la derecha del elemento
                top = rect.top + (rect.height / 2) - (popRect.height / 2);
                left = rect.right + 10;

                // Posición de la flecha
                this.arrow.style.left = "-6px";
                this.arrow.style.top  = "calc(50% - 6px)";
                // Se agrega estilo para la flecha
                this.pop.classList.add("arrow_right");
                break;

        }

        // ----- AUTO REPOSICIONAMIENTO -----

        const padding = 10;

        // Evita salir por la izquierda
        if(left < padding){
            left = padding;
        }

        // Evita salir por la derecha
        if(left + popRect.width > window.innerWidth){
            left = window.innerWidth - popRect.width - padding;
        }

        // Evita salir por arriba
        if(top < padding){
            top = rect.bottom + window.scrollY + 10;
        }

        // Evita salir por abajo
        if(top + popRect.height > window.innerHeight + window.scrollY){
            top = rect.top + window.scrollY - popRect.height - 10;
        }

        return {top,left};

    }

    updatePosition(){

        // Solo recalcula si está visible
        if(!this.pop.classList.contains("show")) return;

        const pos = this.calculatePosition();

        this.pop.style.top  = pos.top + "px";
        this.pop.style.left = pos.left + "px";

    }

    show(){

        // Se calcula la posicion
        const pos = this.calculatePosition();

        // Aplica la posición calculada
        this.pop.style.top  = pos.top + "px";
        this.pop.style.left = pos.left + "px";

        // Muestra el popover agregando la clase show
        this.pop.classList.add("show");

    }

    hide(){

        // Oculta el popover eliminando la clase show
        this.pop.classList.remove("show");
        //Se quitan las clases de la flecha
        this.pop.classList.remove("arrow_top");
        this.pop.classList.remove("arrow_bottom");
        this.pop.classList.remove("arrow_left");
        this.pop.classList.remove("arrow_right");

    }

    toggle(){

        // Si el popover ya está visible lo oculta
        if(this.pop.classList.contains("show")){
            this.hide();
        }else{
            // Si está oculto lo muestra
            this.show();
        }

    }

    static closeAll(){

        // Busca todos los popovers visibles en el documento
        // y elimina la clase show para cerrarlos
        document.querySelectorAll(".popover_ex.show")
        .forEach(p => p.classList.remove("show"));

    }

}