<?php

use Slim\App;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

return function (App $app) {
    require_once __DIR__ . '/../db.php'; // Connexion à la base de données

    global $pdo;  // Déclarer que nous utilisons la variable globale $pdo
 
    // === VOITURES ===   
    $app->get('/voitures', function (Request $request, Response $response) use ($pdo) {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['client_id'])) {
            $response->getBody()->write(json_encode(["message" => "Paramètre client_id requis"]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $client_id = (int) $queryParams['client_id'];

        $stmt = $pdo->prepare("SELECT * FROM voiture WHERE client_id = ?");
        $stmt->execute([$client_id]);
        $voitures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$voitures) {
            $response->getBody()->write(json_encode(["message" => "Aucune voiture trouvée pour ce client"]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode($voitures));
        return $response->withHeader('Content-Type', 'application/json');
    });

     
    $app->get('/voitures/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
        $id = $args['id'];

        $stmt = $pdo->prepare("
            SELECT 
                id_voiture,annee,marque,modele,immatriculation,kilometrage,voiture.client_id,
                client.nom , client.prenom,
                facture.id_facture ,facture.date , facture.montant,
                type_reparation.id_reparation,type_reparation.description , type_reparation.cout,
                facture_type_reparation.quantite
            FROM voiture
            JOIN client ON voiture.client_id = client.id_client
            LEFT JOIN facture ON voiture.id_voiture = facture.id_facture
            LEFT JOIN facture_type_reparation ON facture.id_facture = facture_type_reparation.id_facture
            LEFT JOIN type_reparation ON facture_type_reparation.id_reparation = type_reparation.id_reparation
            WHERE voiture.id_voiture = ?
        ");

        $stmt->execute([$id]);
        $voitureData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$voitureData) {
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json')
                ->getBody()->write(json_encode(["message" => "Voiture non trouvée"]));
        }

        // Structurer la réponse
        $voiture = [
            "id_voiture" => $voitureData[0]["id_voiture"],
            "annee" => $voitureData[0]["annee"],
            "marque" => $voitureData[0]["marque"],
            "modele" => $voitureData[0]["modele"],
            "immatriculation" => $voitureData[0]["immatriculation"],
            "kilometrage" => $voitureData[0]["kilometrage"],
            "client" => [
                "id_client" => $voitureData[0]['client_id'],
                "nom" => $voitureData[0]['nom'],
                "prenom" => $voitureData[0]['prenom']
            ],
            "factures" => [],
            "reparations" =>[],
        ];
   
       $tbFactureId = array();
        foreach ($voitureData as $row){
            if(!empty($row['id_facture'])) {
                if(!in_array($row["id_facture"],$tbFactureId))
                $voiture["factures"][] = [
                    "id_facture" => $row["id_facture"],
                    "date" => $row["date"],
                    "montant" => $row["montant"]
                ];
                array_push($tbFactureId,$row["id_facture"]);
            }
        }

        foreach ($voitureData as $row){
            if(!empty($row['id_reparation'])) {
                $voiture["reparations"][] = [
                    "id_reparation" => $row["id_reparation"],
                    "description" => $row["description"],
                    "cout" => $row["cout"],
                    "quantite" => $row["quantite"]
                ];
                 
            }
        }

        $response->getBody()->write(json_encode($voiture));
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