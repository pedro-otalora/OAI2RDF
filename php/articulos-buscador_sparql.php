<?php
include 'sparql_prefijos.php';

function obtenerArticulos($offset, $limit, $filtros) {
    global $sparqlPrefijos;

    // Generar los filtros dinámicamente en base a los criterios seleccionados
    $sparqlFiltros = '';
    foreach ($filtros as $filtro) {
        if (!empty($filtro['valor'])) {
            // Asegurarse de que el valor sea una cadena
            $valor = is_array($filtro['valor']) ? implode(", ", $filtro['valor']) : $filtro['valor'];

            switch ($filtro['entidad']) {
                case 'Articulo':
                    $sparqlFiltros .= "FILTER(CONTAINS(LCASE(?ArticuloLabel), LCASE('" . addslashes($valor) . "')))\n";
                    break;
                case 'Autor':
                    $sparqlFiltros .= "FILTER(EXISTS { ?Articulo ontorevistas:tieneAutor ?Autor . ?Autor rdfs:label ?AutorLabel . FILTER(CONTAINS(LCASE(?AutorLabel), LCASE('" . addslashes($valor) . "'))) })\n";
                    break;
                case 'PalabraClave':
                    $sparqlFiltros .= "FILTER(EXISTS { ?Articulo ontorevistas:tienePalabraClave ?PalabraClave . ?PalabraClave rdfs:label ?PalabraClaveLabel . FILTER(CONTAINS(LCASE(?PalabraClaveLabel), LCASE('" . addslashes($valor) . "'))) })\n";
                    break;
                case 'Tema':
                    $sparqlFiltros .= "FILTER(EXISTS { ?Articulo ontorevistas:perteneceAGrupoTema ?Tema . ?Tema rdfs:label ?TemaLabel . FILTER(CONTAINS(LCASE(?TemaLabel), LCASE('" . addslashes($valor) . "'))) })\n";
                    break;
                case 'Revista':
                    $sparqlFiltros .= "FILTER(EXISTS { ?Articulo ontorevistas:esParteDeNumero/ontorevistas:esParteDeRevista ?Revista . ?Revista rdfs:label ?RevistaLabel . FILTER(CONTAINS(LCASE(?RevistaLabel), LCASE('" . addslashes($valor) . "'))) })\n";
                    break;
            }
        }
    }

    // Construir la consulta SPARQL
    return <<<SPARQL
        $sparqlPrefijos
SELECT DISTINCT ?Articulo ?ArticuloLabel ?RevistaLabel ?NumeroVolumen 
       (GROUP_CONCAT(DISTINCT ?AutorLabel; SEPARATOR=", ") AS ?Autores)
       ?FechaPublicacion
WHERE {
    # Artículo y sus propiedades principales
    ?Articulo a ontorevistas:Articulo .
    
    # Propiedades opcionales
    OPTIONAL { ?Articulo rdfs:label ?ArticuloLabel . }
    OPTIONAL { 
        ?Articulo ontorevistas:esParteDeNumero ?Numero .
        ?Numero ontorevistas:esParteDeRevista ?Revista .
        ?Revista rdfs:label ?RevistaLabel .
    }
    OPTIONAL { 
        ?Articulo ontorevistas:esParteDeNumero ?Numero .
        ?Numero ontorevistas:numeroVolumen ?NumeroVolumen .
    }
    OPTIONAL { ?Articulo ontorevistas:articuloFechaPublicacion ?FechaPublicacion . }
    
    # Relación con autores (opcional)
    OPTIONAL {
        ?Articulo ontorevistas:tieneAutor/rdfs:label ?AutorLabel .
    }

    # Aplicar los filtros dinámicos
    $sparqlFiltros
}
GROUP BY ?Articulo ?ArticuloLabel ?RevistaLabel ?NumeroVolumen ?FechaPublicacion
ORDER BY LCASE(?ArticuloLabel)
OFFSET $offset
LIMIT $limit
SPARQL;
}



function contarTotalArticulos($filtros) {
    global $sparqlPrefijos;

    // Generar los filtros dinámicamente en base a los criterios seleccionados
    $sparqlFiltros = '';
    foreach ($filtros as $filtro) {
        if (!empty($filtro['valor'])) {
            // Asegurarse de que el valor sea una cadena
            $valor = is_array($filtro['valor']) ? implode(", ", $filtro['valor']) : $filtro['valor'];

            switch ($filtro['entidad']) {
                case 'Articulo':
                    $sparqlFiltros .= "FILTER(CONTAINS(LCASE(?ArticuloLabel), LCASE('" . addslashes($valor) . "')))\n";
                    break;
                case 'Autor':
                    $sparqlFiltros .= "FILTER(EXISTS { ?Articulo ontorevistas:tieneAutor ?Autor . ?Autor rdfs:label ?AutorLabel . FILTER(CONTAINS(LCASE(?AutorLabel), LCASE('" . addslashes($valor) . "'))) })\n";
                    break;
                case 'PalabraClave':
                    $sparqlFiltros .= "FILTER(EXISTS { ?Articulo ontorevistas:tienePalabraClave ?PalabraClave . ?PalabraClave rdfs:label ?PalabraClaveLabel . FILTER(CONTAINS(LCASE(?PalabraClaveLabel), LCASE('" . addslashes($valor) . "'))) })\n";
                    break;
                case 'Tema':
                    $sparqlFiltros .= "FILTER(EXISTS { ?Articulo ontorevistas:perteneceAGrupoTema ?Tema . ?Tema rdfs:label ?TemaLabel . FILTER(CONTAINS(LCASE(?TemaLabel), LCASE('" . addslashes($valor) . "'))) })\n";
                    break;
                case 'Revista':
                    $sparqlFiltros .= "FILTER(EXISTS { ?Articulo ontorevistas:esParteDeNumero/ontorevistas:esParteDeRevista ?Revista . ?Revista rdfs:label ?RevistaLabel . FILTER(CONTAINS(LCASE(?RevistaLabel), LCASE('" . addslashes($valor) . "'))) })\n";
                    break;
            }
        }
    }

    // Construir la consulta SPARQL para contar los artículos
    return <<<SPARQL
        $sparqlPrefijos
        SELECT (COUNT(DISTINCT ?Articulo) AS ?total)
        WHERE {
            # Artículo y sus propiedades principales
            ?Articulo a ontorevistas:Articulo ;
                      rdfs:label ?ArticuloLabel .

            # Aplicar los filtros dinámicos
            $sparqlFiltros
        }
SPARQL;
}

?>
