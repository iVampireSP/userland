/**
 * 推送通知服务工作线程
 */

// 安装事件 - 当服务工作线程首次安装时触发
self.addEventListener('install', event => {
    console.log('服务工作线程已安装');
    self.skipWaiting(); // 确保新的服务工作线程立即激活
});

// 激活事件 - 当服务工作线程激活时触发
self.addEventListener('activate', event => {
    console.log('服务工作线程已激活');
    return self.clients.claim(); // 确保服务工作线程控制所有客户端
});

// 推送事件 - 当收到推送通知时触发
self.addEventListener('push', event => {
    console.log('收到推送消息', event);

    // 解析推送数据
    let data = {};
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data = {
                title: '新通知',
                body: event.data.text(),
                icon: '/favicon.ico'
            };
        }
    }

    // 设置通知选项
    const options = {
        body: data.body || '您有一条新通知',
        icon: data.icon || '/favicon.ico',
        badge: data.badge || '/badge.png',
        data: data.data || {},
        actions: data.actions || [],
        tag: data.tag || 'default',
        renotify: data.renotify || false,
        requireInteraction: data.requireInteraction || false,
        silent: data.silent || false
    };

    // 显示通知
    event.waitUntil(
        self.registration.showNotification(data.title || '系统通知', options)
    );
});

// 通知点击事件 - 当用户点击通知时触发
self.addEventListener('notificationclick', event => {
    console.log('通知被点击', event);

    // 关闭通知
    event.notification.close();

    // 获取通知数据
    const data = event.notification.data;
    const url = data.url || '/';

    // 点击通知时打开指定URL
    event.waitUntil(
        clients.matchAll({ type: 'window' })
            .then(clientList => {
                // 检查是否已有打开的窗口
                for (const client of clientList) {
                    if (client.url === url && 'focus' in client) {
                        return client.focus();
                    }
                }
                // 如果没有打开的窗口，则打开新窗口
                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
    );
});

// 通知关闭事件 - 当通知被关闭时触发
self.addEventListener('notificationclose', event => {
    console.log('通知被关闭', event);
    // 可以在这里添加通知关闭时的逻辑
});
