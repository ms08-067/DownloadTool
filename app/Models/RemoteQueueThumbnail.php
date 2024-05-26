<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemoteQueueThumbnail extends Model
{
    protected $connection = 'sqlite';
    protected $table = 'queue_thumbnails';
}
