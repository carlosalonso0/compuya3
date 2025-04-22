<?php
/**
 * Funciones para el manejo de categorías
 */

/**
 * Obtener todas las categorías
 */
function get_all_categories() {
    $sql = "SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre";
    return get_rows($sql);
}

/**
 * Obtener categorías padres
 */
function get_parent_categories() {
    $sql = "SELECT * FROM categorias WHERE categoria_padre_id IS NULL AND activo = 1 ORDER BY nombre";
    return get_rows($sql);
}

/**
 * Obtener subcategorías de una categoría padre
 */
function get_subcategories($parent_id) {
    $sql = "SELECT * FROM categorias WHERE categoria_padre_id = ? AND activo = 1 ORDER BY nombre";
    return get_rows($sql, [$parent_id]);
}

/**
 * Obtener categoría por ID
 */
function get_category($id) {
    $sql = "SELECT * FROM categorias WHERE id = ?";
    return get_row($sql, [$id]);
}

/**
 * Obtener categoría por slug
 */
function get_category_by_slug($slug) {
    $sql = "SELECT * FROM categorias WHERE slug = ?";
    return get_row($sql, [$slug]);
}

/**
 * Obtener la ruta completa de una categoría (breadcrumb)
 */
function get_category_path($category_id) {
    $path = [];
    $current_id = $category_id;
    
    while ($current_id) {
        $category = get_category($current_id);
        if (!$category) break;
        
        array_unshift($path, $category);
        $current_id = $category['categoria_padre_id'];
    }
    
    return $path;
}

/**
 * Obtener árbol de categorías para menú
 */
function get_categories_tree() {
    // Obtener categorías padre
    $parent_categories = get_parent_categories();
    $tree = [];
    
    foreach ($parent_categories as $parent) {
        $children = get_subcategories($parent['id']);
        $tree[] = [
            'parent' => $parent,
            'children' => $children
        ];
    }
    
    return $tree;
}

/**
 * Renderizar menú de categorías
 */
function render_categories_menu() {
    $categories_tree = get_categories_tree();
    
    echo '<ul class="categories-menu">';
    
    foreach ($categories_tree as $branch) {
        $parent = $branch['parent'];
        $children = $branch['children'];
        
        echo '<li class="parent-category">';
        echo '<a href="' . SITE_URL . '/categoria.php?id=' . $parent['id'] . '">' . htmlspecialchars($parent['nombre']) . '</a>';
        
        if (!empty($children)) {
            echo '<ul class="subcategories">';
            foreach ($children as $child) {
                echo '<li><a href="' . SITE_URL . '/categoria.php?id=' . $child['id'] . '">' . htmlspecialchars($child['nombre']) . '</a></li>';
            }
            echo '</ul>';
        }
        
        echo '</li>';
    }
    
    echo '</ul>';
}