<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTravelOrderRequest;
use App\Http\Requests\UpdateTravelOrderRequest;
use App\Http\Requests\ListTravelOrdersRequest;
use App\Http\Resources\TravelOrderResource;
use App\Application\UseCases\ListTravelOrders;
use App\Application\UseCases\CreateTravelOrder;
use App\Application\UseCases\GetTravelOrderById;
use App\Application\UseCases\UpdateTravelOrder;
use App\Application\UseCases\ApproveTravelOrder;
use App\Application\UseCases\CancelTravelOrder;
use App\Domain\Models\TravelOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class TravelOrderController extends Controller
{

    public function __construct(
        private ListTravelOrders $listUseCase,
        private CreateTravelOrder $createUseCase,
        private GetTravelOrderById $getUseCase,
        private UpdateTravelOrder $updateUseCase,
        private ApproveTravelOrder $approveUseCase,
        private CancelTravelOrder $cancelUseCase
    ) {}

    /**
     * List travel orders with optional filters.
     */
    public function index(ListTravelOrdersRequest $request)
    {
        $this->authorize('viewAny', TravelOrder::class);
        
        $filters = $request->validated();
        $perPage = (int) ($filters['per_page'] ?? 50);
        $user = Auth::user();
        $userId = $user->id;
        $name = $user->name;
        $isAdmin = $user->is_admin;

        $orders = $this->listUseCase->handle($filters, $perPage, $userId, $isAdmin);
        
        return  TravelOrderResource::collection($orders);
        
    }

    /**
     * Store a newly created travel order.
     */
    public function store(StoreTravelOrderRequest $request): JsonResponse
    {
        $this->authorize('create', TravelOrder::class);
        $user = Auth::user();
        
        $order = $this->createUseCase->handle($request->validated(), $user);

        return (new TravelOrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    /** 
     * Display the specified travel order.
     */
    public function show(int|string $id): JsonResponse
    {
        $order = $this->getUseCase->handle($id);

        $this->authorize('view', $order);

        return (new TravelOrderResource($order))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * update the specified travel order.
     */
    public function update(int|string $id, UpdateTravelOrderRequest $request): JsonResponse
    {
        $order = $this->getUseCase->handle($id);

        $this->authorize('update', $order);
        
        $order = $this->updateUseCase->handle($order, $request->validated());

        return (new TravelOrderResource($order))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Approve the specified travel order.
     */
    public function approve(int|string $id): JsonResponse
    {
        $order = $this->getUseCase->handle($id);

        if (!$order) {
            return response()->json(['message' => 'Travel order not found'], 404);
        }

        $this->authorize('approve', $order);
        
        $order = $this->approveUseCase->handle($order);

            return (new TravelOrderResource($order))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Cancel the specified travel order.
     */
    public function cancel(int|string $id): JsonResponse
    {
        $order = $this->getUseCase->handle($id);

        if (!$order) {
            return response()->json(['message' => 'Travel order not found'], 404);
        }

        $this->authorize('cancel', $order);
        
        $order = $this->cancelUseCase->handle($order);

        return (new TravelOrderResource($order))
            ->response()
            ->setStatusCode(200);
    }
}