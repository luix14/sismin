<?php
class Contribuyente {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function search($search, $page = 1, $limit = 50) {
        try {
            $offset = ($page - 1) * $limit;
            $searchTerm = '%' . $search . '%';
            $query = 'SELECT id, rfc, nombre, direccion_fiscal, regimen_fiscal, codigo_postal 
                     FROM contribuyentes';
            $params = [];
            if (!empty($search)) {
                $query .= ' WHERE rfc LIKE :search1 OR nombre LIKE :search2';
                $params[':search1'] = $searchTerm;
                $params[':search2'] = $searchTerm;
            }
            $query .= ' ORDER BY rfc ASC LIMIT :limit OFFSET :offset';
            $params[':limit'] = (int)$limit;
            $params[':offset'] = (int)$offset;
            error_log('Ejecutando búsqueda de contribuyentes: ' . $query . ' con search=' . $search . ', limit=' . $limit . ', offset=' . $offset);
            $stmt = $this->pdo->prepare($query);
            foreach ($params as $key => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $paramType);
            }
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log('Resultados obtenidos en contribuyentes: ' . count($results));

            $countQuery = 'SELECT COUNT(*) FROM contribuyentes';
            $countParams = [];
            if (!empty($search)) {
                $countQuery .= ' WHERE rfc LIKE :search1 OR nombre LIKE :search2';
                $countParams[':search1'] = $searchTerm;
                $countParams[':search2'] = $searchTerm;
            }
            $countStmt = $this->pdo->prepare($countQuery);
            foreach ($countParams as $key => $value) {
                $countStmt->bindValue($key, $value, PDO::PARAM_STR);
            }
            $countStmt->execute();
            $total = $countStmt->fetchColumn();
            error_log('Total de registros en contribuyentes: ' . $total);

            return ['results' => $results, 'total' => $total];
        } catch (PDOException $e) {
            error_log('Error al buscar contribuyentes: ' . $e->getMessage());
            return ['results' => [], 'total' => 0];
        }
    }
    public function updateFromPredios() {
        try {
            $this->pdo->beginTransaction();
            // Truncar la tabla para evitar conflictos de duplicados
            $this->pdo->exec('TRUNCATE TABLE contribuyentes');
            $stmt = $this->pdo->query('SELECT id, rfc, homoclave, propietario, 
                CONCAT(fiscal_colonia, ", ", fiscal_calle, ", ", fiscal_numero, 
                       IF(fiscal_letra != "", CONCAT(" ", fiscal_letra), ""), 
                       IF(fiscal_numero_interior != "", CONCAT(", Int. ", fiscal_numero_interior), "")) AS direccion_fiscal,
                clave_catastral
                FROM predios
                WHERE id IN (
                    SELECT MAX(id)
                    FROM predios
                    GROUP BY COALESCE(NULLIF(TRIM(rfc), ""), propietario)
                )');
            $insertStmt = $this->pdo->prepare('INSERT INTO contribuyentes (rfc, nombre, direccion_fiscal, id_predio, regimen_fiscal, codigo_postal) 
                                              VALUES (?, ?, ?, ?, NULL, NULL)');
            $updateStmt = $this->pdo->prepare('UPDATE contribuyentes 
                                              SET nombre = ?, direccion_fiscal = ?, id_predio = ?, regimen_fiscal = NULL, codigo_postal = NULL 
                                              WHERE rfc = ?');
            $checkStmt = $this->pdo->prepare('SELECT COUNT(*) FROM contribuyentes WHERE rfc = ?');
            $total = 0;
            $exitosos = 0;
            $nuevos = 0;
            $actualizados = 0;
            $fallidos = 0;
            $errores = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Validar que propietario y clave_catastral no sean nulos ni vacíos
                $propietario = trim($row['propietario'] ?? '');
                $clave_catastral = trim($row['clave_catastral'] ?? '');
                if (empty($propietario) || empty($clave_catastral)) {
                    $errores[] = "Clave: {$clave_catastral}: Registro inválido (propietario o clave vacíos)";
                    $fallidos++;
                    error_log("Registro inválido para clave: {$clave_catastral}, propietario: {$propietario}");
                    continue;
                }
                $total++;
                $rfc = trim($row['rfc'] . $row['homoclave']);
                if (empty($rfc) || $rfc == '') {
                    $nombre = $propietario;
                    $partes = explode(' ', $nombre);
                    $apellido1 = $partes[0] ?? '';
                    $apellido2 = $partes[1] ?? '';
                    $nombre1 = $partes[2] ?? '';
                    // Primera letra del apellido paterno
                    $letra1 = strtoupper(substr($apellido1, 0, 1));
                    $letra1 = $letra1 ?: 'X';
                    // Primera vocal después de la primera letra del apellido paterno
                    $vocal = '';
                    for ($i = 1; $i < strlen($apellido1); $i++) {
                        $char = strtoupper($apellido1[$i]);
                        if (in_array($char, ['A', 'E', 'I', 'O', 'U'])) {
                            $vocal = $char;
                            break;
                        }
                    }
                    $letra2 = $vocal ?: 'X';
                    // Primera letra del segundo apellido
                    $letra3 = strtoupper(substr($apellido2, 0, 1));
                    $letra3 = $letra3 ?: 'X';
                    // Primera letra del nombre
                    $letra4 = strtoupper(substr($nombre1, 0, 1));
                    $letra4 = $letra4 ?: 'X';
                    $iniciales = $letra1 . $letra2 . $letra3 . $letra4;
                    $clave = substr($clave_catastral, -6);
                    $homo = substr($clave_catastral, -3);
                    $rfc = $iniciales . $clave . $homo;
                    $baseRfc = $rfc;
                    $suffix = 0;
                    $maxAttempts = 100;
                    $attempt = 0;
                    while ($attempt < $maxAttempts) {
                        $checkStmt->execute([$rfc]);
                        if ($checkStmt->fetchColumn() == 0) {
                            break;
                        }
                        $suffix++;
                        $rfc = $baseRfc . '-' . $suffix;
                        $attempt++;
                    }
                    if ($attempt >= $maxAttempts) {
                        // Fallback: Use hash of clave_catastral + nombre + id
                        $rfc = 'X' . substr(md5($clave_catastral . $nombre . $row['id']), 0, 12);
                        $baseRfc = $rfc;
                        $suffix = 0;
                        $attempt = 0;
                        while ($attempt < $maxAttempts) {
                            $checkStmt->execute([$rfc]);
                            if ($checkStmt->fetchColumn() == 0) {
                                break;
                            }
                            $suffix++;
                            $rfc = $baseRfc . '-' . $suffix;
                            $attempt++;
                        }
                        if ($attempt >= $maxAttempts) {
                            $errores[] = "Clave: {$clave_catastral}: No se pudo generar un RFC único";
                            $fallidos++;
                            error_log("Fallo al generar RFC único para clave: {$clave_catastral}, propietario: {$nombre}");
                            continue;
                        }
                    }
                }
                try {
                    // Verificar si el RFC ya existe
                    $checkStmt->execute([$rfc]);
                    if ($checkStmt->fetchColumn() > 0) {
                        // Actualizar registro existente
                        $updateStmt->execute([
                            $propietario,
                            trim($row['direccion_fiscal']),
                            NULL, // id_predio como NULL
                            $rfc
                        ]);
                        $exitosos++;
                        $actualizados++;
                        error_log("Actualizado contribuyente con RFC: {$rfc}, clave: {$clave_catastral}");
                    } else {
                        // Insertar nuevo registro
                        $insertStmt->execute([
                            $rfc,
                            $propietario,
                            trim($row['direccion_fiscal']),
                            NULL // id_predio como NULL
                        ]);
                        $exitosos++;
                        $nuevos++;
                        error_log("Insertado nuevo contribuyente con RFC: {$rfc}, clave: {$clave_catastral}");
                    }
                } catch (PDOException $e) {
                    $fallidos++;
                    $errores[] = "Clave: {$clave_catastral}: " . $e->getMessage();
                    error_log("Error al procesar contribuyente con clave: {$clave_catastral}, RFC: {$rfc}, error: " . $e->getMessage());
                    continue;
                }
            }
            if ($this->pdo->inTransaction()) {
                $this->pdo->commit();
            }
            $mensaje = "Actualización completada. Total: $total, Exitosos: $exitosos, Nuevos: $nuevos, Actualizados: $actualizados, Fallidos: $fallidos";
            if ($fallidos > 0) {
                $mensaje .= ". Errores: " . implode('; ', array_slice($errores, 0, 5));
            }
            return $mensaje;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('Error al actualizar contribuyentes: ' . $e->getMessage());
            return 'Error al actualizar contribuyentes: ' . $e->getMessage();
        }
    }
    public function create($rfc, $nombre, $direccion_fiscal, $regimen_fiscal, $codigo_postal) {
        try {
            // Validar unicidad del RFC
            $checkStmt = $this->pdo->prepare('SELECT COUNT(*) FROM contribuyentes WHERE rfc = ?');
            $checkStmt->execute([$rfc]);
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception('El RFC ya existe');
            }
            $insertStmt = $this->pdo->prepare('INSERT INTO contribuyentes (rfc, nombre, direccion_fiscal, regimen_fiscal, codigo_postal) 
                                              VALUES (?, ?, ?, ?, ?)');
            $insertStmt->execute([
                $rfc,
                $nombre,
                $direccion_fiscal,
                $regimen_fiscal ?: NULL,
                $codigo_postal ?: NULL
            ]);
        } catch (PDOException $e) {
            error_log('Error al crear contribuyente: ' . $e->getMessage());
            throw new Exception('Error al crear contribuyente: ' . $e->getMessage());
        }
    }
}