<?php
/**
 * Template Name: Sitemap Modern PA con Sottolivelli
 */

get_header();

if (!function_exists('dci_format_trasparenza_section_title')) {
    function dci_format_trasparenza_section_title($title)
    {
        $title = (string) $title;

        if (!preg_match('/\p{Lu}{7,}/u', $title)) {
            return $title;
        }

        $lowercase_title = mb_strtolower($title, 'UTF-8');

        return mb_strtoupper(mb_substr($lowercase_title, 0, 1, 'UTF-8'), 'UTF-8')
            . mb_substr($lowercase_title, 1, null, 'UTF-8');
    }
}

function sitemap_is_amministrazione_trasparente_integrata() {
    if (dci_get_option('ck_abilita_trasparenza') !== 'true') {
        return false;
    }

    $link_amministrazione = dci_get_option('link_ammtrasparente');

    if (empty($link_amministrazione)) {
        return true;
    }

    $normalized_link = untrailingslashit($link_amministrazione);

    return in_array($normalized_link, array(
        untrailingslashit(home_url('/amministrazione-trasparente')),
        untrailingslashit(home_url('/index.php/amministrazione-trasparente')),
    ), true);
}

function sitemap_get_trasparenza_terms($parent_id = 0) {
    $terms = get_terms('tipi_cat_amm_trasp', array(
        'hide_empty' => false,
        'parent'     => $parent_id,
        'orderby'    => 'ID',
        'order'      => 'ASC',
    ));

    if (is_wp_error($terms) || empty($terms)) {
        return array();
    }

    $terms = array_filter($terms, function ($term) {
        return get_term_meta($term->term_id, 'visualizza_elemento', true) == 1;
    });

    usort($terms, function ($a, $b) {
        $ordinamento_a = (int) get_term_meta($a->term_id, 'ordinamento', true);
        $ordinamento_b = (int) get_term_meta($b->term_id, 'ordinamento', true);

        if ($ordinamento_a === $ordinamento_b) {
            return strcmp($a->name, $b->name);
        }

        return $ordinamento_a <=> $ordinamento_b;
    });

    return $terms;
}

function sitemap_render_trasparenza_tree($parent_id = 0, $depth = 1, $max_depth = 5) {
    if ($depth > $max_depth) {
        return;
    }

    $terms = sitemap_get_trasparenza_terms($parent_id);

    if (empty($terms)) {
        return;
    }

    echo '<ul class="sitemap-list sitemap-trasparenza-list">';

    foreach ($terms as $term) {
        $term_link = get_term_link($term, 'tipi_cat_amm_trasp');
        $term_url = get_term_meta($term->term_id, 'term_url', true);
        $open_new_window = get_term_meta($term->term_id, 'open_new_window', true);
        $target = '';

        if (!empty($term_url)) {
            $term_link = $term_url;
            $target = $open_new_window ? ' target="_blank" rel="noopener noreferrer"' : '';
        }

        if (is_wp_error($term_link)) {
            continue;
        }

        echo '<li>';
        echo '<a href="' . esc_url($term_link) . '"' . $target . '>' . esc_html(dci_format_trasparenza_section_title($term->name)) . '</a>';
        sitemap_render_trasparenza_tree($term->term_id, $depth + 1, $max_depth);
        echo '</li>';
    }

    echo '</ul>';
}

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

            if ($page->post_name === 'amministrazione-trasparente' && sitemap_is_amministrazione_trasparente_integrata()) {
                sitemap_render_trasparenza_tree();
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
