<?php

namespace MahmoudMhamed\BackupBrowse\Http\Controllers;

use Illuminate\Routing\Controller;
use MahmoudMhamed\BackupBrowse\Http\Requests\BackupScheduleRequest;
use MahmoudMhamed\BackupBrowse\Models\BackupSchedule;

class BackupScheduleController extends Controller
{
    public function index()
    {
        $schedules = BackupSchedule::latest()->paginate(20);

        return view('backup-browse::schedules.index', compact('schedules'));
    }

    public function create()
    {
        return view('backup-browse::schedules.create');
    }

    public function store(BackupScheduleRequest $request)
    {
        BackupSchedule::create($request->validated());

        return redirect()
            ->route('backup-browse.schedules.index')
            ->with('success', 'Schedule created successfully.');
    }

    public function edit(BackupSchedule $schedule)
    {
        return view('backup-browse::schedules.edit', compact('schedule'));
    }

    public function update(BackupScheduleRequest $request, BackupSchedule $schedule)
    {
        $schedule->update($request->validated());

        return redirect()
            ->route('backup-browse.schedules.index')
            ->with('success', 'Schedule updated successfully.');
    }

    public function destroy(BackupSchedule $schedule)
    {
        $schedule->delete();

        return redirect()
            ->route('backup-browse.schedules.index')
            ->with('success', 'Schedule deleted successfully.');
    }
}
