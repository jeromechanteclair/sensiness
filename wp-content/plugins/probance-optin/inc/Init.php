<?php 

/**
 * @package probance-optin
 */

namespace Inc;

final class Init 
{

    /**
     * Store all classes inside an array
     * @return array Full list of classes
     */
    public static function get_services()
    {
        return [
            Base\Enqueue::class,
            Base\Ajax::class,
            Pages\Admin::class,
            Pages\Newsletter::class,
            Pages\OptinConsents::class
        ];
    }

    /**
     * Loop througt the classes, initialize them,
     * and call the register method if it exists
     * @return
     */
    public static function register_services()
    {
        foreach (self::get_services() as $class) {
            $service = self::instanciate($class);
            if ( method_exists($service, 'register') )
            {
                $service->register();
            }
        }
    }

    /**
     * Initialize the class
     * @param   class $class       class from the services array
     * @return  class $instance    new instance of the class 
     */
    private static function instanciate( $class ) 
    {  
        $service = new $class();

        return $service;
    }
}

?>