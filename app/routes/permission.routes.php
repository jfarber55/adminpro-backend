<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->get('/permissions', function (Request $request, Response $response) {
        $sql = "SELECT id, permission, description FROM permissions";

        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->query($sql);
            $roles = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;

            $data = json_encode([
                'recordCount' => $stmt->rowCount(),
                'data' => $roles
            ]);

            $response->getBody()->write($data);
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } catch (PDOException $e) {
            $error = array(
                "message" => $e->getMessage()
            );

            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    });
};
