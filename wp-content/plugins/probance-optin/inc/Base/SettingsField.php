<?php

/**
 * @package probance-optin
 */

namespace Inc\Base;

use Inc\Data\ApiFields;
use Inc\Data\WebelementFields;
use Inc\Data\NewsletterFields;
use Inc\Data\Translations;
use Inc\Common\Utils;

final class SettingsField 
{
    public static $page='probance';
    public static $settings_id='probance-optin_optin-settings';

    /**
     * Store all classes inside an array
     * @return array
     */
    public static function get_all_settings()
    {
        $api_fields=new ApiFields();
        $webelement_fields=new WebelementFields();
        $newsletter_fields=new NewsletterFields();

        return array(
                array(
                    "fields" => $api_fields->getData(), 
                    "section" => $api_fields->getSectionInfo()
                ),
                array(
                    "fields" => $webelement_fields->getData(), 
                    "section" => $webelement_fields->getSectionInfo()
                ),
                array(
                    "fields" => $newsletter_fields->getData(), 
                    "section" => $newsletter_fields->getSectionInfo()
                )
        );
    }

    /**
     * Create section and settings fields
     * @return
     */
    public function add_settings_fields()
    {
        $data=self::get_all_settings();

        foreach($data as $d)
        {
            // add section
            add_settings_section($d['section']['id'],$d['section']['title'], array($this, 'probance_display_section'), self::$page);
            
            // add settings field
            foreach($d['fields'] as $setting)
            {
                add_settings_field($setting['id'],$setting['properties-label'], array($this, 'probance_display_field'),self::$page,$d['section']['id'],$setting);
            }

            // register settings
            foreach($d['fields'] as $setting)
            {
                register_setting(self::$settings_id,$setting['id']);
            }

            // Create options if not exists
            foreach($d['fields'] as $setting)
            {
                if (!Utils::option_exists($setting['id']))
                {
                    add_option( $setting['id'], $setting['default_value']);
                }
                
            }

        }   

    }

    /**
     * Display admin section (empty for this moment)
     */
    public function probance_display_section()
    {
        echo '';
    }

    /**
     * Display admin settings fields
     * @param $args object with multiples keys as the example bellow
     * @return
     */
    public function probance_display_field($args) {

        /* EXAMPLE INPUT
                'type'      => 'input',
                'subtype'   => '',
                'id'    => $this->plugin_name.'_example_setting',
                'name'      => $this->plugin_name.'_example_setting',
                'required' => 'required="required"',
                'get_option_list' => "",
                    'value_type' = serialized OR normal,
        'wp_data'=>(option or post_meta),
        'post_id' =>
        */     

        if($args['wp_data'] == 'option'){
            $wp_data_value = get_option($args['name'],$args['default_value']);
        } elseif($args['wp_data'] == 'post_meta'){
            $wp_data_value = get_post_meta($args['post_id'], $args['name'], true );
        }

        $languages=Utils::getLanguages();

        $value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;

        if( PROB_DEBUG == 1 ) Utils::write_log("value ".gettype($value)." : $value.");

        switch ($args['type']) {

            case 'input':            
                if($args['subtype'] != 'checkbox'){
                    $step = (isset($args['step'])) ? 'step="'.$args['step'].'"' : '';
                    $min = (isset($args['min'])) ? 'min="'.$args['min'].'"' : '';
                    $max = (isset($args['max'])) ? 'max="'.$args['max'].'"' : '';
                    $size = (isset($args['size'])) ? 'size="'.$args['size'].'"' : 'size="40"';
                    if (isset($args['properties']) && isset($args['to_translate']) && $args['to_translate'] && $languages!=[]){
                        $checked = ($value) ? 'checked' : '';
                        if( PROB_DEBUG == 1 )  Utils::write_log("On affiche le champ avec bouton properties & translations.");
                        echo '<div style="display: flex; justify-content: flex-start;">
                                <input type="text" id="'.$args['id'].'" '.$args['required'].' '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" '.$size.' value="' . esc_attr($value) . '" />
                                <input style="margin-left: 10px;" type="submit" id="'.$args['id'].'-btn-open-properties" name="btn-open-properties" class="button button-small button-primary" value="Properties" data-type="properties"/>
                                <input style="margin-left: 10px;background: #22b138; border-color: #22b138;" type="submit" id="'.$args['id'].'-btn-open-translations" name="btn-open-translations" class="button button-small button-primary" value="Translations" data-type="translations"/>
                            </div>';
                    }elseif (isset($args['properties'])) {
                        $checked = ($value) ? 'checked' : '';
                        if( PROB_DEBUG == 1 ) Utils::write_log("On affiche le champ avec bouton properties");
                        echo '<div style="display: flex; justify-content: flex-start;">
                                <input type="text" id="'.$args['id'].'" '.$args['required'].' '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" '.$size.' value="' . esc_attr($value) . '" />
                                <input style="margin-left: 10px;" type="submit" id="'.$args['id'].'-btn-open-properties" name="btn-open-properties" class="button button-small button-primary" value="Properties" data-type="properties"/>
                            </div>';
                    } elseif (isset($args['disabled'])){
                        // hide the actual input bc if it was just a disabled input the informaiton saved in the database would be wrong - bc it would pass empty values and wipe the actual information
                        echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'_disabled" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'_disabled" '.$size.' disabled value="' . esc_attr($value) . '" /><input type="hidden" id="'.$args['id'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />';
                    } else {
                        echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'" '.$args['required'].' '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" '.$size.' value="' . esc_attr($value) . '" />';
                    }
                } else {
                    $checked = ($value == "1") ? 'checked' : '';
                    echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" name="'.$args['name'].'" size="40" value="1" '.$checked.' />';
                }
            break;
            case 'select':
                echo '<select id="'.$args['id'].'" name="'.$args['name'].'">';
                echo '<option value="'.$value.'">'.$args['select_options'][$value].'</option>';
                foreach($args['select_options'] as $optionVal => $optionTitle)
                {
                    if($optionVal!=$value)
                        echo '<option value="'.$optionVal.'">'.$optionTitle.'</option>';
                }
                echo '</select>';
            break;
            default:
            # code...
            break;
        }

        

        /*
        * Properties
        */
        if(isset($args['properties'])) {

            $first=true;
            /*
            * POP UP COMMON PART 
            */
            echo '  <div id="'.$args['id'].'-block-properties" name="block-properties" data-type="block-popup" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 99; background-color: rgba(0,0,0,0.5);">
                        <div style="display: grid; margin: auto; padding: 15px; background-color: #ebe9eb; border-radius: 4px;cursor: default;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; align-items: end;">
                                <label style="display: block; cursor: default;" class="properties-panel">'. ($args['properties-label'] ? $args['properties-label'] . ' > Properties' : 'Properties') . '</label>
                                <div class="button-group">
                                    <input  style="width: 50px; text-align: center" id="'.$args['id'].'-save-block-properties" name="save-block-properties" class="button button-small button-primary" type="submit" value="Save" data-type="properties"/>
                                    <input  style="width: 50px; text-align: center" id="close-block-properties" name="close-block-properties" class="button button-small button-secondary" type="submit" value="Close" />
                                </div>
                            </div>';
            /*
            * Buttons Group
            */
            echo '<div class="button-group">';
            foreach ($args['properties'] as $key=>$value) {


                if($first) {
                    $class="button-primary";
                    $first=false;
                } else {
                    $class="";
                }

                // echo 'key : ' . $key;
                // echo 'value : ' . $value['default_value'];

                if( PROB_DEBUG == 1 ) Utils::write_log('Functionnalité ! '.$key);
                
                $option=$args['id'].'-'.$key;

                if(!self::option_exists($option)){
                    if( PROB_DEBUG == 1 ) Utils::write_log("Création de l'option : ".$option);
                    add_option($option,(isset($value['default_value']) ? $value['default_value'] : ''));
                    
                }
                
                $f_value=get_option($option);
                if( PROB_DEBUG == 1 ) Utils::write_log("Fonctionnalité [$key] - option : $option - value : ".$f_value);
                if( PROB_DEBUG == 1 ) Utils::write_log("On passe au switch case");
                
                echo '<a id="'.$args['id'].'-btn-toggle-properties-'.$key.'" class="button '.$class.' toggle-textearea-button" data-type="properties">'.$value['label'].'</a>';
            }

            echo    '</div>';

            $first=true;

            /*
            * FOR EACH FUNCTIONALITY 
            */
            foreach ($args['properties'] as $key=>$value) {
                $option=$args['id'].'-'.$key;
                $f_value=get_option($option);

                if($first) {
                    $class="visible";
                    $first=false;
                } else {
                    $class="hidden";
                }

                echo '<textarea id="'.$args['id'].'-'.$key.'" name="'.$args['name'].'-'.$key.'" class="'.$class.'" rows="8" cols="50" >'.(esc_attr($f_value) != '' ? esc_attr($f_value) : $value['default_value']).'</textarea>' ;
                
            }

            /*
            * POP UP END COMMON PART 
            */
            echo '      </div>
                    </div>';
        }

        /*
        * Translations
        */
        if(isset($args['to_translate']) && $args['to_translate'] && $languages!=[]) {

            $first=true;
            /*
            * POP UP COMMON PART 
            */
            echo '  <div id="'.$args['id'].'-block-translations" name="block-translations" data-type="block-popup" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 99; background-color: rgba(0,0,0,0.5);">
                        <div style="display: grid; margin: auto; padding: 15px; background-color: #ebe9eb; border-radius: 4px;cursor: default;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; align-items: end;">
                                <label style="display: block; cursor: default;" class="translations-panel">'. ($args['properties-label'] ? $args['properties-label'] . ' > Translations' : 'Translations') . '</label>
                                <div class="button-group">
                                    <input  style="width: 50px; text-align: center" id="'.$args['id'].'-save-block-translations" name="save-block-translations" class="button button-small button-primary" type="submit" value="Save" data-type="translations"/>
                                    <input  style="width: 50px; text-align: center" id="close-block-translations" name="close-block-translations" class="button button-small button-secondary" type="submit" value="Close" />
                                </div>
                            </div>';
            /*
            * Buttons Group
            */
            echo '<div class="button-group">';
            foreach ($languages as $l) {


                if($first) {
                    $class="button-primary";
                    $first=false;
                } else {
                    $class="";
                }

                if( PROB_DEBUG == 1 ) Utils::write_log('Language : '.$l);
                
                $option=$args['id'] . '-' . strtolower($l);

                if(!self::option_exists($option)){
                    if( PROB_DEBUG == 1 ) Utils::write_log("Création de l'option : ".$option);
                    if(Translations::getOptionTranslation($args['id'], $l)!=false)
                    {
                        add_option($option, Translations::getOptionTranslation($args['id'], $l));
                    }else
                    {
                        add_option($option, get_option($args['id']));
                    }
                }
                
                $f_value=wp_unslash(get_option($option));

                if( PROB_DEBUG == 1 ) Utils::write_log("Language [$l] - option : $option - value : ".$f_value);
                if( PROB_DEBUG == 1 ) Utils::write_log("On passe au switch case");
                
                echo '<a id="'.$args['id'].'-btn-toggle-translations-'.strtolower($l).'" class="button '.$class.' toggle-textearea-button" data-type="translations">'.$l.'</a>';
            }

            echo '<a id="'.$args['id'].'-btn-toggle-translations-plus" class="button toggle-textearea-button" data-type="translations">+</a>';

            echo    '</div>';

            $first=true;

            /*
            * FOR EACH FUNCTIONALITY 
            */
            foreach ($languages as $l) {
                $option=$args['id'] . '-' . strtolower($l);
                $f_value=wp_unslash(get_option($option));

                if($first) {
                    $class="visible";
                    $first=false;
                } else {
                    $class="hidden";
                }

                echo '<textarea id="' . $args['id'] . '-' . strtolower($l) .'" name="' . $args['id'] . '-' . strtolower($l) .'" class="'.$class.'" rows="8" cols="50" >'.(esc_attr($f_value) ? esc_attr($f_value) : '').'</textarea>' ;
                
            }
            
            echo '<div id="'.$args['id'].'-plus" class="hidden add_lang_block">
                    <select class="select_lang" name="' . $args['id'] . '-plus" >
                        <option value="0">Select Language</option>
                        <option value="af">Afrikaans</option>
                        <option value="sq">Albanian - shqip</option>
                        <option value="am">Amharic - አማርኛ</option>
                        <option value="ar">Arabic - العربية</option>
                        <option value="an">Aragonese - aragonés</option>
                        <option value="hy">Armenian - հայերեն</option>
                        <option value="ast">Asturian - asturianu</option>
                        <option value="az">Azerbaijani - azərbaycan dili</option>
                        <option value="eu">Basque - euskara</option>
                        <option value="be">Belarusian - беларуская</option>
                        <option value="bn">Bengali - বাংলা</option>
                        <option value="bs">Bosnian - bosanski</option>
                        <option value="br">Breton - brezhoneg</option>
                        <option value="bg">Bulgarian - български</option>
                        <option value="ca">Catalan - català</option>
                        <option value="ckb">Central Kurdish - کوردی (دەستنوسی عەرەبی)</option>
                        <option value="zh">Chinese - 中文</option>
                        <option value="zh-HK">Chinese (Hong Kong) - 中文（香港）</option>
                        <option value="zh-CN">Chinese (Simplified) - 中文（简体）</option>
                        <option value="zh-TW">Chinese (Traditional) - 中文（繁體）</option>
                        <option value="co">Corsican</option>
                        <option value="hr">Croatian - hrvatski</option>
                        <option value="cs">Czech - čeština</option>
                        <option value="da">Danish - dansk</option>
                        <option value="nl">Dutch - Nederlands</option>
                        <option value="en">English</option>
                        <option value="eo">Esperanto - esperanto</option>
                        <option value="et">Estonian - eesti</option>
                        <option value="fo">Faroese - føroyskt</option>
                        <option value="fil">Filipino</option>
                        <option value="fi">Finnish - suomi</option>
                        <option value="fr">French - français</option>
                        <option value="gl">Galician - galego</option>
                        <option value="ka">Georgian - ქართული</option>
                        <option value="de">German - Deutsch</option>
                        <option value="el">Greek - Ελληνικά</option>
                        <option value="gn">Guarani</option>
                        <option value="gu">Gujarati - ગુજરાતી</option>
                        <option value="ha">Hausa</option>
                        <option value="haw">Hawaiian - ʻŌlelo Hawaiʻi</option>
                        <option value="he">Hebrew - עברית</option>
                        <option value="hi">Hindi - हिन्दी</option>
                        <option value="hu">Hungarian - magyar</option>
                        <option value="is">Icelandic - íslenska</option>
                        <option value="id">Indonesian - Indonesia</option>
                        <option value="ia">Interlingua</option>
                        <option value="ga">Irish - Gaeilge</option>
                        <option value="it">Italian - italiano</option>
                        <option value="it-IT">Italian (Italy) - italiano (Italia)</option>
                        <option value="it-CH">Italian (Switzerland) - italiano (Svizzera)</option>
                        <option value="ja">Japanese - 日本語</option>
                        <option value="kn">Kannada - ಕನ್ನಡ</option>
                        <option value="kk">Kazakh - қазақ тілі</option>
                        <option value="km">Khmer - ខ្មែរ</option>
                        <option value="ko">Korean - 한국어</option>
                        <option value="ku">Kurdish - Kurdî</option>
                        <option value="ky">Kyrgyz - кыргызча</option>
                        <option value="lo">Lao - ລາວ</option>
                        <option value="la">Latin</option>
                        <option value="lv">Latvian - latviešu</option>
                        <option value="ln">Lingala - lingála</option>
                        <option value="lt">Lithuanian - lietuvių</option>
                        <option value="mk">Macedonian - македонски</option>
                        <option value="ms">Malay - Bahasa Melayu</option>
                        <option value="ml">Malayalam - മലയാളം</option>
                        <option value="mt">Maltese - Malti</option>
                        <option value="mr">Marathi - मराठी</option>
                        <option value="mn">Mongolian - монгол</option>
                        <option value="ne">Nepali - नेपाली</option>
                        <option value="no">Norwegian - norsk</option>
                        <option value="nb">Norwegian Bokmål - norsk bokmål</option>
                        <option value="nn">Norwegian Nynorsk - nynorsk</option>
                        <option value="oc">Occitan</option>
                        <option value="or">Oriya - ଓଡ଼ିଆ</option>
                        <option value="om">Oromo - Oromoo</option>
                        <option value="ps">Pashto - پښتو</option>
                        <option value="fa">Persian - فارسی</option>
                        <option value="pl">Polish - polski</option>
                        <option value="pt">Portuguese - português</option>
                        <option value="pa">Punjabi - ਪੰਜਾਬੀ</option>
                        <option value="qu">Quechua</option>
                        <option value="ro">Romanian - română</option>
                        <option value="mo">Romanian (Moldova) - română (Moldova)</option>
                        <option value="rm">Romansh - rumantsch</option>
                        <option value="ru">Russian - русский</option>
                        <option value="gd">Scottish Gaelic</option>
                        <option value="sr">Serbian - српски</option>
                        <option value="sh">Serbo-Croatian - Srpskohrvatski</option>
                        <option value="sn">Shona - chiShona</option>
                        <option value="sd">Sindhi</option>
                        <option value="si">Sinhala - සිංහල</option>
                        <option value="sk">Slovak - slovenčina</option>
                        <option value="sl">Slovenian - slovenščina</option>
                        <option value="so">Somali - Soomaali</option>
                        <option value="st">Southern Sotho</option>
                        <option value="es">Spanish - español</option>
                        <option value="su">Sundanese</option>
                        <option value="sw">Swahili - Kiswahili</option>
                        <option value="sv">Swedish - svenska</option>
                        <option value="tg">Tajik - тоҷикӣ</option>
                        <option value="ta">Tamil - தமிழ்</option>
                        <option value="tt">Tatar</option>
                        <option value="te">Telugu - తెలుగు</option>
                        <option value="th">Thai - ไทย</option>
                        <option value="ti">Tigrinya - ትግርኛ</option>
                        <option value="to">Tongan - lea fakatonga</option>
                        <option value="tr">Turkish - Türkçe</option>
                        <option value="tk">Turkmen</option>
                        <option value="tw">Twi</option>
                        <option value="uk">Ukrainian - українська</option>
                        <option value="ur">Urdu - اردو</option>
                        <option value="ug">Uyghur</option>
                        <option value="uz">Uzbek - o‘zbek</option>
                        <option value="vi">Vietnamese - Tiếng Việt</option>
                        <option value="wa">Walloon - wa</option>
                        <option value="cy">Welsh - Cymraeg</option>
                        <option value="fy">Western Frisian</option>
                        <option value="xh">Xhosa</option>
                        <option value="yi">Yiddish</option>
                        <option value="yo">Yoruba - Èdè Yorùbá</option>
                        <option value="zu">Zulu - isiZulu</option>
                    </select>
                    <input class="add_lang_btn" type="button" value="Add Language" />
                    <p>*Add new language will refresh the current page.</p>
                </div>' ;

            /*
            * POP UP END COMMON PART 
            */
            echo '      </div>
                    </div>';
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