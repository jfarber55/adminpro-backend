<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    // Ruta de prueba
    $app->get('/', function (Request $request, Response $response) {
        $objRole = new stdClass();
        $objRole->ok = true;
        $objRole->msg = 'Hello world !!';
        $data = json_encode($objRole);
        $response->getBody()->write($data);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    });

    // Esta es una Ruta para hacer un clásico Hello World!!!
    // Y comprobar que mi api funciona correctamente.
    $app->get('/hello/{name}', function (Request $request, Response $response, $args) {
        // $data = json_encode(['hellos' => 'world'], JSON_PRETTY_PRINT);
        $data = json_encode(['This is the classic Hello World... So... Hello ' => $args['name']]);
        $response->getBody()->write($data);
        return $response->withHeader('Content-Type', 'application/json');
    });

    /* GET Authorization User */
    $app->post('/authUser', function (Request $request, Response $response) {
        $data = $request->getParsedBody();
        $username = $data['username'];
        $password = $data['password'];
    
        $sql = "SELECT username, password_hash FROM users WHERE username = '$username'";
        
        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->query($sql);
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            $db = null;
        
            $data = json_encode([
                'emptyData' => (strlen($user->username) > 0 ? false : true),
                'data' => [
                    'username' => $username,
                    'password' => $password,
                    'auth' => password_verify($password, $user->password_hash),
                ]
            ]);
            $response->getBody()->write($data);
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } catch (PDOException $e) {
            $error = array("message" => $e->getMessage());
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    });

};
