<?php

namespace App\Services;

use App\Models\User;
use App\Helpers\S3Helper;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function __construct(private UserRepository $userRepository) {}

    public function findById(int $id): User
    {
        return $this->userRepository->findById($id);
    }

    public function update(int $id, array $data): User
    {
        if (isset($data['profile_picture'])) {
            $user = $this->findById($id);
            if (!empty($user->profile_picture)) {
                try {
                    $s3Helper = new S3Helper();
                    $s3Helper->deleteObjectByUrl($user->profile_picture);
                } catch (\Exception $e) {
                    Log::error('Erro ao deletar imagem anterior do S3: ' . $e->getMessage(), [
                        'user_id' => $id,
                        'old_profile_picture' => $user->profile_picture
                    ]);
                }
            }
        }
        return $this->userRepository->update($id, $data);
    }
}
