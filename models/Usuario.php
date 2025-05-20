<?php
class Usuario {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getUserPermissions($user_id) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT p.nombre
                FROM usuarios_permisos up
                JOIN permisos p ON up.permiso_id = p.id
                WHERE up.usuario_id = ? AND up.habilitado = 1
            ');
            $stmt->execute([$user_id]);
            $userPerms = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $stmt = $this->pdo->prepare('
                SELECT p.nombre
                FROM usuarios u
                JOIN roles r ON u.role_id = r.id
                JOIN roles_permisos rp ON r.id = rp.role_id
                JOIN permisos p ON rp.permiso_id = p.id
                WHERE u.id = ? AND p.nombre NOT IN (
                    SELECT p2.nombre FROM usuarios_permisos up2
                    JOIN permisos p2 ON up2.permiso_id = p2.id
                    WHERE up2.usuario_id = ?
                )
            ');
            $stmt->execute([$user_id, $user_id]);
            $rolePerms = $stmt->fetchAll(PDO::FETCH_COLUMN);

            return array_unique(array_merge($userPerms, $rolePerms));
        } catch (PDOException $e) {
            error_log('Error en Usuario::getUserPermissions: ' . $e->getMessage());
            return ['view_cobros'];
        }
    }
}