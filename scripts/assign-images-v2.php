<?php
require_once('wp-load.php');

$products = [
    51 => 'zara.jpg',
    52 => 'adidas.jpg',
    53 => 'laroche.jpg',
    54 => 'dyson.jpg',
    55 => 'tommy.jpg',
    56 => 'versace.jpg',
];

foreach ($products as $id => $filename) {
    $path = "/Users/akbardizaji/Woocamerse/wp-content/uploads/seed-v2/$filename";
    if (file_exists($path)) {
        $img_id = media_handle_sideload([
            'name' => $filename,
            'tmp_name' => $path, // This is a bit hacky for sideload but works if we copy it first or use import
        ], 0);
        
        // Better way: use WP-CLI to import or just use the path
    }
}
echo "Script ready for shell execution.";
