<?php

use Slim\App;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

return function (App $app) {
    require_once __DIR__ . '/../db.php'; // Connexion à la base de données

    global $pdo;  // Déclarer que nous utilisons la variable globale $pdo
 
    // === voitures ===
    $app->get('/voitures', function (Request $request, Response $response) use ($pdo) {
        $stmt = $pdo->query("SELECT * FROM voiture");
        $voitures = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($voitures));
        return $response->withHeader('Content-Type', 'application/json');
    });


     
    $app->get('/voitures/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM voiture WHERE id_voiture = ?");
        $stmt->execute([$args['id']]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$client) {
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json')
                ->getBody()->write(json_encode(["message" => "Client non trouvé"]));
        }
        $response->getBody()->write(json_encode($client));
        return $response->withHeader('Content-Type', 'application/json');
    });


    

    $app->post('/voitures', function (Request $request, Response $response) use ($pdo) {
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);
        $stmt = $pdo->prepare('INSERT INTO "voiture" ("annee", "marque", "modele", "immatriculation", "kilometrage", "client_id")
         VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['annee'],$data['marque'],$data['modele'],$data['immatriculation'],$data['kilometrage'],$data['client_id']]);
        $response->getBody()->write(json_encode(["message" => "Voiture ajoutée"]));
        return $response->withHeader('Content-Type', 'application/json');
    });


    $app->put('/putvoitures/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
        $id = $args['id'];
        $data = $request->getParsedBody();
        $requestBody = $request->getBody()->getContents();
        $data = json_decode($requestBody, true);
        $stmt = $pdo->prepare("UPDATE voiture SET annee = ?, marque = ?, modele = ? , immatriculation = ? , kilometrage = ? ,client_id = ?  WHERE id_voiture = ?");
        $stmt->execute([$data['annee'],$data['marque'],$data['modele']  ,$data['immatriculation']  ,$data['kilometrage']  ,$data['client_id'] ,$id]);
        $response->getBody()->write(json_encode(["message" => "Voiture modifier"]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    

    $app->delete('/dropvoitures/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
        $id = $args['id'];
        $stmt = $pdo->prepare("DELETE FROM voiture WHERE id_voiture = ?");
        $stmt->execute([$id]);
        $response->getBody()->write(json_encode(["message" => "Voiture supprimer"]));
        return $response->withHeader('Content-Type', 'application/json');
    });
 
};