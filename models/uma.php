<?php
class UMA {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        try {
            $stmt = $this->pdo->prepare('SELECT id, valor, fecha_inicio, descripcion FROM uma ORDER BY fecha_inicio DESC');
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener todos los UMA: ' . $e->getMessage());
            return [];
        }
    }

    public function getCurrent() {
        try {
            $stmt = $this->pdo->prepare('SELECT valor FROM uma ORDER BY fecha_inicio DESC LIMIT 1');
            $stmt->execute();
            return $stmt->fetchColumn() ?: 0;
        } catch (PDOException $e) {
            error_log('Error al obtener UMA actual: ' . $e->getMessage());
            return 0;
        }
    }

    public function getLatest() {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM uma ORDER BY fecha_inicio DESC LIMIT 1');
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error al obtener UMA más reciente: ' . $e->getMessage());
            return [];
        }
    }

    public function create($valor, $fecha_inicio, $descripcion) {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO uma (valor, fecha_inicio, descripcion) VALUES (?, ?, ?)');
            return $stmt->execute([$valor, $fecha_inicio, $descripcion ?: NULL]);
        } catch (PDOException $e) {
            error_log('Error al crear UMA: ' . $e->getMessage());
            throw new Exception('Error al crear UMA: ' . $e->getMessage());
        }
    }

    public function edit($id, $valor, $fecha_inicio, $descripcion) {
        try {
            $stmt = $this->pdo->prepare('UPDATE uma SET valor = ?, fecha_inicio = ?, descripcion = ? WHERE id = ?');
            return $stmt->execute([$valor, $fecha_inicio, $descripcion ?: NULL, $id]);
        } catch (PDOException $e) {
            error_log('Error al actualizar UMA: ' . $e->getMessage());
            throw new Exception('Error al actualizar UMA: ' . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM uma WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log('Error al eliminar UMA: ' . $e->getMessage());
            throw new Exception('Error al eliminar UMA: ' . $e->getMessage());
        }
    }
}