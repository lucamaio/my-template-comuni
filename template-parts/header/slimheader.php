<div class="it-header-slim-wrapper d-none d-lg-block"> <!-- visibile solo da LG in su -->
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="it-header-slim-wrapper-content d-flex justify-content-between align-items-center py-2">
          <!-- Nome Regione -->
          
          <a class="navbar-brand text-decoration-none"
             href="<?php echo esc_url(dci_get_option("url_sito_regione")); ?>"
             target="_blank"
             rel="noopener"
             aria-label="Vai al portale <?php echo esc_attr(dci_get_option("nome_regione")); ?> - link esterno"
             title="Vai al portale <?php echo esc_attr(dci_get_option("nome_regione")); ?>">
              <font color="white"><?php echo esc_html(dci_get_option("nome_regione")); ?></font>
          </a>

          <!-- Link desktop -->
          <div class="it-header-slim-right-zone d-flex align-items-center" role="navigation">
            <?php if (dci_get_option("link_ammtrasparente")) : ?>
              <div class="nav-item me-3">
                <a class="navbar-brand text-decoration-none"
                   href="<?php echo esc_url(dci_get_option("link_ammtrasparente")); ?>"
                   target="_blank"
                   rel="noopener"
                   aria-label="Amministrazione trasparente">
                <font color="white">Amministrazione trasparente</font>
                </a>
              </div>
            <?php endif; ?>

            <?php if (dci_get_option("link_albopretorio")) : ?>
              <div class="nav-item me-3">
                <a class="navbar-brand text-decoration-none"
                   href="<?php echo esc_url(dci_get_option("link_albopretorio")); ?>"
                   target="_blank"
                   rel="noopener"
                   aria-label="Albo pretorio">
                 <font color="white"> Albo pretorio</font>
                </a>
              </div>
            <?php endif; ?>

            <?php
            $shortcode_output = do_shortcode('[google-translator]');
            if (trim($shortcode_output) !== '[google-translator]') {
              echo '<div class="nav-item me-3">' . $shortcode_output . '</div>';
            }
            ?>

            <div class="nav-item">
              <?php
              if (!is_user_logged_in()) {
                get_template_part("template-parts/header/header-anon");
              } else {
                get_template_part("template-parts/header/header-logged");
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

