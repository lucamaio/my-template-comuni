<?php


add_action( 'wp_enqueue_scripts', 'load_more_script' );
function load_more_script() {
	global $wp_query, $the_query, $wp_the_query;

    wp_enqueue_script( 'dci-load_more', get_template_directory_uri() . '/assets/js/load_more.js', array('jquery'), null, true );
    $variables = array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'posts' => json_encode( $wp_query->query_vars ), 
		'current_page' => get_query_var( 'paged' ) ? get_query_var('paged') : 1,
		'max_page' => $wp_query->max_num_pages,
    );
    wp_localize_script('dci-load_more', "data", $variables);
}

function load_template_part($template_name, $part_name=null) {
    ob_start();
    get_template_part($template_name, $part_name);
    $var = ob_get_contents();
    ob_end_clean();
    return $var;
}

// load more posts
add_action("wp_ajax_load_more" , "load_more");
add_action("wp_ajax_nopriv_load_more" , "load_more");
function load_more(){
	global $servizio, $i, $hide_categories;
	
    // prepare our arguments for the query
	$load_card_type = isset($_POST['load_card_type']) ? sanitize_key(wp_unslash($_POST['load_card_type'])) : '';
	$post_types = isset($_POST['post_types']) ? json_decode(stripslashes((string) $_POST['post_types']), true) : array();
	$url_query_params = isset($_POST['query_params']) ? json_decode(stripslashes((string) $_POST['query_params']), true) : array();
	$additional_filter = isset($_POST['additional_filter']) ? json_decode(stripslashes((string) $_POST['additional_filter']), true) : array();
	$tax_query = isset($_POST['tax_query']) ? json_decode(stripslashes((string) $_POST['tax_query']), true) : array();
	$filter_ids = isset($_POST['filter_ids']) ? json_decode(stripslashes((string) $_POST['filter_ids']), true) : array();
	$post_count = isset($_POST['post_count']) ? absint($_POST['post_count']) : 0;
	$load_posts = dci_sanitize_posts_per_page(isset($_POST['load_posts']) ? $_POST['load_posts'] : 6, 6, 24);
	$search = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';

	if (!is_array($url_query_params)) {
		$url_query_params = array();
	}
	if (!is_array($additional_filter)) {
		$additional_filter = array();
	}
	if (!is_array($tax_query)) {
		$tax_query = array();
	}
	if (!is_array($filter_ids)) {
		$filter_ids = array();
	}

	switch ($post_types){
			case "notizia":
				$args = array(
					's' => $search,
					'posts_per_page' => $load_posts,
					'post_type'      => $post_types,
					'offset'         => $post_count,
					'post_status'    => 'publish',
					'order'          => 'DESC',
					'meta_query' => array(
						array(
							'key' => '_dci_notizia_data_pubblicazione',
						)
					),
					'meta_type' => 'text_date_timestamp',
					'orderby'   => 'meta_value_num',
				);
				break;
			case "servizio":
				$args = array(
					's' => $search,
					'posts_per_page' => $load_posts,
					'post_type'      => $post_types,
					'post_status'    => 'publish',
					'orderby' => 'post_title',
					'order'   => 'ASC'
				);
				break;
			case "luogo":
				$args = array(
				's' => $search,
				'posts_per_page' => $load_posts,
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'orderby' => 'post_title',
				'order'   => 'ASC'
			);
			break;
			default:
				$args = array(
					's' => $search,
					'posts_per_page' => $load_posts,
					'post_type'      => $post_types,
					'post_status'    => 'publish',
					'orderby' => 'text_date_timestamp',
					'order'   => 'DESC'
				);
		}
		
	

	if ( isset($url_query_params["post_terms"]) ) {
		$taxquery = array(
			array(
				'taxonomy' => 'argomenti',
				'field' => 'id',
				'terms' => $url_query_params["post_terms"]
			)
		);
	
		$args['tax_query'] = $taxquery;
	}
	if ( isset($url_query_params["post_types"]) ) $args['post_type'] = $url_query_params["post_types"];
	if ( isset($url_query_params["s"]) ) $args['s'] = $url_query_params["s"];
	if (!empty($tax_query)) $args['tax_query'] = $tax_query;
	if (!empty($filter_ids)) $args['post__in'] = array_values(array_filter(array_map('absint', $filter_ids)));
	if (!empty($additional_filter)) $args = array_merge($args, $additional_filter);
	$args['posts_per_page'] = $load_posts;
	$args['post_status'] = 'publish';
	$args['offset'] = $post_count;
	$args['ignore_sticky_posts'] = true;
 
	$new_query = new WP_Query( $args );

	$out = '';
    if( $new_query->have_posts() ) :
		
		$i = 0;
		// run the loop
		while( $new_query->have_posts() ): $new_query->the_post();
		$post = get_post();
		++$i;

		if ($load_card_type == "servizio"){
			$servizio = $post;
			$out .= load_template_part("template-parts/servizio/card");  
		}
		if ($load_card_type == "categoria_servizio"){
			$servizio = $post;
			$hide_categories = true;
			$out .= load_template_part("template-parts/servizio/card");  
		}
		if ($load_card_type == "notizia"){
			$out .= load_template_part("template-parts/novita/cards-list");  
		}
		if ($load_card_type == "documento"){
			$out .= load_template_part("template-parts/documento/cards-list");  
		}
		if ($load_card_type == "global-search"){
			$out .= load_template_part("template-parts/search/item");  
		}
		if ($load_card_type == "commissario"){
			$out .= load_template_part("template-parts/commissario_osl/cards-list");  
		}
		if ($load_card_type == "progetto"){
			$out .= load_template_part("template-parts/progetti/cards-list");  
		}
		if ($load_card_type == "personale-amministrativo"){
			$out .= load_template_part("template-parts/personale-amministrativo/cards-list");  
		}
		if ($load_card_type == "domanda-frequente"){
			$out .= load_template_part("template-parts/domanda-frequente/item");  
		}	
		if ($load_card_type == "luogo"){
			$out .= load_template_part("template-parts/luogo/card-full");  
		}	 
		endwhile;
 
	endif;

	$res = array();
	$res['response'] = $out;
	$loaded_count = (int) $new_query->post_count;
	$res['post_count'] = $post_count + $loaded_count;
	if (($post_count + $loaded_count) >= (int) $new_query->found_posts) {
		$res['all_results'] = true;
	}
    wp_reset_postdata();
	wp_send_json($res);
}
