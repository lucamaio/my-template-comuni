<?php 
  $links = dci_get_option('contenuti','ricerca');
  $unique_id = 'search-' . uniqid();
?>
<!-- Search Modal -->
<div
    class="modal fade search-modal"
    id="search-modal"
    tabindex="-1"
    role="dialog"
    aria-hidden="true"
  >
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content perfect-scrollbar">
      <div class="modal-body">
        <form role="search" id="search-form-modal" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
          <div class="container">

            <div class="row variable-gutters">
              <div class="col">
                <div class="modal-title">
                  <button class="search-link d-md-none" type="button" data-bs-toggle="modal" data-bs-target="#search-modal">
                    <svg class="icon icon-md"><use href="#it-arrow-left"></use></svg>
                  </button>

                  <p><span class="h2"><?php _e("","design_comuni_italia"); ?></span></p>

                  <button class="search-link d-none d-md-block" type="button" data-bs-toggle="modal" data-bs-target="#search-modal" data-dismiss="modal">
                    <svg class="icon icon-md"><use href="#it-close-big"></use></svg>
                  </button>
                </div>

                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <div class="input-group-text">
                        <svg class="icon icon-md"><use href="#it-search"></use></svg>
                      </div>
                    </div>

                    <label for="<?php echo $unique_id; ?>">Con Etichetta</label>
                    <input
                      type="search"
                      class="form-control"
                      id="<?php echo $unique_id; ?>"
                      name="s"
                      placeholder="<?php _e("Cerca nel sito","design_comuni_italia"); ?>"
                      value="<?php echo get_search_query(); ?>"
                    />
                  </div>

                  <button type="submit" class="btn btn-primary">
                    <span>Cerca</span>
                  </button>   
                </div>
              </div>
            </div>

            <div class="row variable-gutters p-4">

              <!-- SINISTRA -->
              <div class="col-lg-5">    

                <a href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" class="chip chip-simple chip-lg">
                  <span class="chip-label">RICERCA PARAMETRICA</span>
                </a>

                <p></p>

                <!-- RICERCHE FREQUENTI -->
                <div class="h4 other-link-title">Ricerche frequenti</div>

                <div class="link-list-wrapper mb-4 scroll-frequenti">
                  <ul class="link-list">
                    <?php
                    $popular_posts = new WP_Query([
                        'post_type' => dci_get_sercheable_tipologie(),
                        'posts_per_page' => 7,
                        'meta_key' => 'views',
                        'orderby' => 'meta_value_num',
                        'order' => 'DESC'
                    ]);

                    if (empty($popular_posts->posts)) {
                        $popular_posts = new WP_Query([
                            'post_type' => dci_get_sercheable_tipologie(),
                            'posts_per_page' => 7,
                            'orderby' => 'date',
                            'order' => 'DESC',
                        ]);
                    }

                    if (!empty($popular_posts->posts)) {
                      foreach ($popular_posts->posts as $post) {
                        setup_postdata($post); ?>
                        <li>
                          <a class="list-item active large py-1 icon-left" href="<?php the_permalink(); ?>">
                            <span class="list-item-title-icon-wrapper">
                              <svg class="icon icon-primary icon-sm"><use href="#it-search"></use></svg>
                              <span class="list-item-title"><?php the_title(); ?></span>
                            </span>
                          </a>
                        </li>
                    <?php } } else { ?>
                        <li>Nessun risultato</li>
                    <?php } wp_reset_postdata(); ?>
                  </ul>
                </div>

                <!-- SCELTI PER TE -->
                <?php if ($links) { ?>
                  <div class="h4 other-link-title">Scelti per te</div>
                  <div class="link-list-wrapper mb-4">
                    <ul class="link-list">
                      <?php foreach ($links as $link_id) { 
                        $link = get_post($link_id); ?>
                        <li>
                          <a class="list-item active large py-1 icon-left" href="<?php echo get_permalink($link_id); ?>">
                            <span class="list-item-title-icon-wrapper">
                              <svg class="icon icon-primary icon-sm">
                                <use href="#it-link"></use>
                              </svg>
                              <span class="list-item-title"><?php echo $link->post_title; ?></span>
                            </span>
                          </a>
                        </li>
                      <?php } ?>
                    </ul>
                  </div>
                <?php } ?>

              </div> 

              <!-- DESTRA -->
              <div class="col-lg-6">
                <?php
                $argomenti = get_terms([
                    'taxonomy' => 'argomenti',
                    'orderby' => 'count',
                    'order'   => 'DESC',
                    'hide_empty' => 1,
                    'number' => 20
                ]);

                if(!empty($argomenti)) { ?>
                  <div class="badges-wrapper">
                    <div class="h4 other-link-title"><?php _e("Potrebbero interessarti","design_comuni_italia"); ?></div>
                    <div class="badges">
                      <?php foreach ($argomenti as $argomento){
                        $taglink = get_tag_link($argomento); ?>
                        <a href="<?php echo $taglink; ?>" class="chip chip-simple chip-lg">
                          <span class="chip-label"><?php echo $argomento->name; ?></span>
                        </a>
                      <?php } ?>
                    </div>
                  </div>
                <?php } ?>
              </div>

            </div>   
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- CSS -->
<style>
/* ===== MODAL GENERALE ===== */
.search-modal .modal-content {
  border-radius: 12px;
  border: none;
}

.search-modal .modal-body {
  padding: 30px 25px;
}

/* ===== INPUT RICERCA ===== */
.search-modal .form-control {
  height: 48px;
  font-size: 16px;
}

.search-modal .btn-primary {
  height: 48px;
  font-size: 15px;
  padding: 0 20px;
  margin-top: 10px;
}

/* ===== TITOLI ===== */
.other-link-title {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 10px;
}

/* ===== LISTE ===== */
.link-list .list-item {
  font-size: 14px;
  line-height: 1.4;
  padding: 6px 0;
}

/* icone meglio proporzionate */
.link-list .icon {
  width: 16px;
  height: 16px;
}

/* spazio tra icona e testo */
.list-item-title-icon-wrapper {
  gap: 6px;
}

/* ===== RICERCHE FREQUENTI SCROLL ===== */
.scroll-frequenti {
  max-height: 220px;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-overflow-scrolling: touch;
  padding-right: 6px;
}

/* scrollbar elegante */
.scroll-frequenti::-webkit-scrollbar {
  width: 6px;
}

.scroll-frequenti::-webkit-scrollbar-thumb {
  background: #d0d0d0;
  border-radius: 6px;
}

/* ===== SCELTI PER TE ===== */
.link-list-wrapper .list-item {
  transition: all 0.15s ease;
}

/* hover leggero (molto elegante) */
.link-list-wrapper .list-item:hover {
  transform: translateX(3px);
  color: var(--bs-primary);
}

/* ===== CHIP DESTRA ===== */
.badges .chip {
  font-size: 13px;
  padding: 6px 10px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .search-modal .modal-body {
    padding: 20px 15px;
  }

  .other-link-title {
    font-size: 16px;
  }

  .link-list .list-item {
    font-size: 13px;
  }
}
</style>

<!-- End Search Modal -->
