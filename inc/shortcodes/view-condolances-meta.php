<div class="condolances-meta">
	<ul>
		<li>Geboortedatum: <strong><?php 
			$originalDate = get_post_meta($a['post_id'], 'cmb_condalances_birthday', true);
		$newDate = date("d/m/Y", strtotime($originalDate));

		echo $newDate; 

?></strong></li>
		<li>Overlijdensdatum: <strong><?php 
$originalDate = get_post_meta($a['post_id'], 'cmb_condalances_deathday', true); 
$newDate = date("d/m/Y", strtotime($originalDate));

		echo $newDate; 
?></strong></li>
		<li>Aantal Reacties: <strong><?php echo get_comments_number(); ?></strong></li>
	</ul>
</div>