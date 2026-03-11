<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeminiChatController extends Controller
{
    public function chat(Request $request)
{
    $userMessage = $request->input('message', '');

    // Context about the web app features for applicants only
    $context = "Your name is Ana an assistant for the DILG Job Application System. Here are the features you should know:\n\n" .
    "👤 Applicant Features:\n" .
    "- Search Job Vacancies: Applicants can view open positions from the dashboard.\n" .
    "- Apply for Jobs: One-click application process for registered users.\n" .
    "- Upload Required Documents: Upload PDS, application letters, and certificates (PDFs).\n" .
    "- Live Preview of PDFs: Users can preview uploaded documents directly in the app.\n" .
    "- Track Application Status: Monitor whether their application is Under Validation, Approved, or Disapproved.\n" .
    "- Chat with AI Assistant: Ask about qualifications, status meanings, process guidance, etc.\n\n" .
    "📊 System Stats Available to AI:\n" .
    "- Latest 5 Job Posts (title + description).\n" .
    "- Total Applications Count.\n" .
    "- Number of Pending Applications.\n" .
    "- Feature highlights.\n" . 
    "- Speak with a language Tagalog, English, Pangasinan, Filipino, Applai or Ilocano.\n";
    "- Basic Standard Qualifications (General Requirements for Govt Positions)\n" .
    "- Basic Education Qualifications (Elementary, Junior High, Senior High, College)\n" .
    "- Basic Work Experience Qualifications (Entry Level, Mid Level, Senior Level)\n" .
    "- Basic Skills Qualifications (Technical, Soft Skills, Language Proficiency)\n" .
    "- Basic Personal Data Sheet (PDS) Requirements (Name, Address, Contact Info, Education, Work Experience)\n" .
    "- Basic Application Process (How to Apply, Required Documents)\n" .
    "- Work Experience Sheet if applicable should be required to fill-up if a failure to complete this document the application will not be entertained(What is Work Experience Sheet)\n ";

    // Combine user message with context
    $fullMessage = $context . "\n:User    " . $userMessage;

    // 1. Choose a model
    $model = env('GEMINI_MODEL', 'gemini-2.5-flash');

    // 2. Build endpoint with API key
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent"
         . '?key=' . env('GEMINI_API_KEY');

    // 3. Payload
    $payload = [
        'contents' => [
            ['parts' => [['text' => $fullMessage]]],
        ],
    ];

    // 4. Fire the request
    $response = Http::withoutVerifying()
                   ->timeout(30)
                   ->post($url, $payload);

    // 5. Handle errors
    if ($response->failed()) {
        return response()->json([
            'error'   => 'Gemini API failed',
            'details' => $response->json(),
        ], $response->status());
    }

    return $response->json();
}


}
