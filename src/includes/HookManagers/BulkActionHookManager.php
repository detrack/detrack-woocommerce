<?php

namespace Detrack\DetrackWoocommerce\HookManagers;

use WC_Order;

class BulkActionHookManager extends AbstractHookManager
{
    public static function registerHooks()
    {
        $self = new self();
        add_action('current_screen', array($self, 'handle_bulk_trash'));
    }

    public function handle_bulk_trash()
    {
        $screen = get_current_screen();
        // verify admin screen object
        if (is_object($screen)) {
            if (in_array($screen->post_type, ['shop_order'])) {
                if (isset($_GET['trashed']) && isset($_GET['ids'])) {
                    $this->log('Gonna delete: '.$_GET['ids']);
                    $ids = explode(',', $_GET['ids']);
                    $jobs = array_filter(array_map(function ($id) {
                        $order = new WC_Order($id);

                        return $this->castOrderToDelivery($order);
                    }, $ids));
                    $deliveries = array_filter($jobs, function ($job) {
                        return $job instanceof \Detrack\DetrackCore\Model\Delivery;
                    });
                    $collections = array_filter($jobs, function ($job) {
                        return $job instanceof \Detrack\DetrackCore\Model\Collection;
                    });
                    if ($this->integration->get_option('api_key') == null) {
                        $this->log('API Key not defined, aborting', 'error');

                        return;
                    }
                    try {
                        $client = new \Detrack\DetrackCore\Client\DetrackClient($this->integration->get_option('api_key'));
                        $this->log(json_encode($client->bulkDeleteDeliveries($deliveries)));
                        $this->log(json_encode($client->bulkDeleteCollections($collections)));
                    } catch (\Exception $ex) {
                        $this->log('Error occurred when handling bulk trash, '.$ex->getMessage(), 'error');
                    }
                }
            }
        }
    }
}
