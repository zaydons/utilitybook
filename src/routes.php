<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Create Slim app
$app = AppFactory::create();

// SQLite connection
$pdo = new PDO('sqlite:' . __DIR__ . '/../database/address_book.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create the database table if not exists
$pdo->exec("
    CREATE TABLE IF NOT EXISTS contacts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL
    )
");

// Middleware to parse JSON requests
$app->addBodyParsingMiddleware();

// Get all contacts
$app->get('/contacts', function (Request $request, Response $response) use ($pdo) {
    $stmt = $pdo->query("SELECT * FROM contacts");
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($contacts));
    return $response->withHeader('Content-Type', 'application/json');
});

// Get a single contact by ID
$app->get('/contacts/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $id = $args['id'];
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($contact) {
        $response->getBody()->write(json_encode($contact));
        return $response->withHeader('Content-Type', 'application/json');
    } else {
        $response->getBody()->write(json_encode(['error' => 'Contact not found']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
});

// Create a new contact
$app->post('/contacts', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();

    $stmt = $pdo->prepare("INSERT INTO contacts (name, email, phone) VALUES (:name, :email, :phone)");
    $stmt->execute([
        ':name' => $data['name'] ?? '',
        ':email' => $data['email'] ?? '',
        ':phone' => $data['phone'] ?? '',
    ]);

    $newContactId = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = :id");
    $stmt->execute([':id' => $newContactId]);
    $newContact = $stmt->fetch(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($newContact));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

// Update an existing contact
$app->put('/contacts/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $id = $args['id'];
    $data = $request->getParsedBody();

    $stmt = $pdo->prepare("UPDATE contacts SET name = :name, email = :email, phone = :phone WHERE id = :id");
    $stmt->execute([
        ':name' => $data['name'] ?? '',
        ':email' => $data['email'] ?? '',
        ':phone' => $data['phone'] ?? '',
        ':id' => $id,
    ]);

    if ($stmt->rowCount() === 0) {
        $response->getBody()->write(json_encode(['error' => 'Contact not found or no changes made']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $updatedContact = $stmt->fetch(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($updatedContact));
    return $response->withHeader('Content-Type', 'application/json');
});

// Delete a contact
$app->delete('/contacts/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $id = $args['id'];

    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = :id");
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        $response->getBody()->write(json_encode(['error' => 'Contact not found']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $response->getBody()->write(json_encode(['message' => 'Contact deleted']));
    return $response->withHeader('Content-Type', 'application/json');
});

// Run the app
$app->run();
