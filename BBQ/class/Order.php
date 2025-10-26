<?php

class Order {
    private $conn;
    private $orders_table = "orders";
    private $order_items_table = "order_items";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a new order with items
     * @param int $userId User ID
     * @param array $items Cart items array
     * @param float $totalAmount Total order amount
     * @return int|false Order ID if successful, false otherwise
     */
    public function createOrder($userId, $items, $totalAmount) {
        try {
            // Start transaction
            $this->conn->beginTransaction();

            // Insert into orders table
            $query = "INSERT INTO " . $this->orders_table . " (user_id, total_amount, status, payment_method) 
                      VALUES (:user_id, :total_amount, 'completed', 'cash')";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->bindParam(":total_amount", $totalAmount);
            
            if (!$stmt->execute()) {
                $this->conn->rollBack();
                error_log("Failed to insert order: " . print_r($stmt->errorInfo(), true));
                return false;
            }

            // Get the inserted order ID
            $orderId = $this->conn->lastInsertId();
            error_log("Order created with ID: $orderId");

            // Insert order items
            $itemQuery = "INSERT INTO " . $this->order_items_table . " 
                         (order_id, product_id, product_name, price, quantity, subtotal) 
                         VALUES (:order_id, :product_id, :product_name, :price, :quantity, :subtotal)";
            
            $itemStmt = $this->conn->prepare($itemQuery);

            foreach ($items as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                
                $itemStmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
                $itemStmt->bindParam(":product_id", $item['id'], PDO::PARAM_INT);
                $itemStmt->bindParam(":product_name", $item['name']);
                $itemStmt->bindParam(":price", $item['price']);
                $itemStmt->bindParam(":quantity", $item['quantity'], PDO::PARAM_INT);
                $itemStmt->bindParam(":subtotal", $subtotal);
                
                if (!$itemStmt->execute()) {
                    $this->conn->rollBack();
                    error_log("Failed to insert order item: " . print_r($itemStmt->errorInfo(), true));
                    return false;
                }
                
                error_log("Added item: " . $item['name']);
            }

            // Commit transaction
            $this->conn->commit();
            error_log("Order transaction committed successfully");
            return $orderId;

        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Order creation PDO error: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Order creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all orders for a specific user
     * @param int $userId User ID
     * @return array|false Array of orders or false
     */
    public function getUserOrders($userId) {
        $query = "SELECT * FROM " . $this->orders_table . " 
                  WHERE user_id = :user_id 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get order details with items
     * @param int $orderId Order ID
     * @return array|false Order details with items
     */
    public function getOrderDetails($orderId) {
        // Get order info
        $query = "SELECT o.*, u.name as user_name, u.email as user_email 
                  FROM " . $this->orders_table . " o
                  JOIN user u ON o.user_id = u.id
                  WHERE o.id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $stmt->execute();

        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return false;
        }

        // Get order items
        $itemQuery = "SELECT * FROM " . $this->order_items_table . " 
                      WHERE order_id = :order_id";
        
        $itemStmt = $this->conn->prepare($itemQuery);
        $itemStmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $itemStmt->execute();

        $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        return $order;
    }

    /**
     * Get order statistics for a user
     * @param int $userId User ID
     * @return array Statistics
     */
    public function getUserOrderStats($userId) {
        $query = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_spent,
                    MAX(created_at) as last_order_date
                  FROM " . $this->orders_table . "
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

?>