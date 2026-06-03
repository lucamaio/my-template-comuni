<?php
global $nomefile, $idfile;

$icon = "svg-documents";
if (substr((string) $nomefile, -3) == "pdf") {
    $icon = "it-pdf-document";
}
if (substr((string) $nomefile, -3) == "doc") {
    $icon = "svg-doc-document";
}
if (substr((string) $nomefile, -4) == "docx") {
    $icon = "svg-doc-document";
}
if (substr((string) $nomefile, -3) == "xml") {
    $icon = "svg-xml-document";
}

$attach = get_post($idfile);
$filetocheck = get_attached_file($idfile);
$file_url = wp_get_attachment_url($idfile);
$file_info = $file_url ? getFileSizeAndFormat($file_url) : 'FILE';
$ptitle = $attach ? $attach->post_title : '';
if (trim($ptitle) == "") {
    $filename = $filetocheck ? basename($filetocheck) : (string) $nomefile;
    $ptitle = str_replace(array("-", "_"), " ", pathinfo($filename, PATHINFO_FILENAME));
}
?>
    <div class="card card-bg card-icon rounded">
        <div class="card-body">
            <svg class="icon <?php echo esc_attr($icon); ?>"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#<?php echo esc_attr($icon); ?>"></use></svg>
            <div class="card-icon-content">
                <p><strong><a target="_blank" href="<?php echo esc_url($file_url); ?>"><?php echo esc_html($ptitle); ?></a></strong></p>
                <small><?php echo esc_html($file_info); ?></small>
            </div><!-- /card-icon-content -->
        </div><!-- /card-body -->
    </div><!-- /card card-bg card-icon rounded -->
<?php
