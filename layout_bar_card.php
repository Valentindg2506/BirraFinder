<div class="bar-card">
    <div class="bar-header">
        <h3><?php echo htmlspecialchars($bar['nombre']); ?></h3>
        <span class="badge <?php echo $bar['estado']; ?>"><?php echo ucfirst($bar['estado']); ?></span>
    </div>
    <p style="color:#f59e0b; margin: 0.5rem 0;"><?php echo str_repeat('<i class="fa-solid fa-star"></i>', $bar['puntuacion'] ?? 0); ?></p>
    <p class="bar-comment">"<?php echo htmlspecialchars($bar['comentario']); ?>"</p>
    <div class="bar-actions">
            <?php if ($bar['estado'] === 'pendiente'): ?>
                <button onclick='markAsVisited(<?php echo json_encode($bar); ?>)' title="¡Ya fui! Calificar" style="color: #10b981; border-color: #10b981;"><i class="fa-solid fa-check"></i></button>
            <?php endif; ?>
            <button onclick='editBar(<?php echo json_encode($bar); ?>)'><i class="fa-solid fa-pen"></i></button>
            <form action="app/controllers/BarsController.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Borrar?');">
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="id" value="<?php echo $bar['id']; ?>">
            <button type="submit" class="delete"><i class="fa-solid fa-trash"></i></button>
        </form>
    </div>
</div>
