<?php 

/**
 * @package probance-optin
 */

namespace Inc\Data;

use Inc\Data\Data;
use Inc\Data\NewsletterFields;

use Inc\Common\Utils;

class Translations
{

    /**
     * Funtion to return the translation of a given option id.
     * @param string $option_id : option id
     * @param string $lang : lang of the translation. Default 'default'
     * @return string : If the translation is find return the translation elseif return default value else return empty string.
     */
    public static function getOptionTranslation($option_id, $lang='default')
    {   
        // Conversion to lowercase
        $lang=strtolower($lang);
        $url = PLUGIN_PATH . "translations";

        if( PROB_DEBUG == 1 ) Utils::write_log("Looking for translation in file $url/_$lang.json");
        // Check if the file exists
        if(file_exists( "$url/_$lang.json" ))
        {   
            // Get content file
            $content=json_decode(file_get_contents("$url/_$lang.json", true), true);

            // Find the translation by id
            if(isset($content[$option_id]))
            {
                if( PROB_DEBUG == 1 ) Utils::write_log("Find " . $content[$option_id] . " for option : $option_id and lang : $lang");

                return $content[$option_id];
            }          

        } elseif ( $lang!='default' && file_exists( "$url/_default.json" ))
        {
            // Get content file
            $content=json_decode(file_get_contents("$url/_default.json", true), true);
            
            // Find the translation by id
            if(isset($content[$option_id]))
            {
                if( PROB_DEBUG == 1 ) Utils::write_log("Find " . $content[$option_id] . " for option : $option_id and lang : $lang");

                return $content[$option_id];
            }       
        }

        return false;
    }

    public static function setDefaultValues($arr)
    {
        foreach ( $arr as $key=>$value)
        {
            if(self::getOptionTranslation($arr[$key]['id'])!=false)$arr[$key]['default_value']=self::getOptionTranslation($arr[$key]['id']);
        }

        return $arr;
    }

    public static function getTranslatedFields($module, $lang)
    {   
        switch($module)
        {
            case 'optin':
                $data = (new WebelementFields)->getData();
                break;
            case 'newsletter':
                $data = (new NewsletterFields)->getData();
                break;
            default:
                if( PROB_DEBUG == 1 ) Utils::write_log("Module $module unknown.");
                break;
        }

        $fields=[];

        if(! Utils::translationExists(strtoupper($lang)))
        {
            if( PROB_DEBUG == 1 ) Utils::write_log("No translation set for this locale.");
            $lang="default";
        }

        switch($lang)
        {
            case "default":
                foreach($data as $d)
                {
                    $current_opt=wp_unslash(get_option( $d['id']));
                    $fields[$d["id"]]=($current_opt!="" ? $current_opt : '');
                }
                break;
            default:
                foreach($data as $d)
                {
                    if ( isset($d['to_translate']) && $d['to_translate'])
                    {   
                        $id=$d["id"]."-".$lang;
                        $translated_opt=wp_unslash(get_option($id));
                        if( PROB_DEBUG == 1 ) Utils::write_log("option  $id : $translated_opt");
                        $fields[$d["id"]]=($translated_opt!="" ? $translated_opt : get_option($d["id"]));
                    }
                    else
                    {   
                        $id=$d['id'];
                        $current_opt=wp_unslash(get_option($id));
                        if( PROB_DEBUG == 1 ) Utils::write_log("option  $id : $current_opt");
                        $fields[$d["id"]]=($current_opt!="" ? $current_opt : '');
                    }
                }
                break;
        }
        
        return $fields;
    } 
}

?>