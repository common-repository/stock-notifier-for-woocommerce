(function ($) {
  var stock_notifier_ajax_url = stock_notifier_form.ajax_url;
  var stock_notifier_user_id = stock_notifier_form.user_id;
  var stock_notifier_security = stock_notifier_form.security;
  var stock_notifier_security_error = stock_notifier_form.security_error;
  var stock_notifier_url = stock_notifier_form.url;

  // var plugin_urls = stock_notifier.plugin_urls;
  jQuery(function () {
    // jQuery(".variations_form").on("woocommerce_variation_select_change", function () {
    //     // Fires whenever variation selects are changed
    //     onloadCallback();
    // });

    var stock_rand_vals = document.querySelectorAll(
      ".stock_notifier-rand-code"
    );
    stock_rand_vals.forEach((el) => {
      let in_stock_rand_val = jQuery(el).val();
      jQuery(".single_variation_wrap").on(
        "show_variation",
        function (event, variation) {
          // Fired when the user selects all the required dropdowns / attributes
          // and a final variation is selected / shown
          var vid = variation.variation_id;
          // jQuery(`.stock_notifier-subscribe-form_${in_stock_rand_val}`).hide(); //remove existing form
          jQuery(
            `.stock_notifier-subscribe-form_${in_stock_rand_val}-` + vid
          ).show(); //add subscribe form to show
        }
      );
    });

    jQuery(document).on("click", ".stock_notifier_tooltip", function (e) {
      e.preventDefault();
      let self = $(this);
      let stock_notifier_input = self.find("#stock_notifier_phone");
      let submit_button = self.find(".stock_notifier_submit");
      var stock_notifier_instance = stock_notifier_input.intlTelInput({
        utilsScript: stock_notifier_url + "assets/frontend/utils.js",
      });
      let phone_number = $(self.find("#stock_notifier_phone")).val();
      var stock_notifier_reset = function () {
        $(self.find(".inwpstock_whatsapp_sms")).removeClass("valid");
        $(self.find(".inwpstock_whatsapp_sms")).removeClass("invalid");
      };
      stock_notifier_input.blur(function () {
        stock_notifier_reset();
        if (phone_number.trim()) {
          if (stock_notifier_instance.intlTelInput("isValidNumber")) {
            $(self.find(".inwpstock_whatsapp_sms")).removeClass("invalid");
            $(self.find(".inwpstock_whatsapp_sms")).addClass("valid");
          } else {
            $(self.find(".inwpstock_whatsapp_sms")).removeClass("valid");
            $(self.find(".inwpstock_whatsapp_sms")).addClass("invalid");
          }
        }
      });
      stock_notifier_input.change(stock_notifier_reset);
      stock_notifier_input.keyup(stock_notifier_reset);
      submit_button.off().click(function (e) {
        e.preventDefault();
        if (stock_notifier_instance.intlTelInput("isValidNumber")) {
          var submit_button_obj = jQuery(this);
          var phone_id = jQuery(this)
            .closest(".stock_notifier-subscribe-form")
            .find("#stock_notifier_phone")
            .val();
          var product_id = jQuery(this)
            .closest(".stock_notifier-subscribe-form")
            .find("#stock_notifier-product-id")
            .val();
          var country_code = jQuery(this)
            .closest(".stock_notifier-subscribe-form")
            .find(".stock_notifier_country_code")
            .val();
          var phone_number = country_code + phone_id;
          var rep_phone = phone_number.replace("-", "");
          var var_id = jQuery(this)
            .closest(".stock_notifier-subscribe-form")
            .find(".stock_notifier-variation-id")
            .val();
          if (phone_id == "") {
            return false;
          } else if (country_code == "") {
            return false;
          } else {
            var data = {
              action: "stock_notifier_product_subscribe",
              product_id: product_id,
              variation_id: var_id,
              user_phone: phone_number,
              user_id: stock_notifier_user_id,
              security: stock_notifier_security,
              dataobj: "stock_notifier",
            };
            if (jQuery.fn.block) {
              submit_button_obj
                .closest(".stock_notifier-subscribe-form")
                .block({
                  message: null,
                });
            } else {
              var overlay = jQuery(
                '<div id="stock_notifier-bis-overlay"> </div>'
              );
              overlay.appendTo(
                submit_button_obj.closest(".stock_notifier-subscribe-form")
              );
            }
            //ajax
            jQuery.ajax({
              type: "post",
              url: stock_notifier_ajax_url,
              data: data,
              success: function (msg) {
                self.hide();
                self.siblings().show();
                self
                  .siblings()
                  .find("#stock_notifier_successfully_head")
                  .html(msg);
                // jQuery.unblockUI();
                if (jQuery.fn.block) {
                  submit_button_obj
                    .closest(".stock_notifier-subscribe-form")
                    .unblock();
                } else {
                  submit_button_obj
                    .closest(".stock_notifier-subscribe-form")
                    .find("#stock_notifier-bis-overlay")
                    .fadeOut(400, function () {
                      submit_button_obj
                        .closest(".stock_notifier-subscribe-form")
                        .find("#stock_notifier-bis-overlay")
                        .remove();
                    });
                }
              },
              error: function (request, status, error) {
                if (
                  request.responseText === "-1" ||
                  request.responseText === -1
                ) {
                  submit_button_obj
                    .closest(".stock_notifier-subscribe-form")
                    .find(".inwpstock_output")
                    .fadeIn(2000);
                  submit_button_obj
                    .closest(".stock_notifier-subscribe-form")
                    .find(".inwpstock_output")
                    .html(
                      "<div class='stock_notifiererror' style='color:red;'>" +
                        stock_notifier_security_error +
                        "</div>"
                    );
                }
                //jQuery.unblockUI();
                if (jQuery.fn.block) {
                  submit_button_obj
                    .closest(".stock_notifier-subscribe-form")
                    .unblock();
                } else {
                  submit_button_obj
                    .closest(".stock_notifier-subscribe-form")
                    .find("#stock_notifier-bis-overlay")
                    .fadeOut(400, function () {
                      submit_button_obj
                        .closest(".stock_notifier-subscribe-form")
                        .find("#stock_notifier-bis-overlay")
                        .remove();
                    });
                }
              },
            });
          }
        }
        return false;
      });
      $(self.find(".stock_notifier_tooltip_tooltiptext")).show();
    });
  });
})(jQuery);
