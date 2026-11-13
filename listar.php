<?php
// Conexión a la base de datos
session_start();
require_once __DIR__ . '/conexion.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function escapar($dato): string {
    return htmlspecialchars((string) ($dato ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$tiposFalla = [
    'encendido' => 'No enciende',
    'internet' => 'Sin conexión a internet',
    'teclado' => 'Problema con el teclado',
    'pantalla' => 'Problema con la pantalla',
    'software' => 'Error de software',
    'otro' => 'Otro problema'
];

$busqueda = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$reportes = [];
$errorConsulta = false;

// Consultar los reportes en MariaDB
try {
    $conexion = conectarBD();
    $sql = 'SELECT id, nombre, laboratorio, equipo, fecha_falla, hora_falla,
                   tipo_falla, otro_problema FROM reportes';
    $parametros = [];

    if ($busqueda !== '') {
        $sql .= " WHERE CAST(id AS CHAR) LIKE ?
                   OR nombre LIKE ?
                   OR CONCAT('Laboratorio ', laboratorio) LIKE ?
                   OR equipo LIKE ?
                   OR tipo_falla LIKE ?
                   OR otro_problema LIKE ?";
        $parametros = array_fill(0, 6, '%' . $busqueda . '%');
    }

    $sql .= ' ORDER BY id DESC';
    $consulta = $conexion->prepare($sql);
    $consulta->execute($parametros);
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
    <title>Consulta de reportes de fallas</title>
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
            max-width: 1250px;
            margin: 0 auto;
            padding: 35px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        }
        h1 {
            color: #174a87;
            font-weight: 700;
            text-align: center;
            margin-bottom: 12px;
        }
        .descripcion {
            max-width: 900px;
            margin: 0 auto 28px;
            color: #526174;
            text-align: center;
            line-height: 1.6;
        }
        .navegacion {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }
        .btn-principal {
            background-color: #174a87;
            border: 1px solid #174a87;
            color: white;
        }
        .btn-principal:hover, .btn-principal:focus {
            background-color: #103964;
            border-color: #103964;
            color: white;
        }
        .seccion-busqueda {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 22px;
            margin-bottom: 22px;
        }
        .titulo-busqueda {
            color: #174a87;
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .ayuda-busqueda {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .form-control:focus {
            border-color: #174a87;
            box-shadow: 0 0 0 0.2rem rgba(23, 74, 135, 0.12);
        }
        .controles-busqueda {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .controles-busqueda input { flex: 1; min-width: 200px; }
        .resumen { margin: 22px 0 15px; color: #526174; font-size: 14px; }
        .tabla-contenedor { width: 100%; overflow-x: auto; }
        .table { margin-bottom: 0; }
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
        .acciones { display: flex; align-items: center; gap: 7px; flex-wrap: nowrap; }
        .acciones .btn { white-space: nowrap; }
        .btn-ver {
            background-color: #e8f1ff;
            border: 1px solid #174a87;
            color: #174a87;
        }
        .btn-ver:hover { background-color: #174a87; color: white; }
        .mensaje-vacio {
            padding: 22px;
            background-color: #e8f1ff;
            border-radius: 8px;
            color: #174a87;
        }
        .modal-header { background-color: #174a87; color: white; }
        .modal-header .btn-close { filter: invert(1); }
        .detalle-etiqueta {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .detalle-valor { font-size: 16px; color: #172033; overflow-wrap: anywhere; }
        .detalle-grupo { padding: 12px 0; border-bottom: 1px solid #e2e8f0; }
        .detalle-grupo:last-child { border-bottom: none; }
        @media (max-width: 600px) {
            body { padding: 20px 12px; }
            .contenedor { padding: 22px 18px; }
            h1 { font-size: 26px; }
            .navegacion, .controles-busqueda { flex-direction: column; }
            .navegacion .btn, .controles-busqueda input, .controles-busqueda .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<main class="contenedor">
    <h1>Consulta de reportes de fallas</h1>
    <p class="descripcion">
        Consulte los reportes de fallas registrados en los computadores de los
        laboratorios universitarios. Puede buscar información, visualizar detalles,
        corregir registros y eliminar reportes cuando sea necesario.
    </p>

    <nav class="navegacion" aria-label="Navegación de reportes">
        <a href="index.php" class="btn btn-secondary">Volver al inicio</a>
        <a href="nuevo.php" class="btn btn-principal">Crear nuevo reporte</a>
    </nav>

    <?php if (!$errorConsulta): ?>
        <?php if (($_GET['guardado'] ?? '') === '1'): ?>
            <div class="alert alert-success" role="status">El reporte fue registrado correctamente.</div>
        <?php endif; ?>
        <?php if (($_GET['actualizado'] ?? '') === '1'): ?>
            <div class="alert alert-success" role="status">El reporte fue actualizado correctamente.</div>
        <?php endif; ?>
        <?php if (($_GET['eliminado'] ?? '') === '1'): ?>
            <div class="alert alert-success" role="status">El reporte fue eliminado correctamente.</div>
        <?php endif; ?>

        <section class="seccion-busqueda">
            <h2 class="titulo-busqueda">Buscar un reporte</h2>
            <p class="ayuda-busqueda">
                Puede buscar por número de reporte, estudiante o docente,
                laboratorio, computador o tipo de falla.
            </p>
            <form action="listar.php" method="GET" role="search">
                <label for="buscarReporte" class="form-label fw-semibold">
                    Escriba lo que desea buscar
                </label>
                <div class="controles-busqueda">
                    <input type="search" id="buscarReporte" name="q"
                           class="form-control" maxlength="100"
                           placeholder="Ejemplo: PC-05, Laboratorio 2 o teclado"
                           value="<?= escapar($busqueda) ?>">
                    <button type="submit" class="btn btn-principal">Buscar</button>
                    <a href="listar.php" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($errorConsulta): ?>
        <div class="alert alert-danger" role="alert">
            <strong>No fue posible consultar los reportes.</strong>
            <p class="mb-0 mt-2">Compruebe que la aplicación y la base de datos estén funcionando correctamente.</p>
        </div>
    <?php elseif (empty($reportes)): ?>
        <div class="mensaje-vacio" role="status">
            <?php if ($busqueda !== ''): ?>
                No se encontraron reportes que coincidan con:
                <strong><?= escapar($busqueda) ?></strong>.
                <p class="mb-0 mt-2">Revise la información escrita o utilice el botón Limpiar.</p>
            <?php else: ?>
                Todavía no hay reportes registrados.
                <p class="mb-0 mt-2">Puede crear el primer reporte desde el formulario.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p class="resumen" role="status">
            <strong><?= $busqueda !== '' ? 'Resultados encontrados:' : 'Reportes registrados:' ?></strong>
            <?= count($reportes) ?>
        </p>

        <!-- Mostrar los reportes en una tabla -->
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
                        $tipo = $tiposFalla[$reporte['tipo_falla']] ?? $reporte['tipo_falla'];
                        $otro = trim((string) ($reporte['otro_problema'] ?? ''));
                        if ($otro === '') {
                            $otro = 'No aplica';
                        }
                        ?>
                        <tr>
                            <td><?= $id ?></td>
                            <td><?= escapar($reporte['nombre']) ?></td>
                            <td>Laboratorio <?= escapar($reporte['laboratorio']) ?></td>
                            <td><?= escapar($reporte['equipo']) ?></td>
                            <td><?= escapar($reporte['fecha_falla']) ?></td>
                            <td><?= escapar($reporte['hora_falla']) ?></td>
                            <td><?= escapar($tipo) ?></td>
                            <td><?= escapar($otro) ?></td>
                            <td>
                                <div class="acciones">
                                    <button type="button" class="btn btn-ver btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#modalDetalle"
                                            data-id="<?= $id ?>"
                                            data-nombre="<?= escapar($reporte['nombre']) ?>"
                                            data-laboratorio="<?= escapar($reporte['laboratorio']) ?>"
                                            data-equipo="<?= escapar($reporte['equipo']) ?>"
                                            data-fecha="<?= escapar($reporte['fecha_falla']) ?>"
                                            data-hora="<?= escapar($reporte['hora_falla']) ?>"
                                            data-tipo="<?= escapar($tipo) ?>"
                                            data-otro="<?= escapar($otro) ?>">Ver</button>

                                    <a href="editar.php?id=<?= $id ?>" class="btn btn-warning btn-sm">Editar</a>

                                    <button type="button" class="btn btn-danger btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#modalEliminar"
                                            data-id="<?= $id ?>">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Ver detalles del reporte -->
        <div class="modal fade" id="modalDetalle" tabindex="-1"
             aria-labelledby="tituloModalDetalle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="tituloModalDetalle">Detalles del reporte de falla</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <div class="row">
                            <div class="col-md-6 detalle-grupo">
                                <div class="detalle-etiqueta">Número del reporte</div>
                                <div class="detalle-valor" id="detalle-id"></div>
                            </div>
                            <div class="col-md-6 detalle-grupo">
                                <div class="detalle-etiqueta">Estudiante o docente</div>
                                <div class="detalle-valor" id="detalle-nombre"></div>
                            </div>
                            <div class="col-md-6 detalle-grupo">
                                <div class="detalle-etiqueta">Laboratorio</div>
                                <div class="detalle-valor" id="detalle-laboratorio"></div>
                            </div>
                            <div class="col-md-6 detalle-grupo">
                                <div class="detalle-etiqueta">Computador</div>
                                <div class="detalle-valor" id="detalle-equipo"></div>
                            </div>
                            <div class="col-md-6 detalle-grupo">
                                <div class="detalle-etiqueta">Fecha de la falla</div>
                                <div class="detalle-valor" id="detalle-fecha"></div>
                            </div>
                            <div class="col-md-6 detalle-grupo">
                                <div class="detalle-etiqueta">Hora de la falla</div>
                                <div class="detalle-valor" id="detalle-hora"></div>
                            </div>
                            <div class="col-12 detalle-grupo">
                                <div class="detalle-etiqueta">Tipo de falla</div>
                                <div class="detalle-valor" id="detalle-tipo"></div>
                            </div>
                            <div class="col-12 detalle-grupo">
                                <div class="detalle-etiqueta">Descripción de otro problema</div>
                                <div class="detalle-valor" id="detalle-otro"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmar eliminación -->
        <div class="modal fade" id="modalEliminar" tabindex="-1"
             aria-labelledby="tituloModalEliminar" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="eliminar.php" method="POST" class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h2 class="modal-title fs-5" id="tituloModalEliminar">Confirmar eliminación</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">
                            ¿Deseas eliminar el reporte número
                            <strong id="numeroReporteEliminar"></strong>?
                        </p>
                        <p class="text-danger mb-0">Esta acción es definitiva y no se puede deshacer.</p>
                    </div>
                    <input type="hidden" name="id" id="idReporteEliminar">
                    <input type="hidden" name="csrf_token" value="<?= escapar($_SESSION['csrf_token']) ?>">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Sí, eliminar reporte</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Mostrar detalles del reporte
    const modalDetalle = document.getElementById('modalDetalle');
    if (modalDetalle) {
        modalDetalle.addEventListener('show.bs.modal', function (evento) {
            const boton = evento.relatedTarget;
            if (!boton) return;

            const datos = {
                'detalle-id': boton.dataset.id,
                'detalle-nombre': boton.dataset.nombre,
                'detalle-laboratorio': 'Laboratorio ' + boton.dataset.laboratorio,
                'detalle-equipo': boton.dataset.equipo,
                'detalle-fecha': boton.dataset.fecha,
                'detalle-hora': boton.dataset.hora,
                'detalle-tipo': boton.dataset.tipo,
                'detalle-otro': boton.dataset.otro
            };
            for (const [idCampo, valor] of Object.entries(datos)) {
                document.getElementById(idCampo).textContent = valor ?? '';
            }
        });
    }

    // Seleccionar el reporte que se va a eliminar
    const modalEliminar = document.getElementById('modalEliminar');
    if (modalEliminar) {
        modalEliminar.addEventListener('show.bs.modal', function (evento) {
            const boton = evento.relatedTarget;
            const id = boton ? boton.dataset.id : '';
            document.getElementById('numeroReporteEliminar').textContent = id;
            document.getElementById('idReporteEliminar').value = id;
        });
    }
</script>
</body>
</html>
