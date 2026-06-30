<?php
if (!isset($term_id)) {
    return;
}

// ✅ evita fatal error
if (!function_exists('dci_prepara_termini')) {
    function dci_prepara_termini($terms) {
        if (empty($terms) || is_wp_error($terms)) return [];

        $terms = array_filter($terms, function($term) {
            return get_term_meta($term->term_id, 'visualizza_elemento', true) == 1;
        });

        usort($terms, function($a, $b) {

            $a_ord = (int) get_term_meta($a->term_id, 'ordinamento', true);
            $b_ord = (int) get_term_meta($b->term_id, 'ordinamento', true);

            if ($a_ord === $b_ord) {
                return strcmp($a->name, $b->name);
            }

            return $a_ord <=> $b_ord;
        });

        return $terms;
    }
}

if (!function_exists('dci_stampa_sottocategorie')) {
    function dci_stampa_sottocategorie($parent_id, $depth = 1, $max_depth = 5) {
        if ($depth > $max_depth) {
            return;
        }

        $terms = get_terms([
            'taxonomy'   => 'tipi_cat_amm_trasp',
            'hide_empty' => false,
            'parent'     => $parent_id
        ]);

        $terms = dci_prepara_termini($terms);

        if (empty($terms)) return;

        echo '<ul class="sub-sub-list depth-' . (int) $depth . '">';

        foreach ($terms as $term) {
            $display_name = dci_format_trasparenza_section_title($term->name);
            $term_url = get_term_meta($term->term_id, 'term_url', true);
            $open_new_window = get_term_meta($term->term_id, 'open_new_window', true);
            $child_terms = get_terms([
                'taxonomy'   => 'tipi_cat_amm_trasp',
                'hide_empty' => false,
                'parent'     => $term->term_id
            ]);
            $child_terms = dci_prepara_termini($child_terms);
            $has_children = !empty($child_terms);
            $is_external = false;

            if (!empty($term_url)) {
                $link = $term_url;
                $target = $open_new_window ? ' target="_blank" rel="noopener noreferrer"' : '';
                $is_external = true;
            } else {
                $link = get_term_link($term->term_id);
                $target = '';
            }

            $toggle_id = 'subcat-children-' . $term->term_id;

            echo '<li class="sub-sub-item' . ($has_children ? ' has-children' : ' no-children') . '">';
            echo '<div class="sub-sub-item-head">';
            echo '<a class="' . esc_attr(trim(($is_external ? 'is-external ' : '') . ($has_children ? 'has-children' : 'no-children'))) . '" href="' . esc_url($link) . '" aria-label="' . esc_attr($display_name) . '"' . $target . '>';
            echo '<span class="list-marker list-marker--dash" aria-hidden="true">-</span>';
            echo '<span>' . esc_html($display_name) . '</span>';
            if ($is_external) {
                echo '<svg class="icon icon-xs external-link-icon" aria-hidden="true"><use href="#it-external-link"></use></svg>';
            }
            echo '</a>';
            if ($has_children) {
                echo '<button class="sub-sub-toggle js-subcat-toggle" type="button" aria-expanded="false" aria-controls="' . esc_attr($toggle_id) . '" title="' . esc_attr(sprintf('Mostra o nascondi le sottovoci di %s', $display_name)) . '">';
                echo '<span class="js-subcat-toggle-label">Mostra sottovoci</span>';
                echo '<svg class="icon icon-xs" aria-hidden="true"><use href="#it-expand"></use></svg>';
                echo '<span class="visually-hidden">Mostra o nascondi le sottovoci di ' . esc_html($display_name) . '</span>';
                echo '</button>';
            }

            echo '</div>';

            if ($has_children) {
                echo '<div id="' . esc_attr($toggle_id) . '" class="js-subcat-children" hidden>';
                dci_stampa_sottocategorie($term->term_id, $depth + 1, $max_depth);
                echo '</div>';
            }

            echo '</li>';
        }

        echo '</ul>';
    }
}

// avvio
$depth = isset($depth) ? (int) $depth : 1;
dci_stampa_sottocategorie($term_id, $depth, 5);
?>
