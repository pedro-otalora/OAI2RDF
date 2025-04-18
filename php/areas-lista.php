<!-- areas-lista.php -->
<?php
include 'sparqlquerydispatcher.php';
include 'sparql_prefijos.php';
include 'areas-lista_sparql.php';
include 'menu_principal.php';

// Definimos el endpoint SPARQL
include 'endpoint_url.php';
$queryDispatcher = new SPARQLQueryDispatcher($endpointUrl);

// Ejecutamos la consulta para contar artículos por área temática
$areasTematicasResultados = ejecutarConsulta($queryDispatcher, 'contarArticulosPorArea');

function ejecutarConsulta($queryDispatcher, $funcion)
{
    global $sparqlQueryString;
    $sparqlQueryString = $funcion();

    // Ejecutar la consulta SPARQL
    try {
        $result = $queryDispatcher->query($sparqlQueryString);
        return $result["results"]["bindings"];
    } catch (Exception $e) {
        echo "Error al ejecutar la consulta: " . $e->getMessage();
        return [];
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Áreas Temáticas - Revistas UM</title>
    <link rel="stylesheet" href="estilos_general.css">
    <link rel="stylesheet" href="estilos_index.css">
    <link rel="stylesheet" href="estilos_areas.css">
</head>

<body>

    <?php renderMenu('areas-lista.php'); ?>

    <!-- Bloque de áreas temáticas -->
    <section class="areas-tematicas">
        <h2>Áreas temáticas (basadas en el Tesauro de la UNESCO)</h2>
        
        <div class="areas-tematicas-grid">
            <?php foreach ($areasTematicasResultados as $area): ?>
                <div class="area-tematica-container">
                    <!-- Codifica el parámetro de la URL -->
                    <a href="areas.php?area=<?php echo urlencode($area["GrupoTemaArea"]["value"]); ?>" class="area-link">
                        <h3><?php echo $area["GrupoTemaArea"]["value"]; ?></h3>
                        <p><?php echo intval($area["numArticulos"]["value"]); ?> artículos</p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php renderFooter(); ?>

</body>

</html>