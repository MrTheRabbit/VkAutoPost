<?

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Log;
use Logger;

class TasksController extends Controller {
	
	private $arrRules = [
		'notify' => 'Пользователь разрешил отправлять ему уведомления',
	];
	
	public function addItem(Request $request) {
		$intTime = intval($request->input('f_time_hh')) *60 + intval($request->input('f_time_mm'));

		$intGroupsID = DB::table('tasks')->insertGetId([
			'group_id' => intval($request->input('f_group_id')),
			'action' => intval($request->input('f_action')),
			'time' => $intTime,
			'cnt' => intval($request->input('f_cnt')),
			'patch' => $request->input('f_patch'),
			'type_files' => $request->input('f_type_files'),
			'status' => '',
			'is_error' => 'N',
			'error' => '',
		]);
		
		return redirect()->route('tasks::list');
	}//\\ addItem
	
	public function editItem(Request $request, $intID = 0) {
		
		$arrListActions = [
			1 => 'Пост на стену в группу',
			2 => 'Загрузка фотографий в альбом пользователя',
		];
		
		$arrListUserFolders = [];
		
		$objTask = null;
		if ($intID) {
			$objTask = DB::table('tasks')->where('id', $intID)->first();
		}//\\ if
		
		$arrGroups = DB::table('groups')->select('id', 'title')->orderBy('id', 'desc')->get();
		
		//$objTask->time_hh = $objTask->time_mm = 0;
		if (isset($objTask->time)) {
			$objTask->time_hh = floor($objTask->time / 60);
			$objTask->time_mm = $objTask->time % 60;
		}//\\ if

		$arrF = scandir('/');
		$arrPatchs = [];
		foreach ($arrF as $strT) {
			if ($strT != '.' && $strT != '..' && is_dir('/'.$strT)) {
				$arrPatchs[] = $strT;
			}//\\ if
		}//\\ foreach
		
		return view('tasks_edit', [
			'title' => ($intID ? 'Редактировать задачу' : 'Добавить задачу'), 
			'task' => $objTask, 
			'form_action' => ($intID ? route('tasks::save', [$intID]) : route('tasks::add_post')), 
			'list_groups' => $arrGroups,
			'list_root_patch' => $arrPatchs,
			'list_actions' => $arrListActions,
			'user_folders' => $arrListUserFolders,
		]);
	}//\\ editItem
	
	public function saveItem(Request $request, $intID) {
		$intTime = intval($request->input('f_time_hh')) *60 + intval($request->input('f_time_mm'));
		
		DB::table('tasks')->where('id', $intID)->update([
			'group_id' => intval($request->input('f_group_id')),
			'action' => intval($request->input('f_action')),
			'time' => $intTime,
			'cnt' => intval($request->input('f_cnt')),
			'patch' => $request->input('f_patch'),
			'type_files' => $request->input('f_type_files'),
		]);
		
		return redirect()->route('tasks::list');
	}//\\ saveItem
	
	public function deleteItem(Request $request, $intID) {
		DB::table('tasks')->where('id', $intID)->delete();
		
		return redirect()->route('tasks::list');
	}//\\ deleteItem
	
	public function listItems() {
		
		/*$objMonolog = Log::getMonolog();
		$objMonolog->pushHandler(new Logger\Monolog\Handler\MysqlHandler());
		$objMonolog->info('Тестовый2', ['a' => '111', 'b' => '222']);*/
		
		$arrTasks = DB::table('tasks')->orderBy('time', 'asc')->get();
		
		$arrGroups = DB::table('groups')->pluck('title', 'id');
		
		return view('tasks', ['title' => 'Группы ВК', 'tasks' => $arrTasks, 'all_groups' => $arrGroups]);
	}//\\ listItems
	
}

