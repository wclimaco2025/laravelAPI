<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class StatsService
{
    /**
     * Get users registered by day.
     *
     * @return Collection
     */
    public function getUsersByDay(): Collection
    {
        return User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->get();
    }

    /**
     * Get users registered by week.
     *
     * @return Collection
     */
    public function getUsersByWeek(): Collection
    {
        return User::selectRaw('YEAR(created_at) as year, WEEK(created_at) as week, COUNT(*) as count')
            ->groupBy('year', 'week')
            ->orderByRaw('year DESC, week DESC')
            ->get();
    }

    /**
     * Get users registered by month.
     *
     * @return Collection
     */
    public function getUsersByMonth(): Collection
    {
        return User::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderByRaw('year DESC, month DESC')
            ->get();
    }
}
