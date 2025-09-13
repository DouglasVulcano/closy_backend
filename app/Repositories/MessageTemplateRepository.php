<?php

namespace App\Repositories;

use App\Models\MessageTemplate;

class MessageTemplateRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return MessageTemplate::class;
    }
}
