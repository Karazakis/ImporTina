<?php

function custom_plugin_activation() {
    global $wpdb;
    
    // Nome della tabella nel database (aggiungi il prefisso del database se necessario)
    $table_name = $wpdb->prefix . '_imporTinaMainTab';
    $brand_table =  $wpdb->prefix . '_brands';
    $categories_table = $wpdb->prefix . '_categories';
    $info_table = $wpdb->prefix . '_inventory_info';
    $update_table = $wpdb->prefix . '_update_info';
    // TABELLA FLAGS SETUP INIZIALE
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        first TINYINT(1) NOT NULL DEFAULT 0,
        second TINYINT(1) NOT NULL DEFAULT 0,
        third TINYINT(1) NOT NULL DEFAULT 0,
        fourth TINYINT(1) NOT NULL DEFAULT 0,
        fifth TINYINT (1) NOT NULL DEFAULT 0,
        import_start TINYINT (1) NOT NULL DEFAULT 0,
        setup TINYINT (1) NOT NULL DEFAULT 0,
        xml VARCHAR(128) NOT NULL DEFAULT 'null',
        xml_stock VARCHAR(128) NOT NULL DEFAULT 'null',
        success TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB";
    // TABELLA BRANDS
    $sql2 = "CREATE TABLE IF NOT EXISTS $brand_table (
        id INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(48) NOT NULL DEFAULT 'none',
        percentage VARCHAR(48) NOT NULL DEFAULT 'none',
        PRIMARY KEY (id)
    ) ENGINE=InnoDB";
    $sql3 = "CREATE TABLE IF NOT EXISTS $categories_table (
        id INT NOT NULL AUTO_INCREMENT,
        category_source VARCHAR(48) NOT NULL DEFAULT 'none',
        category_dest VARCHAR(48) NOT NULL DEFAULT 'none',
        PRIMARY KEY (id)
    ) ENGINE=InnoDB";
    $sql4 = "CREATE TABLE IF NOT EXISTS $info_table (
        id INT NOT NULL AUTO_INCREMENT,
        img_width VARCHAR(48) NOT NULL DEFAULT 'none',
        img_height VARCHAR(48) NOT NULL DEFAULT 'none',
        PRIMARY KEY (id)
    ) ENGINE=InnoDB";
    $sql5 = "CREATE TABLE IF NOT EXISTS $update_table (
        id INT NOT NULL AUTO_INCREMENT,
        update_status TINYINT(1) NOT NULL DEFAULT 0,
        counter INT NOT NULL DEFAULT 0,
        total INT NOT NULL DEFAULT 0,
        current_uploaded INT NOT NULL DEFAULT 0,
        esauriti INT NOT NULL DEFAULT 0,
        falliti INT NOT NULL DEFAULT 0,
        import_total INT NOT NULL DEFAULT 0,
        cicli_total INT NOT NULL DEFAULT 0,
        cicli_completed INT NOT NULL DEFAULT 0,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB";
    // Esegui la query per creare la tabella
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $wpdb->query($sql2);
    $wpdb->query($sql);
    $wpdb->query($sql3);
    $wpdb->query($sql4);
    $wpdb->query($sql5);
    // Inserisci la voce predefinita nella tabella
    $wpdb->insert(
        $table_name,
        array(
            'first' => 0,
            'second' => 0,
            'third' => 0,
            'fourth' => 0,
            'fifth' => 0,
            'import_start' => 0,
            'setup' => 0,
            'xml' => 'null',
            'xml_stock' => 'null',
        )
    );

    $wpdb->insert(
        $brand_table,
        array(
            'name' => 'none',
            'percentage' => 'none',
        )
    );

    $wpdb->insert(
        $categories_table,
        array(
            'category_source' => 'none',
            'category_dest' => 'none',
        )
    );

    $wpdb->insert(
        $info_table,
        array(
            'img_width' => 'none',
            'img_height' => 'none',
        )
    );
    $wpdb->insert(
        $update_table,
        array(
            'update_status' => 0,
            'counter' => 0,
            'total' => 0,
            'current_uploaded' => 0,
            'esauriti' => 0,
            'falliti' => 0,
            'import_total' => 0,
            'cicli_total' => 0,
            'cicli_completed' => 0,
        )
    );
}

function custom_plugin_menu() {
    // URL del file di stile
     // -- > ICONA CUSTOM MENU ADMIN $icona = plugin_dir_url( __FILE__ ) . 'icon/IconTina1.svg';
    $plugin_url = plugin_dir_url(__FILE__);

    wp_enqueue_style('custom-plugin-style', $plugin_url . 'style.css');

    wp_enqueue_script('custom-plugin-script', $plugin_url . 'script.js', array(), '1.0', true);
    // Parametri per la query
    $args = array(
        'status' => 'draft', // Filtra solo i prodotti in bozza
        'limit' => -1, // Ottieni tutti i prodotti in bozza
    );

    // Ottieni i prodotti in bozza
    $products = wc_get_products($args);

    // Conta il numero di prodotti in bozza
    $count = count($products);
    if ($count == 0)
    {
        add_menu_page(
            'ZetaStudioPlugin(per Tina)',   // Titolo della pagina
            'ImporTina',                    // Etichetta del menu
            'manage_options',               // Capacità richiesta per visualizzare il menu
            'importa-prodotti',             // ID univoco del menu
            'custom_plugin_page' ,          // Funzione che mostra il contenuto della pagina
            'dashicons-edit-page',          // Icona del menu (opzionale)
            75                              // Posizione del menu nel menu principale (opzionale)
        );
    }
    else
    {
        add_menu_page(
            'ZetaStudioPlugin(per Tina)',   // Titolo della pagina
            'ImporTina <span class="update-plugins count-' . $count . '"><span class="plugin-count">' . $count . '</span></span>',  // Etichetta del menu
            'manage_options',               // Capacità richiesta per visualizzare il menu
            'importa-prodotti',             // ID univoco del menu
            'custom_plugin_page' ,          // Funzione che mostra il contenuto della pagina
            'dashicons-edit-page',          // Icona del menu (opzionale)
            75                              // Posizione del menu nel menu principale (opzionale)
        );
    }
}

function custom_plugin_uninstall() {
    global $wpdb;

    $table_name = $wpdb->prefix . '_imporTinaMainTab';
    $brand_table =  $wpdb->prefix . '_brands';
    $categories_table = $wpdb->prefix . '_categories';
    $inventory_info = $wpdb->prefix . '_inventory_info';
    $update_table = $wpdb->prefix . '_update_info';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $wpdb->query("DROP TABLE IF EXISTS $brand_table");
    $wpdb->query("DROP TABLE IF EXISTS $categories_table");
    $wpdb->query("DROP TABLE IF EXISTS $inventory_info");
    $wpdb->query("DROP TABLE IF EXISTS $update_table");

    delete_option('nome_opzione_plugin');

    $plugins = get_option('active_plugins');
    $plugin_to_remove = 'ImporTina/index.php';
    if (($key = array_search($plugin_to_remove, $plugins)) !== false) {
        unset($plugins[$key]);
        update_option('active_plugins', $plugins);
    }
}

?>
