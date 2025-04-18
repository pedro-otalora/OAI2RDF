<!-- tema_sparql.php -->

<?php
include 'sparql_prefijos.php';

function obtenerDetallesTema($temaURI) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?TemaLabel ?AreaLabel
WHERE {
    <$temaURI> rdfs:label ?TemaLabel ;
              ontorevistas:grupoTemaArea ?AreaLabel .
}
    ";
}

function obtenerArticulosPorTema($temaURI, $offset, $limit) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Articulo ?ArticuloLabel (GROUP_CONCAT(DISTINCT ?AutorLabel; SEPARATOR=\", \") AS ?Autores) 
               ?FechaPublicacion ?RevistaLabel ?NumeroVolumen
        WHERE {
            <$temaURI> ^ontorevistas:perteneceAGrupoTema ?Articulo .
            ?Articulo rdfs:label ?ArticuloLabel ;
                      ontorevistas:esParteDeNumero ?Numero .
            ?Numero ontorevistas:numeroVolumen ?NumeroVolumen ;
                    ontorevistas:esParteDeRevista ?Revista .
            ?Revista rdfs:label ?RevistaLabel .
            OPTIONAL { 
                ?Articulo ontorevistas:tieneAutor ?Autor .
                ?Autor rdfs:label ?AutorLabel .
            }
            OPTIONAL { 
                ?Articulo ontorevistas:articuloFechaPublicacion ?FechaPublicacion .
            }
        }
        GROUP BY ?Articulo ?ArticuloLabel ?FechaPublicacion ?RevistaLabel ?NumeroVolumen
        ORDER BY ASC(?FechaPublicacion)
        OFFSET $offset
        LIMIT $limit
    ";
}



function obtenerAutoresPorTema($temaURI, $offset, $limit) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT 
            ?Autor ?AutorLabel 
            (COUNT(DISTINCT ?ArticuloTema) AS ?numArticulosTema) 
            (COUNT(DISTINCT ?ArticuloTotal) AS ?numArticulosTotal)
        WHERE {
            # Artículos relacionados con el tema específico
            <$temaURI> ^ontorevistas:perteneceAGrupoTema ?ArticuloTema .
            ?ArticuloTema ontorevistas:tieneAutor ?Autor .
            ?Autor rdfs:label ?AutorLabel .

            # Todos los artículos del autor en el dataset
            OPTIONAL {
                ?ArticuloTotal ontorevistas:tieneAutor ?Autor .
            }
        }
        GROUP BY ?Autor ?AutorLabel
        ORDER BY DESC(?numArticulosTema)
        OFFSET $offset
        LIMIT $limit
    ";
}


function contarTotalResultadosPorTema($temaURI, $tabla) {
    global $sparqlPrefijos;
    switch ($tabla) {
        case 'articulos':
            return $sparqlPrefijos . "
                SELECT (COUNT(DISTINCT ?Articulo) AS ?total)
                WHERE {
                    <$temaURI> ^ontorevistas:perteneceAGrupoTema ?Articulo .
                }
            ";
        case 'autores':
            return $sparqlPrefijos . "
                SELECT (COUNT(DISTINCT ?Autor) AS ?total)
                WHERE {
                    <$temaURI> ^ontorevistas:perteneceAGrupoTema ?Articulo .
                    OPTIONAL { 
                        ?Articulo ontorevistas:tieneAutor ?Autor .
                    }
                }
            ";
    }
}


?>