import * as faceapi from "face-api.js";

async function start(video, callback, clip) {
    Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
        // faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
        // faceapi.nets.faceRecognitionNet.loadFromUri('./models'),
        // faceapi.nets.faceExpressionNet.loadFromUri('./models'),
    ]).then(() => {
        startVideo(video, callback, clip)
    })
}

function startVideo(video, callback, clip) {
    navigator.mediaDevices.getUserMedia({ video: {} })
        .then(stream => {
            video.srcObject = stream
            console.log("摄像头已成功开启")

            video.addEventListener("play", () => {
                handlePlayEvent(video, callback, clip)
            })
        })
        .catch(err => {
            console.error("无法访问摄像头:", err)
            alert("无法访问摄像头，请确保摄像头未被其他应用占用，并且页面有相应的权限!")
        })

}



function stopVideo(video) {
    removeListeners(video)

    try {
        const stream = video.srcObject
        const tracks = stream.getTracks()

        tracks.forEach(function (track) {
            track.stop()
        })

        video.srcObject = null
        console.log("摄像头已关闭")
    } catch (e) {
        // console.error(e)
        // console.log("摄像头关闭失败。")
    }
}

function removeListeners(video) {
    video.removeEventListener('play', handlePlayEvent)
}

function handlePlayEvent(video, callback, clip) {
    let stopped = false

    setInterval(async () => {
        const detections = await faceapi
            .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
        // .withFaceLandmarks()
        // .withFaceExpressions()

        if (detections.length > 0) {
            if (stopped) {
                return
            }

            stopped = true

            let image = getImage(video)

            clearInterval(this)

            stopVideo(video)

            if (clip) {
                clipFace(image, detections, function(src) {
                    if (callback) {
                        callback(src)
                    }
                })
            }

            if (callback) {
                callback(image.src)
            }



        }
    }, 100)
}

function getImage(video) {
    const canvas = document.createElement('canvas')
    const context = canvas.getContext('2d')
    canvas.width = video.videoWidth
    canvas.height = video.videoHeight
    context.drawImage(video, 0, 0, video.videoWidth, video.videoHeight)

    const img = new Image()
    img.src = canvas.toDataURL('image/jpeg', 0.5)

    return img
}

function clipFace(image, faces, callback) {
    const box = {
        bottom: -Infinity,
        left: Infinity,
        right: -Infinity,
        top: Infinity,

        get height() {
            return this.bottom - this.top
        },

        get width() {
            return this.right - this.left
        },
    }

    for (const face of faces) {
        box.bottom = Math.max(box.bottom, face.box.bottom)
        box.left = Math.min(box.left, face.box.left)
        box.right = Math.max(box.right, face.box.right)
        box.top = Math.min(box.top, face.box.top)
    }

    const canvas = document.createElement('canvas')
    const context = canvas.getContext('2d')

    image.onload = () => {
        canvas.height = box.height
        canvas.width = box.width


        context.drawImage(
            image,
            box.left,
            box.top,
            box.width ,
            box.height,
            0,
            0,
            canvas.width,
            canvas.height
        )

        const link = document.createElement('a')
        link.href = canvas.toDataURL('image/png', 0.6)

        callback(link.href)
        // 转换为 base64
        // console.log()
    }

}


window.face_capture = {
    start,
    stopVideo,
    removeListeners,
    handlePlayEvent,
    getImage,
}
