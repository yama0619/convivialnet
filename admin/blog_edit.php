<?php
session_start();

// ログイン確認
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Parsedownライブラリを読み込む
require_once 'Parsedown.php';
$parsedown = new Parsedown();

// データベース接続
require_once '../db.php';

// IDの取得と検証
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: blogs.php");
    exit;
}

// 既存のデータを取得
$stmt = $conn->prepare("
    SELECT tecblog.title, tecblog.description, tecblog.content, tecblog.content_html, tecblog.category_id, blog_categories.category_name
    FROM tecblog
    LEFT JOIN blog_categories ON tecblog.category_id = blog_categories.id
    WHERE tecblog.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($title, $description, $content, $content_html, $category_id, $category_name);
$stmt->fetch();
$stmt->close();

// カテゴリの取得
$categoriesQuery = "SELECT id, category_name FROM blog_categories ORDER BY category_name";
$categoriesResult = $conn->query($categoriesQuery);

$categories = [];
if ($categoriesResult) {
    while ($cat = $categoriesResult->fetch_assoc()) {
        $categories[] = $cat;
    }
}

// タグ関連のコードを削除

// 詳細内容のテンプレート（実際の環境に合わせて調整してください）
$contentTemplates = [
    "基本テンプレート" => "## はじめに\nここに導入部分を書きます。\n\n## 本文\nここに本文を書きます。\n\n## まとめ\nここにまとめを書きます。",
    "チュートリアル" => "## はじめに\nこのチュートリアルでは～について説明します。\n\n## 準備\n必要なもの：\n* 項目1\n* 項目2\n\n## 手順1\nここに手順1の詳細を書きます。\n\n## 手順2\nここに手順2の詳細を書きます。\n\n## まとめ\nこのチュートリアルでは～について学びました。",
    "技術解説" => "## 概要\nこの技術の概要を説明します。\n\n## 背景\n背景情報や歴史について説明します。\n\n## 主な特徴\n* 特徴1\n* 特徴2\n* 特徴3\n\n## 使用例\n実際の使用例を紹介します。\n\n## まとめ\nこの技術のまとめと今後の展望について。",
    "比較記事" => "## はじめに\nこの記事では～と～を比較します。\n\n## 比較項目1\n項目1についての比較内容。\n\n## 比較項目2\n項目2についての比較内容。\n\n## 比較表\n| 機能 | 製品A | 製品B |\n| ---- | ----- | ----- |\n| 機能1 | ○ | × |\n| 機能2 | △ | ○ |\n\n## 結論\n比較の結果と推奨。"
];

// フォーム送信時の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $content = $_POST['content'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    
    // Markdownをパースしてコンテンツを生成
    $content_html = $parsedown->text($content);
    
        // 画像以外を更新
    $stmt = $conn->prepare("UPDATE tecblog SET title = ?, description = ?, content = ?, content_html = ?, category_id = ? WHERE id = ?");
    $stmt->bind_param("ssssii", $title, $description, $content, $content_html, $category_id, $id);
    
    if ($stmt->execute()) {
        $success_message = "記事が正常に更新されました。";
    } else {
        $error_message = "エラーが発生しました: " . $stmt->error;
    }
    
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>技術ブログ記事の編集 | Convivial Net</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/github-markdown-css@5.2.0/github-markdown-light.css">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
        }
        .sidebar {
            background-image: linear-gradient(180deg, #1e40af 0%, #3b82f6 100%);
        }
        .content-preview {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            padding: 1rem;
            border-radius: 0.375rem;
            background-color: #f8fafc;
        }
        .markdown-editor {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.9rem;
            line-height: 1.5;
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
                        <h2 class="text-xl font-semibold text-gray-800">技術ブログ記事の編集</h2>
                    </div>
                    <div class="flex items-center">
                        <a href="../techbrog_detail.php?id=<?php echo $id; ?>" class="text-blue-600 hover:text-blue-800 mr-4" target="_blank">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            記事を表示
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

                <!-- 記事編集フォーム -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h3 class="text-lg font-semibold">記事を編集</h3>
                        <a href="blogs.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>一覧に戻る
                        </a>
                    </div>
                    <div class="p-6">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="grid gap-6 mb-6 md:grid-cols-2">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">タイトル <span class="text-red-500">*</span></label>
                                    <input 
                                        type="text" 
                                        id="title" 
                                        name="title" 
                                        value="<?php echo htmlspecialchars($title); ?>"
                                        required 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </div>
                                
                                <div>
                                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">カテゴリー <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <select 
                                            id="category_id" 
                                            name="category_id" 
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        >
                                            <option value="">カテゴリーを選択</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-6">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">概要 <span class="text-red-500">*</span></label>
                                <textarea 
                                    id="description" 
                                    name="description" 
                                    rows="3" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                ><?php echo htmlspecialchars($description); ?></textarea>
                            </div>
                            
                            <div class="grid gap-6 md:grid-cols-2">
                                <div>
                                    <label for="content_template" class="block text-sm font-medium text-gray-700 mb-1">Markdownテンプレート</label>
                                    <select 
                                        id="content_template" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 mb-2"
                                    >
                                        <option value="">テンプレートを選択</option>
                                        <?php foreach ($contentTemplates as $name => $template): ?>
                                            <option value="<?php echo htmlspecialchars($template); ?>"><?php echo htmlspecialchars($name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    
                                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">詳細内容（Markdown）<span class="text-red-500">*</span></label>
                                    <textarea 
                                        id="content" 
                                        name="content" 
                                        rows="15" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 markdown-editor"
                                    ><?php echo htmlspecialchars($content); ?></textarea>
                                    <p class="text-sm text-gray-500 mt-1">Markdownを使用して記事を作成できます。</p>
                                </div>
                                
                                <div>
                                    <div class="flex items-center mb-2">
                                        <label class="block text-sm font-medium text-gray-700 mr-auto">プレビュー</label>
                                        <button 
                                            type="button" 
                                            id="preview_update_button" 
                                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm flex items-center shadow-sm"
                                        >
                                            <i class="fas fa-sync-alt mr-2"></i>プレビュー更新
                                        </button>
                                    </div>
                                    <div id="content_preview" class="content-preview markdown-body">
                                        <?php echo $content_html; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-end space-x-3 mt-8">
                                <a href="blogs.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                                    キャンセル
                                </a>
                                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                    更新する
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // テンプレート選択時の処理
        document.getElementById('content_template').addEventListener('change', function() {
            if (this.value) {
                document.getElementById('content').value = this.value;
                updatePreview();
            }
        });

        // Markdownプレビューの更新
        document.getElementById('content').addEventListener('input', updatePreview);
        
        // 更新ボタンのイベントリスナーを追加
        document.getElementById('preview_update_button').addEventListener('click', updatePreview);

        function updatePreview() {
            const markdownContent = document.getElementById('content').value;
            
            // 更新ボタンを無効化し、ローディング表示
            const updateButton = document.getElementById('preview_update_button');
            updateButton.disabled = true;
            updateButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>更新中...';
            
            // サーバーにMarkdownをHTMLに変換するリクエストを送信
            fetch('markdown-preview.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'content=' + encodeURIComponent(markdownContent)
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('content_preview').innerHTML = html;
                // 更新ボタンを再度有効化
                updateButton.disabled = false;
                updateButton.innerHTML = '<i class="fas fa-sync-alt mr-2"></i>プレビュー更新';
            })
            .catch(error => {
                console.error('Error:', error);
                // エラー時も更新ボタンを再度有効化
                updateButton.disabled = false;
                updateButton.innerHTML = '<i class="fas fa-sync-alt mr-2"></i>プレビュー更新';
            });
        }

    </script>
</body>
</html>