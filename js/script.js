jQuery(document).ready(function() {
    jQuery('#colorpicker').hide();
    jQuery('#colorpicker').farbtastic("#product_css3_tags_customcolor");
    jQuery("#product_css3_tags_customcolor").click(function(){jQuery('#colorpicker').slideToggle()});
});