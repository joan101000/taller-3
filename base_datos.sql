
-- Base de datos del Taller 3
-- Sistema de gestión de fallas en laboratorios

CREATE DATABASE IF NOT EXISTS gestion_fallas;

USE gestion_fallas;

-- Tabla para almacenar los reportes

CREATE TABLE IF NOT EXISTS reportes (
    id INT AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL,

    laboratorio VARCHAR(50) NOT NULL,

    equipo VARCHAR(30) NOT NULL,

    fecha_falla DATE NOT NULL,

    hora_falla TIME NOT NULL,

    tipo_falla VARCHAR(50) NOT NULL,

    otro_problema TEXT,

    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
