
<?php

// Archivo: guardar.php
// Recibe, valida y registra los reportes de fallas.

require_once __DIR__ . "/conexion.php";

// Solo aceptar información enviada mediante POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Acceso no permitido.");
}

// Recibir los datos del formulario.
$nombre = trim($_POST["nombre"] ?? "");
$laboratorio = $_POST["laboratorio"] ?? "";
$equipo = trim($_POST["equipo"] ?? "");
$fecha = $_POST["fecha_falla"] ?? "";
$hora = $_POST["hora_falla"] ?? "";
$tipoFalla = $_POST["falla"] ?? "";
$otroProblema = trim($_POST["otro_problema"] ?? "");

// Comprobar los campos obligatorios.
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

// Comprobar las longitudes.
if (
    strlen($nombre) > 100 ||
    strlen($equipo) > 30
) {
    http_response_code(400);
    exit("Uno de los campos supera la longitud permitida.");
}

// Validar laboratorio.
if (!in_array($laboratorio, ["1", "2", "3"], true)) {
    http_response_code(400);
    exit("Seleccione un laboratorio válido.");
}

// Validar tipo de falla.
$tiposValidos = [
    "encendido",
    "internet",
    "teclado",
    "pantalla",
    "software",
    "otro"
];

if (!in_array($tipoFalla, $tiposValidos, true)) {
    http_response_code(400);
    exit("Seleccione un tipo de falla válido.");
}

// Validar la opción Otro problema.
if ($tipoFalla === "otro" && $otroProblema === "") {
    http_response_code(400);
    exit("Debe describir el otro problema.");
}

if ($tipoFalla !== "otro") {
    $otroProblema = "";
}

// Validar fecha y hora.
$zona = new DateTimeZone("America/Bogota");

$fechaHora = DateTimeImmutable::createFromFormat(
    "!Y-m-d H:i",
    $fecha . " " . $hora,
    $zona
);

if (
    !$fechaHora ||
    $fechaHora->format("Y-m-d H:i") !== $fecha . " " . $hora ||
    $fechaHora > new DateTimeImmutable("now", $zona)
) {
    http_response_code(400);
    exit("La fecha y la hora no son válidas.");
}

// Registrar el reporte en la base de datos.
try {

    $conexion = conectarBD();

    $sql = "INSERT INTO reportes (
                nombre,
                laboratorio,
                equipo,
                fecha_falla,
                hora_falla,
                tipo_falla,
                otro_problema
            ) VALUES (
                :nombre,
                :laboratorio,
                :equipo,
                :fecha,
                :hora,
                :tipo_falla,
                :otro_problema
            )";

    $sentencia = $conexion->prepare($sql);

    $sentencia->execute([
        ":nombre" => $nombre,
        ":laboratorio" => $laboratorio,
        ":equipo" => $equipo,
        ":fecha" => $fecha,
        ":hora" => $hora,
        ":tipo_falla" => $tipoFalla,
        ":otro_problema" => $otroProblema
    ]);

    header("Location: listar.php?guardado=1");
    exit;

} catch (Throwable $error) {

    http_response_code(503);

    echo "<h2>No fue posible guardar el reporte</h2>";
    echo "<p>La conexión a la base de datos está ";
    echo "pendiente de configuración.</p>";
    echo '<p><a href="nuevo.php">Volver al formulario</a></p>';

    exit;
}
