<?php

$errorMessage = __('An error has occured.', 'av');
$successMessage = __('Thank you, your vote will be published soon', 'av');

$html = '<script type="text/javascript">
var avHelpfulIdwebsite = "' .  $idWebsite . '";
var avHelpfulURL = "' . $avHelpfulURL . '";
var avhelpfulExec = false;
var avInitialFingerPrint = "";
var avHelpfulCookie = {};
var avHelpfulErrorMessage = "' . $errorMessage . '";
var avHelpfulSuccessMessage = "' . $successMessage . '";
</script>';
$html .= '<script type="text/javascript" src="' . plugins_url(null, '') . '/netreviews/includes/js/carousel.js"></script>';
$html .= '<script type="text/javascript" src="' . plugins_url(null, '') . '/netreviews/includes/js/fingerprint2.min.js"></script>';
$html .= '<script type="text/javascript" src="' . plugins_url(null, '') . '/netreviews/includes/js/netreviewsHelpful.js"></script>';
$html .= '<script type="text/javascript" src="' . plugins_url(null, '') . '/netreviews/includes/js/carousel.js"></script>';
$html .= '<script type="text/javascript" src="' . plugins_url(null, '') . '/netreviews/includes/js/av_product_tab.js"></script>';
$html .= '<script src="https://kit.fontawesome.com/fd565dd76c.js" crossorigin="anonymous"></script>';
$html .= '<!-- hiddens -->';
$html .= '<input type="hidden" id="rateSelected" value=""/>';
$html .= '<input type="hidden" id="maxPage1" value="' . $maxPage1 . '"/>';
$html .= '<input type="hidden" id="maxPage2" value="' . $maxPage2 . '"/>';
$html .= '<input type="hidden" id="maxPage3" value="' . $maxPage3 . '"/>';
$html .= '<input type="hidden" id="maxPage4" value="' . $maxPage4 . '"/>';
$html .= '<input type="hidden" id="maxPage5" value="' . $maxPage5 . '"/>';
$html .= '<input type="hidden" id="maxPageGlobal" value="' . $maxPages . '">';
$html .= '<input type="hidden" id="av_id_product" value="' . $id_product . '"/>';
$html .= '<input type="hidden" id="avisVarifiesAjaxUrl" value="' . $avisAjaxUrl . '"/>';
$html .= '<input type="hidden" id="wpml_Lang" value="' . $my_current_lang . '"/>';

$html .= '<div id="netreviews_reviews_tab" class="netreviews_tpl_v2">';
$html .= '<table id="netreviews_table_tab">';
$html .= '<tbody>';
$html .= '<tr>';
$html .= '<td class="netreviews_left_column">';
$html .= '<div class="netreviews_rating_header">';
$html .= '<div class="netreviews_logo">';
$html .= '<img src="' . $pluginImagesURL . $logoFile . '" alt="' . __('Verified Reviews', 'av') . '">';
$html .= '</div>';
$html .= '</div>';
$html .= '<div class="netreviews_rating_content">';
$html .= '<div class="netreviews_global_rating">';
$html .= '<div class="netreviews_global_rating_responsive">';
$html .= '<div class="netreviews_note_generale_recommendations">';
$html .= '<div class="netreviews_note_generale_recommendations_responsive">';
$html .= '<div class="netreviews_note_generale">';
$html .= '<table class="netreviews_note_generale_table">';
$html .= '<tbody class="netreviews_note_generale_table_tbody">';
$html .= '<tr class="netreviews_note_generale_table_tr">';
$html .= '<td class="netreviews_note_generale_table_td">' . $getNote . '</td>';
$html .= '<td class="netreviews_note_generale_table_td">|</td>';
$html .= '<td class="netreviews_note_generale_table_td">5</td>';
$html .= '</tr>';
$html .= '</tbody>';
$html .= '</table>';
$html .= '</div>';
$html .= '<p class="netreviews_subtitle">' . $countReviews . ' ' . __('review(s)', 'av') . '<i class="far fa-user"></i></p>';
$html .= '</div>';
$html .= '<p class="netreviews_subtitle">';
$html .= '<b>' . $countRecommended . '%</b> ' . __(' customers recommending this product', 'av');
$html .= '</p>';
$html .= '</div>';
$html .= '<div class="netreviews_resume_rates">';
$html .= '<div class="netreviews_stats_stars_big ';
if (round($ratio5, 1) == 0) {
    $html .= 'netreviews_disabled_click';
}
$html .= '" onclick="refreshReviewsWithFilter(5);">';
$html .= '<div class="stat_star">';
for ($i = 0; $i < 5; $i++) {
    $html .= '<span class="nr-icon nr-star gold"  style = "color :#' . $starsColour . '!important"></span>';
}
$html .= '</div>';
$html .= '<div class="netreviews_percentage_rate">' . round($ratio5, 1) . '%</div>';
$html .= '<div class="netreviews_percentage_bar" style="background: linear-gradient(to right, rgb(173, 173, 173) ' . round($ratio5, 1) . '%, rgb(216, 216, 216) ' . round($ratio5, 1) . '%);"></div>';
$html .= '</div>';
$html .= '<div class="netreviews_stats_stars_big ';
if (round($ratio4, 1) == 0) {
    $html .= 'netreviews_disabled_click';
}
$html .= '" onclick="refreshReviewsWithFilter(4);">';
$html .= '<div class="stat_star">';
for ($i = 0; $i < 4; $i++) {
    $html .= '<span class="nr-icon nr-star gold"  style = "color :#' . $starsColour . '!important"></span>';
}
$html .= '</div>';
$html .= '<div class="netreviews_percentage_rate">' . round($ratio4, 1) . '%</div>';
$html .= '<div class="netreviews_percentage_bar" style="background: linear-gradient(to right, rgb(173, 173, 173) ' . round($ratio4, 1) . '%, rgb(216, 216, 216) ' . round($ratio4, 1) . '%);"></div>';
$html .= '</div>';
$html .= '<div class="netreviews_stats_stars_big ';
if (round($ratio3, 1) == 0) {
    $html .= 'netreviews_disabled_click';
}
$html .= '" onclick="refreshReviewsWithFilter(3);">';
$html .= '<div class="stat_star">';
for ($i = 0; $i < 3; $i++) {
    $html .= '<span class="nr-icon nr-star gold"  style = "color :#' . $starsColour . '!important"></span>';
}
$html .= '</div>';
$html .= '<div class="netreviews_percentage_rate">' . round($ratio3, 1) . '%</div>';
$html .= '<div class="netreviews_percentage_bar" style="background: linear-gradient(to right, rgb(173, 173, 173) ' . round($ratio3, 1) . '%, rgb(216, 216, 216) ' . round($ratio3, 1) . '%);"></div>';
$html .= '</div>';
$html .= '<div class="netreviews_stats_stars_big ';
if (round($ratio2, 1) == 0) {
    $html .= 'netreviews_disabled_click';
}
$html .= '" onclick="refreshReviewsWithFilter(2);">';
$html .= '<div class="stat_star">';
for ($i = 0; $i < 2; $i++) {
    $html .= '<span class="nr-icon nr-star gold"  style = "color :#' . $starsColour . '!important"></span>';
}
$html .= '</div>';
$html .= '<div class="netreviews_percentage_rate">' . round($ratio2, 1) . '%</div>';
$html .= '<div class="netreviews_percentage_bar" style="background: linear-gradient(to right, rgb(173, 173, 173) ' . round($ratio2, 1) . '%, rgb(216, 216, 216) ' . round($ratio2, 1) . '%);"></div>';
$html .= '</div>';

$html .= '<div class="netreviews_stats_stars_big ';
if (round($ratio1, 1) == 0) {
    $html .= 'netreviews_disabled_click';
}
$html .= '" onclick="refreshReviewsWithFilter(1);">';
$html .= '<div class="stat_star">';
for ($i = 0; $i < 1; $i++) {
    $html .= '<span class="nr-icon nr-star gold"  style = "color :#' . $starsColour . '!important"></span>';
}
$html .= '</div>';
$html .= '<div class="netreviews_percentage_rate">' . round($ratio1, 1) . '%</div>';
$html .= '<div class="netreviews_percentage_bar" style="background: linear-gradient(to right, rgb(173, 173, 173) ' . round($ratio1, 1) . '%, rgb(216, 216, 216) ' . round($ratio1, 1) . '%);"></div>';
$html .= '</div>';
$html .= '</div>';
$html .= '</div>';

$html .= '<div class="netreviews_afnor">';
$html .= '<a class="netreviews_certification" target="_blank" href="' . $hrefcertificat . '">' . __('Show the Certificate of Trust', 'av') . '</a>';
$html .= '<div>';
$html .= '<label id="netreviews_informations_label">';
$html .= '<span>' . __('Reviews subject to control', 'av') . '</span>';
$html .= '<span class="nr-icon nr-info"></span>';
$html .= '</label>';
$html .= '<div id="netreviews_informations">';
$html .= '<div class="nr-icon nr-exit"></div>';
$html .= '<ul>';
$html .= '<li>' . __("For further information on the nature of the review controls, as well as the possibility of contacting the author of the review please consult our", 'av');
$html .= '<a href="http://www.' . __('verified-reviews.com', 'av') . '/index.php?page=mod_conditions_utilisation" target="_blank">' . __("GCU", 'av') . '</a>';
$html .= '</li>';
$html .= '<li>' . __("No inducements have been provided for these reviews", 'av') . '</li>';
$html .= '<li>' . __("Reviews are published and kept for a period of five years", 'av') . '</li>';
$html .= '<li>' . __("Reviews can not be modified: If a customer wishes to modify their review then they can do so by contacting Verified Reviews directly to remove the existing review and publish an amended one", 'av') . '</li>';
$html .= '<li>' . __("The reasons for deletion of reviews are available", 'av');
$html .= '<a href="https://www.' . __('verified-reviews.com', 'av') . '/index.php?page=mod_conditions_utilisation#Rejet_de_lavis_de_consommateur" target="_blank">' . __("here", 'av') . '</a>.';
$html .= '</li>';
$html .= '</ul>';
$html .= '</div>';
$html .= '</div>';
$html .= '</div>';
$html .= '</div>';
$html .= '</div>';
$html .= '<div class="netreviews_all_reviews" onclick="refreshReviewsWithFilter(\'newest\')">';
$html .= '<div id="refreshToAllReviews">';
$html .= '<span class="display">' . __('All the reviews', 'av') . '</span>';
$html .= '<i class="netreviews_arrow_up"></i>';
$html .= '</div>';
$html .= '</div>';
$html .= '</td>';
$html .= '<td class="netreviews_right_column">';
$html .= '<div class="netreviews_rating_header">';
$html .= '<div class="netreviews_filtering_section"><label>' . __('Sort the reviews display', 'av');
$html .= '<select id="netreviews_reviews_filter" name="netreviews_reviews_filter" onchange="refreshReviewsWithFilter(\'' . '\')">';
$html .= '<option value="newest" selected="selected">' . __('Newest', 'av') . '</option>';
$html .= '<option value="oldest">' . __('Oldest', 'av') . '</option>';
$html .= '<option value="highest">' . __('Highest rating', 'av') . '</option>';
$html .= '<option value="lowest">' . __('Lowest rating', 'av') . '</option>';
$html .= '<option value="most_useful">' . __('The most helpful', 'av') . '</option>';
$html .= '<option style="display:none;" value="rate">' . __('Rate', 'av') . '</option>';
$html .= '</select></label>';
$html .= '</div>';
$html .= '</div>';
$html .= '<div id="netreviews_media_modal">';
$html .= '<div id="netreviews_media_content"></div>';
$html .= '<a id="netreviews_media_close">×</a>';
$html .= '</div>';
$html .= '<div id="netreviews_reviews_section">';
$html .= '<div id="avisVerifiesAjaxImage"></div>';
$html .= '<div id="ajax_comment_content">';
$html .= appelAjax($id_product, $my_current_lang);
$html .= '</div>';
if ($maxPages > 1) {
    $html .= '<div id="netreviews_button_more_reviews">';
    $html .= '<div class="netreviews_button" data-idProd="' . $id_product . '" id="av_load_next_page" data-page="1" data-page-last="' . $maxPages . '"  onclick="paginationReviews(event);">';
    $html .= '<span class="display">' . __('Display more reviews', 'av') . '</span>';
    $html .= '<i class="netreviews_arrow_down"></i>';
    $html .= '</div>';
    $html .= '</div>';
}
$html .= '</div>';
$html .= '</td>';
$html .= '</tr>';
$html .= '</tbody>';
$html .= '</table>';
$html .= '</div>';
if (ntav_getConfig('OUR_PRODUCT_RICHSNIP', $my_current_lang) == 1 && ntav_getConfig('TYPE_SNIPPET', $my_current_lang) == 'microdata' && ntav_getConfig('NEED_COMPLETE_RS', $my_current_lang) == 1) {
    $html .= '</div>';
}

return $html;
