const theme_folder = dci_data.stylesheet_directory_uri;

jQuery( document ).ready(function() {
    //inject bootstrap-italia icons
    loadSvg();
    jQuery('#wpcontent').click(function () {
            loadSvg();
        }
    );
    jQuery('.selector-search input').on('input',function () {
        setTimeout( function(){
            loadSvg();
        }  , 200);
    });

    //avoid edit form html validation if saving as draft
    jQuery('#save-post').on('click', function(){
        jQuery(':input').filter("[required='required']").attr('required', false)
    })

    //validate edit form fields
    jQuery( 'body' ).on( 'submit.edit-post', '#post', function () {
        //validate only if publishing
        if (document.activeElement.id !== 'publish') {
            return true;
        }
    });
});

function loadSvg(){
    var options = [
        'it-arrow-down',
        'it-arrow-down-circle',
        'it-arrow-down-triangle',
        'it-arrow-left',
        'it-arrow-left-circle',
        'it-arrow-left-triangle',
        'it-arrow-right',
        'it-arrow-right-circle',
        'it-arrow-right-triangle',
        'it-arrow-up',
        'it-arrow-up-circle',
        'it-arrow-up-triangle',
        'it-ban',
        'it-bookmark',
        'it-box',
        'it-burger',
        'it-calendar',
        'it-camera',
        'it-card',
        'it-chart-line',
        'it-check',
        'it-check-circle',
        'it-chevron-left',
        'it-chevron-right',
        'it-clip',
        'it-clock',
        'it-close',
        'it-close-big',
        'it-close-circle',
        'it-code-circle',
        'it-collapse',
        'it-comment',
        'it-copy',
        'it-delete',
        'it-download',
        'it-error',
        'it-exchange-circle',
        'it-expand',
        'it-external-link',
        'it-file',
        'it-files',
        'it-flag',
        'it-folder',
        'it-fullscreen',
        'it-funnel',
        'it-hearing',
        'it-help',
        'it-help-circle',
        'it-horn',
        'it-inbox',
        'it-info-circle',
        'it-key',
        'it-link',
        'it-list',
        'it-locked',
        'it-mail',
        'it-map-marker',
        'it-map-marker-circle',
        'it-map-marker-minus',
        'it-map-marker-plus',
        'it-maximize',
        'it-maximize-alt',
        'it-minimize',
        'it-minus',
        'it-minus-circle',
        'it-more-actions',
        'it-more-items',
        'it-note',
        'it-open-source',
        'it-pa',
        'it-password-invisible',
        'it-password-visible',
        'it-pencil',
        'it-piattaforme',
        'it-pin',
        'it-plug',
        'it-plus',
        'it-plus-circle',
        'it-presentation',
        'it-print',
        'it-refresh',
        'it-rss',
        'it-rss-square',
        'it-search',
        'it-settings',
        'it-share',
        'it-software',
        'it-star-full',
        'it-star-outline',
        'it-telephone',
        'it-tool',
        'it-unlocked',
        'it-upload',
        'it-user',
        'it-video',
        'it-warning',
        'it-warning-circle',
        'it-wifi',
        'it-zoom-in',
        'it-zoom-out',
        'it-restore',
        'it-behance',
        'it-facebook',
        'it-facebook-square',
        'it-flickr',
        'it-flickr-square',
        'it-github',
        'it-instagram',
        'it-linkedin',
        'it-linkedin-square',
        'it-medium',
        'it-medium-square',
        'it-telegram',
        'it-twitter',
        'it-twitter-square',
        'it-whatsapp',
        'it-whatsapp-square',
        'it-youtube',
        'it-google',
        'it-designers-italia',
        'it-team-digitale',
        'user_ldap_team',
    ];
    options.forEach(element => jQuery('.' + element).html('<img src= "' + theme_folder +'/assets/svg/' + element + '.svg" alt="'+element+'" style="width:30px; margin-right:10px;">'));
}
