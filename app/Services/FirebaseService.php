<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Storage;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $storage;
    protected $bucket;

    public function __construct()
    {
        try {
            $firebase = (new Factory)
                ->withServiceAccount(config('firebase.credentials'))
                ->withDatabaseUri(config('firebase.database_url'))
                ->createStorage();

            $this->storage = $firebase;
            $this->bucket = $firebase->getBucket();
        } catch (\Exception $e) {
            Log::error('Firebase initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Upload a file to Firebase Storage
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $path
     * @return string|null
     */
    public function uploadFile($file, $path = 'bugs')
    {
        try {
            if (!$file) {
                return null;
            }

            $fileName = time() . '_' . $file->getClientOriginalName();
            $fullPath = $path . '/' . $fileName;

            $object = $this->bucket->upload(
                file_get_contents($file->getRealPath()),
                [
                    'name' => $fullPath,
                    'metadata' => [
                        'contentType' => $file->getMimeType(),
                    ],
                ]
            );

            // Get the public URL
            $url = $object->signedUrl(new \DateTime('+1 year'));

            Log::info('File uploaded to Firebase', [
                'path' => $fullPath,
                'url' => $url
            ]);

            return $url;
        } catch (\Exception $e) {
            Log::error('Firebase upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Delete a file from Firebase Storage
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile($path)
    {
        try {
            $object = $this->bucket->object($path);
            $object->delete();

            Log::info('File deleted from Firebase', [
                'path' => $path
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Firebase delete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
