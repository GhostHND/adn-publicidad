<?php

namespace App\Observers;

use App\Models\Quotation;
use App\Models\WebsiteLead;
use Throwable;

class QuotationLeadObserver
{
    public function created(
        Quotation $quotation
    ): void {
        try {
            $context =
                session(
                    'website_lead_context'
                );

            if (
                !is_array(
                    $context
                )
            ) {
                return;
            }

            $leadId =
                (int) (
                    $context['lead_id']
                    ?? 0
                );

            $clientId =
                (int) (
                    $context['client_id']
                    ?? 0
                );

            if (
                !$leadId
                ||
                !$clientId
            ) {
                session()->forget(
                    'website_lead_context'
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | SEGURIDAD
            |--------------------------------------------------------------------------
            |
            | Solo vinculamos la cotización si corresponde al cliente creado
            | o recuperado al atender la solicitud.
            |
            */

            if (
                (int) $quotation->client_id
                !==
                $clientId
            ) {
                return;
            }

            $lead =
                WebsiteLead::query()
                    ->find(
                        $leadId
                    );

            if (!$lead) {
                session()->forget(
                    'website_lead_context'
                );

                return;
            }

            $lead->update([
                'quotation_id' =>
                    $quotation->id,

                'client_id' =>
                    $clientId,

                'status' =>
                    'quoted',

                'reviewed_at' =>
                    $lead->reviewed_at
                    ?? now(),

                'attended_at' =>
                    $lead->attended_at
                    ?? now(),

                'contacted_at' =>
                    $lead->contacted_at
                    ?? now(),
            ]);

            session()->forget(
                'website_lead_context'
            );
        } catch (
            Throwable $exception
        ) {
            report(
                $exception
            );
        }
    }
}