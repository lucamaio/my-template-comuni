<?php

/**
 * Definisce post type Persona pubblica
 */


add_action('init', 'dci_register_post_type_persona_pubblica', 60);
function dci_register_post_type_persona_pubblica()
{

    $labels = array(
        'name'          => _x('Persone Pubbliche', 'Post Type General Name', 'design_comuni_italia'),
        'singular_name' => _x('Persona Pubblica', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new'       => _x('Aggiungi una Persona Pubblica', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new_item'  => _x('Aggiungi una nuova Persona Pubblica', 'Post Type Singular Name', 'design_comuni_italia'),
        'edit_item'       => _x('Modifica la Persona Pubblica', 'Post Type Singular Name', 'design_comuni_italia'),
        'featured_image' => __('Immagine di riferimento della Persona Pubblica', 'design_comuni_italia'),
    );

    $args   = array(
        'label'         => __('Persona pubblica', 'design_comuni_italia'),
        'labels'        => $labels,
        'supports'      => array('editor'),
        'hierarchical'  => false,
        'public'        => true,
        'menu_position' => 5,
        'menu_icon'     => 'dashicons-businessperson',
        'has_archive'   => true,
        'rewrite' => array('slug' => 'persona_pubblica', 'with_front' => false),
        'capability_type' => array('persona_pubblica', 'persone_pubbliche'),
        'map_meta_cap'    => true,
        'description'    => __('Questa Tipologia descrive le Persone Pubbliche dell\'Amministrazione', 'design_comuni_italia'),
    );
    register_post_type('persona_pubblica', $args);

    remove_post_type_support('persona_pubblica', 'editor');
}

/**
 * Aggiungo label sotto il titolo
 */
//add_action( 'edit_form_after_title', 'dci_persona_pubblica_add_content_after_title' );
function dci_persona_pubblica_add_content_after_title($post)
{
    if ($post->post_type == "persona_pubblica")
        _e('<span><i>il <b>Titolo</b> è il <b>Nome della Persona Pubblica</b>.</i></span><br><br>', 'design_comuni_italia');
}

/**
 * Crea i metabox del post type Persona pubblica
 */
add_action('cmb2_init', 'dci_add_persona_pubblica_metaboxes');
function dci_add_persona_pubblica_metaboxes()
{

    $prefix = '_dci_persona_pubblica_';

    $cmb_user = new_cmb2_box(array(
        'id'               => $prefix . 'persona_box',
        'title'            => __('Apertura', 'design_comuni_italia'),
        'object_types'     => array('persona_pubblica'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    $cmb_user->add_field(array(
        'id' => $prefix . 'nome',
        'name'        => __('Nome *', 'design_comuni_italia'),
        'type' => 'text',
        'attributes' => array(
            'required' => 'required'
        )
    ));

    $cmb_user->add_field(array(
        'id' => $prefix . 'cognome',
        'name'        => __('Cognome *', 'design_comuni_italia'),
        'type' => 'text',
        'attributes' => array(
            'required' => 'required'
        )
    ));

    $cmb_user->add_field(array(
        'id'         => $prefix . 'descrizione_breve',
        'name'       => __('Descrizione breve *', 'design_comuni_italia'),
        'desc' => __('Breve descrizione della Persona Pubblica. Comparirà all\'interno delle card di presentazione del contenuto.', 'design_comuni_italia'),
        'type'       => 'textarea',
        'attributes'    => array(
            'maxlength'  => '255',
            'required' => 'required'
        ),
    ));

    $cmb_user->add_field(array(
        'name'    => __('Foto della Persona', 'design_comuni_italia'),
        'desc'    => __('Inserire una fotografia che ritrae il soggetto descritto nella scheda', 'design_comuni_italia'),
        'id'      => $prefix . 'foto',
        'type'    => 'file',
    ));

    $cmb_competenze = new_cmb2_box(array(
        'id'               => $prefix . 'competenze_box',
        'title'            => __('Competeze/Incarichi e contatti', 'design_comuni_italia'),
        'object_types'     => array('persona_pubblica'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    $cmb_competenze->add_field(array(
        'id' => $prefix . 'incarichi',
        'name'        => __('Incarichi', 'design_comuni_italia'),
        'desc' => __('Collegamenti con gli incarichi', 'design_comuni_italia'),
        'type'    => 'pw_multiselect',
        'options' => dci_get_posts_options('incarico'),
        'attributes' => array(
            'placeholder' =>  __('Seleziona gli Incarichi', 'design_comuni_italia'),
        )
    ));
    
    // $modifica_UO = dci_get_option('ck_modificaUOPersone', 'amministrazione');  // Variabile non più utilizzata, per via di errori nel salvataggio del post

    $attributes = array(
        'placeholder' => __('Seleziona le Unità Organizzative', 'design_comuni_italia'),
    );

    // Se l'opzione non è "true", disabilita il campo
    // if ($modifica_UO !== 'true') {
    //     $attributes['disabled'] = 'disabled';
    // }

    //  $check_contenuti = dci_get_option('ck_collegamenti_contenuti');
    // if($check_contenuti === 'true' || $check_contenuti === true){
    //    $attributes['disabled'] = 'disabled';
        
    // } else{
    //    unset($attributes['disabled']);
    // }


    
    $cmb_competenze->add_field(array(
        'id'       => $prefix . 'organizzazioni',
        'name'     => __('Organizzazione', 'design_comuni_italia'),
        'desc'     => __('Le organizzazioni di cui fa parte (es. Consiglio Comunale; es. Sistemi informativi)', 'design_comuni_italia'),
        'type'     => 'pw_multiselect',
        'options'  => dci_get_posts_options('unita_organizzativa'),
        'attributes' => $attributes
    ));

    $cmb_competenze->add_field(array(
        'id' => $prefix . 'responsabile_di',
        'name'    => __('Responsabile di', 'design_comuni_italia'),
        'desc' => __('Organizzazione di cui è responsabile.', 'design_comuni_italia'),
        'type'    => 'pw_select',
        'options' => dci_get_posts_options('unita_organizzativa'),
        'attributes' => array(
            'placeholder' =>  __('Seleziona le Unità Organizzative', 'design_comuni_italia'),
        )
    ));


    $cmb_competenze->add_field(array(
        'id' => $prefix . 'competenze',
        'name'        => __('Competenze', 'design_comuni_italia'),
        'desc' => __('Se Persona Politica, descrizione testuale del ruolo, comprensiva delle deleghe <br> OPPURE se Persona Amministrativa, descrizione dei compiti di cui si occupa la persona.', 'design_comuni_italia'),
        'type' => 'wysiwyg',
        'options' => array(
            'textarea_rows' => 10,
            'teeny' => false,
        ),
    ));

    $cmb_competenze->add_field(array(
        'id' => $prefix . 'deleghe',
        'name'        => __('Deleghe', 'design_comuni_italia'),
        'desc' => __('Elenco delle deleghe a capo della persona', 'design_comuni_italia'),
        'type' => 'wysiwyg',
        'options' => array(
            'textarea_rows' => 10,
            'teeny' => false,
        ),
    ));

    $cmb_competenze->add_field(array(
        'id' => $prefix . 'punti_contatto',
        'name'        => __('Punti di contatto *', 'design_comuni_italia'),
        'desc' => __('Telefono, mail o altri punti di contatto<br><a href="post-new.php?post_type=punto_contatto">Inserisci Punto di Contatto</a>', 'design_comuni_italia'),
        'type'    => 'pw_multiselect',
        'options' => dci_get_posts_options('punto_contatto'),
        'attributes'    => array(
            'required'    => 'required',
            'placeholder' =>  __(' Seleziona i Punti di Contatto', 'design_comuni_italia'),
        ),
    ));

    // Sezione Data
    $cmb_date = new_cmb2_box(array(
        'id'           => $prefix . 'date_box',
        'title'        => __('Date Mandato', 'design_comuni_italia'),
        'object_types' => array('persona_pubblica'),
        'desc'         => __('Compila questi campi solo se non sono stati inseriti incarichi specifici.', 'design_comuni_italia'),
        'context'      => 'side',
        'priority'     => 'high',
    ));

    $cmb_date->add_field(array(
        'name' => __('Istruzioni Compilazione', 'design_comuni_italia'),
        'desc' => __('Compila questi campi solo se non sono stati inseriti incarichi specifici', 'design_comuni_italia'),
        'id'   => $prefix . 'info_data_title',
        'type' => 'title',
    ));

    $cmb_date->add_field(array(
        'id'          => $prefix . 'data_inizio_incarico',
        'name'        => __('Data di inizio', 'design_comuni_italia'),
        'desc'        => __('Indica la data di insediamento o inizio del mandato.', 'design_comuni_italia'),
        'type'        => 'text_date',
        'date_format' => 'd-m-Y',
    ));

    $cmb_date->add_field(array(
        'id'          => $prefix . 'data_conclusione_incarico',
        'name'        => __('Data di fine', 'design_comuni_italia'),
        'desc'        => __('Indica la data di termine prevista o effettiva del mandato.', 'design_comuni_italia'),
        'type'        => 'text_date',
        'date_format' => 'd-m-Y',
    ));

    // Sezione informazioni Trasparenza
    $cmb_trasparenza = new_cmb2_box(array(
        'id'           => $prefix . 'trasparenza_box',
        'title'        => __('Amministrazione Trasparente', 'design_comuni_italia'),
        'desc'         => __('Documentazione obbligatoria ai sensi del D.Lgs. 33/2013 per i titolari di incarichi politici.', 'design_comuni_italia'),
        'object_types' => array('persona_pubblica'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    // Aggiungi questo come PRIMO campo subito dopo aver definito $cmb_trasparenza
    $cmb_trasparenza->add_field(array(
        'name' => __('Istruzioni Compilazione', 'design_comuni_italia'),
        'desc' => __('Documentazione obbligatoria ai sensi del D.Lgs. 33/2013 per i titolari di incarichi politici.', 'design_comuni_italia'),
        'id'   => $prefix . 'info_trasparenza_title',
        'type' => 'title',
    ));

    $cmb_trasparenza->add_field(array(
        'id'   => $prefix . 'situazione_patrimoniale',
        'name' => __('Situazione patrimoniale', 'design_comuni_italia'),
        'desc' => __('Descrizione della situazione patrimoniale complessiva del titolare dell\'incarico.', 'design_comuni_italia'),
        'type' => 'wysiwyg',
        'options' => array(
            'textarea_rows' => 6,
            'teeny'         => true,
        ),
    ));

    $cmb_trasparenza->add_field(array(
        'id'   => $prefix . 'variazione_situazione_patrimoniale',
        'name' => __('Variazione situazione patrimoniale', 'design_comuni_italia'),
        'desc' => __('Attestazione delle variazioni patrimoniali intervenute nell\'anno precedente rispetto all\'ultima dichiarazione depositata.', 'design_comuni_italia'),
        'type' => 'file_list',
    ));


    $cmb_trasparenza->add_field(array(
        'id'   => $prefix . 'dichiarazione_redditi',
        'name' => __('Dichiarazione dei redditi', 'design_comuni_italia'),
        'desc' => __('Copia dell\'ultima dichiarazione dei redditi (IRPEF). Nota: Per coniuge e parenti entro il 2° grado è necessario il consenso (o evidenza del mancato consenso). Oscurare i dati sensibili non pertinenti.', 'design_comuni_italia'),
        'type' => 'file_list',
    ));

    $cmb_trasparenza->add_field(array(
        'id'   => $prefix . 'spese_elettorali',
        'name' => __('Spese elettorali', 'design_comuni_italia'),
        'desc' => __('Dichiarazione spese sostenute e obbligazioni assunte per la propaganda elettorale o attestazione di uso esclusivo di mezzi del partito. Includere la formula: «Sul mio onore affermo che la dichiarazione corrisponde al vero».', 'design_comuni_italia'),
        'type' => 'file_list',
    ));

    $cmb_trasparenza->add_field(array(
        'id'   => $prefix . 'curriculum_vitae',
        'name' => __('Curriculum Vitae', 'design_comuni_italia'),
        'desc' => __('Carica il CV aggiornato in formato PDF (standard europeo).', 'design_comuni_italia'),
        'type' => 'file',
    ));

    $cmb_trasparenza->add_field(array(
        'id'   => $prefix . 'altre_cariche',
        'name' => __('Altre cariche e compensi', 'design_comuni_italia'),
        'desc' => __('Dati relativi all\'assunzione di altre cariche presso enti pubblici o privati e relativi compensi corrisposti a qualsiasi titolo.', 'design_comuni_italia'),
        'type' => 'file_list',
    ));

    // Relazioni di inizio e fine mandato

    $cmb_trasparenza->add_field(array(
        'id'   => $prefix . 'relazione_inizio_mandato',
        'name' => __('Relazione di inizio mandato', 'design_comuni_italia'),
        'desc' => __('Relazione dettagliata sulle condizioni economiche e patrimoniali del titolare dell\'incarico all\'inizio del mandato, redatta secondo le modalità stabilite dall\'ANAC.', 'design_comuni_italia'),
        'type' => 'file_list',
    ));

    $cmb_trasparenza->add_field(array(
        'id'   => $prefix . 'relazione_fine_mandato',
        'name' => __('Relazione di fine mandato', 'design_comuni_italia'),
        'desc' => __('Relazione dettagliata sulle condizioni economiche e patrimoniali del titolare dell\'incarico alla fine del mandato, redatta secondo le modalità stabilite dall\'ANAC.', 'design_comuni_italia'),
        'type' => 'file_list',
    ));

    // Altri documenti rilevanti

    $cmb_trasparenza->add_field(array(
        'id'   => $prefix . 'altri_documenti',
        'name' => __('Altri documenti', 'design_comuni_italia'),
        'desc' => __('Altri documenti rilevanti per la persona.', 'design_comuni_italia'),
        'type' => 'file_list',
    ));
   

    // Sezioni ulteriori informazioni sulla persona

     $cmb_moreInfo = new_cmb2_box(array(
        'id'               => $prefix . 'moreInfo_box',
        'title'            => __('Ulteriori Informazioni', 'design_comuni_italia'),
        'object_types'     => array('persona_pubblica'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    $cmb_moreInfo->add_field(array(
        'id' => $prefix . 'biografia',
        'name'        => __('Biografia', 'design_comuni_italia'),
        'desc' => __('Solo per Persona Politica: testo descrittivo che riporta la biografia della persona.', 'design_comuni_italia'),
        'type' => 'wysiwyg',
        'options' => array(
            'textarea_rows' => 10,
            'teeny' => false,
        ),
    ));

    
    $cmb_moreInfo->add_field(array(
        'name'       => __('Galleria di immagini', 'design_comuni_italia'),
        'desc' => __('Solo per Persona Politica: gallery dell attività politica e istituzionale della persona.', 'design_comuni_italia'),
        'id'             => $prefix . 'gallery',
        'type' => 'file_list',
        'query_args' => array('type' => 'image'),
        'attributes'    => array(
            'data-conditional-id'     => $prefix . 'tipologia_persona',
            'data-conditional-value'  => "Persona Politica",
        ),
    ));


    $cmb_moreInfo->add_field(array(
        'id' => $prefix . 'ulteriori_informazioni',
        'name'        => __('Ulteriori informazioni', 'design_comuni_italia'),
        'desc' => __('Ulteriori informazioni relative alla persona.', 'design_comuni_italia'),
        'type' => 'wysiwyg',
        'options' => array(
            'textarea_rows' => 10,
            'teeny' => false,
        ),
    ));
}

/**
 * Valorizzo il post title in base ai campi Nome e Cognome
 * @param $data
 * @return mixed
 */
function dci_persona_pubblica_set_post_title($data)
{

    if ($data['post_type'] == 'persona_pubblica') {

        if (isset($_POST['_dci_persona_pubblica_nome'])  && isset($_POST['_dci_persona_pubblica_cognome'])) {

            $nome = $_POST['_dci_persona_pubblica_nome'];
            $cognome = $_POST['_dci_persona_pubblica_cognome'];
            $title = $nome . ' ' . $cognome;
            $data['post_title'] =  $title;
            unset($data['post_name']);
        }

        $descrizione_breve = '';
        if (isset($_POST['_dci_persona_pubblica_descrizione_breve'])) {
            $descrizione_breve = $_POST['_dci_persona_pubblica_descrizione_breve'];
        }

        $competenze = '';
        if (isset($_POST['_dci_persona_pubblica_competenze'])) {
            $competenze = $_POST['_dci_persona_pubblica_competenze'];
        }

        $info = '';
        if (isset($_POST['_dci_persona_pubblica_ulteriori_informazioni'])) {
            $info = $_POST['_dci_persona_pubblica_ulteriori_informazioni'];
        }

        $content = $descrizione_breve . '<br>' . $competenze . '<br>' . $info;

        $data['post_content'] = $content;
    }

    return $data;
}
add_filter('wp_insert_post_data', 'dci_persona_pubblica_set_post_title', '99', 1);

