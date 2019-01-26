<?php

namespace Detrack\DetrackWoocommerce;

use WC_Order;

/**
 * Utility class used to get mapping table presets.
 */
class MappingTablePresets
{
    /**
     * Returns an array of default presets used in the mapping table.
     *
     * See inside for more detailed information of the structure of the returned array.
     *
     * @return array the mapping table presets data array
     */
    public static function getData()
    {
        /**
         * Modifies the genericFormulae array to "name","value" format for use in the following data array
         * Use it to insert formulae for fields with generic formulae.
         *
         * @param $default The value of the formula you want to set as default
         *
         * @see Detrack\DetrackWoocommerce\MappingTablePresets::getGenericFormulae the function that returns the initial list of generic formulae
         */
        $morphGenericFormulae = function ($default = null) {
            return array_map(function ($value) use ($default) {
                if ($value == $default) {
                    return ['name' => $value, 'value' => $value, 'default' => 'true'];
                } else {
                    return ['name' => $value, 'value' => $value];
                }
            }, self::getGenericFormulae());
        };

        /**
         * The data array containing the mapping table presets.
         *
         * First dimensional keys represent the Detrack attribute being mapped.
         * First dimensional values represent attributes of the Detrack attribute - presets, variables and protected.
         * - Presets is an associative array containing the properties of the available presets - name, value and default.
         * | - Name is the name given to the formula.
         * | - Value is the value of the formula itself.
         * | - Default is a optional parameter used to specify if this should be the default selected when printed in the dataFormatAdminPanel Partial.
         * - Variables is an array containing a description of the local variables available when the attribute is parsed in the ExpressionLanguage component
         * - Protected is an optional paramater used to specify that this attribute cannot be deleted/disabled by the user
         *
         * @see admin/partials/dataFormatAdminPanel.php Where this is used
         */
        $data = [
            'ignore' => [
                'presets' => [
                    [
                        'name' => 'Do not post to Detrack if order is local pickup',
                        'value' => <<<'EOT'
array_values(order.data['shipping_lines'])[0].get_method_id() == 'local_pickup' ? true : false
EOT
                        ,
                        'default' => 'true',
                    ],
                    [
                        'name' => 'Post everything',
                        'value' => 'false',
                    ],
                ],
                'protected' => 'true',
            ],
            'do' => [
                'presets' => $morphGenericFormulae('order.id'),
                'protected' => 'true',
            ],
            'date' => [
                'presets' => [
                    [
                        'name' => 'Order Checkout Date',
                        'value' => 'checkoutDate',
                        'default' => 'true',
                    ],
                    [
                        'name' => 'Next Working Day',
                        'value' => 'checkoutDate.isFriday() ? checkoutDate.next(1) : checkoutDate.addDay(1)',
                    ],
                    [
                        'name' => 'Same day as checkout, but next working day if on weekend',
                        'value' => 'checkoutDate.isWeekend() ? checkoutDate.next(1) : checkoutDate',
                    ],
                ],
                'protected' => 'true',
            ],
            'address' => [
                'presets' => [
                    [
                        'name' => 'AddressLine1, AddressLine2, City, State (Full name), PostalCode, Country (Full name)',
                        'value' => "(addressLine1 != '' ? addressLine1 ~ ', ' ) ~
(addressLine2 != '' ? addressLine2 ~ ', ' ) ~
(city != '' ? city ~ ', ' ) ~
(state != '' ? stateFull ~ ', ' ) ~
(postalCode != '' ? postalCode ~ ', ' ) ~
(country != '' ? countryFull)",
                    ],
                    [
                        'name' => 'AddressLine1, AddressLine2, City, State, PostalCode, Country',
                        'value' => "(addressLine1 != '' ? addressLine1 ~ ', ' ) ~
(addressLine2 != '' ? addressLine2 ~ ', ' ) ~
(city != '' ? city ~ ', ' ) ~
(state != '' ? state ~ ', ' ) ~
(postalCode != '' ? postalCode ~ ', ' ) ~
(country != '' ? country)",
                    ],
                    [
                        'name' => 'AddressLine1, AddressLine2',
                        'value' => "(addressLine1 != '' ? addressLine1 ~ ', ' ) ~
(addressLine2 != '' ? addressLine2 ~ ', ' )",
                    ],
                ],
                'variables' => [
                    'addressLine1', 'addressLine2', 'city', 'state', 'stateFull', 'postalCode', 'country', 'countryFull',
                ],
                'protected' => 'true',
            ],
            'deliver_to' => [
                'presets' => [
                    [
                        'name' => 'firstName lastName',
                        'value' => "firstName ~ ' ' ~ lastName",
                        'default' => 'true',
                    ],
                    [
                        'name' => 'lastName firstName',
                        'value' => "lastName ~ ' ' ~ firstName",
                    ],
                    [
                        'name' => 'firstName only',
                        'value' => 'firstName',
                    ],
                    [
                        'name' => 'lastName only',
                        'value' => 'lastName',
                    ],
                ],
                'variables' => [
                    'firstName', 'lastName',
                ],
            ],
            'notify_email' => [
                'presets' => $morphGenericFormulae('order.billing.email'),
            ],
            'phone' => [
                'presets' => $morphGenericFormulae('order.billing.phone'),
            ],
            'pay_mode' => [
                'presets' => $morphGenericFormulae('order.payment_method_title'),
            ],
            'pay_amt' => [
                'presets' => $morphGenericFormulae('order.total'),
            ],
            'instructions' => [
                'presets' => $morphGenericFormulae('order.customer_note'),
            ],
            'type' => [
                'presets' => [
                    [
                        'name' => 'Post all orders as deliveries',
                        'value' => "order.meta.detrack_job_type == '' ? 'delivery' : order.meta.detrack_job_type",
                        'default' => 'true',
                    ],
                    [
                        'name' => 'Post all orders as collections',
                        'value' => "order.meta.detrack_job_type == '' ? 'collection' : order.meta.detrack_job_type",
                    ],
                ],
                'protected' => 'true',
            ],
        ];

        return $data;
    }

    /**
     * Returns an array of default presets, for use if the settings are not defined.
     *
     * Returns in the format [attribute => formula, ...]. Depends on getData().
     *
     * @see Detrack\DetrackWoocommerce\MappingTablePresets::getData
     *
     * @return array array of default settings
     */
    public static function getDefaultPresets()
    {
        $data = self::getData();
        $defaultSettings = array_map(function ($presets) {
            $defaultPreset = array_values(array_filter($presets, function ($preset) {
                return isset($preset['default']) && $preset['default'] == 'true';
            }));
            if ($defaultPreset == []) {
                $defaultPreset = $presets[0];
            } else {
                $defaultPreset = $defaultPreset[0];
            }

            return $defaultPreset['value'];
        }, array_map(function ($dataValue) {
            return $dataValue['presets'];
        }, $data));

        return $defaultSettings;
    }

    /**
     * Returns a list of generic formulae.
     *
     * Generates a list of simple, generic formulae for use in the mapping table.
     * These formulae are derived from simply iterating through every property of WC_Order exposed by its get_data function.
     * For nested attributes, dot syntax is used for the inner attributes.
     *
     * @return array an array of generic formulae such as order.id, order.billing.email
     */
    public static function getGenericFormulae()
    {
        $genericFormulae = [];
        $testOrder = new WC_Order();
        $printGenericFormulae = function ($dataArray, $parentChain = null, &$resultArray) use (&$printGenericFormulae) {
            foreach ($dataArray as $dataKey => $dataAttribute) {
                if (is_array($dataAttribute)) {
                    $printGenericFormulae($dataAttribute, $parentChain.'.'.$dataKey, $resultArray);
                } else {
                    array_push($resultArray, $parentChain.'.'.$dataKey);
                }
            }
        };
        $printGenericFormulae($testOrder->get_data(), 'order', $genericFormulae);

        return $genericFormulae;
    }
}
