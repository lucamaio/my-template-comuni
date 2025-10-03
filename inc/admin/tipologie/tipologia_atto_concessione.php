<?php

/**
 * Registra il custom post type "atto_concessione"
 */
add_action('init', 'dci_register_post_type_atto_concessione');
function dci_register_post_type_atto_concessione()
{





    
    $labels = array(
        'name'               => _x('Atti di Concessione', 'Post Type General Name', 'design_comuni_italia'),
        'singular_name'      => _x('Atto di Concessione', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new'            => _x('Aggiungi un Atto di Concessione', 'Post Type', 'design_comuni_italia'),
        'add_new_item'       => __('Aggiungi un nuovo Atto di Concessione', 'design_comuni_italia'),
        'edit_item'          => __('Modifica Atto di Concessione', 'design_comuni_italia'),
        'featured_image'     => __('Immagine di riferimento', 'design_comuni_italia'),
    );

    $args = array(
        'label'               => __('Atto di Concessione', 'design_comuni_italia'),
        'labels'              => $labels,
        'supports'            => array('title', 'author'),
        'hierarchical'        => true,
        'public'              => true,
        'show_in_menu'        => False,
        'menu_icon'           => 'dashicons-media-interactive',
        'has_archive'         => false,
        //'rewrite'             => array('slug' => 'atto-concessione', 'with_front' => false),
        'capability_type' => array('atto_concessione', 'atti_concessione'),
        'map_meta_cap'        => true,
        'capabilities' => array(
                'edit_post'             => 'edit_atto_concessione',
                'read_post'             => 'read_atto_concessione',
                'delete_post'           => 'delete_atto_concessione',
                'edit_posts'            => 'edit_atti_concessione',
                'edit_others_posts'     => 'edit_others_atti_concessione',
                'publish_posts'         => 'publish_atti_concessione',
                'read_private_posts'    => 'read_private_atti_concessione',
                'delete_posts'          => 'delete_atti_concessione',
                'delete_private_posts'  => 'delete_private_atti_concessione',
                'delete_published_posts'=> 'delete_published_atti_concessione',
                'delete_others_posts'   => 'delete_others_atti_concessione',
                'edit_private_posts'    => 'edit_private_atti_concessione',
                'edit_published_posts'  => 'edit_published_atti_concessione',
                'create_posts'          => 'create_atti_concessione',
            ),
        'description'         => __("Tipologia personalizzata per la pubblicazione dei atto di concessione del Comune.", 'design_comuni_italia'),
    );

    register_post_type('atto_concessione', $args);

    // Rimuove il supporto all'editor
    remove_post_type_support('atto_concessione', 'editor');
}

add_action('admin_init', function() {
    // Prendi il ruolo amministratore
    $role = get_role('administrator');

    if ($role) {
        $caps = [
            'edit_attoconcessione',
            'read_attoconcessione',
            'delete_attoconcessione',
            'edit_attoconcessione',
            'edit_others_attoconcessione',
            'publish_attoconcessione',
            'read_private_attoconcessione',
            'delete_attoconcessione',
            'delete_private_attoconcessione',
            'delete_published_attoconcessione',
            'delete_others_att_concessione',
            'edit_private_attoconcessione',
            'edit_published_attoconcessione',
            'create_attoconcessione',
        ];

        foreach ($caps as $cap) {
            $role->add_cap($cap);
        }
    }
});




// Aggiungi voce al menu admin per Atti di Concessione, con "Aggiungi nuovo" nascosta
add_action('admin_menu', 'dci_add_atto_concessione_submenu', 9);
function dci_add_atto_concessione_submenu() {

        // Controlla se l'opzione "ck_attidiconcessione" è impostata su 'false' o vuota
    if (dci_get_option("ck_attidiconcessione", "Trasparenza") === 'false' || dci_get_option("ck_attidiconcessione", "Trasparenza") === '') {
        return; // Non registrare il CPT se la condizione non è soddisfatta
    }

    
    $parent_slug = 'edit.php?post_type=elemento_trasparenza';
    $menu_slug   = 'edit.php?post_type=atto_concessione';

    if ( current_user_can('edit_atti_concessione') ) {
        // Lista degli atti
        add_submenu_page(
            $parent_slug,
            __('Atti di Concessione', 'design_comuni_italia'),
            __('Atti di Concessione', 'design_comuni_italia'),
            'edit_atti_concessione',
            $menu_slug
        );

        // Aggiungi nuovo (necessario per permessi, poi nascosto)
        add_submenu_page(
            $parent_slug,
            __('Aggiungi Nuovo Atto', 'design_comuni_italia'),
            __('Aggiungi Nuovo', 'design_comuni_italia'),
            'edit_atti_concessione',
            'post-new.php?post_type=atto_concessione'
        );
    }
}

// Nascondere la voce "Aggiungi nuovo" dal menu
add_action('admin_head', function() {
        // Controlla se l'opzione "ck_attidiconcessione" è impostata su 'false' o vuota
    if (dci_get_option("ck_attidiconcessione", "Trasparenza") === 'false' || dci_get_option("ck_attidiconcessione", "Trasparenza") === '') {
        return; // Non registrare il CPT se la condizione non è soddisfatta
    }

    global $submenu;
    $parent_slug = 'edit.php?post_type=elemento_trasparenza';
    if (isset($submenu[$parent_slug])) {
        foreach ($submenu[$parent_slug] as $key => $item) {
            if ($item[2] === 'post-new.php?post_type=atto_concessione') {
                unset($submenu[$parent_slug][$key]);
            }
        }
    }
});





// Aggiunge la voce "Aggiungi Atto di Concessione" nella Admin Bar
add_action('admin_bar_menu', 'dci_add_admin_bar_new_atto_concessione', 999);
function dci_add_admin_bar_new_atto_concessione($wp_admin_bar) {

    // Controlla se l'opzione è false o vuota
    if (dci_get_option("ck_attidiconcessione", "Trasparenza") === 'false' || dci_get_option("ck_attidiconcessione", "Trasparenza") === '') {
        return; // Non aggiungere la voce
    }

    // Controlla se l'utente ha i permessi
    if (!current_user_can('edit_atti_concessione')) {
        return; // Non aggiungere la voce
    }

    // Aggiunge la voce sotto il menu "Nuovo" (ID: new-content)
    $wp_admin_bar->add_node(array(
        'id'     => 'new-atto_concessione', // ID unico
        'title'  => 'Atto di Concessione',
        'href'   => admin_url('post-new.php?post_type=atto_concessione'),
        'parent' => 'new-content' // Sotto "+ Nuovo"
    ));
}





/**
 * Messaggio informativo sotto il titolo nel backend
 */
add_action('edit_form_after_title', 'dci_atto_concessione_add_content_after_title');
function dci_atto_concessione_add_content_after_title($post)
{
    if ($post->post_type == 'atto_concessione') {
        echo '<span><i>Il <strong>titolo/norma</strong> corrisponde al <strong>titolo del Atto di Concessione</strong>.</i></span><br><br>';
    }
}

/**
 * CMB2 Metaboxes per il CPT "atto_concessione"
 */
add_action('cmb2_init', 'dci_add_atto_concessione_metaboxes');
function dci_add_atto_concessione_metaboxes()
{
    $prefix = '_dci_atto_concessione_';

    // Metabox: Apertura
    $cmb_apertura = new_cmb2_box(array(
        'id'           => $prefix . 'box_apertura',
        'title'        => __('Informazioni sul atto_concessione', 'design_comuni_italia'),
        'object_types' => array('atto_concessione'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

     $cmb_apertura->add_field(array(
        'id'                => $prefix . 'tipo_stato',
        'name'              => __('Stato dell\'atto *', 'design_comuni_italia'),
        'desc'              => __('Selezionare la stato dell\'atto', 'design_comuni_italia'),
        'type'              => 'taxonomy_radio_hierarchical',
        'taxonomy'          => 'tipi_stato_bando',
        'show_option_none'  => false,
        'remove_default'    => true,
        //'attributes'  => array('required' => 'required'),
    ));
    
    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'anno_beneficio',
        'name'        => __('Anno Beneficio *', 'design_comuni_italia'),
        'desc'        => __("Seleziona l'anno di beneficio dell'atto", 'design_comuni_italia'),
        'type'        => 'text_date_timestamp',
        'date_format' => 'Y',
        //'attributes'  => array('required' => 'required'),
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'rag_incarico',
        'name'        => __("Ragione dell'incarico *", 'design_comuni_italia'),
        'desc'        => __("Spoecificare la ragione dell'incarico dell'atto", 'design_comuni_italia'),
        'type'        => 'text',
        //'attributes'  => array('required' => 'required'),
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'importo',
        'name'        => __("Importo *", 'design_comuni_italia'),
        'desc'        => __('Indica l’importo dell\'atto', 'design_comuni_italia'),
        'type'        => 'text',
        //'attributes'  => array('required' => 'required'),
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'responsabile',
        'name'        => __("Responsabile *", 'design_comuni_italia'),
        'desc'        => __('Indica il Responsabile dell\'atto', 'design_comuni_italia'),
        'type'        => 'text',
        //'attributes'  => array('required' => 'required'),
    ));

    // Metabox: Dettagli
    $cmb_dettagli = new_cmb2_box(array(
        'id'           => $prefix . 'box_dettagli',
        'title'        => __('Dettagli  Beneficiario', 'design_comuni_italia'),
        'object_types' => array('atto_concessione'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

     $cmb_dettagli->add_field( array(
        'id' => $prefix . 'descrizione_breve',
        'name'        => __( 'Descrizione breve', 'design_comuni_italia' ),
        'desc' => __( 'Descrizione sintentica dell\'atto, inferiore a 255 caratteri' , 'design_comuni_italia' ),
        'type' => 'textarea',
        //'attributes'    => array('maxlength'  => '255'),
    ) );

        $cmb_dettagli->add_field(array(
        'name' => __('Ragione sociale *', 'design_comuni_italia'),
        'id'   => $prefix . 'ragione_sociale', // Ho aggiunto il prefix anche qui per coerenza
        'type' => 'text',
        //'attributes'  => array('required' => 'required'),
    ));

    $cmb_dettagli->add_field(array(
        'name' => __('Codice fiscale/P.Iva *', 'design_comuni_italia'),
        'id'   => $prefix . 'codice_fiscale', // Ho aggiunto il prefix anche qui per coerenza
        'type' => 'text',
       // 'attributes'  => array('required' => 'required'),
));

    //DOCUMENTI
    $cmb_documenti = new_cmb2_box( array(
        'id'           => $prefix . 'box_documenti',
        'title'        => __( 'Documenti', 'design_comuni_italia' ),
        'object_types' => array('atto_concessione'),
        'context'      => 'normal',
        'priority'     => 'high',
    ) );

     $cmb_documenti->add_field( array(
        'id' => $prefix . 'allegati',
        'name'        => __( 'Allegati', 'design_comuni_italia' ),
        'desc' => __( 'Elenco di documenti allegati al Atto di Concessione' , 'design_comuni_italia' ),
        'type' => 'file_list',
    ) );
}

/**
 * Includi JS personalizzato nella pagina di modifica/creazione progetto
*/
add_action('admin_print_scripts-post-new.php', 'dci_atto_concessione_admin_script', 11);
add_action('admin_print_scripts-post.php', 'dci_atto_concessione_admin_script', 11);

function dci_atto_concessione_admin_script()
{
    global $post_type;
    if ($post_type == 'atto_concessione') {
        wp_enqueue_script('atto_concessione-admin-script', get_template_directory_uri() . '/inc/admin-js/atto_concessione.js');
    }
}

// Imposta il contenuto del post "Progetto" con i campi personalizzati

add_filter('wp_insert_post_data', 'dci_atto_concessione_set_post_content', 99, 1);
function dci_atto_concessione_set_post_content($data)
{
    if ($data['post_type'] == 'atto_concessione') {
        $descrizione_breve = isset($_POST['_dci_atto_concessione_descrizione_breve']) ? $_POST['_dci_atto_concessione_descrizione_breve'] : '';
        $testo_completo    = isset($_POST['_dci_atto_concessione_testo_completo']) ? $_POST['_dci_atto_concessione_testo_completo'] : '';

        $data['post_content'] = $descrizione_breve . '<br>' . $testo_completo;
    }

    return $data;
}



