<!-- numero.php -->

<?php
// Incluimos los archivos necesarios
include 'sparqlquerydispatcher.php';
include 'sparql_prefijos.php';
include 'numero_sparql.php';
include 'menu_principal.php';

// Verificamos si se ha pasado un número en la URL
if (!isset($_GET['numero'])) {
    die("Error: No se especificó un número.");
}

// Parámetros
$numeroURI = urldecode($_GET['numero']);
$tablaActual = isset($_GET['tabla']) ? $_GET['tabla'] : 'articulos'; // articulos o autores
$resultadosPorPagina = isset($_GET['resultadosPorPagina']) ? intval($_GET['resultadosPorPagina']) : 10;
$paginaActual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;

// Capturar los parámetros de ordenación
$orden = isset($_GET['orden']) ? $_GET['orden'] : null;
$direccion = isset($_GET['direccion']) && in_array($_GET['direccion'], ['asc', 'desc']) ? $_GET['direccion'] : null;

// Definimos el endpoint SPARQL
include 'endpoint_url.php';
$queryDispatcher = new SPARQLQueryDispatcher($endpointUrl);

// Consulta para obtener los detalles del número
$sparqlQueryDetalles = obtenerDetallesNumero($numeroURI);
$queryResultsDetalles = $queryDispatcher->query($sparqlQueryDetalles);
$numeroDetalles = $queryResultsDetalles["results"]["bindings"][0];

// Consulta para obtener el total de articulos de este numero
$sparqlQueryTotalArticulosNumero = contarTotalResultadosPorNumero($numeroURI, 'articulos');
$queryResultsTotalArticulosNumero = $queryDispatcher->query($sparqlQueryTotalArticulosNumero);
$numTotalArticulosNumero = isset($queryResultsTotalArticulosNumero["results"]["bindings"][0]["total"]["value"])
    ? intval($queryResultsTotalArticulosNumero["results"]["bindings"][0]["total"]["value"])
    : 0;

// Consulta para obtener el total de autores de este numero
$sparqlQueryTotalAutoresNumero = contarTotalResultadosPorNumero($numeroURI, 'autores');
$queryResultsTotalAutoresNumero = $queryDispatcher->query($sparqlQueryTotalAutoresNumero);
$numTotalAutoresNumero = isset($queryResultsTotalAutoresNumero["results"]["bindings"][0]["total"]["value"])
    ? intval($queryResultsTotalAutoresNumero["results"]["bindings"][0]["total"]["value"])
    : 0;


// Obtener número anterior
$sparqlQueryNumeroAnterior = obtenerNumeroAnterior($numeroURI);
$queryResultsNumeroAnterior = $queryDispatcher->query($sparqlQueryNumeroAnterior);
$numeroAnterior = isset($queryResultsNumeroAnterior["results"]["bindings"][0])
    ? $queryResultsNumeroAnterior["results"]["bindings"][0]
    : null;

// Obtener número siguiente
$sparqlQueryNumeroSiguiente = obtenerNumeroSiguiente($numeroURI);
$queryResultsNumeroSiguiente = $queryDispatcher->query($sparqlQueryNumeroSiguiente);
$numeroSiguiente = isset($queryResultsNumeroSiguiente["results"]["bindings"][0])
    ? $queryResultsNumeroSiguiente["results"]["bindings"][0]
    : null;



// Consulta para obtener los artículos o autores del número
$offset = ($paginaActual - 1) * $resultadosPorPagina;

if ($tablaActual === 'articulos') {
    $sparqlQueryTabla = obtenerArticulosPorNumero($numeroURI, $offset, $resultadosPorPagina, $orden, $direccion);
} else {
    $sparqlQueryTabla = obtenerAutoresPorNumero($numeroURI, $offset, $resultadosPorPagina);
}

$queryResultsTabla = $queryDispatcher->query($sparqlQueryTabla);
$tablaResultados = $queryResultsTabla["results"]["bindings"];

// Contar el total de resultados para la paginación
$sparqlQueryTotal = contarTotalResultadosPorNumero($numeroURI, $tablaActual);
$queryResultsTotal = $queryDispatcher->query($sparqlQueryTotal);
$totalResultados = intval($queryResultsTotal["results"]["bindings"][0]["total"]["value"]);
$totalPaginas = ceil($totalResultados / $resultadosPorPagina);

function truncarTexto($texto, $limite)
{
    if (strlen($texto) > $limite) {
        return substr($texto, 0, $limite) . "...";
    } else {
        return $texto;
    }
}


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $numeroDetalles["NumeroLabel"]["value"]; ?></title>
    <link rel="stylesheet" href="estilos_general.css">
    <link rel="stylesheet" href="estilos_numero.css">
    <link rel="stylesheet" href="estilos_revista.css">
</head>

<body>
    <?php renderMenu('revistas-lista.php'); ?>
    <main class="revista-detalles">

    <div class="page-label">Número/Volumen</div>

        <!-- Título del número -->
        <section class="revista-titulo">
            <h2><?php echo $numeroDetalles["NumeroLabel"]["value"]; ?></h2>
        </section>

        <!-- Contenedor superior: Imagen y tabla -->
        <section class="revista-detalles-grid">
            <div class="revista-info-container">
                <!-- Columna izquierda: Imagen -->
                <div class="revista-col-imagen">
                    <?php if (isset($numeroDetalles["NumeroImagen"]["value"])): ?>
                        <img src="<?php echo $numeroDetalles["NumeroImagen"]["value"]; ?>"
                            alt="<?php echo $numeroDetalles["NumeroLabel"]["value"]; ?>" class="imagen-fija">
                    <?php elseif (isset($numeroDetalles["RevistaImagen"]["value"])): ?>
                        <img src="<?php echo $numeroDetalles["RevistaImagen"]["value"]; ?>"
                            alt="<?php echo $numeroDetalles["RevistaLabel"]["value"]; ?>" class="imagen-fija">
                    <?php endif; ?>
                </div>

                <!-- Columna derecha: Tabla de datos -->
                <div class="revista-col-datos">
                    <table class="tabla-revista-info">
                        <thead>
                            <tr>
                                <th colspan="2">
                                    <?php echo isset($numeroDetalles["RevistaLabel"]["value"]) ? $numeroDetalles["RevistaLabel"]["value"] : "No especificado"; ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Volumen:</strong></td>
                                <td><?php echo isset($numeroDetalles["NumeroVolumen"]["value"]) ? $numeroDetalles["NumeroVolumen"]["value"] : "No especificado"; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Total de Artículos:</strong></td>
                                <td><?php echo $numTotalArticulosNumero; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Total de Autores:</strong></td>
                                <td><?php echo $numTotalAutoresNumero; ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Botón para volver a la revista -->
                    <section class="tabla-botones">
                        <?php if ($numeroAnterior): ?>
                            <button
                                onclick="window.location.href='numero.php?numero=<?php echo urlencode($numeroAnterior["NumeroAnterior"]["value"]); ?>'">
                                Número anterior:
                                <?php echo truncarTexto($numeroAnterior["VolumenAnterior"]["value"], 15); ?>
                            </button>
                        <?php endif; ?>
                    </section>
                    <section class="tabla-botones">


                        <?php if ($numeroSiguiente): ?>
                            <button
                                onclick="window.location.href='numero.php?numero=<?php echo urlencode($numeroSiguiente["NumeroSiguiente"]["value"]); ?>'">
                                Número siguiente:
                                <?php echo truncarTexto($numeroSiguiente["VolumenSiguiente"]["value"], 15); ?>
                            </button>
                        <?php endif; ?>
                    </section>
                    <section class="tabla-botones">

                        <button
                            onclick="window.location.href='revista.php?revista=<?php echo urlencode($numeroDetalles["Revista"]["value"]); ?>'">Volver
                            a la revista</button>
                    </section>

                </div>
            </div>


            <!-- Contenedor inferior: Tablas top -->
            <section class="revista-top-tables-container">
                <!-- Columna 1: Top Autores -->
                <div class="revista-col-tablas-top">
                    <table class="tabla-grupos tabla-autowidth">
                        <thead>
                            <tr>
                                <th colspan="3">Top 5 Autores</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Ejecutar la consulta para obtener el top de autores por número
                            $topAutores = $queryDispatcher->query(obtenerTopAutoresPorNumero($numeroURI));
                            foreach ($topAutores["results"]["bindings"] as $index => $autor): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td> <!-- Posición del autor en el top -->
                                    <td><a href="autor.php?autor=<?php echo urlencode($autor['Autor']['value']); ?>">
                            <?php echo htmlspecialchars($autor['AutorLabel']['value']); ?>
                        </a></td> <!-- Nombre del autor -->
                                    <td><?php echo isset($autor["numArticulos"]["value"]) ? $autor["numArticulos"]["value"] : "0"; ?>
                                    </td> <!-- Número de artículos -->
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Columna 2: Top Palabras Clave -->
                <div class="revista-col-tablas-top">
                    <table class="tabla-grupos tabla-autowidth">
                        <thead>
                            <tr>
                                <th colspan="3">Top 5 Palabras Clave</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $topPalabras = $queryDispatcher->query(obtenerTopPalabrasClavePorNumero($numeroURI));
                            foreach ($topPalabras["results"]["bindings"] as $index => $palabra): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo $palabra["PalabraLabel"]["value"]; ?></td>
                                    <td><?php echo isset($palabra["frecuencia"]["value"]) ? $palabra["frecuencia"]["value"] : "0"; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Columna 3: Top Temas -->
                <div class="revista-col-tablas-top">
                    <table class="tabla-grupos tabla-autowidth">
                        <thead>
                            <tr>
                                <th colspan="3">Top 5 Temas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $topTemas = $queryDispatcher->query(obtenerTopTemasPorNumero($numeroURI));
                            foreach ($topTemas["results"]["bindings"] as $index => $tema): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <!-- Enlace al tema usando su URI -->
                                    <td>
                                        <?php if (isset($tema["Tema"]["value"]) && isset($tema["TemaLabel"]["value"])): ?>
                                            <a href="tema.php?tema=<?php echo urlencode($tema["Tema"]["value"]); ?>">
                                                <?php echo htmlspecialchars($tema["TemaLabel"]["value"]); ?>
                                            </a>
                                        <?php else: ?>
                                            No especificado
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo isset($tema["numArticulos"]["value"]) ? $tema["numArticulos"]["value"] : "0"; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </section>
        </section>

        <!-- Botones para cambiar entre tablas -->
        <section class="tabla-botones">
            <button
                onclick="location.href='numero.php?numero=<?php echo urlencode($numeroURI); ?>&tabla=articulos&resultadosPorPagina=<?php echo $resultadosPorPagina; ?>'">Artículos</button>
            <button
                onclick="location.href='numero.php?numero=<?php echo urlencode($numeroURI); ?>&tabla=autores&resultadosPorPagina=<?php echo $resultadosPorPagina; ?>'">Autores</button>
        </section>


        <h3 class="revista-titulo">

            <?php
            if ($tablaActual == 'autores') {
                echo "Autores del número ";
            } elseif ($tablaActual == 'articulos') {
                echo "Artículos del número ";
            }
            echo htmlspecialchars($numeroDetalles["NumeroLabel"]["value"]);
            ?>
        </h3>





        <!-- Tabla -->
        <table class="tabla-grupos tabla-autowidth">
            <thead>
                <?php if ($tablaActual === 'articulos'): ?>
                    <tr>
                        <th>
                            <a
                                href="numero.php?numero=<?php echo urlencode($numeroURI); ?>&tabla=articulos&orden=titulo&direccion=<?php echo (isset($_GET['direccion']) && $_GET['direccion'] === 'asc') ? 'desc' : 'asc'; ?>&pagina=<?php echo $paginaActual; ?>&resultadosPorPagina=<?php echo $resultadosPorPagina; ?>">
                                Título del Artículo
                                <?php echo (isset($_GET['orden']) && $_GET['orden'] === 'titulo') ? ($_GET['direccion'] === 'asc' ? '<span>&#9650;</span>' : '<span>&#9660;</span>') : '<span>&#8597;</span>'; ?>
                            </a>
                        </th>
                        <th>Autores</th>
                        <th>
                            <a
                                href="numero.php?numero=<?php echo urlencode($numeroURI); ?>&tabla=articulos&orden=fecha&direccion=<?php echo (isset($_GET['direccion']) && $_GET['direccion'] === 'asc') ? 'desc' : 'asc'; ?>&pagina=<?php echo $paginaActual; ?>&resultadosPorPagina=<?php echo $resultadosPorPagina; ?>">
                                Fecha
                                <?php echo (isset($_GET['orden']) && $_GET['orden'] === 'fecha') ? ($_GET['direccion'] === 'asc' ? '<span>&#9650;</span>' : '<span>&#9660;</span>') : '<span>&#8597;</span>'; ?>
                            </a>
                        </th>
                        <th>
                            <a
                                href="numero.php?numero=<?php echo urlencode($numeroURI); ?>&tabla=articulos&orden=tema&direccion=<?php echo (isset($_GET['direccion']) && $_GET['direccion'] === 'asc') ? 'desc' : 'asc'; ?>&pagina=<?php echo $paginaActual; ?>&resultadosPorPagina=<?php echo $resultadosPorPagina; ?>">
                                Tema
                                <?php echo (isset($_GET['orden']) && $_GET['orden'] === 'tema') ? ($_GET['direccion'] === 'asc' ? '<span>&#9650;</span>' : '<span>&#9660;</span>') : '<span>&#8597;</span>'; ?>
                            </a>
                        </th>
                    </tr>
                <?php else: ?>
                    <tr>
                        <th>Nombre del Autor</th>
                        <th>Número de Artículos</th>
                    </tr>
                <?php endif; ?>
            </thead>
            <tbody>
                <?php foreach ($tablaResultados as $resultado): ?>
                    <?php if ($tablaActual === 'articulos'): ?>
                        <tr>


                            <!-- Título del Artículo -->
                            <td><a href="articulo.php?articulo=<?php echo urlencode($resultado["Articulo"]["value"]); ?>">
                                    <?php echo htmlspecialchars(isset($resultado["ArticuloLabel"]["value"])
                                        ? $resultado["ArticuloLabel"]["value"] : "Sin título"); ?></a></td>


                            <td><?php echo isset($resultado["Autores"]["value"]) ? $resultado["Autores"]["value"] : "No especificado"; ?>
                            </td>
                            <td><?php echo isset($resultado["FechaPublicacion"]["value"]) ? $resultado["FechaPublicacion"]["value"] : "No especificada"; ?>
                            </td>
                            <td><?php echo isset($resultado["TemaLabel"]["value"]) ? $resultado["TemaLabel"]["value"] : "No especificado"; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td><a href="autor.php?autor=<?php echo urlencode($resultado['Autor']['value']); ?>">
            <?php echo htmlspecialchars($resultado['AutorLabel']['value']); ?>
        </a></td>
                            <td><?php echo intval($resultado["numArticulos"]["value"]); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Selector de resultados por página -->
        <div class="pagination-settings">
            <label for="resultadosPorPagina">Resultados por página:</label>
            <select id="resultadosPorPagina" name="resultadosPorPagina"
                onchange="location.href='numero.php?numero=<?php echo urlencode($numeroURI); ?>&tabla=<?php echo $tablaActual; ?>&resultadosPorPagina='+this.value">
                <option value="10" <?php if ($resultadosPorPagina == 10)
                    echo 'selected'; ?>>10</option>
                <option value="25" <?php if ($resultadosPorPagina == 25)
                    echo 'selected'; ?>>25</option>
                <option value="50" <?php if ($resultadosPorPagina == 50)
                    echo 'selected'; ?>>50</option>
                <option value="100" <?php if ($resultadosPorPagina == 100)
                    echo 'selected'; ?>>100</option>
            </select>
        </div>

        <!-- Paginación -->
        <div class="pagination">
            <?php
            // Número máximo de botones de página a mostrar
            $maxBotones = 5;

            // Calcular el inicio y el fin del rango de páginas a mostrar
            $inicio = max(1, $paginaActual - floor($maxBotones / 2));
            $fin = min($totalPaginas, $inicio + $maxBotones - 1);

            // Ajustar el inicio si el fin está cerca del total de páginas
            $inicio = max(1, $fin - $maxBotones + 1);

            // Botón "Primero"
            if ($paginaActual > 1) {
                echo "<a href='numero.php?numero=" . urlencode($numeroURI) . "&tabla=" . $tablaActual . "&pagina=1&resultadosPorPagina=" . $resultadosPorPagina . "' class='page-link'>|< Primero</a>";
            }

            // Mostrar los botones de página
            for ($i = $inicio; $i <= $fin; $i++) {
                echo "<a href='numero.php?numero=" . urlencode($numeroURI) . "&tabla=" . $tablaActual . "&pagina=" . $i . "&resultadosPorPagina=" . $resultadosPorPagina . "' class='page-link";
                if ($i == $paginaActual) {
                    echo " active";
                }
                echo "'>" . $i . "</a>";
            }

            // Botón "Último"
            if ($paginaActual < $totalPaginas) {
                echo "<a href='numero.php?numero=" . urlencode($numeroURI) . "&tabla=" . $tablaActual . "&pagina=" . $totalPaginas . "&resultadosPorPagina=" . $resultadosPorPagina . "' class='page-link'>Último >|</a>";
            }

            // Mostrar información de la página actual y el total de páginas
            echo "<span class='page-info'>Página " . $paginaActual . " de " . $totalPaginas . "</span>";
            ?>
        </div>



    </main>
    <?php renderFooter(); ?>
</body>

</html>