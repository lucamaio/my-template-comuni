<?php
global $post;

$argomenti_evidenza = dci_get_option('argomenti_evidenziati','argomenti');

if (is_array($argomenti_evidenza) && count($argomenti_evidenza)) {
?>

<div class="container py-5">

<h2 class="title-xxlarge mb-4">In evidenza</h2>

<div class="row g-4">

<?php foreach ($argomenti_evidenza as $arg_id) {

$argomento = get_term_by('id', $arg_id, 'argomenti');

$img = dci_get_term_meta('immagine', "dci_term_", $argomento->term_id);

?>

<div class="col-sm-6 col-lg-4">

<div class="it-grid-item-wrapper it-grid-item-overlay evidenza-item">

<a href="<?php echo esc_url(get_term_link($argomento->term_id)); ?>"
   class="evidenza-link">

<div class="img-responsive-wrapper">

<div class="img-responsive">

<div class="img-wrapper">

<?php if ($img) { ?>

<?php dci_get_img($img); ?>

<?php } else { ?>

<img
  src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/img/placeholder_grey.jpeg"
  alt="descrizione immagine"
  title="Image Title"
/>

<?php } ?>

</div>
</div>
</div>

<span class="it-griditem-text-wrapper">

<h3><?php echo esc_html($argomento->name); ?></h3>

</span>

</a>

</div>

</div>

<?php } ?>

</div>

</div>

<?php } ?>


<style>

/* ===============================
   FIX WEBVIEW CLICK
================================ */

.evidenza-item,
.evidenza-link {
    touch-action: manipulation;
}

/* Disabilita overlay interni */
.evidenza-item img,
.evidenza-item span,
.evidenza-item h3,
.evidenza-item .img-wrapper,
.evidenza-item .img-responsive,
.evidenza-item .img-responsive-wrapper {
    pointer-events: none;
}

/* Mantieni click solo su <a> */
.evidenza-link {
    display: block;
}

/* Evita hover mobile */
@media (hover:none){

.evidenza-item:hover{
    transform:none;
}

}

</style>
