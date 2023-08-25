<?php

$thumbnail = get_field('image');
$video = get_field('video');
$title = get_field('title');
$subtitle = get_field('subtitle');

$link = get_field('link');
$rating = sensiness\app\Woocommerce::get_average_review_rating();
$total = sensiness\app\Woocommerce::get_total_review_comments();

?>
<section class="hero-video">
	<div class="container">
		<div class="reviews-cartouche">
		<span class="title">
			Avis de nos clients
		</span>
		<span class="rating">
			<?=$rating;?>/5
		</span>
		<div class="stars">
			<svg width="80" height="16" viewBox="0 0 80 16" fill="none" xmlns="http://www.w3.org/2000/svg">
			<mask id="mask0_155_3161" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="48" y="0" width="16" height="16">
			<rect x="48" width="16" height="16" fill="#D9D9D9"/>
			</mask>
			<g mask="url(#mask0_155_3161)">
			<path d="M55.999 11.1225L53.7567 12.8327C53.6601 12.9071 53.5578 12.9415 53.4497 12.936C53.3415 12.9304 53.2447 12.8994 53.1593 12.843C53.0738 12.7866 53.008 12.7078 52.9618 12.6065C52.9157 12.5052 52.914 12.396 52.9567 12.2789L53.817 9.47124L51.6503 7.92126C51.5469 7.85374 51.4817 7.76592 51.4548 7.65781C51.4279 7.54969 51.4324 7.44734 51.4683 7.35076C51.5042 7.25418 51.5638 7.17149 51.6471 7.10269C51.7304 7.03388 51.8307 6.99947 51.9477 6.99947H54.6426L55.5195 4.10719C55.5623 3.9901 55.627 3.89951 55.7137 3.83541C55.8005 3.77131 55.8956 3.73926 55.999 3.73926C56.1024 3.73926 56.1975 3.77131 56.2842 3.83541C56.371 3.89951 56.4357 3.9901 56.4785 4.10719L57.3554 6.99947H60.0502C60.1673 6.99947 60.2675 7.03388 60.3509 7.10269C60.4342 7.17149 60.4938 7.25418 60.5297 7.35076C60.5656 7.44734 60.5701 7.54969 60.5432 7.65781C60.5163 7.76592 60.4511 7.85374 60.3477 7.92126L58.181 9.47124L59.0413 12.2789C59.084 12.396 59.0823 12.5052 59.0361 12.6065C58.99 12.7078 58.9242 12.7866 58.8387 12.843C58.7532 12.8994 58.6564 12.9304 58.5483 12.936C58.4402 12.9415 58.3379 12.9071 58.2413 12.8327L55.999 11.1225Z" fill="#F5F3E6"/>
			</g>
			<mask id="mask1_155_3161" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="16" y="0" width="16" height="16">
			<rect x="16" width="16" height="16" fill="#D9D9D9"/>
			</mask>
			<g mask="url(#mask1_155_3161)">
			<path d="M23.999 11.1225L21.7567 12.8327C21.6601 12.9071 21.5578 12.9415 21.4497 12.936C21.3415 12.9304 21.2447 12.8994 21.1593 12.843C21.0738 12.7866 21.008 12.7078 20.9618 12.6065C20.9157 12.5052 20.914 12.396 20.9567 12.2789L21.817 9.47124L19.6503 7.92126C19.5469 7.85374 19.4817 7.76592 19.4548 7.65781C19.4279 7.54969 19.4324 7.44734 19.4683 7.35076C19.5042 7.25418 19.5638 7.17149 19.6471 7.10269C19.7304 7.03388 19.8307 6.99947 19.9477 6.99947H22.6426L23.5195 4.10719C23.5623 3.9901 23.627 3.89951 23.7137 3.83541C23.8005 3.77131 23.8956 3.73926 23.999 3.73926C24.1024 3.73926 24.1975 3.77131 24.2842 3.83541C24.371 3.89951 24.4357 3.9901 24.4785 4.10719L25.3554 6.99947H28.0502C28.1673 6.99947 28.2675 7.03388 28.3509 7.10269C28.4342 7.17149 28.4938 7.25418 28.5297 7.35076C28.5656 7.44734 28.5701 7.54969 28.5432 7.65781C28.5163 7.76592 28.4511 7.85374 28.3477 7.92126L26.181 9.47124L27.0413 12.2789C27.084 12.396 27.0823 12.5052 27.0361 12.6065C26.99 12.7078 26.9242 12.7866 26.8387 12.843C26.7532 12.8994 26.6564 12.9304 26.5483 12.936C26.4402 12.9415 26.3379 12.9071 26.2413 12.8327L23.999 11.1225Z" fill="#F5F3E6"/>
			</g>
			<mask id="mask2_155_3161" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="32" y="0" width="16" height="16">
			<rect x="32" width="16" height="16" fill="#D9D9D9"/>
			</mask>
			<g mask="url(#mask2_155_3161)">
			<path d="M39.999 11.1225L37.7567 12.8327C37.6601 12.9071 37.5578 12.9415 37.4497 12.936C37.3415 12.9304 37.2447 12.8994 37.1593 12.843C37.0738 12.7866 37.008 12.7078 36.9618 12.6065C36.9157 12.5052 36.914 12.396 36.9567 12.2789L37.817 9.47124L35.6503 7.92126C35.5469 7.85374 35.4817 7.76592 35.4548 7.65781C35.4279 7.54969 35.4324 7.44734 35.4683 7.35076C35.5042 7.25418 35.5638 7.17149 35.6471 7.10269C35.7304 7.03388 35.8307 6.99947 35.9477 6.99947H38.6426L39.5195 4.10719C39.5623 3.9901 39.627 3.89951 39.7137 3.83541C39.8005 3.77131 39.8956 3.73926 39.999 3.73926C40.1024 3.73926 40.1975 3.77131 40.2842 3.83541C40.371 3.89951 40.4357 3.9901 40.4785 4.10719L41.3554 6.99947H44.0502C44.1673 6.99947 44.2675 7.03388 44.3509 7.10269C44.4342 7.17149 44.4938 7.25418 44.5297 7.35076C44.5656 7.44734 44.5701 7.54969 44.5432 7.65781C44.5163 7.76592 44.4511 7.85374 44.3477 7.92126L42.181 9.47124L43.0413 12.2789C43.084 12.396 43.0823 12.5052 43.0361 12.6065C42.99 12.7078 42.9242 12.7866 42.8387 12.843C42.7532 12.8994 42.6564 12.9304 42.5483 12.936C42.4402 12.9415 42.3379 12.9071 42.2413 12.8327L39.999 11.1225Z" fill="#F5F3E6"/>
			</g>
			<mask id="mask3_155_3161" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="16" height="16">
			<rect width="16" height="16" fill="#D9D9D9"/>
			</mask>
			<g mask="url(#mask3_155_3161)">
			<path d="M7.99899 11.1225L5.75671 12.8327C5.66012 12.9071 5.55777 12.9415 5.44966 12.936C5.34153 12.9304 5.24474 12.8994 5.15927 12.843C5.07381 12.7866 5.008 12.7078 4.96184 12.6065C4.91569 12.5052 4.91399 12.396 4.95672 12.2789L5.81697 9.47124L3.65032 7.92126C3.5469 7.85374 3.48173 7.76592 3.45481 7.65781C3.42788 7.54969 3.43237 7.44734 3.46827 7.35076C3.50417 7.25418 3.56378 7.17149 3.64711 7.10269C3.73044 7.03388 3.83065 6.99947 3.94774 6.99947H6.64259L7.51951 4.10719C7.56225 3.9901 7.627 3.89951 7.71374 3.83541C7.8005 3.77131 7.89558 3.73926 7.99899 3.73926C8.1024 3.73926 8.19748 3.77131 8.28424 3.83541C8.37098 3.89951 8.43573 3.9901 8.47847 4.10719L9.35539 6.99947H12.0502C12.1673 6.99947 12.2675 7.03388 12.3509 7.10269C12.4342 7.17149 12.4938 7.25418 12.5297 7.35076C12.5656 7.44734 12.5701 7.54969 12.5432 7.65781C12.5163 7.76592 12.4511 7.85374 12.3477 7.92126L10.181 9.47124L11.0413 12.2789C11.084 12.396 11.0823 12.5052 11.0361 12.6065C10.99 12.7078 10.9242 12.7866 10.8387 12.843C10.7532 12.8994 10.6564 12.9304 10.5483 12.936C10.4402 12.9415 10.3379 12.9071 10.2413 12.8327L7.99899 11.1225Z" fill="#F5F3E6"/>
			</g>
			<mask id="mask4_155_3161" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="64" y="0" width="16" height="16">
			<rect x="64" width="16" height="16" fill="#D9D9D9"/>
			</mask>
			<g mask="url(#mask4_155_3161)">
			<path d="M71.9993 5.93279V9.86612L73.5993 11.0995L72.9993 9.06612L74.4993 7.99946H72.6327L71.9993 5.93279ZM71.9993 11.1225L69.7571 12.8327C69.6605 12.9071 69.5581 12.9415 69.45 12.936C69.3419 12.9304 69.2451 12.8994 69.1596 12.843C69.0742 12.7866 69.0083 12.7078 68.9622 12.6065C68.916 12.5052 68.9143 12.396 68.9571 12.2789L69.8173 9.47124L67.6507 7.92126C67.5458 7.85449 67.4803 7.76686 67.4541 7.65836C67.4279 7.54987 67.4327 7.44734 67.4686 7.35076C67.5045 7.25418 67.5642 7.17149 67.6477 7.10269C67.7311 7.03388 67.8313 6.99947 67.9481 6.99947H70.6458L71.5199 4.10719C71.5614 3.98878 71.6259 3.89785 71.7132 3.83441C71.8006 3.77097 71.8959 3.73926 71.9993 3.73926C72.1028 3.73926 72.1981 3.77097 72.2855 3.83441C72.3728 3.89785 72.4372 3.98878 72.4788 4.10719L73.3529 6.99947H76.0506C76.1674 6.99947 76.2676 7.03388 76.351 7.10269C76.4345 7.17149 76.4942 7.25418 76.5301 7.35076C76.566 7.44734 76.5708 7.54987 76.5446 7.65836C76.5184 7.76686 76.4529 7.85449 76.348 7.92126L74.1814 9.47124L75.0416 12.2789C75.0843 12.396 75.0826 12.5052 75.0365 12.6065C74.9903 12.7078 74.9245 12.7866 74.8391 12.843C74.7536 12.8994 74.6568 12.9304 74.5487 12.936C74.4406 12.9415 74.3382 12.9071 74.2416 12.8327L71.9993 11.1225Z" fill="#F5F3E6"/>
			</g>
			</svg>
		</div>
		<span class="total">
				<?=$total;?> Avis
		</span>
		</div>
	<picture>
		<source srcset="<?= $thumbnail['url'];?>' 2x,<?= $thumbnail['url'];?>" media="(min-width: 999px)"/>
		<source srcset="<?= $thumbnail['url'];?> 2x,<?= $thumbnail['url'];?>" media="(max-width: 999px)"/>
		<img src="<?= $thumbnail['url'];?>" alt="hero" loading="lazy"/>
	</picture>
		<video  id='video-home' playsinline preload='metadata' width='100%' height='100%' loop muted preload="none" poster="<?= $thumbnail['url'];?>">

	

		<source src="<?= $video['url'];?>" type="<?= $video['mime_type'];?>" />

	</video>
		<?= $title ;?>
		<p class="subtitle">
			<?= $subtitle ;?>
		</p>
		<a href="<?= $link['url'];?>" class="button">
			<?= $link['title'];?>

		</a>
	</div>
</section>