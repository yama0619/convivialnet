<?php
// ページタイトルが設定されていない場合のデフォルト値
if (!isset($page_title)) {
    $page_title = '';
}
?>
<header class="sticky top-0 z-50 w-full border-b bg-white shadow-sm">
    <div class="container mx-auto px-4">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center">
                <a href="index.php" class="text-lg font-bold text-gray-900 mr-8">
                    ConvivialNet
                </a>
                
                <nav class="hidden md:flex">
                    <ul class="flex space-x-8">
                        <li>
                            <a href="list.php" class="text-sm font-medium text-gray-900 hover:text-blue-600 transition-colors">
                                活動記録
                            </a>
                        </li>
                        <li>
                            <a href="techbrog.php" class="text-sm font-medium text-gray-900 hover:text-blue-600 transition-colors">
                                技術ブログ
                            </a>
                        </li>
                        <li>
                            <a href="contact.php" class="text-sm font-medium text-gray-900 hover:text-blue-600 transition-colors">
                                お問い合わせ
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            
            <div class="flex items-center">
                <button id="mobile-menu-button" class="md:hidden p-2 mr-2 text-gray-600 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                        <line x1="4" x2="20" y1="12" y2="12"></line>
                        <line x1="4" x2="20" y1="6" y2="6"></line>
                        <line x1="4" x2="20" y1="18" y2="18"></line>
                    </svg>
                    <span class="sr-only">メニューを開く</span>
                </button>
                <a href="login.php" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"> ログイン </a> 
            </div>
        </div>
    </div>
    
    <!-- モバイルメニュー -->
    <div id="mobile-menu" class="hidden md:hidden border-t">
        <nav class="container mx-auto px-4 py-2">
            <ul class="space-y-1">
                <li>
                    <a href="list.php" class="block py-2 text-sm font-medium text-gray-900 hover:text-blue-600">
                        活動記録
                    </a>
                </li>
                <li>
                    <a href="techbrog.php" class="block py-2 text-sm font-medium text-gray-900 hover:text-blue-600">
                        技術ブログ
                    </a>
                </li>
                <li>
                    <a href="contact.php" class="block py-2 text-sm font-medium text-gray-900 hover:text-blue-600">
                        お問い合わせ
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</header>

<script>
    // モバイルメニューの切り替え
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenu.classList.toggle('hidden');
    });
</script>