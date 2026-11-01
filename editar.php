
<?php
// Archivo: editar.php
// Permite consultar y modificar un reporte existente.

require_once "conexion.php";

// Función para mostrar información de manera segura
function escapar($texto) {
    return htmlspecialchars(
        (string)($texto ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

// Obtener el ID del reporte
$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

$reporte = null;
$error = "";

// Verificar que el ID sea válido
if (!$id || $id < 1) {

    $error = "No se seleccionó un reporte válido.";

} else {

    try {

        // Conectarse a la base de datos
        $conexion = conectarBD();

        // Buscar el reporte seleccionado
        $sql = "SELECT
                    id,
                    nombre,
                    laboratorio,
                    equipo,
                    fecha_falla,
                    hora_falla,
                    tipo_falla,
                    otro_problema
                FROM reportes
                WHERE id = :id";

        // Preparar consulta segura
        $sentencia = $conexion->prepare($sql);

        // Ejecutar consulta
        $sentencia->execute([
            ":id" => $id
        ]);

        // Obtener la información
        $reporte = $sentencia->fetch(
            PDO::FETCH_ASSOC
        );

        if (!$reporte) {
            $error = "No se encontró el reporte solicitado.";
        }

    } catch (Throwable $e) {

        $error = "No fue posible consultar el reporte. "
               . "La conexión a la base de datos "
               . "está pendiente de configuración.";
    }
}

// Fecha actual de Colombia
$zonaHoraria = new DateTimeZone("America/Bogota");

$fechaMaxima = (new DateTimeImmutable(
    "now",
    $zonaHoraria
))->format("Y-m-d");

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Modificar reporte de falla</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #f1f5f9;
            color: #172033;
            padding: 30px 15px;
        }

        .contenedor {
            max-width: 650px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            color: #174a87;
            margin-bottom: 12px;
            font-weight: bold;
        }

        .descripcion {
            text-align: center;
            color: #555;
            line-height: 1.5;
            margin-bottom: 25px;
        }

        .formulario {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px #00000015;
        }

        .form-label {
            font-weight: bold;
        }

        .asterisco {
            color: #c62828;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #174a87;
            box-shadow: 0 0 0 3px #174a8726;
        }

        .btn-principal {
            background-color: #174a87;
            border-color: #174a87;
            color: white;
        }

        .btn-principal:hover {
            background-color: #103964;
            border-color: #103964;
            color: white;
        }

        .ayuda {
            color: #526174;
            font-size: 13px;
            margin-top: 5px;
        }

        [hidden] {
            display: none !important;
        }

    </style>

</head>

<body>

    <main class="contenedor">

        <h1>Modificar reporte de falla</h1>

        <p class="descripcion">
            Actualice la información del reporte
            seleccionado y guarde los cambios.
        </p>

        <?php if ($error !== ""): ?>

            <!-- Mensaje de error -->

            <div class="alert alert-warning" role="alert">

                <?= escapar($error) ?>

            </div>

            <a href="listar.php"
               class="btn btn-secondary">

                Volver a reportes

            </a>

        <?php else: ?>

            <!-- Formulario de edición -->

            <form
                id="formulario_editar"
                class="formulario"
                action="actualizar.php"
                method="POST">

                <!-- ID del reporte -->

                <input
                    type="hidden"
                    name="id"
                    value="<?= escapar($reporte['id']) ?>">

                <p class="text-secondary">

                    Editando reporte número:

                    <strong>
                        <?= escapar($reporte['id']) ?>
                    </strong>

                </p>

                <p class="ayuda">
                    Los campos marcados con
                    <span class="asterisco">*</span>
                    son obligatorios.
                </p>


                <!-- Nombre del estudiante -->

                <div class="mb-3">

                    <label for="nombre" class="form-label">

                        Nombre del estudiante
                        <span class="asterisco">*</span>

                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="nombre"
                        name="nombre"
                        maxlength="100"
                        value="<?= escapar($reporte['nombre']) ?>"
                        placeholder="Nombre y apellido"
                        required>

                </div>


                <!-- Laboratorio -->

                <div class="mb-3">

                    <label for="laboratorio" class="form-label">

                        Laboratorio
                        <span class="asterisco">*</span>

                    </label>

                    <select
                        id="laboratorio"
                        name="laboratorio"
                        class="form-select"
                        required>

                        <option value="">
                            Seleccione un laboratorio
                        </option>

                        <?php for ($i = 1; $i <= 3; $i++): ?>

                            <option
                                value="<?= $i ?>"
                                <?= (string)$reporte["laboratorio"] === (string)$i
                                    ? "selected"
                                    : "" ?>>

                                Laboratorio <?= $i ?>

                            </option>

                        <?php endfor; ?>

                    </select>

                </div>


                <!-- Código del computador -->

                <div class="mb-3">

                    <label for="equipo" class="form-label">

                        Código del computador
                        <span class="asterisco">*</span>

                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="equipo"
                        name="equipo"
                        maxlength="30"
                        value="<?= escapar($reporte['equipo']) ?>"
                        placeholder="Ejemplo: PC-012"
                        required>

                </div>


                <!-- Fecha de la falla -->

                <div class="mb-3">

                    <label for="fecha_falla" class="form-label">

                        Fecha en que se detectó la falla
                        <span class="asterisco">*</span>

                    </label>

                    <input
                        type="date"
                        class="form-control"
                        id="fecha_falla"
                        name="fecha_falla"
                        max="<?= escapar($fechaMaxima) ?>"
                        value="<?= escapar($reporte['fecha_falla']) ?>"
                        required>

                    <div class="ayuda">
                        Seleccione la fecha correcta.
                    </div>

                </div>


                <!-- Hora de la falla -->

                <div class="mb-3">

                    <label for="hora_falla" class="form-label">

                        Hora en que se detectó la falla
                        <span class="asterisco">*</span>

                    </label>

                    <input
                        type="time"
                        class="form-control"
                        id="hora_falla"
                        name="hora_falla"
                        value="<?= escapar(substr($reporte['hora_falla'], 0, 5)) ?>"
                        required>

                </div>


                <!-- Tipo de falla -->

                <div class="mb-3">

                    <label for="falla" class="form-label">

                        Tipo de falla
                        <span class="asterisco">*</span>

                    </label>

                    <select
                        id="falla"
                        name="falla"
                        class="form-select"
                        required>

                        <?php

                        $tiposFalla = [
                            "encendido" => "No enciende",
                            "internet" => "Sin conexión a internet",
                            "teclado" => "Problema con el teclado",
                            "pantalla" => "Problema con la pantalla",
                            "software" => "Error de software",
                            "otro" => "Otro problema"
                        ];

                        foreach ($tiposFalla as $valor => $texto):

                        ?>

                            <option
                                value="<?= escapar($valor) ?>"
                                <?= $reporte["tipo_falla"] === $valor
                                    ? "selected"
                                    : "" ?>>

                                <?= escapar($texto) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Descripción para Otro problema -->

                <div
                    id="campo_otro"
                    class="mb-3"
                    hidden>

                    <label for="otro_problema" class="form-label">

                        Describa qué otro problema
                        presenta el computador

                        <span class="asterisco">*</span>

                    </label>

                    <textarea
                        id="otro_problema"
                        name="otro_problema"
                        class="form-control"
                        rows="4"
                        placeholder="Describa el problema"><?= escapar($reporte["otro_problema"]) ?></textarea>

                </div>


                <!-- Botones -->

                <div class="d-flex gap-2 flex-wrap mt-4">

                    <button
                        type="submit"
                        class="btn btn-principal">

                        Guardar cambios

                    </button>

                    <a
                        href="listar.php"
                        class="btn btn-secondary">

                        Cancelar

                    </a>

                </div>

            </form>

        <?php endif; ?>

    </main>


    <!-- JavaScript -->

    <script>

        const falla = document.getElementById("falla");

        const campoOtro = document.getElementById("campo_otro");

        const otroProblema = document.getElementById("otro_problema");

        const formulario = document.getElementById("formulario_editar");


        // Mostrar el campo adicional cuando sea necesario

        function actualizarCampoOtro() {

            const esOtro = falla.value === "otro";

            campoOtro.hidden = !esOtro;

            otroProblema.required = esOtro;

            if (!esOtro) {
                otroProblema.value = "";
            }

        }


        // Solo activar eventos si existe el formulario

        if (formulario) {

            falla.addEventListener(
                "change",
                actualizarCampoOtro
            );

            actualizarCampoOtro();


            // Evitar fechas y horas futuras

            formulario.addEventListener(
                "submit",
                function(evento) {

                    const fecha = document.getElementById(
                        "fecha_falla"
                    ).value;

                    const hora = document.getElementById(
                        "hora_falla"
                    ).value;

                    const fechaHora = new Date(
                        fecha + "T" + hora
                    );

                    if (
                        Number.isNaN(fechaHora.getTime()) ||
                        fechaHora > new Date()
                    ) {

                        evento.preventDefault();

                        alert(
                            "La fecha y hora de la falla no pueden ser futuras."
                        );

                        return;
                    }

                    if (
                        falla.value === "otro" &&
                        !otroProblema.value.trim()
                    ) {

                        evento.preventDefault();

                        alert(
                            "Debe describir el otro problema."
                        );

                        otroProblema.focus();

                    }

                }
            );

        }

    </script>

</body>
</html>
