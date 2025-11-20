<?php
if (!isset($term_id)) {
    return; // Evita errori se $term_id non è passato
}

// TERZO LIVELLO
$sub_sub_categories = get_terms('tipi_cat_amm_trasp', array(
    'hide_empty' => false,
    'parent' => $term_id
));

if (!empty($sub_sub_categories) && !is_wp_error($sub_sub_categories)) { ?>
    <ul class="sub-sub-list">
        <?php foreach ($sub_sub_categories as $sub_sub) { 
            // Recupero i metadati dell'URL personalizzato e del flag per aprire in una nuova finestra
            $term_url = get_term_meta($sub_sub->term_id, 'term_url', true);
            $open_new_window = get_term_meta($sub_sub->term_id, 'open_new_window', true);

            // Se c'è un URL personalizzato, lo sostituisco al link predefinito
            if (!empty($term_url)) {
                $link = $term_url; // Imposto l'URL personalizzato
                $target = $open_new_window ? ' target="_blank"' : ''; // Se c'è la spunta "Apri in una nuova finestra"
            } else {
                $link = get_term_link($sub_sub->term_id); // Link di default se non c'è URL personalizzato
                $target = ''; // Nessun target, se non c'è URL personalizzato
            }
        ?>
            <li>
                <a href="<?= esc_url($link); ?>"<?= $target; ?>>
                    <?= esc_html($sub_sub->name); ?>
                </a>

                <?php
                // QUARTO LIVELLO
                $sub_sub_sub_categories = get_terms('tipi_cat_amm_trasp', array(
                    'hide_empty' => false,
                    'parent' => $sub_sub->term_id
                ));

                if (!empty($sub_sub_sub_categories) && !is_wp_error($sub_sub_sub_categories)) { ?>
                    <ul class="sub-sub-list">
                        <?php foreach ($sub_sub_sub_categories as $sub_sub_sub) { ?>
                            <li>
                                <a href="<?= esc_url(get_term_link($sub_sub_sub->term_id)); ?>">
                                    <?= esc_html($sub_sub_sub->name); ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>

            </li>
        <?php } ?>
    </ul>
<?php } ?>


