<?php

namespace Detrack\DetrackWoocommerce\HookManagers;

class CheckoutHookManager extends AbstractHookManager
{
    public static function registerHooks()
    {
        $self = new self();
        add_action('woocommerce_checkout_order_processed', array($self, 'woocommerce_checkout_order_processed'), 9001);
    }

    /** Posts to Detrack once the customer has finished the checkout process.
     *
     * Requires the "sync_on_checkout" option to be set.
     *
     * @see https://docs.woocommerce.com/wc-apidocs/source-class-WC_Checkout.html#983 Source code of WooCommerce where this action is invoked
     *
     * @param int      $order_id    the id of the new order, as passed by WooCommerce
     * @param array    $posted_data the checkout form post data, as passed by WooCommerce
     * @param WC_Order $order       the new order object, as passed by WooCommerce
     */
    public function woocommerce_checkout_order_processed($order_id, $posted_data, $order)
    {
        $this->log(__FUNCTION__);
        if ($this->integration->get_option('api_key') == null) {
            $this->log('API Key not defined, aborting', 'error');

            return;
        }
        try {
            if ($this->integration->get_option('sync_on_checkout') == 'yes') {
                $delivery = $this->castOrderToDelivery($order_id);
                $order = wc_get_order($order_id);
                //set the status manually, because for some payment methods the status is wrong
                $delivery->status = $this->integration->get_option('new_order_status');
                $delivery->save();
            }
        } catch (\Exception $ex) {
            $this->log('Could not post info on checkout, '.$ex->getMessage(), 'error');
            $this->log('Delivery info: '.var_export($order->get_data(), true), 'error');
        }
    }
}
