<?php
class DatosMunicipio {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getLatest() {
        try {
            $stmt = $this->pdo->query('SELECT * FROM datos_municipio ORDER BY fecha_actualizacion DESC LIMIT 1');
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: [
                'nombre' => '',
                'direccion' => '',
                'telefono' => '',
                'correo' => '',
                'rfc' => '',
                'horario_atencion' => '',
                'eslogan' => ''
            ];
        } catch (PDOException $e) {
            error_log('Error al obtener datos del municipio: ' . $e->getMessage());
            return [
                'nombre' => '',
                'direccion' => '',
                'telefono' => '',
                'correo' => '',
                'rfc' => '',
                'horario_atencion' => '',
                'eslogan' => ''
            ];
        }
    }

    public function update($nombre, $direccion, $telefono, $correo, $rfc, $horario_atencion, $eslogan) {
        try {
            // Verificar si ya existe un registro
            $stmt = $this->pdo->query('SELECT COUNT(*) FROM datos_municipio');
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                // Actualizar el registro existente
                $stmt = $this->pdo->prepare('
                    UPDATE datos_municipio 
                    SET nombre = ?, direccion = ?, telefono = ?, correo = ?, rfc = ?, horario_atencion = ?, eslogan = ?, fecha_actualizacion = NOW()
                    WHERE id = (SELECT id FROM datos_municipio ORDER BY fecha_actualizacion DESC LIMIT 1)
                ');
                $stmt->execute([$nombre, $direccion, $telefono, $correo, $rfc, $horario_atencion, $eslogan]);
            } else {
                // Insertar un nuevo registro
                $stmt = $this->pdo->prepare('
                    INSERT INTO datos_municipio (nombre, direccion, telefono, correo, rfc, horario_atencion, eslogan, fecha_actualizacion)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ');
                $stmt->execute([$nombre, $direccion, $telefono, $correo, $rfc, $horario_atencion, $eslogan]);
            }
            error_log('DatosMunicipio::update - Datos del municipio actualizados: ' . $nombre . ', eslogan: ' . $eslogan);
        } catch (PDOException $e) {
            error_log('Error al actualizar datos del municipio: ' . $e->getMessage());
            throw new Exception('Error al actualizar los datos del municipio: ' . $e->getMessage());
        }
    }
}