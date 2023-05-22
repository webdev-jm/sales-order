<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class STTRegion extends Model
{
    use HasFactory;

    protected $table = 'regions';

    public function getConnectionName()
    {
        return 'stt_db';
    }
}
