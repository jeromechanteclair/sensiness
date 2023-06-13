<?php 
// namespace sensiness\app;

class Commands  {
   public function __invoke( $args ) {
      
			
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
				'comment_type'         => '',
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

if (class_exists('WP_CLI')) {

    WP_CLI::add_command('import_comments', 'Commands');
}