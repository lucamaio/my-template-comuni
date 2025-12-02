<?php

/**
 * Definisce post type consiglio
 */
add_action('init', 'dci_register_post_type_consiglio');
function dci_register_post_type_consiglio()
{

    $labels = array(
        'name'          => _x('Consiglio Comunale', 'Post Type General Name', 'design_comuni_italia'),
        'singular_name' => _x('Consiglio Comunale', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new'       => _x('Aggiungi un Consiglio Comunale', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new_item'  => _x('Aggiungi un Consiglio Comunale', 'Post Type Singular Name', 'design_comuni_italia'),
        'edit_item'       => _x('Modifica Consiglio Comunale', 'Post Type Singular Name', 'design_comuni_italia'),
        'featured_image' => __('Immagine di riferimento', 'design_comuni_italia'),
    );
    $args   = array(
        'label'         => __('Consiglio Comunale', 'design_comuni_italia'),
        'labels'        => $labels,
        'supports'      => array('title', 'editor', 'author'),
        'hierarchical'  => false,
        'public'        => true,
        'menu_position' => 5,
        'menu_icon'     => 'dashicons-groups',
        'has_archive'   => true,
        // 'capability_type' => array('consiglio', 'consiglio'),
        'map_meta_cap'    => true,
        'description'    => __("Tipologia che struttura le informazioni relative a agli aggiornamenti d un comune", 'design_comuni_italia'),
    );
    register_post_type('consiglio', $args);

    remove_post_type_support('consiglio', 'editor');
}

/**
 * Aggiungo label sotto il titolo
 */
add_action('edit_form_after_title', 'dci_consiglio_add_content_after_title');
function dci_consiglio_add_content_after_title($post)
{
    if ($post->post_type == "consiglio")
        _e('<span><i>il <b>Titolo</b> è il <b>Titolo del Consiglio Comunale</b>.</i></span><br><br>', 'design_comuni_italia');
}

add_action('cmb2_init', 'dci_add_consiglio_metaboxes');
function dci_add_consiglio_metaboxes()
{
    $prefix = '_dci_consiglio_';

    //argomenti
    $cmb_argomenti = new_cmb2_box(array(
        'id'           => $prefix . 'box_argomenti',
        'title'        => __('Argomenti *', 'design_comuni_italia'),
        'object_types' => array('consiglio'),
        'context'      => 'side',
        'priority'     => 'high',
    ));

    $cmb_argomenti->add_field(array(
        'id' => $prefix . 'argomenti',
        'type'             => 'taxonomy_multicheck_hierarchical',
        'taxonomy'       => 'argomenti',
        'show_option_none' => false,
        'remove_default' => 'true',
    ));

    //APERTURA
    $cmb_apertura = new_cmb2_box(array(
        'id'           => $prefix . 'box_apertura',
        'title'        => __('Apertura', 'design_comuni_italia'),
        'object_types' => array('consiglio'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));


    // Data del consiglio
    $cmb_apertura->add_field(array(
        'id'           => $prefix . 'data',
        'name'         => __('Data del Consiglio Comunale *', 'design_comuni_italia'),
        'desc'         => __('Seleziona la data in cui si terrà il Consiglio Comunale.', 'design_comuni_italia'),
        'type'         => 'text_date_timestamp',
        'date_format'  => 'd-m-Y',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ));

    // Ora di inizio
    $cmb_apertura->add_field(array(
        'id'           => $prefix . 'ora_inizio',
        'name'         => __('Ora di inizio *', 'design_comuni_italia'),
        'desc'         => __('Inserisci l’orario di inizio del Consiglio Comunale.', 'design_comuni_italia'),
        'type'         => 'text_time',
        'time_format'  => 'H:i',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ));

    // Ora di fine
    $cmb_apertura->add_field(array(
        'id'           => $prefix . 'ora_fine',
        'name'         => __('Ora di fine *', 'design_comuni_italia'),
        'desc'         => __('Inserisci l’orario previsto di conclusione del Consiglio Comunale.', 'design_comuni_italia'),
        'type'         => 'text_time',
        'time_format'  => 'H:i',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ));


    $cmb_apertura->add_field(array(
        'id' => $prefix . 'a_cura_di',
        'name'    => __('A cura di *', 'design_comuni_italia'),
        'desc' => __('Ufficio che ha curato il comunicato (presumibilmente l\'ufficio comunicazione)', 'design_comuni_italia'),
        'type'    => 'pw_multiselect',
        'options' => dci_get_posts_options('unita_organizzativa'),
        'attributes'    => array(
            'required'    => 'required',
            'placeholder' =>  __('Seleziona le unità organizzative', 'design_comuni_italia'),
        ),
    ));

      $cmb_apertura->add_field(array(
        'id' => $prefix . 'partecipanti',
        'name'    => __('Partecipanti', 'design_comuni_italia'),
        'desc' => __('Riferimento alle persone che hanno partecipato al Consiglio Comunale', 'design_comuni_italia'),
        'type'    => 'pw_multiselect',
        'options' => dci_get_posts_options('persona_pubblica'),
        'attributes' => array(
            'placeholder' =>  __('Seleziona le Persone Pubbliche', 'design_comuni_italia'),
        ),
    ));

    $cmb_apertura->add_field(array(
        'id' => $prefix . 'descrizione_breve',
        'name'        => __('Descrizione breve *', 'design_comuni_italia'),
        'desc' => __('Descrizione sintentica della consiglio, inferiore a 255 caratteri', 'design_comuni_italia'),
        'type' => 'textarea',
        'attributes'    => array(
            'maxlength'  => '255',
            'required'    => 'required'
        ),
    ));

    //CORPO
    $cmb_corpo = new_cmb2_box(array(
        'id'           => $prefix . 'box_corpo',
        'title'        => __('Corpo', 'design_comuni_italia'),
        'object_types' => array('consiglio'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));
    $cmb_corpo->add_field(array(
        'id' => $prefix . 'ordini_giorno',
        'name'        => __('Ordini del giorno: *', 'design_comuni_italia'),
        'desc' => __('Elenco degli ordini del giorno del consiglio', 'design_comuni_italia'),
        'type' => 'wysiwyg',
        'attributes'    => array(
            'required'    => 'required'
        ),
        'options' => array(
            'textarea_rows' => 10, // rows="..."
            'teeny' => false, // output the minimal editor config used in Press This
        ),
    ));
   
    //DOCUMENTI
    $cmb_documenti = new_cmb2_box(array(
        'id'           => $prefix . 'box_documenti',
        'title'        => __('Documenti', 'design_comuni_italia'),
        'object_types' => array('consiglio'),
        'context'      => 'normal',
        'priority'     => 'low',
    ));

    $cmb_documenti->add_field(array(
        'id' => $prefix . 'documenti',
        'name'        => __('Documenti', 'design_comuni_italia'),
        'desc' => __('Link a schede di Documenti', 'design_comuni_italia'),
        'type'    => 'pw_multiselect',
        'options' => dci_get_posts_options('documento_pubblico'),
        'attributes' => array(
            'placeholder' =>  __('Seleziona i Documenti Pubblici', 'design_comuni_italia'),
        ),
    ));

    $cmb_documenti->add_field(array(
        'id' => $prefix . 'allegati',
        'name'        => __('Allegati', 'design_comuni_italia'),
        'desc' => __('Elenco di documenti allegati alla struttura', 'design_comuni_italia'),
        'type' => 'file_list',
    ));


    $cmb_extra_info = new_cmb2_box(array(
        'id'           => $prefix . 'box_extra_info',
        'title'        => __('Ulteriori Informazioni', 'design_comuni_italia'),
        'object_types' => array('consiglio'),
        'context'      => 'normal',
        'priority'     => 'low',
    ));

    $cmb_extra_info->add_field(array(
        'id' => $prefix . 'more_info',
        'name'        => __('Note', 'design_comuni_italia'),
        'desc' => __('Eventuali note aggiuntive', 'design_comuni_italia'),
        'type' => 'wysiwyg',
        'options'    => array(
            'textarea_rows' => 3,
        ),
    ));

    // link consiglio streaming
    $cmb_extra_info->add_field(array(
        'id' => $prefix . 'link_streaming',
        'name'        => __('Link al Consiglio in Streaming', 'design_comuni_italia'),
        'desc' => __('Link al Consiglio Comunale in Streaming', 'design_comuni_italia'),
        'type' => 'text_url',
        'attributes'    => array(
            'placeholder' =>  __('https://esempio.com/streaming-consiglio', 'design_comuni_italia'),
        ),
    ));

}

/**
 * aggiungo js per controllo compilazione campi
 */

add_action('admin_print_scripts-post-new.php', 'dci_consiglio_admin_script', 11);
add_action('admin_print_scripts-post.php', 'dci_consiglio_admin_script', 11);

function dci_consiglio_admin_script()
{
    global $post_type;
    if ('consiglio' == $post_type)
        wp_enqueue_script('consiglio-admin-script', get_template_directory_uri() . '/inc/admin-js/consiglio.js');
}

/**
 * Valorizzo il post content in base al contenuto dei campi custom
 * @param $data
 * @return mixed
 */
function dci_consiglio_set_post_content($data)
{

    if ($data['post_type'] == 'consiglio') {

        $descrizione_breve = '';
        if (isset($_POST['_dci_consiglio_descrizione_breve'])) {
            $descrizione_breve = $_POST['_dci_consiglio_descrizione_breve'];
        }

        $ordini_giorno = '';
        if (isset($_POST['_dci_consiglio_ordini_giorno'])) {
            $ordini_giorno = $_POST['_dci_consiglio_ordini_giorno'];
        }

        $content = $descrizione_breve . '<br>' . $ordini_giorno;

        $data['post_content'] = $content;
    }

    return $data;
}
add_filter('wp_insert_post_data', 'dci_consiglio_set_post_content', '99', 1);
