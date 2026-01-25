<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// --- ALERT SISTEM ---
if(isset($_GET['profile_success'])) {
    $msg = htmlspecialchars($_GET['profile_success']);
    echo "<script>alert('✅ $msg'); window.history.replaceState(null, null, window.location.pathname);</script>";
}
if(isset($_GET['profile_error'])) {
    $msg = htmlspecialchars($_GET['profile_error']);
    echo "<script>alert('❌ $msg'); window.history.replaceState(null, null, window.location.pathname);</script>";
}

// Fetch bars
$sql = "SELECT * FROM bares WHERE usuario_id = ? ORDER BY created_at DESC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$bares = [];
while ($row = $result->fetch_assoc()) $bares[] = $row;
$stmt->close();

// Stats Logic (Personal)
$total_bars = count($bares);
$visitados = count(array_filter($bares, fn($b) => $b['estado'] === 'visitado'));
$pendientes = $total_bars - $visitados;

// --- GLOBAL STATS LOGIC (Community) ---
// 1. Top 10 Visitados (Agrupado por nombre)
$sql_top_visited = "SELECT nombre, COUNT(*) as visitas, AVG(puntuacion) as rating 
                    FROM bares 
                    WHERE estado = 'visitado' 
                    GROUP BY nombre 
                    ORDER BY visitas DESC 
                    LIMIT 10";
$res_visited = $conexion->query($sql_top_visited);
$top_visited = [];
if($res_visited) while($row = $res_visited->fetch_assoc()) $top_visited[] = $row;

// 2. Top 10 Mejor Calificados (Minimo 1 voto)
$sql_top_rated = "SELECT nombre, AVG(puntuacion) as rating, COUNT(*) as votos
                  FROM bares 
                  WHERE puntuacion > 0
                  GROUP BY nombre 
                  HAVING votos >= 1
                  ORDER BY rating DESC, votos DESC
                  LIMIT 10";
$res_rated = $conexion->query($sql_top_rated);
$top_rated = [];
if($res_rated) while($row = $res_rated->fetch_assoc()) $top_rated[] = $row;

// 3. Recomendados (Aleatorios de 4 o 5 estrellas)
$sql_recommended = "SELECT nombre, puntuacion, comentario, usuario_id, (SELECT nombre FROM usuarios WHERE id = bares.usuario_id) as usuario
                    FROM bares 
                    WHERE puntuacion >= 4 
                    ORDER BY RAND() 
                    LIMIT 5";
$res_rec = $conexion->query($sql_recommended);
$recommended = [];
if($res_rec) while($row = $res_rec->fetch_assoc()) $recommended[] = $row;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BirraFinder - ¡Explora!</title>
    
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#f59e0b">
    <link rel="apple-touch-icon" href="img/icon-192.png">

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>                                                                                                                             
<body class="dashboard-body">                                       
    
    <aside class="sidebar">
        <div class="logo">
            <h2><i class="fa-solid fa-beer-mug-empty"></i> BirraFinder</h2>
        </div>
        <nav>
            <ul>
                <li id="nav-home" class="active" onclick="switchView('home')"><a href="#"><i class="fa-solid fa-house"></i> Inicio</a></li>
                <li id="nav-map" onclick="switchView('map')"><a href="#"><i class="fa-solid fa-map-location-dot"></i> Mapa</a></li>
                <li id="nav-list" onclick="switchView('list')"><a href="#"><i class="fa-solid fa-beer-mug-empty"></i> Mis Bares</a></li>
                <li id="nav-nearby" onclick="switchView('nearby')"><a href="#"><i class="fa-solid fa-compass"></i> Explorar Cerca</a></li>
                <li onclick="openProfile()"><a href="#"><i class="fa-solid fa-user"></i> Perfil</a></li>
                <li><a href="index.php?logout=1"><i class="fa-solid fa-right-from-bracket"></i> Salir</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <div class="header-greeting">
                <h1>Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?>! 🍻</h1>
                <p>¡Sigue descubriendo!</p>
            </div>
            
            <div class="hero-stats">
                <div class="hero-stat-item">
                    <div class="hero-icon" style="background: #fff7ed; color: #d97706;">
                        <i class="fa-solid fa-beer-mug-empty"></i>
                    </div>
                    <div>
                        <span class="hero-value"><?php echo $total_bars; ?></span>
                        <span class="hero-label">Total</span>
                    </div>
                </div>
                <div class="hero-stat-item">
                    <div class="hero-icon" style="background: #ecfdf5; color: #059669;">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <span class="hero-value"><?php echo $visitados; ?></span>
                        <span class="hero-label">Visitados</span>
                    </div>
                </div>
                <div class="hero-stat-item">
                    <div class="hero-icon" style="background: #fefce8; color: #ca8a04;">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <span class="hero-value"><?php echo $pendientes; ?></span>
                        <span class="hero-label">Pendientes</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Floating Action Button -->
        <button class="fab-btn" onclick="openModal()" title="Añadir Nuevo Bar">
            <i class="fa-solid fa-plus"></i>
        </button>

        <!-- Stats moved inside wrapper -->

        <div id="wrapper-content">
            <!-- Toggle removed -->

            <!-- VISTA INICIO (GLOBAL) -->
            <div id="home-view" class="view-section active">
                
                <div class="stats-grid-global" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    
                    <!-- TOP VISITADOS -->
                    <div class="global-card">
                        <h3 style="color: #d97706; border-bottom: 2px solid #ddd; padding-bottom:10px;"><i class="fa-solid fa-fire"></i> Top 10 Visitados</h3>
                        <table style="width:100%; border-collapse: collapse; margin-top:10px;">
                            <thead>
                                <tr style="background:#f3f4f6; text-align:left;">
                                    <th style="padding:8px;">Bar</th>
                                    <th style="padding:8px;">Visitas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($top_visited as $i => $b): ?>
                                <tr style="border-bottom:1px solid #eee;">
                                    <td style="padding:8px;">
                                        <span style="font-weight:bold; color:#d97706;">#<?php echo $i+1; ?></span> 
                                        <?php echo htmlspecialchars($b['nombre']); ?>
                                    </td>
                                    <td style="padding:8px; font-weight:bold;"><?php echo $b['visitas']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- TOP MEJOR VALORADOS -->
                    <div class="global-card">
                        <h3 style="color: #10b981; border-bottom: 2px solid #ddd; padding-bottom:10px;"><i class="fa-solid fa-star"></i> Top 10 Calidad</h3>
                        <table style="width:100%; border-collapse: collapse; margin-top:10px;">
                            <thead>
                                <tr style="background:#f3f4f6; text-align:left;">
                                    <th style="padding:8px;">Bar</th>
                                    <th style="padding:8px;">Nota</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($top_rated as $i => $b): ?>
                                <tr style="border-bottom:1px solid #eee;">
                                    <td style="padding:8px;">
                                        <span style="font-weight:bold; color:#10b981;">#<?php echo $i+1; ?></span> 
                                        <?php echo htmlspecialchars($b['nombre']); ?>
                                    </td>
                                    <td style="padding:8px;">
                                        <?php echo number_format($b['rating'], 1); ?> <i class="fa-solid fa-star" style="color:gold; font-size:0.8rem;"></i>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- RECOMENDADOS -->
                <div class="global-card" style="margin-top: 20px;">
                     <h3 style="color: #8b5cf6; border-bottom: 2px solid #ddd; padding-bottom:10px;"><i class="fa-solid fa-thumbs-up"></i> Recomendaciones de la Comunidad</h3>
                     <div style="display: flex; gap: 15px; overflow-x: auto; padding: 10px 0;">
                        <?php foreach($recommended as $rec): ?>
                            <div class="recommendation-card" style="min-width: 250px; background: #fafafa; padding: 15px; border-radius: 10px; border: 1px solid #eee;">
                                <h4 style="margin:0;"><?php echo htmlspecialchars($rec['nombre']); ?></h4>
                                <p style="color:gold; margin: 5px 0;">
                                    <?php echo str_repeat('<i class="fa-solid fa-star"></i>', floor($rec['puntuacion'])); ?>
                                </p>
                                <p style="font-style: italic; font-size: 0.9rem; color: #555;">"<?php echo htmlspecialchars($rec['comentario']); ?>"</p>
                                <small style="display:block; margin-top:10px; color:#999;">Recomendado por: <b><?php echo htmlspecialchars($rec['usuario']); ?></b></small>
                            </div>
                        <?php endforeach; ?>
                     </div>
                </div>

            </div>

            <div id="map-view" class="view-section" style="display: none;">
                <div class="search-map-container" style="display:flex; gap:10px; margin-bottom:10px;">
                    <input type="text" id="map-search" placeholder="📍 Buscar por ciudad o código postal..." style="flex:1; padding:0.8rem; border-radius:12px; border:2px solid #e5e7eb;">
                    <button class="btn-primary" onclick="searchLocation()" style="width:auto;"><i class="fa-solid fa-search"></i> Buscar</button>
                    <button class="btn-primary" onclick="useCurrentLocation()" style="width:auto; background:#10b981;"><i class="fa-solid fa-location-crosshairs"></i> Mi Ubicación</button>
                </div>
                <div id="map" style="width: 100%; height: 600px; border-radius: 16px; z-index: 1;"></div>
            </div>

            <div id="list-view" class="view-section" style="display: none;">
                <?php 
                    $pendientes_list = array_filter($bares, fn($b) => $b['estado'] === 'pendiente');
                    $visitados_list = array_filter($bares, fn($b) => $b['estado'] === 'visitado');
                ?>

                <div class="bars-section">
                    <h2 style="color: #d97706; border-bottom: 2px solid #d97706; padding-bottom: 10px; margin-bottom: 20px;">
                        📅 Por Visitar (<?php echo count($pendientes_list); ?>)
                    </h2>
                    <div class="bars-grid">
                        <?php if (empty($pendientes_list)): ?>
                            <p style="color: #6b7280; font-style: italic;">No tienes bares pendientes. ¡Añade uno desde el mapa!</p>
                        <?php else: ?>
                            <?php foreach ($pendientes_list as $bar): ?>
                                <?php include 'layout_bar_card.php'; ?> 
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bars-section" style="margin-top: 40px;">
                    <h2 style="color: #10b981; border-bottom: 2px solid #10b981; padding-bottom: 10px; margin-bottom: 20px;">
                        ✅ Visitados (<?php echo count($visitados_list); ?>)
                    </h2>
                    <div class="bars-grid">
                        <?php if (empty($visitados_list)): ?>
                            <p style="color: #6b7280; font-style: italic;">Aún no has visitado ningún bar. ¿A qué esperas?</p>
                        <?php else: ?>
                            <?php foreach ($visitados_list as $bar): ?>
                                <?php include 'layout_bar_card.php'; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div id="nearby-view" class="view-section" style="display: none;">
                <h3 style="margin-bottom:1rem; color:#6b7280;">Bares encontrados cerca de ti</h3>
                <div id="nearby-loading" style="display:none; text-align:center;"><i class="fa-solid fa-spinner fa-spin"></i> Buscando bares...</div>
                <div id="nearby-list-grid" class="bars-grid">
                    </div>
            </div>
            <!-- Stats View Removed (Moved to Header) -->
        </div>
    </main>

    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h2 id="modalTitle">Nuevo Bar</h2>
            <form id="barForm" action="app/controllers/BarsController.php" method="POST">
                <input type="hidden" name="accion" id="formAccion" value="agregar">
                <input type="hidden" name="id" id="barId">
                <input type="hidden" name="lat" id="lat">
                <input type="hidden" name="lng" id="lng">
                
                <div class="input-group">
                    <label>Nombre del Bar</label>
                    <input type="text" name="nombre" id="nombre" required placeholder="Ej: Cervecería El Paso">
                </div>

                <div class="input-group" style="margin-top: 1rem;">
                    <label>Estado</label>
                    <select name="estado" id="estado">
                        <option value="pendiente">📅 Pendiente (Quiero ir)</option>
                        <option value="visitado">✅ Visitado (Ya fui)</option>
                    </select>
                </div>

                <div class="input-group" style="margin-top: 1rem;">
                    <label>Dirección</label>
                    <input type="text" name="direccion" id="direccion" placeholder="Calle...">
                </div>

                <div class="input-group" style="margin-top: 1rem;">
                    <label>Puntuación</label>
                    <select name="puntuacion" id="puntuacion">
                        <option value="5">⭐⭐⭐⭐⭐ Excelente</option>
                        <option value="4">⭐⭐⭐⭐ Muy bueno</option>
                        <option value="3">⭐⭐⭐ Regular</option>
                        <option value="2">⭐⭐ Malo</option>
                        <option value="1">⭐ Terrible</option>
                    </select>
                </div>

                <div class="input-group" style="margin-top: 1rem;">
                    <label>Comentario</label>
                    <textarea name="comentario" id="comentario" rows="3" placeholder="¿Qué tal las tapas?"></textarea>
                </div>

                <button type="submit" class="btn-primary" style="margin-top: 2rem; width:100%;">Guardar Bar</button>
            </form>
        </div>
    </div>

    <div id="profileModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close" onclick="closeModal('profileModal')">&times;</span>
            <div style="text-align:center; margin-bottom:20px;">
                <h2 style="margin:0;">Mi Perfil</h2>
                <div style="width: 80px; height: 80px; background: #f3f4f6; border-radius: 50%; margin: 10px auto; display:flex; align-items:center; justify-content:center; font-size:2rem; color:#d97706;">
                    <i class="fa-solid fa-user"></i>
                </div>
                <h3 style="margin:0; font-family:'Outfit', sans-serif;"><?php echo htmlspecialchars($_SESSION['usuario']); ?></h3>
                <p style="color:#6b7280; font-size:0.9rem;"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
            </div>

            <!-- Profile Tabs -->
            <div class="profile-tabs" style="display:flex; gap:10px; border-bottom:2px solid #eee; margin-bottom:20px;">
                <button class="tab-btn active" onclick="switchProfileTab('data')" style="flex:1; background:none; border:none; padding:10px; cursor:pointer; font-weight:bold; color:var(--primary-color); border-bottom:3px solid var(--primary-color);">Datos Personales</button>
                <button class="tab-btn" onclick="switchProfileTab('security')" style="flex:1; background:none; border:none; padding:10px; cursor:pointer; font-weight:bold; color:#9ca3af; border-bottom:3px solid transparent;">Seguridad</button>
            </div>

            <!-- TAB: Data -->
            <div id="tab-data" class="profile-section">
                <form action="app/controllers/ProfileController.php" method="POST">
                    <input type="hidden" name="accion" value="update_profile">
                    
                    <div class="input-group">
                        <label>Nombre de Usuario</label>
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($_SESSION['usuario']); ?>" required>
                    </div>

                    <div class="input-group" style="margin-top:10px;">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                    </div>

                    <button type="submit" class="btn-primary" style="width:100%; margin-top:20px;">Actualizar Datos</button>
                </form>
            </div>

            <!-- TAB: Security -->
            <div id="tab-security" class="profile-section" style="display:none;">
                <form action="app/controllers/ProfileController.php" method="POST">
                    <input type="hidden" name="accion" value="change_password">
                    
                    <div class="input-group">
                        <label>Contraseña Actual</label>
                        <input type="password" name="current_password" required placeholder="••••••••">
                    </div>

                    <div class="input-group" style="margin-top:10px;">
                        <label>Nueva Contraseña</label>
                        <input type="password" name="new_password" required placeholder="Nueva contraseña">
                    </div>

                    <div class="input-group" style="margin-top:10px;">
                        <label>Confirmar Nueva</label>
                        <input type="password" name="confirm_password" required placeholder="Repite la nueva contraseña">
                    </div>

                    <button type="submit" class="btn-primary" style="width:100%; margin-top:20px; background:#4b5563;">Cambiar Contraseña</button>
                </form>
            </div>

            <hr style="margin: 20px 0; border:0; border-top:1px solid #eee;">
            
            <button class="btn-primary" style="background: #ef4444; width:100%;" onclick="location.href='index.php?logout=1'">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
            </button>
        </div>
    </div>

    <script src="js/map.js"></script>
    
    <script src="https://maps.googleapis.com/maps/api/js?key=********************************&loading=async&libraries=maps,places,marker&callback=initMap"></script>
    
    <script>
        function switchView(view) {
            // 1. Hide ALL sections
            const sections = ['home', 'map', 'list', 'nearby', 'stats'];
            sections.forEach(s => {
                const el = document.getElementById(s + '-view');
                if(el) el.style.display = 'none';
            });

            // 2. Remove Active from Sidebar
            document.querySelectorAll('.sidebar nav li').forEach(li => li.classList.remove('active'));

            // 3. Show Selected
            const selectedView = document.getElementById(view + '-view');
            if(selectedView) selectedView.style.display = 'block';

            // 4. Set Sidebar Active
            const navItem = document.getElementById('nav-' + view);
            if(navItem) navItem.classList.add('active');

            // 5. Specific Actions
            if(view === 'map') {
                setTimeout(() => { if(window.mapInstance) window.mapInstance.invalidateSize(); }, 100);
            }
            if(view === 'nearby') {
                if(typeof window.useCurrentLocation === 'function') {
                    window.useCurrentLocation(); 
                }
            }
        }

        function openProfile() {
            document.getElementById('profileModal').style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function openModal() {
            document.getElementById('modalTitle').innerText = 'Nuevo Bar';
            document.getElementById('formAccion').value = 'agregar';
            document.getElementById('barId').value = '';
            document.getElementById('barForm').reset();
            document.getElementById('addModal').style.display = 'flex';
        }

        function editBar(bar) {
            document.getElementById('modalTitle').innerText = 'Editar Bar';
            document.getElementById('formAccion').value = 'editar';
            document.getElementById('barId').value = bar.id;
            document.getElementById('nombre').value = bar.nombre;
            document.getElementById('estado').value = bar.estado;
            document.getElementById('direccion').value = bar.direccion;
            document.getElementById('puntuacion').value = bar.puntuacion;
            document.getElementById('comentario').value = bar.comentario;
            document.getElementById('addModal').style.display = 'flex';
        }

        function markAsVisited(bar) {
            document.getElementById('modalTitle').innerText = '¡Visitado! ¿Qué tal estuvo?';
            document.getElementById('formAccion').value = 'editar';
            document.getElementById('barId').value = bar.id;
            
            // Pre-fill existing data
            document.getElementById('nombre').value = bar.nombre;
            document.getElementById('direccion').value = bar.direccion;
            document.getElementById('lat').value = bar.lat;
            document.getElementById('lng').value = bar.lng;

            // Force state to Visitado
            document.getElementById('estado').value = 'visitado';
            
            // Focus on rating/comment
            document.getElementById('puntuacion').focus();
            
            // Show Modal
            document.getElementById('addModal').style.display = 'flex';
        }

        function switchProfileTab(tab) {
            // Hide all sections
            document.getElementById('tab-data').style.display = 'none';
            document.getElementById('tab-security').style.display = 'none';
            
            // Reset buttons
            const btns = document.querySelectorAll('.profile-tabs .tab-btn');
            btns.forEach(b => {
                b.style.color = '#9ca3af';
                b.style.borderBottomColor = 'transparent';
            });
            
            // Show selected
            if(tab === 'data') {
                document.getElementById('tab-data').style.display = 'block';
                btns[0].style.color = 'var(--primary-color)';
                btns[0].style.borderBottomColor = 'var(--primary-color)';
            } else {
                document.getElementById('tab-security').style.display = 'block';
                btns[1].style.color = 'var(--primary-color)';
                btns[1].style.borderBottomColor = 'var(--primary-color)';
            }
        }

        // FAB Draggable Logic
        const fab = document.querySelector('.fab-btn');
        let isDragging = false;
        let startX, startY, initialLeft, initialTop;

        function startDrag(e) {
            isDragging = true;
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            
            // Get current position
            const rect = fab.getBoundingClientRect();
            // We set left/top to fixed values to allow movement
            fab.style.bottom = 'auto';
            fab.style.right = 'auto';
            fab.style.left = rect.left + 'px';
            fab.style.top = rect.top + 'px';

            startX = clientX;
            startY = clientY;
            initialLeft = rect.left;
            initialTop = rect.top;

            fab.style.transition = 'none'; // Disable transition during drag
        }

        function drag(e) {
            if (!isDragging) return;
            e.preventDefault(); // Prevent scrolling
            
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            
            const dx = clientX - startX;
            const dy = clientY - startY;

            fab.style.left = (initialLeft + dx) + 'px';
            fab.style.top = (initialTop + dy) + 'px';
        }

        function endDrag() {
            if (!isDragging) return;
            isDragging = false;
            fab.style.transition = 'transform 0.2s, box-shadow 0.2s'; // Restore transition
        }

        if(fab) {
            fab.addEventListener('mousedown', startDrag);
            fab.addEventListener('touchstart', startDrag, {passive: false});

            window.addEventListener('mousemove', drag);
            window.addEventListener('touchmove', drag, {passive: false});

            window.addEventListener('mouseup', endDrag);
            window.addEventListener('touchend', endDrag);
            
            // Allow click only if not dragged significantly (simple check)
            let clickBlock = false;
            fab.addEventListener('click', (e) => {
                if(clickBlock) { e.preventDefault(); e.stopPropagation(); }
            });
        }

        window.onclick = function(e) {
            if(e.target.classList.contains('modal')) e.target.style.display = 'none';
        }
    </script>

    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
          navigator.serviceWorker.register('service-worker.js')
            .then(reg => console.log('✅ PWA: Service Worker registrado', reg))
            .catch(err => console.error('❌ PWA: Error al registrar', err));
        });
      }
    </script>
</body>
</html>