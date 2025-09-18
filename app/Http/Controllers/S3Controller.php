<?php

namespace App\Http\Controllers;

use App\Helpers\S3Helper;
use App\Http\Requests\PresignedUrlRequest;
use Illuminate\Http\JsonResponse;

class S3Controller extends Controller
{
    const EXPIRATION_MINUTES = 3;

    /**
     * @param PresignedUrlRequest $request Dados do arquivo
     * @return JsonResponse URL pré-assinada e URL pública
     */
    public function generatePresignedUrl(PresignedUrlRequest $request): JsonResponse
    {
        try {
            $s3Helper = new S3Helper();

            $uniqueFileName = $s3Helper->generateUniqueFileNameWithDirectory(
                $request->input('file_name'),
                $request->input('directory')
            );

            $presignedUrl = $s3Helper->generatePresignedUploadUrlForObjectKey(
                $uniqueFileName,
                $request->input('content_type'),
                self::EXPIRATION_MINUTES
            );

            return response()->json([
                'presigned_url' => $presignedUrl,
                'file_name' => $uniqueFileName,
                'content_type' => $request->input('content_type'),
                'expiration_minutes' => self::EXPIRATION_MINUTES,
                'public_url' => $s3Helper->getPublicUrl($uniqueFileName),
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao gerar URL pré-assinada',
                'message' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
