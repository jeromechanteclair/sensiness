<!-- We display the last 2 badges depending the config chosen -->
<?php

global $product;
$id_product = $product->get_id();

$WpmlEnable = ntav_getWpmlEnable();
if ($WpmlEnable == 'yes') {
    $my_current_lang = apply_filters('wpml_current_language', null);
} else {
    $my_current_lang = '';
}

$count = ntav_get_netreviews_count($id_product, $my_current_lang);
$average = ntav_get_netreviews_average($id_product, $my_current_lang);
$logoFile = content_url() . '/plugins/netreviews/includes/images/' . ntav_get_img_by_lang()['sceau_lang'];
$altImage = __('Verified Reviews', 'av');
if (!isset($starsColour)) {
    $starsColour = 'FFCD00';
}
$text = __('See all reviews', 'av');

if (ntav_getConfig('DESIGN_PRODUCT_PAGE', 'non') == 2) {
    $html = '<div class="netreviewsProductWidgetNew">';
    if (
        ntav_getConfig('OUR_PRODUCT_RICHSNIP', $my_current_lang) == 1 && ntav_getConfig(
            'TYPE_SNIPPET',
            $my_current_lang
        ) == 'microdata' && ntav_getConfig('NEED_COMPLETE_RS', $my_current_lang) == 0
    ) {
        $html .= '<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">';
        $html .= '<meta itemprop="ratingValue" content="' . $average . '"/>';
        $html .= '<meta itemprop="worstRating" content="1"/>';
        $html .= '<meta itemprop="bestRating" content="5"/>';
        $html .= '<meta itemprop="reviewCount" content="' . $count . '"/>';
        $html .= '</span>';
    }
    $html .= "<img id='sceau' class='netreviewsProductWidgetNewLogo' src='" . $logoFile . "' alt='" . $altImage . "'>";
    $html .= '<div class="ProductWidgetNewRatingWrapper">';
    $html .= '<div class="netreviews_review_rate_and_stars">';
    $html .= '<div class="netreviews_font_stars">';
    $html .= '<div>';
    $html .= '<span class="nr-icon nr-star grey"></span>';
    $html .= '<span class="nr-icon nr-star grey"></span>';
    $html .= '<span class="nr-icon nr-star grey"></span>';
    $html .= '<span class="nr-icon nr-star grey"></span>';
    $html .= '<span class="nr-icon nr-star grey"></span>';
    $html .= '</div>';
    $html .= '<div style = "color : #' . $starsColour . '!important">';
    for ($i = 1; $i <= $average; $i++) {
        $html .= '<span class="nr-icon nr-star gold"></span>';
    }
    $valueAverage = ((round($average, 1) - ($i - 1)) * 20 < 0 ? "0" : (round($average, 1) - ($i - 1)) * 20);
    $html .= '<span class="nr-icon nr-star gold" style="width: ' . $valueAverage . '%; overflow:hidden; display:inline-block;"></span>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="netreviewsProductWidgetNewRate">';
    $html .= '<span class="ratingValue">' . round($average, 1) . '</span>/<span class="bestRating">5</span>';
    $html .= '</div>';
    $html .= '<a href="javascript:showNetreviewsTab();" class="woocommerce-review-link" id="AV_button">';
    $html .= $text;
    $html .= '(<span>' . $count . '</span>)';
    $html .= '</a> ';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

if (ntav_getConfig('DESIGN_PRODUCT_PAGE', 'non') == 3) {
    $html = '<div class="av_product_award">';
    if (
        ntav_getConfig('OUR_PRODUCT_RICHSNIP', $my_current_lang) == 1 && ntav_getConfig(
            'TYPE_SNIPPET',
            $my_current_lang
        ) == 'microdata' && ntav_getConfig('NEED_COMPLETE_RS', $my_current_lang) == 0
    ) {
        $html .= '<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">';
        $html .= '<meta itemprop="ratingValue" content="' . $average . '"/>';
        $html .= '<meta itemprop="worstRating" content="1"/>';
        $html .= '<meta itemprop="bestRating" content="5"/>';
        $html .= '<meta itemprop="reviewCount" content="' . $count . '"/>';
        $html .= '</span>';
    }
    $html .= '<div id="top"> ';
    $html .= '<div class="netreviews_font_stars">';
    $html .= '<div id="bigBadge">';
    $html .= '<span class="nr-icon nr-star grey"></span>';
    $html .= '<span class="nr-icon nr-star grey"></span>';
    $html .= '<span class="nr-icon nr-star grey"></span>';
    $html .= '<span class="nr-icon nr-star grey"></span>';
    $html .= '<span class="nr-icon nr-star grey"></span>';
    $html .= '</div>';
    $html .= '<div id="bigBadge" style = "color : #' . $starsColour . '!important;">';
    for ($i = 1; $i <= $average; $i++) {
        $html .= '<span class="nr-icon nr-star gold"></span>';
    }
    $valueAverage = ((round($average, 1) - ($i - 1)) * 20 < 0 ? "0" : (round($average, 1) - ($i - 1)) * 20);
    $html .= '<span class="nr-icon nr-star gold" style="width:' . $valueAverage . '%;overflow:hidden; display:inline-block;"></span>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="ratingText">';
    $html .= '<span class="reviewCount">' . $count . '</span> ' . __('Reviews', 'av');
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div id="AV_bottom" style="background:#' . $starsColour . '!important">';
    $html .= '<a id="AV_button" href="javascript:showNetreviewsTab();" class="woocommerce-review-link">' . __(
        'See all reviews',
        'av'
    ) . '</a>';
    $html .= '</div>';
    $html .= '<img id="sceau" src ="' . $logoFile . '" alt="' . __('Verified Reviews', 'av') . '">';
    $html .= '</div> ';

    return $html;
}
