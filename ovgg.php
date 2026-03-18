<?php
system(PHP_OS_FAMILY === 'Windows' ? 'cls' : 'clear');

$Exstentions = [
    "Video Exstentions" => ["MP4", "AVI", "MKV", "MOV", "WMV", "FLV", "3GP", "WEBM", "MPEG", "VOB", "DAT", "TS"],
];

function printExstentions() {
    global $Exstentions;
    $index = 1;
    foreach ($Exstentions as $category => $formats) {
        echo "\033[1;34m" . $category . ":\033[0m\n";
        foreach ($formats as $format) {
            echo "\033[1;32m $index. " . $format . "\033[0m\n";
            $index++;
        }
    }
}

printExstentions();

$choice = (int) readline("Choose Your Video Extension Format (number) => ");
$Exstention = $Exstentions["Video Exstentions"][$choice - 1] ?? null;

if (!$Exstention) {
    die("Error: Invalid choice.\n");
}

$directory = trim(readline("Enter Directory Path Here => "));
$titledisplay = trim(readline("Enter Title Bar for Your HTML file => "));

if (!is_dir($directory)) {
    die("Error: Directory '$directory' does not exist.\n");
}

$handle = opendir($directory);
$videoList = [];

while (($file = readdir($handle)) !== false) {
    if (in_array($file, [".", ".."]) || !preg_match('/\.' . preg_quote($Exstention, '/') . '$/i', $file)) {
        continue;
    }

    $title = preg_replace('/\.' . preg_quote($Exstention, '/') . '$/i', '', $file);

    $video = [
        "title" => $title,
        "source" => $file,
    ];

    $found = false;
    foreach ($videoList as $item) {
        if (strtolower($item["title"]) === strtolower($title)) {
            $found = true;
            break;
        }
    }

    if (!$found) {
        $videoList[] = $video;
    }
}
closedir($handle);

if (empty($videoList)) {
    die("Error: No video files with extension .$Exstention found in '$directory'.\n");
}

$videoItems = "";
foreach ($videoList as $i => $video) {
    $safeTitle = htmlspecialchars($video["title"], ENT_QUOTES);
    $safeSource = htmlspecialchars($video["source"], ENT_QUOTES);

    $videoItems .= <<<HTML
        <div class="vid" data-src="{$safeSource}" data-title="{$safeTitle}" data-index="{$i}">
            <div class="thumb-wrap">
                <video class="thumb-video" muted playsinline preload="metadata" data-src="{$safeSource}"></video>
                <span class="thumb-badge">▶</span>
            </div>
            <div class="video-info">
                <h3 class="title">{$safeTitle}</h3>
            </div>
        </div>

HTML;
}

$safePageTitle = htmlspecialchars($titledisplay, ENT_QUOTES);
$firstVideoSource = htmlspecialchars($videoList[0]["source"], ENT_QUOTES);
$firstVideoTitle = htmlspecialchars($videoList[0]["title"], ENT_QUOTES);
$safeType = strtolower(htmlspecialchars($Exstention, ENT_QUOTES));

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$safePageTitle}</title>
    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            text-transform: capitalize;
            font-family: Arial, sans-serif;
            font-weight: normal;
        }

        body{
            background: rgba(0, 0, 0, 1);
            color: rgba(255, 255, 255, 0.95);
        }

        .theme-toggle{
            margin: 20px auto 0;
            display: block;
            cursor: pointer;
            padding: 10px 16px;
            background-color: rgba(0, 127, 255, 0.5);
            border: none;
            border-radius: 5px;
            color: white;
            box-shadow: 0 1px 2px rgba(255, 255, 255, 1.25);
        }

        .content-area{
            width: min(1700px, 100%);
            margin: 0 auto;
            padding: 20px 5%;
        }

        .main-video{
            background: rgba(0, 0, 139, 0.15);
            border-radius: 10px;
            padding: 18px;
            box-shadow: 0 4px 10px rgba(255, 255, 255, 1);
            margin-bottom: 18px;
        }
        .main-video-topbar{
            display: grid;
            grid-template-columns: 52px 1fr 52px;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .heading{
            color: #fff;
            font-size: 30px;
            line-height: 1.2;
            text-align: center;
            margin: 0;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-shadow: 2px 2px 4px rgba(255, 255, 255, 0.35);
        }

        .theme-toggle{
            width: 52px;
            height: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: none;
            border-radius: 14px;
            background-color: rgba(0, 127, 255, 0.5);
            color: white;
            box-shadow: 0 4px 10px rgba(255, 255, 255, 0.22);
            transition: transform 0.2s ease, background 0.3s ease, box-shadow 0.3s ease;
            margin: 0;
        }

        .theme-toggle:hover{
            background-color: rgba(229, 9, 20, 0.5);
            transform: translateY(-2px);
        }

        .theme-icon{
            font-size: 22px;
            line-height: 1;
        }

        .topbar-space{
            width: 52px;
            height: 52px;
        }

        .main-video .video{
            width: 100%;
            background: rgba(0, 0, 0, 0.25);
            border-radius: 10px;
            overflow: hidden;
        }

        .main-video video{
            width: 100%;
            border-radius: 9px;
            aspect-ratio: 16 / 9;
            max-height: 72vh;
            object-fit: contain;
            display: block;
            background: #000;
            box-shadow: 0 2px 10px rgba(0, 127, 255, 0.5);
        }

        .navigation-buttons{
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }

        button{
            background-color: rgba(0, 0, 51, 1);
            color: white;
            padding: 0.55em 1em;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
            box-shadow: 0 1px 2px rgba(255, 255, 255, 1.25);
        }

        button:hover{
            background-color: rgba(229, 9, 20, 0.5);
        }

        .main-video .title{
            color: rgba(255, 255, 255, 0.95);
            font-size: 26px;
            padding: 15px 0 6px;
            text-align: left;
            font-weight: bold;
            line-height: 1.4;
        }

        .main-video .main-desc{
            color: rgba(255, 255, 255, 0.72);
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 4px;
        }

        .search-container{
            margin-bottom: 18px;
        }

        .search-container input{
            width: 100%;
            padding: 12px 14px;
            border: none;
            border-radius: 8px;
            background-color: rgba(0, 72, 131, 0.25);
            color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 5px rgba(255, 255, 255, 1.15);
            outline: none;
            font-size: 15px;
        }

        .search-container input::placeholder{
            color: rgba(255, 255, 255, 0.7);
            text-transform: none;
        }

        .video-list{
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            align-items: start;
        }

        .video-list .vid{
            display: none;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.12);
            cursor: pointer;
            transition: transform 0.25s ease, background 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(255, 255, 255, 0.35);
        }

        .video-list .vid:hover{
            background: rgba(0, 127, 255, 0.25);
            transform: translateY(-4px);
            box-shadow: 0 10px 22px rgba(255, 255, 255, 0.22);
        }

        .video-list .vid.active{
            background: rgba(229, 9, 20, 0.35);
            border-color: rgba(255, 255, 255, 0.28);
        }

        .thumb-wrap{
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 9;
            background: rgba(0, 0, 51, 0.55);
            overflow: hidden;
        }

        .thumb-video{
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            background: #000;
            transition: transform 0.35s ease;
        }

        .video-list .vid:hover .thumb-video{
            transform: scale(1.06);
        }

        .thumb-badge{
            position: absolute;
            right: 10px;
            bottom: 10px;
            background: rgba(0, 0, 0, 0.55);
            color: rgba(255,255,255,0.95);
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 4px;
            pointer-events: none;
        }

        .pagination{
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
        }

        .pagination button{
            min-width: 42px;
            padding: 0.55em 0.9em;
        }

        .pagination button.active-page{
            background-color: rgba(229, 9, 20, 0.6);
        }

        .video-list .vid video{
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            pointer-events: none;
        }

        .video-list .video-info{
            padding: 12px;
        }

        .video-list .video-info .title{
            color: rgba(255, 255, 255, 0.95);
            font-size: 14px;
            font-weight: bold;
            line-height: 1.45;
            text-align: left;
            margin: 0 0 6px;
            word-break: break-word;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .video-list .video-info .desc{
            color: rgba(255, 255, 255, 0.72);
            font-size: 12px;
            line-height: 1.4;
            text-align: left;
            margin: 0;
            text-transform: none;
        }

        .light-theme .video-list .vid:hover{
            background: rgba(0, 72, 131, 0.25);
            box-shadow: 0 10px 22px rgba(0, 0, 0, 0.25);
        }

        .light-theme .pagination button.active-page{
            background-color: rgba(229, 9, 20, 1);
            color: #fff;
        }

        .light-theme{
            background: #FFFFFF;
            color: rgba(0, 0, 0, 0.95);
        }

        .light-theme .heading{
            color: rgba(0, 0, 0, 0.95);
            text-shadow: none;
        }

        .light-theme .theme-toggle{
            background-color: rgba(255, 255, 255, 1);
            color: rgba(0, 0, 0, 0.95);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.18);
        }

        .light-theme .main-video{
            background: rgba(0, 72, 131, 0.25);
            color: rgba(0, 0, 0, 0.95);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 1);
        }

        .light-theme .video-list .vid{
            background: rgba(0, 72, 131, 0.12);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 1);
        }

        .light-theme .video-list .vid.active{
            background: rgba(229, 9, 20, 1);
        }

        .light-theme .video-list .vid:hover{
            background: rgba(0, 72, 131, 0.25);
        }

        .light-theme .video-list .thumb-wrap{
            background: rgba(0, 72, 131, 0.18);
        }

        .light-theme .main-video .title,
        .light-theme .video-list .video-info .title{
            color: rgba(0, 0, 0, 0.95);
        }

        .light-theme .main-video .main-desc,
        .light-theme .video-list .video-info .desc{
            color: rgba(0, 0, 0, 0.72);
        }

        .light-theme .search-container input{
            background-color: rgba(0, 72, 131, 0.25);
            color: rgba(0, 0, 0, 0.95);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.35);
        }

        .light-theme .search-container input::placeholder{
            color: rgba(0, 0, 0, 0.6);
        }

        .light-theme button{
            background-color: rgba(255, 255, 255, 1);
            color: rgba(0, 0, 0, 0.95);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
        }

        .light-theme button:hover{
            background-color: rgba(229, 9, 20, 0.5);
        }

        @media (max-width: 768px){
            .content-area{
                padding: 15px 4%;
            }

            .main-video-topbar{
                grid-template-columns: 44px 1fr 44px;
                gap: 8px;
                margin-bottom: 12px;
            }

            .theme-toggle,
            .topbar-space{
                width: 44px;
                height: 44px;
            }

            .heading{
                font-size: 22px;
            }

            .theme-icon{
                font-size: 18px;
            }
        }

        @media (max-width: 991px){
            .content-area{
                padding: 15px 4%;
            }

            .video-list{
                grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            }
        }

        @media (max-width: 768px){
            .heading{
                font-size: 28px;
            }

            .main-video{
                padding: 12px;
            }

            .main-video .title{
                font-size: 20px;
                text-align: center;
            }

            .main-video .main-desc{
                text-align: center;
            }

            .video-list{
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 14px;
            }

            .navigation-buttons{
                gap: 8px;
            }

            button{
                font-size: 13px;
                padding: 0.5em 0.9em;
            }
        }
    </style>
</head>
<body>
    <div class="content-area">
        <div class="main-video">
            <div class="main-video-topbar">
                <button class="theme-toggle" aria-label="Toggle theme" title="Toggle theme">
                    <span class="theme-icon">◐</span>
                </button>

                <h1 class="heading">{$safePageTitle}</h1>

                <div class="topbar-space"></div>
            </div>
            <div class="video">
                <video src="{$firstVideoSource}" controls>
                    <source src="{$firstVideoSource}" type="video/{$safeType}">
                    Your browser does not support the video tag.
                </video>
            </div>

            <div class="navigation-buttons">
                <button id="prevButton">Prev</button>
                <button id="nextButton">Next</button>
                <button id="playPauseBtn">Play</button>
                <button id="fullscreenBtn">Fullscreen</button>
                <button id="volumeBtn">Mute</button>
            </div>

            <h3 class="title">{$firstVideoTitle}</h3>
        </div>

        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search video title...">
        </div>

        <div class="video-list">
            {$videoItems}
        </div>

        <div id="pagination" class="pagination"></div>

    <script>
        let allCards = Array.from(document.querySelectorAll('.video-list .vid'));
        let mainVideo = document.querySelector('.main-video video');
        let mainVideoContainer = document.querySelector('.main-video');
        let title = document.querySelector('.main-video .title');
        let playPauseBtn = document.getElementById('playPauseBtn');
        let fullscreenBtn = document.getElementById('fullscreenBtn');
        let volumeBtn = document.getElementById('volumeBtn');
        let prevButton = document.getElementById('prevButton');
        let nextButton = document.getElementById('nextButton');
        let searchInput = document.getElementById('searchInput');
        let themeToggle = document.querySelector('.theme-toggle');
        let pagination = document.getElementById('pagination');

        let perPage = 15;
        let currentPage = 1;
        let filteredCards = [...allCards];
        let activeCard = allCards[0] || null;

        function loadThumb(card) {
            const thumb = card.querySelector('.thumb-video');
            if (!thumb || thumb.dataset.loaded === '1') return;

            thumb.src = thumb.dataset.src;
            thumb.dataset.loaded = '1';
            thumb.load();

            thumb.addEventListener('loadeddata', () => {
                try {
                    thumb.currentTime = 0.1;
                } catch (e) {}
            }, { once: true });
        }

        function unloadThumb(card) {
            const thumb = card.querySelector('.thumb-video');
            if (!thumb || thumb.dataset.loaded !== '1') return;

            thumb.pause();
            thumb.removeAttribute('src');
            thumb.load();
            thumb.dataset.loaded = '0';
        }

        function clearThumbsExcept(cardsToKeep) {
            const keepSet = new Set(cardsToKeep);
            allCards.forEach(card => {
                if (!keepSet.has(card)) {
                    unloadThumb(card);
                }
            });
        }

        function playCard(card, autoScroll = true) {
            if (!card) return;

            allCards.forEach(vid => vid.classList.remove('active'));
            card.classList.add('active');

            mainVideo.src = card.dataset.src;
            title.textContent = card.dataset.title;
            mainVideo.load();

            activeCard = card;

            if (autoScroll) {
                mainVideoContainer.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }

        function renderPagination() {
            const totalPages = Math.max(1, Math.ceil(filteredCards.length / perPage));
            pagination.innerHTML = '';

            if (!filteredCards.length) return;

            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;

                if (i === currentPage) {
                    btn.classList.add('active-page');
                }

                btn.addEventListener('click', () => {
                    renderPage(i, false);
                });

                pagination.appendChild(btn);
            }
        }

        function renderPage(page, preserveActive = true) {
            const totalPages = Math.max(1, Math.ceil(filteredCards.length / perPage));
            currentPage = Math.min(Math.max(page, 1), totalPages);

            allCards.forEach(card => {
                card.style.display = 'none';
            });

            const start = (currentPage - 1) * perPage;
            const pageCards = filteredCards.slice(start, start + perPage);

            pageCards.forEach(card => {
                card.style.display = 'block';
                loadThumb(card);
            });

            clearThumbsExcept(pageCards);
            renderPagination();

            if (pageCards.length) {
                if (!preserveActive || !pageCards.includes(activeCard)) {
                    playCard(pageCards[0], false);
                } else {
                    activeCard.classList.add('active');
                }
            }
        }

        function goRelative(step) {
            if (!filteredCards.length) return;

            let currentIndex = filteredCards.indexOf(activeCard);
            if (currentIndex === -1) currentIndex = 0;

            let nextIndex = (currentIndex + step + filteredCards.length) % filteredCards.length;
            let targetCard = filteredCards[nextIndex];
            let targetPage = Math.floor(nextIndex / perPage) + 1;

            if (targetPage !== currentPage) {
                renderPage(targetPage, true);
            }

            playCard(targetCard, true);
        }

        allCards.forEach(card => {
            card.addEventListener('click', () => {
                playCard(card, true);
            });
        });

        prevButton.addEventListener('click', () => {
            goRelative(-1);
        });

        nextButton.addEventListener('click', () => {
            goRelative(1);
        });

        playPauseBtn.addEventListener('click', () => {
            if (mainVideo.paused) {
                mainVideo.play();
                playPauseBtn.textContent = 'Pause';
            } else {
                mainVideo.pause();
                playPauseBtn.textContent = 'Play';
            }
        });

        mainVideo.addEventListener('play', () => {
            playPauseBtn.textContent = 'Pause';
        });

        mainVideo.addEventListener('pause', () => {
            playPauseBtn.textContent = 'Play';
        });

        fullscreenBtn.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                mainVideo.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        });

        volumeBtn.addEventListener('click', () => {
            mainVideo.muted = !mainVideo.muted;
            volumeBtn.textContent = mainVideo.muted ? 'Unmute' : 'Mute';
        });

        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase().trim();

            filteredCards = allCards.filter(card => {
                const videoTitle = card.querySelector('.title').textContent.toLowerCase();
                return videoTitle.includes(searchTerm);
            });

            if (!filteredCards.length) {
                allCards.forEach(card => {
                    card.style.display = 'none';
                    card.classList.remove('active');
                    unloadThumb(card);
                });

                pagination.innerHTML = '';
                activeCard = null;
                title.textContent = 'Video tidak ditemukan';
                mainVideo.removeAttribute('src');
                mainVideo.load();
                return;
            }

            currentPage = 1;
            renderPage(1, false);
        });

        document.addEventListener('keydown', (event) => {
            switch (event.code) {
                case 'Space':
                    event.preventDefault();
                    mainVideo.paused ? mainVideo.play() : mainVideo.pause();
                    break;
                case 'ArrowUp':
                    mainVideo.volume = Math.min(mainVideo.volume + 0.1, 1);
                    if (mainVideo.volume > 0) {
                        mainVideo.muted = false;
                        volumeBtn.textContent = 'Mute';
                    }
                    break;
                case 'ArrowDown':
                    mainVideo.volume = Math.max(mainVideo.volume - 0.1, 0);
                    if (mainVideo.volume === 0) {
                        mainVideo.muted = true;
                        volumeBtn.textContent = 'Unmute';
                    }
                    break;
                case 'ArrowLeft':
                    mainVideo.currentTime = Math.max(mainVideo.currentTime - 5, 0);
                    break;
                case 'ArrowRight':
                    mainVideo.currentTime = Math.min(mainVideo.currentTime + 5, mainVideo.duration || mainVideo.currentTime + 5);
                    break;
            }
        });

        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('light-theme');
        });

        renderPage(1, false);
    </script>
</body>
</html>
HTML;

$fileNamex = trim(readline("Enter your file name without .html => "));
$fileName = $fileNamex . ".html";
$filePath = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

$fileHandle = fopen($filePath, "w") or die("Error: Could not open file '$filePath' for writing.\n");
fwrite($fileHandle, $html);
fclose($fileHandle);

echo "\033[1;33mGenerated HTML file saved to $filePath\033[0m\n";
?>


