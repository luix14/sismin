<?php
class Predio {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function importOrUpdate($fields) {
        try {
            $clave_catastral = $fields[7];
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM predios WHERE clave_catastral = ?');
            $stmt->execute([$clave_catastral]);
            $exists = $stmt->fetchColumn() > 0;

            if ($exists) {
                $stmt = $this->pdo->prepare('UPDATE predios SET 
                    campo1 = ?, campo2 = ?, campo3 = ?, campo4 = ?, rfc = ?, campo5 = ?, 
                    fecha_generacion_archivo = ?, homoclave = ?, propietario = ?, municipio = ?, 
                    tipo_predio = ?, calificativo = ?, campo6 = ?, campo7 = ?, 
                    ubicacion_colonia = ?, ubicacion_calle = ?, ubicacion_numero_interior = ?, 
                    ubicacion_numero_letra = ?, ubicacion_numero_interior2 = ?, 
                    fiscal_codigo_estado = ?, fiscal_codigo_municipio = ?, 
                    fiscal_codigo_localidad = ?, fiscal_codigo_colonia = ?, fiscal_colonia = ?, 
                    fiscal_calle = ?, fiscal_numero = ?, fiscal_letra = ?, 
                    fiscal_numero_interior = ?, telefono = ?, clave_catastral_anterior = ?, 
                    valor_terreo_anterior = ?, valor_construccion_anterior = ?, 
                    valor_terreno = ?, valor_construccion = ?, superficie_terreno = ?, 
                    superficie_construccion = ?, fecha_de_alta = ?, fecha_ultima_actualizacion = ?, 
                    campo8 = ?, campo9 = ?, campo10 = ?
                    WHERE clave_catastral = ?');
                $stmt->execute(array_merge(array_slice($fields, 0, 7), array_slice($fields, 8), [$clave_catastral]));
                return 'updated';
            } else {
                $stmt = $this->pdo->prepare('INSERT INTO predios (
                    campo1, campo2, campo3, campo4, rfc, campo5, fecha_generacion_archivo, clave_catastral, 
                    homoclave, propietario, municipio, tipo_predio, calificativo, campo6, campo7, 
                    ubicacion_colonia, ubicacion_calle, ubicacion_numero_interior, ubicacion_numero_letra, 
                    ubicacion_numero_interior2, fiscal_codigo_estado, fiscal_codigo_municipio, 
                    fiscal_codigo_localidad, fiscal_codigo_colonia, fiscal_colonia, fiscal_calle, 
                    fiscal_numero, fiscal_letra, fiscal_numero_interior, telefono, clave_catastral_anterior, 
                    valor_terreo_anterior, valor_construccion_anterior, valor_terreno, valor_construccion, 
                    superficie_terreno, superficie_construccion, fecha_de_alta, fecha_ultima_actualizacion, 
                    campo8, campo9, campo10
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute($fields);
                return 'inserted';
            }
        } catch (PDOException $e) {
            error_log('Error al importar/actualizar predio: ' . $e->getMessage());
            throw new Exception('Error al procesar el predio: ' . $e->getMessage());
        }
    }
    public function search($search, $page = 1, $limit = 50) {
        try {
            $offset = ($page - 1) * $limit;
            $searchTerm = '%' . $search . '%';
            $query = 'SELECT clave_catastral, propietario, rfc, homoclave, municipio, 
                ubicacion_colonia, ubicacion_calle, ubicacion_numero_interior, 
                superficie_terreno, superficie_construccion 
                FROM predios';
            $params = [];
            if (!empty($search)) {
                $query .= ' WHERE clave_catastral LIKE :search1 OR propietario LIKE :search2';
                $params[':search1'] = $searchTerm;
                $params[':search2'] = $searchTerm;
            }
            $query .= ' LIMIT :limit OFFSET :offset';
            $params[':limit'] = (int)$limit;
            $params[':offset'] = (int)$offset;
            error_log('Ejecutando búsqueda de predios: ' . $query . ' con search=' . $search . ', limit=' . $limit . ', offset=' . $offset);
            $stmt = $this->pdo->prepare($query);
            foreach ($params as $key => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $paramType);
            }
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log('Resultados obtenidos en predios: ' . count($results));

            $countQuery = 'SELECT COUNT(*) FROM predios';
            $countParams = [];
            if (!empty($search)) {
                $countQuery .= ' WHERE clave_catastral LIKE :search1 OR propietario LIKE :search2';
                $countParams[':search1'] = $searchTerm;
                $countParams[':search2'] = $searchTerm;
            }
            $countStmt = $this->pdo->prepare($countQuery);
            foreach ($countParams as $key => $value) {
                $countStmt->bindValue($key, $value, PDO::PARAM_STR);
            }
            $countStmt->execute();
            $total = $countStmt->fetchColumn();
            error_log('Total de registros en predios: ' . $total);

            return ['results' => $results, 'total' => $total];
        } catch (PDOException $e) {
            error_log('Error al buscar predios: ' . $e->getMessage());
            return ['results' => [], 'total' => 0];
        }
    }
}