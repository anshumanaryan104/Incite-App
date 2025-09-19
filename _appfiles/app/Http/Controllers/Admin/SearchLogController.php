<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\SearchLog;

use Illuminate\Http\Request;

class SearchLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {      
        try {
            $result = SearchLog::getLists($request->all());
            $data['result'] = \Helpers::arrayPaginator($result,$request);
            // echo json_encode($data);exit;
            return view('admin/search-log.index',$data);
        } 
        catch (\Exception $ex) {
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }
}
