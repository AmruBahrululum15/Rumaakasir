<?php

namespace App\Http\Controllers;

use App\Services\GeminiAiAgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function __construct(
        private GeminiAiAgentService $aiAgent
    ) {}

    public function index()
    {
        return view('ai.index');
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {

            $result = $this->aiAgent->chat(
                $validated['message'],
                $request->user()
            );

            /*
            |--------------------------------------------------------------------------
            | NORMALISASI RESPONSE AI
            |--------------------------------------------------------------------------
            | Service bisa mengembalikan string atau array.
            | Kita pastikan frontend selalu menerima:
            |
            | {
            |     success: true,
            |     message: "jawaban AI"
            | }
            |
            */

            if (is_string($result)) {
                return response()->json([
                    'success' => true,
                    'message' => $result,
                ]);
            }

            if (!is_array($result)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format response AI tidak valid.',
                ], 500);
            }

            /*
            |--------------------------------------------------------------------------
            | JIKA SERVICE SUDAH MENGEMBALIKAN ERROR
            |--------------------------------------------------------------------------
            */

            if (isset($result['success']) && $result['success'] === false) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                        ?? 'AI gagal memproses permintaan.',
                ], 500);
            }

            /*
            |--------------------------------------------------------------------------
            | CARI FIELD JAWABAN
            |--------------------------------------------------------------------------
            */

            $answer = $this->extractAnswer($result);

            if ($answer === null || trim($answer) === '') {

                report(new \RuntimeException(
                    'Gemini AI returned a response without readable text.'
                ));

                return response()->json([
                    'success' => false,
                    'message' => 'AI tidak memberikan jawaban.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => $answer,
            ]);

        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada AI: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mencari teks jawaban dari beberapa kemungkinan struktur response.
     */
    private function extractAnswer(array $result): ?string
    {
        $possibleKeys = [
            'message',
            'response',
            'answer',
            'text',
            'content',
            'output',
        ];

        foreach ($possibleKeys as $key) {

            if (
                isset($result[$key]) &&
                is_string($result[$key]) &&
                trim($result[$key]) !== ''
            ) {
                return trim($result[$key]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | NESTED DATA
        |--------------------------------------------------------------------------
        */

        foreach (['data', 'result'] as $parentKey) {

            if (
                isset($result[$parentKey]) &&
                is_array($result[$parentKey])
            ) {

                $nestedAnswer = $this->extractAnswer(
                    $result[$parentKey]
                );

                if ($nestedAnswer !== null) {
                    return $nestedAnswer;
                }
            }

            if (
                isset($result[$parentKey]) &&
                is_string($result[$parentKey]) &&
                trim($result[$parentKey]) !== ''
            ) {
                return trim($result[$parentKey]);
            }
        }

        return null;
    }

    public function reset(Request $request): JsonResponse
    {
        try {

            $this->aiAgent->reset();

            return response()->json([
                'success' => true,
                'message' => 'Percakapan AI telah direset.',
            ]);

        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset percakapan.',
            ], 500);
        }
    }
}