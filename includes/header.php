<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>活動記録 | 組織名</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style type="text/css">
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-50">
    <header class="sticky top-0 z-50 w-full border-b bg-white/95 backdrop-blur">
        <div class="container flex h-16 items-center justify-between px-4 mx-auto">
            <div class="flex items-center gap-2">
                <a href="index.php" class="font-bold">
                    ConvivialNet
                </a>
                <nav class="hidden md:flex md:gap-6 md:text-sm">
                    <a href="index.php" class="font-medium transition-colors hover:text-gray-600">
                        ホーム
                    </a>
                    <a href="list.php" class="font-medium text-black transition-colors hover:text-gray-600">
                        活動記録
                    </a>
                    <a href="techbrog.php" class="font-medium transition-colors hover:text-gray-600">
                        技術ブログ
                    </a>
                    <a href="contact.php" class="font-medium transition-colors hover:text-gray-600">
                        お問い合わせ
                    </a>
                </nav>
            </div>
            <div class="flex items-center gap-2">
                <button id="mobile-menu-button" class="md:hidden p-2 rounded-md border border-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                        <line x1="4" x2="20" y1="12" y2="12"></line>
                        <line x1="4" x2="20" y1="6" y2="6"></line>
                        <line x1="4" x2="20" y1="18" y2="18"></line>
                    </svg>
                    <span class="sr-only">メニューを開く</span>
                </button>
                <a href="login.php" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                    ログイン
                </a>
            </div>
        </div>
        
        <!-- Mobile menu, hidden by default -->
        <div id="mobile-menu" class="hidden md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 border-b">
                <a href="index.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-100">
                    ホーム
                </a>
                <a href="list.php" class="block px-3 py-2 rounded-md text-base font-medium bg-gray-100">
                    活動記録
                </a>
                <a href="techbrog.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-100">
                    技術ブログ
                </a>
                <a href="contact.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-100">
                    お問い合わせ
                </a>
            </div>
        </div>
    </header>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
    </script>

