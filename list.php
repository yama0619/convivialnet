<?php
// データベース接続
require 'db.php';

// AJAXリクエストの処理（もっと見るボタン用）
if (isset($_GET['ajax']) && $_GET['ajax'] == 'load_more') {
    // パラメータの取得
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
    $searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

    // オフセットの計算
    $offset = ($page - 1) * $limit;

    // メインクエリの構築
    $sql = "SELECT id, title, description, participants, created_at, 
            CASE WHEN image_data IS NOT NULL THEN 1 ELSE 0 END as has_image 
            FROM posts";

    // 検索条件がある場合
    if (!empty($searchQuery)) {
        $sql .= " WHERE title LIKE '%" . $conn->real_escape_string($searchQuery) . "%' 
                  OR description LIKE '%" . $conn->real_escape_string($searchQuery) . "%'";
    }

    $sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

    // クエリの実行
    $result = $conn->query($sql);

    // エラーチェック
    if (!$result) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'クエリエラー: ' . $conn->error]);
        exit;
    }

    // 総件数を取得して最後のページかどうかを判定
    $countSql = "SELECT COUNT(*) as total FROM posts";
    if (!empty($searchQuery)) {
        $countSql .= " WHERE title LIKE '%" . $conn->real_escape_string($searchQuery) . "%' 
                      OR description LIKE '%" . $conn->real_escape_string($searchQuery) . "%'";
    }
    $countResult = $conn->query($countSql);
    $totalCount = $countResult->fetch_assoc()['total'];
    $isLastPage = ($offset + $limit) >= $totalCount;

    // 結果の配列を作成
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        // 日付のフォーマット
        $row['created_at'] = date('Y年m月d日', strtotime($row['created_at']));
        $activities[] = $row;
    }

    // JSONとして出力
    header('Content-Type: application/json');
    echo json_encode([
        'activities' => $activities,
        'isLastPage' => $isLastPage,
        'page' => $page,
        'totalCount' => $totalCount
    ]);
    exit;
}

// 通常のページ表示処理（以下は元のコード）
// 最新の活動を取得（サイドバー用）
$latestActivitiesQuery = "SELECT id, title, created_at FROM posts ORDER BY created_at DESC LIMIT 5";
$latestActivitiesResult = $conn->query($latestActivitiesQuery);

// 活動の統計情報を取得
$statsQuery = "SELECT COUNT(*) as total_activities, SUM(participants) as total_participants FROM posts";
$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

// 検索クエリの取得
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// メインクエリの構築
$sql = "SELECT id, title, description, image_data, image_type, participants, created_at FROM posts";

// 検索条件がある場合
if (!empty($searchQuery)) {
    $sql .= " WHERE title LIKE '%" . $conn->real_escape_string($searchQuery) . "%' 
              OR description LIKE '%" . $conn->real_escape_string($searchQuery) . "%'";
}

$sql .= " ORDER BY created_at DESC LIMIT 5"; // 最初は5件だけ表示

// クエリの実行
$result = $conn->query($sql);

// エラーチェック
if (!$result) {
    die("クエリエラー: " . $conn->error);
}

// 投稿がある日付を取得
$currentMonth = date('m');
$currentYear = date('Y');
$postDatesQuery = "SELECT DAY(created_at) as post_day FROM posts 
                  WHERE MONTH(created_at) = $currentMonth 
                  AND YEAR(created_at) = $currentYear";
$postDatesResult = $conn->query($postDatesQuery);

$postDays = [];
if ($postDatesResult) {
    while ($date = $postDatesResult->fetch_assoc()) {
        $postDays[] = (int)$date['post_day'];
    }
}

// 総件数を取得（もっと見るボタンの表示判定用）
$countSql = "SELECT COUNT(*) as total FROM posts";
if (!empty($searchQuery)) {
    $countSql .= " WHERE title LIKE '%" . $conn->real_escape_string($searchQuery) . "%' 
                  OR description LIKE '%" . $conn->real_escape_string($searchQuery) . "%'";
}
$countResult = $conn->query($countSql);
$totalCount = $countResult->fetch_assoc()['total'];
$hasMoreItems = $totalCount > 5; // 5件以上あれば「もっと見る」ボタンを表示

// ヘッダーの読み込み
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>活動記録</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .line-clamp-3 {
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    @media (max-width: 768px) {
      .container {
        flex-direction: column;
      }
      .sidebar {
        width: 100%;
        margin-bottom: 20px;
      }
      .content {
        width: 100%;
        padding-left: 0;
      }
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

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-PH4QLF887P"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-PH4QLF887P');
</script>

<body class="bg-gray-50">
  <main class="container mx-auto px-4 py-8">
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">活動記録</h1>
        <p class="mt-2 text-gray-600">
          私たちの最近の活動と成果を紹介します。
        </p>
      </div>
      
      <!-- 検索フォーム（右側に配置） -->
      <div class="mt-4 md:mt-0">
        <form action="list.php" method="GET" class="flex w-full md:w-64">
          <input 
            type="text" 
            name="search" 
            placeholder="活動を検索..." 
            value="<?php echo htmlspecialchars($searchQuery); ?>"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-r-md hover:bg-blue-700 whitespace-nowrap min-w-[60px]">
            検索
          </button>
        </form>
      </div>
    </div>
    
    <div class="flex flex-col md:flex-row gap-8">
      <!-- サイドバー（新しい内容） -->
      <div class="w-full md:w-1/4">
        <div class="bg-white rounded-lg shadow-md p-6 sticky top-20">        
          <!-- カレンダー -->
          <div class="mb-6">
            <h3 class="font-bold text-lg mb-4">活動カレンダー</h3>
            <div class="bg-gray-50 p-3 rounded-lg text-center">
              <div class="text-sm font-medium text-gray-500 mb-2"><?php echo date('Y年m月'); ?></div>
              <div class="overflow-x-auto">

              <table class="w-full text-xs">
                <thead>
                  <tr>
                    <th class="py-1">日</th>
                    <th class="py-1">月</th>
                    <th class="py-1">火</th>
                    <th class="py-1">水</th>
                    <th class="py-1">木</th>
                    <th class="py-1">金</th>
                    <th class="py-1">土</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $currentMonth = date('m');
                    $currentYear = date('Y');
                    $firstDay = mktime(0, 0, 0, $currentMonth, 1, $currentYear);
                    $daysInMonth = date('t', $firstDay);
                    $dayOfWeek = date('w', $firstDay);
                    
                    // カレンダーの行を開始
                    echo '<tr>';
                    
                    // 月の最初の日までの空白セルを追加
                    for ($i = 0; $i < $dayOfWeek; $i++) {
                      echo '<td></td>';
                    }
                    
                    // 日付を表示
                    for ($day = 1; $day <= $daysInMonth; $day++) {
                      $currentDayOfWeek = ($dayOfWeek + $day - 1) % 7;
                      
                      // 日曜日なら新しい行を開始
                      if ($day > 1 && $currentDayOfWeek == 0) {
                        echo '</tr><tr>';
                      }
                      
                      // 今日の日付を強調表示
                      $isToday = ($day == date('j') && $currentMonth == date('m') && $currentYear == date('Y'));
                      // 投稿がある日付かチェック
                      $hasPost = in_array($day, $postDays);
                      
                      $cellClass = $isToday ? 'bg-blue-100 text-blue-800 font-bold rounded-full' : '';
                      $postClass = $hasPost ? 'post-day' : '';
                      
                      echo '<td class="p-1 text-center"><span class="inline-block w-6 h-6 leading-6 ' . $cellClass . ' ' . $postClass . '">' . $day . '</span></td>';
                    }
                    
                    // 月の最後の日以降の空白セルを追加
                    $remainingCells = 7 - (($dayOfWeek + $daysInMonth) % 7);
                    if ($remainingCells < 7) {
                      for ($i = 0; $i < $remainingCells; $i++) {
                        echo '<td></td>';
                      }
                    }
                    
                    echo '</tr>';
                  ?>
                </tbody>
              </table>
                  </div>
              <div class="mt-3 text-xs flex items-center justify-center">
                <span class="inline-block w-3 h-3 bg-red-500 rounded-full mr-1"></span>
                <span class="text-gray-600">投稿がある日付</span>
              </div>
            </div>
          </div>
          
          <!-- お問い合わせリンク -->
          <div>
            <h3 class="font-bold text-lg mb-4">お問い合わせ</h3>
            <p class="text-sm text-gray-600 mb-4">
              活動に関するお問い合わせやご質問は、お気軽にご連絡ください。
            </p>
            <a href="contact.php" class="block w-full px-4 py-2 bg-blue-600 text-white text-center rounded-md hover:bg-blue-700">
              お問い合わせ
            </a>
          </div>
        </div>
      </div>
      
      <!-- メインコンテンツ -->
      <div class="w-full md:w-3/4">
        <div id="activities-container">
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="md:flex">
                  <div class="md:w-1/3">
                    <?php if (!empty($row['image_data'])): ?>
                      <img 
                        src="image.php?id=<?php echo $row['id']; ?>" 
                        alt="<?php echo htmlspecialchars($row['title']); ?>"
                        class="w-full h-48 md:h-full object-cover"
                      >
                    <?php else: ?>
                      <div class="w-full h-48 md:h-full bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-500">画像なし</span>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="md:w-2/3 p-6">
                    <h3 class="text-xl font-bold mb-3"><?php echo htmlspecialchars($row['title']); ?></h3>
                    
                    <div class="grid gap-2 text-sm text-gray-600 mb-4">
                      <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                          <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                          <line x1="16" x2="16" y1="2" y2="6"></line>
                          <line x1="8" x2="8" y1="2" y2="6"></line>
                          <line x1="3" x2="21" y1="10" y2="10"></line>
                        </svg>
                        <span><?php echo date('Y年m月d日', strtotime($row['created_at'])); ?></span>
                      </div>
                      <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                          <circle cx="9" cy="7" r="4"></circle>
                          <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                          <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>参加者 <?php echo htmlspecialchars($row['participants']); ?>名</span>
                      </div>
                    </div>
                    
                    <p class="line-clamp-3 mb-4"><?php echo htmlspecialchars($row['description']); ?></p>
                    
                    <div class="flex flex-wrap gap-2">
                      <a href="activity_detail.php?id=<?php echo $row['id']; ?>" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        詳細を見る
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
              <?php if (!empty($searchQuery)): ?>
                <p>「<?php echo htmlspecialchars($searchQuery); ?>」に一致する活動記録はありません。</p>
                <a href="list.php" class="inline-block mt-4 text-blue-600 hover:underline">すべての活動を表示</a>
              <?php else: ?>
                <p>活動記録はありません。</p>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
        
        <?php if ($result->num_rows > 0 && $hasMoreItems): ?>
          <!-- ページネーション（もっと見るボタン） -->
          <div class="flex justify-center mt-8">
            <button id="load-more-button" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
              もっと見る
            </button>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>

  <!-- 記事数が5個以上だと、もっと見るが表示される。 -->
  <!-- もっと見るボタンの機能を実装するJavaScript -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const loadMoreButton = document.getElementById("load-more-button");
      if (!loadMoreButton) return;

      let currentPage = 1;
      const itemsPerPage = 5; // 一度に読み込む件数
      let isLoading = false;
      let hasMoreItems = true;

      loadMoreButton.addEventListener("click", () => {
        if (isLoading || !hasMoreItems) return;

        isLoading = true;
        loadMoreButton.textContent = "読み込み中...";
        loadMoreButton.disabled = true;

        // 検索クエリがあれば取得
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get("search") || "";

        // 追加データを取得
        fetch(`list.php?ajax=load_more&page=${currentPage + 1}&limit=${itemsPerPage}&search=${encodeURIComponent(searchQuery)}`)
          .then(response => response.json())
          .then(data => {
            isLoading = false;
            loadMoreButton.disabled = false;
            loadMoreButton.textContent = "もっと見る";

            if (data.activities && data.activities.length > 0) {
              // 新しい活動を追加
              const container = document.getElementById("activities-container");

              data.activities.forEach(activity => {
                const activityHtml = createActivityCard(activity);
                container.insertAdjacentHTML("beforeend", activityHtml);
              });

              currentPage++;

              // すべてのデータを表示したらボタンを非表示
              if (data.activities.length < itemsPerPage || data.isLastPage) {
                hasMoreItems = false;
                loadMoreButton.style.display = "none";
              }
            } else {
              // これ以上データがない場合
              hasMoreItems = false;
              loadMoreButton.style.display = "none";
            }
          })
          .catch(error => {
            console.error("活動の読み込み中にエラーが発生しました:", error);
            isLoading = false;
            loadMoreButton.disabled = false;
            loadMoreButton.textContent = "もっと見る";
          });
      });

      // 活動カードのHTMLを生成する関数
      function createActivityCard(activity) {
        const imageHtml = activity.has_image
          ? `<img src="image.php?id=${activity.id}" alt="${escapeHtml(activity.title)}" class="w-full h-48 md:h-full object-cover">`
          : `<div class="w-full h-48 md:h-full bg-gray-200 flex items-center justify-center"><span class="text-gray-500">画像なし</span></div>`;

        return `
          <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="md:flex">
              <div class="md:w-1/3">
                ${imageHtml}
              </div>
              <div class="md:w-2/3 p-6">
                <h3 class="text-xl font-bold mb-3">${escapeHtml(activity.title)}</h3>
                
                <div class="grid gap-2 text-sm text-gray-600 mb-4">
                  <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                      <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                      <line x1="16" x2="16" y1="2" y2="6"></line>
                      <line x1="8" x2="8" y1="2" y2="6"></line>
                      <line x1="3" x2="21" y1="10" y2="10"></line>
                    </svg>
                    <span>${activity.created_at}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                      <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                      <circle cx="9" cy="7" r="4"></circle>
                      <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                      <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span>参加者 ${escapeHtml(activity.participants)}名</span>
                  </div>
                </div>
                
                <p class="line-clamp-3 mb-4">${escapeHtml(activity.description)}</p>
                
                <div class="flex flex-wrap gap-2">
                  <a href="activity_detail.php?id=${activity.id}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                    詳細を見る
                  </a>
                </div>
              </div>
            </div>
          </div>
        `;
      }

      // HTMLエスケープ関数
      function escapeHtml(unsafe) {
        return unsafe
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;")
          .replace(/'/g, "&#039;");
      }
    });
  </script>
</body>
</html>
