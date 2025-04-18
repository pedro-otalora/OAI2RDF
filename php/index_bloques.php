<?php
// Ejecutamos las consultas para los Top 10
$topRevistasArticulos = ejecutarConsulta($queryDispatcher, 'obtenerTopRevistasPorArticulos');
$topRevistasAutores = ejecutarConsulta($queryDispatcher, 'obtenerTopRevistasPorAutores');
$topAutoresArticulos = ejecutarConsulta($queryDispatcher, 'obtenerTopAutoresPorArticulos');

$topArticulosMasAutores = ejecutarConsulta($queryDispatcher, 'obtenerArticulosConMasAutores');

?>

<section class="destacados-container">
    <h2 class="destacados-titulo">Destacados</h2>
    <div class="top-ten-tables">
        <!-- Primera fila con tres tablas -->
        <div class="destacados-grid">
            <!-- Tabla: Revistas con más artículos -->
            <div class="revista-col-tablas-top">
                <table class="tabla-grupos tabla-autowidth">
                    <thead>
                        <tr><th colspan="3">Top 10 Revistas con más Artículos</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topRevistasArticulos as $index => $revista): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><a href="revista.php?revista=<?php echo urlencode($revista["Revista"]["value"]); ?>">
                                    <?php echo htmlspecialchars($revista["RevistaLabel"]["value"]); ?>
                                </a></td>
                                <td><?php echo intval($revista["numArticulos"]["value"]); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tabla: Revistas con más autores -->
            <div class="revista-col-tablas-top">
                <table class="tabla-grupos tabla-autowidth">
                    <thead>
                        <tr><th colspan="3">Top 10 Revistas con más Autores</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topRevistasAutores as $index => $revista): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><a href="revista.php?revista=<?php echo urlencode($revista["Revista"]["value"]); ?>">
                                    <?php echo htmlspecialchars($revista["RevistaLabel"]["value"]); ?>
                                </a></td>
                                <td><?php echo intval($revista["numAutores"]["value"]); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tabla: Autores con más artículos -->
            <div class="revista-col-tablas-top">
                <table class="tabla-grupos tabla-autowidth">
                    <thead>
                        <tr><th colspan="3">Top 10 Autores con más Artículos</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topAutoresArticulos as $index => $autor): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><a href="autor.php?autor=<?php echo urlencode($autor["Autor"]["value"]); ?>">
                                    <?php echo htmlspecialchars($autor["AutorLabel"]["value"]); ?>
                                </a></td>
                                <td><?php echo intval($autor["numArticulos"]["value"]); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div> <!-- Fin primera fila -->

        <!-- Segunda fila con una tabla completa -->
        <div class="full-width-table">
            <!-- Tabla: Artículos con más autores -->
            <table class="tabla-grupos tabla-autowidth">
                <thead>
                    <tr><th colspan="7">Top 10 Artículos con más autores</th></tr>
                    <tr><th>#</th><th>Revista</th><th>Número Volumen</th><th>Título</th><th>Número de Autores</th><th>Fecha</th><th>Tema</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($topArticulosMasAutores as $index => $articulo): ?>
            <tr>
                <!-- Número de fila -->
                <td><?php echo $index + 1; ?></td>

                <!-- Revista -->
                <?php if (isset($articulo["RevistaLabel"])): ?>
                    <!-- Enlace a revista.php -->
                    <td><a href="revista.php?revista=<?php echo urlencode($articulo["Revista"]["value"]); ?>">
                        <?php echo htmlspecialchars($articulo["RevistaLabel"]["value"]); ?>
                    </a></td> 
                <?php else: ?>
                    <td>No especificado</td>
                <?php endif; ?>

                <!-- Número Volumen -->
                <?php if (isset($articulo["NumeroVolumen"]["value"])): ?>
                    <td><?php echo htmlspecialchars($articulo["NumeroVolumen"]["value"]); ?></td> 
                <?php else: ?>
                    <td>No especificado</td>
                <?php endif; ?>

                <!-- Título del Artículo -->
                <?php if (isset($articulo["ArticuloLabel"]["value"])): ?>
                    <!-- Enlace a articulo.php -->
                    <td><a href="articulo.php?articulo=<?php echo urlencode($articulo["Articulo"]["value"]); ?>">
                        <?php echo htmlspecialchars($articulo["ArticuloLabel"]["value"]); ?>
                    </a></td> 
                <?php else: ?>
                    <td>No especificado</td>
                <?php endif; ?>

                <!-- Número de Autores -->
                <?php if (isset($articulo["numAutores"]["value"])): ?>
                    <td><?php echo intval($articulo["numAutores"]["value"]); ?></td> 
                <?php else: ?>
                    <td>No especificado</td>
                <?php endif; ?>

                <!-- Fecha de publicación -->
                <?php if (isset($articulo["FechaPublicacion"]["value"])): ?>
                    <td><?php echo htmlspecialchars($articulo["FechaPublicacion"]["value"]); ?></td> 
                <?php else: ?>
                    <td>No especificada</td>
                <?php endif; ?>

                <!-- Tema -->
                <?php if (isset($articulo["TemaLabel"]["value"])): ?>
                    <td><?php echo htmlspecialchars($articulo["TemaLabel"]["value"]); ?></td> 
                <?php else: ?>
                    <td>No especificado</td>
                <?php endif; ?>

            </tr>
            <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div> <!-- Fin del bloque de tablas -->
</section>
