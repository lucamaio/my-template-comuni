<?php
global $the_query, $load_posts, $load_card_type;

$per_page = 9;
$load_posts = $per_page;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$paged_from_query = get_query_var('paged');
$paged_from_get = isset($_GET['paged']) ? absint($_GET['paged']) : 0;
$paged = max(1, (int) ($paged_from_query ? $paged_from_query : $paged_from_get));

$args = array(
    's' => $query,
    'posts_per_page' => $per_page,
    'post_type'      => array('documento_pubblico', 'dataset'),
    'post_status'    => 'publish',
    'orderby'        => 'publish',
    'order'          => 'DESC',
    'ignore_sticky_posts' => true,
    'paged'          => $paged,
);

$the_query = new WP_Query($args);
?>

<div class="bg-grey-card py-5">
  <form role="search" id="search-form" method="get" class="search-form">
    <button type="submit" class="d-none"></button>
    <div class="container">
      <h2 class="title-xxlarge mb-4">
        Esplora tutti i documenti
      </h2>
      <div>
        <div class="cmp-input-search">
          <div class="form-group autocomplete-wrapper mb-0">
            <div class="input-group">
              <label for="autocomplete-two" class="visually-hidden">Cerca</label>
              <input type="search" class="autocomplete form-control" placeholder="Cerca per parola chiave"
                id="autocomplete-two" name="search" value="<?php echo $query; ?>" data-bs-autocomplete="[]" />
              <div class="input-group-append">
                <button class="btn btn-primary" type="submit" id="button-3">
                  Invio
                </button>
              </div>
              <span class="autocomplete-icon" aria-hidden="true"><svg class="icon icon-sm icon-primary" role="img"
                  aria-labelledby="autocomplete-label">
                  <use href="#it-search"></use>
                </svg></span>
            </div>
            <p id="autocomplete-label" class="u-grey-light text-paragraph-card mt-2 mb-4 mt-lg-3 mb-lg-40">
              <?php echo $the_query->found_posts; ?> documenti trovati in ordine alfabetico
            </p>
          </div>
        </div>
      </div>
      <div class="row g-4" id="load-more">
          <?php
          $load_card_type = 'documento';
          if ($the_query->have_posts()) {
              while ($the_query->have_posts()) {
                  $the_query->the_post();
                  get_template_part('template-parts/documento/cards-list');
              }
          } else {
              get_template_part('template-parts/content', 'none');
          }
          ?>
      </div>
      <?php if ($the_query->max_num_pages > 1) : ?>
      <div class="row my-4">
          <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine documenti">
              <?php dci_bootstrap_pagination($the_query); ?>
          </nav>
      </div>
      <?php endif; ?>
    </div>
  </form>
</div>
<?php wp_reset_postdata(); ?>
