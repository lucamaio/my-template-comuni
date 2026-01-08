<?php
/**
 * Registra il custom post type "titolare_incarico"
 */
// var_dump(get_role('administrator'));
add_action('init', 'dci_register_post_type_titolare_incarico');
function dci_register_post_type_titolare_incarico()
{


    $labels = array(
        'name'               => _x('Titolari di incarichi di collaborazione o consulenza', 'Post Type General Name', 'design_comuni_italia'),
        'singular_name'      => _x('Titolare di incarichi di collaborazione o consulenza', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new'            => _x('Aggiungi nuovo Titolare di incarichi di collaborazione o consulenza', 'Post Type', 'design_comuni_italia'),
        'add_new_item'       => __('Aggiungi un nuovo Titolare di incarico di collaborazione o consulenza', 'design_comuni_italia'),
        'edit_item'          => __('Modifica Titolare di incarichi di collaborazione o consulenza', 'design_comuni_italia'),
        'featured_image'     => __('Immagine di riferimento', 'design_comuni_italia'),
    );

    $args = array(
        'label'               => __('Titolari di incarichi di collaborazione o consulenza', 'design_comuni_italia'),
        'labels'              => $labels,
        'supports'            => array('title', 'author'),
        'hierarchical'        => true,
        'public'              => true,
        'show_in_menu'        => false,
        'menu_icon'           => 'dashicons-media-interactive',
        'has_archive'         => false,
        // 'rewrite'             => array('slug' => 'titolari_incarico', 'with_front' => false),
        'rewrite'         => array(
            'with_front' => false,
            'pages' => true,
        ),
        'capability_type' => array('titolare_incarico', 'titolari_incarichi'),
        'map_meta_cap'    => true,
        'capabilities'    => array(
            'edit_post'             => 'edit_titolare_incarico',
            'read_post'             => 'read_titolare_incarico',
            'delete_post'           => 'delete_titolare_incarico',
            'edit_posts'            => 'edit_titolari_incarichi',
            'edit_others_posts'     => 'edit_others_titolari_incarichi',
            'publish_posts'         => 'publish_titolari_incarichi',
            'read_private_posts'    => 'read_private_titolari_incarichi',
            'delete_posts'          => 'delete_titolari_incarichi',
            'delete_private_posts'  => 'delete_private_titolari_incarichi',
            'delete_published_posts'=> 'delete_published_titolari_incarichi',
            'delete_others_posts'   => 'delete_others_titolari_incarichi',
            'edit_private_posts'    => 'edit_private_titolari_incarichi',
            'edit_published_posts'  => 'edit_published_titolari_incarichi',
            'create_posts'          => 'create_titolari_incarichi',
        ),

        'description'         => __("Sezione dedicata alla pubblicazione dei titolari di incarichi di collaborazione o consulenza del Comune.", 'design_comuni_italia'),
    );

    register_post_type('titolare_incarico', $args);

    // Rimuove il supporto all’editor classico
    remove_post_type_support('titolare_incarico', 'editor');
}

add_action('admin_init', function() {
    // Prendi il ruolo amministratore
    $role = get_role('administrator');

    if ($role) {
        $caps = [
            'edit_titolare_incarico',
            'read_titolare_incarico',
            'delete_titolare_incarico',
            'edit_titolare_incarico',
            'edit_others_titolare_incarico',
            'publish_titolare_incarico',
            'read_private_titolare_incarico',
            'delete_titolare_incarico',
            'delete_private_titolare_incarico',
            'delete_published_titolare_incarico',
            'delete_others_titolare_incarico',
            'edit_private_titolare_incarico',
            'edit_published_titolare_incarico',
            'create_titolare_incarico',
        ];

        foreach ($caps as $cap) {
            $role->add_cap($cap);
        }
    }
});



// Aggiungi voce al menu admin con "Aggiungi nuovo" nascosta
add_action('admin_menu', 'dci_add_titolare_incarico_submenu', 9);
function dci_add_titolare_incarico_submenu() {

    

    if (dci_get_option("ck_titolariIncarichiCollaborazioneConsulenzaTemplatePersonalizzato", "Trasparenza") === 'false' || dci_get_option("ck_titolariIncarichiCollaborazioneConsulenzaTemplatePersonalizzato", "Trasparenza") === '') {
        return; // Non registrare il CPT se la condizione non è soddisfatta
    }


    
    $parent_slug = 'edit.php?post_type=elemento_trasparenza';
    $menu_slug   = 'edit.php?post_type=titolare_incarico';

    if ( current_user_can('edit_titolari_incarichi') ) {
        // Lista dei titolari
        add_submenu_page(
            $parent_slug,
            __('Titolari Incarichi', 'design_comuni_italia'),
            __('Titolari Incarichi', 'design_comuni_italia'),
            'edit_titolari_incarichi',
            $menu_slug
        );

        // Aggiungi nuovo (necessario per permessi, poi nascosto)
        add_submenu_page(
            $parent_slug,
            __('Aggiungi Nuovo Titolare', 'design_comuni_italia'),
            __('Aggiungi Nuovo', 'design_comuni_italia'),
            'edit_titolari_incarichi',
            'post-new.php?post_type=titolare_incarico'
        );
    }
}

// Nascondere la voce "Aggiungi nuovo" dal menu
add_action('admin_head', function() {

        if (dci_get_option("ck_titolariIncarichiCollaborazioneConsulenzaTemplatePersonalizzato", "Trasparenza") === 'false' || dci_get_option("ck_titolariIncarichiCollaborazioneConsulenzaTemplatePersonalizzato", "Trasparenza") === '') {
        return; // Non registrare il CPT se la condizione non è soddisfatta
    }
    
    global $submenu;
    $parent_slug = 'edit.php?post_type=elemento_trasparenza';
    if (isset($submenu[$parent_slug])) {
        foreach ($submenu[$parent_slug] as $key => $item) {
            if ($item[2] === 'post-new.php?post_type=titolare_incarico') {
                unset($submenu[$parent_slug][$key]);
            }
        }
    }
});



// Aggiunge la voce "Aggiungi Titolare incarico" nella Admin Bar sotto "+ Nuovo"
add_action('admin_bar_menu', 'dci_add_admin_bar_new_titolare_incarico', 999);
function dci_add_admin_bar_new_titolare_incarico($wp_admin_bar) {

    // Controlla l'opzione
    if (dci_get_option("ck_titolariIncarichiCollaborazioneConsulenzaTemplatePersonalizzato", "Trasparenza") === 'false' 
        || dci_get_option("ck_titolariIncarichiCollaborazioneConsulenzaTemplatePersonalizzato", "Trasparenza") === '') {
        return; // Non aggiungere la voce
    }

    // Controlla permessi
    if (!current_user_can('edit_titolari_incarichi')) {
        return;
    }

    // Aggiunge la voce sotto "+ Nuovo"
    $wp_admin_bar->add_node(array(
        'id'     => 'new-titolare-incarico',
        'title'  => 'Titolare incarico',
        'href'   => admin_url('post-new.php?post_type=titolare_incarico'),
        'parent' => 'new-content'
    ));
}







/**
 * Messaggio informativo sotto il titolo nel backend
 */
add_action('edit_form_after_title', 'dci_titolare_incarico_add_content_after_title');
function dci_titolare_incarico_add_content_after_title($post)
{
    if ($post->post_type == 'titolare_incarico') {
        echo '<span><i>Il <strong>titolo</strong> deve corrispondere al <strong>nome del soggetto del titolare dell’incarico</strong>.</i></span><br><br>';
    }
}

/**
 * Metabox CMB2 per il CPT "titolare_incarico"
 */
add_action('cmb2_init', 'dci_add_titolare_incarico_metaboxes');
function dci_add_titolare_incarico_metaboxes()
{
    $prefix = '_dci_titolare_incarico_';

    // --- Apertura ---
    $cmb_apertura = new_cmb2_box(array(
        'id'           => $prefix . 'box_apertura',
        'title'        => __('Dati incarico', 'design_comuni_italia'),
        'object_types' => array('titolare_incarico'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    // $cmb_apertura->add_field(array(
    //     'id'          => $prefix . 'soggetto',
    //     'name'        => __('Soggetto *', 'design_comuni_italia'),
    //     'desc'        => __('Inserisci il nominativo del titolare dell’incarico.', 'design_comuni_italia'),
    //     'type'        => 'text',
    //     'attributes'  => array('required' => 'required'),
    // ));
    
    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'oggetto',
        'name'        => __("Oggetto dell'incarico *", 'design_comuni_italia'),
        'desc'        => __("Descrivi sinteticamente l’oggetto dell’incarico conferito.", 'design_comuni_italia'),
        'type'        => 'wysiwyg',
        'attributes'  => array('required' => 'required'),
        'options'     => array(
            'textarea_rows' => 8,
            'teeny'         => false,
        ),
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'compenso',
        'name'        => __('Compenso', 'design_comuni_italia'),
        'desc'        => __('Inserisci l’importo del compenso previsto per l’incarico.', 'design_comuni_italia'),
        'type'        => 'text',
        // 'attributes'  => array('required' => 'required'),
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'data_inizio',
        'name'        => __('Data di inizio', 'design_comuni_italia'),
        'desc'        => __('Seleziona la data di avvio dell’incarico.', 'design_comuni_italia'),
        'type'        => 'text_date_timestamp',
        'date_format' => 'd-m-Y',
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'data_fine',
        'name'        => __('Data di fine', 'design_comuni_italia'),
        'desc'        => __('Seleziona la data di conclusione dell’incarico.', 'design_comuni_italia'),
        'type'        => 'text_date_timestamp',
        'date_format' => 'd-m-Y',
    ));

     $cmb_apertura->add_field(array(
        'id'          => $prefix . 'durata',
        'name'        => __('Durata', 'design_comuni_italia'),
        'desc'        => __('Inserisci la durata prevista per l’incarico.', 'design_comuni_italia'),
        'type'        => 'text',
    ));


    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'atto_conferimento_incarico',
        'name'        => __('Atto di conferimento *', 'design_comuni_italia'),
        'desc'        => __('Inserisci il riferimento o il nome dell’atto di conferimento dell’incarico.', 'design_comuni_italia'),
        'type'        => 'text',
        'attributes'  => array('required' => 'required'),
    ));

    // Attestazione dell'avvenuta verifica dell'insussistenza di situazioni, anche potenziali, di conflitto di interessi

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'situazioni_conflitto',
        'name'        => __("Attestazione dell'avvenuta verifica dell'insussistenza di situazioni, anche potenziali, di conflitto di interessi", 'design_comuni_italia'),
        'desc'        => __('Seleziona l’esito della verifica di insussistenza di conflitto di interessi.', 'design_comuni_italia'),
        'type'        => 'select',
        'default'     => 'No',
        'options'     => array(
            'Si' => __('Sì', 'design_comuni_italia'),
            'No' => __('No', 'design_comuni_italia'),
        ),
        // 'attributes'  => array('required' => 'required'),
    ));



    // --- Documenti ---
    $cmb_documenti = new_cmb2_box(array(
        'id'           => $prefix . 'box_documenti',
        'title'        => __('Documenti allegati', 'design_comuni_italia'),
        'object_types' => array('titolare_incarico'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    $cmb_documenti->add_field(array(
        'id'   => $prefix . 'allegati',
        'name' => __('Atto di conferimento (documento)', 'design_comuni_italia'),
        'desc' => __('Carica uno o più documenti relativi all’atto di conferimento.', 'design_comuni_italia'),
        'type' => 'file_list',
    ));

    $cmb_documenti->add_field(array(
        'id'   => $prefix . 'cv_allegati',
        'name' => __('Curriculum', 'design_comuni_italia'),
        'desc' => __('Carica il curriculum del titolare dell’incarico.', 'design_comuni_italia'),
        'type' => 'file_list',
    ));
}


/**
 * Includi JS personalizzato nella pagina di modifica/creazione del CPT
 */
add_action('admin_print_scripts-post-new.php', 'dci_titolare_incarico_admin_script', 11);
add_action('admin_print_scripts-post.php', 'dci_titolare_incarico_admin_script', 11);

function dci_titolare_incarico_admin_script()
{
    global $post_type;

    if ($post_type === 'titolare_incarico') {
        wp_enqueue_script(
            'titolare_incarico-admin-script',
            get_template_directory_uri() . '/inc/admin-js/titolare_incarico.js',
            array('jquery'),
            null,
            true
        );
    }
}

/**
 * Imposta automaticamente il contenuto del post
 * utilizzando i campi personalizzati principali
 */
add_filter('wp_insert_post_data', 'dci_titolare_incarico_set_post_content', 99, 1);
function dci_titolare_incarico_set_post_content($data)
{
    if ($data['post_type'] === 'titolare_incarico') {
        
        $oggetto   = isset($_POST['_dci_titolare_incarico_oggetto']) ? wp_strip_all_tags($_POST['_dci_titolare_incarico_oggetto']) : '';
        $compenso  = isset($_POST['_dci_titolare_incarico_compenso']) ? wp_strip_all_tags($_POST['_dci_titolare_incarico_compenso']) : '';
        $atto      = isset($_POST['_dci_titolare_incarico_atto_conferimento_incarico']) ? wp_strip_all_tags($_POST['_dci_titolare_incarico_atto_conferimento_incarico']) : '';

        // Costruisce un contenuto descrittivo automatico
        $contenuto = '';
        if ($oggetto) {
            $contenuto .= '<p><strong>Oggetto:</strong> ' . $oggetto . '</p>';
        }
        if ($compenso) {
            $contenuto .= '<p><strong>Compenso:</strong> ' . $compenso . '</p>';
        }
        if ($atto) {
            $contenuto .= '<p><strong>Atto di conferimento:</strong> ' . $atto . '</p>';
        }

        // Assegna al contenuto del post
        $data['post_content'] = $contenuto;
    }

    return $data;
}









