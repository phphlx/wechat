<?php

$wx = new WeChat();
$img = $wx->createQrcode(0, 10);
echo "<img src='$img' >";
//删除菜单
//echo $wx->deleteMenu();
//创建菜单
//$menuList = include 'menu.php';
//echo $wx->createMenu($menuList);

class WeChat
{
    const APPID = 'wx7813d1c7bff1a7f7';
    const APPSECRET = '7ee50ab06cd6b382fa8b0707cf31944b';

    /**
     * 得到 access_token 全局唯一有效的
     * @return mixed
     */
    private function getAccessToken()
    {
        //缓存文件
        $cacheFile = self::APPID . '_cache.log';
        //判断文件是否存在, 不存在表示没有缓存
        // 判断修改时间是否过了有效期, 如果没有则不进行 url请求
        if (is_file($cacheFile) && filemtime($cacheFile) + 7000 > time()) {
            echo '我是文件缓存' . PHP_EOL;
            return file_get_contents($cacheFile);
        }

        //第一次或缓存过期时执行
        $surl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
        $url = sprintf($surl, self::APPID, self::APPSECRET);
        $json = $this->httpRequest($url);
        $arr = json_decode($json, true);
        $access_token = $arr['access_token'];
        file_put_contents($cacheFile, $access_token);
        echo '我是文件未缓存' . PHP_EOL;
        return $access_token;
    }

    private function getAccessTokenMemcached()
    {
        //缓存 key值
        $cacheKey = self::APPID . '_key';

        $memcached = new Memcached();
        $memcached->addServer('localhost', 11211);
        //添加, 如果存在返回 false
//        $memcached->add($cacheKey, 'abc');
//        $memcached->set($cacheKey, 'abc', 0, 60);
        //有缓存
        if (false != ($access_token = $memcached->get($cacheKey))) {
//            echo '我是memcached缓存' . PHP_EOL;
            return $access_token;
        }

        //第一次或缓存过期时执行
        $surl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
        $url = sprintf($surl, self::APPID, self::APPSECRET);
        $json = $this->httpRequest($url);
        $arr = json_decode($json, true);
        $access_token = $arr['access_token'];
        $memcached->set($cacheKey, $access_token, 7000);
//        echo '我是memcached未缓存' . PHP_EOL;
        return $access_token;
    }

    /**
     * 创建自定义菜单
     * @param array | string $arr
     * @return bool|string
     */
    public function createMenu($arr)
    {
        if (is_array($arr)) {
            $arr = json_encode($arr, JSON_UNESCAPED_UNICODE);
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->getAccessTokenMemcached();

        $json = $this->httpRequest($url, $arr);

        return $json;
    }

    /**
     * 删除菜单
     * @return bool|string
     */
    public function deleteMenu()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=' . $this->getAccessTokenMemcached();
        $result = $this->httpRequest($url);
        return $result;
    }

    public function uploadMaterial($path, $type = 'image', $is_forever = 0)
    {
        if ($is_forever) {
            $surl = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=%s&type=%s';
        } else {
            $surl = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token=%s&type=%s';
        }
        $url = sprintf($surl, $this->getAccessTokenMemcached(), $type);

        $json = $this->httpRequest($url, [], $path);

        $arr = json_decode($json, true);
        return $arr['media_id'] ? $arr['media_id'] : '';
    }

    public function keFuMessage($openid, $message)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $this->getAccessTokenMemcached();
        $data = [
            'touser' => $openid,
            'msgtype' => 'text',
            'text' => ['content' => $message]
        ];

        return $this->httpRequest($url, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    public function createQrcode($temp = 0, $id = 1)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $this->getAccessTokenMemcached();
        if ($temp === 0) {
            $data = [
                "expire_seconds" => 2592000,
                "action_name" => "QR_SCENE",
                "action_info" => [
                    "scene" => [
                        "scene_id" => $id
                    ]
                ]
            ];
        } else {
            $data = [
                "action_name" => "QR_LIMIT_SCENE",
                "action_info" => [
                    "scene" => [
                        "scene_id" => $id
                    ]
                ]
            ];
        }

        // 得到 ticket
        $json = $this->httpRequest($url, json_encode($data, JSON_UNESCAPED_UNICODE));
        $arr = json_decode($json, true);
        $ticket = $arr['ticket'];
        // 用 ticket获取二维码资源
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($ticket);
        $imgResource = $this->httpRequest($url);

        file_put_contents('qrcode.jpg', $imgResource);
        return 'qrcode.jpg';
    }

    /**
     * @param string $url url地址
     * @param string | array $postArr 请求体
     * @param string $file 上传文件绝对地址
     * @return bool|string
     */
    private function httpRequest($url, $postArr = '', $file = '')
    {
        if (!empty($file)) {
            $postArr['media'] = new CURLFile($file);
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MSIE001');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        if ($postArr) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postArr);
        }

        $data = curl_exec($ch);

        if (curl_errno($ch) > 0) {
            echo curl_error($ch);
            $data = '';
        }

        curl_close($ch);
        return $data;
    }
}
