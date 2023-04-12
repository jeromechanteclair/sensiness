<?php

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Fonction qui retourne un tableau indiquant si le module est installé ou non
 * @return tableau contenant les info sur l'activation du module
 */
function ntav_isActiveModule()
{
    if (ntav_check_isApiActive() == false) {
        return array(
            'debug' => 'Module Disabled. Check if module is active in its tab configuration in Wordpress Back Office.',
            'return' => 2, //Module désactivé
            'query' => 'isActive'
        );
    }
    return array(
        'debug' => 'Module installed and enabled',
        'return' => 1, //Module OK
        'query' => $_POST['query']
    );
}

/**
 * Fonction qui verifie si l'idwebsite et la secretKey corresponde avec celle presente sur la boutique
 * @param array $message contient les info envoye depuis la plateforme
 * @param string $post_query contient le nom de la requete
 * @return array  $reponse  contient le retour de la comparaison entre les info de la boutique et de la plateforme
 */
function ntav_check_data($message, $post_query)
{
    if (!empty($message['lang_code'])) {
        $WpmlEnable = ntav_getWpmlEnable();
        if ($WpmlEnable != 'yes') {
            $message['lang_code'] = '';
        } else {
            $WPML_config = ntav_WPMLoption();
            foreach ($WPML_config as $key => $value) {
                $lang = explode(".", $value);
                if (
                    ntav_getConfig('SECRET_KEY', strtoupper($lang[0])) && (ntav_getConfig(
                        'ID_WEBSITE',
                        strtoupper($lang[0])
                    ) == $message['idWebsite'])
                ) {
                    $message['lang_code'] = strtoupper($lang[0]);
                }
            }
        }
    }

    $secret_key = ntav_getConfig('SECRET_KEY', $message['lang_code']);
    $id_website = ntav_getConfig('ID_WEBSITE', $message['lang_code']);

    $SHA1 = sha1($post_query . $id_website . $secret_key);

    if (!$id_website or !$secret_key) {
        $reponse['debug'] = "Identifiants clients non renseignés sur le module";
        $reponse['message'] = "Customer IDs not specified on the module";
        $reponse['return'] = 3; //A definir
        $reponse['query'] = 'check_data';
        return $reponse;
    } elseif ($message['idWebsite'] != $id_website) {
        $reponse['message'] = "Clé Website incorrecte";
        $reponse['debug'] = "Incorrect IdWebsite ";
        $reponse['return'] = 4; //A definir
        $reponse['query'] = 'check_data';
        return $reponse;
    } elseif ($message['sign'] != $SHA1) {
        $reponse['message'] = "La signature est incorrecte";
        $reponse['debug'] = "The signature is incorrect";
        $reponse['return'] = 5; //A definir
        $reponse['query'] = 'check_data';
        return $reponse;
    }
    $reponse['message'] = "Identifiants Client Ok";
    $reponse['debug'] = "Customer IDs OK";
    $reponse['return'] = 1; //A defin
    $reponse['query'] = 'check_data';
    return $reponse;
}

/**
 * Fonction modifiant la configuration du module
 * @param array $message contient les valeurs de configuration a modifie
 * @return array renvoi dans un tableau la configuration présente sur la boutique
 */
function ntav_setModuleConfiguration($message)
{
    ntav_updateConfig('PROCESSINIT', $message['init_reviews_process'], $message['lang_code']);
    //Implode si plusieurs éléments donc is_array
    $ORDERSTATESCHOOSEN = (is_array($message['id_order_status_choosen'])) ? implode(
        ';',
        $message['id_order_status_choosen']
    ) : $message['id_order_status_choosen'];
    ntav_updateConfig('ORDERSTATESCHOOSEN', $ORDERSTATESCHOOSEN, $message['lang_code']);
    ntav_updateConfig('DELAY', $message['delay'], $message['lang_code']);
    ntav_updateConfig('GETPRODREVIEWS', $message['get_product_reviews'], $message['lang_code']);
    ntav_updateConfig('DISPLAYPRODREVIEWS', $message['display_product_reviews'], $message['lang_code']);
    ntav_updateConfig('SCRIPTFLOAT_ALLOWED', $message['display_float_widget'], $message['lang_code']);
    ntav_updateConfig('URLCERTIFICAT', $message['url_certificat'], $message['lang_code']);
    ntav_updateConfig('OUR_PRODUCT_LIST_RATING', $message['display_list_stars'], $message['lang_code']);
    ntav_updateConfig('WOOTAB_DEACTIVE', $message['disable_woo_reviews_tab'], $message['lang_code']);
    ntav_updateConfig('OUR_TAB_ACTIVE', $message['display_av_reviews_tab'], $message['lang_code']);
    ntav_updateConfig('OUR_PRODUCT_RATING', $message['display_product_stars'], $message['lang_code']);
    ntav_updateConfig('OUR_PRODUCT_RICHSNIP', $message['enable_rich_snippets_product'], $message['lang_code']);
    ntav_updateConfig('TYPE_SNIPPET', $message['type_snippet'], $message['lang_code']);
    ntav_updateConfig('NEED_COMPLETE_RS', $message['enable_complete_rich_snippets_product'], $message['lang_code']);
    //Implode si plusieurs éléments donc is_array
    $FORBIDDENEMAIL = (is_array($message['forbidden_mail_extension'])) ? implode(
        ';',
        $message['forbidden_mail_extension']
    ) : $message['forbidden_mail_extension'];
    ntav_updateConfig('FORBIDDEN_EMAIL', $FORBIDDENEMAIL, $message['lang_code']);
    // Encode le donnee avant le sauvegarde dans le db.
    $data_float = ntav_AV_encode_base64(
        str_replace(array("\r\n", "\n"), '', stripslashes(str_replace('\"', '"', $message['script_float_widget'])))
    );
    ntav_updateConfig('SCRIPTFLOAT', $data_float, $message['lang_code']);

    $reponse['message'] = ntav_getModuleAndSiteInfos($message['lang_code']);
    $reponse['debug'] = "La configuration du site a été mise à jour";
    $reponse['return'] = 1; //A definir
    $reponse['query'] = $message['query'];

    return $reponse;
}

/**
 * Recuere la configuration presente sur la boutique
 * @param array $message contient la requete envoye depuis la plateforme
 * @return array $reponse contient les info de la configuration du module ou un message d'erreur
 */
function ntav_getModuleAndSiteConfiguration($message)
{
    $reponse['message'] = ntav_getModuleAndSiteInfos($message['lang_code']);
    $reponse['query'] = $message['query'];
    $reponse['return'] = (empty($reponse['message'])) ? 2 : 1; // 2:error, 1:success.

    return $reponse;
}

/**
 * Fonction qui recupere les commandes et les produits associés en fonction de la configuration
 * @param array $message contient les info de la requete envoye depuis la plateforme
 * @param string $post_query contient la requete envoye depuis la plateforme
 * @return array  $reponse contient les commandes recupere ou un message d'erreur si un probleme est survenue
 */
function ntav_getOrders($message, $post_query)
{
    $wc_version = ntav_getConfig('WCVERSION', 'non');
    if ($wc_version < "2.6.0") {
        return ntav_getOrders_old($message, $post_query);
    }
    $allowedProducts = ntav_getConfig('GETPRODREVIEWS', $message['lang_code']);
    $processChoosen = ntav_getConfig('PROCESSINIT', $message['lang_code']);
    $statusChoosen = implode(",", explode(";", ntav_getConfig('ORDERSTATESCHOOSEN', $message['lang_code'])));
    $forbiddenMailExtensions = explode('%3B', ntav_getConfig('FORBIDDEN_EMAIL', $message['lang_code']));
    $delay = ntav_getConfig('DELAY', $message['lang_code']);
    $list_status = ntav_get_status($statusChoosen);
    $WpmlEnable = ntav_getWpmlEnable();
    $config = array(
        'includeProducts' => $allowedProducts,
        'status' => ($processChoosen == 'onorderstatuschange') ? $statusChoosen : '',
        'flag' => true
    );

    if ($message['force'] == 1) {
        if ($message['date_deb'] && $message['date_fin']) {
            $from = date("Y-m-d H:i:s", strtotime($message['date_deb']));
            $to = date("Y-m-d H:i:s", strtotime($message['date_fin']));
            $data = ntav_getDataOrders(array_merge($config, array('from' => $from, 'to' => $to)));
            if ($data == null || $data == false) {
                $reponse['debug']['mode'] = "[forcé] " . '0' . " commandes récupérées en force du " . $from . " au " . $to;
            } else {
                $reponse['debug']['mode'] = "[forcé] " . count(
                    $data
                ) . " commandes récupérées en force du " . $from . " au " . $to;
            }
        } else { // en cas d'erreur
            $reponse['debug'][] = "Aucune période renseignée pour la récupération des commandes en mode forcé";
            return $reponse;
        }
    } elseif ($processChoosen == 'onorder' || $processChoosen == 'onorderstatuschange') {
        /*** GET ORDERS ***/
        $time_today = new DateTime('now');
        $final_date = $time_today->format('Y-m-d');
        $initial_date = $time_today->modify('-5 month')->format('Y-m-d');
        $list_status_o = ntav_getOrderStatus_origin($list_status);
        $status_array_find = ($processChoosen == "onorder") ? array("wc-") : $list_status_o;
        $args = array(
            'status' => $status_array_find,
            'limit' => 500,
            'return' => 'ids',
            'date_created' => $initial_date . '...' . $final_date,
            'type' => 'shop_order',
            'meta_key' => 'av_flag',
            'meta_value' => '0'
        );
        $orders = wc_get_orders($args);
        $order_list = array();
        foreach ($orders as $o_key => $order_id) {
            $order = wc_get_order($order_id);
            $customerEmailExtension = explode('@', $order->get_billing_email());
            if (!in_array($customerEmailExtension[1], $forbiddenMailExtensions)) {
                $lang_code = get_post_meta($order_id, 'wpml_language', true);
                if (($WpmlEnable == "yes" && $message['lang_code'] == $lang_code) || $WpmlEnable == "no") {
                    $flag_mark = get_post_meta($order_id, 'av_flag', true);
                    $order_list[$order_id]["id_order"] = $order_id;
                    $order_data = $order->get_data();
                    $date_order = strtotime($order_data['date_created'] . '');
                    $order_list[$order_id]["date_order"] = $date_order;
                    $order_list[$order_id]["amount_order"] = $order->get_total();
                    $order_list[$order_id]["date_order_formatted"] = date('d/m/Y H:i', $date_order);
                    $date_modified = strtotime($order->get_date_modified() . '');
                    $order_list[$order_id]["date_last_status_change"] = date('d/m/Y H:i', $date_modified);
                    $order_list[$order_id]["date_av_getted_order"] = '';
                    $order_list[$order_id]["is_flag"] = $flag_mark;
                    $order_list[$order_id]["state_order"] = $order->get_status();
                    $order_list[$order_id]["id_customer"] = $order->get_customer_id();
                    $order_list[$order_id]["firstname_customer"] = $order->get_billing_first_name();
                    $order_list[$order_id]["lastname_customer"] = $order->get_billing_last_name();
                    $order_list[$order_id]["email_customer"] = $order->get_billing_email();
                    $order_list[$order_id]["lang"] = $lang_code;
                    if (strtolower($allowedProducts) === 'no') {
                        $order_list[$order_id]["products"] = array();
                    } else {
                        $i = 0;
                        foreach ($order->get_items() as $item_id => $item) {
                            if ($WpmlEnable == "yes") {
                                $id_product = apply_filters(
                                    'wpml_object_id',
                                    $item->get_product_id(),
                                    'post',
                                    true,
                                    $lang_code
                                );
                            } else {
                                $id_product = $item->get_product_id();
                            }
                            $product = new WC_Product($id_product);
                            $order_list[$order_id]["products"][$i]["id_product"] = $id_product;
                            $order_list[$order_id]["products"][$i]["name_product"] = $product->get_name();
                            $order_list[$order_id]["products"][$i]["sku"] = $product->get_sku();
                            $order_list[$order_id]["products"][$i]["url"] = get_post_permalink($id_product);
                            $order_list[$order_id]["products"][$i]["url_image"] = ntav_get_image_url(get_the_post_thumbnail($id_product));
                            $order_list[$order_id]["products"][$i]["MPN"] = ntav_get_product_attrlabels($id_product, "MPN");
                            $order_list[$order_id]["products"][$i]["GTIN_EAN"] = ntav_get_product_attrlabels($id_product, "GTIN");
                            $order_list[$order_id]["products"][$i]["brand_name"] = ntav_get_product_attrlabels($id_product, "BRAND");
                            $i++;
                        }
                    }
                }
            } else {
                $reponse['message']['Emails_Interdits'][] = 'Commande n°' . $order_id . ' Email:' . $order->get_billing_email(
                );
            }
        } //END FOREACH
        /*** GET ORDERS ***/
        if ($order_list) { //$statusChoosen
            $reponse['debug']['mode'] = "[" . $processChoosen . "]" . count($order_list) . " commandes récupérées";
            $reponse['debug']['status'] = $processChoosen == "onorder" ? "onorder" : $list_status;
            $reponse['return'] = 2;

            //UPDATE FLAG
            $noFlag = $message['no_flag'];
            if (isset($noFlag) && $noFlag == 0 && count($order_list) > 0) {
                ntav_updateFlag($orders);
            }
        } else {
            $reponse['debug']['mode'] = "[" . $processChoosen . "]" . ' pas de commandes';
            $reponse['debug']['status'] = $processChoosen == "onorder" ? "onorder" : $list_status;
            $reponse['return'] = 3;
        }
    } else { // en cas d'erreur
        $reponse['debug'][] = "Aucun évênement onorder ou onorderstatuschange n'a été renseigné pour la récupération des commandes";
        $reponse['return'] = 3;
        return $reponse;
    }

    $reponse['return'] = 1;
    $reponse['query'] = $post_query; // get request post
    $reponse['message']['nb_orders'] = count($order_list);
    $reponse['message']['delay'] = $delay;
    $reponse['message']['nb_orders_bloques'] = 0;
    $reponse['message']['list_orders'] = $order_list;
    if ($processChoosen != 'onorder') {
        $reponse['debug']['status_o'] = $list_status_o;
    }
    $reponse['debug']['force'] = $message['force'];
    $reponse['debug']['produit'] = $allowedProducts;
    $reponse['debug']['no_flag'] = $message['no_flag'];
    $reponse['debug']['lang'] = $message['lang_code'];
    $reponse['debug']['wpml'] = $WpmlEnable;
    $reponse['debug']['wcversion'] = $wc_version;
    return $reponse;
}

function ntav_getOrders_old($message, $post_query)
{
    $allowedProducts = ntav_getConfig('GETPRODREVIEWS', $message['lang_code']);
    $processChoosen = ntav_getConfig('PROCESSINIT', $message['lang_code']);
    $pack_limit = ntav_getConfig('AV_LIMIT_NB_ORDERS', $message['lang_code']);
    $statusChoosen = implode(",", explode(";", ntav_getConfig('ORDERSTATESCHOOSEN', $message['lang_code'])));
    $forbiddenMailExtensions = explode('%3B', ntav_getConfig('FORBIDDEN_EMAIL', $message['lang_code']));
    $delay = ntav_getConfig('DELAY', $message['lang_code']);

    $list_status = ntav_get_status($statusChoosen);

    $config = array(
        'includeProducts' => $allowedProducts,
        'status' => ($processChoosen == 'onorderstatuschange') ? $statusChoosen : '',
        'flag' => true
    );

    if ($message['force'] == 1) {
        if ($message['date_deb'] && $message['date_fin']) {
            $from = date("Y-m-d H:i:s", strtotime($message['date_deb']));
            $to = date("Y-m-d H:i:s", strtotime($message['date_fin']));
            $data = ntav_getDataOrders(array_merge($config, array('from' => $from, 'to' => $to)));
            if ($data == null || $data == false) {
                $reponse['debug']['mode'] = "[forcé] " . '0' . " commandes récupérées en force du " . $from . " au " . $to;
            } else {
                $reponse['debug']['mode'] = "[forcé] " . count(
                    $data
                ) . " commandes récupérées en force du " . $from . " au " . $to;
            }
        } else { // en cas d'erreur
            $reponse['debug'][] = "Aucune période renseignée pour la récupération des commandes en mode forcé";
            return $reponse;
        }
    } elseif ($processChoosen == 'onorder') {
        $data = ntav_getDataOrders($config);
        if ($data == null || $data == false) {
            $reponse['debug']['mode'] = "[onorder] " . '0' . " commandes récupérées";
        } else {
            $reponse['debug']['mode'] = "[onorder] " . count($data) . " commandes récupérées";
        }
    } elseif ($processChoosen == 'onorderstatuschange') {
        if (!empty($statusChoosen)) {
            $data = ntav_getDataOrders($config);
            if ($data == null || $data == false) {
                $reponse['debug']['mode'] = "[onorderstatuschange] " . '0' . " commandes récupérées avec statut " . $list_status;
            } else {
                $reponse['debug']['mode'] = "[onorderstatuschange] " . count(
                    $data
                ) . " commandes récupérées avec statut " . $list_status;
            }
        } else { // en cas d'erreur
            $reponse['debug'][] = "Aucun statut n'a été renseigné pour la récupération des commandes en fonction de leur statut";
            $reponse['return'] = 2;
            return $reponse;
        }
    } else { // en cas d'erreur
        $reponse['debug'][] = "Aucun évênement onorder ou onorderstatuschange n'a été renseigné pour la récupération des commandes";
        $reponse['return'] = 3;
        return $reponse;
    }

    $ordersIds = $tmp = array();

    if (!empty($data) && $pack_limit && is_numeric($pack_limit) && $pack_limit > 0) {
        $data = array_slice($data, 0, $pack_limit);
    }

    if (!empty($data)) {
        foreach ($data as $order) {
            if (!$set_pack_limit || ($set_pack_limit && $pack_limit > 0 && $order_index <= $pack_limit)) { // start if
                $customerEmailExtension = explode('@', $order['email']);
                if (!in_array($customerEmailExtension[1], $forbiddenMailExtensions)) {
                    // save same order into once.
                    $id = (int)$order['refcommande'];
                    $tmp2 = (isset($tmp[$id])) ? $tmp[$id] : array();
                    $tmp[$id] = array_merge($tmp2, array(
                        'id_order' => $order['refcommande'],
                        'date_order' => $order['timestamp'],
                        //date timestamp de la table orders
                        'amount_order' => $order['amount_order'],
                        'date_order_formatted' => $order['datecommande'],
                        //date de la table orders formatté
                        'date_av_getted_order' => (isset($order['date_av_getted_order'])) ? $order['date_av_getted_order'] : null,
                        //date de la table order_history de récup par AV
                        'date_last_status_change' => $order['date_modified'],
                        'is_flag' => (isset($order['is_flag'])) ? $order['is_flag'] : null,
                        //si la commande est déjàÂ flaggué
                        'state_order' => $order['status_order'],
                        // we use the status and not the state.
                        'id_customer' => $order['id_customer'],
                        'firstname_customer' => $order['prenom'],
                        'lastname_customer' => $order['nom'],
                        'email_customer' => $order['email'],
                        'lang' => $order['lang'],
                    ));// add order products as array.
                    if (strtolower($allowedProducts) === 'no') {
                        $tmp[$id]['products'] = array();
                    } else {
                        $label_mpn = ntav_getConfig('ATTRIBUTE_MPN', 'non');
                        $label_gtin = ntav_getConfig('ATTRIBUTE_GTIN', 'non');
                        $label_brand = ntav_getConfig('ATTRIBUTE_BRAND', 'non');
                        $prefix_attr = 'pa_';
                        try {
                            $product = new WC_Product($order['product_id']);
                        } catch (Exception $e) {
                            continue;
                        }

                        if (!empty($label_mpn)) {
                            $variable_mpn = wc_get_product_terms(
                                $product->get_id(),
                                $prefix_attr . $label_mpn,
                                array('fields' => 'names')
                            );
                            $variable_mpn = array_shift($variable_mpn);
                        } else {
                            $variable_mpn = null;
                        }
                        if (!empty($label_gtin)) {
                            $variable_gtin = wc_get_product_terms(
                                $product->get_id(),
                                $prefix_attr . $label_gtin,
                                array('fields' => 'names')
                            );
                            $variable_gtin = array_shift($variable_gtin);
                        } else {
                            $variable_gtin = null;
                        }
                        if (!empty($label_brand)) {
                            $variable_brand = wc_get_product_terms(
                                $product->get_id(),
                                $prefix_attr . $label_brand,
                                array('fields' => 'names')
                            );
                            $variable_brand = array_shift($variable_brand);
                        } else {
                            $variable_brand = null;
                        }

                        //We retrieve product details depending if WPML is enable or not. (Specific URL product and ID_product)
                        $WpmlEnable = ntav_getWpmlEnable();
                        if ($WpmlEnable == "yes") {
                            global $wpdb;
                            $lang = $order['lang'];
                            $product_id = $order['product_id'];

                            $sql = "SELECT wp.post_title, wp.ID FROM " . $wpdb->prefix . "posts AS wp 
                                        LEFT JOIN " . $wpdb->prefix . "icl_translations AS wit ON wp.ID = wit.element_id
                                        WHERE 
                                            wit.trid = (SELECT trid FROM " . $wpdb->prefix . "icl_translations WHERE element_id = $product_id AND element_type = 'post_product') 
                                            AND wit.language_code = '$lang'";

                            $resultsWpml = $wpdb->get_results($sql);
                            $order['product_name'] = $resultsWpml[0]->post_title;
                            $order['url'] = get_post_permalink($resultsWpml[0]->ID);
                            $order['product_id'] = $resultsWpml[0]->ID;
                        }

                        $tmp[$id]['products'][] = array(
                            'id_product' => $order['product_id'],
                            'name_product' => $order['product_name'],
                            'sku' => $product->get_sku(),
                            'url' => $order['url'],
                            'url_image' => $order['url_image'],
                            'MPN' => $variable_mpn,
                            'GTIN_EAN' => $variable_gtin,
                            'brand_name' => $variable_brand
                        );
                    }
                    $ordersIds[] = $id;
                } else {
                    $reponse['message']['Emails_Interdits'][] = 'Commande n°' . $order['increment_id'] . ' Email:' . $order['email'];
                }
            }//endif
        }

        // update Flag db;
        $noFlag = $message['no_flag'];
        if (isset($noFlag) && $noFlag == 0) {
            ntav_updateFlag($ordersIds);
        }
    }
    //die();
    // return value
    $reponse['return'] = 1;
    $reponse['query'] = $post_query; // get request post
    $reponse['message']['nb_orders'] = count($tmp);
    $reponse['message']['delay'] = $delay;
    $reponse['message']['nb_orders_bloques'] = 0;
    $reponse['message']['list_orders'] = $tmp;
    $reponse['debug']['pack_limit'] = $pack_limit;
    $reponse['debug']['force'] = $message['force'];
    $reponse['debug']['produit'] = $allowedProducts;
    $reponse['debug']['no_flag'] = $message['no_flag'];
    return $reponse;
}

/**
 * Fonction qui rajoute les avis dans la bdd de la boutique
 * @param array $message contient les info de la requete envoye depuis la plateforme
 * @param string $post_query contient la requete envoye depuis la plateforme
 * @return array $reponse contient les infos sur les avis ajoutés
 */
function ntav_setProductsReviews($message, $post_query)
{
    $debug = array();
    $reviews = ntav_productReviews(
        $message
    ); // On obtient un tableau d'avis formaté selon la BDD et avec le type à appliquer NEW; UPDATE; DELETE ou AVG
    $count = 0;
    $count_update_new = 0;
    $count_average = 0;
    $count_delete = 0;


    foreach ($reviews['data'] as $data) {
        if ($data['query'] == "DELETE") {
            // Delete Review For a product.
            // PS: a product Can have Many Reviews.
            $table = 'REVIEWS';
            // where data
            $where = array('ref_product' => $data['ref_product'], 'id_product_av' => $data['id_product_av']);
            // delete rows.
            ntav_apiDelete($table, $where);
            $count++;
            $count_delete++;
        } elseif ($data['query'] == "AVG") {
            $table = 'AVERAGE';
            // where data
            // $where = array('ref_product' => $data['ref_product'], 'lang' => $message['lang_code']);
            $where = array('ref_product' => $data['ref_product']);
            // check if value exist

            $res = ntav_apiSelectExist($table, $where);

            // remove extra fields.
            unset($data['query']);
            // update or insert
            if ($res === false) {
                ntav_apiInsert($table, $data);
                $count++;
                $count_average++;
            } else {
                unset($data['id_product_av']); // remove primary key field.
                ntav_apiUpdate($table, $data, $where);
                $count++;
                $count_average++;
            }
        } else {
            $table = 'REVIEWS';
            // where data
            // $where = array('id_product_av' => $data['id_product_av'], 'lang' => $message['lang_code']);
            $where = array('id_product_av' => $data['id_product_av']);
            // check if value exist
            $res = ntav_apiSelectExist($table, $where);
            // remove extra fields.
            unset($data['query']);
            // update or insert
            if ($res === false) {
                ntav_apiInsert($table, $data);
                $count++;
                $count_update_new++;
            } else {
                unset($data['id_product_av']); // remove primary key field.
                ntav_apiUpdate($table, $data, $where);
                $count++;
                $count_update_new++;
            }
        }
    }

    if ($count != array_sum($reviews['checksum'])) {
        $debug[] = "Une erreur s'est produite. Le nombre de lignes reçues ne correspond pas au nombre de lignes traitées par l'API. Des données ont quand même pu être enregistrées";
    } else {
        $debug[] = "La synchronisation a bien eu lieu sans erreur.";
    }

    $reponse['return'] = 1;
    $reponse['query'] = $post_query; // get request post
    //$reponse['message']['lignes_recues'] = $reviews['data'];
    $reponse['message']['count_line_reviews_received'] = count($reviews['data']);
    $reponse['message']['count_line_reviews_treated'] = $count;
    $reponse['message']['nb_update_new_received'] = $reviews['checksum']['nb_new'] + $reviews['checksum']['nb_update'];
    $reponse['message']['nb_update_new_treated'] = $count_update_new;
    $reponse['message']['nb_average_received'] = $reviews['checksum']['nb_average'];
    $reponse['message']['nb_average_treated'] = $count_average;
    $reponse['message']['nb_delete_received'] = $reviews['checksum']['nb_delete'];
    $reponse['message']['nb_delete_treated'] = $count_delete;
    $reponse['debug'] = $debug;
    return $reponse;
}

/**
 * Fonction qui vide les tables reviews et average
 * @param string $post_query contient la requete envoye depuis la plateforme
 * @return array  $reponse
 */
function ntav_truncateTables($post_query)
{
    $reponse['return'] = 1;
    $reponse['debug'][] = "Tables vidées";
    $reponse['message'] = "Tables vidées";
    $reponse['debug'][] = ntav_get_truncatetables();
    $reponse['query'] = $post_query; // get request post
    return $reponse;
}

/**
 * Fonction that update flag orders into 1 or 0.
 * @param string $message contains the message we send from the API
 * @return array  $reponse
 */
function ntav_setFlag($message)
{
    $response['message'] = ntav_setFlagOrders($message);
    $response['query'] = $message['query'];
    $response['datePeriod'] = $message['datePeriod'];
    $response['return'] = (empty($response['message'])) ? 'Error' : 'Success flagging commandes a' . $message['setFlag']; //2:error, 1:success

    return $response;
}

/**
 * Fonction qui update une valeur de configuration en fonction des données passées en paramètre
 * @param string $name contient le nom de la propriete a mettre à jour
 * @param string|integer $value contient la valeur de la propriete a mettre a jour
 * @return [type]        [description]
 */
function ntav_updateConfig($name, $value, $lang = '')
{
    global $wpdb;

    $WpmlEnable = ntav_getWpmlEnable();

    if ($WpmlEnable == "yes") {
        $count = $wpdb->get_var(
            "SELECT count(*) as coun FROM " . $wpdb->prefix . "netreviews_configuration WHERE name = '$name' AND lang_code='$lang'"
        );
        if ($count > 0) {
            $wpdb->query(
                "UPDATE " . $wpdb->prefix . "netreviews_configuration SET value = '" . $value . "' WHERE name = '" . $name . "' AND lang_code='$lang'"
            );
        } else {
            $wpdb->query(
                "INSERT INTO " . $wpdb->prefix . "netreviews_configuration VALUES (NULL,'" . $name . "','" . $value . "','" . $lang . "')"
            );
        }
    } else {
        $count = $wpdb->get_var(
            "SELECT count(*) as coun FROM " . $wpdb->prefix . "netreviews_configuration WHERE name = '$name'"
        );
        if ($count > 0) {
            $wpdb->query(
                "UPDATE " . $wpdb->prefix . "netreviews_configuration SET value = '" . $value . "' WHERE name = '" . $name . "'"
            );
        } else {
            $wpdb->query(
                "INSERT INTO " . $wpdb->prefix . "netreviews_configuration VALUES (NULL,'" . $name . "','" . $value . "',NULL)"
            );
        }
    }
}

/**
 * Fonction qui recupere les info de configuration du module
 * @return array contient les info de la configuration present sur la boutique
 */
function ntav_getModuleAndSiteInfos($lang = '')
{
    global $wp_version;
    $orderStatusList = ntav_getOrderStatus();
    $WPML = ntav_getWpmlConfig();


    //$explode_secret_key = explode('-',$this->secretkey);
    $temp = array(
        'Version_Woocommerce' => ntav_getConfig('WCVERSION', 'non'),
        'Version_WP' => $wp_version,
        'Version_Module' => ntav_getConfig('MODVERSION', 'non'),
        'idWebsite' => ntav_getConfig('ID_WEBSITE', $lang),
        'Websites' => '1',
        'Id_Website_encours' => '1'
    );
    // our configuration
    $champ = array(
        'Delay' => 'DELAY',
        'Statut_choisi' => 'ORDERSTATESCHOOSEN',
        'Initialisation_du_Processus' => 'PROCESSINIT',
        'Recuperation_Avis_Produits' => 'GETPRODREVIEWS',
        'Affiche_Avis_Produits' => 'DISPLAYPRODREVIEWS',
        'Affiche_helpful_reviews' => 'HELPFULOPTION',
        'Affiche_media' => 'MEDIADISPLAY',
        'Affichage_Widget_Flottant' => 'SCRIPTFLOAT_ALLOWED',
        'Script_Widget_Flottant' => 'SCRIPTFLOAT',
        'Emails_Interdits' => 'FORBIDDEN_EMAIL',
        'Affichage_Etoiles_Listing' => 'OUR_PRODUCT_LIST_RATING',
        'Desactiver_Onglet_Woo' => 'WOOTAB_DEACTIVE',
        'Activer_Onglet_AV' => 'OUR_TAB_ACTIVE',
        'Afficher_Etoiles_Produit' => 'OUR_PRODUCT_RATING',
        'Activer_Richsnippets_Produit' => 'OUR_PRODUCT_RICHSNIP',
        'TYPE_SNIPPET' => 'TYPE_SNIPPET',
        'Activate_Complete_Snippets' => 'NEED_COMPLETE_RS'
    );

    foreach ($champ as $key => $champsname) {
        $var = ntav_getConfig($champsname, $lang);
        $var = ($var == 'NULL') ? '' : $var;
        // FIX pour NULL Variable, DATA are saved default with NULL Var
        // fix for SCRIPTFLOAT
        if ($champsname == 'SCRIPTFLOAT') {
            $temp[$key] = ntav_AV_decode_base64($var);
        } else {
            $temp[$key] = $var;
        }
    }

    $temp['Liste_des_statuts'] = $orderStatusList;
    $temp['WPML'] = $WPML;
    $temp['Date_Recuperation_Config'] = date('Y-m-d H:i:s');
    return $temp;
}

/**
 * Fonction qui recupere les différents statut de commande presents sur la boutique
 * @return array contient les différents statut de commande ou NULL
 */
function ntav_getOrderStatus()
{
    $resultat = array();
    $wc_version = ntav_getConfig('WCVERSION');

    if (function_exists('wc_get_order_statuses')) {
        $results = wc_get_order_statuses();
        foreach ($results as $key => $status) {
            $resultat[] = $status;
        }
        return $resultat;
    } else {
        global $wpdb;
        global $table_prefix;
        $query = "SELECT t.term_id AS orders_status_id,t.slug AS orders_status_name
			FROM " . $table_prefix . "terms AS t 
			LEFT JOIN " . $table_prefix . "term_taxonomy AS tt ON tt.term_id = t.term_id 
			WHERE tt.taxonomy IN ('shop_order_status')
			ORDER BY orders_status_id ASC";

        $myrows = $wpdb->get_results($query);
        if (!empty($myrows)) {
            foreach ($myrows as $res) {
                $resultat[$res->orders_status_id] = $res->orders_status_name;
            }
            return $resultat;
        }
    }
    return null;
}

/**
 * Fonction qui recuperer les languages et les idWebsites de chaque Store pour WPML
 * @return array contient les différents IDwebsites et Langages.
 */
function ntav_getWpmlConfig()
{
    global $wpdb;
    $getResults = ntav_getWpmlEnable();

    if ($getResults == "yes") {
        $results = $wpdb->get_results(
            "SELECT code  FROM " . $wpdb->prefix . "icl_languages,  " . $wpdb->prefix . "icl_flags WHERE " . $wpdb->prefix . "icl_languages.active=1 AND " . $wpdb->prefix . "icl_languages.code = " . $wpdb->prefix . "icl_flags.lang_code"
        );
        $i = 0;
        foreach ($results as $result) {
            $list_flags[$i] = $result->code;
            $idWebsite[$result->code] = ntav_getConfig('ID_WEBSITE', $result->code);
            $i++;
        }
        $configWpml = array('WPMLACTIVE' => $getResults, 'LANGUAGES' => $list_flags, 'IDWEBSITE' => $idWebsite);

        return $configWpml;
    } else {
        return $getResults;
    }
}

/**
 * Fonction qui renvoi l'empreinte sha1 en fonction des données en bdd
 * @return string empreinte sha1
 */
function ntav_get_sha1()
{
    $id_website = ntav_getConfig('ID_WEBSITE');
    $secret_key = ntav_getConfig('SECRET_KEY');
    return $SHA1 = SHA1($_POST['query'] . $id_website . $secret_key);
}

/**
 * Fonction qui retourne les id des statuts de commandes envoyés en parametre
 * @param array $statusChoosen liste des id de statut de commande
 * @return null|string $resultat contient les termes des statuts selectionnés
 */
function ntav_get_status($statusChoosen)
{
    $wc_version = ntav_getConfig('WCVERSION');
    if (function_exists('wc_get_order_statuses')) {
        $results = wc_get_order_statuses();
        foreach ($results as $key => $status) {
            $orderStatusList[] = $status;
        }
        $tab_id_status = explode(",", $statusChoosen);
        foreach ($tab_id_status as $index) {
            $tab_final[] = $orderStatusList[array_search($index, $orderStatusList)];
        }
        $resultat = implode(",", $tab_final);
        return $resultat;
    } else {
        global $wpdb;
        global $table_prefix;
        $query = "SELECT slug FROM " . $table_prefix . "terms WHERE term_id IN (" . $statusChoosen . ")";

        $myrows = $wpdb->get_results($query);
        $row = array();
        if (!empty($myrows)) {
            foreach ($myrows as $res) {
                $row[] = $res->slug;
            }
            $resultat = implode(",", $row);
            return $resultat;
        }
    }
    return null;
}

/**
 * Fonction qui recupere les donnees de commande
 * @param array $config contient les paremetres de recuperation de commande
 * @return array  $resultat  contient une liste de commande ou null
 */
function ntav_getDataOrders(array $config)
{
    global $wpdb;
    global $table_prefix;


    if (isset($config['from']) && isset($config['to'])) {
        $from = $config['from'];
        $to = $config['to'];
    } else {
        $from = null;
        $to = null;
    }
    if (isset($config['flag'])) {
        $flag = 0;
    }

    $includeProducts = false;
    if ($config['includeProducts'] == 'yes') {
        $includeProducts = true;
    }

    if (isset($config['status'])) {
        if (function_exists('wc_get_order_statuses')) {
            $results = wc_get_order_statuses();
            foreach ($results as $key => $status) {
                $orderStatusList[] = $key;
            }
            $tab_id_status = explode(",", $config['status']);
            $tab_final = array();
            foreach ($tab_id_status as $index) {
                if (!empty($index)) {
                    $tab_final[] = "'" . $orderStatusList[$index] . "'";
                }
            }
            $whereStatusChosen = implode(",", $tab_final);
        } else {
            $whereStatusChosen = $config['status'];
        }
    }

    $myrows = ntav_getOrdersAPIByWooCommerceVersion($whereStatusChosen, $from, $to, $flag);

    $resultat = array();
    if (!empty($myrows)) {
        foreach ($myrows as $res) {
            $resultat[] = array(
                'refcommande' => $res->refcommande,
                'date_modified' => $res->date_modified,
                'amount_order' => ($res->amount_order * 100),
                'timestamp' => $res->timestamp,
                'status_order' => str_replace('wc-', '', $res->status_order),
                'datecommande' => $res->datecommande,
                'product_id' => ($includeProducts) ? $res->product_id : '',
                'id_customer' => $res->id_customer,
                'email' => $res->email,
                'nom' => $res->custom_last_name,
                'prenom' => $res->custom_first_name,
                'product_name' => ($includeProducts) ? $res->product_name : '',
                'url' => ($includeProducts) ? get_post_permalink($res->product_id) : '',
                'url_image' => ($includeProducts) ? ntav_get_image_url(get_the_post_thumbnail($res->product_id)) : '',
                'delaiavis' => $res->delaiavis,
                'lang' => $res->lang
            );
        }
        return $resultat;
    }
    return null;
}

/**
 * Fonction qui met à jour le flag d'une commande
 * @param array $ids contient des id de commande a mettre a jour
 * @return void [type] [description]
 */
function ntav_updateFlag(array $ids)
{
    global $wpdb;
    global $table_prefix;
    $time = time();
    $where = implode(',', $ids);
    $query_flag = "UPDATE " . $table_prefix . "postmeta SET meta_value = '1' WHERE meta_key = 'av_flag' AND post_id IN (" . $where . ")";
    $query_date = "UPDATE " . $table_prefix . "postmeta SET meta_value = '" . $time . "' WHERE meta_key = 'av_horodate' AND post_id IN (" . $where . ")";

    $wpdb->query($query_flag);
    $wpdb->query($query_date);
}

/**
 * Fonction qui va ajouter ou mettre a jour ou supprimer les avis en fonction des parametres qui lui sont passés
 * @param array $message contient les info de la requete envoyés depuis la plateforme
 * @return array $tmp contient les avis sur lesquels il y a une action a entreprendre
 */
function ntav_productReviews($message)
{
    // if null;
    $msg = $message['data'];
    $lang = $message['lang_code'];
    if (!isset($msg)) {
        return array();
    }

    $reviewsArray = json_decode($message['data'], true);

    $tmp_new = ntav_sortReviewsArray($reviewsArray, 'NEW', 'ntav_column_review', 'nb_new', $lang);
    $tmp_update = ntav_sortReviewsArray($reviewsArray, 'UPDATE', 'ntav_column_review', 'nb_update', $lang);
    $tmp_delete = ntav_sortReviewsArray($reviewsArray, 'DELETE', 'ntav_column_review_delete', 'nb_delete', $lang);
    $tmp_avg = ntav_sortReviewsArray($reviewsArray, 'AVG', 'ntav_column_average', 'nb_average', $lang);

    $data = array_merge($tmp_new['data'], $tmp_update['data'], $tmp_delete['data'], $tmp_avg['data']);
    $tmp['data'] = $data;
    $tmp['checksum']['nb_new'] = $tmp_new['checksum']['nb_new'] + $tmp_update['checksum']['nb_new'] + $tmp_delete['checksum']['nb_new'] + $tmp_avg['checksum']['nb_new'];
    $tmp['checksum']['nb_update'] = $tmp_new['checksum']['nb_update'] + $tmp_update['checksum']['nb_update'] + $tmp_delete['checksum']['nb_update'] + $tmp_avg['checksum']['nb_update'];
    $tmp['checksum']['nb_delete'] = $tmp_new['checksum']['nb_delete'] + $tmp_update['checksum']['nb_delete'] + $tmp_delete['checksum']['nb_delete'] + $tmp_avg['checksum']['nb_delete'];
    $tmp['checksum']['nb_average'] = $tmp_new['checksum']['nb_average'] + $tmp_update['checksum']['nb_average'] + $tmp_delete['checksum']['nb_average'] + $tmp_avg['checksum']['nb_average'];

    return $tmp;
}

/**
 * Fonction qui va passer dans un tableau les avis en fonction des parametres qui lui sont passés
 * @param $reviewsArray tableau d'avis envoyés depuis la plateforme
 * @param $type trie selon NEW, UPDATE, DELETE ou AVG
 * @param $function définit la fonction à utiliser selon le type
 * @param $checksum_type précise le type de check à réaliser
 * @param $lang langue envoyée dans le message de la plateforme
 * @return array $tmp contient les avis sur lesquels il y a une action a entreprendre
 */
function ntav_sortReviewsArray($reviewsArray, $type, $function, $checksum_type, $lang)
{
    $tmp = [
        'data' => [],
        'checksum' => [
            'nb_new' => 0,
            'nb_update' => 0,
            'nb_delete' => 0,
            'nb_average' => 0
        ]
    ];
    if (!empty($reviewsArray[$type])) {
        foreach ($reviewsArray[$type] as $review) {
            if ($function == 'ntav_column_review') {
                $data = ntav_column_review($review);
            } elseif ($function == 'ntav_column_average') {
                $data = ntav_column_average($review);
            } elseif ($function == 'ntav_column_review_delete') {
                $data = ntav_column_review_delete($review);
            }
            $tmp['data'][] = array_merge($data, array('website_id' => bin2hex(random_bytes(12)), 'lang' => $lang));
            $tmp['checksum'][$checksum_type]++;
        }
    }
    return $tmp;
}

/**
 * Fonction qui update en base les avis ou les moyennes en fonction des data passe en parametre
 * @param string $table contient le nom de la table sur laquelle executer la requete
 * @param array $value contient les donnes a mettre a jour
 * @param array $where contient les filtres pour l'update
 * @return void [type] [description]
 */
function ntav_apiUpdate($table, array $value, array $where)
{
    global $wpdb;

    if ($table == 'AVERAGE') {
        $query = "UPDATE " . $wpdb->prefix . "netreviews_products_average SET " . ntav_prepareData(
            $value
        ) . " WHERE " . ntav_prepareDataWhere($where) . "";
    } else {
        $query = "UPDATE " . $wpdb->prefix . "netreviews_products_reviews SET " . ntav_prepareData(
            $value
        ) . " WHERE " . ntav_prepareDataWhere($where) . "";
    }

    $wpdb->query($query);
}

/**
 * Fonction qui ajoute en base les avis ou les moyennes en fonction des data passées en parametre
 * @param string $table contient le nom de la table sur laquelle executer la requete
 * @param array $value contient les donnes a mettre a jour
 * @return void [type]        [description]
 */
function ntav_apiInsert($table, array $value)
{
    global $wpdb;

    if ($table == 'AVERAGE') {
        $query = "INSERT INTO " . $wpdb->prefix . "netreviews_products_average SET " . ntav_prepareData($value) . "";
    } else {
        $query = "INSERT INTO " . $wpdb->prefix . "netreviews_products_reviews SET " . ntav_prepareData($value) . "";
    }


    $wpdb->query($query);
}

/**
 * Fonction qui verifie si les avis ou les moyennes passés en parametre existent en base
 * @param string $table contient le nom de la table sur laquelle executer la requete
 * @param array $where contient la condition where de la requete pour filtrer sur les avis ou les moyennes
 * @return boolean  Si des avis ou myennes existe cela retournera true si il n'existe pas de ligne cela retournera false
 */
function ntav_apiSelectExist($table, array $where)
{
    global $wpdb;
    if ($table == 'AVERAGE') {
        $query = "SELECT ref_product FROM " . $wpdb->prefix . "netreviews_products_average WHERE " . ntav_prepareDataWhere(
            $where
        ) . "";
    } else {
        $query = "SELECT id_product_av FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE " . ntav_prepareDataWhere(
            $where
        ) . "";
    }

    $myrows = $wpdb->get_results($query);
    if (!empty($myrows)) {
        // Exist then empty() -> false,
        // cz we are checking if it exist or not
        // we return !empty()
        if ($table == 'AVERAGE') {
            return !empty($myrows[0]->ref_product);
        } else {
            return !empty($myrows[0]->id_product_av);
        }
    }
    return false;
}

/**
 * Fonction qui supprime en base les avis ou les moyennes en fonction des data passés en parametre
 * @param string $table contient le nom de la table sur laquelle executer la requete
 * @param array $where contient la condition where de la requete pour filtrer sur les avis ou les moyennes
 * @return void [type]        [description]
 */
function ntav_apiDelete($table, array $where)
{
    global $wpdb;

    if ($table == 'AVERAGE') {
        $query = "DELETE FROM " . $wpdb->prefix . "netreviews_products_average WHERE " . ntav_prepareDataWhere(
            $where
        ) . "";
    } else {
        $query = "DELETE FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE " . ntav_prepareDataWhere(
            $where
        ) . "";
    }

    $wpdb->query($query);
}

/**
 * Fonction qui formate les donnees passe en parametre de facon pour executer une requete sql et transforme ou enlève les champs en trop pour calquer avec la bdd
 * @param array $value contient l'avis
 * @return string $query contient les donnes formate pour executer une requete
 */
function ntav_prepareData(array $value)
{
    $query = '';
    foreach ($value as $index => $value) {
        if (is_array($value)) {
            $value = base64_encode(json_encode($value));
        }
        $query = ($query != '') ? $query . ' , ' : $query;
        $query .= $index . ' = "' . $value . '"';
    }
    return $query;
}

/**
 * Fonction qui formate la condition where en fonction des donnes passe en parametre
 * @param array $where contient les info a formate
 * @return string $query contient la condition where formate pour executer une requete
 */
function ntav_prepareDataWhere(array $where)
{
    $query = '';
    foreach ($where as $index => $value) {
        $query = ($query != '') ? $query . ' AND ' : $query;
        if ($index == 'lang' && $value == null) {
            $query .= "(" . $index . " IS NULL OR lang = '') ";
        } else {
            $query .= $index . ' = "' . $value . '" ';
        }
    }
    return $query;
}

/**
 * Fonction qui map les données passées en parametre dans un tableau indexé
 * @param $review tableau contenant les donnees liées à un avis
 * @return array tableau contenant les infos d'un avis mappé
 */
function ntav_column_review($review)
{
    // Clean lastname
    if (!empty($review['name'])) {
        $review['name'] = urlencode($review['name']);
        $review['name'] = str_replace("%22", "", $review['name']); // Delete quotation marks.
        $review['name'] = urldecode($review['name']);
        $review['name'] = strtolower($review['name']);
        $review['name'] = ucwords($review['name']);
    }

    // Clean customer_name
    if (!empty($review['name'])) {
        $customer_name = urlencode(
            ucfirst(($review['prenom'])) . " " . ucfirst($review['name'][0]) . "."
        ); // $column[8][0] first letter
    } else {
        $customer_name = urlencode($review['prenom']);
    }

    // Clean order_date
    if (isset($review['horodateCommande']) && !empty($review['horodateCommande'])) {
        $date_order = $review['horodateCommande'];
    } elseif (isset($review['order_date']) && !empty($review['order_date'])) {
        $date_order = $review['order_date'];
    }

    return array(
        'query' => 'NEW',
        'id_product_av' => $review['idProduit'],
        'ref_product' => $review['refProduit'],
        'rate' => $review['rate'],
        'review' => ($review['avis']),
        'horodate' => $review['horodateAvis'],
        'customer_name' => $customer_name,
        'order_date' => !empty($date_order) ? $date_order : 0,
        'helpfulYes' => !empty($review['count_helpful_yes']) ? $review['count_helpful_yes'] : 0,
        'helpfulNo' => !empty($review['count_helpful_no']) ? $review['count_helpful_no'] : 0,
        'media_full' => !empty($review['media_full']) ? $review['media_full'] : 0,
        'discussion' => !empty($review['moderation']) ? $review['moderation'] : 0,
    );
}

/**
 * Fonction qui map les données passées en parametre dans un tableau indexé
 * @param $review tableau contenant les donnees liées à un avis
 * @return void tableau contenant les infos d'un avis mappé
 */
function ntav_column_average($review)
{
    return array(
        'query' => 'AVG',
        'id_product_av' => $review['idProduit'],
        'ref_product' => $review['refProduit'],
        'rate' => $review['averageProduit'],
        'nb_reviews' => $review['nbAvisProduit'],
        'horodate_update' => time()
    );
}

/**
 * Fonction qui map les données passées en parametre dans un tableau indexé
 * @param $review tableau contenant les donnees liées à un avis
 * @return void tableau contenant les infos d'un avis mappé
 */
function ntav_column_review_delete($review)
{
    return array(
        'query' => 'DELETE',
        'id_product_av' => $review['idProduit'],
        'ref_product' => $review['refProduit']
    );
}

/**
 * Fonction qui execute la requete pour vide les tables reviews et average
 * @return int|string retourne 1 si la requete c'est bien passe ou un message d'erreur si une erreur est survenue
 */
function ntav_get_truncatetables()
{
    global $wpdb;

    $query_reviews = "TRUNCATE TABLE " . $wpdb->prefix . "netreviews_products_reviews";
    $query_average = "TRUNCATE TABLE " . $wpdb->prefix . "netreviews_products_average";

    if ($wpdb->query($query_reviews) == true && $wpdb->query($query_average) == true) {
        return 1; // ok
    } else {
        return 'Erreur tables non vidées.'; // erreur
    }
}

// /**
//  * Fonction that update av_flag to 1 or 0 depending the message we receive from API
//  * @return positive or negative response.
//  */
function ntav_setFlagOrders($message)
{
    global $wpdb;
    global $table_prefix;
    if ($message['datePeriod'] == 'periodOrders') {
        $updateAvflag = "UPDATE " . $table_prefix . "postmeta AS WPM LEFT JOIN " . $table_prefix . "posts AS WP ON WPM.post_id = WP.ID SET WPM.meta_value = " . $message['setFlag'] . " WHERE WPM.meta_key ='av_flag' AND WP.post_type = 'shop_order' AND WP.post_date BETWEEN '" . $message['startDate'] . "' and '" . $message['endDate'] . "'";

        $updateAvhorodate = "UPDATE " . $table_prefix . "postmeta AS WPM LEFT JOIN " . $table_prefix . "posts AS WP ON WPM.post_id = WP.ID SET WPM.meta_value = '' WHERE WPM.meta_key ='av_horodate' AND WP.post_type = 'shop_order' AND WP.post_date between '" . $message['startDate'] . "' and '" . $message['endDate'] . "'";
    } else {
        $updateAvflag = "UPDATE " . $table_prefix . "postmeta AS WPM LEFT JOIN " . $table_prefix . "posts AS WP ON WPM.post_id = WP.ID SET WPM.meta_value = " . $message['setFlag'] . " WHERE WPM.meta_key ='av_flag' AND WP.post_type = 'shop_order'";

        $updateAvhorodate = "UPDATE " . $table_prefix . "postmeta AS WPM LEFT JOIN " . $table_prefix . "posts AS WP ON WPM.post_id = WP.ID SET WPM.meta_value = '' WHERE WPM.meta_key ='av_horodate' AND WP.post_type = 'shop_order'";
    }
    $results0 = $wpdb->query($updateAvflag);
    $results1 = $wpdb->query($updateAvhorodate);


    if ($results0 > 0) {
        return 'Success. Numero de commandes flaggees:' . $results0;
    } elseif ($results0 == 0) {
        return 'Success. Numero de commandes flaggees:' . $results0;
    } else {
        return 'Erreur dans les requetes';
    }

    return 'Erreur dans la Function'; // erreur
}
