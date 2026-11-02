<?php
session_start();
require_once __DIR__ . '/conexion.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function escapar($dato): string
{
    return htmlspecialchars(
        (string) ($dato ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

$tiposFalla = [
    'encendido' => 'No enciende',
    'internet' => 'Sin conexión a internet',
    'teclado' => 'Problema con el teclado',
    'pantalla' => 'Problema con la pantalla',
    'software' => 'Error de software',
    'otro' => 'Otro problema'
];

$reportes = [];
$errorConsulta = false;

try {
    $conexion = conectarBD();

    $consulta = $conexion->query(
        'SELECT id, nombre, laboratorio, equipo,
                fecha_falla, hora_falla, tipo_falla, otro_problema
         FROM reportes
         ORDER BY id DESC'
    );

    $reportes = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $error) {
    $errorConsulta = true;
    error_log('Error al consultar reportes: ' . $error->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar reportes de fallas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 30px 15px;
            background-color: #f1f5f9;
            color: #172033;
            font-family: Arial, sans-serif;
        }

        .contenedor {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        h1 {
            color: #174a87;
            font-weight: 700;
            text-align: center;
            margin-bottom: 12px;
        }

        .descripcion {
            max-width: 900px;
            margin: 0 auto 25px;
            color: #526174;
            text-align: center;
            line-height: 1.6;
        }

        .btn-principal {
            background-color: #174a87;
            border: 1px solid #174a87;
            color: white;
        }

        .btn-principal:hover,
        .btn-principal:focus {
            background-color: #103964;
            border-color: #103964;
            color: white;
        }

        .resumen {
            margin: 25px 0 15px;
            color: #526174;
            font-size: 14px;
        }

        .tabla-contenedor {
            width: 100%;
            overflow-x: auto;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #174a87;
            color: white;
            padding: 12px;
            white-space: nowrap;
            vertical-align: middle;
        }

        .table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            overflow-wrap: anywhere;
        }

        .acciones {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .acciones form {
            margin: 0;
        }

        .acciones .btn {
            white-space: nowrap;
        }

        .mensaje-vacio {
            padding: 20px;
            background-color: #e8f1ff;
            border-radius: 8px;
            color: #174a87;
        }

        @media (max-width: 600px) {
            body {
                padding: 20px 12px;
            }

            .contenedor {
                padding: 22px 18px;
            }

            h1 {
                font-size: 26px;
            }

            .navegacion {
                flex-direction: column;
            }

            .navegacion .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <main class="contenedor">

        <h1>Consulta de reportes de fallas</h1>

        <p class="descripcion">
            Aquí puedes consultar los problemas registrados en los
            computadores de los laboratorios universitarios.
            También puedes corregir la información o eliminar un
            reporte que ya no sea necesario.
        </p>

        <div class="navegacion d-flex gap-2 flex-wrap mb-4">
            <a href="index.php" class="btn btn-secondary">
                Volver al inicio
            </a>

            <a href="nuevo.php" class="btn btn-principal">
                Crear nuevo reporte
            </a>
        </div>

        <?php if (!$errorConsulta): ?>

            <?php if (($_GET['actualizado'] ?? '') === '1'): ?>
                <div class="alert alert-success" role="status">
                    El reporte fue actualizado correctamente.
                </div>
            <?php endif; ?>

            <?php if (($_GET['eliminado'] ?? '') === '1'): ?>
                <div class="alert alert-success" role="status">
                    El reporte fue eliminado correctamente.
                </div>
            <?php endif; ?>

        <?php endif; ?>

        <?php if ($errorConsulta): ?>

            <div class="alert alert-danger" role="alert">
                No fue posible consultar los reportes en este momento.
                Verifica que el servidor y la base de datos estén funcionando.
            </div>

        <?php elseif (empty($reportes)): ?>

            <div class="mensaje-vacio" role="status">
                Todavía no hay reportes registrados.
                Puedes crear el primero desde el formulario.
            </div>

        <?php else: ?>

            <p class="resumen">
                <strong>Reportes registrados:</strong>
                <?= count($reportes) ?>
            </p>

            <div class="tabla-contenedor">

                <table class="table table-bordered table-striped table-hover">

                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Estudiante o docente</th>
                            <th scope="col">Laboratorio</th>
                            <th scope="col">Computador</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Hora</th>
                            <th scope="col">Tipo de falla</th>
                            <th scope="col">Otro problema</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($reportes as $reporte): ?>

                            <?php
                            $id = (int) $reporte['id'];

                            $tipo = $tiposFalla[$reporte['tipo_falla']]
                                ?? $reporte['tipo_falla'];

                            $otro = trim(
                                (string) ($reporte['otro_problema'] ?? '')
                            );

                            if ($otro === '') {
                                $otro = 'No aplica';
                            }
                            ?>

                            <tr>
                                <td><?= $id ?></td>

                                <td>
                                    <?= escapar($reporte['nombre']) ?>
                                </td>

                                <td>
                                    Laboratorio
                                    <?= escapar($reporte['laboratorio']) ?>
                                </td>

                                <td>
                                    <?= escapar($reporte['equipo']) ?>
                                </td>

                                <td>
                                    <?= escapar($reporte['fecha_falla']) ?>
                                </td>

                                <td>
                                    <?= escapar($reporte['hora_falla']) ?>
                                </td>

                                <td>
                                    <?= escapar($tipo) ?>
                                </td>

                                <td>
                                    <?= escapar($otro) ?>
                                </td>

                                <td>
                                    <div class="acciones">

                                        <a href="editar.php?id=<?= $id ?>"
                                           class="btn btn-warning btn-sm">
                                            Editar
                                        </a>

                                        <form action="eliminar.php"
                                              method="POST"
                                              onsubmit="return confirm('¿Deseas eliminar el reporte número <?= $id ?>? Esta acción es definitiva y no se puede deshacer.');">

                                            <input type="hidden"
                                                   name="id"
                                                   value="<?= $id ?>">

                                            <input type="hidden"
                                                   name="csrf_token"
                                                   value="<?= escapar($_SESSION['csrf_token']) ?>">

                                            <button type="submit"
                                                    class="btn btn-danger btn-sm">
                                                Eliminar
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

        <?php endif; ?>

    </main>

</body>
</html>
