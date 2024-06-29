<?php

namespace App\Models;

use App\Exceptions\CommonException;
use App\Support\ImageSupport;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Face extends Model
{
    protected $fillable = [
        'type',
        'user_id',
    ];

    // 用户验证的类型
    public const string TYPE_VALIDATE = 'validate';

    public const string EXT = 'jpeg';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createFace(string $type, User $user): self
    {
        return $this->create([
            'type' => $type,
            'user_id' => $user->id,
        ]);
    }

    public function getPath(): string
    {
        return '/'.$this->created_at->format('Y/m/d').'/faces/'.$this->id.'.'.self::EXT;
    }

    public function putFile($file = null): bool
    {
        if (! $file) {
            return false;
        }

        try {
            $file = (new ImageSupport())->convertToJpeg($file);
        } catch (CommonException $e) {
            Log::error($e->getMessage());

            return false;
        }

        $success = Storage::disk('s3')->put($this->getPath(), $file);

        return ! $success == false;
    }

    public function getTempLink(): string
    {
        // Minio 不支持 temporaryUrl, 所以创建 presignedUrl
        return Storage::disk('s3')->temporaryUrl($this->getPath(), now()->addMinutes(5));
    }

    public function getFile(): string
    {
        return Storage::disk('s3')->get($this->getPath());
    }

    private function deleteFile(): bool
    {
        return Storage::disk('s3')->delete($this->getPath());
    }

    /**
     * @throws Exception
     */
    public function delete(): bool
    {
        if (! $this->deleteFile()) {
            throw new Exception('删除文件失败');
        }

        return parent::delete();
    }

    // scope validate
    public function scopeValidate($query)
    {
        return $query->where('type', self::TYPE_VALIDATE);
    }
}
