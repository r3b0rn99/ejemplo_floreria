<?php
// includes/pdf/generar_pdf.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../lib/phpqrcode/qrlib.php';
require_once __DIR__ . '/../../vendor/fpdf186/fpdf.php';

/**
 * FPDF trabaja en ISO-8859-1. Esta función convierte texto desde UTF-8.
 * Además intenta corregir casos típicos de "mojibake" (AjuÃ±iga, JosÃ©, etc.).
 */
function pdf_text($texto) {
    if ($texto === null) return '';
    $texto = (string)$texto;

    // Mojibake común: UTF-8 interpretado como ISO-8859-1
    if (strpos($texto, 'Ã') !== false || strpos($texto, 'Â') !== false) {
        $fixed = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $texto);
        if ($fixed !== false) $texto = $fixed;
    }

    $out = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $texto);
    return ($out !== false) ? $out : $texto;
}

function only_digits($s) {
    return preg_replace('/\D+/', '', (string)$s);
}

class TicketPDF extends FPDF
{
    public function __construct($heightMm = 230)
    {
        parent::__construct('P', 'mm', [80, $heightMm]); // Ticket 80mm
        $this->SetMargins(4, 4, 4);
        $this->SetAutoPageBreak(true, 8);
    }

    function Header()
    {
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 5, pdf_text(FLORERIA_NOMBRE), 0, 1, 'C');

        $this->SetFont('Arial', '', 8);
        $this->MultiCell(0, 4, pdf_text(FLORERIA_DIRECCION), 0, 'C');
        $this->Cell(0, 4, pdf_text('Tel: ' . FLORERIA_TELEFONO), 0, 1, 'C');

        $this->Ln(1);
        $this->hr();
    }

    function Footer()
    {
        $this->SetY(-7);
        $this->SetFont('Arial', 'I', 7);
        $this->Cell(0, 4, pdf_text('Gracias por su compra'), 0, 0, 'C');
    }

    function hr()
    {
        $x1 = $this->lMargin;
        $x2 = $this->w - $this->rMargin;
        $y  = $this->GetY();
        $this->Line($x1, $y, $x2, $y);
        $this->Ln(2);
    }

    function kv($k, $v)
    {
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(18, 4, pdf_text($k . ':'), 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->MultiCell(0, 4, pdf_text($v), 0, 'L');
    }
}

// ---------- FUNCIÓN PRINCIPAL ----------
function generarBoletaPDF($pedido_id)
{
    $db = new Database();
    $connection = $db->getConnection();

    // Obtener datos del pedido
    $query = "SELECT p.*, u.nombre AS cliente_nombre, u.email
              FROM pedidos p
              JOIN usuarios u ON p.usuario_id = u.id
              WHERE p.id = ?";
    $stmt = $connection->prepare($query);
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        die("Pedido no encontrado.");
    }

    // Obtener productos del pedido
    $query = "SELECT dp.*, pr.nombre AS producto_nombre
              FROM detalle_pedido dp
              JOIN productos pr ON dp.producto_id = pr.id
              WHERE dp.pedido_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->execute([$pedido_id]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($productos)) {
        die("El pedido no tiene productos.");
    }

    // Preparar datos para el ticket
    $data = [];
    $subtotal_calc = 0;
    foreach ($productos as $producto) {
        $sub = (float)$producto['subtotal'];
        $data[] = [
            'producto' => $producto['producto_nombre'],
            'cantidad' => (int)$producto['cantidad'],
            'precio'   => (float)$producto['precio_unitario'],
            'subtotal' => $sub
        ];
        $subtotal_calc += $sub;
    }

    // Totales: usar los valores guardados en la BD si existen (subtotal/envio/total)
    $subtotal = ($pedido['subtotal'] !== null) ? (float)$pedido['subtotal'] : (float)$subtotal_calc;
    $envio    = ($pedido['envio'] !== null)    ? (float)$pedido['envio']    : 15.00;
    $total    = ($pedido['total'] !== null)    ? (float)$pedido['total']    : ($subtotal + $envio);

    // WhatsApp QR
    $waPhone = only_digits(defined('FLORERIA_WHATSAPP') ? FLORERIA_WHATSAPP : FLORERIA_TELEFONO);
    if ($waPhone === '') $waPhone = '51999999999';

    $msg = "Hola, necesito ayuda con mi pedido {$pedido['codigo_pedido']}.";
    $waUrl = "https://wa.me/{$waPhone}?text=" . rawurlencode($msg);

    // Asegurar carpeta QR
    $qrDir = __DIR__ . '/../../assets/qr/';
    if (!is_dir($qrDir)) {
        mkdir($qrDir, 0777, true);
    }
    $qrFile = "qr_{$pedido['codigo_pedido']}.png";
    $qrPath = $qrDir . $qrFile;

    // Generar QR (si ya existe, lo reusamos)
    $qrEnabled = extension_loaded('gd') && function_exists('imagecreate');
    if ($qrEnabled) {
        if (!file_exists($qrPath)) {
            // ECC M, tamaño 4, margen 1
            QRcode::png($waUrl, $qrPath, QR_ECLEVEL_M, 4, 1);
        }
    } else {
        // Si no hay GD, evitamos fatal y solo mostraremos el link.
        $qrPath = '';
    }

// Calcular altura aproximada del ticket según cantidad de productos
    $pageHeight = 160 + (count($data) * 9);
    if ($pageHeight < 220) $pageHeight = 220;
    if ($pageHeight > 290) $pageHeight = 290;

    // Crear PDF Ticket
    $pdf = new TicketPDF($pageHeight);
    $pdf->AddPage();

    // Encabezado de la boleta
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(0, 4, pdf_text('BOLETA DE VENTA'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 4, pdf_text('Nro: ' . $pedido['codigo_pedido']), 0, 1, 'C');
    $pdf->hr();

    // Datos
    $pdf->kv('Fecha', $pedido['fecha_pedido']);
    $pdf->kv('Cliente', $pedido['cliente_nombre']);
    $pdf->kv('Email', $pedido['email']);
    if (!empty($pedido['direccion_envio'])) {
        $pdf->kv('Entrega', $pedido['direccion_envio']);
    }
    if (!empty($pedido['metodo_pago'])) {
        $pdf->kv('Pago', $pedido['metodo_pago']);
    }
    $pdf->hr();

    // Productos
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(0, 4, pdf_text('Detalle'), 0, 1, 'L');
    $pdf->SetFont('Courier', '', 8);

    foreach ($data as $row) {
        $name = trim((string)$row['producto']);
        $pdf->MultiCell(0, 4, pdf_text($name), 0, 'L');

        $pdf->Cell(12, 4, 'x' . $row['cantidad'], 0, 0, 'L');
        $pdf->Cell(28, 4, 'S/ ' . number_format($row['precio'], 2), 0, 0, 'R');
        $pdf->Cell(0, 4, 'S/ ' . number_format($row['subtotal'], 2), 0, 1, 'R');
    }

    $pdf->hr();

    // Totales
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(0, 4, pdf_text('Subtotal: ') . 'S/ ' . number_format($subtotal, 2), 0, 1, 'R');
    $pdf->Cell(0, 4, pdf_text('Envio: ') . 'S/ ' . number_format($envio, 2), 0, 1, 'R');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 5, pdf_text('TOTAL: ') . 'S/ ' . number_format($total, 2), 0, 1, 'R');

    $pdf->Ln(2);
    $pdf->hr();

    // QR y texto
    if (!empty($qrPath) && file_exists($qrPath)) {
        $qrSize = 30;
                // En FPDF 1.86 las propiedades $w/$h son protected; usar el getter.
        $pageW = method_exists($pdf, 'GetPageWidth') ? $pdf->GetPageWidth() : 80;
        $x = ($pageW - $qrSize) / 2;
        $y = $pdf->GetY();
        $pdf->Image($qrPath, $x, $y, $qrSize, $qrSize);
        $pdf->Ln($qrSize + 2);

        $pdf->SetFont('Arial', '', 8);
        $pdf->MultiCell(0, 4, pdf_text('Escanea y escribenos por WhatsApp'), 0, 'C');
        $pdf->SetFont('Courier', '', 7);
        $pdf->MultiCell(0, 3.5, pdf_text('wa.me/' . $waPhone), 0, 'C');
    }
    else {
        // Sin imagen QR (por ejemplo, GD deshabilitado): imprimir link
        $pdf->SetFont('Arial', '', 8);
        $pdf->MultiCell(0, 4, pdf_text('Escribenos por WhatsApp:'), 0, 'C');
        $pdf->SetFont('Courier', '', 7);
        $pdf->MultiCell(0, 3.5, pdf_text('wa.me/' . $waPhone), 0, 'C');
    }

    // Asegurar carpeta boletas
    $boletasDir = __DIR__ . '/../../assets/boletas/';
    if (!is_dir($boletasDir)) {
        mkdir($boletasDir, 0777, true);
    }

    // Guardar PDF
    $filename = 'boleta_' . $pedido['codigo_pedido'] . '.pdf';
    $filepath = $boletasDir . $filename;
    $pdf->Output('F', $filepath);

    // Verificar si ya existe una boleta para este pedido
    $query = "SELECT id FROM boletas WHERE pedido_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->execute([$pedido_id]);
    $boleta = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($boleta) {
        // Ya existe: actualizamos datos
        $query = "UPDATE boletas 
                  SET numero_boleta = ?, pdf_path = ? 
                  WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([
            $pedido['codigo_pedido'],
            $filename,
            $boleta['id']
        ]);
    } else {
        // No existe: insertamos nuevo registro
        $query = "INSERT INTO boletas (pedido_id, numero_boleta, pdf_path) 
                  VALUES (?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->execute([
            $pedido_id,
            $pedido['codigo_pedido'],
            $filename
        ]);
    }

    return [$filename, $filepath];
}

// ---------- CONTROLADOR: manejar la petición GET ----------

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("ID de pedido no válido.");
}

$pedido_id = (int) $_GET['id'];

list($filename, $filepath) = generarBoletaPDF($pedido_id);

// Enviar el PDF al navegador
if (file_exists($filepath)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    readfile($filepath);
    exit;
} else {
    die("No se pudo generar la boleta.");
}
