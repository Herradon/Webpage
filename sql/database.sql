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