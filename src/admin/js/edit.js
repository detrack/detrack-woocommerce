(function ($, window, document) {
  'use strict';
  // execute when the DOM is ready
  $(document).ready(function () {
    // js 'change' event triggered on the wporg_field form field
    $("#loadPODButton").removeAttr("disabled");
    $("#downloadPODPDFbutton").removeAttr("disabled");
    $('#loadPODButton').on('click', function (e) {
      e.preventDefault();
      $("#podLoadingGif").show();
      $("#podContainer").html("");
      $("#loadPODButton").attr("disabled", "disabled");
      $("#downloadPODPDFbutton").attr("disabled", "disabled");
      // jQuery post method, a shorthand for $.ajax with POST
      $.get(ajaxurl, // or ajaxurl
        {
          action: 'detrack_load_POD', // POST data, action
          id: $("#loadPODButton").attr("data-id"),
        },
        function (data) {
          // handle response data
          $("#podLoadingGif").hide();
          $("#loadPODButton").removeAttr("disabled");
          $("#downloadPODPDFbutton").removeAttr('disabled');
          var data = JSON.parse(data);
          for (var i = 0; i < data.length; i++) {
            var imageElement = document.createElement("img");
            imageElement.src = "data:image/jpeg;base64," + data[i];
            $(imageElement).width("150px").height("150px").css("padding", "15px");
            document.getElementById("podContainer").appendChild(imageElement);
            $(imageElement).on("click", function () {
              var modalBox = document.createElement("div");
              modalBox.classList.add("pod-modal");
              var modalBoxContent = document.createElement("div");
              modalBoxContent.classList.add("pod-modal-content");
              modalBox.appendChild(modalBoxContent);
              $("#wpbody-content").append($(modalBox));
              $(modalBoxContent).append($(this).clone().width("50%").height("50%"));
              $(modalBox).show();
              window.onclick = function (event) {
                if (event.target == modalBox) {
                  $(modalBox).remove();
                }
              }
            });
          }
        }
      );
    });
    $("#downloadPODPDFbutton").on("click", function (e) {
      e.preventDefault();
      window.setTimeout(function () {
        $("#podLoadingGif").hide();
        $("#downloadPODPDFbutton").removeAttr("disabled");
        $("#loadPODButton").removeAttr("disabled");
      }, 3000);
      document.getElementById("podDownloadFrame").src = "";
      var originalParams = new URLSearchParams(window.location.search);
      var strippedParams = new URLSearchParams();
      strippedParams.set("action", originalParams.get("action"));
      strippedParams.set("post", originalParams.get("post"));
      strippedParams.set('download', "pod");
      document.getElementById("podDownloadFrame").src = window.location.origin + window.location.pathname + "?" + strippedParams.toString();
      $("#downloadPODPDFbutton").attr("disabled", "disabled");
      $("#loadPODButton").attr("disabled", "disabled");
      $("#podLoadingGif").show();
    })
  });
}(jQuery, window, document));