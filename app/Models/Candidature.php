<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Candidature extends Model
{
    use HasFactory;
    protected $fillable = [
        'etudiant_id',
        'offre_id',
        'statut',
        'date_postulat',
        'lettre_motivation',
        'date_acceptation_entreprise',   // Nouveau champ
        'date_confirmation_etudiant',   // Nouveau champ
        'date_validation_etablissement',// Nouveau champ
        'justificatif_desistement',     
    ];

    protected $casts = [
        'date_postulat'             => 'datetime', 
        'date_acceptation_entreprise' => 'datetime',
        'date_confirmation_etudiant'  => 'datetime',
        'date_validation_etablissement' => 'datetime',
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
    
   
   
}
