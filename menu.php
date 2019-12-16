<?php


//return <<<EOF
//{
//    "button": [
//        {
//            "type": "click",
//            "name": "今日歌曲",
//            "key": "V1001_TODAY_MUSIC"
//        },
//        {
//            "name": "菜单5个字",
//            "sub_button": [
//                {
//                    "type": "view",
//                    "name": "百度搜索20个字",
//                    "url": "http://www.百度.com/"
//                },
//                {
//                    "type": "view",
//                    "name": "知乎",
//                    "url": "http://zhihu.com",
//                },
//            ]
//        },
//        {
//            "type": "click",
//            "name": "最后一个恐龙",
//            "key": "abcd1234"
//        }
//    ]
//}
//EOF;

//return '{
//    "button":[
//    {
//        "type":"click",
//        "name":"今日歌曲",
//        "key":"V1001_TODAY_MUSIC"
//    },
//    {
//        "name":"菜单",
//        "sub_button":[
//        {
//            "type":"view",
//            "name":"百度搜索20个字",
//            "url":"http://www.baidu.com/"
//        },
//        {
//            "type":"view",
//            "name":"知乎",
//            "url":"http://zhihu.com",
//        },
//        {
//            "type":"view",
//            "name":"京东",
//            "url":"http://jd.com",
//        }]
//    },
//    {
//        "type":"view",
//        "name":"最后一首的时间",
//        "url":"http://baidu.com"
//    }]
// }';
$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https' : 'http';
return [
    'button' => [
        [
            "type" => "click",
            "name" => "首页",
            "key" => "index001"
        ],
        [
            "name" => "二级菜单",
            "sub_button" => [
                [
                    'type' => 'view',
                    'name' => '百度搜索20个字',
                    'url' => 'http://baidu.com'
                ],
                [
                    'type' => 'view',
                    'name' => '显示个人信息',
                    'url' => $protocol . '://' . $_SERVER['HTTP_HOST'] . '/auth/goto.php'
                ],
                [
                    'type' => 'click',
                    'name' => '客服,点击',
                    'key' => 'kefu001'
                ],
                [
                    "type" => "pic_sysphoto",
                    "name" => "系统拍照",
                    "key" => "photo001",
                ],
                [
                    "name" => "发送位置",
                    "type" => "location_select",
                    "key" => "rselfmenu_2_0"
                ]
            ]
        ],
        [
            'type' => 'view',
            'name' => '个人中心v2ex',
            'url' => 'https://www.v2ex.com/'
        ]
    ]
];
