<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Offre extends Model
{
    use HasFactory;
    protected $fillable = [
        'entreprise_id', 
        'titre', 
        'description', 
        'domaine_id', 
        'adresse', 
        'date_expiration', 
        'duree_en_semaines', 
        'date_debut', 
        'statut',
        'is_targeted',       
        'visibility'
    ];
    
    protected $casts = [
        'date_expiration' => 'date',
        'date_debut' => 'date',
        'is_targeted' => 'boolean',
    ];
    protected $table = 'offres';
    
    
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
    
    public function candidatures()
    {
        return $this->hasMany(Candidature::class);
    }
    
    public function stages()
    {
        return $this->hasMany(Stage::class);
    }

    public function domaine(){
        return $this->belongsTo(Domaine::class);
    }
    
    
    public function active($query)
    {
        return $query->where('statut', 'active')
                    ->where('date_expiration', '>=', now());
    }

    public function visibleToStudent($query, $etablissementId)
    {
        return $query->where('visibility', 'public')
                     ->orWhere(function ($query) use ($etablissementId) {
                         $query->where('visibility', 'partners_only')
                               ->whereHas('entreprise.partenariats', function ($q) use ($etablissementId) {
                                   $q->where('etablissement_id', $etablissementId)
                                     ->where('statut', 'actif');
                               });
                     });
    }
}
