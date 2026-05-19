USE nutriexperto;
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE receta_alimentos;
TRUNCATE TABLE recetas;
SET FOREIGN_KEY_CHECKS = 1;

SELECT id, nombre FROM recetas ORDER BY id;

ALTER TABLE recetas AUTO_INCREMENT = 1;

SELECT id, nombre FROM alimentos ORDER BY id;

SELECT id, nombre FROM recetas ORDER BY id;

SELECT a.id, a.nombre, a.categoria
FROM alimentos a
LEFT JOIN receta_alimentos ra ON a.id = ra.alimento_id
WHERE ra.alimento_id IS NULL
ORDER BY a.categoria;

USE nutriexperto;
ALTER TABLE usuarios MODIFY tipo ENUM('free','premium','admin') DEFAULT 'free';

-- Crear usuario administrador
INSERT INTO usuarios (nombre, email, password, tipo) VALUES
('Administrador', 'admin@nutriexperto.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Contraseña: password

ALTER TABLE recetas ADD COLUMN activo TINYINT(1) DEFAULT 1;
UPDATE recetas SET activo = 1;

USE nutriexperto;
ALTER TABLE alimentos ADD COLUMN activo TINYINT(1) DEFAULT 1;
UPDATE alimentos SET activo = 1;