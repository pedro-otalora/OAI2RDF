<?php
include 'sparql_prefijos.php';

function obtenerAutores($offset, $limit, $busqueda = '') {
    global $sparqlPrefijos;

    // Filtro dinámico por búsqueda
    $filtroBusqueda = '';
    if (!empty($busqueda)) {
        $filtroBusqueda = "FILTER(CONTAINS(LCASE(?AutorLabel), LCASE('$busqueda')))";
    }

    return <<<SPARQL
        $sparqlPrefijos
        SELECT 
            ?Autor ?AutorLabel 
            (COUNT(DISTINCT ?Articulo) AS ?numArticulosTotal)
            (SAMPLE(?TemaMasArticulosLabel) AS ?temaMasArticulos)
        WHERE {
            # Recuperar solo recursos del tipo 'Autor'
            ?Autor a ontorevistas:Autor ;
                   rdfs:label ?AutorLabel .
            
            # Filtro por búsqueda
            $filtroBusqueda

            # Relación entre autores y todos sus artículos
            OPTIONAL {
                ?Articulo ontorevistas:tieneAutor ?Autor .
            }

            # Relación entre autores, artículos y temas
            OPTIONAL {
                ?Articulo ontorevistas:tieneAutor ?Autor ;
                          ontorevistas:perteneceAGrupoTema ?Tema .
                ?Tema rdfs:label ?TemaMasArticulosLabel .
            }
        }
        GROUP BY ?Autor ?AutorLabel
        ORDER BY ASC(?AutorLabel)
        OFFSET $offset
        LIMIT $limit
    SPARQL;
}

function contarTotalAutores($busqueda = '') {
    global $sparqlPrefijos;

    // Filtro dinámico por búsqueda
    $filtroBusqueda = '';
    if (!empty($busqueda)) {
        $filtroBusqueda = "FILTER(CONTAINS(LCASE(?AutorLabel), LCASE('$busqueda')))";
    }

    return <<<SPARQL
        $sparqlPrefijos
        SELECT (COUNT(DISTINCT ?Autor) AS ?total)
        WHERE {
            # Recuperar solo recursos del tipo 'Autor'
            ?Autor a ontorevistas:Autor ;
                   rdfs:label ?AutorLabel .
            
            # Filtro por búsqueda
            $filtroBusqueda
        }
    SPARQL;
}
?>
