<?php

namespace App\Controllers;

class EtudiantController extends BaseController {
    public function lister(): array
    {
        $etudiant=[["Nom"=>"Mia","age"=>14]];
        return view('listeEtudiants',['etudiant'=>$etudiant]);
    }
}