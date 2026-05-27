<?php
/**
 * Design Comuni Italia functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Design_Comuni_Italia
 */

/**
 * Funzionalità Trasversali
 */
require get_template_directory() . '/inc/funzionalita_trasversali.php';

/**
 * Load more posts
 */
require get_template_directory() . '/inc/load_more.php';

/*
 * Vocabolario
 */
require get_template_directory() . '/inc/vocabolario.php';

/**
 * Extend User Taxonomy
 */
require get_template_directory() . '/inc/extend-tax-to-user.php';

/**
 * Implement Plugin Activations Rules
 */
require get_template_directory() . '/inc/theme-dependencies.php';

/**
 * Implement CMB2 Custom Field Manager
 */
if ( ! function_exists ( 'dci_get_tipologia_articoli_options' ) ) {
	require get_template_directory() . '/inc/cmb2.php';
	require get_template_directory() . '/inc/backend-template.php';
}

/**
 * Utils functions
 */
require get_template_directory() . '/inc/utils.php';

/**
 * Breadcrumb class
 */
require get_template_directory() . '/inc/breadcrumb.php';

/**
 * Activation Hooks
 */
require get_template_directory() . '/inc/activation.php';

/**
 * Actions & Hooks
 */
require get_template_directory() . '/inc/actions.php';

/**
 * Gutenberg editor rules
 */
require get_template_directory() . '/inc/gutenberg.php';

/**
 * Welcome page
 */
require get_template_directory() . '/inc/welcome.php';

/**
 * main menu walker
 */
require get_template_directory() . '/walkers/main-menu.php';

/**
 * menu header right walker
 */
require get_template_directory() . '/walkers/menu-header-right.php';

/**
 * footer info walker
 */
require get_template_directory() . '/walkers/footer-info.php';



/**
 *  Caricamento dati Trasparenza
 */
require get_template_directory() . '/inc/activationTrasparenza.php';


/**
 * Filters
 */
require get_template_directory() . '/inc/filters.php';

if ( ! function_exists( 'dci_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function dci_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on Design Comuni Italia, use a find and replace
		 * to change 'design_comuni_italia' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'design_comuni_italia', get_template_directory() . '/languages' );


        load_theme_textdomain( 'easy-appointments', get_template_directory() . '/languages' );

        // Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

        // image size
        if ( function_exists( 'add_image_size' ) ) {
            add_image_size( 'article-simple-thumb', 500, 384 , true);
            add_image_size( 'item-thumb', 280, 280 , true);
            add_image_size( 'item-gallery', 730, 485 , true);
            add_image_size( 'vertical-card', 190, 290 , true);

            add_image_size( 'banner', 600, 250 , false);
        }

	}
endif;
add_action( 'after_setup_theme', 'dci_setup' );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function dci_widgets_init() {
}
add_action( 'widgets_init', 'dci_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function dci_scripts() {

    //wp_deregister_script('jquery');

	//load bootstrap-italia-comuni
    wp_enqueue_style( 'dci-comuni', get_template_directory_uri() . '/assets/css/bootstrap-italia-comuni.min.css');

    wp_enqueue_style( 'dci-font', get_template_directory_uri() . '/assets/css/fonts.css', array('dci-comuni'));
    wp_enqueue_style( 'dci-wp-style', get_template_directory_uri()."/style.css", array('dci-comuni'));
    wp_enqueue_style( 'dci-accessibility-toolbar', get_template_directory_uri() . '/assets/css/accessibility-toolbar.css', array('dci-wp-style'), false );


    wp_enqueue_script( 'dci-modernizr', get_template_directory_uri() . '/assets/js/modernizr.custom.js');

	// print css
    wp_enqueue_style('dci-print-style',get_template_directory_uri() . '/print.css', array(),'20190912','print' );



    // Aggiungi il codice JavaScript inline per passare il percorso del tema
    wp_add_inline_script( 'dci-comuni', '
        const theme_folder = "' . get_template_directory_uri() . '"; // URL del tema
    ', 'after');


	// footer
    //load Bootstrap Italia latest js if exists in node_modules
    if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . '/node_modules/bootstrap-italia/dist/js/bootstrap-italia.bundle.min.js')) {
        wp_enqueue_script( 'dci-boostrap-italia-min-js', get_template_directory_uri() . '/node_modules/bootstrap-italia/dist/js/bootstrap-italia.bundle.min.js', array(), false, true);
    }
    else {
        wp_enqueue_script( 'dci-boostrap-italia-min-js', get_template_directory_uri() . '/assets/js/bootstrap-italia.bundle.min.js', array(), false, true);
    }
	wp_enqueue_script( 'dci-comuni', get_template_directory_uri() . '/assets/js/comuni.js', array(), false, true);
	wp_enqueue_script( 'dci-accessibility-toolbar', get_template_directory_uri() . '/assets/js/accessibility-toolbar.js', array(), false, true);
	wp_script_add_data( 'dci-accessibility-toolbar', 'defer', true );
	wp_add_inline_script( 'dci-comuni', 'window.wpRestApi = "' . get_rest_url() . '"', 'before' );

	wp_enqueue_script( 'dci-jquery-easing', get_template_directory_uri() . '/assets/js/components/jquery-easing/jquery.easing.js', array(), false, true);
	wp_enqueue_script( 'dci-jquery-scrollto', get_template_directory_uri() . '/assets/js/components/jquery.scrollto/jquery.scrollTo.js', array(), false, true);
	wp_enqueue_script( 'dci-jquery-responsive-dom', get_template_directory_uri() . '/assets/js/components/ResponsiveDom/js/jquery.responsive-dom.js', array(), false, true);
	wp_enqueue_script( 'dci-jpushmenu', get_template_directory_uri() . '/assets/js/components/jPushMenu/jpushmenu.js', array(), false, true);
	wp_enqueue_script( 'dci-perfect-scrollbar', get_template_directory_uri() . '/assets/js/components/perfect-scrollbar-master/perfect-scrollbar/js/perfect-scrollbar.jquery.js', array(), false, true);
	wp_enqueue_script( 'dci-vallento', get_template_directory_uri() . '/assets/js/components/vallenato.js-master/vallenato.js', array(), false, true);
	wp_enqueue_script( 'dci-jquery-responsive-tabs', get_template_directory_uri() . '/assets/js/components/responsive-tabs/js/jquery.responsiveTabs.js', array(), false, true);
	wp_enqueue_script( 'dci-fitvids', get_template_directory_uri() . '/assets/js/components/fitvids/jquery.fitvids.js', array(), false, true);
	wp_enqueue_script( 'dci-sticky-kit', get_template_directory_uri() . '/assets/js/components/sticky-kit-master/dist/sticky-kit.js', array(), false, true);
	
	wp_enqueue_script( 'dci-jquery-match-height', get_template_directory_uri() . '/assets/js/components/jquery-match-height/dist/jquery.matchHeight.js', array(), false, true);

	if(is_singular(array("servizio", "struttura", "luogo", "evento", "scheda_progetto", "post", "circolare", "indirizzo")) || is_archive() || is_search() || is_post_type_archive("luogo")) {
		wp_enqueue_script( 'dci-leaflet-js', get_template_directory_uri() . '/assets/js/components/leaflet/leaflet.js', array(), false, true);
    }

	if(is_singular(array("evento","scheda_progetto")) || is_home() || is_archive() ){
		wp_enqueue_script( 'dci-clndr-json2', get_template_directory_uri() . '/assets/js/components/clndr/json2.js', array(), false, false);
		wp_enqueue_script( 'dci-clndr-moment', get_template_directory_uri() . '/assets/js/components/clndr/moment.js', array(), false, false);
		wp_enqueue_script( 'dci-clndr-underscore', get_template_directory_uri() . '/assets/js/components/clndr/underscore.js', array(), false, false);
		wp_enqueue_script( 'dci-clndr-clndr', get_template_directory_uri() . '/assets/js/components/clndr/clndr.js', array(), false, false);
		wp_enqueue_script( 'dci-clndr-it', get_template_directory_uri() . '/assets/js/components/clndr/it.js', array(), false, false);
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'dci_scripts' );

function add_menu_link_class( $atts, $item, $args ) {
	if (property_exists($args, 'link_class')) {
	  $atts['class'] = $args->link_class;
	}
	return $atts;
  }
  add_filter( 'nav_menu_link_attributes', 'add_menu_link_class', 1, 3 );

  function add_menu_list_item_class($classes, $item, $args) {
	if (property_exists($args, 'list_item_class')) {
		$classes[] = $args->list_item_class;
	}
	return $classes;
  }
  add_filter('nav_menu_css_class', 'add_menu_list_item_class', 1, 3);

  function max_nav_items($sorted_menu_items, $args){
    if (property_exists($args, 'li_slice')) {
		$slice = $args->li_slice;
		$items = array();
		foreach($sorted_menu_items as $item){
			if($item->menu_item_parent != 0) continue;
			$items[] = $item;
		}
		$items = array_slice($items, $slice[0], $slice[1]);
		foreach($sorted_menu_items as $key=>$one_item){
			if($one_item->menu_item_parent == 0 && !in_array($one_item,$items)){
				unset($sorted_menu_items[$key]);
			}
		}
	}
    return $sorted_menu_items;
}
add_filter("wp_nav_menu_objects","max_nav_items",10,2);

function console_log ($output, $msg = "log") {
    echo '<script> console.log("'. $msg .'",'. json_encode($output) .')</script>';
};

function get_parent_template () {
	return basename( get_page_template_slug( wp_get_post_parent_id() ) );
}


 // Restituisce il formato e le dimensioni di un allegato
function getFileSizeAndFormat($url) {
    $percorso = parse_url($url);
    $percorso = isset($percorso["path"]) ? substr($percorso["path"], 0, -strlen(pathinfo($url, PATHINFO_BASENAME))) : '';
    $response = wp_remote_head($url);

    if (is_wp_error($response)) {
        return 'Errore nel recupero delle informazioni del file';
    }

    $headers = wp_remote_retrieve_headers($response);
    $content_length = isset($headers['content-length']) ? intval($headers['content-length']) : 0;

    $base = log($content_length, 1024);
    $suffixes = array('', 'Kb', 'Mb', 'Gb', 'Tb');
    $size_formatted = round(pow(1024, $base - floor($base)), 2) . ' ' . $suffixes[floor($base)];

    $info_file = pathinfo($url);
    $file_format = strtoupper(isset($info_file['extension']) ? $info_file['extension'] : '');

    return $file_format . ' ' . $size_formatted;
}



function my_custom_one_time_function() {
    // Evita lavoro pesante sulle richieste frontend.
    if (!is_admin() && !(defined('WP_CLI') && WP_CLI)) {
        return;
    }

    // Controlla se l'opzione è già stata impostata
    if (!get_option('my_custom_function_executed')) {
        
        $args = [
            'post_type' => 'notizia',
            'post_status' => 'publish',
            'posts_per_page' => 200,
            'paged' => 1,
            'fields' => 'ids'
        ];

        do {
            $posts = get_posts($args);

            foreach ($posts as $post_id) {

                $meta_valore = get_post_meta($post_id, '_dci_notizia_data_pubblicazione', true);

                if (empty($meta_valore)) {
                    $data_pubblicazione = strtotime(get_the_date('d-m-Y', $post_id));
                    update_post_meta($post_id, '_dci_notizia_data_pubblicazione', $data_pubblicazione);
                }
            }

            $args['paged']++;

        } while (!empty($posts));

        // ✅ DEVE stare dentro l'if
        update_option('my_custom_function_executed', 1);
    }
}
add_action('admin_init', 'my_custom_one_time_function');



add_action( 'admin_enqueue_scripts', 'dci_evidenzia_categorie_cmb2', 20 );
function dci_evidenzia_categorie_cmb2( $hook ) {

    // Applica solo su pagine nuovo/modifica post tipo 'elemento_trasparenza'
    $tipo_post = $_GET['post_type'] ?? get_post_type( $_GET['post'] ?? 0 );

    if ( ! in_array( $hook, ['post-new.php', 'post.php'] ) || $tipo_post !== 'elemento_trasparenza' ) {
        return;
    }

    // Aggiungo gli stili CSS inline
    wp_add_inline_style(
        'wp-admin',
        "
        .cmb2-categoria-principale {
            font-weight: 700 !important;
            color: #000000 !important;
            text-transform: uppercase !important; /* Maiuscolo */
        }
        .cmb2-sottocategoria {
            color: #343a40 !important;
        }
        .cmb2-radio-list input[type='checkbox']:disabled {
            cursor: not-allowed; /* Cambia il cursore per la checkbox disabilitata */
        }
        .cmb2-radio-list input[type='checkbox'] {
            display: inline-block;
        }
        .cmb2-radio-list input[type='checkbox'].disabled-checkbox {
            display: none;
        }
        "
    );

    // Aggiungo lo script JS inline per disabilitare la checkbox nella categoria principale
    wp_add_inline_script(
        'jquery-core',
        <<<JS
        (function($){
            $(function(){
                $('.cmb2-radio-list, .cmb2-checkbox-list').each(function(){
                    $(this).children('li').each(function(){
                        var label = $(this).children('label').first();
                        var checkbox = $(this).children('input[type="checkbox"]');
                        
                        if(label.length){
                            // Se è la categoria principale
                            if(label.html().indexOf('&nbsp;') === -1){
                                label.addClass('cmb2-categoria-principale');
                                
                                // Disabilito la checkbox e aggiungo la classe per nasconderla
                                checkbox.prop('disabled', true);  // Disabilita la checkbox
                                checkbox.addClass('disabled-checkbox');  // Nasconde la checkbox

                                // Se vuoi rimuovere la checkbox dal DOM, puoi farlo con il seguente codice:
                                // checkbox.closest('li').remove(); 
                            } else {
                                label.addClass('cmb2-sottocategoria');
                            }
                        }
                    });
                });
            });
        })(jQuery);
JS
    );
}


function crea_pagina_sitemap_personalizzata() {
    $slug = 'page-sitemap';
    $pagina = get_page_by_path($slug);

    if (!$pagina) {
        $pagina_id = wp_insert_post(array(
            'post_title'    => 'Mappa del Sito',
            'post_name'     => $slug,
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'page_template' => 'page-templates/page-sitemap.php', // ✅ Con cartella
        ));

        if (!is_wp_error($pagina_id)) {
            error_log('Pagina "Mappa del Sito" creata automaticamente.');
        }
    } else {
        // Se esiste già, aggiorna il template se non è corretto
        if (get_page_template_slug($pagina->ID) !== 'page-templates/page-sitemap.php') {
            wp_update_post(array(
                'ID'            => $pagina->ID,
                'page_template' => 'page-templates/page-sitemap.php',
            ));
        }
    }
}
add_action('after_setup_theme', 'crea_pagina_sitemap_personalizzata');







// ================================
// CONTATORE ACCESSI OTTIMIZZATO
// ================================

function wpc_contatore_homepage() {

    if ( !is_front_page() && !is_home() ) return; 
    if ( is_admin() ) return;

    $today = date('Y-m-d');

    // Se già conteggiato oggi → esci
    if (isset($_COOKIE['wpc_visited_today'])) {
        return;
    }

    // Imposta cookie per 24h
    setcookie('wpc_visited_today', 1, time() + 86400, COOKIEPATH, COOKIE_DOMAIN);

    // Totale
    $count_total = (int) get_option('wpc_home_count', 0);
    $count_total++;
    update_option('wpc_home_count', $count_total);

    // Giornaliero
    $daily_counts = get_option('wpc_home_daily_counts', array());
    $daily_counts[$today] = ($daily_counts[$today] ?? 0) + 1;

    // 🔥 pulizia automatica (3 mesi)
    $cutoff = strtotime('-3 months');
    foreach ($daily_counts as $date => $value) {
        if (strtotime($date) < $cutoff) {
            unset($daily_counts[$date]);
        }
    }

    update_option('wpc_home_daily_counts', $daily_counts);
}

add_action('template_redirect', 'wpc_contatore_homepage');




// ================================
// SHORTCODE VISUALIZZAZIONE CONTATORE
// ================================
function wpc_contatore_homepage_shortcode() {

    $count_total = (int) get_option('wpc_home_count', 0);
    $daily_counts = get_option('wpc_home_daily_counts', array());
    $today = date('Y-m-d');
    $count_today = $daily_counts[$today] ?? 0;

    return "<div class='home-counter'>
        <strong>Totale accessi:</strong> $count_total<br>
        <strong>Accessi oggi:</strong> $count_today
    </div>";
}
add_shortcode('home_counter', 'wpc_contatore_homepage_shortcode'); 





// ================================
// CHAT CONSOLTO
// ================================
add_action('wp_footer', function () {

  // Legge l'URL salvato nella options page CMB2 (option_key = dci_options)
  $opts    = get_option('dci_options', array());
  $raw_url = isset($opts['consolto_referrer_url']) ? trim((string)$opts['consolto_referrer_url']) : '';

  // Estrae host (DNS). Se vuoto/non valido => host vuoto => tasto nascosto.
  $allowed_host = '';
  if ($raw_url !== '') {
    // se inseriscono solo il dominio senza schema, aggiunge https://
    if (!preg_match('~^https?://~i', $raw_url)) {
      $raw_url = 'https://' . $raw_url;
    }
    $allowed_host = (string) parse_url($raw_url, PHP_URL_HOST);
    $allowed_host = preg_replace('/^www\./i', '', $allowed_host); // normalizza
  }

  ?>
  <script>
  (function () {

    // ✅ host dinamico letto da WordPress (può essere stringa vuota)
    var ALLOWED_HOST = <?php echo json_encode($allowed_host); ?>;

    function getBtn() {
      return document.getElementById("btn-consolto");
    }

    function show(btn) {
      btn.style.display = "inline-flex";
      btn.style.alignItems = "center";
      btn.style.gap = "8px";
    }

    function hide(btn) {
      btn.style.display = "none";
    }

    function forceShowConsolto() {
      var tries = 0;
      var maxTries = 20; // ~10s

      var interval = setInterval(function () {
        tries++;

        document.querySelectorAll('iframe[src*="client.consolto.com"]').forEach(function (f) {
          f.style.setProperty("display", "block", "important");
          f.style.setProperty("visibility", "visible", "important");
          f.style.setProperty("opacity", "1", "important");
        });

        if (tries >= maxTries) clearInterval(interval);
      }, 500);
    }

    function isAllowedReferrer() {
      // Se l'admin non ha configurato l'URL => non abilitare mai
      if (!ALLOWED_HOST) return false;

      var ref = document.referrer || "";
      if (!ref) return false;

      try {
        var u = new URL(ref);
        var h = (u.hostname || "").replace(/^www\./i, "");
        return h === ALLOWED_HOST;
      } catch (e) {
        return false;
      }
    }

    function init() {
      var btn = getBtn();
      if (!btn) return;

      if (btn.dataset.bound) return;
      btn.dataset.bound = "1";

      // ✅ Se ok -> mostra, altrimenti nascondi
      if (isAllowedReferrer()) show(btn);
      else hide(btn);

      btn.addEventListener("click", function () {
        forceShowConsolto();
      });
    }

    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
    } else {
      init();
    }

  })();
  </script>
  <?php
}, 999);




//BLOCCA MODIFICA RUOLI A TUTTI TRANNE A NOI

add_action('admin_init', function() {
    // Controlla se siamo nella pagina di User Role Editor
    if (isset($_GET['page']) && $_GET['page'] === 'users-user-role-editor.php') {
        
        // Se l'utente NON è ID 1 → blocca
        if (get_current_user_id() != 1) {
            wp_die('Non hai i permessi per accedere a questa pagina.');
        }
    }
});


add_action('admin_init', function() {

    global $pagenow;

    // Pagine da bloccare
    $pagine_bloccate = [
		'users-user-role-editor.php',
		'themes.php',
        'user-new.php',
		'options-permalink.php',
		'export.php',
		'import.php',       
    ];

    // Se siamo in una di queste pagine
    if (in_array($pagenow, $pagine_bloccate)) {

        // Se NON sei utente ID 1 → blocca
        if (get_current_user_id() != 1) {
            wp_die('Non hai i permessi per accedere a questa pagina.');
        }
    }

});




// FORZA CREAZIONE PAGINA ORGANIGRAMMA UFFICI
add_action('init', function () {

    $slug  = 'uffici-organigramma';
    $title = 'Uffici Organigramma';

    $page = get_page_by_path($slug);

    if (!$page) {

        // Creo la pagina
        wp_insert_post([
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
        ]);

    } else {

        $update = [];

        // Corregge titolo se modificato
        if ($page->post_title !== $title) {
            $update['post_title'] = $title;
        }

        // Corregge slug se modificato
        if ($page->post_name !== $slug) {
            $update['post_name'] = $slug;
        }

        // Assicura che sia pubblicata
        if ($page->post_status !== 'publish') {
            $update['post_status'] = 'publish';
        }

        // Assicura che sia una pagina
        if ($page->post_type !== 'page') {
            $update['post_type'] = 'page';
        }

        if (!empty($update)) {
            $update['ID'] = $page->ID;
            wp_update_post($update);
        }
    }

});






add_action('init', function() {

    global $wp_post_types;

    $map = [
        'luogo'   => 'luoghi',
        'evento'  => 'eventi',
        'notizia' => 'notizie'
    ];

    foreach ($map as $type => $rest) {

        if (isset($wp_post_types[$type])) {

            $wp_post_types[$type]->show_in_rest = true;
            $wp_post_types[$type]->rest_base = $rest;
            $wp_post_types[$type]->rest_controller_class = 'WP_REST_Posts_Controller';
        }
    }

});




/**
 * Restituisce i servizi attivi per integrazioni esterne (es. APP Comuni).
 */
function dci_is_servizio_attivo($post_id) {
    $prefix = '_dci_servizio_';
    $stato = get_post_meta($post_id, $prefix . 'stato', true);

    if ($stato === 'false') {
        return false;
    }

    $today = current_time('Y-m-d');

    $data_inizio_raw = trim((string) get_post_meta($post_id, $prefix . 'data_inizio_servizio', true));
    if ($data_inizio_raw !== '') {
        $data_inizio = DateTime::createFromFormat('d/m/Y', $data_inizio_raw);
        if ($data_inizio instanceof DateTime && $data_inizio->format('Y-m-d') > $today) {
            return false;
        }
    }

    $data_fine_raw = trim((string) get_post_meta($post_id, $prefix . 'data_fine_servizio', true));
    if ($data_fine_raw !== '') {
        $data_fine = DateTime::createFromFormat('d/m/Y', $data_fine_raw);
        if ($data_fine instanceof DateTime && $data_fine->format('Y-m-d') < $today) {
            return false;
        }
    }

    return true;
}

function dci_get_servizi_attivi_rest_payload(WP_REST_Request $request) {
    $limit = (int) $request->get_param('per_page');
    if ($limit <= 0 || $limit > 200) {
        $limit = 100;
    }

    $servizi = get_posts([
        'post_type' => 'servizio',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    $response = [];

    foreach ($servizi as $servizio) {
        if (!dci_is_servizio_attivo($servizio->ID)) {
            continue;
        }

        $response[] = [
            'id' => $servizio->ID,
            'titolo' => get_the_title($servizio->ID),
            'slug' => $servizio->post_name,
            'link' => get_permalink($servizio->ID),
            'descrizione_breve' => (string) get_post_meta($servizio->ID, '_dci_servizio_descrizione_breve', true),
            'data_inizio_servizio' => (string) get_post_meta($servizio->ID, '_dci_servizio_data_inizio_servizio', true),
            'data_fine_servizio' => (string) get_post_meta($servizio->ID, '_dci_servizio_data_fine_servizio', true),
            'richiedi_online_attivo' => !empty(get_post_meta($servizio->ID, '_dci_servizio_canale_digitale_link', true)),
            'accedi_al_servizio_label' => (string) get_post_meta($servizio->ID, '_dci_servizio_canale_digitale_label', true),
            'accedi_al_servizio_link' => (string) get_post_meta($servizio->ID, '_dci_servizio_canale_digitale_link', true),
        ];
    }

    return rest_ensure_response($response);
}
add_action('rest_api_init', function () {

    
    register_rest_route('wp/v2', '/servizi-attivi', [
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'dci_get_servizi_attivi_rest_payload',
        'permission_callback' => '__return_true',
    ]);

/*
    =====================================
    EVENTO (OTTIMIZZATO)
    =====================================
    */
    register_rest_field('evento', 'data_inizio', [
        'get_callback' => function ($post) {
            $payload = dci_get_evento_rest_payload($post['id']);
            return $payload['data_inizio'];
        }
    ]);

    register_rest_field('evento', 'data_fine', [
        'get_callback' => function ($post) {
            $payload = dci_get_evento_rest_payload($post['id']);
            return $payload['data_fine'];
        }
    ]);

    register_rest_field('evento', 'descrizione_breve', [
        'get_callback' => function ($post) {
            $payload = dci_get_evento_rest_payload($post['id']);
            return $payload['descrizione_breve'];
        }
    ]);

    register_rest_field('evento', 'descrizione_completa', [
        'get_callback' => function ($post) {
            $payload = dci_get_evento_rest_payload($post['id']);
            return $payload['descrizione_completa'];
        }
    ]);

    register_rest_field('evento', 'immagine', [
        'get_callback' => function ($post) {
            $payload = dci_get_evento_rest_payload($post['id']);
            return $payload['immagine'];
        }
    ]);

    /*
    =====================================
    NOTIZIA (OTTIMIZZATO)
    =====================================
    */
    register_rest_field('notizia', 'descrizione_breve', [
        'get_callback' => function ($post) {
            $payload = dci_get_notizia_rest_payload($post['id']);
            return $payload['descrizione_breve'];
        }
    ]);


    register_rest_field('notizia', 'descrizione_completa', [
        'get_callback' => function ($post) {
            $payload = dci_get_notizia_rest_payload($post['id']);
            return $payload['descrizione_completa'];
        }
    ]);

    register_rest_field('notizia', 'allegati', [
        'get_callback' => function ($post) {
            $payload = dci_get_notizia_rest_payload($post['id']);
            return $payload['allegati'];
        }
    ]);


	register_rest_field('notizia', 'immagine', [
    'get_callback' => function ($post) {
        $payload = dci_get_notizia_rest_payload($post['id']);
        return $payload['immagine'];
    }
	]);
	
    /*
    =====================================
    LUOGO (CACHE)
    =====================================
    */

    register_rest_field('luogo', 'descrizione_completa', [
        'get_callback' => function ($post) {
            $payload = get_post_meta($post['id']);
            return $payload['_dci_luogo_descrizione_estesa'][0] ?? '';
        }
    ]);

    register_rest_field('luogo', 'meta_luogo', [
        'get_callback' => function ($post) {

            $cache_key = 'luogo_meta_v2_' . $post['id'];

            $cached = get_transient($cache_key);
            if ($cached !== false) return $cached;

            $prefix = '_dci_luogo_';
            $all_meta = get_post_meta($post['id']);

            $gps = isset($all_meta[$prefix . 'posizione_gps'][0])
                ? maybe_unserialize($all_meta[$prefix . 'posizione_gps'][0])
                : [];

            $tipi = get_the_terms($post['id'], 'tipi_luogo');
            $tipi_array = [];

            if (!empty($tipi) && !is_wp_error($tipi)) {
                foreach ($tipi as $t) {
                    $tipi_array[] = [
                        'name' => $t->name,
                        'link' => get_term_link($t)
                    ];
                }
            }

            $data = [
                'immagine' => $all_meta[$prefix . 'immagine'][0] ?? '',
                'descrizione' => $all_meta[$prefix . 'descrizione_breve'][0] ?? '',
                'descrizione_completa' => $all_meta[$prefix . 'descrizione_estesa'][0] ?? '',
                'lat' => $gps['lat'] ?? '',
                'lng' => $gps['lng'] ?? '',
                'indirizzo' => $all_meta[$prefix . 'indirizzo'][0] ?? '',
                'quartiere' => $all_meta[$prefix . 'quartiere'][0] ?? '',
                'circoscrizione' => $all_meta[$prefix . 'circoscrizione'][0] ?? '',
                'tipi_luogo' => $tipi_array
            ];

            set_transient($cache_key, $data, HOUR_IN_SECONDS);

            return $data;
        }
    ]);

    /*
    =====================================
    FOOTER (CACHE)
    =====================================
    */
    register_rest_route('comune/v1', '/footer', [
        'methods' => 'GET',
        'callback' => function () {

            $cached = get_transient('api_footer');
            if ($cached !== false) return $cached;

            $data = [
                "nome" => dci_get_option("nome_comune"),
                "indirizzo" => dci_get_option("contatti_indirizzo", 'footer'),
                "cf_piva" => dci_get_option("contatti_CF_PIVA", 'footer'),
                "telefono" => dci_get_option("centralino_unico", 'footer'),
                "numero_verde" => dci_get_option("numero_verde", 'footer'),
                "whatsapp" => dci_get_option("SMS_Whatsapp", 'footer'),
                "pec" => dci_get_option("contatti_PEC", 'footer'),
                "iban" => dci_get_option("iban", 'footer'),
                "codice_fatturazione" => dci_get_option("Codice_Univoco_Fatturazione", 'footer'),
                "email_dpo" => dci_get_option("dpo_email", 'footer'),
            ];

            $socials = dci_get_option('link_social', 'socials');
            $data["social"] = [];

            if (is_array($socials)) {
                foreach ($socials as $s) {
                    $data["social"][] = [
                        "nome" => $s["nome_social"],
                        "url" => $s["url_social"]
                    ];
                }
            }

            set_transient('api_footer', $data, HOUR_IN_SECONDS);

            return $data;
        }
    ]);

});

add_filter('rest_notizia_query', function ($args, $request) {
    $id = absint($request->get_param('id'));
    if ($id > 0) {
        $args['p'] = $id;
    }

    return $args;
}, 10, 2);

if (!function_exists('dci_get_notizia_rest_payload')) {
    /**
     * Payload REST notizia con cache breve per alleggerire richieste ripetute.
     *
     * @param int $post_id
     * @return array
     */
    function dci_get_notizia_rest_payload($post_id) {
        $post_id = absint($post_id);
        if ($post_id <= 0) {
            return array(
                'descrizione_breve' => '',
                'data_scadenza' => '',
                'descrizione_completa' => '',
                'allegati' => array(),
            );
        }



        $cache_key = 'dci_notizia_rest_' . $post_id;
        $cached_payload = get_transient($cache_key);
        if (is_array($cached_payload)) {
            return $cached_payload;
        }



$immagine = null;

// 1️⃣ FEATURED IMAGE
$thumbnail_id = get_post_thumbnail_id($post_id);

if ($thumbnail_id) {
    $img_url = wp_get_attachment_image_url($thumbnail_id, 'full');

    if ($img_url) {
        $immagine = [
            'id' => (int) $thumbnail_id,
            'url' => $img_url,
            'alt' => get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true) ?: '',
            'title' => get_the_title($thumbnail_id) ?: '',
        ];
    }
}

// 2️⃣ FALLBACK CMB2
if (!$immagine) {
    $meta_img = get_post_meta($post_id, '_dci_notizia_immagine', true);

    if (!empty($meta_img)) {

        if (is_numeric($meta_img)) {
            $img_url = wp_get_attachment_image_url($meta_img, 'full');

            if ($img_url) {
                $immagine = [
                    'id' => (int) $meta_img,
                    'url' => $img_url,
                    'alt' => get_post_meta($meta_img, '_wp_attachment_image_alt', true) ?: '',
                    'title' => get_the_title($meta_img) ?: '',
                ];
            }
        }
        elseif (is_array($meta_img) && isset($meta_img['url'])) {
            $immagine = [
                'id' => 0,
                'url' => $meta_img['url'],
                'alt' => '',
                'title' => '',
            ];
        }
        elseif (is_string($meta_img)) {
            $immagine = [
                'id' => 0,
                'url' => $meta_img,
                'alt' => '',
                'title' => '',
            ];
        }
    }
}





		

        $meta = get_post_meta($post_id);
        $full_text = $meta['_dci_notizia_testo_completo'][0] ?? '';
        if ($full_text === '') {
            $post_obj = get_post($post_id);
            $full_text = $post_obj ? (string) $post_obj->post_content : '';
        }

        $raw_attachments = $meta['_dci_notizia_allegati'][0] ?? [];
        $attachments = maybe_unserialize($raw_attachments);
        $allegati = array();
        if (is_array($attachments)) {
            foreach ($attachments as $file_id => $file_data) {
                $attachment_id = 0;
                if (is_array($file_data) && isset($file_data['id'])) {
                    $attachment_id = absint($file_data['id']);
                } else {
                    $attachment_id = absint($file_id);
                }

                $file_url = $attachment_id > 0 ? wp_get_attachment_url($attachment_id) : '';
                if (empty($file_url) && is_string($file_data)) {
                    $file_url = $file_data;
                }
                if (empty($file_url)) {
                    continue;
                }

                $file_name = $attachment_id > 0 ? get_the_title($attachment_id) : basename((string) $file_url);
                if (empty($file_name)) {
                    $file_name = basename((string) $file_url);
                }

                $allegati[] = array(
                    'id' => $attachment_id,
                    'nome' => $file_name,
                    'url_download' => $file_url,
                    'mime_type' => $attachment_id > 0 ? get_post_mime_type($attachment_id) : '',
                );
            }
        }

        $payload = array(
            'descrizione_breve' => $meta['_dci_notizia_descrizione_breve'][0] ?? '',
            'data_scadenza' => $meta['_dci_notizia_data_scadenza'][0] ?? '',
            'descrizione_completa' => $full_text,
            'allegati' => $allegati,
			'immagine' => $immagine,
        );

        set_transient($cache_key, $payload, 5 * MINUTE_IN_SECONDS);
        return $payload;
    }
}

if (!function_exists('dci_get_evento_rest_payload')) {
    /**
     * Payload REST evento con cache breve per ridurre latenza sotto carico.
     *
     * @param int $post_id
     * @return array
     */
    function dci_get_evento_rest_payload($post_id) {
        $post_id = absint($post_id);
        if ($post_id <= 0) {
            return array(
                'data_inizio' => '',
                'data_fine' => '',
                'descrizione_breve' => '',
                'descrizione_completa' => '',
                'immagine' => null,
            );
        }

        $cache_key = 'dci_evento_rest_' . $post_id;
        $cached_payload = get_transient($cache_key);
        if (is_array($cached_payload)) {
            return $cached_payload;
        }

        $meta = get_post_meta($post_id);
        $meta_image = $meta['_dci_evento_immagine'][0] ?? '';

        $thumbnail_id = 0;
        if (!empty($meta_image) && is_string($meta_image)) {
            $thumbnail_id = attachment_url_to_postid($meta_image);
        }
        if (!$thumbnail_id) {
            $thumbnail_id = get_post_thumbnail_id($post_id);
        }

        $immagine = null;
        if ($thumbnail_id) {
            $img_url = wp_get_attachment_image_url($thumbnail_id, 'full');
            if ($img_url) {
                $immagine = array(
                    'id' => (int) $thumbnail_id,
                    'url' => $img_url,
                    'alt' => get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true) ?: '',
                    'title' => get_the_title($thumbnail_id) ?: '',
                );
            }
        } elseif (!empty($meta_image) && is_string($meta_image)) {
            $immagine = array(
                'id' => 0,
                'url' => $meta_image,
                'alt' => '',
                'title' => '',
            );
        }

        $payload = array(
            'data_inizio' => $meta['_dci_evento_data_orario_inizio'][0] ?? '',
            'data_fine' => $meta['_dci_evento_data_orario_fine'][0] ?? '',
            'descrizione_breve' => $meta['_dci_evento_descrizione_breve'][0] ?? '',
            'descrizione_completa' => $meta['_dci_evento_descrizione_completa'][0] ?? '',
            'immagine' => $immagine,
        );

        set_transient($cache_key, $payload, 5 * MINUTE_IN_SECONDS);
        return $payload;
    }
}

add_action('save_post_notizia', function ($post_id) {
    delete_transient('dci_notizia_rest_' . absint($post_id));
});

add_action('save_post_evento', function ($post_id) {
    delete_transient('dci_evento_rest_' . absint($post_id));
});

add_action('save_post_luogo', function($post_id) {
    delete_transient('luogo_meta_' . $post_id);
});

add_action('update_option_dci_options', function() {
    delete_transient('api_footer');
});

/**
 * Allinea la query principale della tassonomia trasparenza ai filtri del template
 * per evitare mismatch di paginazione (es. ultima pagina in errore/404).
 */
add_action('pre_get_posts', function (WP_Query $query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if (!$query->is_tax('tipi_cat_amm_trasp')) {
        return;
    }

    $max_posts = isset($_GET['max_posts']) ? absint($_GET['max_posts']) : 10;
    if ($max_posts <= 0) {
        $max_posts = 10;
    }

    $search = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : '';
    $order_type = isset($_GET['order_type']) ? sanitize_key($_GET['order_type']) : 'data_desc';

    $query->set('post_type', 'elemento_trasparenza');
    $query->set('posts_per_page', $max_posts);

    $term_slug = (string) $query->get('tipi_cat_amm_trasp');
    if ($term_slug !== '') {
        $term = get_term_by('slug', $term_slug, 'tipi_cat_amm_trasp');
        if ($term && !is_wp_error($term)) {
            $query->set('tax_query', array(
                array(
                    'taxonomy' => 'tipi_cat_amm_trasp',
                    'field' => 'term_id',
                    'terms' => array((int) $term->term_id),
                    'include_children' => false,
                ),
            ));
        }
    }

    if ($search !== '') {
        $query->set('s', $search);
    }

    if ($order_type === 'alfabetico_asc' || $order_type === 'alfabetico_desc') {
        $query->set('orderby', 'title');
        $query->set('order', $order_type === 'alfabetico_desc' ? 'DESC' : 'ASC');
    } else {
        $query->set('orderby', 'date');
        $query->set('order', $order_type === 'data_asc' ? 'ASC' : 'DESC');
    }
});

// ===============================
// Privacy via Api
// ===============================


add_action('rest_api_init', function () {

    register_rest_route('comune/v1', '/privacy', [
        'methods' => 'GET',
        'callback' => function () {

            // 🔥 CACHE
            $cached = get_transient('api_privacy');
            if ($cached !== false) return $cached;

            $page = get_page_by_path('privacy-policy');

            if (!$page) {
                return [
                    "titolo" => "Privacy Policy",
                    "contenuto" => ""
                ];
            }

            $data = [
                "titolo" => get_the_title($page->ID),
                "contenuto" => apply_filters('the_content', $page->post_content)
            ];

            set_transient('api_privacy', $data, HOUR_IN_SECONDS);

            return $data;
        }
    ]);

});




// ===============================
// MODALITÀ APP (no tracking Apple)
// ===============================
add_action('init', function () {

    $isApp = isset($_GET['app']);

    // fallback SOLO Android
    if (!$isApp && !empty($_SERVER['HTTP_USER_AGENT'])) {
        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strpos($ua, 'wv') !== false) {
            $isApp = true;
        }
    }

    if (!$isApp) return;

    if (!defined('DONOTCACHEPAGE')) {
        define('DONOTCACHEPAGE', true);
    }

    add_filter('script_loader_src', function ($src) {

        if (empty($src)) return $src;

        $blocked = [
            'googletagmanager',
            'google-analytics',
            'gtag/js',
            'doubleclick',
            'facebook'
        ];

        foreach ($blocked as $b) {
            if (stripos($src, $b) !== false) {
                return '';
            }
        }

        return $src;
    });

});
