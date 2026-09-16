<?php

namespace App\Observers;

use App\Models\CctvProject;
use App\Models\Installation;

class InstallationObserver
{
    public function saved(
        Installation $installation
    ): void {
        $project =
            CctvProject::query()
                ->where(
                    'work_order_id',
                    $installation
                        ->work_order_id
                )
                ->first();

        if (!$project) {
            return;
        }

        $data = [
            'installation_id' =>
                $installation->id,
        ];

        switch (
            $installation
                ->status
        ) {
            case 'scheduled':
                $data['status'] =
                    'installation_scheduled';
                break;

            case 'on_route':
            case 'in_progress':
                $data['status'] =
                    'installation_in_progress';
                break;

            case 'completed':
                $data['status'] =
                    'installed';

                $data['installed_at'] =
                    $installation
                        ->completed_at
                    ?? now();

                break;

            case 'cancelled':
                if (
                    !in_array(
                        $project
                            ->status,
                        [
                            'installed',
                            'maintenance',
                        ],
                        true
                    )
                ) {
                    $data['status'] =
                        'ready_installation';
                }

                break;
        }

        $project->update(
            $data
        );
    }
}