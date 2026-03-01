<?php
namespace App\Enums;
enum ReceivingStatus: string {
    case OPEN      = 'open';
    case PARTIAL   = 'partial';
    case COMPLETED = 'completed';
}
