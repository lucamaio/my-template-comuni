<?php 
global $post;

$description = dci_get_meta('descrizione_breve');
$incarichi = dci_get_meta('incarichi');

$incarico = is_array($incarichi) ? $incarichi[0] : null;

$tipo = is_array(get_the_terms($incarico, 'tipi_incarico')) ? get_the_terms($incarico, 'tipi_incarico')[0] : null;

$prefix = '_dci_incarico_';
$nome_incarico = $tipo != NULL ? dci_get_meta('nome', $prefix, $tipo->term_id) : "";
$tipo_name = $tipo != NULL ? $tipo->name : "";

$img = dci_get_meta('foto');

/* NORMALIZZAZIONE TESTI */
if (preg_match('/[A-Z]{5,}/', $post->post_title)) {
    $titolo = ucfirst(strtolower($post->post_title));
} else {
    $titolo = $post->post_title;
}

if (preg_match('/[A-Z]{5,}/', $description)) {
    $descrizione = ucfirst(strtolower($description));
} else {
    $descrizione = $description;
}

$descrizione_mobile = $descrizione;
if (!empty($descrizione_mobile) && function_exists('mb_strlen') && function_exists('mb_substr') && mb_strlen($descrizione_mobile) > 100) {
    $descrizione_mobile = rtrim(mb_substr($descrizione_mobile, 0, 100)) . '...';
} elseif (!empty($descrizione_mobile) && strlen($descrizione_mobile) > 100) {
    $descrizione_mobile = rtrim(substr($descrizione_mobile, 0, 100)) . '...';
}

if ($tipo_name != "politico") {
?>

<div class="col-md-6 col-xl-4">
    <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr dci-person-card-wrapper">
        <div class="card no-after rounded">

            <div class="row g-2 g-md-0 flex-column flex-md-row dci-person-card__row">

                <!-- CONTENUTO (SINISTRA) -->
                <div class="<?php echo $img ? 'col-8 col-md-8' : 'col-12'; ?> dci-person-card__content">
                    <div class="card-body">
                        <a class="text-decoration-none" href="<?php echo get_permalink(); ?>" data-element="administration-element">
                            <h3 class="h5 card-title"><?php echo esc_html($titolo); ?></h3>
                        </a>

                        <?php if (!empty($descrizione)) { ?>
                            <p class="card-text d-md-none">
                                <?php echo esc_html($descrizione_mobile); ?>
                            </p>
                            <p class="card-text d-none d-md-block">
                                <?php echo esc_html($descrizione); ?>
                            </p>
                        <?php } ?>
                    </div>
                </div>

                <!-- IMMAGINE (DESTRA) -->
                <?php if ($img) { ?>
                    <div class="col-4 col-md-4 d-flex align-items-center justify-content-center p-2 dci-person-card__media">
                        <div class="img-wrapper">
                            <?php dci_get_img($img, 'img-fluid img-responsive foto-fixed'); ?>
                        </div>
                    </div>
                <?php } ?>

            </div>

        </div>
    </div>
</div>

<?php } else { ?>

<div class="col-md-6 col-xl-4">
    <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr dci-person-card-wrapper">
        <div class="card no-after rounded">

            <div class="row g-2 g-md-0 flex-column flex-md-row">

                <!-- CONTENUTO -->
                <div class="col-12">
                    <div class="card-body card-img-none rounded-top">
                        <a class="text-decoration-none" href="<?php echo get_permalink(); ?>" data-element="administration-element">
                            <h3 class="h5 card-title"><?php echo esc_html($titolo); ?></h3>
                        </a>

                        <?php if (!empty($descrizione)) { ?>
                            <p class="card-text d-md-none">
                                <?php echo esc_html($descrizione_mobile); ?>
                            </p>
                            <p class="card-text d-none d-md-block">
                                <?php echo esc_html($descrizione); ?>
                            </p>
                        <?php } ?>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<?php } ?>

<style>
    .img-wrapper {
        width: 100%;
        max-width: 110px;
        height: 110px;
        overflow: hidden;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #fff;
    }

    .foto-fixed {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 4px;
    }

    @media (max-width: 767.98px) {
        .dci-person-card-wrapper {
            padding-bottom: 0;
            overflow: hidden;
        }

        .dci-person-card-wrapper > .card {
            height: 100%;
        }

        .dci-person-card__row {
            flex-direction: row !important;
            flex-wrap: nowrap;
            align-items: stretch;
        }

        .dci-person-card__content {
            flex: 1 1 auto;
            min-width: 0;
        }

        .dci-person-card__media {
            flex: 0 0 104px;
            max-width: 104px;
            padding: .75rem .75rem .75rem 0 !important;
        }

        .dci-person-card__content .card-body {
            padding-right: .5rem;
        }

        .img-wrapper {
            max-width: 96px;
            height: 96px;
            margin-left: auto;
        }
    }
</style>
