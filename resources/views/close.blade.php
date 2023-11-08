<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success</title>
</head>

<body>
    <p>You can close this window now.</p>
    <p>您现在可以关闭此窗口了。</p>
    <script>
        if (navigator.userAgent.indexOf('Firefox') !== -1 || navigator.userAgent.indexOf('Chrome') !== -1) {
            window.location.href = 'about:blank'
            window.close()
        } else {
            window.opener = null
            window.open('', '_self')
            window.close()
        }
    </script>
</body>

</html>