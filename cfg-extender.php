<?php
/**
 * Author: Tsuyoshi.
 * Author URI: http://webcake.no003.info/
 */
class CfgExtender
{
	/** プラグイン名称 */
	const PLUGIN_NAME = 'Custom Field GUI Utility Extender';
	
	/** 編集ファイルパス */
	private $_filepath;
	
	/**
	 *	コンストラクタ
	 */
	public function __construct() {
		$this->_filepath = dirname( __FILE__ )  . '/conf.ini';
		
		add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
	}

	/**
	 *	プラグインメニュー
	 */
	public function plugin_menu() {
	
		// @filter_hook プルダウンに表示されるメニュー名
	 	$menuname = apply_filters( 'cfg_menu_name', 'カスタムフィールド設定' );
	 	
	 	// @filter_hook エディタ機能を使うかどうか
	 	$useedit = apply_filters( 'cfg_use_edit', true );
	
		// 管理者ページでのみ実行
		if ( is_admin() && $useedit ) {
			add_options_page(
				// サブメニューページのタイトル
				self::PLUGIN_NAME,
				// プルダウンに表示されるメニュー名
				$menuname,
				// サブメニューの権限名
				'manage_options',
				// サブメニューのスラッグ
				basename( __FILE__ ),
				// サブメニューページのコールバック関数
				array( $this, 'plugin_page' )
			);
		}
	}

	/**
	 *	プラグインページ
	 */
	public function plugin_page() {
		$contents = '';
		
		//エラーチェック
		if ( $this->error_check() === false ) {
			return;
		}
		
		// 編集実行
		if ( isset( $_POST['submit'] ) && ! empty( $_POST['txtconf'] ) ) {
			// ファイル上書き
			if ( file_put_contents( $this->_filepath, $_POST['txtconf'] , LOCK_EX ) ) {
				echo '<div id="message" class="updated fade"><p><strong>更新しました。</strong></p></div>';
			} else {
				$this->view_errors( '更新に失敗しました' );
			}
		}
		
		// iniファイルパースエラー警告出力
		if ( @parse_ini_file( $this->_filepath ) === false ) {
			$this->view_errors( 'iniファイルのパースエラーです <strong>半角 ( ) </strong>などの記号入っていないか、また正しいフォーマットか確認して下さい。' );
		}
	
		// ファイルが存在すれば読み込み
		if ( file_exists( $this->_filepath ) ) {
			
			$contents = file_get_contents( $this->_filepath );
			
			// バックスラッシュでクォートされた文字列を元に戻す
			$contents = stripslashes( $contents );
		}
		
		// View表示
		$this->view_content( $contents );
	}
	
	/**
	 *	View表示
	 */
	private function view_content( $contents ) {
		echo '<div class="wrap">
				<div id="icon-plugins" class="icon32"><br/></div>
				<h2>' . self::PLUGIN_NAME . '</h2>
				<form action="" method="post">
					<p><textarea name="txtconf" rows="30" cols="120">' . $contents  . '</textarea></p>'
						. get_submit_button() .
			'	</form>';
			  '</div>';
	}
	
	/**
	 *	エラーチェック
	 */
	private function error_check() {	
		// ファイル存在チェック
		if ( ! file_exists( $this->_filepath ) ) {
			$this->view_errors( 'ファイル <strong>' . $this->_filepath . '</strong> が存在しません' );
			return false;
		}
		
		return true;
	}
	
	/**
	 *	エラー表示
	 */
	private function view_errors( $message ) {
		echo '<div class="error">
				<p>' . $message .'</p>
			  </div>';
	}
}

// インスタンス生成
$CfgExtender = new CfgExtender();