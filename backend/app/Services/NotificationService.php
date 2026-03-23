<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\DamageReport;
use App\Models\Message;
use App\Models\NotificationRecord;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class NotificationService
{
    protected const STATUS_TEMPLATES = [
        'confirmed' => [
            'title' => 'Booking confirmed',
            'message' => 'Booking {code} has been confirmed. You can pick it up or wait for the shop to contact you.',
        ],
        'cancelled' => [
            'title' => 'Booking cancelled',
            'message' => 'Booking {code} was cancelled. Contact the shop if you need help rebooking.',
        ],
        'completed' => [
            'title' => 'Booking completed',
            'message' => 'Booking {code} is complete. Thanks for riding with us!',
        ],
    ];

    protected const STATUS_ALIAS = [
        'confirmed' => 'confirmed',
        'approved' => 'confirmed',
        'cancelled' => 'cancelled',
        'rejected' => 'cancelled',
        'completed' => 'completed',
    ];

    protected const TABLE_NAME = 'notifications';

    protected static ?bool $shopIdColumnExists = null;
    protected static ?bool $notificationsTableExists = null;

    public static function bookingCreated(Booking $booking): ?NotificationRecord
    {
        $user = self::resolveUser($booking);
        if (!$user) {
            return null;
        }

        $code = self::formatBookingCode($booking);
        $vehicle = self::vehicleLabel($booking);
        $shop = optional($booking->shop)->name;
        $message = "Your booking {$code} for {$vehicle}";

        if ($shop) {
            $message .= " at {$shop}";
        }

        $message .= ' is now pending approval.';

        $ownerMessage = "Booking {$code} for {$vehicle} is waiting for your confirmation";
        if ($shop) {
            $ownerMessage .= " at {$shop}";
        }
        $ownerMessage .= '.';

        self::notifyShopOwner(
            $booking,
            'New booking received',
            $ownerMessage,
            [
                'type' => 'booking',
                'related_type' => Booking::class,
                'related_id' => $booking->id,
            ]
        );

        return self::sendToUser($user, 'Booking received', $message, [
            'type' => 'booking',
            'related_type' => Booking::class,
            'related_id' => $booking->id,
        ]);
    }

    public static function bookingStatusChanged(Booking $booking, string $status): ?NotificationRecord
    {
        $user = self::resolveUser($booking);
        if (!$user) {
            return null;
        }

        $statusKey = self::resolveStatusKey($status);
        if (!$statusKey) {
            return null;
        }

        $template = self::STATUS_TEMPLATES[$statusKey];
        $code = self::formatBookingCode($booking);
        $body = str_replace('{code}', $code, $template['message']);

        $result = self::sendToUser($user, $template['title'], $body, [
            'type' => 'booking',
            'related_type' => Booking::class,
            'related_id' => $booking->id,
        ]);

        if ($statusKey === 'cancelled') {
            $ownerMessage = "Booking {$code} has been cancelled by the customer.";
            self::notifyShopOwner(
                $booking,
                'Booking cancelled',
                $ownerMessage,
                [
                    'type' => 'booking',
                    'related_type' => Booking::class,
                    'related_id' => $booking->id,
                ]
            );
        }

        return $result;
    }

    public static function messageReceived(Message $message): ?NotificationRecord
    {
        $receiver = $message->receiver ?? User::find($message->receiver_id);
        if (!$receiver) {
            return null;
        }

        $senderName = $message->sender?->name ?? 'Someone';
        $subject = $message->subject ? " \"{$message->subject}\"" : '';
        $body = "{$senderName} sent you a message{$subject}.";

        return self::sendToUser($receiver, 'New message', $body, [
            'type' => 'message',
            'related_type' => Message::class,
            'related_id' => $message->id,
            'attributes' => [
                'shop_id' => $message->booking?->shop_id,
            ],
        ]);
    }

    public static function userRegistered(User $user): NotificationRecord
    {
        $name = trim((string) ($user->name ?? 'New user'));
        $email = $user->email ?? 'No email';
        $roleLabel = $user->role ? ucfirst($user->role) : 'User';
        $title = 'New user registered';
        $message = "{$name} ({$email}) joined as {$roleLabel}.";

        return self::notifyAdmins($title, $message, [
            'type' => 'user',
            'related_type' => User::class,
            'related_id' => $user->id,
        ]);
    }

    public static function shopCreated(Shop $shop): NotificationRecord
    {
        $shop->loadMissing('owner');
        $ownerName = $shop->owner?->name ?? 'Shop owner';
        $title = 'New shop created';
        $message = "{$shop->name} was created by {$ownerName}.";

        return self::notifyAdmins($title, $message, [
            'type' => 'shop',
            'related_type' => Shop::class,
            'related_id' => $shop->id,
            'attributes' => [
                'shop_id' => $shop->id,
            ],
        ]);
    }

    public static function damageReportFiled(DamageReport $report): NotificationRecord
    {
        $booking = $report->booking ?? Booking::with(['user', 'vehicle', 'shop'])->find($report->booking_id);
        $bookingCode = $booking ? self::formatBookingCode($booking) : "#{$report->booking_id}";
        $actor = $booking?->user?->name ?? 'Customer';
        $vehicleLabel = $booking ? self::vehicleLabel($booking) : 'vehicle';
        $description = trim((string) ($report->description ?? ''));
        $title = 'New complaint submitted';
        $descriptionSuffix = $description ? ' ' . $description : '';
        $message = "{$actor} reported an issue for {$vehicleLabel} ({$bookingCode}).{$descriptionSuffix}";

        return self::notifyAdmins($title, $message, [
            'type' => 'report',
            'related_type' => DamageReport::class,
            'related_id' => $report->id,
            'attributes' => [
                'shop_id' => $booking?->shop_id ?? null,
            ],
        ]);
    }

    public static function sendToUser(User $user, string $title, string $message, array $options = []): NotificationRecord
    {
        $attributes = array_merge([
            'user_id' => $user->id,
            'role' => null,
            'shop_id' => null,
            'title' => $title,
            'message' => $message,
            'type' => $options['type'] ?? 'general',
            'related_type' => $options['related_type'] ?? null,
            'related_id' => $options['related_id'] ?? null,
            'is_read' => $options['is_read'] ?? false,
        ], $options['attributes'] ?? []);

        return static::persistNotification($attributes);
    }

    protected static function notifyAdmins(string $title, string $message, array $options = []): NotificationRecord
    {
        return self::sendToRole('admin', $title, $message, $options);
    }

    protected static function sendToRole(string $role, string $title, string $message, array $options = []): NotificationRecord
    {
        $base = [
            'user_id' => null,
            'role' => $role,
            'shop_id' => $options['shop_id'] ?? null,
            'title' => $title,
            'message' => $message,
            'type' => $options['type'] ?? 'general',
            'related_type' => $options['related_type'] ?? null,
            'related_id' => $options['related_id'] ?? null,
            'is_read' => $options['is_read'] ?? false,
        ];
        $attributes = array_merge($base, $options['attributes'] ?? []);
        $attributes['role'] = $role;

        return static::persistNotification($attributes);
    }

    protected static function resolveUser(Booking $booking): ?User
    {
        return $booking->user ?? User::find($booking->user_id);
    }

    protected static function resolveStatusKey(string $status): ?string
    {
        $normalized = strtolower(trim($status));

        return self::STATUS_ALIAS[$normalized] ?? null;
    }

    protected static function formatBookingCode(Booking $booking): string
    {
        $date = $booking->created_at?->format('Ymd') ?? now()->format('Ymd');
        $id = str_pad((string) ($booking->id ?? 0), 4, '0', STR_PAD_LEFT);

        return "BK-{$date}-{$id}";
    }

    protected static function vehicleLabel(Booking $booking): string
    {
        $vehicle = $booking->vehicle;
        if ($vehicle) {
            $brand = trim((string) ($vehicle->brand ?? ''));
            $model = trim((string) ($vehicle->model ?? ''));
            if ($brand && $model) {
                return "{$brand} {$model}";
            }

            return $vehicle->name ?? 'vehicle';
        }

        return 'vehicle';
    }

    protected static function notifyShopOwner(Booking $booking, string $title, string $message, array $options = []): ?NotificationRecord
    {
        $owner = self::resolveShopOwner($booking);
        if (!$owner) {
            return null;
        }

        $shopId = self::shopIdFromBooking($booking);
        $options['attributes'] = array_merge($options['attributes'] ?? [], [
            'role' => 'shop_owner',
            'shop_id' => $shopId,
        ]);

        return self::sendToUser($owner, $title, $message, $options);
    }

    protected static function resolveShopOwner(Booking $booking): ?User
    {
        if ($booking->shop && $booking->shop->owner) {
            return $booking->shop->owner;
        }

        if ($booking->shop_id) {
            $shop = Shop::find($booking->shop_id);
            if ($shop && $shop->owner) {
                return $shop->owner;
            }
        }

        return null;
    }

    protected static function shopIdFromBooking(Booking $booking): ?int
    {
        if ($booking->shop_id) {
            return $booking->shop_id;
        }
        return $booking->shop?->id ?? null;
    }

    protected static function persistNotification(array $attributes): NotificationRecord
    {
        $attributes = static::filterMissingColumns($attributes);

        if (!static::hasNotificationsTable()) {
            return new NotificationRecord($attributes);
        }

        return NotificationRecord::create($attributes);
    }

    protected static function filterMissingColumns(array $attributes): array
    {
        if (!array_key_exists('shop_id', $attributes)) {
            return $attributes;
        }

        if (static::hasShopIdColumn()) {
            return $attributes;
        }

        unset($attributes['shop_id']);

        return $attributes;
    }

    protected static function hasShopIdColumn(): bool
    {
        if (static::$shopIdColumnExists !== null) {
            return static::$shopIdColumnExists;
        }

        static::$shopIdColumnExists =
            Schema::hasTable(static::TABLE_NAME) &&
            Schema::hasColumn(static::TABLE_NAME, 'shop_id');

        return static::$shopIdColumnExists;
    }

    protected static function hasNotificationsTable(): bool
    {
        if (static::$notificationsTableExists !== null) {
            return static::$notificationsTableExists;
        }

        static::$notificationsTableExists = Schema::hasTable(static::TABLE_NAME);

        return static::$notificationsTableExists;
    }
}
