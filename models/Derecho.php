<?php
class Derecho {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function getAll() {
        try {
            $stmt = $this->pdo->prepare('SELECT d.id, d.codigo, d.descripcion, d.uma_valor, c.nombre AS categoria, c.id AS id_categoria, d.folio,
                                         (d.uma_valor * (SELECT valor FROM uma ORDER BY fecha_inicio DESC LIMIT 1)) AS costo
                                         FROM derechos d 
                                         JOIN categorias c ON d.id_categoria = c.id');
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener derechos: ' . $e->getMessage());
            return [];
        }
    }
    public function getAllCategorias() {
        try {
            return $this->pdo->query('SELECT id, codigo, nombre FROM categorias')->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener categorías: ' . $e->getMessage());
            return [];
        }
    }
    public function getCategoriaCodigo($id_categoria) {
        try {
            $stmt = $this->pdo->prepare('SELECT codigo FROM categorias WHERE id = ?');
            $stmt->execute([$id_categoria]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['codigo'] : false;
        } catch (PDOException $e) {
            error_log('Error al obtener código de categoría: ' . $e->getMessage());
            return false;
        }
    }
    public function getNextFolio($id_categoria) {
        try {
            $stmt = $this->pdo->prepare('SELECT MAX(CAST(folio AS UNSIGNED)) AS max_folio FROM derechos WHERE id_categoria = ?');
            $stmt->execute([$id_categoria]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['max_folio'] ? $result['max_folio'] + 1 : 1;
        } catch (PDOException $e) {
            error_log('Error al obtener próximo folio: ' . $e->getMessage());
            return 1;
        }
    }
    public function create($data) {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO derechos (codigo, id_categoria, folio, descripcion, uma_valor) 
                                        VALUES (?, ?, ?, ?, ?)');
            return $stmt->execute([
                $data['codigo'],
                $data['id_categoria'],
                $data['folio'],
                $data['descripcion'],
                $data['uma_valor']
            ]);
        } catch (PDOException $e) {
            error_log('Error al insertar derecho: ' . $e->getMessage());
            throw new Exception('Error al insertar el derecho: ' . $e->getMessage());
        }
    }
    public function update($id, $data) {
        try {
            $stmt = $this->pdo->prepare('UPDATE derechos SET codigo = ?, id_categoria = ?, folio = ?, descripcion = ?, uma_valor = ? WHERE id = ?');
            return $stmt->execute([
                $data['codigo'],
                $data['id_categoria'],
                $data['folio'],
                $data['descripcion'],
                $data['uma_valor'],
                $id
            ]);
        } catch (PDOException $e) {
            error_log('Error al actualizar derecho: ' . $e->getMessage());
            throw new Exception('Error al actualizar el derecho: ' . $e->getMessage());
        }
    }
    public function delete($id) {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM derechos WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log('Error al eliminar derecho: ' . $e->getMessage());
            throw new Exception('Error al eliminar el derecho: ' . $e->getMessage());
        }
    }
    public function search($term) {
        try {
            $searchTerm = '%' . $term . '%';
            $stmt = $this->pdo->prepare('SELECT d.id, d.codigo, d.descripcion, d.uma_valor, c.nombre AS categoria, c.id AS id_categoria, d.folio,
                                         (d.uma_valor * (SELECT valor FROM uma ORDER BY fecha_inicio DESC LIMIT 1)) AS costo
                                         FROM derechos d 
                                         JOIN categorias c ON d.id_categoria = c.id
                                         WHERE d.codigo LIKE :term OR d.descripcion LIKE :term
                                         LIMIT 10');
            $stmt->bindValue(':term', $searchTerm, PDO::PARAM_STR);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log('Derechos search: term=' . $term . ', results=' . count($results));
            return $results;
        } catch (PDOException $e) {
            error_log('Error al buscar derechos: ' . $e->getMessage());
            return [];
        }
    }
}