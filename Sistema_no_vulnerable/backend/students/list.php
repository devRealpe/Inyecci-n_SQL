<?php
// STUDENTS: list.php

require_once __DIR__ . '/../config/database.php';

function listarEstudiantes($busqueda = '')
{
    $pdo = getConnection();

    if ($busqueda !== '') {
        // PREPARED STATEMENT con parámetro con comodines ILIKE
        // El "?" recibe el valor con los % incluidos — la BD lo trata como texto, no como código SQL
        $query = "SELECT e.nombre, e.apellido, c.nombre_curso 
                  FROM notas n 
                  JOIN estudiantes e ON n.id_estudiante = e.id_estudiante 
                  JOIN cursos c ON n.id_curso = c.id_curso 
                  WHERE e.nombre ILIKE ?";

        // Se agregan los % al valor antes de pasarlo como parámetro
        $parametro = '%' . $busqueda . '%';

        $_SESSION['last_query'] = str_replace('?', "'[búsqueda protegida]'", $query);

        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute([$parametro]);   // El dato viaja separado del SQL
            return ['data' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'query' => $_SESSION['last_query'], 'error' => null];
        } catch (PDOException $e) {
            return ['data' => [], 'query' => $_SESSION['last_query'], 'error' => 'Error de sistema. Intenta de nuevo.'];
        }
    } else {
        $query = "SELECT e.nombre, e.apellido, c.nombre_curso 
                  FROM notas n 
                  JOIN estudiantes e ON n.id_estudiante = e.id_estudiante 
                  JOIN cursos c ON n.id_curso = c.id_curso 
                  ORDER BY e.apellido";

        $_SESSION['last_query'] = $query;

        try {
            $stmt = $pdo->query($query);
            return ['data' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'query' => $query, 'error' => null];
        } catch (PDOException $e) {
            return ['data' => [], 'query' => $query, 'error' => 'Error de sistema. Intenta de nuevo.'];
        }
    }
}
