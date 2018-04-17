<?php

namespace Detrack\DetrackWoocommerce\Traits;

use WC_Order;

trait OrderCasterTrait
{
    /** Converts a WC_Order object into a Delivery object
     *
     * Requires API Key to be set.
     *
     * @param WC_Order|int $attr pass either the object itself or the id
     *
     * @return Delivery the newly casted delivery
     */
    protected function castOrderToDelivery($attr)
    {
        $order = $attr;
        if (is_int($attr) || is_string($attr)) {
            $order = wc_get_order($attr);
        }
        if (is_null($order)) {
            return null;
        }
        $client = new \Detrack\DetrackCore\Client\DetrackClient($this->integration->get_option('api_key'));
        $delivery = new \Detrack\DetrackCore\Model\Delivery([], $client);
        $delivery->do = $order->get_order_number();
        $delivery->date = $order->get_date_created()->date('Y-m-d');
        $delivery->notify_email = $order->get_billing_email();
        $delivery->address = implode(', ', array_filter(
              [$order->get_shipping_address_1(),
                $order->get_shipping_address_2(),
                $order->get_shipping_city(),
                $order->get_shipping_state(),
                $order->get_shipping_postcode(),
                $order->get_shipping_country(), ]));
        $delivery->deliver_to = implode(' ', array_filter(
              [$order->get_shipping_first_name(),
                $order->get_shipping_last_name(), ]));
        $delivery->phone = $order->get_billing_phone();
        //set status
        if ($this->integration->get_option('sync_order_status') == 'yes') {
            $status = $order->get_status();
            if ($status == 'processing') {
                $delivery->status = 'in progress';
            } elseif ($status == 'on-hold' || $status == 'pending') {
                $delivery->status = 'on hold';
            } elseif ($status == 'cancelled' || $status == 'refunded') {
                $delivery->status = 'cancelled';
            } elseif ($status == 'complete') {
                $delivery->status = 'complete';
            }
        }
        $wcItems = $order->get_items();
        $detrackItems = [];
        foreach ($wcItems as $wcItem) {
            $item = new \Detrack\DetrackCore\Model\Item();
            $item->sku = $wcItem->get_product()->get_sku();
            if ($item->sku == null) {
                $item->sku = strtoupper(str_replace(' ', '-', $wcItem->get_product()->get_name()));
            }
            $item->desc = $wcItem->get_product()->get_name();
            $item->qty = $wcItem->get_quantity();
            $delivery->items->push($item);
        }

        $delivery->instructions = $order->get_customer_note();
        $delivery->notify_url = get_site_url(null, '/wp-json/detrack-woocommerce/completeOrder');

        return $delivery;
    }
}
