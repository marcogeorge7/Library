<?php

namespace App\Services;

use App\Models\Copy;
use App\Models\PrintJob;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PrintJobService
{
    public function __construct(private ThermalBarcodePrinter $printer) {}

    /**
     * @param  Collection<int, Copy>  $copies
     */
    public function print(Collection $copies, ?User $user): PrintJob
    {
        if ($copies->isEmpty()) {
            throw new InvalidArgumentException('No copies selected to print.');
        }

        // Physical print happens first, outside the transaction: if it fails we
        // must not log a job for labels never produced; if it succeeds we must
        // not lose that fact to an unrelated DB error rolling back the record.
        $this->printer->printCopies($copies);

        return DB::transaction(function () use ($copies, $user) {
            $printJob = PrintJob::create(['user_id' => $user?->id]);
            $printJob->copies()->attach($copies->pluck('id'));

            Copy::whereIn('id', $copies->pluck('id'))->update([
                'is_printed' => true,
                'printed_at' => now(),
            ]);

            return $printJob;
        });
    }
}
