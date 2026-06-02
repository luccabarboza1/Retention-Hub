<?php

namespace App\Enums;

enum WebhookTrigger: string
{
    case CardCreated     = 'card.created';
    case CardUpdated     = 'card.updated';
    case CardFinished    = 'card.finished';
    case CustomerUpdated = 'customer.updated';
    case All             = '*';
}
