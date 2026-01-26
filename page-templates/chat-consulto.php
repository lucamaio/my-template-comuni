<?php

/**
 * Template Name: Chat Consulto
 * Description: Pagina dedicata alla prenotazione di appuntamenti via videochiamata o vocale con gli uffici comunali, con possibilità di interagire direttamente con un operatore tramite chat.
 *
 * @package Design_Comuni_Italia
 */

get_header();
?>

<main>
    <div class="container" id="main-container">

        <div class="row">
            <div class="col px-lg-4">
                <?php get_template_part("template-parts/common/breadcrumb"); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 px-lg-4 py-lg-2">

                <h1 class="mb-4">Contatta gli uffici comunali</h1>

                <p class="mb-4">
                    Benvenuto nella pagina dedicata al contatto con gli uffici comunali. Qui puoi richiedere un incontro tramite <strong>videochiamata</strong> o <strong>chiamata vocale</strong>, oppure interagire direttamente con un operatore tramite <strong>chat</strong>.
                </p>

                <p class="mb-4">
                   <strong>Per effettuare una videochiamata o una chiamata vocale è necessario prenotare un appuntamento tramite l’apposito modulo.</STRONG> Ti verranno richiesti i seguenti dati: nome e cognome, indirizzo email e un messaggio. 
                   Nella voce “messaggio” dovrai indicare il motivo della prenotazione e il nome dell’ufficio da contattare.
                </p>

                <p class="mb-4">
                    Una volta che hai effetuato una prenotazione, ti verrà inviata un’email di conferma all’indirizzo indicato nel modulo con il link per accedere all’incontro all’orario stabilito.
                    Per accedere all’incontro, ti basterà utilizzare il link fornito nell’email di conferma all’orario stabilito.<br>
                    <strong>Attenzione:</strong> per motivi di sicurezza, il link sarà valido solo per l’orario indicato nell’email di conferma.
                </p>

                <p class="alert alert-warning mb-4" role="alert">
                    <strong>Attenzione</strong>: questa funzionalità è disponibile solo se l'utente accede con le proprie credenziali SPID o CIE. Tramite il seguente link puoi effettuare il login:
                    <a href="https://servizi.comune.mottacamastra.me.it/Servizi/FiloDiretto2/ProcedimentiClient.aspx?CE=mttcmstr4321&IDPr=13481">Login SPID/CIE</a><br>
                    Puoi ignorarne il contenuto di questo avviso se hai già effettuato l’accesso con le tue credenziali SPID o CIE.
                </p>

                        <button id="btn-consolto" style="
                          display:none;
                          padding: 12px 22px;
                          background: #0b5ed7;
                          color: #ffffff;
                          border: none;
                          border-radius: 10px;
                          font-size: 16px;
                          font-weight: 600;
                          cursor: pointer;
                        ">
                          🎥 Avvia video chat
                        </button>

                
           
               <!-- Box recapiti -->
                <div class="card border-primary my-2 p-2">
                    <h5>Contatti</h5>
                    <ul style="list-style-type: disc; padding-left: 20px;">
                        <?php
                        $indirizzo = dci_get_option("contatti_indirizzo", 'footer');
                        if (!empty($indirizzo)) {
                            echo '<li><strong>Indirizzo:</strong> ' . esc_html($indirizzo) . '</li>';
                        }

                        $cf_piva = dci_get_option("contatti_CF_PIVA", 'footer');
                        if (!empty($cf_piva)) {
                            echo '<li><strong>Codice fiscale / P. IVA:</strong> ' . esc_html($cf_piva) . '</li>';
                        }

                        $ufficio_id = dci_get_option("contatti_URP", 'footer');
                        if (!empty($ufficio_id)) {
                            $ufficio = get_post($ufficio_id);
                            if ($ufficio) {
                                echo '<li><a href="' . esc_url(get_permalink($ufficio_id)) . '" class="list-item" title="Vai alla pagina URP">'
                                    . esc_html($ufficio->post_title) . '</a></li>';
                            }
                        }

                        $numero_verde = dci_get_option("numero_verde", 'footer');
                        if (!empty($numero_verde)) {
                            echo '<li><strong>Numero verde:</strong> <a href="tel:' . esc_html($numero_verde) . '" class="list-item">' . esc_html($numero_verde) . '</a></li>';
                        }

                        $sms_whatsapp = dci_get_option("SMS_Whatsapp", 'footer');
                        if (!empty($sms_whatsapp)) {
                            echo '<li><strong>SMS e WhatsApp:</strong> ' . esc_html($sms_whatsapp) . '</li>';
                        }

                        $pec = dci_get_option("contatti_PEC", 'footer');
                        if (!empty($pec)) {
                            echo '<li><strong>PEC:</strong> <a href="mailto:' . esc_attr($pec) . '" class="list-item" title="PEC ' . esc_attr(dci_get_option("nome_comune")) . '">' . esc_html($pec) . '</a></li>';
                        }

                        $centralino = dci_get_option("centralino_unico", 'footer');
                        if (!empty($centralino)) {
                            echo '<li><strong>Centralino unico:</strong> <a href="tel:' . esc_html($centralino) . '" class="list-item">' . esc_html($centralino) . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>


            </div>
        </div>

        <article id="more-info">
            <div class="row variable-gutters">
                <div class="col-lg-12">
                    <?php get_template_part("template-parts/single/bottom"); ?>
                </div>
            </div>
        </article>

    </div>

    <?php get_template_part("template-parts/common/valuta-servizio"); ?>

    <?php get_template_part("template-parts/common/assistenza-contatti"); ?>
</main>

<?php
get_footer();

?>


