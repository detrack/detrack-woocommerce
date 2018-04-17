<?php
/* The partial file for the POD Meta Box when you edit an order in WooCommerce Admin
*
* Declare an instance of WC_Order as $order before including this file
*/

?>


<button class="button" id="loadPODButton" data-id="<?php echo $order->get_id(); ?>" disabled>Load POD Photos</button>
<button class="button" id="downloadPODPDFbutton" data-id="<?php echo $order->get_id(); ?>" disabled>Download PDF</button>
<img id="podLoadingGif" src="<?php echo plugin_dir_url(__FILE__).'../img/loading.gif'; ?>" width="25px" height="25px" style="display:none"/>
<br/>
<input type="checkbox" id="forceFetchCheckbox"/><label for="forceFetchCheckbox">Force refetch PODs from detrack</label>
<div id="podContainer">
</div>
<iframe id="podDownloadFrame" style="display:none"></iframe>
