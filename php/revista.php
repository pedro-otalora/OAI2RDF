<!-- revistas.php -->
<?php
// Incluimos los archivos necesarios
include 'sparqlquerydispatcher.php';
include 'sparql_prefijos.php';
include 'revista_sparql.php';
include 'menu_principal.php';

// Verificamos si se ha pasado una revista en la URL
if (!isset($_GET['revista'])) {
    die("Error: No se especificó una revista.");
}

// Parámetros de paginación
$revistaURI = urldecode($_GET['revista']);
$tablaActual = isset($_GET['tabla']) ? $_GET['tabla'] : 'numeros'; // numeros, autores, articulos
$resultadosPorPagina = isset($_GET['resultadosPorPagina']) ? intval($_GET['resultadosPorPagina']) : 10;
$paginaActual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;

// Capturar los parámetros de ordenación
$orden = isset($_GET['orden']) ? $_GET['orden'] : null;
$direccion = isset($_GET['direccion']) && in_array($_GET['direccion'], ['asc', 'desc']) ? $_GET['direccion'] : null;

// Definimos el endpoint SPARQL
include 'endpoint_url.php';
$queryDispatcher = new SPARQLQueryDispatcher($endpointUrl);

// Ejecutamos la consulta para obtener los detalles de la revista
$sparqlQueryDetalles = obtenerDetallesRevista($revistaURI);
$queryResultsDetalles = $queryDispatcher->query($sparqlQueryDetalles);
$revistaDetalles = $queryResultsDetalles["results"]["bindings"][0];

// Consulta para obtener los números de la revista
$sparqlQueryNumeros = contarNumerosPorRevistaEspecifica($revistaURI);
$queryResultsNumeros = $queryDispatcher->query($sparqlQueryNumeros);
$numNumeros = isset($queryResultsNumeros["results"]["bindings"][0]["numNumeros"]["value"])
    ? intval($queryResultsNumeros["results"]["bindings"][0]["numNumeros"]["value"])
    : 0;

// Consulta para obtener los artículos de la revista
$sparqlQueryArticulos = contarArticulosPorRevistaEspecifica($revistaURI);
$queryResultsArticulos = $queryDispatcher->query($sparqlQueryArticulos);
$numArticulos = isset($queryResultsArticulos["results"]["bindings"][0]["numArticulos"]["value"])
    ? intval($queryResultsArticulos["results"]["bindings"][0]["numArticulos"]["value"])
    : 0;

// Consulta para obtener el total de autores de la revista
$sparqlQueryTotalAutores = contarAutoresPorRevista($revistaURI);
$queryResultsTotalAutores = $queryDispatcher->query($sparqlQueryTotalAutores);
$numTotalAutores = isset($queryResultsTotalAutores["results"]["bindings"][0]["totalAutores"]["value"])
    ? intval($queryResultsTotalAutores["results"]["bindings"][0]["totalAutores"]["value"])
    : 0;

// Calcular el offset
$offset = ($paginaActual - 1) * $resultadosPorPagina;

// Ejecutar la consulta según la tabla seleccionada
switch ($tablaActual) {
    case 'numeros':
        $sparqlQueryTabla = obtenerNumerosRevista($revistaURI, $offset, $resultadosPorPagina);
        $queryResultsTabla = $queryDispatcher->query($sparqlQueryTabla);
        $tablaResultados = $queryResultsTabla["results"]["bindings"];
        break;
    case 'autores':
        $sparqlQueryTabla = obtenerAutoresRevista($revistaURI, $offset, $resultadosPorPagina);
        $queryResultsTabla = $queryDispatcher->query($sparqlQueryTabla);
        $tablaResultados = $queryResultsTabla["results"]["bindings"];
        break;
    case 'articulos':
        $sparqlQueryTabla = obtenerArticulosRevista($revistaURI, $offset, $resultadosPorPagina, $orden, $direccion);
        $queryResultsTabla = $queryDispatcher->query($sparqlQueryTabla);
        $tablaResultados = $queryResultsTabla["results"]["bindings"];
        break;
    default:
        $sparqlQueryTabla = obtenerNumerosRevista($revistaURI, $offset, $resultadosPorPagina);
        $queryResultsTabla = $queryDispatcher->query($sparqlQueryTabla);
        $tablaResultados = $queryResultsTabla["results"]["bindings"];
        $tablaActual = 'numeros';
        break;
}

// Contar el total de resultados para la paginación
function contarTotalResultados($revistaURI, $tabla)
{
    global $sparqlPrefijos;
    switch ($tabla) {
        case 'numeros':
            $query = $sparqlPrefijos . "
                SELECT (COUNT(DISTINCT ?Numero) AS ?total)
                WHERE {
                    ?Numero a ontorevistas:Numero ;
                            ontorevistas:esParteDeRevista <$revistaURI> .
                }
            ";
            break;
        case 'autores':
            $query = $sparqlPrefijos . "
                SELECT (COUNT(DISTINCT ?Autor) AS ?total)
                WHERE {
                    ?Numero ontorevistas:esParteDeRevista <$revistaURI> .
                    ?Articulo ontorevistas:esParteDeNumero ?Numero ;
                              ontorevistas:tieneAutor ?Autor .
                }
            ";
            break;
        case 'articulos':
            $query = $sparqlPrefijos . "
                SELECT (COUNT(DISTINCT ?Articulo) AS ?total)
                WHERE {
                    ?Numero ontorevistas:esParteDeRevista <$revistaURI> .
                    ?Articulo ontorevistas:esParteDeNumero ?Numero .
                }
            ";
            break;
        default:
            return 0;
    }
    return $query;
}

$sparqlQueryTotal = contarTotalResultados($revistaURI, $tablaActual);
$queryResultsTotal = $queryDispatcher->query($sparqlQueryTotal);
$totalResultados = $queryResultsTotal["results"]["bindings"][0]["total"]["value"];
$totalPaginas = ceil($totalResultados / $resultadosPorPagina);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $revistaDetalles["RevistaLabel"]["value"]; ?></title>
    <link rel="stylesheet" href="estilos_general.css">
    <link rel="stylesheet" href="estilos_revista.css">
</head>

<body>
    <?php renderMenu('revistas-lista.php'); ?>
    <!-- Contenido principal -->
    <main class="revista-detalles">

        <div class="page-label">Revista</div>

        <section class="revista-titulo">
            <h2><?php echo $revistaDetalles["RevistaLabel"]["value"]; ?></h2>
        </section>
        <!-- Nueva estructura para los detalles de la revista -->
        <section class="revista-detalles-grid">
            <!-- Contenedor Superior: Imagen y Tabla de Datos -->
            <div class="revista-info-container">
                <!-- Columna 1: Imagen -->
                <div class="revista-col-imagen">
                    <?php if (isset($revistaDetalles["RevistaImagen"]["value"])): ?>
                        <img src="<?php echo $revistaDetalles["RevistaImagen"]["value"]; ?>"
                            alt="<?php echo $revistaDetalles["RevistaLabel"]["value"]; ?>" class="imagen-fija">
                    <?php endif; ?>
                </div>

                <!-- Columna 2: Tabla de datos -->
                <div class="revista-col-datos">
                    <table class="tabla-revista-info">
                        <thead>
                            <tr>
                                <th colspan="2"><?php echo $revistaDetalles["RevistaLabel"]["value"]; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>ISSN:</strong></td>
                                <td><?php echo isset($revistaDetalles["ISSN"]["value"]) ? $revistaDetalles["ISSN"]["value"] : "No especificado"; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>e-ISSN:</strong></td>
                                <td><?php echo isset($revistaDetalles["eISSN"]["value"]) ? $revistaDetalles["eISSN"]["value"] : "No especificado"; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>DOI:</strong></td>
                                <td><?php if (isset($revistaDetalles["DOI"]["value"])): ?>
                                        <a href="https://doi.org/<?php echo $revistaDetalles["DOI"]["value"]; ?>"
                                            target="_blank"
                                            class="enlace-subrayado"><?php echo $revistaDetalles["DOI"]["value"]; ?></a>
                                    <?php else: ?>
                                        No especificado
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Web:</strong></td>
                                <td>
                                    <?php if (isset($revistaDetalles["Enlace"]["value"])): ?>
                                        <a href="<?php echo $revistaDetalles["Enlace"]["value"]; ?>" target="_blank"
                                            class="enlace-subrayado">
                                            <?php echo $revistaDetalles["Enlace"]["value"]; ?>
                                        </a>
                                    <?php else: ?>
                                        No disponible
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Total números:</strong></td>
                                <td><?php echo $numNumeros; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Total artículos:</strong></td>
                                <td><?php echo $numArticulos; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Total autores:</strong></td>
                                <td><?php echo $numTotalAutores; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Contenedor Inferior: Tablas Top -->
            <div class="revista-top-tables-container">
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
                            $topAutores = $queryDispatcher->query(obtenerTopAutoresRevista($revistaURI));
                            foreach ($topAutores["results"]["bindings"] as $index => $autor): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><a href="autor.php?autor=<?php echo urlencode($autor['Autor']['value']); ?>">
                            <?php echo htmlspecialchars($autor['AutorLabel']['value']); ?>
                        </a></td>
                                    <td><?php echo $autor["numArticulos"]["value"]; ?> artículos</td>
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
                                <th colspan="3">Top 5 Palabras clave</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $topPalabras = $queryDispatcher->query(obtenerTopPalabrasClaveRevista($revistaURI));
                            foreach ($topPalabras["results"]["bindings"] as $index => $palabra): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo $palabra["PalabraLabel"]["value"]; ?></td>
                                    <td><?php echo $palabra["frecuencia"]["value"]; ?> artículos</td>
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
                            $topTemas = $queryDispatcher->query(obtenerTopTemasRevista($revistaURI));
                            foreach ($topTemas["results"]["bindings"] as $index => $tema): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <!-- Enlace al tema usando su URI -->
                                    <td>
                                        <a href="tema.php?tema=<?php echo urlencode($tema["Tema"]["value"]); ?>">
                                            <?php echo htmlspecialchars($tema["TemaLabel"]["value"]); ?>
                                        </a>
                                    </td>
                                    <td><?php echo $tema["numArticulos"]["value"]; ?> artículos</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>



        <section class="revista-tabla">

            <div class="tabla-botones">
                <button
                    onclick="location.href='revista.php?revista=<?php echo urlencode($revistaURI); ?>&tabla=numeros&resultadosPorPagina=<?php echo $resultadosPorPagina; ?>'">Números</button>
                <button
                    onclick="location.href='revista.php?revista=<?php echo urlencode($revistaURI); ?>&tabla=autores&resultadosPorPagina=<?php echo $resultadosPorPagina; ?>'">Autores</button>
                <button
                    onclick="location.href='revista.php?revista=<?php echo urlencode($revistaURI); ?>&tabla=articulos&resultadosPorPagina=<?php echo $resultadosPorPagina; ?>'">Artículos</button>
            </div>

        </section>

        <h3 class="revista-titulo">

            <?php
            if ($tablaActual == 'numeros') {
                echo "Números de la revista ";
            } elseif ($tablaActual == 'autores') {
                echo "Autores de la revista ";
            } elseif ($tablaActual == 'articulos') {
                echo "Artículos de la revista ";
            }
            echo htmlspecialchars($revistaDetalles["RevistaLabel"]["value"]);
            ?>
        </h3>




        <!-- Tabla -->
        <table class="tabla-grupos tabla-autowidth">
            <thead>
                <tr>
                    <?php if ($tablaActual == 'numeros'): ?>
                        <th>Número</th>
                        <th>Artículos</th>
                    <?php elseif ($tablaActual == 'autores'): ?>
                        <th>Autor</th>
                        <th>Artículos</th>
                    <?php elseif ($tablaActual == 'articulos'): ?>
                        <th>Número</th> <!-- Nueva columna -->
                        <th>
                            <a
                                href="revista.php?revista=<?php echo urlencode($revistaURI); ?>&tabla=articulos&orden=titulo&direccion=<?php echo (isset($_GET['direccion']) && $_GET['direccion'] === 'asc') ? 'desc' : 'asc'; ?>&pagina=<?php echo $paginaActual; ?>&resultadosPorPagina=<?php echo $resultadosPorPagina; ?>">
                                Título del Artículo
                                <?php echo (isset($_GET['orden']) && $_GET['orden'] === 'titulo') ? ($_GET['direccion'] === 'asc' ? '<span>&#9650;</span>' : '<span>&#9660;</span>') : '<span>&#8597;</span>'; ?>
                            </a>
                        </th>
                        <th>Autores</th>
                        <th>
                            <a
                                href="revista.php?revista=<?php echo urlencode($revistaURI); ?>&tabla=articulos&orden=fecha&direccion=<?php echo (isset($_GET['direccion']) && $_GET['direccion'] === 'asc') ? 'desc' : 'asc'; ?>&pagina=<?php echo $paginaActual; ?>&resultadosPorPagina=<?php echo $resultadosPorPagina; ?>">
                                Fecha
                                <?php echo (isset($_GET['orden']) && $_GET['orden'] === 'fecha') ? ($_GET['direccion'] === 'asc' ? '<span>&#9650;</span>' : '<span>&#9660;</span>') : '<span>&#8597;</span>'; ?>
                            </a>
                        </th>
                        <th>
                            <a
                                href="revista.php?revista=<?php echo urlencode($revistaURI); ?>&tabla=articulos&orden=tema&direccion=<?php echo (isset($_GET['direccion']) && $_GET['direccion'] === 'asc') ? 'desc' : 'asc'; ?>&pagina=<?php echo $paginaActual; ?>&resultadosPorPagina=<?php echo $resultadosPorPagina; ?>">
                                Tema
                                <?php echo (isset($_GET['orden']) && $_GET['orden'] === 'tema') ? ($_GET['direccion'] === 'asc' ? '<span>&#9650;</span>' : '<span>&#9660;</span>') : '<span>&#8597;</span>'; ?>
                            </a>
                        </th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tablaResultados as $resultado): ?>
                    <tr>
                        <?php if ($tablaActual == 'numeros'): ?>
                            <td>
                                <a href="numero.php?numero=<?php echo urlencode($resultado["Numero"]["value"]); ?>">
                                    <?php echo $resultado["NumeroLabel"]["value"]; ?>
                                </a>
                            </td>
                            <td><?php echo intval($resultado["numArticulos"]["value"]); ?></td>
                        <?php elseif ($tablaActual == 'autores'): ?>
                            <td>
        <a href="autor.php?autor=<?php echo urlencode($resultado['Autor']['value']); ?>">
            <?php echo htmlspecialchars($resultado['AutorLabel']['value']); ?>
        </a>
    </td>
                            <td><?php echo intval($resultado["numArticulos"]["value"]); ?></td>
                        <?php elseif ($tablaActual == 'articulos'): ?>
                            <!-- Nueva columna con el valor del número -->
                            <td><?php echo isset($resultado["NumeroVolumen"]["value"]) ? htmlspecialchars($resultado["NumeroVolumen"]["value"]) : "No especificado"; ?>
                            </td>


                            <!-- Título del Artículo -->
                            <td><a href="articulo.php?articulo=<?php echo urlencode($resultado["Articulo"]["value"]); ?>">
                                    <?php echo htmlspecialchars(isset($resultado["ArticuloLabel"]["value"])
                                        ? $resultado["ArticuloLabel"]["value"] : "Sin título"); ?></a></td>

                            <td><?php echo $resultado["Autores"]["value"]; ?></td>
                            <td><?php echo isset($resultado["FechaPublicacion"]["value"]) ? $resultado["FechaPublicacion"]["value"] : "No especificada"; ?>
                            </td>
                            <td>
                                <?php if (isset($resultado["Tema"]["value"]) && isset($resultado["TemaLabel"]["value"])): ?>
                                    <a href="tema.php?tema=<?php echo urlencode($resultado["Tema"]["value"]); ?>">
                                        <?php echo htmlspecialchars($resultado["TemaLabel"]["value"]); ?>
                                    </a>
                                <?php else: ?>
                                    No especificado
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>


        <!-- Selector de resultados por página -->
        <div class="pagination-settings">
            <label for="resultadosPorPagina">Resultados por página:</label>
            <select id="resultadosPorPagina" name="resultadosPorPagina"
                onchange="location.href='revista.php?revista=<?php echo urlencode($revistaURI); ?>&tabla=<?php echo $tablaActual; ?>&resultadosPorPagina='+this.value">
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

            // Botón "Primero" con icono
            if ($paginaActual > 1) {
                echo "<a href='revista.php?revista=" . urlencode($revistaURI) . "&tabla=" . $tablaActual . "&pagina=1&resultadosPorPagina=" . $resultadosPorPagina . "' class='page-link'>|< Primero</a>";
            }

            // Mostrar los botones de página
            for ($i = $inicio; $i <= $fin; $i++) {
                echo "<a href='revista.php?revista=" . urlencode($revistaURI) . "&tabla=" . $tablaActual . "&pagina=" . $i . "&resultadosPorPagina=" . $resultadosPorPagina . "' class='page-link";
                if ($i == $paginaActual) {
                    echo " active";
                }
                echo "'>" . $i . "</a>";
            }

            // Botón "Último" con icono
            if ($paginaActual < $totalPaginas) {
                echo "<a href='revista.php?revista=" . urlencode($revistaURI) . "&tabla=" . $tablaActual . "&pagina=" . $totalPaginas . "&resultadosPorPagina=" . $resultadosPorPagina . "' class='page-link'>Último >|</a>";
            }

            // Mostrar información de la página actual y el total de páginas
            echo "<span class='page-info'>Página " . $paginaActual . " de " . $totalPaginas . "</span>";
            ?>
        </div>

        </section>
    </main>
    <?php renderFooter(); ?>
</body>

</html>