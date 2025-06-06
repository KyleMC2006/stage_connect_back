<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stage extends Model
{   
    use HasFactory;
    protected $fillable = [
        'etudiant_id', 'offre_id', 'tuteur_stage_id',
        'date_debut', 'date_fin', 'statut', 
        'rapport_stage', 'note_stage', 'commentaire_note'
    ];
    
    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];
    
    
    protected $table = 'stages';

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }
    
    public function offre()
    {
        return $this->belongsTo(Offre::class);
    }
    
    public function tuteurStage()
    {
        return $this->belongsTo(TuteurStage::class);
    }
    
    
    public function scopeEnCours($query)
    {
        return $query->where('statut', 'en_cours');
    }
    
    public function scopeTermine($query)
    {
        return $query->where('statut', 'termine');
    }
}
