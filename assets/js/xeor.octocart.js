+function ($) { "use strict";

    $(document).ready(function(){
        $('.octocart-orders tr').hover(function () {
            $(this).find('.js-btn-action').removeAttr('style');
        }, function () {
            $(this).find('.js-btn-action').hide();
        });
    })

}(window.jQuery);