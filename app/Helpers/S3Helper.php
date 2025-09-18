<?php

namespace App\Helpers;

use Aws\S3\S3Client;
use Illuminate\Support\Str;
use Aws\S3\Exception\S3Exception;

class S3Helper
{
    private $s3Client;
    private $bucketName;
    private $minioEndpoint;
    private $forcePathStyle;
    private $region;

    public function __construct()
    {
        $this->bucketName = config('filesystems.disks.s3.bucket');
        $this->minioEndpoint = config('filesystems.disks.s3.endpoint');
        $this->forcePathStyle = config('filesystems.disks.s3.use_path_style_endpoint', false);
        $this->region = config('filesystems.disks.s3.region');

        // Inicializar o cliente S3 diretamente
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => $this->region,
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
            'endpoint' => $this->minioEndpoint,
            'use_path_style_endpoint' => $this->forcePathStyle,
        ]);
    }

    /**
     * Gera uma URL pré-assinada para upload de arquivo no S3
     *
     * @param string $fileName Nome do arquivo (já deve ser único)
     * @param string $contentType Tipo de conteúdo do arquivo (MIME type)
     * @param int $expirationMinutes Tempo de expiração do link em minutos
     * @return string URL pré-assinada para upload
     */
    public function generatePresignedUploadUrl(string $fileName, string $contentType, int $expirationMinutes = 15): string
    {
        return $this->generatePresignedUploadUrlWithDirectory($fileName, $contentType, $expirationMinutes, null);
    }

    /**
     * Gera uma URL pré-assinada para upload de arquivo no S3 com diretório específico
     *
     * @param string $fileName Nome do arquivo (já deve ser único)
     * @param string $contentType Tipo de conteúdo do arquivo (MIME type)
     * @param int $expirationMinutes Tempo de expiração do link em minutos
     * @param string|null $directory Diretório onde o arquivo será armazenado (opcional)
     * @return string URL pré-assinada para upload
     */
    public function generatePresignedUploadUrlWithDirectory(string $fileName, string $contentType, int $expirationMinutes = 15, ?string $directory = null): string
    {
        $objectKey = $this->buildObjectKey($fileName, $directory);
        return $this->generatePresignedUploadUrlForObjectKey($objectKey, $contentType, $expirationMinutes);
    }

    /**
     * Gera URL pré-assinada para upload usando objectKey já processado
     *
     * @param string $objectKey Chave do objeto (nome único já processado)
     * @param string $contentType Tipo de conteúdo do arquivo
     * @param int $expirationMinutes Tempo de expiração em minutos
     * @return string URL pré-assinada para upload
     */
    public function generatePresignedUploadUrlForObjectKey(string $objectKey, string $contentType, int $expirationMinutes = 15): string
    {
        try {
            $command = $this->s3Client->getCommand('PutObject', [
                'Bucket' => $this->bucketName,
                'Key' => $objectKey,
                'ContentType' => $contentType,
            ]);

            $request = $this->s3Client->createPresignedRequest(
                $command,
                '+' . $expirationMinutes . ' minutes'
            );

            return (string) $request->getUri();
        } catch (S3Exception $e) {
            throw new \Exception('Erro ao gerar URL pré-assinada: ' . $e->getMessage());
        }
    }

    /**
     * Gera um nome único para o arquivo
     *
     * @param string $originalFileName Nome original do arquivo
     * @return string Nome único para o arquivo
     */
    public function generateUniqueFileName(string $originalFileName): string
    {
        return $this->generateUniqueFileNameWithDirectory($originalFileName, null);
    }

    /**
     * Gera um nome único para o arquivo com diretório específico
     *
     * @param string $originalFileName Nome original do arquivo
     * @param string|null $directory Diretório onde o arquivo será armazenado (opcional)
     * @return string Nome único para o arquivo (incluindo diretório se especificado)
     */
    public function generateUniqueFileNameWithDirectory(string $originalFileName, ?string $directory = null): string
    {
        $extension = '';
        if (str_contains($originalFileName, '.')) {
            $extension = substr($originalFileName, strrpos($originalFileName, '.'));
        }

        $uniqueFileName = Str::uuid()->toString() . $extension;
        return $this->buildObjectKey($uniqueFileName, $directory);
    }

    /**
     * Constrói a chave do objeto no S3 incluindo o diretório se especificado
     *
     * @param string $fileName Nome do arquivo
     * @param string|null $directory Diretório (opcional)
     * @return string Chave completa do objeto
     */
    private function buildObjectKey(string $fileName, ?string $directory = null): string
    {
        if ($directory !== null && trim($directory) !== '') {
            // Remove barras no início e fim, e garante que termine com /
            $cleanDirectory = trim($directory, '/');
            if ($cleanDirectory !== '') {
                return $cleanDirectory . '/' . $fileName;
            }
        }
        return $fileName;
    }

    /**
     * Obtém a URL pública de um arquivo no S3/MinIO
     *
     * @param string $objectKey Chave do objeto no S3
     * @return string URL pública do arquivo
     */
    public function getPublicUrl(string $objectKey): string
    {
        if ($this->minioEndpoint !== null && $this->minioEndpoint !== '') {
            // Para MinIO, usar path-style URL
            $cleanEndpoint = rtrim($this->minioEndpoint, '/');
            return sprintf('%s/%s/%s', $cleanEndpoint, $this->bucketName, $objectKey);
        } else {
            // Para AWS S3, usar o formato tradicional
            return sprintf('https://%s.s3.amazonaws.com/%s', $this->bucketName, $objectKey);
        }
    }

    /**
     * Extrai a chave do objeto a partir de uma URL completa
     *
     * @param string $url URL completa do objeto
     * @return string Chave do objeto extraída da URL
     */
    public function extractObjectKeyFromUrl(string $url): string
    {
        // Remove o protocolo e domínio para obter apenas o caminho
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';

        // Remove o nome do bucket do início do caminho
        $bucketPrefix = '/' . $this->bucketName . '/';
        if (str_starts_with($path, $bucketPrefix)) {
            return substr($path, strlen($bucketPrefix));
        }

        // Se não encontrar o padrão esperado, tenta extrair tudo após o último /
        $pathParts = explode('/', trim($path, '/'));
        if (count($pathParts) >= 2) {
            // Remove o primeiro elemento (bucket name) e junta o resto
            array_shift($pathParts);
            return implode('/', $pathParts);
        }

        // Fallback: retorna o caminho original sem as barras iniciais
        return trim($path, '/');
    }

    /**
     * Deleta um objeto do S3/MinIO
     *
     * @param string $objectKey Chave do objeto no S3 a ser deletado
     * @return void
     */
    public function deleteObject(string $objectKey): void
    {
        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucketName,
                'Key' => $objectKey,
            ]);
        } catch (S3Exception $e) {
            throw new \Exception('Erro ao deletar objeto: ' . $e->getMessage());
        }
    }

    /**
     * Deleta um objeto do S3/MinIO usando uma URL completa
     *
     * @param string $url URL completa do objeto a ser deletado
     * @return void
     */
    public function deleteObjectByUrl(string $url): void
    {
        $objectKey = $this->extractObjectKeyFromUrl($url);
        $this->deleteObject($objectKey);
    }
}
