<?php
namespace App\Enums;
enum QualityStatus: string {
    case GOOD     = 'good';
    case DAMAGED  = 'damaged';
    case RETURNED = 'returned';
}
