<?php
/*
Plugin Name: Markdown Bulk Post Widget
Description: マークダウン形式のテキストを2つ以上のハイフンで分割して、最初の行をタイトルとして記事を一括投稿できるカスタムウィジェット。
Version: 1.0
Author: Your Name
*/

// parsedown.phpファイルを読み込む
require_once(dirname(__FILE__) . '/parsedown.php');

// Parsedownクラスをインスタンス化
$parsedown = new Parsedown();

// ウィジェットを追加するためのフック
add_action('wp_dashboard_setup', 'add_markdown_bulk_post_widget');

function add_markdown_bulk_post_widget() {
    wp_add_dashboard_widget('markdown_bulk_post_widget', 'Markdown Bulk Post Widget', 'display_markdown_bulk_post_widget');
}

// ウィジェットの表示内容
function display_markdown_bulk_post_widget() {
    // フォームの表示
    echo '
    <form method="post">
        <p>
            <label for="markdown_content">マークダウン形式のテキスト:</label><br>
            <textarea id="markdown_content" name="markdown_content" rows="10" cols="50" required></textarea>
        </p>
        <p>
            <input type="submit" name="submit_markdown" value="一括投稿">
        </p>
    </form>
    ';

    // フォームが送信されたときの処理
    if (isset($_POST['submit_markdown'])) {
        $markdown_content = sanitize_text_field($_POST['markdown_content']);

        // 正規表現を使用して2つ以上のハイフンでマークダウン形式のテキストを分割
        $articles = preg_split('/-{2,}/', $markdown_content);

        foreach ($articles as $article) {
            $article = trim($article); // 余分な空白を取り除く
            if (!empty($article)) {
                // セクションを行ごとに分割
                $lines = explode("\n", $article);
                
                // 最初の行をタイトルとして取得
                $title = trim($lines[0]);
                
                // 残りの行を内容として結合
                $content = implode("\n", array_slice($lines, 1));
                
                // マークダウンをHTMLに変換（Parsedownを使用）
                $content = $parsedown->text($content);

                // 新しい投稿を作成
                $post_data = array(
                    'post_title' => $title, // 最初の行をタイトルとして設定
                    'post_content' => $content, // 残りの行を内容として設定
                    'post_status' => 'publish', // 公開
                    'post_type' => 'post', // 投稿
                );

                $post_id = wp_insert_post($post_data);

                if ($post_id) {
                    echo '<p>記事が正常に投稿されました。</p>';
                } else {
                    echo '<p>投稿中にエラーが発生しました。</p>';
                }
            }
        }
    }
}
?>
