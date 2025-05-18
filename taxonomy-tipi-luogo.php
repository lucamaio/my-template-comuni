<?php
/**
 * Archivio Tassonomia tipi luogo
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#custom-taxonomies
 * @link https://italia.github.io/design-comuni-pagine-statiche/sito/tipi-luogo.html
 *
 * @package Design_Comuni_Italia
 */

 
global $the_query, $load_posts, $load_card_type, $luogo, $tax_query, $title, $description, $data_element, $hide_tipos;

$obj = get_queried_object();
$max_posts = isset($_GET['max_posts']) ? $_GET['max_posts'] : 3;
$load_posts = 3;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$args = array(
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type'      => 'luogo',
    'tipo_luogo' => $obj->slug,
    'orderby'        => 'post_title',
    'order'          => 'ASC'
);
$the_query = new WP_Query( $args );
$luoghi = $the_query->posts;

$tax_query = array(
  array(
    'taxonomy' => 'tipo_luogo',
    'field' => 'slug',
    'terms' => $obj->slug
  )
);

$amministrazione = dci_get_related_unita_amministrative();

get_header();
?>
 <main>
    <?php 
      $title = $obj->name;
      var_dump($obj);
      $description = $obj->description;
      $data_element = 'data-element="page-name"';
      get_template_part("template-parts/hero/hero"); 
    ?>

    <div class="bg-grey-card">
      <form role="search" id="search-form" method="get" class="search-form">
          <button type="submit" class="d-none"></button>
          <div class="container">
            <div class="row ">
              <h2 class="visually-hidden">Esplora tutti i luoghi</h2>
              <div class="col-12 col-lg-8 pt-30 pt-lg-50 pb-lg-50">
                <div class="cmp-input-search">
                  <div class="form-group autocomplete-wrapper mb-2 mb-lg-4">
                  <div class="input-group">
                  <label for="autocomplete-two" class="visually-hidden">Cerca una parola chiave</label>
                  <input type="search" 
                    class="autocomplete form-control" 
                    placeholder="Cerca una parola chiave"
                    id="autocomplete-two"
                    name="search"
                    value="<?php echo $query; ?>"
                    data-bs-autocomplete="[]">
                  <div class="input-group-append">
                      <button class="btn btn-primary" type="submit" id="button-3">
                          Invio
                      </button>
                  </div>
                  <span class="autocomplete-icon" aria-hidden="true">
                    <svg class="icon icon-sm icon-primary" role="img" aria-labelledby="autocomplete-label"><use href="#it-search"></use></svg>
                  </span>
                  </div>
                  </div>
                </div>
                <div class="row g-4" id="load-more">
                    <?php foreach ($luoghi as $luogo) { 
                        $load_card_type = "tipo_luogo";
                        $hide_tipos = true;
                        get_template_part("template-parts/luogo/card");    
                    } ?>
                 </div>
                       <?php get_template_part("template-parts/search/more-results"); ?>
              </div>
              
              <?php if ( is_array($amministrazione) && count($amministrazione) ) { ?>
                <div class="col-12 col-lg-4 pt-50 pb-30 pt-lg-5 ps-lg-5">
                  <div class="link-list-wrap">
                    <h2 class="title-xsmall-semi-bold"><span>UFFICI</span></h2>
                    <ul class="link-list t-primary">
                      <?php foreach ($amministrazione as $item) { ?>
                        <li class="mb-3 mt-3">
                          <a class="list-item ps-0 title-medium underline" href="<?php echo $item['link']; ?>">
                            <span><?php echo $item['title']; ?></span>
                          </a>
                        </li>
                      <?php } ?>                      
                      <li>
                        <a class="list-item ps-0 text-button-xs-bold d-flex align-items-center text-decoration-none" href="<?php echo get_permalink( get_page_by_path( 'amministrazione' ) ); ?>">
                          <span class="mr-10">VAI ALL’AREA AMMINISTRATIVA</span>
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
           
    <?php echo get_template_part( 'template-parts/common/valuta-luogo'); ?>
    <?php echo get_template_part( 'template-parts/common/assistenza-contatti'); ?>
  </main>
<?php
get_footer();
