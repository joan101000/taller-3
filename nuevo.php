
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar reporte de falla</title>

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
            font-weight: 700;
            text-align: center;
            margin-bottom: 12px;
        }

        .descripcion {
            color: #526174;
            text-align: center;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .formulario {
            background-color: white;
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

        .btn-guardar:hover,
        .btn-guardar:focus {
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

        <h1>Registrar reporte de falla</h1>

        <p class="descripcion">
            Registra los problemas que encuentres en los
            computadores de los laboratorios universitarios.
        </p>

        <form id="formularioReporte"
              class="formulario"
              action="guardar.php"
              method="POST"
              novalidate>

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
                       placeholder="Escribe tu nombre y apellido"
                       maxlength="100"
                       autocomplete="name"
                       pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+([ '-][A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+)*"
                       required>

                <div class="invalid-feedback" id="mensajeNombre">
                    El nombre no puede contener números ni caracteres no permitidos.
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

                    <option value="" selected disabled>
                        Selecciona un laboratorio
                    </option>

                    <option value="1">Laboratorio 1</option>
                    <option value="2">Laboratorio 2</option>
                    <option value="3">Laboratorio 3</option>
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
                       placeholder="Ejemplo: PC-012"
                       maxlength="30"
                       required>

                <div class="ayuda">
                    Escribe el código que identifica el equipo.
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

                    <option value="" selected disabled>
                        Selecciona el problema del equipo
                    </option>

                    <option value="encendido">No enciende</option>
                    <option value="internet">Sin conexión a internet</option>
                    <option value="teclado">Problema con el teclado</option>
                    <option value="pantalla">Problema con la pantalla</option>
                    <option value="software">Error de software</option>
                    <option value="otro">Otro problema</option>
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
                          placeholder="Explica brevemente qué sucede con el computador"></textarea>

                <div class="invalid-feedback">
                    Escribe una breve descripción del problema.
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap mt-4 botones">
                <button type="submit" class="btn btn-guardar">
                    Guardar reporte
                </button>

                <button type="button" class="btn btn-secondary"
                        data-bs-toggle="modal" data-bs-target="#modalLimpiar">
                    Limpiar formulario
                </button>

                <a href="index.php" class="btn btn-outline-secondary">
                    Cancelar y volver
                </a>
            </div>
        </form>
    </main>

    <!-- Confirmar limpieza del formulario -->
    <div class="modal fade" id="modalLimpiar" tabindex="-1"
         aria-labelledby="tituloModalLimpiar" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="tituloModalLimpiar">
                        Limpiar formulario
                    </h2>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¿Deseas borrar toda la información que escribiste en el formulario?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="button" class="btn btn-danger"
                            id="confirmarLimpiar" data-bs-dismiss="modal">
                        Sí, limpiar formulario
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const formulario = document.getElementById('formularioReporte');
        const nombre = document.getElementById('nombre');
        const equipo = document.getElementById('equipo');
        const fecha = document.getElementById('fecha_falla');
        const hora = document.getElementById('hora_falla');
        const falla = document.getElementById('falla');
        const campoOtro = document.getElementById('campoOtro');
        const otroProblema = document.getElementById('otro_problema');
        const mensajeNombre = document.getElementById('mensajeNombre');

        // Validar el nombre mientras se escribe
        function validarNombre() {
            const caracteresInvalidos = /[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ '-]/;
            const tieneError = caracteresInvalidos.test(nombre.value);

            if (tieneError) {
                nombre.setCustomValidity('El nombre contiene caracteres no permitidos.');
                nombre.classList.add('is-invalid');
                mensajeNombre.textContent = 'El nombre no puede contener números ni caracteres no permitidos.';
            } else {
                nombre.setCustomValidity('');
                nombre.classList.remove('is-invalid');
                mensajeNombre.textContent = nombre.value.trim() === ''
                    ? 'Escribe tu nombre completo.'
                    : 'Escribe un nombre válido.';
            }
        }

        nombre.addEventListener('input', validarNombre);

        function fechaActual() {
            const hoy = new Date();
            const anio = hoy.getFullYear();
            const mes = String(hoy.getMonth() + 1).padStart(2, '0');
            const dia = String(hoy.getDate()).padStart(2, '0');

            return `${anio}-${mes}-${dia}`;
        }

        fecha.max = fechaActual();

        function actualizarOtroProblema() {
            const esOtro = falla.value === 'otro';

            campoOtro.hidden = !esOtro;
            otroProblema.required = esOtro;

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
                    'La fecha y la hora no pueden ser futuras.'
                );
            }
        }

        falla.addEventListener('change', actualizarOtroProblema);
        fecha.addEventListener('change', validarFechaHora);
        hora.addEventListener('change', validarFechaHora);

        formulario.addEventListener('submit', function(evento) {
            nombre.value = nombre.value.trim();
            equipo.value = equipo.value.trim();

            validarNombre();

            if (falla.value === 'otro') {
                otroProblema.value = otroProblema.value.trim();
                otroProblema.setCustomValidity(
                    otroProblema.value === ''
                        ? 'Describe el problema.'
                        : ''
                );
            } else {
                otroProblema.setCustomValidity('');
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

        otroProblema.addEventListener('input', function() {
            otroProblema.setCustomValidity('');
        });

        // Limpiar los campos después de confirmar
        document.getElementById('confirmarLimpiar').addEventListener('click', function() {
            formulario.reset();
        });

        formulario.addEventListener('reset', function() {
            formulario.classList.remove('was-validated');

            nombre.setCustomValidity('');
            nombre.classList.remove('is-invalid');
            mensajeNombre.textContent = 'Escribe tu nombre completo.';

            hora.setCustomValidity('');
            otroProblema.setCustomValidity('');

            campoOtro.hidden = true;
            otroProblema.required = false;
        });
    </script>
</body>
</html>
