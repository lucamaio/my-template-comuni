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

                <p>
                    Benvenuto nella pagina dedicata al contatto con gli uffici comunali.
                    Puoi richiedere un incontro tramite <strong>videociamata</strong> o <strong>chiamata vocale</strong>, oppure interagire direttamente con un operatore attraverso la chat.
                </p>

                <p>
                    Per effettuare una videochiamata o una chiamata vocale è necessario prenotare un appuntamento tramite l'apposito modulo.
                    Ti verrà richiesto di fornire i tuoi dati, quali nome e cognome, indirizzo email e un messaggio.
                    Nella sezione "messaggio" dovrai indicare il motivo della prenotazione e il nome dell'ufficio da contattare.
                </p>

                <p>
                    Per accedere alla videochiamata o alla chiamata vocale basterà utilizzare il link fornito nell'email che riceverai all'indirizzo email inserito.
                </p>

                <p>
                    <strong>Attenzione</strong>: l'apertura della chat con un operatore potrebbe richiedere qualche istante.
                    Se non dovesse aprirsi, puoi utilizzare il pulsante nella parte destra della pagina.
                </p>

                <!-- Pulsante chat centrato -->
                <div class="text-center my-5">
                    <p id="messagge" style="display:none; color:#842029; background-color:#f8d7da; border:1px solid #f5c2c7; padding:10px; border-radius:5px; margin-bottom:15px;"></p>
                    <button id="btn-chat" class="btn btn-primary">Avvia chat con un operatore</button>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {

                        // Funzione per aprire Consolto
                        function apriChat() {
                            if (typeof Consolto !== "undefined" && typeof Consolto.open === "function") {
                                Consolto.open();
                                return true;
                            }
                            return false;
                        }

                        // Carica lo script se non esiste
                        if (!document.getElementById('et-iframe')) {
                            const el = document.createElement('script');
                            el.src = 'https://client.consolto.com/iframeApp/iframeApp.js';
                            el.id = 'et-iframe';
                            el.async = true;
                            el.setAttribute('data-widgetId', '694140e7500355203a1be9a1');
                            el.setAttribute('data-version', '0.5');
                            el.setAttribute('data-test', 'false');
                            document.body.appendChild(el);
                        }

                        // Tentativi di apertura automatica
                        let tentativi = 0;
                        const interval = setInterval(() => {
                            if (apriChat()) {
                                clearInterval(interval);
                            }
                            tentativi++;
                            if (tentativi > 20) {
                                clearInterval(interval);
                                const messagge = document.getElementById('messagge');
                                messagge.style.display = 'block'; // mostra il messaggio
                                messagge.innerHTML = 'Il servizio di chat non è disponibile al momento. <strong>Provare ad accedere dal pulsante in basso a destra</strong>';
                                console.warn("Il servizio di chat non è disponibile al momento.");
                            }
                        }, 300);

                        // Event listener sul pulsante
                        const btn = document.getElementById('btn-chat');
                        if (btn) {
                            btn.addEventListener('click', apriChat);
                        }

                    });
                </script>
                <!-- Box recapiti -->
                <div class="card border-primary my-3 p-3">
                    <h5>Contatti</h5>

                    <?php
                    $indirizzo = dci_get_option("contatti_indirizzo", 'footer');
                    if (!empty($indirizzo)) {
                        echo '<p>Indirizzo: ' . esc_html($indirizzo) . '</p>';
                    }

                    $cf_piva = dci_get_option("contatti_CF_PIVA", 'footer');
                    if (!empty($cf_piva)) {
                        echo '<p>Codice fiscale / P. IVA: ' . esc_html($cf_piva) . '</p>';
                    }

                    $ufficio_id = dci_get_option("contatti_URP", 'footer');
                    if (!empty($ufficio_id)) {
                        $ufficio = get_post($ufficio_id);
                        if ($ufficio) {
                            echo '<p><a href="' . esc_url(get_permalink($ufficio_id)) . '" class="list-item" title="Vai alla pagina URP">'
                                . esc_html($ufficio->post_title) . '</a></p>';
                        }
                    }

                    $numero_verde = dci_get_option("numero_verde", 'footer');
                    if (!empty($numero_verde)) {
                        echo '<p>Numero verde: <a href="tel:' . esc_html($numero_verde) . '" class="list-item">' . esc_html($numero_verde) . '</a></p>';
                    }

                    $sms_whatsapp = dci_get_option("SMS_Whatsapp", 'footer');
                    if (!empty($sms_whatsapp)) {
                        echo '<p>SMS e WhatsApp: ' . esc_html($sms_whatsapp) . '</p>';
                    }

                    $pec = dci_get_option("contatti_PEC", 'footer');
                    if (!empty($pec)) {
                        echo '<p>PEC: <a href="mailto:' . esc_attr($pec) . '" class="list-item" title="PEC ' . esc_attr(dci_get_option("nome_comune")) . '">' . esc_html($pec) . '</a></p>';
                    }

                    $centralino = dci_get_option("centralino_unico", 'footer');
                    if (!empty($centralino)) {
                        echo '<p>Centralino unico: <a href="tel:' . esc_html($centralino) . '" class="list-item">' . esc_html($centralino) . '</a></p>';
                    }
                    ?>
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
</main>

<?php
get_footer();
?>