<!-- revista-lista.php -->
<?php
// Incluimos los archivos necesarios
include 'sparqlquerydispatcher.php';
include 'sparql_prefijos.php';
include 'revistas-lista_sparql.php';
include 'menu_principal.php';

// Definimos el endpoint SPARQL
include 'endpoint_url.php';
$queryDispatcher = new SPARQLQueryDispatcher($endpointUrl);

// Ejecutamos las consultas para obtener revistas, números y artículos
$sparqlQueryRevistas = obtenerRevistas();
$queryResultsRevistas = $queryDispatcher->query($sparqlQueryRevistas);
$revistasResultados = $queryResultsRevistas["results"]["bindings"];

$sparqlQueryNumeros = contarNumerosPorRevista();
$queryResultsNumeros = $queryDispatcher->query($sparqlQueryNumeros);
$numerosResultados = $queryResultsNumeros["results"]["bindings"];

$sparqlQueryArticulos = contarArticulosPorRevista();
$queryResultsArticulos = $queryDispatcher->query($sparqlQueryArticulos);
$articulosResultados = $queryResultsArticulos["results"]["bindings"];

// Combinar los resultados en un array asociativo por revista
$revistasData = [];

// Inicializar los datos de las revistas
foreach ($revistasResultados as $revista) {
    $revistaURI = $revista["Revista"]["value"];
    $revistasData[$revistaURI] = [
        "Revista" => $revista["Revista"]["value"],
        "RevistaLabel" => $revista["RevistaLabel"]["value"],
        "RevistaImagen" => isset($revista["RevistaImagen"]["value"]) ? $revista["RevistaImagen"]["value"] : null,
        "numNumeros" => 0,
        "numArticulos" => 0
    ];
}

// Asignar los conteos de números
foreach ($numerosResultados as $numero) {
    $revistaURI = $numero["Revista"]["value"];
    $revistasData[$revistaURI]["numNumeros"] = intval($numero["numNumeros"]["value"]);
}

// Asignar los conteos de artículos
foreach ($articulosResultados as $articulo) {
    $revistaURI = $articulo["Revista"]["value"];
    $revistasData[$revistaURI]["numArticulos"] = intval($articulo["numArticulos"]["value"]);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Revistas</title>
    <link rel="stylesheet" href="estilos_general.css">
    <link rel="stylesheet" href="estilos_revistas-lista.css">
</head>
<body>
<?php renderMenu('revistas.php'); ?>
    <!-- Bloque principal -->
    <section class="revistas-lista">
        <h2>Revistas Disponibles</h2>
        <div class="revistas-grid">
            <?php foreach ($revistasData as $revistaURI => $revistaData): ?>
                <div class="revista-item">
                    <a href="revista.php?revista=<?php echo urlencode($revistaData["Revista"]); ?>" class="entity-link">
                        <?php if ($revistaData["RevistaImagen"]): ?>
                            <img src="<?php echo $revistaData["RevistaImagen"]; ?>" alt="<?php echo $revistaData["RevistaLabel"]; ?>">
                        <?php endif; ?>
                        <h3><?php echo $revistaData["RevistaLabel"]; ?></h3>

                        <div class="revista-datos">
                            <span><strong>Números:</strong> <?php echo $revistaData["numNumeros"]; ?></span>
                            <span><strong>Artículos:</strong> <?php echo $revistaData["numArticulos"]; ?></span>
                        </div>

                        
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php renderFooter(); ?>
</body>
</html>
