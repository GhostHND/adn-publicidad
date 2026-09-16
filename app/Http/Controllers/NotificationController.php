<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Models\WebsiteLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function leadFeed(
        Request $request
    ): JsonResponse {
        $afterId =
            max(
                0,
                (int)
                $request->query(
                    'after_id',
                    0
                )
            );

        $latest =
            WebsiteLead::query()
                ->where(
                    'status',
                    'new'
                )
                ->where(
                    'id',
                    '>',
                    $afterId
                )
                ->orderBy(
                    'id'
                )
                ->limit(10)
                ->get()
                ->map(
                    fn (
                        WebsiteLead $lead
                    ) => [
                        'id' =>
                            $lead->id,

                        'lead_number' =>
                            $lead->lead_number,

                        'name' =>
                            $lead->name,

                        'business_name' =>
                            $lead
                                ->business_name,

                        'phone' =>
                            $lead->phone,

                        'email' =>
                            $lead->email,

                        'service_interest' =>
                            $lead
                                ->service_interest,

                        'message' =>
                            $lead->message,

                        'created_at' =>
                            $lead
                                ->created_at
                                ?->format(
                                    'd/m/Y H:i'
                                ),
                    ]
                )
                ->values();

        $newCount =
            WebsiteLead::query()
                ->unreviewed()
                ->count();

        $latestId =
            WebsiteLead::query()
                ->max('id')
            ?? 0;

        return response()->json([
            'new_count' =>
                $newCount,

            'latest_id' =>
                $latestId,

            'leads' =>
                $latest,
        ]);
    }

    public function lead(
        WebsiteLead $websiteLead
    ): JsonResponse {
        $websiteLead->load([
            'client',
            'quotation',
        ]);

        return response()->json([
            'id' =>
                $websiteLead->id,

            'lead_number' =>
                $websiteLead
                    ->lead_number,

            'name' =>
                $websiteLead->name,

            'business_name' =>
                $websiteLead
                    ->business_name,

            'phone' =>
                $websiteLead->phone,

            'email' =>
                $websiteLead->email,

            'service_interest' =>
                $websiteLead
                    ->service_interest,

            'message' =>
                $websiteLead->message,

            'status' =>
                $websiteLead->status,

            'client_id' =>
                $websiteLead->client_id,

            'quotation_id' =>
                $websiteLead
                    ->quotation_id,

            'created_at' =>
                $websiteLead
                    ->created_at
                    ?->format(
                        'd/m/Y H:i'
                    ),
        ]);
    }

    public function pushConfig(): JsonResponse
    {
        return response()->json([
            'public_key' =>
                config(
                    'webpush.public_key'
                ),

            'enabled' =>
                filled(
                    config(
                        'webpush.public_key'
                    )
                )
                &&
                filled(
                    config(
                        'webpush.private_key'
                    )
                ),
        ]);
    }

    public function subscribe(
        Request $request
    ): JsonResponse {
        $validated =
            $request->validate([
                'endpoint' => [
                    'required',
                    'string',
                    'max:10000',
                ],

                'keys.p256dh' => [
                    'required',
                    'string',
                    'max:10000',
                ],

                'keys.auth' => [
                    'required',
                    'string',
                    'max:10000',
                ],

                'contentEncoding' => [
                    'nullable',
                    'string',
                    'max:50',
                ],
            ]);

        $endpoint =
            $validated['endpoint'];

        $subscription =
            PushSubscription::query()
                ->updateOrCreate(
                    [
                        'endpoint_hash' =>
                            hash(
                                'sha256',
                                $endpoint
                            ),
                    ],
                    [
                        'user_id' =>
                            $request
                                ->user()
                                ->id,

                        'endpoint' =>
                            $endpoint,

                        'p256dh' =>
                            $validated[
                                'keys'
                            ][
                                'p256dh'
                            ],

                        'auth' =>
                            $validated[
                                'keys'
                            ][
                                'auth'
                            ],

                        'content_encoding' =>
                            $validated[
                                'contentEncoding'
                            ]
                            ??
                            'aes128gcm',

                        'user_agent' =>
                            $request
                                ->userAgent(),

                        'last_used_at' =>
                            now(),
                    ]
                );

        return response()->json([
            'success' =>
                true,

            'id' =>
                $subscription->id,
        ]);
    }

    public function unsubscribe(
        Request $request
    ): JsonResponse {
        $validated =
            $request->validate([
                'endpoint' => [
                    'required',
                    'string',
                    'max:10000',
                ],
            ]);

        PushSubscription::query()
            ->where(
                'endpoint_hash',
                hash(
                    'sha256',
                    $validated['endpoint']
                )
            )
            ->where(
                'user_id',
                $request
                    ->user()
                    ->id
            )
            ->delete();

        return response()->json([
            'success' =>
                true,
        ]);
    }
}