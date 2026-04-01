<?php
/* -------------------------------------------------------------
 *  TASSONOMIA: tipi_cat_amm_trasp
 * ----------------------------------------------------------- */

/**
 * 1. Registrazione tassonomia
 */
add_action( 'init', 'dci_register_taxonomy_tipi_cat_amm_trasp', -10 );
function dci_register_taxonomy_tipi_cat_amm_trasp() {
	$labels = array(
		'name'              => _x( 'Tipi categoria Amministrazione Trasparente', 'taxonomy general name', 'design_comuni_italia' ),
		'singular_name'     => _x( 'Tipo di categoria Amministrazione Trasparente', 'taxonomy singular name', 'design_comuni_italia' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'tipi_cat_amm_trasp' ),
		'capabilities'      => array(
			'manage_terms' => 'manage_tipi_cat_amm_trasp',
			'edit_terms'   => 'edit_tipi_cat_amm_trasp',
			'delete_terms' => 'delete_tipi_cat_amm_trasp',
			'assign_terms' => 'assign_tipi_cat_amm_trasp',
		),
	);

	register_taxonomy( 'tipi_cat_amm_trasp', array( 'elemento_trasparenza' ), $args );
}




/**
 * Campi personalizzati aggiunta/modifica termine
 */
add_action( 'tipi_cat_amm_trasp_add_form_fields', 'dci_tassonomia_add_fields' );
add_action( 'tipi_cat_amm_trasp_edit_form_fields', 'dci_tassonomia_edit_fields' );

function dci_get_role_boxes_html( $selected_roles = [] ) {
	global $wp_roles;

	$all_roles = $wp_roles->roles;
	$available_roles = array_diff( array_keys( $all_roles ), $selected_roles );

	ob_start();
	?>
	<style>
		.dci-dual-select {
			display: flex;
			gap: 20px;
			margin-top: 10px;
		}
		.dci-dual-select select {
			width: 200px;
			height: 150px;
		}
	</style>
	<div class="form-field term-excluded_roles-wrap">
		<label><?php _e( 'Ruoli da escludere', 'design_comuni_italia' ); ?></label>
		<div class="dci-dual-select">
			<div>
				<strong><?php _e( 'Disponibili', 'design_comuni_italia' ); ?></strong><br />
				<select id="roles-available" multiple>
					<?php foreach ( $available_roles as $key ): ?>
						<option value="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( translate_user_role( $all_roles[ $key ]['name'] ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div>
				<strong><?php _e( 'Esclusi', 'design_comuni_italia' ); ?></strong><br />
				<select name="excluded_roles[]" id="roles-selected" multiple>
					<?php foreach ( $selected_roles as $key ): ?>
						<option value="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( translate_user_role( $all_roles[ $key ]['name'] ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<p class="description"><?php _e( 'Trascina i ruoli che NON devono vedere questa categoria.', 'design_comuni_italia' ); ?></p>
	</div>
	<script>
		document.addEventListener('DOMContentLoaded', () => {
			const available = document.getElementById('roles-available');
			const selected = document.getElementById('roles-selected');

			function moveSelected(from, to) {
				Array.from(from.selectedOptions).forEach(opt => {
					to.appendChild(opt);
				});
			}

			available.ondblclick = () => moveSelected(available, selected);
			selected.ondblclick = () => moveSelected(selected, available);
		});
	</script>
	<?php
	return ob_get_clean();
}

function dci_tassonomia_add_fields() {
	?>
	<div class="form-field">
		<?php echo dci_get_role_boxes_html(); ?>
	</div>

	<div id="url-wrapper">
		<div class="form-field"><label><?php _e('URL personalizzato', 'design_comuni_italia'); ?></label><input name="term_url" type="url" placeholder="https://..." /></div>
		<div class="form-field"><label><input type="checkbox" name="open_new_window" value="1" /> <?php _e('Apri in nuova finestra', 'design_comuni_italia'); ?></label></div>
	</div>

	<div class="form-field"><label><?php _e('Ordinamento', 'design_comuni_italia'); ?></label><input type="number" name="ordinamento" value="0" /></div>
	<div class="form-field"><label><input type="checkbox" name="visualizza_elemento" value="1" checked /> <?php _e('Visualizza elemento', 'design_comuni_italia'); ?></label></div>

	<script>
	document.addEventListener('DOMContentLoaded', function() {
		const parentSelect = document.getElementById('parent');
		const urlWrapper = document.getElementById('url-wrapper');

		function toggleUrlFields() {
			const isTopLevel = parentSelect && parentSelect.value === '0';
			urlWrapper.style.display = isTopLevel ? 'none' : 'block';
		}

		parentSelect?.addEventListener('change', toggleUrlFields);
		toggleUrlFields();
	});
	</script>
	<?php
}

function dci_tassonomia_edit_fields( $term ) {
	$excluded_roles = get_term_meta( $term->term_id, 'excluded_roles', true );
	$excluded_roles = is_array( $excluded_roles ) ? $excluded_roles : [];
	?>
	<tr class="form-field">
		<th scope="row"><?php _e('Ruoli da escludere', 'design_comuni_italia'); ?></th>
		<td><?php echo dci_get_role_boxes_html($excluded_roles); ?></td>
	</tr>

	<tbody id="url-wrapper-edit">
		<tr class="form-field">
			<th><?php _e('URL', 'design_comuni_italia'); ?></th>
			<td><input type="url" name="term_url" value="<?php echo esc_attr(get_term_meta($term->term_id, 'term_url', true)); ?>" /></td>
		</tr>
		<tr class="form-field">
			<th><?php _e('Nuova finestra', 'design_comuni_italia'); ?></th>
			<td><label><input type="checkbox" name="open_new_window" value="1" <?php checked(get_term_meta($term->term_id, 'open_new_window', true), '1'); ?> /> <?php _e('Apri in nuova finestra', 'design_comuni_italia'); ?></label></td>
		</tr>
	</tbody>

	<tr class="form-field"><th><?php _e('Ordinamento', 'design_comuni_italia'); ?></th><td><input name="ordinamento" type="number" value="<?php echo esc_attr(get_term_meta($term->term_id, 'ordinamento', true)); ?>" /></td></tr>
	<tr class="form-field"><th><?php _e('Visualizza', 'design_comuni_italia'); ?></th><td><label><input name="visualizza_elemento" type="checkbox" value="1" <?php checked(get_term_meta($term->term_id, 'visualizza_elemento', true), '1'); ?> /> <?php _e('Visualizza elemento', 'design_comuni_italia'); ?></label></td></tr>

	<script>
	document.addEventListener('DOMContentLoaded', function() {
		const parentSelect = document.querySelector('select[name="parent"]');
		const urlWrapper = document.getElementById('url-wrapper-edit');

		function toggleUrlFields() {
			const isTopLevel = parentSelect && parentSelect.value === '0';
			urlWrapper.style.display = isTopLevel ? 'none' : '';
		}

		parentSelect?.addEventListener('change', toggleUrlFields);
		toggleUrlFields();
	});
	</script>
	<?php
}


/**
 * 3. Salva metadati personalizzati
 */
add_action( 'created_tipi_cat_amm_trasp', 'dci_save_term_meta', 10, 2 );
add_action( 'edited_tipi_cat_amm_trasp',  'dci_save_term_meta', 10, 2 );
function dci_save_term_meta( $term_id ) {
	if (
		empty( $_POST ) ||
		(
			! isset( $_POST['taxonomy'] ) &&
			! isset( $_POST['tag_ID'] ) &&
			! isset( $_POST['visualizza_elemento'] ) &&
			! isset( $_POST['ordinamento'] ) &&
			! isset( $_POST['term_url'] ) &&
			! isset( $_POST['excluded_roles'] )
		)
	) {
		return;
	}

	update_term_meta( $term_id, 'ordinamento', isset( $_POST['ordinamento'] ) ? intval( $_POST['ordinamento'] ) : 0 );
	update_term_meta( $term_id, 'visualizza_elemento', isset( $_POST['visualizza_elemento'] ) ? '1' : '0' );
	update_term_meta( $term_id, 'term_url', isset( $_POST['term_url'] ) ? esc_url_raw( $_POST['term_url'] ) : '' );
	update_term_meta( $term_id, 'open_new_window', isset( $_POST['open_new_window'] ) ? '1' : '0' );

	if ( isset( $_POST['excluded_roles'] ) && is_array( $_POST['excluded_roles'] ) ) {
		update_term_meta( $term_id, 'excluded_roles', array_map( 'sanitize_text_field', $_POST['excluded_roles'] ) );
	} else {
		delete_term_meta( $term_id, 'excluded_roles' );
	}
}

/**
 * 4. Colonne personalizzate
 */
add_filter('manage_edit-tipi_cat_amm_trasp_columns', 'dci_custom_column_order');
function dci_custom_column_order($columns) {
    $new_columns = [];

    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;
        if ($key === 'name') {
            $new_columns['ordinamento'] = __('Ordinamento', 'design_comuni_italia');
            $new_columns['visualizza_item'] = __('Visualizza', 'design_comuni_italia');
            $new_columns['term_url'] = __('URL', 'design_comuni_italia');
        }
    }

    return $new_columns;
}

add_filter( 'manage_tipi_cat_amm_trasp_custom_column', 'dci_show_custom_columns', 10, 3 );
function dci_show_custom_columns( $out, $column, $term_id ) {
	switch ( $column ) {
		case 'ordinamento':
			$val = get_term_meta( $term_id, 'ordinamento', true );
			return $val !== '' ? esc_html( $val ) : '&mdash;';
		case 'visualizza_item':
			$show = get_term_meta( $term_id, 'visualizza_elemento', true );
			$show = ( $show === '' ) ? '1' : $show;
			return $show === '1' ? __( 'Sì', 'design_comuni_italia' ) : __( 'No', 'design_comuni_italia' );
		case 'term_url':
			$term_url = get_term_meta( $term_id, 'term_url', true );
			$open_new_window = get_term_meta( $term_id, 'open_new_window', true );
			if ( $term_url ) {
				$target = $open_new_window ? ' target="_blank"' : '';
				return '<a href="' . esc_url( $term_url ) . '"' . $target . '>' . esc_html( $term_url ) . '</a>';
			}
			return '&mdash;';
	}
	return $out;
}

/**
 * Helpers per statistiche e filtri della tassonomia Trasparenza.
 */
function dci_normalize_tipi_cat_amm_trasp_admin_name( $name ) {
	if ( function_exists( 'dci_normalize_trasparenza_term_name' ) ) {
		return dci_normalize_trasparenza_term_name( $name );
	}

	return preg_replace( '/\s+/u', ' ', trim( (string) $name ) );
}

function dci_flatten_tipi_cat_amm_trasp_expected_paths( $terms, $parent_path = '' ) {
	$paths = [];

	foreach ( $terms as $key => $children ) {
		if ( is_int( $key ) ) {
			$term_name = $children;
			$children  = [];
		} else {
			$term_name = $key;
		}

		$current_name = dci_normalize_tipi_cat_amm_trasp_admin_name( $term_name );
		$current_path = '' !== $parent_path ? $parent_path . ' > ' . $current_name : $current_name;
		$paths[]      = mb_strtolower( $current_path );

		if ( ! empty( $children ) && is_array( $children ) ) {
			$paths = array_merge(
				$paths,
				dci_flatten_tipi_cat_amm_trasp_expected_paths( $children, $current_path )
			);
		}
	}

	return $paths;
}

function dci_get_tipi_cat_amm_trasp_term_path( $term ) {
	$parts = [ dci_normalize_tipi_cat_amm_trasp_admin_name( $term->name ) ];
	$parent_id = (int) $term->parent;

	while ( $parent_id > 0 ) {
		$parent = get_term( $parent_id, 'tipi_cat_amm_trasp' );
		if ( ! $parent || is_wp_error( $parent ) ) {
			break;
		}

		array_unshift( $parts, dci_normalize_tipi_cat_amm_trasp_admin_name( $parent->name ) );
		$parent_id = (int) $parent->parent;
	}

	return mb_strtolower( implode( ' > ', $parts ) );
}

function dci_get_tipi_cat_amm_trasp_admin_stats() {
	remove_filter( 'terms_clauses', 'dci_filter_tipi_cat_amm_trasp_admin_terms', 15 );

	$terms = get_terms(
		[
			'taxonomy'   => 'tipi_cat_amm_trasp',
			'hide_empty' => false,
		]
	);

	add_filter( 'terms_clauses', 'dci_filter_tipi_cat_amm_trasp_admin_terms', 15, 3 );

	$stats = [
		'total'         => 0,
		'visible'       => 0,
		'hidden'        => 0,
		'with_url'      => 0,
		'duplicate_ids' => [],
		'extra_ids'     => [],
	];

	if ( is_wp_error( $terms ) ) {
		return $stats;
	}

	$stats['total'] = count( $terms );
	$duplicate_map = [];
	$expected_paths = function_exists( 'dci_tipi_cat_amm_trasp_array' )
		? array_flip( dci_flatten_tipi_cat_amm_trasp_expected_paths( dci_tipi_cat_amm_trasp_array() ) )
		: [];

	foreach ( $terms as $term ) {
		$visible = (string) get_term_meta( $term->term_id, 'visualizza_elemento', true );
		$visible = '' === $visible ? '1' : $visible;

		if ( '1' === $visible ) {
			$stats['visible']++;
		} else {
			$stats['hidden']++;
		}

		$term_url = trim( (string) get_term_meta( $term->term_id, 'term_url', true ) );
		if ( '' !== $term_url ) {
			$stats['with_url']++;
		}

		$normalized_name = mb_strtolower( dci_normalize_tipi_cat_amm_trasp_admin_name( $term->name ) );
		if ( ! isset( $duplicate_map[ $normalized_name ] ) ) {
			$duplicate_map[ $normalized_name ] = [];
		}
		$duplicate_map[ $normalized_name ][] = (int) $term->term_id;

		$term_path = dci_get_tipi_cat_amm_trasp_term_path( $term );
		if ( ! empty( $expected_paths ) && ! isset( $expected_paths[ $term_path ] ) ) {
			$stats['extra_ids'][] = (int) $term->term_id;
		}
	}

	foreach ( $duplicate_map as $ids ) {
		if ( count( $ids ) > 1 ) {
			$stats['duplicate_ids'] = array_merge( $stats['duplicate_ids'], $ids );
		}
	}

	$stats['duplicate_ids'] = array_values( array_unique( array_map( 'intval', $stats['duplicate_ids'] ) ) );
	$stats['extra_ids']     = array_values( array_unique( array_map( 'intval', $stats['extra_ids'] ) ) );

	return $stats;
}

function dci_get_tipi_cat_amm_trasp_matching_ids_by_visibility( $visibility ) {
	remove_filter( 'terms_clauses', 'dci_filter_tipi_cat_amm_trasp_admin_terms', 15 );

	$terms = get_terms(
		[
			'taxonomy'   => 'tipi_cat_amm_trasp',
			'hide_empty' => false,
		]
	);

	add_filter( 'terms_clauses', 'dci_filter_tipi_cat_amm_trasp_admin_terms', 15, 3 );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return [];
	}

	$matching_ids = [];

	foreach ( $terms as $term ) {
		$current_visibility = (string) get_term_meta( $term->term_id, 'visualizza_elemento', true );
		$current_visibility = '' === $current_visibility ? '1' : $current_visibility;

		if ( (string) $visibility === $current_visibility ) {
			$matching_ids[] = (int) $term->term_id;

			$ancestor_ids = get_ancestors( $term->term_id, 'tipi_cat_amm_trasp', 'taxonomy' );
			if ( ! empty( $ancestor_ids ) ) {
				$matching_ids = array_merge( $matching_ids, array_map( 'intval', $ancestor_ids ) );
			}
		}
	}

	return array_values( array_unique( $matching_ids ) );
}

function dci_expand_tipi_cat_amm_trasp_ids_with_ancestors( $term_ids ) {
	$expanded_ids = array_map( 'intval', (array) $term_ids );

	foreach ( $expanded_ids as $term_id ) {
		$ancestor_ids = get_ancestors( $term_id, 'tipi_cat_amm_trasp', 'taxonomy' );
		if ( ! empty( $ancestor_ids ) ) {
			$expanded_ids = array_merge( $expanded_ids, array_map( 'intval', $ancestor_ids ) );
		}
	}

	return array_values( array_unique( $expanded_ids ) );
}

/**
 * 5. Filtri admin nella lista termini della tassonomia
 */
add_action( 'all_admin_notices', 'dci_render_tipi_cat_amm_trasp_admin_filters' );
function dci_render_tipi_cat_amm_trasp_admin_filters() {
	global $pagenow;

	if ( 'edit-tags.php' !== $pagenow ) {
		return;
	}

	$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( $_GET['taxonomy'] ) : '';
	if ( 'tipi_cat_amm_trasp' !== $taxonomy ) {
		return;
	}

	$post_type          = isset( $_GET['post_type'] ) ? sanitize_key( $_GET['post_type'] ) : 'elemento_trasparenza';
	$filter_visualizza  = isset( $_GET['filter_visualizza'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_visualizza'] ) ) : '';
	$filter_duplicates  = isset( $_GET['filter_duplicates'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_duplicates'] ) ) : '';
	$filter_extra       = isset( $_GET['filter_extra'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_extra'] ) ) : '';
	$reset_url          = add_query_arg(
		[
			'taxonomy'  => 'tipi_cat_amm_trasp',
			'post_type' => $post_type,
		],
		admin_url( 'edit-tags.php' )
	);
	$stats = dci_get_tipi_cat_amm_trasp_admin_stats();
	$duplicate_url = add_query_arg(
		[
			'taxonomy'          => 'tipi_cat_amm_trasp',
			'post_type'         => $post_type,
			'filter_duplicates' => '1',
		],
		admin_url( 'edit-tags.php' )
	);
	$extra_url = add_query_arg(
		[
			'taxonomy'     => 'tipi_cat_amm_trasp',
			'post_type'    => $post_type,
			'filter_extra' => '1',
		],
		admin_url( 'edit-tags.php' )
	);
	?>
	<div class="notice notice-info" style="padding:12px 16px;">
		<div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px;">
			<span class="button button-secondary" style="pointer-events:none;"><?php echo esc_html( sprintf( 'Totali: %d', $stats['total'] ) ); ?></span>
			<span class="button button-secondary" style="pointer-events:none;"><?php echo esc_html( sprintf( 'Visibili: %d', $stats['visible'] ) ); ?></span>
			<span class="button button-secondary" style="pointer-events:none;"><?php echo esc_html( sprintf( 'Nascoste: %d', $stats['hidden'] ) ); ?></span>
			<span class="button button-secondary" style="pointer-events:none;"><?php echo esc_html( sprintf( 'Con URL: %d', $stats['with_url'] ) ); ?></span>
			<a class="button <?php echo '1' === $filter_duplicates ? 'button-primary' : 'button-secondary'; ?>" href="<?php echo esc_url( $duplicate_url ); ?>"><?php echo esc_html( sprintf( 'Duplicati potenziali: %d', count( $stats['duplicate_ids'] ) ) ); ?></a>
			<?php if ( 1 === (int) get_current_user_id() ) { ?>
				<a class="button <?php echo '1' === $filter_extra ? 'button-primary' : 'button-secondary'; ?>" href="<?php echo esc_url( $extra_url ); ?>"><?php echo esc_html( sprintf( 'Voci extra: %d', count( $stats['extra_ids'] ) ) ); ?></a>
			<?php } ?>
		</div>
		<form method="get" style="display:flex; gap:12px; align-items:end; flex-wrap:wrap; margin:0;">
			<input type="hidden" name="taxonomy" value="tipi_cat_amm_trasp" />
			<input type="hidden" name="post_type" value="<?php echo esc_attr( $post_type ); ?>" />
			<?php if ( '1' === $filter_duplicates ) { ?>
				<input type="hidden" name="filter_duplicates" value="1" />
			<?php } ?>
			<?php if ( '1' === $filter_extra && 1 === (int) get_current_user_id() ) { ?>
				<input type="hidden" name="filter_extra" value="1" />
			<?php } ?>

			<div>
				<label for="filter_visualizza" style="display:block; font-weight:600; margin-bottom:4px;">
					<?php esc_html_e( 'Visualizza', 'design_comuni_italia' ); ?>
				</label>
				<select id="filter_visualizza" name="filter_visualizza">
					<option value=""><?php esc_html_e( 'Tutti', 'design_comuni_italia' ); ?></option>
					<option value="1" <?php selected( $filter_visualizza, '1' ); ?>><?php esc_html_e( 'Sì', 'design_comuni_italia' ); ?></option>
					<option value="0" <?php selected( $filter_visualizza, '0' ); ?>><?php esc_html_e( 'No', 'design_comuni_italia' ); ?></option>
				</select>
			</div>

			<div style="display:flex; gap:8px;">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Filtra', 'design_comuni_italia' ); ?></button>
				<a class="button" href="<?php echo esc_url( $reset_url ); ?>"><?php esc_html_e( 'Reset', 'design_comuni_italia' ); ?></a>
			</div>
		</form>
	</div>
	<?php
}

add_filter( 'terms_clauses', 'dci_filter_tipi_cat_amm_trasp_admin_terms', 15, 3 );
function dci_filter_tipi_cat_amm_trasp_admin_terms( $clauses, $taxonomies, $args ) {
	global $wpdb, $pagenow;

	if ( 'edit-tags.php' !== $pagenow ) {
		return $clauses;
	}

	if ( ! in_array( 'tipi_cat_amm_trasp', (array) $taxonomies, true ) ) {
		return $clauses;
	}

	$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( $_GET['taxonomy'] ) : '';
	if ( 'tipi_cat_amm_trasp' !== $taxonomy ) {
		return $clauses;
	}

	$filter_visualizza = isset( $_GET['filter_visualizza'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_visualizza'] ) ) : '';
	$filter_duplicates = isset( $_GET['filter_duplicates'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_duplicates'] ) ) : '';
	$filter_extra      = isset( $_GET['filter_extra'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_extra'] ) ) : '';

	if ( ! in_array( $filter_visualizza, [ '', '0', '1' ], true ) ) {
		$filter_visualizza = '';
	}

	if ( ! in_array( $filter_duplicates, [ '', '1' ], true ) ) {
		$filter_duplicates = '';
	}

	if ( 1 !== (int) get_current_user_id() ) {
		$filter_extra = '';
	} elseif ( ! in_array( $filter_extra, [ '', '1' ], true ) ) {
		$filter_extra = '';
	}

	if ( '' === $filter_visualizza && '' === $filter_duplicates && '' === $filter_extra ) {
		return $clauses;
	}

	if ( '' !== $filter_visualizza ) {
		$clauses['join'] .= "
			LEFT JOIN {$wpdb->termmeta} tm_filter_vis
				ON tm_filter_vis.term_id = t.term_id
				AND tm_filter_vis.meta_key = 'visualizza_elemento'
		";
	}

	if ( '1' === $filter_visualizza ) {
		$visible_ids = dci_get_tipi_cat_amm_trasp_matching_ids_by_visibility( '1' );
		if ( empty( $visible_ids ) ) {
			$clauses['where'] .= " AND 1 = 0";
		} else {
			$clauses['where'] .= " AND t.term_id IN (" . implode( ',', array_map( 'intval', $visible_ids ) ) . ")";
		}
	} elseif ( '0' === $filter_visualizza ) {
		$hidden_ids = dci_get_tipi_cat_amm_trasp_matching_ids_by_visibility( '0' );
		if ( empty( $hidden_ids ) ) {
			$clauses['where'] .= " AND 1 = 0";
		} else {
			$clauses['where'] .= " AND t.term_id IN (" . implode( ',', array_map( 'intval', $hidden_ids ) ) . ")";
		}
	}

	if ( '1' === $filter_duplicates ) {
		$stats = dci_get_tipi_cat_amm_trasp_admin_stats();
		$duplicate_ids = dci_expand_tipi_cat_amm_trasp_ids_with_ancestors( $stats['duplicate_ids'] );
		if ( empty( $duplicate_ids ) ) {
			$clauses['where'] .= " AND 1 = 0";
		} else {
			$clauses['where'] .= " AND t.term_id IN (" . implode( ',', array_map( 'intval', $duplicate_ids ) ) . ")";
		}
	}

	if ( '1' === $filter_extra ) {
		$stats = dci_get_tipi_cat_amm_trasp_admin_stats();
		$extra_ids = dci_expand_tipi_cat_amm_trasp_ids_with_ancestors( $stats['extra_ids'] );
		if ( empty( $extra_ids ) ) {
			$clauses['where'] .= " AND 1 = 0";
		} else {
			$clauses['where'] .= " AND t.term_id IN (" . implode( ',', array_map( 'intval', $extra_ids ) ) . ")";
		}
	}

	return $clauses;
}

/**
 * 5. Esclude i termini invisibili o non accessibili per ruolo corrente (solo nuova creazione elemento)
 */
add_filter( 'terms_clauses', 'dci_hide_invisible_or_restricted_terms', 10, 3 );
function dci_hide_invisible_or_restricted_terms( $clauses, $taxonomies, $args ) {
	if ( ! in_array( 'tipi_cat_amm_trasp', (array) $taxonomies, true ) ) {
		return $clauses;
	}

	if ( ! is_admin() ) {
		return $clauses;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->base !== 'post' || $screen->action !== 'add' || $screen->post_type !== 'elemento_trasparenza' ) {
		return $clauses;
	}

	global $wpdb;

	if ( false === strpos( $clauses['join'], 'termmeta' ) ) {
		$clauses['join'] .= "
			LEFT JOIN {$wpdb->termmeta} tm_vis
				ON tm_vis.term_id = t.term_id
				AND tm_vis.meta_key = 'visualizza_elemento'
			LEFT JOIN {$wpdb->termmeta} tm_roles
				ON tm_roles.term_id = t.term_id
				AND tm_roles.meta_key = 'excluded_roles'
		";
	}

	$current_user = wp_get_current_user();
	$user_roles = (array) $current_user->roles;

	$role_checks = [];
	foreach ( $user_roles as $role ) {
		$role_checks[] = $wpdb->prepare( "tm_roles.meta_value NOT LIKE %s", '%' . $wpdb->esc_like( $role ) . '%' );
	}
	$roles_condition = ! empty( $role_checks ) ? ' AND ( tm_roles.meta_value IS NULL OR (' . implode( ' AND ', $role_checks ) . ') )' : '';

	$clauses['where'] .= "
		AND (
			tm_vis.meta_value IS NULL
			OR tm_vis.meta_value = ''
			OR tm_vis.meta_value = '1'
		)
		$roles_condition
	";

	return $clauses;
}
?> 
