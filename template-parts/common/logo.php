<?php
$stemma_url = dci_get_option('stemma_comune');
if ( ! $stemma_url ) {
    return;
}

$nome_comune = trim( (string) dci_get_option( 'nome_comune' ) );
$logo_label  = $nome_comune ? 'Stemma del Comune di ' . $nome_comune : 'Stemma del Comune';
?>
<svg width="82" height="82" class="icon" role="img" aria-label="<?php echo esc_attr( $logo_label ); ?>">
    <title><?php echo esc_html( $logo_label ); ?></title>
    <image xlink:href="<?php echo esc_url( $stemma_url ); ?>" width="82" height="82" alt="<?php echo esc_attr( $logo_label ); ?>" title="<?php echo esc_attr( $logo_label ); ?>"/>
</svg>
