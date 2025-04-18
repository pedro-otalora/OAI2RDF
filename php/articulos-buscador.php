<?php
// Incluimos los archivos necesarios
include 'sparqlquerydispatcher.php';
include 'sparql_prefijos.php';
include 'menu_principal.php';
include 'articulos-buscador_sparql.php';

// Parámetros de búsqueda
$filtros = isset($_GET['filtros']) ? $_GET['filtros'] : [];
$resultadosPorPagina = isset($_GET['resultadosPorPagina']) ? intval($_GET['resultadosPorPagina']) : 10;
$paginaActual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;

// Definimos el endpoint SPARQL
include 'endpoint_url.php';
$queryDispatcher = new SPARQLQueryDispatcher($endpointUrl);

// Consulta para contar el total de artículos según los filtros
$sparqlQueryTotal = contarTotalArticulos($filtros);
$queryResultsTotal = $queryDispatcher->query($sparqlQueryTotal);

$totalResultados = !empty($queryResultsTotal["results"]["bindings"])
    ? intval($queryResultsTotal["results"]["bindings"][0]["total"]["value"])
    : 0;
$totalPaginas = ceil($totalResultados / max(1, $resultadosPorPagina));

// Asegurarse de que la página actual esté dentro del rango permitido
$paginaActual = max(1, min($paginaActual, $totalPaginas));

// Calcular el offset
$offset = ($paginaActual - 1) * $resultadosPorPagina;

// Consulta para obtener los artículos según los filtros y la paginación
$sparqlQueryArticulos = obtenerArticulos($offset, $resultadosPorPagina, $filtros);
$queryResultsArticulos = $queryDispatcher->query($sparqlQueryArticulos);

if (!empty($queryResultsArticulos["results"]["bindings"])) {
    $tablaResultados = $queryResultsArticulos["results"]["bindings"];
} else {
    $tablaResultados = [];
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscador de Artículos</title>
    <link rel="stylesheet" href="estilos_general.css">
    <link rel="stylesheet" href="estilos_articulos-buscador.css">
    <script src="articulos-buscador_filtros.js"></script>
</head>

<body>
    <?php renderMenu('articulos-buscador.php'); ?>

    <main class="articulos-buscador">
        <!-- Título -->
        <section class="titulo-articulos">
            <h2>Buscador Avanzado de Artículos</h2>
        </section>

        <!-- Caja de búsqueda con múltiples filtros -->
        <div class="busqueda-articulos">
            <form id="formulario-filtros" action="articulos-buscador.php" method="get">
                <div id="contenedor-filtros">
                    <?php if (!empty($_GET['filtros'])): ?>
                        <?php foreach ($_GET['filtros'] as $index => $filtro): ?>
                            <div class="fila-filtro">
                                <select name="filtros[<?php echo $index; ?>][entidad]">
                                    <option value="Articulo" <?php echo ($filtro['entidad'] === 'Articulo') ? 'selected' : ''; ?>>
                                        Artículo</option>
                                    <option value="Autor" <?php echo ($filtro['entidad'] === 'Autor') ? 'selected' : ''; ?>>Autor
                                    </option>
                                    <option value="PalabraClave" <?php echo ($filtro['entidad'] === 'PalabraClave') ? 'selected' : ''; ?>>Palabra Clave</option>
                                    <option value="Tema" <?php echo ($filtro['entidad'] === 'Tema') ? 'selected' : ''; ?>>Tema
                                    </option>
                                    <option value="Revista" <?php echo ($filtro['entidad'] === 'Revista') ? 'selected' : ''; ?>>
                                        Revista</option>
                                </select>
                                <input type="text" name="filtros[<?php echo $index; ?>][valor]"
                                    value="<?php echo htmlspecialchars($filtro['valor']); ?>"
                                    placeholder="Escribe un término...">
                                <div class="botones">
                                    <?php if ($index > 0): ?>
                                        <button type="button" class="boton-eliminar" onclick="eliminarFiltro(this)">-</button>
                                    <?php endif; ?>
                                    <button type="button" class="boton-agregar" onclick="agregarFiltro()">+</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Fila inicial si no hay filtros -->
                        <div class="fila-filtro">
                            <select name="filtros[0][entidad]">
                                <option value="Articulo" selected>Artículo</option>
                                <option value="Autor">Autor</option>
                                <option value="PalabraClave">Palabra Clave</option>
                                <option value="Tema">Tema</option>
                                <option value="Revista">Revista</option>
                            </select>
                            <input type="text" name="filtros[0][valor]" placeholder="Escribe un término...">
                            <div class="botones">
                                <button type="button" class="boton-agregar" onclick="agregarFiltro()">+</button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>


                <!-- Botón Buscar -->
                <div class="contenedor-boton-buscar">
                    <button type="submit" class="boton-buscar">Buscar</button>
                </div>

                <p class="resultados-info">
    <?php 
    // Mostrar el número total de resultados
    echo $totalResultados; ?> artículos coinciden con los filtros seleccionados.

    <?php 
    // Mostrar los filtros aplicados
    if (!empty($_GET['filtros'])) {
        echo '(';
        $filtrosTexto = [];
        foreach ($_GET['filtros'] as $filtro) {
            if (!empty($filtro['entidad']) && !empty($filtro['valor'])) {
                // Formatear cada filtro como "(Filtro en negrita: valor)"
                $filtrosTexto[] = '<strong>' . htmlspecialchars($filtro['entidad']) . '</strong>: ' . htmlspecialchars($filtro['valor']);
            }
        }
        // Unir los filtros con comas y mostrar
        echo implode('), (', $filtrosTexto) . ')';
    }
    ?>
</p>

                <!-- Parámetros ocultos para mantener la paginación -->
                <input type="hidden" name="resultadosPorPagina" value="<?php echo $resultadosPorPagina; ?>">
            </form>
        </div>

        <!-- Tabla -->
        <table class="tabla-grupos tabla-autowidth">
            <thead>
                <tr>
                    <th>Revista</th>
                    <th>Número</th>
                    <th>Título del Artículo</th>
                    <th>Autores</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tablaResultados as $resultado): ?>
                    <tr>
                        <!-- Revista -->
                        <td><?php echo htmlspecialchars(isset($resultado["RevistaLabel"]["value"]) ? $resultado["RevistaLabel"]["value"] : "No especificada"); ?>
                        </td>

                        <!-- Número -->
                        <td><?php echo htmlspecialchars(isset($resultado["NumeroVolumen"]["value"]) ? $resultado["NumeroVolumen"]["value"] : "No especificado"); ?>
                        </td>

                        <!-- Título del Artículo -->
                        <td><a href="articulo.php?articulo=<?php echo urlencode($resultado["Articulo"]["value"]); ?>">
                                <?php echo htmlspecialchars(isset($resultado["ArticuloLabel"]["value"]) ? $resultado["ArticuloLabel"]["value"] : "Sin título"); ?>
                            </a></td>

                        <!-- Autores -->
                        <td><?php echo htmlspecialchars(isset($resultado["Autores"]["value"]) ? $resultado["Autores"]["value"] : "No especificados"); ?>
                        </td>

                        <!-- Fecha -->
                        <td><?php echo isset($resultado["FechaPublicacion"]["value"]) ? htmlspecialchars($resultado["FechaPublicacion"]["value"]) : "No especificada"; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination-settings">
            <label for="resultadosPorPagina">Resultados por página:</label>
            <select id="resultadosPorPagina" name="resultadosPorPagina"
                onchange="location.href='articulos-buscador.php?pagina=1&resultadosPorPagina='+this.value">
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
    // Serializar los filtros para incluirlos en las URLs
    $filtrosQuery = !empty($_GET['filtros']) ? http_build_query(['filtros' => $_GET['filtros']]) : '';

    // Número máximo de botones de página a mostrar
    $maxBotones = 5;

    // Calcular el inicio y el fin del rango de páginas a mostrar
    $inicio = max(1, $paginaActual - floor($maxBotones / 2));
    $fin = min($totalPaginas, $inicio + $maxBotones - 1);
    $inicio = max(1, $fin - $maxBotones + 1);

    // Botón "Primero"
    if ($paginaActual > 1) {
        echo "<a href='articulos-buscador.php?pagina=1&resultadosPorPagina=$resultadosPorPagina&$filtrosQuery' class='page-link'>|< Primero</a>";
    }

    // Mostrar los botones de página
    for ($i = $inicio; $i <= $fin; $i++) {
        echo "<a href='articulos-buscador.php?pagina=$i&resultadosPorPagina=$resultadosPorPagina&$filtrosQuery' class='page-link" . ($i == $paginaActual ? " active" : "") . "'>$i</a>";
    }

    // Botón "Último"
    if ($paginaActual < $totalPaginas) {
        echo "<a href='articulos-buscador.php?pagina=$totalPaginas&resultadosPorPagina=$resultadosPorPagina&$filtrosQuery' class='page-link'>Último >|</a>";
    }

    // Mostrar información de la página actual y el total de páginas
    echo "<span class='page-info'>Página " . htmlspecialchars($paginaActual) . " de " . htmlspecialchars(max(1, $totalPaginas)) . "</span>";
    ?>
</div>


    </main>

    <?php renderFooter(); ?>
</body>

</html>