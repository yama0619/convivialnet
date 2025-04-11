<?php
session_start();
// データベース接続
require 'db.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"]; 
    $password = $_POST["password"];

    // メールアドレスでユーザーを検索
    $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $username, $password_hash);
    $stmt->fetch();

    // ユーザーが存在して、パスワードが正しい場合
    if ($user_id && password_verify($password, $password_hash)) {
        // セッションにユーザーIDとユーザー名を保存
        $_SESSION["user_id"] = $user_id;
        $_SESSION["username"] = $username;
        //var_dump($_SESSION); //デバック

        header("Location: admin/index.php");
        exit();
    } else {
        $error_message = "メールアドレスまたはパスワードが正しくありません。";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン | Convivial Net</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
        }
        .login-container {
            background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .form-container {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.9);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="login-container min-h-screen flex items-center justify-center p-4">
        <div class="form-container w-full max-w-md rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6 text-white">
                <h1 class="text-2xl font-bold text-center">Convivial Net</h1>
                <p class="text-center text-blue-100 mt-1">管理者画面にログイン</p>
            </div>
            
            <div class="p-6 md:p-8">
                <?php if ($error_message): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
                <?php endif; ?>
                
                <form method="post" action="login.php" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">メールアドレス</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                </svg>
                            </div>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required 
                                class="pl-10 w-full py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="your-email@example.com"
                            >
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">パスワード</label>
                            <!-- <a href="#" class="text-sm text-blue-600 hover:text-blue-500">
                                パスワードをお忘れですか？
                            </a> -->
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                class="pl-10 w-full py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="••••••••"
                            >
                        </div>
                    </div>
                    
                    <!-- <div class="flex items-center">
                        <input 
                            id="remember_me" 
                            name="remember_me" 
                            type="checkbox" 
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="remember_me" class="ml-2 block text-sm text-gray-700">
                            ログイン状態を保持する
                        </label>
                    </div> -->
                    
                    <div>
                        <button 
                            type="submit" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150"
                        >
                            ログイン
                        </button>
                    </div>
                </form>
                
                <!-- <p class="mt-8 text-center text-sm text-gray-600">
                    アカウントをお持ちでないですか？
                    <a href="register.php" class="font-medium text-blue-600 hover:text-blue-500">
                        新規登録
                    </a>
                </p> -->
            </div>
        </div>
    </div>
</body>
</html>

