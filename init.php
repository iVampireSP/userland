<?php

// 检测 storage 下的目录是否正确
$storage = "/app/storage";

// 有无 storage 目录
if (!is_dir($storage)) {
    mkdir($storage);
}

// 有无 app 目录
if (!is_dir($storage . '/app')) {
    mkdir($storage . '/app');

    // 有无 public 目录
    if (!is_dir($storage . '/app/public')) {
        mkdir($storage . '/app/public');
        echo "Created public directory.\n";
    }
}

// 有无 framework 目录
if (!is_dir($storage . '/framework')) {
    mkdir($storage . '/framework');

    // 有无 cache 目录
    if (!is_dir($storage . '/framework/cache')) {
        mkdir($storage . '/framework/cache');
    }

    // 有无 sessions 目录
    if (!is_dir($storage . '/framework/sessions')) {
        mkdir($storage . '/framework/sessions');
    }

    // 有无 testing 目录
    if (!is_dir($storage . '/framework/testing')) {
        mkdir($storage . '/framework/testing');
    }

    // 有无 views 目录
    if (!is_dir($storage . '/framework/views')) {
        mkdir($storage . '/framework/views');
    }
}

// 有无 logs 目录
if (!is_dir($storage . '/logs')) {
    mkdir($storage . '/logs');
}