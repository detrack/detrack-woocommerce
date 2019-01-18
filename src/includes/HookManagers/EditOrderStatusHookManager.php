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
        //internal hook used by woocommerce for any type of change
        add_action('woocommerce_order_status_changed', array($self, 'woocommerce_order_status_changed'), 9001, 3);
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
                if ($delivery == null) {
                    return;
                }
                $delivery->save();
            } catch (\Exception $ex) {
                $this->log('Failed to sync order restore, '.$ex->getMessage(), 'error');
                $this->log('Delivery data: '.var_export($delivery, true), 'error');
                $this->log('Order data: '.var_export(wc_get_order($order_id), true), 'error');
            }
        }
    }

    /** Hotfix for order status sync bug - just sync all possible permutations
     *
     * @param mixed      $order_id
     * @param null|mixed $oldStatus
     * @param null|mixed $newStatus
     * @param null|mixed $order
     */
    public function woocommerce_order_status_changed($order_id, $oldStatus = null, $newStatus = null, $order = null)
    {
        $this->log(__FUNCTION__);
        if ($oldStatus == 'trash' || $newStatus == 'trash') {
            //let wp_trash_post and untrash_post handle instead
            $this->log('status change is trash/untrash, aborting');

            return;
        } elseif ($order == null) {
            $order = wc_get_order($order_id);
            if ($order == null) {
                $this->log('order is null, aborting');

                return;
            }
        }
        $delivery = $this->castOrderToDelivery($order_id);
        if ($delivery == null) {
            return;
        }
        $this->log($delivery->date);
        $delivery->save();
    }
}
