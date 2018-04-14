<?php

namespace Detrack\DetrackWoocommerce\Traits;

trait LoggerTrait
{
    /** Uses WC's logger to log messages
     *
     *
     * @param string $message The message to log
     * @param string $level   The level to record the message as. Default "debug".
     */
    protected function log($message, $level = 'debug')
    {
        $logLevels = implode(',', ['verbose', 'debug', 'error']);
        $setLogLevel = 'debug'; //default log level, in case settings can't be read
        try {
            $integration = new \Detrack\DetrackWoocommerce\Detrack_WC_Integration();
            if ($integration->get_option('log_level') != null) {
                $setLogLevel = $integration->get_option('log_level');
            }
        } catch (\Exception $ex) {
            //don't do anything to $setLogLevel
        }
        if (strpos($level, $logLevels) < strpos($setLogLevel, $logLevels)) {
            //don't do anything if the message's log level is lower than the lowest set log level to log
            return;
        } else {
            $logger = wc_get_logger();
            $context = array('source' => 'detrack-woocommerce');
            $logger->log($level, $message, $context);
        }
    }
}
