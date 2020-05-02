<h1 align="center"> poster（海报） </h1>
<p align="center"> php快速生成海报</p>
## 安装

```shell
$ composer require xuxuxuzw/poster
```

## 示例
```php
 		$background_image_path = "./resource/background.jpg";//定义海报背景图路径

        $font_path = './resource/SourceHanSans-CN-Regular.ttf';//定义海报使用的文字字体路径

        //定义用户头像信息
        $head_portrait = [
            'width' => 120,
            'height' => 120,
            'img_path' => './resource/avatar.png',
            'is_circular' => true
        ];

        //定义用户昵称信息
        $nickname = [
            'user_name' => 'xzw',
            'font_path' => $font_path,
            'font_size' => 18,
            'color' => '#3399ff'
        ];

        //定义海报保存位置(如果直接浏览器输出可不用)
        $image_save_path = "./images/test.png";

        #获取带logo的二维码图片资源
        //创建海报
        $qrcode = new Xuxuxuzw\Poster\Poster(250, 250);
        //获取logo图片在二维码的坐标（也可以自己写具体的坐标值，这里提供了 左上角、左下角、右上角、右下角、中上、中心、中下七个点坐标的快速获取）
        $logo_xy = $qrcode->getPosition(0, 50, 50)[Poster::POSITION_CENTER];
        //这里采用链式，可以直接在后面追加
        $image = $qrcode->addQrCode('https://me.csdn.net/xzw1315915745', [250, 250], Poster::POSITION_CENTER)//添加一个二维码
        ->addImage('./resource/logo.jpg', $logo_xy, [50, 50])//添加一个图片(logo)
        ->getImageResource(); //这里的二维码不单独使用，所以直接获取图片资源回来即可，生成海报使用 render 方法


        #生成一张海报
        //创建海报
        $poster = new Xuxuxuzw\Poster\Poster(800, 1422);
        //这里使用到了用户头像和昵称，直接使用 addUser 方法
        //ps：头像和昵称可以一起展示，也可以单独展示，一起展示时可以设置昵称在头像的右侧和下方，默认昵称在头像右侧
        $poster->user_name_location_direction = Poster::USER_NAME_LOCATION_RIGHT;//设置昵称在头像右侧

        //快速获取二维码图片在海报的坐标，这里是获取右下角坐标
        $qrcode_xy = $poster->getPosition(20, 250, 250)[Poster::POSITION_RIGHT_BOTTOM];

        $poster->addImage($background_image_path, [0, 0], [800, 1422])//添加一个图片(背景图)
        ->addImageResource($image, $qrcode_xy, [250, 250])//添加一个图片资源(上方得到的二维码)
        //->addQrCode('https://me.csdn.net/xzw1315915745', [250, 250], Poster::POSITION_RIGHT_BOTTOM)//直接添加二维码方式，可以直接设置二维码在海报的位置，但无 logo
        ->addUser($head_portrait, $nickname, Poster::POSITION_TOP_LEFT_CORNER)//添加用户头像和昵称信息
        ->addText('A B C', 72, [80, 330], '#33ff99', $font_path)//添加文字，
        ->render();//浏览器输出图片，传入图片路径，即可直接输出到服务器 render($image_save_path);
```

## 方法介绍

#### 构造方法
```php
    /**
    * Poster constructor.
    * @param $width 海报宽度
    * @param $height 海报高度
    * @param array $background_image_color 16进制背景颜色 #cccccc
    */
    $poster = new Xuxuxuzw\Poster\Poster($width,$height,$background_image_color = '#cccccc');
```

#### 添加图片
```php
    /**
    * 添加图片
    * User : xuzhaowen
    * @param $img_path 图片路径
    * @param array $xy 坐标
    * @param array $size_wh 宽度高度
    * @return $this
    */
    public function addImage($img_path, $xy = [0, 0], $size_wh = [100, 100]){}
```

#### 添加图片资源
```php
	/**
     * 添加图片资源
     * User : xuzhaowen
     * @param $imageResource 图片资源
     * @param array $xy 坐标
     * @param array $size_wh 宽度高度
     * @return $this
     */
    public function addImageResource($imageResource, $xy = [0, 0], $size_wh = [100, 100]){}
```

#### 添加文字
```php
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
    public function addText($text, $size = 14, $xy = [0, 0], $color = '#000000', $font_file, $angle = 0){}
```

#### 添加二维码
```php
	/**
     * 添加二维码
     * User : xuzhaowen
     * @param $text 文字内容
     * @param array $size_wh 宽度高度
     * @param int $position 坐标方向（使用自带的7个坐标值）
     * @param array $xy 坐标 $position 无效时有效
     * @return $this
     */
    public function addQrCode($text, $size_wh = [100, 100], $position = 0, $xy = [0, 0]){}
```
#### 添加用户头像和用户昵称
```php
	/**
     * 添加用户头像和用户昵称
     * User : xuzhaowen
     * @param array $head_portrait
          [
            'width' => 120,//头像宽度
            'height' => 120,//头像高度
            'img_path' => './resource/avatar.png', //头像路径
            'is_circular'=>false, //是否圆形的
        ]
     * @param array $nickname
          [
            'user_name' => 'xzw', //用户昵称
            'font_path' => $font_path, //字体包路径
            'font_size' => 18, //昵称字体大小
     		'color' => '#3399ff' //16进制颜色 #000000
        ]
     * @param int $position 坐标方向（使用自带的7个坐标值）
     * @return $this
     */
    public function addUser($head_portrait, $nickname, $position = 0){}
```
