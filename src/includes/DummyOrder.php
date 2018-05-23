<?php

namespace Detrack\DetrackWoocommerce;

use WC_Order;

/**
 * This class encapsulates the WC_Order class, and provides read only methods of accessing the order data to be used in the ExpressionLanguage component.
 */
class DummyOrder
{
    use \Detrack\DetrackWoocommerce\Traits\LoggerTrait;
    protected $data;

    public function __construct(WC_Order $order)
    {
        $this->data = $order->get_data();
    }

    /**
     * Allows accessing properties via dot syntax in the ExpressionLanguage syntax.
     *
     * E.g. order.date, order.shipping.city
     *
     * @param $key key of data you wish to retrieve
     *
     * @return $data the data retrieved
     */
    public function __get($key)
    {
        if (isset($this->data[$key])) {
            if (is_array($this->data[$key])) {
                return json_decode(json_encode($this->data[$key]));
            } else {
                return $this->data[$key];
            }
        } else {
            return null;
        }
    }
}