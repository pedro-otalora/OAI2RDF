<?php
// Incluimos los archivos necesarios
include 'sparqlquerydispatcher.php';
include 'sparql_prefijos.php';
include 'menu_principal.php';
include 'autores-buscador_sparql.php';

// Parámetros de paginación y búsqueda
$resultadosPorPagina = isset($_GET['resultadosPorPagina']) ? intval($_GET['resultadosPorPagina']) : 10;
$paginaActual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Definimos el endpoint SPARQL
include 'endpoint_url.php';
$queryDispatcher = new SPARQLQueryDispatcher($endpointUrl);

// Consulta para contar el total de autores según el filtro de búsqueda
$sparqlQueryTotal = contarTotalAutores($busqueda);
$queryResultsTotal = $queryDispatcher->query($sparqlQueryTotal);

$totalResultados = !empty($queryResultsTotal["results"]["bindings"]) 
                    ? intval($queryResultsTotal["results"]["bindings"][0]["total"]["value"]) 
                    : 0;
$totalPaginas = ceil($totalResultados / max(1, $resultadosPorPagina));

// Asegurarse de que la página actual esté dentro del rango permitido
$paginaActual = max(1, min($paginaActual, $totalPaginas));

// Calcular el offset
$offset = ($paginaActual - 1) * $resultadosPorPagina;

// Consulta para obtener los autores según el filtro y la paginación
$sparqlQueryAutores = obtenerAutores($offset, $resultadosPorPagina, $busqueda);
$queryResultsAutores = $queryDispatcher->query($sparqlQueryAutores);

if (!empty($queryResultsAutores["results"]["bindings"])) {
    $tablaResultados = $queryResultsAutores["results"]["bindings"];
} else {
    $tablaResultados = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscador de Autores</title>
    <link rel="stylesheet" href="estilos_general.css">
    <link rel="stylesheet" href="estilos_autores-buscador.css">
</head>
<body>
<?php renderMenu('autores-buscador.php'); ?>

<main class="autores-buscador">
    <!-- Título -->
    <section class="titulo-autores">
        <h2>Buscador de Autores</h2>
    </section>

    <!-- Caja de búsqueda -->
    <div class="busqueda-autores">
        <form action="autores-buscador.php" method="get">
            <input type="text" 
                   name="busqueda" 
                   placeholder="Escribe un nombre..." 
                   value="<?php echo htmlspecialchars($busqueda); ?>">
            <button type="submit">Buscar</button>
            
            <!-- Parámetros ocultos para mantener la paginación -->
            <input type="hidden" name="resultadosPorPagina" value="<?php echo $resultadosPorPagina; ?>">
        </form>
    </div>

    <!-- Mensaje de filtro aplicado -->
    <?php if (!empty($busqueda)): ?>
    <p class="mensaje-filtro">
        <?php echo $totalResultados; ?> autores coincidentes con el término: <strong><?php echo htmlspecialchars($busqueda); ?></strong>
    </p>
<?php else: ?>
    <p class="mensaje-filtro">
        Total de autores: <?php echo $totalResultados; ?>
    </p>
<?php endif; ?>

    <!-- Tabla -->
    <table class="tabla-grupos tabla-autowidth">
        <thead>
            <tr>
                <th>Nombre del Autor</th>
                <th>Número Total de Artículos</th>
                <th>Tema con Más Artículos</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tablaResultados as $resultado): ?>
                <tr>
                    <!-- Nombre del Autor -->
                    <td><a href="autor.php?autor=<?php echo urlencode($resultado["Autor"]["value"]); ?>">
                        <?php echo htmlspecialchars($resultado["AutorLabel"]["value"]); ?>
                    </a></td>

                    <!-- Número Total de Artículos -->
                    <td><?php echo intval($resultado["numArticulosTotal"]["value"]); ?></td>

                    <!-- Tema con Más Artículos -->
                    <td><?php echo htmlspecialchars(isset($resultado["temaMasArticulos"]["value"]) ? $resultado["temaMasArticulos"]["value"] : "No especificado"); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Paginación -->
    <div class="pagination">
    <?php
    $maxBotones = 5;
    $inicio = max(1, $paginaActual - floor($maxBotones / 2));
    $fin = min($totalPaginas, $inicio + $maxBotones - 1);
    $inicio = max(1, $fin - $maxBotones + 1);

    if ($paginaActual > 1) {
        echo "<a href='autores-buscador.php?busqueda=" . urlencode($busqueda) . "&pagina=1&resultadosPorPagina=$resultadosPorPagina' class='page-link'>|< Primero</a>";
    }

    for ($i = $inicio; $i <= $fin; $i++) {
        echo "<a href='autores-buscador.php?busqueda=" . urlencode($busqueda) . "&pagina=$i&resultadosPorPagina=$resultadosPorPagina' class='page-link" . ($i == $paginaActual ? " active" : "") . "'>$i</a>";
    }

    if ($paginaActual < $totalPaginas) {
        echo "<a href='autores-buscador.php?busqueda=" . urlencode($busqueda) . "&pagina=$totalPaginas&resultadosPorPagina=$resultadosPorPagina' class='page-link'>Último >|</a>";
    }

    echo "<span class='page-info'>Página " . htmlspecialchars($paginaActual) . " de " . htmlspecialchars(max(1, $totalPaginas)) . "</span>";
    ?>
</div>

</main>

<?php renderFooter(); ?>
</body>
</html>
