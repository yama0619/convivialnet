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
  "基本テンプレート" => "## 開催日時
2025年〇月〇日（金）16:50〜18:20

## 参加者
ConvivialNetメンバー〇名

## 活動内容
ここに活動の詳細内容を書きます。

## 成果
ここに活動の成果を書きます。

## 次回の予定
ここに次回の予定を書きます。",

  "勉強会" => "## 開催日時
2025年〇月〇日（金）16:50〜18:20

## 参加者
ConvivialNetメンバー〇名

## 勉強会の内容
今回の勉強会では、〇〇について学びました。

### 学んだこと
1. 
2. 
3. 
4. 

### 実習内容
実習内容を記載

## 感想
あれば

## 次回の予定
次回は「〇〇」をテーマに勉強会を開催する予定です。",

  "イベント参加" => "## 開催日時
2025年〇月〇日（金）16:50〜18:20

## 参加者
ConvivialNetメンバー〇名

## 参加の目的
参加目的について

## 結果
結果

## 今後の予定
今後の活動予定について。",

  "書き方" => "
## 大見出し(章)
### 中見出し(節)
#### 小見出し(項)

```python
# Pythonコードの例
def hello_world():
    print(\"Hello, World!\")
```

Markdownの記法で検索してみてね!
  
"
];

// フォーム送信時の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'] ?? '';
  $description = $_POST['description'] ?? '';
  $content = $_POST['content'] ?? '';
  $participants = intval($_POST['participants'] ?? 0);
  
  // Markdownをパースしてコンテンツを生成
  $content_html = $parsedown->text($content);
  
  // 画像処理
  $imageData = null;
  $imageType = null;
  
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
      // 画像ファイルを読み込む
      $imageData = file_get_contents($_FILES['image']['tmp_name']);
      $imageType = $_FILES['image']['type'];
  }
  
  // データベースに保存
  $stmt = $conn->prepare("INSERT INTO posts (title, description, content, content_html, image_data, image_type, participants) VALUES (?, ?, ?, ?, ?, ?, ?)");
  
  $stmt->bind_param("ssssssi", $title, $description, $content, $content_html, $imageData, $imageType, $participants);
  
  if ($stmt->execute()) {
      $success_message = "活動記録が正常に追加されました。";
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
  <title>活動記録の追加 | Convivial Net</title>
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
                      <h2 class="text-xl font-semibold text-gray-800">活動記録の追加</h2>
                  </div>
                  <div class="flex items-center">
                      <a href="../list.php" class="text-blue-600 hover:text-blue-800 mr-4" target="_blank">
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

              <!-- 活動記録追加フォーム -->
              <div class="bg-white rounded-lg shadow mb-6">
                  <div class="p-4 border-b flex justify-between items-center">
                      <h3 class="text-lg font-semibold">新しい活動記録を追加</h3>
                      <a href="activities.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
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
                          
                          <div class="grid gap-6 mb-6 md:grid-cols-2 mt-6">                              
                              <div>
                                  <label for="participants" class="block text-sm font-medium text-gray-700 mb-1">参加者数</label>
                                  <input 
                                      type="number" 
                                      id="participants" 
                                      name="participants" 
                                      min="0" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                  >
                              </div>
                          </div>
                          
                          
                          <div class="mb-6">
                              <label for="image" class="block text-sm font-medium text-gray-700 mb-1">画像</label>
                              <input 
                                  type="file" 
                                  id="image" 
                                  name="image" 
                                  accept="image/*" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                              >
                              <p class="text-sm text-gray-500 mt-1">推奨サイズ: 1200 x 800 ピクセル</p>
                          </div>
                          
                          <div class="flex justify-end space-x-3">
                              <a href="activities.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
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

      // 初期表示時にプレビューを更新
      updatePreview();
  </script>
</body>
</html>

