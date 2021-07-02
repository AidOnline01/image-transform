<?php
namespace ITDevv\ImageTransform;

include __DIR__ . '/../Image.php';

$path = __DIR__ . '/source.jpg';
$dest = __DIR__ . '/dist/dest';

cropImage($path, $dest);
fitImage($path, $dest);
resizeImage($path, $dest);

function cropImage($path, $dest) {
    $image = new Image();

    if(!$image->load($path)) die('Image doesn\'t exist');
    
    $image->crop(400, 400, 'center', 'center');
    
    $image->save("$dest.crop.jpg", 'jpg');
    $image->save("$dest.crop.jpg.webp", 'webp');
}

function fitImage($path, $dest) {
    $image = new Image();

    if(!$image->load($path)) die('Image doesn\'t exist');
    
    $image->fit(400, 400, 'center', 'center');
    
    $image->save("$dest.fit.jpg", 'jpg');
    $image->save("$dest.fit.jpg.webp", 'webp');
}

function resizeImage($path, $dest) {
    $image = new Image();

    if(!$image->load($path)) die('Image doesn\'t exist');
    
    $image->resize(400, 400, 'center', 'center');
    
    $image->save("$dest.resize.jpg", 'jpg');
    $image->save("$dest.resize.jpg.webp", 'webp');
}