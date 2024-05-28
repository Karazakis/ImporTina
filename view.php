<?php

function print_cat_selector($id, $cat_name)
{
    echo '<select class="cat_selector" name="'.$id.'">';
    $woocommerce_categories = get_terms(array(
        'taxonomy' => 'product_cat', 
        'hide_empty' => false, 
    ));
    
    if (!empty($woocommerce_categories)) {
        foreach ($woocommerce_categories as $category) {
            if ($cat_name === $category->name)
            {
                echo '<option value="' . esc_attr($category->term_id) . '" selected>' . esc_html($category->name) . '</option>';
            }
            else
            {
                echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
            }  
        }
    } else {
        echo '<option value="">Nessuna categoria trovata</option>';
    }
    echo '</select>';
}

function setup_view()
{
    ?>      
            <div class="popup-container" id="deleteall" style="display: none;">
                <div class ="popup-content">
                    <h2>ALERT!</h2>
                    <p> QUESTA AZIONE ELIMINERA' TUTTI I PRODOTTI E TUTTE LE IMMAGINI ASSOCIATE </p>
                    <form method="post">
                        <button class="btn accept-btn" name="delete" onclick="accept()">Conferma</button>
                    </form>
                        <button class="btn cancel-btn" onclick="cancel()">Annulla</button>
                    <script src="script.js">showSecondPopup();</script> 
                </div>
            </div>
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
            <button class ="btn btn-dark btn-lg" type="submit" onclick="showDelete()">DELETE</button>

        
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
            <button class="btn btn-primary button_margin" type="submit" name="file_link">Salva e Continua</button>
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
            echo '<input class="btn btn-primary button_margin" type="submit" name="BrandSave" value="Salva e Continua"><br>';
            echo '<ul class="multi-col-list">'; 
            foreach ($brands as $brand) {
                $unique_string = '_' . $brand->id;
                if ($brand->name != 'none')
                {
                    echo '<li>';
                    echo '<span class="list_span">' . esc_html($brand->name) . '</span>';
                    echo '<input type="text" name="'. $unique_string .'" placeholder="Inserisci nuovo valore" value="'. $brand->percentage .'"/>';
                    echo '</li>';
                }
            }
            echo '</ul>';
            echo '</form>';
        } 
        else
        {
            echo 'Nessun brand trovato.';
        }
}

function price_update($brand_table)
{
    global $wpdb;
    $brands = $wpdb->get_results("SELECT * FROM $brand_table");
    if ($brands)
    {
        echo '<form method="post">';
        echo '<label> Inserisci qui un valore da impostare per tutti i brand</label><br>';
        echo '<input type="text" name="default_all" placeholder="Inserisci un valore" /><br>';
        echo '<input class="btn btn-primary button_margin" type="submit" name="BrandUpdate" value="Salva"><br>';
        echo '<ul class="multi-col-list">'; 
        foreach ($brands as $brand) {
            $unique_string = '_' . $brand->id;
            if ($brand->name != 'none')
            {
                echo '<li>';
                echo '<span class="list_span">' . esc_html($brand->name) . '</span>';
                echo '<input type="text" name="'. $unique_string .'" placeholder="Inserisci nuovo valore" value="'. $brand->percentage .'"/>';
                echo '</li>';
            }
        }
        echo '</ul>';
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
                print_cat_selector($unique_string, "Uncategorized");
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


function update_cat($categories_table, $wpdb)   
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
                print_cat_selector($unique_string, $category->category_dest);
                echo '</li>';
            }

        }
        echo '</ul>'; // Termina la lista ordinata
        echo '<input class="btn btn-primary" type="submit" name="CatUpdate" value="Salva">';
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
            <button class="btn btn-primary button_margin" type="submit" name="immagin">Salva e Continua</button>
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


function loading_view()
{
    global $wpdb;

    $table_name = $wpdb->prefix . '_imporTinaMainTab';
    $categories_table = $wpdb->prefix . '_categories';
    $brand_table =  $wpdb->prefix . '_brands';
    $update_table = $wpdb->prefix . '_update_info';
    $totalItems = $wpdb->get_var("SELECT total FROM $update_table");
    $currentItemsLoaded =  $wpdb->get_var("SELECT current_uploaded FROM $update_table"); 
    $status = $wpdb->get_var("SELECT setup FROM $table_name");
    $xml = $wpdb->get_var("SELECT xml FROM $table_name");
    $stock = $wpdb->get_var("SELECT xml_stock FROM $table_name");
    $update_status = $wpdb->get_var("SELECT update_status FROM $update_table");
    $produts = count_draft_products();
    $brand_count = $wpdb->get_var("SELECT COUNT(*) FROM $brand_table WHERE percentage = '1'");
    $cat_count = $wpdb->get_var("SELECT COUNT(*) FROM $categories_table WHERE category_dest = 'Uncategorized'");
    $imported = $wpdb->get_var("SELECT current_uploaded FROM $update_table");
    $esauriti = $wpdb->get_var("SELECT esauriti FROM $update_table");
    $falliti =  $wpdb->get_var("SELECT falliti FROM $update_table");
    $totale =  $wpdb->get_var("SELECT import_total FROM $update_table");
    $cicli_total =  $wpdb->get_var("SELECT cicli_total FROM $update_table");
    $cicli_completed =  $wpdb->get_var("SELECT cicli_completed FROM $update_table");
    $counter =  $wpdb->get_var("SELECT counter FROM $update_table");
    ?>
    <div class="wrap">
    <h1>Impostazioni del Plugin</h1>

    <!-- Tabella delle schede -->
    <div class="tabs">
        <button class="tablinks active" onclick="openTab(event, 'status')">Stato</button>
        <button class="tablinks" onclick="openTab(event, 'prezzi')">Percentuale</button>
        <button class="tablinks" onclick="openTab(event, 'categorie')">Categorie</button>
        <button class="tablinks" onclick="openTab(event, 'source')">Source</button>
        <button class="tablinks" onclick="openTab(event, 'reset')">Reset</button>
    </div>

    <!-- Contenuto delle schede -->
    <div id="status" class="tabcontent">
        <div class="plugin_row content_row">
        <div class="plugin_content">
            <div>
                <h3> Importazione completata, il plugin è correttamente impostato </h3>
                <div>
                    <div>
                        status import <?php 
                            if($update_status)
                            {
                                echo "IN CORSO";
                            }
                            else
                            {
                                echo "COMPLETATO";
                            } 
                        ?>
                    </div>
                </div>
                <div>
                    <div>
                        prodotti non pubblicati <?php echo $produts; ?>
                    </div>
                </div>
                <div>
                    <div>
                        percentuali non impostate <?php echo $brand_count; ?>
                    </div>
                </div>
                <div>
                    <div>
                        categorie non assegnate <?php echo $cat_count; ?>
                    </div>
                </div>
                <div>
                    <div>
                        <h5>
                            RISULTATI IMPORTAZIONE
                        </h5>
                    </div>
                    <div>
                        <div>
                            Prodotti importati disponibili <?php echo $imported; ?>
                        </div>
                    </div>
                    <div>
                        <div>
                            Prodotti esauriti <?php echo $esauriti; ?>
                        </div>
                    </div>
                    <div>
                        <div>
                            Importazioni fallite (per immagini) <?php echo $falliti; ?>
                        </div>
                    </div>
                    <div>
                        <div>
                            Importazioni fallite (altro)<?php echo ($totale - ($esauriti + $imported + $falliti)); ?>
                        </div>
                    </div>
                    <div>
                        <div>
                            TOTALE IMPORTAZIONE <?php echo $totale; ?> [ <?php echo $counter; ?> ]( cicli commpletati <?php echo $cicli_completed; ?> su <?php echo $cicli_total; ?> )
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
    <div id="prezzi" class="tabcontent">
        <div class="plugin_row content_row">
        <div class="plugin_content">
            <?php
                price_update($brand_table);
            ?>
        </div>
        </div>
    </div>

    <div id="categorie" class="tabcontent" style="display: none;">
        <div class="plugin_row content_row">
        <div class="plugin_content">
            <?php
                update_cat($categories_table, $wpdb);
            ?>
        </div>
        </div>
    </div>

    <div id="source" class="tabcontent" style="display: none;">
        <div class="plugin_row content_row">
        <div class="plugin_content">
            <form method="post">
                <strong class="filestep_title">Inserisci file source (link .xml) </strong><br>
                <label class="filestep_title">Inventory file</label><br>
                <input type="text" name="xml_file" value="<?php echo esc_attr($xml); ?>" ><br>
                <label class="filestep_title">Stock file</label><br>
                <input type="text" name="xml_file_stock" value="<?php echo esc_attr($stock); ?>"><br><br>
                <button class="btn btn-primary button_margin" type="submit" name="file_link">Salva</button>
            </form>
        </div>
        </div>
    </div>

    <div id="reset" class="tabcontent" style="display: none;">
        <div class="plugin_row content_row">
          <div class="plugin_content">
              <button class="btn btn-danger btn-lg" type="submit" onclick="showbtn()">Reset Setup</button>
              <div class="popup-container" id="deleteall" style="display: none;">
                  <div class ="popup-content">
                      <h2>ALERT!</h2>
                      <p> QUESTA AZIONE ELIMINERA' TUTTI I PRODOTTI E TUTTE LE IMMAGINI ASSOCIATE </p>
                      <form method="post">
                          <button class="btn accept-btn" name="delete" onclick="accept()">Conferma</button>
                      </form>
                          <button class="btn cancel-btn" onclick="cancel()">Annulla</button>
                      <script src="script.js">showSecondPopup();</script> 
                  </div>
              </div>
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
              <br>
              <button class ="btn btn-dark btn-lg" type="submit" onclick="showSoftClean()">SoftClean</button>
              <br>
              <br>
              <button class ="btn btn-dark btn-lg" type="submit" onclick="showHardClean()">HardClean</button>
              <br>
              <br>
              <button class ="btn btn-dark btn-lg" type="submit" onclick="showDelete()">DELETE</button>
          </div>
        </div>
    </div>
</div>

<!-- Script per gestire le schede -->
<script>
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    }

    // Imposta la prima scheda attiva di default
    document.getElementById("status").style.display = "block";
</script>
<?php

} 
?>
