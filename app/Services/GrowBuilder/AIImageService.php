<?php

namespace App\Services\GrowBuilder;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AIImageService
{
    private string $replicateKey;
    private string $huggingfaceKey;
    private string $provider;

    public function __construct()
    {
        $this->replicateKey = config('services.replicate.api_key', '');
        $this->huggingfaceKey = config('services.huggingface.api_key', '');
        $this->provider = config('services.image.provider', 'huggingface');
    }

    public function isConfigured(): bool
    {
        if ($this->provider === 'huggingface') {
            return !empty($this->huggingfaceKey);
        }
        return !empty($this->replicateKey);
    }

    /**
     * Generate an image from a text prompt
     */
    public function generateImage(string $prompt, array $options = []): ?array
    {
        $width = $options['width'] ?? 1024;
        $height = $options['height'] ?? 1024;
        $numOutputs = $options['num_outputs'] ?? 1;

        if ($this->provider === 'replicate') {
            return $this->generateReplicate($prompt, $width, $height, $numOutputs);
        }
        return $this->generateHuggingFace($prompt, $width, $height);
    }

    /**
     * Generate a logo for a business
     */
    public function generateLogo(string $businessName, string $industry, array $options = []): ?array
    {
        $style = $options['style'] ?? 'minimalist';
        $prompt = "Professional logo for {$businessName}, {$industry} business, {$style} design, vector style, clean lines, solid background, no text shadows, high contrast, modern branding";

        return $this->generateImage($prompt, [
            'width' => 1024,
            'height' => 1024,
            'num_outputs' => 4,
        ]);
    }

    /**
     * Generate via Replicate (Flux/SDXL - paid, ~$0.003/image)
     */
    private function generateReplicate(string $prompt, int $width, int $height, int $numOutputs): ?array
    {
        if (empty($this->replicateKey)) {
            Log::warning('Replicate key not configured, falling back to HuggingFace');
            return $this->generateHuggingFace($prompt, $width, $height);
        }

        $model = config('services.replicate.model', 'black-forest-labs/flux-dev');
        $aspectRatio = $width >= $height ? ($width > $height ? '16:9' : '1:1') : '9:16';

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->replicateKey}",
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.replicate.com/v1/models/' . $model . '/predictions', [
            'input' => [
                'prompt' => $prompt,
                'num_outputs' => $numOutputs,
                'aspect_ratio' => $aspectRatio,
                'output_format' => 'webp',
                'quality' => 80,
            ],
        ]);

        if (!$response->successful()) {
            Log::error('Replicate API failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        $data = $response->json();
        $predictionId = $data['id'] ?? null;
        if (!$predictionId) return null;

        // Poll for completion (up to 60s)
        $urls = [];
        for ($i = 0; $i < 30; $i++) {
            sleep(2);
            $statusResponse = Http::withHeaders([
                'Authorization' => "Bearer {$this->replicateKey}",
            ])->timeout(10)->get("https://api.replicate.com/v1/predictions/{$predictionId}");

            if (!$statusResponse->successful()) break;

            $statusData = $statusResponse->json();
            $status = $statusData['status'] ?? '';

            if ($status === 'succeeded') {
                $output = $statusData['output'] ?? [];
                $urls = is_array($output) ? $output : [$output];
                break;
            }
            if ($status === 'failed') break;
        }

        if (empty($urls)) return null;

        // Download and store each image
        $images = [];
        foreach ($urls as $url) {
            $imageData = $this->downloadImage($url);
            if ($imageData) $images[] = $imageData;
        }

        return empty($images) ? null : $images;
    }

    /**
     * Generate via HuggingFace Inference API (free, rate-limited)
     */
    private function generateHuggingFace(string $prompt, int $width, int $height): ?array
    {
        if (empty($this->huggingfaceKey)) {
            Log::warning('HuggingFace key not configured');
            return null;
        }

        $model = config('services.huggingface.model', 'black-forest-labs/FLUX.1-dev');
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->huggingfaceKey}",
        ])->timeout(60)->post("https://api-inference.huggingface.co/models/{$model}", [
            'inputs' => $prompt,
            'parameters' => [
                'width' => $width,
                'height' => $height,
            ],
        ]);

        if (!$response->successful()) {
            Log::error('HuggingFace image API failed', ['status' => $response->status()]);
            return null;
        }

        // Store the image
        $imagePath = $this->storeBinaryImage($response->body(), 'png');
        return $imagePath ? [$imagePath] : null;
    }

    /**
     * Download an image from URL and store locally
     */
    private function downloadImage(string $url): ?array
    {
        try {
            $response = Http::timeout(30)->get($url);
            if (!$response->successful()) return null;

            $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'webp';
            return $this->storeBinaryImage($response->body(), $ext);
        } catch (\Exception $e) {
            Log::error('Image download failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Store binary image data and return file info
     */
    private function storeBinaryImage(string $data, string $ext): ?array
    {
        $filename = 'ai_' . uniqid() . '.' . $ext;
        $path = 'growbuilder/ai-generated/' . $filename;

        if (Storage::disk('public')->put($path, $data)) {
            return [
                'url' => Storage::disk('public')->url($path),
                'path' => $path,
                'filename' => $filename,
            ];
        }

        return null;
    }
}
