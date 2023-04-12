<?php

/*
    /!\ IMPORTANT SET VERSION PLUGIN ON DB
 */
function ntav_update_version_plugin()
{
    ntav_updateValue('MODVERSION', '2.3.12');
}

/*
    Fonction qui installe les tables du plugin et flag toutes les commandes à 1
 */


function ntav_install_db_av()
{
    global $wpdb;
    $time = time();
    $table = $wpdb->prefix . 'postmeta';

    ntav_create_table_conf();
    ntav_create_table_ave();
    ntav_create_table_rev();

    //if old data exists in postmeta, initialize the datas
    $wpdb->query("DELETE FROM $table where meta_key = 'av_flag' or meta_key = 'av_horodate'");


    $value_av_flag = '';
    $value_av_horodate = '';
    if (($list_orders_id = ntav_get_orders()) != null) {
        foreach ($list_orders_id as $key => $id) {
            $data_link = ($key != (count($list_orders_id) - 1)) ? "," : ";";
            $value_av_flag .= "(" . $id . ",'av_flag','1'),";
            $value_av_horodate .= "(" . $id . ",'av_horodate',$time)" . $data_link;
        }
    }

    $wpdb->query("INSERT INTO $table (post_id,meta_key,meta_value) VALUE " . $value_av_flag . $value_av_horodate);
}


/*
    Fonction execute lors de la désinstallation du module qui supprime les tables
 */
function ntav_uninstall_db_av()
{
    global $wpdb;
    $tables = array(
        $wpdb->prefix . 'netreviews_configuration',
        $wpdb->prefix . 'netreviews_products_average',
        $wpdb->prefix . 'netreviews_products_reviews'
    );
    foreach ($tables as $name) {
        $wpdb->query("DROP TABLE " . $name . "");
    }
    delete_post_meta_by_key('av_flag');
    delete_post_meta_by_key('av_horodate');
}

/*
     Fonction qui renvoi true si le module est actif
 */
function ntav_check_isApiActive()
{
    global $wpdb;
    $results = $wpdb->get_results(
        "SELECT value FROM " . $wpdb->prefix . "netreviews_configuration "
        . "AS net_conf WHERE net_conf.name='IS_ACTIVE'"
    );

    if ($results[0]->value == 1) {
        return true;
    }
    return false;
}

/**
 * Fonction qui retourne false si le flag a déjà etait rajoute sinon retourne true
 * @param $id
 * @return bool
 */
function ntav_check_meta_exist($id)
{
    global $wpdb;
    global $table_prefix;
    if (
        $results = $wpdb->get_results(
            "SELECT meta_key FROM " . $table_prefix . "postmeta "
            . "WHERE post_id= '" . $id . "' AND meta_key='av_flag'"
        ) == null
    ) {
        return false;
    }
    return true;
}

/**
 * Fonction qui retourne 1 si $table est bien présent dans la $list_tables sinon retourne null
 * @param $table string contenant le nom d'une table du plugin netreviews
 * @param $list_tables tableau contenant la liste des tables se trouvant dans la bdd
 */
function ntav_check_table_exist($table, $list_tables)
{
    global $wpdb;

    if ($table == 'Configuration') {
        $tabname = $wpdb->prefix . 'netreviews_configuration';
    } elseif ($table == 'Reviews') {
        $tabname = $wpdb->prefix . 'netreviews_products_reviews';
    } else {
        $tabname = $wpdb->prefix . 'netreviews_products_average';
    }

    $i = 0;
    $cpt = 0;
    while (isset($list_tables[$i]) == true) {
        if ($tabname == $list_tables[$i]) {
            $cpt = 1;
        }
        ++$i;
    }
    if ($cpt == 0) {
        return null;
    }
    return 1;
}

/**
 * Fonction qui recupere en bdd la valeur d'une propriete en fonction de la valeur de config passer en parametre
 * @param string $name nom d'une propriete de configuration
 * @return string $myrows[0]->value contient la valeur de la propriete passe en parametre ou renvoi null
 */
function ntav_getConfig($name, $lang = '')
{
    global $wpdb;
    $lang_filter = ($lang && $lang != 'non') ? " AND lang_code='" . $lang . "'" : " AND (lang_code IS NULL OR lang_code ='')";
    $query = "SELECT value FROM " . $wpdb->prefix . "netreviews_configuration WHERE name = '" . $name . "'" . $lang_filter;
    $myrows = $wpdb->get_results($query);
    if (!empty($myrows)) {
        return $myrows[0]->value;
    }
}


function ntav_WPMLoption()
{
    $i = 0;
    global $wpdb;
    $results = $wpdb->get_results(
        "SELECT flag FROM " . $wpdb->prefix . "icl_languages,  " . $wpdb->prefix . "icl_flags WHERE " . $wpdb->prefix . "icl_languages.active=1 AND " . $wpdb->prefix . "icl_languages.code = " . $wpdb->prefix . "icl_flags.lang_code"
    );
    foreach ($results as $result) {
        $list_flags[$i] = $result->flag;
        $i++;
    }
    return $list_flags;
}

/*
    Fonction qui retourne un tableau contenant les noms des tables présentes dans la bdd
 */
function ntav_get_list_tables()
{
    $i = 0;
    global $wpdb;
    $name = 'Tables_in_' . $wpdb->dbname;
    if (($results = $wpdb->get_results("SHOW TABLES")) != null) {
        foreach ($results as $result) {
            $list_tables[$i] = $result->$name;
            ++$i;
        }
        return $list_tables;
    }
    return null;
}

/*
    Fonction qui retourne un tableau contenant tout les id de commande
 */
function ntav_get_orders()
{
    $i = 0;
    global $wpdb;
    global $table_prefix;
    if (
        ($results = $wpdb->get_results(
            "SELECT ID FROM " . $table_prefix . "posts "
            . "WHERE post_type = 'shop_order'"
        )) != null
    ) {
        // var_dump($results);
        foreach ($results as $result) {
            $list_ids[$i] = $result->ID;
            ++$i;
        }
        return $list_ids;
    }
    return null;
}

/*
    Fonction qui retourne le prix d'une commande en fonction de l'id passe en parametre sinon retourne null
    @param entier qui correspond à un id de commande
 */
function ntav_get_order_price($id_order)
{
    global $wpdb;
    global $table_prefix;
    if (
        ($results = $wpdb->get_results(
            "SELECT meta_value FROM " . $table_prefix . "postmeta
        WHERE post_id = '" . $id_order . "' AND meta_key = '_order_total'"
        ))
    ) {
        return $results[0]->meta_value;
    }
    return null;
}

/*
    Fonction qui retourne le nombre de ligne present dans la table passe en parametre
    @param $type string qui correspond au nom d'une table de
 */
function ntav_get_count($type)
{
    global $wpdb;
    if (
        ($results = $wpdb->get_results(
            "SELECT COUNT(*) AS count "
            . "FROM " . $wpdb->prefix . "netreviews_products_" . $type . ""
        )) != null
    ) {
        foreach ($results as $result) {
            $count = $result->count;
        }
        return $count;
    }
    return null;
}

/*
    Fonction qui retourne le nombre de commande flager à 0 ou 1 en fonction du paramètre passe
    @param $value entier doit contenir la valeur 0 pour les commandes non recupere ou 1 pour les commandes deja recupere
 */
function ntav_get_count_flag($value)
{
    global $wpdb;
    global $table_prefix;
    if (
        ($results = $wpdb->get_results(
            "SELECT COUNT(meta_key) AS flag 
        FROM " . $table_prefix . "postmeta WHERE meta_value = " . $value . " "
            . "AND meta_key = 'av_flag'"
        )) != null
    ) {
        foreach ($results as $result) {
            $count = $result->flag;
        }
        return $count;
    }
    return null;
}

/*
    Fonction qui retourne un nom generer aleatoirement
 */
function ntav_get_random_name()
{
    global $wpdb;
    $random_name = 'Export_' . date("Y-m-d") . '_';
    $char = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $random_name .= substr(str_shuffle($char), 0, 10);
    $wpdb->query(
        "UPDATE " . $wpdb->prefix . "netreviews_configuration SET value = '" . $random_name . "' WHERE name = 'RANDOMNAME'"
    );

    return $random_name;
}

/**
 * Fonction qui retourne
 * @param  [type] $time [description]
 * @return [type]       [description]
 */
function ntav_get_query_date($time)
{
    $len = strlen($time);
    $len = $len - 1;

    if ($time[$len] == 'w') {
        $value = rtrim($time, 'w');
        $query_date = "DATE_SUB(now(), INTERVAL " . $value . " WEEK)";
    } elseif ($time[$len] == 'm') {
        $value = rtrim($time, 'm');
        $query_date = "DATE_SUB(now(), INTERVAL " . $value . " MONTH)";
    } else {
        $query_date = "DATE_SUB(now(), INTERVAL 1 YEAR)";
    }
    return $query_date;
}

function ntav_get_list_order_status()
{
    global $wpdb;
    global $table_prefix;
    $list_term_id = $wpdb->get_results(
        "SELECT term_id FROM " . $table_prefix . "term_taxonomy WHERE taxonomy = 'shop_order_status' "
    );
    $i = 0;
    $list_id = $list_term_id[$i]->term_id;
    ++$i;
    while (isset($list_term_id[$i]) == true) {
        $list_id .= ',' . $list_term_id[$i]->term_id;
        ++$i;
    }
    $list_status = $wpdb->get_results(
        "SELECT slug FROM " . $table_prefix . "terms WHERE term_id IN (" . $list_id . ") ORDER BY term_id"
    );
    return $list_status;
}

function ntav_get_image_url($html)
{
    if (empty($html)) {
        return '';
    }
    $doc = new DOMDocument();
    $doc->loadHTML($html); // loads your html
    $xpath = new DOMXPath($doc);
    $nodelist = $xpath->query("//img"); // find your image
    $node = $nodelist->item(0); // gets the 1st image
    if (
        is_object($node->attributes) && $node->attributes->getNamedItem('src') && $node->attributes->getNamedItem(
            'src'
        )->nodeValue
    ) {
        return $node->attributes->getNamedItem('src')->nodeValue;
    }
    return '';
}

function ntav_get_url_by_slug($title)
{
    $page = get_page_by_title(strtolower($title));
    if ($page != null) {
        $url = get_permalink($page->ID);
    }
    return $url;
}


// Tri de l'affichage des avis du plus récent au plus ancien
function ntav_get_newReviews($id_product, $id_page, $lang = '')
{
    $numberReviews = ntav_getConfig('NUMBER_REVIEWS_DISPLAYED', 'non');
    if (!isset($numberReviews)) {
        $numberReviews = 5;
    }
    $i = 0;
    $reviews = [];
    $min = ($id_page - 1) * $numberReviews;
    $max = $numberReviews;

    $WpmlEnable = ntav_getWpmlEnable();
    global $wpdb;

    if ($WpmlEnable == 'yes') {
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = %d AND lang = %s ORDER BY horodate DESC LIMIT " . $min . "," . $max . "",
                $id_product,
                $lang
            )
        );
    } else {
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = %d ORDER BY horodate DESC LIMIT " . $min . "," . $max . "",
                $id_product
            )
        );
        //$results = $wpdb->get_results($wpdb->prepare("SELECT DATE(order_date) FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = %d ORDER BY horodate DESC LIMIT " . $min . "," . $max . "", $id_product));
    }

    if ($results != null) {
        foreach ($results as $result) {
            $reviews[$i] = $result;
            $i++;
        }
        return $reviews;
    }
    return null;
}


// Tri de l'affichage des avis du plus ancien au plus récent
function ntav_get_oldReviews($id_product, $id_page, $lang = '')
{
    $WpmlEnable = ntav_getWpmlEnable();
    global $wpdb;
    $numberReviews = ntav_getConfig('NUMBER_REVIEWS_DISPLAYED', 'non');
    if (!isset($numberReviews)) {
        $numberReviews = 5;
    }
    $i = 0;
    $min = ($id_page - 1) * $numberReviews;
    $max = $numberReviews;

    if ($WpmlEnable == 'yes') {
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = %d AND lang = %s ORDER BY horodate ASC LIMIT " . $min . "," . $max . "",
                $id_product,
                $lang
            )
        );
    } else {
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = %d ORDER BY horodate ASC LIMIT " . $min . "," . $max . "",
                $id_product
            )
        );
    }

    if ($results != null) {
        foreach ($results as $result) {
            $reviews[$i] = $result;
            $i++;
        }
        return $reviews;
    }
}

// Tri de l'affichage des avis du mieux au moins bien noté
function ntav_get_bestReviews($id_product, $id_page, $lang = '')
{
    $WpmlEnable = ntav_getWpmlEnable();
    global $wpdb;
    $numberReviews = ntav_getConfig('NUMBER_REVIEWS_DISPLAYED', 'non');
    if (!isset($numberReviews)) {
        $numberReviews = 5;
    }
    $i = 0;
    $min = ($id_page - 1) * $numberReviews;
    $max = $numberReviews;

    if ($WpmlEnable == 'yes') {
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = %d AND lang = %s ORDER BY rate DESC, horodate DESC LIMIT " . $min . "," . $max . "",
                $id_product,
                $lang
            )
        );
    } else {
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = %d ORDER BY rate DESC, horodate DESC LIMIT " . $min . "," . $max . "",
                $id_product
            )
        );
    }

    if ($results != null) {
        foreach ($results as $result) {
            $reviews[$i] = $result;
            $i++;
        }
        return $reviews;
    }
    return null;
}

// Tri de l'affichage des avis du moins bien au mieux noté
function ntav_get_worstReviews($id_product, $id_page, $lang = '')
{
    $WpmlEnable = ntav_getWpmlEnable();
    global $wpdb;
    $numberReviews = ntav_getConfig('NUMBER_REVIEWS_DISPLAYED', 'non');
    if (!isset($numberReviews)) {
        $numberReviews = 5;
    }
    $i = 0;
    $min = ($id_page - 1) * $numberReviews;
    $max = $numberReviews;

    if ($WpmlEnable == 'yes') {
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = %d AND lang = %s ORDER BY rate ASC, horodate DESC LIMIT " . $min . "," . $max . "",
                $id_product,
                $lang
            )
        );
    } else {
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = %d ORDER BY rate ASC, horodate DESC LIMIT " . $min . "," . $max . "",
                $id_product
            )
        );
    }


    if ($results != null) {
        foreach ($results as $result) {
            $reviews[$i] = $result;
            $i++;
        }
        return $reviews;
    }
    return null;
}

// Tri de l'affichage des avis du plus utile au moins utile
function ntav_get_helpfulReviews($id_product, $id_page, $lang = '')
{
    $WpmlEnable = ntav_getWpmlEnable();

    global $wpdb;
    $numberReviews = ntav_getConfig('NUMBER_REVIEWS_DISPLAYED', 'non');
    if (!isset($numberReviews)) {
        $numberReviews = 5;
    }
    $i = 0;
    $min = ($id_page - 1) * $numberReviews;
    $max = $numberReviews;

    if ($WpmlEnable == 'yes') {
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT *, helpfulYes - helpfulNo AS diff FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = %d AND lang = %s ORDER BY diff DESC, horodate DESC LIMIT " . $min . "," . $max . "",
                $id_product,
                $lang
            )
        );
    } else {
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT *, helpfulYes - helpfulNo AS diff FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = %d ORDER BY diff DESC, horodate DESC LIMIT " . $min . "," . $max . "",
                $id_product
            )
        );
    }

    if ($results != null) {
        foreach ($results as $result) {
            $reviews[$i] = $result;
            $i++;
        }
        return $reviews;
    }
    return null;
}

function ntav_sortBy_Rate($id_product, $id_page, $rate, $lang = '')
{
    $WpmlEnable = ntav_getWpmlEnable();
    global $wpdb;
    $numberReviews = ntav_getConfig('NUMBER_REVIEWS_DISPLAYED', 'non');
    if (!isset($numberReviews)) {
        $numberReviews = 5;
    }
    $i = 0;
    $min = ($id_page - 1) * $numberReviews;
    $max = $numberReviews;

    if ($WpmlEnable == 'yes') {
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . $wpdb->prefix . "netreviews_products_reviews    WHERE ref_product = %d AND RATE = %d AND lang= %s ORDER BY horodate DESC LIMIT " . $min . "," . $max . "",
                $id_product,
                $rate,
                $lang
            )
        );
    } else {
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . $wpdb->prefix . "netreviews_products_reviews    WHERE ref_product = %d AND RATE = %d ORDER BY horodate DESC LIMIT " . $min . "," . $max . "",
                $id_product,
                $rate
            )
        );
    }

    if ($results != null) {
        foreach ($results as $result) {
            $reviews[$i] = $result;
            $i++;
        }
        return $reviews;
    }
    return null;
}


function ntav_get_netreviews_helpful($id_product_av)
{
    global $wpdb;

    $counthelpful = $wpdb->get_results(
        "SELECT helpfulYes, helpfulNo FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE id_product_av = '$id_product_av'"
    );

    return $counthelpful;
}


function ntav_get_netreviews_count($id_product, $lang = '')
{
    global $wpdb;
    $WpmlEnable = ntav_getWpmlEnable();

    if ($WpmlEnable == 'yes') {
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(nb_reviews)
                FROM " . $wpdb->prefix . "netreviews_products_average WHERE ref_product = %d AND nb_reviews > 0 AND lang = %s",
                $id_product,
                $lang
            )
        );
    } else {
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(nb_reviews)
        FROM " . $wpdb->prefix . "netreviews_products_average WHERE ref_product = %d AND nb_reviews > 0",
                $id_product
            )
        );
    }

    return $count;
}

function ntav_get_netreviews_countByRate($id_product, $lang = '')
{
    global $wpdb;
    $WpmlEnable = ntav_getWpmlEnable();

    if ($WpmlEnable == 'yes') {
        $countByRate = $wpdb->get_results(
            "SELECT COUNT(RATE) as nbrate, RATE FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = '$id_product' AND lang = '$lang' GROUP BY rate"
        );
    } else {
        $countByRate = $wpdb->get_results(
            "SELECT COUNT(RATE) as nbrate, RATE FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = '$id_product' GROUP BY rate"
        );
    }

    return $countByRate;
}

function ntav_get_netreviews_average($id_product, $lang = '')
{
    global $wpdb;
    $WpmlEnable = ntav_getWpmlEnable();

    //$average_rating = '';
    $count = ntav_get_netreviews_count($id_product, $lang);

    if ($count > 0) {
        if ($WpmlEnable == 'yes') {
            $ratings = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT AVG(rate) 
                    FROM " . $wpdb->prefix . "netreviews_products_average
                    WHERE ref_product = %d AND rate > 0 AND lang = %s",
                    $id_product,
                    $lang
                )
            );
        } else {
            $ratings = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT AVG(rate) 
                FROM " . $wpdb->prefix . "netreviews_products_average
                WHERE ref_product = %d AND rate > 0",
                    $id_product
                )
            );
        }

        return $ratings;
    } else {
        return null;
    }
}

function ntav_get_netreviews_average_by_category($product_cat)
{
    global $wpdb;

    if (!empty($product_cat)) {
        $product_cat_formated = implode(',', array_map('intval', $product_cat));
        $avg = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT AVG(rate) FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product IN (%d)",
                $product_cat_formated
            )
        );

        return $avg;
    }
}

function ntav_get_netreviews_count_by_category($product_cat)
{
    global $wpdb;

    if (!empty($product_cat)) {
        $product_cat_formated = implode(',', array_map('intval', $product_cat));
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT count(review) FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product IN (%d)",
                $product_cat_formated
            )
        );

        return $count;
    } else {
        return false;
    }
}

function ntav_get_widget_floating()
{
    global $wpdb;
    $WpmlEnable = ntav_getWpmlEnable();
    $my_current_lang = apply_filters('wpml_current_language', null);


    if ($WpmlEnable == 'yes') {
        $my_current_lang = apply_filters('wpml_current_language', null);
        $widget = $wpdb->get_var(
            "SELECT value FROM " . $wpdb->prefix . "netreviews_configuration AS net_conf
            WHERE net_conf.name='SCRIPTFLOAT' AND net_conf.lang_code ='$my_current_lang'"
        );
    } else {
        $widget = $wpdb->get_var(
            "SELECT value FROM " . $wpdb->prefix . "netreviews_configuration AS net_conf
            WHERE net_conf.name='SCRIPTFLOAT'"
        );
    }

    return $widget;
}

function ntav_updateVersion()
{
    global $wpdb;

    if (is_admin()) {
        $plugin_av = ABSPATH . 'wp-content/plugins/netreviews/netreviews.php';
        $plugin_wc = ABSPATH . 'wp-content/plugins/woocommerce/woocommerce.php';
        $data_av = get_plugin_data($plugin_av);
        $data_wc = get_plugin_data($plugin_wc);

        ntav_updateValue('MODVERSION', $data_av['Version']);
        ntav_updateValue('WCVERSION', $data_wc['Version']);
    }
}

function ntav_updateValue($name, $value, $lang_code = '', $WPML = '')
{
    global $wpdb;

    if ($WPML == 'yes') {
        if ($name == 'WPMLENABLE' || $name == 'IS_ACTIVE') {
            return $wpdb->query(
                "UPDATE " . $wpdb->prefix . "netreviews_configuration SET value = '$value' WHERE name = '$name'"
            );
        }

        $count = $wpdb->get_var(
            "SELECT count(*) as coun FROM " . $wpdb->prefix . "netreviews_configuration WHERE name = '$name' AND lang_code='$lang_code'"
        );
        if ($count > 0) {
            return $wpdb->query(
                "UPDATE " . $wpdb->prefix . "netreviews_configuration SET value = '$value' WHERE name = '$name' AND lang_code = '$lang_code'"
            );
        } else {
            return $wpdb->query(
                "INSERT INTO " . $wpdb->prefix . "netreviews_configuration VALUES(NULL,'" . $name . "','" . $value . "','" . $lang_code . "')"
            );
        }
    } else {
        $count = $wpdb->get_var(
            "SELECT count(*) as coun FROM " . $wpdb->prefix . "netreviews_configuration WHERE name = '$name' AND lang_code IS NULL"
        );
        if ($count > 0) {
            return $wpdb->query(
                "UPDATE " . $wpdb->prefix . "netreviews_configuration SET value = '" . $value . "' WHERE name = '$name' AND lang_code IS NULL"
            );
        } else {
            return $wpdb->query(
                "INSERT INTO " . $wpdb->prefix . "netreviews_configuration VALUES(NULL,'" . $name . "','" . $value . "', NULL)"
            );
        }
    }
}


function ntav_file_get_contents_curl($url)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

function ntav_create_table_conf()
{
    global $wpdb;
    $wpdb->query(
        "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "netreviews_configuration (
        `id` int(10) NOT NULL AUTO_INCREMENT,
        `name` varchar(25) NOT NULL,
        `value` text NOT NULL,
        `lang_code` varchar(25),
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25"
    );

    return true;
}

function ntav_create_table_ave()
{
    global $wpdb;
    $wpdb->query(
        "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "netreviews_products_average (
        `id_product_av` varchar(36) NOT NULL,
        `ref_product` varchar(20) NOT NULL,
        `rate` varchar(5) NOT NULL,
        `nb_reviews` int(10) NOT NULL,
        `horodate_update` varchar(32) NOT NULL,
        `lang` varchar(5) DEFAULT NULL,
        `website_id` varchar(25) NOT NULL DEFAULT '0',
         PRIMARY KEY (`id_product_av`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
    );
}

function ntav_create_table_rev()
{
    global $wpdb;
    $wpdb->query(
        "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "netreviews_products_reviews (
        `id_product_av` varchar(36) NOT NULL,
        `ref_product` varchar(20) NOT NULL,
        `rate` varchar(5) NOT NULL,
        `review` text NOT NULL,
		`media_full` text NULL,
        `helpfulYes` int(5) DEFAULT '0',
        `helpfulNo` int(5) DEFAULT '0',
        `customer_name` varchar(30) NOT NULL,
        `horodate` varchar(32) NOT NULL,
        `order_date` DATE NULL,
        `discussion` text,
        `lang` varchar(5) DEFAULT NULL,
        `website_id` varchar(25) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id_product_av`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
    );
}

function ntav_new_format_date($old_date)
{
    list($date, $time) = explode(' ', $old_date);
    $date_tmp = explode('-', $date);
    $new_date = $date_tmp[2] . '-' . $date_tmp[1] . '-' . $date_tmp[0] . ' ' . $time;

    return $new_date;
}

function ntav_AV_encode_base64($sData)
{
    $sBase64 = base64_encode($sData);
    return strtr($sBase64, '+/', '-_');
}

function ntav_AV_decode_base64($sData)
{
//    echo gettype($sData);
    $sData = urldecode($sData);
    $sBase64 = strtr($sData, '-_', '+/');
    return base64_decode($sBase64);
}


/**
 * Fonction qui recuperer si WPML est activé en B.O
 * @return  contient la config WPMLENABLE de la BDD
 */

function ntav_getWpmlEnable()
{
    $wpmlActive = ntav_getConfig('WPMLENABLE', 'non');
    return $wpmlActive;
}

function ntav_formatDatesDependingOnLocale($date, $locale)
{
    if (
        preg_match('/^fr_/', $locale) || preg_match('/^es_/', $locale) || preg_match('/^it_/', $locale) || preg_match(
            '/^pt_/',
            $locale
        )
    ) {
        $format = explode('/', $date);
        $new_format = array_reverse($format);
        $format_final = implode('-', $new_format);
    } elseif (preg_match('/^de_/', $locale)) {
        $slash = strpos($date, '/');
        $point = strpos($date, '.');
        if ($slash != false) {
            $format = explode('/', $date);
        } elseif ($point != false) {
            $format = explode('.', $date);
        } else {
            return null;
        }
        $new_format = array_reverse($format);
        $format_final = implode('-', $new_format);
    } else {
        $pos = strpos($date, '/');
        if ($pos != false) {
            $format = explode('/', $date);
            $year = array_pop($format);
            $month_year = implode('-', $format);
            $format_final = $year . '-' . $month_year;
        } else {
            return null;
        }
    }
    return $format_final;
}

function ntav_getOrdersCSVByWooCommerceVersion($whereStatusChosen, $start_export_date, $end_export_date, $locale)
{
    global $wpdb;
    global $table_prefix;

    $start_format_final = ntav_formatDatesDependingOnLocale($start_export_date, $locale);
    if ($start_format_final == null) {
        $start_format_final = '1970-01-01';
    }
    $end_format_final = ntav_formatDatesDependingOnLocale($end_export_date, $locale);
    if ($end_format_final == null) {
        $end_format_final = date("Y-m-d");
    }

    $endDate = new DateTime($end_format_final);
    $endDate->add(new DateInterval('P1D'));
    $end_format_final = $endDate->format('Y-m-d');

    $whereDate = (!empty($start_format_final) && !empty($end_format_final)) ? " AND (p.post_date BETWEEN '{$start_format_final}' AND '{$end_format_final}')" : '';

    $wpdb->query('SET SQL_BIG_SELECTS=1');

    $query = str_replace(
        '?wp_?',
        $table_prefix,
        ntav_getOrdersByWooCommerceVersion1($whereStatusChosen, $whereDate, '')
    );

    if (($res = $wpdb->get_results($query)) != null) {
        return $res;
    }

    $query = str_replace(
        '?wp_?',
        $table_prefix,
        ntav_getOrdersByWooCommerceVersion2($whereStatusChosen, $whereDate, '')
    );
    if (($res = $wpdb->get_results($query)) != null) {
        return $res;
    }
}

function ntav_getOrdersAPIByWooCommerceVersion($whereStatusChosen, $from, $to, $flag)
{
    global $wpdb;
    global $table_prefix;

    $whereFlag = ($flag == 0) ? ' AND pm_flg.meta_value = ' . $flag : '';
    $whereDate = (!empty($from) && !empty($to)) ? ' AND (p.post_modified >= ' . $from . ' AND p.post_modified <= ' . $to . ')' : '';

    $wpdb->query('SET SQL_BIG_SELECTS=1');

    $query = str_replace(
        '?wp_?',
        $table_prefix,
        ntav_getOrdersByWooCommerceVersion1($whereStatusChosen, $whereDate, $whereFlag)
    );

    if (($res = $wpdb->get_results($query)) != null) {
        return $res;
    }

    $query = str_replace(
        '?wp_?',
        $table_prefix,
        ntav_getOrdersByWooCommerceVersion2($whereStatusChosen, $whereDate, $whereFlag)
    );
    if (($res = $wpdb->get_results($query)) != null) {
        return $res;
    }
}

function ntav_getOrdersByWooCommerceVersion1($whereStatusChosen, $whereDate, $whereFlag)
{
    $WpmlEnable = ntav_getWpmlEnable();
    $queryWpml = "";
    $whereWpml = '';
    $selectWpml = '';
    $my_current_lang = apply_filters('wpml_current_language', null);
    if ($WpmlEnable == "yes") {
        $queryWpml = " LEFT JOIN (select post_id,meta_key,meta_value from ?wp_?postmeta where meta_key = 'wpml_language') AS pm_wpml ON p.ID = pm_wpml.post_id ";
        $selectWpml = ', pm_wpml.meta_value AS lang';
        $whereWpml = "AND pm_wpml.meta_value = '$my_current_lang'";
    }
    $whereStatusChosen = (!empty($whereStatusChosen)) ? ' AND p.post_status IN (' . $whereStatusChosen . ')' : '';
    return
        "SELECT 
        p.ID AS refcommande,
        DATE_FORMAT(p.post_modified,'%d/%m/%Y %H:%i') AS date_modified,
        pm_ot.meta_value AS amount_order,
        UNIX_TIMESTAMP(p.post_date) AS timestamp,
        p.post_status AS status_order,
        DATE_FORMAT(p.post_date,'%d/%m/%Y %H:%i') AS datecommande,
        oim_pi.meta_value AS product_id,
        pm_cu.meta_value AS id_customer,
        pm_eml.meta_value AS email,
        pm_ln.meta_value AS custom_last_name,
        pm_fn.meta_value AS custom_first_name,
        oi.order_item_name AS product_name,
        'url',
        'url_image',
        'delaiavis'
        $selectWpml
         
         
    FROM ?wp_?posts AS p 
    
    LEFT JOIN ?wp_?woocommerce_order_items AS oi ON p.ID = oi.order_id
    LEFT JOIN (select order_item_id,meta_key,meta_value from ?wp_?woocommerce_order_itemmeta where meta_key = '_product_id') 
        AS oim_pi ON oi.order_item_id = oim_pi.order_item_id
    
    LEFT JOIN (select post_id,meta_key,meta_value from ?wp_?postmeta where meta_key = '_billing_first_name') AS pm_fn ON p.ID = pm_fn.post_id
    LEFT JOIN (select post_id,meta_key,meta_value from ?wp_?postmeta where meta_key = '_billing_last_name') AS pm_ln ON p.ID = pm_ln.post_id
    LEFT JOIN (select post_id,meta_key,meta_value from ?wp_?postmeta where meta_key = '_billing_email') AS pm_eml ON p.ID = pm_eml.post_id
    LEFT JOIN (select post_id,meta_key,meta_value from ?wp_?postmeta where meta_key = '_customer_user') AS pm_cu ON p.ID = pm_cu.post_id
    LEFT JOIN (select post_id,meta_key,meta_value from ?wp_?postmeta where meta_key = '_order_total') AS pm_ot ON p.ID = pm_ot.post_id
    LEFT JOIN (select post_id,meta_key,meta_value from ?wp_?postmeta where meta_key = 'av_flag') AS pm_flg ON p.ID = pm_flg.post_id
    $queryWpml 
    WHERE p.post_type IN ('shop_order') AND oi.order_item_type = 'line_item' $whereStatusChosen $whereDate $whereFlag $whereWpml";
}

function ntav_getOrdersByWooCommerceVersion2($whereStatusChosen, $whereDate, $whereFlag)
{
    $WpmlEnable = ntav_getWpmlEnable();
    $queryWpml = "";
    $whereWpml = '';
    $selectWpml = '';
    $my_current_lang = apply_filters('wpml_current_language', null);
    if ($WpmlEnable == "yes") {
        $queryWpml = " LEFT JOIN (select post_id,meta_key,meta_value from ?wp_?postmeta where meta_key = 'wpml_language') AS pm_wpml ON p.ID = pm_wpml.post_id ";
        $whereWpml = "AND pm_wpml.meta_value = '$my_current_lang'";
        $selectWpml = ', pm_wpml.meta_value AS lang';
    }
    $whereStatusChosen = (!empty($whereStatusChosen)) ? ' AND t.term_id IN (' . $whereStatusChosen . ')' : '';
    return
        "SELECT 
        p.ID AS refcommande,
        DATE_FORMAT(p.post_modified,'%d/%m/%Y %H:%i') AS date_modified,
        pm_ot.meta_value AS amount_order,
        UNIX_TIMESTAMP(p.post_date) AS timestamp,
        t.slug AS status_order,
        DATE_FORMAT(p.post_date,'%d/%m/%Y %H:%i') AS datecommande,
        oim_pi.meta_value AS product_id,
        pm_cu.meta_value AS id_customer,
        pm_eml.meta_value AS email,
        pm_ln.meta_value AS custom_last_name,
        pm_fn.meta_value AS custom_first_name,
        oi.order_item_name AS product_name,
        'url',
        'url_image',
        'delaiavis' 
     $selectWpml

     FROM ?wp_?posts AS p 
     
     LEFT JOIN ?wp_?term_relationships AS tr ON p.ID = tr.object_id
     LEFT JOIN ?wp_?terms AS t ON tr.term_taxonomy_id = t.term_id
     LEFT JOIN ?wp_?term_taxonomy AS tt ON tt.term_id = t.term_id
     LEFT JOIN ?wp_?woocommerce_order_items AS oi ON p.ID = oi.order_id
     LEFT JOIN (select order_item_id,meta_key,meta_value from ?wp_?woocommerce_order_itemmeta where meta_key = '_product_id') 
        AS oim_pi ON oi.order_item_id = oim_pi.order_item_id
     
     LEFT JOIN (select post_id,meta_key,meta_value from ?wp_?postmeta where meta_key = '_billing_first_name') AS pm_fn ON p.ID = pm_fn.post_id
     LEFT JOIN (select post_id,meta_key,meta_value from ?wp_?postmeta where meta_key = '_billing_last_name') AS pm_ln ON p.ID = pm_ln.post_id
     LEFT JOIN (select post_id,meta_key,meta_value from ?wp_?postmeta where meta_key = '_billing_email') AS pm_eml ON p.ID = pm_eml.post_id
     LEFT JOIN (select post_id,meta_key,meta_value from ?wp_?postmeta where meta_key = '_customer_user') AS pm_cu ON p.ID = pm_cu.post_id
     LEFT JOIN (select post_id,meta_key,meta_value from ?wp_?postmeta where meta_key = '_order_total') AS pm_ot ON p.ID = pm_ot.post_id
     LEFT JOIN (select post_id,meta_key,meta_value from ?wp_?postmeta where meta_key = 'av_flag') AS pm_flg ON p.ID = pm_flg.post_id
    $queryWpml 
     
    WHERE p.post_type IN ('shop_order') AND tt.taxonomy IN ('shop_order_status') AND oi.order_item_type = 'line_item' $whereStatusChosen $whereDate $whereFlag $whereWpml";
}

/**
 * Display product stars based on users rating
 * @param $note
 * @return string
 */
function ntav_addStars($note)
{
    $starsColour = ntav_getConfig('COLOUR_STARS_AV', 'non');
    if (!isset($starsColour)) {
        $starsColour = 'FFCD00';
    }
    $text = '<div>';

    for ($i = 1; $i <= 5; $i++) {
        $text .= '<span class="nr-icon nr-star grey"></span>';
    }
    $text .= '</div>';
    $text .= '<div style="color: #' . $starsColour . ' !important">';
    for ($i = 1; $i <= 5; $i++) {
        if (round($note, 1) > $i) {
            $starWidth = 'width:20%;';
        } else {
            $tempWidth = ((round($note, 1) - ($i - 1)) * 20 < 0 ? "0" : (round($note, 1) - ($i - 1)) * 20);
            $starWidth = 'width:' . $tempWidth . '%;';
        }
        $text .= '<span class="nr-icon nr-star" style="' . $starWidth . '"></span>';
    }
    $text .= '</div>';
    return $text;
}

/**
 * Display user rating in reviews
 * @param $note
 * @return string
 */
function ntav_displayStars($note)
{
    $starsColour = ntav_getConfig('COLOUR_STARS_AV', 'non');
    if (!isset($starsColour)) {
        $starsColour = 'FFCD00';
    }
    $text = '<div >';

    for ($i = 1; $i <= 5; $i++) {
        $text .= '<span class="nr-icon nr-star grey"></span>';
    }
    $text .= '</div>';
    $text .= '<div style="color: #' . $starsColour . ' !important">';

    for ($i = 1; $i <= $note; $i++) {
        $text .= '<span class="nr-icon nr-star gold" style="color: #' . $starsColour . ' !important"></span>';
    }
    $text .= '</div>';
    return $text;
}

/**
 * Decodes a string and returns one (or more) media
 * @param {string} $medias An encoded string containing Json media
 * @return mixed|null
 */
function ntav_medias($medias)
{
    if (empty($medias)) {
        return null;
    } else {
        $json = base64_decode($medias);
        $media = urldecode($json);
        $data = json_decode($media, true);
        return $data;
    }
}

/**
 * Fonction qui map les messages de moderation en fonction des données passées en parametre
 * @param array $column tableau contenant les donnees liées à un avis
 * @param array $checksum contient le nombres de lignes ajoute/supprimer/modifier
 * @param array $debug contient un message d'erreur si un problème survient
 * @return array qui contient soit un tableau avec les messages de moderation formate soit un message d'erreur
 */
function ntav_discussion($discussion, $name)
{
    $my_review['discussion'] = array();
    $unserialized_discussion = array();
    try {
        $unserialized_discussion = unserialize(ntav_AV_decode_base64($discussion));
    } catch (Exception $exc) {
    }
    if ($unserialized_discussion === false) {
        return array();
    }
    $my_current_lang = apply_filters('wpml_current_language', null);

    $hrefcertificat = ntav_getConfig('URLCERTIFICAT', $my_current_lang);

    $urlSite = explode("/", $hrefcertificat);
    foreach ($unserialized_discussion as $k_discussion => $each_discussion) {
        $my_review['discussion'][$k_discussion]['commentaire'] = $each_discussion['commentaire'];
        $my_review['discussion'][$k_discussion]['horodate'] = $each_discussion['horodate'];
        $my_review['discussion'][$k_discussion]['origine'] = $each_discussion['origine'];
        if ($each_discussion['origine'] == 'ecommercant') {
            $my_review['discussion'][$k_discussion]['origine'] = $urlSite[4];
        } elseif ($each_discussion['origine'] == 'internaute') {
            $my_review['discussion'][$k_discussion]['origine'] = 'Client';
        } else {
            $my_review['discussion'][$k_discussion]['origine'] = 'Modérateur';
        }
    }
    return $my_review['discussion'];
}

/**
 * récupère la langue du BO
 * @return string
 */
function ntav_get_user_locale()
{
    global $wp_version;
    if ($wp_version > '4.7') {
        $currentLang = get_user_locale();
    } else {
        $currentLang = get_locale();
    }
    $currentLang = explode('_', $currentLang);
    if (is_array($currentLang) && count($currentLang) > 0) {
        $currentLang = $currentLang[0];
    } else {
        $currentLang = 'en';
    }

    return $currentLang;
}


function ntav_get_img_by_lang()
{
    $lang = ntav_get_user_locale();
    $listImgBo = [];
    $listImgBo['logo'] = 'logo_full_' . $lang . '.png';
    $listImgBo['start'] = 'start_' . $lang . '.png';
    $listImgBo['collecte_multicanal'] = 'collecte_multicanal_' . $lang . '.png';
    $listImgBo['diffusion'] = 'diffusion_' . $lang . '.png';
    $listImgBo['engagement_equipes'] = 'engagement_equipes_' . $lang . '.png';
    $listImgBo['mesure_analyse'] = 'mesure_analyse_' . $lang . '.png';
    $listImgBo['valorisation_client'] = 'valorisation_client_' . $lang . '.png';
    $listImgBo['sceau_lang'] = 'Sceau_100_' . $lang . '.png';
    return $listImgBo;
}


/**
 * We retrieve the info for the "Review" Property (Rich Snippets)
 * @return array (Reviews)
 */
function ntav_getReviewsRS($idproduct)
{
    global $wpdb;
    $WpmlEnable = ntav_getWpmlEnable();

    if ($WpmlEnable == 'yes') {
        $my_current_lang = apply_filters('wpml_current_language', null);

        /* if(is_array($idproduct)){
            foreach($idproduct as $key => $id) {

                $queryWpml = $wpdb->get_results("SELECT customer_name,DATE_FORMAT(FROM_UNIXTIME(horodate), '%d-%m-%Y') as Newhorodate,review,rate FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = $id and lang = '$my_current_lang' ORDER BY horodate DESC LIMIT 0,5");

                if($queryWpml !== null){
                    return $queryWpml;
                }
            }

        } else { */
        $query = $wpdb->get_results(
            "SELECT customer_name,DATE_FORMAT(FROM_UNIXTIME(horodate), '%d-%m-%Y') as Newhorodate,review,rate FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = $idproduct and lang = '$my_current_lang' ORDER BY horodate DESC LIMIT 0,5"
        );
    //}
    } else {
        $my_current_lang = '';
        $query = $wpdb->get_results(
            "SELECT customer_name,DATE_FORMAT(FROM_UNIXTIME(horodate), '%d-%m-%Y') as Newhorodate,review,rate FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = $idproduct and lang = '$my_current_lang' ORDER BY horodate DESC LIMIT 0,5"
        );
    }

    return $query;
}

/**
 * We retrieve number of reviews with rate equal or higher than 3
 * @param $id_product
 * @param string $lang
 * @return array|object|null
 */
function ntav_get_netreviews_countRecommended($id_product, $lang = '')
{
    global $wpdb;
    $WpmlEnable = ntav_getWpmlEnable();

    if ($WpmlEnable == 'yes') {
        /* if(is_array($id_product)){
            foreach($id_product as $key => $id) {
                $countRecommended = $wpdb->get_var($wpdb->prepare("SELECT COUNT(RATE) as nbrate FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = %d AND rate >= 3 AND lang = %s", array($id, $lang)));

                if($countRecommended !== null){
                    return $countRecommended;
                } else {
                    return null;
                }
            }

        } else { */
        $countRecommended = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(RATE) as nbrate FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = %d AND rate >= 3 AND lang = %s",
                array($id_product, $lang)
            )
        );
    //}
    } else {
        $countRecommended = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(RATE) as nbrate FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = %d AND rate >= 3",
                array($id_product)
            )
        );
    }

    return $countRecommended;
}

/**
 * We retrieve % of reviews with rate equal or higher than 3
 * @param $id_product
 * @param string $lang
 * @return array|object|null
 */
function ntav_get_netreviews_percentRecommended($id_product, $lang = '')
{
    return round(
        (ntav_get_netreviews_countRecommended($id_product, $lang) / ntav_get_netreviews_count(
            $id_product,
            $lang
        )) * 100,
        0
    );
}

/**
 * We retrieve % of reviews with rate equal or higher than 3
 * @param $id_product
 * @param string $lang
 * @return array|object|null
 */
function ntav_get_netreviews_idProductWpml($id_product, $lang = '')
{
    global $wpdb;

    foreach ($id_product as $key => $id) {
        $countRecommended = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(RATE) as nbrate FROM " . $wpdb->prefix . "netreviews_products_reviews WHERE ref_product = %d AND rate >= 3 AND lang = %s",
                array($id, $lang)
            )
        );

        if ($countRecommended !== null) {
            return $id;
        } else {
            return null;
        }
    }
}


/**
 */
function ntav_get_product_attrlabels($product_id, $label)
{
    global $wpdb;
    $prefix_attr = 'pa_';
    $label_index = ntav_getConfig('ATTRIBUTE_' . $label);
    if (!empty($label_index)) {
        $lable_var = wc_get_product_terms($product_id, $prefix_attr . $label_index, array('fields' => 'names'));
        $lable_var = array_shift($lable_var);
    } else {
        $lable_var = null;
    }
    return $lable_var;
}

function ntav_getOrderStatus_origin($list_status)
{
    $statuslist = array();
    if (function_exists('wc_get_order_statuses')) {
        $results = wc_get_order_statuses();
        $tab_id_status = explode(",", $list_status);
        $i = 0;
        foreach ($results as $key => $status) {
            if (in_array($status, $tab_id_status)) {
                $statuslist[] = $key;
            }
            $i++;
        }
        return $statuslist;
    }
}
