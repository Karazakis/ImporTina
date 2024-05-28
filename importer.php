<?php
require_once(ABSPATH . 'wp-load.php' );
require_once(plugin_dir_path(__FILE__) . 'imgmoduler.php');


function inserisci_prodotto($prodotto, $WEBP_directory, $curr_imgs, $setup)
{
    global $wpdb;
    $update_table = $wpdb->prefix . '_update_info';
    $existing_product_id = wc_get_product_id_by_sku($prodotto['sku']);
    $brand_table =  $wpdb->prefix . '_brands';
    $brand_perc = $wpdb->get_var("SELECT percentage FROM $brand_table WHERE name = '{$prodotto['brand']}'");
    
    if(!isset($prodotto['sku']) || empty($prodotto['sku']))
    {
        return;
    }
    if($existing_product_id)
    {
        $new_product = wc_get_product($existing_product_id);
    }
    else
    {
        $new_product = new WC_Product();
    }
    global $wpdb;

   
    $image_set = false;
    $category_table = $wpdb->prefix . '_categories';
    $update_table = $wpdb->prefix . '_update_info';
    if(!$existing_product_id)
    {
        $new_product->set_name($prodotto['name']);
        $new_product->set_sku($prodotto['sku']); 
        insert_attributes($prodotto, $new_product);
        if (!empty($prodotto['description']))
            $new_product->set_description($prodotto['description']);
        elseif (!empty($prodotto['desctiption']))
            $new_product->set_description($prodotto['desctiption']);
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
                    error_log("Impossibile caricare l'immagine del prodotto.");
                }
            }
            $gallery_image_ids = $new_product->get_gallery_image_ids() ?: array();
            $merged_gallery_image_ids = array_merge($gallery_image_ids, $attachment_ids);
            $new_product->set_gallery_image_ids($merged_gallery_image_ids); 
        }
    }
    $new_product->set_regular_price($prodotto['price'] * $brand_perc); 



    $new_product_id = $new_product->save();
    $escaped_category = $wpdb->prepare('%s', (string)$prodotto['category']);
    $sql_query = "SELECT category_dest FROM $category_table WHERE category_source = " . $escaped_category;
    $prod_cat_dest = $wpdb->get_var($sql_query);
    $prod_categories = wp_get_object_terms( $new_product_id, 'product_cat', array( 'fields' => 'all' ) );

    // Rimuovi tutte le categorie assegnate al prodotto
    if ( !empty( $prod_categories ) ) {
        foreach ( $prod_categories as $prod_category ) {
            wp_remove_object_terms( $new_product_id, $prod_category->term_id, 'product_cat' );
        }
    }

    wp_set_object_terms($new_product_id, $prod_cat_dest, 'product_cat'); 
    $tot = $wpdb->get_var("SELECT current_uploaded FROM $update_table");
    $tot++;
    $wpdb->query("UPDATE $update_table SET current_uploaded = $tot");
    if($prodotto['warehouse_stock'] == 0)
    {
        update_post_meta($new_product_id, '_stock_status', 'outofstock');
        error_log("tra gli esauriti castato ". (int)$prodotto['warehouse_stock'] . " e non " . $prodotto['warehouse_stock']);
        $esauriti = $wpdb->get_var("SELECT esauriti FROM $update_table");
        $new_esauriti = $esauriti + 1;
        $wpdb->query("UPDATE $update_table SET esauriti = $new_esauriti");
    }
    else
    {
        update_post_meta($new_product_id, '_stock_status', 'instock');
    }
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