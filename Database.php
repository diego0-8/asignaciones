<?php
/**
 * Clase base para conexión a la base de datos
 * Maneja la conexión PDO y operaciones básicas
 */

require_once __DIR__ . '/../config.php';

class Database {
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $pdo;
    
    public function __construct() {
        $this->host = DB_HOST;
        $this->dbname = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        
        $this->connect();
    }
    
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en consulta SQL: " . $e->getMessage());
            throw new Exception("Error en la consulta de base de datos");
        }
    }
    
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error al insertar en {$table}: " . $e->getMessage());
            throw new Exception("Error al insertar datos");
        }
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $values = array_values($data);
            $stmt->execute(array_merge($values, $whereParams));
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error al actualizar en {$table}: " . $e->getMessage());
            throw new Exception("Error al actualizar datos");
        }
    }
    
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error al eliminar de {$table}: " . $e->getMessage());
            throw new Exception("Error al eliminar datos");
        }
    }
    
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollback() {
        return $this->pdo->rollback();
    }
}
?>
