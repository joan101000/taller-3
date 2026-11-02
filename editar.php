<?php
require_once __DIR__ . '/conexion.php';

function escapar($valor): string
{
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$reporte = null;
$error = '';

$tiposFalla = [
    'encendido' => 'No enciende',
    'internet' => 'Sin conexión a internet',
    'teclado' => 'Problema con el teclado',
    'pantalla' => 'Problema con la pantalla',
    'software' => 'Error de software',
    'otro' => 'Otro problema'
];

if (!$id || $id < 1) {
    $error = 'No se ha seleccionado un reporte válido.';
} else {
    try {
        $conexion = conectarBD();

        $consulta = $conexion->prepare(
            'SELECT id, nombre, laboratorio, equipo,
                    fecha_falla, hora_falla, tipo_falla, otro_problema
             FROM reportes
             WHERE id = :id'
        );

        $consulta->execute([':id' => $id]);
        $reporte = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$reporte) {
            $error = 'El reporte solicitado no existe.';
        }

    } catch (Throwable $e) {
        error_log('Error al consultar el reporte: ' . $e->getMessage());
        $error = 'No fue posible cargar el reporte. Inténtalo nuevamente.';
    }
}

$zonaHoraria = new DateTimeZone('America/Bogota');
$fechaMaxima = (new DateTimeImmutable('now', $zonaHoraria))
    ->format('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar reporte de falla</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 35px 15px;
            background-color: #f1f5f9;
            color: #172033;
            font-family: Arial, sans-serif;
        }

        .contenedor {
            max-width: 720px;
            margin: 0 auto;
        }

        h1 {
            color: #174a87;
            text-align: center;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .descripcion {
            color: #526174;
            text-align: center;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .formulario {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .seccion {
            color: #174a87;
            font-size: 17px;
            font-weight: 700;
            margin: 10px 0 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dce4ed;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
        }

        .obligatorio {
            color: #c62828;
        }

        .ayuda {
            color: #64748b;
            font-size: 13px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #174a87;
            box-shadow: 0 0 0 3px rgba(23, 74, 135, 0.15);
        }

        .was-validated .form-control:valid,
        .was-validated .form-select:valid {
            border-color: #dee2e6;
            background-image: none;
            box-shadow: none;
        }

        .was-validated .form-control:valid:focus,
        .was-validated .form-select:valid:focus {
            border-color: #174a87;
            box-shadow: 0 0 0 3px rgba(23, 74, 135, 0.15);
        }

        .btn-guardar {
            background-color: #174a87;
            border: 1px solid #174a87;
            color: white;
        }

        .btn-guardar:hover {
            background-color: #103964;
            border-color: #103964;
            color: white;
        }

        [hidden] {
            display: none !important;
        }

        @media (max-width: 576px) {
            body {
                padding: 20px 12px;
            }

            .formulario {
                padding: 22px 18px;
            }

            h1 {
                font-size: 26px;
            }

            .botones {
                flex-direction: column;
            }

            .botones .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <main class="contenedor">

        <h1>Modificar reporte de falla</h1>

        <p class="descripcion">
            Revisa la información registrada y modifica
            los datos que necesites corregir.
        </p>

        <?php if ($error !== ''): ?>

            <div class="alert alert-warning" role="alert">
                <?= escapar($error) ?>
            </div>

            <a href="listar.php" class="btn btn-secondary">
                Volver a los reportes
            </a>

        <?php else: ?>

            <form id="formularioEditar"
                  class="formulario"
                  action="actualizar.php"
                  method="POST"
                  novalidate>

                <input type="hidden"
                       name="id"
                       value="<?= (int) $reporte['id'] ?>">

                <p class="mb-2">
                    Reporte número:
                    <strong><?= (int) $reporte['id'] ?></strong>
                </p>

                <p class="ayuda mb-4">
                    Los campos marcados con
                    <span class="obligatorio">*</span>
                    son obligatorios.
                </p>

                <h2 class="seccion">Datos del estudiante o docente</h2>

                <div class="mb-4">
                    <label for="nombre" class="form-label">
                        Nombre completo
                        <span class="obligatorio">*</span>
                    </label>

                    <input type="text"
                           id="nombre"
                           name="nombre"
                           class="form-control"
                           maxlength="100"
                           value="<?= escapar($reporte['nombre']) ?>"
                           required>

                    <div class="invalid-feedback">
                        Escribe tu nombre completo.
                    </div>
                </div>

                <h2 class="seccion">Información del computador</h2>

                <div class="mb-3">
                    <label for="laboratorio" class="form-label">
                        Laboratorio
                        <span class="obligatorio">*</span>
                    </label>

                    <select id="laboratorio"
                            name="laboratorio"
                            class="form-select"
                            required>

                        <option value="">
                            Selecciona un laboratorio
                        </option>

                        <?php for ($numero = 1; $numero <= 3; $numero++): ?>
                            <option value="<?= $numero ?>"
                                <?= (string) $reporte['laboratorio'] === (string) $numero
                                    ? 'selected'
                                    : '' ?>>
                                Laboratorio <?= $numero ?>
                            </option>
                        <?php endfor; ?>
                    </select>

                    <div class="invalid-feedback">
                        Selecciona el laboratorio donde está el computador.
                    </div>
                </div>

                <div class="mb-4">
                    <label for="equipo" class="form-label">
                        Código del computador
                        <span class="obligatorio">*</span>
                    </label>

                    <input type="text"
                           id="equipo"
                           name="equipo"
                           class="form-control"
                           maxlength="30"
                           value="<?= escapar($reporte['equipo']) ?>"
                           required>

                    <div class="ayuda">
                        Verifica el código que identifica el equipo.
                    </div>

                    <div class="invalid-feedback">
                        Ingresa el código del computador.
                    </div>
                </div>

                <h2 class="seccion">Descripción de la falla</h2>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_falla" class="form-label">
                            Fecha de la falla
                            <span class="obligatorio">*</span>
                        </label>

                        <input type="date"
                               id="fecha_falla"
                               name="fecha_falla"
                               class="form-control"
                               max="<?= escapar($fechaMaxima) ?>"
                               value="<?= escapar($reporte['fecha_falla']) ?>"
                               required>

                        <div class="invalid-feedback">
                            Selecciona una fecha válida que no sea futura.
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="hora_falla" class="form-label">
                            Hora aproximada
                            <span class="obligatorio">*</span>
                        </label>

                        <input type="time"
                               id="hora_falla"
                               name="hora_falla"
                               class="form-control"
                               value="<?= escapar(substr($reporte['hora_falla'], 0, 5)) ?>"
                               required>

                        <div class="invalid-feedback">
                            Selecciona una hora válida que no sea futura.
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="falla" class="form-label">
                        Tipo de falla
                        <span class="obligatorio">*</span>
                    </label>

                    <select id="falla"
                            name="falla"
                            class="form-select"
                            required>

                        <?php foreach ($tiposFalla as $valor => $descripcion): ?>
                            <option value="<?= escapar($valor) ?>"
                                <?= $reporte['tipo_falla'] === $valor
                                    ? 'selected'
                                    : '' ?>>
                                <?= escapar($descripcion) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="invalid-feedback">
                        Selecciona el tipo de falla.
                    </div>
                </div>

                <div id="campoOtro" class="mb-4" hidden>
                    <label for="otro_problema" class="form-label">
                        Describe el problema
                        <span class="obligatorio">*</span>
                    </label>

                    <textarea id="otro_problema"
                              name="otro_problema"
                              class="form-control"
                              rows="4"
                              placeholder="Explica brevemente qué sucede con el computador"><?= escapar($reporte['otro_problema']) ?></textarea>

                    <div class="invalid-feedback">
                        Escribe una breve descripción del problema.
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap mt-4 botones">
                    <button type="submit" class="btn btn-guardar">
                        Guardar cambios
                    </button>

                    <a href="listar.php" class="btn btn-outline-secondary">
                        Cancelar y volver
                    </a>
                </div>

            </form>

        <?php endif; ?>

    </main>

    <?php if ($reporte && $error === ''): ?>
        <script>
            const formulario = document.getElementById('formularioEditar');
            const nombre = document.getElementById('nombre');
            const equipo = document.getElementById('equipo');
            const fecha = document.getElementById('fecha_falla');
            const hora = document.getElementById('hora_falla');
            const falla = document.getElementById('falla');
            const campoOtro = document.getElementById('campoOtro');
            const otroProblema = document.getElementById('otro_problema');

            function fechaActual() {
                const hoy = new Date();
                const anio = hoy.getFullYear();
                const mes = String(hoy.getMonth() + 1).padStart(2, '0');
                const dia = String(hoy.getDate()).padStart(2, '0');

                return `${anio}-${mes}-${dia}`;
            }

            function actualizarCampoOtro() {
                const esOtro = falla.value === 'otro';

                campoOtro.hidden = !esOtro;
                otroProblema.required = esOtro;
                otroProblema.setCustomValidity('');

                if (!esOtro) {
                    otroProblema.value = '';
                }
            }

            function validarFechaHora() {
                hora.setCustomValidity('');

                if (!fecha.value || !hora.value) {
                    return;
                }

                const fechaSeleccionada = new Date(
                    `${fecha.value}T${hora.value}`
                );

                if (
                    Number.isNaN(fechaSeleccionada.getTime()) ||
                    fechaSeleccionada > new Date()
                ) {
                    hora.setCustomValidity(
                        'La fecha y hora no pueden ser futuras.'
                    );
                }
            }

            falla.addEventListener('change', actualizarCampoOtro);
            fecha.addEventListener('change', validarFechaHora);
            hora.addEventListener('change', validarFechaHora);

            nombre.addEventListener('input', function() {
                nombre.setCustomValidity('');
            });

            equipo.addEventListener('input', function() {
                equipo.setCustomValidity('');
            });

            otroProblema.addEventListener('input', function() {
                otroProblema.setCustomValidity('');
            });

            formulario.addEventListener('submit', function(evento) {
                nombre.value = nombre.value.trim();
                equipo.value = equipo.value.trim();

                nombre.setCustomValidity(
                    nombre.value === '' ? 'Escribe tu nombre.' : ''
                );

                equipo.setCustomValidity(
                    equipo.value === '' ? 'Ingresa el código del computador.' : ''
                );

                if (falla.value === 'otro') {
                    otroProblema.value = otroProblema.value.trim();

                    otroProblema.setCustomValidity(
                        otroProblema.value === ''
                            ? 'Describe el problema.'
                            : ''
                    );
                }

                fecha.max = fechaActual();
                validarFechaHora();

                formulario.classList.add('was-validated');

                if (!formulario.checkValidity()) {
                    evento.preventDefault();

                    const campoInvalido = formulario.querySelector(':invalid');

                    if (campoInvalido) {
                        campoInvalido.focus();
                    }
                }
            });

            actualizarCampoOtro();
        </script>
    <?php endif; ?>

</body>
</html>
