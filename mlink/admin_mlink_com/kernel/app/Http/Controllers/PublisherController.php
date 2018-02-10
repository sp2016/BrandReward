<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

class PublisherController extends Controller
{
    public function getList(Request $request)
    {
        $entity = new PublisherQueryEnity($request->input());
    }
}