<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Three-Tier Docker App</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        header { background: #1e293b; border-bottom: 1px solid #334155; padding: 1.5rem 2rem; display: flex; align-items: center; gap: 1rem; }
        header h1 { font-size: 1.4rem; font-weight: 700; color: #38bdf8; }
        .badge { background: #0ea5e9; color: #fff; font-size: .7rem; padding: .2rem .6rem; border-radius: 999px; font-weight: 600; letter-spacing: .05em; }
        main { max-width: 900px; margin: 2rem auto; padding: 0 1.5rem; }
        .tier-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2.5rem; }
        .tier-card { background: #1e293b; border: 1px solid #334155; border-radius: .75rem; padding: 1.25rem; text-align: center; }
        .tier-card .icon { font-size: 2rem; }
        .tier-card h3 { color: #94a3b8; font-size: .75rem; text-transform: uppercase; letter-spacing: .1em; margin: .5rem 0 .25rem; }
        .tier-card p { color: #38bdf8; font-weight: 600; font-size: .95rem; }
        h2 { font-size: 1.1rem; color: #94a3b8; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: .08em; }
        .post { background: #1e293b; border: 1px solid #334155; border-radius: .75rem; padding: 1.25rem 1.5rem; margin-bottom: 1rem; }
        .post h3 { color: #f8fafc; margin-bottom: .4rem; }
        .post p { color: #94a3b8; font-size: .9rem; line-height: 1.6; }
        .post-meta { font-size: .75rem; color: #475569; margin-top: .75rem; }
        footer { text-align: center; padding: 2rem; color: #475569; font-size: .8rem; margin-top: 2rem; }
        @media(max-width:600px){.tier-grid{grid-template-columns:1fr;}}
    </style>
</head>
<body>
<header>
    <h1>🐳 Three-Tier Docker App</h1>
    <span class="badge">LIVE</span>
</header>
<main>
    <div class="tier-grid">
        <div class="tier-card">
            <div class="icon">🌐</div>
            <h3>Tier 1</h3>
            <p>Nginx 1.25</p>
        </div>
        <div class="tier-card">
            <div class="icon">⚙️</div>
            <h3>Tier 2</h3>
            <p>PHP-FPM 8.2</p>
        </div>
        <div class="tier-card">
            <div class="icon">🗄️</div>
            <h3>Tier 3</h3>
            <p>MySQL 8.0</p>
        </div>
    </div>

    <h2>Posts from Database</h2>

    <?php foreach ($posts as $post): ?>
    <div class="post">
        <h3><?= htmlspecialchars($post['title']) ?></h3>
        <p><?= htmlspecialchars($post['body']) ?></p>
        <div class="post-meta">
            By <strong><?= htmlspecialchars($post['username']) ?></strong>
            &nbsp;·&nbsp;
            <?= date('M j, Y', strtotime($post['created_at'])) ?>
        </div>
    </div>
    <?php endforeach; ?>
</main>
<footer>
    Nginx → PHP-FPM → MySQL &nbsp;|&nbsp; Docker Compose with private network segmentation
</footer>
</body>
</html>
