<?php

/**
 * @package probance-optin
 */
namespace Inc\Common;

final class Utils {

    /**
     * @return boolean : true if a translation exists for the current locale user  
     */
    public static function translationExists($lang)
    {
        return in_array($lang, self::getLanguages());
    }

    /**
     * Return the current user locale
     * @return string $user_locale : string format 2-char lowercase. Empty if no language is detected.
     */
    public static function getUserLanguage()
    {
        $user_locale="";

        $user = wp_get_current_user();

        $user_locale_unformated = get_user_meta($user->ID, 'locale', true);

        if( PROB_DEBUG == 1 ) self::write_log("Language unformated for user ($user->ID) : $user_locale_unformated.");

        if(preg_match('/^[a-z]{2}(?:_[A-Z]{2})?$/', $user_locale_unformated, $matches)===false)
        {
            if( PROB_DEBUG == 1 ) self::write_log("No language detected for the current user ($user->ID).");
        } 
        else
        {   
            switch(true)
            {
                case (preg_match('/^[a-z]{2}_[A-Z]{2}$/', $user_locale_unformated, $matches)==1):
                    $user_locale=explode('_', $user_locale_unformated)[0];
                    break;
 
                case (preg_match('/^[a-z]{2}$/', $user_locale_unformated, $matches)==1):
                    $user_locale=$user_locale_unformated;
                    break;
            }
        } 

        if( PROB_DEBUG == 1 ) self::write_log("Language for user ($user->ID) : $user_locale.");

        return $user_locale;
    }

    /**
     * Return languages available for current website 
     * @return array $langs : array of string format 2-char to uppercase. Empty array if no language detected.
     */
    public static function getLanguages()
    {

        $langs = [];

        // Admin Language
        $admin_lang = get_bloginfo('language');

        // WPML languages available for this site

        // $wpml_langs = array( "es" => array(
        //         "id" => 25,
        //         "active" => 1,
        //         "default_locale" => "es_ES",
        //         "native_name" => "Español",
        //         "missing" => 0,
        //         "translated_name" => "Espagnol",
        //         "language_code" => "es",
        //         "country_flag_url" => "http://yourdomain/wpmlpath/res/flags/it.png",
        //         "url" => "http://yourdomain/it/circa"
        //         ),
        //         "en" => array(
        //             "id" => 26,
        //             "active" => 0,
        //             "default_locale" => "en_EN",
        //             "native_name" => "English",
        //             "missing" => 0,
        //             "translated_name" => "Anglais",
        //             "language_code" => "en",
        //             "country_flag_url" => "http://yourdomain/wpmlpath/res/flags/it.png",
        //             "url" => "http://yourdomain/it/circa"
        //         ),
        //         "it" => array(
        //             "id" => 27,
        //             "active" => 0,
        //             "default_locale" => "it_IT",
        //             "native_name" => "Italiano",
        //             "missing" => 0,
        //             "translated_name" => "Italian",
        //             "language_code" => "it",
        //             "country_flag_url" => "http://yourdomain/wpmlpath/res/flags/it.png",
        //             "url" => "http://yourdomain/it/circa"
        //         )
        //         );

        $wpml_langs = apply_filters( 'wpml_active_languages', NULL);

        if($admin_lang)
        {
            $admin_lang = strtoupper(explode('-', $admin_lang)[0]);        

            array_push($langs, $admin_lang);
        }

        if ($wpml_langs)
        {
            $wpml_langs_keys = [];

            foreach(array_keys($wpml_langs) as $l)
            {
                array_push($wpml_langs_keys, strtoupper($l));
            }

            $langs = array_merge($langs, $wpml_langs_keys);
        }

        if(self::option_exists('probance-optin-languages'))
        {
            $opt_langs_str = get_option('probance-optin-languages');

            $opt_langs_arr = explode(';', $opt_langs_str);

            $langs = array_merge($langs, $opt_langs_arr);
        }

        return array_unique($langs);
    }

    public static function write_log($log) {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }

    /**
     * Check if an option exists 
     * @param $arg string id of the option
     * @return true|false boolean 
     */
    public static function option_exists( $arg )
    {

        global $wpdb;
        $prefix = $wpdb->prefix;
        $db_options = $prefix.'options';
        $sql_query = 'SELECT * FROM ' . $db_options . ' WHERE option_name LIKE "' . $arg . '"';

        $results = $wpdb->get_results( $sql_query, OBJECT );

        if ( count( $results ) === 0 ) {
            return false;
        } else {
            return true;
        }
    }

}

?>