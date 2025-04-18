<!-- index.php -->
<?php
// Incluimos los archivos necesarios
include 'sparqlquerydispatcher.php';
include 'sparql_prefijos.php';
include 'index_sparql.php';
include 'menu_principal.php';

// Definimos el endpoint SPARQL
include 'endpoint_url.php';
// $endpointUrl = 'http://localhost:3030/datarevistas/sparql';
$queryDispatcher = new SPARQLQueryDispatcher($endpointUrl);

// Ejecutamos las consultas para contar revistas, autores y artículos
$revistasResultados = ejecutarConsulta($queryDispatcher, 'contarRevistas');
$autoresResultados = ejecutarConsulta($queryDispatcher, 'contarAutores');
$articulosResultados = ejecutarConsulta($queryDispatcher, 'contarArticulos');

// Extraemos los valores específicos
$numRevistas = isset($revistasResultados[0]["numRevistas"]["value"]) ? intval($revistasResultados[0]["numRevistas"]["value"]) : 0;
$numAutores = isset($autoresResultados[0]["numAutores"]["value"]) ? intval($autoresResultados[0]["numAutores"]["value"]) : 0;
$numArticulos = isset($articulosResultados[0]["numArticulos"]["value"]) ? intval($articulosResultados[0]["numArticulos"]["value"]) : 0;

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
    <title>Revistas UM</title>
    <link rel="stylesheet" href="estilos_general.css">
    <link rel="stylesheet" href="estilos_index.css">
</head>

<body>

    <?php renderMenu('index.php'); ?>

    <div class="intro-container">
    <h1>Explorador Semántico de Revistas Científicas UM</h1>
    <p><strong>Basado en el repositorio OAI-PMH de la Universidad de Murcia (2025)</strong>
    <br>Bienvenido al explorador semántico de revistas científicas de la Universidad de Murcia.
    <br>Este espacio de datos bibliográficos enlazados y navegables ha sido esarrollado mediante un flujo automatizado basado en el protocolo OAI-PMH y modelado RDF.
    <br>El buscador permite explorar relaciones semánticas entre revistas, artículos, autores y temáticas UNESCO y una navegación facetada por artículos, autores y áreas temáticas.
    <br> Este sistema respeta los principios de acceso abierto del repositorio de la Universidad de Murcia, ofreciendo una capa semántica sobre los datos originales y facilitando nuevos modos de descubrimiento.
</p>
</div>
<h1>Contenido</h1>
    <!-- Contenedores principales -->
    <div class="main-grid">
    
        <!-- Contenedor de Revistas -->
        <div class="entity-container" id="revistas-container">
            <a href="revistas-lista.php" class="entity-link">
                <img src="imagenes/icono_revista.png" alt="Revistas">
                <h2>Revistas</h2>
                <p><?php echo $numRevistas; ?></p>
            </a>
        </div>

        <!-- Contenedor de Artículos -->
        <div class="entity-container" id="articulos-container">
            <a href="articulos-buscador.php" class="entity-link">
                <img src="imagenes/icono_articulo.png" alt="Artículos">
                <h2>Artículos</h2>
                <p><?php echo $numArticulos; ?></p>
            </a>
        </div>

        <!-- Contenedor de Autores -->
        <div class="entity-container" id="autores-container">
            <a href="autores-lista.php" class="entity-link">
                <img src="imagenes/icono_autor.png" alt="Autores">
                <h2>Autores</h2>
                <p><?php echo $numAutores; ?></p>
            </a>
        </div>
    </div>

    <!-- Bloque de áreas temáticas -->
    <section class="areas-tematicas">
        <h2>Áreas temáticas (basadas en el Tesauro de la UNESCO)</h2>
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

    <!-- Incluir el bloque de tablas Top 10 -->
    <?php include 'index_bloques.php'; ?>

    <?php renderFooter(); ?>

</body>

</html>
