<?php

namespace sensiness\app;

class Post
{
    public function __construct()
    {
        add_filter('excerpt_length', array($this,'custom_excerpt_length'), 999);

        add_action('save_post',  array($this,'set_custom_cookie_on_save_post'));
        


    }
	    public    function custom_excerpt_length($length)        {
            return 40;
        }
        public   function set_custom_cookie_on_save_post($post_id)
        {
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }
            $post_type = get_post_type($post_id);
            if($post_type=='popup'){

                // Votre code pour définir le cookie ici

                if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                    return;
                }

                // Récupérez l'heure actuelle
                $heure_sauvegarde = current_time('mysql');

                // Enregistrez l'heure de sauvegarde en tant qu'option
                update_option('popup_time_' . $post_id, $heure_sauvegarde);
            }
        }




		// return '';

	

  
}

new Post();
