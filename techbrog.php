<?php
// データベース接続
require 'db.php';

// カテゴリとタグのフィルタリング
$categoryFilter = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// ページネーション設定
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// クエリの構築
$sql = "SELECT t.id, t.title, t.description, t.category_id, t.created_at, bc.category_name 
        FROM tecblog t 
        LEFT JOIN blog_categories bc ON t.category_id = bc.id 
        WHERE 1=1";

if (!empty($categoryFilter) && $categoryFilter !== 'all') {
    $sql .= " AND t.category_id = '" . $conn->real_escape_string($categoryFilter) . "'";
}

if (!empty($searchQuery)) {
    $sql .= " AND (t.title LIKE '%" . $conn->real_escape_string($searchQuery) . "%' 
              OR t.description LIKE '%" . $conn->real_escape_string($searchQuery) . "%'
              OR t.content LIKE '%" . $conn->real_escape_string($searchQuery) . "%')";
}

$count_sql = $sql;
$sql .= " ORDER BY t.created_at DESC LIMIT $offset, $records_per_page";

// クエリの実行
$result = $conn->query($sql);

// 総記事数の取得
$count_result = $conn->query($count_sql);
$total_records = $count_result ? $count_result->num_rows : 0;
$total_pages = ceil($total_records / $records_per_page);

// カテゴリーの取得（記事数も含む）
$categoriesQuery = "SELECT bc.id, bc.category_name, COUNT(t.id) as post_count 
                   FROM blog_categories bc
                   LEFT JOIN tecblog t ON bc.id = t.category_id
                   GROUP BY bc.id, bc.category_name
                   ORDER BY bc.category_name";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
if ($categoriesResult) {
    while ($category = $categoriesResult->fetch_assoc()) {
        $categories[] = $category;
    }
}

// 最新記事の取得（サイドバー用）
$latesttecblogQuery = "SELECT t.id, t.title, t.created_at, bc.category_name 
                      FROM tecblog t
                      LEFT JOIN blog_categories bc ON t.category_id = bc.id
                      ORDER BY t.created_at DESC LIMIT 5";
$latesttecblogResult = $conn->query($latesttecblogQuery);
$latesttecblog = [];
if ($latesttecblogResult) {
    while ($post = $latesttecblogResult->fetch_assoc()) {
        $latesttecblog[] = $post;
    }
}

// ヘッダーの読み込み
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>技術ブログ | Convivial Net</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* ダークモードのコードブロック */
        pre {
            border-radius: 0.5rem;
            margin: 1rem 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.875rem;
        }
        
        :not(pre) > code {
            background-color: #f1f5f9;
            padding: 0.125rem 0.25rem;
            border-radius: 0.25rem;
            color: #0f172a;
        }
        
        /* サイドバー */
        .sidebar-sticky {
            position: sticky;
            top: 2rem;
        }
        
        /* テーブルの行 */
        .table-row {
            transition: background-color 0.2s;
        }
        
        .table-row:hover {
            background-color: #f9fafb;
        }
        
        /* ヘッダーグラデーション */
        .header-gradient {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }
        
        /* 検索バーのプレースホルダー色 */
        .search-input::placeholder {
            color: #93c5fd;
            opacity: 0.8;
        }
        
        /* 検索ボタンのホバーエフェクト */
        .search-button {
            transition: all 0.2s ease;
        }
        
        .search-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- 目立つヘッダーセクション -->
    <div class="header-gradient text-white shadow-lg">
        <div class="container mx-auto px-4 py-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="text-center md:text-left mb-4 md:mb-0">
                    <h1 class="text-3xl md:text-4xl font-bold">技術ブログ</h1>
                    <p class="text-blue-100 text-lg">最新の技術情報とチュートリアル</p>
                </div>
                
                <!-- 小さい検索バー -->
                <div class="w-full md:w-auto">
                    <form action="tecblog.php" method="GET" class="relative flex">
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="記事を検索..." 
                            class="w-full md:w-64 px-4 py-2 pr-10 bg-blue-700/30 border border-blue-400 rounded-lg text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-white/50 search-input"
                            value="<?php echo htmlspecialchars($searchQuery); ?>"
                        >
                        <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- 装飾的な波形の区切り -->
        <div class="relative h-16">
            <svg class="absolute bottom-0 w-full h-16 text-gray-50" preserveAspectRatio="none" viewBox="0 0 1440 54">
                <path fill="currentColor" d="M0 22L120 16.7C240 11 480 1.00001 720 0.700012C960 1.00001 1200 11 1320 16.7L1440 22V54H1320C1200 54 960 54 720 54C480 54 240 54 120 54H0V22Z"></path>
            </svg>
        </div>
    </div>

    <main class="container mx-auto px-4 py-6 -mt-6 relative z-10">
        <!-- 検索結果表示 -->
        <?php if (!empty($searchQuery)): ?>
        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-md">
            <p class="text-blue-800">
                <span class="font-medium">"<?php echo htmlspecialchars($searchQuery); ?>"</span> の検索結果: 
                <?php echo $total_records; ?> 件の記事が見つかりました
            </p>
        </div>
        <?php endif; ?>
        
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- サイドバー（左側に配置） -->
            <div class="w-full lg:w-1/4">
                <div class="sidebar-sticky space-y-6">
                    <!-- カテゴリ一覧 -->
                    <div class="bg-white border border-gray-200 rounded-md overflow-hidden shadow-sm">
                        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                            <h3 class="font-semibold text-gray-800">カテゴリ一覧</h3>
                        </div>
                        <div class="p-4">
                            <ul class="space-y-2">
                                <li>
                                    <a href="tecblog.php" class="flex justify-between items-center px-3 py-2 rounded-md <?php echo empty($categoryFilter) ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'; ?>">
                                        <span>すべてのカテゴリ</span>
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full"><?php echo $total_records; ?></span>
                                    </a>
                                </li>
                                <?php foreach ($categories as $category): ?>
                                <li>
                                    <a href="?category_id=<?php echo urlencode($category['id']); ?>" class="flex justify-between items-center px-3 py-2 rounded-md <?php echo ($categoryFilter === $category['id']) ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'; ?>">
                                        <span><?php echo htmlspecialchars($category['category_name']); ?></span>
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full"><?php echo $category['post_count']; ?></span>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- 最新記事 -->
                    <div class="bg-white border border-gray-200 rounded-md overflow-hidden shadow-sm">
                        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                            <h3 class="font-semibold text-gray-800">最新記事</h3>
                        </div>
                        <div class="p-4">
                            <ul class="divide-y divide-gray-100">
                                <?php foreach ($latesttecblog as $post): ?>
                                <li class="py-2">
                                    <a href="techbrog_detail.php?id=<?php echo $post['id']; ?>" class="block">
                                        <p class="text-sm font-medium text-gray-800 line-clamp-2">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </p>
                                        <div class="flex items-center mt-1">
                                            <span class="text-xs text-gray-500">
                                                <?php echo date('Y年m月d日', strtotime($post['created_at'])); ?>
                                            </span>
                                            <span class="mx-2 text-gray-300">•</span>
                                            <span class="text-xs text-blue-600">
                                                <?php echo htmlspecialchars($post['category_name'] ?? '未分類'); ?>
                                            </span>
                                        </div>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- お知らせ -->
                    <div class="bg-white border border-gray-200 rounded-md overflow-hidden shadow-sm">
                        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                            <h3 class="font-semibold text-gray-800">お知らせ</h3>
                        </div>
                        <div class="p-4">
                            <div class="text-sm text-gray-600">
                                <p class="mb-3">技術ブログの最新情報をお届けします。定期的にチェックして、新しい記事をお見逃しなく。</p>
                                <p>ご質問やフィードバックがありましたら、お気軽にお問い合わせください。</p>
                            </div>
                            <div class="mt-4">
                                <a href="contact.php" class="block w-full py-2 px-4 bg-blue-600 text-white text-center rounded-md text-sm">
                                    お問い合わせ
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- メインコンテンツ -->
            <div class="w-full lg:w-3/4">
                <!-- 記事一覧（管理画面風テーブルレイアウト） -->
                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="bg-white border border-gray-200 rounded-md overflow-hidden shadow-sm">
                        <div class="border-b border-gray-200 px-6 py-4 bg-gray-50 flex justify-between items-center">
                            <h2 class="font-semibold text-gray-800">記事一覧</h2>
                            <div class="text-sm text-gray-500">
                                全 <?php echo $total_records; ?> 件中 <?php echo ($page - 1) * $records_per_page + 1; ?>-<?php echo min($page * $records_per_page, $total_records); ?> 件表示
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            タイトル
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            カテゴリ
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            投稿日
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="table-row hover:bg-gray-50 cursor-pointer" onclick="window.location='techbrog_detail.php?id=<?php echo $row['id']; ?>'">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900 line-clamp-2">
                                                <?php echo htmlspecialchars($row['title']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500 line-clamp-2 mt-1">
                                                <?php echo htmlspecialchars($row['description']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                <?php echo htmlspecialchars($row['category_name'] ?? '未分類'); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('Y年m月d日', strtotime($row['created_at'])); ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- ページネーション -->
                    <?php if ($total_pages > 1): ?>
                    <div class="flex justify-center mt-6">
                        <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo !empty($categoryFilter) ? '&category_id=' . urlencode($categoryFilter) : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">前へ</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <?php else: ?>
                            <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                <span class="sr-only">前へ</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <?php endif; ?>
                            
                            <?php
                            // ページネーションの表示範囲を決定
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            // 最初のページへのリンク（必要な場合）
                            if ($start_page > 1): 
                            ?>
                            <a href="?page=1<?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo !empty($categoryFilter) ? '&category_id=' . urlencode($categoryFilter) : ''; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                1
                            </a>
                            <?php if ($start_page > 2): ?>
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                ...
                            </span>
                            <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <?php if ($i == $page): ?>
                            <span aria-current="page" class="relative inline-flex items-center px-4 py-2 border border-blue-500 bg-blue-50 text-sm font-medium text-blue-600">
                                <?php echo $i; ?>
                            </span>
                            <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo !empty($categoryFilter) ? '&category_id=' . urlencode($categoryFilter) : ''; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <?php echo $i; ?>
                            </a>
                            <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php 
                            // 最後のページへのリンク（必要な場合）
                            if ($end_page < $total_pages): 
                            ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                ...
                            </span>
                            <?php endif; ?>
                            <a href="?page=<?php echo $total_pages; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo !empty($categoryFilter) ? '&category_id=' . urlencode($categoryFilter) : ''; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <?php echo $total_pages; ?>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo !empty($categoryFilter) ? '&category_id=' . urlencode($categoryFilter) : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">次へ</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <?php else: ?>
                            <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                <span class="sr-only">次へ</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <?php endif; ?>
                        </nav>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="bg-white border border-gray-200 rounded-md p-12 text-center shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h2 class="text-xl font-bold text-gray-700 mb-2">記事が見つかりませんでした</h2>
                        <p class="text-gray-500 mb-6">検索条件を変更して、もう一度お試しください。</p>
                        <a href="tecblog.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md">
                            すべての記事を表示
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
</body>
</html>
