<?php
namespace App\Enums;
enum RequestStatus: string {
    case DRAFT     = 'draft';
    case SUBMITTED = 'submitted';
    case APPROVED  = 'approved';
    case REJECTED  = 'rejected';
}
