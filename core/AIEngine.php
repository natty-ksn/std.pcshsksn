<?php
/**
 * AIEngine — สมองกลสำหรับ PCSHSKSN e-Portfolio
 *
 * ใช้สำหรับติดต่อกับ Google Gemini API / Vertex AI Express Mode เพื่อวิเคราะห์ข้อมูลและให้คำแนะนำ
 * - ดึงค่า API Key และ Model จาก Database Settings (หน้าผู้ดูแลระบบ) เป็นลำดับแรก แล้ว Fallback ไปที่ .env
 * - ปิด MySQL Connection (Database::close()) ชั่วคราวก่อนยิง cURL ภายนอกทุกครั้ง เพื่อป้องกันปัญหา max_user_connections เต็ม
 * - รองรับทั้งคีย์มาตรฐาน Google AI Studio (AIza...) และคีย์ Vertex AI Express Mode (AQ....)
 * - มีระบบ Auto-Fallback สลับโมเดลอัตโนมัติหากโมเดลเดิมถูก Google ปิดให้บริการ (เช่น gemini-1.5-flash / gemini-2.0-flash -> gemini-3.8-flash)
 */
class AIEngine
{
    private static string $apiKey = '';
    private static string $model = 'gemini-2.5-flash';
    private static ?string $lastError = null;
    private static ?string $lastUsedModel = null;

    private const GEN_LANG_URL = 'https://generativelanguage.googleapis.com/v1beta/models/';
    private const VERTEX_EXPRESS_URL = 'https://aiplatform.googleapis.com/v1/publishers/google/models/';

    /**
     * โมเดลที่ถูก Google ยกเลิกแล้ว ให้แปลงเป็นรุ่นปัจจุบันอัตโนมัติ
     */
    private const RETIRED_MODELS = [
        'gemini-1.5-flash' => 'gemini-2.5-flash',
        'gemini-1.5-pro'   => 'gemini-2.5-pro',
        'gemini-2.0-flash' => 'gemini-2.5-flash',
    ];

    /**
     * ลำดับโมเดลสำรองกรณีโมเดลหลักไม่พร้อมใช้งาน (HTTP 404 / 429)
     */
    private const FALLBACK_MODELS = [
        'gemini-2.5-flash',
        'gemini-2.5-flash-lite',
        'gemini-2.0-flash',
        'gemini-flash-latest',
    ];

    /**
     * Initialize keys from Database Settings first, then .env
     */
    public static function init(): bool
    {
        self::$lastError = null;

        $dbKey = '';
        $dbModel = '';
        if (class_exists('Settings')) {
            $dbKey = trim(Settings::get('gemini_api_key', ''));
            $dbModel = trim(Settings::get('gemini_model', ''));
        }

        self::$apiKey = $dbKey !== '' ? $dbKey : trim(Env::get('GEMINI_API_KEY', ''));
        $rawModel = $dbModel !== '' ? $dbModel : trim(Env::get('GEMINI_MODEL', 'gemini-2.5-flash'));

        self::$model = self::RETIRED_MODELS[$rawModel] ?? ($rawModel ?: 'gemini-2.5-flash');

        if (empty(self::$apiKey)) {
            self::$lastError = 'ยังไม่ได้ตั้งค่า GEMINI_API_KEY ในหน้าตั้งค่าระบบหรือไฟล์ .env';
            App::log("AI Engine Error: GEMINI_API_KEY is not set in Settings or .env", "WARNING");
            return false;
        }
        return true;
    }

    public static function getLastError(): ?string
    {
        return self::$lastError;
    }

    public static function getLastUsedModel(): string
    {
        return self::$lastUsedModel ?? self::$model;
    }

    /**
     * Send prompt to AI API via cURL (releases DB connection during network wait).
     * Automatically detects provider from API key prefix:
     * - gsk_...         => Groq Cloud (Free Tier, No Billing/Card required)
     * - sk-or-...       => OpenRouter (Free Models, No Billing/Card required)
     * - csk-...         => Cerebras Cloud (Free Tier, No Billing/Card required)
     * - ghp_ / github_pat_ => GitHub Models (Free with GitHub account)
     * - AIza... / AQ... => Google Gemini / Vertex AI
     */
    public static function generateResponse(
        string $prompt,
        string $systemInstruction = '',
        array $generationConfigOverrides = []
    ): ?string {
        if (!self::init()) {
            return null;
        }

        // CRITICAL: ปิด MySQL Connection ก่อนยิง HTTP ภายนอกที่ใช้เวลา 3-25 วินาที
        // เพื่อคืน Slot ให้ผู้ใช้อื่นล็อกอิน/ใช้งานได้ทันที ไม่ติด max_user_connections
        Database::close();

        // ตรวจสอบว่าเป็นคีย์ของค่าย AI ฟรีแบบ OpenAI-compatible หรือไม่
        if (
            str_starts_with(self::$apiKey, 'gsk_') ||
            str_starts_with(self::$apiKey, 'sk-or-') ||
            str_starts_with(self::$apiKey, 'csk-') ||
            str_starts_with(self::$apiKey, 'ghp_') ||
            str_starts_with(self::$apiKey, 'github_pat_')
        ) {
            return self::generateOpenAICompatibleResponse($prompt, $systemInstruction, $generationConfigOverrides);
        }

        // สร้างรายการโมเดลที่จะลองตามลำดับ (สำหรับ Google Gemini / Vertex)
        $modelsToTry = array_values(array_unique(array_merge([self::$model], self::FALLBACK_MODELS)));

        // หากคีย์ขึ้นต้นด้วย AQ. (Vertex AI Express Mode Key) ให้ลอง Vertex Endpoint ก่อน แล้วตามด้วย Generative Language Endpoint
        $isVertexKey = str_starts_with(self::$apiKey, 'AQ.');
        $endpoints = $isVertexKey
            ? [self::VERTEX_EXPRESS_URL, self::GEN_LANG_URL]
            : [self::GEN_LANG_URL];

        foreach ($endpoints as $baseUrl) {
            foreach ($modelsToTry as $candidateModel) {
                $url = $baseUrl . $candidateModel . ':generateContent?key=' . urlencode(self::$apiKey);

                $generationConfig = array_merge([
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 2048,
                ], $generationConfigOverrides);

                $payload = [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => $generationConfig,
                ];

                if (!empty($systemInstruction)) {
                    $payload['systemInstruction'] = [
                        'parts' => [
                            ['text' => $systemInstruction]
                        ]
                    ];
                }

                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'Accept: application/json',
                    ],
                    CURLOPT_CONNECTTIMEOUT => 8,
                    CURLOPT_TIMEOUT => 25,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_SSL_VERIFYPEER => true,
                ]);

                $result = curl_exec($ch);
                $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_close($ch);

                if ($result === false || $curlErr !== '') {
                    self::$lastError = "Network error: {$curlErr}";
                    App::log("AI API cURL Failed ({$candidateModel}): {$curlErr}", "ERROR");
                    continue;
                }

                $response = json_decode((string) $result, true);

                if ($httpCode === 200 && isset($response['candidates'][0]['content']['parts'][0]['text'])) {
                    self::$lastUsedModel = $candidateModel;
                    self::$lastError = null;
                    return $response['candidates'][0]['content']['parts'][0]['text'];
                }

                $errMsg = $response['error']['message'] ?? "HTTP {$httpCode}";
                if (self::$lastError === null || !str_contains(self::$lastError, 'billing')) {
                    self::$lastError = $errMsg;
                }

                // หากเป็น 404 (โมเดลถูกถอด) ให้ลองโมเดลถัดไปทันที
                if ($httpCode === 404) {
                    continue;
                }

                // หากเป็น 403 บน Endpoint นี้ ให้ข้ามไปลอง Endpoint ถัดไป (ไม่ต้องวนทุกโมเดลบน Endpoint เดิมที่ติด 403)
                if ($httpCode === 403) {
                    App::log("AI API Permission Denied ({$baseUrl}{$candidateModel}): {$errMsg}", "WARNING");
                    break;
                }

                App::log("AI API Error ({$candidateModel}, HTTP {$httpCode}): {$errMsg}", "WARNING");
            }
        }

        return null;
    }

    /**
     * รองรับผู้ให้บริการ AI ฟรีแบบไม่ต้องผูกบัตรเครดิต (Groq, OpenRouter, Cerebras, GitHub Models)
     */
    private static function generateOpenAICompatibleResponse(
        string $prompt,
        string $systemInstruction = '',
        array $generationConfigOverrides = []
    ): ?string {
        if (str_starts_with(self::$apiKey, 'gsk_')) {
            $endpoint = 'https://api.groq.com/openai/v1/chat/completions';
            $defaultModels = [
                'openai/gpt-oss-120b',
                'qwen/qwen3.8-27b',
                'openai/gpt-oss-20b',
                'llama-3.3-70b-versatile',
            ];
        } elseif (str_starts_with(self::$apiKey, 'sk-or-')) {
            $endpoint = 'https://openrouter.ai/api/v1/chat/completions';
            $defaultModels = [
                'meta-llama/llama-3.3-70b-instruct:free',
                'deepseek/deepseek-chat-v3-0324:free',
                'google/gemini-2.0-flash-exp:free',
                'qwen/qwen3-32b:free',
            ];
        } elseif (str_starts_with(self::$apiKey, 'csk-')) {
            $endpoint = 'https://api.cerebras.ai/v1/chat/completions';
            $defaultModels = ['llama-3.3-70b', 'llama3.1-8b'];
        } else {
            $endpoint = 'https://models.inference.ai.azure.com/chat/completions';
            $defaultModels = ['gpt-4o-mini', 'Llama-3.3-70B-Instruct'];
        }

        // หากแอดมินระบุชื่อโมเดลเฉพาะที่ไม่ใช่ gemini-* ให้ลองโมเดลนั้นก่อน
        $customModel = (!str_starts_with(self::$model, 'gemini-')) ? [self::$model] : [];
        $modelsToTry = array_values(array_unique(array_merge($customModel, $defaultModels)));

        $messages = [];
        if (!empty($systemInstruction)) {
            $messages[] = ['role' => 'system', 'content' => $systemInstruction];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $wantJson = ($generationConfigOverrides['responseMimeType'] ?? '') === 'application/json';
        $temperature = (float) ($generationConfigOverrides['temperature'] ?? 0.6);
        $maxTokens = (int) ($generationConfigOverrides['maxOutputTokens'] ?? 2048);

        foreach ($modelsToTry as $candidateModel) {
            $payload = [
                'model' => $candidateModel,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ];

            if ($wantJson && !str_starts_with(self::$apiKey, 'sk-or-')) {
                $payload['response_format'] = ['type' => 'json_object'];
            }

            $headers = [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . self::$apiKey,
            ];
            if (str_starts_with(self::$apiKey, 'sk-or-')) {
                $headers[] = 'HTTP-Referer: https://pcshsksn.ac.th';
                $headers[] = 'X-Title: PCSHSKSN e-Portfolio';
            }

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => 25,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $result = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($result === false || $curlErr !== '') {
                self::$lastError = "Network error: {$curlErr}";
                continue;
            }

            $response = json_decode((string) $result, true);
            $content = $response['choices'][0]['message']['content'] ?? null;

            if ($httpCode === 200 && !empty($content)) {
                // ลบ <think>...</think> หากโมเดลตระกูล Qwen/DeepSeek ส่งติดมา
                $content = trim(preg_replace('/<think>.*?<\/think>/s', '', (string) $content));
                self::$lastUsedModel = $candidateModel;
                self::$lastError = null;
                return $content;
            }

            $errMsg = $response['error']['message'] ?? "HTTP {$httpCode}";
            self::$lastError = $errMsg;
            App::log("AI Provider Error ({$candidateModel}, HTTP {$httpCode}): {$errMsg}", "WARNING");
        }

        return null;
    }

    /**
     * Helper: วิเคราะห์ความพร้อมของนักเรียน (Career Readiness Gap Analysis)
     */
    public static function analyzeCareerGap(array $studentProfile, array $portfolios, string $targetCareer): ?string
    {
        $system = "คุณคือผู้เชี่ยวชาญด้านแนะแนวการศึกษาต่อระดับมหาวิทยาลัย (Educational Consultant) ประจำโรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย
                  หน้าที่ของคุณคือวิเคราะห์ 'ช่องว่าง' (Gap Analysis) ระหว่างข้อมูลปัจจุบันของนักเรียนกับความต้องการของคณะ/อาชีพเป้าหมาย
                  ให้นำเสนอคำแนะนำที่สร้างสรรค์ เป็นรูปธรรม และเพิ่มกำลังใจให้นักเรียน";

        $prompt = "วิเคราะห์ข้อมูลนักเรียนต่อไปนี้:\n";
        $prompt .= "เป้าหมายอาชีพ: {$targetCareer}\n";
        $prompt .= "GPA: {$studentProfile['gpa']}\n";
        $prompt .= "รายละเอียด Bio: {$studentProfile['bio']}\n";
        $prompt .= "ผลงานในพอร์ตโฟลิโอ: " . count($portfolios) . " รายการ\n\n";

        $prompt .= "รายการผลงานหลัก:\n";
        foreach (array_slice($portfolios, 0, 10) as $p) {
            $prompt .= "- {$p['title']} (หมวดหมู่: {$p['category_name']})\n";
        }

        $prompt .= "\nจงให้คำแนะนำในหัวข้อดังนี้:\n";
        $prompt .= "1. จุดแข็งปัจจุบันเทียบกับเป้าหมาย\n";
        $prompt .= "2. สิ่งที่ยังขาดหายไปหรือควรทำเพิ่ม (GAP)\n";
        $prompt .= "3. แผนการดำเนินงานแนะนำ (Recommended Action Plan)\n";
        $prompt .= "4. คำแนะนำในการเขียนบรรยายผลงานเพื่อให้โดนใจคณะกรรมการ";

        return self::generateResponse($prompt, $system);
    }
}

