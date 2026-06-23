<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SetupController extends Controller
{
    public function setupAuditKit() { return response()->json(['status' => 'ok']); }
    public function setupAuditSequence() { return response()->json(['status' => 'ok']); }

}