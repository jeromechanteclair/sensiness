<div class="wrap">
    <div id="icon-themes" class="icon32"></div>  
    <h2>Optin Settings</h2>  
    <!--Display errors if there are some -->
    <?php settings_errors(); ?>  
    
    <form method="POST" action="options.php">
        <?php 
            //On charge les champs définis
            settings_fields('probance-optin_optin-settings');
            do_settings_sections( 'probance' ); 
        ?>             
        <?php submit_button(); ?>  
    </form> 
</div>