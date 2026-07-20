<?php

namespace App\Controllers;

use App\Models\PrefixeModel;

class PrefixeController extends BaseController
{
    // 1. Afficher le formulaire et la liste des préfixes
    public function index()
    {
        $prefixeModel = new PrefixeModel();
        
        // On récupère tous les préfixes existants pour les afficher sous le formulaire
        $data['prefixes'] = $prefixeModel->findAll();

        return view('prefixe_view', $data);
    }

    // 2. Traiter la soumission du formulaire
    public function store()
    {
        $prefixeModel = new PrefixeModel();

        // Récupérer la valeur entrée dans le formulaire
        $nomPrefixe = $this->request->getPost('nom');

        // Petite validation rapide pour éviter les champs vides
        if (!empty($nomPrefixe)) {
            $prefixeModel->save([
                'nom' => $nomPrefixe
            ]);

            return redirect()->to('/prefixe')->with('success', 'Préfixe ajouté avec succès !');
        }

        return redirect()->back()->with('error', 'Le champ ne peut pas être vide.');
    }
}