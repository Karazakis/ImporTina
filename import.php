<?php
require_once('wp-load.php');
require_once(plugin_dir_path(__FILE__) . 'importer.php');
require_once(plugin_dir_path(__FILE__) . 'controller.php');

global $wpdb;
$table_name = $wpdb->prefix . '_imporTinaMainTab';
$submitted_xml = $wpdb->get_var("SELECT xml_stock FROM $table_name");
$prod_info = array();
$prod_info = leggiFileSTOCKXML($submitted_xml);
$wpdb->query("UPDATE $table_name SET setup = 1");
file_import($table_name, $wpdb, $prod_info);

?>