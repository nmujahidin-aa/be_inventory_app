<?php
namespace App\Enums;
enum PurchaseOrderStatus: string {
    case DRAFT           = 'draft';
    case PENDING_APPROVAL= 'pending_approval';
    case APPROVED        = 'approved';
    case SENT            = 'sent';
    case CONFIRMED       = 'confirmed';
    case REJECTED        = 'rejected';
    case CANCELLED       = 'cancelled';
}
