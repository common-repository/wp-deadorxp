<?php
/**
 * @package wp-deadorxp
 * @version 0.9
 */
/*
Plugin Name: wp-deadorxp
Plugin URI: http://stocker.jp/diary/wp-deadorxp/
Description: Windows XP利用者に警告を表示するプラグインです。
Author: @Stocker_jp
Version: 0.9
Author URI: http://stocker.jp/
*/

// jQuery をブログの head タグ内で呼び出す
function wp_deadorxp_add_script() {
	// 引数( 登録名, ファイル名, 依存するJavaScriptライブラリー, JavaScriptのバージョン)
	//wp_register_script( 'deadorxp_js', plugins_url('wp-deadorxp/wp-deadorxp.js'), array( 'jquery' ), 0.9 );
	wp_enqueue_script('jquery');
	//wp_enqueue_script('deadorxp_js');
}
add_action('wp_print_scripts','wp_deadorxp_add_script');

// スクリプトを wp_footer() で表示
function wp_deadorxp_load() {
	echo <<< EOS
<script type="text/javascript">
// DOM構築が終わったら
jQuery(function() {
	// XP でなければ return
	var ua = navigator.userAgent;
	if (!ua.match(/Win(dows )?(NT 5\.1|NT 5\.2|XP)/)) {
		return;
	}
	// body に class を追加
	jQuery('body').addClass('deadorxp');
	// 変数に使用する HTML を代入
	var deadorxp_html_modal = '<div id="deadorxp_html_modal"><div><p><span id="deadorxp_html_close">✕</span>
EOS;
	// ダイアログ用のテキストを取得
	$opt_text_val = get_option( 'wp_deadorxp_text' );
	// 表示
	echo $opt_text_val;
	echo <<< EOS
</p></div></div>';
	// deadorxp_html_modal を body の末尾に挿入
	jQuery('body').append(deadorxp_html_modal);
	// ✕ ボタンがクリックされたら消す
	jQuery('#deadorxp_html_close').click(function() {
		jQuery('#deadorxp_html_modal').css({
			display: 'none'
		})
		jQuery('body').removeClass('deadorxp');
	});
});
</script>

EOS;
}
add_action('wp_footer', 'wp_deadorxp_load');

// CSSをブログのheadタグ内で呼び出す
function wp_deadorxp_register_plugin_styles() {
	wp_register_style( 'deadorxp-white', plugins_url( 'wp-deadorxp/wp-deadorxp-white.css' ) );
	wp_register_style( 'deadorxp-black', plugins_url( 'wp-deadorxp/wp-deadorxp-black.css' ) );
	// 表示方法が「白いダイアログ」であれば wp-deadorxp-white.css
	if( get_option( 'wp_deadorxp_design' ) == 'white' ) {
		wp_enqueue_style( 'deadorxp-white' );
	} else {
		wp_enqueue_style( 'deadorxp-black' );
	}
}
add_action( 'wp_enqueue_scripts', 'wp_deadorxp_register_plugin_styles' );

// WordPress 管理画面に設定を表示
function wp_deadorxp_plugin_menu() {
	add_options_page( 'wp-deadorxp の設定', 'wp-deadorxp', 'manage_options', 'wp-deadorxp', 'wp_deadorxp_plugin_options' );
}
add_action( 'admin_menu', 'wp_deadorxp_plugin_menu' );

// 設定画面の内容
function wp_deadorxp_plugin_options() {
	// 権限があるかチェック
	if (!current_user_can('manage_options'))
	{
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	$opt_design = 'wp_deadorxp_design';
	$opt_text = 'wp_deadorxp_text';
	$hidden_field_name = 'wp_deadorxp_submit_hidden';
	$design_name = 'deadorxp_design';
	$text_name = 'deadorxp_text';
	$opt_design_val = get_option( $opt_design );
	$opt_text_val = get_option( $opt_text );

	if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'ht3cs6' ) {
		$opt_design_val = $_POST[ $design_name ];
		$opt_text_val = $_POST[ $text_name ];
		update_option( $opt_design, $opt_design_val );
		update_option( $opt_text, $opt_text_val );
		?>
		<div class="updated"><p><strong><?php _e('settings saved.', 'menu-test' ); ?></strong></p></div>
	<?php
	}
	echo '<div class="wrap">';
	echo "<h2>wp-deadorxp の設定</h2>";
	?>

	<form name="form1" method="post" action="">
		<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="ht3cs6">

		<h3><?php _e("表示方法を選択", 'menu-test' );?></h3>
		<p>
			<label><input type="radio" name="<?php echo $design_name; ?>" value="white" <?php if( get_option( $opt_design ) == 'white' ){echo 'checked';} ?>>白いダイアログ（一般サイト向け）</label><br>
			<label><input type="radio" name="<?php echo $design_name; ?>" value="black" <?php if( get_option( $opt_design ) == 'black' ){echo 'checked';} ?>>全画面黒（Web制作者向け）</label>
		</p>

		<h3><?php _e("XPユーザーに表示するテキスト", 'menu-test' ); ?></h3>
		<p>
			<textarea name="<?php echo $text_name; ?>" rows="7" cols="50"><?php echo $opt_text_val; ?></textarea>
		</p><hr />

		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
		</p>

	</form>
	</div>
<?php
}

// インストール時にデフォルトの設定を決める
function wp_deadorxp_activate() {
	update_option( 'wp_deadorxp_design', 'white' );
	update_option( 'wp_deadorxp_text', 'Microsoft による Windows XP のサポートは既に終了しています。<br>セキュリティ的に問題のある古い OS を使い続けた場合、個人情報の流出など広範囲に被害をもたらす可能性があり、大変危険です。<br>今すぐに Windows 8 以降にアップデートするか新しい PC などに買い替え、Windows XP の使用を終了してください。' );
}
register_activation_hook( __FILE__, 'wp_deadorxp_activate' );