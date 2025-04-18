<?php
include 'sparql_prefijos.php';

// Consulta para obtener los detalles del artículo  SEPARATOR=\", \"
function obtenerDetallesArticulo($articuloURI) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT ?ArticuloLabel ?ArticuloResumen ?Numero ?NumeroVolumen ?NumeroImagen ?revistaImagen ?Autor ?AutorLabel 
               (GROUP_CONCAT(DISTINCT ?AutorLabel; SEPARATOR=\", \") AS ?Autores)
               (GROUP_CONCAT(DISTINCT ?PalabraClaveLabel; SEPARATOR=\", \") AS ?PalabrasClave)
               ?FechaPublicacion ?ArticuloCita ?Idioma ?TemaLabel ?Editor 
               (GROUP_CONCAT(DISTINCT ?ArticuloURL; SEPARATOR=\", \") AS ?URLs)
               (GROUP_CONCAT(DISTINCT ?RecursoURI; SEPARATOR=\", \") AS ?Recursos)
               ?OAI ?DOI
        WHERE {
            <$articuloURI> a ontorevistas:Articulo ;
                           rdfs:label ?ArticuloLabel .


                                      # Propiedades principales como opcionales
            OPTIONAL { <$articuloURI> ontorevistas:esParteDeNumero ?Numero . }
            OPTIONAL { <$articuloURI> ontorevistas:articuloFechaPublicacion ?FechaPublicacion . }
            OPTIONAL { <$articuloURI> ontorevistas:articuloCita ?ArticuloCita . }
            OPTIONAL { <$articuloURI> ontorevistas:articuloIdioma ?Idioma . }
            OPTIONAL { <$articuloURI> ontorevistas:articuloEditor ?Editor . }
            OPTIONAL { <$articuloURI> ontorevistas:articuloOAI ?OAI . }

            # Vincular el número a la revista y obtener su imagen
            ?Numero ontorevistas:esParteDeRevista ?Revista .
            OPTIONAL { ?Revista ontorevistas:revistaImagen ?revistaImagen . }

            # Propiedades opcionales del artículo
            OPTIONAL { <$articuloURI> ontorevistas:articuloResumen ?ArticuloResumen . }
            OPTIONAL { <$articuloURI> ontorevistas:tienePalabraClave ?PalabraClave .
                       ?PalabraClave rdfs:label ?PalabraClaveLabel . }
            OPTIONAL { <$articuloURI> ontorevistas:articuloURL ?ArticuloURL . }
            OPTIONAL { <$articuloURI> ontorevistas:articuloRecursoURI ?RecursoURI . }

            OPTIONAL { <$articuloURI> ontorevistas:articuloDOI ?DOI . }
            

            # Propiedades opcionales del número
            OPTIONAL { ?Numero ontorevistas:numeroVolumen ?NumeroVolumen . }
            OPTIONAL { ?Numero ontorevistas:numeroImagen ?NumeroImagen . }

            # Autores y tema
            OPTIONAL { 
                <$articuloURI> ontorevistas:tieneAutor ?Autor .
                ?Autor rdfs:label ?AutorLabel .
            }
            OPTIONAL { 
                <$articuloURI> ontorevistas:perteneceAGrupoTema/rdfs:label ?TemaLabel .
            }
        }
        GROUP BY  
                 ?ArticuloLabel 
                 ?ArticuloResumen 
                 ?Numero 
                 ?NumeroVolumen 
                 ?NumeroImagen 
                 ?revistaImagen 
                 ?FechaPublicacion 
                 ?ArticuloCita 
                 ?Idioma 
                 ?TemaLabel 
                 ?Editor 
                 ?OAI
                 ?DOI
                 ?Autor
                 ?AutorLabel
    ";
}



function obtenerDetallesRevista($numeroURI)
{
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT DISTINCT ?Revista ?RevistaLabel ?RevistaImagen
        WHERE {
            <$numeroURI> ontorevistas:esParteDeRevista ?Revista .
            
            OPTIONAL { 
                ?Revista rdfs:label ?RevistaLabel ;
                         ontorevistas:revistaImagen ?RevistaImagen .
            }
        }
    ";
}



// Consulta para obtener artículos relacionados (mismo tema)
function obtenerArticulosRelacionados($articuloURI, $offset, $limit) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
      #  SELECT ?articulo ?articuloLabel ?fechaPublicacion ?numero ?revista ?revistaLabel 
      #         (GROUP_CONCAT(DISTINCT ?autorLabel; SEPARATOR=\", \") AS ?autores) ?numeroVolumen
      #  WHERE {
      #      <$articuloURI> ontorevistas:perteneceAGrupoTema ?tema .
      #      ?articulo a ontorevistas:Articulo ;
      #                 ontorevistas:perteneceAGrupoTema ?tema ;
      #                 rdfs:label ?articuloLabel ;
      #                 ontorevistas:articuloFechaPublicacion ?fechaPublicacion ;
      #                 ontorevistas:esParteDeNumero ?numero .
      #      ?numero ontorevistas:esParteDeRevista ?revista ;
      #              ontorevistas:numeroVolumen ?numeroVolumen .
      #      ?revista rdfs:label ?revistaLabel .
      #      OPTIONAL {
      #          ?articulo ontorevistas:tieneAutor ?autor .
      #          ?autor rdfs:label ?autorLabel .
      #      }
      #      FILTER (?articulo != <$articuloURI>)
      #  }
      #  GROUP BY ?articulo ?articuloLabel ?fechaPublicacion ?numero ?revista ?revistaLabel ?numeroVolumen
      #  ORDER BY ?fechaPublicacion
      #  OFFSET $offset
      #  LIMIT $limit

SELECT ?articulo ?articuloLabel ?fechaPublicacion ?numero ?revista ?revistaLabel 
       (GROUP_CONCAT(DISTINCT ?autorLabel; SEPARATOR=\", \") AS ?autores) ?numeroVolumen
WHERE {
    <$articuloURI> ontorevistas:perteneceAGrupoTema ?tema .
    
    ?articulo a ontorevistas:Articulo ;
              ontorevistas:perteneceAGrupoTema ?tema .
    
    # Propiedades opcionales
    OPTIONAL { ?articulo rdfs:label ?articuloLabel . }
    OPTIONAL { ?articulo ontorevistas:articuloFechaPublicacion ?fechaPublicacion . }
    OPTIONAL {
        ?articulo ontorevistas:esParteDeNumero ?numero .
        OPTIONAL { 
            ?numero ontorevistas:esParteDeRevista ?revista .
            OPTIONAL { ?revista rdfs:label ?revistaLabel . }
        }
        OPTIONAL { ?numero ontorevistas:numeroVolumen ?numeroVolumen . }
    }
    OPTIONAL {
        ?articulo ontorevistas:tieneAutor ?autor .
        OPTIONAL { ?autor rdfs:label ?autorLabel . }
    }
    
    FILTER (?articulo != <$articuloURI>)
}
GROUP BY ?articulo ?articuloLabel ?fechaPublicacion ?numero ?revista ?revistaLabel ?numeroVolumen
ORDER BY ?fechaPublicacion
OFFSET $offset
LIMIT $limit




    ";
}



function obtenerTotalArticulosRelacionados($articuloURI) {
    global $sparqlPrefijos;
    return $sparqlPrefijos . "
        SELECT (COUNT(DISTINCT ?articulo) AS ?total)
        WHERE {
            # Obtener el grupo de tema del artículo especificado
            <$articuloURI> ontorevistas:perteneceAGrupoTema ?tema .
            
            # Encontrar otros artículos con el mismo tema
            ?articulo a ontorevistas:Articulo ;
                       ontorevistas:perteneceAGrupoTema ?tema ;
                       ontorevistas:esParteDeNumero ?numero .

            # Excluir el artículo original de los resultados
            FILTER (?articulo != <$articuloURI>)
        }
    ";
}



?>