<?php
    global $luogo, $luoghi;
    $prefix = '_dci_luogo_';
    $img = dci_get_meta('immagine', $prefix, $luogo->ID);
    $indirizzo = dci_get_meta('indirizzo', $prefix, $luogo->ID);
?>
<div class="col-lg-6 col-xl-4">
    <div class="card-wrapper shadow-sm rounded cmp-list-card-img">
        <div class="card card-img no-after rounded">
            <div class="img-responsive-wrapper cmp-list-card-img__wrapper">
                <div class="img-responsive img-responsive-panoramic h-100">
                    <figure class="img-wrapper">
                        <?php dci_get_img($img, 'rounded-top img-fluid'); ?>
                    </figure>
                </div>
            </div>
            <div class="card-body">
                <h3 class="cmp-list-card-img__body-title u-main-primary">
                    <a class="text-decoration-none" href=" <?php echo get_permalink($luogo->ID) ?>"> <?php echo $luogo->post_title; ?></a>
                </h3>
                <p class="cmp-list-card-img__body-description">
                    <?php echo $indirizzo; ?>
                </p>
                <a
                    class="read-more t-primary text-uppercase cmp-list-card-img__body-link"
                    href="<?php echo get_permalink($luogo->ID); ?>" aria-label="Leggi di più sulla pagina di <?php echo $luogo->post_title ?>">
                    <span class="text">Leggi di più</span>
                    <span class="visually-hidden"
                    ></span
                    >
                    <svg class="icon icon-primary icon-xs ml-10">
                    <use
                        href="#it-arrow-right"
                    ></use></svg>
                </a>
            </div>
        </div>
    </div>
</div>

<?php 
    $luoghi = array($luogo);
    get_template_part("template-parts/luogo/map"); 
?>
<style>
    .col-lg-6 .col-xl-4{
        background-color:gray;
    }
</style>
