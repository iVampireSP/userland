<?php

return [
    // 从环境变量获取推送服务器列表，如果为空则返回空数组
    'servers' => array_filter(explode(',', env('PUSH_SERVERS', ''))),
];
