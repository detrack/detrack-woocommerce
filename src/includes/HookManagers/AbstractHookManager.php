<?php

namespace Detrack\DetrackWoocommerce\HookManagers;

abstract class AbstractHookManager
{
    use \Detrack\DetrackWoocommerce\Traits\LoggerTrait;
    use \Detrack\DetrackWoocommerce\Traits\InjectIntegrationTrait;
    use \Detrack\DetrackWoocommerce\Traits\OrderCasterTrait;

    public function __construct()
    {
        $this->injectIntegration();
    }
}
