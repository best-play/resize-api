<?php
/**
 * Class Project for projects managing
 */
class Project
{
  protected $_key_dir;
  protected $_upload_dir;

  public function __construct()
  {
    /** Loading command */
    $this->_key_dir = 'userdata/';
    $this->_upload_dir = 'userdata/upload/';
    $this->command();
  }

  /** Command from client */
  protected function command()
  {
    $actionName = $_REQUEST['action'] . 'Action';
    if (method_exists($this, $actionName)) {
      echo json_encode($this->$actionName());
    } else {
      echo json_encode(['error' => 'Command not found']);
    }
  }

  protected function getRequest($param, $defaultValue = '')
  {
    return isset($_REQUEST[$param]) ? $_REQUEST[$param] : $defaultValue;
  }

  protected function checkKey($key)
  {
    if (!file_exists($this->_key_dir . $key)) {
      return false;
    }
    return true;
  }

  protected function resizeImg($filename, $img_name, $width, $height)
  {
    $img = null;
    $size = getimagesize($filename);
    $ext = $size['mime'];
    $origWidth = $size[0];
    $origHeight = $size[1];

    $width = $width ? $width : $origWidth;
    $height = $height ? $height : $origHeight;

    switch ($ext) {
      case 'image/jpg':
      case 'image/jpeg':
        $img = imagecreatefromjpeg($filename);
        break;

      case 'image/gif':
        $img = imagecreatefromgif($filename);
        break;

      case 'image/png':
        $img = imagecreatefrompng($filename);
        break;
    }

    $newImage = imagecreatetruecolor($width, $height);
    imagecopyresampled($newImage, $img, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);

    switch ($ext) {
      case 'image/jpg':
      case 'image/jpeg':
        // Check PHP supports this file type
        if (imagetypes() & IMG_JPG) {
          imagejpeg($newImage, $this->_upload_dir . $img_name, 100);
        }
        break;

      case 'image/gif':
        if (imagetypes() & IMG_GIF) {
          imagegif($newImage, $this->_upload_dir . $img_name);
        }
        break;

      case 'image/png':
        if (imagetypes() & IMG_PNG) {
          imagepng($newImage, $this->_upload_dir . $img_name, 0);
        }
        break;
    }

    return [
        "img_url" => $this->_upload_dir . $img_name,
        "width" => $width,
        "height" => $height
    ];
  }

  /** INIT new user */
  public function initAction()
  {
    $token = bin2hex(random_bytes(20));

    if (!is_dir($this->_key_dir) or !is_writable($this->_key_dir)) {
      return [
          "error" => 'Dir does not exist or is not writable',
          "success" => false
      ];
    }

    file_put_contents($this->_key_dir . $token, serialize([]));

    return [
        "success" => true,
        "key" => $token
    ];
  }

  /** GET all user's imgs */
  public function getAction()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $key = trim(strip_tags($this->getRequest('key')));
      if (!$this->checkKey($key)) {
        return ["error" => "Wrong key or key does not exist", "success" => false];
      }

      $content = file_get_contents($this->_key_dir . $key);

      return unserialize($content);
    }

    return ["error" => "Wrong Request", "success" => false];
  }

  /** UPLOAD new img from user */
  public function uploadAction()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $width = intval(trim(strip_tags($this->getRequest('width'))));
      $height = intval(trim(strip_tags($this->getRequest('height'))));

      $key = trim(strip_tags($this->getRequest('key')));
      if (!$this->checkKey($key)) {
        return ["error" => "Wrong key or key does not exist", "success" => false];
      }

      if (!@$_FILES['image']['name']) {
        return ["error" => "Please select image", "success" => false];
      }

      if ($_FILES['image']['error']) {
        return ["error" => $_FILES['image']['error'], "success" => false];
      }

      /* Check file type */
      $file = $_FILES['image']['tmp_name'];
      $info = getimagesize($file);
      if (($info[2] !== IMAGETYPE_JPEG) && ($info[2] !== IMAGETYPE_PNG) && ($info[2] !== IMAGETYPE_GIF)) {
        return ["error" => "You can upload only .jpg or .png or .gif file", "success" => false];
      }

      /* Generate unique name */
      $img_name = uniqid(rand()) . image_type_to_extension($info[2]);

      /* Resize image */
      $res = $this->resizeImg($file, $img_name, $width, $height);

      /* Save img url */
      $content = unserialize(file_get_contents($this->_key_dir . $key));
      array_push($content, $res);
      file_put_contents($this->_key_dir . $key, serialize($content));

      return [
          "success" => true,
          "img_url" => $res['img_url']
      ];
    }

    return ["error" => "Wrong Request", "success" => false];
  }
}

new Project();