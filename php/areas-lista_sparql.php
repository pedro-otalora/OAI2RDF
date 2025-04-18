<!-- areas-lista_sparql.php -->
<?php
include 'sparql_prefijos.php';

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
?>
