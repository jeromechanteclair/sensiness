<?php

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

current_user_can('administrator');

require_once(ABSPATH . 'wp-admin/admin.php');
require_once(ABSPATH . 'wp-admin/admin-header.php');

$current_lang = get_locale();
$lang_for_localize = explode('_', $current_lang);
$lang_for_localize = $lang_for_localize[0];

wp_enqueue_style("av_backoffice", plugins_url('css/av_backoffice.css', __FILE__));
wp_enqueue_style("datepicker_css", "//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css");
wp_enqueue_style(
    "datepicker_css_fontawesome",
    "https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"
);
wp_enqueue_script(
    "av_backoffice",
    plugins_url('/js/av_backoffice.js', __FILE__),
    array('jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-datepicker'),
    false,
    false
);
wp_enqueue_script(
    "av_backoffice_dialog",
    plugins_url('/js/av_backoffice.js', __FILE__),
    array('jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-dialog'),
    false,
    false
);
wp_enqueue_script("datepicker_i18n", plugins_url('/js/datepicker/datepicker-' . $lang_for_localize . '.js', __FILE__));
wp_enqueue_script("fontawesome_nr", "https://kit.fontawesome.com/fd565dd76c.js");

// $pluginImagesURL = plugins_url('includes/images/', dirname(__FILE__));
// $listImgByLang = ntav_get_img_by_lang();

ntav_columns_if_not_exists();

global $wc_product_attributes;
$av_link = wp_upload_dir();
set_time_limit(3600);

if (function_exists('wc_get_order_statuses')) {
    $list_status = wc_get_order_statuses();
} else {
    $res = ntav_get_list_order_status();
    foreach ($res as $status) {
        $list_status[$status->slug] = $status->slug;
    }
}
$logoFile = content_url() . '/plugins/netreviews/includes/images/' . ntav_get_img_by_lang()['sceau_lang'];

?>

<!-- ########################################### -->
<!-- PRESENTATION -->
<!-- ########################################### -->
<div class="avpres_wrap">
    <div class="avpres_vagues_top">
        <div class="nr-sprite nr-sprite-vagues_haut"></div>
    </div>
    <div class="avpres_logo">
        <div class="nr-sprite nr-sprite-logo_full_<?php echo ntav_get_user_locale(); ?>"></div>
    </div>
    <div class="avpres_col_left">
        <h1><?php _e('Use the voice of your customers and boost your sales', 'av') ?> !</h1>
        <p>
            <span><?php _e(
                'Verified Reviews is a trusted third party specialized in the collect of seller and product reviews, the control and publication of those',
                'av'
) ?>.</span>
            <span><?php _e('Measure your shopper satisfaction and improve the customer experience', 'av') ?>.</span>
            <span><?php _e(
                'Finally, offer a better visibility to your brand by showing product reviews and influence the choice of your customers consequently',
                'av'
            ) ?>.</span>
        </p>
        <div class="avpictos">
            <div class="nr-sprite nr-sprite-collecte_multicanal_<?php echo ntav_get_user_locale(); ?>"></div>
            <div class="nr-sprite nr-sprite-valorisation_client_<?php echo ntav_get_user_locale(); ?>"></div>
            <div class="nr-sprite nr-sprite-mesure_analyse_<?php echo ntav_get_user_locale(); ?>"></div>
            <div class="nr-sprite nr-sprite-diffusion_<?php echo ntav_get_user_locale(); ?>"></div>
            <div class="nr-sprite nr-sprite-engagement_equipes_<?php echo ntav_get_user_locale(); ?>"></div>
        </div>
    </div>
    <div class="avpres_col_bottom">
        <div class="avpres_align_left">
            <div class="certif"><?php _e('Official partners of', 'av') ?>:</div>
            <div class="nr-sprite nr-sprite-partnership"></div>
        </div>
        <div class="avpres_align_center">
            <a href=<?php _e('http://www.verified-reviews.co.uk/', 'av'); ?> target="_blank">
                <div class="nr-sprite nr-sprite-start_<?php echo ntav_get_user_locale(); ?>"></div>
            </a>
        </div>
        <div class="avpres_align_right">
            <div class="nr-sprite nr-sprite-vagues_bas"></div>
        </div>
    </div>
</div>


<?php
$WPML_config = '';
$s_keyArray = array();
$id_webArray = array();

if (
    is_plugin_active('wpml-string-translation/plugin.php') && is_plugin_active(
        'woocommerce-multilingual/wpml-woocommerce.php'
    )
) {
    $WPML_config = ntav_WPMLoption();
}

$secret_key = ntav_getConfig('SECRET_KEY', null);
$id_website = ntav_getConfig('ID_WEBSITE', null);
$active = ntav_getConfig('IS_ACTIVE', 'non');
$mpn = ntav_getConfig('ATTRIBUTE_MPN', 'non');
$gtin = ntav_getConfig('ATTRIBUTE_GTIN', 'non');
$brand = ntav_getConfig('ATTRIBUTE_BRAND', 'non');
$specificStyle = ntav_getConfig('SPECIFIC_STYLE', 'non');
$responsive = ntav_getConfig('RESPONSIVE', 'non');
$wpmlActive = ntav_getConfig('WPMLENABLE', 'non');
$designWidget = ntav_getConfig('DESIGN_PRODUCT_PAGE', 'non');
$choosenTemplate = ntav_getConfig('TEMPLATE_PRODUCT_PAGE', 'non');
$colourStars = ntav_getConfig('COLOUR_STARS_AV', 'non');
$numberReviews = ntav_getConfig('NUMBER_REVIEWS_DISPLAYED', 'non');
$starsCategoryPage = ntav_getConfig('STARS_CATEGORY_PAGE', 'non');
$enableHelpful = ntav_getConfig('HELPFULOPTION', 'non');
$enableMedia = ntav_getConfig('MEDIADISPLAY', 'non');

if (!ntav_getConfig('DESIGN_PRODUCT_PAGE', 'non')) {
    ntav_updateValue('DESIGN_PRODUCT_PAGE', 1, null, null);
}

if (!ntav_getConfig('TEMPLATE_PRODUCT_PAGE', 'non')) {
    ntav_updateValue('TEMPLATE_PRODUCT_PAGE', 1, null, null);
}

//#################################################################
// ACTION DESIGN
//#################################################################

if (!empty($_POST['designform'])) {
    check_admin_referer('protectform', 'nonce1');
    $specificStyle = $_POST['specificStyle'];
    $responsive = $_POST['responsive'];
    $designWidget = $_POST['widget_version_chosen'];
    $choosenTemplate = $_POST['template_version_chosen'];
    $colourStars = $_POST['colourStars'];
    $numberReviews = $_POST['numberReviews'];
    $starsCategoryPage = $_POST['starsCategorypage'];
    $enableHelpful = $_POST['enable_helpful_reviews'];
    $enableMedia = $_POST['enable_media'];
    ntav_updateValue('SPECIFIC_STYLE', $specificStyle, null, null);
    ntav_updateValue('RESPONSIVE', $responsive, null, null);
    ntav_updateValue('DESIGN_PRODUCT_PAGE', $designWidget, null, null);
    ntav_updateValue('TEMPLATE_PRODUCT_PAGE', $choosenTemplate, null, null);
    ntav_updateValue('COLOUR_STARS_AV', $colourStars, null, null);
    ntav_updateValue('NUMBER_REVIEWS_DISPLAYED', $numberReviews, null, null);
    ntav_updateValue('STARS_CATEGORY_PAGE', $starsCategoryPage, null, null);
    ntav_updateValue('HELPFULOPTION', $enableHelpful, null, null);
    ntav_updateValue('MEDIADISPLAY', $enableMedia, null, null); ?>

    <div class="updated fade">
        <p><?php _e('Changes saved.', 'av'); ?></p>
    </div>
    <?php
}

//#################################################################
// ACTION CONFIGURATION //
//#################################################################

if (!empty($_POST['confform'])) {
    check_admin_referer('protectform', 'nonce1');

    $active = isset($_POST['module']) ? sanitize_text_field($_POST['module']) : 0;
    if (isset($_POST['WPML'])) {
        $wpmlActive = sanitize_text_field($_POST['WPML']);
    } else {
        $wpmlActive = 'no';
    }
    $mpn = sanitize_text_field($_POST['key_mpn']);
    $gtin = sanitize_text_field($_POST['key_gtin']);
    $brand = sanitize_text_field($_POST['key_brand']);


    if (
        is_plugin_active('wpml-string-translation/plugin.php') && is_plugin_active(
            'woocommerce-multilingual/wpml-woocommerce.php'
        ) && $wpmlActive == 'yes'
    ) {
        foreach ($WPML_config as $key => $value) {
            $lang = explode(".", $value);
            $s_key = sanitize_text_field($_POST['s_key_' . strtoupper($lang[0])]);
            $id_web = sanitize_text_field($_POST['id_web_' . strtoupper($lang[0])]);
            ntav_updateValue('SECRET_KEY', $s_key, strtoupper($lang[0]), $wpmlActive);
            ntav_updateValue('ID_WEBSITE', $id_web, strtoupper($lang[0]), $wpmlActive);
        }
    } else {
        $secret_key = sanitize_text_field($_POST['key']);
        $id_website = sanitize_text_field($_POST['id_web']);
        ntav_updateValue('SECRET_KEY', $secret_key, '', 'no');
        ntav_updateValue('ID_WEBSITE', $id_website, '', 'no');
    }
    ntav_updateValue('WPMLENABLE', $wpmlActive, '', null);
    ntav_updateValue('IS_ACTIVE', $active, '', null);
    ntav_updateValue('ATTRIBUTE_MPN', $mpn, '', null);
    ntav_updateValue('ATTRIBUTE_GTIN', $gtin, '', null);
    ntav_updateValue('ATTRIBUTE_BRAND', $brand, '', null);

    ntav_updateVersion(); ?>


    <div class="updated fade">
        <p><?php _e('Changes saved.', 'av'); ?></p>
    </div>
    <?php
}


if ($wpmlActive == 'yes') {
    foreach ($WPML_config as $key => $value) {
        if (!empty($value)) {
            $lang = explode(".", $value);
            $s_key = ntav_getConfig('SECRET_KEY', strtoupper($lang[0]));
            $id_web = ntav_getConfig('ID_WEBSITE', strtoupper($lang[0]));

            array_push($s_keyArray, $s_key);
            array_push($id_webArray, $id_web);
        }
    }
}


//#################################################################
// ACTION FORMULAIRE D'EXPORT
//#################################################################

if (!empty($_POST['exportform'])) {
    $start_export_date = strip_tags($_POST['start']);
    $end_export_date = strip_tags($_POST['end']);
    $locale = $_POST['locale_for_datepicker'];
    $response = sanitize_text_field($_POST['info_prod']);
    $status = isset($_POST['status']) ? ($_POST['status']) : array();

    if (is_array($status)) {
        foreach ($status as $state) {
            $state = esc_attr($state);
        }
    } else {
        $status = esc_attr($status);
    }

    $prefix_attr = 'pa_';
    $label_mpn = ntav_getConfig('ATTRIBUTE_MPN');
    $label_gtin = ntav_getConfig('ATTRIBUTE_GTIN');
    $label_brand = ntav_getConfig('ATTRIBUTE_BRAND');
    $tmp_status = array();
    foreach ($status as $tmp_value) {
        $tmp_status[] = "'" . $tmp_value . "'";
    }
    $whereStatusChosen = implode(',', $tmp_status);
    $random_name = ntav_get_random_name();
    //$query_date = ntav_get_query_between_date($start_export_date, $end_export_date, $locale);

    $results = ntav_getOrdersCSVByWooCommerceVersion($whereStatusChosen, $start_export_date, $end_export_date, $locale);

    if (!empty($results)) {
        // infos et creation du fichier.
        $flag = 0; //flag pour savoir s'il y a une commande valide pour l'intervalle de temps.
        $delimiter = ";";
        $name_file = $av_link['basedir'] . '/' . $random_name . '.csv';
        $file_csv = fopen($name_file, 'w+');
        //ecriture du header.
        $header_csv = array(
            'order_id',
            'email',
            'custom_first_name',
            'custom_last_name',
            'order_date',
            'amount_order',
            'order_status',
            'product_id',
            'product_name',
            'url_product',
            'url _image_ product',
            'MPN',
            'GTIN_EAN',
            'brand_name'
        );
        $tmp_results = array();
        // Si n'exporte pas les avis produits
        if ($response == 'no') {
            unset($header_csv[7], $header_csv[8], $header_csv[9], $header_csv[10], $header_csv[11], $header_csv[12], $header_csv[13]);
        }
        fputcsv($file_csv, $header_csv, $delimiter);
        //ecriture du fichier.
        foreach ($results as $line) {
            try {
                $product = new WC_Product($line->product_id);
            } catch (Exception $e) {
                echo($e->getMessage());
                continue;
            }

            $linetmp['order_id'] = $line->refcommande;
            $linetmp['email'] = $line->email;
            $linetmp['custom_first_name'] = utf8_decode($line->custom_first_name);
            $linetmp['custom_last_name'] = utf8_decode($line->custom_last_name);
            $linetmp['order_date'] = $line->datecommande;
            $linetmp['amount_order'] = ($line->amount_order * 100);
            $linetmp['order_status'] = str_replace('wc-', '', $line->status_order);

            // Si on exporte aussi les avis produits
            if ($response != 'no') {
                $linetmp['product_id'] = $line->product_id;
                if ($wpmlActive == 'yes') {
                    $my_current_lang = apply_filters('wpml_current_language', null);
                    $post_id = apply_filters('wpml_object_id', $product->get_id(), 'post', true, $my_current_lang);
                    $traduced_product = new WC_Product($post_id);
                    $linetmp['product_name'] = htmlspecialchars_decode(utf8_decode($traduced_product->get_name()));
                    $linetmp['url_product'] = get_post_permalink($post_id);
                } else {
                    $linetmp['product_name'] = htmlspecialchars_decode(utf8_decode($line->product_name));
                    $linetmp['url_product'] = get_post_permalink($line->product_id);
                }
                $linetmp['url _image_ product'] = ntav_get_image_url(
                    get_the_post_thumbnail($line->product_id)
                ); // get the src from img tag
                $tabmpn = wc_get_product_terms(
                    $product->get_id(),
                    $prefix_attr . $label_mpn,
                    array('fields' => 'names')
                );
                $tabgtin = wc_get_product_terms(
                    $product->get_id(),
                    $prefix_attr . $label_gtin,
                    array('fields' => 'names')
                );
                $tabbrand = wc_get_product_terms(
                    $product->get_id(),
                    $prefix_attr . $label_brand,
                    array('fields' => 'names')
                );
                $linetmp['MPN'] = array_shift($tabmpn);
                $linetmp['GTIN_EAN'] = array_shift($tabgtin);
                $linetmp['brand_name'] = htmlspecialchars_decode(utf8_decode(array_shift($tabbrand)));
            }

            if (fputcsv($file_csv, $linetmp, $delimiter) != false) {
                $flag = 1;
            }
        }
        fclose($file_csv);
    }
    if (isset($flag) && $flag == 1) : ?>
        <div id="dialog" class="manualExport">
            <p><?php _e("Download the export : ", 'av'); ?>&nbsp;<a class="button action"
                                                                    href="<?php echo $av_link['baseurl'] . '/' . $random_name . '.csv'; ?>"><?php _e(
                                                                        'Download',
                                                                        'av'
                                                                    ); ?></a>
            </p>
        </div>
    <?php else : ?>
        <div id="dialog" class="manualExport">
            <p><?php _e('No orders in this time slot.', 'av'); ?></p>
        </div>
        <?php if (isset($name_file) && file_exists($name_file)) :
            unlink($name_file);
        endif;
    endif;
}
?>


<!-- ########################################### -->
<!-- TABS -->
<!-- ########################################### -->

<div class="avcontent_wrap">

    <!-- ########################################### -->
    <!-- CONFIGURATION -->
    <!-- ########################################### -->

    <button class="accordion active"><?php _e('Settings', 'av') ?></button>

    <div class="panelaccordion panelaccordionconfig" style="max-height: 10000px;">

        <form class="champ form-horizontal col-md-4" name="av_form_conf" method="post">
            <?php wp_nonce_field('protectform', 'nonce1'); ?>
            <input type="hidden" name="confform" value="1"/>

            <div class="items form-group">
                <label for="one"><?php _e('Module Activation :', 'av'); ?></label>
                <input id="one" type="radio" name="module"
                       value="1" <?php if ($active != null && $active == 1) :
                            echo 'checked';
                                 endif; ?> /><?php _e(
                                     'Yes',
                                     'av'
                                 ); ?>
                <input id="two" type="radio" name="module"
                       value="0" <?php if ($active != null && $active == 0) :
                            echo 'checked';
                                 endif; ?> /><?php _e(
                                     'No',
                                     'av'
                                 ); ?>
            </div>
            <br/>

            <!-- ########################################### -->
            <!-- WPML -->
            <!-- ########################################### -->

            <?php if (!empty($WPML_config)) {
                    echo '<label class="WPMLConfig">';
                    _e('Are you using WPML plugin?', 'av');
                    echo '</label>'; ?>

                <div class="items form-group">


                    <input id="yesWPML" type="radio" name="WPML"
                           value="yes" <?php if ($wpmlActive != null && $wpmlActive == 'yes') :
                                echo 'checked';
                                       endif; ?> onclick="displayWPML()"/><?php _e('Yes', 'av'); ?>
                    <input id="noWPML" type="radio" name="WPML"
                           value="no" <?php if ($wpmlActive != null && $wpmlActive == 'no') :
                                echo 'checked';
                                      endif; ?> onclick="displayWPML()"/><?php _e('No', 'av'); ?>

                </div>
                <?php
            } ?>

            <?php if (!empty($WPML_config)) : ?>
                <u><p class="paragraphsecretkeyWpml"><?php _e(
                    'Please, insert your secret key and website id, corresponding to the backoffice of each shop language.',
                    'av'
                ); ?></p></u>


                <?php foreach ($WPML_config as $key => $value) { ?>
                    <?php $lang = explode(".", $value); ?>
                    <div class="elem divsecretkeyWPML" style="display: none;">

                        <div class="form-group">
                            <label for="s_key"><b><?php _e('Secret Key : ', 'av'); ?></b></label><input
                                    class="av_input form-control" id="s_key_<?php echo strtoupper($lang[0]); ?>"
                                    type="text" size="36" name="s_key_<?php echo strtoupper($lang[0]); ?>"
                                    value="<?php if (isset($s_keyArray[$key]) && $s_keyArray[$key] != null) :
                                        echo "$s_keyArray[$key]";
                                           endif; ?>"/>
                        </div>
                        <img src='../wp-content/plugins/sitepress-multilingual-cms/res/flags/<?php echo $value; ?>'
                             alt='<?php echo $value; ?>' height="50px" style="float: right;">
                        <div class="form-group">
                            <label for="id_web"><b><?php _e('ID Website : ', 'av'); ?></b></label><input
                                    class="av_input form-control" id="id_web_<?php echo strtoupper($lang[0]); ?>"
                                    type="text" size="36" name="id_web_<?php echo strtoupper($lang[0]); ?>"
                                    value="<?php if (isset($id_webArray[$key]) && $id_webArray[$key] != null) :
                                        echo "$id_webArray[$key]";
                                           endif; ?>"/>
                        </div>
                    </div>
                <?php }
            endif; ?>

            <!-- ########################################### -->
            <!-- WPML -->
            <!-- ########################################### -->


            <div class="elem divsecretkey">
                <p class="paragraphsecretkey"><?php _e('Please check your customer area on ', 'av');
                    echo " <b>";
                    _e('verified-reviews.co.uk', 'av');
                    echo "</b> ";
                    _e(' to see your login data.', 'av'); ?></p>
                <div class="form-group responsive-form-group">
                    <label for="s_key"><b><?php _e('Secret Key : ', 'av'); ?></b></label><input
                            class="av_input form-control" id="s_key" type="text" size="36" name="key"
                            value="<?php if ($secret_key != null) :
                                echo $secret_key;
                                   endif; ?>"/>
                </div>
                <div class="form-group responsive-form-group">
                    <label for="id_web"><b><?php _e('ID Website : ', 'av'); ?></b></label><input
                            class="av_input form-control" id="id_web" type="text" size="36" name="id_web"
                            value="<?php if ($id_website != null) :
                                echo $id_website;
                                   endif; ?>"/>
                </div>
            </div>

            <br/>

            <p class="form-group googleshopingtext"><?php _e(
                'Product information to associate (required for Google Shopping) :',
                'av'
            ); ?></p>
            <div class="elem ">
                <div class="form-group">
                    <label for="key_mpn"><?php _e('MPN : ', 'av'); ?></label>
                    <select name="key_mpn">
                        <option></option>
                        <?php foreach ($wc_product_attributes as $attribute) {
                            if ($attribute->attribute_name == $mpn) {
                                echo '<option value="' . $attribute->attribute_name . '" selected>' . $attribute->attribute_label . '</option>';
                            } else {
                                echo '<option value="' . $attribute->attribute_name . '">' . $attribute->attribute_label . '</option>';
                            }
                        } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="key_gtin"><?php _e('GTIN : ', 'av'); ?></label>
                    <select name="key_gtin">
                        <option></option>
                        <?php foreach ($wc_product_attributes as $attribute) {
                            if ($attribute->attribute_name == $gtin) {
                                echo '<option value="' . $attribute->attribute_name . '" selected>' . $attribute->attribute_label . '</option>';
                            } else {
                                echo '<option value="' . $attribute->attribute_name . '">' . $attribute->attribute_label . '</option>';
                            }
                        } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_web"><?php _e('Brand : ', 'av'); ?><b/></label>
                    <select name="key_brand">
                        <option></option>
                        <?php foreach ($wc_product_attributes as $attribute) {
                            if ($attribute->attribute_name == $brand) {
                                echo '<option value="' . $attribute->attribute_name . '" selected>' . $attribute->attribute_label . '</option>';
                            } else {
                                echo '<option value="' . $attribute->attribute_name . '">' . $attribute->attribute_label . '</option>';
                            }
                        } ?>
                    </select>
                </div>
            </div>

            <br/>
            <input class="av_button" name="submit" type="submit" value="<?php _e('Save', 'av'); ?>"/>
            <br/>
            <br/>

        </form>
    </div>


    <!-- ########################################### -->
    <!-- DESIGN -->
    <!-- ########################################### -->

    <button class="accordion active"><?php _e('Design', 'av') ?></button>
    <div class="panelaccordion paneldesign" style="max-height: 10000px;">
        <form class="champ form-horizontal col-md-4" name="av_form_design" method="post">
            <?php wp_nonce_field('protectform', 'nonce1'); ?>
            <input type="hidden" name="designform" value='1'/>
            <table class="table_design">
                <tr>
                    <td class='responsive responsive-td-design'>
                        <div class="choose_widget_stars_question"><?php _e(
                            'Select a design for product page stars:',
                            'av'
                        ) ?></div>
                    </td>
                    <td class="choose_widget_stars">
                        <div class="templatewidget_div templatewidget_div_first">
                            <div class="stars_widget_div">
                                <div class="img_widget_to_choose headerStars <?php if ($designWidget == 1) {
                                    ?> widget-active<?php
                                                                             } ?> nr-sprite-responsive"
                                     data-version="1">
                                    <div class="netreviews_bg_stars_big">
                                        <div>
                                            <span class="nr-icon nr-star grey"></span>
                                            <span class="nr-icon nr-star grey"></span>
                                            <span class="nr-icon nr-star grey"></span>
                                            <span class="nr-icon nr-star grey"></span>
                                            <span class="nr-icon nr-star grey"></span>
                                        </div>
                                        <div style="color: #<?php if (!empty($colourStars)) {
                                            echo $colourStars;
                                                            } else {
                                                                echo 'FFCD00';
                                                            } ?> !important">
                                            <span class="nr-icon nr-star"></span>
                                            <span class="nr-icon nr-star"></span>
                                            <span class="nr-icon nr-star"></span>
                                            <span class="nr-icon nr-star"></span>
                                            <span class="nr-icon nr-star"></span>
                                        </div>
                                    </div>
                                    <span class="reviewCount">5 </span><?php echo " " . __('Reviews', 'av'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="templatewidget_div">
                            <div class="img_widget_to_choose <?php if ($designWidget == 2) {
                                ?> widget-active<?php
                                                             } ?> nr-sprite-responsive"
                                 data-version="2">
                                <div class="netreviewsProductWidgetNew">
                                    <img src="<?php echo $logoFile; ?>" class="netreviewsProductWidgetNewLogo" alt="netreviews widget">
                                    <div class="ProductWidgetNewRatingWrapper">
                                        <div class="netreviews_review_rate_and_stars">
                                            <div class="netreviews_font_stars">
                                                <div>
                                                    <span class="nr-icon nr-star grey"></span><span
                                                            class="nr-icon nr-star grey"></span><span
                                                            class="nr-icon nr-star grey"></span><span
                                                            class="nr-icon nr-star grey"></span><span
                                                            class="nr-icon nr-star grey"></span></div>
                                                <div style="color: #<?php if (!empty($colourStars)) {
                                                    echo $colourStars;
                                                                    } else {
                                                                        echo 'FFCD00';
                                                                    } ?> !important">
                                                    <span class="nr-icon nr-star"></span><span
                                                            class="nr-icon nr-star"></span><span
                                                            class="nr-icon nr-star"></span><span
                                                            class="nr-icon nr-star"></span><span class="nr-icon nr-star"
                                                                                                 style="width:10%;display: inline-flex;"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="netreviewsProductWidgetNewRate">
                                            <span class="ratingValue">5</span>/<span class="bestRating">5</span>
                                        </div>
                                        <div id="AV_button"><?php echo ' ' . __('See all reviews', 'av') ?>
                                            (<span>5</span>)
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="templatewidget_div">
                            <div class="img_widget_to_choose <?php if ($designWidget == 3) {
                                ?> widget-active<?php
                                                             } ?> nr-sprite-responsive"
                                 data-version="3">
                                <div class="av_product_award">
                                    <div id="top">
                                        <div class="netreviews_font_stars">
                                            <div>
                                                <span class="nr-icon nr-star grey"></span><span
                                                        class="nr-icon nr-star grey"></span><span
                                                        class="nr-icon nr-star grey"></span><span
                                                        class="nr-icon nr-star grey"></span><span
                                                        class="nr-icon nr-star grey"></span></div>
                                            <div style="color: #<?php if (!empty($colourStars)) {
                                                echo $colourStars;
                                                                } else {
                                                                    echo 'FFCD00';
                                                                } ?>">
                                                <span class="nr-icon nr-star"></span><span
                                                        class="nr-icon nr-star"></span><span
                                                        class="nr-icon nr-star"></span><span
                                                        class="nr-icon nr-star"></span><span class="nr-icon nr-star"
                                                                                             style="width:10%;display: inline-flex;"></span>
                                            </div>
                                        </div>
                                        <div class="ratingText">
                                            5 avis
                                        </div>
                                    </div>
                                    <div id="AV_button" style="background: #<?php if (!empty($colourStars)) {
                                        echo $colourStars;
                                                                            } else {
                                                                                echo '#FFCD00';
                                                                            } ?>">
                                        <?php echo ' ' . __('See all reviews', 'av') ?></div>
                                    <img id="sceau" src="<?php echo $logoFile; ?>" alt="netreviews seal">
                                </div>
                            </div>
                        </div>

                        <input id="widget_version_chosen" type="hidden" name="widget_version_chosen"
                               value="<?php echo $designWidget; ?>">
                    </td>
                </tr>

                <tr>
                    <td class='responsive responsive-td-design'>
                        <div><?php _e('Choose the template of reviews block:', 'av') ?></div>
                    </td>
                    <td class="choose_template">
                        <div class="template_img_div">
                            <div class="template_img <?php if ($choosenTemplate == 1) {
                                ?> tpl-active<?php
                                                     } ?> nr-sprite-tpl1"
                                 data-version="1"></div>
                        </div>
                        <div class="template_img_div">
                            <div class="template_img <?php if ($choosenTemplate == 2) {
                                ?> tpl-active<?php
                                                     } ?> nr-sprite-tpl2"
                                 data-version="2"></div>
                        </div>
                        <input id="template_version_chosen" type="hidden" name="template_version_chosen"
                               value="<?php echo $choosenTemplate; ?>">
                    </td>
                </tr>
            </table>
            <table class="table_design_details">

                <tr>
                    <td class='responsive responsive-td-design min-responsive'>
                    <span class="design-question">
                    <?php _e('Change stars colour:', 'av') ?>
                    </span>
                    </td class="responsive-align">
                    <td>
                        <input class="jscolor" name='colourStars' value="<?php if (!empty($colourStars)) {
                                echo $colourStars;
                                                                         } else {
                                                                             echo '#FFCD00';
                                                                         } ?>" style="border-radius: 5px;">
                    </td>
                </tr>

                <tr>
                    <td class='responsive responsive-td-design min-responsive'>
                    <span class="design-question">
                    <?php _e('Number of reviews to display:', 'av') ?>
                    </span>
                    </td>
                    <td class="responsive-align">
                        <input type="number" id="inputNumberReviews" name="numberReviews" style="border-radius: 5px;"
                               value='<?php if (!empty($numberReviews)) {
                                    echo $numberReviews;
                                      } else {
                                          echo 5;
                                      } ?>' min=1>
                    </td>
                </tr>

                <tr>
                    <td class='responsive responsive-td-design min-responsive'>
                    <span class="design-question">
                    <?php _e('Display stars in category page:', 'av') ?>
                    </span>
                    </td>
                    <td class="responsive-align">
                        <input class="responsive1" type="radio" name="starsCategorypage"
                               value='yes' <?php if ($starsCategoryPage == null || $starsCategoryPage == 'yes') :
                                    echo 'checked';
                                           endif; ?>><?php _e(
                                               'Yes',
                                               'av'
                                           ); ?>
                        <input class="responsive2" type="radio" name="starsCategorypage"
                               value='no' <?php if ($starsCategoryPage == 'no') :
                                    echo 'checked';
                                          endif; ?>> <?php _e(
                                              'No',
                                              'av'
                                          ); ?>
                    </td>
                </tr>

                <tr>
                    <td class='responsive responsive-td-design min-responsive'>
                    <span class="design-question">
                    <?php _e('Add helpful option to your reviews', 'av') ?>
                    </span>
                    </td>
                    <td class="responsive-align">
                        <input class="responsive1" type="radio" id="display_helpful_reviews_yes"
                               name="enable_helpful_reviews"
                               value='yes' <?php if ($enableHelpful == null || $enableHelpful == 'yes') :
                                    echo 'checked';
                                           endif; ?>><?php _e(
                                               'Yes',
                                               'av'
                                           ); ?>
                        <input class="responsive2" type="radio" id="display_helpful_reviews_no"
                               name="enable_helpful_reviews"
                               value='no' <?php if ($enableHelpful == 'no') :
                                    echo 'checked';
                                          endif; ?>> <?php _e(
                                              'No',
                                              'av'
                                          ); ?>
                    </td>
                </tr>

                <tr>
                    <td class='responsive responsive-td-design min-responsive'>
                    <span class="design-question">
                    <?php _e('Display medias with product reviews', 'av') ?>
                    </span>
                    </td>
                    <td class="responsive-align">
                        <input class="responsive1" type="radio" id="enable_media_reviews_yes" name="enable_media"
                               value='yes' <?php if ($enableMedia == null || $enableMedia == 'yes') :
                                    echo 'checked';
                                           endif; ?> /><?php _e(
                                               'Yes',
                                               'av'
                                           ); ?>
                        <input class="responsive2" type="radio" id="enable_media_reviews_no" name="enable_media"
                               value='no' <?php if ($enableMedia == 'no') :
                                    echo 'checked';
                                          endif; ?> /> <?php _e(
                                              'No',
                                              'av'
                                          ); ?>
                    </td>
                </tr>

                <tr id="use-responsive-design">
                    <td class="responsive responsive-td-design min-responsive">
                    <span class="design-question">
                    <?php _e('Use reduced responsive design in product reviews ?', 'av') ?>
                    </span>
                    </td>
                    <td class="responsive-align">
                        <input class="responsive1" type="radio" name="responsive"
                               value="1" <?php if ($responsive != null && $responsive == 1) :
                                    echo 'checked';
                                         endif; ?> /><?php _e(
                                             'Yes',
                                             'av'
                                         ); ?>
                        <input class="responsive2" type="radio" name="responsive"
                               value="0" <?php if ($responsive == null || $responsive == 0) :
                                    echo 'checked';
                                         endif; ?> /><?php _e(
                                             'No',
                                             'av'
                                         ); ?>
                    </td>
                </tr>

                <tr class="responsive-specific-css">
                    <td class="responsive responsive-td-design">
                        <?php _e('Specific CSS :', 'av') ?>
                    </td>
                    <td>
                        <textarea name="specificStyle" rows="5" cols="50"><?php echo $specificStyle ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input class="av_button" name="submit" type="submit" value="<?php _e('Save', 'av'); ?>"/>
                    </td>
                </tr>
            </table>
            <br/>
            <br/>
        </form>
    </div>

    <!-- ########################################### -->
    <!-- EXPORT MANUEL -->
    <!-- ########################################### -->

    <button class="accordion active"><?php _e('Manual export', 'av') ?></button>
    <div class="panelaccordion" style="max-height: 10000px;">
        <form class="export_form" id="av_form_export" name="av_form_export" method="post">
            <input type="hidden" name="exportform" value="1"/>
            <input type="hidden" name="locale_for_datepicker" id="locale_for_datepicker"
                   value="<?php echo $current_lang ?>"/>
            <div>
                <h2><?php _e(
                    'Export your recently received orders to collect immediately your first customer reviews and to show your certificate Verified Reviews.',
                    'av'
                ); ?></h2>
            </div>
            <div class="errorDateMessage"><?php _e('The first date must be older than the second', 'av') ?></div>
            <div class="input-daterange input-group row" id="date_range_datepicker_export">
                <div class="col-md-4 col-sm-12">
                    <div class="input-group-addon"><?php _e('From', 'av') ?></div>
                    <input type="text" id="from_datepicker" class="input-sm form-control" name="start"
                           autocomplete="off" required/>
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="input-group-addon"><?php _e('to', 'av') ?></div>
                    <input type="text" id="to_datepicker" class="input-sm form-control" name="end" autocomplete="off"
                           required/>
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
            <table class="table_export" id="manualExportNr">
                <tr>
                    <td class="responsive-td" width="250px">
                        <?php _e('Collect Product Reviews :', 'av') ?> *
                    </td>
                    <td>
                        <select id="get_prod" type="text" name="info_prod">
                            <option value="yes"><?php _e('Yes', 'av') ?></option>
                            <option value="no"><?php _e('No', 'av') ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="responsive-td">
                        <?php _e('Orders status :', 'av') ?>
                    </td>
                    <td>
                        <?php $i = 0;
                        $list_status = (!empty($list_status)) ? $list_status : array(); ?>
                        <?php foreach ($list_status as $key => $status) : ?>
                            <?php if ($i == 0) : ?>
                                <input id="status_<?php echo $i; ?>" type="checkbox" name="status[]"
                                       value="<?php echo $key; ?>"/><label class="export_label_checkbox"
                                                                           for="status_<?php echo $i; ?>"><?php echo $status; ?></label>
                                <br/>
                            <?php else : ?>
                                <input id="status_<?php echo $i; ?>" type="checkbox" name="status[]"
                                       value="<?php echo $key; ?>"/><label class="export_label_checkbox"
                                                                           for="status_<?php echo $i; ?>"><?php echo $status; ?></label>
                                <br/>
                            <?php endif; ?>
                            <?php ++$i; ?>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input class="av_button" id="exportOrders" type="submit" value="<?php _e('export', 'av') ?>"/>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="av_help_export">
                        * <?php _e(
                            'Without Product Reviews : Your customers will only be asked for their reviews regarding the order (obligatory).',
                            'av'
                        ); ?>
                        <br/>
                        * <?php _e(
                            'With Product Reviews : Your customers will be asked for their review regarding the order (obligatory) and regarding the purchased products as well.',
                            'av'
                        ); ?>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <!--    <div id="dialog" title="Basic dialog">-->
    <!--        <p>Pas de nouvelles commandes</p>-->
    <!--    </div>-->

    <!-- ########################################### -->
    <!-- VERIFIER L'INSTALLATION -->
    <!-- ########################################### -->

    <?php $list_tables = ntav_get_list_tables(); ?>
    <button class="accordion"><?php _e('Check installation', 'av') ?></button>
    <div class="panelaccordion">
        <form>
            <table class="tablecheckinstall">
                <tr>
                    <th style="width:250px;" width="250px"><?php _e('Tables :', 'av'); ?></th>
                    <th><?php _e('Status :', 'av'); ?></th>
                </tr>
                <tr>
                    <td class="responsive-td-checkInstall">netreviews_configuration</td>
                    <td>
                        <div id="conf_img" class="nr-sprite nr-sprite-<?php echo (ntav_check_table_exist(
                            'Configuration',
                            $list_tables
                        ) == null) ? "no" : "yes"; ?>"></div>
                    </td>
                </tr>
                <tr>
                    <td class="responsive-td-checkInstall">netreviews_products_reviews</td>
                    <td>
                        <div id="conf_img" class="nr-sprite nr-sprite-<?php echo (ntav_check_table_exist(
                            'Reviews',
                            $list_tables
                        ) == null) ? "no" : "yes"; ?>"></div>
                    </td>
                </tr>
                <tr>
                    <td class="responsive-td-checkInstall">netreviews_products_average</td>
                    <td>
                        <div id="conf_img" class="nr-sprite nr-sprite-<?php echo (ntav_check_table_exist(
                            'Average',
                            $list_tables
                        ) == null) ? "no" : "yes"; ?>"></div>
                    </td>
                </tr>
            </table>
            <table class="tablecheckinstall">
                <tr>
                    <th style="width:250px;" width="250px"><?php _e('Index :', 'av'); ?></th>
                    <th><?php _e('Count :', 'av'); ?></th>
                </tr>
                <tr>
                    <td class="responsive-td-checkInstall">reviews_count</td>
                    <td>
                        <?php if (ntav_check_table_exist('Reviews', $list_tables) == null) : ?>
                            <span class="lebelcount">0</span>
                        <?php else :
                            $nb_rev = ntav_get_count('reviews'); ?>
                            <span class="lebelcount"><?php echo $nb_rev; ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td class="responsive-td-checkInstall">average_count</td>
                    <td>
                        <?php if (ntav_check_table_exist('Average', $list_tables) == null) : ?>
                            <span class="lebelcount">0</span>
                        <?php else :
                            $nb_ave = ntav_get_count('average'); ?>
                            <span class="lebelcount"><?php echo $nb_ave; ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td class="responsive-td-checkInstall">order(s) with flag</td>
                    <td>
                        <?php $nb_fl_1 = ntav_get_count_flag(1); ?>
                        <span class="lebelcount"><?php echo $nb_fl_1; ?></span>
                    </td>
                </tr>
                <tr>
                    <td class="responsive-td-checkInstall">order(s) without flag</td>
                    <td>
                        <?php $nb_fl_0 = ntav_get_count_flag(0); ?>
                        <span class="lebelcount"><?php echo $nb_fl_0; ?></span>
                    </td>
                </tr>
            </table>
        </form>
    </div>


</div>








