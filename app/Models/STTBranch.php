<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class STTBranch extends Model
{
    use HasFactory;
    protected $table = 'branches';

    public function getConnectionName()
    {
        return 'stt_db';
    }

    public function account() {
        return $this->belongsTo('App\Models\STTAccount');
    }

    public function area() {
        return $this->belongsTo('App\Models\STTArea');
    }

    public function classification() {
        return $this->belongsTo('App\Models\STTClassification');
    }

    public function region() {
        return $this->belongsTo('App\Models\STTRegion');
    }
}
