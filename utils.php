<?php
function clear_website_cache() {
    if (function_exists('wp_cache_clear_cache')) {
        wp_cache_clear_cache();
    }

    if (function_exists('wp_fastest_cache_clear')) {
        wp_fastest_cache_clear();
    }

    // Puoi aggiungere qui altri metodi di pulizia della cache, se necessario

    // Invia un messaggio di conferma
    echo 'Cache svuotata correttamente.';
}

function clear_image_cache() {
    if (function_exists('wpsupercache_flush')) {
        wpsupercache_flush();
    }

    if (function_exists('w3tc_flush_all')) {
        w3tc_flush_all();
    }

    // Puoi aggiungere qui altri metodi di pulizia della cache delle immagini, se necessario

    // Invia un messaggio di conferma
    echo 'Cache delle immagini svuotata correttamente.';
}

function delete_image_thumbnails() {
    global $wpdb;

    $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = '_wp_attachment_metadata'");

    // Invia un messaggio di conferma
    echo 'Miniature delle immagini eliminate correttamente.';
}

function flush_output_buffer() {
    ob_end_clean();
}

function remove_from_trash()
{
    $args = array(
        'post_type'   => 'product',  // Specifica il tipo di post come 'product'
        'post_status' => 'trash',    // Filtra i post che sono nel cestino
        'posts_per_page' => -1,      // Ottiene tutti i post nel cestino
    );
    
    // Ottiene i post che corrispondono ai criteri di ricerca definiti in $args
    $products_in_trash = get_posts($args);
    
    // Itera sui risultati per ottenere gli ID dei prodotti nel cestino
    foreach ($products_in_trash as $product) {
        $product_id = $product->ID;
        wp_delete_post($product_id, true);
    }
    
}

?>