<?php
// Iniciar la sesión y cargar la conexión
session_start();
require_once __DIR__ . '/conexion.php';

// Mostrar mensajes de confirmación o error
function mostrarMensaje(string $titulo, string $mensaje, bool $correcto = false, int $estado = 200): void
{
    http_response_code($estado);

    $titulo = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
    $mensaje = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $titulo ?></title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <style>
            body {
                background-color: #f1f5f9;
                min-height: 100vh;
            }

            .tarjeta {
                max-width: 580px;
                margin: 80px auto;
                border: 0;
                border-radius: 12px;
            }

            .btn-primary {
                background-color: #174a87;
                border-color: #174a87;
            }

            .btn-primary:hover {
                background-color: #103964;
                border-color: #103964;
            }
        </style>
    </head>
    <body>
        <main class="container">
            <div class="card tarjeta shadow-sm">
                <div class="card-body p-4 text-center">

                    <h1 class="h4 <?= $correcto ? 'text-success' : 'text-danger' ?> mb-3">
                        <?= $titulo ?>
                    </h1>

                    <p class="mb-4"><?= $mensaje ?></p>

                    <?php if ($correcto): ?>
                        <a href="listar.php" class="btn btn-primary">
                            Consultar reportes
                        </a>

                        <a href="nuevo.php" class="btn btn-outline-secondary">
                            Registrar otra falla
                        </a>
                    <?php else: ?>
                        <button type="button" onclick="history.back()" class="btn btn-primary">
                            Volver al formulario
                        </button>

                        <a href="index.php" class="btn btn-outline-secondary">
                            Ir al inicio
                        </a>
                    <?php endif; ?>

                </div>
            </div>
        </main>
    </body>
    </html>
    <?php
    exit;
}

// Mostrar la confirmación después de guardar
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (
        ($_GET['confirmado'] ?? '') !== '1' ||
        empty($_SESSION['reporte_guardado'])
    ) {
        header('Location: index.php', true, 303);
        exit;
    }

    $id = (int) $_SESSION['reporte_guardado'];

    mostrarMensaje(
        'Reporte registrado correctamente',
        "El reporte número $id se guardó en el sistema.",
        true
    );
}

// Comprobar que los datos lleguen desde el formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mostrarMensaje(
        'Solicitud no permitida',
        'Debes registrar la falla desde el formulario.',
        false,
        405
    );
}

// Recibir los datos del formulario
function campo(string $nombre): string
{
    $valor = $_POST[$nombre] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

$nombre = campo('nombre');
$laboratorio = campo('laboratorio');
$equipo = campo('equipo');
$fecha = campo('fecha_falla');
$hora = campo('hora_falla');
$tipoFalla = campo('falla');
$otroProblema = campo('otro_problema');

// Validar los campos obligatorios
if (
    $nombre === '' ||
    $laboratorio === '' ||
    $equipo === '' ||
    $fecha === '' ||
    $hora === '' ||
    $tipoFalla === ''
) {
    mostrarMensaje(
        'Faltan datos',
        'Completa todos los campos obligatorios.',
        false,
        400
    );
}

// Validar que el nombre no contenga números
if (!preg_match("/^[\p{L}]+(?:[ '-][\p{L}]+)*$/u", $nombre)) {
    mostrarMensaje(
        'Nombre inválido',
        'El nombre completo solo debe contener letras, sin números.',
        false,
        400
    );
}

// Revisar la longitud de los datos
if (strlen($nombre) > 100 || strlen($equipo) > 30) {
    mostrarMensaje(
        'Datos demasiado largos',
        'Revisa el nombre y el identificador del computador.',
        false,
        400
    );
}

// Validar el laboratorio seleccionado
if (!in_array($laboratorio, ['1', '2', '3'], true)) {
    mostrarMensaje(
        'Laboratorio inválido',
        'Selecciona uno de los laboratorios disponibles.',
        false,
        400
    );
}

// Validar el tipo de falla
$fallasPermitidas = [
    'encendido',
    'internet',
    'teclado',
    'pantalla',
    'software',
    'otro'
];

if (!in_array($tipoFalla, $fallasPermitidas, true)) {
    mostrarMensaje(
        'Tipo de falla inválido',
        'Selecciona un tipo de falla de la lista.',
        false,
        400
    );
}

// Comprobar la descripción de otro problema
if ($tipoFalla === 'otro' && $otroProblema === '') {
    mostrarMensaje(
        'Falta la descripción',
        'Explica cuál es el problema del computador.',
        false,
        400
    );
}

if ($tipoFalla !== 'otro') {
    $otroProblema = '';
}

// Validar la fecha y hora
$zona = new DateTimeZone('America/Bogota');

$fechaHora = DateTimeImmutable::createFromFormat(
    '!Y-m-d H:i',
    "$fecha $hora",
    $zona
);

if (
    !$fechaHora ||
    $fechaHora->format('Y-m-d H:i') !== "$fecha $hora" ||
    $fechaHora > new DateTimeImmutable('now', $zona)
) {
    mostrarMensaje(
        'Fecha u hora inválida',
        'Revisa la fecha y la hora de la falla.',
        false,
        400
    );
}

// Guardar el reporte en la base de datos
try {
    $conexion = conectarBD();

    $consulta = $conexion->prepare(
        'INSERT INTO reportes
         (nombre, laboratorio, equipo, fecha_falla, hora_falla, tipo_falla, otro_problema)
         VALUES (:nombre, :laboratorio, :equipo, :fecha, :hora, :falla, :otro)'
    );

    $consulta->execute([
        ':nombre' => $nombre,
        ':laboratorio' => $laboratorio,
        ':equipo' => $equipo,
        ':fecha' => $fecha,
        ':hora' => $hora,
        ':falla' => $tipoFalla,
        ':otro' => $otroProblema
    ]);

    $_SESSION['reporte_guardado'] = (int) $conexion->lastInsertId();

    header('Location: guardar.php?confirmado=1', true, 303);
    exit;

} catch (Throwable $error) {
    error_log('Error al guardar el reporte: ' . $error->getMessage());

    mostrarMensaje(
        'No se pudo guardar el reporte',
        'Ocurrió un problema al registrar la información. Inténtalo de nuevo.',
        false,
        503
    );
}
