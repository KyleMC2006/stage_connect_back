<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Annee; 

class AnneeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $annees = [
            'L1',
            'L2',
            'L3',
            'M1',
            'M2',
            'Doctorat',
            'Année Préparatoire',
        ];

        foreach ($annees as $libannee) {
            
            Annee::firstOrCreate(['libannee' => $libannee]);
        }
    }
}
