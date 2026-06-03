<?php

namespace App\Enums;

enum WebhookTrigger: string
{
    case CardCreated      = 'card.created';
    case CardUpdated      = 'card.updated';
    case CardFinished     = 'card.finished';
    case CardDeleted      = 'card.deleted';
    case CustomerCreated  = 'customer.created';
    case CustomerUpdated  = 'customer.updated';
    case CustomerDeleted  = 'customer.deleted';
    case All              = '*';
}
