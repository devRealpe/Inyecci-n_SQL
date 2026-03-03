<?php
// STUDENTS: list.php — VULNERABLE A SQL INJECTION (pg_ nativo)
// pg_query() permite múltiples sentencias separadas por ;

require_once __DIR__ . '/../config/database.php';

function listarEstudiantes($busqueda = '')
{
    $conn = getConnection();

    if ($busqueda !== '') {
        // VULNERABLE: entrada del usuario sin sanitizar
        $query = "SELECT e.nombre, e.apellido, c.nombre_curso 
                  FROM notas n 
                  JOIN estudiantes e ON n.id_estudiante = e.id_estudiante 
                  JOIN cursos c      ON n.id_curso      = c.id_curso 
                  WHERE e.nombre ILIKE '%$busqueda%'";
    } else {
        $query = "SELECT e.nombre, e.apellido, c.nombre_curso 
                  FROM notas n 
                  JOIN estudiantes e ON n.id_estudiante = e.id_estudiante 
                  JOIN cursos c      ON n.id_curso      = c.id_curso 
                  ORDER BY e.apellido";
    }

    $_SESSION['last_query'] = $query;

    // pg_query permite múltiples sentencias con ;
    // Ejemplo de inyección que ahora SÍ funciona:
    // %' ; DELETE FROM usuarios WHERE id_usuario=3 ; --
    $result = @pg_query($conn, $query);

    if ($result === false) {
        pg_close($conn);
        return [
            'data'  => [],
            'query' => $query,
            'error' => 'Error SQL: ' . pg_last_error($conn)
        ];
    }

    $rows = pg_fetch_all($result) ?: [];
    pg_free_result($result);
    pg_close($conn);

    return [
        'data'  => $rows,
        'query' => $query,
        'error' => null
    ];
}
