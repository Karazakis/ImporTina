<?php 
function plugin_controller($posted, $OnError, $isParsing)
{
    global $wpdb;
    $table_name = $wpdb->prefix . '_imporTinaMainTab';
    $brand_table =  $wpdb->prefix . '_brands';
    $categories_table = $wpdb->prefix . '_categories';
    $info_table = $wpdb->prefix . '_inventory_info';
    if (isset($posted['reset'])){
        $wpdb->query("UPDATE $table_name SET first = 0");
        $wpdb->query("UPDATE $table_name SET second = 0");
        $wpdb->query("UPDATE $table_name SET third = 0");
        $wpdb->query("UPDATE $table_name SET fourth = 0");
        $wpdb->query("UPDATE $table_name SET fifth = 0");
        $wpdb->query("UPDATE $table_name SET setup = 0");
        $wpdb->query("UPDATE $table_name SET xml = 'null'");
        $wpdb->query("UPDATE $table_name SET xml_stock = 'null'");
        $wpdb->query("DELETE FROM $brand_table");
        $wpdb->query("DELETE FROM $categories_table");
        $wpdb->query("DELETE FROM $info_table");
        wc_delete_product_transients();

        echo '<script>window.location.href = window.location.href;</script>';
        exit();
    }

    if (isset($posted['BrandSave']))
    {
        $brands = $wpdb->get_results("SELECT * FROM $brand_table");
        if (!isset($posted['default_all']) || empty($posted['default_all'])) {
            foreach ($brands as $brand)
            {
                $slug = '_' . $brand->id;
                $new_percentage = $posted[$slug];
                // Esegui la query di aggiornamento solo se la percentuale è stata fornita
                if (!empty($new_percentage)) {
                    // Esegui la query di aggiornamento
                    $wpdb->update(
                        $brand_table,
                        array('percentage' => $new_percentage),
                        array('name' => $brand->name),
                        array('%s'), // Formato per la percentuale (stringa)
                        array('%s')  // Formato per il nome del brand
                    );
                }
            }
        }
        else
        {
            $default_all = sanitize_text_field($posted['default_all']);
            $wpdb->query("UPDATE $brand_table SET percentage = $default_all");
        }
        $wpdb->query("UPDATE $table_name SET third = 1");
        echo '<script>window.location.href = window.location.href;</script>';
        exit(); 

    }
    if (isset($posted['CatSave']))
    {
        $categories_table = $wpdb->prefix . '_categories';

        $categories = $wpdb->get_results("SELECT * FROM $categories_table");
        foreach ($categories as $categorie)
        {   
            if ($categorie->id !== '1')
            {
                    $slug =  '_' .  $categorie->id;
                
                $new_cat = $posted[$slug];
                $selected_category = get_term_by('id', $new_cat, 'product_cat');
                // Esegui la query di aggiornamento solo se la percentuale è stata fornita
                if (!empty($selected_category)) {
                    // Esegui la query di aggiornamento
                    $wpdb->update(
                        $categories_table,
                        array('category_dest' => $selected_category->name),
                        array('category_source' => $categorie->category_source),
                        array('%s'), // Formato per la percentuale (stringa)
                        array('%s')  // Formato per il nome del brand
                    );
                }
            }
        }
        $wpdb->query("UPDATE $table_name SET fourth = 1");
        echo '<script>window.location.href = window.location.href;</script>';
        exit(); 
    }
    if (isset($posted['immagin']))
    {   
        $info_table = $wpdb->prefix . '_inventory_info';
        if (isset($posted['larghezz']) && isset($posted['altezz']))
        {   
            $id = $wpdb->get_var("SELECT id FROM $info_table");
            if ($id)
            {
                // Se l'ID è valido, procedi con l'aggiornamento
                $height = $posted['altezz'];
                $width = $posted['larghezz'];
                $wpdb->update(
                    $info_table,
                    array(
                        'img_width' => $width,
                        'img_height' => $height,
                    ),
                    array(
                        'id' => $id,
                    ),
                    array(
                        '%s', // Formato per 'img_width'
                        '%s' // Formato per 'img_height'
                    )
                );
            }
            else
            {
                $height = $posted['altezz'];
                $width = $posted['larghezz'];
                $wpdb->insert(
                    $info_table,
                    array(
                        'img_width' => $width,
                        'img_height' => $height,
                    ),
                    array(
                        '%s', // Formato per 'img_width'
                        '%s' // Formato per 'img_height'
                    )
                );
            }
            $wpdb->query("UPDATE $table_name SET fifth = 1");
            echo '<script>window.location.href = window.location.href;</script>';
            exit(); 
        }
        else
        {
            $OnError = true;
            echo "Error.missing size info";
        }
        
    }
    if (isset($posted['setup'])) {
        $wpdb->query("UPDATE $table_name SET first = 1");
        // Messaggio di conferma
        if ($wpdb->last_error) {
            echo $wpdb->last_error;
        } else {
            if (!$OnError && !$isParsing)
            {
                echo '<script>window.location.href = window.location.href;</script>';
                exit(); 
            }
        }
    }
    if (isset($posted['Sclean']))
    {
        clear_website_cache();
        clear_image_cache();    
    }
    if (isset($posted['Hclean']))
    { 
        clear_website_cache();
        clear_image_cache();
        delete_image_thumbnails();
        flush_output_buffer();
    }
    if (isset($posted['Trash']))
    { 
        remove_from_trash();
    }
    if (isset($posted['file_link'])) {
        $flag = true;
        $submitted_xml = $posted['xml_file'];
        if (substr($submitted_xml, -4) === ".xml") {
            $wpdb->query("UPDATE $table_name SET xml = '$submitted_xml'");
            leggiFileXML($submitted_xml);
        } else {
            $OnError = true;
            echo "<span>Error.Invalid inventory format.</span><br>";
        }
        $submitted_xml = $posted['xml_file_stock'];
        if (substr($submitted_xml, -4) === ".xml") {
            $wpdb->query("UPDATE $table_name SET xml_stock = '$submitted_xml'");
        } else {
            $OnError = true;
            echo "<span>Error.Invalid stock format.</span>";
        }
        // Messaggio di conferma
        if ($wpdb->last_error) {
            echo $wpdb->last_error;
        } else {
            if (!$OnError && !$isParsing && $flag)
            {
                $wpdb->query("UPDATE $table_name SET second = 1");
                echo '<script>window.location.href = window.location.href;</script>';
                exit();  
            }
        }
    }
    if (isset($posted['import'])) 
    {
        exec('php ' . plugin_dir_path(__FILE__) . 'import.php > /dev/null 2>&1 &');
/*         $submitted_xml = $wpdb->get_var("SELECT xml_stock FROM $table_name");
        $prod_info = array();
        $prod_info = leggiFileSTOCKXML($submitted_xml);
        file_import($table_name, $wpdb, $prod_info); */
        $wpdb->query("UPDATE $table_name SET setup = 1");
        echo '<script>window.location.href = window.location.href;</script>';
        exit();  
    }    
}

function leggiFileXML($file_path) {
    // Verifica se il file esiste

    global $wpdb;
    $brands = array();
    $categorie = array();

    $file_content = file_get_contents($file_path);
    if (!$file_content) {
        return false;
    }


    // Analizza il contenuto XML
    $xml = simplexml_load_string($file_content);

    // Inizializza un array per contenere i prodotti
    $prodotti = array();
    $file_path = '../wp-content/plugins/asd1/log.txt';
    // Itera sui prodotti nel file XML

    foreach ($xml->product as $product) {
/*         $prodotto = array(
            'name' => (string)$product->name,
            'brand' => (string)$product->brand,
            'cathegory' => (string)$product->cathegory,
            'description' => (string)$product->description,
            'sku' => (string)$product->sku,
            'price' => (string)$product->price,
        ); */
        if (!in_array_custom((string)$product->brand, $brands))
        {
            $brands[] = $product->brand; 
        }

        if (!in_array_custom((string)$product->category, $categorie))
        {
            $categorie[] = $product->category;
        }
    }
    $brand_table =  $wpdb->prefix . '_brands';
    $categories_table = $wpdb->prefix . '_categories';

    $unique_brand = array();
    $unique_brand = array_unique($brands);
    foreach ($unique_brand as $brand)
    {
        $wpdb->insert(
            $brand_table,
            array(
                'name' => (string)$brand,
                'percentage' => '1' // Valore predefinito per la percentuale
            ),
            array('%s', '%s')
        );

    }
    $unique_category = array();
    $unique_category = array_unique($categorie);
    foreach ($unique_category as $category)
    {
        $wpdb->insert(
            $categories_table,
            array(
                'category_source' => (string)$category,
                'category_dest' => 'none' // Valore predefinito per la percentuale
            ),
            array('%s', '%s')
        );

    }

    $isParsing = true;
    return $prodotti;
}

function in_array_custom($needle, $haystack) {
    foreach ($haystack as $item) {
        if ($item === $needle) {
            return true;
        }
    }
    return false;
}

function leggiFileSTOCKXML($file_path)
{   
    global $wpdb;
    $file_content = file_get_contents($file_path);
    if (!$file_content) {
        return false;
    }
    $xml = simplexml_load_string($file_content);
    $products_info = array();
    foreach ($xml->stock as $stock)
    {      

        $singleprod_info = array(
            'sku' => (string)$stock->sku,
            'ean' => (string)$stock->ean,
            'warehouse_stock' => $stock->warehouse_stock,
            'supplier_stock' => $stock->supplier_stock
        );
        $products_info[] = $singleprod_info;
    }
    
    return ($products_info);
}
?>
