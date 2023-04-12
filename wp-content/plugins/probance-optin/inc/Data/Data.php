<?php
 
/**
 * @package probance-optin
 */

namespace Inc\Data;

use Inc\Common\Utils;

/**
 * Parent class used to create settings fields variables
 * Used by : Inc\Data\NewsletterFields
 */
class Data
{
    public $data;

    /**
     * Instanciate class variables
     * @param $data array
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     *Get function to return array of data
     * @return $data array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Function to get all options created
     * @return array : return an array with all plugin option ids 
     */
    private function getAllOptionsIds()
    {

        $option_ids=array();
        $languages=Utils::getLanguages();

        foreach($this->data as $d)
        {
            array_push($option_ids, $d['id']);

            // Retrieve properties options
            if(isset($d['properties']))
            {
                foreach($d['properties'] as $key=>$value)
                {
                    array_push($option_ids, $d['id']."-".$key);
                }
            }

            // Retrieve translations options
            if(isset($d['to_translate']) && $d['to_translate'])
            {
                if(count($languages)>0)
                {
                    foreach($languages as $lang)
                    {
                        $trsl_id=$d['id']."-".strtolower($lang);
                        array_push($option_ids, $trsl_id);
                    }
                }
            }
        }

        return $option_ids;
    }

    /**
     * 
     */
    public function deleteAllOptions()
    {
        
        $options=$this->getAllOptionsIds();

        foreach($options as $o)
        {
            
            if( PROB_DEBUG == 1 ) Utils::write_log("Deleting $o option.");

            delete_option($o);
        }
    }
}

?>