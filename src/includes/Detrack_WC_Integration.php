<?php

namespace Detrack\DetrackWoocommerce;

class Detrack_WC_Integration extends \WC_Integration
{
    /**
     * Init and hook in the integration.
     */
    public function __construct()
    {
        global $woocommerce;
        $this->id = 'detrack-woocommerce';
        $this->method_title = __('Detrack', 'detrack-woocommerce');
        $this->method_description = __('Integrate your WooCommerce store with Detrack to automatically send delivery jobs to your drivers <br> <strong>Alpha Test:</strong> Some irregularities may occur, and please check for updates often', 'detrack-woocommerce');
        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();
        /*
        // Define user set variables.
        $this->api_key = $this->get_option('api_key');
        $this->debug = $this->get_option('debug');
        */
        // Actions.
        add_action('woocommerce_update_options_integration_'.$this->id, array($this, 'process_admin_options'));
        add_action('admin_enqueue_scripts', array($this, 'print_scripts'), 9001);
    }

    /**
     * Initialize integration settings form fields.
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            '-1' => array(
              'type' => 'warnings',
            ),
            'api_key' => array(
                'title' => __('API Key', 'detrack-woocommerce'),
                'type' => 'text',
                'description' => __('Enter with your API Key. You can find this in "User Profile" drop-down (top right corner) > API Keys.', 'detrack-woocommerce'),
                'desc_tip' => true,
                'default' => '',
            ),
            'debug' => array(
                'title' => __('Debug Log', 'detrack-woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable logging', 'detrack-woocommerce'),
                'default' => 'no',
                'description' => __('Log events such as API requests', 'detrack-woocommerce'),
            ),
            '0' => array(
              'type' => 'title',
              'title' => __('Sync Options', 'detrack-woocommerce'),
            ),
            'sync_on_checkout' => array(
                'type' => 'checkbox',
                'title' => 'Push on checkout',
                'label' => 'Send new orders to detrack automatically after customer checkout',
                'default' => 'yes',
                'description' => __('If you leave this unchecked, you must push updates to detrack by using the "push to detrack" action in the edit order page.', 'detrack-woocommerce'),
                'desc_tip' => true,
            ),
            'new_order_status' => array(
              'type' => 'select',
              'custom_attributes' => array(
                'disabled' => 'true',
              ),
              'title' => 'Default status for new orders',
              'description' => "This refers to the delivery status on Detrack, not WooCommerce's order status.<br> Set to 'in progress' if you wish to automatically dispatch jobs, or 'on hold' if you have someone to manually dispatch jobs in the Detrack Dashboard.",
              'default' => 'in progress',
              'options' => array(
                'in progress' => 'in progress',
                'info received' => 'info received',
                'on hold' => 'on hold',
              ),
            ),
            'sync_on_processing' => array(
                'type' => 'checkbox',
                'label' => 'Automatically push to detrack when orders are marked as processing',
                'default' => 'yes',
                'description' => __('If you leave this unchecked, you must push updates to detrack by using the "push to detrack" action in the edit order page.', 'detrack-woocommerce'),
                'desc_tip' => true,
            ),
            'sync_on_update' => array(
                'type' => 'checkbox',
                'label' => 'Automatically push to detrack on updating orders',
                'default' => 'yes',
                'description' => __('If you leave this unchecked, you must push updates to detrack by using the "push to detrack" action in the edit order page.', 'detrack-woocommerce'),
                'desc_tip' => true,
            ),
            'sync_items_on_update' => array(
                'type' => 'checkbox',
                'label' => 'Automatically push to detrack on updating order items',
                'default' => 'yes',
                'description' => __('If you leave this unchecked, you must push updates to detrack by using the "push to detrack" action in the edit order page.', 'detrack-woocommerce'),
                'desc_tip' => true,
            ),
            'sync_order_status' => array(
                'type' => 'checkbox',
                'label' => __('Sync order status with detrack'),
                'default' => 'yes',
                'description' => __('Push status updates such as "processing" and "on hold" to detrack', 'detrack-woocommerce'),
            ),
            'auto_complete_orders' => array(
              'type' => 'checkbox',
              'label' => __('Mark Orders Complete'),
              'default' => 'yes',
              'description' => __('Automatically set order status to complete when Detrack knows the delivery has been fufilled'),
            ),
        );
    }

    public function generate_warnings_html($key = null, $value = null)
    {
        ob_start();
        if (ini_get('max-execution-time') < 30 && ini_get('max-execution-time') > 0) {
            ?>
        <div class="notice notice-warning">
          <p>PHP's max execution time is set to less than 30 seconds. Large bulk operations may timeout.</p>
        </div>

        <?php
        }
        $versionNumber = 9001;
        try {
            $sourceFile = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'detrack-woocommerce.php';
            $handle = fopen($sourceFile, 'r');
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (strpos($line, '* Version:')) {
                        $line = str_replace('* Version:', '', $line);
                        $line = trim($line);
                        $versionNumber = $line;
                        break;
                    }
                }

                fclose($handle);
            } else {
                // error opening the file.
            }
        } catch (Exception $ex) {
        } ?>
        <div class="notice notice-info" id="newUpdate" style="display:none">
          <span style="display:none" id="versionNumber"><?php echo $versionNumber; ?></span>
          <p>A new version of the Detrack Plugin (<strong><span id="newVersionNumber"></span></strong>) is available. Please update from our website.</p>
        </div>
        <?php

        return ob_get_clean();
    }

    public function print_scripts($hook)
    {
        //only proceed if we're on the woocommerce menus
        if ($hook == 'woocommerce_page_wc-settings' && isset($_GET['tab']) && $_GET['tab'] == 'integration') {
            wp_enqueue_script('detrack_settings', plugins_url('../admin/js/settings.js', __FILE__), array('jquery'));
        }
    }

    /**
     * Validates API Key.
     */
    public function validate_api_key_field($key, $value)
    {
        try {
            $client = new \Detrack\DetrackCore\Client\DetrackClient($value);

            return $value;
        } catch (\Detrack\DetrackCore\Client\Exception\InvalidAPIKeyException $ex) {
            WC_Admin_Settings::add_error($ex->getMessage());
        }
    }

    /**
     * Get settings array.
     *
     * @return array
     */
    public function get_settings()
    {
        return apply_filters('woocommerce_get_settings_'.$this->id, array());
    }
}
