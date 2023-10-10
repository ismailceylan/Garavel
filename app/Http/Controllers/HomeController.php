<?php

namespace App\Http\Controllers;

use Garavel\Routing\Controller;

class HomeController extends Controller
{
	public function index()
	{
		return "Welcome home.";
	}
}
