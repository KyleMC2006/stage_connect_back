<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;
    protected $fillable = [
        'expediteur_id', 'destinataire_id', 'contenu', 'lu'
    ];
    
    protected $casts = [
        'lu' => 'boolean',
    ];

    protected $table = 'messages';
    
    
    public function expediteur()
    {
        return $this->belongsTo(User::class, 'expediteur_id');
    }
    
    public function destinataire()
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }
    
    
    public function scopeNonLu($query)
    {
        return $query->where('lu', false);
    }
}
