-- ============================================
-- BASE DE DATOS: GESTOR FINANCIERO (FinanIA)
-- Versión completa actualizada
-- ============================================

CREATE DATABASE IF NOT EXISTS gestor_financiero
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE gestor_financiero;

-- ============================================
-- 1. USUARIO
-- ============================================
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) NULL,
    rol ENUM('invitado','registrado','premium') DEFAULT 'registrado',
    estado ENUM('activo','inactivo') DEFAULT 'activo',
    tema ENUM('oscuro','claro') DEFAULT 'oscuro',
    onboarding_completado BOOLEAN DEFAULT FALSE,
    premium_desde DATETIME NULL,
    metodo_pago VARCHAR(255) NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso DATETIME NULL
);

-- ============================================
-- 2. CUENTA
-- ============================================
CREATE TABLE IF NOT EXISTS cuenta (
    id_cuenta INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('efectivo','ahorros','corriente','tarjeta','inversion','otro') DEFAULT 'efectivo',
    saldo_inicial DECIMAL(12,2) DEFAULT 0,
    etiqueta VARCHAR(150) NULL,
    color VARCHAR(20) DEFAULT '#3498db',
    activa BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- ============================================
-- 3. CATEGORIA
-- ============================================
CREATE TABLE IF NOT EXISTS categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('ingreso','gasto') NOT NULL,
    color VARCHAR(20) DEFAULT '#3498db',
    icono VARCHAR(50) NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- ============================================
-- 4. FUENTE_INGRESO
-- ============================================
CREATE TABLE IF NOT EXISTS fuente_ingreso (
    id_fuente INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('fijo','variable','esporadico') DEFAULT 'variable',
    frecuencia ENUM('mensual','quincenal','semanal','irregular') DEFAULT 'irregular',
    monto_promedio DECIMAL(12,2) DEFAULT 0,
    color VARCHAR(20) DEFAULT '#27ae60',
    activa BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- ============================================
-- 5. TRANSACCION
-- ============================================
CREATE TABLE IF NOT EXISTS transaccion (
    id_transaccion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_categoria INT NOT NULL,
    id_cuenta INT NULL,
    id_fuente_ingreso INT NULL,
    monto DECIMAL(12,2) NOT NULL,
    tipo ENUM('ingreso','gasto') NOT NULL,
    descripcion VARCHAR(255) NULL,
    fecha DATE NOT NULL,
    metodo_pago VARCHAR(50) DEFAULT 'cuenta',
    recurrente BOOLEAN DEFAULT FALSE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE CASCADE,
    FOREIGN KEY (id_cuenta) REFERENCES cuenta(id_cuenta) ON DELETE SET NULL,
    FOREIGN KEY (id_fuente_ingreso) REFERENCES fuente_ingreso(id_fuente) ON DELETE SET NULL
);

-- ============================================
-- 6. PRESUPUESTO
-- ============================================
CREATE TABLE IF NOT EXISTS presupuesto (
    id_presupuesto INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    mes INT NOT NULL,
    anio INT NOT NULL,
    monto_total DECIMAL(12,2) NOT NULL DEFAULT 0,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    UNIQUE KEY unique_presupuesto (id_usuario, mes, anio)
);

-- ============================================
-- 7. DETALLE_PRESUPUESTO
-- ============================================
CREATE TABLE IF NOT EXISTS detalle_presupuesto (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_presupuesto INT NOT NULL,
    id_categoria INT NOT NULL,
    limite DECIMAL(12,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (id_presupuesto) REFERENCES presupuesto(id_presupuesto) ON DELETE CASCADE,
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE CASCADE,
    UNIQUE KEY unique_presupuesto_categoria (id_presupuesto, id_categoria)
);

-- ============================================
-- 8. META
-- ============================================
CREATE TABLE IF NOT EXISTS meta (
    id_meta INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    monto_objetivo DECIMAL(12,2) NOT NULL,
    monto_inicial DECIMAL(12,2) DEFAULT 0,
    monto_actual DECIMAL(12,2) DEFAULT 0,
    fecha_inicio DATE NOT NULL,
    fecha_limite DATE NOT NULL,
    estado ENUM('activa','completada','cancelada') DEFAULT 'activa',
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- ============================================
-- 9. APORTE_META
-- ============================================
CREATE TABLE IF NOT EXISTS aporte_meta (
    id_aporte INT AUTO_INCREMENT PRIMARY KEY,
    id_meta INT NOT NULL,
    id_usuario INT NOT NULL,
    id_cuenta INT NULL,
    monto DECIMAL(12,2) NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    nota VARCHAR(255) NULL,
    FOREIGN KEY (id_meta) REFERENCES meta(id_meta) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_cuenta) REFERENCES cuenta(id_cuenta) ON DELETE SET NULL
);

-- ============================================
-- 10. DEUDA
-- ============================================
CREATE TABLE IF NOT EXISTS deuda (
    id_deuda INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    acreedor VARCHAR(150) NOT NULL,
    monto_total DECIMAL(12,2) NOT NULL,
    monto_inicial DECIMAL(12,2) DEFAULT 0,
    monto_pagado DECIMAL(12,2) DEFAULT 0,
    tasa_interes DECIMAL(5,2) DEFAULT 0,
    fecha_inicio DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    pago_minimo DECIMAL(12,2) DEFAULT 0,
    estado ENUM('activa','pagada','vencida') DEFAULT 'activa',
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- ============================================
-- 11. PAGO_DEUDA
-- ============================================
CREATE TABLE IF NOT EXISTS pago_deuda (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_deuda INT NOT NULL,
    id_usuario INT NOT NULL,
    id_cuenta INT NULL,
    monto DECIMAL(12,2) NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    nota VARCHAR(255) NULL,
    FOREIGN KEY (id_deuda) REFERENCES deuda(id_deuda) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_cuenta) REFERENCES cuenta(id_cuenta) ON DELETE SET NULL
);

-- ============================================
-- 12. CONFIGURACION_FINANCIERA
-- ============================================
CREATE TABLE IF NOT EXISTS configuracion_financiera (
    id_config INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor DECIMAL(12,4) NOT NULL,
    descripcion VARCHAR(255) NULL,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- 13. LOGRO
-- ============================================
CREATE TABLE IF NOT EXISTS logro (
    id_logro INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    icono VARCHAR(10) NOT NULL,
    color VARCHAR(20) DEFAULT '#d4af37',
    categoria ENUM('inicio','ahorro','deuda','disciplina','maestria') DEFAULT 'inicio',
    puntos INT DEFAULT 10
);

-- ============================================
-- 14. USUARIO_LOGRO
-- ============================================
CREATE TABLE IF NOT EXISTS usuario_logro (
    id_usuario_logro INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_logro INT NOT NULL,
    fecha_desbloqueo DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_usuario_logro (id_usuario, id_logro),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_logro) REFERENCES logro(id_logro) ON DELETE CASCADE
);

-- ============================================
-- 15. CHAT_CONVERSACION
-- ============================================
CREATE TABLE IF NOT EXISTS chat_conversacion (
    id_chat INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    rol ENUM('user','bot') NOT NULL,
    mensaje TEXT NOT NULL,
    intencion VARCHAR(50) NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- ============================================
-- DATOS POR DEFECTO
-- ============================================

-- Configuración financiera (Colombia)
INSERT INTO configuracion_financiera (clave, valor, descripcion) VALUES
('tasa_usura_ea', 28.79, 'Tasa de usura efectiva anual vigente en Colombia'),
('tasa_interes_bancario_promedio', 15.50, 'Tasa promedio de créditos de consumo'),
('inflacion_anual', 5.20, 'IPC anual Colombia'),
('tasa_cdt_promedio', 12.00, 'Tasa promedio de CDT a 12 meses'),
('tasa_ahorros_promedio', 8.50, 'Tasa promedio cuenta de ahorros'),
('tasa_fondo_promedio', 14.00, 'Rendimiento promedio fondo de inversión'),
('retencion_fuente_cdt', 4.00, 'Retención en la fuente para rendimientos financieros (%)'),
('gravamen_movimientos', 0.40, 'Gravamen a movimientos financieros 4x1000 (%)')
ON DUPLICATE KEY UPDATE valor = VALUES(valor);

-- Logros por defecto
INSERT INTO logro (clave, nombre, descripcion, icono, color, categoria, puntos) VALUES
('bienvenido', 'Bienvenido a FinanIA', 'Completaste tu registro', '👋', '#3498db', 'inicio', 5),
('primera_cuenta', 'Primera cuenta', 'Creaste tu primera cuenta', '🏦', '#27ae60', 'inicio', 10),
('primera_transaccion', 'Primer movimiento', 'Registraste tu primera transacción', '💸', '#3498db', 'inicio', 10),
('primera_meta', 'Soñador', 'Creaste tu primera meta', '🎯', '#d4af37', 'ahorro', 10),
('primera_deuda', 'Cara a cara', 'Registraste tu primera deuda', '💳', '#e67e22', 'deuda', 10),
('10_transacciones', 'Constante', 'Registraste 10 transacciones', '📊', '#9b59b6', 'disciplina', 20),
('50_transacciones', 'Disciplinado', 'Registraste 50 transacciones', '📈', '#8e44ad', 'disciplina', 50),
('racha_7', 'Racha de fuego', '7 días seguidos registrando', '🔥', '#e74c3c', 'disciplina', 30),
('racha_30', 'Imparable', '30 días seguidos registrando', '⚡', '#e74c3c', 'disciplina', 100),
('meta_cumplida', 'Meta alcanzada', 'Completaste tu primera meta', '🏆', '#f39c12', 'ahorro', 50),
('deuda_pagada', 'Libre de deudas', 'Pagaste completamente una deuda', '🎉', '#27ae60', 'deuda', 50),
('fondo_50', 'Medio camino', 'Fondo de emergencia al 50%', '🛡️', '#16a085', 'ahorro', 30),
('fondo_completo', 'Blindado', 'Fondo de emergencia completo', '💎', '#16a085', 'ahorro', 100),
('salud_70', 'Finanzas sanas', 'Salud financiera mayor a 70', '🌟', '#d4af37', 'maestria', 50),
('salud_90', 'Maestro financiero', 'Salud financiera mayor a 90', '👑', '#d4af37', 'maestria', 150),
('usuario_premium', 'Premium', 'Actualizaste a Premium', '⭐', '#9b59b6', 'maestria', 25)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);