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
 * Espone i titolari dell'amministrazione politica con ruoli e dati essenziali.
 */
function dci_register_amministrazione_politica_route() {
    register_rest_route('wp/v2', '/amministrazione-politica/', array(
        'methods' => 'GET',
        'callback' => 'dci_get_amministrazione_politica',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'dci_register_amministrazione_politica_route');

/**
 * Espone gli uffici e i relativi responsabili (se presenti).
 */
function dci_register_uffici_responsabili_route() {
    register_rest_route('wp/v2', '/uffici/responsabili/', array(
        'methods' => 'GET',
        'callback' => 'dci_get_uffici_responsabili',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'dci_register_uffici_responsabili_route');

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
 * Espone l'HTML dell'header per integrazioni esterne.
 */
function dci_register_header_export_route() {
    register_rest_route('wp/v2', '/header/rendered/', array(
        'methods' => 'GET',
        'callback' => 'dci_get_rendered_header',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'dci_register_header_export_route');

/**
 * Restituisce il markup loader da mostrare mentre header/footer sono letti via API.
 *
 * @param string $label
 * @return string
 */
function dci_get_external_fragment_loader_html($label = 'Caricamento contenuto') {
    return '<div class="dci-api-fragment-loader" role="status" aria-live="polite">'
        . '<span class="dci-api-fragment-loader__spinner" aria-hidden="true"></span>'
        . '<span class="dci-api-fragment-loader__text">' . esc_html($label) . '</span>'
        . '</div>';
}

/**
 * Verifica valori booleani salvati dalle opzioni CMB2/radio del tema.
 *
 * @param mixed $value
 * @return bool
 */
function dci_is_truthy_option_value($value) {
    return in_array(strtolower((string) $value), array('1', 'true', 'yes', 'on'), true);
}

/**
 * Verifica se il portale esterno è abilitato.
 *
 * @return bool
 */
function dci_is_external_portal_enabled() {
    return dci_is_truthy_option_value(dci_get_option('ck_portalesoloperusoesterno'));
}

/**
 * Verifica il check "Richiama head da Url Home".
 *
 * @return bool
 */
function dci_should_fetch_external_head() {
    return dci_is_truthy_option_value(dci_get_option('ck_richiama_head_portale_principale', 'dci_options', 'false'));
}

/**
 * Verifica il check "Richiama footer da Url Home".
 *
 * @return bool
 */
function dci_should_fetch_external_footer() {
    return dci_is_truthy_option_value(dci_get_option('ck_richiama_footer_portale_principale', 'dci_options', 'false'));
}



/**
 * Restituisce la base pubblica da usare negli HTML header/footer esportati via API.
 *
 * @return string
 */
function dci_get_api_fragment_public_base_url() {
    $public_home = trim((string) dci_get_option('url_homesoloesterno'));

    if (dci_is_external_portal_enabled() && $public_home !== '') {
        if (!preg_match('#^https?://#i', $public_home)) {
            $public_home = 'https://' . ltrim($public_home, '/');
        }

        if (filter_var($public_home, FILTER_VALIDATE_URL)) {
            return trailingslashit($public_home);
        }
    }

    return home_url('/');
}

/**
 * Verifica se la firma Point Service deve essere visibile nel footer locale.
 *
 * @return bool
 */
function dci_should_show_footer_signature() {
    return !dci_is_truthy_option_value(dci_get_option('firma_nostra'));
}

/**
 * Restituisce la firma Point Service usata dal footer standard.
 *
 * @param string $text_color Colore testo firma, utile per footer API su sfondo chiaro.
 * @return string
 */
function dci_get_footer_signature_html($text_color = '#fff') {
    if (!dci_should_show_footer_signature()) {
        return '';
    }

    $text_color = in_array(strtolower((string) $text_color), array('#000', '#000000'), true) ? '#000000' : '#fff';
    $signature_style = 'color: ' . $text_color . ' !important;';

    return '&nbsp;&nbsp;-&nbsp;&nbsp;<span style="' . esc_attr($signature_style) . '">Sviluppato da </span>'
        . '<a class="text-primary" style="text-decoration:none; ' . esc_attr($signature_style) . '" target="_blank" href="https://www.p-service.it/" title="Point Service S.r.l" aria-label="Point Service S.r.l" aria-labelledby="footerCompanyLabel">'
        . '<span id="footerCompanyLabel" style="' . esc_attr($signature_style) . '">'
        . '&nbsp;Point Service S.r.l'
        . '</span>'
        . '</a>';
}

/**
 * Applica la firma locale al footer recuperato via API quando il check locale lo richiede.
 *
 * @param string $html
 * @return string
 */
function dci_apply_local_footer_signature($html) {
    if (!is_string($html) || $html === '' || !dci_should_show_footer_signature()) {
        return $html;
    }

    if (stripos($html, 'p-service.it') !== false || stripos($html, 'Point Service S.r.l') !== false) {
        return $html;
    }

    $signature_html = dci_get_footer_signature_html('#000000');
    if ($signature_html === '') {
        return $html;
    }

    $pattern = '/(<small\b[^>]*>\s*©[\s\S]*?)(<\/small>)/i';
    if (preg_match($pattern, $html)) {
        return preg_replace($pattern, '$1' . $signature_html . '$2', $html, 1);
    }

    $fallback_html = '<div class="footer-bottom"><ul class="it-footer-small-prints-list list-inline mb-0 d-flex flex-column flex-md-row" style="float: right;">'
        . '<li class="list-inline-item d-flex"><small>© ' . esc_html(dci_get_option('nome_comune')) . $signature_html . '</small></li>'
        . '</ul></div>';

    if (stripos($html, '</footer>') !== false) {
        return preg_replace('/<\/footer>/i', $fallback_html . '</footer>', $html, 1);
    }

    return $html . $fallback_html;
}

/**
 * Stampa nel <head> locale le risorse recuperate dal portale principale, se abilitate.
 */
function dci_print_external_head_html() {
    $head_html = dci_get_external_head_html();
    if ($head_html !== '') {
        echo "\n" . $head_html . "\n";
    }
}
add_action('wp_head', 'dci_print_external_head_html', 20);

/**
 * Restituisce uno snapshot HTML del footer del tema.
 *
 * @return array|null
 */
function dci_get_external_footer_payload() {
    $is_external_only = dci_is_external_portal_enabled();
    $should_fetch_external_footer = dci_should_fetch_external_footer();
    $external_home = trim((string) dci_get_option('url_homesoloesterno'));

    if ($is_external_only && !empty($external_home)) {
        if (!preg_match('#^https?://#i', $external_home)) {
            $external_home = 'https://' . ltrim($external_home, '/');
        }
    }

    $footer_cache_key = 'dci_ext_footer_v2_' . md5((string) $external_home);
    $footer_cached = get_transient($footer_cache_key);
    if (is_array($footer_cached)) {
        if (!empty($footer_cached['html'])) {
            $footer_cached['html'] = dci_apply_local_footer_signature($footer_cached['html']);
            return $footer_cached;
        }
        return null;
    }

    if ($is_external_only && $should_fetch_external_footer && !empty($external_home) && filter_var($external_home, FILTER_VALIDATE_URL)) {
        $cache_key = 'dci_ext_footer_v2_' . md5(strtolower((string) $external_home) . '|' . home_url('/'));
        $cached_payload = get_transient($cache_key);
        if (is_array($cached_payload)) {
            if (!empty($cached_payload['html'])) {
                $cached_payload['html'] = dci_apply_local_footer_signature($cached_payload['html']);
                return $cached_payload;
            }
            return null;
        }

        $current_home = trailingslashit(home_url('/'));
        $external_parts = wp_parse_url($external_home);
        $request_args = array(
            'timeout' => 4,
            'redirection' => 3,
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
                    $payload['html'] = dci_absolutize_external_html_urls($payload['html'], $external_home);
                    $payload['source'] = $external_api;
                    $payload['attempted_sources'] = $attempted_sources;
                    $payload['loading_html'] = dci_get_external_fragment_loader_html('Caricamento footer');
                    set_transient($footer_cache_key, $payload, 5 * MINUTE_IN_SECONDS);
                    $payload['html'] = dci_apply_local_footer_signature($payload['html']);
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
                    $footer_html = dci_absolutize_external_html_urls($footer_html, $external_home);
                    $result = array(
                        'success' => true,
                        'generated_at' => current_time('c'),
                        'html' => $footer_html,
                        'source' => $candidate_home,
                        'attempted_sources' => $attempted_sources,
                        'assets' => array(
                            'css' => array(),
                            'js' => array(),
                        ),
                        'loading_html' => dci_get_external_fragment_loader_html('Caricamento footer'),
                    );
                    set_transient($footer_cache_key, $result, 5 * MINUTE_IN_SECONDS);
                    $result['html'] = dci_apply_local_footer_signature($result['html']);
                    return $result;
                }
            }
        }

        // Negative cache per evitare timeout ripetuti ad ogni request.
        set_transient($cache_key, array('html' => ''), 2 * MINUTE_IN_SECONDS);
    }

    set_transient($footer_cache_key, array('success' => false, 'html' => ''), MINUTE_IN_SECONDS);
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
    $footer_html = dci_absolutize_external_html_urls($footer_html, home_url('/'));

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
        'loading_html' => dci_get_external_fragment_loader_html('Caricamento footer'),
    );
}

/**
 * Restituisce uno snapshot HTML dell'header del tema.
 *
 * @return array
 */
function dci_get_rendered_header() {
    // L'endpoint API deve esportare l'header del sito corrente, non proxyare menu/pulsanti del portale esterno.
    ob_start();
    locate_template('header.php', true, false);
    $raw = ob_get_clean();
    $header_html = dci_extract_header_html($raw);
    $header_html = dci_absolutize_external_html_urls($header_html, dci_get_api_fragment_public_base_url(), home_url('/'));

    return array(
        'success' => true,
        'generated_at' => current_time('c'),
        'html' => $header_html,
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
        'loading_html' => dci_get_external_fragment_loader_html('Caricamento header'),
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
 * Converte in assoluti gli URL relativi dei frammenti HTML recuperati dal portale principale.
 *
 * @param string $html
 * @param string $external_home
 * @param string $source_home Base locale da sostituire quando l'HTML API contiene URL assoluti del sito tecnico.
 * @return string
 */
function dci_absolutize_external_html_urls($html, $external_home, $source_home = '') {
    if (!is_string($html) || $html === '' || empty($external_home)) {
        return $html;
    }

    if (!preg_match('#^https?://#i', $external_home)) {
        $external_home = 'https://' . ltrim($external_home, '/');
    }

    if (!filter_var($external_home, FILTER_VALIDATE_URL)) {
        return $html;
    }

    $base_url = untrailingslashit($external_home);
    $source_base_url = '';
    if (!empty($source_home)) {
        if (!preg_match('#^https?://#i', $source_home)) {
            $source_home = 'https://' . ltrim($source_home, '/');
        }
        if (filter_var($source_home, FILTER_VALIDATE_URL)) {
            $source_base_url = untrailingslashit($source_home);
        }
    }

    $make_absolute = static function ($url) use ($base_url, $source_base_url) {
        $url = trim((string) $url);

        if ($url === '') {
            return $url;
        }

        if ($source_base_url !== '' && strcasecmp($url, $source_base_url) === 0) {
            return esc_url($base_url);
        }

        if ($source_base_url !== '' && stripos($url, $source_base_url . '/') === 0) {
            return esc_url($base_url . substr($url, strlen($source_base_url)));
        }

        if (strpos($url, '/') !== 0 || strpos($url, '//') === 0) {
            return $url;
        }

        return esc_url($base_url . $url);
    };

    $html = preg_replace_callback('/\b(href|src|action|data-href|data-url)=("|\')([^"\']+)\2/i', function ($matches) use ($make_absolute) {
        $absolute_url = $make_absolute($matches[3]);

        if ($absolute_url === trim((string) $matches[3])) {
            return $matches[0];
        }

        return $matches[1] . '=' . $matches[2] . $absolute_url . $matches[2];
    }, $html);

    $html = preg_replace_callback('/\b((?:window\.|document\.)?location(?:\.href)?)\s*=\s*("|\')([^"\']+)\2/i', function ($matches) use ($make_absolute) {
        $absolute_url = $make_absolute($matches[3]);

        if ($absolute_url === trim((string) $matches[3])) {
            return $matches[0];
        }

        return $matches[1] . '=' . $matches[2] . $absolute_url . $matches[2];
    }, $html);

    return preg_replace_callback('/url\(("|\')?([^\)"\']+)\1\)/i', function ($matches) use ($make_absolute) {
        $absolute_url = $make_absolute($matches[2]);

        if ($absolute_url === trim((string) $matches[2])) {
            return $matches[0];
        }

        $quote = isset($matches[1]) ? $matches[1] : '';
        return 'url(' . $quote . $absolute_url . $quote . ')';
    }, $html);
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
    $is_external_only = dci_is_external_portal_enabled();
    $should_fetch_external_head = dci_should_fetch_external_head();
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

    $head_cache_key = 'dci_ext_head_v2_' . md5((string) $external_home);
    $head_cached = get_transient($head_cache_key);
    if (is_string($head_cached)) {
        return ($head_cached === '__empty__') ? '' : $head_cached;
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

    $cache_key = 'dci_ext_head_v2_' . md5(strtolower((string) $external_home) . '|' . home_url('/'));
    $cached_head = get_transient($cache_key);
    if (is_array($cached_head)) {
        return !empty($cached_head['html']) ? $cached_head['html'] : '';
    }

    $external_html = dci_get_external_home_snapshot($external_home, $candidate_homes);
    if ($external_html === '') {
        set_transient($cache_key, array('html' => ''), 2 * MINUTE_IN_SECONDS);
        return '';
    }

    $head_html = dci_extract_head_html($external_html);
    if (!empty($head_html)) {
        $head_html = dci_absolutize_external_html_urls($head_html, $external_home);
        set_transient($cache_key, array('html' => $head_html), 10 * MINUTE_IN_SECONDS);
        return $head_html;
    }

    set_transient($cache_key, array('html' => ''), 2 * MINUTE_IN_SECONDS);
    return '';
}

/**
 * Recupera uno snapshot HTML della home esterna con cache.
 *
 * @param string $external_home
 * @param array  $candidate_homes
 * @return string
 */
function dci_get_external_home_snapshot($external_home, $candidate_homes = array()) {
    $cache_key = 'dci_ext_home_html_v2_' . md5(strtolower((string) $external_home) . '|' . home_url('/'));
    $cached_html = get_transient($cache_key);
    if (is_array($cached_html)) {
        return !empty($cached_html['html']) ? (string) $cached_html['html'] : '';
    }

    if (empty($candidate_homes)) {
        $candidate_homes = array(trailingslashit($external_home));
    }

    $request_args = array(
        'timeout' => 4,
        'redirection' => 3,
        'user-agent' => 'PSR-Theme-Head-Fetch/1.0 (+'. home_url('/') .')',
        'sslverify' => false,
    );

    foreach ($candidate_homes as $candidate_home) {
        $response = wp_remote_get($candidate_home, $request_args);
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $html = wp_remote_retrieve_body($response);
            if (!empty($html)) {
                set_transient($cache_key, array('html' => $html), 10 * MINUTE_IN_SECONDS);
                return $html;
            }
        }
    }

    set_transient($cache_key, array('html' => ''), 2 * MINUTE_IN_SECONDS);
    return '';
}

/**
 * Recupera il blocco header del portale principale (se configurato).
 *
 * @return string
 */
function dci_get_external_header_html() {
    $is_external_only = dci_is_external_portal_enabled();
    $should_fetch_external_header = dci_should_fetch_external_head();
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

    $header_cache_key = 'dci_ext_header_v3_' . md5((string) $external_home);
    $header_cached = get_transient($header_cache_key);
    if (is_string($header_cached)) {
        return ($header_cached === '__empty__') ? '' : $header_cached;
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
        'timeout' => 4,
        'redirection' => 3,
        'user-agent' => 'PSR-Theme-Header-Fetch/1.0 (+'. home_url('/') .')',
        'sslverify' => false,
    );

    foreach ($candidate_homes as $candidate_home) {
        $external_api = trailingslashit($candidate_home) . 'wp-json/wp/v2/header/rendered/';
        $api_response = wp_remote_get($external_api, $request_args);

        if (!is_wp_error($api_response) && wp_remote_retrieve_response_code($api_response) === 200) {
            $payload = json_decode(wp_remote_retrieve_body($api_response), true);
            if (is_array($payload) && !empty($payload['html'])) {
                $header_html = dci_absolutize_external_html_urls($payload['html'], $external_home);
                set_transient($header_cache_key, $header_html, 5 * MINUTE_IN_SECONDS);
                return $header_html;
            }
        }
    }

    foreach ($candidate_homes as $candidate_home) {
        $response = wp_remote_get($candidate_home, $request_args);
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $header_html = dci_extract_header_html(wp_remote_retrieve_body($response));
            if (!empty($header_html)) {
                $header_html = dci_absolutize_external_html_urls($header_html, $external_home);
                set_transient($header_cache_key, $header_html, 5 * MINUTE_IN_SECONDS);
                return $header_html;
            }
        }
    }

    set_transient($header_cache_key, '__empty__', MINUTE_IN_SECONDS);
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

    $is_current_month = ((int) wp_date('n') === $month && (int) wp_date('Y') === $year);
    $cache_key = 'dci_slots_uo_' . $office_id . '_' . $year . '_' . $month;

    if (!$is_current_month) {
        $cached_slots = get_transient($cache_key);
        if (is_array($cached_slots)) {
            return $cached_slots;
        }
    }

    $orario_id = absint(dci_get_meta('orario_uo', '_dci_unita_organizzativa_', $office_id));
    if ($orario_id <= 0) {
        set_transient($cache_key, array(), 2 * MINUTE_IN_SECONDS);
        return array();
    }

    $prefix = '_dci_orario_';
    $data_inizio_raw = get_post_meta($orario_id, $prefix . 'data_inizio', true);
    $data_fine_raw = get_post_meta($orario_id, $prefix . 'data_fine', true);

    $data_inizio = DateTime::createFromFormat('d-m-Y', $data_inizio_raw) ?: null;
    $data_fine = DateTime::createFromFormat('d-m-Y', $data_fine_raw) ?: null;
    if (!$data_inizio || !$data_fine) {
        set_transient($cache_key, array(), 2 * MINUTE_IN_SECONDS);
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

    if (!$is_current_month) {
        set_transient($cache_key, $slots, 5 * MINUTE_IN_SECONDS);
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
 * Normalizza una lista di ID salvata in meta (array/stringa/scalare).
 *
 * @param mixed $value
 * @return int[]
 */
function dci_normalize_meta_ids($value) {
    if (empty($value)) {
        return array();
    }

    $flat_values = array();
    $stack = is_array($value) ? $value : array($value);

    while (!empty($stack)) {
        $current = array_pop($stack);
        if (is_array($current)) {
            foreach ($current as $item) {
                $stack[] = $item;
            }
            continue;
        }
        $flat_values[] = $current;
    }

    return array_values(array_unique(array_filter(array_map('intval', $flat_values))));
}

/**
 * Restituisce i titolari dell'amministrazione politica con ruoli, immagine e descrizione breve.
 *
 * @param WP_REST_Request $request
 * @return array[]
 */
function dci_get_amministrazione_politica(WP_REST_Request $request) {
    $cache_key = 'dci_api_amministrazione_politica_v3';
    $cached = get_transient($cache_key);
    if (is_array($cached)) {
        return $cached;
    }

    $today_ts = current_time('timestamp');

    $candidate_person_ids = array();

    // Persone collegate a incarichi politici.
    $incarichi_politici = get_posts(array(
        'post_type' => 'incarico',
        'post_status' => 'publish',
        'numberposts' => -1,
        'fields' => 'ids',
        'tax_query' => array(
            array(
                'taxonomy' => 'tipi_incarico',
                'field' => 'slug',
                'terms' => array('politico'),
            ),
        ),
    ));

    foreach ($incarichi_politici as $incarico_id) {
        $persone_incarico = dci_normalize_meta_ids(get_post_meta($incarico_id, '_dci_incarico_persona', false));
        foreach ($persone_incarico as $persona_id) {
            $candidate_person_ids[$persona_id] = true;
        }
    }

    // Persone collegate alle unità organizzative politiche richieste.
    $uo_politiche = get_posts(array(
        'post_type' => 'unita_organizzativa',
        'post_status' => 'publish',
        'numberposts' => -1,
        'fields' => 'ids',
        'post_name__in' => array('sindaco', 'giunta-comunale', 'consiglio-comunale'),
    ));

    foreach ($uo_politiche as $uo_id) {
        $responsabili = dci_normalize_meta_ids(dci_get_meta('responsabile', '_dci_unita_organizzativa_', $uo_id));
        $persone_struttura = dci_normalize_meta_ids(dci_get_meta('persone_struttura', '_dci_unita_organizzativa_', $uo_id));

        foreach (array_merge($responsabili, $persone_struttura) as $persona_id) {
            $candidate_person_ids[$persona_id] = true;
        }
    }

    if (empty($candidate_person_ids)) {
        set_transient($cache_key, array(), 5 * MINUTE_IN_SECONDS);
        return array();
    }

    $response = array();


    $role_rank = function ($role_title) {
        $normalized = sanitize_title((string) $role_title);

        $priority_map = array(
            'sindaco' => 0,
            'vicesindaco' => 1,
            'vice-sindaco' => 1,
            'assessore' => 2,
            'giunta-comunale' => 2,
            'presidente-del-consiglio' => 3,
            'vicepresidente-del-consiglio' => 4,
            'consigliere' => 5,
            'consigliere-comunale' => 5,
        );

        if (isset($priority_map[$normalized])) {
            return $priority_map[$normalized];
        }

        if (strpos($normalized, 'sindaco') !== false) {
            return 0;
        }
        if (strpos($normalized, 'vice') !== false && strpos($normalized, 'sindaco') !== false) {
            return 1;
        }
        if (strpos($normalized, 'assessore') !== false || strpos($normalized, 'giunta') !== false) {
            return 2;
        }
        if (strpos($normalized, 'presidente-del-consiglio') !== false) {
            return 3;
        }
        if (strpos($normalized, 'vicepresidente-del-consiglio') !== false) {
            return 4;
        }
        if (strpos($normalized, 'consigliere') !== false) {
            return 5;
        }

        return 99;
    };

    foreach (array_keys($candidate_person_ids) as $person_id) {
        $person = get_post($person_id);
        if (!$person instanceof WP_Post || $person->post_type !== 'persona_pubblica' || $person->post_status !== 'publish') {
            continue;
        }

        $data_conclusione_persona = dci_get_meta('data_conclusione_incarico', '_dci_persona_pubblica_', $person_id);
        if (!empty($data_conclusione_persona)) {
            $fine_persona_ts = is_numeric($data_conclusione_persona) ? intval($data_conclusione_persona) : strtotime($data_conclusione_persona);
            if ($fine_persona_ts && $fine_persona_ts < $today_ts) {
                continue;
            }
        }

        $incarichi_ids = dci_normalize_meta_ids(dci_get_meta('incarichi', '_dci_persona_pubblica_', $person_id));
        $ruoli = array();
        $has_active_role = false;

        foreach ($incarichi_ids as $incarico_id) {
            $incarico = get_post($incarico_id);
            if (!$incarico instanceof WP_Post || $incarico->post_status !== 'publish') {
                continue;
            }

            $ruoli[] = $incarico->post_title;

            $fine_incarico = dci_get_meta('data_conclusione_incarico', '_dci_incarico_', $incarico_id);
            if (empty($fine_incarico)) {
                $has_active_role = true;
                continue;
            }

            $fine_incarico_ts = is_numeric($fine_incarico) ? intval($fine_incarico) : strtotime($fine_incarico);
            if (!$fine_incarico_ts || $fine_incarico_ts >= $today_ts) {
                $has_active_role = true;
            }
        }

        if (empty($ruoli) || !$has_active_role) {
            continue;
        }

        $thumbnail_id = get_post_thumbnail_id($person_id);
        $foto_persona = dci_get_meta('foto', '_dci_persona_pubblica_', $person_id);
        $immagine = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'full') : null;

        if (empty($immagine) && !empty($foto_persona)) {
            if (is_numeric($foto_persona)) {
                $img_url = wp_get_attachment_image_url((int) $foto_persona, 'full');
                $immagine = $img_url ? $img_url : null;
            } elseif (is_array($foto_persona) && isset($foto_persona['url']) && is_string($foto_persona['url'])) {
                $immagine = $foto_persona['url'];
            } elseif (is_string($foto_persona)) {
                $immagine = $foto_persona;
            }
        }

        $contatti = dci_get_contatti_da_punti_ids(
            dci_normalize_meta_ids(dci_get_meta('punti_contatto', '_dci_persona_pubblica_', $person_id))
        );

        $ruoli_unici = array_values(array_unique($ruoli));
        usort($ruoli_unici, function ($a, $b) use ($role_rank) {
            $rank_cmp = $role_rank($a) <=> $role_rank($b);
            if ($rank_cmp !== 0) {
                return $rank_cmp;
            }

            return strcasecmp((string) $a, (string) $b);
        });

        $response[] = array(
            'id' => $person_id,
            'nome' => get_the_title($person_id),
            'url' => get_permalink($person_id),
            'ruoli' => $ruoli_unici,
            'descrizione_breve' => dci_get_meta('descrizione_breve', '_dci_persona_pubblica_', $person_id),
            'immagine' => $immagine,
            'contatti' => $contatti,
        );
    }

    usort($response, function ($a, $b) use ($role_rank) {
        $a_primary = isset($a['ruoli'][0]) ? $role_rank($a['ruoli'][0]) : 99;
        $b_primary = isset($b['ruoli'][0]) ? $role_rank($b['ruoli'][0]) : 99;

        $rank_cmp = $a_primary <=> $b_primary;
        if ($rank_cmp !== 0) {
            return $rank_cmp;
        }

        return strcasecmp((string) $a['nome'], (string) $b['nome']);
    });

    set_transient($cache_key, $response, 5 * MINUTE_IN_SECONDS);

    return $response;
}

/**
 * Restituisce gli uffici (unità organizzative) con i rispettivi responsabili se presenti.
 *
 * @param WP_REST_Request $request
 * @return array[]
 */
function dci_get_uffici_responsabili(WP_REST_Request $request) {
    $cache_key = 'dci_api_uffici_responsabili_v2';
    $cached = get_transient($cache_key);
    if (is_array($cached)) {
        return $cached;
    }

    $uffici = get_posts(array(
        'post_type' => 'unita_organizzativa',
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'tax_query' => array(
            array(
                'taxonomy' => 'tipi_unita_organizzativa',
                'field' => 'slug',
                'terms' => array('ufficio'),
            ),
        ),
    ));

    $response = array();

    foreach ($uffici as $ufficio) {
        $tipi_ufficio = get_the_terms($ufficio, 'tipi_unita_organizzativa');
        if (!is_array($tipi_ufficio) || empty($tipi_ufficio) || !isset($tipi_ufficio[0]->slug) || $tipi_ufficio[0]->slug !== 'ufficio') {
            continue;
        }

        $responsabili_ids = dci_normalize_meta_ids(dci_get_meta('responsabile', '_dci_unita_organizzativa_', $ufficio->ID));
        $responsabili = array();

        foreach ($responsabili_ids as $responsabile_id) {
            $responsabile = get_post($responsabile_id);
            if (!$responsabile instanceof WP_Post || $responsabile->post_status !== 'publish') {
                continue;
            }

            $ruoli = array();
            $incarichi_ids = dci_normalize_meta_ids(dci_get_meta('incarichi', '_dci_persona_pubblica_', $responsabile_id));
            foreach ($incarichi_ids as $incarico_id) {
                $incarico = get_post($incarico_id);
                if ($incarico instanceof WP_Post && $incarico->post_status === 'publish') {
                    $ruoli[] = $incarico->post_title;
                }
            }

            $responsabili[] = array(
                'id' => $responsabile_id,
                'nome' => get_the_title($responsabile_id),
                'url' => get_permalink($responsabile_id),
                'ruoli' => array_values(array_unique($ruoli)),
            );
        }

        $response[] = array(
            'id' => $ufficio->ID,
            'nome' => $ufficio->post_title,
            'url' => get_permalink($ufficio->ID),
            'descrizione_breve' => dci_get_meta('descrizione_breve', '_dci_unita_organizzativa_', $ufficio->ID),
            'orari_ufficio' => dci_get_ufficio_orari_payload($ufficio->ID),
            'contatti' => dci_get_ufficio_contatti_payload($ufficio->ID),
            'responsabili' => $responsabili,
        );
    }

    set_transient($cache_key, $response, 5 * MINUTE_IN_SECONDS);

    return $response;
}

/**
 * Raccoglie i contatti associati all'ufficio.
 *
 * @param int $ufficio_id
 * @return array
 */
function dci_get_ufficio_contatti_payload($ufficio_id) {
    $contact_ids = dci_normalize_meta_ids(dci_get_meta('contatti', '_dci_unita_organizzativa_', $ufficio_id));
    $contacts = dci_get_contatti_da_punti_ids($contact_ids);

    $sede_principale = dci_get_meta('sede_principale', '_dci_unita_organizzativa_', $ufficio_id);
    if (!empty($sede_principale)) {
        $indirizzo = dci_get_meta('indirizzo', '_dci_luogo_', $sede_principale);
        if (!empty($indirizzo)) {
            array_unshift($contacts['indirizzo'], $indirizzo);
        }
    }

    foreach ($contacts as $key => $values) {
        $contacts[$key] = array_values(array_unique(array_filter($values)));
    }

    return $contacts;
}

/**
 * Aggrega i contatti a partire dagli ID dei punti di contatto.
 *
 * @param int[] $contact_ids
 * @return array
 */
function dci_get_contatti_da_punti_ids($contact_ids) {
    $contacts = array(
        'telefono' => array(),
        'email' => array(),
        'pec' => array(),
        'indirizzo' => array(),
        'url' => array(),
    );

    foreach ($contact_ids as $contact_id) {
        $full_contact = dci_get_full_punto_contatto($contact_id);
        if (!is_array($full_contact)) {
            continue;
        }

        foreach ($contacts as $key => $values) {
            if (!empty($full_contact[$key]) && is_array($full_contact[$key])) {
                $contacts[$key] = array_merge($contacts[$key], array_filter($full_contact[$key]));
            }
        }
    }

    foreach ($contacts as $key => $values) {
        $contacts[$key] = array_values(array_unique(array_filter($values)));
    }

    return $contacts;
}

/**
 * Restituisce gli orari dell'ufficio a partire da sede principale/altre sedi.
 *
 * @param int $ufficio_id
 * @return array[]
 */
function dci_get_ufficio_orari_payload($ufficio_id) {
    $sede_principale = (int) dci_get_meta('sede_principale', '_dci_unita_organizzativa_', $ufficio_id);
    $altre_sedi = dci_normalize_meta_ids(dci_get_meta('altre_sedi', '_dci_unita_organizzativa_', $ufficio_id));
    $orario_uo_id = (int) dci_get_meta('orario_uo', '_dci_unita_organizzativa_', $ufficio_id);

    $sedi_ids = array();
    if ($sede_principale > 0) {
        $sedi_ids[] = $sede_principale;
    }

    foreach ($altre_sedi as $sede_id) {
        if (!in_array($sede_id, $sedi_ids, true)) {
            $sedi_ids[] = $sede_id;
        }
    }

    $orari = array();

    // Orario ufficio configurato direttamente sull'Unità organizzativa (tipologia "orario").
    if ($orario_uo_id > 0) {
        $orario_settimanale = dci_get_orario_settimanale_payload($orario_uo_id);
        if (!empty($orario_settimanale)) {
            $orari[] = array(
                'fonte' => 'orario_uo',
                'orario_id' => $orario_uo_id,
                'orario_nome' => get_the_title($orario_uo_id),
                'settimana' => $orario_settimanale,
            );
        }
    }

    // Fallback: orari presenti sui luoghi/sedi associati all'ufficio.
    foreach ($sedi_ids as $sede_id) {
        $sede = get_post($sede_id);
        if (!$sede instanceof WP_Post || $sede->post_status !== 'publish') {
            continue;
        }

        $orario_pubblico = dci_get_wysiwyg_field('orario_pubblico', '_dci_luogo_', $sede_id);
        $indirizzo = dci_get_meta('indirizzo', '_dci_luogo_', $sede_id);

        if (empty($orario_pubblico) && empty($indirizzo)) {
            continue;
        }

        $orari[] = array(
            'fonte' => 'sede',
            'sede_id' => $sede_id,
            'sede_nome' => get_the_title($sede_id),
            'sede_indirizzo' => $indirizzo,
            'orario_pubblico' => $orario_pubblico,
            'is_sede_principale' => ($sede_id === $sede_principale),
        );
    }

    return $orari;
}

/**
 * Restituisce gli orari settimanali dalla tipologia "orario".
 *
 * @param int $orario_id
 * @return array
 */
function dci_get_orario_settimanale_payload($orario_id) {
    $giorni = array(
        'lun' => 'Lunedì',
        'mar' => 'Martedì',
        'mer' => 'Mercoledì',
        'gio' => 'Giovedì',
        'ven' => 'Venerdì',
        'sab' => 'Sabato',
        'dom' => 'Domenica',
    );

    $settimana = array();
    foreach ($giorni as $abbr => $label) {
        $mattina = dci_get_meta($abbr . '_mattina', '_dci_orario_', $orario_id);
        $pomeriggio = dci_get_meta($abbr . '_pomeriggio', '_dci_orario_', $orario_id);
        if (empty($mattina) && empty($pomeriggio)) {
            continue;
        }

        $settimana[] = array(
            'giorno' => $label,
            'mattina' => $mattina,
            'pomeriggio' => $pomeriggio,
        );
    }

    return $settimana;
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
