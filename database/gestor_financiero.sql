-- ============================================
-- BASE DE DATOS: GESTOR FINANCIERO
-- ============================================
CREATE DATABASE IF NOT EXISTS gestor_financiero
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE gestor_financiero;

-- ============================================
-- TABLA: USUARIO
-- ============================================
CREATE TABLE usuario (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  telefono VARCHAR(20),
  rol ENUM('invitado','registrado','premium') DEFAULT 'registrado',
  estado ENUM('activo','inactivo') DEFAULT 'activo',
  fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
  ultimo_acceso DATETIME
);

-- ============================================
-- TABLA: CATEGORIA
-- ============================================
CREATE TABLE categoria (
  id_categoria INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  tipo ENUM('ingreso','gasto') NOT NULL,
  color VARCHAR(20) DEFAULT '#3498db',
  icono VARCHAR(50),
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- ============================================
-- TABLA: TRANSACCION
-- ============================================
CREATE TABLE transaccion (
  id_transaccion INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  id_categoria INT NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  tipo ENUM('ingreso','gasto') NOT NULL,
  descripcion VARCHAR(255),
  fecha DATE NOT NULL,
  metodo_pago ENUM('efectivo','tarjeta','transferencia') DEFAULT 'efectivo',
  recurrente BOOLEAN DEFAULT FALSE,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
  FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE CASCADE
);

-- ============================================
-- TABLA: PRESUPUESTO
-- ============================================
CREATE TABLE presupuesto (
  id_presupuesto INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  mes INT NOT NULL,
  anio INT NOT NULL,
  monto_total DECIMAL(12,2) NOT NULL,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
  UNIQUE KEY unique_presupuesto (id_usuario, mes, anio)
);

-- ============================================
-- TABLA: META
-- ============================================
CREATE TABLE meta (
  id_meta INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  monto_objetivo DECIMAL(12,2) NOT NULL,
  monto_actual DECIMAL(12,2) DEFAULT 0,
  fecha_inicio DATE NOT NULL,
  fecha_limite DATE NOT NULL,
  estado ENUM('activa','completada','cancelada') DEFAULT 'activa',
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- ============================================
-- TABLA: DEUDA
-- ============================================
CREATE TABLE deuda (
  id_deuda INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  acreedor VARCHAR(150) NOT NULL,
  monto_total DECIMAL(12,2) NOT NULL,
  monto_pagado DECIMAL(12,2) DEFAULT 0,
  tasa_interes DECIMAL(5,2) DEFAULT 0,
  fecha_inicio DATE NOT NULL,
  fecha_vencimiento DATE NOT NULL,
  pago_minimo DECIMAL(12,2),
  estado ENUM('activa','pagada','vencida') DEFAULT 'activa',
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- ============================================
-- DATOS INICIALES
-- ============================================
-- Password: admin123 (hash bcrypt)
INSERT INTO usuario (nombre, email, password_hash, rol) VALUES
('Admin Demo', 'admin@demo.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdBqT9m8xvJ0Z5nO2', 'premium'),
('Usuario Demo', 'user@demo.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdBqT9m8xvJ0Z5nO2', 'registrado');

INSERT INTO categoria (id_usuario, nombre, tipo) VALUES
(1, 'Salario', 'ingreso'),
(1, 'Comida', 'gasto'),
(1, 'Transporte', 'gasto'),
(2, 'Salario', 'ingreso'),
(2, 'Comida', 'gasto');