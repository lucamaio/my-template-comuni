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
    function dci_stampa_sottocategorie($parent_id) {

        $terms = get_terms([
            'taxonomy'   => 'tipi_cat_amm_trasp',
            'hide_empty' => false,
            'parent'     => $parent_id
        ]);

        $terms = dci_prepara_termini($terms);

        if (empty($terms)) return;

        echo '<ul class="sub-sub-list">';

        foreach ($terms as $term) {

            $term_url = get_term_meta($term->term_id, 'term_url', true);
            $open_new_window = get_term_meta($term->term_id, 'open_new_window', true);

            if (!empty($term_url)) {
                $link = $term_url;
                $target = $open_new_window ? ' target="_blank"' : '';
            } else {
                $link = get_term_link($term->term_id);
                $target = '';
            }

            echo '<li>';
            echo '<a href="' . esc_url($link) . '"' . $target . '>';
            echo esc_html($term->name);
            echo '</a>';

            // ricorsione
            dci_stampa_sottocategorie($term->term_id);

            echo '</li>';
        }

        echo '</ul>';
    }
}

// avvio
dci_stampa_sottocategorie($term_id);
?>
