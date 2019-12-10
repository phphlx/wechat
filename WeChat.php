<?php

$wx = new WeChat();
echo $wx->getAccessToken();
class WeChat
{
    const APPID = 'wx27c2747cc495283e';
    const APPSECRET = 'eadbb56af0be6b3268325eb5af9e4399';

    /**
     * 得到 access_token 全局唯一有效的
     * @return mixed
     */
    public function getAccessToken()
    {
        //缓存文件
        $cacheFile = self::APPID.'_cache.log';
        //判断文件是否存在, 不存在表示没有缓存
        // 判断修改时间是否过了有效期, 如果没有则不进行 url请求
        if (is_file($cacheFile) && filemtime($cacheFile) + 7000 > time()) {
            return file_get_contents($cacheFile);
        }

        //第一次或缓存过期时执行
        $surl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
        $url = sprintf($surl, self::APPID, self::APPSECRET);
        $json = $this->httpRequest($url);
        $arr = json_decode($json, true);
        $access_token =  $arr['access_token'];
        file_put_contents($cacheFile, $access_token);
        return $access_token;
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
        curl_setopt($ch, CURLOPT_USERAGENT, '');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

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