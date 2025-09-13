<?php

namespace App\Services;

use App\Models\MessageTemplate;
use App\Repositories\MessageTemplateRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageTemplateService
{
    public function __construct(private MessageTemplateRepository $messageTemplateRepository) {}

    public function getPaginated(array $params): LengthAwarePaginator
    {
        return $this->messageTemplateRepository->getPaginated($params);
    }

    public function findById(int $id): MessageTemplate
    {
        return $this->messageTemplateRepository->findById($id);
    }

    public function create(array $data): MessageTemplate
    {
        return $this->messageTemplateRepository->create($data);
    }

    public function update(int $id, array $data): MessageTemplate
    {
        return $this->messageTemplateRepository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->messageTemplateRepository->delete($id);
    }
}
