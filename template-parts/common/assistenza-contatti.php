<?php
  $numero_verde = dci_get_option('numero_verde', 'assistenza');
  $faq_url = dci_get_template_page_url('page-templates/domande-frequenti.php');
  $assistenza_url = dci_get_template_page_url('page-templates/assistenza.php');
  $prenota_url = dci_get_template_page_url('page-templates/prenota-appuntamento.php');
  $disservizio_url = dci_get_template_page_url('page-templates/segnala-disservizio.php');
  $numero_verde_tel = preg_replace('/[^0-9+]/', '', (string) $numero_verde);
  $has_contatti = !empty($faq_url) || !empty($assistenza_url) || (!empty($numero_verde) && !empty($numero_verde_tel)) || !empty($prenota_url);
  $has_problemi = !empty($disservizio_url);

  if (!$has_contatti && !$has_problemi) {
    return;
  }
?>
<div class="bg-grey-card">
  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-6 offset-lg-3 py-5">
        <div class="cmp-contacts">
          <div class="card w-100">
            <div class="card-body">
              <?php if ($has_contatti) { ?>
                <h2 class="title-medium-2-semi-bold">Contatta il comune</h2>
                <ul class="contact-list p-0">
                  <?php if (!empty($faq_url)) { ?>
                    <li>
                      <a class="list-item" href="<?php echo esc_url($faq_url); ?>">
                        <svg class="icon icon-primary icon-sm" aria-hidden="true"><use href="#it-help-circle"></use></svg>
                        <span>Leggi le domande frequenti</span>
                      </a>
                    </li>
                  <?php } ?>
                  <?php if (!empty($assistenza_url)) { ?>
                    <li>
                      <a class="list-item" href="<?php echo esc_url($assistenza_url); ?>" data-element="contacts">
                        <svg class="icon icon-primary icon-sm" aria-hidden="true"><use href="#it-mail"></use></svg>
                        <span>Richiedi assistenza</span>
                      </a>
                    </li>
                  <?php } ?>
                  <?php if (!empty($numero_verde) && !empty($numero_verde_tel)) { ?>
                    <li>
                      <a class="list-item" href="tel:<?php echo esc_attr($numero_verde_tel); ?>">
                        <svg class="icon icon-primary icon-sm" aria-hidden="true"><use href="#it-hearing"></use></svg>
                        <span>Numero verde <?php echo esc_html($numero_verde); ?></span>
                      </a>
                    </li>
                  <?php } ?>
                  <?php if (!empty($prenota_url)) { ?>
                    <li>
                      <a class="list-item" href="<?php echo esc_url($prenota_url); ?>" data-element="appointment-booking">
                        <svg class="icon icon-primary icon-sm" aria-hidden="true"><use href="#it-calendar"></use></svg>
                        <span>Prenota appuntamento</span>
                      </a>
                    </li>
                  <?php } ?>
                </ul>
              <?php } ?>

              <?php if ($has_problemi) { ?>
                <h2 class="title-medium-2-semi-bold <?php echo $has_contatti ? 'mt-4' : ''; ?>">Problemi in citt&agrave;</h2>
                <ul class="contact-list p-0">
                  <li>
                    <a class="list-item" data-element="report-inefficiency" href="<?php echo esc_url($disservizio_url); ?>">
                      <svg class="icon icon-primary icon-sm" aria-hidden="true"><use href="#it-map-marker-circle"></use></svg>
                      <span>Segnala disservizio</span>
                    </a>
                  </li>
                </ul>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
