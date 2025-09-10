<?php

declare(strict_types=1);

namespace Ardenthq\ImageGalleryField;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Attachments\PendingAttachment;

class StorePendingImage
{
    use ValidatesRequests;

    public function __construct(
        /**
         * The field instance.
         */
        public ImageGalleryField $field
    ) {
    }

    /**
     * Attach a pending attachment to the field.
     *
     * @param Request $request
     * @return string
     */
    public function __invoke(Request $request)
    {
        $key = $request->input('key');
        $fullUrl = $request->input('url');
        /** @var string $originalFileName */
        $fileName = $request->input('fileName');
        /** @var string $disk */
        $disk = $this->field->getStorageDisk();
        /** @var string $draftId */
        $draftId = (string) $request->input('draftId');

        Storage::disk($disk)->copy(
            $fileName,
            str_replace('tmp/', '', $key)
        );

        $attachment = PendingAttachment::create([
            'draft_id'      => $draftId,
            'attachment'    => $key,
            'disk'          => $disk,
            'original_name' => $fileName
        ]);

        /** @var FilesystemAdapter $storage */
        $storage = Storage::disk($disk);
        $url     = $storage->url($key);

        // We need to return a string to make it compatible with the parent class
        /** @var string $result */
        $result = json_encode([
            'url' => $url,
            'id'  => $attachment->id,
        ]);

        return $result;
    }
}
