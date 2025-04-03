<?php
// データベース接続
require 'db.php';

// カテゴリとタグのフィルタリング
$categoryFilter = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// クエリの構築
$sql = "SELECT id, title, description, image_data, image_type, category_id, created_at FROM tecblog WHERE 1=1";

if (!empty($categoryFilter) && $categoryFilter !== 'all') {
    $sql .= " AND category_id = '" . $conn->real_escape_string($categoryFilter) . "'";
}

if (!empty($searchQuery)) {
    $sql .= " AND (title LIKE '%" . $conn->real_escape_string($searchQuery) . "%' 
              OR description LIKE '%" . $conn->real_escape_string($searchQuery) . "%'
              OR content LIKE '%" . $conn->real_escape_string($searchQuery) . "%')";
}

$sql .= " ORDER BY created_at DESC";

// クエリの実行
$result = $conn->query($sql);

// カテゴリーの取得
$categoriesQuery = "SELECT DISTINCT category_id FROM tecblog ORDER BY category_id";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
if ($categoriesResult) {
    while ($category_id = $categoriesResult->fetch_assoc()) {
        $categories[] = $category_id['category_id'];
    }
}

// 最新記事の取得（サイドバー用）
$latesttecblogQuery = "SELECT id, title, created_at FROM tecblog ORDER BY created_at DESC LIMIT 5";
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
    <title>技術ブログ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
    <style>
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
        
        /* カスタムアニメーション */
        .hover-lift {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        /* カスタムグラデーション */
        .tech-gradient {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
        }
        
        .post-day {
            position: relative;
        }
        
        .post-day::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            background-color: #ef4444;
            border-radius: 50%;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- ヒーローセクション -->
    <div class="tech-gradient text-white py-16 mb-8">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">技術ブログ</h1>
                <p class="text-xl text-blue-100 mb-8">最新の技術トレンド、チュートリアル、ベストプラクティスを紹介します</p>
                
                <!-- 検索バー -->
                <div class="bg-white/20 backdrop-blur-sm rounded-lg p-2 max-w-xl mx-auto">
                    <form action="tecblog.php" method="GET" class="flex gap-2">
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="記事を検索..." 
                            class="flex-grow px-4 py-3 bg-white/90 border-0 rounded-md focus:outline-none focus:ring-2 focus:ring-white"
                            value="<?php echo htmlspecialchars($searchQuery); ?>"
                        >
                        <button type="submit" class="px-6 py-3 bg-white text-blue-600 font-medium rounded-md hover:bg-blue-50 transition-colors">
                            検索
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <main class="container mx-auto px-4 pb-16">
        <!-- カテゴリフィルター -->
        <?php if (!empty($categories) || !empty($allTags)): ?>
        <div class="mb-8">
            <div class="flex flex-wrap gap-2 items-center">
                <span class="text-sm font-medium text-gray-700">カテゴリ:</span>
                <a href="tecblog.php" class="px-3 py-1 text-sm rounded-md <?php echo empty($categoryFilter) ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'; ?> transition-colors">
                    すべて
                </a>
                <?php foreach ($categories as $category): ?>
                <a href="?category=<?php echo urlencode($category); ?>" class="px-3 py-1 text-sm rounded-md <?php echo ($categoryFilter === $category) ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'; ?> transition-colors">
                    <?php echo htmlspecialchars($category); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- 検索結果表示 -->
        <?php if (!empty($searchQuery)): ?>
        <div class="mb-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-md">
            <p class="text-blue-800">
                <span class="font-medium">"<?php echo htmlspecialchars($searchQuery); ?>"</span> の検索結果: 
                <?php echo $result ? $result->num_rows : 0; ?> 件の記事が見つかりました
            </p>
        </div>
        <?php endif; ?>
        
        <div class="flex flex-col md:flex-row gap-8">
            
            <!-- メインコンテンツ -->
            <div class="w-full md:w-3/4">
                <!-- 記事一覧 -->
                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="grid gap-6 md:grid-cols-2">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                                // タグの取得と整形
                                $tags = explode(',', $row['tags'] ?? '');
                                $tags = array_map('trim', $tags);
                                $tags = array_filter($tags);
                            ?>
                            <article class="bg-white rounded-lg shadow-md overflow-hidden hover-lift">
                                <a href="techbrog_detail.php?id=<?php echo $row['id']; ?>" class="block">
                                    <div class="aspect-[16/9] relative">
                                        <?php if (!empty($row['image_data'])): ?>
                                            <img 
                                                src="image.php?id=<?php echo $row['id']; ?>" 
                                                alt="<?php echo htmlspecialchars($row['title']); ?>"
                                                class="w-full h-full object-cover"
                                            >
                                        <?php else: ?>
                                            <div class="w-full h-full tech-gradient flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                        <div class="absolute top-3 left-3">
                                            <span class="px-2.5 py-1 text-xs font-medium bg-blue-600 text-white rounded-md">
                                                <?php echo htmlspecialchars($row['category_id']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                                <div class="p-6">
                                    <div class="flex flex-wrap items-center gap-2 mb-3">
                                        <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                                            <a href="?tag=<?php echo urlencode($tag); ?>" class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 rounded-full hover:bg-gray-200 transition-colors">
                                                <?php echo htmlspecialchars($tag); ?>
                                            </a>
                                        <?php endforeach; ?>
                                        <?php if (count($tags) > 3): ?>
                                            <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-500 rounded-full">
                                                +<?php echo count($tags) - 3; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <h2 class="text-xl font-bold mb-2 line-clamp-2">
                                        <a href="techbrog_detail.php?id=<?php echo $row['id']; ?>" class="hover:text-blue-600 transition-colors">
                                            <?php echo htmlspecialchars($row['title']); ?>
                                        </a>
                                    </h2>
                                    
                                    <p class="line-clamp-3 mb-4 text-gray-600">
                                        <?php echo htmlspecialchars($row['description']); ?>
                                    </p>
                                    
                                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                        <time datetime="<?php echo date('Y-m-d', strtotime($row['created_at'])); ?>" class="text-sm text-gray-500">
                                            <?php echo date('Y年m月d日', strtotime($row['created_at'])); ?>
                                        </time>
                                        
                                        <a href="techbrog_detail.php?id=<?php echo $row['id']; ?>" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors">
                                            続きを読む
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                    
                    <!-- ページネーション -->
                    <div class="flex justify-center mt-12">
                        <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">前へ</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a href="#" aria-current="page" class="relative inline-flex items-center px-4 py-2 border border-blue-500 bg-blue-50 text-sm font-medium text-blue-600">
                                1
                            </a>
                            <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                2
                            </a>
                            <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                3
                            </a>
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                ...
                            </span>
                            <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">次へ</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </nav>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-lg shadow-md p-12 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h2 class="text-xl font-bold text-gray-700 mb-2">記事が見つかりませんでした</h2>
                        <p class="text-gray-500 mb-6">検索条件を変更して、もう一度お試しください。</p>
                        <a href="tecblog.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
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

