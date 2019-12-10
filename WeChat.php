<?php

$wx = new WeChat();
$menuList = include 'menu.php';
echo $wx->createMenu($menuList);

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
        $memcached->set($cacheKey, $access_token, 0, 7000);
//        echo '我是memcached未缓存' . PHP_EOL;
        return $access_token;
    }

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
     * @param string $url url地址
     * @param string | array $postArr 请求体
     * @param string $file 上传文件绝对地址
     * @return bool|string
     */
    private function httpRequest($url, $postArr = '', $file = '')
    {
        if (!empty($file)) {
            $postArr['pic'] = new CURLFile($file);
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