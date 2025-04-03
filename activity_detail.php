<?php
// データベース接続
require 'db.php';

// IDの取得と検証
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// 記事データの取得
$stmt = $conn->prepare("SELECT id, title, description, content, content_html, image_data, image_type, created_at FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$post = $result->fetch_assoc();
$stmt->close();

// // 関連記事の取得
// $relatedPosts = [];
// if (!empty($tags)) {
//     $tagList = implode("','", array_map(function($tag) use ($conn) {
//         return $conn->real_escape_string($tag);
//     }, $tags));

//     $relatedSql = "SELECT id, title, image_data, image_type, created_at FROM posts 
//                 WHERE id != ? AND (category = ? OR tags LIKE ?) 
//                 ORDER BY created_at DESC LIMIT 3";
//     $stmt = $conn->prepare($relatedSql);
//     $categoryParam = $post['category'];
//     $tagParam = '%' . implode('%', $tags) . '%';
//     $stmt->bind_param("iss", $id, $categoryParam, $tagParam);
//     $stmt->execute();
//     $relatedResult = $stmt->get_result();

//     if ($relatedResult) {
//         while ($related = $relatedResult->fetch_assoc()) {
//             $relatedPosts[] = $related;
//         }
//     }
//     $stmt->close();
// }

// 最新記事の取得（サイドバー用）
$latestPostsQuery = "SELECT id, title, created_at FROM posts WHERE id != ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($latestPostsQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$latestPostsResult = $stmt->get_result();
$latestPosts = [];
if ($latestPostsResult) {
    while ($latestPost = $latestPostsResult->fetch_assoc()) {
        $latestPosts[] = $latestPost;
    }
}
$stmt->close();

// ヘッダーの読み込み
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> | 技術ブログ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
    <style>
        /* コードブロックのスタイル */
        pre {
            border-radius: 0.5rem;
            margin: 1.5rem 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.9rem;
        }
        
        :not(pre) > code {
            background-color: #f1f5f9;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
            color: #0f172a;
            font-weight: 500;
        }
        
        /* 記事本文のスタイル */
        .article-content h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-top: 2.5rem;
            margin-bottom: 1.25rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
            color: #1e293b;
        }
        
        .article-content h3 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: #1e293b;
        }
        
        .article-content p {
            margin-bottom: 1.25rem;
            line-height: 1.8;
            color: #334155;
        }
        
        .article-content ul, .article-content ol {
            margin-bottom: 1.25rem;
            padding-left: 1.75rem;
        }
        
        .article-content ul {
            list-style-type: disc;
        }
        
        .article-content ol {
            list-style-type: decimal;
        }
        
        .article-content li {
            margin-bottom: 0.625rem;
            line-height: 1.7;
            color: #334155;
        }
        
        .article-content a {
            color: #2563eb;
            text-decoration: underline;
            text-decoration-thickness: 1px;
            text-underline-offset: 2px;
            transition: color 0.2s;
        }
        
        .article-content a:hover {
            color: #1d4ed8;
        }
        
        .article-content blockquote {
            border-left: 4px solid #3b82f6;
            padding: 1rem 1.5rem;
            margin: 1.5rem 0;
            background-color: #f8fafc;
            border-radius: 0.375rem;
            font-style: italic;
            color: #4b5563;
        }
        
        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            margin: 2rem auto;
            display: block;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .article-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .article-content th, .article-content td {
            border: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
        }
        
        .article-content th {
            background-color: #f1f5f9;
            font-weight: 600;
            text-align: left;
            color: #1e293b;
        }
        
        .article-content tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* ヒーローセクション */
        .hero-gradient {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.8), rgba(79, 70, 229, 0.8));
        }
        
        /* カスタムスクロールバー */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 20px;
        }
        
        /* アニメーション */
        .hover-lift {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        /* 目次のアクティブ状態 */
        .toc-link.active {
            color: #2563eb;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- ヒーローセクション -->
    <?php if (!empty($post['image_data'])): ?>
    <div class="relative h-64 md:h-96 overflow-hidden">
        <img 
            src="../image.php?id=<?php echo $post['id']; ?>" 
            alt="<?php echo htmlspecialchars($post['title']); ?>"
            class="w-full h-full object-cover"
        >
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center px-4">
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4 max-w-4xl mx-auto">
                    <?php echo htmlspecialchars($post['title']); ?>
                </h1>
                
                <div class="text-white text-sm md:text-base">
                    <time datetime="<?php echo date('Y-m-d', strtotime($post['created_at'])); ?>">
                        <?php echo date('Y年m月d日', strtotime($post['created_at'])); ?>
                    </time>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <main class="container mx-auto px-4 py-8">
        <!-- Breadcrumb navigation -->
        <nav class="flex mb-6 text-sm">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="../home.php" class="text-gray-600 hover:text-gray-900">ホーム</a>
                </li>
                <li class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-gray-400">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                    <a href="index.php" class="text-gray-600 hover:text-gray-900">活動記録</a>
                </li>
                <li class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-gray-400">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                    <span class="text-gray-900 font-medium"><?php echo htmlspecialchars($post['title']); ?></span>
                </li>
            </ol>
        </nav>
        
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- 記事本文 -->
            <div class="w-full lg:w-2/3">
                <?php if (empty($post['image_data'])): ?>
                <div class="mb-6">
                    <h1 class="text-3xl md:text-4xl font-bold mb-4"><?php echo htmlspecialchars($post['title']); ?></h1>
                    
                    <div class="flex items-center text-gray-600">
                        <time datetime="<?php echo date('Y-m-d', strtotime($post['created_at'])); ?>">
                            <?php echo date('Y年m月d日', strtotime($post['created_at'])); ?>
                        </time>
                    </div>
                </div>
                <?php endif; ?>
                
                <article class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 md:p-8">
                        <?php if (!empty($post['description'])): ?>
                        <div class="mb-6 text-lg text-gray-700 bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                            <?php echo htmlspecialchars($post['description']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="prose max-w-none article-content">
                            <?php 
                            // content_htmlがあればそれを使用、なければcontentを使用
                            if (!empty($post['content_html'])) {
                                echo $post['content_html']; 
                            } else {
                                echo nl2br(htmlspecialchars($post['content']));
                            }
                            ?>
                        </div>
                        
                        <!-- 記事のシェアボタン -->
                        <div class="mt-8 pt-6 border-t">
                            <h3 class="text-lg font-bold mb-3">この記事をシェアする</h3>
                            <div class="flex gap-2">
                                <a href="#" class="flex items-center justify-center w-10 h-10 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
                                        <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/>
                                    </svg>
                                </a>
                                <a href="#" class="flex items-center justify-center w-10 h-10 bg-blue-400 text-white rounded-full hover:bg-blue-500 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-twitter" viewBox="0 0 16 16">
                                        <path d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.382A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z"/>
                                    </svg>
                                </a>
                                <a href="#" class="flex items-center justify-center w-10 h-10 bg-green-500 text-white rounded-full hover:bg-green-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-line" viewBox="0 0 16 16">
                                        <path d="M8 0c4.411 0 8 2.912 8 6.492 0 1.433-.555 2.723-1.715 3.994-1.678 1.932-5.431 4.285-6.285 4.645-.83.35-.734-.197-.696-.413l.003-.018.114-.685c.027-.204.055-.521-.026-.723-.09-.223-.444-.339-.704-.395C2.846 12.39 0 9.701 0 6.492 0 2.912 3.59 0 8 0ZM5.022 7.686H3.497V4.918a.156.156 0 0 0-.155-.156H2.78a.156.156 0 0 0-.156.156v3.486c0 .041.017.08.044.107v.001l.002.002.002.002a.154.154 0 0 0 .108.043h2.242c.086 0 .155-.07.155-.156v-.56a.156.156 0 0 0-.155-.157Zm.791-2.924a.156.156 0 0 0-.156.156v3.486c0 .086.07.155.156.155h.562c.086 0 .155-.07.155-.155V4.918a.156.156 0 0 0-.155-.156h-.562Zm3.863 0a.156.156 0 0 0-.156.156v2.07L7.923 4.832a.17.17 0 0 0-.013-.015v-.001a.139.139 0 0 0-.01-.01l-.003-.003a.092.092 0 0 0-.011-.009h-.001L7.88 4.79l-.003-.002a.029.029 0 0 0-.005-.003l-.008-.005h-.002l-.003-.002-.01-.004-.004-.002a.093.093 0 0 0-.01-.003h-.002l-.003-.001-.009-.002h-.006l-.003-.001h-.004l-.002-.001h-.574a.156.156 0 0 0-.156.155v3.486c0 .086.07.155.156.155h.56c.087 0 .157-.07.157-.155v-2.07l1.6 2.16a.154.154 0 0 0 .039.038l.001.001.01.006.004.002a.066.066 0 0 0 .008.004l.007.003.005.002a.168.168 0 0 0 .01.003h.003a.155.155 0 0 0 .04.006h.56c.087 0 .157-.07.157-.155V4.918a.156.156 0 0 0-.156-.156h-.561Zm3.815.717v-.56a.156.156 0 0 0-.155-.157h-2.242a.155.155 0 0 0-.108.044h-.001l-.001.002-.002.003a.155.155 0 0 0-.044.107v3.486c0 .041.017.08.044.107l.002.003.002.002a.155.155 0 0 0 .108.043h2.242c.086 0 .155-.07.155-.156v-.56a.156.156 0 0 0-.155-.157H11.81v-.589h1.525c.086 0 .155-.07.155-.156v-.56a.156.156 0 0 0-.155-.157H11.81v-.589h1.525c.086 0 .155-.07.155-.156Z"/>
                                    </svg>
                                </a>
                                <a href="#" class="flex items-center justify-center w-10 h-10 bg-gray-600 text-white rounded-full hover:bg-gray-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope" viewBox="0 0 16 16">
                                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
            
            <!-- サイドバー -->
            <div class="w-full lg:w-1/3 space-y-6">
                <!-- 目次 -->
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-20">
                    <h2 class="text-lg font-bold mb-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                        目次
                    </h2>
                    <nav class="toc">
                        <ul class="space-y-2 text-sm custom-scrollbar max-h-[calc(100vh-250px)] overflow-y-auto pr-2">
                            <!-- JavaScriptで動的に生成 -->
                        </ul>
                    </nav>
                </div>
                
                <!-- 最新の記事 -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-bold mb-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        最新の記事
                    </h2>
                    <ul class="space-y-4">
                        <?php foreach ($latestPosts as $latestPost): ?>
                            <li class="border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                                <a href="activity_detail.php?id=<?php echo $latestPost['id']; ?>" class="group">
                                    <h3 class="font-medium line-clamp-2 group-hover:text-blue-600 transition-colors">
                                        <?php echo htmlspecialchars($latestPost['title']); ?>
                                    </h3>
                                    <time datetime="<?php echo date('Y-m-d', strtotime($latestPost['created_at'])); ?>" class="text-sm text-gray-500">
                                        <?php echo date('Y年m月d日', strtotime($latestPost['created_at'])); ?>
                                    </time>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
    
    <script>
        // 目次の生成
        document.addEventListener('DOMContentLoaded', function() {
            const articleContent = document.querySelector('.article-content');
            const toc = document.querySelector('.toc ul');
            
            if (articleContent && toc) {
                const headings = articleContent.querySelectorAll('h2, h3');
                
                if (headings.length > 0) {
                    headings.forEach((heading, index) => {
                        // 見出しにIDを付与
                        const id = `heading-${index}`;
                        heading.id = id;
                        
                        // 目次項目の作成
                        const li = document.createElement('li');
                        const a = document.createElement('a');
                        a.href = `#${id}`;
                        a.textContent = heading.textContent;
                        a.className = heading.tagName === 'H3' 
                            ? 'pl-4 text-gray-600 hover:text-blue-600 toc-link flex items-center' 
                            : 'font-medium hover:text-blue-600 toc-link flex items-center';
                        
                        // アイコンを追加
                        if (heading.tagName === 'H2') {
                            const icon = document.createElement('span');
                            icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>';
                            a.prepend(icon);
                        } else {
                            const icon = document.createElement('span');
                            icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-2 w-2 mr-1" viewBox="0 0 20 20" fill="currentColor"><circle cx="10" cy="10" r="3" /></svg>';
                            a.prepend(icon);
                        }
                        
                        li.appendChild(a);
                        toc.appendChild(li);
                    });
                    
                    // スクロール時のアクティブ状態の更新
                    const tocLinks = document.querySelectorAll('.toc-link');
                    const headingElements = Array.from(headings);
                    
                    window.addEventListener('scroll', () => {
                        const scrollPosition = window.scrollY;
                        
                        // 各見出しの位置をチェック
                        for (let i = 0; i < headingElements.length; i++) {
                            const heading = headingElements[i];
                            const nextHeading = headingElements[i + 1];
                            
                            const headingTop = heading.offsetTop - 100;
                            const headingBottom = nextHeading ? nextHeading.offsetTop - 100 : document.body.scrollHeight;
                            
                            if (scrollPosition >= headingTop && scrollPosition < headingBottom) {
                                // 現在のアクティブな目次項目をリセット
                                tocLinks.forEach(link => link.classList.remove('active'));
                                
                                // 現在の見出しに対応する目次項目をアクティブに
                                const activeLink = document.querySelector(`.toc-link[href="#${heading.id}"]`);
                                if (activeLink) {
                                    activeLink.classList.add('active');
                                }
                                
                                break;
                            }
                        }
                    });
                } else {
                    toc.innerHTML = '<li class="text-gray-500">目次はありません</li>';
                }
            }
        });
    </script>
</body>
</html>

