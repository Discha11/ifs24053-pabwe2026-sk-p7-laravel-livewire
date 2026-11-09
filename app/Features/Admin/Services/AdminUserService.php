<?php

namespace App\Features\Admin\Services;

use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminUserService
{
    public function __construct(private readonly StockRepositoryInterface $stocks) {}

    public function users(string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->where('role', User::ROLE_MEMBER)
            ->withCount('stocks')
            ->when($search !== '', fn ($query) => $query->where(
                fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('username', 'like', "%{$search}%")
            ))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function stocksOf(User $user, string $search = '', string $status = '', int $perPage = 10): LengthAwarePaginator
    {
        return $this->stocks->paginateForUser($user->id, $search, $status, $perPage);
    }
}
