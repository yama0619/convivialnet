<?php
session_start();

// ログイン確認
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// データベース接続
require_once '../db.php';

// ユーザーIDの取得
$user_id = $_SESSION["user_id"];

// ユーザー情報の取得
$stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    // ユーザーが見つからない場合はログアウトさせる
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// プロフィール情報の取得
$stmt = $conn->prepare("SELECT id, user_id, display_name, bio, image_data, image_type, show_profile FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

// メッセージ変数の初期化
$message = '';
$error = '';

// フォーム送信時の処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        // プロフィール登録・更新
        if ($_POST['action'] == 'save') {
            $display_name = trim($_POST['display_name']);
            $bio = trim($_POST['bio']);
            $show_profile = isset($_POST['show_profile']) ? 1 : 0;
            
            // 画像アップロード処理
            $image_data = null;
            $image_type = null;
            
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png'];
                $filename = $_FILES['profile_image']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed)) {
                    // 画像データを取得
                    $image_data = file_get_contents($_FILES['profile_image']['tmp_name']);
                    $image_type = $_FILES['profile_image']['type'];
                } else {
                    $error = "許可されていないファイル形式です。JPG, PNG のみ許可されています。";
                }
            } elseif ($profile) {
                // 既存の画像を保持
                $image_data = $profile['image_data'];
                $image_type = $profile['image_type'];
            }
            
            if (empty($error)) {
                // プロフィールが既に存在するか確認
                if ($profile) {
                    // 更新
                    $stmt = $conn->prepare("UPDATE user_profiles SET display_name = ?, bio = ?, image_data = ?, image_type = ?, show_profile = ? WHERE user_id = ?");
                    $stmt->bind_param("ssssii", $display_name, $bio, $image_data, $image_type, $show_profile, $user_id);
                } else {
                    // 新規作成
                    $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, display_name, bio, image_data, image_type, show_profile) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("issssi", $user_id, $display_name, $bio, $image_data, $image_type, $show_profile);
                }
                
                if ($stmt->execute()) {
                    $message = "プロフィールを保存しました。";
                    
                    // 最新のプロフィール情報を取得
                    $stmt = $conn->prepare("SELECT id, user_id, display_name, bio, image_data, image_type, show_profile FROM user_profiles WHERE user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $profile = $result->fetch_assoc();
                } else {
                    $error = "プロフィールの保存に失敗しました。";
                }
                $stmt->close();
            }
        }
        
        // プロフィール削除
        elseif ($_POST['action'] == 'delete' && $profile) {
            // データベースからプロフィールを削除
            $stmt = $conn->prepare("DELETE FROM user_profiles WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                $message = "プロフィールを削除しました。";
                $profile = null; // プロフィール情報をクリア
            } else {
                $error = "プロフィールの削除に失敗しました。";
            }
            $stmt->close();
        }
    }
}

// ページタイトルを設定
$page_title = "プロフィール管理";

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | ConvivialNet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
        }
        .sidebar {
            background-image: linear-gradient(180deg, #1e40af 0%, #3b82f6 100%);
        }
        .line-clamp-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- サイドバーをインクルード -->
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
                        <h2 class="text-xl font-semibold text-gray-800"><?php echo $page_title; ?></h2>
                    </div>
                    <div class="flex items-center">
                        <a href="../index.php" class="text-blue-600 hover:text-blue-800 mr-4" target="_blank">
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
                <?php if (!empty($message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p><?php echo $message; ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p><?php echo $error; ?></p>
                </div>
                <?php endif; ?>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h3 class="text-lg font-semibold">プロフィール情報</h3>
                    </div>
                    <div class="p-6">
                        <form method="POST" enctype="multipart/form-data" class="space-y-6">
                            <input type="hidden" name="action" value="save">
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- プロフィール画像 - サイズを大きく -->
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">プロフィール画像</label>
                                    <div class="flex flex-col items-center">
                                        <div class="w-48 h-48 bg-gray-200 rounded-full overflow-hidden mb-3 shadow-md">
                                            <?php if (!empty($profile['image_data'])): ?>
                                                <img src="data:<?php echo htmlspecialchars($profile['image_type']); ?>;base64,<?php echo base64_encode($profile['image_data']); ?>" alt="プロフィール画像" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center text-gray-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <label for="profile_image" class="cursor-pointer px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors text-sm flex items-center">
                                            <i class="fas fa-camera mr-2"></i>
                                            画像を変更
                                            <input id="profile_image" name="profile_image" type="file" class="hidden" accept="image/*">
                                        </label>
                                        <p class="text-xs text-gray-500 mt-1">JPG, PNG</p>
                                    </div>
                                </div>
                                
                                <!-- プロフィール情報 -->
                                <div class="md:col-span-2 space-y-4">
                                    <div>
                                        <label for="display_name" class="block text-sm font-medium text-gray-700 mb-1">表示名</label>
                                        <input type="text" id="display_name" name="display_name" value="<?php echo isset($profile['display_name']) ? htmlspecialchars($profile['display_name']) : htmlspecialchars($user['username']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="text-xs text-gray-500 mt-1">技術ブログに表示される名前です</p>
                                    </div>
                                    
                                    <div>
                                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">自己紹介</label>
                                        <textarea id="bio" name="bio" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo isset($profile['bio']) ? htmlspecialchars($profile['bio'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        <p class="text-xs text-gray-500 mt-1">技術ブログの記事下に表示される簡単な自己紹介文です</p>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <input type="checkbox" id="show_profile" name="show_profile" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo (isset($profile['show_profile']) && $profile['show_profile'] == 1) ? 'checked' : ''; ?>>
                                        <label for="show_profile" class="ml-2 block text-sm text-gray-700">
                                            技術ブログにプロフィールを表示する
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between pt-4 border-t border-gray-200">
                                <div>
                                    <?php if ($profile): ?>
                                    <button type="submit" name="action" value="delete" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 flex items-center" onclick="return confirm('プロフィールを削除してもよろしいですか？')">
                                        <i class="fas fa-trash-alt mr-2"></i>
                                        プロフィールを削除
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center">
                                    <i class="fas fa-save mr-2"></i>
                                    <?php echo $profile ? '更新する' : '登録する'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- ユーザー情報 -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-semibold">ユーザー情報</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-1">ユーザー名</h3>
                                <p class="text-gray-900 flex items-center">
                                    <i class="fas fa-user text-gray-400 mr-2"></i>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-1">メールアドレス</h3>
                                <p class="text-gray-900 flex items-center">
                                    <i class="fas fa-envelope text-gray-400 mr-2"></i>
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-500 flex items-center">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                ユーザー情報を変更するには、管理者にお問い合わせください。
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- プレビュー -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-semibold">プロフィール表示プレビュー</h3>
                    </div>
                    <div class="p-6">
                        <?php if ($profile): ?>
                        <div class="flex items-start space-x-4 bg-gray-50 p-4 rounded-lg">
                            <div class="flex-shrink-0">
                                <!-- プレビュー画像も大きく -->
                                <div class="w-24 h-24 rounded-full overflow-hidden bg-gray-200 shadow-md">
                                    <?php if (!empty($profile['image_data'])): ?>
                                        <img src="data:<?php echo htmlspecialchars($profile['image_type']); ?>;base64,<?php echo base64_encode($profile['image_data']); ?>" alt="<?php echo htmlspecialchars($profile['display_name']); ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-gray-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($profile['display_name']); ?></h3>
                                <p class="text-gray-700 mt-1"><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="bg-gray-50 p-4 rounded-lg flex items-center text-gray-500">
                            <i class="fas fa-eye-slash text-gray-400 mr-3 text-xl"></i>
                            <p class="italic">プロフィールが設定されていないか、表示が無効になっています。</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>        
        // プロフィール画像のプレビュー
        document.getElementById('profile_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // セレクタを更新して大きいサイズの画像コンテナを対象に
                    const preview = document.querySelector('.w-48.h-48 img, .w-48.h-48 div');
                    const parent = preview.parentElement;
                    
                    // 画像要素を作成
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'プロフィール画像';
                    img.className = 'w-full h-full object-cover';
                    
                    // 既存の要素を置き換え
                    parent.innerHTML = '';
                    parent.appendChild(img);
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>