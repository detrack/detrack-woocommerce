<?php
/**
 * Plugin Name.
 *
 * @author      Detrack
 * @copyright   2017 Detrack
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Detrack Woocommerce
 * Plugin URI:  https://www.detrack.com/tutorials/woocommerce-integration/
 * Description: Integration with the Detrack API
 * Version:     1.6.3
 * Author:      Detrack
 * Author URI:  https://detrack.com
 * Text Domain: detrack-woocommerce
 * WC requires at least: 3
 * WC tested up to: 3.6.4
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
 if (!class_exists('Detrack_WC')) :
class Detrack_WC
{
    private $integration;

    /**
     * Construct the plugin. Hello!
     */
    public function __construct()
    {
        add_action('plugins_loaded', array($this, 'init'));
    }

    /**
     * Initialize the plugin.
     */
    public function init()
    {
        // Checks if WooCommerce is installed.
        if (class_exists('WC_Integration')) {
            // Include our integration class.
            //include_once 'Detrack_WC_Integration.php';
            include_once 'vendor/autoload.php';
            //to register our integration
            add_filter('woocommerce_integrations', array($this, 'add_integration'));
            \Detrack\DetrackWoocommerce\HookManagers\APIHookManager::registerHooks();
            \Detrack\DetrackWoocommerce\HookManagers\BulkActionHookManager::registerHooks();
            \Detrack\DetrackWoocommerce\HookManagers\CheckoutHookManager::registerHooks();
            \Detrack\DetrackWoocommerce\HookManagers\EditOrderStatusHookManager::registerHooks();
            \Detrack\DetrackWoocommerce\HookManagers\EditOrderValueHookManager::registerHooks();
            \Detrack\DetrackWoocommerce\HookManagers\OrderPODHookManager::registerHooks();
            //to handle admin notifications from our extension
            add_action('admin_notices', array($this, 'notify_detrack_messages'));
            $this->upgrade();
        } else {
            // throw an admin error if you like
        }
    }

    /**
     * Add a new integration to WooCommerce.
     *
     * @param mixed $integrations
     */
    public function add_integration($integrations)
    {
        $integrations[] = 'Detrack\DetrackWoocommerce\Detrack_WC_Integration';

        return $integrations;
    }

    /**
     * Handler that posts all detrack related messages.
     */
    public function notify_detrack_messages()
    {
        if (!isset($_GET['detrack_msg'])) {
            return;
        } elseif ($_GET['detrack_msg'] == 'post_success') {
            ?>
           <div class="notice notice-success is-dismissible">
              <p><?php esc_html_e('Successfully posted to detrack!', 'text-domain'); ?></p>
           </div>
          <?php
        }
    }

    /*
     * Runs the upgrader
     *
    */
    public function upgrade()
    {
        $lastUpgraded = get_option('detrack_woocommerce_last_upgraded_version');
        if ($lastUpgraded == null) {
            $lastUpgraded = '1.1.2';
        }
        //patch 1.4.1
        if (version_compare($lastUpgraded, '1.4.1', '<')) {
            $integration = new \Detrack\DetrackWoocommerce\BareIntegration();
            $mappingTable = json_decode($integration->get_option('data_format'));
            $badValue = "(addressLine1 != '' ? addressLine1 ~ ', ' ) ~
(addressLine2 != '' ? addressLine2 ~ ', ' ) ~
(city != '' ? city ~ ', ' ) ~
(state != '' ? state ~ ', ' ) ~
(postalCode != '' ? postalCode ~ ', ' ) ~
(country != '' ? country),";
            $fixedValue = "(addressLine1 != '' ? addressLine1 ~ ', ' ) ~
(addressLine2 != '' ? addressLine2 ~ ', ' ) ~
(city != '' ? city ~ ', ' ) ~
(state != '' ? state ~ ', ' ) ~
(postalCode != '' ? postalCode ~ ', ' ) ~
(country != '' ? country)";
            if ($mappingTable->address == $badValue) {
                $mappingTable->address = $fixedValue;
                $integration->settings['data_format'] = json_encode($mappingTable);
                update_option($integration->get_option_key(), apply_filters('woocommerce_settings_api_sanitized_fields_'.$integration->id, $integration->settings));
            }
        }
        update_option('detrack_woocommerce_last_upgraded_version', '1.4.1');
    }
}
$Detrack_WC = new Detrack_WC(__FILE__);
endif;
