<?php
// Incluimos los archivos necesarios
include 'sparqlquerydispatcher.php';
include 'sparql_prefijos.php';
include 'menu_principal.php';
include 'autores_sparql.php';

// Parámetros de paginación y letra seleccionada
$resultadosPorPagina = isset($_GET['resultadosPorPagina']) ? intval($_GET['resultadosPorPagina']) : 10;
$paginaActual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$letraActual = isset($_GET['letra']) ? strtoupper($_GET['letra']) : ''; // Letra seleccionada (vacío para "Todos")

// Definimos el endpoint SPARQL
include 'endpoint_url.php';
$queryDispatcher = new SPARQLQueryDispatcher($endpointUrl);

// Consulta para contar el total de autores según el filtro (letra seleccionada o todos)
$sparqlQueryTotal = contarTotalAutores($letraActual);
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
$sparqlQueryAutores = obtenerAutores($letraActual, $offset, $resultadosPorPagina);
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
    <title>Lista de Autores</title>
    <link rel="stylesheet" href="estilos_general.css">
    <link rel="stylesheet" href="estilos_autores.css">
    <link rel="stylesheet" href="estilos_autores-buscador.css">
</head>
<body>
<?php renderMenu('autores-lista.php'); ?>

<main class="autores-lista">
    <!-- Título -->
    <section class="titulo-autores">
        <h2>Lista de Autores</h2>
    </section>

    <div class="busqueda-autores">
    <form action="autores-buscador.php" method="get">
        <input type="text" name="busqueda" placeholder="Buscar autores..." required>
        <button type="submit">Buscar</button>
    </form>
</div>


    <!-- Botonera de la A a la Z -->
    <div class="botonera-letras">
        <!-- Botón "Todos" para quitar el filtro -->
        <button onclick="location.href='autores-lista.php?letra=&resultadosPorPagina=<?php echo $resultadosPorPagina; ?>'" 
                class="<?php echo ($letraActual === '') ? 'active' : ''; ?>">Todos</button>
        <?php foreach (range('A', 'Z') as $letra): ?> 
            <button onclick="location.href='autores-lista.php?letra=<?php echo urlencode($letra); ?>&resultadosPorPagina=<?php echo $resultadosPorPagina; ?>'" 
                    class="<?php echo ($letraActual === $letra) ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($letra); ?>
            </button>
        <?php endforeach; ?>
    </div>

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

    <!-- Selector de cantidad de resultados por página -->
    <div class="pagination-settings">
        <label for="resultadosPorPagina">Resultados por página:</label>
        <select id="resultadosPorPagina" name="resultadosPorPagina" onchange="location.href='autores-lista.php?letra=<?php echo urlencode($letraActual); ?>&pagina=1&resultadosPorPagina='+this.value">
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

        // Calcular el inicio y fin del rango de páginas a mostrar
        $inicio = max(1, $paginaActual - floor($maxBotones / 2));
        $fin = min($totalPaginas, $inicio + $maxBotones - 1);

        // Ajustar el inicio si el fin está cerca del total de páginas
        $inicio = max(1, $fin - $maxBotones + 1);

        // Botón "Primero"
        if ($paginaActual > 1) {
            echo "<a href='autores-lista.php?letra=$letraActual&pagina=1&resultadosPorPagina=$resultadosPorPagina' class='page-link'>|< Primero</a>";
        }

        // Mostrar los botones de página
        for ($i = $inicio; $i <= $fin; $i++) {
            echo "<a href='autores-lista.php?letra=$letraActual&pagina=$i&resultadosPorPagina=$resultadosPorPagina' class='page-link";
            if ($i == $paginaActual) {
                echo " active";
            }
            echo "'>" . htmlspecialchars($i) . "</a>";
        }

        // Botón "Último"
        if ($paginaActual < $totalPaginas) {
            echo "<a href='autores-lista.php?letra=$letraActual&pagina=$totalPaginas&resultadosPorPagina=$resultadosPorPagina' class='page-link'>Último >|</a>";
        }

        // Mostrar información de la página actual y el total de páginas
        echo "<span class='page-info'>Página " . htmlspecialchars($paginaActual) . " de " . htmlspecialchars(max(1, $totalPaginas)) . "</span>";
        ?>
    </div>

</main>

<?php renderFooter(); ?>
</body>
</html>
