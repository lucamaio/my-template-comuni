<?php
global $sito_tematico_id;
$categorie_genitori = get_terms('tipi_cat_amm_trasp', array(
    'hide_empty' => false,
    'parent' => 0,
));

$siti_tematici = !empty(dci_get_option("siti_tematici", "trasparenza")) ? dci_get_option("siti_tematici", "trasparenza") : [];
?>

<style>
    .title-custom {
        font-size: 18px;
        background-color: rgb(255, 252, 252);
        padding: 10px;
        border: 1px solid #ccc;
        cursor: pointer;
    }

    .content {
        display: none;
        padding: 10px;
    }

    .content a {
        display: block;
        margin: 5px 0;
        color: rgb(17, 17, 17);
        text-decoration: none;
        padding-left: 10px;
    }

    .content a:hover {
        text-decoration: underline;
    }
</style>

<script>
    function toggleContent(id) {
        var content = document.getElementById(id);
        content.style.display = (content.style.display === "block") ? "none" : "block";
    }
</script>

<main>
    <div class="bg-grey-card">
        <form role="search" id="search-form" method="get" class="search-form">
            <button type="submit" class="d-none"></button>
            <div class="container">
                <div class="row">
                    <h2 class="visually-hidden">Esplora tutti i servizi</h2>
                    <!-- Colonna sinistra: categorie -->
                    <div class="col-12 col-lg-8 pt-30 pt-lg-50 pb-lg-50">
                        <div class="mycontainer p-3">
                            <?php foreach ($categorie_genitori as $genitore) {
                                $nome_genitore = esc_html($genitore->name);
                                $id_genitore = 'cat_' . $genitore->term_id;
                                ?>
                                <h2 class="title-custom" onclick="toggleContent('<?= $id_genitore ?>')"><?= $nome_genitore ?></h2>
                                <div id="<?= $id_genitore ?>" class="content">
                                    <?php
                                    $sottocategorie = get_terms('tipi_cat_amm_trasp', array(
                                        'hide_empty' => false,
                                        'parent' => $genitore->term_id
                                    ));
                                    ?>
                                    <ul class="link-list t-primary">
                                        <?php foreach ($sottocategorie as $sotto) {
                                            $link = get_term_link($sotto);
                                            $nome_sotto = esc_html($sotto->name); ?>
                                            <li class="mb-3 mt-3">
                                                <a class="list-item ps-0 title-medium underline" style="text-decoration:none;" href="<?= $link; ?>">
                                                    <svg class="icon">
                                                        <use xlink:href="#it-arrow-right-triangle"></use>
                                                    </svg>
                                                    <span><?= $nome_sotto; ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <!-- Colonna destra: link utili -->
                    <?php if (is_array($siti_tematici) && count($siti_tematici)) { ?>
                        <div class="col-12 col-lg-4 pt-30 pt-lg-50 pb-lg-50">
                            <div class="link-list-wrap p-3">
                                <h2 class="title-large-semi-bold"><span>Link Utili</span></h2>
                                <ul class="link-list t-primary">
                                    <?php foreach ($siti_tematici as $item) { ?>
                                        <li class="mb-3 mt-3">
                                           <?php $sito_tematico_id=$item;
                                           get_template_part("template-parts/sito-tematico/card"); ?>
                                        </li>
                                    <?php } ?>
                                    <li>
                                        <a class="list-item ps-0 text-button-xs-bold d-flex align-items-center text-decoration-none"
                                           href="<?php echo get_permalink(get_page_by_path('Amministrazione Trasparente')); ?>">
                                            <span class="mr-10">Ritorna alla trasparenza Amministrativa</span>
                                            <svg class="icon icon-xs icon-primary">
                                                <use href="#it-arrow-right"></use>
                                            </svg>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    <?php } ?>

                </div>
            </div>
        </form>
    </div>
</main>
