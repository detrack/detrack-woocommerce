(function($, window, document) {
  jQuery(document).ready(function() {
    console.log("document.ready called from detrack-woocommerce/settings.js!")
    Detrack_WC_Integration_toggleDefaultStatusDropdown();
    jQuery("#woocommerce_detrack-woocommerce_sync_on_checkout").on("change", Detrack_WC_Integration_toggleDefaultStatusDropdown);
    jQuery("#woocommerce_detrack-woocommerce_sync_on_update").on("change", Detrack_WC_Integration_toggleDefaultStatusDropdown);
    var updateCheck = new XMLHttpRequest();
    updateCheck.open("GET", "https://chester.detrack.info/v/woocommerce-version.php?v=" + $("#versionNumber").html());
    updateCheck.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        if (this.responseText != "ok") {
          $("#newUpdate").show();
          $("#newVersionNumber").html(this.responseText);
        }
      }
    }
    updateCheck.send();

    $("#detrack-attribute-mapping-tabs").tabs();
    $("#detrack-attribute-mapping-expert-accordion").accordion({
      icons: false,
      heightStyle: "content",
    });

    $(".detrack-attribute-mapping-expert-code").on("change", writeMasterValue);
    $("#detrack-attribute-mapping-easy select").on("change", showPresetCode);
    $(".detrack-attribute-mapping-expert-code-preset-code-warning a").on("click", unlockCode);
    $(".detrack-attribute-mapping-expert-code-custom-code-info a").on("click", lockCode);
    $(".detrack-attribute-mapping-expert-test").on("click", sendTest);
    $("#detrack-attribute-add-new").on("click", addNewAttribute);
    $("#detrack-attribute-reset").on("click", resetToDefault);
    $(".detrack-delete-attribute-icon").on("click", deleteAttribute);
  });

  function Detrack_WC_Integration_toggleDefaultStatusDropdown() {
    if (jQuery("#woocommerce_detrack-woocommerce_sync_on_checkout").is(":checked")) {
      jQuery("#woocommerce_detrack-woocommerce_new_order_status").removeAttr("disabled");
    } else {
      jQuery("#woocommerce_detrack-woocommerce_new_order_status").attr("disabled", "disabled");
    }
    if (jQuery("#woocommerce_detrack-woocommerce_sync_on_update").is(":checked")) {
      jQuery("#woocommerce_detrack-woocommerce_sync_on_processing").removeAttr("disabled");
    } else {
      jQuery("#woocommerce_detrack-woocommerce_sync_on_processing").attr("disabled", "disabled");
    }
  }

  function showPresetCode() {
    $(".detrack-attribute-mapping-expert-code[data-field='" + $(this).attr("data-field") + "']").val($(this).val());
    $(".detrack-attribute-mapping-expert-code[data-field='" + $(this).attr("data-field") + "']").attr("disabled", "disabled");
    writeMasterValue();
  }

  function unlockCode() {
    $(this).parents(".detrack-attribute-mapping-expert-accordion-col-left").find(".detrack-attribute-mapping-expert-code").removeAttr('disabled');
    $("#detrack-attribute-mapping-easy select[data-field='" + $(this).parents(".detrack-attribute-mapping-expert-accordion-col-left").find(".detrack-attribute-mapping-expert-code").attr("data-field") + "']").attr("disabled", "disabled");
    $("#detrack-attribute-mapping-easy select[data-field='" + $(this).parents(".detrack-attribute-mapping-expert-accordion-col-left").find(".detrack-attribute-mapping-expert-code").attr("data-field") + "']").after("<span><br>Custom code written - see expert mode</span>");
    $(this).parents(".detrack-attribute-mapping-expert-accordion-col-left").find(".detrack-attribute-mapping-expert-code-preset-code-warning").hide();
    $(this).parents(".detrack-attribute-mapping-expert-accordion-col-left").find(".detrack-attribute-mapping-expert-code-custom-code-info").show();

  }

  function lockCode() {
    $(this).parents(".detrack-attribute-mapping-expert-accordion-col-left").find(".detrack-attribute-mapping-expert-code").attr('disabled', "disabled");
    $("#detrack-attribute-mapping-easy select[data-field='" + $(this).parents(".detrack-attribute-mapping-expert-accordion-col-left").find(".detrack-attribute-mapping-expert-code").attr("data-field") + "']").removeAttr("disabled");
    $("#detrack-attribute-mapping-easy select[data-field='" + $(this).parents(".detrack-attribute-mapping-expert-accordion-col-left").find(".detrack-attribute-mapping-expert-code").attr("data-field") + "']").next("span").remove();
    $(this).parents(".detrack-attribute-mapping-expert-accordion-col-left").find(".detrack-attribute-mapping-expert-code-preset-code-warning").show();
    $(this).parents(".detrack-attribute-mapping-expert-accordion-col-left").find(".detrack-attribute-mapping-expert-code-custom-code-info").hide();
    $(this).parents(".detrack-attribute-mapping-expert-accordion-col-left").find(".detrack-attribute-mapping-expert-code").val($("#detrack-attribute-mapping-easy select[data-field='" + $(this).parents(".detrack-attribute-mapping-expert-accordion-col-left").find(".detrack-attribute-mapping-expert-code").attr("data-field") + "']").val().toString());
    writeMasterValue();
  }

  function writeMasterValue() {
    var masterValues = {};
    $(".detrack-attribute-mapping-expert-code").each(function(index, element) {
      masterValues[$(element).attr("data-field")] = $(element).val();
    });
    console.log(masterValues);
    $("#detrack-attribute-mapping-master-value").val(JSON.stringify(masterValues));
  }

  function sendTest() {
    var testForumla = $(this).parents(".detrack-attribute-mapping-expert-accordion-col-right").siblings(".detrack-attribute-mapping-expert-accordion-col-left").find(".detrack-attribute-mapping-expert-code").val();
    var testAttr = $(this).parents(".detrack-attribute-mapping-expert-accordion-col-right").siblings(".detrack-attribute-mapping-expert-accordion-col-left").find(".detrack-attribute-mapping-expert-code").attr("data-field");
    var testOrderNumber = $(this).parents(".detrack-attribute-mapping-expert-instructions").find("input[type='text']").val();
    var targetOutputBox = $(this).parents(".detrack-attribute-mapping-expert-instructions").find(".detrack-attribute-mapping-expert-console-output");
    var loadingGif = $(this).siblings("img");
    var xhr = new XMLHttpRequest();
    xhr.open("POST", ajaxurl);
    var fd = new FormData();
    fd.append("action", "detrack_test_formula");
    fd.append("formula", testForumla);
    fd.append("attribute", testAttr);
    fd.append("orderNumber", testOrderNumber);
    xhr.onreadystatechange = function() {
      if (xhr.readyState == 4) {
        if (xhr.status == 200) {
          targetOutputBox.html(xhr.responseText == "" ? "(nothing. check your code.)" : xhr.responseText);
          var originalColor = targetOutputBox.css("background-color");
          targetOutputBox.css("background-color", "LightGreen");
          targetOutputBox.animate({
            backgroundColor: originalColor
          }, 700);
        } else {
          targetOutputBox.html(xhr.responseText);
          var originalColor = targetOutputBox.css("background-color");
          targetOutputBox.css("background-color", "IndianRed");
          targetOutputBox.animate({
            backgroundColor: originalColor
          }, 1000);
        }
        loadingGif.hide();
      }
    }
    loadingGif.show();
    xhr.send(fd);
    console.log(testForumla, testAttr, testOrderNumber);
  }

  function deleteAttribute(event) {
    event.preventDefault();
    var reallyDelete = confirm("Are you sure you want to delete this?");
    if (reallyDelete) {
      var currentField = $(this).attr("data-field");
      //delete expert mode rows
      $("#detrack-attribute-mapping-expert .detrack-delete-attribute-icon[data-field='" + currentField + "']").parent("h3").first().next("div").first().remove();
      $("#detrack-attribute-mapping-expert .detrack-delete-attribute-icon[data-field='" + currentField + "']").parent("h3").first().remove();
      //delete easy mode row
      $("#detrack-attribute-mapping-easy").find("select[data-field='" + currentField + "']").parents("#detrack-attribute-mapping-easy tr").remove();
      $("#detrack-attribute-mapping-expert-accordion").accordion("refresh");
      writeMasterValue();
    }
  }

  function addNewAttribute(event) {
    event.preventDefault();
    var newAttribute = $("#detrack-attribute-add-select").val();
    if ($("select[data-field='" + newAttribute + "']").length != 0 || $("textarea[data-field='" + newAttribute + "']").length != 0) {
      var existingEasyRow = $("select[data-field='" + newAttribute + "']").parents("tr").first();
      var easyRowOriginalColor = existingEasyRow.children().css("background-color");
      existingEasyRow.children().css("background-color", "IndianRed");
      existingEasyRow.animate({
        backgroundColor: easyRowOriginalColor
      }, 1000);
      var existingExpertRow = $("textarea[data-field='" + newAttribute + "']").parents("div.ui-accordion-content").first();
      existingExpertRow.prev("h3.ui-accordion-header").trigger("click");
      var expertRowOriginalColor = existingExpertRow.css("background-color");
      //override jquery UI defaults
      existingExpertRow.css('background-position-x', "0");
      existingExpertRow.css('background-position-y', "0");
      existingExpertRow.css("background-image", "none");
      existingExpertRow.css("background-color", "IndianRed");
      existingExpertRow.animate({
        backgroundColor: expertRowOriginalColor
      }, 1000);
      if ($("#detrack-attribute-mapping-tabs ul").children("li.ui-state-active").text().indexOf("Expert") != -1) {
        existingExpertRow[0].scrollIntoView();
      } else {
        existingEasyRow[0].scrollIntoView();
      }
      return;
    }
    //add new row in easy panel
    var newEasyRow = $("#detrack-attribute-mapping-easy tr").last().clone(true, true);
    newEasyRow.find("select").attr("data-field", newAttribute);
    newEasyRow.find("td").first().text(newAttribute);
    $("#detrack-attribute-mapping-easy tr").last().before(newEasyRow);
    newEasyRow.show();
    var newEasyRowOriginalColor = newEasyRow.children().css("background-color");
    console.log(newEasyRowOriginalColor);
    console.log(newEasyRow);
    newEasyRow.children().css("background-color", "LightGreen");
    newEasyRow.children().animate({
      backgroundColor: newEasyRowOriginalColor
    }, 500);
    var newHeader = $("#detrack-attribute-mapping-expert-accordion").children("h3").last().clone(true, true);
    var newRow = $("#detrack-attribute-mapping-expert-accordion").children("div").last().clone(true, true);
    newHeader.contents().last().replaceWith(newAttribute);
    newRow.find(".detrack-attribute-mapping-expert-code").attr("data-field", newAttribute);
    newRow.find(".detrack-attribute-mapping-expert-instructions").first().find("ul").html("<i>Nothing</i>");
    newRow.find(".detrack-attribute-mapping-expert-console-output").html("");
    $("#detrack-attribute-mapping-expert-accordion").append(newHeader);
    $("#detrack-attribute-mapping-expert-accordion").append(newRow);
    $("#detrack-attribute-mapping-expert-accordion").accordion("refresh");
    newHeader.trigger("click");
    newRowOriginalColor = newRow.css("background-color");
    newRow.css('background-position-x', "0");
    newRow.css('background-position-y', "0");
    newRow.css("background-image", "none");
    newRow.css("background-color", "LightGreen");
    newRow.animate({
      backgroundColor: newRowOriginalColor
    }, 500);
    newEasyRow.find("select").trigger("change");
    if ($("#detrack-attribute-mapping-tabs ul").children("li.ui-state-active").text().indexOf("Expert") != -1) {
      newRow.find(".detrack-attribute-mapping-expert-code-preset-code-warning a").trigger("click");
    } else {
      newRow.find(".detrack-attribute-mapping-expert-code-custom-code-info a").trigger("click");
    }
  }

  function resetToDefault(event) {
    event.preventDefault();
    event.stopPropagation();
    var reallyReset = confirm("Are you sure you want to reset attribute mapping values?");
    if (reallyReset) {
      $("#detrack-attribute-mapping-master-value").val("default");
      $(".button-primary.woocommerce-save-button").trigger("click");
    }
  }
}(jQuery, window, document));