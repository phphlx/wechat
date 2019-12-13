<?php
// 方倍工作室
header('Content-type:text');

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
        $this->writeLog($ret, 2);
    }

    private function textFunction($obj)
    {
        if ($obj->Content == '音乐') {
            return $this->musicFunction($obj);
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

                //顶级添加数据库
                if (empty($eventKey)) {
                    $sql = "insert into users(openid) values(?)";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([$obj->FromUserName]);
                } else {
                    $id = (int)str_replace('qrscene_', '', $eventKey);
                    $sql = "select * from users where id=$id";
                    $row = $this->pdo->query($sql)->fetch();
                    $sql = "insert into users(openid, f1, f2, f3) values(?, ?, ?, ?)";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([$obj->FromUserName, $row['openid'], $row['f1'], $row['f2']]);
                }
                return $this->createText($obj, '欢迎关注 phphlx的公众平台' . "\n" . '这里有惊喜');
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

    private function writeLog($log, $flag = 1)
    {
        $flagStr = $flag == 1 ? '接收' : '发送';
        $prevStr = $flagStr . date('Y-m-d H:i:s') . "--------------------------------------------\n";
        $log = $prevStr . $log . PHP_EOL;
        file_put_contents('test.log', $log, FILE_APPEND);
        return true;
    }
}