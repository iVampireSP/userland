@extends('layouts.app')

@section('title', '推送通知订阅')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">推送通知订阅</div>

                    <div class="card-body">
                        <div class="alert alert-info">
                            <p>订阅推送通知后，您将可以接收到系统的重要通知。</p>
                        </div>

                        <div class="text-center mb-4">
                            <button id="subscribe-button" class="btn btn-primary">
                                <i class="fas fa-bell"></i> 订阅推送通知
                            </button>
                            <button id="unsubscribe-button" class="btn btn-danger d-none">
                                <i class="fas fa-bell-slash"></i> 取消订阅
                            </button>
                            <button id="test-notification-button" class="btn btn-info d-none ml-2">
                                <i class="fas fa-paper-plane"></i> 发送测试通知
                            </button>
                        </div>

                        <div id="subscription-status" class="alert d-none"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 服务工作线程注册
        let swRegistration = null;
        let isSubscribed = false;
        const applicationServerPublicKey = '{{ config('webpush.vapid.public_key') }}';

        // 将 base64 字符串转换为 Uint8Array
        function urlB64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');

            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);

            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        // 更新订阅按钮状态
        function updateSubscriptionStatus() {
            const subscribeButton = document.getElementById('subscribe-button');
            const unsubscribeButton = document.getElementById('unsubscribe-button');
            const testButton = document.getElementById('test-notification-button');

            if (isSubscribed) {
                subscribeButton.classList.add('d-none');
                unsubscribeButton.classList.remove('d-none');
                testButton.classList.remove('d-none');
            } else {
                subscribeButton.classList.remove('d-none');
                unsubscribeButton.classList.add('d-none');
                testButton.classList.add('d-none');
            }
        }

        // 显示状态消息
        function showStatus(message, type = 'info') {
            const statusDiv = document.getElementById('subscription-status');
            statusDiv.textContent = message;
            statusDiv.className = `alert alert-${type}`;
            statusDiv.classList.remove('d-none');
        }

        // 检查当前订阅状态
        function checkSubscriptionStatus() {
            if (!swRegistration) return;

            swRegistration.pushManager.getSubscription()
                .then(subscription => {
                    isSubscribed = !(subscription === null);
                    updateSubscriptionStatus();

                    if (isSubscribed) {
                        showStatus('您已成功订阅推送通知', 'success');
                    }
                });
        }

        // 订阅推送通知
        function subscribeUser() {
            const applicationServerKey = urlB64ToUint8Array(applicationServerPublicKey);

            swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: applicationServerKey
            })
                .then(subscription => {
                    // 发送订阅信息到服务器
                    return fetch('{{ route('push-subscription.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(subscription)
                    });
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        isSubscribed = true;
                        updateSubscriptionStatus();
                        showStatus('推送通知订阅成功！', 'success');
                    }
                })
                .catch(error => {
                    console.error('订阅失败:', error);
                    showStatus('订阅失败，请稍后重试', 'danger');
                });
        }

        // 取消订阅
        function unsubscribeUser() {
            swRegistration.pushManager.getSubscription()
                .then(subscription => {
                    if (subscription) {
                        return Promise.all([
                            subscription,
                            fetch('{{ route('push-subscription.delete') }}', {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ endpoint: subscription.endpoint })
                            })
                        ]);
                    }
                })
                .then(([subscription]) => {
                    return subscription.unsubscribe();
                })
                .then(() => {
                    isSubscribed = false;
                    updateSubscriptionStatus();
                    showStatus('已取消订阅推送通知', 'info');
                })
                .catch(error => {
                    console.error('取消订阅失败:', error);
                    showStatus('取消订阅失败，请稍后重试', 'danger');
                });
        }

        // 发送测试通知
        function sendTestNotification() {
            fetch('{{ route('push-subscription.test') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showStatus('测试通知已发送，请注意查看', 'info');
                    } else {
                        showStatus(data.message || '发送测试通知失败', 'warning');
                    }
                })
                .catch(error => {
                    console.error('发送测试通知失败:', error);
                    showStatus('发送测试通知失败，请稍后重试', 'danger');
                });
        }


        // 初始化
        if ('serviceWorker' in navigator && 'PushManager' in window) {
            // 注册服务工作线程
            navigator.serviceWorker.register('/service-worker.js')
                .then(registration => {
                    swRegistration = registration;
                    checkSubscriptionStatus();

                    // 添加事件监听器
                    document.getElementById('subscribe-button').addEventListener('click', subscribeUser);
                    document.getElementById('unsubscribe-button').addEventListener('click', unsubscribeUser);
                    document.getElementById('test-notification-button').addEventListener('click', sendTestNotification);
                })
                .catch(error => {
                    console.error('服务工作线程注册失败:', error);
                    showStatus('您的浏览器不支持推送通知', 'warning');
                });
        } else {
            showStatus('您的浏览器不支持推送通知', 'warning');
        }
    });
</script>
