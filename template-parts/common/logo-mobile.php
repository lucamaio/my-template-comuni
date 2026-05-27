<?php
$stemma_mobile_url = dci_get_option('stemma_comune_mobile');
if ( ! $stemma_mobile_url ) {
    return;
}

$nome_comune = trim( (string) dci_get_option( 'nome_comune' ) );
$logo_label  = $nome_comune ? 'Stemma del Comune di ' . $nome_comune : 'Stemma del Comune';
?>
<svg width="48" height="48" role="img" aria-label="<?php echo esc_attr( $logo_label ); ?>">
    <title><?php echo esc_html( $logo_label ); ?></title>
    <image xlink:href="<?php echo esc_url( $stemma_mobile_url ); ?>" width="48" height="48" alt="<?php echo esc_attr( $logo_label ); ?>" title="<?php echo esc_attr( $logo_label ); ?>"/>
</svg>
