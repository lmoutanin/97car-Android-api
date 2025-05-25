<?php

use Slim\App;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

return function (App $app) {
    require_once __DIR__ . '/../db.php'; // Connexion à la base de données

    global $pdo;  // Déclarer que nous utilisons la variable globale $pdo
 
     // === FACTURES ===
     $app->get('/factures', function (Request $request, Response $response) use ($pdo) {
        $queryParams = $request->getQueryParams();
    
        if (!isset($queryParams['client_id'])) {
            $response->getBody()->write(json_encode(["message" => "Paramètre client_id requis"]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    
        $client_id = (int) $queryParams['client_id'];
    
        $stmt = $pdo->prepare("SELECT * FROM facture WHERE client_id = ?");
        $stmt->execute([$client_id]);
        $factures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        if (!$factures) {
            $response->getBody()->write(json_encode(["message" => "Aucune facture trouvée pour ce client"]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    
        $response->getBody()->write(json_encode($factures));
        return $response->withHeader('Content-Type', 'application/json');
    });
    


    $app->get('/factures/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
        $id = $args['id'];

        $stmt = $pdo->prepare("
            SELECT 
                f.id_facture AS facture_id, f.date AS date_facture, f.montant,
                c.id_client, c.nom AS client_nom, c.prenom AS client_prenom, 
                c.telephone AS client_telephone, c.mel AS client_email, 
                c.adresse AS client_adresse, c.code_postal AS client_code_postal, c.ville AS client_ville,
                v.id_voiture, v.marque, v.modele, v.annee, v.immatriculation, v.kilometrage,
                tr.id_reparation, tr.description AS reparation_description, 
                tr.cout AS reparation_cout, ftr.quantite AS reparation_quantite
            FROM facture f
            JOIN client c ON f.client_id = c.id_client 
            JOIN voiture v ON f.voiture_id = v.id_voiture
            LEFT JOIN facture_type_reparation ftr ON f.id_facture = ftr.id_facture
            LEFT JOIN type_reparation tr ON ftr.id_reparation = tr.id_reparation
            WHERE f.id_facture = ?
        ");

        $stmt->execute([$id]);
        $factureData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$factureData) {
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json')
                ->getBody()->write(json_encode(["message" => "Facture non trouvée"]));
        }

        // Structurer la réponse
        $facture = [
            "facture_id" => $factureData[0]['facture_id'],
            "date_facture" => $factureData[0]['date_facture'],
            "montant" => $factureData[0]['montant'],
            "client" => [
                "id_client" => $factureData[0]['id_client'],
                "nom" => $factureData[0]['client_nom'],
                "email" => $factureData[0]['client_email'],
                "telephone" => $factureData[0]['client_telephone']
            ],
            "voiture" => [
                "id" => $factureData[0]['id_voiture'],
                "marque" => $factureData[0]['marque'],
                "modele" => $factureData[0]['modele'],
                "annee" => $factureData[0]['annee']
            ],
            "reparations" => []
        ];

        foreach ($factureData as $row) {
            if (!empty($row['id_reparation'])) {
                $facture["reparations"][] = [
                    "id" => $row['id_reparation'],
                    "description" => $row['reparation_description'],
                    "cout" => $row['reparation_cout'],
                    "quantite" => $row['reparation_quantite']
                ];
            }
        }

        $response->getBody()->write(json_encode($facture));
        return $response->withHeader('Content-Type', 'application/json');
    });



    $app->post('/factures', function (Request $request, Response $response) use ($pdo)  {
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);
        $stmt = $pdo->prepare("INSERT INTO facture (date,client_id,voiture_id) VALUES (?, ?, ?)");
        $stmt->execute([$data['date'],$data['client_id'],$data['voiture_id'] ?? null]);
        $response->getBody()->write(json_encode(["message" => "Facture ajouté"]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/factureCompletes', function (Request $request, Response $response) use ($pdo)  {
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);
        $stmt = $pdo->query("SELECT MAX(id_facture) as max_id FROM facture");
        $maxId = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'];
        $stmt = $pdo->prepare("INSERT INTO facture_type_reparation (id_facture, id_reparation , quantite ) VALUES (?, ?, ?)");
        $stmt->execute([ $maxId,$data['id_reparation'],$data['quantite']]);
        $response->getBody()->write(json_encode(["message" => "Facture ajouté"]));
        return $response->withHeader('Content-Type', 'application/json');
    });


    $app->put('/putfactures/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
        $id = $args['id'];
        $data = $request->getParsedBody();
        $requestBody = $request->getBody()->getContents();
        $data = json_decode($requestBody, true);
        $stmt = $pdo->prepare("UPDATE facture SET date = ?, client_id = ?, voiture_id = ? , montant = ?  WHERE id_facture = ?");
        $stmt->execute([$data['date'],$data['client_id'],$data['voiture_id'],$data['montant'],$id]);
        $response->getBody()->write(json_encode(["message" => "Facture modifier"]));
        return $response->withHeader('Content-Type', 'application/json');
    });


    $app->delete('/dropfactures/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
        $id = $args['id'];
        $stmt = $pdo->prepare("DELETE FROM facture WHERE id_facture = ?");
        $stmt->execute([$id]);
        $response->getBody()->write(json_encode(["message" => "Facture supprimer"]));
        return $response->withHeader('Content-Type', 'application/json');
    });


};
