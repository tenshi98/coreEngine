<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<section class="section dashboard">
    <div class="row">

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <div class="row">
                <?php
/************************************/
$type_1 = 5;//Codigo PHP
$code_1 = '
// Inicializamos variables
$message = "";
$name    = "";

// Verificamos si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Obtenemos y limpiamos el dato
    $name = trim($_POST["name"] ?? "");

    // Validamos que no esté vacío
    if ($name === "") {
        $message = "⚠️ El nombre es obligatorio.";
    } else {
        $message = "✅ Hola, " . htmlspecialchars($name) . "!";
    }
}';
/************************************/
$type_2 = 3;//Codigo JavaScript
$code_2 = '
function sendData() {
    const name = document.getElementById("name").value;

    fetch("api.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "name=" + encodeURIComponent(name)
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
    })
    .catch(error => console.error(error));
}';
/************************************/
$type_3 = 4;//Codigo Python
$code_3 = '
# archivo: app.py

def saludar(nombre):
    if not nombre.strip():
        return "El nombre es obligatorio"
    return f"Hola {nombre}"

if __name__ == "__main__":
    nombre = input("Ingresa tu nombre: ")
    mensaje = saludar(nombre)
    print(mensaje)';
?>
                <div class="col-sm-4 col-xl-4">
                    <?php $data['Fnc_WidgetsCommon']->widget_code_block($type_1, $code_1, $BASE); ?>
                </div>
                <div class="col-sm-4 col-xl-4">
                    <?php $data['Fnc_WidgetsCommon']->widget_code_block($type_2, $code_2, $BASE); ?>
                </div>
                <div class="col-sm-4 col-xl-4">
                    <?php $data['Fnc_WidgetsCommon']->widget_code_block($type_3, $code_3, $BASE); ?>
                </div>

            </div>
        </div>

    </div>
</section>
