<?php

namespace ITDevv\ImageTransform;
use Imagick;

class Image {
    public $source;
    public $info;

    public function load($path) {
        $this->path = $path;

        if(!file_exists($this->path)) return false;

        
        $this->source = new Imagick();

        $this->source->readImage($this->path);

        $this->info = $this->getInfo();

        return true;
    }

    private function getInfo() {
        $info = [];
        $info['ext'] = strToLower($this->source->getImageFormat());
        if($info['ext'] === 'jpeg') $info['ext'] = 'jpg';

        $info['mime'] = $this->source->getImageMimeType();

        $info['width'] = $this->source->getImageWidth();
        $info['height'] = $this->source->getImageHeight();
        
        return $info;
    }

    // resize image to exact size
    public function resize($width, $height) {
        if(!$width && !$height) return false;
        
        if(!$width)  $width = (int)($height / $this->info['height'] * $this->info['width']); 
        if(!$height) $height = (int)($width / $this->info['width'] * $this->info['height']);
        
        $this->source->resizeImage($width, $height, Imagick::FILTER_SINC, 1);
    }

    // crop image to specified area
    public function crop($width, $height, $xPos = 'center', $yPos = 'center') {
        $cropWidth = $width;
        $cropHeight = $height;
        $originalAspect = $this->info['width'] / $this->info['height'];
        $fitAspect = $width / $height;

        // get image minimal dimension
        if($originalAspect < $fitAspect) $height = (int)($width / $this->info['width'] * $this->info['height']);
        else $width = (int)($height / $this->info['height'] * $this->info['width']);

        // get x, y
        if($xPos === 'left') $x = 0;
        if($xPos === 'center') $x = ($width - $cropWidth) / 2;
        if($xPos === 'right') $x = $width - $cropWidth;

        if($yPos === 'top') $y = 0;
        if($yPos === 'center') $y = ($height - $cropHeight) / 2;
        if($yPos === 'bottom') $y = $height - $cropHeight;

        $this->source->resizeImage($width, $height, Imagick::FILTER_SINC, 1);
        $this->source->cropImage($cropWidth, $cropHeight, $x, $y);
    }

    // resize image to area without changing ratio
    public function fit($width, $height) {
        if($width < $this->info['width']) {
            $height = (int)($width / $this->info['width'] * $this->info['height']); 
        }

        if($height < $this->info['height']) {
            $width = (int)($height / $this->info['height'] * $this->info['width']); 
        }

        $this->source->resizeImage($width, $height, Imagick::FILTER_SINC, 1);
    }

    public function save($path, $type, $quality = 85) {
        $dir = dirname($path);
        if(!file_exists($dir)) mkdir($dir, 0775, true);

        $this->source->setImageCompressionQuality($quality);
        $this->source->writeImage("$type:$path");
    }
}