("use strict");

jQuery(function ($) {

  const stock_notifier_wpdm = WPPOOL.Popup('stock_notifier_for_woocommerce');
  jQuery(document).on("click", "#stock_notifier_exports_free", function (e) {
    e.preventDefault();
   // declare only once
   stock_notifier_wpdm.show();
  });

  stock_notifier_wpdm.on("hide", (data) => {
    stock_notifier_wpdm.hide();
})

});
