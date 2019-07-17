<?php

namespace Detrack\DetrackWoocommerce\Traits;

use WC_Order;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Carbon\Carbon;
use Detrack\DetrackWoocommerce\DummyOrder;
use Detrack\DetrackWoocommerce\MappingTablePresets;

trait OrderCasterTrait
{
    /** Converts a WC_Order object into a Delivery object
     *
     * Requires API Key to be set.
     *
     * @param WC_Order|int $attr pass either the object itself or the id
     *
     * @return Delivery|Collection the newly casted delivery/collection
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
        /* replaced by attribute mapping
        $delivery->date = $order->get_date_created()->date('Y-m-d');
        $states = WC()->countries->get_states($order->get_shipping_country());
        if (!empty($states)) {
            $state = $states[$order->get_shipping_state()];
        } else {
            $state = $order->get_shipping_state();
        }
        $delivery->address = implode(', ', array_filter(
              [$order->get_shipping_address_1(),
                $order->get_shipping_address_2(),
                $order->get_shipping_city(),
                $state,
                $order->get_shipping_postcode(),
                WC()->countries->countries[$order->get_shipping_country()], ]));
        $delivery->deliver_to = implode(' ', array_filter(
              [$order->get_shipping_first_name(),
                $order->get_shipping_last_name(), ]));
        $delivery->phone = $order->get_billing_phone();
        $delivery->pay_mode = $order->get_payment_method_title();
        $delivery->pay_amt = $order->get_total();
        $delivery->instructions = $order->get_customer_note();
        */
        $loadedSettings = json_decode($this->integration->get_option('data_format'), true);
        //I don't trust WC's default setting retrieval
        if ($loadedSettings === [] || $loadedSettings === '' || $loadedSettings === null) {
            $this->log('loaded settings not found, using defaults!');
            $loadedSettings = \Detrack\DetrackWoocommerce\MappingTablePresets::getDefaultPresets();
        }
        //global variables for attribute MappingTablePresets
        $orderDate = $order->get_date_created();
        if (is_null($orderDate)) {
            $this->log('order date was empty!', 'warning');
            $orderDate = date('Y-m-d');
        } else {
            $orderDate = $orderDate->date('Y-m-d');
        }
        $extraVars = [
            'order' => new DummyOrder($order),
            'checkoutDate' => Carbon::parse($orderDate),
        ];

        //choose to create either a delivery or a collection
        if (!isset($loadedSettings['type']) || $this->mapAttribute('type', $extraVars) == 'delivery') {
            $delivery = new \Detrack\DetrackCore\Model\Delivery([], $client);
        } else {
            $delivery = new \Detrack\DetrackCore\Model\Collection([], $client);
        }
        $delivery->do = $order->get_order_number();
        $delivery->notify_email = $order->get_billing_email();
        foreach ($loadedSettings as $mappingKey => $mappingFormula) {
            if ($mappingKey == 'deliver_to') {
                $extraVars = array_merge($extraVars, [
                    'firstName' => $order->get_shipping_first_name(),
                    'lastName' => $order->get_shipping_last_name(),
                ]);
            } elseif ($mappingKey == 'address') {
                $extraVars = array_merge($extraVars, [
                    'addressLine1' => $order->get_shipping_address_1(),
                    'addressLine2' => $order->get_shipping_address_2(),
                    'city' => $order->get_shipping_city(),
                    'state' => $order->get_shipping_state(),
                    'stateFull' => isset(WC()->countries->get_states($order->get_shipping_country())[$order->get_shipping_state()]) ? WC()->countries->get_states($order->get_shipping_country())[$order->get_shipping_state()] : $order->get_shipping_state(),
                    'postalCode' => $order->get_shipping_postcode(),
                    'country' => $order->get_shipping_country(),
                    'countryFull' => isset(WC()->countries->countries[$order->get_shipping_country()]) ? WC()->countries->countries[$order->get_shipping_country()] : $order->get_shipping_country(),
                ]);
            }
            try {
                //early return: see if the "ignore" setting evaluates to true
                if ($this->mapAttribute('ignore', $extraVars) === true || $this->mapAttribute('ignore', $extraVars) === 'true') {
                    $this->log('Ignore expression followed for order id '.$order->get_order_number(), 'info');
                    //dont post this order, immediately exit.
                    return null;
                }
                //special attribute names for collection
                if ($delivery instanceof \Detrack\DetrackCore\Model\Collection) {
                    if ($mappingKey == 'deliver_to') {
                        //deliver_to => collect_from
                        $delivery->collect_from = $this->mapAttribute($mappingKey, $extraVars);
                    } elseif ($mappingKey == 'delivery_time') {
                        //delivery_time => collection_time
                        $delivery->collection_time = $this->mapAttribute($mappingKey, $extraVars);
                    } else {
                        //other attributes are the same
                        $delivery->$mappingKey = $this->mapAttribute($mappingKey, $extraVars);
                    }
                } else {
                    $delivery->$mappingKey = $this->mapAttribute($mappingKey, $extraVars);
                }
            } catch (\Exception $ex) {
                $this->log('ExpressionLanguage syntax failed for key '.$mappingKey.$ex->getMessage(), 'error');
                //resort to old-school methods for required fields
                if ($mappingKey == 'date') {
                    $orderDate = $order->get_date_created();
                    if (is_null($orderDate)) {
                        $this->log('order date was empty!', 'warning');
                        $orderDate = date('Y-m-d');
                    } else {
                        $orderDate = $orderDate->date('Y-m-d');
                    }
                    $delivery->date = $orderDate;
                } elseif ($mappingKey == 'address') {
                    $states = WC()->countries->get_states($order->get_shipping_country());
                    if (!empty($states)) {
                        $state = $states[$order->get_shipping_state()];
                    } else {
                        $state = $order->get_shipping_state();
                    }
                    $delivery->address = implode(', ', array_filter(
                        [$order->get_shipping_address_1(),
                            $order->get_shipping_address_2(),
                            $order->get_shipping_city(),
                            $state,
                            $order->get_shipping_postcode(),
                            WC()->countries->countries[$order->get_shipping_country()], ]
                    ));
                }
            }
        }
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
        $wcItems = array_filter($order->get_items(), function ($item) {
            return $item instanceof \WC_Order_Item_Product;
        });
        $detrackItems = [];
        foreach ($wcItems as $wcItem) {
            $item = new \Detrack\DetrackCore\Model\Item();
            $wcProduct = $wcItem->get_product();
            if ($wcProduct == null) {
                continue;
            }
            $item->sku = $wcProduct->get_sku();
            if ($item->sku == null) {
                $item->sku = strtoupper(str_replace(' ', '-', $wcProduct->get_name()));
            }
            $item->desc = $wcProduct->get_name();
            $item->qty = $wcItem->get_quantity();
            $delivery->items->push($item);
        }

        $delivery->notify_url = get_site_url(null, '/wp-json/detrack-woocommerce/completeOrder/'.password_hash($this->integration->get_option('api_key'), PASSWORD_BCRYPT));

        return $delivery;
    }

    /**
     * Given an detrack attribute name and value, return the modified value after passing through the ExpressionLanguage syntax with custom variables.
     *
     * @param string $key       the attribute name
     * @param string $variables variables to pass in
     *
     * @return string $value the value of the modified attribute
     */
    protected function mapAttribute($key, $variables = [])
    {
        $mappingTable = json_decode($this->integration->get_option('data_format'));
        if (!isset($mappingTable->$key) || $mappingTable->$key == '') {
            if (isset(MappingTablePresets::getDefaultPresets()[$key])) {
                $formula = MappingTablePresets::getDefaultPresets()[$key];
            } else {
                return null;
            }
        } else {
            $formula = $mappingTable->$key;
        }
        $expressionLanguage = new ExpressionLanguage();
        try {
            $expressionLanguage->registerProvider(new \Detrack\DetrackWoocommerce\DetrackExpressionLanguageProvider());
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), 'error');
        }
        $result = $expressionLanguage->evaluate(
            $formula,
            $variables
        );
        if ($result instanceof Carbon) {
            $result = $result->format('Y-m-d');
        } elseif (is_scalar($result)) {
            $result = $result;
        } else {
            $result = print_r($result, true);
        }

        return $result;
    }
}
