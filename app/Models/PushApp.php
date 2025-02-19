<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PushApp extends Model
{

    protected $table = "push_apps";

    /**
     * 指定主键不自增
     */
    public $incrementing = false;

    /**
     * 主键类型
     */
    protected $keyType = 'string';

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'id',
        'key',
        'secret',
        'max_connections',
        'enable_client_messages',
        'enabled',
        'max_backend_events_per_sec',
        'max_client_events_per_sec',
        'max_read_req_per_sec',
        'max_presence_members_per_channel',
        'max_presence_member_size_in_kb',
        'max_channel_name_length',
        'max_event_channels_at_once',
        'max_event_name_length',
        'max_event_payload_in_kb',
        'max_event_batch_size',
        'webhooks',
        'enable_user_authentication',
    ];

    /**
     * 应该被转换成原生类型的属性
     */
    protected $casts = [
        'max_connections' => 'integer',
        'enable_client_messages' => 'integer',
        'enabled' => 'integer',
        'max_backend_events_per_sec' => 'integer',
        'max_client_events_per_sec' => 'integer',
        'max_read_req_per_sec' => 'integer',
        'max_presence_members_per_channel' => 'integer',
        'max_presence_member_size_in_kb' => 'integer',
        'max_channel_name_length' => 'integer',
        'max_event_channels_at_once' => 'integer',
        'max_event_name_length' => 'integer',
        'max_event_payload_in_kb' => 'integer',
        'max_event_batch_size' => 'integer',
        'webhooks' => 'json',
        'enable_user_authentication' => 'integer',
    ];
}
