<?php



// includo i singoli file di template di backend
foreach (glob(get_template_directory() ."/inc/admin/*.php") as $filename)
{
    if (
    	!str_contains($filename, 'richiesta_assistenza')
    ) {
	require $filename;
	}
}

//includo comuni_config.php
require get_template_directory()."/inc/comuni_config.php";


//custom js icone bootstrap -- fontawsome icon picker
function dci_icon_script() {

    wp_register_script( 'dci-icon-script', get_template_directory_uri() . '/inc/admin-js/admin.js');
    
    wp_enqueue_script('dci-icon-script');

    $dci_data =   array( 'stylesheet_directory_uri' => get_template_directory_uri() );

    wp_localize_script( 'dci-icon-script', 'dci_data', $dci_data );

}

function psr_login_logo() { 
	
    echo '<style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url('.get_stylesheet_directory_uri().'/assets/img/logo-point.webp);
        }
        </style>
    ';
}
function smallenvelop_login_message( $message ) {

    return '<div class="bg-primary p-3 affix-top text-white">
    <p><strong>Per accedere alla webmail clicca <a href="https://webmailssl.it" class="text-white">qui</a></strong></p>
  </div>';

    if ( empty($message) ){
        return '<p><strong>Per accedere alla webmail clicca <a href="https://webmailssl.it">qui</a></strong></p>';
    } else {
        return $message;
    }
}
function my_login_logo_url() {
    return home_url();
}

function dci_enqueue_stylesheets() {
    wp_enqueue_style( 'dci-boostrap-italia-min', get_template_directory_uri() . '/assets/css/bootstrap-italia.min.css', false, '1.0.0' );
}
add_action( 'admin_enqueue_scripts', 'dci_icon_script' );
add_action( 'login_enqueue_scripts', 'dci_enqueue_stylesheets' );
add_filter( 'login_headerurl', 'my_login_logo_url' );
add_action( 'login_enqueue_scripts', 'psr_login_logo' );
add_filter( 'login_message', 'smallenvelop_login_message' );
