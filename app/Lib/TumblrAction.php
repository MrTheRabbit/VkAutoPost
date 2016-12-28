<?php

namespace App\Lib;

use DB;
use ImgPuzzle;
 
/**
 * TumblrAction
 * Класс по работе с Tumblr.
 * 
 * @package 
 * @author TheRabbit
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class TumblrAction {
	
	/**
	 * TumblrAction::getPostsFromDashboard()
	 * Получает новые посты с Dashboard.
	 * 
	 * @return void
	 */
	public static function getPostsFromDashboard() {
		
		// Авторизуемся
		$objClient = new \App\Lib\Tumblr\API\Client(config('tumblr.consumer_key'), config('tumblr.consumer_secret'), config('tumblr.token'), config('tumblr.token_secret'));
		// Получим инфу о пользователе
		$objResult = $objClient->getUserInfo();

		if (isset($objResult->user->name) && strlen($objResult->user->name)) {
			$arrOptions = [
				'reblog_info' => true,
			];
			
			// Получим посты с Dashboard
			$objResult = $objClient->getDashboardPosts($arrOptions);

			if (isset($objResult->posts) && count(isset($objResult->posts))) {
				foreach ($objResult->posts as $objPost) {

					$arrInsert = $arrPostsID = [];
					$arrInsert[] = ['post_id' => $objPost->id];
					$arrPostsID[] = $objPost->id;
					// Добавим в базу прочитанные посты
					if (isset($objPost->reblogged_from_id) && intval($objPost->reblogged_from_id)) {
						$arrInsert[] = ['post_id' => $objPost->reblogged_from_id];
						$arrPostsID[] = $objPost->reblogged_from_id;
					}//\\ if
					
					// Проверим, может это уже скачивали?
					$intVID = DB::table('tumblr_post')->where('post_id', $arrPostsID)->value('id');
					if (!$intVID) {
						// Сохраним данные о посте
						DB::table('tumblr_post')->insert($arrInsert);
						
						if (isset($objPost->photos) && count($objPost->photos)) {
							foreach ($objPost->photos as $objPhoto) {
								if (isset($objPhoto->original_size->url)) {
									$strF = parse_url($objPhoto->original_size->url, PHP_URL_PATH);
									$arrF = explode('/', $strF);
									$strFileName = end($arrF);
									//var_dump($strFileName);
									if (strlen($strFileName)) {
										// Получим путь до файла в зависимости от тегов
										$strAddFolder = self::getFolderByTags(isset($objPost->tags) ? $objPost->tags : []);
										if (strlen($strAddFolder) && !file_exists(config('tumblr.patch_download').'/'.$strAddFolder)) 
											mkdir(config('tumblr.patch_download').'/'.$strAddFolder, 0777);
										
										$strPatchFile = config('tumblr.patch_download').'/'.$strAddFolder.$strFileName;
										
										// Скачаем контент
										$objFile = fopen($strPatchFile, 'w+');
										$objCurl = curl_init(str_replace(" ", "%20", $objPhoto->original_size->url));
										curl_setopt($objCurl, CURLOPT_TIMEOUT, 50);
										curl_setopt($objCurl, CURLOPT_FILE, $objFile); 
										curl_setopt($objCurl, CURLOPT_FOLLOWLOCATION, true);
										curl_setopt($objCurl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
										curl_exec($objCurl); 
										curl_close($objCurl);
										fclose($objFile);
										
										// Получим хеш файла
										$strHashFile = sha1_file($strPatchFile);
										
										// Поищем такой хеш в базе
										$intSH = DB::table('tumblr_files_hash')->where('hash', $strHashFile)->value('id');
										if ($intSH) @unlink($strPatchFile); // Такой файл был, удалим его
										else {
											// Поищем дубликаты изображения
											$arrWords = ImgPuzzle::searchDublicat($strPatchFile);
											if (ImgPuzzle::isFind($arrWords)) @unlink($strPatchFile); // Такой файл был, удалим его
											else DB::table('tumblr_files_hash')->insert(['hash' => $strHashFile]); // Добавим его в базу
										}//\\ if
									}//\\ if
								}//\\ if
							}//\\ foreach
						}//\\ if
					}//\\ if
				}//\\ foreach
			}//\\ if
		}//\\ if
	}//\\ getPostsFromDashboard
	
	/**
	 * TumblrAction::getFolderByTags()
	 * Анализирует теги и возвращает название папки для сохранения.
	 * 
	 * @param mixed $arrTags
	 * @return
	 */
	public static function getFolderByTags($arrTags = []) {
		$strFolder = '';
		
		if (count($arrTags)) {
			foreach ($arrTags as $intI => $strV)
				$arrTags[$intI] = strtolower($strV);
			
			if (in_array('cat', $arrTags)) $strFolder = 'cat';
			elseif (in_array('rat', $arrTags) || in_array('mouse', $arrTags)) $strFolder = 'mouse';
		}//\\ if
		
		if (strlen($strFolder)) $strFolder .= '/';
		return $strFolder;
	}//\\ getFolderByTags
}
