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

// コンテンツテンプレート
$contentTemplates = [
  "基本テンプレート" => "## はじめに
ここに導入部分を書きます。

## 本文
ここに本文を書きます。

## まとめ
ここにまとめを書きます。",
  "チュートリアル" => "## はじめに
このチュートリアルでは～について説明します。

## 準備
必要なもの：
* 項目1
* 項目2

## 手順1
ここに手順1の詳細を書きます。

```javascript
// サンプルコード
function example() {
  console.log('Hello World!');
}
```

## 手順2

ここに手順2の詳細を書きます。

## まとめ

このチュートリアルでは～について学びました。",
"技術解説" => "## 概要
この技術の概要を説明します。

## 背景

背景情報や歴史について説明します。

## 主な特徴

- 特徴1
- 特徴2
- 特徴3


## 使用例

実際の使用例を紹介します。

```javascript
// サンプルコード
const data = {
  name: 'example',
  value: 42
};

// 処理
processData(data);
```

## まとめ

この技術のまとめと今後の展望について。",
"比較記事" => "## はじめに
この記事では～と～を比較します。

## 比較項目1

項目1についての比較内容。

## 比較項目2

項目2についての比較内容。

## 比較表

| 機能 | 製品A | 製品B
|-----|-----|-----
| 機能1 | ○ | ×
| 機能2 | △ | ○


## 結論

比較の結果と推奨。"
];

// カテゴリの取得
$categories_query = "SELECT id, category_name FROM blog_categories ORDER BY category_name";
$categories_result = $conn->query($categories_query);
$categories = [];
if ($categories_result) {
while ($category = $categories_result->fetch_assoc()) {
$categories[] = $category;
}
}

// user_idの取得
$session_user_id = $_SESSION["user_id"];
$profile_query = "SELECT id FROM user_profiles WHERE user_id = ?";
$profile_stmt = $conn->prepare($profile_query);
$profile_stmt->bind_param("i", $session_user_id);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();

if ($profile_result && $profile_result->num_rows > 0) {
    $profile_data = $profile_result->fetch_assoc();
    $user_id = $profile_data['id'];
} else {
    // プロフィールが見つからない場合はセッションのuser_idを使用
    $user_id = $session_user_id;
    // エラーログに記録
    error_log("ユーザープロフィールが見つかりません。user_id: $session_user_id");
}
$profile_stmt->close();


// フォーム送信時の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$content = $_POST['content'] ?? '';
$category_id = $_POST['category_id'] ?? '';
$user_id = $_SESSION["user_id"];


// Markdownをパースしてコンテンツを生成
$content_html = $parsedown->text($content);

// データベースに保存
$stmt = $conn->prepare("INSERT INTO tecblog (title, description, content, content_html, category_id, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");

$stmt->bind_param("ssssii", $title, $description, $content, $content_html, $category_id, $user_id);

if ($stmt->execute()) {
$success_message = "技術ブログ記事が正常に追加されました。";
} else {
$error_message = "エラーが発生しました: " . $stmt->error;
}

$stmt->close();
}
?>

<!DOCTYPE html><html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>技術ブログ記事の追加 | Convivial Net</title>
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
                  <h2 class="text-xl font-semibold text-gray-800">技術ブログ記事の追加</h2>
              </div>
              <div class="flex items-center">
                  <a href="../techbrog.php" class="text-blue-600 hover:text-blue-800 mr-4" target="_blank">
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

          <!-- 技術ブログ記事追加フォーム -->
          <div class="bg-white rounded-lg shadow mb-6">
              <div class="p-4 border-b flex justify-between items-center">
                  <h3 class="text-lg font-semibold">新しい技術ブログ記事を追加</h3>
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
                                      <?php foreach ($categories as $category): ?>
                                          <option value="<?php echo htmlspecialchars($category['id']); ?>">
                                              <?php echo htmlspecialchars($category['category_name']); ?>
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
                          ></textarea>
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
                              ></textarea>
                              <p class="text-sm text-gray-500 mt-1">Markdownを使用して記事を作成できます。</p>
                          </div>
                          
                          <div>
                              <div class="flex justify-between items-center mb-1">
                                  <label class="block text-sm font-medium text-gray-700">プレビュー</label>
                                  <button 
                                      type="button" 
                                      id="preview_update_button" 
                                      class="px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors text-sm"
                                  >
                                      <i class="fas fa-sync-alt mr-1"></i>更新
                                  </button>
                              </div>
                              <div id="content_preview" class="content-preview markdown-body">
                                  <!-- プレビューがここに表示されます -->
                              </div>
                          </div>
                      </div>
                      
                      <div class="flex justify-end space-x-3">
                          <a href="blogs.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                              キャンセル
                          </a>
                          <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                              保存する
                          </button>
                      </div>
                  </form>
              </div>
          </div>
      </main>
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
          updateButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>更新中...';
          
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
              updateButton.innerHTML = '<i class="fas fa-sync-alt mr-1"></i>更新';
          })
          .catch(error => {
              console.error('Error:', error);
              // エラー時も更新ボタンを再度有効化
              updateButton.disabled = false;
              updateButton.innerHTML = '<i class="fas fa-sync-alt mr-1"></i>更新';
          });
      }
  </script>
  </body>
</html>