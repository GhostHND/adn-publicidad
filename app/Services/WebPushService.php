<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\WebsiteLead;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

class WebPushService
{
    public function sendNewLead(
        WebsiteLead $lead
    ): void {
        $publicKey =
            config(
                'webpush.public_key'
            );

        $privateKey =
            config(
                'webpush.private_key'
            );

        if (
            !$publicKey
            ||
            !$privateKey
        ) {
            return;
        }

        $subscriptions =
            PushSubscription::query()
                ->get();

        if (
            $subscriptions->isEmpty()
        ) {
            return;
        }

        $webPush =
            new WebPush([
                'VAPID' => [
                    'subject' =>
                        config(
                            'webpush.subject'
                        ),

                    'publicKey' =>
                        $publicKey,

                    'privateKey' =>
                        $privateKey,
                ],
            ]);

        $payload =
            json_encode(
                [
                    'type' =>
                        'website_lead',

                    'title' =>
                        'Nueva solicitud',

                    'body' =>
                        $lead->name .
                        (
                            $lead->service_interest
                                ? ' · ' .
                                    $lead->service_interest
                                : ''
                        ),

                    'lead_id' =>
                        $lead->id,

                    'lead_number' =>
                        $lead->lead_number,

                    'url' =>
                        '/website/leads?highlight=' .
                        $lead->id,

                    'tag' =>
                        'website-lead-' .
                        $lead->id,
                ],
                JSON_UNESCAPED_UNICODE
                |
                JSON_UNESCAPED_SLASHES
            );

        foreach (
            $subscriptions
            as $storedSubscription
        ) {
            try {
                $subscription =
                    Subscription::create([
                        'endpoint' =>
                            $storedSubscription
                                ->endpoint,

                        'keys' => [
                            'p256dh' =>
                                $storedSubscription
                                    ->p256dh,

                            'auth' =>
                                $storedSubscription
                                    ->auth,
                        ],

                        'contentEncoding' =>
                            $storedSubscription
                                ->content_encoding,
                    ]);

                $webPush
                    ->queueNotification(
                        $subscription,
                        $payload
                    );

                $storedSubscription
                    ->update([
                        'last_used_at' =>
                            now(),
                    ]);
            } catch (
                Throwable $exception
            ) {
                report(
                    $exception
                );
            }
        }

        try {
            foreach (
                $webPush->flush()
                as $report
            ) {
                if (
                    $report->isSuccess()
                ) {
                    continue;
                }

                if (
                    !$report
                        ->isSubscriptionExpired()
                ) {
                    continue;
                }

                PushSubscription::query()
                    ->where(
                        'endpoint',
                        $report
                            ->getEndpoint()
                    )
                    ->delete();
            }
        } catch (
            Throwable $exception
        ) {
            report(
                $exception
            );
        }
    }
}