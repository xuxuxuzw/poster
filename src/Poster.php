<?php

namespace Xuxuxuzw\Poster;

class Poster
{
    public $background_image; // 背景图

    public $background_image_w;
    public $background_image_h;
    public $background_image_center_x;//中心点x坐标
    public $background_image_center_y;//中心点y坐标
    //public $multiple = 1;//推广海报的比例
    public $max_nickname_width;//最大昵称宽度
    public $max_nickname_line_number = 2;//两行

    public $user_name_location_direction = self::USER_NAME_LOCATION_BOTTOM;//用户名称在头像的方向，1-右，2-下，默认1-右
    //用户名称在头像的方向，1-右，2-下，默认1-右
    const USER_NAME_LOCATION_RIGHT = 1;
    const USER_NAME_LOCATION_BOTTOM = 2;

    const X = 0;
    const Y = 1;

    public $qrcode_margin = 50;//二维码边距
    public $user_margin = 50;//用户信息边距
    public $user_padding = 30;//头像和昵称间隔

    //七个点位置
    const POSITION_TOP_LEFT_CORNER = 1;//1-左上角
    const POSITION_LEFT_BOTTOM = 2;//2-左下角
    const POSITION_TOP_RIGHT_CORNER = 3;//3-右上角
    const POSITION_RIGHT_BOTTOM = 4;//4-右下角
    const POSITION_TOP_CENTER = 5;//5-中上
    const POSITION_CENTER = 6;//6-中间
    const POSITION_CENTER_BOTTOM = 7;//7-中下

    public $qrcode_position = null;
    public $user_head_position = null;
    public $user_nickname_position = null;


    /**
     * Poster constructor.
     * @param string $width 海报宽度
     * @param string $height 海报高度
     * @param array $background_image_color 16进制背景颜色 #cccccc
     */
    public function __construct($width, $height, $background_image_color = '#cccccc')
    {
        $this->createBg($width, $height, $background_image_color);

        //参数初始化
        $this->background_image_w = $width;
        $this->background_image_h = $height;

        $this->background_image_center_x = $width / 2;
        $this->background_image_center_y = $height / 2;
    }

    /**
     * 获取左上角、左下角、右上角、右下角、中上、中心、中下七个点坐标
     * User : xuzhaowen
     * @param int $margin
     * @param int $image_w
     * @param int $image_h
     * @return array
     */
    public function getPosition($margin = 0, $image_w = 0, $image_h = 0)
    {
        return [
            self::POSITION_TOP_LEFT_CORNER => [$margin, $margin],
            self::POSITION_LEFT_BOTTOM => [$margin, $this->background_image_h - $margin - $image_h],
            self::POSITION_TOP_RIGHT_CORNER => [$this->background_image_w - $margin - $image_w, $margin],
            self::POSITION_RIGHT_BOTTOM => [$this->background_image_w - $margin - $image_w, $this->background_image_h - $margin - $image_h],
            self::POSITION_TOP_CENTER => [$this->background_image_center_x - ($image_w / 2), $margin],
            self::POSITION_CENTER => [$this->background_image_center_x - ($image_w / 2), $this->background_image_center_y - ($image_h / 2)],
            self::POSITION_CENTER_BOTTOM => [$this->background_image_center_x - ($image_w / 2), $this->background_image_h - $margin - $image_h],
        ];
    }

    /**
     * User : xuzhaowen
     * @param $width
     * @param $height
     * @param $background_image_color
     */
    protected function createBg($width, $height, $background_image_color)
    {
        $background_image_color = self::hex2rgb($background_image_color, 0, true);

        $this->background_image = imagecreatetruecolor($width, $height);
        $canvas = imagecolorallocate($this->background_image, $background_image_color[0], $background_image_color[1], $background_image_color[2]);
        imagefill($this->background_image, 0, 0, $canvas);
    }

    /**
     * 添加图片
     * User : xuzhaowen
     * @param string $img_path 图片路径
     * @param array $xy 坐标
     * @param array $size_wh 宽度高度
     * @return $this
     */
    public function addImage($img_path, $xy = [0, 0], $size_wh = [100, 100])
    {
        list($l_w, $l_h) = getimagesize($img_path);
        $img = $this->createImageFromFile($img_path);
        imagecopyresized($this->background_image, $img, $xy[0], $xy[1], 0, 0, $size_wh[0], $size_wh[1], $l_w, $l_h);
        imagedestroy($img);
        return $this;
    }

    /**
     * 添加图片资源
     * User : xuzhaowen
     * @param $imageResource 图片资源
     * @param array $xy 坐标
     * @param array $size_wh 宽度高度
     * @return $this
     */
    public function addImageResource($imageResource, $xy = [0, 0], $size_wh = [100, 100])
    {
        imagecopyresized($this->background_image, $imageResource, $xy[0], $xy[1], 0, 0, $size_wh[0], $size_wh[1], $size_wh[0], $size_wh[1]);
        imagedestroy($imageResource);
        return $this;
    }

    /**
     * 添加文字
     * User : xuzhaowen
     * @param string $text 文字内容
     * @param int $size 字体大小，单位px
     * @param array $xy 坐标
     * @param array $color 16进制颜色 #000000
     * @param string $font_file 字体包路径
     * @param int $angle 透明度
     * @return $this
     */
    public function addText($text, $size = 14, $xy = [0, 0], $color = '#000000', $font_file, $angle = 0)
    {
        $color = self::hex2rgb($color);

        imagettftext($this->background_image, $size, $angle, $xy[0], $xy[1], $color, $font_file, $text);

        return $this;
    }

    /**
     * 添加二维码
     * User : xuzhaowen
     * @param string $text 文字内容
     * @param array $size_wh 宽度高度
     * @param int $position 坐标方向（使用自带的7个坐标值）
     * @param array $xy 坐标 $position 无效时有效
     * @return $this
     */
    public function addQrCode($text, $size_wh = [100, 100], $position = 0, $xy = [0, 0])
    {
        if (!is_readable('./qrcodeImage')) mkdir('./qrcodeImage', 0700);

        $file_name = './qrcodeImage/' . md5($text) . '.png';

        \PHPQRCode\QRcode::png($text, $file_name, 0, 4);

        if (in_array($position, array_keys($this->getPosition()))) {
            $this->qrcode_position = $this->getPosition($this->qrcode_margin, $size_wh[0], $size_wh[1]);
            $xy = [$this->qrcode_position[$position][self::X], $this->qrcode_position[$position][self::Y]];
        }

        $image = $this->addImage($file_name, $xy, $size_wh);

        if (file_exists($file_name)) {
            unlink($file_name);
        }

        return $image;
    }

    /**
     * 添加用户头像和用户昵称
     * User : xuzhaowen
     * @param array $head_portrait
     * [
     * 'width' => 120, //头像宽度
     * 'height' => 120, //头像高度
     * 'img_path' => './resource/avatar.png', //头像路径
     * 'is_circular'=>false, //是否圆形的
     * ]
     * @param array $nickname
     * [
     * 'user_name' => 'xzw', //用户昵称
     * 'font_path' => $font_path, //字体包路径
     * 'font_size' => 18, //昵称字体大小
     * 'color' => '#3399ff' //16进制颜色 #000000
     * ]
     * @param int $position 坐标方向（使用自带的7个坐标值）
     * @return $this
     */
    public function addUser($head_portrait, $nickname, $position = 0)
    {
        empty($head_portrait['width']) && $head_portrait['width'] = 0;
        empty($head_portrait['height']) && $head_portrait['height'] = 0;

        $padding = $this->user_padding;

        //获取昵称长度
        $user_name = $nickname['user_name'];

        $this->max_nickname_width = !empty($this->max_nickname_width) ? $this->max_nickname_width : $this->background_image_w / 4;//昵称最大宽度，默认是背景图4分之1

        $string = $this->getStrMaxLen($user_name, $nickname['font_path'], $nickname['font_size'], $this->max_nickname_width);

        $allow_max_text_len = $string['str_len'];
        $string_height = $string['str_height'];

        $text_count_len = mb_strlen($user_name, 'utf8');
        $text_list = [];
        for ($i = 0; $i < ceil($text_count_len / $allow_max_text_len); $i++) {
            if ($i >= ($this->max_nickname_line_number - 1) && ($i * $allow_max_text_len + $allow_max_text_len) < $text_count_len) {
                $str = mb_substr($user_name, $i * $allow_max_text_len, $allow_max_text_len - 1, "UTF-8") . '...';
                $text_list[] = $str;
                break;
            }
            $str = mb_substr($user_name, $i * $allow_max_text_len, $allow_max_text_len, "UTF-8");
            $text_list[] = $str;
        }

        $font_w = $this->max_nickname_width;
        $font_h = (count($text_list) * $string_height) + ((count($text_list) - 1) * 3);

        //计算画布大小
        if ($this->user_name_location_direction == self::USER_NAME_LOCATION_RIGHT) {
            $canvas_widht = $head_portrait['width'] + $font_w + $padding;
            $canvas_height = $head_portrait['height'] > $font_h ? $head_portrait['height'] : $font_h + $padding;
        } else {
            $canvas_widht = $head_portrait['width'] > $font_w ? $head_portrait['width'] : $font_w;
            $canvas_height = $head_portrait['height'] + $font_h + $padding;
        }

        $canvas_center_x = $canvas_widht / 2;
        $canvas_center_y = $canvas_height / 2;

        //创建画布
        //创建一个图
        $canvas = imagecreatetruecolor($canvas_widht, $canvas_height);
        //创建透明背景色，主要127参数，其他可以0-255，因为任何颜色的透明都是透明
        $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
        //指定颜色为透明（做了移除测试，发现没问题）
        imagecolortransparent($canvas, $transparent);
        //保留透明颜色
        imagesavealpha($canvas, true);
        //填充图片颜色
        imagefill($canvas, 0, 0, $transparent);
        //判断坐标
        if ($this->user_name_location_direction == self::USER_NAME_LOCATION_RIGHT) {
            //昵称在头像右方
            if (in_array($position, [3, 4])) {
                //右
                //图片位置
                $avatar_x = $canvas_widht - $head_portrait['width'];
                $avatar_y = 0;
                //文字位置
                $nickname_x = 0;
                $nickname_y = $canvas_center_y;
            } else {
                //左
                //图片位置
                $avatar_x = 0;
                $avatar_y = 0;
                //文字位置
                $nickname_x = $head_portrait['width'] + $padding;
                $nickname_y = $canvas_center_y;
            }
        } else {
            //昵称在头像下方
            //图片位置
            $avatar_x = $canvas_center_x - ($head_portrait['width'] / 2);
            $avatar_y = 0;

            //文字位置
            $nickname_x = $canvas_center_x - ($font_w / 2);
            $nickname_y = $canvas_height - $font_h + $padding / 2;
        }
        //图片与文字合并
        if (!empty($head_portrait['img_path'])) {
            list($avatar_w, $avatar_h) = getimagesize($head_portrait['img_path']);
            $avatar = $this->createImageFromFile($head_portrait['img_path']);
            if ($head_portrait['is_circular']) {
                $avatar = self::circularImg($avatar);
            }
            imagecopyresized($canvas, $avatar, $avatar_x, $avatar_y, 0, 0, $head_portrait['width'], $head_portrait['height'], $avatar_w, $avatar_h);
            imagedestroy($avatar);
        }

        for ($i = 0; $i < count($text_list); $i++) {
            if ($i > 0) {
                $nickname_y += $string_height;
                $nickname_y += 3;
            }

            $nickname['color'] = self::hex2rgb($nickname['color']);

            imagettftext($canvas, $nickname['font_size'], 0, $nickname_x, $nickname_y, $nickname['color'], $nickname['font_path'], $text_list[$i]);
        }
        //5、将内容合并到大图中

        $position_xy = $this->getPosition($this->user_margin, $canvas_widht, $canvas_height);
        $position_xy[self::POSITION_LEFT_BOTTOM][self::Y] -= $font_h;
        $position_xy[self::POSITION_RIGHT_BOTTOM][self::Y] -= $font_h;
        $position_xy[seLF::POSITION_CENTER_BOTTOM][self::Y] -= $font_h;

        imagecopyresized($this->background_image, $canvas, $position_xy[$position][self::X], $position_xy[$position][self::Y], 0, 0, $canvas_widht, $canvas_height, $canvas_widht, $canvas_height);
        imagedestroy($canvas);

        return $this;
    }

    /**
     * 正方形图片尺寸修改
     * @param $image  string 图片地址/支持微信、QQ头像等没有后缀的网络图
     * @param $new_width  string 新图片大小
     * @param $is_path  bool 是否路径
     * @return resource 返回图片资源
     */
    public function changeImgSize($image, $new_width, $is_path = false)
    {
        if ($is_path) {
            $image = imagecreatefromstring(file_get_contents($image, true));
        }

        $old_width = imagesx($image);//图片原宽度
        $old_height = imagesy($image);//图片原高度
        //缩放比例
        $per = round($new_width / $old_width, 3);
        $n_w = $old_width * $per;
        $n_h = $old_width * $per;

        $new = imagecreatetruecolor($n_w, $n_h);

        //copy部分图像并调整
        imagecopyresampled($new, $image, 0, 0, 0, 0, $n_w, $n_h, $old_width, $old_width);

        return $new;
    }

    /**
     * 生成圆形图片
     * @param $image  string 图片地址/支持微信、QQ头像等没有后缀的网络图
     * @param $is_path bool 是否路径
     * @param $saveName string 保存文件名，默认空。
     * @param $border array 设置图片的边框，默认不设置
     * 'border' => [
     * 'width' => '2px',
     * 'color' => '#ffffff'
     * ],
     * @return resource 返回图片资源或保存文件
     */
    public static function circularImg($image, $is_path = false, $saveName = '', $border = [])
    {
        $is_border = 0;
        $border_color = ['r' => 255, 'g' => '255', 'b' => 255];
        if (!empty($border)) {
            $is_border = str_replace('px', '', $border['width']);
            $border_color = self::hex2rgb(trim($border['color']), 0, true);
        }

        if ($is_path) {
            $src_img = imagecreatefromstring(file_get_contents($image, true));
        } else {
            $src_img = $image;
        }
        $w = imagesx($src_img) - ($is_border * 2);
        $h = imagesy($src_img) - ($is_border * 2);
        $w = $h = min($w, $h);

        $img = imagecreatetruecolor($w, $h);

        //这一句一定要有
        imagesavealpha($img, true);

        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);

        imagefill($img, 0, 0, $bg);

        $r = $w / 2; //圆半径
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }
        $image = $img;
        //如果边框的值大于0，即有边框
        if ($is_border > 0) {

            $border_image_w = imagesx($src_img);
            $border_image_h = imagesy($src_img);
            $border_image_w = $border_image_h = min($border_image_w, $border_image_h);

            $circular_w = imagesx($src_img);
            $circular_h = imagesy($src_img);
            $circular_w = $circular_h = min($circular_w, $circular_h);
            //创建颜色背景图片
            $circular_img = imagecreatetruecolor($circular_w, $circular_h);
            $border_image = imagecreatetruecolor($border_image_w, $border_image_h);

            imagesavealpha($circular_img, true);
            imagesavealpha($border_image, true);
            //透明
            $circular_bg = imagecolorallocatealpha($circular_img, 255, 255, 255, 127);
            //头像边框颜色
            $border_image_color = imagecolorallocatealpha($border_image, $border_color['r'], $border_color['g'], $border_color['b'], 0);
            imagefill($circular_img, 0, 0, $circular_bg);
            imagefill($border_image, 0, 0, $border_image_color);

            $circular_r = $circular_w / 2; //圆半径
            for ($x = 0; $x < $circular_w; $x++) {
                for ($y = 0; $y < $circular_h; $y++) {
                    $rgbColor = imagecolorat($border_image, $x, $y);
                    if (((($x - $circular_r) * ($x - $circular_r) + ($y - $circular_r) * ($y - $circular_r)) < ($circular_r * $circular_r))) {
                        imagesetpixel($circular_img, $x, $y, $rgbColor);
                    }
                }
            }

            $r = $w / 2; //圆半径
            for ($x = 0; $x < $w; $x++) {
                for ($y = 0; $y < $h; $y++) {
                    $rgbColor = imagecolorat($img, $x, $y);
                    if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $circular_r))) {
                        imagesetpixel($circular_img, $x + $is_border, $y + $is_border, $rgbColor);
                    }
                }
            }
            $image = $circular_img;
        }

        //返回资源
        if (!$saveName) return $image;
        //输出图片到文件
        imagepng($image, $saveName);
        //释放空间
        imagedestroy($image);
        imagedestroy($image);
    }

    /**
     * 十六进制 转 RGBA
     * User: xuzhaowen@3ncto.com
     * @param $hexColor string 颜色16进制值，如fff或#ffffff
     * @param int $alpha
     * @param bool $is_array
     * @return array|int
     */
    public static function hex2rgb($hexColor, $alpha = 0, $is_array = false)
    {
        $color = str_replace('#', '', $hexColor);
        if (strlen($color) > 3) {
            $rgb = array(
                'r' => hexdec(substr($color, 0, 2)),
                'g' => hexdec(substr($color, 2, 2)),
                'b' => hexdec(substr($color, 4, 2))
            );
        } else {
            $color = $hexColor;
            $r = substr($color, 0, 1) . substr($color, 0, 1);
            $g = substr($color, 1, 1) . substr($color, 1, 1);
            $b = substr($color, 2, 1) . substr($color, 2, 1);
            $rgb = array(
                'r' => hexdec($r),
                'g' => hexdec($g),
                'b' => hexdec($b)
            );
        }
        $img = imagecreatetruecolor(1, 1);
        imagesavealpha($img, true);
        if ($is_array) {
            return $rgb;
        }
        $rgba_color = imagecolorallocatealpha($img, $rgb['r'], $rgb['g'], $rgb['b'], $alpha);

        return $rgba_color;
    }

    /**
     * 像素转磅
     * User: xuzhaowen@3ncto.com
     * @param $px_size string 像素
     * @return float|mixed
     */
    function Px2Pounds($px_size)
    {
        $px_size = str_replace('px', '', $px_size);
        $pounds_list = [
            '6' => '5',
            '7' => '5.5',
            '8' => '6.5',
            '10' => '7.5',
            '12' => '9',
            '14' => '10.5',
            '16' => '12',
            '18' => '14',
            '20' => '15',
            '21' => '16',
            '22' => '16.5',
            '24' => '18',
            '29' => '22',
            '30' => '22.5',
            '32' => '24',
            '34' => '26',
            '48' => '36',
            '56' => '42',
        ];
        if (!empty($pounds_list[$px_size]))
            return $pounds_list[$px_size];

        return round(($px_size * 72) / 96, 1);
    }

    /**
     * 获取文字最大长度
     * User : xuzhaowen
     * @param $content
     * @param $font_path
     * @param int $font_size
     * @param $max_width
     * @return array
     */
    public function getStrMaxLen($content, $font_path, $font_size = 12, $max_width)
    {
        $size = $font_size;

        $str_size = imagettfbbox($size, 0, $font_path, $content);

        $w = abs($str_size[2] - $str_size[0]);
        $h = abs($str_size[5] - $str_size[3]);

        if ($w > $max_width) {
            $content_len = mb_strlen($content, 'utf8') - 1;
            $new_content = mb_substr($content, 0, $content_len, "UTF-8");
            return $this->getStrMaxLen($new_content, $font_path, $font_size, $max_width);
        } else {
            return [
                'str_len' => mb_strlen($content, 'utf8'),
                'str_width' => $w,
                'str_height' => $h,
            ];
        }
    }

    /**
     * 输出图片
     * @param $file_name
     */
    public function render($file_name = '')
    {
        if ($file_name != '') {
            imagepng($this->background_image, $file_name);
        } else {
            Header("Content-Type: image/png");
            imagepng($this->background_image);
        }
        imagedestroy($this->background_image);
    }

    /**
     * 获取海报资源
     * @return mixed
     */
    public function getImageResource()
    {
        return $this->background_image;
    }

    /**
     * 从图片文件创建Image资源
     * @param $file
     * @return bool|resource
     */
    public function createImageFromFile($file)
    {
        if (preg_match('/http(s)?:\/\//', $file)) {
            $fileSuffix = $this->getNetworkImgType($file);
        } else {
            $fileSuffix = pathinfo($file, PATHINFO_EXTENSION);
        }
        if (!$fileSuffix) return false;
        switch ($fileSuffix) {
            case 'jpeg':
                $theImage = @imagecreatefromjpeg($file);
                break;
            case 'jpg':
                $theImage = @imagecreatefromjpeg($file);
                break;
            case 'png':
                $theImage = @imagecreatefrompng($file);
                break;
            case 'gif':
                $theImage = @imagecreatefromgif($file);
                break;
            default:
                $theImage = @imagecreatefromstring(file_get_contents($file));
                break;
        }
        return $theImage;
    }

    /**
     * 获取网络图片类型
     * @param $url
     * @return bool
     */
    public function getNetworkImgType($url)
    {
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url); //设置需要获取的URL
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //支持https
        curl_exec($ch); // 执行curl会话
        $http_code = curl_getinfo($ch); //获取curl连接资源句柄信息
        curl_close($ch); // 关闭资源连接
        if ($http_code['http_code'] == 200) {
            $theImgType = explode('/', $http_code['content_type']);
            if ($theImgType[0] == 'image') {
                return $theImgType[1];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}