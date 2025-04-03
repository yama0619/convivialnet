<?php
session_start();

// ログイン確認
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// データベース接続
require_once '../db.php';

// 活動記録の総数を取得
$activity_count_query = "SELECT COUNT(*) as count FROM posts";
$activity_count_result = $conn->query($activity_count_query);
$activity_count = $activity_count_result->fetch_assoc()['count'];

// 技術ブログの総数を取得
$blog_count_query = "SELECT COUNT(*) as count FROM tecblog";
$blog_count_result = $conn->query($blog_count_query);
$blog_count = $blog_count_result->fetch_assoc()['count'];

// 最近の活動記録を取得
$recent_activities_query = "SELECT id, title, created_at FROM posts ORDER BY created_at DESC LIMIT 5";
$recent_activities_result = $conn->query($recent_activities_query);

// 最近の技術ブログを取得
$recent_blogs_query = "SELECT id, title, category_id, created_at FROM tecblog ORDER BY created_at DESC LIMIT 5";
$recent_blogs_result = $conn->query($recent_blogs_query);

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理ページ | Convivial Net</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
        }
        .sidebar {
            background-image: linear-gradient(180deg, #1e40af 0%, #3b82f6 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- サイドバー -->
        <div class="sidebar w-64 text-white hidden md:block">
            <div class="p-6">
                <h1 class="text-2xl font-bold">管理ページ</h1>
                <p class="text-sm text-blue-200">Convivial Net</p>
            </div>
            <nav class="mt-6">
                <a href="index.php" class="flex items-center py-3 px-6 bg-blue-800 bg-opacity-30">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    <span>ダッシュボード</span>
                </a>
                <a href="activities.php" class="flex items-center py-3 px-6 hover:bg-blue-800 hover:bg-opacity-30 transition-colors">
                    <i class="fas fa-calendar-alt mr-3"></i>
                    <span>活動記録管理</span>
                </a>
                <a href="blogs.php" class="flex items-center py-3 px-6 hover:bg-blue-800 hover:bg-opacity-30 transition-colors">
                    <i class="fas fa-newspaper mr-3"></i>
                    <span>技術ブログ管理</span>
                </a>
                <a href="categories.php" class="flex items-center py-3 px-6 hover:bg-blue-800 hover:bg-opacity-30 transition-colors">
                    <i class="fas fa-tags mr-3"></i>
                    <span>カテゴリ・タグ管理</span>
                </a>
                <a href="users.php" class="flex items-center py-3 px-6 hover:bg-blue-800 hover:bg-opacity-30 transition-colors">
                    <i class="fas fa-users mr-3"></i>
                    <span>ユーザー管理</span>
                </a>
            </nav>
            <div class="absolute bottom-0 w-64 p-6">
                <a href="../logout.php" class="flex items-center text-blue-200 hover:text-white transition-colors">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    <span>ログアウト</span>
                </a>
            </div>
        </div>

        <!-- メインコンテンツ -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- ヘッダー -->
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center">
                        <button class="text-gray-500 focus:outline-none md:hidden mr-3">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h2 class="text-xl font-semibold text-gray-800">ダッシュボード</h2>
                    </div>
                    <div class="flex items-center">
                        <a href="../index.php" class="text-blue-600 hover:text-blue-800 mr-4" target="_blank">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            サイトを表示
                        </a>
                        <div class="relative">
                            <button class="flex items-center text-gray-700 focus:outline-none">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white">
                                    <?php echo substr($_SESSION["username"], 0, 1); ?>
                                </div>
                                <span class="ml-2"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                                <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- コンテンツエリア -->
            <main class="flex-1 overflow-y-auto p-4">
                <!-- 統計カード -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-500 bg-opacity-10 text-blue-500">
                                <i class="fas fa-calendar-alt text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-500 text-sm">活動記録</p>
                                <h3 class="text-2xl font-bold"><?php echo $activity_count; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 bg-opacity-10 text-green-500">
                                <i class="fas fa-newspaper text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-500 text-sm">技術ブログ</p>
                                <h3 class="text-2xl font-bold"><?php echo $blog_count; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-500 bg-opacity-10 text-purple-500">
                                <i class="fas fa-eye text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-500 text-sm">今月の閲覧数</p>
                                <h3 class="text-2xl font-bold">1,254</h3>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-500 bg-opacity-10 text-yellow-500">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-500 text-sm">登録ユーザー</p>
                                <h3 class="text-2xl font-bold">24</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 最近の投稿 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- 最近の活動記録 -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b flex justify-between items-center">
                            <h3 class="text-lg font-semibold">最近の活動記録</h3>
                            <a href="activities.php" class="text-blue-600 hover:text-blue-800 text-sm">すべて表示</a>
                        </div>
                        <div class="p-4">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left text-gray-500 text-sm">
                                        <th class="pb-3 font-medium">タイトル</th>
                                        <th class="pb-3 font-medium">日付</th>
                                        <th class="pb-3 font-medium"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_activities_result && $recent_activities_result->num_rows > 0): ?>
                                        <?php while ($activity = $recent_activities_result->fetch_assoc()): ?>
                                            <tr class="border-t border-gray-100">
                                                <td class="py-3 text-sm">
                                                    <div class="line-clamp-1"><?php echo htmlspecialchars($activity['title']); ?></div>
                                                </td>
                                                <td class="py-3 text-sm text-gray-500">
                                                    <?php echo date('Y/m/d', strtotime($activity['created_at'])); ?>
                                                </td>
                                                <td class="py-3 text-sm">
                                                    <a href="activity_edit.php?id=<?php echo $activity['id']; ?>" class="text-blue-600 hover:text-blue-800">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="py-4 text-center text-gray-500">活動記録がありません</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 最近の技術ブログ -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b flex justify-between items-center">
                            <h3 class="text-lg font-semibold">最近の技術ブログ</h3>
                            <a href="blogs.php" class="text-blue-600 hover:text-blue-800 text-sm">すべて表示</a>
                        </div>
                        <div class="p-4">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left text-gray-500 text-sm">
                                        <th class="pb-3 font-medium">タイトル</th>
                                        <th class="pb-3 font-medium">カテゴリ</th>
                                        <th class="pb-3 font-medium">日付</th>
                                        <th class="pb-3 font-medium"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_blogs_result && $recent_blogs_result->num_rows > 0): ?>
                                        <?php while ($blog = $recent_blogs_result->fetch_assoc()): ?>
                                            <tr class="border-t border-gray-100">
                                                <td class="py-3 text-sm">
                                                    <div class="line-clamp-1"><?php echo htmlspecialchars($blog['title']); ?></div>
                                                </td>
                                                <td class="py-3 text-sm">
                                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                                        <?php echo htmlspecialchars($blog['category_id']); ?>
                                                    </span>
                                                </td>
                                                <td class="py-3 text-sm text-gray-500">
                                                    <?php echo date('Y/m/d', strtotime($blog['created_at'])); ?>
                                                </td>
                                                <td class="py-3 text-sm">
                                                    <a href="blog_edit.php?id=<?php echo $blog['id']; ?>" class="text-blue-600 hover:text-blue-800">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="py-4 text-center text-gray-500">技術ブログがありません</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- クイックアクション -->
                <div class="bg-white rounded-lg shadow mb-8">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-semibold">クイックアクション</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <a href="activity_add.php" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-200 transition-colors">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 mb-3">
                                <i class="fas fa-plus"></i>
                            </div>
                            <span class="text-sm font-medium">活動記録を追加</span>
                        </a>
                        <a href="blog_add.php" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-200 transition-colors">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600 mb-3">
                                <i class="fas fa-plus"></i>
                            </div>
                            <span class="text-sm font-medium">技術ブログを追加</span>
                        </a>
                        <a href="categories.php" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-200 transition-colors">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 mb-3">
                                <i class="fas fa-tags"></i>
                            </div>
                            <span class="text-sm font-medium">カテゴリを管理</span>
                        </a>
                        <a href="profile.php" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-yellow-50 hover:border-yellow-200 transition-colors">
                            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center text-yellow-600 mb-3">
                                <i class="fas fa-images"></i>
                            </div>
                            <span class="text-sm font-medium">プロフィールを管理</span>
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // モバイルメニュートグル
        document.querySelector('button.md\\:hidden').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('hidden');
        });
    </script>
</body>
</html>

