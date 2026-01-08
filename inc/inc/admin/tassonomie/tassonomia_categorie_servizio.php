<?php
/**
 * Definisce la tassonomia Categorie di Servizio
 */
add_action( 'init', 'dci_register_taxonomy_categorie_servizio', -10 );


function dci_register_taxonomy_categorie_servizio() {
    $labels = array(
        'name'              => _x( 'Categorie di Servizio', 'taxonomy general name', 'design_comuni_italia' ),
        'singular_name'     => _x( 'Categoria di Servizio', 'taxonomy singular name', 'design_comuni_italia' ),
        'search_items'      => __( 'Cerca Categoria di Servizio', 'design_comuni_italia' ),
        'all_items'         => __( 'Tutti le Categorie di Servizio', 'design_comuni_italia' ),
        'edit_item'         => __( 'Modifica la Categoria di Servizio', 'design_comuni_italia' ),
        'update_item'       => __( 'Aggiorna la Categoria di Servizio', 'design_comuni_italia' ),
        'add_new_item'      => __( 'Aggiungi una Categoria di Servizio', 'design_comuni_italia' ),
        'new_item_name'     => __( 'Nuovo Tipo di Categoria di Servizio', 'design_comuni_italia' ),
        'menu_name'         => __( 'Categorie di Servizio', 'design_comuni_italia' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'servizi-categoria' ),
        'capabilities'      => array(
            'manage_terms'  => 'manage_categorie_servizio',
            'edit_terms'    => 'edit_categorie_servizio',
            'delete_terms'  => 'delete_categorie_servizio',
            'assign_terms'  => 'assign_categorie_servizio'
        ),
        'show_in_rest'          => true,
        'rest_base'             => 'categorie_servizio',
        'rest_controller_class' => 'WP_REST_Terms_Controller',
    );

    register_taxonomy( 'categorie_servizio', array( 'servizio' ), $args );

       // Aggiungo il pulsante per eliminare tutte le Categporie   
        if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'categorie_servizio') {
            add_action( 'admin_footer', 'add_empty_categories_button' );
        }



}

   if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'categorie_servizio') {        
                function add_empty_categories_button() {
                    ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function($) {
                            // Trova il form per aggiungere una nuova categoria di servizio
                            var addTermForm = $('.form-field.term-parent-wrap').closest('form');
                
                            // Crea un nuovo elemento per il pulsante "Cancella tutte le categorie di servizio"
                            var deleteButtonHtml = '<div style="margin-top: 40px;"><button id="delete-all-categories" class="button">Cancella tutte le categorie di servizio</button></div>';
                
                            // Aggiungi il pulsante sotto il form per aggiungere una nuova categoria di servizio
                            addTermForm.after(deleteButtonHtml);
                
                            // Gestisci il clic del pulsante
                            $(document).on('click', '#delete-all-categories', function(e) {
                                e.preventDefault();
                                var confirmDelete = confirm("Sei sicuro di voler cancellare tutte le categorie di servizio?");
                                if (confirmDelete) {
                                    $.ajax({
                                        url: ajaxurl,
                                        type: 'POST',
                                        data: {
                                            action: 'empty_all_categories',
                                            nonce: '<?php echo wp_create_nonce( "empty-categories-nonce" ); ?>'
                                        },
                                        success: function(response) {
                                            if (response === 'success') {
                                                alert('Tutte le categorie di servizio sono state cancellate.');
                                                location.reload();
                                            } else {
                                                alert('Si è verificato un errore durante la cancellazione delle categorie di servizio.');
                                            }
                                        },
                                        error: function(xhr, status, error) {
                                            console.error(error);
                                            alert('Si è verificato un errore durante la richiesta AJAX.');
                                        }
                                    });
                                }
                            });
                        });
                    </script>
                    <?php
                    }
                
                    // Funzione per svuotare tutte le categorie di servizio
                    add_action( 'wp_ajax_empty_all_categories', 'empty_all_categories_callback' );
                    function empty_all_categories_callback() {
                        check_ajax_referer( 'empty-categories-nonce', 'nonce' );
                    
                        $terms = get_terms( array(
                            'taxonomy'   => 'categorie_servizio',
                            'hide_empty' => false,
                        ) );
                    
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                            foreach ( $terms as $term ) {
                                wp_delete_term( $term->term_id, 'categorie_servizio' );
                            }
                            echo 'success';
                        } else {
                            echo 'error';
                        }
                    
                        wp_die();
                    }
         
            }
?>
