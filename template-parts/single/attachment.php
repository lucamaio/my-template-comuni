<?php
global $file_url;

$file_url = trim((string) $file_url);
$file_info = getFileSizeAndFormat($file_url);
?>

<div class="cmp-icon-link">
    <a class="list-item icon-left d-inline-block" href="<?php echo esc_url($file_url); ?>" aria-label="Scarica Termini e condizioni di servizio (<?php echo esc_attr($file_info); ?>)" data-element="service-file">
    <span class="list-item-title-icon-wrapper">
        <svg class="icon icon-primary icon-sm me-1" aria-hidden="true">
        <use href="#it-clip"></use>
        </svg>
        <span class="list-item t-primary">Termini e condizioni di servizio (<?php echo esc_html($file_info); ?>)</span>
    </span>
    </a>
</div>
