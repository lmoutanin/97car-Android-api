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


    

    $app->post('/clients', function (Request $request, Response $response) use ($pdo)  {
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);
        $stmt = $pdo->prepare("INSERT INTO client (nom, prenom, telephone, mel, adresse, code_postal, ville) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['nom'],$data['prenom'],$data['telephone'] ?? null,$data['mel'] ?? null,$data['adresse'] ?? null,$data['code_postal'] ?? null,$data['ville'] ?? null]);
        $response->getBody()->write(json_encode(["message" => "Client ajouté"]));
        return $response->withHeader('Content-Type', 'application/json');
    });


    $app->put('/putclients/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
        $id = $args['id'];
        $data = $request->getParsedBody();
        $requestBody = $request->getBody()->getContents();
        $data = json_decode($requestBody, true);
        $stmt = $pdo->prepare("UPDATE client SET nom = ?, prenom = ?, telephone = ? , mel = ? , adresse = ? ,code_postal = ? , ville = ? WHERE id_client = ?");
        $stmt->execute([$data['nom'],$data['prenom'],$data['telephone'] ?? null ,$data['mel'] ?? null ,$data['adresse'] ?? null ,$data['code_postal'] ?? null ,$data['ville'] ?? null,$id]);
        $response->getBody()->write(json_encode(["message" => "Client modifier"]));
        return $response->withHeader('Content-Type', 'application/json');
    });
 

    

    $app->delete('/dropclients/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
        $id = $args['id'];
        $stmt = $pdo->prepare("DELETE FROM client WHERE id_client = ?");
        $stmt->execute([$id]);
        $response->getBody()->write(json_encode(["message" => "Client supprimer"]));
        return $response->withHeader('Content-Type', 'application/json');
    });
 
};
