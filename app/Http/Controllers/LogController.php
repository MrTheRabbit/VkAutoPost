<?

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Date;

class LogController extends Controller {
	
	public function index() {

		$arrLogs = DB::table('logs')->orderBy('id', 'desc')->get();
		
		return view('log', ['title' => 'Логи', 'logs' => $arrLogs]);
	}//\\ listItems
	
}

