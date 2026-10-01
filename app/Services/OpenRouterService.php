<?php
namespace App\Services;

use MoeMizrak\LaravelOpenRouter\LaravelOpenRouter;
use MoeMizrak\LaravelOpenRouter\Data\ChatData;
use MoeMizrak\LaravelOpenRouter\Data\MessageData;
use MoeMizrak\LaravelOpenRouter\Enum\RoleType;
use App\Models\CurriculumFile;

class OpenRouterService
{
    protected $openRouter;
    
    public function __construct(LaravelOpenRouter $openRouter)
    {
        $this->openRouter = $openRouter;
    }
    
    public function chat(string $message, ?int $fileId = null, array $history = [])
    {
        $systemPrompt = $this->buildSystemPrompt($fileId);
        
        $messages = [
            new MessageData(
                role: RoleType::SYSTEM,
                content: $systemPrompt
            )
        ];
        
        // إضافة history
        foreach (array_slice($history, -5) as $msg) {
            $messages[] = new MessageData(
                role: $msg['role'] === 'user' ? RoleType::USER : RoleType::ASSISTANT,
                content: $msg['content']
            );
        }
        
        $messages[] = new MessageData(
            role: RoleType::USER,
            content: $message
        );
        
        $chatData = new ChatData(
            messages: $messages,
            model: 'openrouter/free',
            max_tokens: 2000,
            temperature: 0.7
        );
        
        $response = $this->openRouter->chatRequest($chatData);
        
        return $response->choices[0]->message->content;
    }
    
    private function buildSystemPrompt(?int $fileId): string
    {
        $prompt = "You are a helpful AI tutor for a school management system. ";
        
        if ($fileId) {
            $file = CurriculumFile::find($fileId);
            if ($file) {
                $prompt .= "The student is studying: {$file->name}\n";
                $prompt .= "Year: {$file->year}, Subject: {$file->subject}\n";
            }
        }
        
        return $prompt;
    }
}