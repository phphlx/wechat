<?php

header('Content-type:text');
var_dump($_SERVER);
$wechatObj = new wechatCallbackapiTest();

class wechatCallbackapiTest
{
    const TOKEN = 'weixin';

    private $pdo;

    public function __construct()
    {
        if (!empty($_GET['echostr'])) {
            echo $this->valid();
        } else {
            $this->pdo = include 'db.php';
            $this->responseMsg();
        }
    }

    public function valid()
    {
        $echoStr = $_GET['echostr'];
        if ($this->checkSignature()) {
            header('content-type:text');;
            return $echoStr;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET['signature'];
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];

        $token = self::TOKEN;
        $tmpArr = [$token, $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    public function responseMsg()
    {
        // 接收发送给公众号的消息
        $postStr = file_get_contents('php://input');
        $this->writeLog($postStr);
        $obj = simplexml_load_string($postStr, "SimpleXMLElement", LIBXML_NOCDATA);
        $func = $obj->MsgType . 'Function';
//        echo $ret = $this->$func($obj);
        echo $ret = call_user_func([$this, $func], $obj);

        if ($obj->Event != 'unsubscribe') {
            $this->writeLog($ret, 2);
        }
    }

    private function textFunction($obj)
    {
        if ($obj->Content == '音乐') {
            return $this->musicFunction($obj);
        } elseif (strstr($obj->Content, '位置，')) {
            $keyWord = trim(str_replace('位置，', '', $obj->Content));
            $sql = "select longitude, latitude from users where openid=?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$obj->FromUserName]);
            $user = $stmt->fetch();

            $url = "https://restapi.amap.com/v3/place/around?key=1e23c4eb5c9bed348fabf40dcc6e91ba&location=" .
                $user['longitude'] . "," . $user['latitude'] . "&radius=10000&types=050000&keywords=$keyWord";
            $result = $this->httpRequest($url);
            $arr = json_decode($result, true);
            if (count($arr['pois']) == 0) {
                return $this->createText($obj, '没找到相关服务');
            } else {
                $content = '🤯' . "\n";
                for ($i = 0; $i < 10; $i++) {
                    $content .= '名称: ' . $arr['pois'][$i]['name'] . "\n";
                    $content .= '地址: ' . $arr['pois'][$i]['address'] . "\n";
                    $content .= '名称: ' . $arr['pois'][$i]['distance'] . '米' . "\n";
                    $content .= '🤯' . PHP_EOL;
                }
                return $this->createText($obj, $content);
            }
        }
        $content = '公众号:' . $obj->Content;
        return $this->createText($obj, $content);
    }

    private function createText($obj, $content)
    {
        $str = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
        </xml>";
        $resultStr = sprintf($str, $obj->FromUserName, $obj->ToUserName, time(), $content);
        return $resultStr;
    }

    private function imageFunction($obj)
    {
        $mediaid = $obj->MediaId;
        return $this->createImage($obj, $mediaid);
    }

    private function createImage($obj, $mediaid)
    {
        $str = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[image]]></MsgType>
            <Image>
                <MediaId><![CDATA[%s]]></MediaId>
            </Image>
        </xml>";
        $resultStr = sprintf($str, $obj->FromUserName, $obj->ToUserName, time(), $mediaid);
        return $resultStr;
    }

    private function musicFunction($obj)
    {
        $title = '好听的music';
        $description = '试试看, 好不好听';
        $mediaId = '1fRRgpu_i8nt2I9PJ9hyC_LgDIK4w8QsqUg2Aw_MFfmAgqWlViylDxcjNX1tC4gV';
        $url = 'http://mp.phphlx.com/mp3/music1.mp3';
        return $this->createMusic($obj, $title, $description, $mediaId, $url);
    }

    private function createMusic($obj, $title, $description, $mediaId, $url)
    {
        $str = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[music]]></MsgType>
            <Music>
                <Title><![CDATA[%s]]></Title>
                <Description><![CDATA[%s]]></Description>
                <MusicUrl><![CDATA[%s]]></MusicUrl>
                <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
                <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
            </Music>
        </xml>";
        $resultStr = sprintf($str, $obj->FromUserName, $obj->ToUserName, time(), $title, $description, $url, $url,
            $mediaId);
        return $resultStr;
    }

    //时间处理
    private function eventFunction($obj)
    {
        $eventType = $obj->Event;

        switch ($eventType) {
            case 'CLICK':
                return $this->clickFunction($obj);
                break;
            case 'subscribe':
                // 如果 eventKey 没有值, 表示顶级
                $eventKey = (string)$obj->EventKey;
                $sql = "select id from users where openid=?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$obj->FromUserName]);
                $user = $stmt->fetch();
                //顶级添加数据库
                if (!$user) {
                    if (empty($eventKey)) {
                        $sqlInsert = "insert into users(openid) values(?)";
                        $stmtInsert = $this->pdo->prepare($sqlInsert);
                        $stmtInsert->execute([$obj->FromUserName]);
                    } else {
                        $id = (int)str_replace('qrscene_', '', $eventKey);
                        $sql = "select * from users where id=$id";
                        $row = $this->pdo->query($sql)->fetch();
                        $sql = "insert into users(openid, f1, f2, f3) values(?, ?, ?, ?)";
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute([$obj->FromUserName, $row['openid'], $row['f1'], $row['f2']]);
                    }
                }
                return $this->createText($obj, '欢迎关注 phphlx的公众平台' . "\n" . '这里有惊喜' . PHP_EOL . '回复位置,+关键词获取信息. 例如 位置,肯德基;');
                break;
            case 'LOCATION':
                $longitude = $obj->Longitude;
                $latitude = $obj->Latitude;
                $openid = $obj->FromUserName;
                $sql = "update users set latitude= ?, longitude=? where openid=?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$latitude, $longitude, $openid]);
                $locationObjStr = $this->httpRequest('https://restapi.amap.com/v3/geocode/regeo?key=1e23c4eb5c9bed348fabf40dcc6e91ba&location=' . $longitude . ',' . $latitude);
                $adcode = json_decode($locationObjStr)->regeocode->addressComponent->adcode;
                $weatherObj = json_decode($this->httpRequest('https://restapi.amap.com/v3/weather/weatherInfo?key=1e23c4eb5c9bed348fabf40dcc6e91ba&city=' . $adcode), true);
                return $this->createText($obj, "你上报了位置, 我返回了天气\n\n城市: " . $weatherObj['lives'][0]['province'] .
                    $weatherObj['lives'][0]['city'] . "\n天气: " .
                    $weatherObj['lives'][0]['weather'] . "\n实时温度: " . $weatherObj['lives'][0]['temperature'] . '℃');
                break;
        }
    }

    private function clickFunction($obj)
    {
        $eventKey = $obj->EventKey;
        if ($eventKey == 'index001') {
            return $this->createText($obj, '你点击了首页');
        } elseif ($eventKey == 'kefu001') {
            return $this->createText($obj, '你点击找客服小姐姐');
        }
        return $this->createText($obj, '我解决不了');
    }

    private function voiceFunction($obj)
    {
        $content = (string)$obj->Recognition ? $obj->Recognition : '语音未识别';
        return $this->createText($obj, $content);
    }

    private function writeLog($log, $flag = 1)
    {
        $flagStr = $flag == 1 ? '接收' : '发送';
        $prevStr = $flagStr . date('Y-m-d H:i:s') . "--------------------------------------------\n";
        $log = $prevStr . $log . PHP_EOL;
        file_put_contents('test.log', $log, FILE_APPEND);
        return true;
    }

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