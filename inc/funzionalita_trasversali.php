<?php

/**
 * Estendo Wordpress Rest Api
 */
function dci_register_sedi_route() {
    register_rest_route('wp/v2', '/sedi/ufficio/', array(
        'methods' => 'GET',
        'callback' => 'dci_get_sedi_ufficio'
    ));
}
add_action('rest_api_init', 'dci_register_sedi_route');

/**
 * restituisce i luoghi che sono referenziati come sedi dell'Unità Organizzativa passata come parametro (id o title)
 * @param WP_REST_Request $request
 * @return array[]
 */
function dci_get_sedi_ufficio(WP_REST_Request $request) {

    $params = $_GET;
    if (array_key_exists('title', $params)) {
        $ufficio  = get_page_by_title($params['title'], OBJECT, 'unita_organizzativa');
        $id = $ufficio -> ID;
    }
    else if (array_key_exists('id', $params)) {
        $id = $params['id'];
    }

    $sedi_ids = array();
    $sede_principale = dci_get_meta('sede_principale','_dci_unita_organizzativa_', $id);

    if ($sede_principale != '') {
        $sedi_ids [] = $sede_principale;
    }

    $altre_sedi [] = dci_get_meta('altre_sedi','_dci_unita_organizzativa_', $id);

    if (!empty($altre_sedi[0])) {
        foreach ($altre_sedi[0] as $sede) {
            if ($sede != $sede_principale) {
                $sedi_ids [] = $sede;
            }
        }
    }

    if (!isset($id)) {
        return array(
            "error" => array(
                "code" =>  400,
                "message" => "Oops, qualcosa è andato storto!"
            ));
    }

    $sedi = array();

    $sedi = get_posts([
        'post_type' => 'luogo',
        'post_status' => 'publish',
        'numberposts' => -1,
        'post__in' => $sedi_ids,
        'order_by' => 'post__in'
    ]);

    foreach ($sedi as $sede) {
        $sede -> indirizzo = dci_get_meta('indirizzo','_dci_luogo_', $sede ->ID);
        $sede -> apertura = dci_get_wysiwyg_field('orario_pubblico','_dci_luogo_', $sede ->ID);
        $sede -> identificativo = dci_get_meta('id','_dci_luogo_', $sede ->ID);
    }

    return $sedi;
}


/**
 * Estendo Wordpress Rest Api
 */
function dci_register_servizi_ufficio_route() {
    register_rest_route('wp/v2', '/servizi/ufficio/', array(
        'methods' => 'GET',
        'callback' => 'dci_get_servizi_ufficio'
    ));
}
add_action('rest_api_init', 'dci_register_servizi_ufficio_route');

/**
 * Espone gli slot prenotabili costruiti dall'orario associato all'Unità organizzativa.
 */
function dci_register_appuntamenti_ufficio_route() {
    register_rest_route('wp/v2', '/appuntamenti/ufficio/', array(
        'methods' => 'GET',
        'callback' => 'dci_get_appuntamenti_ufficio'
    ));
}
add_action('rest_api_init', 'dci_register_appuntamenti_ufficio_route');

/**
 * Espone l'HTML del footer per integrazioni esterne.
 */
function dci_register_footer_export_route() {
    register_rest_route('wp/v2', '/footer/rendered/', array(
        'methods' => 'GET',
        'callback' => 'dci_get_rendered_footer',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'dci_register_footer_export_route');

/**
 * Restituisce uno snapshot HTML del footer del tema.
 *
 * @return array|null
 */
function dci_get_external_footer_payload() {
    $external_only_raw = dci_get_option('ck_portalesoloperusoesterno');
    $is_external_only = in_array(strtolower((string) $external_only_raw), array('1', 'true', 'yes', 'on'), true);
    $external_footer_toggle_raw = dci_get_option('ck_richiama_footer_portale_principale', 'dci_options', 'true');
    $should_fetch_external_footer = in_array(strtolower((string) $external_footer_toggle_raw), array('1', 'true', 'yes', 'on'), true);
    $external_home = trim((string) dci_get_option('url_homesoloesterno'));

    if ($is_external_only && !empty($external_home)) {
        if (!preg_match('#^https?://#i', $external_home)) {
            $external_home = 'https://' . ltrim($external_home, '/');
        }
    }

    if ($is_external_only && $should_fetch_external_footer && !empty($external_home) && filter_var($external_home, FILTER_VALIDATE_URL)) {
        $current_home = trailingslashit(home_url('/'));
        $external_parts = wp_parse_url($external_home);
        $request_args = array(
            'timeout' => 12,
            'redirection' => 5,
            'user-agent' => 'PSR-Theme-Footer-Fetch/1.0 (+'. home_url('/') .')',
            'sslverify' => false,
        );
        $attempted_sources = array();

        $candidate_homes = array();
        $candidate_homes[] = trailingslashit($external_home);
        if (!empty($external_parts['scheme']) && !empty($external_parts['host'])) {
            $root_home = $external_parts['scheme'] . '://' . $external_parts['host'] . '/';
            $candidate_homes[] = $root_home;

            if (!empty($external_parts['path'])) {
                $path = '/' . ltrim($external_parts['path'], '/');
                $candidate_homes[] = trailingslashit($external_parts['scheme'] . '://' . $external_parts['host'] . untrailingslashit($path));

                if (strpos($path, '/index.php/') !== false) {
                    $before_index = strstr($path, '/index.php/', true);
                    if ($before_index !== false && $before_index !== '') {
                        $candidate_homes[] = trailingslashit($external_parts['scheme'] . '://' . $external_parts['host'] . $before_index);
                    }
                }

                $dirname_path = untrailingslashit(wp_normalize_path(dirname($path)));
                if ($dirname_path !== '' && $dirname_path !== '.' && $dirname_path !== '/') {
                    $candidate_homes[] = trailingslashit($external_parts['scheme'] . '://' . $external_parts['host'] . $dirname_path);
                }
            }
        }
        $candidate_homes = array_values(array_unique(array_filter($candidate_homes)));

        // Evita loop su stesso host.
        $current_host = parse_url($current_home, PHP_URL_HOST);
        foreach ($candidate_homes as $candidate_home) {
            if (parse_url($candidate_home, PHP_URL_HOST) === $current_host) {
                continue;
            }

            $external_api = trailingslashit($candidate_home) . 'wp-json/wp/v2/footer/rendered/';
            $attempted_sources[] = $external_api;
            $api_response = wp_remote_get($external_api, $request_args);

            if (!is_wp_error($api_response) && wp_remote_retrieve_response_code($api_response) === 200) {
                $payload = json_decode(wp_remote_retrieve_body($api_response), true);
                if (is_array($payload) && !empty($payload['html'])) {
                    $payload['source'] = $external_api;
                    $payload['attempted_sources'] = $attempted_sources;
                    return $payload;
                }
            }
        }

        // Fallback: prova a leggere direttamente homepage esterna ed estrarre il footer.
        foreach ($candidate_homes as $candidate_home) {
            if (parse_url($candidate_home, PHP_URL_HOST) === $current_host) {
                continue;
            }

            $attempted_sources[] = $candidate_home;
            $home_response = wp_remote_get($candidate_home, $request_args);
            if (!is_wp_error($home_response) && wp_remote_retrieve_response_code($home_response) === 200) {
                $external_html = wp_remote_retrieve_body($home_response);
                $footer_html = dci_extract_footer_html($external_html);
                if (!empty($footer_html)) {
                    return array(
                        'success' => true,
                        'generated_at' => current_time('c'),
                        'html' => $footer_html,
                        'source' => $candidate_home,
                        'attempted_sources' => $attempted_sources,
                        'assets' => array(
                            'css' => array(),
                            'js' => array(),
                        ),
                    );
                }
            }
        }
    }

    return null;
}

/**
 * Restituisce uno snapshot HTML del footer del tema.
 *
 * @return array
 */
function dci_get_rendered_footer() {
    $external_payload = dci_get_external_footer_payload();
    if (is_array($external_payload) && !empty($external_payload['html'])) {
        return $external_payload;
    }

    ob_start();
    locate_template('footer.php', true, false);
    $raw = ob_get_clean();

    $footer_html = dci_extract_footer_html($raw);

    return array(
        'success' => true,
        'generated_at' => current_time('c'),
        'html' => $footer_html,
        'source' => home_url('/'),
        'assets' => array(
            'css' => array(
                get_template_directory_uri() . '/assets/css/bootstrap-italia-comuni.min.css',
                get_template_directory_uri() . '/assets/css/comuni.css',
                get_stylesheet_uri(),
            ),
            'js' => array(
                get_template_directory_uri() . '/assets/js/bootstrap-italia.bundle.min.js',
                get_template_directory_uri() . '/assets/js/comuni.js',
            ),
        ),
    );
}

/**
 * Estrae blocco HTML footer/cookiebar da una pagina completa.
 *
 * @param string $html
 * @return string
 */
function dci_extract_footer_html($html) {
    if (!is_string($html) || $html === '') {
        return '';
    }

    if (preg_match('/<section class="cookiebar[\\s\\S]*?<\\/footer>/i', $html, $matches)) {
        return $matches[0];
    }

    if (preg_match('/<footer[\\s\\S]*?<\\/footer>/i', $html, $matches)) {
        return $matches[0];
    }

    return $html;
}

/**
 * Estrae il contenuto interno del tag <head> da una pagina completa.
 *
 * @param string $html
 * @return string
 */
function dci_extract_head_html($html) {
    if (!is_string($html) || $html === '') {
        return '';
    }

    if (preg_match('/<head[^>]*>([\\s\\S]*?)<\\/head>/i', $html, $matches)) {
        $head_inner = $matches[1];
        // Evita di importare script inline/esterni del portale sorgente che possono alterare il comportamento locale (es. prompt sul cerca).
        $head_inner = preg_replace('/<script\\b[^>]*>[\\s\\S]*?<\\/script>/i', '', $head_inner);
        return trim($head_inner);
    }

    return '';
}

/**
 * Estrae il blocco <header> da una pagina completa.
 *
 * @param string $html
 * @return string
 */
function dci_extract_header_html($html) {
    if (!is_string($html) || $html === '') {
        return '';
    }

    if (preg_match('/<header[\\s\\S]*?<\\/header>/i', $html, $matches)) {
        return trim($matches[0]);
    }

    return '';
}

/**
 * Recupera il blocco head del portale principale (se configurato).
 *
 * @return string
 */
function dci_get_external_head_html() {
    $external_only_raw = dci_get_option('ck_portalesoloperusoesterno');
    $is_external_only = in_array(strtolower((string) $external_only_raw), array('1', 'true', 'yes', 'on'), true);
    $external_head_toggle_raw = dci_get_option('ck_richiama_head_portale_principale', 'dci_options', 'false');
    $should_fetch_external_head = in_array(strtolower((string) $external_head_toggle_raw), array('1', 'true', 'yes', 'on'), true);
    $external_home = trim((string) dci_get_option('url_homesoloesterno'));

    if (!$is_external_only || !$should_fetch_external_head) {
        return '';
    }

    if (!empty($external_home) && !preg_match('#^https?://#i', $external_home)) {
        $external_home = 'https://' . ltrim($external_home, '/');
    }

    if (empty($external_home) || !filter_var($external_home, FILTER_VALIDATE_URL)) {
        return '';
    }

    $external_parts = wp_parse_url($external_home);
    if (empty($external_parts['scheme']) || empty($external_parts['host'])) {
        return '';
    }

    $candidate_homes = array();
    $candidate_homes[] = trailingslashit($external_home);
    $candidate_homes[] = $external_parts['scheme'] . '://' . $external_parts['host'] . '/';
    if (!empty($external_parts['path'])) {
        $path = '/' . ltrim($external_parts['path'], '/');
        $candidate_homes[] = trailingslashit($external_parts['scheme'] . '://' . $external_parts['host'] . untrailingslashit($path));

        if (strpos($path, '/index.php/') !== false) {
            $before_index = strstr($path, '/index.php/', true);
            if ($before_index !== false && $before_index !== '') {
                $candidate_homes[] = trailingslashit($external_parts['scheme'] . '://' . $external_parts['host'] . $before_index);
            }
        }

        $dirname_path = untrailingslashit(wp_normalize_path(dirname($path)));
        if ($dirname_path !== '' && $dirname_path !== '.' && $dirname_path !== '/') {
            $candidate_homes[] = trailingslashit($external_parts['scheme'] . '://' . $external_parts['host'] . $dirname_path);
        }
    }
    $candidate_homes = array_values(array_unique(array_filter($candidate_homes)));

    $request_args = array(
        'timeout' => 12,
        'redirection' => 5,
        'user-agent' => 'PSR-Theme-Head-Fetch/1.0 (+'. home_url('/') .')',
        'sslverify' => false,
    );

    foreach ($candidate_homes as $candidate_home) {
        $response = wp_remote_get($candidate_home, $request_args);
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $head_html = dci_extract_head_html(wp_remote_retrieve_body($response));
            if (!empty($head_html)) {
                return $head_html;
            }
        }
    }

    return '';
}

/**
 * Recupera il blocco header del portale principale (se configurato).
 *
 * @return string
 */
function dci_get_external_header_html() {
    $external_only_raw = dci_get_option('ck_portalesoloperusoesterno');
    $is_external_only = in_array(strtolower((string) $external_only_raw), array('1', 'true', 'yes', 'on'), true);
    $external_head_toggle_raw = dci_get_option('ck_richiama_head_portale_principale', 'dci_options', 'false');
    $should_fetch_external_header = in_array(strtolower((string) $external_head_toggle_raw), array('1', 'true', 'yes', 'on'), true);
    $external_home = trim((string) dci_get_option('url_homesoloesterno'));

    if (!$is_external_only || !$should_fetch_external_header) {
        return '';
    }

    if (!empty($external_home) && !preg_match('#^https?://#i', $external_home)) {
        $external_home = 'https://' . ltrim($external_home, '/');
    }
    if (empty($external_home) || !filter_var($external_home, FILTER_VALIDATE_URL)) {
        return '';
    }

    $external_parts = wp_parse_url($external_home);
    if (empty($external_parts['scheme']) || empty($external_parts['host'])) {
        return '';
    }

    $candidate_homes = array();
    $candidate_homes[] = trailingslashit($external_home);
    $candidate_homes[] = $external_parts['scheme'] . '://' . $external_parts['host'] . '/';
    $candidate_homes = array_values(array_unique(array_filter($candidate_homes)));

    $request_args = array(
        'timeout' => 12,
        'redirection' => 5,
        'user-agent' => 'PSR-Theme-Header-Fetch/1.0 (+'. home_url('/') .')',
        'sslverify' => false,
    );

    foreach ($candidate_homes as $candidate_home) {
        $response = wp_remote_get($candidate_home, $request_args);
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $header_html = dci_extract_header_html(wp_remote_retrieve_body($response));
            if (!empty($header_html)) {
                return $header_html;
            }
        }
    }

    return '';
}

/**
 * Genera gli slot appuntamento del mese richiesto a partire dall'orario UO.
 *
 * @param WP_REST_Request $request
 * @return array
 */
function dci_get_appuntamenti_ufficio(WP_REST_Request $request) {
    $office_id = absint($request->get_param('id'));
    $month = absint($request->get_param('month'));
    $year = absint($request->get_param('year'));

    if ($office_id <= 0) {
        return array(
            "error" => array(
                "code" => 400,
                "message" => "Parametro id mancante o non valido."
            )
        );
    }

    if ($month < 1 || $month > 12) {
        $month = (int) wp_date('n');
    }
    if ($year < 1) {
        $year = (int) wp_date('Y');
    }

    $orario_id = absint(dci_get_meta('orario_uo', '_dci_unita_organizzativa_', $office_id));
    if ($orario_id <= 0) {
        return array();
    }

    $prefix = '_dci_orario_';
    $data_inizio_raw = get_post_meta($orario_id, $prefix . 'data_inizio', true);
    $data_fine_raw = get_post_meta($orario_id, $prefix . 'data_fine', true);

    $data_inizio = DateTime::createFromFormat('d-m-Y', $data_inizio_raw) ?: null;
    $data_fine = DateTime::createFromFormat('d-m-Y', $data_fine_raw) ?: null;
    if (!$data_inizio || !$data_fine) {
        return array();
    }

    $first_day = DateTime::createFromFormat('Y-n-j H:i:s', $year . '-' . $month . '-1 00:00:00');
    $last_day = clone $first_day;
    $last_day->modify('last day of this month')->setTime(23, 59, 59);

    $slot_duration = 45; // minuti
    $giorni_map = array(
        1 => 'lun',
        2 => 'mar',
        3 => 'mer',
        4 => 'gio',
        5 => 'ven',
        6 => 'sab',
        7 => 'dom',
    );

    $slots = array();
    $cursor = clone $first_day;
    $now = new DateTime('now', wp_timezone());

    while ($cursor <= $last_day) {
        if ($cursor >= $data_inizio && $cursor <= $data_fine && !dci_is_festivo_nazionale($cursor)) {
            $weekday = (int) $cursor->format('N');
            $day_prefix = $giorni_map[$weekday];

            $mattina = get_post_meta($orario_id, $prefix . $day_prefix . '_mattina', true);
            $pomeriggio = get_post_meta($orario_id, $prefix . $day_prefix . '_pomeriggio', true);

            $fasce = array_filter(array($mattina, $pomeriggio));
            foreach ($fasce as $fascia) {
                $parsed = dci_parse_orario_range($fascia);
                if (!$parsed) {
                    continue;
                }

                list($start_h, $start_m, $end_h, $end_m) = $parsed;
                $slot_start = (clone $cursor)->setTime($start_h, $start_m, 0);
                $slot_end_limit = (clone $cursor)->setTime($end_h, $end_m, 0);

                while ((clone $slot_start)->modify('+' . $slot_duration . ' minutes') <= $slot_end_limit) {
                    $slot_end = (clone $slot_start)->modify('+' . $slot_duration . ' minutes');
                    if ($slot_start <= $now) {
                        $slot_start = $slot_end;
                        continue;
                    }

                    $slots[] = array(
                        'startDate' => $slot_start->format('Y-m-d\TH:i'),
                        'endDate' => $slot_end->format('Y-m-d\TH:i'),
                    );
                    $slot_start = $slot_end;
                }
            }
        }

        $cursor->modify('+1 day')->setTime(0, 0, 0);
    }

    return $slots;
}

/**
 * Verifica se una data ricade in un giorno festivo nazionale.
 *
 * @param DateTime $date
 * @return bool
 */
function dci_is_festivo_nazionale(DateTime $date) {
    // Tutte le domeniche sono giorni festivi a livello nazionale.
    if ((int) $date->format('N') === 7) {
        return true;
    }

    $fixed_holidays = array(
        '01-01', // Capodanno
        '01-06', // Epifania
        '04-25', // Liberazione
        '05-01', // Festa del Lavoro
        '06-02', // Festa della Repubblica
        '08-15', // Ferragosto
        '11-01', // Ognissanti
        '12-08', // Immacolata
        '12-25', // Natale
        '12-26', // Santo Stefano
    );
    $day_month = $date->format('m-d');
    if (in_array($day_month, $fixed_holidays, true)) {
        return true;
    }

    // Festività regionale applicata solo ai comuni con regione impostata su Sicilia.
    if (dci_is_regione_sicilia() && $day_month === '05-15') {
        return true;
    }

    $year = (int) $date->format('Y');
    $easter_timestamp = easter_date($year);
    $easter = (new DateTime('@' . $easter_timestamp))->setTimezone(wp_timezone());
    $easter_monday = (clone $easter)->modify('+1 day');

    return $date->format('Y-m-d') === $easter->format('Y-m-d') ||
        $date->format('Y-m-d') === $easter_monday->format('Y-m-d');
}

/**
 * Verifica se il comune configurato appartiene alla Regione Sicilia.
 *
 * @return bool
 */
function dci_is_regione_sicilia() {
    $nome_regione = dci_get_option('nome_regione');
    if (!is_string($nome_regione)) {
        return false;
    }

    return sanitize_title($nome_regione) === 'sicilia';
}

/**
 * Parsing range orario "08:30 - 12:30".
 *
 * @param string $range
 * @return array|null
 */
function dci_parse_orario_range($range) {
    if (!is_string($range) || trim($range) === '') {
        return null;
    }

    if (!preg_match('/^\s*(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})\s*$/', $range, $matches)) {
        return null;
    }

    $start_h = (int) $matches[1];
    $start_m = (int) $matches[2];
    $end_h = (int) $matches[3];
    $end_m = (int) $matches[4];

    if ($start_h > 23 || $end_h > 23 || $start_m > 59 || $end_m > 59) {
        return null;
    }

    if ($end_h < $start_h || ($end_h === $start_h && $end_m <= $start_m)) {
        return null;
    }

    return array($start_h, $start_m, $end_h, $end_m);
}

/**
 * restituisce i servizi che sono disponibili presso l'Unità Organizzativa passata come parametro (id o title)
 * @param WP_REST_Request $request
 * @return array[]
 */
function dci_get_servizi_ufficio(WP_REST_Request $request) {

    $params = $_GET;
    if (array_key_exists('title', $params)) {
        $ufficio  = get_page_by_title($params['title'], OBJECT, 'unita_organizzativa');
        $id = $ufficio -> ID;
    }
    else if (array_key_exists('id', $params)) {
        $id = $params['id'];
    }

    if (!isset($id)) {
        return array(
            "error" => array(
                "code" =>  400,
                "message" => "Oops, qualcosa è andato storto!"
            ));
    }

    $servizi_ids = array();
    $servizi_ids = dci_get_meta('elenco_servizi_offerti','_dci_unita_organizzativa_', $id);

    $servizi = array();

    if (!empty($servizi_ids)) {
        $servizi = get_posts([
            'post_type' => 'servizio',
            'post_status' => 'publish',
            'numberposts' => -1,
            'post__in' => $servizi_ids,
            'order_by' => 'post__in'
        ]);
    }

    return $servizi;
}


/**
 * enqueue script dci-rating
 */
function dci_enqueue_dci_rating_script() {
    wp_enqueue_script( 'dci-rating', get_template_directory_uri() . '/assets/js/rating.js', array('jquery'), null, true );
    $variables = array(
        'ajaxurl' => admin_url( 'admin-ajax.php' )
    );
    wp_localize_script('dci-rating', "data", $variables);
}
add_action( 'wp_enqueue_scripts', 'dci_enqueue_dci_rating_script' );

/**
 * crea contenuto di tipo Rating
 */
function dci_save_rating(){

    $params = json_decode(json_encode($_POST), true);

    if((array_key_exists("title", $params)) && ($params['title']!= null)) {
        $postId = wp_insert_post(array(
            'post_type' => 'rating',
            'post_title' =>  $params['title']
        ));
    }

    if($postId == 0) {
        echo json_encode(array(
            "success" => false,
            "error" => array(
                "code" =>  400,
                "message" => "Oops, qualcosa è andato storto!"
            )));
        wp_die();
    }

    if(array_key_exists("star", $params) && $params['star'] != "null") {
        wp_set_object_terms($postId, $params['star'], "stars");
        update_post_meta($postId, '_dci_rating_stelle',  $params['star']);

    }

    if(array_key_exists("radioResponse", $params) && $params['radioResponse'] != "null") {
        update_post_meta($postId, '_dci_rating_risposta_chiusa',  $params['radioResponse']);
    }

    if(array_key_exists("freeText", $params) && $params['freeText'] != "null") {
        update_post_meta($postId, '_dci_rating_risposta_aperta',  $params['freeText']);
    }

    if(array_key_exists("page", $params) && $params['page'] != "null") {
        update_post_meta($postId, '_dci_rating_url',  $params['page']);
        wp_set_object_terms($postId, $params['page'], "page_urls");
    }

    echo json_encode(array(
        "success" => true,
        "rating" => array(
            "id" => $postId)
    ));
    wp_die();
}
add_action("wp_ajax_save_rating" , "dci_save_rating");
add_action("wp_ajax_nopriv_save_rating" , "dci_save_rating");


/**
 * crea contenuto di tipo Richiesta Assistenza
 */
function dci_save_richiesta_assistenza(){

    $params = json_decode(json_encode($_POST), true);

    date_default_timezone_set('Europe/Rome');
    $start = date('Y-m-d H:i:s');
    $timestamp = date_create($start,new DateTimeZone('Z'))->format('Y-m-d\TH:i:s.ve');

    if(array_key_exists("nome", $params) && array_key_exists("cognome", $params) && array_key_exists("email", $params) && array_key_exists("servizio", $params) ) {
        $ticket_title = 'ticket_'.$timestamp;
        $postId = wp_insert_post(array(
            'post_type' => 'richiesta_assistenza',
            'post_title' =>  $ticket_title,
            'post_status' => 'publish'
        ));
    }

    if($postId == 0) {
        echo json_encode(array(
            "success" => false,
            "error" => array(
                "code" =>  400,
                "message" => "Oops, qualcosa è andato storto!"
            )));
        wp_die();
    }

    if(array_key_exists("nome", $params) && $params['nome'] != "null") {
        update_post_meta($postId, '_dci_richiesta_assistenza_nome',  $params['nome']);
    }

    if(array_key_exists("cognome", $params) && $params['cognome'] != "null") {
        update_post_meta($postId, '_dci_richiesta_assistenza_cognome',  $params['cognome']);
    }

    if(array_key_exists("email", $params) && $params['email'] != "null") {
        update_post_meta($postId, '_dci_richiesta_assistenza_email',  $params['email']);
    }

    if(array_key_exists("categoria_servizio", $params) && $params['categoria_servizio'] != "null") {
        $categoria = get_term_by('term_id', $params['categoria_servizio'], 'categorie_servizio');
        update_post_meta($postId, '_dci_richiesta_assistenza_categoria_servizio', $categoria->name );
    }

    if(array_key_exists("servizio", $params) && $params['servizio'] != "null") {
        update_post_meta($postId, '_dci_richiesta_assistenza_servizio',  $params['servizio']);
    }

    if(array_key_exists("dettagli", $params) && $params['dettagli'] != "null") {
        update_post_meta($postId, '_dci_richiesta_assistenza_dettagli',  $params['dettagli']);
    }

    $mail_sent = dci_send_richiesta_assistenza_notification($postId, $params, $ticket_title);

    echo json_encode(array(
        "success" => true,
        "mail_sent" => $mail_sent,
        "richiesta_assistenza" => array(
            "id" => $postId),
        "title" => $ticket_title
    ));
    wp_die();
}
add_action("wp_ajax_save_richiesta_assistenza" , "dci_save_richiesta_assistenza");
add_action("wp_ajax_nopriv_save_richiesta_assistenza" , "dci_save_richiesta_assistenza");

/**
 * Invia notifica e-mail per nuove richieste assistenza/disservizio.
 *
 * @param int   $postId
 * @param array $params
 * @param string $ticket_title
 * @return bool
 */
function dci_send_richiesta_assistenza_notification($postId, $params, $ticket_title) {
    if (empty($postId)) {
        return false;
    }

    $recipients = array();
    $email_principale = dci_get_option('email_principale');
    if (is_email($email_principale)) {
        $recipients[] = $email_principale;
    }

    $admin_email = get_option('admin_email');
    if (is_email($admin_email)) {
        $recipients[] = $admin_email;
    }

    $recipients = array_values(array_unique(array_filter($recipients)));
    if (empty($recipients)) {
        return false;
    }

    $subject = sprintf('[%s] Nuova segnalazione disservizio', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));
    $message = implode("\n", array(
        'È stata creata una nuova segnalazione di disservizio.',
        '',
        'ID richiesta: ' . $postId,
        'Ticket: ' . $ticket_title,
        'Nome: ' . ($params['nome'] ?? ''),
        'Cognome: ' . ($params['cognome'] ?? ''),
        'Email: ' . ($params['email'] ?? ''),
        'Servizio: ' . ($params['servizio'] ?? ''),
        'Dettagli: ' . ($params['dettagli'] ?? ''),
        '',
        'Link nel pannello admin: ' . admin_url('post.php?post=' . $postId . '&action=edit'),
    ));

    $headers = array('Content-Type: text/plain; charset=UTF-8');
    if (is_email($email_principale)) {
        $headers[] = 'From: ' . wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES) . ' <' . $email_principale . '>';
    }
    if (is_email($params['email'] ?? '')) {
        $headers[] = 'Reply-To: ' . $params['email'];
    }

    $result = true;
    foreach ($recipients as $to) {
        $sent = wp_mail($to, $subject, $message, $headers);
        $result = $result && $sent;
    }

    return $result;
}

/**
 * crea contenuto di tipo Appuntamento
 */
function dci_save_appuntamento(){

    $params = json_decode(json_encode($_POST), true);
    $postId = 0;
    $appuntamento_title = '';

    date_default_timezone_set('Europe/Rome');
    $data = date('Y-m-d\TH:i:s');

    if(array_key_exists("name", $params) && array_key_exists("email", $params) &&  array_key_exists("surname", $params) && array_key_exists("moreDetails", $params) && array_key_exists("service", $params)  && array_key_exists("office", $params) ) {

        $appuntamento_title = $params['surname'].' '.$params['name'].'';

        $postId = wp_insert_post(array(
            'post_type' => 'appuntamento',
            'post_title' =>  $appuntamento_title,
            'post_status' => 'publish'
        ));
    }

    if($postId == 0) {
        echo json_encode(array(
            "success" => false,
            "error" => array(
                "code" =>  400,
                "message" => "Oops, qualcosa è andato storto!"
            )));
        wp_die();
    }

    update_post_meta($postId, '_dci_appuntamento_data_ora_prenotazione',  $data);

    if(array_key_exists("email", $params) && $params['email'] != "null") {
        update_post_meta($postId, '_dci_appuntamento_email_richiedente',  $params['email']);
    }

    if(array_key_exists("moreDetails", $params) && $params['moreDetails'] != "null") {
        update_post_meta($postId, '_dci_appuntamento_dettaglio_richiesta',  $params['moreDetails']);
    }

    if(array_key_exists("service", $params) && $params['service'] != "null") {
        $service_obj = json_decode(stripslashes($params['service']), true);
        //$service_id = $service_obj['id'];
        update_post_meta($postId, '_dci_appuntamento_servizio',$service_obj['name']);
    }

    if(array_key_exists("office", $params) && $params['office'] != "null") {
        $office_obj = json_decode(stripslashes($params['office']), true);
        //$office_id = $office_obj['id'];
        update_post_meta($postId, '_dci_appuntamento_unita_organizzativa', $office_obj['name']);
    }

    if(array_key_exists("appointment", $params) && $params['appointment'] != "null") {

        $appointment_obj = json_decode(stripslashes($params['appointment']), true);
        $startDate = $appointment_obj['startDate'];
        $endDate = $appointment_obj['endDate'];

        update_post_meta($postId, '_dci_appuntamento_data_ora_inizio_appuntamento',  $startDate );
        update_post_meta($postId, '_dci_appuntamento_data_ora_fine_appuntamento',  $endDate);
    }

    $mail_sent = dci_send_appuntamento_notification($postId, $params, $data);

    echo json_encode(array(
        "success" => true,
        "message" => 'Contenuto creato con successo: '.$postId,
        "mail_sent" => $mail_sent,
        "appuntamento" => array(
            "id" => $postId),
        "title" => $appuntamento_title
    ));
    wp_die();
}



add_action("wp_ajax_save_appuntamento" , "dci_save_appuntamento");
add_action("wp_ajax_nopriv_save_appuntamento" , "dci_save_appuntamento");

/**
 * Invia una notifica e-mail alla creazione di una richiesta appuntamento.
 *
 * @param int    $postId
 * @param array  $params
 * @param string $data
 * @return bool
 */
function dci_send_appuntamento_notification($postId, $params, $data) {
    if (empty($postId)) {
        return false;
    }

    $to = dci_get_option('email_prenota_appuntamento');
    if (empty($to)) {
        $to = dci_get_option('email_principale');
    }
    if (empty($to)) {
        $to = get_option('admin_email');
    }
    if (!is_email($to)) {
        return false;
    }

    $service_name = '';
    if (array_key_exists('service', $params) && $params['service'] != "null") {
        $service_obj = json_decode(stripslashes($params['service']), true);
        $service_name = $service_obj['name'] ?? '';
    }

    $office_name = '';
    if (array_key_exists('office', $params) && $params['office'] != "null") {
        $office_obj = json_decode(stripslashes($params['office']), true);
        $office_name = $office_obj['name'] ?? '';
    }

    $appointment_start = '';
    $appointment_end = '';
    if (array_key_exists('appointment', $params) && $params['appointment'] != "null") {
        $appointment_obj = json_decode(stripslashes($params['appointment']), true);
        $appointment_start = $appointment_obj['startDate'] ?? '';
        $appointment_end = $appointment_obj['endDate'] ?? '';
    }

    $subject = sprintf('[%s] Nuova richiesta prenotazione appuntamento', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));
    $message_lines = array(
        'È stata creata una nuova richiesta di prenotazione appuntamento.',
        '',
        'ID richiesta: ' . $postId,
        'Data richiesta: ' . $data,
        'Nome: ' . ($params['name'] ?? ''),
        'Cognome: ' . ($params['surname'] ?? ''),
        'Email richiedente: ' . ($params['email'] ?? ''),
        'Ufficio: ' . $office_name,
        'Servizio: ' . $service_name,
        'Dettagli: ' . ($params['moreDetails'] ?? ''),
        'Inizio appuntamento: ' . $appointment_start,
        'Fine appuntamento: ' . $appointment_end,
        '',
        'Link nel pannello admin: ' . admin_url('post.php?post=' . $postId . '&action=edit'),
    );
    $message = implode("\n", $message_lines);
    $headers = array('Content-Type: text/plain; charset=UTF-8');

    $from = dci_get_option('email_principale');
    if (is_email($from)) {
        $headers[] = 'From: ' . wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES) . ' <' . $from . '>';
    }

    $requester_email = $params['email'] ?? '';
    if (is_email($requester_email)) {
        $headers[] = 'Reply-To: ' . $requester_email;
    }

    return wp_mail($to, $subject, $message, $headers);
}
