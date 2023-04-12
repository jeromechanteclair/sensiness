<?php
$language = get_locale();
$language = get_bloginfo('language');
$firstImg = 'mbe_eship';
$secondImg = 'mbe_packing';
switch ($language) {
	case 'it-IT':
		$mbe_page_link = 'https://www.mbe.it/it/ecommerce';
        $firstImg = $firstImg . '_it' . '.jpg';
        $secondImg = $secondImg . '_it' . '.jpg';
		break;
	case 'fr-FR':
		$mbe_page_link = 'https://www.mbefrance.fr/fr/ecommerce';
        $firstImg = $firstImg . '_fr' . '.jpg';
        $secondImg = $secondImg . '_fr' . '.jpg';
		break;
	case 'es':
		$mbe_page_link = 'https://www.mbe.es/es/ecommerce';
        $firstImg = $firstImg . '_es' . '.jpg';
        $secondImg = $secondImg . '_es' . '.jpg';
		break;
	case 'pl-PL':
		$mbe_page_link = 'https://www.mbe.pl/pl/ecommerce';
        $firstImg = $firstImg . '_pl' . '.jpg';
        $secondImg = $secondImg . '_pl' . '.jpg';
		break;
	case 'de-DE':
		$mbe_page_link = 'https://www.mbe.de/de/ecommerce';
        $firstImg = $firstImg . '_de' . '.jpg';
        $secondImg = $secondImg . '_de' . '.jpg';
		break;
	default:
		$mbe_page_link = 'https://mbe.it/en/ecommerce';
		$firstImg = $firstImg . '.jpg';
		$secondImg = $secondImg . '.jpg';
		break;
}

$firstImg = MBE_ESHIP_PLUGIN_URL.'lib/images/'.sanitize_file_name($firstImg);
$secondImg = MBE_ESHIP_PLUGIN_URL.'lib/images/'.sanitize_file_name($secondImg);


?>
<style>
    .button-mbe {
        background-color: #cc2c24 !important;
        border-color: #cc2c24 !important;
    }

    .button-mbe:hover {
        background-color: darkred !important;
        border-color: darkred !important;
    }
    table.welcome-page {
        width: 100%;
        border:0;
        border-collapse: collapse;
        margin-bottom:30px
    }
    table.welcome-page td {
        padding: 0;
        width: 50%;
        text-align: center;
        background-color: #eaeaea;
    }
    table.welcome-page td div.mbe-service-text{
        text-align: left;
        padding: 15px;
    }
    table.welcome-page td img{
        width: 100%;
    }
</style>

<H2 style="color: red"><?php _e( MBE_ESHIP_PLUGIN_NAME.' Plugin', 'mail-boxes-etc' ) ?></H2>
<div>
    <p><?php _e( 'Give a new impulse to your e-commerce: with MBE solutions to digitize shipments and logistics, designed for companies, entrepreneurs but also artisans who are looking for a reliable partner who can support them by successfully bringing their business online with complete and flexible solutions, even on large marketplaces such as Amazon and e-Bay and are compatible with Magento CE, PrestaShop, Shopify, WooCommerce as well as the ability to customize them via API.', 'mail-boxes-etc' ) ?></p>
</div>
<div>
    <p><?php _e( 'If yes, start the configuration. If not, Contact us.', 'mail-boxes-etc' ) ?></p>
</div>
<div style="text-align: center">
    <a href="<?php echo $mbe_page_link ?>" class="button-mbe button-primary"
       target="_blank"><?php echo mb_strtoupper( __( 'contact us', 'mail-boxes-etc' ) ) ?></a>
    <a href="<?php echo get_admin_url(  get_current_blog_id(),'admin.php?page=' . mbe_e_link_get_settings_url() . '&tab=' . MBE_ESHIP_ID .'&section=mbe_general') ?>" class="button-mbe button-primary"
       target="_self"><?php echo mb_strtoupper( __( 'start the configuration', 'mail-boxes-etc' ) ) ?></a>
</div>
<div>
    <p><?php _e( 'By connecting MBE platform directly to your e-commerce website, you can automate the management of shipments and facilitate sales process.', 'mail-boxes-etc' ) ?></p>
</div>
<div>
    <h2 style="text-align: center"><?php _e( MBE_ESHIP_PLUGIN_NAME.' Plugin', 'mail-boxes-etc' ) ?></h2>
</div>
<div>
    <table class="welcome-page">
        <tr>
            <td>
                <div class="mbe-service-text">
                <h3><?php echo __('MBE Digital Solutions', 'mail-boxes-etc') ?></h3>
                <?php echo __( 'Turnkey digital services that help your brand take off. Reach your customers online with digital solutions and web marketing tools that give you more chances to reach your audience by building an effective online presence for your business and being where your customers are. MBE offers you effective solutions to grow your digital channels. Showcase your brand, e-commerce store, or improve your current website to make it more visible on search engines.', 'mail-boxes-etc') ?>
                </div>
            </td>
            <td><img alt="mbe digital solution" src="<?php echo $firstImg ?>"/></td>
        </tr>
        <tr>
            <td><img alt="mbe packing" src="<?php echo $secondImg ?>"/></td>
            <td>
                <div class="mbe-service-text">
                <h3><?php echo __('MBE Packing', 'mail-boxes-etc') ?></h3>
                <?php echo __('The packaging service that gives your business an edge. Your customers deserve a unique experience: surprise them during unboxing with customized packaging solutions for your products. A highly customized solution that enhances your brand and adds a touch of style based on your specific needs. In addition to optimizing time and resources, you can reduce your environmental impact as MBE uses innovative packaging techniques and materials.', 'mail-boxes-etc') ?>
                </div>
            </td>
        </tr>
    </table>
</div>
<div>
    <h3><?php echo __('Now you know you have a reliable partner by your side!', 'mail-boxes-etc') ?></h3>
    <p><?php echo __('MBE is the right end-to-end partner that supports all realities in the transition to digital by providing complete and turnkey solutions.', 'mail-boxes-etc') ?></p>
</div>
<div style="text-align: center">
    <a href="<?php echo $mbe_page_link ?>" class="button-mbe button-primary"
       target="_blank"><?php echo mb_strtoupper( __( 'Contact your MBE Center', 'mail-boxes-etc' ) ) ?></a>
</div>