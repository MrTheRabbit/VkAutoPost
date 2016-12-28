<?php

namespace App\Lib;

use DB;
use VK;
use VKException;
use Log;
use Logger;
 
/**
 * VKAction
 * Класс для операций с VK.
 * 
 * @package 
 * @author TheRabbit
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class VKAction {
	
	private static $booError = false; 
	
	/**
	 * VKAction::logSendPost()
	 * Записываем сообщение в лог.
	 * 
	 * @param mixed $strType
	 * @param mixed $intTaskID
	 * @param mixed $strErrorMessage
	 * @param mixed $arrDataDump
	 * @return void
	 */
	private static function logSendPost($strType, $intTaskID, $strErrorMessage, $arrDataDump = []) {
		DB::table('tasks')->where('id', $intTaskID)->update([
			'is_error' => 'Y',
			'error' => $strErrorMessage,
		]);
		
		if (count($arrDataDump)) $arrDataDump = array_merge($arrDataDump, ['task_id' => $intTaskID]);
		else $arrDataDump = ['task_id' => $intTaskID];
		
		if (isset($arrDataDump['response']['error']['error_code']) && intval($arrDataDump['response']['error']['error_code']) && isset($arrDataDump['response']['error']['error_msg']) && strlen($arrDataDump['response']['error']['error_msg'])) {
			$strErrorMessage .= ' Ошибка:['.$arrDataDump['response']['error']['error_code'].'] '.$arrDataDump['response']['error']['error_msg'];
		}//\\ if
		
		$objMonolog = Log::getMonolog();
		$objMonolog->pushHandler(new Logger\Monolog\Handler\MysqlHandler());
		
		if ($strType == 'error') {
			self::$booError = true;
			$objMonolog->error($strErrorMessage, $arrDataDump);
		} elseif ($strType == 'info') $objMonolog->info($strErrorMessage, $arrDataDump);

	}//\\ logSendPost
	
	/**
	 * VKAction::sendPost()
	 * Публикует пост в ВК.
	 * 
	 * @param integer $intTaskID ID задачи
	 * @return void
	 */
	public static function sendPost($intTaskID) {
		
		$arrFiles = $arrUploadFiles = $arrUploaderFiles = [];
		$objTask = DB::table('tasks')->where('id', $intTaskID)->first();
		$objGroup = DB::table('groups')->where('id', $objTask->group_id)->first();
		
		DB::table('tasks')->where('id', $intTaskID)->update([
			'status' => 'Задача запущена',
			'is_error' => 'N',
			'error' => '',
		]);
		
		// Получим список файлов по указанному пути
		if ($objTask->type_files == 'gif') {
			$strMaskFiles = $objTask->patch.'/*.{gif,GIF}';
		} elseif ($objTask->type_files == 'img') {
			$strMaskFiles = $objTask->patch.'/*.{jpg,JPG,jpeg,JPEG,png,PNG}';
		}//\\ if

		foreach (glob($strMaskFiles, GLOB_BRACE) as $strF) {
			$arrFiles[] = $strF;
		}//\\ foreach

		// Перемешаем массив и получим массив ключей случайных файлов
		shuffle($arrFiles);
		if (count($arrFiles) < $objTask->cnt) $objTask->cnt = count($arrFiles);
		$arrKeyFiles = array_rand($arrFiles, $objTask->cnt);
		if (!is_array($arrKeyFiles)) $arrKeyFiles = [$arrKeyFiles];
		
		if (count($arrKeyFiles)) {
			foreach ($arrKeyFiles as $intKey) {
				$arrUploadFiles[] = $arrFiles[$intKey];
			}//\\ foreach
		}//\\ if
		unset($arrFiles);
		
		if (!strlen($objGroup->access_token)) {
			self::logSendPost('error', $intTaskID, 'Нет access_token.', ['objTask' => (array)$objTask, 'objGroup' => (array)$objGroup]);
			return false;
		}//\\ if

		if (count($arrUploadFiles)) {
			DB::table('tasks')->where('id', $intTaskID)->update(['status' => 'Выбрано '.count($arrUploadFiles).' файлов']);
			
			$objVK = new VK($objGroup->app_id, $objGroup->api_secret, $objGroup->access_token);
			$objVK->setApiVersion('5.60');
			
			if ($objTask->type_files == 'gif') { // Загрузка в документы
				$intNum = 1;
				foreach ($arrUploadFiles as $strNameFile) {
					// Получим сервер для загрузки
					$arrR = $objVK->api('docs.getUploadServer', [
						'group_id' => $objGroup->group_vk_id
					]);
					sleep(5);
					if (isset($arrR['response']['upload_url']) && strlen($arrR['response']['upload_url'])) {
						$arrInfoFiles = pathinfo($strNameFile);
						$strNewFileName = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)).'.'.$arrInfoFiles['extension'];
						DB::table('tasks')->where('id', $intTaskID)->update(['status' => 'Получен сервер загрузки для файла №'.$intNum]);

						$objCurl = curl_init();
						curl_setopt($objCurl, CURLOPT_URL, $arrR['response']['upload_url']);
						curl_setopt($objCurl, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
						curl_setopt($objCurl, CURLOPT_HEADER, 0);
						curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($objCurl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
						curl_setopt($objCurl, CURLOPT_POSTFIELDS, ['file' => new \CURLFile($strNameFile, mime_content_type($strNameFile), $strNewFileName)]);
						curl_setopt($objCurl, CURLOPT_SSL_VERIFYPEER, false);
						$strResponse = curl_exec($objCurl);
						curl_close($objCurl);
						
						$arrResponse = json_decode($strResponse, true);

						DB::table('tasks')->where('id', $intTaskID)->update(['status' => 'Файл №'.$intNum.' загружен на сервер']);

						sleep(5);
						
						if (isset($arrResponse['file']) && strlen($arrResponse['file'])) {
							DB::table('tasks')->where('id', $intTaskID)->update(['status' => 'Файл №'.$intNum.' успешно загружен на сервер']);
							
							$arrR = $objVK->api('docs.save', [
								'file' => $arrResponse['file']
							]);
							sleep(5);
							if (isset($arrR['response']) && count($arrR['response'])) {
								$arrRT = head($arrR['response']);
	
								if (isset($arrRT['id']) && intval($arrRT['id'])) {
									$arrRT['type'] = 'doc';
									$arrUploaderFiles[] = $arrRT;
									DB::table('tasks')->where('id', $intTaskID)->update(['status' => 'Сохранен файл №'.$intNum]);
								} else self::logSendPost('error', $intTaskID, 'У загруженного документа нет ID.', ['strNameFile' => $strNameFile, 'response' => $arrR]);
							} else self::logSendPost('error', $intTaskID, 'Ошибка docs.save.', ['strNameFile' => $strNameFile, 'response' => $arrR]);
						} else self::logSendPost('error', $intTaskID, 'Документ не загрузился на сервер.', ['strNameFile' => $strNameFile, 'response' => $arrResponse]);
					} else self::logSendPost('error', $intTaskID, 'Не получен сервер для загрузки документа.', ['group_id' => $objGroup->group_vk_id, 'response' => $arrR]);
					$intNum++;
					
					if (self::$booError) break;
				}//\\ foreach
			} elseif ($objTask->type_files == 'img') { // Загрузка в пост
				$intNum = 1;
				foreach ($arrUploadFiles as $strNameFile) {
					// Получим сервер для загрузки
					$arrR = $objVK->api('photos.getWallUploadServer', [
						'group_id' => $objGroup->group_vk_id
					]);
					sleep(5);
					if (isset($arrR['response']['upload_url']) && strlen($arrR['response']['upload_url'])) {
						$arrInfoFiles = pathinfo($strNameFile);
						$strNewFileName = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)).'.'.$arrInfoFiles['extension'];
						DB::table('tasks')->where('id', $intTaskID)->update(['status' => 'Получен сервер загрузки для файла №'.$intNum]);

						$objCurl = curl_init();
						curl_setopt($objCurl, CURLOPT_URL, $arrR['response']['upload_url']);
						curl_setopt($objCurl, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
						curl_setopt($objCurl, CURLOPT_HEADER, 0);
						curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($objCurl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
						curl_setopt($objCurl, CURLOPT_POSTFIELDS, ['photo' => new \CURLFile($strNameFile, mime_content_type($strNameFile), $strNewFileName)]);
						curl_setopt($objCurl, CURLOPT_SSL_VERIFYPEER, false);
						$strResponse = curl_exec($objCurl);
						curl_close($objCurl);
						
						$arrResponse = json_decode($strResponse, true);
						
						DB::table('tasks')->where('id', $intTaskID)->update(['status' => 'Файл №'.$intNum.' загружен на сервер']);
						
						sleep(5);
						
						if (isset($arrResponse['server']) && isset($arrResponse['photo']) && isset($arrResponse['hash'])) {
							DB::table('tasks')->where('id', $intTaskID)->update(['status' => 'Файл №'.$intNum.' успешно загружен на сервер']);

							$arrR = $objVK->api('photos.saveWallPhoto', [
								'group_id' => $objGroup->group_vk_id,
								'photo' => $arrResponse['photo'],
								'server' => $arrResponse['server'],
								'hash' => $arrResponse['hash'],
							]);
							sleep(5);
							if (isset($arrR['response']) && count($arrR['response'])) {
								$arrRT = head($arrR['response']);
	
								if (isset($arrRT['id']) && intval($arrRT['id'])) {
									$arrRT['type'] = 'photo';
									//$arrRT['owner_id'] = '-'.$arrRT['owner_id'];
									$arrUploaderFiles[] = $arrRT;
									DB::table('tasks')->where('id', $intTaskID)->update(['status' => 'Сохранен файл №'.$intNum]);
								} else self::logSendPost('error', $intTaskID, 'У загруженной фотографии нет ID.', ['strNameFile' => $strNameFile, 'response' => $arrR]);
							} else self::logSendPost('error', $intTaskID, 'Ошибка photos.saveWallPhoto.', ['strNameFile' => $strNameFile, 'response' => $arrR]);
						} else self::logSendPost('error', $intTaskID, 'Фотография не загрузилась на сервер.', ['strNameFile' => $strNameFile, 'response' => $arrResponse]);
					} else self::logSendPost('error', $intTaskID, 'Не получен сервер для загрузки фотографии.', ['group_id' => $objGroup->group_vk_id, 'response' => $arrR]);
					$intNum++;
					
					if (self::$booError) break;
				}//\\ foreach
			}//\\ if
			
			// Опубликуем пост
			if (count($arrUploaderFiles)) {
				$arrAttachments = [];
				foreach($arrUploaderFiles as $arrF)
					$arrAttachments[] = $arrF['type'].$arrF['owner_id'].'_'.$arrF['id'];
				
				$params = [
					'access_token' => $objGroup->access_token,
					'owner_id' => '-'.$objGroup->group_vk_id,
					'from_group' => 1,
					'attachments' => implode(',', $arrAttachments),
					'v' => '5.60',
				];
				
				$strR = file_get_contents('https://api.vk.com/method/wall.post', false, stream_context_create(array(
					'http' => array(
						'method'  => 'POST',
						'header'  => 'Content-type: application/x-www-form-urlencoded',
						'content' => http_build_query($params)
					)
				)));
				$arrR = json_decode($strR, true);
				
				if (isset($arrR['response']['post_id']) && intval($arrR['response']['post_id'])) {
					
					self::logSendPost('info', $intTaskID, 'Опубликован пост https://vk.com/wall-'.$objGroup->group_vk_id.'_'.intval($arrR['response']['post_id']));
					DB::table('tasks')->where('id', $intTaskID)->update([
						'status' => 'Опубликован пост https://vk.com/wall-'.$objGroup->group_vk_id.'_'.intval($arrR['response']['post_id']),
						'is_error' => 'N',
						'error' => '',
					]);
					
					// Удалим ненужные уже файлы
					foreach ($arrUploadFiles as $strF) @unlink($strF);
					
				} else self::logSendPost('error', $intTaskID, 'В ВК пост не создался.', ['params' => $params, 'response' => $arrR]);
			} else self::logSendPost('error', $intTaskID, 'На ВК файлы не загрузились.');
			
		} else self::logSendPost('error', $intTaskID, 'Нет файлов для загрузки.');

	}//\\ sendPost
}//\\ VKAction
