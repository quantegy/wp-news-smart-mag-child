jQuery(function() {
    jQuery('.menu-item-has-children a').focus( function () {
        jQuery(this).siblings('.sub-menu').addClass('focused');
    }).blur(function(){
        jQuery(this).siblings('.sub-menu').removeClass('focused');
    });

// Sub Menu
    jQuery('.sub-menu a').focus( function () {
        jQuery(this).parents('.sub-menu').addClass('focused');
    }).blur(function(){
        jQuery(this).parents('.sub-menu').removeClass('focused');
    });
});