<?php
// データベース接続
require 'db.php';

// 最新の活動記録を取得（4件）
$latestActivitiesQuery = "SELECT id, title, description, image_data, image_type, created_at 
                         FROM posts 
                         ORDER BY created_at DESC 
                         LIMIT 4";
$latestActivitiesResult = $conn->query($latestActivitiesQuery);

// 最新の技術ブログ記事を取得（3件）
$latestBlogQuery = "SELECT id, title, description, category_id, created_at 
                   FROM tecblog 
                   ORDER BY created_at DESC 
                   LIMIT 3";
$latestBlogResult = $conn->query($latestBlogQuery);

// ヘッダーの読み込み
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ConvivialNet - ネットワーク技術を実践的に学ぶコミュニティ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Noto+Sans+JP:wght@300;400;500;700&display=swap">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Noto Sans JP', 'sans-serif'],
                        display: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    },
                }
            }
        }
    </script>
    <style>
        .hero-section {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.9) 0%, rgba(16, 185, 129, 0.8) 100%);
            background-size: cover;
            background-position: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            z-index: 0;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .gradient-text {
            background: linear-gradient(90deg, #4f46e5, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
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
        
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            z-index: 0;
            animation: float 8s ease-in-out infinite;
            opacity: 0.5;
        }
        
        .blob-1 {
            width: 300px;
            height: 300px;
            background: rgba(99, 102, 241, 0.4);
            top: -100px;
            right: 10%;
            animation-delay: 0s;
        }
        
        .blob-2 {
            width: 350px;
            height: 350px;
            background: rgba(16, 185, 129, 0.4);
            bottom: -150px;
            left: 5%;
            animation-delay: 2s;
        }
        
        .blob-3 {
            width: 200px;
            height: 200px;
            background: rgba(249, 115, 22, 0.3);
            top: 30%;
            left: 30%;
            animation-delay: 4s;
        }
        
        .feature-card {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .activity-image {
            transition: all 0.5s ease;
        }
        
        .activity-card:hover .activity-image {
            transform: scale(1.05);
        }
        
        .input-focus {
            transition: all 0.3s ease;
        }
        
        .input-focus:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- ヒーローセクション -->
    <section class="hero-section text-white min-h-screen flex items-center">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
        
        <div class="container mx-auto px-4 py-24 md:py-32 hero-content">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-8">
                    <span class="inline-block px-4 py-1 rounded-full bg-white/20 backdrop-blur-sm text-white text-sm font-medium mb-4">
                        中京大学工学部情報工学科
                    </span>
                </div>
                <h1 class="text-5xl md:text-7xl font-bold mb-6 font-display tracking-tight text-center">
                    Convivial<span class="text-primary-200">Net</span>
                </h1>
                <p class="text-xl md:text-2xl mb-10 text-center font-light max-w-3xl mx-auto leading-relaxed">
                    私たちはネットワーク技術を実践的に学び、未来のインターネットを創造するコミュニティです
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4 mt-8">
                    <a href="#about" class="px-8 py-4 bg-white text-primary-700 rounded-full font-medium hover:bg-primary-50 transition-all shadow-lg hover:shadow-xl">
                        詳しく知る
                    </a>
                    <a href="techbrog.php" class="px-8 py-4 bg-transparent border-2 border-white text-white rounded-full font-medium hover:bg-white/10 transition-all">
                        技術ブログを見る
                    </a>
                </div>
                
                <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="feature-card rounded-xl p-6 text-center card-hover">
                        <div class="w-16 h-16 bg-primary-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <h3 class="text-primary-800 font-bold text-lg mb-2">実機を用いた学習</h3>
                        <p class="text-gray-700">実際の機器を使って実践的なネットワーク技術を学びます</p>
                    </div>
                    
                    <div class="feature-card rounded-xl p-6 text-center card-hover">
                        <div class="w-16 h-16 bg-primary-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                        </div>
                        <h3 class="text-primary-800 font-bold text-lg mb-2">外部団体とのピアリング</h3>
                        <p class="text-gray-700">他組織と連携し、実際のインターネットに接続します</p>
                    </div>
                    
                    <div class="feature-card rounded-xl p-6 text-center card-hover">
                        <div class="w-16 h-16 bg-primary-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-primary-800 font-bold text-lg mb-2">コンテスト参加</h3>
                        <p class="text-gray-700">各種技術コンテストに積極的に参加し、スキルを競います</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 私たちについて -->
    <section id="about" class="py-24 bg-white relative overflow-hidden">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-primary-100 rounded-full opacity-50"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-emerald-100 rounded-full opacity-50"></div>
        
        <div class="container mx-auto px-4 relative">
            <div class="max-w-3xl mx-auto text-center mb-16">
                <span class="inline-block px-3 py-1 bg-primary-100 text-primary-800 rounded-full text-sm font-medium mb-4">ABOUT US</span>
                <h2 class="text-4xl font-bold mb-4 font-display gradient-text inline-block">私たちについて</h2>
                <p class="text-gray-600 text-lg">中京大学工学部情報工学科の学生主体で活動するネットワーク技術コミュニティ</p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary-500 to-emerald-500 rounded-2xl transform rotate-3 scale-105 opacity-20"></div>
                    <div class="relative bg-white p-1 rounded-2xl shadow-xl">
                        <div class="aspect-[4/3] rounded-xl overflow-hidden bg-gradient-to-r from-primary-500 to-emerald-500">
                            <!-- 画像を表示 -->
                            <img src="images/sava.jpg" alt="サーバールーム" class="w-full h-full object-cover" />
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-3xl font-bold mb-6 font-display">ミッション</h3>
                    <p class="text-gray-700 mb-8 text-lg leading-relaxed">
                        私たちは、ネットワーク技術の実践的な学習と応用を通じて、次世代のインターネットインフラを支える人材を育成することを目指しています。
                        理論だけでなく実機を用いた経験を積み、実社会で活躍できるスキルを身につけることを大切にしています。
                    </p>
                    
                    <h3 class="text-3xl font-bold mb-6 font-display">活動内容</h3>
                    <ul class="space-y-4 text-gray-700 mb-8">
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-primary-100 flex items-center justify-center mt-1 mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-primary-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <span class="font-medium text-gray-900">ネットワーク技術の実機を用いた学習</span>
                                <p class="mt-1 text-gray-600">ルーターやスイッチなどの実機を使用し、実践的なネットワーク構築技術を学びます。</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-primary-100 flex items-center justify-center mt-1 mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-primary-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <span class="font-medium text-gray-900">外部団体とのトランジット</span>
                                <p class="mt-1 text-gray-600">他大学や企業と連携し、実際のインターネットに接続するための技術交流を行います。</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-primary-100 flex items-center justify-center mt-1 mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-primary-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <span class="font-medium text-gray-900">各種コンテストへの参加</span>
                                <p class="mt-1 text-gray-600">SECCON、ICTトラブルシューティングコンテストなど、技術力を競う大会に積極的に参加しています。</p>
                            </div>
                        </li>
                    </ul>
                    
                    <a href="techbrog.php" class="inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-full font-medium hover:bg-primary-700 transition-all shadow-md hover:shadow-lg">
                        技術ブログを見る
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 最新の活動記録 -->
    <section class="py-24 bg-gray-50 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-64 bg-gradient-to-b from-white to-transparent"></div>
        
        <div class="container mx-auto px-4 relative">
            <div class="text-center mb-16">
                <span class="inline-block px-3 py-1 bg-primary-100 text-primary-800 rounded-full text-sm font-medium mb-4">ACTIVITIES</span>
                <h2 class="text-4xl font-bold mb-4 font-display gradient-text inline-block">最新の活動記録</h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">私たちの最近の取り組みをご紹介します。技術的な挑戦や成果を発信しています。</p>
            </div>
            
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                <?php if ($latestActivitiesResult && $latestActivitiesResult->num_rows > 0): ?>
                    <?php while ($activity = $latestActivitiesResult->fetch_assoc()): ?>
                        <?php
                            // タグの取得と整形
                            $tags = explode(',', $activity['tags'] ?? '');
                            $tags = array_map('trim', $tags);
                            $tags = array_filter($tags);
                            $firstTag = reset($tags);
                        ?>
                        <div class="bg-white rounded-2xl overflow-hidden shadow-lg transition-all duration-300 hover:shadow-2xl card-hover activity-card">
                            <div class="aspect-[4/3] relative overflow-hidden">
                                <?php if (!empty($activity['image_data'])): ?>
                                    <img 
                                        src="image.php?id=<?php echo $activity['id']; ?>" 
                                        alt="<?php echo htmlspecialchars($activity['title']); ?>"
                                        class="w-full h-full object-cover activity-image"
                                    >
                                <?php else: ?>
                                    <div class="w-full h-full bg-gradient-to-br from-primary-400 to-emerald-400 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="absolute top-4 right-4">
                                    <?php if (!empty($firstTag)): ?>
                                        <span class="px-3 py-1 text-xs font-medium bg-white/80 backdrop-blur-sm text-primary-800 rounded-full shadow-sm">
                                            <?php echo htmlspecialchars($firstTag); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="mb-3">
                                    <span class="text-sm text-gray-500 font-medium">
                                        <?php echo date('Y年m月d日', strtotime($activity['created_at'])); ?>
                                    </span>
                                </div>
                                <h3 class="font-bold text-xl mb-3 line-clamp-2 text-gray-900">
                                    <a href="activity_detail.php?id=<?php echo $activity['id']; ?>" class="hover:text-primary-600 transition-colors">
                                        <?php echo htmlspecialchars($activity['title']); ?>
                                    </a>
                                </h3>
                                <p class="text-gray-600 line-clamp-3 mb-4">
                                    <?php echo htmlspecialchars($activity['description']); ?>
                                </p>
                                <a href="activity_detail.php?id=<?php echo $activity['id']; ?>" class="inline-flex items-center text-primary-600 hover:text-primary-800 font-medium group">
                                    詳細を見る
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1 transform transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-4 text-center py-16 bg-white rounded-2xl shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <p class="text-gray-500 text-lg">活動記録がまだありません。</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-16">
                <a href="list.php" class="inline-flex items-center px-8 py-4 bg-white text-primary-700 rounded-full font-medium hover:bg-primary-50 transition-all shadow-lg hover:shadow-xl border border-primary-100">
                    すべての活動記録を見る
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- 技術ブログ -->
    <section class="py-24 bg-white relative overflow-hidden">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-100 rounded-full opacity-50"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-primary-100 rounded-full opacity-50"></div>
        
        <div class="container mx-auto px-4 relative">
            <div class="max-w-3xl mx-auto text-center mb-16">
                <span class="inline-block px-3 py-1 bg-primary-100 text-primary-800 rounded-full text-sm font-medium mb-4">TECH BLOG</span>
                <h2 class="text-4xl font-bold mb-4 font-display gradient-text inline-block">技術ブログ</h2>
                <p class="text-gray-600 text-lg">私たちの技術的な知見や学びを発信しています</p>
            </div>
            
            <div class="grid gap-8 md:grid-cols-3">
                <?php if ($latestBlogResult && $latestBlogResult->num_rows > 0): ?>
                    <?php while ($blog = $latestBlogResult->fetch_assoc()): ?>
                        <div class="bg-white rounded-2xl overflow-hidden shadow-lg transition-all duration-300 hover:shadow-2xl card-hover">
                            <div class="p-6">
                                <div class="mb-3 flex justify-between items-center">
                                    <span class="text-sm text-gray-500 font-medium">
                                        <?php echo date('Y年m月d日', strtotime($blog['created_at'])); ?>
                                    </span>
                                    <?php if (!empty($blog['category'])): ?>
                                        <span class="px-3 py-1 text-xs font-medium bg-primary-100 text-primary-800 rounded-full">
                                            <?php echo htmlspecialchars($blog['category']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="font-bold text-xl mb-3 line-clamp-2 text-gray-900">
                                    <a href="techbrog_detail.php?id=<?php echo $blog['id']; ?>" class="hover:text-primary-600 transition-colors">
                                        <?php echo htmlspecialchars($blog['title']); ?>
                                    </a>
                                </h3>
                                <p class="text-gray-600 line-clamp-3 mb-4">
                                    <?php echo htmlspecialchars($blog['description']); ?>
                                </p>
                                <a href="techbrog_detail.php?id=<?php echo $blog['id']; ?>" class="inline-flex items-center text-primary-600 hover:text-primary-800 font-medium group">
                                    続きを読む
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1 transform transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-3 text-center py-16 bg-white rounded-2xl shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                        <p class="text-gray-500 text-lg">技術ブログの記事がまだありません。</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-16">
                <a href="techbrog.php" class="inline-flex items-center px-8 py-4 bg-white text-primary-700 rounded-full font-medium hover:bg-primary-50 transition-all shadow-lg hover:shadow-xl border border-primary-100">
                    すべての技術ブログを見る
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // スムーズスクロール
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>