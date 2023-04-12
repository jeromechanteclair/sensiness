<!-- We display the pagination in this file.-->
<?php
$WpmlEnable = ntav_getWpmlEnable();
if ($WpmlEnable == 'no') {
    $lang = '';
}
$idWebsite = ntav_getConfig('ID_WEBSITE', $lang);
$secretKey = ntav_getConfig('SECRET_KEY', $lang);
$helpfulOption = ntav_getConfig('HELPFULOPTION', 'non');
$mediaDisplay = ntav_getConfig('MEDIADISPLAY', 'non');
$chosenTemplate = ntav_getConfig('TEMPLATE_PRODUCT_PAGE', 'non');
$html = '';

$output = json_decode(json_encode($reviews), true);

foreach ($output as $k_review => $review) {
    $idProduitCible = $review['id_product_av'];
    $sign = sha1($idWebsite . $idProduitCible . $secretKey);
    $counthelpful = ntav_get_netreviews_helpful($idProduitCible);
    $medias = $review['media_full'];
    $data = ntav_medias($medias);
    $userReview = urldecode($review['review']);
    $note = $review['rate'];
    $stars = ntav_displayStars($review['rate']);

    if (!empty($review['order_date'])) {
        $string = '<span>'
            . __('following an order made on', 'av') .
            '&nbsp;' . date('d/m/Y', strtotime($review['order_date']))
            . '</span>';
    } else {
        $string = '<span>'
            . __('following an order made on', 'av') .
            '&nbsp;' . date('d/m/Y', $review['horodate'])
            . '</span>';
    }
    new dateTime();

    if (ntav_getConfig('OUR_PRODUCT_RICHSNIP', $lang) == 1 && ntav_getConfig('TYPE_SNIPPET', $lang) == 'jsonld') {
        $wc_version = WC_VERSION;
        $wc_version_as_int = (int)str_replace(".", "", $wc_version);
        global $product;
        if (!empty($product) && (is_object($product))) {
            $id_product = $product->get_id();
        }
        $id = get_permalink($id_product);
        $markup = '<script type="application/ld+json">';
        $markup .= '{';
        $markup .= '"@context":"http:\/\/schema.org\/",';

        if ($wc_version_as_int >= 355) {
            $markup .= '"@id":"' . $id . '#product",';
        } else {
            $markup .= '"@id":"' . $id . '",';
        }
        $markup .= '"review":';
        $markup .= '{';
        $markup .= '"@type":"Review",';
        $markup .= '"author":';
        $markup .= '{';
        $markup .= '"@type":"Person",';
        $markup .= '"name":"' . urldecode(urldecode($review['customer_name'])) . '"';
        $markup .= '},';
        $markup .= '"datePublished":"' . date('d/m/Y', $review['horodate']) . '",';
        $markup .= '"description":"' . urldecode(urldecode($review['review'])) . '",';
        $markup .= '"reviewRating":';
        $markup .= '{';
        $markup .= '"@type":"Rating",';
        $markup .= '"bestRating": "5",';
        $markup .= '"ratingValue":"' . $review['rate'] . '",';
        $markup .= '"worstRating": "1"';
        $markup .= '}';
        $markup .= '}';
        $markup .= '}';
        $markup .= '</script>';
        echo $markup;
    }

    $html .= '<div>';
    $html .= '<div id="netreviews_media_modal">';
    $html .= '<div id="netreviews_media_content"></div>';
    $html .= '<a id="netreviews_media_close">×</a>';
    $html .= '</div>';
    $html .= '<div class="netreviews_review_part">';

    if ($chosenTemplate == '1') {
        $html .= '<p class="netreviews_customer_name">' . urldecode(urldecode($review['customer_name']));
        $html .= ' <span>' . date(
            'd/m/Y',
            $review['horodate']
        ) . '</span>&nbsp;<span style="font-size: 13px !important;">' . $string . '</span></p>';
        $html .= '<div class="netreviews_review_rate_and_stars">';
        $html .= $stars;
        $html .= '<div class="netreviews_reviews_rate">' . $note . '/5 </div>';
        $html .= '</div>';
        $html .= '<p class="netreviews_customer_review">' . $userReview . '</p>';
        if ($mediaDisplay != 'no') {
            if (isset($data)) {
                $html .= '<ul class="netreviews_media_part">';
                foreach ($data as $id => $value) {
                    $html .= '<li>';
                    $html .= '<a data-type="image"
                        data-src="' . $data[$id]['large'] . '"
                        class="netreviews_image_thumb" href="javascript:"
                        style="background-image:url(' . $data[$id]['small'] . ');"> </a>';
                    $html .= '</li>';
                }

                $html .= '</ul>';
                $html .= '<div class="netreviews_clear"></div>';
            }
        }
        if (ntav_getConfig('OUR_PRODUCT_RICHSNIP', $lang) == 1 && ntav_getConfig('TYPE_SNIPPET', $lang) != 'jsonld') {
            $html .= '<div itemprop="review" itemscope itemtype="http://schema.org/Review">';
            $html .= '<meta itemprop="reviewBody" content="' . $userReview . '"/>';
            $html .= '<span itemprop="author" itemscope itemtype="https://schema.org/Person">';
            $html .= '<span itemprop="name" content="' . urldecode(
                urldecode($review['customer_name'])
            ) . '"></span></span>';
            $html .= '<meta itemprop="datePublished" itemtype="https://schema.org/datePublished" content="' . date(
                'd/m/Y',
                $review['horodate']
            ) . '"/>';
            $html .= '<div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">';
            $html .= '<meta itemprop="ratingValue" content="' . $note . '"/>';
            $html .= '</div>';
        }
        if ($helpfulOption == 'yes') {
            $html .= '<p class="netreviews_helpful_block">' . __('Was this review helpful ?', 'av');
            $html .= '<a href="javascript:" class="netreviewsVote" data-review-id="' . $idProduitCible . '" data-vote="1" data-sign="' . $sign . '" id="' . $idProduitCible . '_1">' . __(
                'Yes',
                'av'
            ) . '<span>' . $review['helpfulYes'] . '</span></a>';
            $html .= '<a href="javascript:" class="netreviewsVote" data-review-id="' . $idProduitCible . '" data-vote="0" data-sign="' . $sign . '" id="' . $idProduitCible . '_0">' . __(
                'No',
                'av'
            ) . '<span>' . $review['helpfulNo'] . '</span></a>';
            $html .= '</p>';
            $html .= '<p class="netreviews_helpfulmsg" id="' . $idProduitCible . '_msg"></p>';
            //$html .= '</p>';
        }
    } elseif ($chosenTemplate == '2') {
        $html .= '<div class="netreviews_stars_rate">';
        $html .= '<div class="netreviews_review_rate_and_stars">' . $stars . '</div>';
        $html .= '<div class="nrRate">' . $note . '/5</div>';
        $html .= '</div>';
        $html .= '<p class="netreviews_customer_review">' . $userReview . '</p>';
        if (ntav_getConfig('OUR_PRODUCT_RICHSNIP', $lang) == 1 && ntav_getConfig('TYPE_SNIPPET', $lang) != 'jsonld') {
            $html .= '<div itemprop="review" itemscope itemtype="http://schema.org/Review">';
            $html .= '<meta itemprop="reviewBody" content="' . $userReview . '"/>';
            $html .= '<span itemprop="author" itemscope itemtype="https://schema.org/Person">';
            $html .= '<span itemprop="name" content="' . urldecode(
                urldecode($review['customer_name'])
            ) . '"></span></span>';
            $html .= '<meta itemprop="datePublished" itemtype="https://schema.org/datePublished" content="' . date(
                'd/m/Y',
                $review['horodate']
            ) . '"/>';
            $html .= '<div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">';
            $html .= '<meta itemprop="ratingValue" content="' . $note . '"/>';
            $html .= '</div>';
            $html .= '</div>';
        }
        if ($mediaDisplay != 'no') {
            if (isset($data) && !empty($data)) {
                $html .= '<ul class="netreviews_media_part">';
                foreach ($data as $id => $value) {
                    $html .= '<li>';
                    $html .= '<a data-type="image"
                        data-src="' . $data[$id]['large'] . '"
                        class="netreviews_image_thumb" href="javascript:"
                        style="background-image:url(' . $data[$id]['small'] . ');" rel="nofollow noopener"> </a>';
                    $html .= '</li>';
                }
                $html .= '</ul>';
                $html .= '<div class="netreviews_clear"></div>';
            }
        }
        $html .= '<div class="netreviews_customer_name"><p>' . urldecode(urldecode($review['customer_name']));
        $html .= ' <span>' . date('d/m/Y', $review['horodate']) . '</span>&nbsp;<span>' . $string . '</span>';
        $html .= '</p>';
        $html .= '</div>';
    }

    $discussions = json_decode(base64_decode($review['discussion']));
    $nb_Discussion = 0;
    if (!empty($discussions)) {
        $html .= '<div class="netreviews_discussion">';

        foreach (array_reverse($discussions) as $k_Discussion => $discussion) {
            $reviewNumber = ($k_Discussion > 0) ? ' review_number="' . $review['id_product_av'] . '" ' : '';
            $styleNumber = ($k_Discussion > 0) ? ' style="display:none;" ' : '';

            if ($discussion->origine == 'ecommercant') {
                $hrefcertificat = ntav_getConfig('URLCERTIFICAT', $lang);
                $urlSite = explode("/", $hrefcertificat);
                $discussion->origine = $urlSite[4];
            } elseif ($discussion->origine == 'moderateur') {
                $discussion->origine = 'Modérateur';
            } elseif ($discussion->origine == 'internaute') {
                $discussion->origine = urldecode(urldecode($review['customer_name']));
            }

            //$discussion->horodate = explode('T', $discussion->horodate);

            $html .= '<div class="netreviews_website_answer"' . $reviewNumber . $styleNumber . '>';
            $html .= '<p>';
            $html .= '<span class="netreviews_answer_title">' . __(
                'Reply from ',
                'av'
            ) . $discussion->origine . '<small> ' . __('on', 'av') . " " . date(
                'd/m/Y',
                strtotime($discussion->horodate)
            ) . '</small></span>';
            $html .= '</br>';
            $html .= urldecode($discussion->commentaire);
            $html .= '</p>';
            $html .= '</div>';

            $nb_Discussion = $k_Discussion;
        }

        $html .= '</div>';
        if ($nb_Discussion > 0) {
            $html .= '<a href="javascript:switchCommentsVisibilityReviews(\'' . $idProduitCible . '\')"
                id="display_' . $idProduitCible . '" class="netreviews_button_comment"
                review_number="' . $idProduitCible . '">
                <div class="nr-icon nr-comment"></div>
                <span>' . __('Show exchanges', 'av') . '</span>
                </a>';
            $html .= '<a style="display:none" href="javascript:switchCommentsVisibilityReviews(\'' . $idProduitCible . '\')"
                id="hide_' . $idProduitCible . '" class="netreviews_button_comment"
                review_number="' . $idProduitCible . '">
                <div class="nr-icon nr-comment"></div>
                <span>' . __('Hide exchanges', 'av') . '</span>
                </a>';
        }
    }
    if ($chosenTemplate == '2' && $helpfulOption == 'yes') {
        $html .= '<div class="netreviews_helpful_block">' . __('Was this review helpful ?', 'av');
        $html .= '<a href="javascript:" class="netreviewsVote" data-review-id="' . $idProduitCible . '" data-vote="1" data-sign="' . $sign . '" id="' . $idProduitCible . '_1">' . __(
            'Yes',
            'av'
        ) . '<span>' . $review['helpfulYes'] . '</span></a>';
        $html .= '<a href="javascript:" class="netreviewsVote" data-review-id="' . $idProduitCible . '" data-vote="0" data-sign="' . $sign . '" id="' . $idProduitCible . '_0">' . __(
            'No',
            'av'
        ) . '<span>' . $review['helpfulNo'] . '</span></a>';
        $html .= '<p class="netreviews_helpfulmsg" id="' . $idProduitCible . '_msg"></p>';
        $html .= '</div>';
    }
    $html .= '</div>';
    //if ($chosenTemplate == '1') {
    $html .= '</div>';
    //}

    if (
        ntav_getConfig('OUR_PRODUCT_RICHSNIP', $my_current_lang) == 1 && ntav_getConfig(
            'TYPE_SNIPPET',
            $my_current_lang
        ) == 'microdata' && $chosenTemplate == '1' /* && ntav_getConfig('NEED_COMPLETE_RS',$my_current_lang) == 1*/
    ) {
        $html .= '</div>';
    }
}

if (
    ntav_getConfig('OUR_PRODUCT_RICHSNIP', $my_current_lang) == 1 && ntav_getConfig(
        'TYPE_SNIPPET',
        $my_current_lang
    ) == 'microdata' && $chosenTemplate == '2' /* && ntav_getConfig('NEED_COMPLETE_RS',$my_current_lang) == 1*/
) {
    $html .= '</div>';
}

return $html;
