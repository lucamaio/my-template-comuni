jQuery(document).ready(function($) {
    
    let input = $('input[name^="_dci_progetto_tipo_progetto"]');
    input.each(function() {
        $(this).click(function(){
            dci_remove_highlight_missing_field('.cmb2-id--dci-progetto-tipo_progetto');
        });
    });

    $('form[name="post"]').on('submit', function(e) {

        /**
         * Controllo compilazione campo tipo progetto
         */
        if(document.activeElement.id === 'publish' && $('input[name^="_dci_progetto_tipo_progetto"]:checked').length === 0) {
            dci_highlight_missing_field('.cmb2-id--dci-progetto-tipo_progetto');
            return false;
        }
        return true;
    });
});

function dci_highlight_missing_field(fieldClass) {
    jQuery(fieldClass).addClass("highlighted_missing_field")
        .append('<div id="field-required-msg" class="field-required-msg"><em>Campo obbligatorio</em></div>');
    
    jQuery('html,body').animate({
        scrollTop: jQuery("#field-required-msg").parent().offset().top - 100
    }, 'slow');
}

function dci_remove_highlight_missing_field(fieldClass) {
    jQuery(fieldClass).removeClass("highlighted_missing_field");
    jQuery('.field-required-msg').remove();
}
