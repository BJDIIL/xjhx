<?php
/**
 * Uploadify
 * Copyright (c) 2012 Reactive Apps, Ronnie Garcia
 * Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 * User: gaorui
 * Date: 2016/12/16
 * Time: 23:31
 */

namespace Admin\Controller;


class UploadifyController extends AdminController
{
    public function upload()
    {
        // Define a destination
        // 验证上传的token
        $verifyToken = md5('unique_salt' . $_POST['timestamp']);

        if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
            // 检查文件扩展名
            $fileTypes = array('jpg', 'jpeg', 'gif', 'png'); // File extensions
            $fileParts = pathinfo($_FILES['Filedata']['name']);
            if (in_array($fileParts['extension'], $fileTypes)) {
                // 存储位置
                $targetFolder = C('UPLOAD_FILE_PATH') . date('Y-m-d'); // Relative to the root
                if (!is_dir($targetFolder)) {
                    mkdir($targetFolder, 0777, true);
                }
                $tempFile = $_FILES['Filedata']['tmp_name'];
                $targetFile = $targetFolder . '/' . date('YmdHis') . '.' . $fileParts['extension'];
                $saved = move_uploaded_file($tempFile, iconv("UTF-8", "gb2312", $targetFile));
                echo $targetFile;
            } else {
                echo 'Invalid file type.';
            }
        }
    }

    public function crop()
    {
        if ($_POST) {
            $crop = new CropAvatar(isset($_POST['src']) ? $_POST['src'] : null, isset($_POST['data']) ? $_POST['data'] : null);

            $response = array('state' => 200, 'message' => $crop->getMsg(), 'result' => $crop->getResult());

            echo json_encode($response);
        }

    }
}

class CropAvatar
{
    private $src;
    private $data;
    private $dst;
    private $type;
    private $msg;
    private $name;

    function __construct($src, $data)
    {
        $this->setSrc($src);
        $this->setData($data);
        $this->setDst();
        $this->crop($this->src, $this->dst, $this->data);
    }

    private function setSrc($src)
    {
        $this->src = $src;
        $this->type = strtolower(pathinfo($src, PATHINFO_EXTENSION));
    }

    private function setData($data)
    {
        $this->data = $data;
    }

    private function setDst()
    {
        $this->name = C('UPLOAD_FILE_PATH') . date('Y-m-d') . '/' . date('YmdHis') . '.png';
        $this->dst = $this->name;
    }

    private function crop($src, $dst, $data)
    {
        if (!empty($src) && !empty($dst) && !empty($data)) {
            $src = iconv('UTF-8', 'gb2312', $src);
            switch ($this->type) {
                case 'gif':
                    $src_img = imagecreatefromgif($src);
                    break;

                case 'jpeg':
                case 'jpg':
                    $src_img = imagecreatefromjpeg($src);
                    break;

                case 'png':
                    $src_img = @imagecreatefrompng($src);
                    break;
            }

            if (!$src_img) {
                $this->msg = "Failed to read the image file";
                return;
            }

            $size = getimagesize($src);
            $size_w = $size[0]; // natural width
            $size_h = $size[1]; // natural height

            $src_img_w = $size_w;
            $src_img_h = $size_h;

            $degrees = $data['rotate'];

            // Rotate the source image
            if (is_numeric($degrees) && $degrees != 0) {
                // PHP's degrees is opposite to CSS's degrees
                $new_img = imagerotate($src_img, -$degrees, imagecolorallocatealpha($src_img, 0, 0, 0, 127));

                imagedestroy($src_img);
                $src_img = $new_img;

                $deg = abs($degrees) % 180;
                $arc = ($deg > 90 ? (180 - $deg) : $deg) * M_PI / 180;

                $src_img_w = $size_w * cos($arc) + $size_h * sin($arc);
                $src_img_h = $size_w * sin($arc) + $size_h * cos($arc);

                // Fix rotated image miss 1px issue when degrees < 0
                $src_img_w -= 1;
                $src_img_h -= 1;
            }

            $tmp_img_w = $data['width'];
            $tmp_img_h = $data['height'];
            $dst_img_w = C('CROP_WIDTH');
            $dst_img_h = C('CROP_HEIGHT');

            $src_x = $data['x'];
            $src_y = $data['y'];

            if ($src_x <= -$tmp_img_w || $src_x > $src_img_w) {
                $src_x = $src_w = $dst_x = $dst_w = 0;
            } else if ($src_x <= 0) {
                $dst_x = -$src_x;
                $src_x = 0;
                $src_w = $dst_w = min($src_img_w, $tmp_img_w + $src_x);
            } else if ($src_x <= $src_img_w) {
                $dst_x = 0;
                $src_w = $dst_w = min($tmp_img_w, $src_img_w - $src_x);
            }

            if ($src_w <= 0 || $src_y <= -$tmp_img_h || $src_y > $src_img_h) {
                $src_y = $src_h = $dst_y = $dst_h = 0;
            } else if ($src_y <= 0) {
                $dst_y = -$src_y;
                $src_y = 0;
                $src_h = $dst_h = min($src_img_h, $tmp_img_h + $src_y);
            } else if ($src_y <= $src_img_h) {
                $dst_y = 0;
                $src_h = $dst_h = min($tmp_img_h, $src_img_h - $src_y);
            }

            // Scale to destination position and size
            $ratio = $tmp_img_w / $dst_img_w;
            $dst_x /= $ratio;
            $dst_y /= $ratio;
            $dst_w /= $ratio;
            $dst_h /= $ratio;

            $dst_img = imagecreatetruecolor($dst_img_w, $dst_img_h);

            // Add transparent background to destination image
            imagefill($dst_img, 0, 0, imagecolorallocatealpha($dst_img, 0, 0, 0, 127));
            imagesavealpha($dst_img, true);

            $result = imagecopyresampled($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

            if ($result) {
                if (!imagepng($dst_img, $dst)) {
                    $this->msg = "Failed to save the cropped image file";
                }
            } else {
                $this->msg = "Failed to crop the image file";
            }

            imagedestroy($src_img);
            imagedestroy($dst_img);
        }
    }

    private function codeToMessage($code)
    {
        $errors = array(UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini', UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded', UPLOAD_ERR_NO_FILE => 'No file was uploaded', UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder', UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk', UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',);

        if (array_key_exists($code, $errors)) {
            return $errors[$code];
        }

        return 'Unknown upload error';
    }

    public function getResult()
    {
        return $this->name;//!empty($this->data) ? $this->dst : $this->src;
    }

    public function getMsg()
    {
        return $this->msg;
    }
}