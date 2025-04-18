<?php
// Incluimos los archivos necesarios
include 'sparqlquerydispatcher.php';
include 'sparql_prefijos.php';
include 'articulo_sparql.php';
include 'menu_principal.php';

// Validamos el parámetro 'articulo'
if (!isset($_GET['articulo']) || empty($_GET['articulo'])) {
    die("Error: No se especificó un artículo válido.");
}




$articuloURI = urldecode($_GET['articulo']);
include 'endpoint_url.php';
$queryDispatcher = new SPARQLQueryDispatcher($endpointUrl);


// Configuración de paginación
$resultadosPorPagina = isset($_GET['resultadosPorPagina']) ? intval($_GET['resultadosPorPagina']) : 10;
$paginaActual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($paginaActual - 1) * $resultadosPorPagina;

// Consulta para obtener los artículos relacionados
$sparqlQueryRelacionados = obtenerArticulosRelacionados($articuloURI, $offset, $resultadosPorPagina);
$queryResultsRelacionados = $queryDispatcher->query($sparqlQueryRelacionados);

// Procesar resultados
$relacionadosResultados = isset($queryResultsRelacionados["results"]["bindings"]) 
    ? $queryResultsRelacionados["results"]["bindings"] 
    : [];

// Obtener el total de artículos relacionados (para calcular el número total de páginas)
$sparqlQueryTotalRelacionados = obtenerTotalArticulosRelacionados($articuloURI);
$queryResultsTotalRelacionados = $queryDispatcher->query($sparqlQueryTotalRelacionados);
$totalArticulosRelacionados = isset($queryResultsTotalRelacionados["results"]["bindings"][0]["total"]["value"]) 
    ? intval($queryResultsTotalRelacionados["results"]["bindings"][0]["total"]["value"]) 
    : 0;

$totalPaginas = ceil($totalArticulosRelacionados / $resultadosPorPagina);






// Consulta para obtener los detalles del artículo
$sparqlQueryDetalles = obtenerDetallesArticulo($articuloURI);
$queryResultsDetalles = $queryDispatcher->query($sparqlQueryDetalles);

// Validamos si hay resultados antes de acceder al índice
if (!isset($queryResultsDetalles["results"]["bindings"]) || empty($queryResultsDetalles["results"]["bindings"])) {
    die("Error: No se encontraron datos para el artículo especificado.");
}

$articuloDetalles = $queryResultsDetalles["results"]["bindings"][0];

// Consulta para obtener los detalles de la revista asociada al número del artículo
$numeroURI = $articuloDetalles["Numero"]["value"];
$sparqlQueryRevista = obtenerDetallesRevista($numeroURI);
$queryResultsRevista = $queryDispatcher->query($sparqlQueryRevista);

// Validamos si hay resultados para la revista
if (!isset($queryResultsRevista["results"]["bindings"]) || empty($queryResultsRevista["results"]["bindings"])) {
    die("Error: No se encontraron datos para la revista asociada al número especificado.");
}

$revistaDetalles = $queryResultsRevista["results"]["bindings"][0];


// Consulta para obtener los artículos relacionados
//$sparqlQueryRelacionados = obtenerArticulosRelacionados($articuloURI);
//$queryResultsRelacionados = $queryDispatcher->query($sparqlQueryRelacionados);

// Procesamos los resultados de artículos relacionados
$relacionadosResultados = isset($queryResultsRelacionados["results"]["bindings"])
    ? $queryResultsRelacionados["results"]["bindings"]
    : [];

?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($articuloDetalles["ArticuloLabel"]["value"]); ?></title>
    <link rel="stylesheet" href="estilos_general.css">
    <link rel="stylesheet" href="estilos_revista.css">
    <link rel="stylesheet" href="estilos_articulo.css">
</head>

<body>
    <?php renderMenu('revistas-lista.php'); ?>
    <main class="articulo-detalles">

    <div class="page-label">Artículo</div>


        <!-- Título del artículo -->
        <section class="articulo-titulo">
            <h2><?php echo htmlspecialchars($articuloDetalles["ArticuloLabel"]["value"]); ?></h2>
        </section>

        <!-- Contenedor superior: Imagen y tabla -->
        <section class="articulo-detalles-grid">
            <!-- Columna izquierda: Imagen -->
            <div class="articulo-col-imagen"> <?php if (isset($articuloDetalles["NumeroImagen"]["value"])): ?> <img
                        src="<?php echo htmlspecialchars($articuloDetalles["NumeroImagen"]["value"]); ?>"
                        alt="Imagen del Número"> <?php elseif (isset($articuloDetalles["revistaImagen"]["value"])): ?> <img
                        src="<?php echo htmlspecialchars($articuloDetalles["revistaImagen"]["value"]); ?>"
                        alt="Imagen de la Revista"> <?php else: ?>
                    <p>No especificada</p> <?php endif; ?>
            </div>

            <!-- Columna derecha: Detalles -->
            <div class="articulo-col-datos">
                <table class="tabla-articulo-info">
                    <tbody>
                        <tr>
                            <td><strong>Revista:</strong></td>
                            <td><?php echo isset($revistaDetalles["RevistaLabel"]["value"]) ?
                                htmlspecialchars($revistaDetalles["RevistaLabel"]["value"]) : "No especificada"; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Volumen:</strong></td>
                            <td><?php echo isset($articuloDetalles["NumeroVolumen"]["value"]) ?
                                htmlspecialchars($articuloDetalles["NumeroVolumen"]["value"]) : "No especificado"; ?>
                            </td>
                        </tr>
                        <tr>
    <td><strong>Autores:</strong></td>
    <td>
        <?php 
        if (!empty($queryResultsDetalles["results"]["bindings"])) {
            $autores = [];
            
            foreach ($queryResultsDetalles["results"]["bindings"] as $autor) {
                if (isset($autor["Autor"]["value"]) && isset($autor["AutorLabel"]["value"])) {
                    $autorURI = $autor["Autor"]["value"];
                    $autorLabel = $autor["AutorLabel"]["value"];
                    // Generamos el enlace usando URI para href y etiqueta para el texto
                    $autores[] = '<a href="autor.php?autor=' . urlencode($autorURI) . '">' . htmlspecialchars($autorLabel) . '</a>';
                }
            }
            
            // Unimos los enlaces con comas para mostrar en el HTML
            echo implode(", ", $autores);
        } else {
            echo "No especificados";
        }
        ?>
    </td>
</tr>

                        <tr>
                            <td><strong>Palabras clave:</strong></td>
                            <td><?php echo isset($articuloDetalles["PalabrasClave"]["value"]) ?
                                htmlspecialchars($articuloDetalles["PalabrasClave"]["value"]) : "No especificadas"; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Fecha de Publicación:</strong></td>
                            <td><?php echo isset($articuloDetalles["FechaPublicacion"]["value"]) ?
                                htmlspecialchars($articuloDetalles["FechaPublicacion"]["value"]) : "No especificada"; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Idioma:</strong></td>
                            <td><?php echo isset($articuloDetalles["Idioma"]["value"]) ?
                                htmlspecialchars($articuloDetalles["Idioma"]["value"]) : "No especificado"; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Ref. Bibliográfica:</strong></td>
                            <td><?php echo isset($articuloDetalles["ArticuloCita"]["value"]) ?
                                htmlspecialchars($articuloDetalles["ArticuloCita"]["value"]) : "No especificada"; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Editor:</strong></td>
                            <td><?php echo isset($articuloDetalles["Editor"]["value"]) ?
                                htmlspecialchars($articuloDetalles["Editor"]["value"]) : "No especificado"; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tema:</strong></td>
                            <td><?php echo isset($articuloDetalles["TemaLabel"]["value"]) ?
                                htmlspecialchars($articuloDetalles["TemaLabel"]["value"]) : "(sin clasificación)"; ?></td>
                        </tr>

                        <?php if (isset($articuloDetalles["DOI"]["value"]) && !empty($articuloDetalles["DOI"]["value"])): ?>
    <tr>
        <td><strong>DOI:</strong></td>
        <td>
            <a href="<?php echo htmlspecialchars(explode(", ", $articuloDetalles["DOI"]["value"])[0]); ?>"
                target="_blank">
                <?php echo htmlspecialchars(explode(", ", $articuloDetalles["DOI"]["value"])[0]); ?>
            </a>
        </td>
    </tr>
<?php endif; ?>


                        <tr>
                            <td><strong>Web del Artículo:</strong></td>
                            <td>
                                <?php if (isset($articuloDetalles["URLs"]["value"])): ?>
                                    <a href="<?php echo htmlspecialchars(explode(", ", $articuloDetalles["URLs"]["value"])[0]); ?>"
                                        target="_blank">
                                        <?php echo htmlspecialchars(explode(", ", $articuloDetalles["URLs"]["value"])[0]); ?>
                                    </a>
                                <?php else: ?>
                                    No especificada
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Texto completo:</strong></td>
                            <td>
                                <?php if (isset($articuloDetalles["Recursos"]["value"])): ?>
                                    <a href="<?php echo htmlspecialchars(explode(", ", $articuloDetalles["Recursos"]["value"])[0]); ?>"
                                        target="_blank">
                                        <?php echo htmlspecialchars(explode(", ", $articuloDetalles["Recursos"]["value"])[0]); ?>
                                    </a>
                                <?php else: ?>
                                    No especificado
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </section>

        <!-- Resumen -->
        <?php if (isset($articuloDetalles["ArticuloResumen"]["value"])): ?>
            <section class="resumen-articulo">
                <h3>Resumen</h3>
                <p><?php echo htmlspecialchars($articuloDetalles["ArticuloResumen"]["value"]); ?></p>
            </section>
        <?php else: ?>
            <p>No hay resumen disponible.</p>
        <?php endif; ?>




        <!-- Tabla de artículos relacionados -->
        <section class="tabla-articulos-relacionados">
    <h3>Más artículos del tema <?php echo isset($articuloDetalles["TemaLabel"]["value"]) ?
        htmlspecialchars($articuloDetalles["TemaLabel"]["value"]) : "(sin clasificación)"; ?> 
        (<?php echo $totalArticulosRelacionados; ?>)</h3>
            <table class="tabla-grupos tabla-autowidth">
                <thead>
                    <tr>
                        <th>Revista</th>
                        <th>Número</th>
                        <th>Título</th>
                        <th>Autores</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($relacionadosResultados as $resultado): ?>
                        <tr>
                            <!-- Revista -->
                            <td><?php echo htmlspecialchars(isset($resultado["revistaLabel"]["value"])
                                ? $resultado["revistaLabel"]["value"] : "No especificada"); ?></td>

                            <!-- Número -->
                            <td><?php echo htmlspecialchars(isset($resultado["numeroVolumen"]["value"])
                                ? str_replace("http://gicd.inf.um.es/wd/datarevistas/", "", $resultado["numeroVolumen"]["value"]) : "No especificado"); ?>
                            </td>

                            <!-- Título del Artículo -->
                            <td><a href="articulo.php?articulo=<?php echo urlencode($resultado["articulo"]["value"]); ?>">
                                    <?php echo htmlspecialchars(isset($resultado["articuloLabel"]["value"])
                                        ? $resultado["articuloLabel"]["value"] : "Sin título"); ?></a></td>

                            <!-- Autores -->
                            <td><?php echo htmlspecialchars(isset($resultado["autores"]["value"])
                                ? $resultado["autores"]["value"] : "No especificados"); ?></td>

                            <!-- Fecha -->
                            <td><?php echo htmlspecialchars(isset($resultado["fechaPublicacion"]["value"])
                                ? $resultado["fechaPublicacion"]["value"] : "No especificada"); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>


<!-- Selector de resultados por página -->
<div class="pagination-settings">
        <label for="resultadosPorPagina">Resultados por página:</label>
        <select id="resultadosPorPagina" name="resultadosPorPagina"
            onchange="location.href='articulo.php?articulo=<?php echo urlencode($articuloURI); ?>&resultadosPorPagina='+this.value">
            <option value="10" <?php if ($resultadosPorPagina == 10) echo 'selected'; ?>>10</option>
            <option value="25" <?php if ($resultadosPorPagina == 25) echo 'selected'; ?>>25</option>
            <option value="50" <?php if ($resultadosPorPagina == 50) echo 'selected'; ?>>50</option>
        </select>
    </div>

 <!-- Paginación -->
 <div class="pagination">
        <?php
        $maxBotones = 5;
        $inicio = max(1, $paginaActual - floor($maxBotones / 2));
        $fin = min($totalPaginas, $inicio + $maxBotones - 1);
        $inicio = max(1, $fin - $maxBotones + 1);

        if ($paginaActual > 1) {
            echo "<a href='articulo.php?articulo=" . urlencode($articuloURI) . "&pagina=1&resultadosPorPagina=" . $resultadosPorPagina . "' class='page-link'>|< Primero</a>";
        }

        for ($i = $inicio; $i <= $fin; $i++) {
            echo "<a href='articulo.php?articulo=" . urlencode($articuloURI) . "&pagina=" . $i . "&resultadosPorPagina=" . $resultadosPorPagina . "' class='page-link";
            if ($i == $paginaActual) {
                echo " active";
            }
            echo "'>" . $i . "</a>";
        }

        if ($paginaActual < $totalPaginas) {
            echo "<a href='articulo.php?articulo=" . urlencode($articuloURI) . "&pagina=" . $totalPaginas . "&resultadosPorPagina=" . $resultadosPorPagina . "' class='page-link'>Último >|</a>";
        }

        echo "<span class='page-info'>Página " . $paginaActual . " de " . $totalPaginas . "</span>";
        ?>
    </div>


</section>



    </main>

    <?php renderFooter(); ?>
</body>



</html>