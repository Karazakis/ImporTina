<?php
require_once("/home/customer/www/romance-boutique.it/public_html/wp-load.php");
require_once(plugin_dir_path(__FILE__) . 'utils.php');
//mettere i vari include per modula immagine e co
global $wpdb;
$start_time = time();
$table_name = $wpdb->prefix . '_imporTinaMainTab';
$update_table = $wpdb->prefix . '_update_info';
$jpeg_tmp = plugin_dir_path(__FILE__)."_immagini_tmp/";
$bande_bianche_tmp = plugin_dir_path(__FILE__)."_immagini_bande_bianche_tmp/";
$WEBP_directory = plugin_dir_path(__FILE__)."_WEBP_IMAGES/";
$submitted_xml = $wpdb->get_var("SELECT xml_stock FROM $table_name");
$prods_info = array();
$prods_info = leggiFileSTOCKXML($submitted_xml);

$import_start = $wpdb->get_var("SELECT import_start FROM $table_name");
$setup = $wpdb->get_var("SELECT setup FROM $table_name");
$status = $wpdb->get_var("SELECT update_status FROM $update_table");
$counter = $wpdb->get_var("SELECT counter FROM $update_table");
$total = $wpdb->get_var("SELECT total FROM $update_table");
$cicli_total = $wpdb->get_var("SELECT cicli_total FROM $update_table");
$cicli_completed = $wpdb->get_var("SELECT cicli_completed FROM $update_table");
$past = time();
error_log("ciclo " . $cicli_total . " start " . (time() - $start_time) . " diff " . (time() - $past));
$past = time();
//
if (!$import_start)
{
    return;
}
if ($counter >= $total)
{
    if (!$setup)
    {
        $wpdb->query("UPDATE $table_name SET setup = 1");
    }
    $wpdb->query("UPDATE $update_table SET update_status = 0");
    return;
}
error_log("ciclo " . $cicli_total . " pre-mid " . (time() - $start_time) . " diff " . (time() - $past));
$past = time();
if($status === '1')
{
    $new_cicli_total = $cicli_total + 1;
    $wpdb->query("UPDATE $update_table SET cicli_total = $new_cicli_total");
    $file_path = $wpdb->get_var("SELECT xml FROM $table_name");
    $file_content = file_get_contents($file_path);
    $xml = simplexml_load_string($file_content);
    $prodotti = array();
    $warehouse_stock = "";
    $supplier_stock = "";
    if (!file_exists($jpeg_tmp)) 
    {
        mkdir($jpeg_tmp, 0777, true);
    }
    if (!file_exists($bande_bianche_tmp))
    {
        mkdir($bande_bianche_tmp, 0777, true);
    }
    if (!file_exists($WEBP_directory))
    {
        mkdir($WEBP_directory, 0777, true);
    } 
    error_log("ciclo " . $cicli_total . " after_load " . (time() - $start_time) . " diff " . (time() - $past));
    $past = time();
    foreach ($xml->product as $product) {
        $stockflg = 0;
        $images = array();
        $details = array();
        foreach ($product->images as $image)
        {
            $images = $image;
        }
        foreach ($product->details as $detail)
        {
            $detail_array = array(); 
            foreach ($detail as $single)
            {
                $detail_array[] = array(
                    'name' => (string)$single->name,
                    'value' => (string)$single->value,
                );
            }
            $details[] = $detail_array; 
        }
        foreach ($prods_info as $prod_info)
        {
            if ($prod_info['sku'] === (string)$product->sku)
            {   
                $warehouse_stock = $prod_info['warehouse_stock'][0];
                $supplier_stock = $prod_info['supplier_stock'][0];
                $stockflg = 1;
                break;
            }
        }
        if($stockflg == 0)
        {
            $warehouse_stock = -666;
        }
        $prodotto = array(
            'name' => (string)$product->name,
            'brand' => (string)$product->brand,
            'category' => (string)$product->category,
            'description' => (string)$product->description,
            'desctiption' => (string)$product->desctiption,
            'sku' => (string)$product->sku,
            'price' => (string)$product->price,
            'images' => $images,
            'details' => $details,
            'warehouse_stock' => $warehouse_stock,
            'supplier_stock' => $supplier_stock,
        );
        $prodotti[] = $prodotto;    
    }

    error_log("ciclo " . $cicli_total . " after_arraybuild " . (time() - $start_time) . " diff " . (time() - $past));
    $past = time();
    $i = 0;
    while( time() - $start_time <= 95 )
    {
        error_log("ciclo " . $cicli_total . " incycle " . (time() - $start_time) . " diff " . (time() - $past));
        $past = time();
		$new_counter = $counter + 1;
		$wpdb->query("UPDATE $update_table SET counter = $new_counter");
        $import_total = $wpdb->get_var("SELECT import_total FROM $update_table");
        $new_import_total = $import_total + 1;
        $wpdb->query("UPDATE $update_table SET import_total = $new_import_total");
        modula_immagine($prodotti[$counter]["images"], $prodotti[$counter]["sku"], $jpeg_tmp, $bande_bianche_tmp, $WEBP_directory, $prodotti[$counter], $wpdb, $setup);
		$counter = $wpdb->get_var("SELECT counter FROM $update_table");
        $i++;
    }

    destroy_tmps($jpeg_tmp);
    destroy_tmps($bande_bianche_tmp);
    destroy_tmps($WEBP_directory);
    $new_cicli_completed = $cicli_completed + 1;
    $wpdb->query("UPDATE $update_table SET cicli_completed = $new_cicli_completed");
    error_log("ciclo " . $cicli_total . " end " . (time() - $start_time) . " diff " . (time() - $past));

}


?>