<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FaceVerification;
use App\Support\Face\FaceSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Exceptions\CommonException;
use Exception;
use Illuminate\Support\Facades\Log;

class FaceVerificationController extends Controller
{
    /**
     * 最大重试次数
     */
    protected $maxRetries = 5;

    private FaceSupport $faceSupport;

    public function __construct(FaceSupport $faceSupport)
    {
        $this->faceSupport = $faceSupport;
        $this->middleware('auth:web');  // 指定 web guard
    }

    // 开始新的验证会话
    public function startVerification(Request $request)
    {
        $user = $request->user('web');
        if (!$user) {
            return response()->json(['error' => '未登录'], 401);
        }

        $sessionId = Str::random(40);

        $verification = FaceVerification::create([
            'session_id' => $sessionId,
            'user_id' => $user->id,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(5),
            'action_sequence' => $this->generateActionSequence(),
            'flash_sequence' => $this->generateFlashSequence(),
            'verification_data' => [
                'retry_counts' => [
                    'initial' => 0,
                    'actions' => [0, 0, 0],  // 三个动作的重试次数
                    'flash' => 0
                ]
            ]
        ]);

        return response()->json([
            'session_id' => $sessionId,
            'expires_at' => $verification->expires_at
        ]);
    }

    // 提交初始人脸数据
    public function submitInitialFace(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'image' => 'required|string'
        ]);

        $verification = FaceVerification::where('session_id', $request->session_id)
            ->where('status', 'pending')
            ->firstOrFail();

        // 检查重试次数
        $retryCounts = $verification->verification_data['retry_counts'];
        if ($retryCounts['initial'] >= 5) {
            $verification->update(['status' => 'failed']);
            return response()->json(['error' => '初始人脸验证失败次数过多'], 400);
        }

        try {
            Log::debug('初始人脸验证: ' . $request->image);
            // 检查图像并提取特征
            $embedding = $this->faceSupport->test_image($request->image);

            // 更新验证数据
            $verificationData = $verification->verification_data;
            $verificationData['initial_face'] = [
                'embedding' => $embedding,
                'image' => $request->image,
                'timestamp' => now()
            ];
            $verification->verification_data = $verificationData;
            $verification->status = 'processing';
            $verification->save();

            return response()->json([
                'next_action' => $verification->action_sequence[0]
            ]);

        } catch (\Exception $e) {
            // 增加重试次数
            $verificationData = $verification->verification_data;
            $verificationData['retry_counts']['initial']++;
            $verification->verification_data = $verificationData;
            $verification->save();

            if ($verificationData['retry_counts']['initial'] >= 5) {
                $verification->update(['status' => 'failed']);
                return response()->json(['error' => '初始人脸验证失败次数过多'], 400);
            }

            return response()->json([
                'error' => $e->getMessage(),
                'should_retry' => true,
                'retries_left' => 5 - $verificationData['retry_counts']['initial']
            ], 400);
        }
    }

    // 提交动作验证数据
    public function submitActionVerification(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'action_index' => 'required|integer',
            'image' => 'required|string',
            'action_data' => 'required|array'
        ]);

        $verification = FaceVerification::where('session_id', $request->session_id)
            ->where('status', 'processing')
            ->firstOrFail();

        // 检查动作索引是否有效
        if ($request->action_index >= count($verification->action_sequence)) {
            return response()->json(['error' => '无效的动作索引'], 400);
        }

        // 检查重试次数
        $retryCounts = $verification->verification_data['retry_counts'];
        if ($retryCounts['actions'][$request->action_index] >= 5) {
            $verification->update(['status' => 'failed']);
            return response()->json(['error' => '动作验证失败次数过多'], 400);
        }

        try {
            // 验证动作过程图片
            $process_images = $request->action_data['process_images'] ?? [];
            if (empty($process_images)) {
                throw new CommonException('未提供动作过程图片。');
            }

            // 验证每张图片
            $valid_face_count = 0;
            $max_confidence = 0;
            $best_embedding = null;

            foreach ($process_images as $process_image) {
                try {
                    $represent = $this->faceSupport->represent($process_image['image']);
                    Log::info('动作过程图片验证: ' . $process_image['image']);
                    $result = $represent['results'][0];

                    // 检查人脸数量
                    if (count($represent['results']) > 1) {
                        throw new CommonException('画面中有多个人脸，请换个地方尝试。');
                    }

                    // 检查人脸置信度
                    if ($result['face_confidence'] < 0.8) {
                        throw new CommonException('人脸置信度太低，请重新尝试。');
                    }

                    $valid_face_count++;

                    // 记录置信度最高的特征向量
                    if ($result['face_confidence'] > $max_confidence) {
                        $max_confidence = $result['face_confidence'];
                        $best_embedding = $result['embedding'];
                    }
                } catch (Exception $e) {
                    // 记录错误但继续处理其他图片
                    Log::warning('动作过程图片验证失败: ' . $e->getMessage(), [
                        'session_id' => $verification->id,
                        'action_index' => $request->action_index,
                        'amplitude' => $process_image['amplitude']
                    ]);
                    continue;
                }
            }

            // 至少要有一张有效的人脸图片
            if ($valid_face_count === 0) {
                throw new CommonException('未能从动作过程中检测到有效人脸。');
            }

            // TODO: 验证动作是否正确
            // $this->validateActionData(
            //     $verification->action_sequence[$request->action_index],
            //     $request->action_data
            // );

            // 验证人脸是否匹配
            $this->validateFaceMatch(
                $verification->verification_data['initial_face']['embedding'],
                $best_embedding
            );

            // 更新验证数据
            $verificationData = $verification->verification_data;
            $verificationData['actions'][$request->action_index] = [
                'process_images' => $process_images,
                'best_embedding' => $best_embedding,
                'action_data' => $request->action_data,
                'timestamp' => now()
            ];
            $verification->verification_data = $verificationData;
            $verification->save();

            // 检查是否完成所有动作
            if ($request->action_index >= count($verification->action_sequence) - 1) {
                return response()->json([
                    'status' => 'actions_completed',
                    'flash_sequence' => $verification->flash_sequence
                ]);
            }

            // 返回下一个动作
            return response()->json([
                'next_action' => $verification->action_sequence[$request->action_index + 1]
            ]);

        } catch (\Exception $e) {
            Log::error('动作验证失败: ' . $e->getMessage());
            // 增加重试次数
            $verificationData = $verification->verification_data;
            $verificationData['retry_counts']['actions'][$request->action_index]++;
            $verification->verification_data = $verificationData;
            $verification->save();

            if ($verificationData['retry_counts']['actions'][$request->action_index] >= 5) {
                $verification->update(['status' => 'failed']);
                return response()->json(['error' => '动作验证失败次数过多'], 400);
            }

            return response()->json([
                'error' => $e->getMessage(),
                'should_retry' => true,
                'retries_left' => 5 - $verificationData['retry_counts']['actions'][$request->action_index]
            ], 400);
        }
    }

    // 提交炫光验证数据
    public function submitFlashVerification(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'flash_images' => 'required|array',
            'flash_data' => 'required|array'
        ]);

        $verification = FaceVerification::where('session_id', $request->session_id)
            ->where('status', 'processing')
            ->firstOrFail();

        // 检查重试次数
        $retryCounts = $verification->verification_data['retry_counts'];
        if ($retryCounts['flash'] >= 5) {
            $verification->update(['status' => 'failed']);
            return response()->json(['error' => '炫光验证失败次数过多'], 400);
        }

        try {
            // 验证炫光序列是否匹配
            $this->validateFlashSequence(
                $verification->flash_sequence,
                $request->flash_data
            );

            // 验证每张炫光图片中的人脸
            foreach ($request->flash_images as $image) {
                $currentEmbedding = $this->faceSupport->test_image($image);
                $this->validateFaceMatch(
                    $verification->verification_data['initial_face']['embedding'],
                    $currentEmbedding
                );
            }

            // 更新验证状态
            $verificationData = $verification->verification_data;
            $verificationData['flash'] = [
                'images' => $request->flash_images,
                'flash_data' => $request->flash_data,
                'timestamp' => now()
            ];
            $verification->verification_data = $verificationData;
            $verification->status = 'completed';
            $verification->save();

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            // 增加重试次数
            $verificationData = $verification->verification_data;
            $verificationData['retry_counts']['flash']++;
            $verification->verification_data = $verificationData;
            $verification->save();

            if ($verificationData['retry_counts']['flash'] >= 5) {
                $verification->update(['status' => 'failed']);
                return response()->json(['error' => '炫光验证失败次数过多'], 400);
            }

            return response()->json([
                'error' => $e->getMessage(),
                'should_retry' => true,
                'retries_left' => 5 - $verificationData['retry_counts']['flash']
            ], 400);
        }
    }

    /**
     * 获取下一个炫光颜色
     */
    public function getNextFlashColor(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);

        $verification = FaceVerification::where('session_id', $request->session_id)
            ->where('status', 'processing')
            ->firstOrFail();

        $verificationData = $verification->verification_data;

        // 检查是否已完成足够数量的炫光验证
        if (isset($verificationData['verified_flashes']) &&
            count($verificationData['verified_flashes']) >= 3) {
            return response()->json([
                'status' => 'completed',
                'message' => '活体验证成功'
            ]);
        }

        // 生成新的随机炫光颜色
        $colors = ['red', 'green', 'blue', 'yellow', 'purple'];
        $newColor = $colors[array_rand($colors)];

        // 保存当前的炫光颜色
        $verificationData['current_flash_color'] = $newColor;
        $verification->verification_data = $verificationData;
        $verification->save();

        return response()->json([
            'color' => $newColor,
            'total_required' => 3,
            'current_count' => count($verificationData['verified_flashes'] ?? [])
        ]);
    }

    /**
     * 验证单个炫光
     */
    public function verifyFlash(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'flash_data' => 'required|array'
        ]);

        $verification = FaceVerification::where('session_id', $request->session_id)
            ->where('status', 'processing')
            ->firstOrFail();

        try {
            // 获取当前预期的炫光颜色
            $expectedColor = $verification->verification_data['current_flash_color'] ?? null;
            if (!$expectedColor) {
                throw new \Exception('无效的炫光验证顺序');
            }

            // 验证炫光
            $this->validateSingleFlash(
                $expectedColor,
                $request->flash_data,
                $verification->verification_data['initial_face']['embedding']
            );

            // 记录已验证的炫光
            $verificationData = $verification->verification_data;
            if (!isset($verificationData['verified_flashes'])) {
                $verificationData['verified_flashes'] = [];
            }
            $verificationData['verified_flashes'][] = [
                'color' => $expectedColor,
                'data' => $request->flash_data,
                'timestamp' => now()
            ];

            // 检查是否完成了足够数量的炫光验证
            if (count($verificationData['verified_flashes']) >= 3) {
                $verification->status = 'completed';
            }

            $verification->verification_data = $verificationData;
            $verification->save();

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            // 增加重试次数
            $verificationData = $verification->verification_data;
            $verificationData['retry_counts']['flash']++;
            $verification->verification_data = $verificationData;
            $verification->save();

            if ($verificationData['retry_counts']['flash'] >= 5) {
                $verification->update(['status' => 'failed']);
                return response()->json([
                    'error' => '炫光验证失败次数过多',
                    'should_retry' => false
                ], 400);
            }

            return response()->json([
                'error' => $e->getMessage(),
                'should_retry' => true,
                'retries_left' => 5 - $verificationData['retry_counts']['flash']
            ], 400);
        }
    }

    private function generateActionSequence(): array
    {
        $actions = ['nod', 'shake', 'mouth'];
        shuffle($actions);
        return array_slice($actions, 0, 3);
    }

    private function generateFlashSequence(): array
    {
        $colors = ['red', 'green', 'blue', 'yellow', 'purple'];
        shuffle($colors);
        return array_slice($colors, 0, 3);
    }

    private function validateFaceMatch(array $initialEmbedding, array $currentEmbedding): void
    {
        // 计算欧氏距离，判断是否为同一个人
        $distance = $this->calculateEuclideanDistance($initialEmbedding, $currentEmbedding);

        // 根据实际测试数据调整阈值
        // 同一个人的欧氏距离通常在20左右，我们设置25作为阈值
        // 大于25认为是不同的人
        if ($distance > 25) {
            Log::warning('人脸匹配失败，距离值: ' . $distance);
            throw new \Exception('人脸不匹配，请重新验证');
        }

        // 记录匹配距离用于调试
        Log::info('人脸匹配成功，距离值: ' . $distance);
    }

    private function calculateEuclideanDistance(array $embedding1, array $embedding2): float
    {
        if (count($embedding1) !== count($embedding2)) {
            throw new \Exception('特征向量维度不匹配');
        }

        return sqrt(array_sum(array_map(function($a, $b) {
            return pow($a - $b, 2);
        }, $embedding1, $embedding2)));
    }

    private function validateFlashSequence(array $expectedSequence, array $detectedData): void
    {
        // 验证炫光检测结果是否符合预期序列
        if (count($detectedData) !== count($expectedSequence)) {
            throw new \Exception('炫光序列数量不匹配');
        }

        foreach ($detectedData as $index => $data) {
            if (!isset($data['color']) || !isset($data['timestamp'])) {
                throw new \Exception('炫光数据格式错误');
            }

            if ($data['color'] !== $expectedSequence[$index]) {
                Log::warning('炫光序列不匹配', [
                    'expected' => $expectedSequence[$index],
                    'received' => $data['color'],
                    'index' => $index
                ]);
                throw new \Exception('炫光序列不匹配');
            }

            // 验证时间戳的合理性
            if (abs(time() - $data['timestamp']) > 30) {
                throw new \Exception('炫光验证超时');
            }
        }
    }

    /**
     * 验证动作数据
     */
    private function validateActionData(string $expectedAction, array $actionData): void
    {
        // 验证动作数据的时间戳
        if (!isset($actionData['timestamp']) ||
            abs(time() - $actionData['timestamp']) > 10) {
            throw new CommonException('动作数据时间戳无效。');
        }

        // 根据不同动作类型验证数据
        switch ($expectedAction) {
            case 'nod':
                $this->validateNodAction($actionData);
                break;
            case 'shake':
                $this->validateShakeAction($actionData);
                break;
            case 'mouth':
                $this->validateMouthAction($actionData);
                break;
            default:
                throw new CommonException('未知的动作类型。');
        }
    }

    /**
     * 验证点头动作
     */
    protected function validateNodAction(array $actionData): void
    {
        if (!isset($actionData['landmarks'])) {
            throw new CommonException('缺少动作关键点数据。');
        }

        // 这里可以添加更详细的点头动作验证逻辑
        // 例如验证垂直方向的移动幅度等
    }

    /**
     * 验证摇头动作
     */
    protected function validateShakeAction(array $actionData): void
    {
        if (!isset($actionData['landmarks'])) {
            throw new CommonException('缺少动作关键点数据。');
        }

        // 这里可以添加更详细的摇头动作验证逻辑
        // 例如验证水平方向的移动幅度等
    }

    /**
     * 验证张嘴动作
     */
    protected function validateMouthAction(array $actionData): void
    {
        if (!isset($actionData['landmarks']) || !isset($actionData['expressions'])) {
            throw new CommonException('缺少动作数据。');
        }

        // 这里可以添加更详细的张嘴动作验证逻辑
        // 例如验证嘴部关键点的开合度和惊讶表情的程度等
    }

    // 重命名旧的 validateAction 方法为 handleActionValidation
    protected function handleActionValidation(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'action_index' => 'required|integer|min:0|max:2',
            'start_image' => 'required|string',
            'end_image' => 'required|string',
            'action_data' => 'required|array',
        ]);

        $session = $this->getSession($request->session_id);
        if (!$session) {
            throw new CommonException('验证会话已过期，请刷新页面重试。');
        }

        // 验证动作索引
        if ($request->action_index !== $session->current_action_index) {
            throw new CommonException('动作验证顺序错误。');
        }

        try {
            // 验证起始图片中的人脸
            $startImageData = $this->faceSupport->represent($request->start_image);
            if (empty($startImageData['results'])) {
                throw new CommonException('起始图片中未检测到人脸。');
            }
            if (count($startImageData['results']) > 1) {
                throw new CommonException('起始图片中检测到多个人脸。');
            }
            if ($startImageData['results'][0]['face_confidence'] < 0.8) {
                throw new CommonException('起始图片中人脸置信度过低。');
            }

            // 验证结束图片中的人脸
            $endImageData = $this->faceSupport->represent($request->end_image);
            if (empty($endImageData['results'])) {
                throw new CommonException('结束图片中未检测到人脸。');
            }
            if (count($endImageData['results']) > 1) {
                throw new CommonException('结束图片中检测到多个人脸。');
            }
            if ($endImageData['results'][0]['face_confidence'] < 0.8) {
                throw new CommonException('结束图片中人脸置信度过低。');
            }

            // 验证起始和结束图片中的人脸是否匹配
            $startEmbedding = $startImageData['results'][0]['embedding'];
            $endEmbedding = $endImageData['results'][0]['embedding'];

            $this->validateFaceMatch($startEmbedding, $endEmbedding);

            // 验证动作数据
            $this->validateActionData(
                $session->actions[$request->action_index],
                $request->action_data
            );

            // 更新会话状态
            $session->verification_data['actions'][$request->action_index] = [
                'start_image' => [
                    'embedding' => $startEmbedding,
                    'confidence' => $startImageData['results'][0]['face_confidence']
                ],
                'end_image' => [
                    'embedding' => $endEmbedding,
                    'confidence' => $endImageData['results'][0]['face_confidence']
                ],
                'action_data' => $request->action_data,
                'timestamp' => now()
            ];

            $session->current_action_index++;
            $session->save();

            // 检查是否所有动作都已完成
            if ($session->current_action_index >= count($session->actions)) {
                // 生成随机炫光序列
                $flashSequence = $this->generateFlashSequence();
                $session->flash_sequence = $flashSequence;
                $session->save();

                return [
                    'status' => 'actions_completed',
                    'flash_sequence' => $flashSequence
                ];
            }

            // 返回下一个动作
            return [
                'status' => 'next_action',
                'next_action' => $session->actions[$session->current_action_index]
            ];

        } catch (CommonException $e) {
            // 处理重试逻辑
            $session->action_retries = ($session->action_retries ?? 0) + 1;
            $session->save();

            if ($session->action_retries >= $this->maxRetries) {
                throw new CommonException('验证失败次数过多，请稍后再试。');
            }

            return [
                'should_retry' => true,
                'current_action' => $session->actions[$request->action_index],
                'error' => $e->getMessage(),
                'retries_left' => $this->maxRetries - $session->action_retries
            ];
        }
    }

    /**
     * 验证单个炫光
     */
    private function validateSingleFlash(string $expectedColor, array $flashData, array $initialEmbedding): void
    {
        if (!isset($flashData['color']) || !isset($flashData['timestamp']) || !isset($flashData['image'])) {
            throw new \Exception('炫光数据格式错误');
        }

        // 验证颜色是否匹配
        if ($flashData['color'] !== $expectedColor) {
            Log::warning('炫光颜色不匹配', [
                'expected' => $expectedColor,
                'received' => $flashData['color']
            ]);
            throw new \Exception('炫光序列不匹配');
        }

        // 验证时间戳的合理性
        if (abs(time() - $flashData['timestamp']) > 30) {
            throw new \Exception('炫光验证超时');
        }

        // 验证图片中的人脸
        try {
            $embedding = $this->faceSupport->test_image($flashData['image']);
            $this->validateFaceMatch($initialEmbedding, $embedding);
        } catch (\Exception $e) {
            throw new \Exception('炫光验证中的人脸识别失败: ' . $e->getMessage());
        }
    }
}
