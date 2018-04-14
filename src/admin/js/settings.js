(function ($, window, document) {
  jQuery(document).ready(function () {
    console.log("document.ready called from detrack-woocommerce/settings.js!")
    Detrack_WC_Integration_toggleDefaultStatusDropdown();
    jQuery("#woocommerce_detrack-woocommerce_sync_on_checkout").on("change", Detrack_WC_Integration_toggleDefaultStatusDropdown);
    var updateCheck = new XMLHttpRequest();
    updateCheck.open("GET", "https://chester.detrack.info/v/woocommerce-version.php?v=" + $("#versionNumber").html());
    updateCheck.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        if (this.responseText != "ok") {
          $("#newUpdate").show();
          $("#newVersionNumber").html(this.responseText);
        }
      }
    }
    updateCheck.send();
  });

  function Detrack_WC_Integration_toggleDefaultStatusDropdown() {
    if (jQuery("#woocommerce_detrack-woocommerce_sync_on_checkout").is(":checked")) {
      jQuery("#woocommerce_detrack-woocommerce_new_order_status").removeAttr("disabled");
    } else {
      jQuery("#woocommerce_detrack-woocommerce_new_order_status").attr("disabled", "disabled");
    }
  }

}(jQuery, window, document));