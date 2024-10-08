<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\LeadAppointment;
use App\Models\Sale;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Silber\Bouncer\Bouncer;

class TaskController extends Controller
{
    use AuthorizesRequests;

    protected $bouncer;

    public function __construct(Bouncer $bouncer)
    {
        $this->bouncer = $bouncer;
    }
    public function index(Request $request)
    {
        $this->authorize('manage-tasks');

        if (auth()->user()->director_id === null) {
            return false;
        }

        $tasksPage = $request->input('page', 1);
        $noShowLeadsPage = $request->input('page_no_show_leads', 1);
        $trialsLessThanMonthPage = $request->input('trials', 1);

        $tasks = $this->getTasks($tasksPage);
        $noShowLeads = $this->getNoShowLeads($noShowLeadsPage);
        $trialLessThanMonth = $this->getTrialsLessThanMonth($trialsLessThanMonthPage);
        $renewals = $this->getRenewals();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'noShowLeads' => $noShowLeads,
            'trialLessThanMonth' => $trialLessThanMonth,
            'renewals' => $renewals,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage-tasks');

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'director_id' => 'required|exists:users,id',
            'user_sender_id' => 'required|exists:users,id',
            'task_date' => 'required|date',
            'task_description' => 'required|string',
        ]);

        Task::create($validated);

        return redirect()->back();
    }

    public function show($client_id)
    {
        $this->authorize('manage-tasks');

        $tasks = Task::with('userSender:id,name')
            ->where('client_id', $client_id)
            ->where('director_id', auth()->user()->director_id)
            ->orderBy('task_date', 'asc')
            ->get();

        return response()->json($tasks);
    }

    public function destroy(Task $task)
    {
        $this->authorize('manage-tasks');

        if ($task->director_id !== auth()->user()->director_id) {
            return redirect()->back()->withErrors(['error' => 'У вас нет прав на удаление этой задачи.']);
        }
        $task->delete();

        return redirect()->back();
    }

    private function getTasks($page)
    {
        return Task::with(['client:id,surname,name,birthdate,phone,email', 'userSender:id,name'])
            ->where('director_id', auth()->user()->director_id)
            ->orderBy('task_date')
            ->paginate(50, ['*'], 'page', $page);
    }

    private function getNoShowLeads($page)
    {
        return LeadAppointment::with(['client:id,surname,name,birthdate,phone,email'])
            ->where('director_id', auth()->user()->director_id)
            ->where('status', 'no_show')
            ->orderBy('training_date')
            ->paginate(50, ['*'], 'page_no_show_leads', $page);
    }

    private function getTrialsLessThanMonth($page)
    {
        $currentDate = now();
        $oneMonthAgo = $currentDate->subMonth();
        $directorId = auth()->user()->director_id;

        // Получаем все пробные тренировки, которые были менее месяца назад и относятся к текущему director_id
        $trialsLessThanMonth = Sale::where('sale_date', '>=', $oneMonthAgo)
            ->where('service_type', '=', 'trial')
            ->where('director_id', $directorId)
            ->get();

        // Получаем уникальные client_id из этих пробных тренировок
        $clientIdsLessThanMonth = $trialsLessThanMonth->pluck('client_id')->unique();

        // Получаем клиентов, у которых нет активного абонемента и относятся к текущему director_id
        $trialClientsLessThanMonth = Client::whereIn('id', $clientIdsLessThanMonth)
            ->where('director_id', $directorId)
            ->whereDoesntHave('sales', function ($query) use ($currentDate, $directorId) {
                $query->where('subscription_end_date', '>', $currentDate)
                    ->where('director_id', $directorId);
            })
            ->select('id', 'surname', 'name', 'birthdate', 'phone', 'email')
            ->paginate(50, ['*'], 'trials', $page);

        // Получаем training_date для каждого клиента
        $trialClientsLessThanMonth->each(function ($client) use ($trialsLessThanMonth) {
            $client->training_date = $trialsLessThanMonth->where('client_id', $client->id)->first()->sale_date ?? null;
        });

        return $trialClientsLessThanMonth;
    }

    private function getRenewals() {
        $currentDate = now();

        // Получаем всех клиентов с абонементами, отсортированными по дате окончания
        $clients = Client::where('director_id', auth()->user()->director_id)
            ->where('is_lead', false)
            ->whereHas('sales', function ($query) use ($currentDate) {
                $query->whereIn('service_type', ['group', 'minigroup'])
                    ->where('subscription_duration', '!=', 0.03); // Исключаем записи с разовыми
            })
            ->with(['sales' => function ($query) use ($currentDate) {
                $query->select('client_id', 'subscription_end_date', 'service_type')
                    ->whereIn('service_type', ['group', 'minigroup'])
                    ->where('subscription_duration', '!=', 0.03) // Исключаем записи с разовыми
                    ->orderBy('subscription_end_date', 'desc')
                    ->limit(1);
            }])
            ->select('id', 'surname', 'name', 'birthdate', 'phone', 'email')
            ->get();

        // Фильтруем клиентов по условиям
        $clientsToRenewal = $clients->filter(function ($client) use ($currentDate) {
            $subscriptionEndDate = $client->sales->first()->subscription_end_date ?? null;

            if ($subscriptionEndDate === null) {
                return false;
            }

            // Проверяем, есть ли у клиента хотя бы один действующий абонемент
            $hasActiveSubscription = Sale::where('client_id', $client->id)
                ->where('subscription_end_date', '>', $currentDate)
                ->where('subscription_duration', '!=', 0.03) // Исключаем записи с разовыми
                ->exists();
            if ($hasActiveSubscription) {
                return false;
            }

            // Клиенты, у которых заканчивается абонемент в течение недели
            if ($subscriptionEndDate <= (clone $currentDate)->addDays(7) && $subscriptionEndDate >= $currentDate) {
                return true;
            }

            // Клиенты, у которых абонемент закончился в течение последнего месяца
            if ($subscriptionEndDate >= (clone $currentDate)->subMonth()->startOfDay() && $subscriptionEndDate < $currentDate->startOfDay()) {
                return true;
            }

            return false;
        });

        // Добавляем поля из sales к каждому клиенту
        $clientsToRenewal->each(function ($client) {
            $client->subscription_end_date = $client->sales->first()->subscription_end_date ?? null;
            $client->service_type = $client->sales->first()->service_type ?? null;
        });

        // Пагинация на стороне сервера
        $paginatedClients = $this->serverPaginate($clientsToRenewal);

        return $paginatedClients;
    }

    private function serverPaginate($items)
    {
        $perPage = 50;
        $currentPage = request()->input('page', 1);
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($currentPage, $perPage),
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
