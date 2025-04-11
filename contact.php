<?php
// または PHPMailer を手動で読み込む場合
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// 初期化
$name = $email = $subject = $message = '';
$errors = [];
$success = false;

// フォーム送信時の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // バリデーション
    if (empty($_POST['name'])) {
        $errors['name'] = 'お名前を入力してください';
    } else {
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    }
    
    if (empty($_POST['email'])) {
        $errors['email'] = 'メールアドレスを入力してください';
    } else {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = '有効なメールアドレスを入力してください';
        }
    }
    
    if (empty($_POST['subject'])) {
        $errors['subject'] = '件名を入力してください';
    } else {
        $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS);
    }
    
    if (empty($_POST['message'])) {
        $errors['message'] = 'メッセージを入力してください';
    } else {
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS);
    }
    
    // エラーがなければメール送信処理
    if (empty($errors)) {
        // PHPMailerのインスタンスを作成
        $mail = new PHPMailer(true);
        
        try {
            // サーバー設定
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;  // デバッグ出力を有効化（本番環境では無効に）
            $mail->isSMTP();                           // SMTPを使用
            $mail->Host       = 'smtp.gmail.com';    // SMTPサーバー
            $mail->SMTPAuth   = true;                  // SMTP認証を有効化
            $mail->Username   = '1041netohai@gmail.com';    // SMTPユーザー名
            $mail->Password   = 'loadurenrsvmatzn';            // SMTPパスワード
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // 暗号化（TLS）
            $mail->Port       = 587;                   // TCPポート
            $mail->CharSet    = 'UTF-8';               // 文字セット

            // 送信元と送信先
            $mail->setFrom($email, $name);             // 送信元（問い合わせ者）
            $mail->addAddress('1041netohai@gmail.com', 'ConvivialNet'); // 送信先（管理者）
            $mail->addReplyTo($email, $name);          // 返信先

            // メール内容
            $mail->isHTML(false);                      // プレーンテキスト形式
            $mail->Subject = "お問い合わせ: $subject";
            
            // メール本文
            $mailBody = "お名前: $name\n";
            $mailBody .= "メールアドレス: $email\n";
            $mailBody .= "件名: $subject\n\n";
            $mailBody .= "メッセージ:\n$message\n";
            
            $mail->Body = $mailBody;

            // メール送信
            $mail->send();
            
            // 送信成功
            $success = true;
            
            // フォームをクリア
            $name = $email = $subject = $message = '';
            
        } catch (Exception $e) {
            // エラーメッセージを設定
            $errors['mail'] = "メールの送信に失敗しました: {$mail->ErrorInfo}";
        }
    }
}

// ヘッダーの読み込み
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>お問い合わせ</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <main class="container mx-auto px-4 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold tracking-tight">お問い合わせ</h1>
      <p class="mt-2 text-gray-600">
        活動に関するご質問やお問い合わせはこちらからお願いします。
      </p>
    </div>
    
    <div class="max-w-3xl mx-auto">
      <?php if ($success): ?>
        <!-- 送信成功メッセージ -->
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-6 mb-8">
          <h3 class="text-lg font-medium mb-2">お問い合わせありがとうございます</h3>
          <p>メッセージを受け付けました。担当者より折り返しご連絡いたします。</p>
          <div class="mt-4">
            <a href="list.php" class="inline-flex items-center text-green-700 hover:text-green-900">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-1">
                <path d="m15 18-6-6 6-6"></path>
              </svg>
              活動記録に戻る
            </a>
          </div>
        </div>
      <?php endif; ?>
      
      <?php if (isset($errors['mail'])): ?>
        <!-- メール送信エラーメッセージ -->
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-6 mb-8">
          <h3 class="text-lg font-medium mb-2">エラーが発生しました</h3>
          <p><?php echo $errors['mail']; ?></p>
        </div>
      <?php endif; ?>
      
      <!-- お問い合わせフォーム -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 sm:p-8">
          <h2 class="text-xl font-bold mb-6">お問い合わせフォーム</h2>
          
          <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="space-y-6">
              <!-- お名前 -->
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">お名前 <span class="text-red-600">*</span></label>
                <input 
                  type="text" 
                  id="name" 
                  name="name" 
                  value="<?php echo htmlspecialchars($name); ?>" 
                  class="w-full px-4 py-2 border <?php echo isset($errors['name']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                >
                <?php if (isset($errors['name'])): ?>
                  <p class="mt-1 text-sm text-red-600"><?php echo $errors['name']; ?></p>
                <?php endif; ?>
              </div>
              
              <!-- メールアドレス -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">メールアドレス <span class="text-red-600">*</span></label>
                <input 
                  type="email" 
                  id="email" 
                  name="email" 
                  value="<?php echo htmlspecialchars($email); ?>" 
                  class="w-full px-4 py-2 border <?php echo isset($errors['email']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                >
                <?php if (isset($errors['email'])): ?>
                  <p class="mt-1 text-sm text-red-600"><?php echo $errors['email']; ?></p>
                <?php endif; ?>
              </div>
              
              <!-- 件名 -->
              <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">件名 <span class="text-red-600">*</span></label>
                <input 
                  type="text" 
                  id="subject" 
                  name="subject" 
                  value="<?php echo htmlspecialchars($subject); ?>" 
                  class="w-full px-4 py-2 border <?php echo isset($errors['subject']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                >
                <?php if (isset($errors['subject'])): ?>
                  <p class="mt-1 text-sm text-red-600"><?php echo $errors['subject']; ?></p>
                <?php endif; ?>
              </div>
              
              <!-- メッセージ -->
              <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">メッセージ <span class="text-red-600">*</span></label>
                <textarea 
                  id="message" 
                  name="message" 
                  rows="6" 
                  class="w-full px-4 py-2 border <?php echo isset($errors['message']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                ><?php echo htmlspecialchars($message); ?></textarea>
                <?php if (isset($errors['message'])): ?>
                  <p class="mt-1 text-sm text-red-600"><?php echo $errors['message']; ?></p>
                <?php endif; ?>
              </div>
              
              <!-- プライバシーポリシー -->
              <div>
                <div class="flex items-start">
                  <div class="flex items-center h-5">
                    <input 
                      id="privacy" 
                      name="privacy" 
                      type="checkbox" 
                      class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                      required
                    >
                  </div>
                  <div class="ml-3 text-sm">
                    <label for="privacy" class="font-medium text-gray-700">プライバシーポリシーに同意する <span class="text-red-600">*</span></label>
                    <p class="text-gray-500">お問い合わせいただく前に、<a href="privacy-policy.php" class="text-blue-600 hover:underline">プライバシーポリシー</a>をご確認ください。</p>
                  </div>
                </div>
              </div>
              
              <!-- 送信ボタン -->
              <div>
                <button 
                  type="submit" 
                  class="w-full px-6 py-3 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                  送信する
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
      
      <!-- 連絡先情報 -->
      <div class="mt-8 bg-white rounded-lg shadow-md p-6 sm:p-8">
        <h2 class="text-xl font-bold mb-4">その他の連絡方法</h2>
        
        <div class="grid gap-6 md:grid-cols-2">
          <div>
            <h3 class="font-medium mb-2">メールでのお問い合わせ</h3>
            <p class="text-gray-600">
              <a href="mailto:info@convivial.ne.jp" class="text-blue-600 hover:underline">tanimura1041@convivial.ne.jp</a>
            </p>
          </div>
          
          <div>
            <h3 class="font-medium mb-2">所在地</h3>
            <p class="text-gray-600">
            〒470-0393<br>
            愛知県豊田市貝津町床立101</p>
            </p>
          </div>
          
          
        </div>
      </div>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>
</body>
</html>