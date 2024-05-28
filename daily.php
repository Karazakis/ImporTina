<?php

global $wpdb;

$table_name = $wpdb->prefix . '_imporTinaMainTab';
$update_table = $wpdb->prefix . '_update_info';
$setup = $wpdb->get_var("SELECT setup FROM $table_name");
if($setup == 1)
{
    $wpdb->query("UPDATE $update_table SET update_status = 1");
    $wpdb->query("UPDATE $update_table SET counter = 0");
    $wpdb->query("UPDATE $update_table SET current_uploaded = 0");
    $wpdb->query("UPDATE $update_table SET esauriti = 0");
    $wpdb->query("UPDATE $update_table SET falliti = 0");
    $wpdb->query("UPDATE $update_table SET import_total = 0");
    $wpdb->query("UPDATE $update_table SET cicli_total = 0");
    $wpdb->query("UPDATE $update_table SET cicli_completed = 0");
    
}


?>