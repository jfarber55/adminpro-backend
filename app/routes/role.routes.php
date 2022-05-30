<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    /* Get all Roles */
    $app->get('/roles', function (Request $request, Response $response) {
        $sql = "SELECT id, role FROM roles ORDER BY role";

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

    /* Get All Roles with All Permisions */
    $app->get('/roles/all/permissions', function (Request $request, Response $response) {
        $sql = "SELECT r.id roleid, r.role, rp.permission_id, p.permission, p.description permission_description ";
        $sql.= "FROM roles r ";
        $sql.= "LEFT JOIN role_permissions rp ON r.id = rp.role_id ";
        $sql.= "LEFT JOIN permissions p       ON rp.permission_id = p.id ";
        $sql.= "ORDER BY r.id, p.id";

        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->query($sql);
            $rp = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;

            $response->getBody()->write(json_encode($rp));
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
    
    // Get A Role With All Permisions
    $app->get('/roles/{id}/permissions', function (Request $request, Response $response, $args) {
        $id = $args['id'];

        $sql = "SELECT p.id, p.permission, p.description ";
        $sql.= "FROM role_permissions rp ";
        $sql.= "INNER JOIN permissions p ON rp.permission_id = p.id ";
        $sql.= "WHERE rp.role_id = '$id' ";
        $sql.= "ORDER BY p.permission ";

        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->query($sql);
            $resp = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;

            $emptyData = false;
            $data = json_encode([
                'emptyData' => $emptyData,
                'data' => $resp
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

    // Get A Role With Permissions To Assging 
    $app->get('/roles/{id}/permToAssign', function(Request $request, Response $response, $args) {
        $id = $args['id'];
    
        $sql = "Select p.id, p.permission, p.description ";
        $sql.= "From permissions p ";
        $sql.= "WHERE p.id NOT IN ( ";
        $sql.= "    SELECT rp.permission_id ";
        $sql.= "    FROM role_permissions rp ";
        $sql.= "    WHERE rp.role_id = '$id' ) ";
    
        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->query($sql);
            $resp = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
        
            $emptyData = flase;
            $data = json_encode([
                'emptyData' => $emptyData,
                'data' => $resp
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

    /* Get All Roles With All Related Users */
    $app->get('/roles/users', function (Request $request, Response $response) {
        $sql = "SELECT r.role, roleId, userId, username ";
        $sql.= "FROM roles r ";
        $sql.= "LEFT JOIN user_roles ur   ON r.id = ur.roleId ";
        $sql.= "LEFT JOIN users u         ON ur.userId = u.id";

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

    /* Get A Role With All Related Users */
    $app->get('/roles/{id}/users', function (Request $request, Response $response, $args) {
        $id = $args['id'];
        
        $sql = "SELECT r.role, roleId, userId, username ";
        $sql.= "FROM roles r ";
        $sql.= "INNER JOIN user_roles ur ";
        $sql.= "    ON r.id = ur.roleId ";
        $sql.= "INNER JOIN users u ";
        $sql.= "    ON ur.userId = u.id ";
        $sql.= "WHERE r.id = '$id'";

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

    /* Get A Role Info */
    $app->get('/roles/{id}', function (Request $request, Response $response, $args) {
        $id = $args['id'];
        $sql = "SELECT id, role FROM roles WHERE id = '$id' AND active = 1";
    
        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->query($sql);
            $role = $stmt->fetch(PDO::FETCH_OBJ);
            $db = null;
        
            $data = json_encode([
                'emtpyData' => false,
                'data' => $role
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

    /* Save New Role */
    $app->post('/roles', function (Request $request, Response $response) {
        $data = $request->getParsedBody();
        $role = $data['role'];
        
        $sql = "INSERT INTO roles (role) VALUES ('$role')";
        
        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute();
            $db = null;

            $resp = new stdClass();
            $resp->saved = true;
            
            $data = json_encode([
                'message' => "Se actualizó correctamente la información.",
                'data' => $resp
            ]);
            
            //echo "Se actualizó correctamente la información.";
            // $response->getBody()->write(json_encode($result));
            // return $response->withHeader('content-type', 'application/json')
            //     ->withStatus(200);
            
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

    /* Update Role Info */
    $app->post('/roles/{id}', function (Request $request, Response $response, $args) {
        $id = $args['id'];
        $data = $request->getParsedBody();
        $role = $data['role'];
        
        $sql = "UPDATE roles SET role = '$role' WHERE id = '$id'";
        
        $resp = new stdClass();
        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute();
            $db = null;

            $resp->saved = true;
            
            $data = json_encode([
                'message' => "Se actualizó correctamente la información.",
                'data' => $resp,
                'request' => $data,
                'args' => $args
            ]);
            
            //echo "Se actualizó correctamente la información.";
            // $response->getBody()->write(json_encode($result));
            // return $response->withHeader('content-type', 'application/json')
            //     ->withStatus(200);
            
            $response->getBody()->write($data);
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } catch (PDOException $e) {
            $resp->saved = false;
            $error = array(
                "message" => $e->getMessage(),
                "data" => $resp,
                "request" => $request
            );

            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    });
};