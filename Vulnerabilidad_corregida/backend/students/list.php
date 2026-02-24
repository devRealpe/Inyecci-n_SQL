<?php
// STUDENTS: list.php - VERSIÓN SEGURA CON PREPARED STATEMENTS

require_once __DIR__ . '/../config/database.php';

function listarEstudiantes($busqueda = '')
{
    $pdo = getConnection();

    // ══════════════════════════════════════════════════════════════
    //  VALIDACIÓN DE ENTRADA
    // ══════════════════════════════════════════════════════════════

    // 1. Sanitizar entrada
    $busqueda = trim($busqueda);

    // 2. Validar longitud máxima
    if (strlen($busqueda) > 100) {
        return [
            'data' => [],
            'query' => '',
            'error' => 'Búsqueda demasiado larga.'
        ];
    }

    // 3. Validar caracteres permitidos (solo letras, espacios y acentos)
    if ($busqueda !== '' && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $busqueda)) {
        return [
            'data' => [],
            'query' => '',
            'error' => 'Búsqueda contiene caracteres no permitidos.'
        ];
    }

    // ══════════════════════════════════════════════════════════════
    //  PREPARED STATEMENT - PROTECCIÓN CONTRA SQL INJECTION
    // ══════════════════════════════════════════════════════════════

    if ($busqueda !== '') {
        // ✅ CORRECTO: Usar ? como placeholder
        $query = "SELECT e.nombre, e.apellido, c.nombre_curso 
                  FROM notas n 
                  JOIN estudiantes e ON n.id_estudiante = e.id_estudiante 
                  JOIN cursos c ON n.id_curso = c.id_curso 
                  WHERE e.nombre ILIKE ?";

        // El patrón % debe estar en el parámetro, NO en la query
        $searchParam = "%{$busqueda}%";
        $params = [$searchParam];

        // Para mostrar en debug (educativo)
        $queryDisplay = $query . " [Param: '$searchParam']";
    } else {
        $query = "SELECT e.nombre, e.apellido, c.nombre_curso 
                  FROM notas n 
                  JOIN estudiantes e ON n.id_estudiante = e.id_estudiante 
                  JOIN cursos c ON n.id_curso = c.id_curso 
                  ORDER BY e.apellido";
        $params = [];
        $queryDisplay = $query;
    }

    $_SESSION['last_query'] = $queryDisplay;

    try {
        // ✅ PASO 1: Preparar la consulta
        $stmt = $pdo->prepare($query);

        // ✅ PASO 2: Ejecutar con parámetros
        $stmt->execute($params);

        // ✅ PASO 3: Obtener resultados
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $resultados,
            'query' => $queryDisplay,
            'error' => null
        ];
    } catch (PDOException $e) {
        // ══════════════════════════════════════════════════════════════
        //  MANEJO SEGURO DE ERRORES
        // ══════════════════════════════════════════════════════════════

        // ❌ NUNCA mostrar el error SQL real al usuario
        // return ['data' => [], 'query' => $query, 'error' => 'Error SQL: ' . $e->getMessage()];

        // ✅ CORRECTO: Mensaje genérico + log interno
        error_log("Error en búsqueda de estudiantes: " . $e->getMessage());

        return [
            'data' => [],
            'query' => $queryDisplay,
            'error' => 'Error al realizar la búsqueda. Por favor intente nuevamente.'
        ];
    }
}

/**
 * VERSIÓN ALTERNATIVA: Búsqueda múltiple (nombre O apellido)
 * Ejemplo de consulta más compleja con múltiples parámetros
 */
function listarEstudiantesAvanzado($busqueda = '', $curso_id = null, $ordenar = 'apellido')
{
    $pdo = getConnection();

    // Validación
    $busqueda = trim($busqueda);
    if (strlen($busqueda) > 100) {
        return ['data' => [], 'query' => '', 'error' => 'Búsqueda inválida.'];
    }

    // Validar campo de ordenamiento (whitelist)
    $camposPermitidos = ['nombre', 'apellido', 'nombre_curso'];
    if (!in_array($ordenar, $camposPermitidos)) {
        $ordenar = 'apellido'; // valor por defecto seguro
    }

    // Construir query dinámica de forma segura
    $query = "SELECT e.nombre, e.apellido, c.nombre_curso 
              FROM notas n 
              JOIN estudiantes e ON n.id_estudiante = e.id_estudiante 
              JOIN cursos c ON n.id_curso = c.id_curso 
              WHERE 1=1";

    $params = [];

    // Agregar condición de búsqueda si existe
    if ($busqueda !== '') {
        $query .= " AND (e.nombre ILIKE ? OR e.apellido ILIKE ?)";
        $searchParam = "%{$busqueda}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    // Agregar filtro de curso si existe
    if ($curso_id !== null && is_numeric($curso_id)) {
        $query .= " AND c.id_curso = ?";
        $params[] = (int)$curso_id;
    }

    // Ordenamiento seguro (campo ya validado contra whitelist)
    $query .= " ORDER BY e." . $ordenar;

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return ['data' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'query' => $query, 'error' => null];
    } catch (PDOException $e) {
        error_log("Error en búsqueda avanzada: " . $e->getMessage());
        return ['data' => [], 'query' => $query, 'error' => 'Error en la búsqueda.'];
    }
}
