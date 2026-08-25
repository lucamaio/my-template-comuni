<?php
/**
 * Comandi contestuali per l'Amministrazione Trasparente.
 *
 * Il modulo aggiunge un livello di interfaccia alle schermate WordPress gia'
 * esistenti. Non registra nuovi CPT, non modifica slug o metadati storici e non
 * esegue migrazioni: i contenuti creati fuori dal percorso contestuale
 * continuano quindi a essere gestiti esattamente come prima.
 *
 * @package Design_Comuni_Italia
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Restituisce la struttura base usata quando una sezione non e' valida.
 *
 * @return array<string,mixed> Risultato vuoto del resolver.
 */
function dci_at_contextual_empty_resolution()
{
    return array(
        'mode'          => 'invalid',
        'term'          => null,
        'post_type'     => '',
        'registered'    => false,
        'external_url'  => '',
        'section_value' => '',
    );
}

/**
 * Normalizza un nome di sezione per confrontarlo con le etichette esistenti.
 *
 * @param string $value Nome o etichetta della sezione.
 * @return string Valore normalizzato.
 */
function dci_at_contextual_normalize_section_name($value)
{
    $value = html_entity_decode(wp_strip_all_tags((string) $value), ENT_QUOTES, 'UTF-8');

    return sanitize_title(remove_accents($value));
}

/**
 * Indica le sezioni politiche interne alimentate da una procedura composita.
 * Queste pagine aggregano strutture, persone e incarichi e non devono proporre
 * le scorciatoie generiche di creazione o gestione degli elementi.
 *
 * @param array<string,mixed> $resolution Risultato del resolver.
 * @return bool Esito del controllo.
 */
function dci_at_contextual_has_complex_political_workflow($resolution)
{
    if (
        !is_array($resolution)
        || 'external' === ($resolution['mode'] ?? '')
        || empty($resolution['term'])
        || !$resolution['term'] instanceof WP_Term
    ) {
        return false;
    }

    $section_name = dci_at_contextual_normalize_section_name($resolution['term']->name);

    return in_array(
        $section_name,
        array('il-sindaco', 'giunta-comunale', 'consiglio-comunale'),
        true
    );
}

/**
 * Determina il valore del campo sezione per un incarico dirigenziale.
 *
 * La funzione riusa le opzioni gia' dichiarate dal CPT. In questo modo i valori
 * `vertice` e `dirigenti` non vengono ridefiniti in una seconda configurazione.
 *
 * @param WP_Term $term Termine di provenienza.
 * @return string Valore del campo esistente oppure stringa vuota.
 */
function dci_at_contextual_get_manager_section_value($term)
{
    if (!function_exists('dci_incarico_dirigenziale_sections')) {
        return '';
    }

    $term_name = dci_at_contextual_normalize_section_name($term->name);

    foreach (dci_incarico_dirigenziale_sections() as $value => $label) {
        if ($term_name === dci_at_contextual_normalize_section_name($label)) {
            return sanitize_key($value);
        }
    }

    return '';
}

/**
 * Risolve la modalita' di gestione di una sezione Trasparenza.
 *
 * La priorita' e' intenzionalmente rigida: prima un `term_url` HTTP(S) valido,
 * poi una tipologia dedicata abilitata dalla configurazione esistente e infine
 * il CPT storico `elemento_trasparenza`. La funzione non modifica il termine e
 * non crea associazioni retroattive.
 *
 * @param WP_Term|int $term Termine o relativo ID.
 * @return array<string,mixed> Modalita', termine, CPT e dati contestuali.
 */
function dci_at_contextual_resolve_section($term)
{
    $resolution = dci_at_contextual_empty_resolution();

    if (!$term instanceof WP_Term) {
        $term_id = absint($term);
        $term = $term_id > 0 ? get_term($term_id, 'tipi_cat_amm_trasp') : null;
    }

    if (
        !$term instanceof WP_Term
        || is_wp_error($term)
        || 'tipi_cat_amm_trasp' !== $term->taxonomy
    ) {
        return $resolution;
    }

    $resolution['term'] = $term;
    $term_url = trim((string) get_term_meta($term->term_id, 'term_url', true));
    $external_url = $term_url !== ''
        ? esc_url_raw($term_url, array('http', 'https'))
        : '';

    if ($external_url !== '' && wp_http_validate_url($external_url)) {
        $resolution['mode'] = 'external';
        $resolution['external_url'] = $external_url;

        return $resolution;
    }

    $custom_types = function_exists('dci_elemento_trasparenza_get_custom_type_terms')
        ? dci_elemento_trasparenza_get_custom_type_terms()
        : array();

    if (isset($custom_types[$term->name]) && is_array($custom_types[$term->name])) {
        $post_type = isset($custom_types[$term->name]['post_type'])
            ? sanitize_key($custom_types[$term->name]['post_type'])
            : '';

        $resolution['mode'] = 'dedicated';
        $resolution['post_type'] = $post_type;
        $resolution['registered'] = $post_type !== '' && post_type_exists($post_type);

        if ('incarico_dirig' === $post_type) {
            $resolution['section_value'] = dci_at_contextual_get_manager_section_value($term);
        }

        return $resolution;
    }

    $resolution['mode'] = 'standard';
    $resolution['post_type'] = 'elemento_trasparenza';
    $resolution['registered'] = post_type_exists('elemento_trasparenza');

    return $resolution;
}

/**
 * Recupera una capability dichiarata dall'oggetto del post type.
 *
 * @param string $post_type CPT da controllare.
 * @param string $operation Nome della proprieta' capability richiesta.
 * @return string Capability oppure stringa vuota.
 */
function dci_at_contextual_get_post_type_capability($post_type, $operation)
{
    $post_type = sanitize_key($post_type);
    $operation = sanitize_key($operation);
    $post_type_object = get_post_type_object($post_type);

    if (
        !$post_type_object
        || !isset($post_type_object->cap->{$operation})
        || !is_string($post_type_object->cap->{$operation})
    ) {
        return '';
    }

    return $post_type_object->cap->{$operation};
}

/**
 * Verifica una capability del CPT senza ricorrere a permessi hard-coded.
 *
 * @param string $post_type CPT da controllare.
 * @param string $operation Proprieta' di `$post_type_object->cap`.
 * @return bool Esito del controllo.
 */
function dci_at_contextual_user_can_post_type($post_type, $operation)
{
    $capability = dci_at_contextual_get_post_type_capability($post_type, $operation);

    return $capability !== '' && current_user_can($capability);
}

/**
 * Verifica la capability della tassonomia richiesta.
 *
 * @param string $operation Proprieta' di `$taxonomy_object->cap`.
 * @return bool Esito del controllo.
 */
function dci_at_contextual_user_can_taxonomy($operation)
{
    $taxonomy_object = get_taxonomy('tipi_cat_amm_trasp');
    $operation = sanitize_key($operation);

    return $taxonomy_object
        && isset($taxonomy_object->cap->{$operation})
        && is_string($taxonomy_object->cap->{$operation})
        && current_user_can($taxonomy_object->cap->{$operation});
}

/**
 * Verifica esclusivamente il permesso di associare termini gia' esistenti.
 *
 * Gli operatori editoriali non devono gestire l'alberatura della Trasparenza:
 * questa funzione usa soltanto `assign_terms` e non considera mai come fallback
 * `manage_terms`, `edit_terms` o `delete_terms`.
 *
 * @return bool True se l'utente puo' associare una sezione esistente.
 */
function dci_at_contextual_user_can_assign_existing_terms()
{
    return dci_at_contextual_user_can_taxonomy('assign_terms');
}

/**
 * Verifica il permesso strutturale necessario per modificare una sezione.
 *
 * Il controllo e' separato dalla pubblicazione affinche' collegamenti come
 * "Modifica collegamento" o futuri comandi "Configura sezione" non diventino
 * visibili agli operatori che possiedono soltanto `assign_terms`.
 *
 * @return bool True se l'utente puo' modificare i termini della tassonomia.
 */
function dci_at_contextual_user_can_edit_taxonomy_structure()
{
    return dci_at_contextual_user_can_taxonomy('edit_terms');
}

/**
 * Controlla che una sezione standard sia disponibile nel campo CMB2 corrente.
 *
 * Il riuso della funzione esistente mantiene coerenti visibilita', URL esterni,
 * esclusioni per ruolo e tipologie dedicate.
 *
 * @param int $term_id ID del termine.
 * @return bool True se il termine puo' essere usato per un nuovo elemento.
 */
function dci_at_contextual_standard_term_is_available($term_id)
{
    $term_id = absint($term_id);

    if ($term_id <= 0) {
        return false;
    }

    if (!function_exists('dci_elemento_trasparenza_get_excluded_term_ids_for_new_items')) {
        return true;
    }

    return !in_array(
        $term_id,
        array_map('absint', dci_elemento_trasparenza_get_excluded_term_ids_for_new_items()),
        true
    );
}

/**
 * Verifica se l'utente puo' creare un contenuto per la risoluzione indicata.
 *
 * @param array<string,mixed> $resolution Risultato del resolver.
 * @return bool Esito del controllo.
 */
function dci_at_contextual_user_can_create($resolution)
{
    if (
        dci_at_contextual_has_complex_political_workflow($resolution)
        || empty($resolution['registered'])
        || empty($resolution['post_type'])
        || !in_array($resolution['mode'], array('standard', 'dedicated'), true)
        || !dci_at_contextual_user_can_post_type($resolution['post_type'], 'create_posts')
    ) {
        return false;
    }

    if ('standard' === $resolution['mode']) {
        return dci_at_contextual_user_can_assign_existing_terms()
            && dci_at_contextual_standard_term_is_available($resolution['term']->term_id);
    }

    if ('incarico_dirig' === $resolution['post_type']) {
        return in_array($resolution['section_value'], array('vertice', 'dirigenti'), true);
    }

    return true;
}

/**
 * Genera l'URL per una nuova scheda WordPress contestuale.
 *
 * @param array<string,mixed> $resolution Risultato del resolver.
 * @return string URL amministrativo oppure stringa vuota.
 */
function dci_at_contextual_get_create_url($resolution)
{
    if (!dci_at_contextual_user_can_create($resolution)) {
        return '';
    }

    return add_query_arg(
        array(
            'post_type'   => sanitize_key($resolution['post_type']),
            'dci_at_term' => absint($resolution['term']->term_id),
        ),
        admin_url('post-new.php')
    );
}

/**
 * Genera l'URL dell'elenco amministrativo relativo alla sezione.
 *
 * Per gli Elementi Trasparenza usa il query var nativo della tassonomia. Per
 * gli incarichi dirigenziali conserva anche il termine contestuale, che viene
 * trasformato in un filtro sul meta gia' esistente.
 *
 * @param array<string,mixed> $resolution Risultato del resolver.
 * @return string URL amministrativo oppure stringa vuota.
 */
function dci_at_contextual_get_manage_url($resolution)
{
    if (
        dci_at_contextual_has_complex_political_workflow($resolution)
        || empty($resolution['registered'])
        || empty($resolution['post_type'])
        || !in_array($resolution['mode'], array('standard', 'dedicated'), true)
        || !dci_at_contextual_user_can_post_type($resolution['post_type'], 'edit_posts')
    ) {
        return '';
    }

    $query_args = array(
        'post_type'   => sanitize_key($resolution['post_type']),
        'dci_at_term' => absint($resolution['term']->term_id),
    );

    if ('standard' === $resolution['mode']) {
        $query_args['tipi_cat_amm_trasp'] = sanitize_title($resolution['term']->slug);
    }

    return add_query_arg($query_args, admin_url('edit.php'));
}

/**
 * Stampa i comandi contestuali nella pagina pubblica della sezione.
 *
 * Nessun markup o URL amministrativo viene prodotto per utenti anonimi o senza
 * capability. Le sezioni con collegamento esterno non consentono la creazione
 * di contenuti interni; l'eventuale modifica del termine resta disponibile solo
 * a chi possiede la capability della tassonomia.
 *
 * @param WP_Term|null $term Termine corrente; usa quello interrogato se omesso.
 * @return void
 */
function dci_render_trasparenza_contextual_actions($term = null)
{
    if (!is_user_logged_in()) {
        return;
    }

    if (!$term instanceof WP_Term) {
        $term = get_queried_object();
    }

    $resolution = dci_at_contextual_resolve_section($term);
    $actions = array();
    $actions_description = __('Pubblica o gestisci i contenuti collegati.', 'design_comuni_italia');

    if ('external' === $resolution['mode']) {
        $actions_description = __('Aggiorna il collegamento configurato per questa sezione.', 'design_comuni_italia');

        if (dci_at_contextual_user_can_edit_taxonomy_structure()) {
            $edit_term_url = get_edit_term_link($resolution['term']->term_id, 'tipi_cat_amm_trasp');
            if (is_string($edit_term_url) && $edit_term_url !== '') {
                $actions[] = array(
                    'url'   => $edit_term_url,
                    'label' => __('Modifica collegamento', 'design_comuni_italia'),
                    'class' => 'btn btn-outline-primary dci-at-context-actions__link--secondary',
                    'icon'  => '#it-pencil',
                );
            }
        }
    } else {
        $create_url = dci_at_contextual_get_create_url($resolution);
        $manage_url = dci_at_contextual_get_manage_url($resolution);

        if ($create_url !== '') {
            $actions[] = array(
                'url'   => $create_url,
                'label' => __('Aggiungi un elemento', 'design_comuni_italia'),
                'class' => 'btn btn-primary dci-at-context-actions__link--primary',
                'icon'  => '#it-plus',
            );
        }

        if ($manage_url !== '') {
            $actions[] = array(
                'url'   => $manage_url,
                'label' => __('Gestisci gli elementi', 'design_comuni_italia'),
                'class' => 'btn btn-outline-primary dci-at-context-actions__link--secondary',
                'icon'  => '#it-list',
            );
        }
    }

    if (empty($actions)) {
        return;
    }
    ?>
    <nav class="dci-at-context-actions" aria-label="<?php esc_attr_e('Gestione della sezione', 'design_comuni_italia'); ?>">
        <span class="dci-at-context-actions__heading">
            <strong class="dci-at-context-actions__label">
                <?php esc_html_e('Gestione della sezione', 'design_comuni_italia'); ?>
            </strong>
            <span class="dci-at-context-actions__description">
                <?php echo esc_html($actions_description); ?>
            </span>
        </span>
        <span class="dci-at-context-actions__links">
            <?php foreach ($actions as $action) { ?>
                <a
                    class="dci-at-context-actions__link <?php echo esc_attr($action['class']); ?>"
                    href="<?php echo esc_url($action['url']); ?>"
                >
                    <svg class="dci-at-context-actions__icon" width="18" height="18" aria-hidden="true" focusable="false">
                        <use href="<?php echo esc_attr($action['icon']); ?>"></use>
                    </svg>
                    <span><?php echo esc_html($action['label']); ?></span>
                </a>
            <?php } ?>
        </span>
    </nav>
    <?php
}

/**
 * Stampa il collegamento alla normale scheda WordPress di un contenuto.
 *
 * @param int $post_id ID del contenuto mostrato dalla card.
 * @return void
 */
function dci_render_trasparenza_edit_link($post_id)
{
    $post_id = absint($post_id);

    if (
        $post_id <= 0
        || !is_user_logged_in()
        || !current_user_can('edit_post', $post_id)
    ) {
        return;
    }

    $edit_url = get_edit_post_link($post_id, 'raw');
    if (!is_string($edit_url) || $edit_url === '') {
        return;
    }
    $edit_label = sprintf(
        __('Modifica elemento: %s', 'design_comuni_italia'),
        get_the_title($post_id)
    );
    ?>
    <span class="dci-at-context-edit">
        <a
            class="dci-at-context-edit__link btn btn-outline-primary btn-sm"
            href="<?php echo esc_url($edit_url); ?>"
            aria-label="<?php echo esc_attr($edit_label); ?>"
            title="<?php echo esc_attr($edit_label); ?>"
        >
            <svg class="dci-at-context-edit__icon" width="18" height="18" aria-hidden="true" focusable="false">
                <use href="#it-pencil"></use>
            </svg>
            <span><?php esc_html_e('Modifica elemento', 'design_comuni_italia'); ?></span>
        </a>
    </span>
    <?php
}

/**
 * Carica lo stile frontend nelle pagine della Trasparenza.
 *
 * Il foglio serve anche ai pulsanti pubblici "Apri dettaglio" e alle regole
 * responsive delle card: deve quindi essere disponibile anche agli anonimi.
 *
 * @return void
 */
function dci_at_contextual_enqueue_frontend_style()
{
    $is_transparency_page = is_page_template('page-templates/amministrazione-trasparente.php');
    $is_transparency_taxonomy = is_tax('tipi_cat_amm_trasp');
    if (!$is_transparency_page && !$is_transparency_taxonomy) {
        return;
    }

    $style_path = get_template_directory() . '/assets/css/trasparenza-contestuale.css';
    wp_enqueue_style(
        'dci-trasparenza-contestuale',
        get_template_directory_uri() . '/assets/css/trasparenza-contestuale.css',
        array('dci-wp-style'),
        file_exists($style_path) ? filemtime($style_path) : null
    );
}
add_action('wp_enqueue_scripts', 'dci_at_contextual_enqueue_frontend_style');

/**
 * Carica la persistenza del menu soltanto nella pagina principale della
 * Amministrazione Trasparente. Lo stato resta nel browser e non genera
 * scritture sul database del sito in produzione.
 *
 * @return void
 */
function dci_at_enqueue_transparency_menu_state()
{
    if (!is_page_template('page-templates/amministrazione-trasparente.php')) {
        return;
    }

    $script_path = get_template_directory() . '/assets/js/trasparenza-menu-state.js';

    wp_enqueue_script(
        'dci-trasparenza-menu-state',
        get_template_directory_uri() . '/assets/js/trasparenza-menu-state.js',
        array(),
        file_exists($script_path) ? filemtime($script_path) : null,
        true
    );
}
add_action('wp_enqueue_scripts', 'dci_at_enqueue_transparency_menu_state');

/**
 * Carica il raccordo cromatico solo nella sezione Articolazione uffici.
 * Il colore viene letto dallo stile effettivo dell'intestazione, includendo
 * quindi le regole inserite tramite il CSS aggiuntivo di WordPress.
 *
 * @return void
 */
function dci_at_enqueue_office_theme_colors()
{
    if (!is_tax('tipi_cat_amm_trasp')) {
        return;
    }

    $resolution = dci_at_contextual_resolve_section(get_queried_object());
    if ('unita_organizzativa' !== $resolution['post_type']) {
        return;
    }

    $script_path = get_template_directory() . '/assets/js/trasparenza-theme-colors.js';

    wp_enqueue_script(
        'dci-trasparenza-theme-colors',
        get_template_directory_uri() . '/assets/js/trasparenza-theme-colors.js',
        array(),
        file_exists($script_path) ? filemtime($script_path) : null,
        true
    );
}
add_action('wp_enqueue_scripts', 'dci_at_enqueue_office_theme_colors');

/**
 * Legge e valida il contesto della schermata `post-new.php`.
 *
 * Gli input vengono sempre deslashati e sanificati. Il termine deve appartenere
 * alla tassonomia prevista, non deve avere un link esterno e deve risolversi
 * esattamente nel CPT richiesto dall'URL.
 *
 * @return array<string,mixed>|WP_Error Contesto valido oppure errore.
 */
function dci_at_contextual_get_new_screen_context()
{
    if (!isset($_GET['dci_at_term'])) {
        return new WP_Error('dci_at_no_context', __('Contesto non presente.', 'design_comuni_italia'));
    }

    $term_id = absint(wp_unslash($_GET['dci_at_term']));
    $post_type = isset($_GET['post_type'])
        ? sanitize_key(wp_unslash($_GET['post_type']))
        : 'post';
    $term = $term_id > 0 ? get_term($term_id, 'tipi_cat_amm_trasp') : null;

    if (!$term instanceof WP_Term || is_wp_error($term)) {
        return new WP_Error('dci_at_invalid_term', __('La sezione richiesta non esiste.', 'design_comuni_italia'));
    }

    $resolution = dci_at_contextual_resolve_section($term);

    if ('external' === $resolution['mode']) {
        return new WP_Error('dci_at_external_term', __('La sezione utilizza un collegamento esterno.', 'design_comuni_italia'));
    }

    if (
        empty($resolution['registered'])
        || $post_type === ''
        || $post_type !== $resolution['post_type']
    ) {
        return new WP_Error('dci_at_wrong_post_type', __('La tipologia richiesta non gestisce questa sezione.', 'design_comuni_italia'));
    }

    if (!dci_at_contextual_user_can_create($resolution)) {
        return new WP_Error('dci_at_forbidden', __('Non hai i permessi per creare contenuti in questa sezione.', 'design_comuni_italia'));
    }

    return $resolution;
}

/**
 * Interrompe `post-new.php` quando il parametro contestuale e' manipolato.
 *
 * La schermata di creazione normale, priva di `dci_at_term`, resta invariata.
 *
 * @return void
 */
function dci_at_contextual_validate_new_screen()
{
    if (!isset($_GET['dci_at_term'])) {
        return;
    }

    $context = dci_at_contextual_get_new_screen_context();
    if (is_wp_error($context)) {
        wp_die(
            esc_html($context->get_error_message()),
            esc_html__('Contesto Amministrazione Trasparente non valido', 'design_comuni_italia'),
            array('response' => 403)
        );
    }
}
add_action('load-post-new.php', 'dci_at_contextual_validate_new_screen');

/**
 * Crea l'azione del nonce legandola a termine e CPT.
 *
 * @param int    $term_id ID del termine.
 * @param string $post_type CPT contestuale.
 * @return string Azione del nonce.
 */
function dci_at_contextual_nonce_action($term_id, $post_type)
{
    return 'dci_at_context_' . absint($term_id) . '_' . sanitize_key($post_type);
}

/**
 * Mostra il contesto e i campi di sicurezza nella nuova scheda WordPress.
 *
 * Il termine resta presente in un input hidden e il nonce viene verificato di
 * nuovo durante il salvataggio. Il JavaScript migliora l'interfaccia, ma non e'
 * mai l'unica protezione.
 *
 * @param WP_Post $post Auto-draft aperto da WordPress.
 * @return void
 */
function dci_at_contextual_render_admin_context($post)
{
    if (!$post instanceof WP_Post || !isset($_GET['dci_at_term'])) {
        return;
    }

    $context = dci_at_contextual_get_new_screen_context();
    if (is_wp_error($context) || $post->post_type !== $context['post_type']) {
        return;
    }

    $term_id = absint($context['term']->term_id);
    $post_type = sanitize_key($context['post_type']);
    ?>
    <div
        class="notice notice-info dci-at-admin-context"
        role="status"
        data-dci-at-context="1"
        data-post-type="<?php echo esc_attr($post_type); ?>"
        data-term-slug="<?php echo esc_attr($context['term']->slug); ?>"
        data-section-value="<?php echo esc_attr($context['section_value']); ?>"
    >
        <p>
            <strong><?php esc_html_e('Sezione di destinazione:', 'design_comuni_italia'); ?></strong>
            <?php echo esc_html($context['term']->name); ?>
        </p>
        <p><?php esc_html_e('La destinazione e\' bloccata per evitare una pubblicazione nella sottosezione sbagliata.', 'design_comuni_italia'); ?></p>
    </div>
    <input type="hidden" name="dci_at_term" value="<?php echo esc_attr($term_id); ?>">
    <input type="hidden" name="dci_at_context_post_type" value="<?php echo esc_attr($post_type); ?>">
    <input type="hidden" name="dci_at_context_new" value="1">
    <?php
    wp_nonce_field(
        dci_at_contextual_nonce_action($term_id, $post_type),
        'dci_at_context_nonce',
        false
    );
}
add_action('edit_form_after_title', 'dci_at_contextual_render_admin_context', 20);

/**
 * Carica gli asset admin solo nella nuova scheda contestuale valida.
 *
 * @param string $hook_suffix Identificativo della schermata amministrativa.
 * @return void
 */
function dci_at_contextual_enqueue_admin_assets($hook_suffix)
{
    if ('post-new.php' !== $hook_suffix || !isset($_GET['dci_at_term'])) {
        return;
    }

    $context = dci_at_contextual_get_new_screen_context();
    if (is_wp_error($context)) {
        return;
    }

    $style_path = get_template_directory() . '/assets/css/trasparenza-contestuale.css';
    $script_path = get_template_directory() . '/assets/js/trasparenza-contestuale.js';

    wp_enqueue_style(
        'dci-trasparenza-contestuale',
        get_template_directory_uri() . '/assets/css/trasparenza-contestuale.css',
        array(),
        file_exists($style_path) ? filemtime($style_path) : null
    );
    wp_enqueue_script(
        'dci-trasparenza-contestuale',
        get_template_directory_uri() . '/assets/js/trasparenza-contestuale.js',
        array('jquery'),
        file_exists($script_path) ? filemtime($script_path) : null,
        true
    );
}
add_action('admin_enqueue_scripts', 'dci_at_contextual_enqueue_admin_assets', 30);

/**
 * Valida i campi contestuali inviati dal form di un nuovo contenuto.
 *
 * Il controllo richiede auto-draft originario, nonce, capability sul singolo
 * post, corrispondenza del CPT e una nuova risoluzione del termine. In questo
 * modo la manipolazione del DOM o della richiesta POST non puo' cambiare sezione.
 *
 * @param int    $post_id ID del post in salvataggio.
 * @param string $expected_post_type CPT previsto dall'hook.
 * @return array<string,mixed>|WP_Error Contesto verificato oppure errore.
 */
function dci_at_contextual_get_posted_context($post_id, $expected_post_type)
{
    $post_id = absint($post_id);
    $expected_post_type = sanitize_key($expected_post_type);

    if (
        !isset($_POST['dci_at_context_new'])
        || '1' !== sanitize_text_field(wp_unslash($_POST['dci_at_context_new']))
        || !isset($_POST['original_post_status'])
        || 'auto-draft' !== sanitize_text_field(wp_unslash($_POST['original_post_status']))
    ) {
        return new WP_Error('dci_at_not_new', __('Il contenuto non proviene da una nuova scheda contestuale.', 'design_comuni_italia'));
    }

    $term_id = isset($_POST['dci_at_term']) ? absint(wp_unslash($_POST['dci_at_term'])) : 0;
    $posted_post_type = isset($_POST['dci_at_context_post_type'])
        ? sanitize_key(wp_unslash($_POST['dci_at_context_post_type']))
        : '';
    $nonce = isset($_POST['dci_at_context_nonce'])
        ? sanitize_text_field(wp_unslash($_POST['dci_at_context_nonce']))
        : '';

    if (
        $post_id <= 0
        || $term_id <= 0
        || $posted_post_type !== $expected_post_type
        || get_post_type($post_id) !== $expected_post_type
        || !wp_verify_nonce($nonce, dci_at_contextual_nonce_action($term_id, $expected_post_type))
        || !current_user_can('edit_post', $post_id)
    ) {
        return new WP_Error('dci_at_invalid_posted_context', __('Il contesto inviato non e\' valido.', 'design_comuni_italia'));
    }

    $term = get_term($term_id, 'tipi_cat_amm_trasp');
    if (!$term instanceof WP_Term || is_wp_error($term)) {
        return new WP_Error('dci_at_invalid_posted_term', __('La sezione inviata non esiste.', 'design_comuni_italia'));
    }

    $resolution = dci_at_contextual_resolve_section($term);
    if (
        empty($resolution['registered'])
        || $resolution['post_type'] !== $expected_post_type
        || 'external' === $resolution['mode']
    ) {
        return new WP_Error('dci_at_changed_resolution', __('La modalita\' della sezione e\' cambiata.', 'design_comuni_italia'));
    }

    return $resolution;
}

/**
 * Impone lato server il termine al nuovo Elemento Trasparenza contestuale.
 *
 * L'hook ha priorita' successiva al salvataggio CMB2. `wp_set_object_terms()` e'
 * eseguito soltanto per il nuovo elemento proveniente da questo flusso; una
 * normale modifica di contenuti esistenti non entra mai in questa funzione.
 *
 * @param int     $post_id ID del post.
 * @param WP_Post $post Post in salvataggio.
 * @param bool    $update Indica un aggiornamento WordPress.
 * @return void
 */
function dci_at_contextual_save_standard_term($post_id, $post, $update)
{
    unset($update);

    if (
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        || wp_is_post_autosave($post_id)
        || wp_is_post_revision($post_id)
        || !$post instanceof WP_Post
        || 'elemento_trasparenza' !== $post->post_type
    ) {
        return;
    }

    $context = dci_at_contextual_get_posted_context($post_id, 'elemento_trasparenza');
    if (
        is_wp_error($context)
        || 'standard' !== $context['mode']
        || !dci_at_contextual_user_can_assign_existing_terms()
        || !dci_at_contextual_standard_term_is_available($context['term']->term_id)
    ) {
        return;
    }

    wp_set_object_terms(
        absint($post_id),
        array(absint($context['term']->term_id)),
        'tipi_cat_amm_trasp',
        false
    );
}
add_action('save_post_elemento_trasparenza', 'dci_at_contextual_save_standard_term', 100, 3);

/**
 * Impone il valore della sezione al nuovo incarico dirigenziale contestuale.
 *
 * Il meta esistente viene aggiornato solo nel primo salvataggio contestuale;
 * incarichi gia' pubblicati o aperti dal normale editor non vengono riclassificati.
 *
 * @param int     $post_id ID del post.
 * @param WP_Post $post Post in salvataggio.
 * @param bool    $update Indica un aggiornamento WordPress.
 * @return void
 */
function dci_at_contextual_save_manager_section($post_id, $post, $update)
{
    unset($update);

    if (
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        || wp_is_post_autosave($post_id)
        || wp_is_post_revision($post_id)
        || !$post instanceof WP_Post
        || 'incarico_dirig' !== $post->post_type
    ) {
        return;
    }

    $context = dci_at_contextual_get_posted_context($post_id, 'incarico_dirig');
    if (
        is_wp_error($context)
        || 'dedicated' !== $context['mode']
        || !in_array($context['section_value'], array('vertice', 'dirigenti'), true)
    ) {
        return;
    }

    update_post_meta(
        absint($post_id),
        '_dci_incarico_dirigenziale_sezione_pubblicazione',
        sanitize_key($context['section_value'])
    );
}
add_action('save_post_incarico_dirig', 'dci_at_contextual_save_manager_section', 100, 3);

/**
 * Finalizza i valori contestuali dopo il salvataggio generico di CMB2.
 *
 * WordPress esegue `save_post_{post_type}` prima di `save_post`; CMB2 e'
 * collegato al secondo hook con priorita' 10. Il controllo specifico resta
 * disponibile sui due CPT, mentre questa finalizzazione a priorita' 100
 * garantisce che CMB2 non possa sovrascrivere il termine o la sezione bloccati.
 * Tutte le verifiche di nonce, auto-draft e capability vengono ripetute dalle
 * funzioni richiamate.
 *
 * @param int     $post_id ID del post.
 * @param WP_Post $post Post in salvataggio.
 * @param bool    $update Indica un aggiornamento WordPress.
 * @return void
 */
function dci_at_contextual_finalize_after_cmb2($post_id, $post, $update)
{
    if (!$post instanceof WP_Post) {
        return;
    }

    if ('elemento_trasparenza' === $post->post_type) {
        dci_at_contextual_save_standard_term($post_id, $post, $update);
    } elseif ('incarico_dirig' === $post->post_type) {
        dci_at_contextual_save_manager_section($post_id, $post, $update);
    }
}
add_action('save_post', 'dci_at_contextual_finalize_after_cmb2', 100, 3);

/**
 * Analizza in sola lettura le capability dei ruoli editoriali Trasparenza.
 *
 * Sono considerati operatori editoriali i ruoli non amministrativi che possono
 * creare o pubblicare Elementi Trasparenza. Il controllo segnala sia l'assenza
 * di `assign_terms`, necessaria per associare sezioni esistenti, sia l'eventuale
 * presenza di capability strutturali. Non vengono aggiunti o rimossi permessi.
 *
 * @return array<string,array<string,mixed>> Esito dell'analisi per ruolo.
 */
function dci_at_contextual_audit_publisher_role_capabilities()
{
    $post_type_object = get_post_type_object('elemento_trasparenza');
    $taxonomy_object = get_taxonomy('tipi_cat_amm_trasp');

    if (!$post_type_object || !$taxonomy_object) {
        return array();
    }

    $publisher_capabilities = array_filter(array(
        isset($post_type_object->cap->create_posts) ? $post_type_object->cap->create_posts : '',
        isset($post_type_object->cap->publish_posts) ? $post_type_object->cap->publish_posts : '',
    ));
    $assign_capability = isset($taxonomy_object->cap->assign_terms)
        ? (string) $taxonomy_object->cap->assign_terms
        : '';
    $structural_capabilities = array_filter(array(
        isset($taxonomy_object->cap->manage_terms) ? $taxonomy_object->cap->manage_terms : '',
        isset($taxonomy_object->cap->edit_terms) ? $taxonomy_object->cap->edit_terms : '',
        isset($taxonomy_object->cap->delete_terms) ? $taxonomy_object->cap->delete_terms : '',
    ));
    $role_names = wp_roles()->role_names;
    $results = array();

    foreach (wp_roles()->role_objects as $role_key => $role) {
        if ($role->has_cap('manage_options')) {
            continue;
        }

        $is_publisher = false;
        foreach ($publisher_capabilities as $publisher_capability) {
            if ($role->has_cap($publisher_capability)) {
                $is_publisher = true;
                break;
            }
        }

        if (!$is_publisher) {
            continue;
        }

        $unexpected_capabilities = array();
        foreach ($structural_capabilities as $structural_capability) {
            if ($role->has_cap($structural_capability)) {
                $unexpected_capabilities[] = (string) $structural_capability;
            }
        }

        $results[$role_key] = array(
            'label'                 => isset($role_names[$role_key])
                ? translate_user_role($role_names[$role_key])
                : $role_key,
            'missing_assign'        => $assign_capability !== '' && !$role->has_cap($assign_capability),
            'unexpected_structural' => array_values(array_unique($unexpected_capabilities)),
        );
    }

    return $results;
}

/**
 * Mostra un avviso diagnostico senza modificare i ruoli del sito.
 *
 * L'operatore privo di `assign_terms` riceve un messaggio esplicito. Gli utenti
 * amministrativi o autorizzati a modificare i termini vedono anche il riepilogo
 * dei ruoli editoriali con capability mancanti o strutturali da rivedere.
 *
 * @return void
 */
function dci_at_contextual_render_capability_audit_notice()
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (
        !$screen
        || (
            'elemento_trasparenza' !== $screen->post_type
            && 'tipi_cat_amm_trasp' !== $screen->taxonomy
        )
    ) {
        return;
    }

    $create_capability = dci_at_contextual_get_post_type_capability(
        'elemento_trasparenza',
        'create_posts'
    );
    $current_user_is_publisher = $create_capability !== '' && current_user_can($create_capability);

    if ($current_user_is_publisher && !dci_at_contextual_user_can_assign_existing_terms()) {
        ?>
        <div class="notice notice-error">
            <p>
                <strong><?php esc_html_e('Capability Trasparenza mancante:', 'design_comuni_italia'); ?></strong>
                <?php esc_html_e('puoi creare contenuti, ma non possiedi il permesso necessario per associarli a una sezione esistente. Richiedi esclusivamente la capability assign_tipi_cat_amm_trasp.', 'design_comuni_italia'); ?>
            </p>
        </div>
        <?php
    }

    if (!current_user_can('manage_options') && !dci_at_contextual_user_can_edit_taxonomy_structure()) {
        return;
    }

    $audit = dci_at_contextual_audit_publisher_role_capabilities();
    $issues = array_filter(
        $audit,
        static function ($role_status) {
            return !empty($role_status['missing_assign'])
                || !empty($role_status['unexpected_structural']);
        }
    );

    if (empty($issues)) {
        return;
    }
    ?>
    <div class="notice notice-warning">
        <p>
            <strong><?php esc_html_e('Verifica capability degli operatori Trasparenza', 'design_comuni_italia'); ?></strong>
        </p>
        <ul>
            <?php foreach ($issues as $role_status) { ?>
                <li>
                    <strong><?php echo esc_html($role_status['label']); ?>:</strong>
                    <?php if (!empty($role_status['missing_assign'])) { ?>
                        <?php esc_html_e('manca assign_tipi_cat_amm_trasp.', 'design_comuni_italia'); ?>
                    <?php } ?>
                    <?php if (!empty($role_status['unexpected_structural'])) { ?>
                        <?php
                        printf(
                            /* translators: %s: elenco capability strutturali. */
                            esc_html__('capability strutturali presenti da verificare: %s.', 'design_comuni_italia'),
                            esc_html(implode(', ', $role_status['unexpected_structural']))
                        );
                        ?>
                    <?php } ?>
                </li>
            <?php } ?>
        </ul>
        <p><?php esc_html_e('Il tema non modifica automaticamente alcun ruolo o capability.', 'design_comuni_italia'); ?></p>
    </div>
    <?php
}
add_action('admin_notices', 'dci_at_contextual_render_capability_audit_notice');

/**
 * Filtra l'elenco degli incarichi dirigenziali per la sezione contestuale.
 *
 * @param WP_Query $query Query amministrativa corrente.
 * @return void
 */
function dci_at_contextual_filter_manager_admin_list($query)
{
    if (
        !is_admin()
        || !$query->is_main_query()
        || !isset($_GET['dci_at_term'])
        || 'incarico_dirig' !== sanitize_key((string) $query->get('post_type'))
    ) {
        return;
    }

    $term_id = absint(wp_unslash($_GET['dci_at_term']));
    $resolution = dci_at_contextual_resolve_section($term_id);

    if (
        'dedicated' !== $resolution['mode']
        || 'incarico_dirig' !== $resolution['post_type']
        || !in_array($resolution['section_value'], array('vertice', 'dirigenti'), true)
        || !dci_at_contextual_user_can_post_type('incarico_dirig', 'edit_posts')
    ) {
        return;
    }

    $meta_query = (array) $query->get('meta_query');
    $meta_query[] = array(
        'key'     => '_dci_incarico_dirigenziale_sezione_pubblicazione',
        'value'   => sanitize_key($resolution['section_value']),
        'compare' => '=',
    );
    $query->set('meta_query', $meta_query);
}
add_action('pre_get_posts', 'dci_at_contextual_filter_manager_admin_list');
