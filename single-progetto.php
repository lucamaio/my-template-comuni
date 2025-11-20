<?php

/**
 * Template Progetto - Versione moderna
 *
 * @package Design_Comuni_Italia
 */

global $uo_id, $inline;

get_header();
?>

<main>
    <?php
    while (have_posts()) {
        the_post();
        $user_can_view_post = dci_members_can_user_view_post(get_current_user_id(), $post->ID);

        $prefix = '_dci_progetto_';
        $nome_misura         = dci_get_meta("nome_misura", $prefix, $post->ID);
        $descrizione_scopo   = dci_get_meta("descrizione_scopo", $prefix, $post->ID);
        $data_pubblicazione  = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
        $date                = date_i18n('d F Y', mktime(0, 0, 0, $data_pubblicazione[1], $data_pubblicazione[0], $data_pubblicazione[2]));

        $missioni  = get_the_terms($post, 'tipi_progetto');
        $img       = dci_get_meta('immagine', $prefix, $uo_id);

        $componente = dci_get_meta("componente", $prefix, $post->ID);
        $investimento = dci_get_meta("investimento", $prefix, $post->ID);
        $intervento = dci_get_meta("intervento", $prefix, $post->ID);
        $titolare = dci_get_meta("titolare", $prefix, $post->ID);
        $attuatore = dci_get_option("nome_comune");
        $cup = dci_get_meta("cup", $prefix, $post->ID);

        $importo = dci_get_meta("importo", $prefix, $post->ID);
        $modalita_accesso = dci_get_meta("modalita", $prefix, $post->ID);
        $attivita_finanziata = dci_get_meta("attivita", $prefix, $post->ID);
        $avanzamento_progetto = dci_get_meta("avanzamento", $prefix, $post->ID);

        $file_avanzamento = dci_get_meta("avanzamento_allegati", $prefix, $post->ID);
        $atti     = dci_get_meta("atti", $prefix, $post->ID);
        $allegati = dci_get_meta("allegati", $prefix, $post->ID);
        $a_cura_di = dci_get_meta("a_cura_di", $prefix, $post->ID);
    ?>

        <!-- HEADER PAGINA -->
        <div class="container my-5" id="main-container">
            <div class="row align-items-center mb-4">
                <div class="col-lg-8">
                    <?php get_template_part("template-parts/common/breadcrumb"); ?>
                    <h3 class="fw-bold display-6 mb-3"><?php the_title(); ?></h3>
                    <?php if ($nome_misura): ?>
                        <p class="h4 lead text-muted"><?= esc_html($nome_misura); ?></p>
                    <?php endif; ?>
                    <p class="small text-secondary mt-3">
                        <svg class="icon icon-sm me-2" aria-hidden="true">
                            <use href="#it-calendar"></use>
                        </svg>
                        Pubblicato il:
                        <span class="fw-semibold"><?= $date; ?></span>
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <?php
                    $inline = true;
                    get_template_part('template-parts/single/actions');
                    ?>
                </div>
            </div>
        </div>

        <!-- CONTENUTO PRINCIPALE -->
        <div class="container">
            <hr border="2px">
            <div class="row g-4">

                <!-- SEZIONI CONTENUTO -->
                <div class="col-12">

                    <!-- Immagine -->
                    <?php if ($img): ?>
                        <div class="card shadow-sm border-0 mb-4 rounded-4">
                            <div class="card-body text-center">
                                <img src="<?= esc_url($img); ?>" alt="<?= esc_attr(get_the_title()); ?>" class="img-fluid rounded-3 shadow" />
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Descrizione -->
                    <?php if ($descrizione_scopo): ?>
                        <div class="card shadow-sm border-0 mb-4 rounded-4">
                            <div class="card-body">
                                <h2 class="h4 fw-bold mb-3">
                                    <svg class="icon icon-sm me-2" aria-hidden="true">
                                        <use href="#it-pin"></use>
                                    </svg>
                                    Descrizione e Scopo
                                </h2>
                                <div class="richtext-wrapper fs-5 lh-lg">
                                    <?= wp_kses_post($descrizione_scopo); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Dettagli -->
                    <div class="card shadow-sm border-0 mb-4 rounded-4">
                        <div class="card-body">
                            <h2 class="h4 fw-bold mb-3">
                                <svg class="icon icon-sm me-2" aria-hidden="true">
                                    <use href="#it-list"></use>
                                </svg>
                                Dettagli
                            </h2>
                            <dl class="row g-3">
                                <?php if ($missioni): ?>
                                    <dt class="col-sm-4 fw-semibold">Missione:</dt>
                                    <dd class="col-sm-8">
                                        <?php foreach ($missioni as $missione) echo '<span class="badge bg-primary me-2">' . esc_html($missione->name) . '</span>'; ?>
                                    </dd>
                                <?php endif; ?>
                                <dt class="col-sm-4 fw-semibold">Componente:</dt>
                                <dd class="col-sm-8"><?= esc_html($componente); ?></dd>
                                <dt class="col-sm-4 fw-semibold">Investimento:</dt>
                                <dd class="col-sm-8"><?= esc_html($investimento); ?></dd>
                                <dt class="col-sm-4 fw-semibold">Intervento:</dt>
                                <dd class="col-sm-8"><?= esc_html($intervento); ?></dd>
                                <dt class="col-sm-4 fw-semibold">Titolare:</dt>
                                <dd class="col-sm-8"><?= esc_html($titolare); ?></dd>
                                <dt class="col-sm-4 fw-semibold">Soggetto Attuatore:</dt>
                                <dd class="col-sm-8"><?= esc_html($attuatore); ?></dd>
                                <dt class="col-sm-4 fw-semibold">CUP:</dt>
                                <dd class="col-sm-8"><?= esc_html($cup); ?></dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Importo -->
                    <?php if ($importo): ?>
                        <div class="card shadow-sm border-0 mb-4 rounded-4">
                            <div class="card-body text-center">
                                <h2 class="h4 fw-bold mb-3">
                                    <svg class="icon icon-sm me-2" aria-hidden="true">
                                        <use href="#it-card"></use>
                                    </svg>
                                    Importo Finanziato
                                </h2>
                                <p class="fs-3 fw-bold text-success"><?= esc_html($importo); ?></p>
                                <div class="text-center mt-3">
                                    <img class="ue-logo" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRnDeHmoF5L5Fkqq-Ohesy45F6z-_ku02O2Fg&s" alt="Logo Unione Europea" class="img-fluid" style="max-width:250px;">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Avanzamento -->
                    <?php if ($avanzamento_progetto): ?>
                        <div class="card shadow-sm border-0 mb-4 rounded-4">
                            <div class="card-body">
                                <h2 class="h4 fw-bold mb-3">
                                    <svg class="icon icon-sm me-2" aria-hidden="true">
                                        <use href="#it-clock"></use>
                                    </svg>
                                    Avanzamento del Progetto
                                </h2>
                                <div class="richtext-wrapper fs-5 lh-lg">
                                    <?= wp_kses_post($avanzamento_progetto); ?>
                                </div>
                                <?php if ($file_avanzamento): ?>
                                    <div class="mt-4">
                                        <h3 class="h5 fw-semibold mb-3">Documenti di Avanzamento</h3>
                                        <ul class="list-unstyled mb-0">
                                            <?php foreach ($file_avanzamento as $all_url):
                                                $all_id = attachment_url_to_postid($all_url);
                                                $file = get_post($all_id);
                                                $file_name = $file->post_title; ?>
                                                <li class="mb-2">
                                                    <a href="<?= get_the_guid($file) ?>" target="_blank" class="text-decoration-none">
                                                        <svg class="icon icon-sm me-1" aria-hidden="true">
                                                            <use href="#it-file"></use>
                                                        </svg>
                                                        <?= esc_html($file_name); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Atti -->
                    <?php if ($atti): ?>
                        <div class="card shadow-sm border-0 mb-4 rounded-4">
                            <div class="card-body">
                                <h2 class="h4 fw-bold mb-3">
                                    <svg class="icon icon-sm me-2" aria-hidden="true">
                                        <use href="#it-files"></use>
                                    </svg>
                                    Atti Legislativi e Amministrativi
                                </h2>
                                <div class="mt-4">
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($atti as $all_url):
                                            $all_id = attachment_url_to_postid($all_url);
                                            $atto = get_post($all_id);
                                            $atto_name = $atto->post_title; ?>
                                            <li class="mb-2">
                                                <a href="<?= get_the_guid($atto) ?>" target="_blank" class="text-decoration-none">
                                                    <svg class="icon icon-sm me-1" aria-hidden="true">
                                                        <use href="#it-file"></use>
                                                    </svg>
                                                    <?= esc_html($atto_name); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Allegati -->
                    <?php if ($allegati): ?>
                        <div class="card shadow-sm border-0 mb-4 rounded-4">
                            <div class="card-body">
                                <h2 class="h4 fw-bold mb-3">
                                    <h2 class="h4 fw-bold mb-3">
                                        <svg class="icon icon-sm me-2" aria-hidden="true">
                                            <use href="#it-clip"></use>
                                        </svg>
                                        Allegati
                                    </h2>
                                    <div class="mt-4">
                                        <ul class="list-un styled mb-0">
                                            <?php foreach ($allegati as $all_url):
                                                $all_id = attachment_url_to_postid($all_url);
                                                $allegato = get_post($all_id);
                                                $allegato_name = $allegato->post_title; ?>
                                                <li class="mb-2">
                                                    <a href="<?= get_the_guid($allegato) ?>" target="_blank" class="text-decoration-none">
                                                        <svg class="icon icon-sm me-1" aria-hidden="true">
                                                            <use href="#it-file"></use>
                                                        </svg>
                                                        <?= esc_html($allegato_name); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- A cura di -->
                    <?php if ($a_cura_di): ?>
                        <div class="card shadow-sm border-0 mb-4 rounded-4">
                            <div class="card-body">
                                <h2 class="h4 fw-bold mb-3">
                                    <svg class="icon icon-sm me-2" aria-hidden="true">
                                        <use href="#it-pa"></use>
                                    </svg>
                                    A cura di
                                </h2>
                                <div class="richtext-wrapper fs-5 lh-lg">
                                    <div class="row">
                                        <?php foreach ($a_cura_di as $uo_id) {

                                            $with_border = true;
                                            get_template_part("template-parts/unita-organizzativa/card-custom");
                                        } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>


                    <!-- Ulteriori info -->
                    <div class="card shadow-sm border-0 mt-4 rounded-4">
                        <div class="card-body">
                            <h2 class="h4 fw-bold mb-3">
                                <svg class="icon icon-sm me-2" aria-hidden="true">
                                    <use href="#it-info-circle"></use>
                                </svg>
                                Ulteriori informazioni
                            </h2>
                            <?php get_template_part('template-parts/single/page_bottom'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <br>
        <?php get_template_part("template-parts/common/valuta-servizio"); ?>
    <?php } ?>
</main>

<?php get_footer(); ?>
