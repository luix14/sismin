<?php
class Transaccion {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function create($data) {
        $stmt = $this->pdo->prepare('
            INSERT INTO transacciones (folio, id_derecho, id_usuario, id_departamento, monto_total, metodo_pago, recargo, descuento, exencion, observaciones)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        return $stmt->execute([
            $data['folio'], $data['id_derecho'], $data['id_usuario'], $data['id_departamento'],
            $data['monto_total'], $data['metodo_pago'], $data['recargo'], $data['descuento'],
            $data['exencion'], $data['observaciones']
        ]);
    }
}