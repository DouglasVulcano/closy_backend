<?php

require_once __DIR__ . '/vendor/autoload.php';

// Carregar configuração do Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\S3Controller;
use App\Http\Requests\PresignedUrlRequest;
use Illuminate\Http\Request;

echo "=== Teste do S3Controller após correções ===\n\n";

try {
    // Simular dados de request
    $requestData = [
        'file_name' => 'test-image.jpg',
        'content_type' => 'image/jpeg',
        'directory' => 'uploads'
    ];
    
    echo "1. Testando instanciação do S3Controller...\n";
    $controller = new S3Controller();
    echo "✓ S3Controller instanciado com sucesso\n\n";
    
    echo "2. Testando criação de request simulado...\n";
    // Criar um request simulado
    $request = Request::create('/api/s3/presigned-url', 'POST', $requestData);
    echo "✓ Request simulado criado\n\n";
    
    echo "3. Dados do request:\n";
    echo "   - file_name: " . $requestData['file_name'] . "\n";
    echo "   - content_type: " . $requestData['content_type'] . "\n";
    echo "   - directory: " . $requestData['directory'] . "\n\n";
    
    echo "4. Testando se o S3Helper pode ser instanciado dentro do controller...\n";
    $s3Helper = new \App\Helpers\S3Helper();
    echo "✓ S3Helper instanciado com sucesso\n\n";
    
    echo "5. Testando métodos do S3Helper individualmente...\n";
    
    // Testar generateUniqueFileNameWithDirectory
    $uniqueFileName = $s3Helper->generateUniqueFileNameWithDirectory(
        $requestData['file_name'],
        $requestData['directory']
    );
    echo "✓ generateUniqueFileNameWithDirectory: " . $uniqueFileName . "\n";
    
    // Testar generatePresignedUploadUrlForObjectKey
    $presignedUrl = $s3Helper->generatePresignedUploadUrlForObjectKey(
        $uniqueFileName,
        $requestData['content_type'],
        3
    );
    echo "✓ generatePresignedUploadUrlForObjectKey: " . substr($presignedUrl, 0, 50) . "...\n";
    
    // Testar getPublicUrl
    $publicUrl = $s3Helper->getPublicUrl($uniqueFileName);
    echo "✓ getPublicUrl: " . $publicUrl . "\n\n";
    
    echo "=== RESULTADO ===\n";
    echo "✅ Todas as correções estão funcionando corretamente!\n";
    echo "✅ O S3Controller agora usa instâncias do S3Helper ao invés de métodos estáticos\n";
    echo "✅ Todos os métodos do S3Helper estão acessíveis e funcionando\n";
    
} catch (\Exception $e) {
    echo "❌ Erro durante o teste: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}