<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function test()
    {
        $abc = DB::select('select * from CC_BUS');
        var_dump($abc);
    }
}
