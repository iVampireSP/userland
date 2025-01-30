import * as faceapi from "face-api.js";

const imageType = "image/jpeg";

// 检测光线变化的函数
function checkLight(landmarks, expressions, video, lastFrameColors, colorCanvas) {
    if (!video || !colorCanvas) {
        console.error('checkLight: 缺少必要参数', { video: !!video, colorCanvas: !!colorCanvas });
        return {
            result: false,
            colors: null
        };
    }

    try {
        // 分析视频帧的颜色变化
        const colorCtx = colorCanvas.getContext('2d');

        // 确保视频已经准备好
        if (video.videoWidth === 0 || video.videoHeight === 0) {
            console.log('视频尺寸未就绪');
            return {
                result: false,
                colors: null
            };
        }

        // 绘制视频帧到canvas
        colorCtx.drawImage(video, 0, 0, colorCanvas.width, colorCanvas.height);
        const imageData = colorCtx.getImageData(0, 0, colorCanvas.width, colorCanvas.height);
        const currentColors = getAverageColors(imageData);

        console.log('当前帧颜色:', currentColors);

        if (lastFrameColors) {
            const colorDiff = Math.abs(currentColors.r - lastFrameColors.r) +
                            Math.abs(currentColors.g - lastFrameColors.g) +
                            Math.abs(currentColors.b - lastFrameColors.b);

            console.log('颜色变化检测:', {
                currentColors,
                lastFrameColors,
                difference: colorDiff
            });

            return {
                result: colorDiff > 50,  // 提高阈值，使检测更明显
                colors: currentColors
            };
        }

        return {
            result: false,
            colors: currentColors
        };
    } catch (error) {
        console.error('颜色检测错误:', error);
        return {
            result: false,
            colors: null
        };
    }
}

function getAverageColors(imageData) {
    const data = imageData.data;
    let r = 0, g = 0, b = 0;
    const total = data.length / 4;

    for (let i = 0; i < data.length; i += 4) {
        r += data[i];
        g += data[i + 1];
        b += data[i + 2];
    }

    return {
        r: Math.round(r / total),
        g: Math.round(g / total),
        b: Math.round(b / total)
    };
}

// 定义动作列表
const actions = [
    { name: "nod", text: "请点头", check: checkNod },
    { name: "shake", text: "请左右摇头", check: checkShake },
    { name: "mouth", text: "请张嘴", check: checkMouth }
];

// 定义炫光颜色序列
const flashColors = [
    { name: 'red', class: 'flash-red' },
    { name: 'green', class: 'flash-green' },
    { name: 'blue', class: 'flash-blue' },
    { name: 'yellow', class: 'flash-yellow' },
    { name: 'purple', class: 'flash-purple' }
];

let lastLandmarks = null;
let currentAction = null;
let actionHistory = [];
let actionStartTime = null;
let originalBodyColor = null;
let isFlashing = false;

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
    // 尝试获取最佳分辨率
    const constraints = {
        video: {
            width: { ideal: 1280 },
            height: { ideal: 720 },
            facingMode: "user"  // 使用前置摄像头
        }
    };

    navigator.mediaDevices
        .getUserMedia(constraints)
        .then((stream) => {
            video.srcObject = stream;
            console.log("摄像头已成功开启");

            // 获取实际的视频轨道设置
            const videoTrack = stream.getVideoTracks()[0];
            const settings = videoTrack.getSettings();
            console.log("摄像头实际设置:", settings);

            const playHandler = () => {
                handlePlayEvent(video, callback, clip);
                video.removeEventListener("play", playHandler);  // 移除事件监听器，避免重复调用
            };
            video.addEventListener("play", playHandler);
        })
        .catch((err) => {
            console.error("无法以最佳分辨率访问摄像头，尝试默认设置:", err);
            // 如果失败，尝试使用默认设置
            navigator.mediaDevices
                .getUserMedia({ video: true })
                .then((stream) => {
                    video.srcObject = stream;
                    console.log("摄像头已使用默认设置开启");

                    const videoTrack = stream.getVideoTracks()[0];
                    const settings = videoTrack.getSettings();
                    console.log("摄像头默认设置:", settings);

                    const playHandler = () => {
                        handlePlayEvent(video, callback, clip);
                        video.removeEventListener("play", playHandler);  // 移除事件监听器，避免重复调用
                    };
                    video.addEventListener("play", playHandler);
                })
                .catch((err) => {
                    console.error("无法访问摄像头:", err);
                    alert("无法访问摄像头，请确保摄像头未被其他应用占用，并且页面有相应的权限!");
                });
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
    const detectFaceRef = { stopped: false };  // 创建一个引用对象来控制检测状态
    let canvas = null;
    let lastActionCheck = Date.now();
    let actionStartTime = Date.now();
    let consecutiveDetections = 0;
    let lastBrightness = null;
    let flashSuccessCount = 0;
    actionHistory = [];
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
        if (detectFaceRef.stopped || !canvas) return;

        try {
            const detections = await faceapi
                .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceExpressions();

            if (detections.length > 0) {
                const detection = detections[0];
                const landmarks = detection.landmarks;

                // 绘制特征点
                const displaySize = {
                    width: video.videoWidth,
                    height: video.videoHeight,
                };
                const resizedDetections = faceapi.resizeResults(detections, displaySize);
                canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);
                // faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);

                if (!isFlashing) {
                    // 检查当前动作
                    const actionResult = currentAction.check(landmarks, detection.expressions);

                    if (actionResult) {
                        consecutiveDetections++;
                        console.log("动作检测成功次数:", consecutiveDetections);

                        if (consecutiveDetections >= 3) {
                            console.log("动作完成:", currentAction.name);
                            actionHistory.push(currentAction.name);
                            consecutiveDetections = 0;

                            if (actionHistory.length >= 3) {  // 完成所有动作
                                console.log("所有动作完成，开始炫光检测");
                                startFlashDetection(video, landmarks, detection, callback, clip, canvas, detectFaceRef);
                            } else {
                                currentAction = getRandomAction();
                                actionStartTime = Date.now();
                                console.log("切换到下一个动作:", currentAction.name);
                                updateActionPrompt(currentAction.text);
                            }
                        }
                    } else {
                        consecutiveDetections = 0;
                    }
                } else {
                    // 在炫光模式下检测颜色变化
                    const currentBrightness = calculateFaceBrightness(landmarks);

                    if (lastBrightness !== null) {
                        const brightnessDiff = Math.abs(currentBrightness - lastBrightness);
                        console.log('亮度变化:', {
                            current: currentBrightness,
                            last: lastBrightness,
                            difference: brightnessDiff
                        });

                        if (brightnessDiff > 10) {  // 检测到明显的亮度变化
                            flashSuccessCount++;
                            console.log('检测到亮度变化，成功次数:', flashSuccessCount);

                            if (flashSuccessCount >= 3) {  // 需要检测到3次明显的亮度变化
                                console.log('炫光检测完成');
                                // 完成所有检测
                                detectFaceRef.stopped = true;  // 使用引用对象来停止检测
                                let image = getImage(video);
                                canvas.remove();
                                stopVideo(video);

                                // 恢复原始背景色
                                document.body.classList.remove(...flashColors.map(c => c.class));
                                document.body.style.backgroundColor = originalBodyColor;

                                if (clip) {
                                    clipFace(image, detections, function(src) {
                                        if (callback) {
                                            callback(src);
                                        }
                                    });
                                } else if (callback) {
                                    callback(image.src);
                                }
                            }
                        }
                    }
                    lastBrightness = currentBrightness;
                }

                lastLandmarks = landmarks;
            } else {
                console.log("未检测到人脸");
                consecutiveDetections = 0;
            }
        } catch (error) {
            console.error("人脸检测错误:", error);
            consecutiveDetections = 0;
        }

        if (!detectFaceRef.stopped) {
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

function startFlashDetection(video, landmarks, detection, callback, clip, canvas, detectFaceRef) {
    isFlashing = true;
    document.getElementById('light-prompt').classList.remove('d-none');
    document.getElementById('action-prompt').classList.add('d-none');

    // 保存原始背景色
    originalBodyColor = document.body.style.backgroundColor;

    // 开始颜色序列
    let colorIndex = 0;
    const flashInterval = setInterval(() => {
        if (colorIndex >= flashColors.length) {
            clearInterval(flashInterval);
            document.body.style.backgroundColor = originalBodyColor;
            isFlashing = false;

            // 完成所有检测
            console.log("炫光检测完成");
            detectFaceRef.stopped = true;  // 使用传入的引用来停止检测
            let image = getImage(video);
            canvas.remove();
            stopVideo(video);

            if (clip) {
                clipFace(image, detection, function (src) {
                    if (callback) {
                        callback(src);
                    }
                });
            } else if (callback) {
                callback(image.src);
            }
            return;
        }

        // 应用新颜色
        document.body.classList.remove(...flashColors.map(c => c.class));
        document.body.classList.add(flashColors[colorIndex].class);
        console.log('切换炫光颜色:', flashColors[colorIndex].name);

        colorIndex++;
    }, 1000);  // 每秒切换一次颜色
}

function calculateFaceBrightness(landmarks) {
    // 使用关键点的 y 值来估算人脸亮度
    const points = landmarks.positions;
    let totalY = 0;

    points.forEach(point => {
        totalY += point.y;
    });

    return totalY / points.length;
}

window.face_capture = {
    start,
    stopVideo,
    removeListeners,
    handlePlayEvent,
    getImage,
};
