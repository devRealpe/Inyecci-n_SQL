<?php
// STUDENTS: list.php

require_once __DIR__ . '/../config/database.php';

function listarEstudiantes($busqueda = '')
{
    $pdo = getConnection();

    if ($busqueda !== '') {

        $query = "SELECT e.nombre, e.apellido, c.nombre_curso
                  FROM notas n
                  JOIN estudiantes e ON n.id_estudiante = e.id_estudiante
                  JOIN cursos c      ON n.id_curso      = c.id_curso
                  WHERE e.nombre ILIKE '%$busqueda%'
                  OR e.apellido   ILIKE '%$busqueda%'
                  ORDER BY e.apellido";
    } else {
        $query = "SELECT e.nombre, e.apellido, c.nombre_curso
                  FROM notas n
                  JOIN estudiantes e ON n.id_estudiante = e.id_estudiante
                  JOIN cursos c      ON n.id_curso      = c.id_curso
                  ORDER BY e.apellido";
    }

    // Guardamos la query en sesión para mostrarla
    $_SESSION['last_query'] = $query;

    try {
        $stmt = $pdo->query($query);
        return ['data' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'query' => $query, 'error' => null];
    } catch (PDOException $e) {
        return ['data' => [], 'query' => $query, 'error' => 'Error SQL: ' . $e->getMessage()];
    }
}
