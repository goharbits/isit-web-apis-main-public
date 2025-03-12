<?php

namespace App\Services;

use App\Events\MessageEvent;
use App\Facades\GlobalHelper;
use App\Interfaces\ChatInterface;
use App\Models\Attachment;
use App\Models\Conversation;
use App\Models\Message;

class ChatService implements ChatInterface
{
    public function __construct(
        private Message $message,
        private Conversation $conversation,
        private Attachment $attachment
    ) {}


    public function sendMessage($data)
    {
        $message = $this->message->create([
            'sender_id' => $data['sender_id'],
            'receiver_id' => $data['receiver_id'],
            'conversation_id' => $data['conversation_id'],
            'message' => $data['message']
        ]);

        $message = $message->toArray();

        if (isset($data['attachment'])) {
            $message['attachment'] = GlobalHelper::uploadFile($data['attachment'], "uploads/attachments");
            $attachment = $this->attachment->create([
                'path' => $message['attachment'],
                'type' => $data['type'],
                'message_id' => $message['id']
            ]);
            $message['attachment'] = $attachment->toArray();
        }

        event(new MessageEvent($message));

        return $message;
    }
    public function getMessages($id)
    {
        $conversation = $this->conversation->with(['messages.attachment', 'sender.images', 'receiver.images'])
            ->where('id', $id)
            ->first();
        return $conversation;
    }
    public function getConversation($id)
    {
        $conversation = $this->conversation->with([
            'sender.images',
            'receiver.images',
            'messages'
        ])
            ->where('sender_id', $id)
            ->orWhere('receiver_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();


        // $conversation = $this->conversation->with([
        //     'sender.images',
        //     'receiver.images',
        //     'messages',
        //     'latestMessage'
        // ])
        // ->where('sender_id', $id)
        // ->orWhere('receiver_id', $id)
        // ->orderByDesc('latestMessage.created_at')
        // ->get();

        return $conversation;
    }
}
