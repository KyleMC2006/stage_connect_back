<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Entreprise extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'nom_entreprise',
        'email_entreprise',
        'siteweb',
        'adresse',
        'id_domaine',
        'RCCM',
        'ville_id'
    
    ];

    protected $table = 'entreprises';

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function domaine(){
        return $this->belongsTo(Domaine::class, 'id_domaine');
    }

    public function offres(){
        return $this->hasMany(Offre::class);
    }

    public function tuteurStages(){
        return $this->hasMany(TuteurStage::class);
    }

    public function ville(){  
        return $this->belongsTo(Ville::class);
    }


    
    public function partenariats()
    {
        return $this->hasMany(Partenariat::class);
    }
    
    
    
}
