CREATE DATABASE IF NOT EXISTS nutriexperto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nutriexperto;

-- USUARIOS
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    tipo ENUM('free', 'premium') DEFAULT 'free',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1
);

-- ALIMENTOS
CREATE TABLE alimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    categoria ENUM('proteina','verdura','fruta','cereal','lacteo','grasa_saludable') NOT NULL,
    calorias_100g INT DEFAULT 0,
    proteinas_g DECIMAL(5,2) DEFAULT 0,
    carbohidratos_g DECIMAL(5,2) DEFAULT 0,
    grasas_g DECIMAL(5,2) DEFAULT 0,
    apto_vegano TINYINT(1) DEFAULT 0,
    apto_sin_gluten TINYINT(1) DEFAULT 0,
    emoji VARCHAR(10) DEFAULT '🥗'
);

-- RECETAS
CREATE TABLE recetas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    instrucciones TEXT,
    tiempo_minutos INT DEFAULT 30,
    dificultad ENUM('facil','media','dificil') DEFAULT 'facil',
    calorias_aprox INT DEFAULT 0,
    es_premium TINYINT(1) DEFAULT 0
);

-- RELACION RECETA <-> ALIMENTOS
CREATE TABLE receta_alimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receta_id INT NOT NULL,
    alimento_id INT NOT NULL,
    es_principal TINYINT(1) DEFAULT 1,
    FOREIGN KEY (receta_id) REFERENCES recetas(id) ON DELETE CASCADE,
    FOREIGN KEY (alimento_id) REFERENCES alimentos(id) ON DELETE CASCADE
);

-- HISTORIAL DE CONSULTAS (Data Warehouse + perfiles)
CREATE TABLE historial_consultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT DEFAULT NULL,
    alimentos_seleccionados TEXT NOT NULL,
    recetas_recomendadas TEXT,
    puntuacion_salud INT DEFAULT 0,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- RECETAS GUARDADAS (solo premium)
CREATE TABLE recetas_guardadas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    receta_id INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (receta_id) REFERENCES recetas(id) ON DELETE CASCADE
);

-- DATA WAREHOUSE: estadísticas de alimentos
CREATE TABLE dw_alimentos_populares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alimento_id INT NOT NULL,
    total_selecciones INT DEFAULT 0,
    fecha_actualizacion DATE,
    FOREIGN KEY (alimento_id) REFERENCES alimentos(id)
);

-- DATA WAREHOUSE: estadísticas de recetas
CREATE TABLE dw_recetas_populares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receta_id INT NOT NULL,
    total_recomendaciones INT DEFAULT 0,
    fecha_actualizacion DATE,
    FOREIGN KEY (receta_id) REFERENCES recetas(id)
);