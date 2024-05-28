<?php
function clear_website_cache() {
    if (function_exists('wp_cache_clear_cache')) {
        wp_cache_clear_cache();
    }

    if (function_exists('wp_fastest_cache_clear')) {
        wp_fastest_cache_clear();
    }
    echo 'Cache svuotata correttamente.';
}

function clear_image_cache() {
    if (function_exists('wpsupercache_flush')) {
        wpsupercache_flush();
    }

    if (function_exists('w3tc_flush_all')) {
        w3tc_flush_all();
    }
    echo 'Cache delle immagini svuotata correttamente.';
}

function delete_image_thumbnails() {
    global $wpdb;

    $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = '_wp_attachment_metadata'");

    echo 'Miniature delle immagini eliminate correttamente.';
}

function flush_output_buffer() {
    ob_end_clean();
}

function remove_from_trash()
{
    $args = array(
        'post_type'   => 'product', 
        'post_status' => 'trash',    
        'posts_per_page' => -1,      
    );
    
    $products_in_trash = get_posts($args);
    
    foreach ($products_in_trash as $product) {
        $product_id = $product->ID;
        wp_delete_post($product_id, true);
    }
    
}

function get_total()
{

    global $wpdb;

    $update_table = $wpdb->prefix . '_update_info';
    $table_name = $wpdb->prefix . '_imporTinaMainTab';
    $file_path = $wpdb->get_var("SELECT xml FROM $table_name");
    $file_content = file_get_contents($file_path);
    $xml = simplexml_load_string($file_content);
    $count = 0;
    foreach ($xml->product as $product)
    {
        $count++;
    }
    $wpdb->query("UPDATE $update_table SET total = $count");
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

function delete_all()
{

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
    );
    
    $products = new WP_Query($args);
    
    // Elimina tutti i prodotti
    if ($products->have_posts()) {
        while ($products->have_posts()) {
            $products->the_post();
            $product_id = get_the_ID();
            wp_delete_post($product_id, true); // Elimina il prodotto definitivamente
        }
    }
    
    // Ottieni tutte le immagini nella media gallery
    $media_query_args = array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => -1,
    );
    
    $media_query = new WP_Query($media_query_args);
    
    // Elimina tutte le immagini
    if ($media_query->have_posts()) {
        while ($media_query->have_posts()) {
            $media_query->the_post();
            $media_id = get_the_ID();
            wp_delete_attachment($media_id, true); // Elimina l'immagine definitivamente
        }
    }
    
}

function count_draft_products() {
    // Parametri per la query
    $args = array(
        'status' => 'draft', // Filtra solo i prodotti in bozza
        'limit' => -1, // Ottieni tutti i prodotti in bozza
    );

    // Ottieni i prodotti in bozza
    $products = wc_get_products($args);

    // Conta il numero di prodotti in bozza
    $count = count($products);

    return $count;
}

function deserialize_array($array) {
    foreach ($array as $element) {
        if (is_array($element)) {
            $element = deserialize_array($element);
        } elseif (is_string($element) && strpos($element, '<?xml') !== false) {
            // Se l'elemento è una stringa XML, convertilo in un oggetto SimpleXMLElement
            $element = new SimpleXMLElement($element);
        } elseif (is_string($element) && preg_match('/^O:\d+:"\w+":\d+:{/', $element)) {
            // Se l'elemento è una stringa serializzata, deserializzalo
            $element = unserialize($element);
        }
    }
    return $array;
}

// Funzione per serializzare l'array e convertire gli oggetti SimpleXMLElement
function serialize_array($array) {
    foreach ($array as $element) {
        if (is_array($element)) {
            $element = serialize_array($element);
        } elseif ($element instanceof SimpleXMLElement) {
            // Converte l'oggetto SimpleXMLElement in una stringa XML
            $element = $element->asXML();
        } elseif (is_object($element)) {
            // Se l'elemento è un oggetto ma non un SimpleXMLElement, non può essere serializzato direttamente
            // Lo converto in una stringa rappresentativa e lo metto in un array
            $element = (string)$element;
        }
    }
    return $array;
}


?>