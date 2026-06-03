<?php

namespace App\Enums;

enum WebhookTrigger: string
{
    case CardCreated      = 'card.created';
    case CardUpdated      = 'card.updated';
    case CardFinished     = 'card.finished';
    case CustomerCreated  = 'customer.created';
    case CustomerUpdated  = 'customer.updated';
    case All              = '*';
}
