<?php
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
        // メール送信先（管理者のメールアドレスに変更してください）
        $to = 'mt.book4062@gmail.com';
        
        // メールヘッダー
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // メール本文
        $mailBody = "お名前: $name\n";
        $mailBody .= "メールアドレス: $email\n";
        $mailBody .= "件名: $subject\n\n";
        $mailBody .= "メッセージ:\n$message\n";
        
        // メール送信（本番環境では有効にしてください）
        mail($to, "お問い合わせ: $subject", $mailBody, $headers);
        
        // 送信成功フラグ（本番環境ではmail関数の戻り値を使用）
        $success = true;
        
        // フォームをクリア
        $name = $email = $subject = $message = '';
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
              <a href="mailto:info@example.com" class="text-blue-600 hover:underline">info@example.com</a>
            </p>
          </div>
          
          <div>
            <h3 class="font-medium mb-2">電話でのお問い合わせ</h3>
            <p class="text-gray-600">
              <a href="tel:0312345678" class="text-blue-600 hover:underline">03-1234-5678</a><br>
              （平日 9:00〜17:00）
            </p>
          </div>
          
          <div>
            <h3 class="font-medium mb-2">所在地</h3>
            <p class="text-gray-600">
              〒100-0001<br>
              東京都千代田区千代田1-1<br>
              サンプルビル 5階
            </p>
          </div>
          
          <div>
            <h3 class="font-medium mb-2">SNS</h3>
            <div class="flex space-x-4 mt-2">
              <a href="#" class="text-gray-600 hover:text-blue-600">
                <span class="sr-only">Twitter</span>
                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path>
                </svg>
              </a>
              <a href="#" class="text-gray-600 hover:text-blue-600">
                <span class="sr-only">Facebook</span>
                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"></path>
                </svg>
              </a>
              <a href="#" class="text-gray-600 hover:text-blue-600">
                <span class="sr-only">Instagram</span>
                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd"></path>
                </svg>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>
</body>
</html>