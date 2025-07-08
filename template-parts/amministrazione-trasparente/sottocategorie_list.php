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
        <?php foreach ($sub_sub_categories as $sub_sub) { ?>
            <li>
                <a href="<?= get_term_link($sub_sub->term_id); ?>">
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
                                <a href="<?= get_term_link($sub_sub_sub->term_id); ?>">
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