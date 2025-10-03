<?php
/**
 * Custom Post Type: incarichi_dip
 * (Incarichi conferiti ai dipendenti)
 */

/* -------------------------------------------------
   Registrazione CPT con controllo permesso
--------------------------------------------------*/
add_action( 'init', 'dci_register_post_type_icad' );
function dci_register_post_type_icad() {



	
    $labels = array(
        'name'           => _x( 'Incarichi conferiti e autorizzati', 'Post Type General Name', 'design_comuni_italia' ),
        'singular_name'  => _x( 'Incarico conferito', 'Post Type Singular Name', 'design_comuni_italia' ),
        'add_new'        => _x( 'Aggiungi un Incarico conferito', 'Post Type', 'design_comuni_italia' ),
        'add_new_item'   => __( 'Aggiungi un nuovo Incarico conferito', 'design_comuni_italia' ),
        'edit_item'      => __( 'Modifica Incarico conferito', 'design_comuni_italia' ),
        'featured_image' => __( 'Immagine di riferimento incarico', 'design_comuni_italia' ),
    );

    $args = array(
        'label'           => __( 'Incarico conferito', 'design_comuni_italia' ),
        'labels'          => $labels,
        'supports'        => array( 'title', 'author' ),
        'hierarchical'    => true,
        'public'          => true,
        'show_in_menu'    => false, // non mostra menu principale
        'menu_icon'       => 'dashicons-media-interactive',
        'has_archive'     => false, 
        'rewrite'         => array(
            'with_front' => false,
            'pages' => true,
        ),
        'map_meta_cap'    => true,
        'capability_type' => 'incarico_dip',
        'capabilities'    => array(
            'edit_post'             => 'edit_incarico_dip',
            'read_post'             => 'read_incarico_dip',
            'delete_post'           => 'delete_incarico_dip',
            'edit_posts'            => 'edit_incarichi_dip',
            'edit_others_posts'     => 'edit_others_incarichi_dip',
            'publish_posts'         => 'publish_incarichi_dip',
            'read_private_posts'    => 'read_private_incarichi_dip',
            'delete_posts'          => 'delete_incarichi_dip',
            'delete_private_posts'  => 'delete_private_incarichi_dip',
            'delete_published_posts'=> 'delete_published_incarichi_dip',
            'delete_others_posts'   => 'delete_others_incarichi_dip',
            'edit_private_posts'    => 'edit_private_incarichi_dip',
            'edit_published_posts'  => 'edit_published_incarichi_dip',
            'create_posts'          => 'create_incarichi_dip',
        ),
        'description'     => __( 'Incarichi conferiti ai dipendenti del Comune.', 'design_comuni_italia' ),
    );

    register_post_type( 'incarichi_dip', $args );

    // Rimuove l'editor standard
    remove_post_type_support( 'incarichi_dip', 'editor' );
}

/* -------------------------------------------------
   Sottomenu sotto elemento_trasparenza
--------------------------------------------------*/
add_action('admin_menu', 'dci_add_incarichi_dipendenti_submenu', 9);
function dci_add_incarichi_dipendenti_submenu() {



    // Controllo dell'opzione
    if (dci_get_option("ck_incarichieautorizzazioniaidipendenti", "Trasparenza") === 'false' 
        || dci_get_option("ck_incarichieautorizzazioniaidipendenti", "Trasparenza") === '') {
        return; // Non aggiunge il menu se opzione falsa
    }
	
    $parent_slug = 'edit.php?post_type=elemento_trasparenza';
    $menu_slug   = 'edit.php?post_type=incarichi_dip';

    if (current_user_can('edit_incarichi_dip')) {
        // Lista dei post
        add_submenu_page(
            $parent_slug,
            __('Incarichi conferiti e autorizzati', 'design_comuni_italia'),
            __('Incarichi conferiti e autorizzati', 'design_comuni_italia'),
            'edit_incarichi_dip',
            $menu_slug
        );

        // Aggiungi nuovo (necessario per permessi) ma lo nascondiamo via CSS
        add_submenu_page(
            $parent_slug,
            __('Aggiungi Nuovo Incarico', 'design_comuni_italia'),
            __('Aggiungi Nuovo', 'design_comuni_italia'),
            'edit_incarichi_dip',
            'post-new.php?post_type=incarichi_dip'
        );
    }
}

/* -------------------------------------------------
   Nascondere la voce "Aggiungi Nuovo" dal menu
--------------------------------------------------*/
add_action('admin_head', function() {
	
	    // Controllo dell'opzione
    if (dci_get_option("ck_incarichieautorizzazioniaidipendenti", "Trasparenza") === 'false' 
        || dci_get_option("ck_incarichieautorizzazioniaidipendenti", "Trasparenza") === '') {
        return; // Non aggiunge il menu se opzione falsa
    }
	
    global $submenu;
    if (isset($submenu['edit.php?post_type=elemento_trasparenza'])) {
        foreach ($submenu['edit.php?post_type=elemento_trasparenza'] as $key => $item) {
            if ($item[2] === 'post-new.php?post_type=incarichi_dip') {
                unset($submenu['edit.php?post_type=elemento_trasparenza'][$key]);
            }
        }
    }
});



// Aggiunge la voce "Aggiungi Incarico conferito" nella Admin Bar sotto "+ Nuovo"
add_action('admin_bar_menu', 'dci_add_admin_bar_new_incarico_dip', 999);
function dci_add_admin_bar_new_incarico_dip($wp_admin_bar) {

    // Controlla se l'opzione è false o vuota
    if (dci_get_option("ck_incarichieautorizzazioniaidipendenti", "Trasparenza") === 'false' 
        || dci_get_option("ck_incarichieautorizzazioniaidipendenti", "Trasparenza") === '') {
        return; // Non aggiungere la voce
    }

    // Controlla se l'utente ha i permessi
    if (!current_user_can('edit_incarichi_dip')) {
        return;
    }

    // Aggiunge la voce sotto "+ Nuovo" (ID: new-content)
    $wp_admin_bar->add_node(array(
        'id'     => 'new-incarico-dip', // ID unico
        'title'  => 'Incarico conferito',
        'href'   => admin_url('post-new.php?post_type=incarichi_dip'),
        'parent' => 'new-content' // Sotto "+ Nuovo"
    ));
}



/* -------------------------------------------------
   Messaggio informativo nel backend
--------------------------------------------------*/
add_action( 'edit_form_after_title', 'dci_icad_notice_after_title' );


function dci_icad_notice_after_title( $post ) {
	if ( $post->post_type === 'incarichi_dip' ) {
		echo '<span><i>Il <strong>titolo/norma</strong> corrisponde al <strong>titolo dell\'incarico conferito</strong>.</i></span><br><br>';
	}
}

/* -------------------------------------------------
   CMB2 Metaboxes con nuovi campi
--------------------------------------------------*/
add_action( 'cmb2_init', 'dci_icad_metaboxes' );
function dci_icad_metaboxes() {

	$prefix = '_dci_icad_';

	$cmb_apertura = new_cmb2_box( array(
		'id'           => $prefix . 'box_apertura',
		'title'        => __( 'Informazioni sull\'incarico conferito', 'design_comuni_italia' ),
		'object_types' => array( 'incarichi_dip' ),
	) );

	$cmb_apertura->add_field( array(
		'id'          => $prefix . 'anno_conferimento',
		'name'        => __( 'Anno di Conferimento', 'design_comuni_italia' ),
		'type'        => 'text_date_timestamp',
		'date_format' => 'Y',
	) );

	$cmb_apertura->add_field( array(
		'id'   => $prefix . 'soggetto_dichiarante',
		'name' => __( 'Soggetto dichiarante', 'design_comuni_italia' ),
		'type' => 'text',
	) );

	$cmb_apertura->add_field( array(
		'id'   => $prefix . 'soggetto_percettore',
		'name' => __( 'Soggetto percettore', 'design_comuni_italia' ),
		'type' => 'text',
	) );

	$cmb_apertura->add_field( array(
		'id'      => $prefix . 'dirigente_non_dirigente',
		'name'    => __( 'Dirigente/Non Dirigente', 'design_comuni_italia' ),
		'type'    => 'select',
		'options' => array(
			'dirigente'     => __( 'Dirigente', 'design_comuni_italia' ),
			'non_dirigente' => __( 'Non Dirigente', 'design_comuni_italia' ),
		),
	) );

	$cmb_apertura->add_field( array(
		'id'   => $prefix . 'soggetto_conferente',
		'name' => __( 'Soggetto Conferente', 'design_comuni_italia' ),
		'type' => 'text',
	) );

	$cmb_apertura->add_field( array(
		'id'          => $prefix . 'data_conferimento_autorizzazione',
		'name'        => __( 'Data conferimento autorizzazione dell’incarico', 'design_comuni_italia' ),
		'type'        => 'text_date_timestamp',
		'date_format' => 'd/m/Y',
	) );

	$cmb_apertura->add_field( array(
		'id'   => $prefix . 'oggetto_incarico',
		'name' => __( 'Oggetto dell’incarico', 'design_comuni_italia' ),
		'type' => 'text',
	) );

	$cmb_apertura->add_field( array(
		'id'   => $prefix . 'durata',
		'name' => __( 'Durata', 'design_comuni_italia' ),
		'type' => 'text',
	) );

	$cmb_apertura->add_field( array(
		'id'   => $prefix . 'compenso_lordo',
		'name' => __( 'Compenso Lordo', 'design_comuni_italia' ),
		'type' => 'text',
	) );

	$cmb_documenti = new_cmb2_box( array(
		'id'           => $prefix . 'box_documenti',
		'title'        => __( 'Documenti', 'design_comuni_italia' ),
		'object_types' => array( 'incarichi_dip' ),
	) );

	$cmb_documenti->add_field( array(
		'id'   => $prefix . 'allegati',
		'name' => __( 'Allegati', 'design_comuni_italia' ),
		'type' => 'file_list',
	) );
}

/* -------------------------------------------------
   JS backend (opzionale)
--------------------------------------------------*/
add_action( 'admin_print_scripts-post-new.php', 'dci_icad_admin_script', 11 );
add_action( 'admin_print_scripts-post.php',      'dci_icad_admin_script', 11 );
function dci_icad_admin_script() {
	if ( get_current_screen()->post_type === 'incarichi_dip' ) {
		wp_enqueue_script(
			'icad-admin-script',
			get_template_directory_uri() . '/inc/admin-js/incarichi_dip.js',
			array(), null, true
		);
	}
}

/* -------------------------------------------------
   Popola automaticamente post_content
--------------------------------------------------*/
add_filter( 'wp_insert_post_data', 'dci_icad_set_post_content', 99, 1 );
function dci_icad_set_post_content( $data ) {

	if ( $data['post_type'] === 'incarichi_dip' ) {

		$prefix = '_dci_icad_';

		$descrizione_breve = isset( $_POST[$prefix . 'oggetto_incarico'] )
			? sanitize_text_field( $_POST[$prefix . 'oggetto_incarico'] )
			: '';

		$soggetto_dichiarante = isset( $_POST[$prefix . 'soggetto_dichiarante'] )
			? sanitize_text_field( $_POST[$prefix . 'soggetto_dichiarante'] )
			: '';

		$data['post_content'] = $descrizione_breve . "\n\n" . 'Dichiarante: ' . $soggetto_dichiarante;
	}

	return $data;
}



