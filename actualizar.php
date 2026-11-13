<?php
// Conexión a la base de datos
require_once __DIR__ . '/conexion.php';

function mostrarError(string $mensaje, int $estado = 400, int $id = 0): void
{
    http_response_code($estado);

    $mensaje = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
    $enlace = $id > 0 ? 'editar.php?id=' . $id : 'listar.php';
    $textoEnlace = $id > 0 ? 'Volver a editar' : 'Volver a reportes';
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>No se pudo actualizar el reporte</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <style>
            body {
                background-color: #f1f5f9;
                color: #172033;
                font-family: Arial, sans-serif;
                min-height: 100vh;
                padding: 30px 15px;
            }

            .contenedor {
                max-width: 580px;
                margin: 60px auto;
            }

            .tarjeta {
                background-color: white;
                border-radius: 12px;
                padding: 30px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            }

            h1 {
                color: #b42318;
                font-size: 24px;
                margin-bottom: 15px;
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
        </style>
    </head>

    <body>
        <main class="contenedor">
            <div class="tarjeta">

                <h1>No se pudo actualizar el reporte</h1>

                <p><?= $mensaje ?></p>

                <a href="<?= $enlace ?>" class="btn btn-principal">
                    <?= $textoEnlace ?>
                </a>

                <a href="listar.php" class="btn btn-outline-secondary">
                    Consultar reportes
                </a>

            </div>
        </main>
    </body>
    </html>
    <?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mostrarError('Debes realizar la actualización desde el formulario.', 405);
}

// Recibir los datos del formulario
function obtenerCampo(string $campo): string
{
    $valor = $_POST[$campo] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    mostrarError('No se ha seleccionado un reporte válido.');
}

$nombre = obtenerCampo('nombre');
$laboratorio = obtenerCampo('laboratorio');
$equipo = obtenerCampo('equipo');
$fecha = obtenerCampo('fecha_falla');
$hora = obtenerCampo('hora_falla');
$tipoFalla = obtenerCampo('falla');
$otroProblema = obtenerCampo('otro_problema');

// Validar la información
if (
    $nombre === '' ||
    $laboratorio === '' ||
    $equipo === '' ||
    $fecha === '' ||
    $hora === '' ||
    $tipoFalla === ''
) {
    mostrarError('Completa todos los campos obligatorios.', 400, $id);
}

if (strlen($nombre) > 100 || strlen($equipo) > 30) {
    mostrarError(
        'El nombre o el código del computador supera la longitud permitida.',
        400,
        $id
    );
}

if (!in_array($laboratorio, ['1', '2', '3'], true)) {
    mostrarError('Selecciona un laboratorio válido.', 400, $id);
}

$fallasPermitidas = [
    'encendido',
    'internet',
    'teclado',
    'pantalla',
    'software',
    'otro'
];

if (!in_array($tipoFalla, $fallasPermitidas, true)) {
    mostrarError('Selecciona un tipo de falla válido.', 400, $id);
}

if ($tipoFalla === 'otro') {
    if ($otroProblema === '') {
        mostrarError('Describe el problema del computador.', 400, $id);
    }
} else {
    $otroProblema = '';
}

$zonaHoraria = new DateTimeZone('America/Bogota');

$fechaHora = DateTimeImmutable::createFromFormat(
    '!Y-m-d H:i',
    "$fecha $hora",
    $zonaHoraria
);

if (
    !$fechaHora ||
    $fechaHora->format('Y-m-d H:i') !== "$fecha $hora" ||
    $fechaHora > new DateTimeImmutable('now', $zonaHoraria)
) {
    mostrarError(
        'La fecha y la hora ingresadas no son válidas.',
        400,
        $id
    );
}

// Actualizar el reporte en MariaDB
try {
    $conexion = conectarBD();

    $consulta = $conexion->prepare(
        'SELECT id FROM reportes WHERE id = :id'
    );

    $consulta->execute([':id' => $id]);

    if (!$consulta->fetchColumn()) {
        mostrarError('El reporte que intentas modificar no existe.', 404);
    }

    $sql = 'UPDATE reportes
            SET nombre = :nombre,
                laboratorio = :laboratorio,
                equipo = :equipo,
                fecha_falla = :fecha,
                hora_falla = :hora,
                tipo_falla = :falla,
                otro_problema = :otro
            WHERE id = :id';

    $consulta = $conexion->prepare($sql);

    $consulta->execute([
        ':nombre' => $nombre,
        ':laboratorio' => $laboratorio,
        ':equipo' => $equipo,
        ':fecha' => $fecha,
        ':hora' => $hora,
        ':falla' => $tipoFalla,
        ':otro' => $otroProblema,
        ':id' => $id
    ]);

    header('Location: listar.php?actualizado=1', true, 303);
    exit;

} catch (Throwable $error) {
    error_log('Error al actualizar el reporte: ' . $error->getMessage());

    mostrarError(
        'Ocurrió un problema al guardar los cambios. Inténtalo nuevamente.',
        503,
        $id
    );
}
