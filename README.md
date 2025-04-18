# OAI2RDF
## Transformación de metadatos OAI-PMH de revistas científicas y repositorios institucionales a conjuntos de datos semánticos RDF.

**Autores:**  
Pedro Otálora-Giménez (https://orcid.org/0009-0000-2824-1402, pedro.o.g@um.es)  
Juan-Antonio Pastor-Sánchez (https://orcid.org/0000-0002-1677-1059, pastor@um.es)  
Tomás Saorín (https://orcid.org/0000-0001-9448-0866, tsp@um.es)  
Universidad de Murcia

**Palabras clave:** Repositorios institucionales. Revistas científicas. OAI-PMH. RDF. Web semántica. Interoperabilidad. Datos enlazados.

**Objetivos:** El auge de plataformas de publicación de revistas científicas como OJS y otras de gestión de repositorios como Dspace, adoptadas por la mayoría de repositorios institucionales, los posiciona como pilares del acceso abierto a la producción científica. (Morcillo López, 2016). Este trabajo desarrolla un flujo de transformación automatizado para construir conjuntos de datos RDF a partir de información bibliográfica de repositorios que usen OAI-PMH, ampliando así su visibilidad. Para evaluar la viabilidad técnica del proyecto, se empleó como caso de estudio la editorial de Revistas de la Universidad de Murcia.

**Metodología:** La extracción de datos inicial se realizó mediante el protocolo OAI-PMH, combinando técnicas de web scraping (DeVito et al., 2020) para ampliar la información más allá de los metadatos originales. Esto permitió mejorar los resultados de trabajos similares como el framework LOD-GF (Universidad de Cuenca, s.f.). Posteriormente, se aplicaron técnicas de limpieza y normalización previas al enriquecimiento semántico, que se basó en tesauros como UNESCO y en técnicas de Procesamiento de Lenguaje Natural (PLN). A continuación, se generó el conjunto de datos RDF utilizando vocabularios como BIBO, SKOS y FOAF, reconocidos por su amplio potencial de reutilización (Martínez Méndez et al., 2020). Las herramientas utilizadas incluyeron Vocbench para el diseño del perfil de aplicación, Python para los procesos de extracción, normalización y generación de datos RDF (Mertz, 2021), así como PHP junto con Apache Jena Fuseki para la explotación de los datos mediante consultas SPARQL (Mishra y Jain, 2023).

**Resultados:** El flujo automatizado convirtió unos 24.000 registros en un grafo de conocimiento con más de 800.000 relaciones explícitas, facilitando la creación del conjunto de datos enlazados y mejorando el descubrimiento de relaciones entre entidades (Silva y Terra, 2023). El enriquecimiento arrojó una baja reconciliación con tesauros (11%) frente a una mayor cobertura en la asignación automática de categorías (77%). La interfaz web proporcionó un cuadro estadístico con grandes posibilidades para explorar relaciones explícitas y búsquedas facetadas.

**Discusión:** Los resultados muestran la mejora en interoperabilidad y descubrimiento entre las entidades del grafo generado. La clasificación temática reconoció un amplio porcentaje de registros; sin embargo, incluir el texto completo en lugar del resumen podría aumentar significativamente la precisión y cobertura. La automatización del flujo reduce la carga manual, aunque requiere ajustes para escalarlo a otros repositorios.

**Conclusión:** La transformación aplicada al repositorio Revistas UM pone de manifiesto el potencial del modelo RDF para mejorar la accesibilidad a información científica mediante una metodología escalable y modular. Su implementación en diferentes contextos podría generar grafos interconectados que permitan búsquedas federadas más allá de las limitaciones tradicionales.

**Referencias bibliográficas:**

DeVito, N. J., Richards, G. C., & Inglesby, P. (2020). How we learnt to stop worrying and love web scraping. Nature, 585(7826), 531–532. https://doi.org/10.1038/d41586-020-02558-0

Martínez Méndez, F. J., Pastor-Sánchez, J. A., & López Carreño, R. (2020). Linked open data en bibliotecas: estado del arte. Information Research, 25(2). http://InformationR.net/ir/25-2/paper862.html

Mertz, D. (2021). Cleaning Data for Effective Data Science: Doing the Other 80% of the Work with Python, R, and Command-Line Tools. Packt Publishing.

Mishra, S., & Jain, S. (2023). Using Apache Jena Fuseki Server for Execution of SPARQL Queries in Job Search Ontology Using Semantic Technology. International Journal of Innovative Research in Computer Science & Technology (IJIRCST), 11(2), 502–510. Recuperado de https://www.ijircst.org/DOC/99-using-apache-jena-fuseki-server-for-execution-of-sparql-queries-in-job-search-ontology-using-semantic-technology.pdf

Morcillo López, L. (2016). Los repositorios institucionales en las universidades públicas de España: estado de la cuestión. Cuadernos de Gestión de Información, 6, 69–83. Recuperado a partir de https://revistas.um.es/gesinfo/article/view/264121

Silva, A. L., & Terra, A. L. (2023). Cultural heritage on the Semantic Web: The Europeana Data Model. IFLA Journal, 50(1), 93-107. https://doi.org/10.1177/03400352231202506

Sumba, F., Ortiz, J., Segarra, J., & Saquicela, V. (2017). Integración de fuentes de datos bibliográficas utilizando tecnologías de Linked Data: Caso de uso: Biblioteca de la Universidad de Cuenca. Maskana, 8(2), 189–203. Recuperado de https://publicaciones.ucuenca.edu.ec/ojs/index.php/maskana/article/view/1462

Universidad de Cuenca. (s.f.). Linked Open Data Platform: Solution to accomplish the life cycle management for publishing Linked Data on the Web. Recuperado el 29 de marzo de 2025, de https://ucuenca.github.io/lodplatform/

