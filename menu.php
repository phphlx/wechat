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
//        {
//            "type":"click",
//            "name":"今日歌曲",
//            "key":"V1001_TODAY_MUSIC"
//        },
//        {
//            "name":"菜单",
//            "sub_button":[
//                {
//                    "type":"view",
//                    "name":"百度搜索20个字",
//                    "url":"http://www.baidu.com/"
//                },
//                {
//                    "type":"view",
//                    "name":"知乎",
//                    "url":"http://zhihu.com",
//                },
//                {
//                    "type":"view",
//                    "name":"京东",
//                    "url":"http://jd.com",
//                },
//            ]
//        },
//        {
//            "type":"click",
//            "name":"最后一首的时间",
//            "key":"V1001_TODAY_MUSIC123"
//        },
//    ]
// }';

return [
    'button' => [
        [
            "type" => "click",
            "name" => "今日歌曲",
            "key" => "001"
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
                    'name' => '知乎',
                    'url' => 'http://zhihu.com'
                ],
                [
                    'type' => 'view',
                    'name' => '狗东',
                    'url' => 'http://jd.com'
                ]
            ]
        ],
        [
            'type' => 'click',
            'name' => '最后一只恐龙',
            'key' => 'key002'
        ]
    ]
];
