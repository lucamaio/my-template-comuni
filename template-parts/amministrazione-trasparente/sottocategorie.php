<?php
GLOBAL $title, $title;

// Recupero categoria genitore
$categoria_genitore = get_terms('tipi_cat_amm_trasp', array(
    'hide_empty' => false,
    'field' => 'slug',
    'name' => $title
));

if ( ! empty( $categoria_genitore ) && ! is_wp_error( $categoria_genitore ) ) {

    $parent_term_id = $categoria_genitore[0]->term_id; 

    // Recupero sottocategorie
    $sottocategorie = get_terms('tipi_cat_amm_trasp', array(
        'hide_empty' => false, 
        'parent' => $parent_term_id
    ));

    
    // 👉 FILTRO VISIBILITÀ
    $sottocategorie = array_filter($sottocategorie, function($term) {
        return get_term_meta($term->term_id, 'visualizza_elemento', true) == 1;
    });


    // 👉 ORDINAMENTO
    if (!empty($sottocategorie)) {
        usort($sottocategorie, function($a, $b) {

            $a_ord = (int) get_term_meta($a->term_id, 'ordinamento', true);
            $b_ord = (int) get_term_meta($b->term_id, 'ordinamento', true);

            if ($a_ord === $b_ord) {
                return strcmp($a->name, $b->name);
            }

            return $a_ord <=> $b_ord;
        });
    }
}

$conteggi_sottovoci = [];
$termini_trasparenza = get_terms('tipi_cat_amm_trasp', [
    'hide_empty' => false,
]);

if (!is_wp_error($termini_trasparenza)) {
    foreach ($termini_trasparenza as $termine_trasparenza) {
        if (
            (int) $termine_trasparenza->parent > 0
            && get_term_meta($termine_trasparenza->term_id, 'visualizza_elemento', true) == 1
        ) {
            $parent_id = (int) $termine_trasparenza->parent;
            $conteggi_sottovoci[$parent_id] = ($conteggi_sottovoci[$parent_id] ?? 0) + 1;
        }
    }
}
?>

<?php if ( ! empty( $sottocategorie ) ) { ?>
<style>
    .dci-at-subcategories {
        padding-top: 2rem;
        padding-bottom: 2.5rem;
    }

    .dci-at-subcategories__heading {
        margin-bottom: 1.25rem;
        color: #17324d;
        font-size: 1.5rem;
        line-height: 1.25;
        font-weight: 700;
    }

    .dci-at-subcategories__grid {
        row-gap: 1rem;
    }

    .dci-at-subcategory {
        display: flex;
        height: 100%;
        min-height: 10.5rem;
        overflow: hidden;
        color: #17324d;
        background: #fff;
        border: 1px solid #d8e1ea;
        border-left: 4px solid #17324d;
        border-radius: 4px;
        box-shadow: 0 3px 12px rgba(23, 50, 77, 0.07);
        text-decoration: none;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
    }

    .dci-at-subcategory:hover {
        color: #17324d;
        border-color: #17324d;
        box-shadow: 0 8px 20px rgba(23, 50, 77, 0.13);
        transform: translateY(-2px);
        text-decoration: none;
    }

    .dci-at-subcategory:focus-visible {
        outline: 3px solid #17324d;
        outline-offset: 3px;
    }

    .dci-at-subcategory__body {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        padding: 1.35rem 1.4rem 1.25rem;
    }

    .dci-at-subcategory__title-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .dci-at-subcategory__title {
        margin: 0;
        color: #17324d;
        font-size: 1.35rem;
        line-height: 1.22;
        font-weight: 700;
    }

    .dci-at-subcategory__external {
        flex: 0 0 auto;
        width: 1.25rem;
        height: 1.25rem;
        margin-top: 0.15rem;
        fill: currentColor;
    }

    .dci-at-subcategory__description {
        margin: 0.65rem 0 0;
        color: #455a64;
        font-size: 1rem;
        line-height: 1.45;
    }

    .dci-at-subcategory__count {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem 0.55rem;
        margin: 0.75rem 0 0;
        padding-top: 0.65rem;
        color: #455a64;
        border-top: 1px solid #e6edf3;
        font-size: 0.9rem;
        line-height: 1.4;
        font-weight: 600;
    }

    @media (max-width: 767.98px) {
        .dci-at-subcategories {
            padding-top: 1.5rem;
            padding-bottom: 2rem;
        }

        .dci-at-subcategory {
            min-height: auto;
        }

        .dci-at-subcategory__body {
            padding: 1.15rem 1.2rem;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .dci-at-subcategory {
            transition: none;
        }

        .dci-at-subcategory:hover {
            transform: none;
        }
    }
</style>

<section class="container dci-at-subcategories" id="categorie" aria-labelledby="dci-at-subcategories-title">
    <h2 class="dci-at-subcategories__heading" id="dci-at-subcategories-title">
        <?php esc_html_e('In questa sezione', 'design_comuni_italia'); ?>
    </h2>
    <div class="row dci-at-subcategories__grid">

        <?php foreach ( $sottocategorie as $sottocategoria ) { 

            if($title != $sottocategoria->name){

                // 👉 LINK DEFAULT
                $link = get_term_link($sottocategoria->term_id);

                if (is_wp_error($link)) {
                    $link = '#';
                }

                // 👉 META
                $term_url = get_term_meta($sottocategoria->term_id, 'term_url', true);
                $open_new_window = get_term_meta($sottocategoria->term_id, 'open_new_window', true);

                // 👉 DEFAULT
                $target = '';
                $opens_new_window = false;
                $is_external_section = false;

                // 👉 LINK PERSONALIZZATO
                if (!empty($term_url)) {
                    $link = esc_url($term_url);
                    $target = ($open_new_window) ? ' target="_blank" rel="noopener noreferrer"' : '';
                    $opens_new_window = !empty($open_new_window);
                    $is_external_section = true;
                }
        ?>
            <div class="col-12 col-md-6 col-xl-4">
                <a class="dci-at-subcategory" href="<?php echo esc_url($link); ?>"<?php echo $target; ?>>
                    <div class="dci-at-subcategory__body">
                        <div class="dci-at-subcategory__title-row">
                            <h3 class="dci-at-subcategory__title">
                                <?php echo esc_html(dci_format_trasparenza_section_title($sottocategoria->name)); ?>
                            </h3>
                            <?php if ($is_external_section) { ?>
                                <svg class="icon dci-at-subcategory__external" aria-hidden="true">
                                    <use href="#it-external-link"></use>
                                </svg>
                                <span class="visually-hidden">
                                    <?php
                                    echo $opens_new_window
                                        ? esc_html__('Sezione esterna all’Amministrazione Trasparente. Si apre in una nuova finestra', 'design_comuni_italia')
                                        : esc_html__('Sezione esterna all’Amministrazione Trasparente', 'design_comuni_italia');
                                    ?>
                                </span>
                            <?php } ?>
                        </div>

                        <?php if (!empty($sottocategoria->description)) { ?>
                            <p class="dci-at-subcategory__description">
                                <?php echo wp_kses_post($sottocategoria->description); ?>
                            </p>
                        <?php } ?>

                        <?php if (empty($term_url)) { ?>
                            <p class="dci-at-subcategory__count">
                                <span>
                                    <?php
                                    printf(
                                        esc_html(_n(
                                            '%s elemento pubblicato',
                                            '%s elementi pubblicati',
                                            (int) $sottocategoria->count,
                                            'design_comuni_italia'
                                        )),
                                        esc_html(number_format_i18n((int) $sottocategoria->count))
                                    );
                                    ?>
                                </span>
                                <span aria-hidden="true">·</span>
                                <span>
                                    <?php
                                    $numero_sottovoci = $conteggi_sottovoci[$sottocategoria->term_id] ?? 0;
                                    printf(
                                        esc_html(_n(
                                            '%s sottovoce',
                                            '%s sottovoci',
                                            $numero_sottovoci,
                                            'design_comuni_italia'
                                        )),
                                        esc_html(number_format_i18n($numero_sottovoci))
                                    );
                                    ?>
                                </span>
                            </p>
                        <?php } ?>
                    </div>
                </a>
            </div>

        <?php } } ?>

    </div>
</section>
<?php } ?>
