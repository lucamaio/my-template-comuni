<?php

function dci_register_pagina_trasparenza_options()
{
    $prefix = '';

    $args = array(
        'id'           => 'dci_options_trasparenza',
        'title'        => esc_html__('Trasparenza', 'design_comuni_italia'),
        'object_types' => array('options-page'),
        'option_key'   => 'trasparenza',
        'tab_title'    => __('Trasparenza', "design_comuni_italia"),
        'parent_slug'  => 'dci_options',
        'tab_group'    => 'dci_options',
        'capability'   => 'manage_options',
    );

    if (version_compare(CMB2_VERSION, '2.4.0')) {
        $args['display_cb'] = 'dci_options_display_with_tabs';
    }

    $trasparenza_options = new_cmb2_box($args);

    $trasparenza_options->add_field(array(
        'id'    => $prefix . 'trasparente_options',
        'name'  => __('Amministrazione Trasparente', 'design_comuni_italia'),
        'desc'  => __('Configurazione della pagina Amministrazione Trasparente (se interna).', 'design_comuni_italia'),
        'type'  => 'title',
    ));

    $trasparenza_options->add_field(array(
        'id'          => $prefix . 'siti_tematici',
        'name'        => __('Sito Tematico', 'design_comuni_italia'),
        'desc'        => __('Selezionare il sito tematico di cui visualizzare la Card', 'design_comuni_italia'),
        'type'        => 'pw_multiselect',
        'options'     => dci_get_posts_options('sito_tematico'),
        'attributes'  => array(
            'data-maximum-selection-length' => '12',
        ),
    ));

    // Pulsante personalizzato per ricaricare i dati
    $trasparenza_options->add_field(array(
    'id'   => $prefix . 'reload_data_button',
    'type' => 'title',
    'name' => '',
    'desc' => '<a href="' . esc_url(admin_url('themes.php?page=reload-trasparenza-theme-options')) . '" class="button button-primary">Ricarica dati Trasparenza</a>',
));

    $trasparenza_options->add_field(array(
        'id'      => $prefix . 'ck_show_section',
        'name'    => __('Visualizza la sezione di appartenza.', 'design_comuni_italia'),
        'desc'    => __('Questa spunta consente di scegliere se si desidera mostra il titolo della sezione nella card', 'design_comuni_italia'),
        'type'    => 'radio_inline',
        'default' => 'false',
        'options' => array(
            'true'  => __('Sì', 'design_comuni_italia'),
            'false' => __('No', 'design_comuni_italia'),
        ),
        'attributes' => array(
            'data-conditional-value' => 'false',
        ),
    ));

    
    $trasparenza_options->add_field(array(
        'id'    => $prefix . 'trasparente_options1',
        'name'  => __('OPZIONI - Amministrazione Trasparente', 'design_comuni_italia'),
        'desc'  => __('Visualizza le sezioni personalizzate dell amministrazione Trasparente', 'design_comuni_italia'),
        'type'  => 'title',
    ));

    
    $trasparenza_options->add_field(array(
        'id'      => $prefix . 'ck_bandidigaratemplatepersonalizzato',
        'name'    => __('Contratti Pubblici con template personalizzato da noi.', 'design_comuni_italia'),
        'desc'    => __('Questa spunta consente di visualizzare gli elementi di Contratti Pubblici con una grafica personalizzata.', 'design_comuni_italia'),
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

    $trasparenza_options->add_field(array(
        'id'      => $prefix . 'ck_attidiconcessione',
        'name'    => __('Atti di concessione con template personalizzato da noi.', 'design_comuni_italia'),
        'desc'    => __('Questa spunta consente di visualizzare gli elementi di Atti di concessione con una grafica personalizzata.', 'design_comuni_italia'),
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


    $trasparenza_options->add_field(array(
        'id'      => $prefix . 'ck_incarichieautorizzazioniaidipendenti',
        'name'    => __('Incarichi conferiti e autorizzati ai dipendenti con template personalizzato da noi.', 'design_comuni_italia'),
        'desc'    => __('Questa spunta consente di visualizzare gli elementi di Incarichi conferiti e autorizzati ai dipendenti con una grafica personalizzata.', 'design_comuni_italia'),
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


     $trasparenza_options->add_field(array(
        'id'      => $prefix . 'ck_titolariIncarichiCollaborazioneConsulenzaTemplatePersonalizzato',
        'name'    => __('Titolari di incarichi di collaborazione o consulenzacon template personalizzato da noi.', 'design_comuni_italia'),
        'desc'    => __('Questa spunta consente di visualizzare gli elementi di itolari di incarichi di collaborazione o consulenza con una grafica personalizzata.', 'design_comuni_italia'),
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



}?>











