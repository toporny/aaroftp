<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class HelperController extends Controller {

	/**
	 * Display a file for wget.
	 *
	 * @return Response
	 */
	public function showProductsFileForWget()
	{

		// cd "products_local_storage" && wget -O qwe.txt http://aarondev.enbe.pl/helper/showProductsFileForWget && wget -N -i qwe.txt && rm qwe.txt

		// cd "products_local_storage" && wget -O qwe.txt http://aaroftp.local/helper/showProductsFileForWget && wget -N -i qwe.txt && rm qwe.txt
		 
		$sql_query  = "SELECT TRIM(TRAILING '.txt' FROM txt_file_url) as txt_file_url ";
		$sql_query .= "FROM products ";

		$results = DB::select($sql_query);
		if (is_array($results)) {
			foreach ($results as $result) {
				echo $result->txt_file_url.".zip\n";
				echo $result->txt_file_url.".txt\n";
			}
		}
	}


	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
