<?php

namespace App\Http\Controllers;

use App\Models\WebsiteLead;
use App\Services\AdnWebRequestService;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Throwable;

class WebsiteLeadDetailController extends Controller
{
    public function show(
        WebsiteLead $websiteLead,
        AdnWebRequestService $adnWeb
    ): InertiaResponse {
        abort_unless(
            $websiteLead->source ===
                'adn_web',
            404
        );

        $websiteLead->load([
            'assignedEmployee',
            'client',
            'quotation',
        ]);

        $webRequest =
            null;

        $integrationError =
            null;

        try {
            $webRequest =
                $adnWeb->detail(
                    $websiteLead
                );
        } catch (
            Throwable $exception
        ) {
            report(
                $exception
            );

            $integrationError =
                'No fue posible consultar temporalmente los datos completos de ADN Web.';
        }

        return Inertia::render(
            'Website/LeadDetail',
            [
                'lead' => [
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
                        $websiteLead
                            ->message,

                    'status' =>
                        $websiteLead->status,

                    'source' =>
                        $websiteLead->source,

                    'assigned_employee' =>
                        $websiteLead
                            ->assignedEmployee
                            ?->full_name,

                    'client_id' =>
                        $websiteLead
                            ->client_id,

                    'client_name' =>
                        $websiteLead
                            ->client
                            ?->display_name,

                    'quotation_id' =>
                        $websiteLead
                            ->quotation_id,

                    'quotation_number' =>
                        $websiteLead
                            ->quotation
                            ?->quotation_number,

                    'reviewed_at' =>
                        $websiteLead
                            ->reviewed_at
                            ?->format(
                                'd/m/Y H:i'
                            ),

                    'attended_at' =>
                        $websiteLead
                            ->attended_at
                            ?->format(
                                'd/m/Y H:i'
                            ),

                    'contacted_at' =>
                        $websiteLead
                            ->contacted_at
                            ?->format(
                                'd/m/Y H:i'
                            ),

                    'created_at' =>
                        $websiteLead
                            ->created_at
                            ?->format(
                                'd/m/Y H:i'
                            ),
                ],

                'webRequest' =>
                    $webRequest,

                'integrationError' =>
                    $integrationError,
            ]
        );
    }

    public function previewFile(
        WebsiteLead $websiteLead,
        int $fileId,
        AdnWebRequestService $adnWeb
    ): HttpResponse {
        return $this
            ->proxyFile(
                $websiteLead,
                $fileId,
                'view',
                $adnWeb
            );
    }

    public function downloadFile(
        WebsiteLead $websiteLead,
        int $fileId,
        AdnWebRequestService $adnWeb
    ): HttpResponse {
        return $this
            ->proxyFile(
                $websiteLead,
                $fileId,
                'download',
                $adnWeb
            );
    }

    private function proxyFile(
        WebsiteLead $websiteLead,
        int $fileId,
        string $mode,
        AdnWebRequestService $adnWeb
    ): HttpResponse {
        abort_unless(
            $websiteLead->source ===
                'adn_web',
            404
        );

        try {
            $remote =
                $adnWeb->file(
                    $websiteLead,
                    $fileId,
                    $mode
                );
        } catch (
            Throwable $exception
        ) {
            report(
                $exception
            );

            abort(
                502,
                'No fue posible conectar con ADN Web.'
            );
        }

        if (
            $remote->status() ===
            404
        ) {
            abort(
                404
            );
        }

        if (
            !$remote->successful()
        ) {
            abort(
                502,
                'No fue posible obtener el archivo solicitado.'
            );
        }

        $headers = [
            'Content-Type' =>
                $remote->header(
                    'Content-Type'
                )
                ?: 'application/octet-stream',

            'Cache-Control' =>
                'private, no-store, no-cache, must-revalidate',

            'Pragma' =>
                'no-cache',

            'X-Content-Type-Options' =>
                'nosniff',
        ];

        $contentDisposition =
            $remote->header(
                'Content-Disposition'
            );

        if (
            $contentDisposition
        ) {
            $headers[
                'Content-Disposition'
            ] =
                $contentDisposition;
        }

        return response(
            $remote->body(),
            200,
            $headers
        );
    }
}