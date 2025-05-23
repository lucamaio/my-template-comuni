<?php
global $scheda;

$post = get_post($scheda)??null;
$img = dci_get_meta('immagine');
$descrizione_breve = dci_get_meta('descrizione_breve');
$icon = dci_get_post_type_icon_by_id($post->ID);
$page = get_page_by_path( dci_get_group($post->post_type) ); 
$page_macro_slug = dci_get_group($post->post_type);
$page_macro = get_page_by_path($page_macro_slug);

?>
<?php if ($img) { ?>

<?php } else { ?>
    
<?php } ?>

<div class="card-wrapper px-0 card-overlapping card-teaser-wrapper card-teaser-wrapper-equal card-teaser-block-3">
    <div class="card card-teaser card-teaser-image card-flex no-after rounded shadow">
        <div class="card-image-wrapper with-read-more pb-5">
            <div class="card-body p-4">
                <div class="category-top">
                <svg class="icon">
                    <use xlink:href="#it-pa"></use>
                </svg>
                <a class="category" href="#"><?php echo $post->post_type ?></a>
                </div>
                <p class="card-title fw-semibold"><?php echo $post->post_title ?></p>
                <p class="card-text"><?php echo $descrizione_breve ?></p>
            </div>
            <div class="card-image card-image-rounded pb-5">
                <?php dci_get_img($img); ?>
            </div>
        </div>

        <a class="read-more ps-4" href="<?php echo dci_get_template_page_url("page-templates/notizie.php"); ?>">
        <span class="text">Tutte le novità</span>
        <svg class="icon">
            <use xlink:href="#it-arrow-right"></use>
        </svg>
        </a>
    </div>
</div>