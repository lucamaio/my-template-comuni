<?php
$booking_indexes = array(
    1 => array(
        array('href' => '#booking-privacy', 'label' => 'Informativa sulla privacy'),
    ),
    2 => array(
        array('href' => '#office', 'label' => 'Ufficio'),
    ),
    3 => array(
        array('href' => '#appointment-available', 'label' => 'Appuntamenti disponibili'),
        array('href' => '#office-2', 'label' => 'Ufficio'),
    ),
    4 => array(
        array('href' => '#reason', 'label' => 'Motivo'),
        array('href' => '#details', 'label' => 'Dettagli'),
    ),
    5 => array(
        array('href' => '#applicant', 'label' => 'Richiedente'),
    ),
    6 => array(
        array('href' => '#booking-summary', 'label' => 'Riepilogo'),
    ),
);

foreach ($booking_indexes as $step => $links) {
    $heading_id = 'booking-index-title-' . $step;
    $collapse_id = 'booking-index-' . $step;
    ?>
    <aside
        class="col-12 col-lg-3 mb-4 d-none booking-side-navigation"
        data-index="<?php echo esc_attr($step); ?>"
        aria-label="Indice del passaggio corrente"
    >
        <div class="cmp-navscroll sticky-top" aria-labelledby="<?php echo esc_attr($heading_id); ?>">
            <nav class="navbar it-navscroll-wrapper navbar-expand-lg" aria-label="Informazioni richieste" data-bs-navscroll>
                <div class="navbar-custom w-100">
                    <div class="menu-wrapper">
                        <div class="link-list-wrapper">
                            <div class="accordion">
                                <div class="accordion-item">
                                    <span class="accordion-header" id="<?php echo esc_attr($heading_id); ?>">
                                        <button
                                            class="accordion-button pb-10 px-3"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#<?php echo esc_attr($collapse_id); ?>"
                                            aria-expanded="true"
                                            aria-controls="<?php echo esc_attr($collapse_id); ?>"
                                        >
                                            INFORMAZIONI RICHIESTE
                                            <svg class="icon icon-xs right" aria-hidden="true">
                                                <use href="#it-expand"></use>
                                            </svg>
                                        </button>
                                    </span>
                                    <div class="progress">
                                        <div
                                            class="progress-bar it-navscroll-progressbar"
                                            role="progressbar"
                                            aria-valuenow="0"
                                            aria-valuemin="0"
                                            aria-valuemax="100"
                                        ></div>
                                    </div>
                                    <div
                                        id="<?php echo esc_attr($collapse_id); ?>"
                                        class="accordion-collapse collapse show"
                                        role="region"
                                        aria-labelledby="<?php echo esc_attr($heading_id); ?>"
                                    >
                                        <div class="accordion-body">
                                            <ul class="link-list" data-element="page-index">
                                                <?php foreach ($links as $link) { ?>
                                                    <li class="nav-item">
                                                        <a class="nav-link" href="<?php echo esc_attr($link['href']); ?>">
                                                            <span class="title-medium"><?php echo esc_html($link['label']); ?></span>
                                                        </a>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </aside>
    <?php
}
