<?php

if (!function_exists('dci_incarico_dirigenziale_custom_template_enabled')) {
    function dci_incarico_dirigenziale_custom_template_enabled()
    {
        $option_key = 'ck_incarichidirigenzialitemplatepersonalizzato';
        $option_value = function_exists('dci_get_option')
            ? dci_get_option($option_key, 'Trasparenza', 'true')
            : 'true';

        return $option_value !== '' && 'false' !== (string) $option_value;
    }
}

/**
 * Registra il custom post type "incarico_dirigenziale"
 */
add_action('init', 'dci_register_post_type_incarico_dirigenziale');
function dci_register_post_type_incarico_dirigenziale()
{
    $labels = array(
        'name'               => _x('Incarichi dirigenziali', 'Post Type General Name', 'design_comuni_italia'),
        'singular_name'      => _x('Incarico dirigenziale', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new'            => _x('Aggiungi un incarico dirigenziale', 'Post Type', 'design_comuni_italia'),
        'add_new_item'       => __('Aggiungi un incarico dirigenziale', 'design_comuni_italia'),
        'edit_item'          => __('Modifica incarico dirigenziale', 'design_comuni_italia'),
        'featured_image'     => __('Immagine di riferimento', 'design_comuni_italia'),
    );

    $args = array(
        'label'               => __('Incarico dirigenziale', 'design_comuni_italia'),
        'labels'              => $labels,
        'supports'            => array('title', 'author', 'revisions'),
        'hierarchical'        => false,
        'public'              => true,
        'show_in_menu'        => false,
        //'menu_position'       => 5,
        'menu_icon'           => 'dashicons-media-interactive',
        'has_archive'         => false,
        // 'rewrite'             => array('slug' => 'bandi', 'with_front' => false),
        'rewrite'             => array('slug' => 'incarichi-dirigenziali', 'with_front' => false),
        'capability_type'     => array('incarico_dirig', 'incarichi_dirig'),
        'map_meta_cap'        => true,
        'capabilities'        => array(
            'edit_post'              => 'edit_incarico_dirig',
            'read_post'              => 'read_incarico_dirig',
            'delete_post'            => 'delete_incarico_dirig',
            'edit_posts'             => 'edit_incarichi_dirig',
            'edit_others_posts'      => 'edit_others_incarichi_dirig',
            'publish_posts'          => 'publish_incarichi_dirig',
            'read_private_posts'     => 'read_private_incarichi_dirig',
            'delete_posts'           => 'delete_incarichi_dirig',
            'delete_private_posts'   => 'delete_private_incarichi_dirig',
            'delete_published_posts' => 'delete_published_incarichi_dirig',
            'delete_others_posts'    => 'delete_others_incarichi_dirig',
            'edit_private_posts'     => 'edit_private_incarichi_dirig',
            'edit_published_posts'   => 'edit_published_incarichi_dirig',
            'create_posts'           => 'create_incarichi_dirig',
        ),
        'description'     => __('Dati e documenti relativi agli incarichi dirigenziali.', 'design_comuni_italia'),
    );

    register_post_type('incarico_dirig', $args);

    // Rimuove il supporto all'editor
    remove_post_type_support('incarico_dirig', 'editor');
}

/**
 * Assegna una sola volta i permessi della nuova tipologia agli amministratori
 * e ai ruoli che già gestiscono gli Elementi Trasparenza.
 */
add_action('admin_init', 'dci_incarico_dirigenziale_add_capabilities');
function dci_incarico_dirigenziale_add_capabilities()
{
    if ('1' === (string) get_option('dci_incarico_dirig_caps_version', '0')) {
        return;
    }

    $capabilities = array(
        'edit_incarichi_dirig',
        'edit_others_incarichi_dirig',
        'publish_incarichi_dirig',
        'read_private_incarichi_dirig',
        'delete_incarichi_dirig',
        'delete_private_incarichi_dirig',
        'delete_published_incarichi_dirig',
        'delete_others_incarichi_dirig',
        'edit_private_incarichi_dirig',
        'edit_published_incarichi_dirig',
        'create_incarichi_dirig',
    );

    foreach (wp_roles()->role_objects as $role) {
        if (!$role->has_cap('manage_options') && !$role->has_cap('edit_elementi_trasparenza')) {
            continue;
        }
        foreach ($capabilities as $capability) {
            $role->add_cap($capability);
        }
    }

    flush_rewrite_rules(false);
    update_option('dci_incarico_dirig_caps_version', '1', false);
}





// Aggiunge una sola voce sotto Amministrazione Trasparente.
add_action('admin_menu', 'dci_add_incarico_dirigenziale_submenu', 9);
function dci_add_incarico_dirigenziale_submenu()
{
    if (!dci_incarico_dirigenziale_custom_template_enabled()) {
        return;
    }

    $parent_slug = 'edit.php?post_type=elemento_trasparenza';
    $menu_slug   = 'edit.php?post_type=incarico_dirig';

    if (current_user_can('edit_incarichi_dirig')) {
        add_submenu_page(
            $parent_slug,
            __('Incarico dirigenziale', 'design_comuni_italia'),
            __('Incarichi dirigenziali', 'design_comuni_italia'),
            'edit_incarichi_dirig',
            $menu_slug
        );
    }
}

// Aggiunge la voce "Aggiungi incarico_dirigenziale" nella Admin Bar sotto "+ Nuovo"
add_action('admin_bar_menu', 'dci_add_admin_bar_new_incarico_dirigenziale', 999);
function dci_add_admin_bar_new_incarico_dirigenziale($wp_admin_bar)
{
    if (!dci_incarico_dirigenziale_custom_template_enabled()) {
        return;
    }

    // Controlla se l'utente ha i permessi
    if (!current_user_can('create_incarichi_dirig')) {
        return; // Non aggiungere la voce
    }

    // Aggiunge la voce sotto il menu "+ Nuovo" (ID: new-content)
    $wp_admin_bar->add_node(array(
        'id'     => 'new-incarico-dirig',
        'title'  => __('Incarico dirigenziale', 'design_comuni_italia'),
        'href'   => admin_url('post-new.php?post_type=incarico_dirig'),
        'parent' => 'new-content' // Sotto "+ Nuovo"
    ));
}





/**
 * Messaggio informativo sotto il titolo nel backend
 */
add_action('edit_form_after_title', 'dci_incarico_dirigenziale_add_content_after_title');
function dci_incarico_dirigenziale_add_content_after_title($post)
{
    if ($post->post_type === 'incarico_dirig') {
        echo '<p class="description"><em>Inserisci un titolo chiaro che identifichi il titolare e la tipologia di incarico, ad esempio: <strong>Mario Rossi – Responsabile dell’Area Tecnica</strong>. Se lasci il campo vuoto, il titolo verrà generato automaticamente utilizzando il nome e il cognome del titolare.</em></p>';
    }
}

/**
 * CMB2 Metaboxes per il CPT "incarico_dirigenziale"
 */
add_action('cmb2_init', 'dci_add_incarico_dirigenziale_metaboxes');
function dci_add_incarico_dirigenziale_metaboxes()
{
    $prefix = '_dci_incarico_dirigenziale_';

   // Metabox: Apertura
    $cmb_main = new_cmb2_box(array(
        'id'           => $prefix . 'box_main',
        'title'        => __('Informazioni sull’incarico dirigenziale', 'design_comuni_italia'),
        'object_types' => array('incarico_dirig'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    // Descrizione introduttiva del metabox
    $cmb_main->add_field(array(
        'id'   => $prefix . 'box_apertura_description',
        'type' => 'title',
        'name' => __('Indicazioni per la compilazione', 'design_comuni_italia'),
        'desc' => __('Compila i campi con le informazioni relative al titolare e all’incarico dirigenziale. I dati inseriti saranno pubblicati nella sezione selezionata dell’Amministrazione Trasparente.', 'design_comuni_italia'),
    ));

    $cmb_main->add_field(
            array(
                'id'      => $prefix . 'sezione_pubblicazione',
                'name'    => __('Sezione di pubblicazione *', 'design_comuni_italia'),
                'desc'    => __('Seleziona la sezione dell’Amministrazione Trasparente nella quale pubblicare l’incarico dirigenziale.', 'design_comuni_italia'),
                'type'    => 'select',
                'options' => array('' => __('Seleziona la sezione di pubblicazione', 'design_comuni_italia')) + dci_incarico_dirigenziale_sections(),
                'attributes' => array('required' => 'required'),
            )
        );

    
    // Stato dell’incarico
    $cmb_main->add_field(array(
        'id'                => $prefix . 'tipo_stato_incarico_dirigenziale',
        'name'              => __('Stato dell’incarico *', 'design_comuni_italia'),
        'desc'              => __('Indica lo stato attuale dell’incarico dirigenziale.', 'design_comuni_italia'),
        'type'              => 'select',
        'options'           => array(
            ''          => __('Seleziona lo stato dell’incarico', 'design_comuni_italia'),
            'in_corso'  => __('In corso', 'design_comuni_italia'),
            'cessato'   => __('Cessato', 'design_comuni_italia'),
            'revocato'  => __('Revocato', 'design_comuni_italia'),
            'concluso'  => __('Concluso', 'design_comuni_italia'),
        ),
        'attributes'        => array('required' => 'required')
    ));




    // Nome del titolare
    $cmb_main->add_field(array(
        'id'          => $prefix . 'nome_titolare',
        'name'        => __('Nome del titolare *', 'design_comuni_italia'),
        'desc'        => __('Inserisci il nome della persona alla quale è stato conferito l’incarico.', 'design_comuni_italia'),
        'type'        => 'text',
        'attributes'  => array('required' => 'required'),
    ));


    // Cognome del titolare
    $cmb_main->add_field(array(
        'id'          => $prefix . 'cognome_titolare',
        'name'        => __('Cognome del titolare', 'design_comuni_italia'),
        'desc'        => __('Inserisci il cognome della persona alla quale è stato conferito l’incarico.', 'design_comuni_italia'),
        'type'        => 'text',
        // 'attributes'  => array('required' => 'required'),
    ));

     // Mansione del titolare
    $cmb_main->add_field(array(
        'id'          => $prefix . 'mansione_titolare',
        'name'        => __('Denominazione dell’incarico', 'design_comuni_italia'),
        'desc'        => __('Inserisci la denominazione completa dell’incarico o della funzione dirigenziale attribuita al titolare.', 'design_comuni_italia'),
        'type'        => 'text',
        // 'attributes'  => array('required' => 'required'),
    ));

    $cmb_main->add_field(
        array(
            'id'         => $prefix . 'struttura',
            'name'       => __('Struttura organizzativa o ufficio', 'design_comuni_italia'),
            'desc'       => __('Indica la struttura organizzativa, l’area, il settore o l’ufficio al quale è riferito l’incarico.', 'design_comuni_italia'),
            'type'       => 'text',
            'attributes' => array('maxlength' => 256),
        )
    );

    $cmb_main->add_field(
        array(
            'id'      => $prefix . 'gratuito',
            'name'    => __('Incarico a titolo gratuito', 'design_comuni_italia'),
            'desc'    => __('Indica se l’incarico è svolto senza la corresponsione di alcun compenso.', 'design_comuni_italia'),
            'type'    => 'select',
            'default' => 'no',
            'options' => array(
                'no' => __('No', 'design_comuni_italia'),
                'si' => __('Sì', 'design_comuni_italia'),
            ),
        )
    );

    // Compenso

    $cmb_main->add_field(
        array(
            'id'         => $prefix . 'compenso',
            'name'       => __('Compenso lordo annuo', 'design_comuni_italia'),
            'desc'       => __('Indica l’ammontare complessivo del compenso lordo annuo previsto per l’incarico dirigenziale, espresso in euro. Se l’incarico è a titolo gratuito, lasciare il campo vuoto.', 'design_comuni_italia'),
            'type'       => 'text',
        )
    );

    // Durata
    $cmb_main->add_field(array(
        'id'          => $prefix . 'durata',
        'name'        => __('Durata dell’incarico', 'design_comuni_italia'),
        'desc'        => __('Indica la durata complessiva dell’incarico, ad esempio “3 anni” oppure “2 anni e 6 mesi”.', 'design_comuni_italia'),
        'type'        => 'text',
    ));   
    

    // Curriculum vitae
    $cmb_main->add_field(
        array(
            'id'      => $prefix . 'curriculum',
            'name'    => __('Curriculum vitae *', 'design_comuni_italia'),
            'desc'    => __('Carica il curriculum vitae in formato PDF, verificando che non contenga dati personali non pertinenti alla pubblicazione.', 'design_comuni_italia'),
            'type'    => 'file',
            'options' => array('url' => false),
            'query_args' => array('type' => array('application/pdf')),
        )
    );

    // Metabox: conferimento
    $cmb_conferimento = new_cmb2_box(array(
        'id'           => $prefix . 'box_conferimento',
        'title'        => __('Conferimento', 'design_comuni_italia'),
        'object_types' => array('incarico_dirig'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    // Data di conferimento

    $cmb_conferimento->add_field(array(
        'id'          => $prefix . 'data_conferimento',
        'name'        => __('Data di conferimento', 'design_comuni_italia'),
        'desc'        => __('Indica la data in cui l’incarico è stato formalmente conferito.', 'design_comuni_italia'),
        'type'        => 'text_date_timestamp',
        'date_format' => 'd/m/Y',
    ));

    // Data di scadenza
    $cmb_conferimento->add_field(array(
        'id'          => $prefix . 'data_scadenza',
        'name'        => __('Data di scadenza', 'design_comuni_italia'),
        'desc'        => __('Indica la data prevista di conclusione o scadenza dell’incarico.', 'design_comuni_italia'),
        'type'        => 'text_date_timestamp',
        'date_format' => 'd/m/Y',
    ));

    // Documenti allegati
    $cmb_conferimento->add_field(array(
        'id'   => $prefix . 'allegati',
        'name' => __('Atti e documenti relativi all’incarico', 'design_comuni_italia'),
        'desc' => __('Carica l’atto di conferimento e gli eventuali ulteriori documenti relativi all’incarico dirigenziale.', 'design_comuni_italia'),
        'type' => 'file_list',
        ));





    // Metabox ulteriori informazioni
    $cmb_more = new_cmb2_box(array(
        'id'           => $prefix . 'box_more',
        'title'        => __('Informazioni aggiuntive', 'design_comuni_italia'),
        'object_types' => array('incarico_dirig'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    
   $cmb_more->add_field(
        array(
            'id'      => $prefix . 'allegati_aggiuntivi',
            'name'    => __('Allegati aggiuntivi', 'design_comuni_italia'),
            'type'    => 'file_list',
            'query_args' => array('type' => array('application/pdf')),
        )
    );

    // Campo: More info
    $cmb_more->add_field(array(
        'id'          => $prefix . 'more_info',
        'name'        => __('Note e informazioni aggiuntive', 'design_comuni_italia'),
        'desc'        => __('Inserisci eventuali informazioni integrative utili a descrivere l’incarico, i riferimenti normativi o altri dati rilevanti ai fini della trasparenza.', 'design_comuni_italia'),
        'type' => 'wysiwyg',
        'options' => array(
            'textarea_rows' => 8,
            'teeny'         => false,
            'media_buttons' => false,
        ),
    )); 


   
}

/**
 * Imposta automaticamente titolo e contenuto del post utilizzando il nominativo del titolare.
 */
add_filter('wp_insert_post_data', 'dci_incarico_dirigenziale_set_post_content', 99, 1);
function dci_incarico_dirigenziale_set_post_content($data)
{
    if (($data['post_type'] ?? '') === 'incarico_dirig') {
        $nome_key = '_dci_incarico_dirigenziale_nome_titolare';
        $cognome_key = '_dci_incarico_dirigenziale_cognome_titolare';

        // Non alterare titolo o contenuto durante Quick Edit, REST o salvataggi automatici.
        if (!isset($_POST[$nome_key]) && !isset($_POST[$cognome_key])) {
            return $data;
        }

        $nome = isset($_POST[$nome_key])
            ? sanitize_text_field(wp_unslash($_POST[$nome_key]))
            : '';
        $cognome = isset($_POST[$cognome_key])
            ? sanitize_text_field(wp_unslash($_POST[$cognome_key]))
            : '';
        $nominativo = trim($nome . ' ' . $cognome);

        if ($nominativo !== '' && trim((string) ($data['post_title'] ?? '')) === '') {
            $data['post_title'] = $nominativo;
        }
        $data['post_content'] = $nominativo;
    }

    return $data;
}

if (!function_exists('dci_incarico_dirigenziale_sections')) {
    function dci_incarico_dirigenziale_sections()
    {
        return array(
            'vertice'   => __('Titolari di incarichi dirigenziali amministrativi di vertice', 'design_comuni_italia'),
            'dirigenti' => __('Incarichi dirigenziali a qualsiasi titolo conferiti', 'design_comuni_italia'),
        );
    }
}
