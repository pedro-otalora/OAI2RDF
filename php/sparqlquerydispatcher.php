<?php

    // Clase que permite definir objetos para consultar puntos de acceso SPARQL que devuelvan los resultados en JSON 
    class SPARQLQueryDispatcher {
        private $endpoint;
    
        public function __construct($endpoint) {
            $this->endpoint = $endpoint;
        }
    
        public function query($sparqlQuery) {
            $url = $this->endpoint . '?query=' . urlencode($sparqlQuery);
            $opts = [
                "http" => [
                    "header" => "Accept: application/sparql-results+json\r\n"
                ]
            ];
            $context = stream_context_create($opts);
    
            try {
                $response = file_get_contents($url, false, $context);
                if ($response === false) {
                    throw new Exception("Error al ejecutar la consulta SPARQL.");
                }
                return json_decode($response, true);
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
                return null;
            }
        }
    }

    




    
?>
