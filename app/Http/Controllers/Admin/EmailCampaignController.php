<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailCampaignController extends Controller
{
    public function index() { return view('admin.email.campaigns.index'); }
    public function create() { return view('admin.email.campaigns.create'); }
    public function store(Request $request) { return response()->json(['status' => 'saved']); }
    public function edit($id) { return view('admin.email.campaigns.edit', compact('id')); }
    public function update(Request $request, $id) { return response()->json(['status' => 'updated']); }
    public function destroy($id) { return response()->json(['status' => 'deleted']); }
    public function send($id) { return response()->json(['status' => 'sent']); }
}