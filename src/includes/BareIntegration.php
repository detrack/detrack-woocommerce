<?php

namespace Detrack\DetrackWoocommerce;

/**
 * Use this class to accesss options, instead of Detrack_WC_Integration.
 */
class BareIntegration extends \WC_Integration
{
    public function __construct()
    {
        $this->id = 'detrack-woocommerce';
        $this->method_title = __('Detrack', 'detrack-woocommerce');
        $this->method_description = __('Integrate your WooCommerce store with Detrack to automatically send delivery jobs to your drivers <br> <strong>Alpha Test:</strong> Some irregularities may occur, and please check for updates often', 'detrack-woocommerce');
        $this->init_form_fields();
        $this->init_settings();
    }
}
