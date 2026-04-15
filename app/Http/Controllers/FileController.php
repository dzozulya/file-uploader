<?php

namespace App\Http\Controllers;

use App\Actions\Files\DeleteFileAction;
use App\Actions\Files\StoreFileAction;
use App\Http\Requests\StoreFileRequest;
use App\Models\FileItem;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class FileController extends Controller
{
    public function create(): View
    {
        return view('files.create');
    }

    public function index(): View
    {
        $fileItems = FileItem::query()
            ->whereNull('deleted_at')
            ->latest('id')
            ->get();

        return view('files.index', compact('fileItems'));
    }

    public function store(
        StoreFileRequest $request,
        StoreFileAction $action
    ): JsonResponse {
        $fileItem = $action->execute($request->file('file'));

        return response()->json([
            'message' => 'File uploaded successfully.',
            'file' => [
                'id' => $fileItem->id,
                'original_name' => $fileItem->original_name,
                'mime_type' => $fileItem->mime_type,
                'extension' => $fileItem->extension,
                'size' => $fileItem->size,
                'uploaded_at' => $fileItem->uploaded_at?->format('Y-m-d H:i:s'),
                'expires_at' => $fileItem->expires_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function destroy(
        FileItem $fileItem,
        DeleteFileAction $action
    ): JsonResponse {
        $action->execute($fileItem, 'manual');

        return response()->json([
            'message' => 'File deleted successfully.',
        ]);
    }
}
