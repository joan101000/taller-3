<?php
session_start();
require_once __DIR__ . '/conexion.php';

function mostrarError(string $mensaje, int $estado = 400): void
{
    http_response_code($estado);
    $mensaje = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>No se pudo eliminar el reporte</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <style>
            body {
                min-height: 100vh;
                padding: 30px 15px;
                background-color: #f1f5f9;
                color: #172033;
                font-family: Arial, sans-serif;
            }

            .contenedor {
                max-width: 580px;
                margin: 70px auto;
                padding: 30px;
                background-color: white;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            }

            h1 {
                font-size: 24px;
                color: #b42318;
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
            <h1>No se pudo eliminar el reporte</h1>

            <p><?= $mensaje ?></p>

            <a href="listar.php" class="btn btn-principal">
                Volver a los reportes
            </a>
        </main>
    </body>
    </html>
    <?php
    exit;
}

// La eliminación solo se permite desde un formulario POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mostrarError(
        'Debes seleccionar un reporte desde la lista para eliminarlo.',
        405
    );
}

// Verificar que la solicitud provenga del formulario.
$tokenRecibido = $_POST['csrf_token'] ?? '';
$tokenSesion = $_SESSION['csrf_token'] ?? '';

if (
    !is_string($tokenRecibido) ||
    !is_string($tokenSesion) ||
    $tokenSesion === '' ||
    !hash_equals($tokenSesion, $tokenRecibido)
) {
    mostrarError(
        'La solicitud no es válida. Regresa a la lista e inténtalo nuevamente.',
        403
    );
}

// Identificar el reporte seleccionado.
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    mostrarError('El reporte seleccionado no es válido.', 400);
}

try {
    $conexion = conectarBD();

    $consulta = $conexion->prepare(
        'DELETE FROM reportes WHERE id = :id'
    );

    $consulta->execute([
        ':id' => $id
    ]);

    // Verificar que realmente se haya eliminado un registro.
    if ($consulta->rowCount() === 0) {
        mostrarError(
            'No se encontró el reporte. Es posible que ya haya sido eliminado.',
            404
        );
    }

    // Renovar el token para las siguientes operaciones.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    header('Location: listar.php?eliminado=1', true, 303);
    exit;

} catch (Throwable $error) {
    error_log('Error al eliminar el reporte: ' . $error->getMessage());

    mostrarError(
        'Ocurrió un problema al eliminar el reporte. Inténtalo nuevamente.',
        503
    );
}
