<?php
namespace App\Enums;
enum StockOpnameStatus: string {
    case DRAFT     = 'draft';
    case SUBMITTED = 'submitted';
    case APPROVED  = 'approved';
    case ADJUSTED  = 'adjusted';
}
