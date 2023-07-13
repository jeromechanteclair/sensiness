<?php 
// namespace sensiness\app;

class Commands  {
   public function __invoke( $args ) {
      

	if(isset($args[0])=='update'){
				
		$args = array(
			'type' => 'comment',
			'fields' => 'comment_ID'
		);

		$comments_query = new WP_Comment_Query($args);
$comments = $comments_query->comments;



		if (!empty($comments)) {
			foreach ($comments as $comment) {
				// var_dump($comment_id);die();
$comment_id = $comment->comment_ID;
				// $comment = get_comment($comment_id);
				$comment_post_id = $comment->comment_post_ID;

				// Vérifier si le comment_post_id correspond à un produit
				$product = wc_get_product($comment_post_id);
				if ($product) {
					// Mettre à jour le comment_type en "review"
					$updated_comment_data = array(
						'comment_ID' => $comment_id,
						'comment_type' => 'review'
					);
					wp_update_comment($updated_comment_data);

					// Afficher un message de confirmation
					WP_CLI::success( "Comment ID: $comment_id - Commentaire mis à jour en tant que 'review'");
				} else {
					// Le comment_post_id ne correspond pas à un produit
						WP_CLI::error ("Comment ID: $comment_id - Ne correspond pas à un produit");
				}

				
			}
		} 



		}
		else{
		
		$file_path = __DIR__;
		$file = $file_path.'/import/import.csv';
	
		$row = 1;
		if (($handle = fopen($file , "r")) !== false) {
			while (($data = fgetcsv($handle, 1000, ",")) !== false) {
				$num = count($data);
				// echo "<p> $num champs à la ligne $row: <br /></p>\n";
				$row++;
				$name =$data[0] ;
			
				$content =$data[2];
				$rating =$data[3];
				$product_id =$data[4];
				// $date=date('d-m-Y H:i:s', $first_date);

				
				$dateString = str_replace('/', '-',$data[1]);
				$date = new DateTime($dateString);
				$format =$date->format('Y-m-d H:i:s');


		


			$comment_id = wp_insert_comment(array(
				'comment_post_ID'      => $product_id, // <=== The product ID where the review will show up
				'comment_author'       => $name,
				'comment_author_email' => $name, // <== Important
				'comment_author_url'   => '',
				'comment_content'      => $content,
				// 'comment_type'         => 'review',
				'comment_parent'       => 0,
				'user_id'              => 0, // <== Important
				'comment_author_IP'    => '',
				'comment_agent'        => '',
				'comment_date'         =>$format ,
				'comment_approved'     => 1,
			));

			update_comment_meta($comment_id, 'rating',$rating);

					 
				
				

			}
			fclose($handle);
		}
		WP_CLI::success("Commentaires importés!");
			
		}


    }

}

if (class_exists('WP_CLI')) {

    WP_CLI::add_command('import_comments', 'Commands');
}