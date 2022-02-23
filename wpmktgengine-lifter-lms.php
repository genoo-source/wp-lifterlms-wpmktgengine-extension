<?php
/*
    Plugin Name: LifterLMS - WPMktgEngine | Genoo Extension
    Description: Genoo, LLC
    Author:  Genoo, LLC
    Author URI: http://www.genoo.com/
    Author Email: info@genoo.com
    Version: 1.3.14
    License: GPLv2
*/
/*
    Copyright 2016  WPMKTENGINE, LLC  (web : http://www.genoo.com/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * On activation
 */

register_activation_hook(__FILE__, function () {
    // Basic extension data
    $fileFolder = basename(dirname(__FILE__));
    $file = basename(__FILE__);
    $filePlugin = $fileFolder . DIRECTORY_SEPARATOR . $file;
    // Activate?
    $activate = false;
    $isGenoo = false;
    // Get api / repo
    if (
        class_exists('\WPME\ApiFactory') &&
        class_exists('\WPME\RepositorySettingsFactory')
    ) {
        $activate = true;
        $repo = new \WPME\RepositorySettingsFactory();
        $api = new \WPME\ApiFactory($repo);
        if (class_exists('\Genoo\Api')) {
            $isGenoo = true;
        }
    } elseif (
        class_exists('\Genoo\Api') &&
        class_exists('\Genoo\RepositorySettings')
    ) {
        $activate = true;
        $repo = new \Genoo\RepositorySettings();
        $api = new \Genoo\Api($repo);
        $isGenoo = true;
    } elseif (
        class_exists('\WPMKTENGINE\Api') &&
        class_exists('\WPMKTENGINE\RepositorySettings')
    ) {
        $activate = true;
        $repo = new \WPMKTENGINE\RepositorySettings();
        $api = new \WPMKTENGINE\Api($repo);
    }
    // 1. First protectoin, no WPME or Genoo plugin
    if ($activate == false) {
        genoo_wpme_deactivate_plugin(
            $filePlugin,
            'This extension requires WPMKTGENGINE or Genoo plugin to work with.'
        );
    } else {
        // Right on, let's run the tests etc.
        // 2. Second test, can we activate this extension?
        // Active
        $active = get_option('wpmktengine_extension_lms', null);
        if ($isGenoo === true) {
            $active = true;
        }
        if ($active === null) {
            // Oh oh, no value, lets add one
            try {
                // Might be older package
                if (method_exists($api, 'getPackageLMS')) {
                    $active = $api->getPackageLMS();
                } else {
                    $active = false;
                }
            } catch (\Exception $e) {
                $active = false;
            }
            // Save new value
            update_option('wpmktengine_extension_lms', $active, true);
        }
        // Oh oh, no value, lets add one
        try {
            $ecoomerceActivate = true;
            $activeLeadType = false;
            if ($ecoomerceActivate == true || $isGenoo) {
                // Might be older package
                $ch = curl_init();
                if (defined('GENOO_DOMAIN')) {
                    curl_setopt(
                        $ch,
                        CURLOPT_URL,
                        'https:' .
                            GENOO_DOMAIN .
                            '/api/rest/ecommerceenable/true'
                    );
                } else {
                    curl_setopt(
                        $ch,
                        CURLOPT_URL,
                        'https:' .
                            WPMKTENGINE_DOMAIN .
                            '/api/rest/ecommerceenable/true'
                    );
                }
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'X-API-KEY: ' . $api->key,
                ]);
                $resp = curl_exec($ch);
                if (!$resp) {
                    $active = false;
                    $error = curl_error($ch);
                    $errorCode = curl_errno($ch);
                } else {
                    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202) {
                        // Active whowa whoooaa
                        $active = true;
                        // now, get the lead_type_id
                        $json = json_decode($resp);
                        if (is_object($json) && isset($json->lead_type_id)) {
                            $activeLeadType = $json->lead_type_id;
                        }
                    }
                }
                curl_close($ch);
            }
        } catch (\Exception $e) {
            $active = false;
        }
        // Save new value
        update_option('wpmktengine_extension_ecommerce', $active, true);
        // 3. Check if we can activate the plugin after all
        if ($active == false) {
            genoo_wpme_deactivate_plugin(
                $filePlugin,
                'This extension is not allowed as part of your package.'
            );
        } else {
            // 4. After all we can activate, that's great, lets add those calls
            try {
                $api->setStreamTypes([
                    [
                        'name' => 'viewed course',
                        'description' => '',
                    ],
                    [
                        'name' => 'started course',
                        'description' => '',
                    ],
                    [
                        'name' => 'completed lesson',
                        'description' => '',
                    ],
                    [
                        'name' => 'completed course',
                        'description' => '',
                    ],
                    [
                        'name' => 'viewed product',
                        'description' => '',
                    ],
                    [
                        'name' => 'added product to cart',
                        'description' => '',
                    ],
                    [
                        'name' => 'order completed',
                        'description' => '',
                    ],
                    [
                        'name' => 'order canceled',
                        'description' => '',
                    ],
                    [
                        'name' => 'cart emptied',
                        'description' => '',
                    ],
                    [
                        'name' => 'order refund full',
                        'description' => '',
                    ],
                    [
                        'name' => 'order refund partial',
                        'description' => '',
                    ],
                    [
                        'name' => 'new cart',
                        'description' => '',
                    ],
                    [
                        'name' => 'new order',
                        'description' => '',
                    ],
                    [
                        'name' => 'order cancelled',
                        'description' => '',
                    ],
                    [
                        'name' => 'order refund full',
                        'description' => '',
                    ],
                    [
                        'name' => 'viewed lesson',
                        'description' => '',
                    ],

                    [
                        'name' => 'Quiz completed',
                        'description' => '',
                    ],
                    [
                        'name' => 'Quiz passed',
                        'description' => '',
                    ],
                    [
                        'name' => 'Quiz failed',
                        'description' => '',
                    ],
                    [
                        'name' => 'Certificate Awarded',
                        'description' => '',
                    ],

                    [
                        'name' => 'Achievement Awarded',
                        'description' => '',
                    ],
                ]);
            } catch (\Exception $e) {
                // TODO: log this.
            }
        }
        // Activate and save leadType, import products
        if ($activeLeadType == false || is_null($activeLeadType)) {
            // Leadtype not provided, or NULL, they have to set up for them selfes
            // Create a NAG for setting up the field
            // Shouldnt happen
        } else {
            // Set up lead type
            $option = get_option('WPME_ECOMMERCE', []);
            // Save option
            $option['genooLeadUsercustomer'] = $activeLeadType;
            update_option('WPME_ECOMMERCE', $option);
        }
    }
});

/**
 * Plugin loaded
 */

add_action(
    'wpmktengine_init',
    function ($repositarySettings, $api, $cache) {
        // Lifter LMS extension class
        require_once 'lib/WPME/LifterLMSExtension.php';

        /**
         * Add extensions to the Extensions list
         */

        add_filter(
            'wpmktengine_tools_extensions_widget',
            function ($array) {
                $array['LifterLMS - WPMktgEngine | Genoo Extension'] =
                    '<span style="color:green">Active</span>';
                return $array;
            },
            10,
            1
        );

        /**
         * Add settings page
         *  - if not already in
         */
        add_filter(
            'wpmktengine_settings_sections',
            function ($sections) {
                if (is_array($sections) && !empty($sections)) {
                    $isEcommerce = false;
                    foreach ($sections as $section) {
                        if ($section['id'] == 'ECOMMERCE') {
                            $isEcommerce = true;
                            break;
                        }
                    }
                    if (!$isEcommerce) {
                        $sections[] = [
                            'id' => 'WPME_ECOMMERCE',
                            'title' => __('Ecommerce', 'wpmktengine'),
                        ];
                    }
                }
                return $sections;
            },
            10,
            1
        );

        /**
         * Add fields to settings page
         */
        add_filter(
            'wpmktengine_settings_fields',
            function ($fields) {
                if (
                    is_array($fields) &&
                    array_key_exists('genooLeads', $fields) &&
                    is_array($fields['genooLeads'])
                ) {
                    if (!empty($fields['genooLeads'])) {
                        $exists = false;
                        $rolesSave = false;
                        foreach ($fields['genooLeads'] as $key => $role) {
                            if (
                                $role['type'] == 'select' &&
                                $role['name'] == 'genooLeadUsercustomer'
                            ) {
                                // Save
                                $keyToRemove = $key;
                                $field = $role;
                                // Remove from array
                                unset($fields['genooLeads'][$key]);
                                // Add field
                                $field['label'] =
                                    'Save ' . $role['label'] . ' lead as';
                                $fields['WPME_ECOMMERCE'] = [$field];
                                $exists = true;
                                break;
                            }
                        }
                        if (
                            $exists === false &&
                            isset($fields['genooLeads'][1]['options'])
                        ) {
                            $fields['WPME_ECOMMERCE'] = [
                                [
                                    'label' => 'Save customer lead as',
                                    'name' => 'genooLeadUsercustomer',
                                    'type' => 'select',
                                    'options' =>
                                        $fields['genooLeads'][1]['options'],
                                ],
                            ];
                        }
                    }
                }
                return $fields;
            },
            909,
            1
        );

        /**
         * Genoo Leads, recompile to add ecommerce
         */
        add_filter(
            'option_genooLeads',
            function ($array) {
                if (!is_array($array)) {
                    $array = [];
                }
                // Lead type
                $leadType = 0;
                // Get saved
                $leadTypeSaved = get_option('WPME_ECOMMERCE');
                if (
                    is_array($leadTypeSaved) &&
                    array_key_exists('genooLeadUsercustomer', $leadTypeSaved)
                ) {
                    $leadType = $leadTypeSaved['genooLeadUsercustomer'];
                }
                $array['genooLeadUsercustomer'] = $leadType;
                return $array;
            },
            10,
            1
        );

        /**
         * Viewed Course (name of course viewed)(works)
         * Viewed Lesson (name of Lesson - name of course)(works)
         */
        /**
         * Started Course (name of course)(works)
         */

        add_action(
            'wp',
            function () use ($api) {
                // Get user
                $user = wp_get_current_user();
                if ('course' === get_post_type() and is_singular()) {
                    // Course
                    global $post;
                    $api->putActivityByMail(
                        $user->user_email,
                        'Viewed course',
                        '' . $post->post_title . '',
                        '',
                        get_permalink($post->ID)
                    );
                } elseif ('lesson' === get_post_type() and is_singular()) {
                    global $post, $wpdb;

                    $course_id = get_post_meta(
                        $post->ID,
                        '_llms_parent_course',
                        true
                    );

                    $product = get_post($course_id);

                    $starts = $wpdb->get_results(
                        "SELECT * FROM $wpdb->postmeta
                     WHERE post_id=$course_id AND meta_key = '_start_course_ref' AND  meta_value = '" .
                            $user->ID .
                            "' LIMIT 1"
                    );

                    if (!$starts) {
                        $api->putActivityByMail(
                            $user->user_email,
                            'Started course',
                            '' . $product->post_title . '',
                            '',
                            get_permalink($product->ID)
                        );
                        add_post_meta(
                            $course_id,
                            '_start_course_ref',
                            $user->ID
                        );
                    }

                    $parent = get_post_meta($post->ID, '_parent_course', true);
                    $api->putActivityByMail(
                        $user->user_email,
                        'Viewed lesson',
                        '' .
                            $post->post_title .
                            ' - ' .
                            get_post($parent)->post_title .
                            '',
                        '',
                        get_permalink($post->ID)
                    );
                }
            },
            10
        );

        /**
         * Completed Lesson (name of Lesson - name of course)(works)
         */

        add_action(
            'lifterlms_lesson_completed',
            function ($user_id, $lesson_id) use ($api) {
                // Get user
                $user = new \WP_User($user_id);
                $lesson = get_post($lesson_id);
                $parent = get_post_meta($lesson->ID, '_parent_course', true);
                $api->putActivityByMail(
                    $user->user_email,
                    'Completed lesson',
                    '' .
                        $lesson->post_title .
                        ' - ' .
                        get_post($parent)->post_title .
                        '',
                    '',
                    get_permalink($lesson->ID)
                );
            },
            10,
            2
        );

        add_action(
            'lifterlms_quiz_completed',
            function ($user_id, $quiz_id) use ($api) {
                global $wpdb;

                $query_items = quiz_state($user_id, $quiz_id);

                $api->putActivityByMail(
                    $query_items['user']->user_email,
                    'Quiz completed',
                    '' .
                        $query_items['quiz_ids']->post_title .
                        ' - ' .
                        get_post($query_items['parent'])->post_title .
                        '',
                    '',
                    get_permalink($query_items['quiz_ids']->ID)
                );
            },
            10,
            2
        );

        add_action(
            'lifterlms_quiz_failed',
            function ($user_id, $quiz_id) use ($api) {
                $query_items = quiz_state($user_id, $quiz_id);

                $api->putActivityByMail(
                    $query_items['user']->user_email,
                    'Quiz failed',
                    '' .
                        $query_items['quiz_ids']->post_title .
                        ' - ' .
                        get_post($query_items['parent'])->post_title .
                        '',
                    '',
                    get_permalink($query_items['quiz_ids']->ID)
                );
            },
            10,
            2
        );

        add_action(
            'lifterlms_quiz_passed',
            function ($user_id, $quiz_id) use ($api) {
                $query_items = quiz_state($user_id, $quiz_id);

                $api->putActivityByMail(
                    $query_items['user']->user_email,
                    'Quiz passed',
                    '' .
                        $query_items['quiz_ids']->post_title .
                        ' - ' .
                        get_post($query_items['parent'])->post_title .
                        '',
                    '',
                    get_permalink($query_items['quiz_ids']->ID)
                );
            },
            10,
            2
        );

        function quiz_state($user_id, $quiz_id)
        {
            $complete_arrays = [];

            $complete_arrays['user'] = new \WP_User($user_id);

            $complete_arrays['quiz_ids'] = get_post($quiz_id);

            $complete_arrays['parent'] = get_post_meta(
                $complete_arrays['quiz_ids']->ID,
                '_llms_lesson_id',
                true
            );

            return $complete_arrays;
        }
        /**
         * Completed Course (name of course)(works)
         */

        add_action(
            'lifterlms_course_completed',
            function ($user_id, $course_id) use ($api) {
                global $wpdb;
                // Get user
                $user = new \WP_User($user_id);
                $course = get_post($course_id);
                $api->putActivityByMail(
                    $user->user_email,
                    'Completed course',
                    '' . $course->post_title . '',
                    '',
                    get_permalink($course->ID)
                );
                $api->putActivityByMail(
                    $user->user_email,
                    'Completed section',
                    '' . $course->post_title . '',
                    '',
                    get_permalink($course->ID)
                );
            },
            10,
            2
        );

        add_action('llms_user_earned_certificate', function () use ($api) {
            global $wpdb;

            $user = wp_get_current_user();

            $certificates = $wpdb->get_results(
                "SELECT post_id,meta_value FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE user_id = $user->ID AND meta_key = '_certificate_earned'"
            );

            $i = 0;

            foreach ($certificates as $certificate):
                if ($i % 2 == 0) {
                    $certificatevalue = $certificate->meta_value;
                }
                $i++;
            endforeach;

            $lifterlms_certificate = get_post($certificatevalue);

            $api->putActivityByMail(
                $user->user_email,
                'Certificate Awarded',
                '' . $lifterlms_certificate->post_title . '',
                '',
                get_permalink($lifterlms_certificate->ID)
            );
        });
        add_action('llms_user_earned_achievement', function () use ($api) {
            global $wpdb;

            $user = wp_get_current_user();

            $certificates = $wpdb->get_results(
                "SELECT post_id,meta_value FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE user_id = $user->ID AND meta_key = '_achievement_earned'"
            );

            $i = 0;

            foreach ($certificates as $certificate):
                if ($i % 2 == 0) {
                    $certificatevalue = $certificate->meta_value;
                }
                $i++;
            endforeach;

            $achievement_certificate = get_post($certificatevalue);

            $api->putActivityByMail(
                $user->user_email,
                'Achievement Awarded',
                '' . $achievement_certificate->post_title . '',
                '',
                get_permalink($achievement_certificate->ID)
            );
        });
        /**
         * Actual Order
         */
        add_action(
            'lifterlms_order_process_success',
            function ($order) {
                /* genoo_wpme_log_to_file('starting order'); */
                if (isset($order) && is_object($order)) {
                    // Only if Woocommerce is not being used
                    if (!LifterLMSExtension::isWooCommerceEnalbed()) {
                        // Get API
                        global $WPME_API;
                        // Do we have it?
                        if (isset($WPME_API)) {
                            /* genoo_wpme_log_to_file('we have api and woo not enabled order'); */
                            // Start magic product insert and order insert, Lead Creation
                            // Get the email etc.
                            $user = new \WP_User($order->user_id);
                            $userEmail = $user->user_email;
                            $userName = $user->first_name;
                            $userLastName = $user->last_name;
                            $api = $WPME_API;
                            // If Lead
                            $lead_id = genoo_wpme_get_set_user_lid(
                                $order->user_id,
                                $WPME_API,
                                ['source' => 'Lifter LMS']
                            );
                            /* genoo_wpme_log_to_file('lead_id ' . $lead_id); */
                            if ($lead_id !== false) {
                                // We have a lead, whooo aa ...
                                $product_id_external = $order->product_id;
                                if (method_exists($api, 'callCustom')) {
                                    try {
                                        $product_id = false;
                                        $product = $api->callCustom(
                                            '/wpmeproductbyextid/' .
                                                $product_id_external,
                                            'GET',
                                            null
                                        );
                                        if (
                                            $api->http->getResponseCode() == 204
                                        ) {
                                            // No content, product not set
                                            // set product and then continue
                                            $product_id = false;
                                        } elseif (
                                            $api->http->getResponseCode() == 200
                                        ) {
                                            if (
                                                is_object($product) &&
                                                isset($product->product_id)
                                            ) {
                                                $product_id =
                                                    $product->product_id;
                                            }
                                        }
                                    } catch (Exception $e) {
                                        if (
                                            $api->http->getResponseCode() == 404
                                        ) {
                                            // Api call not implemented, we have to get all products and crawl through
                                            try {
                                                $products = $api->callCustom(
                                                    '/wpmeproducts',
                                                    'GET',
                                                    null
                                                );
                                                if (
                                                    is_array($products) &&
                                                    !empty($products)
                                                ) {
                                                    foreach (
                                                        $products
                                                        as $product
                                                    ) {
                                                        if (
                                                            $product->external_product_id ==
                                                            $product_id_external
                                                        ) {
                                                            $product_id =
                                                                $product->product_id;
                                                            break;
                                                        }
                                                    }
                                                }
                                            } catch (Exception $e) {
                                            }
                                        } elseif (
                                            $api->http->getResponseCode() == 204
                                        ) {
                                            // No content, product not set
                                            // set product and then continue
                                            $product_id = false;
                                        }
                                    }
                                    /* genoo_wpme_log_to_file('product id' . $product_id); */
                                    // Do we have internal product_id?
                                    if ($product_id == false) {
                                        // We do not have internal product_id, let's create it
                                        try {
                                            $data = [
                                                'categories' => [],
                                                'id' => $product_id_external,
                                                'name' => $order->product_title,
                                                'price' =>
                                                    $order->product_price,
                                                'sku' => $order->product_sku,
                                                'tags' => '',
                                                'type' => $order->product_type,
                                                'url' => '',
                                                'vendor' => '',
                                                'weight' => 0,
                                                'option1_name' => '',
                                                'option1_value' => '',
                                                'option2_name' => '',
                                                'option2_value' => '',
                                                'option3_name' => '',
                                                'option3_value' => '',
                                            ];
                                            $result = $api->setProduct($data);
                                            if (
                                                is_array($result) &&
                                                isset($result[0])
                                            ) {
                                                $product_id =
                                                    $result[0]->product_id;
                                            }
                                            /* genoo_wpme_log_to_file($result); */
                                        } catch (\Exception $e) {
                                            $product_id = false;
                                        }
                                    }
                                    // Let's see if it's saved, if not we just don't continue, if yes, we do
                                    if ($product_id !== false) {
                                        // Prep data
                                        $cartContents = \LifterLMSExtension::createCartContents(
                                            $product_id,
                                            $order->total,
                                            $order->product_price,
                                            $order->product_id,
                                            $order->product_title
                                        );
                                        $cartTotal = $order->total;
                                        // We have a LEAD_ID and PRODUCT_ID ... we can finish the ORDER ...
                                        // Start order if product in
                                        try {
                                            $cartOrder = new \WPME\Ecommerce\CartOrder();
                                            $cartOrder->setApi($WPME_API);
                                            $cartOrder->addItemsArray(
                                                $cartContents
                                            );
                                            $cartOrder->actionNewOrder();
                                            $cartOrder->actionOrderFullfillment();
                                            $cartOrder->setUser($lead_id);
                                            $cartOrder->setBillingAddress(
                                                $order->billing_address_1,
                                                $order->billing_address_2,
                                                $order->billing_city,
                                                $order->billing_country,
                                                '',
                                                $order->billing_zip,
                                                '',
                                                $order->billing_state
                                            );
                                            $cartOrder->setAddressShippingSameAsBilling();
                                            //$cartOrder->order_number = $data['caffitid'];
                                            $cartOrder->setTotal($cartTotal);
                                            // Status?
                                            $cartOrder->financial_status =
                                                'paid';
                                            $cartOrder->changed->financial_status =
                                                'paid';
                                            // Completed
                                            $cartOrder->completed_date = \WPME\Ecommerce\Utils::getDateTime();
                                            $cartOrder->changed->completed_date = \WPME\Ecommerce\Utils::getDateTime();
                                            // Completed?
                                            $cartOrder->order_status =
                                                'completed';
                                            $cartOrder->changed->order_status =
                                                'completed';
                                            // Send!
                                            $cartOrder->startNewOrder();
                                            /* genoo_wpme_log_to_file('order object'); */
                                            /* genoo_wpme_log_to_file($cartOrder); */
                                        } catch (Exception $e) {
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            },
            9,
            1
        );
    },
    10,
    3
);

/**
 * Get lead type for ecommerce
 */
if (!function_exists('wpme_get_customer_lead_type')) {
    /**
     * Get Customer Lead Type
     *
     * @return bool|int
     */
    function wpme_get_customer_lead_type()
    {
        $leadType = false;
        $leadTypeSaved = get_option('WPME_ECOMMERCE');
        if (
            is_array($leadTypeSaved) &&
            array_key_exists('genooLeadUsercustomer', $leadTypeSaved)
        ) {
            $leadType = (int) $leadTypeSaved['genooLeadUsercustomer'];
        }
        return $leadType === 0 ? false : $leadType;
    }
}

/**
 * Genoo / Wpme get set User LID
 */
if (!function_exists('genoo_wpme_get_set_user_lid')) {
    /**
     * Genoo / Wpme get set User LID
     *
     * @param       $user_id
     * @param       $api
     * @param array $data
     * @return bool|int
     */
    function genoo_wpme_get_set_user_lid($user_id, $api, $data = [])
    {
        $lead_id = get_user_meta($user_id, '_gtld', true);
        if (is_numeric($lead_id)) {
            // lead id exists, return it
            return (int) $lead_id;
        } else {
            // no lead id, let's create it
            $user = new \WP_User($user_id);
            $leadType = wpme_get_customer_lead_type();
            if ($leadType !== false) {
                $leadNew = $api->setLead(
                    (int) $leadType,
                    $user->user_email,
                    '',
                    '',
                    '',
                    false,
                    $data
                );
                $leadNew = (int) $leadNew;
                if (!is_null($leadNew)) {
                    // We have a lead id
                    $lead_id = $leadNew;
                    return $lead_id;
                }
                return false;
            }
        }
        return false;
    }
}

/**
 * Genoo WPME log
 */
if (!function_exists('genoo_wpme_log_to_file')) {
    /**
     * @param $data
     */
    function genoo_wpme_log_to_file($data)
    {
        $date = new DateTime();
        $req_dump =
            '=======================================================' . "\r\n";
        $req_dump .= $date->format('Y-m-d H:i:s') . "\r\n";
        $req_dump .=
            '=======================================================' . "\r\n";
        $req_dump .= print_r($data, true);
        $req_dump .= var_export($data, true);
        $req_dump .=
            '=======================================================' . "\r\n";
        $fp = fopen(__DIR__ . '/log.log', 'a');
        fwrite($fp, $req_dump);
        fclose($fp);
    }
}

/**
 * Genoo / WPME deactivation function
 */
if (!function_exists('genoo_wpme_deactivate_plugin')) {
    /**
     * @param $file
     * @param $message
     * @param string $recover
     */

    function genoo_wpme_deactivate_plugin($file, $message, $recover = '')
    {
        // Require files
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        // Deactivate plugin
        deactivate_plugins($file);
        unset($_GET['activate']);
        // Recover link
        if (empty($recover)) {
            $recover =
                '</p><p><a href="' .
                admin_url('plugins.php') .
                '">&laquo; ' .
                __('Back to plugins.', 'wpmktengine') .
                '</a>';
        }
        // Die with a message
        wp_die($message . $recover);
        exit();
    }
}

//apply_filters( 'lifterlms_lesson_start_quiz_redirect', $redirect );
//apply_filters( 'lifterlms_login_redirect', $redirect, $user->ID );
//apply_filters( 'lifterlms_product_purchase_account_redirect', $account_redirect ) // link
//apply_filters( 'lifterlms_product_purchase_redirect_membership_required', $membership_url ); // membriship required link
//add_filter('lifterlms_login_redirect', function($redirect, $user_id){}, 100, 2);
//apply_filters( 'lifterlms_lesson_start_quiz_redirect', $redirect );
//apply_filters( 'lifterlms_login_redirect', $redirect, $user->ID );
//apply_filters( 'lifterlms_product_purchase_account_redirect', $account_redirect ) // link
//apply_filters( 'lifterlms_product_purchase_redirect_membership_required', $membership_url ); // membriship required link
//add_filter('lifterlms_login_redirect', function($redirect, $user_id){}, 100, 2);
//apply_filters( 'lifterlms_lesson_start_quiz_redirect', $redirect );
