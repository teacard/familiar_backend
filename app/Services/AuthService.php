<?php

namespace App\Services;

use App\Data\AdminApi\Request\Auth\LoginRequestData;
use App\Enums\Admin\Status;
use App\Enums\Auth\ApiCode;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableException;
use App\Models\Admin;
use App\Repositories\Applications\Admin\AdminRepository;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Traits\AsRepositoryProxy;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    use AsRepositoryProxy;

    public function __construct(
        protected AdminRepository $repository,
    ) {
    }

    /** email + 密碼驗證，回傳已認證的 Admin */
    public function login(LoginRequestData $data): Admin
    {
        /** @var Admin|null $admin */
        $admin = $this->first(['email' => $data->email]);

        throw_if(
            condition: is_null($admin) || !Hash::check($data->password, $admin->password),
            exception: new UnprocessableException('帳號或密碼錯誤', ApiCode::INVALID_CREDENTIALS),
        );

        throw_if(
            condition: Status::ACTIVE !== $admin->status,
            exception: NotFoundException::class,
        );

        $admin->tokens()->delete();

        return $admin;
    }

    protected function getProxyRepository(): RepositoryInterface
    {
        return $this->repository;
    }
}
