<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Candidature extends Model
{
    use HasFactory;
    protected $fillable = [
        'etudiant_id', 'offre_id', 'statut', 
        'date_postulat', 'lettre_motivation'
    ];
    
    protected $casts = [
        'date_postulat' => 'date',
    ];

    protected $table = 'candidatures';
    
    
    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }
    
    public function offre()
    {
        return $this->belongsTo(Offre::class);
    }
    
   
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }
    
    public function scopeAcceptee($query)
    {
        return $query->where('statut', 'acceptee');
    }
}
