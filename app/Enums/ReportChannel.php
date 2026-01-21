<?php

namespace App\Enums;

enum ReportChannel: string
{
    case WEBSITE = 'website';
    case WHATSAPP = 'whatsapp';
    case INSTAGRAM = 'instagram';
    case SP4N = 'sp4n';
    case SUPERAPPS = 'superapps';

    /**
     * Get the label for display
     */
    public function label(): string
    {
        return match($this) {
            self::WEBSITE => 'Website',
            self::WHATSAPP => 'WhatsApp',
            self::INSTAGRAM => 'Instagram DM',
            self::SP4N => 'SP4N LAPOR!',
            self::SUPERAPPS => 'SuperApps',
        };
    }

    /**
     * Get the icon for display
     */
    public function icon(): string
    {
        return match($this) {
            self::WEBSITE => 'heroicon-o-globe-alt',
            self::WHATSAPP => 'heroicon-o-chat-bubble-left-right',
            self::INSTAGRAM => 'heroicon-o-camera',
            self::SP4N => 'heroicon-o-building-office-2',
            self::SUPERAPPS => 'heroicon-o-device-phone-mobile',
        };
    }

    /**
     * Get all channels as options for select
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($channel) => [$channel->value => $channel->label()])
            ->toArray();
    }
}
