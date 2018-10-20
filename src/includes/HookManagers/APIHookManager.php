<?php

namespace Detrack\DetrackWoocommerce\HookManagers;

use Detrack\DetrackCore\Model\Delivery;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class APIHookManager extends AbstractHookManager
{
    public static function registerHooks()
    {
        $self = new self();
        //add custom REST api endpoint to receive successful delivery notifications
        add_action('rest_api_init', array($self, 'register_api_routes'));
        //add custom authentication override to allow unathenticated users to access the endpoint
        add_action('rest_authentication_errors', array($self, 'override_authentication'), 9001);
    }

    /** Register Custom API routes we will use
     *
     * Endpoint: https://{host}/wp-json/detrack-woocommerce/{endpoint}.
     *
     * @see APIHookManager::receiveCompleteOrderNotification a registered endpoint
     */
    public function register_api_routes()
    {
        $this->log('registering routes');
        if ($this->integration->get_option('auto_complete_orders') == 'yes') {
            register_rest_route('detrack-woocommerce', '/completeOrder/(?P<secret>[$\w\d./]+)', array(
              'methods' => 'POST',
              'callback' => array($this, 'receiveCompleteOrderNotification'),
            ));
        }
    }

    /** Attempts to override other plugins that block access to our plugin
     *
     * As long as this filter runs after the other rest_authentication_errors filters, we can return no errors ("authenticated") if the current URL is our endpoint.
     *
     * @param $errors Any errors passed by other filters
     *
     * @return WP_Error|null NULL if the current request path matches our endpoint
     */
    public function override_authentication($error = null)
    {
        global $wp;
        if (strpos($wp->request, 'detrack-woocommerce/completeOrder') !== false) {
            return null;
        } else {
            return $error;
        }
    }

    /** Receives notification from Detrack and mark orders as complete
     *
     * @see APIHookManager::register_api_resoutes Where this route is registered
     *
     * @param WP_REST_Request $request The request object, as passed by WordPress
     *
     * @return WP_REST_Response A response containing an error message, if any
     */
    public function receiveCompleteOrderNotification(WP_REST_Request $request, $data = null)
    {
        $this->log('notification received');
        if (isset($request['secret'])) {
            if (!password_verify($this->integration->get_option('api_key'), $request['secret'])) {
                $this->log('Bad secret key passed: '.$request['secret'], 'error');

                return new WP_Error('bad_secret', 'Supplied secret key is wrong, you shall not pass!', array('status' => 403));
            }
        } else {
            return new WP_Error('no_secret', "You didn\'t give the secret key. You shall not pass!", array('status' => 403));
        }
        try {
            $postData = json_decode($request->get_body_params()['json']);
            if (trim($postData->status) == 'Delivered') {
                $order = wc_get_order($postData->do);
                if ($order == null) {
                    $this->log('order not found while processing delivery notification, :'.$postData->do, 'error');

                    return new WP_Error('order_not_found', 'Order not found, aborting', array('status' => 404));
                } elseif ($order->get_status() == 'trash') {
                    //restore, mark as complete, then trash again
                    wp_untrash_post($order->get_id());
                    $order->set_status('completed');
                    $order->save();
                    wp_trash_post($order->get_id());
                    $this->log('delivery completed for order in trash : '.$postData->do.' but moved back to trash', 'debug');

                    return new WP_REST_Response('delivery completed for order in trash : '.$postData->do.' but moved back to trash');
                } else {
                    //else, just normally mark as complete
                    $order->set_status('completed');
                    $order->save();
                    $this->log('delivery completed for order : '.$postData->do, 'debug');

                    return new WP_REST_Response('delivery completed for order :'.$postData->do, 'debug');
                }
            } else {
                $this->log('Status of posted data of DO'.$postData->do.' is not Delivered, aborting.');
                $this->log('body_params:'.var_export($postData, true), 'error');

                return new WP_Error('status_not_delivered', 'Status of Posted Data is not Delivered, aborting.', array('status' => 400));
            }
        } catch (\Exception $ex) {
            $this->log('processing delivery notification failed: '.$ex->getMessage(), 'error');

            return new WP_Error('internal_server_error', 'Error:'.$ex->getMessage(), array('status' => 500));
        }
    }
}
