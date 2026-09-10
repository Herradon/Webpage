CREATE DATABASE IF NOT EXISTS chatbot_web
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;


USE chatbot_web;


CREATE TABLE contactos (

    id INT AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL,

    telefono VARCHAR(30) NOT NULL,

    email VARCHAR(150) NOT NULL,

    mensaje TEXT NOT NULL,

    fecha DATETIME DEFAULT CURRENT_TIMESTAMP

);


CREATE TABLE conversaciones (

    id INT AUTO_INCREMENT PRIMARY KEY,

    usuario TEXT NOT NULL,

    respuesta TEXT NOT NULL,

    fecha DATETIME DEFAULT CURRENT_TIMESTAMP

);

CREATE TABLE reuniones (
    
    id INT AUTO_INCREMENT PRIMARY KEY,

    conversacion_id VARCHAR(50) NOT NULL,

    nombre VARCHAR(255) NOT NULL,

    email VARCHAR(255) NOT NULL,

    especialista VARCHAR(255) NOT NULL,

    fecha DATE NOT NULL,

    hora TIME NOT NULL,

    duracion INT DEFAULT 60,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);