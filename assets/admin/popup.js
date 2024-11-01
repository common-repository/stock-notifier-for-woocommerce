(function($){
    $(document).on('click','.stock_notifier_bottom_bg',function(){
        var $thisbutton = $(this);
        var data = {
            action: 'stock_notifier_popup_ajax',
            nonce_validation:stock_notifier_popup.nonce,
        };
        $.ajax({
            type: 'post',
            url: stock_notifier_popup.ajax_url,
            data: data,
            beforeSend: function (response) {
                $thisbutton.html("Activating....");
              
            },
            complete: function (response) {
                $thisbutton.html("Activating....");
            },
            success: function (response) {
               if ( response.success == true) {
                location.reload();
               }
            },
        });
    })
})(jQuery);