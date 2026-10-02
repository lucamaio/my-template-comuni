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

if (!function_exists('dci_incarico_dirigenziale_cessati_automation_enabled')) {
    /**
     * Indica se gli incarichi cessati devono essere instradati automaticamente.
     *
     * Il valore predefinito e' attivo per mantenere operativa l'automazione
     * anche sulle installazioni che non hanno ancora salvato la nuova opzione.
     *
     * @return bool
     */
    function dci_incarico_dirigenziale_cessati_automation_enabled()
    {
        $option_value = function_exists('dci_get_option')
            ? dci_get_option(
                'ck_incarichidirigenziali_cessati_automatico',
                'Trasparenza',
                'true'
            )
            : 'true';

        if (null === $option_value || (is_string($option_value) && '' === trim($option_value))) {
            return true;
        }

        return function_exists('dci_is_truthy_option_value')
            ? dci_is_truthy_option_value($option_value)
            : in_array(strtolower((string) $option_value), array('1', 'true', 'yes', 'on'), true);
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
    // Preserve metabox IDs and field keys to retain saved data and existing integrations.

    $cmb = new_cmb2_box(array(
        'id' => '_dci_incarico_dirigenziale_box_main',
        'title' => __('1. Titolare e collocazione organizzativa', 'design_comuni_italia'),
        'object_types' => array('incarico_dirig'),
        'context' => 'normal',
        'priority' => 'high',
    ));

    $cmb->add_field(array(
        'id' => '_dci_incarico_dirigenziale_box_apertura_description',
        'type' => 'title',
        'name' => __('Indicazioni per la compilazione', 'design_comuni_italia'),
        'desc' => __('Compilare nell\'ordine i dati del titolare, le informazioni sull\'incarico e i documenti. I campi contrassegnati con * sono obbligatori. Le informazioni inserite, comprese le note, sono destinate alla pubblicazione nella sezione selezionata di Amministrazione Trasparente.', 'design_comuni_italia'),
    ));

    $cmb->add_field(array(
        'id' => '_dci_incarico_dirigenziale_sezione_pubblicazione',
        'name' => __('Sezione di pubblicazione *', 'design_comuni_italia'),
        'desc' => __('Selezionare la sezione di destinazione: incarichi amministrativi di vertice, altri incarichi dirigenziali o dirigenti cessati. Se lo spostamento automatico dei cessati risulta attivo nelle opzioni del tema, al salvataggio la destinazione viene aggiornata in base allo stato dell\'incarico.', 'design_comuni_italia'),
        'type' => 'select',
        'options' => array('' => __('Seleziona la sezione di pubblicazione', 'design_comuni_italia')) + dci_incarico_dirigenziale_sections(),
        'attributes' => array('required' => 'required'),
    ));

    $cmb->add_field(array(
        'id' => '_dci_incarico_dirigenziale_nome_titolare',
        'name' => __('Nome del titolare *', 'design_comuni_italia'),
        'desc' => __('Indicare il nome del titolare, senza titoli professionali o qualifiche.', 'design_comuni_italia'),
        'type' => 'text',
        'attributes' => array('required' => 'required'),
    ));

    $cmb->add_field(array(
        'id' => '_dci_incarico_dirigenziale_cognome_titolare',
        'name' => __('Cognome del titolare', 'design_comuni_italia'),
        'desc' => __('Indicare il cognome del titolare. Nome e cognome identificano la persona nella scheda pubblica.', 'design_comuni_italia'),
        'type' => 'text',
    ));

    $cmb->add_field(array(
        'id' => '_dci_incarico_dirigenziale_mansione_titolare',
        'name' => __('Denominazione dell\'incarico / Mansione', 'design_comuni_italia'),
        'desc' => __('Indicare la funzione attribuita al titolare, ad esempio: Responsabile dell\'Area Tecnica. Il dato viene mostrato come Mansione nella card pubblica.', 'design_comuni_italia'),
        'type' => 'text',
    ));

    $cmb->add_field(array(
        'id' => '_dci_incarico_dirigenziale_struttura',
        'name' => __('Struttura di appartenenza', 'design_comuni_italia'),
        'desc' => __('Indicare l\'area, il settore, il servizio o l\'ufficio presso cui viene svolto l\'incarico, utilizzando la denominazione ufficiale dell\'organizzazione.', 'design_comuni_italia'),
        'type' => 'text',
        'attributes' => array('maxlength' => 256),
    ));

    $cmb = new_cmb2_box(array(
        'id' => '_dci_incarico_dirigenziale_box_conferimento',
        'title' => __('2. Stato, durata e trattamento economico', 'design_comuni_italia'),
        'object_types' => array('incarico_dirig'),
        'context' => 'normal',
        'priority' => 'high',
    ));

    $cmb->add_field(array(
        'id' => '_dci_incarico_dirigenziale_tipo_stato_incarico_dirigenziale',
        'name' => __('Stato dell\'incarico *', 'design_comuni_italia'),
        'desc' => __('Selezionare lo stato risultante dagli atti: In corso, Cessato, Revocato o Concluso. Se lo spostamento automatico risulta attivo, solo lo stato Cessato trasferisce la scheda in Dirigenti cessati; gli altri stati la mantengono o la riportano nella sezione ordinaria.', 'design_comuni_italia'),
        'type' => 'select',
        'options' => array('' => __('Seleziona lo stato dell’incarico', 'design_comuni_italia'), 'in_corso' => __('In corso', 'design_comuni_italia'), 'cessato' => __('Cessato', 'design_comuni_italia'), 'revocato' => __('Revocato', 'design_comuni_italia'), 'concluso' => __('Concluso', 'design_comuni_italia')),
        'attributes' => array('required' => 'required'),
    ));

    $cmb->add_field(array(
        'id' => '_dci_incarico_dirigenziale_data_conferimento',
        'name' => __('Data di conferimento', 'design_comuni_italia'),
        'desc' => __('Indicare la data del conferimento formale dell\'incarico, come riportata nel relativo atto, nel formato giorno/mese/anno.', 'design_comuni_italia'),
        'type' => 'text_date_timestamp',
        'date_format' => 'd/m/Y',
    ));

    $cmb->add_field(array(
        'id' => '_dci_incarico_dirigenziale_data_scadenza',
        'name' => __('Data di scadenza prevista', 'design_comuni_italia'),
        'desc' => __('Indicare la data prevista di conclusione dell\'incarico nel formato giorno/mese/anno e aggiornarla in caso di proroga. La scadenza prevista non attesta, da sola, la cessazione effettiva dell\'incarico.', 'design_comuni_italia'),
        'type' => 'text_date_timestamp',
        'date_format' => 'd/m/Y',
    ));

    $cmb->add_field(array(
        'id' => '_dci_incarico_dirigenziale_durata',
        'name' => __('Durata dell\'incarico', 'design_comuni_italia'),
        'desc' => __('Indicare la durata stabilita nell\'atto, ad esempio: 3 anni oppure 2 anni e 6 mesi. Verificare la coerenza con le date e con eventuali proroghe.', 'design_comuni_italia'),
        'type' => 'text',
    ));

    $cmb->add_field(array(
        'id' => '_dci_incarico_dirigenziale_gratuito',
        'name' => __('Incarico a titolo gratuito', 'design_comuni_italia'),
        'desc' => __('Selezionare Si se non viene corrisposto alcun compenso; selezionare No per un incarico retribuito.', 'design_comuni_italia'),
        'type' => 'select',
        'default' => 'no',
        'options' => array('no' => __('No', 'design_comuni_italia'), 'si' => __('Sì', 'design_comuni_italia')),
    ));

    $cmb->add_field(array(
        'id' => '_dci_incarico_dirigenziale_compenso',
        'name' => __('Compenso lordo annuo (euro)', 'design_comuni_italia'),
        'desc' => __('Indicare il compenso lordo annuo previsto per l\'incarico, espresso in euro, ad esempio: 100.000,00 euro. Per gli incarichi a titolo gratuito lasciare il campo vuoto.', 'design_comuni_italia'),
        'type' => 'text',
    ));

    $cmb = new_cmb2_box(array(
        'id' => '_dci_incarico_dirigenziale_box_more',
        'title' => __('3. Documenti e informazioni integrative', 'design_comuni_italia'),
        'object_types' => array('incarico_dirig'),
        'context' => 'normal',
        'priority' => 'high',
    ));

    $cmb->add_field(array(
        'id' => '_dci_incarico_dirigenziale_curriculum',
        'name' => __('Curriculum vitae', 'design_comuni_italia'),
        'desc' => __('Caricare il curriculum aggiornato, preferibilmente in PDF accessibile, privo di dati personali non pertinenti alla pubblicazione. Sostituire il documento quando viene aggiornato.', 'design_comuni_italia'),
        'type' => 'file',
        'options' => array('url' => false),
    ));

    $cmb->add_field(array(
        'id' => '_dci_incarico_dirigenziale_allegati',
        'name' => __('Atti e documenti dell\'incarico', 'design_comuni_italia'),
        'desc' => __('Caricare l\'atto di conferimento e gli eventuali atti di proroga, revoca o cessazione. Assegnare ai documenti titoli riconoscibili, indicando tipologia, numero e data dell\'atto.', 'design_comuni_italia'),
        'type' => 'file_list',
    ));

    $cmb->add_field(array(
        'id' => '_dci_incarico_dirigenziale_allegati_aggiuntivi',
        'name' => __('Allegati aggiuntivi', 'design_comuni_italia'),
        'type' => 'file_list',
        'desc' => __('Caricare gli ulteriori documenti destinati alla pubblicazione che non rientrano tra gli atti dell\'incarico. Utilizzare titoli descrittivi ed evitare duplicati dei documenti inseriti sopra.', 'design_comuni_italia'),
    ));

    $cmb->add_field(array(
        'id' => '_dci_incarico_dirigenziale_more_info',
        'name' => __('Note e informazioni integrative per la pubblicazione', 'design_comuni_italia'),
        'desc' => __('Inserire chiarimenti sull\'incarico, riferimenti agli atti o altre informazioni utili alla consultazione. Il contenuto viene pubblicato sul portale: non utilizzare questo campo per annotazioni interne.', 'design_comuni_italia'),
        'type' => 'wysiwyg',
        'options' => array('textarea_rows' => 8, 'teeny' => false, 'media_buttons' => false),
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
            'cessati'   => __('Dirigenti cessati', 'design_comuni_italia'),
        );
    }
}

/**
 * Sincronizza la sezione di pubblicazione con lo stato dell'incarico.
 *
 * L'hook generico con priorita' 120 viene eseguito dopo il salvataggio CMB2 e
 * dopo l'eventuale sezione contestuale. Prima dello spostamento viene conservata
 * la sezione originaria, utilizzata se l'incarico torna successivamente attivo.
 *
 * @param int     $post_id ID dell'incarico.
 * @param WP_Post $post Oggetto del post.
 * @param bool    $update Indica se si tratta di un aggiornamento.
 * @return void
 */
function dci_incarico_dirigenziale_sync_cessati_section($post_id, $post, $update)
{
    unset($update);

    if (
        !dci_incarico_dirigenziale_cessati_automation_enabled()
        || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        || wp_is_post_autosave($post_id)
        || wp_is_post_revision($post_id)
        || !$post instanceof WP_Post
        || 'incarico_dirig' !== $post->post_type
        || 'auto-draft' === $post->post_status
    ) {
        return;
    }

    $prefix = '_dci_incarico_dirigenziale_';
    $section_key = $prefix . 'sezione_pubblicazione';
    $previous_section_key = $prefix . 'sezione_pre_cessazione';
    $status = sanitize_key((string) get_post_meta(
        $post_id,
        $prefix . 'tipo_stato_incarico_dirigenziale',
        true
    ));
    $current_section = sanitize_key((string) get_post_meta($post_id, $section_key, true));
    $regular_sections = array('vertice', 'dirigenti');

    if ('cessato' === $status) {
        if (in_array($current_section, $regular_sections, true)) {
            update_post_meta($post_id, $previous_section_key, $current_section);
        }

        if ('cessati' !== $current_section) {
            update_post_meta($post_id, $section_key, 'cessati');
        }

        return;
    }

    if ('cessati' === $current_section) {
        $previous_section = sanitize_key((string) get_post_meta(
            $post_id,
            $previous_section_key,
            true
        ));
        $restored_section = in_array($previous_section, $regular_sections, true)
            ? $previous_section
            : 'dirigenti';

        update_post_meta($post_id, $section_key, $restored_section);
    }

    delete_post_meta($post_id, $previous_section_key);
}
add_action('save_post', 'dci_incarico_dirigenziale_sync_cessati_section', 120, 3);
