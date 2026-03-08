<?php
// Usamos JSON como formato de respuesta
header("Content-Type: application/json; charset=UTF-8");

require_once 'config/database.php';
require_once 'controllers/ProductoController.php';
require_once 'controllers/ClienteController.php';

$database = new Database();
$db = $database->getConnection();

// 1. Capturamos la ruta exacta ignorando cosas extra (como ?id=5)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// 2. Cortamos esa ruta en un array de trocitos usando la barra '/
$uriParts = explode('/', $uri);
// 3. Capturamos el método HTTP
$metodo = $_SERVER['REQUEST_METHOD'];
// 4. Capturamos el recurso solicitado (si existe)
$recurso = isset($uriParts[3]) ? $uriParts[3] : null;

// 5. El enrutador: decidimos a qué controlador llamar
switch ($recurso) {
    case 'productos':
        // Creamos el controlador pasándole la conexión
        $productoController = new ProductoController($db);
        // Capturamos la acción solicitada (si existe)
        $accion = isset($uriParts[4]) ? $uriParts[4] : null;
        // Llamamos al método del controlador que procesa la petición
        $productoController->procesarPeticion($metodo, $accion);
        break;

    case 'clientes':
        // Creamos el controlador pasándole la conexión
        $clienteController = new ClienteController($db);
        // Capturamos la acción solicitada (si existe)
        $accion = isset($uriParts[4]) ? $uriParts[4] : null;
        // Llamamos al método del controlador que procesa la petición   
        $clienteController->procesarPeticion($metodo, $accion);
        break;

    case 'pedidos':
        echo " ¡Has llegado a la seccion de pedidos!";
        break;

    default:
        // Si piden algo que no existe, devolvemos un error 404 (Not Found)
        http_response_code(404);
        echo json_encode(["error" => "Ruta no encontrada"]);
        break;
}
