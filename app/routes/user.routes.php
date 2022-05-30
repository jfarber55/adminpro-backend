<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    /****************************************************************************
    * U S E R S
    *****************************************************************************/
    /* Get all Users */
    $app->get('/users/all', function (Request $request, Response $response) {
        $sql = "SELECT username, email, active FROM users";
    
        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->query($sql);
            $users = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
        
            $data = json_encode([
                'recordCount' => $stmt->rowCount(), 
                'data' => $users
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
    
    /* Get all Users With Assigned Roles */
    $app->get('/users/roles', function (Request $request, Response $response) {
        $sql = "SELECT username, email, userId, roleId, r.role, u.active ";
        $sql.= "FROM users u ";
        $sql.= "LEFT JOIN user_roles ur   ON u.id = ur.userId ";
        $sql.= "LEFT JOIN roles r         ON ur.roleId = r.id";
    
        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->query($sql);
            $users = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
        
            $data = json_encode([
                'recordCount' => $stmt->rowCount(),
                'data' => $users
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
    
    /* Activate a User */
    $app->post('/users/activate', function (Request $request, Response $response, array $args) {
        $data = $request->getParsedBody();
        $id = $data["id"];
       
        $sql = "UPDATE users SET active = 1 WHERE id = $id";
       
        try {
          $db = new Conn();
          $conn = $db->connect();
         
          $stmt = $conn->prepare($sql);
          $stmt->bindParam(':id', $id);
          
          $result = $stmt->execute();
       
          $db = null;
          $response->getBody()->write(json_encode($result));
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
      
      /* Deactivate A User */
      $app->post('/users/deactivate', function (Request $request, Response $response, array $args) {
        $data = $request->getParsedBody();
        $id = $data["id"];
       
        $sql = "UPDATE users SET active = 0 WHERE id = $id";
       
        try {
          $db = new Conn();
          $conn = $db->connect();
         
          $stmt = $conn->prepare($sql);
          $stmt->bindParam(':id', $id);
      
          $result = $stmt->execute();
       
          $db = null;
          $response->getBody()->write(json_encode($result));
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

    /* GET One User */
    $app->get('/users/{id}', function (Request $request, Response $response, $args) {
        $id = $args['id'];
    
        $sql = "SELECT username, email, active FROM users WHERE id = '$id' LIMIT 1";
    
        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->query($sql);
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            $db = null;
        
            $data = json_encode([
                'emptyData' => (strlen($user->username) > 0 ? false : true),
                'data' => $user
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
    
    /* Get Roles from User */
    $app->get('/users/{id}/roles', function (Request $request, Response $response, $args) {
        $id = $args['id'];
    
        $sql = "SELECT  r.id, r.role, u.username, u.email, u.active ";
        $sql.= "FROM    roles r ";
        $sql.= "INNER JOIN user_roles ur ";
        $sql.= "    ON  r.id = ur.roleId ";
        $sql.= "INNER JOIN users u ";
        $sql.= "    ON ur.userId = u.id ";
        $sql.= "WHERE   ur.userId = '$id' ";
    
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

    /* Create New User */
    $app->post('/users', function (Request $request, Response $response) {
        $data = $request->getParsedBody();
        $username = $data['username'];
        $email = $data['email'];
        $password = $data['password'];
        $active = $data['active'] ? $data['active'] : 0;
        // $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
        $sql =  "INSERT INTO users (username, email, password, active)  ";
        $sql .= "VALUES ('$username', '$email', '$password', '$active') ";
    
        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->prepare($sql);        
            $result = $stmt->execute();
            $db = null;
        
            // echo "Se actualizó correctamente la información.";
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('content-type', 'application/json')
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

    /* Update Hash Password */
    $app->put('/users/update/{id}', function (Request $request, Response $response, array $args) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $username = $data['username'];
        $password = $data['password'];
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
        $sql =  "UPDATE users ";
        $sql .= "SET password_hash = '$password_hash' ";
        $sql .= "WHERE  username = '$username' ";
        $sql .= "   AND password = '$password' ";
    
        try {
            $db = new Conn();
            $conn = $db->connect();
        
            $stmt = $conn->prepare($sql);
        
            $result = $stmt->execute();
        
            $db = null;
        
            // echo "Se actualizó correctamente la información.";
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('content-type', 'application/json')
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
