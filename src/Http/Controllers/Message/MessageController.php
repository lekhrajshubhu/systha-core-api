<?php
namespace Systha\Core\Http\Controllers\Message;

use Illuminate\Http\Request;
use Systha\Core\Http\Controllers\BaseController;
use Systha\Core\Models\ChatConversation;

class MessageController extends BaseController
{
    public function show(Request $request, $conversationId){
    $conv = ChatConversation::find($conversationId);
    $user = auth('webContact')->user();
    return view($this->viewPath . '::frontend.dashboard.message.message', compact('conv','user'));
}
}

