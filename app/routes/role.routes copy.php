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
        $sql = "SELECT id, role FROM roles";
    
        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->query($sql);
            $roles = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
        
            $data = json_encode(
                [
                'recordCount' => $stmt->rowCount(),
                'data' => $roles
                ]
            );
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

    /* Get all Roles */
    $app->get('/roles', function (Request $request, Response $response) {
        $sql = "SELECT id, role FROM roles WHERE id = '$id' AND active = 1";
    
        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->query($sql);
            $roles = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
        
            $data = json_encode(
                [
                'recordCount' => $stmt->rowCount(),
                'data' => $roles
                ]
            );
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
  
    $app->get('/roles/{id}/permissions', function (Request $request, Response $response, $args) {
        $id = $args['id'];
    
        $sql = "SELECT r.id roleid, r.role, rp.permission_id, p.permission, p.description permission_description ";
        $sql.= "FROM roles r ";
        $sql.= "LEFT JOIN role_permissions rp ON r.id = rp.role_id ";
        $sql.= "LEFT JOIN permissions p       ON rp.permission_id = p.id ";
        $sql.= "WHERE r.id = '$id' ";
        $sql.= "ORDER BY r.id, p.id ";
    
        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->query($sql);
            $roles = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
        
            // $response->getBody()->write(json_encode($stmt[0]));
            // return $response
            // ->withHeader('content-type', 'application/json')
            // ->withStatus(200);
        
            $emptyData = true;
            if (count($stmt)) {
                $emptyData = false;
                $roles = array();
        
                $i = 0;
                $reg = $stmt[$i];
                $roleId = $reg->roleid;
                $roleName = $reg->role;
                $perms = array();
                if ($stmt[$i]->permission_id) {
                while ($i < count($stmt)) {
                    $obj = new PermissionClass();
                    $obj->id = $stmt[$i]->permission_id;
                    $obj->permission = $stmt[$i]->permission;
                    $obj->description = $stmt[$i]->permission_description;
                    array_push($perms, $obj);
                
                    $i ++;
                }
                }
                $objRole = new RoleClass();
                $objRole->id = $roleId;
                $objRole->role = $roleName;
                $objRole->permissions = $perms;
                array_push($roles, $objRole);
            }
            $data = json_encode(
                [
                'emptyData' => $emptyData,
                'data' => $roles
                ]
            );
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

    $app->get('/roles{id}/permToAssign', function(Request $request, Response $response, $args) {
        $id = $args['id'];
    
        $sql = "SELECT r.id roleid, r.role, rp.permission_id, p.permission, p.description permission_description ";
        $sql.= "From permissions pp ";
        $sql.= "Where pp.id NOT IN (Select p.id ";
        $sql.= "From role_permissions rp ";
        $sql.= "INNER JOIN permissions p ON rp.permission_id = p.id ";
        $sql.= "WHERE rp.role_id = 1) ";
    
        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->query($sql);
            $resp = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
        
            // $response->getBody()->write(json_encode($resp));
            // return $response
            //   ->withHeader('content-type', 'application/json')
            //   ->withStatus(200);
        
            $emptyData = true;
            if (count($resp)) {
                $emptyData = false;
                $roles = array();
        
                $i = 0;
                $reg = $resp[$i];
                $roleId = $reg->roleid;
                $roleName = $reg->role;
                $perms = array();
                if ($resp[$i]->permission_id) {
                while ($i < count($resp)) {
                    $obj = new PermissionClass();
                    $obj->id = $resp[$i]->permission_id;
                    $obj->permission = $resp[$i]->permission;
                    $obj->description = $resp[$i]->permission_description;
                    array_push($perms, $obj);
                
                    $i ++;
                }
                }
                $objRole = new RoleClass();
                $objRole->id = $roleId;
                $objRole->role = $roleName;
                $objRole->permissions = $perms;
                array_push($roles, $objRole);
            }
            $data = json_encode(
                [
                'emptyData' => $emptyData,
                'data' => $roles
                ]
            );
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
        
            $data = json_encode(
                [
                'recordCount' => $stmt->rowCount(),
                'data' => $users
                ]
            );
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
  
    $app->get('/roles/permissions', function (Request $request, Response $response) {
        $sql = "SELECT r.id roleid, r.role, rp.permission_id, p.permission, p.description permission_description ";
        $sql.= "FROM roles r ";
        $sql.= "LEFT JOIN role_permissions rp ON r.id = rp.role_id ";
        $sql.= "LEFT JOIN permissions p       ON rp.permission_id = p.id ";
        $sql.= "ORDER BY r.id, p.id";
    
        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->query($sql);
            $resp = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
        
            $roles = array();
            $records = 0;
            $i = 0;
            while ($i < count($resp)) {
                $reg = $resp[$i];
                while ($reg->roleid == $resp[$i]->roleid) {
                    $roleId = $reg->roleid;
                    $roleName = $reg->role;
            
                    $perms = array();
                    if ($resp[$i]->permission_id) {
                        while ($resp[$i]->permission_id && $reg->roleid == $resp[$i]->roleid) {
                            $obj = new PermissionClass();
                            $obj->id = $resp[$i]->permission_id;
                            $obj->permission = $resp[$i]->permission;
                            $obj->description = $resp[$i]->permission_description;
                            //$json = json_encode($obj);
                            array_push($perms, $obj);
                
                            $i ++;
                        }
                        $objRole = new RoleClass();
                        $objRole->id = $roleId;
                        $objRole->role = $roleName;
                        $objRole->permissions = $perms;
                        // $json = json_encode($objRole);
                        // array_push($roles, $json);
                        array_push($roles, $objRole);
                        $records ++;
                    } else {
                        $objRole = new stdClass();
                        $objRole->id = $roleId;
                        $objRole->role = $roleName;
                        $objRole->permissions = $perms;
                        // $json = json_encode($objRole);
                        // array_push($roles, $json);
                        array_push($roles, $objRole);
                        $i ++;
                        $records ++;
                    }
                }
            }
        
            $data = json_encode(
                [
                'recordCount' => $records,
                'data' => $roles
                ]
            );
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
        
        $sql = "INSERT INTO roles (name) VALUES ('$role')";
        
        try {
            $db = new Conn();
            $conn = $db->connect();
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute();
            $db = null;
        
            $resp = new stdClass();
            $resp->saved = true;
            
            $data = json_encode(
                [
                'message' => "Se actualizó correctamente la información.",
                'data' => $resp
                ]
            );
            
            //echo "Se actualizó correctamente la información.";
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('content-type', 'application/json')
                ->withStatus(200);
            
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
