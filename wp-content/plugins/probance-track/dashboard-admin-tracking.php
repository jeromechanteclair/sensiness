<div class="wrap">
    <div id="icon-themes" class="icon32"></div>  
    <h2>Tracking Settings</h2>  
    <!--Display errors if there are some -->
    <?php settings_errors(); ?>  
    
    <form method="POST" action="options.php">
        <?php 
            //On charge les champs définis
            settings_fields('probance-track_settings');
            do_settings_sections( 'dashboard-admin-track' ); 
        ?>             
        <?php submit_button(); ?>  
    </form> 
</div>