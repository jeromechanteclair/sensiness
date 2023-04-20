<?php 
/**
 * FAQTaxonomy class File : ajoute le thématique aux thématiques
 *

 */

class FAQTaxonomy
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
        'name' => _x('Thématique ', 'taxonomy general name'),
        'singular_name' => _x('Thématique', 'taxonomy singular name'),
        'search_items' => __('Rechercher une thématique'),
        'all_items' => __('Tous les thématiques'),
        'parent_item' => __('Thématique parent'),
        'parent_item_colon' => __('thématique parent:'),
        'edit_item' => __('Editer le thématique'),
        'update_item' => __('Modifier le thématique'),
        'add_new_item' => __('Ajouter une thématique'),
        'new_item_name' => __('Nouvelle thématique'),
        'menu_name' => __('Thématiques'),
        ];
        // nom, type de contenu, args
        register_taxonomy(
            'thematique_tags',
            ['thématique'],
            [
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'rewrite' => [
                    'slug' => 'thématique_thématiques',
            ],
            ]
        );
    }
    
    

  
}
