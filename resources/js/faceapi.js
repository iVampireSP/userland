import * as faceapi from "face-api.js";

const imageType = "image/jpeg";

// 检测光线变化的函数
function checkLight(landmarks, video, colorCanvas) {
    if (!video || !colorCanvas) {
        console.error('checkLight: 缺少必要参数', { video: !!video, colorCanvas: !!colorCanvas });
        return {
            result: false,
            eyeColors: null
        };
    }

    try {
        const colorCtx = colorCanvas.getContext('2d');

        // 确保视频已经准备好
        if (video.videoWidth === 0 || video.videoHeight === 0) {
            console.log('视频尺寸未就绪');
            return {
                result: false,
                eyeColors: null
            };
        }

        // 绘制整个视频帧到canvas
        colorCtx.drawImage(video, 0, 0, colorCanvas.width, colorCanvas.height);

        // 如果有人脸关键点，分析眼睛区域的颜色
        let eyeColors = null;
        if (landmarks) {
            // 获取左右眼区域
            const leftEye = landmarks.getLeftEye();
            const rightEye = landmarks.getRightEye();

            // 计算眼睛区域的边界框
            const leftEyeBounds = {
                left: Math.min(...leftEye.map(p => p._x)),
                right: Math.max(...leftEye.map(p => p._x)),
                top: Math.min(...leftEye.map(p => p._y)),
                bottom: Math.max(...leftEye.map(p => p._y))
            };

            const rightEyeBounds = {
                left: Math.min(...rightEye.map(p => p._x)),
                right: Math.max(...rightEye.map(p => p._x)),
                top: Math.min(...rightEye.map(p => p._y)),
                bottom: Math.max(...rightEye.map(p => p._y))
            };

            // 扩大眼睛区域以包含更多周边区域
            const padding = {
                x: (leftEyeBounds.right - leftEyeBounds.left) * 0.5,
                y: (leftEyeBounds.bottom - leftEyeBounds.top) * 0.5
            };

            // 扩展左眼区域
            leftEyeBounds.left = Math.max(0, leftEyeBounds.left - padding.x);
            leftEyeBounds.right = Math.min(colorCanvas.width, leftEyeBounds.right + padding.x);
            leftEyeBounds.top = Math.max(0, leftEyeBounds.top - padding.y);
            leftEyeBounds.bottom = Math.min(colorCanvas.height, leftEyeBounds.bottom + padding.y);

            // 扩展右眼区域
            rightEyeBounds.left = Math.max(0, rightEyeBounds.left - padding.x);
            rightEyeBounds.right = Math.min(colorCanvas.width, rightEyeBounds.right + padding.x);
            rightEyeBounds.top = Math.max(0, rightEyeBounds.top - padding.y);
            rightEyeBounds.bottom = Math.min(colorCanvas.height, rightEyeBounds.bottom + padding.y);

            // 获取左眼区域的图像数据
            const leftEyeImageData = colorCtx.getImageData(
                leftEyeBounds.left,
                leftEyeBounds.top,
                leftEyeBounds.right - leftEyeBounds.left,
                leftEyeBounds.bottom - leftEyeBounds.top
            );
            const leftEyeColors = getAverageColors(leftEyeImageData);

            // 获取右眼区域的图像数据
            const rightEyeImageData = colorCtx.getImageData(
                rightEyeBounds.left,
                rightEyeBounds.top,
                rightEyeBounds.right - rightEyeBounds.left,
                rightEyeBounds.bottom - rightEyeBounds.top
            );
            const rightEyeColors = getAverageColors(rightEyeImageData);

            // 计算两只眼睛的平均颜色
            eyeColors = {
                r: Math.round((leftEyeColors.r + rightEyeColors.r) / 2),
                g: Math.round((leftEyeColors.g + rightEyeColors.g) / 2),
                b: Math.round((leftEyeColors.b + rightEyeColors.b) / 2)
            };

            // DEBUG: 在canvas上标记眼睛区域
            colorCtx.strokeStyle = 'yellow';
            colorCtx.lineWidth = 2;
            colorCtx.strokeRect(leftEyeBounds.left, leftEyeBounds.top,
                leftEyeBounds.right - leftEyeBounds.left,
                leftEyeBounds.bottom - leftEyeBounds.top);
            colorCtx.strokeRect(rightEyeBounds.left, rightEyeBounds.top,
                rightEyeBounds.right - rightEyeBounds.left,
                rightEyeBounds.bottom - rightEyeBounds.top);
        }

        return {
            result: true,
            eyeColors
        };
    } catch (error) {
        console.error('颜色检测错误:', error);
        return {
            result: false,
            eyeColors: null
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
let verificationSession = null;
let currentActionIndex = 0;
let flashImages = [];
let flashData = [];

// 添加人脸姿态稳定性检测相关变量
let lastStableTime = null;
const STABILITY_THRESHOLD = 2; // 姿态变化阈值
const REQUIRED_STABLE_TIME = 1000; // 需要保持稳定的时间（毫秒）

// 添加动作过程记录相关变量
let actionProcessImages = [];
let lastActionAmplitude = 0;
const AMPLITUDE_THRESHOLD = 0.2; // 动作幅度变化阈值
const MAX_PROCESS_IMAGES = 5; // 每个动作最多采集的图片数量

// 检查人脸姿态是否稳定
function checkFaceStability(landmarks) {
    if (!lastLandmarks) {
        return false;
    }

    // 获取关键点进行比较
    const nose = landmarks.getNose();
    const jaw = landmarks.getJawOutline();
    const leftEye = landmarks.getLeftEye();
    const rightEye = landmarks.getRightEye();

    // 计算关键点的移动
    const movements = {
        // 鼻子移动（水平和垂直）
        noseX: Math.abs(nose[0]._x - lastLandmarks.getNose()[0]._x),
        noseY: Math.abs(nose[0]._y - lastLandmarks.getNose()[0]._y),
        // 下巴中点移动
        chinY: Math.abs(jaw[8]._y - lastLandmarks.getJawOutline()[8]._y),
        // 眼睛移动
        leftEyeX: Math.abs(leftEye[0]._x - lastLandmarks.getLeftEye()[0]._x),
        rightEyeX: Math.abs(rightEye[0]._x - lastLandmarks.getRightEye()[0]._x),
    };

    // 计算总体移动量
    const totalMovement = Object.values(movements).reduce((sum, movement) => sum + movement, 0);
    const avgMovement = totalMovement / Object.keys(movements).length;

    // 检查是否稳定
    const isStable = avgMovement < STABILITY_THRESHOLD;

    // 更新稳定时间
    if (isStable) {
        if (!lastStableTime) {
            lastStableTime = Date.now();
        }
        // 检查是否已经保持足够长的稳定时间
        return Date.now() - lastStableTime >= REQUIRED_STABLE_TIME;
    } else {
        lastStableTime = null;
        return false;
    }
}

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

// 修改 waitForFaceDetection 函数
function waitForFaceDetection(video, canvas) {
    return new Promise((resolve, reject) => {
        let isWaiting = true;
        let noFaceTimeout = null;
        let hasShownStabilityPrompt = false;

        async function detectFace() {
            if (!isWaiting) return;

            try {
                const detections = await faceapi
                    .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceExpressions();

                // 清除画布
                canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);

                if (detections.length === 1) {
                    // 检测到一个人脸
                    const detection = detections[0];
                    const landmarks = detection.landmarks;

                    const displaySize = {
                        width: video.videoWidth,
                        height: video.videoHeight
                    };
                    faceapi.matchDimensions(canvas, displaySize);
                    const resizedDetections = faceapi.resizeResults(detections, displaySize);
                    faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);

                    // 检查人脸姿态是否稳定
                    if (checkFaceStability(landmarks)) {
                        // 清除超时计时器
                        if (noFaceTimeout) {
                            clearTimeout(noFaceTimeout);
                            noFaceTimeout = null;
                        }

                        isWaiting = false;
                        resolve(detection);
                        return;
                    } else {
                        if (!hasShownStabilityPrompt) {
                            updateActionPrompt('请保持正视摄像头，不要晃动');
                            hasShownStabilityPrompt = true;
                        }
                        lastLandmarks = landmarks;
                    }
                } else if (detections.length > 1) {
                    updateActionPrompt('检测到多个人脸，请确保画面中只有一个人脸');
                    hasShownStabilityPrompt = false;
                } else {
                    updateActionPrompt('未检测到人脸，请将脸部对准摄像头');
                    hasShownStabilityPrompt = false;

                    if (!noFaceTimeout) {
                        noFaceTimeout = setTimeout(() => {
                            updateActionPrompt('长时间未检测到人脸，请确保光线充足且正对摄像头');
                            noFaceTimeout = null;
                        }, 10000);
                    }
                }

                // 继续检测
                requestAnimationFrame(detectFace);
            } catch (error) {
                console.error('人脸检测出错:', error);
                updateActionPrompt('人脸检测出错，请刷新页面重试');
                isWaiting = false;
                reject(error);
            }
        }

        detectFace();

        // 添加清理函数
        return () => {
            isWaiting = false;
            if (noFaceTimeout) {
                clearTimeout(noFaceTimeout);
            }
        };
    });
}

async function handlePlayEvent(video, callback, clip) {
    try {
        // 开始新的验证会话
        await startVerification();

        const verificationStatus = document.getElementById('verification-status');
        verificationStatus.classList.remove('d-none');
        updateActionPrompt('请将脸部对准摄像头');

        // 创建canvas用于显示标记点（只创建一次）
        const canvas = faceapi.createCanvasFromMedia(video);
        const videoContainer = video.parentElement;
        canvas.style.position = "absolute";
        canvas.style.top = "0";
        canvas.style.left = "0";
        canvas.style.width = "100%";
        canvas.style.height = "100%";
        canvas.id = "face-canvas"; // 添加 id 以便识别
        videoContainer.appendChild(canvas);

        // 等待检测到人脸后再开始验证流程
        try {
            await waitForFaceDetection(video, canvas);
        } catch (error) {
            console.error('人脸检测失败:', error);
            // 不要立即显示错误，而是继续等待
            return;
        }

        let nextAction = null;
        while (nextAction === null) {
            // 获取初始人脸图像并提交
            const initialImage = getImage(video);
            nextAction = await submitInitialFace(initialImage.src);
            // 如果返回 null，表示需要重试
            if (nextAction === null) {
                await new Promise(resolve => setTimeout(resolve, 1000)); // 等待1秒后重试
            }
        }

        // 开始动作验证
        currentActionIndex = 0;
        currentAction = { name: nextAction, text: getActionText(nextAction) };
        updateActionPrompt(currentAction.text);

        // 开始人脸检测和动作验证流程，使用已创建的 canvas
        startActionDetection(video, canvas, callback, clip);

    } catch (error) {
        console.error('验证初始化失败:', error);
        updateActionPrompt('验证初始化失败，请刷新页面重试');
    }
}

// 修改 startActionDetection 函数
function startActionDetection(video, canvas, callback, clip) {
    let consecutiveDetections = 0;
    let lastDetectionTime = Date.now();
    let hasStartImage = false;
    let hasEndImage = false;

    async function detectFace() {
        try {
            const detections = await faceapi
                .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceExpressions();

            if (detections.length === 1) {
                const detection = detections[0];
                const landmarks = detection.landmarks;
                const expressions = detection.expressions;

                // 更新画面显示
                const displaySize = {
                    width: video.videoWidth,
                    height: video.videoHeight
                };
                const resizedDetections = faceapi.resizeResults(detections, displaySize);
                canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);

                if (!isFlashing) {
                    // 检查当前动作
                    const [actionResult, amplitude] = checkActionWithAmplitude(currentAction.name, landmarks, expressions);

                    if (actionResult) {
                        // 记录动作过程图片
                        if (!hasStartImage) {
                            // 采集动作开始前的图片
                            const startImage = getImage(video, detection);
                            actionProcessImages = [{
                                image: startImage.src,
                                amplitude: 0,
                                timestamp: Math.floor(Date.now() / 1000)
                            }];
                            hasStartImage = true;
                            console.log('采集动作开始图片');
                        } else if (
                            Math.abs(amplitude - lastActionAmplitude) > AMPLITUDE_THRESHOLD &&
                            actionProcessImages.length < MAX_PROCESS_IMAGES - 1
                        ) {
                            // 采集动作过程中的图片
                            const processImage = getImage(video, detection);
                            actionProcessImages.push({
                                image: processImage.src,
                                amplitude: amplitude,
                                timestamp: Math.floor(Date.now() / 1000)
                            });
                            console.log('采集动作过程图片，幅度:', amplitude);
                        }

                        lastActionAmplitude = amplitude;
                        consecutiveDetections++;
                        console.log("动作检测成功次数:", consecutiveDetections);

                        if (consecutiveDetections >= 3 && !hasEndImage) {
                            // 等待动作完成后的稳定状态
                            await new Promise(resolve => setTimeout(resolve, 500));
                            const endImage = getImage(video, detection);
                            actionProcessImages.push({
                                image: endImage.src,
                                amplitude: 0,
                                timestamp: Math.floor(Date.now() / 1000)
                            });
                            hasEndImage = true;
                            console.log('采集动作结束图片');

                            // 获取所有关键点
                            const allPoints = [
                                ...landmarks.getJawOutline(),
                                ...landmarks.getNose(),
                                ...landmarks.getLeftEye(),
                                ...landmarks.getRightEye(),
                                ...landmarks.getLeftEyeBrow(),
                                ...landmarks.getRightEyeBrow(),
                                ...landmarks.getMouth()
                            ];

                            // 转换关键点数据格式
                            const landmarkPositions = allPoints.map(point => ({
                                x: point._x,
                                y: point._y
                            }));

                            // 提交动作验证
                            const result = await handleActionComplete(video, {
                                landmarks: landmarkPositions,
                                expressions: expressions,
                                timestamp: Math.floor(Date.now() / 1000),
                                process_images: actionProcessImages
                            }, endImage);

                            // 重置状态
                            consecutiveDetections = 0;
                            hasStartImage = false;
                            hasEndImage = false;
                            actionProcessImages = [];
                            lastActionAmplitude = 0;

                            if (result && result.should_retry) {
                                console.log('重试当前动作:', currentAction.name);
                                requestAnimationFrame(detectFace);
                                return;
                            }

                            if (result && result.status === 'actions_completed') {
                                startFlashDetection(video);
                                return;
                            }
                        }
                    } else {
                        consecutiveDetections = 0;
                        hasStartImage = false;
                        hasEndImage = false;
                        actionProcessImages = [];
                        lastActionAmplitude = 0;
                    }
                }

                lastLandmarks = landmarks;
            } else if (detections.length > 1) {
                showError('检测到多个人脸，请确保画面中只有一个人脸');
                return;
            }

            // 继续检测
            if (!isFlashing) {
                requestAnimationFrame(detectFace);
            }
        } catch (error) {
            console.error('人脸检测错误:', error);
            showError('人脸检测出错，请重试');
            if (!isFlashing) {
                requestAnimationFrame(detectFace);
            }
        }
    }

    detectFace();
}

// 修改动作检测函数，返回动作幅度
function checkActionWithAmplitude(actionName, landmarks, expressions) {
    switch (actionName) {
        case 'nod':
            return checkNodWithAmplitude(landmarks);
        case 'shake':
            return checkShakeWithAmplitude(landmarks);
        case 'mouth':
            return checkMouthWithAmplitude(landmarks, expressions);
        default:
            return [false, 0];
    }
}

function checkNodWithAmplitude(landmarks) {
    if (!lastLandmarks) return [false, 0];

    const nose = landmarks.getNose();
    const jaw = landmarks.getJawOutline();

    const movements = {
        nose: Math.abs(nose[0]._y - lastLandmarks.getNose()[0]._y),
        noseTip: Math.abs(nose[3]._y - lastLandmarks.getNose()[3]._y),
        chin: Math.abs(jaw[8]._y - lastLandmarks.getJawOutline()[8]._y),
    };

    const avgMovement = (movements.nose + movements.noseTip + movements.chin) / 3;
    return [avgMovement > 4, avgMovement];
}

function checkShakeWithAmplitude(landmarks) {
    if (!lastLandmarks) return [false, 0];

    const nose = landmarks.getNose();
    const leftEye = landmarks.getLeftEye()[0];
    const rightEye = landmarks.getRightEye()[3];

    const movements = {
        nose: Math.abs(nose[0]._x - lastLandmarks.getNose()[0]._x),
        leftEye: Math.abs(leftEye._x - lastLandmarks.getLeftEye()[0]._x),
        rightEye: Math.abs(rightEye._x - lastLandmarks.getRightEye()[3]._x),
    };

    const avgMovement = (movements.nose + movements.leftEye + movements.rightEye) / 3;
    return [avgMovement > 4, avgMovement];
}

function checkMouthWithAmplitude(landmarks, expressions) {
    const mouth = landmarks.getMouth();
    const topLip = mouth[14];
    const bottomLip = mouth[18];

    const mouthOpen = getDistance(
        { x: topLip._x, y: topLip._y },
        { x: bottomLip._x, y: bottomLip._y }
    );

    const mouthWidth = getDistance(
        { x: mouth[0]._x, y: mouth[0]._y },
        { x: mouth[6]._x, y: mouth[6]._y }
    );

    const mouthRatio = mouthOpen / mouthWidth;
    const amplitude = Math.max(mouthRatio, expressions.surprised);

    return [mouthRatio > 0.5 || expressions.surprised > 0.3, amplitude];
}

function getActionText(actionName) {
    const actionTexts = {
        'nod': '请点头',
        'shake': '请左右摇头',
        'mouth': '请张嘴'
    };
    return actionTexts[actionName] || '未知动作';
}

// 添加检测单个人脸的函数
async function detectSingleFace(video) {
    try {
        const detections = await faceapi
            .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceExpressions();

        if (detections.length === 0) {
            updateActionPrompt('未检测到人脸，请将脸部对准摄像头');
            return null;
        } else if (detections.length > 1) {
            updateActionPrompt('检测到多个人脸，请确保画面中只有一个人脸');
            return null;
        }

        return detections[0];
    } catch (error) {
        console.error('人脸检测出错:', error);
        return null;
    }
}

// 修改 submitInitialFace 函数
async function submitInitialFace(imageData) {
    try {
        // 先检测人脸
        const detection = await detectSingleFace(video);
        if (!detection) {
            return null; // 如果没有检测到合适的人脸，直接返回 null 进行重试
        }

        // 使用检测结果获取裁剪后的图像
        const image = getImage(video, detection);

        const response = await fetch('/face/verification/initial', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                session_id: verificationSession.session_id,
                image: image.src
            })
        });

        // 检查状态码
        if (response.status === 429) {
            stopVideo(video);
            showError('验证失败次数过多，请稍后再试');
            return null;
        }

        const result = await response.json();

        if (!response.ok) {
            if (result.should_retry) {
                const retryCount = result.retries_left ?? 0;
                const errorMessage = result.error || '验证失败';
                console.log(`初始人脸验证失败，剩余重试次数: ${retryCount}`);
                updateActionPrompt(`验证失败: ${errorMessage}，剩余重试次数: ${retryCount}，请等待3秒后重试`);

                // 等待3秒后才允许重试
                await new Promise(resolve => setTimeout(resolve, 3000));

                return null;
            }
            throw new Error(result.error || '初始人脸验证失败');
        }

        return result.next_action;
    } catch (error) {
        console.error('提交初始人脸失败:', error);
        throw error;
    }
}

// 修改 handleActionComplete 函数
async function handleActionComplete(video, actionData, image) {
    try {
        // 先检测人脸
        const detection = await detectSingleFace(video);
        if (!detection) {
            return {
                should_retry: true,
                current_action: currentAction.name,
                error: '未检测到有效人脸',
                retries_left: null // 这种情况不消耗重试次数
            };
        }

        const result = await submitActionVerification(
            currentActionIndex,
            image.src,
            actionData
        );

        // 处理重试情况
        if (result.should_retry) {
            const retryCount = result.retries_left ?? 0;
            const errorMessage = result.error || '验证失败';
            console.log(`动作验证失败，剩余重试次数: ${retryCount}`);
            updateActionPrompt(`验证失败: ${errorMessage}，剩余重试次数: ${retryCount}，请等待3秒后重试`);

            // 等待3秒后才允许重试
            await new Promise(resolve => setTimeout(resolve, 3000));

            // 返回重试信息，让调用者继续当前动作的检测
            return {
                should_retry: true,
                current_action: currentAction.name
            };
        }

        if (result.status === 'actions_completed') {
            // 所有动作完成，开始炫光检测
            flashImages = [];
            flashData = [];
            startFlashDetection(video);
            return result;
        } else {
            // 继续下一个动作
            currentActionIndex++;
            currentAction = { name: result.next_action, text: getActionText(result.next_action) };
            updateActionPrompt(currentAction.text);
            return result;
        }
    } catch (error) {
        console.error('动作验证失败:', error);
        // 检查是否是重试次数过多
        if (error.status === 429) {
            stopVideo(video);
            showError('验证失败次数过多，请稍后再试');
            return {
                should_retry: false,
                error: '验证失败次数过多'
            };
        }
        showError('动作验证失败，请重试');
        return {
            should_retry: true,
            current_action: currentAction.name,
            error: error.message
        };
    }
}

// 修改 handleFlashComplete 函数
async function handleFlashComplete(video, callback, clip) {
    try {
        // 先检测人脸
        const detection = await detectSingleFace(video);
        if (!detection) {
            return {
                should_retry: true,
                error: '未检测到有效人脸'
            };
        }

        const result = await submitFlashVerification();

        if (result.should_retry) {
            console.log('炫光验证失败，需要重试');
            return result;  // 返回重试信息
        }

        if (result.status === 'success') {
            // 验证成功，提交最终结果
            const finalImage = getImage(video, detection);
            if (clip) {
                clipFace(finalImage, detections, function(src) {
                    if (callback) callback(src);
                });
            } else if (callback) {
                callback(finalImage.src);
            }
        } else {
            showError('验证失败，请重试');
        }
        return result;
    } catch (error) {
        console.error('炫光验证失败:', error);
        showError('验证失败，请重试');
        return {
            should_retry: true,
            error: error.message
        };
    }
}

function showError(message) {
    const alertFailed = document.getElementById('alert-failed');
    alertFailed.textContent = message;
    alertFailed.classList.remove('d-none');
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
        nose: Math.abs(nose[0]._y - lastLandmarks.getNose()[0]._y),
        noseTip: Math.abs(nose[3]._y - lastLandmarks.getNose()[3]._y),
        chin: Math.abs(jaw[8]._y - lastLandmarks.getJawOutline()[8]._y),
    };

    // 计算平均移动距离
    const avgMovement = (movements.nose + movements.noseTip + movements.chin) / 3;

    console.log("点头检测详细信息:", {
        movements,
        avgMovement,
        currentPoints: {
            nose: nose[0]._y,
            noseTip: nose[3]._y,
            chin: jaw[8]._y,
        },
        lastPoints: {
            nose: lastLandmarks.getNose()[0]._y,
            noseTip: lastLandmarks.getNose()[3]._y,
            chin: lastLandmarks.getJawOutline()[8]._y,
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
        nose: Math.abs(nose[0]._x - lastLandmarks.getNose()[0]._x),
        leftEye: Math.abs(leftEye._x - lastLandmarks.getLeftEye()[0]._x),
        rightEye: Math.abs(rightEye._x - lastLandmarks.getRightEye()[3]._x),
    };

    // 计算平均移动距离
    const avgMovement = (movements.nose + movements.leftEye + movements.rightEye) / 3;

    console.log("摇头检测详细信息:", {
        movements,
        avgMovement,
        currentPoints: {
            nose: nose[0]._x,
            leftEye: leftEye._x,
            rightEye: rightEye._x,
        },
        lastPoints: {
            nose: lastLandmarks.getNose()[0]._x,
            leftEye: lastLandmarks.getLeftEye()[0]._x,
            rightEye: lastLandmarks.getRightEye()[3]._x,
        },
    });

    return avgMovement > 4;
}

function checkMouth(landmarks, expressions) {
    const mouth = landmarks.getMouth();

    // 计算嘴巴的开合程度
    const topLip = mouth[14]; // 上唇中点
    const bottomLip = mouth[18]; // 下唇中点
    const mouthOpen = getDistance(
        { x: topLip._x, y: topLip._y },
        { x: bottomLip._x, y: bottomLip._y }
    );

    // 计算嘴巴的宽度作为参考
    const mouthWidth = getDistance(
        { x: mouth[0]._x, y: mouth[0]._y },
        { x: mouth[6]._x, y: mouth[6]._y }
    );

    // 计算开合比例
    const mouthRatio = mouthOpen / mouthWidth;

    console.log("张嘴检测详细信息:", {
        expressions,
        mouth: {
            width: mouthWidth,
            openDistance: mouthOpen,
            ratio: mouthRatio,
            points: mouth.map(p => ({ x: p._x, y: p._y })),
        },
        topLip: { x: topLip._x, y: topLip._y },
        bottomLip: { x: bottomLip._x, y: bottomLip._y },
    });

    // 使用开合比例和表情综合判断
    return mouthRatio > 0.5 || expressions.surprised > 0.3;
}

function getDistance(point1, point2) {
    return Math.sqrt(
        Math.pow(point1.x - point2.x, 2) + Math.pow(point1.y - point2.y, 2)
    );
}

function getImage(video, detection = null) {
    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");

    if (detection && detection.detection) {
        // 如果有人脸检测结果，裁剪人脸区域并居中
        const box = detection.detection.box;

        // 计算理想的边距（使用较大的值确保捕获完整的头部）
        const padding = {
            x: box.width * 0.5,  // 水平方向添加 50% 的边距
            y: box.height * 0.5  // 垂直方向添加 50% 的边距
        };

        // 计算人脸中心点
        const faceCenter = {
            x: box.x + box.width / 2,
            y: box.y + box.height / 2
        };

        // 计算裁剪区域（以人脸中心点为中心）
        const cropWidth = box.width + padding.x * 2;
        const cropHeight = box.height + padding.y * 2;

        // 计算裁剪区域的起始点（确保人脸居中）
        const crop = {
            x: faceCenter.x - cropWidth / 2,
            y: faceCenter.y - cropHeight / 2,
            width: cropWidth,
            height: cropHeight
        };

        // 处理边界情况
        if (crop.x < 0) {
            crop.x = 0;
        }
        if (crop.y < 0) {
            crop.y = 0;
        }
        if (crop.x + crop.width > video.videoWidth) {
            crop.width = video.videoWidth - crop.x;
        }
        if (crop.y + crop.height > video.videoHeight) {
            crop.height = video.videoHeight - crop.y;
        }

        // 确保裁剪区域是正方形（使用较大的边作为正方形边长）
        const squareSize = Math.max(crop.width, crop.height);

        // 重新调整裁剪区域为正方形，并保持人脸居中
        crop.width = squareSize;
        crop.height = squareSize;
        crop.x = Math.max(0, Math.min(
            faceCenter.x - squareSize / 2,
            video.videoWidth - squareSize
        ));
        crop.y = Math.max(0, Math.min(
            faceCenter.y - squareSize / 2,
            video.videoHeight - squareSize
        ));

        // 设置画布大小为正方形
        canvas.width = squareSize;
        canvas.height = squareSize;

        // 绘制裁剪后的图像
        context.drawImage(
            video,
            crop.x, crop.y, crop.width, crop.height,  // 源图像裁剪区域
            0, 0, canvas.width, canvas.height  // 目标区域（完整画布）
        );
    } else {
        // 如果没有检测结果，使用固定尺寸
        const maxDimension = 640; // 设置最大尺寸
        let width = video.videoWidth;
        let height = video.videoHeight;

        // 等比例缩放
        if (width > height && width > maxDimension) {
            height = (height * maxDimension) / width;
            width = maxDimension;
        } else if (height > maxDimension) {
            width = (width * maxDimension) / height;
            height = maxDimension;
        }

        canvas.width = width;
        canvas.height = height;
        context.drawImage(video, 0, 0, width, height);
    }

    const img = new Image();
    img.src = canvas.toDataURL(imageType, 0.8);

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

async function startFlashDetection(video) {
    isFlashing = true;
    document.getElementById('light-prompt').classList.remove('d-none');
    document.getElementById('action-prompt').classList.add('d-none');

    // 保存原始背景色
    originalBodyColor = document.body.style.backgroundColor;

    // 重置炫光数据
    flashImages = [];
    flashData = [];

    // 创建用于颜色检测的canvas
    const colorCanvas = document.createElement('canvas');
    colorCanvas.width = video.videoWidth;
    colorCanvas.height = video.videoHeight;

    let lastEyeColors = null;

    // 等待检测到人脸的函数
    async function waitForFace() {
        while (true) {
            const detection = await detectSingleFace(video);
            if (detection) {
                return detection;
            }
            // 等待100ms后重试
            await new Promise(resolve => setTimeout(resolve, 100));
        }
    }

    async function processNextFlash() {
        try {
            // 获取下一个炫光颜色
            const response = await fetch('/face/verification/next-flash', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    session_id: verificationSession.session_id
                })
            });

            if (!response.ok) {
                const error = await response.json();
                throw error;
            }

            const result = await response.json();

            // 检查是否完成所有炫光验证
            if (result.status === 'completed') {
                document.body.style.backgroundColor = originalBodyColor;
                isFlashing = false;
                updateActionPrompt('活体验证成功');
                return;
            }

            const color = result.color;
            console.log(`正在进行第 ${result.current_count + 1}/${result.total_required} 次炫光验证`);

            // 等待检测到人脸
            updateActionPrompt('请保持正面面对摄像头');
            const detection = await waitForFace();

            // 记录炫光前的眼睛颜色
            const beforeFlash = checkLight(detection.landmarks, video, colorCanvas);
            lastEyeColors = beforeFlash.eyeColors;
            console.log('炫光前眼睛颜色:', lastEyeColors);

            // 应用炫光颜色
            document.body.classList.remove(...flashColors.map(c => c.class));
            document.body.classList.add(`flash-${color}`);
            console.log('显示炫光颜色:', color);

            // 等待一段时间让炫光效果稳定
            await new Promise(resolve => setTimeout(resolve, 500));

            // 再次等待检测到人脸
            const duringDetection = await waitForFace();

            // 检测炫光期间的眼睛颜色
            const duringFlash = checkLight(duringDetection.landmarks, video, colorCanvas);
            console.log('炫光期间眼睛颜色:', duringFlash.eyeColors);

            // 验证眼睛颜色是否匹配炫光颜色
            const isValidFlash = validateEyeColor(duringFlash.eyeColors, color);
            if (!isValidFlash) {
                throw new Error('未检测到有效的炫光反应，请调整光线后重试');
            }

            // 等待一段时间让用户适应
            await new Promise(resolve => setTimeout(resolve, 500));

            // 移除炫光颜色
            document.body.classList.remove(`flash-${color}`);
            document.body.style.backgroundColor = originalBodyColor;

            // 等待一段时间后拍照
            await new Promise(resolve => setTimeout(resolve, 500));

            // 再次等待检测到人脸并拍照
            const finalDetection = await waitForFace();
            const image = getImage(video, finalDetection);

            // 提交验证数据到服务器
            const verifyResponse = await fetch('/face/verification/verify-flash', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    session_id: verificationSession.session_id,
                    flash_data: {
                        color: color,
                        timestamp: Math.floor(Date.now() / 1000),
                        image: image.src,
                        light_change: {
                            before: lastEyeColors,
                            during: duringFlash.eyeColors
                        }
                    }
                })
            });

            if (!verifyResponse.ok) {
                const error = await verifyResponse.json();
                throw error;
            }

            // 继续处理下一个炫光
            await processNextFlash();
        } catch (error) {
            console.error('炫光处理失败:', error);
            document.body.style.backgroundColor = originalBodyColor;
            document.body.classList.remove(...flashColors.map(c => c.class));
            isFlashing = false;

            if (error.should_retry) {
                showError(`炫光验证失败: ${error.error}，剩余重试次数: ${error.retries_left}`);
                // 等待3秒后重试
                await new Promise(resolve => setTimeout(resolve, 3000));
                await processNextFlash();
            } else {
                showError('炫光验证失败，请重新开始验证');
            }
        }
    }

    // 开始处理第一个炫光
    await processNextFlash();
}

// 本地验证炫光颜色
function validateFlashColorLocally(avgColor, expectedColor) {
    // 定义颜色阈值
    const colorThresholds = {
        red: { r: 150, g: 50, b: 50 },
        green: { r: 50, g: 150, b: 50 },
        blue: { r: 50, g: 50, b: 150 },
        yellow: { r: 150, g: 150, b: 50 },
        purple: { r: 150, g: 50, b: 150 }
    };

    const threshold = colorThresholds[expectedColor];
    if (!threshold) {
        console.error('未知的炫光颜色');
        return false;
    }

    // 验证颜色是否匹配
    return (
        avgColor.r >= threshold.r &&
        avgColor.g >= threshold.g &&
        avgColor.b >= threshold.b
    );
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

async function startVerification() {
    try {
        const response = await fetch('/face/verification/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (!response.ok) {
            throw new Error('开始验证失败');
        }

        verificationSession = await response.json();
        return verificationSession;
    } catch (error) {
        console.error('开始验证失败:', error);
        throw error;
    }
}

async function submitActionVerification(actionIndex, imageData, actionData) {
    try {
        const response = await fetch('/face/verification/action', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                session_id: verificationSession.session_id,
                action_index: actionIndex,
                image: imageData,
                action_data: actionData
            })
        });

        const result = await response.json();

        // 检查状态码
        if (response.status === 429) {
            throw { status: 429, message: '验证失败次数过多' };
        }

        if (!response.ok) {
            if (result.should_retry) {
                const retryCount = result.retries_left ?? 0;
                const errorMessage = result.error || '验证失败';
                console.log(`动作验证失败，剩余重试次数: ${retryCount}`);
                updateActionPrompt(`验证失败: ${errorMessage}，剩余重试次数: ${retryCount}，请等待3秒后重试`);
                // 允许重试当前动作
                return {
                    should_retry: true,
                    current_action: currentAction.name,
                    error: errorMessage,
                    retries_left: retryCount
                };
            }
            throw new Error(result.error || '动作验证失败');
        }

        return result;
    } catch (error) {
        console.error('提交动作验证失败:', error);
        throw error;
    }
}

async function submitFlashVerification() {
    try {
        const response = await fetch('/face/verification/flash', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                session_id: verificationSession.session_id,
                flash_images: flashImages,
                flash_data: flashData
            })
        });

        // 检查状态码
        if (response.status === 429) {
            stopVideo(video);
            showError('验证失败次数过多，请稍后再试');
            return { should_retry: false };
        }

        const result = await response.json();

        if (!response.ok) {
            if (result.should_retry) {
                const retryCount = result.retries_left ?? 0;
                const errorMessage = result.error || '验证失败';
                console.log(`炫光验证失败，剩余重试次数: ${retryCount}`);
                updateActionPrompt(`验证失败: ${errorMessage}，剩余重试次数: ${retryCount}，请等待3秒后重试`);

                // 等待3秒后才允许重试
                await new Promise(resolve => setTimeout(resolve, 3000));

                return { should_retry: true };
            }
            throw new Error(result.error || '炫光验证失败');
        }

        return result;
    } catch (error) {
        console.error('提交炫光验证失败:', error);
        throw error;
    }
}

// 验证眼睛区域的颜色是否匹配炫光颜色
function validateEyeColor(eyeColors, expectedColor) {
    if (!eyeColors) {
        console.error('未获取到眼睛颜色数据');
        return false;
    }

    // 定义基准颜色值
    const baseColors = {
        red: { r: 255, g: 0, b: 0 },
        green: { r: 0, g: 255, b: 0 },
        blue: { r: 0, g: 0, b: 255 },
        yellow: { r: 255, g: 255, b: 0 },
        purple: { r: 255, g: 0, b: 255 }
    };

    const baseColor = baseColors[expectedColor];
    if (!baseColor) {
        console.error('未知的炫光颜色:', expectedColor);
        return false;
    }

    // 计算每个颜色通道的差异比例
    const diffR = Math.abs(eyeColors.r - baseColor.r) / 255;
    const diffG = Math.abs(eyeColors.g - baseColor.g) / 255;
    const diffB = Math.abs(eyeColors.b - baseColor.b) / 255;

    // 允许的差异阈值（0.3 表示 30% 的差异）
    const THRESHOLD = 0.3;

    // 根据不同颜色的特性判断
    let isValid = false;
    switch (expectedColor) {
        case 'red':
            // 红色：要求红色分量最强，其他分量相对较弱
            isValid = eyeColors.r > eyeColors.g && eyeColors.r > eyeColors.b;
            break;
        case 'green':
            // 绿色：要求绿色分量最强，其他分量相对较弱
            isValid = eyeColors.g > eyeColors.r && eyeColors.g > eyeColors.b;
            break;
        case 'blue':
            // 蓝色：要求蓝色分量最强，其他分量相对较弱
            isValid = eyeColors.b > eyeColors.r && eyeColors.b > eyeColors.g;
            break;
        case 'yellow':
            // 黄色：要求红色和绿色分量都较强，蓝色分量较弱
            isValid = eyeColors.r > eyeColors.b && eyeColors.g > eyeColors.b;
            break;
        case 'purple':
            // 紫色：要求红色和蓝色分量都较强，绿色分量较弱
            isValid = eyeColors.r > eyeColors.g && eyeColors.b > eyeColors.g;
            break;
    }

    console.log('眼睛颜色验证:', {
        expected: baseColor,
        actual: eyeColors,
        differences: {
            r: diffR.toFixed(2),
            g: diffG.toFixed(2),
            b: diffB.toFixed(2)
        },
        isValid: isValid
    });

    return isValid;
}

window.face_capture = {
    start,
    stopVideo,
    removeListeners,
    handlePlayEvent,
    getImage,
};
