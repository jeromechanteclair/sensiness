<?php

namespace sensiness\app;

class Summary
{
    public function __construct()
    {
		add_action('save_post',array($this,'add_summary_meta'));
		add_action('add_meta_boxes', array($this,'metabox_summary'));


    }
	public function metabox_summary(){
		
		add_meta_box(
			'summary_metabox', // ID unique de la metabox
			'Sommaire', // Titre de la metabox
			 array($this,'display_fields'), // Fonction pour afficher le champ
			'post', // Type de contenu auquel la metabox sera ajoutée (articles)
			'normal', // Contexte de la metabox (normal, side, advanced)
			'high' // Priorité de la metabox (high, low)
		);

	}
	public function display_fields($post){
		
		// Récupérer la valeur actuelle du champ "summary" s'il existe
		$summary = get_post_meta($post->ID, 'summary', true);
		$frontend_summary = get_post_meta($post->ID, 'frontend_summary', true);
		// var_dump($frontend_summary);die();
		if(!empty($frontend_summary)){
			foreach($summary as $key=> $item){
				
				echo '<label for="frontend_summary['.$key.']">Titre du lien :</label>';
				echo '<input type="text" id="frontend_summary['.$key.'][content]" name="frontend_summary['.$key.'][content]" style="width: 100%;" 
				value="'. wp_strip_all_tags($frontend_summary[$key]['content']) .'">';
				echo '<input type="hidden" id="frontend_summary['.$key.'][id]" name="frontend_summary['.$key.'][id]" style="width: 100%;" 
				value="'. wp_strip_all_tags($item['id']) .'">';
				echo'<br>';
				echo'<br>';
				echo '<label for="frontend_summary['.$key.'][is_display]">';
				echo '<input type="checkbox" id="frontend_summary['.$key.'][is_display]" name="frontend_summary['.$key.'][is_display]" 
				value="'.$frontend_summary[$key]['is_display'].'" ' . checked(1, $frontend_summary[$key]['is_display'], false) . '>';
				echo ' Afficher le lien';
				echo '</label>';
				echo'<br>';
				echo'<hr>';

			}
		}
		else if(!empty($summary)){
			foreach($summary as $key=> $item){
				
				echo '<label for="frontend_summary['.$key.']">Titre du lien :</label>';
				echo '<input type="text" id="frontend_summary['.$key.'][content]" name="frontend_summary['.$key.'][content]" style="width: 100%;" 
				value="'. wp_strip_all_tags($item['contenu']) .'">';
				echo '<input type="hidden" id="frontend_summary['.$key.'][id]" name="frontend_summary['.$key.'][id]" style="width: 100%;" 
				value="'. wp_strip_all_tags($item['id']) .'">';
				echo'<br>';
				echo'<br>';
				echo '<label for="frontend_summary['.$key.'][is_display]">';
				echo '<input type="checkbox" id="frontend_summary['.$key.'][is_display]" name="frontend_summary['.$key.'][is_display]" 
				value="1" ' . checked(1, $item['is_display'], false) . '>';
				echo ' Afficher le lien';
				echo '</label>';
				echo'<br>';
				echo'<hr>';

			}
		}

	}

	public function add_summary_meta($post_id){


		if (get_post_type($post_id) !== 'post') {
			return;
		}

		// Récupérer le contenu de l'article
		$contenu_article = get_post_field('post_content', $post_id);

		// Utiliser une expression régulière pour extraire les attributs id et le contenu des titres
		preg_match_all('/<(h[2-2])[^>]*id=["\']([^"\']+)["\'][^>]*>(.*?)<\/\1>/is', $contenu_article, $correspondances, PREG_SET_ORDER);

		// Enregistrer les attributs id et le contenu des titres dans un post meta
		$attributs_id_titres = array();

		foreach ($correspondances as $correspondance) {
			$balise = $correspondance[1];
			$attribut_id = $correspondance[2];
			$contenu = $correspondance[3];

			$attributs_id_titres[] = array(
				'balise' => $balise,
				'id' => $attribut_id,
				'contenu' => $contenu,
				'is_display'=>true
			);
		}

		// Enregistrer les attributs id et le contenu des titres dans le post meta
		update_post_meta($post_id, 'summary', $attributs_id_titres);
  	// Vérifier les permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        // Vérifier si le champ "summary" est défini
        if (isset($_POST['frontend_summary'])) {
            // Échapper les données avant de les enregistrer dans la base de données
            // $summary = sanitize_textarea_field($_POST['frontend_summary']);
            // Enregistrer le champ "summary" dans la base de données
            update_post_meta($post_id, 'frontend_summary', $_POST['frontend_summary']);
        } else {
            // Si le champ est vide, supprimer la valeur du champ "summary" de la base de données
            // delete_post_meta($post_id, 'summary');
        }
    }


	
}

new Summary();
