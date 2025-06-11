<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\User;
use App\Notifications\InspectionAssignedNotification;
use App\Notifications\InspectionUnderReviewNotification;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class InspectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Inspection::query();

        $query->with(['lastInspectedLot', 'lastReview', 'plant', 'product.client', 'product.attributes', 'groupLeader', 'salesAgents', 'defects', 'reworks']);
        $query->withCount('reviews');
        $query->latest('id');

        $query->when($request->input('filter.status'), function ($query, $status) {
            return $query->whereIn('status', Arr::wrap($status));
        });

        if ($request->user()->role === User::GROUP_LEADER_ROLE) {
            $query->where('group_leader_id', $request->user()->id);
        }

        $query->when($request->input('search'), function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('product', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                })->orWhereHas('product.client', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                })->orWhere('id', $search);
            });
        });

        /** @var LengthAwarePaginator $result */
        $result = $query->paginate($request->input('page_size'));
        $result->getCollection()->each(fn($inspection) => $inspection->append('client'));

        return $result;
    }

    public function show(Inspection $inspection)
    {
        $inspection->load(['lastInspectedLot', 'lastReview', 'plant', 'product.client', 'product.attributes', 'groupLeader', 'salesAgents', 'defects', 'reworks']);
        $inspection->loadCount('reviews');
        $inspection->append('client');
        return $inspection;
    }

    public function store(Request $request)
    {
        $payload = $request->all();
        $inspection = DB::transaction(function () use ($payload) {
            $status = Arr::get($payload, 'status');
            if ($status === Inspection::ACTIVE_STATUS) {
                $payload['start_date'] = now();
            }
            if ($status === Inspection::COMPLETED_STATUS) {
                $payload['start_date'] = now();
                $payload['complete_date'] = now();
            }

            /** @var Inspection $inspection */
            $inspection = Inspection::create(Arr::except($payload, ['sales_agent_ids', 'defect_ids', 'rework_ids']));
            $inspection->salesAgents()->sync($payload['sales_agent_ids']);
            $inspection->defects()->sync($payload['defect_ids']);
            $inspection->reworks()->sync($payload['rework_ids']);

            // Notify group leader when inspection is assigned
            $groupLeader = User::find($inspection->group_leader_id);
            if ($groupLeader) {
                $groupLeader->notify(new InspectionAssignedNotification($inspection));
            }

            return $inspection;
        });

        return response()->json($inspection, 201);
    }

    public function update(Request $request, Inspection $inspection)
    {
        $payload = $request->all();
        DB::transaction(function () use ($inspection, $payload) {
            $oldGroupLeaderId = $inspection->group_leader_id;
            $newGroupLeaderId = Arr::get($payload, 'group_leader_id');
            $oldStatus = $inspection->status;
            $newStatus = Arr::get($payload, 'status');

            if ($newStatus === Inspection::ACTIVE_STATUS && $inspection->start_date === null) {
                $payload['start_date'] = now();
            }
            if ($newStatus === Inspection::COMPLETED_STATUS && $inspection->complete_date === null) {
                $payload['complete_date'] = now();
            }

            /** @var Inspection $inspection */
            $inspection->update(Arr::except($payload, ['sales_agent_ids', 'defect_ids', 'rework_ids']));
            if (isset($payload['sales_agent_ids'])) {
                $inspection->salesAgents()->sync($payload['sales_agent_ids']);
            }
            if (isset($payload['defect_ids'])) {
                $inspection->defects()->sync($payload['defect_ids']);
            }
            if (isset($payload['rework_ids'])) {
                $inspection->reworks()->sync($payload['rework_ids']);
            }

            // Notify group leader when assigned group leader change
            if ($oldGroupLeaderId !== $newGroupLeaderId) {
                $newGroupLeader = User::find($newGroupLeaderId);
                if ($newGroupLeader) {
                    $newGroupLeader->notify(new InspectionAssignedNotification($inspection));
                }
            }

            // Notify admins when inspection status changes to under review
            if ($oldStatus !== Inspection::UNDER_REVIEW_STATUS && $newStatus === Inspection::UNDER_REVIEW_STATUS) {
                // Maybe should use a queue with query chunking
                $admins = User::whereIn('role', [User::ADMIN_ROLE, User::SUPERADMIN_ROLE])->get();
                foreach ($admins as $admin) {
                    $admin->notify(new InspectionUnderReviewNotification($inspection));
                }
            }
        });

        return $inspection;
    }

    /**
     * Obtiene la lista de colaboradores únicos de las inspecciones previas de un jefe de grupo
     *
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCollaborators(Request $request)
    {
        $user = $request->user();

        $collaborators = Inspection::query()
            // ->where('group_leader_id', $user->id)
            ->where('complete_date', '>', now()->subMonthsWithNoOverflow(3))
            ->whereNotNull('collaborators')
            ->pluck('collaborators')
            ->flatten()
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        return response()->json($collaborators);
    }
}
