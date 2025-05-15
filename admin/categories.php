<?php
session_start();

// ログイン確認
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// データベース接続
require_once '../db.php';

// blog_categoriesテーブルが存在しない場合は作成
$check_table_query = "SHOW TABLES LIKE 'blog_categories'";
$table_exists = $conn->query($check_table_query)->num_rows > 0;

if (!$table_exists) {
    $create_table_query = "CREATE TABLE blog_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_name VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table_query) === TRUE) {
        // 既存のカテゴリをインポート
        $import_query = "INSERT IGNORE INTO blog_categories (category_name) 
                         SELECT DISTINCT category_id FROM techblog WHERE category_id IS NOT NULL AND category_id != ''";
        $conn->query($import_query);
    } else {
        $error_message = "カテゴリテーブルの作成に失敗しました: " . $conn->error;
    }
}

// 技術ブログのカテゴリ取得（カテゴリテーブルと記事数を結合）
$blog_categories_query = "
    SELECT bc.id, bc.category_name, COUNT(t.id) as count 
    FROM blog_categories bc
    LEFT JOIN techblog t ON bc.id = t.category_id
    GROUP BY bc.id, bc.category_name
    ORDER BY bc.category_name
";
$blog_categories_result = $conn->query($blog_categories_query);

// カテゴリ追加処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    
    if (!empty($category_name)) {
        // カテゴリテーブルに追加
        $insert_query = "INSERT INTO blog_categories (category_name) VALUES (?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("s", $category_name);
        
        if ($stmt->execute()) {
            $success_message = "新しいカテゴリ「" . htmlspecialchars($category_name) . "」を追加しました。";
            // ページをリロード
            header("Location: categories.php?success=1");
            exit();
        } else {
            // エラーの場合、既に存在するカテゴリかどうかを確認
            if ($conn->errno == 1062) { // Duplicate entry エラーコード
                $error_message = "そのカテゴリは既に存在します。";
            } else {
                $error_message = "カテゴリの追加に失敗しました: " . $conn->error;
            }
        }
    } else {
        $error_message = "カテゴリ名を入力してください。";
    }
}

// カテゴリ名変更処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rename_category'])) {
    $old_name = $_POST['old_category_name'];
    $new_name = trim($_POST['new_category_name']);
    
    if (!empty($new_name)) {
        // トランザクション開始
        $conn->begin_transaction();
        
        try {
            // カテゴリテーブルの更新
            $update_category_query = "UPDATE blog_categories SET category_name = ? WHERE category_name = ?";
            $stmt = $conn->prepare($update_category_query);
            $stmt->bind_param("ss", $new_name, $old_name);
            $stmt->execute();
            
            // 記事テーブルの更新
            $update_posts_query = "UPDATE techblog SET category_id = ? WHERE category_id = ?";
            $stmt = $conn->prepare($update_posts_query);
            $stmt->bind_param("ss", $new_name, $old_name);
            $stmt->execute();
            
            // コミット
            $conn->commit();
            
            $success_message = "カテゴリ名を「" . htmlspecialchars($old_name) . "」から「" . htmlspecialchars($new_name) . "」に変更しました。";
            // ページをリロード
            header("Location: categories.php?success=1");
            exit();
        } catch (Exception $e) {
            // ロールバック
            $conn->rollback();
            $error_message = "カテゴリ名の変更に失敗しました: " . $e->getMessage();
        }
    } else {
        $error_message = "新しいカテゴリ名を入力してください。";
    }
}

// カテゴリ削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $category_name = $_POST['delete_category_name'];
    
    if (!empty($category_name)) {
        // トランザクション開始
        $conn->begin_transaction();
        
        try {
            // カテゴリテーブルから削除
            $delete_category_query = "DELETE FROM blog_categories WHERE category_name = ?";
            $stmt = $conn->prepare($delete_category_query);
            $stmt->bind_param("s", $category_name);
            $stmt->execute();
            
            // 記事のカテゴリを空にする（記事自体は削除しない）
            $update_posts_query = "UPDATE techblog SET category_id = '' WHERE category_id = ?";
            $stmt = $conn->prepare($update_posts_query);
            $stmt->bind_param("s", $category_name);
            $stmt->execute();
            
            // コミット
            $conn->commit();
            
            $success_message = "カテゴリ「" . htmlspecialchars($category_name) . "」を削除しました。";
            // ページをリロード
            header("Location: categories.php?success=1");
            exit();
        } catch (Exception $e) {
            // ロールバック
            $conn->rollback();
            $error_message = "カテゴリの削除に失敗しました: " . $e->getMessage();
        }
    } else {
        $error_message = "カテゴリ名が指定されていません。";
    }
}

// 成功メッセージの取得
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = "操作が正常に完了しました。";
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>技術ブログカテゴリ管理 | Convivial Net</title>
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
        .transition-all {
            transition: all 0.3s ease;
        }
        .table-row-hover:hover {
            background-color: rgba(59, 130, 246, 0.05);
        }
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .btn-primary {
            background-color: #3b82f6;
            color: white;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #2563eb;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background-color: #e5e7eb;
            color: #4b5563;
            transition: all 0.2s ease;
        }
        .btn-secondary:hover {
            background-color: #d1d5db;
            transform: translateY(-1px);
        }
        .modal-content {
            transform: scale(0.95);
            opacity: 0;
            transition: all 0.2s ease;
        }
        .modal-active .modal-content {
            transform: scale(1);
            opacity: 1;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .badge-blue {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }
        .badge-green {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        .badge-yellow {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }
        .badge-red {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        .badge-purple {
            background-color: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }
        .badge-pink {
            background-color: rgba(236, 72, 153, 0.1);
            color: #ec4899;
        }
        .badge-indigo {
            background-color: rgba(99, 102, 241, 0.1);
            color: #6366f1;
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
                        <button id="sidebarToggle" class="text-gray-500 focus:outline-none md:hidden mr-3">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h2 class="text-xl font-semibold text-gray-800">技術ブログカテゴリ管理</h2>
                    </div>
                    <div class="flex items-center">
                        <a href="../techblog.php" class="text-blue-600 hover:text-blue-800 mr-4 flex items-center transition-all" target="_blank">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            <span>ブログを表示</span>
                        </a>
                        <div class="relative">
                            <button id="userMenuButton" class="flex items-center text-gray-700 focus:outline-none">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white shadow-md">
                                    <?php echo substr($_SESSION["username"], 0, 1); ?>
                                </div>
                                <span class="ml-2"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                                <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </button>
                            <div id="userMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden">
                                <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">ログアウト</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- コンテンツエリア -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- 成功・エラーメッセージ -->
                <?php if (isset($success_message)): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md shadow-sm flex items-start" role="alert">
                        <i class="fas fa-check-circle text-green-500 mr-3 mt-0.5"></i>
                        <div>
                            <p class="font-medium">成功</p>
                            <p><?php echo $success_message; ?></p>
                        </div>
                        <button class="ml-auto text-green-500 hover:text-green-700" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow-sm flex items-start" role="alert">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-0.5"></i>
                        <div>
                            <p class="font-medium">エラー</p>
                            <p><?php echo $error_message; ?></p>
                        </div>
                        <button class="ml-auto text-red-500 hover:text-red-700" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- ページヘッダー -->
                <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">カテゴリ管理</h1>
                        <p class="mt-1 text-sm text-gray-500">技術ブログのカテゴリを管理します</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <button onclick="showModal('addCategoryModal')" class="btn-primary px-4 py-2 rounded-md shadow-sm flex items-center">
                            <i class="fas fa-plus mr-2"></i>
                            <span>新しいカテゴリを追加</span>
                        </button>
                    </div>
                </div>

                <!-- タブナビゲーション -->
                <div class="mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8">
                            <button id="tab-blog" class="tab-btn py-4 px-1 border-b-2 border-blue-500 font-medium text-blue-600">
                                技術ブログカテゴリ
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- カテゴリ管理カード -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden card-hover">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">カテゴリ一覧</h3>
                            <p class="text-sm text-gray-500 mt-1">技術ブログで使用されているカテゴリの一覧です</p>
                        </div>
                        <div class="flex items-center">
                            <div class="relative mr-2">
                                <input type="text" id="categorySearch" placeholder="カテゴリを検索..." class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">カテゴリ名</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">記事数</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if ($blog_categories_result && $blog_categories_result->num_rows > 0): ?>
                                    <?php while ($category = $blog_categories_result->fetch_assoc()): ?>
                                        <tr class="table-row-hover">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-md flex items-center justify-center text-blue-600">
                                                        <i class="fas fa-folder"></i>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($category['category_name']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?php echo $category['count']; ?> 記事</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <button 
                                                        onclick="showRenameModal('<?php echo addslashes(htmlspecialchars($category['category_name'])); ?>')" 
                                                        class="text-blue-600 hover:text-blue-900 transition-all"
                                                        title="カテゴリ名を変更"
                                                    >
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a 
                                                        href="blogs.php?search=&category_id=<?php echo urlencode($category['id']); ?>" 
                                                        class="text-gray-600 hover:text-gray-900 transition-all"
                                                        title="このカテゴリの記事を表示"
                                                    >
                                                        <i class="fas fa-search"></i>
                                                    </a>
                                                    <button 
                                                        class="text-red-600 hover:text-red-900 transition-all"
                                                        title="カテゴリを削除"
                                                        onclick="confirmDelete('<?php echo addslashes(htmlspecialchars($category['category_name'])); ?>')"
                                                    >
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                            <div class="py-8">
                                                <i class="fas fa-folder-open text-gray-300 text-5xl mb-4"></i>
                                                <p>カテゴリがありません</p>
                                                <button onclick="showModal('addCategoryModal')" class="mt-4 px-4 py-2 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition-all">
                                                    カテゴリを追加する
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-500">
                                <?php 
                                    $total_categories = $blog_categories_result ? $blog_categories_result->num_rows : 0;
                                    echo $total_categories . ' カテゴリ';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 使用状況カード -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-folder text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">カテゴリ数</h3>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $total_categories; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-newspaper text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">総記事数</h3>
                                <?php
                                    $total_posts_query = "SELECT COUNT(*) as count FROM techblog";
                                    $total_posts_result = $conn->query($total_posts_query);
                                    $total_posts = $total_posts_result ? $total_posts_result->fetch_assoc()['count'] : 0;
                                ?>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $total_posts; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <i class="fas fa-chart-pie text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">平均記事数</h3>
                                <?php
                                    $avg_posts = $total_categories > 0 ? round($total_posts / $total_categories, 1) : 0;
                                ?>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $avg_posts; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- カテゴリ追加モーダル -->
    <div id="addCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 modal-container">
        <div class="modal-content bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">新しいカテゴリを追加</h3>
                <button type="button" onclick="hideModal('addCategoryModal')" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST">
                <div class="mb-4">
                    <label for="category_name" class="block text-sm font-medium text-gray-700 mb-1">カテゴリ名</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-folder text-gray-400"></i>
                        </div>
                        <input 
                            type="text" 
                            id="category_name" 
                            name="category_name" 
                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="新しいカテゴリ名を入力"
                            required
                        >
                    </div>
                    <p class="mt-1 text-xs text-gray-500">例: プログラミング、デザイン、インフラなど</p>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideModal('addCategoryModal')" class="btn-secondary px-4 py-2 rounded-md">
                        キャンセル
                    </button>
                    <button type="submit" name="add_category" class="btn-primary px-4 py-2 rounded-md">
                        <i class="fas fa-plus mr-1"></i> 追加する
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- カテゴリ名変更モーダル -->
    <div id="renameCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 modal-container">
        <div class="modal-content bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">カテゴリ名を変更</h3>
                <button type="button" onclick="hideModal('renameCategoryModal')" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" id="old_category_name" name="old_category_name" value="">
                <div class="mb-4">
                    <label for="new_category_name" class="block text-sm font-medium text-gray-700 mb-1">新しいカテゴリ名</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-edit text-gray-400"></i>
                        </div>
                        <input 
                            type="text" 
                            id="new_category_name" 
                            name="new_category_name" 
                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideModal('renameCategoryModal')" class="btn-secondary px-4 py-2 rounded-md">
                        キャンセル
                    </button>
                    <button type="submit" name="rename_category" class="btn-primary px-4 py-2 rounded-md">
                        <i class="fas fa-check mr-1"></i> 変更する
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 削除確認モーダル -->
    <div id="deleteCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 modal-container">
        <div class="modal-content bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">カテゴリを削除</h3>
                <button type="button" onclick="hideModal('deleteCategoryModal')" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-4">
                <div class="bg-red-50 text-red-600 p-4 rounded-md mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium">警告</h3>
                            <div class="mt-2 text-sm">
                                <p>このカテゴリを削除すると、関連する記事のカテゴリ情報が失われます。この操作は元に戻せません。</p>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-gray-700"><span id="deleteTargetName" class="font-semibold"></span> カテゴリを削除してもよろしいですか？</p>
            </div>
            <form method="POST">
                <input type="hidden" id="delete_category_name" name="delete_category_name" value="">
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideModal('deleteCategoryModal')" class="btn-secondary px-4 py-2 rounded-md">
                        キャンセル
                    </button>
                    <button type="submit" name="delete_category" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-all">
                        <i class="fas fa-trash-alt mr-1"></i> 削除する
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // ユーザーメニュートグル
    const userMenuButton = document.getElementById('userMenuButton');
    const userMenu = document.getElementById('userMenu');
    
    // メニューボタンをクリックしたときの処理
    userMenuButton.addEventListener('click', function(event) {
        // イベントの伝播を停止（これがないと、documentのクリックイベントも発火してしまう）
        event.stopPropagation();
        
        // メニューの表示/非表示を切り替え
        userMenu.classList.toggle('hidden');
    });
    
    // ドキュメント全体のクリックを検知
    document.addEventListener('click', function(event) {
        // クリックされた要素がメニュー内部でない場合
        if (!userMenu.contains(event.target) && !userMenuButton.contains(event.target)) {
            // メニューを非表示にする
            userMenu.classList.add('hidden');
        }
    });

    // ドロップダウンメニュー
    document.querySelectorAll('.dropdown').forEach(dropdown => {
        const button = dropdown.querySelector('button');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        button.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });
    });

    // モーダル表示
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('modal-active');
        }, 10);
    }

    // モーダル非表示
    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.remove('modal-active');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }

    // カテゴリ名変更モーダル表示
    function showRenameModal(categoryName) {
        document.getElementById('old_category_name').value = categoryName;
        document.getElementById('new_category_name').value = categoryName;
        showModal('renameCategoryModal');
    }

    // カテゴリ削除確認モーダル表示
    function confirmDelete(categoryName) {
        document.getElementById('delete_category_name').value = categoryName;
        document.getElementById('deleteTargetName').textContent = categoryName;
        showModal('deleteCategoryModal');
    }

    // カテゴリ検索機能
    document.getElementById('categorySearch').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('tbody tr');
        
        tableRows.forEach(row => {
            const categoryName = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
            
            if (categoryName.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // モーダル外クリックで閉じる
    document.querySelectorAll('.modal-container').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                const modalId = modal.getAttribute('id');
                hideModal(modalId);
            }
        });
    });

    // 通知メッセージの自動非表示
    setTimeout(() => {
        const alerts = document.querySelectorAll('[role="alert"]');
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        });
    }, 5000);
</script>
</body>
</html>