<?php
// Script para crear rol de administrador y asignarlo al usuario admin
require __DIR__.'/../current/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Cargar variables de entorno
if (file_exists(__DIR__.'/../current/.env')) {
    $dotenv = new Dotenv();
    $dotenv->load(__DIR__.'/../current/.env');
}

$kernel = new \App\Kernel($_ENV['APP_ENV'] ?? 'prod', false);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');
$connection = $em->getConnection();

// Verificar si ya existe un rol de administrador
$stmt = $connection->prepare("SELECT id FROM privilegio_roles WHERE descripcion = :desc");
$stmt->execute(['desc' => 'Administrador']);
$existingRole = $stmt->fetch();

if ($existingRole) {
    echo "Rol 'Administrador' ya existe con ID: " . $existingRole['id'] . "\n";
    $roleId = $existingRole['id'];
} else {
    echo "Creando rol 'Administrador' con todos los permisos...\n";
    
    // Crear rol con todos los permisos habilitados (todos los campos booleanos en 1)
    // Usamos un INSERT con todos los campos en 1
    $sql = "INSERT INTO privilegio_roles (descripcion";
    $values = "VALUES ('Administrador'";
    
    // Obtener todos los campos booleanos de la tabla
    $columns = $connection->executeQuery("SHOW COLUMNS FROM privilegio_roles WHERE Type LIKE 'tinyint%'")->fetchAll();
    
    foreach ($columns as $column) {
        if ($column['Field'] !== 'id') {
            $sql .= ", " . $column['Field'];
            $values .= ", 1";
        }
    }
    
    $sql .= ") " . $values . ")";
    $connection->executeQuery($sql);
    
    $roleId = $connection->lastInsertId();
    echo "Rol creado con ID: $roleId\n";
}

// Asignar el rol al usuario admin
echo "Asignando rol al usuario admin...\n";
$stmt = $connection->prepare("UPDATE fos_user SET rol_id = :roleId WHERE username = 'admin'");
$stmt->execute(['roleId' => $roleId]);

$affected = $stmt->rowCount();
if ($affected > 0) {
    echo "✓ Rol asignado correctamente al usuario admin\n";
} else {
    echo "⚠ No se pudo asignar el rol (usuario admin puede no existir)\n";
}

echo "\n";
echo "Rol ID: $roleId\n";
echo "Usuario admin ahora tiene el rol 'Administrador' asignado.\n";

