 <?php

/**
 * Definisce la tassonomia delle Tipologie di Amministrazione Trasparente
 */
add_action( 'init', 'dci_register_taxonomy_tipi_cat_amm_trasp', -10 );
function dci_register_taxonomy_tipi_cat_amm_trasp() {

    $labels = array(
        'name'              => _x( 'Tipi categoria Amministrazione Trasparente', 'taxonomy general name', 'design_comuni_italia' ),
        'singular_name'     => _x( 'Tipo di categoria Amministrazione Trasparente', 'taxonomy singular name', 'design_comuni_italia' ),
        'search_items'      => __( 'Cerca Tipo categoria Amministrazione Trasparente', 'design_comuni_italia' ),
        'all_items'         => __( 'Tutti i Tipi di categoria Amministrazione Trasparente ', 'design_comuni_italia' ),
        'edit_item'         => __( 'Modifica il Tipo di categoria Amministrazione Trasparente', 'design_comuni_italia' ),
        'update_item'       => __( 'Aggiorna il Tipo di categoria Amministrazione Trasparente', 'design_comuni_italia' ),
        'add_new_item'      => __( 'Aggiungi un Tipo di categoria Amministrazione Trasparente', 'design_comuni_italia' ),
        'new_item_name'     => __( 'Nuovo Tipo di categoria Amministrazione Trasparente', 'design_comuni_italia' ),
        'menu_name'         => __( 'Tipi di categoria Amministrazione Trasparente', 'design_comuni_italia' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'public'            => true, //enable to get term archive page
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'has_archive'           => true,    //archive page
        'rewrite'           => array( 'slug' => 'tipi_cat_amm_trasp' ),
        'capabilities'      => array(
            'manage_terms'  => 'manage_tipi_cat_amm_trasp',
            'edit_terms'    => 'edit_tipi_cat_amm_trasp',
            'delete_terms'  => 'delete_tipi_cat_amm_trasp',
            'assign_terms'  => 'assign_tipi_cat_amm_trasp'
        )
    );

    register_taxonomy( 'tipi_cat_amm_trasp', array( 'elemento_trasparenza' ), $args );
} 
?>