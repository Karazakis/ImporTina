<?php

function print_cat_selector($id)
{
    echo '<select name="'.$id.'">';
    $woocommerce_categories = get_terms(array(
        'taxonomy' => 'product_cat', // la tassonomia delle categorie di WooCommerce
        'hide_empty' => false, // includi le categorie senza prodotti
    ));
    
    // Verifica se ci sono categorie
    if (!empty($woocommerce_categories)) {
        foreach ($woocommerce_categories as $category) {
            echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
        }
    } else {
        echo '<option value="">Nessuna categoria trovata</option>';
    }
    echo '</select>';
}

function setup_view()
{
    ?>
            <div class="popup-container" id="softclean" style="display: none;">
                <div class="popup-content">
                    <h2>Alert!</h2>
                    <p>Questa azione cancellerà la cache del sito e delle immagini. Vuoi procedere?</p>
                    <form method="post">
                        <button class="btn accept-btn" name="Sclean" onclick="accept()">Conferma</button>
                    </form>
                        <button class="btn cancel-btn" onclick="cancel()">Annulla</button>
                    <script src="script.js">showSecondPopup();</script>
                </div>
            </div>
            <div class="popup-container" id="hardclean" style="display:none;">
                <div class="popup-content">
                <h2>Alert!</h2>
                <p>Questa azione cancellerà la cache del sito e delle immagini, thumbnails e l'output buffer (verrai disconesso). Vuoi procedere?</p>
                <form method="post">
                    <button class="btn accept-btn" name="Hclean" onclick="accept()">Conferma</button>
                </form>
                    <button class="btn cancel-btn" onclick="cancel()">Annulla</button>
                <script src="script.js">showSecondPopup();</script>
                </div>
            </div>
            <form method="post">
            <button class="btn btn-dark btn-lg" type="submit" name="setup">Setup Wizard</button>
            </form>
            <br>
            <br>
            <button class ="btn btn-dark btn-lg" type="submit" onclick="showSoftClean()">SoftClean</button>
            <br>
            <br>
            <button class ="btn btn-dark btn-lg" type="submit" onclick="showHardClean()">HardClean</button>
            <br>
            <br>

        
    <?php
}

function firststep_view()
{
    ?>
        <form method="post">
            <strong class="filestep_title">Inserisci file source (link .xml) </strong><br>
            <label class="filestep_title">Inventory file</label><br>
            <input type="text" name="xml_file"><br>
            <label class="filestep_title">Stock file</label><br>
            <input type="text" name="xml_file_stock"><br>
            <button class="btn btn-primary" type="submit" name="file_link">Salva e Continua</button>
        </form>
    <?php
}

function secondstep_view($brand_table, $wpdb)
{
        $brands = $wpdb->get_results("SELECT * FROM $brand_table");
        if ($brands)
        {
            echo '<form method="post">';
            echo '<label> Inserisci qui un valore da impostare per tutti i brand</label><br>';
            echo '<input type="text" name="default_all" placeholder="Inserisci un valore" /><br>';
            echo '<input class="btn btn-primary" type="submit" name="BrandSave" value="Salva e Continua"><br>';
            echo '<ul class="multi-col-list">'; 
            foreach ($brands as $brand) {
                // Stampa ogni brand come un elemento della lista
                $unique_string = '_' . $brand->id;
                if ($brand->name != 'none')
                {
                    echo '<li>';
                    echo '<span class="list_span">' . esc_html($brand->name) . '</span>';
                    echo '<input type="text" name="'. $unique_string .'" placeholder="Inserisci nuovo valore" value="'. $brand->percentage .'"/>';
                    echo '</li>';
                }
                // Aggiungi un form di input di testo accanto al brand
            }
            echo '</ul>'; // Termina la lista ordinata
            echo '</form>';
        } 
        else
        {
            echo 'Nessun brand trovato.';
        }
}

function thirdstep_view($categories_table, $wpdb)
{
    $categories = $wpdb->get_results("SELECT * FROM $categories_table");
    if ($categories)
    {
        echo '<form method="post">';
        echo '<ul class="multi-col-list">'; // Inizia la lista ordinata
        foreach ($categories as $category) {
            // Stampa ogni brand come un elemento della lista
            $unique_string = '_' . $category->id;
            if ($category->category_source != 'none')
            {
                echo '<li>';
                echo '<span class="list_span">' . esc_html($category->category_source). '</span>';
                // Aggiungi un form di input di testo accanto al brand
                print_cat_selector($unique_string);
                echo '</li>';
            }

        }
        echo '</ul>'; // Termina la lista ordinata
        echo '<input class="btn btn-primary" type="submit" name="CatSave" value="Salva e Continua">';
        echo '</form>';
    } 
    else
    {
        echo 'Nessuna categoria trovata.';
    }
}

function fourthstep_view()
{
    ?>
        <form method="post">
            <label> metti proporzioni immagine </label><br>
            <input type="text" name="altezz">
            <input type="text" name="larghezz"><br>
            <button class="btn btn-primary" type="submit" name="immagin">Salva e Continua</button>
        </form>
    <?php
}

function import_view()
{
    ?>
        <form method="post">
                <button class="btn btn-success btn-lg"type="submit" name="import">START IMPORT</button>
        </form>
    <?php
}


/* function loading_view()
{
    ?>

} */
?>
