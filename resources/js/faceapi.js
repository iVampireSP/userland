import * as faceapi from "face-api.js";

const imageType = "image/jpeg";

// 定义动作列表
const actions = [
    // { name: 'blink', text: '请眨眨眼', check: checkBlink },
    { name: "nod", text: "请点头", check: checkNod },
    { name: "shake", text: "请左右摇头", check: checkShake },
    { name: "mouth", text: "请张嘴", check: checkMouth },
];

let lastLandmarks = null;
let currentAction = null;
let actionHistory = [];
let actionStartTime = null;

async function start(video, callback, clip) {
    Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri("/models"),
        faceapi.nets.faceLandmark68Net.loadFromUri("/models"),
        faceapi.nets.faceExpressionNet.loadFromUri("/models"),
    ]).then(() => {
        startVideo(video, callback, clip);
    });
}

function startVideo(video, callback, clip) {
    navigator.mediaDevices
        .getUserMedia({ video: {} })
        .then((stream) => {
            video.srcObject = stream;
            console.log("摄像头已成功开启");

            video.addEventListener("play", () => {
                handlePlayEvent(video, callback, clip);
            });
        })
        .catch((err) => {
            console.error("无法访问摄像头:", err);
            alert(
                "无法访问摄像头，请确保摄像头未被其他应用占用，并且页面有相应的权限!"
            );
        });
}

function stopVideo(video) {
    removeListeners(video);

    try {
        const stream = video.srcObject;
        const tracks = stream.getTracks();

        tracks.forEach(function (track) {
            track.stop();
        });

        video.srcObject = null;
        console.log("摄像头已关闭");
    } catch (e) {
        // console.error(e)
        // console.log("摄像头关闭失败。")
    }
}

function removeListeners(video) {
    video.removeEventListener("play", handlePlayEvent);
}

function handlePlayEvent(video, callback, clip) {
    let stopped = false;
    let canvas = null;
    let lastActionCheck = Date.now();
    let actionStartTime = Date.now();
    let consecutiveDetections = 0; // 连续检测到动作的次数
    actionHistory = []; // 重置动作历史
    currentAction = getRandomAction();

    // 更新动作提示
    updateActionPrompt(currentAction.text);
    console.log("开始新的动作检测:", currentAction.name);

    // 创建canvas用于显示标记点
    canvas = faceapi.createCanvasFromMedia(video);
    const videoContainer = video.parentElement;
    canvas.style.position = "absolute";
    canvas.style.top = "0";
    canvas.style.left = "0";
    canvas.style.width = "100%";
    canvas.style.height = "100%";
    videoContainer.appendChild(canvas);

    async function detectFace() {
        console.log("开始检测");
        if (stopped || !canvas) {
            console.log("停止检测");
            return;
        }

        try {
            const detections = await faceapi
                .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceExpressions();

            console.log("检测到人脸:", detections);

            if (detections.length > 0) {
                console.log("检测到人脸:", detections[0]);
                const detection = detections[0];
                const landmarks = detection.landmarks;

                // 绘制特征点
                const displaySize = {
                    width: video.videoWidth,
                    height: video.videoHeight,
                };
                const resizedDetections = faceapi.resizeResults(
                    detections,
                    displaySize
                );
                canvas
                    .getContext("2d")
                    .clearRect(0, 0, canvas.width, canvas.height);
                faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);

                // 检查当前动作
                const actionResult = currentAction.check(
                    landmarks,
                    detection.expressions
                );
                console.log("当前动作检测结果:", {
                    action: currentAction.name,
                    result: actionResult,
                    expressions: detection.expressions,
                });

                if (actionResult) {
                    consecutiveDetections++;
                    console.log("动作检测成功次数:", consecutiveDetections);

                    // 需要连续3次检测到才算真正完成动作
                    if (consecutiveDetections >= 3) {
                        console.log("动作完成:", currentAction.name);
                        actionHistory.push(currentAction.name);
                        consecutiveDetections = 0; // 重置计数器

                        // 如果完成了所有动作
                        if (actionHistory.length >= 2) {
                            console.log("所有动作完成");
                            stopped = true;
                            let image = getImage(video);
                            canvas.remove();
                            stopVideo(video);

                            if (clip) {
                                clipFace(image, detections, function (src) {
                                    if (callback) {
                                        callback(src);
                                    }
                                });
                            } else if (callback) {
                                callback(image.src);
                            }
                        } else {
                            // 获取下一个动作
                            currentAction = getRandomAction();
                            actionStartTime = Date.now();
                            console.log(
                                "切换到下一个动作:",
                                currentAction.name
                            );
                            updateActionPrompt(currentAction.text);
                        }
                    }
                } else {
                    consecutiveDetections = 0; // 重置连续检测计数
                }

                lastLandmarks = landmarks;
            } else {
                console.log("未检测到人脸");
                consecutiveDetections = 0; // 重置连续检测计数
            }
        } catch (error) {
            console.error("人脸检测错误:", error);
            consecutiveDetections = 0; // 重置连续检测计数
        }

        if (!stopped) {
            requestAnimationFrame(detectFace);
        }
    }

    // 设置canvas尺寸并开始检测
    function initDetection() {
        console.log("视频尺寸:", {
            width: video.videoWidth,
            height: video.videoHeight,
        });
        if (video.videoWidth === 0 || video.videoHeight === 0) {
            console.log("视频尺寸还未就绪，等待中...");
            setTimeout(initDetection, 100);
            return;
        }

        const displaySize = {
            width: video.videoWidth,
            height: video.videoHeight,
        };
        faceapi.matchDimensions(canvas, displaySize);
        console.log("Canvas 已创建和设置:", displaySize);

        // 开始检测
        detectFace();
    }

    // 开始初始化检测
    initDetection();
}

function getRandomAction() {
    const availableActions = actions.filter(
        (action) => !actionHistory.includes(action.name)
    );
    return availableActions[
        Math.floor(Math.random() * availableActions.length)
    ];
}

function updateActionPrompt(text) {
    const prompt = document.getElementById("action-prompt");
    if (prompt) {
        prompt.textContent = text;
    }
}

function checkBlink(landmarks, expressions) {
    const leftEye = landmarks.getLeftEye();
    const rightEye = landmarks.getRightEye();

    // 计算眼睛的宽度作为参考
    const leftEyeWidth = getDistance(leftEye[0], leftEye[3]);
    const rightEyeWidth = getDistance(rightEye[0], rightEye[3]);

    // 计算眼睛的高度
    const leftEyeHeight = getDistance(leftEye[1], leftEye[5]);
    const rightEyeHeight = getDistance(rightEye[1], rightEye[5]);

    // 计算眼睛高宽比
    const leftRatio = leftEyeHeight / leftEyeWidth;
    const rightRatio = rightEyeHeight / rightEyeWidth;

    console.log("眨眼检测详细信息:", {
        leftEye: {
            width: leftEyeWidth,
            height: leftEyeHeight,
            ratio: leftRatio,
            points: leftEye.map((p) => ({ x: p.x, y: p.y })),
        },
        rightEye: {
            width: rightEyeWidth,
            height: rightEyeHeight,
            ratio: rightRatio,
            points: rightEye.map((p) => ({ x: p.x, y: p.y })),
        },
    });

    // 使用高宽比来判断眨眼
    return leftRatio < 0.2 && rightRatio < 0.2;
}

function checkNod(landmarks) {
    if (!lastLandmarks) return false;

    // 获取多个特征点来提高准确性
    const nose = landmarks.getNose();
    const jaw = landmarks.getJawOutline();

    // 计算多个点的垂直移动
    const movements = {
        nose: Math.abs(nose[0].y - lastLandmarks.getNose()[0].y),
        noseTip: Math.abs(nose[3].y - lastLandmarks.getNose()[3].y),
        chin: Math.abs(jaw[8].y - lastLandmarks.getJawOutline()[8].y),
    };

    // 计算平均移动距离
    const avgMovement =
        (movements.nose + movements.noseTip + movements.chin) / 3;

    console.log("点头检测详细信息:", {
        movements,
        avgMovement,
        currentPoints: {
            nose: nose[0].y,
            noseTip: nose[3].y,
            chin: jaw[8].y,
        },
        lastPoints: {
            nose: lastLandmarks.getNose()[0].y,
            noseTip: lastLandmarks.getNose()[3].y,
            chin: lastLandmarks.getJawOutline()[8].y,
        },
    });

    return avgMovement > 4;
}

function checkShake(landmarks) {
    if (!lastLandmarks) return false;

    // 获取多个特征点
    const nose = landmarks.getNose();
    const leftEye = landmarks.getLeftEye()[0];
    const rightEye = landmarks.getRightEye()[3];

    // 计算多个点的水平移动
    const movements = {
        nose: Math.abs(nose[0].x - lastLandmarks.getNose()[0].x),
        leftEye: Math.abs(leftEye.x - lastLandmarks.getLeftEye()[0].x),
        rightEye: Math.abs(rightEye.x - lastLandmarks.getRightEye()[3].x),
    };

    // 计算平均移动距离
    const avgMovement =
        (movements.nose + movements.leftEye + movements.rightEye) / 3;

    console.log("摇头检测详细信息:", {
        movements,
        avgMovement,
        currentPoints: {
            nose: nose[0].x,
            leftEye: leftEye.x,
            rightEye: rightEye.x,
        },
        lastPoints: {
            nose: lastLandmarks.getNose()[0].x,
            leftEye: lastLandmarks.getLeftEye()[0].x,
            rightEye: lastLandmarks.getRightEye()[3].x,
        },
    });

    return avgMovement > 4;
}

function checkMouth(landmarks, expressions) {
    const mouth = landmarks.getMouth();

    // 计算嘴巴的开合程度
    const topLip = mouth[14]; // 上唇中点
    const bottomLip = mouth[18]; // 下唇中点
    const mouthOpen = getDistance(topLip, bottomLip);

    // 计算嘴巴的宽度作为参考
    const mouthWidth = getDistance(mouth[0], mouth[6]);

    // 计算开合比例
    const mouthRatio = mouthOpen / mouthWidth;

    console.log("张嘴检测详细信息:", {
        expressions,
        mouth: {
            width: mouthWidth,
            openDistance: mouthOpen,
            ratio: mouthRatio,
            points: mouth.map((p) => ({ x: p.x, y: p.y })),
        },
        topLip: { x: topLip.x, y: topLip.y },
        bottomLip: { x: bottomLip.x, y: bottomLip.y },
    });

    // 使用开合比例和表情综合判断
    return mouthRatio > 0.5 || expressions.surprised > 0.3;
}

function getDistance(point1, point2) {
    return Math.sqrt(
        Math.pow(point1.x - point2.x, 2) + Math.pow(point1.y - point2.y, 2)
    );
}

function getImage(video) {
    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, video.videoWidth, video.videoHeight);

    const img = new Image();
    img.src = canvas.toDataURL(imageType, 0.5);

    return img;
}

function clipFace(image, faces, callback) {
    const box = {
        bottom: -Infinity,
        left: Infinity,
        right: -Infinity,
        top: Infinity,

        get height() {
            return this.bottom - this.top;
        },

        get width() {
            return this.right - this.left;
        },
    };

    for (const face of faces) {
        box.bottom = Math.max(box.bottom, face.box.bottom);
        box.left = Math.min(box.left, face.box.left);
        box.right = Math.max(box.right, face.box.right);
        box.top = Math.min(box.top, face.box.top);
    }

    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");

    image.onload = () => {
        canvas.height = box.height;
        canvas.width = box.width;

        context.drawImage(
            image,
            box.left,
            box.top,
            box.width,
            box.height,
            0,
            0,
            canvas.width,
            canvas.height
        );

        const link = document.createElement("a");
        link.href = canvas.toDataURL(imageType, 0.6);

        callback(link.href);
        // 转换为 base64
        // console.log()
    };
}

window.face_capture = {
    start,
    stopVideo,
    removeListeners,
    handlePlayEvent,
    getImage,
};
