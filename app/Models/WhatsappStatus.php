<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use App\WhatsappUser;
use App\Raffle;

class WhatsappStatus extends Model
{
    use SoftDeletes;

    protected $table = 'whatsapp_status';
    
    protected $fillable = [
                            'zenvia_id', 'zenvia_timestamp', 'zenvia_type', 'zenvia_subscriptionId', 'zenvia_channel', 'zenvia_messageId', 
                            'zenvia_contentIndex', 'zenvia_status_timestamp', 'zenvia_status_code', 'zenvia_status_description', 'zenvia_status_cause_channelErrorCode',
                            'zenvia_status_cause_reason', 'status' 
                        ];
}
