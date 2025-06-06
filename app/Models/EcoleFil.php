<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EcoleFil extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_filiere', 
        'id_etablissement'
    ];
    
    protected $table = 'ecole_fils';

    public function filiere()
    {
        return $this->belongsTo(Filiere::class, 'id_filiere');
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'id_etablissement');
    }
}
