<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Application\Services\Promotion\PromotionalCardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class PromotionalCardAdminController extends Controller
{
    public function __construct(
        private PromotionalCardService $cardService
    ) {
        // Middleware applied in routes/admin.php
    }

    /**
     * Display admin dashboard for promotional cards
     */
    public function index(): Response
    {
        $cards = $this->cardService->getAllCards();

        return Inertia::render('Admin/PromotionalCards/Index', [
            'cards' => $cards,
        ]);
    }

    /**
     * Store a new promotional card
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'category' => 'required|string|in:general,opportunity,training,success,announcement',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('promotional-cards', 'public');
            $validated['image_path'] = $imagePath;
        }

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $validated['is_active'] ?? true;

        $this->cardService->createCard($validated);

        return redirect()->route('admin.promotional-cards.index')
            ->with('success', 'Promotional card created successfully');
    }

    /**
     * Update a promotional card
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'category' => 'required|string|in:general,opportunity,training,success,announcement',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('promotional-cards', 'public');
            $validated['image_path'] = $imagePath;
        }

        $this->cardService->updateCard($id, $validated);

        return redirect()->route('admin.promotional-cards.index')
            ->with('success', 'Promotional card updated successfully');
    }

    /**
     * Delete a promotional card
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->cardService->deleteCard($id);

        return redirect()->route('admin.promotional-cards.index')
            ->with('success', 'Promotional card deleted successfully');
    }

    /**
     * Toggle card active status
     */
    public function toggleActive(int $id): JsonResponse
    {
        $card = $this->cardService->toggleActive($id);

        return response()->json([
            'success' => true,
            'is_active' => $card->is_active,
        ]);
    }

    /**
     * Get card statistics
     */
    public function statistics(int $id): JsonResponse
    {
        $stats = $this->cardService->getCardStatistics($id);

        return response()->json([
            'success' => true,
            'statistics' => $stats,
        ]);
    }

    /**
     * Reorder cards
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:promotional_cards,id',
        ]);

        $this->cardService->reorderCards($validated['order']);

        return response()->json([
            'success' => true,
            'message' => 'Cards reordered successfully',
        ]);
    }
}
