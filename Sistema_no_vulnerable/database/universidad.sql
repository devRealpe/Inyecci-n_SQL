
-- ============================================
-- CREAR BASE DE DATOS
-- ============================================
CREATE DATABASE Universidad;

-- Conectarse a la base de datos
-- \c Universidad;

-- ============================================
-- TABLA: estudiantes
-- ============================================
CREATE TABLE estudiantes (
    id_estudiante SERIAL PRIMARY KEY,
    nombre        VARCHAR(100) NOT NULL,
    apellido      VARCHAR(100) NOT NULL,
    email         VARCHAR(150) UNIQUE NOT NULL,
    fecha_nacimiento DATE,
    fecha_registro   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLA: cursos
-- ============================================
CREATE TABLE cursos (
    id_curso     SERIAL PRIMARY KEY,
    nombre_curso VARCHAR(150) NOT NULL,
    descripcion  TEXT,
    creditos     INTEGER NOT NULL CHECK (creditos > 0)
);

-- ============================================
-- TABLA: notas
-- ============================================
CREATE TABLE notas (
    id_nota      SERIAL PRIMARY KEY,
    id_estudiante INTEGER NOT NULL,
    id_curso      INTEGER NOT NULL,
    nota          NUMERIC(4,2) CHECK (nota >= 0 AND nota <= 5),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_estudiante
        FOREIGN KEY (id_estudiante)
        REFERENCES estudiantes(id_estudiante)
        ON DELETE CASCADE,
    CONSTRAINT fk_curso
        FOREIGN KEY (id_curso)
        REFERENCES cursos(id_curso)
        ON DELETE CASCADE,
    CONSTRAINT unica_nota
        UNIQUE (id_estudiante, id_curso)
);

-- ============================================
-- TABLA: usuarios
-- (Contrasenas en texto plano — VULNERABLE a proposito)
-- ============================================
CREATE TABLE usuarios (
    id_usuario SERIAL PRIMARY KEY,
    username   VARCHAR(80) UNIQUE NOT NULL,
    password   VARCHAR(80) NOT NULL,
    rol        VARCHAR(20) DEFAULT 'admin',
    creado_en  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- DATOS DE PRUEBA: estudiantes
-- ============================================
INSERT INTO estudiantes (nombre, apellido, email, fecha_nacimiento)
VALUES 
    ('Juan',   'Pérez',    'juan@email.com',   '2002-05-10'),
    ('María',  'Gómez',    'maria@email.com',  '2001-08-15'),
    ('Carlos', 'Ramírez',  'carlos@email.com', '2003-01-22'),
    ('Laura',  'Torres',   'laura@email.com',  '2002-11-05'),
    ('Andrés', 'Morales',  'andres@email.com', '2001-07-30');

-- ============================================
-- DATOS DE PRUEBA: cursos
-- ============================================
INSERT INTO cursos (nombre_curso, descripcion, creditos)
VALUES 
    ('Base de Datos',    'Curso de PostgreSQL',                        3),
    ('Programación',     'Curso de fundamentos de programación',       4),
    ('Redes',            'Curso de redes y comunicaciones',            3),
    ('Seguridad',        'Seguridad en sistemas informáticos',         3);

-- ============================================
-- DATOS DE PRUEBA: notas
-- ============================================
INSERT INTO notas (id_estudiante, id_curso, nota)
VALUES
    (1, 1, 4.5),
    (1, 2, 3.8),
    (1, 3, 4.0),
    (2, 1, 4.2),
    (2, 2, 3.5),
    (3, 1, 3.9),
    (3, 4, 4.7),
    (4, 2, 4.1),
    (4, 3, 3.6),
    (5, 4, 4.8);

-- ============================================
-- DATOS DE PRUEBA: usuarios
-- (Contrasenas en texto plano — VULNERABLE a proposito)
-- ============================================
INSERT INTO usuarios (username, password, rol)
VALUES
    ('admin',    'admin123',  'admin'),
    ('profesor', 'prof2024',  'admin'),
    ('invitado', 'invitado1', 'viewer');

-- ============================================
-- CONSULTA DE PRUEBA (JOIN)
-- ============================================
SELECT 
    e.nombre,
    e.apellido,
    c.nombre_curso,
    n.nota
FROM notas n
JOIN estudiantes e ON n.id_estudiante = e.id_estudiante
JOIN cursos c      ON n.id_curso      = c.id_curso
ORDER BY e.apellido, c.nombre_curso;