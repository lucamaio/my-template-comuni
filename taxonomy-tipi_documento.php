<?php
/**
 * Archivio Tassonomia Tipi Documento
 *
 * @package Design_Comuni_Italia
 */

global $the_query, $load_posts, $load_card_type, $documento, $tax_query, $title, $description, $data_element, $hide_categories;

$obj = get_queried_object();
$per_page = dci_sanitize_posts_per_page(apply_filters('dci_document_taxonomy_posts_per_page', 9), 9, 24);
$load_posts = $per_page;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$paged_from_query = get_query_var('paged');
$paged_from_get = isset($_GET['paged']) ? absint($_GET['paged']) : 0;
$paged = max(1, (int) ($paged_from_query ? $paged_from_query : $paged_from_get));

// Aggiungi un filtro tax_query per limitare i risultati ai documenti della tassonomia selezionata
$args = array(
    's' => $query,
    'posts_per_page' => $per_page,
    'post_status'    => 'publish',
    'post_type'      => 'documento_pubblico',
    'orderby'        => 'text_date_timestamp',    
    'order'          => 'DESC',
    'ignore_sticky_posts' => true,
    'paged'          => $paged,
    'tax_query'      => array(
        array(
            'taxonomy' => 'tipi_documento', // La tassonomia personalizzata
            'field'    => 'slug',           // Filtra per slug
            'terms'    => $obj->slug,       // Ottieni il termine della tassonomia selezionata
        ),
    ),
);

$the_query = new WP_Query($args);

get_header();
?>
<main>
    <?php 
      $title = $obj->name;
      $description = $obj->description;
      $data_element = 'data-element="page-name"';
      get_template_part("template-parts/hero/hero"); 
    ?>
  
    <div class="bg-grey-card">
      <form role="search" id="search-form" method="get" class="search-form">
          <button type="submit" class="d-none"></button>
          <div class="container">
            <div class="row ">
              <h2 class="visually-hidden">Esplora tutti i documenti</h2>
              <div class="col-12 pt-30 pt-lg-50 pb-lg-50">
                <div class="cmp-input-search">
                  <div class="form-group autocomplete-wrapper mb-2 mb-lg-4">
                  <div class="input-group">
                  <label for="autocomplete-two" class="visually-hidden">Cerca una parola chiave</label>
                  <input type="search" 
                    class="autocomplete form-control" 
                    placeholder="Cerca una parola chiave"
                    id="autocomplete-two"
                    name="search"
                    value="<?php echo esc_attr($query); ?>"
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
                  <p id="autocomplete-label" class="mb-4"><strong><?php echo $the_query->found_posts; ?> </strong>documenti trovati in ordine alfabetico</p>
                </div>
                <div class="row g-4" id="load-more">
                    <?php
                    $load_card_type = "documento";
                    $hide_categories = true;
                    $full_width = true;

                    if ($the_query->have_posts()) {
                        while ($the_query->have_posts()) {
                            $the_query->the_post();
                            get_template_part("template-parts/documento/cards-list");
                        }
                    } else {
                        get_template_part('template-parts/content', 'none');
                    }
                    ?>
                </div>
                <?php if ($the_query->max_num_pages > 1) : ?>
                    <div class="row my-4">
                        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine documenti">
                            <?php echo dci_bootstrap_pagination($the_query, false); ?>
                        </nav>
                    </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
      </form>
    </div>
    
    <?php echo get_template_part( 'template-parts/common/valuta-servizio'); ?>
    <?php echo get_template_part( 'template-parts/common/assistenza-contatti'); ?>
</main>
<?php
wp_reset_postdata();
get_footer();
?>
