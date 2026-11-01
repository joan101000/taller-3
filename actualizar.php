
<?php

// Archivo: actualizar.php
// Actualiza un reporte de fallas existente.

require_once "conexion.php";

// Solo aceptar datos enviados por POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    exit("Acceso no permitido.");

}
// Recibir el ID del reporte
$id = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);
// Recibir los datos modificados
$nombre = trim($_POST["nombre"] ?? "");

$laboratorio = $_POST["laboratorio"] ?? "";

$equipo = trim($_POST["equipo"] ?? "");

$fecha = $_POST["fecha_falla"] ?? "";

$hora = $_POST["hora_falla"] ?? "";

$tipoFalla = $_POST["falla"] ?? "";

$otroProblema = trim(
    $_POST["otro_problema"] ?? ""
);

// VALIDAR EL ID

if (!$id || $id < 1) {

    http_response_code(400);

    exit("El reporte seleccionado no es válido.");

}
// VALIDAR LOS CAMPOS OBLIGATORIOS

if (
    $nombre === "" ||
    $laboratorio === "" ||
    $equipo === "" ||
    $fecha === "" ||
    $hora === "" ||
    $tipoFalla === ""
) {

    http_response_code(400);

    exit("Debe completar todos los campos obligatorios.");

}
// VALIDAR LA LONGITUD DE LOS DATOS

if (
    strlen($nombre) > 100 ||
    strlen($laboratorio) > 50 ||
    strlen($equipo) > 30
) {

    http_response_code(400);

    exit("Uno de los campos supera la longitud permitida.");

}
// VALIDAR EL LABORATORIO

$laboratoriosValidos = ["1", "2", "3"];

if (!in_array(
    $laboratorio,
    $laboratoriosValidos,
    true
)) {

    http_response_code(400);

    exit("Seleccione un laboratorio válido.");

}
// VALIDAR EL TIPO DE FALLA

$tiposValidos = [
    "encendido",
    "internet",
    "teclado",
    "pantalla",
    "software",
    "otro"
];

if (!in_array(
    $tipoFalla,
    $tiposValidos,
    true
)) {

    http_response_code(400);

    exit("Seleccione un tipo de falla válido.");

}
// VALIDAR LA OPCIÓN OTRO PROBLEMA

if (
    $tipoFalla === "otro" &&
    $otroProblema === ""
) {

    http_response_code(400);

    exit("Debe describir el otro problema.");

}

// Si no seleccionó Otro, no guardar texto adicional
if ($tipoFalla !== "otro") {

    $otroProblema = "";

}
// VALIDAR FECHA Y HORA

$zonaHoraria = new DateTimeZone(
    "America/Bogota"
);

$fechaHora = DateTimeImmutable::createFromFormat(
    "!Y-m-d H:i",
    $fecha . " " . $hora,
    $zonaHoraria
);

if (
    !$fechaHora ||
    $fechaHora->format("Y-m-d H:i") !==
        $fecha . " " . $hora ||
    $fechaHora > new DateTimeImmutable(
        "now",
        $zonaHoraria
    )
) {

    http_response_code(400);

    exit(
        "La fecha y la hora ingresadas no son válidas."
    );

}
// ACTUALIZAR EL REPORTE EN LA BASE DE DATOS

try {

    // Conectarse a la base de datos
    $conexion = conectarBD();


    // Comprobar que el reporte exista
    $consulta = $conexion->prepare(
        "SELECT id FROM reportes WHERE id = :id"
    );

    $consulta->execute([
        ":id" => $id
    ]);

    if (!$consulta->fetch(PDO::FETCH_ASSOC)) {

        http_response_code(404);

        exit("No se encontró el reporte solicitado.");

    }
    // Instrucción SQL para actualizar
    $sql = "UPDATE reportes SET

                nombre = :nombre,

                laboratorio = :laboratorio,

                equipo = :equipo,

                fecha_falla = :fecha,

                hora_falla = :hora,

                tipo_falla = :tipo_falla,

                otro_problema = :otro_problema

            WHERE id = :id";
    // Preparar la consulta
    $sentencia = $conexion->prepare($sql);
    // Ejecutar la actualización
    $sentencia->execute([

        ":nombre" => $nombre,

        ":laboratorio" => $laboratorio,

        ":equipo" => $equipo,

        ":fecha" => $fecha,

        ":hora" => $hora,

        ":tipo_falla" => $tipoFalla,

        ":otro_problema" => $otroProblema,

        ":id" => $id

    ]);
    // Regresar al listado de reportes
    header("Location: listar.php?actualizado=1");

    exit;
} catch (Throwable $error) {

    http_response_code(503);

    exit(
        "No fue posible actualizar el reporte. " .
        "La conexión a la base de datos " .
        "todavía no está configurada."
    );

}

?>
