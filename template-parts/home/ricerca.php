<?php
$links = dci_get_option('link', 'link_utili');
?>

<section id="link-utili" class="useful-links-section bg-grey-card py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10">

        <!-- Titolo sezione -->
        <h2 class="title-xxlarge mb-4">Link utili</h2>
        <!-- Form ricerca -->
        <form role="search" method="get" class="search-form mb-4" action="<?php echo esc_url(home_url('/')); ?>">
          <div class="input-group shadow-sm">
            <input type="search" class="form-control" placeholder="Cerca una parola chiave" name="s" value="<?php echo get_search_query(); ?>" aria-label="Cerca una parola chiave">
            <button class="btn btn-primary" type="submit">
              <svg class="icon icon-sm me-1" aria-hidden="true" style="fill: white;">
                <use href="#it-search"></use>
              </svg>
              Cerca
            </button>
          </div>
        </form>

        <!-- Link utili -->
        <?php if ($links) { ?>
          <div class="d-flex flex-wrap gap-3">
            <?php foreach ($links as $link) { ?>
              <a href="<?php echo esc_url($link['url']); ?>" target="_blank" class="d-inline-flex align-items-center gap-2 px-3 py-2 border border-light rounded shadow-sm bg-white text-primary text-decoration-none link-hover flex-shrink-0" style="max-width: 100%;">
                <svg class="icon icon-primary" aria-hidden="true" style="min-width: 1.2rem;">
                  <use href="#it-link"></use>
                </svg>
                <span class="fw-semibold text-truncate" style="max-width: 200px;">
                  <?php echo esc_html($link['testo']); ?>
                </span>
              </a>
            <?php } ?>
          </div>
        <?php } else { ?>
          <p class="text-muted">Nessun link disponibile al momento.</p>
        <?php } ?>

      </div>
    </div>
  </div>
</section>


