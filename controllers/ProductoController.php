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
                    try {
                        $query = "SELECT id, nombre, descripcion, precio_actual, stock FROM productos";
                        $stmt = $this->conn->prepare($query);
                        $stmt->execute();
                        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($productos) > 0) {
                            http_response_code(200); // OK
                            echo json_encode($productos, JSON_UNESCAPED_UNICODE);
                        } else {
                            http_response_code(404); // Not Found
                            echo json_encode(["message" => "No se encontraron productos"], JSON_UNESCAPED_UNICODE);
                        }
                    } catch (PDOException $e) {
                        http_response_code(500); // Internal Server Error
                        echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
                    }
                } elseif (is_numeric($accion)) {
                    // Lógica para obtener un producto específico por ID
                    try {
                        // Preparamos la consulta SQL para obtener un producto por su ID
                        $query = "SELECT id, nombre, descripcion, precio_actual, stock FROM productos WHERE id = :id";
                        $stmt = $this->conn->prepare($query);

                        $stmt->bindParam(':id', $accion);
                        $stmt->execute();
                        // Usamos fetch() en singular. Si lo encuentra, devuelve el array; si no, devuelve false.
                        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($producto) {
                            http_response_code(200); // OK
                            echo json_encode($producto, JSON_UNESCAPED_UNICODE);
                        } else {
                            http_response_code(404); // Not Found
                            echo json_encode(["message" => "Producto no encontrado"], JSON_UNESCAPED_UNICODE);
                        }
                    } catch (PDOException $e) {
                        http_response_code(500); // Internal Server Error
                        echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Acción no encontrada"], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'POST':
                if ($accion == 'crear' || $accion == null) {
                    // Capturamos el JSON enviado en el cuerpo de la petición
                    $jsonRecibido = file_get_contents("php://input");
                    // Decodificamos el JSON a un array asociativo
                    $datos = json_decode($jsonRecibido);
                    // Validamos que los datos necesarios estén presentes
                    if (!empty($datos->nombre) && !empty($datos->precio_actual)) {
                        try {
                            // Preparamos la consulta SQL para insertar un nuevo producto
                            $query = "INSERT INTO productos (nombre, descripcion, precio_actual, stock)
                         VALUES (:nombre, :descripcion, :precio_actual, :stock)";

                            $stmt = $this->conn->prepare($query);
                            // Asignamos los valores a los parámetros de la consulta
                            $descripcion = isset($datos->descripcion) ? $datos->descripcion : "";
                            $stock = isset($datos->stock) ? $datos->stock : 0;
                            // Vinculamos los parámetros con los valores del JSON
                            $stmt->bindParam(':nombre', $datos->nombre);
                            $stmt->bindParam(':descripcion', $descripcion);
                            $stmt->bindParam(':precio_actual', $datos->precio_actual);
                            $stmt->bindParam(':stock', $stock);
                            if ($stmt->execute()) {
                                http_response_code(201); // Created
                                echo json_encode(["message" => "Producto creado exitosamente"], JSON_UNESCAPED_UNICODE);
                            } else {
                                http_response_code(503); // Internal Server Error
                                echo json_encode(["error" => "Error al crear el producto"], JSON_UNESCAPED_UNICODE);
                            }
                        } catch (PDOException $e) {
                            http_response_code(500); // Internal Server Error
                            echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
                        }
                    } else {
                        http_response_code(400); // Bad Request
                        echo json_encode(["error" => "Datos incompletos"], JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Acción no encontrada"], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'PUT':
                if (is_numeric($accion)) {
                    // Lógica para actualizar un producto específico por ID
                    $jsonRecibido = file_get_contents("php://input");
                    $datos = json_decode($jsonRecibido);
                    // Validamos que los datos necesarios estén presentes
                    if (!empty($datos->nombre) && !empty($datos->precio_actual)) {
                        try {
                            // Preparamos la consulta SQL para actualizar un producto existente
                            $query = "UPDATE productos SET nombre = :nombre, descripcion = :descripcion, precio_actual = :precio_actual, stock = :stock WHERE id = :id";
                            $stmt = $this->conn->prepare($query);

                            // Asignamos los valores a los parámetros de la consulta
                            $descripcion = isset($datos->descripcion) ? $datos->descripcion : "";
                            $stock = isset($datos->stock) ? $datos->stock : 0;

                            // Vinculamos los parámetros con los valores del JSON y el ID del producto a actualizar
                            $stmt->bindParam(':nombre', $datos->nombre);
                            $stmt->bindParam(':descripcion', $descripcion);
                            $stmt->bindParam(':precio_actual', $datos->precio_actual);
                            $stmt->bindParam(':stock', $stock);
                            $stmt->bindParam(':id', $accion);

                            if ($stmt->execute()) {
                                if ($stmt->rowCount() > 0) {
                                    http_response_code(200); // OK
                                    echo json_encode(["message" => "Producto actualizado exitosamente"], JSON_UNESCAPED_UNICODE);
                                } else {
                                    http_response_code(200); // OK, pero no se actualizó nada (posiblemente porque los datos son iguales a los existentes)
                                    echo json_encode(["mensaje" => "No se realizaron cambios (datos idénticos o ID no encontrado)"], JSON_UNESCAPED_UNICODE);
                                    return;
                                }
                            } else {
                                http_response_code(503); // Internal Server Error
                                echo json_encode(["error" => "Error al actualizar el producto"], JSON_UNESCAPED_UNICODE);
                            }
                        } catch (PDOException $e) {
                            http_response_code(500); // Internal Server Error
                            echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
                        }
                    } else {
                        http_response_code(400); // Bad Request
                        echo json_encode(["error" => "Datos incompletos"], JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Acción no encontrada"], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'DELETE':
                if (is_numeric($accion)) {
                    // Lógica para eliminar un producto específico por ID
                    try {
                        // Preparamos la consulta SQL para eliminar un producto por su ID
                        $query = "DELETE FROM productos WHERE id = :id";
                        $stmt = $this->conn->prepare($query);
                        $stmt->bindParam(':id', $accion);
                        if ($stmt->execute()) {
                            if ($stmt->rowCount() > 0) {
                                http_response_code(200); // OK
                                echo json_encode(["message" => "Producto eliminado exitosamente"], JSON_UNESCAPED_UNICODE);
                            } else {
                                http_response_code(404); // Not Found
                                echo json_encode(["message" => "Producto no encontrado"], JSON_UNESCAPED_UNICODE);
                            }
                        } else {
                            http_response_code(503); // Internal Server Error
                            echo json_encode(["error" => "Error al eliminar el producto"], JSON_UNESCAPED_UNICODE);
                        }
                    } catch (PDOException $e) {
                        http_response_code(500); // Internal Server Error
                        echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
                    }
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
