<?php
/**
 * Template Name: Sitemap Modern PA con Sottolivelli
 */

get_header();

function sitemap_comune_style($parent_id = 0) {
    $args = array(
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'sort_column'  => 'menu_order, post_title',
        'sort_order'   => 'asc',
        'parent'       => $parent_id,
    );

    $pages = get_pages($args);

    if ($pages) {
        echo '<ul class="sitemap-list">';
        foreach ($pages as $page) {
            $children = get_pages(array(
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'sort_column'  => 'menu_order, post_title',
                'sort_order'   => 'asc',
                'parent'       => $page->ID,
            ));

            if ($parent_id == 0) {
                echo '<li class="sitemap-section">';
                echo '<h3><a href="' . get_permalink($page->ID) . '">' . esc_html($page->post_title) . '</a></h3>';
            } else {
                echo '<li>';
                echo '<a href="' . get_permalink($page->ID) . '">' . esc_html($page->post_title) . '</a>';
            }

            if (!empty($children)) {
                sitemap_comune_style($page->ID);
            }

            echo '</li>';
        }
        echo '</ul>';
    }
}
?>

<style>
/* Sitemap con testi neri e linee verticali grigie */

.sitemap-section {
    margin-bottom: 2rem;
    border-left: 5px solid #ccc; /* linea verticale grigia */
    padding-left: 1rem;
}

.sitemap-section > h3 {
    font-weight: 700;
    font-size: 1.6rem;
    margin-bottom: 0.5rem;
    color: #000; /* testo nero */
    border-bottom: 2px solid #ccc; /* linea sotto grigia */
    padding-bottom: 0.3rem;
}

.sitemap-section > h3 a {
    color: #000; /* link nero */
    text-decoration: none;
    transition: color 0.3s ease;
}

.sitemap-section > h3 a:hover,
.sitemap-section > h3 a:focus {
    color: #333; /* nero scuro hover/focus */
    text-decoration: underline;
    outline-offset: 3px;
    outline: 2px solid #333;
    outline-radius: 4px;
}

.sitemap-list ul {
    margin-left: 1.5rem;
    margin-top: 0.5rem;
    padding-left: 1rem;
    border-left: 2px solid #ccc; /* linea verticale grigia */
}

.sitemap-list li > a {
    font-weight: 600;
    color: #000; /* testo nero */
    text-decoration: none;
    display: inline-block;
    margin: 0.25rem 0;
    transition: color 0.3s ease;
}

.sitemap-list li > a:hover,
.sitemap-list li > a:focus {
    color: #333; /* nero scuro hover/focus */
    text-decoration: underline;
    outline-offset: 3px;
    outline: 2px solid #333;
    outline-radius: 4px;
}
</style>



<div class="container my-5">
    <h1>Mappa del sito</h1>
    <?php sitemap_comune_style(); ?>
</div>

<?php get_footer(); ?>
