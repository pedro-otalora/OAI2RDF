<!-- index_sparql.php -->
<?php
include 'sparql_prefijos.php';

// Consulta para contar revistas
function contarRevistas() {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT (COUNT(DISTINCT ?Revista) AS ?numRevistas)
        WHERE {
            ?Revista a ontorevistas:Revista .
        }
    ";
}

// Consulta para contar autores
function contarAutores() {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT (COUNT(DISTINCT ?Autor) AS ?numAutores)
        WHERE {
            ?Autor a ontorevistas:Autor .
        }
    ";
}

// Consulta para contar artículos
function contarArticulos() {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT (COUNT(DISTINCT ?Articulo) AS ?numArticulos)
        WHERE {
            ?Articulo a ontorevistas:Articulo .
        }
    ";
}

// Consulta para contar artículos por área temática
function contarArticulosPorArea() {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?GrupoTemaArea (COUNT(?Articulo) AS ?numArticulos)
        WHERE {
            ?Articulo a ontorevistas:Articulo ;
                      ontorevistas:perteneceAGrupoTema ?GrupoTema .
        
            ?GrupoTema ontorevistas:grupoTemaArea ?GrupoTemaArea .
        }
        GROUP BY ?GrupoTemaArea
        ORDER BY DESC(?numArticulos)
    ";
}


function obtenerTopRevistasPorArticulos() {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Revista ?RevistaLabel (COUNT(?Articulo) AS ?numArticulos)
        WHERE {
            ?Articulo ontorevistas:esParteDeNumero/ontorevistas:esParteDeRevista ?Revista .
            ?Revista rdfs:label ?RevistaLabel .
        }
        GROUP BY ?Revista ?RevistaLabel
        ORDER BY DESC(?numArticulos)
        LIMIT 10
    ";
}

function obtenerTopRevistasPorAutores() {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Revista ?RevistaLabel (COUNT(DISTINCT ?Autor) AS ?numAutores)
        WHERE {
            ?Articulo ontorevistas:tieneAutor ?Autor ;
                      ontorevistas:esParteDeNumero/ontorevistas:esParteDeRevista ?Revista .
            ?Revista rdfs:label ?RevistaLabel .
        }
        GROUP BY ?Revista ?RevistaLabel
        ORDER BY DESC(?numAutores)
        LIMIT 10
    ";
}

function obtenerTopArticulosPorAutores() {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Articulo ?ArticuloLabel (COUNT(DISTINCT ?Autor) AS ?numAutores)
        WHERE {
            ?Articulo ontorevistas:tieneAutor ?Autor ;
                      rdfs:label ?ArticuloLabel .
        }
        GROUP BY ?Articulo ?ArticuloLabel
        ORDER BY DESC(?numAutores)
        LIMIT 10
    ";
}

function obtenerTopAutoresPorArticulos() {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Autor ?AutorLabel (COUNT(?Articulo) AS ?numArticulos)
        WHERE {
            ?Articulo ontorevistas:tieneAutor ?Autor .
            ?Autor rdfs:label ?AutorLabel .
        }
        GROUP BY ?Autor ?AutorLabel
        ORDER BY DESC(?numArticulos)
        LIMIT 10
    ";
}


function obtenerArticulosConMasAutores($limit = 10, $offset = 0) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT 
    ?Articulo ?ArticuloLabel ?Revista ?RevistaLabel ?NumeroVolumen
    ?FechaPublicacion ?TemaLabel (COUNT(DISTINCT ?Autor) AS ?numAutores)
WHERE {
    # Relación entre números y revistas
    ?Numero ontorevistas:tieneArticulo ?Articulo .
    ?Numero ontorevistas:esParteDeRevista ?Revista .
    ?Revista rdfs:label ?RevistaLabel .

    # Relación entre números y volúmenes
    OPTIONAL { 
        ?Numero ontorevistas:numeroVolumen ?NumeroVolumen .
    }

    # Información del artículo
    OPTIONAL { 
        ?Articulo rdfs:label ?ArticuloLabel .
        ?Articulo ontorevistas:articuloFechaPublicacion ?FechaPublicacion .
        ?Articulo ontorevistas:perteneceAGrupoTema/rdfs:label ?TemaLabel .
        ?Articulo ontorevistas:tieneAutor ?Autor .
    }
}
GROUP BY 
    ?Articulo 
    ?ArticuloLabel 
    ?Revista 
    ?RevistaLabel 
    ?NumeroVolumen 
    ?FechaPublicacion 
    ?TemaLabel
ORDER BY DESC(?numAutores)
LIMIT $limit OFFSET $offset

    ";
}




?>