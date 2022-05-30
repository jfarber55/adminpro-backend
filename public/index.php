<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

class Conn
{
    private $host = 'localhost';
    private $user = 'root';
    private $pass = 'r00t';
    private $dbname = 'resort';

    public function connect()
    {
        $conn_str = "mysql:host=$this->host;port=3306;dbname=$this->dbname";
        $conn = new PDO($conn_str, $this->user, $this->pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    }
}

class PermissionClass {
  public $id;
  public $permission;
  public $description;
}

class RoleClass {
  public $id;
  public $role;
  public $permissions = array();
}

$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response
          ->withHeader('Access-Control-Allow-Origin', 'http://localhost:4200')
          ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
          ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Parse json, form data and xml
$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();

$app->options('/{routes:.*}', function (Request $request, Response $response) {
	// CORS Pre-Flight OPTIONS Request Handler
	return $response;
});

/*
 AUTHORIZATION ROUTES 
*/
$authRoute = require __DIR__ . '/../app/routes/auth.routes.php';
$authRoute($app);

/*
PERMISSION ROUTES
*/
$permissionRoutes = require __DIR__ . '/../app/routes/permission.routes.php';
$permissionRoutes($app);

/*
ROLE ROUTES
*/
$roleRoutes = require __DIR__ . '/../app/routes/role.routes.php';
$roleRoutes($app);

/*
USER ROUTES
*/
$userRoutes = require __DIR__ . '/../app/routes/user.routes.php';
$userRoutes($app);

/***************************************************************************
* R E S O R T S
****************************************************************************/

/* Get All Resorts */
// $app->get('/resorts/all', function (Request $request, Response $response) {
//   $sql = "SELECT * FROM resorts";
 
//   try {
//     $db = new Conn();
//     $conn = $db->connect();
//     $stmt = $conn->query($sql);
//     $resorts = $stmt->fetchAll(PDO::FETCH_OBJ);
//     $db = null;
   
//     $response->getBody()->write(json_encode($resorts));
//     return $response
//       ->withHeader('content-type', 'application/json')
//       ->withStatus(200);
//   } catch (PDOException $e) {
//     $error = array(
//       "message" => $e->getMessage()
//     );
 
//     $response->getBody()->write(json_encode($error));
//     return $response
//       ->withHeader('content-type', 'application/json')
//       ->withStatus(500);
//   }
// });

/* Add New Resort */
// $app->post('/resorts/store', function (Request $request, Response $response, array $args) {
//   $data = $request->getParsedBody();
//   $name = $data["name"];
//   $description = $data["description"];

//   $sql = 'INSERT INTO resorts (`name`, `description`) VALUES (:name, :description)';
 
//   try {
//     $db = new Conn();
//     $conn = $db->connect();
   
//     $stmt = $conn->prepare($sql);
//     $stmt->bindParam(':name', $name);
//     $stmt->bindParam(':description', $description);
//     $result = $stmt->execute();
 
//     $db = null;
//     $response->getBody()->write(json_encode($result));
//     return $response
//       ->withHeader('content-type', 'application/json')
//       ->withStatus(200);
//   } catch (PDOException $e) {
//     $error = array(
//       "message" => $e->getMessage()
//     );
 
//     $response->getBody()->write(json_encode($error));
//     return $response
//       ->withHeader('content-type', 'application/json')
//       ->withStatus(500);
//   }
// });

/* 
  Estas ultimas líneas tienen que ir para manejo de errores, no aparecen muy bonito que
  digamos pero maneja las excepciones.
*/
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
  throw new HttpNotFoundException($request);
});

/* Para que funione toda la api y mis rutas que vaya poniendo. */
$app->run();