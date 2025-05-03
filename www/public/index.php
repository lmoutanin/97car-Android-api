<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Psr\Http\Server\RequestHandlerInterface;

$app = AppFactory::create();

// Middleware d'erreurs
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Inclure les routes et les exécuter correctement
$routes = require __DIR__ . '/../src/routes.php';
$routes($app); // 💡 Ici, on exécute la fonction qui enregistre les routes

// Inclure le client et l'exécuter correctement
$client = require __DIR__ . '/../src/Controller/client.php';
$client($app); // 💡 Ici, on exécute la fonction qui enregistre les routes du client


// Inclure la facture et exécuter correctement
$facture = require __DIR__ . '/../src/Controller/facture.php';
$facture($app); // 💡 Ici, on exécute la fonction qui enregistre les routes de la facture


// Inclure la voiture et exécuter correctement
$voiture = require __DIR__ . '/../src/Controller/voiture.php';
$voiture($app); // 💡 Ici, on exécute la fonction qui enregistre les routes du voiture




// Middleware pour gérer les CORS
$app->add(function (Request $request, RequestHandlerInterface $handler): Response {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

// Exécuter l'application
$app->run();