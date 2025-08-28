<?php
/**
 * Registra il custom post type "Titolari di incarichi di collaborazione o consulenza"
 */
add_action('init', 'dci_register_post_type_titolari_di_incarichi_collaborazione_consulenza');
function dci_register_post_type_titolari_di_incarichi_collaborazione_consulenza()
{
    // Verifica se l'opzione è attiva
    // if (dci_get_option("ck_titolari_incarico_collaborazione_consulenzadigaratemplatepersonalizzato", "Trasparenza") === 'false' 
    //     || dci_get_option("ck_titolari_incarico_collaborazione_consulenzadigaratemplatepersonalizzato", "Trasparenza") === '') {
    //     return; 
    // }

    $labels = array(
        'name'               => _x('Titolari di incarichi di collaborazione o consulenza', 'Post Type General Name', 'design_comuni_italia'),
        'singular_name'      => _x('Titolare di incarichi di collaborazione o consulenza', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new'            => _x('Aggiungi nuovo incarico', 'Post Type', 'design_comuni_italia'),
        'add_new_item'       => __('Aggiungi un nuovo Titolare di incarico di collaborazione o consulenza', 'design_comuni_italia'),
        'edit_item'          => __('Modifica incarico di collaborazione o consulenza', 'design_comuni_italia'),
        'featured_image'     => __('Immagine di riferimento', 'design_comuni_italia'),
    );

    $args = array(
        'label'               => __('Titolari di incarichi di collaborazione o consulenza', 'design_comuni_italia'),
        'labels'              => $labels,
        'supports'            => array('title', 'author'),
        'hierarchical'        => false,
        'public'              => true,
        'show_in_menu'        => 'edit.php?post_type=elemento_trasparenza',
        'menu_icon'           => 'dashicons-media-interactive',
        'has_archive'         => false,
        'rewrite'             => array('slug' => 'titolari_incarico_collaborazione_consulenza', 'with_front' => false),
        'capability_type'     => array('titolare_incarico_collaborazione_consulenza', 'titolari_incarico_collaborazione_consulenza'),
        'map_meta_cap'        => true,
        'description'         => __("Sezione dedicata alla pubblicazione dei titolari di incarichi di collaborazione o consulenza del Comune.", 'design_comuni_italia'),
    );

    register_post_type('titolare_incarico_collaborazione_consulenza', $args);

    // Rimuove il supporto all’editor classico
    remove_post_type_support('titolare_incarico_collaborazione_consulenza', 'editor');
}

/**
 * Messaggio informativo sotto il titolo nel backend
 */
add_action('edit_form_after_title', 'dci_titolare_incarico_collaborazione_consulenza_add_content_after_title');
function dci_titolare_incarico_collaborazione_consulenza_add_content_after_title($post)
{
    if ($post->post_type == 'titolare_incarico_collaborazione_consulenza') {
        echo '<span><i>Il <strong>titolo</strong> deve corrispondere al <strong>nome del titolare dell’incarico</strong>.</i></span><br><br>';
    }
}

/**
 * Metabox CMB2 per il CPT "titolare_incarico_collaborazione_consulenza"
 */
add_action('cmb2_init', 'dci_add_titolare_incarico_collaborazione_consulenza_metaboxes');
function dci_add_titolare_incarico_collaborazione_consulenza_metaboxes()
{
    $prefix = '_dci_titolare_incarico_collaborazione_consulenza_';

    // --- Apertura ---
    $cmb_apertura = new_cmb2_box(array(
        'id'           => $prefix . 'box_apertura',
        'title'        => __('Dati incarico', 'design_comuni_italia'),
        'object_types' => array('titolare_incarico_collaborazione_consulenza'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'soggetto',
        'name'        => __('Soggetto *', 'design_comuni_italia'),
        'desc'        => __('Inserisci il nominativo del titolare dell’incarico.', 'design_comuni_italia'),
        'type'        => 'text',
        'attributes'  => array('required' => 'required'),
    ));
    
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
        'name'        => __('Compenso *', 'design_comuni_italia'),
        'desc'        => __('Inserisci l’importo del compenso previsto per l’incarico.', 'design_comuni_italia'),
        'type'        => 'text',
        'attributes'  => array('required' => 'required'),
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
        'id'          => $prefix . 'atto_conferimento_incarico',
        'name'        => __('Atto di conferimento *', 'design_comuni_italia'),
        'desc'        => __('Inserisci il riferimento o il nome dell’atto di conferimento dell’incarico.', 'design_comuni_italia'),
        'type'        => 'text',
        'attributes'  => array('required' => 'required'),
    ));

    // --- Documenti ---
    $cmb_documenti = new_cmb2_box(array(
        'id'           => $prefix . 'box_documenti',
        'title'        => __('Documenti allegati', 'design_comuni_italia'),
        'object_types' => array('titolare_incarico_collaborazione_consulenza'),
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
add_action('admin_print_scripts-post-new.php', 'dci_titolare_incarico_collaborazione_consulenza_admin_script', 11);
add_action('admin_print_scripts-post.php', 'dci_titolare_incarico_collaborazione_consulenza_admin_script', 11);

function dci_titolare_incarico_collaborazione_consulenza_admin_script()
{
    global $post_type;

    if ($post_type === 'titolare_incarico_collaborazione_consulenza') {
        wp_enqueue_script(
            'titolare_incarico_collaborazione_consulenza-admin-script',
            get_template_directory_uri() . '/inc/admin-js/titolare_incarico_collaborazione_consulenza.js',
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
add_filter('wp_insert_post_data', 'dci_titolare_incarico_collaborazione_consulenza_set_post_content', 99, 1);
function dci_titolare_incarico_collaborazione_consulenza_set_post_content($data)
{
    if ($data['post_type'] === 'titolare_incarico_collaborazione_consulenza') {
        
        $oggetto   = isset($_POST['_dci_titolare_incarico_collaborazione_consulenza_oggetto']) ? wp_strip_all_tags($_POST['_dci_titolare_incarico_collaborazione_consulenza_oggetto']) : '';
        $compenso  = isset($_POST['_dci_titolare_incarico_collaborazione_consulenza_compenso']) ? wp_strip_all_tags($_POST['_dci_titolare_incarico_collaborazione_consulenza_compenso']) : '';
        $atto      = isset($_POST['_dci_titolare_incarico_collaborazione_consulenza_atto_conferimento_incarico']) ? wp_strip_all_tags($_POST['_dci_titolare_incarico_collaborazione_consulenza_atto_conferimento_incarico']) : '';

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
