<?php

/**
 * filters url for post type Notizia (rewrite slug in tipologia_notizia.php)
 * @param $post_link
 * @param int $id
 * @return array|mixed|string|string[]
 */
function dci_tipi_notizia_post_link( $post_link, $id = 0 ){
    $post = get_post($id);
    if ( is_object( $post ) ){
        $terms = wp_get_object_terms( $post->ID, 'tipi_notizia' );
        if( $terms ){
            return str_replace( '%tipi_notizia%' , $terms[0]->slug , $post_link );
        }
    }
    return $post_link;
}
//add_filter( 'post_type_link', 'dci_tipi_notizia_post_link', 1, 3 );


/**
 * filters url for post type Evento and Luogo (rewrite slug in tipologia_notizia.php)
 * @param $post_link
 * @param int $id
 * @return array|mixed|string|string[]
 */
function dci_vivere_il_comune_post_link( $post_link, $id = 0 ){
    $post = get_post($id);
    if ( is_object( $post ) ){
        $post_type = get_post_type($id);
        if( ($post_type == 'evento') || ($post_type == 'luogo') ){
            return str_replace( '%vivere-il-comune%' , 'vivere-il-comune' , $post_link );
        }
    }
    return $post_link;
}
add_filter( 'post_type_link', 'dci_vivere_il_comune_post_link', 1, 3 );
/**
 * Build sanitized alt/title text for images based on related post title.
 *
 * Removes special characters, normalizes whitespace and limits length.
 *
 * @param int $post_id
 * @return string
 */
function dci_get_sanitized_image_text_from_post( $post_id ) {
    $max_length = 90;
    $title = '';

    if ( $post_id ) {
        $title = get_the_title( $post_id );
    }

    if ( empty( $title ) && is_singular() ) {
        $title = get_the_title();
    }

    if ( empty( $title ) ) {
        return '';
    }

    $title = wp_strip_all_tags( $title );
    $title = preg_replace( '/[^\p{L}\p{N}\s\-]/u', ' ', $title );
    $title = preg_replace( '/\s+/u', ' ', $title );
    $title = trim( $title );

    if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) ) {
        if ( mb_strlen( $title ) > $max_length ) {
            $title = rtrim( mb_substr( $title, 0, $max_length - 1 ) ) . '…';
        }
    } elseif ( strlen( $title ) > $max_length ) {
        $title = rtrim( substr( $title, 0, $max_length - 1 ) ) . '…';
    }

    return $title;
}

/**
 * Ensure attachment images have meaningful alt and title attributes.
 *
 * @param array        $attr
 * @param WP_Post      $attachment
 * @param string|array $size
 * @return array
 */
function dci_enforce_image_alt_and_title_attributes( $attr, $attachment, $size ) {
    if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return $attr;
    }

    $related_post_id = get_the_ID();

    if ( ! $related_post_id ) {
        $queried_object_id = get_queried_object_id();
        $related_post_id   = $queried_object_id ? $queried_object_id : 0;
    }

    $fallback_text = dci_get_sanitized_image_text_from_post( $related_post_id );

    if ( empty( $fallback_text ) ) {
        $fallback_text = dci_get_sanitized_image_text_from_post( $attachment->post_parent );
    }

    if ( empty( $fallback_text ) ) {
        $fallback_text = dci_get_sanitized_image_text_from_post( $attachment->ID );
    }

    if ( ! empty( $fallback_text ) ) {
        $attr['alt']   = ! empty( $attr['alt'] ) ? $attr['alt'] : $fallback_text;
        $attr['title'] = ! empty( $attr['title'] ) ? $attr['title'] : $attr['alt'];
    }

    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'dci_enforce_image_alt_and_title_attributes', 10, 3 );

/**
 * Add missing title attributes to links/buttons and enforce alt/title on img tags in frontend HTML output.
 * This supports institutional accessibility checklists requiring explicit attributes.
 */
function dci_accessibility_enhance_frontend_markup( $html ) {
    if ( empty( $html ) ) {
        return $html;
    }

    if ( ! class_exists( 'DOMDocument' ) ) {
        return $html;
    }

    libxml_use_internal_errors( true );
    $dom = new DOMDocument();
    $loaded = $dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
    if ( ! $loaded ) {
        libxml_clear_errors();
        return $html;
    }

    // IMG: ensure both alt and title are present.
    $imgs = $dom->getElementsByTagName( 'img' );
    foreach ( $imgs as $img ) {
        $alt   = trim( (string) $img->getAttribute( 'alt' ) );
        $title = trim( (string) $img->getAttribute( 'title' ) );

        if ( empty( $title ) ) {
            $title = $alt;
        }

        if ( empty( $alt ) && ! empty( $title ) ) {
            $alt = $title;
        }

        if ( empty( $alt ) || empty( $title ) ) {
            $src = (string) $img->getAttribute( 'src' );
            if ( ! empty( $src ) ) {
                $file_label = trim( preg_replace( '/[-_]+/', ' ', pathinfo( $src, PATHINFO_FILENAME ) ) );
                if ( empty( $alt ) ) {
                    $alt = $file_label;
                }
                if ( empty( $title ) ) {
                    $title = $file_label;
                }
            }
        }

        if ( ! empty( $alt ) ) {
            $img->setAttribute( 'alt', $alt );
        }
        if ( ! empty( $title ) ) {
            $img->setAttribute( 'title', $title );
        }
    }

    // A, BUTTON: add title if missing from aria-label or visible text.
    foreach ( array( 'a', 'button' ) as $tag ) {
        $nodes = $dom->getElementsByTagName( $tag );
        foreach ( $nodes as $node ) {
            $title = trim( (string) $node->getAttribute( 'title' ) );
            if ( ! empty( $title ) ) {
                continue;
            }

            $label = trim( (string) $node->getAttribute( 'aria-label' ) );
            if ( empty( $label ) ) {
                $label = trim( preg_replace( '/\s+/u', ' ', $node->textContent ) );
            }

            if ( empty( $label ) && $tag === 'a' ) {
                $href = trim( (string) $node->getAttribute( 'href' ) );
                if ( ! empty( $href ) ) {
                    $path = parse_url( $href, PHP_URL_PATH );
                    $label = trim( preg_replace( '/[-_]+/', ' ', basename( (string) $path ) ) );
                }
            }

            if ( empty( $label ) && $tag === 'button' ) {
                $label = trim( (string) $node->getAttribute( 'value' ) );
            }

            if ( ! empty( $label ) ) {
                $node->setAttribute( 'title', $label );
            }
        }
    }

    $result = $dom->saveHTML();
    libxml_clear_errors();

    return ! empty( $result ) ? $result : $html;
}

function dci_accessibility_start_output_buffer() {
    // Safety switch: disabled in produzione to avoid side effects on full-page rendering.
    return;
}
add_action( 'template_redirect', 'dci_accessibility_start_output_buffer', 0 );


/**
 * Redirect legacy template-like legal notes paths to the actual page permalink.
 */
function dci_redirect_legacy_note_legali_path() {
    if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return;
    }

    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
    if ( stripos( $request_uri, '/page-templates/note-legali' ) === false ) {
        return;
    }

    $target = dci_get_template_page_url( 'page-templates/note-legali.php' );
    if ( empty( $target ) ) {
        $target = home_url( '/note-legali/' );
    }

    wp_safe_redirect( $target, 301 );
    exit;
}
add_action( 'template_redirect', 'dci_redirect_legacy_note_legali_path', 1 );


/**
 * Redirect legacy template-like privacy path to canonical privacy page permalink.
 */
function dci_redirect_legacy_privacy_path() {
    if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return;
    }

    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
    if ( stripos( $request_uri, '/page-templates/privacy' ) === false ) {
        return;
    }

    $target = dci_get_template_page_url( 'page-templates/privacy.php' );
    if ( empty( $target ) ) {
        $target = home_url( '/privacy/' );
    }

    wp_safe_redirect( $target, 301 );
    exit;
}
add_action( 'template_redirect', 'dci_redirect_legacy_privacy_path', 1 );
