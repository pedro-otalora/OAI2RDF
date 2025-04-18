<!-- revistas-lista_sparql.php -->
<?php
include 'sparql_prefijos.php';


// Consulta para obtener las revistas disponibles
function obtenerRevistas() {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Revista ?RevistaLabel (SAMPLE(?Imagen) AS ?RevistaImagen)
        WHERE {
            ?Revista a ontorevistas:Revista ;
                     rdfs:label ?RevistaLabel .
            OPTIONAL { ?Revista ontorevistas:revistaImagen ?Imagen . }
        }
        GROUP BY ?Revista ?RevistaLabel
        ORDER BY ?RevistaLabel
    ";
}

// Consulta para contar los números por revista
function contarNumerosPorRevista() {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Revista ?RevistaLabel (COUNT(DISTINCT ?Numero) AS ?numNumeros)
        WHERE {
            ?Revista a ontorevistas:Revista ;
                     rdfs:label ?RevistaLabel .
            OPTIONAL { 
                ?Numero a ontorevistas:Numero ;
                        ontorevistas:esParteDeRevista ?Revista . 
            }
        }
        GROUP BY ?Revista ?RevistaLabel
        ORDER BY ?RevistaLabel
    ";
}

// Consulta para contar los artículos por revista
function contarArticulosPorRevista() {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Revista ?RevistaLabel (COUNT(DISTINCT ?Articulo) AS ?numArticulos)
        WHERE {
            ?Revista a ontorevistas:Revista ;
                     rdfs:label ?RevistaLabel .
            OPTIONAL { 
                ?Numero a ontorevistas:Numero ;
                        ontorevistas:esParteDeRevista ?Revista .
                ?Articulo a ontorevistas:Articulo ;
                          ontorevistas:esParteDeNumero ?Numero .
            }
        }
        GROUP BY ?Revista ?RevistaLabel
        ORDER BY ?RevistaLabel
    ";
}

?>