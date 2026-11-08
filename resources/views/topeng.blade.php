<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $topeng->topeng_nama }} - Topeng</title>
    <style>
        :root {
            --bg: #f6f1e7;
            --card: #fffaf1;
            --ink: #2b2118;
            --muted: #6f6256;
            --line: #e2d6c4;
            --accent: #8a3b2e;
            --accent-dark: #6d2c22;
            --ok-bg: #e5f1e4;
            --ok-ink: #2f6b33;
            --off-bg: #efe8dc;
            --off-ink: #7a6a58;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            background: var(--bg);
            color: var(--ink);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            line-height: 1.5;
        }

        .card {
            width: 100%;
            max-width: 420px;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(43, 33, 24, 0.08);
        }

        .photo {
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            background: var(--off-bg);
        }

        .body { padding: 20px; }

        .eyebrow {
            margin: 0 0 4px;
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
        }

        h1 {
            margin: 0 0 4px;
            font-size: 24px;
            line-height: 1.25;
        }

        .sifat {
            margin: 0 0 14px;
            color: var(--muted);
            font-size: 14px;
        }

        .desc {
            margin: 0 0 16px;
            font-size: 15px;
        }

        .status {
            display: inline-block;
            margin-bottom: 18px;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
        }

        .status.on { background: var(--ok-bg); color: var(--ok-ink); }
        .status.off { background: var(--off-bg); color: var(--off-ink); }

        .cta {
            display: block;
            width: 100%;
            padding: 13px 16px;
            border-radius: 10px;
            background: var(--accent);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
        }

        .cta:active { background: var(--accent-dark); }

        .hint {
            margin: 12px 0 0;
            font-size: 13px;
            color: var(--muted);
            text-align: center;
        }
    </style>
</head>
<body>
    <main class="card">
        @if ($topeng->topeng_gambar_url)
            <img class="photo" src="{{ $topeng->topeng_gambar_url }}" alt="{{ $topeng->topeng_nama }}">
        @endif

        <div class="body">
            <p class="eyebrow">Topeng</p>
            <h1>{{ $topeng->topeng_nama }}</h1>

            @if ($topeng->sifat_topeng)
                <p class="sifat">{{ $topeng->sifat_topeng }}</p>
            @endif

            @if ($topeng->topeng_deskripsi)
                <p class="desc">{{ $topeng->topeng_deskripsi }}</p>
            @endif

            @if ($hasVideo)
                <span class="status on">Video AI tersedia</span>
            @else
                <span class="status off">Belum ada video AI</span>
            @endif

            <a class="cta" href="{{ $deepLink }}">Buka aplikasi</a>
            <p class="hint">Belum punya aplikasi? Untuk demo, install Expo Go di HP lalu tekan tombol di atas.</p>
        </div>
    </main>
</body>
</html>
