This is image transformation lib, that allow transform images on fly as well as use it directly in code.

### HttpImage

You can transform image on fly, using links on format. Where type is one of (crop|resize|fit).

> https://site.com/image.png?type=(type)&width=100&height=100


To use it. You need to add rewrite to nginx and setup `image.php`.

###### nginx

```nginx
rewrite \.(jpg|png|jpeg|gif) /image.php break;
```

###### php

```php
include __DIR__ . '/vendor/autoload.php';

use ITDevv\ImageTransform\HttpImage;

new HttpImage([
  'refresh_time' => 100, // keep cache for 100sec
  'tmp_path' => __DIR__ . '/tmp_images' // keep cache here
]);

```

### Direct use

```php
include __DIR__ . '/vendor/autoload.php';

use ITDevv\ImageTransform\Image;

$image = new Image();

if(!$image->load(__DIR__ . '/image.png')) die('No such image');

$width = 100;
$height = 100;

// crop image (800:800 -> 300:200) will first resize image to 300:300, 
// and then crop it to 300:200
$image->crop($width, $height);

// resize keeping aspect ratio
$image->fit($width, $height);

// resize ignoring aspect ratio
$image->resize($width, $height);

// save image
// $image->save($outputPath, $ext, $quality?);
$image->save(__DIR__ . '/dest.jpg', 'jpg', 85);
$image->save(__DIR__ . '/dest.webp', 'webp'); 
```