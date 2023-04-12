<?php 
/**
 * VilleTaxonomy class File : ajoute le ville aux villes
 *
 * @category  Class
 * @package   150_lillet
 * @author    Bigbump contact@bigbump.fr
 * @copyright Copyright 2015 Company, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE
 * @link      https://www.lillet.com/
 */

class VilleTaxonomy
{
    /**
     * Constructor
     *
     * @return void 
     */
    public function __construct()
    {
        add_action('init', array( $this, 'create' ));
    }

    /**
     * @return void 
     */
    public function create()
    {
        // traductions
        $labels = [
        'name' => _x('Ville ', 'taxonomy general name'),
        'singular_name' => _x('Ville', 'taxonomy singular name'),
        'search_items' => __('Rechercher une ville'),
        'all_items' => __('Tous les villes'),
        'parent_item' => __('Ville parent'),
        'parent_item_colon' => __('ville parent:'),
        'edit_item' => __('Editer le ville'),
        'update_item' => __('Modifier le ville'),
        'add_new_item' => __('Ajouter une ville'),
        'new_item_name' => __('Nouvelle ville'),
        'menu_name' => __('Villes'),
        ];
        // nom, type de contenu, args
        register_taxonomy(
            'ville_tags',
            ['ville'],
            [
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'rewrite' => [
                    'slug' => 'ville_villes',
            ],
            ]
        );
    }
    
    

  
}
