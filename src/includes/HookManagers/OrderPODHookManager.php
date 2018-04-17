<?php

namespace Detrack\DetrackWoocommerce\HookManagers;

use WC_Order;
use Detrack\DetrackCore\Model\Delivery;

class OrderPODHookManager extends AbstractHookManager
{
    public static function registerHooks()
    {
        $self = new self();
        //add meta boxes to admin page
        add_action('add_meta_boxes', array($self, 'add_meta_boxes'));
        add_action('admin_enqueue_scripts', array($self, 'add_meta_box_scripts'));
        add_action('wp_ajax_detrack_load_POD', array($self, 'show_detrack_pod_ajax_handler'));
        add_action('current_screen', array($self, 'download_pod_pdf'));
    }

    public function add_meta_boxes()
    {
        add_meta_box('show_detrack_pod', 'Proof Of Delivery', array($this, 'show_detrack_pod_box_html'), 'shop_order');
    }

    public function add_meta_box_scripts()
    {
        $screen = get_current_screen();
        // verify admin screen object
        if (is_object($screen)) {
            // enqueue only for specific post types
            if (in_array($screen->post_type, ['shop_order'])) {
                // enqueue script
                if (isset($_GET['action']) && $_GET['action'] == 'edit') {
                    wp_enqueue_script('detrack-woocommerce_edit', plugins_url('../../admin/js/edit.js', __FILE__), ['jquery']);
                    wp_enqueue_style('detrack-woocommerce_edit-css', plugins_url('../../admin/css/edit.css', __FILE__));
                }
            }
        }
    }

    public function download_pod_pdf()
    {
        $screen = get_current_screen();
        // verify admin screen object
        if (is_object($screen)) {
            if (in_array($screen->post_type, ['shop_order'])) {
                if (isset($_GET['download']) && $_GET['download'] == 'pod') {
                    $order = new WC_Order($_GET['post']);
                    try {
                        $delivery = $this->castOrderToDelivery($order->get_id());
                    } catch (\Exception $ex) {
                        echo 'something broke';
                        echo $ex->getMessage();
                        die;
                    }
                    $folder = sys_get_temp_dir().DIRECTORY_SEPARATOR.'detrack-woocommerce'.DIRECTORY_SEPARATOR.preg_replace("/[^\d\w]/", '', $delivery->do);
                    $file = $folder.DIRECTORY_SEPARATOR.'pod.pdf';
                    if (!file_exists($file) || (isset($_GET['forcefetch']) && $_GET['forcefetch'] == 'true')) {
                        $this->log($delivery->downloadPODPDF($file));
                        $this->log('redownloading pod');
                    }
                    $this->log('filename:'.$file);
                    $this->log('date'.$delivery->date);
                    $this->log('do'.$delivery->do);
                    $this->log('downloading pdf');
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="'.basename($file).'"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: '.filesize($file));
                    readfile($file);
                    exit;
                }
            }
        }
    }

    public function show_detrack_pod_ajax_handler()
    {
        if (!isset($_GET['id'])) {
            die;
        }
        $id = $_GET['id'];
        $order = new WC_Order($id);
        try {
            $delivery = $this->castOrderToDelivery($order->get_id());
        } catch (\Exception $ex) {
            $this->log('retrieving POD photos for order id '.$order->get_id().' : '.$ex->getMessage(), 'error');
            $this->log('order data: '.$order, 'error');
            echo 'something broke';
            echo $ex->getMessage();
            die;
        }
        $images = [];
        for ($i = 1; $i <= 5; ++$i) {
            try {
                array_push($images, base64_encode($delivery->getPODImage($i)));
            } catch (\RuntimeException $ex) {
                break;
            }
        }
        if ($images == []) {
            $this->log('delivery '.$delivery->do.' has no PODs!', 'debug');
        }
        echo json_encode(array_filter($images));
        die;
    }

    public function show_detrack_pod_box_html($post)
    {
        $order = new WC_Order($post->ID);
        include plugin_dir_path(__FILE__).'../../admin/partials/proofOfDeliveryMetaBox.php';
    }
}
