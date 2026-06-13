<?php

namespace App\Http\Controllers;

class ProcessController extends Controller
{
    public function processEmails()
    {
        return response()->json(['status' => 'ok']);
    }

    public function processAutomation()
    {
        return response()->json(['status' => 'ok']);
    }

    public function emailQueueStatus()
    {
        return response()->json(['status' => 'ok']);
    }
}
