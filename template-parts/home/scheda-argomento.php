<?php
global $argomento_full, $count, $sito_tematico_id;

$argomento = get_term_by(
    'slug',
    $argomento_full['argomento_'.$count.'_argomento'],
    'argomenti'
);

$icon = dci_get_term_meta('icona', "dci_term_", $argomento->term_id);

if (isset($argomento_full['argomento_'.$count.'_siti_tematici']))
    $sito_tematico_id = $argomento_full['argomento_'.$count.'_siti_tematici'];

if (isset($argomento_full['argomento_'.$count.'_contenuti']))
    $links = $argomento_full['argomento_'.$count.'_contenuti'];
?>


<div class="card card-teaser no-after rounded shadow-sm border border-light argomento-card"
     style="overflow:hidden; position:relative;">

<div class="card-body pb-4 mt-2">


<!-- ================= HEADER ================= -->

<div class="category-top d-flex align-items-center mb-2">

<h3 class="card-title title-xlarge-card mb-0"
    style="font-size:1.3rem;font-weight:600;">

<svg xmlns="http://www.w3.org/2000/svg"
     viewBox="0 0 640 640"
     class="icon text-primary"
     style="width:20px;height:20px;fill:#A2A2A2;pointer-events:none;">

<path d="M371.8 82.4C359.8 87.4 352 99 352 112L352 192L240 192C142.8 192 64 270.8 64 368C64 481.3 145.5 531.9 164.2 542.1C166.7 543.5 169.5 544 172.3 544C183.2 544 192 535.1 192 524.3C192 516.8 187.7 509.9 182.2 504.8C172.8 496 160 478.4 160 448.1C160 395.1 203 352.1 256 352.1L352 352.1L352 432.1C352 445 359.8 456.7 371.8 461.7C383.8 466.7 397.5 463.9 406.7 454.8L566.7 294.8C579.2 282.3 579.2 262 566.7 249.5L406.7 89.5C397.5 80.3 383.8 77.6 371.8 82.6z"/>

</svg>

<?php echo esc_html($argomento->name); ?>

</h3>

</div>


<!-- ================= BADGE ================= -->

<div class="mb-3">

<span style="background:#eef2f7;color:#495057;
font-size:0.75rem;padding:0.25rem 0.6rem;
border-radius:0.5rem;">

Argomento •
<?php echo isset($links) ? count($links)." link" : "0 link"; ?>

</span>

</div>


<!-- ================= DESCRIZIONE ================= -->

<p class="card-text text-muted"
   style="font-size:0.95rem;line-height:1.4;">

<?php echo esc_html($argomento->description); ?>

</p>


<!-- ================= SITO TEMATICO ================= -->

<?php if($sito_tematico_id) { ?>

<p class="card-text pb-3 mt-3 fw-bold">

<svg xmlns="http://www.w3.org/2000/svg"
     viewBox="0 0 640 640"
     width="15" height="15"
     style="vertical-align:middle;margin-right:5px;
            pointer-events:none;">

<path d="M288.6 76.8C344.8 20.6 436 20.6 492.2 76.8C548.4 133 548.4 224.2 492.2 280.4L328.2 444.4C293.8 478.8 238.1 478.8 203.7 444.4C169.3 410 169.3 354.3 203.7 319.9L356.5 167.3C369 154.8 389.3 154.8 401.8 167.3C414.3 179.8 414.3 200.1 401.8 212.6L249 365.3C239.6 374.7 239.6 389.9 249 399.2C258.4 408.5 273.6 408.6 282.9 399.2L446.9 235.2C478.1 204 478.1 153.3 446.9 122.1C415.7 90.9 365 90.9 333.8 122.1L169.8 286.1C116.7 339.2 116.7 425.3 169.8 478.4C222.9 531.5 309 531.5 362.1 478.4L492.3 348.3C504.8 335.8 525.1 335.8 537.6 348.3C550.1 360.8 550.1 381.1 537.6 393.6L407.4 523.6C329.3 601.7 202.7 601.7 124.6 523.6C46.5 445.5 46.5 318.9 124.6 240.8L288.6 76.8z"/>

</svg>

Visita il sito:

</p>

<?php
get_template_part("template-parts/sito-tematico/card_argomento");
?>

<?php } ?>


<!-- ================= LINK LIST ================= -->

<?php if(!empty($links)) { ?>

<div class="link-list-wrapper mt-4">

<ul class="link-list"
    style="padding-left:0;list-style:none;margin:0;">

<?php foreach ($links as $link_id) {

$link_obj = get_post($link_id);
$title = wp_trim_words($link_obj->post_title, 15, '...');

?>

<li style="margin-bottom:10px;">

<a class="list-item icon-left d-flex align-items-center argomento-link"
   href="<?php echo get_permalink(intval($link_id)); ?>"
   target="_blank"
   style="padding:10px 14px;border-radius:8px;
          background:#f8f9fa;text-decoration:none;
          color:#212529;display:flex;
          align-items:center;
          box-shadow:0 2px 4px rgba(0,0,0,0.05);
          transition:all .3s ease;">

<svg class="icon text-secondary me-2"
     style="width:18px;height:18px;
            margin-right:8px;
            pointer-events:none;">

<use xlink:href="#it-link"></use>

</svg>

<span style="font-size:0.95rem;font-weight:500;
             pointer-events:none;">

<?php echo esc_html($title); ?>

</span>

</a>

</li>

<?php } ?>

</ul>

</div>

<?php } ?>


</div>


<!-- ================= FOOTER ================= -->

<div class="card-footer mt-4" style="padding:0;border:none;">

<a class="read-more d-inline-flex align-items-center argomento-link"
   href="<?php echo get_term_link(intval($argomento->term_id), 'argomenti'); ?>"
   style="text-decoration:none;font-weight:500;
          margin-top:20px;color:#0d6efd;">

<span class="text"
      style="font-size:0.85rem;
             pointer-events:none;">

<b>Esplora Argomento</b>

</span>

<svg class="icon ms-1"
     style="width:18px;height:18px;
            pointer-events:none;">

<use xlink:href="#it-arrow-right"></use>

</svg>

</a>

</div>

</div>


<style>

/* ===============================
   FIX WEBVIEW GLOBAL
================================ */

.argomento-card,
.argomento-link {
    touch-action: manipulation;
}

.argomento-card svg,
.argomento-card span {
    pointer-events: none;
}

/* Disabilita hover mobile */
@media (hover:none){

.argomento-link:hover{
    background:inherit;
    transform:none;
}

}

</style>


<?php
$sito_tematico_id = null;







