<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\CommonException;
use App\Http\Controllers\Controller;
use App\Models\Face;
use App\Support\FaceSupport;
use App\Support\MilvusSupport;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;

class FaceController extends Controller
{
    public function index()
    {
        $face = auth()->user()->faces()->validate()->first();

        return view('faces.index', compact('face'));
    }

    public function capture(Request $request)
    {
        $face = auth()->user()->faces()->validate()->first();

        if ($face) {
            return redirect()->route('faces.index')->with('error', '你已经录入过人脸，请勿重复录入。');
        }

        if ($request->post()) {
            return $this->doCapture($request);
        }

        return view('faces.capture', [
            'type' => 'store',
        ]);
    }

    private function doCapture(Request $request)
    {
        $request->validate([
            'image_b64' => 'required|string',
        ]);

        $image_b64 = $request->input('image_b64');

        $faceSupport = new FaceSupport();
        try {
            $faceSupport->check($image_b64);
            $embedding = $faceSupport->test_image($image_b64);
        } catch (CommonException $e) {
            return back()->with('error', $e->getMessage());
        }

        try {
            $faces = $faceSupport->search($embedding);
        } catch (CommonException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (count($faces) > 5) {
            return redirect()->route('faces.index')->with('error', '你不能录入太多账户。');
        }

        $face = new Face;

        // decode
        $face = $face->createFace(Face::TYPE_VALIDATE, $request->user('web'));

        $face->putFile($image_b64);

        // 存入 Milvus
        $milvusSupport = new MilvusSupport();

        try {
            $milvusSupport->insert([
                'face_id' => $face->id,
                'embedding' => $embedding,
            ]);

        } catch (ConnectionException $e) {
            try {
                $face->delete();
            } catch (Exception $e) {
                return redirect()->route('faces.index')->with('error', '特征无法保存，且回滚更改时也发生错误。');
            }

            return redirect()->route('faces.index')->with('error', '保存特征数据时发生了错误。');
        }

        // 成功
        return redirect()->route('faces.index')->with('success', '录入成功');
    }

    public function destroy()
    {
        $face = auth()->user()->faces()->validate()->first();

        if (! $face) {
            return redirect()->route('faces.index')->with('error', '没有找到人脸信息。');
        }

        $milvusSupport = new MilvusSupport();
        try {
            $milvusSupport->delete('face_id == '.$face->id);
        } catch (ConnectionException $e) {
            return redirect()->route('faces.index')->with('error', '删除特征数据时发生了错误。');
        }

        $result = $face->delete();

        if (! $result) {
            return redirect()->route('faces.index')->with('error', '删除人脸信息时发生了错误。');
        }

        return redirect()->route('faces.index')->with('success', '删除成功。');
    }

    public function test(Request $request)
    {
        if ($request->post()) {
            return $this->doTest($request);
        }

        return view('faces.capture', [
            'type' => 'test',
        ]);
    }

    private function doTest(Request $request)
    {
        $request->validate([
            'image_b64' => 'required|string',
        ]);

        $image_b64 = $request->input('image_b64');

        $faceSupport = new FaceSupport();
        try {
            $faceSupport->check($image_b64);
            $embedding = $faceSupport->test_image($image_b64);
        } catch (CommonException $e) {
            return back()->with('error', $e->getMessage());
        }

        try {
            $faces = $faceSupport->search($embedding);
        } catch (CommonException $e) {
            return back()->with('error', $e->getMessage());
        }

        $count = count($faces);

        if ($count == 0) {
            return redirect()->route('faces.index')->with('failed', '找不到对应的用户。');
        }

        if ($count > 1) {
            return redirect()->route('faces.index')->with('success', '验证成功。你可能录入了多个账户，在使用人脸登录时，您可以同时登录到这些账户。');
        }

        if ($faces[0]['user_id'] != auth('web')->user()->id) {
            return redirect()->route('faces.index')->with('failed', '验证失败。');
        }

        return redirect()->route('faces.index')->with('success', '验证成功。');
    }
}
