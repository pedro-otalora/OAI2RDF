<?php
// Incluimos los archivos necesarios
include 'sparqlquerydispatcher.php';
include 'sparql_prefijos.php';
include 'menu_principal.php';
include 'autor_sparql.php';

// Verificamos si se ha pasado un autor en la URL
if (!isset($_GET['autor'])) {
    die("Error: No se especificó un autor.");
}

// Parámetro del autor
$autorURI = urldecode($_GET['autor']);

// Definimos el endpoint SPARQL
include 'endpoint_url.php';
$queryDispatcher = new SPARQLQueryDispatcher($endpointUrl);

// Consulta para obtener el nombre del autor
$sparqlQueryDetalles = obtenerDetallesAutor($autorURI);
$queryResultsDetalles = $queryDispatcher->query($sparqlQueryDetalles);

if (!empty($queryResultsDetalles["results"]["bindings"])) {
    $autorDetalles = $queryResultsDetalles["results"]["bindings"][0];
} else {
    die("Error: No se encontraron detalles para el autor especificado.");
}

// Consulta para obtener los colaboradores del autor
$sparqlQueryColaboradores = obtenerColaboradores($autorURI);
$queryResultsColaboradores = $queryDispatcher->query($sparqlQueryColaboradores);

$colaboradoresResultados = !empty($queryResultsColaboradores["results"]["bindings"])
    ? $queryResultsColaboradores["results"]["bindings"]
    : [];

// Consulta para obtener los artículos del autor
$sparqlQueryArticulos = obtenerArticulosPorAutor($autorURI);
$queryResultsArticulos = $queryDispatcher->query($sparqlQueryArticulos);

$articulosResultados = !empty($queryResultsArticulos["results"]["bindings"])
    ? $queryResultsArticulos["results"]["bindings"]
    : [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($autorDetalles["AutorLabel"]["value"]); ?></title>
    <link rel="stylesheet" href="estilos_general.css">
    <link rel="stylesheet" href="estilos_autor.css">
</head>
<body>
<?php renderMenu('autores-lista.php'); ?>

<main class="autor-detalles">
    <!-- Título del autor -->
    <section class="titulo-autor">
        <h2><?php echo htmlspecialchars($autorDetalles["AutorLabel"]["value"]); ?></h2>
    </section>

    <!-- Tabla de colaboradores -->
    <section class="tabla-colaboradores">
        <h3>Colaboradores</h3>
        <table class="tabla-grupos tabla-autowidth">
            <thead>
                <tr>
                    <th>Nombre del Colaborador</th>
                    <th>Número de Artículos en Común</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($colaboradoresResultados as $colaborador): ?>
                    <tr>
                        <!-- Nombre del Colaborador -->
                        <td><a href="autor.php?autor=<?php echo urlencode($colaborador["Colaborador"]["value"]); ?>">
                            <?php echo htmlspecialchars($colaborador["ColaboradorLabel"]["value"]); ?>
                        </a></td>

                        <!-- Número de Artículos en Común -->
                        <td><?php echo intval($colaborador["numArticulosComunes"]["value"]); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <!-- Tabla de artículos -->
    <section class="tabla-articulos">
        <h3>Artículos del Autor</h3>
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
                <?php foreach ($articulosResultados as $articulo): ?>
                    <tr>
                        <!-- Revista -->
                        <td><?php echo htmlspecialchars(isset($articulo["RevistaLabel"]["value"]) ? $articulo["RevistaLabel"]["value"] : "No especificada"); ?></td>

                        <!-- Número -->
                        <td><?php echo htmlspecialchars(isset($articulo["NumeroVolumen"]["value"]) ? $articulo["NumeroVolumen"]["value"] : "No especificado"); ?></td>

                        <!-- Título del Artículo -->
                        <td><a href="articulo.php?articulo=<?php echo urlencode($articulo["Articulo"]["value"]); ?>">
                            <?php echo htmlspecialchars(isset($articulo["ArticuloLabel"]["value"]) ? $articulo["ArticuloLabel"]["value"] : "Sin título"); ?>
                        </a></td>

                        <!-- Autores -->
                        <td><?php echo htmlspecialchars(isset($articulo["Autores"]["value"]) ? $articulo["Autores"]["value"] : "No especificados"); ?></td>

                        <!-- Fecha -->
                        <td><?php echo htmlspecialchars(isset($articulo["FechaPublicacion"]["value"]) ? $articulo["FechaPublicacion"]["value"] : "No especificada"); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

</main>

<?php renderFooter(); ?>
</body>
</html>
