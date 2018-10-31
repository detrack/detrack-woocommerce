<?php

namespace Detrack\DetrackWoocommerce;

use WC_Order;
use WC_Integration;
use Carbon\Carbon;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * DO NOT CONSTRUCT THIS CLASS MANUALLY!
 * This class must only be initialized once.
 * Or the hooks will be duplicated.
 * If you want to access the settings in the application, use the BareIntegration class.
 *
 * @see \Detrack\DetrackWoocommerce\BareIntegration the class to use if you want to access the options
 */
class Detrack_WC_Integration extends WC_Integration
{
    use \Detrack\DetrackWoocommerce\Traits\LoggerTrait;
    use \Detrack\DetrackWoocommerce\Traits\InjectIntegrationTrait;
    use \Detrack\DetrackWoocommerce\Traits\OrderCasterTrait;

    /**
     * Init and hook in the integration.
     */
    public function __construct()
    {
        global $woocommerce;
        global $wp_filter;
        $this->id = 'detrack-woocommerce';
        $this->method_title = __('Detrack', 'detrack-woocommerce');
        $this->method_description = __('Integrate your WooCommerce store with Detrack to automatically send delivery jobs to your drivers', 'detrack-woocommerce');
        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();
        // Actions.
        //Must prevent adding action multiple times
        if (!has_action('woocommerce_update_options_integration_'.$this->id)) {
            add_action('admin_enqueue_scripts', array($this, 'print_scripts'), 9001);
        }
        //add_action('woocommerce_update_options_integration_'.$this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_update_options_integration_'.$this->id, function () {
            $this->process_admin_options();
        });
        if (!has_action('wp_ajax_detrack_test_formula')) {
            add_action('wp_ajax_detrack_test_formula', array($this, 'test_formula'));
        }
    }

    /**
     * Initialize integration settings form fields.
     *
     * While not clearly specified, this is also where the WooCommerce Settings/Integrations API look for default values if it is not set
     *
     * @see https://docs.woocommerce.com/wc-apidocs/source-class-WC_Settings_API.html#249 The get_option functio
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
            /*
            * Temporarily comment this out for now
            '1' => array(
              'type' => 'title',
              'title' => __('Advanced Sync Options', 'detrack-woocommerce'),
            ),
            */
            'data_format' => array(
                'type' => 'data_format',
                'title' => 'Data Format',
                'default' => json_encode(\Detrack\DetrackWoocommerce\MappingTablePresets::getDefaultPresets()),
                'desc_tip' => 'Specify custom formulae for sending attributes to Detrack',
            ),
        );
    }

    /**
     * Used to generate the HTML for warnings, if any.
     *
     * This function checks for two possible issues:
     * 1. Checks if PHP's max_execution_time directive is set to less than 30 seconds.
     * 2. Checks if there is a new update available. Reads the detrack-woocommerce.php file to retrieve the version number.
     *
     * @param string $key   should be "warnings" as passed by WooCommerce. Ignore this.
     * @param string $value should be NULL. Ignore this.
     */
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
        if (version_compare(phpversion(), '5.6', '<')) {
            ?>
        <div class="notice notice-error">
          <strong> Fatal error: </strong> Your PHP version is <?php echo phpversion(); ?>. Detrack requires at least PHP 5.6 - please contact your hosting provider to update your version of PHP!
          <br>You should <em>not</em> be using versions of PHP older than 5.6 anyway; official support has been dropped for these old versions for a long time.
        </div>
        <?php
        }

        return ob_get_clean();
    }

    /**
     * Retrieves data mapping (data format) settings and generates the front-facing HTML.
     *
     * Options are stored as a single json string, in the following format:
     * attr1:formula1, attr2:formula2
     * The presentation logic is in a separate PHP file. If the settings are missing, pre-defined defaults are used.
     * The test order is by default the latest order made.
     *
     * @param string $key  should always be "data_format", as passed by the WC Settings API
     * @param array  $data should always be
     *
     * @see admin/partials/dataFormatAdminPanel.php the partial file where the HTML is printed
     */
    public function generate_data_format_html($key = null, $data = null)
    {
        ob_start();
        $field = $this->plugin_id.$this->id.'_'.$key;
        $loadedSettings = json_decode($this->get_option($key), true);
        //$this->log('loaded settings: '.var_export($loadedSettings, true));
        if ($loadedSettings == [] || $loadedSettings == '' || $loadedSettings == null) {
            $loadedSettings = \Detrack\DetrackWoocommerce\MappingTablePresets::getDefaultPresets();
        } else {
            //check if protected attributes are missing
            foreach (\Detrack\DetrackWoocommerce\MappingTablePresets::getData() as $attr => $attrValue) {
                //$this->log(var_export($attrValue, true));
                if (isset($attrValue['protected']) && $attrValue['protected'] == 'true') {
                    if (!isset($loadedSettings[$attr])) {
                        //set the default
                        foreach ($attrValue['presets'] as $protectedSettingPreset) {
                            if ($protectedSettingPreset['default'] == 'true') {
                                $loadedSettings[$attr] = $protectedSettingPreset['value'];
                            }
                        }
                        //if no presets were marked as default, set the first value as the default
                        $loadedSettings[$attr] = $attrValue['presets'][0]['value'];
                    }
                }
            }
        }
        $testOrders = wc_get_orders(['limit' => 1]);
        if (isset($testOrders) && count($testOrders) != 0) {
            $defaultTestOrder = $testOrders[0];
        } else {
            $defaultTestOrder = null;
        }
        include __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'partials'.DIRECTORY_SEPARATOR.'dataFormatAdminPanel.php';

        return ob_get_clean();
    }

    /**
     * Validates the data format field.
     *
     * Check if the value is a proper json string, and check if required parameters are present. Else, force the user to go back.
     *
     * @param $key should always be "data_format"
     * @param $value master value of the data format field
     *
     * @return $value master value of the data format field
     */
    public function validate_data_format_field($key, $value)
    {
        $value = stripslashes($value);
        //reset to default if needed
        if ($value == 'default') {
            \WC_Admin_Settings::add_message(esc_html__('Attribute mapping values reset to default!'));

            return json_encode(\Detrack\DetrackWoocommerce\MappingTablePresets::getDefaultPresets());
        }
        if (json_decode($value) == null) {
            \WC_Admin_Settings::add_error(esc_html__('Bad JSON for master values! Please reset to default to fix.', 'detrack-woocommerce'));
        } else {
            $loadedSettings = json_decode($value, true);
            if (!isset($loadedSettings['date']) || $loadedSettings['date'] == null) {
                \WC_Admin_Settings::add_error(esc_html__('Date field in attribute mapping is not defined.', 'detrack-woocommerce'));
            }
            if (!isset($loadedSettings['address']) || $loadedSettings['address'] == null) {
                \WC_Admin_Settings::add_error(esc_html__('Address field in attribute mapping is not defined.', 'detrack-woocommerce'));
            }
        }
        //you must return the value or it will not be saved!!
        return $value;
    }

    public function test_formula()
    {
        if (!isset($_POST['orderNumber']) || trim($_POST['orderNumber']) == '') {
            http_response_code(400);
            echo 'No order number given';
            die();
        } elseif (!preg_match("/^\d+$/", trim($_POST['orderNumber']))) {
            http_response_code(400);
            echo 'Invalid order number';
            die();
        }
        if (!isset($_POST['attribute']) || trim($_POST['attribute']) == '') {
            http_response_code(400);
            echo 'No test attribute given';
            die();
        }
        if (!isset($_POST['formula']) || trim($_POST['formula']) == '') {
            http_response_code(400);
            echo 'No formula given';
            die();
        }
        $testOrderNumber = trim($_POST['orderNumber']);
        try {
            $testOrder = new WC_Order($testOrderNumber);
        } catch (\Exception $ex) {
            http_response_code(500);
            echo 'No such order found';
            die();
        }
        $testAttr = trim($_POST['attribute']);
        $testForumla = trim($_POST['formula']);
        $extraVars = [];
        if ($testAttr == 'deliver_to') {
            $extraVars = array_merge($extraVars, [
                'firstName' => $testOrder->get_shipping_first_name(),
                'lastName' => $testOrder->get_shipping_last_name(),
            ]);
        } elseif ($testAttr == 'address') {
            $extraVars = array_merge($extraVars, [
                'addressLine1' => $testOrder->get_shipping_address_1(),
                'addressLine2' => $testOrder->get_shipping_address_2(),
                'city' => $testOrder->get_shipping_city(),
                'state' => $testOrder->get_shipping_state(),
                'stateFull' => WC()->countries->get_states($testOrder->get_shipping_country())[$testOrder->get_shipping_state()],
                'postalCode' => $testOrder->get_shipping_postcode(),
                'country' => $testOrder->get_shipping_country(),
                'countryFull' => WC()->countries->countries[$testOrder->get_shipping_country()],
            ]);
        }
        $el = new ExpressionLanguage();
        try {
            $el->registerProvider(new \Detrack\DetrackWoocommerce\DetrackExpressionLanguageProvider());
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), 'error');
        }
        try {
            $result = $el->evaluate(
            stripslashes($testForumla),
            array_merge($extraVars, [
                'checkoutDate' => new Carbon($testOrder->get_date_created()),
                'order' => new DummyOrder($testOrder),
            ])
          );
            if ($result instanceof Carbon) {
                echo $result->format('Y-m-d');
            } elseif (is_scalar($result)) {
                echo $result;
            } else {
                print_r($result);
            }
        } catch (\Exception $ex) {
            http_response_code(500);
            echo 'Exception: '.($ex->getMessage() == '' ? $ex->__toString() : $ex->getMessage());
            die();
            //calling wp_die() will always give HTTP 200, so use normal die
        }
        wp_die();
    }

    public function print_scripts($hook)
    {
        //only proceed if we're on the woocommerce menus
        if ($hook == 'woocommerce_page_wc-settings' && isset($_GET['tab']) && $_GET['tab'] == 'integration') {
            wp_enqueue_script(
                'detrack_settings',
                plugins_url('../admin/js/settings.js', __FILE__),
              array(
                  'jquery',
                  'jquery-ui-core',
                  'jquery-ui-widget',
                  'jquery-ui-accordion',
                  'jquery-ui-tabs',
                  'jquery-effects-core',
              )
            );
            wp_enqueue_style('detrack_settings', plugins_url('../admin/css/settings.css', __FILE__));
            wp_enqueue_script('fontawesome', 'https://use.fontawesome.com/releases/v5.0.10/js/all.js');
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
            \WC_Admin_Settings::add_error($ex->getMessage());
        }

        return $value;
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
