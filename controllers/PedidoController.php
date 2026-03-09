<?php
class PedidoController
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function procesarPeticion($metodo, $accion)
    {
        switch ($metodo) {
            case 'GET':
                break;
            case 'POST':
                if ($accion == 'crear' || $accion == null) {
                    $jsonRecibido = file_get_contents("php://input");
                    $datos = json_decode($jsonRecibido);

                    if (!empty($datos->cliente_id) && !empty($datos->detalles) && is_array($datos->detalles) && count($datos->detalles) > 0) {
                        try {
                            // Iniciar la transacción para que todas las operaciones se realicen correctamente o se deshagan en caso de error
                            $this->conn->beginTransaction();

                            //Empezamos con la cabezera del pedido
                            $queryPedido = "INSERT INTO pedidos (cliente_id, notas) VALUES (:cliente_id, :notas)";
                            $stmtPedido = $this->conn->prepare($queryPedido);

                            $notas = isset($datos->notas) ? $datos->notas : "";
                            $stmtPedido->bindParam(':cliente_id', $datos->cliente_id);
                            $stmtPedido->bindParam(':notas', $notas);

                            $stmtPedido->execute();

                            //Obtenemos el ID del pedido recién creado
                            $pedidoId = $this->conn->lastInsertId();

                            //Ahora insertamos los detalles del pedido
                            $queryDetalle = "INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario) 
                                             VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario)";
                            $stmtDetalle = $this->conn->prepare($queryDetalle);

                            foreach ($datos->detalles as $detalle) {
                                $stmtDetalle->bindParam(':pedido_id', $pedidoId);
                                $stmtDetalle->bindParam(':producto_id', $detalle->producto_id);
                                $stmtDetalle->bindParam(':cantidad', $detalle->cantidad);
                                $stmtDetalle->bindParam(':precio_unitario', $detalle->precio_unitario);

                                $stmtDetalle->execute();
                            }
                            // Si todo se ejecutó correctamente, confirmamos la transacción
                            $this->conn->commit();
                            http_response_code(201);
                            echo json_encode(["message" => "Pedido creado exitosamente", "pedido_id" => $pedidoId], JSON_UNESCAPED_UNICODE);
                        } catch (Exception $e) {
                            // Si explota (ej: no existe el producto), cancelamos los inserts a la base de datos
                            $this->conn->rollback();
                            http_response_code(500);
                            echo json_encode(["error" => "Error al procesar el pedido: " . $e->getMessage()]);
                        }
                    } else {
                        http_response_code(400);
                        echo json_encode(["error" => "Datos incompletos o inválidos. Asegúrate de incluir cliente_id y detalles con al menos un producto."]);
                    }
                }
                break;
            case 'PUT':
                break;
            case 'DELETE':
                break;
            default:
                http_response_code(405); // Method Not Allowed
                echo json_encode(["error" => "Método no permitido"]);
                break;
        }
    }
}
