<?php

namespace Detrack\DetrackWoocommerce\Traits;

trait InjectIntegrationTrait
{
    public function injectIntegration()
    {
        //$this->integration = new \Detrack\DetrackWoocommerce\Detrack_WC_Integration();
        $this->integration = new \Detrack\DetrackWoocommerce\BareIntegration();
    }
}
