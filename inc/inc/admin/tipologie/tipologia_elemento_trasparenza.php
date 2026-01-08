<?php

/**
 * Definisce post type Elemento Trasparenza
 */
add_action('init', 'dci_register_post_type_elemento_trasparenza');
function dci_register_post_type_elemento_trasparenza()
{
    $labels = array(
        'name'                  => _x('Amministrazione Trasparente', 'Post Type General Name', 'design_comuni_italia'),
        'singular_name'         => _x('Amministrazione Trasparente', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new'               => _x('Aggiungi un Elemento Trasparenza', 'design_comuni_italia'),
        'add_new_item'          => _x('Aggiungi un Elemento Trasparenza', 'design_comuni_italia'), 
        'edit_item'             => _x('Modifica l\'Elemento Trasparenza', 'design_comuni_italia'),
        'new_item'              => __('Nuovo Elemento Trasparenza', 'design_comuni_italia'),
        'menu_name'             => __('Amministrazione Trasparente', 'design_comuni_italia'),
    );

    $args = array(
        'label'                 => __('Elemento Trasparenza', 'design_comuni_italia'),
        'labels'                => $labels,
        'supports'              => array('title', 'author'),
        'taxonomies'            => array('tipologia'),
        'hierarchical'          => false,
        'public'                => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-archive',
        'has_archive'           => false,
        'capability_type'       => array('elemento_trasparenza', 'elementi_trasparenza'),
        'map_meta_cap'          => true,
        'capabilities' => array(
            'edit_post'             => 'edit_elemento_trasparenza',
            'read_post'             => 'read_elemento_trasparenza',
            'delete_post'           => 'delete_elemento_trasparenza',
            'edit_posts'            => 'edit_elementi_trasparenza',
            'edit_others_posts'     => 'edit_others_elementi_trasparenza',
            'publish_posts'         => 'publish_elementi_trasparenza',
            'read_private_posts'    => 'read_private_elementi_trasparenza',
            'delete_posts'          => 'delete_elementi_trasparenza',
            'delete_private_posts'  => 'delete_private_elementi_trasparenza',
            'delete_published_posts' => 'delete_published_elementi_trasparenza',
            'delete_others_posts' => 'delete_others_elementi_trasparenza',
            'edit_private_posts' => 'edit_private_elementi_trasparenza',
            'edit_published_posts' => 'edit_published_elementi_trasparenza',
            'create_posts'          => 'create_elementi_trasparenza'
        ),
        'description'           => __('Struttura delle informazioni relative utili a presentare un Elemento Trasparenza', 'design_comuni_italia'),
    );

    register_post_type('elemento_trasparenza', $args);


    remove_post_type_support('elemento_trasparenza', 'editor');
}





add_action('edit_form_after_title', 'dci_elemento_trasparenza_add_content_after_title');
function dci_elemento_trasparenza_add_content_after_title($post)
{
    if ($post->post_type !== 'elemento_trasparenza') {
        return;
    }

    echo "<span><i>Il <b>Titolo</b> è il <b>Nome dell'elemento dell'amministrazione trasparente</b>.</i></span><br><br>";

    ?>
    <style>
        .dci-section-box {
            background: #f0f6fc;
            border-left: 4px solid #0073aa;
            padding: 20px;
            margin: 30px 0;
            border-radius: 6px;
            box-shadow: inset 0 -1px 0 rgba(0,0,0,0.05);
        }
        .dci-section-box h2 {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 20px;
            color: #1d2327;
        }
        .dci-menu-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .dci-menu-btn {
            display: inline-block;
            padding: 14px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            background: #fff;
            border: 1px solid #ddd;
            color: #1d2327;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.2s ease;
            min-width: 240px;
        }
        .dci-menu-btn:hover {
            background: #fff;
            border-color: #0073aa;
            color: #0073aa;
            box-shadow: 0 4px 10px rgba(0,115,170,0.15);
        }
        .dci-menu-btn b {
            display: block;
            font-weight: 700;
            margin-top: 5px;
            color: #0073aa;
        }
    </style>

    <div class="dci-section-box">
        <h2>Categorie personalizzate</h2>
        <div class="dci-menu-container">

            <?php if (dci_get_option("ck_incarichieautorizzazioniaidipendenti", "Trasparenza") !== 'false' && dci_get_option("ck_incarichieautorizzazioniaidipendenti", "Trasparenza") !== ''): ?>
                <a href="edit.php?post_type=incarichi_dip" class="dci-menu-btn">
                    Personale <b>Incarichi conferiti e autorizzati</b>
                </a>
            <?php endif; ?>

            <?php if (dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") !== 'false' && dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") !== ''): ?>
                <a href="edit.php?post_type=bando" class="dci-menu-btn">
                    Bandi di Gara e contratti <b>Contratti Pubblici</b>
                </a>
            <?php endif; ?>

            <?php if (dci_get_option("ck_attidiconcessione", "Trasparenza") !== 'false' && dci_get_option("ck_attidiconcessione", "Trasparenza") !== ''): ?>
                <a href="edit.php?post_type=atto_concessione" class="dci-menu-btn">
                    Sovvenzioni, contributi <b>Atti di concessione</b>
                </a>
            <?php endif; ?>

            <?php if (dci_get_option("ck_titolariIncarichiCollaborazioneConsulenzaTemplatePersonalizzato", "Trasparenza") !== 'false' && dci_get_option("ck_titolariincarico", "Trasparenza") !== ''): ?>
                <a href="edit.php?post_type=titolare_incarico" class="dci-menu-btn">
                    Titolari di incarichi <b>Consulenze e Collaborazioni</b>
                </a>
            <?php endif; ?>

        </div>
    </div>
    <?php
}





// Aggiungi la nuova voce di sottomenu per la pagina "Multi-Post"
add_action('admin_menu', 'dci_add_transparency_multipost_page');

function dci_add_transparency_multipost_page() {
    // Aggiungi una sottovoce sotto "Amministrazione Trasparente"
    add_submenu_page(
        'edit.php?post_type=elemento_trasparenza',
        __('Aggiungi Multi-Elemento Trasparenza', 'design_comuni_italia'), 
        __('Multi-Elemento', 'design_comuni_italia'),
        'create_elementi_trasparenza',              
        'dci_transparency_multipost_page',              
        'dci_render_transparency_multipost_page',
        7
    );
}





/**
 * Funzione di callback per renderizzare la pagina di amministrazione "Multi-Post Amministrazione Trasparente".
 */
function dci_render_transparency_multipost_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <p><?php _e('Questa pagina ti permette di creare rapidamente più Elementi di Amministrazione Trasparente.', 'design_comuni_italia'); ?></p>

        <h2><?php _e('Opzioni di Inserimento Multiplo', 'design_comuni_italia'); ?></h2>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('dci_multipost_transparency_action', 'dci_multipost_transparency_nonce'); ?>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="dci_multi_files"><?php _e('Carica Documenti Multipli:', 'design_comuni_italia'); ?></label></th>
                        <td>
                            <input type="file" id="dci_multi_files" name="dci_multi_files[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.rar,.7zip">
                            <p class="description"><?php _e('Seleziona più documenti da caricare. Verrà creato un Elemento Trasparenza per ogni file.', 'design_comuni_italia'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dci_default_category"><?php _e('Categoria Predefinita per i Nuovi Elementi:', 'design_comuni_italia'); ?></label></th>
                        <td>
                            <?php
                            wp_dropdown_categories( array(
                                'taxonomy'            => 'tipi_cat_amm_trasp',
                                'name'                => 'dci_default_category',
                                'id'                  => 'dci_default_category',
                                'show_option_none'    => false,
                                'remove_default'      => true,
                                'hide_empty'          => 0,
                                'echo'                => 1,
                                'selected'            => '', // Puoi pre-selezionare una categoria se vuoi
                                'show_option_none'    => __('Seleziona una categoria', 'design_comuni_italia'),
                                'value_field'         => 'term_id',
                                'orderby'             => 'name',
                                'order'               => 'ASC',
                            ) );
                            ?>
                            <p class="description"><?php _e('Questa categoria verrà assegnata a tutti i nuovi elementi creati da questa pagina.', 'design_comuni_italia'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dci_default_open_new_tab"><?php _e('Apri in nuova finestra predefinito:', 'design_comuni_italia'); ?></label></th>
                        <td>
                            <input type="checkbox" id="dci_default_open_new_tab" name="dci_default_open_new_tab" value="1">
                            <p class="description"><?php _e('Spunta per impostare "Apri in una nuova finestra" per tutti i nuovi elementi.', 'design_comuni_italia'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dci_default_open_direct"><?php _e('Apri link in modo diretto:', 'design_comuni_italia'); ?></label></th>
                        <td>
                            <input type="checkbox" id="dci_default_open_direct" name="dci_default_open_direct" value="on">
                            <p class="description"><?php _e('Spunta per impostare "Apri direttamente il file " per tutti i nuovi elementi.', 'design_comuni_italia'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>

           <?php submit_button(__('Crea Elementi Trasparenza', 'design_comuni_italia')); ?>
        </form>

        <?php
        // Processa il form quando viene inviato
        if ( isset( $_POST['submit'] ) && check_admin_referer('dci_multipost_transparency_action', 'dci_multipost_transparency_nonce') ) {
            $default_category = isset( $_POST['dci_default_category'] ) ? absint( $_POST['dci_default_category'] ) : 0;
            $open_new_tab     = isset( $_POST['dci_default_open_new_tab'] ) ? "on" : 0;
            $open_direct_tab  = isset( $_POST['dci_default_open_direct'] ) ?"on" : 0; 
            if ( $default_category === 0 ) {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Seleziona una categoria predefinita per gli elementi.', 'design_comuni_italia') . '</p></div>';
            } else {
                if ( ! empty( $_FILES['dci_multi_files']['name'][0] ) ) {
                    // Carica i file
                    require_once( ABSPATH . 'wp-admin/includes/image.php' );
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                    require_once( ABSPATH . 'wp-admin/includes/media.php' );

                    $uploaded_count = 0;
                    $error_count = 0;

                    foreach ( $_FILES['dci_multi_files']['name'] as $key => $filename ) {
                        if ( $_FILES['dci_multi_files']['error'][$key] === UPLOAD_ERR_OK ) {
                            $file = array(
                                'name'     => $_FILES['dci_multi_files']['name'][$key],
                                'type'     => $_FILES['dci_multi_files']['type'][$key],
                                'tmp_name' => $_FILES['dci_multi_files']['tmp_name'][$key],
                                'error'    => $_FILES['dci_multi_files']['error'][$key],
                                'size'     => $_FILES['dci_multi_files']['size'][$key]
                            );

                            $upload_overrides = array( 'test_form' => false );
                            $movefile = wp_handle_upload( $file, $upload_overrides );

                            if ( $movefile && ! isset( $movefile['error'] ) ) {
                                // Il file è stato caricato con successo nella libreria media
                                $attachment_id = wp_insert_attachment( array(
                                    'guid'           => $movefile['url'],
                                    'post_mime_type' => $movefile['type'],
                                    'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                                    'post_content'   => '',
                                    'post_status'    => 'inherit'
                                ), $movefile['file'] );

                                // Genera i meta dati per l'allegato
                                if ( ! is_wp_error( $attachment_id ) ) {
                                    require_once( ABSPATH . 'wp-admin/includes/image.php' ); // Già inclusa prima, ma non fa male averla qui
                                    $attachment_data = wp_generate_attachment_metadata( $attachment_id, $movefile['file'] );
                                    wp_update_attachment_metadata( $attachment_id, $attachment_data );

                                    // Crea il nuovo Elemento Trasparenza
                                    $new_post_title = preg_replace( '/\.[^.]+$/', '', basename( $filename ) );
                                    $post_data = array(
                                        'post_title'    => $new_post_title,
                                        'post_status'   => 'publish', // o 'draft' se vuoi revisionare
                                        'post_type'     => 'elemento_trasparenza',
                                    );

                                    $post_id = wp_insert_post( $post_data );

                                    if ( ! is_wp_error( $post_id ) ) {
                                        // Assegna la categoria
                                        wp_set_object_terms( $post_id, $default_category, 'tipi_cat_amm_trasp' );

                                        update_post_meta( $post_id, '_dci_elemento_trasparenza_file', array( $attachment_id ) );
                                        update_post_meta( $post_id, '_dci_elemento_trasparenza_open_in_new_tab', $open_new_tab );

                                        $cmb_extra->add_field(array(
                                            'id'            => $prefix . 'ordinamento',
                                            'name'          => __('Ordinamento', 'design_comuni_italia'),
                                            'desc'          => __('Inserisci un valore numerico per l\'ordinamento', 'design_comuni_italia'),
                                            'type'          => 'text',
                                            'attributes'    => array(
                                                'type' => 'number',
                                                'min'  => 0,
                                                'step' => 1,
                                            ),
                                        ));

                                        
                                        update_post_meta( $post_id, '_dci_elemento_trasparenza_open_direct', $open_direct_tab ); 

                                        $uploaded_count++;
                                    } else {
                                        $error_count++;
                                        echo '<div class="notice notice-error is-dismissible"><p>' . sprintf( __('Errore durante la creazione del post per il file %s: %s', 'design_comuni_italia'), esc_html($filename), esc_html($post_id->get_error_message()) ) . '</p></div>';
                                    }
                                } else {
                                    $error_count++;
                                    echo '<div class="notice notice-error is-dismissible"><p>' . sprintf( __('Errore durante l\'inserimento dell\'allegato per il file %s: %s', 'design_comuni_italia'), esc_html($filename), esc_html($attachment_id->get_error_message()) ) . '</p></div>';
                                }
                            } else {
                                $error_count++;
                                echo '<div class="notice notice-error is-dismissible"><p>' . sprintf( __('Errore durante il caricamento del file %s: %s', 'design_comuni_italia'), esc_html($filename), esc_html($movefile['error']) ) . '</p></div>';
                            }
                        } else {
                            $error_count++;
                            echo '<div class="notice notice-error is-dismissible"><p>' . sprintf( __('Errore nel caricamento del file %s (Codice: %d)', 'design_comuni_italia'), esc_html($filename), $_FILES['dci_multi_files']['error'][$key] ) . '</p></div>';
                        }
                    }

                    if ( $uploaded_count > 0 ) {
                        echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( __('Creati con successo %d Elementi di Trasparenza.', 'design_comuni_italia'), $uploaded_count ) . '</p></div>';
                    }
                    if ( $error_count > 0 ) {
                        echo '<div class="notice notice-warning is-dismissible"><p>' . sprintf( __('Sono stati riscontrati %d errori durante la creazione di elementi.', 'design_comuni_italia'), $error_count ) . '</p></div>';
                    }
                } else {
                    echo '<div class="notice notice-info is-dismissible"><p>' . __('Nessun file selezionato per il caricamento.', 'design_comuni_italia') . '</p></div>';
                }
            }
        }
        ?>
    </div>
    <?php
}





/**
 * Esclude i termini:
 * - con visualizza_elemento = 0
 * - o con ruoli dell'utente corrente presenti in excluded_roles
 * SOLO nella pagina di creazione di un Elemento Trasparenza
 */
add_filter( 'terms_clauses', 'dci_hide_invisible_or_blocked_terms', 10, 3 );
function dci_hide_invisible_or_blocked_terms( $clauses, $taxonomies, $args ) {

    // Applichiamo solo alla nostra tassonomia
    if ( ! in_array( 'tipi_cat_amm_trasp', (array) $taxonomies, true ) ) {
        return $clauses;
    }

    // Solo admin area
    if ( ! is_admin() ) {
        return $clauses;
    }

    // Verifica la schermata corrente
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if (
        ! $screen ||
        $screen->base !== 'post' ||
        $screen->action !== 'add' ||
        $screen->post_type !== 'elemento_trasparenza'
    ) {
        return $clauses;
    }

    global $wpdb;

    // JOIN per visualizza_elemento
    if ( false === strpos( $clauses['join'], 'tm_vis' ) ) {
        $clauses['join']  .= " LEFT JOIN {$wpdb->termmeta} tm_vis
                               ON tm_vis.term_id = t.term_id
                               AND tm_vis.meta_key = 'visualizza_elemento' ";
    }

    // JOIN per excluded_roles
    if ( false === strpos( $clauses['join'], 'tm_roles' ) ) {
        $clauses['join']  .= " LEFT JOIN {$wpdb->termmeta} tm_roles
                               ON tm_roles.term_id = t.term_id
                               AND tm_roles.meta_key = 'excluded_roles' ";
    }

    // Prendi i ruoli utente
    $current_user = wp_get_current_user();
    $user_roles = $current_user->roles;
    $escaped_roles = array_map( [ $wpdb, 'esc_like' ], $user_roles );

    $excluded_conditions = [];
    foreach ( $escaped_roles as $role ) {
        // Cerca valore serializzato contenente il ruolo
        $excluded_conditions[] = $wpdb->prepare( "tm_roles.meta_value LIKE %s", '%' . $role . '%' );
    }

    $excluded_sql = '';
    if ( ! empty( $excluded_conditions ) ) {
        $excluded_sql = ' OR ( ' . implode( ' OR ', $excluded_conditions ) . ' ) ';
    }

    $clauses['where'] .= " AND (
        tm_vis.meta_value IS NULL
        OR tm_vis.meta_value = ''
        OR tm_vis.meta_value = '1'
    )
    AND (
        tm_roles.meta_value IS NULL
        $excluded_sql
    ) ";

    return $clauses;
}





// --- Funzioni CMB2 esistenti (rimangono invariate) ---
add_action('cmb2_init', 'dci_add_elemento_trasparenza_metaboxes');
function dci_add_elemento_trasparenza_metaboxes()
{
    $prefix = '_dci_elemento_trasparenza_';

    $cmb_apertura = new_cmb2_box(array(
        'id'            => $prefix . 'box_apertura',
        'title'         => __('Apertura', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'normal',
        'priority'      => 'high',
        'closed'        => false,
    ));

    // $cmb_apertura->add_field(array(
    //     'id'            => $prefix . 'data_pubblicazione',
    //     'name'          => __('Data di pubblicazione', 'design_comuni_italia'),
    //     'desc'          => __('Data in cui il post sarà reso visibile pubblicamente.', 'design_comuni_italia'),
    //     'type'          => 'text_date_timestamp',
    //     'date_format'   => 'd-m-Y',
    // ));


    $cmb_apertura->add_field(array(
        'id'            => $prefix . 'descrizione_breve',
        'name'          => __('Descrizione breve ', 'design_comuni_italia'),
        'desc'          => __('Indicare una sintetica descrizione (max 512 caratteri spazi inclusi)', 'design_comuni_italia'),
        'type'          => 'textarea',
        'attributes'    => array(
            'maxlength' => '512',
        ),
    ));

    $cmb_sezione = new_cmb2_box(array(
        'id'            => $prefix . 'box_sezione_post',
        'title'         => __('Seleziona la sezione', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'normal',
        'priority'      => 'high',
    ));

        $cmb_sezione->add_field( array(
            'id'                => $prefix . 'tipo_cat_amm_trasp',
            'name'              => __( 'Categoria Trasparenza *', 'design_comuni_italia' ),
            'desc'              => __( 'Selezionare una categoria …', 'design_comuni_italia' ),
            'type'              => 'taxonomy_radio_hierarchical',
            'taxonomy'          => 'tipi_cat_amm_trasp',
            'show_option_none'  => false,
            'remove_default'    => true,
            /* ↓↓↓ usa la callback che restituisce SOLO i termini “visibili” ↓↓↓ */
            'options_cb'        => 'dci_get_visible_amministrazione_terms',
        ) );

        $cmb_corpo = new_cmb2_box(array(
        'id'            => $prefix . 'box_corpo',
        'title'         => __('Corpo', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'normal',
        'priority'      => 'low',
    ));

    $cmb_corpo->add_field( array(
        'id' => $prefix . 'descrizione',
        'name'          => __( 'Descrizione', 'design_comuni_italia' ),
        'desc' => __( 'Testo principale del post' , 'design_comuni_italia' ),
        'type' => 'wysiwyg',
        'options' => array(
            'textarea_rows' => 10, 
            'teeny' => false, 
        ),
    ) );

    $cmb_documento = new_cmb2_box(array(
        'id'            => $prefix . 'box_documento',
        'title'         => __('Documento/Link *', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'normal',
        'priority'      => 'high',
    ));

    $cmb_documento->add_field(array(
        'id'            => $prefix . 'url',
        'name'          => __('URL', 'design_comuni_italia'),
        'desc'          => __('Link ad una pagina interna o esterna al sito', 'design_comuni_italia'),
        'type'          => 'text_url',
    ));

      // Gruppo per URL multipli
    $cmb_documento->add_field(array(
        'id'            => $prefix . 'url_documento_group',
        'type'          => 'group',
        'description' => __('Aggiungi uno o più link al documento', 'design_comuni_italia'),
        'options'     => array(
            'group_title'   => __('Link Documento {#}', 'design_comuni_italia'),
            'add_button'    => __('Aggiungi link', 'design_comuni_italia'),
            'remove_button' => __('Rimuovi link', 'design_comuni_italia'),
            'sortable'      => true,
            'closed'        => true,
        ),
    ));
    
    // URL del documento
    $cmb_documento->add_group_field($prefix . 'url_documento_group', array(
        'name' => __('URL del documento', 'design_comuni_italia'),
        'id'   => 'url_documento',
        'type' => 'text_url',
    ));
    
    // Titolo del documento
    $cmb_documento->add_group_field($prefix . 'url_documento_group', array(
        'name' => __('Titolo del link', 'design_comuni_italia'),
        'id'   => 'titolo',
        'type' => 'text',
    ));
    
    // Checkbox: apri in nuova scheda
    $cmb_documento->add_group_field($prefix . 'url_documento_group', array(
        'name' => __('Apri in nuova scheda', 'design_comuni_italia'),
        'id'   => 'target_blank',
        'type' => 'checkbox',
    ));


    $cmb_documento->add_field(array(
        'id'            => $prefix . 'file',
        'name'          => __('Documento: Carica più file', 'design_comuni_italia'),
        'desc'          => __('Carica uno o più documenti. Devono essere scaricabili e stampabili.', 'design_comuni_italia'),
        'type'          => 'file_list',
        'preview_size' => array(100, 100),
        'text'          => array(
            'add_upload_files_text' => __('Aggiungi allegati', 'design_comuni_italia'),
            'remove_image_text'     => __('Rimuovi', 'design_comuni_italia'),
            'remove_text'           => __('Rimuovi', 'design_comuni_italia'),
        ),
    ));

      $cmb_extra = new_cmb2_box(array(
        'id'            => $prefix . 'box_extra',
        'title'         => __('Extra', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'side',
        'priority'      => 'high',
    ));

    $cmb_extra->add_field(array(
        'id'            => $prefix . 'open_in_new_tab',
        'name'          => __('Apri in una nuova finestra', 'design_comuni_italia'),
        'desc'          => __('Spuntare per aprire il documento in una nuova finestra del browser', 'design_comuni_italia'),
        'type'          => 'checkbox',
    ));
    $cmb_extra->add_field(array(
        'id'            => $prefix . 'open_direct',
        'name'          => __('Apri link in modo diretto', 'design_comuni_italia'),
        'desc'          => __('Link diretto al link senza visualizzare alcuna pagina intermedia', 'design_comuni_italia'),
        'type'          => 'checkbox',
    ));

    $cmb_post_collegati = new_cmb2_box(array(
        'id'            => $prefix . 'box_postcollegati',
        'title'         => __('Documenti correlati', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'normal',
        'priority'      => 'low',
    ));

    $cmb_post_collegati->add_field( array(
        'id' => $prefix . 'post_trasparenza',
        'name'          => __( 'Documenti correlati', 'design_comuni_italia' ),
        'desc' => __( 'Selezionare i documenti di trasparenza correlati a quello attualmente pubblicato.', 'design_comuni_italia' ),
        'type'          => 'pw_multiselect',
        'options' => dci_get_posts_options('elemento_trasparenza'),
        'attributes'    => array(
            'placeholder' =>  __( 'Seleziona i documenti correlati', 'design_comuni_italia' ),
        ),
    ) );

}

add_action('admin_print_scripts-post-new.php', 'dci_elemento_trasparenza_admin_script', 11);
add_action('admin_print_scripts-post.php', 'dci_elemento_trasparenza_admin_script', 11);
// Aggiungi l'hook per la tua pagina di amministrazione personalizzata
add_action('admin_enqueue_scripts', 'dci_enqueue_multipost_transparency_scripts');







function dci_elemento_trasparenza_admin_script()
{
    global $post_type;
    if ($post_type === 'elemento_trasparenza') {
        wp_enqueue_script('elemento-trasparenza-admin-script', get_template_directory_uri() . '/inc/admin-js/elemento_trasparenza.js', array('jquery'), null, true);
    }
}

function dci_enqueue_multipost_transparency_scripts($hook_suffix) {
    // Il $hook_suffix per le pagine di sottomenu è tipicamente 'post_type_page_YOUR_PAGE_SLUG'
    if ( 'elemento_trasparenza_page_dci_transparency_multipost_page' === $hook_suffix ) {
        wp_enqueue_script('multipost-transparency-validation-script', get_template_directory_uri() . '/inc/admin-js/elemento_trasparenza.js', array('jquery'), null, true);
    }
}

add_filter('wp_insert_post_data', 'dci_elemento_trasparenza_set_post_content', 99, 1);
function dci_elemento_trasparenza_set_post_content($data)
{
    if ($data['post_type'] === 'elemento_trasparenza') {
        // personalizzazione futura del content
    }
    return $data;
}

// Questa funzione è rimasta dalla logica precedente (pre-impostare campi in CPT con parametro)
// Puoi mantenerla se hai ancora un pulsante che aggiunge un "Tipo 2" di Elemento Trasparenza
// che NON è la pagina di caricamento multiplo. Altrimenti, puoi rimuoverla se non più necessaria.
add_action( 'load-post-new.php', 'dci_handle_specific_elemento_trasparenza_creation' );
function dci_handle_specific_elemento_trasparenza_creation() {
    if ( 'elemento_trasparenza' !== get_current_screen()->post_type ) {
        return;
    }

    if ( isset( $_GET['tipo_elemento'] ) && $_GET['tipo_elemento'] === '2' ) {
        add_filter( 'cmb2_override_meta_value', 'dci_set_default_cmb2_values_for_type_2', 10, 4 );
    }
}

// Funzione per impostare valori predefiniti per CMB2 (esempio)
function dci_set_default_cmb2_values_for_type_2( $value, $object_id, $field_args, $cmb ) {
    if ( $field_args['id'] === '_dci_elemento_trasparenza_tipo_cat_amm_trasp' ) {
        // Sostituisci 'ID_DELLA_CATEGORIA_PREDEFINITA' con l'ID reale del tuo termine di tassonomia
        $value = 'ID_DELLA_CATEGORIA_PREDEFINITA'; // Ricorda di mettere l'ID effettivo qui!
    }
    return $value;

}












