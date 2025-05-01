<?php

use Slim\App;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

return function (App $app) {
    require_once __DIR__ . '/../db.php'; // Connexion à la base de données

    global $pdo;  // Déclarer que nous utilisons la variable globale $pdo
 
    // === CLIENTS ===
    $app->get('/clients', function (Request $request, Response $response) use ($pdo) {
        $stmt = $pdo->query("SELECT * FROM client");
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($clients));
        return $response->withHeader('Content-Type', 'application/json');
    });


     
    $app->get('/clients/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM client WHERE id_client = ?");
        $stmt->execute([$args['id']]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$client) {
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json')
                ->getBody()->write(json_encode(["message" => "Client non trouvé"]));
        }
        $response->getBody()->write(json_encode($client));
        return $response->withHeader('Content-Type', 'application/json');
    });


    $app->post('/clients', function (Request $request, Response $response) use ($pdo) {
        // Lire le corps de la requête
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);

        // Vérifier si le JSON est valide
        if (json_last_error() !== JSON_ERROR_NONE) {
            $response->getBody()->write(json_encode(["error" => "JSON invalide"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Vérifier la présence des champs obligatoires
        if (!isset($data['nom']) || !isset($data['prenom'])) {
            $response->getBody()->write(json_encode(["error" => "Données manquantes pour 'nom' ou 'prenom'"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Exécuter la requête SQL
        $stmt = $pdo->prepare("INSERT INTO client (nom, prenom, telephone, mel, adresse, code_postal, ville) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['nom'],
            $data['prenom'],
            $data['telephone'] ?? null,
            $data['mel'] ?? null,
            $data['adresse'] ?? null,
            $data['code_postal'] ?? null,
            $data['ville'] ?? null
        ]);

        $response->getBody()->write(json_encode(["message" => "Client ajouté"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    });















 
};
