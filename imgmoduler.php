<?php
function modula_immagine($images, $sku, $dir_path, $new_dir_path, $webp_dir, $prodotto, $wpdb, $setup)
 {   
    dl('imagick.so');
    $info_table = $wpdb->prefix . '_inventory_info';
    $altezzaSet = $wpdb->get_var("SELECT img_height FROM $info_table");
    $larghezzaSet = $wpdb->get_var("SELECT img_width FROM $info_table");
    $update_table = $wpdb->prefix . '_update_info';
    $converted_images = array();
    $i = 0;
    $existing_product_id = wc_get_product_id_by_sku($prodotto['sku']);
    if ((int)$prodotto['warehouse_stock'] < 0)
    {
        return;
    }

   if (!$existing_product_id)
    {
        foreach ($images as $image_url) {
            $dont_add = false;
            try {
                $destination = $dir_path . $sku . '_image_' . $i . '.jpeg';
                $new_dest = $new_dir_path . $sku . '_image_' . $i . '.jpeg';
                $final_dest = $webp_dir . $sku . '_image_' . $i . '.WEBP';

                $image_content = file_get_contents($image_url);
                // Determine image format
                $image_info = exif_imagetype($image_url);
                if ($image_info !== false) {
                    // If image is not JPEG, convert it to JPEG
                    if ($image_info !== IMAGETYPE_JPEG) {
                        $img = new Imagick();
                        $img->readImageBlob($image_content);
                        $img->setImageFormat('jpeg');
                        // Set white background to handle transparency
                        $img->setImageBackgroundColor('white');
                        $img = $img->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
                        $image_content = $img->getImageBlob();
                        $img->destroy();
                    }
                } else {
                    throw new ImagickException('Impossibile determinare il tipo di immagine.');
                }

                // Create Imagick object from image content
                $imagick = new Imagick();
                $imagick->readImageBlob($image_content);

                // Write image content to destination using Imagick
                $imagick->writeImage($destination);
                $imagick->destroy();

                // Process image with aggiungiBandeBianche function
                $i++;
            } catch (ImagickException $e) {
               $ch = curl_init($image_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				$image_content = curl_exec($ch);
			
				if (curl_errno($ch)) {
					error_log('Errore durante il download dell\'immagine: ' . curl_error($ch));
				} else {
					curl_close($ch);

                    try {
                        // Get image format
                        error_log("salvato con altro ". $destination);
                        $image_info = exif_imagetype($image_url);
                        if ($image_info === false) {
                            throw new Exception('Impossibile determinare il tipo di immagine.');
                        }

                        // Check image format and process accordingly
                        if ($image_info === IMAGETYPE_JPEG) {
                            if(!file_put_contents($destination, $image_content))
                            {
                                throw new Exception('Impossibile putcontent.');
                            }
                        } else {
                            $immagine_originale = imagecreatefromstring($image_content);
                            if ($immagine_originale === false) {
                                throw new Exception('Impossibile convertire l\'immagine in JPEG.');
                            }
                            // Save image as JPEG
                            imagejpeg($immagine_originale, $destination, 80);
                            imagedestroy($immagine_originale);
                        }

                        $i++;
                    } catch (Exception $e) {
                        error_log('Error processing image: ' . $e->getMessage());
                        // Handle exception appropriately (e.g., log error, skip image)
                        $dont_add = true;
                    }

				}
            }
            if(!$dont_add)
            {
                $converted_images[] = aggiungiBandeBianche($destination, $new_dest, $larghezzaSet, $altezzaSet, $final_dest, $webp_dir, $i);
            }
        }

    }
    if(!empty($converted_images))
    {
        inserisci_prodotto($prodotto, $webp_dir, $converted_images, $setup);
    }
    else
    {
        if($existing_product_id)
        {
                inserisci_prodotto($prodotto, $webp_dir, array(), $setup);
        }
        else
        {
            $falliti = $wpdb->get_var("SELECT falliti FROM $update_table");
            $new_falliti = $falliti + 1;
            $wpdb->query("UPDATE $update_table SET falliti = $new_falliti");
        }
    }
 }
 
function aggiungiBandeBianche($percorsoImmagineInput, $percorsoImmagineOutput, $larghezzaSet, $altezzaSet, $final_dest, $webp_dir, $i) {
    
    if ($i !== 1) {
        return convertiJPEGtoWebP($percorsoImmagineInput, $final_dest, 80, $webp_dir);
    }

    $jpegImage = new Imagick($percorsoImmagineInput);

    // Ottieni le dimensioni originali dell'immagine
    $larghezzaOriginale = $jpegImage->getImageWidth();
    $altezzaOriginale = $jpegImage->getImageHeight();

    if($larghezzaOriginale > $altezzaOriginale)
    {
        $nuovaLarghezza = $larghezzaSet;
        $delta = $larghezzaOriginale - $larghezzaSet;
        $deltaPerc = 1 - ($delta / $larghezzaOriginale);
        $nuovaAltezza = $altezzaOriginale * $deltaPerc;
    }
    else
    {
        $nuovaAltezza = $altezzaSet;
        $delta = $altezzaOriginale - $altezzaSet;
        $deltaPerc = 1 - ($delta / $altezzaOriginale);
        $nuovaLarghezza = $larghezzaOriginale * $deltaPerc;
    }
    $x = ($larghezzaSet - $nuovaLarghezza) / 2;
    $y = ($altezzaSet - $nuovaAltezza) / 2;

    $jpegImage->resizeImage($nuovaLarghezza, $nuovaAltezza, Imagick::FILTER_LANCZOS, 1);

    $nuovaImmagine = new Imagick();
    $nuovaImmagine->newImage($larghezzaSet, $altezzaSet, 'white');

    // Copia l'immagine originale sulla nuova immagine con sfondo bianco
    $nuovaImmagine->compositeImage($jpegImage, Imagick::COMPOSITE_OVER, $x, $y);

    // Salva l'immagine risultante in formato JPEG
    $nuovaImmagine->setImageFormat('jpeg');
    $nuovaImmagine->setImageCompressionQuality(100);
    $nuovaImmagine->writeImage($percorsoImmagineOutput);

    // Distruggi gli oggetti Imagick
    $jpegImage->destroy();
    $nuovaImmagine->destroy();
    return convertiJPEGtoWebP($percorsoImmagineOutput, $final_dest, 80, $webp_dir);
}

function convertiJPEGtoWebP($inputPath, $outputPath, $qualita, $webp_dir) {
    // Verifica se Imagick è disponibile
    if (!class_exists('Imagick')) {
        die("La conversione in WebP richiede la libreria Imagick. Assicurati che sia installata sul tuo server.");
    }
 
    // Crea un oggetto Imagick dall'immagine JPEG
    $jpegImage = new Imagick($inputPath);

    // Controlla se l'immagine ha trasparenza
    if ($jpegImage->getImageAlphaChannel()) {
        // Abilita la trasparenza
        $jpegImage->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
        $jpegImage->setImageBackgroundColor('transparent');
        $jpegImage = $jpegImage->flattenImages();
    }

    // Imposta la qualità della compressione
    $jpegImage->setImageCompressionQuality($qualita);

    // Salva l'immagine WebP
    if (!$jpegImage->writeImage($outputPath)) {
        die("Errore durante il salvataggio dell'immagine WebP.");
    }

    // Distrugge l'oggetto Imagick
    $jpegImage->destroy();

    return $outputPath;
}

function upload_image_to_media_library($image_path, $WEBP_directory, $prodotto) {
    $upload_dir = wp_upload_dir();
    $image_file = $upload_dir['path'] . '/' . basename($image_path);
    
    if (copy($image_path, $image_file))
    {
        $attachment = array(
            'post_mime_type' => 'image/jpeg', 
            'post_title' => basename($image_file),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment($attachment, $image_file);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $image_file);
        wp_update_attachment_metadata($attach_id, $attach_data);
        return $attach_id;
    } else {
        return false;
    }
}

?>