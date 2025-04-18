<!-- tema_sparql.php -->

<?php
include 'sparql_prefijos.php';

function obtenerDetallesAutor($autorURI) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?AutorLabel
        WHERE {
            <$autorURI> rdfs:label ?AutorLabel .
        }
    ";
}

function obtenerColaboradores($autorURI) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Colaborador ?ColaboradorLabel (COUNT(DISTINCT ?Articulo) AS ?numArticulosComunes)
        WHERE {
            ?Articulo ontorevistas:tieneAutor <$autorURI> ;
                      ontorevistas:tieneAutor ?Colaborador .
            FILTER(?Colaborador != <$autorURI>)
            ?Colaborador rdfs:label ?ColaboradorLabel .
        }
        GROUP BY ?Colaborador ?ColaboradorLabel
        ORDER BY DESC(?numArticulosComunes)
    ";
}

function obtenerArticulosPorAutor($autorURI) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Articulo ?ArticuloLabel (GROUP_CONCAT(DISTINCT ?AutorLabel; SEPARATOR=\", \") AS ?Autores) 
               ?FechaPublicacion ?RevistaLabel ?NumeroVolumen
        WHERE {
            ?Articulo ontorevistas:tieneAutor <$autorURI> ;
                      rdfs:label ?ArticuloLabel ;
                      ontorevistas:esParteDeNumero ?Numero .
            OPTIONAL { 
                ?Numero ontorevistas:numeroVolumen ?NumeroVolumen ;
                        ontorevistas:esParteDeRevista ?Revista .
                ?Revista rdfs:label ?RevistaLabel .
            }
            OPTIONAL { 
                ?Articulo ontorevistas:tieneAutor ?OtroAutor .
                ?OtroAutor rdfs:label ?AutorLabel .
            }
            OPTIONAL { 
                ?Articulo ontorevistas:articuloFechaPublicacion ?FechaPublicacion .
            }
        }
        GROUP BY ?Articulo ?ArticuloLabel ?FechaPublicacion ?RevistaLabel ?NumeroVolumen
        ORDER BY ASC(?FechaPublicacion)
    ";
}

?>