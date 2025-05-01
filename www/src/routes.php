<?php

use Slim\App;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

return function (App $app) {
    require_once __DIR__ . '/db.php'; // Connexion à la base de données


    // Route racine avec documentation
    $app->get('/', function (Request $request, Response $response) {
        $docs = [
            'title' => 'API Documentation',
            'version' => '1.0.0',
            'endpoints' => [
                'clients' => [
                    'GET /clients' => 'Récupérer tous les clients',
                    'GET /clients/{id}' => 'Récupérer un client spécifique',
                    'POST /clients' => 'Créer un nouveau client'
                ],
                'factures' => [
                    'GET /factures?client_id={id}' => 'Récupérer les factures d\'un client',
                    'GET /factures/{id}' => 'Récupérer les détails d\'une facture',
                    'POST /factures' => 'Créer une nouvelle facture'
                ]
            ],
            'exemples' => [
                'création client' => [
                    'méthode' => 'POST /clients',
                    'body' => [
                        'nom' => 'Dupont',
                        'prenom' => 'Jean',
                        'telephone' => '0123456789',
                        'mel' => 'jean.dupont@email.com',
                        'adresse' => '123 rue Example',
                        'code_postal' => '75000',
                        'ville' => 'Paris'
                    ]
                ],
                'création facture' => [
                    'méthode' => 'POST /factures',
                    'body' => [
                        'id_client' => 1,
                        'id_voiture' => 1,
                        'date' => '2024-02-22'
                    ]
                ]
            ]
        ];

        $response->getBody()->write(json_encode($docs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json');
    });


};