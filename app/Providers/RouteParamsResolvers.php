<?php

namespace App\Providers;

use Garavel\Routing\ParamResolver;
use App\Http\Controllers\Api\BaseController;

class RouteParamsResolvers extends ParamResolver
{
	public function register()
	{
		static::resolve(
			\JDB\Database::class,
			fn( $_, $routeArgs ) => $this->db( $routeArgs->dbname )
		);

		static::resolve(
			\JDB\Table::class,
			fn( $_, $routeArgs ) => $this->db( $routeArgs->dbname )->table( $routeArgs->tablename )
		);

		static::resolve(
			\JDB\Row::class,
			fn( $_, $routeArgs ) =>
				$this
					->db( $routeArgs->dbname )
					->table( $routeArgs->tablename )
					->find( $routeArgs->id )
		);
	}

	private function db( $name )
	{
		return \JDB\JDB::connect(
			BaseController::DATAPATH . DIRECTORY_SEPARATOR . $name
		);
	}
}
