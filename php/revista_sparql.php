<!-- revista_sparql.php -->

<?php
include 'sparql_prefijos.php';

// Consulta para obtener los detalles de la revista
function obtenerDetallesRevista($revistaURI)
{
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Revista ?RevistaLabel ?RevistaImagen ?Editorial ?ISSN ?eISSN ?DOI ?Enlace
        WHERE {
            ?Revista a ontorevistas:Revista ;
                     rdfs:label ?RevistaLabel .
            OPTIONAL { ?Revista ontorevistas:revistaImagen ?RevistaImagen . }
            OPTIONAL { ?Revista ontorevistas:revistaISSN ?ISSN . }
            OPTIONAL { ?Revista ontorevistas:revistaISSNE ?eISSN . }
            OPTIONAL { ?Revista ontorevistas:revistaDOI ?DOI . }
            OPTIONAL { ?Revista ontorevistas:revistaURL ?Enlace . }
            FILTER (?Revista = <$revistaURI>)
        }
    ";
}

// Consulta para contar el total de números para una revista específica
function contarNumerosPorRevistaEspecifica($revistaURI)
{
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT (COUNT(DISTINCT ?Numero) AS ?numNumeros)
        WHERE {
            ?Numero a ontorevistas:Numero ;
                    ontorevistas:esParteDeRevista <$revistaURI> .
        }
    ";
}


// Consulta para contar el total de artículos para una revista específica
function contarArticulosPorRevistaEspecifica($revistaURI)
{
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT (COUNT(DISTINCT ?Articulo) AS ?numArticulos)
        WHERE {
            ?Numero ontorevistas:esParteDeRevista <$revistaURI> .
            ?Articulo ontorevistas:esParteDeNumero ?Numero .
        }
    ";
}

// Consulta para contar autores por revista
function contarAutoresPorRevista($revistaURI)
{
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT (COUNT(DISTINCT ?Autor) AS ?totalAutores)
        WHERE {
            ?Numero ontorevistas:esParteDeRevista <$revistaURI> .
            ?Articulo ontorevistas:esParteDeNumero ?Numero ;
                      ontorevistas:tieneAutor ?Autor .
        }
    ";
}


// Consulta para obtener los números de la revista y el conteo de artículos (Paginado)
function obtenerNumerosRevista($revistaURI, $offset, $limit)
{
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Numero ?NumeroLabel (COUNT(?Articulo) AS ?numArticulos)
        WHERE {
            ?Numero a ontorevistas:Numero ;
                    ontorevistas:esParteDeRevista <$revistaURI> ;
                    rdfs:label ?NumeroLabel .
            OPTIONAL { ?Articulo ontorevistas:esParteDeNumero ?Numero . }
        }
        GROUP BY ?Numero ?NumeroLabel
        ORDER BY ?NumeroLabel
        LIMIT $limit
        OFFSET $offset
    ";
}

// Consulta para obtener los autores de la revista y el conteo de artículos (Paginado)
function obtenerAutoresRevista($revistaURI, $offset, $limit)
{
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Autor ?AutorLabel (COUNT(?Articulo) AS ?numArticulos)
        WHERE {
            ?Numero ontorevistas:esParteDeRevista <$revistaURI> .
            ?Articulo ontorevistas:esParteDeNumero ?Numero ;
                      ontorevistas:tieneAutor ?Autor .
            ?Autor rdfs:label ?AutorLabel .
        }
        GROUP BY ?Autor ?AutorLabel
        ORDER BY ?AutorLabel
        LIMIT $limit
        OFFSET $offset
    ";
}

// Consulta para obtener los artículos de la revista (Paginado)
function obtenerArticulosRevista($revistaURI, $offset, $limit, $orden = null, $direccion = 'asc')
{
    global $sparqlPrefijos;

    // Determinar la columna por la que se ordenará
    $orderBy = "";
    if ($orden === "titulo") {
        $orderBy = "ORDER BY " . ($direccion === "desc" ? "DESC(?ArticuloLabel)" : "ASC(?ArticuloLabel)");
    } elseif ($orden === "fecha") {
        $orderBy = "ORDER BY " . ($direccion === "desc" ? "DESC(?FechaPublicacion)" : "ASC(?FechaPublicacion)");
    } elseif ($orden === "tema") {
        $orderBy = "ORDER BY " . ($direccion === "desc" ? "DESC(?TemaLabel)" : "ASC(?TemaLabel)");
    }

    return $sparqlPrefijos . "
        SELECT ?Articulo ?ArticuloLabel (GROUP_CONCAT(DISTINCT ?AutorLabel; SEPARATOR=\", \") AS ?Autores) 
               ?FechaPublicacion ?Tema ?TemaLabel ?NumeroVolumen
        WHERE {
            ?Numero ontorevistas:esParteDeRevista <$revistaURI> ;
                    ontorevistas:numeroVolumen ?NumeroVolumen .
            ?Articulo ontorevistas:esParteDeNumero ?Numero ;
                      rdfs:label ?ArticuloLabel .
            OPTIONAL { 
                ?Articulo ontorevistas:articuloFechaPublicacion ?FechaPublicacion .
            }
            OPTIONAL { 
                ?Articulo ontorevistas:perteneceAGrupoTema ?Tema .
                ?Tema rdfs:label ?TemaLabel .
            }
            OPTIONAL { 
                ?Articulo ontorevistas:tieneAutor ?Autor .
                ?Autor rdfs:label ?AutorLabel .
            }
        }
        GROUP BY ?Articulo ?ArticuloLabel ?FechaPublicacion ?Tema ?TemaLabel ?NumeroVolumen
        $orderBy
        LIMIT $limit OFFSET $offset
    ";
}



// <!-- revista_sparql.php (nuevas funciones) -->
function obtenerTopAutoresRevista($revistaURI)
{
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Autor ?AutorLabel (COUNT(?Articulo) AS ?numArticulos)
        WHERE {
            ?Numero ontorevistas:esParteDeRevista <$revistaURI> .
            ?Articulo ontorevistas:esParteDeNumero ?Numero ;
                      ontorevistas:tieneAutor ?Autor .
            ?Autor rdfs:label ?AutorLabel .
        }
        GROUP BY ?Autor ?AutorLabel
        ORDER BY DESC(?numArticulos)
        LIMIT 5
    ";
}

function obtenerTopPalabrasClaveRevista($revistaURI)
{
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?PalabraLabel (COUNT(?Articulo) AS ?frecuencia)
        WHERE {
            ?Numero ontorevistas:esParteDeRevista <$revistaURI> .
            ?Articulo ontorevistas:esParteDeNumero ?Numero ;
                      ontorevistas:tienePalabraClave ?Palabra .
            ?Palabra rdfs:label ?PalabraLabel .
        }
        GROUP BY ?PalabraLabel
        ORDER BY DESC(?frecuencia)
        LIMIT 5
    ";
}

function obtenerTopTemasRevista($revistaURI)
{
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Tema ?TemaLabel (COUNT(?Articulo) AS ?numArticulos)
        WHERE {
            ?Numero ontorevistas:esParteDeRevista <$revistaURI> .
            ?Articulo ontorevistas:esParteDeNumero ?Numero ;
                      ontorevistas:perteneceAGrupoTema ?Tema .
            ?Tema rdfs:label ?TemaLabel .
        }
        GROUP BY ?Tema ?TemaLabel
        ORDER BY DESC(?numArticulos)
        LIMIT 5
    ";
}





?>