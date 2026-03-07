<?php
// Usamos JSON como formato de respuesta
header("Content-Type: application/json; charset=UTF-8");

// 1. Capturamos la ruta exacta ignorando cosas extra (como ?id=5)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// 2. Cortamos esa ruta en un array de trocitos usando la barra '/
$uriParts = explode('/', $uri);
// 3. Capturamos el método HTTP
$metodo = $_SERVER['REQUEST_METHOD'];
// 4. Capturamos el recurso solicitado (si existe)
$recurso = isset($uriParts[3]) ? $uriParts[3] : null;
// 5. Devolvemos un JSON con la info que hemos capturado para comprobar que todo funciona
echo json_encode([
    "metodo_usado" => $metodo,
    "recurso_solicitado" => $recurso
]);

// 6. El enrutador: decidimos a qué controlador llamar
switch ($recurso) {
    case 'productos':
        echo " ¡Has llegado a la seccion de productos!";
        break;

    case 'clientes':
        echo " ¡Has llegado a la seccion de clientes!";
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
