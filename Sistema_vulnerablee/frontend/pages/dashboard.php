<?php
session_start();

if (empty($_SESSION['logueado'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../backend/students/list.php';

$busqueda    = $_GET['busqueda'] ?? '';
$resultado   = listarEstudiantes($busqueda);
$estudiantes = $resultado['data'];
$queryActual = $resultado['query'];
$sqlError    = $resultado['error'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Sistema Universidad</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        body {
            display: block;
            align-items: unset;
            justify-content: unset;
            padding: 0;
        }

        .navbar {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-weight: 700;
            font-size: 1rem;
            color: var(--text-primary);
        }

        .navbar-brand span {
            font-size: 1.3rem;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-pill {
            background: var(--accent-soft);
            border: 1px solid rgba(108, 99, 255, 0.3);
            border-radius: 999px;
            padding: 0.3rem 0.9rem;
            font-size: 0.82rem;
            color: var(--accent);
            font-weight: 500;
        }

        .btn-logout {
            padding: 0.35rem 0.9rem;
            background: var(--danger-soft);
            border: 1px solid var(--danger);
            border-radius: var(--radius-sm);
            color: var(--danger);
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-logout:hover {
            background: rgba(255, 77, 77, 0.22);
        }

        .main {
            max-width: 1100px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .page-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }

        .page-subtitle {
            font-size: 0.88rem;
            color: var(--text-secondary);
            margin-bottom: 1.8rem;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.8rem;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.2rem;
        }

        .stat-card .label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--text-secondary);
            margin-bottom: 0.4rem;
        }

        .stat-card .value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--accent);
        }

        .search-bar {
            display: flex;
            gap: 0.8rem;
            margin-bottom: 1.2rem;
        }

        .search-bar input {
            flex: 1;
            padding: 0.7rem 1rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: 0.92rem;
            font-family: inherit;
            outline: none;
            transition: border 0.2s, box-shadow 0.2s;
        }

        .search-bar input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        .search-bar button {
            padding: 0.7rem 1.4rem;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.2s;
        }

        .search-bar button:hover {
            background: var(--accent-hover);
        }

        .debug-section {
            margin-top: 2rem;
        }

        .debug-section h3 {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-secondary);
        }

        .empty-state .icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .vuln-hint {
            background: rgba(255, 209, 102, 0.08);
            border: 1px solid rgba(255, 209, 102, 0.3);
            border-radius: var(--radius-sm);
            padding: 0.7rem 1rem;
            font-size: 0.8rem;
            color: #ffd166;
            margin-bottom: 1rem;
        }

        .vuln-hint code {
            background: rgba(255, 255, 255, 0.08);
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.82rem;
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="navbar-brand"><span>🎓</span> Sistema Universidad</div>
        <div class="navbar-right">
            <span class="user-pill">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="logout.php" class="btn-logout">Cerrar sesión</a>
        </div>
    </nav>

    <main class="main">
        <h1 class="page-title">Dashboard Académico</h1>
        <p class="page-subtitle">Listado de estudiantes, cursos y notas registradas.</p>

        <div class="stats-row">
            <div class="stat-card">
                <div class="label">Registros encontrados</div>
                <div class="value"><?= count($estudiantes) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Usuario activo</div>
                <div class="value" style="font-size:1rem;padding-top:0.4rem;">
                    <span class="badge badge-accent"><?= htmlspecialchars($_SESSION['rol']) ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="label">Estado BD</div>
                <div class="value" style="font-size:1rem;padding-top:0.4rem;">
                    <?php if ($sqlError): ?>
                        <span class="badge badge-danger">Error SQL</span>
                    <?php else: ?>
                        <span class="badge badge-success">OK</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <form method="GET" action="">
            <div class="search-bar">
                <input type="text" name="busqueda"
                    placeholder="Buscar por nombre... (campo vulnerable — pg_ nativo)"
                    value="<?= htmlspecialchars($busqueda) ?>"
                    autocomplete="off">
                <button type="submit">Buscar</button>
            </div>
        </form>

        <?php if ($sqlError): ?>
            <div class="alert alert-danger"><?= $sqlError ?></div>
        <?php endif; ?>

        <?php if (!empty($estudiantes)): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Curso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $i => $est): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($est['nombre']       ?? $est[0] ?? '—') ?></td>
                                <td><?= htmlspecialchars($est['apellido']     ?? $est[1] ?? '—') ?></td>
                                <td>
                                    <span class="badge badge-accent">
                                        <?= htmlspecialchars($est['nombre_curso'] ?? $est[2] ?? '—') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">🔍</div>
                <p>No se encontraron resultados.</p>
            </div>
        <?php endif; ?>

        <div class="debug-section">
            <h3>🔍 Query ejecutada en base de datos</h3>
            <div class="query-box"><?= htmlspecialchars($queryActual) ?></div>
        </div>
    </main>

</body>

</html>