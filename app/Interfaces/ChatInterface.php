<?php

namespace App\Interfaces;

interface ChatInterface
{
    public function sendMessage($data);
    public function getMessages($id);
    public function getConversation($id);
}
