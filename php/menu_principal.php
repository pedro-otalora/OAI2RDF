<!-- menu_principal.php -->
<?php
function renderMenu($currentPage = '')
{
    $menuItems = [
        'index.php' => 'Inicio',
        'revistas-lista.php' => 'Revistas',
        'articulos-buscador.php' => 'Artículos',
        'autores-lista.php' => 'Autores',
        'areas-lista.php' => 'Áreas Temáticas',
        // Agrega más enlaces aquí según sea necesario
    ];

    // Cabecera
    echo '<header>';
    echo '<div class="header-container">';
    echo '<div class="header-grid">';
    echo '<div class="header-logo-left">';
    echo '<img src="imagenes/logo-editum.png" alt="Logo Editum">';
    echo '</div>';
    echo '<div class="header-title">';
    echo '<h1>Revistas UM</h1>';
    echo '</div>';
    echo '<div class="header-logo-right">';
    echo '<img src="imagenes/logo-um.png" alt="Logo Universidad de Murcia">';
    echo '</div>';
    echo '</div>';

    // Barra de navegación
    echo '<nav class="navbar">';
    echo '<ul class="navbar-nav">';
    foreach ($menuItems as $page => $label) {
        $activeClass = ($currentPage === $page) ? 'active' : '';
        echo "<li class=\"$activeClass\"><a href=\"$page\">$label</a></li>";
    }
    echo '</ul>';
    echo '</nav>';
    echo '</div>';
    echo '</header>';

    // Contenido principal
    echo '<main>';
}

function renderFooter()
{
    // Pie de página
    echo '</main>';
    echo '<footer class="site-footer">';
    echo '<p>Pedro Otálora. TFG. Conjunto de datos RDF con información de revistas científicas de la Universidad de Murcia creado a partir de datos OAI-PMH.</p>';
    echo '</footer>';
}
?>