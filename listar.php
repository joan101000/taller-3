
<?php
// Archivo: listar.php
// Sistema de Gestión de Fallas en Laboratorios
// Permite consultar, editar y eliminar reportes.

session_start();

require_once "conexion.php";

// Crear un token de seguridad para eliminar reportes.
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Función para mostrar datos de forma segura.
function escapar($valor) {
    return htmlspecialchars(
        (string) ($valor ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

// Nombres de los tipos de fallas.
$tiposFalla = [
    "encendido" => "No enciende",
    "internet" => "Sin conexión a internet",
    "teclado" => "Problema con el teclado",
    "pantalla" => "Problema con la pantalla",
    "software" => "Error de software",
    "otro" => "Otro problema"
];

// Aquí guardaremos los reportes consultados.
$reportes = [];

// Control de errores de conexión.
$errorConexion = false;

// Consultar los registros de la base de datos.
try {

    $conexion = conectarBD();

    $sql = "SELECT
                id,
                nombre,
                laboratorio,
                equipo,
                fecha_falla,
                hora_falla,
                tipo_falla,
                otro_problema
            FROM reportes
            ORDER BY id DESC";

    $sentencia = $conexion->query($sql);

    $reportes = $sentencia->fetchAll(
        PDO::FETCH_ASSOC
    );

} catch (Throwable $error) {

    $errorConexion = true;

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Consultar reportes de fallas</title>

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
            max-width: 1200px;
            margin: auto;
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px #00000015;
        }

        h1 {
            color: #174a87;
            text-align: center;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .descripcion {
            color: #526174;
            text-align: center;
            line-height: 1.5;
            margin-bottom: 25px;
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

        .tabla-contenedor {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background-color: #174a87 !important;
            color: white !important;
            padding: 14px;
            white-space: nowrap;
        }

        tbody td {
            padding: 12px;
            vertical-align: middle;
            overflow-wrap: anywhere;
        }

        .acciones {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .acciones form {
            margin: 0;
        }

        .acciones .btn {
            white-space: nowrap;
        }

        .mensaje {
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            line-height: 1.5;
        }

        .advertencia {
            background-color: #fff4dc;
            color: #765000;
        }

        .informacion {
            background-color: #e8f1ff;
            color: #174a87;
        }

        @media (max-width: 600px) {

            .contenedor {
                padding: 20px;
            }

            .navegacion a {
                width: 100%;
            }

            h1 {
                font-size: 25px;
            }

        }

    </style>

</head>

<body>

<main class="contenedor">

    <!-- TÍTULO -->

    <h1>Consulta de reportes de fallas</h1>

    <p class="descripcion">

        Consulte los problemas registrados en los
        computadores de los laboratorios universitarios.

        Desde esta sección podrá modificar o eliminar
        los reportes existentes.

    </p>


    <!-- BOTONES DE NAVEGACIÓN -->

    <div class="navegacion d-flex gap-2 flex-wrap mb-4">

        <a href="index.php"
           class="btn btn-secondary">

            Volver al inicio

        </a>

        <a href="nuevo.php"
           class="btn btn-principal">

            Crear nuevo reporte

        </a>

    </div>


    <!-- MENSAJES DESPUÉS DE ACTUALIZAR O ELIMINAR -->

    <?php if (
        !$errorConexion &&
        isset($_GET["actualizado"]) &&
        $_GET["actualizado"] === "1"
    ): ?>

        <div class="alert alert-success" role="status">

            El reporte fue actualizado correctamente.

        </div>

    <?php endif; ?>


    <?php if (
        !$errorConexion &&
        isset($_GET["eliminado"]) &&
        $_GET["eliminado"] === "1"
    ): ?>

        <div class="alert alert-success" role="status">

            El reporte fue eliminado correctamente.

        </div>

    <?php endif; ?>


    <!-- VERIFICAR SI EXISTE CONEXIÓN -->

    <?php if ($errorConexion): ?>

        <div class="mensaje advertencia" role="alert">

            <strong>Conexión pendiente.</strong>

            <p class="mb-2 mt-2">

                Todavía no se ha configurado una
                base de datos autorizada para este proyecto.

            </p>

            <p class="mb-0">

                Cuando se configure la conexión,
                los reportes registrados aparecerán aquí.

            </p>

        </div>


    <!-- VERIFICAR SI LA TABLA ESTÁ VACÍA -->

    <?php elseif (count($reportes) === 0): ?>

        <div class="mensaje informacion" role="status">

            Todavía no hay reportes registrados
            en la base de datos.

        </div>


    <!-- MOSTRAR TABLA DE REPORTES -->

    <?php else: ?>

        <div class="tabla-contenedor">

            <table class="table table-bordered table-striped table-hover">

                <thead>

                    <tr>

                        <th scope="col">ID</th>

                        <th scope="col">Estudiante</th>

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

                        <tr>

                            <!-- ID -->

                            <td>

                                <?= (int) $reporte["id"] ?>

                            </td>


                            <!-- ESTUDIANTE -->

                            <td>

                                <?= escapar($reporte["nombre"]) ?>

                            </td>


                            <!-- LABORATORIO -->

                            <td>

                                Laboratorio
                                <?= escapar($reporte["laboratorio"]) ?>

                            </td>


                            <!-- COMPUTADOR -->

                            <td>

                                <?= escapar($reporte["equipo"]) ?>

                            </td>


                            <!-- FECHA -->

                            <td>

                                <?= escapar($reporte["fecha_falla"]) ?>

                            </td>


                            <!-- HORA -->

                            <td>

                                <?= escapar($reporte["hora_falla"]) ?>

                            </td>


                            <!-- TIPO DE FALLA -->

                            <td>

                                <?= escapar(
                                    $tiposFalla[$reporte["tipo_falla"]]
                                    ?? $reporte["tipo_falla"]
                                ) ?>

                            </td>


                            <!-- OTRO PROBLEMA -->

                            <td>

                                <?php

                                $otro = trim(
                                    (string) (
                                        $reporte["otro_problema"] ?? ""
                                    )
                                );

                                ?>

                                <?= escapar(
                                    $otro !== ""
                                        ? $otro
                                        : "No aplica"
                                ) ?>

                            </td>


                            <!-- BOTONES EDITAR Y ELIMINAR -->

                            <td>

                                <div class="acciones">


                                    <!-- EDITAR REPORTE -->

                                    <a
                                        href="editar.php?id=<?= (int) $reporte['id'] ?>"
                                        class="btn btn-warning btn-sm">

                                        Editar

                                    </a>


                                    <!-- ELIMINAR REPORTE -->

                                    <form
                                        action="eliminar.php"
                                        method="POST"
                                        onsubmit="return confirm('¿Está seguro de que desea eliminar este reporte? Esta acción no se puede deshacer.');">


                                        <!-- ID DEL REPORTE -->

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $reporte['id'] ?>">


                                        <!-- TOKEN DE SEGURIDAD -->

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= escapar(
                                                $_SESSION["csrf_token"]
                                            ) ?>">


                                        <!-- BOTÓN ELIMINAR -->

                                        <button
                                            type="submit"
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
