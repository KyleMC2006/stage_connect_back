<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FilAnnee extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_fil', 
        'id_annee'
    ];
    
    protected $table = 'filannee';

    public function filiere()
    {
        return $this->belongsTo(Filiere::class, 'id_fil');
    }

    public function annee()
    {
        return $this->belongsTo(Annee::class, 'id_annee');
    }
}
