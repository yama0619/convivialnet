<?php
// データベース接続
require 'db.php';

// 最新の活動記録を取得（4件）
$latestActivitiesQuery = "SELECT id, title, description, image_data, image_type, created_at 
                         FROM posts 
                         ORDER BY created_at DESC 
                         LIMIT 4";
$latestActivitiesResult = $conn->query($latestActivitiesQuery);

// ヘッダーの読み込み
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>組織名 - ホーム</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .hero-section {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.7)), url('/images/hero-bg.jpg');
            background-size: cover;
            background-position: center;
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
    </style>
</head>
<body class="bg-gray-50">
    <!-- ヒーローセクション -->
    <section class="hero-section text-white">
        <div class="container mx-auto px-4 py-24 md:py-32">
            <div class="max-w-3xl">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">ConvivialNet</h1>
                <p class="text-xl mb-8">私たちはネットワーク技術を実践的に学ぶために様々な活動を行っています。</p>
            </div>
        </div>
    </section>

    <!-- 私たちについて -->
    <section id="about" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center mb-12">
                <h2 class="text-3xl font-bold">私たちについて</h2>
                <p class="text-gray-600 mt-2">中京大学工学部情報工学科の学生主体で活動</p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="aspect-[4/3] rounded-lg overflow-hidden">
                        <!-- <img src="/images/sava.jpg" alt="私たちの活動" class="w-full h-full object-cover" onerror="this.src='/placeholder.svg?height=400&width=600'; this.alt='組織の活動';"> -->
                    </div>
                </div>
                <div>
                    <h3 class="text-2xl font-bold mb-4">ミッション</h3>
                    <p class="text-gray-700 mb-6">
                        私たちは、地域社会の発展と国際交流を通じて、より良い社会づくりに貢献することを目指しています。
                        教育支援、環境保全、文化交流など様々な活動を通じて、持続可能な社会の実現に向けて取り組んでいます。
                    </p>
                    
                    <h3 class="text-2xl font-bold mb-4">活動内容</h3>
                    <ul class="space-y-3 text-gray-700 mb-6">
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            ネットワーク技術の実機を用いた学習
                        </li>
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            外部団体とのトランジット
                        </li>
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            各種コンテストへの参加
                        </li>
                    </ul>
                    
                    <a href="#contact" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                        お問い合わせはこちら →
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 最新の活動記録 -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold">最新の活動記録</h2>
                <p class="text-gray-600 mt-2">私たちの最近の取り組みをご紹介します</p>
            </div>
            
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <?php if ($latestActivitiesResult && $latestActivitiesResult->num_rows > 0): ?>
                    <?php while ($activity = $latestActivitiesResult->fetch_assoc()): ?>
                        <?php
                            // タグの取得と整形
                            $tags = explode(',', $activity['tags'] ?? '');
                            $tags = array_map('trim', $tags);
                            $tags = array_filter($tags);
                            $firstTag = reset($tags);
                        ?>
                        <div class="bg-gray-50 rounded-lg overflow-hidden shadow-md transition hover:shadow-lg">
                            <div class="aspect-[4/3] relative">
                                <?php if (!empty($activity['image_data'])): ?>
                                    <img 
                                        src="image.php?id=<?php echo $activity['id']; ?>" 
                                        alt="<?php echo htmlspecialchars($activity['title']); ?>"
                                        class="w-full h-full object-cover"
                                    >
                                <?php else: ?>
                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-gray-500">画像なし</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-500">
                                        <?php echo date('Y年m月d日', strtotime($activity['created_at'])); ?>
                                    </span>
                                    <?php if (!empty($firstTag)): ?>
                                        <span class="px-2 py-0.5 text-xs bg-gray-100 rounded-full">
                                            <?php echo htmlspecialchars($firstTag); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="font-bold text-lg mb-2 line-clamp-2">
                                    <a href="activity_detail.php?id=<?php echo $activity['id']; ?>" class="hover:text-blue-600">
                                        <?php echo htmlspecialchars($activity['title']); ?>
                                    </a>
                                </h3>
                                <p class="text-gray-600 text-sm line-clamp-3 mb-4">
                                    <?php echo htmlspecialchars($activity['description']); ?>
                                </p>
                                <a href="activity_detail.php?id=<?php echo $activity['id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    詳細を見る →
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-4 text-center py-12">
                        <p>活動記録がまだありません。</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-10">
                <a href="list.php" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    すべての活動記録を見る
                </a>
            </div>
        </div>
    </section>

    <!-- お問い合わせ -->
    <section id="contact" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold">お問い合わせ</h2>
                    <p class="text-gray-600 mt-2">ご質問やご相談はこちらからお気軽にどうぞ</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-8">
                    <form>
                        <div class="grid gap-6 mb-6 md:grid-cols-2">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">お名前（姓）</label>
                                <input type="text" id="first_name" name="first_name" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">お名前（名）</label>
                                <input type="text" id="last_name" name="last_name" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">メールアドレス</label>
                            <input type="email" id="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        
                        <div class="mb-6">
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">件名</label>
                            <input type="text" id="subject" name="subject" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        
                        <div class="mb-6">
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">メッセージ</label>
                            <textarea id="message" name="message" rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                送信する
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

