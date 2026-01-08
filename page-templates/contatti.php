<?php
/**
 * Template Name: Contatti
 * Description: Pagina contenente i contatti dell'ente
 *
 * @package Design_Comuni_Italia
 */

get_header();
?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Stile moderno per la pagina contatti -->
<style>

    /* Card principale */
    .contact-card {
        /* background-color: #f0f0f0ff; */
        border: 1px solid var(--border-color);
        border-radius: 0; /* BORDI NON ARROTONDATI */
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    /* Header card */
    .contact-header {
        color: #ffffff;
        padding: 1.25rem 1.5rem;
        border-radius: 0;
    }

    .contact-header h2 {
        font-weight: 600;
        font-size: 1.25rem;
        margin: 0;
    }

    /* Corpo card */
    .contact-body {
        padding: 0;
    }

    /* Elementi elenco */
    .contact-list-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        /* border-bottom: 1px solid var(--border-color); */
        background-color: #ffffff;
        /* transition: var(--transition); */
    }

    .contact-list-item {
        border-left: 3px solid transparent;
    }

    .contact-list-item:hover {
        background-color: rgba(0, 0, 0, 0.04);
        border-left-color: var(--bs-primary);
    }



    /* Icone */
    .contact-icon {
        font-size: 1.4rem;
        line-height: 1.4;
        flex-shrink: 0;
    }

    /* Link */
    .contact-link {
        font-weight: 500;
        text-decoration: none;
    }

    .contact-link:hover {
        text-decoration: underline;
    }

    /* Responsive mobile */
    @media (max-width: 768px) {
        .contact-list-item {
            flex-direction: column;
            gap: 0.5rem;
        }

        .contact-icon {
            font-size: 1.3rem;
        }
    }
</style>

<main id="content">
    <div class="container" id="main-container">

        <!-- Breadcrumb -->
        <div class="row mb-4">
            <div class="col px-lg-4">
                <?php get_template_part("template-parts/hero/hero"); ?>
            </div>
        </div>

        <!-- Card Contatti Moderna -->
        <div class="row">
            <div class="col-lg-10 offset-lg-1 px-lg-4">
                <div class="card contact-card mb-4" role="region" aria-labelledby="contact-header">

                    <div class="card-header contact-header bg-primary" id="contact-header">
                        <h2 class="h5 mb-0">
                            <i class="fa-solid fa-address-book me-2" aria-hidden="true"></i>
                            Contatti istituzionali
                        </h2>
                    </div>

                    <div class="card-body contact-body bg-grey-dsk">
                        <ul class="list-group list-group-flush">

                            <?php
                            $indirizzo = dci_get_option("contatti_indirizzo", 'footer');
                            if (!empty($indirizzo)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-solid fa-location-dot contact-icon text-decoration-none" aria-hidden="true"></i>
                                        <div>
                                            <strong>Indirizzo:</strong><br>
                                            ' . esc_html($indirizzo) . '
                                        </div>
                                      </li>';
                            }

                            $cf_piva = dci_get_option("contatti_CF_PIVA", 'footer');
                            if (!empty($cf_piva)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-solid fa-id-card contact-icon text-decoration-none" aria-hidden="true"></i>
                                        <div>
                                            <strong>Codice fiscale / Partita IVA:</strong><br>
                                            ' . esc_html($cf_piva) . '
                                        </div>
                                      </li>';
                            }

                            $ufficio_id = dci_get_option("contatti_URP", 'footer');
                            if (!empty($ufficio_id)) {
                                $ufficio = get_post($ufficio_id);
                                if ($ufficio) {
                                    echo '<li class="list-group-item contact-list-item">
                                            <i class="fa-solid fa-building-user contact-icon text-decoration-none" aria-hidden="true"></i>
                                            <div>
                                                <strong>Ufficio Relazioni con il Pubblico (URP):</strong><br>
                                                <a href="' . esc_url(get_permalink($ufficio_id)) . '" class="text-decoration-none">
                                                    ' . esc_html($ufficio->post_title) . '
                                                </a>
                                            </div>
                                          </li>';
                                }
                            }

                            $numero_verde = dci_get_option("numero_verde", 'footer');
                            if (!empty($numero_verde)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-solid fa-phone-volume contact-icon text-decoration-none" aria-hidden="true"></i>
                                        <div>
                                            <strong>Numero verde:</strong><br>
                                            <a href="tel:' . esc_html($numero_verde) . '" class="text-decoration-none">
                                                ' . esc_html($numero_verde) . '
                                            </a>
                                        </div>
                                      </li>';
                            }

                            $sms_whatsapp = dci_get_option("SMS_Whatsapp", 'footer');
                            if (!empty($sms_whatsapp)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-brands fa-whatsapp contact-icon text-decoration-none" aria-hidden="true"></i>
                                        <div>
                                            <strong>SMS e WhatsApp:</strong><br>
                                            ' . esc_html($sms_whatsapp) . '
                                        </div>
                                      </li>';
                            }

                            $pec = dci_get_option("contatti_PEC", 'footer');
                            if (!empty($pec)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-solid fa-envelope-circle-check contact-icon text-decoration-none" aria-hidden="true"></i>
                                        <div>
                                            <strong>Posta Elettronica Certificata (PEC):</strong><br>
                                            <a href="mailto:' . esc_attr($pec) . '" class="text-decoration-none">
                                                ' . esc_html($pec) . '
                                            </a>
                                        </div>
                                      </li>';
                            }

                            $centralino = dci_get_option("centralino_unico", 'footer');
                            if (!empty($centralino)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-solid fa-headset contact-icon text-decoration-none" aria-hidden="true"></i>
                                        <div>
                                            <strong>Centralino unico:</strong><br>
                                            <a href="tel:' . esc_html($centralino) . '" class="text-decoration-none">
                                                ' . esc_html($centralino) . '
                                            </a>
                                        </div>
                                      </li>';
                            }
                            ?>

                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenuti aggiuntivi -->
        <article id="more-info">
            <div class="row">
                <div class="col-lg-12">
                    <?php get_template_part("template-parts/single/bottom"); ?>
                </div>
            </div>
        </article>

    </div>

    <?php get_template_part("template-parts/common/valuta-servizio"); ?>
</main>

<?php get_footer(); ?>

