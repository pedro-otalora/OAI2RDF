<?php
// Incluimos los archivos necesarios
include 'sparqlquerydispatcher.php';
include 'sparql_prefijos.php';
include 'areas_sparql.php';
include 'index_sparql.php';
include 'menu_principal.php';

// Verificamos si se ha pasado un área temática en la URL
if (!isset($_GET['area']) || empty($_GET['area'])) {
    die("Error: No se especificó un área temática.");
}

// Decodifica el parámetro recibido
$areaLabel = urldecode($_GET['area']);

// Definimos el endpoint SPARQL
include 'endpoint_url.php';
$queryDispatcher = new SPARQLQueryDispatcher($endpointUrl);

// Ejecutamos la consulta para contar artículos por área temática
$areasTematicasResultados = ejecutarConsulta($queryDispatcher, 'contarArticulosPorArea');

function ejecutarConsulta($queryDispatcher, $funcion) {
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

// Consulta para obtener los grupos temáticos y el recuento de artículos del área seleccionada
$sparqlQueryGrupos = obtenerGruposPorArea($areaLabel);
$queryResultsGrupos = $queryDispatcher->query($sparqlQueryGrupos);

// Validar si hay resultados para los grupos temáticos
$gruposResultados = !empty($queryResultsGrupos["results"]["bindings"]) ? $queryResultsGrupos["results"]["bindings"] : [];

// Ejecutar la consulta para contar los grupos temáticos del área
$sparqlQueryContarGrupos = contarGruposPorArea($areaLabel);
$queryResultsContarGrupos = $queryDispatcher->query($sparqlQueryContarGrupos);

// Obtener el total de grupos temáticos
$totalGrupos = isset($queryResultsContarGrupos["results"]["bindings"][0]["totalGrupos"]["value"]) 
    ? intval($queryResultsContarGrupos["results"]["bindings"][0]["totalGrupos"]["value"]) 
    : 0;

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área Temática</title>
    <link rel="stylesheet" href="estilos_general.css">
    <link rel="stylesheet" href="estilos_index.css">
    <link rel="stylesheet" href="estilos_areas.css">
</head>
<body>
<?php renderMenu('areas.php'); ?>

<section class="areas-tematicas">
    <h2>Áreas temáticas</h2>
    <div class="areas-tematicas-grid">
        <?php foreach ($areasTematicasResultados as $area): ?>
            <div class="area-tematica-container">
                <!-- Codifica el parámetro de la URL -->
                <a href="areas.php?area=<?php echo urlencode($area["GrupoTemaArea"]["value"]); ?>" class="area-link">
                    <h3><?php echo htmlspecialchars($area["GrupoTemaArea"]["value"]); ?></h3>
                    <p><?php echo intval($area["numArticulos"]["value"]); ?> artículos</p>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php if (!empty($gruposResultados)): ?>
<!-- Mostrar tabla solo si hay resultados -->
<section class="grupos-tematicos-lista">
    <h2>Temas del área temática <?php echo htmlspecialchars($areaLabel); ?> (<?php echo $totalGrupos; ?>)</h2>
    <table class="tabla-grupos">
        <thead>
            <tr>
                <th>Tema</th>
                <th>Número de Artículos</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gruposResultados as $grupo): ?>
                <tr>
                    <!-- Enlace al tema solo si hay datos -->
                    <td><a href="tema.php?tema=<?php echo urlencode($grupo["GrupoTematico"]["value"]); ?>"><?php echo htmlspecialchars($grupo["GrupoTematicoLabel"]["value"]); ?></a></td>
                    <td><?php echo intval($grupo["numArticulos"]["value"]); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php else: ?>
<!-- Mostrar mensaje si no hay datos -->
<section class="grupos-tematicos-lista">
    <h2>No hay grupos temáticos disponibles para esta área.</h2>
</section>
<?php endif; ?>

<?php renderFooter(); ?>
</body>
</html>
