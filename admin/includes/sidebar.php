<?php
// セッションが開始されていない場合は開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ログイン確認
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// 現在のページのファイル名を取得
$current_page = basename($_SERVER['PHP_SELF']);
?>


<!-- サイドバー -->
<div class="sidebar w-64 text-white hidden md:block bg-blue-800">
    <div class="p-6">
        <h1 class="text-2xl font-bold">管理ページ</h1>
        <p class="text-sm text-blue-200">ConvivialNet</p>
    </div>
    <nav class="mt-6">
        <a href="index.php" class="flex items-center py-3 px-6 <?php echo ($current_page == 'index.php') ? 'bg-blue-800 bg-opacity-30' : 'hover:bg-blue-800 hover:bg-opacity-30 transition-colors'; ?>">
            <i class="fas fa-tachometer-alt mr-3"></i>
            <span>ダッシュボード</span>
        </a>
        <a href="activities.php" class="flex items-center py-3 px-6 <?php echo ($current_page == 'activities.php' || $current_page == 'activity_add.php' || $current_page == 'activity_edit.php') ? 'bg-blue-800 bg-opacity-30' : 'hover:bg-blue-800 hover:bg-opacity-30 transition-colors'; ?>">
            <i class="fas fa-calendar-alt mr-3"></i>
            <span>活動記録管理</span>
        </a>
        <a href="blogs.php" class="flex items-center py-3 px-6 <?php echo ($current_page == 'blogs.php' || $current_page == 'blog_add.php' || $current_page == 'blog_edit.php') ? 'bg-blue-800 bg-opacity-30' : 'hover:bg-blue-800 hover:bg-opacity-30 transition-colors'; ?>">
            <i class="fas fa-newspaper mr-3"></i>
            <span>技術ブログ管理</span>
        </a>
        <a href="categories.php" class="flex items-center py-3 px-6 <?php echo ($current_page == 'categories.php') ? 'bg-blue-800 bg-opacity-30' : 'hover:bg-blue-800 hover:bg-opacity-30 transition-colors'; ?>">
            <i class="fas fa-tags mr-3"></i>
            <span>カテゴリ・タグ管理</span>
        </a>
        <a href="users.php" class="flex items-center py-3 px-6 <?php echo ($current_page == 'users.php' || $current_page == 'user_add.php' || $current_page == 'user_edit.php') ? 'bg-blue-800 bg-opacity-30' : 'hover:bg-blue-800 hover:bg-opacity-30 transition-colors'; ?>">
            <i class="fas fa-users mr-3"></i>
            <span>ユーザー管理</span>
        </a>
        <a href="profile.php" class="flex items-center py-3 px-6 <?php echo ($current_page == 'profile.php') ? 'bg-blue-800 bg-opacity-30' : 'hover:bg-blue-800 hover:bg-opacity-30 transition-colors'; ?>">
            <i class="fas fa-users mr-3"></i>
            <span>プロフィール管理</span>
        </a>
    </nav>
    <div class="absolute bottom-0 w-64 p-6">
        <a href="../logout.php" class="flex items-center text-blue-200 hover:text-white transition-colors">
            <i class="fas fa-sign-out-alt mr-3"></i>
            <span>ログアウト</span>
        </a>
    </div>
</div>

<!-- モバイルメニュートグルのJavaScript -->
<script>
    // モバイルメニュートグル用のイベントリスナーを設定
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.querySelector('button.md\\:hidden');
        if (mobileMenuButton) {
            mobileMenuButton.addEventListener('click', function() {
                const sidebar = document.querySelector('.sidebar');
                sidebar.classList.toggle('hidden');
            });
        }
    });
</script>