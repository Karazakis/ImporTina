<?php
/**
 * Plugin Name: ImporTina
 * Description: Plugin di organizzazione di stock data, ridimensionamento e conversione immagini in WEBP, e inventory item managment
 * Version: 1.0
 * Author: A.K & M.M
 */


require_once(plugin_dir_path(__FILE__) . 'importer.php');
require_once(plugin_dir_path(__FILE__) . 'install.php');
require_once(plugin_dir_path(__FILE__) . 'view.php');
require_once(plugin_dir_path(__FILE__) . 'controller.php');
require_once(plugin_dir_path(__FILE__) . 'imgmoduler.php');
require_once(plugin_dir_path(__FILE__) . 'utils.php');

register_activation_hook(__FILE__, 'custom_plugin_activation');
register_uninstall_hook(__FILE__, 'custom_plugin_uninstall');
add_action('admin_menu', 'custom_plugin_menu');
 

 // Funzione per visualizzare il contenuto della pagina del plugin
function custom_plugin_page() {
    global $wpdb;
    $OnError = false;
    $isParsing = false;
    $table_name = $wpdb->prefix . '_imporTinaMainTab';
    $brand_table =  $wpdb->prefix . '_brands';
    $categories_table = $wpdb->prefix . '_categories';
    // Esegui una query per ottenere il valore di 'first' e 'second'
    $first_value = $wpdb->get_var("SELECT first FROM $table_name");
    $first_step = $wpdb->get_var("SELECT second FROM $table_name");
    $second_step = $wpdb->get_var("SELECT third FROM $table_name");
    $third_step = $wpdb->get_var("SELECT fourth FROM $table_name");
    $fourth_step = $wpdb->get_var("SELECT fifth FROM $table_name");
    $setup_done = $wpdb->get_var("SELECT setup FROM $table_name");
    $finished = $wpdb->get_var("SELECT success FROM $table_name");

    ?>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <div class="wrap">
        <div class="plugin_row">
            <h1 id="plugin_title"> ImporTina </h1> <br>
            <h2 id="plugin_subtitle">a ZetaStudio Plugin</h2>
        </div>
        <div class="plugin_row content_row">
        <div class="plugin_content">
    <?php
    
    if (!$first_value) {
        setup_view();
    } elseif ($first_value && !$first_step) {
        firststep_view();
    }
    elseif ($first_value && $first_step && !$second_step)
    {
        secondstep_view($brand_table, $wpdb);
    } 
    elseif($first_value && $first_step && $second_step && !$third_step)
    {
        thirdstep_view($categories_table, $wpdb);
    }
    elseif ($first_value && $first_step && $second_step && $third_step && !$fourth_step)
    {
        fourthstep_view();
    }
    elseif ($first_value && $first_step && $second_step && $third_step && $fourth_step && !$setup_done)
    {
        import_view();
    }
/*     elseif ($first_value && $first_step && $second_step && $third_step && $fourth_step && $setup_done)
    {
        loading_view();
    } */
    ?>
    </div>
    </div>
    <div class="plugin_row">
        <button class="btn btn-danger btn-lg" type="submit" onclick="showbtn()">Reset Setup</button>
    </div>
    <div class="plugin_row">
        <button class ="btn btn-danger btn-lg" type="submit" onclick="showTrash()">Empty Product Bin</button>
    </div>
    <div class="popup-container" id="popup" style="display: none;">
        <div class="popup-content">
            <h2>Alert!</h2>
            <p>Sei sicuro di voler procedere? Questa azione riavvierà l'installazione.</p>
            <form method="post">
                <button class="btn accept-btn" name="reset" onclick="accept()">Conferma</button>
            </form>
            <button class="btn cancel-btn" onclick="cancel()">Annulla</button>
        </div>
    <script src="script.js"> showPopup(); </script>
    </div>
    <div class="popup-container" id="trashclean" style="display:none;">
        <div class="popup-content">
            <h2>Alert!</h2>
            <p>Questa azione eliminerà definitivamente tutti i prodotti dal cestino. Vuoi procedere?</p>
            <form method="post">
                <button class="btn accept-btn" name="Trash">Conferma</button>
            </form>
                <button class="btn cancel-btn" onclick="cancel()">Annulla</button>
            <script src="script.js">showSecondPopup();</script>
        </div>
    </div>
    <?php
    plugin_controller($_POST, $OnError ,$isParsing);
}
