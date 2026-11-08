<?php
/**
 * Returns a usable <img src> for a product.
 * Falls back to an inline placeholder when the product has no image
 * or the uploaded file is missing (avoids broken-image icons).
 */
function productImage($image, $basePath = '../') {
    $file = basename((string)$image);
    if ($file !== '' && is_file(__DIR__ . '/../uploads/' . $file)) {
        return $basePath . 'uploads/' . rawurlencode($file);
    }
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300">'
         . '<rect width="100%" height="100%" fill="#e2e8f0"/>'
         . '<text x="50%" y="50%" fill="#64748b" font-family="Arial" font-size="22" '
         . 'text-anchor="middle" dominant-baseline="middle">No image</text></svg>';
    return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
}
