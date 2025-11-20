<?php

function dci_register_comune_options(){
    $prefix = '';

    /**
     * Opzioni di base
     * nome Comune, Regione, informazioni essenziali
     */
    $args = array(
        'id'           => 'dci_options_configurazione',
        'title'        => esc_html__( 'Configurazione', 'design_comuni_italia' ),
        'object_types' => array( 'options-page' ),
        'option_key'   => 'dci_options',
        'tab_group'    => 'dci_options',
        'tab_title'    => __('Configurazione Comune', "design_comuni_italia"),
        'capability'   => 'manage_options',
        'position'     => 2, // Menu position. Only applicable if 'parent_slug' is left empty.
        'icon_url'     => 'dashicons-admin-tools', // Menu icon. Only applicable if 'parent_slug' is left empty.
    );

    // 'tab_group' property is supported in > 2.4.0.
    if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
        $args['display_cb'] = 'dci_options_display_with_tabs';
    }

    $header_options = new_cmb2_box( $args );

    // Configurazione delle informazioni di base
    $header_options->add_field( array(
        'id' => $prefix . 'home_istruzioni',
        'name'        => __( 'Configurazione Comune', 'design_comuni_italia' ),
        'desc' => __( 'Area di configurazione delle informazioni di base' , 'design_comuni_italia' ),
        'type' => 'title',
    ));

    // Altri campi (quelli che hai già nel codice)
    $header_options->add_field( array(
        'id'    => $prefix . 'area_riservata',
        'name'  => __('Area Riservata', 'design_comuni_italia' ),
        'desc'  => __( 'Url per agganciare il tasto ad uno sportello esterno di autenticazione con CNS o SPID (Se aggiunto un url il tasto verrà automaticamente indirizzato al link da te inserito.)'),
        'type'  => 'text'
    ));   

    $header_options->add_field( array(
        'id'    => $prefix . 'nome_comune',
        'name'  => __( 'Nome del Comune *', 'design_comuni_italia' ),
        'desc'  => __( 'Il Nome del Comune' , 'design_comuni_italia' ),
        'type'  => 'text',
        'attributes' => array(
            'required' => 'required'
        ),
    ));

    $header_options->add_field( array(
        'id'    => $prefix . 'nome_regione',
        'name'  => __( 'Nome Regione *', 'design_comuni_italia' ),
        'desc'  => __( 'La Regione di appartenenza del Comune' , 'design_comuni_italia' ),
        'type'  => 'text',
        'attributes' => array(
            'required' => 'required'
        ),
    ));

    $header_options->add_field( array(
        'id'    => $prefix . 'url_sito_regione',
        'name'  => __( 'Sito Regione', 'design_comuni_italia' ),
        'desc'  => __( 'Link al sito della Regione di Appartenenza' , 'design_comuni_italia' ),
        'type'  => 'text_url',
        'attributes' => array(
            'required' => 'required'
        ),
    ));

    $header_options->add_field( array(
        'id'    => $prefix . 'motto_comune',
        'name'  => __( 'Motto del Comune ', 'design_comuni_italia' ),
        'desc'  => __( 'Il Motto del Comune, viene visualizzato sotto il nome del Comune' , 'design_comuni_italia' ),
        'type'  => 'text',
    ));

    $header_options->add_field( array(
        'id'    => $prefix . 'stemma_comune',
        'name'  => __('Stemma', 'design_comuni_italia' ),
        'desc'  => __( 'Lo stemma del Comune. Si raccomanda di caricare un\'immagine in formato svg' , 'design_comuni_italia' ),
        'type'  => 'file',
        'query_args' => array(
            'type' => array(
                'image/svg',
            )
        )
    ));

    $header_options->add_field( array(
        'id'    => $prefix . 'stemma_comune_mobile',
        'name'  => __('Stemma per mobile', 'design_comuni_italia' ),
        'desc'  => __( 'Utilizzare questo campo per caricare un\'immagine alternativa dello stemma del Comune visibile dal menu hamburger (mobile). Si raccomanda di caricare un\'immagine in formato svg' , 'design_comuni_italia' ),
        'type'  => 'file',
        'query_args' => array(
            'type' => array(
                'image/svg',
            )
        )
    ));

    // Altri campi per i link
    $header_options->add_field( array(
        'id'    => $prefix . 'link_albopretorio',
        'name'  => __('Link Albo pretorio', 'design_comuni_italia' ),
        'desc'  => __( 'Utilizzare questo campo per inserire il link esterno ad albo pretorio (Lasciare vuoto se interno)'),
        'type'  => 'text_url'
    ));

    $header_options->add_field( array(
        'id'    => $prefix . 'link_ammtrasparente',
        'name'  => __('Link Amministrazione Trasparente', 'design_comuni_italia' ),
        'desc'  => __( 'Utilizzare questo campo per inserire il link esterno ad amministrazione trasparente (Lasciare vuoto se interna)'),
        'type'  => 'text_url'
    ));

    $header_options->add_field( array(
        'id'    => $prefix . 'link_pagopa',
        'name'  => __('Link PagoPA', 'design_comuni_italia' ),
        'desc'  => __( 'Utilizzare questo campo per inserire il link esterno a pagoPA (Lasciare vuoto se interna)'),
        'type'  => 'text_url'
    ));

       $header_options->add_field( array(
        'id'    => $prefix . 'link_suap',
        'name' => __('Link SUAP', 'design_comuni_italia' ),
        'desc' => __( 'Utilizzare questo campo per inserire il link esterno a SUAP (Lasciare vuoto se interna)'),
        'type' => 'text_url'
    ));

    $header_options->add_field( array(
        'id'    => $prefix . 'link_sue',
        'name' => __('Link SUE', 'design_comuni_italia' ),
        'desc' => __( 'Utilizzare questo campo per inserire il link esterno a SUE (Lasciare vuoto se interna)'),
        'type' => 'text_url'
    ));

    $header_options->add_field( array(
        'id'    => $prefix . 'email_principale',
        'name' => __('E-mail principale', 'design_comuni_italia' ),
        'desc' => __( 'Utilizzare questo campo per specificare email principale del sito'),
        'type' => 'text'
    ));
    $header_options->add_field( array(
        'id'    => $prefix . 'prenota_appuntamento',
        'name' => __('Link Prenota Appuntamento', 'design_comuni_italia' ),
        'desc' => __( 'Utilizzare questo campo per specificare il link alla pagina prenota appuntamento'),
        'type' => 'text'
    ));


    
  $header_options->add_field( array(
        'id'    => $prefix . 'favicon',
        'name' => __('Icona', 'design_comuni_italia' ),
        'desc' => __( 'L\'immagine da utilizzare come icona (favicon). Si raccomanda di caricare un\'immagine in formato svg' , 'design_comuni_italia' ),
        'type' => 'file',
        'query_args'   => array(
        'type' => array(
            'image/svg',
        ))
    ));


    // Aggiungi il campo per visualizzare il percorso attuale del template
    $header_options->add_field( array(
        'id'    => $prefix . 'percorso_template',
        'name'  => __( 'Percorso Template Attuale', 'design_comuni_italia' ),
        'desc'  => __( 'Visualizza il percorso corrente del template', 'design_comuni_italia' ),
        'type'  => 'text',
        'default' => get_template_directory_uri(), // Visualizza il percorso del template
        'attributes' => array(
            'readonly' => 'readonly', // Rendi il campo di sola lettura
        ),
    ));

    // Continuazione degli altri campi che hai nel codice precedente...

    $header_options->add_field( array(
        'id'    => $prefix . 'dichiarazioneaccessibilita',
        'name'  => __( 'Dichiarazione Accessibilità', 'design_comuni_italia' ),
        'desc'  => __( 'Inserisci qui il link della dichiarazione di accessibilità', 'design_comuni_italia' ),
        'type'  => 'text'
    ));

    $header_options->add_field(array(
        'id'    => $prefix . 'firma_nostra',
        'name'  => __('Nascondi la nostra firma.', 'design_comuni_italia'),
        'desc'  => __('Opzione per nascondere dal footer la nostra firma.', 'design_comuni_italia'),
        'type'  => 'radio_inline',
        'default' => 'false',
        'options' => array(
            'true' => __('Si', 'design_comuni_italia'),
            'false' => __('No', 'design_comuni_italia'),
        ),
        'attributes' => array(
            'data-conditional-value' => "false",
        ),
    ));

    $header_options->add_field(array(
        'id'      => $prefix . 'ck_collegamenti_contenuti',
        'name'    => __('Collegamenti Automaticament Contenuti Correlati', 'design_comuni_italia'),
        'desc'    => __('Attivando questa opzione, i nuovi contenuto inseriti saranno automaticamente collegati tra loro, il sistema gestirà in modo automatico i collegamenti tra le pagine e i contenuto correlati.', 'design_comuni_italia'),
        'type'    => 'radio_inline',
        'default' => 'true',
        'options' => array(
            'true'  => __('Sì', 'design_comuni_italia'),
            'false' => __('No', 'design_comuni_italia'),
        ),
        'attributes' => array(
            'data-conditional-value' => 'true',
        ),
    ));


    $header_options->add_field( array(
        'id'    => $prefix . 'portalesoloperusoesterno',
        'name'  => __('<br><br><center>Portale personalizzato per uso Esterno. <br> (Questa sezione viene visualizzata solo agli utenti Administrator.)</center>', 'design_comuni_italia'),
        'desc'  => __('<center>Opzione per chi usa solo la nostra trasparenza e pnnr https://amministrazionetrasparente.servizipa.cloud.</center><br><br>', 'design_comuni_italia'),
        'type'  => 'title',
        'show_on_cb' => 'dci_show_only_admin_field',
    ));

    $header_options->add_field( array(
        'id'      => $prefix . 'ck_portalesoloperusoesterno',
        'name'    => __('Portale Personalizzato per uso esterno.', 'design_comuni_italia'),
        'desc'    => __('Con questa opzione abiliti il portale solo per uso PNNR e Amminsitrazione Trasparente', 'design_comuni_italia'),
        'type'    => 'radio_inline',
        'default' => 'false',
        'options' => array(
            'true'  => __('Sì', 'design_comuni_italia'),
            'false' => __('No', 'design_comuni_italia'),
        ),
        'attributes' => array(
            'data-conditional-value' => 'false',
        ),
        'show_on_cb' => 'dci_show_only_admin_field',
    ));

        $header_options->add_field( array(
            'id'    => $prefix . 'url_homesoloesterno',
            'name'  => __( 'Url Home Page Personalizzato', 'design_comuni_italia' ),
            'desc'  => __( 'Inserisci url del portale del comune in modo da indirizzare allo loro homepage.', 'design_comuni_italia' ),
            'type'  => 'text_url',
            'show_on_cb' => 'dci_show_only_super_admin_field', // Usa la funzione per mostrare il campo solo al super admin
        ));

}

add_action('cmb2_admin_init', 'dci_register_comune_options');

/**
 * Callback per mostrare campo solo se l’utente è amministratore
 *
 * @param CMB2_Field|array $field (dipende versione)
 * @return bool
 */
function dci_show_only_super_admin_field( $field ) {
    // Ottieni l'ID dell'utente corrente
    $user_id = get_current_user_id();
    
    // Controlla se l'ID dell'utente corrente è 1 (super admin)
    if ( $user_id == 1 ) {
        return true;
    }
    
    return false;
}
