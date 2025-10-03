<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Design_Comuni_Italia
 */
$theme_locations = get_nav_menu_locations();
$current_group = dci_get_current_group();
?>
<!doctype html>
<html lang="it">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<?php get_template_part("template-parts/common/svg"); ?>
<?php get_template_part("template-parts/common/sprites"); ?>
<?php get_template_part("template-parts/common/skiplink"); ?>

<header
    class="it-header-wrapper"
    data-bs-target="#header-nav-wrapper"
    style=""
>
    <?php get_template_part("template-parts/header/slimheader"); ?> 

    <div class="it-nav-wrapper">
    <div class="it-header-center-wrapper">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="it-header-center-content-wrapper">
              <div class="it-brand-wrapper">
                <a 
                href="<?php echo home_url(); ?>" 
                <?php if(!is_front_page()) echo 'title="Vai alla Homepage"'; ?>>
                    <div class="it-brand-text d-flex align-items-center">
                      <?php get_template_part("template-parts/common/logo"); ?>
                      <div>
                        <div class="it-brand-title"><?php echo dci_get_option("nome_comune"); ?></div>
                       <div class="it-brand-tagline d-none d-md-block">
                          <?php echo dci_get_option("motto_comune"); ?>
                        </div>
                      </div>
                    </div>
                </a>
              </div>
              <div class="it-right-zone">
              <?php
                    $show_socials = dci_get_option( "show_socials", "socials" );
                    if($show_socials == "true") : 
                    $socials = dci_get_option('link_social', 'socials');
                    ?>
                    <div class="it-socials d-none d-lg-flex">
                        <span>Seguici su:</span>
                        <ul>
                            <?php foreach ($socials as $social) { ?>
                              <li>
                                <a href="<?php echo $social['url_social'] ?>" target="_blank">
                                    <svg class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" href="#<?php echo $social['icona_social'] ?>"></use>
                                  </svg>
                                  <span class="visually-hidden"><?php echo $social['nome_social']; ?></span>
                                </a>
                            </li>
                            <?php } ?>                            
                        </ul><!-- /header-social-wrapper -->
                    </div><!-- /it-socials -->
                    <?php endif ?>
                <div class="it-search-wrapper">
                  <span class="d-none d-md-block">Cerca</span>
                  <button class="search-link rounded-icon" type="button" data-bs-toggle="modal" data-bs-target="#search-modal" aria-label="Cerca nel sito"  id="search-home">
                      <svg class="icon">
                        <use href="#it-search"></use>
                      </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="it-header-navbar-wrapper" id="header-nav-wrapper">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div
              class="navbar navbar-expand-lg has-megamenu"
            >
              <button
                class="custom-navbar-toggler"
                type="button"
                aria-controls="nav4"
                aria-expanded="false"
                aria-label="Mostra/Nascondi la navigazione"
                data-bs-target="#nav4"
                data-bs-toggle="navbarcollapsible"
              >
                <svg class="icon">
                  <use href="#it-burger"></use>
                </svg>
              </button>
              <div class="navbar-collapsable" id="nav4">
                <div class="overlay" style="display: none"></div>
                <div class="close-div">
                  <button class="btn close-menu" type="button">
                    <span class="visually-hidden">Nascondi la navigazione</span>
                    <svg class="icon">
                      <use href="#it-close-big"></use>
                    </svg>
                  </button>
                </div>

		      
	                <div class="menu-wrapper">

   <?php if (wp_is_mobile()) : ?>
	  <div class="mobile-login-language-wrapper mt-3 mb-2 px-3">
	    <div class="ente-name mb-2 fw-semibold">
	      <?php echo esc_html(dci_get_option("nome_comune")); ?>
	    </div>
	
	    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
	     <div class="language-selector">
	        <?php
	          $shortcode_output = do_shortcode('[google-translator]');
	          if (trim($shortcode_output) !== '[google-translator]') {
	              echo $shortcode_output;
	          }
	        ?>
	      </div>	
	      <div class="login-area ms-auto">
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
	
	  <ul class="navbar-nav mobile-extra-menu p-3 rounded">

		    <?php if (dci_get_option('url_sito_regione') && dci_get_option('nome_regione')) : ?>
		      <li class="nav-item mb-2">
		        <a class="nav-link text-white"
		           href="<?php echo esc_url(dci_get_option('url_sito_regione')); ?>"
		           target="_blank"
		           rel="noopener"
		           aria-label="Vai al portale <?php echo esc_attr(dci_get_option('nome_regione')); ?>">
		         <?php echo esc_html(dci_get_option('nome_regione')); ?>
		        </a>
		      </li>
		    <?php endif; ?>

				
		    <?php if (dci_get_option('link_ammtrasparente')) : ?>
		      <li class="nav-item mb-2">
		        <a class="nav-link text-white"
		           href="<?php echo esc_url(dci_get_option('link_ammtrasparente')); ?>"
		           target="_blank"
		           rel="noopener"
		           aria-label="Amministrazione trasparente">
		        Amministrazione trasparente
		        </a>
		      </li>
		    <?php endif; ?>
		
		    <?php if (dci_get_option('link_albopretorio')) : ?>
		      <li class="nav-item mb-2">
		        <a class="nav-link text-white"
		           href="<?php echo esc_url(dci_get_option('link_albopretorio')); ?>"
		           target="_blank"
		           rel="noopener"
		           aria-label="Albo pretorio">
		         Albo pretorio
		        </a>
		      </li>
		    <?php endif; ?>
		

		  </ul>
		<?php endif; ?>

                <nav aria-label="Principale">
                  <?php
                      $location = "menu-header-main";
                      if ( has_nav_menu( $location ) ) {
                          wp_nav_menu(array(
                            "theme_location" => $location, 
                            "depth" => 0,  
                            "menu_class" => "navbar-nav", 
                            'items_wrap' => '<ul class="%2$s" id="%1$s" data-element="main-navigation">%3$s</ul>',
                            "container" => "",
                            'list_item_class'  => 'nav-item',
                            'link_class'   => 'nav-link',
                            'current_group' => $current_group,
                            'walker' => new Main_Menu_Walker()
                          ));
                      }
                    ?>
                </nav>
                <nav aria-label="Secondaria">
                  <?php
                    $location = "menu-header-right";
                    if ( has_nav_menu( $location ) ) {
                        wp_nav_menu(array(
                          "theme_location" => $location, 
                          "depth" => 0,  
                          "menu_class" => "navbar-nav navbar-secondary", 
                          "container" => "",
                          'list_item_class'  => 'nav-item',
                          'link_class'   => 'nav-link',
                          'walker' => new Menu_Header_Right_Walker()
                        ));
                    }
                    ?>
                </nav>


	
			
                  <?php
                    $show_socials = dci_get_option( "show_socials", "socials" );
                    if($show_socials == "true") : 
                    $socials = dci_get_option('link_social', 'socials');
                    ?>
                    <div class="it-socials">
                        <span>Seguici su:</span>
                        <ul>
                            <?php foreach ($socials as $social) { ?>
                              <li>
                                <a href="<?php echo $social['url_social'] ?>" target="_blank">
                                    <svg class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" href="#<?php echo $social['icona_social'] ?>"></use>
                                  </svg>
                                  <span class="visually-hidden"><?php echo $social['nome_social']; ?></span>
                                </a>
                            </li>
                            <?php } ?>                            
                        </ul><!-- /header-social-wrapper -->
                    </div><!-- /it-socials -->
                    <?php endif ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>

<?php get_template_part("template-parts/common/search-modal"); ?>
<?php
if(!is_user_logged_in())
    get_template_part("template-parts/common/access-modal");
?>
	
<style>
@media (max-width: 767.98px) {
  /* Riduce margine e padding tra voci menu mobile */
  .navbar-nav > li.nav-item {
    margin-bottom: 4px !important;
  }

  .navbar-nav > li.nav-item > a.nav-link {
    padding-top: 6px !important;
    padding-bottom: 6px !important;
    line-height: 1.2 !important;
  }

  /* Extra menu mobile */
  .mobile-extra-menu .nav-item {
    margin-bottom: 4px !important;
  }

  .mobile-extra-menu .nav-link {
    padding-top: 6px !important;
    padding-bottom: 6px !important;
    line-height: 1.2 !important;
  }

  /* Blocchi login + lingua + nome comune */
  .mobile-login-language-wrapper {
    background-color: transparent !important;
  }

  .mobile-login-language-wrapper .ente-name {
    font-size: 1.1rem;
    font-weight: 600;
  }

  .mobile-login-language-wrapper .language-selector,
  .mobile-login-language-wrapper .login-area {
    display: flex;
    align-items: center;
  }

  .mobile-login-language-wrapper .language-selector select {
    max-width: 100px;
  }
}
</style>
