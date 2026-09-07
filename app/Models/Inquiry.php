<?php

namespace App\Models;

use App\Enums\InquiryStatus;
use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $customer_name
 * @property string|null $phone
 * @property string|null $note
 * @property string $whatsapp_url
 * @property InquiryStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['customer_name', 'phone', 'note', 'whatsapp_url', 'status'])]
class Inquiry extends Model
{
    /** @use HasFactory<InquiryFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => InquiryStatus::class,
        ];
    }

    /**
     * @return HasMany<InquiryItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(InquiryItem::class);
    }
}
