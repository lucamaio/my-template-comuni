<?php
global $argomento_full, $count, $sito_tematico_id;

$argomento = get_term_by('slug', $argomento_full['argomento_'.$count.'_argomento'], 'argomenti');

$icon = dci_get_term_meta('icona', "dci_term_", $argomento->term_id);

if (isset($argomento_full['argomento_'.$count.'_siti_tematici']))
  $sito_tematico_id = $argomento_full['argomento_'.$count.'_siti_tematici'];
if (isset($argomento_full['argomento_'.$count.'_contenuti']))
  $links = $argomento_full['argomento_'.$count.'_contenuti'];
?>

<div class="card card-teaser no-after rounded shadow-sm border border-light" style="overflow:hidden; position:relative;">
  
  <!-- Icona posizionata sopra la card, in alto a sinistra -->
<svg class="icon text-primary" style="position:absolute; top:7px; left:0px; width:18px; height:18px; z-index:1;">
  <use xlink:href="#it-share"></use>
</svg>
  <div class="card-body pb-4" style="padding-top: 20px;">
    <!-- card head -->
    <div class="category-top d-flex align-items-center mb-2" style="text-align:left;">
      <h3 class="card-title title-xlarge-card mb-0" style="font-size:1.3rem; font-weight:600;">
        <?php echo $argomento->name ?>
      </h3>
    </div>

    <!-- badge -->
    <div class="mb-3">
      <span style="background-color:#eef2f7; color:#495057; font-size:0.75rem; padding:0.25rem 0.6rem; border-radius:0.5rem;">
        Argomento • <?php echo isset($links) && is_array($links) ? count($links) . " link" : "0 link"; ?>
      </span>
    </div>

    <p class="card-text text-muted" style="font-size:0.95rem; line-height:1.4;">
      <?php echo $argomento->description ?>
    </p>

    <!-- sito tematico -->
    <?php if($sito_tematico_id) { ?>
      <p class="card-text pb-3 mt-3 fw-bold">🌐 Visita il sito:</p>
      <?php 
        $custom_class = "no-after mt-0";
        get_template_part("template-parts/sito-tematico/card_argomento");
      ?>
    <?php } ?>

    <!-- links -->
    <?php if(isset($links) && is_array($links) && count($links)) { ?>
      <div class="link-list-wrapper mt-4">
        <ul class="link-list" style="padding-left:0; list-style:none; margin:0;">
          <?php foreach ($links as $link_id) { 
            $link_obj = get_post($link_id);
          ?>
            <li style="margin-bottom:10px;">
              <a class="list-item icon-left d-flex align-items-center"
                 href="<?php echo get_permalink(intval($link_id)); ?>"
                 style="padding:10px 14px; border-radius:8px; background-color:#f8f9fa; text-decoration:none; color:#212529; display:flex; align-items:center; box-shadow:0 2px 4px rgba(0,0,0,0.05); transition: all 0.3s ease;"
                 onmouseover="this.style.backgroundColor='#e2e6ea'; this.style.transform='translateY(-2px)';"
                 onmouseout="this.style.backgroundColor='#f8f9fa'; this.style.transform='translateY(0)';"
              >
                <svg class="icon text-secondary me-2" style="width:18px; height:18px; margin-right:8px;">
                  <use xlink:href="#it-link"></use>
                </svg>
                <span style="font-size:0.95rem; font-weight:500;"><?php echo $link_obj->post_title; ?></span>
              </a>
            </li>
          <?php } ?>
        </ul>
      </div>
    <?php } ?>

  </div>

  <!-- footer -->
  <div class="card-footer mt-4" style="padding:0; border:none;">
    <a class="read-more d-inline-flex align-items-center"
       href="<?php echo get_term_link(intval($argomento->term_id), 'argomenti'); ?>"
       style="text-decoration:none; font-weight:500; margin-left:0 !important; padding-left:0 !important; margin-top:20px; color:#0d6efd;"
       aria-label="Esplora l'argomento <?php echo $argomento->name; ?>"
       title="Esplora l'argomento <?php echo $argomento->name; ?>"
       data-focus-mouse="false"
    >
        <span class="text" style="font-size:0.80rem;"><b>Esplora argomento</b></span>
        <svg class="icon ms-1" style="width:18px; height:18px;">
            <use xlink:href="#it-arrow-right"></use>
        </svg>
    </a>
  </div>

</div>


<?php
$sito_tematico_id = null;
