<?php

namespace Detrack\DetrackWoocommerce\HookManagers;

use WC_Order;
use Detrack\DetrackCore\Model\Item;

class EditOrderValueHookManager extends AbstractHookManager
{
    protected static $oldDelivery = null;

    public static function registerHooks()
    {
        $self = new self();
        //to add a custom action in the woocommerce edit order page
        add_action('woocommerce_order_actions', array($self, 'add_post_to_detrack_button'));
        //the handler for the custom action added
        add_action('woocommerce_order_action_post_to_detrack', array($self, 'post_to_detrack_button_clicked'));
        //to push orders updates made in the admin panel
        //must put low-priority, or else the hook will be called before the order is actually saved,
        //and you will end up pushing the old values instead.
        add_action('woocommerce_process_shop_order_meta', array($self, 'woocommerce_order_updated'), 9001, 2);
        //to push order item updates made in the admin panel
        add_action('woocommerce_saved_order_items', array($self, 'woocommerce_order_items_updated'));
        //add_action('woocommerce_process_shop_order_meta', array($self, 'nani'), 1, 2);
    }

    /*
    public function nani($order, $orderDataStore)
    {
        static::$oldDelivery = $this->castOrderToDelivery($order);
    }
    */

    /** Adds the "Post to detrack" action in the order admin panel
     *
     * @see https://docs.woocommerce.com/wc-apidocs/source-class-WC_Meta_Box_Order_Actions.html#35
     * @see Detrack_WC::post_to_detrack_button_clicked() the handler for this action
     *
     * @param array $actions the other actions available, as passed by WooCommerce
     */
    public function add_post_to_detrack_button($actions)
    {
        $actions['post_to_detrack'] = 'Post to detrack';

        return $actions;
    }

    /** Handler for the "Post to detrack" action
     *
     * @see Detrack_WC::add_post_to_detrack_button() where this action is added
     *
     * @param WC_Order $order the order being edited in the admin control panel, as passed by WooCommerce
     */
    public function post_to_detrack_button_clicked($order)
    {
        if ($this->integration->get_option('api_key') == null) {
            $this->log('API Key is not defined, aborting.');

            return;
        }
        try {
            $delivery = $this->castOrderToDelivery($order);
            if ($delivery == null) {
                return;
            }
            $delivery->save();
            //set meta data for custom do
            add_post_meta($order_id, 'detrack_do', $delivery->do, true);
            $this->notify_successful_post();
        } catch (\Exception $ex) {
            $this->log('Manual posting to Detrack via action button failed, '.$ex->getMessage(), 'error');
        }
    }

    /** Posts to Detrack when the admin edits the order in the admin panel
     *
     * Requires the "sync_on_update" option to be set.
     *
     * @see https://docs.woocommerce.com/wc-apidocs/source-class-WC_Admin_Meta_Boxes.html#217
     *
     * @param int      $order_id the id of the updated order, as passed by WooCommerce
     * @param WC_Order $order    the updated order object, as passed by WooCommerce
     */
    public function woocommerce_order_updated($order_id, $order = null)
    {
        if ($this->integration->get_option('api_key') == null) {
            $this->log('API Key not defined, aborting.');

            return;
        }
        if ($this->integration->get_option('sync_on_update') == 'yes') {
            try {
                $delivery = $this->castOrderToDelivery($order_id);
                if ($delivery == null) {
                    return;
                }
                $delivery->save();
                /*
                $this->log(var_export(static::$oldDelivery, true));
                $oldDelivery = static::$oldDelivery;
                $this->log(var_export($delivery, true));
                if ($oldDelivery->date != $delivery->date) {
                    //change of date detected!!!!
                    //attempt to merge attributes
                    $this->log('change in date detected!');
                    $client = new \Detrack\DetrackCore\Client\DetrackClient($this->integration->get_option('api_key'));
                    $serverDelivery = $client->findDelivery($oldDelivery->getIdentifier());
                    if ($serverDelivery != null) {
                        $this->log('server delivery is not null');
                        $serverDelivery->delete();
                    }
                }
                */
                //set meta data for custom do
                add_post_meta($order_id, 'detrack_do', $delivery->do, true);
                $this->notify_successful_post();
            } catch (\Exception $ex) {
                $this->log('Failed to sync order update, '.$ex->getMessage(), 'error');
            }
        }
    }

    /** Posts to Detrack when the admin edits the order's items in the admin panel
     * Requires the sync_items_on_update option to be set.
     *
     * @see https://docs.woocommerce.com/wc-apidocs/source-function-wc_save_order_items.html#310
     *
     * @param int      $order_id the id of the updated order, as passed by WooCommerce
     * @param WC_Order $items    the updated order object, as passed by WooCommerce
     */
    public function woocommerce_order_items_updated($order_id, $items = null)
    {
        if ($this->integration->get_option('api_key') == null) {
            $this->log('API Key not defined, aborting.');

            return;
        }
        if ($this->integration->get_option('sync_items_on_update') == 'yes') {
            try {
                $delivery = $this->castOrderToDelivery($order_id);
                if ($delivery == null) {
                    return;
                }
                $delivery->save();
                //set meta data for custom do
                add_post_meta($order_id, 'detrack_do', $delivery->do, true); ?>
               <div class="notice notice-success is-dismissible">
                  <p><?php esc_html_e('Items successfully updated to Detrack!', 'text-domain'); ?></p>
               </div>
              <?php
            } catch (\Exception $ex) {
                $this->log('Failed to sync order items update, '.$ex->getMessage(), 'error');
                $this->log('Delivery data: '.var_export($delivery, true), 'error');
                $this->log('Order data: '.var_export(wc_get_order($order_id), true), 'error');
            }
        }
    }

    /**
     * See: https://wordpress.stackexchange.com/a/152059.
     */
    public function notify_successful_post()
    {
        add_filter('redirect_post_location', array($this, 'notify_successful_post_add_query_var'), 99);
    }

    public function notify_successful_post_add_query_var($location)
    {
        remove_filter('redirect_post_location', array($this, 'notify_successful_post_add_query_var'), 99);

        return add_query_arg(array('detrack_msg' => 'post_success'), $location);
    }
}
