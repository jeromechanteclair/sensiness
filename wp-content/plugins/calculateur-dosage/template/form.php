<?php




$pattern = '/\[(.*)\]/';
$replacement = '<span class="posologie"><span class="posologie__value">10</span>mg CBD /jour</span>';
$posologie= preg_replace($pattern, $replacement, $posologie);


?>
<div class="cbd-calculator">
	<div class="cbd-calculator__container">
		<!-- Titre -->
		<div class="cbd-calculator__title">
		
			<?php if($isproduct):?>
			<h2>Calculez votre dosage</h2>
			<?php else:?>
				<?= $title;?>
			<?php endif;?>
		</div>
		<!-- Description -->
		<div class="cbd-calculator__description">
			<?php if(!$isproduct):?>
			<?= $description;?>
			
			<?php endif;?>
		</div>
		<form class="cbd-calculator-form">
			<div class="radios 	<?php if($isproduct):?>hide <?php endif;?>">
				<input type="radio" id="client_false" name="client" value="client_false" <?php if(!$isproduct):?>checked<?php endif;?>>
				<label for="client_false">Connaitre mon dosage</label>
				<input type="radio" id="client_true" name="client" value="client_true"  <?php if($isproduct):?>checked<?php endif;?>>
				<label for="client_true">J’ai déjà un produit Sensiness </label>
      		
			</div>

			<?php if(!empty($products || $isproduct)):?>
				
				<div class="product_selector hide">
		
					<label for="product">Votre produit</label>
					<select id="product" name="product">
						<?php if(!$isproduct):?><option name="product" value="*">Choisissez votre produit</option><?php endif;?>
						<?php foreach($products as $product):
							if($isproduct){
								$id =$product;
							}
							else{

								$id = $product->product_id;
							}
                    			$product = wc_get_product($id);
                    			$title = $product->get_name();
								$posologies = get_the_terms($id, 'pa_traitement');
								$posologies = wp_list_pluck($posologies, 'term_taxonomy_id');
							?>
							<option name="product" <?php if($isproduct):?>selected <?php endif;?>value="<?=json_encode($posologies);?>"  ><?=$title;?></option>
						<?php endforeach;?>
					</select>
				</div>
			<?php endif;?>
			<?php if(!empty($traitement)):?>
				<div class="traitement_selector ">
					<label for="traitement">Traitement</label>
					<select id="traitement" name="traitement">

						<?php foreach($traitement as $maux):?>
							<?php $values = get_term_meta($maux->term_taxonomy_id, 'poids', true);	?>
							<option name="traitement" value="<?=$maux->term_taxonomy_id;?>" data-posologie='<?=json_encode($values);?>'>
							<?=$maux->name;?>
						</option>
						<?php endforeach;?>
					</select>
				</div>
			<?php endif;?>
			<div class="human_weight_selector ">
				<label for="human_weight">Votre poids</label>
				<input type="range" id="human_weight" name="human_weight" min="0" max="150" value="70" step="1">
				<span class="range_value"><span>70</span>kg</span>
			</div>
					<!-- Posologie -->
		<div class="cbd-calculator__posologie">
			<h3>Posologie</h3>
			<?= $posologie;?>
		</div>
	
		
		</form>

		<div class="cbd-calculator__results">

		</div>
		
	</div>
</div>
