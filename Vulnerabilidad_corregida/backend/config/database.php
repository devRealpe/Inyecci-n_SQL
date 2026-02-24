<?php

// CONEXIÓN A POSTGRESQL - VERSIÓN SEGURA

// ══════════════════════════════════════════════════════════════
//  CONFIGURACIÓN DE CREDENCIALES (usar variables de entorno en producción)
// ══════════════════════════════════════════════════════════════

// ✅ MEJOR PRÁCTICA: Leer desde variables de entorno
// define('DB_HOST',     getenv('DB_HOST') ?: 'localhost');
// define('DB_PORT',     getenv('DB_PORT') ?: '5432');
// define('DB_NAME',     getenv('DB_NAME') ?: 'Universidad');
// define('DB_USER',     getenv('DB_USER') ?: 'app_user');
// define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');

// Para este ejemplo educativo, mantenemos valores estáticos:
define('DB_HOST',     'localhost');
define('DB_PORT',     '5432');
define('DB_NAME',     'Universidad');

// ══════════════════════════════════════════════════════════════
//  USUARIO CON PRIVILEGIOS MÍNIMOS
// ══════════════════════════════════════════════════════════════

// ❌ INSEGURO: usar 'postgres' (superusuario)
// define('DB_USER',     'postgres');
// define('DB_PASSWORD', 'admin');

// ✅ SEGURO: crear usuario con permisos limitados
define('DB_USER',     'app_universidad');
define('DB_PASSWORD', 'P@ssw0rd_S3cur3_2025!');

/*
  COMANDO PARA CREAR USUARIO SEGURO EN POSTGRESQL:
  
  -- Conectarse como postgres
  sudo -u postgres psql
  
  -- Crear usuario sin privilegios de superusuario
  CREATE USER app_universidad WITH PASSWORD 'P@ssw0rd_S3cur3_2025!';
  
  -- Otorgar SOLO permisos necesarios en la base de datos
  GRANT CONNECT ON DATABASE Universidad TO app_universidad;
  
  -- Conectarse a la base de datos
  \c Universidad
  
  -- Otorgar permisos sobre el esquema público
  GRANT USAGE ON SCHEMA public TO app_universidad;
  
  -- Otorgar SOLO SELECT, INSERT, UPDATE en tablas necesarias
  GRANT SELECT, INSERT, UPDATE ON TABLE usuarios TO app_universidad;
  GRANT SELECT ON TABLE estudiantes, cursos, notas TO app_universidad;
  
  -- Otorgar uso de secuencias (para IDs autoincrementales)
  GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO app_universidad;
  
  -- IMPORTANTE: NO dar permisos de DELETE, DROP, CREATE TABLE, ALTER
  -- Esto limita el daño en caso de SQL injection
*/

// ══════════════════════════════════════════════════════════════
//  FUNCIÓN DE CONEXIÓN SEGURA
// ══════════════════════════════════════════════════════════════

function getConnection()
{
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
            // ══════════════════════════════════════════════════════════════
            //  CONFIGURACIONES DE SEGURIDAD PDO
            // ══════════════════════════════════════════════════════════════

            // ✅ Modo de errores: lanzar excepciones (para manejo controlado)
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            // ✅ NO emular prepared statements (usar nativos de PostgreSQL)
            PDO::ATTR_EMULATE_PREPARES => false,

            // ✅ Modo de fetch por defecto
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            // ✅ Deshabilitar múltiples consultas en una sola ejecución
            PDO::ATTR_EMULATE_PREPARES => false,

            // ✅ Conexión persistente (opcional, mejora rendimiento)
            // PDO::ATTR_PERSISTENT => true,

            // ✅ Timeout de conexión
            PDO::ATTR_TIMEOUT => 5,
        ]);

        // ══════════════════════════════════════════════════════════════
        //  CONFIGURACIONES ADICIONALES DE POSTGRESQL
        // ══════════════════════════════════════════════════════════════

        // ✅ Establecer zona horaria
        $pdo->exec("SET TIME ZONE 'America/Lima'");

        // ✅ Establecer codificación UTF-8
        $pdo->exec("SET NAMES 'UTF8'");

        // ✅ Deshabilitar el notice de PostgreSQL
        $pdo->exec("SET client_min_messages TO WARNING");

        return $pdo;
    } catch (PDOException $e) {
        // ══════════════════════════════════════════════════════════════
        //  MANEJO SEGURO DE ERRORES DE CONEXIÓN
        // ══════════════════════════════════════════════════════════════

        // ❌ INSEGURO: mostrar detalles del error
        // die("Error de conexión: " . $e->getMessage());

        // ✅ SEGURO: Log interno + mensaje genérico
        error_log("Error crítico de conexión a BD: " . $e->getMessage());

        // Mostrar página de error genérica
        http_response_code(503);
        die("El servicio no está disponible temporalmente. Por favor intente más tarde.");
    }
}

// ══════════════════════════════════════════════════════════════
//  FUNCIONES AUXILIARES DE SEGURIDAD
// ══════════════════════════════════════════════════════════════

/**
 * Verificar salud de la conexión a BD
 */
function verificarConexion()
{
    try {
        $pdo = getConnection();
        $stmt = $pdo->query("SELECT 1");
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log("Verificación de conexión falló: " . $e->getMessage());
        return false;
    }
}

/**
 * Ejecutar query de forma segura con logging
 */
function ejecutarQuerySegura($query, $params = [], $tipo = 'SELECT')
{
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        // Log de auditoría (opcional)
        registrarQueryAuditoria($query, $params, $tipo, 'SUCCESS');

        return $stmt;
    } catch (PDOException $e) {
        // Log de error
        error_log("Error en query: " . $e->getMessage());
        registrarQueryAuditoria($query, $params, $tipo, 'ERROR', $e->getMessage());

        throw $e; // Re-lanzar para manejo superior
    }
}

/**
 * Registrar queries en tabla de auditoría
 */
function registrarQueryAuditoria($query, $params, $tipo, $estado, $error = null)
{
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO auditoria_queries (query_tipo, query_hash, estado, error, usuario, ip_address, fecha) 
             VALUES (?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            $tipo,
            md5($query), // Hash para no guardar la query completa
            $estado,
            $error,
            $_SESSION['username'] ?? 'anonymous',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (PDOException $e) {
        error_log("Error en auditoría de queries: " . $e->getMessage());
    }
}

// ══════════════════════════════════════════════════════════════
//  SCRIPTS SQL PARA CREAR TABLAS DE AUDITORÍA
// ══════════════════════════════════════════════════════════════

/*
  -- Tabla para auditoría de seguridad (logins, accesos)
  CREATE TABLE IF NOT EXISTS auditoria_seguridad (
      id SERIAL PRIMARY KEY,
      tipo_evento VARCHAR(50) NOT NULL,
      usuario VARCHAR(100),
      detalle TEXT,
      ip_address VARCHAR(45),
      user_agent TEXT,
      fecha TIMESTAMP NOT NULL DEFAULT NOW()
  );
  
  CREATE INDEX idx_auditoria_seg_tipo ON auditoria_seguridad(tipo_evento);
  CREATE INDEX idx_auditoria_seg_fecha ON auditoria_seguridad(fecha);
  CREATE INDEX idx_auditoria_seg_usuario ON auditoria_seguridad(usuario);
  
  -- Tabla para auditoría de queries
  CREATE TABLE IF NOT EXISTS auditoria_queries (
      id SERIAL PRIMARY KEY,
      query_tipo VARCHAR(20) NOT NULL,
      query_hash VARCHAR(32) NOT NULL,
      estado VARCHAR(20) NOT NULL,
      error TEXT,
      usuario VARCHAR(100),
      ip_address VARCHAR(45),
      fecha TIMESTAMP NOT NULL DEFAULT NOW()
  );
  
  CREATE INDEX idx_auditoria_queries_tipo ON auditoria_queries(query_tipo);
  CREATE INDEX idx_auditoria_queries_fecha ON auditoria_queries(fecha);
  CREATE INDEX idx_auditoria_queries_estado ON auditoria_queries(estado);
*/