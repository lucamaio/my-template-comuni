<?php
/** Condizione condivisa tra elenco, storico e anni disponibili della sezione. */
function dci_titolare_incarico_archive_sql()
{
    global $wpdb;

    $now = current_datetime();

    /*
     * Soglia basata sulla data di pubblicazione.
     * Utilizzata solamente come fallback quando non sono disponibili
     * date utili relative all'incarico.
     *
     * Esempio nel 2026: 2023-01-01 00:00:00
     */
    $publication_start = ((int) $now->format('Y') - 3) . '-01-01 00:00:00';

    /*
     * Data esatta di tre anni fa.
     * Il giorno del terzo anniversario rimane ancora pubblico.
     */
    $cessation_cutoff = $now
        ->setTime(0, 0)
        ->modify('-3 years')
        ->getTimestamp();

    return $wpdb->prepare(
        "(
            /*
             * CASO 1
             * Esiste una data di cessazione valida.
             *
             * In questo caso è la data di cessazione a determinare
             * la permanenza o meno nella sezione pubblica,
             * indipendentemente dalla data di pubblicazione del post.
             */
            EXISTS (
                SELECT 1
                FROM {$wpdb->postmeta} AS dci_end
                WHERE dci_end.post_id = {$wpdb->posts}.ID
                  AND dci_end.meta_key = '_dci_titolare_incarico_data_fine'
                  AND dci_end.meta_value REGEXP '^[0-9]+$'
                  AND CAST(dci_end.meta_value AS UNSIGNED) > 0
                  AND CAST(dci_end.meta_value AS UNSIGNED) < %d
            )

            OR

            /*
             * CASO 2
             * Non esiste una data di cessazione valida
             * e non esiste nemmeno una data di inizio valida.
             *
             * Non abbiamo quindi informazioni temporali
             * sull'incarico e utilizziamo la data di pubblicazione
             * come criterio di fallback.
             */
            (
                {$wpdb->posts}.post_date < %s

                AND NOT EXISTS (
                    SELECT 1
                    FROM {$wpdb->postmeta} AS dci_end_missing
                    WHERE dci_end_missing.post_id = {$wpdb->posts}.ID
                      AND dci_end_missing.meta_key = '_dci_titolare_incarico_data_fine'
                      AND dci_end_missing.meta_value REGEXP '^[0-9]+$'
                      AND CAST(dci_end_missing.meta_value AS UNSIGNED) > 0
                )

                AND NOT EXISTS (
                    SELECT 1
                    FROM {$wpdb->postmeta} AS dci_start_missing
                    WHERE dci_start_missing.post_id = {$wpdb->posts}.ID
                      AND dci_start_missing.meta_key = '_dci_titolare_incarico_data_inizio'
                      AND dci_start_missing.meta_value REGEXP '^[0-9]+$'
                      AND CAST(dci_start_missing.meta_value AS UNSIGNED) > 0
                )
            )
        )",
        $cessation_cutoff,
        $publication_start
    );
}

add_filter('posts_where', 'dci_titolare_incarico_visibility_where', 10, 2);
function dci_titolare_incarico_visibility_where($where, $query)
{
    // La ricerca globale comprende più tipi di contenuto: limita solo i titolari.
    if ($query->get('dci_at_global_search') && !dci_user_can_view_trasparenza_archive()) {
        global $wpdb;
        $where .= " AND ({$wpdb->posts}.post_type <> 'titolare_incarico' OR NOT "
            . dci_titolare_incarico_archive_sql() . ')';
    }

    if ($query->get('post_type') !== 'titolare_incarico') {
        return $where;
    }
    $visibility = $query->get('dci_titolari_visibility');
    if (!in_array($visibility, ['public', 'archive'], true)) {
        return $where;
    }
    if ($visibility === 'archive' && !dci_user_can_view_trasparenza_archive()) {
        return $where . ' AND 1=0';
    }
    return $where . ' AND ' . ($visibility === 'public' ? 'NOT ' : '') . dci_titolare_incarico_archive_sql();
}

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

    // Sezione Apertura - Dati principali del titolare incarico
    $cmb_apertura = new_cmb2_box(array(
        'id'           => $prefix . 'box_apertura',
        'title'        => __('Dati principali del titolare dell’incarico', 'design_comuni_italia'),
        'object_types' => array('titolare_incarico'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    // Descizione informativa per la sezione
    $cmb_apertura->add_field(array(
        'id'   => $prefix . 'descrizione_dati_incarico',
        'name' => __('Informazioni principali dell’incarico conferito', 'design_comuni_italia'),
        'desc' => __(
            '<p>
                Inserisci i principali dati relativi all’incarico conferito, indicando in modo completo l’oggetto, il compenso, l’atto di conferimento, le date di inizio e cessazione e le ulteriori informazioni richieste.
            </p>
            <p>
                <strong>Presta particolare attenzione alle date dell’incarico:</strong> queste informazioni vengono utilizzate per determinare correttamente il periodo di pubblicazione e la visibilità del contenuto nella sezione Amministrazione Trasparente.
            </p>
            <p class="mb-0">
                <strong>In assenza delle date dell’incarico,</strong> ai fini della gestione della visibilità verrà utilizzata come riferimento la data di pubblicazione del contenuto.
            </p>',
            'design_comuni_italia'
        ),
        'type' => 'title',
    ));

    // $cmb_apertura->add_field(array(
    //     'id'          => $prefix . 'soggetto',
    //     'name'        => __('Soggetto *', 'design_comuni_italia'),
    //     'desc'        => __('Inserisci il nominativo del titolare dell’incarico.', 'design_comuni_italia'),
    //     'type'        => 'text',
    //     'attributes'  => array('required' => 'required'),
    // ));
    
    // Informazioni preliminari sul titolare dell’incarico
    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'oggetto',
        'name'        => __("Oggetto dell'incarico *", 'design_comuni_italia'),
        'desc'        => __("Descrivi sinteticamente l’oggetto dell’incarico conferito.", 'design_comuni_italia'),
        'type'        => 'wysiwyg',
        // 'attributes'  => array('required' => 'required'),
        'options'     => array(
            'textarea_rows' => 4,
            'teeny'         => false,
        ),
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'compenso',
        'name'        => __('Compenso *', 'design_comuni_italia'),
        'desc'        => __('Inserisci l’importo del compenso previsto per l’incarico.', 'design_comuni_italia'),
        'type'        => 'text',
        // 'attributes'  => array('required' => 'required'),
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'atto_conferimento_incarico',
        'name'        => __('Atto di conferimento *', 'design_comuni_italia'),
        'desc'        => __('Inserisci il riferimento o il nome dell’atto di conferimento dell’incarico.', 'design_comuni_italia'),
        'type'        => 'text',
        // 'attributes'  => array('required' => 'required'),
    ));

    // Date e durata dell’incarico

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'data_inizio',
        'name'        => __('Data di inizio *', 'design_comuni_italia'),
        'desc'        => __(
            '<p class="mb-2">Seleziona la data di inizio dell’incarico.</p>
            <p style="font-weight: bold;">
                Compila correttamente questo campo, in quanto la data di inizio contribuisce a determinare il periodo temporale effettivo dell’incarico e la corretta gestione della sua visibilità nella sezione Amministrazione Trasparente.
            </p>',
            'design_comuni_italia'
        ),
        'type'        => 'text_date_timestamp',
        'date_format' => 'd-m-Y',
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'data_fine',
        'name'        => __('Data di fine *', 'design_comuni_italia'),
        'desc'        => __(
            '<p class="mb-2">Seleziona la data di cessazione dell’incarico.</p>
            <p style="font-weight: bold;">
                Compila correttamente questo campo, in quanto la data di cessazione viene utilizzata per determinare il periodo di pubblicazione dell’incarico e consentire, una volta decorso il termine di visibilità previsto dalla normativa, la sua esclusione dalla consultazione pubblica ordinaria.
            </p>',
            'design_comuni_italia'
        ),
        'type'        => 'text_date_timestamp',
        'date_format' => 'd-m-Y',
    ));

     $cmb_apertura->add_field(array(
        'id'          => $prefix . 'durata',
        'name'        => __('Durata', 'design_comuni_italia'),
        'desc'        => __('Inserisci la durata prevista per l’incarico. Esempio: 1 anno.', 'design_comuni_italia'),
        'type'        => 'text',
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



    // Sezione documenti e allegati del incarico
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

    // Sezione ulteriori informazioni
    $cmb_moreInfo = new_cmb2_box(array(
        'id'               => $prefix . 'moreInfo_box',
        'title'            => __('Ulteriori Informazioni', 'design_comuni_italia'),
        'object_types'     => array('titolare_incarico'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    $cmb_moreInfo->add_field(array(
        'id' => $prefix . 'more_info',
        'name'        => __('Ulteriori Informazioni', 'design_comuni_italia'),
        'desc' => __('Inserisci eventuale informazione aggiuntiva riguardo il titolare dell’incarico.', 'design_comuni_italia'),
        'type' => 'wysiwyg',
        'options' => array(
            'textarea_rows' => 8,
            'teeny' => false,
        ),
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









