<?php

// =====================================================
// ARCHIVO: listar.php
// SISTEMA DE GESTIÓN DE FALLAS
//
// Funciones:
// - Consultar reportes.
// - Buscar reportes.
// - Visualizar detalles.
// - Acceder a la edición.
// - Eliminar con confirmación y protección CSRF.
// =====================================================

// Iniciar sesión antes de generar contenido HTML.
session_start();

// Cargar la conexión a MariaDB.
require_once __DIR__ . "/conexion.php";

// Crear un token de seguridad para eliminar reportes.
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Evitar que los datos se interpreten como código HTML.
function escapar($dato): string
{
    return htmlspecialchars(
        (string) ($dato ?? ""),
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8"
    );
}

// Nombres comprensibles de los tipos de falla.
$tiposFalla = [
    "encendido" => "No enciende",
    "internet" => "Sin conexión a internet",
    "teclado" => "Problema con el teclado",
    "pantalla" => "Problema con la pantalla",
    "software" => "Error de software",
    "otro" => "Otro problema"
];

// Recibir la búsqueda enviada por el usuario.
$busqueda = "";

if (isset($_GET["q"]) && is_string($_GET["q"])) {
    $busqueda = trim($_GET["q"]);
}

// Variables para almacenar los resultados.
$reportes = [];
$errorConsulta = false;

// =====================================================
// CONSULTAR LA BASE DE DATOS
// =====================================================

try {

    $conexion = conectarBD();

    // Consulta base: obtener los reportes más recientes.
    $sql = "
        SELECT
            id,
            nombre,
            laboratorio,
            equipo,
            fecha_falla,
            hora_falla,
            tipo_falla,
            otro_problema
        FROM reportes
    ";

    $parametros = [];

    // Si existe una búsqueda, aplicar filtros.
    if ($busqueda !== "") {

        $sql .= "
            WHERE
                CAST(id AS CHAR) LIKE ?
                OR nombre LIKE ?
                OR CONCAT('Laboratorio ', laboratorio) LIKE ?
                OR equipo LIKE ?
                OR tipo_falla LIKE ?
                OR otro_problema LIKE ?
        ";

        $valorBuscar = "%" . $busqueda . "%";

        $parametros = array_fill(
            0,
            6,
            $valorBuscar
        );
    }

    // Mostrar los registros más recientes primero.
    $sql .= " ORDER BY id DESC";

    // Preparar y ejecutar la consulta de manera segura.
    $consulta = $conexion->prepare($sql);

    $consulta->execute($parametros);

    // Recuperar los registros encontrados.
    $reportes = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $error) {

    $errorConsulta = true;

    error_log(
        "Error al consultar reportes: " .
        $error->getMessage()
    );
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Consulta de reportes de fallas</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        /* =========================================
           DISEÑO GENERAL
        ========================================= */

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
            background-color: #ffffff;
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

        /* =========================================
           BOTONES
        ========================================= */

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

        .navegacion {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        /* =========================================
           BUSCADOR
        ========================================= */

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

        .controles-busqueda input {
            flex: 1;
            min-width: 200px;
        }

        /* =========================================
           TABLA DE REPORTES
        ========================================= */

        .resumen {
            margin: 22px 0 15px;
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
            color: #ffffff;
            padding: 12px;
            white-space: nowrap;
            vertical-align: middle;
        }

        .table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            overflow-wrap: anywhere;
        }

        /* =========================================
           ACCIONES DE CADA REPORTE
        ========================================= */

        .acciones {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-wrap: nowrap;
        }

        .acciones form {
            margin: 0;
        }

        .acciones .btn {
            white-space: nowrap;
        }

        .btn-ver {
            background-color: #e8f1ff;
            border: 1px solid #174a87;
            color: #174a87;
        }

        .btn-ver:hover {
            background-color: #174a87;
            color: #ffffff;
        }

        /* =========================================
           MENSAJES
        ========================================= */

        .mensaje-vacio {
            padding: 22px;
            background-color: #e8f1ff;
            border-radius: 8px;
            color: #174a87;
        }

        /* =========================================
           VENTANA DE DETALLES
        ========================================= */

        .modal-header {
            background-color: #174a87;
            color: white;
        }

        .modal-header .btn-close {
            filter: invert(1);
        }

        .detalle-etiqueta {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .detalle-valor {
            font-size: 16px;
            color: #172033;
            overflow-wrap: anywhere;
        }

        .detalle-grupo {
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .detalle-grupo:last-child {
            border-bottom: none;
        }

        /* =========================================
           DISEÑO RESPONSIVE
        ========================================= */

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

            .controles-busqueda {
                flex-direction: column;
            }

            .controles-busqueda input,
            .controles-busqueda .btn {
                width: 100%;
            }

        }

    </style>

</head>

<body>

<main class="contenedor">

    <!-- =========================================
         ENCABEZADO
    ========================================= -->

    <h1>Consulta de reportes de fallas</h1>

    <p class="descripcion">
        Consulte los reportes de fallas registrados
        en los computadores de los laboratorios
        universitarios. Puede buscar información,
        visualizar detalles, corregir registros
        y eliminar reportes cuando sea necesario.
    </p>

    <!-- =========================================
         NAVEGACIÓN
    ========================================= -->

    <nav class="navegacion" aria-label="Navegación de reportes">

        <a
            href="index.php"
            class="btn btn-secondary"
        >
            Volver al inicio
        </a>

        <a
            href="nuevo.php"
            class="btn btn-principal"
        >
            Crear nuevo reporte
        </a>

    </nav>

    <!-- =========================================
         MENSAJES DE CONFIRMACIÓN
    ========================================= -->

    <?php if (!$errorConsulta): ?>

        <?php if (($_GET["guardado"] ?? "") === "1"): ?>

            <div class="alert alert-success" role="status">
                El reporte fue registrado correctamente.
            </div>

        <?php endif; ?>

        <?php if (($_GET["actualizado"] ?? "") === "1"): ?>

            <div class="alert alert-success" role="status">
                El reporte fue actualizado correctamente.
            </div>

        <?php endif; ?>

        <?php if (($_GET["eliminado"] ?? "") === "1"): ?>

            <div class="alert alert-success" role="status">
                El reporte fue eliminado correctamente.
            </div>

        <?php endif; ?>

    <?php endif; ?>

    <!-- =========================================
         BUSCADOR
    ========================================= -->

    <?php if (!$errorConsulta): ?>

        <section class="seccion-busqueda">

            <h2 class="titulo-busqueda">
                Buscar un reporte
            </h2>

            <p class="ayuda-busqueda">
                Puede buscar por número de reporte,
                estudiante o docente, laboratorio,
                computador o tipo de falla.
            </p>

            <form
                action="listar.php"
                method="GET"
                role="search"
            >

                <label
                    for="buscarReporte"
                    class="form-label fw-semibold"
                >
                    Escriba lo que desea buscar
                </label>

                <div class="controles-busqueda">

                    <input
                        type="search"
                        id="buscarReporte"
                        name="q"
                        class="form-control"
                        placeholder="Ejemplo: PC-05, Laboratorio 2 o teclado"
                        value="<?= escapar($busqueda) ?>"
                        maxlength="100"
                    >

                    <button
                        type="submit"
                        class="btn btn-principal"
                    >
                        Buscar
                    </button>

                    <a
                        href="listar.php"
                        class="btn btn-outline-secondary"
                    >
                        Limpiar
                    </a>

                </div>

            </form>

        </section>

    <?php endif; ?>

    <!-- =========================================
         ERROR DE CONSULTA
    ========================================= -->

    <?php if ($errorConsulta): ?>

        <div class="alert alert-danger" role="alert">

            <strong>No fue posible consultar los reportes.</strong>

            <p class="mb-0 mt-2">
                Compruebe que la aplicación y la base
                de datos estén funcionando correctamente.
            </p>

        </div>

    <!-- =========================================
         SIN RESULTADOS
    ========================================= -->

    <?php elseif (empty($reportes)): ?>

        <?php if ($busqueda !== ""): ?>

            <div class="mensaje-vacio" role="status">

                No se encontraron reportes que coincidan con:

                <strong><?= escapar($busqueda) ?></strong>.

                <p class="mb-0 mt-2">
                    Revise la información escrita
                    o utilice el botón Limpiar.
                </p>

            </div>

        <?php else: ?>

            <div class="mensaje-vacio" role="status">

                Todavía no hay reportes registrados.

                <p class="mb-0 mt-2">
                    Puede crear el primer reporte
                    desde el formulario.
                </p>

            </div>

        <?php endif; ?>

    <!-- =========================================
         LISTADO DE REPORTES
    ========================================= -->

    <?php else: ?>

        <p class="resumen" role="status">

            <?php if ($busqueda !== ""): ?>

                <strong>Resultados encontrados:</strong>

            <?php else: ?>

                <strong>Reportes registrados:</strong>

            <?php endif; ?>

            <?= count($reportes) ?>

        </p>

        <div class="tabla-contenedor">

            <table
                class="table table-bordered table-striped table-hover"
            >

                <thead>

                    <tr>

                        <th scope="col">ID</th>

                        <th scope="col">
                            Estudiante o docente
                        </th>

                        <th scope="col">
                            Laboratorio
                        </th>

                        <th scope="col">
                            Computador
                        </th>

                        <th scope="col">
                            Fecha
                        </th>

                        <th scope="col">
                            Hora
                        </th>

                        <th scope="col">
                            Tipo de falla
                        </th>

                        <th scope="col">
                            Otro problema
                        </th>

                        <th scope="col">
                            Acciones
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($reportes as $reporte): ?>

                        <?php

                        $id = (int) $reporte["id"];

                        $tipo = $tiposFalla[
                            $reporte["tipo_falla"]
                        ] ?? $reporte["tipo_falla"];

                        $otro = trim(
                            (string) (
                                $reporte["otro_problema"] ?? ""
                            )
                        );

                        if ($otro === "") {
                            $otro = "No aplica";
                        }

                        ?>

                        <tr>

                            <td>
                                <?= $id ?>
                            </td>

                            <td>
                                <?= escapar($reporte["nombre"]) ?>
                            </td>

                            <td>
                                Laboratorio
                                <?= escapar($reporte["laboratorio"]) ?>
                            </td>

                            <td>
                                <?= escapar($reporte["equipo"]) ?>
                            </td>

                            <td>
                                <?= escapar($reporte["fecha_falla"]) ?>
                            </td>

                            <td>
                                <?= escapar($reporte["hora_falla"]) ?>
                            </td>

                            <td>
                                <?= escapar($tipo) ?>
                            </td>

                            <td>
                                <?= escapar($otro) ?>
                            </td>

                            <td>

                                <div class="acciones">

                                    <!-- VER DETALLES -->

                                    <button
                                        type="button"
                                        class="btn btn-ver btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDetalle"
                                        data-id="<?= $id ?>"
                                        data-nombre="<?= escapar($reporte["nombre"]) ?>"
                                        data-laboratorio="<?= escapar($reporte["laboratorio"]) ?>"
                                        data-equipo="<?= escapar($reporte["equipo"]) ?>"
                                        data-fecha="<?= escapar($reporte["fecha_falla"]) ?>"
                                        data-hora="<?= escapar($reporte["hora_falla"]) ?>"
                                        data-tipo="<?= escapar($tipo) ?>"
                                        data-otro="<?= escapar($otro) ?>"
                                    >
                                        Ver
                                    </button>

                                    <!-- EDITAR -->

                                    <a
                                        href="editar.php?id=<?= $id ?>"
                                        class="btn btn-warning btn-sm"
                                    >
                                        Editar
                                    </a>

                                    <!-- ELIMINAR -->

                                    <form
                                        action="eliminar.php"
                                        method="POST"
                                        onsubmit="return confirm('¿Deseas eliminar el reporte número <?= $id ?>? Esta acción es definitiva y no se puede deshacer.');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= $id ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= escapar($_SESSION["csrf_token"]) ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-danger btn-sm"
                                        >
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

        <!-- =====================================
             VENTANA PARA VER DETALLES
        ====================================== -->

        <div
            class="modal fade"
            id="modalDetalle"
            tabindex="-1"
            aria-labelledby="tituloModalDetalle"
            aria-hidden="true"
        >

            <div class="modal-dialog modal-dialog-centered modal-lg">

                <div class="modal-content">

                    <div class="modal-header">

                        <h2
                            class="modal-title fs-5"
                            id="tituloModalDetalle"
                        >
                            Detalles del reporte de falla
                        </h2>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar"
                        ></button>

                    </div>

                    <div class="modal-body px-4 py-3">

                        <div class="row">

                            <div class="col-md-6 detalle-grupo">
                                <div class="detalle-etiqueta">
                                    Número del reporte
                                </div>
                                <div
                                    class="detalle-valor"
                                    id="detalle-id"
                                ></div>
                            </div>

                            <div class="col-md-6 detalle-grupo">
                                <div class="detalle-etiqueta">
                                    Estudiante o docente
                                </div>
                                <div
                                    class="detalle-valor"
                                    id="detalle-nombre"
                                ></div>
                            </div>

                            <div class="col-md-6 detalle-grupo">
                                <div class="detalle-etiqueta">
                                    Laboratorio
                                </div>
                                <div
                                    class="detalle-valor"
                                    id="detalle-laboratorio"
                                ></div>
                            </div>

                            <div class="col-md-6 detalle-grupo">
                                <div class="detalle-etiqueta">
                                    Computador
                                </div>
                                <div
                                    class="detalle-valor"
                                    id="detalle-equipo"
                                ></div>
                            </div>

                            <div class="col-md-6 detalle-grupo">
                                <div class="detalle-etiqueta">
                                    Fecha de la falla
                                </div>
                                <div
                                    class="detalle-valor"
                                    id="detalle-fecha"
                                ></div>
                            </div>

                            <div class="col-md-6 detalle-grupo">
                                <div class="detalle-etiqueta">
                                    Hora de la falla
                                </div>
                                <div
                                    class="detalle-valor"
                                    id="detalle-hora"
                                ></div>
                            </div>

                            <div class="col-12 detalle-grupo">
                                <div class="detalle-etiqueta">
                                    Tipo de falla
                                </div>
                                <div
                                    class="detalle-valor"
                                    id="detalle-tipo"
                                ></div>
                            </div>

                            <div class="col-12 detalle-grupo">
                                <div class="detalle-etiqueta">
                                    Descripción de otro problema
                                </div>
                                <div
                                    class="detalle-valor"
                                    id="detalle-otro"
                                ></div>
                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cerrar
                        </button>

                    </div>

                </div>

            </div>

        </div>

    <?php endif; ?>

</main>

<!-- =========================================
     JAVASCRIPT DE BOOTSTRAP
========================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

<script>

    // =========================================
    // MOSTRAR LOS DETALLES DEL REPORTE
    // =========================================

    const modalDetalle = document.getElementById(
        "modalDetalle"
    );

    if (modalDetalle) {

        modalDetalle.addEventListener(
            "show.bs.modal",
            function (evento) {

                // Identificar el botón Ver seleccionado.
                const boton = evento.relatedTarget;

                if (!boton) {
                    return;
                }

                // Obtener los datos del reporte.
                const datos = {
                    "detalle-id": boton.dataset.id,
                    "detalle-nombre": boton.dataset.nombre,
                    "detalle-laboratorio":
                        "Laboratorio " + boton.dataset.laboratorio,
                    "detalle-equipo": boton.dataset.equipo,
                    "detalle-fecha": boton.dataset.fecha,
                    "detalle-hora": boton.dataset.hora,
                    "detalle-tipo": boton.dataset.tipo,
                    "detalle-otro": boton.dataset.otro
                };

                // Colocar cada dato en su campo.
                // textContent evita interpretar HTML.
                for (const [idCampo, valor] of Object.entries(datos)) {

                    const campo = document.getElementById(
                        idCampo
                    );

                    if (campo) {
                        campo.textContent = valor ?? "";
                    }

                }

            }
        );

    }

</script>

</body>
</html>
