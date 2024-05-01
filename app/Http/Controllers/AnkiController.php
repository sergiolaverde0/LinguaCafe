<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\AnkiApiService;


// request classes
use App\Http\Requests\Anki\AddCardToAnkiRequest;

class AnkiController extends Controller
{
    private $ankiApiService;
    
    public function __construct(AnkiApiService $ankiApiService) {
        $this->ankiApiService = $ankiApiService;
    }

    public function addCardToAnki(AddCardToAnkiRequest $request) {
        $language = Auth::user()->selected_language;
        $word = mb_strtolower($request->post('word'));
        $reading = $request->post('reading') ? $request->post('reading') : '';
        $translation = $request->post('translation') ? $request->post('translation') : '';
        $exampleSentence = $request->post('exampleSentence') ? $request->post('exampleSentence') : '';

        try {
            $testResult = $this->ankiApiService->addWord($language, $word, $reading, $translation, $exampleSentence);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
        
        return response()->json($testResult, 200);
    }
}