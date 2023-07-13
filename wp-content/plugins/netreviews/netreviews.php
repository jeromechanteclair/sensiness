<?php

/**
 * Plugin Name: Netreviews
 * Plugin URI: http://www.avis-verifies.com/eCommerce/woocommerce/netreviews.zip
 * Description: We provide you with a solution that enables you to collect customer reviews about your website and products which will show on your
 * website and on a attestation which will increase the credibility of published reviews.
 * Version: 2.3.14
 * Author: NetReviews SAS <contact@avis-verifies.com>
 * Author URI: www.avis-verifies.com
 * Text Domain: avis-verifies
 * Copyright: © 2020 Netreviews SAS
 * Domain Path: /languages/
 */

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

$CONFIG_NETREVIEWS = array();

define('AVNT_PATH', plugin_dir_path(__FILE__));

require_once dirname(__FILE__) . '/functions.php';

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////      INSTALLATION | ACTIVATION   //////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Function handling API/AJAX call
 * Our endpoint looks like : website.com/?netreviews-plugin=[param]
 * When calling it, we check weither its an API call or an AJAX call, then we process the request
 */
function ntav_netreviews_parse_request()
{
    // We need to initialize $id_product for that first Ajax call in the NetReviews tab,
    // and pass it as a parameter to appelAjax function
    $id_product = '';

    if (isset($_GET['netreviews-plugin'])) {
        switch ($_GET['netreviews-plugin']) {
            case 'api':
                appelApi();
                die;
                break;
            case 'ajax':
                appelAjax($id_product);

                die;
                break;
            default:
                break;
        }
    }
}

add_action('parse_request', 'ntav_netreviews_parse_request');


/**
 * API call
 * Making API logic and echoing a string
 */
function appelApi()
{
    include_once 'api_functions.php';

    $POST_DATA = $_POST;

    if (!isset($POST_DATA) or empty($POST_DATA)) {
        $reponse['debug'] = "Aucunes variables POST reçues";
        $reponse['return'] = 2; //A definir
        $reponse['query'] = (isset($POST_DATA['query'])) ? $POST_DATA['query'] : null;

        echo '#netreviews-start#' . ntav_AV_encode_base64(json_encode($reponse)) . '#netreviews-end#';
        exit;
    }


    if (isset($POST_DATA['message'])) {
        $message = json_decode(ntav_AV_decode_base64($POST_DATA['message']), true);
    }
    $WpmlEnable = ntav_getWpmlEnable();

    if ($WpmlEnable == 'yes' && empty($message['lang_code'])) {
        $message['lang_code'] = apply_filters('wpml_current_language', null);
    } elseif (($WpmlEnable == 'no') && empty($message['lang_code'])) {
        $message['lang_code'] = null;
    }

    $isActiveReply = ntav_isActiveModule();
    if ($isActiveReply['return'] != 1) {
        echo '#netreviews-start#' . ntav_AV_encode_base64(json_encode($isActiveReply)) . '#netreviews-end#';
        exit;
    }

    $checkReply = ntav_check_data($message, $POST_DATA['query']);
    if ($checkReply['return'] != 1) {
        echo '#netreviews-start#' . ntav_AV_encode_base64(json_encode($checkReply)) . '#netreviews-end#';
        exit;
    }

    switch ($POST_DATA['query']) {
        case 'isActiveModule':
            $reply = ntav_isActiveModule();
            break;
        case 'setModuleConfiguration':
            $reply = ntav_setModuleConfiguration($message);
            break;
        case 'getModuleAndSiteConfiguration':
            $reply = ntav_getModuleAndSiteConfiguration($message);
            break;
        case 'getOrders':
            $reply = ntav_getOrders($message, $POST_DATA['query']);
            break;
        /*case 'getUrlProducts': //récupération url produit et url image (pas encore décidé).
            $reply = getUrlProducts($message);
            break;*/
        case 'setProductsReviews':
            $reply = ntav_setProductsReviews($message, $POST_DATA['query']);
            break;
        case 'truncateTables':
            $reply = ntav_truncateTables($POST_DATA['query']);
            break;
        case 'setFlag':
            $reply = ntav_setFlag($message);
            break;
        default:
            break;
    }

    echo '#netreviews-start#' . ntav_AV_encode_base64(json_encode($reply)) . '#netreviews-end#';
}


/**
 * Ajax call
 *
 * @param  $id_product
 * @return string
 */
function appelAjax($id_product, $lang = '')
{
    // On initialise quelques données
    $tri_avis = '';
    $htmltoreturn = '';

    // Si on charge les avis en arrivant sur la page
    if (isset($id_product) && $id_product != null) {
        $id_page = 1;
    } // Sinon si on passe par le JS on a besoin de recup product_id et pagination ou tri_avis
    elseif (($_GET['product_id'] && $_GET['pagination']) || (!empty($_GET['tri_avis']) && !empty($_GET['product_id']))) {
        $id_product = (int)$_GET['product_id'];
        $id_page = (int)$_GET['pagination'];
        $tri_avis = $_GET['tri_avis'];
        $lang = $_GET['nrLang'];
        if (empty($id_page)) {
            $id_page = 1;
        }
    } else {
        exit;
    }

    $lang = escape($lang);

    // Switch sur l'ordre d'affichage des avis
    // Par defaut on récupère les avis triés par ordre chronologique (plus récent au plus ancien)
    switch ($tri_avis) {
        case "newest":
            $reviews = ntav_get_newReviews($id_product, $id_page, $lang);
            break;
        case "oldest":
            $reviews = ntav_get_oldReviews($id_product, $id_page, $lang);
            break;
        case "highest":
            $reviews = ntav_get_bestReviews($id_product, $id_page, $lang);
            break;
        case "lowest":
            $reviews = ntav_get_worstReviews($id_product, $id_page, $lang);
            break;
        case "most_useful":
            $reviews = ntav_get_helpfulReviews($id_product, $id_page, $lang);
            break;
        case "rate":
            $rate = (int)$_GET['rate'];
            $reviews = ntav_sortBy_Rate($id_product, $id_page, $rate, $lang);
            break;
        default:
            $reviews = ntav_get_newReviews($id_product, $id_page, $lang);
            break;
    }

    $my_current_lang = $lang;

    if (isset($reviews) && !empty($reviews)) {
        $return = include_once dirname(__file__) . '/includes/review_Pagination.php';
        if ($id_page > 1 || $tri_avis != '') {
            echo $return;
        }
        return $return;
    } else {
        return '';
    }
}

// ACTIVATION HOOK
register_activation_hook(__FILE__, 'ntav_netreviews_plugin_activate');
function ntav_netreviews_plugin_activate()
{
    global $wp_version;
    ntav_install_db_av();
    if (version_compare($wp_version, '3.5', '<')) {
        echo '<!-- Ne marche pas pour cette version -->';
    }
}

// UNINSTALL HOOK
register_uninstall_hook(__FILE__, 'ntav_netreviews_plugin_uninstall');
function ntav_netreviews_plugin_uninstall()
{
    ntav_uninstall_db_av();
}

// Activer la traduction.
add_action('plugins_loaded', 'ntav_av_plugin_load_textdomain');
function ntav_av_plugin_load_textdomain()
{
    load_plugin_textdomain('av', false, dirname(plugin_basename(__FILE__)) . '/i18n/languages/');
}

// Enregistrer la version du module en bdd.
add_action('wp_footer', 'ntav_update_version_plugin');

//  Ajout onglet av dans le menu admin
add_action('admin_menu', 'ntav_av_plugin_backoffice');
function ntav_av_plugin_backoffice()
{
    // On ajoute un lien dans le menu
    add_menu_page(
        __('Verified Reviews', 'av'),
        __('Verified Reviews', 'av'),
        'manage_options',
        'av',
        'ntav_av_backoffice',
        'http://www.avis-verifies.com/favicon.ico',
        50
    );

    // Fonction qui affiche le backoffice
    function ntav_av_backoffice()
    {
        include_once dirname(__file__) . '/includes/av_backoffice.php';
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////      CSS STARS     ////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
add_action('wp_footer', 'ntav_netreviews_css');
function ntav_netreviews_css()
{
    wp_enqueue_style("av_css_stars", plugins_url('/includes/css/netreviews-style-2017.css', __FILE__));
}


//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////      CSS SPECIFIC    ////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
add_action('wp_footer', 'add_specific_style');
function add_specific_style()
{
    echo "<style>" . ntav_getConfig('SPECIFIC_STYLE') . "</style>";
}


//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////      LISTE PRODUIT     ////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////

// add_action('woocommerce_after_shop_loop_item_title', 'ntav_netreviews_loop_rating', 31);
add_action('netreviews_category_rating', 'ntav_netreviews_loop_rating');
add_shortcode('netreviews_loop_product_rating', 'ntav_netreviews_loop_rating_short_code');

//Fonction ajoutant les étoiles dans les catégories
function ntav_netreviews_loop_rating()
{
    $content = ntav_getContentForProductLoopRating();
    echo $content;
}

function ntav_netreviews_loop_rating_short_code()
{
    $content = ntav_getContentForProductLoopRating();
    return $content;
}

function ntav_getContentForProductLoopRating()
{
    global $product;
    $WpmlEnable = ntav_getWpmlEnable();

    // Si on recupere le produit et que le module est actif
    if (isset($product) && ntav_getConfig('IS_ACTIVE', 'non') == 1) {
        $id_product = $product->get_id();
        if ($WpmlEnable == 'yes') {
            $my_current_lang = apply_filters('wpml_current_language', null);
        } else {
            $my_current_lang = '';
        }
        $count = ntav_get_netreviews_count($id_product, $my_current_lang);
        $starsCategory = ntav_getConfig('STARS_CATEGORY_PAGE', 'non');
        if (!isset($starsCategory)) {
            $starsCategory = 'yes';
        }

        // recuperation de la moyenne d'avis
        $average = ntav_get_netreviews_average($id_product, $my_current_lang);
        $stars = ntav_addStars($average);

        // calcul pour gerer le remplissage des etoiles
        if (!empty($average) && $starsCategory == 'yes') {
            // Si la moyenne est supérieur à 0 (cas impossible), que le module est actif et que l'affichage des etoiles dans les categories est activé
            if (
                $average > 0 && ntav_getConfig('IS_ACTIVE', 'non') == 1 && ntav_getConfig(
                    'OUR_PRODUCT_LIST_RATING',
                    $my_current_lang
                ) == 1
            ) {
                $html = '<div class="netreviews_bg_stars_big listStars" title="' . $average . '/5">';
                $html .= $stars;
                $html .= ' <span class="reviewCount">' . $count;
                $html .= ' ' . __('review(s)', 'av');
                $html .= '</span>';
                $html .= '</div>';
                return $html;
            }
        }
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////      FICHE PRODUIT     ////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//hook personnalisé pour afficher étoile dans les fiches produit
add_action('netreviews_product_rating', 'ntav_netreviews_product_rating');
add_action('woocommerce_single_product_summary', 'ntav_netreviews_product_rating', 31);
add_shortcode('netreviews_product_rating', 'ntav_netreviews_product_rating_short_code');

$WpmlEnable = ntav_getWpmlEnable();

if ($WpmlEnable == 'yes') {
    $my_current_lang = apply_filters('wpml_current_language', null);
} else {
    $my_current_lang = '';
}

// fonction affichant les étoiles dans la description rapide et rajoute des rich snippet produit si c'est configure (activation rich snippet et microdata selectionne)
function ntav_netreviews_product_rating()
{
    $content = ntav_getContentForProductRating();
    echo $content;
}

function ntav_netreviews_product_rating_short_code()
{
    $content = ntav_getContentForProductRating();
    return $content;
}

function ntav_getContentForProductRating()
{
    global $product;
    global $sitepress;

    $WpmlEnable = ntav_getWpmlEnable();

    if (isset($product) && ntav_getConfig('IS_ACTIVE', 'non') == 1) {
        $id_product = $product->get_id();

        if ($WpmlEnable == 'yes') {
            //$id_product = icl_object_id( get_the_id(), 'post', true, $sitepress->get_default_language() ); //Will display the ID of the original post for WPML plugin
            $my_current_lang = apply_filters('wpml_current_language', null);
        } else {
            $my_current_lang = '';
        }

        $id = get_permalink($product->get_id());
        $count = ntav_get_netreviews_count($id_product, $my_current_lang);
        $average = ntav_get_netreviews_average($id_product, $my_current_lang);
        $stars = ntav_addStars($average);
        $reviews = ntav_getReviewsRS($id_product);
        $designStars = ntav_getConfig('DESIGN_PRODUCT_PAGE', 'non');
        $starsColour = ntav_getConfig('COLOUR_STARS_AV', 'non');

        if (!isset($designStars)) {
            $designStars = '1';
        }

        if (!empty($average)) {
            // Si il y a des avis et qu'on decide de les afficher
            if ($count > 0 && ntav_getConfig('OUR_PRODUCT_RATING', $my_current_lang) == 1) {
                // Si on a activé les richsnippets microdata
                if (
                    ntav_getConfig('OUR_PRODUCT_RICHSNIP', $my_current_lang) == 1 && ntav_getConfig(
                        'TYPE_SNIPPET',
                        $my_current_lang
                    ) == 'microdata'
                ) {
                    if (ntav_getConfig('NEED_COMPLETE_RS', $my_current_lang) == 0) {
                        //Depending the design of our Badge we display one code or another.
                        if ($designStars == 1) {
                            $html = '<div class="woocommerce-product-rating netreviews-product-rating">';
                            $html .= '<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">';
                            $html .= '<div class="netreviews_bg_stars_big headerStars" title="' . round(
                                $average,
                                1
                            ) . '/5">';
                            $html .= $stars;
                            $html .= '</div>';
                            $html .= '<a href="javascript:showNetreviewsTab();" class="woocommerce-review-link" rel="nofollow">';
                            $html .= ' <span itemprop="reviewCount">' . $count . '</span> ' . __('review(s)', 'av');
                            $html .= '</a>';
                            $html .= ' <meta itemprop="ratingValue" content="' . $average . '"/>';
                            $html .= ' <meta itemprop="worstRating" content="1"/>';
                            $html .= ' <meta itemprop="bestRating" content="5"/>';
                            $html .= '</span>';
                            $html .= '<div class="netreviewsclear"></div>';
                            $html .= '</div>';
                            return $html;
                        } else {
                            return include_once dirname(__FILE__) . '/includes/starsProduct.php';
                        }
                    } else {
                        //Depending the design of our Badge we display one code or another.
                        if ($designStars == 1) {
                            $html = '<div class="woocommerce-product-rating netreviews-product-rating">';
                            $html .= '<div class="netreviews_bg_stars_big headerStars" title="' . round(
                                $average,
                                1
                            ) . '/5">';
                            $html .= $stars;
                            $html .= '</div>';
                            $html .= '<a href="javascript:showNetreviewsTab();" class="woocommerce-review-link" rel="nofollow">';
                            $html .= ' <span>' . $count . '</span> ' . __('review(s)', 'av');
                            $html .= '</a>';
                            $html .= '<div class="netreviewsclear"></div>';
                            $html .= '</div>';
                            return $html;
                        } else {
                            return include_once dirname(__FILE__) . '/includes/starsProduct.php';
                        }
                    }
                } // Sinon on affiche les etoiles sans microdata
                elseif (
                    ntav_getConfig('OUR_PRODUCT_RICHSNIP', $my_current_lang) == 1 && ntav_getConfig(
                        'TYPE_SNIPPET',
                        $my_current_lang
                    ) != 'microdata'
                ) {
                    if ($designStars == 1) {
                        $html = '<div class="woocommerce-product-rating netreviews-product-rating">';
                        $html .= '<span>';
                        $html .= '<div class="netreviews_bg_stars_big headerStars" title="' . round(
                            $average,
                            1
                        ) . '/5">';
                        $html .= $stars;
                        $html .= '</div>';
                        $html .= '<a href="javascript:showNetreviewsTab();" class="woocommerce-review-link" rel="nofollow">';
                        $html .= ' <span>' . $count . '</span> ' . __('review(s)', 'av');
                        $html .= '</a>';
                        $html .= '</span>';
                        $html .= '<div class="netreviewsclear"></div>';
                        $html .= '</div>';
                        return $html;
                    } else {
                        return include_once dirname(__FILE__) . '/includes/starsProduct.php';
                    }
                } //In case Rich Snippets disable in Platform configuration.
                else {
                    if ($designStars == 1) {
                        $html = '<div class="woocommerce-product-rating netreviews-product-rating">';
                        $html .= '<span>';
                        $html .= '<div class="netreviews_bg_stars_big headerStars" title="' . round(
                            $average,
                            1
                        ) . '/5">';
                        $html .= $stars;
                        $html .= '</div>';
                        $html .= '<a href="javascript:showNetreviewsTab();" class="woocommerce-review-link" rel="nofollow">';
                        $html .= ' <span>' . $count . '</span> ' . __('review(s)', 'av');
                        $html .= '</a>';
                        $html .= '</span>';
                        $html .= '<div class="netreviewsclear"></div>';
                        $html .= '</div>';
                        return $html;
                    } else {
                        return include_once dirname(__FILE__) . '/includes/starsProduct.php';
                    }
                }
            }
        }
    }
}

// fonction rajoutant les rich snippet produit sous format json ld
function ntav_netreviews_jsonld_product($product = null)
{
    global $sitepress;

    $WpmlEnable = ntav_getWpmlEnable();

    if (!is_object($product)) {
        global $product;
    }
    if (!empty($product) && (is_object($product))) {
        $id_product = $product->get_id();
        if ($WpmlEnable == 'yes') {
            $my_current_lang = apply_filters('wpml_current_language', null);
            $id_product = apply_filters( 'wpml_object_id', $id_product, 'post', true, $my_current_lang ); //Will display the ID of the original post for WPML plugin
        } else {
            $my_current_lang = '';
        }
        $id = get_permalink($id_product);
        $count = ntav_get_netreviews_count($id_product, $my_current_lang);
        $average = ntav_get_netreviews_average($id_product, $my_current_lang);
        //$reviews = ntav_getReviewsRS($id_product);

        $i = 0;
        $markup = '';
        $wc_version = WC_VERSION;
        $wc_version_as_int = (int)str_replace(".", "", $wc_version);

        if ($count > 0 && $average > 0) {
            $markup = '<script type="application/ld+json">';
            $markup .= '{';
            $markup .= '"@context":"http:\/\/schema.org\/",';

            //Modification de l'id selon la version de WooCommerce pour suffixer l'id
            if ($wc_version_as_int >= 355) {
                $markup .= '"@id":"' . $id . '#product",';
            } else {
                $markup .= '"@id":"' . $id . '",';
            }

            $markup .= '"aggregateRating":';
            $markup .= '{';
            $markup .= '"@type":"AggregateRating",';
            $markup .= '"ratingValue":"' . $average . '",';
            $markup .= '"ratingCount":"' . $count . '",';
            $markup .= '"bestRating":"5",';
            $markup .= '"worstRating":"1"';
            $markup .= '}';
            $markup .= '}';
            $markup .= '</script>';
        }

        echo $markup;
    }
}

//si notre module actif
if (ntav_getConfig('WOOTAB_DEACTIVE', $my_current_lang) == 1) {
    add_filter('woocommerce_product_tabs', 'ntav_av_remove_tab', 98);
}

/**
 * fonction qui supprime l'onglet avis natif woocommerce
 *
 * @param  $tabs tableau contenant les onglets présent sur la fiche produit
 * @return mixed
 */
function ntav_av_remove_tab($tabs)
{
    unset($tabs['reviews']);
    return $tabs;
}

//si notre module actif
if (ntav_getConfig('OUR_TAB_ACTIVE', $my_current_lang) == 1 && ntav_getConfig('IS_ACTIVE', 'non') == 1) {
    add_filter('woocommerce_product_tabs', 'ntav_add_our_review_tab', 98);
}

// Ajout du block d'avis dans l'onglet
function ntav_add_our_review_tab($tabs)
{
    global $product;
    $WpmlEnable = ntav_getWpmlEnable();
    if (isset($product) && ntav_getConfig('IS_ACTIVE', 'non') == 1) {
        $id_product = $product->get_id();

        if ($WpmlEnable == 'yes') {
            //$id_product = icl_object_id( get_the_id(), 'post', true, $sitepress->get_default_language() ); //Will display the ID of the original post for WPML plugin
            $my_current_lang = apply_filters('wpml_current_language', null);
        } else {
            $my_current_lang = '';
        }

        $count = ntav_get_netreviews_count($id_product, $my_current_lang);

        if ($count > 0) {
            $tabs['reviews']['title'] = __('Product Reviews', 'av');
            $tabs['reviews']['callback'] = 'ntav_netreviews_tab';
            if (!empty($tabs['description'])) {
                $tabs['reviews']['priority'] = $tabs['description']['priority'] + 1;
            }
        }
    }
    return $tabs;
}

add_action('netreviews_product_reviews', 'ntav_netreviews_tab');
add_shortcode('netreviews_product_reviews', 'ntav_netreviews_product_reviews_short_code');

// this function is used as a short code. we need the if active check at this level also.
function ntav_netreviews_tab()
{
    $content = ntav_getContentForProductReviews();
    echo $content;
}

function ntav_netreviews_product_reviews_short_code()
{
    $content = ntav_getContentForProductReviews();
    return $content;
}

function ntav_getContentForProductReviews()
{
    global $product;
    $id_product = $product->get_id();
    $WpmlEnable = ntav_getWpmlEnable();

    if ($WpmlEnable == 'yes') {
        //global $sitepress;
        //$id_product = icl_object_id( get_the_id(), 'post', true, $sitepress->get_default_language() ); //Will display the ID of the original post for WPML plugin
        $my_current_lang = apply_filters('wpml_current_language', null);
    } else {
        $my_current_lang = '';
    }

    $count = ntav_get_netreviews_count($id_product, $my_current_lang);

    if (
        ntav_getConfig('OUR_PRODUCT_RATING', $my_current_lang) == 1 && ntav_getConfig(
            'OUR_PRODUCT_RICHSNIP',
            $my_current_lang
        ) == 1 && ntav_getConfig('TYPE_SNIPPET', $my_current_lang) == 'jsonld'
    ) {
        ntav_netreviews_jsonld_product();
    }

    if (ntav_getConfig('IS_ACTIVE', 'non') == 1 && $count >= 1) {
        return include_once AVNT_PATH . 'includes/av_product_tab.php';
    }
}


//////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////  WIDGET /////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////

// affichage widget flotant et hide des pages API et AJAX du nav-menu de wordpress.
add_action('wp_footer', 'ntav_av_widget_floating');
function ntav_av_widget_floating()
{
    global $wpdb;
    $WpmlEnable = ntav_getWpmlEnable();
    if ($WpmlEnable == 'yes') {
        $my_current_lang = apply_filters('wpml_current_language', null);
    } else {
        $my_current_lang = '';
    }

    if (ntav_getConfig('SCRIPTFLOAT_ALLOWED', $my_current_lang) == 'yes') {
        $widget = ntav_get_widget_floating();
        $script = ntav_AV_decode_base64($widget);
        echo $script;
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////   RECUPERATION DES COMMANDES  ///////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////

// hook de confirmation de commande : ajout meta.
add_action('woocommerce_thankyou', 'ntav_av_new_order');
add_action('woocommerce_order_status_changed', 'ntav_av_new_order');
add_action('woocommerce_new_order', 'ntav_av_new_order');

function ntav_av_new_order($id)
{
    $flag_mark = get_post_meta($id, 'av_flag', true);
    if (!is_numeric($flag_mark) && $flag_mark != 1) { //if order is created already in postmeta, avoid duplication
        $time = time();
        add_post_meta($id, 'av_flag', '0', true);
        add_post_meta($id, 'av_horodate', $time, true);
    }
}


add_action('woocommerce_checkout_order_processed', 'ntav_av_new_order_2', 1);
function ntav_av_new_order_2($id)
{
    $time = time();
    add_post_meta($id, 'av_flag', '0', true);
    add_post_meta($id, 'av_horodate', $time, true);
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////   NEW TABLES  ///////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Cette fonction a été ajoutée pour créer les nouvelles tables préfixées si elles n'existent pas.
// Correction du bug rencontré suite passage module en multisite
add_action('wp_footer', 'ntav_fix_tables_if_not_exists');
function ntav_fix_tables_if_not_exists()
{
    global $wpdb;
    $oldTables = $wpdb->get_results("SHOW TABLES LIKE  'netreviews%';");
    // Si les anciennes tables sont toujours présentes
    if (is_array($oldTables) && count($oldTables) > 0) {
        // On créer les nouvelles tables
        ntav_create_table_conf();
        ntav_create_table_ave();
        ntav_create_table_rev();
        // On vide la nouvelle table de conf
        $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . "netreviews_configuration");
        // On insère les anciennes datas dans les nouvelles tables
        $wpdb->query(
            "INSERT INTO " . $wpdb->prefix . "netreviews_configuration SELECT * FROM " . $wpdb->prefix . "netreviews_configuration"
        );
        $wpdb->query(
            "INSERT INTO " . $wpdb->prefix . "netreviews_products_average SELECT * FROM " . $wpdb->prefix . "netreviews_products_average"
        );
        $wpdb->query(
            "INSERT INTO " . $wpdb->prefix . "netreviews_products_reviews SELECT * FROM " . $wpdb->prefix . "netreviews_products_reviews"
        );
        $tablesToDrop = array('netreviews_configuration', 'netreviews_products_average', 'netreviews_products_reviews');
        // Ondelete les anciennes tables
        foreach ($tablesToDrop as $tableName) {
            $wpdb->query("DROP TABLE " . $tableName . "");
        }
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////   Avis utiles | Loi 2018   ///////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action('wp_footer', 'ntav_columns_if_not_exists');
// Check new column created
function ntav_columns_if_not_exists()
{
    global $wpdb;
    // colonnes nécessaires pour les avis utiles
    $result = $wpdb->get_results(
        "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA =  '" . $wpdb->dbname . "' AND TABLE_NAME =  '" . $wpdb->prefix . "netreviews_products_reviews' AND COLUMN_NAME =  'helpfulYes'"
    );
    if (empty($result)) {
        $wpdb->query(
            "ALTER TABLE " . $wpdb->prefix . "netreviews_products_reviews ADD  (`helpfulYes` int(5) DEFAULT '0', `helpfulNo` int(5) DEFAULT '0');"
        );
    }
    // loi 2018
    $result2 = $wpdb->get_results(
        "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA =  '" . $wpdb->dbname . "' AND TABLE_NAME =  '" . $wpdb->prefix . "netreviews_products_reviews' AND COLUMN_NAME =  'order_date'"
    );
    if (empty($result2)) {
        $wpdb->query("ALTER TABLE " . $wpdb->prefix . "netreviews_products_reviews ADD  (`order_date` DATE NULL);");
    }
    // media
    $result3 = $wpdb->get_results(
        "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA =  '" . $wpdb->dbname . "' AND TABLE_NAME =  '" . $wpdb->prefix . "netreviews_products_reviews' AND COLUMN_NAME =  'media_full'"
    );
    if (empty($result3)) {
        $wpdb->query("ALTER TABLE " . $wpdb->prefix . "netreviews_products_reviews ADD  (`media_full` text NULL);");
    }

    // Ajouter lang_code sur Table config (WPML)
    $result4 = $wpdb->get_results(
        "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA =  '" . $wpdb->dbname . "' AND TABLE_NAME =  '" . $wpdb->prefix . "netreviews_configuration' AND COLUMN_NAME =  'lang_code'"
    );
    if (empty($result4)) {
        $wpdb->query("ALTER TABLE " . $wpdb->prefix . "netreviews_configuration ADD  (`lang_code` varchar(25));");
    }
}

/**
 * Sanitize data which will be injected into SQL query.
 *
 * @param string $string SQL data which will be injected into SQL query
 *
 * @return string Sanitized data
 */

function escape($string)
{
    $output = '';
    $search = array(
        "'",
        '"',
        "`",
        '&',
        ',',
        ';',
        '/',
        ' ',
        'SELECT',
        'WHERE',
        'AND',
        'DROP',
        'DELETE',
        'TRUNCATE',
        'OR',
        'CREATE',
        'FROM',
        'CASCADE',
        'IN',
        '-',
        '\\'
    );

    $filter_0 = explode(";", strtoupper($string));
    $filter_1 = str_replace($search, '', $filter_0[0]);

    $output = strip_tags(trim($filter_1));

    return $output;
}

/**
 * Make sure that some config are stored by default in database
 *
 * @param field $field we are checking it exists in database
 *
 * @return true or false depending is it exists or not
 */

function checkData($fields)
{
    global $wpdb;

    // Ajouter lang_code sur Table config (WPML)
    $result = $wpdb->get_results(
        "SELECT value FROM " . $wpdb->prefix . "netreviews_configuration WHERE lang_code IS NULL AND name = '" . $fields . "';"
    );
    if (empty($result)) {
        //$wpdb->query("ALTER TABLE " . $wpdb->prefix . "netreviews_configuration ADD  (`lang_code` varchar(25));");
        return false;
    } else {
        return true;
    }
}
