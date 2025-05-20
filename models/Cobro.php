<?php
require_once 'vendor/autoload.php';

class Cobro {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($rfc, $nombre, $telefono, $direccion, $observaciones, $derechos) {
        try {
            $this->pdo->beginTransaction();
            $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : NULL;
            $folioStmt = $this->pdo->query('SELECT MAX(id) AS max_id FROM cobros');
            $max_id = $folioStmt->fetchColumn();
            $folio = 'COBRO-' . sprintf('%06d', ($max_id ? $max_id + 1 : 1));
            $total = 0;
            foreach ($derechos as $d) {
                $derechoStmt = $this->pdo->prepare('SELECT uma_valor FROM derechos WHERE id = ?');
                $derechoStmt->execute([$d['id']]);
                $uma_valor = $derechoStmt->fetchColumn();
                if ($uma_valor === false) {
                    throw new Exception('Derecho no encontrado: ID ' . $d['id']);
                }
                $umaStmt = $this->pdo->prepare('SELECT valor FROM uma ORDER BY fecha_inicio DESC LIMIT 1');
                $umaStmt->execute();
                $valor_uma = $umaStmt->fetchColumn();
                if ($valor_uma === false) {
                    throw new Exception('Valor de UMA no encontrado');
                }
                $precio_unitario = $uma_valor * $valor_uma;
                $importe_bruto = $precio_unitario * $d['cantidad'];
                $descuento = floatval($d['descuento'] ?: 0);
                $importe_neto = $importe_bruto - $descuento;
                $total += $importe_neto;
            }
            $insertStmt = $this->pdo->prepare('INSERT INTO cobros (folio, rfc, nombre, telefono, direccion, total, observaciones, fecha, estatus, user_id) 
                                              VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)');
            $insertStmt->execute([
                $folio,
                $rfc,
                $nombre ?: NULL,
                $telefono ?: NULL,
                $direccion ?: NULL,
                $total,
                $observaciones ?: NULL,
                'pendiente',
                $user_id
            ]);
            $cobro_id = $this->pdo->lastInsertId();
            $insertDerechoStmt = $this->pdo->prepare('INSERT INTO cobro_derechos (cobro_id, derecho_id, cantidad, precio_unitario, importe_bruto, descuento, importe_neto) 
                                                     VALUES (?, ?, ?, ?, ?, ?, ?)');
            foreach ($derechos as $d) {
                $derechoStmt = $this->pdo->prepare('SELECT uma_valor FROM derechos WHERE id = ?');
                $derechoStmt->execute([$d['id']]);
                $uma_valor = $derechoStmt->fetchColumn();
                $umaStmt = $this->pdo->prepare('SELECT valor FROM uma ORDER BY fecha_inicio DESC LIMIT 1');
                $umaStmt->execute();
                $valor_uma = $umaStmt->fetchColumn();
                $precio_unitario = $uma_valor * $valor_uma;
                $importe_bruto = $precio_unitario * $d['cantidad'];
                $descuento = floatval($d['descuento'] ?: 0);
                $importe_neto = $importe_bruto - $descuento;
                $insertDerechoStmt->execute([$cobro_id, $d['id'], $d['cantidad'], $precio_unitario, $importe_bruto, $descuento, $importe_neto]);
            }
            $this->pdo->commit();
            return $cobro_id;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('Error al crear cobro: ' . $e->getMessage());
            throw new Exception('Error al crear cobro: ' . $e->getMessage());
        }
    }

    public function generateReceipt($id, $format) {
        try {
            $stmt = $this->pdo->prepare('SELECT c.*, 
                                         COALESCE(c.nombre, cont.nombre) AS nombre_contribuyente, 
                                         COALESCE(c.direccion, cont.direccion_fiscal) AS direccion_contribuyente, 
                                         c.telefono, 
                                         u.username AS usuario_nombre,
                                         (SELECT nombre FROM datos_municipio ORDER BY fecha_actualizacion DESC LIMIT 1) AS municipio_nombre,
                                         (SELECT direccion FROM datos_municipio ORDER BY fecha_actualizacion DESC LIMIT 1) AS municipio_direccion,
                                         (SELECT telefono FROM datos_municipio ORDER BY fecha_actualizacion DESC LIMIT 1) AS municipio_telefono,
                                         (SELECT correo FROM datos_municipio ORDER BY fecha_actualizacion DESC LIMIT 1) AS municipio_correo,
                                         (SELECT rfc FROM datos_municipio ORDER BY fecha_actualizacion DESC LIMIT 1) AS municipio_rfc,
                                         (SELECT horario_atencion FROM datos_municipio ORDER BY fecha_actualizacion DESC LIMIT 1) AS municipio_horario,
                                         (SELECT eslogan FROM datos_municipio ORDER BY fecha_actualizacion DESC LIMIT 1) AS municipio_eslogan,
                                         GROUP_CONCAT(
                                             CONCAT(
                                                 COALESCE(d.descripcion, "Desconocido"), 
                                                 "|", COALESCE(cd.cantidad, 0), 
                                                 "|", COALESCE(FORMAT(cd.precio_unitario, 2), "0.00"),
                                                 "|", COALESCE(FORMAT(cd.descuento, 2), "0.00"),
                                                 "|", COALESCE(FORMAT(cd.importe_neto, 2), "0.00")
                                             )
                                             SEPARATOR ";"
                                         ) AS derechos
                                         FROM cobros c
                                         LEFT JOIN contribuyentes cont ON c.rfc = cont.rfc
                                         LEFT JOIN usuarios u ON c.user_id = u.id
                                         LEFT JOIN cobro_derechos cd ON c.id = cd.cobro_id
                                         LEFT JOIN derechos d ON cd.derecho_id = d.id
                                         WHERE c.id = ?
                                         GROUP BY c.id');
            $stmt->execute([$id]);
            $cobro = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$cobro) {
                throw new Exception('Cobro no encontrado');
            }
            $numberToWordsMX = function ($number) {
                $units = ['', 'un', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
                $teens = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];
                $tens = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
                $hundreds = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];
                $thousands = ['mil', 'millón', 'millones'];

                $number = floatval($number);
                $pesos = floor($number);
                $centavos = round(($number - $pesos) * 100);

                $toWords = function ($n, $level = 0) use ($units, $teens, $tens, $hundreds, $thousands) {
                    if ($n == 0) return $level == 0 ? 'cero' : '';
                    $result = '';
                    if ($n >= 1000000) {
                        $millones = floor($n / 1000000);
                        $n = $n % 1000000;
                        $result .= $toWords($millones, 2) . ($millones == 1 ? " {$thousands[1]}" : " {$thousands[2]}");
                    }
                    if ($n >= 1000) {
                        $miles = floor($n / 1000);
                        $n = $n % 1000;
                        $result .= ($result ? ' ' : '') . ($miles == 1 ? 'mil' : $toWords($miles, 1) . " {$thousands[0]}");
                    }
                    if ($n >= 100) {
                        $centenas = floor($n / 100);
                        $n = $n % 100;
                        $result .= ($result ? ' ' : '') . ($centenas == 1 && $n == 0 ? 'cien' : $hundreds[$centenas]);
                    }
                    if ($n >= 20) {
                        $decenas = floor($n / 10);
                        $n = $n % 10;
                        $result .= ($result ? ' y ' : '') . $tens[$decenas];
                        if ($n > 0) $result .= ' y ' . $units[$n];
                    } elseif ($n >= 10) {
                        $result .= ($result ? ' ' : '') . $teens[$n - 10];
                    } elseif ($n > 0) {
                        $result .= ($result ? ' y ' : '') . $units[$n];
                    }
                    return $result;
                };

                $pesosText = $toWords($pesos);
                $pesosText = $pesosText ? ucfirst(trim($pesosText)) . ' pesos' : 'Cero pesos';
                $centavosText = sprintf('%02d', $centavos) . '/100 M.N.';
                return $pesosText . ' ' . $centavosText;
            };

            if ($format === 'recibo') {
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('SISMIN');
                $pdf->SetTitle('Recibo de Pago');
                $pdf->SetMargins(15, 15, 15);
                $pdf->SetAutoPageBreak(true, 15);
                $pdf->AddPage();
                $pdf->SetFont('helvetica', '', 10);
                $pdf->SetTextColor(51, 51, 51);
                $pdf->Image('assets/img/heraldica.png', 15, 10, 25, 0, '', '', '', false, 300);
                $pdf->Image('assets/img/logo.png', 170, 10, 25, 0, '', '', '', false, 300);
                $pdf->SetFont('helvetica', 'B', 16);
                $pdf->SetTextColor(75, 110, 168);
                $pdf->Ln(15);
                $pdf->Cell(0, 10, 'Recibo de Pago', 0, 1, 'C');
                $pdf->SetFont('helvetica', 'I', 10);
                $pdf->Cell(0, 6, $cobro['municipio_nombre'] ?? 'Municipio de Jalpan de Serra', 0, 1, 'C');
                $pdf->SetFont('helvetica', '', 9);
                $pdf->SetTextColor(51, 51, 51);
                $pdf->Ln(5);
                $pdf->SetLineStyle(['width' => 0.3, 'color' => [75, 110, 168]]);
                $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
                $pdf->Ln(5);
                $pdf->SetFillColor(233, 236, 239);
                $pdf->SetDrawColor(75, 110, 168);
                $pdf->SetLineStyle(['width' => 0.2]);
                $pdf->Rect(15, $pdf->GetY(), 180, 26, 'DF');
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(180, 8, 'Información del Municipio', 'LTR', 1, 'L', 1);
                $pdf->SetFont('helvetica', '', 9);
                $pdf->Cell(90, 6, 'RFC: ' . ($cobro['municipio_rfc'] ?? 'Sin RFC'), 'L', 0, 'L', 1);
                $pdf->Cell(90, 6, 'Folio: ' . ($cobro['folio'] ?? 'Sin Folio'), 'R', 1, 'L', 1);
                $pdf->Cell(180, 6, 'Dirección: ' . ($cobro['municipio_direccion'] ?? 'Sin Dirección'), 'LR', 1, 'L', 1);
                $pdf->Cell(180, 6, 'Teléfono: ' . ($cobro['municipio_telefono'] ?? 'Sin Teléfono') . ' | Correo: ' . ($cobro['municipio_correo'] ?? 'Sin Correo'), 'LR', 1, 'L', 1);
                $pdf->Cell(90, 6, 'Horario: ' . ($cobro['municipio_horario'] ?? 'Sin Horario'), 'LB', 0, 'L', 1);
                $pdf->Cell(90, 6, 'Fecha: ' . ($cobro['fecha'] ?? 'Sin Fecha'), 'RB', 1, 'L', 1);
                $pdf->Ln(8);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Rect(15, $pdf->GetY(), 180, 20, 'DF');
                $pdf->Cell(180, 8, 'Contribuyente', 'LTR', 1, 'L', 1);
                $pdf->SetFont('helvetica', '', 9);
                $pdf->Cell(180, 6, 'Nombre: ' . ($cobro['nombre_contribuyente'] ?? 'Sin Nombre'), 'LR', 1, 'L', 1);
                $pdf->Cell(180, 6, 'RFC: ' . ($cobro['rfc'] ?? 'Sin RFC'), 'LR', 1, 'L', 1);
                $pdf->Cell(180, 6, 'Dirección: ' . ($cobro['direccion_contribuyente'] ?? 'Sin Dirección'), 'LR', 1, 'L', 1);
                $pdf->Cell(180, 6, 'Teléfono: ' . ($cobro['telefono'] ?? 'Sin Teléfono'), 'LBR', 1, 'L', 1);
                $pdf->Ln(8);
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->SetFillColor(75, 110, 168);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Rect(15, $pdf->GetY(), 180, 8, 'DF');
                $pdf->Cell(20, 8, 'Cant.', 1, 0, 'C', 1);
                $pdf->Cell(70, 8, 'Descripción', 1, 0, 'L', 1);
                $pdf->Cell(30, 8, 'Unitario', 1, 0, 'R', 1);
                $pdf->Cell(30, 8, 'Descuento', 1, 0, 'R', 1);
                $pdf->Cell(30, 8, 'Total', 1, 1, 'R', 1);
                $pdf->SetFont('helvetica', '', 9);
                $pdf->SetTextColor(51, 51, 51);
                $derechos = $cobro['derechos'] ? explode(';', $cobro['derechos']) : [];
                $fill = 0;
                if (empty($derechos) || count($derechos) === 1 && empty(trim($derechos[0]))) {
                    $pdf->SetFillColor(245, 245, 245);
                    $pdf->Cell(180, 6, 'No se encontraron derechos asociados a este cobro.', 1, 1, 'L', 1);
                } else {
                    foreach ($derechos as $index => $derecho) {
                        if (empty(trim($derecho))) continue;
                        $parts = explode('|', $derecho);
                        if (count($parts) < 5) {
                            error_log('Formato de derecho inválido: ' . $derecho);
                            continue;
                        }
                        $descripcion = $parts[0];
                        $cantidad = $parts[1];
                        $precio_unitario = str_replace(',', '', $parts[2]);
                        $descuento = str_replace(',', '', $parts[3]);
                        $importe = str_replace(',', '', $parts[4]);
                        if ($fill) {
                            $pdf->SetFillColor(248, 249, 250);
                        } else {
                            $pdf->SetFillColor(255, 255, 255);
                        }
                        $pdf->Cell(20, 6, $cantidad, 1, 0, 'C', $fill);
                        $pdf->Cell(70, 6, $descripcion, 1, 0, 'L', $fill);
                        $pdf->Cell(30, 6, '$' . number_format($precio_unitario, 2), 1, 0, 'R', $fill);
                        $pdf->Cell(30, 6, $descuento > 0 ? '$' . number_format($descuento, 2) : '-', 1, 0, 'R', $fill);
                        $pdf->Cell(30, 6, '$' . number_format($importe, 2), 1, 1, 'R', $fill);
                        $fill = !$fill;
                    }
                }
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetTextColor(40, 167, 69);
                $pdf->SetDrawColor(75, 110, 168);
                $pdf->Rect(15, $pdf->GetY(), 180, 8, 'DF');
                $pdf->Cell(150, 8, 'Total', 'TLB', 0, 'R', 1);
                $pdf->Cell(30, 8, '$' . number_format($cobro['total'], 2), 'TRB', 1, 'R', 1);
                $pdf->SetFont('helvetica', 'I', 9);
                $pdf->SetTextColor(51, 51, 51);
                $pdf->Ln(5);
                $pdf->Cell(180, 6, 'Cantidad en letras: ' . $numberToWordsMX($cobro['total']), 0, 1, 'L');
                $pdf->Ln(5);
                if (!empty(trim($cobro['observaciones'] ?? ''))) {
                    $pdf->SetFont('helvetica', 'B', 8);
                    $pdf->SetFillColor(233, 236, 239);
                    $pdf->Rect(15, $pdf->GetY(), 180, 18, 'DF');
                    $pdf->Cell(180, 8, 'Observaciones', 'LTR', 1, 'L', 1);
                    $pdf->SetFont('helvetica', '', 8);
                    $pdf->MultiCell(180, 10, trim($cobro['observaciones']), 'LBR', 'L', 1);
                    $pdf->Ln(5);
                }
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->Cell(180, 6, 'Atendido por: ' . ($cobro['usuario_nombre'] ?? 'Sistema SISMIN'), 0, 1, 'L');
                $pdf->Ln(8);
                $qrUrl = BASE_URL . 'cobro/receipt?id=' . $id . '&format=pdf';
                $pdf->write2DBarcode($qrUrl, 'QRCODE,L', 15, $pdf->GetY(), 20, 20, ['border' => false], 'N');
                $pdf->SetFont('helvetica', '', 7);
                $pdf->Cell(180, 4, 'Escanea para verificar', 0, 1, 'L');
                $pdf->Ln(10);
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->SetTextColor(75, 110, 168);
                $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
                $pdf->Ln(2);
                $pdf->Cell(0, 5, 'Gracias por su pago. Sistema SISMIN.', 0, 1, 'C');
                $eslogan = $cobro['municipio_eslogan'] ? ($cobro['municipio_nombre'] . ' - ' . $cobro['municipio_eslogan']) : 'Municipio de Jalpan de Serra - Comprometidos con tu bienestar';
                $pdf->Cell(0, 5, $eslogan, 0, 1, 'C');
                $pdf->Output('recibo_' . $id . '.pdf', 'I');
            } else {
                header('Content-Type: text/plain');
                echo "=== Recibo de Pago ===\n";
                echo "Folio: " . ($cobro['folio'] ?? '') . "\n";
                echo "Fecha: " . ($cobro['fecha'] ?? '') . "\n";
                echo "Municipio: " . ($cobro['municipio_nombre'] ?? '') . "\n";
                echo "RFC: " . ($cobro['municipio_rfc'] ?? '') . "\n";
                echo "Dirección: " . ($cobro['municipio_direccion'] ?? '') . "\n";
                echo "Teléfono: " . ($cobro['municipio_telefono'] ?? '') . "\n";
                echo "Correo: " . ($cobro['municipio_correo'] ?? '') . "\n";
                echo "Horario: " . ($cobro['municipio_horario'] ?? '') . "\n";
                echo "Eslogan: " . ($cobro['municipio_eslogan'] ?? '') . "\n";
                echo "RFC: " . ($cobro['rfc'] ?? '') . "\n";
                echo "Nombre: " . ($cobro['nombre_contribuyente'] ?? '') . "\n";
                echo "Dirección: " . ($cobro['direccion_contribuyente'] ?? '') . "\n";
                echo "Teléfono: " . ($cobro['telefono'] ?? '') . "\n";
                echo "Derechos: " . ($cobro['derechos'] ?? '') . "\n";
                echo "Total: $" . number_format($cobro['total'], 2) . "\n";
                echo "Total en letra: " . $numberToWordsMX($cobro['total']) . "\n";
                if (!empty(trim($cobro['observaciones'] ?? ''))) {
                    echo "Observaciones: " . trim($cobro['observaciones']) . "\n";
                }
                echo "Le atendió: " . ($cobro['usuario_nombre'] ?? 'Sistema SISMIN') . "\n";
                echo "Verificar: " . BASE_URL . 'cobro/receipt?id=' . $id . '&format=pdf' . "\n";
                echo "==================\n";
            }
        } catch (Exception $e) {
            error_log('Error al generar recibo: ' . $e->getMessage());
            throw new Exception('Error al generar recibo: ' . $e->getMessage());
        }
    }

    public function getAll($filters = [], $limit = 10, $offset = 0) {
        try {
            $query = '
                SELECT c.id, c.folio, c.rfc, COALESCE(c.nombre, cont.nombre) AS nombre_contribuyente, 
                       c.total, c.fecha, c.estatus, u.username AS usuario_nombre
                FROM cobros c
                LEFT JOIN contribuyentes cont ON c.rfc = cont.rfc
                LEFT JOIN usuarios u ON c.user_id = u.id
                WHERE 1=1
            ';
            $params = [];

            if (!empty($filters['folio'])) {
                $query .= ' AND c.folio LIKE ?';
                $params[] = '%' . $filters['folio'] . '%';
            }
            if (!empty($filters['rfc'])) {
                $query .= ' AND c.rfc LIKE ?';
                $params[] = '%' . $filters['rfc'] . '%';
            }
            if (!empty($filters['nombre'])) {
                $query .= ' AND (c.nombre LIKE ? OR cont.nombre LIKE ?)';
                $params[] = '%' . $filters['nombre'] . '%';
                $params[] = '%' . $filters['nombre'] . '%';
            }
            if (!empty($filters['fecha_desde'])) {
                $query .= ' AND c.fecha >= ?';
                $params[] = $filters['fecha_desde'];
            }
            if (!empty($filters['fecha_hasta'])) {
                $query .= ' AND c.fecha <= ?';
                $params[] = $filters['fecha_hasta'];
            }

            $query .= ' ORDER BY c.fecha DESC';

            $stmt = $this->pdo->prepare($query);
            foreach ($params as $index => $param) {
                $stmt->bindValue($index + 1, $param);
            }

            $stmt->execute();
            $cobros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $countQuery = '
                SELECT COUNT(*) 
                FROM cobros c
                LEFT JOIN contribuyentes cont ON c.rfc = cont.rfc
                WHERE 1=1
            ';
            $countParams = [];
            if (!empty($filters['folio'])) {
                $countQuery .= ' AND c.folio LIKE ?';
                $countParams[] = '%' . $filters['folio'] . '%';
            }
            if (!empty($filters['rfc'])) {
                $countQuery .= ' AND c.rfc LIKE ?';
                $countParams[] = '%' . $filters['rfc'] . '%';
            }
            if (!empty($filters['nombre'])) {
                $countQuery .= ' AND (c.nombre LIKE ? OR cont.nombre LIKE ?)';
                $countParams[] = '%' . $filters['nombre'] . '%';
                $countParams[] = '%' . $filters['nombre'] . '%';
            }
            if (!empty($filters['fecha_desde'])) {
                $countQuery .= ' AND c.fecha >= ?';
                $countParams[] = $filters['fecha_desde'];
            }
            if (!empty($filters['fecha_hasta'])) {
                $countQuery .= ' AND c.fecha <= ?';
                $countParams[] = $filters['fecha_hasta'];
            }

            $countStmt = $this->pdo->prepare($countQuery);
            $countStmt->execute($countParams);
            $totalRecords = $countStmt->fetchColumn();

            return [
                'cobros' => $cobros,
                'totalRecords' => $totalRecords
            ];
        } catch (PDOException $e) {
            error_log('Error al obtener cobros: ' . $e->getMessage());
            throw new Exception('Error al obtener cobros: ' . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare('DELETE FROM cobro_derechos WHERE cobro_id = ?');
            $stmt->execute([$id]);
            $stmt = $this->pdo->prepare('DELETE FROM cobros WHERE id = ?');
            $stmt->execute([$id]);
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('Error al eliminar cobro: ' . $e->getMessage());
            throw new Exception('Error al eliminar cobro: ' . $e->getMessage());
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT c.*, 
                       COALESCE(c.nombre, cont.nombre) AS nombre_contribuyente,
                       COALESCE(c.direccion, cont.direccion_fiscal) AS direccion_contribuyente,
                       c.telefono, c.observaciones,
                       GROUP_CONCAT(
                           CONCAT(
                               d.id, "|", d.codigo, "|", d.descripcion, "|", cd.cantidad, "|", 
                               cd.precio_unitario, "|", cd.descuento, "|", cd.importe_neto
                           )
                           SEPARATOR ";"
                       ) AS derechos
                FROM cobros c
                LEFT JOIN contribuyentes cont ON c.rfc = cont.rfc
                LEFT JOIN cobro_derechos cd ON c.id = cd.cobro_id
                LEFT JOIN derechos d ON cd.derecho_id = d.id
                WHERE c.id = ?
                GROUP BY c.id
            ');
            $stmt->execute([$id]);
            $cobro = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$cobro) {
                throw new Exception('Cobro no encontrado');
            }
            $cobro['derechos'] = $cobro['derechos'] ? array_map(function($d) {
                $parts = explode('|', $d);
                return [
                    'id' => $parts[0],
                    'codigo' => $parts[1],
                    'descripcion' => $parts[2],
                    'cantidad' => $parts[3],
                    'precio_unitario' => $parts[4],
                    'descuento' => $parts[5],
                    'importe_neto' => $parts[6]
                ];
            }, explode(';', $cobro['derechos'])) : [];
            return $cobro;
        } catch (PDOException $e) {
            error_log('Error al obtener cobro por ID: ' . $e->getMessage());
            throw new Exception('Error al obtener cobro: ' . $e->getMessage());
        }
    }

    public function update($id, $rfc, $nombre, $telefono, $direccion, $observaciones, $derechos) {
        try {
            $this->pdo->beginTransaction();
            $total = 0;
            foreach ($derechos as $d) {
                $derechoStmt = $this->pdo->prepare('SELECT uma_valor FROM derechos WHERE id = ?');
                $derechoStmt->execute([$d['id']]);
                $uma_valor = $derechoStmt->fetchColumn();
                if ($uma_valor === false) {
                    throw new Exception('Derecho no encontrado: ID ' . $d['id']);
                }
                $umaStmt = $this->pdo->prepare('SELECT valor FROM uma ORDER BY fecha_inicio DESC LIMIT 1');
                $umaStmt->execute();
                $valor_uma = $umaStmt->fetchColumn();
                if ($valor_uma === false) {
                    throw new Exception('Valor de UMA no encontrado');
                }
                $precio_unitario = $uma_valor * $valor_uma;
                $importe_bruto = $precio_unitario * $d['cantidad'];
                $descuento = floatval($d['descuento'] ?: 0);
                $importe_neto = $importe_bruto - $descuento;
                $total += $importe_neto;
            }
            $updateStmt = $this->pdo->prepare('
                UPDATE cobros 
                SET rfc = ?, nombre = ?, telefono = ?, direccion = ?, total = ?, observaciones = ?
                WHERE id = ?
            ');
            $updateStmt->execute([
                $rfc,
                $nombre ?: NULL,
                $telefono ?: NULL,
                $direccion ?: NULL,
                $total,
                $observaciones ?: NULL,
                $id
            ]);
            $deleteDerechosStmt = $this->pdo->prepare('DELETE FROM cobro_derechos WHERE cobro_id = ?');
            $deleteDerechosStmt->execute([$id]);
            $insertDerechoStmt = $this->pdo->prepare('
                INSERT INTO cobro_derechos (cobro_id, derecho_id, cantidad, precio_unitario, importe_bruto, descuento, importe_neto) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');
            foreach ($derechos as $d) {
                $derechoStmt = $this->pdo->prepare('SELECT uma_valor FROM derechos WHERE id = ?');
                $derechoStmt->execute([$d['id']]);
                $uma_valor = $derechoStmt->fetchColumn();
                $umaStmt = $this->pdo->prepare('SELECT valor FROM uma ORDER BY fecha_inicio DESC LIMIT 1');
                $umaStmt->execute();
                $valor_uma = $umaStmt->fetchColumn();
                $precio_unitario = $uma_valor * $valor_uma;
                $importe_bruto = $precio_unitario * $d['cantidad'];
                $descuento = floatval($d['descuento'] ?: 0);
                $importe_neto = $importe_bruto - $descuento;
                $insertDerechoStmt->execute([$id, $d['id'], $d['cantidad'], $precio_unitario, $importe_bruto, $descuento, $importe_neto]);
            }
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('Error al actualizar cobro: ' . $e->getMessage());
            throw new Exception('Error al actualizar cobro: ' . $e->getMessage());
        }
    }
}