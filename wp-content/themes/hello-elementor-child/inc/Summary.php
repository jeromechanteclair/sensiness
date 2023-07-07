<?php

namespace sensiness\app;

class Summary
{
    public function __construct()
    {
		add_action('save_post',array($this,'add_summary_meta'));

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
			);
		}

		// Enregistrer les attributs id et le contenu des titres dans le post meta
		update_post_meta($post_id, 'summary', $attributs_id_titres);



	}
}

new Summary();
