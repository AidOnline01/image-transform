<?php
namespace ITDevv\ImageTransform;

class HttpImage {
  private $resized = false;
  private $sourceDir;
  private $destDir;
  private $name;
  private $ext;
  private $finalName;
  
  function __construct($args) {
    header("Image-Proccessor: PHP");
    
    $this->tmpPath = $args['tmp_path'];
    $this->refreshTime = $args['refresh_time'];

    $this->parseUrl();

    // download
    if($this->type === 'download') $this->download();

    // resize
    if($this->type === 'crop') $this->crop();
    else if($this->type === 'fit') $this->fit();
    else if($this->type === 'resize') $this->resize();

    if($this->isWebpSupported()) {
      $sourcePath = "$this->sourceDir/$this->name.$this->ext";
      $path = $this->resized ? "$this->destDir/$this->finalName.$this->ext" : $sourcePath;

      header('Content-Type: image/webp');

      if(!$this->isCached("$path.webp")) {
        $this->loadImage($path);
        $this->source->save("$path.webp", 'webp');
      }

      echo file_get_contents("$path.webp");
      exit;
    }
    else {
      $sourcePath = "$this->sourceDir/$this->name.$this->ext";
      $path = $this->resized ? "$this->destDir/$this->finalName.$this->ext" : $sourcePath;
      $mime = mime_content_type($path);

      header("Content-Type: $mime");
      echo file_get_contents($path);
      exit;
    }
  }

  private function parseUrl() {
    // get image path
    $link = $_SERVER['REQUEST_URI'];
    $link = urldecode($link);
    $link = preg_replace('/\?.*/', '', $link);

    // parse link
    preg_match('/(.*)\/([^\/\.]+)\.(.+)$/', $link, $matches);
    $dir = $matches[1];

    $this->sourceDir = $_SERVER['DOCUMENT_ROOT'] . $dir;
    $this->destDir = $this->tmpPath . $dir;
    $this->name = $matches[2];
    $this->ext = $matches[3];
    $this->finalName = $this->name;

    $this->type = $_GET['type'] ?? false;
    $this->width = $_GET['width'] ?? false;
    $this->height = $_GET['height'] ?? false;
  }

  private function loadImage($path) {
    $this->source = new Image();

    if(!$this->source->load($path)) {
      http_response_code(404);
      exit;
    }

    // if image bigger than limits, just serve it
    if($this->source->info['width'] > 10000 || $this->source->info['height'] > 10000) {
      header("Content-Type: " . $this->source->info['mime']);
      echo $this->source->source->getImageBlob();
      exit;
    }
  }

  private function download() {
    header('Content-Type: application/download');
    header('Content-Disposition: attachment; filename="' . $this->name . '.' . $this->ext . '"');
    header("Content-Length: " . filesize("$this->sourceDir/$this->name.$this->ext"));

    echo file_get_contents("$this->sourceDir/$this->name.$this->ext");

    exit;
  }

  private function resize() {
    if(!$this->width && !$this->height) return;

    $this->resized = true;
    $this->finalName = "$this->name.resize.w-" . ($this->width ?? 'auto') . ".h-" . ($this->height ?? 'auto');

    $sourcePath = "$this->sourceDir/$this->name.$this->ext";
    $destPath = "$this->destDir/$this->finalName.$this->ext";

    if($this->isCached($destPath)) return;

    $this->loadImage($sourcePath);
    $this->source->resize($this->width, $this->height);
    $this->source->save($destPath, $this->source->info['ext']);
  }

  private function crop() {
    if(!$this->width || !$this->height) return;

    $this->resized = true;
    $this->finalName = "$this->name.crop.w-$this->width.h-$this->height";

    $sourcePath = "$this->sourceDir/$this->name.$this->ext";
    $destPath = "$this->destDir/$this->finalName.$this->ext";

    if($this->isCached($destPath)) return;

    $this->loadImage($sourcePath);
    $this->source->crop($this->width, $this->height);
    $this->source->save($destPath, $this->source->info['ext']);
  }

  private function fit() {
    if(!$this->width || !$this->height) return;

    $this->resized = true;
    $this->finalName = "$this->name.fit.w-$this->width.h-$this->height";

    $sourcePath = "$this->sourceDir/$this->name.$this->ext";
    $destPath = "$this->destDir/$this->finalName.$this->ext";

    if($this->isCached($destPath)) return;

    $this->loadImage($sourcePath);
    $this->source->fit($this->width, $this->height);
    $this->source->save($destPath, $this->source->info['ext']);
  }

  private function isCached($path) {
    if(!file_exists($path)) return false;

    // check age of cache
    $fresh = time() - (int)filectime($path) < $this->refreshTime;
    if(!$fresh) unlink($path);

    return $fresh;
  }

  public function deleteOutdatedFile($path) {
    // if no file return
    if(!file_exists($path)) return false;
    
    // get current time and file time
    $now = time();
    $file_date = filemtime($path);
    $diff = $now - $file_date;
    $diff_minutes = floor($diff / 60);

    // if older than refreshTime, delete file
    if($diff_minutes >= $this->args['refresh_time']) {
      unlink($path);
    }
  }

  private function isWebpSupported() {
    return strpos($_SERVER['HTTP_ACCEPT'] ?? null, 'image/webp') !== false || strpos($_SERVER['HTTP_USER_AGENT'] ?? null, ' Chrome/' ) !== false;
  }
}