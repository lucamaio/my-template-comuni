<?php
$booking_steps = array(
    1 => 'Privacy',
    2 => 'Luogo',
    3 => 'Data e orario',
    4 => 'Dettagli',
    5 => 'Richiedente',
    6 => 'Riepilogo',
);
?>
<div class="container booking-progress-navigation">
    <?php foreach ($booking_steps as $active_step => $active_label) { ?>
        <div class="cmp-info-progress d-flex d-none" data-progress="<?php echo esc_attr($active_step); ?>">
            <?php foreach ($booking_steps as $step => $label) {
                $classes = 'info-progress-wrapper d-none d-lg-flex w-100 px-3 flex-column justify-content-end';
                if ($step < $active_step) {
                    $classes .= ' completed';
                } elseif ($step === $active_step) {
                    $classes .= ' step-active';
                }
                ?>
                <div class="<?php echo esc_attr($classes); ?>">
                    <div class="info-progress-body d-flex justify-content-between align-self-end align-items-end w-100 py-3">
                        <span class="d-block h-100 title-medium text-uppercase"><?php echo esc_html($label); ?></span>
                        <?php if ($step < $active_step) { ?>
                            <svg class="d-block icon icon-primary icon-sm" aria-hidden="true">
                                <use href="#it-check"></use>
                            </svg>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
            <div class="iscrizioni-header d-lg-none w-100">
                <h2 class="step-title d-flex align-items-center justify-content-between drop-shadow">
                    <span><?php echo esc_html($active_label); ?></span>
                    <span class="step"><?php echo esc_html($active_step); ?>/6</span>
                </h2>
                <?php if ($active_step < 6) { ?>
                    <p class="title-xsmall mt-40 mb-3">
                        I campi contraddistinti dal simbolo asterisco sono obbligatori
                    </p>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</div>
