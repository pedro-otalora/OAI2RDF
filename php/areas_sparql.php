<!-- areas_sparql.php -->
<?php
include 'sparql_prefijos.php';


// Consulta para obtener los grupos tematicos de cada área y contar sus artículos
function obtenerGruposPorArea($areaURI) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?GrupoTematico ?GrupoTematicoLabel (COUNT(?Articulo) AS ?numArticulos)
        WHERE {
            # Filtrar por el área temática pasada en la URL
            ?GrupoTematico ontorevistas:grupoTemaArea ?AreaTematica ;
                           rdfs:label ?GrupoTematicoLabel .
            ?Articulo ontorevistas:perteneceAGrupoTema ?GrupoTematico .
            FILTER (?AreaTematica = '$areaURI')
        }
        GROUP BY ?GrupoTematico ?GrupoTematicoLabel
        ORDER BY DESC(?numArticulos)
    ";
}

function contarGruposPorArea($areaURI) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT (COUNT(DISTINCT ?GrupoTematico) AS ?totalGrupos)
        WHERE {
            ?GrupoTematico ontorevistas:grupoTemaArea ?AreaTematica .
            FILTER (STR(?AreaTematica) = '$areaURI')
        }
    ";
}


?>