<?php
require_once __DIR__ . '/config.php';

$mensaje = '';
$error = '';
$editar = null;

// Crear o actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        if ($accion === 'guardar') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $destinatario = trim($_POST['destinatario'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');

            if ($destinatario === '' || $direccion === '' || $descripcion === '') {
                throw new Exception('Completa todos los campos del envío.');
            }

            if ($id) {
                $stmt = $pdo->prepare("
                    UPDATE envios
                    SET destinatario = ?, direccion = ?, descripcion = ?
                    WHERE id = ?
                ");
                $stmt->execute([$destinatario, $direccion, $descripcion, $id]);
                $mensaje = 'Envío actualizado correctamente.';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO envios (destinatario, direccion, descripcion)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$destinatario, $direccion, $descripcion]);
                $mensaje = 'Envío registrado correctamente.';
            }
        }

        if ($accion === 'eliminar') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('Envío no válido.');
            $stmt = $pdo->prepare("DELETE FROM envios WHERE id = ?");
            $stmt->execute([$id]);
            $mensaje = 'Envío eliminado correctamente.';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

// Cargar envío para edición
if (isset($_GET['editar'])) {
    $id = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM envios WHERE id = ?");
        $stmt->execute([$id]);
        $editar = $stmt->fetch();
        if (!$editar) $error = 'El envío solicitado no existe.';
    }
}

$buscar = trim($_GET['buscar'] ?? '');

if ($buscar !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM envios
        WHERE destinatario LIKE ? OR direccion LIKE ? OR descripcion LIKE ?
        ORDER BY fecha_creacion DESC
    ");
    $like = "%{$buscar}%";
    $stmt->execute([$like, $like, $like]);
    $envios = $stmt->fetchAll();
} else {
    $envios = $pdo->query("SELECT * FROM envios ORDER BY fecha_creacion DESC")->fetchAll();
}

$total = count($envios);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Envíos | Gestión</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --surface: #ffffff;
            --surface-2: #eef3f8;
            --text: #172033;
            --muted: #617087;
            --primary: #2457d6;
            --primary-dark: #173fa6;
            --secondary: #12a889;
            --danger: #d64545;
            --border: #dce3ec;
            --shadow: 0 14px 35px rgba(24, 39, 75, .10);
            --radius: 18px;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.5;
        }

        button, input, textarea { font: inherit; }
        button, a { -webkit-tap-highlight-color: transparent; }

        .topbar {
            background: linear-gradient(135deg, #162a52, #2457d6);
            color: white;
            padding: 28px 20px 70px;
        }

        .container { width: min(1120px, calc(100% - 32px)); margin: 0 auto; }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .logo {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,.15);
            font-size: 24px;
            font-weight: 800;
        }

        h1 { margin: 0; font-size: clamp(25px, 4vw, 36px); }
        .subtitle { margin: 3px 0 0; opacity: .85; }

        main { margin-top: -42px; padding-bottom: 110px; }

        .grid {
            display: grid;
            grid-template-columns: 370px 1fr;
            gap: 22px;
            align-items: start;
        }

        .card {
            background: var(--surface);
            border: 1px solid rgba(220,227,236,.9);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 24px;
        }

        .card h2 { margin: 0 0 6px; font-size: 21px; }
        .hint { color: var(--muted); margin: 0 0 20px; font-size: 14px; }

        label {
            display: block;
            font-weight: 700;
            margin: 15px 0 7px;
        }

        input, textarea {
            width: 100%;
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: 12px 13px;
            background: white;
            color: var(--text);
            outline: none;
            transition: .18s ease;
        }

        input:focus, textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(36,87,214,.13);
        }

        textarea { min-height: 120px; resize: vertical; }

        .actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            min-height: 46px;
            border: 0;
            border-radius: 12px;
            padding: 11px 15px;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: var(--surface-2); color: var(--text); }
        .btn-danger { background: #fff0f0; color: var(--danger); }
        .btn-small { min-height: 38px; padding: 8px 11px; font-size: 13px; }

        .search {
            display: flex;
            gap: 10px;
            margin-bottom: 18px;
        }

        .search input { flex: 1; }

        .stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            gap: 10px;
        }

        .badge {
            background: #e8efff;
            color: #2349a5;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 13px;
            font-weight: 800;
        }

        .list { display: grid; gap: 13px; }

        .shipment {
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 17px;
            background: #fff;
        }

        .shipment-head {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            align-items: flex-start;
        }

        .shipment h3 { margin: 0; font-size: 17px; }
        .meta { color: var(--muted); font-size: 13px; margin-top: 3px; }
        .description { margin: 13px 0; white-space: pre-wrap; }
        .address {
            background: #f7f9fc;
            border-radius: 11px;
            padding: 10px 12px;
            font-size: 14px;
        }

        .shipment-actions {
            display: flex;
            gap: 8px;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid var(--border);
        }

        .alert {
            width: min(1120px, calc(100% - 32px));
            margin: 18px auto 0;
            padding: 12px 15px;
            border-radius: 12px;
            font-weight: 700;
        }

        .success { background: #e8f8f2; color: #087c61; }
        .error { background: #fff0f0; color: #a32d2d; }

        .empty {
            text-align: center;
            padding: 40px 20px;
            color: var(--muted);
            border: 2px dashed var(--border);
            border-radius: 15px;
        }

        .footer-nav {
            position: fixed;
            z-index: 20;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,.96);
            backdrop-filter: blur(12px);
            border-top: 1px solid var(--border);
            box-shadow: 0 -8px 25px rgba(24,39,75,.08);
            padding: 10px 16px;
        }

        .footer-nav-inner {
            width: min(650px, 100%);
            margin: auto;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 9px;
        }

        .footer-nav .btn { min-height: 48px; }

        @media (max-width: 850px) {
            .grid { grid-template-columns: 1fr; }
            .card.form-card { order: 1; }
            .card.list-card { order: 2; }
        }

        @media (max-width: 520px) {
            .container { width: min(100% - 20px, 1120px); }
            .topbar { padding-left: 12px; padding-right: 12px; }
            .card { padding: 17px; }
            .search { flex-direction: column; }
            .search .btn { width: 100%; }
            .shipment-head { flex-direction: column; }
            .actions { grid-template-columns: 1fr; }
            .footer-nav-inner { grid-template-columns: 1fr 1fr; }
            .footer-nav .btn:last-child { grid-column: 1 / -1; }
        }
    </style>
</head>
<body>
<header class="topbar">
    <div class="container">
        <div class="brand">
            <div class="logo">✦</div>
            <div>
                <h1>Gestión de Envíos</h1>
                <p class="subtitle">Registra, consulta y administra tus envíos.</p>
            </div>
        </div>
    </div>
</header>

<?php if ($mensaje): ?>
    <div class="alert success" role="status"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert error" role="alert"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<main class="container">
    <div class="grid">
        <section class="card form-card" id="formulario">
            <h2><?= $editar ? 'Editar envío' : 'Nuevo envío' ?></h2>
            <p class="hint">Los tres campos son obligatorios.</p>

            <form method="post" autocomplete="off">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" name="id" value="<?= $editar ? (int)$editar['id'] : '' ?>">

                <label for="destinatario">Destinatario</label>
                <input id="destinatario" name="destinatario" maxlength="150" required
                       value="<?= htmlspecialchars($editar['destinatario'] ?? '') ?>"
                       placeholder="Ej. María González">

                <label for="direccion">Dirección</label>
                <input id="direccion" name="direccion" maxlength="255" required
                       value="<?= htmlspecialchars($editar['direccion'] ?? '') ?>"
                       placeholder="Ej. Calle 10 # 25-30, Cali">

                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" required
                          placeholder="Describe el contenido o información del envío..."><?= htmlspecialchars($editar['descripcion'] ?? '') ?></textarea>

                <div class="actions">
                    <button class="btn btn-primary" type="submit">
                        <?= $editar ? '✓ Guardar cambios' : '+ Registrar envío' ?>
                    </button>
                    <?php if ($editar): ?>
                        <a class="btn btn-secondary" href="index.php">Cancelar</a>
                    <?php else: ?>
                        <button class="btn btn-secondary" type="reset">Limpiar</button>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="card list-card" id="envios">
            <div class="stats">
                <div>
                    <h2 style="margin:0">Envíos registrados</h2>
                    <div class="hint" style="margin:2px 0 0">Consulta y administra los registros.</div>
                </div>
                <span class="badge"><?= $total ?> registro<?= $total === 1 ? '' : 's' ?></span>
            </div>

            <form class="search" method="get">
                <input name="buscar" aria-label="Buscar envíos"
                       value="<?= htmlspecialchars($buscar) ?>"
                       placeholder="Buscar destinatario, dirección o descripción...">
                <button class="btn btn-primary" type="submit">Buscar</button>
                <?php if ($buscar !== ''): ?>
                    <a class="btn btn-secondary" href="index.php">Ver todos</a>
                <?php endif; ?>
            </form>

            <?php if (!$envios): ?>
                <div class="empty">
                    <div style="font-size:35px">📦</div>
                    <strong>No hay envíos para mostrar.</strong>
                    <div>Registra el primero usando el formulario.</div>
                </div>
            <?php else: ?>
                <div class="list">
                    <?php foreach ($envios as $envio): ?>
                        <article class="shipment">
                            <div class="shipment-head">
                                <div>
                                    <h3><?= htmlspecialchars($envio['destinatario']) ?></h3>
                                    <div class="meta">
                                        Envío #<?= (int)$envio['id'] ?> ·
                                        <?= htmlspecialchars(date('d/m/Y H:i', strtotime($envio['fecha_creacion']))) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="address">
                                <strong>📍 Dirección:</strong>
                                <?= htmlspecialchars($envio['direccion']) ?>
                            </div>

                            <div class="description"><?= htmlspecialchars($envio['descripcion']) ?></div>

                            <div class="shipment-actions">
                                <a class="btn btn-small btn-secondary"
                                   href="?editar=<?= (int)$envio['id'] ?>">✎ Editar</a>

                                <form method="post" onsubmit="return confirm('¿Eliminar este envío? Esta acción no se puede deshacer.');">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?= (int)$envio['id'] ?>">
                                    <button class="btn btn-small btn-danger" type="submit">🗑 Eliminar</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<nav class="footer-nav" aria-label="Acciones principales">
    <div class="footer-nav-inner">
        <a class="btn btn-secondary" href="#envios">📦 Envíos</a>
        <a class="btn btn-primary" href="#formulario">＋ Nuevo envío</a>
        <a class="btn btn-secondary" href="index.php">⌂ Inicio</a>
    </div>
</nav>
</body>
</html>
