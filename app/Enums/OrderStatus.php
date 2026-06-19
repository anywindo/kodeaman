<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case REFUND_REQUESTED = 'refund_requested';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';
}
