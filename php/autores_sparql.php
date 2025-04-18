<!-- autores_sparql.php -->

<?php
include 'sparql_prefijos.php';

function obtenerAutores($letraInicial = '', $offset, $limit) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT 
            ?Autor ?AutorLabel 
            (COUNT(DISTINCT ?Articulo) AS ?numArticulosTotal)
            (SAMPLE(?TemaMasArticulosLabel) AS ?temaMasArticulos)
        WHERE {
            # Recuperar solo recursos del tipo 'Autor'
            ?Autor a ontorevistas:Autor ;
                   rdfs:label ?AutorLabel .
            
            # Filtro por letra inicial si aplica
            " . ($letraInicial !== '' ? "FILTER(STRSTARTS(UCASE(STR(?AutorLabel)), \"$letraInicial\"))" : "") . "

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
    ";
}






function contarTotalAutores($letraInicial = '') {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT (COUNT(DISTINCT ?Autor) AS ?total)
        WHERE {
            # Recuperar solo recursos del tipo 'Autor'
            ?Autor a ontorevistas:Autor ;
                   rdfs:label ?AutorLabel .
            " . ($letraInicial !== '' ? "FILTER(STRSTARTS(UCASE(STR(?AutorLabel)), \"$letraInicial\"))" : "") . "
        }
    ";
}




?>