<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TuteurStage extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom_tuteur', 'contact', 'poste', 'entreprise_id'
    ];

    protected $table = 'tuteur_stages';
    
    
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
    
    public function stages()
    {
        return $this->hasMany(Stage::class);
    }
}
