<?php

namespace App\Console\Commands;

use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserActive extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'user:active';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Comando para validar usuarios activos';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{

		try {

			$data = DB::select('SELECT * FROM HCW_USUARIO_ACTIVO WHERE ESTADO = 1');

			foreach ($data as $key => $value) {
				$horaActual = new DateTime();
				$horaUsuario = new DateTime($value->ultima_conexion);
				$diferencia = ($horaActual->getTimestamp() - $horaUsuario->getTimestamp());
				// Storage::append('logs.txt', $horaActual->format('Y-m-d H:i:s') . ' - ' . $horaUsuario->format('Y-m-d H:i:s') . ' - ' . $diferencia);
				if ($diferencia > 30) {
					// cambiar estado a 0
					DB::update('UPDATE HCW_USUARIO_ACTIVO SET ESTADO = 0 WHERE USER_ID = ?', [$value->user_id]);
					Storage::append('logs.txt', '[' . $horaUsuario->format('Y-m-d H:i:s') . '] => Usuario ' . $value->user_id . ' desconectado, inactivo por ' . $diferencia . ' segundos');
				}
			}
		} catch (\Throwable $th) {
			Storage::append('logs.txt', '[' . $horaUsuario->format('Y-m-d H:i:s') . '] => ERROR ' . $th->getMessage());
		}
	}
}
