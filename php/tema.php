<?php
// Incluimos los archivos necesarios
include 'sparqlquerydispatcher.php';
include 'sparql_prefijos.php';
include 'tema_sparql.php';
include 'menu_principal.php';

// Verificamos si se ha pasado un tema en la URL
if (!isset($_GET['tema'])) {
    die("Error: No se especificó un tema.");
}

// Parámetros de paginación
$temaURI = urldecode($_GET['tema']);
$tablaActual = isset($_GET['tabla']) ? $_GET['tabla'] : 'articulos'; // articulos o autores
$resultadosPorPagina = isset($_GET['resultadosPorPagina']) ? intval($_GET['resultadosPorPagina']) : 10;
$paginaActual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;

// Definimos el endpoint SPARQL
include 'endpoint_url.php';
$queryDispatcher = new SPARQLQueryDispatcher($endpointUrl);

// Consulta para obtener los detalles del tema y su área temática
$sparqlQueryDetalles = obtenerDetallesTema($temaURI);
$queryResultsDetalles = $queryDispatcher->query($sparqlQueryDetalles);
// echo $sparqlQueryDetalles;

if (!empty($queryResultsDetalles["results"]["bindings"])) {
    $temaDetalles = $queryResultsDetalles["results"]["bindings"][0];
} else {
    die("Error: No se encontraron detalles para el tema especificado.");
}

// Calcular el offset
$offset = ($paginaActual - 1) * $resultadosPorPagina;

// Ejecutar la consulta según la tabla seleccionada
switch ($tablaActual) {
    case 'articulos':
        $sparqlQueryTabla = obtenerArticulosPorTema($temaURI, $offset, $resultadosPorPagina);
        break;
    case 'autores':
        $sparqlQueryTabla = obtenerAutoresPorTema($temaURI, $offset, $resultadosPorPagina);
        break;
    default:
        $sparqlQueryTabla = obtenerArticulosPorTema($temaURI, $offset, $resultadosPorPagina);
        $tablaActual = 'articulos';
        break;
}

$queryResultsTabla = $queryDispatcher->query($sparqlQueryTabla);

if (!empty($queryResultsTabla["results"]["bindings"])) {
    $tablaResultados = $queryResultsTabla["results"]["bindings"];
} else {
    $tablaResultados = [];
}

// Contar el total de resultados para la paginación
$sparqlQueryTotal = contarTotalResultadosPorTema($temaURI, $tablaActual);
$queryResultsTotal = $queryDispatcher->query($sparqlQueryTotal);

$totalResultados = !empty($queryResultsTotal["results"]["bindings"]) 
                    ? intval($queryResultsTotal["results"]["bindings"][0]["total"]["value"]) 
                    : 0;
$totalPaginas = ceil($totalResultados / max(1, $resultadosPorPagina));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($temaDetalles["TemaLabel"]["value"]); ?></title>
    <link rel="stylesheet" href="estilos_general.css">
    <link rel="stylesheet" href="estilos_tema.css">
</head>
<body>
<?php renderMenu('temas-lista.php'); ?>

<main class="tema-detalles">
    <!-- Título del tema -->
    <section class="titulo-tema">
        <h2><?php echo htmlspecialchars($temaDetalles["TemaLabel"]["value"]); ?></h2>
        <h3>Área temática: <?php echo htmlspecialchars($temaDetalles["AreaLabel"]["value"]); ?></h3>
    </section>

    <!-- Botones para cambiar entre tablas -->
    <div class="tabla-botones">
        <button onclick="location.href='tema.php?tema=<?php echo urlencode($temaURI); ?>&tabla=articulos&resultadosPorPagina=<?php echo $resultadosPorPagina; ?>'">Artículos</button>
        <button onclick="location.href='tema.php?tema=<?php echo urlencode($temaURI); ?>&tabla=autores&resultadosPorPagina=<?php echo $resultadosPorPagina; ?>'">Autores</button>
    </div>

    <!-- Tabla -->
    <table class="tabla-grupos tabla-autowidth">
    <thead>
        <?php if ($tablaActual === 'articulos'): ?>
            <tr>
                <th>Revista</th>
                <th>Número</th>
                <th>Título del Artículo</th>
                <th>Autores</th>
                <th>Fecha</th>
            </tr>
        <?php else: ?>
            <tr>
                <th>Nombre del Autor</th>
                <th>Número de Artículos (en este Tema)</th>
                <th>Número Total de Artículos</th>
            </tr>
        <?php endif; ?>
    </thead>
    <tbody>
        <?php foreach ($tablaResultados as $resultado): ?>
            <?php if ($tablaActual === 'articulos'): ?>
                <tr>
                    <!-- Revista -->
                    <td><?php echo htmlspecialchars(isset($resultado["RevistaLabel"]["value"]) ? $resultado["RevistaLabel"]["value"] : "No especificada"); ?></td>

                    <!-- Número -->
                    <td><?php echo htmlspecialchars(isset($resultado["NumeroVolumen"]["value"]) ? $resultado["NumeroVolumen"]["value"] : "No especificado"); ?></td>

                    <!-- Título del Artículo -->
                    <!-- Título del Artículo -->
                    <td><a href="articulo.php?articulo=<?php echo urlencode($resultado["Articulo"]["value"]); ?>">
                                    <?php echo htmlspecialchars(isset($resultado["ArticuloLabel"]["value"])
                                        ? $resultado["ArticuloLabel"]["value"] : "Sin título"); ?></a></td>

                    <!-- Autores -->
                    <td><?php echo htmlspecialchars($resultado["Autores"]["value"]); ?></td>

                    <!-- Fecha -->
                    <td><?php echo isset($resultado["FechaPublicacion"]["value"]) ? htmlspecialchars($resultado["FechaPublicacion"]["value"]) : "No especificada"; ?></td>
                </tr>
            <?php else: ?>
                <tr>
                    <!-- Nombre del Autor -->
                    <td><?php echo htmlspecialchars($resultado["AutorLabel"]["value"]); ?></td>

                    <!-- Número de Artículos en este Tema -->
                    <td><?php echo intval($resultado["numArticulosTema"]["value"]); ?></td>

                    <!-- Número Total de Artículos -->
                    <td><?php echo intval($resultado["numArticulosTotal"]["value"]); ?></td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </tbody>
</table>


 <!-- Selector de cantidad de resultados por página -->
 <div class="pagination-settings">
        <label for="resultadosPorPagina">Resultados por página:</label>
        <select id="resultadosPorPagina" name="resultadosPorPagina" onchange="location.href='tema.php?tema=<?php echo urlencode($temaURI); ?>&tabla=<?php echo $tablaActual; ?>&pagina=1&resultadosPorPagina='+this.value">
            <option value="10" <?php if ($resultadosPorPagina == 10) echo 'selected'; ?>>10</option>
            <option value="25" <?php if ($resultadosPorPagina == 25) echo 'selected'; ?>>25</option>
            <option value="50" <?php if ($resultadosPorPagina == 50) echo 'selected'; ?>>50</option>
            <option value="100" <?php if ($resultadosPorPagina == 100) echo 'selected'; ?>>100</option>
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
            echo "<a href='tema.php?tema=" . urlencode($temaURI) . "&tabla=" . $tablaActual . "&pagina=1&resultadosPorPagina=" . $resultadosPorPagina . "' class='page-link'>|< Primero</a>";
        }

        // Mostrar los botones de página
        for ($i = $inicio; $i <= $fin; $i++) {
            echo "<a href='tema.php?tema=" . urlencode($temaURI) . "&tabla=" . $tablaActual . "&pagina=" . $i . "&resultadosPorPagina=" . $resultadosPorPagina . "' class='page-link";
            if ($i == $paginaActual) {
                echo " active";
            }
            echo "'>" . $i . "</a>";
        }

        // Botón "Último"
        if ($paginaActual < $totalPaginas) {
            echo "<a href='tema.php?tema=" . urlencode($temaURI) . "&tabla=" . $tablaActual . "&pagina=" . $totalPaginas . "&resultadosPorPagina=" . $resultadosPorPagina . "' class='page-link'>Último >|</a>";
        }

        // Mostrar información de la página actual y el total de páginas
        echo "<span class='page-info'>Página " . $paginaActual . " de " . max(1, $totalPaginas) . "</span>";
        ?>
    </div>

</main>

<?php renderFooter(); ?>
</body>
</html>
