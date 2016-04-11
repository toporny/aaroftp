<?php namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;


class HomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Home Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders your application's "dashboard" for users that
	| are authenticated. Of course, you are free to change or remove the
	| controller as you wish. It is just here to get your app started!
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		$available_sorts = ['user_firstname', 'user_email', 'pname', 'date_ordered', 'date_installed', 'transaction_id'];

		$sort = $request->input('sort','date_ordered');
		$tmp = array_search($sort, $available_sorts);
		if ($tmp === false) $sort = 'date_ordered desc';

		$order = $request->input('order','desc');
		$tmp = array_search($order, ['asc','desc']);
		if ($tmp === false) $order = 'asc';

		$status_filter = $request->input('status_filter','all');
		$date_filter = $request->input('date_filter','');

		$records_on_page = $request->input('records_on_page','100');
		$text_search = $request->input('text_search','');

		$good_date = preg_match ("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date_filter ) ;

		$tmp = array_search($status_filter, ['installed','waiting','problem','cancelled', 'all']);
		if ($tmp === false) $status_filter = 'all';

		$sql_query = "SELECT * FROM ";
		$sql_query .= "(SELECT CONCAT( '[ ', count(*), ' ] - All') AS count_all FROM transactions ) AS t, ";
		$sql_query .= "(SELECT CONCAT( '[ ', count(*), ' ] - Waiting') as count_waiting FROM transactions WHERE status='waiting' ) as w, ";
		$sql_query .= "(SELECT CONCAT( '[ ', count(*), ' ] - Installed') as count_installed FROM transactions WHERE status='installed' ) as e, ";
		$sql_query .= "(SELECT CONCAT( '[ ', count(*), ' ] - Problems') as count_problem FROM transactions WHERE status='problem' ) as r, ";
		$sql_query .= "(SELECT CONCAT( '[ ', count(*), ' ] - Cancelled') as count_cancelled FROM transactions WHERE status='cancelled') as dt ";

		$counts_descriptions = DB::select($sql_query);


		$transactions = DB::table('transactions')
		->leftJoin('products', 'transactions.uicode', '=', 'products.uicode')
		->select('transactions.*', 'products.directory'
				, 'products.id as ready_to_install'
				, DB::raw('(
					FIND_IN_SET ("ftp_path_ok", statuses_features) &
					FIND_IN_SET ("autoinstall_started", statuses_features) &
					FIND_IN_SET ("autoinstall_pizfile_uploaded", statuses_features) ) as ready_to_install,

					DATEDIFF(now(), date_ordered ) as wait_days,
					FIND_IN_SET ("ftp_path_ok", statuses_features) as ftp_path_ok,
					FIND_IN_SET ("aweber", statuses_features) as aweber,
					FIND_IN_SET ("free_product", statuses_features) as free_product
					')
				)
		->orderBy('transactions.date_ordered', 'desc');
		
		if ($status_filter != 'all'){
			$transactions->where('status', '=', $status_filter);
		}

		if ($text_search != '') {
			 // try to find only name or surname
			if ((strlen($text_search)>6) && (strpos($text_search,' ')))  {
				$aTmp = explode(' ', $text_search);
				$transactions->whereRaw( "LOWER(`user_firstname`) like ?", array( '%'.strtolower($aTmp[0]).'%' ) );
				$transactions->whereRaw( "LOWER(`user_lastname`) like ?", array( '%'.strtolower($aTmp[1]).'%' ) );

			} else { // try to find everything else
				$transactions->whereRaw( "LOWER(`user_email`) like ?", array( '%'.strtolower($text_search).'%' ) );
				$transactions->orWhereRaw( "LOWER(`website`) like ?", array( '%'.strtolower($text_search).'%' ) );
				$transactions->orWhereRaw( "LOWER(`user_firstname`) like ?", array( '%'.strtolower($text_search).'%' ) );
				$transactions->orWhereRaw( "LOWER(`user_lastname`) like ?", array( '%'.strtolower($text_search).'%' ) );
			}
		}

		if ($good_date > 0)  {
			$transactions->where('date_ordered', '>=', $date_filter) ;
			$next_day = date('Y-m-d',date(strtotime("+1 day", strtotime($date_filter))));
			$transactions->where('date_ordered', '<', $next_day);
		}

		$transactions = $transactions->paginate($records_on_page);

		$pass_variables = array(
								'sort'=> $sort,
								'order'=> $order,
								'date_filter' => $date_filter,
								'auth_user' => Auth::user()->role,
								'text_search' => $text_search,
								'status_filter'=> $status_filter,
								'transactions' =>  $transactions,
								'items' =>  $transactions,
								'product_installed_email_template' =>  url()."/email_templates/product_installed",
								'counts_descriptions' => $counts_descriptions
								);
		return view('home', $pass_variables );
	}
}
