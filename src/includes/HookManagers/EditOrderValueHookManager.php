<?php

namespace Detrack\DetrackWoocommerce\HookManagers;

use WC_Order;
use Detrack\DetrackCore\Model\Item;

class EditOrderValueHookManager extends AbstractHookManager
{
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
        add_action('woocommerce_process_shop_order_meta', array($self, 'woocommerce_order_updated'), 9001);
        //to push order item updates made in the admin panel
        add_action('woocommerce_saved_order_items', array($self, 'woocommerce_order_items_updated'));
    }

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
            $this->castOrderToDelivery($order)->save();
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
                $this->castOrderToDelivery($order_id)->save();
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
                $this->castOrderToDelivery($order_id)->save(); ?>
               <div class="notice notice-success is-dismissible">
                  <p><?php esc_html_e('Items successfully updated to Detrack!', 'text-domain'); ?></p>
               </div>
              <?php
            } catch (\Exception $ex) {
                $this->log('Failed to sync order items update, '.$ex->getMessage(), 'error');
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
