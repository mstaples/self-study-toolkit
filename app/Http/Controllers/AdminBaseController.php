<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class AdminBaseController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $message = '';
    protected $message_role = 'alert';
    protected $pathId = '';
    protected $promptId = '';
    protected $segmentId = '';
    protected $questionId = '';

    public function __construct()
    {
        $this->message = '';
        $this->middleware('auth');
    }

    public function adminView($view, $data)
    {
        $data['message'] = $this->message;
        $data['message_role'] = $this->message_role;
        $selection_ids = [ 'pathId', 'promptId', 'segmentId', 'questionId' ];
        foreach ($selection_ids as $id) {
            if (!array_key_exists($id, $data)) {
                $data[$id] = $this->$id;
            }
        }
        Log::debug("adminView($view, data) + message = ".$this->message);
        return view($view, $data);
    }
}
