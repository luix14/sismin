<?php
class Configuracion {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function getAll() {
        try {
            return $this->pdo->query('SELECT id, clave, valor, descripcion FROM configuraciones')->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener configuraciones: ' . $e->getMessage());
            return [];
        }
    }
    public function update($id, $data) {
        try {
            $stmt = $this->pdo->prepare('UPDATE configuraciones SET valor = ? WHERE id = ?');
            return $stmt->execute([$data['valor'], $id]);
        } catch (PDOException $e) {
            error_log('Error al actualizar configuración: ' . $e->getMessage());
            throw new Exception('Error al actualizar la configuración: ' . $e->getMessage());
        }
    }
    public function getUMA() {
        try {
            $stmt = $this->pdo->prepare('SELECT valor FROM configuraciones WHERE clave = ?');
            $stmt->execute(['uma']);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['valor'] : 0;
        } catch (PDOException $e) {
            error_log('Error al obtener valor de UMA: ' . $e->getMessage());
            return 0;
        }
    }
}