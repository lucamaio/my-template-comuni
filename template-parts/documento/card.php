<?php
global $documento;

$url = get_permalink($documento->ID);
?>
<div class="card card-teaser shadow-sm p-4s rounded border border-light flex-nowrap">
    <svg class="icon" aria-hidden="true">
        <use xlink:href="#it-clip"></use>
    </svg>
    <div class="card-body">
        <h5 class="card-title">
            <?php
             if(preg_match('/[A-Z]{5,}/', $documento->post_title)){
                echo ''.$url.'">'.$url.'';
                $titolo_documento=ucfirst(strtolower($documento->post_title));
            } else {
                $titolo_documento= $documento->post_title;
            }?>

            <a class="text-decoration-none" href="<?php echo $url; ?>"
                aria-label="Vai al documento <?php echo $titolo_documento; ?>"
                title="Vai al documento <?php echo $titolo_documento; ?>">
                <?php echo $titolo_documento; ?>
            </a>

        </h5>
        <div class="card-text">
            <p>
                <?php echo dci_get_meta('descrizione_breve', '_dci_documento_pubblico_', $documento->ID); ?>
            </p>
        </div>
    </div>
</div>
