<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;


class AccountController extends Controller
{
    private string $imageDisk = 's3';
    // Constants
    private const COLLECTOR_LEVELS = ["Discount Accounts",
  "Expert collector",
  "Renowned Collector",
  "Exalted Collector",
  "Mega Collector",
  "World Collector",
  "World Collector +",];
    private const CATEGORIES = [
        'mobile_legend' => 'Mobile Legend',
        'pubg' => 'PUBG'
    ];
    public function __construct()
    {
        $this->imageDisk = 's3';
    }


    public function index(Request $request)
{
    $page = $request->get('page', 1);
    $pageSize = $request->get('pageSize', 10);
    $category = $request->get('category');
    $search = $request->get('q'); // Search query parameter
    $includeSold = $request->get('includeSold', true); // Default to include sold items
    $limit = $request->get('limit'); // Optional limit for search results

    $query = Account::query()
        ->orderBy('created_at', 'desc');

    // Apply category filter
    if ($category) {
        $query->where('category', $category);
    }

    // Apply search filter by title or description
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%')
              ->orWhere('description', 'like', '%' . $search . '%');
        });
    }

    // Filter out sold items if requested
    if (!$includeSold) {
        $query->where('is_sold', false);
    }

    // For search results with limit (for autocomplete/search dropdown)
    if ($limit) {
        $accounts = $query->limit($limit)->get();

        return response()->json([
            'status' => 'success',
            'data' => $accounts
        ]);
    }

    // For paginated results
    $total = $query->count();

    // Paginate results
    $accounts = $query->skip(($page - 1) * $pageSize)
        ->take($pageSize)
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => $accounts,
        'pagination' => [
            'page' => $page,
            'pageSize' => $pageSize,
            'total' => $total,
            'totalPages' => ceil($total / $pageSize)
        ]
    ]);
}

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'skins' => 'required|integer|min:0',
            'collector_level' => 'nullable|string|in:' . implode(',', self::COLLECTOR_LEVELS),
            'category' => 'required|string|in:' . implode(',', array_keys(self::CATEGORIES)),
            'discount' => 'nullable|numeric|min:0|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $imagePaths = [];

        try {
            $imagePaths = array_merge(
                $this->uploadRequestImages($request, 'images'),
                $this->uploadRequestImages($request, 'image')
            );
        } catch (\RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }

        $account = Account::create([
            'id' => (string) Str::uuid(),
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'skins' => $request->skins,
            'collector_level' => $request->collector_level,
            'images' => $imagePaths,
            'category' => $request->category,
            'discount' => $request->discount
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Account created successfully',
            'data' => $account
        ], 201);
    }

    public function show($id)
    {
        $account = Account::find($id);

        if (!$account) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $account
        ]);
    }

    // public function update(Request $request, $id)
    // {
    //     $account = Account::find($id);

    //     if (!$account) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Account not found'
    //         ], 404);
    //     }

    //     // Check if account is marked for deletion
    //     if ($account->deleted_at) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Cannot edit an account that is marked for deletion'
    //         ], 400);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'title' => 'sometimes|required|string|max:255',
    //         'description' => 'sometimes|required|string',
    //         'price' => 'sometimes|required|numeric|min:0',
    //         'skins' => 'sometimes|required|integer|min:0',
    //         'collector_level' => 'nullable|string|in:' . implode(',', self::COLLECTOR_LEVELS),
    //         'images' => 'sometimes|nullable',
    //         'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
    //         'category' => 'sometimes|required|string|in:' . implode(',', array_keys(self::CATEGORIES)),
    //         'discount' => 'nullable|numeric|min:0|max:100'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     $imagePaths = $account->images ?? [];

    //     // Handle new image uploads
    //     if ($request->hasFile('images')) {
    //         $files = $request->file('images');

    //         // If single file, wrap into array
    //         if ($files instanceof UploadedFile) {
    //             $files = [$files];
    //         }

    //         foreach ($files as $image) {
    //             $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();

    //             $path = $image->storeAs(
    //                 'accounts',
    //                 $filename,
    //                 'public'
    //             );

    //             // Store the full URL for access
    //             $imagePaths[] = config('app.url') .  Storage::url($path);
    //         }
    //     }

    //     // Update account with new data including images
    //     $account->update([
    //         'title' => $request->title ?? $account->title,
    //         'description' => $request->description ?? $account->description,
    //         'price' => $request->price ?? $account->price,
    //         'skins' => $request->skins ?? $account->skins,
    //         'collector_level' => $request->collector_level ?? $account->collector_level,
    //         'images' => $imagePaths,
    //         'category' => $request->category ?? $account->category,
    //         'discount' => $request->discount ?? $account->discount
    //     ]);

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Account updated successfully',
    //         'data' => $account
    //     ]);
    // }

    public function update(Request $request, $id)
{
    $account = Account::find($id);

    if (!$account) {
        return response()->json([
            'status' => 'error',
            'message' => 'Account not found'
        ], 404);
    }

    if ($account->deleted_at) {
        return response()->json([
            'status' => 'error',
            'message' => 'Cannot edit an account that is marked for deletion'
        ], 400);
    }

    $validator = Validator::make($request->all(), [
        'title' => 'sometimes|required|string|max:255',
        'description' => 'sometimes|required|string',
        'price' => 'sometimes|required|numeric|min:0',
        'skins' => 'sometimes|required|integer|min:0',
        'collector_level' => 'nullable|string|in:' . implode(',', self::COLLECTOR_LEVELS),
        'images' => 'sometimes|nullable|array',
        'images.*' => 'string', // URLs of images to keep
        'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        'new_images' => 'sometimes|nullable|array',
        'new_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        'category' => 'sometimes|required|string|in:' . implode(',', array_keys(self::CATEGORIES)),
        'discount' => 'nullable|numeric|min:0|max:100'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    // Start with existing images or empty array
    $currentImages = $account->images ?? [];
    $imagesToKeep = $request->images ?? $currentImages;
    $imagesToKeep = array_values(array_filter(array_map(fn ($image) => $this->normalizeImagePath($image), $imagesToKeep)));

    // Handle new image uploads
    try {
        $newImagePaths = array_merge(
            $this->uploadRequestImages($request, 'new_images'),
            $this->uploadRequestImages($request, 'image')
        );
    } catch (\RuntimeException $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }

    // Combine kept images and new images
    $allImages = array_merge($imagesToKeep, $newImagePaths);

    // Update account
    $account->update([
        'title' => $request->title ?? $account->title,
        'description' => $request->description ?? $account->description,
        'price' => $request->price ?? $account->price,
        'skins' => $request->skins ?? $account->skins,
        'collector_level' => $request->collector_level ?? $account->collector_level,
        'images' => $allImages,
        'category' => $request->category ?? $account->category,
        'discount' => $request->discount ?? $account->discount
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Account updated successfully',
        'data' => $account
    ]);
}

    public function markForDeletion($id)
    {
        $account = Account::find($id);

        if (!$account) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not found'
            ], 404);
        }

        $account->update([
            'is_sold' => true,
            'sold_at' => now(),
            // 'deleted_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Account marked for deletion. It will be permanently deleted in 24 hours.'
        ]);
    }

    public function restore($id)
    {
        $account = Account::find($id);

        if (!$account) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not found'
            ], 404);
        }

        $account->update([
            'is_sold' => false,
            'sold_at' => null
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Account restored successfully'
        ]);
    }

    public function destroy($id)
    {
        $account = Account::find($id);

        if (!$account) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not found'
            ], 404);
        }

        // Delete images from storage
        if ($account->images && is_array($account->images)) {
            foreach ($account->images as $imageUrl) {
                // Extract filename from URL and delete from storage
                try {
                    $path = $this->normalizeImagePath($imageUrl);
                    if ($path && Storage::disk($this->imageDisk)->exists($path)) {
                        Storage::disk($this->imageDisk)->delete($path);
                    }
                } catch (\Exception $e) {
                    // Log error but continue
                    \Log::error('Failed to delete image: ' . $e->getMessage());
                }
            }
        }

        $account->forceDelete();

        return response()->json([
            'status' => 'success',
            'message' => 'Account permanently deleted'
        ]);
    }

    public function cleanupExpired()
    {
        $twentyFourHoursAgo = now()->subHours(24);

        $expiredAccounts = Account::where('sold_at', '<', $twentyFourHoursAgo)
            ->get();

        $deletedCount = 0;
        $imagesDeleted = 0;

        foreach ($expiredAccounts as $account) {
            // Delete images from storage
            if ($account->images && is_array($account->images)) {
                foreach ($account->images as $imageUrl) {
                    try {
                        // Extract filename from URL and delete from storage
                        $path = $this->normalizeImagePath($imageUrl);
                        if ($path && Storage::disk($this->imageDisk)->exists($path)) {
                            Storage::disk($this->imageDisk)->delete($path);
                            $imagesDeleted++;
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to delete image during cleanup: ' . $e->getMessage());
                    }
                }
            }

            $account->forceDelete();
            $deletedCount++;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Cleanup completed',
            'data' => [
                'accounts_deleted' => $deletedCount,
                'images_deleted' => $imagesDeleted
            ]
        ]);
    }

    // Remove the separate uploadImage method since we're handling uploads in store/update
    // public function uploadImage(Request $request) { ... }

    public function getConstants()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'collector_levels' => self::COLLECTOR_LEVELS,
                'categories' => self::CATEGORIES
            ]
        ]);
    }

    private function normalizeImagePath(string $image): ?string
    {
        if ($image === '') {
            return null;
        }

        if (!filter_var($image, FILTER_VALIDATE_URL)) {
            return ltrim($image, '/');
        }

        $path = ltrim((string) parse_url($image, PHP_URL_PATH), '/');
        $bucket = (string) config("filesystems.disks.{$this->imageDisk}.bucket");

        if (str_starts_with($path, 'storage/')) {
            return substr($path, 8);
        }

        if ($bucket !== '' && str_starts_with($path, $bucket . '/')) {
            return substr($path, strlen($bucket) + 1);
        }

        return $path ?: null;
    }

    private function uploadRequestImages(Request $request, string $key): array
    {
        if (!$request->hasFile($key)) {
            return [];
        }

        $files = $request->file($key);
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        $paths = [];
        foreach ($files as $image) {
            if (!$image instanceof UploadedFile || !$image->isValid()) {
                throw new \RuntimeException("Invalid uploaded file for {$key}.");
            }

            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storePubliclyAs('accounts', $filename, $this->imageDisk);

            if ($path === false) {
                throw new \RuntimeException("Failed to upload {$key} to DigitalOcean Spaces.");
            }

            $paths[] = $path;
        }

        return $paths;
    }
}
