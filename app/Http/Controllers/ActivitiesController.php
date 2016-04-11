<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Http\Request;

class ActivitiesController extends Controller {


	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->beforeFilter(function() {
			if (Auth::user()->role != 'admin')
				return view('only_for_admin');
		});
	}


	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{

		$records_on_page = 100;

		$transactions = DB::table('log_installations')
		->select(
			'log_installations.user_id',
			'log_installations.user_name',
			'log_installations.timestamp',
			'log_installations.transaction_id',
			'log_installations.status',
			'transactions.pname',
			'transactions.user_email',
			'transactions.date_ordered',
			'transactions.date_installed',
			DB::raw('DATEDIFF(transactions.date_installed, transactions.date_ordered) as days_delay')
			)
		->join('transactions', 'log_installations.transaction_id', '=', 'transactions.id')
		->groupBy('log_installations.transaction_id')
		->groupBy('log_installations.status')
		->orderBy('log_installations.transaction_id', 'desc')
		->orderBy('log_installations.timestamp', 'desc')
		;
		
		$transactions = $transactions->paginate($records_on_page);

		$pass_variables = array(
								'transactions' =>  $transactions
								);

		return view('activities_install', $pass_variables );
	}





	public function userLogs()
	{
		$records_on_page = 100;

		$transactions = DB::table('log_users_log')
		->select( 'timestamp', 'user_name')
		->where('user_id', '>', '2') // don't show toporny_staff and toporny_admin
		->orderBy('log_users_log.id', 'desc');
		
		$transactions = $transactions->paginate($records_on_page);
		$pass_variables = array( 'transactions' => $transactions );
		return view('activities_users_log', $pass_variables );
	}



	public function free_installations()
	{
		$records_on_page = 100;
		$free_installations = DB::table('transactions')
		->select( 'id', 'user_firstname', 'user_lastname', 'user_email', 'statuses_features', 'date_ordered', 'pname')
		->whereRaw("FIND_IN_SET('free_product', statuses_features)")
		->orderBy('transactions.id', 'desc');
		$free_installations = $free_installations->paginate($records_on_page);
		$pass_variables = array( 'free_installations' => $free_installations );
		return view('activities_free_installations', $pass_variables );
	}


	public function globalLogs()
	{
		$records_on_page = 100;

		$transactions = DB::table('log_global')
		->select( 'timestamp', 'status', 'msg')
		->orderBy('log_global.id', 'desc');
		
		$transactions = $transactions->paginate($records_on_page);
		$pass_variables = array( 'transactions' => $transactions );
		return view('activities_global_logs', $pass_variables );
	}

	public function statistics()
	{

		// $sql_query   = "SELECT date(date_ordered) AS date_ordered, COUNT(DISTINCT user_email) as count ";
		// $sql_query  .= "FROM transactions ";
		// $sql_query  .= "WHERE date(date_ordered) >  date_ordered - INTERVAL 30 DAY ";
		// $sql_query  .= "GROUP BY date(date_ordered) ";
		// $sql_query  .= "ORDER BY date_ordered DESC ";

		// $new_cust_30_days = DB::select($sql_query);

		// $new_customers_30_days = array();

		// for ($i=0; $i<30; $i++) {
		//   $timestamp  = mktime(0, 0, 0, date("m")  , date("d") - $i, date("Y"));
		//   $days30[] = date("Y-m-d", $timestamp);;
		// }
		// foreach ($new_cust_30_days as $result) {
		// 	$arr_keys[$result->date_ordered] = $result->count;
		// }
		// foreach ($days30 as $day) {
		// 	(isset($arr_keys[$day])) ? $new_customers_30_days[$day] = $arr_keys[$day] : $new_customers_30_days[$day] = 0;
		// }


		// ============================================================================

		$new_customers_past_months = array();
		$sql_query  = "SELECT DATE_FORMAT(date_ordered, '%Y-%m') AS date_ordered, ";
		$sql_query .= "COUNT(DISTINCT date_ordered) as count ";
		$sql_query .= "FROM transactions ";
		$sql_query .= "WHERE status != 'cancelled' ";
		$sql_query .= "AND status != 'removed' ";
		$sql_query .= "GROUP BY DATE_FORMAT(date_ordered, '%Y-%m') ";
		$sql_query .= "ORDER BY date_ordered DESC ";
		$new_customers_past_months = DB::select($sql_query);

		// ============================================================================

		$new_customers_this_month = array();
		$sql_query = "SELECT date_ordered, user_email, user_firstname, user_lastname, count(*) AS amount_of_products ";
		$sql_query .= "FROM transactions ";
		$sql_query .= "WHERE (date_ordered BETWEEN DATE_FORMAT(NOW() ,'%Y-%m-01') AND NOW() ) ";
		$sql_query .= "AND status != 'cancelled' ";
		$sql_query .= "AND status != 'removed' ";
		$sql_query .= "GROUP BY date_ordered ";
		$sql_query .= "ORDER BY date_ordered DESC ";
		$new_customers_this_month = DB::select($sql_query);

// print "<pre>";
// print_R($new_customers_this_month);
// exit;


		// ============================================================================

		$new_customers_last_30_days = array();
		$sql_query = "SELECT date_ordered, user_email, user_firstname, user_lastname, count(*) AS amount_of_products ";
		$sql_query .= "FROM transactions ";
		$sql_query .= "WHERE DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= date_ordered ";
		$sql_query .= "AND status != 'cancelled' ";
		$sql_query .= "AND status != 'removed' ";
		$sql_query .= "GROUP BY date_ordered ";
		$sql_query .= "ORDER BY date_ordered DESC ";
		$new_customers_last_30_days = DB::select($sql_query);

		$pass_variables = array(
//			'new_customers_30_days' => $new_customers_30_days,
			'new_customers_last_30_days' => $new_customers_last_30_days,
			'new_customers_past_months' => $new_customers_past_months,
			'new_customers_this_month' => $new_customers_this_month
		);
		return view('activities_statistics', $pass_variables );
	}

}
