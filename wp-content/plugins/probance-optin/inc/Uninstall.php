<?php 

/**
 * @package probance-optin
 */

namespace Inc;

use Inc\Data\NewsletterFields;
use Inc\Data\ApiFields;
use Inc\Data\WebelementFields;
use Inc\Common\Utils;

final class Uninstall 
{

    /**
     * Delete all options created by the project
     * @return
     */
    public static function delete_options()
    {
        /**
         * DISABLED
         * 
         */    
        
        // Remove Newsletter Fields options
        // (new NewsletterFields())->deleteAllOptions();

        // // Remove API Fields options0
        // (new ApiFields())->deleteAllOptions();

        // // Remove Webelement Fields options
        // (new WebelementFields())->deleteAllOptions();

        // // Created languages
        // delete_option('probance-optin-languages');

        return null;
    }
}

?>