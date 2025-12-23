<?php
/**
 * CATEGORIE
 */
$categorie = get_terms(array(
    'taxonomy'   => 'tipi_notizia',
    'hide_empty' => false,
));

/**
 * ARGOMENTI IN EVIDENZA
 */
$argomenti_evidenza = dci_get_option('argomenti', 'novita');
?>

<?php if (!is_wp_error($categorie) && !empty($categorie)) { ?>
<div class="container py-5" id="categoria">
    <h2 class="title-xxlarge mb-4">Esplora per categoria</h2>

    <div class="row g-4">
        <?php foreach ($categorie as $categoria) {

            $term_link = get_term_link($categoria);
            if (is_wp_error($term_link)) {
                continue;
            }
        ?>
            <div class="col-md-6 col-xl-4">
                <div class="cmp-card-simple card-wrapper pb-0 rounded border border-light">
                    <div class="card shadow-sm rounded">
                        <div class="card-body">
                            <a class="text-decoration-none"
                               href="<?php echo esc_url($term_link); ?>"
                               data-element="news-category-link">

                                <h3 class="card-title t-primary title-xlarge">
                                    <?php echo esc_html($categoria->name); ?>
                                </h3>
                            </a>

                            <?php if (!empty($categoria->description)) { ?>
                                <p class="titillium text-paragraph mb-0 description">
                                    <?php echo esc_html($categoria->description); ?>
                                </p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<?php } ?>

<?php if (!empty($argomenti_evidenza) && is_array($argomenti_evidenza)) { ?>

<div class="container py-4" id="argomenti">
    <h3 class="title-xlarge mb-4">Argomenti</h3>

    <div class="row pt-20">
        <div class="col-12">

            <div class="button-group">
                <?php
                foreach ($argomenti_evidenza as $arg_id) {

                    $argomento = get_term_by(
                        'term_taxonomy_id',
                        (int) $arg_id,
                        'argomenti'
                    );

                    if (!$argomento || is_wp_error($argomento)) {
                        continue;
                    }

                    $url = get_term_link($argomento);
                    if (is_wp_error($url)) {
                        continue;
                    }
                ?>
                    <a href="<?php echo esc_url($url); ?>"
                       class="btn-argomento"
                       style="display:inline-flex; align-items:center; gap:8px;">

                        <svg class="icon text-primary"
                             style="width:20px; height:30px; display:inline-block; vertical-align:middle;">
                            <use xlink:href="#it-bookmark"></use>
                        </svg>

                        <?php echo esc_html($argomento->name); ?>
                    </a>
                <?php } ?>
            </div>

            <div style="display:flex; justify-content:center; margin-top:20px;">
                <a href="<?php echo esc_url(dci_get_template_page_url('page-templates/argomenti.php')); ?>"
                   class="btn btn-primary">
                    Mostra tutti
                </a>
            </div>

        </div>
    </div>
</div>

<?php } ?>

<!-- STILE SEZIONE ARGOMENTI -->
<style>
.container .row.pt-20 {
    text-align: left;
}

.container .row.pt-20 .button-group {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    justify-content: flex-start;
}

.container .row.pt-20 .button-group a.btn-argomento {
    display: inline-block;
    padding: 10px 16px;
    background-color: #ffffff;
    color: #333;
    font-size: 1rem;
    font-weight: 500;
    border: 2px solid #dcdcdc;
    border-radius: 8px;
    text-decoration: none;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.container .row.pt-20 .button-group a.btn-argomento:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .container .row.pt-20 .button-group {
        gap: 12px;
    }

    .container .row.pt-20 .button-group a.btn-argomento {
        padding: 8px 14px;
        font-size: 0.9rem;
    }
}
</style>