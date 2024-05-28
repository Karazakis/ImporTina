<?php

require_once(ABSPATH . 'wp-load.php' );

function file_import($table_name, $wpdb, $prods_info)
{
    $file_path = $wpdb->get_var("SELECT xml FROM $table_name");
    $file_content = file_get_contents($file_path);
    $xml = simplexml_load_string($file_content);
    $prodotti = array();
    $det_coppia = array();
    $warehouse_stock = "";
    $supplier_stock = "";
    foreach ($xml->product as $product) {
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
                break;
            }
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
        $brands[] = $product->brand;
    }
	woocommerce_import($prodotti, $wpdb, $brands);
}

function woocommerce_import($prodotti, $wpdb, $brands)
{
    $jpeg_tmp = plugin_dir_path(__FILE__)."_immagini_tmp/";
    $bande_bianche_tmp = plugin_dir_path(__FILE__)."_immagini_bande_bianche_tmp/";
    $WEBP_directory = plugin_dir_path(__FILE__)."_WEBP_IMAGES/";
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
    $i = 0; 
    foreach($prodotti as $prodotto)
    {   
        modula_immagine($prodotto["images"], $prodotto["sku"], $jpeg_tmp, $bande_bianche_tmp, $WEBP_directory, $prodotto, $wpdb);
        if($i === 20)
        {
            usleep(500000);
            $i = 0;
        }
        $i++;
    } 
    destroy_tmps($jpeg_tmp);
    destroy_tmps($bande_bianche_tmp);
    destroy_tmps($WEBP_directory);
}

function destroy_tmps($dir)
{
        if (!is_dir($dir)) {
            return false;
        }
    
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
    
        return rmdir($dir);
}

function inserisci_prodotto($prodotto, $WEBP_directory, $curr_imgs)
{
    $existing_product_id = wc_get_product_id_by_sku($prodotto['sku']);

    if ($existing_product_id) {
        echo "Il prodotto è già stato inserito con ID: " . $existing_product_id;
        return;
    }
    if(!isset($prodotto['sku']) || empty($prodotto['sku']))
    {
       // echo "Sku del prodotto " . $existing_product_id . "assente.";
        return;
    }
    global $wpdb;

    $new_product = new WC_Product();
    $image_set = false;
    $category_table = $wpdb->prefix . '_categories';

    $new_product->set_name($prodotto['name']);
    if (!empty($prodotto['description']))
        $new_product->set_description($prodotto['description']);
    elseif (!empty($prodotto['desctiption']))
        $new_product->set_description($prodotto['desctiption']);
    $new_product->set_regular_price($prodotto['price']); // Prezzo del prodotto
    $new_product->set_sku($prodotto['sku']); // Codice SKU del prodotto
    $new_product->set_status('publish'); // Stato del prodotto
    insert_attributes($prodotto, $new_product);
    if (!empty($curr_imgs)) {
    $attachment_ids = array(); 
    foreach ($curr_imgs as $curr_img) {
        $image_id = upload_image_to_media_library($curr_img, $WEBP_directory, $prodotto);
        if (!$image_set)
        {
            $new_product->set_image_id($image_id);
            $image_set = true;
        }
        if ($image_id) {
            $attachment_ids[] = $image_id;
        } else {
            echo "Impossibile caricare l'immagine del prodotto.";
        }
    }
    $gallery_image_ids = $new_product->get_gallery_image_ids() ?: array();
    $merged_gallery_image_ids = array_merge($gallery_image_ids, $attachment_ids);
    $new_product->set_gallery_image_ids($merged_gallery_image_ids);
}

$new_product_id = $new_product->save();
if ($prodotto['warehouse_stock'] > 0)
{
    update_post_meta($new_product_id, '_stock_status', 'instock');
}
else
{
    update_post_meta($new_product_id, '_stock_status', 'outofstock');
}


$prod_cat_dest = $wpdb->get_results($wpdb->prepare("SELECT category_dest FROM $category_table WHERE category_source = %s", $prodotto['category']));
$true_cat = "";
if (!empty($prod_cat_dest[0]->category_dest))
{
    $true_cat = $prod_cat_dest[0]->category_dest;
}

if (!empty($true_cat))
{
    wp_remove_object_terms( $new_product_id, 'uncategorized', 'product_cat' );
}


wp_set_object_terms($new_product_id, $true_cat, 'product_cat', true);
$tags = array('Tag1', 'Tag2');
foreach ($tags as $tag) {
    wp_set_object_terms($new_product_id, $tag, 'product_tag', true);
}
//echo "Prodotto inserito con successo con ID: " . $new_product_id;
}

function insert_attributes($prodotto, $new_product)
{   
    $attributes = array();
    // AGGIUNGI BRAND ATTRIBUTO
    $brand_attribute = new WC_Product_Attribute();

    $brand_attribute->set_id(0);
    $brand_attribute->set_name('brand');
    $brand_attribute->set_options(array($prodotto['brand'])); 
    $brand_attribute->set_position(1);
    $brand_attribute->set_visible(true);
    $brand_attribute->set_variation(false);
    $attributes[] = $brand_attribute;

    //AGGIUNGI DETTAGLI ATTRIBUTE
    foreach ($prodotto['details'] as $detail_array) {
        foreach ($detail_array as $detail)
        {
            $details_attribute = new WC_Product_Attribute();
            $details_attribute->set_name($detail['name']); 
            $details_attribute->set_options(array($detail['value'])); 
            $details_attribute->set_id(0);
            $details_attribute->set_position(1);
            $details_attribute->set_visible(true);
            $details_attribute->set_variation(false);
            $attributes[] = $details_attribute;
        }
        $new_product->set_attributes($attributes);
    }
}
?>