<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Pending = 'pending';
    case UnderReview = 'under_review';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::UnderReview => 'Under Review',
            self::InProgress => 'In Progress',
            self::Resolved => 'Resolved',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::UnderReview => 'blue',
            self::InProgress => 'indigo',
            self::Resolved => 'green',
            self::Rejected => 'red',
        };
    }
}
