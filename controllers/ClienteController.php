<?php

class ClienteController
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
                        $query = "SELECT id, nombre, apellidos, email, direccion_envio, direccion_facturacion, fecha_registro FROM clientes";
                        $stmt = $this->conn->prepare($query);
                        $stmt->execute();
                        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($clientes) > 0) {
                            http_response_code(200); // OK
                            echo json_encode($clientes, JSON_UNESCAPED_UNICODE);
                        } else {
                            http_response_code(404); // Not Found
                            echo json_encode(["message" => "No se encontraron clientes"], JSON_UNESCAPED_UNICODE);
                        }
                    } catch (PDOException $e) {
                        http_response_code(500); // Internal Server Error
                        echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
                    }
                } elseif (is_numeric($accion)) {
                    // Lógica para obtener un cliente específico por ID
                    try {
                        // Preparamos la consulta SQL para obtener un cliente por su ID
                        $query = "SELECT id, nombre, apellidos, email, direccion_envio, direccion_facturacion, fecha_registro FROM clientes WHERE id = :id";
                        $stmt = $this->conn->prepare($query);

                        $stmt->bindParam(':id', $accion);
                        $stmt->execute();
                        // Usamos fetch() en singular. Si lo encuentra, devuelve el array; si no, devuelve false.
                        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($cliente) {
                            http_response_code(200); // OK
                            echo json_encode($cliente, JSON_UNESCAPED_UNICODE);
                        } else {
                            http_response_code(404); // Not Found
                            echo json_encode(["message" => "Cliente no encontrado"], JSON_UNESCAPED_UNICODE);
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
                    if (!empty($datos->nombre) && !empty($datos->apellidos) && !empty($datos->email) && !empty($datos->direccion_envio) && !empty($datos->direccion_facturacion)) {
                        try {
                            // Preparamos la consulta SQL para insertar un nuevo cliente
                            $query = "INSERT INTO clientes (nombre, apellidos, email, direccion_envio, direccion_facturacion)
                         VALUES (:nombre, :apellidos, :email, :direccion_envio, :direccion_facturacion)";

                            $stmt = $this->conn->prepare($query);
                            // Asignamos los valores a los parámetros de la consulta
                            $apellidos = $datos->apellidos;
                            $email = $datos->email;
                            $direccion_envio = $datos->direccion_envio;
                            $direccion_facturacion = $datos->direccion_facturacion;
                            // Vinculamos los parámetros con los valores del JSON
                            $stmt->bindParam(':nombre', $datos->nombre);
                            $stmt->bindParam(':apellidos', $apellidos);
                            $stmt->bindParam(':email', $email);
                            $stmt->bindParam(':direccion_envio', $direccion_envio);
                            $stmt->bindParam(':direccion_facturacion', $direccion_facturacion);
                            if ($stmt->execute()) {
                                http_response_code(201); // Created
                                echo json_encode(["message" => "Cliente creado exitosamente"], JSON_UNESCAPED_UNICODE);
                            } else {
                                http_response_code(503); // Internal Server Error
                                echo json_encode(["error" => "Error al crear el cliente"], JSON_UNESCAPED_UNICODE);
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
                    // Lógica para actualizar un cliente específico por ID
                    $jsonRecibido = file_get_contents("php://input");
                    $datos = json_decode($jsonRecibido);
                    // Validamos que los datos necesarios estén presentes
                    if (!empty($datos->nombre) && !empty($datos->apellidos) && !empty($datos->email) && !empty($datos->direccion_envio) && !empty($datos->direccion_facturacion)) {
                        try {
                            // Preparamos la consulta SQL para actualizar un cliente existente
                            $query = "UPDATE clientes SET nombre = :nombre, apellidos = :apellidos, email = :email, direccion_envio = :direccion_envio, direccion_facturacion = :direccion_facturacion WHERE id = :id";
                            $stmt = $this->conn->prepare($query);

                            // Asignamos los valores a los parámetros de la consulta
                            $apellidos = $datos->apellidos;
                            $email = $datos->email;
                            $direccion_envio = $datos->direccion_envio;
                            $direccion_facturacion = $datos->direccion_facturacion;

                            // Vinculamos los parámetros con los valores del JSON y el ID del cliente a actualizar
                            $stmt->bindParam(':nombre', $datos->nombre);
                            $stmt->bindParam(':apellidos', $apellidos);
                            $stmt->bindParam(':email', $email);
                            $stmt->bindParam(':direccion_envio', $direccion_envio);
                            $stmt->bindParam(':direccion_facturacion', $direccion_facturacion);
                            $stmt->bindParam(':id', $accion);

                            if ($stmt->execute()) {
                                if ($stmt->rowCount() > 0) {
                                    http_response_code(200); // OK
                                    echo json_encode(["message" => "Cliente actualizado exitosamente"], JSON_UNESCAPED_UNICODE);
                                } else {
                                    http_response_code(200); // OK, pero no se actualizó nada (posiblemente porque los datos son iguales a los existentes)
                                    echo json_encode(["mensaje" => "No se realizaron cambios (datos idénticos o ID no encontrado)"], JSON_UNESCAPED_UNICODE);
                                    return;
                                }
                            } else {
                                http_response_code(503); // Internal Server Error
                                echo json_encode(["error" => "Error al actualizar el cliente"], JSON_UNESCAPED_UNICODE);
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
                    // Lógica para eliminar un cliente específico por ID
                    try {
                        // Preparamos la consulta SQL para eliminar un cliente por su ID
                        $query = "DELETE FROM clientes WHERE id = :id";
                        $stmt = $this->conn->prepare($query);
                        $stmt->bindParam(':id', $accion);
                        if ($stmt->execute()) {
                            if ($stmt->rowCount() > 0) {
                                http_response_code(200); // OK
                                echo json_encode(["message" => "Cliente eliminado exitosamente"], JSON_UNESCAPED_UNICODE);
                            } else {
                                http_response_code(404); // Not Found
                                echo json_encode(["message" => "Cliente no encontrado"], JSON_UNESCAPED_UNICODE);
                            }
                        } else {
                            http_response_code(503); // Internal Server Error
                            echo json_encode(["error" => "Error al eliminar el cliente"], JSON_UNESCAPED_UNICODE);
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
