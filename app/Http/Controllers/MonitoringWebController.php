<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MonitoringWebController extends Controller
{
    protected $monitoringController;

    public function __construct(MonitoringController $monitoringController)
    {
        $this->monitoringController = $monitoringController;
    }

    /**
     * Display the monitoring dashboard
     */
    public function index()
    {
        $users = User::where('user_type', 'user')
            ->select('id', 'first_name', 'last_name', 'email', 'username', 'status')
            ->orderBy('first_name')
            ->get();

        return view('monitoring.index', compact('users'));
    }

    /**
     * Get training stats for web interface
     */
    public function getTrainingStats(Request $request): JsonResponse
    {
        return $this->monitoringController->getUserTrainingStats($request);
    }

    /**
     * Get discipline stats for web interface
     */
    public function getDisciplineStats(Request $request): JsonResponse
    {
        return $this->monitoringController->getUserDisciplineStats($request);
    }

    /**
     * Get monthly category for web interface
     */
    public function getMonthlyCategory(Request $request): JsonResponse
    {
        return $this->monitoringController->getUserMonthlyCategoryAverages($request);
    }

    /**
     * Get competition averages for web interface
     */
    public function getCompetitionAverages(Request $request): JsonResponse
    {
        return $this->monitoringController->getUserCompetitionAverages($request);
    }
}
