<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;
    protected $table = 'notifications';

    protected $fillable = ['user_id','type','message','donnees_dup'];

    protected $casts = [
        'donnees_sup' => 'array',
        'is_read' => 'boolean',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
