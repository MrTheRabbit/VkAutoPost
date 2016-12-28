<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use VK;
use VKException;

class GroupsController extends Controller {
	
	private $arrRules = [
		'notify' => 'Пользователь разрешил отправлять ему уведомления',
		'friends' => 'Доступ к друзьям',
		'photos' => 'Доступ к фотографиям',
		'audio' => 'Доступ к аудиозаписям',
		'video' => 'Доступ к видеозаписям',
		'pages' => 'Доступ к wiki-страницам',
		'status' => 'Доступ к статусу пользователя',
		'notes' => 'Доступ к заметкам пользователя',
		'messages' => 'Доступ к расширенным методам работы с сообщениями',
		'wall' => 'Доступ к обычным и расширенным методам работы со стеной',
		'ads' => 'Доступ к расширенным методам работы с рекламным API',
		'docs' => 'Доступ к документам',
		'groups' => 'Доступ к группам пользователя',
		'notifications' => 'Доступ к оповещениям об ответах пользователю',
		'stats' => 'Доступ к статистике групп и приложений пользователя, администратором которых он является',
		'email' => 'Доступ к email пользователя',
		'market' => 'Доступ к товарам',
	];
	
	public function addItem(Request $request) {
		$strToken = $request->input('f_access_token');
		$strApiSettings = '';
		if (count($request->input('f_api_settings'))) $strApiSettings = implode(',', $request->input('f_api_settings'));
		$intID = DB::table('groups')->insertGetId([
			'app_id' => $request->input('f_app_id'),
			'api_secret' => $request->input('f_api_secret'),
			'access_token' => (strlen($strToken) ? $strToken : ''),
			'api_settings' => $strApiSettings,
		]);

		// Если получем в форме токен - получим инфу о группе
		if (strlen($request->input('f_app_id')) && strlen($request->input('f_api_secret')) && strlen($request->input('f_access_token'))) {
			$objVK = new VK($request->input('f_app_id'), $request->input('f_api_secret'), $request->input('f_access_token'));
			$objVK->setApiVersion('5.60');
				
			$arrInfoGroup = $objVK->api('apps.get', [
				'app_id' => $request->input('f_app_id'),
				'platform' => 'web',
			]);
			if (isset($arrInfoGroup['response']['items']) && count($arrInfoGroup['response']['items'])) {
				$arrResponse = head($arrInfoGroup['response']['items']);
				DB::table('groups')->where('id', $intID)->update(['title' => $arrResponse['title'], 'group_vk_id' => $arrResponse['author_group']]);
			}//\\ if
		}//\\ if
		
		return redirect()->route('groups::list');
	}//\\ addItem
	
	public function editItem(Request $request, $intID = 0) {
		$strAuthorizeUrl = '#';
		$objGroup = null;

		if ($intID) {
			$objGroup = DB::table('groups')->where('id', $intID)->first();
			
			$objGroup->api_settings = explode(',', $objGroup->api_settings);
			
			$arrApiSettings = $objGroup->api_settings;
			$arrApiSettings[] = 'offline';
			
			$objVK = new VK($objGroup->app_id, $objGroup->api_secret);
			$objVK->setApiVersion('5.60');
			
			// https://oauth.vk.com/authorize?client_id=5775743&scope=friends,photos,status,wall,docs,groups,stats,email,offline&redirect_uri=blank.html&display=popup&response_type=token
			/*if ($request->has('code')) {
				
				$arrAccessToken = $objVK->getAccessToken($request->input('code'), route('groups::edit', [$intID]));
				
				$objVK->setAccessToken($arrAccessToken['access_token']);
				
				$strTitle = $strGroupVkID = '';
				$arrInfoGroup = $objVK->api('apps.get', [
					'app_id' => $objGroup->app_id,
					'platform' => 'web',
				]);
				//dd($arrInfoGroup['response']['items']);
				if (isset($arrInfoGroup['response']['items']) && count($arrInfoGroup['response']['items'])) {
					$arrResponse = head($arrInfoGroup['response']['items']);
					
					$strTitle = $arrResponse['title'];
					$strGroupVkID = $arrResponse['author_group'];
				}//\\ if
	
				DB::table('groups')->where('id', $intID)->update(['access_token' => $arrAccessToken['access_token'], 'title' => $strTitle, 'group_vk_id' => $strGroupVkID]);
				
				return redirect()->route('groups::edit', [$intID]);
				
			} else {*/
			//$strAuthorizeUrl = $objVK->getAuthorizeURL(implode(',', $arrApiSettings), route('groups::edit', [$intID]));
			$strAuthorizeUrl = 'https://oauth.vk.com/authorize?client_id=5775743&scope='.implode(',', $arrApiSettings).',offline&redirect_uri=blank.html&display=popup&response_type=token';
			//}//\\ if
		}//\\ if
		
		
		return view('groups_edit', [
			'title' => ($intID ? 'Редактировать группу' : 'Добавить группу'), 
			'group' => $objGroup, 
			'form_action' => ($intID ? route('groups::save', [$intID]) : route('groups::add_post')), 
			'authorize_url' => $strAuthorizeUrl,
			'list_api_settings' => $this->arrRules,
		]);
	}//\\ editItem
	
	public function saveItem(Request $request, $intID) {
		$strApiSettings = '';
		if (count($request->input('f_api_settings'))) $strApiSettings = implode(',', $request->input('f_api_settings'));
		
		DB::table('groups')->where('id', $intID)->update([
			'app_id' => $request->input('f_app_id'),
			'api_secret' => $request->input('f_api_secret'),
			'api_settings' => $strApiSettings,
			'access_token' => $request->input('f_access_token'),
		]);

		// Если получем в форме токен - получим инфу о группе
		if (strlen($request->input('f_app_id')) && strlen($request->input('f_api_secret')) && strlen($request->input('f_access_token'))) {
			$objVK = new VK($request->input('f_app_id'), $request->input('f_api_secret'), $request->input('f_access_token'));
			$objVK->setApiVersion('5.60');
				
			$arrInfoGroup = $objVK->api('apps.get', [
				'app_id' => $request->input('f_app_id'),
				'platform' => 'web',
			]);
			if (isset($arrInfoGroup['response']['items']) && count($arrInfoGroup['response']['items'])) {
				$arrResponse = head($arrInfoGroup['response']['items']);
				DB::table('groups')->where('id', $intID)->update(['title' => $arrResponse['title'], 'group_vk_id' => (isset($arrResponse['author_group']) ? $arrResponse['author_group'] : '')]);
			}//\\ if
		}//\\ if
		
		return redirect()->route('groups::list');
	}//\\ saveItem
	
	public function deleteItem(Request $request, $intID) {
		DB::table('groups')->where('id', $intID)->delete();
		
		return redirect()->route('groups::list');
	}//\\ deleteItem
	
	public function listItems() {
		
		$arrGroups = DB::table('groups')->orderBy('id', 'desc')->get();
		
		return view('groups', ['title' => 'Группы ВК', 'groups' => $arrGroups]);
	}//\\ listItems
	
}

