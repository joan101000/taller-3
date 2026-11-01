
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Registrar reporte de falla</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f1f5f9;
            color: #172033;
            margin: 0;
            padding: 30px 15px;
        }

        .contenedor {
            max-width: 650px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            color: #174a87;
            font-weight: bold;
            margin-bottom: 12px;
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

        .ayuda {
            color: #526174;
            font-size: 13px;
            margin-top: 5px;
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

        [hidden] {
            display: none !important;
        }

        @media (max-width: 600px) {
            .formulario {
                padding: 20px;
            }

            h1 {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>

    <main class="contenedor">

        <h1>Registrar reporte de falla</h1>

        <p class="descripcion">
            Complete el siguiente formulario para
            registrar problemas en los computadores
            de los laboratorios de la universidad.
        </p>

        <!-- FORMULARIO PARA REGISTRAR REPORTES -->

        <form
            id="formulario_reporte"
            class="formulario"
            action="guardar.php"
            method="POST">

            <p class="ayuda">
                Los campos marcados con
                <span class="asterisco">*</span>
                son obligatorios.
            </p>

            <!-- NOMBRE DEL ESTUDIANTE -->

            <div class="mb-3">

                <label for="nombre" class="form-label">
                    Nombre del estudiante
                    <span class="asterisco">*</span>
                </label>

                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    class="form-control"
                    placeholder="Escriba su nombre y apellido"
                    maxlength="100"
                    autocomplete="name"
                    required>

            </div>

            <!-- LABORATORIO -->

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

                    <option value="" selected disabled>
                        Seleccione el laboratorio donde está el equipo
                    </option>

                    <option value="1">Laboratorio 1</option>
                    <option value="2">Laboratorio 2</option>
                    <option value="3">Laboratorio 3</option>

                </select>

            </div>

            <!-- CÓDIGO DEL COMPUTADOR -->

            <div class="mb-3">

                <label for="equipo" class="form-label">
                    Código del computador
                    <span class="asterisco">*</span>
                </label>

                <input
                    type="text"
                    id="equipo"
                    name="equipo"
                    class="form-control"
                    placeholder="Ejemplo: PC-012"
                    maxlength="30"
                    required>

            </div>

            <!-- FECHA DE LA FALLA -->

            <div class="mb-3">

                <label for="fecha_falla" class="form-label">
                    Fecha en que se detectó la falla
                    <span class="asterisco">*</span>
                </label>

                <input
                    type="date"
                    id="fecha_falla"
                    name="fecha_falla"
                    class="form-control"
                    required>

                <div class="ayuda">
                    Seleccione el día, mes y año en el calendario.
                </div>

            </div>

            <!-- HORA DE LA FALLA -->

            <div class="mb-3">

                <label for="hora_falla" class="form-label">
                    Hora en que se detectó la falla
                    <span class="asterisco">*</span>
                </label>

                <input
                    type="time"
                    id="hora_falla"
                    name="hora_falla"
                    class="form-control"
                    required>

                <div class="ayuda">
                    Seleccione la hora aproximada en que
                    descubrió el problema.
                </div>

            </div>

            <!-- TIPO DE FALLA -->

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

                    <option value="" selected disabled>
                        Seleccione el problema que presenta el equipo
                    </option>

                    <option value="encendido">No enciende</option>
                    <option value="internet">Sin conexión a internet</option>
                    <option value="teclado">Problema con el teclado</option>
                    <option value="pantalla">Problema con la pantalla</option>
                    <option value="software">Error de software</option>
                    <option value="otro">Otro problema</option>

                </select>

            </div>

            <!-- CAMPO QUE APARECE AL SELECCIONAR OTRO -->

            <div id="campo_otro" class="mb-3" hidden>

                <label for="otro_problema" class="form-label">
                    Describa qué otro problema presenta el computador
                    <span class="asterisco">*</span>
                </label>

                <textarea
                    id="otro_problema"
                    name="otro_problema"
                    class="form-control"
                    rows="4"
                    placeholder="Explique brevemente el problema"></textarea>

            </div>

            <!-- BOTONES -->

            <div class="d-flex gap-2 flex-wrap mt-4">

                <button
                    type="submit"
                    class="btn btn-principal">

                    Guardar reporte

                </button>

                <button
                    type="reset"
                    class="btn btn-secondary">

                    Limpiar formulario

                </button>

                <a
                    href="index.php"
                    class="btn btn-outline-secondary">

                    Volver al inicio

                </a>

            </div>

        </form>

    </main>

    <!-- JAVASCRIPT -->

    <script>

        const formulario = document.getElementById(
            "formulario_reporte"
        );

        const falla = document.getElementById("falla");

        const campoOtro = document.getElementById("campo_otro");

        const otroProblema = document.getElementById(
            "otro_problema"
        );

        const fecha = document.getElementById("fecha_falla");

        const hora = document.getElementById("hora_falla");


        // Obtener la fecha actual del navegador

        function fechaLocal() {

            const ahora = new Date();

            const anio = ahora.getFullYear();

            const mes = String(
                ahora.getMonth() + 1
            ).padStart(2, "0");

            const dia = String(
                ahora.getDate()
            ).padStart(2, "0");

            return `${anio}-${mes}-${dia}`;

        }


        // No permitir fechas posteriores a hoy

        fecha.max = fechaLocal();


        // Mostrar u ocultar el campo Otro problema

        function actualizarCampoOtro() {

            const esOtro = falla.value === "otro";

            campoOtro.hidden = !esOtro;

            otroProblema.required = esOtro;

            if (!esOtro) {
                otroProblema.value = "";
            }

        }


        falla.addEventListener(
            "change",
            actualizarCampoOtro
        );


        // Validaciones antes de enviar a PHP

        formulario.addEventListener(
            "submit",
            function(evento) {

                // Evitar nombres que solo tengan espacios

                const nombre = document.getElementById(
                    "nombre"
                );

                const equipo = document.getElementById(
                    "equipo"
                );

                if (
                    nombre.value.trim() === "" ||
                    equipo.value.trim() === ""
                ) {

                    evento.preventDefault();

                    alert(
                        "Complete correctamente el nombre " +
                        "y el código del computador."
                    );

                    return;

                }


                // Validar fecha y hora

                const fechaHora = new Date(
                    fecha.value + "T" + hora.value
                );

                if (
                    Number.isNaN(fechaHora.getTime()) ||
                    fechaHora > new Date()
                ) {

                    evento.preventDefault();

                    alert(
                        "La fecha y la hora de la falla " +
                        "no pueden ser futuras."
                    );

                    return;

                }


                // Validar la descripción de Otro problema

                if (
                    falla.value === "otro" &&
                    otroProblema.value.trim() === ""
                ) {

                    evento.preventDefault();

                    alert(
                        "Debe describir el otro problema " +
                        "que presenta el computador."
                    );

                    otroProblema.focus();

                    return;

                }

                // Si todo es válido, el navegador envía
                // los datos mediante POST a guardar.php.

            }
        );


        // Confirmar antes de limpiar el formulario

        formulario.addEventListener(
            "reset",
            function(evento) {

                const confirmar = confirm(
                    "¿Desea limpiar todos los campos del formulario?"
                );

                if (!confirmar) {

                    evento.preventDefault();
                    return;

                }

                campoOtro.hidden = true;
                otroProblema.required = false;

            }
        );

    </script>

</body>
</html>
