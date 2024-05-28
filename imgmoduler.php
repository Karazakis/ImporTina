<?php
function modula_immagine($images, $sku, $dir_path, $new_dir_path, $webp_dir, $prodotto, $wpdb)
 {   
     $info_table = $wpdb->prefix . '_inventory_info';
     $altezzaSet = $wpdb->get_var("SELECT img_height FROM $info_table");
     $larghezzaSet = $wpdb->get_var("SELECT img_width FROM $info_table");
     $converted_images = array();
     $i = 0;
     foreach ($images as $image_url)
     {   
         $destination = $dir_path . $sku . '_image_' . $i .'.jpeg';
         $new_dest = $new_dir_path . $sku . "'_image_" . $i. '.jpeg';
         $final_dest = $webp_dir . $sku . "'_image_" . $i. '.WEBP';
         $ch = curl_init($image_url);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         $image_content = curl_exec($ch);
     
         if (curl_errno($ch)) {
             echo 'Errore durante il download dell\'immagine: ' . curl_error($ch);
         } else {
             curl_close($ch);
             
             $image_info = exif_imagetype($image_url);
             if ($image_info !== false) {
                 if ($image_info === IMAGETYPE_JPEG) {
                     file_put_contents($destination, $image_content);
                 } else {
                     $immagine_originale = imagecreatefromstring($image_content);
                     if ($immagine_originale !== false) {
                         imagejpeg($immagine_originale, $destination, 80);
                         imagedestroy($immagine_originale);
                     } else {
                         echo 'Impossibile convertire l\'immagine in JPEG.';
                     }
                 }
             } else {
                 echo 'Impossibile determinare il tipo di immagine.';
             }
             
             $converted_images[] = aggiungiBandeBianche($destination, $new_dest, $larghezzaSet, $altezzaSet, $final_dest, $webp_dir);
         }
         $i++;
     }
     inserisci_prodotto($prodotto, $webp_dir, $converted_images);
 }
 


 function aggiungiBandeBianche($percorsoImmagineInput, $percorsoImmagineOutput, $larghezzaSet, $altezzaSet, $final_dest, $webp_dir) {
  
    $dimensioniOriginale = getimagesize($percorsoImmagineInput);
    $larghezzaOriginale = $dimensioniOriginale[0];
    $altezzaOriginale = $dimensioniOriginale[1];


    $deltaLarg = $larghezzaOriginale - (int)$larghezzaSet;
    $deltaAlt = $altezzaOriginale - (int)$altezzaSet;

    if($deltaAlt >= $deltaLarg)
    {
        $nuovaLarghezzaOriginale = $larghezzaOriginale - $deltaAlt;
        $nuovaAltezzaOriginale = $altezzaOriginale - $deltaAlt;
    }
    else
    {
        $nuovaLarghezzaOriginale = $larghezzaOriginale - $deltaLarg;
        $nuovaAltezzaOriginale = $altezzaOriginale - $deltaLarg;
    }

    $immagineOriginale = imagecreatefromjpeg($percorsoImmagineInput);
    $immagineRidimensionata = imagecreatetruecolor($nuovaLarghezzaOriginale, $nuovaAltezzaOriginale);
    imagecopyresampled($immagineRidimensionata, $immagineOriginale, 0, 0, 0, 0, $nuovaLarghezzaOriginale, $nuovaAltezzaOriginale, $larghezzaOriginale, $altezzaOriginale);

    $nuovaImmagine = imagecreatetruecolor($larghezzaSet, $altezzaSet);
    $coloreBianco = imagecolorallocate($nuovaImmagine, 255, 255, 255);
    imagefilledrectangle($nuovaImmagine, 0, 0, $larghezzaSet, $altezzaSet, $coloreBianco);

    $posizioneInizio = $larghezzaSet;
    $posizioneFine = $larghezzaSet + $nuovaLarghezzaOriginale;


    imagecopy($nuovaImmagine, $immagineRidimensionata, 0, 0, 0, 0, $larghezzaSet, $altezzaSet);

    imagejpeg($nuovaImmagine, $percorsoImmagineOutput, 80); // 80 è la qualità della compressione JPEG (puoi regolarla secondo le tue preferenze)

    imagedestroy($immagineOriginale);
    imagedestroy($nuovaImmagine);

    return(convertiJPEGtoWebP($percorsoImmagineOutput, $final_dest, 80, $webp_dir));
}


function convertiJPEGtoWebP($inputPath, $outputPath, $qualita = 80, $webp_dir) {
    if (!function_exists('imagewebp')) {
        die("Il tuo server non supporta WebP. Assicurati che il modulo GD sia configurato con il supporto WebP.");
    }
    $jpegImage = imagecreatefromjpeg($inputPath);

    if (!$jpegImage) {
        die("Impossibile aprire l'immagine JPEG.");
    }
    if (!imagewebp($jpegImage, $outputPath, $qualita)) {
        die("Errore durante il salvataggio dell'immagine WebP.");
    }

    imagedestroy($jpegImage);
    return ($outputPath);
}

function upload_image_to_media_library($image_path, $WEBP_directory, $prodotto) {
    $upload_dir = wp_upload_dir();
    $image_file = $upload_dir['path'] . '/' . basename($image_path);
    
    // Copia l'immagine nella directory di upload di WordPress
    if (copy($image_path, $image_file))
    {
        $attachment = array(
            'post_mime_type' => 'image/jpeg', // Modifica il tipo MIME se necessario
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