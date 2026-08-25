<?php
require_once get_template_directory() . '/inc/admin/guida-trasparenza.php';

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
        'supports'              => array('title', 'author', 'revisions'),
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

/**
 * Mostra i collegamenti rapidi e la guida nella pagina principale degli
 * Elementi di Amministrazione Trasparente.
 */
add_filter('views_edit-elemento_trasparenza', 'dci_elemento_trasparenza_render_admin_help', 5);
function dci_elemento_trasparenza_render_admin_help($views)
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;

    if (
        ! $screen ||
        'edit' !== $screen->base ||
        'elemento_trasparenza' !== $screen->post_type
    ) {
        return $views;
    }

    $trasparenza_attiva = 'true' === dci_get_option('ck_abilita_trasparenza');
    $pagina_pubblica = home_url('/amministrazione-trasparente/');
    ?>

    <div class="dci-trasparenza-admin-tools">
        <?php if (!$trasparenza_attiva) : ?>
            <div class="dci-trasparenza-admin-tools__warning" role="status">
                <strong><?php esc_html_e('Amministrazione Trasparente non attiva', 'design_comuni_italia'); ?></strong>
                <p>
                    <?php esc_html_e(
                        'La sezione pubblica è attualmente disabilitata. Puoi continuare a gestire gli elementi, ma i contenuti non saranno visibili ai cittadini finché la funzionalità non verrà abilitata.',
                        'design_comuni_italia'
                    ); ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="dci-trasparenza-admin-tools__actions">
            <a
                class="button button-primary"
                href="<?php echo esc_url($pagina_pubblica); ?>"
                target="_blank"
                rel="noopener noreferrer"
            >
                <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                <?php esc_html_e('Visualizza Amministrazione Trasparente', 'design_comuni_italia'); ?>
            </a>

            <button
                type="button"
                class="button"
                id="dci-trasparenza-guide-toggle"
                aria-expanded="false"
                aria-controls="dci-trasparenza-admin-guide"
                data-open-label="<?php echo esc_attr__('Mostra guida', 'design_comuni_italia'); ?>"
                data-close-label="<?php echo esc_attr__('Nascondi guida', 'design_comuni_italia'); ?>"
            >
                <span class="dashicons dashicons-book-alt" aria-hidden="true"></span>
                <span class="dci-trasparenza-guide-toggle__label">
                    <?php esc_html_e('Mostra guida', 'design_comuni_italia'); ?>
                </span>
            </button>
        </div>

        <div class="dci-trasparenza-admin-tools__warning dci-trasparenza-admin-tools__warning--always" role="note">
            <strong><?php esc_html_e('Avviso sull’aggiornamento delle categorie', 'design_comuni_italia'); ?></strong>
            <p>
                <?php esc_html_e(
                    'È in corso l’aggiornamento della mappa dell’Amministrazione Trasparente per adeguarla agli standard normativi vigenti. L’eventuale aggiunta o mantenimento di voci ulteriori rispetto alla mappa prevista dovrà essere supportato da un documento ufficiale firmato digitalmente dal Segretario comunale.',
                    'design_comuni_italia'
                ); ?>
            </p>
        </div>

        

        <div
            class="dci-trasparenza-admin-guide"
            id="dci-trasparenza-admin-guide"
            hidden
        >
            <h2><?php esc_html_e('Come pubblicare un elemento', 'design_comuni_italia'); ?></h2>
            <p>
                <?php esc_html_e(
                    'Per pubblicare un elemento nell’Amministrazione Trasparente, segui questi passaggi:',
                    'design_comuni_italia'
                ); ?>
            </p>
            <ol>
                <li>
                    <?php
                    printf(
                        /* translators: %s: Button label. */
                        esc_html__('Fai clic sul pulsante %s, accanto al titolo “Amministrazione Trasparente”. Si aprirà la schermata per l’inserimento dei dati.', 'design_comuni_italia'),
                        '<strong>' . esc_html__('Aggiungi un Elemento Trasparenza', 'design_comuni_italia') . '</strong>'
                    );
                    ?>
                </li>
                <li><?php esc_html_e('Inserisci un titolo chiaro e riconoscibile per l’elemento da pubblicare.', 'design_comuni_italia'); ?></li>
                <li><?php esc_html_e('Seleziona la categoria nella quale pubblicare l’elemento. Nell’elenco sono disponibili soltanto le categorie interne al sito che non rimandano a collegamenti esterni.', 'design_comuni_italia'); ?></li>
                <li><?php esc_html_e('Se necessario, inserisci una breve descrizione del contenuto.', 'design_comuni_italia'); ?></li>
                <li>
                    <?php
                    printf(
                        /* translators: %s: Field label. */
                        esc_html__('Inserisci il documento o il collegamento da pubblicare nel campo %s.', 'design_comuni_italia'),
                        '<strong>' . esc_html__('Documento/link', 'design_comuni_italia') . '</strong>'
                    );
                    ?>
                </li>
                <li>
                    <?php
                    printf(
                        /* translators: %s: Button label. */
                        esc_html__('Controlla attentamente i dati inseriti, quindi pubblica l’elemento tramite il pulsante laterale %s.', 'design_comuni_italia'),
                        '<strong>' . esc_html__('Pubblica', 'design_comuni_italia') . '</strong>'
                    );
                    ?>
                </li>
                <li>
                    <?php
                    printf(
                        /* translators: %s: Link label. */
                        esc_html__('Dopo la pubblicazione, apri l’elemento tramite il collegamento %s e verifica che contenuto, documento e categoria siano corretti.', 'design_comuni_italia'),
                        '<strong>' . esc_html__('Visualizza', 'design_comuni_italia') . '</strong>'
                    );
                    ?>
                </li>
            </ol>
            <p>
                <strong><?php esc_html_e('Nota:', 'design_comuni_italia'); ?></strong>
                <?php esc_html_e(
                    'le categorie principali servono a organizzare l’alberatura; per la pubblicazione seleziona una delle relative sottocategorie disponibili.',
                    'design_comuni_italia'
                ); ?>
            </p>
        </div>
    </div>

    <script>
        (function () {
            var toggle = document.getElementById('dci-trasparenza-guide-toggle');
            var guide = document.getElementById('dci-trasparenza-admin-guide');

            if (!toggle || !guide) {
                return;
            }

            toggle.addEventListener('click', function () {
                var isOpen = toggle.getAttribute('aria-expanded') === 'true';
                var label = toggle.querySelector('.dci-trasparenza-guide-toggle__label');

                toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                guide.hidden = isOpen;

                if (label) {
                    label.textContent = isOpen
                        ? toggle.getAttribute('data-open-label')
                        : toggle.getAttribute('data-close-label');
                }
            });
        }());
    </script>
    <?php

    return $views;
}





/**
 * Mostra, nella schermata di creazione/modifica di un Elemento Trasparenza,
 * il riquadro con le categorie gestite da tipologie personalizzate.
 *
 * Le card servono a indirizzare l'utente verso il post type corretto
 * (bandi, atti di concessione, incarichi, uffici, ecc.) quando una sezione
 * non deve essere compilata tramite il normale campo "Categoria Trasparenza".
 * Le voci vengono raggruppate per post type, così più sezioni che usano la
 * stessa tipologia compaiono in una sola card.
 *
 * @param WP_Post $post Post in modifica.
 * @return void
 */
add_action('edit_form_after_title', 'dci_elemento_trasparenza_add_content_after_title');
function dci_elemento_trasparenza_add_content_after_title($post)
{
    if ($post->post_type !== 'elemento_trasparenza') {
        return;
    }

    $custom_type_cards = array();
    foreach (dci_elemento_trasparenza_get_custom_type_terms() as $category_name => $custom_type) {
        $card_key = !empty($custom_type['post_type'])
            ? sanitize_key($custom_type['post_type'])
            : md5((string) $custom_type['url']);

        if (!isset($custom_type_cards[$card_key])) {
            $custom_type_cards[$card_key] = $custom_type;
            $custom_type_cards[$card_key]['categories'] = array();
        }

        $custom_type_cards[$card_key]['categories'][] = $category_name;
    }

    // Mantiene la card informativa preesistente senza modificare i filtri della tassonomia.
    if (dci_get_option("ck_portalesoloperusoesterno") === 'false') {
        $custom_type_cards['persona_pubblica'] = array(
            'categories'   => array('Titolari di incarichi politici e di amministrazione'),
            'description'  => __('Questa sezione viene generata dalle Persone pubbliche e dai relativi incarichi politici.', 'design_comuni_italia'),
            'url'          => admin_url('edit.php?post_type=persona_pubblica'),
            'action_label' => __('Vai alle persone pubbliche', 'design_comuni_italia'),
            'post_type'    => 'persona_pubblica',
        );
    }

    echo "<span><i>Il <b>Titolo</b> è il <b>Nome dell'elemento dell'amministrazione trasparente</b>.</i></span><br><br>";

    ?>
    <?php if (!empty($custom_type_cards)) : ?>
        <div class="dci-section-box">
            <h2><?php esc_html_e('Categorie personalizzate', 'design_comuni_italia'); ?></h2>
            <p class="dci-section-box__description">
                <?php esc_html_e(
                    'Queste sezioni sono gestite da tipologie dedicate e non devono essere selezionate nell’elenco delle categorie standard. Usa la relativa scheda per pubblicare o modificare i contenuti.',
                    'design_comuni_italia'
                ); ?>
            </p>
            <div class="dci-menu-container">
                <?php foreach ($custom_type_cards as $custom_type_card) :
                    $post_type_object = get_post_type_object($custom_type_card['post_type'] ?? '');
                    $required_capability = $custom_type_card['capability'] ?? 'edit_posts';
                    $can_manage_custom_type = $post_type_object
                        && isset($post_type_object->cap->{$required_capability})
                        && current_user_can($post_type_object->cap->{$required_capability});
                    ?>
                    <?php if ($can_manage_custom_type) : ?>
                        <a
                            href="<?php echo esc_url($custom_type_card['url']); ?>"
                            class="dci-menu-btn"
                            aria-label="<?php echo esc_attr($custom_type_card['action_label']); ?>"
                        >
                    <?php else : ?>
                        <div class="dci-menu-btn dci-menu-btn--disabled" aria-disabled="true">
                    <?php endif; ?>
                        <strong class="dci-menu-btn__title">
                            <?php echo esc_html(implode(' / ', $custom_type_card['categories'])); ?>
                        </strong>
                        <span class="dci-menu-btn__description">
                            <?php echo esc_html($custom_type_card['description']); ?>
                        </span>
                        <span class="dci-menu-btn__action">
                            <?php if ($can_manage_custom_type) : ?>
                                <?php echo esc_html($custom_type_card['action_label']); ?> &rarr;
                            <?php else : ?>
                                <?php esc_html_e('Non disponi dei permessi per gestire questa tipologia.', 'design_comuni_italia'); ?>
                            <?php endif; ?>
                        </span>
                    <?php if ($can_manage_custom_type) : ?>
                        </a>
                    <?php else : ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    <?php
}





/**
 * Registra la pagina amministrativa "Multi-Elemento" sotto il menu
 * Amministrazione Trasparente.
 *
 * La pagina consente di caricare più documenti insieme e creare un elemento
 * trasparenza per ciascun file, usando una categoria predefinita scelta
 * dall'operatore.
 *
 * @return void
 */
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
 * Renderizza e gestisce la pagina "Multi-Elemento".
 *
 * La funzione mostra il form di caricamento multiplo, valida il nonce,
 * carica i file nella libreria media e crea un post "elemento_trasparenza"
 * per ogni allegato caricato correttamente. Imposta inoltre categoria,
 * allegato principale e preferenze di apertura del link.
 *
 * @return void
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
                            $excluded_term_ids = dci_elemento_trasparenza_get_excluded_term_ids_for_new_items();
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
                                'exclude'             => $excluded_term_ids,
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
 * Restituisce le categorie gestite tramite una tipologia di contenuto dedicata.
 *
 * Queste categorie vengono mostrate come riferimento nella schermata di
 * creazione, ma non possono essere selezionate come Elemento Trasparenza.
 * La stessa mappa viene usata anche per generare le card informative e per
 * escludere i relativi termini dal campo "Categoria Trasparenza".
 *
 * @return array<string,array<string,mixed>> Mappa nome sezione => dati card/tipologia.
 */
if (!function_exists('dci_elemento_trasparenza_get_custom_type_terms')) {
    function dci_elemento_trasparenza_get_custom_type_terms() {
        static $custom_terms = null;

        if (is_array($custom_terms)) {
            return $custom_terms;
        }

        $custom_terms = array();

        if (dci_get_option("ck_incarichieautorizzazioniaidipendenti", "Trasparenza") !== 'false' && dci_get_option("ck_incarichieautorizzazioniaidipendenti", "Trasparenza") !== '') {
            $custom_terms['Incarichi conferiti e autorizzati ai dipendenti'] = array(
                'description'  => __('Il caricamento dei dati in questa sezione avviene creando un Incarico conferito o autorizzato.', 'design_comuni_italia'),
                'url'          => admin_url('edit.php?post_type=incarichi_dip'),
                'action_label' => __('Vai agli incarichi dei dipendenti', 'design_comuni_italia'),
                'post_type'    => 'incarichi_dip',
            );
        }

        if (dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") !== 'false' && dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") !== '') {
            $bando_data = array(
                'description'  => __('Il caricamento dei dati in questa sezione avviene creando un Bando di gara o contratto.', 'design_comuni_italia'),
                'url'          => admin_url('edit.php?post_type=bando'),
                'action_label' => __('Vai a bandi e contratti', 'design_comuni_italia'),
                'post_type'    => 'bando',
            );
            $custom_terms['Contratti Pubblici'] = $bando_data;
            $custom_terms['Atti, documenti e link a BDNCP'] = $bando_data;
        }

        if (dci_get_option("ck_attidiconcessione", "Trasparenza") !== 'false' && dci_get_option("ck_attidiconcessione", "Trasparenza") !== '') {
            $custom_terms['Atti di concessione'] = array(
                'description'  => __('Il caricamento dei dati in questa sezione avviene creando un Atto di concessione.', 'design_comuni_italia'),
                'url'          => admin_url('edit.php?post_type=atto_concessione'),
                'action_label' => __('Vai agli atti di concessione', 'design_comuni_italia'),
                'post_type'    => 'atto_concessione',
            );
        }

        if (dci_get_option("ck_titolariIncarichiCollaborazioneConsulenzaTemplatePersonalizzato", "Trasparenza") !== 'false' && dci_get_option("ck_titolariIncarichiCollaborazioneConsulenzaTemplatePersonalizzato", "Trasparenza") !== '') {
            $custom_terms['Titolari di incarichi di collaborazione o consulenza'] = array(
                'description'  => __('Il caricamento dei dati in questa sezione avviene creando un Titolare di incarico.', 'design_comuni_italia'),
                'url'          => admin_url('edit.php?post_type=titolare_incarico'),
                'action_label' => __('Vai ai titolari di incarico', 'design_comuni_italia'),
                'post_type'    => 'titolare_incarico',
            );
        }

        if (function_exists('dci_incarico_dirigenziale_custom_template_enabled') && dci_incarico_dirigenziale_custom_template_enabled()) {
            $incarico_dirigenziale_data = array(
                'description'  => __('Il caricamento dei dati in questa sezione avviene creando un Incarico dirigenziale.', 'design_comuni_italia'),
                'url'          => admin_url('post-new.php?post_type=incarico_dirig'),
                'action_label' => __('Vai agli incarichi dirigenziali', 'design_comuni_italia'),
                'post_type'    => 'incarico_dirig',
            );

            $custom_terms['Titolari di incarichi dirigenziali amministrativi di vertice'] = $incarico_dirigenziale_data;
            $custom_terms['Incarichi dirigenziali a qualsiasi titolo conferiti'] = $incarico_dirigenziale_data;
        }

        if (dci_get_option("ck_portalesoloperusoesterno") !== 'true' && dci_get_option("ck_portalesoloperusoesterno") !== '') {
            $custom_terms['Articolazione uffici'] = array(
                'description'  => __('Il caricamento dei dati in questa sezione avviene creando un Ufficio.', 'design_comuni_italia'),
                'url'          => admin_url('edit.php?post_type=unita_organizzativa'),
                'action_label' => __('Vai alle unità organizzative', 'design_comuni_italia'),
                'post_type'    => 'unita_organizzativa',
            );
            $custom_terms['Telefono e posta elettronica'] = array(
                'description'  => __('Il caricamento dei dati in questa sezione avviene creando un Punto di contatto.', 'design_comuni_italia'),
                'url'          => admin_url('edit.php?post_type=punto_contatto'),
                'action_label' => __('Vai ai punti di contatto', 'design_comuni_italia'),
                'post_type'    => 'punto_contatto',
            );
        }

        foreach ($custom_terms as $name => &$custom_term) {
            $custom_term['name'] = $name;
        }
        unset($custom_term);

        return $custom_terms;
    }
}

/**
 * Restituisce i nomi dei termini da nascondere nel campo "Categoria Trasparenza".
 *
 * I nomi derivano dalle tipologie personalizzate attive: quando un contenuto
 * deve essere gestito con un CPT dedicato, il termine corrispondente non deve
 * essere selezionabile nel normale Elemento Trasparenza.
 *
 * @return string[] Nomi dei termini da escludere.
 */
if (!function_exists('dci_elemento_trasparenza_get_terms_hidden_for_new_items')) {
    function dci_elemento_trasparenza_get_terms_hidden_for_new_items() {
        return array_keys(dci_elemento_trasparenza_get_custom_type_terms());
    }
}

/**
 * Calcola gli ID dei termini non selezionabili nel campo "Categoria Trasparenza".
 *
 * Vengono esclusi:
 * - termini non marcati come visualizzabili;
 * - termini con URL esterno;
 * - termini bloccati per il ruolo dell'utente corrente;
 * - termini gestiti da tipologie personalizzate attive.
 *
 * Il risultato viene memorizzato in cache statica per evitare query ripetute
 * durante lo stesso caricamento pagina.
 *
 * @return int[] ID dei termini da escludere.
 */
if (!function_exists('dci_elemento_trasparenza_get_excluded_term_ids_for_new_items')) {
    function dci_elemento_trasparenza_get_excluded_term_ids_for_new_items() {
        static $excluded_ids_cache = null;

        if (is_array($excluded_ids_cache)) {
            return $excluded_ids_cache;
        }

        $excluded_ids = array();
        $hidden_names = dci_elemento_trasparenza_get_terms_hidden_for_new_items();

        $terms = get_terms(array(
            'taxonomy'   => 'tipi_cat_amm_trasp',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ));

        if (is_wp_error($terms) || empty($terms)) {
            $excluded_ids_cache = array();
            return $excluded_ids_cache;
        }

        foreach ($terms as $term) {
            $visible = (string) get_term_meta($term->term_id, 'visualizza_elemento', true);
            $term_url = trim((string) get_term_meta($term->term_id, 'term_url', true));
            $excluded_roles = get_term_meta($term->term_id, 'excluded_roles', true);
            $excluded_roles = is_array($excluded_roles) ? $excluded_roles : maybe_unserialize($excluded_roles);
            $excluded_roles = is_array($excluded_roles) ? $excluded_roles : array();

            $user_cannot_see_term = false;
            foreach ((array) wp_get_current_user()->roles as $role) {
                if (in_array($role, $excluded_roles, true)) {
                    $user_cannot_see_term = true;
                    break;
                }
            }

            if (
                $visible !== '1' ||
                $term_url !== '' ||
                $user_cannot_see_term ||
                in_array($term->name, $hidden_names, true)
            ) {
                $excluded_ids[] = (int) $term->term_id;
            }
        }

        $excluded_ids_cache = array_values(array_unique($excluded_ids));
        return $excluded_ids_cache;
    }
}

/**
 * Restituisce le opzioni visibili della tassonomia Amministrazione Trasparente.
 *
 * Usa la stessa logica di esclusione del campo "Categoria Trasparenza" e
 * mantiene la gerarchia dei termini aggiungendo un prefisso testuale in base
 * alla profondità. Serve per menu o select custom che devono mostrare solo
 * sezioni realmente selezionabili.
 *
 * @return array<int,string> Mappa term_id => nome visualizzato.
 */
if (!function_exists('dci_get_visible_amministrazione_terms')) {
    function dci_get_visible_amministrazione_terms() {
        $excluded_ids = dci_elemento_trasparenza_get_excluded_term_ids_for_new_items();

        $terms = get_terms(array(
            'taxonomy'   => 'tipi_cat_amm_trasp',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
            'exclude'    => $excluded_ids,
        ));

        if (is_wp_error($terms) || empty($terms)) {
            return array();
        }

        $children_map = array();
        foreach ($terms as $term) {
            $parent_id = (int) $term->parent;
            if (!isset($children_map[$parent_id])) {
                $children_map[$parent_id] = array();
            }
            $children_map[$parent_id][] = $term;
        }

        $options = array();
        $append_terms = static function ($parent_id, $depth) use (&$append_terms, &$children_map, &$options) {
            if (empty($children_map[$parent_id])) {
                return;
            }

            foreach ($children_map[$parent_id] as $term) {
                $prefix = $depth > 0 ? str_repeat('- ', $depth) : '';
                $options[$term->term_id] = $prefix . $term->name;
                $append_terms((int) $term->term_id, $depth + 1);
            }
        };

        $append_terms(0, 0);

        return $options;
    }
}

/**
 * Filtro storico per nascondere termini non selezionabili nelle query termini.
 *
 * Al momento il filtro è disattivato dal `return $clauses` iniziale perché la
 * gestione effettiva dell'esclusione avviene tramite `query_args` dei campi CMB2
 * e tramite le funzioni dedicate sopra. Il codice successivo rimane come
 * riferimento della precedente strategia basata su SQL.
 *
 * @param array    $clauses    Parti SQL generate da WordPress per la query termini.
 * @param string[] $taxonomies Tassonomie coinvolte nella query.
 * @param array    $args       Argomenti originali della query termini.
 * @return array Parti SQL eventualmente modificate.
 */
add_filter( 'terms_clauses', 'dci_hide_invisible_or_blocked_terms', 10, 3 );
function dci_hide_invisible_or_blocked_terms( $clauses, $taxonomies, $args ) {
    return $clauses;

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

    if ( false === strpos( $clauses['join'], 'tm_url' ) ) {
        $clauses['join']  .= " LEFT JOIN {$wpdb->termmeta} tm_url
                               ON tm_url.term_id = t.term_id
                               AND tm_url.meta_key = 'term_url' ";
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

    $hidden_names = dci_elemento_trasparenza_get_terms_hidden_for_new_items();
    $hidden_names_sql = '';
    if ( ! empty( $hidden_names ) ) {
        $quoted_names = array_map( static function ( $name ) {
            return "'" . esc_sql( $name ) . "'";
        }, $hidden_names );

        $hidden_names_sql = ' AND t.name NOT IN (' . implode( ', ', $quoted_names ) . ')';
    }

    $clauses['where'] .= " AND (
        tm_vis.meta_value = '1'
    )
    AND (
        tm_url.meta_value IS NULL
        OR tm_url.meta_value = ''
    )
    AND (
        tm_roles.meta_value IS NULL
        $excluded_sql
    ) "
    . $hidden_names_sql;

    $clauses['orderby'] = ' ORDER BY t.name ASC ';

    return $clauses;
}





/**
 * Registra i metabox CMB2 per il post type "elemento_trasparenza".
 *
 * Definisce i gruppi di campi per apertura, categoria, descrizione, documenti,
 * collegamenti, opzioni extra e contenuti correlati. Nel campo categoria
 * applica gli ID esclusi per non mostrare sezioni nascoste, esterne o gestite
 * da tipologie personalizzate. In modifica preserva il valore storico tramite
 * input hidden se la categoria salvata non è più selezionabile.
 *
 * @return void
 */
add_action('cmb2_init', 'dci_add_elemento_trasparenza_metaboxes');

/**
 * Accetta nel campo immagine soltanto allegati con un formato grafico.
 *
 * @param mixed $value Valore proposto da CMB2.
 * @return string URL dell'immagine oppure una stringa vuota.
 */
function dci_sanitize_elemento_trasparenza_immagine($value)
{
    $image_url = is_string($value) ? esc_url_raw($value) : '';

    if ($image_url === '') {
        return '';
    }

    $attachment_id = attachment_url_to_postid($image_url);
    $mime_type = $attachment_id ? (string) get_post_mime_type($attachment_id) : '';
    $image_path = (string) wp_parse_url($image_url, PHP_URL_PATH);
    $image_extension = strtolower((string) pathinfo($image_path, PATHINFO_EXTENSION));
    $image_extensions = array('jpg', 'jpeg', 'jpe', 'png', 'gif', 'webp', 'avif', 'svg', 'bmp', 'ico', 'tif', 'tiff', 'heic', 'heif');

    if (strpos($mime_type, 'image/') !== 0 && !in_array($image_extension, $image_extensions, true)) {
        return '';
    }

    return $image_url;
}

/**
 * Restituisce le categorie mostrate nel selettore solo a scopo informativo.
 *
 * @return array<int,array<string,string>> Dati informativi indicizzati per term ID.
 */
if (!function_exists('dci_elemento_trasparenza_get_informational_terms')) {
    function dci_elemento_trasparenza_get_informational_terms() {
        $informational_terms = array();
        $custom_terms = dci_elemento_trasparenza_get_custom_type_terms();
        $is_internal_portal = dci_get_option('ck_portalesoloperusoesterno') !== 'true';
        $internal_political_section_slugs = array(
            'il-sindaco',
            'sindaco',
            'giunta-comunale',
            'consiglio-comunale',
        );
        $terms = get_terms(array(
            'taxonomy'   => 'tipi_cat_amm_trasp',
            'hide_empty' => false,
        ));

        if (is_wp_error($terms)) {
            return $informational_terms;
        }

        foreach ($terms as $term) {
            $term_url = trim((string) get_term_meta($term->term_id, 'term_url', true));

            $normalized_term_name = sanitize_title((string) $term->name);
            if ($is_internal_portal && (
                in_array((string) $term->slug, $internal_political_section_slugs, true)
                || in_array($normalized_term_name, $internal_political_section_slugs, true)
            )) {
                $informational_terms[(int) $term->term_id] = array(
                    'type'        => 'political-office',
                    'description' => __('Questa sezione è alimentata automaticamente dalle persone inserite nel relativo ufficio politico.', 'design_comuni_italia'),
                    'destination' => sprintf(
                        __('Pubblica e aggiorna i contenuti dalla scheda dell’ufficio: %s', 'design_comuni_italia'),
                        $term->name
                    ),
                );
                continue;
            }

            if ($term_url !== '') {
                $informational_terms[(int) $term->term_id] = array(
                    'type'        => 'link',
                    'description' => __('Questa categoria è gestita tramite il collegamento configurato e non accetta Elementi Trasparenza.', 'design_comuni_italia'),
                    'destination' => sprintf(__('Link configurato: %s', 'design_comuni_italia'), $term_url),
                );
                continue;
            }

            if (isset($custom_terms[$term->name])) {
                $custom_term = $custom_terms[$term->name];
                $informational_terms[(int) $term->term_id] = array(
                    'type'        => 'custom',
                    'description' => (string) ($custom_term['description'] ?? __('Questa categoria è gestita da una tipologia di contenuto dedicata.', 'design_comuni_italia')),
                    'destination' => sprintf(
                        __('Area di pubblicazione: %s', 'design_comuni_italia'),
                        (string) ($custom_term['action_label'] ?? $term->name)
                    ),
                );
            }
        }

        return $informational_terms;
    }
}

/**
 * Mantiene solo l'HTML editoriale consentito da WordPress.
 *
 * @param mixed $value Valore proposto da CMB2.
 * @return string Testo formattato sanificato.
 */
function dci_sanitize_elemento_trasparenza_testo_formattato($value, $field_args = array(), $field = null)
{
    $sanitized_value = wp_kses_post((string) $value);
    $plain_value = html_entity_decode(
        wp_strip_all_tags($sanitized_value),
        ENT_QUOTES | ENT_HTML5,
        'UTF-8'
    );

    if (mb_strlen($plain_value, 'UTF-8') <= 1024) {
        return $sanitized_value;
    }

    $object_id = $field instanceof CMB2_Field ? absint($field->object_id()) : 0;
    $field_id = isset($field_args['id']) ? (string) $field_args['id'] : '';

    return $object_id > 0 && $field_id !== ''
        ? (string) get_post_meta($object_id, $field_id, true)
        : '';
}

function dci_add_elemento_trasparenza_metaboxes()
{
    $prefix = '_dci_elemento_trasparenza_';
    $excluded_term_ids = dci_elemento_trasparenza_get_excluded_term_ids_for_new_items();
    $informational_term_ids = array_keys(dci_elemento_trasparenza_get_informational_terms());
    $category_field_excluded_ids = array_values(array_diff($excluded_term_ids, $informational_term_ids));

    /*
     * Salvaguardia per i contenuti storici: se la categoria già associata è
     * esclusa per visibilità, URL, ruolo o tipologia personalizzata, la si
     * conserva senza renderla nuovamente selezionabile.
     */
    $preserved_category_input = '';
    $editing_post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
    if ($editing_post_id > 0 && get_post_type($editing_post_id) === 'elemento_trasparenza') {
        $assigned_terms = wp_get_object_terms(
            $editing_post_id,
            'tipi_cat_amm_trasp',
            array('fields' => 'all')
        );

        if (!is_wp_error($assigned_terms)) {
            foreach ($assigned_terms as $assigned_term) {
                if (in_array((int) $assigned_term->term_id, $excluded_term_ids, true)) {
                    $preserved_category_input = sprintf(
                        '<input type="hidden" name="%1$s" value="%2$s" data-dci-preserved-category="1">',
                        esc_attr($prefix . 'tipo_cat_amm_trasp'),
                        esc_attr($assigned_term->slug)
                    );
                    break;
                }
            }
        }
    }

    $cmb_apertura = new_cmb2_box(array(
        'id'            => $prefix . 'box_apertura',
        'title'         => __('Apertura', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'normal',
        'priority'      => 'high',
        'closed'        => false,
    ));

    $cmb_apertura->add_field(array(
        'name'            => __('Immagine', 'design_comuni_italia'),
        'desc'            => __('Immagine principale della card. Sono ammessi esclusivamente formati immagine (ad esempio PNG, JPG, WebP o SVG).', 'design_comuni_italia'),
        'id'              => $prefix . 'immagine',
        'type'            => 'file',
        'options'         => array('url' => false),
        'query_args'      => array('type' => 'image'),
        'sanitization_cb' => 'dci_sanitize_elemento_trasparenza_immagine',
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
        'desc'          => __('Inserisci una sintesi chiara del contenuto pubblicato. Puoi usare grassetto, corsivo, collegamenti ed elenchi. Massimo 1024 caratteri visibili, spazi inclusi.', 'design_comuni_italia'),
        'type'          => 'wysiwyg',
        'options'       => array(
            'media_buttons' => false,
            'textarea_rows' => 5,
            'teeny'         => true,
        ),
        'sanitization_cb' => 'dci_sanitize_elemento_trasparenza_testo_formattato',
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
            'desc'              => __( 'Cerca e seleziona la sezione nella quale pubblicare questo elemento. Le voci viola si pubblicano da una tipologia dedicata; le voci ambra sono gestite tramite un collegamento. Queste voci sono informative e non selezionabili.', 'design_comuni_italia' ),
            'type'              => 'taxonomy_radio_hierarchical',
            'taxonomy'          => 'tipi_cat_amm_trasp',
            'show_option_none'  => false,
            'remove_default'    => true,
            'before_field'      => $preserved_category_input,
            'query_args'        => array(
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
                'exclude'    => $category_field_excluded_ids,
            ),
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
        'desc' => __('Inserisci eventuali informazioni di approfondimento sul contenuto, sui documenti allegati o sui collegamenti pubblicati. Il testo sarà visualizzato nella pagina di dettaglio dell’elemento.', 'design_comuni_italia'),
        'type' => 'wysiwyg',
        'options' => array(
            'textarea_rows' => 10, 
            'teeny' => false, 
        ),
    ) );

    $cmb_documento = new_cmb2_box(array(
        'id'            => $prefix . 'box_documento',
        'title'         => __('Documenti e collegamenti *', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'normal',
        'priority'      => 'high',
    ));

    $cmb_documento->add_field(array(
        'id'            => $prefix . 'url',
        'name'          => __('Collegamento principale', 'design_comuni_italia'),
        'desc'          => __('Inserisci l’indirizzo completo di una pagina interna o di un sito esterno.', 'design_comuni_italia'),
        'type'          => 'text_url',
        'attributes'    => array(
            'placeholder' => 'https://www.esempio.it/pagina',
        ),
    ));

      // Gruppo per URL multipli
    $cmb_documento->add_field(array(
        'id'            => $prefix . 'url_documento_group',
        'type'          => 'group',
        'description' => __('Aggiungi uno o più collegamenti, indicando per ciascuno un indirizzo e un testo descrittivo.', 'design_comuni_italia'),
        'options'     => array(
            'group_title'   => __('Collegamento {#}', 'design_comuni_italia'),
            'add_button'    => __('Aggiungi collegamento', 'design_comuni_italia'),
            'remove_button' => __('Rimuovi collegamento', 'design_comuni_italia'),
            'sortable'      => true,
            'closed'        => true,
        ),
    ));
    
    // URL del documento
    $cmb_documento->add_group_field($prefix . 'url_documento_group', array(
        'name'       => __('Indirizzo del collegamento', 'design_comuni_italia'),
        'desc'       => __('Inserisci l’URL completo, incluso https://.', 'design_comuni_italia'),
        'id'         => 'url_documento',
        'type'       => 'text_url',
        'attributes' => array(
            'placeholder' => 'https://www.esempio.it/documento',
        ),
    ));
    
    // Titolo del documento
    $cmb_documento->add_group_field($prefix . 'url_documento_group', array(
        'name'       => __('Testo del collegamento', 'design_comuni_italia'),
        'desc'       => __('Usa un testo breve che descriva chiaramente la destinazione del collegamento.', 'design_comuni_italia'),
        'id'         => 'titolo',
        'type'       => 'text',
        'attributes' => array(
            'placeholder' => __('Es. Consulta il documento online', 'design_comuni_italia'),
        ),
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
    $cmb_extra->add_field(array(
        'id'            => $prefix . 'in_evidenza',
        'name'          => __('Metti in evidenza', 'design_comuni_italia'),
        'desc'          => __('Evidenzia questo elemento negli elenchi dell’Amministrazione Trasparente.', 'design_comuni_italia'),
        'type'          => 'checkbox',
    ));

    $cmb_post_collegati = new_cmb2_box(array(
        'id'            => $prefix . 'box_postcollegati',
        'title'         => __('Contenuti collegati', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'normal',
        'priority'      => 'low',
    ));

    $cmb_post_collegati->add_field( array(
        'id' => $prefix . 'post_trasparenza',
        'name'          => __( 'Elementi di Amministrazione Trasparente collegati', 'design_comuni_italia' ),
        'desc' => __( 'Seleziona uno o più elementi di Amministrazione Trasparente da mostrare come contenuti correlati nella pagina corrente.', 'design_comuni_italia' ),
        'type'          => 'pw_multiselect',
        'options' => dci_get_posts_options('elemento_trasparenza'),
        'attributes'    => array(
            'placeholder' =>  __( 'Seleziona gli elementi collegati', 'design_comuni_italia' ),
        ),
    ) );

}

add_action('admin_print_scripts-post-new.php', 'dci_elemento_trasparenza_admin_script', 11);
add_action('admin_print_scripts-post.php', 'dci_elemento_trasparenza_admin_script', 11);
add_action('admin_enqueue_scripts', 'dci_enqueue_multipost_transparency_scripts');
add_action('admin_enqueue_scripts', 'dci_elemento_trasparenza_admin_style', 20);







/**
 * Carica il CSS amministrativo dedicato agli Elementi Trasparenza.
 *
 * Il foglio di stile contiene sia la UI del selettore categoria nella pagina
 * di creazione/modifica, sia il box strumenti/guida nella schermata elenco.
 * Per questo viene caricato solo quando la schermata corrente riguarda il
 * post type "elemento_trasparenza" e la base è `post` oppure `edit`.
 *
 * @return void
 */
function dci_elemento_trasparenza_admin_style()
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (
        !$screen ||
        !in_array($screen->base, array('post', 'edit'), true) ||
        $screen->post_type !== 'elemento_trasparenza'
    ) {
        return;
    }

    $style_path = get_template_directory() . '/inc/admin-css/elemento-trasparenza.css';
    wp_enqueue_style(
        'elemento-trasparenza-admin-style',
        get_template_directory_uri() . '/inc/admin-css/elemento-trasparenza.css',
        array(),
        file_exists($style_path) ? filemtime($style_path) : null
    );
}

/**
 * Carica lo script admin degli Elementi Trasparenza e gli passa i dati utili.
 *
 * Lo script migliora il campo "Categoria Trasparenza" con ricerca, conteggi,
 * descrizioni dei termini e selezione guidata. Prima dell'enqueue vengono
 * raccolte descrizioni e conteggi dei termini visibili, escludendo quelli non
 * selezionabili secondo la logica centralizzata del file.
 *
 * @return void
 */
function dci_elemento_trasparenza_admin_script()
{
    global $post_type;
    if ($post_type === 'elemento_trasparenza') {
        $editing_post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
        $category_locked = $editing_post_id > 0 && get_post_status($editing_post_id) === 'publish';
        $script_path = get_template_directory() . '/inc/admin-js/elemento_trasparenza.js';
        $term_descriptions = array();
        $term_counts = array();
        $term_direct_counts = array();
        $term_children = array();
        $term_states = array();
        $informational_terms = dci_elemento_trasparenza_get_informational_terms();
        $informational_term_ids = array_keys($informational_terms);
        $excluded_term_ids = array_values(array_diff(
            dci_elemento_trasparenza_get_excluded_term_ids_for_new_items(),
            $informational_term_ids
        ));
        $transparency_terms = get_terms(array(
            'taxonomy'   => 'tipi_cat_amm_trasp',
            'hide_empty' => false,
            'exclude'    => $excluded_term_ids,
        ));

        if (!is_wp_error($transparency_terms)) {
            foreach ($transparency_terms as $transparency_term) {
                $description = trim(wp_strip_all_tags($transparency_term->description));
                $term_id = (int) $transparency_term->term_id;

                if (isset($informational_terms[$term_id])) {
                    $term_state = $informational_terms[$term_id];
                    $description = trim((string) $term_state['description']);
                    $term_states[$transparency_term->slug] = array(
                        'type'        => (string) $term_state['type'],
                        'description' => $description,
                        'destination' => (string) $term_state['destination'],
                    );
                }

                if ($description === '') {
                    $description = sprintf(
                        __('Sezione dedicata alla pubblicazione di contenuti relativi a “%s”.', 'design_comuni_italia'),
                        $transparency_term->name
                    );
                }

                if ($description !== '') {
                    $term_descriptions[$transparency_term->slug] = $description;
                }

                $term_direct_counts[(int) $transparency_term->term_id] = (int) $transparency_term->count;
                $parent_id = (int) $transparency_term->parent;
                if (!isset($term_children[$parent_id])) {
                    $term_children[$parent_id] = array();
                }
                $term_children[$parent_id][] = (int) $transparency_term->term_id;
            }

            $count_cache = array();
            $calculate_term_count = static function ($term_id) use (&$calculate_term_count, &$count_cache, $term_direct_counts, $term_children) {
                if (isset($count_cache[$term_id])) {
                    return $count_cache[$term_id];
                }

                $count = isset($term_direct_counts[$term_id]) ? $term_direct_counts[$term_id] : 0;
                foreach ($term_children[$term_id] ?? array() as $child_id) {
                    $count += $calculate_term_count($child_id);
                }

                $count_cache[$term_id] = $count;
                return $count;
            };

            foreach ($transparency_terms as $transparency_term) {
                $term_counts[$transparency_term->slug] = $calculate_term_count((int) $transparency_term->term_id);
            }
        }

        wp_enqueue_script(
            'elemento-trasparenza-admin-script',
            get_template_directory_uri() . '/inc/admin-js/elemento_trasparenza.js',
            array('jquery'),
            file_exists($script_path) ? filemtime($script_path) : null,
            true
        );
        wp_localize_script(
            'elemento-trasparenza-admin-script',
            'dciElementoTrasparenzaUi',
            array(
                'termDescriptions' => $term_descriptions,
                'termCounts'       => $term_counts,
                'termStates'       => $term_states,
                'categoryLocked'   => $category_locked,
                'internalPortal'   => dci_get_option('ck_portalesoloperusoesterno') !== 'true',
            )
        );
    }
}

/**
 * Carica lo script usato nella pagina "Multi-Elemento".
 *
 * La pagina di caricamento multiplo riusa lo script admin degli Elementi
 * Trasparenza per le validazioni lato interfaccia. Il controllo su
 * `$hook_suffix` evita di caricarlo su altre pagine di amministrazione.
 *
 * @param string $hook_suffix Identificativo della pagina admin corrente.
 * @return void
 */
function dci_enqueue_multipost_transparency_scripts($hook_suffix) {
    // Il $hook_suffix per le pagine di sottomenu è tipicamente 'post_type_page_YOUR_PAGE_SLUG'
    if ( 'elemento_trasparenza_page_dci_transparency_multipost_page' === $hook_suffix ) {
        wp_enqueue_script('multipost-transparency-validation-script', get_template_directory_uri() . '/inc/admin-js/elemento_trasparenza.js', array('jquery'), null, true);
    }
}

/**
 * Punto di estensione sul contenuto salvato dell'Elemento Trasparenza.
 *
 * Il filtro viene eseguito prima dell'inserimento/aggiornamento del post.
 * Attualmente non modifica il contenuto, ma rimane come punto controllato per
 * eventuali normalizzazioni future senza intervenire sulla registrazione del
 * post type o sui metabox.
 *
 * @param array $data Dati del post in fase di salvataggio.
 * @return array Dati del post, invariati salvo future personalizzazioni.
 */
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
/**
 * Aggancia i valori predefiniti per creazioni speciali di Elemento Trasparenza.
 *
 * La logica è storica e si attiva solo quando si apre la pagina di nuovo post
 * con `tipo_elemento=2`. In quel caso viene aggiunto un filtro CMB2 che può
 * precompilare alcuni campi del form.
 *
 * @return void
 */
add_action( 'load-post-new.php', 'dci_handle_specific_elemento_trasparenza_creation' );
function dci_handle_specific_elemento_trasparenza_creation() {
    if ( 'elemento_trasparenza' !== get_current_screen()->post_type ) {
        return;
    }

    if ( isset( $_GET['tipo_elemento'] ) && $_GET['tipo_elemento'] === '2' ) {
        add_filter( 'cmb2_override_meta_value', 'dci_set_default_cmb2_values_for_type_2', 10, 4 );
    }
}

/**
 * Fornisce valori predefiniti a CMB2 per la creazione speciale `tipo_elemento=2`.
 *
 * La funzione intercetta il valore letto da CMB2 e, per il campo categoria,
 * può sostituirlo con un valore predefinito. Il valore attuale è un segnaposto
 * storico e va considerato inattivo finché non viene sostituito con un ID reale.
 *
 * @param mixed $value Valore che CMB2 sta per usare.
 * @param int   $object_id ID del post/oggetto.
 * @param array $field_args Configurazione del campo CMB2.
 * @param CMB2  $cmb Oggetto metabox CMB2.
 * @return mixed Valore originale o valore predefinito.
 */
function dci_set_default_cmb2_values_for_type_2( $value, $object_id, $field_args, $cmb ) {
    if ( $field_args['id'] === '_dci_elemento_trasparenza_tipo_cat_amm_trasp' ) {
        // Sostituisci 'ID_DELLA_CATEGORIA_PREDEFINITA' con l'ID reale del tuo termine di tassonomia
        $value = 'ID_DELLA_CATEGORIA_PREDEFINITA'; // Ricorda di mettere l'ID effettivo qui!
    }
    return $value;

}


