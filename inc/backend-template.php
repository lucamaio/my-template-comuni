<?php



// includo i singoli file di template di backend
foreach (glob(get_template_directory() ."/inc/admin/*.php") as $filename)
{
	require $filename;
}

//includo comuni_config.php
require get_template_directory()."/inc/comuni_config.php";


//custom js icone bootstrap -- fontawsome icon picker
function dci_icon_script() {

    if (!dci_is_theme_admin_screen()) { return; }

    wp_register_script( 'dci-icon-script', get_template_directory_uri() . '/inc/admin-js/admin.js', array('jquery'), false, true);
    
    wp_enqueue_script('dci-icon-script');

    $dci_data =   array( 'stylesheet_directory_uri' => get_template_directory_uri() );

    wp_localize_script( 'dci-icon-script', 'dci_data', $dci_data );

}

/** Usa le risorse singole del core: load-styles/load-scripts possono essere negati dal server. */
function psr_individual_core_assets() {
    $GLOBALS['concatenate_scripts'] = false;
}
add_action('login_init', 'psr_individual_core_assets', 5);
add_action('admin_init', 'psr_individual_core_assets', 5);

/** Logo configurabile con lo stemma gia' presente nella configurazione Comune. */
function psr_login_logo($text = '') {
    $options = get_option('dci_options', array());
    $logo = is_array($options) && isset($options['stemma_comune']) && is_string($options['stemma_comune'])
        ? $options['stemma_comune'] : '';
    if ($logo === '') {
        $logo = wp_get_attachment_image_url((int)get_theme_mod('custom_logo'), 'medium');
    }
    if (!$logo) { $logo = get_template_directory_uri() . '/assets/img/logo-point.webp'; }
    return '<img class="psr-login-logo" src="' . esc_url($logo) . '" alt="" width="72" height="72">'
        . '<span class="psr-login-portal">' . esc_html(get_bloginfo('name')) . '</span>'
        . '<span class="psr-login-caption">Area riservata</span>';
}
function smallenvelop_login_message( $message ) {
    // I messaggi core/plugin (recupero, errori, OTP) devono restare intatti.
    return $message . '<p class="psr-login-webmail">Posta elettronica del portale: '
        . '<a href="https://webmailssl.it">accedi alla webmail</a></p>';
}
function my_login_logo_url() {
    return home_url();
}

function dci_enqueue_stylesheets() {
    $relative = '/assets/css/psr-login.css';
    $local = get_template_directory() . $relative;
    if (is_readable($local)) {
        wp_enqueue_style('psr-login', get_template_directory_uri() . $relative,
            array('login'), (string)filemtime($local));
    }
}
add_action( 'admin_enqueue_scripts', 'dci_icon_script' );
add_action( 'login_enqueue_scripts', 'dci_enqueue_stylesheets' );
add_filter( 'login_headerurl', 'my_login_logo_url' );
add_filter( 'login_headertext', 'psr_login_logo' );
add_filter( 'login_message', 'smallenvelop_login_message' );
