<?php
class Categoria {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function getAll() {
        return $this->pdo->query('SELECT id, codigo, nombre FROM categorias')->fetchAll();
    }
    public function create($data) {
        $stmt = $this->pdo->prepare('INSERT INTO categorias (codigo, nombre) VALUES (?, ?)');
        return $stmt->execute([$data['codigo'], $data['nombre']]);
    }
    public function update($id, $data) {
        $stmt = $this->pdo->prepare('UPDATE categorias SET codigo = ?, nombre = ? WHERE id = ?');
        return $stmt->execute([$data['codigo'], $data['nombre'], $id]);
    }
    public function delete($id) {
        $stmt = $this->pdo->prepare('DELETE FROM categorias WHERE id = ?');
        return $stmt->execute([$id]);
    }
    public function getById($id) {
        $stmt = $this->pdo->prepare('SELECT codigo FROM categorias WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}