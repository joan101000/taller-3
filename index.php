<?php
// Página principal del sistema
$titulo = "Sistema de Gestión de Fallas";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title><?php echo $titulo; ?></title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f5f9;
            color: #172033;
            min-height: 100vh;
            margin: 0;
            padding: 30px 15px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .contenedor {
            width: 100%;
            max-width: 650px;
            padding: 40px 30px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px #00000015;
            text-align: center;
        }

        h1 {
            color: #174a87;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .descripcion {
            color: #526174;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .opciones {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
        }

        .btn-principal {
            background-color: #174a87;
            border: 1px solid #174a87;
            color: white;
        }

        .btn-principal:hover {
            background-color: #103964;
            color: white;
        }

        .btn-secundario {
            background-color: white;
            border: 1px solid #174a87;
            color: #174a87;
        }

        .btn-secundario:hover {
            background-color: #e8f1ff;
            color: #103964;
        }

        .opciones .btn {
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 500;
        }

        .pie {
            margin-top: 30px;
            font-size: 13px;
            color: #64748b;
        }

        @media (max-width: 600px) {
            .contenedor {
                padding: 25px 20px;
            }

            .opciones {
                flex-direction: column;
            }

            .opciones .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <main class="contenedor">

        <h1>
            <?php echo htmlspecialchars(
                $titulo,
                ENT_QUOTES,
                "UTF-8"
            ); ?>
        </h1>

        <p class="descripcion">
            Aplicación web para registrar y gestionar
            reportes de fallas en los computadores
            de los laboratorios universitarios.
        </p>

        <div class="opciones">

            <!-- Crear un reporte -->

            <a href="nuevo.php"
               class="btn btn-principal">

                Crear nuevo reporte

            </a>

            <!-- Consultar reportes -->

            <a href="listar.php"
               class="btn btn-secundario">

                Consultar reportes

            </a>

        </div>

        <p class="pie">
            Taller 3 - Desarrollo de Aplicaciones en la Web
        </p>

    </main>

</body>
</html>
