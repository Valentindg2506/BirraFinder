<?php
session_start();
require_once '/config/db.php';

if (!isset($_SESSION['admin_user'])) {
    header("Location: index.php");
    exit;
}

// Fetch Stats
$total_users = $conexion->query("SELECT count(*) as c FROM usuarios")->fetch_assoc()['c'];
$total_bars = $conexion->query("SELECT count(*) as c FROM bares")->fetch_assoc()['c'];

// Fetch Users
$users = $conexion->query("SELECT * FROM usuarios");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-main { 
            padding: 2rem; 
            overflow-y: auto; 
            width: 100%;
        }
        .stats {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card { 
            background: white; 
            padding: 1.5rem; 
            border-radius: 16px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 2px solid #e5e7eb;
            min-width: 200px;
            text-align: center;
        }
        .stat-card h3 { margin: 0; color: #6b7280; font-size: 1rem; }
        .stat-card p { font-size: 2.5rem; font-weight: 800; color: var(--primary-color); margin: 0.5rem 0 0; }

        /* Table Styles */
        table { 
            width: 100%; 
            border-collapse: separate; 
            border-spacing: 0; 
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 2px solid #e5e7eb;
        }
        th { 
            background: #f9fafb; 
            padding: 1rem; 
            text-align: left; 
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        td { 
            padding: 1rem; 
            border-bottom: 1px solid #f3f4f6; 
            color: #4b5563;
        }
        tr:last-child td { border-bottom: none; }
        
        .btn-icon {
            background: #fee2e2;
            color: #ef4444;
            border: 1px solid #fca5a5;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.2s;
        }
        .btn-icon:hover { background: #fca5a5; color: white; }
    </style>
</head>
<body class="dashboard-body">
    <aside class="sidebar">
        <div class="logo"><h2>Admin</h2></div>
        <nav>
            <ul>
                <li><a href="../../app/controllers/AdminController.php?action=logout">Salir</a></li>
            </ul>
        </nav>
    </aside>

    <main class="admin-main">
        <h1>Panel de Administración</h1>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Usuarios</h3>
                <p><?php echo $total_users; ?></p>
            </div>
            <div class="stat-card">
                <h3>Bares Totales</h3>
                <p><?php echo $total_bars; ?></p>
            </div>
        </div>

        <h2>Usuarios Registrados</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($u = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td>
                        <form action="../app/controllers/AdminController.php" method="POST" onsubmit="return confirm('Eliminar usuario?');">
                            <input type="hidden" name="action" value="delete_user">
                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                            <button type="submit" class="btn-icon" style="color:red;">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
