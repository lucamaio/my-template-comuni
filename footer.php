<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Design_Comuni_Italia
 */

$external_only_raw = dci_get_option('ck_portalesoloperusoesterno');
$is_external_only = in_array(strtolower((string) $external_only_raw), array('1', 'true', 'yes', 'on'), true);
if ($is_external_only && function_exists('dci_get_external_footer_payload')) {
    $external_footer = dci_get_external_footer_payload();
    if (is_array($external_footer) && !empty($external_footer['html'])) {
        echo $external_footer['html'];
        wp_footer();
        return;
    }
}
?>
<style>
.cookiebar {
  right: auto;
  bottom: 24px;
  left: 50%;
  width: calc(100% - 48px);
  max-width: 1100px;
  margin: 0;
  box-sizing: border-box;
  transform: translateX(-50%);
  align-items: center;
  gap: 28px;
  padding: 24px 28px;
  overflow: hidden;
  background: linear-gradient(135deg, #1f3b57 0%, #2f5f80 100%);
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 18px;
  box-shadow: 0 20px 45px rgba(10, 33, 54, 0.28);
}

.cookiebar::before {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  background:
    radial-gradient(circle at 12% 20%, rgba(255, 255, 255, 0.18), transparent 28%),
    radial-gradient(circle at 85% 90%, rgba(255, 255, 255, 0.10), transparent 26%);
}

.cookiebar.show {
  display: flex;
}

.cookiebar p,
.cookiebar .cookiebar-buttons {
  position: relative;
  z-index: 1;
}

.cookiebar p {
  width: auto;
  max-width: 720px;
  margin: 0;
  color: #ffffff;
  font-size: 0.95rem;
  line-height: 1.55;
}

.cookiebar-title {
  display: block;
  margin-bottom: 4px;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.cookiebar .cookiebar-btn:not(.cookiebar-confirm) {
  color: #ffffff;
  font-weight: 700;
  text-decoration: underline;
  text-decoration-thickness: 2px;
  text-underline-offset: 4px;
}

.cookiebar .cookiebar-buttons {
  display: flex;
  flex-shrink: 0;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 0;
}

.cookiebar .cookiebar-btn {
  transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, color 0.2s ease;
}

.cookiebar .cookiebar-btn:hover {
  transform: translateY(-1px);
  text-decoration: none;
}

.cookiebar .cookiebar-confirm {
  min-width: 112px;
  padding: 13px 18px;
  border: 1px solid rgba(255, 255, 255, 0.55);
  border-radius: 999px;
  letter-spacing: 0.08em;
  background: rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 18px rgba(14, 36, 55, 0.18);
}

.cookiebar .cookiebar-confirm:last-child {
  margin-left: 0;
}

.cookiebar .acceptAllCookie {
  color: #17324d;
  background: #ffffff;
  border-color: #ffffff;
}

.cookiebar .acceptAllCookie:hover {
  color: #17324d;
  box-shadow: 0 12px 24px rgba(255, 255, 255, 0.22);
}

.cookiebar .denyAllCookie:hover {
  background: rgba(255, 255, 255, 0.16);
}

@media (max-width: 1024px) {
  .cookiebar {
    display: none !important;
  }
}

.back-to-top,
.back-to-top.back-to-top-small,
.back-to-top.back-to-top-show,
.back-to-top:hover,
.back-to-top:focus {
  background: #ffffff !important;
  background-color: #ffffff !important;
}

.back-to-top .icon,
.back-to-top .icon use {
  color: #000000 !important;
  fill: #000000 !important;
}
</style>
<section class="cookiebar fade" aria-label="Gestione dei cookies" aria-live="polite">
  <p><strong class="cookiebar-title">Cookies</strong> Si usano i cookies e altre tecniche di tracciamento per migliorare la tua esperienza di navigazione nel nostro sito, per mostrarti contenuti personalizzati e annunci mirati, per analizzare il traffico sul nostro sito, e per capire da dove arrivano i nostri visitatori.
    <a href="/privacy/" class="cookiebar-btn">Info Privacy<span class="visually-hidden"> cookies</span></a>
  </p>
  <div class="cookiebar-buttons">
    <button data-bs-accept="cookiebar" class="cookiebar-btn cookiebar-confirm acceptAllCookie">Accetto<span class="visually-hidden"> i cookies</span></button>
    <button class="cookiebar-btn cookiebar-confirm denyAllCookie">Nega<span class="visually-hidden"> i cookies</span></button>
  </div>
</section>


<div id="backToTop" data-bs-toggle="backtotop" class="back-to-top back-to-top-show" style="overflow-hidden; cursor: pointer; box-shadow: 0 4px 8px rgba(0,0,0,0.2); background-color: white; transition: background-color 0.3s;">
  <svg class="icon" aria-label="Torna a inizio pagina" style="color: #000000; fill: #000000;">
    <use href="#it-collapse" style="color: #000000; fill: #000000;"></use>
  </svg>
</div>
<script>
(function() {
  var headerSelectors = [
    '.it-header-center-wrapper',
    '.it-header-navbar-wrapper',
    '.it-nav-wrapper',
    '.it-header-wrapper'
  ];

  function isVisible(element) {
    return !!(element && element.offsetWidth && element.offsetHeight);
  }

  function isSolidColor(color) {
    if (!color || color === 'transparent') {
      return false;
    }

    var rgbaMatch = color.match(/^rgba?\(([^)]+)\)$/);
    if (!rgbaMatch) {
      return true;
    }

    var parts = rgbaMatch[1].split(',').map(function(part) {
      return part.trim();
    });

    return parts.length < 4 || parseFloat(parts[3]) > 0;
  }

  function getElementBackground(element) {
    if (!element || !isVisible(element)) {
      return '';
    }

    var style = window.getComputedStyle(element);
    var backgroundColor = style.backgroundColor;

    if (isSolidColor(backgroundColor)) {
      return backgroundColor;
    }

    if (style.backgroundImage && style.backgroundImage !== 'none') {
      return style.backgroundImage;
    }

    return '';
  }

  function getHeaderBackgroundFromPoint() {
    var header = document.querySelector('.it-header-wrapper');
    if (!header || typeof document.elementsFromPoint !== 'function') {
      return '';
    }

    var rect = header.getBoundingClientRect();
    if (!rect.width || !rect.height) {
      return '';
    }

    var x = rect.left + (rect.width / 2);
    var y = rect.top + Math.min(rect.height - 1, Math.max(1, rect.height / 2));
    var elements = document.elementsFromPoint(x, y);

    for (var i = 0; i < elements.length; i++) {
      if (header.contains(elements[i]) || elements[i] === header) {
        var background = getElementBackground(elements[i]);
        if (background) {
          return background;
        }
      }
    }

    return '';
  }

  function getHeaderBackground() {
    var sampledBackground = getHeaderBackgroundFromPoint();
    if (sampledBackground) {
      return sampledBackground;
    }

    for (var i = 0; i < headerSelectors.length; i++) {
      var element = document.querySelector(headerSelectors[i]);
      var background = getElementBackground(element);
      if (background) {
        return background;
      }
    }

    return '';
  }

  function syncBackToTopColor() {
    var background = getHeaderBackground();
    if (background) {
      document.documentElement.style.setProperty('--dci-back-to-top-bg', background);
    }
  }

  function scheduleSync() {
    syncBackToTopColor();
    window.requestAnimationFrame(syncBackToTopColor);
    window.setTimeout(syncBackToTopColor, 250);
    window.setTimeout(syncBackToTopColor, 1000);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', scheduleSync);
  } else {
    scheduleSync();
  }

  window.addEventListener('load', scheduleSync);

  if ('MutationObserver' in window) {
    var header = document.querySelector('.it-header-wrapper');
    if (header) {
      new MutationObserver(scheduleSync).observe(header, {
        attributes: true,
        attributeFilter: ['class', 'style'],
        childList: true,
        subtree: true
      });
    }
  }
}());
</script>

<footer class="it-footer" id="footer">
    <div class="it-footer-main">
        <div class="container">
            <div class="row">
                <div class="col-12 footer-items-wrapper logo-wrapper">
                <img class="ue-logo" src="<?php echo esc_url( get_template_directory_uri()); ?>/assets/img/logo-eu-inverted.svg" alt="logo Unione Europea">
                    <div class="it-brand-wrapper">
                        <a href="<?php echo home_url() ?>">
                            <?php get_template_part("template-parts/common/logo");?>
                            <div class="it-brand-text">
                                <h2 class="no_toc"><?php echo dci_get_option("nome_comune");?></h2>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 footer-items-wrapper">
                    <?php
                    $location = "menu-footer-col-1";
                    if ( has_nav_menu( $location ) ) { ?>
                        <h3 class="footer-heading-title">
                            <?php echo wp_get_nav_menu_name($location); ?>
                        </h3>
                        <?php wp_nav_menu(array(
                            "theme_location" => $location,
                            "depth" => 0,
                            "menu_class" => "footer-list",
                            'walker' => new Footer_Menu_Walker()
                        ));
                    }
                    ?>
                </div>
                <div class="col-md-6 footer-items-wrapper">
                    <?php
                    $location = "menu-footer-col-2";
                    if ( has_nav_menu( $location ) ) { 
                        $theme_locations = get_nav_menu_locations();
                        $menu = get_term( $theme_locations[$location], 'nav_menu' );
                        $menu_count = $menu->count;
                    ?>
                        <h3 class="footer-heading-title">
                            <?php echo wp_get_nav_menu_name($location); ?>
                        </h3>
                        <div class="row">
                            <div class="col-md-6">
                            <?php wp_nav_menu(array(
                                "theme_location" => $location,
                                "depth" => 0,
                                "menu_class" => "footer-list",
                                "li_slice" => array(0, ceil($menu_count / 2)),
                                'walker' => new Footer_Menu_Walker()
                            ));?>
                            </div>
                            <div class="col-md-6">
                            <?php wp_nav_menu(array(
                                "theme_location" => $location,
                                "depth" => 0,
                                "menu_class" => "footer-list",
                                "li_slice" => array(ceil($menu_count / 2), $menu_count),
                                'walker' => new Footer_Menu_Walker()
                            ));?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="col-md-3 footer-items-wrapper">
                    <?php
                    $location = "menu-footer-col-3-1";
                    if ( has_nav_menu( $location ) ) { ?>
                        <h3 class="footer-heading-title">
                            <?php echo wp_get_nav_menu_name($location); ?>
                        </h3>
                        <?php wp_nav_menu(array(
                            "theme_location" => $location,
                            "depth" => 0,
                            "menu_class" => "footer-list",
                            "container_class" => "footer-list",
                            'walker' => new Footer_Menu_Walker()
                        ));
                    }
                    ?>
                    <?php
                    $location = "menu-footer-col-3-2";
                    if ( has_nav_menu( $location ) ) { ?>
                        <h3 class="footer-heading-title">
                            <?php echo wp_get_nav_menu_name($location); ?>
                        </h3>
                        <?php wp_nav_menu(array(
                            "theme_location" => $location,
                            "depth" => 0,
                            "menu_class" => "footer-list",
                            'walker' => new Footer_Menu_Walker()
                        ));
                    }
                    ?>
                </div>
                <div class="col-md-9 mt-md-4 footer-items-wrapper">
                    <h3 class="footer-heading-title">Contatti</h3>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="footer-info">
                                <?php echo dci_get_option("nome_comune"); ?>
                                <br /><?php echo dci_get_option("contatti_indirizzo",'footer'); ?>
                                <br /><?php if(dci_get_option("contatti_CF_PIVA",'footer')) echo 'Codice fiscale / P. IVA:' . dci_get_option("contatti_CF_PIVA",'footer'); ?>
                                <br /><br />
                                <?php
                                    $ufficio_id = dci_get_option("contatti_URP",'footer');
                                    $ufficio = get_post($ufficio_id);
                                    if ($ufficio_id) { ?>
                                        <a href="<?php echo get_post_permalink($ufficio_id); ?>" class="list-item" title="Vai alla pagina: URP">
                                            <?php echo $ufficio->post_title ?>
                                        </a>
                                <?php } ?>
                                <?php if(dci_get_option("numero_verde",'footer')) echo '<br />Numero verde: ' . dci_get_option("numero_verde",'footer'); ?>
                                <?php if(dci_get_option("SMS_Whatsapp",'footer')) echo '<br />SMS e WhatsApp: ' . dci_get_option("SMS_Whatsapp",'footer'); ?>
                                <?php
                                    if (dci_get_option("contatti_PEC",'footer')) echo '<br />PEC: '; ?>
                                        <a href="mailto:<?php echo dci_get_option("contatti_PEC",'footer'); ?>" class="list-item" title="PEC <?php echo dci_get_option("nome_comune");?>"><?php echo dci_get_option("contatti_PEC",'footer'); ?></a>
								<?php if(dci_get_option("centralino_unico",'footer')) echo '<br />Centralino unico: ' . dci_get_option("centralino_unico",'footer'); ?>
								<br>
								<?php if(dci_get_option("iban",'footer')) echo '<br />IBAN: ' . dci_get_option("iban",'footer'); ?>
								<?php if(dci_get_option("Codice_Univoco_Fatturazione",'footer')) echo '<br />Codice Univoco Fatturazione: ' . dci_get_option("Codice_Univoco_Fatturazione",'footer'); ?>
								<?php if(dci_get_option("dpo_email",'footer')) echo '<br />Email DPO: ' . dci_get_option("dpo_email",'footer'); ?>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <?php 
                            $location = "menu-footer-info-1";
                            if ( has_nav_menu( $location ) ) { 
                                wp_nav_menu(array(
                                    "theme_location" => $location,
                                    "depth" => 0,
                                    "menu_class" => "footer-list",
                                    'walker' => new Footer_Menu_Walker()
                                ));
                            }
                            ?>
                        </div>
                        <div class="col-md-4">
                            <?php 
                            $location = "menu-footer-info-2";
                            if ( has_nav_menu( $location ) ) { 
                                wp_nav_menu(array(
                                    "theme_location" => $location,
                                    "depth" => 0,
                                    "menu_class" => "footer-list",
                                    'walker' => new Footer_Menu_Walker()
                                ));
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mt-md-4 footer-items-wrapper">
                    <?php
                        $socials = dci_get_option('link_social', 'socials');
                        if (is_array($socials) && count($socials)) {
                    ?>
                        <h3 class="footer-heading-title">Seguici su</h3>
                        <ul class="list-inline text-start social">
                            <?php foreach ($socials as $social) { ?>
                                    <li class="list-inline-item">
                                        <a href="<?php echo $social['url_social'] ?>" target="_blank" class="p-2 text-white">
                                            <svg class="icon icon-sm icon-white align-top"><use xmlns:xlink="http://www.w3.org/1999/xlink" href="#<?php echo $social['icona_social'] ?>"></use>
                                            </svg>
                                            <span class="visually-hidden"><?php echo $social['nome_social']; ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul><!-- /header-social-wrapper -->
                    <?php } ?>
                </div>
            </div>
            <div class="row">
                <div class="col-12 footer-items-wrapper">
			
                           <?php 
				$shortcode_output = do_shortcode('[google-translator]');
				
				if ($shortcode_output !== '[google-translator]') {
				    echo $shortcode_output;
				}
			     ?>
						<?php 
					// Contatore accessi
					echo do_shortcode('[home_counter]');
					?>

				
				
								



					
                    <div class="footer-bottom">

						<?php if(dci_get_option("media_policy",'footer')) { ?>
							<a href="<?php echo dci_get_option("media_policy",'footer'); ?>">Media policy</a>
						<?php } ?>

			    
				          <?php

								//Se il portale gestisce solo la nostra Trasparenza in modo esterno, indirizza all'home del comune.
								$portalesoloperusoesterno = dci_get_option("ck_portalesoloperusoesterno");

							
							if ($portalesoloperusoesterno !== 'true') :
							    $custom_sitemap_url = dci_get_option("sitemap", 'footer');
							    $has_custom_sitemap = strlen(trim($custom_sitemap_url)) > 3;
							    ?>
							    
							    <a href="<?php echo esc_url(dci_get_feed_rss_page_url()); ?>">
							        Feed RSS
							    </a>

							    <a target="_blank" href="<?php echo esc_url($has_custom_sitemap ? $custom_sitemap_url : home_url('/page-sitemap')); ?>">
							        Mappa del sito
							    </a>
							
							    <a href="<?php echo esc_url(site_url('/servizi')); ?>">Servizi</a>
							
							<?php endif; ?>

						
						<a id="area_personale_admin" href="<?php echo get_admin_url(); ?>">Area Riservata</a>
								                      
				



			         <ul class="it-footer-small-prints-list list-inline mb-0 d-flex flex-column flex-md-row" style="float: right;">
	                            <li class="list-inline-item d-flex">
	                                <small>  © <?php echo dci_get_option("nome_comune"); ?>                                        
					 <?php
						$firma_nostra = dci_get_option("firma_nostra");

						if ($firma_nostra === 'false' || $firma_nostra === false) : ?>
						    &nbsp;&nbsp;-&nbsp;&nbsp;Sviluppato da 
						        <a class="text-primary" style="text-decoration:none;" target="_blank" href="https://www.p-service.it/" title="Point Service S.r.l" aria-label="Point Service S.r.l" aria-labelledby="footerCompanyLabel">
						            <span id="footerCompanyLabel" style="color: #fff">
						                &nbsp;Point Service S.r.l
						            </span>
						        </a>  						   
						<?php endif; ?>
				      </small>
	                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
<script>
document.addEventListener("DOMContentLoaded", function () {

  const isTouchDevice = 
    'ontouchstart' in window || 
    navigator.maxTouchPoints > 0;

  const cookieBar = document.querySelector(".cookiebar");
  const acceptButton = document.querySelector(".acceptAllCookie");
  const denyButton   = document.querySelector(".denyAllCookie");

  // 🔥 MOBILE + TABLET → niente banner ma NO tracking
  if (isTouchDevice) {
    localStorage.setItem("cookieChoice", "denied");

    if (cookieBar) cookieBar.remove();

    // 👉 blocca eventuali script di tracking
    window.disableTracking = true;

    return;
  }

  // DESKTOP → comportamento normale
  if (localStorage.getItem("cookieChoice")) {
    if (cookieBar) cookieBar.style.display = "none";
  }

  if (acceptButton) {
    acceptButton.addEventListener("click", function () {
      localStorage.setItem("cookieChoice", "accepted");
      window.disableTracking = false;
      if (cookieBar) cookieBar.style.display = "none";
    });
  }

  if (denyButton) {
    denyButton.addEventListener("click", function () {
      localStorage.setItem("cookieChoice", "denied");
      window.disableTracking = true;
      if (cookieBar) cookieBar.style.display = "none";
    });
  }

});

// 👇 questo lascialo separato (ok così)
document.addEventListener('keydown', function(e) {
  const editable = e.target.isContentEditable || 
                   e.target.tagName.toLowerCase() === 'input' || 
                   e.target.tagName.toLowerCase() === 'textarea';

  if (editable && e.key.toLowerCase() === 'm') {
    e.stopPropagation();
  }
}, true);
</script>


</body>
</html>
