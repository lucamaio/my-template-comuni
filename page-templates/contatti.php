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
    :root {
        --primary-color: #007bff; /* Blu moderno */
        --secondary-color: #f8f9fa; /* Grigio chiaro */
        --accent-color: #28a745; /* Verde per link attivi */
        --shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Ombra morbida */
        --border-radius: 12px; /* Bordi arrotondati */
        --transition: all 0.3s ease; /* Transizioni fluide */
    }

    .contact-card {
        background: linear-gradient(135deg, var(--secondary-color) 0%, #ffffff 100%); /* Gradiente sottile */
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: var(--transition);
    }

    .contact-card:hover {
        transform: translateY(-2px); /* Effetto sollevamento al hover */
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .contact-header {
        background: linear-gradient(90deg, var(--primary-color), #0056b3); /* Gradiente header */
        color: white;
        padding: 1.5rem;
        border-bottom: none;
    }

    .contact-header h2 {
        font-weight: 600;
        font-size: 1.25rem;
        margin: 0;
    }

    .contact-body {
        padding: 0;
    }

    .contact-list-item {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e9ecef;
        transition: var(--transition);
        display: flex;
        align-items: flex-start;
    }

    .contact-list-item:last-child {
        border-bottom: none;
    }

    .contact-list-item:hover {
        background-color: rgba(0, 123, 255, 0.05); /* Sfondo leggero al hover */
    }

    .contact-icon {
        color: var(--primary-color);
        font-size: 1.5rem;
        margin-right: 1rem;
        flex-shrink: 0;
        transition: var(--transition);
    }

    .contact-icon:hover {
        color: var(--accent-color);
        transform: scale(1.1); /* Icona cresce leggermente */
    }

    .contact-link {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition);
    }

    .contact-link:hover {
        color: var(--accent-color);
        text-decoration: underline;
    }

    /* Responsive per mobile */
    @media (max-width: 768px) {
        .contact-list-item {
            flex-direction: column;
            align-items: flex-start;
        }
        .contact-icon {
            margin-bottom: 0.5rem;
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

                    <div class="card-header contact-header" id="contact-header">
                        <h2 class="h5 mb-0">
                            <i class="fa-solid fa-address-book me-2" aria-hidden="true"></i>
                            Contatti istituzionali
                        </h2>
                    </div>

                    <div class="card-body contact-body">
                        <ul class="list-group list-group-flush">

                            <?php
                            $indirizzo = dci_get_option("contatti_indirizzo", 'footer');
                            if (!empty($indirizzo)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-solid fa-location-dot contact-icon" aria-hidden="true"></i>
                                        <div>
                                            <strong>Indirizzo:</strong><br>
                                            ' . esc_html($indirizzo) . '
                                        </div>
                                      </li>';
                            }

                            $cf_piva = dci_get_option("contatti_CF_PIVA", 'footer');
                            if (!empty($cf_piva)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-solid fa-id-card contact-icon" aria-hidden="true"></i>
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
                                            <i class="fa-solid fa-building-user contact-icon" aria-hidden="true"></i>
                                            <div>
                                                <strong>Ufficio Relazioni con il Pubblico (URP):</strong><br>
                                                <a href="' . esc_url(get_permalink($ufficio_id)) . '" class="contact-link">
                                                    ' . esc_html($ufficio->post_title) . '
                                                </a>
                                            </div>
                                          </li>';
                                }
                            }

                            $numero_verde = dci_get_option("numero_verde", 'footer');
                            if (!empty($numero_verde)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-solid fa-phone-volume contact-icon" aria-hidden="true"></i>
                                        <div>
                                            <strong>Numero verde:</strong><br>
                                            <a href="tel:' . esc_html($numero_verde) . '" class="contact-link">
                                                ' . esc_html($numero_verde) . '
                                            </a>
                                        </div>
                                      </li>';
                            }

                            $sms_whatsapp = dci_get_option("SMS_Whatsapp", 'footer');
                            if (!empty($sms_whatsapp)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-brands fa-whatsapp contact-icon" aria-hidden="true"></i>
                                        <div>
                                            <strong>SMS e WhatsApp:</strong><br>
                                            ' . esc_html($sms_whatsapp) . '
                                        </div>
                                      </li>';
                            }

                            $pec = dci_get_option("contatti_PEC", 'footer');
                            if (!empty($pec)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-solid fa-envelope-circle-check contact-icon" aria-hidden="true"></i>
                                        <div>
                                            <strong>Posta Elettronica Certificata (PEC):</strong><br>
                                            <a href="mailto:' . esc_attr($pec) . '" class="contact-link">
                                                ' . esc_html($pec) . '
                                            </a>
                                        </div>
                                      </li>';
                            }

                            $centralino = dci_get_option("centralino_unico", 'footer');
                            if (!empty($centralino)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-solid fa-headset contact-icon" aria-hidden="true"></i>
                                        <div>
                                            <strong>Centralino unico:</strong><br>
                                            <a href="tel:' . esc_html($centralino) . '" class="contact-link">
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