<?php
class ProductoModel {
  public function __construct(private PDO $db) {}

  public function destacados(?string $q, int $cat): array {
    $sql = "SELECT * FROM productos WHERE destacado=1 AND activo=1";
    $p = [];
    if ($q) { $sql .= " AND (nombre LIKE ? OR descripcion LIKE ?)"; $p[]="%$q%"; $p[]="%$q%"; }
    if ($cat>0) { $sql .= " AND categoria_id=?"; $p[]=$cat; }
    $sql .= " ORDER BY id DESC";
    $st = $this->db->prepare($sql);
    $st->execute($p);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }
}
