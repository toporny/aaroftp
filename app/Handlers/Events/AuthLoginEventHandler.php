<?php namespace App\Handlers\Events;


use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use DB;
use App\User;

class AuthLoginEventHandler {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  Events  $event
	 * @return void
	 */
	public function handle(User $user, $remember)
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		    $ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		    $ip = $_SERVER['REMOTE_ADDR'];
		}		

		$data['ip'] = $ip;

		try
		{
	        $sql_query  = "SELECT name, id FROM users WHERE id = '".$user->id."'";
	        $results = DB::select($sql_query);
			$id = DB::table('log_users_log')->insert(
						    ['ip' => $ip,
						    'user_id' => $results[0]->id,
						    'timestamp' =>  date("Y-m-d H:i:s"),
						    'user_name' => $results[0]->name]
						);
		}
		catch(\Exception $ex)
		{
			dd("problem with inserting into log_users_log table");
			exit;
		}

	}

}
