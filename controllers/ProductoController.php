<?php

class ProductoController
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Método para procesar la petición según el método HTTP y la acción solicitada
    public function procesarPeticion($metodo, $accion)
    {
        switch ($metodo) {
            case 'GET':
                if ($accion == 'listar' || $accion == null) {
                    echo "Listando productos...";
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Acción no encontrada"], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'POST':
                if ($accion == 'crear' || $accion == null) {
                    echo "Creando producto...";
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Acción no encontrada"], JSON_UNESCAPED_UNICODE);
                }
                break;

            default:
                http_response_code(405); // Método no permitido
                echo json_encode(["error" => "Método HTTP no permitido"], JSON_UNESCAPED_UNICODE);
                break;
        }
    }
}
