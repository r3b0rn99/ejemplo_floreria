<?php
class ReporteController {
    public function __construct(private PDO $db) {}

    public function ventas(string $desde = '', string $hasta = '', string $estado = ''): array
    {
        $sql = "SELECT 
                    p.id,
                    p.usuario_id,
                    u.nombre AS cliente_nombre,
                    u.email AS cliente_email,
                    p.total,
                    p.metodo_pago,
                    p.estado,
                    p.fecha_pedido
                FROM pedidos p
                LEFT JOIN usuarios u ON u.id = p.usuario_id
                WHERE 1=1";
        $params = [];

        if ($desde !== '') {
            $sql .= " AND DATE(p.fecha_pedido) >= ?";
            $params[] = $desde;
        }
        if ($hasta !== '') {
            $sql .= " AND DATE(p.fecha_pedido) <= ?";
            $params[] = $hasta;
        }
        if ($estado !== '') {
            $sql .= " AND p.estado = ?";
            $params[] = $estado;
        }

        $sql .= " ORDER BY p.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
