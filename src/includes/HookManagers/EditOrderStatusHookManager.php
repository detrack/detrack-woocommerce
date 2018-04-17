<?php

namespace Detrack\DetrackWoocommerce\HookManagers;

class EditOrderStatusHookManager extends AbstractHookManager
{
    public static function registerHooks()
    {
        $self = new self();
        //delete from detrack if order is trashed
        add_action('wp_trash_post', array($self, 'wp_trash_post'));
        //restoration
        add_action('untrash_post', array($self, 'untrash_post'));
    }

    /** Removes from detrack if order is trashed
     *
     * @param int $order_id the id of the order to be trashed, as passed by Wordpress
     */
    public function wp_trash_post($order_id)
    {
        $this->log(__FUNCTION__);
        if ($this->integration->get_option('api_key') == null) {
            $this->log('API Key not defined, aborting.');

            return;
        }
        if (get_post_type($order_id) == 'shop_order') {
            //attempt to deduce if this wp_trash_post was called via a bulk edit or not
            $backtrace = debug_backtrace();
            if (strpos($backtrace[count($backtrace) - 1]['file'], 'edit.php')) {
                $this->log('trash_post was called in a bulk delete context, ignoring');
            } else {
                try {
                    $delivery = $this->castOrderToDelivery($order_id);
                    $delivery->delete();
                } catch (\Exception $ex) {
                    $this->log('Failed to sync order delete, '.$ex->getMessage(), 'error');
                }
            }
        }
    }

    /** Reposts back to detrack if order is restored
     *
     * @param int $order_id the id of the restored order, as passed by Wordpress
     */
    public function untrash_post($order_id)
    {
        $this->log(__FUNCTION__);
        if ($this->integration->get_option('api_key') == null) {
            $this->log('API Key not defined, aborting.');

            return;
        }
        if (get_post_type($order_id) == 'shop_order') {
            try {
                $delivery = $this->castOrderToDelivery($order_id);
                $delivery->save();
            } catch (\Exception $ex) {
                $this->log('Failed to sync order restore, '.$ex->getMessage(), 'error');
                $this->log('Delivery data: '.var_export($delivery, true), 'error');
                $this->log('Order data: '.var_export(wc_get_order($order_id), true), 'error');
            }
        }
    }
}
