<?php

namespace App\Http\Controllers;

use App\Objects\Prompt;
use App\Objects\PromptSegment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class AdminBaseController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $message = '';
    public $message_role = 'info';
    public $promptId = '';
    public $pathId = '';
    public $segmentId = '';
    public $questionId = '';
    public $nav = '';

    public function __construct()
    {
        $this->message = '';
        $this->middleware('auth');
    }

    public function adminView($view, $data = [])
    {
        $data['message'] = $this->message;
        $data['message_role'] = $this->message_role;
        $selection_ids = [ 'pathId', 'promptId', 'segmentId', 'questionId', 'nav' ];
        foreach ($selection_ids as $id) {
            if (!array_key_exists($id, $data)) $data[$id] = $this->$id;
        }
        Log::debug($this->message);
        return view($view, $data);
    }
}
