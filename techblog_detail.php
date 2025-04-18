<?php
// データベース接続
require 'db.php';

// IDの取得と検証
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: tecblog.php");
    exit;
}

// 記事データの取得（カテゴリ名も含める）
$stmt = $conn->prepare("
    SELECT t.id, t.title, t.description, t.content, t.content_html, 
           t.category_id, t.created_at, t.user_id, bc.category_name 
    FROM tecblog t
    LEFT JOIN blog_categories bc ON t.category_id = bc.id
    WHERE t.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    header("Location: tecblog.php");
    exit;
}

$post = $result->fetch_assoc();
$stmt->close();

// 著者情報の取得
$author_info = null;
if (!empty($post['user_id'])) {
    // まずuser_profilesテーブルからプロフィール情報を取得
    $stmt = $conn->prepare("
        SELECT id, user_id, display_name, bio, image_data, image_type, show_profile 
        FROM user_profiles 
        WHERE user_id = ? AND show_profile = 1
    ");
    $stmt->bind_param("i", $post['user_id']);
    $stmt->execute();
    $profile_result = $stmt->get_result();
    if ($profile_result && $profile_result->num_rows > 0) {
        $author_info = $profile_result->fetch_assoc();
        $author_info['has_profile'] = true;
    } else {
        // プロフィールがない場合はusersテーブルから基本情報を取得
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE id = ?");
        $stmt->bind_param("i", $post['user_id']);
        $stmt->execute();
        $user_result = $stmt->get_result();
        if ($user_result && $user_result->num_rows > 0) {
            $user = $user_result->fetch_assoc();
            $author_info = [
                'user_id' => $user['id'],
                'display_name' => '管理者',
                'bio' => 'このサイトの管理者です。技術的な内容から運営に関する情報まで、幅広いトピックについて発信しています。',
                'has_profile' => false
            ];
        }
    }
    $stmt->close();
}

// 関連記事の取得
$relatedPosts = [];
if (!empty($post['category_id'])) {
    $relatedSql = "
        SELECT t.id, t.title, t.created_at, bc.category_name 
        FROM tecblog t
        LEFT JOIN blog_categories bc ON t.category_id = bc.id
        WHERE t.id != ? AND t.category_id = ? 
        ORDER BY t.created_at DESC LIMIT 3
    ";
    $stmt = $conn->prepare($relatedSql);
    $stmt->bind_param("ii", $id, $post['category_id']);
    $stmt->execute();
    $relatedResult = $stmt->get_result();

    if ($relatedResult) {
        while ($related = $relatedResult->fetch_assoc()) {
            $relatedPosts[] = $related;
        }
    }
    $stmt->close();
}

// 最新記事の取得（サイドバー用）
$latestPostsQuery = "
    SELECT t.id, t.title, t.created_at, bc.category_name 
    FROM tecblog t
    LEFT JOIN blog_categories bc ON t.category_id = bc.id
    WHERE t.id != ? 
    ORDER BY t.created_at DESC LIMIT 5
";
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

// ページタイトルを設定
$page_title = htmlspecialchars($post['title']);

// ヘッダーの読み込み
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | 技術ブログ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
        }
        
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
        
        /* 目次のアクティブ状態 */
        .toc-link.active {
            color: #2563eb;
            font-weight: 600;
        }
        
        /* タイトルの装飾 */
        .article-title {
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        
        .article-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
            border-radius: 2px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <main class="container mx-auto px-4 py-8">
        <!-- パンくずナビゲーション -->
        <nav class="flex mb-6 text-sm">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="index.php" class="text-gray-600 hover:text-gray-900">ホーム</a>
                </li>
                <li class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-gray-400">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                    <a href="tecblog.php" class="text-gray-600 hover:text-gray-900">技術ブログ</a>
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
                <article class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 md:p-8">
                        <!-- 目立つタイトル -->
                        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 article-title">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </h1>
                        
                        <div class="flex items-center mb-6">
                            <div class="flex items-center text-gray-600 mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <time datetime="<?php echo date('Y-m-d', strtotime($post['created_at'])); ?>">
                                    <?php echo date('Y年m月d日', strtotime($post['created_at'])); ?>
                                </time>
                            </div>
                            
                            <?php if (!empty($post['category_name'])): ?>
                            <div class="flex items-center text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <a href="tecblog.php?category_id=<?php echo urlencode($post['category_id']); ?>" class="text-blue-600 hover:text-blue-800">
                                    <?php echo htmlspecialchars($post['category_name']); ?>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($post['description'])): ?>
                        <div class="mb-8 text-lg text-gray-700 bg-blue-50 p-5 rounded-lg border-l-4 border-blue-500">
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
                        
                    </div>
                </article>
                
                <!-- 著者プロフィール -->
                <?php if ($author_info): ?>
                <div class="mt-8">
                    <h2 class="text-xl font-bold mb-4">この記事の著者</h2>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-4 hover:bg-gray-50">
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-20 h-20 rounded-full overflow-hidden bg-gray-200">
                                        <?php if (isset($author_info['has_profile']) && $author_info['has_profile'] && !empty($author_info['image_data'])): ?>
                                            <img src="data:<?php echo htmlspecialchars($author_info['image_type']); ?>;base64,<?php echo base64_encode($author_info['image_data']); ?>" alt="<?php echo htmlspecialchars($author_info['display_name']); ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center text-gray-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($author_info['display_name']); ?></h3>
                                    <?php if (!empty($author_info['bio'])): ?>
                                    <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($author_info['bio'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- 関連記事 -->
                <?php if (!empty($relatedPosts)): ?>
                <div class="mt-8">
                    <h2 class="text-xl font-bold mb-4">関連記事</h2>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <ul class="divide-y divide-gray-100">
                            <?php foreach ($relatedPosts as $related): ?>
                            <li class="p-4 hover:bg-gray-50">
                                <a href="techblog_detail.php?id=<?php echo $related['id']; ?>" class="block">
                                    <h3 class="font-bold text-gray-900 mb-1">
                                        <?php echo htmlspecialchars($related['title']); ?>
                                    </h3>
                                    <div class="flex items-center text-sm text-gray-500">
                                        <time datetime="<?php echo date('Y-m-d', strtotime($related['created_at'])); ?>">
                                            <?php echo date('Y年m月d日', strtotime($related['created_at'])); ?>
                                        </time>
                                        <?php if (!empty($related['category_name'])): ?>
                                        <span class="mx-2">•</span>
                                        <span class="text-blue-600">
                                            <?php echo htmlspecialchars($related['category_name']); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- サイドバー -->
            <div class="w-full lg:w-1/3 space-y-6">
                <!-- 目次 -->
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-20">
                    <h2 class="text-lg font-bold mb-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4  18h7" />
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
                                <a href="techblog_detail.php?id=<?php echo $latestPost['id']; ?>" class="group">
                                    <h3 class="font-medium line-clamp-2 group-hover:text-blue-600 transition-colors">
                                        <?php echo htmlspecialchars($latestPost['title']); ?>
                                    </h3>
                                    <div class="flex items-center text-sm text-gray-500 mt-1">
                                        <time datetime="<?php echo date('Y-m-d', strtotime($latestPost['created_at'])); ?>">
                                            <?php echo date('Y年m月d日', strtotime($latestPost['created_at'])); ?>
                                        </time>
                                        <?php if (!empty($latestPost['category_name'])): ?>
                                        <span class="mx-2">•</span>
                                        <span class="text-blue-600">
                                            <?php echo htmlspecialchars($latestPost['category_name']); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
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
