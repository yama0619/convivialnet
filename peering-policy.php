<?php
// ヘッダーの読み込み（もし共通のヘッダーがあれば）
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ピアリングポリシー | ConvivialNet</title>
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
                        primary: { 50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc', 400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca', 800: '#3730a3', 900: '#312e81' },
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-text {
            background: linear-gradient(90deg, #4f46e5, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans text-gray-800">

    <header class="bg-primary-900 py-16 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="container mx-auto px-4 relative z-10 text-center">
            <h1 class="text-4xl md:text-5xl font-bold font-display mb-4 tracking-tight">Peering Policy</h1>
            <p class="text-primary-200 text-lg">AS45689 ピアリングポリシー</p>
        </div>
    </header>

    <main class="container mx-auto px-4 py-12 max-w-4xl">
        
        <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-8">
            <div class="flex items-center mb-6">
                <div class="w-2 h-8 bg-primary-600 rounded-full mr-4"></div>
                <h2 class="text-2xl font-bold text-gray-900">1. ネットワーク情報</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 rounded-xl p-6 font-mono text-sm">
                <div>
                    <h3 class="text-gray-500 uppercase text-xs mb-2">AS Number</h3>
                    <p class="text-xl font-bold text-primary-700">AS45689</p>
                </div>
                <div>
                    <h3 class="text-gray-500 uppercase text-xs mb-2">Prefixes</h3>
                    <ul class="space-y-1 text-gray-700">
                        <li>202.222.160.0/20</li>
                        <li>202.222.176.0/20</li>
                        <li>2001:0df0:0068::/48</li>
                    </ul>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h2 class="text-xl font-bold mb-4 flex items-center">
                    <svg class="w-6 h-6 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    接続条件
                </h2>
                <ul class="space-y-3 text-sm text-gray-600 leading-relaxed">
                    <li><strong class="text-gray-900">非営利性:</strong> 商用（営利）目的のネットワークではないこと。</li>
                    <li><strong class="text-gray-900">目的:</strong> 学術・研究・教育目的、または当組織の研究に寄与すること。</li>
                    <li><strong class="text-gray-900">正規割当:</strong> RIR/NIR等から正規にAS・IPの割り当てを受けていること。</li>
                </ul>
                <p class="mt-4 text-xs text-gray-400">※ 当組織の判断により接続をお断りする場合があります。</p>
            </section>

            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h2 class="text-xl font-bold mb-4 flex items-center">
                    <svg class="w-6 h-6 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    利用ルール
                </h2>
                <ul class="space-y-3 text-sm text-gray-600 leading-relaxed">
                    <li><strong class="text-gray-900">連絡体制:</strong> PeeringDB等のアドレスで常に連絡が取れること。</li>
                    <li><strong class="text-gray-900">迅速な対応:</strong> 管理者要請（メンテ・障害対応等）に速やかに応じること。</li>
                    <li><strong class="text-gray-900">遵守事項:</strong> 下記の禁止事項を行わないこと。</li>
                </ul>
            </section>
        </div>

        <section class="bg-white rounded-2xl shadow-sm border border-red-100 p-8 mb-8">
            <div class="flex items-center mb-6 text-red-600">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <h2 class="text-2xl font-bold">禁止事項</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                <div class="border-l-2 border-red-500 pl-4">
                    <h3 class="font-bold mb-1">商用・営利目的の利用</h3>
                    <p class="text-gray-600">商用サービスの基盤としての利用、または営利を目的としたトラフィックの送受信。</p>
                </div>
                <div class="border-l-2 border-red-500 pl-4">
                    <h3 class="font-bold mb-1">不正な接続・広報</h3>
                    <p class="text-gray-600">無断接続、送信元偽装、未割当空間の利用、申請と異なるAS番号での広報。</p>
                </div>
                <div class="border-l-2 border-red-500 pl-4">
                    <h3 class="font-bold mb-1">不適切な経路制御</h3>
                    <p class="text-gray-600">合意のないトラフィック送信、無許可の静的経路設定、Default Routeの送出。</p>
                </div>
                <div class="border-l-2 border-red-500 pl-4">
                    <h3 class="font-bold mb-1">過剰なL2パケット</h3>
                    <p class="text-gray-600">CDP, LLDP, STP等のブロードキャスト・マルチキャストパケットの送信。</p>
                </div>
            </div>
        </section>

        <section class="text-center text-gray-500 text-sm py-8 border-t border-gray-200">
            <p>本ポリシーは当組織の判断により予告なく変更される場合があります。</p>
            <p class="mt-2">変更後も接続を継続する場合、変更後のポリシーに同意したものとみなします。</p>
        </div>

        <div class="text-center mt-8">
            <a href="index.php" class="text-primary-600 hover:text-primary-800 font-medium flex items-center justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                トップページへ戻る
            </a>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>