<!-- numero_sparql.php -->

<?php
include 'sparql_prefijos.php';

// Obtener detalles del número
function obtenerDetallesNumero($numeroURI) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Numero ?NumeroLabel ?NumeroImagen ?NumeroVolumen ?Revista ?RevistaLabel ?RevistaImagen ?FechaPublicacion
        WHERE {
            ?Numero a ontorevistas:Numero ;
                    rdfs:label ?NumeroLabel ;
                    ontorevistas:esParteDeRevista ?Revista .
            OPTIONAL { ?Numero ontorevistas:fechaPublicacion ?FechaPublicacion . }
            OPTIONAL { ?Numero ontorevistas:numeroImagen ?NumeroImagen . }
            OPTIONAL { ?Numero ontorevistas:numeroVolumen ?NumeroVolumen . }
            OPTIONAL { ?Revista rdfs:label ?RevistaLabel . }
            OPTIONAL { ?Revista ontorevistas:revistaImagen ?RevistaImagen . }
            FILTER (?Numero = <$numeroURI>)
        }
    ";
}

// Obtener artículos por número:
function obtenerArticulosPorNumero($numeroURI, $offset, $limit, $orden = null, $direccion = null) {
    global $sparqlPrefijos;

    // Ordenar por columna específica
    $orderBy = "";
    if ($orden === "titulo") {
        $orderBy = "ORDER BY " . ($direccion === "desc" ? "DESC(?ArticuloLabel)" : "ASC(?ArticuloLabel)");
    } elseif ($orden === "fecha") {
        $orderBy = "ORDER BY " . ($direccion === "desc" ? "DESC(?FechaPublicacion)" : "ASC(?FechaPublicacion)");
    } elseif ($orden === "tema") {
        $orderBy = "ORDER BY " . ($direccion === "desc" ? "DESC(?TemaLabel)" : "ASC(?TemaLabel)");
    }

    return $sparqlPrefijos . "
        SELECT ?Articulo ?ArticuloLabel (GROUP_CONCAT(DISTINCT ?AutorLabel; SEPARATOR=', ') AS ?Autores) ?FechaPublicacion ?TemaLabel
        WHERE {
            ?Articulo a ontorevistas:Articulo ;
                      ontorevistas:esParteDeNumero <$numeroURI> ;
                      rdfs:label ?ArticuloLabel .
            OPTIONAL { ?Articulo ontorevistas:tieneAutor ?Autor . ?Autor rdfs:label ?AutorLabel . }
            OPTIONAL { ?Articulo ontorevistas:articuloFechaPublicacion ?FechaPublicacion . }
            OPTIONAL { 
                ?Articulo ontorevistas:perteneceAGrupoTema ?Tema .
                ?Tema rdfs:label ?TemaLabel .
            }
        }
        GROUP BY ?Articulo ?ArticuloLabel ?FechaPublicacion ?TemaLabel
        $orderBy
        LIMIT $limit OFFSET $offset
    ";
}

// Obtener autores por número:
function obtenerAutoresPorNumero($numeroURI, $offset, $limit) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT DISTINCT ?Autor ?AutorLabel (COUNT(?Articulo) AS ?numArticulos)
        WHERE {
            <$numeroURI> ontorevistas:tieneArticulo ?Articulo .
            ?Articulo ontorevistas:tieneAutor ?Autor .
            ?Autor rdfs:label ?AutorLabel .
        }
        GROUP BY ?Autor ?AutorLabel
        ORDER BY ASC(?AutorLabel)
        LIMIT $limit OFFSET $offset
    ";
}

// Contar total de resultados por tabla
function contarTotalResultadosPorNumero($numeroURI, $tabla) {
    global $sparqlPrefijos;
    
    if ($tabla === 'articulos') {
        return $sparqlPrefijos . "
            SELECT (COUNT(DISTINCT ?Articulo) AS ?total)
            WHERE {
                <$numeroURI> ontorevistas:tieneArticulo ?Articulo .
            }
        ";
    } elseif ($tabla === 'autores') {
        return $sparqlPrefijos . "
            SELECT (COUNT(DISTINCT ?Autor) AS ?total)
            WHERE {
                <$numeroURI> ontorevistas:tieneArticulo ?Articulo .
                ?Articulo ontorevistas:tieneAutor ?Autor .
            }
        ";
    }
}

function obtenerTopAutoresPorNumero($numeroURI) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Autor ?AutorLabel (COUNT(?Articulo) AS ?numArticulos)
        WHERE {
            <$numeroURI> ontorevistas:tieneArticulo ?Articulo .
            ?Articulo ontorevistas:tieneAutor ?Autor .
            ?Autor rdfs:label ?AutorLabel .
        }
        GROUP BY ?Autor ?AutorLabel
        ORDER BY DESC(?numArticulos)
        LIMIT 5
    ";
}

function obtenerTopPalabrasClavePorNumero($numeroURI) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?PalabraLabel (COUNT(?Articulo) AS ?frecuencia)
WHERE {
    <$numeroURI> ontorevistas:tieneArticulo ?Articulo .
    ?Articulo ontorevistas:tienePalabraClave ?PalabraClave .
    ?PalabraClave rdfs:label ?PalabraLabel .
}
GROUP BY ?PalabraLabel
ORDER BY DESC(?frecuencia)
LIMIT 5
    ";
}

function obtenerTopTemasPorNumero($numeroURI) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?Tema ?TemaLabel (COUNT(?Articulo) AS ?numArticulos)
        WHERE {
            <$numeroURI> ontorevistas:tieneArticulo ?Articulo .
            ?Articulo ontorevistas:perteneceAGrupoTema ?Tema .
            ?Tema rdfs:label ?TemaLabel .
        }
        GROUP BY ?Tema ?TemaLabel
        ORDER BY DESC(?numArticulos)
        LIMIT 5
    ";
}

function obtenerNumeroAnterior($numeroURI) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?NumeroAnterior ?VolumenAnterior
WHERE {
    <$numeroURI> ontorevistas:esParteDeRevista ?Revista .
    ?NumeroAnterior ontorevistas:esParteDeRevista ?Revista ;
                    ontorevistas:numeroVolumen ?VolumenAnterior .
    <$numeroURI> ontorevistas:numeroVolumen ?VolumenActual .
    FILTER (?VolumenAnterior < ?VolumenActual)
}
ORDER BY DESC(?VolumenAnterior)
LIMIT 1
    ";
}

function obtenerNumeroSiguiente($numeroURI) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?NumeroSiguiente ?VolumenSiguiente
WHERE {
    <$numeroURI> ontorevistas:esParteDeRevista ?Revista .
    ?NumeroSiguiente ontorevistas:esParteDeRevista ?Revista ;
                     ontorevistas:numeroVolumen ?VolumenSiguiente .
    <$numeroURI> ontorevistas:numeroVolumen ?VolumenActual .
    FILTER (?VolumenSiguiente > ?VolumenActual)
}
ORDER BY ASC(?VolumenSiguiente)
LIMIT 1
    ";
}



?>