<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
$WpmlEnable = ntav_getWpmlEnable();
if ($WpmlEnable == 'yes') {
    $my_current_lang = apply_filters('wpml_current_language', null);
} else {
    $my_current_lang = '';
}

// On calcule l'url à utiliser pour la fonctionnalité Avis Utile (API)
$hrefcertificat = ntav_getConfig('URLCERTIFICAT', $my_current_lang);
$platform = explode("/", $hrefcertificat);
if (strpos($platform[2], 'www') !== false) {
    $avHelpfulURL = "https://" . $platform[2] . "/index.php?action=act_api_product_reviews_helpful";
} else {
    $avHelpfulURL = "https://www.avis-verifies.com/index.php?action=act_api_product_reviews_helpful";
}

$avisAjaxUrl = get_site_url() . '/?netreviews-plugin=ajax';
$pluginImagesURL = content_url() . '/plugins/netreviews/includes/images/';
$logoFile = __('logo_full_en.png', 'av');
$idWebsite = ntav_getConfig('ID_WEBSITE', $my_current_lang);
$responsive = ntav_getConfig('RESPONSIVE', $my_current_lang);
$helpfulOption = ntav_getConfig('HELPFULOPTION', $my_current_lang);
$id_product = get_the_ID();
$starsColour = ntav_getConfig('COLOUR_STARS_AV', 'non');
$templateVersion = ntav_getConfig('TEMPLATE_PRODUCT_PAGE', 'non');
$numberReviews = ntav_getConfig('NUMBER_REVIEWS_DISPLAYED', 'non');

if (!isset($starsColour, $numberReviews)) {
    $starsColour = 'FFCD00';
    $numberReviews = 5;
}

$reviews = ntav_getReviewsRS($id_product);
$countReviews = ntav_get_netreviews_count($id_product, $my_current_lang);
$getNote = round(ntav_get_netreviews_average($id_product, $my_current_lang), 1);
$countByRate = ntav_get_netreviews_countByRate($id_product, $my_current_lang);
$countRecommended = ntav_get_netreviews_percentRecommended($id_product, $my_current_lang);

$ratio1 = 0;
$ratio2 = 0;
$ratio3 = 0;
$ratio4 = 0;
$ratio5 = 0;
$count1 = 0;
$count2 = 0;
$count3 = 0;
$count4 = 0;
$count5 = 0;
$maxPage1 = 0;
$maxPage2 = 0;
$maxPage3 = 0;
$maxPage4 = 0;
$maxPage5 = 0;

//check pas de division par 0
if ($countReviews > 0 && $getNote > 0) {
    $maxPages = ceil($countReviews / $numberReviews);
    $globalStarsWith = ($getNote / 5) * 100;

    foreach ($countByRate as $value) {
        switch ($value->RATE) {
            case 1:
                $ratio1 = ($value->nbrate / $countReviews) * 100;
                $count1 = $value->nbrate;
                $maxPage1 = ceil($count1 / $numberReviews);
                break;
            case 2:
                $ratio2 = ($value->nbrate / $countReviews) * 100;
                $count2 = $value->nbrate;
                $maxPage2 = ceil($count2 / $numberReviews);
                break;
            case 3:
                $ratio3 = ($value->nbrate / $countReviews) * 100;
                $count3 = $value->nbrate;
                $maxPage3 = ceil($count3 / $numberReviews);
                break;
            case 4:
                $ratio4 = ($value->nbrate / $countReviews) * 100;
                $count4 = $value->nbrate;
                $maxPage4 = ceil($count4 / $numberReviews);
                break;
            case 5:
                $ratio5 = ($value->nbrate / $countReviews) * 100;
                $count5 = $value->nbrate;
                $maxPage5 = ceil($count5 / $numberReviews);
                break;
        }
    }

    //If we need complete Product tag
    if (
        ntav_getConfig('OUR_PRODUCT_RICHSNIP', $my_current_lang) == 1 && ntav_getConfig(
            'NEED_COMPLETE_RS',
            $my_current_lang
        ) == 1
    ) {
        global $product;
        $productName = $product->get_name();
        $productDescription = $product->get_short_description();
        //$productPrice = $product->get_price_including_tax();
        $productPrice = wc_get_price_including_tax($product);
        $productCurrency = get_woocommerce_currency();
        $stock = $product->get_stock_status();
        if ($stock == 'instock') {
            $productAvailability = 'InStock';
        } elseif ($stock == 'outofstock') {
            $productAvailability = 'OutOfStock';
        } else {
            $productAvailability = null;
        }
        $productUrl = get_permalink($product->get_id());
        $imageUrl = wp_get_attachment_url($product->get_image_id());
        $productSku = $product->get_sku();
        $label_brand = ntav_getConfig('ATTRIBUTE_BRAND', '');
        $prefix_attr = 'pa_';
        if (!empty($label_brand)) {
            $variable_brand = wc_get_product_terms(
                $product->get_id(),
                $prefix_attr . $label_brand,
                array('fields' => 'names')
            );
            $productBrand = array_shift($variable_brand);
        } else {
            $productBrand = null;
        }
        $label_mpn = ntav_getConfig('ATTRIBUTE_MPN', '');
        if (!empty($label_mpn)) {
            $variable_mpn = wc_get_product_terms(
                $product->get_id(),
                $prefix_attr . $label_mpn,
                array('fields' => 'names')
            );
            $productMpn = array_shift($variable_mpn);
        } else {
            $productMpn = null;
        }
        $label_gtin = ntav_getConfig('ATTRIBUTE_GTIN', '');
        if (!empty($label_gtin)) {
            $variable_gtin = wc_get_product_terms(
                $product->get_id(),
                $prefix_attr . $label_gtin,
                array('fields' => 'names')
            );
            $productGtin = array_shift($variable_gtin);
        } else {
            $productGtin = null;
        }

        if (ntav_getConfig('TYPE_SNIPPET', $my_current_lang) == 'microdata') {
            $htmlRS = '<div id="netreviews_div_complete_rs_microdata" itemscope itemtype="http://schema.org/Product">';
            $htmlRS .= '<meta itemprop="name" content="' . $productName . '">';
            $htmlRS .= '<meta itemprop="description" content="' . strip_tags($productDescription) . '">';
            $htmlRS .= '<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">';
            $htmlRS .= '<meta itemprop="priceCurrency" content="' . $productCurrency . '">';
            $htmlRS .= '<meta itemprop="price" content="' . $productPrice . '">';
            if ($productAvailability) {
                $htmlRS .= '<link itemprop="availability" href="http://schema.org/' . $productAvailability . '" />';
            }
            if ($productUrl) {
                $htmlRS .= '<meta itemprop="url" content="' . $productUrl . '">';
            }
            $htmlRS .= '</span>';
            if ($imageUrl) {
                $htmlRS .= '<meta itemprop="image" content="' . $imageUrl . '">';
            }
            if ($productUrl) {
                $htmlRS .= '<meta itemprop="url" content="' . $productUrl . '">';
            }
            if ($productSku) {
                $htmlRS .= '<meta itemprop="sku" content="' . $productSku . '">';
            }
            if ($productBrand) {
                $htmlRS .= '<meta itemprop="brand" content="' . $productBrand . '">';
            }
            if ($productMpn) {
                $htmlRS .= '<meta itemprop="mpn" content="' . $productMpn . '">';
            }
            if ($productGtin) {
                $htmlRS .= '<meta itemprop="gtin13" content="' . $productGtin . '">';
            }
            if ($countReviews && $getNote) {
                $htmlRS .= '<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">';
                $htmlRS .= '<meta itemprop="reviewCount" content="' . $countReviews . '"/>';
                $htmlRS .= '<meta itemprop="ratingValue" content="' . $getNote . '"/>';
                $htmlRS .= '<meta itemprop="worstRating" content="1"/>';
                $htmlRS .= '<meta itemprop="bestRating" content="5"/>';
                $htmlRS .= '</span>';
            }
            echo $htmlRS;
        } elseif (ntav_getConfig('TYPE_SNIPPET', $my_current_lang) == 'jsonld') {
            $wc_version = WC_VERSION;
            $wc_version_as_int = (int)str_replace(".", "", $wc_version);
            if (!is_object($product)) {
                global $product;
            }
            if (!empty($product) && (is_object($product))) {
                $id_product = $product->get_id();
            }
            $id = get_permalink($id_product);

            $htmlRSJson = '<script type="application/ld+json">';
            $htmlRSJson .= '{';
            $htmlRSJson .= '"@context": "http://schema.org/",';
            $htmlRSJson .= '"@type": "Product",';
            if ($wc_version_as_int >= 355) {
                $htmlRSJson .= '"@id":"' . $id . '#product",';
            } else {
                $htmlRSJson .= '"@id":"' . $id . '",';
            }
            $htmlRSJson .= '"name": "' . $productName . '",';
            $htmlRSJson .= '"description": "' . strip_tags($productDescription) . '",';
            $htmlRSJson .= '"offers":';
            $htmlRSJson .= '{';
            $htmlRSJson .= '"@type": "Offer",';
            $htmlRSJson .= '"priceCurrency": "' . $productCurrency . '",';
            $htmlRSJson .= '"price": "' . $productPrice . '",';
            $htmlRSJson .= '"availability": "' . $productAvailability . '",';
            $htmlRSJson .= '"name": "' . $productName . '",';
            $htmlRSJson .= '"url": "' . $productUrl . '"';
            $htmlRSJson .= '}';
            if ($imageUrl || $productSku || $productBrand || $productMpn || $productGtin) {
                $htmlRSJson .= ',';
            }
            if ($imageUrl) {
                $htmlRSJson .= '"image": "' . $imageUrl . '"';
                if ($productSku || $productBrand || $productMpn || $productGtin) {
                    $htmlRSJson .= ',';
                }
            }
            if ($productSku) {
                $htmlRSJson .= '"sku": "' . $productSku . '"';
                if ($productBrand || $productMpn || $productGtin) {
                    $htmlRSJson .= ',';
                }
            }
            if ($productBrand) {
                $htmlRSJson .= '"brand": "' . $productBrand . '"';
                if ($productMpn || $productGtin) {
                    $htmlRSJson .= ',';
                }
            }
            if ($productMpn) {
                $htmlRSJson .= '"mpn": "' . $productMpn . '"';
                if ($productGtin) {
                    $htmlRSJson .= ',';
                }
            }
            if ($productGtin) {
                $htmlRSJson .= '"gtin13": "' . $productGtin . '"';
            }
            $htmlRSJson .= '}';
            $htmlRSJson .= '</script>';
            echo $htmlRSJson;
        }
    }

    if ($templateVersion == 1) {
        return include(dirname(__FILE__) . '/templates/templateV1.php');
    } elseif ($templateVersion == 2) {
        return include(dirname(__FILE__) . '/templates/templateV2.php');
    } ?>

    <!-- NETREVIEWS - END -->

    <?php
} ?>
