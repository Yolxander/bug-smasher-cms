<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $documentationPage->title }} - Preview</title>
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #fffbe8;
            margin: 0;
            min-height: 100vh;
        }
        .wavy-header {
            width: 100%;
            height: 60px;
            background: #fffbe8;
            position: relative;
        }
        .wavy-header svg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .nav-bar {
            max-width: 900px;
            margin: -30px auto 0 auto;
            background: #fff;
            border: 2px solid #222;
            border-radius: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem 2rem;
            box-shadow: 0 2px 8px 0 rgba(0,0,0,0.03);
            position: relative;
            z-index: 2;
        }
        .nav-left {
            font-weight: 700;
            font-size: 1.3rem;
            letter-spacing: -0.03em;
        }
        .nav-links {
            display: flex;
            gap: 2rem;
            font-size: 1rem;
        }
        .nav-links a {
            color: #222;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .nav-links a:hover {
            color: #facc15;
        }
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .nav-btn {
            background: #fffbe8;
            border: 2px solid #222;
            border-radius: 2rem;
            padding: 0.5rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: #222;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
            box-shadow: 0 2px 8px 0 rgba(0,0,0,0.03);
        }
        .nav-btn:hover {
            background: #facc15;
            color: #222;
        }
        .main-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 60vh;
            margin-top: 3rem;
        }
        .doc-title {
            font-size: 2.5rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 1.2rem;
            margin-top: 2rem;
            color: #111;
            letter-spacing: -0.03em;
        }
        .doc-category {
            display: inline-block;
            background: #facc15;
            color: #222;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 9999px;
            padding: 0.2rem 1rem;
            margin-bottom: 1.5rem;
            border: 1.5px solid #222;
        }
        .markdown-content {
            background: #fff;
            border: 2px solid #222;
            border-radius: 2rem;
            max-width: 700px;
            width: 100%;
            margin: 0 auto 2.5rem auto;
            padding: 2.5rem 2rem;
            box-shadow: 0 2px 8px 0 rgba(0,0,0,0.04);
            font-size: 1.15rem;
            color: #222;
            min-height: 120px;
        }
        .markdown-content h1, .markdown-content h2, .markdown-content h3 {
            font-weight: 700;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }
        .markdown-content ul {
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }
        .markdown-content li {
            margin-bottom: 0.5rem;
        }
        .markdown-content pre {
            background: #facc15;
            color: #222;
            border-radius: 1rem;
            padding: 1rem;
            font-size: 1rem;
            overflow-x: auto;
        }
        .markdown-content code {
            background: #facc15;
            color: #222;
            border-radius: 0.5rem;
            padding: 0.2rem 0.5rem;
        }
        .markdown-content a {
            color: #facc15;
            text-decoration: underline;
        }
        .center-btn {
            display: flex;
            justify-content: center;
            margin-top: 2.5rem;
        }
        .main-btn {
            background: #fffbe8;
            border: 2px solid #222;
            border-radius: 2rem;
            padding: 0.75rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: #222;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
            box-shadow: 0 2px 8px 0 rgba(0,0,0,0.03);
        }
        .main-btn:hover {
            background: #facc15;
            color: #222;
        }
        /* Sidebar styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 340px;
            max-width: 90vw;
            height: 100vh;
            background: #fffbe8;
            border-right: 2px solid #222;
            box-shadow: 2px 0 16px 0 rgba(0,0,0,0.07);
            z-index: 50;
            padding: 2rem 1.5rem 1.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            transition: transform 0.2s;
        }
        .sidebar-header {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #222;
        }
        .sidebar-list {
            flex: 1;
            overflow-y: auto;
        }
        .sidebar-list-item {
            padding: 0.7rem 1rem;
            border-radius: 1rem;
            font-size: 1.05rem;
            font-weight: 500;
            color: #222;
            cursor: pointer;
            border: 2px solid transparent;
            margin-bottom: 0.5rem;
            transition: background 0.2s, border 0.2s;
        }
        .sidebar-list-item.active, .sidebar-list-item:hover {
            background: #facc15;
            border: 2px solid #222;
        }
        .sidebar-close {
            align-self: flex-end;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #222;
            margin-bottom: 1rem;
        }
        .sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.15);
            z-index: 40;
        }
        @media (max-width: 700px) {
            .nav-bar, .markdown-content {
                border-radius: 1rem;
                padding: 1rem;
            }
            .main-content {
                margin-top: 1.5rem;
            }
            .doc-title {
                font-size: 2rem;
            }
            .sidebar {
                width: 95vw;
                padding: 1.2rem 0.7rem 1rem 0.7rem;
            }
        }
    </style>
</head>
<body x-data="docPreview()" x-init="init({{ $documentationPage->id }})">
    <div class="wavy-header">
        <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 40 Q 360 0 720 40 T 1440 40 V60 H0Z" fill="#facc15"/>
        </svg>
    </div>
    <nav class="nav-bar">
        <div class="nav-left">Documentation</div>
        <div class="nav-links">

        </div>
        <div class="nav-actions">
            <button class="nav-btn" @click="showList = true">Show List</button>
            <a href="{{ route('filament.admin.pages.dashboard') }}" class="nav-btn">Dashboard</a>
        </div>
    </nav>
    <!-- Sidebar for documentation list -->
    <template x-if="showList">
        <div>
            <div class="sidebar-backdrop" @click="showList = false"></div>
            <aside class="sidebar">
                <button class="sidebar-close" @click="showList = false">&times;</button>
                <div class="sidebar-header">Documentation Pages</div>
                <div class="sidebar-list">
                    <template x-for="page in pages" :key="page.id">
                        <div :class="['sidebar-list-item', page.id === currentId ? 'active' : '']"
                             @click="loadPage(page.id)">
                            <span x-text="page.title"></span>
                        </div>
                    </template>
                </div>
            </aside>
        </div>
    </template>
    <main class="main-content">
        <div class="doc-category" x-text="currentCategory ?? '{{ ucfirst(str_replace('-', ' ', $documentationPage->category)) }}'"></div>
        <div class="doc-title" x-text="currentTitle ?? @js($documentationPage->title)"></div>
        <div class="markdown-content" x-html="currentContent ?? @js(Str::markdown($documentationPage->content))"></div>
        <div class="center-btn">
            <button class="main-btn" @click="showList = true">Show List</button>
        </div>
    </main>
    <script>
    function docPreview() {
        return {
            showList: false,
            pages: [],
            currentId: null,
            currentTitle: null,
            currentCategory: null,
            currentContent: null,
            init(initialId) {
                this.currentId = initialId;
                // Fetch all documentation pages (id, title, category)
                fetch('/documentation-pages-list')
                    .then(res => res.json())
                    .then(data => {
                        this.pages = data;
                    });
            },
            loadPage(id) {
                this.showList = false;
                fetch(`/documentation/${id}/json`)
                    .then(res => res.json())
                    .then(data => {
                        this.currentId = data.id;
                        this.currentTitle = data.title;
                        this.currentCategory = data.category.charAt(0).toUpperCase() + data.category.slice(1).replace('-', ' ');
                        this.currentContent = data.content_html;
                        window.history.replaceState({}, '', `/documentation/${data.id}/preview`);
                    });
            }
        }
    }
    </script>
</body>
</html>
