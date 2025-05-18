<?php
global $uo_id, $with_border;
$ufficio = get_post($uo_id);

$prefix = '_dci_unita_organizzativa_';
$img = dci_get_meta('immagine', $prefix, $uo_id);
$description = dci_get_meta('descrizione_breve', $prefix, $uo_id);
?>

<div class="col-md-6 mb-4">
    <div class="card card-teaser border rounded shadow p-4 d-flex flex-row align-text-justify  h-100">
        <!-- Testo -->
        <div class="card-body pe-3">
            <h4 class="u-main-black mb-1 title-small-semi-bold-medium">
                <a class="text-decoration-none" href="<?php echo get_permalink($ufficio->ID); ?>">
                    <?php echo $ufficio->post_title; ?>
                </a>
            </h4>
            <?php if ($description) { ?>
                <div class="card-text">
                    <p><?php echo $description; ?></p>
                </div>
            <?php } ?>
        </div>

        <!-- Immagine avatar a destra -->
        <?php if ($img) { ?>
            <div class="avatar size-xl">
                <?php dci_get_img($img); ?>
            </div>
        <?php } ?>
    </div>
</div>
