<?php
session_start();

// ログイン確認
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// データベース接続
require_once '../db.php';

// ページネーション設定
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// 検索条件
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';

// クエリの構築
$sql = "SELECT t.id, t.title, t.description, t.category_id, t.created_at, bc.category_name 
        FROM tecblog t 
        LEFT JOIN blog_categories bc ON t.category_id = bc.id 
        WHERE 1=1";
$count_sql = "SELECT COUNT(*) as total FROM tecblog t WHERE 1=1";

if (!empty($search)) {
    $search_term = '%' . $conn->real_escape_string($search) . '%';
    $sql .= " AND (t.title LIKE '$search_term' OR t.description LIKE '$search_term')";
    $count_sql .= " AND (t.title LIKE '$search_term' OR t.description LIKE '$search_term')";
}

if (!empty($category_id)) {
    $sql .= " AND t.category_id = '" . $conn->real_escape_string($category_id) . "'";
    $count_sql .= " AND t.category_id = '" . $conn->real_escape_string($category_id) . "'";
}

$sql .= " ORDER BY t.created_at DESC LIMIT $offset, $records_per_page";

// クエリの実行
$result = $conn->query($sql);
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// カテゴリの取得（blog_categoriesテーブルから取得）
$categories_query = "SELECT id, category_name FROM blog_categories ORDER BY category_name";
$categories_result = $conn->query($categories_query);
$categories = [];
if ($categories_result) {
    while ($cat = $categories_result->fetch_assoc()) {
        $categories[] = $cat;
    }
}

// 削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    $delete_sql = "DELETE FROM tecblog WHERE id = $delete_id";
    if ($conn->query($delete_sql)) {
        $success_message = "技術ブログ記事を削除しました。";
        // 現在のページにリダイレクト（GETパラメータを維持）
        $redirect_url = $_SERVER['PHP_SELF'];
        if (!empty($_GET)) {
            $redirect_url .= '?' . http_build_query($_GET);
        }
        header("Location: $redirect_url");
        exit();
    } else {
        $error_message = "削除に失敗しました: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>技術ブログ管理 | Convivial Net</title>
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
        .line-clamp-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
    <?php include 'includes/sidebar.php'; ?>
        <!-- メインコンテンツ -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- ヘッダー -->
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center">
                        <button class="text-gray-500 focus:outline-none md:hidden mr-3">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h2 class="text-xl font-semibold text-gray-800">技術ブログ管理</h2>
                    </div>
                    <div class="flex items-center">
                        <a href="../tech-blog/index.php" class="text-blue-600 hover:text-blue-800 mr-4" target="_blank">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            ブログを表示
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
                <!-- 成功・エラーメッセージ -->
                <?php if (isset($success_message)): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p><?php echo $success_message; ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p><?php echo $error_message; ?></p>
                    </div>
                <?php endif; ?>

                <!-- 検索・フィルターエリア -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-semibold">検索とフィルター</h3>
                    </div>
                    <div class="p-4">
                        <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">キーワード検索</label>
                                <input 
                                    type="text" 
                                    id="search" 
                                    name="search" 
                                    value="<?php echo htmlspecialchars($search); ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="タイトルまたは説明で検索..."
                                >
                            </div>
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">カテゴリ</label>
                                <select 
                                    id="category_id" 
                                    name="category_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">すべてのカテゴリ</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-search mr-2"></i>検索
                                </button>
                                <a href="blogs.php" class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                                    リセット
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 技術ブログ一覧 -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h3 class="text-lg font-semibold">技術ブログ一覧</h3>
                        <a href="blog_add.php" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>新規追加
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 text-left text-gray-500 text-sm">
                                    <th class="p-4 font-medium">ID</th>
                                    <th class="p-4 font-medium">タイトル</th>
                                    <th class="p-4 font-medium">カテゴリ</th>
                                    <th class="p-4 font-medium">作成日</th>
                                    <th class="p-4 font-medium">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr class="border-t border-gray-100 hover:bg-gray-50">
                                            <td class="p-4 text-sm"><?php echo $row['id']; ?></td>
                                            <td class="p-4 text-sm">
                                                <div class="line-clamp-1 max-w-xs"><?php echo htmlspecialchars($row['title']); ?></div>
                                            </td>
                                            <td class="p-4 text-sm">
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                                    <?php echo htmlspecialchars($row['category_name'] ?? '未分類'); ?>
                                                </span>
                                            </td>
                                            
                                            <td class="p-4 text-sm text-gray-500">
                                                <?php echo date('Y/m/d', strtotime($row['created_at'])); ?>
                                            </td>
                                            <td class="p-4 text-sm">
                                                <div class="flex space-x-2">
                                                    <a href="../techbrog_detail.php?id=<?php echo $row['id']; ?>" class="text-gray-600 hover:text-gray-900" target="_blank" title="表示">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="blog_edit.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:text-blue-800" title="編集">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button 
                                                        onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes(htmlspecialchars($row['title'])); ?>')" 
                                                        class="text-red-600 hover:text-red-800" 
                                                        title="削除"
                                                    >
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="p-4 text-center text-gray-500">技術ブログ記事がありません</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ページネーション -->
                    <?php if ($total_pages > 1): ?>
                    <div class="p-4 border-t flex justify-center">
                        <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category_id) ? '&category_id=' . urlencode($category_id) : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">前へ</span>
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php else: ?>
                                <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                    <span class="sr-only">前へ</span>
                                    <i class="fas fa-chevron-left"></i>
                                </span>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span aria-current="page" class="relative inline-flex items-center px-4 py-2 border border-blue-500 bg-blue-50 text-sm font-medium text-blue-600">
                                        <?php echo $i; ?>
                                    </span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category_id) ? '&category_id=' . urlencode($category_id) : ''; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category_id) ? '&category_id=' . urlencode($category_id) : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">次へ</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                    <span class="sr-only">次へ</span>
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                            <?php endif; ?>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- 削除確認モーダル -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-bold mb-4">削除の確認</h3>
            <p class="mb-4">以下の技術ブログ記事を削除してもよろしいですか？</p>
            <p id="deleteItemTitle" class="font-medium text-red-600 mb-6"></p>
            <div class="flex justify-end space-x-3">
                <button id="cancelDelete" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                    キャンセル
                </button>
                <form id="deleteForm" method="POST">
                    <input type="hidden" id="delete_id" name="delete_id" value="">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        削除する
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // 削除確認モーダル
        function confirmDelete(id, title) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteItemTitle').textContent = title;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        document.getElementById('cancelDelete').addEventListener('click', function() {
            document.getElementById('deleteModal').classList.add('hidden');
        });
    </script>
</body>
</html>